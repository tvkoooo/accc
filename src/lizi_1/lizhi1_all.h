#ifndef _INC_lizhi1_all
#define _INC_lizhi1_all

#ifdef _WIN32

#ifndef WIN32_LEAN_AND_MEAN
#define WIN32_LEAN_AND_MEAN
#endif//WIN32_LEAN_AND_MEAN

/* enable getenv() and gmtime() in msvc8 */
#ifndef _CRT_SECURE_NO_WARNINGS
#define _CRT_SECURE_NO_WARNINGS
#endif//_CRT_SECURE_NO_WARNINGS
#ifndef _CRT_SECURE_NO_DEPRECATE
#define _CRT_SECURE_NO_DEPRECATE
#endif//_CRT_SECURE_NO_DEPRECATE
/*
	* we need to include <windows.h> explicitly before <winsock2.h> because
	* the warning 4201 is enabled in <windows.h>
	*/
#include <windows.h>
#else
#endif

//#include <tchar.h>
//#include <stdio.h>
//#include <exception>
//#include <time.h>
//#include <vld.h>
//#include <string.h>
//#include "pthread.h"
//#include "pthread_1.h"
//#include "pthread_2.h"
//#include "pthread_huidiao.h"









#endif  /* _INC_lizhi1_all */