#ifndef __robot_entry_h_
#define __robot_entry_h_

#include <pthread.h>



struct robot_entry 
{



	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)
};



      
extern void robot_entry_init(struct robot_entry* p);
extern void robot_entry_destroy(struct robot_entry* p);
//
extern void robot_entry_poll_wait(struct robot_entry* p);
//
extern void robot_entry_start(struct robot_entry* p);
extern void robot_entry_interrupt(struct robot_entry* p);
extern void robot_entry_shutdown(struct robot_entry* p);
extern void robot_entry_join(struct robot_entry* p);
   

#endif//__robot_entry_h_