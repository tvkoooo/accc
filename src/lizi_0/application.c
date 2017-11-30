#include "application.h"
//#include <all.h>
#include <vld.h>
#include <stdlib.h>
#include <time.h>
#include "tou.h"
#include <stdio.h>
#include "juzhen_gf.h"
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
	li2();
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

void application_fuzhi(struct application* p,int argc,char **argv)
{
	printf("int argc:%d",argc);
	while(argc-->1)
	{
		printf("%s",*++argv);
	}

}