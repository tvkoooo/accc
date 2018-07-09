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
	
	logs::addLog("params:".json_encode($params), $logfile);
	
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid")) {
		$error_logs_array = array("lost property"=>" (uid)");
        logs::addLog($error_logs_array, $logfile);
		return json_encode($retparams);
	}
	$db = new db();
	$db->dbconnect($config->db_host, $config->db_user, $config->db_passwd, $config->db_raidcall);
	$redis = getRedis() ;
	
	$retparams->uid = $jparams->uid;
	$uid = (int)$retparams->uid ;
	$retparams->dayTask = array(); 
	$retparams->rewardModels = array();
	
	$key = "userleaf_uid:$jparams->uid";
	$userLeaf = $redis->get($key);
	$retparams->leaf_total_num = 0;
	if(!empty($userLeaf)){
	    $retparams->leaf_total_num = (int)$userLeaf;
	}

	$date = getCurDate();//date("Y-m-d");
	
	$l_key = "loop_num:uid:$jparams->uid:$date";
	$l_datatmp = $redis->get($l_key);
	$l_data = json_decode($l_datatmp, TRUE);
	
	$l_cur_num = $l_data['l_cur_num'];
	$h_cur_num = $l_data['h_cur_num'];
	$h_total_num = $l_data['h_total_num'];
	$l_total_num = $l_data['l_total_num'];
	
	$retparams->h_cur_num = $h_cur_num;
	$retparams->l_cur_num = $l_cur_num;
	$retparams->h_total_num = $h_total_num;
	

	//<2018-07-05> Increase the mechanism of SQL error
	//<2018-07-05> Adding a if function package 	
	if( !(0 == $uid || is_null($l_cur_num ) || is_null($h_cur_num ))){
	    
		$retparams->is_finish = false;
		if($l_cur_num >= $l_total_num && $h_cur_num > $h_total_num){
		    //已经完成了所有轮任务
		    $retparams->resCode = 201;
		    $retparams->is_finish = true;
		    $retparams->h_cur_num = $h_cur_num-1;
		    
		    logs::addLog("已经完成所有任务。return:".json_encode($retparams), $logfile);
		    return json_encode($retparams);
		}
		
		//获取更新时间
		$key = "looptask_refurbish:$date:uid:$jparams->uid";
		$ts = $redis->get($key);
		if(empty($ts)){	//如果为nil 则置成 0
		    $ts = 0;
		}
		$retparams->refurbishTime = (int)$ts;
		
		$retparams->toolNum = 0;
		$sql = "select t.num from card.user_goods_info t where t.uid = $jparams->uid and t.goods_id = 32";
		$rows = $db->query($sql);
		logs::addLog("excue sql :$sql, rs:$rows", $logfile);
		if($rows){
		    $row = $db->fetch_assoc($rows);
		    $t_num = (int)$row['num'];
		     
		    logs::addLog("222222222,num:$t_num", $logfile);
		    $retparams->toolNum = $t_num;
		}
		
		//获取该轮跑环任务的奖励详情
		$sql = "select t.sun, t.leaf, r.sun_revise, r.leaf_revise, p.parm1 as leaf_top, 
		    t.debris, t.gold, r.debris_revise, r.gold_revise from card.task_sun t  
			left join card.task_sun_revise r on r.id = $l_cur_num
			left join card.parameters_info p on p.id = 38 
			where t.id = $h_cur_num";
		$rows = $db->query($sql);
		
		logs::addLog("excute sql:$sql, flag:$rows", $logfile);
		
		if($rows && $row = $db->fetch_assoc($rows)){
		    $sun = (int)$row['sun'];
		    $leaf = (int)$row['leaf'];
		    $sun_revise = (int)$row['sun_revise'];
		    $leaf_revise = (int)$row['leaf_revise'];
		    $leaf_top = (int)$row['leaf_top'];
		    $debris = (int)$row['debris'];
		    $gold = (int)$row['gold'];
		    $debris_revise = (int)$row['debris_revise'];
		    $gold_revise = (int)$row['gold_revise'];
		    
		    //奖励id 9：碎片  8：叶子 11：阳光  10：金币
		    $n = 0;
		    if(!empty($debris)){
		        $retparams->rewardModels[$n]["r_id"] = 9;
		        $retparams->rewardModels[$n]["r_revise"] = $debris_revise;
		        $retparams->rewardModels[$n++]["r_num"] = $debris;
		    }
		    if(!empty($leaf)){
		        $retparams->rewardModels[$n]["r_id"] = 8;
		        $retparams->rewardModels[$n]["r_revise"] = $leaf_revise;
		        $retparams->rewardModels[$n++]["r_num"] = $leaf;
		    }
		    if(!empty($sun)){
		        $retparams->rewardModels[$n]["r_id"] = 11;
		        $retparams->rewardModels[$n]["r_revise"] = $sun_revise;
		        $retparams->rewardModels[$n++]["r_num"] = $sun;
		    }
		    if(!empty($gold)){
		        $retparams->rewardModels[$n]["r_id"] = 10;
		        $retparams->rewardModels[$n]["r_revise"] = $gold_revise;
		        $retparams->rewardModels[$n++]["r_num"] = $gold;
		    }
		    	    
		    $retparams->leaf_num = $leaf;
		    $retparams->sun_num = $sun;
		    $retparams->sun_revise = $sun_revise;
		    $retparams->leaf_revise = $leaf_revise;
		    /*$retparams->debris_num = $debris;
		    $retparams->gold_num = $gold;
		    $retparams->debris_revise = $debris_revise;
		    $retparams->gold_revise = $gold_revise; */
		    $retparams->leaf_top = $leaf_top;
		    
		}else{
		    logs::addLog("excute sql error!!! uid:$jparams->uid, sql:$sql", $logfile);
		}

	}else{
		logs::addLog("getlooptasklist::ERROR 跑环列表异常 uid:$uid  uid==0  or user has no init looptask", $logfile);
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
	
	//获取该轮跑环任务的详情
	$query = "select t.id, tc.task_name, tc.task_sketch,tc.task_type, tc.target_type, tc.award_goods_num,
	t.t_status, t.t_total_progress, tc.target_params2, g.goods_icon, r.folder_path, tc.leaf, tc.sun from card.task_info t
	left join card.task_conf tc on t.t_id = tc.id
	left join card.treasure_box_info b on b.id = tc.award_goods_id
	left join card.goods_info g on g.id = b.good_id
	left join card.resoure_folder_info r on r.id = g.path_id
	where t.uid = $jparams->uid and t.t_status in(0, 1) and t.t_type = 11 and t.create_time = '$date'" ;
	
	$rows = $db->query($query) ;

	$logs_array = array("query"=>$query, "result"=>$rows);
    logs::addLog($logs_array, $logfile);
        
	if($rows){
		$retparams->resCode = 200;
		$i = 0;
		$j = 0;
		if($row = $db->fetch_assoc($rows)){
		    $tid = (int)$row['id'];
			$taskType = (int)$row['task_type'];

			//从redis中读取该任务的完成情况
			$key = "looptask:uid:$jparams->uid:$date:tid:$tid";
	        
	        $data = $redis->get($key);
	        $f_progress = 0;
	        if(!empty($data)){
	        	$data = json_decode($data, TRUE);
	        	$f_progress = $data['t_finish_progress'];
	        }
		
			//任务类型:0为主线任务,1为每日任务,2为每周任务,3为每月任务
			$retparams->dayTask[$j]["t_id"] = (int)$row['id'];
			$retparams->dayTask[$j]["t_name"] = base64_encode($row['task_name']);
			$retparams->dayTask[$j]["t_desc"] = base64_encode($row['task_sketch']);
			$retparams->dayTask[$j]["t_dest_type"] = (int)$row['target_type'];
			$imgpath = $row['folder_path']."/".$row['goods_icon'];
			$retparams->dayTask[$j]["t_reward_img"] = base64_encode($imgpath);
			$retparams->dayTask[$j]["t_reward_num"] = (int)$row['award_goods_num'];
			$retparams->dayTask[$j]["t_status"] = (int)$row['t_status'];
			$retparams->dayTask[$j]["t_finish_progress"] = $f_progress;
			$retparams->dayTask[$j++]["t_total_progress"] = (int)$row['t_total_progress'];
		}
		$db->disconnect() ;

		$logs_array = array("return data., ret"=>json_encode($retparams));
		logs::addLog($logs_array, $logfile);
		
		return json_encode($retparams) ;
	}else{
		$db->disconnect() ;
		return false ;
	}
}
?>
