#include "robot_entry.h"




static void* __static_uuu_poll_wait_thread(void* arg);

void robot_entry_init(struct robot_entry* p)
{

	p->state = ts_closed;


}
void robot_entry_destroy(struct robot_entry* p)
{

	p->state = ts_closed;

}
//
void robot_entry_poll_wait(struct robot_entry* p)
{


	while( ts_motion == p->state )
	{

	}

}
//
void robot_entry_start(struct robot_entry* p)
{

	p->state = ts_finish == p->state ? ts_closed : ts_motion;

	pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
}
void robot_entry_interrupt(struct robot_entry* p)
{
	p->state = ts_closed;
}
void robot_entry_shutdown(struct robot_entry* p)
{


	p->state = ts_finish;

}
void robot_entry_join(struct robot_entry* p)
{

	pthread_join(p->poll_thread, NULL);
}
static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct robot_entry* p = (struct robot_entry*)(arg);
	robot_entry_poll_wait(p);
	return NULL;
}