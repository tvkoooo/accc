//////////////////////////////////////////////////////////////////////////
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <exception>
#include "application.h"

#ifndef WIN32_LEAN_AND_MEAN
#define WIN32_LEAN_AND_MEAN
#endif//WIN32_LEAN_AND_MEAN

#include "platform_config.h"


#define use_vld_check_memory_leak
#if _DEBUG
#ifdef use_vld_check_memory_leak
#endif
#endif // _DEBUG

#ifdef use_vld_check_memory_leak
#include <vld.h>
#endif

struct application g_application;


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
			application_shutdown(&g_application);
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
	try
	{
		application_init(&g_application);

		application_fuzhi(&g_application,argc,argv);

		application_start(&g_application);
		application_join(&g_application);
		application_destroy(&g_application);

	}
	catch (std::exception& e)
	{
		printf("A untreated exception occur:%s\n",e.what());
	}
	
	return 0;
}

