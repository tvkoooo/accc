#ifndef __consumer
#define __consumer
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>
//#include "wangluo_fw_accept.h"

// 维护select线程状态
//接受sClient，维护表
//处理接收过来的recv的等待。
//接收的信息发布，recv  的buf


struct consumer
{
	int save[5];

	pthread_mutex_t mute_save;

	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)

};
    


extern void consumer_init(struct consumer* p);
extern void consumer_destroy(struct consumer* p);
//
extern void consumer_pthread_mutex_init(struct consumer* p);
extern void consumer_pthread_mutex_destroy(struct consumer *p);
//
extern void consumer_shujuchuandi(struct consumer* p, int mes[5]);

extern void consumer_poll_wait(struct consumer* p);
//
extern void consumer_start(struct consumer* p);
extern void consumer_interrupt(struct consumer* p);
extern void consumer_shutdown(struct consumer* p);
extern void consumer_join(struct consumer* p);


#endif//__consumer