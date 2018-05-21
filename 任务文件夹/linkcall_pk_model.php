 <?php
 
 //连麦pk功能
 class linkcall_pk_model extends ModelBase
{ 
    public function __construct ()
    {
        parent::__construct();
    }
    //linkcall_pk_model 常量：     
    public static $LINKCALL_PK_SET_CONTROL                   = 1;//连麦pk功能控制阀门
 
    public static $LINKCALL_PK_EXP_TIME                      = 259200;//默认连麦redis无操作最大缓存时长（3天）
    public static $LINKCALL_PK_EXP_60S_TIME                  = 300;    //默认连麦  小键    生命期  60s * 5
    public static $LINKCALL_PK_EXP_POPUP_TIME                = 30;    //客户端最大弹窗生命时间 30s

    public static $LINKCALL_PK_SINGER_OFFLINE                =0;    //主播下线
    public static $LINKCALL_PK_SINGER_APPLY                  =1;    //申请         连麦pk
    public static $LINKCALL_PK_SINGER_APPLYING               =2;    //已申请    连麦pk    
    public static $LINKCALL_PK_SINGER_LINK                   =3;    //连线         连麦pk
    public static $LINKCALL_PK_SINGER_LINKING                =4;    //已连线    连麦pk
    public static $LINKCALL_PK_SINGER_PKING                  =5;    //主播正在pk
    public static $LINKCALL_PK_SINGER_GAMING                 =6;    //主播正在游戏
    public static $LINKCALL_PK_SINGER_SAWING                 =7;    //主播正在电锯
    public static $LINKCALL_PK_SINGER_POPUP                  =8;    //主播收到一个连线弹窗，未处理
    public static $LINKCALL_PK_SINGER_NO                     =9;    //拒绝连线
    public static $LINKCALL_PK_SINGER_YES                    =10;    //同意连线
    public static $LINKCALL_PK_SINGER_START                  =11;    //开始pk  
    public static $LINKCALL_PK_SINGER_COUNT                  =12;    //结算pk（这个是时间到用尽结算，暂未退出pk）
    public static $LINKCALL_PK_SINGER_ADDTIME                =13;   //延长pk
    public static $LINKCALL_PK_SINGER_OVER                   =14;   //结束pk（这个有可能是提前结算，并退出pk）    

     
    public static $LINKCALL_PK_PKINFO_NOPK                   =0;    //这个pkid 没有在pk
    public static $LINKCALL_PK_PKINFO_READY                  =1;    //这个pkid 建立pk界面
    public static $LINKCALL_PK_PKINFO_PKING                  =2;    //这个pkid 当前正在pk，未结束
    public static $LINKCALL_PK_PKINFO_BEYOND                 =3;    //这个pkid 超出了pk时间
    public static $LINKCALL_PK_PKINFO_ACCOUNT                =4;    //这个pkid pk结束，进行结算
    
    public static $LINKCALL_PK_SCENE_PK                      =0;    //pk   场景
    public static $LINKCALL_PK_SCENE_HOST                    =1;    //pk   主场主播场景
    public static $LINKCALL_PK_SCENE_GUEST                   =2;    //pk   客场主播场景

    
    
    public static $LINKCALL_PK_PAGE_NUMBER                   = 10;//显示主播列表分页记录条数
    public static $LINKCALL_PK_GIFT_PAGE_NUMBER              = 10;//显示送礼排行榜分页记录调试
    public static $LINKCALL_PK_GIFT_FIRST5LIST               = 5; //显示送礼排行榜最前的5个送礼列表

    //linkcall_pk_model 配置文件：  select id,parm1,parm2,parm3 from card.parameters_info where id =292 ||id =293 ||id =297;
    public static $LINKCALL_PK_SINGER_STAR    =  292;   //申请pk主播的最低星级
    public static $LINKCALL_PK_LINK_PKTIME    =  293;   //连麦正常pk总时间
    public static $LINKCALL_PK_LINK_ADDTIME   =  297;   //连麦pk 延长时间
    public static $LINKCALL_PK_LINK_POPUPTIME =  298;   //连麦pk 弹窗时间
    
    // 0 redis 连麦pk 的mysql 配置信息缓存：
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
    // 3 redis 服务器在线连麦可pk主播列表缓存:
    public static function linkcallpk_singer_onlinelist_zset_key()
    {
        return "linkcallpk:singer:onlinelist:zset";
    }  

    // 4.1 redis 主播客场申请pk列表（singer_id）缓存:
    public static function linkcallpk_singer_guestlist_zset_key($singer_id)
    {
        return "linkcallpk:singer:guestlist:zset:$singer_id";
    }
    
    // 4.2 redis 主播主场连线pk列表（singer_id）缓存:
    public static function linkcallpk_singer_hostlist_zset_key($singer_id)
    {
        return "linkcallpk:singer:hostlist:zset:$singer_id";
    }   
    
    // 5 redis 连麦pk号创建
    public static function linkcallpk_pkid_create_string_key()
    {
        return "linkcallpk:pkid:create:string";
    }  
    // 6.1 redis 连麦pk信息缓存
    public static function linkcallpk_pk_info_hash_key($pkid)
    {
        return "linkcallpk:pk:info:hash:$pkid";
    }
    // 6.2 redis 连麦pk 主播弹窗（规定时间内一个主播只能收到一个弹窗，主播拒绝可以去掉该弹窗）
    public static function linkcallpk_pk_popup_zset_key()
    {
        return "linkcallpk:pk:popup:zset";
    }
    // 7 redis 连麦pk期间的送礼用户列表
    public static function linkcallpk_gift_list_zset_key($singer_id)
    {
        return "linkcallpk:gift:list:zset:$singer_id";
    }
    // 8.1 redis 连麦pk期间主播对应pk号 缓存
    public static function linkcallpk_singer_pkid_zset_key()
    {
        return "linkcallpk:singer:pkid:zset";
    }
    
    // 8.2 redis 连麦pk期间主播对应场景 scene 缓存
    public static function linkcallpk_singer_scene_zset_key()
    {
        return "linkcallpk:singer:scene:zset";
    }
    
     // 9 redis 服务器所有正在连麦pk的主播id 及礼物金额
    public static function linkcallpk_pking_singer_gift_zset_key()
    {
        return "linkcallpk:pking:singer:gift:zset";
    }  

    // 10 redis 服务器记录当前所有还有弹窗的双方信息
    public static function linkcallpk_guest_popup_from_host_hash()
    {
        return "linkcallpk:guest:popup:from:host:hash";
    } 

    //0.1    redis 写入     mysql配置数据缓存：
    public function redis_set_mysql_info(&$error,$id1,$id2,$id3,$id4,$value1,$value2,$value3,$value4)
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
            
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_60S_TIME;
            $key = linkcall_pk_model::linkcallpk_mysql_config_info_hash_key();
            $redis->hSet($key,$id1,$value1);
            $redis->hSet($key,$id2,$value2);
            $redis->hSet($key,$id3,$value3);
            $redis->hSet($key,$id4,$value4);
            $redis->expire($key,$exp_time);
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    
    }
    
    // 0.1    redis 读出     mysql配置数据缓存：
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_mysql_config_info_hash_key();
            $get_value = $redis->hGet($key,$id);
            if(true == empty($get_value))
            {
                // 403400012(012)读取数据为空,需要重新写入数据
                //取mysql值
                $sys_parameters = new SysParametersModel();
                $id1    = linkcall_pk_model::$LINKCALL_PK_SINGER_STAR;
                $id2    = linkcall_pk_model::$LINKCALL_PK_LINK_PKTIME;
                $id3    = linkcall_pk_model::$LINKCALL_PK_LINK_ADDTIME;
                $id4    = linkcall_pk_model::$LINKCALL_PK_LINK_POPUPTIME;
                $value1 = $sys_parameters->GetSysParameters($id1, 'parm1');
                $value2 = $sys_parameters->GetSysParameters($id2, 'parm1');
                $value3 = $sys_parameters->GetSysParameters($id3, 'parm1');
                $value4 = $sys_parameters->GetSysParameters($id4, 'parm1');
                //重新写入redis
                $this->redis_set_mysql_info(&$error,$id1,$id2,$id3,$id4,$value1,$value2,$value3,$value4);
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
                }elseif ($id == $id3){
                    $value = $value3;
                }elseif ($id == $id4){
                    $value = $value4;
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_mysql_info. id:$id error:".json_encode($error));
        }
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
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_singer_info_hash_key(); 
            $redis->hSet($key,$singer_id,json_encode($singer_cache));
            $redis->expire($key,$exp_time); 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_singer_info.singer_id:$singer_id error:".json_encode($error));
        }
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_info_hash_key();           
            $singer_data_json = $redis->hGet($key,$singer_id);
            if(true == empty($singer_data_json))
            {
                // 403400012(012)读取数据为空
                $error['code'] = 403400012;
                $error['desc'] = '读取数据为空';        
                break;
            }            
            $v = json_decode($singer_data_json, true);
            if(true == empty($v))
            {
                // 403400013(013)解包失败
                $error['code'] = 403400013;
                $error['desc'] = '解包失败';               
                break;
            }
            $singer_cache = $v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
           LogApi::logProcess("linkcall_pk_model.redis_get_singer_info.singer_id:$singer_id error:".json_encode($error));
        }
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
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_user_info_hash_key(); 
            $redis->hSet($key,$user_id,json_encode($user_cache));
            $redis->expire($key,$exp_time); 
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_user_info.user_id:$user_id error:".json_encode($error));
        }
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_user_info_hash_key();           
            $user_data_json = $redis->hGet($key,$user_id);
            if(true == empty($user_data_json))
            {
                // 403400012(012)读取数据为空
                $error['code'] = 403400012;
                $error['desc'] = '读取数据为空';
                break;
            }            
            $v = json_decode($user_data_json, true);
            if(true == empty($v))
            {
                // 403400013(013)解包失败
                $error['code'] = 403400013;
                $error['desc'] = '解包失败';
                break;
            }
            $user_cache = $v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_singer_info.user_id:$user_id error:".json_encode($error));
        }
    }
    

    //3.1   redis 写入     服务器在线连麦可pk主播列表
    public function redis_set_online_singer_list(&$error,$singer_id,$time_open)
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
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_singer_onlinelist_zset_key();
            $e=$redis->zAdd($key, $time_open, $singer_id);
            $redis->expire($key,$exp_time);
            //备注：如果该主播已经在列表，则刷新申请时间，用于其他主播在更前面位置看到记录
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
           LogApi::logProcess("linkcall_pk_model.redis_set_online_singer_list.singer_id:$singer_id time_open:$time_open error:".json_encode($error));
        }
    }  

    //3.2   redis 读出     服务器在线连麦可pk主播列表（分页）    
    public function redis_get_online_singer_list(&$error,$page_num,$selfsinger,&$singer_list)	
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $start_number = $page_num * (linkcall_pk_model::$LINKCALL_PK_PAGE_NUMBER);
        $stop_number = $page_num * (linkcall_pk_model::$LINKCALL_PK_PAGE_NUMBER) - 1;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';                
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_online_singer_list.page_num:$page_num error:".json_encode($error));
        }
    }
    
    //3.3   redis 移除     服务器在线连麦可pk主播列表中一个
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_onlinelist_zset_key();
            $v = $redis->zRem($key,$singer_id);
            if(true == empty($v))
            {
                //如果数据不存在，忽略错误                
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_rem_online_singer_list.singer_id:$singer_id error:".json_encode($error));
        }
    
    }
    
    //3.4   redis 读出     服务器在线连麦可pk主播 中 其中一个主播的  开启时间
    public function redis_get_online_singer_list_opentime(&$error,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $time_open = 0;    
        do
        {
    
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';                
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_onlinelist_zset_key();
            $e=$redis->zScore($key, $singer_id);
            if (true == empty($e))
            {
                //如果取出数据为空，给个0 值
                $e = 0;
            }
            $time_open = $e;
            
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_online_singer_list_applytime.singer_id:$singer_id error:".json_encode($error));
        }
        return $time_open;
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_singer_guest_apply_list.selfsinger:$selfsinger objsinger:$objsinger time_apply:$time_apply error:".json_encode($error));
        }
    }
    
    //4.2   redis 读出     主播客场连麦pk申请列表
    public function redis_get_singer_guest_apply_list(&$error,$selfsinger,&$objsinger_list)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $start_number = 0;
        $stop_number = -1;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';                
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_singer_guest_apply_list. selfsinger:$selfsinger  error:".json_encode($error));
        }
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_singer_guest_apply_time. selfsinger:$selfsinger objsinger:$objsinger error:".json_encode($error));
        }
        return $time_apply;
    }
    
    //4.4a   redis 移除     主播客场连麦pk申请列表（$guest_id）列表中一个
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_guestlist_zset_key($selfsinger);
            $redis->zRem($key,$objsinger);
            //如果数据不存在，忽略错误
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_rem_singer_guest_apply_list. selfsinger:$selfsinger objsinger:$objsinger error:".json_encode($error));
        }
    }
    
    //4.4b   redis 删除这个主播的申请列表
    public function redis_del_singer_guest_apply_list(&$error,$selfsinger)
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_guestlist_zset_key($selfsinger);
            $redis->del($key);
            //如果数据不存在，忽略错误
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_del_singer_guest_apply_list. selfsinger:$selfsinger error:".json_encode($error));
        }
    }
    
    //4.5   redis 写入     主播主场连线列表（$guest_id）
    public function redis_set_singer_host_link_list(&$error,$selfsinger,$objsinger,$time_link)
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
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_singer_hostlist_zset_key($selfsinger);
            $e=$redis->zAdd($key, $time_link, $objsinger);
            $redis->expire($key,$exp_time);
            //备注：如果该主播已经在列表，则刷新申请时间，用于其他主播在更前面位置看到记录
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_singer_host_link_list.selfsinger:$selfsinger objsinger:$objsinger time_link:$time_link error:".json_encode($error));
        }
    }
    
    //4.6   redis 读出     主播主场连线列表分页（$guest_id）
     public function redis_get_singer_host_link_list(&$error,$selfsinger,$page_num,&$objsinger_list)	
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $start_number = $page_num * (linkcall_pk_model::$LINKCALL_PK_PAGE_NUMBER);
        $stop_number = $page_num * (linkcall_pk_model::$LINKCALL_PK_PAGE_NUMBER) - 1;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';                
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_singer_host_link_list. selfsinger:$selfsinger page_num:$page_num error:".json_encode($error));
        }
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_singer_host_link_time. selfsinger:$selfsinger objsinger:$objsinger error:".json_encode($error));
        }
        return $time_link;
    }
    
    //4.8a   redis 移除     主播主场连线列表（$guest_id）列表中一个
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_hostlist_zset_key($selfsinger);
            $redis->zRem($key,$objsinger);
            //如果数据不存在，忽略错误
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_rem_singer_guestlist. selfsinger:$selfsinger objsinger:$objsinger error:".json_encode($error));
        }
    }
    
    //4.8b   redis 删除这个主播的连线列表
    public function redis_del_singer_host_link_list(&$error,$selfsinger)
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_hostlist_zset_key($selfsinger);
            $redis->del($key);
            //如果数据不存在，忽略错误
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_del_singer_host_link_list. selfsinger:$selfsinger error:".json_encode($error));
        }
    }
    
    //5   redis 创建     连麦pk pkid号
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
                break;
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_create_pkid. error:".json_encode($error));
        }
        return $v;
    }
    
    //6.1A   redis 写入    连麦pk信息缓存 pk状态
    public function redis_set_pk_info_process(&$error,$pkid,$pk_process)
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
                break;
            }    
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_pk_info_hash_key($pkid);
            $redis->hSet($key, "pk_process", $pk_process);
            $redis->expire($key,$exp_time);
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_pk_info_process.pkid:$pkid pk_process:$pk_process error:".json_encode($error));
        }
    }
    
    //6.2A   redis 读出     连麦pk信息缓存 pk状态
    public function redis_get_pk_info_process(&$error,$pkid,&$pk_process)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            if ($pkid == 0)
            {
                //pkid =0,直接退出，由于没有pk，默认情况 这个pkid 没有在pk。
                $pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK ;
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = linkcall_pk_model::linkcallpk_pk_info_hash_key($pkid);
            $get_key = $redis->hGet($key,"pk_process");
            if(true == empty($get_key))
            {
                // 403400012(012)读取数据为空,有pkid，但是没有状态，默认这个pkid未启用
                $get_key = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK ;
            }
            $pk_process = $get_key;    
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_pk_info_account.pkid:$pkid error:".json_encode($error));
        }
    }
    
    //6.1B   redis 写入     连麦pk信息缓存
    public function redis_set_pk_info(&$error,$pkid,$starttime,$pkalltime,$host_id,$host_sid,$guest_id,$guest_sid,$host_gift,$guest_gift)
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
                break;
            }
            $pk_info = array();
            $pk_info["starttime"]  = $starttime;
            $pk_info["pkalltime"]  = $pkalltime;
            $pk_info["host_id"]    = $host_id;
            $pk_info["host_sid"]   = $host_sid;
            $pk_info["guest_id"]   = $guest_id;
            $pk_info["guest_sid"]  = $guest_sid;
            $pk_info["host_gift"]  = $host_gift;
            $pk_info["guest_gift"] = $guest_gift;         
            
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_pk_info_hash_key($pkid);
            $redis->hSet($key, $pkid, json_encode($pk_info));  
            $redis->expire($key,$exp_time);

            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_pk_info.pkid:$pkid host_id:$host_id guest_id:$guest_id starttime:$starttime error:".json_encode($error));
        }
    }
    
    //6.2B   redis 读出     连麦pk信息缓存
    public function redis_get_pk_info(&$error,$pkid,&$pk_info)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';        
        do
        {
            if ($pkid == 0)
            {
                //pkid =0,直接退出
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';                
                break;
            }
            $key = linkcall_pk_model::linkcallpk_pk_info_hash_key($pkid);
            $pk_info_json = $redis->hGet($key,$pkid);
            if(true == empty($pk_info_json))
            {
                // 403400012(012)读取数据为空
                $error['code'] = 403400012;
                $error['desc'] = '读取数据为空';
                break;
            }
            $v = json_decode($pk_info_json, true);
            if(true == empty($v))
            {
                // 403400013(013)解包失败
                $error['code'] = 403400013;
                $error['desc'] = '解包失败';
                break;
            }
            $pk_info = $v;            

            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_pk_info.pkid:$pkid error:".json_encode($error));
        }
    }   
    //6.3   redis 写入     连麦pk信息缓存
    public function redis_set_pk_info_use_array(&$error,$pkid,&$pk_info)
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
                break;
            }    
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_pk_info_hash_key($pkid);
            $redis->hSet($key, $pkid, json_encode($pk_info));
            $redis->expire($key,$exp_time);
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_pk_info_use_array.pkid:$pkid  error:".json_encode($error));
        }
    }
    
    //6.4   redis 写入     服务器所有正在弹窗的主播的弹窗时间
    public function redis_set_pk_popup(&$error,$singer_id,$popup_time)
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
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_pk_popup_zset_key();
            $redis->zAdd($key, $popup_time, $singer_id);
            $redis->expire($key,$exp_time);
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_pk_popup singer_id:$singer_id popup_time:$popup_time error:".json_encode($error));
        }
    }
    
    //6.5   redis 读出      服务器所有正在弹窗的主播   该主播弹窗时间
    public function redis_get_pk_popup(&$error,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $popup_time = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';                
                break;
            }
            $key = linkcall_pk_model::linkcallpk_pk_popup_zset_key();
            //取出$popup_time
            $get_popup_time = $redis->zScore($key,$singer_id);
            if(true == empty($get_popup_time))
            {
                // 403400012(012)读取数据为空,给个0值
                $get_popup_time = 0;
            }
            $popup_time = $get_popup_time;

            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_pk_popup singer_id:$singer_id error:".json_encode($error));
        }
        return $popup_time;
    }
    
    //6.6   redis 删除      服务器所有正在弹窗的主播   其中一个主播的数据
    public function redis_rem_pk_popup(&$error,$singer_id)
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_pk_popup_zset_key();
            $redis->zRem($key,$singer_id);
            //如果移除失败，忽略错误
    
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_rem_pk_popup singer_id:$singer_id error:".json_encode($error));
        }
    }
    
    
    //7.1   redis 写入     连麦pk期间的送礼用户和金额（$singer_id）
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
                break;
            }
            $redis->expire($key,$exp_time);
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_user_gift.singer_id:$singer_id user_id:$user_id gift:$gift error:".json_encode($error));
        }
    }
    
    //7.2   redis 读出     连麦pk期间的送礼用户列表（$singer_id）
    public function redis_get_user_gift_list(&$error,$page_num,$singer_id,&$user_gift_list)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $start_number = $page_num * (linkcall_pk_model::$LINKCALL_PK_GIFT_PAGE_NUMBER);
        $stop_number = $page_num * (linkcall_pk_model::$LINKCALL_PK_GIFT_PAGE_NUMBER) - 1;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';                
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_user_gift_list.page_num:$page_num error:".json_encode($error));
        }
    } 
    
    //7.2b   redis 读出     连麦pk期间的送礼用户列表（$singer_id）
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
            foreach ($get_list as $user_id => $score)
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_user_gift_list. singer_id：$singer_id error:".json_encode($error));
        }
    }
    
    //7.3   redis 读出     连麦pk期间的其中一个送礼用户的金额
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_user_gift.singer_id:$singer_id user_id:$user_id error:".json_encode($error));
        }
        return $gift;
    }
    
    //7.4   redis 删除     连麦pk期间 的列表
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_gift_list_zset_key($singer_id);
            $redis->del($key);
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_del_user_gift_list.singer_id:$singer_id error:".json_encode($error));
        }
    }
    
    //7.5   redis 读出     连麦pk期间的其中一个送礼用户的金额  的倒叙排名
    public function redis_ranking_user_gift(&$error,$singer_id,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $ranking = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = linkcall_pk_model::linkcallpk_gift_list_zset_key($singer_id);
            $v = $redis->zRevRank($key,$user_id);
            if(is_null($v))
            {
                //如果取出无数据，因为是倒叙，没有值说明排名很后面，给个很大值给他
                $v = 999;
            }
            $ranking =$v;
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_ranking_user_gift.singer_id:$singer_id user_id:$user_id error:".json_encode($error));
        }
        return $ranking;
    }

    //8.1   redis 写入     服务器所有正在连麦pk 的pkid 号
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
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_singer_pkid_zset_key();
            $redis->zAdd($key, $pkid, $singer_id);
            $redis->expire($key,$exp_time);
            
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_singer_pkid.pkid:$pkid singer_id:$singer_id error:".json_encode($error));
        }
    }
    
    //8.2   redis 读出    服务器所有正在连麦pk 的其一个主播pkid 号
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_pkid_zset_key();
            //取出$pkid
            $get_pkid = $redis->zScore($key,$singer_id);
            if(true == empty($get_pkid))
            {
                //如果无数据，说明该用户不在 pk当中
                $get_pkid = 0 ;
            }
            $pkid = $get_pkid; 
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_singer_pkid.singer_id:$singer_id error:".json_encode($error));
        }
        return $pkid;
    }
    
    //8.3   redis 读出     服务器所有正在连麦pk 的  主播列表
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_singer_pkid.singer_id:$singer_id error:".json_encode($error));
        }
    }

    //8.4   redis 移除     服务器所有正在连麦pk 的   中某个主播
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_pkid_zset_key();            
            $rem_pkid = $redis->zRem($key,$singer_id);
            if(true == empty($rem_pkid))
            {
                //如果无数据，说明该用户不在 pk当中,忽略错误
            }

            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_rem_singer_pkid.singer_id:$singer_id error:".json_encode($error));
        }
    }
    
    //8.5   redis 设置  服务器所有正在连麦pk 的   中某个主播的场景状态
    public function redis_set_singer_scene(&$error,$pk_scene,$singer_id)
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
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_singer_scene_zset_key(); 
            $redis->zAdd($key, $pk_scene, $singer_id);
            $redis->expire($key,$exp_time);
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_singer_scene. singer_id:$singer_id pk_scene:$pk_scene error:".json_encode($error));
        }
    }
    
    //8.6   redis 读出    服务器所有正在连麦pk 中某个主播的场景状态
    public function redis_get_singer_scene(&$error,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $pk_scene = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_scene_zset_key();
            //取出$pkid
            $get_key = $redis->zScore($key,$singer_id);
            if(true == empty($get_key))
            {
                //如果无数据，默认是pk 界面
                $get_key = linkcall_pk_model::$LINKCALL_PK_SCENE_PK ;
            }
            $pk_scene = $get_key;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_singer_scene.singer_id:$singer_id error:".json_encode($error));
        }
        return $pk_scene;
    }
    
    //8.7   redis 移除     服务器所有正在连麦pk 的   中某个主播的场景状态
    public function redis_rem_singer_scene(&$error,$singer_id)
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_singer_scene_zset_key();
            $rem_key = $redis->zRem($key,$singer_id);
            if(true == empty($rem_key))
            {
                //如果无数据，说明该用户不在 pk当中,忽略错误
            }
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_rem_singer_scene.singer_id:$singer_id error:".json_encode($error));
        }
    }    

    
    //9.1   redis 写入    服务器所有正在连麦pk的主播id 及礼物金额
    public function redis_set_pking_info_singer_gift(&$error,$singer_id,$gift)
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
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_pking_singer_gift_zset_key();
            $e=$redis->zIncrBy($key, $gift, $singer_id);
            if(true == empty($e) )
            {
                // 403400015(015)礼物金额登记是0,忽略错误';
            }
            $redis->expire($key,$exp_time);
            
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_pking_info_singer_gift.singer_id:$singer_id gift:$gift error:".json_encode($error));
        }
    }
    
    //9.2   redis 读出    服务器所有正在连麦pk的主播id 及礼物金额 列表
    public function redis_get_pking_info_singer_gift_list(&$error,&$singer_list)
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
                break;
            }
            $get_list =array();
            $key = linkcall_pk_model::linkcallpk_pking_singer_gift_zset_key();
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
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_pking_info_singer_gift_list. error:".json_encode($error));
        }
    }
    
    //9.3   redis 读出     服务器所有正在连麦pk的主播id 及礼物金额     当中某个主播的金额   
    public function redis_get_pking_info_singer_gift(&$error,$singer_id)
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_pking_singer_gift_zset_key();
            $v = $redis->zScore($key,$singer_id);
            if(true == empty($v))
            {
                //如果数据不存在，给个0值
                $v = 0 ;
            }
            $gift = $v;
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_get_pking_info_singer_gift.singer_id:$singer_id error:".json_encode($error));
        }
        return $gift;
    }    
    
    //9.4   redis 移除    服务器所有正在连麦pk的主播id 及礼物金额     当中某个主播
    public function redis_rem_pking_info_singer_gift(&$error,$singer_id)
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_pking_singer_gift_zset_key();
            $v = $redis->zRem($key,$singer_id);
            if(true == empty($v))
            {
                //如果数据不存在，忽略错误

            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_rem_pking_info_singer_gift.singer_id:$singer_id error:".json_encode($error));
        }
    
    }
    
    //10.1   redis 写入    客场主播收到来自主场主播弹窗的信息
    public function redis_set_guest_popup_from_host(&$error,$host_id,$guest_id)
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
                break;
            }
            $exp_time =linkcall_pk_model::$LINKCALL_PK_EXP_TIME;
            $key = linkcall_pk_model::linkcallpk_guest_popup_from_host_hash();
            $redis->hSet($key, $guest_id, $host_id);
            $redis->expire($key,$exp_time);
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_guest_popup_from_host. host_id:$host_id guest_id:$guest_id  error:".json_encode($error));
        }
    }
    
    //10.2   redis 读出     客场主播收到来自主场主播弹窗的信息
    public function redis_get_guest_popup_from_host(&$error,$guest_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $host_id = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 403400011(011)网络数据库断开连接
                $error['code'] = 403400011;
                $error['desc'] = '网络数据库断开连接';                
                break;
            }
            $key = linkcall_pk_model::linkcallpk_guest_popup_from_host_hash();
            //取出$pkid
            $get_host_id = $redis->hGet($key,$guest_id);
            if(true == empty($get_host_id))
            {
                //如果无数据，需要返回一个0值给函数做判断
                $get_host_id = 0;
            }
            $host_id = $get_host_id;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_set_guest_popup_from_host. guest_id:$guest_id  error:".json_encode($error));
        }
        return $host_id;
    }

    //10.3   redis 移除     客场主播收到来自主场主播弹窗的信息
    public function redis_rem_guest_popup_from_host(&$error,$guest_id)
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
                break;
            }
            $key = linkcall_pk_model::linkcallpk_guest_popup_from_host_hash();
            $redis->hDel($key, $guest_id);
            //如果用户本身不在里面，忽略错误
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.redis_rem_guest_popup_from_host. guest_id:$guest_id  error:".json_encode($error));
        }
    }
    
    
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //功能模块    
  
    //A 在连麦pk情况下，这个用户的数据 通过用户id，拼装用户信息
    public function linkcallpk_user_info_by_userid(&$error,$singer_id,$user_id,&$user_info)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $user_gift = 0;
        do
        {
            $user_cache = array ();
            //0 用$user_id 去获取用户送礼 总金额 在列表的位置（如果超出规定位数，就不需要推送用户数据了）
            $ranking = $this->redis_ranking_user_gift(&$error,$singer_id,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            LogApi::logProcess("linkcall_pk_model.linkcallpk_user_info_by_userid singer_id:$singer_id user_id:$user_id ranking:$ranking");
            if ($ranking > linkcall_pk_model::$LINKCALL_PK_GIFT_FIRST5LIST) 
            {
                //不在规定范围里面，不需要推送数据变更
                $error['code'] = 0;
                $error['desc'] = '';
                break;
                
            }
            
            
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
            $user_cache['user_gift'] = (int)$user_gift;
            $user_info = $user_cache;
            
            
            $error['code'] = 0;
            $error['desc'] = '';
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.linkcallpk_user_info_by_userid error:".json_encode($error));
        }
    }
    
    //B 在连麦pk情况下，这个用户的数据 通过用户id，拼装用户信息（不对前五做过滤）
    public function linkcallpk_user_info_by_userid_all(&$error,$singer_id,$user_id,&$user_info)
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
            $user_cache['user_gift'] = (int)$user_gift;
            $user_info = $user_cache;
    
    
            $error['code'] = 0;
            $error['desc'] = '';
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.linkcallpk_user_info_by_userid_all error:".json_encode($error));
        }
    }
    
    //C 在连麦pk环境下，根据 pkid ，拼装pk信息
    public function linkcallpk_pk_info_by_pkid(&$error,$pkid,&$pk_info)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $neibu_pk_info =array();
        do
        {   

            //1 查看这个pkid号是否已经结束pk（0：没有pk进行   1刚刚创建pk，未开始   2：pk时间用尽  9：pk还未结束）
            $is_pking = $this->linkcallpk_is_pking_by_pkid(&$error,$pkid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }            
            if ($is_pking == 0)
            {
                //如果目前不在pk，直接返回空的$pk_info信息
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            //2 用$pkid 去获取pk基础信息（备注，由于送礼金额时时刷新，因此基础信息的金额除非结算，否则是不变的）
            $this->redis_get_pk_info(&$error,$pkid,&$neibu_pk_info);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            if($is_pking == 9 )
            {
                //需要用在线主播金额替换缓存金额
                $host_id = (int)$neibu_pk_info["host_id"];
                $guest_id = (int)$neibu_pk_info["guest_id"];
                //取出主场主播最新的礼物总金额
                $get_host_gift = $this->redis_get_pking_info_singer_gift(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $host_gift = $get_host_gift;
                //取出客场主播最新的礼物总金额
                $get_guest_gift = $this->redis_get_pking_info_singer_gift(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $guest_gift = $get_guest_gift;
                $neibu_pk_info["host_gift"] = (int)$host_gift;
                $neibu_pk_info["guest_gift"] = (int)$guest_gift;
                $pk_info = $neibu_pk_info;
                
            }
            if ($is_pking == 1) 
            {
                //刚刚创建pk
                $neibu_pk_info["host_gift"] = (int)0;
                $neibu_pk_info["guest_gift"] = (int)0;   
                $pk_info = $neibu_pk_info;
            }
            if ($is_pking == 2) 
            {                
                $this->redis_get_pk_info(&$error,$pkid,&$pk_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
  
            
            $error['code'] = 0;
            $error['desc'] = '';
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.linkcallpk_pk_info_by_pkid error:".json_encode($error));
        }
    }
    
    //D 根据客户端的分页号，取出申请列表中该分页的主播信息singer_datas
    public function linkcallpk_apply_singer_datas_by_pag_num(&$error,$singer_id,$page_num,&$singer_datas)
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
                //2.1 如果被操作的主播$objsinger是  主播$singer_id 本身，需要跳过
                if ($objsinger == $singer_id)
                {
                    continue;
                }
                $singer_cache = array ();
                //2.2 取出这些被操作的主播$objsinger是否在主播$singer_id的申请列表里面
                $get_apply_time = $this->redis_get_singer_guest_apply_time(&$error,$singer_id,$objsinger);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //如果取出的申请时间是0，说明$objsinger并未在 $singer_id 申请列表里面,申请状态是未申请，否则是已经申请
                if ($get_apply_time == 0)
                {
                    $objsinger_state = linkcall_pk_model::$LINKCALL_PK_SINGER_APPLY;
                }
                else 
                {
                    $objsinger_state = linkcall_pk_model::$LINKCALL_PK_SINGER_APPLYING;
                }
                //2.3 取出该主播的缓存信息
                $this->redis_get_singer_info(&$error,$objsinger,&$singer_cache);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $singer_cache["pk_state"] = $objsinger_state;
                $singer_datas[] = $singer_cache;
            }
            
            $error['code'] = 0;
            $error['desc'] = '';
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.linkcallpk_apply_singer_datas_by_pag_num error:".json_encode($error));
        }
    }
    
    //E 根据客户端的分页号，取出连线列表中该分页的主播信息singer_datas
    public function linkcallpk_link_singer_datas_by_pag_num(&$error,$singer_id,$page_num,&$singer_datas)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            //1 取出该分页的主播id list
            $singer_list = array ();
            $this->redis_get_singer_host_link_list(&$error,$singer_id,$page_num,&$singer_list);
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

                $singer_cache = array ();
                //2.1 取出该主播是否在服务器所有正在弹窗的列表里面
                $get_popup_time = $this->redis_get_pk_popup(&$error,$objsinger);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //如果取出的弹窗时间，确定主播是否有弹窗显示
                $time_now = time();
                if ($time_now - $get_popup_time > linkcall_pk_model::$LINKCALL_PK_EXP_POPUP_TIME)
                {
                    $objsinger_state = linkcall_pk_model::$LINKCALL_PK_SINGER_LINK;
                }
                else
                {
                    $objsinger_state = linkcall_pk_model::$LINKCALL_PK_SINGER_LINKING;
                }
                //2.2 取出该主播的缓存信息
                $this->redis_get_singer_info(&$error,$objsinger,&$singer_cache);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $singer_cache["pk_state"] = $objsinger_state;
                $singer_datas[] = $singer_cache;
            }
            
            $error['code'] = 0;
            $error['desc'] = '';
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.linkcallpk_link_singer_datas_by_pag_num error:".json_encode($error));
        }
    }
    
    //F 根据主播id，推送通知nt给主播
    public function linkcallpk_singer_nt_singer_pk_info(&$error,$nt_singer_id,$nt_singer_sid,$objsinger,$pk_state)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            //拼装nt包///////////////////////////////////////////////////////////////////////////////////////////
            //1 取出服务器时间
            $time_now = time();
            
            $nt = array();
            //2 取出主播信息
            $this->redis_get_singer_info(&$error,$objsinger,&$nt);           
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误,本次nt无效
                break;
            } 
            $nt['cmd'] = 'linkcallpk_singer_pk_info_nt';
            $nt['time_now'] = $time_now;
            $nt['pk_state'] = (int)$pk_state;
            
            $error['code'] = 0;
            $error['desc'] = '';
            
        }while(0);
        // 如果系统异常，本次不发送nt，只打印错误。
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.linkcallpk_singer_nt_singer_pk_info error:".json_encode($error));
        }
        else 
        {
            //涉及两个房间推送，采用房间之间的回推
            $m = new cback_channel_model();
            $m->unicast($nt_singer_sid, $nt_singer_id, $nt);
            LogApi::logProcess("linkcall_pk_model.linkcallpk_singer_pk_info_nt sid:$nt_singer_sid singer_id:".$nt_singer_id." nt:".json_encode($nt));
        }
    }
    
    //G1 根据主播房间sid号，多播通知nt给房间(包括主客场主播id和sid，送礼的用户id和收礼主播id)
    //备注：如果此时推送pkid是0，则没有pk信息，如果user_id是0，则没有礼物变化信息。
    public function linkcallpk_room_nt_pk_info(&$error,$pkid,$user_id,$singer_id)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        $host_sid = 0;
        $guest_sid = 0;
        do
        {
            //1 取出服务器时间
            $time_now = time();  

                        
            //2 根据pkid 取出pk信息
            $pk_info = array();
            $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;//初始化
            $this->linkcallpk_pk_info_process_by_pkid(&$error,$pkid,&$pk_process,&$pk_info);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            $host_sid = (int)$pk_info["host_sid"];
            $guest_sid = (int)$pk_info["guest_sid"];
            LogApi::logProcess("linkcall_pk_model.linkcallpk_room_nt_pk_info host_sid:$host_sid guest_sid:$guest_sid");

            //3 如果有送礼变化，需要取出变化用户数据            
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
            
            $error['code'] = 0;
            $error['desc'] = '';
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.linkcallpk_room_nt_pk_info error:".json_encode($error));
        }
        //拼装nt包///////////////////////////////////////////////////////////////////////////////////////////
        $nt=array();
        $nt['cmd'] = 'linkcallpk_room_pk_info_nt';
        $nt['time_now'] = $time_now;
        $nt['pkid'] = (int)$pkid;
        $nt['singer_id'] = (int)$singer_id;
        $nt['pk'] = $pk_info;
        $nt['user'] = $user_info;

        //涉及两个房间推送，采用房间之间的广播
        if ($host_sid !=0 || $guest_sid )
        {
            $m = new cback_channel_model();
            $m->broadcast($host_sid, $nt);
            $m->broadcast($guest_sid, $nt);
            LogApi::logProcess("linkcall_pk_model.linkcallpk_room_pk_info_nt host_sid:$host_sid guest_sid:".$guest_sid." nt:".json_encode($nt));
        }      
        
    }  
    
    //G2 根据主播房间sid号，广播nt 给房间
    public function linkcallpk_room_pk_scene_nt(&$error,$pkid,$singer_id,$singer_sid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        $host_sid = 0;
        $guest_sid = 0;
        $pk_scene = linkcall_pk_model::$LINKCALL_PK_SCENE_PK;
        do
        {
            //1 取出服务器时间
            $time_now = time();
            
            //2 取出该主播的场景状态
            $pk_scene = $this->redis_get_singer_scene(&$error,$singer_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }

    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.linkcallpk_room_pk_scene_nt error:".json_encode($error));
        }
        //拼装nt包///////////////////////////////////////////////////////////////////////////////////////////
        $nt=array();
        $nt['cmd'] = 'linkcallpk_room_pk_scene_nt';
        $nt['singer_id'] = (int)$singer_id;
        $nt['singer_sid'] = (int)$singer_sid;
        $nt['time_now'] = $time_now;
        $nt['pk_scene'] = (int)$pk_scene;
        $nt['pkid'] = (int)$pkid;


    
        //涉及两个房间推送，采用房间之间的广播
        if ($singer_sid !=0 )
        {
            $m = new cback_channel_model();
            $m->broadcast($singer_sid, $nt);
            LogApi::logProcess("linkcall_pk_model.linkcallpk_room_pk_info_nt singer_sid:$singer_sid nt:".json_encode($nt));
        }
    
    }
    
    //G.3 根据主播id，拼装主播信息，推送给两个房间
    public function linkcallpk_room_pk_singer_info_nt(&$error,$host_id,$host_sid,$guest_id,$guest_sid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
    
        do
        {
            $h_singer_cache = array ();
            $g_singer_cache = array ();
    
            //1 用$host_id 去获取主播基础信息
            $this->redis_get_singer_info(&$error,$host_id,&$h_singer_cache);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }

            //2 用$guest_id 去获取主播基础信息
            $this->redis_get_singer_info(&$error,$guest_id,&$g_singer_cache);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.linkcallpk_singer_apply_info_by_singerid error:".json_encode($error));
        }
        //拼装nt
        $nt=array();
        $nt['cmd'] = 'linkcallpk_room_pk_singer_info_nt';
        $nt['h_singer'] = $h_singer_cache;
        $nt['g_singer'] = $g_singer_cache;
        
        //涉及两个房间推送，采用房间之间的广播
        if ($host_sid !=0 || $guest_sid )
        {
            $m = new cback_channel_model();
            $m->broadcast($host_sid, $nt);
            $m->broadcast($guest_sid, $nt);
            LogApi::logProcess("linkcall_pk_model.linkcallpk_room_pk_singer_info_nt host_sid:$host_sid guest_sid:".$guest_sid." nt:".json_encode($nt));
        }
    }
    
    //H 根据pkid判断这个pkid的pk是否已经结束
    public function linkcallpk_pk_info_process_by_pkid(&$error,$pkid,&$pk_process,&$pk_info)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;  //默认0代表这个pkid 没有在pk
        $get_pk_info = array();
        $time_now = time();      

        do
        {            
            //1  如果pkid = 0，直接反馈 这个pkid 没有在pk, $pk_info 是空
            if ($pkid == 0)
            {
                $pk_process = $get_pk_process;
                $pk_info = $get_pk_info;
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            
            //2  根据 pkid 取出 保存的$get_pk_info            
            $this->redis_get_pk_info(&$error,$pkid,&$get_pk_info);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            //3  取出 $pk_process
            $this->redis_get_pk_info_process(&$error,$pkid,&$get_pk_process);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            //4  这个pkid 刚刚建立pk界面   或者  这个pkid 超出了pk时间  或者  这个 pkid pk结束，或者这个pkid出现异常，有pkid，没有在pk 
            if ($get_pk_process == 1 || $get_pk_process == 3 || $get_pk_process == 4 || $get_pk_process == 0 )
            {
                $pk_process = $get_pk_process;
                $pk_info = $get_pk_info;
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            
            //5 这个pkid当前在pk中（$get_pk_info缓存是过期的，需要先取出双方主播送礼金额）
            $starttime = $get_pk_info["starttime"];
            $pkalltime = $get_pk_info["pkalltime"];
            $host_id = $get_pk_info["host_id"];
            $guest_id = $get_pk_info["guest_id"];
            if ($get_pk_process ==2)
            {
                //5.1  取出双方主播送礼金额
                $get_host_gift = $this->redis_get_pking_info_singer_gift(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //取出客场主播最新的礼物总金额
                $get_guest_gift = $this->redis_get_pking_info_singer_gift(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //  更新$get_pk_info 刷新返回给客户端的新送礼金额数据
                $get_pk_info["host_gift"] = $get_host_gift;
                $get_pk_info["guest_gift"] = $get_guest_gift;

                //5.2  判断pk是否已经结束
                if ($starttime + $pkalltime > $time_now)
                {
                    //  说明pk还没有结束 还在pk当中
                    $pk_process = $get_pk_process;

                }
                else
                {
                    //1  说明pk结束异常，客户端因为延时或者其他原因未发送结算请求，但是系统需要备注已经结算溢出，后续送礼不计入
                    $pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_BEYOND;  //这个pkid 超出了pk时间
                    //2  系统对pk进行优先结算，登记结算后的pk_info
                    $this->redis_set_pk_info_use_array(&$error,$pkid,&$get_pk_info); 
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    //3  刷新登记pk信息的pk状态过程：这个pkid 超出了pk时间
                    $this->redis_set_pk_info_process(&$error,$pkid,$pk_process);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    
                }
                $pk_info = $get_pk_info;
                //
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            $error['code'] = 0;
            $error['desc'] = '';

        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.linkcallpk_pk_info_process_by_pkid error:".json_encode($error));
        }
    } 
    
    //I 根据singer_id 找到pk对手，如果返回值是0 ，说明没有进行连麦pk
    public function linkcallpk_find_pk_singer_by_singerid($singer_id,&$pk_singer_id,&$pk_singer_sid,&$pkid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        
        $time_now = time();
        do
        {
            //1 根据singer_id  取出 pkid
            $pkid = $this->redis_get_singer_pkid(&$error,$singer_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //2  取出pk_info  
            $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;//初始化
            $pk_info  = array();
            $this->linkcallpk_pk_info_process_by_pkid(&$error,$pkid,&$get_pk_process,&$pk_info);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            $starttime = $pk_info["starttime"];
            $pkalltime = $pk_info["pkalltime"];
            $host_id   = $pk_info["host_id"];
            $guest_id  = $pk_info["guest_id"];
            $host_sid   = $pk_info["host_sid"];
            $guest_sid  = $pk_info["guest_sid"];
            //3  给出正在pk的情况主播id  和  sid
            if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_PKING)
            {
                //说明pk还没有结束
                $pk_singer_id =  ($singer_id == $host_id )? $guest_id : $host_id;
                $pk_singer_sid = ($singer_id == $host_id )? $guest_sid : $host_sid;
            }
    
        }while(0);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("linkcall_pk_model.linkcallpk_find_pk_singer_by_singerid error:".json_encode($error));
        }
    }
    
    
 }
