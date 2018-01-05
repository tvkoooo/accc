#ifndef _INC_application
#define _INC_application

#include "wangluo_fw.h"
#include "wangluo_kh.h"
//#include "builder.h"
//#include "savestore.h"

#include "mysql_co.h"
#include "redis_connect.h"
#include "robot_state_contrl_map.h"

struct application
{
	//struct wangluo_fw fw1;
	//int flag_fw1;
	//struct wangluo_kh kh1;
	//int flag_kh1;
	//int	argc;
	//char **argv;

	struct robot_contrl_map ma1;
	
	//savestore s1;
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