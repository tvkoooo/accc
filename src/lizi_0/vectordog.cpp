#include <stdio.h>
#include <stdlib.h>
#include "vectordog.h"
#include <string.h>

#define Lenen sizeof(struct fun__vectordog_new)

void kongkongkong(struct fun__vectordog_nam* pname)
{

}
//tool init  �������������ڴ�
struct fun__vectordog_new* fun__vectordog_new_alloc()
{
	fun__vectordog_new* head;
	head=(fun__vectordog_new*) malloc(Lenen);
	return(head);
}

//tool init  �������������ڴ�
void fun__vectordog_new_dealloc(struct fun__vectordog_new* head)
{
	free(head);
}


//tool init  �����ʼ��
void fun__vectordog_new_init(struct fun__vectordog_new* head)
{

	head->fun__vectordog_talk=kongkongkong;
	head->pnext=NULL;
}
//tool init  С�����ݳ�ʼ��
void fun__vectordog_new_init(struct fun__vectordog_nam *p_dog)
{
	strcpy(p_dog->dog_name,"0");
	strcpy((p_dog+1)->dog_name,"0");
	strcpy((p_dog+2)->dog_name,"0");

}


//tool init  С�����ݸ�ֵ
void fun__vectordog_new_dogname(struct fun__vectordog_nam *p_dog)
{	
	int i;
	for (i=0;i<3;i++)
	{	
	printf("С�� %d :�������ǣ�\n",i);
	scanf(" %[^\n]",(p_dog+i)->dog_name);
	}


}

//void fun__vectordog_talk_a(struct fun__vectordog_nam *p_dog)
//{
//
//}


//tool init  ����������ӣ����ģ�
void fun__vectordog_new_add(struct fun__vectordog_new* head,fun__vectordog fdog)
{	
		fun__vectordog_new *find_p,*p1,*p2;

		p2=(fun__vectordog_new*) malloc(Lenen);
		p1=head;
		while (p1->pnext!=NULL)
		{
			find_p=p1;
			p1=p1->pnext;

		}
		p1->pnext=p2;

		//printf("С���������ǣ�\n");
		//scanf(" %[^\n]",pname->dog_name);
		p2->fun__vectordog_talk=fdog;
		p2->pnext=NULL;
}

//tool init  ����������٣����ģ�
void fun__vectordog_new_sub(struct fun__vectordog_new* head)
{
	fun__vectordog_new *find_p,*p1;
	find_p=p1=head;
	if (p1->pnext==NULL)
	{
		printf("�����Ѿ�ɾ��,��770������666������ݣ�777ɾ���ϸ����ݣ�888�鿴�������ݣ�999�鿴������С���˳�520250��\n");
	}
	else
	{
		while (p1->pnext!=NULL)
		{
			find_p=p1;
			p1=p1->pnext;
		}
		p1->fun__vectordog_talk=kongkongkong;
		free(p1);
		p1=NULL;
		//strcpy(pname->dog_name,"0");
		find_p->pnext=NULL;
	}

}


////tool init  tool����ˢ��
//void fun__vectordog_call(struct fun__vectordog_nam* pname)
//{
//		printf("С����%s\n",pname->dog_name);
//
//}

//tool init  ˢ������������
void fun__vectordog_new_update(struct fun__vectordog_new* ptalk,struct fun__vectordog_nam* pname)
{
	fun__vectordog_new *find_p,*p1;
	int a=0;
	p1=ptalk;

	while(p1->pnext!=NULL) 
	{

		find_p=p1;
		p1=p1->pnext;
		p1->fun__vectordog_talk(pname+a%3);
		a++;
	}
}


//tool init  �����ߣ�ͳ�ƶ��ĸ���
int fun__vectordog_new_seizof(struct fun__vectordog_new*head)
{
	fun__vectordog_new *find_p,*p1;
	int n=0;
	find_p=p1=NULL;
	p1=head;
	while(p1->pnext!=NULL)
	{
		find_p=p1;
		p1=p1->pnext;
		n++;
	};
	return(n);
}

//tool destroy �������㣨������ģ�
void fun__vectordog_new_clear(struct fun__vectordog_new* ptalk)
{
	fun__vectordog_new *find_p,*p1;
	find_p=p1=ptalk;

	while (ptalk->pnext!=NULL)
	{	
		find_p=ptalk;
		p1=ptalk->pnext;
		while(p1->pnext!=NULL) 
		{
	
			find_p=p1;
			p1=p1->pnext;
		};

		free(p1);
		p1=NULL;
		find_p->pnext=NULL;
	}

}

//tool destroy С����������
void fun__vectordog_nam_clear(struct fun__vectordog_nam* pname)
{

	strcpy(pname->dog_name,"0");
	strcpy((pname+1)->dog_name,"0");
	strcpy((pname+2)->dog_name,"0");

}



//tool destroy �������
void fun__vectordog_new_destroy(struct fun__vectordog_new* ptalk)
{
	fun__vectordog_new_clear(ptalk);
	ptalk->fun__vectordog_talk=kongkongkong;
	ptalk->pnext=NULL;
}

//tool destroy �����¼��ص�
void fun__vectordog_talk_a(struct fun__vectordog_nam* pname)
{
	int chos;
	chos=shuijishu()%3;
	printf("\nС��  %s:    ",pname);
	switch (chos)
	{
	case 0: printf("����ʳ��\n");break;
	case 1: printf("�Ҷ���\n");break;
	case 2: printf("������ʳ��С����ҧ��!\n");break;
	default:
		break;
	}
}

void fun__vectordog_new_test()
{
	//���������
	int num=0;
	//������С
	int vecnum;
	vecnum=0;

	fun__vectordog_new* creat_vec;

	fun__vectordog_nam dog[3];
	//��������
	creat_vec=fun__vectordog_new_alloc();

	//�������ݳ�ʼ��
	fun__vectordog_new_init(creat_vec);

	fun__vectordog_new_init(dog);
	//С�����ݳ�ʼ��
	while(num!=555)
	{

		printf("333��С��ȡ������222,����С��������770������666������ݣ�777ɾ���ϸ����ݣ�888�鿴�������ݣ�999�鿴������С���˳�555��\n");
		scanf(" %d",&num);
		if ((num==333))
		{
	//С�����ݸ�ֵ
			fun__vectordog_new_dogname(dog);
		}
		if ((num==222))
		{
				//�������ӣ����Ӷ���
			fun__vectordog_new_add(creat_vec,fun__vectordog_talk_a);

		}

		if (num==770)
		{
			//����
			system("CLS");
		}
		if (num==555)
		{
			//�˳�
			break;
		}
		if (num==777)
		{
			//��������ȥ��һ�� ����
			fun__vectordog_new_sub(creat_vec);
		}
		if (num==888)
		{
			//������������
			fun__vectordog_new_update(creat_vec,dog);
		}
		if (num==999)
		{
			//�鿴����
			vecnum=fun__vectordog_new_seizof(creat_vec);
			printf("�鿴������С������%d�����ݣ��ڴ�����С�ǣ�%d\n",vecnum,vecnum*Lenen);
		}
		if (num==666)
		{
			//�������ȥ������
			fun__vectordog_new_clear(creat_vec);
			fun__vectordog_nam_clear(dog);

		}

	}
			//�������ȥ������
			fun__vectordog_new_clear(creat_vec);
			//���С������
			fun__vectordog_nam_clear(dog);
	//�����ʼ������
	fun__vectordog_new_destroy(creat_vec);
	//��������
	fun__vectordog_new_dealloc(creat_vec);
}//С�������ڴ����