#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"
#include "wangluo_fw_sel.h"


void *wangluo_fw_sel_pro(void *p)
{
	struct wangluo_fw_sel *ps;
	ps=(struct wangluo_fw_sel *)p;
	int sel_fh=88;
	int ret;
	int max_nfds=0;
	char revData[255];

	ps->map_timeout.tv_sec=1;
	ps->map_timeout.tv_usec=0;

	fd_set fds;
	FD_ZERO(&fds);
	while (1)
	{		
		max_nfds=ps->sClient;
		FD_SET(ps->sClient,&fds);
		sel_fh=select(max_nfds+1,&fds,0,0,&ps->map_timeout);

		if (0>sel_fh)
		{
			//socket_context_closed(ps->slisten);
			break;
		}
		else if(0==sel_fh)
		{
			continue;
		}
		else
		{
			if (FD_ISSET(ps->sClient,&fds))
			{
				ret = recv(ps->sClient,revData,255,0);
				if(ret > 0)
				{
					revData[ret] = 0x00;
					printf("客户端消息\t:");
					printf(revData);
				}
				const char * sendData = "\n服务器：你好，TCP客户端！\n";
				send(ps->sClient,sendData,strlen(sendData),0);
			}
		}

		//socket_context_closed(ps->sClient);
	}
	return NULL;
}


