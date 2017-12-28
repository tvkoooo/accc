
#include "redis_connect.h"

static void fun_redis_reply(redisReply* reply)
{
	if (NULL!=reply->str)
	{
		printf("操作内容长度:%d \t",reply->len);
		printf("操作内容类型:%d \t",reply->type);
		printf("操作内容:%s \n",reply->str);

	}

	if (NULL==reply->str)
	{
		if (0==reply->integer)
		{
			printf("操作的下级内容:%d行数据\n",reply->elements);
			for (unsigned int i=0;i<reply->elements;i++)
			{	
				printf("第%d行长度:%d \t",i+1,(*(reply->element+i))->len);
				printf("第%d行类型:%d \t",i+1,(*(reply->element+i))->type);
				printf("第%d行内容:%s \n",i+1,(*(reply->element+i))->str);
			}
		}
		else
		{

		}

	}

}


void fun_redis()
{


	socket_context_init();


	redisContext *c = redisConnect("101.200.169.28", 50500);
	if (c == NULL || c->err)
	{
		int err = pp_errno();
		printf(" error %s",errnomber(err));  
		if (c) 
		{
			printf("Error: %s\n", c->errstr);
			// handle error
		} 
		else
		{
			printf("Can't allocate redis context\n");
		}
	}

	char* command1 = "del lj_mttt";
	redisReply* reply = (redisReply*)redisCommand(c, command1);    // 执行命令，结果强转成redisReply*类型  
	if( NULL == reply)  
	{  
		printf("Execut command1 failure\n"); 
		redisFree(c);
		socket_context_destroy();
		return;
	}


	fun_redis_reply(reply);
	freeReplyObject(reply);  
	printf("正确处理命令[%s]\n", command1);  
	// 一切正常，则对返回值进行处理
	redisFree(c);
	socket_context_destroy();
}



