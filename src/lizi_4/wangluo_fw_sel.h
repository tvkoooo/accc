#ifndef __wangluo_fw_sel
#define __wangluo_fw_sel
#include <pthread.h>
#include "socket_context_lizi4.h"
#include <map>

// ά��select�߳�״̬
//����sClient��ά����
//������չ�����recv�ĵȴ���


//����  recv  ���� accept socket�׽��֣��ȴ��ͻ�������Ϣ
//����  send  ���� accept socket�׽��֣�������Ϣ���ͻ�
struct wangluo_fw_sel
{
	int flag;
	socket_type sClient;
	struct timeval map_timeout;
	//std::map <socket_type,fd_set *> map_sel;
};
    
extern void *wangluo_fw_sel_pro(void *p);





#endif//__wangluo_fw_sel