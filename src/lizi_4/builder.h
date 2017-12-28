#ifndef __builder
#define __builder
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>
#include "producer.h"
#include "consumer.h"
//#include "wangluo_fw_accept.h"

// ά��select�߳�״̬
//����sClient��ά����
//������չ�����recv�ĵȴ���
//���յ���Ϣ������recv  ��buf


struct builder
{

	struct producer pro_1;
	struct consumer con_1;

	//pthread_t poll_thread;
	//int state;// mm_thread_state_t,default is ts_closed(0)

};
    
//extern void *builder_pro(void *p);
//extern fun_void_s3 builder_shujuchuandi(void *p,socket_type sClient,struct sockaddr_in *pr,socklen_t nAddrlen);


extern void builder_init(struct builder* p);
extern void builder_destroy(struct builder* p);
//
//extern void builder_updata(void* p,socket_type sClient);
//extern void builder_poll_wait(struct builder* p);
//
extern void builder_start(struct builder* p);
extern void builder_interrupt(struct builder* p);
extern void builder_shutdown(struct builder* p);
extern void builder_join(struct builder* p);


#endif//__builder