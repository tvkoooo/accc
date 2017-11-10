#ifndef _INC_huidiao
#define _INC_huidiao

struct fun_huidiao_in
{
	int clocktime;
	void (*fun_hui) (void); 
};

extern void editprinthuidiao();
extern int shuijishu();
extern void huidiao_clock_in(struct fun_huidiao_in clock_a);
extern void huidiao_a();

#endif  /* _INC_huidiao */