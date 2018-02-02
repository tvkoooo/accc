#ifndef _lj_map_rob_h__
#define _lj_map_rob_h__

#include <map>
#include <pthread.h>
#include "core\mm_config_platform.h"

namespace mm
{
	class lj_map_rob
	{
	protected:
		typedef std::map<uint32_t,uint32_t> map_type;
	protected:
		map_type map_sid_number;
		pthread_mutex_t d_cond_locker;
		pthread_cond_t d_cond_not_null;
		pthread_cond_t d_cond_not_full;
		pthread_spinlock_t d_map_locker;
		pthread_spinlock_t d_locker;
		size_t d_size;// current size.
		size_t d_max_size;// max transport size.default is -1;
	public:
		lj_map_rob();
		~lj_map_rob();
	public:
		void lock();
		void unlock();
	public:
		// you can interrupt block state.but will make the size error.
		// other way is push a NULL.
		// Note: you can not lock it manual.
		void cond_not_null();
		void cond_not_full();
		//////////////////////////////////////////////////////////////////////////
		// this function is single thread.you might lock you self.
		void set_max_size(uint32_t d_max_size);
		void clean_map();
		bool insert_data(uint32_t  sid,uint32_t  number);
		void cover_data(uint32_t  sid,uint32_t  number);
		void delete_data(uint32_t  sid,uint32_t  number);
		size_t get_size();
		uint32_t map_began();
		uint32_t map_end();	
		uint32_t map_data(uint32_t * sid);
	};

}


#endif//_lj_map_rob_h__