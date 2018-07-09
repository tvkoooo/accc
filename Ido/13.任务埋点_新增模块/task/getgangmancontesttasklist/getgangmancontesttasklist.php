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
	logs::addLog("INFO::taskinfo::getgangmancontesttasklist::获得帮会个人竞技任务列表  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid")) {
		//$error_logs_array = array("lost property"=>" (uid)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::getgangmancontesttasklist::获得帮会个人竞技任务列表  params lost uid ", $logfile);
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
	
	//<2018-6-27备注>反正都要查库，就不做缓存查询了
	//$key = 'uid:' . $jparams->uid;
	//$value = $redis->get($key);
	//$user = json_decode($value, true);
	//$gangid = $user['union_id'];
	$unionLevel = 0;
	
    $sql_1 = "select t.union_id, un.union_up_level from raidcall.uinfo t 
        left join raidcall.union_info un on un.id = t.union_id where t.id = $jparams->uid";
	$rows_1 = $db->query($sql_1) ;
	if($row = $db->fetch_assoc($rows_1)){
	    $gangid = $row['union_id'];
	    $unionLevel = $row['union_up_level'];
	}
	else {
	    logs::addLog("WARN::taskinfo::getgangmancontesttasklist::获得帮会个人竞技任务列表 uid:$uid  SQL_FAIL=>sql_1:$sql_1 ", $logfile);
	}
	
	if(empty($gangid)){
	    $retparams->resCode = 101;
	    $db->disconnect() ;
	     
	    //$logs_array = array("return data., ret"=>json_encode($retparams));
	    //logs::addLog($logs_array, $logfile);
	    logs::addLog("INFO::taskinfo::getgangmancontesttasklist::获得帮会个人竞技任务列表  uid:$uid  empty(gangid) return data=>:".json_encode($retparams), $logfile);
	    return json_encode($retparams) ;
	}
	
	//TODO:根据帮会等级获得宝箱id
	$award_goods_id = 0;
	$sql_2 = "select t.parm1 as award_goods_id, t.parm2 as u_level from card.parameters_info t where t.id in(196, 197, 198, 199, 200, 201)";
	$rows_2 = $db->query($sql_2) ;
	while($row = $db->fetch_assoc($rows_2)){
	    if($unionLevel == $row['u_level']){
	        $award_goods_id = $row['award_goods_id'];
	    }
	}
	
	//查询该用户今天的帮会擂台任务
	$date = getCurDate();//date("Y-m-d");
	$query_3 = "select t.id, tc.task_name, tc.task_sketch, tc.target_type, tc.award_goods_num, 
	        t.t_status, t.t_finish_progress, t.t_total_progress, tc.target_params2, 
	        g.goods_icon, r.folder_path, u.status, union_up_level as union_level, ul.honor_level from card.task_info t 
			left join card.task_conf tc on t.t_id = tc.id 
			left join card.treasure_box_info b on b.id = $award_goods_id
			left join card.goods_info g on g.id = b.good_id
			left join card.user_all_box u on u.uid = $jparams->uid and u.task_id = t.id
			left join card.resoure_folder_info r on r.id = g.path_id
			left join raidcall.union_info ui on ui.id = $gangid
            left join `union`.union_level ul on ul.union_level = ui.union_up_level and ul.star_level = ui.union_up_star
			where t.uid = $jparams->uid and t.t_type = 13 and t.create_time = '$date'" ;
			
	//$logs_array = array("query"=>$query);
    //logs::addLog($logs_array, $logfile);
    
	$rows_3 = $db->query($query_3) ;

	//$logs_array = array("query"=>$query, "result"=>$rows);
    //logs::addLog($logs_array, $logfile);
        
	if($rows_3){
		$retparams->resCode = 200;
		$i = 0;
		while($row = $db->fetch_assoc($rows_3)){
		    $tid = (int)$row['id'];
	        $key = "gangman:$jparams->uid:$date:tid:$tid";
	        
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
			if($status == 2){			//帮会擂台任务如果已领取奖励，则改成已完成
			    $status = 4;
			}else{
			    $status = (int)$row['t_status'];
			}
			$retparams->data[$i]["t_status"] = $status;
			$retparams->data[$i]["t_finish_progress"] = $f_progress;
			$retparams->data[$i]["t_total_progress"] = (int)$row['t_total_progress'];
			
			$retparams->data[$i]["union_level"] = (int)$row['union_level']+1;
			$retparams->data[$i]["honor_level"] = base64_encode($row['honor_level']);
			$i++;
		}
		$db->disconnect() ;

		//$logs_array = array("return data., ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);
		logs::addLog("INFO::taskinfo::getgangmancontesttasklist::获得帮会个人竞技任务列表 uid:$uid gangid:$gangid retparams:".json_encode($retparams), $logfile);
		return json_encode($retparams) ;
	}else{
	    logs::addLog("WARN::taskinfo::getgangmancontesttasklist::获得帮会个人竞技任务列表 uid:$uid gangid:$gangid SQL_FAIL=>query_3:$query_3 ", $logfile);
		$db->disconnect() ;
		return false ;
	}
}
?>
