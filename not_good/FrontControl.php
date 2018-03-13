<?php

class FrontControl
{

    public static function execCommand($command, $params)
    {
$startMTime = microtime(true)*1000;
        
         LogApi::logProcess('***************FrontControl::execCommand command:' . $command);
    	//file_put_contents("/data/phplog/1.log","AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"."execCommand $command=\n", FILE_APPEND);
        switch ($command) {
        	//握手：联通与客户端的连接
            case 'PHandshake':
                $result = InternalApi::handShake($params);
                break;
            case 'PLogActiveUser'://
                //活跃用户记录
                $result = InternalApi::logActiveUser($params);
                break;
            case 'PGetTimestamp':
                $result = InternalApi::getTimestamp($params);
                break;
            case 'PInitShowWidget'://初始化进入直播间后的展示组件：类似非实时数据，需要从数据库获取的
                $result = InternalApi::initShowWidget($params);
                break;
            case 'PInitEnv'://服务器初始化房间时返回给客户端的礼物列表
                $result = InternalApi::initEnv($params);
                break;
            case 'PInitUser':
                $result = InternalApi::initUser($params);
                break;
            case 'PReadNotice':
                $result = WidgetApi::readNotice($params);
                break;
            case 'PGetGiftList':
                $result = ToolApi::getGiftList($params);
                LogApi::logProcess('PGetGiftList::***************return :' . json_encode($result));
                break;
            case 'PGetShowTool':
                $result = ToolApi::getShowTools($params);
                break;
            case 'PGetToolState':
                $result = ToolApi::getToolState($params);
                break;
            case 'PGetGiftPacket'://获取包裹礼物信息
                $result = ToolApi::getToolsFromPacket($params, 1);
                break;
            case 'PGetToolPacket':
                $result = ToolApi::getToolsFromPacket($params, 2);
                break;
            case 'PAddGiftToPacket':
                $result = ToolApi::addToolsToPacket($params, 1);
            	break;
            case 'PGetRanking':
                $result = WidgetApi::getRanking($params, 'gift');
                break;
            case 'PGetSingerInfo':
                $result = UserApi::getSingerInfo($params);
                break;
            case 'PGetUserInfo':
                $result = UserApi::getUserInfo($params);
                break;
            case 'PGetUserCountdownTime'://获取用户进入直播间倒计时的剩余时间
                $result = UserApi::getUserCountdownTime($params);
                break;
            case 'PSingerLeaveChannel'://主播结束直播
                $result = UserApi::singerLeave($params);
                break;
            case 'PLeaveChannel'://用户离开房间更新在线时间
                $result = UserApi::userLeave($params);
                break;
            case 'PCreateGift'://产生礼物
                $result = UserApi::createGift($params);
                break;
            case 'PGetLevelInfo':
                $result = UserApi::getLevelInfo($params);
                break;
            case 'PSendGift'://发送礼物
                $result = ToolApi::useToolV3($params);
                break;
            case 'PSendBarrage'://发送弹幕
                $result = ToolApi::sendBarrage($params);
                LogApi::logProcess('****************PSendBarrage:' . json_encode($result));
                break;
            case 'PAtMessage'://@人指令
                $result = ToolApi::atMessage($params);
                break;
            case 'PTextChat'://公聊
                $result = ToolApi::textChat($params);
                break;
            case 'PGetSingerOpenTask'://用户进入直播间获得主播开启的任务信息
                $result = ChannelLiveApi::getSingerOpenTask($params);
                break;
            case 'PGetSunValue'://获得用户阳光值
                $result = ChannelLiveApi::GetSunValue($params);
                break;
            case 'POpenOrCloseMicro'://主播打开或关闭麦克风
                $result = ChannelLiveApi::openOrCloseMicro($params);
                break;
            case 'POpenOrCloseCamera'://主播打开或关闭摄像头
                $result = ChannelLiveApi::openOrCloseCamera($params);
                break;
            case 'PGetSingerOpenTask'://获得主播开启任务
//                 $result = ChannelLiveApi::getSingerOpenTask($params);
                break;
                
            case 'PGetSingerLaunchGame': //获取主播发起的游戏
            	$result = ChannelLiveApi::getSingerLaunchGame($params);
            	break;
            	
            case 'PCheckIsValidSinger': //验证主播有效性
            	$result = ChannelLiveApi::checkIsValidSinger($params);
            	break;
            	
            case 'PGetSingerRoomSunTask': //获取主播用户阳光
            	$result = ChannelLiveApi::GetSingerRoomSunTask($params);
            	break;
            	
            case 'PLaunchGuessGame'://主播发起你动我猜游戏
                $result = GameApi::launchGuessGame($params);
                break;
            case 'PCancelGame'://主播取消你动我猜游戏
                $result = GameApi::cancelGame($params);
                break;
            case 'PEnrollGame'://用户报名参加你动我猜游戏
                $result = GameApi::enrollGame($params);
                break;
            case 'PStartGuessGame'://主播开始游戏
                $result = GameApi::startGuessGame($params);
                break;
            case 'PDoGuessGame'://用户答题
                $result = GameApi::doGuessGame($params);
                break;
            case 'PActNextQuestion'://主播表演下一题
                $result = GameApi::actNextQuestion($params);
                break;
            case 'POverGuessGame'://结束你动我猜游戏
                $result = GameApi::overGuessGame($params);
                break;
            case 'PLaunchDiceGame'://主播发起摇骰子游戏
                $result = GameApi::launchDiceGame($params);
                break;
            case 'PEnrollDiceGame'://用户报名参加摇骰子游戏
                $result = GameApi::enrollDiceGame($params);
                break;
            case 'PCancelDiceGame'://主播取消摇骰子游戏
                $result = GameApi::cancelDiceGame($params);
                break;
            case 'PStartDiceGame'://主播开始骰子游戏
                $result = GameApi::startDiceGame($params);
                break;
            case 'PDoDiceGame'://用户开始摇骰子
                $result = GameApi::doDiceGame($params);
                break;
            case 'POverDiceGame'://主播结束摇骰子游戏
                $result = GameApi::overDiceGame($params);
                break;
            case 'PGetCharisma':
                $result = ToolApi::getCharisma($params);
                break;
            case 'PSetEffect':
                $result = ToolApi::useTool($params, 2);
                break;
            case 'PSendHeart':
                $result = WidgetApi::sendHeart($params);
                break;
            case 'PConvertHeart':
                $result = WidgetApi::convertHeart($params);
                break;
            case 'PSmashEgg':
                $result = WidgetApi::smashEgg($params);
                break;
            case 'PRefreshEgg':
                $result = WidgetApi::refreshEgg($params);
                break;
            case 'PSpeaker':
                $result = WidgetApi::speaker($params);
                break;
            case 'POpenGiftBox':
                $result = WidgetApi::openGiftBox($params);
                break;
            case 'PBuyVip':
                $result = UserApi::buyVip($params);
                break;
            case 'PBuyTool':
                $result = ToolApi::buyTool($params);
                break;
            case 'PFollowSinger': //关注
                $result = UserApi::followSinger($params);
                break;
            case 'PUnfollowSinger'://取消关注
                $result = UserApi::unfollowSinger($params);
                break;
            case 'PIsFollow':
                $result = UserApi::isFollow($params);
                break;
            case 'PCanCallFans':
                $result = UserApi::canCallFans($params);
                break;
            case 'PCallFans':
                $result = UserApi::callFans($params);
                break;
            case 'PGetActivityInfo':
                $result = ActivityApi::getActivityInfo($params);
                break;
            case 'PGetActivityDailyPacket':
                $result = ActivityApi::getActivityDailyPacket($params);
                break;
            case 'PGetTaskInfo':
                $result = WidgetApi::getTaskInfo($params);
                break;
            case 'PGetTaskReward':
                $result = WidgetApi::getTaskReward($params);
                break;
            case 'PGetRankInfo':
                $result = WidgetApi::getRankInfo($params);
                break;
            /* case 'PGetLoginPacket':
                $result = WidgetApi::getLoginPacket($params);
                break;
            case 'PSendLoginPacket':
                $result = WidgetApi::sendLoginPacket($params);
                break; */
            case 'PGetVideoSinger':
                $result = WidgetApi::getVideoSinger($params);
                break;
            case 'PGetGuardApplyInfo':
                $result = UserApi::getGuardApplyInfo($params);
                break;
            case 'PApplySingerGuard':
                $result = UserApi::applySingerGuard($params);
                break;
            case 'PAcceptGuardApply':
                $result = UserApi::acceptGuardApply($params);
                break;
            case 'PGetGuardApplyList':
                $result = UserApi::getGuardApplyList($params);
                break;
            case 'PGetSingerGuard'://获取主播守护列表
                $result = UserApi::getSingerGuard($params);
                break;
            case 'PEnterChannel'://入场广播播，客户端发往服务器（只为了直播间广播：欢迎xxx进入房间）
                $result = UserApi::enterChannel($params);
                //LogApi::logProcess('****************PEnterChannel:' . json_encode($result));
                break;
            /*case 'PGetChip':
                $result = ToolApi::getChip($params);
                break;
            case 'PMergeChip':
                $result = ToolApi::mergeChip($params);
                break;*/
            case 'PGetAds':
                $result = WidgetApi::getAds($params);
                break;
            case 'MicOff':
                $result = UserApi::micOff($params);
                break;
            case 'MicOn':
                $result = UserApi::micOn($params);
                break;
	    case 'PGetSessOwnerInfo':
		$result = UserApi::getSessOwnerInfo($params);
		break;
            /*case 'GetVideoEnable':
                $result = InternalApi::getVideoEnable($params);
                break;
            case 'SetVideoEnable':
                $result = InternalApi::setVideoEnable($params);
                break;*/
            case 'Notice':
                $result = WidgetApi::notice($params);
                break;
            case 'PGetLuckyDail':
                $result = WidgetApi::getLuckyDail($params);
                break;
            case 'PDrawLuckyDail':
                $result = WidgetApi::drawLuckyDail($params);
                break;
            case 'PGetLuckySinger':
                $result = WidgetApi::getLuckySinger($params);
                break;
            /*
            case 'PFcBuyGamePoint':
                $result = GameApi::fcBuyGamePoint($params);
                break;
            case 'PFcThrowDice':
                $result = GameApi::fcThrowDice($params);
                break;
            case 'PFcBuyGamePoint ':
                $result = GameApi::fcBuyGamePoint($params);
                break;
            case 'PFcGetChess':
                $result = GameApi::fcGetChess($params);
                break;
            */
            case 'PLuckyShake':
                $result = GameApi::luckyShake($params);
                break;
            case 'PIkalaVerify':
                $result = WidgetApi::ikalaVerify($params);
                break;
            /********************新版秀场协议********************/
            case 'PNewSendGift':
                $result = ToolApi::sendGift($params);
                break;
            case 'PGetShop':
                $result = ToolApi::getShop($params);
                break;
            case 'PGetFansRank':
                $result = UserApi::getFansRank($params);
                break;
            case 'PGetVipChair':
                $result = UserApi::getVipChair($params);
                break;
            case 'PGetStage':
                $result = ToolApi::getStage($params);
                break;
            case 'PSetStage':
                $result = ToolApi::setStage($params);
                break;
            case 'PGetBadgeWall':
                $result = UserApi::getBadgeWall($params);
                break;
            case 'PGetRankList':
                $result = ToolApi::getSidConsumeRank($params);
                break;
            case 'PGetGiftDisplayInfo':
                $result = ToolApi::getGiftDisplayInfo($params);
                break;
            case 'PGetChannalCarInfo':
                $result = UserApi::getChannelCarinfo($params);
                break;
            case 'PSendShowHeart':
                $result = WidgetApi::sendShowHeart($params);
                break;
            case 'PSetShowHeart':
                $result = WidgetApi::setShowHeart($params);
                break;
            case 'PGetShowHeart':
                $result = WidgetApi::getShowHeart($params);
                break;
            case 'PGetUserDetailInfoList'://获取用户列表
                $result = UserApi::getUserDetailInfoList($params);
            	break;
            case 'PSingerPlayStart'://主播开始直播
            	$result = UserApi::singerPlayStart($params);
            	break;
            /* case'PStartChannelLive'://主播开始直播
                $result = ChannelLiveApi::startChannelLive($params);
                break; */
//             case 'PUpdateChannelLiveInfo'://更新正在开播的直播间信息
// 				LogApi::logProcess('****************begin PUpdateChannelLiveInfo, pid='.posix_getpid());
//                 $result = ChannelLiveApi::updateChannelLiveInfo($params);
//                 LogApi::logProcess('****************end PUpdateChannelLiveInfo, pid='.posix_getpid().',result:'.json_encode($result));
//                 break;
//             case 'PUpdateChannelLiveInfoxxx'://更新直播间信息（停止开播的）
// 				LogApi::logProcess('****************begin PUpdateChannelLiveInfoxxx, pid='.posix_getpid());
//                 $result = ChannelLiveApi::updateChannelLiveInfoxxx($params);
// 				LogApi::logProcess('****************end PUpdateChannelLiveInfoxxx, pid='.posix_getpid().',result:'.json_encode($result));
//                 break;
            case 'PUpdatePlayUrl':
            	$result = ChannelLiveApi::setPlayUrl($params);
            	break;
            case 'PGetPlayUrl':
            	$result = ChannelLiveApi::getPlayUrl($params);
            	break;
            case 'PGetAllPlatformGiftSendInfo':// 获取礼物全平台广播数据(用户第一次进入直播间时会请求这个命令)
            	$result = ToolApi::getAllPlatformGiftSendInfo($params);
            	break;
            case 'PReloadRoomRankInfo':
            	$result = ToolApi::reloadRoomRankInfo($params);
            	break;
            case 'PAddRoomRandInfo':
            	$result = ToolApi::addRoomRankInfo($params);
            	break;
            case 'PUpdatePublishState':
            	$result = ChannelLiveApi::setPublishState($params);
            	break;
            case  'PGetAnchorSun'://zkay 主播阳光值获取
                $result = ToolApi::GetAnchorSun($params);
                LogApi::logProcess('****************PGetAnchorSun:' . json_encode($result));
            	break;
        	case  'PUserLaunchGame':// 用户递交发起互动游戏申请请求递交
        	    $result = GameApi::OnPUserLaunchGame($params);
        	    LogApi::logProcess('****************PUserLaunchGame:' . json_encode($result));
        	    break;
    	    case  'PSingerApplyLaunch':// 播主同意某申请请求
    	        $result = GameApi::OnPSingerApplyLaunch($params);
    	        LogApi::logProcess('****************PSingerApplyLaunch:' . json_encode($result));
    	        break;
    	        
	        case  'flag_make_rq':// 开启夺旗请求
	            $result = flag_faction_api::on_flag_make_rq($params);
	            LogApi::logProcess('****************flag_make_rq:' . json_encode($result));
	            break;
            case  'flag_details_rq':// 查询夺旗详情请求
                $result = flag_faction_api::on_flag_details_rq($params);
                LogApi::logProcess('****************flag_details_rq:' . json_encode($result));
                break;
            case  'flag_join_rq':// 加入夺旗请求
                $result = flag_faction_api::on_flag_join_rq($params);
                LogApi::logProcess('****************flag_join_rq:' . json_encode($result));
                break;
            case  'flag_exit_rq':// 退出夺旗请求
                $result = flag_faction_api::on_flag_exit_rq($params);
                LogApi::logProcess('****************flag_exit_rq:' . json_encode($result));
                break;
            case 'PSendLuckyRedPacket':
            	$result = RedPacketApi::sendLuckyRedPacket($params);
            	break;
            case 'PSendFansRedPacket':
            	$result = RedPacketApi::sendFansRedPacket($params);
            	break;
            case 'PSendShareRedPacket':
            	$result = RedPacketApi::sendShareRedPacket($params);
            	break;
            case 'PGetRedPacketList':
            	$result = RedPacketApi::getRedPacketList($params);
            	break;
            case 'PPickLuckyRedPacket':
            	$result = RedPacketApi::pickLuckyRedPacket($params);
            	break;
            case 'PPickFansRedPacket':
            	$result = RedPacketApi::pickFansRedPacket($params);
            	break;
            case 'PPickShareRedPacket':
            	$result = RedPacketApi::pickSharePacket($params);
            	break;
            case 'PGetRedPacketPickItems':
            	$result = RedPacketApi::getRedPacketPickItems($params);
            	break;
            case 'PShareSuccess':
            	$result = RedPacketApi::shareSuccess($params);
            	break;
            case 'PGetFollowerRel':
            	$result = RedPacketApi::getFollowerRel($params);
            	break;
            case 'PFansEnrollRedPacket':
            	$result = RedPacketApi::fansEnrollRedPacket($params);
            	break;
            case 'PFansEnterByRP':
            	$result = RedPacketApi::fansEnterByRedPacket($params);
            	break;
            case 'PUseRoomProp':
            	$result = ToolApi::useRoomProp($params);
            	break;
            case 'get_live_game_rq':
            	$result = game_manager_api::get_live_game_rq($params);
            	break;
            case 'get_saw_game_detail_rq':
            	$result = game_saw_api::get_saw_game_detail_rq($params);
            	break;
            case 'saw_game_attack_normal_rq':
            	$result = game_saw_api::saw_game_attack_normal_rq($params);
            	break;
            case 'saw_game_loot_prop_rq':
            	$result = game_saw_api::saw_game_loot_prop_rq($params);
            	break;
            case 'saw_game_use_prop_special_rq':
            	$result = game_saw_api::saw_game_use_prop_special_rq($params);
            	break;
            case 'saw_game_loot_prize_rq':
            	$result = game_saw_api::saw_game_loot_prize_rq($params);
            	break;
            case 'p_user_real_enter_channel_event':
            	$result = channel_api::on_p_user_real_enter_channel_event($params);
            	break;            	
            case 'p_user_real_leave_channel_event':
            	$result = channel_api::on_p_user_real_leave_channel_event($params);
            	break; 
            case 'p_channel_heartbeat_event':
            	$result = channel_api::on_p_channel_heartbeat_event($params);
            	break;
            case 'clean_game_info_rq':
            	$result = GameApi::clean_game_info_rq($params);
            	break;
            case 'PCreateStream':
            	$result = ChannelLiveApi::on_create_stream_rq($params);
            	break;
            case 'p_channel_heartbeat_30_event':
            	$result = ChannelLiveApi::on_p_channel_heartbeat_30_event($params);
            	break;
            case 'p_channel_heartbeat_60_empty_event':
            	$result = ChannelLiveApi::on_p_channel_heartbeat_60_empty_event($params);
            	break;
            case 'on_guard_received_rq':
                $result = sun_income_task_api::on_guard_received_rq($params);
                break;
            case 'on_fans_group_sun_peek_rq':
                $result = sun_income_task_api::on_fans_group_sun_peek_rq($params);
                break;
            case 'on_task_sun_award_rq':
                $result = sun_income_task_api::on_task_sun_award_rq($params);
                break;
            case 'on_sun_income_test_rq':
                $result = sun_income_task_api::on_test_rq($params);
                break;
            case 'PSendGTicketRedPacket':
                $result = RedPacketApi::sendGTicketRedPacket($params);
                break;
            case 'PPickGTicketRedPacket':
                $result = RedPacketApi::pickGTicketRedPacket($params);
                break;
            case 'user_rich_level_up_rq':
                $result = evt_deal_api::user_rich_level_up_rq($params);
                break;
            case 'user_active_level_up_rq':
                $result = evt_deal_api::user_active_level_up_rq($params);
                break;
            case 'anchor_level_up_rq':
                $result = evt_deal_api::anchor_level_up_rq($params);
                break;
            case 'anchor_sunshine_level_up_rq':
                $result = evt_deal_api::anchor_sunshine_level_up_rq($params);
                break;
            case 'linkcall_set_state_rq'://连麦:主播打开/关闭连麦功能
                $result = linkcall_api::on_linkcall_set_state_rq($params);
                break;
            case 'linkcall_apply_rq'://连麦:用户（听众）发起/取消/退出连麦                
                $result = linkcall_api::on_linkcall_apply_rq($params);
                break;
            case 'linkcall_allow_rq'://连麦:主播允许/拒绝/删除连麦
                $result = linkcall_api::on_linkcall_allow_rq($params);
                break;
            case 'linkcall_list_singer_rq'://连麦:主播查询最新申请列表
                $result = linkcall_api::on_linkcall_list_singer_rq($params);
                break;
            case 'linkcall_list_user_rq'://连麦:用户（主播/用户）查询当前最新连麦信息
                $result = linkcall_api::on_linkcall_list_user_rq($params);
                break;
                
            default:
                $result = -1;
                break;
        }
		$endMTime = microtime(true)*1000;
		$costMTime = $endMTime - $startMTime;
		LogApi::logProcess('[analysis info]  cmdName:'.$command.'  costMTime:'.$costMTime.'  params:'.json_encode($params));
        return $result;
    }
}

?>
