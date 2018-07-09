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
    $items = getRoomSunConfFromCache($redis);
    if (!empty($items)) {
        return $items;
    }

    $items = array();

    $key = "h_conf_room_glory_level";
    $sql = "SELECT * FROM card.room_sun_level";
    $rows = $db->query($sql);

    if (!empty($rows)) {
        $row = null;
        $row = $rows->fetch_assoc();
         
        while (!empty($row)) {
            $items[(int)$row['level']] = $row;
            $redis->hSet($key, $row['level'] . "", json_encode($row));
            $row = $rows->fetch_assoc();
        }
    }

    return $items;
}

//获得主播加成
function getSingerRatio($db, $redis, $glory_total)
{
    $conf_glory_inf = getRoomSunConf($db, $redis);
    if (empty($conf_glory_inf)) {
        break;
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
	logs::addLog("INFO::taskinfo::getsingertasklist::获得主播任务列表  params:$params ", $logfile);
	$retparams = new stdClass();
 	
	$jparams = json_decode($params);GlobalConfig::assign_server_id($jparams);
	$config = getDbConfig();
	$retparams->resCode = 403;

	if (!property_exists($jparams,"uid")) {
		//$error_logs_array = array("lost property"=>" (uid)");
        //logs::addLog($error_logs_array, $logfile);
	    logs::addLog("WARN::taskinfo::getsingertasklist::获得主播任务列表  params lost uid ", $logfile);
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
	
	$key = "userleaf_uid:$jparams->uid";
	$userLeaf = $redis->get($key);
	$retparams->leaf_total_num = 0;
	if(!empty($userLeaf)){
	    $retparams->leaf_total_num = (int)$userLeaf;
	}
	
	//任务宝箱需要的叶子数
	$sql_1 = "select t.parm1 as leaf_top from card.parameters_info t where t.id = 38";
	$rows_1 = $db->query($sql_1);
	if($row = $db->fetch_assoc($rows_1)){
	    $retparams->leaf_top = (int)$row['leaf_top'];
	}
	else {
	    logs::addLog("WARN::taskinfo::getsingertasklist::获得主播任务列表  uid:$uid SQL_FAIL=>sql_1:$sql_1 ", $logfile);
	}
	
	$sid = 0;
	$query_2 = "select s.sid from raidcall.sess_info s where s.owner = $jparams->uid";
	$rows_2 = $db->query($query_2);
	if($row = $db->fetch_assoc($rows_2)){
	    $sid = (int)$row['sid'];
	}
	else {
	    logs::addLog("WARN::taskinfo::getsingertasklist::获得主播任务列表  uid:$uid SQL_FAIL=>query_2:$query_2 ", $logfile);
	}
	//获取房间荣耀值信息(暂时不加)
	/* $key = "h_room_glory_inf";
	$field = "$sid:glory_total";
	$glory_total = $redis->hGet($key, $field);
		
	$ratio = getSingerRatio($db, $redis, $glory_total);
	logs::addLog("singerid:$retparams->uid:sid:$sid:ratio:$ratio:glory_total:$glory_total", $logfile); */

	//获取今天主播的面向主播的每日任务，包括等待领奖、未开启、已开启、完成的任务
	$date = getCurDate();//date("Y-m-d");
	$query_3 = "select t.id, tc.task_name, tc.task_type, tc.task_sketch, tc.target_type, tc.award_goods_num, rt.icon, t.t_status,
			t.t_finish_progress, t.t_total_progress, tc.target_params2, g.goods_icon, r.folder_path, tc.leaf, tc.sun, tc.debris, tc.gold, tc.open_object, tc.face_object from card.task_info t 
			left join card.task_conf tc on t.t_id = tc.id and tc.is_show = 1
			left join card.treasure_box_info b on b.id = tc.award_goods_id
			left join card.goods_info g on g.id = b.good_id
			left join card.resoure_folder_info r on r.id = g.path_id
			left join rcec_main.tool rt on rt.id = tc.target_params2
			where t.uid = $jparams->uid and t.t_status in(1,2,3,4) and t.t_type in(16, 1) and t.create_time = '$date' and tc.open_object = 1 and tc.face_object = 0 order by t.t_type DESC" ;
			
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
	        $key = "singeruid:$jparams->uid:$date:tid:$tid";
	        
	        $data = $redis->get($key);
	        $f_progress = 0;
	        if(!empty($data)){
	        	$data = json_decode($data, TRUE);
	        	$f_progress = $data['t_finish_progress'];
	        }
	        
	        if(empty($data)){
	            $key = "singerlast_task:uid:$jparams->uid:$date:tid:$tid";
	            $data = $redis->get($key);
	            if(!empty($data)){
	                $data = json_decode($data, TRUE);
	                $f_progress = $data['t_finish_progress'];
	            }
	        }
	        
			$retparams->data[$i]["t_id"] = (int)$row['id'];
			$retparams->data[$i]["t_name"] = base64_encode($row['task_name']);
			$retparams->data[$i]["t_desc"] = base64_encode($row['task_sketch']);
			$retparams->data[$i]["t_type"] = (int)$row['task_type'];
			$retparams->data[$i]["t_dest_type"] = (int)$row['target_type'];
			$imgpath = $row['folder_path']."/".$row['goods_icon'];
			$retparams->data[$i]["t_reward_img"] = base64_encode($imgpath);
			$retparams->data[$i]["t_reward_num"] = (int)$row['award_goods_num'];
			$retparams->data[$i]["t_status"] = (int)$row['t_status'];
			$retparams->data[$i]["t_finish_progress"] = $f_progress;
			$retparams->data[$i]["t_total_progress"] = (int)$row['t_total_progress'];
		    $retparams->data[$i]["t_leaf_num"] = (int)$row['leaf'];
		    
		    
		    
		    $retparams->data[$i]["t_gift_url"] = base64_encode($row['icon']);
		    
		    $sun = (int)$row['sun'];
			/* if(!empty($ratio)){
			    $sun = (1+$ratio)*$sun;
			} */
			$retparams->data[$i]["t_sun_num"] = $sun;
			//logs::addLog("singerid:$retparams->uid:sid:$sid:ratio:$ratio:glory_total:$glory_total:sunvalue:$sun", $logfile);
			
			$leaf = (int)$row['leaf'];
			$sun_revise = 0;
			$leaf_revise = 0;
			$debris = (int)$row['debris'];
			$gold = (int)$row['gold'];
			$debris_revise = 0;
			$gold_revise = 0;
				
			//奖励id 9：碎片  8：叶子 11：阳光  10：金币
			$n = 0;
// 			if(!empty($debris)){
			    $retparams->data[$i]["debris_id"] = 9;
			    $retparams->data[$i]["debris_revise"] = $debris_revise;
			    $retparams->data[$i]["debris_num"] = $debris;
// 			}
// 			if(!empty($leaf)){
			    $retparams->data[$i]["leaf_id"] = 8;
			    $retparams->data[$i]["leaf_revise"] = $leaf_revise;
			    $retparams->data[$i]["leaf_num"] = $leaf;
// 			}
// 			if(!empty($sun)){
			    $retparams->data[$i]["sun_id"] = 11;
			    $retparams->data[$i]["sun_revise"] = $sun_revise;
			    $retparams->data[$i]["sun_num"] = $sun;
// 			}
// 			if(!empty($gold)){
			    $retparams->data[$i]["gold_id"] = 10;
			    $retparams->data[$i]["gold_revise"] = $gold_revise;
			    $retparams->data[$i]["gold_num"] = $gold;
// 			}
			
			$i++;
		}
		$db->disconnect() ;

		//$logs_array = array("return data., ret"=>json_encode($retparams));
		//logs::addLog($logs_array, $logfile);
		logs::addLog("INFO::taskinfo::getsingertasklist::获得主播任务列表 uid:$uid  retparams:".json_encode($retparams), $logfile);
		
		return json_encode($retparams) ;
	}else{
	    logs::addLog("WARN::taskinfo::getsingertasklist::获得主播任务列表 uid:$uid SQL_FAIL=>query_3:$query_3 ", $logfile);
		$db->disconnect() ;
		return false ;
	}
}
?>
