#include "application.h"

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "pthread.h"
#include "wangluo_fw.h"
#include "platform_config.h"
#include "wangluo_kh.h"




void application_init(struct application* p)
{
	wangluo_fw_init(&p->fw1);
	wangluo_kh_init(&p->kh1);
}
void application_destroy(struct application* p)
{
	wangluo_fw_destroy(&p->fw1);
	wangluo_kh_destroy(&p->kh1);

}

void application_fuzhi(struct application* p,int argc,char **argv)
{

}

void application_start(struct application* p)
{	
	//lizhi4_wangluo_fw_fuwu_open(&p->wk1);
	wangluo_fw_start(&p->fw1);
	wangluo_kh_start(&p->kh1);

}

void application_wait(struct application* p)
{	
	//lizhi4_wangluo_fw_fuwu_open(&p->wk1);
	wangluo_fw_poll_wait(&p->fw1);
	wangluo_kh_poll_wait(&p->kh1);

}





void application_interrupt(struct application* p)
{
	wangluo_fw_interrupt(&p->fw1);
	wangluo_kh_interrupt(&p->kh1);

}
void application_shutdown(struct application* p)
{
	printf("Ctrl+c ½øÐÐÖÐ¶Ï\n");
	wangluo_fw_shutdown(&p->fw1);
	wangluo_kh_shutdown(&p->kh1);

}
void application_join(struct application* p)
{
	wangluo_fw_join(&p->fw1);
	wangluo_kh_join(&p->kh1);

	//lizhi4_wangluo_fw_fuwu_close(&p->wk1);
}
