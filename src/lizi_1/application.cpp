#include "application.h"
#include "pthread.h"
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <windows.h>
#include "appaction.h"
#include "fun_log_dll.h"

void application_init(struct application* p)
{

}
void application_destroy(struct application* p)
{

}

void application_start(struct application* p)
{	
	char bbc[30]="wo kao ni lao mao,ri o ";
	int ak=5;
	fun_log_fprintf(bbc);
	printf("%s",bbc);

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