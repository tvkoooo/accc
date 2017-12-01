#include "wangluo_kh.h"
#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"
static void* __static_uuu_poll_wait_thread(void* arg);

void wangluo_kh_init(struct wangluo_kh* p)
{
	p->state = ts_closed;
	socket_context_init();

}
void wangluo_kh_destroy(struct wangluo_kh* p)
{
	p->state = ts_closed;
	socket_context_destroy();
}
//
void wangluo_kh_poll_wait(struct wangluo_kh* p)
{
	while( ts_motion == p->state )
	{
		p->sclient = socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);
		if(p->sclient == PP_INVALID_SOCKET)  
		{  
			printf("invalid socket!");  
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
		{  //Á¬½ÓÊ§°Ü  
			int err = pp_errno();
			printf("connect error %s",errnomber(err));  
			socket_context_closed(p->sclient);  
			return ;  
		}  
		char data[200]="0";
		scanf(" %[^\n]",data);
		send(p->sclient, data, strlen(data), 0); 
		char recData[255];  
		int ret = recv(p->sclient, recData, 255, 0);  
		if(ret>0)
		{  
			recData[ret] = 0x00;  
			printf(recData);  
		}   
		socket_context_closed(p->sclient);
	}
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