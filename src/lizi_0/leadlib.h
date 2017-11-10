#ifndef _INC_leadlib
#define _INC_leadlib


struct leadlib
{

	char lead_name[20];
	float  lead_money;
///////////////////////////////
	int  lead_dog_buy;
	int  lead_dog_sell;
	int  lead_dog_numb;
	int  lead_dog_play;
	int  lead_dog_feed;
	int  lead_dog_train;
	int  lead_dog_act;
	double  lead_dog_act_money;

};
extern void createdog(struct doglib *p_dog,struct leadlib*p_lead);
extern void diedog(struct doglib *p_dog,struct leadlib*p_lead);

extern void buydog(struct doglib *p_dog,struct leadlib*p_lead);
extern void selldog(struct doglib *p_dog,struct leadlib*p_lead);
extern void buyfooddog(struct doglib *p_dog,struct leadlib*p_lead);


extern void datedog(struct doglib *p_dog,struct leadlib*p_lead);

extern void checkdog(struct doglib *p_dog,struct leadlib*p_lead);
extern void checkmyself(struct doglib *p_dog,struct leadlib*p_lead);
extern void checkmystore(struct doglib *p_dog,struct leadlib*p_lead);

#endif  /* _INC_leadlib */