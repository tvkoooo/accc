<?php
// 直播间事件接口
class channel_api
{    
    // 用户进入直播间事件
    public static function on_p_user_real_enter_channel_event($params)
    {        
        LogApi::logProcess("on_p_user_real_enter_channel_event rq:".json_encode($params));

        //
        $sid = $params['sid'];
        $uid = $params['uid'];
        $singer_id = $params['singer_id'];
        //取出用户信息，取出主播信息
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        $info_singer = $userInfo->getInfoById($singer_id);
        //取出是否是新用户信息
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);      

        do
        {
            if (0 == $sid || 0 == $uid)
            {
                // 100000301(301)无效的参数
                $error['code'] = 100000301;
                $error['desc'] = '无效的参数';
                break;
            }   
            $return = array();
            
            //触发机器人说话的入场流程
            {     
                //触发机器人说话的入场流程
                $rtm = new robot_talk_model();
                //是否有超过3s的新人数据,如果有，去执行说话
                $rtm->on_lookup_newuser_in_redis(&$return,$sid,$info_singer);
                //用户入场登记
                $rtm->on_redis_record_user($sid,$user,$userAttr);
            }
            
            //触发连麦主播/用户入场流程
            {
                //LogApi::logProcess("linkcall sid:$sid singer_id:$singer_id");
                $linkcall = new linkcall_api();
                //判断是否是主播
                if ($singer_id == $uid) 
                {
                    //是主播，触发主播入场
                   // LogApi::logProcess("linkcall sid:$sid singer_id:$singer_id");
                    //$linkcall->on_linkcall_singer_start($params);
                    
                }
                else 
                {
                    //是用户，触发用户入场
                   // LogApi::logProcess("linkcall sid:$sid singer_id:$singer_id");
                    //$linkcall->on_linkcall_user_in($params);
                    
                }
            }
            
            
            
		}while(FALSE);
        return $return;
	}
	
	// 用户离开直播间事件
    public static function on_p_user_real_leave_channel_event($params)
    {        
        LogApi::logProcess("on_p_user_real_leave_channel_event rq:".json_encode($params));
        //
        $sid = $params['sid'];
        $uid = $params['uid'];
        $singer_id = $params['singer_id'];
        do
        {
            if (0 == $sid || 0 == $uid)
            {
                // 100000301(301)无效的参数
                $error['code'] = 100000301;
                $error['desc'] = '无效的参数';
                break;
            }  
            $return = array();
            
            //触发机器人说话的出场流程
            {
                // 触发机器人说话的出场流程
                $rtm = new robot_talk_model();                
                $rtm->on_redis_delete_user($sid,$uid);
            }
            
            //触发连麦主播/用户出场流程
            {
                $linkcall = new linkcall_api();
                //判断是否是主播
                if ($singer_id == $uid)
                {
                    //是主播，触发主播离场
                    $linkcall->on_linkcall_singer_over($params,&$return);
                }
                else
                {
                    //是用户，触发用户离场
                    $linkcall->on_linkcall_user_out($params,&$return);
            
                }
            }
            
            
        }while(FALSE);
        return $return;        
	}
	
	// 用户一分钟没有说话触发事件
	public static function on_p_channel_heartbeat_event($params)
	{
	    LogApi::logProcess("on_p_channel_heartbeat_event rq:".json_encode($params));
	    //
	    $sid = $params['sid'];
	    $uid = $params['uid'];
	    $singer_id = $params['singer_id'];
	    //取出主播信息
	    $userInfo = new UserInfoModel();
	    $info_singer = $userInfo->getInfoById($singer_id);
	    do
	    {
	        if (0 == $sid || 0 == $uid)
	        {
	            // 100000301(301)无效的参数
	            $error['code'] = 100000301;
	            $error['desc'] = '无效的参数';
	            break;
	        }
	        $return = array();
	
	        //
	        {   
                // 触发机器人说话的1分钟刷新流程
                $rtm = new robot_talk_model();
                //判断redis缓存time 数据是否有超过60s的房间号（sid）,如果有就提交到[普通说话发送]
                $rtm->on_lookup_time_in_redis(&$return,$sid,$info_singer);
                //判断redis缓存newuser 数据是否有超过3s的房间号（sid）如果有，内容提交到[控制台合成新人说话]
                $rtm->on_lookup_newuser_in_redis(&$return,$sid,$info_singer);
                //判断redis缓存gift 数据是否有超过3s的房间号（sid）并执行一次说话
                $rtm->on_lookup_gift_in_redis(&$return,$sid,$info_singer);
	        }
	
	    }while(FALSE);
	    return $return;
	}	
	
	
}

?>