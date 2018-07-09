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
	logs::addLog("INFO::taskinfo::starttask::主播开启任务  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid") && !property_exists($jparams,"tid")) {
		//$error_logs_array = array("lost property"=>" (uid, tid)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::starttask::主播开启任务  params lost uid or tid", $logfile);
		return json_encode($retparams);
	}
	$db = new db();
	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	$redis = getRedis() ;
	
	//$logs_array = array("host"=>$config->db_host, "user"=>$config->db_user, 
	//					"db_passwd"=>$config->db_passwd, "db_raidcall"=>$config->db_raidcall);
    //logs::addLog($logs_array, $logfile);

	$retparams->uid = $jparams->uid;
	$retparams->tid = $jparams->tid;
	$uid = (int)$jparams->uid;
	$task_id = (int)$jparams->tid;
	
	$date = getCurDate();//date("Y-m-d");
	$key = "taskstart::uid:$jparams->uid:$date";
	$data = $redis->get($key);
	//logs::addLog("**1111********key:$key, data:".json_encode($data), $logfile);
    if(empty($data)){
    	$redis->set($key, $retparams->tid);
    }else{
    	$retparams->resCode = 111;
	    $db->disconnect() ;
		//$logs_array = array("已有开启任务，return data. ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::starttask::主播开启任务  uid:$uid task_id:$task_id 主播开启任务key:$key 已有开启任务，开启失败", $logfile);		
		return json_encode($retparams) ;
    }
    $now = time();
	//设置任务状态为已开启
	$query_1 = "update card.task_info t set t_status = 3, update_time=$now where t.id = $jparams->tid" ;
			
	//$logs_array = array("query"=>$query);
    //logs::addLog($logs_array, $logfile);
    
    //logs::addLog("starttask params".$params, $logfile);
    
	$row_1 = $db->query($query_1) ;

	//$logs_array = array("query"=>$query, "result"=>$row);
    //logs::addLog($logs_array, $logfile);

	if($row_1){
		$retparams->resCode = 200;
		
		$key = "singeruid:$jparams->uid:$date:tid:$jparams->tid";		 
		$value = $redis->get($key);
		if(empty($value)){
		   $key = "singerlast_task:uid:$jparams->uid:$date:tid:$jparams->tid";
		   $value = $redis->get($key);
		}
		$data = json_decode($value, TRUE);
		
		//设置任务状态为已开启
		$data['status'] = 3;
		$redis->set($key, json_encode($data));
		
		//logs::addLog("**********uid:$jparams->uid key:$key, data:".json_encode($data), $logfile);
		
		$query_2 = "select tl.id as tool_id, tl.name as tool_name, tl.icon as tool_icon, 
				 t.t_total_progress, s.sid from card.task_info t 
			     left join card.task_conf tc on tc.id = t.t_id
			     left join rcec_main.tool tl on tl.id = tc.target_params2
			     left join raidcall.sess_info s on s.owner = t.uid 
		         where t.id = $jparams->tid";

		$rows_2 = $db->query($query_2);

		if($row = $db->fetch_assoc($rows_2)){
			$retparams->tool_id = (int)$row['tool_id'];
			$retparams->tool_name = base64_encode($row['tool_name']);
			$retparams->tool_icon = base64_encode($row['tool_icon']);
			$retparams->t_total_progress = (int)$row['t_total_progress'];
			$retparams->sid = (int)$row['sid'];
		}
		else 
		{
		    logs::addLog("WARN::taskinfo::starttask::主播开启任务  uid:$uid task_id:$task_id SQL_FAIL=>query_2:$query_2 ", $logfile);
		}
		
		//任务埋点状态登记缓存修正		
		$m_key = "maidian:taskid:$task_id" ;
		$redis->hset($m_key, "tasklog",2);//1产生；2开始；3完成；4领奖；5刷新；6放弃
		
		
		//主播任务事件埋点
		{
		    $event_task = new CEventHandleTask;
			$event_task->redis = $redis;
			$event_task->db = $db;
		    $event_type = 2;//类型2：主播任务开启
		    $awardItems = array();
		    $uid = $jparams->uid;
		    $id  = $jparams->tid;
		    $event_task->taskModule_singer_event($event_type,$uid, $id,&$awardItems);
		}
		
		
		
	}else{
	    logs::addLog("WARN::taskinfo::starttask::主播开启任务  uid:$uid task_id:$task_id SQL_FAIL=>query:$query_1 ", $logfile);
		$redis->del($key);
		$retparams->resCode = 101;
	}
	
	$db->disconnect();
	
	//$logs_array = array("return data., ret"=>json_encode($retparams));
	//logs::addLog($logs_array, $logfile);
	logs::addLog("INFO::taskinfo::starttask::主播开启任务  uid:$uid task_id:$task_id retparams:".json_encode($retparams), $logfile);
	
	return json_encode($retparams) ;
}
?>
