#ifndef _INC_application
#define _INC_application
#include "wangluo.h"

struct application
{
	struct wangluo w1;
};

extern void application_init(struct application* p);
extern void application_destroy(struct application* p);
///////////////////////////////////////////////////
extern void application_fuzhi(struct application* p,int argc,char **argv);
///////////////////////////////////////////////////
extern void application_start(struct application* p);
extern void application_interrupt(struct application* p);
extern void application_shutdown(struct application* p);
extern void application_join(struct application* p);

#endif  /* _INC_application */