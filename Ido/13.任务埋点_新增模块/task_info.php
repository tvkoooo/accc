<?

$path=dirname(__FILE__);
include_once "$path/../../bases/GlobalConfig.php";
include_once "$path/../../session/member/member_list.php" ;

$path=dirname(__FILE__);
include_once "$path/task_eventHandle.php";

class TaskInfo{
    const TARGET_TYPE_ONLINE_TIME = 1;//为在线时长
    const TARGET_TYPE_WATCH_TIME = 2;//为看直播时长
    const TARGET_TYPE_SHOW_TIME = 3;//为开播时长
    const TARGET_TYPE_SERIES_LOGIN = 4;//为连续登陆天数
    const TARGET_TYPE_SERIES_SHOW = 5;//为连续开播天数
    const TARGET_TYPE_SEND_GIFT = 6;//为送礼物
    const TARGET_TYPE_RECV_GIFT = 7;//为收礼物
    const TARGET_TYPE_ROOM_TEXT = 8;//为直播间发言
    const TARGET_TYPE_SHARE_SINGER = 9;//为分享主播
    const TARGET_TYPE_HOT = 10;//为上热门
    const TARGET_TYPE_WEEK_START = 11;//为上周星
    const TARGET_TYPE_ROOM_MANS = 12;//为主播间在线人数
    const TARGET_TYPE_KP_UPGRADE = 13;//为卡牌升级
    const TARGET_TYPE_HELP_SEARCH = 14;//为协助挖宝
    const TARGET_TYPE_MY_SEARCH = 15;//为自己挖宝
    const TARGET_TYPE_DO_ACT = 16;//为发起互动
    const TARGET_TYPE_PLAY_ACT = 17;//为参与互动
    
    const REDIS_KEY_EXPIRE_TIME = 172800;//2*24*60*60;
    const REDIS_KEY_MAIDIAN_TIME = 604800;//7*24*60*60;埋点数据缓存保存7天
    
	var $redis ;
	var $db ;

	public function __construct($redis, $db){
		$this->redis = $redis ;
		$this->db = $db ;
	}
	
	//获得帮会通用奖励
	public function getGangCommonAwards($taskid, $gangid){
	    $logfile=basename(__FILE__, '.php');
	    $sql = "select t.id from raidcall.uinfo t where t.union_id = $gangid";
	    $rows = $this->db->query($sql);
	    
	    $data = array();
	    while($row = $this->db->fetch_assoc($rows)){
	        $uid = (int)$row['id'];
	        $result = $this->getGangCommonAwards2($taskid, $uid);
	        if(!empty($result)){
	            $data[] = $result;
	        }
	    }
	    
	    //TODO:获得钥匙，需要把tool_num换成真正的钥匙字段
	    $sql = "select tc.key from card.task_info t 
	       left join card.task_conf tc on tc.id = t.t_id where t.id = $taskid";
	    $rows = $this->db->query($sql);
	    $key_num = 0;
	    if($row = $this->db->fetch_assoc($rows)){
	        $key_num = (int)$row['key'];
	    }
	    
	    $key = "union:key:unionid:$gangid";
	    $gang_key_num = $this->redis->incrBy($key, $key_num);
	    $this->redis->expire($key, 86410);
	    /* //TODO:更改task_info表的t_status值为4，表示已经发送了礼物
	    $query = "update card.task_info t set t_status = 4 where t.id = $taskid";
	    $rs = $this->db->query($query); */
	    
	    //任务埋点进度缓存修正
	    $m_key = "maidian:taskid:$taskid" ;
	    $this->redis->hIncrBy($m_key, "t_finish_progress",$key_num);
	    
	    //触发帮会集体任务埋点
	    {
	        $event_gangcommon = new CEventHandleTask;
    	    $event_gangcommon->redis = $this->redis;
    	    $event_gangcommon->db = $this->db;
	        $event_gangcommon->taskModule_gangCommonComplete_event($taskid,$gangid);
	    }
	    
	    logs::addLog("INFO::taskinfo::getGangCommonAwards:: taskid:$taskid gangid:$gangid gang_key_num:$gang_key_num", $logfile);
	    return $data;
	}
	
	public function getGangCommonAwards2($taskid, $uid){
	    $logfile=basename(__FILE__, '.php');
	    $sql = "select t.id, t.uid, t.create_time, tc.open_object, tc.task_type, tc.task_name, 
	    tc.task_sketch, tc.award_goods_id, ru.union_up_level from card.task_info t left join card.task_conf tc on t.t_id = tc.id
	    left join raidcall.union_info ru on ru.id = t.uid where t.id = $taskid";
	    
	    $now = time()*1000;
	    $data = array();
	    $rows = $this->db->query($sql);
	    if($row = $this->db->fetch_assoc($rows)){
	        $data["t_id"] = (int)$row['id'];
	        $data["uid"] = (int)$uid;
	        $data["t_type"] = (int)$row['task_type'];
	        $data["t_open_object"] = (int)$row['open_object'];
	        $data["t_name"] = base64_encode($row['task_name']);
	        $data["t_desc"] = base64_encode($row['task_sketch']);
	        
	        $data["status"] = 1;
	        $data["sid"] = 0;
	        	
	        /* $tid = (int)$row['id'];
	        $level = (int)$row['union_up_level'];
	        $openObj = (int)$row['open_object'];
	        $dropids = explode("|",$row['award_goods_id']);
	        $dropid = (int)$dropids[$level];
	        
	        $date = $row['create_time'];
	        $sql = "INSERT INTO card.user_all_box (drop_id,good_id,uid,create_time,status, type, task_id) select $dropid, good_id, $uid, $now, 1, 1, $taskid from card.treasure_box_info where id = $dropid";
	        $rss = $this->db->query($sql);
	        logs::addLog("执行插入宝箱sql:$sql", $logfile);
	        if($rss){
	            $sql = "select LAST_INSERT_ID() as id";
	            $rs = $this->db->query($sql);
	            $boxid = 0;
	            if($r = $this->db->fetch_assoc($rs)){
	                $data["award_goods_id"] = (int)$r['id'];
	                $boxid = (int)$r['id'];
	                logs::addLog("getGangCommonAwards2::执行插入宝箱后，获得的主键id：$boxid, sql:$sql", $logfile);
	                $sql = "select g.goods_icon, r.folder_path from card.treasure_box_info t 
                        left join card.goods_info g on g.id = t.good_id
                        left join card.resoure_folder_info r on r.id = g.path_id where t.id = $dropid";
	                $rs = $this->db->query($sql);
	                if($r = $this->db->fetch_assoc($rs)){
	                    $imgpath = $r['folder_path']."/".$r['goods_icon'];
	                    $data["goods_icon_close"] = $imgpath;
	                    $data["goods_icon_open"] = $imgpath.'p';
	                }else{
	                    $logs_array = array("getGangCommonAwards2::获取宝箱图片失败。 excute sql error, sql:"=>$sql);
	                    logs::addLog($logs_array, $logfile);
	                }
	            }else{
	                $logs_array = array("getGangCommonAwards2::获取宝箱id失败。 excute sql error, sql:"=>$sql);
	                logs::addLog($logs_array, $logfile);
	                return false;
	            }
	            
	        }else{
	            $logs_array = array("getGangCommonAwards2::excute sql error, sql:"=>$sql);
	            logs::addLog($logs_array, $logfile);
	            return false;
	        } */
	    }
	    logs::addLog("INFO::taskinfo::getGangCommonAwards2:: taskid:$taskid uid:$uid ",$logfile);
	    //logs::addLog("DEBUG::taskinfo::getGangCommonAwards2:: taskid:$taskid uid:$uid $data:".json_encode($data),$logfile);//debug
	    return $data;
	}
	
	
	
	public function getCommonAwards($taskid){
	    $logfile=basename(__FILE__, '.php');
	    $sql = "select t.id, t.uid, t.create_time, tc.open_object, tc.task_type, tc.task_name, 
	    tc.task_sketch, tc.award_goods_id, tc.active_exp, tc.bonus_exp, tc.leaf, tc.sun 
	    from card.task_info t left join card.task_conf tc on t.t_id = tc.id where t.id = $taskid";
	    
	    $now = time();
	    $data = array();
	    $rows = $this->db->query($sql);
	    if($row = $this->db->fetch_assoc($rows)){
	        $uid = (int)$row['uid'];
	        $type = (int)$row['task_type'];
	        
	        $data["t_id"] = (int)$row['id'];
	        $data["uid"] = $uid;
	        $data["t_type"] = $type;
	        $data["t_open_object"] = (int)$row['open_object'];
	        $data["t_name"] = base64_encode($row['task_name']);
	        $data["t_desc"] = base64_encode($row['task_sketch']);
	        $data["status"] = 1;
	        
	        //TODO:根据uid到缓存里读sid
	        $user_at_room_key = member_list::HashUserAtRoomKey($uid);
	        $sid = $this->redis->hGet($user_at_room_key, $uid);
	        $data["sid"] = (int)$sid;
	        
	        $openObj = (int)$row['open_object'];
	        //跑环任务
	        if(11 == $type && !empty($sid)){
	            $sql = "select t.owner from raidcall.sess_info t where t.sid = $sid";
	            $rs = $this->db->query($sql);
	            $singerid = 0;
	            if($r = $this->db->fetch_assoc($rs)){
	                $singerid = (int)$r['owner'];
	            }
	            
	            $sql = "select t.parm1 from card.parameters_info t where t.id = 165";
	            $rs = $this->db->query($sql);
	            $total_num = 9999;
	            if($r = $this->db->fetch_assoc($rs)){
	                $total_num = (int)$r['parm1'];
	            }
	            
	            $date = $this->getdate();//date("Y-m-d");
	            $singerdata = array();
	            $key = "singer_day_sun_task:$date:$singerid";
	            $v = $this->redis->get($key);
	            $vv = json_decode($v, TRUE);
	            
	            $finish_num = 0;
	            if(empty($vv)){
	                $data['singerid'] = $singerid;
	                $data['finish_num'] = 1;
	                $data['total_num'] = $total_num;
	                
	                $singerdata['singerid'] = $singerid;
	                $singerdata['finish_num'] = $finish_num = 1;
	                $singerdata['total_num'] = $total_num;
	                $value = json_encode($singerdata);
	                $this->redis->set($key, $value);
	                $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
	                logs::addLog("INFO::taskinfo::getCommonAwards:: taskid:$taskid uid:$uid key=>:$key V=>:$value",$logfile);
	            }else{
	                $data['singerid'] = $singerid;
	                $data['finish_num'] = $finish_num = $vv['finish_num']+1;
	                $data['total_num'] = $total_num;
	                
	                $singerdata['singerid'] = $singerid;
	                $singerdata['finish_num'] = $vv['finish_num']+1;
	                $singerdata['total_num'] = $total_num;
	                $value = json_encode($singerdata);
	                $this->redis->set($key, $value);
	                logs::addLog("INFO::taskinfo::getCommonAwards:: taskid:$taskid uid:$uid key=>:$key V=>:$value",$logfile);
	            }	            
	            
	            //TODO:拆分xxxxxxxxxxx
	            $sql = "SELECT COUNT(*) as size FROM rcec_record.anchor_user_sun_task_detail t where t.singerid = $singerid and t.createTime = \"$date\"";
	            $rs = $this->db->query($sql);
	            $r = $this->db->fetch_assoc($rs);
	            if($r['size'] > 0){
	                $sql = "UPDATE rcec_record.anchor_user_sun_task_detail SET updateTime = $now AND finish_num = $finish_num WHERE singerid = $singerid and createTime = \"$date\"";
	            }else{
	                $sql = "INSERT INTO rcec_record.anchor_user_sun_task_detail(singerid, finish_num, total_num, createTime, updateTime)
	                   VALUES($singerid, $finish_num, $total_num, \"$date\", $now)";
	            }
	            
	            //TODO:rcec_recard.anchor_user_sun_task_detail
	            /* $sql = "INSERT INTO rcec_record.anchor_user_sun_task_detail(singerid, finish_num, total_num, createTime, updateTime)
	               VALUES($singerid, $finish_num, $total_num, \"$date\", $now) ON DUPLICATE KEY UPDATE singerid = VALUES(singerid), finish_num = VALUES(finish_num), total_num=VALUES(total_num), createTime=VALUES(createTime), updateTime = VALUES(updateTime)";
	             */
	            $rs = $this->db->query($sql);
	            
	            $json_data = json_encode($data);
	            logs::addLog("INFO::taskinfo::getCommonAwards::taskid:$taskid singer user finish sun task_data: $json_data", $logfile);
	        }
	        
	        //如果是主播开启的项目，则主播可以开启下一个任务
	        /* if(1 == $openObj){
	            $key = "taskstart::uid:$uid:$date";
	            $this->redis->del($key);
	        } */
	        
	        /* 	领取叶子奖励时用
	        $tid = (int)$row['id'];
	        $uid = (int)$row['uid'];
	        $openObj = (int)$row['open_object'];
	        $dropid = (int)$row['award_goods_id'];
	        $date = $row['create_time'];

	        $sql = "INSERT INTO card.user_all_box (drop_id,good_id,uid,create_time,status, type, task_id) select $dropid, good_id, $uid, $now, 1, 1, $taskid from card.treasure_box_info where id = $dropid";
	        $rss = $this->db->query($sql);
	        logs::addLog("getCommonAwards::执行插入宝箱sql:$sql", $logfile);
	        if($rss){
	            $sql = "select LAST_INSERT_ID() as id";
	            $rs = $this->db->query($sql);
	            $boxid = 0;
	            if($r = $this->db->fetch_assoc($rs)){
	                $data["award_goods_id"] = (int)$r['id'];
	                $boxid = (int)$r['id'];
	                logs::addLog("执行插入宝箱后，获得的主键id：$boxid, sql:$sql", $logfile);
	                $sql = "select g.goods_icon, r.folder_path from card.treasure_box_info t 
                        left join card.goods_info g on g.id = t.good_id
                        left join card.resoure_folder_info r on r.id = g.path_id where t.id = $dropid";
	                $rs = $this->db->query($sql);
	                if($r = $this->db->fetch_assoc($rs)){
	                    $imgpath = $r['folder_path']."/".$r['goods_icon'];
	                    $data["goods_icon_close"] = $imgpath;
	                    $data["goods_icon_open"] = $imgpath.'p';
	                }else{
	                    $logs_array = array("getCommonAwards::获取宝箱图片失败。 excute sql error, sql:"=>$sql);
	                    logs::addLog($logs_array, $logfile);
	                }
	            }else{
	                $logs_array = array("getCommonAwards::获取宝箱id失败。 excute sql error, sql:"=>$sql);
	                logs::addLog($logs_array, $logfile);
	                return false;
	            }
	            //TODO:更改task_info表的t_status值为4，表示已经发送了礼物
	            $query = "update card.task_info t set t_status = 4, t_box_id=$boxid where t.id = $tid";
	            $rs = $this->db->query($query);
	    
	            //如果是主播开启的项目，则主播可以开启下一个任务
	            if(1 == $openObj){
	                $key = "taskstart::uid:$uid:$date";
	                $this->redis->del($key);
	            }
	        }else{
	            $logs_array = array("getCommonAwards::excute sql error, sql:"=>$sql);
	            logs::addLog($logs_array, $logfile);
	            return false;
	        } */
	    }
	    
	    return $data;
	}
	
	//帮会星级任务，不处理返回值
	public function dealGangStartAwards($gangid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::dealGangStartAwards:: gangid:$gangid, target_type:$target_type num:$num Function_enter", $logfile); //debug

	    /* $gangid = $this->getGangId($uid);
	    if(empty($gangid)){
	        logs::addLog("getGangStartAwards::*uid:$uid,不是帮会成员.", $logfile);
	        return false;
	    } */
	    
	    $data = array();
	    $h_key = $this->get_gangstart_target_keys_by_gangid($gangid, $target_type);

	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::dealGangStartAwards::gangid:$gangid 没有帮会星级任务******, targetType:$target_type", $logfile);
	        return false;
	    }
	    
	    $result = array();
	    foreach ($fields as $field){
	        $key = $this->redis->hget($h_key, $field);
	        $value = $this->redis->get($key);
	         
	        //如果该用户没有该任务，则不执行
	        if(empty($value)){
	            continue;
	        }
	    
	        $data = json_decode($value, TRUE);
	    
	        /* if(!empty($extra_param)){
	            if(!empty($data['t_attach_param'])
                    && $data['t_attach_param'] != $extra_param){
                        continue;
	            }
	        } */
	        if(!empty($data['t_attach_param'])){
	            if(!empty($extra_param)
	                && $data['t_attach_param'] != $extra_param
	                || empty($extra_param)){
	                    //logs::addLog("WARN::taskinfo::dealGangStartAwards::gangid:$gangid extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
	                    continue;
	            }
	        }
	        	
	        if($data['status'] == 1){
	            //logs::addLog("DEBUG::taskinfo::dealGangStartAwards::该帮会星级任务已完成******gangid:$gangid, key:$key", $logfile);//DEBUG
	            continue;
	        }
	        	
	        if(($num >= $data['t_total_progress']) ||
	            ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
	                $data['t_finish_progress'] = $data['t_total_progress'];
	        }else{
	            $data['t_finish_progress']+= $num;
            }
            
            logs::addLog("INFO::taskinfo::dealGangStartAwards:: 帮会星级任务进度打点  finish_once gangid:$gangid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            //帮会在线人数不做累加直接赋值（已经去掉）
            /* if(22 == $target_type){
                $data['t_finish_progress'] = $num;
                if($num > $data['t_total_progress']){
                    $data['t_finish_progress'] = $data['t_total_progress'];
                }
            } */
	    
            $this->redis->set($key, json_encode($data));
            $now = time();
            /* $sql = "update card.task_info t set update_time=$now, t_finish_progress = t_finish_progress+$num
             where t.id = ".$data['id'];
            $rows = $this->db->query($sql); */
            
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                
                $tmp_key = "gangstar_task_tmp_gangid:$gangid:tid:".$data['id'];
                $tmp_value = $this->redis->incrBy($tmp_key, 1);
                $this->redis->expire($tmp_key, 2*24*60*60);		//设置两天过期
                if($tmp_value > 1){
                    logs::addLog("WARN::taskinfo::dealGangStartAwards::重复完成帮会星级任务  Str_key:$tmp_key", $logfile);
                    continue;
                    //return $result;
                }
                
                //TODO:处理帮会表中的星星数和星星等级
                $sql = "update raidcall.union_info set union_level_id=(
                        select max(union_level) from `union`.union_level where star_level<=union_current_star+1
                        ), union_current_star = union_current_star+1 where id = $gangid and union_current_star < 20";
                $r = $this->db->query($sql);
                if(!$r){
                    logs::addLog("WARN::taskinfo::dealGangStartAwards::任务完成，但更新数据库union_info表失败***gangid:$gangid, Str_key:$key, exe sql error!**sql:$sql", $logfile);
                    return $result;
                }
    			logs::addLog("INFO::taskinfo::dealGangStartAwards:: union_current_star plus 1 at task_info.id = ".$data['id']." gangid = $gangid, sql:$sql", $logfile);

                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->db->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->redis->set($key, json_encode($data));
                    
                    logs::addLog("INFO::taskinfo::dealGangStartAwards:: 帮会星级任务完成打点  finish_task gangid:$gangid Str_key:$key task_id:".$data['id'], $logfile);
                    //触发帮会星级任务埋点
                    {
                        $event_gangcommon = new CEventHandleTask;
						$event_gangcommon->redis = $this->redis;
						$event_gangcommon->db = $this->db;
                        $taskid = $data['id'];
                        $event_gangcommon->taskModule_gangStarComplete_event($taskid,$gangid);
                    }
                    
                    //帮会星级任务没有奖励
                    /* $result = $this->getCommonAwards($data['id']);
                    //数据回滚
                    if(!$result){
                        $sql = "update card.task_info t set t_status=1 where t.id = ".$data['id'];
                        $rows = $this->db->query($sql);
    
                        $data['status'] = 1;
                        $this->redis->set($key, json_encode($data));
                        logs::addLog("getGangStartAwards::任务完成，但执行getCommonAwards失败，数据回滚...***gangid:$gangid, key:$key", $logfile);
                    } */
                }else{
                    logs::addLog("WARN::taskinfo::dealGangStartAwards::任务完成，但更新数据库task_info表失败***gangid:$gangid, Str_key:$key, exe sql error!**sql:$sql", $logfile);
                }
            }
	    }
	    
	    logs::addLog("INFO::taskinfo::dealGangStartAwards:: gangid:$gangid target_type:$target_type num:$num unction_exit ", $logfile);
	    return $result;
	    
	}
	
	//获得师徒任务奖励
	public function getMasterAndApprenticeAwards($uid, $target_type, $num, $extra_param, $masterid){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::getMasterAndApprenticeAwards::uid:$uid:target_type:$target_type num:$num", $logfile);//debug
	    
	    $data = array();
	    $h_key = $this->get_master_apprentice_target_keys_by_uid($uid, $target_type);
	    
	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::getMasterAndApprenticeAwards::师徒任务***empty(fields)***uid:$uid, targetType:$target_type", $logfile);
	        return false;
	    }
	    
	    $result = array();
	    foreach ($fields as $field){
            $key = $this->redis->hget($h_key, $field);
            $value = $this->redis->get($key);
             
            //如果该用户没有该任务，则不执行
            if(empty($value)){
                continue;
            }
            
            $data = json_decode($value, TRUE);
            
            if(!empty($data['t_attach_param'])){
                if(!empty($extra_param)
                    && $data['t_attach_param'] != $extra_param
                    || empty($extra_param)){
                        //logs::addLog("WARN::taskinfo::getMasterAndApprenticeAwards::uid:$uid 师徒任务 Str_key:$key extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
                        continue;
                }
            }
    	    
    	    if($data['status'] == 1){
    	        //logs::addLog("DEBUG::taskinfo::getMasterAndApprenticeAwards::师徒任务已完成******uid:$uid, key:$key", $logfile);//debug
    	        continue;
    	    }
    	    
    	    if(($num >= $data['t_total_progress']) ||
    	        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
                $data['t_finish_progress'] = $data['t_total_progress'];
            }else{
                $data['t_finish_progress']+= $num;
            }
            
            logs::addLog("INFO::taskinfo::getMasterAndApprenticeAwards:: 师徒任务进度打点  finish_once uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            
            $this->redis->set($key, json_encode($data));
            //任务埋点进度缓存修正
            $task_id = $data['id'];
            $m_key = "maidian:taskid:$task_id" ;
            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);
            
            
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $now = time();
                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id']." and t.t_status = 0";
                $rows = $this->db->query($sql);
                if($rows && $this->db->affected_rows() > 0){
                    $data['status'] = 1;
                    $this->redis->set($key, json_encode($data));
                    
                    logs::addLog("INFO::taskinfo::getMasterAndApprenticeAwards:: 师徒任务完成打点  finish_task uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
                    //任务埋点状态登记缓存修正
                    $t_total_progress = $data['t_total_progress'];
                    $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
                    //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);
                    
                    $result = $this->getCommonAwards($data['id']);
                    
                    $key = "masterid:$masterid:discipleid:$uid";
                    $v = array();
                    $v_str = $this->redis->get($key);
                    $v = json_decode($v_str, TRUE);
                    $v['finish_progress'] = $v['finish_progress']+1;
                    $v_str = json_encode($v);
                    $this->redis->set($key, $v_str);
                    
                    
                    //if($v['finish_progress'] >= $v['total_progress']){
                    //活跃等级达到10级以上，师徒关系结束
                    if(49 == $target_type){
                        $key = "disciple:count:down:$uid";
                        $this->redis->del($key);
                        logs::addLog("INFO::taskinfo::getMasterAndApprenticeAwards total finish 师徒关系结束 ::uid:$uid DEL_key:$key", $logfile);
                    }
                    
                    logs::addLog("INFO::taskinfo::getMasterAndApprenticeAwards is finish::uid:$uid,target_type:$target_type", $logfile);
                    
                }else{
                    logs::addLog("WARN::taskinfo::getGangManTaskAwards::任务完成，但更新数据库失败***uid:$uid, key:$key, exe sql error!**sql:$sql", $logfile);
                }
            }
        }
    
        //logs::addLog("DEBUG::taskinfo::getMasterAndApprenticeAwards Function_exit**data:".json_encode($data), $logfile);
        return $result;
	}
	
	//获得帮会成员个人任务奖励
	public function getGangManTaskAwards($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::getGangManTaskAwards::uid:$uid:target_type:$target_type num:$num", $logfile);
	    
	    //<2018-6-26新增> 如果是主播，主播没有帮会
	    $is_singer = $this->isSinger($uid);
	    if ($is_singer) {
	        return false;
	    }
	    
	    $data = array();
	    $h_key = $this->get_gang_man_target_keys_by_uid($uid, $target_type);
	    
	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::getGangManTaskAwards::帮会成员个人任务**empty(fields)***uid:$uid, targetType:$target_type", $logfile);
	        return false;
	    }
	    
	    //logs::addLog("DEBUG::taskinfo::getGangManTaskAwards:: Has_key:$h_key count_value:".count($fields), $logfile);
	    
	    $result = array();
	    foreach ($fields as $key){
            //$key = $this->redis->hget($h_key, $key);
            $value = $this->redis->get($key);
            
            //logs::addLog("DEBUG::taskinfo::getGangManTaskAwards:: Str_key:$key", $logfile);
             
            //如果该用户没有该任务，则不执行
            if(empty($value)){
                logs::addLog("WARN::taskinfo::getGangManTaskAwards:: uid:$uid Str_key:$key, value is empty.", $logfile);
                continue;
            }
            
            $data = json_decode($value, TRUE);
            
            if(!empty($data['t_attach_param'])){
                if(!empty($extra_param)
                    && $data['t_attach_param'] != $extra_param
                    || empty($extra_param)){
                        //logs::addLog("WARN::taskinfo::getGangManTaskAwards:: uid:$uid extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
                        continue;
                }
            }
    	    
    	    if($data['status'] == 1){
    	        //logs::addLog("DEBUG::taskinfo::getGangManTaskAwards::帮会成员个人任务已完成******uid:$uid, Str_key:$key", $logfile);
    	        continue;
    	    }
    	    
    	    if(($num >= $data['t_total_progress']) ||
    	        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
                $data['t_finish_progress'] = $data['t_total_progress'];
            }else{
                $data['t_finish_progress']+= $num;
            }
            
            logs::addLog("INFO::taskinfo::getGangManTaskAwards:: 帮会成员个人任务进度打点  finish_once uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            
            $this->redis->set($key, json_encode($data));

            //任务埋点进度缓存修正
            $task_id = $data['id'];
            $m_key = "maidian:taskid:$task_id" ;
            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);
            
            
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = $this->getdate();//date("Y-m-d");
                $now = time();
                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->db->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->redis->set($key, json_encode($data));
                    
                    logs::addLog("INFO::taskinfo::getGangManTaskAwards:: 帮会成员个人任务完成打点  finish_task uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
                    //触发帮会个人任务埋点
                    {
                        //任务埋点状态登记缓存修正
                        $t_total_progress = $data['t_total_progress'];
                        $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
                        //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);
                    
                        $event_gangman = new CEventHandleTask;
                        $event_gangman->redis = $this->redis;
                        $event_gangman->db = $this->db;
                        $event_gangman->taskModule_gangManComplete_event($task_id, $uid);
                    }
                    
                    $result[] = $this->getCommonAwards($data['id']);                   

                    $g_rs = array();
                    if( 41 == $target_type ||
                        18 == $target_type){//6 == $target_type ||
                            //获得帮会礼物任务
                            $g_rs = $this->getGangAwards($uid, 39, 1, null);	//完成每日帮会成员个人礼物任务次数
                            logs::addLog("INFO::taskinfo::getGangManTaskAwards::uid:$uid getGangAwards:gifttask target_type 39 ", $logfile);
                    }elseif (34 == $target_type ||
                        29 == $target_type){//40 == $target_type ||
                            $g_rs = $this->getGangAwards($uid, 38, 1, null);	//完成每日帮会成员个人竞技任务次数
                            logs::addLog("INFO::taskinfo::getGangManTaskAwards::uid:$uid getGangAwards:contesttask target_type 38 ", $logfile);
                    }elseif(27 == $target_type ||
                        2 == $target_type){
                        $g_rs = $this->getGangAwards($uid, 37, 1, null);	//完成每日帮会成员个人活跃任务次数
                        logs::addLog("INFO::taskinfo::getGangManTaskAwards::uid:$uid getGangAwards:activetask target_type 37", $logfile);
                    }
                    
                    if(!empty($g_rs)){                       

                        for($i = 0; $i < count($g_rs); $i++){
                            $result[] = $g_rs[$i];
                        }
                    }
                    
                    //师徒任务
                    $masterid = $this->getMasterByApprenticeid($uid);
                    if(!empty($masterid)){
                        $flag = $this->getMasterAndApprenticeAwards($uid, 52, 1, null, $masterid);
                        if(!empty($flag)){
                            $result[] = $flag;
                        }
                    }
                    
                }else{
                    logs::addLog("WARN::taskinfo::getGangManTaskAwards::任务完成，但更新数据库失败***uid:$uid, Str_key:$key, exe sql error!**sql:$sql", $logfile);
                }
            }
        }    
        //logs::addLog("DEBUG::taskinfo::getGangManTaskAwards end!*********data:".json_encode($result), $logfile);
        return $result;
	}
	
	//获取帮会任务奖励
	public function getGangAwards($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    
	    //<2018-6-26新增> 如果是主播，主播没有帮会
	    $is_singer = $this->isSinger($uid);
	    if ($is_singer) {
	        return false;
	    }
	    
	    $gangid = $this->getGangId($uid);
	    if(empty($gangid)){
	        //logs::addLog("DEBUG::taskinfo::getGangAwards:: gangid:$gangid uid:$uid  不是帮会成员.", $logfile);
	        return false;
	    }	    
	    //logs::addLog("DEBUG::taskinfo::getGangAwards, gangid:$gangid extra_param:$extra_param target_type:$target_type  num:$num  uid:$uid", $logfile);
	    
	    $data = array();
	    $h_key = $this->get_gang_target_keys_by_gangid($gangid, $target_type);

	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::getGangAwards::没有帮会任务******gangid:$gangid, targetType:$target_type uid:$uid Has_key:$h_key ", $logfile);
	        return false;
	    }
	    
	    $result = array();
	    foreach ($fields as $field){
	        $key = $this->redis->hget($h_key, $field);
	        $value = $this->redis->get($key);
	         
	        //如果该用户没有该任务，则不执行
	        if(empty($value)){
	            logs::addLog("WARN::taskinfo::getGangAwards::帮会任务有Str_key没有value gangid:$gangid uid:$uid Has_key:$h_key Str_key:$key ", $logfile);
	            continue;
	        }
	    
	        $data = json_decode($value, TRUE);
	    
	        if(!empty($extra_param)
	            && !empty($data['t_attach_param'])
	            && $data['t_attach_param'] != $extra_param){
	                continue;
	        }
	        	
	        if($data['status'] == 1){
	            logs::addLog("WARN::taskinfo::getGangAwards::该帮会任务已完成  gangid:$gangid uid:$uid Str_key:$key ", $logfile);
	            continue;
	        }
	        	
	        if(($num >= $data['t_total_progress']) ||
	            ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
	                $data['t_finish_progress'] = $data['t_total_progress'];
	            }else{
	                $data['t_finish_progress']+= $num;
	            }
	    
	            $this->redis->set($key, json_encode($data));
	            
	            logs::addLog("INFO::taskinfo::getGangAwards:: 帮会任务进度打点  finish_once gangid:$gangid uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
	            //任务埋点进度缓存修正
	            $task_id = $data['id'];
	            $m_key = "maidian:taskid:$task_id" ;
	            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);
	    
	            if($data['t_finish_progress'] >= $data['t_total_progress']){
	                $date = $this->getdate();//date("Y-m-d");
	                $now = time();
	                $sql = "update card.task_info t set t_status=4, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
	                " where t.id = ".$data['id'];
	                $rows = $this->db->query($sql);
	                if($rows){
	                    $data['status'] = 1;
	                    $this->redis->set($key, json_encode($data)); 
	                    
	                    logs::addLog("INFO::taskinfo::getGangAwards:: 帮会任务完成打点  finish_task gangid:$gangid uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
	                    //任务埋点状态登记缓存修正
	                    $t_total_progress = $data['t_total_progress'];
	                    $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
	                    //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);
	                    
	                    $result = $this->getGangCommonAwards($data['id'], $gangid);
	                    
	                    $taskName = "";
	                    $sql = "select c.task_name from card.task_conf c left join card.task_info t on c.id=t.t_id where t.id=" . $data['id'];
	                    $rowstmp = $this->db->query($sql);
						if($this->db->affected_rows() > 0){
							$rowtmp = $this->db->fetch_assoc($rowstmp);
							$taskName = $rowtmp['task_name'];
						}
						
						$summary = "帮会任务" . $taskName . "已完成，快去领大家努力的宝箱!";
						$content = "<font color='#8ca0c8'>帮会任务</font> <font color='#8ca0c8'>" . $taskName . "</font> <font color='#8ca0c8'>已完成，快去领大家努力的宝箱!</font>";
						$msg = array(
							'group_id' => $gangid,
							'content' => array(
									'type' => 0,
									'text' => $summary,
									'msgs' => array(
										0 => array(
											'content' => $content,
										)
									),
									'summary' => $summary
							)
						);
	                     
	                    $tmpKey = "gangtaskmsg:$gangid" . ":" . time();
	                    $this->redis->set($tmpKey, json_encode($msg));
	                    $rsp_data = file_get_contents(GlobalConfig::GetSendGrpMsgURL() . $tmpKey);
	                    logs::addLog("INFO::taskinfo::getGangAwards:: gangid:$gangid uid:$uid 发送消息至粉丝群rsp***" . $rsp_data, $logfile);
	                    
	                    //TODO:根据帮会$target_type转换成帮会星级$target_type
	                    //帮会星级目的类型：23为完成礼物任务 24为完成擂台任务 25为完成活跃任务
	                    //帮会目的类型：6为送礼物 39为完成个人礼物任务  
	                    //          38为完成个人竞技任务 40成功夺旗占领直播间
	                    //         36为帮会成员登录平台次数  37为完成个人活跃任务
	                    
	                    $start_target_type = 0;
	                    if(36 == $target_type
	                        || 37 == $target_type){
	                        $start_target_type = 25;
	                    }elseif (6 == $target_type
	                        || 39 == $target_type){
	                        $start_target_type = 23;
	                    }elseif (40 == $target_type
	                        || 38 == $target_type){
	                        $start_target_type = 24;
	                    }
	                    logs::addLog("INFO::taskinfo::getGangAwards:: gangid:$gangid uid:$uid 任务完成，执行帮会星级任务** taskid:".$data['id']." start_target_type:$start_target_type", $logfile);
	                    
	                    //TODO:判断任务id是否相同，如果相同则不处理
	                    $tmp_key = "gang_task_tmp_gangid:$gangid:tid:".$data['id'];
	                    $tmp_value = $this->redis->incrBy($tmp_key, 1);
	                    $this->redis->expire($tmp_key, 2*24*60*60);		//设置两天过期
	                    if($tmp_value > 1){
	                        logs::addLog("WARN::taskinfo::getGangAwards:: gangid:$gangid uid:$uid 重复完成帮会任务 领取记录Str_key:$tmp_key", $logfile);
	                        continue;
	                    }
	                    //获得帮会星级任务
	                    $this->dealGangStartAwards($gangid, $start_target_type, 1, $extra_param);
	                    
	                    //数据回滚
	                    if(!$result){
	                        $sql = "update card.task_info t set t_status=1 where t.id = ".$data['id'];
	                        $rows = $this->db->query($sql);
	    
	                        $data['status'] = 1;
	                        $this->redis->set($key, json_encode($data));
	                        logs::addLog("WARN::taskinfo::getGangAwards:: gangid:$gangid uid:$uid  任务完成，但执行getCommonAwards失败，数据回滚..., key:$key", $logfile);
	                    }
	                }else{
	                    logs::addLog("WARN::taskinfo::getGangAwards:: gangid:$gangid uid:$uid 任务完成，但更新数据库失败 , key:$key, exe sql error!**sql:$sql", $logfile);
	                }
	            }
	    }
	    
	    //logs::addLog("DEBUG::taskinfo::getGangAwards end! gangid:$gangid uid:$uid data:".json_encode($result), $logfile);
	    return $result;
	}
	
	//获取用户每日随机任务奖励
	public function getDayRandomTask($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::getDayRandomTask! uid:$uid num:$num", $logfile);
	    
	    $data = array();
	    $h_key = $this->get_day_random_target_keys_by_uid($uid, $target_type);
	    
	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::getDayRandomTask:: 没有该每日随机任务******uid:$uid, targetType:$target_type Has_key:$h_key", $logfile);
	        return false;
	    }
	    
	    $result = array();
	    foreach ($fields as $field){
            $key = $this->redis->hget($h_key, $field);
            $value = $this->redis->get($key);
             
            //如果该用户没有该任务，则不执行
            if(empty($value)){
                logs::addLog("WARN::taskinfo::getDayRandomTask:: 有Str_key但是没有value ******uid:$uid Has_key:$h_key Str_key:$key", $logfile);
                continue;
            }
            
            $data = json_decode($value, TRUE);
            
            if(!empty($extra_param) 
                && !empty($data['t_attach_param'])
                && $data['t_attach_param'] != $extra_param){
                continue;
            }
    	    
    	    if($data['status'] == 1){
    	        //logs::addLog("DEBUG::taskinfo::getDayRandomTask::该每日随机任务已完成******uid:$uid, Str_key:$key", $logfile);
    	        continue;
    	    }
    	    
    	    //如果目标类型为上热门，需要判断上热门的名次是否是任务规定的，如果不是则不处理
    	    if($target_type == TaskInfo::TARGET_TYPE_HOT){
    	        if($extra_param > $data['t_attach_param']){
    	            logs::addLog("INFO::taskinfo::getDayRandomTask::上热门名次不在任务规定名次内.******uid:$uid, Str_key:$key", $logfile);
    	            continue;
    	        }
    	    }
    	    
    	    if(($num >= $data['t_total_progress']) ||
    	        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
                $data['t_finish_progress'] = $data['t_total_progress'];
            }else{
                $data['t_finish_progress']+= $num;
            }
            
            $this->redis->set($key, json_encode($data));
            
            logs::addLog("INFO::taskinfo::getDayRandomTask:: 用户每日随机任务进度打点  finish_once uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            //任务埋点进度缓存修正
            $task_id = $data['id'];
            $m_key = "maidian:taskid:$task_id" ;
            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);
            
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = $this->getdate();//date("Y-m-d");
                $now = time();
                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->db->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->redis->set($key, json_encode($data));
                    
                    logs::addLog("INFO::taskinfo::getDayRandomTask:: 用户每日随机任务完成打点  finish_task uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
                    //任务埋点状态登记缓存修正
                    $t_total_progress = $data['t_total_progress'];
                    $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
                    //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);                    
                    
                    $result = $this->getCommonAwards($data['id']);
                    
                    //数据回滚
                    if(!$result){
                        $sql = "update card.task_info t set t_status=1 where t.id = ".$data['id'];
                        $rows = $this->db->query($sql);
                        
                        $data['status'] = 1;
                        $this->redis->set($key, json_encode($data));
                        logs::addLog("WARN::taskinfo::getDayRandomTask::任务完成，但执行getCommonAwards失败，数据回滚...***uid:$uid, Str_key:$key", $logfile);
                    }
                }else{
                    logs::addLog("WARN::taskinfo::getDayRandomTask::任务完成，但更新数据库失败***uid:$uid, Str_key:$key, exe sql error!**sql:$sql", $logfile);
                }
            }
        }
    
        //logs::addLog("DEBUG::taskinfo::getDayRandomTask end!****uid:$uid****data:".json_encode($result), $logfile);
        return $result;
	    
	}

	//处理粉丝团任务，没有返回值
	public function dealFollowerAwards($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::dealFollowerAwards! uid:$uid num:$num", $logfile);
	    
	    $singerid = $extra_param;
	    
	    $date = $this->getdate();//date("Y-m-d");
	    $h_key = "follower:uid:$uid:singerid:$singerid:$date:$target_type";
	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::dealFollowerAwards::没有该粉丝团任务******uid:$uid, singerid:$singerid, Has_key:$h_key", $logfile);
	        return;
	    }
	    foreach ($fields as $field){
	        $key = $this->redis->hget($h_key, $field);
	        $value = $this->redis->get($key);
	         
	        //如果该用户没有该任务，则不执行
	        if(empty($value)){
	            logs::addLog("WARN::taskinfo::dealFollowerAwards:: 有Str_key但是没有value ******uid:$uid Has_key:$h_key Str_key:$key", $logfile);
	            continue;
	        }
	    
	        $data = json_decode($value, TRUE);
	    
	        /* if(!empty($extra_param)
	            && !empty($data['t_attach_param'])
	            && $data['t_attach_param'] != $extra_param){
	                continue;
	        } */
	        	
	        if($data['status'] == 1){
	            //logs::addLog("DEBUG::taskinfo::dealFollowerAwards::该粉丝团任务已完成******uid:$uid, Str_key:$key", $logfile);
	            continue;
	        }
	        	
	        if(($num >= $data['t_total_progress']) ||
	            ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
                $data['t_finish_progress'] = $data['t_total_progress'];
            }else{
                $data['t_finish_progress']+= $num;
            }
    
            $this->redis->set($key, json_encode($data));
            logs::addLog("INFO::taskinfo::dealFollowerAwards:: 粉丝团任务进度打点  finish_once uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            //任务埋点进度缓存修正
            $task_id = $data['id'];
            $m_key = "maidian:taskid:$task_id" ;
            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);

    
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = $this->getdate();//date("Y-m-d");
                $now = time();
                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->db->query($sql);
                if(!$rows){
                    logs::addLog("WARN::dealFollowerAwards::任务完成，但更新数据库失败***uid:$uid, key:$key, exe sql error!**sql:$sql", $logfile);
                    return;
                }
                
                $t = date("Y-m-d H:i:s",time());
                $exp = $data['intimacy_exp'];
                $sql = "insert into cms_manager.fins_group_love(uid, zid, experience, loyalTask, create_time) 
                    values($uid, $singerid, $exp, ".$data['id'].", '$t')";
                $rows = $this->db->query($sql);
                if(!$rows){
                    logs::addLog("WARN::dealFollowerAwards::任务完成，但更新数据库失败***uid:$uid, key:$key, exe sql error!**sql:$sql", $logfile);
                    return;
                }
                
                $sql = "update cms_manager.loyal_fins_group set level=(
                        select max(f.level) from cms_manager.fans_anchor f where total_exp<=experience+$exp
                        ), experience = experience+$exp where uid = $singerid";
                $rows = $this->db->query($sql);
                if(!$rows){
                    logs::addLog("WARN::taskinfo::dealFollowerAwards::任务完成，但更新数据库失败***uid:$uid, key:$key, exe sql error!**sql:$sql", $logfile);
                    return;
                }
                
                $data['status'] = 1;
                $this->redis->set($key, json_encode($data));
                
                logs::addLog("INFO::taskinfo::getDayRandomTask:: 粉丝团任务完成打点  finish_task uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
                //任务埋点状态登记缓存修正
                $t_total_progress = $data['t_total_progress'];
                $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
                //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);
            }
	    }
	}
	
	public function getDayLoopTaskAwards($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::getDayLoopTaskAwards, uid:$uid,extra_param:$extra_param,target_type:$target_type, num:$num", $logfile);
	    
	    //<2018-6-26新增> 如果是主播，主播没有每日跑环任务
	    $is_singer = $this->isSinger($uid);
	    if ($is_singer) {
	        return false;
	    }
	    
	    //判断轮数是否已满，如果已满则不再去完成任务
	    $date = $this->getdate();//date("Y-m-d");
	    $l_key = "loop_num:uid:$uid:$date";
	    $l_datatmp = $this->redis->get($l_key);
	    $l_data = json_decode($l_datatmp, TRUE);
	    $l_num = $l_data['l_cur_num'];
	    $h_num = $l_data['h_cur_num'];
	    
	    if($l_num > $l_data['l_total_num']){
	        //已经完成了所有轮任务
	        return false;
	    }
	    
	    $data = array();
	    $h_key = $this->get_day_loop_target_keys_by_uid($uid, $target_type);
	    
	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::getDayLoopTaskAwards::empty(fields)  uid:$uid 用户当前跑环key:$l_key Has_key:$h_key", $logfile);
	        return false;
	    }
	    
	    $result = array();
	    foreach ($fields as $field){
            $key = $this->redis->hget($h_key, $field);
            $value = $this->redis->get($key);
             
            //如果该用户没有该任务，则不执行
            if(empty($value)){
                logs::addLog("WARN::taskinfo::getDayLoopTaskAwards::任务有Str_key没有value  uid:$uid 用户当前跑环key:$l_key Has_key:$h_key Str_key:$key", $logfile);
                continue;
            }
            
            $data = json_decode($value, TRUE);
    	    
    	    if($data['status'] == 1){
    	        //logs::addLog("DEBUG::taskinfo::getDayLoopTaskAwards::该每日跑环任务已完成******uid:$uid, key:$key", $logfile);
    	        continue;
    	    }
            
            //观看主播数
            if(28 == $target_type){
                $date = $this->getdate();//date("Y-m-d");
                $w_key = "watchsinger:$date:uid:$uid:singerid:$extra_param:tid:".$data['id'];
                $w_data = $this->redis->get($w_key);
                if(!empty($w_data)){
                    continue;
                }else {
                    $this->redis->set($w_key, $w_key);
                }
            }
            
            if(!empty($data['t_attach_param'])){
                if(!empty($extra_param)
                    && $data['t_attach_param'] != $extra_param
                    || empty($extra_param)){
                        //logs::addLog("WARN::taskinfo::getDayLoopTaskAwards::uid:$uid Str_key:$key extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
                        continue;
                }
            }
    	    
    	    //如果目标类型为上热门，需要判断上热门的名次是否是任务规定的，如果不是则不处理
    	    if($target_type == TaskInfo::TARGET_TYPE_HOT){
    	        if($extra_param > $data['t_attach_param']){
    	            logs::addLog("INFO::taskinfo::getDayLoopTaskAwards::上热门名次不在任务规定名次内.******uid:$uid, Str_key:$key", $logfile);
    	            continue;
    	        }
    	    }
    	    
    	    if(($num >= $data['t_total_progress']) ||
    	        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
                $data['t_finish_progress'] = $data['t_total_progress'];
            }else{
                $data['t_finish_progress']+= $num;
            }
            
            logs::addLog("INFO::taskinfo::getDayLoopTaskAwards:: 每日跑环任务进度打点  finish_once uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            //任务埋点进度缓存修正
            $task_id = $data['id'];
            $m_key = "maidian:taskid:$task_id";                
            $t_finish_progress = $data['t_finish_progress'];
            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);

            
            //logs::addLog("taskinfo::getDayLoopTaskAwards::fayan ci shu. uid:$uid, key:$key, target_type:$target_type, num:$num, finis_num:".$data['t_finish_progress'], $logfile);
            
            $this->redis->set($key, json_encode($data));
            
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = $this->getdate();//date("Y-m-d");
                $now = time();
				//将任务状态置成等待领奖
                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->db->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->redis->set($key, json_encode($data));
                    
                    $result = $this->getCommonAwards($data['id']);
                    
                    logs::addLog("INFO::taskinfo::getDayRandomTask:: 每日跑环任务完成打点  finish_task uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
                    //跑环任务完成事件
                    {
                        //任务埋点状态登记缓存修正                        
                        $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
                        $t_total_progress = $data['t_total_progress'] ;
                        //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);
                        
                        $event = new CEventHandleTask;
                        $event->redis = $this->redis;
                        $event->db = $this->db;
                        $event_type = 3;//跑环任务日志类型：3代表完成任务
                        $awardItems = array();//$awardItems 为空
                        $event->taskModule_loop_event($event_type, $uid, $task_id, $l_num, $h_num,&$awardItems);
                    }
                    
                    $sql = "INSERT INTO card.user_reddot_status_info (uid,tab_type,sub_type,recent_timestamp)  VALUES($uid, 0, 9, $now) ON DUPLICATE KEY UPDATE recent_timestamp=$now";
                    $rows = $this->db->query($sql);
                    if(!$rows){
                        logs::addLog("WARN::taskinfo::getDayLoopTaskAwards uid:$uid Str_key:$key task_id:$task_id reddot::插入红点失败！！！", $logfile);
                    }
                }else{
                    logs::addLog("WARN::taskinfo::getDayLoopTaskAwards uid:$uid Str_key:$key task_id:$task_id 任务完成，但更新数据库失败  exe sql error!**sql:$sql", $logfile);
                }
            }
        }

        //logs::addLog("DEBUG::taskinfo::getDayLoopTaskAwards end! uid:$uid, data:".json_encode($result), $logfile);
        return $result;
	}
	
	//获取主播挖宝任务
	public function getSingerTreasureAwards($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::getSingerTreasureAwards, uid:$uid,extra_param:$extra_param,target_type:$target_type, num:$num", $logfile);
	    
	    //<2018-6-26新增> 如果不是主播，没有主播挖宝任务
	    $is_singer = $this->isSinger($uid);
	    if (!$is_singer) {
	        return false;
	    }
	    
	    $data = array();
	    $h_key = $this->get_day_singer_treasure_target_keys_by_uid($uid, $target_type);
	    
	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::getSingerTreasureAwards::empty(fields) 没有挖宝任务******uid:$uid, Has_key:$h_key", $logfile);
	        return false;
	    }
	    
	    $result = array();
	    foreach ($fields as $field){
            $key = $this->redis->hget($h_key, $field);
            $value = $this->redis->get($key);
             
            //如果该用户没有该任务，则不执行
            if(empty($value)){
                logs::addLog("WARN::taskinfo::getSingerTreasureAwards::任务有Str_key没有value  uid:$uid Has_key:$h_key Str_key:$key", $logfile);
                continue;
            }
            
            $data = json_decode($value, TRUE);
                        
            /* if(!empty($extra_param)){
                if(!empty($data['t_attach_param'])
                    && $data['t_attach_param'] != $extra_param){
                        logs::addLog("extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
                        continue;
                }
            } */
            if(!empty($data['t_attach_param'])){
                if(!empty($extra_param)
                    && $data['t_attach_param'] != $extra_param
                    || empty($extra_param)){
                        //logs::addLog("WARN::taskinfo::getSingerTreasureAwards::uid:$uid Str_key:$key extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
                        continue;
                }
            }
    	    
    	    if($data['status'] == 1){
    	        //logs::addLog("DEBUG::taskinfo::getSingerTreasureAwards::该挖宝任务已完成******uid:$uid, Str_key:$key", $logfile);
    	        continue;
    	    }
    	    
    	    if(($num >= $data['t_total_progress']) ||
    	        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
                $data['t_finish_progress'] = $data['t_total_progress'];
            }else{
                $data['t_finish_progress']+= $num;
            }                       
            
            $this->redis->set($key, json_encode($data));
            
            logs::addLog("INFO::taskinfo::getSingerTreasureAwards:: 主播挖宝任务进度打点  finish_once uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            //任务埋点进度缓存修正
            $task_id = $data['id'];
            $m_key = "maidian:taskid:$task_id" ;
            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);
            
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = $this->getdate();//date("Y-m-d");
                $now = time();
                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->db->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->redis->set($key, json_encode($data));
                    
                    logs::addLog("INFO::taskinfo::getSingerTreasureAwards:: 主播挖宝任务完成打点  finish_task uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
                    //任务埋点状态登记缓存修正
                    $t_total_progress = $data['t_total_progress'];
                    $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
                    //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);
                    
                    $result = $this->getCommonAwards($data['id']);
                    //数据回滚
                    /* if(!$result){
                        $sql = "update card.task_info t set t_status=1 where t.id = ".$data['id'];
                        $rows = $this->db->query($sql);
                        
                        $data['status'] = 1;
                        $this->redis->set($key, json_encode($data));
                        logs::addLog("getDayAwards::任务完成，但执行getCommonAwards失败，数据回滚...***uid:$uid, key:$key", $logfile);
                    } */
                }else{
                    logs::addLog("WARN::taskinfo::getSingerTreasureAwards::任务完成，但更新数据库失败***uid:$uid, Str_key:$key, exe sql error!**sql:$sql", $logfile);
                }
            }
        }
    
        //logs::addLog("DEBUG::taskinfo::getSingerTreasureAwards end!*****uid:$uid****data:".json_encode($result), $logfile);
        return $result;
	}
	
	//获取主播终极任务
	public function getSingerLastAwards($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::getSingerLastAwards, uid:$uid,extra_param:$extra_param,target_type:$target_type, num:$num", $logfile);
	    
	    //<2018-6-26新增> 如果不是主播，没有主播终极任务
	    $is_singer = $this->isSinger($uid);
	    if (!$is_singer) {
	        return false;
	    }
	    
	    $data = array();
	    $h_key = $this->get_day_singer_last_target_keys_by_uid($uid, $target_type);
	    
	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::getSingerLastAwards::empty(fields) 主播终极任务******uid:$uid, Has_key:$h_key", $logfile);
	        return false;
	    }
	    
	    $result = array();
	    foreach ($fields as $field){
            $key = $this->redis->hget($h_key, $field);
            $value = $this->redis->get($key);
             
            //如果该用户没有该任务，则不执行
            if(empty($value)){
                logs::addLog("WARN::taskinfo::getSingerLastAwards::任务有Str_key没有value  uid:$uid Has_key:$h_key Str_key:$key", $logfile);                
                continue;
            }
            
            $data = json_decode($value, TRUE);
                        
            /* if(!empty($extra_param)){
                if(!empty($data['t_attach_param'])
                    && $data['t_attach_param'] != $extra_param){
                        logs::addLog("extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
                        continue;
                }
            } */
            if(!empty($data['t_attach_param'])){
                if(!empty($extra_param)
                    && $data['t_attach_param'] != $extra_param
                    || empty($extra_param)){
                        //logs::addLog("WARN::taskinfo::getSingerLastAwards::uid:$uid Str_key:$key extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);                        
                        continue;
                }
            }
    	    
    	    if($data['status'] == 1){
    	        //logs::addLog("DEBUG::taskinfo::getSingerLastAwards::主播终极任务已完成******uid:$uid, key:$key", $logfile);
    	        continue;
    	    }
    	    
    	    if(($num >= $data['t_total_progress']) ||
    	        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
                $data['t_finish_progress'] = $data['t_total_progress'];
            }else{
                $data['t_finish_progress']+= $num;
            }
            
            $this->redis->set($key, json_encode($data));
            
            logs::addLog("INFO::taskinfo::getSingerLastAwards:: 主播终极任务进度打点  finish_once uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            //任务埋点进度缓存修正
            $task_id = $data['id'];
            $m_key = "maidian:taskid:$task_id" ;
            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);
            
            
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = $this->getdate();//date("Y-m-d");
                $now = time();
                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->db->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->redis->set($key, json_encode($data));
                    
                    logs::addLog("INFO::taskinfo::getSingerLastAwards:: 主播终极任务完成打点  finish_task uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
                    //任务埋点状态登记缓存修正
                    $t_total_progress = $data['t_total_progress'];
                    $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
                    //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);
                    
                    $result = $this->getCommonAwards($data['id']);
                    //数据回滚
                    /* if(!$result){
                        $sql = "update card.task_info t set t_status=1 where t.id = ".$data['id'];
                        $rows = $this->db->query($sql);
                        
                        $data['status'] = 1;
                        $this->redis->set($key, json_encode($data));
                        logs::addLog("getDayAwards::任务完成，但执行getCommonAwards失败，数据回滚...***uid:$uid, key:$key", $logfile);
                    } */
                }else{
                    logs::addLog("WARN::taskinfo::getSingerLastAwards::任务完成，但更新数据库失败***uid:$uid, Str_key:$key, exe sql error!**sql:$sql", $logfile);
                }
            }
        }
    
        //logs::addLog("DEBUG::taskinfo::getSingerLastAwards end!***** uid:$uid, Str_key:$key ****data:".json_encode($result), $logfile);
        return $result;
	}
	
	//获取用户挖宝任务奖励
	public function getUserTreasureAwards($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::getUserTreasureAwards, uid:$uid,extra_param:$extra_param,target_type:$target_type, num:$num", $logfile);
	    
	    //<2018-6-26新增> 如果是主播，主播有自己的主播挖宝任务
	    $is_singer = $this->isSinger($uid);
	    if ($is_singer) {
	        return false;
	    }
	     
	    
	    $data = array();
	    $h_key = $this->get_day_treasure_target_keys_by_uid($uid, $target_type);
	    
	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::getUserTreasureAwards::empty(fields) 没有挖宝任务******uid:$uid, Has_key:$h_key", $logfile);
	        return false;
	    }
	    
	    $result = array();
	    foreach ($fields as $field){
            $key = $this->redis->hget($h_key, $field);
            $value = $this->redis->get($key);
             
            //如果该用户没有该任务，则不执行
            if(empty($value)){
                logs::addLog("WARN::taskinfo::getUserTreasureAwards::任务有Str_key没有value  uid:$uid Has_key:$h_key Str_key:$key", $logfile);
                continue;
            }
            
            $data = json_decode($value, TRUE);
                        
            /* if(!empty($extra_param)){
                if(!empty($data['t_attach_param'])
                    && $data['t_attach_param'] != $extra_param){
                        logs::addLog("extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
                        continue;
                }
            } */
            if(!empty($data['t_attach_param'])){
                if(!empty($extra_param)
                    && $data['t_attach_param'] != $extra_param
                    || empty($extra_param)){
                        //logs::addLog("WARN::taskinfo::getUserTreasureAwards::uid:$uid Str_key:$key extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
                        continue;
                }
            }
    	    
    	    if($data['status'] == 1){
    	        //logs::addLog("DEBUG::taskinfo::getUserTreasureAwards::该挖宝任务已完成******uid:$uid, key:$key", $logfile);
    	        continue;
    	    }
    	    
    	    if(($num >= $data['t_total_progress']) ||
    	        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
                $data['t_finish_progress'] = $data['t_total_progress'];
            }else{
                $data['t_finish_progress']+= $num;
            }
            
            $this->redis->set($key, json_encode($data));
            
            logs::addLog("INFO::taskinfo::getUserTreasureAwards:: 用户挖宝任务进度打点  finish_once uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            //任务埋点进度缓存修正
            $task_id = $data['id'];
            $m_key = "maidian:taskid:$task_id" ;
            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);
            
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = $this->getdate();//date("Y-m-d");
                $now = time();
                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->db->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->redis->set($key, json_encode($data));
                    
                    logs::addLog("INFO::taskinfo::getUserTreasureAwards:: 用户挖宝任务完成打点  finish_task uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
                    //任务埋点状态登记缓存修正
                    $t_total_progress = $data['t_total_progress'];
                    $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
                    //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);
                    
                    $result = $this->getCommonAwards($data['id']);
                    //数据回滚
                    /* if(!$result){
                        $sql = "update card.task_info t set t_status=1 where t.id = ".$data['id'];
                        $rows = $this->db->query($sql);
                        
                        $data['status'] = 1;
                        $this->redis->set($key, json_encode($data));
                        logs::addLog("getDayAwards::任务完成，但执行getCommonAwards失败，数据回滚...***uid:$uid, key:$key", $logfile);
                    } */
                }else{
                    logs::addLog("WARN::taskinfo::getUserTreasureAwards::任务完成，但更新数据库失败***uid:$uid, key:$key, exe sql error!**sql:$sql", $logfile);
                }
            }
        }
    
        //logs::addLog("DEBUG::taskinfo::getUserTreasureAwards end!*******uid:$uid***data:".json_encode($result), $logfile);
        return $result;
	}
	
	//获取主播每日任务奖励
	public function getSingerDayAwards($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::begin getSingerDayAwards, uid:$uid,extra_param:$extra_param,target_type:$target_type, num:$num", $logfile);
	    
	    //<2018-6-26新增> 如果不是主播，没有主播每日任务奖励
	    $is_singer = $this->isSinger($uid);
	    if (!$is_singer) {
	        return false;
	    }
	     
	    
	    $data = array();
	    //$key = $this->get_day_target_key_by_uid($uid, $target_type, $extra_param);
	    $h_key = $this->get_day_target_keys_by_uid($uid, $target_type);
	    
	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::getSingerDayAwards::empty(fields) 主播每日任务******uid:$uid, Has_key:$h_key", $logfile);
	        return false;
	    }
	    
	    $result = array();
	    foreach ($fields as $field){
            $key = $this->redis->hget($h_key, $field);
            $value = $this->redis->get($key);
             
            //如果该用户没有该任务，则不执行
            if(empty($value)){
                logs::addLog("WARN::taskinfo::getSingerDayAwards::任务有Str_key没有value  uid:$uid Has_key:$h_key Str_key:$key", $logfile);
                continue;
            }
            
            $data = json_decode($value, TRUE);
            
            //观看主播数
            /* if(28 == $target_type){
                $date = $this->getdate();//date("Y-m-d");
                $w_key = "watchsinger:$date:uid:$uid:singerid:$extra_param";
                $w_data = $this->redis->get($w_key);
                if(!empty($w_data)){
                    continue;
                }else {
                    $this->redis->set($w_key, $w_key);
                }
            } */
            
            /* if(!empty($extra_param)){
                if(!empty($data['t_attach_param'])
                    && $data['t_attach_param'] != $extra_param){
                        logs::addLog("extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
                        continue;
                }
            } */
            if(!empty($data['t_attach_param'])){
                if(!empty($extra_param)
                    && $data['t_attach_param'] != $extra_param
                    || empty($extra_param)){ 
                        //logs::addLog("WARN::taskinfo::getSingerDayAwards::uid:$uid Str_key:$key extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
                        continue;
                }
            }
            
            /* if(!empty($extra_param) 
                && !empty($data['t_attach_param'])
                && $data['t_attach_param'] != $extra_param){
                continue;
            } */
    	    
    	    if($data['status'] == 1){
    	        //logs::addLog("DEBUG::taskinfo::getDayAwards::该每日任务已完成******uid:$uid, key:$key", $logfile);
    	        continue;
    	    }
    	    
    	    //如果目标类型为上热门，需要判断上热门的名次是否是任务规定的，如果不是则不处理
    	    if($target_type == TaskInfo::TARGET_TYPE_HOT){
    	        if($extra_param > $data['t_attach_param']){
    	            logs::addLog("INFO::taskinfo::getDayAwards::上热门名次不在任务规定名次内.******uid:$uid, key:$key", $logfile);
    	            continue;
    	        }
    	    }
    	    
    	    if(($num >= $data['t_total_progress']) ||
    	        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
                $data['t_finish_progress'] = $data['t_total_progress'];
            }else{
                $data['t_finish_progress']+= $num;
            }
            
            $this->redis->set($key, json_encode($data));
            
            logs::addLog("INFO::taskinfo::getDayAwards:: 主播每日任务进度打点  finish_once uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            //任务埋点进度缓存修正
            $task_id = $data['id'];
            $m_key = "maidian:taskid:$task_id" ;
            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);
            
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = $this->getdate();//date("Y-m-d");
                $now = time();
                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->db->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->redis->set($key, json_encode($data));
                    
                    logs::addLog("INFO::taskinfo::getDayAwards:: 用户每日随机任务完成打点  finish_task uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
                    //任务埋点状态登记缓存修正
                    $t_total_progress = $data['t_total_progress'];
                    $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
                    //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);
                    
                    $result = $this->getCommonAwards($data['id']);
                    //数据回滚
                    /* if(!$result){
                        $sql = "update card.task_info t set t_status=1 where t.id = ".$data['id'];
                        $rows = $this->db->query($sql);
                        
                        $data['status'] = 1;
                        $this->redis->set($key, json_encode($data));
                        logs::addLog("getDayAwards::任务完成，但执行getCommonAwards失败，数据回滚...***uid:$uid, key:$key", $logfile);
                    } */
                    
                    //主播任务事件埋点
                    {
                        $event_task = new CEventHandleTask;
						$event_task->redis = $this->redis;
						$event_task->db = $this->db;
                        $event_type = 3;//类型3：主播每日任务完成
                        $awardItems = array();
                        $id = $data['id'];
                        $event_task->taskModule_singer_event($event_type,$uid, $id,&$awardItems);
                    }
                    
                }else{
                    logs::addLog("WARN::taskinfo::getDayAwards::任务完成，但更新数据库失败***uid:$uid, key:$key, exe sql error!**sql:$sql", $logfile);
                }
            }
        }
    
        //logs::addLog("DEBUG::taskinfo::getDayAwards end!*****uid:$uid*****data:".json_encode($result), $logfile);
        return $result;
	}
    
    //初始化用户主线任务缓存
/*     public function initMainCatch($id, $uid, $targetType, $totalNum, $attachParam, $followTaskid, $status){
        $logfile=basename(__FILE__, '.php');
        logs::addLog("begin initMainCatch!*********", $logfile);
        $key = $this->get_main_target_key_by_uid($uid, $targetType);
    	
    	$data = array();
        $data['id'] = $id;
        $data['uid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        logs::addLog("begin initMainCatch! 1**********attachParam:$attachParam", $logfile);
        if(!empty($attachParam)){
            $data['t_attach_param'] = (int)$attachParam;
        }
        logs::addLog("begin initMainCatch! 2**********followTaskid:$followTaskid", $logfile);
        if(!empty($followTaskid)){
            $data['follow_task_id'] = (int)$followTaskid;
        }
        logs::addLog("begin initMainCatch! 3**********status:$status", $logfile);
        $data['status'] = (int)$status;
    	$this->redisMaster->set($key, json_encode($data));
    	logs::addLog("end initMainCatch! **********data:".json_encode($data), $logfile);
    } */
    
    //更新下一个主线任务
    public function updateNextMainTask($uid, $nextTaskId){
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG::taskinfo::updateNextMainTask!*********uid:$uid, nextTaskId:$nextTaskId", $logfile);
        
        if(empty($nextTaskId)){
            logs::addLog("WARN::taskinfo::updateNextMainTask!****下一任务为空!*****uid:$uid, nextTaskId:$nextTaskId", $logfile);
            return false;
        }
        
        $date = date("Y-m-d");
        //把随后任务插入到任务信息表中
        $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_total_progress, create_time)" .
            " SELECT $uid, id, task_type, target_params1, '$date' FROM card.task_conf WHERE id =$nextTaskId";
        $rows = $this->db->query($sql);
        if(!$rows){
            logs::addLog("WARN::taskinfo::updateNextMainTask!*****exe sql error!**sql:$sql", $logfile);
            return false;
        }
        
        $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.target_type, tc.task_type, tc.target_params2, tc.follow_task_id from card.task_info t ".
        "left join card.task_conf tc on t.t_id = tc.id  where t.uid = $uid and t.t_type = 0 and t.create_time = '$date' and t.t_id=$nextTaskId for update";
        //TODO:根据类型初始化任务catch
        $rows = $this->db->query($sql);
        if ($row = $this->db->fetch_assoc($rows)) {
            $id = $row['id'];
            $totalNum = $row['t_total_progress'];
            $targetType = $row['target_type'];
            $attachParam = $row['target_params2'];
            $followTaskid = $row['follow_task_id'];
            $status = $row['t_status'];
            $t_id = (int)$row['t_id'];
            $task_type = (int)$row['task_type'];
            //logs::addLog("DEBUG::taskinfo::updateNextMainTask!****uid:$uid*****data:".json_encode($row), $logfile);            
            $this->initMainCatch($id, $uid, $targetType, $totalNum, $attachParam, $followTaskid, $status,$t_id,$task_type);
        }
        
        logs::addLog("INFO::taskinfo::updateNextMainTask!*****uid:$uid nextTaskId:$nextTaskId", $logfile);        
        return true;
    }
	
	public function getMainAwards($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::getMainAwards!****uid:$uid target_type:$target_type num:$num", $logfile);
	    
	    //$key = $this->get_main_target_key_by_uid($uid, $target_type);
	    $h_key = $this->get_main_target_keys_by_uid($uid, $target_type);
	    
	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::getMainAwards::empty(fields) 没有该主线任务******uid:$uid, Has_key:$h_key", $logfile);
	        return false;
	    }
	    
	    $result = array();
	    foreach ($fields as $field){
            $key = $this->redis->hget($h_key, $field);
            $value = $this->redis->get($key);
             
            //如果该用户没有该任务，则不执行
            if(empty($value)){
                logs::addLog("WARN::taskinfo::getMainAwards::任务有Str_key没有value  uid:$uid Has_key:$h_key Str_key:$key", $logfile);
                continue;
            }
            
            $data = json_decode($value, TRUE);
            
            if(!empty($extra_param) 
                && !empty($data['t_attach_param'])
                && $data['t_attach_param'] != $extra_param){
                   // logs::addLog("WARN::taskinfo::getMainAwards::uid:$uid Str_key:$key extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
                continue;
            }
    	    
    	    if($data['status'] == 1){
    	        //logs::addLog("DEBUG::taskinfo::getMainAwards::该主线任务已完成******uid:$uid, Str_key:$key", $logfile);
    	        continue;
    	    }
    	    
    	    //如果目标类型为上热门，需要判断上热门的名次是否是任务规定的，如果不是则不处理
    	    if($target_type == TaskInfo::TARGET_TYPE_HOT){
    	        if($extra_param > $data['t_attach_param']){
    	            logs::addLog("INFO::taskinfo::getMainAwards::上热门名次不在任务规定名次内.******uid:$uid, Str_key:$key", $logfile);
    	            continue;
    	        }
    	    }
    	    
    	    if(($num >= $data['t_total_progress']) ||
    	        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
                $data['t_finish_progress'] = $data['t_total_progress'];
            }else{
                $data['t_finish_progress']+= $num;
            }
            
            $this->redis->set($key, json_encode($data));
            
            logs::addLog("INFO::taskinfo::getMainAwards:: 主线任务进度打点  finish_once uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            //任务埋点进度缓存修正
            $task_id = $data['id'];
            $m_key = "maidian:taskid:$task_id" ;
            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);
            
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = date("Y-m-d");
                $now = time();
                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->db->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->redis->set($key, json_encode($data));
                    
                    logs::addLog("INFO::taskinfo::getMainAwards:: 主线任务完成打点  finish_task uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
                    //任务埋点状态登记缓存修正
                    $t_total_progress = $data['t_total_progress'];
                    $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
                    //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);
                    
                    $result = $this->getCommonAwards($data['id']);
                    //数据回滚
                    if(!$result){
                        $sql = "update card.task_info t set t_status=1 where t.id = ".$data['id'];
                        $rows = $this->db->query($sql);
                        
                        $data['status'] = 1;
                        $this->redis->set($key, json_encode($data));
                        logs::addLog("WARN::taskinfo::getMainAwards::任务完成，但执行getCommonAwards失败，数据回滚...***uid:$uid, key:$key", $logfile);
                    }else{
                        //处理下一任务
                        $this->updateNextMainTask($uid, $data['follow_task_id']);
                    }
                }else{
                    logs::addLog("WARN::taskinfo::getDayAwards::任务完成，但更新数据库失败***uid:$uid, key:$key, exe sql error!**sql:$sql", $logfile);
                    
                }
                
            }
        }
        
        //logs::addLog("DEBUG::taskinfo::getDayAwards end!****uid:$uid*****data:".json_encode($result), $logfile);
        return $result;
	}
	
	//获取已完成的主播开启任务列表
	public function getSingerAwards($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    
	    //<2018-6-26新增> 如果不是主播，没有获取已完成的主播开启任务列表功能
	    $is_singer = $this->isSinger($uid);
	    if (!$is_singer) {
	        return false;
	    }
	     
	    
	    //logs::addLog("DEBUG::taskinfo::getSingerAwards, uid:$uid,extra_param:$extra_param,target_type:$target_type, num:$num", $logfile);
	    
// 	    $key = $this->get_singer_target_key_by_uid($uid, $target_type, $extra_param);
	    $h_key = $this->get_singer_target_keys_by_uid($uid, $target_type);
	    
	    //logs::addLog("DEBUG::taskinfo::getSingerAwards::该主播每日开启任务******uid:$uid, targetType:$target_type h_key:$h_key", $logfile);
	    
	    $fields = $this->redis->hkeys($h_key);
	    if(empty($fields)){
	        //logs::addLog("WARN::taskinfo::getSingerAwards::empty(fields) 没有主播每日开启任务******uid:$uid, Has_key:$h_key", $logfile);
	        return false;
	    }
	     
	    $result = array();
	    foreach ($fields as $field){
	        $key = $this->redis->hget($h_key, $field);
	        $value = $this->redis->get($key);
	         
	        //如果该用户没有该任务，则不执行
	        if(empty($value)){
	            logs::addLog("WARN::taskinfo::getSingerAwards::任务有Str_key没有value  uid:$uid Has_key:$h_key Str_key:$key", $logfile);
	            continue;
	        }
	    
	        $data = json_decode($value, TRUE);
            
	        /* if(!empty($extra_param)){
	            if(!empty($data['t_attach_param'])
                    && $data['t_attach_param'] != $extra_param){
	                    logs::addLog("extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);
	                    continue;
	            }
	        } */
	        if(!empty($data['t_attach_param'])){
	            if(!empty($extra_param)
	                && $data['t_attach_param'] != $extra_param
	                || empty($extra_param)){
	                    //logs::addLog("WARN::taskinfo::getSingerAwards::uid:$uid Str_key:$key extra_param:$extra_param != 缓存t_attach_param:".$data['t_attach_param'], $logfile);	                    
	                    continue;
	            }
	        }
	        
            /* if(!empty($extra_param) 
                && !empty($data['t_attach_param'])
                && $data['t_attach_param'] != $extra_param){
                continue;
            } */
            
            //任务还没开启
            if($data['status'] != 3){
                logs::addLog("INFO::taskinfo::getSingerAwards::该主播任务还没有开启******uid:$uid, Str_key:$key", $logfile);
                continue;
            }
    	    if($data['status'] == 1){
    	        logs::addLog("INFO::taskinfo::getSingerAwards::该主播开启任务已完成******uid:$uid, Str_key:$key", $logfile);
    	        continue;
    	    }
    	    
    	    //如果目标类型为上热门，需要判断上热门的名次是否是任务规定的，如果不是则不处理
    	    if($target_type == TaskInfo::TARGET_TYPE_HOT){
    	        if($extra_param > $data['t_attach_param']){
    	            logs::addLog("INFO::taskinfo::getSingerAwards::上热门名次不在任务规定名次内.******uid:$uid, key:$key", $logfile);
    	            continue;
    	        }
    	    }
    	    
    	    if(($num >= $data['t_total_progress']) ||
    	        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
                $data['t_finish_progress'] = $data['t_total_progress'];
            }else{
                $data['t_finish_progress']+= $num;
            }
            
            $this->redis->set($key, json_encode($data));
            
            logs::addLog("INFO::taskinfo::getSingerAwards:: 主播每日开启任务进度打点  finish_once uid:$uid num:$num Str_key:$key task_id:".$data['id'], $logfile);
            //任务埋点进度缓存修正
            $task_id = $data['id'];
            $m_key = "maidian:taskid:$task_id" ;
            $this->redis->hIncrBy($m_key, "t_finish_progress",$num);            
            
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = $this->getdate();//date("Y-m-d");
                $now = time();
                $sql = "update card.task_info t set t_status=1, update_time=$now, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->db->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->redis->set($key, json_encode($data));
                    
                    logs::addLog("INFO::taskinfo::getSingerAwards:: 主播每日开启任务完成打点  finish_task uid:$uid Str_key:$key task_id:".$data['id'], $logfile);
                    //任务埋点状态登记缓存修正
                    $t_total_progress = $data['t_total_progress'];
                    $this->redis->hset($m_key, "tasklog",3);//1产生；2开始；3完成；4领奖；5刷新；6放弃
                    //$this->redis->hset($m_key, "t_finish_progress",$t_total_progress);
                    
                    $result = $this->getCommonAwards($data['id']);
                    //数据回滚
                    if(empty($result) || !$result){
                        $sql = "update card.task_info t set t_status=1 where t.id = ".$data['id'];
                        $rows = $this->db->query($sql);
                        
                        $data['status'] = 1;
                        $this->redis->set($key, json_encode($data));
                        logs::addLog("WARN::taskinfo::getSingerAwards::任务完成，但执行getCommonAwards失败，数据回滚...***uid:$uid, key:$key", $logfile);
                    }else{
                        $sql = "select s.sid from raidcall.sess_info s left join card.task_info t on s.owner = t.uid where t.id = ".$data['id'];
                        
                        $rows = $this->db->query($sql);
                        if($row = $this->db->fetch_assoc($rows)){
                            $result["sid"] = (int)$row['sid'];
                        }
                        
                        //TODO:如果所有主播开启任务都完成，则初始化主播终极任务
                        $sql = "select count(*) as num from card.task_info t where t.uid = $uid and t.t_status in(1,4) ".
                               "and t.t_type in (1, 16) and t.create_time = '$date' and t.t_open_object = 1 group by t.t_type for update" ;
                        
                        $rows = $this->db->query($sql);
                        if($rows){
                            $t_num = 0;
                            while ($row = $this->db->fetch_assoc($rows)){
                                $t_num += (int)$row['num'];
                            }
                            if(3 == $t_num){
                                //TODO:初始化主播终极任务
                                $this->initSingerLastTask($uid);
                            }
                        }
                    }   
                }else{
                    logs::addLog("WARN::taskinfo::getSingerAwards::任务完成，但更新数据库失败***uid:$uid, key:$key, exe sql error!**sql:$sql", $logfile);   
                }
            }
	    }
        
        //logs::addLog("DEBUG::taskinfo::getSingerAwards end!*****uid:$uid****data:".json_encode($result), $logfile);
        return $result;
	}
	
	//获得奖励
	public function getAwards($uid, $target_type, $num, $extra_param){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::getAwards, uid:$uid,extra_param:$extra_param,target_type:$target_type, num:$num", $logfile);
	    
		$result = array();
		
		if(empty($uid)){
		    return $result;
		}
		
		//用户每日跑环任务
		$flag = $this->getDayLoopTaskAwards($uid, $target_type, $num, $extra_param);
		if(!empty($flag)){
		   $result[] = $flag;
		   
		   $json_data = json_encode($flag);
		   //logs::addLog("DEBUG::taskinfo::getAwards::finish user:$uid sun task: $json_data", $logfile);
		   
		   //师徒任务
		   $masterid = $this->getMasterByApprenticeid($uid);
		   if(!empty($masterid)){
		       $flag = $this->getMasterAndApprenticeAwards($uid, 27, 1, null, $masterid);
		       if(!empty($flag)){
		           $result[] = $flag;
		       }
		   }
		   /* //获得帮会活跃任务
		   $flag = $this->getGangAwards($uid, 27, 1, null);	//完成每日任务次数
		   if(!empty($flag)){
		       for($i = 0; $i < count($flag); $i++){
		           $result[] = $flag[$i];
		       }
		   } */
		   
		   //获得帮会个人活跃任务
		   $flag = $this->getGangManTaskAwards($uid, 27, 1, null);	//完成每日任务次数
		   if(!empty($flag)){
		       for($i = 0; $i < count($flag); $i++){
		           if(!empty($flag[$i])){
		               $result[] = $flag[$i];
		           }
		       }
		   }
		}
		
		//TODO：需要把下面的条件判断放到getGangManTaskAwards函数内部
		//帮会成员个人任务
		$flag = $this->getGangManTaskAwards($uid, $target_type, $num, $extra_param);
		if(!empty($flag)){
		    for($i = 0; $i < count($flag); $i++){
		        //$result[] = $flag[$i];
		        if(!empty($flag[$i])){
		            $result[] = $flag[$i];
		        }
		    }
		}
		/* if(!empty($flag)){
		    $result[] = $flag;
		    //帮会目的类型：27为完成每日任务次数 6为送礼物 29为守擂时间
		    if(6 == $target_type || 
		        41 == $target_type){
		        //获得帮会礼物任务
		        $flag = $this->getGangAwards($uid, 39, 1, null);	//完成每日帮会成员个人礼物任务次数
		    }elseif (34 == $target_type || 
		        40 == $target_type || 
		        29 == $target_type){
	            $flag = $this->getGangAwards($uid, 38, 1, null);	//完成每日帮会成员个人竞技任务次数
		    }elseif(27 == $target_type){
		        $flag = $this->getGangAwards($uid, 37, 1, null);	//完成每日帮会成员个人活跃任务次数
		    }
		    
		    if(!empty($flag)){
		        for($i = 0; $i < count($flag); $i++){
		            $result[] = $flag[$i];
		        }
		    }
		} */
		
		//主播终极任务
		$flag = $this->getSingerLastAwards($uid, $target_type, $num, $extra_param);
		if(!empty($flag)){
		    $result[] = $flag;
		}
		//主播挖宝
		$flag = $this->getSingerTreasureAwards($uid, $target_type, $num, $extra_param);
		if(!empty($flag)){
		    $result[] = $flag;
		}
		//用户挖宝
		$flag = $this->getUserTreasureAwards($uid, $target_type, $num, $extra_param);
		if(!empty($flag)){
		    $result[] = $flag;
		}
		
		//师徒任务
		$masterid = $this->getMasterByApprenticeid($uid);
		if(!empty($masterid)){
		    $flag = $this->getMasterAndApprenticeAwards($uid, $target_type, $num, $extra_param, $masterid);
		    if(!empty($flag)){
		        $result[] = $flag;
		    }  
		}
		
		//每日随机任务
		/* $flag = $this->getDayRandomTask($uid, $target_type, $num, $extra_param);
		if(!empty($flag)){
		    $result[] = $flag;
		} */
		
		//主播每日任务
		$flag = $this->getSingerDayAwards($uid, $target_type, $num, $extra_param);
		
		//主线任务
		/* $flag = $this->getMainAwards($uid, $target_type, $num, $extra_param);
		if(!empty($flag)){
		    $result[] = $flag;
		} */
		//主播开启任务
		$flag = $this->getSingerAwards($uid, $target_type, $num, $extra_param);
		if(!empty($flag)){
		    $result[] = $flag;
		}
		
		//帮会任务
		$flag = $this->getGangAwards($uid, $target_type, $num, $extra_param);
		if(!empty($flag)){
		      for($i = 0; $i < count($flag); $i++){
	               //$result[] = $flag[$i];
		          if(!empty($flag[$i])){
		              $result[] = $flag[$i];
		          }
		      }
		}
		
		//星级任务：帮会宝贝周星任务（帮会星级任务是没有奖励的，所以不处理返回值）
		$isSinger = $this->isSinger($uid);
		if(!empty($isSinger)){
		    $gangid = $this->getSingerGangId($uid);
		    if(!empty($gangid)){
		        $this->dealGangStartAwards($gangid, $target_type, $num, $extra_param);
		    }
		}
		
		//帮会星级任务：帮会人数任务（已经去掉）
		/* if(22 == $target_type){//此时uid为帮会id
		    $this->dealGangStartAwards($uid, $target_type, $num, $extra_param);
		} */
		/* //去掉粉丝团任务
		logs::addLog("开始处理粉丝团任务：uid:$uid,extra_param:$extra_param,target_type:$target_type", $logfile);
		//TODO:处理粉丝团任务
		$this->dealFollowerAwards($uid, $target_type, $num, $extra_param);
		 */	
		
		if (!empty($result)) {
		    logs::addLog("INFO::taskinfo::getAwards::用户($uid)获得的奖励信息".json_encode($result), $logfile);
		}
		return $result;
	}
	
	//获得帮会宝贝帮会id
	public function getSingerGangId($uid){
		$logfile=basename(__FILE__, '.php'); 
	
	    $query = "SELECT IFNULL(u.union_id, '') AS union_id, IFNULL(u.uid, '') AS uid
		      FROM rcec_record.union_guard_anchor_record u 
		      WHERE u.createTime > FROM_UNIXTIME(UNIX_TIMESTAMP(SYSDATE()) - 7 * 24 * 60 * 60, '%Y-%m-%d %H:%i:%s')
		      AND u.uid= $uid and u.flag=1";
		$rs = $this->db->query($query);
		
		if ($row = $this->db->fetch_array($rs)) {
	        return $row['union_id'];
	    }
        
		return false;
	}
	
	//是否为帮会会员
	public function getGangId($uid){
		$logfile=basename(__FILE__, '.php'); 
	
		$key = 'uid:' . $uid;
		$value = $this->redis->get($key);
        if ($value !== false) {
            $user = json_decode($value, true);
            if(!empty($user['union_id'])){
            	//logs::addLog("DEBUG::taskinfo::getGangId uid:$uid,is Gang.", $logfile);
            	return $user['union_id'];
            }
        }else{
			$query = "select * from raidcall.uinfo t where t.union_id != '' and t.id = $uid";
			$rs = $this->db->query($query);
			
			if ($row = $this->db->fetch_array($rs)) {
		        return $row['union_id'];
		    }
        }
        
		return false;
	}
	
	//是否是会长
	public function isGangLeader($uid){
		$logfile=basename(__FILE__, '.php'); 
	
		$key = 'uid:' . $uid;
		$value = $this->redis->get($key);
        if ($value !== false) {
            $user = json_decode($value, true);
            if($user['identity'] == 3){
            	logs::addLog("DEBUG::taskinfo::isGangLeader uid:$uid,is gangLeader.", $logfile);
            	return true;
            }
        }
        
		return false;
	}
	
	//获得师傅id
	public function getMasterByApprenticeid($uid){
	    $logfile=basename(__FILE__, '.php');
	    $key = 'disciple:count:down:' . $uid;
	    $value = $this->redis->get($key);
	    
	    //logs::addLog("DEBUG::taskinfo::getMasterByApprenticeid::uid:$uid, key:$key, value:$value", $logfile);
	    return $value;
	}
	
	public function isSinger($uid){
		$logfile=basename(__FILE__, '.php'); 
	
		$key = 'uid:' . $uid;
		$value = $this->redis->get($key);
        if ($value !== false) {
            $user = json_decode($value, true);
            if($user['identity'] == 2){
            	//logs::addLog("DEBUG::taskinfo::isSinger uid:$uid,is singer.", $logfile);
            	return true;
            }
        }else{
        	//检查是否创建有房间/是否是主播
			$query = "select * from raidcall.anchor_info where flag = 1 and uid = $uid";
			$rs = $this->db->query($query);
			
			if ($row = $this->db->fetch_array($rs)) {
		        return true;
		    }
        }
        
		return false;
	}
	
	//获得未领取的宝箱
	public function getNoRecvAwards($uid){
	    $logfile=basename(__FILE__, '.php');
	    
	    $data = array();
	    $i = 0;
	    /* $query = "select t.id, t.t_box_id, ui.id as boxid, tc.task_type, tc.task_name, tc.task_sketch
	        from card.task_info t left join card.task_conf tc on t.t_id = tc.id
	        left join card.user_treasure_box_info ui on t.t_box_id = ui.id
	       where t.t_status = 4 and t.uid = $uid";
	    $rs = $this->db->query($query);
	    
	    while ($row = $this->db->fetch_array($rs)) {
	        if(empty($row['boxid'])){
	            continue;
	        }
	        
	        $data[$i]["tid"] = (int)$row['id'];
	        $data[$i]["awardsId"] = (int)$row['t_box_id'];
	        $data[$i]["type"] = (int)$row['task_type'];
	        $data[$i]["name"] = base64_encode($row['task_name']);
	        $data[$i]["desc"] = base64_encode($row['task_sketch']);
	        $i++;
	    } */
	    
	    $start = date('Y-m-d 00:00:00');
	    $end = date('Y-m-d H:i:s');
	    
	    //帮会任务宝箱都在这个表里
	    $query = "select t.id, t.drop_id, t.type, t.task_id, g.goods_icon, r.folder_path, tc.task_type, tc.task_name, tc.task_sketch from card.user_all_box t 
                left join card.treasure_box_info b on b.id = t.drop_id
                left join card.goods_info g on g.id = b.good_id 
                left join card.resoure_folder_info r on r.id = g.path_id
				left join card.task_info ti on ti.id = t.task_id
				left join card.task_conf tc on ti.t_id = tc.id 
	            where t.uid = $uid and (t.type in(2, 3, 5) || (t.type=1 and t.create_time>= unix_timestamp( '$start' ) and t.create_time <= unix_timestamp( '$end' ))) and t.status = 1";
	    $rs = $this->db->query($query);
	    
	    while ($row = $this->db->fetch_array($rs)) {
	        $boxid = (int)$row['id'];
	        $taskid = (int)$row['task_id'];
	        $type = (int)$row['type'];
	        
	        //处理财富升级奖励和消费奖励未领宝箱
	        if(empty($taskid)){
	            $data[$i]["tid"] = 0;
	        }else{//处理帮会未领宝箱
	            $data[$i]["tid"] = $taskid;
	        }
	        
	        $data[$i]["awardsId"] = $boxid;
	        if($type == 1){
	            $data[$i]["type"] = (int)$row['task_type'];
	            $data[$i]["name"] = base64_encode($row['task_name']);
	            $data[$i]["desc"] = base64_encode($row['task_sketch']);
	        }elseif ($type == 2){
	            $data[$i]["type"] = 101;
	            $data[$i]["name"] = base64_encode("消费奖励升级");
	            $data[$i]["desc"] = base64_encode("消费奖励升级");
	        }elseif ($type == 3){
	            $data[$i]["type"] = 102;
	            $data[$i]["name"] = base64_encode("财富升级");
	            $data[$i]["desc"] = base64_encode("财富升级");
	        }
	        $imgpath = $row['folder_path']."/".$row['goods_icon'];
	        $data[$i]["goods_icon_close"] = base64_encode($imgpath);
	        $data[$i]["goods_icon_open"] = base64_encode($imgpath.'p');
	        $i++;
	    }
	    //备注：宝箱信息太大了，尽量不打，只用于调试
	    //logs::addLog("DEBUG::taskinfo::getNoRecvAwards 用户($uid)登录系统，返回的宝箱信息：".json_encode($data), $logfile);
	    
	    return $data;
	}
    
    //初始化粉丝团任务
    public function initFollowerTasks($singerid, $uid){
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG::taskinfo::initFollowerTasks::***********uid:$uid:singerid:$singerid", $logfile);
        $date =  $this->getdate();//date("Y-m-d");
        $key = "follower:uid:$uid:singerid:$singerid:$date";
        $value = $this->redis->get($key);
        
        //logs::addLog("DEBUG::taskinfo::initFollowerTasks:: key:$key value:$value", $logfile);
        
        if(empty($value)){
            $date = $this->getdate();//date("Y-m-d");
            $now = time();
            
            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time, singerid)" .
                " SELECT $uid, id, task_type, open_object, target_params1, '$date', $now, $singerid FROM card.task_conf WHERE open_object = 0 and task_type = 10";
            
            $flag = $this->db->query($sql);
            
            //logs::addLog("DEBUG::taskinfo::initFollowerTasks:: singerid:$singerid, uid:$uid flag:$flag, sql:$sql", $logfile);
            
            if($flag){
                $this->redis->set($key, $date);
                logs::addLog("INFO::taskinfo::initFollowerTasks::  singerid:$singerid, uid:$uid key=>:$key V=>:$date", $logfile);
            
                $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2, tc.intimacy_exp from card.task_info t ". 
                      "left join card.task_conf tc on t.t_id = tc.id where t.uid = $uid and t.singerid = $singerid and t.t_type = 10 and t.t_open_object = 0 and t.create_time = '$date' for update";
                //TODO:根据类型初始化任务catch
                $rows = $this->db->query($sql);
                while($row = $this->db->fetch_array($rows)) {
                    $id = (int)$row['id'];
                    $totalNum = (int)$row['t_total_progress'];
                    $openType = (int)$row['open_type'];
                    //$tool_id = $row['tool_id'];
                    //$tool_num = $row['tool_num'];
                    $targetType = (int)$row['target_type'];
                    //$attachParam = (int)$row['target_params2'];
                    $status = (int)$row['t_status'];
                    $intimacy = (int)$row['intimacy_exp'];
                    $t_id = (int)$row['t_id'];
                    $task_type = (int)$row['task_type'];
                    $this->initFollowerCatch($id, $uid, $singerid, $targetType, $totalNum, $openType, $status, $intimacy,$t_id,$task_type);
                }
            }
        }
    }
    
    //初始化粉丝团任务缓存
    public function initFollowerCatch($id, $uid, $singerid, $targetType, $totalNum, $openType, $status, $intimacy,$t_id,$task_type){
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG::taskinfo::initFollowerCatch***$uid:$uid task_id:$id***", $logfile);
		$date = $this->getdate();//date("Y-m-d");
		$key = "follower:uid:$uid:$date:tid:$id";
    	
    	$data = array();
        $data['id'] = $id;
        $data['uid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['open_type'] = (int)$openType;
        $data['status'] = (int)$status;
        $data['intimacy_exp'] = (int)$intimacy;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
    	$this->redis->set($key, json_encode($data));  	
    	
    	
    	//保存上面的key
    	$h_key = "follower:uid:$uid:singerid:$singerid:$date:$targetType";
    	$this->redis->hset($h_key, $key, $key);    	
    	
    	//埋点任务详情缓存(任务缓存变更修改测试)
    	$m_key = "maidian:taskid:$id" ;
    	$this->redis->hset($m_key, "taskinfo",json_encode($data));
    	$this->redis->hset($m_key, "t_total_progress",$totalNum);
    	$this->redis->hset($m_key, "t_finish_progress",0);
    	$this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
    	$this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME);
    	
    	logs::addLog("INFO::taskinfo::initFollowerCatch uid:$uid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);    	
    }
    
    public function getdate(){
        $flag = $this->is2day();
        if(empty($flag) || !$flag){
            $date = date("Y-m-d",strtotime("-1 day"));
        }else{
            $date = date("Y-m-d");
        }
        
        return $date;
    }
    
    //任务的刷新时间是：过了凌晨5点才算第二天
    public function is2day(){
        $now = strtotime("now");
        $daybegin=strtotime(date("Ymd"));
        $hour5=$daybegin+5*60*60;
         
        //没到5点不算第二天
        if($now < $hour5){
            return false;
        }
        
        return true;
    }
    
    public function initSingerSunTask($singerid){
        $logfile=basename(__FILE__, '.php');
        $sql = "select t.parm1 from card.parameters_info t where t.id = 165";
        $rs = $this->db->query($sql);
        $total_num = 9999;
        if($r = $this->db->fetch_assoc($rs)){
            $total_num = (int)$r['parm1'];
        }
         
        $date = date("Y-m-d");
        $singerdata = array();
        $key = "singer_day_sun_task:$date:$singerid";
        $data = array();
        $data['singerid'] = $singerid;
        $data['finish_num'] = 0;
        $data['total_num'] = $total_num;
        $value = json_encode($data);
        $this->redis->set($key, $value);
        $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
        logs::addLog("INFO::taskinfo::initSingerSunTask******singerid:$singerid, key=>:$key V=>:$value", $logfile);
    }
    
    //初始化师徒任务
    public function initMasterAndApprenticeTasks($masterUid, $apprenticeUid)
    {
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG::taskinfo::initMasterAndApprenticeTasks::apprenticeUid:$apprenticeUid:masterUid:$masterUid****", $logfile);
        
        $date = $this->getdate();//date("Y-m-d");
        
        $key = $this->get_master_apprentice_key_by_uid($apprenticeUid);
        $value = $this->redis->get($key);
         
        $sql = "select count(*) as num from card.task_info t
        where t.uid = $apprenticeUid and t.t_type = 17" ;
        $rss = $this->db->query($sql);
        $nCount = 0;
        if($rss && $row = $this->db->fetch_assoc($rss)){
            $nCount = (int)$row['num'];
        }
         
        if(empty($value) && empty($nCount)){
             
            $now = time();
             
            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
                " SELECT $apprenticeUid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE open_object = 0 and task_type = 17";
             
            $flag = $this->db->query($sql);
             
            //logs::addLog("DEBUG::taskinfo::initMasterAndApprenticeTasks:: flag:$flag, sql:$sql", $logfile);
             
            if($flag){
                $this->redis->set($key, $now);
                //$this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
                logs::addLog("IFNO::taskinfo::initMasterAndApprenticeTasks::apprenticeUid:$apprenticeUid:masterUid:$masterUid Str_key=>:$key V=>:$now", $logfile);
                 
                $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2 from card.task_info t ".
                      "left join card.task_conf tc on t.t_id = tc.id where t.uid = $apprenticeUid and t.t_type = 17 and t.t_open_object = 0 for update";
                //TODO:根据类型初始化任务catch
                $rows = $this->db->query($sql);
                
                $data = array();
                while($row = $this->db->fetch_assoc($rows)){
                    $id = (int)$row['id'];
                    $totalNum = (int)$row['t_total_progress'];
                    $openType = (int)$row['open_type'];
                    $tool_id = $row['tool_id'];
                    $tool_num = $row['tool_num'];
                    $targetType = (int)$row['target_type'];
                    $attachParam = (int)$row['target_params2'];
                    $status = (int)$row['t_status'];
                    $t_id = (int)$row['t_id'];
                    $task_type = (int)$row['task_type'];
                    $this->initMasterAndapprenticeCatch($id, $apprenticeUid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type);
                    
                    $data[] = "($masterUid, $id),($apprenticeUid, $id)";
                }
                //logs::addLog("DEBUG::taskinfo::initMasterAndApprenticeTasks:: flag:$flag, sql:$sql", $logfile);
                
                if(!empty($data)){
                    //TODO:INSERT INTO card.task_master_disciple(uid, task_id)values(1, 2),(3, 4)
                    $in_sql = implode(',', $data);
                    $sql = "INSERT INTO card.task_master_disciple(uid, task_id)values".$in_sql;
                    
                    $rows = $this->db->query($sql);
                    
                    $key = "masterid:$masterUid:discipleid:$apprenticeUid";
                    $v = array();
                    $v['total_progress'] = count($data);
                    $v['finish_progress'] = 0;
                    $v_str = json_encode($v);
                    $this->redis->set($key, $v_str);
                    logs::addLog("INFO::taskinfo::initMasterAndApprenticeTasks apprenticeUid:$apprenticeUid:masterUid:$masterUid Str_key:$key", $logfile);
                }
            }
             
            //logs::addLog("DEBUG::taskinfo::initMasterAndApprenticeTasks**apprenticeUid:$apprenticeUid:masterUid:$masterUid**end", $logfile);
        }
         
        
    }
	
	public function initTasks($uid){
	    
	    //初始化主播每日任务
		$isSinger = $this->isSinger($uid);		
		$this->initSingerDayTasks($uid, $isSinger);
// 		$this->initMainTasks($uid, $isSinger);
        
		if($isSinger){
		    //初始化主播主动开启任务
			$this->initSingerTasks($uid);
			//初始化主播直播间用户跑环任务
			$this->initSingerSunTask($uid);
			//初始化主播挖宝任务
			$this->initSingerTreasureTask($uid);
		}
		
		if(!$isSinger){
		    //初始化用户每日跑环任务
		    $this->initDayLoopTask($uid);
		    //初始化用户挖宝任务
		    $this->initUserTreasureTask($uid);
		}
				
		//初始化用户每日随机任务
		/* if(!$isSinger){
		    $this->initDayRandomTask($uid);
		} */
				
		
		//是否为帮会会员
		$gangid = $this->getGangId($uid);
		if(!empty($gangid)){
		    //初始化帮会成员个人任务
		    $this->initGangManDayTasks($uid, $gangid);
		    //初始化帮会任务
		    $this->initGangTasks($uid,$gangid);
		    //初始化帮会星级任务
		    $this->initGangStartTasks($uid, $gangid);
		}
		
		//初始化帮会星级任务
		/* $isGangLeader = $this->isGangLeader($uid);
		if($isGangLeader){
		    $gangid = $this->getGangId($uid);
		    $this->initGangStartTasks($uid, $gangid);
		} */
	}
	
	//初始化主播要开启的任务列表
	public function initSingerTasks($uid){
		$logfile=basename(__FILE__, '.php');
		//logs::addLog("DEBUG::taskinfo::initSingerTasks******uid:$uid", $logfile);
		$key = $this->get_singer_key_by_uid($uid);
        $value = $this->redis->get($key);

        $date = $this->getdate();//date("Y-m-d");
        
        $sql = "select count(*) as num from card.task_info t
        left join card.task_conf tc on t.t_id = tc.id
        where t.uid = $uid and t.t_type = 1 and t.create_time = '$date' 
        and tc.open_object = 1 and tc.face_object = 0";
        $rss = $this->db->query($sql);
        $nCount = 0;
        if($rss && $row = $this->db->fetch_assoc($rss)){
            $nCount = (int)$row['num'];
        }
        if(empty($value) && empty($nCount)){
        	//logs::addLog("DEBUG::taskinfo::initSingerTasks line 2 uid:$uid", $logfile);
        	
        	/* $sql = "SELECT min(id) as id, count(id) as total FROM card.task_conf WHERE open_object = 1 and task_type = 1 and face_object = 0";
        	$rows = $this->db->query($sql);
         
         	$id = 0;
         	$total = 0;
	        if ($row = $this->db->fetch_assoc($rows)) {
	        	logs::addLog("initSingerTasks******3", $logfile);
	            $total = (int)$row['total'];
	            $id = (int)$row['id'];
	        }
	        logs::addLog("initSingerTasks******id:$id, total:$total, rows:$rows, sql:$sql", $logfile);
	        
	        $numbers = range ($id, $id+$total-1);
	        //shuffle 将数组顺序随即打乱
	        shuffle ($numbers);
	        //array_slice 取该数组中的某一段
	        $nums = array_slice($numbers,0,3); */
        	
        	$sql = "SELECT t.id, t.probability FROM card.task_conf t WHERE open_object = 1 and task_type = 1 and face_object = 0";
        	$rows = $this->db->query($sql);
        	$prize_arr = array();
        	while ($row = $this->db->fetch_assoc($rows)) {
        	    $tid = $row['id'];
        	    $probability = (int)$row['probability'];
        	     
        	    $prize_arr[$tid] = $probability;
        	}
        	$t_ids = array();
        	$t_ids = $this->pro_rand_unique_multi($prize_arr, 3);
	        
	        $in_sql = implode(',', $t_ids);
	        
	        //logs::addLog("taskinfo::initSingerTasks:: uid:$uid in_sql:$in_sql", $logfile);
        	
            $now = time();
            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, t_status, create_time, update_time)" .
                " SELECT $uid, id, task_type, open_object, target_params1, 2, '$date', $now FROM card.task_conf where id in($in_sql)";
            
            $flag = $this->db->query($sql);
            
            //logs::addLog("taskinfo::initSingerTasks:: uid:$uid flag:$flag, sql:$sql", $logfile);
            
            if($flag)
            {
                $this->redis->set($key, $now);
                $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
                logs::addLog("INFO::taskinfo::initSingerTasks:: uid:$uid Str_key=>:$key V=>:$now", $logfile);
                
	            $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2, t.t_status from card.task_info t ".
	                  "left join card.task_conf tc on t.t_id = tc.id where t.uid = $uid and t.t_id in($in_sql) and t.create_time = '$date' for update";
	            //TODO:根据类型初始化任务catch
	            // sleep(1);
	            $rows = $this->db->query($sql);
	            
	            //logs::addLog("taskinfo::initSingerTasks:: initSingerCatch uid:$uid rows number:".$this->db->num_rows()." mysql_error:".mysql_error()." sql:$sql", $logfile);
	            while($row = $this->db->fetch_assoc($rows))
	            {
					$id = (int)$row['id'];
					$totalNum = (int)$row['t_total_progress'];
					$openType = (int)$row['open_type'];
					$tool_id = $row['tool_id'];
					$tool_num = $row['tool_num'];
					$targetType = (int)$row['target_type'];
					$status = (int)$row['t_status'];
					$attachParam = (int)$row['target_params2'];
					$t_id = (int)$row['t_id'];
					$task_type = (int)$row['task_type'];

					//logs::addLog("taskinfo::initSingerTasks initSingerCatch uid:$uid id:$id row:".json_encode($row), $logfile);

					$this->initSingerCatch($id, $uid, $targetType, $totalNum, $status, $attachParam, $openType, $tool_id, $tool_num,$t_id,$task_type);
				}
            }
            else
            {
                logs::addLog("WARN::taskinfo::initSingerTasks***uid:$uid**:: flag:$flag, sql:$sql", $logfile);
            }
        }
        
        //logs::addLog("DEBUG::taskinfo::initSingerTasks***uid:$uid***end", $logfile);
	}
	
	//用户初次初始化主线任务列表
	public function initMainTasks($uid, $isSinger){
		$logfile=basename(__FILE__, '.php');
		//logs::addLog("DEBUG::taskinfo::initMainTasks***uid:$uid isSinger:$isSinger***", $logfile);
		$key = $this->get_main_key_by_uid($uid);
        $value = $this->redis->get($key);
        
        if(empty($value) || !$value){
            $faceObj = 0;
            $mainTaskId = 20001;
            if(!$isSinger){
                $faceObj = 1;
                $mainTaskId = 10001;
            }
            
            $date = date("Y-m-d");
            $now = time();
            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
                " SELECT $uid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE open_object = 0 and task_type = 0 and face_object = $faceObj and id in($mainTaskId)";
            
            $flag = $this->db->query($sql);
            
            //logs::addLog("DEBUG::taskinfo::initMainTasks main task:: flag:$flag, sql:$sql", $logfile);
            
            if($flag){
                $this->redis->set($key, $date);
                logs::addLog("DEBUG::taskinfo::initMainTasks***uid:$uid isSinger:$isSinger Str_key=>:$key  V=>:$date ***", $logfile);
                
	            $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.target_type, tc.task_type, tc.target_params2, tc.follow_task_id from card.task_info t ".
	            	  "left join card.task_conf tc on t.t_id = tc.id and tc.task_type = 0 where t.uid = $uid and t.t_type = 0 and t.create_time = '$date' for update";
	            //TODO:根据类型初始化任务catch
	            $rows = $this->db->query($sql);
	            while($row = $this->db->fetch_assoc($rows)){
					$id = (int)$row['id'];
					$totalNum = (int)$row['t_total_progress'];
					$targetType = (int)$row['target_type'];
					$attachParam = (int)$row['target_params2'];
					$followTaskid = (int)$row['follow_task_id'];
					$status = (int)$row['t_status'];
					$t_id = (int)$row['t_id'];
					$task_type = (int)$row['task_type'];
					$this->initMainCatch($id, $uid, $targetType, $totalNum, $attachParam, $followTaskid, $status,$t_id,$task_type);
				}
            }
            else 
            {
                logs::addLog("WARN::taskinfo::initMainTasks***uid:$uid**:: flag:$flag, sql:$sql", $logfile);
            }            
        	
        }        
        //logs::addLog("DEBUG::taskinfo::initMainTasks***uid:$uid END******", $logfile);
	}
	
	//初始化帮会礼物任务
	private function initGangGiftTasks($gangid){
	    $logfile=basename(__FILE__, '.php');
	    
	    $date = $this->getdate();//date("Y-m-d");
	    $now = time();
	    
	    $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
	        " SELECT $gangid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE open_object = 0 and task_type = 15 and face_object = 4";
	     
	    $flag = $this->db->query($sql);
	    if(!$flag)
	    {
	        logs::addLog("WARN::taskinfo::initGangGiftTasks::gangid:$gangid flag:$flag, sql:$sql", $logfile);
	    }
	    
	    
	    /* $sql = "SELECT min(id) as id, count(id) as total FROM card.task_conf WHERE open_object = 0 and task_type = 6 and face_object = 4";
	    $rows = $this->db->query($sql);
	     
	    $id = 0;
	    $total = 0;
	    if ($row = $this->db->fetch_assoc($rows)) {
	        $total = (int)$row['total'];
	        $id = (int)$row['id'];
	    }
	    logs::addLog("initGangGiftTasks******id:$id, total:$total, rows:$rows, sql:$sql", $logfile);
	     
	    $numbers = range ($id, $id+$total-1);
	    //shuffle 将数组顺序随即打乱
	    shuffle ($numbers);
	    //array_slice 取该数组中的某一段
	    $nums = array_slice($numbers,0,3); */
	    
	    $sql = "SELECT t.id, t.probability FROM card.task_conf t WHERE open_object = 0 and task_type = 6 and face_object = 4";
	    $rows = $this->db->query($sql);
	    $prize_arr = array();
	    while ($row = $this->db->fetch_assoc($rows)) {
	        $tid = $row['id'];
	        $probability = (int)$row['probability'];
	    
	        $prize_arr[$tid] = $probability;
	    }
	    $t_ids = array();
	    /* for($i = 0; $i < 3; $i++){
	        $t_ids[$i] = $this->get_rand($prize_arr);
	    } */
	    $t_ids = $this->pro_rand_unique_multi($prize_arr, 3);
	     
	    $in_sql = implode(',', $t_ids);
	     
	    //logs::addLog("DEBUG::taskinfo::initGangGiftTasks::gangid:$gangid in_sql:$in_sql", $logfile);
	    
	    $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
	        " SELECT $gangid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE id in($in_sql)";
	    
	    $flag = $this->db->query($sql);
	    if(!$flag)
	    {
	        logs::addLog("WARN::taskinfo::initGangGiftTasks::gangid:$gangid flag:$flag, sql:$sql", $logfile);
	    }	    
	    
	    return $flag;
	}
	
	//初始化帮会个人任务信息
	public function initGangManDayTasks($uid, $gangid){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::initGangManDayTasks::uid:$uid gangid:$gangid****", $logfile);
	     
	    $date = $this->getdate();//date("Y-m-d");
	     
	    $key = $this->get_gang_man_key_by_uid($uid);
	    $value = $this->redis->get($key);
	    
	    $sql = "select count(*) as num from card.task_info t
	    where t.uid = $uid and t.t_type in(12,13,14) and t.create_time = '$date'" ;
	    $rss = $this->db->query($sql);
	    $nCount = 0;
	    if($rss && $row = $this->db->fetch_assoc($rss)){
	        $nCount = (int)$row['num'];
	    }
	    
	    if(empty($value) && empty($nCount)){
	        
	        $now = time();
	        
	        $exist = $this->redis->sismember("weChatActivity:union", $gangid);
	        if(empty($exist)){
	            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
	                " SELECT $uid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE open_object = 0 and task_type in(12, 13, 14)";
	        }else{
	            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
	                " SELECT $uid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE open_object = 0 and task_type in(12, 13, 14, 18)";
	        }
	        
	        
	        $flag = $this->db->query($sql);
	        
	        //logs::addLog("DEBUG::taskinfo::initGangManDayTasks:: uid:$uid gangid:$gangid flag:$flag, sql:$sql", $logfile);
	        
	        if($flag){
	            $this->redis->set($key, $now);
	            $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
	            logs::addLog("INFO::taskinfo::initGangManDayTasks:: uid:$uid gangid:$gangid Str_key=>:$key V=>:$now ", $logfile);
	        
	            $sql ="select t.id, t.t_id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.task_type, tc.target_params2 from card.task_info t ".
	                  "left join card.task_conf tc on t.t_id = tc.id ".
	                  "where t.uid = $uid and t.t_type in(12, 13, 14, 18) and t.t_open_object = 0 and t.create_time = '$date' for update";
	            //TODO:根据类型初始化任务catch
	            $rows = $this->db->query($sql);
	            while($row = $this->db->fetch_assoc($rows)){
	                $id = (int)$row['id'];
	                $totalNum = (int)$row['t_total_progress'];
	                $openType = (int)$row['open_type'];
	                $tool_id = $row['tool_id'];
	                $tool_num = $row['tool_num'];
	                $targetType = (int)$row['target_type'];
	                $attachParam = (int)$row['target_params2'];
	                $status = (int)$row['t_status'];
	                $t_id = (int)$row['t_id'];
	                $task_type = (int)$row['task_type'];
	                $this->initGangManCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type);
	            }	            
	        }
	        else
	        {
	            logs::addLog("WARN::taskinfo::initGangManDayTasks***uid:$uid gangid:$gangid**:: flag:$flag, sql:$sql", $logfile);
	        }
	        
	        //logs::addLog("DEBUG::taskinfo::initGangManDayTasks::uid:$uid gangid:$gangid END****", $logfile);
	    }
	    
	}
	
	//初始化帮会任务
	public function initGangTasks($uid, $gangid){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::initGangTasks::uid:$uid gangid:$gangid****", $logfile);
	    
	    $date = $this->getdate();//date("Y-m-d");
	    
	    $key = $this->get_gang_key_by_gangid($gangid);
	    $value = $this->redis->get($key);
	    
	    $sql = "select count(*) as num from card.task_info t
	    where t.uid = $gangid and t.t_type in(6,7,8) and t.create_time = '$date'" ;
	    $rss = $this->db->query($sql);
	    $nCount = 0;
	    if($rss && $row = $this->db->fetch_assoc($rss)){
	        $nCount = (int)$row['num'];
	    }
	    
	    if(empty($value) && empty($nCount)){
	        
	        $now = time();
	        
	        //初始化帮会礼物任务
	        $this->initGangGiftTasks($gangid);
	        
	        $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
	            " SELECT $gangid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE open_object = 0 and task_type in(7, 8) and face_object = 4";
	        
	        $flag = $this->db->query($sql);
	        
	        //logs::addLog("DEBUG::taskinfo::initGangTasks::uid:$uid gangid:$gangid flag:$flag, sql:$sql", $logfile);
	        
	        if($flag){
	            $this->redis->set($key, $now);
	            $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
	            logs::addLog("INFO::taskinfo::initGangTasks:: uid:$uid gangid:$gangid Str_key=>:$key V=>:$now", $logfile);
	        
	            $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2 from card.task_info t ".
	                  "left join card.task_conf tc on t.t_id = tc.id ".
	                  "where t.uid = $gangid and t.t_type in(6, 7, 8, 15) and t.t_open_object = 0 and t.create_time = '$date' for update";
	            //TODO:根据类型初始化任务catch
	            $rows = $this->db->query($sql);
	            while($row = $this->db->fetch_assoc($rows)){
	                $id = (int)$row['id'];
	                $totalNum = (int)$row['t_total_progress'];
	                $openType = (int)$row['open_type'];
	                $tool_id = $row['tool_id'];
	                $tool_num = $row['tool_num'];
	                $targetType = (int)$row['target_type'];
	                $attachParam = (int)$row['target_params2'];
	                $status = (int)$row['t_status'];
	                $t_id = (int)$row['t_id'];
	                $task_type = (int)$row['task_type'];
	                $this->initGangCatch($id, $gangid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type);
	            }	            
	        }
	        else
	        {
	            logs::addLog("WARN::taskinfo::initGangTasks::uid:$uid flag:$flag, sql:$sql", $logfile);
	        }
	        
	        //logs::addLog("DEBUG::taskinfo::initGangTasks::uid:$uid END****", $logfile);
	    }
	}
	
	//初始化帮会星级任务（每月一次，只有帮主才又这个任务）
	public function initGangStartTasks($uid, $gangid){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::initGangStartTasks::uid:$uid gangid:$gangid", $logfile);
	    
	    $beginDate=date('Y-m-01', strtotime($this->getdate()));//date("Y-m-d")));
	    $key = "gangstart:$gangid:$beginDate";
	    $value = $this->redis->get($key);
	    
	    //logs::addLog("DEBUG::taskinfo::initGangStartTasks::uid:$uid gangid:$gangid key:$key value:$value", $logfile);
	    
	    $sql = "select count(*) as num from card.task_info t
	    where t.uid = $gangid and t.t_type = 5 and t.create_time = '$beginDate'" ;
	    $rss = $this->db->query($sql);
	    $nCount = 0;
	    if($rss && $row = $this->db->fetch_assoc($rss)){
	        $nCount = (int)$row['num'];
	    }
	    
	    if(empty($value) && empty($nCount)){
	        $date = $this->getdate();//date("Y-m-d");
	        $now = time();
	        
	        $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
	            " SELECT $gangid, id, task_type, open_object, target_params1, '$beginDate', $now FROM card.task_conf WHERE open_object = 0 and task_type = 5 and face_object = 2";
	        
	        $flag = $this->db->query($sql);
	        
	        //logs::addLog("DEBUG::taskinfo::initGangStartTasks::gangid:$gangid flag:$flag, sql:$sql", $logfile);
	        
	        if($flag){
	            $this->redis->set($key, $now);
	            logs::addLog("INFO::taskinfo::initGangStartTasks::uid:$uid gangid:$gangid Str_key=>:$key V=>:$now", $logfile);
	        
	            $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2 from card.task_info t ".
	                  "left join card.task_conf tc on t.t_id = tc.id ".
	                  "where t.uid = $gangid and t.t_type = 5 and t.t_open_object = 0 and t.create_time = '$beginDate' for update";
	            //TODO:根据类型初始化任务catch
	            $rows = $this->db->query($sql);
	            while($row = $this->db->fetch_assoc($rows)){
	                $id = (int)$row['id'];
	                $totalNum = (int)$row['t_total_progress'];
	                $openType = (int)$row['open_type'];
	                $tool_id = $row['tool_id'];
	                $tool_num = $row['tool_num'];
	                $targetType = (int)$row['target_type'];
	                $attachParam = (int)$row['target_params2'];
	                $status = (int)$row['t_status'];
	                $t_id = (int)$row['t_id'];
	                $task_type = (int)$row['task_type'];
	                $this->initGangStartCatch($id, $gangid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type);
	            }	            
	        }
	        else
	        {
	            logs::addLog("WARN::taskinfo::initGangStartTasks::uid:$uid initGangStartTasks-2::gangid:$gangid flag:$flag, sql:$sql", $logfile);
	        }        
	        
	    }
	    //logs::addLog("DEBUG::taskinfo::initGangStartTasks::uid:$uid gangid:$gangid END", $logfile);
	}
	
	public function getUserAttribute($uid){
	    $logfile=basename(__FILE__, '.php');
	    
	    //获得用户昵称
	    $u_key = 'user_attribute:' . $uid;
	    $user_v = $this->redis->get($u_key);
	    if(empty($user_v)){
	        $query = "select * from rcec_main.user_attribute t where t.uid = $uid";
	        $rows = $this->db->query($query);
	        if ($rows) {
	            $row = $this->db->fetch_assoc($rows);
	            //logs::addLog("DEBUG::task_info::getUserAttribute****row::".json_encode($row), $logfile);
	            return $row;
	        }
	    }else{
	        //logs::addLog("DEBUG::task_info::getUserAttribute****user::$user_v", $logfile);
	        $user = json_decode($user_v, TRUE);
	        return $user[0];
	    }
	}
	
/* 	public function get_rand($proArr) {
	    $result = '';
	    //概率数组的总概率精度
	    $proSum = array_sum($proArr);
	    //概率数组循环
	    foreach ($proArr as $key => $proCur) {
	        $randNum = mt_rand(1, $proSum);  //抽取随机数
	        if ($randNum <= $proCur) {
	            $result = $key;              //得出结果
	            break;
	        } else {
	            $proSum -= $proCur;
	        }
	    }
	    unset ($proArr);
	    return $result;
	} */
	
	//初始化用户每日跑环任务
	public function initDayLoopTask($uid){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::initDayLoopTask::uid:$uid****", $logfile);

	    $date = $this->getdate();//date("Y-m-d");
	    
	    $key = $this->get_day_loop_key_by_uid($uid);
	    $value = $this->redis->get($key);
	    
	    //logs::addLog("DEBUG::taskinfo::initDayLoopTask::uid:$uid key:$key, value:$value", $logfile);
	    
	    $sql = "select count(*) as num from card.task_info t 
	    where t.uid = $uid and t.t_type = 11 and t.create_time = '$date'" ;
	    $rss = $this->db->query($sql);
	    $nCount = 0;
	    if($rss && $row = $this->db->fetch_assoc($rss)){
	        $nCount = (int)$row['num'];
	    }
	    
	    if(empty($value) && empty($nCount)){
	        //TODO:获得用户活跃等级
	        $user_active_level = 0;
	        $user = $this->getUserAttribute($uid);
	        //logs::addLog("DEBUG::task_info::initDayLoopTask****user::".json_encode($user), $logfile);
	        $user_active_level = $user['active_level'];
	        
	        logs::addLog("INFO::taskinfo::initDayLoopTask uid:$uid****user_active_level::$user_active_level", $logfile);
	        $sql = "SELECT t.id, t.probability FROM card.task_conf t WHERE open_object = 0
	           and task_type = 11 and face_object = 1 and tool_id <= $user_active_level and tool_num = 1";
	        $rows = $this->db->query($sql);
	        $prize_arr = array();
	        while ($row = $this->db->fetch_assoc($rows)) {
	            $tid = $row['id'];
	            $probability = (int)$row['probability'];
	            
	            $prize_arr[$tid] = $probability;
	        }
	        $t_id = $this->get_rand($prize_arr);
	        
	        /* 
            $sql = "SELECT min(id) as id, count(*) as total FROM card.task_conf WHERE open_object = 0 
                and task_type = 11 and face_object = 1 and tool_id <= $user_active_level";
            $rows = $this->db->query($sql);
             
            $id = 0;
            $total = 0;
            if ($row = $this->db->fetch_assoc($rows)) {
                $total = (int)$row['total'];
                $id = (int)$row['id'];
            }
            logs::addLog("initDayLoopTask******id:$id, total:$total, rows:$rows, sql:$sql", $logfile);
             
            $numbers = range ($id, $id+$total-1);
            //shuffle 将数组顺序随即打乱
            shuffle ($numbers);
            //array_slice 取该数组中的某一段
            $nums = array_slice($numbers,0,1);
             
            $in_sql = implode(',', $nums);
             
            logs::addLog("initDayLoopTask:: in_sql:$in_sql", $logfile); */
            
            $now = time();
            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
                " SELECT $uid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE id = $t_id";
            
            $flag = $this->db->query($sql);
            
            //logs::addLog("DEBUG::taskinfo::initDayLoopTask::uid:$uid flag:$flag, sql:$sql", $logfile);
            
            if($flag){
                $this->redis->set($key, $now);
                $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
                logs::addLog("INFO::taskinfo::initDayLoopTask::uid:$uid 跑环启动时间 Str_key=>:$key  v=>:$now", $logfile);
                
	            $sql ="select t.id, t.t_id ,t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2 from card.task_info t ".
	            	  "left join card.task_conf tc on t.t_id = tc.id ".
	            	  "where t.uid = $uid and t.t_type = 11 and t.t_open_object = 0 and t.create_time = '$date' for update";
	            //TODO:根据类型初始化任务catch
	            $rows = $this->db->query($sql);
	            while($row = $this->db->fetch_assoc($rows)){
	            	$id = (int)$row['id'];
					$totalNum = (int)$row['t_total_progress'];
					$openType = (int)$row['open_type'];
					$tool_id = $row['tool_id'];
					$tool_num = $row['tool_num'];
					$targetType = (int)$row['target_type'];
					$attachParam = (int)$row['target_params2'];
					$t_id = (int)$row['t_id'];
					$task_type = (int)$row['task_type'];
					$status = 0;
					$this->initDayLoopCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type);
					//发送跑环任务到事件
					{
					    $event_type = $type_loop = 1;//类型1：跑环任务  产生(跑环任务初始化)
					    $l_cur_num = 1;//跑环任务初始化，第一轮
					    $h_cur_num = 1;//跑环任务初始化，第一环
					    $awardItems = array();
					    
					    $event_task = new CEventHandleTask;
					    $event_task->redis = $this->redis;
					    $event_task->db = $this->db;
					    $event_task->taskModule_loop_event($event_type,$uid, $id, $l_cur_num, $h_cur_num,&$awardItems);
					}
				}
				
				//TODO:初始化轮数和环数
				$l_num = 0;
				$h_num = 0;
				$sql ="select t.id, t.parm1 from card.parameters_info t where t.id in (39, 40)";
				//TODO:根据类型初始化任务catch
				$rows = $this->db->query($sql);
				while ($row = $this->db->fetch_assoc($rows)) {
				    $id = (int)$row['id'];
				    $value = (int)$row['parm1'];
				    switch ($id)
				    {
				        case 39: //
				            $l_num = $value;
				            break;
				        case 40: //
				            $h_num = $value;
				            break;
				    }
				}
				
				$key = "loop_num:uid:$uid:$date";
				$data = array();
				$data['l_total_num'] = $l_num;
				$data['h_total_num'] = $h_num;
				$data['l_cur_num'] = 1;
				$data['h_cur_num'] = 1;
				
				$l_value = json_encode($data);
				$this->redis->set($key, $l_value);
				logs::addLog("INFO::taskinfo::initDayLoopTask::uid:$uid 登记跑环任务进度 Str_key=>:$key V=>:$l_value", $logfile);
            }
            else
            {
                logs::addLog("WARN::taskinfo::initDayLoopTask***uid:$uid**:: flag:$flag, sql:$sql", $logfile);
            }
	    }
	    //logs::addLog("DEBUG::taskinfo::initDayLoopTask::uid:$uid END", $logfile);
	}
	
	//初始化用户下一每日跑环任务
	public function initNextDayLoopTask($uid, $l_cur_num,$h_cur_num,&$awardItems){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::initNextDayLoopTask, uid:$uid****", $logfile);
	    
	    /* $sql = "SELECT min(id) as id, count(*) as total FROM card.task_conf WHERE open_object = 0 and task_type = 11 and face_object = 1";
	    $rows = $this->db->query($sql);
	     
	    $id = 0;
	    $total = 0;
	    if ($row = $this->db->fetch_assoc($rows)) {
	        $total = (int)$row['total'];
	        $id = (int)$row['id'];
	    }
	    logs::addLog("initNextDayLoopTask, $uid******id:$id, total:$total, rows:$rows, sql:$sql", $logfile);
	     
	    $numbers = range ($id, $id+$total-1);
	    //shuffle 将数组顺序随即打乱
	    shuffle ($numbers);
	    //array_slice 取该数组中的某一段
	    $nums = array_slice($numbers,0,1);
	     
	    $in_sql = implode(',', $nums); 
	    
	    logs::addLog("initNextDayLoopTask, $uid:: in_sql:$in_sql", $logfile);
	    */
	    
	    $user_active_level = 0;
	    $user = $this->getUserAttribute($uid);
	    //logs::addLog("task_info::initDayLoopTask****user::".json_encode($user), $logfile);
	    $user_active_level = $user['active_level'];
	     
	    logs::addLog("INFO::task_info::initDayLoopTask uid:$uid 已经完成的任务 l_cur_num:$l_cur_num h_cur_num:$h_cur_num user_active_level::$user_active_level", $logfile);
	    $sql = "SELECT t.id, t.probability FROM card.task_conf t WHERE open_object = 0
	    and task_type = 11 and face_object = 1 and tool_id <= $user_active_level and tool_num = $l_cur_num";
	    $rows = $this->db->query($sql);
	    $prize_arr = array();
	    while ($row = $this->db->fetch_assoc($rows)) {
	        $tid = $row['id'];
	        $probability = (int)$row['probability'];
	         
	        $prize_arr[$tid] = $probability;
	    }
	    $t_id = $this->get_rand($prize_arr);
	    
		//加入一条新的跑环任务
	    $date = $this->getdate();//date("Y-m-d");
	    $now = time();
	    $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
	        " SELECT $uid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE id = $t_id";
	    
	    $flag = $this->db->query($sql);
	    if($flag)
	    {
	        ;
	    }
	    else
	    {
	        logs::addLog("WARN::taskinfo::initNextDayLoopTask, uid:$uid flag:$flag, sql:$sql", $logfile);
	    }    

		//查询刚刚加入的那条任务的信息，把它加入到Redis缓存中
	    $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2 from card.task_info t ".
	          "left join card.task_conf tc on t.t_id = tc.id ".
	          "where t.uid = $uid and t.t_status != 5 and t.t_type = 11 and t.t_open_object = 0 and t.create_time = '$date' for update";
	    //TODO:根据类型初始化任务catch
	    $rows = $this->db->query($sql);
	    if($row = $this->db->fetch_assoc($rows)){
	        $id = (int)$row['id'];
	        $totalNum = (int)$row['t_total_progress'];
	        $openType = (int)$row['open_type'];
	        $tool_id = $row['tool_id'];
	        $tool_num = $row['tool_num'];
	        $targetType = (int)$row['target_type'];
	        $attachParam = (int)$row['target_params2'];
	        $t_id = (int)$row['t_id'];
	        $task_type = (int)$row['task_type'];
	        $status = 0;
	        $this->initDayLoopCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type);
	        //发送跑环任务到事件
	        {
	            $event_type = 1;//1产生；2开始；3完成；4领奖；5刷新；6放弃
	            $event_task = new CEventHandleTask;
				$event_task->redis = $this->redis;
				$event_task->db = $this->db;
	            $event_task->taskModule_loop_event($event_type,$uid, $id, $l_cur_num, $h_cur_num,&$awardItems);	             
	        }

	        
	    }else{
	        logs::addLog("WARN::taskinfo::initNextDayLoopTask, uid:$uid:: query error, sql:$sql", $logfile);
	    }
	    //logs::addLog("DEBUG::taskinfo::initNextDayLoopTask, uid:$uid****", $logfile);
	}
	
	//初始化挖宝任务
	public function initUserTreasureTask($uid){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::initUserTreasureTask uid:$uid***", $logfile);

	    $date = $this->getdate();//date("Y-m-d");
	    
	    $key = $this->get_day_treasure_key_by_uid($uid);
	    $value = $this->redis->get($key);
	    
	    $sql = "select count(*) as num from card.task_info t
	    where t.uid = $uid and t.t_type = 9 and t.create_time = '$date'" ;
	    $rss = $this->db->query($sql);
	    $nCount = 0;
	    if($rss && $row = $this->db->fetch_assoc($rss)){
	        $nCount = (int)$row['num'];
	    }
	    
// 	    logs::addLog("DEBUG::taskinfo::initUserTreasureTask uid:$uid value:$value", $logfile);
	    if(empty($value) && empty($nCount)){
            /* $sql = "SELECT min(id) as id, count(id) as total FROM card.task_conf WHERE open_object = 0 and task_type = 9 and face_object = 1";
            $rows = $this->db->query($sql);
             
            $id = 0;
            $total = 0;
            if ($row = $this->db->fetch_assoc($rows)) {
                $total = (int)$row['total'];
                $id = (int)$row['id'];
            }
            logs::addLog("initUserTreasureTask******id:$id, total:$total, rows:$rows, sql:$sql", $logfile);
             
            $numbers = range ($id, $id+$total-1);
            //shuffle 将数组顺序随即打乱
            shuffle ($numbers);
            //array_slice 取该数组中的某一段
            $nums = array_slice($numbers,0,1);
             
            $in_sql = implode(',', $nums);
             
            logs::addLog("initUserTreasureTask:: in_sql:$in_sql", $logfile); */
	        
	        $sql = "SELECT t.id, t.probability FROM card.task_conf t WHERE open_object = 0 and task_type = 9 and face_object = 1";
	        $rows = $this->db->query($sql);
	        $prize_arr = array();
	        while ($row = $this->db->fetch_assoc($rows)) {
	            $tid = $row['id'];
	            $probability = (int)$row['probability'];
	        
	            $prize_arr[$tid] = $probability;
	        }
	        $t_id = $this->get_rand($prize_arr);
            
            $now = time();
            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
                " SELECT $uid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE id = $t_id";
            
            $flag = $this->db->query($sql);
            
            //logs::addLog("DEBUG::taskinfo::initUserTreasureTask uid:$uid flag:$flag, sql:$sql", $logfile);
            
            if($flag){
                $this->redis->set($key, $now);
                $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
                logs::addLog("INFO::taskinfo::initUserTreasureTask uid:$uid Str_key=>:$key V=>:$now ", $logfile);
                
	            $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2 from card.task_info t ".
	            	  "left join card.task_conf tc on t.t_id = tc.id ".
	            	  "where t.uid = $uid and t.t_type = 9 and t.t_open_object = 0 and t.create_time = '$date' for update";
	            //TODO:根据类型初始化任务catch
	            $rows = $this->db->query($sql);
	            while($row = $this->db->fetch_assoc($rows)){
	            	$id = (int)$row['id'];
					$totalNum = (int)$row['t_total_progress'];
					$openType = (int)$row['open_type'];
					$tool_id = $row['tool_id'];
					$tool_num = $row['tool_num'];
					$targetType = (int)$row['target_type'];
					$attachParam = (int)$row['target_params2'];
					$status = (int)$row['t_status'];
					$t_id = (int)$row['t_id'];
					$task_type = (int)$row['task_type'];
					$this->initUserTreasureCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type);
				}
            }
            else
            {
                logs::addLog("WARN::taskinfo::initUserTreasureTask***uid:$uid**:: flag:$flag, sql:$sql", $logfile);
            }
	    }
	    //logs::addLog("DEBUG::taskinfo::initUserTreasureTask uid:$uid  END****", $logfile);
	}
	
	//TODO:初始化主播终极任务
	public function initSingerLastTask($uid){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::initSingerLastTask:: uid:$uid", $logfile);
	
	    $date = $this->getdate();//date("Y-m-d");
	    
	    $re_key = "init_singerlast:uid:$uid:$date";
	    $value = $this->redis->incrBy($re_key, 1);
	    $this->redis->expire($re_key, 2*24*60*60);		//设置两天过期
	    if($value > 1){
	        logs::addLog("WARN::taskinfo::initSingerLastTask:: uid:$uid, 重复初始化终极任务.", $logfile);
	        return;
	    }
	     
	    $key = $this->get_day_singer_last_key_by_uid($uid);
	    $value = $this->redis->get($key);
	     
	    $sql = "select count(*) as num from card.task_info t
	    where t.uid = $uid and t.t_type = 16 and t.create_time = '$date'" ;
	    $rss = $this->db->query($sql);
	    $nCount = 0;
	    if($rss && $row = $this->db->fetch_assoc($rss)){
	        $nCount = (int)$row['num'];
	    }
	    
	    if(empty($value) && empty($nCount)){
	        /* $sql = "SELECT min(id) as id, count(id) as total FROM card.task_conf WHERE open_object = 0 and task_type = 9 and face_object = 0";
	         $rows = $this->db->query($sql);
	          
	         $id = 0;
	         $total = 0;
	         if ($row = $this->db->fetch_assoc($rows)) {
	         $total = (int)$row['total'];
	         $id = (int)$row['id'];
	         }
	         logs::addLog("initSingerTreasureTask******id:$id, total:$total, rows:$rows, sql:$sql", $logfile);
	          
	         $numbers = range ($id, $id+$total-1);
	         //shuffle 将数组顺序随即打乱
	         shuffle ($numbers);
	         //array_slice 取该数组中的某一段
	         $nums = array_slice($numbers,0,1);
	          
	         $in_sql = implode(',', $nums);
	          
	         logs::addLog("initSingerTreasureTask:: in_sql:$in_sql", $logfile); */
	         
	        $sql = "SELECT t.id, t.probability FROM card.task_conf t WHERE open_object = 1 and task_type = 16 and face_object = 0";
	        $rows = $this->db->query($sql);
	        $prize_arr = array();
	        while ($row = $this->db->fetch_assoc($rows)) {
	            $tid = $row['id'];
	            $probability = (int)$row['probability'];
	
	            $prize_arr[$tid] = $probability;
	        }
	        $t_id = $this->get_rand($prize_arr);
	
	        $now = time();
	        $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, t_status, create_time, update_time)" .
	            " SELECT $uid, id, task_type, open_object, target_params1, 2, '$date', $now FROM card.task_conf where id = $t_id";
	        $flag = $this->db->query($sql);
	
	        //logs::addLog("DEBUG::taskinfo::initSingerLastTask:: uid:$uid:: flag:$flag, sql:$sql", $logfile);
	
	        if($flag){
	            $this->redis->set($key, $now);
	            $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
	            logs::addLog("INFO::taskinfo::initSingerLastTask uid:$uid Str_key=>:$key V=>:$now ", $logfile);
	
	            $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2 from card.task_info t ".
	                  "left join card.task_conf tc on t.t_id = tc.id ".
	                  "where t.uid = $uid and t.t_type = 16 and t.t_open_object = 1 and t.create_time = '$date' for update";
	            //TODO:根据类型初始化任务catch
	            $rows = $this->db->query($sql);
	            if($rows){
	                $row = $this->db->fetch_assoc($rows);
	                $id = (int)$row['id'];
	                $totalNum = (int)$row['t_total_progress'];
	                $openType = (int)$row['open_type'];
	                $tool_id = $row['tool_id'];
	                $tool_num = $row['tool_num'];
	                $targetType = (int)$row['target_type'];
	                $attachParam = (int)$row['target_params2'];
	                $status = (int)$row['t_status'];
	                $t_id = (int)$row['t_id'];
	                $task_type = (int)$row['task_type'];
	                $this->initSingerLastCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type);
	            }
	        }
	        else
	        {
	            logs::addLog("WARN::taskinfo::initSingerLastTask***uid:$uid**:: flag:$flag, sql:$sql", $logfile);
	        }
	    }
	    //logs::addLog("DEBUG::taskinfo::initSingerLastTask:: uid:$uid END****", $logfile);
	}
	
	//初始化主播挖宝任务
	public function initSingerTreasureTask($uid){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::initSingerTreasureTask uid:$uid ****", $logfile);

	    $date = $this->getdate();//date("Y-m-d");
	    
	    $key = $this->get_day_singer_treasure_key_by_uid($uid);
	    $value = $this->redis->get($key);
	    
	    $sql = "select count(*) as num from card.task_info t
	    where t.uid = $uid and t.t_type = 9 and t.create_time = '$date'" ;
	    $rss = $this->db->query($sql);
	    $nCount = 0;
	    if($rss && $row = $this->db->fetch_assoc($rss)){
	        $nCount = (int)$row['num'];
	    }
	    
 	    //logs::addLog("DEBUG::taskinfo::initSingerTreasureTask uid:$uid value:$value", $logfile);
	    if(empty($value) && empty($nCount)){
            /* $sql = "SELECT min(id) as id, count(id) as total FROM card.task_conf WHERE open_object = 0 and task_type = 9 and face_object = 0";
            $rows = $this->db->query($sql);
             
            $id = 0;
            $total = 0;
            if ($row = $this->db->fetch_assoc($rows)) {
                $total = (int)$row['total'];
                $id = (int)$row['id'];
            }
            logs::addLog("initSingerTreasureTask******id:$id, total:$total, rows:$rows, sql:$sql", $logfile);
             
            $numbers = range ($id, $id+$total-1);
            //shuffle 将数组顺序随即打乱
            shuffle ($numbers);
            //array_slice 取该数组中的某一段
            $nums = array_slice($numbers,0,1);
             
            $in_sql = implode(',', $nums);
             
            logs::addLog("initSingerTreasureTask:: in_sql:$in_sql", $logfile); */
	        
	        $sql = "SELECT t.id, t.probability FROM card.task_conf t WHERE open_object = 0 and task_type = 9 and face_object = 0";
	        $rows = $this->db->query($sql);
	        $prize_arr = array();
	        while ($row = $this->db->fetch_assoc($rows)) {
	            $tid = $row['id'];
	            $probability = (int)$row['probability'];
	             
	            $prize_arr[$tid] = $probability;
	        }
	        $t_id = $this->get_rand($prize_arr);
            
            $now = time();
            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
                " SELECT $uid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE id = $t_id";
            
            $flag = $this->db->query($sql);
            
            //logs::addLog("DEBUG::taskinfo::initSingerTreasureTask uid:$uid:: flag:$flag, sql:$sql", $logfile);
            
            if($flag){
                $this->redis->set($key, $now);
                $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
                logs::addLog("INFO::taskinfo::initSingerTreasureTask uid:$uid Str_key=>:$key V=>:$now ", $logfile);
                
	            $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2 from card.task_info t ".
	            	  "left join card.task_conf tc on t.t_id = tc.id ".
	            	  "where t.uid = $uid and t.t_type = 9 and t.t_open_object = 0 and t.create_time = '$date' for update";
	            //TODO:根据类型初始化任务catch
	            $rows = $this->db->query($sql);
	            while($row = $this->db->fetch_assoc($rows)){
	            	$id = (int)$row['id'];
					$totalNum = (int)$row['t_total_progress'];
					$openType = (int)$row['open_type'];
					$tool_id = $row['tool_id'];
					$tool_num = $row['tool_num'];
					$targetType = (int)$row['target_type'];
					$attachParam = (int)$row['target_params2'];
					$status = (int)$row['t_status'];
					$t_id = (int)$row['t_id'];
					$task_type = (int)$row['task_type'];
					$this->initSingerTreasureCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type);
				}
            }
            else
            {
                logs::addLog("WARN::taskinfo::initSingerTreasureTask***uid:$uid**:: flag:$flag, sql:$sql", $logfile);
            }
	    }
	    //logs::addLog("DEBUG::taskinfo::initSingerTreasureTask uid:$uid END****", $logfile);
	}
	
	//初始化用户每日随机任务
	public function initDayRandomTask($uid){
	    $logfile=basename(__FILE__, '.php');
	    //logs::addLog("DEBUG::taskinfo::initDayRandomTask uid:$uid ****", $logfile);
	    
	    $key = $this->get_day_random_key_by_uid($uid);
	    $value = $this->redis->get($key);
	    
	    //logs::addLog("DEBUG::taskinfo::initDayRandomTask uid:$uid value:$value", $logfile);
	    if(empty($value)){
            $sql = "SELECT min(id) as id, count(id) as total FROM card.task_conf WHERE open_object = 0 and task_type = 9 and face_object = 1";
            $rows = $this->db->query($sql);
             
            $id = 0;
            $total = 0;
            if ($row = $this->db->fetch_assoc($rows)) {                
                $total = (int)$row['total'];
                $id = (int)$row['id'];
            }
            //logs::addLog("DEBUG::taskinfo::initDayRandomTask uid:$uid id:$id, total:$total, rows:$rows, sql:$sql", $logfile);
             
            $numbers = range ($id, $id+$total-1);
            //shuffle 将数组顺序随即打乱
            shuffle ($numbers);
            //array_slice 取该数组中的某一段
            $nums = array_slice($numbers,0,2);
             
            $in_sql = implode(',', $nums);
             
            //logs::addLog("DEBUG::taskinfo::initDayRandomTask uid:$uid:: in_sql:$in_sql", $logfile);
             
            $date = date("Y-m-d");
            $now = time();
            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
                " SELECT $uid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE id in($in_sql)";
            
            $flag = $this->db->query($sql);
            
            //logs::addLog("taskinfo::initDayRandomTask uid:$uid:: flag:$flag, sql:$sql", $logfile);
            
            if($flag){
                $this->redis->set($key, $date);
                logs::addLog("INFO::taskinfo::initDayRandomTask uid:$uid Str_key=>:$key V=>:$date ", $logfile);
                
	            $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2 from card.task_info t ".
	            	  "left join card.task_conf tc on t.t_id = tc.id ".
	            	  "where t.uid = $uid and t.t_type = 9 and t.t_open_object = 0 and t.create_time = '$date' for update";
	            //TODO:根据类型初始化任务catch
	            $rows = $this->db->query($sql);
	            while($row = $this->db->fetch_assoc($rows)){
	            	$id = (int)$row['id'];
					$totalNum = (int)$row['t_total_progress'];
					$openType = (int)$row['open_type'];
					$tool_id = $row['tool_id'];
					$tool_num = $row['tool_num'];
					$targetType = (int)$row['target_type'];
					$attachParam = (int)$row['target_params2'];
					$status = (int)$row['t_status'];
					$t_id = (int)$row['t_id'];
					$task_type = (int)$row['task_type'];
					$this->initDayRandomCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type);
				}
            }
            else
            {
                logs::addLog("WARN::taskinfo::initDayRandomTask***uid:$uid**:: flag:$flag, sql:$sql", $logfile);
            }
	    }
	    //logs::addLog("DEBUG::taskinfo::initDayRandomTask uid:$uid END****", $logfile);
	}
    
    //用户第一次登录时初始化每日任务列表
    public function initSingerDayTasks($uid, $isSinger){
        
        //主播有每日任务，用户每日任务不在这处理
        if(!$isSinger){
            return;
        }
        
    	$logfile=basename(__FILE__, '.php');
    	//logs::addLog("DEBUG::taskinfo::initSingerDayTasks:: uid:$uid isSinger:$isSinger Function_enter", $logfile);
    	
    	$date = $this->getdate();//date("Y-m-d");
        $key = $this->get_day_key_by_uid($uid);
        $value = $this->redis->get($key);
        
        //logs::addLog("DEBUG::taskinfo::initSingerDayTasks:: uid:$uid singer_day_task_init_time:$value", $logfile);        
        $sql = "select count(*) as num from card.task_info t 
                left join card.task_conf tc on t.t_id = tc.id
	            where t.uid = $uid and t.t_type = 1 
                and t.create_time = '$date' and tc.open_object = 0 and tc.face_object = 0";
        $rss = $this->db->query($sql);
        $nCount = 0;
        if($rss && $row = $this->db->fetch_assoc($rss)){
            $nCount = (int)$row['num'];
        }
        if(empty($value) && empty($nCount)){
            $faceObj = 0;
            if(!$isSinger){
                $faceObj = 1;
            }
            
            $now = time();
            
            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time)" .
                " SELECT $uid, id, task_type, open_object, target_params1, '$date', $now FROM card.task_conf WHERE open_object = 0 and task_type = 1 and face_object = $faceObj";
            
            $flag = $this->db->query($sql);
            
            //logs::addLog("DEBUG::taskinfo::initSingerDayTasks:: uid:$uid INSERT_flag:$flag, sql:$sql", $logfile);            
            if($flag){
                $this->redis->set($key, $now);
                $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
                logs::addLog("INFO::taskinfo::initSingerDayTasks uid:$uid Str_key=>:$key V=>:$now ", $logfile);
                
	            $sql ="select t.id, t.t_id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.task_type, tc.target_params2 from card.task_info t ".
	            	  "left join card.task_conf tc on t.t_id = tc.id ".
	            	  "where t.uid = $uid and t.t_type = 1 and t.t_open_object = 0 and t.create_time = '$date' for update";
	            //TODO:根据类型初始化任务catch
	            $rows = $this->db->query($sql);
	            while($row = $this->db->fetch_assoc($rows)){
	            	$id = (int)$row['id'];
					$totalNum = (int)$row['t_total_progress'];
					$openType = (int)$row['open_type'];
					$tool_id = $row['tool_id'];
					$tool_num = $row['tool_num'];
					$targetType = (int)$row['target_type'];
					$attachParam = (int)$row['target_params2'];
					$status = (int)$row['t_status'];
					$t_id = (int)$row['t_id'];
					$task_type = (int)$row['task_type'];
					$this->initDayCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type);
				}
            }
            else
            {
                logs::addLog("WARN::taskinfo::initSingerDayTasks***uid:$uid**:: flag:$flag, sql:$sql", $logfile);
            }
        }
        
        //logs::addLog("DEBUG::taskinfo::initSingerDayTasks:: uid:$uid END", $logfile);
    }
    
    //初始化帮会星级任务
    public function initGangStartCatch($id, $gangid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type){
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG::taskinfo::initGangStartCatch***gangid:$gangid***task_id:$id", $logfile);
        $key = $this->get_gangstart_target_key_by_gangid($gangid, $id);
        
        $data = array();
        $data['id'] = $id;
        $data['gangid'] = $gangid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['t_attach_param'] = (int)$attachParam;
        $data['open_type'] = (int)$openType;
        $data['tool_id'] = $tool_id;
        $data['tool_num'] = $tool_num;
        $data['status'] = (int)$status;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
        $this->redis->set($key, json_encode($data));
         
        //保存上面的key
        $h_key = $this->get_gangstart_target_keys_by_gangid($gangid, $targetType);
        $this->redis->hset($h_key, $key, $key);
        
        //埋点任务详情缓存(任务缓存变更修改测试)
        $m_key = "maidian:taskid:$id" ;
        $this->redis->hset($m_key, "taskinfo",json_encode($data));
        $this->redis->hset($m_key, "t_total_progress",$totalNum);
        $this->redis->hset($m_key, "t_finish_progress",0);
        $this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
        $this->redis->expire($m_key, 2678400);//帮会星级任务有效期是一个月，定义缓存时间 为  31天  ：  31*24*60*60 = 2678400 
        logs::addLog("INFO::taskinfo::initGangStartCatch gangid:$gangid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
    }
    
    //初始化帮会任务缓存
    public function initGangCatch($id, $gangid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type){
        $logfile=basename(__FILE__, '.php');
		//logs::addLog("DEBUG::taskinfo::initGangCatch gangid:$gangid***task_id:$id ******", $logfile);
		$key = $this->get_gang_target_key_by_gangid($gangid, $id);
    	
    	$data = array();
        $data['id'] = $id;
        $data['gangid'] = $gangid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['t_attach_param'] = (int)$attachParam;
        $data['open_type'] = (int)$openType;
        $data['tool_id'] = $tool_id;
        $data['tool_num'] = $tool_num;
        $data['status'] = (int)$status;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
    	$this->redis->set($key, json_encode($data));
    	$this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
    	
    	//保存上面的key
    	$h_key = $this->get_gang_target_keys_by_gangid($gangid, $targetType);
    	$this->redis->hset($h_key, $key, $key);
    	$this->redis->expire($h_key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
    	
    	//埋点任务详情缓存(任务缓存变更修改测试)
    	$m_key = "maidian:taskid:$id" ;
    	$this->redis->hset($m_key, "taskinfo",json_encode($data));
    	$this->redis->hset($m_key, "t_total_progress",$totalNum);
    	$this->redis->hset($m_key, "t_finish_progress",0);
    	$this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
    	$this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME);
    	logs::addLog("INFO::taskinfo::initGangCatch gangid:$gangid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
    	
    }
    
    //master_apprentice:
    public function initMasterAndapprenticeCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type){
        $logfile=basename(__FILE__, '.php');
		//logs::addLog("DEBUG::taskinfo::initMasterAndapprenticeCatch::uid:$uid task_id:$id", $logfile);
		$key = $this->get_master_apprentice_target_key_by_uid($uid, $id);
    	
    	$data = array();
        $data['id'] = $id;
        $data['gangid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['t_attach_param'] = (int)$attachParam;
        $data['open_type'] = (int)$openType;
        $data['tool_id'] = $tool_id;
        $data['tool_num'] = $tool_num;
        $data['status'] = (int)$status;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
    	$this->redis->set($key, json_encode($data));
    	//$this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
    	
    	//保存上面的key
    	$h_key = $this->get_master_apprentice_target_keys_by_uid($uid, $targetType);
    	$this->redis->hset($h_key, $key, $key);
    	//$this->redis->expire($h_key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
    	
    	//埋点任务详情缓存(任务缓存变更修改测试)
    	$m_key = "maidian:taskid:$id" ;
    	$this->redis->hset($m_key, "taskinfo",json_encode($data));
    	$this->redis->hset($m_key, "t_total_progress",$totalNum);
    	$this->redis->hset($m_key, "t_finish_progress",0);
    	$this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
    	$this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME);    	
    	logs::addLog("INFO::taskinfo::initMasterAndapprenticeCatch uid:$uid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
    }
    
    //初始化帮会成员任务缓存
    public function initGangManCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type){
        $logfile=basename(__FILE__, '.php');
		//logs::addLog("DEBUG::taskinfo::initGangManCatch::uid:$uid task_id:$id", $logfile);
		$key = $this->get_gang_man_target_key_by_uid($uid, $id);
    	
    	$data = array();
        $data['id'] = $id;
        $data['gangid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['t_attach_param'] = (int)$attachParam;
        $data['open_type'] = (int)$openType;
        $data['tool_id'] = $tool_id;
        $data['tool_num'] = $tool_num;
        $data['status'] = (int)$status;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
    	$this->redis->set($key, json_encode($data));
    	$this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
    	
    	//保存上面的key
    	$h_key = $this->get_gang_man_target_keys_by_uid($uid, $targetType);
    	$this->redis->hset($h_key, $key, $key);
    	$this->redis->expire($h_key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
    	
    	//埋点任务详情缓存(任务缓存变更修改测试)
    	$m_key = "maidian:taskid:$id" ;
    	$this->redis->hset($m_key, "taskinfo",json_encode($data));
    	$this->redis->hset($m_key, "t_total_progress",$totalNum);
    	$this->redis->hset($m_key, "t_finish_progress",0);
    	$this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
    	$this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME);    	
    	logs::addLog("INFO::taskinfo::initGangManCatch uid:$uid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
		
    }
    
    public function get_rand($proArr) { 
        $result = 1; 
        //概率数组的总概率精度 
        $proSum = array_sum($proArr); 
        //概率数组循环 
        foreach ($proArr as $key => $proCur) { 
            $randNum = mt_rand(1, $proSum);             //抽取随机数
            if ($randNum <= $proCur) { 
                $result = $key;                         //得出结果
                break; 
            } else { 
                $proSum -= $proCur;                     
            } 
        } 
        unset ($proArr); 
        return $result; 
    }
    
    public function pro_rand_unique_multi( $proArr, $num = 1 ){
        $logfile=basename(__FILE__, '.php');
        
        $result = array();
        if( $num > count($proArr) ){
            //logs::addLog("DEBUG::taskinfo::pro_rand_unique_multi ******", $logfile);
            return $result;
        }
        while(1){
            if($num < 1){
                break;
            }
            $curResult = $this->get_rand($proArr);
            $result[] = $curResult;
            //重置总概率精度，有待概率论验证
            unset($proArr[$curResult]);
            $num -= 1;
        }
         
        return $result;
    }
    
    public function initDayLoopCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type){
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG:taskinfo::initDayLoopCatch uid:$uid***task_id:$id ******", $logfile);
        $key = $this->get_day_loop_target_key_by_uid($uid, $id);
        
        $data = array();
        $data['id'] = $id;
        $data['uid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['t_attach_param'] = (int)$attachParam;
        $data['open_type'] = (int)$openType;
        $data['tool_id'] = $tool_id;
        $data['tool_num'] = $tool_num;
        $data['status'] = (int)$status;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
        $this->redis->set($key, json_encode($data));
        $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
         
        //保存上面的key
        $h_key = $this->get_day_loop_target_keys_by_uid($uid, $targetType);
        $this->redis->hset($h_key, $key, $key);
        $this->redis->expire($h_key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
        
        //埋点任务详情缓存(任务缓存变更修改测试)
        $m_key = "maidian:taskid:$id" ;
        $this->redis->hset($m_key, "taskinfo",json_encode($data));
        $this->redis->hset($m_key, "t_total_progress",$totalNum);
        $this->redis->hset($m_key, "t_finish_progress",0);//进度为0
        $this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
        $this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME); 
        logs::addLog("INFO::taskinfo::initDayLoopCatch uid:$uid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
    }
    
    public function initUserTreasureCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type){
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG::taskinfo::initUserTreasureCatch  uid:$uid***task_id:$id  ******", $logfile);
        $key = $this->get_day_treasure_target_key_by_uid($uid, $id);
        
        $data = array();
        $data['id'] = $id;
        $data['uid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['t_attach_param'] = (int)$attachParam;
        $data['open_type'] = (int)$openType;
        $data['tool_id'] = $tool_id;
        $data['tool_num'] = $tool_num;
        $data['status'] = (int)$status;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
        $this->redis->set($key, json_encode($data));
        $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
         
        //保存上面的key
        $h_key = $this->get_day_treasure_target_keys_by_uid($uid, $targetType);
        $this->redis->hset($h_key, $key, $key);
        $this->redis->expire($h_key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
        
        //埋点任务详情缓存(任务缓存变更修改测试)
        $m_key = "maidian:taskid:$id" ;
        $this->redis->hset($m_key, "taskinfo",json_encode($data));
        $this->redis->hset($m_key, "t_total_progress",$totalNum);
        $this->redis->hset($m_key, "t_finish_progress",0);
        $this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
        $this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME);  
        logs::addLog("INFO::taskinfo::initUserTreasureCatch uid:$uid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
    }
    
    public function initSingerLastCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type){
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG::taskinfo::initSingerLastCatch  uid:$uid***task_id:$id  ******", $logfile);
        $key = $this->get_day_singer_last_target_key_by_uid($uid, $id);
        
        $data = array();
        $data['id'] = $id;
        $data['uid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['t_attach_param'] = (int)$attachParam;
        $data['open_type'] = (int)$openType;
        $data['tool_id'] = $tool_id;
        $data['tool_num'] = $tool_num;
        $data['status'] = (int)$status;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
        $this->redis->set($key, json_encode($data));
        $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
         
        //保存上面的key
        $h_key = $this->get_day_singer_last_target_keys_by_uid($uid, $targetType);
        $this->redis->hset($h_key, $key, $key);
        $this->redis->expire($h_key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
        
        //埋点任务详情缓存(任务缓存变更修改测试)
        $m_key = "maidian:taskid:$id" ;
        $this->redis->hset($m_key, "taskinfo",json_encode($data));
        $this->redis->hset($m_key, "t_total_progress",$totalNum);
        $this->redis->hset($m_key, "t_finish_progress",0);
        $this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
        $this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME);
        
        //主播任务事件埋点
        {
            $event_task = new CEventHandleTask;
			$event_task->redis = $this->redis;
			$event_task->db = $this->db;
            $event_type = 1;//类型1：主播终极任务产生
            $awardItems = array();
            $event_task->taskModule_singer_event($event_type,$uid, $id,&$awardItems);
        } 
        logs::addLog("INFO::taskinfo::initSingerLastCatch uid:$uid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
    }
    
    public function initSingerTreasureCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type){
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG::taskinfo::initSingerTreasureCatch  uid:$uid***task_id:$id  ******", $logfile);
        $key = $this->get_day_singer_treasure_target_key_by_uid($uid, $id);
        
        $data = array();
        $data['id'] = $id;
        $data['uid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['t_attach_param'] = (int)$attachParam;
        $data['open_type'] = (int)$openType;
        $data['tool_id'] = $tool_id;
        $data['tool_num'] = $tool_num;
        $data['status'] = (int)$status;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
        $this->redis->set($key, json_encode($data));
        $this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
         
        //保存上面的key
        $h_key = $this->get_day_singer_treasure_target_keys_by_uid($uid, $targetType);
        $this->redis->hset($h_key, $key, $key);
        $this->redis->expire($h_key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
        
        //埋点任务详情缓存(任务缓存变更修改测试)
        $m_key = "maidian:taskid:$id" ;
        $this->redis->hset($m_key, "taskinfo",json_encode($data));
        $this->redis->hset($m_key, "t_total_progress",$totalNum);
        $this->redis->hset($m_key, "t_finish_progress",0);
        $this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
        $this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME);  
        logs::addLog("INFO::taskinfo::initSingerTreasureCatch uid:$uid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
    }
    
    public function initDayRandomCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type){
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG::taskinfo::initDayRandomCatch uid:$uid***task_id:$id  ******", $logfile);
        $key = $this->get_day_random_target_key_by_uid($uid, $id);
        
        $proArr =array(3=>10,2=>30,1=>60);
        $times = $this->get_rand($proArr);
        
        $data = array();
        $data['id'] = $id;
        $data['uid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['t_attach_param'] = (int)$attachParam;
        $data['open_type'] = (int)$openType;
        $data['tool_id'] = $tool_id;
        $data['tool_num'] = $tool_num;
        $data['status'] = (int)$status;
        $data['times'] = (int)$times;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
        $this->redis->set($key, json_encode($data));
         
        //保存上面的key
        $h_key = $this->get_day_random_target_keys_by_uid($uid, $targetType);
        $this->redis->hset($h_key, $key, $key);
        
        //埋点任务详情缓存(任务缓存变更修改测试)
        $m_key = "maidian:taskid:$id" ;
        $this->redis->hset($m_key, "taskinfo",json_encode($data));
        $this->redis->hset($m_key, "t_total_progress",$totalNum);
        $this->redis->hset($m_key, "t_finish_progress",0);
        $this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
        $this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME);  
        logs::addLog("INFO::taskinfo::initDayRandomCatch uid:$uid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
    }
    
    //初始化用户每日任务缓存
    public function initDayCatch($id, $uid, $targetType, $totalNum, $attachParam, $openType, $tool_id, $tool_num, $status,$t_id,$task_type){
		$logfile=basename(__FILE__, '.php');
		//logs::addLog("DEBUG::taskinfo::initDayCatch:: uid:$uid task_id:$id", $logfile);
		//$key = $this->get_day_target_key_by_uid($uid, $targetType, $attachParam);
		$key = $this->get_day_target_key_by_uid($uid, $id);
    	
    	$data = array();
        $data['id'] = $id;
        $data['uid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['t_attach_param'] = (int)$attachParam;
        $data['open_type'] = (int)$openType;
        $data['tool_id'] = $tool_id;
        $data['tool_num'] = $tool_num;
        $data['status'] = (int)$status;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
    	$this->redis->set($key, json_encode($data));
    	$this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
    	
    	//保存上面的key
    	$h_key = $this->get_day_target_keys_by_uid($uid, $targetType);
    	$this->redis->hset($h_key, $key, $key);
    	$this->redis->expire($h_key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
    	
    	//埋点任务详情缓存(任务缓存变更修改测试)
    	$m_key = "maidian:taskid:$id" ;
    	$this->redis->hset($m_key, "taskinfo",json_encode($data));
    	$this->redis->hset($m_key, "t_total_progress",$totalNum);
    	$this->redis->hset($m_key, "t_finish_progress",0);
    	$this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
    	$this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME);    	
    	   	
    	//主播任务事件埋点
    	{
    	    $event_task = new CEventHandleTask;
			$event_task->redis = $this->redis;
			$event_task->db = $this->db;
    	    $event_type = 1;//类型1：主播每日任务产生
    	    $awardItems = array();
    	    $event_task->taskModule_singer_event($event_type,$uid, $id,&$awardItems);
    	}
    	logs::addLog("INFO::taskinfo::initDayCatch uid:$uid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
    }
    
    //初始化用户主线任务缓存
    public function initMainCatch($id, $uid, $targetType, $totalNum, $attachParam, $followTaskid, $status,$t_id,$task_type,$t_id,$task_type){
    	$logfile=basename(__FILE__, '.php');
    	//logs::addLog("DEBUG::taskinfo::initMainCatch uid:$uid task_id:$id  ******", $logfile);
    	$key = $this->get_main_target_key_by_uid($uid, $id);
    	
    	$data = array();
        $data['id'] = $id;
        $data['uid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['t_attach_param'] = (int)$attachParam;
        $data['follow_task_id'] = (int)$followTaskid;
        $data['status'] = (int)$status;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
    	$this->redis->set($key, json_encode($data));
    	
    	//保存上面的key
    	$h_key = $this->get_main_target_keys_by_uid($uid, $targetType);
    	$this->redis->hset($h_key, $key, $key);
    	
    	//埋点任务详情缓存(任务缓存变更修改测试)
    	$m_key = "maidian:taskid:$id" ;
    	$this->redis->hset($m_key, "taskinfo",json_encode($data));
    	$this->redis->hset($m_key, "t_total_progress",$totalNum);
    	$this->redis->hset($m_key, "t_finish_progress",0);
    	$this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
    	$this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME);  
    	logs::addLog("INFO::taskinfo::initMainCatch uid:$uid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
    }
    
    //初始化主播开启任务缓存
    public function initSingerCatch($id, $uid, $targetType, $totalNum, $status, $attachParam, $openType, $tool_id, $tool_num,$t_id,$task_type){
    	$logfile=basename(__FILE__, '.php');
    	//logs::addLog("DEBUG::taskinfo::initSingerCatch uid:$uid task_id:$id ******", $logfile);
    	$key = $this->get_singer_target_key_by_uid($uid, $id);
    	
    	$data = array();
    	$data['id'] = $id;
        $data['uid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        $data['status'] = (int)$status;
        $data['t_attach_param'] = (int)$attachParam;
        $data['open_type'] = (int)$openType;
        $data['tool_id'] = $tool_id;
        $data['tool_num'] = $tool_num;
        $data['t_id'] = (int)$t_id;
        $data['task_type'] = (int)$task_type;
    	$this->redis->set($key, json_encode($data));
    	$this->redis->expire($key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
    	
    	$h_key = $this->get_singer_target_keys_by_uid($uid, $targetType);
    	$this->redis->hset($h_key, $key, $key);
    	$this->redis->expire($h_key, TaskInfo::REDIS_KEY_EXPIRE_TIME);
    	
    	//埋点任务详情缓存(任务缓存变更修改测试)
    	$m_key = "maidian:taskid:$id" ;
    	$this->redis->hset($m_key, "taskinfo",json_encode($data));
    	$this->redis->hset($m_key, "t_total_progress",$totalNum);
    	$this->redis->hset($m_key, "t_finish_progress",0);
    	$this->redis->hset($m_key, "tasklog",1);//1产生；2开始；3完成；4领奖；5刷新；6放弃
    	$this->redis->expire($m_key, TaskInfo::REDIS_KEY_MAIDIAN_TIME);    	    	
    	    	
    	//主播任务事件埋点
    	{
    	    $event_task = new CEventHandleTask;
    	    $event_task->redis = $this->redis;
    	    $event_task->db = $this->db;
    	    $event_type = 1;//类型1：主播任务产生
    	    $awardItems = array();
    	    $event_task->taskModule_singer_event($event_type,$uid, $id,&$awardItems);
    	}
    	logs::addLog("INFO::taskinfo::initSingerCatch uid:$uid task_id:$id  缓存初始化: 任务大类Has_key:$h_key 具体任务详情Str_key:$key 埋点m_key:$m_key", $logfile);
    }

	public function checkStatus($hash_key,$hash_fields){
		$logfile=basename(__FILE__, '.php');
    	//logs::addLog("DEBUG::taskinfo::checkStatus hash_key:$hash_key hash_fields:$hash_fields ", $logfile);

		$target_type = 32;
		//判断任务是否全完成了
		foreach ($hash_fields as $field){
			$key = $this->redis->hget($hash_key, $field);
			$value = $this->redis->get($key);
			
			//如果该用户没有该任务，则不执行
			if(empty($value)){
			    logs::addLog("WARN::taskinfo::checkStatus::任务有Str_key没有value  Has_key:$hash_key Str_key:$key", $logfile);
				continue;
			}
			
			$data = json_decode($value, TRUE);
			//有等待领奖、完成、删除状态的任务 就不用客户端再发标志了
			if ($data['status'] == 1 || $data['status'] == 4 || $data['status'] == 5){
				//logs::addLog("DEBUG::taskinfo::checkStatus hash_key:$hash_key hash_fields:$hash_fields **有等待领奖、完成、删除状态的任务 就不用客户端再发标志****return false", $logfile);
				return false;
			}
		}

		//logs::addLog("DEBUG::taskinfo::checkStatus hash_key:$hash_key hash_fields:$hash_fields END *****return true", $logfile);
		return true;
	}

	//在缓存中检查用户当前的任务中 是否存在每日群粉丝聊天的任务
	public function checkChatTaskFlag($uid){
		$logfile=basename(__FILE__, '.php');
    	//logs::addLog("DEBUG::taskinfo::checkChatTaskFlag******uid:".$uid, $logfile);

		$target_type = 32;
		$isSinger = $this->isSinger($uid);
		if ($isSinger){
			//主播每日任务
			$h_key = $this->get_day_target_keys_by_uid($uid, $target_type);
    		$fields = $this->redis->hkeys($h_key);
			if($fields && $this->checkStatus($h_key,$fields)){
				return true;
			}

			//主播开启的任务
			$h_key = $this->get_singer_target_keys_by_uid($uid, $target_type);
    		$fields = $this->redis->hkeys($h_key);
			if($fields && $this->checkStatus($h_key,$fields)){
				return true;
			}
		}else{
			//用户每日跑环任务 
			$h_key = $this->get_day_loop_target_keys_by_uid($uid,$target_type);
			$fields = $this->redis->hkeys($h_key);
			if($fields && $this->checkStatus($h_key,$fields)){
				return true;
			}
		}

		//检查帮会任务,是否为帮会会员
		$gangid = $this->getGangId($uid);
		if(!empty($gangid)){
			$h_key = $this->get_gang_target_keys_by_gangid($gangid, $target_type);
    		$fields = $this->redis->hkeys($h_key);
			if ($fields && $this->checkStatus($h_key,$fields)){
				return true;
			}
		}

		//logs::addLog("DEBUG::taskinfo::checkChatTaskFlag******return false!uid:".$uid, $logfile);
		return false;
	}
	
	/*
	public function get_taskinfo_by_uid($uid){
		$date = date("Y-m-d");
		$query = "select * from task_info t left join task_conf tc on t.t_id = tc.id and tc.face_object = 0
				where t.uid = $uid and t.create_time = $date" ;
		$rs = $this->db->query($query) ;

		$rows = array() ;
		while($row = $this->db->fetch_assoc($rs)){
			$rows[] = $row ;
		}
		
		return $rows;
	}*/
    
    //获取帮会成员任务key
    private function get_gang_key_by_gangid($gangid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "gang:$gangid:$date";
         
        return $key;
    }
    
    //获取帮会个人任务key
    private function get_gang_man_key_by_uid($uid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "gangman:$uid:$date";
         
        return $key;
    }
    
    //获取师徒任务key
    private function get_master_apprentice_key_by_uid($uid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "master_apprentice:$uid:$date";
         
        return $key;
    }
    
    //获取用户每日跑环任务key
    private function get_day_loop_key_by_uid($uid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "looptask:uid:$uid:$date";
         
        return $key;
    }
    
    private function get_day_loop_target_key_by_uid($uid, $tid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "looptask:uid:$uid:$date:tid:$tid";
         
        return $key;
    }
	//获取用户每日目标任务的所有key
	private function get_day_loop_target_keys_by_uid($uid, $targetType){
	    $date = $this->getdate();//date("Y-m-d");
	    $key = "looptask:uid:$uid:$date:$targetType";
	    
	    return $key;
	}

	//获取主播终极任务key
	private function get_day_singer_last_key_by_uid($uid){
	    $date = $this->getdate();//date("Y-m-d");
	    $key = "singerlast_task:uid:$uid:$date";
	
	    return $key;
	}
	private function get_day_singer_last_target_key_by_uid($uid, $tid){
	    $date = $this->getdate();//date("Y-m-d");
	    $key = "singerlast_task:uid:$uid:$date:tid:$tid";
	
	    return $key;
	}
	private function get_day_singer_last_target_keys_by_uid($uid, $targetType){
	    $date = $this->getdate();//date("Y-m-d");
	    $key = "singerlast_task:uid:$uid:$date:$targetType";
	
	    return $key;
	}
	
	
	
	//获取主播每日挖宝任务key
	private function get_day_singer_treasure_key_by_uid($uid){
	    $date = $this->getdate();//date("Y-m-d");
	    $key = "singertreasure_task:uid:$uid:$date";
	     
	    return $key;
	}
	
	private function get_day_singer_treasure_target_key_by_uid($uid, $tid){
	    $date = $this->getdate();//date("Y-m-d");
	    $key = "singertreasure_task:uid:$uid:$date:tid:$tid";
	     
	    return $key;
	}
	//获取用户每日目标任务的所有key
	private function get_day_singer_treasure_target_keys_by_uid($uid, $targetType){
	    $date = $this->getdate();//date("Y-m-d");
	    $key = "singertreasure_task:uid:$uid:$date:$targetType";
	     
	    return $key;
	}
	
    //获取用户每日挖宝任务key
    private function get_day_treasure_key_by_uid($uid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "usertreasure_task:uid:$uid:$date";
         
        return $key;
    }
    
    private function get_day_treasure_target_key_by_uid($uid, $tid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "usertreasure_task:uid:$uid:$date:tid:$tid";
         
        return $key;
    }
	//获取用户每日目标任务的所有key
	private function get_day_treasure_target_keys_by_uid($uid, $targetType){
	    $date = $this->getdate();//date("Y-m-d");
	    $key = "usertreasure_task:uid:$uid:$date:$targetType";
	    
	    return $key;
	}
    
    //获取用户每日随机任务key
    private function get_day_random_key_by_uid($uid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "randomtask:uid:$uid:$date";
         
        return $key;
    }
    
    private function get_day_random_target_key_by_uid($uid, $tid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "randomtask:uid:$uid:$date:tid:$tid";
         
        return $key;
    }
	//获取用户每日目标任务的所有key
	private function get_day_random_target_keys_by_uid($uid, $targetType){
	    $date = $this->getdate();//date("Y-m-d");
	    $key = "randomtask:uid:$uid:$date:$targetType";
	    
	    return $key;
	}

	//获取用户每日任务key
	private function get_day_key_by_uid($uid){
		$date = $this->getdate();//date("Y-m-d");
    	$key = "singer_day_uid:$uid:$date";
    	
    	return $key;
	}
	//获取用户每日目标任务的所有key
	private function get_day_target_keys_by_uid($uid, $targetType){
	    $date = $this->getdate();//date("Y-m-d");
	    $key = "singer_day_uid:$uid:$date:$targetType";
	    
	    return $key;
	}
	//获取用户每日目标任务key
    private function get_day_target_key_by_uid($uid, $tid){//$uid, $targetType, $giftid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "singer_day_uid:$uid:$date:tid:$tid";
         
        return $key;
    }
    
    private function get_gangstart_target_keys_by_gangid($gangid, $targetType){
        $date = date('Y-m-01', strtotime($this->getdate()));//date("Y-m-d")));
        $key = "gangstart:$gangid:$date:$targetType";
         
        return $key;
    }
    
    private function get_gangstart_target_key_by_gangid($gangid, $tid){
        $date = date('Y-m-01', strtotime($this->getdate()));//date("Y-m-d")));
        $key = "gangstart:$gangid:$date:tid:$tid";
         
        return $key;
    }
    
    private function get_gang_target_keys_by_gangid($gangid, $targetType){
        $date = $this->getdate();//date("Y-m-d");
        $key = "gang:$gangid:$date:$targetType";
         
        return $key;
    }
    private function get_gang_target_key_by_gangid($gangid, $tid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "gang:$gangid:$date:tid:$tid";
         
        return $key;
    }
    
    private function get_gang_man_target_keys_by_uid($uid, $targetType){
        $date = $this->getdate();//date("Y-m-d");
        $key = "gangman:$uid:$date:$targetType";
         
        return $key;
    }
    private function get_gang_man_target_key_by_uid($uid, $tid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "gangman:$uid:$date:tid:$tid";
         
        return $key;
    }
    
    private function get_master_apprentice_target_keys_by_uid($uid, $targetType){
        //$date = $this->getdate();//date("Y-m-d");
        $key = "master_apprentice:$uid:$targetType";
         
        return $key;
    }
    private function get_master_apprentice_target_key_by_uid($uid, $tid){
        //$date = $this->getdate();//date("Y-m-d");
        $key = "master_apprentice:$uid:tid:$tid";
         
        return $key;
    }
	
	private function get_main_key_by_uid($uid){
		return "uid:$uid:main";
	}
	//获取用户主线目标任务的所有key
	private function get_main_target_keys_by_uid($uid, $targetType){
	    $key = "uid:$uid:main:$targetType";
	    
	    return $key;
	}
	//获取用户主线目标任务key
    private function get_main_target_key_by_uid($uid, $tid){
        return "uid:$uid:main:tid:$tid";
    }
	
	private function get_singer_key_by_uid($uid){
		$date = $this->getdate();//date("Y-m-d");
    	$key = "singeruid:$uid:$date";
    	
    	return $key;
	}
	//获取主播每日开播任务的所有key
	private function get_singer_target_keys_by_uid($uid, $targetType){
	    $date = $this->getdate();//date("Y-m-d");
	    $key = "singeruid:$uid:$date:$targetType";
	    
	    return $key;
	}
	private function get_singer_target_key_by_uid($uid, $tid){
        $date = $this->getdate();//date("Y-m-d");
        $key = "singeruid:$uid:$date:tid:$tid";
        return $key;
    }
    
    
    
    
    
    /***********************************主播获得阳光处理逻辑如下****************************************/
    
    public function get_anchor_sun_exp_conf($level)
    {
        $key = "h_anchor_sun_exp_conf";
        $field = $level . "";
    
        $ret = $this->redis->hGet($key, $field);
        if (!empty($ret)) {
            return json_decode($ret, true);
        }
    
        $sql = "SELECT * FROM raidcall.anchor_level_info WHERE anchor_level=$level";
        $rows = $this->db->query($sql);
        if (!empty($rows) && $this->db->affected_rows() > 0) {
            $row = $rows->fetch_assoc();
            $this->redis->hSet($key, $field, json_encode($row));
            logs::addLog("INFO::taskinfo::get_anchor_sun_exp_conf HSET_HASH_KEY  key:$key field:$field", $logfile);
            return $row;
        }
    
        return null;
    }
    
    //
    public function on_anchor_sun_exp_add($uid, $level_now, $exp_now, $exp_add)
    {
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG::taskinfo::on_anchor_sun_exp_add. uid:$uid, level_now:$level_now, exp_now:$exp_now, exp_add:$exp_add", $logfile);
        
        $lvl_add = $level_now;
        $exp_final = $exp_add + $exp_now;
        $lvl_inf_now = $this->get_anchor_sun_exp_conf($lvl_add);
        $lvl_inf_next = $this->get_anchor_sun_exp_conf($lvl_add + 1);
    
        do {
            if (empty($lvl_inf_now) || empty($lvl_inf_next)) {
                break;
            }
             
            do {
                if ($exp_final < $lvl_inf_now['level_exp']) {
                    break;
                }
    
                $exp_final = $exp_final - $lvl_inf_now['level_exp'];
                $lvl_add += 1;
    
                $lvl_inf_now = $lvl_inf_next;
                $lvl_inf_next = $this->get_anchor_sun_exp_conf($lvl_add + 1);
    
            } while (!empty($lvl_inf_next));
             
        } while (0);
    
        $sql = "UPDATE raidcall.anchor_info SET anchor_current_experience=anchor_current_experience+$exp_add,level_id=$lvl_add,anchor_curr_exp=$exp_final WHERE uid=$uid";
        $rows = $this->db->query($sql);
        
        //logs::addLog("DEBUG::taskinfo::on_anchor_sun_exp_add  uid:$uid  sql:$sql, result:$rows, row size:".$this->db->affected_rows(), $logfile);
        if (empty($rows) || $this->db->affected_rows() <= 0) {
            logs::addLog("WARN::taskinfo::on_anchor_sun_exp_add. uid:$uid, level_now:$level_now, exp_now:$exp_now, exp_add:$exp_add  数据更新失败failure_sql:$sql", $logfile);
            return false;
        }        
        return true;    
        // no cache. no need clean.
    }
    
    //主播获得阳光的处理逻辑
    public function add_singer_sun($singerid, $sun_num){
        $logfile=basename(__FILE__, '.php');
        //logs::addLog("DEBUG::taskinfo::add_singer_sun singerid: $singerid sun_num:$sun_num ", $logfile);
        //获取主播旧奖励等级
        $query = "select * from raidcall.anchor_info where flag = 1 and uid = $singerid";
        $rs = $this->db->query($query);
        if ($row = $this->db->fetch_assoc($rs)) {
            $flag = $this->on_anchor_sun_exp_add($singerid, $row['level_id'], $row['anchor_curr_exp'], $sun_num);
            if(empty($flag)){
                return false;
            }
            
            $query = "select t.family_id from raidcall.anchor_info t where t.uid = $singerid";
            $rs2 = $this->db->query($query) ;
            $family_id = 0;
            if($rs2){
                $rs = $this->db->fetch_assoc($rs2);
                $family_id = (int)$rs['family_id'];
            }
            	
            $now = time();
            $query = "insert into rcec_record.sun_record (sid, uid, zid, time, num, type, family_id) values(0, 0, $singerid, $now, $sun_num, 1, $family_id)";
            $rs2 = $this->db->query($query);
            if (!$rs2) {
                logs::addLog("WARN::taskinfo::add_singer_sun singerid: $singerid sun_num:$sun_num **:添加阳光记录表失败.sql:".$query, $logfile);
                return false;
            }
            
            return true;
        }else{
            return false;
        }
    }
    
    /**********************************************************************************/
    
    /**
     * 发送post请求
    * @param string $url 请求地址
    * @param array $post_data post键值对数据
    * @return string
    */
    public function send_post($url, $post_data) {
    
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 3 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
    
        return $result;
    }
    
    function post_msg($uid, $sun_num)
    {
        $logfile=basename(__FILE__, '.php');
    
        $user_data = array(
            'cmd' => 'on_task_sun_award_rq',
            'uid' => (int)$uid,
            'sunshine' => $sun_num,
            'timestamp' => time()
        );
        $server_data = array(
            'svid' => GlobalConfig::$SERVER_ID,
            'rq_from' => 'task'
        );
        $params_data = array(
            'user_data' => $user_data,
            'server_data' => $server_data
        );
         
        $post_data = array(
            'params' => json_encode($params_data),
            'data' => '',
            'method' => 'rcec.index'
        );
        $url = GlobalConfig::GetPhpApi();
        logs::addLog("INFO::taskinfo::post_msg uid:$uid, post_data:".json_encode($post_data), $logfile);
        $post_rs = $this->send_post($url, $post_data);
        logs::addLog("INFO::taskinfo::post_msg uid:$uid, post_rs:".json_encode($post_rs), $logfile);
    }
}
?>
