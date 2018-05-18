<?php

class GiftPersistDisplayTool extends ModelBase
{
    public function __construct ()
    {
        parent::__construct();
    }
    
    public function addGlobalGiftSendInfo($giftInfo, $coinCost, $tsNow)
    {
    	  $key = "gift_global_send_info";
    	  $this->getRedisMaster()->zAdd($key, $tsNow, json_encode($giftInfo));
    }

    public function getGlobalGiftSendInfo($num, $latestTs, $lastTs)
    {
        ToolApi::logProcess('GiftPersistDisplayTool::getGlobalGiftSendInfo num:' . $num . ' latestTs:' . $latestTs . ' lastTs:' . $lastTs);
    	
        $key = "gift_global_send_info";
        $retList = array();
        
        $newRecord = 0;
        $dataOffset = $lastTs;
        $tsLast = time() - 7200;
        $i = 1;
        $list = $this->getRedisSlave()->zRevRange($key, 0, 1000, true);
        foreach ($list as $info => $score) {
            if($i > $num) {
                break;
            }
        	  
            ToolApi::logProcess('tsLast:' . $tsLast . ' score:' . $score);
            if($score < $tsLast) {
                break;
            }
        	  
            if($i == 1) {
                if($score > $latestTs) {
        	    $newRecord = 1;
        	}
            }
        	  
            if($newRecord == 1) {
                $retList[$i] = $info;
        	$i++;
            }
            else {
                if($dataOffset == 0) {
                    $retList[$i] = $info;
                    $i++;
                }
                else {
                    if($score >= $dataOffset) {
                        continue;
                    }
                    else {
                        $retList[$i] = $info;
                        $i++;
                    }
                }
            }
        }
        return $retList;
    }
	
	
	public function putAllPlatformGiftInfo($giftInfo){
		$key = "AllPlatformGiftInfo";
		$this->getRedisSlave()->set($key, json_encode($giftInfo));
	}
	public function getAllPlatformGiftInfo($count){
		$key = "AllPlatformGiftInfo";
		$giftInfoStr = $this->getRedisSlave()->get($key);
		if(null == $giftInfoStr){
			return null;
		}
		return json_decode($giftInfoStr);
	}
}

?>
