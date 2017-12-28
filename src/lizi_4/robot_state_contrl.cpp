#include "robot_state_contrl.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdint.h>
#include <time.h>

//#include "platform_config.h"

#define MM_MSEC_PER_SEC 1000
#define MM_USEC_PER_SEC 1000000
#define MM_NSEC_PER_SEC 1000000000

#ifdef _MSC_VER

#ifndef WIN32_LEAN_AND_MEAN
#define WIN32_LEAN_AND_MEAN
#endif//WIN32_LEAN_AND_MEAN

#include <windows.h>
#include <winsock2.h>


int mm_gettimeofday(struct timeval *tp, struct timezone *tz)
{
    uint64_t  intervals;
    FILETIME  ft;

    GetSystemTimeAsFileTime(&ft);

    /*
     * A file time is a 64-bit value that represents the number
     * of 100-nanosecond intervals that have elapsed since
     * January 1, 1601 12:00 A.M. UTC.
     *
     * Between January 1, 1970 (Epoch) and January 1, 1601 there were
     * 134744 days,
     * 11644473600 seconds or
     * 11644473600,000,000,0 100-nanosecond intervals.
     *
     * See also MSKB Q167296.
     */

    intervals = ((uint64_t) ft.dwHighDateTime << 32) | ft.dwLowDateTime;
    intervals -= 116444736000000000;

    tp->tv_sec = (long) (intervals / 10000000);
    tp->tv_usec = (long) ((intervals % 10000000) / 10);
	return 0;
}
#define mm_msleep(v) Sleep(v)
#else
#include <sys/time.h>
#include <unistd.h>
#define mm_gettimeofday gettimeofday
#define mm_msleep(v) usleep((1000 * v))
#endif

int mm_timedwait_nearby(pthread_cond_t* signal_cond, pthread_mutex_t* signal_mutex, struct timeval* ntime, struct timespec* otime, int _nearby_time)
{
	int rt = 0;
	// some os pthread timedwait impl precision is low.
	// MM_MSEC_PER_SEC check can avoid some precision problem
	// but will extend the application shutdown time.
	if ( 0 == _nearby_time )
	{
		// next loop immediately.
		rt = 0;
	}
	else if( MM_MSEC_PER_SEC < _nearby_time )
	{
		// timedwait a while.
		int _a = _nearby_time / 1000;
		int _b = _nearby_time % 1000;
		mm_gettimeofday(ntime, NULL);
		otime->tv_sec  = (ntime->tv_sec  + _a        );
		otime->tv_nsec = (ntime->tv_usec + _b * 1000 ) * 1000;
		otime->tv_sec += otime->tv_nsec / MM_NSEC_PER_SEC;
		otime->tv_nsec = otime->tv_nsec % MM_NSEC_PER_SEC;
		//
		pthread_mutex_lock(signal_mutex);
		rt = pthread_cond_timedwait(signal_cond,signal_mutex,otime);
		pthread_mutex_unlock(signal_mutex);
	}
	else
	{
		// msleep a while.
		mm_msleep(_nearby_time);
		rt = 0;
	}
	return rt;
}


	void pthread_huidiao(struct robot_contrl* obj )
	{

	}

	static void* __static_uuu_poll_wait_thread(void* arg)
	{
		struct robot_contrl* p = (struct robot_contrl*)(arg);
		robot_contrl_poll_wait(p);
		return NULL;
	}

	void robot_contrl_init(struct robot_contrl *p)
	{
		p->sid=0;
		p->chushijiqiren=chushijiqiren_default;
		p->suijijiqiqren=suijijiqiqren_default;
		p->shuimin_time=shuimin_time_default;
		p->xunhuanceshu=0;
		p->uaddtotalnum=0;

		pthread_cond_init(&p->c,NULL);
		pthread_mutex_init(&p->m,NULL);
		p->state = ts_closed;
		p->funpthread_hui=&pthread_huidiao;
		p->u = NULL;
		pthread_mutex_init(&p->locker,NULL);
	}
	void robot_contrl_destroy(struct robot_contrl*p)
	{
		p->sid=0;
		p->chushijiqiren=chushijiqiren_default;
		p->suijijiqiqren=suijijiqiqren_default;
		p->shuimin_time=shuimin_time_default;
		p->xunhuanceshu=0;
		p->uaddtotalnum=0;
		pthread_cond_destroy(&p->c);
		pthread_mutex_destroy(&p->m);
		p->state = ts_closed;
		p->funpthread_hui=&pthread_huidiao;
		p->u = NULL;
		pthread_mutex_destroy(&p->locker);
	}

	void robot_contrl_fuzhi(struct robot_contrl*p,robot_huidiao_type f, void* u)
	{
		p->funpthread_hui=f;
		p->u=u;
	}

	void robot_contrl_poll_wait(struct robot_contrl* p)
	{
		struct timeval ntime;
		struct timespec otime;
		while( ts_motion == p->state )
		{
			(*(p->funpthread_hui))(p);
			mm_timedwait_nearby(&p->c,&p->m,&ntime,&otime,p->shuimin_time);
		}
	}

	void robot_contrl_start(struct robot_contrl* p)
	{
		p->state = ts_finish == p->state ? ts_closed : ts_motion;
		pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
	}
	void robot_contrl_shutdown(struct robot_contrl* p)
	{
		p->state = ts_finish;
		pthread_mutex_lock(&p->m);
		pthread_cond_signal(&p->c);
		pthread_mutex_unlock(&p->m);
	}

	void robot_contrl_interrupt(struct robot_contrl* p)
	{
		p->state = ts_closed;
		pthread_mutex_lock(&p->m);
		pthread_cond_signal(&p->c);
		pthread_mutex_unlock(&p->m);
	}
	

	void robot_contrl_join(struct robot_contrl*p)
	{
		if (p->state== ts_motion)
		{
			pthread_mutex_lock(&p->m);
			pthread_cond_wait(&p->c,&p->m);
			pthread_mutex_unlock(&p->m);
		}
		pthread_join(p->poll_thread, NULL);
	}


	void robot_contrl_rand_number(struct robot_contrl*p)
	{
		p->uaddtotalnum = p->chushijiqiren + rand() % p->suijijiqiqren;
	}

	void robot_contrl_lock(struct robot_contrl*p)
	{
		pthread_mutex_lock(&p->locker);
	}
	void robot_contrl_unlock(struct robot_contrl*p)
	{
		pthread_mutex_unlock(&p->locker);
	}
