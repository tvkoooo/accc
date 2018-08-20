<?php

$path=dirname(__FILE__);
include_once "$path/task_packet_model.php" ;
$path=dirname(__FILE__);
include_once "$path/redis_interfun.php";
$path=dirname(__FILE__);
include_once "$path/../../bases/GlobalConfig.php";
class task_cback_channel_model
{
	public function broadcast($sid, $data)
	{
		$real_data = array(
			'type' => 30,
			'sid' => intval($sid),
			'all' => true,
			'data' => $data
		);

		$real_data = json_encode($real_data);

		$model = task_packet_model::new_broadcast($sid, $real_data);

		$rs = $model->pack();

		$redis = getCbackRedis();

		if (empty($redis)) {
        	LogApi::logProcess("task_cback_channel_model:broadcast redis null. data:$real_data");
			return;
		}

		$redis->lpush(GlobalConfig::GetCbackQueueChannel(), $rs);
	}

	public function multicast($sid, $uids, $data)
	{
		$real_data = array(
			'type' => 30,
			'sid' => intval($sid),
			'all' => false,
			'to_uids' => $uids,
			'data' => $data
		);

		$real_data = json_encode($real_data);

		$model = task_packet_model::new_multicast($sid, $real_data, $uids);

		$rs = $model->pack();

		$redis = getCbackRedis();

		if (empty($redis)) {
			LogApi::logProcess("task_cback_channel_model:multicast redis null. data:$real_data");
			return;
		}

		$redis->lpush(GlobalConfig::GetCbackQueueChannel(), $rs);
	}

	public function unicast($sid, $uid, $data)
	{

		$real_data = array(
			'type' => 30,
			'sid' => intval($sid),
			'all' => false,
			'to_uids' => array($uid),
			'data' => $data
		);

		$real_data = json_encode($real_data);

		$model = task_packet_model::new_unicast($sid, $uid, $real_data);

		$rs = $model->pack();

		$redis = getCbackRedis();

		if (empty($redis)) {
			LogApi::logProcess("task_cback_channel_model:unicast redis null. data:$real_data");
			return;			
		}

		$redis->lpush(GlobalConfig::GetCbackQueueChannel(), $rs);
	}
}
?>