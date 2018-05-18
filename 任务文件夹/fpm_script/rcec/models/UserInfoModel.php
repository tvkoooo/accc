<?php

class UserInfoModel extends ModelBase
{

    public function __construct()
    {
        parent::__construct();
    }
    
    //判断是否可以产生礼物
    public function createGift($uid, $type)
    {
    	$hashobj = ToolModel::KEY_COUNTDOWNTIME;
    	$key = $hashobj.':'.$uid;
    	
    	$value = $this->getRedisMaster()->hget($hashobj, $key);
    	
    	LogApi::logProcess("************UserInfoModel::createGift::type::$type *** key::$key *** value::$value");
    	
    	$ts = time();
    	$result['type'] = $type;
    	$result['result'] = '0';
    	if (!empty($value)) {
    		$tmp = explode("_", $value);
    		
    		LogApi::logProcess("************UserInfoModel::createGift::分割_::".json_encode($tmp));
    		
    		//当天在线时长
    		$curTimeLen = $ts-$tmp[0]+$tmp[1];
    		LogApi::logProcess("************UserInfoModel::createGift::当前在线时长::".$curTimeLen);
			switch ($type)
			{
				case 5:
					if($curTimeLen >= 5*60){//5分钟
					  	$result['result'] = 1;
					};
					break;
				case 15:
					if($curTimeLen >= 15*60){//15分钟
					  	$result['result'] = 1;
					};
					break;
				case 30:
					if($curTimeLen >= 30*60){//30分钟
					  	$result['result'] = 1;
					};
					break;
				case 60:
					if($curTimeLen >= 60*60){//60分钟
					  	$result['result'] = 1;
					};
					break;
				default:
				  	echo "";
			}
			
			if(1 == $result['result']){
				$toolConsumRecordModel = new ToolConsumeRecordModel();
		        $success = $toolConsumRecordModel->createGift($uid, $type);
				if(!$success){
					$result['result'] = 0;
				}
			}
    	}
		
    	return $result;
    }
    
    //用户离开直播间时更新当天停留在直播间的时长
    public function updateUserCountdownTime($uid)
    {
    	$hashobj = ToolModel::KEY_COUNTDOWNTIME;
    	$key = $hashobj.':'.$uid;
    	
    	$value = $this->getRedisMaster()->hget($hashobj, $key);
    	
    	LogApi::logProcess("************UserInfoModel::updateUserCountdownTime::key::$key *** value::$value");
    	
    	$ts = time();
    	if (!empty($value)) {
    		$tmp = explode("_", $value);
    		
    		if(1 == $tmp[2]){
    			return;
    		}
    		
    		//当前时间-最近一次开始时间=当前在线时长
    		$curTimeLen = $ts-$tmp[0];
    		//当天之前的在线时长总和+当前在线时长=当天在线时长总和
    		$total = $tmp[1]+$curTimeLen;
    		//每次退出直播间都更新在线时长
    		$value = $tmp[0] . '_' . $total;
    		$this->getRedisMaster()->hset($hashobj, $key, $value);
    	}
    }
    
    //用户每次进入直播间获取用户当天进入直播间停留的时长(每天凌晨5点是刷新点，过了5点算第一天的开始，此时要清空数据，清空数据放到web服务器处理)
    //
    public function getUserJoinChannelTime($uid)
    {
    	$hashobj = ToolModel::KEY_COUNTDOWNTIME;
    	$key = $hashobj.':'.$uid;
    	//获得用户集合
    	$value = $this->getRedisMaster()->hget($hashobj, $key);
    	//如果当天礼物已经领完，则只需要给客户端返回finish参数即可
    	if (!empty($value)) {
    		$tmp = explode("_", $value);
    		
    		LogApi::logProcess("************UserInfoModel::getUserJoinChannelTime::key:$key tmp[0]:$tmp[0]  tmp[1]:$tmp[1]  tmp[2]:$tmp[2]");
    		
    		if(1 == $tmp[2]){
    			$result['finish'] = '1';
    			return $result;
    		}
    	}
    	
		//查询领取礼物sql
	    $datetime='\''.date('Y-m-d').' 05:00:00\'';
    	$query = "select fromnum from user_signgift_history h  where (date_format(FROM_UNIXTIME(createtime),'%Y-%m-%d %H:%i:%S'))>=$datetime and fromtype = 3 and isreceive = 2 and uid = ".$uid;
    	
    	LogApi::logProcess("************UserInfoModel::getUserJoinChannelTime::query::".$query);
    	
        $rs = $this->getDbMain()->query($query);
    	if ($rs && $rs->num_rows > 0) {
            $row = $rs->fetch_assoc();
            while ($row) {
                $rows[] = $row;
                $row = $rs->fetch_assoc();
            }
        }
        
        LogApi::logProcess("************UserInfoModel::getUserJoinChannelTime::查询领取礼物结果::rows: ".json_encode($rows));
        
        $toolConsumeRecordModel = new ToolConsumeRecordModel();
        $giftObjs = $toolConsumeRecordModel->getOnlineGiftInfo();
        
        $result['finish'] = '0';
        
        $datas = array();
        $data = array();
    	foreach ($giftObjs as $giftObj) {
    		//LogApi::logProcess("************UserInfoModel::getUserJoinChannelTime::giftObj::".json_encode($giftObj));
    		$gObj = json_decode($giftObj, TRUE);
    		//默认没有领取礼物
    		$data['get'] = 0;
        	$data['fromtype'] = 	$gObj['fromtype'];
        	$data['fromnum'] = 		$gObj['fromnum'];
        	$data['giftid'] = 		$gObj['giftid'];
        	if(0 == $gObj['gifttype']){//金币url
        		$data['imgurl'] = 	$gObj['jinbiimgurl'];
        	}else{//金币礼物url
        		$data['imgurl'] = 	$gObj['giftimgurl'];
        	}
        
	        if (!empty($rows)){
		        foreach ($rows as $row) {
		        	if($gObj['fromnum'] == $row['fromnum']){
		        		//礼物已经被领取
		            	$data['get'] = 1;
		        	}
			        //如果领取礼物的fromnum为60，则表示当天礼物已经领完
			        if(60 == $row['fromnum']){
			        	$result['finish'] = '1';
			        }
		        }
	        }
        
        	$datas[] = $data;
    	}
        
        $result['data'] = $datas;
		
    	$result['uid'] = $uid;
        
    	$ts = time();
    	if (!empty($value)) {
    		$tmp = explode("_", $value);
    		$result['start_time'] = $ts;
    		$result['remain_time'] = $tmp[1];
    		//每次进入房间都更新开始时间（保存开始时间是为了用户退出时计算停留时间）
    		$value = $ts . '_' . $tmp[1] . '_' . $result['finish'];
    		$this->getRedisMaster()->hSet($hashobj, $key, $value);
    		if($tmp[1] >= 60*60){//在线时间超过一个小时停止倒计时60*60
    			$result['stop_time'] = 1;
    		}else{
    			$result['stop_time'] = 0;
    		}
    	}else{
    		//$ts.'_0_0':开始时间_在线时长_是否完成领取
    		$value = $ts.'_0_0';
    		$this->getRedisMaster()->hSet($hashobj, $key, $value);
    		$result['start_time'] = $ts;
    		$result['remain_time'] = 0;
    		$result['stop_time'] = 0;
    	}
    	
    	LogApi::logProcess("************UserInfoModel::getUserJoinChannelTime::result::".json_encode($result));
    	
    	return $result;
    }
    
    public function updateUnionInfo($unionId, $singerUid, $SpeakCount, $sunNum, $liveTime)
    {
        $key = "h_union_guard:$unionId:$singerUid";
        $redis = $this->getRedisJavaUtil();
        if ($redis->exists($key)) {
            $redis->hIncrBy($key, "sun_num", $sunNum);
            $redis->hIncrBy($key, "time_length", $liveTime);
            $redis->hIncrBy($key, "speak_count", $SpeakCount);
        }
    }
    
    //更新帮会成员观看时长
    public function updateUnionLiveTime($sid, $unionId, $singerUid, $uid)
    {
        LogApi::logProcess('updateUnionLiveTime 1, union_id:'.$unionId);
        if(!empty($unionId)){
            /* $key = "room_active_$sid";
            $field = "room_active:$uid"; */
            $key = member_list::UserWatchRoomTimeKey($uid);
            
            $value = $this->getRedisMaster()->get($key);
            if(empty($value)){
                LogApi::logProcess('updateUnionLiveTime 2');
                return;
            }
            $data = json_decode($value, true);
            
            if($data['stop'] == 1){
                LogApi::logProcess('updateUnionLiveTime 3');
                return;
            }
            
            LogApi::logProcess("用户离开直播间更新帮会成员观看时长, data:".json_encode($data));
            
            //因为调用之前已经判断用户和主播是同一帮会，所以用户进入直播间的时间就是本场观看开始时间，
            //用现在时间-开始观看时间=本场用户观看时长
            $liveTime = time() - $data['start_time'];
            
            $this->updateUnionInfo($unionId, $singerUid, 0, 0, $liveTime);
        }
    }
    
    //更新帮会阳光值
    public function updateUnionSunNum($unionId, $singerUid, $sunNum)
    {
        if(!empty($unionId)){
            $this->updateUnionInfo($unionId, $singerUid, 0, $sunNum, 0);
        }
    }
    
    //更新帮会发言次数
    public function updateUnionSpeakCount($unionId, $singerUid)
    {
        if(!empty($unionId)){
            $this->updateUnionInfo($unionId, $singerUid, 1, 0, 0);
        }
    }

    public function getInfoById($uid) //zzzzz
    {
        $key = 'uid:' . $uid;
        $ttl = 3600;
        $value = $this->getRedisSlave()->get($key);
        if ($value !== false) {
            return json_decode($value, true);
        } else {
            $query = "select * from raidcall.uinfo t where t.id = $uid";
            $rows = $this->getDbRaidcall()->query($query);
            if ($rows && $rows->num_rows > 0) {
                $row = $rows->fetch_assoc();
                $this->getRedisMaster()->setex($key, $ttl, json_encode($row));
                return $row;
            } else {
                return array(
                    'uid' => $uid,
                    'nick' => '',
                    'account' => '',
                    'is_robot' => 1,
                    'sivler' => 0
                );
            }
        }
        /* 
        $key = 'uid:' . $uid;
        $query = "select * from raidcall.uinfo t where t.id = $uid";
        $rows = $this->read($key, $query, 0, 'dbRaidcall', false);
        
        LogApi::logProcess("### getInfoById 0 返回用户key:$key, return:$rows num:".count($rows));
        
        if (count($rows) == 1) {
            	
            LogApi::logProcess("### getInfoById 1");
            $data = $rows[0];
            LogApi::logProcess("### getInfoById 2");
        }else{
            $data = array(
                'uid' => $uid,
                'nick' => '',
                'account' => '',
                'sivler' => 0
            );
        }
        
        LogApi::logProcess("getInfoById 返回用户 $uid, data:".json_encode($data));
        
        return $data; */
        
        //$key = 'raidcall:uinfo:' . $uid;
        /* $key = 'uid:' . $uid;
        $ttl = 3600;
        $value = getRedisSlave()->get($key);
        if ($value !== false) {
            return json_decode($value, true);
        } else {
            $req = new HttpRequestRcdb(); //zzzzzz
            $data = $req->get_uinfo($uid);
            if ($data && false != $data && !empty($data)) {
                getRedisMaster()->setex($key, $ttl, json_encode($data));
                return $data;
            } else {
                return array(
                    'uid' => $uid,
                    'nick' => '',
                    'account' => '',
                    'sivler' => 0
                );
            }
        } */
    }


    public function getSessOwnerInfo($sid, $uid)
    {
	ToolApi::logProcess("getSessOwnerInfo+++" . $sid);
    	$req = new HttpRequestRcdb();
	$user_attr = new UserAttributeModel();
	ToolApi::logProcess("getSessOwnerInfo---" . $uid);
	$user_data = $req->get_uinfo($uid);
	if(!empty($user_data)) {
		$nick = $user_data['nick'];
		$flower = $user_attr->getFlower($uid);
		ToolApi::logProcess("getSessOwnerInfo---++++" . $nick);
		return array(
				'sid' => $sid,
				'uid' => $uid,
				'nick' => $nick,
				'flower' => $flower
			    );
	}
	return array(
			'sid' => $sid,
			'uid' => '',
			'nick' => '',
			'flower' => 0
		    );
    }

    public function updateSilver($uid, $silver)
    {
        $req = new HttpRequestRcdb();
        $data = $req->change_silver($uid, $silver);
        return $data;
    }

    public function getSilver($uid)
    {
        $info = $this->getInfoById($uid);
        if (!empty($info['silver'])) {
            return $info['silver'];
        } else {
            return 0;
        }
    }

    public function getNickName($uid)
    {
        $info = $this->getInfoById($uid);
        if($info){
        	if (!empty($info['nick'])) {
	            return $info['nick'];
	        } else {
	            return '';
	        }
        }
        return '';
    }

    public function getBadgeList($uid)
    {
        $key = 'raidcall:badge:' . $uid;
        $ttl = 3600;
        $value = $this->getRedisSlave()->get($key);
        if ($value !== false) {
            return json_decode($value, true);
        } else {
            $req = new HttpRequestRcdb();
            $data = $req->get_badge($uid);
            if (!empty($data)) {
                $this->getRedisMaster()->setex($key, $ttl, json_encode($data));
                return $data;
            } else {
                return false;
            }
        }
    }

    public function getVipLevel($uid)
    {
        $info = $this->getInfoById($uid);
        if (!empty($info['vip_level'])) {
            return $info['vip_level'];
        } else {
            return 0;
        }
    }
    
    public function GetSidByUid($uid)
    {
        $value = 0;
        
        $sql = "select sid from raidcall.sess_info where owner = $uid";
        $rows = $this->getDbMain()->query($sql);
        if ($rows) 
        {
            $row = $rows->fetch_assoc();
            $value = intval($row['sid']);
        }
        else 
        {
            LogApi::logProcess("GetSidByUid sql:".$sql);
        }        
        return $value;
    }
    public function GetUidBySid($sid)
    {
        $value = 0;
    
        $sql = "select owner from raidcall.sess_info where sid = $sid";
        $rows = $this->getDbMain()->query($sql);
        if ($rows)
        {
            $row = $rows->fetch_assoc();
            $value = intval($row['owner']);
        }
        else
        {
            LogApi::logProcess("GetUidBySid sql:".$sql);
        }
        return $value;
    }
    public function GetSingerFamilyId($singer_id)
    {
    	$key = "str_anchor_family:$singer_id";
    	$redis = $this->getRedisMaster();
    	
    	$family_id = $redis->get($key);
    	
    	if ($family_id === false) {
    		$sql = "SELECT family_id FROM raidcall.anchor_info WHERE uid=$singer_id";
    		$rows = $this->getDbMain()->query($sql);
    		$family_id = 0;
    		if ($rows && $rows->num_rows > 0) {
    			$row = $rows->fetch_assoc();
    			$family_id = intval($row['family_id']);
    			
    			$redis->setex($key, 24*60*60, $family_id);
    		} else {
    			LogApi::logProcess("SetSingerFamilyId exe sql is error****************sql:$sql");
    		}
    	}
    	
    	return intval($family_id);
    }
    
    public function SetAndGetSingerFamilyId($singer_id)
    {
    	$key = "str_anchor_family:$singer_id";
    	$redis = $this->getRedisMaster();
    	
    	$family_id = $redis->get($key);
    	
    	$sql = "SELECT family_id FROM raidcall.anchor_info WHERE uid=$singer_id";
    	$rows = $this->getDbMain()->query($sql);
    	$family_id = 0;
    	if ($rows && $rows->num_rows > 0) {
    		$row = $rows->fetch_assoc();
    		$family_id = intval($row['family_id']);
    		 
    		$redis->setex($key, 24*60*60, $family_id);
    	} else {
    		LogApi::logProcess("SetAndGetSingerFamilyId exe sql is error****************sql:$sql");
    	}
    	
    	return intval($family_id);
    }

	
	public function isHasRedTicket($uid)
    {
		$sql = "SELECT uid FROM card.`user_goods_info` WHERE uid = $uid AND goods_type = 5 AND num > 0";
    	$rows = $this->getDbMain()->query($sql);
    	if ($rows && $rows->num_rows > 0)
    	{
    		return true;
    	} 
    	else if(!$rows)
    	{
    		LogApi::logProcess("isHasRedTicket exe sql is error****************sql:$sql");
    	}

    	return false;
    }

	public function ishasSunflowerSeeds($uid)
    {
		$sql = "SELECT uid FROM card.`user_goods_info` WHERE uid = $uid AND goods_id = 127 AND num > 0";
    	$rows = $this->getDbMain()->query($sql);
    	if ($rows && $rows->num_rows > 0)
    	{
    		return true;
    	} 
    	else if(!$rows)
    	{
    		LogApi::logProcess("ishasSunflowerSeeds exe sql is error****************sql:$sql");
    	}

    	return false;
    }
    
    
}
