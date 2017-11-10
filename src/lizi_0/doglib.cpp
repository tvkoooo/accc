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
		printf("��Ҫ�� %s  ȡ��ʲô���ָ�����",p_dog->dog_name);
		scanf("%s",p_dog->dog_name);
		p_dog->dog_friend=p_dog->dog_friend-30;
		printf("%s  ��Թ�Ŀ�����",p_dog->dog_name);
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
		printf("��׼����ô����\nA��ι��\tB������\t��ѡ��A����B\n��");
		scanf(" %c",&xuan);
		if (xuan=='A'||xuan=='a')
		{
			feeddog(p_dog,p_lead);
			if (chufa==3)
			{
				p_dog->dog_friend=p_dog->dog_friend+3;
				printf("JJ BOM �����С���ҳ϶�  +3 ��\n");

			}
			if (chufa==1||chufa==4)
			{
				p_dog->dog_friend=p_dog->dog_friend+1;
				printf("JJ FLY �����С���ҳ϶ȼ�  +1 \n");

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
					printf("JJ BOM �����С���ҳ϶�  +3 ��\n");

				}
				if (chufa==0||chufa==2)
				{
					p_dog->dog_friend=p_dog->dog_friend+1;
					printf("JJ FLY �����С���ҳ϶ȼ�  +1 \n");

				}


			} 
			else
			{
				printf("������벻�ԣ��Ѿ������޹�����\n%s �����㣡��",p_dog->dog_name);
				p_dog->dog_friend=p_dog->dog_friend-3;
				printf("JJ DIE �����С���ҳ϶�ŭ��  3 \n");

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
	//С���ۼƱ��ݳ���50�Σ�ȡ���50��
	if (p_dog->dog_actnumb>50)
	{
		actnumber=50;
	}
	else
	{
		actnumber=p_dog->dog_actnumb;
	}
	//С��ѵ�����20�κ�ȡ���20�Σ�
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
		printf("\n·�ˣ�%s��úܺã���82�֣�ʣ��666����",p_dog->dog_name);
		}
		if (actmoney>150&&actmoney<300)
		{
		printf("\n·�ˣ�%s���һ���ɣ�������",p_dog->dog_name);
		} 
		else
		{
		printf("\n·�ˣ�%s�Ҳ�����ȥ�������ɣ�Ӱ������",p_dog->dog_name);
		}

		dogaction(p_dog,p_lead);

	}
	else
	{
		;
	}	


}