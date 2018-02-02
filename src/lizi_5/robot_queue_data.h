#ifndef __robot_queue_data_h_
#define __robot_queue_data_h_
#include <string>
#include "core\mm_config_platform.h"

namespace oo
{
	struct uint32_3data
	{
			uint32_t sid;
			uint32_t cnt;
			uint32_t num;
	};
	class robot_queue_data 
	{
		protected:
			struct uint32_3data d_3;
			std::string pack;

    	public:
			robot_queue_data()
			{   
				memset((void*)(&this->d_3),0,sizeof(uint32_3data));
				this->pack.resize(sizeof(uint32_3data));
			}
			virtual ~robot_queue_data(){};

		public:
			void set_sid(uint32_t sid){	this->d_3.sid=sid;	}
			void set_cnt(uint32_t cnt){	this->d_3.cnt=cnt;	}
			void set_num(uint32_t num){	this->d_3.num=num;	}
			void set_pack(std::string pack){	this->pack=pack;	};					
			//
			void data_encode()
			{
				memcpy((void*)this->pack.data(), (void*)&d_3, sizeof(uint32_3data));
			}
			void data_decode()
			{
				memcpy((void*)&d_3, (void*)this->pack.data(), sizeof(uint32_3data));
			}
			//
			uint32_t get_sid(){	return this->d_3.sid;	}
			uint32_t get_cnt(){	return this->d_3.cnt;	}
			uint32_t get_num(){	return this->d_3.num;	}
			std::string get_pack(){	return this->pack;	}
	};	  

}

   

#endif//__robot_queue_data_h_