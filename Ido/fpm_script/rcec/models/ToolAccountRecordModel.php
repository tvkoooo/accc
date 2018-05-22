<?php

class ToolAccountRecordModel extends ModelBase
{

    const FROM_LUCKYDRAW = 1;

    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * $from:
     * 1 = 秀場砸蛋
     * 2 = 成長禮包活動
     * 3 = 幸運星活動
     * 4 = 秀場寶箱
     * 5 = 首充活動
     * 6 = 內部發放
     * 7 = 秀場運營活動（比如端午活動）
     * 8 = 合作方外部導入
     * 9 = 每日登陸禮包
     * 10 = 補償
     * 11 = 合成道具
     */
    public function addRecord ($uid, $from, $tool_id, $qty)
    {
        $now = time();
        $query = "INSERT INTO `tool_account_record` (`record_time`, `uid`, `from`, `tool_id`, `qty`)
        VALUE ($now, $uid, $from, $tool_id, $qty)";
        $this->pushToMessageQueue('rcec_record', $query);
    }
}
?>