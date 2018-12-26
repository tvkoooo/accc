#ifndef _INC_platform
#define _INC_platform

#if !defined(myfun_static_lib)
#  if defined(myfun_build)
#    define add_dllport __declspec (dllexport)
#  else
#    define add_dllport __declspec (dllimport)
#  endif
#else
#  define add_dllport
#endif


#endif  /* _INC_platform */