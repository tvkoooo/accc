#include "lizhi1_all.h"
#include <mutex>

int Quanju_lizhi1_pth=0;

//线程1
void * lizhi_pthr_1(void *)
{

	for (int j=0; j < 3; j++)
	{
		
		Quanju_lizhi1_pth++;
	    printf("this is 线程1 %d\n",Quanju_lizhi1_pth);
		Sleep(200);
	}
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
	return 0;
}
//线程2
void * lizhi_pthr_2(void *)
{

	for ( int i = 0; i < 12; i++)
	{
		Quanju_lizhi1_pth++;	
	    printf("this is 线程2 %d\n",Quanju_lizhi1_pth);
		Sleep(200);
	}
	//int i=0;
	//for (i=0;i<3;i++)
	//
	//	printf("this is lizhi1_pthread_2\n");
 //       pthread_exit(0);		
	//
		return 0;
}



void pthread_lizhi1_test()
{
	pthread_t id_1,id_2;
	int ret;
//创建线程一
	ret=pthread_create(&id_1,NULL,lizhi_pthr_1,NULL);
	if (ret!=0)
	{
		printf("creat pthread error!\n");
		//return -1;
	}
//创建线程二
	ret=pthread_create(&id_2,NULL,lizhi_pthr_2,NULL);
	if (ret!=0)
	{
		printf("creat pthread error!\n");
		//return -1;
	}

do 
{
	printf("%d \n",Quanju_lizhi1_pth);
   //等待线程结束
	pthread_join(id_1,NULL);
	pthread_join(id_2,NULL);

} while (Quanju_lizhi1_pth<10);

	//return 0;
}
