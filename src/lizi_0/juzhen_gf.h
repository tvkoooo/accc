#ifndef _INC_juzhen_gf
#define _INC_juzhen_gf


struct juzhen_gf
{
	float juzhen_gf1[4][4];

};


#if defined(__cplusplus)
extern "C"
{
#endif


//extern void juzhen4_gf_malloc(struct juzhen_gf *p,int num);
//extern void juzhen4_gf_delloc(struct juzhen_gf *p);

extern void juzhen4_gf_init(struct juzhen_gf *p,int num);
extern void juzhen4_gf_destory(struct juzhen_gf *p,int num);

extern void juzhen4_gf_do_assignment(struct juzhen_gf *p,int num);
extern void juzhen4_gf_do_2Multip2(struct juzhen_gf *pa,struct juzhen_gf *pb,struct juzhen_gf *pchu);
extern void juzhen4_gf_do_print(struct juzhen_gf *p);


extern void juzhen4_gf_test();

#if defined(__cplusplus)
}
#endif 

#endif  /* _INC_juzhen_gf */