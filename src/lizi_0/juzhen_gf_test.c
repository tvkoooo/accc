#include "juzhen_gf.h"
#include <stdio.h>
#include <stdlib.h>

void juzhen4_gf_test()
{
///////////////////////////////////////////////////////////////////////////////////////
	int i,j,k,n=0;
	struct juzhen_gf aaa,bbb,oushu,jishu,suoyou,*juzhen4_cre;
///////////////////////////////////////////////////////////////////////////////////////
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
			/////////////////////////////////////////////////////////////////////////////////////

	        juzhen4_cre=(struct juzhen_gf*) malloc(sizeof(struct juzhen_gf)*n);
			/////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////////////////////////


			juzhen4_gf_init(juzhen4_cre,n);
			juzhen4_gf_init(&aaa,1);
			juzhen4_gf_init(&bbb,1);
			juzhen4_gf_init(&oushu,1);
			juzhen4_gf_init(&jishu,1);
			juzhen4_gf_init(&suoyou,1);

			juzhen4_gf_do_assignment(juzhen4_cre,n);

			/////////////////////////////////////////////////////////////////////////////////////
			//所有连乘//////////////////////////////////////////////////////////////////////////
			suoyou=juzhen4_cre[0];
			for(i=0;i<n-1;i++)
			{
				aaa=suoyou;
				bbb=juzhen4_cre[i+1];
				juzhen4_gf_do_2Multip2(&aaa,&bbb,&suoyou);
			}
			printf("矩阵连乘结果");
			juzhen4_gf_do_print(&suoyou);

			/////////////////////////////////////////////////////////////////////////////////////
			//奇数连乘////////////////////////////////////////////////////////////////////////////
			if (n>2)
			{
				jishu=juzhen4_cre[0];
				for(j=0;j<n-2;)
				{
					aaa=jishu;
					j=j+2;
					bbb=juzhen4_cre[j];
					juzhen4_gf_do_2Multip2(&aaa,&bbb,&jishu);
				}	
				printf("矩阵奇数项连乘结果");
				juzhen4_gf_do_print(&jishu);
			}			
			///////////////////////////////////////////////////////////////////////////////////////
			//偶数连乘////////////////////////////////////////////////////////////////////////////
			if (n>3)
			{
				oushu=juzhen4_cre[1];
				for(k=1;k<n-2;)
				{
					aaa=oushu;
					k=k+2;
					bbb=juzhen4_cre[k];
					juzhen4_gf_do_2Multip2(&aaa,&bbb,&oushu);
				}	
				printf("矩阵偶数项连乘结果");
				juzhen4_gf_do_print(&oushu);
			}	
	
			//////////////////////////////////////////////////////////////////////////////////////
			//////////////////////////////////////////////////////////////////////////////////////

			juzhen4_gf_destory(juzhen4_cre,n);
			juzhen4_gf_destory(&aaa,1);
			juzhen4_gf_destory(&bbb,1);
			juzhen4_gf_destory(&oushu,1);
			juzhen4_gf_destory(&jishu,1);
			juzhen4_gf_destory(&suoyou,1);

			//////////////////////////////////////////////////////////////////////////////////////
			//////////////////////////////////////////////////////////////////////////////////////
	        free(juzhen4_cre);

			//////////////////////////////////////////////////////////////////////////////////////
		} 
		else
		{
			printf("输入4X4 矩阵的个数不够，请重新输入\n");
		}
		///////////////////////////////////////////////////////////////////////////////////////////
	} 
}

