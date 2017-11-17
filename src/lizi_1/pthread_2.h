#ifndef _INC_pthread_2
#define _INC_pthread_2
#include "lizhi1_all.h"

extern int flagctrlc;

struct lizhi1_2_pthr_s
{
	FILE * fpin;

	int  intin;
	pthread_mutex_t mymutex1;

	pthread_mutex_t all;
};

extern void lizhi1_2_pthr_s_init(struct lizhi1_2_pthr_s *p);
extern void lizhi1_2_pthr_s_destroy(struct lizhi1_2_pthr_s*p);
// 
extern void lizhi1_2_pthr_s_lock(struct lizhi1_2_pthr_s*p);
extern void lizhi1_2_pthr_s_unlock(struct lizhi1_2_pthr_s*p);


//  mymutex1
extern void lizhi1_2_pthr_s_f1(struct lizhi1_2_pthr_s*p);
//  mymutex1
extern void lizhi1_2_pthr_s_f2(struct lizhi1_2_pthr_s*p);


extern void lizhi_2_pthr_1();
extern void lizhi_2_pthr_2();
extern void pthread_lizhi1_2_test();


#endif  /* _INC_pthread_2 */