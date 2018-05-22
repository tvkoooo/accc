<?php

class cback_task_model extends ModelBase
{
	public function trigger($data)
	{
		$real_data = json_encode($data);
		
		$redis = $this->getRedisCback();

		if (empty($redis)) {
			LogApi::logProcess("cback_task_model:trigger redis null. data:$real_data");
			return;
		}

		$redis->lpush(GlobalConfig::GetCbackQueueTask(), $real_data);
	}
}

?>