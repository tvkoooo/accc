#ifndef _lmaths_h_
#define _lmaths_h_


#include <time.h>
#include <stdlib.h>
#include <string>



extern int get_int_rand(int max);
extern int get_int_between(int min , int max);
extern float get_float_between(float min , float max);
extern std::string  Get_Current_Date();
extern std::string  Get_Current_Date_time();
#endif  /* _lmaths_h_ */