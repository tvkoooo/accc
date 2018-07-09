package linkcallModule

import (
	"encoding/json"
	"github.com/golang/glog"
	"BuriedPointSvr/app/model"
	"BuriedPointSvr/app/taskHandle/commonHandle"
)

//语音连麦事件
type linkcallModule_linkcall_event  struct {
	EventName       string           //事件名
	EventTime       int              //事件时间
	Linkcall_id     int              //连麦记录号（具体语音连麦产生的记录）
	Log_type        int              //日志记录类型（语音连麦（2拒绝；3结束））
	User_id         int              //用户id
	User_nick       string           //用户昵称
	Singer_id       int              //主播id
	Singer_nick     string           //主播昵称
	Singer_sid      int              //主播sid
	Success         int              //连麦结果 (0=拒绝，1=同意)
	Link_time       int              //连麦总时长
}

//语音连麦事件
func LinkcallModule_linkcall_event_handle(eventData string){
	linkcall_event := linkcallModule_linkcall_event{}
	//解析json
	json.Unmarshal([]byte(eventData), &linkcall_event)
	if(0 == linkcall_event.User_id){//说明解析的数据有问题
	
		glog.Error("LinkcallModule_linkcall_event_handle Unmarshal failed! eventData: ", eventData)
		return
	}

	//拼接taskChangeLog信息
	linkcallModuleLogInfo := make(map[string]interface{})
	{
		linkcallModuleLogInfo["uniqueID"]              = model.EventUniqueIDProducer(linkcall_event.Singer_id, linkcall_event.EventName)
		linkcallModuleLogInfo["eventTime"]             = linkcall_event.EventTime		
		linkcallModuleLogInfo["linkcallId"]            = linkcall_event.Linkcall_id
		linkcallModuleLogInfo["logType"]               = linkcall_event.Log_type		
		linkcallModuleLogInfo["userId"]                = linkcall_event.User_id
		linkcallModuleLogInfo["userNick"]              = linkcall_event.User_nick
		linkcallModuleLogInfo["singerId"]              = linkcall_event.Singer_id
		linkcallModuleLogInfo["singerNick"]            = linkcall_event.Singer_nick
		linkcallModuleLogInfo["singerSid"]             = linkcall_event.Singer_sid
		linkcallModuleLogInfo["success"]               = linkcall_event.Success
		linkcallModuleLogInfo["linkTime"]              = linkcall_event.Link_time		
	}
	commonHandle.SendEventHandleResult(&linkcallModuleLogInfo, "tj_linkcall_log", linkcall_event.User_id)

}









