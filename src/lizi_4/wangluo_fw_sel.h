#ifndef __wangluo_fw_sel
#define __wangluo_fw_sel
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>

//维护socket的状态（包含启动和结束）
//做一个accept的线程，并维护他的生命周期
//处理接收过来的recv的等待。


//启动  recv  利用 accept socket套接字，等待客户传递信息
//启动  send  利用 accept socket套接字，传递信息给客户
struct wangluo_fw_sel
{
	int flag;
	socket_type sClient;
	std::map <socket_type,fd_set *> map_sel;
	struct timeval map_timeout;
};
    
extern void *wangluo_fw_sel_pro(void *p);





#endif//__wangluo_fw_sel