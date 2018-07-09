<?php

$path=dirname(__FILE__);
require "$path/../../include/db.class.php";
require "$path/../../include/xlog.class.php";
require "$path/../../include/interfun.php";
require "$path/../../include/redis/redis_interfun.php";
//require "$path/../../include/redis/task_info.php";
///////////////////////////////////////////////////////
$path=dirname(__FILE__);
include_once "$path/../../include/redis/task_eventHandle.php";
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

function call($params, $postdata)
{
	$logfile=basename(__FILE__, '.php');
	logs::addLog("INFO::taskinfo::droptask::主播放弃任务  params:$params ", $logfile);
	$retparams = new stdClass();
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
 	$db = new db();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid") && !property_exists($jparams,"tid")) {
		//$error_logs_array = array("lost property"=>" (uid, tid)");
        //logs::addLog($error_logs_array, $logfile);
        logs::addLog("WARN::taskinfo::droptask::主播放弃任务  params lost uid or tid", $logfile);
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
	
	$sql_1 = "select t.t_status from card.task_info t where t.id = $jparams->tid";
	$rows_1 = $db->query($sql_1) ;
	if($row = $db->fetch_assoc($rows_1)){
		$status = (int)$row['t_status'];
		if(1 == $status){
		    $retparams->resCode = 111;	
		    //$logs_array = array("task is finish，return data. ret"=>json_encode($retparams));
		    //logs::addLog($logs_array, $logfile);
		    logs::addLog("INFO::taskinfo::droptask::主播放弃任务(task is finish) uid:$uid task_id:$task_id return data=>:".json_encode($retparams), $logfile);		    
		    $db->disconnect();
		    return json_encode($retparams) ;
		}
	}
	else 
	{
	    logs::addLog("WARN::taskinfo::droptask::主播放弃任务 uid:$uid task_id:$task_id SQL_FAIL=>sql_1:$sql_1 ", $logfile);
	}
    
	$now = time();
	//更新任务状态为未开启
	$query_2 = "update card.task_info t set t_status = 2, update_time=$now where t.id = $jparams->tid" ;
			
	//$logs_array = array("query"=>$query);
    //logs::addLog($logs_array, $logfile);
    
	$row_2 = $db->query($query_2) ;

	//$logs_array = array("query"=>$query, "result"=>$row);
    //logs::addLog($logs_array, $logfile);
        
	if($row_2){
		$retparams->resCode = 200;
		$date = getCurDate();//date("Y-m-d");
		$key = "taskstart::uid:$retparams->uid:$date";
	    $redis->del($key);
	    
	    $key = "singeruid:$jparams->uid:$date:tid:$jparams->tid";
	    $value = $redis->get($key);
	    if(empty($value)){
	        $key = "singerlast_task:uid:$jparams->uid:$date:tid:$jparams->tid";
	        $value = $redis->get($key);
	    }
	    $data = json_decode($value, TRUE);
		//设置任务状态为未开启
	    $data['status'] = 2;
	    $data['t_finish_progress'] = 0;
	    $redis->set($key, json_encode($data));
	    
	    //logs::addLog("**********key:$key, data:".json_encode($data), $logfile);
	    logs::addLog("INFO::taskinfo::droptask::主播放弃任务 uid:$uid task_id:$task_id "." key:$key, data:".json_encode($data), $logfile);	
	    
		$query_3 = "select s.sid from card.task_info t 
			     left join raidcall.sess_info s on s.owner = t.uid  
		         where t.id = $jparams->tid";
		$rows_3 = $db->query($query_3);
		if($row = $db->fetch_assoc($rows_3)){
			$retparams->sid = (int)$row['sid'];
		}
		else 
		{
		    logs::addLog("WARN::taskinfo::droptask::主播放弃任务 uid:$uid task_id:$task_id sql fail  sql:$query_3 ", $logfile);
		}
		
		//任务埋点状态登记缓存修正
		$task_id = $data['id'];
		$m_key = "maidian:taskid:$task_id" ;
		$redis->hset($m_key, "tasklog",6);//1产生；2开始；3完成；4领奖；5刷新；6放弃
		$redis->hset($m_key, "t_finish_progress",0);//放弃任务重置
		
		
		//主播任务事件埋点
		{
		    $event_task = new CEventHandleTask;
		    $event_task->redis = $redis;
		    $event_task->db = $db;
		    $event_type = 6;//类型6：主播任务放弃
		    $awardItems = array();		    
		    $event_task->taskModule_singer_event($event_type,$uid, $task_id,&$awardItems);
		}		
		
	}else{
		$retparams->resCode = 111;	
		//$logs_array = array("放弃任务失败，return data. ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);		
		logs::addLog("WARN::taskinfo::droptask::主播放弃任务 uid:$uid task_id:$task_id SQL_FAIL=>query_2:$query_2", $logfile);
	}	
	$db->disconnect();	
	
	//$logs_array = array("return data., ret"=>json_encode($retparams));
	//logs::addLog($logs_array, $logfile);
	logs::addLog("INFO::taskinfo::droptask::主播放弃任务 uid:$uid task_id:$task_id return data=>:".json_encode($retparams), $logfile);
	return json_encode($retparams) ;
}
?>
