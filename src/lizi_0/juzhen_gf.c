#include "juzhen_gf.h"
#include <stdio.h>
#include <stdlib.h>

//void juzhen4_gf_malloc(struct juzhen_gf *p,int num)
//{
//	p=(juzhen_gf*) malloc(sizeof(struct juzhen_gf)*num);
//}
//void juzhen4_gf_delloc(struct juzhen_gf *p)
//{
//	free(p);
//}

void juzhen4_gf_init(struct juzhen_gf *p,int num)
{
	int i,j,k;
	for (k=0;k<num;k++)
	{
		for (i=0;i<4;i++)
		{
			for (j=0;j<4;j++)
			{
				(p+k)->juzhen_gf1[i][j]=0;

			}
		}

	}
}
void juzhen4_gf_destory(struct juzhen_gf *p,int num)
{
	int i,j,k;
	for (k=0;k<num;k++)
	{
		for (i=0;i<4;i++)
		{
			for (j=0;j<4;j++)
			{
				(p+k)->juzhen_gf1[i][j]=0;

			}
		}

	}

}

void juzhen4_gf_do_assignment(struct juzhen_gf *p,int num)
{
	int i,j,k;
	float ru;
	for (k=0;k<num;k++)
	{
		printf(" ‰»Îæÿ’Û‘™Àÿ\n");
		scanf(" %f",&ru);

		for (i=0;i<4;i++)
		{
			for (j=0;j<4;j++)
			{
				
				
				(p+k)->juzhen_gf1[i][j]=ru;

			}
		}
		juzhen4_gf_do_print(p+k);
	}
}

void juzhen4_gf_do_2Multip2(struct juzhen_gf *pa,struct juzhen_gf *pb,struct juzhen_gf *pchu)
{
	int ii,jj,kk;
	for (ii=0;ii<4;ii++)
		for (jj=0;jj<4;jj++)
		{
			pchu->juzhen_gf1[ii][jj]=0;
			for (kk=0;kk<4;kk++)
			{
				pchu->juzhen_gf1[ii][jj]=pchu->juzhen_gf1[ii][jj]+pa->juzhen_gf1[ii][kk]*pb->juzhen_gf1[kk][jj];
			}

		}	
}
void juzhen4_gf_do_print(struct juzhen_gf *p)
{
	int i;
	printf("æÿ’Û[4][4]: \n");
	for (i=0;i<4;i++)
	{
		printf("%f\t%f\t%f\t%f\t\n",p->juzhen_gf1[i][0],p->juzhen_gf1[i][1],p->juzhen_gf1[i][2],p->juzhen_gf1[i][3]);
	}
}