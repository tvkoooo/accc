#ifndef __socket_context_h__
#define __socket_context_h__

#include "platform_config.h"

#include "library_export_common.h"

#include "c_start.h"


typedef SOCKET socket_type;

#define PP_INVALID_SOCKET INVALID_SOCKET

LIB_EXPORT_COMMON void socket_context_init();
LIB_EXPORT_COMMON void socket_context_destroy();

LIB_EXPORT_COMMON int socket_context_closed(socket_type soc);

LIB_EXPORT_COMMON void socket_context_sleep(unsigned long ms);


#include "c_end.h"
#endif  /* __socket_context_h__ */