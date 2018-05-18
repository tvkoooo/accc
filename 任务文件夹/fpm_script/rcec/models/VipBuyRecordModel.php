<?php

class VipBuyRecordModel extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function addRecord ($uid, $vipInfo)
    {
        $now = time();
        $vip = $vipInfo['vip'];
        $price = $vipInfo['vipPrice'];
        $query = "INSERT INTO `vip_buy_record`
        VALUES ($now, $uid, $vip, $price)";
        $this->pushToMessageQueue('rcec_record', $query);
    }
}
?>