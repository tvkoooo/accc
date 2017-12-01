#include "wangluo_fw.h"
#include "wangluo_fw_accept.h"
#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"

void wangluo_fw_accept(struct wangluo_fw_accept *p)
{
	int sel_fh;
	int maxfd=-1;
	p->map_timeout.tv_sec=0;
	p->map_timeout.tv_usec=0;

			fd_set fds;
			FD_ZERO(&fds);
			FD_SET(0,&fds);
			FD_SET(p->sClient,&fds);
			maxfd=p->sClient;
			sel_fh=select(p->sClient+1,&fds,0,0,&p->map_timeout);

			if (0>sel_fh)
			{
				socket_context_closed(p->slisten);
				return;
			}
			else if(0==sel_fh)
			{
				
			}
			else
			{
				//if (FD_ISSET(0,&fds))
				//{
				//	ret = recv(p->sClient,p->revData,255,0);
				//	if(ret > 0)
				//	{
				//		p->revData[ret] = 0x00;
				//		printf("客户端消息\t:");
				//		printf(p->revData);
				//	}
				//}
			}



		

}




