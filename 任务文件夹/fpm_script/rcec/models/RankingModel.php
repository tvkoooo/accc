<?php
	// zxy modify 2015-11-30 17:12:10
//	static $gConsumeUpdateTimeMap = array();
//	function ConsumeUpdateTimeMap(){
//		$consumeUpdateTimeMap = $GLOBALS["gConsumeUpdateTimeMap"];
//		
//		if(null == $consumeUpdateTimeMap){
//			$GLOBALS["gConsumeUpdateTimeMap"] = $consumeUpdateTimeMap = array();
//		}
//		return $consumeUpdateTimeMap;//$GLOBALS["gConsumeUpdateTimeMap"];
//	}
class RankingModel extends ModelBase {
    public function __construct ()
    {
        parent::__construct();
    }
    
	public function getGiftRankingByUid($uid) {
		$rank = $this->getRedisSlave()->get('gift_ranking:' . $uid);
		return json_decode($rank, true);
	}

	public function setGiftRankingByUid($uid, $ranking) {
		return $this->getRedisMaster()->set('gift_ranking:' . $uid, json_encode($ranking));
	}

	public function getRankList($key, $length = 10) {
		$newList = array ();
		$list = $this->getRedisSlave()->zRevRange($key, 0, $length -1, true);
		$i = 1;
		$userAttrModel = new UserAttributeModel();
		foreach ($list as $uid => $score) {
			$data = array ();
			$data['rank'] = $i++;
			$data['score'] = $score;
			$data += $userAttrModel->getUserInfo($uid);
			$newList[] = $data;
		}
		return $newList;
	}

	public function getRankByUid($key, $uid, $ttl = 0) {
		$data = array ();
		$rank = $this->getRedisSlave()->zRevRank($key, $uid);
		if ($rank !== false) {
			$data['rank'] = $rank +1;
			$data['score'] = $this->getRedisSlave()->zScore($key, $uid);
		} else {
			$data['rank'] = 0;
			$data['score'] = 0;
		}
		$userAttrModel = new UserAttributeModel();
		$data += $userAttrModel->getUserInfo($uid);
		return $data;
	}

	public function pushToMq($uid, $recUid, $giftValue, $closeValue, $toolId) {
		$message = "$uid,$recUid,$giftValue,$closeValue,$toolId";
		$this->getRedisMq()->lPush('rank_mq', $message);
	}

	public function getRankListWithBadge($key, $length = 10) {
		$newList = array ();
		$list = $this->getRedisSlave()->zRevRange($key, 0, $length -1, true);
		$i = 1;
		$userAttrModel = new UserAttributeModel();
		$userInfoModel = new UserInfoModel();
		foreach ($list as $uid => $score) {
			$data = array ();
			$data['rank'] = $i++;
			$data['score'] = $score;
			$data['badge'] = $userInfoModel->getBadgeList($uid);
			$data += $userAttrModel->getUserInfo($uid);
			$newList[] = $data;
		}
		return $newList;
	}

	public function updateFansRank($uid, $singerUid, $giftValue) {
		$key = 'fans_rank:' . $singerUid . '_' . date('Ymd');
		$length = 15;
		$rank = $this->getRedisSlave()->zRevRank($key, $uid);
		$this->getRedisMaster()->zIncrBy($key, $giftValue, $uid);
		$newRank = $this->getRedisSlave()->zRevRank($key, $uid);
		if ($newRank <= $length && ($newRank < $rank || $rank === false)) {
			$data['newRank'] = $newRank +1;
			$data['fansRank'] = $this->getRankListWithBadge($key, $length);
			return $data;
		}
		return false;
	}

	public function getFansRank($singerUid) {
		$key = 'fans_rank:' . $singerUid . '_' . date('Ymd');
		$length = 15;
		return $this->getRankListWithBadge($key, $length);
	}

	public function updateVipChair($uid, $singerUid, $giftValue) {
		$key = 'vip_chair:' . $singerUid;
		$length = 5;
		$rank = $this->getRedisSlave()->zRevRank($key, $uid);
		$this->getRedisMaster()->zAdd($key, $giftValue, $uid);
		$newRank = $this->getRedisSlave()->zRevRank($key, $uid);
		if ($newRank <= $length && ($newRank < $rank || $rank === false)) {
			$data['vipChair'] = $this->getVipChair($singerUid, $length);
			return $data;
		}
		return false;
	}

	public function getVipChair($singerUid, $length = 5) {
		$key = 'vip_chair:' . $singerUid;
		$list = $this->getRedisSlave()->zRevRange($key, 0, $length -1, true);
		$newList = array ();
		$i = 1;
		$userInfoModel = new UserInfoModel();
		foreach ($list as $uid => $score) {
			$data = array ();
			$data['rank'] = $i++;
			$data['score'] = $score;
			$data['uid'] = $uid;
			$data['nick'] = $userInfoModel->getNickName($uid);
			$newList[] = $data;
		}
		return $newList;
	}

	public function clearVipChair($singerUid) {
		$key = 'vip_chair:' . $singerUid;
		$this->getRedisMaster()->zRem($key, $singerUid);
	}
	// 获取用户在直播间的消费日榜
	public function getSidUserConsumeDayRank($sid, $singerUid){
		$date = 'DataSidUsrConsumeRank:' . $sid . '_' . date('Ymd');
		return $this->getRankList2($date, $singerUid);
	}
	// 获取用户在直播间的消费周榜
	public function getSidUserConsumeWeekRank($sid, $singerUid){
		$week = 'WeekSidUsrConsumeRank:' . $sid;
		$tsNow = time();
		LogApi::logProcess('RankingModel::getSidUserConsumeWeekRank entry... sid:' . $sid . ", singerUid:" . $singerUid);
		if(RankingStatApi::isConsumeTimeout($week, $tsNow)){
			//file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "RankingModel::updateSinUserConsumeWeekRankNew time out\n", FILE_APPEND);
		LogApi::logProcess('RankingModel::getSidUserConsumeWeekRank need update');
			$this->updateSinUserConsumeWeekRankNew($week, $sid);
		}
		LogApi::logProcess('RankingModel::getSidUserConsumeWeekRank completed');
		return $this->getRankList2($week, $singerUid);
	}
	// 获取用户在直播间的消费月榜
	public function getSidUserConsumeMonthRank($sid, $singerUid){
		$month = 'MonthSidUsrConsumeRank:' . $sid . '_' . date('Ym');
		return $this->getRankList2($month, $singerUid);
	}
	///////////////////////////////////
	public function updateSidUserConsumeRank($sid, $uid, $singerUid, $giftValue, $tsTime, & $isRankChg) {
		LogApi::logProcess('RankingModel::updateSidUserConsumeRank entry...');
		// zxy modify 2015-11-30 17:12:10
		return $this->updateSidUserConsumeWeekRankNew($sid, $uid, $singerUid, $giftValue, $tsTime, $isRankChg);
		//////////////////////////////////
		// 查询用户当前的名次
		/*$date = 'DataSidUsrConsumeRank:' . $sid . '_' . date('Ymd');
		$pre_rank = getRedisMaster()->zRevRank($date, $uid);
		//更新日榜		  
		getRedisMaster()->zIncrBy($date, $giftValue, $uid);
		//更新周榜
		$week = 'WeekSidUsrConsumeRank:' . $sid . '_' . date('W');
		getRedisMaster()->zIncrBy($week, $giftValue, $uid);
		//更新月榜
		$month = 'MonthSidUsrConsumeRank:' . $sid . '_' . date('Ym');
		getRedisMaster()->zIncrBy($month, $giftValue, $uid);

		$data = array ();
		$temp_date = $this->getRankList2($date, $singerUid);
		foreach ($temp_date as $i => $dict) {
			if ($dict['uid'] == $uid) {
				$data['dayRankList'] = $temp_date;
			}
		}

		$temp_week = $this->getRankList2($week, $singerUid);
		foreach ($temp_week as $i => $dict) {
			if ($dict['uid'] == $uid) {
				$data['weekRankList'] = $temp_week;
			}
		}

		$temp_month = $this->getRankList2($month, $singerUid);
		foreach ($temp_month as $i => $dict) {
			if ($dict['uid'] == $uid) {
				$data['monthRankList'] = $temp_month;
			}
		}

		$post_rank = getRedisMaster()->zRevRank($date, $uid);
		if ($pre_rank) {
			if ($pre_rank > 9 && $post_rank <= 9) {
				$isRankChg = true;
			}

		} else {
			if ($post_rank <= 9) {
				$isRankChg = true;
			}
		}

		return $data;
		*/
	}
	private function generateConsumeRecord($sid, $uid, $singerUid, $giftValue, $tsTime){
		return '' . $uid . ':' .$singerUid . ':' . $tsTime . ':' . $giftValue . ':' . Utils::guid();
	}
	public function updateSidUserConsumeWeekRankNew($sid, $uid, $singerUid, $giftValue, $tsTime, & $isRankChg) {
		// 更新周榜
		$week = 'WeekSidUsrConsumeRank:' . $sid;
		$consumeRecord = 'RoomConsumeRecord:' . $sid;
		//$tsTime = time();
		$outTime = $tsTime-60*60*24*7;
		// 生成序列化记录
		$record = $this->generateConsumeRecord($sid, $uid, $singerUid, $giftValue, $tsTime);
		// 数据修改前的周榜中，用户排序序号
		$pre_rank = $this->getRedisRankSlave()->zRevRank($week, $uid);
		
		// 为直播间周榜增加相应消费数值
		$this->getRedisRankMaster()->zIncrBy($week, $giftValue, $uid);
		
		LogApi::logProcess("updateSidUserConsumeWeekRankNew******1");
		// 为主播收入7天榜增加相应消费数值
		$this->addSingerIncomeRankInfo($sid, $singerUid, $giftValue);
		
		LogApi::logProcess("updateSidUserConsumeWeekRankNew******2");
		// 获取该房间当前时间之前的的消费记录数据
		$outTimeRecords = $this->getRedisRankSlave()->zRangeByScore($consumeRecord, 0, $outTime, array('withscores' => TRUE));
		
		LogApi::logProcess("updateSidUserConsumeWeekRankNew******2-1");
		
		if($outTimeRecords && count($outTimeRecords) > 0){
		    
		    LogApi::logProcess("updateSidUserConsumeWeekRankNew******2-2");
			// 遍历超时记录
			foreach ($outTimeRecords as $outTimeRecord => $score){
			    LogApi::logProcess("updateSidUserConsumeWeekRankNew******2-3");
				$recordInfo = explode(':', $outTimeRecord);
				LogApi::logProcess("updateSidUserConsumeWeekRankNew******2-4");
				// 移除直播间周榜7天前数据
				$this->getRedisRankMaster()->zIncrBy($week, 0-intval($recordInfo[3]), intval($recordInfo[0]));
				LogApi::logProcess("updateSidUserConsumeWeekRankNew******2-5");
				// 移除主播收入7天榜7天前数据
				$this->addSingerIncomeRankInfo($sid, intval($recordInfo[1]), 0-intval($recordInfo[3]));
		          
				LogApi::logProcess("updateSidUserConsumeWeekRankNew******2-6");
			}
			LogApi::logProcess("updateSidUserConsumeWeekRankNew******2-7");
			// 移除该区间的数据
			$this->getRedisRankSlave()->zRemRangeByScore($consumeRecord, 0, $outTime);
		}
		
		LogApi::logProcess("updateSidUserConsumeWeekRankNew******3");
	    // 将当前消费计入房间消费记录
		$this->getRedisRankMaster()->zIncrBy($consumeRecord, $tsTime, $record);
		// 测试（30秒后，该调数据，将被减掉）
		//getRedisRankMaster()->zIncrBy($consumeRecord, $tsNow-7*24*60*60+30, $record);
		//// 更新消费排行榜的更新时间
		//RankingStatApi::updateConsumeTime($week, $tsTime);
		
		LogApi::logProcess("updateSidUserConsumeWeekRankNew******4");
		$data = array ();
		$temp_week = $this->getRankList2($week, $singerUid);
		foreach ($temp_week as $i => $dict) {
			if ($dict['uid'] == $uid) {
				$data['weekRankList'] = $temp_week;
			}
		}
		
		LogApi::logProcess("updateSidUserConsumeWeekRankNew******5");
		$post_rank = $this->getRedisRankSlave()->zRevRank($week, $uid);
		if ($pre_rank) {
			// 有排序
			
			if ($pre_rank > 9) {
				// 消费前，排序靠后
				
				if($post_rank <= 9){
					// 消费后，排序进入前十
					$isRankChg = true;
				}
			}else{
				// 消费前，排序在前十
				
				if($pre_rank != $post_rank){
					// 消费后，排序变动
					$isRankChg = true;
				}
			}
		} else {
			if ($post_rank <= 9) {
				$isRankChg = true;
			}
		}
		LogApi::logProcess("updateSidUserConsumeWeekRankNew******6");
		return $data;
	}
	// 更新周榜信息
	public function updateSinUserConsumeWeekRankNew($weekKey, $sid){
		$consumeRecord = 'RoomConsumeRecord:' . $sid;
		$tsNow = time();
		$outTime = $tsNow-60*60*24*7;
		LogApi::logProcess('RankingModel::updateSinUserConsumeWeekRankNew entry...');
		// 获取该房间当前时间之前的的消费记录数据
		$outTimeRecords = $this->getRedisRankSlave()->zRangeByScore($consumeRecord, 0, $outTime, array('withscores' => TRUE));
		// 移除该区间的数据
		if($outTimeRecords && count($outTimeRecords) > 0){
			LogApi::logProcess('RankingModel::updateSinUserConsumeWeekRankNew timeout > 0...');
			// 遍历超时记录
			foreach ($outTimeRecords as $outTimeRecord => $score){
				$recordInfo = explode(':', $outTimeRecord);
				// 移除直播间周榜7天前数据
				$this->getRedisRankMaster()->zIncrBy($weekKey, 0-intval($recordInfo[3]), intval($recordInfo[0]));
				// 移除主播收入7天榜7天前数据
				$this->addSingerIncomeRankInfo($sid, intval($recordInfo[1]), 0-intval($recordInfo[3]));
		    }
			$this->getRedisRankSlave()->zRemRangeByScore($consumeRecord, 0, $outTime);
		}
		//RankingStatApi::updateConsumeTime($weekKey, $tsNow);
		LogApi::logProcess('RankingModel::updateSinUserConsumeWeekRankNew completed...');
	}

	public function getRankList2($key, $singerUid) {
		//LogApi::logProcess('RankingModel::getRankList2 entry...');

		$activityModel = new ActivityModel();
		$list = $activityModel->getList2($key, $singerUid);
		return $list;
	}



	public function reloadRankListFromDB(){
		LogApi::logProcess('RankingModel::reloadRankListFromDB');
  		{	// 清除所有房间的周榜数据和送礼记录数据
			$week = 'WeekSidUsrConsumeRank:*';
			$consumeRecord = 'RoomConsumeRecord:*';
			$singerIncomeWeekRank = 'SingerIncomeWeekRank';
			
			$this->getRedisRankSlave()->delete($this->getRedisRankSlave()->keys($week));
			$this->getRedisRankSlave()->delete($this->getRedisRankSlave()->keys($consumeRecord));
			$this->getRedisRankSlave()->delete($singerIncomeWeekRank);
  		}
  		$stopTime = time();
  		$startTime = $stopTime - 60*60*24*7;
  		$sql = "SELECT sid,uid,receiver_uid,total_coins_cost,record_time FROM tool_consume_record WHERE record_time > $startTime and record_time < $stopTime";
  		$rs = $this->getDbRecord()->query($sql, false);
  		$isRankChg = false;
		LogApi::logProcess('RankingModel::reloadRankListFromDB sql:' . $sql);
  		if(null == $rs){
			LogApi::logProcess('RankingModel::reloadRankListFromDB is null');
  			return;
  		}
		LogApi::logProcess('RankingModel::reloadRankListFromDB rows:' . $rs->num_rows);
		$handledCount = 0;
		if ($rs->num_rows > 0) {
            //$row = $rs->fetch_assoc();
            while ($row = $rs->fetch_assoc()) {
//                //// 一行记录
//                ////$rows[] = $row;
//                //$this->updateSidUserConsumeRank($row['sid'], $row['uid'], $row['receiver_uid'], $row['total_coins_cost'], $row['record_time'], $isRankChg);
				$sid = $row['sid'];
				$uid = $row['uid'];
				$singerUid = $row['receiver_uid'];
				$giftValue = $row['total_coins_cost'];
				$tsTime = $row['record_time'];
//				
//				$week = 'WeekSidUsrConsumeRank:'.$sid;
//				$consumeRecord = 'RoomConsumeRecord:'.$sid;
//				$record = $this->generateConsumeRecord($sid, $uid, $singerUid, $giftValue, $tsTime);
//				getRedisRankMaster()->zIncrBy($consumeRecord, $tsTime, $record);
//				getRedisRankMaster()->zIncrBy($week, $giftValue, $uid);
				$this->addRoomRankInfo($sid, $uid, $singerUid, $giftValue, $tsTime);
				$this->addSingerIncomeRankInfo($sid, $singerUid, $giftValue);
				
				$handledCount += 1;
				
				//LogApi::logProcess('RankingModel::reloadRankListFromDB row' . $giftValue);
            }
        }
		LogApi::logProcess('RankingModel::reloadRankListFromDB completed handled:' . $handledCount);
	}
	public function addRoomRankInfo($sid, $uid, $singerUid, $giftValue, $tsTime){
		$week = 'WeekSidUsrConsumeRank:'.$sid;
		$consumeRecord = 'RoomConsumeRecord:'.$sid;
		$record = $this->generateConsumeRecord($sid, $uid, $singerUid, $giftValue, $tsTime);
		$this->getRedisRankMaster()->zIncrBy($consumeRecord, $tsTime, $record);
		$this->getRedisRankMaster()->zIncrBy($week, $giftValue, $uid);
	}
	
	private function addSingerIncomeRankInfo($sid, $singerUid, $giftValue){
		$key = 'SingerIncomeWeekRank';
		$this->getRedisRankMaster()->zIncrBy($key, $giftValue, $singerUid);
		//LogApi::logProcess('RankingModel::addSingerIncomeRankInfo ret:' . $ret);
	}
	public function singerIncomeRankForOnline($sid, $singerUid, $isPublish){
		LogApi::logProcess('RankingModel::singerIncomeRankForOnline isPublish:' . $isPublish);
		$key = 'SingerIncomeWeekRank';
		$onlineGiftValue = 100000000;
		$preGiftValue = $this->getRedisRankMaster()->zScore($key, $singerUid);
		// 注释原因：当主播第一次直播时，此值可能为空
		//if(null == $preGiftValue){
		//	return false;
		//}
		if($isPublish){
			if(null != $preGiftValue && $preGiftValue >= $onlineGiftValue){
				// 已设置上线
				LogApi::logProcess('RankingModel::singerIncomeRankForOnline online repeat uid:' . $singerUid);
				return true;
			}
			LogApi::logProcess('RankingModel::singerIncomeRankForOnline set online uid:' . $singerUid);
			// 未设置上线，则设置上线
			$this->getRedisRankMaster()->zIncrBy($key, $onlineGiftValue, $singerUid);
		}else{
			if(null == $preGiftValue || $preGiftValue < $onlineGiftValue){
				// 未设置上线或已设置下线
				LogApi::logProcess('RankingModel::singerIncomeRankForOnline offline repeat uid:' . $singerUid);
				return true;
			}
			LogApi::logProcess('RankingModel::singerIncomeRankForOnline set offline uid:' . $singerUid);
			// 未设置下线，则设置下线
			$this->getRedisRankMaster()->zIncrBy($key, 0-$onlineGiftValue, $singerUid);
		}
		return true;
	}
	
	
	public function updatePromotionsRank($singerUid, $giftId, $giftValue, $tsTime){
		if($giftId == 343 || $giftId == 342){
			if($tsTime >= 1450886400 && $tsTime <= 1451836799){
				$key = '343:201512:SingerPromotionsRank';
				$this->getRedisRankMaster()->zIncrBy($key, $giftValue, $singerUid);
			}
		}
	}
}
?>
