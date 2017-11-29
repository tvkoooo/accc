//////////////////////////////////////////////////////////////////////////
#include <stdio.h>
#include <stdlib.h>
//#include <windows.h>
#include "fun_log_dll.h"
//#include "iostream"

myfun_dllport void fun_log_fprintf(char *adr)
{
	FILE * fpin;
	char filenametest[20]="log.txt";
	if ((fpin=fopen(filenametest,"w+"))==NULL)
	{
		printf("cannot open\n");
		exit(0);
	}

	//char kkkk[100];
	//sprintf(kkkk,"全局变量Quanju_lizhi1_pth ： %d\n",a->intin);
	//fputs(kkkk,a->fpin);
	fprintf(fpin,"输出日志： %s\n",adr);
	fclose(fpin);
}
