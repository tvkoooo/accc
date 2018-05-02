 <?php
 
 //连麦PK功能
 class linkcall_pk_model extends ModelBase
{ 
    public function __construct ()
    {
        parent::__construct();
    }
    //linkcall_pk_model 常量：     
    public static $LINKCALL_PK_SET_CONTROL                   = 1;//连麦PK功能控制阀门
 
    public static $LINKCALL_PK_EXP_TIME                      = 259200;//默认连麦redis无操作最大缓存时长（3天）
    public static $LINKCALL_PK_EXP_60s_TIME                  = 60;    //默认连麦  小键    生命期  60s

    public static $LINKCALL_PK_SINGER_OFFLINE                =0;    //主播下线
    public static $LINKCALL_PK_SINGER_APPLY                  =1;    //申请连麦pk
    public static $LINKCALL_PK_SINGER_PKING                  =2;    //主播正在pk
    public static $LINKCALL_PK_SINGER_GAMING                 =3;    //主播正在游戏
    public static $LINKCALL_PK_SINGER_SAWING                 =4;    //主播正在电锯
    public static $LINKCALL_PK_SINGER_POPUP                  =5;    //主播收到一个连线弹窗，未处理
    public static $LINKCALL_PK_SINGER_NO                     =6;    //拒绝连线
    public static $LINKCALL_PK_SINGER_YES                    =7;    //同意连线
    public static $LINKCALL_PK_SINGER_START                  =8;    //开始pk  
    public static $LINKCALL_PK_SINGER_COUNT                  =9;    //结算pk（这个是时间到用尽结算，暂未退出pk）
    public static $LINKCALL_PK_SINGER_ADDTIME                =10;   //延长pk
    public static $LINKCALL_PK_SINGER_OVER                   =11;   //结束pk（这个有可能是提前结算，并退出pk）
    
    public static $LINKCALL_PK_SINGER_STATE_APPLY            =1;    //主播申请列表状态：申请
    public static $LINKCALL_PK_SINGER_STATE_APPLY            =1;    //主播申请列表状态：申请
    public static $LINKCALL_PK_SINGER_STATE_APPLY            =1;    //主播申请列表状态：申请
    public static $LINKCALL_PK_SINGER_STATE_APPLY            =1;    //主播申请列表状态：申请  
    
    public static $LINKCALL_PK_PAGE_NUMBER                   = 10;//显示主播列表分页记录条数
    public static $LINKCALL_PK_GIFT_PAGE_NUMBER              = 10;//显示送礼排行榜分页记录调试
    public static $LINKCALL_PK_GIFT_FIRST5LIST               = 5; //显示送礼排行榜最前的5个送礼列表

    //linkcall_pk_model 配置文件：  select id,parm1,parm2,parm3 from card.parameters_info where id =292 ||id =293 ||id =297;
    public static $LINKCALL_PK_SINGER_START   =  292;   //申请PK主播的最低星级
    public static $LINKCALL_PK_LINK_PKTIME    =  293;   //连麦正常PK总时间
    public static $LINKCALL_PK_LINK_ADDTIME   =  297;   //连麦PK 延长时间
    public static $LINKCALL_PK_LINK_POPUPTIME =  297;   //连麦PK 弹窗时间
    
    // 0 redis 连麦PK 的mysql 配置信息缓存：
    public static function linkcallpk_mysql_config_info_hash_key()
    {
        return "linkcallpk:mysql:config:info:hash";
    }
    
    // 1 redis 主播基础数据缓存： 
    public static function linkcallpk_singer_info_hash_key()
    {
        return "linkcallpk:singer:info:hash";
    }
    
    // 2 redis 送礼用户基础数据缓存：
    public static function linkcallpk_user_info_hash_key()
    {
        return "linkcallpk:user:info:hash";
    }
    // 3 redis 服务器在线连麦可PK主播列表缓存:
    public static function linkcallpk_singer_onlinelist_zset_key()
    {
        return "linkcallpk:singer:onlinelist:zset";
    }  

    // 4.1 redis 主播客场申请PK列表（singer_id）缓存:
    public static function linkcallpk_singer_guestlist_zset_key($singer_id)
    {
        return "linkcallpk:singer:guestlist:zset:$singer_id";
    }
    
    // 4.2 redis 主播主场连线PK列表（singer_id）缓存:
    public static function linkcallpk_singer_hostlist_zset_key($singer_id)
    {
        return "linkcallpk:singer:hostlist:zset:$singer_id";
    }   
    
    // 5 redis 连麦PK号创建
    public static function linkcallpk_pkid_create_string_key()
    {
        return "linkcallpk:pkid:create:string";
    }  
    // 6 redis 连麦PK信息缓存
    public static function linkcallpk_PK_info_hash_key($pkid)
    {
        return "linkcallpk:PK:info:hash:$pkid";
    } 
    // 7 redis 连麦PK期间的送礼用户列表
    public static function linkcallpk_gift_list_zset_key($singer_id)
    {
        return "linkcallpk:gift:list:zset:$singer_id";
    }
    // 8 redis 连麦PK期间主播对应PK号 缓存
    public static function linkcallpk_singer_pkid_zset_key()
    {
        return "linkcallpk:singer:pkid:zset";
    }
    
     // 9 redis 服务器所有正在连麦PK的主播id 及礼物金额
    public static function linkcallpk_PKing_singer_gift_zset_key()
    {
        return "linkcallpk:PKing:singer:gift:zset";
    }  

    // 10 redis 服务器记录主播连麦PK 连麦请求状态缓存
    public static function linkcallpk_singer_state_hash_key($singer_id)
    {
        return "linkcallpk:singer:state:hash:$singer_id";
    }    

    //0.1    redis 写入     mysql配置数据缓存：
    public function redis_set_mysql_info(&$error,$id1,$id2,$id3,$value1,$value2,$value3)
    {
    
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_mysql_info. error:".json_encode($error));
                break;
            }        
            
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_60s_TIME;
            $key = linkcall_pk_model::linkcallpk_mysql_config_info_hash_key();
            $redis->hSet($key,$id1,$value1);
            $redis->hSet($key,$id2,$value2);
            $redis->hSet($key,$id3,$value3);
            
            $redis->expire($key,$exp_time);
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    
    }
    
    // 1.2    redis 读出     主播基础数据缓存：
    public function redis_get_mysql_info(&$error,$id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $value = 0;    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_mysql_info. id:$id error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_mysql_config_info_hash_key();
            $get_value = $redis->hGet($key,$id);
            if(true == empty($get_value))
            {
                // 403400012(012)读取数据为空,需要重新写入数据
                //取mysql值
                $sys_parameters = new SysParametersModel();
                $id1    = linkcall_pk_model::$LINKCALL_PK_SINGER_START;
                $id2    = linkcall_pk_model::$LINKCALL_PK_LINK_PKTIME;
                $id3    = linkcall_pk_model::$LINKCALL_PK_LINK_ADDTIME;
                $value1 = $sys_parameters->GetSysParameters($id1, 'parm1');
                $value2 = $sys_parameters->GetSysParameters($id2, 'parm1');
                $value3 = $sys_parameters->GetSysParameters($id3, 'parm1');
                //重新写入redis
                $this->redis_set_mysql_info(&$error,$id1,$id2,$id3,$value1,$value2,$value3);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //给出返回值
                if ($id == $id1 ) {
                    $value = $value1;
                }elseif ($id == $id2){
                    $value = $value2;
                }elseif ($id == $id1){
                    $value = $value3;
                }else{
                    // 403400016(016)mysql配置参数读取出错
                    $error['code'] = 403400016;
                    $error['desc'] = 'mysql配置参数读取出错';
                    break;
                }
            }
            else 
            {
                $value = $get_value;
            }            
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $value;
    }
    
    //1.1    redis 写入     主播基础数据缓存：
    public function redis_set_singer_info(&$error,$singer_id,&$singer_cache)
    {

        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {            
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_singer_info.singer_id:$singer_id error:".json_encode($error));
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_singer_info_hash_key(); 
            $redis->hSet($key,$singer_id,json_encode($singer_cache));
            $redis->expire($key,$exp_time); 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);

    }
    
    // 1.2    redis 读出     主播基础数据缓存：
    public function redis_get_singer_info(&$error,$singer_id,&$singer_cache)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';               
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_info.singer_id:$singer_id error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_info_hash_key();           
            $singer_data_json = $redis->hGet($key,$user_id);
            if(true == empty($singer_data_json))
            {
                // 403400012(012)读取数据为空
                $error['code'] = 403400012;
                $error['desc'] = '读取数据为空';
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_info.singer_id:$singer_id error:".json_encode($error));
                break;
            }            
            $v = json_decode($singer_data_json, true);
            if(true == empty($v))
            {
                // 403400013(013)解包失败
                $error['code'] = 403400013;
                $error['desc'] = '解包失败';
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_info.singer_id:$singer_id error:".json_encode($error));
                break;
            }
            $singer_cache = $v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
     //2.1    redis 写入     送礼用户基础数据缓存：
    public function redis_set_user_info(&$error,$user_id,&$user_cache)
    {

        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {            
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_user_info.user_id:$user_id error:".json_encode($error));
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_user_info_hash_key(); 
            $redis->hSet($key,$user_id,json_encode($user_cache));
            $redis->expire($key,$exp_time); 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);

    }
    
    // 2.2    redis 读出     送礼用户基础数据缓存：
    public function redis_get_user_info(&$error,$user_id,&$user_cache)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';               
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_info.user_id:$user_id error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_user_info_hash_key();           
            $user_data_json = $redis->hGet($key,$user_id);
            if(true == empty($user_data_json))
            {
                // 403400012(012)读取数据为空
                $error['code'] = 403400012;
                $error['desc'] = '读取数据为空';
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_info.user_id:$user_id error:".json_encode($error));
                break;
            }            
            $v = json_decode($user_data_json, true);
            if(true == empty($v))
            {
                // 403400013(013)解包失败
                $error['code'] = 403400013;
                $error['desc'] = '解包失败';
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_info.user_id:$user_id error:".json_encode($error));
                break;
            }
            $user_cache = $v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    

    //3.1   redis 写入     服务器在线连麦可PK主播列表
    public function redis_set_online_singer_list(&$error,$singer_id,$time_apply)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {

            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_online_singer_list.singer_id:$singer_id time_apply:$time_apply error:".json_encode($error));
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_singer_onlinelist_zset_key();
            $e=$redis->zAdd($key, $time_apply, $singer_id);
            $redis->expire($key,$exp_time);
            //备注：如果该主播已经在列表，则刷新申请时间，用于其他主播在更前面位置看到记录
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }  

    //3.2   redis 读出     服务器在线连麦可PK主播列表（分页）    
    public function redis_get_online_singer_list(&$error,$page_num,$selfsinger,&$singer_list)	
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $start_number = $page_num * (linkcall_pk_model::LINKCALL_PK_PAGE_NUMBER - 1);
        $stop_number = $page_num * (linkcall_pk_model::LINKCALL_PK_PAGE_NUMBER) - 1;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_online_singer_list.page_num:$page_num error:".json_encode($error));
                break;
            }
            $get_list =array();
            $key = linkcall_pk_model::linkcallpk_singer_onlinelist_zset_key();
            //1 先找出这个范围内是否有自己,如果有自己，还需要多取出一个数据
            $get_rank = $redis->zRank($key,$selfsinger);
            if ($get_rank > $start_number && $get_rank < $stop_number)
            {
                $stop_number = $stop_number + 1;
            }
            //2 取出所需范围的数据
            $get_list = $redis->zRange($key,$start_number,$stop_number);
            if (true == empty($get_list))
            {
                //如果取出数据为空，$user_gift_list返回是空值
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            //输出获取的列表
            foreach ($get_list as $singer_id)
            {
                $singer_list[] = $singer_id;
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //3.3   redis 移除     服务器在线连麦可PK主播列表中一个
    public function redis_rem_online_singer_list(&$error,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_rem_online_singer_list.singer_id:$singer_id error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_onlinelist_zset_key();
            $v = $redis->zRem($key,$singer_id);
            if(true == empty($v))
            {
                //如果数据不存在，忽略错误
                LogApi::logProcess("linkcall_pk_model.redis_rem_online_singer_list.zRem 删除数据返回0: singer_id:$singer_id");
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    
    }
 
    //4.1   redis 写入     主播客场申请列表（$guest_id）
    public function redis_set_singer_guest_apply_list(&$error,$selfsinger,$objsinger,$time_apply)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_singer_guest_apply_list.selfsinger:$selfsinger objsinger:$objsinger time_apply:$time_apply error:".json_encode($error));
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_singer_guestlist_zset_key($selfsinger);
            $e=$redis->zAdd($key, $time_apply, $objsinger);
            $redis->expire($key,$exp_time);
            //备注：如果该主播已经在列表，则刷新申请时间，用于其他主播在更前面位置看到记录
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.2   redis 读出     主播客场连麦PK申请列表分页（$guest_id）
    public function redis_get_singer_guest_apply_list(&$error,$selfsinger,$page_num,&$objsinger_list)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $start_number = $page_num * (linkcall_pk_model::LINKCALL_PK_PAGE_NUMBER - 1);
        $stop_number = $page_num * (linkcall_pk_model::LINKCALL_PK_PAGE_NUMBER) - 1;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_guest_apply_list. selfsinger:$selfsinger page_num:$page_num error:".json_encode($error));
                break;
            }
            $get_list =array();
            $key = linkcall_pk_model::linkcallpk_singer_guestlist_zset_key($selfsinger);
            $get_list = $redis->zRange($key,$start_number,$stop_number); 
            if (true == empty($get_list))
            {
                //如果取出数据为空，$user_gift_list返回是空值
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            //输出获取的列表            
            foreach ($get_list as $singer_id)
            {
                $objsinger_list[] = $singer_id;
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.3   redis 读出     主播客场申请列表（$guest_id） 被操作主播的申请时间
    public function redis_get_singer_guest_apply_time(&$error,$selfsinger,$objsinger)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $time_apply = 0;

        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_guest_apply_time. selfsinger:$selfsinger objsinger:$objsinger error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_guestlist_zset_key($selfsinger);
            $get_v = $redis->zScore($key,$objsinger);
            if (true == empty($get_v))
            {
                //如果取出数据为空，$get_v 给个空值0
                $get_v = 0;
            }
            $time_apply = $get_v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $time_apply;
    }
    
    //4.4   redis 移除     主播客场连麦PK申请列表（$guest_id）列表中一个
    public function redis_rem_singer_guest_apply_list(&$error,$selfsinger,$objsinger)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_rem_singer_guest_apply_list. selfsinger:$selfsinger objsinger:$objsinger error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_guestlist_zset_key($selfsinger);
            $redis->zRem($key,$objsinger);
            //如果数据不存在，忽略错误
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    
    }
    
    //4.5   redis 写入     主播主场连线列表（$guest_id）
    public function redis_set_singer_host_link_list(&$error,$selfsinger,$objsinger,$time_apply)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_singer_host_link_list.selfsinger:$selfsinger objsinger:$objsinger time_apply:$time_apply error:".json_encode($error));
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_singer_hostlist_zset_key($selfsinger);
            $e=$redis->zAdd($key, $time_apply, $objsinger);
            $redis->expire($key,$exp_time);
            //备注：如果该主播已经在列表，则刷新申请时间，用于其他主播在更前面位置看到记录
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.6   redis 读出     主播主场连线列表分页（$guest_id）
     public function redis_get_singer_host_link_list(&$error,$selfsinger,$page_num,&$objsinger_list)	
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $start_number = $page_num * (linkcall_pk_model::LINKCALL_PK_PAGE_NUMBER - 1);
        $stop_number = $page_num * (linkcall_pk_model::LINKCALL_PK_PAGE_NUMBER) - 1;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_host_link_list. selfsinger:$selfsinger page_num:$page_num error:".json_encode($error));
                break;
            }
            $get_list =array();
            $key = linkcall_pk_model::linkcallpk_singer_hostlist_zset_key($selfsinger);
            $get_list = $redis->zRange($key,$start_number,$stop_number);
            if (true == empty($get_list))
            {
                //如果取出数据为空，$user_gift_list返回是空值
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }    
            //输出获取的列表
            foreach ($get_list as $singer_id)
            {
                $objsinger_list[] = $singer_id;
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.7   redis 读出     主播主场连线列表分页（$guest_id）  被操作主播的申请时间
    public function redis_get_singer_host_link_time(&$error,$selfsinger,$objsinger)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $time_link = 0;
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_host_link_time. selfsinger:$selfsinger objsinger:$objsinger error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_hostlist_zset_key($selfsinger);
            $get_v = $redis->zScore($key,$objsinger);
            if (true == empty($get_v))
            {
                //如果取出数据为空，$get_v 给个空值0
                $get_v = 0;
            }
            $time_link = $get_v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $time_link;
    }
    
    //4.8   redis 移除     主播主场连线列表（$guest_id）列表中一个
    public function redis_rem_singer_host_link_list(&$error,$selfsinger,$objsinger)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_rem_singer_guestlist. selfsinger:$selfsinger objsinger:$objsinger error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_hostlist_zset_key($selfsinger);
            $redis->zRem($key,$objsinger);
            //如果数据不存在，忽略错误
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    
    }
    
    //5   redis 创建     连麦PK pkid号
    public function redis_create_pkid(&$error)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $v = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_create_pkid. error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_pkid_create_string_key();
            $redis->incr($key);
            $v = $redis->get($key);            
            if(true == empty($v))
            {
                // 403400014(014)创建pkid失败
                $error['code'] = 403400014;
                $error['desc'] = '创建pkid失败';
                LogApi::logProcess("linkcall_pk_model.redis_create_pkid.get 返回0: ");
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $v;
    }
    
    //6.1   redis 写入     连麦PK信息缓存
    public function redis_set_PK_info(&$error,$pkid,$starttime,$pkalltime,$host_id,$guest_id,$host_gift,$guest_gift)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_PK_info.pkid:$pkid host_id:$host_id guest_id:$guest_id starttime:$starttime error:".json_encode($error));
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_PK_info_hash_key($pkid);
            $redis->hSet($key, "starttime", $starttime);
            $redis->hSet($key, "pkalltime", $pkalltime);
            $redis->hSet($key, "host_id", $host_id);
            $redis->hSet($key, "guest_id", $guest_id);
            $redis->hSet($key, "host_gift", $host_gift);
            $redis->hSet($key, "guest_gift", $guest_gift);            
            
            $redis->expire($key,$exp_time);

            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //6.2   redis 读出     连麦PK信息缓存
    public function redis_get_PK_info(&$error,$pkid,&$starttime,&$pkalltime,&$host_id,&$guest_id,&$host_gift,&$guest_gift)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $get_starttime = 0;
        $get_pkalltime = 0;
        $get_host_id = 0;
        $get_guest_id = 0;
        $get_host_gift = 0;
        $get_guest_gift = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_PK_info.pkid:$pkid error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_PK_info_hash_key($pkid);
            //取出$starttime
            $get_starttime = $redis->hGet($key,"starttime");    
            if(true == empty($get_starttime))
            {
                // 403400012(012)读取数据为空
                $error['code'] = 403400012;
                $error['desc'] = '读取数据为空';
                LogApi::logProcess("linkcall_pk_model.redis_get_PK_info.pkid:$pkid starttime error:".json_encode($error));
                break;
            }
            $starttime = $get_starttime;
            //取出$pkalltime
            $get_pkalltime = $redis->hGet($key,"pkalltime");
            if(true == empty($get_pkalltime))
            {
                // 403400012(012)读取数据为空
                $error['code'] = 403400012;
                $error['desc'] = '读取数据为空';
                LogApi::logProcess("linkcall_pk_model.redis_get_PK_info.pkid:$pkid pkalltime error:".json_encode($error));
                break;
            }
            $pkalltime = $get_pkalltime;
            //取出$host_id
            $get_host_id = $redis->hGet($key,"host_id");
            if(true == empty($get_host_id))
            {
                // 403400012(012)读取数据为空
                $error['code'] = 403400012;
                $error['desc'] = '读取数据为空';
                LogApi::logProcess("linkcall_pk_model.redis_get_PK_info.pkid:$pkid host_id error:".json_encode($error));
                break;
            }
            $host_id = $get_host_id;   
            //取出$guest_id
            $get_guest_id = $redis->hGet($key,"guest_id");
            if(true == empty($get_guest_id))
            {
                // 403400012(012)读取数据为空
                $error['code'] = 403400012;
                $error['desc'] = '读取数据为空';
                LogApi::logProcess("linkcall_pk_model.redis_get_PK_info.pkid:$pkid guest_id error:".json_encode($error));
                break;
            }
            $guest_id = $get_guest_id;
            //取出$host_gift 主场礼物总数
            $get_host_gift = $redis->hGet($key,"host_gift");
            if(true == empty($get_host_gift))
            {
                // 403400012(012)读取数据为空，给个0值，忽略错误
                $get_host_gift = 0;
            }
            $host_gift = $get_host_gift;
            //取出$guest_gift 主场礼物总数
            $get_guest_gift = $redis->hGet($key,"host_gift");
            if(true == empty($get_guest_gift))
            {
                // 403400012(012)读取数据为空，给个0值，忽略错误
                $get_guest_gift = 0;
            }
            $guest_gift = $get_guest_gift;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //6.3   redis 结算     连麦PK 主客场金额
    public function redis_settlement_PK_info(&$error,$pkid,$host_gift,$guest_gift)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_PK_info.pkid:$pkid host_gift:$host_gift guest_gift:$guest_gift error:".json_encode($error));
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_PK_info_hash_key($pkid);
            $redis->hIncrBy($key, "host_gift", $host_gift);
            $redis->hIncrBy($key, "guest_gift", $guest_gift);            
            
            $redis->expire($key,$exp_time);

            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }  
    
    //7.1   redis 写入     连麦PK期间的送礼用户和金额（$singer_id）
    public function redis_set_user_gift(&$error,$singer_id,$user_id,$gift)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_user_gift.singer_id:$singer_id user_id:$user_id gift:$gift error:".json_encode($error));
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_gift_list_zset_key($singer_id);
            $e=$redis->zIncrBy($key, $gift, $user_id);
            if(true == empty($e) || $e < 0 || $gift < 0)
            {
                // 403400015(015)礼物金额登记失败
                $error['code'] = 403400015;
                $error['desc'] = '礼物金额登记失败';
                LogApi::logProcess("linkcall_pk_model.redis_get_PK_info.singer_id:$singer_id user_id:$user_id gift:$gift error:".json_encode($error));
                break;
            }
            $redis->expire($key,$exp_time);
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //7.2   redis 读出     连麦PK期间的送礼用户列表（$singer_id）
    public function redis_get_user_gift_list(&$error,$page_num,$singer_id,&$user_gift_list)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $start_number = $page_num * (linkcall_pk_model::$LINKCALL_PK_GIFT_PAGE_NUMBER - 1);
        $stop_number = $page_num * (linkcall_pk_model::$LINKCALL_PK_GIFT_PAGE_NUMBER) - 1;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_user_gift_list.page_num:$page_num error:".json_encode($error));
                break;
            }
            $get_list =array();
            $key = linkcall_pk_model::linkcallpk_gift_list_zset_key($singer_id);
            $get_list = $redis->zRevRange($key,$start_number,$stop_number,true);
            if (true == empty($get_list)) 
            {
                //如果取出数据为空，$user_gift_list返回是空值
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            //输出获取的列表
            foreach ($get_list as $score => $user_id)
            {
                $user_gift = array();
                $user_gift["user_id"] = (int)$user_id;
                $user_gift["user_gift"] = (int)$score;
                $user_gift_list[] = $user_gift;
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    } 
    
    //7.2b   redis 读出     连麦PK期间的送礼用户列表（$singer_id）
    public function redis_get_user_gift_5list(&$error,$singer_id,&$user_gift_list)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $start_number = 0;
        $stop_number = (linkcall_pk_model::$LINKCALL_PK_GIFT_FIRST5LIST) - 1;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_user_gift_list. singer_id：$singer_id error:".json_encode($error));
                break;
            }
            $get_list =array();
            $key = linkcall_pk_model::linkcallpk_gift_list_zset_key($singer_id);
            $get_list = $redis->zRevRange($key,$start_number,$stop_number,true);
            if (true == empty($get_list))
            {
                //如果取出数据为空，$user_gift_list返回是空值
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            //输出获取的列表
            foreach ($get_list as $score => $user_id)
            {
                $user_gift = array();
                $user_gift["user_id"] = (int)$user_id;
                $user_gift["user_gift"] = (int)$score;
                $user_gift_list[] = $user_gift;
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //7.3   redis 读出     连麦PK期间的其中一个送礼用户的金额
    public function redis_get_user_gift(&$error,$singer_id,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $gift = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_user_gift.singer_id:$singer_id user_id:$user_id error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_gift_list_zset_key($singer_id);
            $v = $redis->zScore($key,$user_id);
            if(true == empty($v))
            {
                //如果取出无数据，给个default 值 0，代表列表无数据
                $v = 0;
            }
            $gift =$v;
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $gift;
    }
    
    //7.4   redis 删除     连麦PK期间 的列表
    public function redis_del_user_gift_list(&$error,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';

        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_del_user_gift_list.singer_id:$singer_id error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_gift_list_zset_key($singer_id);
            $redis->del($key);
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }

    //8.1   redis 写入     服务器所有正在连麦PK 的pkid 号
    public function redis_set_singer_pkid(&$error,$pkid,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_singer_pkid.pkid:$pkid singer_id:$singer_id error:".json_encode($error));
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_singer_pkid_zset_key();
            $redis->zAdd($key, $pkid, $singer_id);
            $redis->expire($key,$exp_time);
            
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //8.2   redis 读出    服务器所有正在连麦PK 的pkid 号
    public function redis_get_singer_pkid(&$error,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $pkid = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_pkid.singer_id:$singer_id error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_pkid_zset_key();
            //取出$pkid
            $get_pkid = $redis->zScore($key,$singer_id);
            if(true == empty($get_pkid))
            {
                //如果无数据，说明该用户不在 PK当中
                $get_pkid = 0 ;
            }
            $pkid = $get_pkid; 
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $pkid;
    }
    
    //8.3   redis 读出     服务器所有正在连麦PK 的  主播列表
    public function redis_get_singer_pkid_singerlist(&$error,&$singer_list)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_pkid.singer_id:$singer_id error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_pkid_zset_key();
            //取出$pkid
            
            $get_singer_list = $redis->zRange($key,$singer_id);
            if (true == empty($get_singer_list))
            {
                //如果取出数据为空，$user_gift_list返回是空值
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            //输出获取的列表
            foreach ($get_singer_list as $score => $singer_id)
            {
                $singer_list[] = $singer_id;
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }

    //8.4   redis 移除     服务器所有正在连麦PK 的   中某个主播
    public function redis_rem_singer_pkid(&$error,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_rem_singer_pkid.singer_id:$singer_id error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_pkid_zset_key();            
            $rem_pkid = $redis->zRem($key,$singer_id);
            if(true == empty($rem_pkid))
            {
                //如果无数据，说明该用户不在 PK当中,忽略错误
            }

            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //9.1   redis 写入    服务器所有正在连麦PK的主播id 及礼物金额
    public function redis_set_PKing_info_singer_gift(&$error,$singer_id,$gift)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {    
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_PKing_info_singer_gift.singer_id:$singer_id gift:$gift error:".json_encode($error));
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_PKing_singer_gift_zset_key();
            $e=$redis->zIncrBy($key, $gift, $singer_id);
            if(true == empty($e) || $e < 0 || $gift < 0)
            {
                // 403400015(015)礼物金额登记失败
                $error['code'] = 403400015;
                $error['desc'] = '礼物金额登记失败';
                LogApi::logProcess("linkcall_pk_model.redis_set_PKing_info_singer_gift.singer_id:$singer_id gift:$gift error:".json_encode($error));
                break;
            }
            $redis->expire($key,$exp_time);

            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //9.2   redis 读出    服务器所有正在连麦PK的主播id 及礼物金额 列表
    public function redis_get_PKing_info_singer_gift_list(&$error,&$singer_list)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';

        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_PKing_info_singer_gift_list. error:".json_encode($error));
                break;
            }
            $get_list =array();
            $key = linkcall_pk_model::linkcallpk_PKing_singer_gift_zset_key();
            $get_list = $redis->zRevRange($key,0,-1);
            if (true == empty($get_list))
            {
                //如果取出数据为空，$user_gift_list返回是空值
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }    
            //输出获取的列表
            foreach ($get_list as $singer_id)
            {
                $singer_list[] = $singer_id;
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //9.3   redis 读出     服务器所有正在连麦PK的主播id 及礼物金额     当中某个主播的金额   
    public function redis_get_PKing_info_singer_gift(&$error,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $gift = 0 ;
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_PKing_info_singer_gift.singer_id:$singer_id error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_PKing_singer_gift_zset_key();
            $v = $redis->zScore($key,$singer_id);
            if(true == empty($v))
            {
                //如果数据不存在，说明该用户结束PK

                $v = -1 ;
            }
            $gift = $v;
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    
    }    
    
    //9.4   redis 移除    服务器所有正在连麦PK的主播id 及礼物金额     当中某个主播
    public function redis_rem_PKing_info_singer_gift(&$error,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_rem_PKing_info_singer_gift.singer_id:$singer_id error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_PKing_singer_gift_zset_key();
            $v = $redis->zRem($key,$singer_id);
            if(true == empty($v))
            {
                //如果数据不存在，忽略错误
                LogApi::logProcess("linkcall_pk_model.redis_rem_PKing_singer_gift.zRem 删除数据返回0: singer_id:$singer_id");
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    
    }
    
    //10.1   redis 写入     服务器记录主播连麦PK状态缓存
    public function redis_set_singer_state(&$error,$singer_state,$selfsinger,$objsinger)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_set_singer_state.singer_state:$singer_state objsinger:$objsinger selfsinger:$selfsinger error:".json_encode($error));
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_singer_state_hash_key($selfsinger);
            $redis->hSet($key, $objsinger, $singer_state);
            $redis->expire($key,$exp_time);
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //10.2   redis 读出     服务器记录主播连麦PK状态缓存
    public function redis_get_singer_state(&$error,$selfsinger,$objsinger)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $singer_state = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_get_singer_state. objsinger:$objsinger selfsinger:$selfsinger error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_state_hash_key($selfsinger);
            //取出$pkid
            $get_state = $redis->hGet($key,$objsinger);
            if(true == empty($get_state))
            {
                //如果无数据，给出改用户是下线状态
                $get_state = linkcall_pk_model::LINKCALL_PK_SINGER_OFFLINE ;
            }
            $singer_state = $get_state;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $singer_state;
    }

    //10.3   redis 移除     服务器记录主播连麦PK状态缓存 当中其中一个用户状态
    public function redis_rem_singer_state(&$error,$selfsinger,$objsinger)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                LogApi::logProcess("linkcall_pk_model.redis_rem_singer_state. objsinger:$objsinger selfsinger:$selfsinger error:".json_encode($error));
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_state_hash_key($selfsinger);
            $redis->hDel($key, $objsinger);
            //如果用户本身不在里面，删除忽略错误
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //功能模块
    //A.1 根据主播id，拼装主播信息
    public function linkcallpk_singer_apply_info_by_singerid(&$error,$singer_id,$objsinger,&$singer_info)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $singer_state = 0;
        do
        {
            $singer_cache = array ();
            //1 查看$objsinger 是否在  $singer_id 已经申请列表里面
            $singer_state = $this->redis_get_singer_state(&$error,$singer_id,$objsinger);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            //2 用$singer_id 去获取主播基础信息
            $this->redis_get_singer_info(&$error,$singer_id,&$singer_cache);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //拼装主播 data
            $singer_info[]= $singer_cache;
            $singer_info['state'] = (int)$singer_state;
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcallpk_singer_info_by_singerid error:".json_encode($error));
        }
    }
    
    //A.2 根据主播id，拼装主播信息
    public function linkcallpk_singer_link_info_by_singerid(&$error,$singer_id,$objsinger,&$singer_info)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $singer_state = 0;
        do
        {
            $singer_cache = array ();
            //1 用$objsinger 和$singer_id  去获取主播状态
            $singer_state = $this->redis_get_singer_state(&$error,$singer_id,$objsinger);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            //2 用$singer_id 去获取主播基础信息
            $this->redis_get_singer_info(&$error,$singer_id,&$singer_cache);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //拼装主播 data
            $singer_info[]= $singer_cache;
            $singer_info['state'] = (int)$singer_state;
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcallpk_singer_info_by_singerid error:".json_encode($error));
        }
    }
  
    //B 在连麦PK情况下，这个用户的数据 通过用户id，拼装用户信息
    public function linkcallpk_user_info_by_userid(&$error,$singer_id,$user_id,&$user_info)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $user_gift = 0;
        do
        {
            $user_cache = array ();
            //1 用$user_id 去获取用户送礼 总金额 $user_gift
            $user_gift = $this->redis_get_user_gift(&$error, $singer_id ,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            //2 用$user_id 去获取用户基础信息
            $this->redis_get_user_info(&$error,$user_id,&$user_cache);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //拼装用户 data
            $user_info []= $user_cache;
            $user_info['user_gift'] = (int)$user_gift;
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcallpk_user_info_by_userid error:".json_encode($error));
        }
    }
    
    //C 在连麦PK环境下，根据 pkid ，拼装PK信息
    public function linkcallpk_PK_info_by_PKsinger(&$error,$pkid,&$pk_info)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $starttime = 0;
        $pkalltime = 0;
        $host_id   = 0;
        $guest_id  = 0;
        $host_gift   = 0;
        $guest_gift  = 0;
        do
        {   

            //1 用$pkid 去获取PK基础信息
            $this->redis_get_PK_info(&$error,$pkid,&$starttime,&$pkalltime,&$host_id,&$guest_id,&$host_gift,&$guest_gift);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //2 查看主场主播是否已经结束PK
            $get_host_gift = $this->redis_get_PKing_info_singer_gift(&$error,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //if 如果主场主播已经结束PK，查询的金额是已经释放后的数据，需要根据pkid 的 PKinfo缓存，采用缓存数据来做结算
            if($get_host_gift == -1 )
            {
                //什么都不用做，当前的数据就是缓存数据
            }
            else 
            {
                $host_gift = $get_host_gift;
                //并且同样道理取出客场礼物金币
                $get_guest_gift = $this->redis_get_PKing_info_singer_gift(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if($get_guest_gift == -1 )
                {
                    //容错，让他为0
                    $get_host_gift = 0;
                }
                $guest_gift = $get_guest_gift;
            }
            
            //拼装PK info
            $pk_info["starttime"] = $starttime;
            $pk_info["pkalltime"] = $pkalltime;
            $pk_info["host_gift"] = $host_gift;
            $pk_info["guest_gift"] = $guest_gift;
            $pk_info["host_id"] = $host_id;
            $pk_info["guest_id"] = $guest_id;  
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcallpk_PK_info_by_PKsinger error:".json_encode($error));
        }
    }
    
    //D 根据客户端的分页号，取出该分页的主播信息singer_datas
    public function linkcallpk_singer_datas_by_pag_num(&$error,$singer_id,$pag_num,&$singer_datas)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';

        do
        {
            //1 取出该分页的主播id list
            $singer_list = array ();
            $this->redis_get_online_singer_list(&$error,$page_num,$singer_id,&$singer_list);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            if (true == empty($singer_list))
            {
                //如果取出数据为空，$user_gift_list返回是空值
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }

            //2 遍历该列表list 拼装主播信息,并放入$singer_datas
            foreach ($singer_list as $objsinger)
            {
                //如果被操作的主播$objsinger是  主播$singer_id 本身，需要跳过
                if ($objsinger == $singer_id)
                {
                    continue;
                }
                $singer_cache = array ();
                //2.1 取出这些被操作的主播$objsinger是否在主播$singer_id的申请列表里面
                $get_apply_time = $this->redis_get_singer_guest_apply_time(&$error,$selfsinger,$objsinger);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //如果取出的申请时间是0，说明$objsinger并未在 $singer_id 申请列表里面
                if ($get_apply_time == 0)
                {
                    $objsinger_state = 0;
                }
                //2.2 取出主播状态
                $objsinger_state = $this->redis_get_singer_state(&$error,$singer_id,$objsinger);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //如果取出的数据是用户已经下线，但是现在遍历用户发现用户又已经上线，因此需要更改用户状态为用户已经上线
                if ($objsinger_state == linkcall_pk_model::LINKCALL_PK_SINGER_OFFLINE)
                {
                    //设置主播状态为上线可以申请
                    $objsinger_state == linkcall_pk_model::$LINKCALL_PK_SINGER_RQ_APPLY;
                    $this->redis_set_singer_state(&$error,$objsinger_state,$singer_id,$objsinger);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                }
                $singer_cache["state"] = $objsinger_state;
                $singer_datas[] = $singer_cache;
            }
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcallpk_singer_datas_by_pag_num error:".json_encode($error));
        }
    }
    
    //E 根据主播id，推送通知nt给主播
    public function linkcallpk_singer_nt_singer_PKinfo(&$error,$nt_singer_id,$nt_singer_sid,$objsinger)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            //1 取出服务器时间
            $time_now = time();
            
            //2 取出pkid号（如果pkid号是0，代表该主播没有开始PK）
            $pkid = $this->redis_get_singer_pkid(&$error,$nt_singer_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            $singer_info = array();
            //3 取出主播信息
            $this->linkcallpk_singer_info_by_singerid(&$error,$nt_singer_id,$objsinger,&$singer_info);           
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }    
            
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcallpk_singer_nt_singer_PKinfo error:".json_encode($error));
        }
        //拼装nt包///////////////////////////////////////////////////////////////////////////////////////////
        $nt=array();
        $nt['cmd'] = 'linkcallpk_singer_PKinfo_nt';
        $nt['time_now'] = $time_now;
        $nt['pkid'] = (int)$pkid;
        $nt['singer'] = $singer_info;

        //涉及两个房间推送，采用房间之间的回推
        $m = new cback_channel_model();
        $m->unicast($nt_singer_sid, $nt_singer_id, $nt);
        LogApi::logProcess("linkcallpk_singer_PKinfo_nt sid:$nt_singer_sid singer_id:".$nt_singer_id." nt:".json_encode($nt));
    }
    
    //E 根据主播房间sid号，多播通知nt给房间(包括主客场主播id和sid，送礼的用户id和收礼主播id)
    //备注：如果此时推送pkid是0，则没有pk信息，如果user_id是0，则没有礼物变化信息。
    public function linkcallpk_room_nt_PKinfo(&$error,$host_id,$host_sid,$guest_id,$guest_sid,$user_id,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            //1 取出服务器时间
            $time_now = time();
    
            //2 取出pkid号（如果pkid号是0，代表该主播没有开始PK）
            $pkid = $this->redis_get_singer_pkid(&$error,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
        
            $pk_info = array();
            
            //3 如果有pkid，取出PK信息；如果 没有pikd，则$pk_info是空
            if ( $pk_info !=0 )
            {
                $this->linkcallpk_PK_info_by_PKsinger(&$error,$pkid,&$pk_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }

            //4 取出变化用户数据            
            $user_info = array();
            if (  $user_id != 0 )
            {
                $this->linkcallpk_user_info_by_userid(&$error,$singer_id,$user_id,&$user_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcallpk_room_nt_PKinfo error:".json_encode($error));
        }
        //拼装nt包///////////////////////////////////////////////////////////////////////////////////////////
        $nt=array();
        $nt['cmd'] = 'linkcallpk_room_pkinfo_nt';
        $nt['time_now'] = $time_now;
        $nt['pkid'] = (int)$pkid;
        $nt['pk'] = $pk_info;
        $nt['user'] = $user_info;

        //涉及两个房间推送，采用房间之间的广播
        $m = new cback_channel_model();
        $m->broadcast($host_sid, $nt);
        $m->broadcast($guest_sid, $nt);
        
        LogApi::logProcess("linkcallpk_room_pkinfo_nt host_sid:$host_sid guest_sid:".$guest_sid." nt:".json_encode($nt));
    }
    
 }
