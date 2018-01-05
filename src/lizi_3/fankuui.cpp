#include "fankuui.h"
#include "net/mm_streambuf_packet.h"
#include <string>
#include "core/mm_os_context.h"
static void* __static_fan_num_wait_thread(void* arg)
{
	struct lj_lizhi3::fan_num* p = (struct lj_lizhi3::fan_num*)(arg);
	lj_lizhi3::fan_num_wait(p);
	return NULL;
}

static void fan_num_huidiaotifun_queshen(void* obj, void* u, struct mm_packet* pack)
{

}

static void fan_tcp_back_broken(void* obj)
{

}


void lj_lizhi3::fan_num_init(struct fan_num* p)
{
	struct mm_os_context* g_os_context = mm_os_context_instance();
	mm_os_context_init(g_os_context);
	mm_os_context_perform(g_os_context);
	mm_net_tcp_init(&p->fw_net_1);
	mm_mailbox_init(&p->fw_net_0);
	p->state = ts_closed;

}
void lj_lizhi3::fan_num_destroy(struct fan_num* p)
{
	mm_net_tcp_destroy(&p->fw_net_1);
	mm_mailbox_destroy(&p->fw_net_0);
	p->state = ts_closed;
	struct mm_os_context* g_os_context = mm_os_context_instance();
	mm_os_context_destroy(g_os_context);
}
///////////////////////////////////////////////////
void lj_lizhi3::fan_num_fuzhi(struct fan_num* p)
{

	////////////////////////////////////////////////////////////////////////
	struct mm_mailbox_callback mm_mailbox_callback;
	mm_mailbox_callback_init(&mm_mailbox_callback);
	mm_mailbox_callback.handle=&fan_num_huidiaotifun_queshen;
	mm_mailbox_callback.broken=&fan_tcp_back_broken;
	mm_mailbox_callback.finish=&fan_tcp_back_broken;
	mm_mailbox_callback.nready=&fan_tcp_back_broken;
	mm_mailbox_callback.obj=p;
	mm_mailbox_assign_native(&p->fw_net_0,"0.0.0.0",55000);
	mm_mailbox_assign_callback(&p->fw_net_0,10086,&fan_num_huidiaotifun_fw);
	mm_mailbox_assign_mailbox_callback(&p->fw_net_0,&mm_mailbox_callback);
	mm_mailbox_set_length(&p->fw_net_0,1);
	mm_mailbox_assign_context(&p->fw_net_0,p);
	mm_mailbox_fopen_socket(&p->fw_net_0);
	mm_mailbox_bind(&p->fw_net_0);
	mm_mailbox_listen(&p->fw_net_0);
	mm_mailbox_callback_destroy(&mm_mailbox_callback);

	struct mm_net_tcp_callback net_tcp_callback;
	mm_net_tcp_callback_init(&net_tcp_callback);
	net_tcp_callback.handle=&fan_num_huidiaotifun_queshen;
	net_tcp_callback.obj=p;
	net_tcp_callback.broken=&fan_tcp_back_broken;
	net_tcp_callback.finish=&fan_tcp_back_broken;
	net_tcp_callback.nready=&fan_tcp_back_broken;
	mm_net_tcp_assign_remote(&p->fw_net_1,"127.0.0.1",55000);
	mm_net_tcp_assign_callback(&p->fw_net_1,10087,&fan_num_huidiaotifun);
	mm_net_tcp_assign_net_tcp_callback(&p->fw_net_1,&net_tcp_callback);
	mm_net_tcp_assign_context(&p->fw_net_1,p);
	mm_net_tcp_fopen_socket(&p->fw_net_1);
	mm_net_tcp_connect(&p->fw_net_1);
	mm_net_tcp_callback_destroy(&net_tcp_callback);
}

void lj_lizhi3::fan_num_start(struct fan_num* p)
{	
	mm_net_tcp_start(&p->fw_net_1);
	mm_mailbox_start(&p->fw_net_0);
	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_fan_num_wait_thread, p);
}

void lj_lizhi3::fan_num_wait(struct fan_num* p)
{
	while( ts_motion == p->state )
	{
		mm_msleep(300);

		char buffer[5] = "aad";

		mm_net_tcp_lock(&p->fw_net_1);

		struct mm_packet rq_pack;
		struct mm_packet_head phead;
		mm_tcp* tcp = &p->fw_net_1.tcp;
		rq_pack.hbuff.length = MM_MSG_COMM_HEAD_SIZE;
		rq_pack.bbuff.length = 5;
		mm_streambuf_packet_overdraft(&tcp->buff_send, &rq_pack);
		mm_packet_head_base_zero(&rq_pack);
		rq_pack.phead.mid = 10086;
		rq_pack.phead.uid = 0;
		mm_packet_head_encode(&rq_pack, &rq_pack.hbuff, &phead);
		mm_memcpy((void*)(rq_pack.bbuff.buffer + rq_pack.bbuff.offset), (void*)buffer, rq_pack.bbuff.length);

		mm_net_tcp_flush_send(&p->fw_net_1);
		mm_net_tcp_unlock(&p->fw_net_1);
		//////////////////////////////////////////////////////////////////////////////////////////
	}
}




void lj_lizhi3::fan_num_interrupt(struct fan_num* p)
{
	mm_net_tcp_interrupt(&p->fw_net_1);
	mm_mailbox_interrupt(&p->fw_net_0);
	p->state = ts_closed;
}
void lj_lizhi3::fan_num_shutdown(struct fan_num* p)
{
	mm_net_tcp_shutdown(&p->fw_net_1);
	mm_mailbox_shutdown(&p->fw_net_0);
	p->state = ts_finish;
}
void lj_lizhi3::fan_num_join(struct fan_num* p)
{
	mm_net_tcp_join(&p->fw_net_1);
	mm_mailbox_join(&p->fw_net_0);
	mm_net_tcp_close_socket(&p->fw_net_1);
	mm_mailbox_close_socket(&p->fw_net_0);
}


void lj_lizhi3::fankui(struct fan_num* p)
{


}

void lj_lizhi3::fan_num_huidiaotifun(void* obj, void* u, struct mm_packet* rs_pack)
{
	std::string rs_buffer((const char*)(rs_pack->bbuff.buffer + rs_pack->bbuff.offset), rs_pack->bbuff.length);
	printf("%s\n", rs_buffer.c_str());
}


void lj_lizhi3::fan_num_huidiaotifun_fw(void* obj, void* u, struct mm_packet* rq_pack)
{
	struct fan_num* mmbox = (struct fan_num*)u;
	std::string rq_buffer((const char*)(rq_pack->bbuff.buffer + rq_pack->bbuff.offset), rq_pack->bbuff.length);
	printf("%s\n", rq_buffer.c_str());

	// uid -> tcp fd
	mm_tcp* tcp_nt = mm_mailbox_get(&mmbox->fw_net_0, 10000);

	mm_tcp* tcp = (mm_tcp*)obj;
	mm_tcp_lock(tcp);

	char buffer[5] = "fwq";
	struct mm_packet rs_pack;
	struct mm_packet_head phead;
	rs_pack.hbuff.length = rq_pack->hbuff.length;
	rs_pack.bbuff.length = 5;
	mm_streambuf_packet_overdraft(&tcp->buff_send, &rs_pack);
	mm_packet_head_base_copy(&rs_pack,rq_pack);
	rs_pack.phead.mid = 10087;
	mm_packet_head_encode(&rs_pack, &rs_pack.hbuff, &phead);
	mm_memcpy((void*)(rs_pack.bbuff.buffer + rs_pack.bbuff.offset), (void*)buffer, rs_pack.bbuff.length);

	mm_tcp_flush_send(tcp);
	mm_tcp_unlock(tcp);
}