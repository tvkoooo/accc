
<?php

// 主播福利金币缓存, 过期时间7天
// key		    :h_divide:$anchor_id:yymmdd
// filed		:base		value:$(baseValue) * 100
// field		:prize		value:$(prizeValue) * 100
// field		:back		value:$(back) * 100
// field		:singer_sun	value:$(singerSunValue)

// 帮会分成缓存，过期时间7天
// key		    :h_union_divide:$unionid:yymmdd
// field		:union_value	value:$(unionValue) * 100
// field		:union_back		value:$(union_back) * 100
// field		:union_prize	value:$(union_prize) * 100
// field		:union_sun		value:$(union_sun)
class divide_model extends ModelBase
{
	const REDIS_KEY_ANCHOR_DIVIDE = "h_anchor_divide:";
	const REDIS_KEY_UNION_DIVIDE = "h_union_divide:";

	const EXPIRE_KEY_ANCHOR_DIVIDE = 604800;
	const EXPIRE_KEY_UNION_DIVIDE = 604800;

	const TYPE_GAME_GUESS = 4;
	const TYPE_GAME_DICE = 5;

	private function get_redis_key_anchor_divide($anchor_id) 
	{
		$date = date("Ymd");
		return divide_model::REDIS_KEY_ANCHOR_DIVIDE . $anchor_id . ":" . $date;
	}
	private function get_redis_key_anchor_divide_with_time($anchor_id, $time)
	{
		$date = date("Ymd", $time);
		return divide_model::REDIS_KEY_ANCHOR_DIVIDE . $anchor_id . ":" . $date;
	}
	private function get_redis_key_union_divide($uniond_id)
	{
		$date = date("Ymd");
		return divide_model::REDIS_KEY_UNION_DIVIDE . $uniond_id . ":" . $date;
	}
	private function get_redis_key_union_divide_with_time($uniond_id, $time)
	{
		$date = date("Ymd", $time);
		return divide_model::REDIS_KEY_UNION_DIVIDE . $uniond_id . ":" . $date;
	}

	public function add_anchor_divide_in_cache($anchor_id, $base, $prize, $back, $singer_sun, $time = 0)
	{
		$key = "";
		if ($time == 0) {
			$key = $this->get_redis_key_anchor_divide($anchor_id);
		} else {
			$key = $this->get_redis_key_anchor_divide_with_time($anchor_id, $time);
		}

		$redis = $this->getRedisMaster();

		$redis->hIncrby($key, "base", floor($base * 100));
		$redis->hIncrby($key, "prize", floor($prize * 100));
		$redis->hIncrby($key, "back", floor($back * 100));
		$redis->hIncrby($key, "singer_sun", $singer_sun);
		$redis->expire($key, divide_model::EXPIRE_KEY_ANCHOR_DIVIDE);
	}

	public function add_union_divide_in_cache($unoid_id, $value, $back, $prize, $union_sun, $time = 0)
	{
		$key = "";
		if (empty($time)) {
			$key = $this->get_redis_key_union_divide($unoid_id);
		} else {
			$key = $this->get_redis_key_union_divide_with_time($unoid_id, $time);
		}

		$redis = $this->getRedisMaster();
		$redis->hIncrby($key, "union_value", floor($value * 100));
		$redis->hIncrby($key, "union_back", floor($back * 100));
		$redis->hIncrby($key, "union_prize", floor($prize * 100));
		$redis->hIncrby($key, "union_sun", floor($union_sun));

		$redis->expire($key, divide_model::EXPIRE_KEY_UNION_DIVIDE);
	}

	public function get_anchor_divide_back_from_cache($anchor_id, $time = 0)
	{
		$key = "";
		if (empty($time)) {
			$key = $this->get_redis_key_anchor_divide($anchor_id);
		} else {
			$key = $this->get_redis_key_anchor_divide_with_time($anchor_id, $time);
		}

		$redis = $this->getRedisMaster();

		$ret = $redis->hGet($key, "back");

		$ret = empty($ret) ? 0 : $ret / 100;

		return $ret;
	}

	public function insert_anchor_divide_back_bill($anchor_id, $type, $back_before, $back_change, $preheat, $parm1)
	{
		$db = $this->getDbRecord();
		$sql = "INSERT INTO rcec_record.t_anchor_back_fund_bill(uid, type, preheat, back_before, back_change, parm1) VALUES ($anchor_id, $type, $preheat, $back_before, $back_change, $parm1)";

		$rows = $db->query($sql);

		if (empty($rows)) {
			LogApi::logProcess("divide_model:insert_anchor_divide_back_bill failure. sql:$sql");
		}
	}
}
?>