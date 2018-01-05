#ifndef _INC_fuwuqi_h_
#define _INC_fuwuqi_h_
#include <stdio.h>
#include "net/mm_net_tcp.h"
#include "net/mm_packet.h"
#include "net/mm_mailbox.h"
#include <map>
namespace lj_lizhi3
{
	typedef std::map<mm_uint64_t,mm_socket_t>  map_type;//类型实例化，泛型要实例化操作
	struct fuwuqi
	{
		std::map<mm_uint64_t,mm_socket_t> save_info;

		mm_mailbox fw_net_0;
		pthread_t poll_thread;
		int state;// mm_thread_state_t,default is ts_closed(0)
	};

	extern void fuwuqi_init(struct fuwuqi* p);
	extern void fuwuqi_destroy(struct fuwuqi* p);
	///////////////////////////////////////////////////
	extern void fuwuqi_fuzhi(struct fuwuqi* p);
	extern void fuwuqi_wait(struct fuwuqi* p);
	extern void fuwuqi_start(struct fuwuqi* p);
	extern void fuwuqi_interrupt(struct fuwuqi* p);
	extern void fuwuqi_shutdown(struct fuwuqi* p);
	extern void fuwuqi_join(struct fuwuqi* p);


	extern void fuwuqi_huidiaotifun(void* obj, void* u, struct mm_packet* pack);
	extern void fuwuqi_huidiaotifun_fw(void* obj, void* u, struct mm_packet* pack);








}

 


#endif  /* _INC_fuwuqi_h_ */