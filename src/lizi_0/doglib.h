#ifndef _INC_doglib
#define _INC_doglib


struct doglib
{

	int dog_flag;
///////////////////////////////////
	char   dog_name[20];
	char   dog_hair[20];
	float  dog_type;
///////////////////////////////////
    float  dog_money_Price;
	float  dog_money_buy;
	float  dog_money_sell;
///////////////////////////////////
	int    dog_friend;
	int    dog_hunger;
	double dog_train;
	///////////////////////////////////
	int  dog_play;
	int  dog_feed;
	int  dog_actnumb;
	double  dog_act_money;
///////////////////////////////////
};

struct dogtalk
{
	char dog_act[8][20];
};

#if defined(__cplusplus)
extern "C"
{
#endif


extern struct dogtalk G_dogtalk;

extern void feeddog(struct doglib *p_dog,struct leadlib*p_lead);
extern void Strokedog(struct doglib *p_dog,struct leadlib*p_lead);
extern void changenamedog(struct doglib *p_dog,struct leadlib*p_lead);
extern void playdog(struct doglib *p_dog,struct leadlib*p_lead);
extern void traindog(struct doglib *p_dog,struct leadlib*p_lead);
extern void actdog(struct doglib *p_dog,struct leadlib*p_lead);
extern void dogaction(struct doglib *p_dog,struct leadlib*p_lead);
extern int shuijishu();
#if defined(__cplusplus)
}
#endif 

#endif  /* _INC_doglib */