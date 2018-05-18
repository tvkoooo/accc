<?php

class evt_deal_api 
{

	public static function user_active_level_up_rq($params)
	{
		LogApi::logProcess("evt_deal_api:user_active_level_up_rq " . json_encode($params));

		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$level_old = isset($params['level_old'])?$params['level_old']:0;
		$level_now = isset($params['level_now'])?$params['level_now']:0;


		$result = array(
			'cmd' => 'user_active_level_up_rs',
			'result' => 0,
			'uid' => $uid,
			'sid' => $sid
		);

		do { 
			if (empty($uid) || empty($sid) || empty($level_old) || empty($level_now) || $level_now <= $level_old) {
				$result['result'] = 201;
				break;
			} 

			$model_uinfo = new UserInfoModel();
	        $model_uattr = new UserAttributeModel();
	        $uattr = $model_uattr->getAttrByUid($uid);
	        $user = $model_uinfo->getInfoById($uid);

	        $oldActiveManInfo = $model_uattr->getActiveLevel(0, $uid, $level_old);
        	$newActiveManInfo = $model_uattr->getActiveLevel(0, $uid, $level_now);

			$rich_info = $model_uattr->getRichManLevel($uid, 0, $uattr['consume_level']);
			$model_combat = new CombatModel();
			$card_info = $model_combat->getMaxCombatCardInfo($uid);

			$old_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $oldActiveManInfo, $rich_info);
			$new_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $newActiveManInfo, $rich_info);
			$model_combat->flushCombatAttr2Cache($uid, $card_info['current_format_type'], $new_combat_info);
			
			$levelChange = array();
			$levelChange['cmd'] = 'BUserActiveLevelUp';
			$levelChange['uid'] = $uid;
			$levelChange['nick'] = $user['nick'];
			$levelChange['oldLevel'] = $level_old;
			$levelChange['newLevel'] = $level_now;
			$levelChange['display_id'] = $newActiveManInfo['display_id'];
			$levelChange['combatOld'] = $old_combat_info;
			$levelChange['combatNew'] = $new_combat_info;
			$levelChange['photo'] = $user['photo'];
			
			$model_callback = new callback_dispatch_model();
			$model_callback->dispatch_channel($levelChange, $sid);

    		$sys_parameters = new SysParametersModel();
    		$newer_active_level = $sys_parameters->GetSysParameters(205, 'parm1');
    		
    		if ($level_now >= $newer_active_level) {
    			// 迎新活跃等级
				$model_task_dispatch = new task_dispatch_model();
				$model_task_dispatch->dispatch(intval($uid), 49, (int)$level_now, 0);
    		}

		} while (0);

		LogApi::logProcess("evt_deal_api:user_active_level_up_rq rs ");
	}

	public static function user_rich_level_up_rq($params)
	{
		LogApi::logProcess("evt_deal_api:user_rich_level_up_rq " . json_encode($params));

		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$level_old = isset($params['level_old'])?$params['level_old']:0;
		$level_now = isset($params['level_now'])?$params['level_now']:0;


		$result = array(
			'cmd' => 'user_rich_level_up_rs',
			'result' => 0,
			'uid' => $uid,
			'sid' => $sid
		);

		do {

			if (empty($uid) || empty($sid) || empty($level_old) || empty($level_now) || $level_now <= $level_old) {
				$result['result'] = 201;
				break;
			}

			$model_uattr = new UserAttributeModel();
			$model_uinfo = new UserInfoModel();
    		$user = $model_uinfo->getInfoById($uid);

    		$richManInfo = $model_uattr->getRichManLevel($uid, 0, $level_now);

			$oldRichManInfo = $model_uattr->getRichManLevel($uid, 0, $level_old);

			// 更新用户荣耀值
			$model_glory = new GloryModel();
			$model_glory->gloryAdd($uid, $richManInfo['glory'] - $oldRichManInfo['glory']);
				
			$oldLevel = empty ( $oldRichManInfo ['richManLevel'] ) ? 0 : $oldRichManInfo ['richManLevel'];
			$newLevel = empty ( $richManInfo ['richManLevel'] ) ? 0 : $richManInfo ['richManLevel'];

			$dropid = $richManInfo ['boxid'];
			$is_all = $richManInfo ['is_all'];
			$levelData = array ();
			if (empty ( $dropid )) {
				$levelData = $model_uattr->getUpLevelBoxid ( $oldRichManInfo ['richManLevel'], $richManInfo ['richManLevel'] );
				if (! empty ( $levelData )) {
					$dropid = $levelData ['boxid'];
					$is_all = $levelData ['is_all'];
				}
			}
			$boxid = 0;
			if (! empty ( $dropid )) {
				// 财富升级奖励
				$boxid = $toolConsumRecordModel->getConsumeUpRewards ( $uid, $dropid );
			}
				
			// combat
			$active_info = $model_uattr->getActiveLevel(0, $uid, 0);
			$model_combat = new CombatModel();
			$card_info = $model_combat->getMaxCombatCardInfo($uid);

			$old_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $active_info, $oldRichManInfo);
			$new_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $active_info, $richManInfo);

			$model_combat->flushCombatAttr2Cache($uid, $card_info['current_format_type'], $new_combat_info);

			$data_channel = array (
					'cmd' => 'BRichManLevelUpAward',
					'uid' => $uid,
					'nick' => $user['nick'],
					'oldLevel' => $oldRichManInfo ['richManLevel'],
					'oldLevelTitle' => $oldRichManInfo ['richManTitle'],
					'oldChildLevel' => $oldRichManInfo ['child_level'],
					'newLevel' => $richManInfo ['richManLevel'],
					'newLevelTitle' => $richManInfo ['richManTitle'],
					'newChildLevel' => $richManInfo ['child_level'],
					'richManEffect' => $richManInfo ['richManEffect'],
					'isAll' => ( int ) $is_all,
					'boxid' => $boxid,
					'photo' => $user ['photo'],
					'combatOld' => $old_combat_info,
					'combatNew' => $new_combat_info
			);
			
			$model_callback = new callback_dispatch_model();
			$model_callback->dispatch_channel($data_channel, $sid);

			// 迎新任务
			$model_task_dispatch = new task_dispatch_model();
			$model_task_dispatch->dispatch($uid, 50, 1, 0);

    	} while ( 0 );
	}

	public static function anchor_sunshine_level_up_rq($params)
	{
		LogApi::logProcess("evt_deal_api:anchor_sunshine_level_up_rq " . json_encode($params));

		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$level_old = isset($params['level_old'])?$params['level_old']:0;
		$level_now = isset($params['level_now'])?$params['level_now']:0;


		$result = array(
			'cmd' => 'anchor_level_up_rs',
			'result' => 0,
			'uid' => $uid,
			'sid' => $sid
		);

		do {
			if (empty($uid) || empty($sid) || empty($level_old) || empty($level_now) || $level_now <= $level_old) {
				$result['result'] = 201;
				break;
			}

        	$model_uinfo = new UserInfoModel();

        	$singer = $model_uinfo->getInfoById($uid);
			$singerUp = array();
			$singerUp['cmd'] = 'BSingerRewardUpdate';
			
			$singerUp['uid'] = $uid;
			$singerUp['sid'] = $sid;
			$singerUp['nick'] = $singer['nick'];
			$singerUp['old_level'] = $level_old;
			$singerUp['new_level'] = $level_now;
			
			$model_callback = new callback_dispatch_model();
			$model_callback->dispatch_channel($singerUp, $sid);
			 
            // 发送消息至粉丝群
            $summary = "恭喜" . $singerUp['nick'] . "阳光等级升为" . $level_now . "级，主播感受到了满满的小太阳之爱~";
            $text = "<font color='#8ca0c8'>恭喜</font> <font color='#b4b4ff'>" . $singerUp['nick'] . "</font> <font color='#beaa78'>阳光等级</font> <font color='#8ca0c8'>升为 " . $level_now . "级，主播感受到了满满的小太阳之爱~</font>";
            $msg = array(
                'group_id' => $uid,
                'content' => array(
                        'type' => 0,
                        'text' => $summary,
                        'msgs' => array(
                            0 => array(
                                'content' => $text,
                            )
                        ),
                        'summary' => $summary
                )
            );
			 
			$tmpKey = "zbrewardup:$uid" . ":" . time();
			$model_uinfo->getRedisMaster()->set($tmpKey, json_encode($msg));
			 
			$url = GlobalConfig::GetSendGrpMsgURL() . $tmpKey;
			$ch = curl_init();
			$curl_opt = array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT_MS => 1000
			);
			curl_setopt_array($ch, $curl_opt);
			$data = curl_exec($ch);
			curl_close($ch);
		} while (0);

		LogApi::logProcess("evt_deal_api:anchor_sunshine_level_up_rq rs");
	}

	public static function anchor_level_up_rq($params)
	{
		LogApi::logProcess("evt_deal_api:anchor_level_up_rq " . json_encode($params));

		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$level_old = isset($params['level_old'])?$params['level_old']:0;
		$level_now = isset($params['level_now'])?$params['level_now']:0;


		$result = array(
			'cmd' => 'anchor_level_up_rs',
			'result' => 0,
			'uid' => $uid,
			'sid' => $sid
		);

		do {
			if (empty($uid) || empty($sid) || empty($level_old) || empty($level_now) || $level_now <= $level_old) {
				$result['result'] = 201;
				break;
			}

			$model_uattr = new UserAttributeModel();
			$model_uinfo = new UserInfoModel();
			$user = $model_uinfo->getInfoById($uid);
			$newLevelInfo = $model_uattr->getExperienceLevel($level_now);
			$oldLevelInfo = $model_uattr->getExperienceLevel($level_old);
			$levelChange = $newLevelInfo;

			$levelChange['cmd'] = 'BSingerLevelUp';
			$levelChange['singerUid'] = $uid;
			$levelConf = $model_uattr->getAnchorLevelName($newLevelInfo['singerLevel']);
			if ($levelConf) {
				$levelChange['levelName'] = $levelConf['name'];
				$levelChange['subLevel'] = $newLevelInfo['singerLevel'] - $levelConf['levelStart'] + 1;
			} else {
				$levelChange['levelName'] = "无敌";
				$levelChange['subLevel'] = 1;
			}

			$levelChange['oldLevel'] = $oldLevelInfo['singerLevel'];
			$levelChange['newLevel'] = $newLevelInfo['singerLevel'];
			$levelChange['nick'] = $user['nick'];
			$levelChange['newLevelTitle'] = $newLevelInfo['singerTitle'];
			$levelChange['display_id'] = $newLevelInfo['display_id'];

			$model_callback = new callback_dispatch_model();
			$model_callback->dispatch_channel($levelChange, $sid);

			$summary = "恭喜" . $levelChange['nick'] . "主播等级升为" . $levelChange['levelName'] . $levelChange['subLevel'] . "级，直播更有动力了呢";
			$text = "<font color='#8ca0c8'>恭喜</font> <font color='#b4b4ff'>" . $levelChange['nick'] . "</font> <font color='#beaa78'>主播等级</font> <font color='#8ca0c8'>升为 " . $levelChange['levelName'] . $levelChange['subLevel'] . "级，直播更有动力了呢！</font>";
			$msg = array(
					'group_id' => $uid,
					'content' => array(
							'type' => 0,
							'text' => $summary,
							'msgs' => array(
									0 => array(
											'content' => $text,
									)
							),
							'summary' => $summary
					)
			);

			$tmpKey = "zblevel:$uid" . ":" . time();
			$model_uattr->getRedisMaster()->set($tmpKey, json_encode($msg));

			$url = GlobalConfig::GetSendGrpMsgURL() . $tmpKey;
			$ch = curl_init();
			$curl_opt = array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT_MS => 1000
			);
			curl_setopt_array($ch, $curl_opt);
			$data = curl_exec($ch);
			curl_close($ch);

			$model_glory = new GloryModel();
			$model_glory->anchorCharmAdd($uid, $newLevelInfo['charm'] - $oldLevelInfo['charm']);

		} while (0);

		LogApi::logProcess("evt_deal_api:anchor_level_up_rq rs ");
	}
}
?>