#include "transformation.h"
#include <stdlib.h>



//vs Compiler
#ifdef _MSC_VER


#else


#endif

int char2int(const char * str)
{
	return atoi(str);
}

double char2double(const char * str)
{
	return atof(str);
}

long char2long(const char * str)
{
	return atol(str);
}

double char2double000(const char * str, char** endptr)
{
	return strtod(str , endptr);
}

long int char2long000(const char* str, char** endptr, int base)
{
	return strtol(str,endptr,base);
}
unsigned long char2ulong000(const char* str, char** endptr, int base)
{
	return strtoul(str,endptr,base);
}