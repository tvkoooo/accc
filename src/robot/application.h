#ifndef _INC_application
#define _INC_application

#include "llog.h"
#include "fileopr.h"
#include "ltcp.h"
#include "lmaths.h"
#include "dataslot.h"
#include "lprotobuff.h"


#include "error_desc.h"

struct application
{
	struct ltcp_conn conn;
	int argc;
	char** argv;

	char* log_path;
	int   log_level;
	char* instance;
	char* service_num;
	char* object_net;
	int   object_port;

	struct lj_data_slot d_slot;

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