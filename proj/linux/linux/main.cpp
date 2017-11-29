//////////////////////////////////////////////////////////////////////////
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <signal.h>
#include <exception>
#include "application.h"


#define use_vld_check_memory_leak
#if _DEBUG
#ifdef use_vld_check_memory_leak
#endif
#endif // _DEBUG

struct application g_application;

static void __static_signal_destroy(int n)  
{
	application_shutdown(&g_application);
}

int main(int argc,char **argv)
{	
	//
	signal(SIGINT	,&__static_signal_destroy);
	// We expect write failures to occur but we want to handle them where 
	// the error occurs rather than in a SIGPIPE handler.
	// when send to a disconnected socket,will trigger SIGPIPE,default handler is terminate.
	// we not find a best way for this signal.
	signal(SIGPIPE  ,SIG_IGN);
	//
	try
	{
		application_init(&g_application);
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

