#ifndef _INC_llog
#define _INC_llog

#include "stdio.h"
#include "stdlib.h"
#include "time.h"
#include "pthread.h"

#if defined(__cplusplus)
extern "C"
{
#endif


#if __GNUC__ > 2 || (__GNUC__ == 2 && __GNUC_MINOR__ > 4)
#	define WWMM_STRING_ATTR_PRINTF(fmt, arg) __attribute__((__format__ (__printf__, fmt, arg)))
#else
#	define WWMM_STRING_ATTR_PRINTF(fmt, arg)
#endif

	//vs Compiler
#ifdef _MSC_VER

#else

#endif

	enum log_level
	{
		LOG_EMERG    = 0,
		LOG_FAIL     = 1,
		LOG_ALERT    = 2,
		LOG_ERR      = 3,
		LOG_WARNING  = 4,
		LOG_NOTICE   = 5,
		LOG_INFO     = 6,
		LOG_DEBUG    = 7
	};


	struct llog
	{
		FILE *fp_log;
		char set_log_level;
		//////fp_log  pthread_mutex_t
		pthread_mutex_t mut;
	};


	extern void llog_init(struct llog* p);
	extern void llog_destroy(struct llog* p);
/////////////////////////////////////////////////////////////////////
	extern struct llog* llog_get_instance();
/////////////////////////////////////////////////////////////////////
	extern void llog_set_log_level(struct llog* p,char level);
	extern void llog_open(struct llog* p,const char* path);
	extern void llog_out(struct llog* p,char level , const char *format, ...) WWMM_STRING_ATTR_PRINTF(2, 3);
	extern void llog_close(struct llog* p);
/////////////////////////////////////////////////////////////////////

#define llog_Init() llog_init(llog_get_instance())
#define llog_Destroy() llog_destroy(llog_get_instance())

#define llog_instance(p) struct llog* p = llog_get_instance();
#define llog_Debug(p,fmt, ...) llog_out(p,LOG_DEBUG, fmt, __VA_ARGS__)
#define llog_Info(p,fmt, ...) llog_out(p,LOG_INFO, fmt, __VA_ARGS__)
#define llog_Notice(p,fmt, ...) llog_out(p,LOG_NOTICE, fmt, __VA_ARGS__)
#define llog_Warn(p,fmt, ...) llog_out(p,LOG_WARNING, fmt, __VA_ARGS__)
#define llog_Err(p,fmt, ...) llog_out(p,LOG_ERR, fmt, __VA_ARGS__)
#define llog_Alert(p,fmt, ...) llog_out(p,LOG_ALERT, fmt, __VA_ARGS__)
#define llog_Fail(p,fmt, ...) llog_out(p,LOG_FAIL, fmt, __VA_ARGS__)

#define llog_D(fmt, ...) llog_out(llog_get_instance(),LOG_DEBUG, fmt, __VA_ARGS__)
#define llog_I(fmt, ...) llog_out(llog_get_instance(),LOG_INFO, fmt, __VA_ARGS__)
#define llog_N(fmt, ...) llog_out(llog_get_instance(),LOG_NOTICE, fmt, __VA_ARGS__)
#define llog_W(fmt, ...) llog_out(llog_get_instance(),LOG_WARNING, fmt, __VA_ARGS__)
#define llog_E(fmt, ...) llog_out(llog_get_instance(),LOG_ERR, fmt, __VA_ARGS__)
#define llog_A(fmt, ...) llog_out(llog_get_instance(),LOG_ALERT, fmt, __VA_ARGS__)
#define llog_F(fmt, ...) llog_out(llog_get_instance(),LOG_FAIL, fmt, __VA_ARGS__)

#if defined(__cplusplus)
}
#endif 

#endif  /* _INC_llog */