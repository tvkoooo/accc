#include <tchar.h>
#include <stdio.h>
#include <stdlib.h>
#include <exception>
#include "tou.h"
#include <string.h>
#include <ctime>
//#include <iostream>
extern void checkgouwo(struct gou *pgou,int *yanggou,int *goulanflag,int *goulanmax);
extern void buydog(int *maigou,int *yanggou);
extern void anpaisushe(struct gou *pgou,int *yanggou,int *maigou);
extern void palydog(int *dogover,int *maigou,struct gou *pgou,int *yanggou,int *goulanflag,int *goulanmax,struct goutalk *ppgou);
extern void kanxiaogou(struct gou *pgou,int *yanggou,int *goulanflag,int *goulanmax,struct goutalk *ppgou);
extern void gougundan(struct gou *pgou,int *yanggou,int *goulanflag,int *goulanmax,struct goutalk *ppgou);

void li3()
{
//���ݳ�ʼ��
	int cmp=0;
	int k=0;
	int i,j=1;
	int flag=0;
	int	maigou=0;
	int dogover=0;
//���ѳ�ʼ��
	int yanggou=0;
	int goulanmax=1;
	int goulanflag=0;

//�湷��ʼ��
	//char xuan='0';

	//char *biaozhun1="back";
	//char *jieshu="exit";
	//char *str="0";
	//char *strxuan0="0";
	//char *strxuan1="0";
	struct gou *pgou=NULL;
	struct gou mygou[9];
	pgou=mygou;
	struct goutalk *ppgou=NULL;
	
//��չ���
	for(i=0;i<9;i++)
	{
		pgou->gouname="0";
		pgou->gouou=quangougou;
	}

//��Ϸ��ʼ
	
	do
	{
//һ��鹷��
checkgouwo(pgou,&yanggou,&goulanflag,&goulanmax);
//����
buydog( &maigou,&yanggou);
//�����Ź���
anpaisushe(pgou,&yanggou,&maigou);
//���湷
palydog(&dogover,&maigou,pgou,&yanggou,&goulanflag,&goulanmax,ppgou);

if(dogover)
	break;
else
	palydog(&dogover,&maigou,pgou,&yanggou,&goulanflag,&goulanmax,ppgou);

    }while(1);
		
	}


//	��һ������鹷��
void checkgouwo(struct gou *pgou,int *yanggou,int *goulanflag,int *goulanmax)
{
	int i,cmp;
	for(i=1;i<10;i++)
	{
		cmp=strcmp(pgou->gouname,"0");
		if(cmp) 
			{
				printf("����%d��С��ס�ţ���%s",i,pgou->gouname);
				*yanggou++;
		        *goulanflag=i;	
	     	}
		else
		{
		printf("����%d�ǿյ�,���Է���С��");
		}
		
		if(*yanggou)
			*goulanmax=*goulanflag;
		else
			*goulanmax=1;

	}
}
//  ��һ������鹷�ѽ���

//  �ڶ�������
void buydog(int *maigou,int *yanggou)
	{
		system("cls");
	    printf("\n�����ü�ֻС���ؼ���?�����ֻ������9ֻ����\n");
	    printf("��ʾ������Ҫ����һ��������.\n�������������0\n");	
		scanf("%d",maigou);
	
		if(!((*maigou)>=0&&(*maigou<10)))
		{
	    printf("������������⣬���������룬��ȻС��������.\n");

		}
		  else
	   { 
		   if(*yanggou+*maigou>10)
		   {
			printf("������������⣬���������룬��ȻС��������.\n");
	        printf("�������������0.\n\n");
		   }
	     	else
	     	{
	     	printf("��������   %d  �����͹���\n",*maigou);
	     	
	    	}

	  	}
				
	
		*yanggou=*yanggou+*maigou;
}
//	�ڶ�����ȥ�̳��򹷽���


//	������������С������
	void anpaisushe(struct gou *pgou,int *yanggou,int *maigou)
{
    int cmp;
	if(*yanggou)
    for(int k=0;k<*maigou;k++)
    	for(int i=1;i<10;i++)
	   {
		cmp=strcmp(pgou->gouname,"0");
		if(cmp) 
			{
				printf("����%d��С��ס�ţ���%s",i,pgou->gouname);
	     	}
		else
		{
			printf("����%d�ǿյ�,���Է���С����С����ʲô����س�������");
		    scanf_s("%[^\n]",pgou->gouname,sizeof(pgou->gouname));

		}

	   }
  }
//	������������С���������

//	���Ĳ����湷
void palydog(int *dogover,int *maigou,struct gou *pgou,int *yanggou,int *goulanflag,int *goulanmax,struct goutalk *ppgou)
{
	char xuan;
	system("cls");
	   printf("��Ҫ��ʲô��\nA���鿴С����Ϣ\tB������Щ��������\tC����ȥ��һЩ��\tD�������湷��\tE��������Ϸ��OUT\n");
	   scanf("%c",&xuan);	
	   switch(xuan)
	   {
	   case  'A':
	   case  'a':
	          kanxiaogou(pgou,yanggou,goulanflag,goulanmax,ppgou);break;
	   case  'B':
	   case  'b':
	          gougundan(pgou,yanggou,goulanflag,goulanmax,ppgou);break;
	   case  'C':
	   case  'c':  
              buydog(maigou,yanggou);break;
	   case  'd':
	   case  'D':
	          *dogover=1;break;
	   case  'e':
	   case  'E':
	          *dogover=2;break;
	   default  :
		   printf("�����������⣬���������룬������Ϸ��OUT��������E\n");
	   }
}

void kanxiaogou(struct gou *pgou,int *yanggou,int *goulanflag,int *goulanmax,struct goutalk *ppgou)
{
	int i,cmpall=7;
	int j=0;
	int flagkan=0;
	int cmpname=7;
	char shuru[20]={0};
	j=shuijishu();
	printf("���뿴��ֻС��\n������С�����ֻ�������All�鿴���й���Ϣ\n");
	 scanf_s("%[^\n]",shuru,sizeof(shuru));
	 cmpall=strcmp(shuru,"All");
	 if(!cmpall)
	checkgouwo(pgou,yanggou,goulanflag,goulanmax);

    	for(i=0;i<9;i++)
	   {
		cmpname=strcmp(pgou->gouname,shuru);
		if(cmpname) 
			{
				continue;
	     	}
		else
		    {
            flagkan=1;
			printf("С������%s     %s\n",pgou+i,ppgou+j);
        	break;

		    }

		}
			if(!flagkan)
		{
		printf("�������С������û���ҵ�����֪Ϥ��\n");
		}
}

void gougundan(struct gou *pgou,int *yanggou,int *goulanflag,int *goulanmax,struct goutalk *ppgou)
{
	int i,cmpall=7;
	int j=0;
	int flaggun=0;
	int cmpname=7;
	char gundan[20]={0};
	j=shuijishu();
	printf("����Ҫ��ֻС�������������빷���֡�����뿴С����Ϣ��������All\n");

	 scanf_s("%[^\n]",gundan,sizeof(gundan));
	 cmpall=strcmp(gundan,"All");
	 if(!cmpall)
	checkgouwo(pgou,yanggou,goulanflag,goulanmax);

    	for(i=0;i<9;i++)
	   {
		cmpname=strcmp(pgou->gouname,gundan);
		if(cmpname) 
			{
				continue;
	     	}
		else
		    {
             flaggun=1;
			printf("С������%s     %s����Ҫ������\n",pgou+i,ppgou+j);
			(pgou+i)->gouname="0";
        	break;
		    }
		}
			if(!flaggun)
		{
		printf("�������С������û���ҵ�����֪Ϥ��\n");
		}

}

