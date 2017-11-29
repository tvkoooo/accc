#include "application.h"
#include <all.h>
#include <vld.h>
int shuijishu()
{
	return rand();
}


void application_init(struct application* p)
{

}
void application_destroy(struct application* p)
{

}

void application_start(struct application* p)
{	
	srand((int)time(0));

	fprintf(stderr, "\n\n%s\t%d\n\n", __FILE__, __LINE__);

	juzhen4_gf_test();
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