<?php

class TaskModel extends ModelBase
{
    
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
        
    public function __construct ()
    {
        parent::__construct();
    }
    
    private function get_day_target_key_by_uid_gift($uid, $targetType, $giftid){
        $date = date("Y-m-d");
        $key = "uid:$uid:$date:$targetType:$giftid";
         
        return $key;
    }
    //获取用户每日目标任务key
    private function get_day_target_key_by_uid_gift_bak($uid, $targetType, $giftid){
        $date = date("Y-m-d");
        $key = "uid:$uid:$date:$targetType:$giftid:bak";
         
        return $key;
    }
    /*
     * 获取用户每日目标任务key
     */
    private function get_day_target_key_by_uid($uid, $targetType){
        $date = date("Y-m-d");
        $key = "uid:$uid:$date:$targetType";
         
        return $key;
    }
    //获取用户每日目标任务key
    private function get_day_target_key_by_uid_bak($uid, $targetType){
        $date = date("Y-m-d");
        $key = "uid:$uid:$date:$targetType:bak";
         
        return $key;
    }
    
    //获取用户主线任务key
    private function get_main_target_key_by_uid_bak($uid, $targetType){
        return "uid:$uid:main:$targetType:bak";
    }
    //获取用户主线目标任务key
    private function get_main_target_key_by_uid($uid, $targetType){
        return "uid:$uid:main:$targetType";
    }
    
    //获取主播开启目标任务key
	private function get_singer_target_key_by_uid($uid, $targetType, $giftid){
        $date = date("Y-m-d");
        $key = "singeruid:$uid:$date:$targetType";
        //收礼任务比较特殊，会根据礼物id不同而生成不同的任务
        if(7 == $targetType){
        	$key = "singeruid:$uid:$date:$targetType:$giftid";
        }
         
        return $key;
    }
    
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
        $flag = $this->is2day();
        if(empty($flag) || !$flag){
            $date = date("Y-m-d",strtotime("-1 day"));
        }else{
            $date = date("Y-m-d");
        }
    
        return $date;
    }
    
    //获得主播开启任务
    public function getSingerOpenTask($singerid){
        LogApi::logProcess("getSingerOpenTask::***********singerid:$singerid");
        
        $result = array();
        
        $date = $this->getCurDate();//date("Y-m-d");
        $key = "taskstart::uid:$singerid:$date";
        $tid = $this->getRedisMaster()->get($key);
        
        if(empty($tid)){
            return $result;
        }
        
        $result['taskid'] = $tid;
        
        $key = "singeruid:$singerid:$date:tid:$tid";
         
        $data = $this->getRedisMaster()->get($key);
        
        if(empty($data)){
            $key = "singerlast_task:uid:$singerid:$date:tid:$tid";
            $data = $this->getRedisMaster()->get($key);
        }
        
        if(!empty($data)){
            $data = json_decode($data, TRUE);
            $result['finish_num'] = isset($data['t_finish_progress']) ? $data['t_finish_progress'] : 0;
            $result['total_num'] = isset($data['t_total_progress'])? $data['t_total_progress']: 0;
            $result['tool_id'] = isset($data['t_attach_param']) ? $data['t_attach_param'] : 0;
            
            $result['tool_name'] = "";
            $result['tool_icon'] = "";
            if(!empty($result['tool_id'])){
                $toolModel = new ToolModel();
                $tool = $toolModel->getToolByTid($result['tool_id']);
                
                $result['tool_name'] = $tool['name'];
                $result['tool_icon'] = $tool['icon'];
            }
        }
        
        return $result;
    }
    
    /* public function getdate(){
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
    } */
    
    //初始化粉丝团任务
    public function initFollowerTasks($singerid, $uid){
        LogApi::logProcess("initFollowerTasks::***********uid:$uid:singerid:$singerid");
        $date = $this->getCurDate();//date("Y-m-d");
        $key = "follower:uid:$uid:singerid:$singerid:$date";
        $value = $this->getRedisMaster()->get($key);
        
        LogApi::logProcess("value:$value");
        
        if(empty($value)){
//             $date = date("Y-m-d");
            $now = time();
            
            $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_open_object, t_total_progress, create_time, update_time, singerid)" .
                " SELECT $uid, id, task_type, open_object, target_params1, '$date', $now, $singerid FROM card.task_conf WHERE open_object = 0 and task_type = 10";
            
            $flag = $this->getDbChannellive()->query($sql);
            
            LogApi::logProcess("flag:$flag, sql:$sql");
            
            if($flag){
                $this->getRedisMaster()->set($key, $date);
            
                $sql ="select t.id, t.uid, t.t_total_progress, t.t_status, tc.open_type, tc.tool_id, tc.tool_num, tc.target_type, tc.target_params2, tc.intimacy_exp from card.task_info t
                left join card.task_conf tc on t.t_id = tc.id where t.uid = $uid and t.singerid = $singerid and t.t_type = 10 and t.t_open_object = 0 and t.create_time = '$date'";
                //TODO:根据类型初始化任务catch
                $rows = $this->getDbChannellive()->query($sql);
                while($row = $rows->fetch_assoc()){
                    $id = (int)$row['id'];
                    $totalNum = (int)$row['t_total_progress'];
                    $openType = (int)$row['open_type'];
                    //$tool_id = $row['tool_id'];
                    //$tool_num = $row['tool_num'];
                    $targetType = (int)$row['target_type'];
                    //$attachParam = (int)$row['target_params2'];
                    $status = (int)$row['t_status'];
                    $intimacy = (int)$row['intimacy_exp'];
                    $this->initFollowerCatch($id, $uid, $singerid, $targetType, $totalNum, $openType, $status, $intimacy);
                }
            }
        }
    }
    
    //初始化用户每日任务缓存
    public function initFollowerCatch($id, $uid, $singerid, $targetType, $totalNum, $openType, $status, $intimacy){
		LogApi::logProcess("initFollowerCatch******");
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
    	$this->getRedisMaster()->set($key, json_encode($data));
    	
    	LogApi::logProcess("initFollowerCatch******data:".json_encode($data));
    	
    	//保存上面的key
    	$h_key = "follower:uid:$uid:singerid:$singerid:$date:$targetType";
    	$this->getRedisMaster()->hset($h_key, $key, $key);
    	LogApi::logProcess("end initFollowerCatch******h_key:$h_key");
    }

    //主播直播任务
    public function startShowTask($uid){
        $key = "uid:$uid:3:bak";
    
        $now = strtotime("now");
        $data = array();
        $data['uid'] = $uid;
        //当天观看时间
        $data['remain_time'] = 0;
        $data['minute_update_time'] = $now;
        $data['update_time'] = $now;
        $this->getRedisMaster()->set($key, json_encode($data));
    }
    
    //统计开播时长
    public function statShowTimeTask($uid){
        $now = strtotime("now");
        $key = "uid:$uid:3:bak";
        $value = $this->getRedisMaster()->get($key);
    
        $data = json_decode($value, TRUE);
        if(empty($data) 
            || !isset($data['remain_time'])
            || !isset($data['update_time'])
            || !isset($data['minute_update_time'])
            ){
            return false;
        }
    
        $data['remain_time'] += $now - $data['update_time'];
        $data['update_time'] = $now;
    
    
        if($now - $data['minute_update_time'] >= 60){
    
            $data['minute_update_time'] = $now;
            $this->getRedisMaster()->set($key, json_encode($data));
            
            return array(
            	'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 3,//3为开播时长
                    'num' => 1,
                    'extra_param' => 0
                )
            );
        }else{
            return false;
        }
    }
    
    //开启用户每日观看任务
    public function startWatchTask($uid){        
        $key = "uid:$uid:2:bak";
        
        $now = strtotime("now");
        $data = array();
        $data['uid'] = $uid;
        //当天观看时间
        $data['remain_time'] = 0;
        $data['minute_update_time'] = $now;
        $data['update_time'] = $now;
        $this->getRedisMaster()->set($key, json_encode($data));
    }
    
    //统计开播时长
    public function statWatchTask($singerid, $uid){
        $now = strtotime("now");
        $key = "uid:$uid:2:bak";
        $value = $this->getRedisMaster()->get($key);
    
        $data = json_decode($value, TRUE);
    
        $data['remain_time'] += $now - $data['update_time'];
        $data['update_time'] = $now;
    
        $return = array();
        if($now - $data['minute_update_time'] >= 60){
    
            $data['minute_update_time'] = $now;
            $this->getRedisMaster()->set($key, json_encode($data));
            
            $return[] = array(
            	'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 2,//2为观看时长
                    'num' => 1,
                    'extra_param' => 0
                )
            );
            //用于粉丝团任务type=10
            /* $return[] = array(
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 2,//2为观看时长
                    'num' => 1,
                    'extra_param' => $singerid
                )
            ); */
            
            return $return;
        }else{
            return false;
        }
    }
    
    /********************************以下不用****************************/
    
    //统计主播开启任务
    public function statSingerOpenTask($uid, $giftid, $num){
        LogApi::logProcess("TaskModel::statSingerStartTask begin************");
        $key = $this->get_singer_target_key_by_uid($uid, TaskModel::TARGET_TYPE_RECV_GIFT, $giftid);
        $value = $this->getRedisMaster()->get($key);
        
        //如果该用户没有该任务，则不执行
        if(empty($value)){
            LogApi::logProcess("TaskModel::statSingerStartTask 没有该主播开启任务************key:$key");
            return;
        }
        
        LogApi::logProcess("TaskModel::statSingerStartTask************ key:$key, value:$value");
        
        $data = json_decode($value, TRUE);
        
        //任务还没开启
        if($data['status'] != 3){
            LogApi::logProcess("TaskModel::statSingerStartTask 该主播任务还没有开启************key:$key");
            return;
        }
        
        //任务已完成
        if($data['status'] == 1){
            LogApi::logProcess("TaskModel::statSingerStartTask 该主播开启任务已完成************key:$key");
            return;
        }

        if(($num >= $data['t_total_progress']) ||
        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
            $data['t_finish_progress'] = $data['t_total_progress'];
        }else{
            $data['t_finish_progress']+= $num;
        }
            
        if($data['t_finish_progress'] >= $data['t_total_progress']){
            $date = date("Y-m-d");
            
            $sql = "update card.task_info t set t_status=1, t_finish_progress = ".$data['t_finish_progress'].
            " where t.id = ".$data['id'];
            $rows = $this->getDbChannellive()->query($sql);
            if($rows){
                $data['status'] = 1;
                $this->getRedisMaster()->set($key, json_encode($data));
            }else {
                LogApi::logProcess("TaskModel::statSingerStartTask*****exe sql error!**sql:$sql");
            }
            return;
        }
        
        $this->getRedisMaster()->set($key, json_encode($data));
        LogApi::logProcess("TaskModel::statSingerStartTask end*****data:".json_encode($data));
    }
    
    //统计主播在线人数主线任务,进入房间num=1, 退出房间num =-1
    public function statOnlineMansMainTask($uid, $num){
        $key = $this->get_main_target_key_by_uid($uid, TaskModel::TARGET_TYPE_ROOM_MANS);
        $value = $this->getRedisMaster()->get($key);
        
        //如果该用户没有该任务，则不执行
        if(empty($value)){
            return;
        }
        
        $data = json_decode($value, TRUE);
        
        if($data['status'] == 1){
            return;
        }

        $data['t_finish_progress']+= $num;
        
        if($data['t_finish_progress'] >= $data['t_total_progress']){
            $date = date("Y-m-d");
            
            $sql = "update card.task_info t set t_status=1, t_finish_progress = ".$data['t_finish_progress'].
            " where t.id = ".$data['id'];
            $rows = $this->getDbChannellive()->query($sql);
            if($rows){
                $data['status'] = 1;
                $this->getRedisMaster()->set($key, json_encode($data));
                //把随后任务插入到任务信息表中
                $this->updateNextMainTask($uid, $data['follow_task_id']);
                /*
                $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_total_progress, create_time)" .
                    " SELECT $uid, id, task_type, target_params1, '$date' FROM card.task_conf WHERE id =".$data['follow_task_id'];
                $rows = getDbChannellive()->query($sql);
                if(!$rows){
                    LogApi::logProcess("TaskModel::statShowTimeMainTask*****exe sql error!**sql:$sql");
                }
                
                $sql ="select t.id, t.uid, t.t_total_progress, t.t_status, tc.target_type, tc.target_params2, tc.follow_task_id from card.task_info t
                left join card.task_conf tc on t.t_id = tc.id  where t.uid = $uid and t.t_type = 0 and t.create_time = '$date' and t.t_id=".$data['follow_task_id'];
                //TODO:根据类型初始化任务catch
                $rows = getDbChannellive()->query($sql);
                if ($rows && $rows->num_rows > 0) {
                    $row = $rows->fetch_assoc();
                    $id = $row['id'];
                    $totalNum = $row['t_total_progress'];
                    $targetType = $row['target_type'];
                    $attachParam = $row['target_params2'];
                    $followTaskid = $row['follow_task_id'];
                    $status = $row['t_status'];
                    $this->initMainCatch($id, $uid, $targetType, $totalNum, $attachParam, $followTaskid, $status);
                }*/
            }else{
                LogApi::logProcess("TaskModel::statRecvGiftMainTask*****exe sql error!**sql:$sql");
            }
            
            return;
        }
        
        $this->getRedisMaster()->set($key, json_encode($data));
    }
    
    //用户首次送礼主线任务
    public function statUserSendGiftMainTask($uid, $num){
        LogApi::logProcess("TaskModel::statUserSendGiftMainTask begin************");
        
        $key = $this->get_main_target_key_by_uid($uid, TaskModel::TARGET_TYPE_SEND_GIFT);
        $value = $this->getRedisMaster()->get($key);
        
        //如果该用户没有该任务，则不执行
        if(empty($value)){
            LogApi::logProcess("TaskModel::statUserSendGiftMainTask 没有该用户每日送礼主线任务************key:$key");
            return;
        }
        
        $data = json_decode($value, TRUE);
        
        if($data['status'] == 1){
            return;
        }

        if(($num >= $data['t_total_progress']) ||
            ($data['t_finish_progress']+$num>= $data['t_total_progress'])){
            $data['t_finish_progress'] = $data['t_total_progress'];
        }else{
            $data['t_finish_progress']+= $num;
        }
        
        if($data['t_finish_progress'] >= $data['t_total_progress']){
            $date = date("Y-m-d");
        
            $sql = "update card.task_info t set t_status=1, t_finish_progress = ".$data['t_finish_progress'].
            " where t.id = ".$data['id'];
            $rows = $this->getDbChannellive()->query($sql);
            if($rows){
                $data['status'] = 1;
                $this->getRedisMaster()->set($key, json_encode($data));
                //把随后任务插入到任务信息表中
                $this->updateNextMainTask($uid, $data['follow_task_id']);
            }else{
                LogApi::logProcess("TaskModel::statUserSendGiftMainTask*****exe sql error!**sql:$sql");
            }
        
            return;
        }
        
        $this->getRedisMaster()->set($key, json_encode($data));
        LogApi::logProcess("TaskModel::statUserSendGiftMainTask end************");
    }
    
    //用户首次发声主线任务
    public function statUserTextMainTask($uid){
        LogApi::logProcess("TaskModel::statUserTextMainTask begin************uid:$uid");
        
        $key = $this->get_main_target_key_by_uid($uid, TaskModel::TARGET_TYPE_ROOM_TEXT);
        $value = $this->getRedisMaster()->get($key);
        
        //如果该用户没有该任务，则不执行
        if(empty($value)){
            LogApi::logProcess("TaskModel::statUserTextMainTask 没有该用户每日发言主线任务************key:$key");
            return;
        }
        
        $data = json_decode($value, TRUE);
        
        if($data['status'] == 1){
            return;
        }

        $data['t_finish_progress']+= 1;
        
        LogApi::logProcess("TaskModel::statUserTextMainTask ************key:$key, data:".json_encode($data));
        
        if($data['t_finish_progress'] >= $data['t_total_progress']){
            $date = date("Y-m-d");
        
            $sql = "update card.task_info t set t_status=1, t_finish_progress = ".$data['t_finish_progress'].
            " where t.id = ".$data['id'];
            $rows = $this->getDbChannellive()->query($sql);
            if($rows){
                $data['status'] = 1;
                $this->getRedisMaster()->set($key, json_encode($data));
                //把随后任务插入到任务信息表中
                $this->updateNextMainTask($uid, $data['follow_task_id']);
            }else{
                LogApi::logProcess("TaskModel::statUserTextMainTask*****exe sql error!**sql:$sql");
            }
        
            return;
        }
        
        $this->getRedisMaster()->set($key, json_encode($data));
        LogApi::logProcess("TaskModel::statUserTextMainTask end************");
    }
    
    //统计主播收礼主线任务
    public function statSingerRecvGiftMainTask($uid, $giftid, $num){
        LogApi::logProcess("TaskModel::statRecvGiftMainTask begin************");
        
        $key = $this->get_main_target_key_by_uid($uid, TaskModel::TARGET_TYPE_RECV_GIFT);
        $value = $this->getRedisMaster()->get($key);
        
        //如果该用户没有该任务，则不执行
        if(empty($value)){
            LogApi::logProcess("TaskModel::statRecvGiftMainTask 没有该主播每日收礼主线任务************key:$key");
            return;
        }
        
        $data = json_decode($value, TRUE);
        
        if($data['status'] == 1){
            return;
        }

        if(($num >= $data['t_total_progress']) ||
        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
            $data['t_finish_progress'] = $data['t_total_progress'];
        }else{
            $data['t_finish_progress']+= $num;
        }
            
        if($data['t_finish_progress'] >= $data['t_total_progress']){
            $date = date("Y-m-d");
            
            $sql = "update card.task_info t set t_status=1, t_finish_progress = ".$data['t_finish_progress'].
            " where t.id = ".$data['id'];
            $rows = $this->getDbChannellive()->query($sql);
            if($rows){
                $data['status'] = 1;
                $this->getRedisMaster()->set($key, json_encode($data));
                //把随后任务插入到任务信息表中
                $this->updateNextMainTask($uid, $data['follow_task_id']);
                
            }else{
                LogApi::logProcess("TaskModel::statRecvGiftMainTask*****exe sql error!**sql:$sql");
            }
            
            return;
        }
        
        $this->getRedisMaster()->set($key, json_encode($data));
        LogApi::logProcess("TaskModel::statRecvGiftMainTask end************");
    }
    
    //更新下一个主线任务
    public function updateNextMainTask($uid, $nextTaskId){
        $date = date("Y-m-d");
        //把随后任务插入到任务信息表中
        $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_total_progress, create_time)" .
            " SELECT $uid, id, task_type, target_params1, '$date' FROM card.task_conf WHERE id =$nextTaskId";
        $rows = $this->getDbChannellive()->query($sql);
        if(!$rows){
            LogApi::logProcess("TaskModel::updateNextMainTask*****exe sql error!**sql:$sql");
            return false;
        }
        
        $sql ="select t.id, t.uid, t.t_total_progress, t.t_status, tc.target_type, tc.target_params2, tc.follow_task_id from card.task_info t
        left join card.task_conf tc on t.t_id = tc.id  where t.uid = $uid and t.t_type = 0 and t.create_time = '$date' and t.t_id=$nextTaskId";
        //TODO:根据类型初始化任务catch
        $rows = $this->getDbChannellive()->query($sql);
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $id = $row['id'];
            $totalNum = $row['t_total_progress'];
            $targetType = $row['target_type'];
            $attachParam = $row['target_params2'];
            $followTaskid = $row['follow_task_id'];
            $status = $row['t_status'];
            
            LogApi::logProcess("TaskModel::updateNextMainTask*****data:".json_encode($row));
            
            $this->initMainCatch($id, $uid, $targetType, $totalNum, $attachParam, $followTaskid, $status);
        }
        
        LogApi::logProcess("TaskModel::updateNextMainTask*****result:$rows, sql:$sql");
        
        return true;
    }
    
    public function startShowMainTask($uid){
        $key = $this->get_main_target_key_by_uid($uid, TaskModel::TARGET_TYPE_SHOW_TIME);
        $value = $this->getRedisMaster()->get($key);
        
        //如果该用户没有该任务，则不执行
        if(empty($value)){
            return;
        }
        
        $key = $this->get_main_target_key_by_uid_bak($uid, TaskModel::TARGET_TYPE_SHOW_TIME);
        
        $now = strtotime("now");
        $data = array();
        $data['uid'] = $uid;
        //当天观看时间
        $data['remain_time'] = 0;
        $data['minute_update_time'] = $now;
        $data['update_time'] = $now;
        $this->getRedisMaster()->set($key, json_encode($data));
    }
    
    //统计主播直播时长主线任务
    public function statShowTimeMainTask($uid){
        $key = $this->get_main_target_key_by_uid($uid, TaskModel::TARGET_TYPE_SHOW_TIME);
        $value = $this->getRedisMaster()->get($key);
        
        if(empty($value)){
            return;
        }
        
        $now = strtotime("now");
        
        $data = json_decode($value, TRUE);
        
        $key_bak = $this->get_main_target_key_by_uid_bak($uid, TaskModel::TARGET_TYPE_SHOW_TIME);
        $value = $this->getRedisMaster()->get($key_bak);
        
        if(empty($value)){
            return;
        }
        
        $data_bak = json_decode($value, TRUE);
        
        $data_bak['remain_time'] += $now - $data_bak['update_time'];
        $data_bak['update_time'] = $now;
        
        if($data['status'] == 1){
            return;
        }
        
        if($now - $data_bak['minute_update_time'] >= 60){
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = date("Y-m-d");
                
                $sql = "update card.task_info t set t_status=1, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->getDbChannellive()->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->getRedisMaster()->set($key, json_encode($data));
                    //把随后任务插入到任务信息表中
                    $this->updateNextMainTask($uid, $data['follow_task_id']);
                    
                }else{
                    LogApi::logProcess("TaskModel::statShowTimeMainTask*****exe sql error!**sql:$sql");
                }
                
                return;
            }
            $data['t_finish_progress'] += 1;
            $data_bak['minute_update_time'] = $now;
            
            $this->getRedisMaster()->set($key, json_encode($data));
            
        }
        $this->getRedisMaster()->set($key_bak, json_encode($data_bak)); 
    }
    
    //初始化用户主线任务缓存
    public function initMainCatch($id, $uid, $targetType, $totalNum, $attachParam, $followTaskid, $status){
        LogApi::logProcess("begin TaskModel::initMainCatch**********");
        $key = $this->get_main_target_key_by_uid($uid, $targetType);
    	
    	$data = array();
        $data['id'] = $id;
        $data['uid'] = $uid;
        $data['target_type'] = (int)$targetType;
        $data['t_total_progress'] = (int)$totalNum;
        $data['t_finish_progress'] = 0;
        LogApi::logProcess("TaskModel::initMainCatch 1**********attachParam:$attachParam");
        if(!empty($attachParam)){
            $data['t_attach_param'] = (int)$attachParam;
        }
        LogApi::logProcess("TaskModel::initMainCatch 2**********followTaskid:$followTaskid");
        if(!empty($followTaskid)){
            $data['follow_task_id'] = (int)$followTaskid;
        }
        LogApi::logProcess("TaskModel::initMainCatch 3**********status:$status");
        $data['status'] = (int)$status;
    	$this->getRedisMaster()->set($key, json_encode($data));
    	LogApi::logProcess("end TaskModel::initMainCatch*****data:".json_encode($data));
    }
    
    //开始用户每日观看直播主线任务
    public function startWatchMainTask($uid){
        $key = $this->get_main_target_key_by_uid($uid, TaskModel::TARGET_TYPE_WATCH_TIME);
        $value = $this->getRedisMaster()->get($key);
        
        //如果该用户没有该任务，则不执行
        if(empty($value)){
            return;
        }
        
        $key = $this->get_main_target_key_by_uid_bak($uid, TaskModel::TARGET_TYPE_WATCH_TIME);
        
        $now = strtotime("now");
        $data = array();
        $data['uid'] = $uid;
        //当天观看时间
        $data['remain_time'] = 0;
        $data['minute_update_time'] = $now;
        $data['update_time'] = $now;
        $this->getRedisMaster()->set($key, json_encode($data));
    }
    
    //统计用户观看时长每日主线任务
    public function statWatchTimeMainTask($uid){
        $key = $this->get_main_target_key_by_uid($uid, TaskModel::TARGET_TYPE_WATCH_TIME);
        $value = $this->getRedisMaster()->get($key);
        
        if(empty($value)){
            return;
        }
        
        //LogApi::logProcess("TaskModel::statWatchTimeMainTask*****key:$key, value:$value");
        
        $now = strtotime("now");
        
        $data = json_decode($value, TRUE);
        
        $key_bak = $this->get_main_target_key_by_uid_bak($uid, TaskModel::TARGET_TYPE_WATCH_TIME);
        $value = $this->getRedisMaster()->get($key_bak);
        
        //LogApi::logProcess("TaskModel::statWatchTimeMainTask*****key:$key, value_bak:$value");
        
        if(empty($value)){
            return;
        }
        
        $data_bak = json_decode($value, TRUE);
        
        $data_bak['remain_time'] += $now - $data_bak['update_time'];
        $data_bak['update_time'] = $now;
        
        if($data['status'] == 1){
            return;
        }
        
        if($now - $data_bak['minute_update_time'] >= 60){
            $data['t_finish_progress'] += 1;
            $data_bak['minute_update_time'] = $now;
                
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = date("Y-m-d");
                
                $sql = "update card.task_info t set t_status=1, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->getDbChannellive()->query($sql);
                LogApi::logProcess("TaskModel::statWatchTimeMainTask*****sql:$sql");
                if($rows){
                    $data['status'] = 1;
                    $this->getRedisMaster()->set($key, json_encode($data));
                    
                    //把随后任务插入到任务信息表中
                    $this->updateNextMainTask($uid, $data['follow_task_id']);
                    /*
                    $sql = "INSERT INTO card.task_info(uid, t_id, t_type, t_total_progress, create_time)" .
                        " SELECT $uid, id, task_type, target_params1, '$date' FROM card.task_conf WHERE id =".$data['follow_task_id'];
                    $rows = getDbChannellive()->query($sql);
                    if(!$rows){
                        LogApi::logProcess("TaskModel::statWatchTimeMainTask*****exe sql error!**sql:$sql");
                    }
                    
                    $sql ="select t.id, t.uid, t.t_total_progress, t.t_status, tc.target_type, tc.target_params2, tc.follow_task_id from card.task_info t
                    left join card.task_conf tc on t.t_id = tc.id  where t.uid = $uid and t.t_type = 0 and t.create_time = '$date' and t.t_id=".$data['follow_task_id'];
                    //TODO:根据类型初始化任务catch
                    $rows = getDbChannellive()->query($sql);
                    if ($rows && $rows->num_rows > 0) {
                        $row = $rows->fetch_assoc();
                        $id = $row['id'];
                        $totalNum = $row['t_total_progress'];
                        $targetType = $row['target_type'];
                        $attachParam = $row['target_params2'];
                        $followTaskid = $row['follow_task_id'];
                        $status = $row['t_status'];
                        $this->initMainCatch($id, $uid, $targetType, $totalNum, $attachParam, $followTaskid, $status);
                    }*/
                }else{
                    LogApi::logProcess("TaskModel::statWatchTimeMainTask*****exe sql error!**sql:$sql");
                }
                return;
            }
            
            $this->getRedisMaster()->set($key, json_encode($data));
        }
        $this->getRedisMaster()->set($key_bak, json_encode($data_bak));
            
    }
    
    //开启主播每日收礼任务
    public function statRecvGiftDayTask($uid, $giftid, $num){
        LogApi::logProcess("TaskModel::statRecvGiftDayTask begin************");
        $key = $this->get_day_target_key_by_uid_gift($uid, TaskModel::TARGET_TYPE_RECV_GIFT, $giftid);
        $value = $this->getRedisMaster()->get($key);
        
        //如果该用户没有该任务，则不执行
        if(empty($value)){
            LogApi::logProcess("TaskModel::statRecvGiftDayTask 没有该主播每日收礼任务************key:$key");
            return;
        }
        
        $data = json_decode($value, TRUE);
        /*
        if($data['open_type'] == 1 && $data['tool_id'] != $giftid){
            return ;
        }
        
        $key = $this->get_day_target_key_by_uid_gift_bak($uid, TaskModel::TARGET_TYPE_RECV_GIFT, $giftid);
        $value = getRedisMaster()->get($key);
        if(empty($value)){
            $data_bak = array();
            $data_bak[''] = 
        }*/
        
        if($data['status'] == 1){
            return;
        }

        if(($num >= $data['t_total_progress']) ||
        ($data['t_finish_progress']+$num >= $data['t_total_progress'])) {
            $data['t_finish_progress'] = $data['t_total_progress'];
        }else{
            $data['t_finish_progress']+= $num;
        }
        
        if($data['t_finish_progress'] >= $data['t_total_progress']){
            $date = date("Y-m-d");
            
            $sql = "update card.task_info t set t_status=1, t_finish_progress = ".$data['t_finish_progress'].
            " where t.id = ".$data['id'];
            $rows = $this->getDbChannellive()->query($sql);
            if($rows){
                $data['status'] = 1;
                $this->getRedisMaster()->set($key, json_encode($data));
            }else{
                LogApi::logProcess("TaskModel::statWatchTimeMainTask*****exe sql error!**sql:$sql");
            }
            return;
        }
        
        $this->getRedisMaster()->set($key, json_encode($data));
        LogApi::logProcess("TaskModel::statRecvGiftDayTask end************");
    }
    
    //开启用户每日观看任务
    public function startWatchDayTask($uid){
        $key = $this->get_day_target_key_by_uid($uid, TaskModel::TARGET_TYPE_WATCH_TIME);
        $value = $this->getRedisMaster()->get($key);
        
        //如果该用户没有该任务，则不执行
        if(empty($value)){
            return;
        }
        
        $key = $this->get_day_target_key_by_uid_bak($uid, TaskModel::TARGET_TYPE_WATCH_TIME);
        
        $now = strtotime("now");
        $data = array();
        $data['uid'] = $uid;
        //当天观看时间
        $data['remain_time'] = 0;
        $data['minute_update_time'] = $now;
        $data['update_time'] = $now;
        $this->getRedisMaster()->set($key, json_encode($data));
    }
    
    //统计用户观看时长每日任务
    public function statWatchTimeDayTask($uid){
        $key = $this->get_day_target_key_by_uid($uid, TaskModel::TARGET_TYPE_WATCH_TIME);
        $value = $this->getRedisMaster()->get($key);
        
        if(empty($value)){
            return;
        }
        
        $now = strtotime("now");
        
        $data = json_decode($value, TRUE);
        
        $key_bak = $this->get_day_target_key_by_uid_bak($uid, TaskModel::TARGET_TYPE_WATCH_TIME);
        $value = $this->getRedisMaster()->get($key_bak);
        
        if(empty($value)){
            return;
        }
        
        $data_bak = json_decode($value, TRUE);
        
        $data_bak['remain_time'] += $now - $data_bak['update_time'];
        $data_bak['update_time'] = $now;
        
        if($data['status'] == 1){
            return;
        }
        if($now - $data_bak['minute_update_time'] >= 60){
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = date("Y-m-d");
                
                $sql = "update card.task_info t set t_status = 1, t_finish_progress = ".$data['t_finish_progress'].
                " where t.id = ".$data['id'];
                $rows = $this->getDbChannellive()->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->getRedisMaster()->set($key, json_encode($data));
                }else{
                    LogApi::logProcess("TaskModel::statWatchTimeDayTask*****exe sql error!**sql:$sql");
                }
                
                return;
            }
            $data['t_finish_progress'] += 1;
            $data_bak['minute_update_time'] = $now;
            
            $this->getRedisMaster()->set($key, json_encode($data));
            
            /*
            $date = date("Y-m-d");
            
            $sql = "update card.task_info set t_finish_progress = ".$data['t_finish_progress'].
            " where uid = $uid and t_type = 2 and create_time = $date";
            $rows = getDbChannellive()->query($sql);
            if(!$rows){
                LogApi::logProcess("TaskModel::statDayTask*****exe sql error!**sql:$sql");
            }*/
        }
        $this->getRedisMaster()->set($key_bak, json_encode($data_bak));
            
    }

    //开启主播每日直播任务
    public function startShowDayTask($uid){
        $key = $this->get_day_target_key_by_uid($uid, TaskModel::TARGET_TYPE_SHOW_TIME);
        $value = $this->getRedisMaster()->get($key);
    
        //如果该用户没有该任务，则不执行
        if(empty($value)){
            return;
        }
    
        $key = $this->get_day_target_key_by_uid_bak($uid, TaskModel::TARGET_TYPE_SHOW_TIME);
    
        $now = strtotime("now");
        $data = array();
        $data['uid'] = $uid;
        //当天观看时间
        $data['remain_time'] = 0;
        $data['minute_update_time'] = $now;
        $data['update_time'] = $now;
        $this->getRedisMaster()->set($key, json_encode($data));
    }
    
    //统计开播时长
    public function statShowTimeDayTask($uid){
        $key = $this->get_day_target_key_by_uid($uid, TaskModel::TARGET_TYPE_SHOW_TIME);
        $value = $this->getRedisMaster()->get($key);
        
        if(empty($value)){
            return;
        }
        
        $now = strtotime("now");
        
        $data = json_decode($value, TRUE);
        
        $key_bak = $this->get_day_target_key_by_uid_bak($uid, TaskModel::TARGET_TYPE_SHOW_TIME);
        $value = $this->getRedisMaster()->get($key_bak);
        
        if(empty($value)){
            return;
        }
        
        $data_bak = json_decode($value, TRUE);
        
        $data_bak['remain_time'] += $now - $data_bak['update_time'];
        $data_bak['update_time'] = $now;
        
        
        if($now - $data_bak['minute_update_time'] >= 60){
            if($data['t_finish_progress'] >= $data['t_total_progress']){
                $date = date("Y-m-d");
                
                $sql = "update card.task_info set t_status = 1, t_finish_progress = ".$data['t_finish_progress'].
                " where id = ".$data['id'];
                $rows = $this->getDbChannellive()->query($sql);
                if($rows){
                    $data['status'] = 1;
                    $this->getRedisMaster()->set($key, json_encode($data));
                }else{
                    LogApi::logProcess("TaskModel::statShowTimeDayTask*****exe sql error!**sql:$sql");
                }
                return;
            }
            $data['t_finish_progress'] += 1;
            $data_bak['minute_update_time'] = $now;
        
            $this->getRedisMaster()->set($key, json_encode($data));
            
        }
        $this->getRedisMaster()->set($key_bak, json_encode($data_bak));
    }
    
    
    /******************************以下是旧的************************************/

    public function setStatusByUid ($uid, $field, $value)
    {
        $key = $this->getStatusKey($uid);
        return $this->getRedisMaster()->hSet($key, $field, $value);
    }

    public function getStatusByUid ($uid, $field)
    {
        $key = $this->getStatusKey($uid);
        return $this->getRedisMaster()->hGet($key, $field);
    }

    public function statusIncrease ($uid, $field, $value = 1)
    {
        $key = $this->getStatusKey($uid);
        return $this->getRedisMaster()->hIncrBy($key, $field, $value);
    }

    public function getStatusKey ($uid)
    {
        return 'task_status:' . date('Ymd') . ':' . $uid;
    }

    public function getTaskKey ()
    {
        return 'task_collect_gifts:' . date('Ymd');
    }

    public function createTaskList ()
    {
        $taskRedisKey = $this->getTaskKey();
        // 這個array裏面的禮物是比較便宜的禮物，不會給歌手帶來秀點分成，比較容易獲得
        $cheapGiftList = array(
            77,
            78,
            79,
            83,
            98,
            100
        );
        // 這個array裏面的禮物是只能通過商城買這個渠道獲得的10秀幣禮物
        $buyGiftList = array(
            14,
            17,
            20,
            24,
            35,
            36,
            37,
            46,
            47,
            48,
            49
        );
        // 這個array裏面的禮物只能通過砸蛋來獲得
        $eggGiftList = array(
            80,
            81,
            84,
            82,
            85
        );
        $taskSetting = array(
            array(
                'id' => 1,
                'target' => 2628,
                'reward' => 6688,
                'giftId' => $cheapGiftList[array_rand($cheapGiftList)]
            ),
            array(
                'id' => 2,
                'target' => 999,
                'reward' => 13140,
                'giftId' => $buyGiftList[array_rand($buyGiftList)]
            ),
            array(
                'id' => 3,
                'target' => 360,
                'reward' => 20800,
                'giftId' => $eggGiftList[array_rand($eggGiftList)]
            )
        );
        $this->getRedisMaster()->set($taskRedisKey, json_encode($taskSetting));
        // 返回
        return $this->getRedisMaster()->get($taskRedisKey);
    }

    public function getTasks ($taskId = 0)
    {
        $taskRedisKey = $this->getTaskKey();
        $taskList = $this->getRedisMaster()->get($taskRedisKey);
        if (empty($taskList)) {
            $taskList = $this->createTaskList();
        }
        $tasks = json_decode($taskList, true);
        if ($taskId > 0) {
            foreach ($tasks as $task) {
                if ($task['id'] == $taskId) {
                    return $task;
                }
            }
        }
        return $tasks;
    }

    public function getTaskInfo ($uid, $recUid)
    {
        $info = array();
        $tasks = $this->getTasks();
        foreach ($tasks as $task) {
            $taskId = $task['id'];
            $task['progress'] = $this->getStatusByUid($uid, 'progress_' . $taskId);
            $task['rewardStatus'] = $this->getStatusByUid($uid, 'reward_status_' . $taskId);
            $info[] = $task;
        }
        return $info;
    }

    public function getTaskIdByGift ($giftId)
    {
        $tasks = $this->getTasks();
        foreach ($tasks as $task) {
            if ($task['giftId'] == $giftId) {
                return $task['id'];
            }
        }
        return false;
    }

    public function updateTaskProcess ($uid, $recUid, $giftId, $giftQty)
    {
        $taskId = $this->getTaskIdByGift($giftId);
        if ($taskId) {
            $this->statusIncrease($recUid, 'progress_' . $taskId, $giftQty);
        }
    }

    public function sendTaskReward ($uid, $task)
    {
        $taskId = $task['id'];
        if ($this->getStatusByUid($uid, 'reward_status_' . $taskId)) {
            return 142; // 任務獎勵已經發放
        }
        if ($this->getStatusByUid($uid, 'progress_' . $taskId) >= $task['target']) {
            $userAttrModel = new UserAttributeModel();
            $rs = $userAttrModel->addExperienceByUid($uid, $task['reward']);
            $this->addRecord($uid, $taskId, $task['reward']);
            if ($rs) {
                $this->setStatusByUid($uid, 'reward_status_' . $taskId, 1);
            }
            return 0;
        } else {
            return 143; // 任務目標未完成，不能領取獎勵
        }
    }

    public function addRecord ($uid, $taskId, $reward)
    {
        $now = time();
        $query = "INSERT INTO `task_reward_record` (`record_time`, `uid`, `task_id`, `reward`)
        VALUE ($now, $uid, $taskId, $reward)";
        $this->pushToMessageQueue('rcec_record', $query);
    }
}
