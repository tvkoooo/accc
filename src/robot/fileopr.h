#ifndef _fileopr_h_
#define _fileopr_h_

//vs Compiler
#ifdef _MSC_VER
#include <io.h>
#include <direct.h>

#else
#include <unistd.h>
#include <dirent.h>
#include <sys/types.h>
#include <sys/stat.h>
#endif

extern int check_dir(const char* path , int _AccessMode);
extern int rm_dir(const char *path);
//vs Compiler
#ifdef _MSC_VER
extern int create_dir(const char *path);
#else
extern int create_dir(const char *pathname, mode_t mode);
#endif

#endif  /* _fileopr_h_ */