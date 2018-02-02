#include "mm_locker_queue.h"

namespace mm
{
	mm_locker_queue::mm_locker_queue()
	{
		pthread_mutex_init(&this->d_cond_locker, NULL);
		pthread_cond_init(&this->d_cond_not_null, NULL);
		pthread_cond_init(&this->d_cond_not_full, NULL);
		pthread_spin_init(&this->d_list_locker, 0);
		pthread_spin_init(&this->d_locker, 0);
		this->d_queue.clear();
		this->d_size = 0;
		this->d_max_size = -1;// -1 is default limit size_t value.
	}

	mm_locker_queue::~mm_locker_queue()
	{
		pthread_mutex_destroy(&this->d_cond_locker);
		pthread_cond_destroy(&this->d_cond_not_null);
		pthread_cond_destroy(&this->d_cond_not_full);
		pthread_spin_destroy(&this->d_list_locker);
		pthread_spin_destroy(&this->d_locker);
		this->d_queue.clear();
		this->d_size = 0;
		this->d_max_size = -1;// -1 is default limit size_t value.
	}

	void mm_locker_queue::lock()
	{
		pthread_spin_lock(&this->d_locker);
	}

	void mm_locker_queue::unlock()
	{
		pthread_spin_unlock(&this->d_locker);
	}

	void mm_locker_queue::cond_not_null()
	{
		pthread_mutex_lock(&this->d_cond_locker);
		pthread_cond_signal(&this->d_cond_not_null);
		pthread_mutex_unlock(&this->d_cond_locker);
	}

	void mm_locker_queue::cond_not_full()
	{
		pthread_mutex_lock(&this->d_cond_locker);
		pthread_cond_signal(&this->d_cond_not_full);
		pthread_mutex_unlock(&this->d_cond_locker);
	}

	void mm_locker_queue::lpush( void* e )
	{
		pthread_spin_lock(&this->d_list_locker);
		this->d_queue.push_front(e);
		pthread_mutex_lock(&this->d_cond_locker);
		this->d_size ++;
		pthread_mutex_unlock(&this->d_cond_locker);
		pthread_spin_unlock(&this->d_list_locker);
	}

	void* mm_locker_queue::lpop()
	{
		void* v = NULL;
		pthread_spin_lock(&this->d_list_locker);
		v = this->d_queue.front();
		this->d_queue.pop_front();
		pthread_mutex_lock(&this->d_cond_locker);
		this->d_size --;
		pthread_mutex_unlock(&this->d_cond_locker);
		pthread_spin_unlock(&this->d_list_locker);
		return v;
	}

	void mm_locker_queue::rpush( void* e )
	{
		pthread_spin_lock(&this->d_list_locker);
		this->d_queue.push_back(e);
		pthread_mutex_lock(&this->d_cond_locker);
		this->d_size ++;
		pthread_mutex_unlock(&this->d_cond_locker);
		pthread_spin_unlock(&this->d_list_locker);
	}

	void* mm_locker_queue::rpop()
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

	void mm_locker_queue::blpush( void* e )
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

	void* mm_locker_queue::blpop()
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

	void mm_locker_queue::brpush( void* e )
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

	void* mm_locker_queue::brpop()
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
