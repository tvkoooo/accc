#include "pthread_2.h"

//#include <mutex>





//线程1
void * lizhi_2_pthr_1(void *p)
{
	int i = 0;
	struct lizhi1_2_pthr_s *a;
	a=(struct lizhi1_2_pthr_s *)p;

	//int mmb=0;

	for (i = 0; i < 10; i++)
	{
		char kkkk[100];
		lizhi1_2_pthr_s_lock(a);


		//pthread_mutex_lock(&a->mymutex1);
		lizhi1_2_pthr_s_f1(a);
		a->intin++;
		lizhi1_2_pthr_s_unlock(a);

		lizhi1_2_pthr_s_lock(a);
		sprintf(kkkk,"全局变量Quanju_lizhi1_pth ： %d\n",a->intin);
		fputs(kkkk,a->fpin);
		lizhi1_2_pthr_s_unlock(a);
		//
		lizhi1_2_pthr_s_f1(a);
		//
		lizhi1_2_pthr_s_lock(a);
		fputs(kkkk,a->fpin);
		lizhi1_2_pthr_s_unlock(a);
		//
		lizhi1_2_pthr_s_lock(a);
		fputs(kkkk,a->fpin);
		lizhi1_2_pthr_s_unlock(a);

		//fputs("\n",a->fpin);
		//fprintf(a->fpin,"全局变量Quanju_lizhi1_pth ： %d\n",a->intin++);
		//pthread_mutex_unlock(&a->mymutex1);
		//lizhi1_2_pthr_s_unlock(a);

		//printf("this is 线程1         全局变量值：  %d\n",Quanju_lizhi1_pth);
		//printf("this is 线程1         局部变量值：  %d\n",mmb);


	}

	//do 
	//{
	//	mmb++;
	//		Sleep(1000);

	//		Sleep(1000);
	//	

	//} while (flagctrlc==0);
	return 0;

	//do 
	//{
	//	printf("deng dai \n");

	//	Sleep(500);

	//} while (flagctrlc==0);
	//int i=0;
	//for (i=0;i<=9;i++)
	//{
	//	printf("this is lizhi1_pthread_1\n");
	//	if (i==5)
	//	{
	//		pthread_exit(0);
	//		Sleep(1);
	//	}
	//}
}
////线程2
//void * lizhi_2_pthr_2(void *)
//{
//	do 
//	{
//
//		Quanju_lizhi1_pth++;
//		printf("this is 线程2          全局变量值：  %d\n",Quanju_lizhi1_pth);
//		Sleep(1000);
//
//
//	} while (flagctrlc==0);
//	return 0;
//
//	//int i=0;
//	//for (i=0;i<3;i++)
//	//
//	//	printf("this is lizhi1_pthread_2\n");
// //       pthread_exit(0);		
//	//
//
//}






void pthread_lizhi1_2_test()
{
	pthread_t id_1;
	pthread_t id_2;
	pthread_t id_3;
	////////////////////////////////////////////////////	
	int ret;
	//int Quanju_lizhi1_pth=0;

	struct lizhi1_2_pthr_s pth_2_a;
	////////////////////////////////////////////////////
	FILE * fpin;
	//char chtest;
	char filenametest[20]="worizhi.txt";
	if ((fpin=fopen(filenametest,"wb"))==NULL)
	{
		printf("cannot open\n");
		return;
	}
	////////////////////////////////////////////////////
    lizhi1_2_pthr_s_init(&pth_2_a);
	//////////////////////////////////////////////////////
	//pth_a.intin=Quanju_lizhi1_pth;
	pth_2_a.fpin=fpin;

	/////////////////////////////////////////////////////////


	//创建线程一
	ret=pthread_create(&id_1,NULL,lizhi_2_pthr_1,&pth_2_a);
	if (ret!=0)
	{
		printf("creat pthread error!\n");
		//return -1;
	}
	//创建线程二
	ret=pthread_create(&id_2,NULL,lizhi_2_pthr_1,&pth_2_a);
	if (ret!=0)
	{
		printf("creat pthread error!\n");
		//return -1;
	}
	//创建线程二
	ret=pthread_create(&id_3,NULL,lizhi_2_pthr_1,&pth_2_a);
	if (ret!=0)
	{
		printf("creat pthread error!\n");
		//return -1;
	}

	pthread_join(id_1,NULL);
	pthread_join(id_2,NULL);
	pthread_join(id_3,NULL);

	////////////////////////////////////////////////////
    lizhi1_2_pthr_s_destroy(&pth_2_a);
	////////////////////////////////////////////////////

	fclose(fpin);
	//return 0;
}

void lizhi1_2_pthr_s_init(struct lizhi1_2_pthr_s *p)
{

	p->intin=0;
	p->fpin=NULL;
	pthread_mutex_init(&p->mymutex1,NULL);
}
void lizhi1_2_pthr_s_destroy(struct lizhi1_2_pthr_s *p)
{

	p->intin=0;
	p->fpin=NULL;
	pthread_mutex_destroy(&p->mymutex1);
}
void lizhi1_2_pthr_s_lock(struct lizhi1_2_pthr_s*p)
{

	pthread_mutex_lock(&p->all);
}
void lizhi1_2_pthr_s_unlock(struct lizhi1_2_pthr_s*p)
{
	pthread_mutex_unlock(&p->all);

}

void lizhi1_2_pthr_s_f1(struct lizhi1_2_pthr_s*p)
{
	pthread_mutex_lock(&p->mymutex1);
	p->intin++;
	pthread_mutex_unlock(&p->mymutex1);

}
void lizhi1_2_pthr_s_f2(struct lizhi1_2_pthr_s*p)
{
	pthread_mutex_lock(&p->mymutex1);
;	p->intin++;
	pthread_mutex_unlock(&p->mymutex1);
}
