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
	
	//创建事件链
	do_dog1=shijianqi_alloc();

	//事件链数据初始化
	shijianqi_init(do_dog1);

	//小狗数据初始化
	shijian_dog_init(&dog[0]);
	shijian_dog_init(&dog[1]);
	shijian_dog_init(&dog[2]);

	//操作1-命名 n
	shijian_dog_do_name(&dog[0]);
	shijian_dog_do_name(&dog[1]);
	shijian_dog_do_name(&dog[2]);

	//操作2-标价 n
	shijian_dog_do_money(&dog[0]);
	shijian_dog_do_money(&dog[1]);
	shijian_dog_do_money(&dog[2]);

	//订阅 n
	shijianqi_add(do_dog1,shijian_dog_do_speak,&dog[0]);
	shijianqi_add(do_dog1,shijian_dog_do_speak,&dog[1]);
	shijianqi_add(do_dog1,shijian_dog_do_speak,&dog[2]);


	//发布
	shijianqi_update(do_dog1);

	//刷新状态
	shijian_dog_do_money(&dog[1]);

	//重新发布
	shijianqi_update(do_dog1);	


	//清空小狗数据
	shijian_dog_destroy(&dog[0]);
	shijian_dog_destroy(&dog[1]);
	shijian_dog_destroy(&dog[2]);

	shijianqi_destroy(do_dog1);
	//链表内存回收
	shijianqi_dealloc(do_dog1);

}//小狗数据内存回收