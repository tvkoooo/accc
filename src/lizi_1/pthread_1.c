#include "pthread_1.h"
#include <stdio.h>
#include <pthread.h>
//#include <mutex>

extern int flagctrlc;

struct lizhi1_pthr_s
{
	FILE * fpin;
	int  intin;
};

//�߳�1
void * lizhi_pthr_1(void *p)
{
	int i = 0;
	struct lizhi1_pthr_s *a;
	a=(struct lizhi1_pthr_s *)p;

	//int mmb=0;

	for (i = 0; i < 10000; i++)
	{
		char kkkk[100];
		a->intin++;
		sprintf(kkkk,"ȫ�ֱ���Quanju_lizhi1_pth �� %d\n",a->intin);
		fputs(kkkk,a->fpin);
		//fputs("\n",a->fpin);
		 //fprintf(a->fpin,"ȫ�ֱ���Quanju_lizhi1_pth �� %d\n",a->intin++);

		//printf("this is �߳�1         ȫ�ֱ���ֵ��  %d\n",Quanju_lizhi1_pth);
		//printf("this is �߳�1         �ֲ�����ֵ��  %d\n",mmb);


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
////�߳�2
//void * lizhi_pthr_2(void *)
//{
//	do 
//	{
//
//		Quanju_lizhi1_pth++;
//		printf("this is �߳�2          ȫ�ֱ���ֵ��  %d\n",Quanju_lizhi1_pth);
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






void pthread_lizhi1_test()
{
	pthread_t id_1;
	pthread_t id_2;
	pthread_t id_3;
////////////////////////////////////////////////////	
	int ret;
	//int Quanju_lizhi1_pth=0;

	struct lizhi1_pthr_s pth_a;
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
	pth_a.intin=0;
	pth_a.fpin=NULL;
//////////////////////////////////////////////////////
	//pth_a.intin=Quanju_lizhi1_pth;
	pth_a.fpin=fpin;
/////////////////////////////////////////////////////////


//�����߳�һ
	ret=pthread_create(&id_1,NULL,lizhi_pthr_1,&pth_a);
	if (ret!=0)
	{
		printf("creat pthread error!\n");
		//return -1;
	}
//�����̶߳�
	ret=pthread_create(&id_2,NULL,lizhi_pthr_1,&pth_a);
	if (ret!=0)
	{
		printf("creat pthread error!\n");
		//return -1;
	}
//�����̶߳�
	ret=pthread_create(&id_3,NULL,lizhi_pthr_1,&pth_a);
	if (ret!=0)
	{
		printf("creat pthread error!\n");
		//return -1;
	}

	pthread_join(id_1,NULL);
	pthread_join(id_2,NULL);
	pthread_join(id_3,NULL);

	////////////////////////////////////////////////////
	pth_a.intin=0;
	pth_a.fpin=NULL;
	////////////////////////////////////////////////////

	fclose(fpin);
	//return 0;
}
