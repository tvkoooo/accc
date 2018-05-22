<?php

    $gConsumeUpdateTimeMap = array();
class RankingStatApi
{
//    public static function &GetConsumeUpdateTimeMap(){
//    	global $gConsumeUpdateTimeMap;
//    	return self::$gConsumeUpdateTimeMap;
//    }
    public static function updateConsumeTime($key, $tsNow){
    	return;
    	global $gConsumeUpdateTimeMap;
    	if(null == $gConsumeUpdateTimeMap){
			file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "RankingStatApi::updateConsumeTime map is null\n", FILE_APPEND);
			$GLOBALS['gConsumeUpdateTimeMap'] = array();
			$gConsumeUpdateTimeMap = $GLOBALS['gConsumeUpdateTimeMap'];
    	}
    	if(array_key_exists($key, $gConsumeUpdateTimeMap)){
    		$gConsumeUpdateTimeMap[$key] = $tsNow;
    	}else{
    		$gConsumeUpdateTimeMap = array($key => $tsNow);
    	}
    }
    public static function isConsumeTimeout($key, $tsNow){
    	return true;
    	global $gConsumeUpdateTimeMap;
    	if(null == $gConsumeUpdateTimeMap){
			file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "RankingStatApi::isConsumeTimeout map is null\n", FILE_APPEND);
    		return true;
    	}
    	if(array_key_exists($key, $gConsumeUpdateTimeMap)){
    		// 存在
    		$consumeUpdateTime = $gConsumeUpdateTimeMap[$key];
    		if($tsNow - $consumeUpdateTime > 60*60){
    			// 一小时
    			return true;
    		}
    	}else{
    		return true;
    	}
//    	$consumeUpdateTime = $gConsumeUpdateTimeMap[$key];
//    	if($consumeUpdateTime){
//    		if($tsNow - $consumeUpdateTime > 60*60){
//    			// 一小时
//    			return true;
//    		}
//    	}else{
//    		return true;
//    	}
    	return false;
    }
}

?>