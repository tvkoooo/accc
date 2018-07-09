package taskModule

import (
	"encoding/json"
	"github.com/golang/glog"
	"BuriedPointSvr/app/model"
	"BuriedPointSvr/common/publicInfo"
	"BuriedPointSvr/app/taskHandle/commonHandle"
	"fmt"
	"BuriedPointSvr/common/util"
)

//跑环任务事件
type taskModule_loop_event  struct {
	EventName   string           //事件名
	EventTime   int              //事件时间
	Task_id     int              //任务记录号（具体任务实例id）
	Log_type    int              //日志记录类型（跑环: 1. 任务产生 3. 任务完成 4. 任务领奖 5.任务刷新（换一换））
	T_id        int              //任务id（任务id）
	T_type      int              //任务类型
	Target_type int              //目标任务类型	
	User_id     int              //用户id
	User_nick   string           //用户昵称
	H_cur_num   int              //阳光跑环开启当前环数
	L_cur_num   int              //阳光跑环开启当前轮数	
    AwardItems publicInfo.AwardItems  //奖励信息	
}
//帮会个人任务事件
type taskModule_gangMan_event  struct {
	EventName   string           //事件名
	EventTime   int              //事件时间
	Task_id     int              //任务记录号（具体任务实例id） 
	Log_type    int              //日志记录类型（帮会个人:  3. 任务完成）	
	T_id        int              //任务id（任务id）
	T_type      int              //任务类型
	Target_type int              //目标任务类型
	Union_id    int              //帮会id
	Union_nick  string           //帮会昵称	
	Union_level int              //帮会当前等级
	User_id     int              //用户id
	User_nick   string           //用户昵称
}
//帮会集体任务事件
type taskModule_gangCommon_event  struct {
	EventName      string           //事件名
	EventTime      int              //事件时间
	Task_id        int              //任务记录号（具体任务实例id） 
	Log_type       int              //日志记录类型（帮会集体:  3. 任务完成）
	T_id           int              //任务id（任务id）
	T_type         int              //任务类型
	Target_type    int              //目标任务类型
	Union_id       int              //帮会id
	Union_nick     string           //帮会昵称	
	Union_level    int              //帮会当前等级
    Union_key_num  int              //帮会此刻钥匙数量
}
//帮会星级任务事件
type taskModule_gangStar_event  struct {
	EventName         string           //事件名
	EventTime         int              //事件时间
	Task_id           int              //任务记录号（具体任务实例id） 
	Log_type          int              //日志记录类型（帮会星级:  3. 任务完成）
	T_id              int              //任务id（任务id）
	T_type            int              //任务类型
	Target_type       int              //目标任务类型
	Union_id          int              //帮会id
	Union_nick        string           //帮会昵称	
	Union_level       int              //帮会当前等级
    Union_star_num    int              //获得本颗星后帮会的星星数量
    Union_star_level  int              //获得本次任务后的帮会星级	
}

//主播任务事件
type taskModule_singer_event  struct {
	EventName   string           //事件名
	EventTime   int              //事件时间
	Task_id     int              //任务记录号（具体任务实例id）
	Log_type    int              //日志记录类型（1任务产生时；2任务开启时；3任务完成时；4任务领取奖励时；6任务放弃时）
	T_id        int              //任务id（任务id）
	T_type      int              //任务类型
	Target_type int              //目标任务类型	
	Singer_id   int              //主播id
	Singer_nick string           //主播昵称
	Singer_sid  int              //主播sid
    AwardItems  publicInfo.AwardItems  //奖励信息	
}

//挖宝奖励领取事件
type digTreasure_getAward_event struct{
	EventName   string           //事件名
	GetAwardTime   int           //	领取奖励时间
	Uid int                      //	用户id
	Leaf int                     //	获得叶子数
	Sun int                      //	获得阳光数
	Debris int                   //	获得爱心数
	Gold int                     //获得金币数
}




//跑环任务事件处理
func TaskModule_loop_event_handle(eventData string){
	loop_event := taskModule_loop_event{}
	//解析json
	json.Unmarshal([]byte(eventData), &loop_event)
	if(0 == loop_event.User_id){//说明解析的数据有问题
	
		glog.Error("TaskModule_loop_event_handle Unmarshal failed! eventData: ", eventData)
		return
	}

	//拼接taskChangeLog信息
	taskChangeLogInfo := make(map[string]interface{})
	uniqueID := model.EventUniqueIDProducer(loop_event.User_id, loop_event.EventName)
	{
		taskChangeLogInfo["uniqueID"]              = uniqueID
		taskChangeLogInfo["eventTime"]             = loop_event.EventTime
		taskChangeLogInfo["taskId"]               = loop_event.Task_id
		taskChangeLogInfo["logType"]              = loop_event.Log_type
		taskChangeLogInfo["tId"]                  = loop_event.T_id
		taskChangeLogInfo["userId"]               = loop_event.User_id
		taskChangeLogInfo["userNick"]             = loop_event.User_nick
		taskChangeLogInfo["hCurNum"]             = loop_event.H_cur_num
		taskChangeLogInfo["lCurNum"]             = loop_event.L_cur_num
		taskChangeLogInfo["taskLoopFinishTime"]    = 0	
		taskChangeLogInfo["taskLoopRewardTime"]    = 0	
		taskChangeLogInfo["updataNum"]             = 0			
	    taskChangeLogInfo["updataNumUseItem"]     = 0			
		
		if 3 == loop_event.Log_type {
			taskChangeLogInfo["taskLoopFinishTime"]    = loop_event.EventTime 
		}		
		
		if 5 == loop_event.Log_type {
			taskChangeLogInfo["taskLoopRewardTime"]    = loop_event.EventTime
			taskChangeLogInfo["updataNum"]             = 1
			//循环遍历数组，查看是否有使用刷新卡			
			for _, v := range loop_event.AwardItems.AwardItems {
				if 32 == v.Goods_type {
				taskChangeLogInfo["updataNumUseItem"] = 1
				break
				}
            }		
		}
		
	}
	commonHandle.SendEventHandleResult(&taskChangeLogInfo, "tj_task_loop_log", loop_event.User_id)
    //处理奖励变化
	if 4 == loop_event.Log_type || 5 == loop_event.Log_type {
		if 0!= len(loop_event.AwardItems.AwardItems){
			commonHandle.AwardsChangeHandle(loop_event.EventTime, uniqueID, loop_event.EventName, loop_event.User_id, loop_event.AwardItems)
		}
	}

}


//帮会个人任务完成事件
func TaskModule_gangMan_event_handle(eventData string){
	gangMan_event := taskModule_gangMan_event{}
	//解析json
	json.Unmarshal([]byte(eventData), &gangMan_event)
	if(0 == gangMan_event.User_id){//说明解析的数据有问题
	
		glog.Error("TaskModule_gangMan_event_handle Unmarshal failed! eventData: ", eventData)
		return
	}

	//拼接taskChangeLog信息
	taskChangeLogInfo := make(map[string]interface{})
	{
		taskChangeLogInfo["uniqueID"]              = model.EventUniqueIDProducer(gangMan_event.User_id, gangMan_event.EventName)
		taskChangeLogInfo["eventTime"]             = gangMan_event.EventTime		
		taskChangeLogInfo["taskId"]                = gangMan_event.Task_id
		taskChangeLogInfo["logType"]               = gangMan_event.Log_type		
		taskChangeLogInfo["tId"]                   = gangMan_event.T_id
		taskChangeLogInfo["unionId"]               = gangMan_event.Union_id
		taskChangeLogInfo["unionNick"]             = gangMan_event.Union_nick
		taskChangeLogInfo["unionLevel"]            = gangMan_event.Union_level
		taskChangeLogInfo["userId"]                = gangMan_event.User_id
		taskChangeLogInfo["userNick"]              = gangMan_event.User_nick
		
	}
	commonHandle.SendEventHandleResult(&taskChangeLogInfo, "tj_task_gang_man_log", gangMan_event.User_id)
}

//帮会集体任务完成事件
func TaskModule_gangCommon_event_handle(eventData string){
	gangCommon_event := taskModule_gangCommon_event{}
	//解析json
	json.Unmarshal([]byte(eventData), &gangCommon_event)
	if(0 == gangCommon_event.Union_id){//说明解析的数据有问题
	
		glog.Error("TaskModule_gangCommon_event_handle Unmarshal failed! eventData: ", eventData)
		return
	}

	//拼接taskChangeLog信息
	taskChangeLogInfo := make(map[string]interface{})
	{
		taskChangeLogInfo["uniqueID"]              = model.EventUniqueIDProducer(gangCommon_event.Union_id, gangCommon_event.EventName)
		taskChangeLogInfo["eventTime"]             = gangCommon_event.EventTime		
		taskChangeLogInfo["taskId"]                = gangCommon_event.Task_id
		taskChangeLogInfo["logType"]               = gangCommon_event.Log_type			
		taskChangeLogInfo["tId"]                   = gangCommon_event.T_id
		taskChangeLogInfo["unionId"]               = gangCommon_event.Union_id
		taskChangeLogInfo["unionNick"]             = gangCommon_event.Union_nick
		taskChangeLogInfo["unionLevel"]            = gangCommon_event.Union_level
		taskChangeLogInfo["unionkeyNum"]           = gangCommon_event.Union_key_num
		
	}
	commonHandle.SendEventHandleResult(&taskChangeLogInfo, "tj_task_gang_common_log", 0)
}


//帮会星级任务完成事件
func TaskModule_gangStar_event_handle(eventData string){
	gangStar_event := taskModule_gangStar_event{}
	//解析json
	json.Unmarshal([]byte(eventData), &gangStar_event)
	if(0 == gangStar_event.Union_id){//说明解析的数据有问题
	
		glog.Error("TaskModule_gangStar_event_handle Unmarshal failed! eventData: ", eventData)
		return
	}

	//拼接taskChangeLog信息
	taskChangeLogInfo := make(map[string]interface{})
	{
		taskChangeLogInfo["uniqueID"]              = model.EventUniqueIDProducer(gangStar_event.Union_id, gangStar_event.EventName)
		taskChangeLogInfo["eventTime"]             = gangStar_event.EventTime		
		taskChangeLogInfo["taskId"]                = gangStar_event.Task_id
		taskChangeLogInfo["logType"]               = gangStar_event.Log_type		
		taskChangeLogInfo["tId"]                   = gangStar_event.T_id
		taskChangeLogInfo["unionId"]               = gangStar_event.Union_id
		taskChangeLogInfo["unionNick"]             = gangStar_event.Union_nick
		taskChangeLogInfo["unionLevel"]            = gangStar_event.Union_level
		taskChangeLogInfo["unionStarNum"]          = gangStar_event.Union_star_num
		taskChangeLogInfo["unionStarLevel"]        = gangStar_event.Union_star_level
	}
	commonHandle.SendEventHandleResult(&taskChangeLogInfo, "tj_task_gang_star_log", 0)
}


//主播任务任务完成事件
func TaskModule_singer_event_handle(eventData string){
	singer_event := taskModule_singer_event{}
	//解析json
	json.Unmarshal([]byte(eventData), &singer_event)
	if(0 == singer_event.Singer_id){//说明解析的数据有问题
	
		glog.Error("TaskModule_singer_event_handle Unmarshal failed! eventData: ", eventData)
		return
	}

	//拼接taskChangeLog信息
	taskChangeLogInfo := make(map[string]interface{})
	uniqueID := model.EventUniqueIDProducer(singer_event.Singer_id, singer_event.EventName)
	{
		taskChangeLogInfo["uniqueID"]              = uniqueID
		taskChangeLogInfo["eventTime"]             = singer_event.EventTime		
		taskChangeLogInfo["taskId"]                = singer_event.Task_id
		taskChangeLogInfo["logType"]               = singer_event.Log_type		
		taskChangeLogInfo["tId"]                   = singer_event.T_id
		taskChangeLogInfo["singerId"]              = singer_event.Singer_id
		taskChangeLogInfo["singerNick"]            = singer_event.Singer_nick
		taskChangeLogInfo["singerSid"]             = singer_event.Singer_sid
	}
	commonHandle.SendEventHandleResult(&taskChangeLogInfo, "tj_task_singer_log", singer_event.Singer_id)
	//处理奖励变化
	if 4 == singer_event.Log_type {
		if 0!= len(singer_event.AwardItems.AwardItems){
			commonHandle.AwardsChangeHandle(singer_event.EventTime, uniqueID, singer_event.EventName, singer_event.Singer_id, singer_event.AwardItems)
		}
	}
}


//挖宝奖励领取事件
func DigTreasure_getAward_event(eventData string){
	eventInfo := digTreasure_getAward_event{}
	//解析json
	json.Unmarshal([]byte(eventData), &eventInfo)
	if(0 == eventInfo.Uid){//说明解析的数据有问题
		glog.Error("Unmarshal failed! eventData: ", eventData)
		return
	}

	//拼接changeLog信息
	changeLogInfo := make(map[string]interface{})
	uniqueID := model.EventUniqueIDProducer(eventInfo.Uid, eventInfo.EventName)
	{
		changeLogInfo["uniqueID"] = uniqueID
		changeLogInfo["type"] = 3
		changeLogInfo["uid"] = eventInfo.Uid
		changeLogInfo["eventTime"] = eventInfo.GetAwardTime

		//得到用户基本信息
		result, err := model.GetUInfo(eventInfo.Uid)
		if nil != err {
			glog.Error("model.GetUInfol failed! eventData: ", eventData)
			return
		}
		changeLogInfo["userNick"] = (*result)["nick"]

		roleType, err := model.GetUserIdentity(eventInfo.Uid)
		if nil != err{
			glog.Error("model.GetUserIdentity failed! eventData: ", eventData)
			return
		}
		changeLogInfo["roleType"] =roleType

		//获取挖宝id
		{
			sql := fmt.Sprintf("SELECT id, star_num FROM card.`user_dig_treasure_info` WHERE uid = %v ORDER BY id DESC LIMIT 1", eventInfo.Uid)
			ret, err := model.QueryOneDataAndConvertToMap(sql, util.GetMysqlDB())
			if nil != err{
				glog.Error("model.QueryOneDataAndConvertToMap failed! sql: ", sql, eventData)
				return
			}

			digID, _ := model.ConvertDataToInt((*ret)["id"])
			star_num, _ := model.ConvertDataToInt((*ret)["star_num"])

			changeLogInfo["digTreasureID"] = fmt.Sprintf("%v", digID)
			changeLogInfo["hardLevel"] = star_num
		}
	}

	//发送结果
	commonHandle.SendEventHandleResult(&changeLogInfo, "tj_user_dig_treasure_log", eventInfo.Uid)


	//处理奖励信息
	{
		items := publicInfo.AwardItems{}

		//处理叶子
		if(0 < eventInfo.Leaf){
			item := publicInfo.AwardItemInfo{}
			item.Num = eventInfo.Leaf
			item.Goods_type = 10
			item.ItemID = 8
			item.Type = 0
			items.AwardItems = append(items.AwardItems, item)
		}

		//处理阳光
		if(0 < eventInfo.Sun){
			item := publicInfo.AwardItemInfo{}
			item.Num = eventInfo.Sun
			item.Goods_type = 16
			item.ItemID = 11
			item.Type = 0
			items.AwardItems = append(items.AwardItems, item)
		}

		//处理爱心
		if(0 < eventInfo.Debris){
			item := publicInfo.AwardItemInfo{}
			item.Num = eventInfo.Debris
			item.Goods_type = 10
			item.ItemID = 9
			item.Type = 0
			items.AwardItems = append(items.AwardItems, item)
		}

		//处理金币
		if(0 < eventInfo.Gold){
			item := publicInfo.AwardItemInfo{}
			item.Num = eventInfo.Gold
			item.Goods_type = 15
			item.ItemID = 10
			item.Type = 0
			items.AwardItems = append(items.AwardItems, item)
		}

		//记录奖励
		commonHandle.AwardsChangeHandle(eventInfo.GetAwardTime, uniqueID, eventInfo.EventName, eventInfo.Uid, items)
	}

}






