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



//tool init  �ڴ��ʼ��
extern struct fun__vectorlj_new* fun__vectorlj_new_alloc();

extern void fun__vectorlj_new_dealloc(struct fun__vectorlj_new* head);

//tool init  ���ݳ�ʼ��
extern void fun__vectorlj_new_init(struct fun__vectorlj_new*head);

//tool init  ���ݸ�ֵ
extern void fun__vectorlj_new_fuzhi(struct fun__vectorlj_new*head,int *n);

//tool init  ����ͳ�Ƹ���
extern int fun__vectorlj_new_seizof(struct fun__vectorlj_new*head);

//tool init  ����ˢ��
extern void fun__vectorlj_new_update(struct fun__vectorlj_new*head);

//tool init  ��������
extern void fun__vectorlj_new_clear(struct fun__vectorlj_new*head);

//tool destroy ���ݻس�ʼ��
extern void fun__vectorlj_new_destroy(struct fun__vectorlj_new*head);

//tool init  �ڴ�����
extern void fun__vectorlj_new_dealloc(struct fun__vectorlj_new*head);

extern void fun__vectorlj_new_test();

#if defined(__cplusplus)
}
#endif 

#endif  /* _INC_vectorlj */