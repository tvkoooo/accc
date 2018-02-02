#ifndef __mm_classic_packet_head_h__
#define __mm_classic_packet_head_h__

#include "core/mm_core.h"

//////////////////////////////////////////////////////////////////////////
namespace mm
{
	class mm_classic_packet_head
	{
	public:
		uint32_t length;
		uint32_t uri;
		uint16_t sid;
		uint16_t resCode;
		uint8_t tag;
	public:
		void encode(sox::Pack& archive)
		{
			archive.push_uint32(this->length);
			archive.push_uint32(this->uri);
			archive.push_uint16(this->sid);
			archive.push_uint16(this->resCode);
			archive.push_uint8(this->tag);
		}
		void decode(const sox::Unpack& archive)
		{
			this->length = archive.pop_uint32();
			this->uri = archive.pop_uint32();
			this->sid = archive.pop_uint16();
			this->resCode = archive.pop_uint16();
			this->tag = archive.pop_uint8();
		}
	};
}
//////////////////////////////////////////////////////////////////////////

#endif//__mm_classic_packet_head_h__