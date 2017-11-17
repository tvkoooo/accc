#include <windows.h>
#include <tchar.h>
#include <stdio.h>
#include <exception>
#include <time.h>
#include <vld.h>
#include <string.h>
#include "pthread.h"
#include <mutex>
#include "pthread_huidiao.h"
void pthread_huidiao()
{

}
//int *flag_huidiao_1;
//int clocktime;
//pthread_huidiao_type funpthread_hui;
//pthread_mutex_t *all;
void lizhi1_huidiao_init(struct pthread_huidiao_1 *p)
{
	p->clocktime=0;
	p->flag_appact=0;
	p->funpthread_hui=pthread_huidiao;
    pthread_mutex_init(&p->all,NULL);
	//
	p->funpthread_hui=lizhi1_huidiao_f1;
}
void lizhi1_huidiao_destroy(struct pthread_huidiao_1*p)
{
	p->clocktime=0;
	p->flag_appact=0;
	p->funpthread_hui=pthread_huidiao;
	 pthread_mutex_destroy(&p->all);
}

void lizhi1_huidiao_start(struct pthread_huidiao_1* p)
{
	int ret;
	ret=pthread_create(&p->poll_thread,NULL,lizhi1_huidiao_pthr_1,p);
	if (ret!=0)
	{
		printf("creat pthread error!\n");
	}

}
void lizhi1_huidiao_shutdown(struct pthread_huidiao_1* p)
{
	p->flag_appact=1;
}

void lizhi1_huidiao_lock(struct pthread_huidiao_1*p)
{
	pthread_mutex_lock(&p->all);
}
void lizhi1_huidiao_unlock(struct pthread_huidiao_1*p)
{
	pthread_mutex_unlock(&p->all);
}


void lizhi1_huidiao_join(struct pthread_huidiao_1*p)
{
		pthread_join(p->poll_thread,NULL);
}

//  mymutex1
void lizhi1_huidiao_f1()
{
		printf("huidiao chengong!\n");
}
////  mymutex1
//void lizhi1_huidiao_f2(struct pthread_huidiao_1*p)
//{
//
//}
////void fun_huidiao_update(struct pthread_huidiao_1*p)
//{
//
//}

void * lizhi1_huidiao_pthr_1(void * p)
{		

	struct pthread_huidiao_1 *a;
	a=(struct pthread_huidiao_1 *)p;
	do 
	{
		lizhi1_huidiao_lock(a);
		a->clocktime++;
		lizhi1_huidiao_unlock(a);
		Sleep(1000);
		a->funpthread_hui();
	} while (a->flag_appact==0);
	return 0;
}
//void lizhi1_huidiao_pthr_2()
//{
//
//}

void lizhi1_huidiao_test()
{
//////////////////////////////////////////////////////////////////
	struct pthread_huidiao_1 pth_clk;
	//pthread_t id_2;
	//pthread_t id_3;
////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////	
	//FILE * fpin;
	////char chtest;
	//char filenametest[20]="worizhi.txt";
	//if ((fpin=fopen(filenametest,"wb"))==NULL)
	//{
	//	printf("cannot open\n");
	//	exit(0);
	//}
///////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
	lizhi1_huidiao_init(&pth_clk);
///////////////////////////////////////////////////////////////////

    lizhi1_huidiao_start(&pth_clk);
//////////////////////////////////////////////////////////////////
	////创建线程二
	//ret=pthread_create(&id_2,NULL,lizhi_2_pthr_1,&pth_clk);
	//if (ret!=0)
	//{
	//	printf("creat pthread error!\n");
	//	//return -1;
	//}
	////创建线程三
	//ret=pthread_create(&id_3,NULL,lizhi_2_pthr_1,&pth_clk);
	//if (ret!=0)
	//{
	//	printf("creat pthread error!\n");
	//	//return -1;
	//}
///////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////

	lizhi1_huidiao_join(&pth_clk);

	////////////////////////////////////////////////////
	lizhi1_huidiao_destroy(&pth_clk);
	////////////////////////////////////////////////////
	//fclose(fpin);
	//return 0;
}