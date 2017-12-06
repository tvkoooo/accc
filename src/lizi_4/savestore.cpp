#include <stdio.h>
#include "platform_config.h"
#include "mm_thread_state_t.h"
#include "socket_context_lizi4.h"
#include "savestore.h"
//
//static void huidiao( void* obj, int mes[5])
//{
//
//}
//extern const int PAGE_SIZE;

static void* static_savestore_wait_thread(void* arg)
{
	struct savestore* p = (struct savestore*)(arg);
	savestore_poll_wait(p);
	return NULL;
}

//void savestore_callback_init(struct savestore_callback* p)
//{
//	p->handle=huidiao;
//	p->obj=NULL;
//}
//void savestore_callback_destroy(struct savestore_callback* p)
//{
//	p->handle=huidiao;
//	p->obj=NULL;
//}


void savestore_init(struct savestore* p)
{
	//memset(p->mes,0,sizeof(int)*5);
	//savestore_callback_init(&p->callback);
	p->page_now=0;
	p->buffer_head=NULL;
	p->store_gptr=NULL;
	p->store_pptr=NULL;
	p->state = ts_closed;
//
	p->page_now=1;
	p->buffer_head=(char *)malloc(sizeof(char)*(PAGE_SIZE)*(p->page_now));
	p->store_gptr=p->buffer_head;
	p->store_pptr=p->buffer_head;
	p->state = ts_closed;

}

void savestore_destroy(struct savestore* p)
{
	//memset(p->mes,0,sizeof(int)*5);
	//savestore_callback_destroy(&p->callback);
	if (NULL!=p->buffer_head)
	{
		free(p->buffer_head);
	}
	else
	{
		printf("free error !");
	}

	p->page_now=0;
	p->buffer_head=NULL;
	p->store_gptr=NULL;
	p->store_pptr=NULL;
	p->state = ts_closed;
	p->state = ts_closed;
}

void savestore_move_data(struct savestore* p)
{
	int size_green=(p->store_pptr)-(p->store_gptr);
	memmove(p->buffer_head,p->store_gptr,size_green); 
	p->store_gptr=p->buffer_head;
	p->store_pptr=p->buffer_head+size_green;
}

void savestore_change_vec(struct savestore* p,int page_num)
{
	char *buffer_head_linshi=p->buffer_head;
	int size_green=(p->store_pptr)-(p->store_gptr);

	p->buffer_head=(char *)malloc(sizeof(char)*(PAGE_SIZE)*(page_num));
	memcpy(p->buffer_head,buffer_head_linshi,size_green);
	p->store_gptr=p->buffer_head;
	p->store_pptr=p->buffer_head+size_green;

	if (NULL!=buffer_head_linshi)
	{
		free(buffer_head_linshi);
	}
	else
	{
		printf("free error !");
	}
}

void savestore_cpydata(struct savestore* p,char *addr,int seize_o,int size_data)
{
	memcpy(p->store_pptr,addr+seize_o,size_data);
	p->store_pptr+=size_data;
}

void savestore_indata(struct savestore* p,char *addr,int seize_o,int size_data)
{
	int store_max_size=(PAGE_SIZE)*(p->page_now);
	int size_green=(p->store_pptr)-(p->store_gptr);
	int size_gray=(p->store_gptr)-(p->buffer_head);
	int size_blue=store_max_size-size_green-size_gray;
	if (size_data<=size_blue)
	{
		savestore_cpydata(p,addr,0,size_data);
	}
	else
	{
		if (size_blue<size_data&&size_data<=size_gray+size_blue)
		{
			savestore_move_data(p);
			savestore_cpydata(p,addr,0,size_data);
		}
		else 
		{		
			if (size_data>size_gray+size_blue)
			{
				p->page_now=(size_data+size_green)/PAGE_SIZE+1;
				savestore_change_vec(p,p->page_now);
				savestore_cpydata(p,addr,0,size_data);
			}

		}


	}

	store_max_size=(PAGE_SIZE)*(p->page_now);
	size_green=(p->store_pptr)-(p->store_gptr);
	size_gray=(p->store_gptr)-(p->buffer_head);
	size_blue=store_max_size-size_green-size_gray;
	printf("增加数据内容:\n");
	for (int i=0;i<size_data;i++)
	{	
		printf("%c",addr[i]);
	}
	printf("|\n");

	printf("数据槽内容:\n");
	for (int i=0;i<size_green;i++)
	{	
		printf("%c",p->buffer_head[size_gray+i]);
	}
	printf("|\n");
}
void savestore_outdata(struct savestore* p,char *addr,int seize_o,int size_data)
{

	int store_max_size=(PAGE_SIZE)*(p->page_now);
	int size_green=(p->store_pptr)-(p->store_gptr);
	int size_gray=(p->store_gptr)-(p->buffer_head);
	int size_blue=store_max_size-size_green-size_gray;

	if (size_gray>=PAGE_SIZE)
	{
		p->page_now=(size_green)/PAGE_SIZE+1;
		savestore_change_vec(p,p->page_now);
	}
	memcpy(addr,p->store_gptr,seize_o+size_data);
	p->store_gptr+=seize_o+size_data;


	store_max_size=(PAGE_SIZE)*(p->page_now);
	size_green=(p->store_pptr)-(p->store_gptr);
	size_gray=(p->store_gptr)-(p->buffer_head);
	size_blue=store_max_size-size_green-size_gray;
	printf("取出数据内容:\n");
	for (int i=0;i<size_data;i++)
	{	
		printf("%c",addr[i]);
	}
	printf("|\n");

	printf("数据槽内容:\n");
	for (int i=0;i<size_green;i++)
	{	
		printf("%c",p->buffer_head[size_gray+i]);
	}
	printf("|\n");

}



//void savestore_setcallback(struct savestore* p,struct savestore_callback* pp)
//{
//	p->callback=*pp;
//}

void savestore_poll_wait(struct savestore* p)
{
	//test
	//char t1[3]="wo";
	//char t2[6]="ai si";
	//char t3[2]="n";
	//char t4[12]="wo cao mmp";
	//char g1[3],g2[6],g3[2],g4[12];

	char mp[20],mo[20];
	struct intint_ out1,in1;
	memset(&mp,0,20);
	memset(&mo,0,20);
	memset(&in1,0,sizeof(struct intint_));
	memset(&out1,0,sizeof(struct intint_));

	in1.a=0x12345678;
	in1.b=0x78563412;

	memcpy(mp,&in1.a,4);
	memcpy(mp+4,&in1.b,4);
	
	printf("存入数据内容:\n");
	
		printf("0x%08x\n",in1.a);
		printf("0x%08x\n",in1.b);

	printf("|\n");

	savestore_indata(p,mp,0,8);
	savestore_outdata(p,mo,0,8);

	memcpy(&out1.a,mo,4);
	memcpy(&out1.b,mo+4,4);

	printf("取出数据内容:\n");

	printf("0x%08x\n",out1.a);
	printf("0x%08x\n",out1.b);

	printf("|\n");

	//test
	//savestore_indata(p,t1,0,3);
	//savestore_indata(p,t2,0,6);
	//savestore_outdata(p,g1,0,3);
	//savestore_indata(p,t3,0,2);
	//savestore_outdata(p,g2,0,6);
	//savestore_indata(p,t4,0,12);
	//savestore_outdata(p,g3,0,2);
	//savestore_outdata(p,g4,0,12);

	while( ts_motion == p->state )
	{
		printf("xun huan dangzhong");
		Sleep(3000);
	}
}

void savestore_start(struct savestore* p)
{
	p->state = ts_finish == p->state ? ts_closed : ts_motion;
	pthread_create(&p->poll_thread, NULL, &static_savestore_wait_thread, p);
}


void savestore_interrupt(struct savestore* p)
{
		p->state = ts_closed;
}
void savestore_shutdown(struct savestore* p)
{
		p->state = ts_finish;
}
void savestore_join(struct savestore* p)
{
		pthread_join(p->poll_thread, NULL);
}




