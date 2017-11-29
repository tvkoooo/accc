#ifndef _INC_juzhen
#define _INC_juzhen




struct chen_juzhen4
{
	float juzhen4[4][4];

};

#if defined(__cplusplus)
extern "C"
{
#endif



extern void juzhen4_chen_4juzhen();
extern void juzhen4_shuru(struct chen_juzhen4 *p);
extern void juzhen4_dianchen(struct chen_juzhen4 *pa,struct chen_juzhen4 *pb,struct chen_juzhen4 *pchu);
extern void juzhen4_printf(struct chen_juzhen4 *p);
extern int shuijishu();

#if defined(__cplusplus)
}
#endif 


#endif  /* _INC_juzhen */