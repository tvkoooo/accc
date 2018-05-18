<?php

class HeartConvertRecordModel extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * 扣银豆，增加用户爱心。
     * 注意：因为操作的是三个db，所以不能使用事务机制。
     *
     * @param int $uid            
     * @param int $silver            
     * @param int $qty            
     */
    public function convert ($uid, $silver, $qty)
    {
        // 扣用户银豆
        $userInfoModel = new UserInfoModel();
        $rs = $userInfoModel->updateSilver($uid, $silver);
        if (! $rs) {
            return 109; // 没有足够的银豆
        }
        // 增加用户的爱心并更新redis缓存
        $userAttrModel = new UserAttributeModel();
        $rs = $userAttrModel->addHeartByUid($uid, $qty);
        if (! $rs) {
            return 106; // 內部錯誤
        }
        // 記錄最後換愛心時間
        $now = time();
        $userAttrModel->setStatusByUid($uid, 'heart_convert_time', $now);
        // 更新redis缓存
        $redisKeys[] = 'user_attribute:' . $uid;
        $this->getRedisMaster()->del($redisKeys);
        // 写交易记录
        $query = "INSERT INTO `heart_convert_record` (`record_time`, `uid`, `qty`, `silver`) 
                VALUES ($now, $uid, $qty, $silver)";
        $this->pushToMessageQueue('rcec_record', $query);
        return 0;
    }

    public function convertInterval ($uid)
    {
        $userAttrModel = new UserAttributeModel();
        $recordTime = $userAttrModel->getStatusByUid($uid, 'heart_convert_time');
        if ($recordTime) {
            $interval = time() - $recordTime;
            if ($interval < 259200) { // 3600*24*3
                return ceil((259200 - $interval) / 3600);
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
}