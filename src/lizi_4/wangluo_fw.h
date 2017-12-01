#ifndef __wangluo_fw_
#define __wangluo_fw_
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>




struct wangluo_fw 
{
	std::map<socket_type,int> map_s;
	socket_type sClient;
	socket_type slisten;
	struct sockaddr_in remoteAddr;
	socklen_t nAddrlen;
	char revData[255];

	struct timeval map_timeout;

	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)
	int error_code;
};






     
extern void wangluo_fw_init(struct wangluo_fw* p);
extern void wangluo_fw_destroy(struct wangluo_fw* p);
//
extern void wangluo_fw_poll_wait(struct wangluo_fw* p);
//
extern void wangluo_fw_start(struct wangluo_fw* p);
extern void wangluo_fw_interrupt(struct wangluo_fw* p);
extern void wangluo_fw_shutdown(struct wangluo_fw* p);
extern void wangluo_fw_join(struct wangluo_fw* p);
    

#endif//__wangluo_fw_