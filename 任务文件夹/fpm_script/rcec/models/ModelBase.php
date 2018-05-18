<?php

class ModelBase
{
	/*
    var $dbMain;
    var $dbRaidcall;
    var $dbRecord;
    var $dbFlower;
    var $dbChannellive;
    
    var $redisMaster;
    var $redisSlave;
    var $redisMq;
    var $redisRankMaster;
    var $redisRankSlave;
	*/
    
    /* var $sessAttrModel;
    var $userAttrModel;
    var $chanAttrModel;
    var $toolModel;
    var $toolAccoModel;
    var $toolSubsModel;
    var $settingsModel;
    var $userImageModel;
    var $userFbModel;
    var $userInfoModel;  */
	
	public function getDbMain() {
		return ServiceFactory::getService('mysql', 'rcec_main');
	}
	
	public function getDbRaidcall() {
		return ServiceFactory::getService('mysql', 'raidcall');
	}
	
	public function getDbRecord() {
		return ServiceFactory::getService('mysql', 'rcec_record');
	}
	
	public function getDbFlower() {
		return ServiceFactory::getService('mysql', 'flower');
	}
	
	public function getDbChannellive() {
		return ServiceFactory::getService('mysql', 'channellive');
	}
	public function getDbTest() {
	    return ServiceFactory::getService('mysql', 'raidcall');
	}

	public function getRedisMaster() {
		return ServiceFactory::getService('redis', 'master');
	}
	
	public function getRedisSlave() {
		return ServiceFactory::getService('redis', 'slave');
	}
	
	public function getRedisMq() {
		return ServiceFactory::getService('redis', 'mq');
	}
	
	public function getRedisRankMaster() {
		return ServiceFactory::getService('redis', 'rank_master');
	}
	
	public function getRedisRankSlave() {
		return ServiceFactory::getService('redis', 'rank_slave');
	}
	
	public function getRedisJavaUtil() {
		return ServiceFactory::getService('redis', 'java_util');
	}

    public function getRedisCback() {
        return ServiceFactory::getService('redis', 'cback');
    }

    public function __construct(){
       /*
        getDbMain() = ServiceFactory::getService('mysql', 'rcec_main');
        getDbRaidcall() = ServiceFactory::getService('mysql', 'raidcall');
        getDbRecord() = ServiceFactory::getService('mysql', 'rcec_record');
        getDbFlower() = ServiceFactory::getService('mysql', 'flower');
        getDbChannellive() = ServiceFactory::getService('mysql', 'channellive');
    
        getRedisMaster() = ServiceFactory::getService('redis', 'master');
        getRedisSlave() = ServiceFactory::getService('redis', 'slave');
        getRedisMq() = ServiceFactory::getService('redis', 'mq');
        getRedisRankMaster() = ServiceFactory::getService('redis', 'rank_master');
        getRedisRankSlave() = ServiceFactory::getService('redis', 'rank_slave');
		*/
    }

    /* public function __get($name)
    {
        // lazyload
        switch ($name) {
            // 服务对象
            case 'dbMain':
                return ServiceFactory::getService('mysql', 'rcec_main');
                break;
            case 'dbRaidcall':
                return ServiceFactory::getService('mysql', 'raidcall');
                break;
            case 'dbRecord':
                return ServiceFactory::getService('mysql', 'rcec_record');
                break;
            case 'dbFlower':
                return ServiceFactory::getService('mysql', 'flower');
                break;
            case 'dbChannellive':
                return ServiceFactory::getService('mysql', 'channellive');
                break;
            case 'redisMaster':
                return ServiceFactory::getService('redis', 'master');
                break;
            case 'redisSlave':
                return ServiceFactory::getService('redis', 'slave');
                break;
            case 'redisMq':
                return ServiceFactory::getService('redis', 'mq');
                break;
            case 'redisRankMaster':
                return ServiceFactory::getService('redis', 'rank_master');
                break;
            case 'redisRankSlave':
                return ServiceFactory::getService('redis', 'rank_slave');
                break;
            //case 'redisRaidcall':
              //  return ServiceFactory::getService('redis', 'raidcall');
                //break; 
            case 'sessAttrModel':
                return new SessionAttributeModel();
                break;
            case 'userAttrModel':
                return new UserAttributeModel();
                break;
            case 'chanAttrModel':
                return new ChannelAttributeModel();
                break;
            case 'toolModel':
                return new ToolModel();
                break;
            case 'toolAccoModel':
                return new ToolAccountModel();
                break;
            case 'toolSubsModel':
                return new ToolSubscriptionModel();
                break;
            case 'settingsModel':
                return new SettingsModel();
                break;
            case 'userImageModel':
                return new UserImageModel();
                break;
            case 'userFbModel':
                return new UserFacebookInfoModel();
                break;
            case 'userInfoModel':
                return new UserInfoModel();
                break;
            default:
                return null;
                break;
        }
    } */

    /**
     * 读数据，先从redis读，redis没有从mysql读，然后更新redis。
     *
     * @param string $key
     * @param string $query
     * @param int $ttl
     *            緩存保存的描述，0 表示永久保存
     * @return array
     */
    protected function read($key, $query, $ttl = 0, $dbHandle = 'dbMain', $setWhenEmpty = true)
    {
        $value = $this->getRedisSlave()->get($key);
        if ($value !== false) {
            return json_decode($value, true);
        } else {
            $rows = array();
			
			switch ($dbHandle)
			{
				case 'dbMain':
				$db = $this->getDbMain();
				break;
				case 'dbRaidcall':
				$db = $this->getDbRaidcall();
				break;
				case 'dbFlower':
				$db = $this->getDbFlower();
				break;
				case 'dbRecord':
				$db = $this->getDbRecord();
				break;
				case 'dbChannellive':
				$db = $this->getDbChannellive();
				default:
				$db = $this->getDbMain();
				break;
			}
			
            $rs = $db->query($query);
            if ($rs && $rs->num_rows > 0) {
                $row = $rs->fetch_assoc();
                while ($row) {
                    $rows[] = $row;
                    $row = $rs->fetch_assoc();
                }
            }
            if (!empty($rows) || $setWhenEmpty) {
                if ($ttl > 0) {
                    $this->getRedisMaster()->setex($key, $ttl, json_encode($rows));
                } else {
                    $this->getRedisMaster()->set($key, json_encode($rows));
                }
            }
            if($rs){
            	$rs->close();
            }
            return $rows;
        }
    }

    /**
     * 清理缓存
     *
     * @param string $key
     */
    protected function clean($key)
    {
        return $this->getRedisMaster()->del($key);
    }

    protected function pushToMessageQueue($db, $query)
    {
        LogApi::logProcess("in pushToMessageQueue*******************.");
        if ($db == 'rcec_record') {
            $message = json_encode(array(
                'db' => $db,
                'sql' => $query
            ));
            
            // 不再往队列写
            //$this->getRedisMq()->lPush('rcec_mq_sql', $message);
	       $rs = $this->getDbRecord()->query($query);
        }
    }

    protected function sendBadge($uid, $badgeId)
    {
        $this->getRedisMaster()->lpush('badge_mq', json_encode(array('uid' => $uid, 'badgeId' => $badgeId)));
    }

    protected function sendVip($uid, $days, $grow){
        $this->getRedisMaster()->lpush('vip_mq',json_encode(array('uid' => $uid,'days' => $days,'growPoint' => $grow)));
    }
}

?>
