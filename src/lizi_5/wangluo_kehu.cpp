#include "wangluo_kehu.h"
#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include <string>
#include <iostream>
#include "mm_archive_endian.h"
#include "core\mm_alloc.h"
#include "cJSON.h"
//#include "common\packet.h"
//#include "request.h"
//
//using namespace protocol;
//using namespace slist;
//using namespace link;
//using namespace sox;


static void* __static_uuu_poll_wait_thread(void* arg);

void wangluo_kehu_init(struct wangluo_kehu* p)
{
	p->sclient=PP_INVALID_SOCKET;
	p->state = ts_closed;
	socket_context_init();

}
void wangluo_kehu_destroy(struct wangluo_kehu* p)
{
	p->sclient=PP_INVALID_SOCKET;
	p->state = ts_closed;
	socket_context_destroy();
}
//
void wangluo_kehu_poll_wait(struct wangluo_kehu* p)
{

	p->sclient = socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);
	if(p->sclient == PP_INVALID_SOCKET)  
	{  
		printf("客户invalid socket!");  
		return ;  
	} 
	struct sockaddr_in serAddr; 
	memset(&serAddr,0,sizeof(struct sockaddr_in));		
	serAddr.sin_family = AF_INET;  
	serAddr.sin_port = htons(9090);
	//inet_pton(AF_INET, "101.200.169.28", &serAddr.sin_addr);
	inet_pton(AF_INET, "127.0.0.1", &serAddr.sin_addr);

	int err_x;
	err_x=connect(p->sclient, (struct sockaddr *)&serAddr, sizeof(serAddr));
	if(-1==err_x) 
	{  //连接失败  
		int err = pp_errno();
		printf("客户connect error %s",errnomber(err));  
		socket_context_closed(p->sclient);  
		return ;  
	}

	char body[512]={0};
	int bodylengh=0;
	char data[1024]={0};
	while( ts_motion == p->state )
	{
		UINT16 usertype=2;
		UINT32 sid=10005127;
		UINT32 pk_size=6;

		UINT16 big_usertype=mm_hton16(usertype);
		UINT32 big_sid=mm_hton32(sid);
		UINT32 big_pk_size=mm_hton32(pk_size);

		memset(body,0,512);
		memcpy(body,&big_sid,4);
		memcpy(body+4,&big_usertype,2);
		memcpy(body+6,&big_pk_size,4);
		bodylengh=10;


		UINT32 length=13+bodylengh;
		UINT32 uri=(35 << 8 | 4);
		UINT16 u_sid=5127;
		UINT16 resCode=0x66;
		UINT8  tag=0x8;

		UINT32 big_length=mm_hton32(length);
		UINT32 big_uri=mm_hton32(uri);
		UINT16 big_u_sid=mm_hton16(u_sid);
		UINT16 big_resCode=mm_hton16(resCode);
		UINT8 big_tag=tag;

		memset(data,0,1024);
		memcpy(data+0,&big_length,4);
		memcpy(data+4,&big_uri,4);
		memcpy(data+8,&big_u_sid,2);
		memcpy(data+10,&big_resCode,2);
		memcpy(data+12,&big_tag,1);
		memcpy(data+13,&body,bodylengh);

		//scanf(" %[^\n]",data);
		//send(p->sclient, data,strlen(data), 0);
		//printf("%s",data);

		//struct PSessionLbs rs;
		//mm::mm_classic_packet_head packet_head;
		//sox::PackBuffer buff;
		//sox::Pack archive(buff);
		//rs.sid=10005127;
		//rs.type=(ISPType)0x2;
		//UINT32 pack_leng=6;

		//mm::mm_classic_packet_head pack_send;
		//pack_send.length=13+pack_leng;
		//pack_send.uri=(35 << 8 | 4);
		//pack_send.sid=5127;
		//pack_send.resCode=7;
		//pack_send.tag=1;

		//packet_head.encode(archive);
		//rs.marshal(archive);
		//archive.replace_uint32(0, archive.size());
		memset(data,0,1024);
		//UINT64 d1=mm_hton64(0x130004230000ffff);
		//UINT64 d2=mm_hton64(0xffc801ffffffabff);
		//UINT32 d3=mm_hton32(0xffff8610);
		//UINT8 d4=0x10;


		////std::string sendddd="13000423 0000ffff ffc801ff ffffabff ffff8610 10";
		//memcpy(data,&d1,8);
		//memcpy(data+8,&d2,8);
		//memcpy(data+16,&d3,24);
		//memcpy(data+20,&d4,1);
		{
			mm_uint32_t lenght = 19;
			mm_uint32_t uri = (35 << 8 | 4);
			mm_uint16_t sid = 0;
			mm_uint16_t res_code = 200;
			mm_uint8_t tag = 1;

			mm_uint32_t pk_sid = 100011;
			mm_uint16_t pk_type = 1;

			struct mm_streambuf streambuf;
			mm::mm_o_archive_little archive_o(streambuf);
			mm::mm_i_archive_little archive_i(streambuf);
			mm_streambuf_init(&streambuf);
			//////////////////////////////////////////////////////////////////////////
			archive_o << lenght;
			archive_o << uri;
			archive_o << sid;
			archive_o << res_code;
			archive_o << tag;
			archive_o << pk_sid;
			archive_o << pk_type;
			//////////////////////////////////////////////////////////////////////////
			size_t sz = mm_streambuf_size(&streambuf);
			char buffer[32] = {0};
			mm_memcpy((void*)buffer, (void*)(streambuf.buff + streambuf.gptr), sz);
			std::string sendtest_1 ="wocaonio";
			sendword send_word;
			send_word.Mid=800122002;
			send_word.Pid=10087;
			send_word.Datawords="hello world! i am c++ !";
			std::string send_word_str;


			int size_send = sendtest_1.size();
			int iiiiii=send(p->sclient,sendtest_1.c_str(),size_send,0);
		}

		char recData[1024];  
		int ret = recv(p->sclient, recData, 1024, 0);  
		if(ret>0)
		{  

			UINT32 big_rec_length;
			UINT32 big_rec_uri;
			UINT16 big_rec_u_sid;
			UINT16 big_rec_resCode;
			UINT8  big_rec_tag;

			UINT32 big_rec_sid;
			UINT32 big_rec_asid;
			char data_rec[512];

			memcpy(&big_rec_length,&recData,4);
			memcpy(&big_rec_uri,&recData+4,4);
			memcpy(&big_rec_u_sid,&recData+8,2);
			memcpy(&big_rec_resCode,&recData+10,2);
			memcpy(&big_rec_tag,&recData+12,1);
			memcpy(&big_rec_sid,&recData+13,4);
			memcpy(&big_rec_asid,&recData+17,4);
			memcpy(&data_rec,&recData+21,60);

			UINT32 rec_length=mm_ntoh32(big_rec_length);
			UINT32 rec_uri=mm_ntoh32(big_rec_uri);
			UINT16 rec_u_sid=mm_ntoh16(big_rec_u_sid);
			UINT16 rec_resCode=mm_ntoh16(big_rec_resCode);
			UINT8  rec_tag=big_rec_tag;


			UINT32 rec_sid=mm_ntoh32(big_rec_sid);
			UINT32 rec_asid=mm_ntoh32(big_rec_asid);



			std::cout<<rec_length<<"\t"<<rec_uri<<"\t"<<rec_u_sid<<"\t"<<rec_resCode<<"\t"<<rec_tag<<"\t"<<rec_sid<<"\t"<<rec_asid<<recData<<std::endl;
		}   
		Sleep(50000);
	}
		socket_context_closed(p->sclient);
}
//
void wangluo_kehu_start(struct wangluo_kehu* p)
{

	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	socket_context_sleep(200);
	pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
}
void wangluo_kehu_interrupt(struct wangluo_kehu* p)
{
	p->state = ts_closed;
}
void wangluo_kehu_shutdown(struct wangluo_kehu* p)
{
	shutdown(p->sclient,2);
	socket_context_closed(p->sclient);
	p->state = ts_finish;

}
void wangluo_kehu_join(struct wangluo_kehu* p)
{

	pthread_join(p->poll_thread, NULL);
}
static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct wangluo_kehu* p = (struct wangluo_kehu*)(arg);

	wangluo_kehu_poll_wait(p);
	return NULL;
}