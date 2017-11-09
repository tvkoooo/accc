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

    char m;
	struct dog a,b,c;

	dogname_init(&a);
	dogname_init(&b);	
	dogname_init(&c);	

	dogtalk_init(&a);
	dogtalk_init(&b);
	dogtalk_init(&c);

	do 
	{
		printf("\nWhich one would you like to choose?ÍË³öÇë°´n \n");
		printf("A:%s\nB:%s\nC:%s\n",(&a)->dog_name,(&b)->dog_name,(&c)->dog_name);

		scanf(" %c",&m);
		if(m=='n'||m=='N')
			break;
		if(m=='A'||m=='B'||m=='C'||m=='a'||m=='b'||m=='c')
			switch (m)
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


	} while (1);
	

	dogtalk_destroy(&a);	
	dogtalk_destroy(&b);
	dogtalk_destroy(&c);


	dogname_destroy(&a);
	dogname_destroy(&b);
	dogname_destroy(&c);
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


