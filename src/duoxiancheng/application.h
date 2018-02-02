#ifndef _INC_application
#define _INC_application

#include <pthread.h>
#include "mm_buffer_queue.h"
#include "zixiancheng.h"
#include <map>

struct application
{

	mm::mm_buffer_queue robot_queue;

	zixiancheng x1;
	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)
};



extern void application_init(struct application* p);
extern void application_destroy(struct application* p);
///////////////////////////////////////////////////
extern void application_fuzhi(struct application* p,int argc,char **argv);
///////////////////////////////////////////////////
extern void zixiancheng_poll_wait(struct application* p);
///////////////////////////////////////////////////
extern void application_start(struct application* p);
extern void application_interrupt(struct application* p);
extern void application_shutdown(struct application* p);
extern void application_join(struct application* p);

#endif  /* _INC_application */