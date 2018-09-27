#include "socket_context.h"
#include <stdio.h>


LIB_EXPORT_COMMON void socket_context_init()
{
	//////加载Winsock库
	WORD sockVersion =MAKEWORD(2,2);
	WSADATA wsaData;
	if(WSAStartup(sockVersion,&wsaData)!=0)
	{
		printf("socket_context_init error\n");  
	}
}
LIB_EXPORT_COMMON void socket_context_destroy()
{
	/////释放Winsock库
	WSACleanup();
}

LIB_EXPORT_COMMON int socket_context_closed(socket_type soc)
{
			 return closesocket(soc);
}

LIB_EXPORT_COMMON void socket_context_sleep(unsigned long ms)
{
		Sleep(ms);
}