#include "test3.h"
//#include <vector>
#include <iostream>
#include <string>
#include <stddef.h>
#include <vector>
#include <list>
#include <map>
#include <time.inl>

using namespace std;

void dog_name(g_dog *p)
{
	string cl,typ;
	int c0,t0;
	c0=rand()%4;
	t0=rand()%3;
	switch (c0)
	{
	case 0:cl="Red";break;
	case 1:cl="Blue";break;
	case 2:cl="Yellow";break;
	case 3:cl="Green";break;
	default:
		break;
	}
	switch (t0)
	{
	case 0:typ="_big";break;
	case 1:typ="_small";break;
	case 2:typ="_little";break;
	default:
		break;
	}
	p->type=typ;
	p->name=cl+typ;
	p->now_price=(float)(2*c0+18)*10+(3*t0+8)*10;
}


void lizhi3_test3()
{
	int i,chose,chosebuy,chosedog;
	g_dog dogall[6];

	//map<int,g_dog> dogalll;

	srand((unsigned)time(NULL));
	for (i=0;i<6;i++)
	{	
		dog_name(&dogall[i]);
	}

	businessman_dog busiman;
	for (i=0;i<6;i++)
	{	
		busiman.dog_name[i]=dogall[i].name;
		busiman.dog_price[i]=dogall[i].now_price;
	}

	storehouse_dog house;
	for (i=0;i<3;i++)
	{	
		house.dog_name[i]="Nodog";
	}

	ower_dog user1;
	cout<<"请输入你的英文名字"<<endl;
	cin>>user1.name;

	do 
	{
		user1.lookmyself();
		house.lookstore();
		do 
		{
			cout<<"你想做毛啊？1：逛商场\t2：遛狗\t3：喂狗\t4：偷鸡摸狗\t5：训练\t6：表演\t  退出请输入88"<<endl;
			cin>>chose;
			//system("cls");
		} while (chose!=88&&chose!=1&&chose!=2&&chose!=3&&chose!=4&&chose!=5&&chose!=6);
		if (chose==88)
		{
			break;
		}
		if (chose==1)
		{
			do 
			{
				cout<<"你想做毛啊？1：买狗\t2：卖狗\t3：买食物\t4：什么都不做"<<endl;
				cin>>chosebuy;
				//system("cls");
			} while (chosebuy!=1&&chosebuy!=2&&chosebuy!=3&&chosebuy!=4);
			if (chosebuy==1)
			{
				house.buydog(&busiman);
			}
			if (chosebuy==2)
			{
				house.selldog();
			}
			if (chosebuy==3)
			{
				house.buyfood(&busiman);
			}
		}

		if (chose==2)
		{
			do 
			{
				cout<<"你想玩那只狗？   \t狗1:"<<house.dog_name[0]<<"\n";
				cout<<"                \t狗2:"<<house.dog_name[1]<<"\n";
				cout<<"                \t狗3:"<<house.dog_name[2]<<"\n";
				cout<<"什么都不做请输入 4 "<<endl;
				cin>>chosedog;
				//system("cls");
			} while (chosedog!=1&&chosedog!=2&&chosedog!=3&&chosedog!=4);
			if (house.dog_name[chosedog-1]=="Nodog")
			{
				cout<<"你没有这条狗 "<<endl;
			}
		}









	} while (1);

}

