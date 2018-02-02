#ifndef _zixiancheng_h_
#define _zixiancheng_h_
#include <pthread.h>

#include "errno_lizi4.h"
#include <string>


typedef void (*handle_callback)(void* obj, void* u, std::string* pack);

struct zixiancheng_callback
{
	handle_callback handle;
	void* obj;// weak ref. user data for callback.
};
extern void zixiancheng_callback_init(struct zixiancheng_callback* p);
extern void zixiancheng_callback_destroy(struct zixiancheng_callback* p);

struct zixiancheng 
{
	zixiancheng_callback d_callback;
	void* obj;// weak ref for tcp handle callback.it is mm_tcp.
	std::string pack;

	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)
};



      
extern void zixiancheng_init(struct zixiancheng* p);
extern void zixiancheng_destroy(struct zixiancheng* p);
//
extern void application_fuzhi(struct zixiancheng* p);
extern void zixiancheng_poll_wait(struct zixiancheng* p);
//
extern void zixiancheng_start(struct zixiancheng* p);
extern void zixiancheng_interrupt(struct zixiancheng* p);
extern void zixiancheng_shutdown(struct zixiancheng* p);
extern void zixiancheng_join(struct zixiancheng* p);
   

#endif//_zixiancheng_h_