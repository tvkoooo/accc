#include<WINSOCK2.H>  
#include<STDIO.H>  
#include<iostream>  
#include<cstring>  
#include "wangluo.h"
#include <ws2tcpip.h>
using namespace std;  
#pragma comment(lib, "ws2_32.lib")  

static LPSTR ConvertErrorCodeToString(DWORD ErrorCode)
{
	HLOCAL LocalAddress=NULL;
	FormatMessage(FORMAT_MESSAGE_ALLOCATE_BUFFER|FORMAT_MESSAGE_IGNORE_INSERTS|FORMAT_MESSAGE_FROM_SYSTEM,NULL,ErrorCode,0,(PTSTR)&LocalAddress,0,NULL);
	return (LPSTR)LocalAddress;
}

void *lizhi4_wangluo_kehu(void *)
{  
	/////加载Winsock库
	WORD sockVersion = MAKEWORD(2, 2);  
	WSADATA data;  
	if(WSAStartup(sockVersion, &data)!=0)  
	{  
		return NULL;  
	}  

	while(true){  
		SOCKET sclient = socket(PF_INET6, SOCK_STREAM, IPPROTO_TCP);  
		if(sclient == INVALID_SOCKET)  
		{  
			printf("invalid socket!");  
		return NULL;  
		}  

		sockaddr_in6 serAddr; 
		memset(&serAddr,0,sizeof(sockaddr_in6));		
		serAddr.sin6_family = AF_INET6;  
		serAddr.sin6_port = htons(8888);
		//inet_pton(AF_INET, "192.168.111.203", &serAddr.sin_addr);
		inet_pton(AF_INET6, "::1", &serAddr.sin6_addr);

		int err_x;
		err_x=connect(sclient, (sockaddr *)&serAddr, sizeof(serAddr));
		if(SOCKET_ERROR==err_x)  
		{  //连接失败  
					int err = GetLastError();
			printf("connect error %s",ConvertErrorCodeToString(err));  
			closesocket(sclient);  
		return NULL;  
		}  

		char data[20]="00000000";
		cin>>data;  
		const char * sendData;  
		sendData = data;   //string转const char*   
		//char * sendData = "你好，TCP服务端，我是客户端\n";  
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
		closesocket(sclient);  
	}  

	////释放Winsock库
	WSACleanup();  
		return NULL;  

}  