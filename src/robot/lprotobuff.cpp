#include "lprotobuff.h"
#include "lj_byteswap.h"
#include "llog.h"

////////////////////////////////////////////////////////////////////////////////////
bigEndian_in::bigEndian_in(struct lj_data_slot* slot_data)
{
	this->slot_in = slot_data;
}

bigEndian_in::~bigEndian_in()
{
}

bigEndian_in& operator << (bigEndian_in &b, const uint8_t d)
{
	uint8_t swap_u = big_endian_unit8(d);
	lj_data_slot_char_putn(b.slot_in , 1, (char*)&swap_u ,0);
	return b;
}
bigEndian_in& operator << (bigEndian_in &b, const int8_t d)
{
	uint8_t swap_u = big_endian_unit8(d);
	lj_data_slot_char_putn(b.slot_in , 1, (char*)&swap_u ,0);
	return b;
}
bigEndian_in& operator << (bigEndian_in &b, const uint16_t d)
{
	uint16_t swap_u = big_endian_unit16(d);
	lj_data_slot_char_putn(b.slot_in , 2, (char*)&swap_u ,0);
	return b;
}
bigEndian_in& operator << (bigEndian_in &b, const int16_t d)
{
	int16_t swap_u = big_endian_unit16(d);
	lj_data_slot_char_putn(b.slot_in , 2, (char*)&swap_u ,0);
	return b;
}
bigEndian_in& operator << (bigEndian_in &b, const uint32_t d)
{
	uint32_t swap_u = big_endian_unit32(d);
	lj_data_slot_char_putn(b.slot_in , 4, (char*)&swap_u ,0);
	return b;
}
bigEndian_in& operator << (bigEndian_in &b, const int32_t d)
{
	int32_t swap_u = big_endian_unit32(d);
	lj_data_slot_char_putn(b.slot_in , 4, (char*)&swap_u ,0);
	return b;
}
bigEndian_in& operator << (bigEndian_in &b, const uint64_t d)
{
	uint64_t swap_u = big_endian_unit64(d);
	lj_data_slot_char_putn(b.slot_in , 8, (char*)&swap_u ,0);
	return b;
}
bigEndian_in& operator << (bigEndian_in &b, const int64_t d)
{
	int64_t swap_u = big_endian_unit64(d);
	lj_data_slot_char_putn(b.slot_in , 8, (char*)&swap_u ,0);
	return b;
}
bigEndian_in& operator << (bigEndian_in &b, const bool d)
{
	char bool_value;
	if (d)
	{
		bool_value = 1;
	}
	else
	{
		bool_value = 0;
	}
	lj_data_slot_char_putn(b.slot_in , 1, (char*)&bool_value ,0);
	return b;
}
bigEndian_in& operator << (bigEndian_in &b, const float d)
{
	lj_data_slot_char_putn(b.slot_in , 4, (char*)&d ,0);
	return b;
}
bigEndian_in& operator << (bigEndian_in &b, const double d)
{
	lj_data_slot_char_putn(b.slot_in , 8, (char*)&d ,0);
	return b;
}
bigEndian_in& operator << (bigEndian_in &b, const std::string& s)
{
	uint16_t swap_length = big_endian_unit16(s.length());
	lj_data_slot_char_putn(b.slot_in , 2, (char*)&swap_length ,0);
	lj_data_slot_char_putn(b.slot_in , s.length(), (char*)s.c_str() ,0);
	return b;
}


////////////////////////////////////////////////////////////////////////////////////
bigEndian_out::bigEndian_out(struct lj_data_slot* slot_data)
{
	this->slot_out = slot_data;
}

bigEndian_out::~bigEndian_out()
{
}

const bigEndian_out& operator >> (const bigEndian_out &b, uint8_t& d) 
{
	uint8_t big_u;
	if (lj_data_slot_char_getn(b.slot_out , 1, (char*)&big_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = big_endian_unit8(big_u);
	return b;
}
const bigEndian_out& operator >> (const bigEndian_out &b, int8_t& d) 
{
	int8_t big_u;
	if (lj_data_slot_char_getn(b.slot_out , 1, (char*)&big_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = big_endian_unit8(big_u);
	return b;
}
const bigEndian_out& operator >> (const bigEndian_out &b, uint16_t& d) 
{
	uint16_t big_u;
	if (lj_data_slot_char_getn(b.slot_out , 2, (char*)&big_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = big_endian_unit16(big_u);
	return b;
}
const bigEndian_out& operator >> (const bigEndian_out &b, int16_t& d) 
{
	int16_t big_u;
	if (lj_data_slot_char_getn(b.slot_out , 2, (char*)&big_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = big_endian_unit16(big_u);
	return b;
}
const bigEndian_out& operator >> (const bigEndian_out &b, uint32_t& d) 
{
	uint32_t big_u;
	if (lj_data_slot_char_getn(b.slot_out , 4, (char*)&big_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = big_endian_unit32(big_u);
	return b;
}
const bigEndian_out& operator >> (const bigEndian_out &b, int32_t& d) 
{
	int32_t big_u;
	if (lj_data_slot_char_getn(b.slot_out , 4, (char*)&big_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = big_endian_unit32(big_u);
	return b;
}
const bigEndian_out& operator >> (const bigEndian_out &b, uint64_t& d) 
{
	uint64_t big_u;
	if (lj_data_slot_char_getn(b.slot_out , 8, (char*)&big_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = big_endian_unit64(big_u);
	return b;
}
const bigEndian_out& operator >> (const bigEndian_out &b, int64_t& d) 
{
	int64_t big_u;
	if (lj_data_slot_char_getn(b.slot_out , 8, (char*)&big_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = big_endian_unit64(big_u);
	return b;
}
const bigEndian_out& operator >> (const bigEndian_out &b, bool& d) 
{
	char bool_value;
	if (lj_data_slot_char_getn(b.slot_out , 1, (char*)&bool_value ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	if (1 == bool_value)
	{
		d = true;
	}
	else
	{
		d = false;
	}
	return b;
}
const bigEndian_out& operator >> (const bigEndian_out &b, float& d) 
{
	if (lj_data_slot_char_getn(b.slot_out , 4, (char*)&d ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	return b;
}
const bigEndian_out& operator >> (const bigEndian_out &b, double& d) 
{
	if (lj_data_slot_char_getn(b.slot_out , 8, (char*)&d ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	return b;
}
const bigEndian_out& operator >> (const bigEndian_out &b, std::string& s) 
{
	uint32_t s_len;
	if (lj_data_slot_char_getn(b.slot_out , 2, (char*)&s_len ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	if (lj_data_slot_char_getn(b.slot_out , s_len, (char*)s.c_str() ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	return b;
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
littleEndian_in::littleEndian_in(struct lj_data_slot* slot_data)
{
	this->slot_in = slot_data;
}

littleEndian_in::~littleEndian_in()
{
}

littleEndian_in& operator << (littleEndian_in &b, const uint8_t d)
{
	uint8_t swap_u = little_endian_unit8(d);
	lj_data_slot_char_putn(b.slot_in , 1, (char*)&swap_u ,0);
	return b;
}
littleEndian_in& operator << (littleEndian_in &b, const int8_t d)
{
	uint8_t swap_u = little_endian_unit8(d);
	lj_data_slot_char_putn(b.slot_in , 1, (char*)&swap_u ,0);
	return b;
}
littleEndian_in& operator << (littleEndian_in &b, const uint16_t d)
{
	uint16_t swap_u = little_endian_unit16(d);
	lj_data_slot_char_putn(b.slot_in , 2, (char*)&swap_u ,0);
	return b;
}
littleEndian_in& operator << (littleEndian_in &b, const int16_t d)
{
	int16_t swap_u = little_endian_unit16(d);
	lj_data_slot_char_putn(b.slot_in , 2, (char*)&swap_u ,0);
	return b;
}
littleEndian_in& operator << (littleEndian_in &b, const uint32_t d)
{
	uint32_t swap_u = little_endian_unit32(d);
	lj_data_slot_char_putn(b.slot_in , 4, (char*)&swap_u ,0);
	return b;
}
littleEndian_in& operator << (littleEndian_in &b, const int32_t d)
{
	int32_t swap_u = little_endian_unit32(d);
	lj_data_slot_char_putn(b.slot_in , 4, (char*)&swap_u ,0);
	return b;
}
littleEndian_in& operator << (littleEndian_in &b, const uint64_t d)
{
	uint64_t swap_u = little_endian_unit64(d);
	lj_data_slot_char_putn(b.slot_in , 8, (char*)&swap_u ,0);
	return b;
}
littleEndian_in& operator << (littleEndian_in &b, const int64_t d)
{
	int64_t swap_u = little_endian_unit64(d);
	lj_data_slot_char_putn(b.slot_in , 8, (char*)&swap_u ,0);
	return b;
}
littleEndian_in& operator << (littleEndian_in &b, const bool d)
{
	char bool_value;
	if (d)
	{
		bool_value = 1;
	}
	else
	{
		bool_value = 0;
	}
	lj_data_slot_char_putn(b.slot_in , 1, (char*)&bool_value ,0);
	return b;
}
littleEndian_in& operator << (littleEndian_in &b, const float d)
{
	lj_data_slot_char_putn(b.slot_in , 4, (char*)&d ,0);
	return b;
}
littleEndian_in& operator << (littleEndian_in &b, const double d)
{
	lj_data_slot_char_putn(b.slot_in , 8, (char*)&d ,0);
	return b;
}
littleEndian_in& operator << (littleEndian_in &b, const std::string& s)
{
	uint16_t swap_length = little_endian_unit16(s.length());
	lj_data_slot_char_putn(b.slot_in , 2, (char*)&swap_length ,0);
	lj_data_slot_char_putn(b.slot_in , s.length(), (char*)s.c_str() ,0);
	return b;
}


////////////////////////////////////////////////////////////////////////////////////
littleEndian_out::littleEndian_out(struct lj_data_slot* slot_data)
{
	this->slot_out = slot_data;
}

littleEndian_out::~littleEndian_out()
{
}

const littleEndian_out& operator >> (const littleEndian_out &b, uint8_t& d) 
{
	uint8_t little_u;
	if (lj_data_slot_char_getn(b.slot_out , 1, (char*)&little_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = little_endian_unit8(little_u);
	return b;
}
const littleEndian_out& operator >> (const littleEndian_out &b, int8_t& d) 
{
	int8_t little_u;
	if (lj_data_slot_char_getn(b.slot_out , 1, (char*)&little_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = little_endian_unit8(little_u);
	return b;
}
const littleEndian_out& operator >> (const littleEndian_out &b, uint16_t& d) 
{
	uint16_t little_u;
	if (lj_data_slot_char_getn(b.slot_out , 2, (char*)&little_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = little_endian_unit16(little_u);
	return b;
}
const littleEndian_out& operator >> (const littleEndian_out &b, int16_t& d) 
{
	int16_t little_u;
	if (lj_data_slot_char_getn(b.slot_out , 2, (char*)&little_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = little_endian_unit16(little_u);
	return b;
}
const littleEndian_out& operator >> (const littleEndian_out &b, uint32_t& d) 
{
	uint32_t little_u;
	if (lj_data_slot_char_getn(b.slot_out , 4, (char*)&little_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = little_endian_unit32(little_u);
	return b;
}
const littleEndian_out& operator >> (const littleEndian_out &b, int32_t& d) 
{
	uint32_t little_u;
	if (lj_data_slot_char_getn(b.slot_out , 4, (char*)&little_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = little_endian_unit32(little_u);
	return b;
}
const littleEndian_out& operator >> (const littleEndian_out &b, uint64_t& d) 
{
	uint64_t little_u;
	if (lj_data_slot_char_getn(b.slot_out , 8, (char*)&little_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = little_endian_unit64(little_u);
	return b;
}
const littleEndian_out& operator >> (const littleEndian_out &b, int64_t& d) 
{
	uint64_t little_u;
	if (lj_data_slot_char_getn(b.slot_out , 8, (char*)&little_u ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	d = little_endian_unit64(little_u);
	return b;
}
const littleEndian_out& operator >> (const littleEndian_out &b, bool& d) 
{
	char bool_value;
	if (lj_data_slot_char_getn(b.slot_out , 1, (char*)&bool_value ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	if (1 == bool_value)
	{
		d = true;
	}
	else
	{
		d = false;
	}
	return b;
}
const littleEndian_out& operator >> (const littleEndian_out &b, float& d) 
{
	if (lj_data_slot_char_getn(b.slot_out , 4, (char*)&d ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	return b;
}
const littleEndian_out& operator >> (const littleEndian_out &b, double& d) 
{
	if (lj_data_slot_char_getn(b.slot_out , 8, (char*)&d ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	return b;
}
const littleEndian_out& operator >> (const littleEndian_out &b, std::string& s) 
{
	uint32_t s_len;
	if (lj_data_slot_char_getn(b.slot_out , 2, (char*)&s_len ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	if (lj_data_slot_char_getn(b.slot_out , s_len, (char*)s.c_str() ,0))
	{
		llog_W("%s %d %s Data slot warning ,Wrong data will be obtained !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	return b;
}


