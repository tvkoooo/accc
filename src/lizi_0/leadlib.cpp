#include <tchar.h>
#include <stdio.h>
#include <stdlib.h>
#include <exception>
#include <string.h>
#include <ctime>
#include <doglib.h>
#include <leadlib.h>

void createdog(struct doglib *p_dog,struct leadlib*p_lead)
{

	printf("请给狗取个名字\n");
	scanf(" %s",p_dog->dog_name);
	strcpy(p_dog->dog_hair,"Red");
	strcpy(p_dog->dog_hair,"Red");
//	p_dog->dog_type=
}