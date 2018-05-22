<?php

class robot_talk_model extends ModelBase
{    
    
    public function __construct ()
    {
        parent::__construct();
    }
    //机器人聊天控制开关
    public static $ROBOT_TALK_CONTROL = TRUE;
    ////robot_talk_model 配置文件：   房间号取 1024 模；
    //public static $ROOM_MOD_NUMBER = 1024;
    
	//robot_talk_model 配置文件：  select id,parm1,parm2,parm3 from card.parameters_info where id =230 ||id =231 || id =232;
	public static $ROBOT_TALK_INTERVAL_TIME = 230;
    public static $ROBOT_TALK_GIFT_MIN_GOLD = 231;
    public static $ROBOT_TALK_AGAIN_FREQUENCY = 232;
    //robot_talk_model 配置文件：   普通话  1；    新人进场  2；礼物语 3；
    public static $TALK_TOPIC_PUTONG = 1;
    public static $TALK_TOPIC_NEWER = 2;
    public static $TALK_TOPIC_GIFT = 3;
    //robot_talk_model 配置文件：  设置mysql 缓存键（redis_hash redis_set）过期时间
    public static $MYSQL_TO_REDIS_DATA_HASH_TIME = 3600;//mysql字段talk_string保存在缓存   redis_hash 过期时间60分钟，60*60
    public static $MYSQL_TO_REDIS_DATA_SET_TIME = 3600;//mysql字段talk_id     保存在缓存   redis_set  过期时间60分钟，60*60
    
	
	// redis 缓存话语结构：  redis_talk
    public static function robot_talk_mysql_hash_key($talk_topic)
    {
        return "robot:talk:mysql:hash:$talk_topic";
    }
	// redis 缓存talk_topic索引结构： redis_talkindex
	public static function robot_talk_mysql_set_key($talk_topic)
    {
        return "robot:talk:mysql:set:$talk_topic";
    }
	// redis 所有房间，某房间内任意用户最后一次说话时间点结构： redis_time
	public static function robot_talk_room_lasttalk_hash_key()
    {
        return "robot:talk:room:lasttalk:hash";
    }
    // redis 机器人列表结构： redis_robot
    public static function robot_talk_robot_member_zset_key($sid)
    {
        //$mod = $sid % robot_talk_model::$ROOM_MOD_NUMBER;
        return "robot:talk:robot:member:zset:$sid";
    }	
    // redis 新人列表结构： redis_newuser
    public static function robot_talk_newuser_member_zset_key($sid)
    {
        //$mod = $sid % robot_talk_model::$ROOM_MOD_NUMBER;
        return "robot:talk:newuser:member:zset:$sid";
    }
    // redis 礼物语表结构： redis_gift
    public static function robot_talk_gift_zset_key($sid)
    {
        return "robot:talk:gift:zset:$sid";
    }
    // redis 礼物标志位缓存： redis_giftflag
    public static function robot_talk_gift_flag_set_key($uid)
    {
        return "robot:talk:gift:flag:set:$uid";
    }  
    

	// 1.redis 更新 talk和talkindex的数据
	public function redis_updata_for_talk_and_talkindex(&$error,$talk_topic)
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
                $error['desc'] = '网络数据库断开连接：和redis断开';
                break;
            }
			$key_talk_base = robot_talk_model::robot_talk_mysql_hash_key($talk_topic);
			$key_talk_index = robot_talk_model::robot_talk_mysql_set_key($talk_topic);			
			
            $query = "select talk_id,talk_topic,talk_string from cms_manager.t_robot_talking_base where talk_topic = $talk_topic";
            $mysql = $this->getDbMain();
            if(null == $mysql)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接：和mysql断开';
                break;
            }
            $rows = $mysql->query($query);
            if (null == $rows || 0 >= $rows->num_rows)
            {
                // query failure.
                // 100000101(101)执行存储过程失败
                $error['code'] = 100000101;
                $error['desc'] = '执行存储过程失败：mysql读取数据失败';
                break;
            }
            
			for ($x=0; $x<$rows->num_rows; $x++)
			{
				$row = $rows->fetch_assoc();
				$robot_talk_id = (int)$row['talk_id'];
				$robot_talk_topic = (int)$row['talk_topic'];
				$robot_talk_string= (string)$row['talk_string'];
				
				//把  mysql 数据 读取 加载到 redis 缓存  redis_hash 和  redis_set
				$redis->hSet($key_talk_base,$robot_talk_id,$robot_talk_string);			
				$redis->sAdd($key_talk_index,$robot_talk_id);
			}
            //设置缓存 redis_hash 和 redis_set 过期时间
			$redis->expire($key_talk_base,robot_talk_model::$MYSQL_TO_REDIS_DATA_HASH_TIME);
			$redis->expire($key_talk_index,robot_talk_model::$MYSQL_TO_REDIS_DATA_SET_TIME);
			
            $error['code'] = 0;
            $error['desc'] = '';			
        }while(0);         
    }
	//2. redis 清空 talk和talkindex的数据
	public function redis_clear_for_talk_and_talkindex(&$error,$talk_topic)	
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
                $error['desc'] = '网络数据库断开连接：和redis断开';
                break;
            }
			$key_talk_base = robot_talk_model::robot_talk_mysql_hash_key($talk_topic);
			$key_talk_index = robot_talk_model::robot_talk_mysql_set_key($talk_topic);
			
			$redis->del($key_talk_index);
			$redis->del($key_talk_base);
			
            $error['code'] = 0;
            $error['desc'] = '';			
		}while(0);		
    }	
	
	//3. 控制台  从redis 取出 说话内容 talking
	public function control_read_talking_from_redis(&$error,$talk_topic,&$robot_talking)
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
                $error['desc'] = '网络数据库断开连接：和redis断开';
                break;
            }
			$key_talk_base = robot_talk_model::robot_talk_mysql_hash_key($talk_topic);
			$key_talk_index = robot_talk_model::robot_talk_mysql_set_key($talk_topic);

			// 如果查询talk_topic索引 或者 缓存话语结构 没有缓存，则先清空对应talk_topic的redis，重新回mysql加载
			if(0==$redis->sCard($key_talk_index) or  0==$redis->hLen($key_talk_base))
			{
				// 先清空对应talk_topic的redis  talk_topic索引 和 talk_topic话语
				$this->redis_clear_for_talk_and_talkindex(&$error,$talk_topic);
                if(0 != $error['code'])
                {
                    //出现一些逻辑错误，退出
                    break;
                }					
                // 回mysql 重新加载 对应 talk_topic索引的 索引和话语
				$this->redis_updata_for_talk_and_talkindex(&$error,$talk_topic);
				if(0 != $error['code'])
				{
				    //出现一些逻辑错误，退出
				    break;
				}			
			}	
		
			// redis 取出talk_topic索引 的 随机元素 talk_id。
			$talk_index_random=$redis->sRandMember($key_talk_index);
			// redis 根据索引提供的随机元素 talk_id，取出 缓存话语 对应的 string。
			$robot_talking=$redis->hGet($key_talk_base,$talk_index_random);	
			
			LogApi::logProcess("robot_talk_model.control_read_talking_from_redis  robot_talking:$robot_talking");			
			
			if(empty($robot_talking))
			{
				$error['code'] = 200000003;
				$error['desc'] = '数据更新失败：redis 缓存无 robot_talking, redis_clear_for_talk_and_talkindex 失效';	
				$robot_talking = '';
				break;
			}	
            $error['code'] = 0;
            $error['desc'] = '';			
	
	    }while(0);   
    }
    
    //4. redis 更新 robot 的数据
    public function redis_updata_for_robot(&$error,$sid,$info_user_id)
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
                $error['desc'] = '网络数据库断开连接：和redis断开';
                break;
            }
            $key_talk_robot = robot_talk_model::robot_talk_robot_member_zset_key($sid);            
            $timecode_now=time();
            $redis->zAdd($key_talk_robot,$timecode_now,$info_user_id);
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);       
    }
    //5. redis 更新 newuser 的数据
    public function redis_updata_for_newuser(&$error,$sid,$info_user_id)
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
                $error['desc'] = '网络数据库断开连接：和redis断开';
                break;
            }
            $key_talk_newuser = robot_talk_model::robot_talk_newuser_member_zset_key($sid);
            $timecode_now=time();
            $redis->zAdd($key_talk_newuser,$timecode_now,$info_user_id);
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    } 
    
    //4. redis 更新 gift 的数据
    public function redis_updata_for_gift(&$error,$sid)
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
                $error['desc'] = '网络数据库断开连接：和redis断开';
                break;
            }
            $key_talk_gift = robot_talk_model::robot_talk_gift_zset_key($sid);
            $timecode_now=time();
            $redis->zAdd($key_talk_gift,$timecode_now,$timecode_now);
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //6.控制台  从redis 取出 robot
    public function control_read_robot_from_redis(&$error,$sid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $uid_robot=0;
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接：和redis断开';
                break;
            }
            $key_talk_robot = robot_talk_model::robot_talk_robot_member_zset_key($sid);
            //zRange 取出的是一个列表，虽然只有一个，需要判断是否有数据
            $uid_array=$redis->zRange($key_talk_robot,0,0);
            if(empty($uid_array))
            {
                // 200000002(002)获取数据失败
                $error['code'] = 200000002;
                $error['desc'] = '获取数据失败:redis 缓存无机器人，请开启机器人服务';
                break;
            }
            //获得zRange 列表中第一个元素
            $uid_robot=(int)current($uid_array);
            if(empty($uid_robot))
            {
                // 200000002(002)获取数据失败
                $error['code'] = 200000002;
                $error['desc'] = '获取数据失败：读取机器人列表异常，请重试（列表有数据，读出失败）';
                break;
            }
            $error['code'] = 0;
            $error['desc'] = '';           
        }while(0);
        return $uid_robot;
    }  
    
    //7.控制台  从redis 取出 newuser
    public function control_read_newuser_from_redis(&$error,$sid)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $uid_newuser=0;
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接：和redis断开';
                break;
            }
            $key_talk_newuser = robot_talk_model::robot_talk_newuser_member_zset_key($sid);
            //zRange 取出的是一个列表，虽然只有一个，需要判断是否有数据
            $uid_array=$redis->zRange($key_talk_newuser,0,0);
            if(empty($uid_array))
            {
                // 200000002(002)获取数据失败
                $error['code'] = 200000002;
                $error['desc'] = '获取数据失败：redis 缓存无新人列表，请判断是否没有新人进入，或者on_lookup_newuser_in_redis 误判';
                break;
            }
            //获得zRange 列表中第一个元素
            $uid_newuser=(int)current($uid_array);
            if(empty($uid_newuser))
            {
                // 200000002(002)获取数据失败
                $error['code'] = 200000002;
                $error['desc'] = '获取数据失败：读取新人列表异常，请重试（列表有数据，读出失败）';
                break;
            }
            
            $error['code'] = 0;
            $error['desc'] = '';           
            $redis->zRem($key_talk_newuser,$uid_newuser);
        }while(0);
        return $uid_newuser;
    }    
    
    //8.新人说话发送过程
    public function control_send_newuser_talking(&$return,$sid,$info_robot,$info_singer,$info_user,$robot_talking)
    {
        $params=array();
        $params['cid']=1;
        $params['receiver']=$info_singer['nick'];
        $params['roler']=25;
        $params['sender']=$info_robot['nick'];
        $params['sid']=$sid;
        $params['uid']=$info_robot['id'];
        $params['uid_onmic']=$info_singer['id'];           
        $params['usercount']=0;             
        $params['cmd']='PAtMessage';                
        $params['context']=$robot_talking;            
        $params['fromNickname']=$info_robot['nick'];            
        $params['fromUid']=$info_robot['id'];            
        $params['singerid']=$info_singer['id'];            
        $params['toNickname']=$info_user['nick'];            
        $params['toUid']=$info_user['id']; 
        
        $return_result = ToolApi::atMessage($params);
        $return=array_merge($return_result,$return);
    }  
    
    //9.普通说话发送过程
    public function control_send_usuall_talking(&$return,$sid,$info_robot,$info_singer,$robot_talking)
    {
        $params=array();
        $params['cid']=1;        
        $params['receiver']=$info_singer['nick'];
        $params['roler']=25;
        $params['sender']=$info_robot['nick'];
        $params['sid']=$sid;
        $params['uid']=$info_robot['id'];
        $params['uid_onmic']=$info_singer['id'];
        $params['usercount']=0;
        $params['cmd']='PTextChat';
        $params['context']=$robot_talking; 
        $params['singerid']=$info_singer['id'];

        $return_result = ToolApi::textChat($params);
        $return=array_merge($return_result,$return);
    }       
    
	//10.控制台合成新人说话内容提交到【新人说话发送】
	public function control_make_newuser_talking_next_to_send(&$error,&$return,$sid,$info_singer)
    {    
        $error['code'] = -1;
        $error['desc'] = '未知错误';
	    do 	
        {	            
                $talk_topic=robot_talk_model::$TALK_TOPIC_NEWER;
    			$robot_talking='';
    			
    			//1.控制台  从redis 取出 说话内容talking
    			$this->control_read_talking_from_redis(&$error,$talk_topic,&$robot_talking);
    			if(0 != $error['code'])
    			{
    			    //出现一些逻辑错误，退出
    			    break;
    			}
    			
    			//2.1 控制台  从redis 取出 robot
    			$robot_id=$this->control_read_robot_from_redis(&$error,$sid);
    			if(0 != $error['code'])
    			{
    			    //出现一些逻辑错误，退出
    			    break;
    			}  	
    			//2.2根据机器人用户id，获取机器人用户属性
    			$userinfo_modle = new UserInfoModel();
    			$info_robot = $userinfo_modle->getInfoById($robot_id);    			
    			$robot_nick=$info_robot['nick'];
    			
    			//3.1 控制台  从redis 取出 newuser
    			$newuser_id=$this->control_read_newuser_from_redis(&$error,$sid);
    			if(0 != $error['code'])
    			{
    			    //出现一些逻辑错误，退出
    			    break;
    			}
    			//3.2根据新人用户id，获取机器人用户属性
    			$info_newuser = $userinfo_modle->getInfoById($newuser_id);
    			$newuser_nick=$info_newuser['nick']; 
    			
    			//4. 打出说话内容 是否正确
    			LogApi::logProcess("robot_talk_model.control_make_newuser_talking_next_to_send sid:$sid robot_id:$robot_id robot_nick:$robot_nick newuser_id:$newuser_id newuser_nick:$newuser_nick robot_talking:$robot_talking");
    			
    			//5. 新人说话发送过程
    			$this->control_send_newuser_talking(&$return,$sid,$info_robot,$info_singer,$info_newuser,$robot_talking); 

    			
		
			$error['code'] = 0;
            $error['desc'] = '';			
        }while(0);      
    }	    
    
    //11.用户（新人 和 机器人 ）入场登记
    public function on_redis_record_user($sid,$info_user,$user_attr)
    {
        if(robot_talk_model::$ROBOT_TALK_CONTROL)
        {
            $error = array();
            $error['code'] = -1;
            $error['desc'] = '未知错误';
            do
            {
                $info_user_id=$info_user['id'];
                //判断是否是机器人，如果是则加入redis缓存,并 跳出该环节
                if(0!=$info_user['is_robot'])
                {
                    //redis 更新 robot 的数据
                    $this->redis_updata_for_robot(&$error,$sid,$info_user_id);
                    if(0 != $error['code'])
                    {
                        //出现一些逻辑错误，退出
                        break;
                    }
            
                    $error['code'] = 0;
                    $error['desc'] = '';
                    break;
                }
                //判断用户是否是新人，如果是则加入redis缓存,并 跳出该环节
                if(0!=$user_attr['new'])
                {
                    //redis 更新 newuser 的数据
                    $this->redis_updata_for_newuser(&$error,$sid,$info_user_id);
                    if(0 != $error['code'])
                    {
                        //出现一些逻辑错误，退出
                        break;
                    }
                    $error['code'] = 0;
                    $error['desc'] = '';
                    break;
                }
            
                $error['code'] = 0;
                $error['desc'] = '';
            }while(0);
            if (0 != $error['code'])
            {
                $code = $error['code'];
                $desc = $error['desc'];
                LogApi::logProcess("robot_talk_model.on_user_comein_room error($code):$desc");
            }
        }
 
    }
    
	//12. 用户（新人 和 机器人 ）出场移除
    public function on_redis_delete_user($sid,$info_user)
    {
        if(robot_talk_model::$ROBOT_TALK_CONTROL)
        {
            $error = array();
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
                $key_talk_robot = robot_talk_model::robot_talk_robot_member_zset_key($sid);
                $key_talk_newuser = robot_talk_model::robot_talk_newuser_member_zset_key($sid);
                //事实上用户即使不是在新人缓存还是机器人缓存，对移除无影响。主要因为用户有两个不同的属性表，判断需求信息量太多，因此直接移除。
                $redis->zRem($key_talk_robot,$info_user);//移除机器人uid在房间sid的情况情况，防止出去的机器人还能说话
                $redis->zRem($key_talk_newuser,$info_user);//移除新人uid在房间sid的情况情况，避免新人进入闪出房间，机器人会@该人。
                $error['code'] = 0;
                $error['desc'] = '';
            }while(0);
            if (0 != $error['code'])
            {
                $code = $error['code'];
                $desc = $error['desc'];
                LogApi::logProcess("robot_talk_model.on_user_comeout_room error($code):$desc");
            }
        }
        
    }
    //礼物的接口函数，目前不改变外部接口
    public function on_user_send_gift_room(&$return,$sid,$info_singer,$info_user,$tool_inf,$params)
    {
        if(robot_talk_model::$ROBOT_TALK_CONTROL)
        {
            $error = array();
            $error['code'] = -1;
            $error['desc'] = '未知错误';
            do
            {
                //1. 判断redis缓存gift 数据是否有超过3s的房间号（sid）并执行一次说话
                $this->on_lookup_gift_in_redis(&$return,$sid,$info_singer);
            
            
                //2.  redis 判断记录 gift的数据
                $this->redis_record_gift(&$error,$sid,$info_user,$tool_inf,$params);
                if(0 != $error['code'])
                {
                    //出现一些逻辑错误，退出
                    break;
                }
            
            }while(0);
            if (0 != $error['code'])
            {
                $code = $error['code'];
                $desc = $error['desc'];
                LogApi::logProcess("robot_talk_model.on_user_send_gift_room error($code):$desc");
            }
        }
	
    }
    
    //13. redis 判断记录 gift的数据

    public function redis_record_gift(&$error,$sid,$info_user,$tool_inf,$params)
    {

        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {
            $info_user_id=$info_user['id'];//取出用户id
            $flag=0;//初始化标志位，用于和静态变量比较
            $talk_empty=0;//初始化空值，用于和静态变量比较
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接：和redis断开';
                break;
            }
            //取出礼物标志位，如果判空，说明不是连续送礼
            $key_talk_gift_flag = robot_talk_model::robot_talk_gift_flag_set_key($info_user_id);
            $talk_gift_flag=$redis->get($key_talk_gift_flag);
            if(empty($talk_gift_flag))
            {
                $talk_empty=1;
            }
    
            $gift_even_send_unmber=0;//初始化连送礼物数量
            $gift_num=$params['num'];//组送礼物数量
            $gift_price=$tool_inf['price'];//礼物价格
            $gift_total_gold = $gift_price * $gift_num;//礼物总价
    
            $gift_even_send_unmber=$params['serialNum']; //连送次数
            $gift_even_send_gold=$gift_total_gold * $gift_even_send_unmber;//连送总价
            $redis->expire($key_talk_gift_flag,3);//重置连送标志位
    
            $sys_parameters = new SysParametersModel();
            $base_gift_gold = $sys_parameters->GetSysParameters(robot_talk_model::$ROBOT_TALK_GIFT_MIN_GOLD, 'parm1');
            $talk_again = $sys_parameters->GetSysParameters(robot_talk_model::$ROBOT_TALK_AGAIN_FREQUENCY, 'parm1');
            //设定随机概率值
            $suiji=rand(1,$talk_again);
            if(2==$suiji)
            {
                $flag=1;
            }
            LogApi::logProcess("robot_talk_model.redis_record_gift on talk_empty:$talk_empty gift_even_send_gold:$gift_even_send_gold send_unmber:$gift_even_send_unmber flag:$flag");
            // 1.单次送礼 >= 设置金币  必定说话     2.连送 >= 设置金币，第一次说话必出现   3.连送 >= 设置金币，后续说话随机出现
            if(($gift_even_send_gold>=$base_gift_gold && 1==$gift_even_send_unmber) || ($talk_empty==1 && $gift_even_send_gold>=$base_gift_gold) || (1==$flag && $gift_even_send_gold>=$base_gift_gold))
            {
                
                LogApi::logProcess("robot_talk_model. gift updata");
                //1. redis 更新  gift 的数据
                $this->redis_updata_for_gift(&$error,$sid);	

                
                //重置礼物标志位，用于连送时判断是否处于连送状态
                $redis->set($key_talk_gift_flag,'giftflag');
                $redis->expire($key_talk_gift_flag,3);
            }
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }    
    
	//14.判断redis缓存gift房间号（sid） 数据是否有超过3s的情况，并执行一次说话
	public function on_lookup_gift_in_redis(&$return,$sid,$info_singer)
    {
        if(robot_talk_model::$ROBOT_TALK_CONTROL)
        {
            $error = array();
            $error['code'] = -1;
            $error['desc'] = '未知错误';
            do
            {
                $redis = $this->getRedisMaster();
                if(null == $redis)
                {
                    // 100000701(701)网络数据库断开连接
                    $error['code'] = 100000701;
                    $error['desc'] = '网络数据库断开连接：和redis断开';
                    break;
                }
                $key_talk_gift = robot_talk_model::robot_talk_gift_zset_key($sid);
                //判断缓存gift列表当中是否有超过3秒的数据,如果没有，退出当前步骤
                $time_3s_before=time()-3;
                $sid_array_3s_array=$redis->zRangeByScore($key_talk_gift,0,$time_3s_before);
                if(empty($sid_array_3s_array))
                {
                    // 缓存当前没有满足的条件礼物语情况
                    $error['code'] = 0;
                    $error['desc'] = '';
                    break;
                }
                else
                {
                    //如果有数据（该房间 有人送礼物达到要求，缓存了3s时间以上），取出第一个数据，用于说礼物语
                    $get_from_zset_array=$redis->zRange($key_talk_gift,0,0);
                    if(empty($get_from_zset_array))
                    {
                        // 200000002(002)获取数据失败
                        $error['code'] = 200000002;
                        $error['desc'] = '获取数据失败：redis缓存中无礼物列表信息';
                        break;
                    }
                    //获得zRange 列表中第一个元素
                    $get_from_zset=(int)current($get_from_zset_array);
                    if(empty($get_from_zset))
                    {
                        // 200000002(002)获取数据失败
                        $error['code'] = 200000002;
                        $error['desc'] = '获取数据失败，请重试（列表有数据，读出失败）';
                        break;
                    }
                    $this->control_make_liwu_talking_next_to_send(&$error,&$return,$sid,$info_singer);
                    if(0 != $error['code'])
                    {
                        //出现一些逻辑错误，退出
                        break;
                    }
                    //在redis缓存gift，移除第一个元素$get_from_zset
                    $redis->zRem($key_talk_gift,$get_from_zset);
                }
                $error['code'] = 0;
                $error['desc'] = '';
            }while(0);
            if (0 != $error['code'])
            {
                $code = $error['code'];
                $desc = $error['desc'];
                LogApi::logProcess("robot_talk_model.on_lookup_gift_in_redis error($code):$desc");
            }
        }

    }

            
    //15.控制台合成礼物语说话内容提交到[普通说话发送]
    public function control_make_liwu_talking_next_to_send(&$error,&$return,$sid,$info_singer)
    {            
        $error['code'] = -1;
        $error['desc'] = '未知错误';
        do
        {            
            $talk_topic=robot_talk_model::$TALK_TOPIC_GIFT;
            $robot_talking='';
            //控制台  从redis 取出 robot
            $robot_id=$this->control_read_robot_from_redis(&$error,$sid);
            if(0 != $error['code'])
            {
                //出现一些逻辑错误，退出
                break;
            }
            //控制台  从redis 取出 说话内容talking
            $this->control_read_talking_from_redis(&$error,$talk_topic,&$robot_talking);
            if(0 != $error['code'])
            {
                //出现一些逻辑错误，退出
                break;
            }
            //根据机器人用户id，获取机器人用户属性
            $userinfo_modle = new UserInfoModel();
            $info_robot = $userinfo_modle->getInfoById($robot_id);
             
            $robot_nick=$info_robot['nick'];
            //打出说话内容 是否正确
            LogApi::logProcess("robot_talk_model.control_make_liwu_talking_next_to_send sid:$sid robot_id:$robot_id robot_nick:$robot_nick  robot_talking:$robot_talking");
            //普通说话的具体实现过程
            $this->control_send_usuall_talking(&$return,$sid,$info_robot,$info_singer,$robot_talking);           
   
			$error['code'] = 0;
            $error['desc'] = '';			
        }while(0);	
    }		
	
	// 16.判断redis缓存time 数据是否有超过60s的房间号（sid）,如果有就提交到[普通说话发送]
	public function on_lookup_time_in_redis(&$return,$sid,$info_singer)
    {
        if(robot_talk_model::$ROBOT_TALK_CONTROL)
        {
            $error = array();
            $error['code'] = -1;
            $error['desc'] = '未知错误';
            do
            {
                $redis = $this->getRedisMaster();
                if(null == $redis)
                {
                    // 100000701(701)网络数据库断开连接
                    $error['code'] = 100000701;
                    $error['desc'] = '网络数据库断开连接：和redis断开	';
                    break;
                }
                $key_talk_time = robot_talk_model::robot_talk_room_lasttalk_hash_key();
                // 取出本房间号最后 真人 说话时间
                $room_time_now = $redis->hGet($key_talk_time,$sid);
            
                	
                if (true == empty($room_time_now))
                {
                    // redis缓存无数据，获取数据失败
                    $error['code'] = 200000002;
                    $error['desc'] = '获取数据失败：redis缓存列表， 60s无人说话的时刻为空，请查看redis_updata_for_time是否异常';
                    break;
                }
                //取出配置文件，设定无人说话为  1 分钟,取出配置时间 $base_set_intervla_time
                $sys_parameters = new SysParametersModel();
                $base_set_intervla_time = $sys_parameters->GetSysParameters(robot_talk_model::$ROBOT_TALK_INTERVAL_TIME, 'parm1');
                //1分钟   转换为时间戳为  60 s
                $base_set_intervla_time_use_second=$base_set_intervla_time * 60;
            
                //时间差（当前时间-取出时间）
                $time_now=time();
            
                $interval_time=$time_now-$room_time_now;
            
                //判断时间差      是否      大于    设置时间（1分钟=60s）
                if($interval_time-$base_set_intervla_time_use_second>=0)
                {
                    $talk_topic=robot_talk_model::$TALK_TOPIC_PUTONG;
                    $robot_talking='';
                    //1. 控制台  从redis 取出 robot
                    $robot_id=$this->control_read_robot_from_redis(&$error,$sid);
                    if(0 != $error['code'])
                    {
                        //出现一些逻辑错误，退出
                        break;
                    }
                    //2. 控制台  从redis 取出 说话内容talking
                    $this->control_read_talking_from_redis(&$error,$talk_topic,&$robot_talking);
                    if(0 != $error['code'])
                    {
                        //出现一些逻辑错误，退出
                        break;
                    }
                    //3. 根据机器人用户id，获取机器人用户属性
                    $userinfo_modle = new UserInfoModel();
                    $info_robot = $userinfo_modle->getInfoById($robot_id);
            
                    //4. 打出说话内容 是否正确
                    $robot_nick=$info_robot['nick'];
                    LogApi::logProcess("robot_talk_model.on_lookup_time_in_redis sid:$sid robot_id:$robot_id robot_nick:$robot_nick robot_talking:$robot_talking");
            
                    //5. 普通说话的具体实现过程
                    $this->control_send_usuall_talking(&$return,$sid,$info_robot,$info_singer,$robot_talking);
            
                    //重置说话时间，为了防止执行时间误差，重设 $time_now
                    //$time_now=time();
            
                    $redis->hSet($key_talk_time,$sid,$time_now);
            
                }
            
                $error['code'] = 0;
                $error['desc'] = '';
            }while(0);
            if (0 != $error['code'])
            {
                $code = $error['code'];
                $desc = $error['desc'];
                LogApi::logProcess("robot_talk_model.on_lookup_time_in_redis error($code):$desc");
            }
        }

    }	
    //外部接口函数，监听是否有真人在说话
    public function on_robot_listen_set_redis($sid,$user_info)
    {
        if(robot_talk_model::$ROBOT_TALK_CONTROL)
        {
            $error=array();
            $error['code'] = -1;
            $error['desc'] = '未知错误';
            do
            {
            
                //1. redis 更新 time 的数据
            
                $this->redis_updata_for_time(&$error,$sid,$user_info);
            
            
                if(0 != $error['code'])
                {
                    //出现一些逻辑错误，退出
                    break;
                }
            
                $error['code'] = 0;
                $error['desc'] = '';
            
            }while(0);
            if (0 != $error['code'])
            {
                $code = $error['code'];
                $desc = $error['desc'];
                LogApi::logProcess("robot_talk_model.on_robot_listen_set_redis error($code):$desc");
            }
        }

    }
    
	//17. redis 更新 time 的数据
	public function redis_updata_for_time(&$error,$sid,$user_info)
    {
        $error['code'] = -1;
        $error['desc'] = '未知错误';
   
	    do 
        {   

            //判断是否是机器人，跳出该环节
            if(0!=$user_info['is_robot'])
            {
                $error['code'] = 0;
                $error['desc'] = '';

                break;
            }  

            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)网络数据库断开连接
                $error['code'] = 100000701;
                $error['desc'] = '网络数据库断开连接：和redis断开	';
                break;
            }

            $key_talk_time = robot_talk_model::robot_talk_room_lasttalk_hash_key();
			$listen_time=time();

			$redis->hSet($key_talk_time,$sid,$listen_time);

			$error['code'] = 0;
            $error['desc'] = '';

        }while(0);
    }
    
    //18. 主播入场流程(主播信息,房间号)
    public function on_singer_comein_room($sid,$uid)
    {
        if(robot_talk_model::$ROBOT_TALK_CONTROL)
        {
            $error = array();
            $error['code'] = -1;
            $error['desc'] = '未知错误';
            do
            {
                $redis = $this->getRedisMaster();
                if(null == $redis)
                {
                    // 100000701(701)网络数据库断开连接
                    $error['code'] = 100000701;
                    $error['desc'] = '网络数据库断开连接：和redis断开	';
                    break;
                }
                //主播入场，设置无人说话时间戳
                $key_talk_time = robot_talk_model::robot_talk_room_lasttalk_hash_key();
                $listen_time=time();
                $redis->hSet($key_talk_time,$sid,$listen_time);
                $error['code'] = 0;
                $error['desc'] = '';
            }while(0);
            if (0 != $error['code'])
            {
                $code = $error['code'];
                $desc = $error['desc'];
                LogApi::logProcess("robot_talk_model.on_singer_comein_room error($code):$desc");
            }
        }

    }  
    //19.主播离场流程(主播信息,房间号)
    public function on_singer_leave_room($sid,$uid)
    {
        if(robot_talk_model::$ROBOT_TALK_CONTROL)
        {
            $error = array();
            $error['code'] = -1;
            $error['desc'] = '未知错误';
            do
            {
                $redis = $this->getRedisMaster();
                if(null == $redis)
                {
                    // 100000701(701)网络数据库断开连接
                    $error['code'] = 100000701;
                    $error['desc'] = '网络数据库断开连接：和redis断开	';
                    break;
                }
                $key_talk_robot = robot_talk_model::robot_talk_robot_member_zset_key($sid);
                $key_talk_newuser = robot_talk_model::robot_talk_newuser_member_zset_key($sid);
                $key_talk_gift = robot_talk_model::robot_talk_gift_zset_key($sid);
                $key_talk_time = robot_talk_model::robot_talk_room_lasttalk_hash_key();
                $redis->del($key_talk_robot);//删掉该房间sid 的机器人缓存
                $redis->del($key_talk_newuser);//删掉该房间sid 的新人缓存
                $redis->del($key_talk_gift);//删掉该房间礼物语缓存
                $redis->hDel($key_talk_time,$sid);//移除真人 最后说话 缓存  的  房间 sid
            
                $error['code'] = 0;
                $error['desc'] = '';
            }while(0);
            if (0 != $error['code'])
            {
                $code = $error['code'];
                $desc = $error['desc'];
                LogApi::logProcess("robot_talk_model.on_singer_leave_room error($code):$desc");
            }
        }

    }    
 

    
    //20.判断redis缓存newuser 数据是否有超过3s的房间号（sid）并执行一次说话
    public function on_lookup_newuser_in_redis(&$return,$sid,$info_singer)
    {
        if(robot_talk_model::$ROBOT_TALK_CONTROL)
        {
            $error = array();
            $error['code'] = -1;
            $error['desc'] = '未知错误';
            do
            {
                $redis = $this->getRedisMaster();
                if(null == $redis)
                {
                    // 100000701(701)网络数据库断开连接
                    $error['code'] = 100000701;
                    $error['desc'] = '网络数据库断开连接：和redis断开	';
                    break;
                }
                $key_talk_newuser = robot_talk_model::robot_talk_newuser_member_zset_key($sid);
                //判断缓newuser 数据 当中是否有超过3秒的数据
                $time_3s_before=time()-3;
                $uid_robot_array=$redis->zRangeByScore($key_talk_newuser,0,$time_3s_before);
                if(empty($uid_robot_array))
                {
                    // 缓存当前没有满足的新人
                    $error['code'] = 0;
                    $error['desc'] = '';
                    break;
                }
                else
                {
                    //控制台合成新人说话内容提交到[新人说话发送]
                    $this->control_make_newuser_talking_next_to_send(&$error,&$return,$sid,$info_singer);
                    if(0 != $error['code'])
                    {
                        //出现一些逻辑错误，退出
                        break;
                    }
                }
            
                $error['code'] = 0;
                $error['desc'] = '';
            }while(0);
            if (0 != $error['code'])
            {
                $code = $error['code'];
                $desc = $error['desc'];
                LogApi::logProcess("robot_talk_model.on_lookup_newuser_in_redis error($code):$desc");
            }
        }
 
    } 
    
}
?>