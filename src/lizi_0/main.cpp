//////////////////////////////////////////////////////////////////////////
#include <all.h>

#define use_vld_check_memory_leak
#if _DEBUG
#ifdef use_vld_check_memory_leak
#include <vld.h>
#endif
#endif // _DEBUG


//////////////////////////////////////////////////////////////////////////


int main(int argc,char **argv)
{
	srand((int)time(0));

	fprintf(stderr, "\n\n%s\t%d\n\n", __FILE__, __LINE__);

    juzhen4_gf_test();
	//int i=0;
	//int eee;
	//for(i=0;i<10;i++)
	//{	
	//	{
	//		eee=shuijishu()%21;
	//		printf("%d\n",eee);
	//	}
	//}

      

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
