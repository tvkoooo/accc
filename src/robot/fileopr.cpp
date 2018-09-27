#include "fileopr.h"


//vs Compiler
#ifdef _MSC_VER
int check_dir(const char* path , int _AccessMode)
{
	return _access(path , _AccessMode);
}
int create_dir(const char *path)
{
	return _mkdir(path);
}
int rm_dir(const char *path)
{
	return _rmdir(path);
}


#else
int check_dir(const char* path , int _AccessMode)
{
	return access(path , _AccessMode);
}
int create_dir(const char *pathname, mode_t mode)
{
	return mkdir(pathname, mode);
}
int rm_dir(const char *path)
{
	return rmdir(path);
}

#endif