#ifndef __socket_context_h__
#define __socket_context_h__

#include <stdint.h>
#include "library_export_common.h"

#include "c_start.h"


//vs Compiler
#ifdef _MSC_VER
#	define lj_byteswap_16 _byteswap_ushort
#	define lj_byteswap_32 _byteswap_ulong
#	define lj_byteswap_64 _byteswap_uint64

#else
#	define lj_byteswap_16 bswap_16
#	define lj_byteswap_32 bswap_32
#	define lj_byteswap_64 bswap_64
#endif


//default model:Little Endian Machine
#ifdef __BIG_ENDIAN_MACHINE__
#	define little_endian_unit8(u8 )  u8
#	define little_endian_unit16(u16) lj_byteswap_16
#	define little_endian_unit32(u32) lj_byteswap_32
#	define little_endian_unit64(u64) lj_byteswap_64
#	define big_endian_unit8(u8 )  u8
#	define big_endian_unit16(u16) u16
#	define big_endian_unit32(u32) u32
#	define big_endian_unit64(u64) u64
#else

#	define little_endian_unit8(u8 )  u8
#	define little_endian_unit16(u16) u16
#	define little_endian_unit32(u32) u32
#	define little_endian_unit64(u64) u64
#	define big_endian_unit8(u8 )  u8
#	define big_endian_unit16(u16) lj_byteswap_16(u16)
#	define big_endian_unit32(u32) lj_byteswap_32(u32)
#	define big_endian_unit64(u64) lj_byteswap_64(u64)
#endif



#include "c_end.h"
#endif  /* __socket_context_h__ */