#ifndef _INC_savestore
#define _INC_savestore
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>

//#include "wangluo_fw_accept.h"

// 维护select线程状态
//接受sClient，维护表
//处理接收过来的recv的等待。
//接收的信息发布，recv  的buf

//typedef void (*savestore_handle)( void* obj, int mes[5]);

//struct savestore_callback
//{
//	savestore_handle handle;
//	void* obj;// weak ref. user data for callback.
//};
//
//extern void savestore_callback_init(struct savestore_callback* p);
//extern void savestore_callback_destroy(struct savestore_callback* p);
//
//struct lj_message_head_data
//{
//	UINT32 mid;
//	UINT32 pid;
//	UINT64 sid;
//	UINT64 uid;
//};
//
//struct lj_message_head
//{
//	UINT16 msg_size;
//	UINT16 head_size;
//	struct lj_message_head_data lj_hd;
//	//char * head_data;
//	//char * body_data;
//};

struct intint_
{
	int a;
	int b;
};

static const int PAGE_SIZE=8;
struct savestore
{
	int       page_now;
	char      *buffer_head;
	char      *store_gptr;
	char      *store_pptr;

	//状态维护
	pthread_t poll_thread;
	int state;// mm_thread_state_t,default is ts_closed(0)

};

extern void savestore_move_data(struct savestore* p);
extern void savestore_change_vec(struct savestore* p,int page_num);
extern void savestore_cpydata(struct savestore* p,char *addr,int seize_o,int size_data);
extern void savestore_indata(struct savestore* p,char *addr,int seize_o,int size_data);
extern void savestore_outdata(struct savestore* p,char *addr,int seize_o,int size_data);

extern void savestore_init(struct savestore* p);
extern void savestore_destroy(struct savestore* p);
//
//extern void savestore_setcallback(struct savestore* p,struct savestore_callback* pp);
//
extern void savestore_poll_wait(struct savestore* p);
//
extern void savestore_start(struct savestore* p);
extern void savestore_interrupt(struct savestore* p);
extern void savestore_shutdown(struct savestore* p);
extern void savestore_join(struct savestore* p);


#endif//_INC_savestore