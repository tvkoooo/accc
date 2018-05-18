<?php

class ToolBuyRecordModel extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function addRecord ($uid, $toolInfo, $qty)
    {
        $now = time();
        $toolId = $toolInfo['id'];
        $category1 = $toolInfo['category1'];
        $category2 = $toolInfo['category2'];
        $price = $toolInfo['price'];
        $consumeType = $toolInfo['consume_type'];
        $totalCost = $price * $qty;
        $query = "INSERT INTO `tool_buy_record`
        VALUES ($now, $uid, $toolId, $category1, $category2, $price, $qty, $consumeType, $totalCost)";
        $this->pushToMessageQueue('rcec_record', $query);
    }
}
?>