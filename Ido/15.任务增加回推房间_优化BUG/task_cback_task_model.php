<?php

$path=dirname(__FILE__);
include_once "$path/redis_interfun.php";
$path=dirname(__FILE__);
include_once "$path/../../bases/GlobalConfig.php";
class task_cback_task_model
{
	public function trigger($data)
	{
		$real_data = json_encode($data);
		
		$redis = getCbackRedis();

		if (empty($redis)) {
			LogApi::logProcess("task_cback_task_model:trigger redis null. data:$real_data");
			return;
		}

		$redis->lpush(GlobalConfig::GetCbackQueueTask(), $real_data);
	}
}

?>