#include "kehuduan.h"
#include "net/mm_streambuf_packet.h"
#include <string>
#include "core/mm_os_context.h"
static void* __static_kehuduan_wait_thread(void* arg)
{
	struct lj_lizhi3::kehuduan* p = (struct lj_lizhi3::kehuduan*)(arg);
	lj_lizhi3::kehuduan_wait(p);
	return NULL;
}

static void kehuduan_huidiaotifun_queshen(void* obj, void* u, struct mm_packet* pack)
{

}

static void fan_tcp_back_broken(void* obj)
{

}


void lj_lizhi3::kehuduan_init(struct kehuduan* p)
{
	p->state_say_next=0;
	mm_memset(p->syst_say1,0,256);
	mm_memset(p->syst_say2,0,256);
	p->sid=0;
	p->uid=0;
	mm_net_tcp_init(&p->fw_net_1);

	p->state = ts_closed;

}
void lj_lizhi3::kehuduan_destroy(struct kehuduan* p)
{
	p->state_say_next=0;
	mm_memset(p->syst_say1,0,256);
	mm_memset(p->syst_say2,0,256);
	p->sid=0;
	p->uid=0;
	mm_net_tcp_destroy(&p->fw_net_1);
	p->state = ts_closed;

}
///////////////////////////////////////////////////
void lj_lizhi3::kehuduan_fuzhi(struct kehuduan* p)
{
	mm_msleep(300);
	mm_memcpy(p->syst_say1,"It not here",256);
	mm_memcpy(p->syst_say2,"Please weit",256);	
	mm_uint64_t uid=1111;
	printf("your uid=:");
	scanf("%d",&uid);
	printf("your uid=:%d\n",uid);
	p->uid=uid;

	struct mm_net_tcp_callback net_tcp_callback;
	mm_net_tcp_callback_init(&net_tcp_callback);
	net_tcp_callback.handle=&kehuduan_huidiaotifun_queshen;
	net_tcp_callback.obj=p;
	net_tcp_callback.broken=&fan_tcp_back_broken;
	net_tcp_callback.finish=&fan_tcp_back_broken;
	net_tcp_callback.nready=&fan_tcp_back_broken;
	mm_net_tcp_assign_remote(&p->fw_net_1,"127.0.0.1",55000);
	mm_net_tcp_assign_callback(&p->fw_net_1,10087,&kehuduan_huidiaotifun);
	mm_net_tcp_assign_net_tcp_callback(&p->fw_net_1,&net_tcp_callback);
	mm_net_tcp_assign_context(&p->fw_net_1,p);
	mm_net_tcp_fopen_socket(&p->fw_net_1);
	mm_net_tcp_connect(&p->fw_net_1);
	mm_net_tcp_callback_destroy(&net_tcp_callback);
}

void lj_lizhi3::kehuduan_start(struct kehuduan* p)
{	
	mm_net_tcp_start(&p->fw_net_1);
	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_kehuduan_wait_thread, p);
}

void lj_lizhi3::kehuduan_wait(struct kehuduan* p)
{
	while( ts_motion == p->state )
	{
		mm_msleep(100);

		mm_uint64_t sid=1111;
		char massage[256] = "null";

		if (0==p->state_say_next||1==p->state_say_next)
		{
			printf("your message=:");
			scanf(" %[^\n]",massage);
		}
		else
		{
			printf("\n我=>");
			scanf(" %[^\n]",massage);
		}

		//printf("kehu:your message=:%s\n",massage);
		if (0==p->state_say_next||1==p->state_say_next)
		{
			printf("Who do you send to [sid]:");
			scanf("%d",&sid);
			//printf("kehu:you send pid=:%d\n",sid);
			p->sid=sid;
		}

		mm_net_tcp_lock(&p->fw_net_1);
		struct mm_packet rq_pack;
		struct mm_packet_head phead;
		mm_tcp* tcp = &p->fw_net_1.tcp;
		rq_pack.hbuff.length = MM_MSG_COMM_HEAD_SIZE;
		rq_pack.bbuff.length = 256;
		mm_streambuf_packet_overdraft(&tcp->buff_send, &rq_pack);
		mm_packet_head_base_zero(&rq_pack);
		rq_pack.phead.mid = 10086;
		rq_pack.phead.uid = p->uid;
		rq_pack.phead.sid = p->sid;
		mm_packet_head_encode(&rq_pack, &rq_pack.hbuff, &phead);
		mm_memcpy((void*)(rq_pack.bbuff.buffer + rq_pack.bbuff.offset), (void*)massage, rq_pack.bbuff.length);

		mm_net_tcp_flush_send(&p->fw_net_1);
		mm_net_tcp_unlock(&p->fw_net_1);
		mm_msleep(400);


		//////////////////////////////////////////////////////////////////////////////////////////
	}
}




void lj_lizhi3::kehuduan_interrupt(struct kehuduan* p)
{
	mm_net_tcp_interrupt(&p->fw_net_1);

	p->state = ts_closed;
}
void lj_lizhi3::kehuduan_shutdown(struct kehuduan* p)
{
	mm_net_tcp_shutdown(&p->fw_net_1);

	p->state = ts_finish;
}
void lj_lizhi3::kehuduan_join(struct kehuduan* p)
{
	mm_net_tcp_join(&p->fw_net_1);

	mm_net_tcp_close_socket(&p->fw_net_1);

}




void lj_lizhi3::kehuduan_huidiaotifun(void* obj, void* u, struct mm_packet* rs_pack)
{
	struct kehuduan* mmbox = (struct kehuduan*)u;
	std::string rs_buffer((const char*)(rs_pack->bbuff.buffer + rs_pack->bbuff.offset), rs_pack->bbuff.length);

	if (0!=strcmp(mmbox->syst_say2,rs_buffer.c_str()))
	{
		printf("\n基友=>:%s\n", rs_buffer.c_str());
	}
	if (0==strcmp(mmbox->syst_say1,rs_buffer.c_str()))
	{
		mmbox->state_say_next=1;

	}
	if (0==strcmp(mmbox->syst_say2,rs_buffer.c_str()))
	{
		mmbox->state_say_next=2;
	}
}


