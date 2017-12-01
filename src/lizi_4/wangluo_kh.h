#ifndef __wangluo_kh_
#define __wangluo_kh_

#include <pthread.h>
#include "socket_context_lizi4.h"

struct wangluo_kh 
{
	socket_type sclient;


	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)
};



      
extern void wangluo_kh_init(struct wangluo_kh* p);
extern void wangluo_kh_destroy(struct wangluo_kh* p);
//
extern void wangluo_kh_poll_wait(struct wangluo_kh* p);
//
extern void wangluo_kh_start(struct wangluo_kh* p);
extern void wangluo_kh_interrupt(struct wangluo_kh* p);
extern void wangluo_kh_shutdown(struct wangluo_kh* p);
extern void wangluo_kh_join(struct wangluo_kh* p);
   

#endif//__wangluo_kh_