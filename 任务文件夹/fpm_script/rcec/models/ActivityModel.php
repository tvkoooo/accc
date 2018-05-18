<?php

class ActivityModel extends ModelBase
{

    private  $startTime = '2013-12-16 12:00:00';

    private $closeTime = '2013-12-31 12:00:00';

    private $newSingerListKey = 'annual_party_new_singer_ranking';

    private $oldSingerListKey = 'annual_party_old_singer_ranking';
    
    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * 活動主題禮物
     */
    private $giftList = array(
        167 => 10,
        168 => 10,
    );

    /**
     * 砸蛋：各種蛋出現的概率
     */
    private $eggs = array(
        'bronze' => array(
            'price' => 8,
            'priceLow' => 3,
            'priceType' => 1, // 1=銀豆,2=秀幣
            'rate' => 0.25,
            'bestBonus' => 520,
            'package' => array(
                'GP0' => 0.299,
                'GP1Y' => 0.6,
                'GP10Y' => 0.1,
                'GP520Y' => 0.001
            )
        ),
        'silver' => array(
            'price' => 11,
            'priceLow' => 11,
            'priceType' => 2, // 1=銀豆,2=秀幣
            'rate' => 0.25,
            'bestBonus' => 660,
            'package' => array(
                'GP10D' => 0.4,
                'GP520D' => 0.078,
                'GP10B' => 0.5,
                'GP100B' => 0.02,
                'GP660B' => 0.002
            )
        ),
        'glod' => array(
            'price' => 168,
            'priceLow' => 168,
            'priceType' => 2, // 1=銀豆,2=秀幣
            'rate' => 0.25,
            'bestBonus' => 3600,
            'package' => array(
                'GP10D' => 0.13,
                'GP520D' => 0.1,
                'GP10B' => 0.3,
                'GP100B' => 0.4,
                'GP660B' => 0.05,
                'GP3600B' => 0.02
            )
        ),
        'premium' => array(
            'price' => 2688,
            'priceLow' => 2688,
            'priceType' => 2, // 1=銀豆,2=秀幣
            'rate' => 0.25,
            'bestBonus' => 26280,
            'package' => array(
                'GP1560D' => 0.27,
                'GP1880B' => 0.5,
                'GP3600B' => 0.2,
                'GP26280B' => 0.03
            )
        )
    );

    /**
     * 砸蛋：各種包裹出現的概率和獎勵
     */
    private $packages = array(
        'GP0' => array(
            'value' => 0,
            'amount' => 0,
            'list' => ''
        ),
        'GP1Y' => array(
            'value' => 1,
            'amount' => 1,
            'list' => '99'
        ),
        'GP10Y' => array(
            'value' => 10,
            'amount' => 10,
            'list' => '99'
        ),
        'GP520Y' => array(
            'value' => 520,
            'amount' => 520,
            'list' => '99'
        ),
        'GP10D' => array(
            'value' => 10,
            'amount' => 10,
            'list' => '77,78,79,83'
        ),
        'GP520D' => array(
            'value' => 520,
            'amount' => 520,
            'list' => '77,78,79,83'
        ),
        'GP1560D' => array(
            'value' => 1560,
            'amount' => 520,
            'list' => '98,100'
        ),
        'GP10B' => array(
            'value' => 10,
            'amount' => 1,
            'list' => '167'
        ),
        'GP100B' => array(
            'value' => 100,
            'amount' => 10,
            'list' => '167'
        ),
        'GP660B' => array(
            'value' => 660,
            'amount' => 66,
            'list' => '167'
        ),
        'GP1880B' => array(
            'value' => 1880,
            'amount' => 188,
            'list' => '167'
        ),
        'GP5200B' => array(
            'value' => 5200,
            'amount' => 520,
            'list' => '167'
        ),
        'GP3600B' => array(
            'value' => 3600,
            'amount' => 360,
            'list' => '167'
        ),
        'GP26280B' => array(
            'value' => 26280,
            'amount' => 2628,
            'list' => '167'
        )
    );

    /**
     * 每日登陸獎勵：禮包組合
     */
    private $loginPackets = array();

    /**
     * 活動是否進行中
     */
    public function isActivityOpen()
    {
        if (time() > strtotime($this->closeTime) || time() < strtotime($this->startTime)) {
            return false;
        }
        return true;
    }

    /**
     * 獲取砸蛋
     */
    public function getEggs()
    {
        return $this->eggs;
    }

    /**
     * 獲取砸蛋的禮包組合
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * 獲取每日登陸禮包組合
     */
    public function getLoginPackets()
    {
        return $this->loginPackets;
    }

    /**
     * 設置活動狀態
     */
    public function setStatusByUid($uid, $field, $value)
    {
        $key = 'activity_status:' . $uid;
        return $this->getRedisMaster()->hSet($key, $field, $value);
    }

    /**
     * 獲取活動狀態
     */
    public function getStatusByUid($uid, $field = false)
    {
        $key = 'activity_status:' . $uid;
        if($field == false){
            return $this->getRedisMaster()->hGetall($key);
        }
        return $this->getRedisMaster()->hGet($key, $field);
    }

    /**
     * 設置活動狀態計算器加一
     */
    public function statusIncrease($uid, $field, $value = 1)
    {
        $key = 'activity_status:' . $uid;
        return $this->getRedisMaster()->hIncrBy($key, $field, $value);
    }

    /**
     * 獲取某個用戶在某個榜單的排名信息
     */
    public function getRank($key, $uid, $getUserInfo = true, $field = '')
    {
        $data = array();
        $rank = $this->getRedisMaster()->zRevRank($key, $uid);
        if ($rank !== false) {
            $data['rank'] = $rank + 1;
            $data['score'] = $this->getRedisMaster()->zScore($key, $uid);
        } else {
            $data['rank'] = 0;
            $data['score'] = 0;
        }
        if ($getUserInfo) {
            $userAttrModel = new UserAttributeModel();
            $data += $userAttrModel->getUserInfo($uid);
        } else {
            $data['id'] = $uid;
        }
        if (!empty($field) && isset($data[$field])) {
            return $data[$field];
        } else {
            return $data;
        }
    }

    /**
     * 獲取某個榜單前十名用戶的榜單
     */
    public function getList($key, $length = 10, $getUserInfo = true)
    {
        $newList = array();
        $list = $this->getRedisMaster()->zRevRange($key, 0, $length - 1, true);
        $i = 1;
        $userAttrModel = new UserAttributeModel();
        foreach ($list as $uid => $score) {
            $data = array();
            $data['rank'] = $i++;
            $data['score'] = $score;
            if ($getUserInfo) {
                $data += $userAttrModel->getUserInfo($uid);
 //               $data['nick'] = $this->userInfoModel->getNickName($uid);
 //               $data['id'] = $uid;
            } else {
                $data['id'] = $uid;
            }
            $newList[] = $data;
        }
        return $newList;
    }

    /**
     * 把消息推入活動消息隊列（消息隊列處理進程會根據消息來更新榜單和用戶活動狀態信息）
     */
    public function pushToMq($message)
    {
        $this->getRedisMq()->lPush('activity_mq', $message);
    }

    /**
     * 判斷某個禮物是否活動禮物
     */
    public function isActivityGift($giftId)
    {
        return array_key_exists($giftId, $this->giftList) ? true : false;
    }

    /**
     * 獲取實時排名變動情況，回報到客戶端
     */
    public function getRankChange($uid, $singerUid, $tool, $qty)
    {
        if (!$this->isActivityOpen() or !$this->isActivityGift($tool['id'])) {
            return false;
        }
        $oldPit = $this->getRedisMaster()->sismember('annual_party_old_singer',$singerUid);
        if($oldPit){
            $result['member'] = 2;
        }else{
            $newPit = $this->getRedisMaster()->sismember('annual_party_new_singer',$singerUid);
            if($newPit){
                $result['member'] = 1;
            }
        }
        if(empty($oldPit) && empty($newPit)){
            return false;
        }
        $rankChangeNotified = $this->statusIncrease($singerUid,'annual_rank_change_notified');
        if($rankChangeNotified == 1){
            $result['cmd'] = 'BActivityRankChange';
            $rank = $this->getStatusByUid($singerUid,'annual_rank');
            if(!empty($rank)){
                $result['rank'] = $rank;
                $result['singerUid'] = $singerUid;
                $rankChange = $this->getStatusByUid($singerUid,'annual_rank_change');
                if($rankChange == '-'){
                    $result['rankChange'] = 0;
                    return $result;
                }elseif(!empty($rankChange)){
                    $result['rankChange'] = 1;
                    $this->setStatusByUid($singerUid,'annual_rank_change',0);
                    return $result;
                }
                return $result;
            }
        }
        return false;
    }

    /**
     * 獲取主播當前活動的排名信息（一般是活動的總收禮榜）
     */
    public function getSingerRank($userAttr)
    {
        $singerUid = $userAttr['uid'];
        $change = array();
        if($this->getRedisMaster()->sismember('annual_party_new_singer',$singerUid)){
            $change['member'] = 1;
            $change['rank'] = $this->getRank($this->newSingerListKey, $singerUid, false,'rank');
        }elseif($this->getRedisMaster()->sismember('annual_party_old_singer',$singerUid)){
            $change['member'] = 2;
            $change['rank'] = $this->getRank($this->oldSingerListKey, $singerUid, false,'rank');
        }else{
            $change['member'] = 0;
            $change['rank'] = 0;
        }
        return $change;
    }

    /**
     * 獲取活動的所有榜單信息、用戶在每個榜單的排名信息以及相關信息
     */
    public function getActivityInfo($uid, $recUid)
    {
        $size = 20;
        $newSingerRankingList = $this->newSingerListKey;
        $oldSingerRankingList = $this->oldSingerListKey;

        $info['oldList'] = $this->getList($oldSingerRankingList, $size);
        $info['oldNameList'] = $this->getList($oldSingerRankingList, $size);
        $info['newList'] = $this->getList($newSingerRankingList, $size);
        $info['newNameList'] = $this->getList($newSingerRankingList, $size);
        $info['oldRank'] = $this->getRank($oldSingerRankingList,$uid,false);
        $info['newRank'] = $this->getRank($newSingerRankingList,$uid,false);
        return $info;
    }

    /**
     * 獲取某個榜單（周榜）的歷史記錄
     */
    public function getWeekRankHistory($key, $length = 10, $getUserInfo = true)
    {
        $history = array();
        $newList = array();
        $week = date('W') - 1;
        $start = date('W', strtotime($this->startTime));
        $userInfoModel = new UserInfoModel();
        while ($week >= $start) {
            $postfix = '_week_' . $week;
            $text = $week - $start;
            $item = array(
                'week' => $text
            );
            $list = $this->getRedisMaster()->zRevRange($key . $postfix, 0, $length - 1, true);
            $i = 1;
            $newList = array();
            if ($list) {
                foreach ($list as $uid => $score) {
                    $data = array();
                    $data['rank'] = $i++;
                    $data['score'] = $score;
                    if ($getUserInfo) {
                        //$data += $this->userAttrModel->getUserInfo($uid);
                        $data['nick'] = $userInfoModel->getNickName($uid);
                        $data['uid'] = $uid;
                    } else {
                        $data['id'] = $uid;
                    }
                    $newList[] = $data;
                }
            }
            $item['list'] = $newList;
            $history[] = $item;
            $week -= 1;
        }
        return $history;
    }

    /**
     * 獲取某個榜單（日榜）的歷史記錄
     */
    public function getDayRankHistory($key, $length = 10, $getUserInfo = true)
    {
        $history = array();
        $timestamp = strtotime('yesterday');
        $j = 5;
        $userInfoModel = new UserInfoModel();
        while ($timestamp >= strtotime($this->startTime) and $j > 0) {
            $date = date('Ymd', $timestamp);
            $item = array(
                'date' => $date
            );
            $list = $this->getRedisMaster()->zRevRange($key . '_' . $date, 0, $length - 1, true);
            $i = 1;
            $newList = array();
            if ($list) {
                $j--;
                foreach ($list as $uid => $score) {
                    $data = array();
                    $data['rank'] = $i++;
                    $data['score'] = $score;
                    if ($getUserInfo) {
                        //$data += $this->userAttrModel->getUserInfo($uid);
                        $data['nick'] = $userInfoModel->getNickName($uid);
                        $data['uid'] = $uid;
                    } else {
                        $data['id'] = $uid;
                    }
                    $newList[] = $data;
                }
            }
            $item['list'] = $newList;
            $history[] = $item;
            $timestamp -= 86400;
        }
        return $history;
    }

    /**
     * 獲取活動每日禮包
     */
    public function getDailyPacket($uid)
    {
        $userFbModel = new UserFacebookInfoModel();
        if ($this->isActivityOpen()
            and $userFbModel->isFbBound($uid)
            and !$this->getDailyPacketStatus($uid)
        ) {
            if($this->statusIncrease($uid, 'daily_packet_lock') == 1){
                $giftNum = 10;
                $giftId = 168;
                $toolAccoModel = new ToolAccountModel();
                $toolAccoModel->update($uid, $giftId, $giftNum);
                $toolAccoRecordModel = new ToolAccountRecordModel();
                $toolAccoRecordModel->addRecord($uid, '7', $giftId, $giftNum);
                $result = array(
                    'giftId' => $giftId,
                    'giftNum' => $giftNum,
                );
                $this->setStatusByUid($uid, 'last_time_get_daily_packet', time());
                $this->setStatusByUid($uid, 'daily_packet_lock', 0);
                return $result;
            }else{
                $this->statusIncrease($uid, 'daily_packet_lock',-1);
                return false;
            }
        } else {
            return false;
        }
    }

    public function getHourlyPacket($uid)
    {
        $userFbModel = new UserFacebookInfoModel();
        if ($this->isActivityOpen()
            and $userFbModel->isFbBound($uid)
            and !$this->getHourlyPacketStatus($uid)
            and $this->statusIncrease($uid, 'hourly_packet_lock') == 1
        ) {
            $userAttrModel = new UserAttributeModel();
            $userAttrModel->addGamePoint($uid, 1000, FlightChessModel::$gameId,
                FlightChessModel::$dailyGamePoint);
            $result = array('gamePoint' => 1000);
            $this->setStatusByUid($uid, 'last_time_get_hourly_packet', time());
            $this->setStatusByUid($uid, 'hourly_packet_lock', 0);
            return $result;
        } else {
            $this->setStatusByUid($uid, 'hourly_packet_lock', 0);
            return false;
        }
    }

    /**
     * 獲取活動期間的每日活動禮包的狀態
     */
    public function getDailyPacketStatus($uid)
    {
        $lastTime = $this->getStatusByUid($uid, 'last_time_get_daily_packet');
        if (date('ymd') == date('ymd', $lastTime)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getHourlyPacketStatus($uid)
    {
        $lastTime = $this->getStatusByUid($uid, 'last_time_get_hourly_packet');
        if (date('ymdH') == date('ymdH', $lastTime)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getGamePoint($tId)
    {
        if (isset($this->giftList[$tId])) {
            return $this->giftList[$tId];
        } else {
            return 0;
        }
    }

    public function sendVipReward($uid,$days,$grow){
        $this->sendVip($uid,$days,$grow);
    }

    public function sendBadgeReward($uid,$badgeId){
        $this->sendBadge($uid,$badgeId);
    }

	    /**
     * 獲取某個榜單前十名用戶的榜單,和getList相比，多一个字段id
     */
    public function getList2($key, $singerUid, $length = 10, $getUserInfo = true)
    {
        ToolApi::logProcess('ActivityModel::getList2 entry... key ' . $key);
    	
        $newList = array();
        //if($singerUid == 10002360){
        //	$key = 'WeekSidUsrConsumeRank:101077';
        //	$length = 10;
        //	return $newList;
        //}
        $list = $this->getRedisRankMaster()->zRevRange($key, 0, $length - 1, true);
        $i = 1;
        if(null == $list){
       		ToolApi::logProcess('ActivityModel::getList2 entry... zRevRange return null');
        	return $newList;
        }
        
        $userAttrModel = new UserAttributeModel();
        foreach ($list as $uid => $score) {
            $data = array();
            $data['rank'] = $i++;
            $data['coin'] = $score;
            if ($getUserInfo) {
                $data += $userAttrModel->getUserInfo($uid);
            } else {
                $data['uid'] = $uid;
            }
            /*
	      $singerGuardModel = new SingerGuardModel();
	      $closeValue = $singerGuardModel->getCloseValue($uid, $singerUid);
	      if($closeValue) {
	      	$closeResult = $singerGuardModel->getCloseLevel($closeValue);
	      	if($closeResult) {
	      		$data['guardLevel'] = $closeResult['closeLevel'];
	      	}
	      	else {
	      		$data['guardLevel'] = 0;
	      	}
	      }
	      else {
	      	$data['guardLevel'] = 0;
	      }*/
	      $data['guardLevel'] = 0;
          	$newList[] = $data;
        }
        ToolApi::logProcess('ActivityModel::getList2 completed...');
        return $newList;
    }
}
