#include "xuhanshu.h"


static void lizhi_3_xuhanshu_fun1(lizhi3_cl_A & aaaa);
static void lizhi_3_xuhanshu_fun2(lizhi3_lc_B & bbbb);
static void lizhi_3_xuhanshu_fun3(lizhi3_cl_A * aaaa);
static void lizhi_3_xuhanshu_fun4(lizhi3_lc_B * bbbb);
static void lizhi_3_xuhanshu_fun5(lizhi3_cl_A aaaa);

void lizhi_3_xuhanshu()
{
	lizhi3_cl_A a;
	lizhi3_lc_B b;

	printf("this is the lizhi_3_xuhanshu::a.printf_lizhi3() \n");
	a.printf_lizhi3_ppppp();
	printf("this is the lizhi_3_xuhanshu::b.printf_lizhi3() \n");
	b.printf_lizhi3_ppppp();

	lizhi_3_xuhanshu_fun1(a);
	lizhi_3_xuhanshu_fun1(b);
	lizhi_3_xuhanshu_fun3(&a);
	lizhi_3_xuhanshu_fun3(&b);
	//lizhi_3_xuhanshu_fun4(&a);
	//lizhi_3_xuhanshu_fun4(&b);
}

static void lizhi_3_xuhanshu_fun1(lizhi3_cl_A & aaaa)
{
	printf("this is the lizhi_3_xuhanshu_fun1::aaaa.printf_lizhi3() \n");
	aaaa.printf_lizhi3_ppppp();
}

static void lizhi_3_xuhanshu_fun2(lizhi3_lc_B & bbbb)
{
	printf("this is the lizhi_3_xuhanshu_fun2::bbbb.printf_lizhi3() \n");
	bbbb.printf_lizhi3_ppppp();
}

static void lizhi_3_xuhanshu_fun3(lizhi3_cl_A * aaaa)
{
	printf("this is the lizhi_3_xuhanshu_fun3::aaaa->printf_lizhi3() \n");
	aaaa->printf_lizhi3_ppppp();
}

static void lizhi_3_xuhanshu_fun4(lizhi3_lc_B * bbbb)
{
	printf("this is the lizhi_3_xuhanshu_fun4::bbbb->printf_lizhi3() \n");
	bbbb->printf_lizhi3_ppppp();
}

static void lizhi_3_xuhanshu_fun5(lizhi3_cl_A aaaa)
{
	printf("this is the lizhi_3_xuhanshu_fun5::bbbb->printf_lizhi3() \n");
	aaaa.printf_lizhi3_ppppp();
}

