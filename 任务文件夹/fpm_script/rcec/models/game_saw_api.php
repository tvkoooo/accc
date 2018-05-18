<?php

class game_saw_api extends ModelBase 
{
	public static function get_saw_game_detail_rq($params)
	{
		LogApi::logProcess("game_saw_api:get_saw_game_detail_rq " . json_encode($params));
		
		$result = array(
				'cmd' => 'get_saw_game_detail_rs',
				'result' => game_saw_model::GAME_SAW_ERR_CODE_SUCCESS,
				'uid' => $params['uid'],
				'sid' => $params['sid'],
				'singer_id' => $params['singer_id'],
				'game_id' => $params['game_id'],
				'game_type' => $params['game_type']
		);
		
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$singer_id = isset($params['singer_id'])?$params['singer_id']:0;
		$game_id = isset($params['game_id'])?$params['game_id']:0;
		$game_type = isset($params['game_type'])?$params['game_type']:0;
		
		do {
			if (empty($uid) || empty($sid) || empty($singer_id)) {
				$result['result'] = game_saw_model::GAME_SAW_ERR_CODE_PARAM_ERR;
				break;
			}
			
			$model_saw = new game_saw_model();
			
			$game_inf = $model_saw->get_saw_game_base_inf($game_id);
			
			if (!empty($game_inf)) {
				$game_inf['status_duration'] = $game_inf['game_status_in_time'] - (time() - intval($game_inf['game_status_start']/1000));
				if ($game_inf['status_duration'] < 0) {
					$game_inf['status_duration'] = 0;
				}
				
				// 获取已报名人数
				$enroll_num = $model_saw->get_enroll_number($game_id);
				$enroll_num = isset($enroll_num)?$enroll_num:0;
				$game_inf['enroll_number'] = $enroll_num;
				
				// 获取当前血量，温度
				$game_inf['hp_now'] = $model_saw->get_hp_now($game_id);
				$game_inf['temperature_now'] = $model_saw->get_temperature_now($game_id);
				
				$result['game_inf'] = $game_inf;
				// 判断当前用户是否已报名
				$b_enroll = $model_saw->b_enroll($game_id, $uid);
				
				if ($b_enroll > 0) {
					$result['b_enroll'] = true;
				} else {
					$result['b_enroll'] = false;
				}
			}
		} while(0);
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $result
		);
		
		LogApi::logProcess("game_saw_api:get_saw_game_detail_rq rs " . json_encode($return));
		
		return $return;
	}
	
	public static function saw_game_attack_normal_rq($params)
	{
		LogApi::logProcess("game_saw_api:saw_game_attack_normal_rq " . json_encode($params));
		
		$result = array(
				'cmd' => 'saw_game_attack_normal_rs',
				'result' => game_saw_model::GAME_SAW_ERR_CODE_SUCCESS,
				'uid' => $params['uid'],
				'sid' => $params['sid'],
				'singer_id' => $params['singer_id'],
				'game_id' => $params['game_id'],
				'attack_type' => $params['attack_type']
		);
		
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$singer_id = isset($params['singer_id'])?$params['singer_id']:0;
		$game_id = isset($params['game_id'])?$params['game_id']:0;
		$attack_type = isset($params['attack_type'])?$params['attack_type']:0;
		
		$prize_drop = array();
		$prop_special_drop = array();
		$status_notify = array();
		$settle = array();
		$tips_times = array();
		do {
			if (empty($uid) || empty($sid) || empty($singer_id)) {
				$result['result'] = game_saw_model::GAME_SAW_ERR_CODE_PARAM_ERR;
				break;
			}
			
			// 获取游戏状态
			$model_saw = new game_saw_model();
			
			$saw_status = $model_saw->get_saw_game_status($game_id);
			
			if ($saw_status != game_saw_model::GAME_SAW_STATUS_ING) {
				$result['result'] = game_saw_model::GAME_SAW_ERR_CODE_UNKNOWN;
				break;
			}
			
			$attack_valid = false;
			if ($attack_type == game_saw_model::GAME_SAW_ATTACK_DRILL) {
				$model_saw->drill_attack($game_id);
				$attack_valid = true;
				
			} else if ($attack_type == game_saw_model::GAME_SAW_ATTACK_WATER) {
				$tmp_tm = $model_saw->water_attack($game_id);
				if ($tmp_tm > 0) {
					$attack_valid = true;
				}
			} else {
				// 不支持的攻击类型
				$result['result'] = game_saw_model::GAME_SAW_ERR_CODE_ATTACK_FORM_NOT_SUPPORT;
				break;
			}
			
			// 冗余判断，有必要
			$saw_status = $model_saw->get_saw_game_status($game_id);
			if ($saw_status != game_saw_model::GAME_SAW_STATUS_ING) {
				break;
			}
			
			// 尝试进行游戏结算
			$settle = $model_saw->try_saw_settle($game_id, $sid, $singer_id);
			if (!empty($settle)) {
				break;
			}
			
			// 记录用户攻击次数
			$model_saw->upd_saw_game_user_attack_times($game_id, $uid, 1);
			
			// 每1s推送一次变更
			if ($model_saw->if_notify_hp_tm($game_id)) {
				$hp_now = $model_saw->get_hp_now($game_id);
				$tm_now = $model_saw->get_temperature_now($game_id);
				
				if ($hp_now < 0) {
					$hp_now = 0;
				}
				
				$status_notify['cmd'] = 'saw_game_status_change';
				$status_notify['sid'] = $sid;
				$status_notify['singer_id'] = $singer_id;
				$status_notify['game_id'] = $game_id;
				$status_notify['hp'] = $hp_now;
				$status_notify['temperature'] = $tm_now;
			}
			
			// 计算是否掉落奖励
			// 如果掉落奖励，则调用奖励掉落方法
			if ($attack_valid && $model_saw->if_can_drop_prize($game_id)) {
				$prize_drop = $model_saw->drop_saw_game_prop_normal($game_id, 1);
			}

			// 计算是否掉落游戏道具
			// 如果掉落道具，则调用道具掉落方法
			if ($model_saw->if_can_drop_prop_special($game_id)) {
				$prop_special_drop = $model_saw->drop_saw_game_prop_special($game_id, 1);
			}

			if (game_saw_model::GAME_SAW_TIPS_OPEN && $model_saw->if_notify_tips($game_id)) {
				$tips_times = $model_saw->get_tips_notify_times($game_id);

				$tips_array = $model_saw->get_saw_tips_array();
				if ($tips_times <= count($tips_array)) {
					$tips_notify['cmd'] = "saw_game_tips_nt";
					$tips_notify['sid'] = $sid;
					$tips_notify['singer_id'] = $singer_id;
					$tips_notify['tips'] = $tips_array[$tips_times - 1];
					$tips_notify['game_id'] = $game_id;
				}
			}
		} while (0);
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $result
		);
		
		if (!empty($prize_drop) || !empty($prop_special_drop)) {
			$af = array_merge($prize_drop, $prop_special_drop);
			$return[] = array(
					'broadcast' => 1,
					'data' => array (
							'cmd' => 'saw_game_prop_drop_nt',
							'sid' => $params['sid'],
							'game_id' => $params['game_id'],
							'singer_id' => $params['singer_id'],
							'props' => $af
					)
			);
		}
		
		if (!empty($settle)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $settle
			);
		}
		
		if (!empty($status_notify)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $status_notify
			);
		}

		if (!empty($tips_notify)) {
			$return[] = array(
				'broadcast' => 1,
				'data' => $tips_notify
			);
		}
		
		LogApi::logProcess("game_saw_api:saw_game_attack_normal_rq rs " . json_encode($return));
		return $return;
	}
	
	public static function saw_game_loot_prop_rq($params)
	{
		LogApi::logProcess("game_saw_api:saw_game_loot_prop_rq " . json_encode($params));
		
		$sid = isset($params['sid'])?$params['sid']:0;
		$uid = isset($params['uid'])?$params['uid']:0;
		$singer_id = isset($params['singer_id'])?$params['singer_id']:0;
		$game_id = isset($params['game_id'])?$params['game_id']:0;
		$drop_id = isset($params['drop_id'])?$params['drop_id']:0;
		
		$result = array(
				'cmd' => 'saw_game_loot_prop_rs',
				'uid' => $params['uid'],
				'sid' => $params['sid'],
				'singer_id' => $params['singer_id'],
				'game_id' => $params['game_id'],
				'drop_id' => $params['drop_id'],
				'result' => game_saw_model::GAME_SAW_ERR_CODE_SUCCESS
		);
		
		$nt = array();
		
		do {
			if (empty($sid) || empty($uid) || empty($singer_id)) {
				$result['result'] = game_saw_model::GAME_SAW_ERR_CODE_PARAM_ERR;
				break;
			}
			
			$model_saw = new game_saw_model();
			$loot_res = $model_saw->loot_prop($uid, $game_id, $drop_id);
			
			if (!empty($loot_res)) {
				$result['prop_inf'] = $loot_res;
				
				$model_uinf = new UserInfoModel();
				$uinf = $model_uinf->getInfoById($uid);
				
				$nt['cmd'] = 'saw_game_loot_prop_nt';
				$nt['uid'] = $uid;
				$nt['sid'] = $sid;
				$nt['game_id'] = $game_id;
				$nt['drop_id'] = $drop_id;
				$nt['singer_id'] = $singer_id;
				$nt['prop_inf'] = $loot_res;
				$nt['unick'] = $uinf['nick'];
			} else {
				// 失败
				$result['result'] = game_saw_model::GAME_SAW_ERR_CODE_UNKNOWN;
			}
			
		} while(0);
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $result
		);
		
		if (!empty($nt)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $nt
			);
		}
		
		LogApi::logProcess("game_saw_api:saw_game_loot_prop_rq rs " . json_encode($return));
		return $return;
	}
	
	public static function saw_game_use_prop_special_rq($params)
	{
		LogApi::logProcess("game_saw_api:saw_game_use_prop_special_rq " . json_encode($params));
		
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$singer_id = isset($params['singer_id'])?$params['singer_id']:0;
		$prop_id = isset($params['prop_id'])?$params['prop_id']:0;
		$times = isset($params['times'])?$params['times']:0;
		$game_id = isset($params['game_id'])?$params['game_id']:0;
		
		$rs = array(
				'cmd' => 'saw_game_use_prop_special_rs',
				'result' => game_saw_model::GAME_SAW_ERR_CODE_SUCCESS,
				'uid' => $uid,
				'sid' => $sid,
				'singer_id' => $singer_id,
				'game_id' => $game_id,
				'prop_id' => $prop_id,
				'times' => $times
		);
		
		$settle = array();
		$status_notify = array();
		
		do {
			if (empty($uid) || empty($sid) || empty($singer_id)) {
				$rs['result'] = game_saw_model::GAME_SAW_ERR_CODE_PARAM_ERR;
				break;
			}
			
			$model_saw = new game_saw_model();
			
			$saw_status = $model_saw->get_saw_game_status($game_id);
				
			if ($saw_status != game_saw_model::GAME_SAW_STATUS_ING) {
				$result['result'] = game_saw_model::GAME_SAW_ERR_CODE_UNKNOWN;
				break;
			}
			
			if ($prop_id == game_saw_model::GAME_SAW_PROP_ID_SAW) {
				$model_saw->saw_attack($game_id);
			} else if ($prop_id == game_saw_model::GAME_SAW_PROP_ID_ICE_BLOCK) {
				$model_saw->ice_block_attack($game_id);
			} else {
				$rs['result'] = game_saw_model::GAME_SAW_ERR_CODE_PROP_NOT_SUPPORT;
				break;
			}
			
			// 冗余判断，有必要
			$saw_status = $model_saw->get_saw_game_status($game_id);
			if ($saw_status != game_saw_model::GAME_SAW_STATUS_ING) {
				break;
			}
			
			// 尝试进行游戏结算
			$settle = $model_saw->try_saw_settle($game_id, $sid, $singer_id);
			if (!empty($settle)) {
				break;
			}
			
			if ($model_saw->if_notify_hp_tm($game_id)) {
				$hp_now = $model_saw->get_hp_now($game_id);
				$tm_now = $model_saw->get_temperature_now($game_id);
			
				if ($hp_now < 0) {
					$hp_now = 0;
				}
			
				$status_notify['cmd'] = 'saw_game_status_change';
				$status_notify['sid'] = $sid;
				$status_notify['singer_id'] = $singer_id;
				$status_notify['game_id'] = $game_id;
				$status_notify['hp'] = $hp_now;
				$status_notify['temperature'] = $tm_now;
			}
		} while (0);
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		if (!empty($settle)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $settle
			);
		}
		
		if (!empty($status_notify)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $status_notify
			);
		}
		
		LogApi::logProcess("game_saw_api:saw_game_use_prop_special_rq rs " . json_encode($return));
		
		return $return;
	}
	
	public static function saw_game_loot_prize_rq($params)
	{
		LogApi::logProcess("game_saw_api:saw_game_loot_prize_rq " . json_encode($params));
		
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$singer_id = isset($params['singer_id'])?$params['singer_id']:0;
		$game_id = isset($params['game_id'])?$params['game_id']:0;
		$drop_id = isset($params['drop_id'])?$params['drop_id']:0;
		
		$rs = array(
				'cmd' => 'saw_game_loot_prize_rs',
				'result' => game_saw_model::GAME_SAW_ERR_CODE_SUCCESS,
				'uid' => $params['uid'],
				'sid' => $params['sid'],
				'singer_id' => $params['singer_id'],
				'game_id' => $params['game_id'],
				'drop_id' => $params['drop_id']
		);
		
		$nt = array();
		
		do {
			if (empty($uid) || empty($sid) || empty($singer_id)) {
				$rs['result'] = game_saw_model::GAME_SAW_ERR_CODE_PARAM_ERR;
				break;
			}
			
			$model_saw = new game_saw_model();
			
			$loot_res = $model_saw->loot_box($uid, $game_id, $drop_id);
			
			if ($loot_res['code'] == 0) {
				$rs['box_inf'] = $loot_res['box_inf'];
				$rs['props'] = $loot_res['props'];
				
				$nt['cmd'] = 'saw_game_loot_prize_nt';
				$nt['uid'] = $uid;
				$nt['sid'] = $sid;
				$nt['singer_id'] = $singer_id;
				$nt['game_id'] = $game_id;
				$nt['drop_id'] = $drop_id;
				$nt['box_inf'] = $loot_res['box_inf'];
				$nt['props'] = $loot_res['props'];
				
				$model_uinf = new UserInfoModel();
				$uinf = $model_uinf->getInfoById($uid);
				$nt['unick'] = $uinf['nick'];
				
			} else {
				$rs['result'] = $loot_res['code'];
			}
		} while (0);
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		if (!empty($nt)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $nt
			);
		}
		
		LogApi::logProcess("game_saw_api:saw_game_loot_prize_rq rs " . json_encode($return));
		
		return $return;
	}
	
	public static function saw_game_on_singer_leave($singer_id)
	{
		LogApi::logProcess("game_saw_api:saw_game_on_singer_leave singer_id:$singer_id");
		
		$model_gm = new game_manager_model();
		
		$res = $model_gm->get_game_saw_inf($singer_id);
		
		if (empty($res) || $res['code'] != 0) {
			return;
		}
		
		$game_id = $res['game_id'];
		
		$model_saw = new game_saw_model();
		
		$status = $model_saw->get_saw_game_status($game_id);
		
		if ($status == game_saw_model::GAME_SAW_STATUS_ING ) {
			$model_saw->upd_saw_game_status($game_id, game_saw_model::GAME_SAW_STATUS_FAILURE_LEAVE);
		} else if($status == game_saw_model::GAME_SAW_STATUS_ENROLL) {
			$model_saw->upd_saw_game_status($game_id, game_saw_model::GAME_SAW_STATUS_CANCEL);
		}
	}
}