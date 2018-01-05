#ifndef _INC_fankuui_h_
#define _INC_fankuui_h_
#include <stdio.h>
#include "net/mm_net_tcp.h"
#include "net/mm_packet.h"
#include "net/mm_mailbox.h"
namespace lj_lizhi3
{

	struct fan_num
	{
		mm_mailbox fw_net_0;
		mm_net_tcp fw_net_1;
		pthread_t poll_thread;
		int state;// mm_thread_state_t,default is ts_closed(0)
	};

	extern void fan_num_init(struct fan_num* p);
	extern void fan_num_destroy(struct fan_num* p);
	///////////////////////////////////////////////////
	extern void fan_num_fuzhi(struct fan_num* p);
	extern void fan_num_wait(struct fan_num* p);
	extern void fan_num_start(struct fan_num* p);
	extern void fan_num_interrupt(struct fan_num* p);
	extern void fan_num_shutdown(struct fan_num* p);
	extern void fan_num_join(struct fan_num* p);
	extern void fankui(struct fan_num* p);

	extern void fan_num_huidiaotifun(void* obj, void* u, struct mm_packet* pack);
	extern void fan_num_huidiaotifun_fw(void* obj, void* u, struct mm_packet* pack);








}

 


#endif  /* _INC_fankuui_h_ */