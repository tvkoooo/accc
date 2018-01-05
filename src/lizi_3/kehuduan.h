#ifndef _INC_kehuduan_h_
#define _INC_kehuduan_h_
#include <stdio.h>
#include "net/mm_net_tcp.h"
#include "net/mm_packet.h"
#include "net/mm_mailbox.h"
namespace lj_lizhi3
{

	struct kehuduan
	{
		int state_say_next;
		char syst_say1[256];
		char syst_say2[256];
		mm_uint64_t sid;
		mm_uint64_t uid;
		mm_net_tcp fw_net_1;
		pthread_t poll_thread;
		int state;// mm_thread_state_t,default is ts_closed(0)
	};

	extern void kehuduan_init(struct kehuduan* p);
	extern void kehuduan_destroy(struct kehuduan* p);
	///////////////////////////////////////////////////
	extern void kehuduan_fuzhi(struct kehuduan* p);
	extern void kehuduan_wait(struct kehuduan* p);
	extern void kehuduan_start(struct kehuduan* p);
	extern void kehuduan_interrupt(struct kehuduan* p);
	extern void kehuduan_shutdown(struct kehuduan* p);
	extern void kehuduan_join(struct kehuduan* p);


	extern void kehuduan_huidiaotifun(void* obj, void* u, struct mm_packet* pack);
	extern void kehuduan_huidiaotifun_fw(void* obj, void* u, struct mm_packet* pack);








}

 


#endif  /* _INC_kehuduan_h_ */