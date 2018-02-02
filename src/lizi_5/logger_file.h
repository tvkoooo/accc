#ifndef __logger_file_h__
#define __logger_file_h__
#include <stdarg.h>
#include <string>

#if __GNUC__ > 2 || (__GNUC__ == 2 && __GNUC_MINOR__ > 4)
#	define MM_STD_STRING_ATTR_PRINTF(fmt, arg) __attribute__((__format__ (__printf__, fmt, arg)))
#else
#	define MM_STD_STRING_ATTR_PRINTF(fmt, arg)
#endif

// a temporary logger file.
//////////////////////////////////////////////////////////////////////////
int logger_file_std_string_vsprintf( std::string& p, const char *fmt, va_list ap ) MM_STD_STRING_ATTR_PRINTF(2, 0);
//////////////////////////////////////////////////////////////////////////
//#define	LOG_EMERG	0	/* system is unusable */
//#define	LOG_ALERT	1	/* action must be taken immediately */
//#define	LOG_CRIT	2	/* critical conditions */
//#define	LOG_ERR		3	/* error conditions */
//#define	LOG_WARNING	4	/* warning conditions */
//#define	LOG_NOTICE	5	/* normal but significant condition */
//#define	LOG_INFO	6	/* informational */
//#define	LOG_DEBUG	7	/* debug-level messages */
enum
{
	OO_Fatal  = 0,
	OO_ALERT  = 1,
	OO_CRIT   = 2,
	OO_Error  = 3,
	OO_Warn   = 4,
	OO_Notice = 5,
	OO_Info   = 6,
	OO_Debug  = 7,
};
struct logger_file_level_mark
{
	const char* m;// mark
	const char* n;// name
};
const struct logger_file_level_mark* logger_file_logger_level_mark(int lvl);
//////////////////////////////////////////////////////////////////////////
void logger_file_init();
void logger_file_destroy();
void logger_file_assign_file(const char* file_path,const char* file_name);
void logger_file_assign_level(int lvl);
void logger_file_assign_file_size(size_t file_size);
void logger_file_log(int lvl, const char *fmt, ...);
void logger_file_callback_printf(int lvl, const char* message);
//////////////////////////////////////////////////////////////////////////

#endif//__logger_file_h__