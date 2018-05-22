<?php

class HeartSendRecordModel extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function send ($uid, $receiver_uid, $qty, $charm)
    {
        // getDbMain()->autocommit(FALSE);
        $redisKeys = array();
        // 扣用户的爱心值
        $rs1 = $this->getDbMain()->query(
                "UPDATE user_attribute SET heart = heart - $qty
                WHERE uid = $uid and heart >= $qty ");
        if (! $rs1 || $this->getDbMain()->affected_rows != 1) {
            // getDbMain()->rollback();
            return 108; // 愛心不足
        }
        $redisKeys[] = 'user_attribute:' . $uid;
        // 增加接收方用户的魅力值和經驗
        $experience = $qty * 100;
        $rs2 = $this->getDbMain()->query(
                "UPDATE user_attribute SET 
                    charm = charm + $charm , 
                    experience = experience + $experience
                WHERE uid = $receiver_uid ");
        if (! $rs2) {
            // getDbMain()->rollback();
            return 106; // 事務出錯
        }
        $redisKeys[] = 'user_attribute:' . $receiver_uid;
        // $success = getDbMain()->commit();
        // 向用户行为记录数据库写交易记录
        $now = time();
        $query = "INSERT INTO `heart_send_record`
                    (`record_time`, `uid`, `receiver_uid`, `qty`, `charm`)
                    VALUES ($now, $uid, $receiver_uid, $qty, $charm)";
        $this->pushToMessageQueue('rcec_record', $query);
        // 更新redis缓存
        $this->getRedisMaster()->del($redisKeys);
        return 0; // 成功
    }
}
?>