#include <stdio.h>
#include <stdlib.h>
#include "vectordogdingyue.h"
#include <string.h>
//小狗说话
void fun__vectordog_talk_a(void * p,int g)
{
	struct fun__vectordog_nam * dogname;
	int chos;


	dogname=(struct fun__vectordog_nam*)p;

	chos=shuijishu()%3;
	printf("\n小狗  %s  %d  %d:    ",dogname->dog_name,dogname->icc,g);

	switch (chos)
	{
	case 0: printf("给我食物\n");break;
	case 1: printf("我饿了\n");break;
	case 2: printf("不给我食物小心我咬你!\n");break;
	default:
		break;
	}
}
void fun__vectordog_qb_a(void * p,int g)
{
	struct fun__vectordog_nam * dogname;
	int chos;
	dogname=(struct fun__vectordog_nam*)p;

	chos=shuijishu()%3;
	printf("\n小狗  %s  %d qb",dogname->dog_name,dogname->icc);
}
void fun__vectordog_rrrr_a(void * p,int g)
{

}
typedef void (*huidiaorr_type) (void * p,int g);
void fun__vectordog_new_test()
{
	//定义操作项
	int num=0;
	//容器大小
	int vecnum;
	struct fun__vectordog_new* creat_vec;
	struct fun__vectordog_new* creat_vqb;

	struct fun__vectordog_nam dog[3];

	{
		// 1:1
		huidiaorr_type dd;
		//订阅 1
		dd = fun__vectordog_rrrr_a;
		//发布
		dd(NULL,8);
	}

	vecnum=0;

	// 1:n

	//创建容器
	creat_vec=fun__vectordog_new_alloc();
	creat_vqb=fun__vectordog_new_alloc();

	//容器数据初始化
	fun__vectordog_new_init(creat_vec);
	fun__vectordog_new_init(creat_vqb);
	//小狗数据初始化
	fun__vectordog_nam_init(&dog[0]);
	fun__vectordog_nam_init(&dog[1]);
	fun__vectordog_nam_init(&dog[2]);

	strcpy(dog[0].dog_name,"ni mei");
	strcpy(dog[1].dog_name,"nsss sf");
	strcpy(dog[2].dog_name,"a regeryy");
	dog[0].icc=34;
	dog[1].icc=22;
	dog[2].icc=87;

	//订阅 n
	fun__vectordog_new_add(creat_vec,fun__vectordog_talk_a,&dog[0]);
	fun__vectordog_new_add(creat_vec,fun__vectordog_talk_a,&dog[1]);
	fun__vectordog_new_add(creat_vec,fun__vectordog_talk_a,&dog[2]);

	fun__vectordog_new_add(creat_vqb,fun__vectordog_qb_a,&dog[0]);
	fun__vectordog_new_add(creat_vqb,fun__vectordog_qb_a,&dog[1]);
	fun__vectordog_new_add(creat_vqb,fun__vectordog_qb_a,&dog[2]);

	//发布
	fun__vectordog_new_update(creat_vec,7);
	fun__vectordog_new_update(creat_vqb,8);
	//while(num!=555)
	//{

	//	printf("333给小狗取名。【222,增加小狗操作，770清屏，666清除操作，777删掉上个操作，888查看操作内容，999查看操作个数，退出555】\n");
	//	scanf(" %d",&num);
	//	if ((num==333))
	//	{
	//		//小狗数据赋值
	//		fun__vectordog_nam_dogname(dog);
	//	}
	//	//if ((num==222))
	//	//{
	//	//	//链表增加，增加订阅
	//	//	fun__vectordog_new_add(creat_vec,fun__vectordog_talk_a,dog);

	//	//}

	//	if (num==770)
	//	{
	//		//清屏
	//		system("CLS");
	//	}
	//	if (num==555)
	//	{
	//		//退出
	//		break;
	//	}
	//	//if (num==777)
	//	//{
	//	//	//减少链表，去掉一项 订阅
	//	//	fun__vectordog_new_sub(creat_vec);
	//	//}
	//	if (num==888)
	//	{
	//		//链表触发，发布
	//		fun__vectordog_new_update(creat_vec);
	//	}
	//	if (num==999)
	//	{
	//		//查看容器
	//		vecnum=fun__vectordog_new_seizof(creat_vec);
	//		printf("查看容器大小：共有%d个数据，内存分配大小是：%d\n",vecnum,vecnum*sizeof(struct fun__vectordog_new));
	//	}
	//	if (num==666)
	//	{
	//		//清空链表，去掉订阅
	//		fun__vectordog_new_clear(creat_vec);
	//	}

	//}

	//清空链表

	//清空小狗数据
	fun__vectordog_nam_destroy(&dog[0]);
	fun__vectordog_nam_destroy(&dog[1]);
	fun__vectordog_nam_destroy(&dog[2]);

	fun__vectordog_new_destroy(creat_vec);
	//链表内存回收
	fun__vectordog_new_dealloc(creat_vec);

	fun__vectordog_new_destroy(creat_vqb);
	//链表内存回收
	fun__vectordog_new_dealloc(creat_vqb);
}//小狗数据内存回收

//struct playet
//{
//	float m;//钱
//	struct fun__vectordog_new evt_yzd;
//
//	float f;//防御力
//	struct fun__vectordog_new evt_yzd;
//};
