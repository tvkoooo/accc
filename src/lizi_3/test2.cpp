#include "test2.h"
//#include <vector>
#include <iostream>
#include <string>
#include <stddef.h>
#include <vector>
#include <list>
#include <map>
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

#if 0
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

#endif    /////vector 相关命令

#if 0
	vector<int> iVec;
	int c,i;
	cout << "容器 大小为: " << iVec.size() << endl;
	cout << "容器 容量为: " << iVec.capacity() << endl; //0个元素， 容器容量为0
	iVec.push_back(1);
	cout << "容器 大小为: " << iVec.size() << endl;
	cout << "容器 容量为: " << iVec.capacity() << endl; //1个元素， 容器容量为1
	iVec.push_back(2);
	cout << "容器 大小为: " << iVec.size() << endl;
	cout << "容器 容量为: " << iVec.capacity() << endl; //2个元素， 容器容量为2
	iVec.push_back(3);
	cout << "容器 大小为: " << iVec.size() << endl;
	cout << "容器 容量为: " << iVec.capacity() << endl; //3个元素， 容器容量为3
	iVec.push_back(4);
	iVec.push_back(5);
	cout << "容器 大小为: " << iVec.size() << endl;
	cout << "容器 容量为: " << iVec.capacity() << endl; //5个元素， 容器容量为6
	iVec.push_back(6);
	cout << "容器 大小为: " << iVec.size() << endl;
	cout << "容器 容量为: " << iVec.capacity() << endl; //6个元素， 容器容量为6
	iVec.push_back(7);
	cout << "容器 大小为: " << iVec.size() << endl;
	cout << "容器 容量为: " << iVec.capacity() << endl; //7个元素， 容器容量为9
	iVec.push_back(8);
	cout << "容器 大小为: " << iVec.size() << endl;
	cout << "容器 容量为: " << iVec.capacity() << endl; //8个元素， 容器容量为9
	iVec.push_back(9);
	cout << "容器 大小为: " << iVec.size() << endl;
	cout << "容器 容量为: " << iVec.capacity() << endl; //9个元素， 容器容量为9
	/* vs2005/8 容量增长不是翻倍的，如 
	    9个元素   容量9 
		    10个元素 容量13 */
	/* 测试effective stl中的特殊的交换 swap() */
	cout << "当前vector 的大小为: " << iVec.size() << endl;
	cout << "当前vector 的容量为: " << iVec.capacity() << endl;    //9个元素， 容器容量为9
	vector<int>(iVec).swap(iVec);
	cout << "临时的vector<int>对象 的大小为: " << (vector<int>(iVec)).size() << endl;
	cout << "临时的vector<int>对象 的容量为: " << (vector<int>(iVec)).capacity() << endl;  //9个元素， 容器容量为9
	cout << "交换后，当前vector 的大小为: " << iVec.size() << endl;
	cout << "交换后，当前vector 的容量为: " << iVec.capacity() << endl;  //9个元素， 容器容量为9

	for (i=0;i<iVec.capacity();i++)
	{
		cout << iVec[i]<<"\t";
	}
	cout <<endl;

	iVec.clear();
	for (i=0;i<iVec.capacity();i++)
	{
		cout << iVec[i]<<"\t";
	}
	cout <<endl;

	c=iVec.empty();
	cout << c<< endl;
#endif    //vector  元素和尺寸

#if 0
	AAC m1;
	cout<<m1.i<<"\t"<<m1.j<<"\t"<<endl;

	m1.i=5;
	m1.j=6;
	cout<<m1.i<<"\t"<<m1.j<<"\t"<<endl;

	AAC m2(m1);
	cout<<m2.i<<"\t"<<m2.j<<"\t"<<endl;

	AAC m3;
	m3=m1;
	cout<<m2.i<<"\t"<<m2.j<<"\t"<<endl;

	m3=m1+m2;

#endif    // 构造函数，拷贝构造函数，赋值构造函数

#if 0
	// list map vector
	// 基础数据结构，（了解实现方式）
	//遍历
	//用list<int> 创建一个int 名为list_lj_1的list对象
	list <int> list_lj_1;

	//声明mcc为迭代器  
	list <int>::iterator mcc;
	list_lj_1.push_back(1);
	list_lj_1.push_back(2);
	list_lj_1.push_back(3);
	list_lj_1.push_front(4);
	list_lj_1.push_front(5);
	for (mcc = list_lj_1.begin();mcc != list_lj_1.end();mcc ++)
	{

		cout<< *mcc <<"\t";
	}
	    cout<<endl;
///////////////////////////////////////////////////////////////////
		for (mcc = list_lj_1.begin();mcc != list_lj_1.end();)
		{
			if (*mcc==2)
			{
				mcc=list_lj_1.erase(mcc++);
			} 
			else
			{
				mcc++;
			}
		}
		//copy(list_lj_1.begin(),list_lj_1.end(),ostream_iterator<int>(cout," "));
////////////////////////////////////////////////////////////////////
	for (mcc = list_lj_1.begin();mcc != list_lj_1.end();mcc ++)
	{

		cout<< *mcc <<"\t";
	}
	cout<<endl;

#endif    //list 删除和遍历

#if 0
	// list map vector
	// 基础数据结构，（了解实现方式）
	//遍历
	//用list<int> 创建一个int 名为list_lj_1的list对象
	map <char,int> list_lj_1;

	//声明mcc为迭代器  
	map <char,int>::iterator mcc;
	list_lj_1['a']=11;
	list_lj_1['d']=22;
	list_lj_1['e']=33;
	list_lj_1['k']=44;
	list_lj_1['j']=55;

	for (mcc = list_lj_1.begin();mcc != list_lj_1.end();mcc ++)
	{

		cout<< mcc->first<<mcc->second <<"\t";
	}
	cout<<endl;
	///////////////////////////////////////////////////////////////////
	for (mcc = list_lj_1.begin();mcc != list_lj_1.end();)
	{
		if (mcc->second==33)
		{
			 list_lj_1.erase(mcc++); 

		} 
		else
		{
			mcc++;
		}
		std::max
	}
	//copy(list_lj_1.begin(),list_lj_1.end(),ostream_iterator<int>(cout," "));
	////////////////////////////////////////////////////////////////////
	for (mcc = list_lj_1.begin();mcc != list_lj_1.end();mcc ++)
	{

		cout<< mcc->first<<mcc->second <<"\t";
	}
	cout<<endl;

	list_lj_1 ['c']=99;

	for (mcc = list_lj_1.begin();mcc != list_lj_1.end();mcc ++)
	{

		cout<< mcc->first<<mcc->second <<"\t";
	}
	cout<<endl;

#endif   //map 删除和遍历

#if 0
	{
		int a,b,c;
		a=4;
		b=2;
		c=max_11(a,b);
		cout<<a<<b<<c<<endl;
	}
	{
		float a,b,c;
		a=3;
		b=9;
		c=max_11(a,b);
		cout<<a<<b<<c<<endl;
	}
#endif   //泛型函数使用,C++模板

#if 0
	vector<int> vtoto(4,3);
	cout<<"\n max_size\n"<<vtoto.max_size()<<"\n size\n"<<vtoto.size()<<"\n"<<endl;
	cout<<vtoto.at(3)<<endl;
	vector <int>::iterator mcc;
	vtoto.push_back(11);
	vtoto.push_back(22);
	vtoto.push_back(33);
	vtoto.push_back(44);
	vtoto.push_back(55);
	vtoto.push_back(66);

	for (mcc = vtoto.begin();mcc != vtoto.end();mcc ++)
	{

		cout<< *mcc <<"\t";
	}
	cout<<endl;
	///////////////////////////////////////////////////////////////////
	for (mcc = vtoto.begin();mcc != vtoto.end();)
	{
		if (*mcc==44)
		{
			mcc=vtoto.erase(mcc++);
		} 
		else
		{
			mcc++;
		}
	}
	//copy(list_lj_1.begin(),list_lj_1.end(),ostream_iterator<int>(cout," "));
	////////////////////////////////////////////////////////////////////
	for (mcc = vtoto.begin();mcc != vtoto.end();mcc ++)
	{

		cout<< *mcc <<"\t";
	}
	cout<<endl;



#endif   //vector 删除和遍历







}
