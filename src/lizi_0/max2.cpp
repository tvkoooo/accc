#include <tchar.h>
#include <stdio.h>
#include <exception>
#include <tou.h>


int sumn(int m,int *x,int *y)
	{
		//struct ff hb;
		li2();
		int i,sum1=0;
	    for(i=1;i<=m;i++)
		{
		sum1=sum1+i;
		if(i%2==0)
			*x=*x+1;
		else
			*y=*y+1;		
		}
		return(sum1);
	}



int shuijishu()
{

		int i,flag5;		
		for(i=0;i<146;i++)				
		flag5=rand()%6;
		return(flag5);
	
		
}





