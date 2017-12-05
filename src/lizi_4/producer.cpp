#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"
#include "producer.h"

static void huidiao( void* obj, int mes[5])
{

}


static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct producer* p = (struct producer*)(arg);
	producer_poll_wait(p);
	return NULL;
}

void producer_callback_init(struct producer_callback* p)
{
	p->handle=huidiao;
	p->obj=NULL;
}
void producer_callback_destroy(struct producer_callback* p)
{
	p->handle=huidiao;
	p->obj=NULL;
}


void producer_init(struct producer* p)
{
	memset(p->mes,0,sizeof(int)*5);
	producer_callback_init(&p->callback);
	p->state = ts_closed;

}
void producer_destroy(struct producer* p)
{
	memset(p->mes,0,sizeof(int)*5);
	producer_callback_destroy(&p->callback);
	p->state = ts_closed;

}


void producer_setcallback(struct producer* p,struct producer_callback* pp)
{
	p->callback=*pp;
}

void producer_poll_wait(struct producer* p)
{

	int i,j=0;
	while( ts_motion == p->state )
	{
		for (i=0;i<6;i++)
		{
			if (i<5)
			{
			p->mes[i]=i+j;
			}
		}
		j++;
		i=i%6;
		printf("\n producer DATA:%d\t%d\t%d\t%d\t%d\n",p->mes[0],p->mes[1],p->mes[2],p->mes[3],p->mes[4]);
		(*(p->callback.handle))(p,p->mes);
		Sleep(3000);
	}
}

void producer_start(struct producer* p)
{
	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
}


void producer_interrupt(struct producer* p)
{
		p->state = ts_closed;
}
void producer_shutdown(struct producer* p)
{
		p->state = ts_finish;
}
void producer_join(struct producer* p)
{
		pthread_join(p->poll_thread, NULL);
}




