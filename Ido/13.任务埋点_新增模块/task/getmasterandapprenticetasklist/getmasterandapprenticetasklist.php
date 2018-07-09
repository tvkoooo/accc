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
	logs::addLog("INFO::taskinfo::getmasterandapprenticetasklist::获得师徒任务列表  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"masterid") 
	    || !property_exists($jparams,"apprenticeid")
	    || !property_exists($jparams,"type")) {
		//$error_logs_array = array("lost property"=>" (masterid, apprenticeid, type)");
        //logs::addLog($error_logs_array, $logfile);
        logs::addLog("WARN::taskinfo::getmasterandapprenticetasklist::获得师徒任务列表   params lost masterid or apprenticeid or type", $logfile);
		return json_encode($retparams);
	}
	$db = new db();
	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	$redis = getRedis() ;
	
	//$logs_array = array("host"=>$config->db_host, "user"=>$config->db_user, 
	//					"db_passwd"=>$config->db_passwd, "db_raidcall"=>$config->db_raidcall);
    //logs::addLog($logs_array, $logfile);

    //师傅id
	$retparams->masterid = $jparams->masterid;
	$master_id = (int)$jparams->masterid;
	//徒弟id
	$retparams->apprenticeid = $jparams->apprenticeid;
	$apprentice_id = (int)$jparams->apprenticeid;
	//0:徒弟 1:师傅
	$retparams->type = $jparams->type;
	$retparams->data = array(); 
	
	$retparams->uid = 0;
	//TODO:根据师徒类型获得宝箱id
	$award_goods_id = 0;
	$sql_1 = "select t.award_goods_id from card.task_conf t where t.task_type = 17";
	$rows_1 = $db->query($sql_1) ;
	if($row = $db->fetch_assoc($rows_1)){
	    $dropids = explode("|",$row['award_goods_id']);
	    $dropid = 0;
	    if(0 == $jparams->type){
	        $award_goods_id = (int)$dropids[1];
	        $retparams->uid = $jparams->apprenticeid;
	    }else{
	        $award_goods_id = (int)$dropids[0];
	        $retparams->uid = $jparams->masterid;
	    }
	}
	else {
	    logs::addLog("WARN::taskinfo::getmasterandapprenticetasklist::获得师徒任务列表 master_id:$master_id apprentice_id:$apprentice_id SQL_FAIL=>sql_1:$sql_1 ", $logfile);
	}
	
	//查询该用户今天的帮会活跃任务
	$date = getCurDate();//date("Y-m-d");
	$query_2 = "select t.id, tc.task_name, tc.task_sketch, tc.target_type, tc.award_goods_num, 
	        t.t_status, t.t_finish_progress, t.t_total_progress, tc.target_params2, 
	        g.goods_icon, r.folder_path, m.status from card.task_info t 
			left join card.task_conf tc on t.t_id = tc.id 
			left join card.treasure_box_info b on b.id = $award_goods_id
			left join card.goods_info g on g.id = b.good_id
			left join card.task_master_disciple m on m.uid = $retparams->uid and m.task_id = t.id
			left join card.resoure_folder_info r on r.id = g.path_id
			where t.uid = $jparams->apprenticeid and t.t_type = 17 " ;
			
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
	        $key = "master_apprentice:$jparams->apprenticeid:tid:$tid";
	        
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
			$status = (int)$row['status'];
			if($status == 1){
			    $status = 4;		//任务如果已领取奖励，则状态改为已完成
			}else{
			    $status = (int)$row['t_status'];
			}
			$retparams->data[$i]["t_status"] = $status;
			$retparams->data[$i]["t_finish_progress"] = $f_progress;
			$retparams->data[$i]["t_total_progress"] = (int)$row['t_total_progress'];
			$i++;
		}
		$db->disconnect() ;

		//$logs_array = array("return data., ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);
		logs::addLog("INFO::taskinfo::getmasterandapprenticetasklist::获得师徒任务列表 master_id:$master_id apprentice_id:$apprentice_id retparams:".json_encode($retparams), $logfile);
		
		return json_encode($retparams) ;
	}else{
	    logs::addLog("WARN::taskinfo::getmasterandapprenticetasklist::获得师徒任务列表 master_id:$master_id apprentice_id:$apprentice_id SQL_FAIL=>query_2:$query_2 ", $logfile);
		$db->disconnect() ;
		return false ;
	}
}
?>
