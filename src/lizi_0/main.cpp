//////////////////////////////////////////////////////////////////////////
#include <tchar.h>
#include <stdio.h>
#include <exception>
//#include <tou.h>
//#include "dog.h"
//#include "juzhen.h"
//#include "huidiao.h"
//#include "recordput.h"
//#include "shuchurizhi.h"
#include "vectordog.h"


//#include <max2.cpp>
//#include <li1.cpp>
//#include <li2.cpp>
//#include <li3.cpp>

#define use_vld_check_memory_leak
#if _DEBUG
#ifdef use_vld_check_memory_leak
#include <vld.h>
#endif
#endif // _DEBUG
#include <time.h>

//////////////////////////////////////////////////////////////////////////

int main(int argc,char **argv)
{
	srand((int)time(0));
	//int i=0;
	//int eee;
	//for(i=0;i<10;i++)
	//{	
	//	{
	//		eee=shuijishu()%21;
	//		printf("%d\n",eee);
	//	}
	//}

   fun__vectordog_new_test();
//input_record_test();
	//li3();
	//li5();
    //huidiao_a();
	//juzhen4_chen_4juzhen();
	////sumscan();   //li1.app  function
 ////   li2();    //li2.app  function
	//
	//struct ff hb;
	return 0;
}

int shuijishu()
{
	return rand();
	//int i,flag5;		
	//for(i=0;i<335;i++)				
	//	flag5=rand();
	//return(flag5);


}
