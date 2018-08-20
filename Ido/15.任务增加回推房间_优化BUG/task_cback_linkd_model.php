<?php

$path=dirname(__FILE__);
include_once "$path/task_task_packet_model.php" ;
$path=dirname(__FILE__);
include_once "$path/redis_interfun.php"; 
$path=dirname(__FILE__);
include_once "$path/../../bases/GlobalConfig.php";
class task_cback_linkd_model 
{
	public function broadcast($data)
	{

		$real_data = array(
			'type' => 0,
			'all' => true,
			'data' => $data
		);

		$real_data = json_encode($real_data);

		$model = task_packet_model::new_broadcast(-1, $real_data);

		$rs = $model->pack();

		$redis = getCbackRedis();

		if (empty($redis)) {
			LogApi::logProcess("task_cback_linkd_model:broadcast redis null. data:$real_data");
			return;
		}

		$redis->lpush(GlobalConfig::GetCbackQueueLinkd(), $rs);
	}

	public function multicast($uids, $data)
	{

		$real_data = array(
			'type' => 0,
			'all' => false,
			'data' => $data,
			'to_uids' => $uids
		);

		$real_data = json_encode($real_data);

		$model = task_packet_model::new_multicast(0, $real_data, $uids);

		$rs = $model->pack();

		$redis = getCbackRedis();

		if (empty($redis)) {
			LogApi::logProcess("task_cback_linkd_model:multicast redis null. uids:" . json_encode($uids) . " data:$real_data");
			return;
		}

		$redis->lpush(GlobalConfig::GetCbackQueueLinkd(), $rs);
	}

	public function unicast($uid, $data)
	{

		$real_data = array(
			'type' => 0,
			'all' => false,
			'data' => $data['data'],
			'to_uids' => array($uid)
		);

		$real_data = json_encode($real_data);
		
		$model = task_packet_model::new_unicast(0, $real_data);

		$rs = $model->pack();

		$redis = getCbackRedis();

		if (empty($redis)) {
			LogApi::logProcess("task_cback_linkd_model:unicast redis null. data:$real_data");
			return;
		}

		$redis->lpush(GlobalConfig::GetCbackQueueLinkd(), $rs);
	}
}
?>