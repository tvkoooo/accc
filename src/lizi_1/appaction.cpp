#include "appaction.h"
#include "pthread.h"
#include "lizhi1_all.h"
static void* __static_rtrr(void* arg)
{

}


void appaction_init(struct appaction* p)
{
    lizhi1_huidiao_init(&p->huidiaotest_1);
	mm_uuu_init(&p->m1);
}
void appaction_destroy(struct appaction* p)
{
	lizhi1_huidiao_destroy(&p->huidiaotest_1);
	mm_uuu_destroy(&p->m1);
}

void appaction_start(struct appaction* p)
{	
	lizhi1_huidiao_start(&p->huidiaotest_1);
	mm_uuu_start(&p->m1);

}
void appaction_interrupt(struct appaction* p)
{
	mm_uuu_interrupt(&p->m1);
	
}
void appaction_shutdown(struct appaction* p)
{
	lizhi1_huidiao_shutdown(&p->huidiaotest_1);
	mm_uuu_shutdown(&p->m1);

}
void appaction_join(struct appaction* p)
{
	lizhi1_huidiao_join(&p->huidiaotest_1);
	mm_uuu_join(&p->m1);
}
//void appaction_fun1()
//{
//	printf("回调成功!\n");
//}
//
//
//void * appaction_pthr_1(void * p)
//{		
//
//	struct pthread_huidiao_1 *a;
//	a=(struct pthread_huidiao_1 *)p;
//	do 
//	{
//		lizhi1_huidiao_lock(a);
//		a->clocktime++;
//		lizhi1_huidiao_unlock(a);
//		Sleep(1000);
//		a->funpthread_hui();
//	} while (a->flag_appact==0);
//	return 0;
//}
