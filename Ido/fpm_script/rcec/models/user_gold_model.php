<?php

class user_gold_model extends ModelBase
{
	const redis_key_user_con_incen_dedu = "h_user_con_incen:";

	const expire_time_key_user_con_incen = 86400;	// 24 * 60 * 60
	private function get_redis_key_user_con_incen($uid)
	{
		$date = date("Ymd");
		return user_gold_model::redis_key_user_con_incen_dedu . $date . ":" . ($uid % 1024);
	}

	public function con_incen_dedu_add($uid, $gold)
	{
		$key = $this->get_redis_key_user_con_incen($uid);

		$redis = $this->getRedisMaster();

		$total = $redis->hIncrBy($key, $uid, $gold);

		$redis->expire($key, user_gold_model::expire_time_key_user_con_incen);

		$total = empty($total) ? 0 : intval($total);

		return $total;
	}
}
?>