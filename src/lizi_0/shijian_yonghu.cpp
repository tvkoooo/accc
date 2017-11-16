#include <stdio.h>
#include <stdlib.h>
#include "shijian_yonghu.h"
#include "shijianqi.h"
#include "shijian_dog.h"
#include <string.h>



void shijian_yonghu_test()
{

	// 1:n
	shijianqi* do_dog1;
	
	shijian_dog dog[3];
	
	//�����¼���
	do_dog1=shijianqi_alloc();

	//�¼������ݳ�ʼ��
	shijianqi_init(do_dog1);

	//С�����ݳ�ʼ��
	shijian_dog_init(&dog[0]);
	shijian_dog_init(&dog[1]);
	shijian_dog_init(&dog[2]);

	//����1-���� n
	shijian_dog_do_name(&dog[0]);
	shijian_dog_do_name(&dog[1]);
	shijian_dog_do_name(&dog[2]);

	//����2-��� n
	shijian_dog_do_money(&dog[0]);
	shijian_dog_do_money(&dog[1]);
	shijian_dog_do_money(&dog[2]);

	//���� n
	shijianqi_add(do_dog1,shijian_dog_do_speak,&dog[0]);
	shijianqi_add(do_dog1,shijian_dog_do_speak,&dog[1]);
	shijianqi_add(do_dog1,shijian_dog_do_speak,&dog[2]);


	//����
	shijianqi_update(do_dog1);

	//ˢ��״̬
	shijian_dog_do_money(&dog[1]);

	//���·���
	shijianqi_update(do_dog1);	


	//���С������
	shijian_dog_destroy(&dog[0]);
	shijian_dog_destroy(&dog[1]);
	shijian_dog_destroy(&dog[2]);

	shijianqi_destroy(do_dog1);
	//�����ڴ����
	shijianqi_dealloc(do_dog1);

}//С�������ڴ����