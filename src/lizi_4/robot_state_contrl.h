#ifndef _INC_robot_state_contrl
#define _INC_robot_state_contrl
#include <pthread.h>
//#include "mm_thread_state_t.h"
#include <map>


#ifndef _INC_mm_thread_state_t
#define _INC_mm_thread_state_t

// thread have some interface.
//  init         state = ts_closed
//  start        state = ts_finish == state ? ts_closed : ts_motion;
//  interrupt    state = ts_closed
//  shutdown     state = ts_finish
//  join         state = state
//  destroy      state = ts_closed
enum mm_thread_state_t
{
	ts_closed    = 0,// thread not start or be closed or be interrupt.
	ts_motion    = 1,// thread is running.
	ts_finish    = 2,// application is termination and can not restart.
};
//

#endif  /* _INC_mm_thread_state_t */

#define chushijiqiren_default 100
#define suijijiqiqren_default 20
#define shuimin_time_default 600



	typedef void (*robot_huidiao_type) (struct robot_contrl* obj);
	struct robot_contrl
	{

		int sid;
		int chushijiqiren;
		int suijijiqiqren;
		int shuimin_time;
		int xunhuanceshu;
	 	unsigned int uaddtotalnum;

		pthread_cond_t c;// 条件变量
		pthread_mutex_t m;// 条件变量锁

		//////进程声明 pthread_t 
		pthread_t poll_thread;
		//////进程控制状态
		int state;
		//////回调函数 pthread_huidiao_type ////typedef void (*pthread_huidiao_type) (void)
		robot_huidiao_type funpthread_hui;
		void* u;
		//////进程锁 pthread_mutex_t
		pthread_mutex_t locker;// 外锁,内存锁
	};



	extern void robot_contrl_init(struct robot_contrl *p);
	extern void robot_contrl_destroy(struct robot_contrl*p);
	extern void robot_contrl_fuzhi(struct robot_contrl*p,robot_huidiao_type f, void* u);
	extern void robot_contrl_poll_wait(struct robot_contrl* p);
	extern void robot_contrl_start(struct robot_contrl* p);
	extern void robot_contrl_shutdown(struct robot_contrl* p);
	extern void robot_contrl_interrupt(struct robot_contrl* p);
	extern void robot_contrl_join(struct robot_contrl*p);
	
	extern void robot_contrl_rand_number(struct robot_contrl*p);

	extern void robot_contrl_lock(struct robot_contrl*p);
	extern void robot_contrl_unlock(struct robot_contrl*p);


#endif  /* _INC_robot_state_contrl */