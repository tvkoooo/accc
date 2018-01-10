#include "c_dialogue_baseinfo_data.h"

void c_dialogue_baseinfo_data_init(struct c_dialogue_baseinfo_data* p)
{
	p->enum_msg=0;
	p->error_state=0;
	p->error_desc="";
	p->user_id=0;
	p->user_nick="";
	p->user_password="";
	p->to_user_id=0;
	p->to_user_nick="";
	p->talking="";
	p->system_state=0;
	p->system_desc="";
}
void c_dialogue_baseinfo_data_destroy(struct c_dialogue_baseinfo_data* p)
{
	p->enum_msg=0;
	p->error_state=0;
	p->error_desc="";
	p->user_id=0;
	p->user_nick="";
	p->user_password="";
	p->to_user_id=0;
	p->to_user_nick="";
	p->talking="";
	p->system_state=0;
	p->system_desc="";
}

void c_dialogue_baseinfo_data_clear(struct c_dialogue_baseinfo_data* p)
{
	c_dialogue_baseinfo_data_init(p);
}