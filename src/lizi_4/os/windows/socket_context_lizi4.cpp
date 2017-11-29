#include "socket_context_lizi4.h"
#include "platform_config.h"

#include <stdio.h>


void socket_context_init()
{
	//////加载Winsock库
	WORD sockVersion =MAKEWORD(2,2);
	WSADATA wsaData;
	if(WSAStartup(sockVersion,&wsaData)!=0)
	{
		printf("socket_context_init error\n");  
	}
}
void socket_context_destroy()
{
	/////释放Winsock库
	WSACleanup();
}

int socket_context_closed(socket_type soc)
{
			 return closesocket(soc);
}

void socket_context_sleep(unsigned long ms)
{
		Sleep(ms);
}