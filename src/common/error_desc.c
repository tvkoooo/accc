#include "error_desc.h"

LIB_EXPORT_COMMON const char* errnomber(int err)
{
	HLOCAL LocalAddress=NULL;
	FormatMessage(FORMAT_MESSAGE_ALLOCATE_BUFFER|FORMAT_MESSAGE_IGNORE_INSERTS|FORMAT_MESSAGE_FROM_SYSTEM,NULL,err,0,(PTSTR)&LocalAddress,0,NULL);
	return (LPSTR)LocalAddress;
}