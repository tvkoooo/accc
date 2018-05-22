<?php

class ToolConsumeRecordModel extends ModelBase
{

    public function __construct()
    {
        parent::__construct();
    }

    public function consume($uid, $sid, $cid, $tool, $qty, $receiver_uid, $buy, $charmRate = 1, $isNew = false)
    {
        $tid = $tool['id'];
        $tool_category1 = $tool['category1'];
        $tool_category2 = $tool['category2'];
        $tool_price = $tool['price'];
        $total_receiver_points = 0;
        $total_receiver_charm = 0;
        $total_session_points = 0;
        $total_session_charm = 0;
        $total_game_point = 0;
        $redisKeys = array();
        $total_coins_cost = $tool['price'] * $qty;
        if ($buy == ToolModel::SPEND_RCCOIN) {
            // 扣秀幣
            $query = "UPDATE user_attribute SET coin_balance = coin_balance - $total_coins_cost
                WHERE uid =$uid AND coin_balance >= $total_coins_cost";
            $redisKeys[] = "user_attribute:{$uid}";
        } else {
            // 扣道具
            $query = "UPDATE tool_account SET tool_qty = tool_qty - $qty
                WHERE uid =$uid AND tool_id =$tid AND tool_qty >= $qty";
            $redisKeys[] = "tool_account:{$uid}";
        }
        $rs1 = $this->getDbMain()->query($query);
        if (!$rs1) {
            return false;
        }
        switch ($tool['category1']) {
            case ToolModel::TYPE_GIFT:
                // 接收方加点卷
                $total_receiver_points = $tool['receiver_points'] * $qty;
                $total_receiver_charm = floor($tool['receiver_charm'] * $qty * $charmRate);
                $total_session_points = $tool['session_points'] * $qty;
                $total_session_charm = $tool['session_charm'] * $qty;
                $activityModel = new ActivityModel();
                $total_game_point = $tool['price'] * $qty;
                $query = "UPDATE user_attribute SET
                    point_balance = point_balance + $total_receiver_points ,
                    experience = experience + $total_receiver_charm
                WHERE uid = $receiver_uid";
                $rs2 = $this->getDbMain()->query($query);
                if (!$rs2) {
                    return false;
                }
                $gift_consume = ($tool['receiver_points'] > 0 ? $tool['price'] * 0.5 * $qty : $tool['price'] * 0.5 * $qty);
                $query = "UPDATE user_attribute SET
                    gift_consume = gift_consume + $gift_consume,
                    game_point = game_point + $total_game_point
                WHERE uid = $uid";
                $rs3 = $this->getDbMain()->query($query);
                if (!$rs3) {
                    return false;
                }
                // 累積計算月度秀點
                if ($total_receiver_points > 0) {
                    $month = date('Ym', time() - 32400);
                    $this->getRedisMaster()->zIncrBy('receiver_points_' . $month, $total_receiver_points, $receiver_uid);
                }
                $redisKeys[] = "user_attribute:{$receiver_uid}";
                $redisKeys[] = "user_attribute:{$uid}";
                break;
            case ToolModel::TYPE_EFFECT:
                // 設置頻道效果
                // DBLE
                // 废弃，直接删除
                LogApi::logProcess("[DBLElog] ToolConsumeRecordModel-back:consume");
                break;
            default:
                break;
        }
        // 向用户行为记录数据库写交易记录
        $now = time();
        $query = "INSERT INTO `tool_consume_record`
                (`record_time`, `uid`, `receiver_uid`, `sid`, `cid`, `tool_id`, `tool_category1`, `tool_category2`,
                `qty`, `buy`, `tool_price`, `total_coins_cost`, `total_receiver_points`, `total_receiver_charm`,
                `total_session_points`, `total_session_charm`)
                VALUES ($now,$uid,$receiver_uid,$sid,$cid,$tid,$tool_category1,$tool_category2,
                $qty,$buy,$tool_price,$total_coins_cost,$total_receiver_points,$total_receiver_charm,
                $total_session_points,$total_session_charm)";
        $this->pushToMessageQueue('rcec_record', $query);
        if ($isNew) {
            $query = "INSERT INTO `first_gift` (`uid`, `receiver_uid`, `sid`, `cid`, `record_time`)
            VALUES ($uid, $receiver_uid, $sid, $cid, $now)";
            $this->pushToMessageQueue('rcec_record', $query);
        }
        if ($total_game_point > 0) {
            $logQuery = "insert into game_point_record (uid,record_time,type,game,num) values ($uid, $now, 1, 2, $total_game_point)";
            $this->pushToMessageQueue('rcec_record', $logQuery);
        }
        // 更新redis缓存
        $this->getRedisMaster()->del($redisKeys);
        return true;
    }
}

?>