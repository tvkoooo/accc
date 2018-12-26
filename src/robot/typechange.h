#ifndef _typechange_h_
#define _typechange_h_
#include <sstream>
#include <iostream>
#include <string>

//vs Compiler
#ifdef _MSC_VER


#else

#endif


//vs Compiler
#ifdef _MSC_VER

#else

#endif

extern void string2int(int &int_temp,std::string &string_temp);
extern void int2string(int &int_temp,std::string &string_temp);
extern void string2float(float &float_temp,std::string &string_temp);
extern void float2string(float &float_temp,std::string &string_temp);


#endif  /* _typechange_h_ */