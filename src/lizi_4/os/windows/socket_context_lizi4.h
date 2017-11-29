#ifndef _INC_socket_context
#define _INC_socket_context

#include "platform_config.h"

#include "c_start.h"

typedef SOCKET socket_type;

#define PP_INVALID_SOCKET INVALID_SOCKET

extern void socket_context_init();
extern void socket_context_destroy();

extern int socket_context_closed(socket_type soc);

extern void socket_context_sleep(unsigned long ms);


#include "c_end.h"
#endif  /* _INC_socket_context */