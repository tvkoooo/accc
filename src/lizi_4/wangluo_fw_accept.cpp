
#include "wangluo_fw_accept.h"
#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"


static void* __static_uuu_poll_wait_thread(void* arg);
void huidiao(socket_type,struct sockaddr_in *,socklen_t)
{

}

void wangluo_fw_accept_fuzhi_f3(struct wangluo_fw_accept* p,fun_void_s3 p1)
{
		p->fun_hui=p1;
}

void wangluo_fw_accept_init(struct wangluo_fw_accept* p)
{
	p->fun_hui=&huidiao;
	p->state = ts_closed;
	socket_context_init();
}
void wangluo_fw_accept_destroy(struct wangluo_fw_accept* p)
{
	p->fun_hui=&huidiao;
	p->state = ts_closed;
	socket_context_destroy();
}
//
void wangluo_fw_accept_poll_wait(struct wangluo_fw_accept* p)
{
	//int sel_fh=88;
	//int ret;
	//int max_nfds=0;
	//fd_set fds;
	//p->map_timeout.tv_sec=1;
	//p->map_timeout.tv_usec=0;
	//FD_ZERO(&fds);

	while( ts_motion == p->state )
	{
		p->sClient = accept(p->slisten, (struct sockaddr *)&p->remoteAddr, &p->nAddrlen);
		if(p->sClient == PP_INVALID_SOCKET)
		{
			int err = pp_errno();
			printf("accept error %s",errnomber(err));
			break;
		}
		char mmp[100];
		inet_ntop(AF_INET,&p->remoteAddr,mmp,sizeof(mmp));
		printf("服务器：接受到一个连接:%s \r\n客户需要什么？\n",mmp);
		// sClient 发出去
		p->fun_hui(p->sClient, &p->remoteAddr, p->nAddrlen);
	}
	//p->map_s[p->sClient]=(int)p->sClient;
	//max_nfds=p->map_s.end()->first;

	//while( ts_motion == p->state )
	//{
		//max_nfds=p->sClient;

		//FD_SET(p->sClient,&fds);
		//sel_fh=select(max_nfds+1,&fds,0,0,&p->map_timeout);

		//if (0>sel_fh)
		//{
		//	socket_context_closed(p->slisten);
		//	return;
		//}
		//else if(0==sel_fh)
		//{

		//	continue;
		//}
		//else
		//{
		//	if (FD_ISSET(p->sClient,&fds))
		//	{
		//		ret = recv(p->sClient,p->revData,255,0);
		//		if(ret > 0)
		//		{
		//			p->revData[ret] = 0x00;
		//			printf("客户端消息\t:");
		//			printf(p->revData);
		//		}
		//		const char * sendData = "\n服务器：你好，TCP客户端！\n";
		//		send(p->sClient,sendData,strlen(sendData),0);
		//	}
		//}

		//int ret;
		//ret = recv(p->sClient,p->revData,255,0);
		//if(ret > 0)
		//{
		//	p->revData[ret] = 0x00;
		//	printf("客户端消息\t:");
		//	printf(p->revData);
		//}
		//const char * sendData = "\n服务器：你好，TCP客户端！\n";
		//send(p->sClient,sendData,strlen(sendData),0);

		//socket_context_closed(p->sClient);
	//}
}
//
void wangluo_fw_accept_start(struct wangluo_fw_accept* p)
{

	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	p->slisten = socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);
	if(p->slisten==PP_INVALID_SOCKET)
	{
		printf("socket error !");
		return ;  
	}

	struct sockaddr_in sin;
	memset(&sin,0,sizeof(struct sockaddr_in));
	sin.sin_family = AF_INET;
	sin.sin_port = htons(57000);
	inet_pton(AF_INET, "0.0.0.0", &sin.sin_addr);
	socklen_t addrlen = sizeof(sin);
	int reuseaddr_flag=1;
	p->error_code =setsockopt(p->slisten, SOL_SOCKET, SO_REUSEADDR,(const char*)&reuseaddr_flag, sizeof(reuseaddr_flag));
	if (-1 == p->error_code)
	{
		int err = pp_errno();
		printf("SO_REUSEADDR error:%s",errnomber(err));
		return ;
	}
	p->error_code = bind(p->slisten, (const struct sockaddr *)&sin, addrlen);
	if(-1 == p->error_code)
	{
		int err = pp_errno();
		printf("bind error:%s",errnomber(err));
		return ;
	}
	p->nAddrlen = sizeof(struct sockaddr_in);
	p->error_code = listen(p->slisten,5);
	if(-1 == p->error_code)
	{
		printf("slisten error !");
		return ;  
	}
	printf("\n服务器：等待连接...\n");

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
static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct wangluo_fw_accept* p = (struct wangluo_fw_accept*)(arg);
	wangluo_fw_accept_poll_wait(p);
	return NULL;
}



