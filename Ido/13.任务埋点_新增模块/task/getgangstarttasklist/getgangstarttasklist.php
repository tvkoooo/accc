<?php

$path=dirname(__FILE__);
require "$path/../../include/db.class.php";
require "$path/../../include/xlog.class.php";
require "$path/../../include/interfun.php";
require "$path/../../include/redis/redis_interfun.php";
//require "$path/../../include/redis/task_info.php";
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
	logs::addLog("INFO::taskinfo::getgangstarttasklist::获得帮会星级任务列表  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid")) {
		//$error_logs_array = array("lost property"=>" (uid)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::getgangstarttasklist::获得帮会星级任务列表  params lost uid ", $logfile);
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
	
	$key = 'uid:' . $jparams->uid;
	$value = $redis->get($key);
	$user = json_decode($value, true);
	$gangid = $user['union_id'];

	//如果缓存无数据，有可能缓存异常(用户登陆后再加入帮会这段时间)，从数据库取值
	if(empty($gangid))
	{
	    $sql_1 = "select t.union_id from raidcall.uinfo t where t.id = $jparams->uid";
	    $rows_1 = $db->query($sql_1) ;
	    if($row = $db->fetch_assoc($rows_1)){
	        $gangid = $row['union_id'];
	    }
	    else {
	        logs::addLog("WARN::taskinfo::getgangstarttasklist::获得帮会星级任务列表 uid:$uid SQL_FAIL=>sql_1:$sql_1 ", $logfile);
	    }
	}	
	//如果数据库也没有数据说明用户没有帮会
	if(empty($gangid)){
	    $retparams->resCode = 101;
	    $db->disconnect() ;
	     
	    //$logs_array = array("return data., ret"=>json_encode($retparams));
	    //logs::addLog($logs_array, $logfile);
	    logs::addLog("INFO::taskinfo::getgangstarttasklist::获得帮会星级任务列表  uid:$uid  empty(gangid) return data=>:".json_encode($retparams), $logfile);
	    return json_encode($retparams) ;
	}
	
	//获取今天的获得帮会星级任务列表
	//$date = date('Y-m-01', strtotime(date("Y-m-d")));;
	$date = date('Y-m-01', strtotime(getCurDate()));;
	$query_2 = "select t.id, tc.task_name, tc.task_sketch, tc.target_type, tc.award_goods_num, t.t_status,
			t.t_finish_progress, t.t_total_progress, tc.target_params2, g.goods_icon, r.folder_path from card.task_info t 
			left join card.task_conf tc on t.t_id = tc.id 
			left join card.treasure_box_info b on b.id = tc.award_goods_id
			left join card.goods_info g on g.id = b.good_id
			left join card.resoure_folder_info r on r.id = g.path_id
			where t.uid = $gangid and t.t_type = 5 and t.create_time = '$date'" ;
			
	//$logs_array = array("query"=>$query);
    //logs::addLog($logs_array, $logfile);
    
	$rows_2 = $db->query($query_2) ;

	//$logs_array = array("query"=>$query, "result"=>$rows);
    //logs::addLog($logs_array, $logfile);
        
	if($rows_2){
		$retparams->resCode = 200;
		$i = 0;
		while($row = $db->fetch_assoc($rows_2)){
		    $tid = (int)$row['id'];
	        $key = "gangstart:$gangid:$date:tid:$tid";
	        
	        $data = $redis->get($key);
	        $f_progress = 0;
	        if(!empty($data)){
	        	$data = json_decode($data, TRUE);
	        	$f_progress = $data['t_finish_progress'];
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
			$retparams->data[$i++]["t_total_progress"] = (int)$row['t_total_progress'];
			
		}
		$db->disconnect() ;

		//$logs_array = array("return data., ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);
		logs::addLog("INFO::taskinfo::getgangstarttasklist::获得帮会星级任务列表 uid:$uid gangid:$gangid retparams:".json_encode($retparams), $logfile);
		
		return json_encode($retparams) ;
	}else{
	    logs::addLog("WARN::taskinfo::getgangstarttasklist::获得帮会星级任务列表 uid:$uid gangid:$gangid SQL_FAIL=>query_2:$query_2 ", $logfile);
		$db->disconnect() ;
		return false ;
	}
}
?>
