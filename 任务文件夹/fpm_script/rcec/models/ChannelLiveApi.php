<?php

class ChannelLiveApi
{
    //获得主播开启任务
    public static function getSingerOpenTask($params)
    {
        LogApi::logProcess("begin getSingerOpenTask::****************params:".json_encode($params));
        
        $uid = intval($params['uid']);
        $singerid = intval($params['singerid']);
        
        $result = array(
            'cmd' => 'RGetSingerOpenTask',
            'singerid' => $singerid,
            'uid' => $uid,
//             'data' => array(),
            'result' => 0
        );
        
        $taskModel = new TaskModel();
        $data = $taskModel->getSingerOpenTask($singerid);
        if(empty($data)){
            $result['result'] = -1;
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        
        $return = array();
        $arr_data = array_merge($result, $data);
        
        $return[] = array(
            'broadcast' => 0,
            'data' => $arr_data
        );
        
        LogApi::logProcess("end getSingerOpenTask::****************return:".json_encode($return));
        
        return $return;
    }
    
    //主播开播获取直播间完成阳光任务信息
    public static function GetSingerRoomSunTask($params)
    {
        LogApi::logProcess("begin GetSingerRoomSunTask::params:".json_encode($params));
        $singerid = intval($params['singerid']);
        $result = array(
            'cmd' => 'RGetSingerRoomSunTask',
            'result' => 0
        );
        
        $channelLiveModel = new ChannelLiveModel();
        $data = $channelLiveModel->GetSingerRoomSunTask($singerid);
        
        $return = array();
        $arr_data = array_merge($result, $data);
        
        $return[] = array(
            'broadcast' => 0,
            'data' => $arr_data
        );
        
        LogApi::logProcess("end GetSingerRoomSunTask::****************".json_encode($return));
        
        return $return;
    }
    
    //用户领取阳光
    public static function GetSunValue($params)
    {
        LogApi::logProcess("begin GetSunValue::****************params:".json_encode($params));
        $uid = intval($params['uid']);
        $result = array(
            'cmd' => 'RGetSunValue',
            'uid' => $uid,
            'result' => 0
        );
        
        $channelLiveModel = new ChannelLiveModel();
        $flag = $channelLiveModel->GetSunValue($uid);
        if(empty($flag)){
            $result['result'] = -1;
        }else{
            $result['data'] = $flag;
        }
        
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $result['total_sun'] = (int)$userAttr['sun_num'];
        
        $return = array();
        
        $return[] = array(
            'broadcast' => 0,
            'data' => $result
        );
        
        LogApi::logProcess("end GetSunValue::****************".json_encode($return));
        
        return $return;
    }
    
    //主播打开或关闭摄像头
    public static function openOrCloseCamera($params)
    {
        $sid = intval($params['sid']);
        $flag = intval($params['flag']);
        
        $return = array();
        $result = array(
            'cmd' => 'ROpenOrCloseCamera',
            'singerUid' => $params['singerUid'],
            'sid' => $sid,
            'flag' => $flag,
            'result' => 0
        );
        $broadcastResult = array(
            'cmd' => 'BOpenOrCloseCamera',
            'singerUid' => $params['singerUid'],
            'sid' => $sid,
            'flag' => $flag
        );
        
        $return[] = array(
            'broadcast' => 0,
            'data' => $result
        );
        $return[] = array(
            'broadcast' => 1, //全直播间
            'data' => $broadcastResult
        );

        $channelLiveModel = new ChannelLiveModel();
        $channelLiveModel->setCameraStatus($sid, $flag);
        
        return $return; 
    }
    
    //主播打开或关闭麦克风
    public static function openOrCloseMicro($params)
    {
        LogApi::logProcess("openOrCloseMicro::****************".json_encode($params));
        $sid = intval($params['sid']);
        $flag = intval($params['flag']);
        
        $return = array();
        $result = array(
            'cmd' => 'ROpenOrCloseMicro',
            'singerUid' => $params['singerUid'],
            'sid' => $sid,
            'flag' => $flag,
            'result' => 0
        );
        $broadcastResult = array(
            'cmd' => 'BOpenOrCloseMicro',
            'singerUid' => $params['singerUid'],
            'sid' => $sid,
            'flag' => $flag
        );
        
        $return[] = array(
            'broadcast' => 0,
            'data' => $result
        );
        $return[] = array(
            'broadcast' => 1, //全直播间
            'data' => $broadcastResult
        );

        $channelLiveModel = new ChannelLiveModel();
        $channelLiveModel->setMicroStatus($sid, $flag);
        
        return $return; 
    }
    /* 
    //主播开始直播
    public static function startChannelLive($params)
    {

        LogApi::logProcess('************************startChannelLive***********::' . json_encode($params));
        
        $result = array(
            'cmd' => 'RStartChannelLive'
        );
        
        $sid = $params["sid"];//intval($params["sid"]);;

        $channelLiveModel = new ChannelLiveModel();
        $channelLiveModel->clearUserCountCache($sid);
        

        LogApi::logProcess('************************end startChannelLive***********sid:' . $sid);
        
        
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    } */
    
    //更新直播间信息（不开播的）
    public static function updateChannelLiveInfoxxx($params)
    {
        LogApi::logProcess('************************更新直播间信息（不开播）***********');
        $result = array(
            'cmd' => 'RUpdateChannelLiveInfoxxx'
        );
        
        $channelLiveModel = new ChannelLiveModel();
        $channelLiveModel->updateChannelLiveInfoxxx($params);
        
        $return = array();
        $return[] = array(
            'broadcast' => 0,
            'data' => $result
        );
        
        return $return;
    }
    
    //更新正在开播的直播间信息
    public static function updateChannelLiveInfo($params)
    {
        $result = array(
            'cmd' => 'RUpdateChannelLiveInfo'
        );
        
        if (!isset($params["sid"]) or !isset($params["cid"]) or !isset($params["uid"]) or !isset($params["starttime"]) or !isset($params["num"])) {
            $result["success"] = false;
            $result["reason"] = "invalid params";
            
            LogApi::logProcess('************************更新正在开播的直播间信息*invalid params**********'. json_encode($params));
            
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        
        $channelLiveModel = new ChannelLiveModel();
        $data = $channelLiveModel->updateChannelLiveInfo($params);
        
        $return = array(
            array(
                'broadcast' => 0,
                'data' => $data
            )
        );
        
//         LogApi::logProcess('************************结束更新正在开播的直播间信息***********::' . json_encode($return));
        
        return $return;
    }

    public static function getPlayUrl($params)
    {
        $returnResult = array(
            'cmd' => 'RGetPlayUrl',
            'result' => 0
        );
        $sid = $params['sid'];
        $channelLiveModel = new ChannelLiveModel();
        $playParamStr = $channelLiveModel->getPlayUrl($sid);
        $playParam = json_decode($playParamStr);
        $returnResult['sid'] = $sid;
        $returnResult['param'] = $playParam;
        
        $return = array();
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function setPlayUrl($params)
    {
        $returnResult = array(
            'cmd' => 'RSetPlayUrl',
            'result' => 0
        );
        $sid = $params['sid'];
        $playParamStr = json_encode($params['param']);
        //LogApi::logProcess('************************setPlayUrl***********::' . $playParamStr );
        $channelLiveModel = new ChannelLiveModel();
		$channelLiveModel->setPlayUrl($sid, $playParamStr);
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }
    public static function setPublishState($params){
        $result = 0;
        $sid = $params['sid'];
        $singerUid = $params['singerUid'];
        $isPublish = $params['isPublish'];
        
        // 主播开播，则更新主播7天榜排序
        $rankingModel = new RankingModel();
		if($rankingModel->singerIncomeRankForOnline($sid, $singerUid, $isPublish)){
			$result = 0;
		}else{
			$result = -1;
		}
		
    	$returnResult = array(
            'cmd' => 'RUpdatePublishState',
            'result' => $result
        );
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }
    
    public static function getSingerLaunchGame($params)
    {
    	LogApi::logProcess("begin getSingerLaunchGame****************".json_encode($params));
    	$singerid = intval($params['singerid']);
    	$sid = intval($params['sid']);
    	$uid = intval($params['uid']);
    	
    	$result = array(
    			'cmd' => 'RGetSingerLaunchGame',
    			'result' => 0
    	);
    	
    	if(empty($params['singerid'])
    			|| empty($params['sid'])
    			|| empty($params['uid'])){
    				$result['result'] = -1;
    				$result['errmsg'] = "请求参数异常";
    				LogApi::logProcess("getSingerLaunchGame*************error0***".json_encode($result));
    				return array(
    					array(
    							'broadcast' => 0,
    							'data' => $result
    					)
    				);
    	}
    	
    	$gameModel = new GameModel();
    	$data = $gameModel->GetSingerLaunchGame($singerid, $uid);
    	
    	if (empty($data)) {
    		$result['result'] = 1;
    		$result['errmsg'] = "主播未发起游戏";
			return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
    	}
    	
    	if ($data['gameid'] == GameModel::$GAME_ID_DICE) {
    		$toolModel = new ToolModel();
    		$tool = $toolModel->getToolByTid($data['giftId']);
    		$data['giftName'] = $tool['name'];
    		$data['giftMoney'] = $tool['price'];
    	}
    	
        $arr_data = array_merge($result, $data);
        
        $return = array();
    	$return[] = array(
    			'broadcast' => 0,
    			'data' => $arr_data
    	);
    	
    	LogApi::logProcess("end getSingerLaunchGame::****************return:".json_encode($return));
    	
    	return $return;
    }
    
    public static function checkIsValidSinger($params)
    {
    	LogApi::logProcess("begin checkIsValidSinger****************".json_encode($params));
    	$uid = $params['uid'];
    	
    	$result = array(
    			'cmd' => 'RCheckIsValidSinger',
    			'result' => -1
    	);
    	
    	if (empty($uid)) {
    		LogApi::logProcess("checkIsValidSinger*************error0***".json_encode($result));
    		return array(
    				array(
    						'broadcast' => 0,
    						'data' => $result
    				)
    		);
    	}
    	
    	$channelLiveModel = new ChannelLiveModel();
    	$row = $channelLiveModel->getSingerAnchorInfo($uid);
    	if ($row) {
    		if ($row['call_flag'] == 1) {
    			$result['result'] = 0;
    		}
    	}
    	
    	$return = array();
    	$return[] = array(
    			'broadcast' => 0,
    			'data' => $result
    	);
    	
    	LogApi::logProcess("end checkIsValidSinger::****************return:".json_encode($return));
    	 
    	return $return;
    }
    
    public static function on_create_stream_rq($params)
    {
    	LogApi::logProcess("ChannelLiveApi:on_create_stream_rq:" . json_encode($params));
    	$uid = isset($params['uid'])?$params['uid']:0;
    	
    	$result = array(
    			'cmd' => 'RCreateStream',
    			'uid' => intval($uid),
    			'url' => '',
    			'audio_url' => '',
    			'token' => '',
    			'stream' => '',
    			'result' => 0,
                'errmsg' => ''
    	);
    	
    	$result_stream_start = null;
    	do {
    		if (empty($uid)) {
    			$result['result'] = 201;
    			break;
    		}

            // 验证平台是否维护
            $model_sysparam = new SysParametersModel();
            $is_closed = $model_sysparam->GetSysParameters(248, 'parm1');

            if ($is_closed != 1) {
                $result['result'] = 2;
                $result['errmsg'] = '小草平台正在维护，暂时不能开播，请耐心等待。';
                break;
            }
    		
    		// 验证主播有效性
    		$channelLiveModel = new ChannelLiveModel();
    		$row = $channelLiveModel->getSingerAnchorInfo($uid);
            if (empty($row) || $row['call_flag'] != 1) {
                $result['result'] = 1;
                $result['errmsg'] = '您没有开播权限！';
                break;
            }

    		// 发送StreamStart至session服务
    		$result_stream_start = array(
    				'cmd' => 'StreamStarted'
    		);
    		
    		// 处理SingerPlayStart
    		// singerPlayStart need 'starttime'.
    		$params['starttime'] = time();    		
    		$return_play_start = UserApi::singerPlayStart($params);
    		
    	} while (0);
    	
    	$return[] = array(
    			'broadcast' => 0,
    			'data' => $result
    	);
    	
    	if (!empty($result_stream_start)) {
    		$return[] = array (
    				'broadcast' => 9,	// sessionServerOnly
    				'data' => $result_stream_start
    		);
    	}

    	LogApi::logProcess("ChannelLiveApi:on_create_stream_rq rs:" . json_encode($return));
    	
    	return $return;
    }
    
    public static function on_p_channel_heartbeat_30_event($params)
    {    	
    	LogApi::logProcess("ChannelLiveApi:on_p_channel_heartbeat_30_event:" . json_encode($params));

    	if (!isset($params["sid"]) or !isset($params["cid"]) or !isset($params["uid"]) or !isset($params["starttime"]) or !isset($params["num"])) {
    		$result["success"] = false;
    		$result["reason"] = "invalid params";
    		LogApi::logProcess("ChannelLiveApi:on_p_channel_heartbeat_30_event invalid params:" . json_encode($params));
    		return;
    	}
    	
    	$channelLiveModel = new ChannelLiveModel();
    	$data = $channelLiveModel->updateChannelLiveInfo($params);
    	
    	LogApi::logProcess("ChannelLiveApi:on_p_channel_heartbeat_30_event rs:" . json_encode($data));
    	
    	return $data;
    }
    
    public static function on_p_channel_heartbeat_60_empty_event($params)
    {
    	LogApi::logProcess("ChannelLiveApi:on_p_channel_heartbeat_60_empty_event:" . json_encode($params));

    	$channelLiveModel = new ChannelLiveModel();
    	$channelLiveModel->updateChannelLiveInfoxxx($params);
    	
    	return null;
    }
}

?>