#ifndef _INC_vectordog
#define _INC_vectordog
typedef void (*fun__vectordog) (void* p,int g);


struct fun__vectordog_nam
{
	char dog_name[20];
	int icc;
};
//С�����ݳ�ʼ��
extern void fun__vectordog_nam_init(struct fun__vectordog_nam *p_dog);
//С����������
extern void fun__vectordog_nam_destroy(struct fun__vectordog_nam *p_dog);

//С�����ָ�ֵ
extern void fun__vectordog_nam_dogname(struct fun__vectordog_nam *p_dog);


// �¼�������
struct fun__vectordog_new
{
	void *oo ;
	fun__vectordog fun__vectordog_talk;
	struct fun__vectordog_new *pnext;
};

// �����ڴ����
extern struct fun__vectordog_new* fun__vectordog_new_alloc();
// �����ڴ����
extern void fun__vectordog_new_dealloc(struct fun__vectordog_new* ptalk);


// �������ݳ�ʼ��
extern void fun__vectordog_new_init(struct fun__vectordog_new* ptalk);
// ������������
extern void fun__vectordog_new_clear(struct fun__vectordog_new* ptalk);
// ���������ͷ�
extern void fun__vectordog_new_destroy(struct fun__vectordog_new* ptalk);

// �������---���ӣ����Ӷ��ģ�
extern void fun__vectordog_new_add(struct fun__vectordog_new* ptalk,fun__vectordog fdog,void* p);
//  �������---ɾ����ɾ�����ģ�
extern void fun__vectordog_new_sub(struct fun__vectordog_new* ptalk);
//  �������---������ͳ�Ƹ���
extern int fun__vectordog_new_seizof(struct fun__vectordog_new*ptalk);


//  ��������ˢ��---����
extern void fun__vectordog_new_update(struct fun__vectordog_new* ptalk,int g);



//  �ⲿ��������
extern void fun__vectordog_new_test();
extern int shuijishu();

#endif  /* _INC_vectordog */