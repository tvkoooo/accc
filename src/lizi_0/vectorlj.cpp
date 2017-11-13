#include <stdio.h>
#include <stdlib.h>
#include "vectorlj.h"

#define LEN sizeof(struct fun__vectorlj_new)


struct fun__vectorlj_new* fun__vectorlj_new_alloc()
{
	fun__vectorlj_new* head;
	head=(fun__vectorlj_new*) malloc(LEN);
	return(head);
}

void fun__vectorlj_new_dealloc(fun__vectorlj_new* head)
{

	free(head);
}

//tool init  tool���ݲ����ʼ��
void fun__vectorlj_new_init(struct fun__vectorlj_new* head)
{
	head->munber=0;
	head->pnext=NULL;
}


void fun__vectorlj_new_add(struct fun__vectorlj_new* head,int *num)
{	
		fun__vectorlj_new *find_p,*p1,*p2;	
		p2=(fun__vectorlj_new*) malloc(LEN);
		p1=head;
		while (p1->pnext!=NULL)
		{
			find_p=p1;
			p1=p1->pnext;
		}
		p1->pnext=p2;
		p2->munber=*num;
		p2->pnext=NULL;
}

void fun__vectorlj_new_sub(struct fun__vectorlj_new* head)
{
	fun__vectorlj_new *find_p,*p1;
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
		free(p1);
		p1=NULL;
		find_p->pnext=NULL;
	}

}


//tool init  tool����ˢ��
void fun__vectorlj_new_update(struct fun__vectorlj_new* head)
{
	fun__vectorlj_new *find_p,*p1;
	p1=head;

	while(p1->pnext!=NULL) 
	{
		find_p=p1;
		p1=p1->pnext;
		printf("���ݣ�%d\n",p1->munber);

	};
	
}

//tool init  ����ͳ�Ƹ���
int fun__vectorlj_new_seizof(struct fun__vectorlj_new*head)
{
	fun__vectorlj_new *find_p,*p1;
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

//tool destroy ������������
void fun__vectorlj_new_clear(struct fun__vectorlj_new* head)
{
	fun__vectorlj_new *find_p,*p1;
	find_p=p1=head;

	while (head->pnext!=NULL)
	{	
		find_p=head;
		p1=head->pnext;
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

//tool destroy ���ݻس�ʼ��
void fun__vectorlj_new_destroy(struct fun__vectorlj_new *head)
{
	fun__vectorlj_new_clear(head);

	head->munber=0;
	head->pnext=NULL;
}

void fun__vectorlj_new_test()
{
	int vecnum=0;
	//��������
	int num;
	num=0;
	//��������
	fun__vectorlj_new* creat_vec;

	//��������
	creat_vec=fun__vectorlj_new_alloc();

	//�������ݳ�ʼ��
	fun__vectorlj_new_init(creat_vec);


	while(num!=555)
	{
		//�����������
		printf("��Ҫ��ӵ�����������,��770������666������ݣ�777ɾ���ϸ����ݣ�888�鿴�������ݣ�999�鿴������С���˳�555��\n");
		scanf("%d",&num);
		if ((num!=770)&&(num!=666)&&(num!=777)&&(num!=888)&&(num!=999)&&(num!=555))
		{
			fun__vectorlj_new_add(creat_vec,&num);
		}
		if (num==770)
		{
			system("CLS");
		}
		if (num==555)
		{
			break;
		}
		if (num==777)
		{
			fun__vectorlj_new_sub(creat_vec);
		}
		if (num==888)
		{
			fun__vectorlj_new_update(creat_vec);
		}
		if (num==999)
		{
			vecnum=fun__vectorlj_new_seizof(creat_vec);
			printf("�鿴������С������%d�����ݣ��ڴ�����С�ǣ�%d\n",vecnum,vecnum*LEN);
		}
		if (num==666)
		{
			fun__vectorlj_new_clear(creat_vec);

		}

	}
	fun__vectorlj_new_clear(creat_vec);
	fun__vectorlj_new_destroy(creat_vec);
	fun__vectorlj_new_dealloc(creat_vec);
}