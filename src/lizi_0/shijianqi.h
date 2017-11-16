#ifndef _INC_shijianqi
#define _INC_shijianqi
typedef void (*fun_shijianqi) (void* p);

struct shijianqi
{
	void *p_1;
	fun_shijianqi shijian1;
	struct shijianqi *pnext;
};

// 链表内存分配
extern struct shijianqi* shijianqi_alloc();
// 链表内存回收
extern void shijianqi_dealloc(struct shijianqi* p);


// 链表数据初始化
extern void shijianqi_init(struct shijianqi* p);
// 链表数据清零
extern void shijianqi_clear(struct shijianqi* p);
// 链表清零释放
extern void shijianqi_destroy(struct shijianqi* p);

// 链表操作---增加（增加订阅）
extern void shijianqi_add(struct shijianqi* p,fun_shijianqi p2,void* p3);
//  链表操作---删掉（删掉订阅）
extern void shijianqi_sub(struct shijianqi* p);
//  链表操作---链表订阅统计个数
extern int shijianqi_seizof(struct shijianqi* p);

extern void shijianqi_update(struct shijianqi* p);
#endif  /* _INC_vectordog */