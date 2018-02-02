#ifndef __mm_locker_queue_h__
#define __mm_locker_queue_h__

#include <list>
#include <pthread.h>

namespace mm
{
	class mm_locker_queue
	{
	public:
		typedef std::list<void*> queue_type;
	public:
		queue_type d_queue;
		pthread_mutex_t d_cond_locker;
		pthread_cond_t d_cond_not_null;
		pthread_cond_t d_cond_not_full;
		pthread_spinlock_t d_list_locker;
		pthread_spinlock_t d_locker;
		size_t d_size;// current size.
		size_t d_max_size;// max transport size.default is -1;
	public:
		mm_locker_queue();
		~mm_locker_queue();
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
		void lpush(void* e);
		void* lpop();
		void rpush(void* e);
		void* rpop();
		//////////////////////////////////////////////////////////////////////////
		// block function.will lock.
		void blpush(void* e);
		void* blpop();
		void brpush(void* e);
		void* brpop();
	};

}


#endif//__mm_locker_queue_h__