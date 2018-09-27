#include "ltcp.h"
#include "llog.h"
#include "socket_context.h"




//vs Compiler
#ifdef _MSC_VER
void ltcp_conn_close(struct ltcp_conn *p)
{
	closesocket(p->fd);
}
#else
void ltcp_conn_close(struct ltcp_conn *p)
{
	close(p->fd);
}
#endif

void ltcp_conn_init(struct ltcp_conn *p)
{
	socket_context_init();
	SOCKET gfd;
	gfd = socket(AF_INET,SOCK_STREAM,IPPROTO_TCP);
	llog_instance(log);
	llog_Debug(log,"Get socket fd:%d \n",gfd);
	p->fd = gfd;
	if ( p->fd <= 0)
	{
		llog_Warn(log,"%s %d %s ::Get socket fd:%d Fail \n",__FILE__,__LINE__,__FUNCTION__,p->fd);
	}
	memset(&p->fd_info, 0 , sizeof(struct sockaddr_in));
	p->nAddrlen = 0;
}

void ltcp_conn_destroy(struct ltcp_conn *p)
{
	p->fd = LJ_INVALID_SOCKET;
	memset(&p->fd_info, 0 , sizeof(struct sockaddr_in));
	p->nAddrlen = 0;
	socket_context_destroy();
}


int ltcp_conn_link(struct ltcp_conn *p ,const char* ip, int port )
{
	p->fd_info.sin_family = AF_INET;//set family
	p->fd_info.sin_port = htons(port);  //set port
	p->fd_info.sin_addr.s_addr = inet_addr(ip);  //set address
	p->nAddrlen = sizeof(struct sockaddr);
	return connect(p->fd,(struct sockaddr *)&p->fd_info,p->nAddrlen);//connect
}

int ltcp_conn_listen(struct ltcp_conn *p , const char* ip, int port ,int backlog)
{
	llog_instance(log);
	int err_monitor;
	p->fd_info.sin_family = AF_INET;//set family
	p->fd_info.sin_port = htons(port);  //set port
	p->fd_info.sin_addr.s_addr = inet_addr(ip);  //set address
	p->nAddrlen = sizeof(struct sockaddr);
	do 
	{
		err_monitor = bind(p->fd,(struct sockaddr *)&p->fd_info,p->nAddrlen);//bind
		if (-1 == err_monitor)
		{
			llog_Warn(log,"%s %d %s ::socket bind fd:%d error. \n",__FILE__,__LINE__,__FUNCTION__,p->fd);
			break;
		}
		err_monitor = listen(p->fd,backlog);//set listen
		{
			llog_Warn(log,"%s %d %s ::socket listen fd:%d error. \n",__FILE__,__LINE__,__FUNCTION__,p->fd);
		}
	} while (0);
	return err_monitor;
}

int ltcp_conn_fd_timeout(struct ltcp_conn *p ,const struct timeval *t)
{
	fd_set fds;
	FD_ZERO(&fds);
	FD_SET(p->fd,&fds);
	int err_sel = select(p->fd + 1,&fds,0,0,t);
	if (err_sel < 0)
	{
		llog_W("%s %d %s ::socket select fd:%d error. \n",__FILE__,__LINE__,__FUNCTION__,p->fd);
	} 
	return err_sel;
}


void ltcp_conn_accept(struct ltcp_conn *p_s ,struct ltcp_conn *p_c ,const struct timeval *t)
{
	if (ltcp_conn_fd_timeout(p_s,t) > 0)
	{
		p_c->fd = accept(p_s->fd,(struct sockaddr *)&p_c->fd_info,&p_c->nAddrlen);
	}
}

int ltcp_conn_send(struct ltcp_conn *p, const char *msg, int len)
{
	return send(p->fd, msg,  len, 0);
}

int ltcp_conn_recv(struct ltcp_conn *p, char *buff, int len)
{
	return recv(p->fd, buff,  len, 0);
}

int ltcp_conn_shutdown(struct ltcp_conn *p, int how)
{
	return shutdown(p->fd,how);
}

