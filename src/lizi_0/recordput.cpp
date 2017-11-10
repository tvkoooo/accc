#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "recordput.h"
#include "juzhen.h"


void input_record_test()
{
	int i,j;
	struct bodytest_new testa;
	struct bodytest_new testb;

	strcpy(testa.bodytest_name,"love");
	testa.bodytest_age=19;

	for (i=0;i<4;i++)
	{
		testa.bodytest_score[i]=80;
	}

	for (i=0;i<2;i++)
		for (j=0;j<2;j++)
		{
			testa.bodytest_mima[i][j]=7;
		}

	FILE * fpin;
	//char chtest;
	char filenametest[10];
	printf("输入文件名");
	scanf("%s",filenametest);
	getchar();
	if ((fpin=fopen(filenametest,"w+"))==NULL)
	{
      printf("cannot open\n");
	  exit(0);
	}

	//printf("输入一个字符串，以#结束。");
	//chtest=getchar();	
	//while (chtest!='#')
	//{
	//	fputc(chtest,fp);
	//	putchar(chtest);
	//	chtest=getchar();
	//}
	//fprintf(fpin,"a:");


	//for (i=0;i<2;i++)
	//	for (j=0;j<2;j++)

	//{
	//	fprintf(fpin," %d ",a[i][j]);
	//}
	//	fprintf(fpin,"\n");


	//size_t sz = strlen(bbb);
	//fwrite(bbb,sizeof(char),sz,fpin);

	fwrite(&testa,sizeof(struct bodytest_new),1,fpin);
	printf("\ntesta shuju \n%s\t%d",testa.bodytest_name,testa.bodytest_age);

	fseek(fpin,0,SEEK_SET);
	fread(&testb,sizeof(struct bodytest_new),1,fpin);
	printf("\ntesta shuju \n%s\t%d",testb.bodytest_name,testb.bodytest_age);
	fclose(fpin);
	putchar(10);

}