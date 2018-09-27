#include "kehuduan.h"
#include "net/mm_streambuf_packet.h"
#include <string>
#include "core/mm_os_context.h"
#include "mm_protobuff_cxx.h"
#include <iostream>
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

static void lj_lizhi3::_static_kehuduan_login_send(struct kehuduan* p)
{

	mm_net_tcp_lock(&p->fw_net_1);
	mm_net_tcp_check(&p->fw_net_1);
	mm_net_tcp_unlock(&p->fw_net_1);
	if (0 != mm_net_tcp_finally_state(&p->fw_net_1))
	{
		// 如果连接不成功，返回
		return;
	}

	c_dialogue_com::dialogue_login_rq login_rq;
	struct mm_packet rq_pack;
	std::string user_nick;
	std::string user_password;

	printf("Your nick:");
	std::cin>>user_nick;
	printf("Your password:");
	std::cin>>user_password;
	login_rq.set_user_nick(user_nick);
	login_rq.set_user_password(user_password);
	rq_pack.phead.mid = c_dialogue_com::dialogue_login_rq_msg_id;
	rq_pack.hbuff.length=MM_MSG_COMM_HEAD_SIZE;
	rq_pack.bbuff.length=login_rq.ByteSizeLong();
	pthread_mutex_lock(&p->t_data);
	rq_pack.phead.uid=p->uid;
	pthread_mutex_unlock(&p->t_data);
	mm_net_tcp_lock(&p->fw_net_1);
	//int error_login_send=mm_protobuf_cxx_q_tcp_append_rq(&p->fw_net_1,rq_pack.phead.uid,rq_pack.phead.mid,&login_rq,&rq_pack);
	mm_net_tcp_unlock(&p->fw_net_1);
	if (error_login_send)
	{
		return;
	}
	mm_net_tcp_lock(&p->fw_net_1);
	mm_protobuf_cxx_n_net_tcp_flush_send(&p->fw_net_1);
	mm_net_tcp_unlock(&p->fw_net_1);

	pthread_mutex_lock(&p->t_data);
	p->system_state=28;
	pthread_mutex_unlock(&p->t_data);
}

static void lj_lizhi3::_static_kehuduan_seek_send(struct kehuduan* p)
{
	mm_net_tcp_lock(&p->fw_net_1);
	mm_net_tcp_check(&p->fw_net_1);
	mm_net_tcp_unlock(&p->fw_net_1);
	if (0 != mm_net_tcp_finally_state(&p->fw_net_1))
	{
		// 如果连接不成功，返回
		return;
	}
	struct mm_packet rq_pack;
	c_dialogue_com::dialogue_seek_rq seek_rq;
	seek_rq.Clear();
	std::string to_user_nick="";
	printf("你想找谁吹牛:");
	std::cin>>to_user_nick;

	pthread_mutex_lock(&p->t_data);
	seek_rq.set_user_id(p->uid);
	seek_rq.set_user_nick(p->user_nick);
	seek_rq.set_socket(p->user_socket);
	pthread_mutex_unlock(&p->t_data);

	seek_rq.set_to_user_id(0);
	seek_rq.set_to_user_nick(to_user_nick);
	rq_pack.phead.mid = c_dialogue_com::dialogue_seek_rq_msg_id;
	rq_pack.hbuff.length=MM_MSG_COMM_HEAD_SIZE;
	rq_pack.bbuff.length=seek_rq.ByteSizeLong();
	mm_net_tcp_lock(&p->fw_net_1);
	//int error_login_send=mm_protobuf_cxx_q_tcp_append_rq(&p->fw_net_1,rq_pack.phead.uid,rq_pack.phead.mid,&seek_rq,&rq_pack);
	mm_net_tcp_unlock(&p->fw_net_1);
	if (error_login_send)
	{
		return;
	}
	mm_net_tcp_lock(&p->fw_net_1);
	mm_protobuf_cxx_n_net_tcp_flush_send(&p->fw_net_1);
	mm_net_tcp_unlock(&p->fw_net_1);
	pthread_mutex_lock(&p->t_data);
	p->system_state=28;
	pthread_mutex_unlock(&p->t_data);
}

static void lj_lizhi3::_static_kehuduan_talk_send(struct kehuduan* p)
{
	mm_net_tcp_lock(&p->fw_net_1);
	mm_net_tcp_check(&p->fw_net_1);
	mm_net_tcp_unlock(&p->fw_net_1);
	if (0 != mm_net_tcp_finally_state(&p->fw_net_1))
	{
		// 如果连接不成功，返回
		return;
	}

	struct mm_packet rq_pack;
	c_dialogue_com::dialogue_talk_rq talk_rq;
	talk_rq.Clear();
	std::string talking="";
	std::cin>>talking;

	pthread_mutex_lock(&p->t_data);
	if (101==p->system_state)
	{
		printf("你基友还没有回复，是不是等等");
	}
	talk_rq.set_user_id(p->uid);
	talk_rq.set_user_nick(p->user_nick);
	talk_rq.set_socket(p->user_socket);
	talk_rq.set_to_user_id(p->to_uid);
	talk_rq.set_to_user_nick(p->to_user_nick);
	talk_rq.set_talking(talking);
	talk_rq.set_socket(p->user_socket);
	talk_rq.set_to_socket(p->to_user_socket);
	pthread_mutex_unlock(&p->t_data);

	rq_pack.phead.mid = c_dialogue_com::dialogue_talk_rq_msg_id;
	rq_pack.hbuff.length=MM_MSG_COMM_HEAD_SIZE;
	rq_pack.bbuff.length=talk_rq.ByteSizeLong();
	mm_net_tcp_lock(&p->fw_net_1);
	//int error_login_send=mm_protobuf_cxx_q_tcp_append_rq(&p->fw_net_1,rq_pack.phead.uid,rq_pack.phead.mid,&talk_rq,&rq_pack);
	mm_net_tcp_unlock(&p->fw_net_1);
	if (error_login_send)
	{
		return;
	}

	mm_net_tcp_lock(&p->fw_net_1);
	mm_protobuf_cxx_n_net_tcp_flush_send(&p->fw_net_1);
	mm_net_tcp_unlock(&p->fw_net_1);
	pthread_mutex_lock(&p->t_data);
	p->system_state=28;
	pthread_mutex_unlock(&p->t_data);
}

void lj_lizhi3::kehuduan_init(struct kehuduan* p)
{
	p->system_state=0;
	p->uid=0;
	p->user_nick="";
	p->to_uid=0;
	p->to_user_nick="";
	p->user_socket=0;
	p->to_user_socket=0;
	p->system_state=0;

	pthread_mutex_init(&p->t_data,NULL);
	mm_net_tcp_init(&p->fw_net_1);
	p->state = ts_closed;

}
void lj_lizhi3::kehuduan_destroy(struct kehuduan* p)
{
	p->system_state=0;
	p->uid=0;
	p->user_nick="";
	p->to_uid=0;
	p->to_user_nick="";
	p->user_socket=0;
	p->to_user_socket=0;
	p->system_state=0;

	pthread_mutex_destroy(&p->t_data);
	mm_net_tcp_destroy(&p->fw_net_1);
	p->state = ts_closed;

}
///////////////////////////////////////////////////
void lj_lizhi3::kehuduan_fuzhi(struct kehuduan* p)
{
	struct mm_net_tcp_callback net_tcp_callback;
	mm_net_tcp_callback_init(&net_tcp_callback);
	net_tcp_callback.handle=&kehuduan_huidiaotifun_queshen;
	net_tcp_callback.obj=p;
	net_tcp_callback.broken=&fan_tcp_back_broken;
	net_tcp_callback.finish=&fan_tcp_back_broken;
	net_tcp_callback.nready=&fan_tcp_back_broken;
	mm_net_tcp_assign_remote(&p->fw_net_1,"127.0.0.1",55000);
	mm_net_tcp_assign_callback(&p->fw_net_1,c_dialogue_com::dialogue_login_rs_msg_id,&kehuduan_huidiaoti_dialogue_login);
	mm_net_tcp_assign_callback(&p->fw_net_1,c_dialogue_com::dialogue_seek_rs_msg_id,&kehuduan_huidiaoti_dialogue_seek);
	mm_net_tcp_assign_callback(&p->fw_net_1,c_dialogue_com::dialogue_talk_nt_msg_id,&kehuduan_huidiaoti_dialogue_talk_nt);
	mm_net_tcp_assign_callback(&p->fw_net_1,c_dialogue_com::dialogue_talk_rs_msg_id,&kehuduan_huidiaoti_dialogue_talk_rs);
	mm_net_tcp_assign_net_tcp_callback(&p->fw_net_1,&net_tcp_callback);
	mm_net_tcp_assign_context(&p->fw_net_1,p);
	mm_net_tcp_fopen_socket(&p->fw_net_1);
	// mm_net_tcp_connect(&p->fw_net_1);
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
		pthread_mutex_lock(&p->t_data);
		int logic_chose=p->system_state;
		pthread_mutex_unlock(&p->t_data);
		if (logic_chose<10)
		{	
			lj_lizhi3::_static_kehuduan_login_send(p);
		}
		if (91==logic_chose||21==logic_chose||22==logic_chose)
		{	
			lj_lizhi3::_static_kehuduan_seek_send(p);
		}
		if (92==logic_chose||100==logic_chose||101==logic_chose)
		{	
			lj_lizhi3::_static_kehuduan_talk_send(p);
		}
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




void lj_lizhi3::kehuduan_huidiaoti_dialogue_login(void* obj, void* u, struct mm_packet* rs_pack)
{
	struct kehuduan* khd = (struct kehuduan*)u;
	struct mm_tcp* tcp = (mm_tcp*)obj;
	c_dialogue_com::dialogue_login_rs kh_login_rs;
	kh_login_rs.Clear();
	int error_decode_message=mm_protobuf_cxx_decode_message(rs_pack,&kh_login_rs);
	if (error_decode_message)
	{
		return;
	}
	pthread_mutex_lock(&khd->t_data);
	khd->system_state=kh_login_rs.login_state();
	printf("\n系统=>:%s\n",kh_login_rs.login_desc().c_str());
	if (91==khd->system_state)
	{
		khd->uid=kh_login_rs.user_id();
		khd->user_nick=kh_login_rs.user_nick();
		khd->user_socket=kh_login_rs.socket();
	}
	pthread_mutex_unlock(&khd->t_data);
}

void lj_lizhi3::kehuduan_huidiaoti_dialogue_seek(void* obj, void* u, struct mm_packet* rs_pack)
{
	struct kehuduan* khd = (struct kehuduan*)u;
	struct mm_tcp* tcp = (mm_tcp*)obj;
	c_dialogue_com::dialogue_seek_rs kh_seek_rs;
	kh_seek_rs.Clear();
	int error_decode_message=mm_protobuf_cxx_decode_message(rs_pack,&kh_seek_rs);
	if (error_decode_message)
	{
		return;
	}

	pthread_mutex_lock(&khd->t_data);
	khd->system_state=kh_seek_rs.seek_state();
	printf("\n系统=>:%s\n",kh_seek_rs.seek_desc().c_str());
	if (92==khd->system_state)
	{
		khd->to_uid=kh_seek_rs.to_user_id();
		khd->to_user_nick=kh_seek_rs.to_user_nick();
		khd->to_user_socket=kh_seek_rs.to_socket();
	}
	pthread_mutex_unlock(&khd->t_data);

}

void lj_lizhi3::kehuduan_huidiaoti_dialogue_talk_nt(void* obj, void* u, struct mm_packet* rs_pack)
{
	mm_msleep(100);
	struct kehuduan* khd = (struct kehuduan*)u;
	struct mm_tcp* tcp = (mm_tcp*)obj;
	c_dialogue_com::dialogue_talk_nt kh_talk_nt;
	kh_talk_nt.Clear();
	int error_decode_message=mm_protobuf_cxx_decode_message(rs_pack,&kh_talk_nt);
	if (error_decode_message)
	{
		return;
	}
	if (100==kh_talk_nt.talk_nt_state())
	{

		pthread_mutex_lock(&khd->t_data);
		khd->system_state=kh_talk_nt.talk_nt_state();
		pthread_mutex_unlock(&khd->t_data);

		printf("%s=>:%s\n",kh_talk_nt.to_user_nick().c_str(),kh_talk_nt.talking().c_str());
	}

}

void lj_lizhi3::kehuduan_huidiaoti_dialogue_talk_rs(void* obj, void* u, struct mm_packet* rs_pack)
{
	struct kehuduan* khd = (struct kehuduan*)u;
	struct mm_tcp* tcp = (mm_tcp*)obj;
	c_dialogue_com::dialogue_talk_rs kh_talk_rs;
	kh_talk_rs.Clear();
	int error_decode_message=mm_protobuf_cxx_decode_message(rs_pack,&kh_talk_rs);
	if (error_decode_message)
	{
		return;
	}
	if (101==kh_talk_rs.talk_rs_state())
	{
		pthread_mutex_lock(&khd->t_data);
		khd->system_state=kh_talk_rs.talk_rs_state();
		pthread_mutex_unlock(&khd->t_data);

	}

}
