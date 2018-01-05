#include "fuwuqi.h"
#include "net/mm_streambuf_packet.h"
#include <string>
#include "core/mm_os_context.h"
static void* __static_fuwuqi_wait_thread(void* arg)
{
	struct lj_lizhi3::fuwuqi* p = (struct lj_lizhi3::fuwuqi*)(arg);
	lj_lizhi3::fuwuqi_wait(p);
	return NULL;
}

static void fuwuqi_huidiaotifun_queshen(void* obj, void* u, struct mm_packet* pack)
{

}

static void fan_tcp_back_broken(void* obj)
{

}


void lj_lizhi3::fuwuqi_init(struct fuwuqi* p)
{
	p->save_info.clear();
	mm_mailbox_init(&p->fw_net_0);
	p->state = ts_closed;

}
void lj_lizhi3::fuwuqi_destroy(struct fuwuqi* p)
{
	p->save_info.clear();
	mm_mailbox_destroy(&p->fw_net_0);
	p->state = ts_closed;

}
///////////////////////////////////////////////////
void lj_lizhi3::fuwuqi_fuzhi(struct fuwuqi* p)
{

	////////////////////////////////////////////////////////////////////////
	struct mm_mailbox_callback mm_mailbox_callback;
	mm_mailbox_callback_init(&mm_mailbox_callback);
	mm_mailbox_callback.handle=&fuwuqi_huidiaotifun_queshen;
	mm_mailbox_callback.broken=&fan_tcp_back_broken;
	mm_mailbox_callback.finish=&fan_tcp_back_broken;
	mm_mailbox_callback.nready=&fan_tcp_back_broken;
	mm_mailbox_callback.obj=p;
	mm_mailbox_assign_native(&p->fw_net_0,"0.0.0.0",55000);
	mm_mailbox_assign_callback(&p->fw_net_0,10086,&fuwuqi_huidiaotifun_fw);
	mm_mailbox_assign_mailbox_callback(&p->fw_net_0,&mm_mailbox_callback);
	mm_mailbox_set_length(&p->fw_net_0,1);
	mm_mailbox_assign_context(&p->fw_net_0,p);
	mm_mailbox_fopen_socket(&p->fw_net_0);
	mm_mailbox_bind(&p->fw_net_0);
	mm_mailbox_listen(&p->fw_net_0);
	mm_mailbox_callback_destroy(&mm_mailbox_callback);

}

void lj_lizhi3::fuwuqi_start(struct fuwuqi* p)
{	
	mm_mailbox_start(&p->fw_net_0);
	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_fuwuqi_wait_thread, p);
}

void lj_lizhi3::fuwuqi_wait(struct fuwuqi* p)
{
	while( ts_motion == p->state )
	{
		mm_msleep(300);


	}
}




void lj_lizhi3::fuwuqi_interrupt(struct fuwuqi* p)
{

	mm_mailbox_interrupt(&p->fw_net_0);
	p->state = ts_closed;
}
void lj_lizhi3::fuwuqi_shutdown(struct fuwuqi* p)
{

	mm_mailbox_shutdown(&p->fw_net_0);
	p->state = ts_finish;
}
void lj_lizhi3::fuwuqi_join(struct fuwuqi* p)
{

	mm_mailbox_join(&p->fw_net_0);

	mm_mailbox_close_socket(&p->fw_net_0);
}






void lj_lizhi3::fuwuqi_huidiaotifun_fw(void* obj, void* u, struct mm_packet* rq_pack)
{

	struct fuwuqi* mmbox = (struct fuwuqi*)u;
	char send_user[256] = "OK";
	mm_uint32_t   mid=rq_pack->phead.mid;
	mm_uint32_t   pid=rq_pack->phead.pid;
	mm_uint64_t   sid=rq_pack->phead.sid;
	mm_uint64_t   uid=rq_pack->phead.uid;

	printf("fwq:the massege:%I64d send to %I64d\n",uid,sid);
	std::string rq_buffer((const char*)(rq_pack->bbuff.buffer + rq_pack->bbuff.offset), rq_pack->bbuff.length);
	printf("fwq:the massege is:%s\n", rq_buffer.c_str());
	
	mm_tcp* tcp = (mm_tcp*)obj;
	mm_tcp_lock(tcp);
	mm_socket_t fd,fd_to;
	fd=fd_to=0;
	fd=tcp->socket.socket;
/////////////////////////////////////////////////////////////////
	map_type::iterator it = mmbox->save_info.find(uid);
	if (it == mmbox->save_info.end())
	{

		//没有找到
		mmbox->save_info.insert(map_type::value_type(uid,fd));
	}
	else
	{
		//找到
		//it->first;
		//it->second;
	}
////////////////////////////////////////////////////////////////////
	if (0==sid)
	{
		mm_memcpy(send_user,"Welcome",256);
	}
	map_type::iterator it_to = mmbox->save_info.find(sid);
	if (it_to == mmbox->save_info.end())
	{

		//没有找到
		mm_memcpy(send_user,"It not here",256);

	}
	else
	{
		//找到
		mm_memcpy(send_user,"Please weit",256);
		fd_to=it_to->second;
	}
/////////////////////////////////////////////////////////////////////
	struct mm_packet rs_pack;
	struct mm_packet_head phead;
	rs_pack.hbuff.length = rq_pack->hbuff.length;
	rs_pack.bbuff.length = 256;
	mm_streambuf_packet_overdraft(&tcp->buff_send, &rs_pack);
	mm_packet_head_base_copy(&rs_pack,rq_pack);
	rs_pack.phead.mid = 10087;
	mm_packet_head_encode(&rs_pack, &rs_pack.hbuff, &phead);
	mm_memcpy((void*)(rs_pack.bbuff.buffer + rs_pack.bbuff.offset), (void*)send_user, rs_pack.bbuff.length);

	mm_tcp_flush_send(tcp);
	mm_tcp_unlock(tcp);
/////////////////////////////////////////////////////////////////////
	//mm_msleep(200);
	//mm_tcp_lock(tcp);
	//char send_user2[100] = "fwq:de er ci fa song ";
	//struct mm_packet rs_pack2;
	//struct mm_packet_head phead2;
	//rs_pack2.hbuff.length = rq_pack->hbuff.length;
	//rs_pack2.bbuff.length = 20;
	//mm_streambuf_packet_overdraft(&tcp->buff_send, &rs_pack2);
	//mm_packet_head_base_copy(&rs_pack2,rq_pack);
	//rs_pack2.phead.mid = 10087;
	//mm_packet_head_encode(&rs_pack2, &rs_pack2.hbuff, &phead2);
	//mm_memcpy((void*)(rs_pack2.bbuff.buffer + rs_pack2.bbuff.offset), (void*)send_user2, rs_pack2.bbuff.length);

	//mm_tcp_flush_send(tcp);
	//mm_tcp_unlock(tcp);
	

////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////

	if (0!=fd_to)
	{
		char buffer_to[256] = "i fuck you ";
		mm_memcpy(buffer_to,rq_buffer.c_str(),rq_buffer.length());
		mm_tcp* tcp_to=mm_mailbox_get(&mmbox->fw_net_0,fd_to);
		mm_tcp_lock(tcp_to);

		struct mm_packet rs_pack_to;
		struct mm_packet_head phead_to;
		rs_pack_to.hbuff.length = rq_pack->hbuff.length;
		rs_pack_to.bbuff.length = 256;
		mm_streambuf_packet_overdraft(&tcp_to->buff_send, &rs_pack_to);
		mm_packet_head_base_copy(&rs_pack_to,rq_pack);
		rs_pack_to.phead.mid = 10087;
		mm_packet_head_encode(&rs_pack_to, &rs_pack_to.hbuff, &phead_to);
		mm_memcpy((void*)(rs_pack_to.bbuff.buffer + rs_pack_to.bbuff.offset), (void*)buffer_to, rs_pack_to.bbuff.length);

		mm_tcp_flush_send(tcp_to);
		mm_tcp_unlock(tcp_to);
	}

}