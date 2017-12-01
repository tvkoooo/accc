#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"
#include "wangluo_fw_sel.h"


void *wangluo_fw_sel_pro(void *p)
{
	struct wangluo_fw_sel *ps;
	ps=(struct wangluo_fw_sel *)p;
	int sel_fh;
	int maxfd=-1;
	ps->map_timeout.tv_sec=1;
	ps->map_timeout.tv_usec=0;
	while (1)
	{		
		if (ps->flag==1)
		{
			fd_set fds;
			FD_ZERO(&fds);
			FD_SET(0,&fds);
			FD_SET(ps->sClient,&fds);
			maxfd=ps->sClient;
			sel_fh=select(ps->sClient+1,&fds,0,0,&ps->map_timeout);
		}

	}
}


