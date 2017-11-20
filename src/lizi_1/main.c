//////////////////////////////////////////////////////////////////////////
#include "pthread.h"
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <windows.h>
#include "appaction.h"
#include "fun_log_dll.h"

#define use_vld_check_memory_leak
#if _DEBUG
#ifdef use_vld_check_memory_leak
#endif
#endif // _DEBUG

struct appaction g_appaction;


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
			appaction_shutdown(&g_appaction);
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

	appaction_init(&g_appaction);
	appaction_start(&g_appaction);
	appaction_join(&g_appaction);
	appaction_destroy(&g_appaction);

	{
		char bbc[30]="wo kao ni lao mao";
		int ak=5;
		fun_log_fprintf(bbc);
		printf("%s",bbc);
	

	
		//FILE * fpin;
		//char filenametest[20]="log.txt";
		//if ((fpin=fopen(filenametest,"w+"))==NULL)
		//{
		//	printf("cannot open\n");
		//	exit(0);
		//}

		////char kkkk[100];
		////sprintf(kkkk,"全局变量Quanju_lizhi1_pth ： %d\n",a->intin);
		////fputs(kkkk,a->fpin);
		//fprintf(fpin,"输出日志： %s\n",bbc);
		//fclose(fpin);

	}





	return 0;
}

