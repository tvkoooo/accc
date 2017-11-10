#include <stdio.h>
#include <stdlib.h>
#include "juzhen.h"

void juzhen4_chen_4juzhen()
{
	int i,j,k,n=0;
	struct chen_juzhen4 a,b,jishu,oushu,suoyou;
	while (1)
	{
		printf("输入4X4 矩阵的个数n,退出请输入666.\n");
		scanf(" %d",&n);
		if (n==666)
		{
			break;
		}
///////////////////////////////////////////////////////////////////////////////////////
		if (n>1)
		{
			struct chen_juzhen4 *junzhen_create;
			junzhen_create=(chen_juzhen4*) malloc(sizeof(struct chen_juzhen4)*n);
			for (i=0;i<n;i++)
			{	
				juzhen4_shuru(&junzhen_create[i]);
				printf("输入");
				juzhen4_printf(&junzhen_create[i]);

			}
/////////////////////////////////////////////////////////////////////////////////////
			suoyou=junzhen_create[0];
			for(i=0;i<n-1;i++)
			{
				a=suoyou;
				b=junzhen_create[i+1];
	     		juzhen4_dianchen(&a,&b,&suoyou);
			}	
			printf("矩阵连乘结果");
				juzhen4_printf(&suoyou);
///////////////////////////////////////////////////////////////////////////////////////
			if (n>2)
			{
			jishu=junzhen_create[0];
			for(j=0;j<n-2;)
			{
				a=jishu;
				j=j+2;
				b=junzhen_create[j];
				juzhen4_dianchen(&a,&b,&jishu);
			}	
			printf("矩阵奇数项连乘结果");
			juzhen4_printf(&jishu);
			}			
///////////////////////////////////////////////////////////////////////////////////////
			if (n>3)
			{
				oushu=junzhen_create[1];
				for(k=1;k<n-2;)
				{
					a=oushu;
					k=k+2;
					b=junzhen_create[k];
					juzhen4_dianchen(&a,&b,&oushu);
				}	
				printf("矩阵偶数项连乘结果");
				juzhen4_printf(&oushu);
			}	
			free(junzhen_create);		
//////////////////////////////////////////////////////////////////////////////////////
		} 
		else
		{
			printf("输入4X4 矩阵的个数不够，请重新输入\n");
		}
///////////////////////////////////////////////////////////////////////////////////////////

	} ;
}

void juzhen4_shuru(struct chen_juzhen4 *p)
{
		int iii,jjj;
		float ru=0;
	printf("输入矩阵元素\n");
	scanf(" %f",&ru);
	for (iii=0;iii<4;iii++)
		for (jjj=0;jjj<4;jjj++)

		{
			p->juzhen4[iii][jjj]=ru;
		}
}


void juzhen4_dianchen(struct chen_juzhen4 *pa,struct chen_juzhen4 *pb,struct chen_juzhen4 *pchu)
{
	int ii,jj,kk;
	for (ii=0;ii<4;ii++)
		for (jj=0;jj<4;jj++)
		{
			pchu->juzhen4[ii][jj]=0;
		    for (kk=0;kk<4;kk++)
		{
			pchu->juzhen4[ii][jj]=pchu->juzhen4[ii][jj]+pa->juzhen4[ii][kk]*pb->juzhen4[kk][jj];
		}
			
		}
}

void juzhen4_printf(struct chen_juzhen4 *p)
{
	int i;
	printf("矩阵[4][4]: \n");
	for (i=0;i<4;i++)
		{
			printf("%f\t%f\t%f\t%f\t\n",p->juzhen4[i][0],p->juzhen4[i][1],p->juzhen4[i][2],p->juzhen4[i][3]);
		}
}
