#include <tchar.h>
#include <stdio.h>
#include <stdlib.h>
#include <exception>
#include <string.h>
#include <ctime>
#include <doglib.h>
#include <leadlib.h>

static struct dogtalk G_dogtalk =
{
	"",
	"",
	"",
	"",
	"",
	"",
	"",
	"",
};
void dogaction(struct doglib *p_dog,struct leadlib*p_lead)
{
		int i,action=0;
		action=p_dog->dog_friend+p_dog->dog_hunger;
		i=(action/25)%8;
		if (i>=0&&i<8)
		{
           printf("\n%s:::%s\n",p_dog->dog_name,G_dogtalk.dog_act[i]);
		} 
		
	}


void feeddog(struct doglib *p_dog,struct leadlib*p_lead)
{
	if(1==p_dog->dog_flag)	{

		p_dog->dog_feed++;
		p_dog->dog_friend=p_dog->dog_friend+2;
		p_dog->dog_hunger=p_dog->dog_hunger+20;	
		p_lead->lead_dog_play++;
		p_lead->lead_dog_feed++;
		dogaction(p_dog,p_lead);
	}
	else
	{
		;
	}	

}

void Strokedog(struct doglib *p_dog,struct leadlib*p_lead)
{
	if(1==p_dog->dog_flag)
	{

		p_dog->dog_friend=p_dog->dog_friend+1;
		p_dog->dog_hunger=p_dog->dog_hunger-2;	
		p_lead->lead_dog_play++;
		dogaction(p_dog,p_lead);
	}
	else
	{
		;
	}	

}

void changenamedog(struct doglib *p_dog,struct leadlib*p_lead)
{
	if(1==p_dog->dog_flag)
	{
		dogaction(p_dog,p_lead);
		printf("你要给 %s  取个什么名字给它？",p_dog->dog_name);
		scanf("%s",p_dog->dog_name);
		p_dog->dog_friend=p_dog->dog_friend-30;
		printf("%s  幽怨的看着你",p_dog->dog_name);
		dogaction(p_dog,p_lead);
	}
	else
	{
		;
	}	
	
}

void playdog(struct doglib *p_dog,struct leadlib*p_lead)
{
	int chufa;
	char xuan;
	chufa=shuijishu()%6;
	if(1==p_dog->dog_flag)
	{
		printf("你准备怎么玩它\nA：喂它\tB：摸它\t请选择A或者B\n？");
		scanf(" %c",&xuan);
		if (xuan=='A'||xuan=='a')
		{
			feeddog(p_dog,p_lead);
			if (chufa==3)
			{
				p_dog->dog_friend=p_dog->dog_friend+3;
				printf("JJ BOM ！你的小狗忠诚度  +3 啦\n");

			}
			if (chufa==1||chufa==4)
			{
				p_dog->dog_friend=p_dog->dog_friend+1;
				printf("JJ FLY ！你的小狗忠诚度加  +1 \n");

			}


		} 
		else
		{
			if (xuan=='B'||xuan=='b')
			{
				Strokedog(p_dog,p_lead);
				if (chufa==5)
				{
					p_dog->dog_friend=p_dog->dog_friend+3;
					printf("JJ BOM ！你的小狗忠诚度  +3 啦\n");

				}
				if (chufa==0||chufa==2)
				{
					p_dog->dog_friend=p_dog->dog_friend+1;
					printf("JJ FLY ！你的小狗忠诚度加  +1 \n");

				}


			} 
			else
			{
				printf("你的输入不对，已经放弃遛狗！！\n%s 讨厌你！！",p_dog->dog_name);
				p_dog->dog_friend=p_dog->dog_friend-3;
				printf("JJ DIE ！你的小狗忠诚度怒掉  3 \n");

			}
		}
	}
	else
	{
		;
	}	
}

void traindog(struct doglib *p_dog,struct leadlib*p_lead)
{
	double train;
	train=p_dog->dog_type*0.1;
	if(1==p_dog->dog_flag)		
	{

		p_dog->dog_friend=p_dog->dog_friend-2;
		p_dog->dog_hunger=p_dog->dog_hunger-5;
		p_dog->dog_train=p_dog->dog_train+train;
		p_lead->lead_dog_train++;
		dogaction(p_dog,p_lead);
	}
	else
	{
		;
	}	

}

void actdog(struct doglib *p_dog,struct leadlib*p_lead)
{
	double actmoney;
	int actnumber;
	double dog_trainer;
	//小狗累计表演超过50次，取最大50；
	if (p_dog->dog_actnumb>50)
	{
		actnumber=50;
	}
	else
	{
		actnumber=p_dog->dog_actnumb;
	}
	//小狗训练最大20次后，取最大20次；
	if (p_dog->dog_train>20)
	{
		dog_trainer=20;
	}
	else
	{
		dog_trainer=p_dog->dog_train;
	}



	actmoney=(actnumber*2+p_dog->dog_friend+p_dog->dog_hunger+dog_trainer*5)*p_dog->dog_type*0.1;
	if(1==p_dog->dog_flag)		
	{

		p_dog->dog_friend=p_dog->dog_friend-1;
		p_dog->dog_hunger=p_dog->dog_hunger-3;
		p_dog->dog_actnumb++;
		p_dog->dog_act_money=p_dog->dog_act_money+actmoney;
		p_lead->lead_dog_act++;
		p_lead->lead_dog_act_money=p_lead->lead_dog_act_money+actmoney;

		if(actmoney>300)
		{
		printf("\n路人：%s玩得很好，给82分，剩下666给它",p_dog->dog_name);
		}
		if (actmoney>150&&actmoney<300)
		{
		printf("\n路人：%s玩的一般般吧，就那样",p_dog->dog_name);
		} 
		else
		{
		printf("\n路人：%s我擦，回去再练练吧，影响心情",p_dog->dog_name);
		}

		dogaction(p_dog,p_lead);

	}
	else
	{
		;
	}	


}