#ifndef __error_desc_h__
#define __error_desc_h__
#include "library_export_common.h"
#include "platform_config.h"

#include "c_start.h"
#define pp_errno() GetLastError()
LIB_EXPORT_COMMON const char* errnomber(int err);
#include "c_end.h"
#endif  /* __error_desc_h__ */