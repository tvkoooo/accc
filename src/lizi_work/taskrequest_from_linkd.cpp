#include "taskrequest_from_linkd.h"
#include "core/mm_os_context.h"
#include "net/mm_streambuf_packet.h"
#include "net/mm_default_handle.h"

#include <string>


void __static_timeer_0(struct mm_timer_heap* timer_heap, struct mm_timer_entry* entry)
{
	taskrequest_from_linkd* p = (taskrequest_from_linkd*)entry->callback.obj;

	mm_modules_runtime_update_runtime(&p->runtime_0);
	
}
void __static_timeer_1(struct mm_timer_heap* timer_heap, struct mm_timer_entry* entry)
{
	taskrequest_from_linkd* p = (taskrequest_from_linkd*)entry->callback.obj;
	
	mm_modules_runtime_commit_zk(&p->runtime_0);
}

void taskrequest_from_linkd_init(struct taskrequest_from_linkd* p)
{
	char path[64] = {0};
	struct mm_mailbox_callback mm_mailbox_callback;

	mm_modules_runtime_init(&p->runtime_0);
	mm_mailbox_init(&p->mailbox_0);
	mm_timer_init(&p->timer_0);

	mm_mailbox_callback_init(&mm_mailbox_callback);
	mm_mailbox_callback.handle=&mm_mailbox_handle_default;
	mm_mailbox_callback.broken=&mm_mailbox_broken_default;
	mm_mailbox_callback.finish=&mm_mailbox_nready_default;
	mm_mailbox_callback.nready=&mm_mailbox_finish_default;
	mm_mailbox_callback.obj=p;
	mm_mailbox_assign_mailbox_callback(&p->mailbox_0,&mm_mailbox_callback);
	mm_mailbox_callback_destroy(&mm_mailbox_callback);

	//初始化计算器//////////////////////////////////////////////////////////////////
	mm_timer_schedule(&p->timer_0, 10, 5000, &__static_timeer_0, p);
	mm_timer_schedule(&p->timer_0, 10, 5000, &__static_timeer_1, p);
	//
	mm_modules_runtime_assign_internal_mailbox(&p->runtime_0,&p->mailbox_0);
	//
	mm_modules_runtime_module_path(TASKREQUEST_FROM_LINKD_MODEL_NUMBER, path);
	mm_modules_runtime_assign_zkwp_path(&p->runtime_0,path);
}
void taskrequest_from_linkd_destroy(struct taskrequest_from_linkd* p)
{
	mm_timer_destroy(&p->timer_0);
	mm_mailbox_destroy(&p->mailbox_0);
	mm_modules_runtime_destroy(&p->runtime_0);
}
///////////////////////////////////////////////////
void taskrequest_from_linkd_set_instance_number(struct taskrequest_from_linkd* p,mm_uint32_t instance_number)
{
	mm_modules_runtime_assign_unique_id(&p->runtime_0,instance_number);
	mm_modules_runtime_assign_zkwp_slot(&p->runtime_0,0,instance_number);
}
void taskrequest_from_linkd_set_zk_host_port(struct taskrequest_from_linkd* p,const std::string& zk_host_port)
{
	mm_modules_runtime_assign_zkwp_host(&p->runtime_0,zk_host_port.c_str());
}
void taskrequest_from_linkd_set_task_host_port(struct taskrequest_from_linkd* p,const std::string& task_host_port)
{
	mm_mailbox_assign_parameters(&p->mailbox_0,task_host_port.c_str());
}

void taskrequest_from_linkd_start(struct taskrequest_from_linkd* p)
{	
	mm_mailbox_fopen_socket(&p->mailbox_0);
	mm_mailbox_bind(&p->mailbox_0);
	mm_mailbox_listen(&p->mailbox_0);
	mm_mailbox_start(&p->mailbox_0);
	mm_modules_runtime_start(&p->runtime_0);
	mm_timer_start(&p->timer_0);
}

void taskrequest_from_linkd_interrupt(struct taskrequest_from_linkd* p)
{

	mm_mailbox_interrupt(&p->mailbox_0);
	mm_modules_runtime_interrupt(&p->runtime_0);
	mm_timer_interrupt(&p->timer_0);
}
void taskrequest_from_linkd_shutdown(struct taskrequest_from_linkd* p)
{

	mm_mailbox_shutdown(&p->mailbox_0);
	mm_modules_runtime_shutdown(&p->runtime_0);
	mm_timer_shutdown(&p->timer_0);
}
void taskrequest_from_linkd_join(struct taskrequest_from_linkd* p)
{
	mm_mailbox_join(&p->mailbox_0);
	mm_modules_runtime_join(&p->runtime_0);
	mm_timer_join(&p->timer_0);
}


