#ifndef __mm_archive_endian_h__
#define __mm_archive_endian_h__

#include "core/mm_core.h"
#include "core/mm_streambuf.h"
#include "core/mm_byteswap.h"

#include <string>

namespace mm
{
	class mm_archive_byte_order_little
	{
	public:
		static mm_inline mm_uint16_t endian_encode_16(const mm_uint16_t& v) { return mm_htol16(v); }
		static mm_inline mm_uint16_t endian_decode_16(const mm_uint16_t& v) { return mm_ltoh16(v); }
		static mm_inline mm_uint32_t endian_encode_32(const mm_uint32_t& v) { return mm_htol32(v); }
		static mm_inline mm_uint32_t endian_decode_32(const mm_uint32_t& v) { return mm_ltoh32(v); }
		static mm_inline mm_uint64_t endian_encode_64(const mm_uint64_t& v) { return mm_htol64(v); }
		static mm_inline mm_uint64_t endian_decode_64(const mm_uint64_t& v) { return mm_ltoh64(v); }
	};
	class mm_archive_byte_order_bigger
	{
	public:
		static mm_inline mm_uint16_t endian_encode_16(const mm_uint16_t& v) { return mm_htob16(v); }
		static mm_inline mm_uint16_t endian_decode_16(const mm_uint16_t& v) { return mm_btoh16(v); }
		static mm_inline mm_uint32_t endian_encode_32(const mm_uint32_t& v) { return mm_htob32(v); }
		static mm_inline mm_uint32_t endian_decode_32(const mm_uint32_t& v) { return mm_btoh32(v); }
		static mm_inline mm_uint64_t endian_encode_64(const mm_uint64_t& v) { return mm_htob64(v); }
		static mm_inline mm_uint64_t endian_decode_64(const mm_uint64_t& v) { return mm_btoh64(v); }
	};

	template<typename archive_byte_order = mm_archive_byte_order_little>
	class mm_i_archive_endian
	{
	public:
		struct mm_streambuf& streambuf;
	public:
		mm_i_archive_endian(struct mm_streambuf& _streambuf)
			: streambuf(_streambuf)
		{

		}
	};
	typedef mm_i_archive_endian<mm_archive_byte_order_little> mm_i_archive_little;
	typedef mm_i_archive_endian<mm_archive_byte_order_bigger> mm_i_archive_bigger;

	template<class archive_byte_order>
	static mm_inline const mm_i_archive_endian<archive_byte_order>& operator >> (const mm_i_archive_endian<archive_byte_order>& archive, mm_uint8_t& val) 
	{
		// mm_uint8_t is benchmark unit.
		mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&val, 0, sizeof(mm_uint8_t));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_i_archive_endian<archive_byte_order>& operator >> (const mm_i_archive_endian<archive_byte_order>& archive, mm_sint8_t& val) 
	{
		// mm_sint8_t is benchmark unit.
		mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_sint8_t*)&val, 0, sizeof(mm_sint8_t));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_i_archive_endian<archive_byte_order>& operator >> (const mm_i_archive_endian<archive_byte_order>& archive, mm_uint16_t& val) 
	{
		mm_uint16_t v = mm_uint16_t();
		mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_uint16_t));
		val = archive_byte_order::endian_decode_16(v);
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_i_archive_endian<archive_byte_order>& operator >> (const mm_i_archive_endian<archive_byte_order>& archive, mm_sint16_t& val) 
	{
		mm_sint16_t v = mm_sint16_t();
		mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_sint16_t));
		val = archive_byte_order::endian_decode_16(v);
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_i_archive_endian<archive_byte_order>& operator >> (const mm_i_archive_endian<archive_byte_order>& archive, mm_uint32_t& val) 
	{
		mm_uint32_t v = mm_uint32_t();
		mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_uint32_t));
		val = archive_byte_order::endian_decode_32(v);
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_i_archive_endian<archive_byte_order>& operator >> (const mm_i_archive_endian<archive_byte_order>& archive, mm_sint32_t& val) 
	{
		mm_sint32_t v = mm_sint32_t();
		mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_sint32_t));
		val = archive_byte_order::endian_decode_32(v);
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_i_archive_endian<archive_byte_order>& operator >> (const mm_i_archive_endian<archive_byte_order>& archive, mm_uint64_t& val) 
	{
		mm_uint64_t v = mm_uint64_t();
		mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_uint64_t));
		val = archive_byte_order::endian_decode_64(v);
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_i_archive_endian<archive_byte_order>& operator >> (const mm_i_archive_endian<archive_byte_order>& archive, mm_sint64_t& val) 
	{
		mm_sint64_t v = mm_sint64_t();
		mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_sint64_t));
		val = archive_byte_order::endian_decode_64(v);
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_i_archive_endian<archive_byte_order>& operator >> (const mm_i_archive_endian<archive_byte_order>& archive, float& val) 
	{
		// float is IEEE754
		mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&val, 0, sizeof(float));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_i_archive_endian<archive_byte_order>& operator >> (const mm_i_archive_endian<archive_byte_order>& archive, double& val) 
	{
		// double is IEEE754
		mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&val, 0, sizeof(double));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_i_archive_endian<archive_byte_order>& operator >> (const mm_i_archive_endian<archive_byte_order>& archive, std::string& val) 
	{
		mm_uint32_t v = mm_uint32_t();
		mm_uint32_t size = mm_uint32_t();
		mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_uint32_t));
		size = archive_byte_order::endian_decode_32(v);
		if ( 0 != size )
		{
			val.resize(size);
			mm_streambuf_sgetn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)val.data(), 0, size);
		}
		return archive;
	}

	template<typename archive_byte_order = mm_archive_byte_order_little>
	class mm_o_archive_endian
	{
	public:
		struct mm_streambuf& streambuf;
	public:
		mm_o_archive_endian(struct mm_streambuf& _streambuf)
			: streambuf(_streambuf)
		{

		}
	};
	typedef mm_o_archive_endian<mm_archive_byte_order_little> mm_o_archive_little;
	typedef mm_o_archive_endian<mm_archive_byte_order_bigger> mm_o_archive_bigger;

	template<class archive_byte_order>
	static mm_inline const mm_o_archive_endian<archive_byte_order>& operator << (mm_o_archive_endian<archive_byte_order>& archive, const mm_uint8_t& val) 
	{
		// mm_uint8_t is benchmark unit.
		mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&val, 0, sizeof(mm_uint8_t));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_o_archive_endian<archive_byte_order>& operator << (mm_o_archive_endian<archive_byte_order>& archive, const mm_sint8_t& val) 
	{
		// mm_sint8_t is benchmark unit.
		mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&val, 0, sizeof(mm_sint8_t));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_o_archive_endian<archive_byte_order>& operator << (mm_o_archive_endian<archive_byte_order>& archive, const mm_uint16_t& val) 
	{
		mm_uint16_t v = archive_byte_order::endian_encode_16(val);
		mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_uint16_t));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_o_archive_endian<archive_byte_order>& operator << (mm_o_archive_endian<archive_byte_order>& archive, const mm_sint16_t& val) 
	{
		mm_sint16_t v = archive_byte_order::endian_encode_16(val);
		mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_sint16_t));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_o_archive_endian<archive_byte_order>& operator << (mm_o_archive_endian<archive_byte_order>& archive, const mm_uint32_t& val) 
	{
		mm_uint32_t v = archive_byte_order::endian_encode_32(val);
		mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_uint32_t));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_o_archive_endian<archive_byte_order>& operator << (mm_o_archive_endian<archive_byte_order>& archive, const mm_sint32_t& val) 
	{
		mm_sint32_t v = archive_byte_order::endian_encode_32(val);
		mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_sint32_t));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_o_archive_endian<archive_byte_order>& operator << (mm_o_archive_endian<archive_byte_order>& archive, const mm_uint64_t& val) 
	{
		mm_uint64_t v = archive_byte_order::endian_encode_64(val);
		mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_uint64_t));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_o_archive_endian<archive_byte_order>& operator << (mm_o_archive_endian<archive_byte_order>& archive, const mm_sint64_t& val) 
	{
		mm_sint64_t v = archive_byte_order::endian_encode_64(val);
		mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_sint64_t));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_o_archive_endian<archive_byte_order>& operator << (mm_o_archive_endian<archive_byte_order>& archive, const float& val) 
	{
		// float is IEEE754
		mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&val, 0, sizeof(float));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_o_archive_endian<archive_byte_order>& operator << (mm_o_archive_endian<archive_byte_order>& archive, const double& val) 
	{
		// double is IEEE754
		mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&val, 0, sizeof(double));
		return archive;
	}
	template<class archive_byte_order>
	static mm_inline const mm_o_archive_endian<archive_byte_order>& operator << (mm_o_archive_endian<archive_byte_order>& archive, const std::string& val) 
	{
		mm_uint32_t size = val.size();
		mm_uint32_t v = archive_byte_order::endian_encode_32(size);
		mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)&v, 0, sizeof(mm_uint32_t));
		if ( 0 != size )
		{
			mm_streambuf_sputn((struct mm_streambuf*)&archive.streambuf,(mm_uint8_t*)val.c_str(), 0, size);
		}
		return archive;
	}
}

#endif//__mm_archive_endian_h__