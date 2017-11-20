#ifndef _INC_lizhi3_test1
#define _INC_lizhi3_test1
#include <iostream>
//using namespace std;

struct _D
{
	int ddd;
	int mmm;
	int vvv;
	void funwer(){};

};
extern void _D_init(struct _D * p);
extern void _D_destroy(struct _D * p);
extern void _D_func1();
extern void _D_func2(struct _D * p);


class D
{
protected:
	int ddd;
	int mmm;
	int vvv;
public:
	D()
		: ddd(0)
		, mmm(0)
		, vvv(0)
	{
		printf("D\n");
	}

	~D()
	{
		printf("~D\n");
	}

public:
	static void func1()
	{
		printf("D.func1\n");
	}
	void func2()
	{
		printf("D.func2 %d\n",this->ddd);
	}
public:
	void fun_tete()
	{
		printf("D.fun_tete\n");
	};

};

class B:public D
{
public:
	B(){std::cout<<"B()"<<std::endl;}

	~B(){std::cout<<"~B()"<<std::endl;}

public:
	void fun_tete(){std::cout<<"B.fun_tete"<<std::endl;};
protected:
	int b;

};

class A:virtual public B
{
public:
	A(){std::cout<<"A()"<<std::endl;}
	~A(){std::cout<<"~A()"<<std::endl;}
public:
	void fun_tete(){std::cout<<"A.fun_tete"<<std::endl;};
protected:
	int a;
};

class C:public A
{
public:
	C(){std::cout<<"C()"<<std::endl;}
	~C(){std::cout<<"~C()"<<std::endl;}
public:
	void fun_tete(){std::cout<<"A.fun_tete"<<std::endl;};
protected:
	int c;
};

extern void lizhi3_test1();
 


#endif  /* _INC_lizhi3_test1 */