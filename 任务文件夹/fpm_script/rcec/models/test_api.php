<?php

class test_api
{
	public static function on_test_multicast_packet($params)
	{
		LogApi::logProcess("test_api:on_test_multicast_packet:" . json_encode($params));
		$model_test = new test_model();

		$mid = isset($params['mid'])? intval($params['mid']) : 0;
		$pid = isset($params['pid'])? intval($params['pid']) : 0;
		$sid = isset($params['sid'])? intval($params['sid']) : 0;
		$uid = isset($params['uid'])? intval($params['uid']) : 0;
		$type = isset($params['cast_type'])? intval($params['cast_type']) : 0;

		$data = isset($params['data']) ? $params['data'] : "";
		$uids = $params['uids'];

		$model = new test_model();
		$model->on_test_multicast_packet($mid, $pid, $sid, $uid, $data, $uids, $type);
	}
}
?>