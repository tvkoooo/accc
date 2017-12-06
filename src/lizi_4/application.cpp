#include "application.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "pthread.h"
#include "platform_config.h"





void application_init(struct application* p)
{
	savestore_init(&p->s1);

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

	savestore_destroy(&p->s1);
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
	savestore_start(&p->s1);
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
	savestore_interrupt(&p->s1);
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
	savestore_shutdown(&p->s1);
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
	savestore_join(&p->s1);
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
