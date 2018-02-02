#include "application.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "pthread.h"
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "lizi_5\logger_file.h"



static void* __static_uuu_poll_wait_thread(void* arg);
static void handle_robot_add(void* obj, void* u, std::string* pack)
{

}



void application_init(struct application* p)
{	
	logger_file_init();
	zixiancheng_init(&p->x1);
	p->state = ts_closed;
	logger_file_assign_file("D:/github/tvkoooo/accc/src/lizi_5/","ddd_log.log");
	logger_file_assign_file_size(2048*30);
}
void application_destroy(struct application* p)
{
	logger_file_destroy();
	zixiancheng_destroy(&p->x1);
	p->state = ts_closed;
}

void application_fuzhi(struct application* p,int argc,char **argv)
{

	p->x1.d_callback.handle=&handle_robot_add;
	p->x1.d_callback.obj=p;

}

void zixiancheng_poll_wait(struct application* p)
{
	int quit=0;
	void * p_quit=&quit;



	while( ts_motion == p->state && 50 >quit )
	{
		quit++;

		Sleep(100);
		std::string mmmmm="sdfsfsdfsdfds";
		logger_file_log(OO_Info,"nimei=%s",mmmmm.c_str());
	}

}

void application_start(struct application* p)
{	
	zixiancheng_start(&p->x1);
	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
}

void application_interrupt(struct application* p)
{
	zixiancheng_interrupt(&p->x1);
	p->state = ts_closed;
}
void application_shutdown(struct application* p)
{
	zixiancheng_shutdown(&p->x1);
	p->state = ts_finish;
}
void application_join(struct application* p)
{
	zixiancheng_join(&p->x1);
	pthread_join(p->poll_thread, NULL);
}

static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct application* p = (struct application*)(arg);
	zixiancheng_poll_wait(p);
	return NULL;
}