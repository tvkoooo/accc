#include "llog.h"
#include <stdarg.h>


static struct llog aaaa;

struct llog* llog_get_instance()
{
	return &aaaa;
}

void llog_init(struct llog* p)
{
	pthread_mutex_init(&p->mut,NULL);
}

void llog_destroy(struct llog* p)
{
	llog_close(p);
	pthread_mutex_destroy(&p->mut);
}

void llog_set_log_level(struct llog* p,char level)
{
	p->set_log_level = level;
}

void llog_open(struct llog* p,const char* path)
{
	pthread_mutex_lock(&p->mut);
	if (p->fp_log == NULL)
	{
		p->fp_log=fopen(path,"ab");
	}else{
		fflush(p->fp_log);
		fclose(p->fp_log);
		p->fp_log=fopen(path,"ab");
	}
	pthread_mutex_unlock(&p->mut);
}

static const char* log_desc[]=
{
	"EMERG",
	"FAIL",
	"ALERT",
	"ERROR",
	"WARNING",
	"NOTICE",
	"INFO",
	"DEBUG"
};

void llog_out(struct llog* p,char level , const char *format, ...)
{
	if (p->set_log_level >= level )
	{
		va_list ap;
		time_t time_log = time(NULL);
		struct tm* tm_log = localtime(&time_log);
		va_start(ap, format);		
		pthread_mutex_lock(&p->mut);
		fprintf(p->fp_log,"[%04d-%02d-%02d %02d:%02d:%02d][%s]",tm_log->tm_year + 1900, tm_log->tm_mon + 1, tm_log->tm_mday, tm_log->tm_hour, tm_log->tm_min, tm_log->tm_sec,log_desc[level]);
		vfprintf(p->fp_log,format,ap);
		pthread_mutex_unlock(&p->mut);
		va_end(ap);
	}
}

void llog_close(struct llog* p)
{
	pthread_mutex_lock(&p->mut);
	if (p->fp_log != NULL)
	{
		fflush(p->fp_log);
		fclose(p->fp_log);
		p->fp_log = NULL;
	} 
	pthread_mutex_unlock(&p->mut);
}

