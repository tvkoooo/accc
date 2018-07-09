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
	
	//logs::addLog("params:".json_encode($params)." postdata:".json_encode($postdata), $logfile);
	logs::addLog("INFO::taskinfo::gettasklist::获得任务列表  params:$params ", $logfile);
	
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid") && !property_exists($jparams,"is_singer")) {
		//$error_logs_array = array("lost property"=>" (uid, is_singer)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::gettasklist::获得任务列表  params lost uid or is_singer", $logfile);
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
	$is_singer = (int)$jparams->is_singer;
	$retparams->dayTask = array(); 
	$retparams->mainTask = array(); 

	$date = getCurDate();//date("Y-m-d");
	/* $query = "select t.id, tc.task_name, tc.task_sketch,tc.task_type, tc.target_type, tc.award_goods_num, 
			t.t_status, t.t_total_progress, tc.target_params2, g.goods_icon, r.folder_path from card.task_info t 
			left join card.task_conf tc on t.t_id = tc.id
			left join card.treasure_box_info b on b.id = tc.award_goods_id
			left join card.goods_info g on g.id = b.good_id 
			left join card.resoure_folder_info r on r.id = g.path_id
			where t.uid = $jparams->uid and t.t_status = 0 and ((t.t_type = 1 and t.create_time = '$date') || t.t_type = 0)" ; */
	$query_1 = "select t.id, tc.task_name, tc.task_sketch,tc.task_type, tc.target_type, tc.award_goods_num,
	t.t_status, t.t_total_progress, tc.target_params2, g.goods_icon, r.folder_path, tc.leaf, tc.sun, tc.debris, tc.gold, tc.open_object, tc.face_object from card.task_info t
	left join card.task_conf tc on t.t_id = tc.id
	left join card.treasure_box_info b on b.id = tc.award_goods_id
	left join card.goods_info g on g.id = b.good_id
	left join card.resoure_folder_info r on r.id = g.path_id
	where t.uid = $jparams->uid and t.t_status in(0,1,4) and t.t_type = 1 and t.create_time = '$date' and tc.open_object = 0 and tc.face_object = 0" ;
			
	//$logs_array = array("query"=>$query);
    //logs::addLog($logs_array, $logfile);
    
	$rows_1 = $db->query($query_1) ;

	//$logs_array = array("query"=>$query, "result"=>$rows);
    //logs::addLog($logs_array, $logfile);
        
	if($rows_1){
		$retparams->resCode = 200;
		$i = 0;
		$j = 0;
		while($row = $db->fetch_assoc($rows_1)){
		    $tid = (int)$row['id'];
			$taskType = (int)$row['task_type'];
			//主线
			if(0 == $taskType){
		        $key = "uid:$jparams->uid:main:tid:$tid";
			}else if(1 == $taskType){
				$key = "singer_day_uid:$jparams->uid:$date:tid:$tid";
			}
	        
	        $data = $redis->get($key);
	        $f_progress = 0;
	        if(!empty($data)){
	        	$data = json_decode($data, TRUE);
	        	$f_progress = $data['t_finish_progress'];
	        }
		
			//任务类型:0为主线任务,1为每日任务,2为每周任务,3为每月任务
			if($row['task_type'] == 0){
// 				$retparams->mainTask[$i]["t_id"] = (int)$row['id'];
// 				$retparams->mainTask[$i]["t_name"] = base64_encode($row['task_name']);
// 				$retparams->mainTask[$i]["t_desc"] = base64_encode($row['task_sketch']);
// 				$retparams->mainTask[$i]["t_dest_type"] = (int)$row['target_type'];
// 				$retparams->mainTask[$i]["t_reward_img"] = base64_encode($row['goods_icon']);
// 				$retparams->mainTask[$i]["t_reward_num"] = (int)$row['award_goods_num'];
// 				$retparams->mainTask[$i]["t_status"] = (int)$row['t_status'];
// 				$retparams->mainTask[$i]["t_finish_progress"] = $f_progress;
// 				$retparams->mainTask[$i++]["t_total_progress"] = (int)$row['t_total_progress'];
			}else if($row['task_type'] == 1){
				$retparams->dayTask[$j]["t_id"] = (int)$row['id'];
				$retparams->dayTask[$j]["t_name"] = base64_encode($row['task_name']);
				$retparams->dayTask[$j]["t_desc"] = base64_encode($row['task_sketch']);
				$retparams->dayTask[$j]["t_dest_type"] = (int)$row['target_type'];
				$imgpath = $row['folder_path']."/".$row['goods_icon'];
				$retparams->dayTask[$j]["t_reward_img"] = base64_encode($imgpath);
				$retparams->dayTask[$j]["t_reward_num"] = (int)$row['award_goods_num'];
				$retparams->dayTask[$j]["t_status"] = (int)$row['t_status'];
				$retparams->dayTask[$j]["t_finish_progress"] = $f_progress;
				$retparams->dayTask[$j]["t_total_progress"] = (int)$row['t_total_progress'];
				$retparams->dayTask[$j]["t_leaf_num"] = (int)$row['leaf'];
				$retparams->dayTask[$j]["t_sun_num"] = (int)$row['sun'];
				
				$sun = (int)$row['sun'];
				$leaf = (int)$row['leaf'];
				$sun_revise = 0;
				$leaf_revise = 0;
				$debris = (int)$row['debris'];
				$gold = (int)$row['gold'];
				$debris_revise = 0;
				$gold_revise = 0;
				
				//奖励id 9：碎片  8：叶子 11：阳光  10：金币
				$n = 0;
// 				if(!empty($debris)){
				    $retparams->dayTask[$j]["debris_id"] = 9;
				    $retparams->dayTask[$j]["debris_revise"] = $debris_revise;
				    $retparams->dayTask[$j]["debris_num"] = $debris;
// 				}
// 				if(!empty($leaf)){
				    $retparams->dayTask[$j]["leaf_id"] = 8;
				    $retparams->dayTask[$j]["leaf_revise"] = $leaf_revise;
				    $retparams->dayTask[$j]["leaf_num"] = $leaf;
// 				}
// 				if(!empty($sun)){
				    $retparams->dayTask[$j]["sun_id"] = 11;
				    $retparams->dayTask[$j]["sun_revise"] = $sun_revise;
				    $retparams->dayTask[$j]["sun_num"] = $sun;
// 				}
// 				if(!empty($gold)){
				    $retparams->dayTask[$j]["gold_id"] = 10;
				    $retparams->dayTask[$j]["gold_revise"] = $gold_revise;
				    $retparams->dayTask[$j]["gold_num"] = $gold;
// 				}
				
				$j++;
			}
			
		}
		$db->disconnect() ;

		//$logs_array = array("return data., ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);
		logs::addLog("INFO::taskinfo::gettasklist::获得任务列表  uid:$uid is_singer:$is_singer retparams:".json_encode($retparams), $logfile);
		
		return json_encode($retparams) ;
	}else{
	    logs::addLog("WARN::taskinfo::gettasklist::获得任务列表  uid:$uid is_singer:$is_singer SQL_FAIL=>query_1:$query_1 ", $logfile);
		$db->disconnect() ;
		return false ;
	}
}
?>
