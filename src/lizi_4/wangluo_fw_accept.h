#ifndef __wangluo_fw_accept
#define __wangluo_fw_accept
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>


//维护socket的状态（包含启动和结束）
//做一个accept的线程，并维护他的生命周期
//传递  启动 accept socket套接字信息。
struct wangluo_fw_accept
{
	int flag;
	socket_type slisten;
	socket_type sClient;
	struct sockaddr_in remoteAddr;
	socklen_t nAddrlen;
	char revData[255];
	struct timeval map_timeout;
	std::map <socket_type,fd_set *> map_sel;
	//fd_set set_map;
	//std::map <socket_type,fd_set *> map_sel;
	//struct timeval map_timeout;
};
    
extern void wangluo_fw_accept_start(wangluo_fw_accept *p);
extern void wangluo_fw_accept_close(wangluo_fw_accept *p);




#endif//__wangluo_fw_accept