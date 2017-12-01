#include "wangluo.h"
#include <iostream>


#include <stdio.h>
#include <string.h>
#include "platform_config.h"
#include "socket_context_lizi4.h"
#include "errno_lizi4.h"




//#if 1
//void lizhi4_wangluo_do(socket_type *psClient,char prevData[255])
//{
//	int ret = recv(*psClient,prevData,255,0);
//	if(ret > 0)
//	{
//		prevData[ret] = 0x00;
//		printf("客户端消息\t:");
//		printf(prevData);
//	}
//	const char * sendData = "\n服务器：你好，TCP客户端！\n";
//	//ssize_t send(int sockfd, const void *buff, size_t nbytes, int flags);
//	send(*psClient,sendData,strlen(sendData),0);
//}
//#endif


#if 1

void *lizhi4_wangluo_fuwu(void *p)
{
	struct wangluo *pb;
	pb=(struct wangluo *) p;

	socket_context_init();

	int error_code = 0;
	//socket_type slisten = socket(PF_INET6, SOCK_STREAM, IPPROTO_TCP);
	socket_type slisten = socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);

	//pb->slisten = socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);//测试应力

	if(slisten==PP_INVALID_SOCKET)
		//if(pb->slisten==PP_INVALID_SOCKET)//测试应力

	{
		printf("socket error !");
		return NULL;  
	}
	//struct sockaddr_in6 sin;
	//memset(&sin,0,sizeof(struct sockaddr_in6));
	//sin.sin6_family = AF_INET6;
	//sin.sin6_port = htons(57000);
	//inet_pton(AF_INET6, "::", &sin.sin6_addr);

	struct sockaddr_in sin;
	memset(&sin,0,sizeof(struct sockaddr_in));
	sin.sin_family = AF_INET;
	sin.sin_port = htons(57000);
	inet_pton(AF_INET, "0.0.0.0", &sin.sin_addr);

	socklen_t addrlen = sizeof(sin);

	int reuseaddr_flag=1;

	//error_code =setsockopt(pb->slisten, SOL_SOCKET, SO_REUSEADDR,(const char*)&reuseaddr_flag, sizeof(reuseaddr_flag));//测试应力
	error_code =setsockopt(slisten, SOL_SOCKET, SO_REUSEADDR,(const char*)&reuseaddr_flag, sizeof(reuseaddr_flag));


	if (-1 == error_code)
	{
		int err = pp_errno();
		printf("SO_REUSEADDR error:%s",errnomber(err));
		return NULL;
	}
	//int bind(int sockfd, const struct sockaddr *addr,	socklen_t addrlen);
	error_code = bind(slisten, (const struct sockaddr *)&sin, addrlen);
	//error_code = bind(pb->slisten, (const struct sockaddr *)&sin, addrlen);//测试应力

	if(-1 == error_code)
	{
		int err = pp_errno();
		printf("bind error:%s",errnomber(err));
		return NULL;
	}

	socket_type sClient;
	//struct sockaddr_in6 remoteAddr;
	//socklen_t nAddrlen = sizeof(struct sockaddr_in6);
	struct sockaddr_in remoteAddr;
	socklen_t nAddrlen = sizeof(struct sockaddr_in);
	char revData[255];


	// int listen(int sockfd, int backlog);
	error_code = listen(slisten,5);
	//error_code = listen(pb->slisten,5);//测试应力


	if(-1 == error_code)
	{
		printf("slisten error !");
		return NULL;  
	}


	printf("\n服务器：等待连接...\n");
	// int accept(int sockfd, struct sockaddr *addr, socklen_t *addrlen);
	do
	{
		sClient = accept(slisten, (struct sockaddr *)&remoteAddr, &nAddrlen);
		//sClient = accept(pb->slisten, (struct sockaddr *)&remoteAddr, &nAddrlen);//测试应力

		if(sClient == PP_INVALID_SOCKET)
		{
			int err = pp_errno();
			printf("accept error %s",errnomber(err));
			return NULL;
		}

		// const char *inet_ntop(int af, const void *src, char *dst, socklen_t cnt);
		//inet_pton(AF_INET, IPdotdec, (void *)&s);

		char mmp[100];
		//inet_ntop(AF_INET6,&remoteAddr,mmp,sizeof(mmp));
		inet_ntop(AF_INET,&remoteAddr,mmp,sizeof(mmp));

		printf("服务器：接受到一个连接:%s \r\n客户需要什么？\n",mmp);

		//lizhi4_wangluo_do(&sClient,revData);

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
	}while (1);//==pb->wangluoshut



	socket_context_closed(slisten);
	//socket_context_closed(pb->slisten);//测试应力

	socket_context_destroy();

	return NULL;
}

#endif


