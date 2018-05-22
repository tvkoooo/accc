<?php

class test_model extends ModelBase
{
	public function on_test_multicast_packet($mid, $pid, $sid, $uid, $data, $uids, $type)
	{

		$model = null;
		switch ($type) {
			case 0:				// unicast
				$model = packet_model::new_unicast($sid, $uid, $data);
				break;
			case 1:				// multicast
				$model = packet_model::new_multicast($sid, $data, $uids);
				break;
			case 2:				// broadcast
				$model = packet_model::new_broadcast($sid, $data);
				break;
			default:
				break;
		}

		if (!empty($model)) {
			$result = $model->pack();

			$redis = $this->getRedisCback();

			$redis->lpush(GlobalConfig::GetCbackQueueChannel(), $result);
		}
	}
}

?>