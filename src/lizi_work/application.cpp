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
	//	// ����ʧ��
	//	sox::log(Error, "TaskRequest::onTaskRequest parse error json :", p->data.data());
	//	return request.end();
	//}
	//std::string cmd;

	//if(!jscore::getJsonString(root, "cmd", &cmd)){
	//	sox::log(Error, "TaskRequest::onTaskRequest no cmd json :", p->data.data());
	//	return request.end();
	//}
	//if(0 == cmd.compare("PGetTaskList")){
	//	// ��ȡ�û������б�
	//	if(!impl->doPGetTaskList(p, tcp, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetSingerTaskList")){
	//	// ��ȡ�����ɿ����������б�
	//	if(!doPGetSingerTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetSingerTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetTaskDetailInfo")){
	//	// ��ȡ������ϸ��Ϣ
	//	if(!doPGetTaskDetailInfo(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetTaskDetailInfo is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PStartTask")){
	//	// ������������
	//	if(!doPStartTask(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PStartTask is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PDropTask")){
	//	// ������������
	//	if(!doPDropTask(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PDropTask is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("POpenBox")){
	//	// ������������
	//	if(!doPOpenBox(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd POpenBox is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangActiveTaskList")){
	//	// ��ȡ����Ծ����
	//	if(!doPGetGangActiveTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangActiveTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangGiftTaskList")){
	//	// ��ȡ�����������
	//	if(!doPGetGangGiftTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangGiftTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangContestTaskList")){
	//	// ��ȡ�����̨����
	//	if(!doPGetGangContestTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangContestTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangStartTaskList")){
	//	// ��ȡ����Ǽ�����
	//	if(!doGetGangStartTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd GetGangStartTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetRandomTaskList")){
	//	// ��ȡ�������
	//	if(!doGetRandomTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetRandomTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetReward")){
	//	// ��ȡ����������
	//	if(!doGetReward(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetReward is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetLoopReward")){
	//	// ��ȡ�ܻ�������
	//	if(!doGetLoopReward(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetLoopReward is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetLoopTaskList")){
	//	// ��ȡ�û��ܻ������б�
	//	if(!doPGetLoopTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetLoopTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetTreasureTaskList")){
	//	// ��ȡ�û��������ڱ������б�
	//	if(!doGetTreasureTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetTreasureTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetTreasureReward")){
	//	// ��ȡ�ڱ�������
	//	if(!doGetTreasureReward(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetTreasureReward is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PRefurbishLooptask")){
	//	// ˢ���ܻ�����
	//	if(!doRefurbishLooptask(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PRefurbishLooptask is error");
	//	}
	//	return request.end();
	//}else if (0 == cmd.compare("PTriggerChatTask")){
	//	// ����һ��Ⱥ��˿����������¼
	//	if(!doTriggerChatTask(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PTriggerChatTask is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangManActiveTaskList")){
	//	// ��ȡ�����˻�Ծ����
	//	if(!doPGetGangManActiveTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangManActiveTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangManGiftTaskList")){
	//	// ��ȡ��������������
	//	if(!doPGetGangManGiftTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangManGiftTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetGangManContestTaskList")){
	//	// ��ȡ��������̨����
	//	if(!doPGetGangManContestTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetGangContestTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetMasterAndApprenticeTaskList")){
	//	// ��ȡʦͽ�����б�
	//	if(!doPGetMasterAndApprenticeTaskList(p, conn, root)){
	//		sox::log(Error, "TaskRequest::onTaskRequest cmd PGetMasterAndApprenticeTaskList is error");
	//	}
	//	return request.end();
	//}else if(0 == cmd.compare("PGetNewManConfigTaskList")){
	//	// ��ȡ�������������б�
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
