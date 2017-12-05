#ifndef __wangluo_fw_
#define __wangluo_fw_
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>
#include "wangluo_fw_accept.h"
#include "wangluo_fw_sel.h"


struct wangluo_fw 
{
	struct wangluo_fw_accept fw_ac_1;
	struct wangluo_fw_sel fw_sel_1;


	//pthread_t poll_thread;
	//int state;// mm_thread_state_t,default is ts_closed(0)
	//int error_code;
};

     
extern void wangluo_fw_init(struct wangluo_fw* p);
extern void wangluo_fw_destroy(struct wangluo_fw* p);
//
//extern void wangluo_fw_poll_wait(struct wangluo_fw* p);
//
extern void wangluo_fw_start(struct wangluo_fw* p);
extern void wangluo_fw_interrupt(struct wangluo_fw* p);
extern void wangluo_fw_shutdown(struct wangluo_fw* p);
extern void wangluo_fw_join(struct wangluo_fw* p);
    

#endif//__wangluo_fw_