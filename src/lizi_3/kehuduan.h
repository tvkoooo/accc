#ifndef _INC_kehuduan_h_
#define _INC_kehuduan_h_
#include <stdio.h>
#include "net/mm_net_tcp.h"
#include "net/mm_packet.h"
#include "net/mm_mailbox.h"
#include "lc_message_package.pb.h"
#include "b_error.pb.h"
#include "c_dialogue_com.pb.h"
//#include "c_dialogue_baseinfo.pb.h"
//#include "c_dialogue_baseinfo_data.h"

namespace lj_lizhi3
{

	struct kehuduan
	{

		mm_uint32_t system_state;
		mm_uint64_t uid;
		std::string user_nick;
		mm_uint64_t to_uid;
		std::string to_user_nick;
		mm_uint32_t user_socket;
		mm_uint32_t to_user_socket;
		mm_net_tcp fw_net_1;

		pthread_mutex_t t_data;// Êý¾ÝËø
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


	extern void kehuduan_huidiaoti_dialogue_login(void* obj, void* u, struct mm_packet* pack);
	extern void kehuduan_huidiaoti_dialogue_seek(void* obj, void* u, struct mm_packet* pack);
	extern void kehuduan_huidiaoti_dialogue_talk_nt(void* obj, void* u, struct mm_packet* pack);
	extern void kehuduan_huidiaoti_dialogue_talk_rs(void* obj, void* u, struct mm_packet* pack);


	static void _static_kehuduan_login_send(struct kehuduan* p);
	static void _static_kehuduan_seek_send(struct kehuduan* p);
	static void _static_kehuduan_talk_send(struct kehuduan* p);


}

 


#endif  /* _INC_kehuduan_h_ */