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
	//logs::addLog("params:".json_encode($params)." postdata:".json_encode($postdata), $logfile);
	logs::addLog("INFO::taskinfo::triggerchattask::检查粉丝聊天任务  params:$params ", $logfile);
	$retparams = new stdClass();
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
 	$db = new db();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid") && !property_exists($jparams,"type")) {
		//$error_logs_array = array("lost property"=>" (uid,type)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::triggerchattask::检查粉丝聊天任务  params lost uid or type", $logfile);
		return json_encode($retparams);
	}

	$uid = $jparams->uid;
	$type = $jparams->type;

	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	$redis = getRedis() ;
	
	$retparams->data = array(); 

	//logs::addLog("triggerchattask uid= ".$uid." type= ".$type,$logfile);
	$num = 1;
	$target_type = 32;
	$extra_param = $type;
	$task_info = new TaskInfo($redis, $db);
	
	$task_info->getAwards($uid, $target_type, $num, $extra_param);
	
	$retparams->res_code = 200;
	$retparams->uid = $uid;
	$retparams->chatflag = $task_info->checkChatTaskFlag($uid);	//检查一下还要不要再发trigger信令了

    $db->disconnect() ;

    //$logs_array = array("triggerchattask return data., ret"=>json_encode($retparams));
	//logs::addLog($logs_array, $logfile);
    logs::addLog("INFO::taskinfo::triggerchattask::检查粉丝聊天任务 uid:$uid retparams:".json_encode($retparams), $logfile);

    return json_encode($retparams) ; 
}
?>
