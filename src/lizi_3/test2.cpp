#include "test2.h"
//#include <vector>
#include <iostream>
#include <string>
#include <stddef.h>
#include <vector>
using namespace std;



void lizhi3_test2()
{
#if 0
	cout << "Hello World!" << endl;
	Derive1 woka,wokb;
	int *a,*b;
	a=&woka.base1_1;
	b=&woka.base1_2;
	cout<<sizeof(Derive1)<<endl;
	cout<<sizeof(Derive1)<<endl;
	cout<<offsetof(Derive1, base1_1)<<endl;
	cout<<offsetof(Derive1, base1_2)<<endl;
	printf("\n sizeof(D) %d\n",sizeof(D));
	printf("\n sizeof(D) %d\n",sizeof(akc));
#endif   // 类的结构,seizof(类),virtual 原理
#if 0
	{
		string st("fdds");
		string st1="fdds";

		int abc;
		cout<<"the size of "<< st1<<" is "<<st.size()<<endl;
	}
#endif  //  字符串的使用
#if 0
	printData pd;

	// 输出整数
	pd.print(5);
	// 输出浮点数
	pd.print(500.263);
	// 输出字符串
	pd.print("Hello C++");

#endif  // class printData    函数重载和运算重载
#if 0
	Box Box1;                // 声明 Box1，类型为 Box
	Box Box2;                // 声明 Box2，类型为 Box
	Box Box3;                // 声明 Box3，类型为 Box
	double volume = 0.0;     // 把体积存储在该变量中
	// Box1 详述
	Box1.setLength(6.0); 
	Box1.setBreadth(7.0); 
	Box1.setHeight(5.0);
	// Box2 详述
	Box2.setLength(12.0); 
	Box2.setBreadth(13.0); 
	Box2.setHeight(10.0);
	// Box1 的体积
	volume = Box1.getVolume();
	cout << "Volume of Box1 : " << volume <<endl;
	// Box2 的体积
	volume = Box2.getVolume();
	cout << "Volume of Box2 : " << volume <<endl;
	// 把两个对象相加，得到 Box3
	Box3 = Box1 - Box2;
	// Box3 的体积
	volume = Box3.getVolume();
	cout << "Volume of Box3 : " << volume <<endl;
#endif   //   class Box    函数重载和运算重载
#if 0
	Shape a1;
	Rectangle rec;
	Triangle  tri;
	a1.Shape_in(10,7);	
	a1.area();	
	rec.Shape_in(10,7);	
	rec.area();	
	tri.Shape_in(10,7);	
	tri.area();	

#endif  //类的多态，virtual在多态的使用

#if 1
	int leng_vet=9;
	vector<int> vi(13);
	int i;
		cout<<"容器数据：\t";
	for (i=0;i<leng_vet;i++)
	{
		vi[i]=((i+1)*2);

		cout<<vi[i]<<"\t";
	}
	cout<<endl;
	cout<<"\n max_size\n"<<vi.max_size()<<"\n size\n"<<vi.size()<<"\n at(4)\n"<<vi.at(4)<<endl;
	for (i=0;i<vi.size();i++)
		cout<<vi[i]<<"\t";
	cout<<endl;

	vi.push_back(998);
	cout<<"\n max_size\n"<<vi.max_size()<<"\n size\n"<<vi.size()<<"\n at(4)\n"<<vi.at(4)<<endl;
	for (i=0;i<vi.size();i++)
		cout<<vi[i]<<"\t";
	cout<<endl;

#endif
	




}
