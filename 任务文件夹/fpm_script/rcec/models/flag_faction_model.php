<?php

class flag_faction_model extends ModelBase
{
    public static $USER_MOD_NUMBER = 1024;
    // (cache)EXPIRE 3 * 24 * 60 * 60 = 259200(s)
    public static $CACHE_KEY_EXPIRE = 259200;
    
    public static $CONST_PARM_ID_BASE_FLAG_NUMBER = 156;
    public static $CONST_PARM_ID_FLAG_TIME_LENGTH = 157;
    public static $CONST_PARM_ID_FLAG_SUCCESS_PLUS = 243;
    
    public static $op_status_clean = 0;// 清除旗位
    public static $op_wins_success = 1;// 夺旗成功
    public static $op_wins_failure = 2;// 夺旗失败
    
    // 帮会夺旗,每分钟每人算一次护旗计数时间点数据
    public $ff_task_01minute_per_number = 60;// 秒 60
    // 帮会夺旗,守旗10分钟参与守旗的成员每人算一次插旗计数
    public $ff_task_10minute_per_number = 600;// 秒 10 * 60
    
    // 帮会等级缓存数据过期时间，超过1分钟后缓存数据过期，重新mysql取数据
    public static $bangpai_cash_time = 60;// 秒 1 * 60    
    
    public function __construct ()
    {
        parent::__construct();
    }
    // 帮会夺旗基础缓存数据
    public static function redis_hash_faction_flag_base_info_key($faction_id)
    {
        $mod = $faction_id % flag_faction_model::$USER_MOD_NUMBER;
        return "faction:flag:base_info:$mod";
    }
    // 帮会插旗人数数据
    public static function redis_zset_faction_flag_number_info_key($sid)
    {
        return "faction:flag:number_info:$sid";
    }
    // 帮会插旗成员数据
    public static function redis_zset_faction_flag_member_info_key($sid, $faction_id)
    {
        return "faction:flag:member_info:$sid:$faction_id";
    }
    // 帮会插旗当前占旗数据
    public static function redis_hash_faction_flag_current_info_key($sid)
    {
        $mod = $sid % flag_faction_model::$USER_MOD_NUMBER;
        return "faction:flag:current_info:$mod";
    }
    // 帮会插旗夺旗动作时间数据
    public static function redis_zset_faction_flag_action_info_key($sid)
    {
        return "faction:flag:action_info:$sid";
    }
    // 帮会夺旗,每分钟每人算一次护旗计数时间点数据
    public static function redis_hash_faction_flag_task_members_01minute_time($uid)
    {
        $mod = $uid % flag_faction_model::$USER_MOD_NUMBER;
        return "faction:flag:task:protect:1minute:time:$mod";
    }
    // 帮会夺旗,守旗10分钟参与守旗的成员每人算一次插旗计数
    public static function redis_hash_faction_flag_task_faction_10minute_time($sid)
    {
        $mod = $sid % flag_faction_model::$USER_MOD_NUMBER;
        return "faction:flag:task:faction_flag:10minute:time:$mod";
    }  
    
    public function task_faction_members_update(&$error, &$return, $sid, $timecode_now)
    {
        do 
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $faction_current = array();
            $flg_info_faction_id = 0;
            $flg_info_timecode = 0;
            $this->redis_room_get_current_info(&$error, &$faction_current, $sid);
            $flg_info_faction_id = $faction_current['faction_id'];
            $flg_info_timecode = $faction_current['timecode'];
            if (0 >= $flg_info_faction_id)
            {
                // need do nothing.
                break;
            }
            $room_member_info_key = flag_faction_model::redis_zset_faction_flag_member_info_key($sid, $flg_info_faction_id);
            // 获取所有护旗成员.
            $members = $redis->zRange($room_member_info_key, 0, -1, true);
            if (true == empty($members))
            {
                // need do nothing.
                break;
            }
            // 夺旗任务1: 帮会夺旗,守旗10分钟参与守旗的成员每人算一次插旗计数
            // 当前护旗的帮派
            $this->task_faction_member_update(&$error, &$return, $members, $flg_info_faction_id, $sid, $flg_info_timecode, $timecode_now);
            
            // 夺旗任务2: 帮会夺旗,每分钟每人算一次护旗计数时间点数据
            foreach ($members as $member => $timecode)
            {
                // 当前护旗的选手
                $this->task_members_member_update(&$error, &$return, $members, $member, $sid, $timecode, $timecode_now);
            }
        }while (0);
    }
    // 更新参与护旗的用户任务情况.
    public function task_members_member_update(&$error, &$return, $members, $uid, $sid, $timecode_start, $timecode_now)
    {
        do 
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $ff_task_members_timecode_key = flag_faction_model::redis_hash_faction_flag_task_members_01minute_time($uid);
            $timecode_last = $redis->hGet($ff_task_members_timecode_key,$uid);
            
            if (empty($timecode_last)){$timecode_last = $timecode_start;}
            $timecode_later = $timecode_start > $timecode_last ? $timecode_start : $timecode_last;
            
            LogApi::logProcess("flag_faction_model.task_members_member_update"." uid:".$uid." sid:".$sid." timecode_now:".$timecode_now." timecode_start:".$timecode_start." timecode_last:".$timecode_last." timecode_later:".$timecode_later);
        
            // dt time to total.
            $dt = $timecode_now - $timecode_later;
            if (0 < $dt)
            {
                $n_a = floor((int)$dt / $this->ff_task_01minute_per_number);
                $n_b = floor((int)$dt % $this->ff_task_01minute_per_number);
                $real_dt = $n_a * $this->ff_task_01minute_per_number;
                $timecode_curr = $timecode_later + $real_dt;
                $redis->hSet($ff_task_members_timecode_key,$uid,$timecode_curr);
                $redis->expire($ff_task_members_timecode_key,flag_faction_model::$CACHE_KEY_EXPIRE);
                // append item $n_a.
                $this->task_counter_members_append(&$error, &$return, $members, $uid, $sid, $n_a, $timecode_now);
            
                LogApi::logProcess("flag_faction_model.task_members_member_update"." uid:".$uid." sid:".$sid." n_a:".$n_a." dt:".$dt." ff_task_01minute_per_number:".$this->ff_task_01minute_per_number." n:".$dt / $this->ff_task_01minute_per_number);
            }
        }while (0);
    }
    public function task_faction_member_update(&$error, &$return, $members, $faction_id, $sid, $timecode_start, $timecode_now)
    {
        do 
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $ff_task_faction_timecode_key = flag_faction_model::redis_hash_faction_flag_task_faction_10minute_time($sid);
            $timecode_last = $redis->hGet($ff_task_faction_timecode_key,$sid);
            
            if (empty($timecode_last)){$timecode_last = $timecode_start;}
            $timecode_later = $timecode_start > $timecode_last ? $timecode_start : $timecode_last;
            
            LogApi::logProcess("flag_faction_model.task_faction_member_update"." faction_id:".$faction_id." sid:".$sid." timecode_now:".$timecode_now." timecode_start:".$timecode_start." timecode_last:".$timecode_last." timecode_later:".$timecode_later);
        
            // dt time to total.
            $dt = $timecode_now - $timecode_later;
            if (0 < $dt)
            {
                $n_a = floor((int)$dt / $this->ff_task_10minute_per_number);
                $n_b = floor((int)$dt % $this->ff_task_10minute_per_number);
                $real_dt = $n_a * $this->ff_task_10minute_per_number;
                $timecode_curr = $timecode_later + $real_dt;
                $redis->hSet($ff_task_faction_timecode_key,$faction_id,$timecode_curr);
                $redis->expire($ff_task_faction_timecode_key,flag_faction_model::$CACHE_KEY_EXPIRE);
                // append item $n_a.
                $this->task_counter_faction_append(&$error, &$return, $members, $faction_id, $sid, $n_a, $timecode_now);
            
                LogApi::logProcess("flag_faction_model.task_faction_member_update"." faction_id:".$faction_id." sid:".$sid." n_a:".$n_a." dt:".$dt." ff_task_01minute_per_number:".$this->ff_task_10minute_per_number." n:".$dt / $this->ff_task_10minute_per_number);
            }
        }while (0);
    }
    public function task_counter_members_append(&$error, &$return, $members, $uid, $sid, $number, $timecode)
    {
        $this->task_counter_faction_34_append(&$error, &$return, $uid, $sid, $number, $timecode);
    }
    public function task_counter_faction_append(&$error, &$return, $members, $faction_id, $sid,$number, $timecode)
    {
        foreach ($members as $member => $timecode)
        {
            // 当前护旗的选手
            $this->task_counter_faction_40_append(&$error, &$return, $member, $sid, $number, $timecode);
        }
    }
    // 任务34: 帮会夺旗,每分钟每人算一次护旗计数时间点数据
    public function task_counter_faction_34_append(&$error, &$return, $uid, $sid, $number, $timecode)
    {
        $d = array();
        $d['extra_param'] = 0;
        $d['num'] = $number;
        $d['target_type'] = 34;
        $d['uid'] = $uid;
        
        $message = array();
        $message['data'] = $d;
        $message['broadcast'] = 5;
        
        $return[] = $message;
    }
    // 任务40: 帮会夺旗,守旗10分钟参与守旗的成员每人算一次插旗计数
    public function task_counter_faction_40_append(&$error, &$return, $uid, $sid, $number, $timecode)
    {
        $d = array();
        $d['extra_param'] = 0;
        $d['num'] = $number;
        $d['target_type'] = 40;
        $d['uid'] = $uid;
        
        $message = array();
        $message['data'] = $d;
        $message['broadcast'] = 5;
        
        $return[] = $message;
    }
    public function task_update(&$return, $sid)
    {
        $error = array();
        $timecode_now = time();
        $this->task_faction_members_update(&$error, &$return, $sid, $timecode_now);
        if (0 != $error['code'])
        {
            $code = $error['code'];
            $desc = $error['desc'];
            LogApi::logProcess("task_update error($code):$desc");
        }
    }
    public function mysql_get_faction_base_info(&$error, $faction_id, &$faction_name, &$faction_icon, &$faction_level)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do 
        {
            $query = "select union_name,imgurl,union_up_level from raidcall.union_info where id = $faction_id";
            $mysql = $this->getDbRaidcall();
            if(null == $mysql)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $rows = $mysql->query($query);
            if (null == $rows || 0 >= $rows->num_rows)
            {
                // query failure.
                // 100000101(101)执行存储过程失败
                $error['code'] = 100000101;
                $error['desc'] = '执行存储过程失败';
                break;
            }
            $row = $rows->fetch_assoc();
            $faction_name = (string)$row['union_name'];
            $faction_icon = (string)$row['imgurl'];
            $faction_level = (int)$row['union_up_level'];
            // 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    public function redis_get_faction_base_info(&$error, $faction_id, &$faction_name, &$faction_icon, &$faction_level,&$faction_time)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do 
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = flag_faction_model::redis_hash_faction_flag_base_info_key($faction_id);
            $faction_cache_string = $redis->hGet($key,$faction_id);
            if(true == empty($faction_cache_string))
            {
                // 100000103(103)执行命令失败
                $error['code'] = 100000103;
                $error['desc'] = '执行命令失败';
                break;
            }
            $faction_cache_elem = json_decode($faction_cache_string, true);
            if(true == empty($faction_cache_elem))
            {
                // 100000001(001)解包失败
                $error['code'] = 100000001;
                $error['desc'] = '解包失败';
                break;
            }
            $faction_name = $faction_cache_elem['faction_name'];
            $faction_icon = $faction_cache_elem['faction_icon'];
            $faction_level = $faction_cache_elem['faction_level'];
            $faction_time = $faction_cache_elem['faction_time'];
          
            // 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    public function redis_set_faction_base_info(&$error, $faction_id, &$faction_name, &$faction_icon, &$faction_level)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = flag_faction_model::redis_hash_faction_flag_base_info_key($faction_id);
        
            $faction_cache_elem = array();
            $faction_cache_elem['faction_name'] = $faction_name;
            $faction_cache_elem['faction_icon'] = $faction_icon;
            $faction_cache_elem['faction_level'] = $faction_level;
            $faction_cache_elem['faction_time'] = time();
           
            $redis->hSet($key,$faction_id,json_encode($faction_cache_elem));
            // 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    // 清除帮派基础数据的缓存数据
    public function redis_del_faction_base_info(&$error, $faction_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = flag_faction_model::redis_hash_faction_flag_base_info_key($faction_id);
            $redis->hDel($key,$faction_id);
            // 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    public function get_faction_base_info(&$error, $faction_id, &$faction_name, &$faction_icon, &$faction_level)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {   
            $faction_time=0;
            $this->redis_get_faction_base_info(&$error, $faction_id, &$faction_name, &$faction_icon, &$faction_level,&$faction_time);
            $faction_time_test=time();            
            if(0 == $error['code'] and $faction_time_test-$faction_time<flag_faction_model::$bangpai_cash_time)
            { 
                // 已经获取了数据
                //
                $error['code'] = 0;
                $error['desc'] = '';
                break; 
            }
            $this->mysql_get_faction_base_info(&$error, $faction_id, &$faction_name, &$faction_icon, &$faction_level);
            if(0 != $error['code'])
            {
                // 100000301(301)无效的参数
                $error['code'] = 100000301;
                $error['desc'] = '无效的参数';
                break;
            }
            // 将mysql数据缓存到redis中
            $this->redis_set_faction_base_info(&$error ,$faction_id, &$faction_name, &$faction_icon, &$faction_level);
        }while(0);
    }
    public function redis_user_flag_add(&$error, &$number, $uid, $sid, $faction_id, $timecode)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $number_info_key = flag_faction_model::redis_zset_faction_flag_number_info_key($sid);
            $member_info_key = flag_faction_model::redis_zset_faction_flag_member_info_key($sid, $faction_id);
            // 加入用户详情
            $number_front = $redis->zCard($member_info_key);
            $redis->zAdd($member_info_key, $timecode, $uid);
            $number_after = $redis->zCard($member_info_key);
            if ($number_front == $number_after)
            {
                // 403200004,//(004)不能重复加入夺旗
                $error['code'] = 403200004;
                $error['desc'] = '不能重复加入夺旗';
                break;
            }
            $number = $number_after;
            // 更新人数
            $redis->zAdd($number_info_key, $number, $faction_id);
            // 设置过期
            $redis->expire($member_info_key, flag_faction_model::$CACHE_KEY_EXPIRE);
            $redis->expire($number_info_key, flag_faction_model::$CACHE_KEY_EXPIRE);
            // 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    public function redis_user_flag_rmv(&$error, &$number, $uid, $sid, $faction_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $number_info_key = flag_faction_model::redis_zset_faction_flag_number_info_key($sid);
            $member_info_key = flag_faction_model::redis_zset_faction_flag_member_info_key($sid, $faction_id);
            // 移除用户详情
            $redis->zRem($member_info_key, $uid);
            $number = $redis->zCard($member_info_key);
            // 更新人数
            $redis->zAdd($number_info_key, $number, $faction_id);
            // 移除没必要设置过期
            // $redis->expire($member_info_key, flag_faction_model::$CACHE_KEY_EXPIRE);
            // $redis->expire($number_info_key, flag_faction_model::$CACHE_KEY_EXPIRE);
            // 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    public function redis_user_flag_get(&$error, &$number, $sid, $faction_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $member_info_key = flag_faction_model::redis_zset_faction_flag_member_info_key($sid, $faction_id);
            $number = $redis->zCard($member_info_key);
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    // $faction_current {uint32 faction_id,uint32 timecode}
    public function redis_room_get_current_info(&$error, &$faction_current, $sid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        
        $faction_current['faction_id'] = 0;
        $faction_current['timecode'] = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = flag_faction_model::redis_hash_faction_flag_current_info_key($sid);
            $faction_current_string = $redis->hGet($key,$sid);
            if (true == empty($faction_current_string))
            {
                // not find data.
                $faction_current['faction_id'] = 0;
                $faction_current['timecode'] = 0;
                //
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            $faction_current_object = json_decode($faction_current_string, true);
            if (true == empty($faction_current_object))
            {
                // decode error.
                // not find data.
                $faction_current['faction_id'] = 0;
                $faction_current['timecode'] = 0;
                //
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            
            $faction_current['faction_id'] = $faction_current_object['faction_id'];
            $faction_current['timecode'] = $faction_current_object['timecode'];
            // 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    // $faction_current {uint32 faction_id,uint32 timecode}
    public function redis_room_set_current_info(&$error, $faction_current, $sid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = flag_faction_model::redis_hash_faction_flag_current_info_key($sid);
            $redis->hSet($key,$sid,json_encode($faction_current));
            // 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    // 加入房间内某个帮派的夺旗动作
    public function redis_room_action_info_zadd(&$error, &$timecode_expire, $sid, $faction_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = flag_faction_model::redis_zset_faction_flag_action_info_key($sid);
            $redis->zAdd($key, $timecode_expire, $faction_id);
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    // 获取房间内某个帮派的夺旗动作超时时间点
    public function redis_room_action_info_zscore(&$error, &$timecode_expire, $sid, $faction_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = flag_faction_model::redis_zset_faction_flag_action_info_key($sid);
            $timecode_expire = $redis->zScore($key, $faction_id);
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    // 获取房间内某个帮派的夺旗动作超时时间点
    public function redis_room_action_info_near_timecode(&$error, &$timecode_expire, $sid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = flag_faction_model::redis_zset_faction_flag_action_info_key($sid);            
            $timecode_expire_array_near = $redis->zRange($key, 0, 0, true);
            if (true == empty($timecode_expire_array_near))
            {
                $timecode_expire = 0;
            }
            else 
            {
                // 取出唯一的元素
                $max_info_unit = each($timecode_expire_array_near);
                $timecode_expire = $max_info_unit['value'];
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    // 清除房间内某个帮派的夺旗动作
    public function redis_room_action_info_zrem(&$error, $sid, $faction_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = flag_faction_model::redis_zset_faction_flag_action_info_key($sid);
            $redis->zRem($key, $faction_id);
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    // 清除房间内所有的夺旗动作
    public function redis_room_action_info_del(&$error, $sid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = flag_faction_model::redis_zset_faction_flag_action_info_key($sid);
            $redis->del($key);
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    public function redis_get_faction_flag_full_info(&$error, &$faction_info, $faction_id, $sid)
    {
        $faction_info['faction_id'] = $faction_id;
        if (0 < $faction_id)
        {
            $faction_name = '';
            $faction_icon = '';
            $number = 0;
            $faction_level = 0;
            //
            $this->get_faction_base_info(&$error, $faction_id, &$faction_name, &$faction_icon, &$faction_level);
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            $this->redis_user_flag_get(&$error, &$number, $sid, $faction_id);
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            // 将帮派数据赋给当前插旗
            $faction_info['faction_name'] = $faction_name;
            $faction_info['faction_icon'] = $faction_icon;
            $faction_info['flag_number'] = $number;
            $faction_info['faction_level'] = $faction_level;
        }
    }
    public function redis_clear_room_cache(&$error, $sid)
    {
        if (0 < $sid)
        {
            $error['code'] = -1;
            $error['desc'] = '未知错误';
            do
            {
                $redis = $this->getRedisMaster();
                if(null == $redis)
                {
                    // 100000701(701)网络数据库断开连接
                    $error['code'] = 100000701;
                    $error['desc'] = '网络数据库断开连接';
                    break;
                }
                $number_info_key = flag_faction_model::redis_zset_faction_flag_number_info_key($sid);
                // 移除所有帮会插旗成员数据
                $faction_array = $redis->zRange($number_info_key, 0, -1);
                foreach ($faction_array as $faction_id)
                {
                    $member_info_key = flag_faction_model::redis_zset_faction_flag_member_info_key($sid, $faction_id);
                    $redis->del($member_info_key);
                }
                $redis->del($number_info_key);
                // 将本房间的夺旗状态置为未-1(开启夺旗)
                $faction_id = -1;
                $timecode_now = time();
                
                $faction_current = array();
                $faction_current['faction_id'] = $faction_id;
                $faction_current['timecode'] = $timecode_now;
                $this->redis_room_set_current_info(&$error, $faction_current, $sid);
                // 将本房间的夺旗动作状态清除
                $this->redis_room_action_info_del(&$error, $sid);
                //
                $error['code'] = 0;
                $error['desc'] = '';
            }while(0);
        }
    }
    public function handle_user_room_action(&$error, &$return, &$current, &$trigger, $uid, $sid, $faction_id, $flg_info_faction_id, $dt)
    {
        do 
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $timecode_now = time(null);
            
            $sys_parameters = new SysParametersModel();
            //
            $base_flag_number = $sys_parameters->GetSysParameters(flag_faction_model::$CONST_PARM_ID_BASE_FLAG_NUMBER, 'parm1');
            $base_time_length = $sys_parameters->GetSysParameters(flag_faction_model::$CONST_PARM_ID_FLAG_TIME_LENGTH, 'parm1');
            // 填充本房间的插旗帮派数据
            $this->redis_get_faction_flag_full_info(&$error, &$current, $flg_info_faction_id, $sid);
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            //
            if ($flg_info_faction_id == $faction_id)
            {
                // 处于守旗状态
                // 拷贝数据到自己的帮派字段
                $trigger['faction_id'] = $current['faction_id'];
                $trigger['faction_name'] = $current['faction_name'];
                $trigger['faction_icon'] = $current['faction_icon'];
                $trigger['flag_number'] = $current['flag_number'];
                $trigger['faction_level'] = $current['faction_level'];
            }
            else
            {
                // 获取触发本次事件的帮派数据
                $this->redis_get_faction_flag_full_info(&$error, &$trigger, $faction_id, $sid);
                if (0 != $error['code'])
                {
                    // 出现了一些逻辑错误
                    break;
                }
            }
            $number_info_key = flag_faction_model::redis_zset_faction_flag_number_info_key($sid);
            // 取分数最大的那个
            $max_info_faction_id = 0;
            $max_info_number     = 0;
            $faction_array_max = $redis->zRevRange($number_info_key, 0, 0, true);
            if (true == empty($faction_array_max))
            {
                // 结算出本次时间点的分数最大帮派
                $max_info_faction_id = 0;
                $max_info_number     = 0;
            }
            else 
            {
                // 取出唯一的元素
                $max_info_faction_id_unit = each($faction_array_max);
                // 结算出本次时间点的分数最大帮派
                $max_info_faction_id = $max_info_faction_id_unit['key'];
                $max_info_number     = $max_info_faction_id_unit['value'];
            }
            
            // 触发一次夺旗动作的检验
            // 插旗状态下比插旗阀值大
            // 夺旗状态下比本房间插旗帮派分数大
            LogApi::logProcess("handle_user_room_action uid:".$uid." sid:".$sid." dt:".$dt." current.flag_number:".$current['flag_number']." trigger.flag_number:".$trigger['flag_number']." base_flag_number:".$base_flag_number);

            // 当前房间无帮派且触发帮派人数大于等于基准数量                                               (插旗)
            // 当前帮派和触发帮派不同且触发帮派人数大于当前帮派                                           (夺旗)
            // 触发帮派为当前帮派且为人数减少事件且当前帮派人数小于基准帮派                               (清旗)
            if (( 0 == $flg_info_faction_id && $trigger['flag_number'] >= $base_flag_number) ||
                ( ($faction_id != $flg_info_faction_id) && ( 0  < $flg_info_faction_id && $trigger['flag_number'] >  $current['flag_number'])) ||
                ( ($faction_id == $flg_info_faction_id) && (0 > $dt) && ( 0  < $flg_info_faction_id && $trigger['flag_number'] < $base_flag_number)) )
            {
                $action_faction_id = $faction_id;
                // 尝试拿出触发帮派的结算超时时间
                $trigger_faction_action_timeout = 0;
                $this->redis_room_action_info_zscore(&$error, &$trigger_faction_action_timeout, $sid, $action_faction_id);
                if (false == empty($trigger_faction_action_timeout))
                {
                    // 已经在结算状态了,不能覆盖结算超时时间
                    break;
                }
                
                // 将超时时间点设置到缓存
                $timecode_expire = $timecode_now + $base_time_length;
                $this->redis_room_action_info_zadd(&$error, &$timecode_expire, $sid, $action_faction_id);
                //
                $nt = array();
                $nt['cmd'] = 'flag_action_nt';
                $nt['sid'] = $sid;
                $nt['uid'] = $uid;
                $nt['current'] = &$current;
                $nt['trigger'] = &$trigger;
                $nt['timelength'] = $base_time_length;
                $nt['timecode'] = $timecode_now;
            
                $return[] = array
                (
                    'broadcast' => 2,// 直播间通知
                    'data' => $nt,
                );
                LogApi::logProcess("handle_user_room_action uid:".$uid." nt:".json_encode($nt));
            }
            // 当触发帮派为当前帮派,动作为减少,当时最多人数的帮派不是当前帮派,人数小于当时最多人数的帮派  (夺旗)
            if ( (0 != $max_info_faction_id) && ($faction_id == $flg_info_faction_id) && (0 > $dt) && ($max_info_faction_id != $flg_info_faction_id) && ( $max_info_number > $current['flag_number']) )
            {
                $action_faction_id = $max_info_faction_id;
                // 尝试拿出触发帮派的结算超时时间
                $trigger_faction_action_timeout = 0;
                $this->redis_room_action_info_zscore(&$error, &$trigger_faction_action_timeout, $sid, $action_faction_id);
                if (false == empty($trigger_faction_action_timeout))
                {
                    // 已经在结算状态了,不能覆盖结算超时时间
                    break;
                }
                
                // 将超时时间点设置到缓存
                $timecode_expire = $timecode_now + $base_time_length;
                $this->redis_room_action_info_zadd(&$error, &$timecode_expire, $sid, $action_faction_id);
                //                
                $nt = array();
                $nt['cmd'] = 'flag_action_nt';
                $nt['sid'] = $sid;
                $nt['uid'] = $uid;
                $nt['current'] = &$current;
                $nt['trigger'] = &$trigger;
                $nt['timelength'] = $base_time_length;
                $nt['timecode'] = $timecode_now;
                
                $return[] = array
                (
                    'broadcast' => 2,// 直播间通知
                    'data' => $nt,
                );
                LogApi::logProcess("handle_user_room_action uid:".$uid." nt:".json_encode($nt));
            }
        }while (0);
    }
    public function event_user_room_enter(&$return, $uid, $sid)
    {
        // nothing.
    }
    public function event_user_room_leave(&$return, $uid, $sid)
    {
        LogApi::logProcess("event_user_room_leave uid:".$uid." sid:".$sid);
        $error = array();
        $number = 0;
        $u = new UserInfoModel();
        $user_info = $u->getInfoById($uid);
        $faction_id = $user_info['union_id'];
        if (0 < $faction_id)
        {
            do 
            {
                $current = array();
                $trigger = array();
                
                $current['faction_id'] = -1;
                $current['faction_name'] = '';
                $current['faction_icon'] = '';
                $current['flag_number'] = 0;
                $current['faction_level'] = 0;
                
                $trigger['faction_id'] = -1;
                $trigger['faction_name'] = '';
                $trigger['faction_icon'] = '';
                $trigger['flag_number'] = 0;
                $trigger['faction_level'] = 0;
                
                $faction_current = array();
                $flg_info_faction_id = 0;
                // 获取本房间的插旗帮派
                $this->redis_room_get_current_info(&$error, &$faction_current, $sid);
                $flg_info_faction_id = $faction_current['faction_id'];
                if (0 != $error['code'])
                {
                    // 出现了一些逻辑错误
                    break;
                }
                if (-1 == $flg_info_faction_id)
                {
                    // 403200003,//(003)在加入前需要先开启夺旗
                    $error['code'] = 403200003;
                    $error['desc'] = '在加入前需要先开启夺旗';
                    break;
                }
                
                // 将本用户从夺旗列表移除
                $this->redis_user_flag_rmv(&$error, &$number, $uid, $sid, $faction_id);
                // 做一次夺旗动作触发校验
                $this->handle_user_room_action(&$error, &$return, &$current, &$trigger ,$uid, $sid, $faction_id, $flg_info_faction_id, -1);
            
                $nt = array();
                $nt['cmd'] = 'flag_exit_nt';
                $nt['sid'] = $sid;
                $nt['uid'] = $uid;
                $nt['current'] = &$current;
                $nt['trigger'] = &$trigger;
                
                $return[] = array
                (
                    'broadcast' => 2,// 直播间通知
                    'data' => $nt,
                );
            }while (0);
        }
    }
    public function event_singer_room_enter(&$return, $singerid, $sid)
    {
        // nothing.
    }
    public function event_singer_room_leave(&$return, $singerid, $sid)
    {
        $error = array();
        $this->redis_clear_room_cache(&$error, $sid);
        if (0 != $error['code'])
        {
            $code = $error['code'];
            $desc = $error['desc'];
            LogApi::logProcess("event_singer_room_leave error($code):$desc");
        }
    }
    public function flag_update(&$return, $sid)
    {
        $error = array();
        do
        {
            if (0 >= $sid)
            {
                // 无效的房间号,什么也不做
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            LogApi::logProcess("event_room_heartbeat for faction flag sid:$sid.");
            $error['code'] = -1;
            $error['desc'] = '未知错误';
            //
            $timecode_now = time(null);
            ///////////////////////////////////////////////
            $faction_flag_action_info_key = flag_faction_model::redis_zset_faction_flag_action_info_key($sid);
            ///////////////////////////////////////////////
            // 本房间将超时的结算帮派拿出
            $faction_array_now = $redis->zRangeByScore($faction_flag_action_info_key, 0, $timecode_now);
            if (true == empty($faction_array_now))
            {
                // 没有房间可以进行夺旗结算
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            foreach ($faction_array_now as $unit_faction_id)
            {
                // 结算一次超时结算的帮派
                $this->handle_user_room_heartbeat_settlement(&$error, &$return, $sid, $unit_faction_id);
                // 移除超时的夺旗动作记录
                $redis->zRem($faction_flag_action_info_key, $unit_faction_id);
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
            /////////////////////////////////////////////////////////////////////////////////////
        }while(0);
        if (0 != $error['code'])
        {
            $code = $error['code'];
            $desc = $error['desc'];
            LogApi::logProcess("flag_update error($code):$desc");
        }
    }
    public function handle_user_room_heartbeat_settlement(&$error, &$return, $sid, $unit_faction_id)
    {
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $timecode_now = time();
            ///////////////////////////////////////////////
            $faction_name = '';
            $faction_icon = '';
            $faction_level = 0;
            $flag_icon = "uknown.png";
            ///////////////////////////////////////////////
            $current = array();
    
            $current['faction_id'] = -1;
            $current['faction_name'] = '';
            $current['faction_icon'] = '';
            $current['flag_number'] = 0;
            $current['faction_level'] = 0;
    
            $oneself = array();
    
            $oneself['faction_id'] = -1;
            $oneself['faction_name'] = '';
            $oneself['faction_icon'] = '';
            $oneself['flag_number'] = 0;
            $oneself['faction_level'] = 0;
    
            $thelast = array();
    
            $thelast['faction_id'] = -1;
            $thelast['faction_name'] = '';
            $thelast['faction_icon'] = '';
            $thelast['flag_number'] = 0;
            $thelast['faction_level'] = 0;
    
            $failure = array();
    
            $nt = array();
            $nt['cmd'] = 'flag_settlement_nt';
            $nt['sid'] = $sid;
            $nt['current'] = &$current;
            $nt['thelast'] = &$thelast;
            $nt['failure'] = &$failure;
            $nt['flag_icon'] = $flag_icon;
            $nt['opcode'] = flag_faction_model::$op_wins_success;
            $nt['timelength'] = 0;
            $nt['timecode'] = 0;            
            ///////////////////////////////////////////////
            $faction_current = array();
            // 本房间的插旗帮派
            $flg_info_faction_id = 0;
            // 本次时间点的分数最大帮派
            $max_info_faction_id = 0;
            // 本次时间点的超时结算帮派
            $now_info_faction_id = $unit_faction_id;
            //
            // 结算逻辑无论是插旗还是夺旗结算逻辑都为:对本房间的每次结算都依次进行一下逻辑
            // 1.对本次结算帮派做结算查看分数最大的帮派是否是自己,不是自己则夺旗失败,是自己则夺旗成功
            // 2.当前插旗的帮派做结算查看分数最大的帮派是否是自己,不是自己则守旗失败,是自己则守旗成功
            // 3.结算当前优胜帮派分数需要保证大于基准分数,否则将本房间置为空旗状态
            ///////////////////////////////////////////////
            $number_info_key = flag_faction_model::redis_zset_faction_flag_number_info_key($sid);
            $sys_parameters = new SysParametersModel();
            $base_flag_number = $sys_parameters->GetSysParameters(flag_faction_model::$CONST_PARM_ID_BASE_FLAG_NUMBER, 'parm1');
            $base_time_length = $sys_parameters->GetSysParameters(flag_faction_model::$CONST_PARM_ID_FLAG_TIME_LENGTH, 'parm1');
            ///////////////////////////////////////////////
            $nt['timelength'] = $base_time_length;
            $timecode_now = time(null);
            // 尝试拿出本房间占旗帮派的结算超时时间
            $trigger_faction_action_timeout = 0;
            $this->redis_room_action_info_near_timecode(&$error, &$trigger_faction_action_timeout, $sid);
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            // 将超时时间点计算出来
            if (true == empty($trigger_faction_action_timeout))
            {
                $trigger_faction_action_timeout = 0;
                $nt['timecode'] = 0;
            }
            else
            {
                if ($timecode_now >= $trigger_faction_action_timeout)
                {
                    $nt['timecode'] = 0;
                }
                else 
                {
                    $nt['timecode'] = $trigger_faction_action_timeout - $base_time_length;
                }                
            }
            // 获取本房间的插旗帮派
            $this->redis_room_get_current_info(&$error, &$faction_current, $sid);
            $flg_info_faction_id = $faction_current['faction_id'];
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            // 填充本房间的插旗帮派数据
            $this->redis_get_faction_flag_full_info(&$error, &$thelast, $flg_info_faction_id, $sid);
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            // 取分数最大的那个
            $max_info_faction_id = 0;
            $max_info_number     = 0;
            $faction_array_max = $redis->zRevRange($number_info_key, 0, 0, true);
            if (true == empty($faction_array_max))
            {
                // 结算出本次时间点的分数最大帮派
                $max_info_faction_id = 0;
                $max_info_number     = 0;
            }
            else 
            {
                // 取出唯一的元素
                $max_info_faction_id_unit = each($faction_array_max);
                // 结算出本次时间点的分数最大帮派
                $max_info_faction_id = $max_info_faction_id_unit['key'];
                $max_info_number     = $max_info_faction_id_unit['value'];
            }
            LogApi::logProcess("handle_user_room_heartbeat_settlement sid:".$sid." now_info_faction_id:".$now_info_faction_id." max_info_faction_id:".$max_info_faction_id." max_info_number:".$max_info_number);
            ///////////////////////////////////////////////
            // 填充本次时间点的超时结算帮派数据
            $this->redis_get_faction_flag_full_info(&$error, &$oneself, $now_info_faction_id, $sid);
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            ///////////////////////////////////////////////
            // 结算夺旗结果
            // 对于并列第一的情况,先结算的帮派算夺旗成功.
            $ff_d = $max_info_faction_id == $now_info_faction_id || $max_info_number == $oneself['flag_number'];
            $ff_s = $max_info_faction_id == $flg_info_faction_id || $max_info_number == $thelast['flag_number'];
            if ($ff_d && !$ff_s)
            {
                // 夺旗成功
                $current['faction_id'] = $oneself['faction_id'];
                $current['faction_name'] = $oneself['faction_name'];
                $current['faction_icon'] = $oneself['faction_icon'];
                $current['flag_number'] = $oneself['flag_number'];
                $current['faction_level'] = $oneself['faction_level'];
                $nt['opcode'] = flag_faction_model::$op_wins_success;
            }
            else
            {
                // 自己抢自己不算失败.
                if ($now_info_faction_id != $thelast['faction_id'])
                {
                    // 夺旗失败
                    if (0 < $oneself['faction_id'])
                    {
                        $failure[] = $oneself;
                    }
                    $nt['opcode'] = flag_faction_model::$op_wins_failure;
                }
                else
                {
                    $nt['opcode'] = flag_faction_model::$op_wins_success;
                }
            }
            // 结算守旗结果
            // 对于并列第一的情况,算守旗成功.
            if ($ff_s)
            {
                // 守旗成功
                $current['faction_id'] = $thelast['faction_id'];
                $current['faction_name'] = $thelast['faction_name'];
                $current['faction_icon'] = $thelast['faction_icon'];
                $current['flag_number'] = $thelast['flag_number'];
                $current['faction_level'] = $thelast['faction_level'];
            }
            else
            {
                // 自己抢自己不算失败.
                if ($now_info_faction_id != $thelast['faction_id'])
                {
                    // 守旗失败
                    if (0 < $thelast['faction_id'])
                    {
                        $failure[] = $thelast;
                    }
                }
            }
            // 仅剩下当前帮派,结算当前优胜帮派分数需要保证大于基准分数
            if ($base_flag_number > $current['flag_number'])
            {
                // 没有保证大于基准分数,将当前结算的优胜者放入失败列表
                if (0 < $current['faction_id'])
                {
                    $failure[] = $current;
                }
                // 将本房间的夺旗状态切换为空旗,但是已经开启了插旗状态
                $current['faction_id'] = 0;
                $current['faction_name'] = '';
                $current['faction_icon'] = '';
                $current['flag_number'] = 0;
                $current['faction_level'] = 0;
                $nt['opcode'] = flag_faction_model::$op_status_clean;
                // 注意这里不能将本房间清空,有可能有其他帮派在进行夺旗
            }
            // 结算夺旗结果
            if ($flg_info_faction_id != $current['faction_id'])
            {
                // 本房间插旗帮派发生了改变
                // 清除redis缓存
                $this->redis_del_faction_base_info(&$error, $current['faction_id']);
                // 重新获取最新的帮派基础数据
                $this->get_faction_base_info(&$error, $current['faction_id'], &$faction_name, &$faction_icon, &$faction_level);
                $current['faction_name'] = $faction_name;
                $current['faction_icon'] = $faction_icon;
                $current['faction_level'] = $faction_level;
                // 将本房间的结算帮会生效(-1 为空旗 0 为清除  非0 为替换)
    
                $faction_current = array();
                $faction_current['faction_id'] = $current['faction_id'];
                $faction_current['timecode'] = $timecode_now;
                $this->redis_room_set_current_info(&$error, $faction_current, $sid);
            }         
            
            // send to room.
            $nt['isRoom'] = true;
            // 将结算消息打包
            
            
            $return[] = array
            (
                'broadcast' => 2,// 直播间通知
                'data' => $nt,
            );
            LogApi::logProcess("on_get_flag_success_sunvalue_plus.handle_user_room_heartbeat_settlement sid:".$sid." nt:".json_encode($nt));
        }while(0);
        if (0 != $error['code'])
        {
            $code = $error['code'];
            $desc = $error['desc'];
            LogApi::logProcess("handle_user_room_heartbeat_settlement error($code):$desc");
        }
    }
    public function event_room_heartbeat(&$return, $sid)
    {
        // 任务,帮派夺旗触发器.
        $this->task_update(&$return, $sid);
        // 夺旗,事件触发器.
        $this->flag_update(&$return, $sid);
        LogApi::logProcess("event_room_heartbeat sid:".$sid." return:".json_encode($return));
    }
    
    public function on_get_flag_success_sunvalue_plus($sid,$uid,$faction_id)
    {
        $error = array();
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $flag_success_Plus=0;
        do
        {
           $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $faction_current = array();
            $flg_info_faction_id = 0;
            // 获取本房间的插旗帮派
            $this->redis_room_get_current_info(&$error, &$faction_current, $sid);
            $flg_info_faction_id = $faction_current['faction_id'];
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
           // LogApi::logProcess("flag_faction_model.on_get_flag_success_sunvalue_plus flg_info_faction_id:$flg_info_faction_id");
            if ($flg_info_faction_id!=$faction_id)
            {
                // 判断用户的帮会是否是夺旗帮会
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }  
            //LogApi::logProcess("flag_faction_model.on_get_flag_success_sunvalue_plus faction_id:$faction_id");
            $key_uid = flag_faction_model::redis_zset_faction_flag_member_info_key($sid,$faction_id);
            $faction_array_now = $redis->zScore($key_uid,$uid);
            if (true == empty($faction_array_now))
            {
                // 该帮派和房间参加人员没有该用户
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            
            else 
            {
                $sys_parameters = new SysParametersModel();                
                $flag_success_Plus_num = $sys_parameters->GetSysParameters(flag_faction_model::$CONST_PARM_ID_FLAG_SUCCESS_PLUS, 'parm1');
                $flag_success_Plus=$flag_success_Plus_num*0.01;
            }            
            $code = $error['code'];
            $desc = $error['desc'];
        }while(0);
        //LogApi::logProcess("flag_faction_model.on_get_flag_success_sunvalue_plus flag_success_Plus:$flag_success_Plus");
        if (0 != $error['code'])
        {
            $code = $error['code'];
            $desc = $error['desc'];
            LogApi::logProcess("flag_faction_model.on_get_flag_success_sunvalue_plus error($code):$desc");
        }
        return $flag_success_Plus;
    }
    
    
    
    
    
}
?>