#include <stdio.h>
#include <stdlib.h>
#include "shijianqi.h"
#include <string.h>

//����պ�������
void kongkongkong(void*p)
{

}

// �����ڴ����
struct shijianqi* shijianqi_alloc()
{
	shijianqi* head;
	head=(shijianqi*) malloc(sizeof(struct shijianqi));
	return(head);
}
// �����ڴ����
void shijianqi_dealloc(struct shijianqi* p)
{
	free(p);
}

// �������ݳ�ʼ��
void shijianqi_init(struct shijianqi* p)
{
	p->p_1=NULL;
	p->shijian1=kongkongkong;
	p->pnext=NULL;
}
// ������������
void shijianqi_clear(struct shijianqi* p)
{
	shijianqi *find_p,*p_see;
	find_p=p_see=p;

	while (p->pnext!=NULL)
	{	
		find_p=p;
		p_see=p->pnext;
		while(p_see->pnext!=NULL) 
		{

			find_p=p_see;
			p_see=p_see->pnext;
		};

		p_see->p_1=NULL;
		p_see->shijian1=kongkongkong;
		find_p->pnext=p_see->pnext;
		free(p_see);
	}

}
// ���������ͷ�
void shijianqi_destroy(struct shijianqi* p)
{
	shijianqi_clear(p);
	p->p_1=NULL;
	p->shijian1=kongkongkong;
	p->pnext=NULL;
}

// �������---���ӣ����Ӷ��ģ�
void shijianqi_add(struct shijianqi* p,fun_shijianqi p2,void* p3)
{
	shijianqi *find_p,*psee,*pdo;
	pdo=(shijianqi*) malloc(sizeof(struct shijianqi));
	psee=p;
	while (psee->pnext!=NULL)
	{
		find_p=psee;
		psee=psee->pnext;

	}
	psee->pnext=pdo;
	pdo->shijian1=p2;
	pdo->p_1=p3;
	pdo->pnext=NULL;

}
//  �������---ɾ����ɾ�����ģ�
void shijianqi_sub(struct shijianqi* p,void *p_sub)
{
	int lian=0;
	shijianqi *find_p,*psee;
	find_p=psee=p;
	if (psee->pnext==NULL)
	{
		printf("���ж����Ѿ�ɾ��");
	}
	else
	{
		while (psee->pnext!=NULL)
		{
			if (psee->p_1==p_sub)
			{
				break;
			}

			find_p=psee;
			psee=psee->pnext;
			lian++;
		}
		psee->shijian1=kongkongkong;
		psee->p_1=NULL;
		find_p->pnext=psee->pnext;
		free(psee);
	}
}
//  �������---������ͳ�Ƹ���
int shijianqi_seizof(struct shijianqi* p)
{
	shijianqi *find_p,*psee;
	int n=0;
	find_p=psee=p;
	while(psee->pnext!=NULL)
	{
		find_p=psee;
		psee=psee->pnext;
		n++;
	};
	return(n);
}

void shijianqi_update(struct shijianqi* p)
{
	shijianqi *find_p,*psee;

	find_p=psee=p;

	while(psee->pnext!=NULL) 
	{

		find_p=psee;
		psee=psee->pnext;
		{
			psee->shijian1(psee->p_1);
		}

	}

}