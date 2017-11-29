#include "socket_context_lizi4.h"
#include "platform_config.h"
#include <unistd.h>


void socket_context_init()
{
}
void socket_context_destroy()
{

}

int socket_context_closed(socket_type soc)
{
	return close(soc);
}

void socket_context_sleep(unsigned long ms)
{
    usleep(ms*1000);
}