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

// 从缓存获取房间荣耀-阳光等级配置
function getRoomSunConfFromCache($redis)
{
    $item = array();
    $key = "h_conf_room_glory_level";
    $ret = $redis->hGetAll($key);

    if (!empty($ret)) {
        foreach ($ret as $field=>$value) {
            $item[(int)$field] = json_decode($value, true);
        }
    }

    return $item;
}

// 获取房间荣耀-阳光等级配置
function getRoomSunConf($db, $redis)
{
    $items = getRoomSunConfFromCache($redis);
    if (!empty($items)) {
        return $items;
    }

    $items = array();

    $key = "h_conf_room_glory_level";
    $sql = "SELECT * FROM card.room_sun_level";
    $rows = $db->query($sql);

    if (!empty($rows)) {
        $row = null;
        $row = $rows->fetch_assoc();
        	
        while (!empty($row)) {
            $items[(int)$row['level']] = $row;
            $redis->hSet($key, $row['level'] . "", json_encode($row));
            $row = $rows->fetch_assoc();
        }
    }
    return $items;
}

//获得主播加成
function getSingerRatio($db, $redis, $glory_total)
{
    $conf_glory_inf = getRoomSunConf($db, $redis);
    if (empty($conf_glory_inf)) {
        break;
    }
    
    $ratio = 0;
    $size = count($conf_glory_inf);
    for ($i=$size-1; $i>=0; --$i) {
        if ($glory_total >= $conf_glory_inf[$i]['glory_value']) {
            $ratio = $conf_glory_inf[$i]['anchor_sun_plus'] / 100;
            break;
        }
    } 
    
    return $ratio;
}

function call($params, $postdata)
{
	$logfile=basename(__FILE__, '.php');
	
	//logs::addLog("params:".json_encode($params), $logfile);
	logs::addLog("INFO::taskinfo::getreward::任务领奖  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid") && !property_exists($jparams,"tid")) {
		//$error_logs_array = array("lost property"=>" (uid, tid)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::getreward::任务领奖  params lost uid or tid", $logfile);
		return json_encode($retparams);
	}
	$redis = getRedis() ;
	
	$retparams->uid = $jparams->uid;
	$retparams->tid = $jparams->tid;
	$uid = (int)$jparams->uid;
	$task_id = (int)$jparams->tid;
	
	$re_key = "uid:$jparams->uid:tid:$jparams->tid";
	$value = $redis->incrBy($re_key, 1);
	$redis->expire($re_key, 2*24*60*60);		//设置两天过期
	if($value > 1){
	    $retparams->resCode = 401;
	    //logs::addLog("uid:$jparams->uid, 重复领取.", $logfile);
	    logs::addLog("WARN::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id 领取次数re_key:$re_key 重复领取", $logfile);
	    return json_encode($retparams);
	}
	
	$db = new db();
	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
		
	$sql_1 = "select t_status from card.task_info t where t.id = $jparams->tid";
	$rows_1 = $db->query($sql_1);
	if($row = $db->fetch_assoc($rows_1)){
	    $status = (int)$row['t_status'];
	    if(4 == $status){
	        //logs::addLog("用户 $jparams->uid,已经完成领取奖励", $logfile);
	        logs::addLog("WARN::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id 已经完成领取奖励", $logfile);
	        $retparams->resCode = 401;
	        $db->disconnect() ;
	        return json_encode($retparams);
	    }
	    if(0 == $status){
	        //未完成任务不能领取
	        $redis->del($re_key);
	        //logs::addLog("用户 $jparams->uid 未完成任务不能领取奖励", $logfile);
	        logs::addLog("WARN::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id 未完成任务不能领取奖励", $logfile);
	        $retparams->resCode = 402;
	        $db->disconnect() ;
	        return json_encode($retparams);
	    }
	}
	else {
	    logs::addLog("WARN::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id SQL_FAIL=>sql_1:$sql_1 ", $logfile);
	}
	
	$query_2 = "select s.sid from card.task_info t
	left join raidcall.sess_info s on s.owner = t.uid
	where t.id = $jparams->tid";
	$rows_2 = $db->query($query_2);
	if($row = $db->fetch_assoc($rows_2)){
	    $retparams->sid = (int)$row['sid'];
	}
	else {
	    logs::addLog("WARN::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id SQL_FAIL=>query_2:$query_2 ", $logfile);
	}
	
	//获取主播的该任务的奖励详情
	$query_3 = "select tc.leaf, tc.sun, tc.debris, tc.gold, tc.open_object, p.parm1 as leaf_num from card.task_info t 
	   left join card.task_conf tc on t.t_id = tc.id
	   left join card.parameters_info p on p.id = 38
	   where t.id = $jparams->tid" ;

	$rows_3 = $db->query($query_3) ;

	//$logs_array = array("query:"=>$query, "result"=>$rows);
    //logs::addLog($logs_array, $logfile);
        
	if($rows_3){
	    $sql_begin = microtime(true);
	    $db->query("SET AUTOCOMMIT=0");
	    $db->query("BEGIN");
	    
		//更新任务状态为已完成
	    $sql_4 = "update card.task_info t set t_status=4 where t.id = $jparams->tid";
	    $rs_4 = $db->query($sql_4);
	    //logs::addLog("uid:$jparams->uid, update sql:$sql, result:$rs, row size:".$db->affected_rows(), $logfile);
	    if(!$rs_4 || $db->affected_rows() <= 0){
	        $redis->del($re_key);
	        //$logs_array = array("uid:$jparams->uid, error!!!, sql:"=>$sql, "result"=>$rs);
	        //logs::addLog($logs_array, $logfile);
	        logs::addLog("WARN::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id SQL_FAIL=>sql_4:$sql_4", $logfile);
	        $db->query("ROLLBACK");
	        
	        $db->disconnect() ;
	        return json_encode($retparams) ;
	    }
	    
	    /* //begin测试回滚
	    logs::addLog('*********测试回滚', $logfile);
	    $retparams->resCode = 403;
	    $db->query("ROLLBACK");
	    return json_encode($retparams);
	    //end */
	    
		$i = 0;
		$j = 0;
		if($row = $db->fetch_assoc($rows_3)){
		    $retparams->resCode = 200;
		    
		    $retparams->leaf = (int)$row['leaf'];
			$retparams->sun = (int)$row['sun'];
		    $retparams->debris = (int)$row['debris'];
			$retparams->gold = (int)$row['gold'];
			$retparams->leaf_top = (int)$row['leaf_num'];
			
			$openObject = (int)$row['open_object'];	//开启对象，1为主播，0为系统
			if(empty($openObject)){
			    $retparams->type = 0;
			}else{
			    $retparams->type = 1;
			}
			
			$key = "userleaf_uid:$jparams->uid";
			$retparams->leaf_total_num = $redis->incrBy($key, $retparams->leaf);
			
			//获取房间荣耀值信息
			$key = "h_room_glory_inf";
			$field = "$retparams->sid:glory_total";
			$glory_total = $redis->hGet($key, $field);
			
			//logs::addLog("singerid:$retparams->uid:sid:$retparams->sid:glory_total:$glory_total", $logfile);			
			
			$ratio = getSingerRatio($db, $redis, $glory_total);
			$retparams->ratio = $ratio*100;
			//logs::addLog("singerid:$retparams->uid:sid:$retparams->sid:glory_total:$glory_total:ratio:$ratio:sunvalue:$retparams->sun", $logfile);
			if(!empty($ratio)){
			    $retparams->sun = (1+$ratio)*$retparams->sun;
			    //logs::addLog("sunvalue:$retparams->sun", $logfile);
			    logs::addLog("INFO::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id  阳光增加sunvalue:$retparams->sun", $logfile);
			}
			
			/* //获取主播旧奖励等级
			$query = "select * from raidcall.anchor_info where flag = 1 and uid = $jparams->uid";
			$rs = $db->query($query);
				
			if ($row = $db->fetch_array($rs)) {
			    on_anchor_sun_exp_add($db, $redis, $jparams->uid, $row['level_id'], $row['anchor_curr_exp'], $retparams->sun);
			} 
			
			
			$query = "select t.family_id from raidcall.anchor_info t where t.uid = $jparams->uid";
			$rs2 = $db->query($query) ;
			$family_id = 0;
	        if($rs2){
	            $rs = $db->fetch_assoc($rs2);
	            $family_id = (int)$rs['family_id'];
	        }
			
			$now = time();
			$query = "insert into rcec_record.sun_record (sid, uid, zid, time, num, type, family_id) values(0, 0, $jparams->uid, $now, $retparams->sun, 1, $family_id)";
			$rs2 = $db->query($query);
			if (!$rs2) {
			    logs::addLog('*********:添加阳光记录表失败.sql:'.$query, $logfile);
			    $retparams->resCode = 403;
			    $db->query("ROLLBACK");
			    $db->disconnect() ;
			    return json_encode($retparams);
			} */
			
			$task_info = new TaskInfo($redis, $db);
			$flag = $task_info->add_singer_sun($jparams->uid, $retparams->sun);
			if(empty($flag)){
			    $redis->del($re_key);
			    $retparams->resCode = 403;
			    logs::addLog("uid:$jparams->uid, add_singer_sun is error!", $logfile);
			    logs::addLog("WARN::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id  (task_info->add_singer_sun) add_singer_sun is error", $logfile);
			    
			    $db->query("ROLLBACK");
			    $db->disconnect() ;
			    return json_encode($retparams) ;
			}
			
		    //主播当天获得的任务阳光数
		    if(!empty($retparams->sun)){
		        $date1 = date("Y-m-d");
		        $key = "singer_day_sun:$date1:$jparams->uid";
		        $redis->incrBy($key, $retparams->sun);
		        $redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
		    }	    
		    
			
		    $task_common = new TaskCommon($redis, $db);
		    $flag = $task_common->AppendItemToDB($jparams->uid, $retparams->debris, $retparams->leaf, 0, $retparams->gold);
		    
			/* $query = "UPDATE rcec_main.user_attribute SET debris = debris + $retparams->debris,
			coin_balance = coin_balance + $retparams->gold, leaf = leaf+$retparams->leaf WHERE uid = $jparams->uid";
			 
			$rs = $db->query($query);*/
			if (empty($flag)) {
			    $redis->del($re_key);
			    //logs::addLog("uid:$jparams->uid, *********:主播添加碎片失败.", $logfile);
			    logs::addLog("WARN::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id  (task_common->AppendItemToDB) 主播添加碎片失败", $logfile);
			    
			    $retparams->resCode = 403;
			    $db->query("ROLLBACK");
			    
			    $db->disconnect() ;
			    return json_encode($retparams);
			}
			//主播任务事件埋点
			{
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
			    $event_type = 4;//类型4：主播任务领取奖励
			    $uid = $jparams->uid;
			    $id  = $jparams->tid;			    
			    $awardItems = array(
			        $awardItem_leaf,
			        $awardItem_sun,
			        $awardItem_gold,
			        $awardItem_heart,
			    );
			    $event_task->taskModule_singer_event($event_type,$uid, $id,&$awardItems);
			}
			
			
			//获取主播当前的阳光值
			$retparams->totalSun = 0;
			$query_5 = "select anchor_current_experience from raidcall.anchor_info where flag = 1 and uid = $jparams->uid";
			$rows_5 = $db->query($query_5);
			if($row = $db->fetch_assoc($rows_5)){
			    $retparams->totalSun = (int)$row['anchor_current_experience'];
			}
			else {
			    logs::addLog("WARN::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id SQL_FAIL=>query_5:$query_5 ", $logfile);
			}
			//领取主播开启奖励，需要清除掉开启任务，此时主播才可以开启新任务
			if(1 == $openObject){
			    $date = getCurDate();
			    $key = "taskstart::uid:$jparams->uid:$date";
			    $redis->del($key);
			}
			
			$db->query("COMMIT");			
			
			
			$task_info->post_msg($jparams->uid, $retparams->sun);
			
		}else{
		    $redis->del($re_key);
		}
		$db->disconnect() ;
		$sql_commit = microtime(true);
		$sql_usetime = $sql_commit-$sql_begin;

		//$logs_array = array("return data., ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);
		logs::addLog("INFO::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id sql_usetime:$sql_usetime retparams:".json_encode($retparams), $logfile);
		
		return json_encode($retparams) ;
	}else{
	    $redis->del($re_key);
		$db->disconnect() ;		
		//logs::addLog('*********数据库连接失败！！！', $logfile);
		logs::addLog("WARN::taskinfo::getreward::任务领奖 uid:$uid task_id:$task_id SQL_FAIL=>query_3:$query_3 ", $logfile);
	    $retparams->resCode = 403;
	    return json_encode($retparams);
	}
}
?>
