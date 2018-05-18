<?php 

class evt_user_ae_exp_add 
{
	public $uid;
	public $sid;
	public $tid;
	public $exp;
}

class evt_user_rh_exp_add 
{
	public $uid;
	public $sid;
	public $tid;
	public $exp;
}

class evt_anchor_se_exp_add 
{
	public $uid;
	public $sid;
	public $tid;
	public $exp;
}

class evt_anchor_ll_exp_add 
{
	public $uid;
	public $sid;
	public $tid;
	public $exp;
}

class evt_tool_consume_add
{
	public $tid;
	public $uid;
	public $receiver_uid;
	public $sid;
	public $cid;
	public $tool_id;
	public $tool_category1;
	public $tool_category2;
	public $qty;
	public $buy;
	public $tool_price;
	public $total_coins_cost;
	public $total_receiver_points;
	public $total_receiver_charm;
	public $total_session_points;
	public $total_session_charm;
	public $base_percentage;
	public $prize_upper_limit;
	public $give_back_fund;
	public $union_income;
	public $sys_control_fund;
	public $sys_income;
	public $union_id;
	public $union_earnings;
	public $union_back_fund;
	public $union_prize_budget;
	public $union_sun_num;
	public $anchor_sun_num;
	public $family_id;
	public $record_time;
}

class evt_week_tool_consume_add
{
	public $tid;
	public $uid;
	public $receiver_uid;
	public $tool_id;
	public $tool_category1;
	public $tool_category2;
	public $qty;
	public $tool_price;
	public $total_coins_cost;
	public $record_time;
}

class evt_dispatch_model extends ModelBase 
{
	const queue_evt_user_active_exp = "vnc.evt.fishing.user.active_exp";
	const queue_evt_user_rich_exp = "vnc.evt.fishing.user.rich_exp";
	const queue_evt_anchor_sunshine_exp = "vnc.evt.fishing.anchor.sunshine_exp";
	const queue_evt_anchor_level_exp = "vnc.evt.fishing.anchor.level_exp";
	const queue_evt_tool_consume = "vnc.evt.fishing.record.tool_consume";
	const queue_evt_week_tool_consume = "vnc.evt.fishing.record.week_tool_consume";


	private function get_queue_name($queue, $id) 
	{
		return $queue . "." . ($id % GlobalConfig::GetGProcs() + 1);
	}
	// tid 格式
	// tid:$type:$uid:$anchor_id:$mt_rand
	const type_gift_normal = 1;
	const type_gift_sunshine = 2;
	const type_gift_gticket = 3;
	const type_gift_effect = 4;
	const type_guard = 11;
	public function get_transaction_id($type, $uid, $anchor_id)
	{
		$r = mt_rand();

		return "tid:$type:$uid:$anchor_id:$r";
	}

	public function trigger_evt_user_active_exp_add ($uid, $sid, $tid, $exp) 
	{
		$data = array(
			'uid' => intval($uid),
			'sid' => intval($sid),
			'tid' => $tid,
			'exp' => intval($exp)
		);

		$redis = $this->getRedisJavaUtil();

		if (empty($redis)) {
        	LogApi::logProcess("evt_dispatch_model:trigger_evt_user_active_exp_add faliure:" . json_encode($data));
        	return;
		}

		$redis->lpush($this->get_queue_name(evt_dispatch_model::queue_evt_user_active_exp, $uid), json_encode($data));

		LogApi::logProcess("evt_dispatch_model:trigger_evt_user_active_exp_add data:" . json_encode($data));
	}

	public function trigger_evt_user_rich_exp_add($uid, $sid, $tid, $exp)
	{
		$data = array(
			'uid' => intval($uid),
			'sid' => intval($sid),
			'tid' => $tid,
			'exp' => intval($exp)
		);

		$redis = $this->getRedisJavaUtil();

		if (empty($redis)) {
        	LogApi::logProcess("evt_dispatch_model:trigger_evt_user_rich_exp_add faliure:" . json_encode($data));
        	return;
		}

		$redis->lpush($this->get_queue_name(evt_dispatch_model::queue_evt_user_rich_exp, $uid), json_encode($data));

		LogApi::logProcess("evt_dispatch_model:trigger_evt_user_rich_exp_add data:" . json_encode($data));
	}

	public function trigger_evt_anchor_se_exp_add($uid, $sid, $tid, $exp)
	{
		$data = array(
			'uid' => intval($uid),
			'sid' => intval($sid),
			'tid' => $tid,
			'exp' => intval($exp)
		);

		$redis = $this->getRedisJavaUtil();

		if (empty($redis)) {
        	LogApi::logProcess("evt_dispatch_model:trigger_evt_anchor_se_exp_add faliure:" . json_encode($data));
        	return;
		}

		$redis->lpush($this->get_queue_name(evt_dispatch_model::queue_evt_anchor_sunshine_exp, $uid), json_encode($data));

		LogApi::logProcess("evt_dispatch_model:trigger_evt_anchor_se_exp_add data:" . json_encode($data));
	}

	public function trigger_evt_anchor_ll_exp_add($uid, $sid, $tid, $exp)
	{
		$data = array(
			'uid' => intval($uid),
			'sid' => intval($sid),
			'tid' => $tid,
			'exp' => intval($exp)
		);

		$redis = $this->getRedisJavaUtil();

		if (empty($redis)) {
        	LogApi::logProcess("evt_dispatch_model:trigger_evt_anchor_ll_exp_add faliure:" . json_encode($data));
        	return;
		}

		$redis->lpush($this->get_queue_name(evt_dispatch_model::queue_evt_anchor_level_exp, $uid), json_encode($data));

		LogApi::logProcess("evt_dispatch_model:trigger_evt_anchor_ll_exp_add data:" . json_encode($data));
	}

	public function trigger_evt_tool_consume_add($evt)
	{
		$redis = $this->getRedisJavaUtil();

		if (empty($redis)) {
        	LogApi::logProcess("evt_dispatch_model:trigger_evt_tool_consume_add faliure:" . json_encode($evt));
        	return;
		}

		$redis->lpush($this->get_queue_name(evt_dispatch_model::queue_evt_tool_consume, $evt->receiver_uid), json_encode($evt));

		LogApi::logProcess("evt_dispatch_model:trigger_evt_tool_consume_add data:" . json_encode($evt));
	}

	public function trigger_evt_week_tool_consume_add($evt)
	{
		$redis = $this->getRedisJavaUtil();

		if (empty($redis)) {
        	LogApi::logProcess("evt_dispatch_model:trigger_evt_week_tool_consume_add faliure:" . json_encode($evt));
        	return;
		}

		$redis->lpush($this->get_queue_name(evt_dispatch_model::queue_evt_week_tool_consume, $evt->receiver_uid), json_encode($evt));

		LogApi::logProcess("evt_dispatch_model:trigger_evt_week_tool_consume_add data:" . json_encode($evt));
	}
}
?>