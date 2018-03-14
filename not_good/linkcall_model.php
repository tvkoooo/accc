 <?php
 
 class linkcall_model extends ModelBase
{ 
    public function __construct ()
    {
        parent::__construct();
    }
    //Linkcall 常量：  
    public static $LINKCALL_STATE_OPEN       = 0;//连麦功能开启
    public static $LINKCALL_STATE_CLOSED     = 1;//连麦功能关闭
    
    public static $LINKCALL_APPLY_COUNT_MAX  = 3;//连麦最大申请次数
    public static $LINKCALL_LINK_COUNT_MAX   = 2;//连麦最大连接条数
    public static $LINKCALL_EXP_TIME         = 259200;//默认连麦redis无操作最大缓存时长（3天）
    public static $LINKCALL_EXP_60_STIME     = 60;//默认连麦申请在60s内，有3次申请判断为骚扰
    
    public static $LINKCALL_APPLY_DEFAULT    = 0;//用户连麦 default
    public static $LINKCALL_APPLY_APPLY      = 1;//用户连麦申请
    public static $LINKCALL_APPLY_DESAPPLY   = 2;//用户退出申请
    public static $LINKCALL_APPLY_OUT        = 3;//用户断开连麦    
    public static $LINKCALL_APPLY_YES        = 4;//主播同意申请
    public static $LINKCALL_APPLY_NO         = 5;//主播拒绝申请
    public static $LINKCALL_APPLY_DEL        = 6;//主播删除连麦

    
    public static $LINKCALL_APPLY_MAX_PLAYER = 10;//最大连麦申请个数    
    
    // redis 主播连麦功能运行状态缓存： 
    public static function linkcall_state_searc_center_hash_key()
    {
        return "linkcall:state:searc:center:hash";
    }
    // redis 连麦用户数据缓存:（不会实时同步，本回合有效，避免频繁查询mysql）
    public static function linkcall_user_data_json_hash_key($sid)
    {
        return "linkcall:user:data:json:hash:$sid";
    }  
    // redis 房间内用户连麦申请索引（记录连麦申请时间戳）
    public static function linkcall_user_data_apply_indexes_zset_key($sid)
    {
        return "linkcall:user:data:apply:indexes:zset:$sid";
    }
    // redis 房间内用户连麦申请当前列表（当前申请的人数）
    public static function linkcall_user_data_apply_set_key($sid)
    {
        return "linkcall:user:data:apply:set:$sid";
    }   
    
    // redis 房间内用户连麦60s重复申请判断
    public static function linkcall_user_data_apply_indexes_60s_zset_key($sid,$uid)
    {
        return "linkcall:user:data:apply:indexes:60s:zset:$sid:$uid";
    }  
    // redis 房间内用户连麦连通索引（记录连麦连通时间戳）
    public static function linkcall_user_data_link_indexes_zset_key($sid)
    {
        return "linkcall:user:data:link:indexes:zset:$sid";
    } 
    // redis 房间内用户连麦连接当前列表（当前连接的人数）
    public static function linkcall_user_data_link_set_key($sid)
    {
        return "linkcall:user:data:link:indexes:set:$sid";
    }
    // redis 房间内用户连麦申请状态索引（记录连麦申请状态）
    public static function linkcall_user_data_state_indexes_hash_key($sid)
    {
        return "linkcall:user:data:state:indexes:hash:$sid";
    } 

    //1.1    redis 写入     主播连麦功能运行状态缓存：
    public function set_singer_linkcall_state(&$error,$sid,$linkcall_state)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.set_singer_linkcall_state.redis: sid:$sid linkcall_state:$linkcall_state");
                break;
            }
            $key = linkcall_model::linkcall_state_searc_center_hash_key();
            $redis->hSet($key,$sid,$linkcall_state);
            
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    // 1.2    redis 读出     主播连麦功能运行状态缓存：
    public function get_singer_linkcall_state(&$error,$sid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $linkcall_state = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.get_singer_linkcall_state.redis: sid:$sid");
                break;
            }
            $key = linkcall_model::linkcall_state_searc_center_hash_key();
            $v=$redis->hGet($key,$sid);
            if(true == empty($v))
            {
                // 空值,$V 给个默认开启状态
                $v = linkcall_model::$LINKCALL_STATE_OPEN;
            }
            $linkcall_state=$v;
            $error['code'] = 0;
            $error['desc'] = '';            
        }while(0);
        return $linkcall_state;
    }
    
    //2.1  redis 写入     连麦用户数据缓存:（不会实时同步，本回合有效，避免频繁查询mysql）
    public function set_user_data_json(&$error,$sid,$user_id,&$data_cache)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.set_user_data_json.redis：sid:$sid user_id:$user_id");
                break;
            }
            $exp_time =linkcall_model::$LINKCALL_EXP_TIME;
            $key = linkcall_model::linkcall_user_data_json_hash_key($sid); 
            $redis->hSet($key,$user_id,json_encode($data_cache));
            $redis->expire($key,$exp_time);
            LogApi::logProcess("linkcall_model.set_user_data_json.hset：sid:$sid user_id:$user_id data_cache:".json_encode($data_cache));
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    } 
    
    //2.2    redis 读出     连麦用户数据缓存:（不会实时同步，本回合有效，避免频繁查询mysql）
    public function get_user_data_json(&$error,$sid,$user_id,&$data_cache)
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
                $error['desc'] = 'redis数据库断开连接';                
                LogApi::logProcess("linkcall_model.get_user_data_json.redis: sid:$sid user_id:$user_id");
                break;
            }
            $key = linkcall_model::linkcall_user_data_json_hash_key($sid);            
            $linkcall_user_data_json = $redis->hGet($key,$user_id);
            if(true == empty($linkcall_user_data_json))
            {
                // 403300012(012)读取数据为空
                $error['code'] = 403300012;
                $error['desc'] = '无用户缓存数据';
                LogApi::logProcess("linkcall_model.get_user_data_json.hget: sid:$sid user_id:$user_id");
                break;
            }
            //LogApi::logProcess("linkcall_model.get_user_data_json：sid:$sid user_id:$user_id linkcall_user_data_json:$linkcall_user_data_json");
            $v = json_decode($linkcall_user_data_json, true);
            if(true == empty($v))
            {
                // 403300013(013)解包失败
                $error['code'] = 403300013;
                $error['desc'] = 'json解包失败';
                LogApi::logProcess("linkcall_model.get_user_data_json.hget.json解包失败  user_id:$user_id");
                break;
            }
            $data_cache = $v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    } 
    
    //2.3    redis 删除     该主播连麦用户数据缓存
    public function del_user_data_json(&$error,$sid)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.set_user_data_json.redis：sid:$sid ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_json_hash_key($sid);
            $redis->del($key);
            LogApi::logProcess("linkcall_model.del_user_data_json.del：sid:$sid ");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //3.1   redis 写入     房间内用户连麦申请时间
    public function set_user_apply_time(&$error,$sid,$user_id,$time_apply)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
         if ($time_apply == 0)
            {
                $error['code'] = 200000001;
                $error['desc'] = 'redis记录申请时间为0';
                break;
            }
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.set_user_apply_time.redis: sid:$sid user_id:$user_id time_apply:$time_apply");
                break;
            }
            $exp_time =linkcall_model::$LINKCALL_EXP_TIME;
            $key = linkcall_model::linkcall_user_data_apply_indexes_zset_key($sid);
            $e=$redis->zAdd($key, $time_apply, $user_id);
            $redis->expire($key,$exp_time);
            if(0== $e)
            {
                //刷新申请时间
                LogApi::logProcess("inkcall_model.set_user_apply_time.zadd写入数据返回0: sid:$sid uid:$user_id time_apply:$time_apply");
                
            }
            LogApi::logProcess("linkcall_model.set_user_apply_time sid:$sid user_id:$user_id time_apply:$time_apply");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }  

    //3.2   redis 读出     房间内用户连麦申请时间    
    public function get_user_apply_time(&$error,$sid,$user_id)	
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $time_apply = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.get_user_apply_time.redis: sid:$sid user_id:$user_id ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_apply_indexes_zset_key($sid);    
            $v = $redis->zScore($key,$user_id);
            if(true == empty($v))
            {
                //如果取出无数据，给个default 值 0，代表列表无数据
                $v = 0;
            }
            $time_apply =$v;
            //LogApi::logProcess("linkcall_model.get_user_apply_time.zscore: sid:$sid user_id:$user_id time_apply:$time_apply");
            //
            $error['code'] = 0;
            $error['desc'] = '';            
        }while(0);
        return $time_apply;
    }
    
    //3.3   redis 删除     房间内用户连麦申请用户列表
    public function del_user_apply_time(&$error,$sid)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.del_user_apply_time.redis：sid:$sid ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_apply_indexes_zset_key($sid);
            $redis->del($key);
            LogApi::logProcess("linkcall_model.del_user_apply_time.del：sid:$sid ");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //3.4   redis 写入     房间内用户连麦申请用户
    public function set_user_apply(&$error,$sid,$user_id)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.set_user_apply.redis: sid:$sid user_id:$user_id ");
                break;
            }
            $exp_time =linkcall_model::$LINKCALL_EXP_TIME;
            $key = linkcall_model::linkcall_user_data_apply_set_key($sid);
            $e=$redis->sAdd($key, $user_id);
            $redis->expire($key,$exp_time);
            if(0== $e)
            {
                $error['code'] = 403300014;
                $error['desc'] = '数据写入异常';
                LogApi::logProcess("inkcall_model.set_user_apply.zadd写入数据返回0: sid:$sid uid:$user_id ");
                break;
            }
            //LogApi::logProcess("linkcall_model.set_user_apply sid:$sid user_id:$user_id ");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //3.5   redis 移除    房间内用户连麦申请用户
    public function rem_user_apply(&$error,$sid,$user_id)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.del_user_apply.redis: sid:$sid user_id:$user_id ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_apply_set_key($sid);
            $v = $redis->sRem($key,$user_id);
            if(true == empty($v))
            {
                $error['code'] = 403300015;
                $error['desc'] = '数据删除出现异常';
                LogApi::logProcess("inkcall_model.del_user_link_time.zRem删除数据返回0: sid:$sid uid:$user_id");
                break;
            }

            //LogApi::logProcess("linkcall_model.del_user_apply.empty: sid:$sid user_id:$user_id ");
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);

    }
    
    //3.6a   redis 读出     房间内用户连麦申请用户列表
    public function get_user_apply_index(&$error,$sid,&$apply_list)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.get_user_apply_index.redis: sid:$sid ");
                break;
            }
            $get_apply_list =array();
            $key = linkcall_model::linkcall_user_data_apply_set_key($sid);
            $get_apply_list = $redis->sDiff($key);
    
            //输出获取的列表
            foreach ($get_apply_list as $uid)
            {
                $apply_list[] = $uid;
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }

    //3.6b   redis 查询     房间内用户是否在申请列表
    public function find_user_apply_index(&$error,$sid,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $is_apply =false;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.find_user_apply_index.redis: sid:$sid ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_apply_set_key($sid);
            $f = $redis->sIsMember($key,$user_id);
            $is_apply = $f;

            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $is_apply;
    }
    
    //3.7   redis 读出     房间内用户连麦申请用户个数
    public function get_user_apply_index_count(&$error,$sid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $num_apply = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.get_user_apply_index_count.redis: sid:$sid ");
                break;
            }

            $key = linkcall_model::linkcall_user_data_apply_set_key($sid);
            $v = $redis->sCard($key);
            if(true == empty($v))
            {
                //如果取出无数据，给个default 值 0，代表列表无数据
                $v = 0;
            }
            $num_apply =$v;
            LogApi::logProcess("linkcall_model.get_user_apply_index_count.sCard: sid:$sid num_apply:$num_apply");
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $num_apply;
    }    

    //3.8   redis 删除     房间内用户连麦申请用户列表
    public function del_user_apply_index(&$error,$sid)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.del_user_apply_index.redis：sid:$sid ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_apply_set_key($sid);
            $redis->del($key);
            LogApi::logProcess("linkcall_model.del_user_apply_index.del：sid:$sid ");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }    

    
    //3.9   redis 写入     60s内的用户申请记录
    public function set_user_apply_time_index_60s(&$error,$sid,$user_id,$time_apply)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            if ($time_apply == 0)
            {
                $error['code'] = 200000001;
                $error['desc'] = 'redis记录申请时间为0';
                break;
            }
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.set_user_apply_time_index_60s.redis: sid:$sid user_id:$user_id time_apply:$time_apply");
                break;
            }
            $exp_time =linkcall_model::$LINKCALL_EXP_60_STIME;
            $key_linkcall_user_data_apply_indexes_60s= linkcall_model::linkcall_user_data_apply_indexes_60s_zset_key($sid,$user_id);
            $e=$redis->zAdd($key_linkcall_user_data_apply_indexes_60s, $time_apply, $time_apply);
            $redis->expire($key_linkcall_user_data_apply_indexes_60s,$exp_time);
            if(0== $e)
            {
                $error['code'] = 403300014;
                $error['desc'] = '数据写入出现异常';
                LogApi::logProcess("inkcall_model.set_user_apply_time_index_60s.zadd写入数据返回0: sid:$sid uid:$user_id time_apply:$time_apply");
                break;
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //3.10   redis 读出     60s内的用户申请记录次数    
    public function get_user_apply_time_60s_count(&$error,$sid,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $num_apply = 0;
        $time_now= time();
        $time_60s_ago =$time_now -60;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.get_user_apply_time_60s.redis: sid:$sid user_id:$user_id ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_apply_indexes_60s_zset_key($sid,$user_id);
            $v = $redis->zCount($key,$time_60s_ago,$time_now);
            if(true == empty($v))
            {
                //如果取出无数据，给个default 值 0，代表列表无数据
                $v = 0;
            }
            $num_apply =$v;
            LogApi::logProcess("linkcall_model.get_user_apply_time_60s.zCount: sid:$sid user_id:$user_id num_apply:$num_apply");
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $num_apply;
    }
 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    //4.1   redis 写入     房间内用户连麦连接时间
    public function set_user_link_time(&$error,$sid,$user_id,$time_link)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            if ($time_link == 0)
            {
                $error['code'] = 200000001;
                $error['desc'] = 'redis记录连接时间为0';
                break;
            }
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.set_user_link_time.redis: sid:$sid user_id:$user_id time_link:$time_link");
                break;
            }
            $exp_time =linkcall_model::$LINKCALL_EXP_TIME;
            $key = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);
            $e=$redis->zAdd($key, $time_link, $user_id);
            $redis->expire($key,$exp_time);
            if(0== $e)
            {
                //刷新连接时间
                LogApi::logProcess("inkcall_model.set_user_link_time.zadd写入数据返回0: sid:$sid uid:$user_id time_link:$time_link");
                
            }
            LogApi::logProcess("linkcall_model.set_user_link_time sid:$sid user_id:$user_id time_link:$time_link");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.2   redis 读出     房间内用户连麦连接时间
    public function get_user_link_time(&$error,$sid,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $time_link = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.get_user_link_time.redis: sid:$sid user_id:$user_id ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);
            $v = $redis->zScore($key,$user_id);
            if(true == empty($v))
            {
                //如果取出无数据，给个default 值 0，代表列表无数据
                $v = 0;
            }
            $time_link =$v;
            //LogApi::logProcess("linkcall_model.get_user_link_time.zscore: sid:$sid user_id:$user_id time_link:$time_link");
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $time_link;
    }
    
    //4.3   redis 删除     房间内用户连麦连接用户列表
    public function del_user_link_time(&$error,$sid)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.del_user_link_time.redis：sid:$sid ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);
            $redis->del($key);
            LogApi::logProcess("linkcall_model.del_user_link_time.del：sid:$sid ");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.4   redis 写入     房间内用户连麦申请用户
    public function set_user_link(&$error,$sid,$user_id)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.set_user_link.redis: sid:$sid user_id:$user_id ");
                break;
            }
            $exp_time =linkcall_model::$LINKCALL_EXP_TIME;
            $key = linkcall_model::linkcall_user_data_link_set_key($sid);
            $e=$redis->sAdd($key, $user_id);
            $redis->expire($key,$exp_time);
            if(0== $e)
            {
                $error['code'] = 403300014;
                $error['desc'] = '数据写入出现异常';
                LogApi::logProcess("inkcall_model.set_user_link.zadd写入数据返回0: sid:$sid uid:$user_id ");
                break;
            }
            LogApi::logProcess("linkcall_model.set_user_link sid:$sid user_id:$user_id ");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.5   redis 移除    房间内用户连麦申请用户
    public function rem_user_link(&$error,$sid,$user_id)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.rem_user_link.redis: sid:$sid user_id:$user_id ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_link_set_key($sid);
            $v = $redis->sRem($key,$user_id);
            if(true == empty($v))
            {
                $error['code'] = 403300015;
                $error['desc'] = '数据删除出现异常';
                LogApi::logProcess("inkcall_model.del_user_link_time.zRem删除数据返回0: sid:$sid uid:$user_id");
                break;
            }

            //LogApi::logProcess("linkcall_model.rem_user_link.sRem: sid:$sid user_id:$user_id");
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);

    }
    
    
    //4.6a   redis 读出     房间内用户连麦申请用户列表
    public function get_user_link_index(&$error,$sid,&$link_list)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.get_user_link_index.redis: sid:$sid ");
                break;
            }
            $get_link_list =array();
            $key = linkcall_model::linkcall_user_data_link_set_key($sid);
            $get_link_list = $redis->sDiff($key);
    
            //输出获取的列表
            foreach ($get_link_list as $uid)
            {
                $link_list[] = $uid;
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.6b   redis 查询     房间内用户是否在申请列表
    public function find_user_link_index(&$error,$sid,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $is_link =false;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.find_user_link_index.redis: sid:$sid ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_link_set_key($sid);
            $f = $redis->sIsMember($key,$user_id);
            $is_link = $f;
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $is_link;
    }
    
    //4.7   redis 读出     房间内用户连麦申请用户个数
    public function get_user_link_index_count(&$error,$sid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $num_link = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.get_user_link_index_count.redis: sid:$sid ");
                break;
            }
    
            $key = linkcall_model::linkcall_user_data_link_set_key($sid);
            $v = $redis->sCard($key);
            if(true == empty($v))
            {
                //如果取出无数据，给个default 值 0，代表列表无数据
                $v = 0;
            }
            $num_link =$v;
            LogApi::logProcess("linkcall_model.get_user_link_index_count.sCard: sid:$sid num_link:$num_link");
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $num_link;
    }
    
    //4.8   redis 删除     房间内用户连麦申请用户列表
    public function del_user_link_index(&$error,$sid)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.del_user_link_index.redis：sid:$sid ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_link_set_key($sid);
            $redis->del($key);
            LogApi::logProcess("linkcall_model.del_user_link_index.del：sid:$sid ");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
 
 
    //5.1  redis 写入     房间内用户连麦申请状态索引（记录连麦申请状态）
    public function set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.set_user_apply_state.redis: sid:$sid user_id:$user_id linkcall_apply:$linkcall_apply");
                break;
            }
            $exp_time =linkcall_model::$LINKCALL_EXP_TIME;
            $key_linkcall_user_data_state_indexes = linkcall_model::linkcall_user_data_state_indexes_hash_key($sid);
            $redis->hSet($key_linkcall_user_data_state_indexes,$user_id,$linkcall_apply);
            $redis->expire($key_linkcall_user_data_state_indexes,$exp_time);
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //5.2   redis 读出     房间内用户连麦申请状态索引（记录连麦申请状态）
    public function get_user_apply_state(&$error,$sid,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $linkcall_apply = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.set_user_apply_state.redis: sid:$sid");
                break;
            }
            $key_linkcall_user_data_state_indexes = linkcall_model::linkcall_user_data_state_indexes_hash_key($sid);
            $v=$redis->hGet($key_linkcall_user_data_state_indexes,$user_id);
            if(true == empty($v))
            {
                // 200000099(099)读取数据为空
                $v = 0;
            }
            $linkcall_apply = $v;
            $error['code'] = 0;
            $error['desc'] = '';

        }while(0);
        return $linkcall_apply;
    } 
    
    //5.3   redis 删除     房间内所有用户连麦申请状态索引
    public function del_user_apply_state(&$error,$sid)
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
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.del_user_apply_state.redis：sid:$sid ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_state_indexes_hash_key($sid);
            $redis->del($key);
            LogApi::logProcess("linkcall_model.del_user_apply_state.del：sid:$sid ");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //功能模块
    
    //6.1   用户发起申请连麦 
    public function user_apply_apply_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$data_cache,&$linkcall_apply,&$linkcall_state)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $op_code = 1;
        $time_link_num =0;
        $time_apply_num=0;
        do
        {
            $time_apply = time();
            // 0 记录用户60s三次申请时间连麦警告。
            $this->set_user_apply_time_index_60s(&$error, $sid, $user_id, $time_apply);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            // 1 查看60s内是否重复申请了3次。
            $num_apply = $this->get_user_apply_time_60s_count(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //if ($num_apply >= linkcall_model::$LINKCALL_APPLY_COUNT_MAX)
            //{
            //    // 403300017(017)用户60s内已经重复申请3次
            //    $error['code'] = 403300017;
            //    $error['desc'] = '用户60s内已经重复申请3次';
            //    break;
            //}
            LogApi::logProcess("user_apply_apply_linkcall num_apply:$num_apply  time_apply;$time_apply");
            // 2 查询该用户是否已经在申请索引列表
            $is_apply = $this->find_user_apply_index(&$error,$sid,$user_id);           
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            if (true == $is_apply)
            {
                // 403300016(016)用户已经存在申请列表
                $error['code'] = 403300016;
                $error['desc'] = '用户已经存在申请列表';
                break;
            }            

            // 2.1 查询当前的申请列表申请个数
            $time_apply_num = $this->get_user_apply_index_count(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }         
            LogApi::logProcess("user_apply_apply_linkcall num_apply:$num_apply  time_apply;$time_apply time_apply_num:$time_apply_num");
            if ($time_apply_num >= linkcall_model::$LINKCALL_APPLY_MAX_PLAYER)
            {
                // 403300022(022)当前申请人数超过最大值，请核对
                $error['code'] = 403300022;
                $error['desc'] = '当前申请人数超过最大值，请核对';
                break;
            }
            

            // 3 查询当前主播连麦连接总人数。        
            $time_link_num =$this->get_user_link_index_count(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }        
            if ($time_link_num >= linkcall_model::$LINKCALL_LINK_COUNT_MAX)
            {
                // 403300018(018)当前连麦人数超过最大值，请核对
                $error['code'] = 403300018;
                $error['desc'] = '当前连麦人数超过最大值，请核对';
                break;
            }
            
            
            // 4 记录用户
            {
                $time_apply_num =$time_apply_num +1;
                //记录用户连麦申请状态。
                $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //记录用户连麦申请时间。                
                //LogApi::logProcess("user_apply_apply_linkcall time_apply:$time_apply");
                $this->set_user_apply_time(&$error,$sid,$user_id,$time_apply);                
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                } 
                
                //记录用户进入申请列表。
                $this->set_user_apply(&$error,$sid,$user_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                
                //记录用户连麦数据缓存
                $this->set_user_data_json(&$error, $sid, $user_id, &$data_cache);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }        
            
            // 5 单播连麦申请给主播
            
            $this->linkcall_apply_singer_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }  
        }while(0); 
    }
    //6.2   用户取消申请连麦
    public function user_apply_desapply_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_apply,&$linkcall_state)
    { 
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $op_code =2;
        $time_apply = 0;
        do
        {
            // 1 查询该用户是否在申请列表
            $is_apply = $this->find_user_apply_index(&$error,$sid,$user_id);           
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            if (false == $is_apply)
            {
                // 403300019(019)用户不在申请列表，请核对
                $error['code'] = 403300019;
                $error['desc'] = '用户不在申请列表，请核对';
                break;
            }  

            // 2 登记用户取消申请状态        
            $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            // 3 发送用户取消连麦，单播给主播nt
            $err = $this->linkcall_apply_singer_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            // 4 从连麦申请列表移除用户
            $this->rem_user_apply(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            // 5 查询用户连麦时间
            $time_apply = $this->get_user_apply_time(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
        }while(0);
    }
    //6.3   用户退出连麦
    public function user_apply_out_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_apply,&$linkcall_state)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $op_code =3 ;
        do
        {
            // 1 查询该用户是否已经在连接列表
            $is_link = $this->find_user_link_index(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            if (false == $is_link)
            {
                // 403300020(020)该用户不在链接列表
                $error['code'] = 403300020;
                $error['desc'] = '用户不在连接列表，请核对';
                break;
            }      
            
            // 2 记录用户连麦申请状态。
            $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            // 3 发送用户退出连麦，单播给主播nt
            $err = $this->linkcall_apply_singer_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            // 4 删除用户的连麦链接表
            $this->rem_user_link(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            // 5 广播直播间，当前连麦连接状态   
            $this->linkcall_room_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            } 
            // 5 查询用户申请时间
            $time_apply = $this->get_user_apply_time(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            // 6 查询用户连麦时间
            $time_allow = $this->get_user_link_time(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
        }while(0);
    }
    
    //7.1  主播允许申请
    public function singer_apply_yes_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_state,&$linkcall_apply)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $op_code =1;
        $num_link = 0;
        $time_allow= 0;
        do
        {

            // 1 查询该用户是否在申请列表
            $is_apply = $this->find_user_apply_index(&$error,$sid,$user_id);           
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }

            if (false == $is_apply)
            {
                // 403300019(019)用户不在申请列表，请核对
                $error['code'] = 403300019;
                $error['desc'] = '用户不在申请列表，请核对';
                break;
            }

            // 2 查询当前连麦总数（当前有多少人连麦）
            $num_link =$this->get_user_link_index_count(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }

            if ($num_link >= linkcall_model::$LINKCALL_LINK_COUNT_MAX)
            {
                // 403300018(018)当前连麦人数超过最大值，请核对
                $error['code'] = 403300018;
                $error['desc'] = '当前连麦人数超过最大值，请核对';
                break;
            } 

            $num_link = $num_link + 1;
            $time_allow = time();

            // 3.1 把该用户存入连麦连接列表
            $this->set_user_link(&$error,$sid,$user_id);
            

            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            } 

            // 3.2 把该用户连麦时间存入连麦连接查询表
            $this->set_user_link_time(&$error,$sid,$user_id,$time_allow);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }         

            // 4 从连麦申请列表把该用户移除
            $this->rem_user_apply(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            // 5 记录用户连麦申请状态
            $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            // 6 主播单播连麦允许给用户        
            $this->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            // 7 广播直播间，当前连麦连接状态
            $this->linkcall_room_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            // 8 $link_num 是否是最大的连麦人数
            if ( $num_link == linkcall_model::$LINKCALL_LINK_COUNT_MAX)
            {            
                //查询当前连麦申请列表，取出连麦申请user_id
                $apply_list=array();
                $this->get_user_apply_index(&$error,$sid,&$apply_list);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //用查询到的user_id，去推送到相应的用户，主播拒绝申请
                $linkcall_apply_for = linkcall_model::$LINKCALL_APPLY_NO;
                foreach ($apply_list as $uid)
                {

                    //根据 $uid修改当前用户的申请状态为   主播拒绝
                    $this->set_user_apply_state(&$error,$sid,$uid,$linkcall_apply_for);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    //根据 $uid去推送给用户   主播拒绝     
                    $this->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$uid);                
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                }
                // 由于主播连麦最大值，其他申请被动拒绝申请，清空申请列表
                $this->del_user_apply_index(&$error,$sid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
        }while(0);
    }
    
    //7.2   主播拒绝申请
    public function singer_apply_no_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_state,&$linkcall_apply)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $op_code =2;
        do
        {
            // 1 查询该用户是否在申请列表
            $is_apply = $this->find_user_apply_index(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            if (false == $is_apply)
            {
                // 403300019(019)用户不在申请列表，请核对
                $error['code'] = 403300019;
                $error['desc'] = '用户不在申请列表，请核对';
                break;
            }
            
            // 2 记录用户连麦申请状态。
            $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            // 3 主播单播连麦申请用户
            $this->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            // 3 移除该用户的申请记录。
            $this->rem_user_apply(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
        }while(0);

    }
    
    //7.3   主播断开连麦
    public function singer_apply_del_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_state,&$linkcall_apply)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $op_code =3;
        do
        {
            // 1 查询该用户是否在连麦列表
            $is_link = $this->find_user_link_index(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            if (false == $is_link)
            {
                // 403300019(019)用户不在申请列表，请核对
                $error['code'] = 403300020;
                $error['desc'] = '连麦：用户不在连接列表';
                break;
            }
            
            // 2 记录用户连麦申请状态。
            $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            // 3 主播单播断开连麦消息给用户        
            $this->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            // 4 从连麦连接列表移除该用户。
            $this->rem_user_link(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            // 5 广播直播间，当前连麦连接状态      
            $this->linkcall_room_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }

        }while(0);
    }
    
    //8.1   根据用户user_id 拼装用户 data
    public function linkcall_userdata_by_uid(&$error,$sid,$user_id,&$data)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $data_cache = array ();
            //1 用$user_id 去获取用户申请连麦状态
            $linkcall_apply = $this->get_user_apply_state(&$error, $sid, $user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }  
            
            //2 用$user_id 去获取用户申请连麦的时间  （如果用户被主播断开或者自己退出连麦，连麦 申请$time_apply = 0）
            $time_apply = $this->get_user_apply_time(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //3 根据$linkcall_apply 确定 用户连麦时间戳（没有连麦，连麦时间戳time_allow = 0）
            $time_allow = $this->get_user_link_time(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            //4 用$user_id 去获取用户缓存信息
            $this->get_user_data_json(&$error,$sid,$user_id,&$data_cache);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //拼装用户 data
            $data= $data_cache;
            $data['linkcall_apply'] = $linkcall_apply;
            $data['time_apply'] = $time_apply;
            $data['time_allow'] = $time_allow;  
            
        }while(0);
    }    
    
    //8.2  拼装连接用户数据datas
    public function linkcall_link_all_user_datas(&$error,$sid,&$datas)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            // 1 查询当前连接连麦的用户id
            $link_list=array();
            $this->get_user_link_index(&$error,$sid,&$link_list);
            // 2.拼装data， 返回datas
            foreach ($link_list as $uid)
            {
                $data=array();
                $this->linkcall_userdata_by_uid(&$error,$sid,$uid,&$data);
                $datas[] = $data;
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            } 
        }while(0);
    }
    
    //8.3  拼装连麦申请用户数据datas
    public function linkcall_apply_all_user_datas(&$error,$sid,&$datas)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            // 1 查询当前连麦申请的用户id
            $apply_list=array();
            $this->get_user_apply_index(&$error,$sid,&$apply_list);
            // 2.拼装data， 返回datas
            foreach ($apply_list as $uid)
            {
                $data=array();
                $this->linkcall_userdata_by_uid(&$error,$sid,$uid,&$data);
                $data['time_allow'] = 0 ;
                $datas[] = $data;
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
        }while(0);
    }
    
    //9.1   推送房间通知
    public function linkcall_room_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            // 拼装nt房间回包
            $nt=array();
            $datas=array();
            $nt['cmd'] = 'linkcall_room_state_nt';
            $nt['sid'] = $sid;
            $nt['singer_id'] = $singer_id;
            $nt['singer_nick'] = $singer_nick;
            $nt['linkcall_state'] = $linkcall_state;
            $this->linkcall_link_all_user_datas(&$error,$sid,&$datas);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            $nt['datas'] = $datas;
            
            //nt包
            $return[] = array
            (
                'broadcast' => 2,// 发广播nt包
                'data' => $nt,
            );
            LogApi::logProcess("linkcall_room_state_nt sid:".$sid." nt:".json_encode($nt));
        }while(0);
    }
    
    //9.2   推送用户通知
    public function linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            // 拼装nt用户回包
            $nt=array();
            $data=array();
            $nt['cmd'] = 'linkcall_user_state_nt';
            $nt['sid'] = $sid;
            $nt['singer_id'] = $singer_id;
            $nt['singer_nick'] = $singer_nick;
            $nt['linkcall_state'] = $linkcall_state;
            $this->linkcall_userdata_by_uid(&$error,$sid,$user_id,&$data);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            $nt['data'] = $data;
            
            //nt包
            $return[] = array
            (
                'broadcast' => 6,// 发用户nt包
                'target_uid' => $user_id,
                'data' => $nt,
            );
            LogApi::logProcess("linkcall_user_state_nt sid:".$sid."user_id:".$user_id." nt:".json_encode($nt));
        }while(0);
    }
    
    //9.3   推送主播通知
    public function linkcall_apply_singer_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
           // 拼装nt主播回包
            $nt=array();
            $data=array();
            $nt['cmd'] = 'linkcall_apply_singer_nt';
            $nt['sid'] = $sid;
            $nt['singer_id'] = $singer_id;
            $nt['singer_nick'] = $singer_nick;
            $nt['linkcall_state'] = $linkcall_state;
            $this->linkcall_userdata_by_uid(&$error,$sid,$user_id,&$data);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            $nt['data'] = $data;
            
            //nt包
            $return[] = array
            (
                'broadcast' => 6,// 发主播nt包
                'target_uid' => $singer_id,
                'data' => $nt,
            );
            LogApi::logProcess("linkcall_apply_singer_nt sid:".$sid."user_id:".$user_id." nt:".json_encode($nt));       
        }while(0);
    }
    
    
    
}