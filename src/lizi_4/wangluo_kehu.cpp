#include<stdio.h>  
#include<iostream>  
#include<cstring>  
#include "wangluo.h"
#include "platform_config.h"
#include "socket_context_lizi4.h"
#include "errno_lizi4.h"
//#include <string>

using namespace std;  


void *lizhi4_wangluo_kehu(void *)
{  
	socket_context_init();
	socket_context_sleep(200);
	while(true){  
		socket_type sclient = socket(PF_INET6, SOCK_STREAM, IPPROTO_TCP);  
		if(sclient == PP_INVALID_SOCKET)  
		{  
			printf("invalid socket!");  
		return NULL;  
		}  

		struct sockaddr_in6 serAddr; 
		memset(&serAddr,0,sizeof(struct sockaddr_in6));		
		serAddr.sin6_family = AF_INET6;  
		serAddr.sin6_port = htons(8888);
		//inet_pton(AF_INET, "127.0.0.1", &serAddr.sin_addr);
		inet_pton(AF_INET6, "::1", &serAddr.sin6_addr);

		int err_x;
		err_x=connect(sclient, (struct sockaddr *)&serAddr, sizeof(serAddr));
		if(-1==err_x)  
		{  //连接失败  
					int err = pp_errno();
			printf("connect error %s",errnomber(err));  
			socket_context_closed(sclient);  
		return NULL;  
		}  

		char data[200]="00000000";
		gets(data);
		//cin>>data;  
		const char * sendData;  
		sendData = data;   //string转const char*   
		/*char * sendData = "你好，TCP服务端，我是客户端\n";*/  
		send(sclient, sendData, strlen(sendData), 0);  
		//send()用来将数据由指定的socket传给对方主机  
		//int send(int s, const void * msg, int len, unsigned int flags)  
		//s为已建立好连接的socket，msg指向数据内容，len则为数据长度，参数flags一般设0  
		//成功则返回实际传送出去的字符数，失败返回-1，错误原因存于error   

		char recData[255];  
		int ret = recv(sclient, recData, 255, 0);  
		if(ret>0){  
			recData[ret] = 0x00;  
			printf(recData);  
		}   
		socket_context_closed(sclient);  
	}  

	socket_context_destroy();
		return NULL;  

}  