#ifndef _INC_application
#define _INC_application
#include <pthread.h>
#include "errno_lizi4.h"
#include "wangluo_kehu.h"
struct application
{
	wangluo_kehu khtest;
	//pthread_t poll_thread;
	//int state;// mm_thread_state_t,default is ts_closed(0)
};



extern void application_init(struct application* p);
extern void application_destroy(struct application* p);
///////////////////////////////////////////////////
extern void application_fuzhi(struct application* p,int argc,char **argv);
///////////////////////////////////////////////////
extern void zixiancheng_poll_wait(struct zixiancheng* p);
///////////////////////////////////////////////////
extern void application_start(struct application* p);
extern void application_interrupt(struct application* p);
extern void application_shutdown(struct application* p);
extern void application_join(struct application* p);

#endif  /* _INC_application */