#include "test1.h"
#include <iostream>


using namespace std;


void lizhi3_test1()
{
	{
		struct _D tea;
		_D_init(& tea);

		_D_func1();
		_D_func2(& tea);

		_D_destroy(& tea);
	}
	{
		D ddewfd;

		D::func1();
		ddewfd.func2();
	}
	//{

	//	B ofjwoe;
	//}


}

void _D_init(struct _D * p)
{
	p->ddd=0;
	p->mmm=0;
	p->vvv=0;
	printf("_D_init\n");
}
void _D_destroy(struct _D * p)
{
	p->ddd=0;
	p->mmm=0;
	p->vvv=0;
	printf("_D_destroy\n");
}
void _D_func1()
{
	printf("_D_func1\n");
}
void _D_func2(struct _D * p)
{
	printf("_D_func2 %d\n",p->ddd);
}