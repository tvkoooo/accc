//////////////////////////////////////////////////////////////////////////
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "pthread.h"
#include "wangluo.h"

#ifndef WIN32_LEAN_AND_MEAN
#define WIN32_LEAN_AND_MEAN
#endif//WIN32_LEAN_AND_MEAN

#include <windows.h>
#include <winsock2.h>
#include <ws2ipdef.h>


#define use_vld_check_memory_leak
#if _DEBUG
#ifdef use_vld_check_memory_leak
#endif
#endif // _DEBUG

//struct appaction g_appaction;


BOOL WINAPI __static_signal_destroy(DWORD msgType)
{
	switch (msgType)
	{
	case CTRL_C_EVENT:
	case CTRL_BREAK_EVENT:
	case CTRL_CLOSE_EVENT:
	case CTRL_LOGOFF_EVENT:
	case CTRL_SHUTDOWN_EVENT:
		{
			//appaction_shutdown(&g_appaction);
			printf("输入ctrl + c  \n");
			return TRUE;
		}
		break;
	default:
		return FALSE;
	}
	return FALSE;
}






int main(int argc,char **argv)
{	

	SetConsoleCtrlHandler(__static_signal_destroy, TRUE);
	if(0)
	{
		int aa;
		aa=sizeof(struct sockaddr);
		cout<<"struct sockaddr\t"<<aa<<"\t";
		aa=sizeof(struct sockaddr_in);
		cout<<"struct sockaddr_in\t"<<aa<<"\t";
		aa=sizeof(struct sockaddr_in6);
		cout<<"struct sockaddr_in6\t"<<aa<<"\t";
		aa=sizeof(struct sockaddr_storage);
		cout<<"struct sockaddr_storage\t"<<aa<<endl;
	}
	//appaction_init(&g_appaction);
	//appaction_start(&g_appaction);
	//appaction_join(&g_appaction);
	//appaction_destroy(&g_appaction);
	if(1)
	{
	pthread_t id_1;
	pthread_t id_2;
		int ret;

		//创建线程一
		ret=pthread_create(&id_1,NULL,lizhi4_wangluo_3,NULL);
		if (ret!=0)
		{
			printf("creat pthread error!\n");
			//return -1;
		}
		//创建线程二
		ret=pthread_create(&id_2,NULL,lizhi4_wangluo_kehu,NULL);
		if (ret!=0)
		{
			printf("creat pthread error!\n");
			//return -1;
		}

		pthread_join(id_1,NULL);
		pthread_join(id_2,NULL);
	}


	return 0;
}

