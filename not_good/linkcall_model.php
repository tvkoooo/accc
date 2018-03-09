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
    // redis 房间内用户连麦连通索引（记录连麦连通时间戳）
    public static function linkcall_user_data_link_indexes_zset_key($sid)
    {
        return "linkcall:user:data:link:indexes:zset:$sid";
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
            $key_linkcall_state = linkcall_model::linkcall_state_searc_center_hash_key();
            $redis->hSet($key_linkcall_state,$sid,$linkcall_state);
            
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
            $key_linkcall_state = linkcall_model::linkcall_state_searc_center_hash_key();
            $v=$redis->hGet($key_linkcall_state,$sid);
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
    public function set_user_data_json(&$error,$sid,$user_id,&$linkcall_user_data)
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
            $key_linkcall_user_data_json = linkcall_model::linkcall_user_data_json_hash_key($sid); 
            $redis->hSet($key_linkcall_user_data_json,$user_id,json_encode($linkcall_user_data));
            LogApi::logProcess("linkcall_model.set_user_data_json.hset：sid:$sid user_id:$user_id linkcall_user_data_json:".json_encode($linkcall_user_data));
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    } 
    
    //2.2    redis 读出     连麦用户数据缓存:（不会实时同步，本回合有效，避免频繁查询mysql）
    public function get_user_data_json(&$error,$sid,$user_id,&$linkcall_user_data)
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
            $key_linkcall_user_data_json = linkcall_model::linkcall_user_data_json_hash_key($sid);            
            $linkcall_user_data_json = $redis->hGet($key_linkcall_user_data_json,$user_id);
            if(true == empty($linkcall_user_data_json))
            {
                // 200000099(099)读取数据为空
                $error['code'] = 200000099;
                $error['desc'] = '无用户缓存数据';
                LogApi::logProcess("linkcall_model.get_user_data_json.hget: sid:$sid user_id:$user_id");
                break;
            }
            LogApi::logProcess("linkcall_model.get_user_data_json：sid:$sid user_id:$user_id linkcall_user_data_json:$linkcall_user_data_json");
            $v = json_decode($linkcall_user_data_json, true);
            if(true == empty($v))
            {
                // 100000001(001)解包失败
                $error['code'] = 100000001;
                $error['desc'] = '解包失败';
                LogApi::logProcess("linkcall_model.get_user_data_json.hget.json解包失败  user_id:$user_id");
                break;
            }
            $linkcall_user_data = $v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    } 
    
    //3.1   redis 写入     房间内用户连麦申请索引（记录连麦申请时间戳）
    public function set_user_apply_time(&$error,$sid,$user_id,$time_apply)
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
                LogApi::logProcess("linkcall_model.set_user_apply_time.redis: sid:$sid user_id:$user_id time_apply:$time_apply");
                break;
            }
            $key_linkcall_user_data_apply_indexes = linkcall_model::linkcall_user_data_apply_indexes_zset_key($sid);
            $e=$redis->zAdd($key_linkcall_user_data_apply_indexes, $time_apply, $user_id);
            if(0== $e)
            {
                $error['code'] = 200000003;
                $error['desc'] = '数据写入出现异常';
                LogApi::logProcess("inkcall_model.set_user_apply_time.zadd写入数据返回0: sid:$sid uid:$user_id time_apply:$time_apply");
                break;
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }  

    //3.2   redis 读出     房间内指定用户连麦申请索引（取出查询用户的申请时间戳）
    
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
            $key_linkcall_user_data_apply_indexes = linkcall_model::linkcall_user_data_apply_indexes_zset_key($sid);    
            $v = $redis->zScore($key_linkcall_user_data_apply_indexes,$user_id);
            if(true == empty($v))
            {
                //如果取出无数据，给个default 值 0，代表列表无数据
                $v = 0;
            }
            $time_apply =$v;
            LogApi::logProcess("linkcall_model.get_user_apply_time.zscore: sid:$sid user_id:$user_id time_apply:$time_apply");
            //
            $error['code'] = 0;
            $error['desc'] = '';            
        }while(0);
        return $time_apply;
    }
    

    //3.3   redis 读出     房间内用户连麦申请索引（记录连麦申请时间戳）
    public function get_user_apply_time_index(&$error,$sid,&$apply_list)
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
                LogApi::logProcess("linkcall_model.get_user_apply_time_index.redis: sid:$sid ");
                break;
            }
            $key_linkcall_user_data_apply_indexes = linkcall_model::linkcall_user_data_apply_indexes_zset_key($sid);
            $set_number = linkcall_model::$LINKCALL_APPLY_MAX_PLAYER-1;
            $get_apply_list = $redis->zRange($key_linkcall_user_data_apply_indexes,0,$set_number,true);

            //输出获取的列表
            foreach ($get_apply_list as $uid => $score)
            {
                $data = array ();
                $data['time_apply'] = $score;
                $data['user_id'] = $uid;
                $apply_list[] = $data;
            }            
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.1   redis 写入     房间内用户连麦连通索引（记录连麦连通时间戳）
    public function set_user_link_time(&$error,$sid,$user_id,$time_allow)
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
                LogApi::logProcess("linkcall_model.set_user_link_time.redis: sid:$sid user_id:$user_id time_allow:$time_allow");
                break;
            }
            $key_linkcall_user_data_link_indexes = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);
            $e=$redis->zAdd($key_linkcall_user_data_link_indexes,$time_allow, $user_id);
            if(0== $e)
            {
                $error['code'] = 200000003;
                $error['desc'] = '数据写入出现异常';
                LogApi::logProcess("inkcall_model.set_user_link_time.zadd写入数据返回0: sid:$sid uid:$user_id time_allow:$time_allow");
                break;
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.2  redis 读出     房间内指定用户连麦连接索引（取出查询用户的连接时间戳）
    public function get_user_link_time(&$error,$sid,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $time_allow = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = 'redis数据库断开连接';
                LogApi::logProcess("linkcall_model.get_user_link_time.redis: sid:$sid user_id:$user_id");
                break;
            }
            $key_linkcall_user_data_link_indexes = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);    
            $v = $redis->zScore($key_linkcall_user_data_link_indexes,$user_id);
            if(true == empty($v))
            {
                $v = 0;
            }
            $time_allow = $v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $time_allow; 
    }
    
    //4.3   redis 读出     房间内用户连麦连通索引（记录连麦连通时间戳）
    public function get_user_link_time_index(&$error,$sid,&$link_list)
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
                LogApi::logProcess("linkcall_model.get_user_link_time_index.redis: sid:$sid");
                break;
            }
            $key_linkcall_user_data_link_indexes = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);    
            $get_link_list = $redis->zRange($key_linkcall_user_data_link_indexes,0,1,true);
            //输出获取的列表
            foreach ($get_link_list as $uid => $score)
            {
                $data = array ();
                $data['time_allow'] = $score;
                $data['user_id'] = $uid;
                $link_list[] = $data;
            }
            //
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
            $key_linkcall_user_data_state_indexes = linkcall_model::linkcall_user_data_state_indexes_hash_key($sid);
            $redis->hSet($key_linkcall_user_data_state_indexes,$user_id,$linkcall_apply);
    
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
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //功能模块
    
    //6.1   用户发起申请连麦 
    public function user_apply_apply_linkcall(&$error,$sid,$singer_id,$user_id,&$data_cache,&$rs)
    {
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_APPLY;
        // 1 查询该用户是否已经在申请列表
        $time_apply = $this->get_user_apply_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        if (0 != $time_apply)
        {
            // 403300013(013)用户已经存在申请列表
            $error['code'] = 403300013;
            $error['desc'] = '用户已经存在申请列表';
            break;
        }
        // 2 查看60s内是否重复申请了3次。
        $err = $this->get_user_apply_time_record(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        if (0 != $err)
        {
            // 403300014(014)用户60s内已经重复申请3次
            $error['code'] = 403300014;
            $error['desc'] = '用户60s内已经重复申请3次';
            break;
        }
        // 3 记录用户
        {
            $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            $time_apply = time();
            $this->set_user_apply_time(&$error, $sid, $user_id, $time_apply);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            $this->set_user_data_json(&$error, $sid, $user_id, &$data_cache);
            {
                //出现了一些逻辑错误
                break;
            }
        }        
        
        // 4 单播连麦申请给主播
        
        $err = $this->linkcall_apply_singer_nt(&$error,$sid,$user_id,$linkcall_apply,$time_apply,&$data_cache);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }        
    }
    //6.2   用户取消申请连麦
    public function user_apply_desapply_linkcall(&$error,$sid,$singer_id,$user_id,&$data_cache,&$rs)
    { 
        // 3 单播连麦申请给主播
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_DESAPPLY;
        
        // 1 查询该用户是否已经在申请列表
        $time_apply = $this->get_user_apply_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        // 2 取出该用户的缓存信息
        $this->get_user_data_json(&$error, $sid, $user_id, &$data_cache);
        {
            //出现了一些逻辑错误
            break;
        } 
        $err = $this->linkcall_apply_singer_nt(&$error,$sid,$user_id,$linkcall_apply,$time_apply,&$data_cache);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
    }
    //6.3   用户退出连麦
    public function user_apply_out_linkcall(&$error,$sid,$singer_id,$singer_nick,$user_id,&$data_cache,&$rs,&$linkcall_state)
    {
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_OUT;
        
        // 1 查询该用户是否已经在申请列表
        $time_apply = $this->get_user_apply_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        // 2 取出该用户的缓存信息
        $this->get_user_data_json(&$error, $sid, $user_id, &$data_cache);
        {
            //出现了一些逻辑错误
            break;
        }
        // 3 单播退出连麦给主播
        $err = $this->linkcall_apply_singer_nt(&$error,$sid,$user_id,$linkcall_apply,$time_apply,&$data_cache);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        // 4 广播直播间，当前连麦连接状态    
        
        $this->linkcall_room_state_nt(&$error,$sid,$singer_id,$singer_nick,$linkcall_state,&$data_cache);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }  
    }
    
    //7.1  主播允许申请
    public function singer_apply_yes_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list)
    {
                        
        // 1 查询该用户是否已经在申请列表
        $time_apply = $this->get_user_apply_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        if (0 == $time_apply)
        {
            // 403300014(014)用户已经不在申请列表，请核对
            $error['code'] = 403300014;
            $error['desc'] = '用户已经不在申请列表，请核对';
            break;
        }
        
        // 2 主播单播连麦允许给用户
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_YES;
        $err = $this->linkcall_user_state_nt(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        
        // 3 查询当前连麦信息（当前又多少人连麦）
        $this->get_user_link_time_index(&$error,$sid,&$link_list);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }

        $time_allow     = $user_data['time_allow'] = time();
        // 4 把该用户存入连接列表
        $this->set_user_link_time(&$error,$sid,$user_id,$time_allow);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        
        // 5 广播直播间，当前连麦连接状态
        $linkcall_state = linkcall_model::LINKCALL_STATE_OPEN;
        $this->linkcall_room_state_nt(&$error,$sid,$linkcall_state);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        // 6 查询当前连麦信息（当前又多少人连麦）,超过2人后，剔除所有当前连麦申请
        $link_num = count($link_list);
        if ( 2 == $link_num )
        {
            //查询当前连麦申请列表，取出连麦申请user_id
            $this->get_user_apply_time_index(&$error,$sid,&$apply_list);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //用查询到的user_id，去推送到相应的用户，主播拒绝申请
            $linkcall_apply = linkcall_model::LINKCALL_APPLY_NO;
            foreach ($apply_list as $uid => $score)
            {
                $data_get = array ();
                $data_get['time_apply'] = $score;
                $data_get['user_id'] = $uid ;
                //根据 $uid去推送给用户   主播拒绝
                $this->linkcall_user_state_nt(&$error,$sid,$uid,$linkcall_apply);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
        }
    }
    
    //7.2   主播拒绝申请
    public function singer_apply_no_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list)
    {
        // 1 主播单播连麦申请用户
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_NO;
        $this->linkcall_user_state_nt(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            $error['code'] = 403300014;
            $error['desc'] = '用户已经不在申请列表，请核对';
            //出现了一些逻辑错误
            break;
        }
    }
    
    //7.3   主播断开连麦
    public function singer_apply_del_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list)
    {
        // 1 主播单播退出连麦给用户
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_DEL;
        $this->linkcall_user_state_nt(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        // 2 广播直播间，当前连麦连接状态       
        $linkcall_state = linkcall_model::LINKCALL_APPLY_DEL;
        $this->linkcall_room_state_nt(&$error,$sid,$linkcall_state);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        } 
    }
    
    //8.1   根据用户user_id 拼装用户 data
    public function linkcall_user_link_list_to_data(&$error,$sid,$user_id,&$data)
    {
        $data_cache = array ();
        //1 用$user_id 去获取用户申请连麦状态
        $linkcall_apply = $this->get_user_apply_state(&$error, $sid, $user_id);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }  
        //$linkcall_apply 应该只有2种情况，申请或者，连麦，其他情况退出
        if (!($linkcall_apply == linkcall_model::LINKCALL_APPLY_APPLY || $linkcall_apply == linkcall_model::LINKCALL_APPLY_APPLY)) 
        {
            // 403300015(015)用户已经不在申请列表，请核对
            $error['code'] = 403300015;
            $error['desc'] = '连麦：用户登记连麦申请状态异常';
            //出现了一些逻辑错误
            break;
        }
        //2 用$user_id 去获取用户申请连麦的时间
        $time_apply = $this->get_user_apply_time(&$error, $sid, $user_id);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        //3 根据$linkcall_apply 确定 用户连麦时间戳（如果该用户只有申请，没有连麦，连麦时间戳time_allow = 0）
        if ( $linkcall_apply == linkcall_model::LINKCALL_APPLY_APPLY )
        {
            $time_allow =0 ;
        }
        else
            if ($linkcall_apply == linkcall_model::LINKCALL_APPLY_YES) 
            {
                //2 用$user_id 去获取用户允许连麦的时间戳
                $time_allow = $this->get_user_link_time(&$error, $sid, $user_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
        
        //4 用$user_id 去获取用户缓存信息
        $this->get_user_data_json(&$error,$sid,$user_id,&$data_cache);
        if (0 != $error['code'])
        {
            //出现了一些逻辑错误
            break;
        }
        //拼装用户 data
        $data[] = $data_cache;
        $data['linkcall_apply'] = $linkcall_apply;
        $data['time_apply'] = $time_apply;
        $data['time_allow'] = $time_allow;                
    }    
    
    
    
    
    
    
    
    
    
}