#ifndef _INC_lizhi3_xuhanshu
#define _INC_lizhi3_xuhanshu

#include <stdio.h>

class lizhi3_base
{
public:
	virtual void printf_lizhi3_ppppp(){printf("this is the lizhi3_base::a.printf_lizhi3_ppppp() \n");};
};


class lizhi3_cl_A: public lizhi3_base
{
public:
	void printf_lizhi3_ppppp(){printf("this is the lizhi3_cl_A::a.printf_lizhi3_ppppp() \n");};
};

class lizhi3_lc_B: public lizhi3_base
{
public:
	void printf_lizhi3_ppppp(){printf("this is the lizhi3_lc_B::a.printf_lizhi3_ppppp() \n");};
};

 


#endif  /* _INC_lizhi3_xuhanshu */