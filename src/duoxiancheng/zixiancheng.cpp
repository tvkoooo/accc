#include "zixiancheng.h"
#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include <string>
#include <iostream>

static void __static_zixiancheng_handle_callback(void* obj, void* u, std::string* pack)
{
}

static void* __static_uuu_poll_wait_thread(void* arg);

extern void zixiancheng_callback_init(struct zixiancheng_callback* p)
{
	p->handle=__static_zixiancheng_handle_callback;
	p->obj=NULL;
}
extern void zixiancheng_callback_destroy(struct zixiancheng_callback* p)
{
	p->handle=__static_zixiancheng_handle_callback;
	p->obj=NULL;
}

void zixiancheng_init(struct zixiancheng* p)
{
	zixiancheng_callback_init(&p->d_callback);
	p->obj=NULL;
	p->pack="";
	p->state = ts_closed;
}
void zixiancheng_destroy(struct zixiancheng* p)
{
	zixiancheng_callback_destroy(&p->d_callback);
	p->obj=NULL;
	p->pack="";
	p->state = ts_closed;
}
//
void application_fuzhi(struct zixiancheng* p,handle_callback handle,void *obj , void *u,std::string* pack)
{
	p->d_callback.handle=handle;
	p->d_callback.obj=obj;
	p->obj=u;
	p->pack=*pack;
}
void zixiancheng_poll_wait(struct zixiancheng* p)
{
	int quit=0;

	while( ts_motion == p->state || quit==60)
	{

		(*(p->d_callback.handle))(p->d_callback.obj,p->obj,&p->pack);
		quit++;
	}
}
//
void zixiancheng_start(struct zixiancheng* p)
{

	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
}
void zixiancheng_interrupt(struct zixiancheng* p)
{
	p->state = ts_closed;
}
void zixiancheng_shutdown(struct zixiancheng* p)
{
	p->state = ts_finish;
}
void zixiancheng_join(struct zixiancheng* p)
{
	pthread_join(p->poll_thread, NULL);
}
static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct zixiancheng* p = (struct zixiancheng*)(arg);
	zixiancheng_poll_wait(p);
	return NULL;
}