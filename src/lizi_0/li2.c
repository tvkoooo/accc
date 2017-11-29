#include <tchar.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>

struct fff
{
	int acc0;
	int acc1;
	int acc2;
};

void li2()
{
	int i,j,k;
	int a[4][4]={{1,2,3,4,},{5,6,7,8},{9,10,11,12},{13,14,15,16}};
	int b[4][1]={1,3,5,7};
	int c[4][4]={0};
	int shuju=0;
	int linshi=0;
	int g = a[77][9];
	int abc0=0;//fuzhi gouzhao hanshu
	int abc1;// gouzhao hanshu
	//struct fff* zzz=NULL;
	//{
	//	struct fff zzz0;

	//	zzz=&zzz0;
	//	size_t a1 = sizeof(struct fff);
	//	size_t a2 = sizeof(struct fff*); 
	//	//zzz = NULL;
	//	//free(zzz);
	//}
	//struct fff* zzz1 = NULL;
	//{
	//	zzz1 = (struct fff*)malloc(sizeof(struct fff));
	//}


	abc0=0;//   fuzhi

	abc1=0;



	//{
	//	zzz->acc0 = 0;
	//}
	//{
	//	//free(zzz);
	//}


	//{
	//	zzz1->acc0 = 0;
	//}
	//{
	//	free(zzz1);
	//	zzz1 = NULL;
	//}

	//struct fff* zzz8=&zzz0;

	//(*zzz).acc = 0;
	//zzz->acc = 0;



	printf("a[4][4]=\n");
	for(i=0;i<4;i++) 
	{for(j=0;j<4;j++)

	{
		printf("%11d",a[i][j]);
	}
	printf("\n");
	}

	printf("b[4][1]=\n");
	for(i=0;i<4;i++) 
	{for(j=0;j<1;j++)

	{
		printf("%11d",b[i][j]);
	}
	printf("\n");
	}

	//juzhen function

	for(i=0;i<4;i++)
	{
		for(j=0;j<1;j++) 
		{for(k=0;k<4;k++)

		{

			linshi=a[i][k]*b[k][j];
			shuju=linshi+shuju;

		}
		c[i][j]=shuju;    

		}

	}


	printf("c[4][4]=\n");
	for(i=0;i<4;i++) 
	{for(j=0;j<4;j++)

	{
		printf("%11d",c[i][j]);
	}
	printf("\n");
	}

}