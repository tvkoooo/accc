#include <tchar.h>
#include <stdio.h>
#include <exception>
#include "dog.h"


void dogname_init(struct dog *pscanf)
{
	
	printf("creat dog name\n");
	//memset(pscanf,0,sizeof(struct dog));
	scanf("%s",pscanf->dog_name);

	printf("your dog is :%s\n",pscanf->dog_name);


}

void dogname_destroy(struct dog *pscanf)
{
	int i;
	//printf("del dog%s \n",pscanf->dog_name);
	//memset(pscanf,0,sizeof(struct dog));
	for(i=0;i<20;i++)
		pscanf->dog_name[i]=0;
}


 void dogtalk_init(struct dog *pscanf)
{
	printf("\n\nyour dog is :%s\n",pscanf->dog_name);
	printf("creat dog talk1\n");
	scanf("%s",&pscanf->dog_talk1);
	printf("creat dog talk2\n");
	scanf("%s",&pscanf->dog_talk2);
	printf("creat dog talk3\n");
	scanf("%s",&pscanf->dog_talk3);
	printf("creat dog talk4\n");
	scanf("%s",&pscanf->dog_talk4);
}


 void dogtalk_destroy(struct dog *pscanf)
{
	int i;
	//printf("del dog%s talk\n",pscanf->dog_name);
	for(i=0;i<20;i++)
        pscanf->dog_talk1[i]=0;
	for(i=0;i<20;i++)
		pscanf->dog_talk2[i]=0;
	for(i=0;i<20;i++)
		pscanf->dog_talk3[i]=0;
	for(i=0;i<20;i++)
		pscanf->dog_talk4[i]=0;
}

void dog_talk(struct dog *pscanf)
{
	int i;
	i=shuijishu()%4;
	switch (i)
	{
	case 0:printf("Dog %s:%s\n",pscanf->dog_name,pscanf->dog_talk1);break;
	case 1:printf("Dog %s:%s\n",pscanf->dog_name,pscanf->dog_talk2);break;
	case 2:printf("Dog %s:%s\n",pscanf->dog_name,pscanf->dog_talk3);break;
	case 3:printf("Dog %s:%s\n",pscanf->dog_name,pscanf->dog_talk4);break;

	default:printf("no dog can be");
		break;
	}

}
