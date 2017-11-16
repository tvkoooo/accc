#ifndef _INC_vectordog
#define _INC_vectordog
typedef void (*fun__vectordog) (void* p,int g);


struct fun__vectordog_nam
{
	char dog_name[20];
	int icc;
};
//小狗数据初始化
extern void fun__vectordog_nam_init(struct fun__vectordog_nam *p_dog);
//小狗数据清零
extern void fun__vectordog_nam_destroy(struct fun__vectordog_nam *p_dog);

//小狗名字赋值
extern void fun__vectordog_nam_dogname(struct fun__vectordog_nam *p_dog);


// 事件驱动器
struct fun__vectordog_new
{
	void *oo ;
	fun__vectordog fun__vectordog_talk;
	struct fun__vectordog_new *pnext;
};

// 链表内存分配
extern struct fun__vectordog_new* fun__vectordog_new_alloc();
// 链表内存回收
extern void fun__vectordog_new_dealloc(struct fun__vectordog_new* ptalk);


// 链表数据初始化
extern void fun__vectordog_new_init(struct fun__vectordog_new* ptalk);
// 链表数据清零
extern void fun__vectordog_new_clear(struct fun__vectordog_new* ptalk);
// 链表清零释放
extern void fun__vectordog_new_destroy(struct fun__vectordog_new* ptalk);

// 链表操作---增加（增加订阅）
extern void fun__vectordog_new_add(struct fun__vectordog_new* ptalk,fun__vectordog fdog,void* p);
//  链表操作---删掉（删掉订阅）
extern void fun__vectordog_new_sub(struct fun__vectordog_new* ptalk);
//  链表操作---链表订阅统计个数
extern int fun__vectordog_new_seizof(struct fun__vectordog_new*ptalk);


//  链表数据刷新---发布
extern void fun__vectordog_new_update(struct fun__vectordog_new* ptalk,int g);



//  外部函数声明
extern void fun__vectordog_new_test();
extern int shuijishu();

#endif  /* _INC_vectordog */