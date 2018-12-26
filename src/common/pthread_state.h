#ifndef __pthread_state_h__
#define __pthread_state_h__

#include <pthread.h>
// thread have some interface.
//  init         state = ts_closed
//  start        state = ts_finish == state ? ts_closed : ts_motion;
//  interrupt    state = ts_closed
//  shutdown     state = ts_finish
//  join         state = state
//  destroy      state = ts_closed
enum thread_state_s
{
	ts_closed    = 0,// thread not start or be closed or be interrupt.
	ts_motion    = 1,// thread is running.
	ts_finish    = 2,// application is termination and can not restart.
};      


////
//struct mm_uuu 
//{
//	pthread_t poll_thread;
//	int state;// mm_thread_state_t,default is ts_closed(0)
//};
//
//
//#if defined(__cplusplus)
//extern "C"
//{
//#endif       
//	extern void mm_uuu_init(struct mm_uuu* p);
//	extern void mm_uuu_destroy(struct mm_uuu* p);
//	//
//	extern void mm_uuu_poll_wait(struct mm_uuu* p);
//	//
//	extern void mm_uuu_start(struct mm_uuu* p);
//	extern void mm_uuu_interrupt(struct mm_uuu* p);
//	extern void mm_uuu_shutdown(struct mm_uuu* p);
//	extern void mm_uuu_join(struct mm_uuu* p);
//#if defined(__cplusplus)
//}
//#endif    


#endif//__pthread_state_h__