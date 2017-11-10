#ifndef _INC_recordput
#define _INC_recordput


struct bodytest_new
{
	char bodytest_name[20];
	int  bodytest_age;
	int  bodytest_score[4];
	int  bodytest_mima[3][3];

};


extern void input_record_test();

//extern void juzhen4_chen_4juzhen();
//extern void juzhen4_shuru(struct chen_juzhen4 *p);
//extern void juzhen4_dianchen(struct chen_juzhen4 *pa,struct chen_juzhen4 *pb,struct chen_juzhen4 *pchu);
//extern void juzhen4_printf(struct chen_juzhen4 *p);
//extern int shuijishu();
#endif  /* _recordput */