#ifndef _INC_pthread_appaction
#define _INC_pthread_appaction
#include "pthread_huidiao.h"
#include "mm_uuu.h"

struct appaction
{
	struct pthread_huidiao_1 huidiaotest_1;
	struct mm_uuu m1;

};

extern void appaction_init(struct appaction* p);
extern void appaction_destroy(struct appaction* p);
///////////////////////////////////////////////////
extern void appaction_start(struct appaction* p);
extern void appaction_interrupt(struct appaction* p);
extern void appaction_shutdown(struct appaction* p);
extern void appaction_join(struct appaction* p);

#endif  /* _INC_pthread_appaction */