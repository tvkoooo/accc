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




//tool init  �ڴ��ʼ��
extern struct fun__vectordog_new* fun__vectordog_new_alloc();

extern void fun__vectordog_new_dealloc(fun__vectordog_new* head);

//tool init  ���ݳ�ʼ��
extern void fun__vectordog_new_init(struct fun__vectordog_new* head,struct fun__vectordog_nam *p_dog);

extern void fun__vectordog_new_dogname(struct fun__vectordog_nam *p_dog);

//tool init  ���ݸ�ֵ
extern void fun__vectordog_new_add(struct fun__vectordog_new* head,fun__vectordog fdog,struct fun__vectordog_nam *p_dog);

extern void fun__vectordog_new_sub(struct fun__vectordog_new* head);

//tool init  ����ͳ�Ƹ���
extern int fun__vectordog_new_seizof(struct fun__vectordog_new*head);

//tool init  ����ˢ��
extern void fun__vectordog_new_update(struct fun__vectordog_new* ptalk,struct fun__vectordog_nam* pname);

//tool init  С��˵��
extern void fun__vectordog_talk_a();

//tool init  ��������
extern void fun__vectordog_new_clear(struct fun__vectordog_new* ptalk);

//tool destroy ���ݻس�ʼ��
extern void fun__vectordog_new_destroy(struct fun__vectordog_new* ptalk);

//tool init  �ڴ�����
extern void fun__vectordog_new_dealloc(struct fun__vectordog_new*head);

extern void fun__vectordog_new_test();

extern int shuijishu();

#endif  /* _INC_vectordog */