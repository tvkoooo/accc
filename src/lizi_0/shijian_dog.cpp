#include <stdio.h>
#include <stdlib.h>
#include "shijian_dog.h"
#include <string.h>

//С�����ݳ�ʼ��
void shijian_dog_init(struct shijian_dog *p_dog)
{
	p_dog->dog_money=0;
	strcpy(p_dog->dog_name,"0");
}
//С����������
void shijian_dog_destroy(struct shijian_dog *p_dog)
{
	p_dog->dog_money=0;
	strcpy(p_dog->dog_name,"0");
}

void shijian_dog_do_name(void *p_dog)
{
	shijian_dog * dog;
	dog=(shijian_dog*)p_dog;
	char name[20];
	printf("�����빷��������\n");
	scanf(" %s",name);
	strcpy(dog->dog_name,name);
}

void shijian_dog_do_money(void *p_dog)
{
	shijian_dog * d;
	d=(shijian_dog*)p_dog;

	printf("������ %s ���ļ۸�\n",d->dog_name);
	scanf("%d",&d->dog_money);

}

void shijian_dog_do_speak(void *p_dog)
{
	shijian_dog * dogname;
	dogname=(shijian_dog*)p_dog;
	int chos;
	chos=rand()%3;
	printf("\nС��%s(%d  $):",dogname->dog_name,dogname->dog_money);
	switch (chos)
	{
	case 0: printf("����ʳ��\n");break;
	case 1: printf("�Ҷ���\n");break;
	case 2: printf("������ʳ��С����ҧ��!\n");break;
	default:
		break;
	}
}