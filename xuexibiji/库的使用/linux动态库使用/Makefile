projectname = main_1
objects = main.o libmyfun_math.so myfun_math.o
$(projectname) : $(objects)
	cc -o $(projectname) main.o -L. libmyfun_math.so
main.o : main.cpp 
	cc -c main.cpp
libmyfun_math.so : myfun_math.c
	cc -fpic -c myfun_math.c
	cc -fpic -shared -o libmyfun_math.so myfun_math.o
.PHONY : clean
clean :
	rm -rf $(projectname) $(objects)
	