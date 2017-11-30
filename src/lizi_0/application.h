#ifndef _INC_application
#define _INC_application

struct application
{
	int a;
};

#if defined(__cplusplus)
extern "C"
{
#endif


extern void application_init(struct application* p);
extern void application_destroy(struct application* p);
///////////////////////////////////////////////////

extern void application_fuzhi(struct application* p,int argc,char **argv);
extern void application_start(struct application* p);
extern void application_interrupt(struct application* p);
extern void application_shutdown(struct application* p);
extern void application_join(struct application* p);

extern int shuijishu();
#if defined(__cplusplus)
}
#endif 
#endif  /* _INC_application */