#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"
#include "builder.h"


//static void* __static_uuu_poll_wait_thread(void* arg)
//{
//	struct builder* p = (struct builder*)(arg);
//	builder_poll_wait(p);
//	return NULL;
//}
void __static_builder_producer_handle( void* obj, int mes[5]);

void builder_init(struct builder* p)
{
	//std::map<int,int,int> luobo;

		producer_init(&p->pro_1);
		consumer_init(&p->con_1);
		//p->state = ts_closed;
	//p->pro_1.producer_fun1=p->con_1.consum;
	//p->pro_1.obj=p->con_1.obj;

		p->pro_1.callback.handle=&__static_builder_producer_handle;
		p->pro_1.callback.obj=&p->con_1;
		producer_setcallback(&p->pro_1,&p->pro_1.callback);
}
void builder_destroy(struct builder* p)
{
		producer_destroy(&p->pro_1);
		consumer_destroy(&p->con_1);
		//p->state = ts_closed;
}

void __static_builder_producer_handle( void* obj, int mes[5])
{
	struct producer* producer = (struct producer*)(obj);
	struct consumer* consumer = (struct consumer*)(producer->callback.obj);
	consumer_shujuchuandi(consumer,mes);
}
//void builder_poll_wait(struct builder* p)
//{
//
//	producer_poll_wait(&p->pro_1);
//	consumer_poll_wait(&p->con_1);
//	while( ts_motion == p->state )
//	{
//
//		Sleep(500);
//	}
//}

void builder_start(struct builder* p)
{
	//p->state = ts_finish == p->state ? ts_closed : ts_motion;

	producer_start(&p->pro_1);
	consumer_start(&p->con_1);
	//pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
}


void builder_interrupt(struct builder* p)
{
		//p->state = ts_closed;
		producer_interrupt(&p->pro_1);
		consumer_interrupt(&p->con_1);
}
void builder_shutdown(struct builder* p)
{
		//p->state = ts_finish;
		producer_shutdown(&p->pro_1);
		consumer_shutdown(&p->con_1);
}
void builder_join(struct builder* p)
{
		//pthread_join(p->poll_thread, NULL);
	producer_join(&p->pro_1);
	consumer_join(&p->con_1);
}




