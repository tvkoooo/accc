#include "kehuduan.h"
#include "net/mm_streambuf_packet.h"
#include <string>
#include "core/mm_os_context.h"
//#include "mm_protobuff_cxx.h"
#include <iostream>
static void* __static_kehuduan_wait_thread(void* arg)
{
	struct kehuduan* p = (struct kehuduan*)(arg);
	kehuduan_wait(p);
	return NULL;
}

static void kehuduan_huidiaotifun_queshen(void* obj, void* u, struct mm_packet* pack)
{

}

static void fan_tcp_back_broken(void* obj)
{

}

static void _static_kehuduan_huidiao_linkd(struct kehuduan* p)
{


}


void kehuduan_init(struct kehuduan* p)
{
	p->user_socket=0;
	mm_mt_contact_init(&p->kh1);
	pthread_mutex_init(&p->t_data,NULL);
	p->state = ts_closed;
}
void kehuduan_destroy(struct kehuduan* p)
{
	p->state = ts_closed;
	pthread_mutex_destroy(&p->t_data);
	mm_mt_contact_destroy(&p->kh1);
	p->user_socket=0;
}
///////////////////////////////////////////////////
void kehuduan_fuzhi(struct kehuduan* p)
{



}

void kehuduan_start(struct kehuduan* p)
{	
	mm_mt_contact_start(&p->kh1);
	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_kehuduan_wait_thread, p);
}

void kehuduan_wait(struct kehuduan* p)
{

	while( ts_motion == p->state )
	{

	}
}

void kehuduan_interrupt(struct kehuduan* p)
{
	mm_mt_contact_interrupt(&p->kh1);
	p->state = ts_closed;
}
void kehuduan_shutdown(struct kehuduan* p)
{
	mm_mt_contact_shutdown(&p->kh1);
	p->state = ts_finish;
}
void kehuduan_join(struct kehuduan* p)
{
	mm_mt_contact_join(&p->kh1);

}




void kehuduan_huidiao_linkd(void* obj, void* u, struct mm_packet* rs_pack)
{

}


