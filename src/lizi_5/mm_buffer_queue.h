#ifndef __mm_buffer_queue_h__
#define __mm_buffer_queue_h__

#include "mm_locker_queue.h"
#include <string>

namespace mm
{
	class mm_buffer_queue
	{
	public:
		static size_t MM_BUFFER_QUEUE_MAX_POPER_NUMBER;
	public:
		typedef void (*handle_callback)(void* obj, void* u, std::string* pack);
		class mm_buffer_queue_callback
		{
		public:
			handle_callback handle;
			void* obj;// weak ref. user data for callback.
		public:
			mm_buffer_queue_callback();
			~mm_buffer_queue_callback();
		};
	public:
		class mm_buffer_queue_data
		{
		public:
			void* obj;// weak ref for tcp handle callback.it is mm_tcp.
			std::string pack;
		public:
			mm_buffer_queue_data();
			~mm_buffer_queue_data();
		};
	public:
		mm_locker_queue d_locker_queue;
		mm_buffer_queue_callback d_callback;// value ref. queue tcp callback.
		pthread_spinlock_t d_locker;
		size_t d_max_pop;// max pop to make sure current not wait long times.default is MM_MQ_TCP_MAX_POPER_NUMBER. 
		void* d_u;// user data.
	public:
		mm_buffer_queue();
		~mm_buffer_queue();
	public:
		void lock();
		void unlock();
	public:
		//void assign_callback(mm_uint32_t id,net_tcp_handle callback);
		void assign_queue_tcp_callback(mm_buffer_queue_callback* queue_callback);
		void assign_max_poper_number(size_t max_pop);

		// assign context handle.
		void assign_context(void* u);
	public:
		// you can interrupt block state.but will make the size error.
		// other way is push a NULL.
		void cond_not_null();
		void cond_not_full();
		//////////////////////////////////////////////////////////////////////////
		// main thread trigger self thread handle.such as gl thread.
		void thread_handle();
		// delete all not handle pack at queue.
		void dispose();
		// push a pack and context tp queue.this function will copy alloc pack.
		void push( void* obj, std::string* pack );
		// pop and trigger handle.
		void pop();
	};

}


#endif//__mm_buffer_queue_h__