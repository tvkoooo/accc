#include "lmaths.h"



int get_int_rand(int max)
{
	srand((int)time(0));
	return rand() % max ;
}

int get_int_between(int min , int max)
{
	srand((int)time(0));
	return (rand() % (max - min) +  min);
}

float get_float_between(float min , float max)
{
	srand((int)time(0));
	return (float)((((float)rand() / max ) *(max - min) +  min));
}

std::string  Get_Current_Date()
{
	time_t nowtime;  
	nowtime = time(NULL); //获取日历时间   
	char tmp[64];   
	strftime(tmp,sizeof(tmp),"%Y_%m_%d",localtime(&nowtime));   
	return tmp;
}

std::string  Get_Current_Date_time()
{
	time_t nowtime;  
	nowtime = time(NULL); //获取日历时间   
	char tmp[64];   
	strftime(tmp,sizeof(tmp),"%Y_%m_%d_%H_%M_%S",localtime(&nowtime));   
	return tmp;
}