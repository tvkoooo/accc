#ifndef _INC_robot_state_contrl_map
#define _INC_robot_state_contrl_map
//#include "mm_thread_state_t.h"
#include "robot_state_contrl.h"
#include <map>
#include <pthread.h>


	typedef std::map<int,robot_contrl*>  map_int_robot_con;//类型实例化，泛型要实例化操作


	struct robot_contrl_map
	{
		map_int_robot_con map_uid;// 强引用内存
		robot_huidiao_type hui_rob;
		pthread_mutex_t l;// 表锁

		pthread_cond_t c;// 条件变量
		pthread_mutex_t m;// 条件变量锁
		void* u;
	};

	extern void robot_contrl_map_init(struct robot_contrl_map *p);
	extern void robot_contrl_map_destroy(struct robot_contrl_map*p);
	extern void robot_contrl_map_start(struct robot_contrl_map* p);
	extern void robot_contrl_map_shutdown(struct robot_contrl_map* p);
	extern void robot_contrl_map_interrupt(struct robot_contrl_map* p);
	extern void robot_contrl_map_join(struct robot_contrl_map*p);


	//extern void robot_contrl_map_lock(struct robot_contrl_map*p);
	//extern void robot_contrl_map_unlock(struct robot_contrl_map*p);

	// 如果没有创建一个 V 元素 的内存 并初始化数据结构
	extern robot_contrl* robot_contrl_map_add(struct robot_contrl_map *p,int sid);
	// 如果没有元素返回空
	extern robot_contrl* robot_contrl_map_get(struct robot_contrl_map *p,int sid);
	// 获取一个元素，并销毁他的数据结构。释放这个 内存块
	extern void robot_contrl_map_rmv(struct robot_contrl_map *p,int sid);
	// 获取一个，如果没有就添加一个 内存块并初始化数据结构
	extern robot_contrl* robot_contrl_map_huoqubingchuangjian(struct robot_contrl_map *p,int sid);
	// 析构所有数据结构，清楚所有内存块
	extern void robot_contrl_map_clean(struct robot_contrl_map *p);

	extern void robot_contrl_map_kaibo(struct robot_contrl_map *p, int sid);


	extern void robot_contrl_map_fuzhi(struct robot_contrl_map*p,robot_huidiao_type f, void* u);

	extern void robot_contrl_map_guanbo(struct robot_contrl_map *p, int sid);



#endif  /* _INC_robot_state_contrl_map */