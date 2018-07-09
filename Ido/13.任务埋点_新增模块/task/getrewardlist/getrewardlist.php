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
	//logs::addLog("DEBUG::taskinfo::getrewardlist::获得奖励查询  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid") 
			&& !property_exists($jparams,"target_type")
			&& !property_exists($jparams,"num")
			&& !property_exists($jparams,"extra_param")) {
		//$error_logs_array = array("lost property"=>" (uid, target_type, num, extra_param)");
        //logs::addLog($error_logs_array, $logfile);
		logs::addLog("WARN::taskinfo::getrewardlist::获得奖励查询  params lost uid or target_type or num or extra_param ", $logfile);
		return json_encode($retparams);
	}
	
	//logs::addLog("params:".json_encode($jparams), $logfile);
	$db = new db();
	$uid= (int)$db->escape_string($jparams->uid);
	$target_type= (int)$db->escape_string($jparams->target_type);
	$num= (int)$db->escape_string($jparams->num);
	$extra_param= (int)$db->escape_string($jparams->extra_param);

	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	$redis = getRedis() ;

	$retparams->data = array(); 
	
	/* if($extra_param == 10002417 && $uid == 10002422){
	    logs::addLog("params:".json_encode($jparams), $logfile);
	} */
	
	$task_info = new TaskInfo($redis, $db);
	$flag = $task_info->getAwards($uid, $target_type, $num, $extra_param);
	if(!empty($flag)){
	    $retparams->resCode = 200;
	    $retparams->data = $flag;
	}
	
	$db->disconnect() ;
	
	$logs_array = array("return data, ret"=>json_encode($retparams));
	logs::addLog($logs_array, $logfile);
	
	return json_encode($retparams) ;
/*
	$date = date("Y-m-d");
	$query = "select t.id, t.uid, t.create_time, tc.open_object, tc.task_type, tc.task_name, tc.task_sketch, tc.award_goods_id from card.task_info t left join card.task_conf tc on t.t_id = tc.id 
			where t.t_status = 1 and ((t.t_type = 1 and t.create_time = '$date') || t.t_type=0)";
			
	$logs_array = array("query"=>$query);
    logs::addLog($logs_array, $logfile);
    
	$rows = $db->query($query) ;

	$logs_array = array("query"=>$query, "result"=>$rows);
    logs::addLog($logs_array, $logfile);
    
    $now = time()*1000;
    
	if($rows){
		$retparams->resCode = 200;
		$i = 0;
		while($row = $db->fetch_assoc($rows)){
			$retparams->data[$i]["t_id"] = (int)$row['id'];
			$retparams->data[$i]["uid"] = (int)$row['uid'];
			$retparams->data[$i]["t_type"] = (int)$row['task_type'];
			$retparams->data[$i]["t_name"] = base64_encode($row['task_name']);
			$retparams->data[$i]["t_desc"] = base64_encode($row['task_sketch']);
			
			$tid = (int)$row['id'];
			$uid = (int)$row['uid'];
			$openObj = (int)$row['open_object'];
			$dropid = (int)$row['award_goods_id'];
			$date = $row['create_time'];
			$sql = "INSERT INTO card.user_treasure_box_info (drop_id,box_id,uid,create_time,status) select $dropid, good_id, $uid, $now, 2 from card.treasure_box_info where id = $dropid";
			$rs = $db->query($sql);
			if($rs){
				$sql = "select LAST_INSERT_ID() as id";
				$rs = $db->query($sql);
				if($r = $db->fetch_assoc($rs)){
					$retparams->data[$i]["award_goods_id"] = (int)$r['id'];
				}else{
					$logs_array = array("excute sql error, sql:"=>$sql);
					logs::addLog($logs_array, $logfile);
				}
				//TODO:更改task_info表的t_status值为4，表示已经发送了礼物
				$query = "update card.task_info t set t_status = 4 where t.id = $tid";
				$rs = $db->query($query);
				
				if(1 == $openObj){
					$key = "taskstart::uid:$uid:$date";
	    			$redis->del($key);
				}
			}else{
				$logs_array = array("excute sql error, sql:"=>$sql);
				logs::addLog($logs_array, $logfile);
			}
			$i++;
		}
		$db->disconnect() ;

		$logs_array = array("return data, ret"=>json_encode($retparams));
		logs::addLog($logs_array, $logfile);
		
		return json_encode($retparams) ;
	}else{
		$db->disconnect() ;
		return false ;
	}
	*/
}
?>
