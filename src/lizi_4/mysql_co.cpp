#include "mysql_co.h"

#include <iostream>
#include <fstream>
#include <cstdlib>
#include <string>


int db_close(MYSQL *mysql) 
{ 
mysql_close(mysql); 
return 0; 
}

void find_ps(MYSQL *mysql) 
{ 
	MYSQL_ROW m_row;
	MYSQL_RES *m_res;
	char sql[1024];

	sprintf(sql,"select user_money from t_userinfo where user_name='wocao'"); 

	if(mysql_query(mysql,sql) != 0) 
	{ 
		printf("Mysql 命令 语法错误: %s\n",mysql_error(mysql)); 
		return ; 
	} 
	m_res= mysql_store_result(mysql); 
	if(m_res==NULL) 
	{ 
		printf("Mysql 命令 未查询到结果: %s\n",mysql_error(mysql)); 
		return; 
	} 
	if(m_row = mysql_fetch_row(m_res)) 
	{ 
		printf("select user_money from t_userinfo where user_name='wocao'\n"); 
		printf("m_row=%d\n",atoi(m_row[0])); 
	} 
		printf("搜索结果为空\n"); 
	mysql_free_result(m_res); 
	return; 
}

void fun_mysql_test1()
{

MYSQL mysql;

char host[32]="localhost"; 
char user[32]="tvkoooo"; 
char passwd[32]="198766"; 
char dbname[32]="easydb";

if( mysql_init(&mysql) == NULL ) 
{ 
	printf("inital mysql handle error\n"); 
	return; 
}
if (mysql_real_connect(&mysql,host,user,passwd,dbname,0,NULL,0) == NULL) 
{ 
	printf("Failed to connect to database: Error: %s\n",mysql_error(&mysql)); 
	return; 
} 

else printf("connect to database: \n"); 
find_ps(&mysql); 
db_close(&mysql); 
return;


}

