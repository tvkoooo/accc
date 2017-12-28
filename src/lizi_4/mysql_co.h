#ifndef _INC_mysql_co
#define _INC_mysql_co
#include "socket_context_lizi4.h"
#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "mysql.h"



extern int db_close(MYSQL *mysql);

extern void fun_mysql_test1();

extern void find_ps(MYSQL *mysql);

//extern void wangluo_fw_sel_init(struct wangluo_fw_sel* p);
//extern void wangluo_fw_sel_destroy(struct wangluo_fw_sel* p);
////
//extern void wangluo_fw_sel_shujuchuandi(struct wangluo_fw_sel *p,socket_type sClient);
//
//extern void wangluo_fw_sel_poll_wait(struct wangluo_fw_sel* p);
////
//extern void wangluo_fw_sel_start(struct wangluo_fw_sel* p);
//extern void wangluo_fw_sel_interrupt(struct wangluo_fw_sel* p);
//extern void wangluo_fw_sel_shutdown(struct wangluo_fw_sel* p);
//extern void wangluo_fw_sel_join(struct wangluo_fw_sel* p);


#endif//_INC_mysql_co