<?php

$path=dirname(__FILE__);
include_once "$path/task_cback_channel_model.php" ;
include_once "$path/task_cback_linkd_model.php" ;
include_once "$path/task_cback_task_model.php" ;
class task_cback_api
{
	public static function deal_cback($sid, $result)
	{
		if (!is_array($result)) {
			return $result;
		}
		
		$return = array();

		foreach ($result as $rs) {
			$broadcast_type = isset($rs['broadcast']) ? $rs['broadcast'] : -1;
			switch ($broadcast_type) {
				case 1:
				case 2:
				case 3:
					// session brodcast
					task_cback_api::cback_channel_broadcast($sid, $rs);
					break;
				case 4:
					// linkd broadcast
					task_cback_api::cback_linkd_broadcast($sid, $rs);
					break;
				case 5:
					// task trigger
					task_cback_api::cback_trigger_task($sid, $rs);
					break;
				case 6:
					task_cback_api::cback_channel_unicast($sid, $rs);
					// session unicast
					break;
				case 7:
					// linkd unicast
					task_cback_api::cback_linkd_unicast($sid, $rs);
					break;
				case 8:
					// linkd multicast
					task_cback_api::cback_linkd_multicast($sid, $rs);
					break;
				default:
					// response
					$return[] = $rs;
					break;
			}
		}

		return $return;
	}



	private static function cback_channel_broadcast($sid, $data)
	{
		$real_data = isset($data['data']) ? $data['data'] : "";

		if (empty($sid) || empty($real_data)) {
			LogApi::logProcess("task_cback_api:cback_channel_broadcast invalid. sid:$sid data:" . json_encode($data));
		}

		$model = new task_cback_channel_model();
		$model->broadcast($sid, $real_data);
	}

	private static function cback_channel_multicast($sid, $data)
	{
		$real_data = isset($data['data']) ? $data['data'] : "";

		$target_uids = isset($data['uids']) ? $data['uids'] : array();

		if (count($target_uids) == 0 || empty($real_data) || empty($sid)) {
			LogApi::logProcess("task_cback_api:cback_channel_multicast invalid. sid:$sid uids:" . json_encode($target_uids) . " data:" . json_encode($data));
			return;
		}

		$model = new task_cback_channel_model();
		$model->multicast($sid, $target_uids, $real_data);
	}

	private static function cback_channel_unicast($sid, $data)
	{
		$real_data = isset($data['data']) ? $data['data'] : "";

		$target_uid = isset($data['target_uid']) ? intval($data['target_uid']) : 0;

		if ($target_uid == 0 || empty($sid) || empty($real_data)) {
			LogApi::logProcess("task_cback_api:cback_channel_unicast invalid. sid:$sid uid:$target_uid data:" . json_encode($data));
			return;
		}

		$model = new task_cback_channel_model();
		$model->unicast($sid, $uid, $real_data);
	}

	private static function cback_linkd_broadcast($sid, $data)
	{
		$real_data = isset($data) ? $data['data'] : "";

		if (empty($real_data)) {
			LogApi::logProcess("task_cback_api:cback_linkd_broadcast invalid. data:" . json_encode($data));
			return;
		}

		$model = new task_cback_linkd_model();
		$model->broadcast($real_data);
	}

	private static function cback_linkd_multicast($sid, $data)
	{
		$real_data = isset($data['data']) ? $data['data'] : "";

		$target_uids = isset($data['uids']) ? $data['uids'] : array();

		if (count($target_uids) == 0 || empty($real_data)) {
			LogApi::logProcess("task_cback_api:cback_linkd_multicast invalid. uids:" . json_encode($target_uids) . " data:" . json_encode($data));
		}

		$model = new task_cback_linkd_model();
		$model->multicast($target_uids, $real_data);
	}

	private static function cback_linkd_unicast($sid, $data)
	{
		$real_data = isset($data['data']) ? $data['data'] : "";

		$target_uid = isset($data['target_uid']) ? $data['target_uid'] : 0;

		if ($target_uid == 0 || empty($real_data)) {
			LogApi::logProcess("task_cback_api:cback_linkd_unicast invalid. uid:$uid data:" . json_encode($data));
			return;
		}

		$model = new task_cback_linkd_model();
		$model->unicast($target_uid, $real_data);
	}

	private static function cback_trigger_task($sid, $data)
	{
		$data = isset($data['data']) ? $data['data'] : "";

		$model = new task_cback_task_model();

		$model->trigger($data);
	}
}
?>