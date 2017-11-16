#ifndef _INC_shijian_dog
#define _INC_shijian_dog


struct shijian_dog
{
	char   dog_name[20];
	int   dog_money;
};
//小狗数据初始化
extern void shijian_dog_init(struct shijian_dog *p_dog);
//小狗数据清零
extern void shijian_dog_destroy(struct shijian_dog *p_dog);

extern void shijian_dog_do_name(void *p_dog);

extern void shijian_dog_do_money(void *p_dog);

extern void shijian_dog_do_speak(void *p_dog);


#endif  /* _INC_shijian_dog */