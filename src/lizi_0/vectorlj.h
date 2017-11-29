#ifndef _INC_vectorlj
#define _INC_vectorlj
//typedef void (*vectorlj_type) (void);

struct fun__vectorlj_new
{
	int munber;
	struct fun__vectorlj_new *pnext;
};

#if defined(__cplusplus)
extern "C"
{
#endif



//tool init  内存初始化
extern struct fun__vectorlj_new* fun__vectorlj_new_alloc();

extern void fun__vectorlj_new_dealloc(struct fun__vectorlj_new* head);

//tool init  数据初始化
extern void fun__vectorlj_new_init(struct fun__vectorlj_new*head);

//tool init  数据赋值
extern void fun__vectorlj_new_fuzhi(struct fun__vectorlj_new*head,int *n);

//tool init  数据统计个数
extern int fun__vectorlj_new_seizof(struct fun__vectorlj_new*head);

//tool init  数据刷新
extern void fun__vectorlj_new_update(struct fun__vectorlj_new*head);

//tool init  数据清零
extern void fun__vectorlj_new_clear(struct fun__vectorlj_new*head);

//tool destroy 数据回初始化
extern void fun__vectorlj_new_destroy(struct fun__vectorlj_new*head);

//tool init  内存销毁
extern void fun__vectorlj_new_dealloc(struct fun__vectorlj_new*head);

extern void fun__vectorlj_new_test();

#if defined(__cplusplus)
}
#endif 

#endif  /* _INC_vectorlj */