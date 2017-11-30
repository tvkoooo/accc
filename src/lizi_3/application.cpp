#include "application.h"
#include "test3.h"

void application_init(struct application* p)
{

}
void application_destroy(struct application* p)
{

}

void application_start(struct application* p)
{	
	    lizhi3_test3();
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
	printf("int argc:%d",argc);
	while(argc-->1)
	{
		printf("%s",*++argv);
	}

}