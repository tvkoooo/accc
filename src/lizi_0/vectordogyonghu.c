#include <stdio.h>
#include <stdlib.h>
#include "vectordogdingyue.h"
#include <string.h>
//С��˵��
void fun__vectordog_talk_a(void * p,int g)
{
	struct fun__vectordog_nam * dogname;
	int chos;


	dogname=(struct fun__vectordog_nam*)p;

	chos=shuijishu()%3;
	printf("\nС��  %s  %d  %d:    ",dogname->dog_name,dogname->icc,g);

	switch (chos)
	{
	case 0: printf("����ʳ��\n");break;
	case 1: printf("�Ҷ���\n");break;
	case 2: printf("������ʳ��С����ҧ��!\n");break;
	default:
		break;
	}
}
void fun__vectordog_qb_a(void * p,int g)
{
	struct fun__vectordog_nam * dogname;
	int chos;
	dogname=(struct fun__vectordog_nam*)p;

	chos=shuijishu()%3;
	printf("\nС��  %s  %d qb",dogname->dog_name,dogname->icc);
}
void fun__vectordog_rrrr_a(void * p,int g)
{

}
typedef void (*huidiaorr_type) (void * p,int g);
void fun__vectordog_new_test()
{
	//���������
	int num=0;
	//������С
	int vecnum;
	struct fun__vectordog_new* creat_vec;
	struct fun__vectordog_new* creat_vqb;

	struct fun__vectordog_nam dog[3];

	{
		// 1:1
		huidiaorr_type dd;
		//���� 1
		dd = fun__vectordog_rrrr_a;
		//����
		dd(NULL,8);
	}

	vecnum=0;

	// 1:n

	//��������
	creat_vec=fun__vectordog_new_alloc();
	creat_vqb=fun__vectordog_new_alloc();

	//�������ݳ�ʼ��
	fun__vectordog_new_init(creat_vec);
	fun__vectordog_new_init(creat_vqb);
	//С�����ݳ�ʼ��
	fun__vectordog_nam_init(&dog[0]);
	fun__vectordog_nam_init(&dog[1]);
	fun__vectordog_nam_init(&dog[2]);

	strcpy(dog[0].dog_name,"ni mei");
	strcpy(dog[1].dog_name,"nsss sf");
	strcpy(dog[2].dog_name,"a regeryy");
	dog[0].icc=34;
	dog[1].icc=22;
	dog[2].icc=87;

	//���� n
	fun__vectordog_new_add(creat_vec,fun__vectordog_talk_a,&dog[0]);
	fun__vectordog_new_add(creat_vec,fun__vectordog_talk_a,&dog[1]);
	fun__vectordog_new_add(creat_vec,fun__vectordog_talk_a,&dog[2]);

	fun__vectordog_new_add(creat_vqb,fun__vectordog_qb_a,&dog[0]);
	fun__vectordog_new_add(creat_vqb,fun__vectordog_qb_a,&dog[1]);
	fun__vectordog_new_add(creat_vqb,fun__vectordog_qb_a,&dog[2]);

	//����
	fun__vectordog_new_update(creat_vec,7);
	fun__vectordog_new_update(creat_vqb,8);
	//while(num!=555)
	//{

	//	printf("333��С��ȡ������222,����С��������770������666���������777ɾ���ϸ�������888�鿴�������ݣ�999�鿴�����������˳�555��\n");
	//	scanf(" %d",&num);
	//	if ((num==333))
	//	{
	//		//С�����ݸ�ֵ
	//		fun__vectordog_nam_dogname(dog);
	//	}
	//	//if ((num==222))
	//	//{
	//	//	//�������ӣ����Ӷ���
	//	//	fun__vectordog_new_add(creat_vec,fun__vectordog_talk_a,dog);

	//	//}

	//	if (num==770)
	//	{
	//		//����
	//		system("CLS");
	//	}
	//	if (num==555)
	//	{
	//		//�˳�
	//		break;
	//	}
	//	//if (num==777)
	//	//{
	//	//	//��������ȥ��һ�� ����
	//	//	fun__vectordog_new_sub(creat_vec);
	//	//}
	//	if (num==888)
	//	{
	//		//������������
	//		fun__vectordog_new_update(creat_vec);
	//	}
	//	if (num==999)
	//	{
	//		//�鿴����
	//		vecnum=fun__vectordog_new_seizof(creat_vec);
	//		printf("�鿴������С������%d�����ݣ��ڴ�����С�ǣ�%d\n",vecnum,vecnum*sizeof(struct fun__vectordog_new));
	//	}
	//	if (num==666)
	//	{
	//		//�������ȥ������
	//		fun__vectordog_new_clear(creat_vec);
	//	}

	//}

	//�������

	//���С������
	fun__vectordog_nam_destroy(&dog[0]);
	fun__vectordog_nam_destroy(&dog[1]);
	fun__vectordog_nam_destroy(&dog[2]);

	fun__vectordog_new_destroy(creat_vec);
	//�����ڴ����
	fun__vectordog_new_dealloc(creat_vec);

	fun__vectordog_new_destroy(creat_vqb);
	//�����ڴ����
	fun__vectordog_new_dealloc(creat_vqb);
}//С�������ڴ����

//struct playet
//{
//	float m;//Ǯ
//	struct fun__vectordog_new evt_yzd;
//
//	float f;//������
//	struct fun__vectordog_new evt_yzd;
//};
