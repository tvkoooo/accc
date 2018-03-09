#include "application.h"
//#include "core/mm_os_context.h"

//#include "test3.h"
extern "C"
{
	extern int amqp_connect_timeout_main(int argc, char **argv);
};

void application_init(struct application* p)
{

}
void application_destroy(struct application* p)
{


}

void application_start(struct application* p)
{	

}
void application_interrupt(struct application* p)
{

}
void application_shutdown(struct application* p)
{

}
void application_join(struct application* p)
{

}

void application_fuzhi(struct application* p,int argc,char **argv)
{

	amqp_connect_timeout_main(argc, argv);
}