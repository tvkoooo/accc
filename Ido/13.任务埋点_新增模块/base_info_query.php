<?

$path=dirname(__FILE__);

require_once "$path/LogApi.php";

class base_info_query 
{
	var $redis;
	var $db;

	public function __construct($redis, $db)
	{	    
        $this->redis = $redis;
        $this->db = $db;
	}

	//基础函数，uid查找用户信息
	public function base_uid2userInfo($uid,&$userInfo)
	{
	    $error['code'] = -1;
	    $error['desc'] = '未知错误';
	    do
	    {	        
	        $key = "uid:$uid";
	        $get_userInfo = $this->redis->get($key);
	        if(true == empty($get_userInfo))
	        {
	            // 100000103(103)执行命令失败
	            $error['code'] = 100000103;
	            $error['desc'] = '执行命令失败';
	            break;
	        }
	        
	        $userInfo_dec = json_decode($get_userInfo, true);
	        if(true == empty($userInfo_dec))
	        {
	            // 100000001(001)解包失败
	            $error['code'] = 100000001;
	            $error['desc'] = '解包失败';
	            break;
	        }
	        $userInfo = $userInfo_dec;
	        //
	        $error['code'] = 0;
	        $error['desc'] = '';
	    }while(0);
	    if (0 != $error['code'])
	    {
	        LogApi::logProcess("base_info_query.base_uid2userInfo error:".json_encode($error));
	    }	
	}
	
	//基础函数，task_id(具体任务实例id) 查找 t_id（任务id） 和t_type（任务类型） 和 $target_type（目标类型）
	public function base_taskId2taskType($task_id, &$taskInfo)
	{
	    $error['code'] = -1;
	    $error['desc'] = '未知错误';
	    do
	    {
	        $m_key = "maidian:taskid:$task_id" ;
	        $get_taskInfo = $this->redis->hGet($m_key,"taskinfo");
	        //LogApi::logProcess("ljljlj  m_key:$m_key get_taskInfo:$get_taskInfo");
	        if(true == empty($get_taskInfo))
	        {
	            // 100000103(103)执行命令失败
	            $error['code'] = 100000103;
	            $error['desc'] = '执行命令失败';
	            break;
	        }
	        $taskInfo_dec = json_decode($get_taskInfo, true);
	        if(true == empty($taskInfo_dec))
	        {
	            // 100000001(001)解包失败
	            $error['code'] = 100000001;
	            $error['desc'] = '解包失败';
	            break;
	        }
	        $taskInfo = $taskInfo_dec;
	        //
	        $error['code'] = 0;
	        $error['desc'] = '';
	    }while(0);
	    if (0 != $error['code'])
	    {
	        LogApi::logProcess("base_info_query.base_taskId2taskType error:".json_encode($error));
	    }

	}	

//  本函数因查库太频繁禁用	
//	//基础函数，task_id(具体任务实例id) 查找 t_id（任务id） 和t_type（任务类型） 和 $target_type（目标类型）
// 	public function base_taskId2taskType_sql($task_id, &$t_id,&$t_type,&$target_type)
// 	{
// 	    $error['code'] = -1;
// 	    $error['desc'] = '未知错误';
// 	    do
// 	    {
// 	        $sql = "select t.t_id,t.t_type, tc.target_type from card.task_info t ".
// 	            "left join card.task_conf tc on t.t_id = tc.id where t.id = $task_id for update" ;
// 	        $rows = $this->db->query($sql);
// 	        if (null == $rows)
// 	        {
// 	            // query failure.
// 	            // 100000101(101)执行存储过程失败
// 	            $error['code'] = 100000101;
// 	            $error['desc'] = '执行存储过程失败';
// 	            break;
// 	        }
// 	        $row = $this->db->fetch_assoc($rows);
	
// 	        $t_id = (int)$row['t_id'];
// 	        $t_type = (int)$row['t_type'];
// 	        $target_type = (int)$row['target_type'];
	
// 	        $error['code'] = 0;
// 	        $error['desc'] = '';
// 	    }while(0);
// 	    if (0 != $error['code'])
// 	    {
// 	        LogApi::logProcess("base_info_query.base_taskId2taskType error:".json_encode($error));
// 	    }
// 	}
	
	//基础函数，union_id查找 帮会信息
	public function base_unionId2unionInfo($union_id,&$union_info)
	{
	    $error['code'] = -1;
	    $error['desc'] = '未知错误';
	    do
	    {     

	        $key = "mysql:get:unioninfo:$union_id";
	        $union_info_json = $this->redis->hGet($key,$union_id);
	        if(true == empty($union_info_json))
	        {
	            $sql = "select * from raidcall.union_info where id = $union_id";
	            $rows = $this->db->query($sql);
	            if (null == $rows)
	            {
	                // query failure.
	                // 100000101(101)执行存储过程失败
	                $error['code'] = 100000101;
	                $error['desc'] = '执行存储过程失败';
	                break;
	            }
	            $row = $this->db->fetch_assoc($rows);
	            //保存数据库mysql资源进缓存
	            $union_info = $row;

	            $key_life = 5 * 60;//给个默认时间是5分钟刷新
	            $this->redis->hSet($key,$union_id,json_encode($union_info));
	            $this->redis->expire($key, $key_life);
	        }
	        else 
	        {
	            $union_info_dec = json_decode($union_info_json, true);
	            if(true == empty($union_info_dec))
	            {
	                // 100000001(001)解包失败
	                $error['code'] = 100000001;
	                $error['desc'] = '解包失败';
	                break;
	            }
	            $union_info  = $union_info_dec;
	        }

	        //
	        $error['code'] = 0;
	        $error['desc'] = '';
	    }while(0);
	    if (0 != $error['code'])
	    {
	        LogApi::logProcess("base_info_query.base_unionId2unionInfo error:".json_encode($error));
	    }
	}
	
	//基础函数，union_id 查询此刻 帮会钥匙数量
	public function base_unionId2unionKeyNum($union_id,&$union_key_num)
	{
	    $error['code'] = -1;
	    $error['desc'] = '未知错误';
	    do
	    {
	
	        $key_key = "union:key:unionid:$union_id";
	        $get_union_key_num = $this->redis->get($key_key);
	        if(true == empty($get_union_key_num))
	        {
	            $get_union_key_num = 0;
	        }
	        $union_key_num = $get_union_key_num;	
	        //
	        $error['code'] = 0;
	        $error['desc'] = '';
	    }while(0);
	    if (0 != $error['code'])
	    {
	        LogApi::logProcess("base_info_query.base_unionId2unionKeyNum error:".json_encode($error));
	    }
	}
	
	//基础函数，union_id 查询此刻(实时，直接查库的信息) 帮会信息
	public function base_unionId2unionInfo_real_time($union_id,&$union_info)
	{
	    $error['code'] = -1;
	    $error['desc'] = '未知错误';
	    do
	    {	
	        $sql = "select * from raidcall.union_info where id = $union_id for update";
	        $rows = $this->db->query($sql);
	        if (null == $rows)
	        {
	            // query failure.
	            // 100000101(101)执行存储过程失败
	            $error['code'] = 100000101;
	            $error['desc'] = '执行存储过程失败';
	            break;
	        }
	        $row = $this->db->fetch_assoc($rows);
	        $union_info = $row;
	
	        //
	        $error['code'] = 0;
	        $error['desc'] = '';
	    }while(0);
	    if (0 != $error['code'])
	    {
	        LogApi::logProcess("base_info_query.base_unionId2unionInfo_real_time error:".json_encode($error));
	    }
	}

};



?>
