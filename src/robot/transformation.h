#ifndef _transformation_h_
#define _transformation_h_


//vs Compiler
#ifdef _MSC_VER


#else

#endif


//vs Compiler
#ifdef _MSC_VER

#else

#endif

extern int char2int(const char * str);
extern long char2long(const char * str);

//char szNumbers[] = "2001 60c0c0 -1101110100110100100000 0x6fffff";
//char * pEnd;
//long int li1, li2, li3, li4;
//li1 = char2long000 (szNumbers,&pEnd,10);
//li2 = char2long000 (pEnd,&pEnd,16);
//li3 = char2long000 (pEnd,&pEnd,2);
//li4 = char2long000 (pEnd,NULL,0);
//printf ("转换成10进制: %ld、%ld、%ld、%ld\n", li1, li2, li3, li4);
extern long int char2long000(const char* str, char** endptr, int base);
extern unsigned long char2ulong000(const char* str, char** endptr, int base);

extern double char2double(const char * str);

//char szOrbits[] = "365.24 29.53";
//char* pEnd;
//double d1, d2;
//d1 = char2double000 (szOrbits, &pEnd);
//d2 = char2double000 (pEnd, NULL);
extern double char2double000(const char * str, char** endptr);





#endif  /* _transformation_h_ */