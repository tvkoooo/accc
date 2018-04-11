#include "application.h"
#include "core\mm_os_context.h"

//#include "test3.h"
static void __static_taskrequest_from_linkd_callback1(void* obj, void* u, struct mm_packet* rs_pack);
static void __static_taskrequest_from_linkd_callback2(void* obj, void* u, struct mm_packet* rs_pack);

void application_init(struct application* p)
{
	taskrequest_from_linkd_init(&p->f1);
	p->task_cmd["B000"]=&application::B000;
	p->task_cmd["C000"]=&application::C000;

	std::string cmd = "B000";
	typedef std::map<std::string,task_cmd_callback> map_type;
	map_type::iterator it = p->task_cmd.find(cmd);
	if (it != p->task_cmd.end())
	{
		task_cmd_callback mmmm= it->second;
		(p->*mmmm)(NULL,NULL,"");
	}

}
void application_destroy(struct application* p)
{
	taskrequest_from_linkd_destroy(&p->f1);
}

void application_start(struct application* p)
{	
	taskrequest_from_linkd_start(&p->f1);
}
void application_interrupt(struct application* p)
{
	taskrequest_from_linkd_interrupt(&p->f1);
}
void application_shutdown(struct application* p)
{
	taskrequest_from_linkd_shutdown(&p->f1);
}
void application_join(struct application* p)
{
	taskrequest_from_linkd_join(&p->f1);
}

void application_fuzhi(struct application* p,int argc,char **argv)
{
	mm_uint32_t instance_number = 11;
	if (2 == argc)
	{		
		instance_number = (mm_uint32_t)atof(argv[1]);
	}
	else
	{
		instance_number = 110 ;
	}
	 taskrequest_from_linkd_set_instance_number(&p->f1,instance_number);
	 taskrequest_from_linkd_set_zk_host_port(&p->f1,"127.0.0.1:10300,");
	 taskrequest_from_linkd_set_task_host_port(&p->f1,"0.0.0.0-55000(1)");
	 mm_uint32_t message1_number = (401 << 8) | 70;
	 //mm_uint32_t message2_number = (402 << 8) | 70;
	 mm_mailbox_assign_context(&p->f1.mailbox_0,p);
	 mm_mailbox_assign_callback(&p->f1.mailbox_0,message1_number,&__static_taskrequest_from_linkd_callback1);
	 //mm_mailbox_assign_callback(&p->f1.mailbox_0,message2_number,&__static_taskrequest_from_linkd_callback2);

}

static void __static_taskrequest_from_linkd_callback1(void* obj, void* u, struct mm_packet* rs_pack)
{
	//TaskRequest* impl = (TaskRequest*)u;
	//struct mm_tcp* tcp = (struct mm_tcp*)obj;
	//Json::Value root;
	//Json::Reader reader;
	//if(!reader.parse(p->data, root)) {
	//	// 解析失败
	//	sox::log(Error, "TaskRequest::onTaskRequest parse error json :", p->data.data());
	//	return request.end();
	//}
	//std::string cmd;

	//if(!jscore::getJsonString(root, "cmd", &cmd)){
	//	sox::log(Error, "TaskRequest::onTaskRequest no cmd json :", p->data.data());
	//	return request.end();
	//}
	//if(0 == cmd.compare("PGetTaskList")){
	//	// 获取用户任务列表
	//	if(!impl->doPGetTaskList(p, tcp, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetSingerTaskList")){
	//	// 获取主播可开启的任务列表
	//	if(!doPGetSingerTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetSingerTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetTaskDetailInfo")){
	//	// 获取任务详细信息
	//	if(!doPGetTaskDetailInfo(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetTaskDetailInfo is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PStartTask")){
	//	// 主播开启任务
	//	if(!doPStartTask(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PStartTask is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PDropTask")){
	//	// 主播放弃任务
	//	if(!doPDropTask(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PDropTask is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("POpenBox")){
	//	// 主播开启宝箱
	//	if(!doPOpenBox(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd POpenBox is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangActiveTaskList")){
	//	// 获取帮会活跃任务
	//	if(!doPGetGangActiveTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangActiveTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangGiftTaskList")){
	//	// 获取帮会礼物任务
	//	if(!doPGetGangGiftTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangGiftTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangContestTaskList")){
	//	// 获取帮会擂台任务
	//	if(!doPGetGangContestTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangContestTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangStartTaskList")){
	//	// 获取帮会星级任务
	//	if(!doGetGangStartTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd GetGangStartTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetRandomTaskList")){
	//	// 获取随机任务
	//	if(!doGetRandomTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetRandomTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetReward")){
	//	// 领取主播任务奖励
	//	if(!doGetReward(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetReward is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetLoopReward")){
	//	// 领取跑环任务奖励
	//	if(!doGetLoopReward(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetLoopReward is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetLoopTaskList")){
	//	// 获取用户跑环任务列表
	//	if(!doPGetLoopTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetLoopTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetTreasureTaskList")){
	//	// 获取用户或主播挖宝任务列表
	//	if(!doGetTreasureTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetTreasureTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetTreasureReward")){
	//	// 领取挖宝任务奖励
	//	if(!doGetTreasureReward(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetTreasureReward is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PRefurbishLooptask")){
	//	// 刷新跑环任务
	//	if(!doRefurbishLooptask(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PRefurbishLooptask is error");
	//	}
	//	return request.end();
	//}else if (0 == cmd.compare("PTriggerChatTask")){
	//	// 触发一次群粉丝聊天的任务记录
	//	if(!doTriggerChatTask(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PTriggerChatTask is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangManActiveTaskList")){
	//	// 获取帮会个人活跃任务
	//	if(!doPGetGangManActiveTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangManActiveTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangManGiftTaskList")){
	//	// 获取帮会个人礼物任务
	//	if(!doPGetGangManGiftTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangManGiftTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangManContestTaskList")){
	//	// 获取帮会个人擂台任务
	//	if(!doPGetGangManContestTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangContestTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetMasterAndApprenticeTaskList")){
	//	// 获取师徒任务列表
	//	if(!doPGetMasterAndApprenticeTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetMasterAndApprenticeTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetNewManConfigTaskList")){
	//	// 获取新人任务配置列表
	//	if(!doPGetNewManConfigTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetNewManConfigTaskList is error");
	//	}
	//	return request.end();
	//}
}
static void __static_taskrequest_from_linkd_callback2(void* obj, void* u, struct mm_packet* rs_pack)
{


}

bool application::B000( void*p, void *conn, const std::string& root )
{
	return true;
}

bool application::C000( void*p, void *conn, const std::string& root )
{
	return true;
}
