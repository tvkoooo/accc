#ifndef _INC_dog
#define _INC_dog


struct dog
{

	char dog_name[20];

	char dog_talk1[20];
	char dog_talk2[20];
	char dog_talk3[20];
	char dog_talk4[20];
};

extern void dogname_init(struct dog *pscanf);
extern void dogname_destroy(struct dog *pscanf);
//////////////////////////////////////////////////////////////////////////
extern void dogtalk_init(struct dog *pscanf);
extern void dogtalk_destroy(struct dog *pscanf);
//////////////////////////////////////////////////////////////////////////
extern void dog_talk(struct dog *pscanf);
//extern void juzhenB(struct dog *pB);
//extern void juzhenC(struct dog *pC);
//extern void dog_printjuzhen(struct dog *pprint);
//extern void fdog_juchen(struct dog *pchen);
//extern void dog_fjujia(struct dog *pjia);
//extern void dog_fjujian(struct dog *pjian);
//extern void dog_li5();
extern int shuijishu();
extern void li5();
#endif  /* _INC_dog */