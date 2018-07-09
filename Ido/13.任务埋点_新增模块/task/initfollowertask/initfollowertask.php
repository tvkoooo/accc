<?php

$path=dirname(__FILE__);
require "$path/../../include/db.class.php";
require "$path/../../include/xlog.class.php";
require "$path/../../include/interfun.php";
require "$path/../../include/redis/redis_interfun.php";
require "$path/../../include/redis/task_info.php";
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
function call($params, $postdata)
{
	$logfile=basename(__FILE__, '.php');
	logs::addLog("INFO::taskinfo::initfollowertask::初始化粉丝团任务  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid") && !property_exists($jparams,"singerid")) {
		//$error_logs_array = array("lost property"=>" (uid, singerid)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::initfollowertask::初始化粉丝团任务  params lost uid ", $logfile);
		return json_encode($retparams);
	}
	$db = new db();
	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	$redis = getRedis() ;
	
	//$logs_array = array("host"=>$config->db_host, "user"=>$config->db_user, 
	//					"db_passwd"=>$config->db_passwd, "db_raidcall"=>$config->db_raidcall);
    //logs::addLog($logs_array, $logfile);

    //logs::addLog("uid:$jparams->uid, singerid:$jparams->singerid", $logfile);
	$task_info = new TaskInfo($redis, $db);
	$task_info->initFollowerTasks($jparams->singerid, $jparams->uid);
	
	$db->disconnect();
	
	//$logs_array = array("return data., ret"=>json_encode($retparams));
	//logs::addLog($logs_array, $logfile);
	logs::addLog("INFO::taskinfo::initfollowertask::初始化粉丝团任务  uid:$jparams->uid singerid:$jparams->singerid retparams:".json_encode($retparams), $logfile);
	
	return json_encode($retparams) ;
}
?>
