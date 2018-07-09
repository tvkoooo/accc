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
	logs::addLog("INFO::taskinfo::gettaskinfo::获得任务信息  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid") && !property_exists($jparams,"tid")) {
		//$error_logs_array = array("lost property"=>" (uid, tid)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::gettaskinfo::获得任务信息  params lost uid or tid", $logfile);
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
	$retparams->data = array(); 

	$query_1 = "select t.id, tc.task_name, tc.task_content, tc.target_type, tc.award_goods_num, t.t_status,
			t.t_finish_progress, t.t_total_progress, tc.open_type, tc.tool_id, tc.tool_num, tc.task_help_content,
			tc.task_target_content, g.goods_icon from card.task_info t 
			left join card.task_conf tc on t.t_id = tc.id 
			left join rcec_main.tool rt on tc.tool_id = rt.id
			left join card.treasure_box_info b on b.id = tc.award_goods_id
			left join card.goods_info g on g.id = b.good_id
			where t.id = $retparams->tid" ;
    
	$rows_1 = $db->query($query_1) ;

	//$logs_array = array("query"=>$query, "result"=>$rows);
    //logs::addLog($logs_array, $logfile);
        
	if($row = $db->fetch_assoc($rows_1)){
		$retparams->resCode = 200;
		
		$retparams->data["t_id"] = (int)$row['id'];
		$retparams->data["t_name"] = base64_encode($row['task_name']);
		$retparams->data["t_desc"] = base64_encode($row['task_content']);
		$retparams->data["t_dest_type"] = (int)$row['target_type'];
		$retparams->data["t_reward_img"] = base64_encode($row['goods_icon']);
		$retparams->data["t_reward_num"] = (int)$row['award_goods_num'];
		$retparams->data["t_status"] = (int)$row['t_status'];
		$retparams->data["t_finish_progress"] = (int)$row['t_finish_progress'];
		$retparams->data["t_total_progress"] = (int)$row['t_total_progress'];
		
		$retparams->data["t_open_type"] = (int)$row['open_type'];
		$retparams->data["t_tool_id"] = (int)$row['tool_id'];
		$retparams->data["t_tool_num"] = (int)$row['tool_num'];
		$retparams->data["t_help_content"] = base64_encode($row['task_help_content']);
		$retparams->data["t_dest_content"] = base64_encode($row['task_target_content']);
		
		$openContent = "无";
		if((int)$row['open_type'] == 1){
			$openContent = "收到礼物'".$row['name']."'个数".$row['tool_num'];
		}
		$retparams->data["t_open_content"] = base64_encode($openContent);
		
		$db->disconnect() ;

		//$logs_array = array("return data., ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);
		logs::addLog("INFO::taskinfo::gettaskinfo::获得任务信息 uid:$uid task_id:$task_id retparams:".json_encode($retparams), $logfile);
		
		return json_encode($retparams) ;
	}else{
	    logs::addLog("WARN::taskinfo::gettaskinfo::获得任务信息 uid:$uid task_id:$task_id  SQL_FAIL=>query_1:$query_1 ", $logfile);
		$db->disconnect() ;
		return false ;
	}
}
?>
