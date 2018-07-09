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
	logs::addLog("INFO::taskinfo::openbox::打开宝箱  params:$params ", $logfile);
	
	$retparams = new stdClass();
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
 	$db = new db();
	$retparams->resCode = 403;	

	if (!property_exists($jparams,"uid") && !property_exists($jparams,"tid")) {
		//$error_logs_array = array("lost property"=>" (uid, tid)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::openbox::打开宝箱  params lost uid or tid", $logfile);
		return json_encode($retparams);
	}

	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	$redis = getRedis() ;
	
	//$logs_array = array("host"=>$config->db_host, "user"=>$config->db_user, 
	//					"db_passwd"=>$config->db_passwd, "db_raidcall"=>$config->db_raidcall);
    //logs::addLog($logs_array, $logfile);

	$retparams->uid = $jparams->uid;
	$retparams->tid = $jparams->tid;
	$uid = (int)$jparams->uid;
	$task_id = (int)$jparams->tid;
	
	
	$now = time();
	//设置任务状态为已删除
	$query_1 = "update card.task_info t set t_status = 5, update_time=$now where t.id = $jparams->tid" ;

	$row_1 = $db->query($query_1) ;

	//$logs_array = array("query"=>$query, "result"=>$row);
    //logs::addLog($logs_array, $logfile);
        
	if($row_1){
		$retparams->resCode = 200;
	}else{
	    logs::addLog("WARN::taskinfo::openbox::打开宝箱 uid:$uid task_id:$task_id SQL_FAIL=>query_1:$query_1 ", $logfile);
		$retparams->resCode = 101;
	}
	
	$db->disconnect();
	
	//$logs_array = array("return data., ret"=>json_encode($retparams));
	//logs::addLog($logs_array, $logfile);
	logs::addLog("INFO::taskinfo::openbox::打开宝箱 uid:$uid task_id:$task_id retparams:".json_encode($retparams), $logfile);
	
	return json_encode($retparams) ;
}
?>
