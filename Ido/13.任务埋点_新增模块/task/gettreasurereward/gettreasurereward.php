<?php

$path=dirname(__FILE__);
require "$path/../../include/db.class.php";
require "$path/../../include/xlog.class.php";
require "$path/../../include/interfun.php";
include_once "$path/../../include/sysparameters.php";
include_once "$path/../../include/taskcommon.php";
$path=dirname(__FILE__);
require "$path/../../include/redis/redis_interfun.php";
require "$path/../../include/redis/task_info.php";

$path=dirname(__FILE__);
include_once "$path/../../include/redis/task_eventHandle.php";

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

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
    $logfile=basename(__FILE__, '.php');
    $items = getRoomSunConfFromCache($redis);
    if (!empty($items)) {
        return $items;
    }

    $items = array();

    $key = "h_conf_room_glory_level";
    $sql_1 = "SELECT * FROM card.room_sun_level";
    $rows_1 = $db->query($sql_1);

    if (!empty($rows_1)) {
        $row = null;
        $row = $rows_1->fetch_assoc();
         
        while (!empty($row)) {
            $items[(int)$row['level']] = $row;
            $redis->hSet($key, $row['level'] . "", json_encode($row));
            $row = $rows_1->fetch_assoc();
        }
    }
    else {
        logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  SQL_FAIL=>sql_1:$sql_1 ", $logfile);
    }

    return $items;
}

//获得主播加成
function getSingerRatio($db, $redis, $glory_total)
{
    $conf_glory_inf = getRoomSunConf($db, $redis);
    if (empty($conf_glory_inf)) {
        return;
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
	logs::addLog("INFO::taskinfo::gettreasurereward::获得挖宝任务奖励  params:$params ", $logfile);
	
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid") 
	    && !property_exists($jparams,"tid")
	    && !property_exists($jparams,"type")) {
		//$error_logs_array = array("lost property"=>" (uid, tid, type)");
        //logs::addLog($error_logs_array, $logfile);
	        logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  params lost uid or tid or type ", $logfile);
		return json_encode($retparams);
	}
	$redis = getRedis() ;
	
	$retparams->uid = $jparams->uid;
	$retparams->tid = $jparams->tid;
	$uid = (int)$jparams->uid;
	$task_id = (int)$jparams->tid;
	$treasure_type = (int)$jparams->type;//0：用户 1：主播
	
	$re_key = "uid:$jparams->uid:tid:$jparams->tid";
	$value = $redis->incrBy($re_key, 1);
	$redis->expire($re_key, 2*24*60*60);
	if($value > 1){
	    $retparams->resCode = 401;
	    //logs::addLog("重复领取.", $logfile);
	    logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type 领取时间 re_key:$re_key 重复领取  ", $logfile);
	    return json_encode($retparams);
	}
	
	$db = new db();
	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	
	//0：用户 1：主播
	$retparams->type = $jparams->type;	
	
	//得到任务状态
	$sql_2 = "select t_status from card.task_info t where t.id = $jparams->tid";
	
	$rows_2 = $db->query($sql_2);
	if($row = $db->fetch_assoc($rows_2)){
	    $status = (int)$row['t_status'];
	    if(4 == $status){
	        //logs::addLog("用户 $jparams->uid,已经完成领取奖励", $logfile);
	        logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type 已经完成领取奖励  ", $logfile);
	        $retparams->resCode = 401;
	        
	        $db->disconnect() ;
	        return json_encode($retparams);
	    }
	    if(0 == $status){
	        $redis->del($re_key);
	        //未完成任务不能领取
	        //logs::addLog("用户 $jparams->uid 未完成任务不能领取奖励", $logfile);
	        logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type 未完成任务不能领取奖励  ", $logfile);
	        $retparams->resCode = 402;
	        
	        $db->disconnect() ;
	        return json_encode($retparams);
	    }
	}
	else 
	{
	    logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type SQL_FAIL=>sql_2:$sql_2 ", $logfile);
	}
	
	//得到该挖宝任务的奖励详情
	$query_3 = "select tc.leaf, tc.sun, tc.debris, tc.gold, p.parm1 as leaf_num from card.task_info t 
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
	    
	    $sql_4 = "update card.task_info t set t_status=4 where t.id = $jparams->tid";	    
	    $rs_4 = $db->query($sql_4);
	    //$jparams->uid
	    //logs::addLog("uid $jparams->uid, sql:$sql, result:$rs, row sizse:".$db->affected_rows(), $logfile);
	    
	    if(!$rs_4 || $db->affected_rows() <= 0){
	        $redis->del($re_key);
	        $retparams->resCode = 403;
	        //logs::addLog("uid $jparams->uid, error!!!, sql:$sql", $logfile);
	        logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type SQL_FAIL=>$sql_4:sql_4 ", $logfile);
	        $db->query("ROLLBACK");
	        $db->disconnect() ;
	        return json_encode($retparams) ;
	    }
	    
	    $task_info = new TaskInfo($redis, $db);
	    
		$i = 0;
		$j = 0;
		if($row = $db->fetch_assoc($rows_3)){
		    $retparams->resCode = 200;
		    
		    $retparams->leaf = (int)$row['leaf'];
			$retparams->sun = (int)$row['sun'];
		    $retparams->debris = (int)$row['debris'];
			$retparams->gold = (int)$row['gold'];
			$retparams->leaf_top = (int)$row['leaf_num'];

			//叶子总数记在Redis中		
			$key = "userleaf_uid:$jparams->uid";
			$retparams->leaf_total_num = $redis->incrBy($key, $retparams->leaf);
			
			$retparams->ratio = 0;
			
			$task_common = new TaskCommon($redis, $db);
			
			if(empty($jparams->type)){
			    //用户
			    /* $query = "UPDATE rcec_main.user_attribute SET sun_num = sun_num + $retparams->sun,
			    debris = debris + $retparams->debris, leaf = leaf+$retparams->leaf,
			    coin_balance = coin_balance + $retparams->gold WHERE uid = $jparams->uid";
			    
			    $rs = $db->query($query); */
			    
			    $flag = $task_common->AppendItemToDB($jparams->uid, $retparams->debris, $retparams->leaf, $retparams->sun, $retparams->gold);
			    if (empty($flag)) {
			        $redis->del($re_key);
			        $retparams->resCode = 403;
			        //logs::addLog("uid $jparams->uid,*********:用户添加阳光等信息失败.", $logfile);
			        logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type  用户添加阳光等信息失败(task_common->AppendItemToDB)", $logfile);
			        $db->query("ROLLBACK");
			        $db->disconnect() ;
			        return json_encode($retparams) ;
			    }
			}else{
			    $query_5 = "select s.sid from card.task_info t
			    left join raidcall.sess_info s on s.owner = t.uid
			    where t.id = $jparams->tid";
			    $rows_5 = $db->query($query_5);
			    if($row = $db->fetch_assoc($rows_5)){
			        $sid = (int)$row['sid'];
			        $retparams->sid = $sid;
			    }
			    else 
			    {
			        logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type SQL_FAIL=>query_5:$query_5 ", $logfile);
			    }
			    
			    //获取房间荣耀值信息
			    $key = "h_room_glory_inf";
			    $field = "$sid:glory_total";
			    $glory_total = $redis->hGet($key, $field);
			    	
			    //logs::addLog("singerid:$retparams->uid:sid:$sid:glory_total:$glory_total", $logfile);
			    	
			    $ratio = getSingerRatio($db, $redis, $glory_total);
			    $retparams->ratio = $ratio*100;
			    if(!empty($ratio)){
			        $retparams->sun = (1+$ratio)*$retparams->sun;
			        //logs::addLog("sunvalue:$retparams->sun", $logfile);
			        logs::addLog("INFO::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type 阳光增加 sunvalue:$retparams->sun", $logfile);
			    }
			    
			    
			    $flag = $task_info->add_singer_sun($jparams->uid, $retparams->sun);
			    if(empty($flag)){
			        $redis->del($re_key);
			        $retparams->resCode = 403;
			        //logs::addLog("uid $jparams->uid, add_singer_sun is error!", $logfile);
			        logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type add_singer_sun is error(task_info->add_singer_sun)", $logfile);
			        $db->query("ROLLBACK");
			        $db->disconnect() ;
			        return json_encode($retparams) ;
			    }
			    
			    //主播当天获得的任务阳光数
			    if(!empty($retparams->sun)){
			        $date1 = date("Y-m-d");
			        $key = "singer_day_sun:$date1:$jparams->uid";
			        $redis->incrBy($key, $retparams->sun);
			        $redis->expire($key, 2*24*60*60);
			    }
			    
			    /* $query = "UPDATE rcec_main.user_attribute SET debris = debris + $retparams->debris, 
			         coin_balance = coin_balance + $retparams->gold, leaf = leaf+$retparams->leaf WHERE uid = $jparams->uid";
			    	
			    $rs = $db->query($query); */
			    $flag = $task_common->AppendItemToDB($jparams->uid, $retparams->debris, $retparams->leaf, 0, $retparams->gold);
			    if (empty($flag)) {
			        $redis->del($re_key);
			        $retparams->resCode = 403;
			        //logs::addLog("uid $jparams->uid, *********:主播添加碎片失败.", $logfile);
			        logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type 添加碎片失败(task_common->AppendItemToDB)", $logfile);
			        $db->query("ROLLBACK");
			        $db->disconnect() ;
			        return json_encode($retparams) ;
			    }
			    
			    //获取主播当前的阳光值
			    $retparams->totalSun = 0;
			    $query_6 = "select anchor_current_experience from raidcall.anchor_info where flag = 1 and uid = $jparams->uid";
			    $rows_6 = $db->query($query_6);
			    if($row = $db->fetch_assoc($rows_6)){
			        $retparams->totalSun = (int)$row['anchor_current_experience'];
			    }
			    else {
			        logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id SQL_FAIL=>query_6:$query_6 ", $logfile);
			    }
			}

            $taskEventHandle = new CEventHandleTask();
            $taskEventHandle->digTreasure_getAward_event($jparams->uid, $retparams->leaf, $retparams->sun, $retparams->debris, $retparams->gold);

		}else{
		    $redis->del($re_key);
		}
		
		$db->query("COMMIT");
		
		if(!empty($jparams->type)){
		    $task_info->post_msg($jparams->uid, $retparams->sun);
		}
		
		$db->disconnect() ;

		//$logs_array = array("return data., ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);
		$sql_commit = microtime(true);
		$sql_usetime = $sql_commit-$sql_begin;
		logs::addLog("INFO::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type 数据库事务时间 sql_usetime:$sql_usetime retparams:".json_encode($retparams), $logfile);
		
		return json_encode($retparams) ;
	}else{
	    $redis->del($re_key);
	    //logs::addLog('*********数据库连接失败！！！', $logfile);
	    logs::addLog("WARN::taskinfo::gettreasurereward::获得挖宝任务奖励  uid:$uid task_id:$task_id treasure_type:$treasure_type SQL_FAIL=>query_3:$query_3 ", $logfile);
		$db->disconnect() ;
		return false ;
	}
}
?>
