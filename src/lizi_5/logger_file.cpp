#include "logger_file.h"


#include <time.h>
#include <stdio.h>

#include <pthread.h>

#ifndef va_copy 
# ifdef __va_copy 
# define va_copy(DEST,SRC) __va_copy((DEST),(SRC)) 
# else 
# define va_copy(DEST, SRC) memcpy((&DEST), (&SRC), sizeof(va_list)) 
# endif 
#endif

#ifndef mm_roundup32 // round a 32-bit integer to the next closet integer; from "bit twiddling hacks"
#define mm_roundup32(x) (--(x), (x)|=(x)>>1, (x)|=(x)>>2, (x)|=(x)>>4, (x)|=(x)>>8, (x)|=(x)>>16, ++(x))
#endif

#if (_MSC_VER)
#	define mm_vsnprintf vsnprintf
#	define mm_snprintf _snprintf
#else
#	define mm_vsnprintf vsnprintf
#	define mm_snprintf snprintf
#endif

#if (_MSC_VER)
#include <winsock2.h>

#if defined(_MSC_VER) || defined(_MSC_EXTENSIONS)
#define DELTA_EPOCH_IN_MICROSECS  11644473600000000Ui64
#else
#define DELTA_EPOCH_IN_MICROSECS  11644473600000000ULL
#endif

struct timezone 
{
	int tz_minuteswest; /* minutes W of Greenwich */
	int tz_dsttime;     /* type of dst correction */
};
static int __static_gettimeofday(struct timeval *tp, struct timezone *tz)
{
	FILETIME ft;
	unsigned __int64 tmpres = 0;
	static int tzflag = 0;

	if (NULL != tp)
	{
		GetSystemTimeAsFileTime(&ft);

		tmpres |= ft.dwHighDateTime;
		tmpres <<= 32;
		tmpres |= ft.dwLowDateTime;

		tmpres /= 10;  /*convert into microseconds*/
		/*converting file time to unix epoch*/
		tmpres -= DELTA_EPOCH_IN_MICROSECS; 
		tp->tv_sec = (long)(tmpres / 1000000UL);
		tp->tv_usec = (long)(tmpres % 1000000UL);
	}

	if (NULL != tz)
	{
		if (!tzflag)
		{
			_tzset();
			tzflag++;
		}
		tz->tz_minuteswest = _timezone / 60;
		tz->tz_dsttime = _daylight;
	}

	return 0;
}
#else
#include <time.h>
#include <sys/time.h>
#define __static_gettimeofday(tp,tz) gettimeofday(tp,tz)
#endif

struct logger_file
{
	FILE* stream;
	std::string file_path;
	std::string file_name;
	int level;
	size_t file_size;
	pthread_mutex_t mutex;
};

struct logger_file* g_logger_file = NULL;

int logger_file_std_string_vsprintf( std::string& p, const char *fmt, va_list ap )
{
	va_list args;
	int l;
	do 
	{
		// This line does not work with glibc 2.0. See `man snprintf'.
		// if buffer size is 0.l will a real len.
		va_copy(args, ap);
		l = mm_vsnprintf((char*)p.data() + p.size(), p.capacity() - p.size(), fmt, args);
		va_end(args);
		if ( 0 < l && (size_t)(l + 1) > p.capacity() - p.size() ) 
		{
			int length = p.size() + l + 2;
			mm_roundup32(length);
			p.reserve(length);
			// write again.
			va_copy(args, ap);
			l = mm_vsnprintf((char*)p.data() + p.size(), p.capacity() - p.size(), fmt, args);
			va_end(args);
		}
		if ( 0 > l )
		{
			int length = p.capacity() * 2;
			mm_roundup32(length);
			p.reserve(length);
		}
	} while ( 0 > l );
	// p.resize(p.size() + l);
	return l;
}

static void __static_current_time_string_file(std::string& time_string)
{
	time_t tt = time(NULL);
	tm* t = localtime(&tt);
	time_string.resize(128);
	sprintf((char*)time_string.data(), "%.4d-%.2d-%.2d_%.2d_%.2d_%.2d",
		t->tm_year+1900, t->tm_mon + 1, t->tm_mday,
		t->tm_hour, t->tm_min, t->tm_sec);
}
//////////////////////////////////////////////////////////////////////////
static struct logger_file_level_mark const_logger_file_level_mark[] = 
{
	{"F","fatal",},
	{"A","alert",},
	{"C","crit",},
	{"E","error",},
	{"W","warning",},
	{"N","notice",},
	{"I","info",},
	{"D","debug",},
};

const struct logger_file_level_mark* logger_file_logger_level_mark(int lvl)
{
	if (OO_Fatal <= lvl && lvl <= OO_Debug)
	{
		return &const_logger_file_level_mark[lvl];
	}
	else
	{
		return &const_logger_file_level_mark[OO_Debug];
	}
}
//////////////////////////////////////////////////////////////////////////
// 1992/01/26 05:20:14-001
static void __static_current_time_string_logger(struct timeval* tv, char* time_string)
{
	time_t tt = tv->tv_sec;
	tm* t = localtime(&tt);
	sprintf(time_string, "%.4d/%.2d/%.2d %.2d:%.2d:%.2d-%.3d",
		t->tm_year+1900, t->tm_mon + 1, t->tm_mday,
		t->tm_hour, t->tm_min, t->tm_sec, (int)(tv->tv_usec/1000));
}
//////////////////////////////////////////////////////////////////////////
void logger_file_init()
{
	// 100 M = 104857600 = 100 * 1024 * 1024
	g_logger_file = new struct logger_file;
	//
	g_logger_file->stream = NULL;
	g_logger_file->file_path = "logger.log";
	g_logger_file->level = 6;
	g_logger_file->file_size = 104857600;
	pthread_mutex_init(&g_logger_file->mutex, NULL);
}
void logger_file_destroy()
{
	if (NULL != g_logger_file->stream)
	{
		fflush(g_logger_file->stream);
		fclose(g_logger_file->stream);
	}
	g_logger_file->stream = NULL;
	g_logger_file->file_path = "logger.log";
	g_logger_file->level = 6;
	g_logger_file->file_size = 104857600;
	pthread_mutex_destroy(&g_logger_file->mutex);
	//
	delete g_logger_file;
	g_logger_file = NULL;
}
void logger_file_assign_file(const char* file_path,const char* file_name)
{
	g_logger_file->file_path = file_path;
	g_logger_file->file_name = file_name;
	char c = 0;
	std::string::reverse_iterator it = g_logger_file->file_path.rbegin();
	if (it != g_logger_file->file_path.rend())
	{
		c = *it;
		if (!(c=='/'||c=='\\'))
		{
			 g_logger_file->file_path= g_logger_file->file_path + '/';
		}
	}

	std::string file_all_name = g_logger_file->file_path + g_logger_file->file_name;
	if (NULL != file_path)
	{
		g_logger_file->stream = fopen(file_all_name.c_str(),"ab+");
	}
}

void logger_file_assign_level(int lvl)
{
	g_logger_file->level = lvl;
}
void logger_file_assign_file_size(size_t file_size)
{
	g_logger_file->file_size=file_size;
}
void logger_file_log(int lvl, const char *fmt, ...)
{
	std::string message_buffer;
	va_list ap;
	va_start(ap, fmt);
	logger_file_std_string_vsprintf(message_buffer, fmt, ap);
	va_end(ap);
	//
	logger_file_callback_printf(lvl,message_buffer.c_str());
}

void logger_file_callback_printf(int lvl, const char* message)
{
	if (NULL != g_logger_file)
	{
		if ( lvl > g_logger_file->level )
		{
			// logger filter.
			return;
		}
		if (NULL != g_logger_file->stream)
		{
			const struct logger_file_level_mark* mark = logger_file_logger_level_mark( lvl );
			char log_head_suffix[16] = {0};
			char section[8] = "mm";
			char time_stamp_string[32] = {0};

			struct timeval tv;
			__static_gettimeofday(&tv, NULL);
			__static_current_time_string_logger(&tv, time_stamp_string);
			// [ 8 V ]
			sprintf(log_head_suffix," %d %s ",lvl,mark->n);

			pthread_mutex_lock(&g_logger_file->mutex);
			//
			fputs(time_stamp_string,g_logger_file->stream);
			fputs(log_head_suffix,g_logger_file->stream);
			fputs(section,g_logger_file->stream);
			fputc(' ',g_logger_file->stream);
			fputs(message,g_logger_file->stream);
			fputc('\n',g_logger_file->stream);
			//
			pthread_mutex_unlock(&g_logger_file->mutex);
		}
		if (NULL != g_logger_file->stream)
		{
			if ( (size_t)ftell(g_logger_file->stream) >= g_logger_file->file_size)
			{
				fflush(g_logger_file->stream);
				fclose(g_logger_file->stream);
				g_logger_file->stream = NULL;

				std::string file_name;
				__static_current_time_string_file(file_name);
				file_name = std::string("") + file_name.c_str()+".log";
				std::string file_all_name =g_logger_file->file_path + g_logger_file->file_name;
				std::string file_all_name_old =g_logger_file->file_path + file_name;

				rename(file_all_name.c_str(),file_all_name_old.c_str());

				g_logger_file->stream = fopen(file_all_name.c_str(),"ab+");
			}
		}
	}
}
