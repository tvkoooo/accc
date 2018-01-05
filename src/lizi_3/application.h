#ifndef _INC_application
#define _INC_application
#include <assert.h>
#include <stdio.h>
#include "fuwuqi.h"
#include "kehuduan.h"



struct application
{
	lj_lizhi3::fuwuqi f1;
	lj_lizhi3::kehuduan k1;
	int argc;
	char **argv;
	int flag_f1;
	int flag_k1;
};

extern void application_init(struct application* p);
extern void application_destroy(struct application* p);
///////////////////////////////////////////////////
extern void application_fuzhi(struct application* p,int argc,char **argv);

extern void application_start(struct application* p);
extern void application_interrupt(struct application* p);
extern void application_shutdown(struct application* p);
extern void application_join(struct application* p);

#endif  /* _INC_application */