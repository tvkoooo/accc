#include <tchar.h>
#include <stdio.h>
#include <exception>
#include "dog.h"


void dogname_init(struct dog *pscanf)
{
	int a;
	printf("creat dog name");
	//memset(pscanf,0,sizeof(struct dog));
	scanf("%d",&a);
	pscanf->dog_name=a;

}

void dogname_destroy(struct dog *pscanf)
{
	printf("del dog name");
	//memset(pscanf,0,sizeof(struct dog));
	pscanf->dog_name=0;
}


 void dogtalk_init(struct dog *pscanf)
{
	printf("creat dog talk1");
	scanf("%s",&pscanf->dog_talk1);
	printf("creat dog talk2");
	scanf("%s",&pscanf->dog_talk2);
	printf("creat dog talk3");
	scanf("%s",&pscanf->dog_talk3);
	printf("creat dog talk4");
	scanf("%s",&pscanf->dog_talk4);
}


 void dogtalk_destroy(struct dog *pscanf)
{
	int i;
	printf("del dog talk");
	for(i=0;i++;i<20)
        pscanf->dog_talk1[i]=0;
	for(i=0;i++;i<20)
		pscanf->dog_talk2[i]=0;
	for(i=0;i++;i<20)
		pscanf->dog_talk3[i]=0;
	for(i=0;i++;i<20)
		pscanf->dog_talk4[i]=0;
}

void dog_talk(struct dog *pscanf)
{
	int i;
	i=shuijishu()%4;
	switch (i)
	{
	case 0:printf("Dog %d:s%",pscanf->dog_name,pscanf->dog_talk1);break;
	case 1:printf("Dog %d:s%",pscanf->dog_name,pscanf->dog_talk1);break;
	case 2:printf("Dog %d:s%",pscanf->dog_name,pscanf->dog_talk1);break;
	case 3:printf("Dog %d:s%",pscanf->dog_name,pscanf->dog_talk1);break;

	default:
		break;
	}

}
