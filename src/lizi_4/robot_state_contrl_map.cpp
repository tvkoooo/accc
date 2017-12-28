#include "robot_state_contrl_map.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <pthread.h>
//#include "platform_config.h"
#include <map>

static void robot_contrl_map_poll_wait(struct robot_contrl_map* p);

static void pthread_huidiao(struct robot_contrl* obj)
{

}

	void robot_contrl_map_init(struct robot_contrl_map *p)
	{
		p->map_uid.clear();
		p->hui_rob=&pthread_huidiao;
		p->u = NULL;
		pthread_mutex_init(&p->l,NULL);
		pthread_cond_init(&p->c,NULL);
		pthread_mutex_init(&p->m,NULL);

	}
	void robot_contrl_map_destroy(struct robot_contrl_map *p)
	{
		robot_contrl_map_clean(p);
		//
		p->hui_rob=&pthread_huidiao;
		p->map_uid.clear();
		p->u = NULL;
		pthread_mutex_destroy(&p->l);
		pthread_cond_destroy(&p->c);
		pthread_mutex_destroy(&p->m);
	}



	void robot_contrl_map_fuzhi(struct robot_contrl_map*p,robot_huidiao_type f,  void* u)
	{
		p->hui_rob=f;
		p->u=u;
	}


	void robot_contrl_map_start(struct robot_contrl_map * p)
	{

	}



	void robot_contrl_map_shutdown(struct robot_contrl_map *p)
	{
		pthread_mutex_lock(&p->l);
		map_int_robot_con::iterator it=p->map_uid.begin();
		while(it!=p->map_uid.end())
		{
			struct robot_contrl* e = it->second;
			robot_contrl_lock(e);
			robot_contrl_shutdown(e);
			robot_contrl_unlock(e);
			it++;
		}
		pthread_mutex_unlock(&p->l);
		//
		pthread_mutex_lock(&p->m);
		pthread_cond_signal(&p->c);
		pthread_mutex_unlock(&p->m);
	}

	void robot_contrl_map_interrupt(struct robot_contrl_map* p)
	{
		pthread_mutex_lock(&p->l);
		map_int_robot_con::iterator it=p->map_uid.begin();
		while(it!=p->map_uid.end())
		{
			struct robot_contrl* e = it->second;
			robot_contrl_lock(e);
			robot_contrl_interrupt(e);
			robot_contrl_unlock(e);
			it++;
		}
		pthread_mutex_unlock(&p->l);
		//
		pthread_mutex_lock(&p->m);
		pthread_cond_signal(&p->c);
		pthread_mutex_unlock(&p->m);
	}

	void robot_contrl_map_join(struct robot_contrl_map *p)
	{
		pthread_mutex_lock(&p->m);
		pthread_cond_wait(&p->c,&p->m);
		pthread_mutex_unlock(&p->m);

		pthread_mutex_lock(&p->l);
		map_int_robot_con::iterator it=p->map_uid.begin();
		while(it!=p->map_uid.end())
		{
			struct robot_contrl* e = it->second;
			robot_contrl_lock(e);
			robot_contrl_join(e);
			robot_contrl_unlock(e);
			it++;
		}
		pthread_mutex_unlock(&p->l);
	}
	

	// 如果没有创建一个 V 元素 的内存 并初始化数据结构
	robot_contrl* robot_contrl_map_add(struct robot_contrl_map * p,int sid)
	{
		robot_contrl *p_malloc= robot_contrl_map_get(p, sid);
		if (NULL == p_malloc)
		{
			//没有找到
			p_malloc=(robot_contrl *)malloc(sizeof(robot_contrl));
			robot_contrl_init(p_malloc);
			robot_contrl_fuzhi(p_malloc,p->hui_rob,p->u);
			p_malloc->sid=sid;
			//
			pthread_mutex_lock(&p->l);
			p->map_uid.insert(map_int_robot_con::value_type(sid,p_malloc));
			pthread_mutex_unlock(&p->l);
		}
		return p_malloc;
	}
	// 如果没有元素返回空
	robot_contrl* robot_contrl_map_get(struct robot_contrl_map *p,int sid)
	{
		robot_contrl *p_malloc= NULL;
		pthread_mutex_lock(&p->l);
		map_int_robot_con::iterator it= p->map_uid.find(sid);
		if (it != p->map_uid.end())
		{
			//找到
			p_malloc = it->second;
		}
		pthread_mutex_unlock(&p->l);
		return p_malloc;
	}
	// 获取一个元素，并销毁他的数据结构。释放这个 内存块
	void robot_contrl_map_rmv(struct robot_contrl_map *p,int sid)
	{
		pthread_mutex_lock(&p->l);
		map_int_robot_con::iterator it= p->map_uid.find(sid);
		if (it != p->map_uid.end())
		{
			//找到
			struct robot_contrl* e = it->second;
			robot_contrl_lock(e);
			p->map_uid.erase(it);
			robot_contrl_unlock(e);
			free(e);
		}
		pthread_mutex_unlock(&p->l);
	}
	// 获取一个，如果没有就添加一个 内存块并初始化数据结构
	robot_contrl* robot_contrl_map_huoqubingchuangjian(struct robot_contrl_map *p,int sid)
	{
		// lock free.
		robot_contrl *p_malloc= robot_contrl_map_get(p, sid);
		if (NULL == p_malloc)
		{
			//没有找到
			p_malloc = robot_contrl_map_add(p,sid);

		}
		return p_malloc;
	}
	// 析构所有数据结构，清楚所有内存块
	void robot_contrl_map_clean(struct robot_contrl_map *p)
	{
		pthread_mutex_lock(&p->l);
		map_int_robot_con::iterator it=p->map_uid.begin();
		while(it!=p->map_uid.end())
		{
			struct robot_contrl* e = it->second;
			robot_contrl_lock(e);
			p->map_uid.erase(it++);
			robot_contrl_unlock(e);
			free(e);
		}
		pthread_mutex_unlock(&p->l);
	}


	void robot_contrl_map_kaibo(struct robot_contrl_map *p, int sid)
	{
		// lock free.

		robot_contrl *sid_singer;
		//1.查看 输入的 sid号是否已经在列表中，如果不在则添加入map。
		sid_singer=robot_contrl_map_get(p,sid);
		if (NULL==sid_singer)
		{
			//2.把 sid 加人map，并且绑定sid 对应的 开辟的结构体指针
			sid_singer = robot_contrl_map_huoqubingchuangjian(p, sid);
			//3.加载sid 对应新加入的结构体
			robot_contrl_rand_number(sid_singer);
			robot_contrl_start(sid_singer);
		}
		else
		{
			//什么都不做。
		}
	}


	void robot_contrl_map_guanbo(struct robot_contrl_map *p, int sid)
	{
		// lock free.

		robot_contrl *sid_singer;
		//1.查看 输入的 sid号是否已经在列表中,如果存在则移除map。
		sid_singer=robot_contrl_map_get(p,sid);
		if (NULL==sid_singer)
		{
			//需要输出异常，数据消失
		} 
		else
		{
			//2.加载sid 对应新加入的结构体
			robot_contrl_shutdown(sid_singer);
			robot_contrl_join(sid_singer);
			//3.移除 map sid号内容
			robot_contrl_map_rmv(p,sid);
		}
	}

