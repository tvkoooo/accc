

#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"
#include "wangluo_fw_accept.h"

static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct wangluo_fw_accept* p = (struct wangluo_fw_accept*)(arg);
	wangluo_fw_accept_poll_wait(p);
	return NULL;
}

void huidiao(void* obj, socket_type sClient)
{

}

void wangluo_fw_accept_callback_init(struct wangluo_fw_accept_callback* p)
{
	p->handle=huidiao;
	p->obj=NULL;
}
void wangluo_fw_accept_callback_destroy(struct wangluo_fw_accept_callback* p)
{
	p->handle=huidiao;
	p->obj=NULL;
}

void wangluo_fw_accept_init(struct wangluo_fw_accept* p)
{
	int error_code;
	p->state = ts_closed;
	wangluo_fw_accept_callback_init(&p->callback);
	socket_context_init();
	//
	//accept初始化
	p->slisten = socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);
	if(p->slisten==PP_INVALID_SOCKET)
	{
		printf("服务器socket error !");
		return ;  
	}

	struct sockaddr_in sin;
	memset(&sin,0,sizeof(struct sockaddr_in));
	sin.sin_family = AF_INET;
	sin.sin_port = htons(57000);
	inet_pton(AF_INET, "0.0.0.0", &sin.sin_addr);
	socklen_t addrlen = sizeof(sin);
	int reuseaddr_flag=1;
	error_code =setsockopt(p->slisten, SOL_SOCKET, SO_REUSEADDR,(const char*)&reuseaddr_flag, sizeof(reuseaddr_flag));
	if (-1 ==error_code)
	{
		int err = pp_errno();
		printf("服务器SO_REUSEADDR error:%s",errnomber(err));
		return ;
	}
	error_code = bind(p->slisten, (const struct sockaddr *)&sin, addrlen);
	if(-1 ==error_code)
	{
		int err = pp_errno();
		printf("服务器bind error:%s",errnomber(err));
		return ;
	}
	p->nAddrlen = sizeof(struct sockaddr_in);
	error_code = listen(p->slisten,5);
	if(-1 ==error_code)
	{
		printf("slisten error !");
		return ;  
	}
	printf("\n服务器：等待连接...\n");

}
void wangluo_fw_accept_destroy(struct wangluo_fw_accept* p)
{
	p->state = ts_closed;
	wangluo_fw_accept_callback_destroy(&p->callback);
	socket_context_destroy();
}
//
void wangluo_fw_accept_setcallback(struct wangluo_fw_accept* p,struct wangluo_fw_accept_callback* pp)
{
	p->callback=*pp;
}


void wangluo_fw_accept_poll_wait(struct wangluo_fw_accept* p)
{
	while( ts_motion == p->state )
	{
		p->sClient_jin = accept(p->slisten, (struct sockaddr *)&p->remoteAddr, &p->nAddrlen);

		p->sClient=p->sClient_jin;

		if(p->sClient_jin == PP_INVALID_SOCKET)
		{
			int err = pp_errno();
			printf("服务器accept error %s",errnomber(err));
			break;
		}
		char mmp[100];
		inet_ntop(AF_INET,&p->remoteAddr,mmp,sizeof(mmp));
		printf("服务器：接受到一个连接:%s \n",mmp);
		// sClient 发出去
		(*(p->callback.handle))(p,p->sClient);
		Sleep(1000);
	}
}

void wangluo_fw_accept_start(struct wangluo_fw_accept* p)
{
	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
}
void wangluo_fw_accept_interrupt(struct wangluo_fw_accept* p)
{
	p->state = ts_closed;
}
void wangluo_fw_accept_shutdown(struct wangluo_fw_accept* p)
{
	p->state = ts_finish;
	shutdown(p->slisten,2);
	socket_context_closed(p->slisten);
}
void wangluo_fw_accept_join(struct wangluo_fw_accept* p)
{
	pthread_join(p->poll_thread, NULL);
	socket_context_closed(p->slisten);
}



