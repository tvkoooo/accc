<?php

/**
* 
*/
class sun_income_task_api extends ModelBase
{
	
	public static function on_guard_received_rq($params)
	{
		 LogApi::logProcess("sun_income_task_api:on_guard_received_rq:" . json_encode($params));

		 $result = array(
		 		'cmd' => 'on_guard_received_rs',
		 		'result' => 0,
		 		'uid' => $params['uid'],
		 		'anchor_id' => $params['anchor_id'],
		 		'money' => $params['money'],
		 		'timestamp' => $params['timestamp'] 
		 );

		 $uid = isset($params['uid']) ? intval($params['uid']) : 0;
		 $anchor_id = isset($params['anchor_id']) ? intval($params['anchor_id']) : 0;
		 $money = isset($params['money']) ? intval($params['money']) : 0;
		 $timestamp = isset($params['timestamp']) ? intval($params['timestamp']) : 0;

		 do {
		 	if (empty($uid) || empty($anchor_id) || empty($timestamp)) {
		 		$result['result'] = 201;
		 		break;
		 	}

		 	$model_sun_income_task = new sun_income_task_model();
		 	$model_sun_income_task->on_guard_received($anchor_id, $money);
		 } while (0);

		 LogApi::logProcess("sun_income_task_api:on_guard_received_rq rs:" . json_encode($result));

		 return $result;
	}

	public static function on_fans_group_sun_peek_rq($params)
	{
		 LogApi::logProcess("sun_income_task_api:on_fans_group_sun_peek_rq:" . json_encode($params));

		  $result = array(
		 		'cmd' => 'on_fans_group_sun_peek_rs',
		 		'result' => 0,
		 		'uid' => $params['uid'],
		 		'sunshine' => $params['sunshine'],
		 		'timestamp' => $params['timestamp'] 
		 );

		 $uid = isset($params['uid']) ? intval($params['uid']) : 0;
		 $sunshine = isset($params['sunshine']) ? $params['sunshine'] : 0;
		 $timestamp = isset($params['timestamp']) ? intval($params['timestamp']) : 0;

		 do {
		 	if (empty($uid) || empty($sunshine) || empty($timestamp)) {
		 		$result['result'] = 201;
		 		break;
		 	}

		 	$model_sun_income_task = new sun_income_task_model();
		 	$model_sun_income_task->on_fans_group_sun_peek($uid);
		 } while (0);

		 LogApi::logProcess("sun_income_task_api:on_fans_group_sun_peek_rq rs:" . json_encode($result));

		 return $result;
	}

	public static function on_task_sun_award_rq($params)
	{
		 LogApi::logProcess("sun_income_task_api:on_task_sun_award_rq:" . json_encode($params));

		  $result = array(
		 		'cmd' => 'on_task_sun_award_rs',
		 		'result' => 0,
		 		'uid' => $params['uid'],
		 		'sunshine' => $params['sunshine'],
		 		'timestamp' => $params['timestamp'] 
		 );

		 $uid = isset($params['uid']) ? intval($params['uid']) : 0;
		 $sunshine = isset($params['sunshine']) ? $params['sunshine'] : 0;
		 $timestamp = isset($params['timestamp']) ? intval($params['timestamp']) : 0;

		 do {
		 	if (empty($uid) || empty($sunshine) || empty($timestamp)) {
		 		$result['result'] = 201;
		 		break;
		 	}

		 	$model_sun_income_task = new sun_income_task_model();
		 	$model_sun_income_task->on_task_sun_award($uid);
		 } while (0);

		 LogApi::logProcess("sun_income_task_api:on_task_sun_award_rq rs:" . json_encode($result));

		 return $result;
	}

	public function on_test_rq($params)
	{
		LogApi::logProcess("sun_income_task_api:on_test_rq:" . json_encode($params));
		$uid = $params['uid'];
		$level = $params['level'];

		$result = array(
			'cmd' => 'on_test_sun_income_rs',
			'result' => 0
		);

		$model_sun_income_task = new sun_income_task_model();
		$model_sun_income_task->on_test($uid, $level);

		LogApi::logProcess("sun_income_task_api:on_test_rq rs:" . json_encode($result));

		return $result;
	}
}
?>