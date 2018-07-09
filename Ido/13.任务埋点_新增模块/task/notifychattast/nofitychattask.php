<?php

$path=dirname(__FILE__);
require "$path/../../include/db.class.php";
require "$path/../../include/xlog.class.php";
require "$path/../../include/interfun.php";
require "$path/../../include/redis/redis_interfun.php";
//require "$path/../../include/redis/task_info.php";
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////


function call($params, $postdata)
{
	$logfile=basename(__FILE__, '.php');
	
	//logs::addLog("params:".json_encode($params)." postdata:".json_encode($postdata), $logfile);
	logs::addLog("INFO::taskinfo::notifychattast::nofitychattask::不知道是否存在的任务  params:$params ", $logfile);
	
	$retparams = new stdClass();
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
 	$db = new db();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid")) {
		//$error_logs_array = array("lost property"=>" (uid)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::notifychattast::nofitychattask::不知道是否存在的任务  params lost uid ", $logfile);
		return json_encode($retparams);
	}

	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	$redis = getRedis() ;

	//$logs_array = array("host"=>$config->db_host, "user"=>$config->db_user, 
	//					"db_passwd"=>$config->db_passwd, "db_raidcall"=>$config->db_raidcall);
    //logs::addLog($logs_array, $logfile);

    $retparams->resCode = 200;
	$retparams->uid = $jparams->uid;

	$task_info = new TaskInfo($redis, $db);
	$retparams->send = $task_info->checkChatTaskFlag($jparams->uid);
    
    $db->disconnect() ;

    //$logs_array = array("return data., ret"=>json_encode($retparams));
	//logs::addLog($logs_array, $logfile);
    logs::addLog("INFO::taskinfo::notifychattast::nofitychattask::不知道是否存在的任务 uid:$uid retparams:".json_encode($retparams), $logfile);

    return json_encode($retparams) ; 
}
?>
