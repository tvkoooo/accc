package taskHandle

import (
	"BuriedPointSvr/control/taskDispatch"
	"BuriedPointSvr/app/taskHandle/lordModule"
	"BuriedPointSvr/app/taskHandle/climbTower"
	"BuriedPointSvr/app/taskHandle/channelLive"
	"BuriedPointSvr/app/taskHandle/game/dice"
	"BuriedPointSvr/app/taskHandle/taskModule"
	"BuriedPointSvr/app/taskHandle/awardsAndExpHandle"
	"BuriedPointSvr/app/taskHandle/linkcallModule"
)

var g_taskDispatchInfo *taskDispatch.TaskDispatchInfo

func Init(taskDispatchInfo *taskDispatch.TaskDispatchInfo){
	g_taskDispatchInfo = taskDispatchInfo
}


//所有其他模块任务处理回调在此注册
func RegisterTaskFun(){
	//对外接口，处理物品、金币、经验变化
	g_taskDispatchInfo.RegisterCallFun("user_awardItems_product", awardsAndExpHandle.HandleAwardsInfoToBuriedPointInfo)
	g_taskDispatchInfo.RegisterCallFun("user_exp_change", awardsAndExpHandle.HandleExpChangeToBuriedPointInfo)

	//擂主模块相关
	g_taskDispatchInfo.RegisterCallFun("lordModule_userUp_event", lordModule.LordModule_userUp_event_handle)
	g_taskDispatchInfo.RegisterCallFun("lordModule_userDown_event", lordModule.LordModule_userDown_event_handle)
	g_taskDispatchInfo.RegisterCallFun("lordModule_userGetAward_event", lordModule.LordModule_userGetAward_event_handle)

	//爬塔模块相关
	g_taskDispatchInfo.RegisterCallFun("climbTower_battleResult_event", climbTower.HandleClimbTowerResultEvent)
	g_taskDispatchInfo.RegisterCallFun("climbTower_getAwards_event", climbTower.HandleClimbToweGetAwardsEvent)
	g_taskDispatchInfo.RegisterCallFun("climbTower_rankUserGetAwards_event", climbTower.HandleClimbToweGetRankAwardsEvent)

	//直播间相关事件
	g_taskDispatchInfo.RegisterCallFun("channel_liveChat_event", channelLive.Channel_liveChat_event_handle)
	g_taskDispatchInfo.RegisterCallFun("channel_singerOpenLive_event", channelLive.Channel_singerOpenLive_event_handle)
	g_taskDispatchInfo.RegisterCallFun("channel_singerCloseLive_event", channelLive.Channel_singerCloseLive_event_handle)
	g_taskDispatchInfo.RegisterCallFun("channel_userEnter_event", channelLive.Channel_userEnter_event_handle)
	g_taskDispatchInfo.RegisterCallFun("channel_userLeave_event", channelLive.Channel_userLeave_event_handle)
	g_taskDispatchInfo.RegisterCallFun("channel_sendGift_event", channelLive.Channel_sendGift_event_handle)
	g_taskDispatchInfo.RegisterCallFun("channel_userSunCreate_event", channelLive.Channel_userSunCreate_event_handle)
	g_taskDispatchInfo.RegisterCallFun("channel_singerSunCreate_event", channelLive.Channel_singerSunCreate_event_handle)
	g_taskDispatchInfo.RegisterCallFun("channel_room_authority_event", channelLive.Channel_room_authority_event_handle)

	//小游戏相关事件
	g_taskDispatchInfo.RegisterCallFun("channel_gameDice_singerLanch_event", dice.Channel_gameDice_singerLanch_event_handle)
	g_taskDispatchInfo.RegisterCallFun("channel_gameDice_userEnroll_event", dice.Channel_gameDice_userEnroll_event_handle)
	g_taskDispatchInfo.RegisterCallFun("channel_gameDice_userDo_event", dice.Channel_gameDice_userDo_event_handle)
	g_taskDispatchInfo.RegisterCallFun("channel_gameDice_over_event", dice.Channel_gameDice_over_event_handle)
	
	//任务模块相关
	g_taskDispatchInfo.RegisterCallFun("taskModule_loop_event", taskModule.TaskModule_loop_event_handle)
	g_taskDispatchInfo.RegisterCallFun("taskModule_gangMan_event", taskModule.TaskModule_gangMan_event_handle)
	g_taskDispatchInfo.RegisterCallFun("taskModule_gangCommon_event", taskModule.TaskModule_gangCommon_event_handle)
	g_taskDispatchInfo.RegisterCallFun("taskModule_gangStar_event", taskModule.TaskModule_gangStar_event_handle)
	g_taskDispatchInfo.RegisterCallFun("taskModule_singer_event", taskModule.TaskModule_singer_event_handle)
	g_taskDispatchInfo.RegisterCallFun("digTreasure_getAward_event", taskModule.DigTreasure_getAward_event)

	//连麦模块相关
	g_taskDispatchInfo.RegisterCallFun("linkcallModule_linkcall_event", linkcallModule.LinkcallModule_linkcall_event_handle)


}









