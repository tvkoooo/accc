#ifndef __producer
#define __producer
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>
//#include "wangluo_fw_accept.h"

// 维护select线程状态
//接受sClient，维护表
//处理接收过来的recv的等待。
//接收的信息发布，recv  的buf

typedef void (*producer_handle)( void* obj, int mes[5]);

struct producer_callback
{
	producer_handle handle;
	void* obj;// weak ref. user data for callback.
};

extern void producer_callback_init(struct producer_callback* p);
extern void producer_callback_destroy(struct producer_callback* p);


struct producer
{
	//回调信息
	int mes[5];
	//回调结构
	struct producer_callback callback;
	//状态维护
	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)

};
    
//extern void *producer_pro(void *p);
//extern fun_void_s3 producer_shujuchuandi(void *p,socket_type sClient,struct sockaddr_in *pr,socklen_t nAddrlen);


extern void producer_init(struct producer* p);
extern void producer_destroy(struct producer* p);
//
extern void producer_setcallback(struct producer* p,struct producer_callback* pp);
//
extern void producer_poll_wait(struct producer* p);
//
extern void producer_start(struct producer* p);
extern void producer_interrupt(struct producer* p);
extern void producer_shutdown(struct producer* p);
extern void producer_join(struct producer* p);


#endif//__producer