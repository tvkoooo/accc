#ifndef _INC_shijianqi
#define _INC_shijianqi
typedef void (*fun_shijianqi) (void* p);

struct shijianqi
{
	void *p_1;
	fun_shijianqi shijian1;
	struct shijianqi *pnext;
};

// �����ڴ����
extern struct shijianqi* shijianqi_alloc();
// �����ڴ����
extern void shijianqi_dealloc(struct shijianqi* p);


// �������ݳ�ʼ��
extern void shijianqi_init(struct shijianqi* p);
// ������������
extern void shijianqi_clear(struct shijianqi* p);
// ���������ͷ�
extern void shijianqi_destroy(struct shijianqi* p);

// �������---���ӣ����Ӷ��ģ�
extern void shijianqi_add(struct shijianqi* p,fun_shijianqi p2,void* p3);
//  �������---ɾ����ɾ�����ģ�
extern void shijianqi_sub(struct shijianqi* p);
//  �������---������ͳ�Ƹ���
extern int shijianqi_seizof(struct shijianqi* p);

extern void shijianqi_update(struct shijianqi* p);
#endif  /* _INC_vectordog */