<?php

$path=dirname(__FILE__);
require "$path/../../include/db.class.php";
require "$path/../../include/xlog.class.php";
require "$path/../../include/interfun.php";
require "$path/../../include/redis/redis_interfun.php";
require "$path/../../include/redis/task_info.php";
///////////////////////////////////////////////////////
//任务的刷新时间是：过了凌晨5点才算第二天
function is2day(){
    $now = strtotime("now");
    $daybegin=strtotime(date("Ymd"));
    $hour5=$daybegin+5*60*60;
     
    //没到5点不算第二天
    if($now < $hour5){
        return false;
    }

    return true;
}
function getCurDate(){
    $flag = is2day();
    if(empty($flag) || !$flag){
        $date = date("Y-m-d",strtotime("-1 day"));
    }else{
        $date = date("Y-m-d");
    }

    return $date;
}
///////////////////////////////////////////////////////
function call($params, $postdata)
{
	$logfile=basename(__FILE__, '.php');
	logs::addLog("INFO::taskinfo::inittaskinfo::初始化任务信息  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uids")) {
		//$error_logs_array = array("lost property"=>" (uids)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::inittaskinfo::初始化任务信息  params lost uids ", $logfile);
		return json_encode($retparams);
	}
	
	if(empty($jparams->uids)){
	    return json_encode($retparams);
	}
	
	/* $now = strtotime("now");
	$daybegin=strtotime(date("Ymd"));
	$hour5=$daybegin+5*60*60;
	
	//没到5点不算第二天
	if($now < $hour5){
	    return json_encode($retparams);
	} */
	
	$flag = is2day();
	if(empty($flag)){
	    return json_encode($retparams);
	}
	
	$redis = getRedis() ;
	
	$date = getCurDate();
	$key = "timer_inittask:$date";
	$nCount = $redis->incrBy($key, 1);
	$redis->expire($key, 24*60*60);
	if($nCount > 1){
	    return json_encode($retparams);
	}
	
	$now = strtotime("now");
	//logs::addLog("auto init task, time:$now", $logfile);
	logs::addLog("INFO::taskinfo::inittaskinfo::初始化任务信息  系统自动初始化任务时间  time:$now ", $logfile);
	
	$db = new db();
	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	
	//$logs_array = array("host"=>$config->db_host, "user"=>$config->db_user, 
	//					"db_passwd"=>$config->db_passwd, "db_raidcall"=>$config->db_raidcall);
    //logs::addLog($logs_array, $logfile);

    //logs::addLog("uids:".json_encode($jparams->uids), $logfile);
    logs::addLog("INFO::taskinfo::inittaskinfo::初始化任务信息  系统自动初始化任务的用户  uids:".json_encode($jparams->uids), $logfile);
    
    $task_info = new TaskInfo($redis, $db);

//     foreach ($jparams->uids as $uid){
//         $task_info->initTasks($uid);
//     }

    for($iii=0;$iii < 1024; $iii++){
        $it = NULL;
        $redis_key = "vnc:sid2uid:$iii";
        /* Don't ever return an empty array until we're done iterating */
        $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
        while($arr_keys = $redis->hScan($redis_key, $it)) {
            foreach($arr_keys as $str_sid => $str_uid) {
                $task_info->initTasks($str_uid);
            }
        }        
    }

	$db->disconnect();
	
	//$logs_array = array("return data., ret"=>json_encode($retparams));
	//logs::addLog($logs_array, $logfile);
	logs::addLog("INFO::taskinfo::inittaskinfo::初始化任务信息  retparams:".json_encode($retparams), $logfile);

}
?>
