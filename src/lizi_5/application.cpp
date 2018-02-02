#include "application.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "pthread.h"
#include "platform_config.h"
#include "robot_queue_data.h"





void application_init(struct application* p)
{
	
	wangluo_kehu_init(&p->khtest);

}
void application_destroy(struct application* p)
{
	
	wangluo_kehu_destroy(&p->khtest);
}

void application_fuzhi(struct application* p,int argc,char **argv)
{


}

void application_start(struct application* p)
{	

	//oo::robot_queue_data da1,da2;
	//da1.set_sid(46165498);
	//da1.set_cnt(100);
	//da1.set_num(5);
	//da1.data_encode();
	//std::string out_pack=da1.get_pack();
	//da2.set_pack(out_pack);
	//da2.data_decode();
	//uint32_t sid_d2=da2.get_sid();
	//uint32_t cnt_d2=da2.get_cnt();
	//uint32_t num_d2=da2.get_num();

	//printf("%ld %ld %ld ",sid_d2,cnt_d2,num_d2);

	wangluo_kehu_start(&p->khtest);

}

void application_interrupt(struct application* p)
{

	wangluo_kehu_interrupt(&p->khtest);
}
void application_shutdown(struct application* p)
{

	wangluo_kehu_shutdown(&p->khtest);

}
void application_join(struct application* p)
{

	wangluo_kehu_join(&p->khtest);
}
