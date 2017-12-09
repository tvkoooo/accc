#include "application.h"
#include "pthread.h"
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <windows.h>

#include "fun_log_dll.h"
#include "mm_uuu.h"
#include "pthread_huidiao.h"
#include "ccc.h"

static void* __static_rtrr(void* arg)
{

}

void application_init(struct application* p)
{
	int b = mm::AAAA::bbbb;

	mm::AAAA a;
	a.func();
	//lizhi1_huidiao_init(&p->huidiaotest_1);
	mm_uuu_init(&p->m1);
}
void application_destroy(struct application* p)
{
	//lizhi1_huidiao_destroy(&p->huidiaotest_1);
	mm_uuu_destroy(&p->m1);
}

void application_start(struct application* p)
{	
	//lizhi1_huidiao_start(&p->huidiaotest_1);
	mm_uuu_start(&p->m1);
	//char bbc[30]="wo kao ni lao mao,ri o ";
	//int ak=5;
	//fun_log_fprintf(bbc);
	//printf("%s",bbc);

}
void application_interrupt(struct application* p)
{
		mm_uuu_interrupt(&p->m1);
	
}
void application_shutdown(struct application* p)
{
	//lizhi1_huidiao_shutdown(&p->huidiaotest_1);
	mm_uuu_shutdown(&p->m1);


}
void application_join(struct application* p)
{
	//lizhi1_huidiao_join(&p->huidiaotest_1);
	mm_uuu_join(&p->m1);
}

void application_fuzhi(struct application* p,int argc,char **argv)
{
	//printf("int argc:%d",argc);
	//while(argc-->1)
	//{
	//	printf("%s",*++argv);
	//}

}