<?php

class SingerGuardModel extends ModelBase
{

    public $closeValueList = array(
        0,
        303000,
        815000,
        1542000,
        2490000,
        3665000,
        5073000,
        6720000,
        8612000,
        10755000, // 2
        13155000,
        15818000,
        18750000,
        21957000,
        25445000,
        29220000,
        33288000,
        37655000,
        42327000,
        47310000, // 3
        52610000,
        58233000,
        64185000,
        70472000,
        77100000,
        84075000,
        91403000,
        99090000,
        107142000,
        115565000,
        124365000
    );

    public $charmRateList = array(
        100,
        100,
        100,
        100,
        100,
        103,
        103,
        103,
        103,
        103,
        107,
        107,
        107,
        107,
        107,
        110,
        110,
        110,
        110,
        110,
        113,
        113,
        113,
        113,
        113,
        117,
        117,
        117,
        117,
        117,
        120
    );

    public function __construct ()
    {
        parent::__construct();
    }

    public function getList ($uid, $isSinger = false)
    {
        $now = time();
        if ($isSinger) {
            $key = 'singer_guard_for:' . $uid;
            $query = "select * from singer_guard where singer_uid = $uid and end_time > $now";
        } else {
            $key = 'singer_guard:' . $uid;
            $query = "select * from singer_guard where uid = $uid and end_time > $now";
        }
        $rows = $this->read($key, $query, 864000);
        $data = array();
        if (count($rows) > 0) {
            foreach ($rows as $tmpRow) {
                if ($tmpRow['end_time'] > $now) {
                    $data[] = $tmpRow;
                }
            }
            return $data;
        }
        return false;
    }

    public function closeEnough ($uid, $singerUid, $closeValue)
    {
        $list = $this->getList($uid);
        $guard = array();
        if ($list) {
            foreach ($list as $row) {
                if ($row['singer_uid'] == $singerUid) {
                    $guard = $row;
                }
            }
        }
        if (empty($guard)) {
            return 155; // 沒有開通守護
        }
        if ($guard['close_value'] < $closeValue) {
            return 154; // 您和主播的親密度不夠，不能贈送守護專屬禮物
        }
        return 0;
    }


    public function addGuardRecord2 ($singerUid, $uid, $duration)
    {
        $query = "select * from singer_guard where singer_uid = $singerUid and uid = $uid";
        $rs = $this->getDbMain()->query($query);
        $authTime = time();
        if (! $rs || $rs->num_rows == 0) {
            // 直接增加
            $endTime = time() + $duration * 2592000;
            $query = "insert into singer_guard set singer_uid = $singerUid, uid = $uid, auth_time = $authTime, end_time = $endTime, close_value = 1";
            $rs = $this->getDbMain()->query($query);
        } else {
            // 更新時間
            $row = $rs->fetch_assoc();
            $oldEndTime = max(array(
                time(),
                $row['end_time']
            ));
            $endTime = $oldEndTime + $duration * 2592000;
            $query = "update singer_guard set auth_time = $authTime, end_time = $endTime where singer_uid = $singerUid and uid = $uid";
            $rs = $this->getDbMain()->query($query);
        }
        // 清理緩存，更新最長守護時間
    	$this->clearCache($uid, $singerUid);
    	$userAttrModel = new UserAttributeModel();
    	$guardEndTime = $userAttrModel->getGuardEndTime($uid, $singerUid);
    	if($endTime > $guardEndTime)
    	{
    		$userAttrModel->setGuardEndTime($uid, $singerUid, $endTime);
    	}       
	/*
	$guardEndTime = $this->userAttrModel->getStatusByUid($uid, 'guard_end_time');
        if ($endTime > $guardEndTime) {
            $this->userAttrModel->setStatusByUid($uid, 'guard_end_time', $endTime);
        }*/
	   return $endTime;
    }

    public function addGuardRecord ($applyRow)
    {
        $singerUid = $applyRow['singer_uid'];
        $uid = $applyRow['uid'];
        $query = "select * from singer_guard where singer_uid = $singerUid and uid = $uid";
        $rs = $this->getDbMain()->query($query);
        $authTime = time();
        if (! $rs || $rs->num_rows == 0) {
            // 直接增加
            $endTime = time() + $applyRow['duration'] * 2592000;
            $query = "insert into singer_guard set singer_uid = $singerUid, uid = $uid, auth_time = $authTime, end_time = $endTime, close_value = 1";
            $rs = $this->getDbMain()->query($query);
        } else {
            // 更新時間
            $row = $rs->fetch_assoc();
            $oldEndTime = max(array(
                time(),
                $row['end_time']
            ));
            $endTime = $oldEndTime + $applyRow['duration'] * 2592000;
            $query = "update singer_guard set auth_time = $authTime, end_time = $endTime where singer_uid = $singerUid and uid = $uid";
            $rs = $this->getDbMain()->query($query);
        }
        // 清理緩存，更新最長守護時間
        $this->clearCache($uid, $singerUid);
        $userAttrModel = new UserAttributeModel();
    	$guardEndTime = $userAttrModel->getGuardEndTime($uid, $singerUid);
    	if($endTime > $guardEndTime)
    	{
    		$userAttrModel->setGuardEndTime($uid, $singerUid, $endTime);
    	}
/*
        $guardEndTime = $this->userAttrModel->getStatusByUid($uid, 'guard_end_time');
        if ($endTime > $guardEndTime) {
            $this->userAttrModel->setStatusByUid($uid, 'guard_end_time', $endTime);
        }
*/
    }
    
    //是否为铁杆粉
    public function isDiehard($singerid, $uid){ 
        LogApi::logProcess("in isDiehard Uid=$uid, singeruid=$singerid");
        
        $sql = "select id from rcec_main.singer_guard s where s.singer_uid=$singerid 
            and s.uid = $uid and s.end_time>UNIX_TIMESTAMP()";
        LogApi::logProcess("isDiehard Uid ...sql:$sql");
        $rows = $this->getDbMain()->query($sql);
        
        LogApi::logProcess("isDiehard Uid=$uid, singeruid=$singerid");
        
        if ($rows && $rows->num_rows > 0) {
            LogApi::logProcess("isDiehard is success. Uid=$uid, singeruid=$singerid");
            return true;
        }
        
        LogApi::logProcess("不是铁杆粉. Uid=$uid, singeruid=$singerid");
        
        return false;
    }
    
    public function getGuardType($uid, $singerUid)
    {
    	
    	// modified by yukl 2017-06-14
    	// 与web端讨论的结果，守护关系改为rcec_main.user_guard_identify中查询，特此说明
    	LogApi::logProcess("getGuardType Uid=" . $uid . "singeruid=" . $singerUid);	
// 		$query = "SELECT s.guardType FROM rcec_main.singer_guard s
//             LEFT JOIN cms_manager.guard_price_record gpr ON s.guardType = gpr.type
//             WHERE s.singer_uid = $singerUid AND s.uid = $uid AND s.end_time > UNIX_TIMESTAMP() 
// 		    ORDER BY s.guardToolId DESC LIMIT 0, 1";
		$query = "select identify from rcec_main.user_guard_identify where zid=$singerUid and uid=$uid";
		$rs = $this->getDbMain()->query($query);
		if (! $rs || $rs->num_rows == 0) {
			LogApi::logProcess("getGuardType error Uid=$uid, singeruid=$singerUid, sql:$query");
			return 0;
		}
		else
		{
			$row = $rs->fetch_assoc();
			//$guardType = (int)$row['guardType'];
			$guardType = (int)$row['identify'];
			
			return $guardType;
		}
    }

    public function getGuardEndTime($uid, $singerUid)
    {
    	// 做兼容，客户端判断是否是守护只需判断guardType字段即可
    	return 9999999999;
    	
    	LogApi::logProcess("getGuardEndTime Uid=" . $uid . "singeruid=" . $singerUid);	
    	$endTime = 0;
//     	$endTime = $this->userAttrModel->getGuardEndTime($uid, $singerUid);
//     	if(empty($endTime))
//     	{
    		//$query = "select * from singer_guard where singer_uid = $singerUid and uid = $uid";
        $query = "SELECT * FROM rcec_main.singer_guard s
            LEFT JOIN cms_manager.guard_price_record gpr ON s.guardType = gpr.type
            WHERE s.singer_uid = $singerUid AND s.uid = $uid AND s.end_time > UNIX_TIMESTAMP()
            ORDER BY s.guardToolId DESC LIMIT 0, 1";
		$rs = $this->getDbMain()->query($query);
		if (! $rs || $rs->num_rows == 0) {
			LogApi::logProcess("getGuardEndTime error Uid=" . $uid . "singeruid=" . $singerUid);
			return $endTime;
		}
		else
		{
		    $userAttrModel = new UserAttributeModel();
			$row = $rs->fetch_assoc();
			$endTime = $row['end_time'];
			$userAttrModel->setGuardEndTime($uid, $singerUid, $endTime);
			LogApi::logProcess("getGuardEndTime Uid=" . $uid . "singeruid=" . $singerUid);
			return $endTime;
		}
//     	}
    	LogApi::logProcess("getGuardEndTime cache Uid=" . $uid . "singeruid=" . $singerUid);
        return $endTime;
    }

    public function clearCache ($uid, $singerUid)
    {
        $this->getRedisMaster()->del('singer_guard:' . $uid);
        $this->getRedisMaster()->del('singer_guard_for:' . $uid);
        $this->getRedisMaster()->del('singer_guard:' . $singerUid);
        $this->getRedisMaster()->del('singer_guard_for:' . $singerUid);
    }

    public function getCloseLevel ($closeValue)
    {
        $result = array();
        $result['closeValue'] = $closeValue; // 經驗
        for ($i = count($this->closeValueList) - 2; $i >= 0; $i --) {
            if ($closeValue >= $this->closeValueList[$i]) {
                $level = $i + 1;
                $result['closeLevel'] = $level; // 等級
                break;
            }
        }
        return $result;
    }

    public function getGuardListInfo ($uid, $isSinger = false)
    {
        $newList = array();
        $userInfoModel = new UserInfoModel();
        $sort = array();
        $list = $this->getList($uid, $isSinger);
        if ($list) {
		foreach ($list as $guard) {
			$info = array();
			if ($isSinger) {
				$info['uid'] = $guard['uid'];
				$info['nick'] = $userInfoModel->getNickName($guard['uid']);
				$info['badge'] = $userInfoModel->getBadgeList($guard['uid']);
			} else {
				$info['uid'] = $guard['singer_uid'];
				$info['nick'] = $userInfoModel->getNickName($guard['singer_uid']);
				$info['badge'] = $userInfoModel->getBadgeList($guard['singer_uid']);
			}
			$info['authTime'] = $guard['auth_time'];
			$info['endTime'] = $guard['end_time'];
			$info['dayLeft'] = ceil(($guard['end_time'] - time()) / 86400);
			$info += $this->getCloseLevel($guard['close_value']);
			$newList[$info['uid']] = $info;
			// 排序
			$sort[$info['uid']] = $guard['close_value'];
		}
        }
        $sortedList = array();
        arsort($sort);
        foreach ($sort as $sortUid => $sortCloseValue) {
            $sortedList[] = $newList[$sortUid];
        }
        return $sortedList;
    }

    public function getMyGuardList ($uid)
    {
        $newList = array();
        $userInfoModel = new UserInfoModel();
        $list = $this->getList($uid);
        if ($list) {
            foreach ($list as $guard) {
                $newList[] = $userInfoModel->getNickName($guard['singer_uid']);
            }
        }
        return $newList;
    }

    public function notifyGuards ($uid, $nick, $sid, $cid)
    {
        $key = 'last_time_notify_guard';
        $now = time();
        $userAttrModel = new UserAttributeModel();
        $lastTime = intval($userAttrModel->getStatusByUid($uid, $key));
        if (($now - $lastTime) < 3600) {
            // 兩次通知時間間隔要大於1小時
            return false;
        }
        $newList = array();
        $list = $this->getList($uid, true);
        if ($list) {
            foreach ($list as $guard) {
                // 10級或以上才能收到
                if ($guard['close_value'] >= 10755000) {
                    $newList[] = $guard['uid'];
                }
            }
        }
        if (! empty($newList)) {
            // 更新最後通知時間
            $userAttrModel->setStatusByUid($uid, $key, $now);
            // 通知守護
            $bo = new BroadcastOnline();
            $boResult = $bo->callFansByIm($newList, 
                    array(
                        'uid' => $uid,
                        'nick' => $nick,
                        'sid' => $sid,
                        'cid' => $cid
                    ), 'guard');
        }
        return true;
    }

    public function getCloseValue ($uid, $singerUid)
    {
        $list = $this->getList($uid);
        if ($list) {
            foreach ($list as $value) {
                if ($value['singer_uid'] == $singerUid) {
                    return $value['close_value'];
                }
            }
        }
        return false;
    }

    public function getCharmRate ($level)
    {
        $level --;
        if (isset($this->charmRateList[$level])) {
            $rate = $this->charmRateList[$level] * 0.01;
        } else {
            $rate = 1;
        }
        return $rate;
    }

    public function getEffectText ($nick)
    {
        $packages = array(
            '<font color="#d65c02">XXX祝大家聖誕快樂，新年快樂！</font>' => 0.3,
            '<font color="#d65c02">XXX帶領守護天使光臨，佑護大家2014年身體健康,笑口常開^^</font>' => 0.3,
            '<font color="#d65c02">XXX聽聞2013年度盛典正在進行，特乘私人飛機為賣上主播捧場！</font>' => 0.2,
            '<font color="#d65c02">XXX攜帶【逢考必過】護身符飄過，期末考再也不用擔心了XD</font>' => 0.2,
        );
        $max = 100;
        $number = rand(1, $max);
        $start = 1;
        foreach ($packages as $text => $rate) {
            $end = $start + $max * $rate - 1;
            if ($number >= $start && $number <= $end) {
                return str_replace('XXX', $nick, $text);
            }
            $start = $end + 1;
        }
        return $nick . '靜悄悄的進來了。';
    }

    public function getMaxCloseLevel ($uid)
    {
        $userAttrModel = new UserAttributeModel();
        $key = 'guard_max_close_value';
        $value = $userAttrModel->getStatusByUid($uid, $key);
        if (empty($value)) {
            return 1;
        }
        if ($value >= 47310000) {
            return 3;
        } elseif ($value >= 10755000) {
            return 2;
        } else {
            return 1;
        }
    }
    
    public function getGuardName($singerId, $guardType) {
    	$guardName = "";
    	if ($guardType == 1 ||$guardType == 2 || $guardType == 3 || $guardType == 10 || $guardType == 11) {
    		$query = "select name from cms_manager.guard_price_record where type=$guardType";
    		$rs = $this->getDbMain()->query($query);
    		if ($rs && $rs->num_rows > 0) {
    			$row = $rs->fetch_assoc();
    			$guardName = $row['name'];
    		}
    	}
    	
    	if ($guardType == 10 || $guardType == 11) {
    		$query = "select name from cms_manager.group_identify_name where group_id=$singerId and identify=$guardType";
    		$rs = $this->getDbMain()->query($query);
    		if ($rs && $rs->num_rows > 0) {
    			$row = $rs->fetch_assoc();
    			$guardName = $row['name'];
    		}
    	}
    	
    	return $guardName;
    }
}
