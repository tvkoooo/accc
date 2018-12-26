#ifndef __wangluo_kehu_h_
#define __wangluo_kehu_h_

#include <pthread.h>
#include "socket_context_lizi4.h"
#include "errno_lizi4.h"
#include "core/mm_os_socket.h"
#include <string>

//#include "plink.h"
//#include "mm_classic_packet_head.h"


struct sendword
{
	uint64_t Pid;     
	uint64_t Mid;
	std::string Datawords;
};




struct wangluo_kehu 
{
	socket_type sclient;


	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)
};

      
extern void wangluo_kehu_init(struct wangluo_kehu* p);
extern void wangluo_kehu_destroy(struct wangluo_kehu* p);
//
extern void wangluo_kehu_poll_wait(struct wangluo_kehu* p);
//
extern void wangluo_kehu_start(struct wangluo_kehu* p);
extern void wangluo_kehu_interrupt(struct wangluo_kehu* p);
extern void wangluo_kehu_shutdown(struct wangluo_kehu* p);
extern void wangluo_kehu_join(struct wangluo_kehu* p);
   

#endif//__wangluo_kehu_h_