#ifndef _INC_robot_add_peo
#define _INC_robot_add_peo
#include <pthread.h>
//#include "mm_thread_state_t.h"



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

#define robot_add_peo_shuimin_time_default 600



	typedef void (*robot_add_peo_huidiao_type) (struct robot_add_peo* obj);
	struct robot_add_peo
	{
		int peonumber;

		pthread_cond_t c;// 条件变量
		pthread_mutex_t m;// 条件变量锁

		//////进程声明 pthread_t 
		pthread_t poll_thread;
		//////进程控制状态
		int state;
		//////回调函数 pthread_huidiao_type ////typedef void (*pthread_huidiao_type) (void)
		robot_add_peo_huidiao_type funpthread_hui;

		//////进程锁 pthread_mutex_t
		pthread_mutex_t locker;// 外锁,内存锁
	};



	extern void robot_add_peo_init(struct robot_add_peo *p);
	extern void robot_add_peo_destroy(struct robot_add_peo*p);
	extern void robot_add_peo_fuzhi(struct robot_add_peo* p,robot_add_peo_huidiao_type f);
	extern void robot_add_peo_poll_wait(struct robot_add_peo* p);
	extern void robot_add_peo_start(struct robot_add_peo* p);
	extern void robot_add_peo_shutdown(struct robot_add_peo* p);
	extern void robot_add_peo_interrupt(struct robot_add_peo* p);
	extern void robot_add_peo_join(struct robot_add_peo*p);
	
	extern void robot_add_peo_rand_number(struct robot_add_peo*p);

	extern void robot_add_peo_lock(struct robot_add_peo*p);
	extern void robot_add_peo_unlock(struct robot_add_peo*p);


#endif  /* _INC_robot_add_peo */