#ifndef _INC_application
#define _INC_application
#include "wangluo.h"
#include "wangluo_fw.h"
#include "wangluo_kh.h"

struct application
{
	struct wangluo_fw fw1;
	struct wangluo_kh kh1;
};

extern void application_init(struct application* p);
extern void application_destroy(struct application* p);
///////////////////////////////////////////////////
extern void application_fuzhi(struct application* p,int argc,char **argv);
///////////////////////////////////////////////////
extern void application_start(struct application* p);
extern void application_wait(struct application* p);
extern void application_interrupt(struct application* p);
extern void application_shutdown(struct application* p);
extern void application_join(struct application* p);

#endif  /* _INC_application */