<?php

class cback_channel_model extends ModelBase
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

		$model = packet_model::new_broadcast($sid, $real_data);

		$rs = $model->pack();

		$redis = $this->getRedisCback();

		if (empty($redis)) {
        	LogApi::logProcess("cback_channel_modle:broadcast redis null. data:$real_data");
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

		$model = packet_model::new_multicast($sid, $real_data, $uids);

		$rs = $model->pack();

		$redis = $this->getRedisCback();

		if (empty($redis)) {
			LogApi::logProcess("cback_channel_modle:multicast redis null. data:$real_data");
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

		$model = packet_model::new_unicast($sid, $uid, $real_data);

		$rs = $model->pack();

		$redis = $this->getRedisCback();

		if (empty($redis)) {
			LogApi::logProcess("cback_channel_modle:unicast redis null. data:$real_data");
			return;			
		}

		$redis->lpush(GlobalConfig::GetCbackQueueChannel(), $rs);
	}
}
?>