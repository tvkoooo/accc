
#ifndef _INC_c_dialogue_baseinfo_data
#define _INC_c_dialogue_baseinfo_data
#include "core\mm_types.h"
#include <string>



struct c_dialogue_baseinfo_data
{
	mm_uint32_t enum_msg;
	mm_uint32_t error_state;
	std::string error_desc;
	mm_uint64_t user_id;
	std::string user_nick;
	std::string user_password;
	mm_uint64_t to_user_id;
	std::string to_user_nick;
	std::string talking;
	mm_uint32_t system_state;
	std::string system_desc;	
};

extern void c_dialogue_baseinfo_data_init(struct c_dialogue_baseinfo_data* p);
extern void c_dialogue_baseinfo_data_destroy(struct c_dialogue_baseinfo_data* p);
extern void c_dialogue_baseinfo_data_clear(struct c_dialogue_baseinfo_data* p);
///////////////////////////////////////////////////


#endif  /* _INC_c_dialogue_baseinfo_data */