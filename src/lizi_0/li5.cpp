#include <tchar.h>
#include <stdio.h>
#include <stdlib.h>
#include <dog.h>
//
//void func(int* p)
//{
//
//}
//
//void func3(float ppp[4][4])
//{
//
//}
//
//
//void func2(struct juzhen4 * p1)
//{
//	func3(p1->b);
//}
//


void li5()
{
	char i;
	struct dog a,b,c;
	dogname_init(&a);
	dogname_init(&b);	
	dogname_init(&c);	

	printf("Which one would you like to choose? ");
	printf("A:%d\nB:%d\nC:%d\n ",(&a)->dog_name,(&b)->dog_name,(&c)->dog_name);
	scanf("%d",&i);
	if(i=='A'||i=='B'||i=='C'||i=='a'||i=='b'||i=='c')
	switch (i)
	{
	case 'A':
	case 'a':dog_talk(&a);break;

	case 'B':
	case 'b':dog_talk(&b);break;

	case 'C':
	case 'c':dog_talk(&c);break;

	default:
		break;
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//{
	//	int A;
	//	func(&A);
	//}
	//{
	//	struct juzhen4 A;
	//	init(&A);
	//	A.b[3][2]=0;
	//}
	//struct juzhen4 *p=NULL;

	//p->a=

	//scanfjuzhen(p->b);
	//printjuzhen(p);

	//scanfjuzhen(p);
	//printjuzhen(p);

	//fjujia(p);

}


