#ifndef _INC_pthread_huidiao
#define _INC_pthread_huidiao
//#include <windows.h>
//#include <tchar.h>
//#include <stdio.h>
//#include <exception>
//#include <time.h>
//#include <vld.h>
//#include <string.h>
#include "pthread.h"

typedef void (*pthread_huidiao_type) (void);
typedef void* (*pthread_create_funtion_type) (void *);


struct pthread_huidiao_1
{
//////定时器，计时 int
	int clocktime;

//////进程状态 int *
	int flag_appact;
//////进程声明 pthread_t 
	pthread_t poll_thread;
//////回调函数 pthread_huidiao_type ////typedef void (*pthread_huidiao_type) (void)
	pthread_huidiao_type funpthread_hui;

//////进程锁 pthread_mutex_t
	pthread_mutex_t all;
};

#if defined(__cplusplus)
extern "C"
{
#endif 

extern void lizhi1_huidiao_init(struct pthread_huidiao_1 *p);
extern void lizhi1_huidiao_destroy(struct pthread_huidiao_1*p);
extern void lizhi1_huidiao_start(struct pthread_huidiao_1* p);
extern void lizhi1_huidiao_shutdown(struct pthread_huidiao_1* p);
extern void lizhi1_huidiao_join(struct pthread_huidiao_1*p);


extern void lizhi1_huidiao_lock(struct pthread_huidiao_1*p);
extern void lizhi1_huidiao_unlock(struct pthread_huidiao_1*p);


//  mymutex1
extern void lizhi1_huidiao_f1(void);
//  mymutex1
extern void * lizhi1_huidiao_pthr_1(void * p);

extern void lizhi1_huidiao_test();

#if defined(__cplusplus)
}
#endif 


#endif  /* _INC_pthread_huidiao */