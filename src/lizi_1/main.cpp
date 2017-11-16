//////////////////////////////////////////////////////////////////////////
#include "lizhi1_all.h"


#define use_vld_check_memory_leak
#if _DEBUG
#ifdef use_vld_check_memory_leak
#endif
#endif // _DEBUG

//////////////////////////////////////////////////////////////////////////


int main(int argc,char **argv)
{	
	//srand((int)time(0));
	//fprintf(stderr, "\n\n%s\t%d\n\n", __FILE__, __LINE__);

    pthread_lizhi1_test();
	return 0;
}

