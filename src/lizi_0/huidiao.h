#ifndef _INC_huidiao
#define _INC_huidiao

struct fun_huidiao_new
{
	int clocktime;
	void (*fun_hui) (void); 
};

//tool init  tool���ݲ����ʼ��
extern void fun_huidiao_init(struct fun_huidiao_new*p);
//tool fun    ʱ�ӻص�
extern void fun_huidiao_fun();
//tool destroy tool���ݲ�������
extern void fun_huidiao_destroy(struct fun_huidiao_new *p);

extern void huidiao_new();





//extern void editprinthuidiao();
//extern int shuijishu();
//extern void huidiao_clock_in(struct fun_huidiao_in clock_a);
//extern void huidiao_a();

#endif  /* _INC_huidiao */