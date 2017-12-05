#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"
#include "wangluo_fw.h"


//static void* __static_uuu_poll_wait_thread(void* arg)
//{
//	struct wangluo_fw* p = (struct wangluo_fw*)(arg);
//	wangluo_fw_poll_wait(p);
//	return NULL;
//}

void __static_builder_producer_handle(void* obj, socket_type sClient);

void wangluo_fw_init(struct wangluo_fw* p)
{
	wangluo_fw_accept_init(&p->fw_ac_1);
	wangluo_fw_sel_init(&p->fw_sel_1);
	//
	p->fw_ac_1.callback.handle=&__static_builder_producer_handle;
	p->fw_ac_1.callback.obj=&p->fw_sel_1;
	wangluo_fw_accept_setcallback(&p->fw_ac_1,&p->fw_ac_1.callback);
}
void wangluo_fw_destroy(struct wangluo_fw* p)
{
	wangluo_fw_accept_destroy(&p->fw_ac_1);
	wangluo_fw_sel_destroy(&p->fw_sel_1);
}
//
void __static_builder_producer_handle(void* obj, socket_type sClient)
{
	struct wangluo_fw_accept* producer = (struct wangluo_fw_accept*)(obj);
	struct wangluo_fw_sel* consumer = (struct wangluo_fw_sel*)(producer->callback.obj);
	wangluo_fw_sel_shujuchuandi(consumer,sClient);
}
//

void wangluo_fw_start(struct wangluo_fw* p)
{	
	wangluo_fw_accept_start(&p->fw_ac_1);
	wangluo_fw_sel_start(&p->fw_sel_1);
}
void wangluo_fw_interrupt(struct wangluo_fw* p)
{
	wangluo_fw_accept_interrupt(&p->fw_ac_1);
	wangluo_fw_sel_interrupt(&p->fw_sel_1);
}
void wangluo_fw_shutdown(struct wangluo_fw* p)
{
	wangluo_fw_accept_shutdown(&p->fw_ac_1);
	wangluo_fw_sel_shutdown(&p->fw_sel_1);


}
void wangluo_fw_join(struct wangluo_fw* p)
{
	wangluo_fw_accept_join(&p->fw_ac_1);
	wangluo_fw_sel_join(&p->fw_sel_1);

}
