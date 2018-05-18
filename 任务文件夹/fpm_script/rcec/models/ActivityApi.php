<?php

class ActivityApi
{

    public static function getActivityInfo($params)
    {
        $returnResult = array(
            'cmd' => 'RGetActivityInfo',
            'result' => 0
        );
        $uid = $params['uid'];
        $recUid = $params['uid_onmic'];
        $activityModel = new ActivityModel();
        $isOpen = $activityModel->isActivityOpen();
        if (!$isOpen) {
            $returnResult['result'] = 138; // 已經領取過，或者活動已結束
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $returnResult['info'] = $activityModel->getActivityInfo($uid, $recUid);
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function getActivityDailyPacket($params)
    {
        $returnResult = array(
            'cmd' => 'RGetActivityDailyPacket',
            'result' => 0
        );
        $uid = $params['uid'];
        $activityModel = new ActivityModel();
        $getRs = $activityModel->getDailyPacket($uid);
        if ($getRs) {
            $returnResult['result'] = $getRs;
            $return[] = array(
                'broadcast' => 0,
                'data' => $returnResult
            );
            return $return;
        }
    }
	public static function getOrderId(){
	    static $baseOrderId = 0;
	    static $offsetOrderId = 0;
    	if($baseOrderId == 0 || $offsetOrderId >= 999){
    		$baseOrderId = intval(microtime(true)/1000);
    		$baseOrderId *= 1000;
    		$offsetOrderId = 0;
    	}
    	$offsetOrderId++;
    	return $baseOrderId + $offsetOrderId;
    }
}

?>