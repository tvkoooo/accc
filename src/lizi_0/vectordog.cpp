#include <stdio.h>
#include <stdlib.h>
#include "vectordog.h"
#include <string.h>

#define Lenen sizeof(struct fun__vectordog_new)

void kongkongkong()
{

}

struct fun__vectordog_new* fun__vectordog_new_alloc()
{
	fun__vectordog_new* head;
	head=(fun__vectordog_new*) malloc(Lenen);
	return(head);
}


void fun__vectordog_new_dealloc(struct fun__vectordog_new* head)
{
	free(head);
}


//tool init  tool数据层面初始化
void fun__vectordog_new_init(struct fun__vectordog_new* head)
{

	head->fun__vectordog_talk=kongkongkong;
	head->pnext=NULL;
}

void fun__vectordog_new_init(struct fun__vectordog_nam *p_dog)
{
	strcpy(p_dog->dog_name,"0");
	strcpy((p_dog+1)->dog_name,"0");
	strcpy((p_dog+2)->dog_name,"0");

}



void fun__vectordog_new_dogname(struct fun__vectordog_nam *p_dog)
{	
	int i;
	for (i=0;i<3;i++)
	{	
	printf("小狗 %d :的名字是？\n",i);
	scanf(" %[^\n]",(p_dog+i)->dog_name);
	}


}

void fun__vectordog_new_add(struct fun__vectordog_new* head,fun__vectordog fdog)
{	
		fun__vectordog_new *find_p,*p1,*p2;	
		p2=(fun__vectordog_new*) malloc(Lenen);
		p1=head;
		while (p1->pnext!=NULL)
		{
			find_p=p1;
			p1=p1->pnext;
		}
		p1->pnext=p2;
		//printf("小狗的名字是？\n");
		//scanf(" %[^\n]",pname->dog_name);
		p2->fun__vectordog_talk=fdog;
		p2->pnext=NULL;
}

void fun__vectordog_new_sub(struct fun__vectordog_new* head)
{
	fun__vectordog_new *find_p,*p1;
	find_p=p1=head;
	if (p1->pnext==NULL)
	{
		printf("容器已经删空,【770清屏，666清除数据，777删掉上个内容，888查看容器内容，999查看容器大小，退出520250】\n");
	}
	else
	{
		while (p1->pnext!=NULL)
		{
			find_p=p1;
			p1=p1->pnext;
		}
		free(p1);
		p1=NULL;
		//strcpy(pname->dog_name,"0");
		find_p->fun__vectordog_talk=kongkongkong;
		find_p->pnext=NULL;
	}

}


//tool init  tool数据刷新
void fun__vectordog_new_update(struct fun__vectordog_new* ptalk,struct fun__vectordog_nam* pname)
{
	fun__vectordog_new *find_p,*p1;
	p1=ptalk;
	
	while(p1->pnext!=NULL) 
	{
		find_p=p1;
		p1=p1->pnext;

		int i;
		for (i=0;i<3;i++)
		{
			printf("小狗：%s\n",(pname+i)->dog_name);
			p1->fun__vectordog_talk();


		}
    }
}

void fun__vectordog_new_update(struct fun__vectordog_new* ptalk,struct fun__vectordog_nam* pname)
{
	fun__vectordog_new *find_p,*p1;
	p1=ptalk;

	while(p1->pnext!=NULL) 
	{
		find_p=p1;
		p1=p1->pnext;

		int i;
		for (i=0;i<3;i++)
		{
			printf("小狗：%s\n",(pname+i)->dog_name);
			p1->fun__vectordog_talk();


		}
	}
}






//tool init  数据统计个数
int fun__vectordog_new_seizof(struct fun__vectordog_new*head)
{
	fun__vectordog_new *find_p,*p1;
	int n=0;
	find_p=p1=NULL;
	p1=head;
	while(p1->pnext!=NULL)
	{
		find_p=p1;
		p1=p1->pnext;
		n++;
	};
	return(n);
}

//tool destroy 容器销毁销毁
void fun__vectordog_new_clear(struct fun__vectordog_new* ptalk,struct fun__vectordog_nam* pname)
{
	fun__vectordog_new *find_p,*p1;
	find_p=p1=ptalk;

	while (ptalk->pnext!=NULL)
	{	
		find_p=ptalk;
		p1=ptalk->pnext;
		while(p1->pnext!=NULL) 
		{
	
			find_p=p1;
			p1=p1->pnext;
		};

		free(p1);
		p1=NULL;
		find_p->pnext=NULL;
	}

	strcpy(pname->dog_name,"0");
	strcpy((pname+1)->dog_name,"0");
	strcpy((pname+2)->dog_name,"0");

}

//tool destroy 数据回初始化
void fun__vectordog_new_destroy(struct fun__vectordog_new* ptalk,struct fun__vectordog_nam* pname)
{
	fun__vectordog_new_clear(ptalk,pname);

	strcpy(pname->dog_name,"0");
	ptalk->fun__vectordog_talk=kongkongkong;
	ptalk->pnext=NULL;
}

void fun__vectordog_talk_a()
{
	int chos;
	chos=shuijishu()%3;
	switch (chos)
	{
	case 0: printf("给我食物\n");break;
	case 1: printf("我饿了\n");break;
	case 2: printf("不给我食物小心我咬你!\n");break;
	default:
		break;
	}
}

void fun__vectordog_new_test()
{
	int num=0;
	//容器内容
	int vecnum;
	vecnum=0;
	//声明容器
	fun__vectordog_new* creat_vec;
	fun__vectordog_nam dog[3];
	//创建容器
	creat_vec=fun__vectordog_new_alloc();

	//容器数据初始化
	fun__vectordog_new_init(creat_vec);
	fun__vectordog_nam_init(dog);
	

	while(num!=555)
	{
		//添加容器内容
		printf("333给小狗取名。【222,增加小狗操作，770清屏，666清除数据，777删掉上个内容，888查看容器内容，999查看容器大小，退出555】\n");
		scanf("%d",&num);
		if ((num==333))
		{

			fun__vectordog_new_dogname(dog);
		}
		if ((num==222))
		{
			fun__vectordog_new_add(creat_vec,fun__vectordog_talk_a);

		}


		if (num==770)
		{
			system("CLS");
		}
		if (num==555)
		{
			break;
		}
		if (num==777)
		{
			fun__vectordog_new_sub(creat_vec);
		}
		if (num==888)
		{
			fun__vectordog_new_update(creat_vec,dog);
		}
		if (num==999)
		{
			vecnum=fun__vectordog_new_seizof(creat_vec);
			printf("查看容器大小：共有%d个数据，内存分配大小是：%d\n",vecnum,vecnum*Lenen);
		}
		if (num==666)
		{
			fun__vectordog_new_clear(creat_vec,dog);

		}

	}
	fun__vectordog_new_clear(creat_vec,dog);
	fun__vectordog_new_destroy(creat_vec,dog);
	fun__vectordog_new_dealloc(creat_vec);
}