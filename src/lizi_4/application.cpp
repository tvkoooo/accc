#include "application.h"

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "pthread.h"
#include "wangluo.h"
#include "platform_config.h"




void application_init(struct application* p)
{
	wangluo_init(&p->w1);
}
void application_destroy(struct application* p)
{
	wangluo_destroy(&p->w1);
}

void application_fuzhi(struct application* p,int argc,char **argv)
{
	wangluo_fuzhi(&p->w1,argc,argv);
}

void application_start(struct application* p)
{	
	wangluo_start(&p->w1);
}
void application_interrupt(struct application* p)
{
	wangluo_interrupt(&p->w1);
}
void application_shutdown(struct application* p)
{

	wangluo_shutdown(&p->w1);
}
void application_join(struct application* p)
{
	wangluo_join(&p->w1);
}
