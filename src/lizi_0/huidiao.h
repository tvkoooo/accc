#ifndef _INC_huidiao
#define _INC_huidiao
typedef void (*huidiao_type) (void);

struct fun_huidiao_new
{
	int clocktime;
	huidiao_type fun_hui;
};

#if defined(__cplusplus)
extern "C"
{
#endif



//tool init  tool数据层面初始化
extern void fun_huidiao_init(struct fun_huidiao_new*p);

//tool init  tool数据赋值
extern void fun_huidiao_fuzhi(struct fun_huidiao_new*p,huidiao_type p1);


//tool fun    时钟回掉
extern void fun_huidiao_fun(void);

//tool init  tool数据刷新
extern void fun_huidiao_update(struct fun_huidiao_new*p);

//tool destroy tool数据层面销毁
extern void fun_huidiao_destroy(struct fun_huidiao_new *p);

extern void huidiao_new();

#if defined(__cplusplus)
}
#endif 



//extern void editprinthuidiao();
//extern int shuijishu();
//extern void huidiao_clock_in(struct fun_huidiao_in clock_a);
//extern void huidiao_a();

#endif  /* _INC_huidiao */