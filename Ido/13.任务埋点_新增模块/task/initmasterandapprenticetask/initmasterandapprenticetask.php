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
	logs::addLog("INFO::taskinfo::initmasterandapprenticetask::初始化师徒任务  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"master_uid") && !property_exists($jparams,"disciple_uid")) {
		//$error_logs_array = array("lost property"=>" (master_uid, disciple_uid)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::initmasterandapprenticetask::初始化师徒任务  params lost master_uid or disciple_uid ", $logfile);
		return json_encode($retparams);
	}
	$db = new db();
	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	$redis = getRedis() ;
	
	//$logs_array = array("host"=>$config->db_host, "user"=>$config->db_user, 
	//					"db_passwd"=>$config->db_passwd, "db_raidcall"=>$config->db_raidcall);
    //logs::addLog($logs_array, $logfile);

    //logs::addLog("master_uid:$jparams->master_uid, disciple_uid:$jparams->disciple_uid", $logfile);
	$task_info = new TaskInfo($redis, $db);
	$task_info->initMasterAndApprenticeTasks($jparams->master_uid, $jparams->disciple_uid);
	
	$db->disconnect();
	
	//$logs_array = array("return data., ret"=>json_encode($retparams));
	//logs::addLog($logs_array, $logfile);
	logs::addLog("INFO::taskinfo::initmasterandapprenticetask::初始化师徒任务 master_uid:$jparams->master_uid, disciple_uid:$jparams->disciple_uid retparams:".json_encode($retparams), $logfile);
	
	return json_encode($retparams) ;
}
?>
