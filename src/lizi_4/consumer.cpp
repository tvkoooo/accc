#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"
#include "consumer.h"
#include "producer.h"


static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct consumer* p = (struct consumer*)(arg);
	consumer_poll_wait(p);
	return NULL;
}


void consumer_shujuchuandi(struct consumer* p,int mes[5])
{

	for (int i=0;i<5;i++)
	{
		pthread_mutex_lock(&p->mute_save);
		p->save[i]=mes[i];
		pthread_mutex_unlock(&p->mute_save);
	}
	printf("\n shujuchuandi DATA:%d\t%d\t%d\t%d\t%d\n",p->save[0],p->save[1],p->save[2],p->save[3],p->save[4]);
}


void consumer_init(struct consumer* p)
{
	p->save[0]=p->save[1]=p->save[2]=p->save[3]=p->save[4]=0;
	consumer_pthread_mutex_init(p);
	p->state = ts_closed;
}
void consumer_destroy(struct consumer* p)
{
	p->save[0]=p->save[1]=p->save[2]=p->save[3]=p->save[4]=0;
	consumer_pthread_mutex_destroy(p);
	p->state = ts_closed;

}

void consumer_pthread_mutex_init(struct consumer* p)
{
	pthread_mutex_init(&p->mute_save,NULL);
}

void consumer_pthread_mutex_destroy(struct consumer *p)
{
	pthread_mutex_destroy(&p->mute_save);
}

void consumer_poll_wait(struct consumer* p)
{
	while( ts_motion == p->state )
	{
		pthread_mutex_lock(&p->mute_save);
		printf("\n consumer DATA:%d\t%d\t%d\t%d\t%d\n",p->save[0],p->save[1],p->save[2],p->save[3],p->save[4]);
		pthread_mutex_unlock(&p->mute_save);
		Sleep(1500);

	}
}

void consumer_start(struct consumer* p)
{
	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
}


void consumer_interrupt(struct consumer* p)
{
		p->state = ts_closed;
}
void consumer_shutdown(struct consumer* p)
{
		p->state = ts_finish;
}
void consumer_join(struct consumer* p)
{
		pthread_join(p->poll_thread, NULL);
}




