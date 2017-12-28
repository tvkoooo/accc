#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"
#include "wangluo_fw_sel.h"
#include <set>

static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct wangluo_fw_sel* p = (struct wangluo_fw_sel*)(arg);
	wangluo_fw_sel_poll_wait(p);
	return NULL;
}


void wangluo_fw_sel_shujuchuandi(struct wangluo_fw_sel *p,socket_type sClient)
{
	pthread_mutex_lock(&p->mute_sClient);
	p->fw_sel_map1[sClient]=sClient;
	pthread_mutex_unlock(&p->mute_sClient);
	printf("\n 服务器shujuchuandi sClient:\t%d\n",sClient);
}

void wangluo_fw_sel_init(struct wangluo_fw_sel* p)
{
	p->map_timeout.tv_sec=1;
	p->map_timeout.tv_usec=0;
	pthread_mutex_init(&p->mute_sClient,NULL);
	p->state = ts_closed;
	//
	p->fw_sel_map1.clear();
}
void wangluo_fw_sel_destroy(struct wangluo_fw_sel* p)
{
	pthread_mutex_destroy(&p->mute_sClient);
	p->state = ts_closed;
	p->fw_sel_map1.clear();
}

void wangluo_fw_sel_poll_wait(struct wangluo_fw_sel* p)
{
	int sel_fh=88;
	int ret;
	int now_nfds,max_nfds=0;
	char revData[255],rev_body[100];
	memset(revData,0,250);
	memset(rev_body,0,100);
	UINT16 mes_size=0;
	UINT16 head_size=0;
	UINT32 mid=0;
	UINT32 pid=0;
	UINT64 sid=0;
	UINT64 uid=0;
	std::map<socket_type,int>::iterator it;
	fd_set fds;
	while( ts_motion == p->state )
	{

		FD_ZERO(&fds);
		if (p->fw_sel_map1.empty())
		{
			socket_context_sleep(500);
		}
		else
		{
			pthread_mutex_lock(&p->mute_sClient);
			it =p->fw_sel_map1.begin();
			max_nfds=it->first;
			while(it != p->fw_sel_map1.end())
			{
				FD_SET(it->first,&fds);
				it ++; 
			}

			pthread_mutex_unlock(&p->mute_sClient);
			sel_fh=select(max_nfds+1,&fds,0,0,&p->map_timeout);

			if (0>sel_fh)
			{
				break;
			}
			else if(0==sel_fh)
			{
				continue;
			}
			else
			{
				std::set<socket_type> lin_set1;

				pthread_mutex_lock(&p->mute_sClient);
				it =p->fw_sel_map1.begin();
				while(it != p->fw_sel_map1.end())
				{
					if (FD_ISSET(it->first,&fds))
					{
						lin_set1.insert(it->first);
					}
					it++;
				}
				pthread_mutex_unlock(&p->mute_sClient);


				std::set<socket_type>::iterator set_find;
				set_find=lin_set1.begin();
				while(set_find != lin_set1.end())
				{
					now_nfds=*set_find;
					ret = recv(now_nfds,revData,255,0);
					if(ret > 0)
					{
						revData[ret] = 0x00;
						printf("服务器收到 %d 信息：\t:",ret);
						memcpy(&mes_size,revData,4);
						memcpy(&head_size,revData+4,4);
						memcpy(&mid,revData+8,8);
						memcpy(&pid,revData+16,8);
						memcpy(&sid,revData+24,16);
						memcpy(&uid,revData+40,16);
						memcpy(rev_body,revData+56,mes_size-head_size);

						printf("mes_size=%d;head_size=%d;mid=%d;pid=%d;sid=%lld;uid=%lld\n",mes_size,head_size,mid,pid,sid,uid);
						printf("rev_body=%s",rev_body);
						printf("\n");
					}
					const char * sendData = "\n服务器：你好，TCP客户端！\n";
					send(now_nfds,sendData,strlen(sendData),0);
					set_find++;
				}
			}
		}
	}
}

void wangluo_fw_sel_start(struct wangluo_fw_sel* p)
{
	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
}


void wangluo_fw_sel_interrupt(struct wangluo_fw_sel* p)
{
	p->state = ts_closed;
}
void wangluo_fw_sel_shutdown(struct wangluo_fw_sel* p)
{
	p->state = ts_finish;
}
void wangluo_fw_sel_join(struct wangluo_fw_sel* p)
{
	pthread_join(p->poll_thread, NULL);
}




