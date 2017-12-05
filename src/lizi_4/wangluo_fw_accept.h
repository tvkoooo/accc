#ifndef __wangluo_fw_accept
#define __wangluo_fw_accept
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>

//维护socket的状态（包含启动和结束）
//做一个accept的线程，并维护他的生命周期
//传递  启动 accept socket套接字信息。

typedef void (*wangluo_fw_accept_handle)( void* obj, socket_type sClient);
struct wangluo_fw_accept_callback
{
	wangluo_fw_accept_handle handle;
	void* obj;// weak ref. user data for callback.
};

extern void wangluo_fw_accept_callback_init(struct wangluo_fw_accept_callback* p);
extern void wangluo_fw_accept_callback_destroy(struct wangluo_fw_accept_callback* p);

struct wangluo_fw_accept 
{
	//std::map<socket_type,int> map_s;
	//回调部分
	socket_type sClient;
	struct wangluo_fw_accept_callback callback;
	//wangluo_fw_accept数据
	socket_type sClient_jin;
	socket_type slisten;
	struct sockaddr_in remoteAddr;
	socklen_t nAddrlen;
	//维护状态
	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)
	//int error_code;
};


extern void wangluo_fw_accept_init(struct wangluo_fw_accept* p);
extern void wangluo_fw_accept_destroy(struct wangluo_fw_accept* p);
//
extern void wangluo_fw_accept_setcallback(struct wangluo_fw_accept* p,struct wangluo_fw_accept_callback* pp);
//
extern void wangluo_fw_accept_poll_wait(struct wangluo_fw_accept* p);
//
extern void wangluo_fw_accept_start(struct wangluo_fw_accept* p);
extern void wangluo_fw_accept_interrupt(struct wangluo_fw_accept* p);
extern void wangluo_fw_accept_shutdown(struct wangluo_fw_accept* p);
extern void wangluo_fw_accept_join(struct wangluo_fw_accept* p);





#endif//__wangluo_fw_accept