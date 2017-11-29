#include <stdio.h>
#include <stdlib.h>
#include "shijian_dog.h"
#include <string.h>

//小狗数据初始化
void shijian_dog_init(struct shijian_dog *p_dog)
{
	p_dog->dog_money=0;
	strcpy(p_dog->dog_name,"0");
}
//小狗数据清零
void shijian_dog_destroy(struct shijian_dog *p_dog)
{
	p_dog->dog_money=0;
	strcpy(p_dog->dog_name,"0");
}

void shijian_dog_do_name(void *p_dog)
{
	struct shijian_dog * dog;
	char name[20];
	dog=(struct shijian_dog*)p_dog;

	printf("请输入狗的新名字\n");
	scanf(" %s",name);
	strcpy(dog->dog_name,name);
}

void shijian_dog_do_money(void *p_dog)
{
	struct shijian_dog * d;
	d=(struct shijian_dog*)p_dog;

	printf("请输入 %s 狗的价格\n",d->dog_name);
	scanf("%d",&d->dog_money);

}

void shijian_dog_do_speak(void *p_dog)
{
	struct shijian_dog * dogname;
	int chos;
	dogname=(struct shijian_dog*)p_dog;

	chos=rand()%3;
	printf("\n小狗%s(%d  $):",dogname->dog_name,dogname->dog_money);
	switch (chos)
	{
	case 0: printf("给我食物\n");break;
	case 1: printf("我饿了\n");break;
	case 2: printf("不给我食物小心我咬你!\n");break;
	default:
		break;
	}
}