#include "mm_uuu.h"
#include <stdio.h>

#ifdef _WIN32

#ifndef WIN32_LEAN_AND_MEAN
#define WIN32_LEAN_AND_MEAN
#endif//WIN32_LEAN_AND_MEAN

/* enable getenv() and gmtime() in msvc8 */
#ifndef _CRT_SECURE_NO_WARNINGS
#define _CRT_SECURE_NO_WARNINGS
#endif//_CRT_SECURE_NO_WARNINGS
#ifndef _CRT_SECURE_NO_DEPRECATE
#define _CRT_SECURE_NO_DEPRECATE
#endif//_CRT_SECURE_NO_DEPRECATE
/*
* we need to include <windows.h> explicitly before <winsock2.h> because
* the warning 4201 is enabled in <windows.h>
*/
#include <windows.h>

#define mm_msleep Sleep
#else
#define mm_msleep(ms)        (void) usleep((ms) * 1000)
#endif

static void* __static_uuu_poll_wait_thread(void* arg);

void mm_uuu_init(struct mm_uuu* p)
{
	DWORD id=GetCurrentThreadId();
	p->state = ts_closed;
	printf("mm_uuu_init  %d\n",id);
}
void mm_uuu_destroy(struct mm_uuu* p)
{
	DWORD id=GetCurrentThreadId();
	p->state = ts_closed;
	printf("mm_uuu_destroy  %d\n",id);
}
//
void mm_uuu_poll_wait(struct mm_uuu* p)
{
	DWORD id1=GetCurrentThreadId();
	printf("mm_uuu_poll_wait 开始 %d\n",id1);
	while( ts_motion == p->state )
	{
		DWORD id2=GetCurrentThreadId();
		// the first quick size checking.
		printf("xxx %d\n",id2);
		mm_msleep(1000);
	}
	DWORD id3=GetCurrentThreadId();
	printf("mm_uuu_poll_wait 结束  %d\n",id3);
}
//
void mm_uuu_start(struct mm_uuu* p)
{

	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
	DWORD id=GetCurrentThreadId();
	printf("mm_uuu_start  %d\n",id);
}
void mm_uuu_interrupt(struct mm_uuu* p)
{
	DWORD id=GetCurrentThreadId();
	p->state = ts_closed;
	printf("mm_uuu_interrupt  %d\n",id);
}
void mm_uuu_shutdown(struct mm_uuu* p)
{
	p->state = ts_finish;
	DWORD id=GetCurrentThreadId();
	printf("mm_uuu_shutdown  %d\n",id);
}
void mm_uuu_join(struct mm_uuu* p)
{
	DWORD id0=GetCurrentThreadId();
	printf("mm_uuu_join 开始  %d\n",id0);

	pthread_join(p->poll_thread, NULL);
	DWORD id=GetCurrentThreadId();
	printf("mm_uuu_join 结束 %d\n",id);
}
static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct mm_uuu* p = (struct mm_uuu*)(arg);
	DWORD id0=GetCurrentThreadId();
	printf("__static_uuu_poll_wait_thread开始  %d\n",id0);

	mm_uuu_poll_wait(p);
	DWORD id2=GetCurrentThreadId();
	printf("__static_uuu_poll_wait_thread结束  %d\n",id2);
	return NULL;
}