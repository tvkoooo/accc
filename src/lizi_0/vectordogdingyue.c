#include <stdio.h>
#include <stdlib.h>
#include "vectordogdingyue.h"
#include <string.h>




//С�����ݳ�ʼ��
void fun__vectordog_nam_init(struct fun__vectordog_nam *p_dog)
{
	strcpy(p_dog->dog_name,"0");
	p_dog->icc=0;

}
//С����������
void fun__vectordog_nam_destroy(struct fun__vectordog_nam *p_dog)
{
	strcpy(p_dog->dog_name,"0");
	p_dog->icc=0;
}

//С�����ָ�ֵ
void fun__vectordog_nam_dogname(struct fun__vectordog_nam *p_dog)
{
	int i;
	for (i=0;i<3;i++)
	{	
		printf("С�� %d :�������ǣ�\n",i);
		scanf(" %[^\n]",(p_dog+i)->dog_name);
	}

}

//����պ�������
static void kongkongkong(void*p,int g)
{

}

// �����ڴ����
struct fun__vectordog_new* fun__vectordog_new_alloc()
{
	struct fun__vectordog_new* head;
	head=(struct fun__vectordog_new*) malloc(sizeof(struct fun__vectordog_new));
	return(head);
}
// �����ڴ����
void fun__vectordog_new_dealloc(struct fun__vectordog_new* ptalk)
{
		free(ptalk);
}


// �������ݳ�ʼ��
void fun__vectordog_new_init(struct fun__vectordog_new* ptalk)
{
	ptalk->fun__vectordog_talk=kongkongkong;
	ptalk->pnext=NULL;
}
// ������������
void fun__vectordog_new_clear(struct fun__vectordog_new* ptalk)
{
	struct fun__vectordog_new *find_p,*p1;
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
// ���������ͷ�
void fun__vectordog_new_destroy(struct fun__vectordog_new* ptalk)
{
	fun__vectordog_new_clear(ptalk);
	ptalk->fun__vectordog_talk=kongkongkong;
	ptalk->pnext=NULL;
}

// �������---���ӣ����Ӷ��ģ�
void fun__vectordog_new_add(struct fun__vectordog_new* ptalk,fun__vectordog fdog,void *p)
{
	struct fun__vectordog_new *find_p,*p1,*p2;

	p2=(struct fun__vectordog_new*) malloc(sizeof(struct fun__vectordog_new));
	p1=ptalk;
	while (p1->pnext!=NULL)
	{
		find_p=p1;
		p1=p1->pnext;

	}
	p1->pnext=p2;

	//printf("С���������ǣ�\n");
	//scanf(" %[^\n]",pname->dog_name);
	p2->fun__vectordog_talk=fdog;
	p2->oo=p;
	p2->pnext=NULL;
}
//  �������---ɾ����ɾ�����ģ�
void fun__vectordog_new_sub(struct fun__vectordog_new* ptalk)
{
	struct fun__vectordog_new *find_p,*p1;
	find_p=p1=ptalk;
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
//  �������---������ͳ�Ƹ���
int fun__vectordog_new_seizof(struct fun__vectordog_new*ptalk)
{
	struct fun__vectordog_new *find_p,*p1;
	int n=0;
	find_p=p1=NULL;
	p1=ptalk;
	while(p1->pnext!=NULL)
	{
		find_p=p1;
		p1=p1->pnext;
		n++;
	};
	return(n);
}


//  ��������ˢ��---����
void fun__vectordog_new_update(struct fun__vectordog_new* ptalk,int g)
{
	struct fun__vectordog_new *find_p,*p1;

	p1=ptalk;

	while(p1->pnext!=NULL) 
	{

		find_p=p1;
		p1=p1->pnext;
		p1->fun__vectordog_talk(p1->oo,g);

	}

}

