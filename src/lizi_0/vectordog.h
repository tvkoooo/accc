#ifndef _INC_vectordog
#define _INC_vectordog
typedef void (*fun__vectordog) (struct fun__vectordog_nam* pname);

struct fun__vectordog_new
{

	fun__vectordog fun__vectordog_talk;
	struct fun__vectordog_new *pnext;
};

struct fun__vectordog_nam
{
	char dog_name[20];

};




//tool init  内存初始化
extern struct fun__vectordog_new* fun__vectordog_new_alloc();

extern void fun__vectordog_new_dealloc(fun__vectordog_new* head);

//tool init  数据初始化
extern void fun__vectordog_new_init(struct fun__vectordog_new* head,struct fun__vectordog_nam *p_dog);

extern void fun__vectordog_new_dogname(struct fun__vectordog_nam *p_dog);

//tool init  数据赋值
extern void fun__vectordog_new_add(struct fun__vectordog_new* head,fun__vectordog fdog,struct fun__vectordog_nam *p_dog);

extern void fun__vectordog_new_sub(struct fun__vectordog_new* head);

//tool init  数据统计个数
extern int fun__vectordog_new_seizof(struct fun__vectordog_new*head);

//tool init  数据刷新
extern void fun__vectordog_new_update(struct fun__vectordog_new* ptalk,struct fun__vectordog_nam* pname);

//tool init  小狗说话
extern void fun__vectordog_talk_a();

//tool init  数据清零
extern void fun__vectordog_new_clear(struct fun__vectordog_new* ptalk);

//tool destroy 数据回初始化
extern void fun__vectordog_new_destroy(struct fun__vectordog_new* ptalk);

//tool init  内存销毁
extern void fun__vectordog_new_dealloc(struct fun__vectordog_new*head);

extern void fun__vectordog_new_test();

extern int shuijishu();

#endif  /* _INC_vectordog */