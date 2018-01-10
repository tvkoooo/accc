#ifndef _INC_fuwuqi_h_
#define _INC_fuwuqi_h_
#include <stdio.h>
#include "net/mm_net_tcp.h"
#include "net/mm_packet.h"
#include "net/mm_mailbox.h"
//#include "lc_message_package.pb.h"
#include "b_error.pb.h"
#include "c_dialogue_com.pb.h"
#include "mm_protobuff_cxx.h"
#include <map>
#include <fstream>
namespace lj_lizhi3
{
	typedef std::map<mm_uint64_t,mm_socket_t>  map_type_int64_soc;//类型实例化，泛型要实例化操作
	typedef std::map<std::string,mm_uint64_t>  map_type_str_int64;//类型实例化，泛型要实例化操作
	typedef std::map<mm_uint64_t,std::string>  map_type_int64_str;//类型实例化，泛型要实例化操作
	struct fuwuqi
	{

		map_type_int64_soc map_id_socket;
		map_type_str_int64 map_nick_id;
		map_type_int64_str map_id_password;
		mm_uint64_t max_uid;

		pthread_mutex_t t_map;// 表锁
		mm_mailbox fw_net_0;
		//pthread_t poll_thread;
		//int state;// mm_thread_state_t,default is ts_closed(0)
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


	extern void fuwuqi_huidiao_dialogue_login(void* obj, void* u, struct mm_packet* pack);
	extern void fuwuqi_huidiao_dialogue_seek(void* obj, void* u, struct mm_packet* pack);
	extern void fuwuqi_huidiao_dialogue_talk(void* obj, void* u, struct mm_packet* pack);

	static void _static_fuwuqi_rec(struct mm_tcp* p_tcp,struct fuwuqi* p_fwq, struct mm_packet* rs_pack);





}

 


#endif  /* _INC_fuwuqi_h_ */