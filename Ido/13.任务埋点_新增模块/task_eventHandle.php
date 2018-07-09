<?php


$path=dirname(__FILE__);
include_once "$path/../rabbitMqInterface.php";
$path=dirname(__FILE__);
include_once "$path/base_info_query.php";
include_once "$path/LogApi.php";

class CEventHandleTask
{
    var $redis;
    var $db;
	//阳光跑环任务事件     
	public function taskModule_loop_event($event_type,$uid, $task_id, $l_cur_num, $h_cur_num,&$awardItems)
	{
	    $userinfo = array();
	    $baseinfo = new base_info_query($this->redis, $this->db);
	    $baseinfo->base_uid2userInfo($uid,&$userinfo);
	    $nick = $userinfo["nick"];	    

	    $taskInfo = array();
	    $baseinfo->base_taskId2taskType($task_id, &$taskInfo);   
	    
        $eventInfo = array();
        $eventInfo["eventName"] = "taskModule_loop_event";
        $eventInfo["eventTime"] = time();//开启事件时间
        $eventInfo["log_type"] = (int)$event_type;//日志类型
        $eventInfo["task_id"] = (int)$task_id;//具体任务实例id
        $eventInfo["t_id"] = (int)$taskInfo["t_id"];//    任务id
        $eventInfo["t_type"] = (int)$taskInfo["task_type"];//任务类型
        $eventInfo["target_type"] = (int)$taskInfo["target_type"];//目标任务类型
        $eventInfo["user_id"] = (int)$uid;//用户id
        $eventInfo["user_nick"] = (string)$nick;//用户昵称
        $eventInfo["l_cur_num"] = (int)$l_cur_num;//阳光跑环开启当前轮数
        $eventInfo["h_cur_num"] = (int)$h_cur_num;//阳光跑环开启当前环数 
        $eventInfo["awardItems"] = $awardItems;//资产变更（是个数组）
        
        LogApi::logProcess("INFO::taskinfo::CEventHandleTask.taskModule_loop_event: task_id:$task_id eventInfo:".json_encode($eventInfo));
        
        if(!RabbitMqInterface::rabbitmq_publish('event_taskModule_exchange', "event_taskModule_routingKey", json_encode($eventInfo)))
        {
            LogApi::logProcess("WARN::taskinfo::CEventHandleTask.taskModule_loop_event:rabbitmq_publish Failed! ".json_encode($eventInfo));
        }
	}


	//帮会个人任务完成事件
	public function taskModule_gangManComplete_event($task_id,$uid)
	{
	    $userinfo = array();
	    $baseinfo = new base_info_query($this->redis, $this->db);
	    $baseinfo->base_uid2userInfo($uid,&$userinfo);
	    $nick = $userinfo["nick"];
	    $union_id = $userinfo["union_id"];
	     
	    $taskInfo = array();
	    $baseinfo->base_taskId2taskType($task_id, &$taskInfo);     

	    $union_info = array();
	    $baseinfo->base_unionId2unionInfo($union_id, &$union_info);
	    $union_name = $union_info["union_name"];
	    $union_level = $union_info["union_up_level"];
	    
	    $eventInfo = array();
	    $eventInfo["eventName"] = "taskModule_gangMan_event";
	    $eventInfo["eventTime"] = time();//开启事件时间
        $eventInfo["task_id"] = (int)$task_id;//具体任务实例id
        $eventInfo["log_type"] = (int)3;//日志类型,完成是3
        $eventInfo["t_id"] = (int)$taskInfo["t_id"];//    任务id
        $eventInfo["t_type"] = (int)$taskInfo["task_type"];//任务类型
        $eventInfo["target_type"] = (int)$taskInfo["target_type"];//目标任务类型      
	    $eventInfo["union_id"] = (int)$union_id;//帮会id
	    $eventInfo["union_nick"] = (string)$union_name;//帮会昵称
	    $eventInfo["union_level"] = (int)$union_level;//帮会等级
        $eventInfo["user_id"] = (int)$uid;//用户id
        $eventInfo["user_nick"] = (string)$nick;//用户昵称
        
        LogApi::logProcess("INFO::taskinfo::CEventHandleTask.taskModule_gangManComplete_event: task_id:$task_id eventInfo:".json_encode($eventInfo));
	
	    if(!RabbitMqInterface::rabbitmq_publish('event_taskModule_exchange', "event_taskModule_routingKey", json_encode($eventInfo)))
	    {
	        LogApi::logProcess("WARN::taskinfo::CEventHandleTask.taskModule_gangManComplete_event:rabbitmq_publish Failed! ".json_encode($eventInfo));
	    }
	}
	
	//帮会集体任务完成事件
	public function taskModule_gangCommonComplete_event($task_id,$union_id)
	{
	    $baseinfo = new base_info_query($this->redis, $this->db);
	    
	    $taskInfo = array();
	    $baseinfo->base_taskId2taskType($task_id, &$taskInfo); 
	
	    $union_info = array();
	    $baseinfo->base_unionId2unionInfo($union_id, &$union_info);
	    $union_name = $union_info["union_name"];
	    $union_level = $union_info["union_up_level"];
	    
	    $union_key_num = 0;
	    $baseinfo->base_unionId2unionKeyNum($union_id,&$union_key_num);
	     
	    $eventInfo = array();
	    $eventInfo["eventName"] = "taskModule_gangCommon_event";
	    $eventInfo["eventTime"] = time();//开启事件时间
	    $eventInfo["task_id"] = (int)$task_id;//具体任务实例id
	    $eventInfo["log_type"] = (int)3;//日志类型,完成是3	    
        $eventInfo["t_id"] = (int)$taskInfo["t_id"];//    任务id
        $eventInfo["t_type"] = (int)$taskInfo["task_type"];//任务类型
        $eventInfo["target_type"] = (int)$taskInfo["target_type"];//目标任务类型
	    $eventInfo["union_id"] = (int)$union_id;//帮会id
	    $eventInfo["union_nick"] = (string)$union_name;//帮会昵称
	    $eventInfo["union_level"] = (int)$union_level;//帮会等级
	    $eventInfo["union_key_num"] = (int)$union_key_num;//本次事件后总钥匙数量
	    
	    LogApi::logProcess("INFO::taskinfo::CEventHandleTask.taskModule_gangCommonComplete_event: task_id:$task_id eventInfo:".json_encode($eventInfo));
	
	    if(!RabbitMqInterface::rabbitmq_publish('event_taskModule_exchange', "event_taskModule_routingKey", json_encode($eventInfo)))
	    {
	        LogApi::logProcess("WARN::taskinfo::CEventHandleTask.taskModule_gangCommonComplete_event :rabbitmq_publish Failed! ".json_encode($eventInfo));
	    }
	}

	//帮会星级任务完成事件
	public function taskModule_gangStarComplete_event($task_id,$union_id)
	{
	    $baseinfo = new base_info_query($this->redis, $this->db);
	     
	    $taskInfo = array();
	    $baseinfo->base_taskId2taskType($task_id, &$taskInfo); 
	
	    $union_info = array();
	    $baseinfo->base_unionId2unionInfo_real_time($union_id, &$union_info);
	    $union_name = $union_info["union_name"];
	    $union_level = $union_info["union_up_level"];
	    $union_star_num = $union_info["union_current_star"];
	    $union_star_level = $union_info["union_level_id"];
	
	    $eventInfo = array();
	    $eventInfo["eventName"] = "taskModule_gangStar_event";
	    $eventInfo["eventTime"] = time();//开启事件时间
	    $eventInfo["task_id"] = (int)$task_id;//具体任务实例id
	    $eventInfo["log_type"] = (int)3;//日志类型,完成是3	    
        $eventInfo["t_id"] = (int)$taskInfo["t_id"];//    任务id
        $eventInfo["t_type"] = (int)$taskInfo["task_type"];//任务类型
        $eventInfo["target_type"] = (int)$taskInfo["target_type"];//目标任务类型
	    $eventInfo["union_id"] = (int)$union_id;//帮会id
	    $eventInfo["union_nick"] = (string)$union_name;//帮会昵称
	    $eventInfo["union_level"] = (int)$union_level;//帮会等级
	    $eventInfo["union_star_num"] = (int)$union_star_num;//获得本颗星后帮会的星星数量
	    $eventInfo["union_star_level"] = (int)$union_star_level;//获得本次任务后的帮会星级
	    
	    LogApi::logProcess("INFO::taskinfo::CEventHandleTask.taskModule_gangStarComplete_event: task_id:$task_id eventInfo:".json_encode($eventInfo));
	
	    if(!RabbitMqInterface::rabbitmq_publish('event_taskModule_exchange', "event_taskModule_routingKey ", json_encode($eventInfo)))
	    {
	        LogApi::logProcess("WARN::taskinfo::CEventHandleTask.taskModule_gangStarComplete_event:rabbitmq_publish Failed! ".json_encode($eventInfo));
	    }
	}
	
	//主播任务事件
	public function taskModule_singer_event($event_type,$singer_id, $task_id,&$awardItems)
	{
	    $userinfo = array();
	    $baseinfo = new base_info_query($this->redis, $this->db);
	    $baseinfo->base_uid2userInfo($singer_id,&$userinfo);
	    $singer_nick = $userinfo["nick"];
	     
	    $taskInfo = array();
	    $baseinfo->base_taskId2taskType($task_id, &$taskInfo); 
	     
	    $eventInfo = array();
	    $eventInfo["eventName"] = "taskModule_singer_event";
	    $eventInfo["eventTime"] = time();//开启事件时间
	    $eventInfo["log_type"] = (int)$event_type;//日志类型
	    $eventInfo["task_id"] = (int)$task_id;//具体任务实例id
        $eventInfo["t_id"] = (int)$taskInfo["t_id"];//    任务id
        $eventInfo["t_type"] = (int)$taskInfo["task_type"];//任务类型
        $eventInfo["target_type"] = (int)$taskInfo["target_type"];//目标任务类型
	    $eventInfo["singer_id"] = (int)$singer_id;//主播id
	    $eventInfo["singer_nick"] = (string)$singer_nick;//用主播昵称
	    $eventInfo["awardItems"] = $awardItems;//资产变更（是个数组）
	    
	    LogApi::logProcess("INFO::taskinfo::CEventHandleTask.taskModule_singer_event: task_id:$task_id eventInfo:".json_encode($eventInfo));
	    
	    if(!RabbitMqInterface::rabbitmq_publish('event_taskModule_exchange', "event_taskModule_routingKey", json_encode($eventInfo)))
	    {
	        LogApi::logProcess("WARN::taskinfo::CEventHandleTask.taskModule_singer_event:rabbitmq_publish Failed! ".json_encode($eventInfo));
	    }
	    
	}

	//挖宝奖励领取
	public function digTreasure_getAward_event($uid, $leaf, $sun, $debris, $gold)
	{
        $eventInfo = array();
        $eventInfo["eventName"] = "digTreasure_getAward_event";
        $eventInfo["getAwardTime"] = time();
        $eventInfo["leaf"] = intval($leaf);
        $eventInfo["sun"] = intval($sun);
        $eventInfo["debris"] = intval($debris);
        $eventInfo["gold"] = intval($gold);
        $eventInfo["uid"] = intval($uid);

        LogApi::logProcess("INFO::taskinfo::CEventHandleTask.digTreasure_getAward_event: eventInfo:".json_encode($eventInfo));
        
        if(!RabbitMqInterface::rabbitmq_publish('event_taskModule_exchange', "event_taskModule_routingKey", json_encode($eventInfo)))
        {
            LogApi::logProcess("WARN::taskinfo::CEventHandleTask.digTreasure_getAward_event:rabbitmq_publish Failed! ".json_encode($eventInfo));
        }
	}
	
}







?>

