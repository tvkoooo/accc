<?php 
class star_top_spot_model extends ModelBase 
{
    // hash last_tool_week_max_zuid {field:10086,value:uid}
	const STRING_KEY_LAST_WEEK_STAR_TOP = 'last_tool_week_max_zuid';
	
	public function redis_string_last_week_star_top_key()
	{
		return star_top_spot_model::STRING_KEY_LAST_WEEK_STAR_TOP;
	}
	
	public function redis_get_last_week_star_top_uid(&$error, $singer_id, &$uid)
	{
	    $uid = 0;
	    
        $error['code'] = -1;
        $error['desc'] = '未知错误';
	    do
	    {
	        $redis = $this->getRedisMaster();
	        if(null == $redis)
	        {
	            // 100000701(701)网络数据库断开连接
	            $error['code'] = 100000701;
	            $error['desc'] = '网络数据库断开连接';
	            break;
	        }
	        $key = $this->redis_string_last_week_star_top_key();
	        $redis_uid = $redis->hGet($key, $singer_id);
	        if (true == empty($redis_uid))
	        {
	            // need do nothing.
	            break;
	        }
	        $uid = $redis_uid;
	        
	        $error['code'] = 0;
	        $error['desc'] = '';
	    }while (0);
	}
}
?>