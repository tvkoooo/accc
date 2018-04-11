#include "zookeeper/mm_zkwp_path.h"
#include "core/mm_logger.h"
#include "core/mm_atoi.h"
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_watcher(zhandle_t* zh, int type, int state, const char* path, void* watcherCtx);
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_awexists_watcher( zhandle_t* zh, int type, int state, const char* path, void* watcherCtx);
static void __static_zkwp_path_awexists_stat_completion( int rc, const struct Stat* stat, const void* data );
// awexists path
static void __static_zkwp_path_awexists(struct mm_zkwp_path* p);
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_acreate_string_completion(int rc, const char *value, const void *data);
// acreate path
static void __static_zkwp_path_acreate(struct mm_zkwp_path* p);
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_shard_awexists_watcher( zhandle_t* zh, int type, int state, const char* path, void* watcherCtx);
static void __static_zkwp_path_shard_awexists_stat_completion( int rc, const struct Stat* stat, const void* data );
// awexists path/shard
static void __static_zkwp_path_shard_awexists(struct mm_zkwp_path* p);
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_shard_acreate_string_completion(int rc, const char *value, const void *data);
// acreate path/shard
static void __static_zkwp_path_shard_acreate(struct mm_zkwp_path* p);
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_shard_unique_id_awexists_watcher( zhandle_t* zh, int type, int state, const char* path, void* watcherCtx);
static void __static_zkwp_path_shard_unique_id_awexists_stat_completion( int rc, const struct Stat* stat, const void* data );
// awexists path/shard/unique_id
static void __static_zkwp_path_shard_unique_id_awexists(struct mm_zkwp_path* p);
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_shard_unique_id_acreate_string_completion(int rc, const char *value, const void *data);
// acreate path/shard/unique_id
static void __static_zkwp_path_shard_unique_id_acreate(struct mm_zkwp_path* p);
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_shard_unique_id_aset_stat_completion(int rc, const struct Stat *stat, const void *data);
// aset path/shard/unique_id
static void __static_zkwp_path_shard_unique_id_aset(struct mm_zkwp_path* p);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_event( struct mm_zkwp_path* p )
{

}
//////////////////////////////////////////////////////////////////////////
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_callback_init(struct mm_zkwp_path_callback* p)
{
	p->created = &__static_zkwp_path_event;
	p->deleted = &__static_zkwp_path_event;
	p->changed = &__static_zkwp_path_event;
	p->net_connect = &__static_zkwp_path_event;
	p->net_expired = &__static_zkwp_path_event;
	p->obj = NULL;
}
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_callback_destroy(struct mm_zkwp_path_callback* p)
{
	p->created = &__static_zkwp_path_event;
	p->deleted = &__static_zkwp_path_event;
	p->changed = &__static_zkwp_path_event;
	p->net_connect = &__static_zkwp_path_event;
	p->net_expired = &__static_zkwp_path_event;
	p->obj = NULL;
}
//////////////////////////////////////////////////////////////////////////
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_init(struct mm_zkwp_path* p)
{
	struct mm_rbtree_u32_vpt_alloc rbtree_u32_vpt_alloc;

	mm_string_init(&p->path);
	mm_string_init(&p->host);
	mm_string_init(&p->value_buffer);
	mm_rbtree_u32_vpt_init(&p->rbtree);
	mm_zkwp_path_callback_init(&p->callback);
	mm_spinlock_init(&p->locker, NULL);
	p->unique_id = 0;
	p->shard = 0;
	p->depth = 0;
	p->timeout = MM_ZKWP_PATH_TIMEOUT;
	p->zkhandle = NULL;

	mm_string_assigns(&p->path,"/zkwp");
	mm_string_assigns(&p->host,"127.0.0.1:2181,");

	rbtree_u32_vpt_alloc.alloc = &mm_rbtree_u32_vpt_weak_alloc;
	rbtree_u32_vpt_alloc.relax = &mm_rbtree_u32_vpt_weak_relax;
	rbtree_u32_vpt_alloc.obj = p;
	mm_rbtree_u32_vpt_assign_alloc(&p->rbtree,&rbtree_u32_vpt_alloc);
}
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_destroy(struct mm_zkwp_path* p)
{
	mm_zkwp_path_abandon(p);
	//
	mm_string_destroy(&p->path);
	mm_string_destroy(&p->host);
	mm_string_destroy(&p->value_buffer);
	mm_rbtree_u32_vpt_destroy(&p->rbtree);
	mm_zkwp_path_callback_destroy(&p->callback);
	mm_spinlock_destroy(&p->locker);
	p->unique_id = 0;
	p->shard = 0;
	p->depth = 0;
	p->timeout = 0;
	p->zkhandle = NULL;
}
//////////////////////////////////////////////////////////////////////////
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_assign_unique_id(struct mm_zkwp_path* p,mm_uint32_t unique_id)
{
	p->unique_id = unique_id;
}
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_assign_slot(struct mm_zkwp_path* p,mm_uint32_t shard,mm_uint32_t depth)
{
	p->shard = shard;
	p->depth = depth;
}
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_assign_path(struct mm_zkwp_path* p,const char* path)
{
	assert(NULL != path && "you can not assign null path.");
	mm_string_assigns(&p->path,path);
}
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_assign_host(struct mm_zkwp_path* p,const char* host)
{
	assert(NULL != host && "you can not assign null host.");
	mm_string_assigns(&p->host,host);
}
//////////////////////////////////////////////////////////////////////////
// launch watcher the path and child.
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_watcher(struct mm_zkwp_path* p)
{
	struct mm_logger* g_logger = mm_logger_instance();
	// try abandon first.
	mm_zkwp_path_abandon(p);
	if ( 0 != p->host.l )
	{
		// init a new zk handle.
		p->zkhandle = zookeeper_init(p->host.s, __static_zkwp_path_watcher, p->timeout, 0, p, 0);
		if ( NULL == p->zkhandle ) 
		{
			mm_logger_log_E(g_logger,"%s %d init zookeeper servers failure.",__FUNCTION__,__LINE__);
		}
		else
		{
			mm_logger_log_I(g_logger,"%s %d init zookeeper servers success.",__FUNCTION__,__LINE__);
		}
	}
	else
	{
		mm_logger_log_W(g_logger,"%s %d the host is empty.",__FUNCTION__,__LINE__);
	}
}
// finish abandon the path and child.
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_abandon(struct mm_zkwp_path* p)
{
	if ( NULL != p->zkhandle )
	{
		struct mm_logger* g_logger = mm_logger_instance();
		zookeeper_close(p->zkhandle);
		p->zkhandle = NULL;
		mm_logger_log_I(g_logger,"%s %d close zookeeper servers success.",__FUNCTION__,__LINE__);
	}
}
//////////////////////////////////////////////////////////////////////////
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_lock(struct mm_zkwp_path* p)
{
	mm_spinlock_lock(&p->locker);
}
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_unlock(struct mm_zkwp_path* p)
{
	mm_spinlock_unlock(&p->locker);
}
//////////////////////////////////////////////////////////////////////////
MM_EXPORT_ZOOKEEPER void mm_zkwp_path_commit(struct mm_zkwp_path* p)
{
	__static_zkwp_path_awexists(p);
}
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_watcher(zhandle_t* zh, int type, int state, const char* path, void* watcherCtx)
{
	struct mm_logger* g_logger = mm_logger_instance();
	struct mm_zkwp_path* _zkwp_path = (struct mm_zkwp_path*)(watcherCtx);
	if ( ZOO_SESSION_EVENT == type )
	{
		if ( ZOO_CONNECTED_STATE == state )
		{
			mm_logger_log_I(g_logger,"%s %d %s connected.",__FUNCTION__,__LINE__,path);
			(*(_zkwp_path->callback.net_connect))(_zkwp_path);
			__static_zkwp_path_awexists(_zkwp_path);
		}
		else if ( ZOO_EXPIRED_SESSION_STATE == state )
		{
			mm_logger_log_I(g_logger,"%s %d %s session expired.",__FUNCTION__,__LINE__,path);
			(*(_zkwp_path->callback.net_connect))(_zkwp_path);
			mm_zkwp_path_watcher(_zkwp_path);
		}
		else if ( ZOO_CONNECTING_STATE == state )
		{
			mm_logger_log_I(g_logger,"%s %d %s connecting.",__FUNCTION__,__LINE__,path);
		}
		else
		{
			mm_logger_log_I(g_logger,"%s %d event happened.type:%d state:%d path:%s.",__FUNCTION__,__LINE__,type,state,path);
		}
	}
}
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_awexists_watcher( zhandle_t* zh, int type, int state, const char* path, void* watcherCtx)
{
	struct mm_logger* g_logger = mm_logger_instance();
	struct mm_zkwp_path* _zkwp_path = (struct mm_zkwp_path*)(watcherCtx);
	if (state == ZOO_CONNECTED_STATE) 
	{
		if (type == ZOO_DELETED_EVENT) 
		{
			mm_logger_log_V(g_logger,"%s %d %s deleted...",__FUNCTION__,__LINE__,path);
			(*(_zkwp_path->callback.deleted))(_zkwp_path);
		} 
		else if (type == ZOO_CREATED_EVENT) 
		{
			mm_logger_log_V(g_logger,"%s %d %s created...",__FUNCTION__,__LINE__,path);
			(*(_zkwp_path->callback.created))(_zkwp_path);
		}
		else if (type == ZOO_CHANGED_EVENT) 
		{
			mm_logger_log_V(g_logger,"%s %d %s changed...",__FUNCTION__,__LINE__,path);
			(*(_zkwp_path->callback.changed))(_zkwp_path);
		}
		else if (type == ZOO_CHILD_EVENT) 
		{
			mm_logger_log_V(g_logger,"%s %d %s children...",__FUNCTION__,__LINE__,path);
		}
	}
	// commit not need reset watcher again.if watcher will case dead loop.
	// __static_zkwp_path_awexists(_zkwp_path);
}
static void __static_zkwp_path_awexists_stat_completion( int rc, const struct Stat* stat, const void* data )
{
	struct mm_logger* g_logger = mm_logger_instance();
	struct mm_zkwp_path* _zkwp_path = (struct mm_zkwp_path*)(data);
	if ( ZOK == rc )
	{
		// awexists path/shard.
		__static_zkwp_path_shard_awexists(_zkwp_path);
	}
	else if(ZNONODE == rc)
	{
		// create path.
		__static_zkwp_path_acreate(_zkwp_path);
	}
	else
	{
		mm_logger_log_E(g_logger,"%s %d zerror:%s",__FUNCTION__,__LINE__,zerror(rc));
	}
}
static void __static_zkwp_path_awexists(struct mm_zkwp_path* p)
{
	int ret = zoo_awexists(p->zkhandle, p->path.s, __static_zkwp_path_awexists_watcher, p, __static_zkwp_path_awexists_stat_completion, p);
	if ( ZOK != ret ) 
	{
		struct mm_logger* g_logger = mm_logger_instance();
		mm_logger_log_E(g_logger,"%s %d zerror:%s.",__FUNCTION__,__LINE__,zerror(ret));
	}
}
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_acreate_string_completion(int rc, const char *value, const void *data)
{
	struct mm_logger* g_logger = mm_logger_instance();
	struct mm_zkwp_path* _zkwp_path = (struct mm_zkwp_path*)(data);
	if ( ZOK == rc )
	{
		// awexists path/shard.
		__static_zkwp_path_shard_awexists(_zkwp_path);
	}
	else if(ZNODEEXISTS == rc)
	{
		// awexists path/shard.
		__static_zkwp_path_shard_awexists(_zkwp_path);
	}
	else
	{
		mm_logger_log_E(g_logger,"%s %d zerror:%s",__FUNCTION__,__LINE__,zerror(rc));
	}
}
// acreate path
static void __static_zkwp_path_acreate(struct mm_zkwp_path* p)
{
	// not ZOO_SEQUENCE
	int ret = zoo_acreate(p->zkhandle, p->path.s, "", 0, &ZOO_OPEN_ACL_UNSAFE, 0, __static_zkwp_path_acreate_string_completion, p);
	if ( ZOK != ret ) 
	{
		struct mm_logger* g_logger = mm_logger_instance();
		mm_logger_log_E(g_logger,"%s %d zerror:%s.",__FUNCTION__,__LINE__,zerror(ret));
	}
}
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_shard_awexists_watcher( zhandle_t* zh, int type, int state, const char* path, void* watcherCtx)
{
	// struct mm_zkwp_path* _zkwp_path = (struct mm_zkwp_path*)(watcherCtx);
	// commit not need reset watcher again.if watcher will case dead loop.
	// __static_zkwp_path_shard_awexists(_zkwp_path);
}
static void __static_zkwp_path_shard_awexists_stat_completion( int rc, const struct Stat* stat, const void* data )
{
	struct mm_logger* g_logger = mm_logger_instance();
	struct mm_zkwp_path* _zkwp_path = (struct mm_zkwp_path*)(data);
	if ( ZOK == rc )
	{
		// awexists path/shard/unique_id
		__static_zkwp_path_shard_unique_id_awexists(_zkwp_path);
	}
	else if(ZNONODE == rc)
	{
		// acreate path/shard
		__static_zkwp_path_shard_acreate(_zkwp_path);
	}
	else
	{
		mm_logger_log_E(g_logger,"%s %d zerror:%s",__FUNCTION__,__LINE__,zerror(rc));
	}
}
// awexists path/shard
static void __static_zkwp_path_shard_awexists(struct mm_zkwp_path* p)
{
	char path[128];
	int ret = 0;
	mm_sprintf(path,"%s/" MM_ZKWP_FORMAT_1,p->path.s,p->shard);
	ret = zoo_awexists(p->zkhandle, path, __static_zkwp_path_shard_awexists_watcher, p, __static_zkwp_path_shard_awexists_stat_completion, p);
	if ( ZOK != ret ) 
	{
		struct mm_logger* g_logger = mm_logger_instance();
		mm_logger_log_E(g_logger,"%s %d zerror:%s.",__FUNCTION__,__LINE__,zerror(ret));
	}
}
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_shard_acreate_string_completion(int rc, const char *value, const void *data)
{
	struct mm_logger* g_logger = mm_logger_instance();
	struct mm_zkwp_path* _zkwp_path = (struct mm_zkwp_path*)(data);
	if ( ZOK == rc )
	{
		// awexists path/shard/unique_id
		__static_zkwp_path_shard_unique_id_awexists(_zkwp_path);
	}
	else if ( ZNODEEXISTS == rc )
	{
		// awexists path/shard/unique_id
		__static_zkwp_path_shard_unique_id_awexists(_zkwp_path);
	}
	else
	{
		mm_logger_log_E(g_logger,"%s %d zerror:%s",__FUNCTION__,__LINE__,zerror(rc));
	}
}
// acreate path/shard
static void __static_zkwp_path_shard_acreate(struct mm_zkwp_path* p)
{
	char path[128] = {0};
	int ret = 0;
	mm_sprintf(path,"%s/" MM_ZKWP_FORMAT_1,p->path.s,p->shard);
	ret = zoo_acreate(p->zkhandle, path, "", 0, &ZOO_OPEN_ACL_UNSAFE, 0, __static_zkwp_path_shard_acreate_string_completion, p);
	if ( ZOK != ret ) 
	{
		struct mm_logger* g_logger = mm_logger_instance();
		mm_logger_log_E(g_logger,"%s %d zerror:%s.",__FUNCTION__,__LINE__,zerror(ret));
	}
}
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_shard_unique_id_awexists_watcher( zhandle_t* zh, int type, int state, const char* path, void* watcherCtx)
{
	// struct mm_zkwp_path* _zkwp_path = (struct mm_zkwp_path*)(watcherCtx);
	// commit not need reset watcher again.if watcher will case dead loop.
	// __static_zkwp_path_shard_unique_id_awexists(_zkwp_path);
}
static void __static_zkwp_path_shard_unique_id_awexists_stat_completion( int rc, const struct Stat* stat, const void* data )
{
	struct mm_logger* g_logger = mm_logger_instance();
	struct mm_zkwp_path* _zkwp_path = (struct mm_zkwp_path*)(data);
	if ( ZOK == rc )
	{
		// aset path/module_id/unique_id
		__static_zkwp_path_shard_unique_id_aset(_zkwp_path);
	}
	else if(ZNONODE == rc)
	{
		// acreate path/module_id/unique_id
		__static_zkwp_path_shard_unique_id_acreate(_zkwp_path);
	}
	else
	{
		mm_logger_log_E(g_logger,"%s %d zerror:%s",__FUNCTION__,__LINE__,zerror(rc));
	}
}
// awexists path/shard/unique_id
static void __static_zkwp_path_shard_unique_id_awexists(struct mm_zkwp_path* p)
{
	char path[128];
	int ret = 0;
	mm_sprintf(path,"%s/" MM_ZKWP_FORMAT_1 "/" MM_ZKWP_FORMAT_2,p->path.s,p->shard,p->depth);
	ret = zoo_awexists(p->zkhandle, path, __static_zkwp_path_shard_unique_id_awexists_watcher, p, __static_zkwp_path_shard_unique_id_awexists_stat_completion, p);
	if ( ZOK != ret ) 
	{
		struct mm_logger* g_logger = mm_logger_instance();
		mm_logger_log_E(g_logger,"%s %d zerror:%s.",__FUNCTION__,__LINE__,zerror(ret));
	}
}
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_shard_unique_id_acreate_string_completion(int rc, const char *value, const void *data)
{
	struct mm_logger* g_logger = mm_logger_instance();
	struct mm_zkwp_path* _zkwp_path = (struct mm_zkwp_path*)(data);
	if ( ZOK == rc )
	{
		// aset path/shard/unique_id
		__static_zkwp_path_shard_unique_id_aset(_zkwp_path);
	}
	else if ( ZNODEEXISTS == rc )
	{
		// aset path/shard/unique_id
		__static_zkwp_path_shard_unique_id_aset(_zkwp_path);
	}
	else
	{
		mm_logger_log_E(g_logger,"%s %d zerror:%s",__FUNCTION__,__LINE__,zerror(rc));
	}
}
// acreate path/shard/unique_id
static void __static_zkwp_path_shard_unique_id_acreate(struct mm_zkwp_path* p)
{
	char path[128];
	int ret = 0;
	mm_sprintf(path,"%s/" MM_ZKWP_FORMAT_1 "/" MM_ZKWP_FORMAT_2,p->path.s,p->shard,p->depth);
	ret = zoo_acreate(p->zkhandle, path, p->value_buffer.s, p->value_buffer.l, &ZOO_OPEN_ACL_UNSAFE, ZOO_EPHEMERAL, __static_zkwp_path_shard_unique_id_acreate_string_completion, p);
	if ( ZOK != ret ) 
	{
		struct mm_logger* g_logger = mm_logger_instance();
		mm_logger_log_E(g_logger,"%s %d zerror:%s.",__FUNCTION__,__LINE__,zerror(ret));
	}
}
//////////////////////////////////////////////////////////////////////////
static void __static_zkwp_path_shard_unique_id_aset_stat_completion(int rc, const struct Stat *stat, const void *data)
{
	struct mm_logger* g_logger = mm_logger_instance();
	if ( ZOK != rc )
	{
		mm_logger_log_E(g_logger,"%s %d zerror:%s",__FUNCTION__,__LINE__,zerror(rc));
	}
}
// aset path/shard/unique_id
static void __static_zkwp_path_shard_unique_id_aset(struct mm_zkwp_path* p)
{
	char path[128] = {0};
	int ret = 0;
	mm_sprintf(path,"%s/" MM_ZKWP_FORMAT_1 "/" MM_ZKWP_FORMAT_2,p->path.s,p->shard,p->depth);
	ret = zoo_aset(p->zkhandle, path, p->value_buffer.s, p->value_buffer.l, -1, __static_zkwp_path_shard_unique_id_aset_stat_completion,p);
	if ( ZOK != ret ) 
	{
		struct mm_logger* g_logger = mm_logger_instance();
		mm_logger_log_E(g_logger,"%s %d zerror:%s.",__FUNCTION__,__LINE__,zerror(ret));
	}
}
////////////////////////////////////////////////////////////////////////////