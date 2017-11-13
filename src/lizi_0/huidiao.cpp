#include <stdio.h>
#include <stdlib.h>
#include "huidiao.h"
#include <windows.h>


void huidiao_new()
{

	struct fun_huidiao_new fclock_a;

	fun_huidiao_init(&fclock_a);

	fclock_a.clocktime=0;
	fclock_a.fun_hui=&fun_huidiao_fun;

	do 
	{


		if (fclock_a.clocktime>=6)
		{
			break;
		}

		Sleep (1000);
		fclock_a.fun_hui();
		fclock_a.clocktime++;
	} while (1);


	fun_huidiao_destroy(&fclock_a);

}

//tool init  tool数据层面初始化
void fun_huidiao_init(struct fun_huidiao_new *p)
{
	p->clocktime=0;
	(*p).fun_hui=NULL;
}

//tool destroy tool数据层面销毁
void fun_huidiao_destroy(struct fun_huidiao_new *p)
{
	p->clocktime=0;
	(*p).fun_hui=NULL;
}

//tool fun    时钟回掉
void fun_huidiao_fun()
{
	printf("huidiao chengong!\n");
}




