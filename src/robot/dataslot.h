#ifndef _dataslot_h_
#define _dataslot_h_
#include <stdint.h>
#include "lj_byteswap.h"
#include <time.h>


//vs Compiler
#ifdef _MSC_VER
#	define lj_byteswap_16 _byteswap_ushort
#	define lj_byteswap_32 _byteswap_ulong
#	define lj_byteswap_64 _byteswap_uint64

#else
#	define lj_byteswap_16 bswap_16
#	define lj_byteswap_32 bswap_32
#	define lj_byteswap_64 bswap_64
#endif


struct lj_data_slot
{
	char*  data_fp;
	uint32_t len_slot;
	uint32_t len_remove;
	uint32_t len_data;
	uint32_t base_page;
	uint32_t base_slot;
	uint32_t max_slot;
	time_t init_time;
	uint32_t num_cross;
	uint32_t shortFlag;
};
/////////////////////////////////////////////////////////////////////
//Destructor/////////////////////////////////////////////////////////////////
extern void lj_data_slot_init(struct lj_data_slot* p);
extern void lj_data_slot_destroy(struct lj_data_slot* p);

/////////////////////////////////////////////////////////////////////
//Test function/////////////////////////////////////////////////////////////////
extern char* lj_data_slot_get_buff_adress(struct lj_data_slot* p);
extern char* lj_data_slot_get_data_adress(struct lj_data_slot* p , uint32_t position);
extern void lj_data_slot_printf_data_slot(struct lj_data_slot* p);
extern void lj_data_slot_printf_residual_data(struct lj_data_slot* p);

//Struct function/////////////////////////////////////////////////////////////////
extern void lj_data_slot_set_data_slot(struct lj_data_slot* p , uint32_t base_page ,uint32_t base_slot);
extern void lj_data_slot_buffer_move(struct lj_data_slot* p );
extern void lj_data_slot_cross_buffer_move(struct lj_data_slot* p , uint32_t page );

/////////////////////////////////////////////////////////////////////
//function/////////////////////////////////////////////////////////////////
//A region is appended to the data slot to satisfy the requirement of writing the data to be appended in, 
//to prevent the length of the data slot back section from being too small and the containers from being inadequate.
extern uint32_t lj_data_slot_add_data_a_cup(struct lj_data_slot* p , uint32_t max_cup );
extern void lj_data_slot_clean_data(struct lj_data_slot* p );
extern void lj_data_slot_char_putn(struct lj_data_slot* p , uint32_t length_put_data, char* put_data ,uint32_t offset_put_data);
extern bool lj_data_slot_char_getn(struct lj_data_slot* p , uint32_t length_get_data, char* get_data ,uint32_t offset_get_data);
extern bool lj_data_slot_data_jupm(struct lj_data_slot* p , uint32_t length_jupm_data);
/////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////



#endif  /* _dataslot_h_ */