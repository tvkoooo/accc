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
	p->state = ts_closed;
}
void mm_uuu_destroy(struct mm_uuu* p)
{
	p->state = ts_closed;
}
//
void mm_uuu_poll_wait(struct mm_uuu* p)
{
	while( ts_motion == p->state )
	{
		// the first quick size checking.
		printf("xxx\n");
		mm_msleep(1000);
	}
}
//
void mm_uuu_start(struct mm_uuu* p)
{
	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &__static_uuu_poll_wait_thread, p);
}
void mm_uuu_interrupt(struct mm_uuu* p)
{
	p->state = ts_closed;
}
void mm_uuu_shutdown(struct mm_uuu* p)
{
	p->state = ts_finish;
}
void mm_uuu_join(struct mm_uuu* p)
{
	pthread_join(p->poll_thread, NULL);
}
static void* __static_uuu_poll_wait_thread(void* arg)
{
	struct mm_uuu* p = (struct mm_uuu*)(arg);
	mm_uuu_poll_wait(p);
	return NULL;
}