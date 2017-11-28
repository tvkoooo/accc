#ifndef _INC_lizhi3_test3
#define _INC_lizhi3_test3
#include <iostream>
#include <string>
#include <ctime>
#include <process.h>
#include <windows.h>
using namespace std;



#if 1
class g_dog
{
public:
	string name;
	string colour;
	string type;
	float  now_price;
	//float  buy_price;
	//float  sell_price;
	float  income;
	int    train;
	int    qinmi;
	int    hungry;
	int    time_play;
	int    time_food;
	int    time_action;
	int    time_train;
public:
	g_dog()
		:name("Red_big")
		,colour("red")
		,income(0)
		,train(0)
		,qinmi(10)
		,hungry(40)
		,time_play(0)
		,time_food(0)
		,time_action(0)
		,time_train(0)
	{
	}
	~g_dog(){}
public:
	  void fooddog()
	{
		this->qinmi+=2;
		this->hungry+=10;
		this->time_food++;
		cout<<"开始喂狗，亲密+2，饥饿+10"<<endl;
	}
	  void changenamedog()
	{
		this->qinmi-=20;
		getline(cin,this->name);
		cout<<"狗的名字是：\t"<<this->name<<"\t亲密降低20\t"<<endl;				
	}
	  void touch_dog()
	{
		this->qinmi+=1;
		this->hungry-=1;

		cout<<"摸一摸，小狗变摩托,亲密+1，饥饿-1"<<endl;
	}
	  void playdog()
	{
		srand((unsigned)time(0));
		int shuiji;
		this->time_play++;
		shuiji=rand()%7;
		if (shuiji==1||shuiji==6)			
			this->fooddog();
		if (shuiji==3||shuiji==4||shuiji==5||shuiji==0)
			this->touch_dog();
		if (shuiji==2)
		{
			this->qinmi+=5;
			this->hungry+=5;
			cout<<"大爆发，亲密和饥饿+5"<<endl;
		}			
	}
	  void traindog()
	{
		this->qinmi-=3;
		this->hungry-=3;
		this->time_train++;
		this->train+=5;
		cout<<"训练：亲密和饥饿-3，训练次数+1，训练度+5"<<endl;

	}
	  void actiondog()
	{
		this->traindog();
		this->qinmi-=2;
		this->time_action++;
		this->income+=30;
		cout<<"表演：亲密-5，饥饿-3，表演次数+1，收入+30"<<endl;

	}
};
#endif   ////class  of  dog



#if 1
class businessman_dog
{
public:
	int dog_foodA;
	int dog_foodB;
	int dog_foodC;
	float dog_price[6];
	string dog_name[6];
public:
	businessman_dog()
	:dog_foodA(40)
	,dog_foodB(60)
	,dog_foodC(70)
	{

	}
	//~businessman_dog(){};
public:
	  void lookbusines()
	{
		system("cls");
		cout<<"\t狗 粮 A:"<<this->dog_foodA<<"袋\t"<<endl;
		cout<<"\t狗 粮 B:"<<this->dog_foodB<<"袋\t"<<endl;
		cout<<"\t狗 粮 C:"<<this->dog_foodC<<"袋\t"<<endl;
		cout<<"\t狗    1:"<<this->dog_name[0]<<"\t"<<endl;
		cout<<"\t狗    2:"<<this->dog_name[1]<<"\t"<<endl;
		cout<<"\t狗    3:"<<this->dog_name[2]<<"\t"<<endl;
		cout<<"\t狗    4:"<<this->dog_name[3]<<"\t"<<endl;
		cout<<"\t狗    5:"<<this->dog_name[4]<<"\t"<<endl;
		cout<<"\t狗    6:"<<this->dog_name[5]<<"\t"<<endl;
	}

};
#endif   ////class  of  business

#if 1
class storehouse_dog
{
public:
	int dog_foodA;
	int dog_foodB;
	int dog_foodC;
	string dog_name[3];
	float  gold;

public:
	storehouse_dog()
	:dog_foodA(0)
	,dog_foodB(0)
	,dog_foodC(0)
	,gold(10000)
	{

	}
	//~storehouse_dog();
public:
	  void lookstore()
	{
		//system("cls");
		cout<<"\t狗 粮 A:"<<this->dog_foodA<<"袋\t"<<endl;
		cout<<"\t狗 粮 B:"<<this->dog_foodB<<"袋\t"<<endl;
		cout<<"\t狗 粮 C:"<<this->dog_foodC<<"袋\t"<<endl;
		cout<<"\t狗    1:"<<this->dog_name[0]<<"\t"<<endl;
		cout<<"\t狗    2:"<<this->dog_name[1]<<"\t"<<endl;
		cout<<"\t狗    3:"<<this->dog_name[2]<<"\t"<<endl;
		cout<<"\t总 金 币:"<<this->gold<<"\t"<<endl;

	}
	  void buydog(class businessman_dog *p)
	{
		int i,j,flagbuy=0;
		int cho;
		string name_cm="Nodog";
		for (i=0;i<3;i++)
		{
			if (this->dog_name[i]==name_cm)
			{
				for (j=0;j<6;j++)
				{
					cout<<"\n狗"<<j+1<<"的名字:"<<p->dog_name[j];
				}
				cout<<endl;
				cout<<"请选择买狗的编码数字，不买请输入88"<<endl;
				cin>>cho;
				if (cho==1||cho==2||cho==3||cho==4||cho==5||cho==6||cho==88)
				{		
					if (cho==88)
					{
						break;
					}
					this->dog_name[i]=p->dog_name[cho-1];
				}

				this->gold-=p->dog_price[cho-1];
				p->dog_name[cho-1]="Nodog";
				break;
			}
			flagbuy++;
		}
		if (flagbuy==3)
		{
			cout<<"你的狗栏满了，需要足够的狗栏"<<endl;
			Sleep(2000);
		}
	}
	  void selldog()
	{
		int cho=0;
		int i,flagsell=0;
		int flag_st=0;
		string name_new,name_cm="Nodog";
		for (i=0;i<3;i++)
		{
			if (this->dog_name[i]!=name_cm)
			{
				break;
				//this->dog_name[i]="Nodog";
				//this->gold+=200;
			}
			flagsell++;
		}
		if (flagsell==3)
		{
			cout<<"你的狗卖光了"<<endl;
			Sleep(2000);
			return;
		}

		for (i=0;i<3;i++)
		{
			cout<<"\n狗"<<i+1<<"的名字:"<<this->dog_name[i];
		}
		cout<<endl;
		cout<<"请选择卖狗的编码数字"<<endl;
		cin>>cho;
			if (this->dog_name[cho-1]==name_cm)
			{
				cout<<"你这只狗早死了"<<endl;
				Sleep(2000);
				return;
			}
		if (cho==1||cho==2||cho==3)
		{		
			this->dog_name[cho-1]="Nodog";
			this->gold+=200;
		}
		else
		{
			cout<<"你不想卖狗早说吗！"<<endl;
		}
	}
	  void buyfood(class businessman_dog *p)
	{
		if (p->dog_foodA)
		{			
			this->dog_foodA++;
			p->dog_foodA--;
			this->gold-=20;
		}
		if (p->dog_foodB)
		{			
			this->dog_foodB++;
			p->dog_foodB--;
			this->gold-=15;
		}
		if (p->dog_foodC)
		{			
			this->dog_foodC++;
			p->dog_foodC--;
			this->gold-=10;
		}

	}
};
#endif   ////class  of  store


#if 1
	class ower_dog
	{
	public:
		string name;
		int    buydog_all;
		int    selldog_all;
		int    nowdog_all;
		int    playdog_all;
		int    fooddog_all;
		int    traindog_all;
		int    actiondong_all;
		float  action_gold;
	public:
		ower_dog()
		:name("user")
		,buydog_all(0)
		,selldog_all(0)
		,nowdog_all(0)
		,playdog_all(0)
		,fooddog_all(0)
		,traindog_all(0)
		,actiondong_all(0)
		,action_gold(0)
		{
		}
		//~ower_dog(){}
	public:
		  void lookmyself()
		{
			system("cls");
			cout<<"\t主角名字:"<<this->name<<"\t"<<endl;
			cout<<"\t购买狗数:"<<this->buydog_all<<"\t"<<endl;
			cout<<"\t贩卖狗数:"<<this->selldog_all<<"\t"<<endl;
			cout<<"\t目前狗数:"<<this->nowdog_all<<"\t"<<endl;
			cout<<"\t玩狗次数:"<<this->playdog_all<<"\t"<<endl;
			cout<<"\t喂狗次数:"<<this->fooddog_all<<"\t"<<endl;
			cout<<"\t训练次数:"<<this->traindog_all<<"\t"<<endl;
			cout<<"\t表演次数:"<<this->actiondong_all<<"\t"<<endl;
		}
		  void fooddog()
		{
			this->fooddog_all++;
		}	

		  void playdog()
		{
			this->playdog_all++;
		}
		  void traindog()
		{
			this->traindog_all++;
		}
		  void actiondog(class storehouse_dog *p)
		{
			this->actiondong_all++;
			this->action_gold+=50;
			p->gold+=50;
		}
	};
#endif    ////class  of  ower










extern void lizhi3_test3();
 


#endif  /* _INC_lizhi3_test3 */