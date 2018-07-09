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
	
	//logs::addLog("params:".json_encode($params), $logfile);
	logs::addLog("INFO::taskinfo::gettreasuretasklist::挖宝任务列表  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid") || !property_exists($jparams,"type")) {
		//$error_logs_array = array("lost property"=>" (uid, type)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::gettreasuretasklist::挖宝任务列表  params lost uid or type", $logfile);
		return json_encode($retparams);
	}
	$db = new db();
	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	$redis = getRedis() ;
	
	$retparams->uid = $jparams->uid;
	$uid = (int)$jparams->uid;
	$treasure_type = (int)$jparams->type;
	
	$retparams->dayTask = array(); 
	
	//获取用户叶子总数
	$key = "userleaf_uid:$jparams->uid";
	$userLeaf = $redis->get($key);
	$retparams->leaf_total_num = 0;
	if(!empty($userLeaf)){
	    $retparams->leaf_total_num = (int)$userLeaf;
	}

	$date = getCurDate();//date("Y-m-d");
	
	//获取用户的挖宝任务详情
	$query_1 = "select t.id, tc.task_name, tc.task_sketch,tc.task_type, tc.target_type, tc.award_goods_num,
	t.t_status, t.t_total_progress, tc.target_params2, g.goods_icon, r.folder_path, tc.leaf, tc.sun, tc.debris, tc.gold from card.task_info t
	left join card.task_conf tc on t.t_id = tc.id
	left join card.treasure_box_info b on b.id = tc.award_goods_id
	left join card.goods_info g on g.id = b.good_id
	left join card.resoure_folder_info r on r.id = g.path_id
	where t.uid = $jparams->uid and t.t_type = 9 and t.create_time = '$date'" ;
	
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

			//默认用户
			$key = "usertreasure_task:uid:$jparams->uid:$date:tid:$tid";
			if($jparams->type == 1){
			    //主播
			    $key = "singertreasure_task:uid:$jparams->uid:$date:tid:$tid";
			}
			
			//从redis里读取挖宝任务的完成进度
	        $data = $redis->get($key);
	        $f_progress = 0;
	        if(!empty($data)){
	        	$data = json_decode($data, TRUE);
	        	$f_progress = $data['t_finish_progress'];
	        }
		
			//任务类型:9
			$retparams->dayTask[$j]["t_id"] = (int)$row['id'];
			$retparams->dayTask[$j]["t_name"] = base64_encode($row['task_name']);
			$retparams->dayTask[$j]["t_desc"] = base64_encode($row['task_sketch']);
			$retparams->dayTask[$j]["t_dest_type"] = (int)$row['target_type'];
			$imgpath = $row['folder_path']."/".$row['goods_icon'];
			$retparams->dayTask[$j]["t_reward_img"] = base64_encode($imgpath);
			$retparams->dayTask[$j]["t_reward_num"] = (int)$row['award_goods_num'];
			$retparams->dayTask[$j]["t_map_level"] = (int)$row['target_params2'];
			$retparams->dayTask[$j]["t_status"] = (int)$row['t_status'];
			$retparams->dayTask[$j]["t_finish_progress"] = $f_progress;
			$retparams->dayTask[$j]["t_total_progress"] = (int)$row['t_total_progress'];
			
    		$sun = (int)$row['sun'];
    	    $leaf = (int)$row['leaf'];
    	    $sun_revise = 0;
    	    $leaf_revise = 0;
    	    $debris = (int)$row['debris'];
    	    $gold = (int)$row['gold'];
    	    $debris_revise = 0;
    	    $gold_revise = 0;
    	    
    	    //奖励id 9：碎片  8：叶子 11：阳光  10：金币
//     	    if(!empty($debris)){
    	        $retparams->dayTask[$j]["debris_id"] = 9;
    	        $retparams->dayTask[$j]["debris_revise"] = $debris_revise;
    	        $retparams->dayTask[$j]["debris_num"] = $debris;
//     	    }
//     	    if(!empty($leaf)){
    	        $retparams->dayTask[$j]["leaf_id"] = 8;
    	        $retparams->dayTask[$j]["leaf_revise"] = $leaf_revise;
    	        $retparams->dayTask[$j]["leaf_num"] = $leaf;
//     	    }
//     	    if(!empty($sun)){
    	        $retparams->dayTask[$j]["sun_id"] = 11;
    	        $retparams->dayTask[$j]["sun_revise"] = $sun_revise;
    	        $retparams->dayTask[$j]["sun_num"] = $sun;
//     	    }
//     	    if(!empty($gold)){
    	        $retparams->dayTask[$j]["gold_id"] = 10;
    	        $retparams->dayTask[$j]["gold_revise"] = $gold_revise;
    	        $retparams->dayTask[$j]["gold_num"] = $gold;
//     	    }
    	    /* $n = 0;
    	    if(!empty($debris)){
    	        $retparams->dayTask[$j]["rewardModels"][$n]["r_id"] = 9;
    	        $retparams->dayTask[$j]["rewardModels"][$n]["r_revise"] = $debris_revise;
    	        $retparams->dayTask[$j]["rewardModels"][$n++]["r_num"] = $debris;
    	    }
    	    if(!empty($leaf)){
    	        $retparams->dayTask[$j]["rewardModels"][$n]["r_id"] = 8;
    	        $retparams->dayTask[$j]["rewardModels"][$n]["r_revise"] = $leaf_revise;
    	        $retparams->dayTask[$j]["rewardModels"][$n++]["r_num"] = $leaf;
    	    }
    	    if(!empty($sun)){
    	        $retparams->dayTask[$j]["rewardModels"][$n]["r_id"] = 11;
    	        $retparams->dayTask[$j]["rewardModels"][$n]["r_revise"] = $sun_revise;
    	        $retparams->dayTask[$j]["rewardModels"][$n++]["r_num"] = $sun;
    	    }
    	    if(!empty($gold)){
    	        $retparams->dayTask[$j]["rewardModels"][$n]["r_id"] = 10;
    	        $retparams->dayTask[$j]["rewardModels"][$n]["r_revise"] = $gold_revise;
    	        $retparams->dayTask[$j]["rewardModels"][$n++]["r_num"] = $gold;
    	    } */
    	    
    	    $j++;
		}
		$db->disconnect() ;

		//$logs_array = array("return data., ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);
		logs::addLog("INFO::taskinfo::gettreasuretasklist::挖宝任务列表  uid:$uid treasure_type:$treasure_type retparams:".json_encode($retparams), $logfile);
		
		return json_encode($retparams) ;
	}else{
	    logs::addLog("WARN::taskinfo::gettreasuretasklist::挖宝任务列表  uid:$uid treasure_type:$treasure_type SQL_FAIL=>query_1:$query_1 ", $logfile);
		$db->disconnect() ;
		return false ;
	}
}
?>
