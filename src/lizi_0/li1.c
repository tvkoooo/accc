#include <tchar.h>
#include <stdio.h>

#include "tou.h"
void sumscan()
{
int n;
	int xx=0;
	int yy=0;
	int sum2=0;
	//int sumn(int n,int *x,int *y);
	printf("n=\n");
	scanf("%d",&n);
	sum2=sumn(n,&xx,&yy);

	printf("sumn=%8d\n",sum2);
	printf("oushu=%8d\n",xx);
	printf("jishu=%8d\n",yy);
		

}
