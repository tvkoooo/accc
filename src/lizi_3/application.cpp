#include "application.h"
#include "core\mm_os_context.h"

//#include "test3.h"

void application_init(struct application* p)
{
	//lizhi_3_xuhanshu();
	return;

	struct mm_os_context* g_os_context = mm_os_context_instance();
	mm_os_context_init(g_os_context);
	mm_os_context_perform(g_os_context);

	lj_lizhi3::fuwuqi_init(&p->f1);
	lj_lizhi3::kehuduan_init(&p->k1);

	p->argc=0;
	p->argv=NULL;
	p->flag_f1=0;
	p->flag_k1=0;
}
void application_destroy(struct application* p)
{
	lj_lizhi3::fuwuqi_destroy(&p->f1);
	lj_lizhi3::kehuduan_destroy(&p->k1);

	p->argc=0;
	p->argv=NULL;
	p->flag_f1=0;
	p->flag_k1=0;

	struct mm_os_context* g_os_context = mm_os_context_instance();
	mm_os_context_destroy(g_os_context);
	// Optional:  Delete all global objects allocated by libprotobuf.
	google::protobuf::ShutdownProtobufLibrary();

}

void application_start(struct application* p)
{	
	if (1==p->flag_f1)
	{
		lj_lizhi3::fuwuqi_fuzhi(&p->f1);
		lj_lizhi3::fuwuqi_start(&p->f1);
	}
	if (1==p->flag_k1)
	{
		lj_lizhi3::kehuduan_fuzhi(&p->k1);
		lj_lizhi3::kehuduan_start(&p->k1);
	}
}
void application_interrupt(struct application* p)
{
	if (1==p->flag_f1)
	{
		lj_lizhi3::fuwuqi_interrupt(&p->f1);
	}
	if (1==p->flag_k1)
	{
		lj_lizhi3::kehuduan_interrupt(&p->k1);
	}
}
void application_shutdown(struct application* p)
{
	if (1==p->flag_f1)
	{
		lj_lizhi3::fuwuqi_shutdown(&p->f1);
	}
	if (1==p->flag_k1)
	{
		lj_lizhi3::kehuduan_shutdown(&p->k1);

	}
}
void application_join(struct application* p)
{
	if (1==p->flag_f1)
	{
		lj_lizhi3::fuwuqi_join(&p->f1);
	}
	if (1==p->flag_k1)
	{
		lj_lizhi3::kehuduan_join(&p->k1);

	}
}

void application_fuzhi(struct application* p,int argc,char **argv)
{
	p->argc=argc;
	p->argv=argv;
	printf("int argc:%d\n",argc);
	while(argc-->1)
	{
		printf("%s\n",*++argv);
	}
	if (1==p->argc)
	{
		p->flag_f1=p->flag_k1=1;
	}
	else
	{
		if (0==strcmp(p->argv[1],"0"))
		{
			p->flag_f1=1;
		}
		if (0==strcmp(p->argv[1],"1"))
		{
			p->flag_k1=1;
		}
	}

}