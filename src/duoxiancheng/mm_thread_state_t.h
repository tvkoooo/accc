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