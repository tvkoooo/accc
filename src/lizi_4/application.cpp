#include "application.h"

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "pthread.h"
#include "wangluo.h"
#include "platform_config.h"




void application_init(struct application* p)
{

}
void application_destroy(struct application* p)
{

}

void application_start(struct application* p)
{	
	if(0)
	{
		int aa;
		aa=sizeof(struct sockaddr);
		cout<<"struct sockaddr\t"<<aa<<"\t";
		aa=sizeof(struct sockaddr_in);
		cout<<"struct sockaddr_in\t"<<aa<<"\t";
		aa=sizeof(struct sockaddr_in6);
		cout<<"struct sockaddr_in6\t"<<aa<<"\t";
		aa=sizeof(struct sockaddr_storage);
		cout<<"struct sockaddr_storage\t"<<aa<<endl;
	}
	//appaction_init(&g_appaction);
	//appaction_start(&g_appaction);
	//appaction_join(&g_appaction);
	//appaction_destroy(&g_appaction);
	if(1)
	{
		pthread_t id_1;
		pthread_t id_2;
		int ret;

		//创建线程一
		ret=pthread_create(&id_1,NULL,lizhi4_wangluo_3,NULL);
		if (ret!=0)
		{
			printf("creat pthread error!\n");
			//return -1;
		}
		//创建线程二
		ret=pthread_create(&id_2,NULL,lizhi4_wangluo_kehu,NULL);
		if (ret!=0)
		{
			printf("creat pthread error!\n");
			//return -1;
		}

		pthread_join(id_1,NULL);
		pthread_join(id_2,NULL);
	}

}
void application_interrupt(struct application* p)
{

	
}
void application_shutdown(struct application* p)
{


}
void application_join(struct application* p)
{

}