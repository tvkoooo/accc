#include "application.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "pthread.h"
#include "platform_config.h"

#define xunhuancishu_default 100

static void nininin(struct robot_contrl* obj)
{

	if (xunhuancishu_default <= obj->xunhuanceshu)
	{
		robot_contrl_shutdown(obj);
		printf("robot_contrl_shutdown fff \n");
	}
	else
	{
		uint32_t uAddCnt1 = obj->uaddtotalnum / obj->chushijiqiren;
		uint32_t uAddCnt2 = obj->uaddtotalnum % obj->chushijiqiren;
		//log(Info, "SessionConn::StartStreamAddUserThread: cmd=RSC_LIVE_START, sid=%u, uAddCnt=%u", sid, uAddTotalNum);
		if(obj->chushijiqiren == obj->xunhuanceshu)
		{
			uAddCnt1 += uAddCnt2;
		}
		//RobotMgr::instance().addChannelGroupRobots(sid, uAddCnt, 5);
		//RobotMgr* mgr = (RobotMgr*)(obj->u);
		//mgr->addChannelGroupRobots(sid, uAddCnt, 5);

		printf("uAddCnt1:%d  fff \n",uAddCnt1);
	}
	obj->xunhuanceshu++;
}


void application_init(struct application* p)
{

	robot_contrl_map_init(&p->ma1);

	robot_contrl_map_fuzhi(&p->ma1,&nininin,NULL);

	////savestore_init(&p->s1);

	//wangluo_fw_init(&p->fw1);
	//p->flag_fw1=0;
	////builder_init(&p->b1);
	//wangluo_kh_init(&p->kh1);
	//p->flag_kh1=0;

	//p->argc=0;
	//p->argv=NULL;

}
void application_destroy(struct application* p)
{

	robot_contrl_map_destroy(&p->ma1);
	////savestore_destroy(&p->s1);

	//wangluo_fw_destroy(&p->fw1);
	//p->flag_fw1=0;
	////builder_destroy(&p->b1);
	//wangluo_kh_destroy(&p->kh1);
	//p->flag_kh1=0;

	//p->argc=0;
	//p->argv=NULL;
}

void application_fuzhi(struct application* p,int argc,char **argv)
{

	//p->argc=argc;
	//p->argv=argv;
	//printf("The number of argc:%d\n",argc);
	//for(int i=0;i<argc;i++)
	//{
	//	printf("input argv:%s\n",p->argv[i]);
	//}

	//if (1==argc)
	//{
	//	p->flag_fw1=p->flag_kh1=1;
	//}
	//if (2==argc)
	//{
	//	if (0==strcmp(p->argv[1],"0"))
	//	{
	//		p->flag_fw1=1;
	//	}
	//	if (0==strcmp(p->argv[1],"1"))
	//	{
	//		p->flag_kh1=1;
	//	}
	//}
}

void application_start(struct application* p)
{	
	robot_contrl_map_kaibo(&p->ma1,101);
	robot_contrl_map_kaibo(&p->ma1,101);
	robot_contrl_map_start(&p->ma1);
	//fun_redis();
	//fun_mysql_test1();
	////savestore_start(&p->s1);

	//if (1==p->flag_fw1)
	//{
	//	wangluo_fw_start(&p->fw1);

	//}
	////builder_start(&p->b1);
	//if (1==p->flag_kh1)
	//{
	//	wangluo_kh_start(&p->kh1);
	//}

}

void application_interrupt(struct application* p)
{
	robot_contrl_map_interrupt(&p->ma1);

	////savestore_interrupt(&p->s1);

	//if (1==p->flag_fw1)
	//{
	//wangluo_fw_interrupt(&p->fw1);
	//}
	////builder_interrupt(&p->b1);
	//if (1==p->flag_kh1)
	//{
	//wangluo_kh_interrupt(&p->kh1);
	//}
}
void application_shutdown(struct application* p)
{
	robot_contrl_map_shutdown(&p->ma1);

	////savestore_shutdown(&p->s1);

	//printf("Ctrl+c ½øÐÐÖÐ¶Ï\n");
	//if (1==p->flag_fw1)
	//{
	//wangluo_fw_shutdown(&p->fw1);
	//}
	////builder_shutdown(&p->b1);
	//if (1==p->flag_kh1)
	//{
	//wangluo_kh_shutdown(&p->kh1);
	//}
}
void application_join(struct application* p)
{
	robot_contrl_map_join(&p->ma1);

	////savestore_join(&p->s1);

	//if (1==p->flag_fw1)
	//{
	//wangluo_fw_join(&p->fw1);
	//}
	////builder_join(&p->b1);
	//if (1==p->flag_kh1)
	//{
	//wangluo_kh_join(&p->kh1);
	//}
}
