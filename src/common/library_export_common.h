#ifndef __library_export_common_h__
#define __library_export_common_h__


// windwos
#ifdef _MSC_VER

#  ifdef LIB_STATIC_COMMON
#    define LIB_EXPORT_COMMON
#    define LIB_IMPORT_COMMON
#  else
#    ifndef LIB_EXPORT_COMMON
#      ifdef LIB_SHARED_COMMON
/* We are building this library */
#        define LIB_EXPORT_COMMON __declspec(dllexport)
#      else
/* We are using this library */
#        define LIB_EXPORT_COMMON __declspec(dllimport)
#      endif
#    endif

#    ifndef LIB_PRIVATE_COMMON
#      define LIB_PRIVATE_COMMON 
#    endif
#  endif

#  ifndef LIB_DEPRECATED_COMMON
#    define LIB_DEPRECATED_COMMON __declspec(deprecated)
#  endif

#  ifndef LIB_DEPRECATED_EXPORT_COMMON
#    define LIB_DEPRECATED_EXPORT_COMMON LIB_EXPORT_COMMON LIB_DEPRECATED_COMMON
#  endif

#  ifndef LIB_DEPRECATED_PRIVATE_COMMON
#    define LIB_DEPRECATED_PRIVATE_COMMON LIB_PRIVATE_COMMON LIB_DEPRECATED_COMMON
#  endif

#else// unix

// Add -fvisibility=hidden to compiler options. With -fvisibility=hidden, you are telling
// GCC that every declaration not explicitly marked with a visibility attribute (MM_EXPORT)
// has a hidden visibility (like in windows).
#  ifdef LIB_STATIC_COMMON
#    define LIB_EXPORT_COMMON
#    define LIB_IMPORT_COMMON
#  else
#    ifndef LIB_EXPORT_COMMON
#      ifdef LIB_SHARED_COMMON
/* We are building this library */
#        define LIB_EXPORT_COMMON __attribute__ ((visibility("default")))
#      else
/* We are using this library */
#        define LIB_EXPORT_COMMON 
#      endif
#    endif

#    ifndef LIB_PRIVATE_COMMON
#      define LIB_PRIVATE_COMMON 
#    endif
#  endif

#  ifndef LIB_DEPRECATED_COMMON
#    define LIB_DEPRECATED_COMMON __attribute__ ((deprecated))
#  endif

#  ifndef LIB_DEPRECATED_EXPORT_COMMON
#    define LIB_DEPRECATED_EXPORT_COMMON LIB_EXPORT_COMMON LIB_DEPRECATED_COMMON
#  endif

#  ifndef LIB_DEPRECATED_PRIVATE_COMMON
#    define LIB_DEPRECATED_PRIVATE_COMMON LIB_PRIVATE_COMMON LIB_DEPRECATED_COMMON
#  endif

#endif

#endif//__library_export_common_h__