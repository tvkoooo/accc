<?php

$path=dirname(__FILE__);
require "$path/../../include/db.class.php";
require "$path/../../include/xlog.class.php";
require "$path/../../include/interfun.php";
require "$path/../../include/redis/redis_interfun.php";
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
function call($params, $postdata)
{
	$logfile=basename(__FILE__, '.php');
	logs::addLog("INFO::taskinfo::getrandomtasklist::获得随机任务列表  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid")) {
		//$error_logs_array = array("lost property"=>" (uid)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::getrandomtasklist::获得随机任务列表  params lost uid ", $logfile);
		return json_encode($retparams);
	}
	$db = new db();
	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	$redis = getRedis() ;
	
	//$logs_array = array("host"=>$config->db_host, "user"=>$config->db_user, 
	//					"db_passwd"=>$config->db_passwd, "db_raidcall"=>$config->db_raidcall);
    //logs::addLog($logs_array, $logfile);

	$retparams->uid = $jparams->uid;
	$uid = (int)$jparams->uid;
	$retparams->data = array(); 

	//获得今天未完成的随机任务
	$date = date("Y-m-d");
	$query_1 = "select t.id, tc.task_name, tc.task_sketch, tc.target_type, tc.award_goods_num, t.t_status,
			t.t_finish_progress, t.t_total_progress, tc.target_params2, g.goods_icon, r.folder_path from card.task_info t 
			left join card.task_conf tc on t.t_id = tc.id 
			left join card.treasure_box_info b on b.id = tc.award_goods_id
			left join card.goods_info g on g.id = b.good_id
			left join card.resoure_folder_info r on r.id = g.path_id
			where t.uid = $jparams->uid and t.t_status = 0 and t.t_type = 9 and t.create_time = '$date'" ;
			
	//$logs_array = array("query"=>$query);
    //logs::addLog($logs_array, $logfile);
    
	$rows_1 = $db->query($query_1) ;

	//$logs_array = array("query"=>$query, "result"=>$rows);
    //logs::addLog($logs_array, $logfile);
        
	if($rows_1){
		$retparams->resCode = 200;
		$i = 0;
		while($row = $db->fetch_assoc($rows_1)){
		    $tid = (int)$row['id'];
	        $key = "randomtask:uid:$jparams->uid:$date:tid:$tid";
	        
	        $data = $redis->get($key);
	        $f_progress = 0;
	        $times = 1;
	        if(!empty($data)){
	        	$data = json_decode($data, TRUE);
	        	$f_progress = $data['t_finish_progress'];
	        	$times = $data['times'];
	        }
	        
			$retparams->data[$i]["t_id"] = (int)$row['id'];
			$retparams->data[$i]["t_name"] = base64_encode($row['task_name']);
			$retparams->data[$i]["t_desc"] = base64_encode($row['task_sketch']);
			$retparams->data[$i]["t_dest_type"] = (int)$row['target_type'];
			$imgpath = $row['folder_path']."/".$row['goods_icon'];
			$retparams->data[$i]["t_reward_img"] = base64_encode($imgpath);
			$retparams->data[$i]["t_reward_num"] = (int)$row['award_goods_num'];
			$retparams->data[$i]["t_status"] = (int)$row['t_status'];
			$retparams->data[$i]["t_finish_progress"] = $f_progress;
			$retparams->data[$i]["t_total_progress"] = (int)$row['t_total_progress'];
			$retparams->data[$i++]["t_times"] = $times;
		}
		$db->disconnect() ;

		//$logs_array = array("return data., ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);
		logs::addLog("INFO::taskinfo::getrandomtasklist::获得随机任务列表 uid:$uid  retparams:".json_encode($retparams), $logfile);
		
		return json_encode($retparams) ;
	}else{
	    logs::addLog("WARN::taskinfo::getrandomtasklist::获得随机任务列表 uid:$uid gangid:$gangid SQL_FAIL=>query_1:$query_1 ", $logfile);
		$db->disconnect() ;
		return false ;
	}
}
?>
