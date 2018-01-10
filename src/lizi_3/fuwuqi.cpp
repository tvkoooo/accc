#include "fuwuqi.h"
#include "net/mm_streambuf_packet.h"
#include <string>
#include "core/mm_os_context.h"


static void fuwuqi_huidiaotifun_queshen(void* obj, void* u, struct mm_packet* pack)
{

}

static void fan_tcp_back_broken(void* obj)
{

}

static void lj_lizhi3::_static_fuwuqi_rec(struct mm_tcp* p_tcp,struct fuwuqi* p_fwq, struct mm_packet* rq_pack)
{


}


void lj_lizhi3::fuwuqi_init(struct fuwuqi* p)
{
	p->map_id_socket.clear();
	p->map_nick_id.clear();
	p->map_id_password.clear();
	p->max_uid=0;
	pthread_mutex_init(&p->t_map,NULL);
	mm_mailbox_init(&p->fw_net_0);
}
void lj_lizhi3::fuwuqi_destroy(struct fuwuqi* p)
{
	p->map_id_socket.clear();
	p->map_nick_id.clear();
	p->map_id_password.clear();
	p->max_uid=0;
	pthread_mutex_destroy(&p->t_map);
	mm_mailbox_destroy(&p->fw_net_0);
}
///////////////////////////////////////////////////
void lj_lizhi3::fuwuqi_fuzhi(struct fuwuqi* p)
{
	do 
	{
		std::ifstream user_info_map;
		user_info_map.open("user_info.haoSB");
		if(user_info_map.fail())
		{
			break;
		}				
		while(!user_info_map.eof())
		{
			std::string nick;
			mm_uint64_t userid;
			std::string password;
			user_info_map>>nick>>userid>>password;
			if (nick!="")
			{
				p->max_uid=(p->max_uid)>userid?(p->max_uid):userid;
				p->map_nick_id.insert(map_type_str_int64::value_type(nick,userid));
				p->map_id_password.insert(map_type_int64_str::value_type(userid,password));

			}
		}
		user_info_map.close();
	} while (0);
	////////////////////////////////////////////////////////////////////////
	struct mm_mailbox_callback mm_mailbox_callback;
	mm_mailbox_callback_init(&mm_mailbox_callback);
	mm_mailbox_callback.handle=&fuwuqi_huidiaotifun_queshen;
	mm_mailbox_callback.broken=&fan_tcp_back_broken;
	mm_mailbox_callback.finish=&fan_tcp_back_broken;
	mm_mailbox_callback.nready=&fan_tcp_back_broken;
	mm_mailbox_callback.obj=p;
	mm_mailbox_assign_native(&p->fw_net_0,"0.0.0.0",55000);
	mm_mailbox_assign_callback(&p->fw_net_0,c_dialogue_com::dialogue_login_rq_msg_id,&fuwuqi_huidiao_dialogue_login);
	mm_mailbox_assign_callback(&p->fw_net_0,c_dialogue_com::dialogue_seek_rq_msg_id,&fuwuqi_huidiao_dialogue_seek);
	mm_mailbox_assign_callback(&p->fw_net_0,c_dialogue_com::dialogue_talk_rq_msg_id,&fuwuqi_huidiao_dialogue_talk);
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
}




void lj_lizhi3::fuwuqi_interrupt(struct fuwuqi* p)
{

	mm_mailbox_interrupt(&p->fw_net_0);
}
void lj_lizhi3::fuwuqi_shutdown(struct fuwuqi* p)
{

	mm_mailbox_shutdown(&p->fw_net_0);
}
void lj_lizhi3::fuwuqi_join(struct fuwuqi* p)
{
	mm_mailbox_join(&p->fw_net_0);
}

void lj_lizhi3::fuwuqi_huidiao_dialogue_login(void* obj, void* u, struct mm_packet* rs_pack)
{
	struct fuwuqi* fwq = (struct fuwuqi*)u;
	struct mm_tcp* tcp = (mm_tcp*)obj;
	struct mm_packet rq_pack;
	mm_uint32_t error_state=0;
	std::string error_desc="";
	rq_pack.phead.mid=c_dialogue_com::dialogue_login_rs_msg_id;
	mm_uint32_t system_state=0;
	mm_uint64_t user_id=0;
	std::string say="";
	c_dialogue_com::dialogue_login_rq fwq_kh_login_rs;
	c_dialogue_com::dialogue_login_rs fwq_kh_login_rq;
	do 
	{
	int error_decode_message=mm_protobuf_cxx_decode_message(rs_pack,&fwq_kh_login_rs);
	if (error_decode_message)
	{
		mm_uint32_t error_state=999;
		std::string error_desc="服务器解码失败";
		break;
	}
	//printf("fuwuqi_huidiao_dialogue_login==>包号：%X \t user_id：%d \t user_nick：%s \t user_password：%s \t ",
	//	rs_pack->phead.mid,fwq_kh_login_rs.user_id(),fwq_kh_login_rs.user_nick().c_str,fwq_kh_login_rs.user_password().c_str);

	fwq_kh_login_rq.set_user_nick(fwq_kh_login_rs.user_nick());
	map_type_str_int64::iterator it_map_nick_id;
	pthread_mutex_lock(&fwq->t_map);
	it_map_nick_id= fwq->map_nick_id.find(fwq_kh_login_rs.user_nick());
	int it_map_nick_id_flag=(it_map_nick_id == fwq->map_nick_id.end());
	pthread_mutex_unlock(&fwq->t_map);
	if (it_map_nick_id_flag)
	{
		//没有找到用户名
		say="用户名不存在,请核对";
		fwq_kh_login_rq.set_login_desc(say);
		system_state=1;//用户名不存在
	}
	else
	{
		//找到
		map_type_int64_str::iterator it_map_id_password;
		pthread_mutex_lock(&fwq->t_map);
		user_id=it_map_nick_id->second;
		it_map_id_password= fwq->map_id_password.find(user_id);
		std::string userpassword=it_map_id_password->second;
		pthread_mutex_unlock(&fwq->t_map);
		if (userpassword==fwq_kh_login_rs.user_password())
		{
			//输入密码相等
			say="登录成功，欢迎进入大傻逼俱乐部.";
			fwq_kh_login_rq.set_login_desc(say);
			fwq_kh_login_rq.set_user_id(user_id);
			rq_pack.phead.uid=user_id;

			mm_tcp_lock(tcp);
			mm_socket_t fd=tcp->socket.socket;
			mm_tcp_unlock(tcp);

			pthread_mutex_lock(&fwq->t_map);
			fwq->map_id_socket.insert(map_type_int64_soc::value_type(user_id,fd));
			pthread_mutex_unlock(&fwq->t_map);

			fwq_kh_login_rq.set_socket(fd);
			system_state=91;//登录成功
		} 
		else
		{
			say="密码错误!请尝试.";
			fwq_kh_login_rq.set_login_desc(say);
			system_state=2;//密码错误
		}


	}
	fwq_kh_login_rq.set_login_state(system_state);
	b_error::info*  b_error_login= fwq_kh_login_rq.mutable_error();
	b_error_login->set_code(error_state);
	b_error_login->set_desc(error_desc);

	mm_mailbox_lock(&fwq->fw_net_0);
	int error_message_rq=mm_protobuf_cxx_n_tcp_append_rs(&fwq->fw_net_0,tcp,rq_pack.phead.mid,&fwq_kh_login_rq,rs_pack,&rq_pack);
	int message_send=mm_protobuf_cxx_n_tcp_flush_send(tcp);
	mm_mailbox_unlock(&fwq->fw_net_0);
	} while (0);

}

void lj_lizhi3::fuwuqi_huidiao_dialogue_seek(void* obj, void* u, struct mm_packet* rs_pack)
{
	struct fuwuqi* fwq = (struct fuwuqi*)u;
	struct mm_tcp* tcp = (mm_tcp*)obj;
	struct mm_packet rq_pack;
	rq_pack.phead.mid=c_dialogue_com::dialogue_seek_rs_msg_id;
	mm_uint32_t system_state=0;
	std::string say="";
	c_dialogue_com::dialogue_seek_rq fwq_kh_seek_rs;
	c_dialogue_com::dialogue_seek_rs fwq_kh_seek_rq;
	do 
	{
		int error_decode_message=mm_protobuf_cxx_decode_message(rs_pack,&fwq_kh_seek_rs);
		if (error_decode_message)
		{
			mm_uint32_t error_state=999;
			std::string error_desc="服务器解码失败";
			break;
		}
		fwq_kh_seek_rq.set_user_nick(fwq_kh_seek_rs.user_nick());
		fwq_kh_seek_rq.set_user_id(fwq_kh_seek_rs.user_id());
		fwq_kh_seek_rq.set_socket(fwq_kh_seek_rs.socket());
		map_type_str_int64::iterator it_map_nick_id;
		pthread_mutex_lock(&fwq->t_map);
		it_map_nick_id= fwq->map_nick_id.find(fwq_kh_seek_rs.to_user_nick());
		int it_map_nick_id_id=(it_map_nick_id == fwq->map_nick_id.end());
		pthread_mutex_unlock(&fwq->t_map);	
		if (it_map_nick_id_id)
		{
			//没有找到用户名
			say="你吹牛的对手还没有注册，请推荐他赶紧上车";
			fwq_kh_seek_rq.set_seek_desc(say);
			system_state=21;//说话的对象用户还未注册
		}
		else
		{
			//找到
			map_type_int64_soc::iterator it_map_id_socket;
			pthread_mutex_lock(&fwq->t_map);
			mm_uint64_t to_user_id=it_map_nick_id->second;
			it_map_id_socket= fwq->map_id_socket.find(to_user_id);
			int it_map_id_socket_flag=(it_map_id_socket ==fwq->map_id_socket.end());
			mm_socket_t fd_to_nuknow=it_map_id_socket->second;
			pthread_mutex_unlock(&fwq->t_map);	
			if (it_map_id_socket_flag)
			{
				//没有找到socket,对方未登录
				say="你基友还没有起床!请等待.";
				fwq_kh_seek_rq.set_seek_desc(say);
				system_state=22;//你基友还没有起床!请等待.
			} 
			else
			{
				//找到
				say="你的基友已经在路上了，赶紧跟着老司机Hight起来.";
				fwq_kh_seek_rq.set_seek_desc(say);
				mm_socket_t fd_to=fd_to_nuknow;
				fwq_kh_seek_rq.set_to_user_id(to_user_id);
				fwq_kh_seek_rq.set_to_user_nick(fwq_kh_seek_rs.to_user_nick());
				fwq_kh_seek_rq.set_to_socket(fd_to);
				system_state=92;//你的基友已经上线
			}
		}
		fwq_kh_seek_rq.set_seek_state(system_state);
		mm_mailbox_lock(&fwq->fw_net_0);
		int error_message_rq=mm_protobuf_cxx_n_tcp_append_rs(&fwq->fw_net_0,tcp,rq_pack.phead.mid,&fwq_kh_seek_rq,rs_pack,&rq_pack);
		int error_message_send_nt=mm_protobuf_cxx_n_tcp_flush_send(tcp);
		mm_mailbox_unlock(&fwq->fw_net_0);
	}while(0);
}

void lj_lizhi3::fuwuqi_huidiao_dialogue_talk(void* obj, void* u, struct mm_packet* rs_pack)
{
	struct fuwuqi* fwq = (struct fuwuqi*)u;
	struct mm_tcp* tcp = (mm_tcp*)obj;
	struct mm_packet nt_pack,rq_pack;
	nt_pack.phead.mid=c_dialogue_com::dialogue_talk_nt_msg_id;
	rq_pack.phead.mid=c_dialogue_com::dialogue_talk_rs_msg_id;
	mm_uint32_t system_state=0;
	std::string say="";
	c_dialogue_com::dialogue_talk_rq fwq_kh_talk_rs;
	c_dialogue_com::dialogue_talk_nt fwq_kh_talk_nt;
	c_dialogue_com::dialogue_talk_rs fwq_kh_talk_rq;
	do 
	{
		int error_decode_message=mm_protobuf_cxx_decode_message(rs_pack,&fwq_kh_talk_rs);
		if (error_decode_message)
		{
			mm_uint32_t error_state=999;
			std::string error_desc="服务器解码失败";
			break;
		}
		/////////////////////////////////////////////////////////////////////////////////////
		system_state=100;
		say="服务器接收对话成功";
		fwq_kh_talk_nt.set_user_id(fwq_kh_talk_rs.user_id());
		fwq_kh_talk_nt.set_user_nick(fwq_kh_talk_rs.user_nick());
		fwq_kh_talk_nt.set_to_user_id(fwq_kh_talk_rs.user_id());
		fwq_kh_talk_nt.set_to_user_nick(fwq_kh_talk_rs.user_nick());
		fwq_kh_talk_nt.set_talking(fwq_kh_talk_rs.talking());
		fwq_kh_talk_nt.set_talk_nt_state(system_state);
		fwq_kh_talk_nt.set_talk_nt_desc(say);
		fwq_kh_talk_nt.set_socket(fwq_kh_talk_rs.socket());
		fwq_kh_talk_nt.set_to_socket(fwq_kh_talk_rs.to_socket());
		mm_mailbox_lock(&fwq->fw_net_0);
		struct mm_tcp* to_tcp=mm_mailbox_get(&fwq->fw_net_0,fwq_kh_talk_rs.to_socket());
		int error_message_nt=mm_protobuf_cxx_n_tcp_append_rs(&fwq->fw_net_0,to_tcp,nt_pack.phead.mid,&fwq_kh_talk_nt,rs_pack,&nt_pack);
		int message_send_nt=mm_protobuf_cxx_n_tcp_flush_send(to_tcp);
		mm_mailbox_unlock(&fwq->fw_net_0);
		/////////////////////////////////////////////////////////////////////////////////////////////
		system_state=101;
		say="服务器转发通话成功";
		fwq_kh_talk_rq.set_user_id(fwq_kh_talk_rs.user_id());
		fwq_kh_talk_rq.set_user_nick(fwq_kh_talk_rs.user_nick());
		fwq_kh_talk_rq.set_talk_rs_state(system_state);
		fwq_kh_talk_rq.set_talk_rs_desc(say);
		fwq_kh_talk_rq.set_socket(fwq_kh_talk_rs.socket());
		fwq_kh_talk_rq.set_to_socket(fwq_kh_talk_rs.to_socket());
		mm_mailbox_lock(&fwq->fw_net_0);
		int error_message_rq=mm_protobuf_cxx_n_tcp_append_rs(&fwq->fw_net_0,tcp,rq_pack.phead.mid,&fwq_kh_talk_rq,rs_pack,&rq_pack);
		int message_send_rq=mm_protobuf_cxx_n_tcp_flush_send(tcp);
		mm_mailbox_unlock(&fwq->fw_net_0);
	} while (0);
}
