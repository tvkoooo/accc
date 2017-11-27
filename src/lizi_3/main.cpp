//////////////////////////////////////////////////////////////////////////
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "pthread.h"
#include <windows.h>
#include "test2.h"

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
			printf(" ‰»Îctrl + c  \n");
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

	//appaction_init(&g_appaction);
	//appaction_start(&g_appaction);
	//appaction_join(&g_appaction);
	//appaction_destroy(&g_appaction);

    lizhi3_test2();



	return 0;
}

