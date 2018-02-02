#include "mm_buffer_queue.h"
#include <assert.h>
namespace mm
{
	static void __static_mm_buffer_queue_handle_callback(void* obj, void* u, std::string* pack)
	{

	}
	mm_buffer_queue::mm_buffer_queue_callback::mm_buffer_queue_callback()
	{
		this->handle = &__static_mm_buffer_queue_handle_callback;
		this->obj = NULL;
	}

	mm_buffer_queue::mm_buffer_queue_callback::~mm_buffer_queue_callback()
	{
		this->handle = &__static_mm_buffer_queue_handle_callback;
		this->obj = NULL;
	}
	//////////////////////////////////////////////////////////////////////////
	mm_buffer_queue::mm_buffer_queue_data::mm_buffer_queue_data()
	{
		this->obj = NULL;
	}

	mm_buffer_queue::mm_buffer_queue_data::~mm_buffer_queue_data()
	{
		this->obj = NULL;
	}
	//////////////////////////////////////////////////////////////////////////
	size_t mm_buffer_queue::MM_BUFFER_QUEUE_MAX_POPER_NUMBER = 10000;
	mm_buffer_queue::mm_buffer_queue()
	{
		pthread_spin_init(&this->d_locker, 0);
		this->d_max_pop = MM_BUFFER_QUEUE_MAX_POPER_NUMBER;
		this->d_u = NULL;
	}

	mm_buffer_queue::~mm_buffer_queue()
	{
		this->dispose();
		//
		pthread_spin_destroy(&this->d_locker);
		this->d_max_pop = -1;
		this->d_u = NULL;
	}

	void mm_buffer_queue::lock()
	{
		pthread_spin_lock(&this->d_locker);
	}

	void mm_buffer_queue::unlock()
	{
		pthread_spin_unlock(&this->d_locker);
	}

	void mm_buffer_queue::assign_queue_tcp_callback( mm_buffer_queue_callback* queue_callback )
	{
		this->d_callback = *queue_callback;
	}

	void mm_buffer_queue::assign_max_poper_number( size_t max_pop )
	{
		this->d_max_pop = max_pop;
	}

	void mm_buffer_queue::assign_context( void* u )
	{
		this->d_u = u;
	}

	void mm_buffer_queue::cond_not_null()
	{
		this->d_locker_queue.cond_not_null();
	}

	void mm_buffer_queue::cond_not_full()
	{
		this->d_locker_queue.cond_not_full();
	}

	void mm_buffer_queue::thread_handle()
	{
		this->pop();
	}

	void mm_buffer_queue::dispose()
	{
		typedef mm_locker_queue::queue_type queue_type;
		mm_buffer_queue_data* data = NULL;
		pthread_spin_unlock(&this->d_locker_queue.d_list_locker);
		queue_type& _queue = this->d_locker_queue.d_queue;
		queue_type::iterator lvp = _queue.begin();
		while (lvp != _queue.end())
		{
			data = (mm_buffer_queue_data*)(*lvp);
			_queue.erase(lvp++);
			delete data;
		}
		pthread_spin_unlock(&this->d_locker_queue.d_list_locker);
	}

	void mm_buffer_queue::push( void* obj, std::string* pack )
	{
		mm_buffer_queue_data* data = new mm_buffer_queue_data;
		data->obj = obj;
		data->pack = *pack;
		// push to message queue.
		this->d_locker_queue.blpush(data);
	}

	void mm_buffer_queue::pop()
	{
		mm_buffer_queue_data* data = NULL;
		size_t n = 0;
		// lock queue size is lock free.and we not need check it thread safe.
		while( 0 != this->d_locker_queue.d_size && n <= this->d_max_pop )
		{
			data = (mm_buffer_queue_data*)this->d_locker_queue.brpop();
			if (NULL != data)
			{
				std::string* pack = &data->pack;
				void* obj = data->obj;

				// if not define the handler,fire the default function.
				assert(NULL != this->d_callback.handle && "this->callback.handle is a null.");
				(*(this->d_callback.handle))(obj,this->d_u,pack);

				delete data;
				data = NULL;
			}
			n++;
		}
	}
}
