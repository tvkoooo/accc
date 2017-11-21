#ifndef _INC_lizhi3_test2
#define _INC_lizhi3_test2
#include <iostream>
#include <string>
using namespace std;


#if 0  
union MyUnion_test2
{
	char c[4];
	int i;
};
#endif   //union MyUnion_test2
#if 0
class printData 
{
public:
	void print(int i) {
		cout << "整数为: " << i << endl;
	}

	void print(double  f) {
		cout << "浮点数为: " << f << endl;
	}

	void print(string c) {
		cout << "字符串为: " << c << endl;
	}
};
#endif   // class printData    函数重载和运算重载
#if 0
class DD
{
//public:
//	DD(){std::cout<<"D()"<<std::endl;}
//
//	~DD(){std::cout<<"~D()"<<std::endl;}
public:
	//virtual void fun_tete1(){std::cout<<"D.fun_tete1"<<std::endl;};
	//virtual void fun_tete2(){std::cout<<"D.fun_tete2"<<std::endl;};
	int base1_1;
	int base1_2;
};

class Base2
{
public:
	int base2_1;
	int base2_2;

	virtual void base2_fun1() {}
	virtual void base2_fun2() {}
};
class Derive1 : public DD,public Base2
{
public:
	int derive1_1;
	int derive1_2;
	virtual void fun_tete1(){std::cout<<"D.fun_tete1"<<std::endl;};
	virtual void base2_fun2() {}
	virtual void wolegeca() {}
};
class BB:virtual public DD
{
public:
	BB(){std::cout<<"B()"<<std::endl;}

	~BB(){std::cout<<"~B()"<<std::endl;}

public:
	void fun_tete(){std::cout<<"B.fun_tete"<<std::endl;};
protected:
	int b;
};
class AA:virtual public DD
{
public:
	AA(){std::cout<<"A()"<<std::endl;}
	~AA(){std::cout<<"~A()"<<std::endl;}
public:
	void fun_tete(){std::cout<<"A.fun_tete"<<std::endl;};
protected:
	int a;
};
class CC:public AA,public BB
{
public:
	CC(){std::cout<<"C()"<<std::endl;}
	~CC(){std::cout<<"~C()"<<std::endl;}
public:
	void fun_tete(){std::cout<<"A.fun_tete"<<std::endl;};
protected:
	int c;
};
#endif  // // 类的结构,seizof(类),virtual 原理
#if 0
class Box
{
public:
	double getVolume(void)
	{
		return length * breadth * height;
	}
	void setLength( double len )
	{
		length = len;
	}
	void setBreadth( double bre )
	{
		breadth = bre;
	}
	void setHeight( double hei )
	{
		height = hei;
	}
	// 重载 + 运算符，用于把两个 Box 对象相加
	Box operator - (const Box& b)  ////备注：类似宏定义，Box operator + (const Box& b)，其中Box是class名，Box& b取地址，“ + ” 为运算符
		////////////////////////////////例如  这里“ + ”改为 “-”，使用时候“ + ”同样改为 “-”，效果是一样的,需要改成运算符。
	{
		Box box;
		box.length = this->length + b.length;
		box.breadth = this->breadth + b.breadth;
		box.height = this->height + b.height;
		return box;
	}
private:
	double length;      // 长度
	double breadth;     // 宽度
	double height;      // 高度
};
#endif   //   class Box   函数重载和运算重载
#if 0
class Shape
{
protected:
	int width, height;
public:
	virtual void Shape_in( int a=0, int b=0)
	{
		width = a;
		height = b;
	}
	virtual int area()
	{ 
		cout << "Rectangle class area :"<<endl;
		return (width * height); 
	}
};
class Rectangle:public Shape
{
public:
	//Rectangle( int a=0, int b=0):Shape(a, b) { }
	int area ()
	{ 
		cout << "Rectangle class area :" <<width * height<<endl;
		return (width * height); 
	}
};
class Triangle: public Shape
{
public:
	//Triangle( int a=0, int b=0):Shape(a, b) { }
	int area ()
	{ 
		cout << "Triangle class area :"<<(width * height / 2) <<endl;
		return (width * height / 2); 
	}
};

#endif   //   类的多态，virtual在多态的使用



extern void lizhi3_test2();
 


#endif  /* _INC_lizhi3_test2 */