<?php

class UserApi
{
	//产生礼物
	public static function createGift($params)
	{
		LogApi::logProcess("************UserApi::createGift");
		
        $result = array(
            'cmd' => 'RCreateGift'
        );
        
		$uid = $params['uid'];
		$type = $params['type'];
        
    	$userInfoModel = new UserInfoModel();
    	$value = $userInfoModel->createGift($uid, $type);
    	$result = $result + $value;
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
	}
	
	//获取用户进入直播间倒计时的剩余时间
    public static function getUserCountdownTime($params)
    {
    	//LogApi::logProcess("************UserApi::getUserCountdownTime");
    	
    	//'robot' => '0'默认不是机器人
        $result = array(
            'cmd' => 'RGetUserCountdownTime',
            'robot' => 0
        );
        
    	$uid = $params['uid'];
    	
    	if(10000985<=$uid && $uid<=10002302){//uid在该区间为机器人
    		$result['robot'] = 1;
    	}else{
	    	$userInfoModel = new UserInfoModel();
	    	$value = $userInfoModel->getUserJoinChannelTime($uid);
	    	$result = $result + $value;
    	}
    	
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    	
    }
    
    //用户离开直播间时更新在线时长
    public static function updateUserCountdownTime($params){
    	//LogApi::logProcess("************UserApi::updateUserCountdownTime");
    	$result = array(
            'cmd' => 'RGetUserCountdownTime'
        );
        
        $uid = $params['uid'];
        
    	if(10000985<=$uid && $uid<=10002302){//uid在该区间为机器人
    		
    	}else{
	    	$userInfoModel = new UserInfoModel();
	    	$userInfoModel->updateUserCountdownTime($uid);
    	}
        
        $result['result'] = 0;
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function getSingerInfo($params)
    {
		ToolApi::logProcess("getSingerInfo IN");
		
        $result = array(
            'cmd' => 'RGetSingerInfo'
        );
        if (empty($params['uid_onmic'])) {
            $result['result'] = 103; // 没有麦上表演者信息
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
		
		ToolApi::logProcess("getSingerInfo ------");
		
        $userAttrModel = new UserAttributeModel(); ///zzzzz
        $perfAttr = $userAttrModel->getAttrByUid($params['uid_onmic']);
		ToolApi::logProcess("begin getResponseInfo");
        $response = $userAttrModel->getResponseInfo($perfAttr, true);
		ToolApi::logProcess("end getResponseInfo");
        // 返回结果
        $result = $result + $response;
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function getUserInfo($params, $init = FALSE)
    {
        $result = array(
            'cmd' => 'RGetUserInfo'
        );
        $uid = $params['uid'];
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $response = $userAttrModel->getResponseInfo($userAttr);
        $result = $result + $response;
        if ($init) {
            $userAttrModel->delStatusByUid($uid, 'heart_convert_lock');
            $userAttrModel->delStatusByUid($uid, 'num_heart_convert');
        }
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function getSessOwnerInfo($params)
    {
	ToolApi::logProcess("getSessOwnerInfo1111111111111" . $params);
        $result = array(
            'cmd' => 'RGetSessOwnerInfo'
        );
        $sid = $params['sid'];
	$userAttrModel = new UserAttributeModel();
	ToolApi::logProcess("getSessOwnerInfo+++++++++++1" . $result);
	$uid = $userAttrModel->getSessOwner($sid);
	ToolApi::logProcess("getSessOwnerInfo+++++++++++2" . $uid);
	$userInfoModel = new UserInfoModel();
	$data = $userInfoModel->getSessOwnerInfo($sid, $uid);
	//$userAttrModel2 = new UserAttributeModel();
	//$userAttr = $userAttrModel2->getUserInfo($uid);
	//ToolApi::logProcess("getSessOwnerInfo+++++++++++3" . $userAttr['nick']);
	//$userAttrModel3 = new UserAttributeModel();
        //$flower = $userAttrModel3->getFlower($uid);
	//ToolApi::logProcess("getSessOwnerInfo+++++++++++4" . $flower);

	$result = $result + $data;
	ToolApi::logProcess("getSessOwnerInfo+++++++++++" . $result);
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }


    public static function micOff($params)
    {
        $returnResult = array(
            'cmd' => 'BMicOff'
        );
        $singerUid = $params['uid_onmic'];
        $userAttrModel = new UserAttributeModel();
        $userAttrModel->setStatusByUid($singerUid, 'on_mic', false);
        $userAttrModel->setStatusByUid($singerUid, 'effect', 0);
        $returnResult['sid'] = $params['sid'];
        $returnResult['cid'] = $params['cid'];
        return array(
            array(
                'broadcast' => 1,
                'data' => $returnResult
            )
        );
    }

    public static function micOn($params)
    {
        $result = array(
            'cmd' => 'BMicOn'
        );
        $singerUid = $params['uid_onmic'];
        $userAttrModel = new UserAttributeModel();
        $userAttrModel->setStatusByUid($singerUid, 'on_mic', true);
        // 初始化寶箱互動道具
        $giftBoxModel = new GiftBoxModel();
        $giftBoxModel->initGiftBox($singerUid);
        // 獲取歌手信息
        $info = UserApi::getSingerInfo($params);
        $result['SingerInfo'] = $info[0]['data'];
        // 獲取歌手的周粉絲榜
        $info = WidgetApi::getRanking($params, 'gift');
        $result['Ranking'] = $info[0]['data'];
        // 上麥通知
        $userInfoModel = new UserInfoModel();
        $singerNick = $userInfoModel->getNickName($singerUid);
        $singerGuardModel = new SingerGuardModel();
        $singerGuardModel->notifyGuards($singerUid, $singerNick, $params['sid'], $params['cid']);
        // 清理
        $rankingModel = new RankingModel();
        $rankingModel->clearVipChair($singerUid);
        // 返回结果
        return array(
            array(
                'broadcast' => 1,
                'data' => $result
            )
        );
    }

    /**
     * 用戶進入一個頻道，根據身份播放動畫
     */
     public static function enterChannel($params)
    {
        LogApi::logProcess('***************UserApi::enterChannel ...params:'.json_encode($params));
        
        $uid = (int)$params['uid'];
        $nick = $params['sender'];
        $singerUid = (int)$params['uid_onmic'];
        $sid = (int)$params['sid'];
        //$socket_id = (int)$params['socket_id'];
        $socket_id = (int)(isset($params['socket_id'])?$params['socket_id']:0);
        
        $now = time();
        $userInfo = new UserInfoModel();
        $userAttrModel = new UserAttributeModel();
        $singerGuardModel = new SingerGuardModel();
       
		// zxy increase 2015-9-12 15:24:24
        $user = $userInfo->getInfoById($uid);
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $richManInfo = $userAttrModel->getRichManLevel($uid, $userAttr['gift_consume'], $userAttr['consume_level']);
		$rachManLevel = $richManInfo['richManLevel'];
		
		$is_robot = (int)$user['is_robot'];
		
        // 新人判断
        $new = 0;
        if (!$is_robot) {
        	if (isset($userAttr['new'])) {
        		$new = $userAttr['new'];
        	} else {
        		$new = UserApi::isNewerByUattrinfo($userAttr, $user);
        	}
        }

        // 新人礼物使用次数
        $newer_gift_left = 0;
        if ($new != 0) {
        	$toolModel = new ToolModel();
        	$user_newer_gift_used = $toolModel->getNewerGiftCountUsed($uid);
        	$user_newer_gift_used = isset($user_newer_gift_used)?$user_newer_gift_used:0;
        	
        	$sys_parameters = new SysParametersModel();
        	$newer_gift_per_day = $sys_parameters->GetSysParameters(208, 'parm1');
        	$newer_gift_per_day = isset($newer_gift_per_day)?$newer_gift_per_day:0;
        	
        	$newer_gift_left = $newer_gift_per_day - $user_newer_gift_used;
        	if ($newer_gift_left < 0) {
        		$newer_gift_left = 0;
        	}
        }

        $carinfo = $userAttrModel->getCarInfoByUid($uid);
        $carnum= $userAttrModel->getCarNumberByUid($uid,'number');
        $endTime = $singerGuardModel->getGuardEndTime($uid, $singerUid);//$userAttrModel->getStatusByUid($uid, 'guard_end_time');
        $guardType = $singerGuardModel->getGuardType($uid, $singerUid);
        $isguard = 0 ;
        if (!empty($endTime) && $endTime > $now) {
            if(1 == $guardType || 2 == $guardType || 3 == $guardType){
                //守护有效
                $isguard = 1;
            }
        } 
        //新增加大天使小天使改守护判断,如果该主播什么都不是，取出天使守护主播id判断是否 是其他主播的大天使。
        $guardType_tianshi=$guardType;// 避免其他模块 $guardType 混用，新增功能小天使显示另用一个继承字段 $guardType_tianshi
        {
            if( 0 == $isguard )
            {
                $userAttrModel = new UserAttributeModel();
                $query = "select 1 from rcec_main.user_guard_identify where uid=$uid and identify=3 limit 1";
                $rs = $userAttrModel->getDbMain()->query($query);
                if (! $rs || $rs->num_rows == 0) {
                    ;//无数据，该用户没有天使特效
                }
                else
                {
                    $guardType_tianshi = 33;//有数据，改用户有其他房间的天使特效，但不是本房间大天使
                }
            }            
        }
        
        

        if($carinfo!=null)
        {
            $userAttrModel->addChannelCarinfo($sid,$uid,$carinfo['id'],$carnum);
        }
        
        //zkay 将用户id添加到redis
        $activeManInfo = $userAttrModel->getActiveLevel($userAttr['active_point'], $uid, 0);
//        $userAttrModel->addCacheUserIdList($sid,$singerUid,$uid,$activeManInfo['activeManLevel'],$isguard);
//         $userCount = $userAttrModel->getOnlineUserCount($sid);
        
        //把用户等级相关信息放入redis缓存
        $userAttrModel->addChannelUserLevelinfoToRedis($sid,$uid,$now);
        
        $channelLiveModel = new ChannelLiveModel();
        $len = $channelLiveModel->getSingerCurDayPlayTime($singerUid);
        //获取直播间摄像头和麦克风状态
        $flags = $channelLiveModel->getRoomStatus($sid);
        LogApi::logProcess("**********************UserApi::enterChannel::flags: ".json_encode($flags));
        
        $return = array();
        if ($singerUid == $uid) {
            // 清除游戏状态
            GameApi::dealExceptionOver($uid, &$return);
        }

        //记录用户在直播间时长
        
        $user_glory_total = 0;
        $user_glory_lv = 0;
        if($singerUid != $uid && !$is_robot){
            LogApi::logProcess("*xxxxxxxxxxxxxxxxxxxUserApi::enterChannel:: 开始计时。。。 ");
            $channelLiveModel->startWatchRoom(&$return, $sid, $uid);
            //亲密度：铁杆粉丝每天第一次进入直播间增加与主播的亲密度
            $channelLiveModel->addIntimacy($singerUid, $uid);
            
            $taskModel = new TaskModel();
            $taskModel->startWatchTask($uid);
            LogApi::logProcess("下面开始铁杆粉任务**********************");
            /* //去掉粉丝团任务
            //初始化粉丝团任务
            $falg = $singerGuardModel->isDiehard($singerUid, $uid);
            if($falg){
                LogApi::logProcess("初始化用户：$uid, 主播：$singerUid, 铁杆粉任务**********************");
                $taskModel->initFollowerTasks($singerUid, $uid);
                
            } */
            
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$singerUid,
                    'target_type' => 12,//为主播间在线人数
                    'num' => 1,
                    'extra_param' => 0
                )
            );
            
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 28,//28为观看主播数
                    'num' => 1,
                    'extra_param' => (int)$singerUid
                )
            );
        }
        
        if (!$is_robot) {
        	// 用户荣耀值
        	$model_glory = new GloryModel();
        	$ret_glory = $model_glory->onUserEnter($sid, $uid, $singerUid, $userAttr['consume_level']);
        	$user_glory_total = $ret_glory['user_glory_inf']['glory_total'];
        	$user_glory_lv = $ret_glory['user_glory_inf']['glory_lv'];
        	
        	if (!empty($ret_glory['room_glory_inf'])) {
        		$return[] = $ret_glory['room_glory_inf'];
        	}
        }

//         $activeManInfo = $userAttrModel->getActiveLevel($userAttr['active_point'], $uid);
        
        //if( $isguard != 0 || $carinfo != null  ) {
            //如果有车或者是守护，则广播用户进入消息
            
        $channelLiveModel = new ChannelLiveModel();
        $session = $channelLiveModel->getSessionInfo($sid);
        
        $singerUid = $session['owner'];
        
        $user_anchor_model = new UserAnchorSignupWeek();
        $user_anchor_model->uid = $uid;
        $user_anchor_model->sid = $sid;
        $user_anchor_model->zid = $singerUid;
        $user_anchor_model->GetDataFramDB($now);
        
        // 获取关注关系
        $model_fans = new follow_rel_model();
        $b_fans = $model_fans->b_my_fans($singerUid, $uid);
            
        // 星魁
        $star_top_spot_uid = 0;
        {
            $star_top_spot = new star_top_spot_model();
            $error = array();
            $star_top_spot->redis_get_last_week_star_top_uid(&$error, $singerUid, &$star_top_spot_uid);
        }
        
        $return[] = array(
            'broadcast' => 0,
            'data' => array(
                'cmd' => 'REnterChannel',
                'uid' => $uid,
                'palyTotalTime' => $len,//TODO:需要加入总的直播时长
                'money' => (int)$userAttr['coin_balance'],
                'totalSun' => (int)$userAttr['sun_num'],
                'cameraStatus' => $flags['cameraStatus'],//0:关闭 1：打开
                'microStatus' => $flags['microStatus'],//0:关闭 1：打开
                'canweekStar' => (int)$user_anchor_model->canweekStar, //可以报名周星 0：否  1：是
                'weekStar' => (int)$user_anchor_model->weekStar, //是否已报名 0：未报名 1：已报名
                'weektool_id' => (int)$user_anchor_model->weektool_id, //周星礼物id
                'week_ranking' => (int)$user_anchor_model->week_ranking, //周星排名
                'weektool_img' => $user_anchor_model->weektool_img, //周星礼物图片
                'weektool_name' => $user_anchor_model->weektool_name, //周星礼物名称
                'new' => $new,
            	'newer_gift_left' => $newer_gift_left,
                'star_king' => (int)$star_top_spot_uid
            )
        );
            
        if ($singerUid != $uid) {
            //如果还有未领取的阳光，需要广播
            $data_arr = $channelLiveModel->getNoRecvSunValue($uid);
            if(!empty($data_arr)){
                $value = 0;
                foreach ($data_arr as $data)
                {                    
                    $l_obj = json_decode($data);
                    // LogApi::logProcess("?????GetSunValue :: data :$data");
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
                            // $key_light = ChannelLiveModel::ZsetCountLoadLightKey($sid);
                            // $this->getRedisMaster()->zIncrBy($key_light,$dt_value,$uid);
                            // $this->getRedisMaster()->expire($key_light,ChannelLiveModel::$LORD_COUNT_TTL);
                        }
                        
                        $sunvalue_final = (int)$l_obj->sunvalue * (1 + $glory_jc + $lord_jc);
                        $value += $sunvalue_final;            
                    }
                }
                if(!empty($value)){
                    $return[] = array(
                        'broadcast' => 1,
                        'data' => array(
                            'cmd' => 'BCreateSunshune',
                            'uid' => (int)$uid,
                            'sun_num' => $value,
                            'isRoom' => true
                        )
                    );
                }
                
            }
        }


		//新人信息
		$newerInfo = new stdClass();
		if($new)
		{
			$newerInfo->hasMoney = 2 == $new ? 1 : 0;
			$newerInfo->hasRedTicket = $userInfo->isHasRedTicket($uid) ? 1 : 0;
			$newerInfo->hasSunflowerSeeds = (0 < $newer_gift_left || $userInfo->ishasSunflowerSeeds($uid)) ? 1 : 0;
			$newerInfo->hasUnion = empty($user["union_id"]) ? 0 : 1;
		}

        
        $return[] = array(
            'broadcast' => 1,
            'data' => array(
                'cmd' => 'BEnterChannel',
                'uid' => $uid,
                'socket_id' => $socket_id,
                'nick' => $user['nick'],
                'photo' => $user['photo'],
                // zxy increase 2015-9-12 15:24:24
                'richManLevel' => $rachManLevel,
                'richManTitile'=> $richManInfo['richManTitle'],
                'richManEffect'=> $richManInfo['richManEffect'],
                'channelPoint' => $userAttr['channel_point'],
                'sd' => $now,
                'isguard' => $isguard,
                'guardType' => $guardType_tianshi,
                'activeLevel' => $activeManInfo['activeManLevel'],
                'activeManEffect' => $activeManInfo['activeManEffect'],
                //'vip' => $userAttr['vip'],
                'carid' => $carinfo['id'],
                'carname' => $carinfo['name'],
                'carnum' => $carnum,
                'caricon' => $carinfo['icon'],
                'carimg' => $carinfo['image'],
                'resource' => $carinfo['resource'],
                'entertime' => time(),
                'new' => $new,
            	'glory_info' => array(
            			'glory_total' => $user_glory_total,
            			'glory_lv' => $user_glory_lv
            	),
            	'follow_rel_type' => $b_fans ? 1 : -1,
            	'newInfo' => $newerInfo
            )
        );
       // }
       // file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "enterChannel end ===$nick==\n", FILE_APPEND);
       LogApi::logProcess('**********************UserApi::enterChannel leave...,return:'.json_encode($return));
        return $return;
    }
    public static function getChannelCarinfo($params)
    {
        $result = array(
            'cmd' => 'RGetChannelCarinfo',
            'uid' => (int)$params['uid']
        );
        $userAttrModel = new UserAttributeModel();
        $sid = $params['sid'];
        $result['cardata'] = $userAttrModel->getChannelCarinfobyCid($sid);

         // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );

    }
    public static function buyVip($params)
    {
        $returnResult = array(
            'cmd' => 'RBuyVip',
            'result' => 0
        );
        $uid = $params['uid'];
        $vip = $params['vip'];
        $userAttrModel = new UserAttributeModel();
        // 秀幣不夠，不能開通vip
        $newVipInfo = $userAttrModel->vipList[$vip];
        if (!$userAttrModel->deductCoin($uid, $newVipInfo['vipPrice'])) {
            $returnResult['result'] = 137;
            $return[] = array(
                'broadcast' => 0,
                'data' => $returnResult
            );
            return $return;
        }
        // 開vip，加相應記錄
        $result = $userAttrModel->openVip($uid, $vip);
        if ($result) {
            $vipBuyRecordModel = new VipBuyRecordModel();
            $vipBuyRecordModel->addRecord($uid, $newVipInfo);
            $returnResult += $result;
        }
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }
    
    //关注
    public static function followSinger($params)
    {
        $returnResult = array(
            'cmd' => 'RFollowSinger',
            'result' => 0
        );
        //主动方uid
        $uid = $params['uid'];
        //被动方uid
        $followUid = $params['followUid'];
        $userAttrModel = new UserAttributeModel();
        
        if (!empty($followUid) && $uid != $followUid) {
            $userAttrModel->follow($uid, $followUid);
        } else {
            $returnResult['result'] = 145; // 不能關注自己
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }

        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        // 廣播
        $broadcastResult = array(
            'cmd' => 'BFollowSinger',
            'uid' => $uid,
            'followUid' => $followUid,
            'fansNumber' => $userAttrModel->getFansNumber($followUid)
        );
        $return[] = array(
            'broadcast' => 1,
            'data' => $broadcastResult
        );
        return $return;
    }

    /* public static function followSinger($params)
    {
        $returnResult = array(
            'cmd' => 'RFollowSinger',
            'result' => 0
        );
        $uid = $params['uid'];
        $singerUid = $params['singeruid'];
        $userAttrModel = new UserAttributeModel();
        if ($userAttrModel->getFollowNumber($uid) > 100) {
            $returnResult['limit'] = 100;
            $returnResult['result'] = 144; // 關注主播數量超過100上限
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        if (!empty($singerUid) && $uid != $singerUid) {
            $userAttrModel->follow($uid, $singerUid);
        } else {
            $returnResult['result'] = 145; // 不能關注自己
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }

        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        // 廣播
        $broadcastResult = array(
            'cmd' => 'BFollowSinger',
            'singerUid' => $singerUid,
            'fansNumber' => $userAttrModel->getFansNumber($singerUid)
        );
        $return[] = array(
            'broadcast' => 1,
            'data' => $broadcastResult
        );
        return $return;
    } */

    public static function followSinger2($uid, $singerUid)
    {
        $returnResult = array(
            'cmd' => 'RFollowSinger',
            'result' => 0
        );
        $userAttrModel = new UserAttributeModel();
	if($userAttrModel->isFollow($uid, $singerUid))
	{
		return;
	}
        if ($userAttrModel->getFollowNumber($uid) > 100) {
		return;
        }
        if (!empty($singerUid) && $uid != $singerUid) {
            $userAttrModel->follow($uid, $singerUid);
        } else {
        	return;
	}
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        // 廣播
        $broadcastResult = array(
            'cmd' => 'BFollowSinger',
            'singerUid' => $singerUid,
            'fansNumber' => $userAttrModel->getFansNumber($singerUid)
        );
        $return[] = array(
            'broadcast' => 1,
            'data' => $broadcastResult
        );
        return $return;
    }


    public static function unfollowSinger($params)
    {
        $returnResult = array(
            'cmd' => 'RUnfollowSinger',
            'result' => 0
        );
        $uid = $params['uid'];
        $unfollowUid = $params['unfollow_uid'];
        $userAttrModel = new UserAttributeModel();
        $userAttrModel->unfollow($uid, $unfollowUid);
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        // 廣播
        $broadcastResult = array(
            'cmd' => 'BFollowSinger',
            'uid' => $uid,
            'unfollowUid' => $unfollowUid,
            'fansNumber' => $userAttrModel->getFansNumber($unfollowUid)
        );
        $return[] = array(
            'broadcast' => 1,
            'data' => $broadcastResult
        );
        return $return;
    }

    public static function isFollow($params)
    {
        $returnResult = array(
            'cmd' => 'RIsFollow',
            'result' => 0
        );
        $uid = $params['uid'];
        $singerUid = $params['uid_onmic'];
        $userAttrModel = new UserAttributeModel();
        $returnResult['isFollow'] = $userAttrModel->isFollow($uid, $singerUid);
        $returnResult['singerUid'] = $singerUid;
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function canCallFans($params)
    {
        $returnResult = array(
            'cmd' => 'RCanCallFans',
            'result' => 0
        );
        $uid = $params['uid'];
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        // 只有認證主播才能夠召喚粉絲
        if (empty($userAttr['auth'])) {
            $returnResult['result'] = 139; // 對不起，目前只有認證主播才能使用召喚粉絲的功能
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // 每天只能發起一次召喚粉絲
        if (!$userAttrModel->canCallFans($uid)) {
            $returnResult['result'] = 140; // 召喚粉絲次數達到上限
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // 可以召喚粉絲
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function callFans($params)
    {
        $returnResult = array(
            'cmd' => 'RCallFans',
            'result' => 0
        );
        $uid = $params['uid'];
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        // 只有認證主播才能夠召喚粉絲
        if (empty($userAttr['auth'])) {
            $returnResult['result'] = 139; // 對不起，目前只有認證主播才能使用召喚粉絲的功能
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // 每天只能發起一次召喚粉絲
        if (!$userAttrModel->canCallFans($uid)) {
            $returnResult['result'] = 140; // 召喚粉絲次數達到上限
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // 召喚粉絲
        $fans = $userAttrModel->getFans($uid);
        $bo = new BroadcastOnline();
        $boResult = $bo->callFansByIm($fans,
            array(
                'uid' => $params['uid'],
                'nick' => $params['sender'],
                'sid' => $params['sid'],
                'cid' => $params['cid']
            ));
        if (!$boResult) {
            $returnResult['result'] = 141; // 召喚粉絲失敗
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $userAttrModel->setStatusByUid($uid, 'last_time_call_fans', time());
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    /**
     * 守護基本數據獲取接口，返回價格信息
     */
    public static function getGuardApplyInfo($params)
    {
        $returnResult = array(
            'cmd' => 'RGetGuardApplyInfo',
            'result' => 0
        );
        $data = array();
        $data['uid'] = $params['uid'];
        $data['singerUid'] = $params['uid_onmic'];
        $userInfoModel = new UserInfoModel();
        $user = $userInfoModel->getInfoById($data['uid']);
        $data['userNick'] = $user['nick'];
        $data['userAccount'] = str_replace('@rc.im', '', $user['account']);
        $singer = $userInfoModel->getInfoById($data['singerUid']);
        $data['singerNick'] = $singer['nick'];
        $data['singerAccount'] = str_replace('@rc.im', '', $singer['account']);
        $userImageModel = new UserImageModel();
        $data['singerImage'] = $userImageModel->getDefaultImage($data['singerUid']);
        $data['time'] = time();
        $guardApplyModel = new SingerGuardApplyModel();
        $returnResult += array(
            'info' => $guardApplyModel->getPriceInfo(),
            'data' => $data
        );
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }
    /**
     * 申請開通守護
     */
    public static function applySingerGuard($params)
    {
        $returnResult = array(
            'cmd' => 'RApplySingerGuard',
            'result' => 0
        );
        $uid = $params['uid'];
        $sid = $params['sid'];
        $singerUid = $params['singerUid'];
        if (empty($singerUid)) {
            $returnResult['result'] = 151; // 沒有主播id
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // zxy modify 2015-12-29 21:14:13 禁用部分用户
		if($uid < 0 || $uid >= 10100000 || $uid == 10003266 || $uid == 10003260 || $uid == 10003258 || $uid == 10000298 || $uid==10000750){
			$returnResult['result'] = 149; // 沒有對應的價格信息
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
		}
        if ($singerUid == $uid) {
            $returnResult['result'] = 152; // 不能申請自己的守護
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $duration = empty($params['duration']) ? 1 : $params['duration'];
        $applyModel = new SingerGuardApplyModel();
        // 判斷是否已經有一條沒審核的申請
        if ($applyModel->hasNotHanlded($uid, $singerUid)) {
            $returnResult['result'] = 148; // 已經有一條沒審核的申請，暫時不能提交
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // 扣錢
        $price = $applyModel->getPriceByDuration($duration);
        if (!$price) {
            $returnResult['result'] = 149; // 沒有對應的價格信息
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $userAttrModel = new UserAttributeModel();
        try{
	        $userAttrModel->getDbMain()->query("BEGIN", false);
	        if (!$userAttrModel->deductCoin($uid, $price)) {
	        	$userAttrModel->getDbMain()->query("ROLLBACK", false);
	            $returnResult['result'] = 150; // 秀幣餘額不足，無法購買守護
	            return array(
	                array(
	                    'broadcast' => 0,
	                    'data' => $returnResult
	                )
	            );
	        }
	        
	        {
	        	// 增加用户的消费值，以便后续的财富等级计算
	            $query = "UPDATE user_attribute SET gift_consume = gift_consume + $price WHERE uid = $uid";
	            $rs = $userAttrModel->getDbMain()->query($query);
	            if($rs == false || $userAttrModel->getDbMain()->affected_rows <= 0){
		        	$userAttrModel->getDbMain()->query("ROLLBACK", false);
		            $returnResult['result'] = 151; // 更新用户消费值失败
		            return array(
		                array(
		                    'broadcast' => 0,
		                    'data' => $returnResult
		                )
		            );
	            }
	        }
	        // 添加申請 （自己和對方的記錄cache要清理）
	        $applyModel->addApplyRecord($uid, $singerUid, $duration, $price, $sid);
	        
	        // 加入守護列表，延長守護時間
	        $guardModel = new SingerGuardModel();
	        $endTime = $guardModel->addGuardRecord2($singerUid, $uid, $duration);
	        
	        // 給主播分成秀點
	        $userPoint = $price * 0.5;//Config.getGaurdConsumeShowPointScale();
	        //$userAttrModel2 = new UserAttributeModel();
	        $userAttrModel->addPoint($singerUid, $userPoint);
	        $userAttrModel->updateMonthPoint($userPoint, $singerUid);
	        $experience = $price * 1;//Config.getGiftConsumeExperience();
	        $userAttrModel->addExperienceByUid($singerUid, $experience);
	        // 給群分成秀點
	        $sessPoint = $price * 0.0;
	        $sessAttrModel = new SessionAttributeModel();
	        $sessAttrModel->addPoint($sid, $sessPoint);
	        
	        
	        {	// 增加开通守护的消费记录
	        	$order_id = microtime(true);
				$userAttrModel->addShowBiRecord($uid, $price, $order_id, '开通守护', $duration);
	        }
	        
	        $singerInfo = $userAttrModel->getAttrByUid($singerUid);
	        $userInfoModel = new UserInfoModel();
	        $singerNick = $userInfoModel->getNickName($singerUid);
	        {	// 更新排行榜
	        	$rankingModel = new RankingModel();
                // 更新房间内的贡献值排行表
                $isRankChg = false;
                $rankList = $rankingModel->updateSidUserConsumeRank($sid, $uid, $singerUid, $price, time(), $isRankChg);
                if($rankList) {
                  $rankList['cmd'] = 'RGetRankList';
                  $rankList['senderNick'] = $singerNick;
                  $return[] = array(
                        'broadcast' => 1,
                        'data' => $rankList
                  );
                }
                
                if ($isRankChg !== false) {
                    $return[] = array(
                        'broadcast' => 1,
                        'data' => array(
                            'cmd' => 'BBroadcast',
                            'rank' => array(
                                'uid' => $uid,
                                'nick' => $singerNick
                            )
                        )
                    );
                }
	        }
	        // 廣播
	        $closeLevel = 1;
	        $closeValue = $guardModel->getCloseValue($uid, $singerUid);
	        if ($closeValue !== false) {
	            $closeLevel = $guardModel->getCloseLevel($closeValue);
	        }
			$authTime = time();
	        
	        $broadcastResult = array(
	            'cmd' => 'BAcceptGuardApply',
	            'singerUid' => $singerUid,
	            'singerNick' => $singerNick,
	            'closeLevel' => $closeLevel,
    			'authTime' => $authTime,
    			'endTime' => $endTime,
    			'singerChannelPoint' => $singerInfo['channel_point']
	        );
	        $userInfo = $userAttrModel->getUserInfo($uid);
	        $broadcastResult += $userInfo;
	        /*
	        $return[] = array(
	            'broadcast' => 1,
	            'data' => $broadcastResult
	        );
	     		*/
			$returnResult['coinBalance'] = $userAttrModel->getAttrByUid($uid, 'coin_balance');
			$returnResult['authTime'] = $authTime;
			$returnResult['endTime'] = $endTime;
	        // 廣播
	        /*
	        $broadcastResult = array(
	            'cmd' => 'BApplySingerGuard',
	            'uid' => $uid,
	            'singerUid' => $singerUid
	        );
	        */
	        $return[] = array(
	            'broadcast' => 0,
	            'data' => $returnResult
	        );
	        $return[] = array(
	            'broadcast' => 1,
	            'data' => $broadcastResult
	        );
	
	
	        $returnResult2 = array(
	            'cmd' => 'RFollowSinger',
	            'result' => 0
	        );
	        $charismaModel = new CharismaModel();
	        // 增加主播直播间内秀币统计值
	        $money_const = $price;
	        $moneyFinal = $charismaModel->AddSingerMoneyCount($singerUid, $money_const);
	        $money_nt = array();
	        $money_nt['cmd'] = 'BSingerAttrMoneyUpdate';
	        
	        $money_nt['uid'] = $singerUid;
	        $money_nt['sid'] = $sid;
	        $money_nt['moneyFinal'] = (int)$moneyFinal;
	        $money_nt['moneyDelta'] = (int)$money_const;
	        
	        $return[] = array
	        (
	            'broadcast' => 1, //全直播间
	            'data' => $money_nt,
	        );
	        // dump money_nt.
	        LogApi::logProcess('money_nt:'.json_encode($money_nt));
	        {	// 全局广播
	        	$giftInfo = array(
                            'receiver' => $singerUid,
                            'receiverNick' => $singerNick,
                            'vip' => $userInfo['vip'],
                            'richManLevel' => $userInfo['richManLevel'],
                            'richManTitle' => $userInfo['richManTitle'],
                            'richManStart' => $userInfo['richManStart'],
                            'sender' => $uid,
						    'senderNick' => $userInfo['nick'],
						    'type' => 0,
						    'id' => 0,
						    'icon' => 0,
						    'resource' => '',
						    'gift_name' => '开通守护',
						    'ts' => $authTime,
						    'num' => 1,
                            'sid' => $sid
                        );
	        	$return[] = array(
                    'broadcast' => 3,
                    'data' => array(
                        'cmd' => 'BBroadcast',
                        'gift' => $giftInfo
                    )
	        	);
            	$giftPersistDisplayTool = new GiftPersistDisplayTool();
            	$giftPersistDisplayTool->putAllPlatformGiftInfo($giftInfo);
	        }
	        //$userAttrModel = new UserAttributeModel();
	        do{
				if($userAttrModel->isFollow($uid, $singerUid))
				{
			        $userAttrModel->getDbMain()->query("COMMIT", false);
					return $return;
				}
			    if ($userAttrModel->getFollowNumber($uid) > 100) {
		        	$userAttrModel->getDbMain()->query("COMMIT", false);
		        	break;
				}
			    if (!empty($singerUid) && $uid != $singerUid) {
			        $userAttrModel->follow($uid, $singerUid);
			    } else {
			        $userAttrModel->getDbMain()->query("COMMIT", false);
					break;
				}
		        $return[] = array(
		            'broadcast' => 0,
		            'data' => $returnResult2
		        );
		        // 廣播
		        $broadcastResult2 = array(
		            'cmd' => 'BFollowSinger',
		            'singerUid' => $singerUid,
		            'fansNumber' => $userAttrModel->getFansNumber($singerUid)
		        );
		        $return[] = array(
		            'broadcast' => 1,
		            'data' => $broadcastResult2
		        );
		        $userAttrModel->getDbMain()->query("COMMIT", false);
	        }while(false);
        }catch(Exception $e){
       		$userAttrModel->getDbMain()->query("ROLLBACK", false);
       		throw $e;
        }
        return $return;
    }

    /**
     * 歌手通過守護申請
     */
    public static function acceptGuardApply($params)
    {
        $returnResult = array(
            'cmd' => 'RAcceptGuardApply',
            'result' => 0
        );
        $id = $params['id']; // 申請訂單的id
        $returnResult['id'] = $id;
        $uid = $params['uid'];
        $sid = $params['sid'];
        // 訂單改狀態：1等待審核 -> 2通過
        $applyModel = new SingerGuardApplyModel();
        $applyRow = $applyModel->updateApplyStatus($id, 2);
        if (!$applyRow) {
            $returnResult['result'] = 153; // 訂單不存在或已審核通過
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // 加入守護列表，延長守護時間
        $guardModel = new SingerGuardModel();
        $guardModel->addGuardRecord($applyRow);
        // 給主播分成秀點
        $userPoint = $applyRow['price'] * Config.getGaurdConsumeShowPointScale();
        $userAttrModel = new UserAttributeModel();
        $userAttrModel->addPoint($applyRow['singer_uid'], $userPoint);
        $userAttrModel->updateMonthPoint($userPoint, $applyRow['singer_uid']);
        $experience = $applyRow['price'] * 10;
        $userAttrModel->addExperienceByUid($applyRow['singer_uid'], $experience);
        // 給群分成秀點
        $sessPoint = $applyRow['price'] * 0.1;
        $sessAttrModel = new SessionAttributeModel();
        $sessAttrModel->addPoint($applyRow['sid'], $sessPoint);
        // 廣播
        $closeLevel = 1;
        $closeValue = $guardModel->getCloseValue($applyRow['uid'], $applyRow['singer_uid']);
        if ($closeValue !== false) {
            $closeLevel = $guardModel->getCloseLevel($closeValue);
        }
	$authTime = time();
	$endTime = time() + $applyRow['duration'] * 2592000;
        $userInfoModel = new UserInfoModel();
        $broadcastResult = array(
            'cmd' => 'BAcceptGuardApply',
            'singerUid' => $applyRow['singer_uid'],
            'singerNick' => $userInfoModel->getNickName($applyRow['singer_uid']),
            'closeLevel' => $closeLevel,
	    'authTime' => $authTime,
	    'endTime' => $endTime
        );
        $userInfo = $userAttrModel->getUserInfo($applyRow['uid']);
        $broadcastResult += $userInfo;
        // 返回數據
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        $return[] = array(
            'broadcast' => 1,
            'data' => $broadcastResult
        );
        return $return;
    }

    /**
     * 獲取我的申請及對我的申請的接口，返回兩組數據
     */
    public static function getGuardApplyList($params)
    {
        $returnResult = array(
            'cmd' => 'RGetGuardApplyList',
            'result' => 0
        );
        $uid = $params['uid'];
        $applyModel = new SingerGuardApplyModel();
        $guardModel = new SingerGuardModel();
        $returnResult += array(
            'myApply' => $applyModel->getApplyListInfo($uid),
            'applyForMe' => $applyModel->getApplyListInfo($uid, true),
            'myGuard' => $guardModel->getGuardListInfo($uid),
            'guardForMe' => $guardModel->getGuardListInfo($uid, true)
        );
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    /**
     * 獲取麥上歌手的守護uid列表
     */
    public static function getSingerGuard($params)
    {
        $returnResult = array(
            'cmd' => 'RGetSingerGuard',
            'result' => 0
        );
        $singerUid = $params['uid_onmic'];
        $guardModel = new SingerGuardModel();
        $returnResult += array(
            'list' => $guardModel->getGuardListInfo($singerUid, true),
            'info' => array(
                'singerNick' => $params['receiver']
            )
        );
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function getLevelInfo($params)
    {
        $result = array(
            'cmd' => 'RGetLevelInfo'
        );
        $uid = $params['uid'];
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $userInfo = $userAttrModel->getUserInfo($uid);
        $userInfo['coinBalance'] = $userAttr['coin_balance'];
        $userInfo['pointBalance'] = $userAttr['point_balance'];
        $result = $result + $userInfo;
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function getFansRank($params)
    {
        $result = array(
            'cmd' => 'RGetFansRank'
        );
        $uid = $params['uid'];
        $singerUid = $params['uid_onmic'];
        $rankModel = new RankingModel();
        $result['fansRank'] = $rankModel->getFansRank($singerUid);
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function getVipChair($params)
    {
        $result = array(
            'cmd' => 'RGetVipChair'
        );
        $uid = $params['uid'];
        $singerUid = $params['uid_onmic'];
        $rankModel = new RankingModel();
        $result['vipChair'] = $rankModel->getVipChair($singerUid);
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function getBadgeWall($params)
    {
        $result = array(
            'cmd' => 'RGetBadgeWall'
        );
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }
    
    public static function getUserDetailInfoList($params){
        $result = array(
            'cmd' => 'RGetUserDetailInfoList'
        );
        
        
        $jsonStr = json_encode($params);
        //file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "getUserDetailInfoList json:". $jsonStr ."\n", FILE_APPEND);
        
        $uid = $params['uid'];
        $sid = $params['sid'];
        
        $total = $params['total'];
        $uidcount = $params['count'];
        $start = $params['start'];
        
        $result['uid'] = $uid;
        $result['sid'] = $sid;
        
        $result['total'] = $total;
        $result['count'] = $uidcount;
        $result['start'] = $start;
        
        $userinfolist = array();
        // 
        $userAttrModel = new UserAttributeModel();
        
        $useridlist = $params['useridlist'];
        //file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "getUserDetailInfoList useridlist:". $useridlist ."\n", FILE_APPEND);
        foreach ($useridlist as $userid){
        	//file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "getUserDetailInfoList userid:". $userid ."\n", FILE_APPEND);
        	$userAttr = array();
        	if($userid >= 1000000000){
        		// 游客
        		$userAttr['uid'] = $userid;
                $userAttr['richManLevel'] = 0;
        	}else{
				$userAttr = $userAttrModel->getUserInfo($userid);
				if(empty($userAttr)){
        			$userAttr['uid'] = $userid;
	                $userAttr['richManLevel'] = 0;
				}
        	}
			$userinfolist[] = $userAttr;
        	//file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "getUserDetailInfoList userid:". $userid . ",nick:". $userAttr['nick'] ."\n", FILE_APPEND);
    	}
        $result['userdetailinfolist'] = $userinfolist;
        
        $return[] = array(
                'broadcast' => 0,
                'data' => $result
        );
        //file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "getUserDetailInfoList count:". $uidcount ."\n", FILE_APPEND);
        // 返回结果
        return $return;
    }
    public static function singerPlayStart($params){
        LogApi::logProcess('开始singerPlayStart::****************'.json_encode($params));
        $result = array(
            'cmd' => 'RSingerPlayStart'
        );
        $return = array();
        $uid = $params['uid'];
        $userAttrModel = new UserAttributeModel();
        if($userAttrModel->resetSingerChannelPoint($uid)){
            $result['result'] = 0;
        }else{
        	$result['result'] = 1;
        }
        
        $channelLiveModel = new ChannelLiveModel();
//         $channelLiveModel->clearUserCountCache($sid);
        $channelLiveModel->startPlayer(&$return, $params);
        
        $channelLiveModel->initSingerHotPoint($uid);
        //主播推送
        $channelLiveModel->pushWeb($uid);
        
        $taskModel = new TaskModel();
        $taskModel->startShowDayTask($uid);
        $taskModel->startShowMainTask($uid);
        
        
        //触发机器人说话的主播开播流程
        {
            $sid = intval($params["sid"]);
            $rtm = new robot_talk_model();
            $rtm->on_singer_comein_room($sid,$uid);
        }
        
        $return[] = array(
            'broadcast' => 0,
            'data' => $result
        );

        return $return;
    }
    
    public static function singerLeave($params){
        LogApi::logProcess('begin singerLeave::****************'.json_encode($params));
        $return = array();
        
        $uid = empty($params['uid']) ? 0 :(int)$params['uid'];
        $sid = empty($params['sid']) ? 0 :(int)$params['sid'];
        $uid_onmic = isset($params['uid_onmic']) ? (int)$params['uid_onmic'] : 0;
        

        $channelLiveModel = new ChannelLiveModel();
        $channelLiveModel->clear_hot_value($uid);
        
        if ($uid_onmic !=0 && $uid != $uid_onmic) {
        	LogApi::logProcess("singerLeave Illegal uid:$uid uid_onmic:$uid_onmic");
        	return;
        }
        
        $session = $channelLiveModel->getSessionInfo($sid);
        if(!empty($session)){
            $singerid = (int)$session['owner'];
            if($uid != $singerid){
                $uid = $singerid;
            }
        }
        LogApi::logProcess('singerLeave 1');
        
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        LogApi::logProcess('singerLeave 2');
        //LogApi::logProcess('userLeave::****************user:'.json_encode($user));
        /*
        $robot = (int)$user['is_robot'];
        
        if($robot){
            return $return;
        } */
        
        LogApi::logProcess('singerLeave 3');
        
        $result = array();
        
        $nickname = $user['nick'];
        //获得阳光数
        $charismaModel = new CharismaModel();
        $sunCount = $charismaModel->getAnchorSun($uid);
        
        LogApi::logProcess('singerLeave 4');
        
        //获得本场主播关注值
        $fans = $channelLiveModel->getLocalFans($sid);
        
        LogApi::logProcess('singerLeave 5');
        
        //获得本场主播直播时长
        $liveInfo = $channelLiveModel->getLocalLiveInfo($sid, $uid);
        $totalUserNum = $liveInfo['user_count'];
        // 如果未到刷新时间则直接使用客户端上传的数据作为依据.
        $totalUserNum = 0 == $totalUserNum ? $params['usercount'] : $totalUserNum;
        LogApi::logProcess('singerLeave 6');
        //清理直播间状态
        $channelLiveModel->clearRoomStatus($uid, $sid);
        
        $channelLiveModel->stopPlayer(&$return, $sid, $uid);

        // 获取金币统计值
        $moneyCount = $charismaModel->GetSingerMoneyCount($uid);
        
        $result = array(
            'cmd' => 'BSingerLeaveChannel',
            'totalRecvPrice' => (int)$sunCount,
            'newFans' => (int)$fans,
            'totalUserNum' => (int)$totalUserNum,
            'onlineTime' => (int)$liveInfo['time_len'],
            'total_money_num' => (int)$moneyCount,
            'photo' => (string)$user['photo'],
        );
        //如果主播正在游戏中或者发起状态，则需要对当前游戏记录做处理
       
        $result['nickname'] = $nickname;
        $result['uid'] = $uid;
        $return = array();
        $data = GameApi::dealExceptionOver($uid,&$return);
        $result['gameData'] = $data;
        
        
        // 清理电锯游戏状态
        // TODO: 将猜猜，骰子，电锯这些游戏状态的清除封装到game_manager中
        game_saw_api::saw_game_on_singer_leave($uid);
        
        $return[] = array
        (
            'broadcast' => 10,
            'data' => $result
        );
        
        // 红包退还处理
        RedPacketApi::singerLeaveEvent($sid);
        //触发机器人说话的主播离场流程(主播信息,房间号)
        {
            $rtm = new robot_talk_model();
            $rtm->on_singer_leave_room($sid,$uid);
        }
        //触发连麦功能，主播离场事件
        {
            
        }
        
        
        LogApi::logProcess("singerLeave::************uid:$uid return:".json_encode($return));
        
        return $return;
    }
    
    public static function userLeave($params){
        LogApi::logProcess('begin userLeave::****************'.json_encode($params));
        $return = array();
        
        if(empty($params['uid']) || empty($params['sid'])){
            LogApi::logProcess('userLeave:: uid or sid is null!!!');
            return $return;
        }
        
        $uid = (int)$params['uid'];
        $sid = (int)$params['sid'];
        
        LogApi::logProcess('userLeave 1');
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        
        LogApi::logProcess('userLeave 2');
        
        $robot = (int)$user['is_robot'];

        if($robot){
            return $return;
        }
        LogApi::logProcess('userLeave 3');
        $singerUid = 0;
        $channelLiveModel = new ChannelLiveModel();
        if(empty($params['singerid'])){
            $session = $channelLiveModel->getSessionInfo($sid);
            if(!empty($session)){
                $singerUid = (int)$session['owner'];
            }
        }else{
            $singerUid = (int)$params['singerid'];
        }
        
        LogApi::logProcess('userLeave 4');
        
        if($singerUid == $uid){//???????????有个问题，当非正常结束时，这是可以的，但正常结束时，客户端发一次，c++服务器在removeuser里又发一次，相当于执行了两次主播结束
            LogApi::logProcess("userLeave:: singerUid == uid, id:$uid");
            return $return;
        }
        LogApi::logProcess('userLeave 5');

        $result = array();
        $nickname = $user['nick'];
        $result = array(
            'cmd' => 'BUserLeaveChannel'
        );
        
        $isUnionGuard = $channelLiveModel->isUnionGuard($singerUid, $uid);
        LogApi::logProcess('userLeave 6 isUnionGuard:'.$isUnionGuard);
        if($isUnionGuard){
            LogApi::logProcess('userLeave 7, union_id:'.$user['union_id']);
            //更新主播所在帮会信息
            $userInfo->updateUnionLiveTime($sid, $user['union_id'], $singerUid, $uid);
        }
        
        //更新用户在线时长
        if($channelLiveModel->stopWatchRoom(&$return, $sid, $uid));
        {
        	$model_uattr = new UserAttributeModel();
        	$uattr = $model_uattr->getAttrByUid($uid);
        	$model_glory = new GloryModel();
        	$ret_glory = $model_glory->onUserLeave($sid, $uid, $uattr['consume_level']);
        	if (!empty($ret_glory)) {
        		$return[] = $ret_glory;
        	}
        }
        
        //任务数据
        $return[] = array(
            'broadcast' => 5,
            'data' => array(
                'uid' => (int)$singerUid,
                'target_type' => 12,//为主播间在线人数
                'num' => -1,
                'extra_param' => 0
            )
        );
       
        $result['nickname'] = $nickname;
        $result['uid'] = $uid;
        $return[] = array(
            'broadcast' => 1,
            'data' => $result
        );
        
        LogApi::logProcess("userLeave::************uid:$uid ".json_encode($return));
        
        return $return;
    }
    
    public static function isNewerByUattrinfo($uattr, $uinfo)
    {
    	$new = 0;
    	
    	$sys_parameter = new SysParametersModel();
    	$newer_active_level = $sys_parameter->GetSysParameters(205, 'parm1');
    	$newer_time = $sys_parameter->GetSysParameters(206, 'parm1');
    	
    	if (!empty($newer_active_level) && !empty($newer_time)) {
    		if ($uattr['active_level'] < $newer_active_level && (time() - strtotime($uinfo['signup_time'])) < $newer_time*60*60) {
    			$new = 1;
    		}
    	}
    	
    	return $new;
    }
    
    public static function isNewerByUid($uid)
    {
    	$model_uattr = new UserAttributeModel();
    	$model_uinfo = new UserInfoModel();
    	$uattr = $model_uattr->getAttrByUid($uid);
    	$uinfo = $model_uinfo->getInfoById($uid);
    	
    	return UserApi::isNewerByUattrinfo($uattr, $uinfo);
    }
}

?>
