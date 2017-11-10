#include <stdio.h>
#include <stdlib.h>
#include "huidiao.h"
#include <windows.h>


void huidiao_a()
{
	int a;
	a=0;
	struct fun_huidiao_in fun_time_a;
	fun_time_a.fun_hui=editprinthuidiao;

	do 
	{
		fun_time_a.clocktime=a++;
		huidiao_clock_in(fun_time_a);
		if (fun_time_a.clocktime>20)
		{
			break;
		}
		Sleep (1000);

	} while (1);

}

void editprinthuidiao()
{
	printf("huidiao chengong!\n");
}

void huidiao_clock_in(struct fun_huidiao_in clock_a)
{
	if (clock_a.clocktime%6==3)
	{
		clock_a.fun_hui();
	}
}

