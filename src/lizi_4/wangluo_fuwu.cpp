#include "wangluo.h"
#include <iostream>

#if 1
#include <stdio.h>
#include <string.h>
#include "platform_config.h"
#include "socket_context_lizi4.h"
#include "errno_lizi4.h"
void *lizhi4_wangluo_fuwu(void *p)
{
	struct wangluo *pb;
	pb=(struct wangluo *) p;

		socket_context_init();

		int error_code = 0;
		socket_type slisten = socket(PF_INET6, SOCK_STREAM, IPPROTO_TCP);
		if(slisten==PP_INVALID_SOCKET)
		{

			printf("socket error !");
			return NULL;  
		}
		//sockaddr_in sin;
		//sin.sin_family = AF_INET;
		//sin.sin_port = htons(8888);
		//sin.sin_addr.S_un.S_addr = INADDR_ANY;
		//socklen_t addrlen = sizeof(sin);
		struct sockaddr_in6 sin;
		memset(&sin,0,sizeof(struct sockaddr_in6));
		sin.sin6_family = AF_INET6;
		sin.sin6_port = htons(57000);
		inet_pton(AF_INET6, "::", &sin.sin6_addr);
		// sin.sin6_addr = in6addr_any;
		socklen_t addrlen = sizeof(sin);
		int reuseaddr_flag=1;
		error_code =setsockopt(slisten, SOL_SOCKET, SO_REUSEADDR,(const char*)&reuseaddr_flag , sizeof(reuseaddr_flag));
		if (-1 == error_code)
		{
			int err = pp_errno();
			printf("SO_REUSEADDR error:%s",errnomber(err));
			return NULL;
		}
		//int bind(int sockfd, const struct sockaddr *addr,	socklen_t addrlen);
		error_code = bind(slisten, (const struct sockaddr *)&sin, addrlen);
		if(-1 == error_code)
		{
			int err = pp_errno();
			printf("bind error:%s",errnomber(err));
			return NULL;
		}
		// int listen(int sockfd, int backlog);
		error_code = listen(slisten,5);
		if(-1 == error_code)
		{
			printf("slisten error !");
			return NULL;  
		}
		socket_type sClient;
		struct sockaddr_in6 remoteAddr;
		socklen_t nAddrlen = sizeof(struct sockaddr_in6);
		char revData[255];
		do
		{
			printf("\n服务器：等待连接...\n");
			// int accept(int sockfd, struct sockaddr *addr, socklen_t *addrlen);
			sClient = accept(slisten, (struct sockaddr *)&remoteAddr, &nAddrlen);
			if(sClient == PP_INVALID_SOCKET)
			{
				int err = pp_errno();
				printf("accept error %s",errnomber(err));
				continue;
			}
			// const char *inet_ntop(int af, const void *src, char *dst, socklen_t cnt);
			char mmp[100];
			inet_ntop(AF_INET6,&remoteAddr,mmp,sizeof(mmp));
			//inet_pton(AF_INET, IPdotdec, (void *)&s);
			printf("服务器：接受到一个连接:%s \r\n客户需要什么？\n",mmp);

			//ssize_t recv(int sockfd, void *buff, size_t nbytes, int flags);
			int ret = recv(sClient,revData,255,0);
			if(ret > 0)
			{
				revData[ret] = 0x00;
				printf("客户端消息\t:");
				printf(revData);
			}
			const char * sendData = "\n服务器：你好，TCP客户端！\n";
			//ssize_t send(int sockfd, const void *buff, size_t nbytes, int flags);
			send(sClient,sendData,strlen(sendData),0);
			socket_context_closed(sClient);
		}while (1==pb->wangluoshut);
		socket_context_closed(slisten);


		socket_context_destroy();

	return NULL;
}


#endif