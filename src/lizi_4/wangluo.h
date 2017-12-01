#ifndef _INC_lizhi4_wangluo
#define _INC_lizhi4_wangluo
#include <iostream>
using namespace std;
#include <pthread.h>
#include "socket_context_lizi4.h"

struct wangluo
{
	int argc;
	char **argv;
	int wangluoshut;
	pthread_t id_k;
	int ctrl_idk;// 客户进程 id_k 控制变量
	pthread_t id_f;
	int ctrl_idf;// 服务器进程 id_f 控制变量

	//socket_type slisten;

};
extern void wangluo_init(struct wangluo* p);
extern void wangluo_destroy(struct wangluo* p);
///////////////////////////////////////////////////
extern void wangluo_fuzhi(struct wangluo* p,int argc,char **argv);
///////////////////////////////////////////////////
extern void wangluo_start(struct wangluo* p);
extern void wangluo_wait(struct wangluo* p);
extern void wangluo_interrupt(struct wangluo* p);
extern void wangluo_shutdown(struct wangluo* p);
extern void wangluo_join(struct wangluo* p);


extern void *lizhi4_wangluo_fuwu(void *p);
extern void *lizhi4_wangluo_kehu(void *p);


#endif  /* _INC_lizhi4_wangluo */