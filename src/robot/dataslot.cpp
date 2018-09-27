#include "dataslot.h"
#include <stdlib.h>
#include "llog.h"
#include <string.h>

#define  DATA_SLOT_BASE_CAP 1024

//vs Compiler
#ifdef _MSC_VER


#else


#endif
/////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////
void lj_data_slot_init(struct lj_data_slot* p)
{
	p->len_remove = 0;
	p->len_data  = 0;
	p->base_page = 1;
	p->base_slot = DATA_SLOT_BASE_CAP;
	p->num_cross = 0;
	p->init_time = time(NULL); //获取日历时间  
	p->shortFlag = 0;
	p->len_slot = p->base_page * p->base_slot;
	p->max_slot = p->len_slot;
	p->data_fp = NULL;
	p->data_fp =(char*)malloc((p->len_slot + 1)* sizeof(char));//使用malloc分配内存的首地址，然后赋值给a
	if (NULL == p->data_fp)
	{
		llog_E("%s %d %s ERROR, The pointer is empty !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	else
	{
		memset(p->data_fp,0,(p->len_slot + 1) * sizeof(char));
	}
}

void lj_data_slot_destroy(struct lj_data_slot* p)
{
	p->len_remove = 0;
	p->len_data  = 0;
	p->base_page = 0;
	p->base_slot = 0;
	p->num_cross = 0;
	p->init_time = 0;  
	p->shortFlag = 0;
	p->len_slot = 0;
	p->max_slot = 0;
	if ( NULL !=p->data_fp )
	{
		free(p->data_fp);
		p->data_fp = NULL;
	}
}
/////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////

char* lj_data_slot_get_buff_adress(struct lj_data_slot* p)
{
	return p->data_fp;
}

char* lj_data_slot_get_data_adress(struct lj_data_slot* p , uint32_t position)
{
	return p->data_fp + position;
}

void lj_data_slot_printf_data_slot(struct lj_data_slot* p)
{
	uint32_t i;
	char *buff = (char*)malloc( sizeof(char) * ((p->len_slot)*4 + 5));	
	memset(buff,0,sizeof(char) * ((p->len_slot)*4 + 5));
	sprintf(buff + 0,"[");
	for (i=0;i<p->len_slot;i++)
	{
		sprintf(buff + 1 + i*4,"%03u,",(uint32_t)(p->data_fp[0 + i]));
	}
	sprintf(buff + (p->len_slot) * 4 + 1,"]");	
	llog_I("slot data:%s \n",buff);
	free(buff);
}
void lj_data_slot_printf_residual_data(struct lj_data_slot* p)
{
	if (p->len_data - p->len_remove >0)
	{
		uint32_t i;
		char *buff = (char*)malloc( sizeof(char) * ((p->len_data - p->len_remove)*4 + 5));	
		memset(buff,0,sizeof(char) * ((p->len_data - p->len_remove)*4 + 5));
		sprintf(buff + 0,"[");
		for (i=0;i<p->len_data;i++)
		{
			sprintf(buff + 1 + i*4,"%03u,",(uint32_t)(p->data_fp[p->len_remove + i]));
		}
		sprintf(buff + (p->len_data - p->len_remove) * 4 + 1,"]");	
		llog_I("slot data:%s \n",buff);
		free(buff);
	}
}

/////////////////////////////////////////////////////////////////////
void lj_data_slot_set_data_slot(struct lj_data_slot* p , uint32_t base_page ,uint32_t base_slot)
{
	p->base_page = base_page;
	p->base_slot = base_slot;
	lj_data_slot_cross_buffer_move(p,base_page);
}

/////////////////////////////////////////////////////////////////////
void lj_data_slot_buffer_move(struct lj_data_slot* p )
{
	if ( p->len_data - p->len_remove <=0)
	{
		p->len_remove = 0;
		p->len_data = 0 ;
	}else
	{
		if (0 != p->len_remove)
		{
			if (p->len_data - p->len_remove >0)
			{
				memcpy(p->data_fp,p->data_fp + p->len_remove, p->len_data - p->len_remove );
			}		
		}
		p->len_remove = 0;
		p->len_data -=p->len_remove;
	}

}

void lj_data_slot_cross_buffer_move(struct lj_data_slot* p , uint32_t page )
{
	char* new_slot =(char*)malloc((p->base_slot * page + 1) * sizeof(char));//使用malloc分配内存的首地址，然后赋值给a
	if (NULL == new_slot)
	{
		llog_E("%s %d %s ERROR, The pointer is empty !\n",__FILE__,__LINE__,__FUNCTION__);
	}
	else
	{
		memset(new_slot,0,(p->base_slot * page + 1) * sizeof(char));
	}	

	if (p->len_data - p->len_remove >0 )
	{
		memcpy(new_slot,p->data_fp + p->len_remove, p->len_data - p->len_remove);
		p->len_remove = 0;
		p->len_data -=p->len_remove;
	}
	else
	{
		p->len_remove = 0;
		p->len_data = 0 ;
	}	
	p->len_slot = p->base_slot * page;
	p->shortFlag = 0;
	free(p->data_fp);
	p->data_fp = new_slot;
	p->num_cross ++; 
	if (p->len_slot > p->max_slot)
	{
		p->max_slot = p->len_slot;
	} 
}
/////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////





/////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////
uint32_t lj_data_slot_add_data_a_cup(struct lj_data_slot* p , uint32_t max_cup )
{
	if ( p->len_data - p->len_remove <=0)
	{
		p->len_remove = 0;
		p->len_data = 0 ;
	}

	if (p->base_page * p->base_slot != p->len_slot)
	{
		if ((p->len_data - p->len_remove + max_cup) <= (p->base_page * p->base_slot))
		{
			p->shortFlag ++;
		} 
		else
		{
			p->shortFlag = 0;
		}
		if (p->shortFlag > 5)
		{
			lj_data_slot_cross_buffer_move(p,p->base_page);
		}
	} 

	if ((p->len_slot - p->len_data) < max_cup)
	{
		if ((p->len_slot - p->len_data + p->len_remove) > max_cup )
		{
			lj_data_slot_buffer_move(p);
		} 
		else
		{
			uint32_t new_page = (p->len_data - p->len_remove + max_cup)/p->base_slot + 1;
			lj_data_slot_cross_buffer_move(p,new_page);
		}
	} 
	return (p->len_slot - p->len_data + max_cup );
}

void lj_data_slot_clean_data(struct lj_data_slot* p )
{
	memset(p->data_fp,0,p->len_slot +1 );
	p->len_remove = 0;
	p->len_data = 0;
}
void lj_data_slot_char_putn(struct lj_data_slot* p , uint32_t length_put_data, char* put_data ,uint32_t offset_put_data)
{
	if (p->len_slot - p->len_data > length_put_data)
	{
		memcpy(p->data_fp + p->len_data,put_data + offset_put_data , length_put_data);
		p->len_data += length_put_data;
	}else
	{
		lj_data_slot_add_data_a_cup(p,length_put_data);
		memcpy(p->data_fp + p->len_data,put_data + offset_put_data , length_put_data);
		p->len_data += length_put_data;
	}
}

bool lj_data_slot_char_getn(struct lj_data_slot* p , uint32_t length_get_data, char* get_data ,uint32_t offset_get_data)
{
	bool do_ok = false;
	if (p->len_data - p->len_remove > length_get_data)
	{
		memcpy(get_data + offset_get_data ,p->data_fp + p->len_remove, length_get_data);
		p->len_remove += length_get_data;
		do_ok = true;
	}else
	{
		llog_W("Data slot remaining data length:%d,Need to get out data length:%d. However, data is not enough ,Get wrong data... \n",p->len_data,length_get_data);
		if (p->len_slot - p->len_remove > length_get_data)
		{
			memcpy(get_data + offset_get_data ,p->data_fp + p->len_remove, length_get_data);
			p->len_remove += length_get_data;
		}
		else
		{
			lj_data_slot_add_data_a_cup(p,length_get_data);
			memcpy(get_data + offset_get_data ,p->data_fp + p->len_remove, length_get_data);
			p->len_remove += length_get_data;
		}
	}
	return do_ok;
}

bool lj_data_slot_data_jupm(struct lj_data_slot* p , uint32_t length_jupm_data)
{
	bool do_ok = false;
	if (p->len_data - p->len_remove  >= length_jupm_data)
	{
		p->len_remove -= length_jupm_data;
		do_ok = true;
	} 
	else
	{
		llog_W("Data slot remaining data length:%d,Need to jump data length:%d. However, data is not enough,Data slot is clean now... \n",p->len_data,length_jupm_data);
		p->len_remove = 0;
		p->len_data = 0;
	}
	return do_ok;
}


