projectname = main_0
objects = main.o libmyfun_math.a myfun_math.o
$(projectname) : $(objects)
	cc -o $(projectname) main.o -L. -lmyfun_math
main.o : main.cpp 
	cc -c main.cpp
libmyfun_math.a : myfun_math.c 	
	cc -c myfun_math.c
	ar cr libmyfun_math.a myfun_math.o
.PHONY : clean
clean :
	rm -rf $(projectname) $(objects)
	