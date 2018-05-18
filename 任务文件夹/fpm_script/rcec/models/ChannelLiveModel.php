<?php

class ChannelLiveModel extends ModelBase
{
    public static $LORD_USER_MOD_NUMBER = 1024;
    public static $LORD_COUNT_TTL = 259200;// 3 * 24 * 60 * 60
    public static $SUN_DROP_AWARD_TTL = 604800;// 7 * 24 * 60 * 60
    
    // 1.只计算主播等级小于20级的主播，20为表格配置，ID=113。
    public $new_point_lvl_limit = 20;//
    
    public static $SINGER_SUN_COUNT	= 300;//主播粉丝群阳光掉落基数
    public static $SINGER_SUN_INTERVAL = 30;//主播粉丝群阳光掉落间隔（ 分钟）
    public static $SINGER_SUN_TIMES_MAX = 6;//主播粉丝群阳光掉落数量
    
    //抑制期时间衰减系数表
    public $yzRatioList = array(
        array(
            'index' => 1,
            'ratio' => 1
        ),
        array(
            'index' => 2,
            'ratio' => 0.9
        ),
        array(
            'index' => 3,
            'ratio' => 0.8
        ),
        array(
            'index' => 4,
            'ratio' => 0.7
        ),
        array(
            'index' => 5,
            'ratio' => 0.6
        ),
        array(
            'index' => 6,
            'ratio' => 0.5
        ),
        array(
            'index' => 7,
            'ratio' => 0.4
        ),
        array(
            'index' => 8,
            'ratio' => 0.3
        ),
        array(
            'index' => 9,
            'ratio' => 0.2
        ),
        array(
            'index' => 10,
            'ratio' => 0.1
        ));
    
    public function __construct ()
    {
        parent::__construct();
    }
    public function InitConfigDB()
    {
        $id_1 = 113;
        // select id,parm1,parm2,parm3 from card.parameters_info where id >= 82 && id <= 90;
        $sql = "select id,parm1,parm2,parm3 from card.parameters_info where id = $id_1";
        $rows = $this->getDbMain()->query($sql);
        $db_array = array();
        if ( $rows )
        {
            if ( 0 < $rows->num_rows )
            {
                for ($x=0; $x<$rows->num_rows; $x++)
                {
                    $row = $rows->fetch_assoc();
                    // 0  1     2     3
                    // id,parm1,parm2,parm3
                    $db_array[$row['id']] = array('parm1'=>$row['parm1'],'parm2'=>$row['parm2'],'parm3'=>$row['parm3']);
                }
                if(isset($db_array['113'])){$this->new_point_lvl_limit = intval($db_array['113']['parm1']);}
    
                LogApi::logProcess("new_point_lvl_limit:".$this->new_point_lvl_limit);
            }
        }
        else
        {
            LogApi::logProcess("InitConfigDB::****************sql:$sql");
        }
        LogApi::logProcess("InitConfigDB::**************** db_array:".json_encode($db_array));
    }
    
    public function loadSingerSunConf()
    {
    	$sql = "SELECT * FROM card.parameters_info WHERE id in (192,193,194)";
    	$db_main = $this->getDbMain();
    	$rows = $db_main->query($sql);
    	$db_array = array();
    	if (!empty($rows) && $rows->num_rows > 0) {
    		
    		$row = $rows->fetch_assoc();
    		do {
    			$db_array[$row['id']] = $row['parm1'];
    			$row = $rows->fetch_assoc();
    		} while (!empty($row));
    	}
    	
    	if (isset($db_array['192'])) {ChannelLiveModel::$SINGER_SUN_COUNT = $db_array['192'];}
    	if (isset($db_array['193'])) {ChannelLiveModel::$SINGER_SUN_TIMES_MAX = $db_array['193'];}
    	if (isset($db_array['194'])) {ChannelLiveModel::$SINGER_SUN_INTERVAL = $db_array['194'];}
    }
    
	/**
     * 设置播放url
     */
    public function setPlayUrl($sid, $paramStr)
    {
        $key = "PlayUrl:" . $sid;
    	//file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "ChannelLiveModel::setPlayUrl paramStr: $paramStr\n", FILE_APPEND);
//LogApi::logProcess('**************key:' . $key.'************************setPlayUrl***********::' . $paramStr );
        return $this->getRedisMaster()->set($key, $paramStr);
    }
    /**
     * 获取播放url
     */
    public function getPlayUrl($sid)
    {
        $key = "PlayUrl:" . $sid;
        return $this->getRedisMaster()->get($key);
    }
    
    //是否超过了报名周星的时间段
    public function canEnrollWeekStar()
    {
        $query = "select t.parm3 from card.parameters_info t where t.id = 79";
        $rows = $this->getDbChannellive()->query($query);
        
        if ($row = $rows->fetch_assoc()) {
            $t = $row['parm3'];
            
        }
        
        return false;
    }
    
    //
    public function getSingerAnchorInfo($singerid)
    {
        //获取主播最新奖励等级
        $query = "select * from raidcall.anchor_info where flag = 1 and uid = $singerid";
        $rows = $this->getDbChannellive()->query($query);
        
        if ($row = $rows->fetch_assoc()) {
            return $row;
        }
        
        return false;
    }
    
    //判断用户是否为主播的帮会守护
    public function isUnionGuard($singerid, $uid)
    {
        LogApi::logProcess('isUnionGuard 1');
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        if(empty($user['union_id'])){
            return false;
        }
        LogApi::logProcess('isUnionGuard 2');
        $unionid = $user['union_id'];
        
        $sql = "select IFNULL(u.union_id,'') AS union_id,IFNULL(u.uid,'') as uid 
            from rcec_record.union_guard_anchor_record u where 
		u.createTime> FROM_UNIXTIME(UNIX_TIMESTAMP(SYSDATE())-7*24*60*60,'%Y-%m-%d %H:%i:%s') 
            and u.uid= $singerid and u.union_id= $unionid and u.flag=1";
        
        $rows = $this->getDbChannellive()->query($sql);
        
        if ($rows && $rows->num_rows > 0) {
            LogApi::logProcess('isUnionGuard 3');
            return true;
        }else {
            LogApi::logProcess('isUnionGuard 4');
            return false;
        }
    }
    
    //根据sid获得房间信息包括房间拥有者
    public function getSessionInfoBySingerid($singerid)
    {
        $key = "session_:".$singerid;
        $sql = "select * from raidcall.sess_info where owner = $singerid";
        $rows = $this->read($key, $sql, 0, 'dbRaidcall', false);
        $data = array();
        if (count($rows) == 1) {
            $data = $rows[0];
        }
        
        //LogApi::logProcess("getSessionInfoBySingerid::****************sql::$sql".json_encode($data));
        
        return $data;
    }
    
    //根据sid获得房间信息包括房间拥有者
    public function getSessionInfo($sid)
    {
        $key = "session:".$sid;
        $sql = "select * from raidcall.sess_info where sid = $sid";
        $rows = $this->read($key, $sql, 0, 'dbRaidcall', false);
        $data = array();
        if (count($rows) == 1) {
            $data = $rows[0];
        }
        
        //LogApi::logProcess("getSessionInfo::****************sql::$sql".json_encode($data));
        
        return $data;
    }
    
    //判断用户是否被禁言
    public function isDisableText($sid, $uid)
    {
        $key = "disable_text";
        $field = "disable_text:$sid:$uid";
        $value = $this->getRedisMaster()->hget($key, $field);
        
        if(empty($value)){
            return false;
        }
        
        return $value;
    }
    
    //主播离开房间时，清理房间状态
    public function clearRoomStatus($singerUid, $sid)
    {
        $key = "roomMicroStatus_".$sid;
        $this->getRedisMaster()->del($key);
        
        $key = "roomCameraStatus_".$sid;
        $this->getRedisMaster()->del($key);
        
        //清除掉退出直播间的主播
        $key = "onlineAnchorList";
        $this->getRedisMaster()->hdel($key, $singerUid);
    }
    
    //设置直播间麦克风是否开启-1:关闭 1：打开
    public function setMicroStatus($sid, $flag)
    {
        $key = "roomMicroStatus_".$sid;
        $result = $this->getRedisMaster()->set($key, $flag);
        $value = $this->getRedisMaster()->get($key);
        LogApi::logProcess("setMicroStatus::**sid:$sid***flag:$flag*******reslut:$result***value:$value*");
    }
    
    //获取直播间麦克风是否开启
    public function getRoomStatus($sid)
    {
        $flags = array();
        $microKey = "roomMicroStatus_".$sid;
        $cameraKey = "roomCameraStatus_".$sid;
        
        $microFlag = $this->getRedisMaster()->get($microKey);
        LogApi::logProcess("getRoomStatus::******microKey:$microKey**$microFlag***");
        if(false == $microFlag){
            $microFlag = 1;
        }
        
        $cameraFlag = $this->getRedisMaster()->get($cameraKey);
        LogApi::logProcess("getRoomStatus::*******cameraKey:$cameraKey***$cameraFlag**");
        if(false == $cameraFlag){
            $cameraFlag = 1;
        }

        $flags['microStatus'] = $microFlag;
        $flags['cameraStatus'] = $cameraFlag;
        
        LogApi::logProcess("getRoomStatus::******microKey:$microKey**$microFlag***cameraKey:$cameraKey***$cameraFlag**");
        
        return $flags;
    }
    
    //设置直播间摄像头是否开启-1:关闭 1：打开
    public function setCameraStatus($sid, $flag)
    {
        $key = "roomCameraStatus_".$sid;
        $result = $this->getRedisMaster()->set($key, $flag);
        $value = $this->getRedisMaster()->get($key);
        LogApi::logProcess("setCameraStatus::**sid:$sid***flag:$flag****reslut:$result***value:$value*");
    }
    
    //清理上次直播间在线人数信息
    public function clearUserCountCache($sid)
    {
        $allUserKey = "roomalluser_".$sid;        
        // $key = "roomuserlist_".$sid;
        $key = member_list::HashUserListInfoKey($sid);
        $this->getRedisMaster()->del($allUserKey);
        $this->getRedisMaster()->del($key);
        
        $num = $this->getRedisMaster()->sSize($allUserKey);
        
        LogApi::logProcess("清理完上次直播间在线人数信息clearUserCountCache::roompeakvalue:$num");
    }
    
    /* function LogFile(){
        $dir = "/data/raidcall/log/channellive/" . date("Y-m-d") . "/";
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir .  "channellive.log";
    }
    function Live($sid, $cid, $uid, $num, $time) {
    	file_put_contents($this->LogFile(), $sid . ":" . $cid . ":" . $uid . ":" . $time. ":" . $num . "\n", FILE_APPEND);
    } */
    
    //获得本场主播在线直播时长
    public function getLocalLiveInfo($sid, $uid)
    {
        $sql = "select num, start_time, time from channellive.live_notify where  sid=$sid and uid=$uid";
        
        $rows = $this->getDbChannellive()->query($sql);
        
        $data = array();
        $len = 0;
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $start = (int)$row['start_time'];
            $end = (int)$row['time'];
            $now = time();
            $end = ($end > $now) ? $end : $now;
            $len = $end - $start; 
            $data['time_len'] = $len;
            $data['user_count'] = (int)$row['num'];

            LogApi::logProcess("getLocalLiveTime::****************sql::$sql, start::$start, end::$end, len::$len");
        }else{
        	$sql = "select num, start_time, time from channellive.live_record where sid=$sid and uid=$uid order by id desc limit 1";
        	$rows = $this->getDbChannellive()->query($sql);
        	if ($rows && $rows->num_rows > 0) {
            	$row = $rows->fetch_assoc();
            	$start = (int)$row['start_time'];
            	$end = (int)$row['time'];
            	$len = $end - $start;
            	$data['time_len'] = $len;
            	$data['user_count'] = (int)$row['num'];
        	} else {
        		$data['time_len'] = 0;
        		$data['user_count'] = 0;
        	}
            LogApi::logProcess("getLocalLiveTime:: error!!!****************sql::$sql");
        }
        
        return $data;
    }
    
    //获得本场主播新增关注数
    public function getLocalFans($sid)
    {
        LogApi::logProcess("begin getLocalFans::****************sid::$sid");
        
        $sql = "select count(a.fid) as total from cms_manager.follow_user_record a 
        left join live_notify b on a.fid=b.uid where  b.sid=$sid and UNIX_TIMESTAMP(a.update_time) >= b.start_time and UNIX_TIMESTAMP(a.update_time) <= b.time";
        
        $rows = $this->getDbChannellive()->query($sql);
        
        LogApi::logProcess("getLocalFans::****************sql::$sql");
         
        $total = 0;
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $total = (int)$row['total'];
        }
        
        LogApi::logProcess("getLocalFans::****************fansCount::$total");
        
        return $total;
    }
    
    //更新直播间峰值人数
    public function updatePeakCountToDB($sid, $uid)
    {
        //在线观看人数峰值
//         $scoreKey = 'roompeakvalue:score';
//         $peakNum = getRedisMaster()->ZSCORE($scoreKey,$sid);
          $allUserKey = "roomalluser_".$sid;
          $peakNum = $this->getRedisMaster()->sSize($allUserKey);
        
        $sql = "UPDATE live_notify SET live_peak=$peakNum WHERE sid=$sid and uid=$uid";
        $rows = $this->getDbChannellive()->query($sql);
        
        LogApi::logProcess("更新直播间峰值人数updatePeakCount::****************sql:$sql");
    }
    
    //更新用户在线时长
    public function stopWatchRoom(&$return, $sid, $uid)
    {
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        if(1 == $user['is_robot']){
            return false;
        }
        // 维护直播间用户互动游戏完整
        $gulm = new GameUserLaunchModel();
        $gulm->UserLeave($sid, $uid);
        //该接口移动到 channel_api.php 用户离场 on_p_user_real_leave_channel_event
        //// 帮派夺旗事件触发
        //$ff_model = new flag_faction_model();
        //$ff_model->event_user_room_leave(&$return, $uid, $sid);
        
        LogApi::logProcess("用户$uid,离开直播间,stopWatchRoom************************************");
        //更新用户在直播间状态
        /* $key = "room_active_$sid";
        $field = "room_active:$uid"; */
        $key = member_list::UserWatchRoomTimeKey($uid);
        
        $value = $this->getRedisMaster()->get($key);

        if(empty($value)){
            return false;
        }
        
        LogApi::logProcess("stopWatchRoom::****key:$key*****value:$value**");
        
        $data = json_decode($value, TRUE);
        
        if($data['stop']){
            return false;
        }
        
        // 观看，阳光掉落时长异常处理
        $now = strtotime("now");
        $remain = $now - $data['update_time'];
        if ($remain >= 35) {
        	$remain = 30;
        }
        $data['remain_time'] += $remain;
        $data['sun_remain_time'] += $remain;
        $data['update_time'] = $now;
        $data['last_uptime_watch'] = $now;
        
        $data['stop'] = 1;
        
        $startTime = $data['start_time'];
        //$active = $data['active_value'];
        
        //清除本场缓存里的观看活跃值
        //$data['active_value'] = 0;
        
        $this->getRedisMaster()->set($key, json_encode($data));
        /*****************************************/
        /* $daybegin=strtotime(date("Ymd"));
        $dayend=$daybegin+86400; */
                
        $sql = "update channellive.user_live_notify set total_time_length=(total_time_length+$now-$startTime) where 
            start_time BETWEEN UNIX_TIMESTAMP(date_format(now(), '%Y-%m-%d 00:00:00'))
			AND UNIX_TIMESTAMP(date_format(now(), '%Y-%m-%d 23:59:59')) and sid = $sid and uid = $uid";
        $rows = $this->getDbChannellive()->query($sql);
        if (!$rows) {
            LogApi::logProcess("stopWatchRoom :: exe sql error, sql:$sql");
            return false;
        }
        
        $sql = "INSERT INTO live_user_length_record(sid, uid, start_time, time)
        VALUES($sid, $uid, $startTime, $now)";
        $rows = $this->getDbChannellive()->query($sql);
        if (!$rows) {
            LogApi::logProcess("stopWatchRoom :: exe sql error, sql:$sql");
            return false;
        }
        
        return true;
    }
    
    //获取用户还有多少阳光没有领取
    public function getNoRecvSunValue($uid)
    {
        $key = "user_sun_times_tmp:$uid";
        // $data_arr = $this->getRedisMaster()->lrange($key,0,$this->getRedisMaster()->llen($key));
        $data_arr = $this->getRedisMaster()->lrange($key,0,-1);
        
        return $data_arr;
    }
    
    //
    public function GetSingerRoomSunTask($singerid)
    {
        $date = date("Y-m-d");
        $key = "singer_day_sun_task:$date:$singerid";
        $value = $this->getRedisMaster()->get($key);
        $data = json_decode($value, TRUE);
        if(empty($data)){
            $data = array();
        }
        return $data;
    }
    
    //用户领取阳光
    public function GetSunValue($uid)
    {
        //保存的次数
        $key = "user_sun_times_tmp:$uid";
        // $data_arr = $this->getRedisMaster()->lrange($key,0,$this->getRedisMaster()->llen($key));
        $data_arr = $this->getRedisMaster()->lrange($key,0,-1);
        
        LogApi::logProcess("?????GetSunValue :: key:$key, lrange return:".json_encode($data_arr));
        //把阳光值加入到数据库中
        $value = 0;
        foreach ($data_arr as $data)
        {
            $l_obj = json_decode($data);
            LogApi::logProcess("?????GetSunValue :: data :$data");
            // {"sid":101015,"islord":1,"sunvalue":29}          
            if(
                !property_exists($l_obj,"sid") || 
                !property_exists($l_obj,"islord") || 
                !property_exists($l_obj,"sunvalue"))
            {
                // old version.
                $value += (int)$data;                
            }
            else 
            {
                // new version.
                $sid = (int)$l_obj->sid;
                $islord = (int)$l_obj->islord;
                $sunvalue = (int)$l_obj->sunvalue;
                
                $glory_jc = 0;
                if (property_exists($l_obj, "glory_jc")) {
                	$glory_jc = floatval($l_obj->glory_jc);
                }
                
                $lord_jc = 0;
                if (1 == $islord) {
                	$lord_jc = 1;
                }
                
                if ( 1 == $islord )
                {
                    $sunvalue_new = ChannelLiveModel::LordUserSunshuneValue($sunvalue);
                    $dt_value = $sunvalue_new - $sunvalue;
                    $sunvalue = $sunvalue_new;
                    // count data.
                    $key_light = ChannelLiveModel::ZsetCountLoadLightKey($sid);
                    $this->getRedisMaster()->zIncrBy($key_light,$dt_value,$uid);
                    $this->getRedisMaster()->expire($key_light,ChannelLiveModel::$LORD_COUNT_TTL);
                }
                
                $sunvalue_final = (int)$l_obj->sunvalue * (1 + $glory_jc + $lord_jc);
                $value += $sunvalue_final;            
            }
        }
        if (0 < $value)
        {
            $sql = "UPDATE rcec_main.user_attribute SET sun_num = sun_num + $value WHERE uid =$uid";
            $rows = $this->getDbChannellive()->query($sql);
            if (!$rows) {
                LogApi::logProcess("GetSunValue :: exe sql error, sql:$sql");
                return false;
            }
            $userkey = "user_attribute:{$uid}";
            $this->getRedisMaster()->del($userkey);
        }        
        $this->getRedisMaster()->del($key);       
        return $data_arr;
    }
    
    //获得阳光日产出值
    public function getSunBaseValue($uid)
    {   
        //TODO:从周哥处获得
        $key = "userCharm:$uid";
        $base = $this->getRedisMaster()->get($key);
        
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        
        $activeLevel = (int)$userAttr['active_level'];
        $query = "select t.id, t.parm3 from card.parameters_info t where t.id in (30, 31, 32, 33, 34, 35)";
        $rows = $this->getDbMain()->query($query);
        
        $a = 1;
        $b = 30;
        $c = 120;
        $x = 0;
        $y = 0.3;
        $z = 1;
        while ($row = $rows->fetch_assoc()) {
            $id = (int)$row['id'];
            $value = doubleval($row['parm3']);
            switch ($id)
            {
                case 30: //
                    $a = $value;
                    break;
                case 31: //
                    $b = $value;
                    break;
                case 32: //
                    $c = $value;
                    break;
                case 33: //
                    $x = $value;
                    break;
                case 34: //
                    $y = $value;
                    break;
                case 35: //
                    $z = $value;
                    break; 
            }
        }
        
        //获得阳光加成值
        $sunjc = 0;
        if($activeLevel > $b){
            $sunjc = $y+($activeLevel-$b)*($z - $y)/($c-$b);
        }else{
            $sunjc = ($activeLevel-$a)*($y-$x)/($b-$a)+$x;
        }
        
        $totalValue = $base*(1+$sunjc);
              
        return $totalValue;
    }
    
    //产生阳光
    public function CreateSunShine($uid,$sid)
    {
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        $identity = (int)$user['identity'];
        //主播不产生阳光
        if(2 == $identity){
            return array();
        }
        
        $date = date("Y-m-d");
        //保存的次数
        $sunTimesKey = "user_sun_times:$date:$uid";
        //用于领取阳光用
        $sunTimesKey_tmp = "user_sun_times_tmp:$uid";
        $times = $this->getRedisMaster()->llen($sunTimesKey);
        if(empty($times)){
            $times = 0;
        }
        //暂时注释掉
        if($times >= 8){
            LogApi::logProcess("***CreateSunShine error*****userid:$uid out 8 times.");
            return array();
        }
        
        /* $sunFloatKey = "user_sunfloat:$uid";
        $sunfloat = getRedisMaster()->get($sunFloatKey);
        if(empty($sunfloat)){
            $sunfloat = 0;
        } */
        //获得日产出量
        $baseSun = $this->getSunBaseValue($uid);
        
        $query = "select t.parm3 as ratio from card.parameters_info t where t.id = 48";
        $rows = $this->getDbMain()->query($query);
        if (!$rows) {
            LogApi::logProcess("***CreateSunShine error user($uid), sql:$query");
            return array();
        }
        $row = $rows->fetch_assoc();
        $ratio = floatval($row['ratio']);
        
        //$times从0开始
        $sunvalue = (0.125-3.5*$ratio+$times*$ratio)*$baseSun;//+floatval($sunfloat/100);
        
        $sunvalue = ceil($sunvalue);
                
        /* $sunfloat = number_format($sunvalue, 2);
        $sunfloat = end(explode('.', $sunfloat)); */
        
        //getRedisMaster()->set($sunFloatKey, $sunfloat);
        /* //用户阳光总值key
        $sunKey = "user_sunvalue:$uid";
        getRedisMaster()->zIncrBy($sunKey, $sunvalue, $uid); */
        
//         $query = "UPDATE rcec_main.user_attribute SET sun_num = sun_num + $sunvalue WHERE uid = $uid";
        
//         $rs1 = getDbMain()->query($query);
//         if (!$rs1) {
//             LogApi::logProcess("***CreateSunShine error*****user($uid), sql:$query");
//         }

        $glory_jc = 0;
        {
        	// 直播间阳光等级加成
        	$model_glory = new GloryModel();
        	$cur_room_glory_total = $model_glory->getRoomGloryTotal($sid);
        	$room_sunshine_inf = $model_glory->calcRoomSunShineLv($sid, $cur_room_glory_total);
        	$glory_jc = $room_sunshine_inf['user_sunshine_plus'];
        	$room_sunshine_inf = json_encode($room_sunshine_inf);
        	
        	LogApi::logProcess("CreateSunShine uid:$uid sid:$sid room_glory_inf:" . json_encode($room_sunshine_inf));
        }
        
        $redisKey = "user_attribute:{$uid}";
        $this->getRedisMaster()->del($redisKey);
        
        //每次保存的阳光数
        $this->getRedisMaster()->lpush($sunTimesKey, $sunvalue);
        if ($times == 0) {
            $this->getRedisMaster()->expire($sunTimesKey, 24*60*60);
        }

        //TODO:如果是擂主，则产生阳光翻倍（擂主需要在session 的 Channel::setBattleArenaWinner方法里设置擂主uid：_curBattleArenaWinnerUid）
        $lord_user_key = ChannelLiveModel::HashLordUserKey($sid);
        $lord_id = $this->getRedisMaster()->hGet($lord_user_key,$sid);
        if (empty($lord_id)){$lord_id = 0;}
        $islord = ( $uid == $lord_id && 0 != $sid ) ? 1 : 0;
        
        $lord_jc = 0;
        if (1 == $islord) {
        	$lord_jc = 1;
        }
        //TODO:夺旗成功后，则产生阳光基数50%
        {
            $user_faction_id = (int)$user['union_id'];
            $m = new flag_faction_model();            
            $flag_success_Plus=$m->on_get_flag_success_sunvalue_plus($sid,$uid,$user_faction_id);
            //LogApi::logProcess("flag_faction_model.on_get_flag_success_sunvalue_plus sid:$sid uid:$uid user_faction_id:$user_faction_id flag_success_Plus:$flag_success_Plus");
        }
        
        $sunvalue_finally = $sunvalue * (1 + $lord_jc + $glory_jc + $flag_success_Plus);
//         if (1 == $islord)
//         {
//            $sunvalue_finally = ChannelLiveModel::LordUserSunshuneValue($sunvalue);
//         }
        $l_obj = array
        (
            'sid'=>$sid,
            'islord'=>$islord,
            'sunvalue'=>$sunvalue,
        	'glory_jc' => $glory_jc
        );
        // $this->getRedisMaster()->lpush($sunTimesKey_tmp, $sunvalue);
        $this->getRedisMaster()->lpush($sunTimesKey_tmp, json_encode($l_obj));
        $this->getRedisMaster()->expire($sunTimesKey_tmp,ChannelLiveModel::$SUN_DROP_AWARD_TTL);
        
        LogApi::logProcess("CreateSunShine ".json_encode($l_obj));
        //向前端返回本次产生的阳光数
        return array(
        	'broadcast' => 1,
            'data' => array(
                'cmd' => 'BCreateSunshune',
                'uid' => (int)$uid,
                'sun_num' => $sunvalue_finally,
                'isRoom' => true
            )
        );
    }
    public static function LordUserSunshuneValue($value)
    {
        return $value * 2;
    }
    public static function HashLordUserKey($sid)
    {
	    $mod = $sid % ChannelLiveModel::$LORD_USER_MOD_NUMBER;
	    return "lord:room:$mod";
    }
    public static function ZsetCountLoadLightKey($sid)
    {
        return "lord:count:light:$sid";
    }
    //实时刷新时处理跨天情况
    public function deal2day($uid, $sid, $data, &$isTwoDay)
    {
        LogApi::logProcess("in deal2day, data:".json_encode($data));
        /* if(empty($uid)){
            LogApi::logProcess("deal2day :: uid is empty!!!");
            return false;
        }
        //更新用户在直播间状态
        $key = "room_active";
        $field = "room_active:$uid";
        
        $value = getRedisMaster()->hGet($key, $field);
        
        $data = json_decode($value, TRUE); */
        //$uid = $data['uid'];
        /* if(empty($uid)){
            LogApi::logProcess("deal2day :: uid is empty!!!");
            return false;
        } */
        /* $key = "room_active_$sid";
        $field = "room_active:$uid"; */
        $key = member_list::UserWatchRoomTimeKey($uid);
        if(empty($data['start_time'])){
            return false;
        }
        
        $startTime = $data['start_time'];
//         $active = $data['active_value'];
        
        /*****************************************/
        $now = strtotime("now");
        $daybegin=strtotime(date("Ymd"));
        $dayend=$daybegin+86400;
        
        $endtime = $now;
        $isTwoDay = false;
        if($startTime < $daybegin){
            LogApi::logProcess("跨天了--------deal2day :: XXXXXXXXXXXXXXX, data:".json_encode($data));
            
            //已经跨天
//             $active2 = ($now-$daybegin)/(5*60);
            
            LogApi::logProcess("跨天了--------deal2day :: now:$now, daybegin:$daybegin");
            
            //$data['active_value'] = 0;//$active2;
            // 不保留当天时间，无需太精确
            $data['remain_time'] = 0;
            $data['sun_remain_time'] = 0;
            $data['start_time'] = $now;//$now;
            $data['update_time'] = $now;
            //每分钟更新一次时间
            $data['minute_update_time'] = $now;
            $data['last_uptime_watch'] = $now;

            //$data['10_minute_update_time'] = $daybegin;
            $endtime = $daybegin-30;//减去30秒是为了避免结束时间跨天
            $isTwoDay = true;
            
            //用于判断当天是否为第一次进入直播间
            $date = date("Y-m-d");
            $key_tmp = "enter:uid:$uid:$date";
            $this->getRedisMaster()->set($key_tmp, $now);
            
            $this->getRedisMaster()->set($key, json_encode($data));
            
            $data_tmp = $this->getRedisMaster()->get($key);
            LogApi::logProcess("deal2day::跨天处理结束，保存的data信息为 :: key:$key, data:".json_encode($data_tmp));
            
            $sql = "update channellive.user_live_notify set total_time_length=(total_time_length+$endtime-$startTime) where start_time<$endtime and start_time>$endtime-86400 and
            sid = $sid and uid = $uid";
            $rows = $this->getDbChannellive()->query($sql);
            if (!$rows) {
                LogApi::logProcess("deal2day :: exe sql error, sql:$sql");
                return false;
            }
            
            $sql = "INSERT INTO live_user_length_record(sid, uid, start_time, time)
            VALUES($sid, $uid, $startTime, $endtime)";
            $rows = $this->getDbChannellive()->query($sql);
            if (!$rows) {
                LogApi::logProcess("deal2day :: exe sql error, sql:$sql");
                return false;
            }
            
            $sql = "INSERT INTO channellive.user_live_notify(sid, uid, start_time, total_time_length)
            VALUES($sid, $uid, $now, 0)";
            $rows = $this->getDbChannellive()->query($sql);
            if (!$rows) {
                LogApi::logProcess("deal2day :: exe sql error, sql:$sql");
                return false;
            }
            
            return true;
        }else{
            /* if($startTime < $dayend && $startTime+$data['remain_time']>$dayend){
                LogApi::logProcess("跨天了2-------- :: XXXXXXXXXXXXXXX, value:$value");
                //跨天
//                 $active2 = ($now-$daybegin)/(5*60);
                
                LogApi::logProcess("跨天了21-------- :: now:$now, daybegin:$daybegin");
                
                $data['active_value'] = 0;
                $data['remain_time'] = $startTime+$data['remain_time']-$dayend;
                $data['sun_remain_time'] = $startTime+$data['remain_time']-$dayend;
                $data['start_time'] = $dayend+1;
                $data['update_time'] = $dayend+1;
                //每分钟更新一次时间
                $data['minute_update_time'] = $dayend+1;
                $data['10_minute_update_time'] = $dayend+1;
                $endtime = $dayend;
                $isTwoDay = true;

                $date = date("Y-m-d");
                $key_tmp = "enter:uid:$uid:$date";
                getRedisMaster()->set($key_tmp, $date);
            } */
        }
        
        return false;
    }
    
    public function addGiftIntimacy($singerid, $uid)
    {
    	$date = date('Y-m-d');
        $key = "intimacy_gift:$date:$singerid";
        $value = $this->getRedisMaster()->hget($key, $uid);
        if(empty($value)){
            $this->getRedisMaster()->hset($key, $uid, 1);
            $sql = "select id from singer_guard s where s.singer_uid=$singerid 
                    and s.uid = $uid and s.end_time>UNIX_TIMESTAMP()";
            $rs = $this->getDbMain()->query($sql);
	        if ($rs && $this->getDbMain()->affected_rows > 0) {
	            $now = time();
	            $sql = "insert into cms_manager.fins_love_detail(uid, zid, experience, create_time)
                    values($uid, $singerid, 20, $now)";
	            $rows = $this->getDbChannellive()->query($sql);
	            
	            if(!$rows){
	                LogApi::logProcess("addGiftIntimacy:: exe sql error, sql:$sql");
	            }
	            
	        }else{
	            LogApi::logProcess("addGiftIntimacy:: exe sql error, sql:$sql");
	        }
        }
        
        $this->getRedisMaster()->expire($key, 48*60*60);
    }
    
    //亲密度
    public function addIntimacy($singerid, $uid)
    {
    	$date = date('Y-m-d');
        $key = "intimacy_room:$date:$singerid";
        
        $value = $this->getRedisMaster()->hget($key, $uid);
        if(empty($value)){
            $this->getRedisMaster()->hset($key, $uid, 1);
            $sql = "select id from rcec_main.singer_guard s where s.singer_uid=$singerid 
                    and s.uid = $uid and s.end_time>UNIX_TIMESTAMP()";
            $rs = $this->getDbMain()->query($sql);
	        if ($rs && $this->getDbMain()->affected_rows > 0) {
	            $now = time();
	            $sql = "insert into cms_manager.fins_love_detail(uid, zid, experience, create_time)
                    values($uid, $singerid, 5, $now)";
	            $rows = $this->getDbChannellive()->query($sql);
	            
	            if(!$rows){
	                LogApi::logProcess("addIntimacy:: exe sql error, sql:$sql");
	            }
	            
	        }else{
	            LogApi::logProcess("addIntimacy:: exe sql error, sql:$sql");
	        }
        }
        
        $this->getRedisMaster()->expire($key, 48*60*60); 
    }
    
    //获得主播当天的直播时长
    public function getSingerCurDayPlayTime($singerid)
    {
        $sql = "
        SELECT
            table1.uid,
            sum(table1.timelength) AS time_length
        FROM
            (
                SELECT
                    (l.time - l.start_time) AS timelength,
                    l.uid
                FROM
                    channellive.live_notify l
                WHERE
                    l.time >= unix_timestamp(cast(now() AS date))
                AND l.time < unix_timestamp(
                    cast(now() AS date) + INTERVAL 1 DAY
                )
                UNION ALL
                    SELECT
                        sum(l.time - l.start_time) AS timelength,
                        l.uid
                    FROM
                        channellive.live_record l
                    WHERE
                        l.time >= unix_timestamp(cast(now() AS date))
                    AND l.time < unix_timestamp(
                        cast(now() AS date) + INTERVAL 1 DAY
                    )
                    GROUP BY
                        l.uid
            ) AS table1
        WHERE
            table1.uid = $singerid
        HAVING
            sum(table1.timelength) > 0";

        $rs = $this->getDbMain()->query($sql);
        $len = 0;
        if ($rs && $this->getDbMain()->affected_rows > 0) {
            $row = $rs->fetch_assoc();
            $len = (int)$row['time_length'];
        }
        
        return $len;
    }
    
    //添加用户进入直播间
    public function startWatchRoom(&$return, $sid, $uid)
    {
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        if(1 == $user['is_robot']){
            continue;
        }
        
        LogApi::logProcess("进入直播间startWatchRoom::****************uid:$uid");
        /*********************更新用户在直播间停留时间***************************/
        $data = array();
        //用户在直播间观看时长的活跃度是在每30秒轮训中更新的
        /* $key = "room_active_$sid";
        $field = "room_active:$uid"; */
        $key = member_list::UserWatchRoomTimeKey($uid);
        
        $now = time();
        $value = $this->getRedisMaster()->get($key);
        
        LogApi::logProcess("开始计时**key:$key**************value:$value");
        
        
        $date = date("Y-m-d");
        $key_time = "enter:uid:$uid:$date";
        $isFirst = $this->getRedisMaster()->get($key_time);
        if(empty($isFirst)){
            LogApi::logProcess("第一次进入直播间****************uid:$uid");
            $data['uid'] = $uid;
            //$data['sid'] = $sid;
            //当天观看时间
            $data['remain_time'] = 0;
            $data['sun_remain_time'] = 0;
            //$data['active_value'] = 0;
            
            $this->getRedisMaster()->set($key_time, $now);
        }else{
            LogApi::logProcess("非第一次进入直播间****************uid:$uid");
            $data_tmp = json_decode($value, TRUE);
            $data['uid'] = $uid;
            $data['remain_time'] = empty($data_tmp['remain_time']) ? 0 : (int)$data_tmp['remain_time'];
            $data['sun_remain_time'] = empty($data_tmp['sun_remain_time']) ? 0 : (int)$data_tmp['sun_remain_time'];
            //$data['active_value'] = empty($data_tmp['active_value']) ? 0 : (int)$data_tmp['active_value'];
        }
        
        $data['stop'] = 0;
        $data['start_time'] = $now;
        $data['update_time'] = $now;
        //每分钟更新一次时间
        $data['minute_update_time'] = $now;
        $data['last_uptime_watch'] = $now;
        //$data['10_minute_update_time'] = $now;

        $this->getRedisMaster()->set($key, json_encode($data));
            
        $valuetmp = $this->getRedisMaster()->get($key);
        
        $gulm = new GameUserLaunchModel();
        $gulm->UserEnter($sid, $uid);
        // 帮派夺旗事件触发
        $ff_model = new flag_faction_model();
        $ff_model->event_user_room_enter(&$return, $uid, $sid);
        //
        LogApi::logProcess("startWatchRoom::更新完用户观看时长后的数据************data:$valuetmp");
            
        /********************保存数据库**********************/
        $now = strtotime("now");
        $daybegin=strtotime(date("Ymd"));
        $dayend=$daybegin+86400;
        
        /* $sql = "select id from user_live_notify where start_time<$dayend and start_time>$daybegin and
            sid = $sid and uid = $uid"; */
        $sql = "select id from channellive.user_live_notify where start_time BETWEEN UNIX_TIMESTAMP(date_format(now(), '%Y-%m-%d 00:00:00'))
			AND UNIX_TIMESTAMP(date_format(now(), '%Y-%m-%d 23:59:59')) and sid = $sid and uid = $uid";
        $rows = $this->getDbChannellive()->query($sql);
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $id = $row['id'];
            $sql = "update user_live_notify set start_time = $now where id = $id";
        }else {
            $sql = "INSERT INTO channellive.user_live_notify(sid, uid, start_time, total_time_length)
            VALUES($sid, $uid, $now, 0)";
        }
        
        LogApi::logProcess("添加用户进入直播间startWatchRoom::****************sql:$sql");
        
        $rows = $this->getDbChannellive()->query($sql);
        if(!$rows){
            return false;
        }
        
        return true;
    }

    public function clear_hot_value($uid)
    {
        $key = "singer_hot_point_top";
        $this->getRedisMaster()->zrem($key, $uid);
        $key = "singer_hot_point_top_tmp";
        $this->getRedisMaster()->zrem($key, $uid);
        $key = "singer_hot_value_top";
        $this->getRedisMaster()->zrem($key, $uid);
        
        $this->clear_new_star_point($uid);
                
        $model_hot_rank = new hot_rank_model();
        $model_hot_rank->clear_rank($uid);

        $model_anchor_pt = new anchor_points_model();
        $model_anchor_pt->clear_anchor_points($uid);
    }
    
    public function updateChannelLiveInfoxxx($param)
    {
        LogApi::logProcess("ChannelLiveModel:updateChannelLiveInfoxxx param:" . json_encode($param));

        $uid = isset($param['singer_id'])?$param['singer_id']:0;

        if (empty($uid)) {
            return;
        }

        $now = time();
        $mainkey = "singer_hot";
        $field = "singer_hot:$uid";

        $value = $this->getRedisMaster()->hget($mainkey, $field);

        $data = json_decode($value, TRUE);
        $stop_time = 0;
        
        if(isset($data['stop_time'])) {
            $stop_time = $data['stop_time'];
        }

        if( $stop_time == 0 ) {
            return;
        }
                
        //如果停播时间超过3分钟，则情况热度值
        if($now - $data['stop_time'] > 3*60){
            $key = "singer_hot_point_top";
            $this->getRedisMaster()->zrem($key, $uid);
            $key = "singer_hot_point_top_tmp";
            $this->getRedisMaster()->zrem($key, $uid);
            $key = "singer_hot_value_top";
            $this->getRedisMaster()->zrem($key, $uid);
            
            $this->clear_new_star_point($uid);

            $this->getRedisMaster()->hdel($mainkey, $field);
            
            $model_hot_rank = new hot_rank_model();
            $model_hot_rank->clear_rank($uid);

            LogApi::logProcess("ChannelLiveModel:updateChannelLiveInfoxxx result 已经停播超过3分钟未在开播，清除主播uid:$uid,的热度值。");
        }
    }
    
    //主播推送
    public function pushWeb($singerid){
        LogApi::logProcess("主播推送************uid:$singerid");
        $url = GlobalConfig::GetSingerPlayStartPushURL() . $singerid;
        LogApi::logProcess("主播推送************url:$url");
        /* if (!($data = file_get_contents($url))) {
            LogApi::logProcess("主播推送失败！！！***********url:$url");
            return false;
        }
        LogApi::logProcess("主播推送***********返回data:".json_decode($data)); */
        $timeout = 5;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        //curl_setopt($ch, CURLOPT_POST, 1); // post提交方式
        //         curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $result = curl_exec($ch);
        curl_close($ch);
        if (empty($result)) {
            LogApi::logProcess("主播推送成功。***********");
            return true;
        } else {
            LogApi::logProcess("主播推送失败！！！***********url:$url");
            return false;
        }
    }
    
    public function startPlayer(&$return, $param)
    {
        LogApi::logProcess('开始startPlayer::****************'.json_encode($param));
        $sid = intval($param["sid"]);
        $cid = intval($param["cid"]);
        $uid = intval($param["uid"]);
        $starttime = intval($param["starttime"]);
        //$type = 0;
        $type = intval($param["type"]);
        //$gameid = 1;
        $gameid = intval($param["gameid"]);
        
        if (isset($param["ip"])) {
            $ip = $param["ip"];
        }else {
            $ip = "";
        }
        
        {
            $time_valid = 5;
            // 主播停播后，将数据移到开播历史记录表里
            $sql = "INSERT INTO channellive.live_record(sid, cid, uid, start_time, num, time, ip, type, game_id, live_peak, theme, family_id)" .
                " SELECT sid, cid, uid, start_time, num, time, ip, type, game_id, live_peak, theme, family_id FROM channellive.live_notify WHERE (sid=$sid && uid=$uid && (time - start_time >= $time_valid) )";
            $rows = $this->getDbChannellive()->query($sql);
            if ($rows)
            {
                LogApi::logProcess("startPlayer success sql:$sql");
            }
            else
            {
                LogApi::logProcess("startPlayer common sql:$sql");
            }
        }
        
        
//         if(1 == $count){
            // 主播开播后，第一次上传数据
        /* $recordSql = "INSERT INTO live_record(sid, cid, uid, start_time, num, time, ip, type, game_id, live_peak, theme)" .
            " SELECT sid, cid, uid, start_time, num, time, ip, type, game_id, live_peak, theme FROM live_notify WHERE sid=$sid and uid=$uid and cid=$cid";
    
        $flag = getDbChannellive()->query($recordSql);
    
        LogApi::logProcess("sql:" . $recordSql . "result:" . $flag); */
    
        //把主题信息存储到live_notify里
        $sql = "select theme, channel_id from live_theme_info where sid=$sid and uid=$uid";
    
        $rows = $this->getDbChannellive()->query($sql);
        
        $theme = "";
        $channel_id = "";
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $theme = $row['theme'];
            $channel_id = $row['channel_id'];
             
            LogApi::logProcess("theme::$theme****************channel_id::$channel_id");
        }else{
            LogApi::logProcess("startPlayer exe sql is error****************sql:$sql");
        }
        
        // 获取主播家族id
        $model_uinfo = new UserInfoModel();
        $family_id = $model_uinfo->SetAndGetSingerFamilyId($uid);
        
        $now = strtotime("now");

        // DBLE
        $sql = "SELECT sid, cid, uid FROM channellive.live_notify WHERE sid=$sid AND cid=$cid AND uid=$uid";
        $db_channel = $this->getDbChannellive();

        $rows = $db_channel->query($sql);

        if (!empty($rows) && $rows->num_rows > 0) {
            $sql = "UPDATE channellive.live_notify SET start_time = unix_timestamp(), num = 0, time=unix_timestamp(), ip='$ip', type = $type, game_id = $gameid, theme = '$theme', channel_id='$channel_id',family_id=$family_id WHERE sid=$sid AND cid=$cid AND uid=$uid";
        } else {
            $sql = "INSERT INTO channellive.live_notify(sid, cid, uid, start_time, num, time, ip, type, game_id, theme, channel_id, family_id) VALUES($sid, $cid, $uid, unix_timestamp(), 0, unix_timestamp(), \"$ip\", $type, $gameid, \"$theme\", \"$channel_id\", $family_id)";
        }
        
        $rows = $db_channel->query($sql);
        if(!$rows || $db_channel->affected_rows <= 0){
            LogApi::logProcess("[DBLElog] ChannelLiveModel:startPlayer sql error:$sql");
        }
        
        {
	        // 如果是第一次开播，记录第一次开播时间
	        $sql = "INSERT IGNORE INTO channellive.t_anchor_first_open (uid, sid, time_first_open) VALUES ($uid,$sid,$now)";
	        $rows = $this->getDbChannellive()->query($sql);
        }
        
        $this->createSunSingerPlay($uid, $sid, time());
        // 维护直播间用户互动游戏完整
        $gulm = new GameUserLaunchModel();
        $gulm->SingerEnter($sid, $uid);
        // 帮派夺旗事件触发
        $ff_model = new flag_faction_model();
        $ff_model->event_singer_room_enter(&$return, $uid, $sid);
        
//         }
    }
    
    //更新正在开播的直播间信息
    public function updateChannelLiveInfo($param)
    {
		$timeStart = microtime(true);
 
         LogApi::logProcess('*****************开始更新主播在线时间::param:'.json_encode($param));
        $result = array();
        
        $sid = intval($param["sid"]);
        $cid = intval($param["cid"]);
        $uid = intval($param["uid"]);
        $starttime = intval($param["starttime"]);
        //停止直播时，当前直播间的观看人数（非峰值）
        $num = intval($param["num"]);
        //$type = 0;
        $type = intval($param["type"]);
        //$gameid = 1;
        $gameid = intval($param["gameid"]);
        //开播后更新次数
        $count = intval($param["count"]);
        
        if (isset($param["ip"])) {
            $ip = $param["ip"];
        }else {
            $ip = "";
        }
        
        $now = strtotime("now");
        $time = time();

        $model_anchor_pt = new anchor_points_model();
        
        if(1 == $count){ 
            // 主播开播后，第一次上传数据
            /* $recordSql = "INSERT INTO live_record(sid, cid, uid, start_time, num, time, ip, type, game_id, live_peak, theme)" .
                " SELECT sid, cid, uid, start_time, num, time, ip, type, game_id, live_peak, theme FROM live_notify WHERE sid=$sid and uid=$uid and cid=$cid";

            $flag = getDbChannellive()->query($recordSql);
        
            file_put_contents($this->LogFile(), "sql:" . $recordSql . "result:" . $flag ."\n", FILE_APPEND);
            LogApi::logProcess("sql:" . $recordSql . "result:" . $flag); */
            
            //把主题信息存储到live_notify里
            $sql = "select theme, channel_id from live_theme_info where sid=$sid and uid=$uid";

            $rows = $this->getDbChannellive()->query($sql);
                         
            if ($rows && $rows->num_rows > 0) {
                $row = $rows->fetch_assoc();
                $theme = $row['theme'];
                $channel_id = $row['channel_id'];
                 
                LogApi::logProcess("theme::$theme****************channel_id::$channel_id");
        
                $now = strtotime("now");
                $sql = "update channellive.live_notify set ip = \"$ip\", theme = \"$theme\", 
                channel_id = \"$channel_id\" where sid = $sid and uid = $uid";// and cid = $cid
                $rows = $this->getDbChannellive()->query($sql);
                if(!$rows){
                    LogApi::logProcess("count=1,updateChannelLiveInfo::***excute sql error!!!*************sql::$sql");
                }
            }
        
        }else{
            $sql = "update channellive.live_notify set num = $num, time = $now, type = $type, 
            game_id = $gameid where sid = $sid and uid = $uid";// and cid = $cid
            
            $flag = $this->getDbChannellive()->query($sql);
            
            if (!$flag) {
                LogApi::logProcess('*****************更新主播在线时间失败，excuse sql：'.$sql);
            }
        }
        
        //
        $cdg = new ChestDebrisGenerate();
        $cdg->InitConfigDB();
        // 更新主播.
        $cdg->Update($uid,$sid,$starttime,$now);

        // 更新主播直播时间，判断是否产生阳光
        {
        	$res_sun = $this->createSunSingerPlay($uid, $sid, $now);
        	if ($res_sun['flag'] == true) {
        		$this->createSunAndSendMsg($uid, $now, intval($res_sun['sun_num']));
        	}
        }
        
        // // 用户阳光
        // $urttm = new UserRoomTimeTotalModel();
        // $urttm->InitConfigDB();
        // $urttm->Update($result,$uid,$sid,$starttime,$now);
        //$this->Live($sid, $cid, $uid, $num, $now);
        
        //LogApi::logProcess('*****************更新主播在线时间，返回：：'.json_encode($result).' cid::'.$cid.' sid::'.$sid.' uid::'.$uid.' 观看人数::'.$num);
        
        /**************************************/
         $taskModel = new TaskModel();
        /*//统计主播每日直播任务
        $taskModel->statShowTimeDayTask($uid);
        //统计主播每日直播主线任务
        $taskModel->statShowTimeMainTask($uid); */
        
        $rData = $taskModel->statShowTimeTask($uid);
        if(!empty($rData)){
            $result[] = $rData;
        }
        
        $singer_hot_score_limit = array(
           'speak' => 1200,
           'alive' => 1200,
           'share' => 1200,
        );
        $singer_hot_score_real = array(
           'speak' => 0,
           'alive' => 0,
           'share' => 0,
        );
        /**************************************/
        
        $userInfo = new UserInfoModel();
        /***********************更新用户在直播间观看的活跃度**************************/
        //$key = "room_active_$sid";
        $key = member_list::HashUserListInfoKey($sid);
        $fields = $this->getRedisMaster()->hKeys($key);
        //LogApi::logProcess('更新用户在直播间观看的活跃度*************人数:'.count($fields));
        
        // 校正房间荣耀总值
        {	
        	$model_glory = new GloryModel();
        	$ret_glory = $model_glory->onUpdate($sid, $fields);
        	if (!empty($ret_glory)) {
        		$result[] = $ret_glory;
        	}
        }
        
        if(!empty($fields)){
            foreach ($fields as $userid){
                $u_key = member_list::UserWatchRoomTimeKey($userid);
                $value = $this->getRedisMaster()->get($u_key);
                
                $data = json_decode($value, TRUE);
                if(empty($data)){
                    continue;
                }
                
                /* if($data['stop'] && $data['sid'] == $sid){
                    continue;
                } */
                
                $user = $userInfo->getInfoById($userid);
                $isRobot = (int)$user['is_robot'];
                if(1 == $isRobot){
                    continue;
                }
                LogApi::logProcess('stop 过滤后，更新用户在直播间观看的活跃度:****'.json_encode($data));
                
                /**************************************/
                //LogApi::logProcess("统计用户观看每日和主线任务*************data:$value");
                /* //统计用户每日观看任务
                $taskModel->statWatchTimeDayTask($data['uid']);
                //统计用户观看主线任务
                $taskModel->statWatchTimeMainTask($data['uid']); */
                $rData = $taskModel->statWatchTask($uid, $userid);
                if(!empty($rData)){
                    for($i = 0; $i < count($rData); $i++)
                    $result[] = $rData[$i];
                }
                
                //停留在直播间的时间累加
                $remain = $now - $data['update_time'];
                if ($remain >= 35) {
                	$remain = 0;
                }
                $data['remain_time'] += $remain;
                $data['sun_remain_time'] += $remain;
                $data['update_time'] = $now;
                
                //TODO:产生阳光,每天只有2个小时是计算阳光值的(只要是15分钟倍数就产生阳光，产生一次阳光就把时间减去15分钟，然后用剩余的时间去除以15分钟，来判断是否要产生阳光，所以需要新建一个key和停留时间一致，唯一的区别是产生阳光后需要减去15分钟，)
            	//保存的次数
            	$date_sun = date("Y-m-d");
            	$sunTimesKey = "user_sun_times:$date_sun:$userid";
                $times = $this->getRedisMaster()->llen($sunTimesKey);
                if ($times < 8) {

                    $excpt_flag = false;
                    if($times <= 0){
                        if($data['sun_remain_time'] / 60 > 1){
                            $excpt_flag = true;
                            $data['sun_remain_time'] = $data['sun_remain_time']-60;
                            //LogApi::logProcess("***first start create sun*****user:".$userid);
                            $sunvalue = $this->CreateSunShine($userid, $sid);
                            //LogApi::logProcess("***end first create sun*****user:$userid result:".$sunvalue);
                            if(!empty($sunvalue)){
                                $result[] = $sunvalue;
                            }
                        }
                    }else{
                        if($data['sun_remain_time'] / (15*60) > 1){
                            $excpt_flag = true;
                            $data['sun_remain_time'] = $data['sun_remain_time']-15*60;
                            //LogApi::logProcess("***start create sun*****user:".$userid);
                            $sunvalue = $this->CreateSunShine($userid, $sid);
                            //LogApi::logProcess("***end create sun*****user:$userid result:".$sunvalue);
                            if(!empty($sunvalue)){
                                $result[] = $sunvalue;
                            }
                        }
                    }

                    // 针对异常情况做兼容处理
                    if ($excpt_flag == true && $data['sun_remain_time'] > 30) {
                        $data['sun_remain_time'] = 30;
                    }                    
                }

                
                if($now - $data['minute_update_time'] >= 60){
                    $model_anchor_pt->on_user_watch($uid, $userid);
                }
                
                /**************************************/
                //LogApi::logProcess('开始执行跨天处理:****'.json_encode($data));
                //处理跨天情况
                $b_twoday = false;
                $d_flag = $this->deal2day($userid, $sid, $data, $b_twoday);
                if (!$b_twoday) {
                    $u_flag = $this->updateWatchTime($userid, $sid, $now, $data);
                    if (!$u_flag) {
                        $this->getRedisMaster()->set($u_key, json_encode($data));                        
                    }                    
                }
            }
        }

        /******************************每隔3分钟计算主播热度******************************/
        $key = "singer_hot";
        $field = "singer_hot:$uid";
        $value = $this->getRedisMaster()->hGet($key, $field);
        $data = json_decode($value, TRUE);

        $date_last_1min = isset($data['last_1min']) ? $data['last_1min'] : $now;
        if ($now - $date_last_1min >= 60) {
            $model_anchor_pt->flush_per_1min($uid);
            $data['last_1min'] = $now;
        }
        
        // 热度榜计时阀值
        $list_number_threshold = 10000;
        // 热度榜主播人数
        $valuekey = "singer_hot_value_top";
        $hot_list_number = $this->getRedisMaster()->zCard($valuekey);
        //LogApi::logProcess("*****计算热度值 hot_list_number:$hot_list_number need_delay_calculate:$need_delay_calculate");
        
        //1.1当热门榜主播人数大于20时，之后开播的主播1个小时后开始计时，计算热度。
        //1.2当热门榜主播人数小于20时，之后开播的主播立刻开始计时，计算热度。
        $time_dt = $now - $data['update_time'];
        $data_up_time = isset($data['update_time']) ? $data['update_time'] : $now;
        $need_calculate = false;

        // 间隔时间大于3分钟
        $need_calculate = $time_dt >= 3*60;

        if(true == $need_calculate){
        	// 获取评分配置
        	$score_level = $this->get_singer_score_coefficient($uid);
            $point = $model_anchor_pt->flush_per_3min($uid);
        	LogApi::logProcess("ChannelLiveModel:updateChannelLiveInfo score coefficient. singer_id:$uid  pt:$point conf:" . json_encode($score_level));
        	$proportion = $score_level['proportion'];
        	if (empty($proportion)) {
        		$proportion = 1;
        	}
        	
            //TODO:判断主播是否在抑制期
            $isYZ = $this->isYZ($uid);
            /* 
             *该主播排名第一，则进入抑制期,同时衰减系数为1，在抑制期内，如果名次和上一次更新时的名次相比，
             *没有下降，则衰减系数+1，如果下降，则衰减系数变为1
             */
            $valuekey = "singer_hot_value_top";
            if($isYZ){
                $yz_time = $this->getYZtime();
                $yzkey = "yz_time";
                $yzfield = "yz_time:$uid";
                
                $yzdata = $this->getRedisMaster()->hGet($yzkey, $yzfield);
                $yz_data = array();
                
                if(!empty($yzdata)){
                    $json_data = json_decode($yzdata, TRUE);
                    //TODO:如果不是第一次进入抑制期，对比两次排名是否有变化
                    
                    $hotValueKey = "singer_hot_value_top";
                    
                    $i_old = $this->getRedisMaster()->zrevrank($hotValueKey, $uid);
                    if (null == $i_old)
                    {
                        // rank return null,mean rank max.
                        $i_old = PHP_INT_MAX;
                    }
                    else
                    {
                        // rank from 0, value++ it. 
                        $i_old = $i_old + 1;
                    }
                    //LogApi::logProcess("*****计算热度值.主播上轮热度值排名:$i_old");
                    // 使用快速的zrevrank代替遍历查询
                    /*
                    $ulist_old = $this->getRedisMaster()->zrevrange($hotValueKey, 0, -1);
                    //主播历史热度排名
                    $i_old = 0;
                    foreach ($ulist_old as $u){
                        $i_old++;
                        if($uid == $u){
                            break;
                        }
                    }
                    LogApi::logProcess("*****计算热度值 i_old:$i_old");
                    */
                    
                    //开始计算热度值
//                     $valuekey = "singer_hot_value_top";
                    $historyHotValue = $this->getRedisMaster()->zScore($valuekey, $uid);
                    if(empty($historyHotValue)){
                        $historyHotValue = 0;
                    }
                    
                    //获得时间累积衰减值
                    $index = $json_data['ratio']-1;
                    if($index >= count($this->yzRatioList)){
                        $index = count($this->yzRatioList)-1;
                    }
                    $ratio = $this->yzRatioList[$index];
                    
                    // 抑制期热度计算
                    $hotValue = $this->muffleTimeHotCalculate($uid, $historyHotValue, $point, $ratio, $proportion);
                    LogApi::logProcess("ChannelLiveModel:updateChannelLiveInfo calc_hot_point_in_yz. singer_id:$uid history:$historyHotValue new:$point index:$index ratio:" . json_encode($ratio));
                    $this->getRedisMaster()->zRem($valuekey, $uid);
                    $this->getRedisMaster()->zAdd($valuekey, $hotValue, $uid);
                    $this->set_new_start_point($uid, $hotValue);
                    //清除已经参与计算的积分

                    //主播当前热度值排名                    
                    $i = $this->getRedisMaster()->zrevrank($valuekey, $uid);
                    if (null == $i)
                    {
                        // rank return null,mean rank max.
                        $i = PHP_INT_MAX;
                    }
                    else
                    {
                        // rank from 0, value++ it. 
                        $i = $i + 1;
                    }
                    //3.4 如果抑制期间，该主播掉出热门榜前20，则结束抑制。
                    if (20 < $i)
                    {
                        $this->finishYZ($uid);
                    }
                    //如果排名没有下降则时间衰减系数+1
                    if($i <= $i_old){
                        if($json_data['ratio']+1 >= count($this->yzRatioList)){
                            $yz_data['ratio'] = count($this->yzRatioList)-1;
                        }else{
                            $yz_data['ratio'] = $json_data['ratio']+1;
                        }
                    }else{
                        $yz_data['ratio'] = 1;
                    }
                }
                
                /*********以防万一加入此判断******/
                if (empty($yz_data['ratio'])){
                    //首次进入抑制期，抑制系数为1
                    $yz_data['ratio'] = 1;
                }
                /******end********/
                //刷新抑制期时间（如果该主播排名在第一，则刷新抑制期时间）
                $flag = $this->isYzOfCalculate($uid);
                if($flag){
                    $yz_data['start_time'] = $now;
                    $yz_data['end_time'] = $now+$yz_time;
                }
                $this->getRedisMaster()->hSet($yzkey, $yzfield, json_encode($yz_data));
                
                $this->isYZOutTime($uid);
                
            }else{
                //TODO:计算主播热度
//                 $valuekey = "singer_hot_value_top";
                $historyHotValue = $this->getRedisMaster()->zScore($valuekey, $uid);
                if(empty($historyHotValue)){
                    $historyHotValue = 0;
                }
                
                //热度=历史*0.986+新增*1.35
                // $hotValue = $historyHotValue*0.986+$point*1.35;
                // 普通期热度计算
                $hotValue = $this->commonTimeHotCalculate($uid, $historyHotValue, $point, $proportion);
                LogApi::logProcess("ChannelLiveModel:updateChannelLiveInfo calc_hot_point_no_yz. singer_id:$uid history:$historyHotValue new:$point");
                
                $this->getRedisMaster()->zRem($valuekey, $uid);
                $this->getRedisMaster()->zAdd($valuekey, $hotValue, $uid);
                $this->set_new_start_point($uid, $hotValue);
                //清除已经参与计算的积分
                
                $flag = $this->isYzOfCalculate($uid);
                if($flag){
                    $now = strtotime("now");
                    $yz_time = $this->getYZtime();
                    $yzkey = "yz_time";
                    $yzfield = "yz_time:$uid";
                    
                    $yz_data = array();
                    $yz_data['start_time'] = $now;
                    $yz_data['end_time'] = $now+$yz_time;
                    $yz_data['ratio'] = 1;
                    
                    $this->getRedisMaster()->hSet($yzkey, $yzfield, json_encode($yz_data));
                                        
                }
                
            }
            
            
            
            /****************处理上热门任务************************/
            $hotValueKey = "singer_hot_value_top";
            //主播当前热度值排名
            $i = $this->getRedisMaster()->zrevrank($valuekey, $uid);
            if (empty($i))
            {
                // rank return null,mean rank max.
                $i = PHP_INT_MAX;
            }
            else
            {
                // rank from 0, value++ it.
                $i = $i + 1;
            }
            //LogApi::logProcess("*****计算热度值.主播当前热度值排名:$i");
            // 使用快速的zrevrank代替遍历查询
            /*
            $ulist = $this->getRedisMaster()->zrevrange($valuekey, 0, -1);
            $i = 0;
            foreach ($ulist as $u){
                $i++;
                if($uid == $u){
                    break;
                }
            }
            */
            if($i <= 20){
                $result[] = array(
                	'broadcast' => 5,
                    'data' => array(
                        'uid' => (int)$uid,
                        'target_type' => 10,//10为上热门
                        'num' => 1,
                        'extra_param' => (int)$i
                    )
                );
            }
            
            $data['update_time'] = $now;
            
            $key = "singer_hot";
            $field = "singer_hot:$uid";
            
            $this->getRedisMaster()->hSet($key, $field, json_encode($data));
        }        
//         LogApi::logProcess("*****定时刷新直播间信息：".json_encode($result));
		
		$timeCost = round((microtime(true) - $timeStart) * 1000, 2);
		Logger::fileLog("updateChannelLiveInfo DONE", $timeCost, 'info');
     
		// 帮派夺旗事件触发
		$ff_model = new flag_faction_model();
		$ff_model->event_room_heartbeat($result, $sid);
		
		// 红包
		$rp_rs = RedPacketApi::heartBeatEvent($sid, $time);
		if (!empty($rp_rs)) {
			$result[] = $rp_rs;
		}
		
		// 热门排名
		$model_hot_rank = new hot_rank_model();
		$model_hot_rank->update_rank_score($uid);
		
        return $result;
    }
    //加热度(增加上限限制)
    public function addHotPointWithLimit($uid,$singer_hot_score_limit,$singer_hot_score_real,$type,$value){
        $hot_real_value_max = $singer_hot_score_limit[$type] - $singer_hot_score_real[$type];
        $hot_real_value = $hot_real_value_max > $value ? $value : $hot_real_value_max;
        if (0 < $hot_real_value)
        {
            $singer_hot_score_real[$type] += $hot_real_value;
            $this->addHotPoint($uid, $hot_real_value);
        }    
    }
    //更新总热度(增加上限限制)
    public function updateTotalHotPointWithLimit($uid,$singer_hot_score_limit,$singer_hot_score_real){
        foreach ($singer_hot_score_real as $k => $v){
            $hot_real_value_max = $singer_hot_score_limit[$k];
            $hot_real_value = $hot_real_value_max > $v ? $v : $hot_real_value_max;
            
            LogApi::logProcess("*****计算热度值.加总热度key:$k, value:$v, hot_real_value_max:$hot_real_value_max, hot_real_value:$hot_real_value");
            
            if (0 < $hot_real_value)
            {
                $new_point = $this->addHotPoint($uid, $hot_real_value);
                LogApi::logProcess("*****计算热度值.加总热度key:$k value:$hot_real_value");
            }
        }
    }
    //更新总新星(增加上限限制)
    public function updateTotalNewPointWithLimit($uid,$singer_hot_score_limit,$singer_hot_score_real){
        foreach ($singer_hot_score_real as $k => $v){
            //$hot_real_value_max = $singer_hot_score_limit[$k];
            //$hot_real_value = $hot_real_value_max > $v ? $v : $hot_real_value_max;
            $hot_real_value = $v;
            if (0 < $hot_real_value)
            {
                $new_point = $this->add_new_star_point($uid, $hot_real_value);
                LogApi::logProcess("ChannelLiveModel:updateTotalNewPointWithLimit key:$k value:$hot_real_value total:$new_point");
            }
        }
        // bata2.2 version not need append star_hot_point_top value.
        // append getRedisMaster star_hot_point_top
        // $top_key = ChannelLiveModel::ZsetSingerNewPointTopKey();
        // $star_key = ChannelLiveModel::ZsetStarHotPointTopKey();
        // $top_value_key = ChannelLiveModel::ZsetSingerNewPointTopValueKey();
        // $new_point = $this->getRedisMaster()->zScore($top_value_key, $uid);
        
        // $star_score = $this->getRedisMaster()->zScore($star_key, $uid);    
        // $score_total = $star_score + $new_point;        
        // $this->getRedisMaster()->zAdd($top_key, $score_total,$uid);
        // LogApi::logProcess("*****计算热度值.新总新星 uid:$uid new_point:$new_point star_score:$star_score score_total:$score_total");
    }
    public static function ZsetSingerNewPointTopKey()
    {
        return "singer_new_point_top";
    }
    public static function ZsetStarHotPointTopKey()
    {
        return "star_hot_point_top";
    }
    public static function ZsetSingerNewPointTopValueKey()
    {
        return "singer_new_point_top_value";
    }
    
    //判断抑制期超时
    public function isYZOutTime($singerid){
        $yzkey = "yz_time";
        $yzfield = "yz_time:$singerid";
        $yzdata = $this->getRedisMaster()->hget($yzkey, $yzfield);
        if(!empty($yzdata)){
            $data = json_decode($yzdata, TRUE);
        
            $end = $data['end_time'];
            if(time() <= $end){
                return false;
            }else{
                $this->finishYZ($singerid);
                return true;
            }
        }
        
        return true;
    }
    
    //计算后判断是否在抑制期
    public function isYzOfCalculate($singerid){
        //在榜首则进入抑制期
        $key = "singer_hot_value_top";
        $top1 = $this->getRedisMaster()->zrevrange($key, 0, 0);
        LogApi::logProcess('判断主播是否在抑制期：isYZ, 获得top1'.json_encode($top1). ' 主播'.$singerid);
        $isYZ = 0;
        foreach ($top1 as $top){
            if($singerid == $top){
                LogApi::logProcess("主播$singerid, is yz.");
                $isYZ = 1;
                return true;
            }
        }
        //在抑制期内的所有主播集合
        /* $yzkey = "yz_time";
        $yzfield = "yz_time:$singerid";
        $yzdata = $this->getRedisMaster()->hget($yzkey, $yzfield);
        if(!empty($yzdata)){
            $data = json_decode($yzdata, TRUE);
        
            $end = $data['end_time'];
            if(time() <= $end){
                return true;
            }else{
                $this->finishYZ($singerid);
                return false;
            }
        } */
        return false;
    }
    
    //判断是否在抑制期
    public function isYZ($singerid){
        $yzkey = "yz_time";
        $yzfield = "yz_time:$singerid";
        $yzdata = $this->getRedisMaster()->hget($yzkey, $yzfield);
        if(!empty($yzdata)){
            $data = json_decode($yzdata, TRUE);
        
            $end = $data['end_time'];
            if(time() <= $end){
                return true;
            }else{
                $this->finishYZ($singerid);
                return false;
            }
        }
        return false;
    }
    //结束抑制期
    public function finishYZ($singerid){
        //在抑制期内的所有主播集合
        $yzkey = "yz_time";
        $yzfield = "yz_time:$singerid";
        $this->getRedisMaster()->hdel($yzkey, $yzfield);
    }
    
    //获得抑制期时间
    public function getYZtime(){
        $now = strtotime("now");
        $daybegin=strtotime(date("Ymd"));
        $t2 = $daybegin+2*60*60;
        $t8 = $daybegin+8*60*60;
        $t10 = $daybegin+10*60*60;
        $t14 = $daybegin+14*60*60;
        $t18 = $daybegin+18*60*60;
        $t22 = $daybegin+22*60*60;
        $dayend=$daybegin+86400;
        
        //0-2时段
        if($now > $daybegin && $now <=$t2){
            return 60*60;
        }
        //2-8时段
        if($now > $t2 && $now <=$t8){
            return 20*60;
        }
        //8-10时段
        if($now > $t8 && $now <=$t10){
            return 40*60;
        }
        //10-14时段
        if($now > $t10 && $now <=$t14){
            return 60*60;
        }
        //14-18时段
        if($now > $t14 && $now <=$t18){
            return 60*60;
        }
        //18-22时段
        if($now > $t18 && $now <=$t22){
            return 90*60;
        }
        //22-24时段
        if($now > $t22 && $now <=$dayend){
            return 120*60;
        }
        
        LogApi::logProcess('getYZtime:*****************没有找到时段');
        return 0;
    }
    
    public function getHour()
    {
        $now = strtotime("now");
        $daybegin=strtotime(date("Ymd"));
        $t0 = $daybegin+1*60*60;
        $t1 = $daybegin+2*60*60;
        $t2 = $daybegin+3*60*60;
        $t3 = $daybegin+4*60*60;
        $t4 = $daybegin+5*60*60;
        $t5 = $daybegin+6*60*60;
        $t6 = $daybegin+7*60*60;
        $t7 = $daybegin+8*60*60;
        $t8 = $daybegin+9*60*60;
        $t9 = $daybegin+10*60*60;
        $t10 = $daybegin+11*60*60;
        $t11 = $daybegin+12*60*60;
        $t12 = $daybegin+13*60*60;
        $t13 = $daybegin+14*60*60;
        $t14 = $daybegin+15*60*60;
        $t15 = $daybegin+16*60*60;
        $t16 = $daybegin+17*60*60;
        $t17 = $daybegin+18*60*60;
        $t18 = $daybegin+19*60*60;
        $t19 = $daybegin+20*60*60;
        $t20 = $daybegin+21*60*60;
        $t21 = $daybegin+22*60*60;
        $t22 = $daybegin+23*60*60;
        $dayend=$daybegin+86400;
        
        //0时段
        if($now <=$t0){
            return 0;
        }
        if($now <=$t1){
            return 1;
        }
        if($now <=$t2){
            return 2;
        }
        if($now <=$t3){
            return 3;
        }
        if($now <=$t4){
            return 4;
        }
        if($now <=$t5){
            return 5;
        }
        if($now <=$t6){
            return 6;
        }
        if($now <=$t7){
            return 7;
        }
        if($now <=$t8){
            return 8;
        }
        if($now <=$t9){
            return 9;
        }
        if($now <=$t10){
            return 10;
        }
        if($now <=$t11){
            return 11;
        }
        if($now <=$t12){
            return 12;
        }
        if($now <=$t13){
            return 13;
        }
        if($now <=$t14){
            return 14;
        }
        if($now <=$t15){
            return 15;
        }
        if($now <=$t16){
            return 16;
        }
        if($now <=$t17){
            return 17;
        }
        if($now <=$t18){
            return 18;
        }
        if($now <=$t19){
            return 19;
        }
        if($now <=$t20){
            return 20;
        }
        if($now <=$t21){
            return 21;
        }
        if($now <=$t22){
            return 22;
        }
        if($now <=$dayend){
            return 23;
        }
        
        LogApi::logProcess('getHour:*****************没有找到时段');
        return 0;
    }
    
    public function getConfValue(){
        $hour = $this->getHour();
        $yday = date("Y-m-d",strtotime("-1 day"));
        $sql = "select * from cms_manager.anchro_stand_time_point_compte where hour=$hour and create_time='$yday'";
        
        $rows = $this->getDbChannellive()->query($sql);
                
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $configVal = $row['configVal'];
            $computVal = $row['computVal'];
            
            if(!empty($configVal)){
                return $configVal;
            }
            
            if(!empty($computVal)){
                return $computVal;
            }
        }else{
            LogApi::logProcess('getConfValue:**exe sql error***************sql:'.$sql);
        }
        
        return 10000;
    }
    public function getDefaultTargetHotValue(){
        $value = 1800;// default value.
        $DefaultTargetHotValueId = 91;
        $sql = "select parm1 from card.parameters_info where id=$DefaultTargetHotValueId";
    
        $rows = $this->getDbChannellive()->query($sql);
    
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $parm1 = $row['parm1'];
    
            if(!empty($parm1)){
                $value = (int)$parm1;
            }
        }else{
            LogApi::logProcess('getConfValue:**exe sql error***************sql:'.$sql);
        }
    
        return $value;
    }
    //停止直播
    public function stopPlayer(&$return, $sid, $singerUid){
        LogApi::logProcess("停止直播XXXXXXXXXXXXXX:sid=$sid , singerid=$singerUid");
        // 主播停播后，将数据移到开播历史记录表里
//         $sql = "INSERT INTO channellive.live_record(sid, cid, uid, start_time, num, time, ip, type, game_id, live_peak, theme)" .
//             " SELECT sid, cid, uid, start_time, num, time, ip, type, game_id, live_peak, theme FROM live_notify WHERE sid=$sid and uid=$singerUid";
        
//         $flag = $this->getDbChannellive()->query($sql);
//         LogApi::logProcess("把本场直播数据插入到播放历史表中：sql:$sql");
//         if($flag){
//             //删除主播开播记录
//             $sql = "delete from channellive.live_notify WHERE sid=$sid and uid=$singerUid";
//             $flag = $this->getDbChannellive()->query($sql);
//             LogApi::logProcess("执行删除本场直播数据：sql:$sql");
//             if(!$flag){
//                 LogApi::logProcess("stopPlayer 执行删除本场直播数据 is error. sql:$sql");
//             }
            
//         }else{
//             LogApi::logProcess("stopPlayer 把本场直播数据插入到播放历史表中 is error. sql:$sql");
//         }
        
        $key = "singer_hot";
        $field = "singer_hot:$singerUid";
        
        $value = $this->getRedisMaster()->hGet($key, $field);
        
        $data = json_decode($value, TRUE);
        $data['stop_time'] = time();
        
        LogApi::logProcess("end 停止直播XXXXXXXXXXXXXX:key:$key field:$field data:".json_encode($data));
        
        $this->getRedisMaster()->hSet($key, $field, json_encode($data));
           
        // 维护直播间用户互动游戏完整
        $gulm = new GameUserLaunchModel();
        $gulm->SingerLeave($sid, $singerUid);
        //
        $gameModel = new GameModel();
        $gameModel->ClearPreheatingInfo($singerUid);
        //该接口移动到 channel_api.php 主播离场 on_p_user_real_leave_channel_event
        //// 帮派夺旗事件触发
        //$ff_model = new flag_faction_model();
        //$ff_model->event_singer_room_leave(&$return, $singerUid, $sid);
    }
    
    //主播开始直播
    public function initSingerHotPoint($singerUid){
        $key = "singer_hot";
        $field = "singer_hot:$singerUid";
        
        $now = time();
        $data = array();
        $data['uid'] = $singerUid;
        $data['start_time'] = $now;
        $data['update_time'] = $now;
        $data['stop_time'] = 0;
        $data['last_1min'] = $now;
        
        $this->getRedisMaster()->hset($key, $field, json_encode($data));
        
        $key = "singer_hot_value_top";
        $value = $this->getRedisMaster()->zScore($key, $singerUid);
        if(!empty($value)){
            LogApi::logProcess("initSingerHotPoint:: singerid:$singerUid is exist value:$value.");
            return;
        }
        
        $key = "singer_hot_value_top";
        $top1 = $this->getRedisMaster()->zrevrange($key, 0, 0);
                
        $hotValue = 0;
        if(!empty($top1)){
            
            $confValue = $this->getDefaultTargetHotValue();
            
            $topUid = 0;
            foreach ($top1 as $top){
                $topUid = $top;
                break;
            }
            
            $topValue = $this->getRedisMaster()->zscore($key, $topUid); 
			$score_level = $this->get_singer_score_coefficient($singerUid);
			$b = $score_level['coefficient'];
			
            $hotValue = $topValue - $b * $confValue;
            $hotValue = $hotValue > 0 ? $hotValue : 0;
            
            LogApi::logProcess("ChannelLiveModel:initSingerHotPoint uid:$singerUid top1:" . json_encode($top1) . " top1_value:$topValue confValue:$confValue, b:$b, hotValue:$hotValue");
        } else {
            LogApi::logProcess("ChannelLiveModel:initSingerHotPoint uid:$singerUid is top1");
        }
        
        //增加主播热度
        $this->addHotValue($singerUid, $hotValue);
        $this->set_new_start_point($singerUid, $hotValue);
        
        $model_hot_rank = new hot_rank_model();
        $model_hot_rank->update_rank_score($singerUid);
    }

    public function getSingerBackstageScore($singerUid){
    	$key = "h_anchor_grade:" . $singerUid % 1024;
    	$field = $singerUid . "";
    	$redis = $this->getRedisMaster();
    	
    	$value = $redis->hGet($key, $field);
    	
    	if ($value === false || $value === null) {
    		$value = 50;// default 50
    	} else {
    		$value = intval($value);
    	}

        return $value;
    }
    public function addSayHotPoint($singerUid, $uid){
        $key = "singer_say_hot:$singerUid";
        $field = "singer_say_hot:$uid";
        $add_point = 5;
        $value = $this->getRedisMaster()->hGet($key, $field);
        if(empty($value)){
            $this->getRedisMaster()->hSet($key, $field, $add_point);
        }else{
            $value = $value+$add_point;
            $this->getRedisMaster()->hSet($key, $field, $value);
        }        
    }
    //主播增加新星值
    public function addNewPoint($singerUid, $hotPoint){
        $value = 0;
        //$key = "singer_new_point_top";
        //$this->getRedisMaster()->zIncrBy($key, $hotPoint, $singerUid);
        //新星数值
        $key = ChannelLiveModel::ZsetSingerNewPointTopValueKey();
        $value = $this->getRedisMaster()->zIncrBy($key, (double)$hotPoint, $singerUid);
        return $value;
    }
    
    
    //主播增加积分值
    public function addHotPoint($singerUid, $hotPoint){
        $value = 0;
        $key = "singer_hot_point_top";
        $value = $this->getRedisMaster()->zIncrBy($key, (double)$hotPoint, $singerUid);
        
        //积分榜
        $key = "singer_hot_point_top_tmp";
        $this->getRedisMaster()->zIncrBy($key, (double)$hotPoint, $singerUid);
        return $value;
    }
    
    //主播增加热度值
    public function addHotValue($singerUid, $value){
        $key = "singer_hot_value_top";
        $this->getRedisMaster()->zAdd($key, (double)$value, $singerUid);
    }
    
    public function incrHotValue($singer_id, $value){
    	$key = 'singer_hot_value_top';
    	$this->getRedisMaster()->zIncrBy($key, $value, $singer_id);
    }
    
    public function addStarPoint($singerUid, $point){
        $key = "singer_hot_point_top";
        $this->getRedisMaster()->zIncrBy($key, (double)$point, $singerUid);
    }
    
    // 抑制期热度计算
    public function muffleTimeHotCalculate($singer_id, $historyHotValue, $point, $ratio, $proportion = 1)
    {
        $real_point = $point > 3000 ? 3000 : $point;
        $hotValue = $historyHotValue*0.935+$real_point*0.2*$ratio['ratio'] * $proportion;
        LogApi::logProcess("ChannelLiveModel:muffleTimeHotCalculate uid:$singer_id point:$point ($historyHotValue*0.935+$real_point*0.2*". $ratio['ratio'] . "*$proportion) hotValue:$hotValue");
        return $hotValue;
    }
    // 普通期热度计算
    public function commonTimeHotCalculate($singer_id, $historyHotValue, $point, $proportion = 1)
    {
        $real_point = $point > 3000 ? 3000 : $point;
        $hotValue = $historyHotValue*0.978+$real_point*0.77*$proportion;
        LogApi::logProcess("ChannelLiveModel:commonTimeHotCalculate uid:$singer_id point:$point ($historyHotValue*0.978+$real_point*0.77*$proportion) hotvalue:$hotValue");
        return $hotValue;
    }
    // 主播评分修正表
    public function singerScoreFix($value)
    {
    	$b = 0;
    	$item = $this->anchor_score_coefficient_conf();
    	
    	if (!empty($item)) {
    		$i = 1;
    		while (!empty($item[$i])) {
    			if ($value >= $item[$i]['min'] && $value < $item[$i]['max']) {
    				$b = $item[$i]['coefficient'];
    				break;
    			}
    			$i++;
    		}
    	}
    	
    	return $b;
    	

        //[0,30）	18.8	33
        //[30,50）	14.5	27
        //[50,70）	10	    20
        //[70,90）	6	    13
        //[90,105）	4.4	    10 
//         if (0 <= $value && $value < 30)
//         {
//             $b = 18.8;
//         }
//         else if (30 <= $value && $value < 50)
//         {
//             $b = 14.5;
//         }
//         else if (50 <= $value && $value < 70)
//         {
//             $b = 10;
//         }
//         else if (70 <= $value && $value < 90)
//         {
//             $b = 6;
//         }
//         else if (90 <= $value && $value <= 105)
//         {
//             $b = 4.4;
//         }
//         else 
//         {
//             $b = 4.4;
//         }
//         return $b;
    }
    // 礼物价值热榜积分表
    public function giftHotScore($value)
    {
        //[0，100]	     180
        //[100，500]	 450
        //[500，1000]	 900
        //[1000，9900]	 1800     10000 -> 9900
        //[9900，∞] 	 3600     10000 -> 9900
        $b = 0;
        if (0 <= $value && $value < 100)
        {
            //$b = 180;
            $b = 3;
        }
        else if (100 <= $value && $value < 500)
        {
            //$b = 450;
            $b = 60;
        }
        else if (500 <= $value && $value < 1000)
        {
            //$b = 900;
            $b = 500;
        }
        else if (1000 <= $value && $value < 9900)
        {
            //$b = 1800;
            $b = 1000;
        }
        else 
        {
            //$b = 3600;
            $b = 3000;
        }
        return $b;
    }
    // 礼物价值新星积分表
    public function giftNewScore($value)
    {
        //[0，100]	     180
        //[100，500]	 450
        //[500，1000]	 900
        //[1000，10000]	 1800
        //[10000，∞] 	 3600       
        $b = 0;
        if (0 <= $value && $value < 100)
        {
            //$b = 180;
            $b = 3;
        }
        else if (100 <= $value && $value < 500)
        {
            //$b = 450;
            $b = 60;
        }
        else if (500 <= $value && $value < 1000)
        {
            //$b = 900;
            $b = 500;
        }
        else if (1000 <= $value && $value < 10000)
        {
            //$b = 1800;
            $b = 1000;
        }
        else 
        {
            //b = 3600;
            $b = 3000;
        }
        return $b;        
    }
    public function GetSingerGiftLevel($uid)
    {
        $userAttrModel = new UserAttributeModel();
        $singerAttr = $userAttrModel->getAttrByUid($uid);
        $singerLevel = $singerAttr['experience_level'];
        LogApi::logProcess("GetSingerGiftLevel uid:".$uid." singerLevel:".$singerLevel);
        return $singerLevel;
    }
    
    // 送礼物获取热榜积分
    public function giftHotPoint($singerUid,$giftValue)
    {
        $hotPoint = $this->giftHotScore($giftValue);
        $this->addHotPoint($singerUid, $hotPoint);
    }
    // 送礼物获取新星积分
    public function giftNewPoint($singerUid,$giftValue)
    {
        $this->InitConfigDB();
        $SingerGiftLevel = $this->GetSingerGiftLevel($singerUid);
        if ($SingerGiftLevel < $this->new_point_lvl_limit)
        {
            $newPoint = $this->giftNewScore($giftValue);
            $this->addNewPoint($singerUid, $newPoint);
        }
    }

    public function updateWatchTime($uid, $sid, $now, $data)
    {
        LogApi::logProcess("updateWatchTime start uid:$uid sid:$sid now:$now data:".json_encode($data));
        $last_uptime = $data['last_uptime_watch'];
        $start_time = $data['start_time'];
        
        if (empty($last_uptime) && empty($start_time)) {
            return false;
        }

        if (empty($last_uptime)) {
            $last_uptime = $start_time;
        }

        // 3分钟更新
        if ($now - $last_uptime > 180) {
            $daybegin=strtotime(date("Ymd", $now));
            $dayend=$daybegin+86400;

            $length = $now - $last_uptime;
            
            // PEnterChannel 比UpdateChannelLiveInfo晚,会出现$length 特别大的异常情况
            // 由于离场时，已经处理了观看时长，所以出现异常情况时，直接丢弃
            $length = $length>225 ? 0 : $length;
            
            $data['last_uptime_watch'] = $now;
            $sql = "update channellive.user_live_notify set total_time_length=(total_time_length+$length) where start_time<$dayend and start_time>= $daybegin and sid = $sid and uid = $uid";
            $rows = $this->getDbChannellive()->query($sql);
            if (!$rows) {
                LogApi::logProcess("updateWatchTime :: exe sql error, sql:$sql");
                return false;
            }

            $key = member_list::UserWatchRoomTimeKey($uid);
            $this->getRedisMaster()->set($key, json_encode($data));
            LogApi::logProcess("updateWatchTime save uid:$uid sid:$sid now:$now len:$length data:".json_encode($data));

            return true;
        }

        return false;
    }
    
    function createSunSingerPlay($singerId, $sid, $time)
    {
    	$this->loadSingerSunConf();
    	
    	$return = array (
    			'flag' => true,
    			'sun_num' => ChannelLiveModel::$SINGER_SUN_COUNT
    	);
    	
    	$flag = false;
    	$today = date("Ymd", $time);
    	$key = "hash:createsun:$today:singer:$singerId";
    	$result = $this->getRedisMaster()->hGetAll($key);
    	
    	if (empty($result)) {
    		$values = array (
    				'singer_id' => $singerId,
    				'sid' => $sid,
    				'sun_times' => 0,
    				'last_uptime' => $time,
    				'since_last_sun' => 0
    		);
    		$this->getRedisMaster()->hMset($key, $values);
    		$this->getRedisMaster()->expire($key, 24*60*60);
    	} else if ((int)$result['sun_times'] >= ChannelLiveModel::$SINGER_SUN_TIMES_MAX) {
    		// do nothing
    	} else {
    		$totalTime = (int)$result['since_last_sun'] + (int)$time - (int)$result['last_uptime'];
    		$values = array (
    				'last_uptime' => $time
    		);
    		if ($totalTime >= ChannelLiveModel::$SINGER_SUN_INTERVAL*60) {
    			$values['since_last_sun'] = 0;
    			$values['sun_times'] = (int)$result['sun_times'] + 1;
    			$flag = true;
    		} else {
    			$values['since_last_sun'] = $totalTime;
    		}
    		$this->getRedisMaster()->hMset($key, $values);
    	}
    	
    	$return['flag'] = $flag;
    	return $return;
    }
    
    function createSunAndSendMsg($singerId, $time, $sun_num)
    {
    	$value = array(
    		'uid' => $singerId,
    		'num' => $sun_num
    	);
    	
    	$key = "hash:sun:singer:$singerId:unreceived";
    	$field = $time . "";
    	
    	$this->getRedisMaster()->hSet($key, $field, json_encode($value));
    	
    	// send msg
        $content = '<font color="#8ca0c8">系统消息:&nbsp; 主播在直播中幸运的获得了' . $sun_num . '阳光</font><br /><font color="#ffe184"><u>点击领取</u></font>';
        //$content = '<span style="color: #8ca0c8">系统消息: 主播在直播中幸运的获得了300阳光<br/><font color=#ffe184><u>点击领取</u></font></span>';
        $summary = "主播在直播中幸运的获得了" . $sun_num . "阳光";
        //$ucontent = '<span style="color: #8ca0c8">系统消息: 主播在直播中幸运的获得了300阳光</span>';
        $ucontent = '<font color="#8ca0c8">系统消息:&nbsp; 主播在直播中幸运的获得了' . $sun_num . '阳光</font>';
    	$msg = array(
    			'group_id' => $singerId,
    			'content' => array(
    					'type' => 1,
    					'text' => '版本过低，请升级最新版查看！',
                        'msgs' => array(
                            0 => array(
                                'content' => $content,
                                'visable' => array(0 => 2),
                                'click' => array(
                                    'key' => $key,
    							    'field' => $field
                                )
                            ),
                            1 => array(
                                'content' => $ucontent,
                                'visable' => array(
                                    0 => 1,
                                    1 => 3,
                                    2 => 4,
                                    3 => 5,
                                    4 => 6,
                                    5 => 7,
                                    6 => 8,
                                    7 => 9
                                )
                            )
                        ),
                        'summary' => $summary
    			)
    	);
    	
    	$tmpKey = "zbsunmsg:$singerId" . ":" . $time;
    	$this->getRedisMaster()->set($tmpKey, json_encode($msg));
    	
    	$url = GlobalConfig::GetSendGrpMsgURL() . $tmpKey;
    	$ch = curl_init();
    	$curl_opt = array(
    			CURLOPT_URL => $url,
    			CURLOPT_RETURNTRANSFER => true,
    			CURLOPT_TIMEOUT_MS => 1000
    	);
    	curl_setopt_array($ch, $curl_opt);
    	$data = curl_exec($ch);
    	curl_close($ch);
    }
    
    // 获取主播热度排名
    public function getHotRank($singer_id)
    {
    	$key = "singer_hot_value_top";
    	return $this->getRedisMaster()->zRevRank($key, $singer_id);    	
    }
    
    public function anchor_score_coefficient_conf()
    {
    	$item = array();
    	
    	$key = "h_anchor_score_coefficient_conf";
    	$redis = $this->getRedisMaster();
    	 
    	$ret = $redis->hGetAll($key);
    	 
    	if (!empty($ret)) {
    		foreach ($ret as $field=>$value) {
    			$item[(int)$field] = json_decode($value, true);
    		}
    		 
    		return $item;
    	}
    	 
    	$sql = "SELECT * FROM cms_manager.anchor_score_coefficient";
    	 
    	$db_card = $this->getDbMain();
    	$rows = $db_card->query($sql);
    	 
    	if (!empty($rows)) {
    		$row = null;
    		$row = $rows->fetch_assoc();
    		 
    		while (!empty($row)) {
    			$item[(int)$row['level']] = $row;
    			$redis->hSet($key, $row['level'] . "", json_encode($row));
    			$row = $rows->fetch_assoc();
    		}
    	}
    	 
    	return $item;
    }
    
    public function get_singer_score_coefficient($singer_id)
    {
    	// get singer score level
    	$redis = $this->getRedisMaster();
    	$key = "anchor:score:$singer_id";
    	$level = $redis->get($key);
    	
    	if (empty($level)) {
    		$level = 2;
    	}
    	
    	return $this->get_anchor_score_coefficient_conf_with_level($level);
    }
    
    public function get_anchor_score_coefficient_conf_with_level($level)
    {
    	$res = array();
    	
    	$key = "h_anchor_score_coefficient_conf";
    	$redis = $this->getRedisMaster();
    	
    	$ret = $redis->hGet($key, $level . '');
    	
    	if (!empty($ret)) {
    		$res = json_decode($ret, true);
    		return $res;
    	}
    	
    	$sql = "SELECT * FROM cms_manager.anchor_score_coefficient WHERE level=$level";
    	
    	$db_card = $this->getDbMain();
    	$rows = $db_card->query($sql);
    	
    	if ($rows && $rows->num_rows > 0) {
    		$res = $rows->fetch_assoc();
    		if (!empty($res)) {
    			$redis->hSet($key, $res['level'] . "", json_encode($res));
    		}
    	}
    	
    	if (empty($res)) {
    		$res['level'] = 2;
    		$res['min'] = 30;
    		$res['max'] = 50;
    		$res['coefficient'] = 15.2;
    		$res['proportion'] = 0.9;
    	}
    	
    	return $res;
    }
    
    public function get_hot_value($singerUid)
    {
    	$key = "singer_hot_value_top";
    	return $this->getRedisMaster()->zScore($key, $singerUid. '');
    }

    public function init_new_star_point($singer_id)
    {
        $sys_param = new SysParametersModel();
        $lvl_limit = $sys_param->GetSysParameters(113, 'parm1');
        $lvl_anchor = $this->GetSingerGiftLevel($singer_id);

        if ($lvl_anchor >= $lvl_limit) {
            $this->clear_new_star_point($singer_id);
            LogApi::logProcess("ChannelLiveModel:init_new_star_point:: anchor level out of limit. anchor_id:$singer_id anchor_lvl:$lvl_anchor limit_lvl:$lvl_limit");
            return;
        }

        $key = $this->ZsetSingerNewPointTopValueKey();
        $value = $this->getRedisMaster()->zScore($key, $singer_id);
        if(!empty($value)){
            LogApi::logProcess("ChannelLiveModel:init_new_star_point:: singerid:$singer_id is exist. value:$value");
            return;
        }
        
        $top1 = $this->getRedisMaster()->zrevrange($key, 0, 0);
        
        LogApi::logProcess("ChannelLiveModel:init_new_star_point singerid:$singer_id 当前新星榜第一主播:".json_encode($top1));
        
        $hotValue = 0;
        if(!empty($top1)){
            
            $confValue = $this->getDefaultTargetHotValue();
            
            $topUid = 0;
            foreach ($top1 as $top){
                $topUid = $top;
                break;
            }
            
            $topValue = $this->getRedisMaster()->zscore($key, $topUid); 
            LogApi::logProcess("ChannelLiveModel:init_new_star_point 当前新星榜第一主播的新星值:$topValue");

            $score_level = $this->get_singer_score_coefficient($singer_id);
            $b = $score_level['coefficient'];
            
            $hotValue = $topValue - $b * $confValue;
            $hotValue = $hotValue > 0 ? $hotValue : 0;
            
            LogApi::logProcess("ChannelLiveModel:init_new_star_point confValue:$confValue, b:$b, hotValue:$hotValue");
        }
        
        //增加新星
        $this->getRedisMaster()->zAdd($key, (double)$hotValue, $singer_id);        
    }

    public function clear_new_star_point($singer_id)
    {
        $key = $this->ZsetSingerNewPointTopValueKey();
        $this->getRedisMaster()->zrem($key, $singer_id);
    }

    public function add_new_star_point($singer_id, $value)
    {
        $ret = 0;

        $sys_param = new SysParametersModel();
        $lvl_limit = $sys_param->GetSysParameters(113, 'parm1');

        $lvl_anchor = $this->GetSingerGiftLevel($singer_id);

        LogApi::logProcess("ChannelLiveModel:add_new_star_point singer_id:$singer_id value:$value lvl_limit:$lvl_limit lvl_anchor:$lvl_anchor");

        if ($lvl_anchor < $lvl_limit) {
            $ret = $this->addNewPoint($singer_id, $value);
        } else {
            $this->clear_new_star_point($singer_id);
            $ret = -1;
        }

        return $ret;
    }

    public function set_new_start_point($singer_id, $value)
    {
        $ret = 0;

        $sys_param = new SysParametersModel();
        $lvl_limit = $sys_param->GetSysParameters(113, 'parm1');

        $lvl_anchor = $this->GetSingerGiftLevel($singer_id);

        LogApi::logProcess("ChannelLiveModel:set_new_start_point singer_id:$singer_id value:$value lvl_limit:$lvl_limit lvl_anchor:$lvl_anchor");

        if ($lvl_anchor < $lvl_limit) {
            $key = ChannelLiveModel::ZsetSingerNewPointTopValueKey();
            $value = $this->getRedisMaster()->zAdd($key, (double)$value, $singer_id);
        } else {
            $this->clear_new_star_point($singer_id);
            $ret = -1;
        }

        return $ret;
    }

    public function update_popularity($sid, $number)
    {
        $key = "h_user_faker_populary:" . ($sid % 1024); 
        $this->getRedisMaster()->hSet($key, $sid, $number);
    }
}
?>
