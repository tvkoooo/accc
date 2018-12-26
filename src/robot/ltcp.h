#ifndef _ltcp_h_
#define _ltcp_h_


//vs Compiler
#ifdef _MSC_VER
#include "platform_config.h"
#define LJ_INVALID_SOCKET INVALID_SOCKET
#else
#include <sys/socket.h>
#include <sys/types.h>
#define LJ_INVALID_SOCKET (-1)
#endif




struct ltcp_conn
{
	int fd;
	struct sockaddr_in fd_info;
	int nAddrlen;
};

extern void ltcp_conn_init(struct ltcp_conn *p);
extern void ltcp_conn_destroy(struct ltcp_conn *p);

extern int ltcp_conn_link(struct ltcp_conn *p ,const char* ip, int port );
extern int ltcp_conn_listen(struct ltcp_conn *p , const char* ip, int port ,int backlog = 5);

//timeval NULL Mean block;timeval {0,0} Mean Return immediately;A deadline is required.
extern int ltcp_conn_fd_timeout(struct ltcp_conn *p ,const struct timeval *t);
extern void ltcp_conn_accept(struct ltcp_conn *p_s ,struct ltcp_conn *p_c ,const struct timeval *t);


extern int ltcp_conn_send(struct ltcp_conn *p, const char *msg, int len);
extern int ltcp_conn_recv(struct ltcp_conn *p, char *buff, int len);
extern int ltcp_conn_shutdown(struct ltcp_conn *p, int how);
extern void ltcp_conn_close(struct ltcp_conn *p);

#endif  /* _ltcp_h_ */