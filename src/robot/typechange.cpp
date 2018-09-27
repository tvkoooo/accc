#include "typechange.h"



//vs Compiler
#ifdef _MSC_VER


#else


#endif

void string2int(int &int_temp,std::string &string_temp)  
{  
	std::stringstream stream(string_temp);  
	stream>>int_temp;  
}

void int2string(int &int_temp, std::string &string_temp)  
{  
	std::stringstream stream;  
	stream<<int_temp;  
	string_temp=stream.str();   //此处也可以用 stream>>string_temp  
}
void string2float(float &float_temp,std::string &string_temp) 
{  
	std::stringstream stream(string_temp);  
	stream>>float_temp;  
}

void float2string(float &float_temp,std::string &string_temp) 
{  
	std::stringstream stream;  
	stream<<float_temp;  
	string_temp=stream.str();   //此处也可以用 stream>>string_temp  
}
