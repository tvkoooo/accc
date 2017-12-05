#include "application.h"

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "pthread.h"
#include "wangluo.h"
#include "platform_config.h"



//
//void wangluo_init(struct wangluo* p)
//{
//	p->argc=0;
//	p->argv=NULL;
//	p->wangluoshut=0;
//	p->ctrl_idf=0;
//	p->ctrl_idk=0;
//}
//void wangluo_destroy(struct wangluo* p)
//{
//	p->argc=0;
//	p->argv=NULL;
//	p->wangluoshut=0;
//	p->ctrl_idf=0;
//	p->ctrl_idk=0;
//}
//
//void wangluo_fuzhi(struct wangluo* p,int argc,char **argv)
//{
//	p->argc=argc;
//	p->argv=argv;
//	printf("int argc:%d\n",argc);
//	for(int i=0;i<argc;i++)
//	{
//		printf("%s\n",argv[i]);
//	}
//	if (1==p->argc)
//	{
//		p->ctrl_idf=1;
//		p->ctrl_idk=1;
//	}
//	if (2==p->argc)
//	{
//		int b0,b1;
//		b0=strcmp(p->argv[1],"0");
//		b1=strcmp(p->argv[1],"1");
//		if (0==b0)
//		{
//			p->ctrl_idf=1;
//			p->ctrl_idk=0;
//		}
//		if (0==b1)
//		{
//			p->ctrl_idf=0;
//			p->ctrl_idk=1;
//		}
//	}
//}
//
//void wangluo_start(struct wangluo* p)
//{	
//	int ret;
//	p->wangluoshut=1;
//	if (1==p->ctrl_idf)
//	{
//		//创建线程一
//		ret=pthread_create(&p->id_f,NULL,lizhi4_wangluo_fuwu,p);
//		if (ret!=0)
//		{
//			printf("creat pthread error!\n");
//			//return -1;
//		}
//	}
//	if (1==p->ctrl_idk)
//	{
//		//创建线程二
//		ret=pthread_create(&p->id_k,NULL,lizhi4_wangluo_kehu,p);
//		if (ret!=0)
//		{
//			printf("creat pthread error!\n");
//			//return -1;
//		}
//	}
//}
//
//void wangluo_wait(struct wangluo* p)
//{
//
//}
//
//
//void wangluo_interrupt(struct wangluo* p)
//{
//	
//}
//void wangluo_shutdown(struct wangluo* p)
//{
//	printf("\n程序被打断,请继续上步操作正常关闭退出\n");
//	p->wangluoshut=0;
//	//shutdown(p->slisten,2);
//}
//void wangluo_join(struct wangluo* p)
//{
//	if (1==p->ctrl_idf)
//	{
//		pthread_join(p->id_f,NULL);
//	}
//	if (1==p->ctrl_idk)
//	{
//		pthread_join(p->id_k,NULL);
//	}
//}
