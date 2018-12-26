#ifndef _lprotobuff_h_
#define _lprotobuff_h_

#include "dataslot.h"
#include <string>
#include <set>
#include <vector>

//////////////////////////////////////////////////////////////////////////////////////////////
class bigEndian_in
{
public:
	bigEndian_in(struct lj_data_slot* slot_data);
	~bigEndian_in();

public:
	struct lj_data_slot* slot_in;
};
bigEndian_in& operator << (bigEndian_in &b, const uint8_t d) ;
bigEndian_in& operator << (bigEndian_in &b, const int8_t d) ;
bigEndian_in& operator << (bigEndian_in &b, const uint16_t d) ;
bigEndian_in& operator << (bigEndian_in &b, const int16_t d) ;
bigEndian_in& operator << (bigEndian_in &b, const uint32_t d) ;
bigEndian_in& operator << (bigEndian_in &b, const int32_t d) ;
bigEndian_in& operator << (bigEndian_in &b, const uint64_t d) ;
bigEndian_in& operator << (bigEndian_in &b, const int64_t d) ;
bigEndian_in& operator << (bigEndian_in &b, const bool d) ;
bigEndian_in& operator << (bigEndian_in &b, const float d) ;
bigEndian_in& operator << (bigEndian_in &b, const double d) ;
bigEndian_in& operator << (bigEndian_in &b, const std::string& s) ;

template<typename _T>
bigEndian_in& operator << (bigEndian_in &b, const std::vector<_T>& vec)
{
	uint32_t size_vec = (uint32_t)vec.size();
	b<<size_vec;
	std::vector<_T>::iterator it = vec.begin();
	for (;it!=vec.end();++it)
	{
		b<<(*it);
	}
}


//////////////////////////////////////////////////////////////////////////////////////////////
class bigEndian_out
{
public:
	bigEndian_out(struct lj_data_slot* slot_data);
	~bigEndian_out();
public:
	struct lj_data_slot* slot_out;
};
const bigEndian_out& operator >> (const bigEndian_out &b, uint8_t& d) ;
const bigEndian_out& operator >> (const bigEndian_out &b, int8_t& d) ;
const bigEndian_out& operator >> (const bigEndian_out &b, uint16_t& d) ;
const bigEndian_out& operator >> (const bigEndian_out &b, int16_t& d) ;
const bigEndian_out& operator >> (const bigEndian_out &b, uint32_t& d) ;
const bigEndian_out& operator >> (const bigEndian_out &b, int32_t& d) ;
const bigEndian_out& operator >> (const bigEndian_out &b, uint64_t& d) ;
const bigEndian_out& operator >> (const bigEndian_out &b, int64_t& d) ;
const bigEndian_out& operator >> (const bigEndian_out &b, bool& d) ;
const bigEndian_out& operator >> (const bigEndian_out &b, float& d) ;
const bigEndian_out& operator >> (const bigEndian_out &b, double& d) ;
const bigEndian_out& operator >> (const bigEndian_out &b, std::string& s) ;

template<typename _T>
const bigEndian_out& operator >> (const bigEndian_out &b, std::vector<_T>& vec)
{
	uint32_t size_vec ;
	b>>size_vec;
	vec.resize(size_vec);
	std::vector<_T>::iterator it = vec.begin();
	for (;it!=vec.end();++it)
	{
		b>>(*it);
	}
}



//////////////////////////////////////////////////////////////////////////////////////////////
class littleEndian_in
{
public:
	littleEndian_in(struct lj_data_slot* slot_data);
	~littleEndian_in();
public:
	struct lj_data_slot* slot_in;
};
littleEndian_in& operator << (littleEndian_in &b, const uint8_t d) ;
littleEndian_in& operator << (littleEndian_in &b, const int8_t d) ;
littleEndian_in& operator << (littleEndian_in &b, const uint16_t d) ;
littleEndian_in& operator << (littleEndian_in &b, const int16_t d) ;
littleEndian_in& operator << (littleEndian_in &b, const uint32_t d) ;
littleEndian_in& operator << (littleEndian_in &b, const int32_t d) ;
littleEndian_in& operator << (littleEndian_in &b, const uint64_t d) ;
littleEndian_in& operator << (littleEndian_in &b, const int64_t d) ;
littleEndian_in& operator << (littleEndian_in &b, const bool d) ;
littleEndian_in& operator << (littleEndian_in &b, const float d) ;
littleEndian_in& operator << (littleEndian_in &b, const double d) ;
littleEndian_in& operator << (littleEndian_in &b, const std::string& s) ;

template<typename _T>
littleEndian_in& operator << (littleEndian_in &b, const std::vector<_T>& vec)
{
	uint32_t size_vec = (uint32_t)vec.size();
	b<<size_vec;
	std::vector<_T>::iterator it = vec.begin();
	for (;it!=vec.end();++it)
	{
		b<<(*it);
	}
}



//////////////////////////////////////////////////////////////////////////////////////////////
class littleEndian_out
{
public:
	littleEndian_out(struct lj_data_slot* slot_data);
	~littleEndian_out();
public:
	struct lj_data_slot* slot_out;
};
const littleEndian_out& operator >> (const littleEndian_out &b, uint8_t& d) ;
const littleEndian_out& operator >> (const littleEndian_out &b, int8_t& d) ;
const littleEndian_out& operator >> (const littleEndian_out &b, uint16_t& d) ;
const littleEndian_out& operator >> (const littleEndian_out &b, int16_t& d) ;
const littleEndian_out& operator >> (const littleEndian_out &b, uint32_t& d) ;
const littleEndian_out& operator >> (const littleEndian_out &b, int32_t& d) ;
const littleEndian_out& operator >> (const littleEndian_out &b, uint64_t& d) ;
const littleEndian_out& operator >> (const littleEndian_out &b, int64_t& d) ;
const littleEndian_out& operator >> (const littleEndian_out &b, bool& d) ;
const littleEndian_out& operator >> (const littleEndian_out &b, float& d) ;
const littleEndian_out& operator >> (const littleEndian_out &b, double& d) ;
const littleEndian_out& operator >> (const littleEndian_out &b, std::string& s) ;

template<typename _T>
const littleEndian_out& operator >> (const littleEndian_out &b, std::vector<_T>& vec)
{
	uint32_t size_vec ;
	b>>size_vec;
	vec.resize(size_vec);
	std::vector<_T>::iterator it = vec.begin();
	for (;it!=vec.end();++it)
	{
		b>>(*it);
	}
}







#endif  /* _lprotobuff_h_ */