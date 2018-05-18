<?php

class task_dispatch_model extends ModelBase
{

	const redis_key_task_list = "list.task.request";
	public function dispatch($uid, $target_type, $num, $extra_param)
	{
		$data = array(
			'uid' => intval($uid),
			'target_type' => intval($target_type),
			'num' => intval($num),
			'extra_param' => $extra_param
		);

		$model = new cback_task_model();
		$model->trigger($data);
	}
}
?>