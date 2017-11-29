#include "errno_lizi4.h"
#include "platform_config.h"
#include <errno.h>
#include <string.h>

const char* errnomber(int err)
{
	return (const char*)strerror(err);
}