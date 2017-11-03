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
//数据初始化
	int cmp=0;
	int k=0;
	int i,j=1;
	int flag=0;
	int	maigou=0;
	int dogover=0;
//狗窝初始化
	int yanggou=0;
	int goulanmax=1;
	int goulanflag=0;

//玩狗初始化
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
	
//清空狗窝
	for(i=0;i<9;i++)
	{
		pgou->gouname="0";
		pgou->gouou=quangougou;
	}

//游戏开始
	
	do
	{
//一检查狗窝
checkgouwo(pgou,&yanggou,&goulanflag,&goulanmax);
//二买狗
buydog( &maigou,&yanggou);
//三安排狗舍
anpaisushe(pgou,&yanggou,&maigou);
//四玩狗
palydog(&dogover,&maigou,pgou,&yanggou,&goulanflag,&goulanmax,ppgou);

if(dogover)
	break;
else
	palydog(&dogover,&maigou,pgou,&yanggou,&goulanflag,&goulanmax,ppgou);

    }while(1);
		
	}


//	第一步：检查狗窝
void checkgouwo(struct gou *pgou,int *yanggou,int *goulanflag,int *goulanmax)
{
	int i,cmp;
	for(i=1;i<10;i++)
	{
		cmp=strcmp(pgou->gouname,"0");
		if(cmp) 
			{
				printf("狗窝%d有小狗住着，叫%s",i,pgou->gouname);
				*yanggou++;
		        *goulanflag=i;	
	     	}
		else
		{
		printf("狗窝%d是空的,可以放入小狗");
		}
		
		if(*yanggou)
			*goulanmax=*goulanflag;
		else
			*goulanmax=1;

	}
}
//  第一步：检查狗窝结束

//  第二步：买狗
void buydog(int *maigou,int *yanggou)
	{
		system("cls");
	    printf("\n你想拿几只小狗回家玩?你最多只可以养9只狗。\n");
	    printf("提示：你需要输入一个正整数.\n如果不想买，请输0\n");	
		scanf("%d",maigou);
	
		if(!((*maigou)>=0&&(*maigou<10)))
		{
	    printf("你的数字有问题，请重新输入，不然小狗狗不卖.\n");

		}
		  else
	   { 
		   if(*yanggou+*maigou>10)
		   {
			printf("你的数字有问题，请重新输入，不然小狗狗不卖.\n");
	        printf("如果不想买，请输0.\n\n");
		   }
	     	else
	     	{
	     	printf("咯，拿走   %d  条哈巴狗！\n",*maigou);
	     	
	    	}

	  	}
				
	
		*yanggou=*yanggou+*maigou;
}
//	第二步：去商城买狗结束


//	第三步：安排小狗宿舍
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
				printf("狗窝%d有小狗住着，叫%s",i,pgou->gouname);
	     	}
		else
		{
			printf("狗窝%d是空的,可以放入小狗，小狗叫什么，请回车键结束");
		    scanf_s("%[^\n]",pgou->gouname,sizeof(pgou->gouname));

		}

	   }
  }
//	第三步：安排小狗宿舍结束

//	第四步：玩狗
void palydog(int *dogover,int *maigou,struct gou *pgou,int *yanggou,int *goulanflag,int *goulanmax,struct goutalk *ppgou)
{
	char xuan;
	system("cls");
	   printf("你要干什么？\nA、查看小狗信息\tB、让哪些狗狗滚蛋\tC、再去买一些狗\tD、不想玩狗了\tE、结束游戏，OUT\n");
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
		   printf("你输入有问题，请重新输入，结束游戏，OUT，请输入E\n");
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
	printf("你想看哪只小狗\n请输入小狗名字或者输入All查看所有狗信息\n");
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
			printf("小狗名：%s     %s\n",pgou+i,ppgou+j);
        	break;

		    }

		}
			if(!flagkan)
		{
		printf("你输入的小狗名字没有找到，请知悉！\n");
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
	printf("你想要哪只小狗滚蛋？请输入狗名字。如果想看小狗信息，请输入All\n");

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
			printf("小狗名：%s     %s马上要滚蛋了\n",pgou+i,ppgou+j);
			(pgou+i)->gouname="0";
        	break;
		    }
		}
			if(!flaggun)
		{
		printf("你输入的小狗名字没有找到，请知悉！\n");
		}

}

