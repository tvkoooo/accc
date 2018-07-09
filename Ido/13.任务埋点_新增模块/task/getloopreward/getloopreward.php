<?php

$path=dirname(__FILE__);
require "$path/../../include/db.class.php";
require "$path/../../include/xlog.class.php";
require "$path/../../include/interfun.php";
include_once "$path/../../include/sysparameters.php";
include_once "$path/../../include/taskcommon.php";
$path=dirname(__FILE__);
require "$path/../../include/redis/redis_interfun.php";
include_once "$path/../../include/redis/task_info.php";

///////////////////////////////////////////////////////
$path=dirname(__FILE__);
include_once "$path/../../include/redis/task_eventHandle.php";
///////////////////////////////////////////////////////
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
	
	logs::addLog("INFO::taskinfo::getloopreward::跑环任务领取奖励  params:$params ", $logfile);
	//$start = strtotime("now");
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid") && !property_exists($jparams,"tid")) {
		//$error_logs_array = array("lost property"=>" (uid, tid)");
        //::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::getloopreward::跑环任务领取奖励  params lost uid or tid ", $logfile);
		return json_encode($retparams);
	}
	$redis = getRedis() ;
	
	$retparams->uid = $jparams->uid;
	$retparams->tid = $jparams->tid;
	$uid = (int)$jparams->uid;
	$task_id = (int)$jparams->tid;
	
	$key = "uid:$jparams->uid:tid:$jparams->tid";
	$value = $redis->incrBy($key, 1);
	$redis->expire($key, 2*24*60*60);		//设置两天过期
	if($value > 1){
	    //logs::addLog("重复领取.", $logfile);
	    logs::addLog("WARN::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id  领取时间key:$key 重复领取 ", $logfile);
	    return json_encode($retparams);
	}
	
	$date = getCurDate();//date("Y-m-d");
	$db = new db();
	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	
	$sql_1 = "select t_status from card.task_info t where t.id = $jparams->tid";
	$rows_1 = $db->query($sql_1);
	
	//logs::addLog("是否已领过？ sql:$sql, result:$rows", $logfile);
	if($row = $db->fetch_assoc($rows_1)){
	    //logs::addLog("rows:".json_encode($row), $logfile);
	    $status = (int)$row['t_status'];
	    if(5 == $status){
	        $retparams->resCode = 401;	        
	        //logs::addLog("已经领过了.", $logfile);
	        logs::addLog("WARN::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id  跑环任务领取奖励已经领奖  return data=>:".json_encode($retparams), $logfile);
	        
	        $db->disconnect() ;
	        return json_encode($retparams);
	    }
	    
		//获取redis里该任务的详情
	    $key = "looptask:uid:$jparams->uid:$date:tid:$jparams->tid";
	    $data = $redis->get($key);
	    
	    //logs::addLog("key:$key data:$data", $logfile);
	    $status_redis = 1;
	    if(!empty($data)){
	        $data = json_decode($data, TRUE);
	        $status_redis = $data['status'];	//该跑环任务领取奖励在redis里的状态
	        
	        if($data['t_total_progress'] == $data['t_finish_progress']){
	            $status_redis = 1;
	        }
	    }
	    
	    if(0 == $status || 0 == $status_redis){
	        $retparams->resCode = 402;
	        //logs::addLog("任务还未完成.", $logfile);
	        logs::addLog("INFO::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id  任务详情key:$key 任务还未完成 ", $logfile);
	         
	        $db->disconnect() ;
	        
	        $key = "uid:$jparams->uid:tid:$jparams->tid";
	        $value = $redis->incrBy($key, -1);
	        return json_encode($retparams);
	    }
	}
	else {
	    logs::addLog("WARN::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id SQL_FAIL=>sql_1:$sql_1 ", $logfile);
	}
	
	//获得用户昵称
	$u_key = 'uid:' . $jparams->uid;
	$user_v = $redis->get($u_key);
	if(empty($user_v)){
	    $query_2 = "select * from raidcall.uinfo t where t.id = $jparams->uid";
	    $rows_2 = $db->query($query_2);
	    if ($rows_2) {
	        $row = $db->fetch_assoc($rows_2);
	        $retparams->nick = base64_encode($row['nick']);
	    }
	    else {
	        logs::addLog("WARN::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id SQL_FAIL=>query:$query_2 ", $logfile);
	    }
	}else{
	    $user = json_decode($user_v, true);
	    $retparams->nick = base64_encode($user['nick']);
	}
	
	//更换轮数
	$l_key = "loop_num:uid:$jparams->uid:$date";
	$l_datatmp = $redis->get($l_key);
	
	//logs::addLog("更新前key:$l_key, value:$l_datatmp", $logfile);
	logs::addLog("INFO::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id  更新前key:$l_key, value:$l_datatmp  ", $logfile);
	
	$retparams->h_finish = false;
	
	$l_data = json_decode($l_datatmp, TRUE);
	
	$l_cur_num = $l_data['l_cur_num'];
	$h_cur_num = $l_data['h_cur_num'];
	$h_total_num = $l_data['h_total_num'];
	$l_total_num = $l_data['l_total_num'];
	
	$l_cur_num_new = $l_cur_num;
	
	if($l_cur_num >= $l_total_num && $h_cur_num > $h_total_num){
	    //已经完成了所有轮任务
	    //logs::addLog("已经完成了所有轮任务！", $logfile);
	    logs::addLog("INFO::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id  已经完成了所有轮任务 ", $logfile);
	    $db->disconnect() ;
	    return json_encode($retparams);
	}
	
	if($l_cur_num >= $l_total_num && $h_cur_num == $h_total_num){
	    $l_data['h_cur_num'] = $h_cur_num + 1;
	}else{
	    if($h_cur_num >= $h_total_num){
	        $l_data['h_cur_num'] = 1;
	        $l_data['l_cur_num'] = $l_cur_num+1;
	        $l_cur_num_new = $l_cur_num+1;
	        
	        $user_at_room_key = member_list::HashUserAtRoomKey($jparams->uid);
	        $sid = $redis->hGet($user_at_room_key, $jparams->uid);
	        $retparams->sid = (int)$sid;
	        if(!empty($sid)){
	            $retparams->h_finish = true;
	        }
	        
	        $retparams->finish_l_num = $l_cur_num;
	    }else{
	        $l_data['h_cur_num'] = $h_cur_num + 1;
	    }
	}
	$h_cur_num_new = $l_data['h_cur_num'];
	$l_value = json_encode($l_data);
	
	//获取该轮的跑环任务领取的奖励
	$sql_3 = "select t.sun, t.leaf, r.sun_revise, r.leaf_revise,
    	t.debris, t.gold, r.debris_revise, r.gold_revise from card.task_sun t
    	left join card.task_sun_revise r on r.id = $l_cur_num 
    	where t.id = $h_cur_num"; 
	$rows_3 = $db->query($sql_3);
	if($rows_3 && $row = $db->fetch_assoc($rows_3)){	    
	    $sun = (int)$row['sun'];
	    $leaf = (int)$row['leaf'];
	    $sun_revise = (int)$row['sun_revise'];
	    $leaf_revise = (int)$row['leaf_revise'];
	    $debris = (int)$row['debris'];
	    $gold = (int)$row['gold'];
	    $debris_revise = (int)$row['debris_revise'];
	    $gold_revise = (int)$row['gold_revise'];
	}else{
	    logs::addLog("WARN::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id SQL_FAIL=>sql_3:$sql_3 ", $logfile);
	    //logs::addLog("**********获取跑环任务领取奖励失败!!! uid:$jparams->uid, sql:$sql", $logfile);
	}
	
	/* //获取环数对应的奖励
	$sql = "select t.id, t.sun, t.leaf from card.task_sun t where t.id = $h_cur_num";
	$rows = $db->query($sql);
	if($row = $db->fetch_assoc($rows)){
	    $sun = (int)$row['sun'];
	    $leaf = (int)$row['leaf'];
	}
	
	//获取轮数对应的奖励系数
	$sql = "select t.id, t.sun_revise, t.leaf_revise from card.task_sun_revise t where t.id = $l_cur_num";
	$rows = $db->query($sql);
	if($row = $db->fetch_assoc($rows)){
	    $sun_revise = (int)$row['sun_revise'];
	    $leaf_revise = (int)$row['leaf_revise'];
	} */
	
	$retparams->leaf = $leaf*$leaf_revise/100;
	$retparams->sun = $sun*$sun_revise/100;
	$retparams->gold = $gold*$gold_revise/100;
	$retparams->debris = $debris*$debris_revise/100;
	
	$sql_begin = microtime(true);
	$db->query("SET AUTOCOMMIT=0");
	$db->query("BEGIN");
	
	$task_common = new TaskCommon($redis, $db);
	$flag = $task_common->AppendItemToDB($jparams->uid, $retparams->debris, $retparams->leaf, $retparams->sun, $retparams->gold);
	
	/* $query = "UPDATE rcec_main.user_attribute SET sun_num = sun_num + $retparams->sun, 
	   debris = debris + $retparams->debris, coin_balance = coin_balance + $retparams->gold,
	   leaf = leaf+$retparams->leaf WHERE uid = $jparams->uid";
	
	$rs1 = $db->query($query); */
	if (empty($flag)) {
	    //logs::addLog("$jparams->uid, 领取跑环任务领取奖励失败!!!", $logfile);
	    logs::addLog("WARN::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id 领取跑环任务领取奖励奖励失败", $logfile);
	    $db->query("ROLLBACK");
	    $db->disconnect() ;
	    
	    $key = "uid:$jparams->uid:tid:$jparams->tid";
	    $value = $redis->incrBy($key, -1);
	    return json_encode($retparams);
	}
	
	$redisKey = "user_attribute:{$jparams->uid}";
	$redis->del($redisKey);
	
	$sql_4 = "update card.task_info t set t_status=5 where t.id = $jparams->tid";
	$rs_4 = $db->query($sql_4);
	if(!$rs_4 || $db->affected_rows() <= 0){
	    
	    $db->query("ROLLBACK");
	    //$logs_array = array("error!!!, sql:"=>$sql, "result"=>$rs);
	    //logs::addLog($logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id SQL_FAIL=>sql_4:=>$sql_4", $logfile);
	    
	    $db->disconnect() ;
	    $key = "uid:$jparams->uid:tid:$jparams->tid";
	    $value = $redis->incrBy($key, -1);
	    return json_encode($retparams);
	}
	
	$retparams->resCode = 200;
	
	$key = "userleaf_uid:$jparams->uid";
	$retparams->leaf_total_num = $redis->incrBy($key, $retparams->leaf);
	
	$redis->set($l_key, $l_value);
	//logs::addLog("更新后key:$l_key, value:$l_value", $logfile);
	logs::addLog("INFO::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id  更新后key:$l_key, value:$l_value ", $logfile);
	
	//领取奖励不涉及使用道具  $isTool = 0;	
	//发送跑环任务领取奖励到事件
	{
	    //修正埋点数据
	    $task_id = $data['id'];
	    $m_key = "maidian:taskid:$task_id" ;	    
	    $redis->hset($m_key, "tasklog",4);//1产生；2开始；3完成；4领奖；5刷新；6放弃	    
	    
	    //拼装奖励信息
	    $awardItem_leaf =array(
	        "type"=>0,
	        "itemID"=>8,//叶子id是8
	        "num"=>$retparams->leaf,
	        "goods_type"=>10,//叶子goods_type是10
	    );
	    $awardItem_sun =array(
	        "type"=>0,
	        "itemID"=>11,//阳光id是11
	        "num"=>$retparams->sun,
	        "goods_type"=>16,//阳光goods_type是16
	    );
	    $awardItem_gold =array(
	        "type"=>0,
	        "itemID"=>10,//金币id是11
	        "num"=>$retparams->gold,
	        "goods_type"=>15,//金币goods_type是16
	    );
	    $awardItem_heart =array(
	        "type"=>0,
	        "itemID"=>9,//碎片（爱心）id是11
	        "num"=>$retparams->debris,
	        "goods_type"=>10,//碎片（爱心）goods_type是16
	    );	    
	    
	    $event_task = new CEventHandleTask;
		$event_task->redis = $redis;
		$event_task->db = $db;
	    $event_type = 4;//类型4：领取奖励	    
	    $awardItems = array(
	        $awardItem_leaf,
	        $awardItem_sun,
	        $awardItem_gold,
	        $awardItem_heart,
	    );
	    $event_task->taskModule_loop_event($event_type,$jparams->uid,$task_id, $l_cur_num, $h_cur_num,&$awardItems);
	}
	
	//next
	$task_info = new TaskInfo($redis, $db);
	//领取任务
	{
	    $awardItems = array();
	    $task_info->initNextDayLoopTask($jparams->uid, $l_cur_num_new,$h_cur_num_new,&$awardItems);
	}

	
	$db->query("COMMIT");
	
	$db->disconnect();
	//$end = strtotime("now");
	//$costtime = $end -$start;
	$sql_commit = microtime(true);
	$sql_usetime = $sql_commit-$sql_begin;
	
	//logs::addLog("return: costtiem:$costtime".json_encode($retparams), $logfile);
	logs::addLog("INFO::taskinfo::getloopreward::跑环任务领取奖励 uid:$uid task_id:$task_id 数据库事物时间 sql_usetime:$sql_usetime ".json_encode($retparams), $logfile);
	return json_encode($retparams);
}
?>
