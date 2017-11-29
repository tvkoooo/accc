#include <stdio.h>
#include <stdlib.h>
#include "vectordogdingyue.h"
#include <string.h>




//小狗数据初始化
void fun__vectordog_nam_init(struct fun__vectordog_nam *p_dog)
{
	strcpy(p_dog->dog_name,"0");
	p_dog->icc=0;

}
//小狗数据清零
void fun__vectordog_nam_destroy(struct fun__vectordog_nam *p_dog)
{
	strcpy(p_dog->dog_name,"0");
	p_dog->icc=0;
}

//小狗名字赋值
void fun__vectordog_nam_dogname(struct fun__vectordog_nam *p_dog)
{
	int i;
	for (i=0;i<3;i++)
	{	
		printf("小狗 %d :的名字是？\n",i);
		scanf(" %[^\n]",(p_dog+i)->dog_name);
	}

}

//定义空函数保护
static void kongkongkong(void*p,int g)
{

}

// 链表内存分配
struct fun__vectordog_new* fun__vectordog_new_alloc()
{
	struct fun__vectordog_new* head;
	head=(struct fun__vectordog_new*) malloc(sizeof(struct fun__vectordog_new));
	return(head);
}
// 链表内存回收
void fun__vectordog_new_dealloc(struct fun__vectordog_new* ptalk)
{
		free(ptalk);
}


// 链表数据初始化
void fun__vectordog_new_init(struct fun__vectordog_new* ptalk)
{
	ptalk->fun__vectordog_talk=kongkongkong;
	ptalk->pnext=NULL;
}
// 链表数据清零
void fun__vectordog_new_clear(struct fun__vectordog_new* ptalk)
{
	struct fun__vectordog_new *find_p,*p1;
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

}
// 链表清零释放
void fun__vectordog_new_destroy(struct fun__vectordog_new* ptalk)
{
	fun__vectordog_new_clear(ptalk);
	ptalk->fun__vectordog_talk=kongkongkong;
	ptalk->pnext=NULL;
}

// 链表操作---增加（增加订阅）
void fun__vectordog_new_add(struct fun__vectordog_new* ptalk,fun__vectordog fdog,void *p)
{
	struct fun__vectordog_new *find_p,*p1,*p2;

	p2=(struct fun__vectordog_new*) malloc(sizeof(struct fun__vectordog_new));
	p1=ptalk;
	while (p1->pnext!=NULL)
	{
		find_p=p1;
		p1=p1->pnext;

	}
	p1->pnext=p2;

	//printf("小狗的名字是？\n");
	//scanf(" %[^\n]",pname->dog_name);
	p2->fun__vectordog_talk=fdog;
	p2->oo=p;
	p2->pnext=NULL;
}
//  链表操作---删掉（删掉订阅）
void fun__vectordog_new_sub(struct fun__vectordog_new* ptalk)
{
	struct fun__vectordog_new *find_p,*p1;
	find_p=p1=ptalk;
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
		p1->fun__vectordog_talk=kongkongkong;
		free(p1);
		p1=NULL;
		//strcpy(pname->dog_name,"0");
		find_p->pnext=NULL;
	}
}
//  链表操作---链表订阅统计个数
int fun__vectordog_new_seizof(struct fun__vectordog_new*ptalk)
{
	struct fun__vectordog_new *find_p,*p1;
	int n=0;
	find_p=p1=NULL;
	p1=ptalk;
	while(p1->pnext!=NULL)
	{
		find_p=p1;
		p1=p1->pnext;
		n++;
	};
	return(n);
}


//  链表数据刷新---发布
void fun__vectordog_new_update(struct fun__vectordog_new* ptalk,int g)
{
	struct fun__vectordog_new *find_p,*p1;

	p1=ptalk;

	while(p1->pnext!=NULL) 
	{

		find_p=p1;
		p1=p1->pnext;
		p1->fun__vectordog_talk(p1->oo,g);

	}

}

