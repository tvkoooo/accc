<?php

class SpecialToolModel extends ModelBase
{

    const SPEAKER_ID = 1;

    const SPEAKER_PRICE = 50;

    const SPEAKER_MAX_WORD_COUNT = 30;
    
    public function __construct ()
    {
        parent::__construct();
    }

    public function addRecord ($tool, $uid, $sid, $cid, $uidOnmic, $price, $otherData)
    {
        $now = time();
        $query = "INSERT INTO `special_tool_record` (`record_time`, `tool`, `uid`, `sid`, `cid`, `uid_onmic`, `price`)
        VALUES ($now, $tool, $uid, $sid, $cid, $uidOnmic, $price)";
        /* if ($otherData) {
            $otherData = addslashes($otherData);
            Logger::logToDataFile('special_tool_data.log', 
                    date('Ymd His') . ", uid=$uid, sid=$sid, cid=$cid, uidOnmic=$uidOnmic, otherData=$otherData \n");
        } */
        $this->pushToMessageQueue('rcec_record', $query);
    }
}
?>