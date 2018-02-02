#include "lj_map_rob.h"

namespace mm
{
	lj_map_rob::lj_map_rob()
	{
		pthread_mutex_init(&this->d_cond_locker, NULL);
		pthread_cond_init(&this->d_cond_not_null, NULL);
		pthread_cond_init(&this->d_cond_not_full, NULL);
		pthread_spin_init(&this->d_map_locker, 0);
		pthread_spin_init(&this->d_locker, 0);
		this->map_sid_number.clear();
		this->d_size = 0;
		this->d_max_size = -1;// -1 is default limit size_t value.
	}

	lj_map_rob::~lj_map_rob()
	{
		pthread_mutex_destroy(&this->d_cond_locker);
		pthread_cond_destroy(&this->d_cond_not_null);
		pthread_cond_destroy(&this->d_cond_not_full);
		pthread_spin_destroy(&this->d_map_locker);
		pthread_spin_destroy(&this->d_locker);
		this->map_sid_number.clear();
		this->d_size = 0;
		this->d_max_size = -1;// -1 is default limit size_t value.
	}

	void lj_map_rob::lock()
	{
		pthread_spin_lock(&this->d_locker);
	}

	void lj_map_rob::unlock()
	{
		pthread_spin_unlock(&this->d_locker);
	}

	void lj_map_rob::cond_not_null()
	{
		pthread_mutex_lock(&this->d_cond_locker);
		pthread_cond_signal(&this->d_cond_not_null);
		pthread_mutex_unlock(&this->d_cond_locker);
	}

	void lj_map_rob::cond_not_full()
	{
		pthread_mutex_lock(&this->d_cond_locker);
		pthread_cond_signal(&this->d_cond_not_full);
		pthread_mutex_unlock(&this->d_cond_locker);
	}

	void lj_map_rob::set_max_size(uint32_t d_max_size)
	{ 
	this->d_max_size = d_max_size; 
	}

	void lj_map_rob::clean_map()
	{ 

		this->map_sid_number.clear(); 

	}

	bool lj_map_rob::insert_data(uint32_t  sid,uint32_t  number)
	{

		map_type::iterator it = this->map_sid_number.find(sid);

		if (it == this->map_sid_number.end())
		{
			this->map_sid_number.insert(map_type::value_type(sid,number));
			return true;
		}
		else
		{
			return false;
		}
	}

	void* lj_map_rob::rpop()
	{
		void* v = NULL;
		pthread_spin_lock(&this->d_list_locker);
		v = this->d_queue.back();
		this->d_queue.pop_back();
		pthread_mutex_lock(&this->d_cond_locker);
		this->d_size --;
		pthread_mutex_unlock(&this->d_cond_locker);
		pthread_spin_unlock(&this->d_list_locker);
		return v;
	}

	void lj_map_rob::blpush( void* e )
	{
		if ( this->d_max_size <= this->d_size )
		{
			pthread_mutex_lock(&this->d_cond_locker);
			if ( this->d_max_size <= this->d_size )
			{
				// double lock checking.
				// because the first quick size checking is not thread safe.
				// here we lock and check the size once again.
				pthread_cond_wait(&this->d_cond_not_full, &this->d_cond_locker);
			}
			pthread_mutex_unlock(&this->d_cond_locker);
		}
		//
		this->lpush(e);
		// conn map from 0 to 1,change to not empty,we signal to msg loop.
		if ( 1 == this->d_size)
		{
			pthread_mutex_lock(&this->d_cond_locker);
			pthread_cond_signal(&this->d_cond_not_null);
			pthread_mutex_unlock(&this->d_cond_locker);
		}
	}

	void* lj_map_rob::blpop()
	{
		void* v = NULL;
		if ( 0 >= this->d_size )
		{
			pthread_mutex_lock(&this->d_cond_locker);
			if ( 0 >= this->d_size )
			{
				// double lock checking.
				// because the first quick size checking is not thread safe.
				// here we lock and check the size once again.
				pthread_cond_wait(&this->d_cond_not_null, &this->d_cond_locker);
			}
			pthread_mutex_unlock(&this->d_cond_locker);
		}
		//
		v = this->lpop();
		//
		if ( this->d_max_size - 1 == this->d_size )
		{
			pthread_mutex_lock(&this->d_cond_locker);
			pthread_cond_signal(&this->d_cond_not_full);
			pthread_mutex_unlock(&this->d_cond_locker);
		}
		return v;
	}

	void lj_map_rob::brpush( void* e )
	{
		if ( this->d_max_size <= this->d_size )
		{
			pthread_mutex_lock(&this->d_cond_locker);
			if ( this->d_max_size <= this->d_size )
			{
				// double lock checking.
				// because the first quick size checking is not thread safe.
				// here we lock and check the size once again.
				pthread_cond_wait(&this->d_cond_not_full, &this->d_cond_locker);
			}
			pthread_mutex_unlock(&this->d_cond_locker);
		}
		//
		this->rpush(e);
		// conn map from 0 to 1,change to not empty,we signal to msg loop.
		if ( 1 == this->d_size)
		{
			pthread_mutex_lock(&this->d_cond_locker);
			pthread_cond_signal(&this->d_cond_not_null);
			pthread_mutex_unlock(&this->d_cond_locker);
		}
	}

	void* lj_map_rob::brpop()
	{
		void* v = NULL;
		if ( 0 >= this->d_size )
		{
			pthread_mutex_lock(&this->d_cond_locker);
			if ( 0 >= this->d_size )
			{
				// double lock checking.
				// because the first quick size checking is not thread safe.
				// here we lock and check the size once again.
				pthread_cond_wait(&this->d_cond_not_null, &this->d_cond_locker);
			}
			pthread_mutex_unlock(&this->d_cond_locker);
		}
		//
		v = this->rpop();
		//
		if ( this->d_max_size - 1 == this->d_size )
		{
			pthread_mutex_lock(&this->d_cond_locker);
			pthread_cond_signal(&this->d_cond_not_full);
			pthread_mutex_unlock(&this->d_cond_locker);
		}
		return v;
	}

}
