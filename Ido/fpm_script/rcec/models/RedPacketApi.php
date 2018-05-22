<?php
class RedPacketApi
{	
	// 发手气红包
	public static function sendLuckyRedPacket($params)
	{
		LogApi::logProcess("RedPacketApi sendLuckyRedPacket rq:" . json_encode($params));
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$money_total = isset($params['money_total'])?$params['money_total']:0;
		$count_total = isset($params['count_total'])?$params['count_total']:0;
		$summary = isset($params['summary']) ? $params['summary'] :"";
		$tmp_id = isset($params['tmp_id'])?$params['tmp_id']:"";
		
		$rs = array(
				'cmd' => "RSendLuckyRedPacket",
				'result' => 0,
				'uid' => $uid,
				'sid' => $sid,
				'tmp_id' => $tmp_id
		);
		
		$broadcast_data = null;
		
		do {
			$rp_model = new RedPacketModel();
			$rp_model->loadLuckyRedPacketConf();
			// 上行参数检查
			if (empty($uid) || empty($sid) || empty($money_total) || empty($count_total) || empty($tmp_id) || 
					$money_total < RedPacketModel::$CONF_LUCKY_RP_MONEY_LOWER_LIMIT ||
					$money_total > RedPacketModel::$CONF_LUCKY_RP_MONEY_UPPER_LIMIT ||
					$count_total < RedPacketModel::$CONF_LUCKY_RP_COUNT_LOWER_LIMIT ||
					$count_total > RedPacketModel::$CONF_LUCKY_RP_COUNT_UPPER_LIMIT   ) {
						$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
						$rs['errmsg'] = "请求参数错误";
						break;
			}
	
			$res = $rp_model->newRedPacket($sid, $uid, RedPacketModel::TYPE_LUCKY_RP, RedPacketModel::STATUS_ACTIVE, $money_total, $count_total, $summary);
	
			if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				
				$user_attr = new UserAttributeModel();
				$user_attr->cleanCache($uid);
				
				// 发送广播
				$uinfo_model = new UserInfoModel();
				$uinfo = $uinfo_model->getInfoById($uid);
				$item = $res['data'];
				$item['unick'] = $uinfo['nick'];
				$item['uphoto'] = $uinfo['photo'];
					
				// 存储redis
				$rp_id = $item['id'];
				$create_time = $item['create_time'];
				$item['active_time'] = $create_time;
				$item['conf_expire_time'] = RedPacketModel::$CONF_LUCKY_RP_EXPIRE_TIME * 60;
				
				$rp_model->convertRedPacketInf($item);
				
				$rp_model->updateRedPacketInCache($item);
				
				$broadcast_data = array (
						'cmd' => "BNewLuckyRedPacketInChannel"
				);
				
				$broadcast_data = array_merge($broadcast_data, $item);
								
				$rs['money_delta'] = $money_total;
			}
	
			$rs['result'] = $res['code'];
		} while (0);
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		if (!empty($broadcast_data)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $broadcast_data
			);
		}

		if ($rs['result'] == RedPacketModel::$ERR_CODE_SUCCESS) {

			$pmd_data = RedPacketApi::if_pmd($uid, $sid, $money_total, RedPacketModel::TYPE_LUCKY_RP);
			$return[] = array(
				'broadcast' => 4,
				'data' => $pmd_data
			);
		}
		
		LogApi::logProcess("RedPacketApi sendLuckyRedPacket rs:" . json_encode($return));
		return $return;
	}
	
	// 发粉丝红包
	public static function sendFansRedPacket($params)
	{
		LogApi::logProcess("RedPacketApi sendFansRedPacket rq:" . json_encode($params));
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$money_total = isset($params['money_total'])?$params['money_total']:0;
		$count_total = isset($params['count_total'])?$params['count_total']:0;
		$summary = isset($params['summary'])?$params['summary']:"";
		$tmp_id = isset($params['tmp_id'])?$params['tmp_id']:"";
		
		$rs = array(
				'cmd' => "RSendFansRedPacket",
				'result' => 0,
				'uid' => $uid,
				'sid' => $sid,
				'tmp_id' => $tmp_id
		);
		
		$broadcast_data = null;
		$broadcast_linkd = null;
		
		do {
			$rp_model = new RedPacketModel();
			$rp_model->loadFansRedPacketConf();
			
			if (empty($uid) || empty($sid) || empty($money_total) || empty($tmp_id) || 
					$money_total < RedPacketModel::$CONF_FANS_RP_MONEY_LOWER_LIMIT ||
					$money_total > RedPacketModel::$CONF_FANS_RP_MONEY_UPPER_LIMIT) {
						$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
						$rs['errmsg'] = "请求参数错误";
						break;
			}
	
			$res = $rp_model->newRedPacket($sid, $uid, RedPacketModel::TYPE_FANS_RP, RedPacketModel::STATUS_INITIAL, $money_total, 0, $summary);
	
			if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				$user_attr = new UserAttributeModel();
				$user_attr->cleanCache($uid);
				
				// 存储redis
				// 发直播间广播
				// 发linkd广播
				$singer_id = 0;
				$channellive_model = new ChannelLiveModel();
				$channel_info = $channellive_model->getSessionInfo($sid);
				$uinfo_model = new UserInfoModel();
				$uinfo = $uinfo_model->getInfoById($uid);
								
				$item = $res['data'];
				$item['snick'] = "";
				if (!empty($channel_info)) {
					$singer_id = $channel_info['owner'];
					$sinfo = $uinfo_model->getInfoById($singer_id);
					$item['snick'] = $sinfo['nick'];
				}
				
				$item['unick'] = $uinfo['nick'];
				$item['uphoto'] = $uinfo['photo'];
				$item['singer_id'] = $singer_id;
					
				$rp_id = $item['id'];
				$create_time = $item['create_time'];
				$item['active_time'] = 0;
				$item['conf_expire_time'] = RedPacketModel::$CONF_FANS_RP_EXPIRE_TIME * 60;
				$item['conf_active_time'] = RedPacketModel::$CONF_FANS_RP_ACTIVE_TIME * 60;
				$rp_model->convertRedPacketInf($item);
				$rp_model->updateRedPacketInCache($item);
				
				$broadcast_data = array (
						'cmd' => "BNewFansRedPacketInChannel"
				);
				
				$broadcast_data = array_merge($broadcast_data, $item);
				
				// linkd粉丝通知
				$rt = $rp_model->getFansIdsFromDB($uid);
				if ($rt['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
					if (!empty($rt['data'])) {
						$broadcast_linkd = array(
							'broadcast' => 8,
							'uids' => $rt['data'],
							'data' => array_merge($item, array('cmd' => "BNewFansRedPacket"))
								//'uids' => $rt['data'],
								//'data' => array_merge($item, array('cmd' => "BNewFansRedPacket"))
						);
					}
				}
				
				// push 推送
				$url = GlobalConfig::GetRedPacketFansPushURL();
				$unick = $item['unick'];
				$snick = $item['snick'];
				$url .= "?uid=$uid&packId=$rp_id&sid=$sid&unick=$unick&zname=$snick&zid=$singer_id";

				$ch = curl_init();
				$curl_opt = array(
						CURLOPT_URL => $url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT_MS => 1000
				);
				curl_setopt_array($ch, $curl_opt);
				$data = curl_exec($ch);
				curl_close($ch);
				
				$rs['money_delta'] = $money_total;
			}
	
			$rs['result'] = $res['code'];
		} while (0);
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		if (!empty($broadcast_data)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $broadcast_data
			);
		}
		if (!empty($broadcast_linkd)) {
			$return[] = $broadcast_linkd;
			// $return[] = array(
			// 		'broadcast' => 8,		
			// 		'data' => $broadcast_linkd
			// );
			// todo: send push notify.
		}

		if ($rs['result'] == RedPacketModel::$ERR_CODE_SUCCESS) {

			$pmd_data = RedPacketApi::if_pmd($uid, $sid, $money_total, RedPacketModel::TYPE_FANS_RP);
			$return[] = array(
				'broadcast' => 4,
				'data' => $pmd_data
			);
		}
		
		LogApi::logProcess("RedPacketApi sendFansRedPacket rs:" . json_encode($return));
		return $return;
		
	}
	
	// 发分享红包
	public static function sendShareRedPacket($params)
	{
		LogApi::logProcess("RedPacketApi sendShareRedPacket rq:" . json_encode($params));
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$money_total = isset($params['money_total'])?$params['money_total']:0;
		$count_total = isset($params['count_total'])?$params['count_total']:0;
		$summary = isset($params['summary']) ? $params['summary'] :"";
		$tmp_id = isset($params['tmp_id'])?$params['tmp_id']:"";
		
		$rs = array(
				'cmd' => "RSendShareRedPacket",
				'result' => 0,
				'uid' => $uid,
				'sid' => $sid,
				'tmp_id' => $tmp_id
		);
		
		$broadcast_data = null;
		
		do {
			$rp_model = new RedPacketModel();
			$rp_model->loadShareRedPacketConf();
			// 上行参数检查
			if (empty($uid) || empty($sid) || empty($money_total) || empty($count_total) || empty($tmp_id) ||
					$money_total < RedPacketModel::$CONF_SHARE_RP_MONEY_LOWER_LIMIT ||
					$money_total > RedPacketModel::$CONF_SHARE_RP_MONEY_UPPER_LIMIT ||
					$count_total < RedPacketModel::$CONF_SHARE_RP_COUNT_LOWER_LIMIT ||
					$count_total > RedPacketModel::$CONF_SHARE_RP_COUNT_UPPER_LIMIT  ) {
						$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
						$rs['errmsg'] = "请求参数错误";
						break;
			}
	
			$times_shared_max = ($count_total >= 2*RedPacketModel::$CONF_SHARE_RP_ACTIVE_COUNT_BASE)?(floor($count_total/2)):RedPacketModel::$CONF_SHARE_RP_ACTIVE_COUNT_BASE;
			$res = $rp_model->newRedPacket($sid, $uid, RedPacketModel::TYPE_SHARE_RP, RedPacketModel::STATUS_INITIAL, $money_total, $count_total, $summary);
	
			if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				$user_attr = new UserAttributeModel();
				$user_attr->cleanCache($uid);
				
				// 发送广播
				$uinfo_model = new UserInfoModel();
				$uinfo = $uinfo_model->getInfoById($uid);
				$item = $res['data'];
				$item['unick'] = $uinfo['nick'];
				$item['uphoto'] = $uinfo['photo'];
				$item['times_shared_max'] = $times_shared_max;
				
				// 存储redis
				$rp_id = $item['id'];
				$create_time = $item['create_time'];
				$item['active_time'] = 0;
				$item['conf_expire_time'] = RedPacketModel::$CONF_SHARE_RP_EXPIRE_TIME * 60;
				$item['conf_active_expire_time'] = RedPacketModel::$CONF_SHARE_RP_ACTIVE_TIMEOUT * 60;
				$item['times_shared'] = 0;
				
				$rp_model->convertRedPacketInf($item);
				
				$rp_model->updateRedPacketInCache($item);
				
				$broadcast_data = array (
						'cmd' => "BNewShareRedPacketInChannel"
				);
				
				$broadcast_data = array_merge($broadcast_data, $item);
				
				$rs['money_delta'] = $money_total;
			}
	
			$rs['result'] = $res['code'];
		} while (0);
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);

		if (!empty($broadcast_data)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $broadcast_data
			);
		}

		// 判断是否上跑道
		if ($rs['result'] == RedPacketModel::$ERR_CODE_SUCCESS) {

			$pmd_data = RedPacketApi::if_pmd($uid, $sid, $money_total, RedPacketModel::TYPE_SHARE_RP);
			$return[] = array(
				'broadcast' => 4,
				'data' => $pmd_data
			);
		}
		
		LogApi::logProcess("RedPacketApi sendShareRedPacket rs:" . json_encode($return));
		return $return;
	}

	// 发送帮会票红包
	public static function sendGTicketRedPacket($params)
	{		
		LogApi::logProcess("RedPacketApi sendGTicketRedPacket rq:" . json_encode($params));
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$money_total = isset($params['money_total'])?$params['money_total']:0;
		$target_tickets = isset($params['target_tickets'])?$params['target_tickets']:0;
		$summary = isset($params['summary']) ? $params['summary'] :"";
		$tmp_id = isset($params['tmp_id'])?$params['tmp_id']:"";
		$count_total = 0;	// set to zero.

		$rs = array(
				'cmd' => "RSendGTicketRedPacket",
				'result' => 0,
				'uid' => $uid,
				'sid' => $sid,
				'tmp_id' => $tmp_id
		);
		
		$broadcast_data = null;
		
		do {
			$rp_model = new RedPacketModel();
			$rp_model->loadGTicketRedPacketConf();
			// 上行参数检查
			if (empty($uid) || empty($sid) || empty($money_total) || empty($target_tickets) || empty($tmp_id) || 
					$money_total < RedPacketModel::$CONF_GTICKET_RP_MONEY_LOWER_LIMIT ||
					$money_total > RedPacketModel::$CONF_GTICKET_RP_MONEY_UPPER_LIMIT ||
					$target_tickets < RedPacketModel::$CONF_GTICKET_RP_COUNT_LOWER_LIMIT ||
					$target_tickets > RedPacketModel::$CONF_GTICKET_RP_COUNT_UPPER_LIMIT   ) {
						$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
						$rs['errmsg'] = "请求参数错误";
						break;
			}
	
			$res = $rp_model->newRedPacket($sid, $uid, RedPacketModel::TYPE_GTICKET_RP, RedPacketModel::STATUS_ACTIVE, $money_total, $count_total, $summary);
	
			if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				
				$user_attr = new UserAttributeModel();
				$user_attr->cleanCache($uid);
				
				// 发送广播
				$uinfo_model = new UserInfoModel();
				$uinfo = $uinfo_model->getInfoById($uid);
				$item = $res['data'];
				$item['unick'] = $uinfo['nick'];
				$item['uphoto'] = $uinfo['photo'];
					
				// 存储redis
				$rp_id = $item['id'];
				$create_time = $item['create_time'];
				$item['active_time'] = $create_time;
				$item['conf_expire_time'] = RedPacketModel::$CONF_GTICKET_RP_EXPIRE_TIME * 60;
				$item['target_tickets'] = $target_tickets;
				$item['current_tickets'] = 0;
				
				$rp_model->convertRedPacketInf($item);
				
				$rp_model->updateRedPacketInCache($item);
				$rp_model->setGTicketTargetNumber($rp_id, $target_tickets, (RedPacketModel::$CONF_GTICKET_RP_EXPIRE_TIME + 3) * 60);
				
				$broadcast_data = array (
						'cmd' => "BNewGTicketRedPacketInChannel"
				);
				
				$broadcast_data = array_merge($broadcast_data, $item);
								
				$rs['money_delta'] = $money_total;
			}
	
			$rs['result'] = $res['code'];
		} while (0);
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		if (!empty($broadcast_data)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $broadcast_data
			);
		}

		if ($rs['result'] == RedPacketModel::$ERR_CODE_SUCCESS) {

			$pmd_data = RedPacketApi::if_pmd($uid, $sid, $money_total, RedPacketModel::TYPE_GTICKET_RP);
			$return[] = array(
				'broadcast' => 4,
				'data' => $pmd_data
			);
		}
		
		LogApi::logProcess("RedPacketApi sendGTicketRedPacket rs:" . json_encode($return));
		return $return;
	}
	
	// 领取手气红包
	public static function pickLuckyRedPacket($params)
	{
		LogApi::logProcess("RedPacketApi pickLuckyRedPacket rq:" . json_encode($params));
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$rp_id = isset($params['rp_id'])?$params['rp_id']:0;
		
		$rs = array(
				'cmd' => "RPickLuckyRedPacket",
				'result' => RedPacketModel::$ERR_CODE_SUCCESS,
				'uid' => $uid,
				'sid' => $sid,
				'rp_id' => $rp_id
		);
		
		$broadcast_data = null;
		$money_picked = 0;
		$list_pick_items = null;
		$pick_item = null;
		$inf_rp = null;
		
		$rp_model = new RedPacketModel();
		$uinfo_model = new UserInfoModel();
		
		do {
			if (empty($uid) || empty($sid) || empty($rp_id)) {
				$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
				$rs['errmsg'] = "请求参数错误";
				break;
			}
			
			// 首先从redis或数据库中判断是否领取过
			// 如果领取过了，则查询当前红包信息，及领取列表信息，直接返回
			// 否则走领取流程
			
			$rp_model->loadLuckyRedPacketConf();
			$inf_rp = $rp_model->getRedPacketRecordFromDB($rp_id);
			$pick_item = $rp_model->getRedPacketPickRecord($uid, $rp_id);
			
// 			if (empty($pick_item)) {
// 				if ($inf_rp['status'] != RedPacketModel::STATUS_ACTIVE) {
// 					$rs['result'] = $rp_model->statusErrcode($inf_rp['status']);
// 					break;
// 				}
// 			} else {
// 				$inf_rp['flag'] = 1;
// 				$pick_item_res = $rp_model->getPickItems(0, $rp_id, 20);
// 				if ($pick_item_res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
// 					$list_pick_items = $pick_item_res['data']['list_pick_items'];
// 				}
// 				$money_picked = $pick_item['money_picked'];
// 				break;
// 			}
			
			$pick_item_res = $rp_model->getPickItems(0, $rp_id, 20);
			if ($pick_item_res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				$list_pick_items = $pick_item_res['data']['list_pick_items'];
			}
			
			if (!empty($pick_item)) {
				$inf_rp['flag'] = 1;
				$money_picked = $pick_item['money_picked'];
				break;
			}
			
			if ($inf_rp['status'] != RedPacketModel::STATUS_ACTIVE) {
				$rs['result'] = $rp_model->statusErrcode($inf_rp['status']);
				break;
			}
			
			$lock_res = $rp_model->pickLock($uid, $rp_id);

			if (!$lock_res['ok']) {
				LogApi::logProcess("RedPacketApi pickLuckyRedPacket fetch lock failure. uid:$uid rp_id:$rp_id");
				break;
			}
			
			$res = $rp_model->pickRedPacket($uid, $sid, $rp_id, RedPacketModel::$CONF_LUCKY_RP_TAX, RedPacketModel::$CONF_LUCKY_RP_DUTY_FREE);
			$inf_rp = $res['data']['inf_rp'];
			
			if (!empty($inf_rp['sid'])) {
				$sid = intval($inf_rp['sid']);
			}
			
			if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				
				$user_attr = new UserAttributeModel();
				$user_attr->cleanCache($uid);
				
				if ($inf_rp['status'] == RedPacketModel::STATUS_END) {
					// 发送变更通知
					// 清除redis缓存
					
					$rp_model->clearRedPacketCache($sid, $rp_id);
						
					$broadcast_data = array(
							'cmd' => "BRedPacketStatusChange",
							'list_lucky_rps' => array(
									0 => $inf_rp
							)
					);
				} else if ($inf_rp['status'] == RedPacketModel::STATUS_ACTIVE) {
					$rp_model->pickRecordCache($rp_id, $uid);	
				}
				$inf_rp['flag'] = 1;
			}
			
			$pick_item = $res['data']['pick_item'];
			$money_picked = $res['data']['money_picked'];
			$rs['result'] = $res['code'];
			
			$pick_id = 0;
			if (!empty($pick_item)) {
				$pick_id = $pick_item['id'];
			}
			
			$pick_item_res = $rp_model->getPickItems($pick_id, $rp_id, 20, true);
			if ($pick_item_res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				$list_pick_items = $pick_item_res['data']['list_pick_items'];
			}
			
			$rp_model->pickUnLock($uid, $rp_id, $lock_res['rd']);
		} while(0);
		
		if (!empty($inf_rp)) {
			$uinfo = $uinfo_model->getInfoById($inf_rp['uid']);
			$inf_rp['unick'] = $uinfo['nick'];
			$inf_rp['uphoto'] = $uinfo['photo'];
			$inf_rp['conf_expire_time'] = RedPacketModel::$CONF_LUCKY_RP_EXPIRE_TIME * 60;
		}
		
		if (!empty($list_pick_items)) {
			for ($i=0; $i<count($list_pick_items); ++$i) {
				$uid = $list_pick_items[$i]['uid'];
				$uinfo = $uinfo_model->getInfoById($uid);
				$list_pick_items[$i]['unick'] = $uinfo['nick'];
				$list_pick_items[$i]['uphoto'] = $uinfo['photo'];
			}
		}
		
		$rp_model->convertRedPacketInf($inf_rp);
		$rs['money_picked'] = $money_picked;
		$rs['list_pick_items'] = $list_pick_items;
		$rs['inf_rp'] = $inf_rp;
			
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		if (!empty($broadcast_data)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $broadcast_data
			);
		}
		
		LogApi::logProcess("RedPacketApi pickLuckyRedPacket rs:" . json_encode($return));
		return $return;
	}
	
	// 领取粉丝红包
	public static function pickFansRedPacket($params)
	{
		LogApi::logProcess("RedPacketApi pickFansRedPacket rq:" . json_encode($params));
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$rp_id = isset($params['rp_id'])?$params['rp_id']:0;
		
		$rs = array(
				'cmd' => "RPickFansRedPacket",
				'result' => RedPacketModel::$ERR_CODE_SUCCESS,
				'uid' => $uid,
				'sid' => $sid,
				'rp_id' => $rp_id
		);
		
		$broadcast_data = null;
		$money_picked = 0;
		$list_pick_items = null;
		$pick_item = null;
		$inf_rp = null;
		
		$rp_model = new RedPacketModel();
		$uinfo_model = new UserInfoModel();
		
		do {
			if (empty($uid) || empty($sid) || empty($rp_id)) {
				$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
				$rs['errmsg'] = "请求参数错误";
				break;
			}
			
			// 判断是否是粉丝

			$inf_rp = $rp_model->getRedPacketRecordFromDB($rp_id);
			if (empty($inf_rp)) {
				$rs['result'] = RedPacketModel::$ERR_CODE_UNKNOWN;
				LogApi::logProcess("RedPacketApi pickFansRedPacket getRedPacketRecordFromDB failure. rp_id:$rp_id");
				break;
			}
			
			if ($uid != $inf_rp['uid']) {
				$inf_fans = $rp_model->getFansInfFromDB($inf_rp['uid'], $uid);
				if (empty($inf_fans) || intval($inf_fans['type']) != 1) {
					$rs['result'] = RedPacketModel::$ERR_CODE_NOT_FANS;
					break;
				} else if ($inf_fans['update_time'] > $inf_rp['create_time']) {
					$rs['result'] = RedPacketModel::$ERR_CODE_FANS_LATE;
					break;
				}
			}
			
			$rp_model->loadFansRedPacketConf();
// 			$inf_rp = $rp_model->getRedPacketRecordFromDB($rp_id);
			$pick_item = $rp_model->getRedPacketPickRecord($uid, $rp_id);
			
// 			if (empty($pick_item)) {
// 				if ($inf_rp['status'] != RedPacketModel::STATUS_ACTIVE) {
// 					$rs['result'] = $rp_model->statusErrcode($inf_rp['status']);
// 					break;
// 				}
// 			} else {
// 				$pick_item_res = $rp_model->getPickItems(0, $rp_id, 20);
// 				if ($pick_item_res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
// 					$list_pick_items = $pick_item_res['data']['list_pick_items'];
// 				}
// 				$money_picked = $pick_item['money_picked'];
// 				$inf_rp['flag'] = 1;
// 				break;
// 			}
			
			$pick_item_res = $rp_model->getPickItems(0, $rp_id, 20);
			if ($pick_item_res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				$list_pick_items = $pick_item_res['data']['list_pick_items'];
			}
			
			if (!empty($pick_item)) {
				$inf_rp['flag'] = 1;
				$money_picked = $pick_item['money_picked'];
				break;
			}
			
			if ($inf_rp['status'] != RedPacketModel::STATUS_ACTIVE) {
				$rs['result'] = $rp_model->statusErrcode($inf_rp['status']);
				break;
			}
			
			$lock_res = $rp_model->pickLock($uid, $rp_id);
			if (!$lock_res['ok']) {
				LogApi::logProcess("RedPacketApi pickFansRedPacket fetch lock failure. uid:$uid rp_id:$rp_id");
				break;
			}
				
			$res = $rp_model->pickRedPacket($uid, $sid, $rp_id, RedPacketModel::$CONF_FANS_RP_TAX, RedPacketModel::$CONF_FANS_RP_DUTY_FREE);
			$inf_rp = $res['data']['inf_rp'];
			
			if (!empty($inf_rp['sid'])) {
				$sid = intval($inf_rp['sid']);
			}
			
			if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				
				$user_attr = new UserAttributeModel();
				$user_attr->cleanCache($uid);
				
				if ($inf_rp['status'] == RedPacketModel::STATUS_END) {
					// 发送变更通知
					// 清除redis缓存
					
					$rp_model->clearRedPacketCache($sid, $rp_id);
					$rp_model->clearFansRedPacketCache($rp_id);
					
					$broadcast_data = array(
							'cmd' => "BRedPacketStatusChange",
							'list_fans_rps' => array(
									0 => $inf_rp
							)
					);
				} else if ($inf_rp['status'] == RedPacketModel::STATUS_ACTIVE) {
					$rp_model->pickRecordCache($rp_id, $uid);
				}
				$inf_rp['flag'] = 1;
			}
			
			$pick_item = $res['data']['pick_item'];
			$money_picked = $res['data']['money_picked'];
			$rs['result'] = $res['code'];
			
			$pick_id = 0;
			if (!empty($pick_item)) {
				$pick_id = $pick_item['id'];
			}
				
			$pick_item_res = $rp_model->getPickItems($pick_id, $rp_id, 20, true);
			
			if ($pick_item_res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				$list_pick_items = $pick_item_res['data']['list_pick_items'];
			}
			
			$rp_model->pickUnLock($uid, $rp_id, $lock_res['rd']);
		} while (0);
		
		if (!empty($inf_rp)) {
			$uinfo = $uinfo_model->getInfoById($inf_rp['uid']);
			$inf_rp['unick'] = $uinfo['nick'];
			$inf_rp['uphoto'] = $uinfo['photo'];
			$inf_rp['conf_expire_time'] = RedPacketModel::$CONF_FANS_RP_EXPIRE_TIME * 60;
			$inf_rp['conf_active_time'] = RedPacketModel::$CONF_FANS_RP_ACTIVE_TIME * 60;
		}
		
		if (!empty($list_pick_items)) {
			for ($i=0; $i<count($list_pick_items); ++$i) {
				$uid = $list_pick_items[$i]['uid'];
				$uinfo = $uinfo_model->getInfoById($uid);
				$list_pick_items[$i]['unick'] = $uinfo['nick'];
				$list_pick_items[$i]['uphoto'] = $uinfo['photo'];
			}
		}
		
		$rp_model->convertRedPacketInf($inf_rp);
		$rs['money_picked'] = $money_picked;
		$rs['list_pick_items'] = $list_pick_items;
		$rs['inf_rp'] = $inf_rp;
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
			
		if (!empty($broadcast_data)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $broadcast_data
			);
		}
		
		LogApi::logProcess("RedPacketApi pickFansRedPacket rs:" . json_encode($return));
		return $return;
	}
	
	// 领取分享红包
	public static function pickSharePacket($params)
	{
		LogApi::logProcess("RedPacketApi pickSharePacket rq:" . json_encode($params));
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$rp_id = isset($params['rp_id'])?$params['rp_id']:0;
		
		$rs = array(
				'cmd' => "RPickShareRedPacket",
				'result' => RedPacketModel::$ERR_CODE_SUCCESS,
				'uid' => $uid,
				'sid' => $sid,
				'rp_id' => $rp_id
		);
		
		$broadcast_data = null;
		$money_picked = 0;
		$list_pick_items = null;
		$pick_item = null;
		$inf_rp = null;
		
		$rp_model = new RedPacketModel();
		$uinfo_model = new UserInfoModel();
		
		do {
			if (empty($uid) || empty($sid) || empty($rp_id)) {
				$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
				$rs['errmsg'] = "请求参数错误";
				break;
			}
				
			$rp_model->loadShareRedPacketConf();
			$inf_rp = $rp_model->getRedPacketRecordFromDB($rp_id);
			$pick_item = $rp_model->getRedPacketPickRecord($uid, $rp_id);
				
// 			if (empty($pick_item)) {
// 				if ($inf_rp['status'] != RedPacketModel::STATUS_ACTIVE) {
// 					$rs['result'] = $rp_model->statusErrcode($inf_rp['status']);
// 					break;
// 				}
// 			} else {
// 				$pick_item_res = $rp_model->getPickItems(0, $rp_id, 20);
// 				if ($pick_item_res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
// 					$list_pick_items = $pick_item_res['data']['list_pick_items'];
// 				}
// 				$money_picked = $pick_item['money_picked'];
// 				$inf_rp['flag'] = 1;
// 				break;
// 			}
			
			$pick_item_res = $rp_model->getPickItems(0, $rp_id, 20);
			if ($pick_item_res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				$list_pick_items = $pick_item_res['data']['list_pick_items'];
			}
				
			if (!empty($pick_item)) {
				$inf_rp['flag'] = 1;
				$money_picked = $pick_item['money_picked'];
				break;
			}
				
			if ($inf_rp['status'] != RedPacketModel::STATUS_ACTIVE) {
				$rs['result'] = $rp_model->statusErrcode($inf_rp['status']);
				break;
			}
			
			$lock_res = $rp_model->pickLock($uid, $rp_id);
			if (!$lock_res['ok']) {
				LogApi::logProcess("RedPacketApi pickSharePacket fetch lock failure. uid:$uid rp_id:$rp_id");
				break;
			}
				
			$res = $rp_model->pickRedPacket($uid, $sid, $rp_id, RedPacketModel::$CONF_SHARE_RP_TAX, RedPacketModel::$CONF_SHARE_RP_DUTY_FREE);
			$inf_rp = $res['data']['inf_rp'];
			if (!empty($inf_rp['sid'])) {
				$sid = intval($inf_rp['sid']);
			}
			if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				
				$user_attr = new UserAttributeModel();
				$user_attr->cleanCache($uid);
				
				if ($inf_rp['status'] == RedPacketModel::STATUS_END) {
					// 发送变更通知
					// 清除redis缓存
					
					$rp_model->clearRedPacketCache($sid, $rp_id);
					$rp_model->clearShareRedPacketCache($rp_id);
					
					$broadcast_data = array(
							'cmd' => "BRedPacketStatusChange",
							'list_share_rps' => array(
									0 => $inf_rp
							)
					);
				} else if ($inf_rp['status'] == RedPacketModel::STATUS_ACTIVE) {
					$rp_model->pickRecordCache($rp_id, $uid);
				}
				$inf_rp['flag'] = 1;
			}

			$pick_item = $res['data']['pick_item'];
			$money_picked = $res['data']['money_picked'];
			$rs['result'] = $res['code'];
			
			$pick_id = 0;
			if (!empty($pick_item)) {
				$pick_id = $pick_item['id'];
			}
			
			$pick_item_res = $rp_model->getPickItems($pick_id, $rp_id, 20, true);
			if ($pick_item_res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				$list_pick_items = $pick_item_res['data']['list_pick_items'];
			}
			
			$rp_model->pickUnLock($uid, $rp_id, $lock_res['rd']);
				
		} while(0);
		
		if (!empty($inf_rp)) {
			$uinfo = $uinfo_model->getInfoById($inf_rp['uid']);
			$inf_rp['unick'] = $uinfo['nick'];
			$inf_rp['uphoto'] = $uinfo['photo'];
			$inf_rp['conf_expire_time'] = RedPacketModel::$CONF_SHARE_RP_EXPIRE_TIME * 60;
			$inf_rp['conf_active_expire_time'] = RedPacketModel::$CONF_SHARE_RP_ACTIVE_TIMEOUT * 60;
		}
		
		if (!empty($list_pick_items)) {
			for ($i=0; $i<count($list_pick_items); ++$i) {
				$uid = $list_pick_items[$i]['uid'];
				$uinfo = $uinfo_model->getInfoById($uid);
				$list_pick_items[$i]['unick'] = $uinfo['nick'];
				$list_pick_items[$i]['uphoto'] = $uinfo['photo'];
			}
		}
		
		$rp_model->convertRedPacketInf($inf_rp);
		$rs['money_picked'] = $money_picked;
		$rs['list_pick_items'] = $list_pick_items;
		$rs['inf_rp'] = $inf_rp;
			
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		if (!empty($broadcast_data)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $broadcast_data
			);
		}
		
		LogApi::logProcess("RedPacketApi pickSharePacket rs:" . json_encode($return));
		return $return;
	}

	public static function pickGTicketRedPacket($params)
	{
		LogApi::logProcess("RedPacketApi pickGTicketRedPacket rq:" . json_encode($params));
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$rp_id = isset($params['rp_id'])?$params['rp_id']:0;
		
		$rs = array(
				'cmd' => "RPickGTicketRedPacket",
				'result' => RedPacketModel::$ERR_CODE_SUCCESS,
				'uid' => $uid,
				'sid' => $sid,
				'rp_id' => $rp_id
		);
		
		$broadcast_data = null;
		$money_picked = 0;
		$list_pick_items = null;
		$pick_item = null;
		$inf_rp = null;
		
		$rp_model = new RedPacketModel();
		$uinfo_model = new UserInfoModel();
		
		do {
			if (empty($uid) || empty($sid) || empty($rp_id)) {
				$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
				$rs['errmsg'] = "请求参数错误";
				break;
			}
				
			$rp_model->loadGTicketRedPacketConf();
			$inf_rp = $rp_model->getRedPacketRecordFromDB($rp_id);
			$pick_item = $rp_model->getRedPacketPickRecord($uid, $rp_id);
			
			$pick_item_res = $rp_model->getPickItems(0, $rp_id, 20);
			if ($pick_item_res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				$list_pick_items = $pick_item_res['data']['list_pick_items'];
			}
			
			$inf_rp['flag'] = 0;

			if (!empty($pick_item)) {
				$inf_rp['flag'] = 1;
				$money_picked = $pick_item['money_picked'];
			}
				
			if ($inf_rp['status'] != RedPacketModel::STATUS_ACTIVE) {
				$rs['result'] = $rp_model->statusErrcode($inf_rp['status']);
				break;
			}
			
			$lock_res = $rp_model->pickLock($uid, $rp_id);
			if (!$lock_res['ok']) {
				LogApi::logProcess("RedPacketApi pickGTicketRedPacket fetch lock failure. uid:$uid rp_id:$rp_id");
				break;
			}

			// 用户贡献帮会票数量
			$gnumber = 0;

			// 目标帮会票数量
			$ticket_target = 0;
			do {
				// 获取用户赠送帮会票数量
				$gnumber = $rp_model->getGTicketByUser($rp_id, $uid);
				if (empty($gnumber)) {
					if ($inf_rp['flag'] != 1) {
						$rs['result'] = RedPacketModel::$ERR_CODE_GTICKET_NO_CONTRIBUTE;
					}
					break;
				}

				$ticket_target = $rp_model->getGTicketTargetNumber($rp_id);
				if (empty($ticket_target)) {
					if ($inf_rp['flag'] != 1) {
						$rs['result'] = RedPacketModel::$ERR_CODE_GTICKET_NO_CONTRIBUTE;
					}
					break;
				}

				$res = $rp_model->pickGTicketRedPacket($uid, $sid, $rp_id, $gnumber, $ticket_target, RedPacketModel::$CONF_GTICKET_RP_TAX, RedPacketModel::$CONF_GTICKET_RP_DUTY_FREE);
				$inf_rp = $res['data']['inf_rp'];
				if (!empty($inf_rp['sid'])) {
					$sid = intval($inf_rp['sid']);
				}
				if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
					
					$user_attr = new UserAttributeModel();
					$user_attr->cleanCache($uid);
					$rp_model->consumeGTicket($rp_id, $uid);

					if ($inf_rp['status'] == RedPacketModel::STATUS_END) {
						// 发送变更通知
						// 清除redis缓存
						
						$rp_model->clearRedPacketCache($sid, $rp_id);
						$rp_model->clearShareRedPacketCache($rp_id);
						$rp_model->clearGTicketUserContributed($rp_id);
						$rp_model->clearToalGTickets($rp_id);
						
						$broadcast_data = array(
								'cmd' => "BRedPacketStatusChange",
								'list_gticket_rps' => array(
										0 => $inf_rp
								)
						);
					} else if ($inf_rp['status'] == RedPacketModel::STATUS_ACTIVE) {
						$rp_model->pickRecordCache($rp_id, $uid);
					}
					$inf_rp['flag'] = 1;
				}

				$pick_item = $res['data']['pick_item'];
				$money_picked = $res['data']['money_picked'];
				$rs['result'] = $res['code'];
				if ($inf_rp['flag'] != 1) {
					$rs['result'] = RedPacketModel::$ERR_CODE_GTICKET_NO_CONTRIBUTE;
				}
				
				$pick_id = 0;
				if (!empty($pick_item)) {
					$pick_id = $pick_item['id'];
				}
				
				$pick_item_res = $rp_model->getPickItems($pick_id, $rp_id, 20, true);
				if ($pick_item_res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
					$list_pick_items = $pick_item_res['data']['list_pick_items'];
				}
			} while (0);

			$rp_model->pickUnLock($uid, $rp_id, $lock_res['rd']);
				
		} while(0);
		
		if (!empty($inf_rp)) {
			$uinfo = $uinfo_model->getInfoById($inf_rp['uid']);
			$inf_rp['unick'] = $uinfo['nick'];
			$inf_rp['uphoto'] = $uinfo['photo'];
			$inf_rp['conf_expire_time'] = RedPacketModel::$CONF_GTICKET_RP_EXPIRE_TIME * 60;
		}
		
		if (!empty($list_pick_items)) {
			for ($i=0; $i<count($list_pick_items); ++$i) {
				$uid = $list_pick_items[$i]['uid'];
				$uinfo = $uinfo_model->getInfoById($uid);
				$list_pick_items[$i]['unick'] = $uinfo['nick'];
				$list_pick_items[$i]['uphoto'] = $uinfo['photo'];
			}
		}
		
		$rp_model->convertRedPacketInf($inf_rp);
		$rs['money_picked'] = $money_picked;
		$rs['list_pick_items'] = $list_pick_items;
		$rs['inf_rp'] = $inf_rp;
			
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		if (!empty($broadcast_data)) {
			$return[] = array(
					'broadcast' => 1,
					'data' => $broadcast_data
			);
		}
		
		LogApi::logProcess("RedPacketApi pickGTicketRedPacket rs:" . json_encode($return));
		return $return;
	}
	
	// 获取直播间红包列表
	public static function getRedPacketList($params)
	{
		LogApi::logProcess("RedPacketApi getRedPacketList rq:" . json_encode($params));
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$singer_id = isset($params['singer_id'])?$params['singer_id']:0;
	
		$rs = array(
				'cmd' => "RGetRedPacketList",
				'result' => 0,
				'uid' => $uid,
				'sid' => $sid,
				'singer_id' => $singer_id
		);
		
		$list_lucky_rps = array();
		$list_fans_rps = array();
		$list_share_rps = array();
		$list_gticket_rps = array();
		do {
			if (empty($uid) || empty($sid) || empty($singer_id)) {
				$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
				break;
			}
			
			$rp_model = new RedPacketModel();
			$list_rps = $rp_model->getRedPacketListFromCache($sid);
			foreach ($list_rps as $rp) {
				$rp = json_decode($rp, true);
				if ($rp_model->bPicked($rp['id'], $uid)) {
					$rp['flag'] = 1;
				} else {
					$rp['flag'] = 0;
				}
				
				$rp_model->convertRedPacketInf($rp);
				
				if ($rp['type'] == RedPacketModel::TYPE_LUCKY_RP) {
					array_push($list_lucky_rps, $rp);
				} elseif ($rp['type'] == RedPacketModel::TYPE_SHARE_RP) {
					$time_shared = $rp_model->updateShareRedPacketActiveCdtInCache($rp['id'], 0);
					$time_shared = $time_shared > $rp['times_shared_max']?$rp['times_shared_max']:$time_shared;
					$rp['times_shared'] = intval($time_shared);
					array_push($list_share_rps, $rp);
				} elseif ($rp['type'] == RedPacketModel::TYPE_FANS_RP) {
					array_push($list_fans_rps, $rp);
				} elseif ($rp['type'] == RedPacketModel::TYPE_GTICKET_RP) {
					array_push($list_gticket_rps, $rp);
				}
			}
		} while (0);
		
		if (!empty($list_lucky_rps)) {
			$rs['list_lucky_rps'] = $list_lucky_rps;
		}
		if (!empty($list_fans_rps)) {
			$rs['list_fans_rps'] = $list_fans_rps;
		}
		if (!empty($list_share_rps)) {
			$rs['list_share_rps'] = $list_share_rps;
		}
		if (!empty($list_gticket_rps)) {
			$rs['list_gticket_rps'] = $list_gticket_rps;
		}
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		LogApi::logProcess("RedPacketApi getRedPacketList rs:" . json_encode($return));
		return $return;
	}
	
	// 获取红包领取列表
	public static function getRedPacketPickItems($params)
	{
		LogApi::logProcess("RedPacketApi getRedPacketPickItems rq:" . json_encode($params));
		$uid = isset($params['uid'])?$params['uid']:0;
		$rp_id = isset($params['rp_id'])?$params['rp_id']:0;
		$item_id = isset($params['item_id'])?$params['item_id']:0;
		$size = isset($params['size'])?$params['size']:0;
		
		$rs = array(
				'cmd' => "RGetRedPacketPickItems",
				'result' => RedPacketModel::$ERR_CODE_SUCCESS,
				'uid' => $uid,
				'rp_id' => $rp_id
				
		);
		
		$list_pick_items = null;
		
		do {
			if (empty($uid) || empty($rp_id) || empty($size)) {
				$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
				break;
			}
			$rp_model = new RedPacketModel();
			$res = $rp_model->getPickItems($item_id, $rp_id, $size);
			$rs['result'] = $res['code'];
			if (!empty($res['data']) && !empty($res['data']['list_pick_items'])) {
				$list_pick_items = $res['data']['list_pick_items'];
			}
		} while (0);
		
		if (!empty($list_pick_items)) {
			$uinfo_model = new UserInfoModel();
			for ($i=0; $i<count($list_pick_items); ++$i) {
				$uid = $list_pick_items[$i]['uid'];
				$uinfo = $uinfo_model->getInfoById($uid);
				$list_pick_items[$i]['unick'] = $uinfo['nick'];
				$list_pick_items[$i]['uphoto'] = $uinfo['photo'];
			}
		}
		$rs['list_pick_items'] = $list_pick_items;
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		LogApi::logProcess("RedPacketApi getRedPacketPickItems rs:" . json_encode($return));
		return $return;
	}
	
	public static function shareSuccess($params)
	{
		LogApi::logProcess("RedPacketApi shareSuccess rq:" . json_encode($params));
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$singer_id = isset($params['singer_id'])?$params['singer_id']:0;
		
		$rs = array(
				'cmd' => "RShareSuccess",
				'result' => RedPacketModel::$ERR_CODE_SUCCESS,
				'uid' => $uid,
				'sid' => $sid,
				'singer_id' => $singer_id
		);

		$list_share_red_packet = array();
		
		$rp_model = new RedPacketModel();
		
		$list_rps = $rp_model->getRedPacketListFromCache($sid);
		
		$i = 0;
		foreach ($list_rps as $rp) {
			$rp = json_decode($rp, true);
			if ($rp['type'] == RedPacketModel::TYPE_SHARE_RP && $rp['status'] == RedPacketModel::STATUS_INITIAL) {
				$rt = $rp_model->addShareRedPacketShareUid($rp['id'], $uid);
				if (intval($rt) == 1) {
					$time_shared = $rp_model->updateShareRedPacketActiveCdtInCache($rp['id'], 1);
					if ($time_shared == $rp['times_shared_max']) {
						$res_active = $rp_model->shareRedPacketActive($rp['id'], 0);
						if ($res_active['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
							$inf_rp = $res_active['data'];
							$rp['status'] = $inf_rp['status'];
							$rp['active_time'] = time();
							$rp_model->updateRedPacketInCache($rp);
						} else {
							$rp_model->updateShareRedPacketActiveCdtInCache($rp['id'], -1);
						}
					}
					$time_shared = $time_shared > $rp['times_shared_max']?$rp['times_shared_max']:$time_shared;
					$rp['times_shared'] = intval($time_shared);
					
					$list_share_red_packet[$i++] = $rp;
				}
			}
		}
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		if (count($list_share_red_packet) > 0) {
			$return[] = array (
					'broadcast' => 1,
					'data' => array(
							'cmd' => "BRedPacketStatusChange",
							'list_share_rps' => $list_share_red_packet
					)
			);
		}
		
		LogApi::logProcess("RedPacketApi shareSuccess rs:" . json_encode($return));
		return $return;
	}
	
	public static function heartBeatEvent($sid, $time)
	{
		LogApi::logProcess("RedPacketApi heartBeatEvent. sid:$sid time:$time");
		$list_lucky_rps = array();
		$list_fans_rps = array();
		$list_share_rps = array();
		$list_gticket_rps = array();
		$return = array();
		
		$now = time();
		$rp_model = new RedPacketModel();
		$list_rps = $rp_model->getRedPacketListFromCache($sid);
		foreach ($list_rps as $rp) {
			
			LogApi::logProcess("RedPacketApi heartBeatEvent json:" . $rp);
			$rp = json_decode($rp, true);
			$rp_id = $rp['id'];
			$rp_type = $rp['type'];
			$rp_status = $rp['status'];
			$uid = $rp['uid'];
			$create_time = $rp['create_time'];
			
			if ($rp_status == RedPacketModel::STATUS_ACTIVE) {
				$active_time = $rp['active_time'];
				$conf_expire_time = $rp['conf_expire_time'];
				
				if ($active_time + $conf_expire_time < $time) {
					$res = $rp_model->redPacketExpire($rp_id);
					if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
						$inf_rp = $res['data'];
						
						$rp_model->clearRedPacketCache($sid, $rp_id);
						if ($rp_type == RedPacketModel::TYPE_SHARE_RP) {
							$rp_model->clearShareRedPacketCache($rp_id);
						} else if ($rp_type == RedPacketModel::TYPE_FANS_RP) {
							$rp_model->clearFansRedPacketCache($rp_id);
						} else if ($rp_type == RedPacketModel::TYPE_GTICKET_RP) {
							$rp_model->clearGTicketUserContributed($rp_id);
							$rp_model->clearGTicketTargetNumber($rp_id);
						}
						
						$rp['status'] = $inf_rp['status'];
						$rp['last_uptime'] = $inf_rp['last_uptime'];
						$rp['money_picked'] = $inf_rp['money_picked'];
						$rp['count_picked'] = $inf_rp['count_picked'];
						
						$rp_model->convertRedPacketInf($rp);
						
						if ($rp_type == RedPacketModel::TYPE_LUCKY_RP) {
							array_push($list_lucky_rps, $rp);
						} else if ($rp_type == RedPacketModel::TYPE_FANS_RP) {
							array_push($list_fans_rps, $rp);
						} else if ($rp_type == RedPacketModel::TYPE_SHARE_RP) {
							array_push($list_share_rps, $rp);
						} else if ($rp_type == RedPacketModel::TYPE_GTICKET_RP) {
							array_push($list_gticket_rps, $rp);
						}
						
						$sys_msg = $rp_model->formatRedPacketSysMsg($rp, $rp_status);
						if (!empty($sys_msg)) {
							$rp_model->sendSysMsg($uid, $rp_id, $sys_msg);
						}
					}
				}
			} else if ($rp_type == RedPacketModel::TYPE_SHARE_RP && $rp_status == RedPacketModel::STATUS_INITIAL) {
				$conf_active_expire_time = $rp['conf_active_expire_time'];
				if ($create_time + $conf_active_expire_time < $time) {
					$res = $rp_model->redPacketExpire($rp_id, RedPacketModel::STATUS_ACTIVE_FAIL);
					if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
						$inf_rp = $res['data'];
						
						$time_shared = $rp_model->updateShareRedPacketActiveCdtInCache($rp_id, 0);
						$time_shared = $time_shared > $rp['times_shared_max']?$rp['times_shared_max']:$time_shared;
						
						$rp_model->clearRedPacketCache($sid, $rp_id);
						$rp_model->clearShareRedPacketCache($rp_id);
						
						$rp['status'] = $inf_rp['status'];
						$rp['last_uptime'] = $inf_rp['last_uptime'];
						$rp['money_picked'] = $inf_rp['money_picked'];
						$rp['count_picked'] = $inf_rp['count_picked'];
						$rp['times_shared'] = $time_shared;
						
						$rp_model->convertRedPacketInf($rp);
						
						array_push($list_share_rps, $rp);
						
						$sys_msg = $rp_model->formatRedPacketSysMsg($rp, $rp_status);
						if (!empty($sys_msg)) {
							$rp_model->sendSysMsg($uid, $rp_id, $sys_msg);
						}
					}
				}
			} else if ($rp_type == RedPacketModel::TYPE_FANS_RP && $rp_status == RedPacketModel::STATUS_INITIAL) {
				$conf_active_time = $rp['conf_active_time'];
				if ($conf_active_time + $create_time <= $time) {
					// 粉丝红包激活
					$size = $rp_model->updateFansEnrollCount($rp_id, 0);
					$size = isset($size)?$size:0;
					
					$rp_model->loadFansRedPacketConf();
					$size = ($size<RedPacketModel::$CONF_FANS_RP_COUNT_BASE?RedPacketModel::$CONF_FANS_RP_COUNT_BASE:$size);
					$size = $size>$rp['money_total']?$rp['money_total']:$size;
					
					$res = $rp_model->fansRedPacketActive($rp['id'], $size);
					if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
						$inf_rp = $res['data'];
						$rp['status'] = $inf_rp['status'];
						$rp['active_time'] = $now;
						$rp['last_uptime'] = $now;
						$rp['count_total'] = $size;
						
						$rp_model->convertRedPacketInf($rp);
						
						$rp_model->updateRedPacketInCache($rp);
						array_push($list_fans_rps, $rp);
					}
				}
			}
		}
		
		$data = array();
		if (!empty($list_lucky_rps)) {
			$data['list_lucky_rps'] = $list_lucky_rps;
		}
		
		if (!empty($list_fans_rps)) {
			$data['list_fans_rps'] = $list_fans_rps;
		}
		
		if (!empty($list_share_rps)) {
			$data['list_share_rps'] = $list_share_rps;
		}
		
		if (!empty($list_gticket_rps)) {
			$data['list_gticket_rps'] = $list_gticket_rps;
		}

		if (!empty($data)) {
			$data['cmd'] = "BRedPacketStatusChange";
			$data['isRoom'] = true;
			$return['data'] = $data;				
			$return['broadcast'] = 1;
		}
		
		if (!empty($return)) {
			LogApi::logProcess("RedPacketApi heartBeatEvent rs:" . json_encode($return));
		}
		
		return $return;
	}
	
	public static function singerLeaveEvent($sid)
	{
		LogApi::logProcess("RedPacketApi singerLeaveEvent");
		$rp_model = new RedPacketModel();
		$list_rps = $rp_model->getRedPacketListFromCache($sid);
		
		foreach ($list_rps as $rp) {
			LogApi::logProcess("RedPacketApi singerLeaveEvent cache json:" . $rp);
			$rp = json_decode($rp, true);
			$rp_id = $rp['id'];
			$uid = $rp['uid'];
			$status = $rp['status'];
			$res = $rp_model->redPacketExpire($rp_id);
			LogApi::logProcess("RedPacketApi singerLeaveEvent res:" . json_encode($res));
				
			if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				$inf_rp = $res['data'];
				
				$rp['status'] = $inf_rp['status'];
				$rp['last_uptime'] = $inf_rp['last_uptime'];
				$rp['money_picked'] = $inf_rp['money_picked'];
				$rp['count_picked'] = $inf_rp['count_picked'];
				
				$rp_model->clearRedPacketCache($sid, $rp_id);
				
				if ($rp['type'] == RedPacketModel::TYPE_SHARE_RP) {
					$rp_model->clearShareRedPacketCache($rp_id);
					
				} else if ($rp['type'] == RedPacketModel::TYPE_FANS_RP) {
					$rp_model->clearFansRedPacketCache($rp_id);

				} else if ($rp['type'] == RedPacketModel::TYPE_GTICKET_RP) {
					$rp_model->clearGTicketUserContributed($rp_id);
					$rp_model->clearGTicketTargetNumber($rp_id);
				}
				
				$sys_msg = $rp_model->formatRedPacketSysMsg($rp, $status);
				if (!empty($sys_msg)) {
					$rp_model->sendSysMsg($uid, $rp_id, $sys_msg);
				}
			}
		}
	}
	
	public static function fansEnrollRedPacket($params)
	{
		LogApi::logProcess("RedPacketApi fansEnrollRedPacket rq:" . json_encode($params));
		
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$rp_id = isset($params['rp_id'])?$params['rp_id']:0;
		
		$rs = array(
				'cmd' => "RFansEnrollRedPacket",
				'result' => RedPacketModel::$ERR_CODE_SUCCESS,
				'uid' => $uid,
				'sid' => $sid,
				'rp_id' => $rp_id
		);
		
		do {
			if (empty($uid) || empty($sid) || empty($rp_id)) {
				$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
				break;
			}
			
			$rp_model = new RedPacketModel();
			$inf_rp = $rp_model->getRedPacketFromCache($sid, $rp_id);
			if (!empty($inf_rp)) {
				$inf_rp = json_decode($inf_rp, true);
				
				if ($inf_rp['status'] == RedPacketModel::STATUS_INITIAL) {
					$rp_model->updateFansEnrollCount($rp_id, 1);
				}
			}
		} while (0);

		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		LogApi::logProcess("RedPacketApi fansEnrollRedPacket rs:" . json_encode($return));
		
		return $return;
	}
	
	public static function getFollowerRel($params)
	{
		LogApi::logProcess("RedPacketApi getFollowerRel rq:" . json_encode($params));
		
		$uid = isset($params['uid'])?$params['uid']:0;
		$fuids = $params['fuid'];
		
		$rs = array(
				'cmd' => "RGetFollowerRel",
				'result' => RedPacketModel::$ERR_CODE_SUCCESS,
				'uid' => $uid,
				'item_followers' => array()
		);
		
		do {
			if (empty($uid)) {
				$rs['result'] = RedPacketModel::$ERR_CODE_BAD_REQ_PARAM;
				break;
			}
			
			$rp_model = new RedPacketModel();
			$res = $rp_model->getFansInfsFromDB($uid, $fuids);
			if ($res['code'] == RedPacketModel::$ERR_CODE_SUCCESS) {
				$rs['item_followers'] = $res['data'];
			}
			
			$rs['result'] = $res['code'];
		} while (0);
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		LogApi::logProcess("RedPacketApi getFollowerRel rs:" . json_encode($return));
		return $return;
	}
	
	public static function fansEnterByRedPacket($params)
	{
		LogApi::logProcess("RedPacketApi fansEnterByRedPacket rq:" . json_encode($params));
		
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$rp_id = isset($params['rp_id'])?$params['rp_id']:0;
		$unick = isset($params['unick'])?$params['unick']:"";
		$rp_sender_id = isset($params['rp_sender_id'])?$params['rp_sender_id']:0;
		$rp_sender_nick = isset($params['rp_sender_nick'])?$params['rp_sender_nick']:"";
		
		$rs = array (
				'cmd' => "RFansEnterByRP",
				'result' => RedPacketModel::$ERR_CODE_SUCCESS,
				'uid' => $uid,
				'sid' => $sid,
				'rp_id' => $rp_id
		);
		
		$broadcast = array(
				'cmd' => "BFansEnterByRP",
				'uid' => $uid,
				'sid' => $sid,
				'rp_id' => $rp_id,
				'unick' => $unick,
				'rp_sender_id' => $rp_sender_id,
				'rp_sender_nick' => $rp_sender_nick
		);
		
		$return[] = array(
				'broadcast' => 0,
				'data' => $rs
		);
		
		$return[] = array(
				'broadcast' => 1,
				'data' => $broadcast
		);
		
		LogApi::logProcess("RedPacketApi fansEnterByRedPacket rs:" . json_encode($return));
		return $return;
	}

	public static function on_gticket_received($sid, $singer_id, $uid, $num)
	{
		LogApi::logProcess("RedPacketApi on_gticket_received sid:$sid singer_id:$singer_id uid:$uid num:$num");

		$list_status_red_packet = array();
		$result = array();
		
		$rp_model = new RedPacketModel();
		
		$list_rps = $rp_model->getRedPacketListFromCache($sid);
		
		foreach ($list_rps as $rp) {
			$rp = json_decode($rp, true);
			$rp_id = $rp['id'];
			if ($rp['type'] == RedPacketModel::TYPE_GTICKET_RP && $rp['status'] == RedPacketModel::STATUS_ACTIVE) {
				$rt = $rp_model->contributeGTicket($rp_id, $uid, $num);
				LogApi::logProcess("RedPacketApi on_gticket_received contribute. rp_id:$rp_id sid:$sid singer_id:$singer_id uid:$uid num:$num total:$rt");

				$cur_tickets = $rp_model->incrToalGTickets($rp_id, $num);
				if ($cur_tickets <= $rp['target_tickets']) {
					$rp['current_tickets'] = intval($cur_tickets);
					$rp['last_uptime'] = time();

					$rp_model->convertRedPacketInf($rp);
					array_push($list_status_red_packet, $rp);
				}
			}
		}


		if (count($list_status_red_packet)>0) {
			$result['broadcast'] = 1;
			$result['data'] = array(
				'cmd' => 'BRedPacketStatusChange',
				'list_gticket_rps' => $list_status_red_packet
			);
		}

		LogApi::logProcess("RedPacketApi on_gticket_received result:" . json_encode($result));

		return $result;
	}

	private static function if_pmd($uid, $sid, $money_total, $type)
	{
		$sys_parameters = new SysParametersModel();
		$pmdPrice = $sys_parameters->GetSysParameters(226, 'parm1');
		if (empty($pmdPrice)) {
			$pmdPrice = 5000;
		}
		
		$pmd = false;
		if($money_total >= $pmdPrice){
			$pmd = true;
		}
		
		$honors = array();
		if ($pmd) {
			// 获取礼物勋章信息
			$toolModel = new ToolModel();
			$gifts = $toolModel->getAllTools();
			foreach ($gifts as $g){
				$top3 = $toolModel->getTop3($g['id'], $uid);
				$honor = 0;
				$index = 0;
				foreach ($top3 as $top){
					$index++;
					if($top == $uid){
						$honor = $index;
						break;
					}
				}
				if($honor){
					$item = array();
					$item['tid'] = $g['id'];
					$item['img'] = $g['icon'];
					$item['index'] = $honor;
					$honors['items'][] = $item;
				}
			}
			
			$uinfo_model = new UserInfoModel();
			$uinfo = $uinfo_model->getInfoById($uid);
			
			$singer_id = 0;
			$sinfo = null;
			$channellive_model = new ChannelLiveModel();
			$channel_info = $channellive_model->getSessionInfo($sid);				
			if (!empty($channel_info)) {
				$singer_id = $channel_info['owner'];
				$sinfo = $uinfo_model->getInfoById($singer_id);
			}
			
			$red_packet_obj = array (
					'uid' => intval($uid),
					'unick' => $uinfo['nick'],
					'singer_id' => intval($singer_id),
					'sid' => intval($sid),
					'snick' => $sinfo['nick'],
					'money' => intval($money_total),
					'type' => $type,
					'honors' => $honors
			);

			return  array (
				'cmd' => 'BBroadcast',
				'type' => 6,
				'red_packet' => $red_packet_obj
			);

		}

		return null;
	}
}