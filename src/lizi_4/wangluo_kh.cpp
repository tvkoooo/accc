#include "wangluo_kh.h"
#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"
static void* __static_uuu_poll_wait_thread(void* arg);

void wangluo_kh_init(struct wangluo_kh* p)
{
	p->sclient=PP_INVALID_SOCKET;
	p->state = ts_closed;
	socket_context_init();

}
void wangluo_kh_destroy(struct wangluo_kh* p)
{
	p->sclient=PP_INVALID_SOCKET;
	p->state = ts_closed;
	socket_context_destroy();
}
//
void wangluo_kh_poll_wait(struct wangluo_kh* p)
{
	int pscl,kh_talk=0;
	char scl[100];
	char shuju[100]="    数据kh_talk：  ";
	p->sclient = socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);
	if(p->sclient == PP_INVALID_SOCKET)  
	{  
		printf("客户invalid socket!");  
		return ;  
	} 
	struct sockaddr_in serAddr; 
	memset(&serAddr,0,sizeof(struct sockaddr_in));		
	serAddr.sin_family = AF_INET;  
	serAddr.sin_port = htons(57000);
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

	while( ts_motion == p->state )
	{
		//char data[250]="客户socket  sclient==";
		pscl=p->sclient;
		sprintf(scl,"%d",pscl);
		//strcat(data,scl);
		//strcat(data,shuju);
		//sprintf(scl,"%d",kh_talk);
		//strcat(data,scl);

		char body[100]="wo cao ni mmp de";
		int bodylengh=strlen(body);

		UINT16 mes_size=56+bodylengh;
		UINT16 head_size=48;
		UINT32 mid=10000630;
		UINT32 pid=30808;
		UINT64 sid=40005000600088;
		UINT64 uid=9000193254;

		char data[250];
		memset(data,0,250);
		memcpy(data,&mes_size,4);
		memcpy(data+4,&head_size,4);
		memcpy(data+8,&mid,8);
		memcpy(data+16,&pid,8);
		memcpy(data+24,&sid,16);
		memcpy(data+40,&uid,16);
		memcpy(data+56,&body,bodylengh);

		//scanf(" %[^\n]",data);
		//send(p->sclient, data,strlen(data), 0);
		send(p->sclient, data,mes_size,0);

		char recData[255];  
		int ret = recv(p->sclient, recData, 255, 0);  
		if(ret>0)
		{  
			recData[ret] = 0x00;  
			printf(recData);  
		}   
		Sleep(1500);
		kh_talk++;
	}
		socket_context_closed(p->sclient);
}
//
void wangluo_kh_start(struct wangluo_kh* p)
{

	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	socket_context_sleep(200);
	pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
}
void wangluo_kh_interrupt(struct wangluo_kh* p)
{
	p->state = ts_closed;
}
void wangluo_kh_shutdown(struct wangluo_kh* p)
{
	shutdown(p->sclient,2);
	socket_context_closed(p->sclient);
	p->state = ts_finish;

}
void wangluo_kh_join(struct wangluo_kh* p)
{

	pthread_join(p->poll_thread, NULL);
}
static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct wangluo_kh* p = (struct wangluo_kh*)(arg);

	wangluo_kh_poll_wait(p);
	return NULL;
}