#ifndef __wangluo_fw_sel
#define __wangluo_fw_sel
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>

// 维护select线程状态
//接受sClient，维护表
//处理接收过来的recv的等待。
//接收的信息发布，recv  的buf


struct wangluo_fw_sel
{

	//struct sockaddr_in remoteAddr;
	//socklen_t nAddrlen;
	struct timeval map_timeout;
	std::map<socket_type,int> fw_sel_map1;

	pthread_mutex_t mute_sClient;

	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)

};
    


extern void wangluo_fw_sel_init(struct wangluo_fw_sel* p);
extern void wangluo_fw_sel_destroy(struct wangluo_fw_sel* p);
//
extern void wangluo_fw_sel_shujuchuandi(struct wangluo_fw_sel *p,socket_type sClient);

extern void wangluo_fw_sel_poll_wait(struct wangluo_fw_sel* p);
//
extern void wangluo_fw_sel_start(struct wangluo_fw_sel* p);
extern void wangluo_fw_sel_interrupt(struct wangluo_fw_sel* p);
extern void wangluo_fw_sel_shutdown(struct wangluo_fw_sel* p);
extern void wangluo_fw_sel_join(struct wangluo_fw_sel* p);


#endif//__wangluo_fw_sel