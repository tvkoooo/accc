#ifndef __wangluo_fw_accept
#define __wangluo_fw_accept
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>

typedef void (*fun_void_s3) (socket_type,struct sockaddr_in *,socklen_t);

//ά��socket��״̬�����������ͽ�����
//��һ��accept���̣߳���ά��������������
//����  ���� accept socket�׽�����Ϣ��
struct wangluo_fw_accept 
{
	std::map<socket_type,int> map_s;
	socket_type sClient;
	socket_type slisten;
	struct sockaddr_in remoteAddr;
	socklen_t nAddrlen;

	//char revData[255];

	struct timeval map_timeout;

	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)
	int error_code;

	fun_void_s3 fun_hui;
};





extern void wangluo_fw_accept_init(struct wangluo_fw_accept* p);
extern void wangluo_fw_accept_destroy(struct wangluo_fw_accept* p);
//
extern void wangluo_fw_accept_fuzhi_f3(struct wangluo_fw_accept* p,fun_void_s3 p1);
extern void wangluo_fw_accept_poll_wait(struct wangluo_fw_accept* p);
//
extern void wangluo_fw_accept_start(struct wangluo_fw_accept* p);
extern void wangluo_fw_accept_interrupt(struct wangluo_fw_accept* p);
extern void wangluo_fw_accept_shutdown(struct wangluo_fw_accept* p);
extern void wangluo_fw_accept_join(struct wangluo_fw_accept* p);





#endif//__wangluo_fw_accept