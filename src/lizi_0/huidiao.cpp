#include <stdio.h>
#include <stdlib.h>
#include "huidiao.h"
#include <windows.h>

void huidiao()
{
	
}
void huidiao_new()
{

	struct fun_huidiao_new fclock_a;

	fun_huidiao_init(&fclock_a);

	fun_huidiao_fuzhi(&fclock_a,&fun_huidiao_fun);

	do 
	{


		if (fclock_a.clocktime>=6)
		{
			break;
		}

		Sleep (1000);

		fun_huidiao_update(&fclock_a);

	} while (1);


	  fun_huidiao_destroy(&fclock_a);

}

//tool init  tool数据层面初始化
void fun_huidiao_init(struct fun_huidiao_new *p)
{
	p->clocktime=0;

	p->fun_hui=&huidiao;
}

//tool destroy tool数据层面销毁
void fun_huidiao_destroy(struct fun_huidiao_new *p)
{
	p->clocktime=0;
	(*p).fun_hui=&huidiao;
}

//tool fun    时钟回掉
void fun_huidiao_fun()
{
	printf("huidiao chengong!\n");
}

//tool init  tool数据赋值
void fun_huidiao_fuzhi(struct fun_huidiao_new*p,huidiao_type p1)
{

	p->clocktime=0;
	p->fun_hui=p1;
}

//tool init  tool数据刷新
void fun_huidiao_update(struct fun_huidiao_new*p)
{

	p->fun_hui();
	p->clocktime++;
}
