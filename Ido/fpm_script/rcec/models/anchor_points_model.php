<?php

class anchor_points_model extends ModelBase 
{
	const points_add_per_speak = 5;
	const points_add_per_watch = 10;
	const points_add_per_share = 100;
	const points_add_per_game_dice = 600;
	const points_add_per_game_guess = 600;
	const points_add_per_game_saw = 1200;

	const points_add_per_1min_speak_limit = 25;
	const points_add_per_1min_watch_limit = 10;
	const points_add_per_1min_share_limit = 100;
	const points_add_per_3min_speak_limit = 1200;
	const points_add_per_3min_watch_limit = 1200;
	const points_add_per_3min_share_limit = 1200;

	const redis_key_1min_speak = "h_anchor_points_per_1min_speak:";
	const redis_key_1min_watch = "h_anchor_points_per_1min_watch:";
	const redis_key_1min_share = "h_anchor_points_per_1min_share:";
	const redis_key_3min = "h_anchor_points_per_3min:";

	const redis_field_3min_speak = "speak";
	const redis_field_3min_watch = "watch";
	const redis_field_3min_share = "share";
	const redis_field_3min_recv_gift = "recv_gift";
	const redis_field_3min_recv_sunshine = "recv_sunshine";
	const redis_field_3min_game = "game";

	private function get_redis_key_1min_speak($singer_id)
	{
		return anchor_points_model::redis_key_1min_speak . $singer_id;
	}
	private function get_redis_key_1min_watch($singer_id)
	{
		return anchor_points_model::redis_key_1min_watch . $singer_id;
	}
	private function get_redis_key_1min_share($singer_id)
	{
		return anchor_points_model::redis_key_1min_share . $singer_id;
	}
	private function get_redis_key_3min($singer_id)
	{
		return anchor_points_model::redis_key_3min . $singer_id;
	}

	private function update_points_per_3min($singer_id, $field, $pt)
	{
		$key = $this->get_redis_key_3min($singer_id);
		$redis = $this->getRedisMaster();
		$ret = $redis->hIncrBy($key, $field, $pt);

		LogApi::logProcess("anchor_points_model:update_points_per_3min singer_id:$singer_id field:$field pt:$pt ret:$ret");
	}

	private function convert_gift_price_2pt($total_price)
	{
		if ($total_price > 0 && $total_price < 100) 
		{
			return 180;
		} 
		else if ($total_price >= 100 && $total_price < 500) 
		{
			return 450;
		} 
		else if ($total_price >= 500 && $total_price < 1000) 
		{
			return 900;
		} 
		else if ($total_price >= 1000 && $total_price < 10000) 
		{
			return 1800;
		}
		else if ($total_price >= 10000) 
		{
			return 3600;
		}

		return 0;
	}

	private function convert_gift_sunshine_2pt($total_price)
	{
		if ($total_price > 0 && $total_price < 500) 
		{
			return 180;
		}
		else if ($total_price >= 500 && $total_price < 1500) 
		{
			return 600;
		}
		else if ($total_price >=1500 && $total_price < 8000)
		{
			return 1200;
		}
		else if ($total_price >= 8000)
		{
			return 3600;
		}

		return 0;
	}

	public function on_user_speak($singer_id, $uid)
	{
		$redis = $this->getRedisMaster();
		$key = $this->get_redis_key_1min_speak($singer_id);
		$ret = $redis->hIncrBy($key, $uid, anchor_points_model::points_add_per_speak);
		LogApi::logProcess("anchor_points_model:on_user_speak singer_id:$singer_id uid:$uid ret:$ret");
	}

	public function on_user_watch($singer_id, $uid)
	{
		$redis = $this->getRedisMaster();
		$key = $this->get_redis_key_1min_watch($singer_id);
		$ret = $redis->hIncrBy($key, $uid, anchor_points_model::points_add_per_watch);
		LogApi::logProcess("anchor_points_model:on_user_speak singer_id:$singer_id uid:$uid ret:$ret");
	}

	public function on_user_share($singer_id, $uid)
	{
		$redis = $this->getRedisMaster();
		$key = $this->get_redis_key_1min_share($singer_id, $uid);
		$ret = $redis->hIncrBy($key, $uid, anchor_points_model::points_add_per_share);
		LogApi::logProcess("anchor_points_model:on_user_speak singer_id:$singer_id uid:$uid ret:$ret");
	}

	public function on_anchor_recv_gift($singer_id, $total_price)
	{
		$this->update_points_per_3min($singer_id, anchor_points_model::redis_field_3min_recv_gift, $total_price);
	}

	public  function on_anchor_recv_sunshine($singer_id, $total_sunshine)
	{
		$this->update_points_per_3min($singer_id, anchor_points_model::redis_field_3min_recv_sunshine, $total_sunshine);
	}

	public function on_anchor_finish_game_dice($singer_id)
	{
		$this->update_points_per_3min($singer_id, anchor_points_model::redis_field_3min_game, anchor_points_model::points_add_per_game_dice);
	}

	public function on_anchor_finish_game_guess($singer_id)
	{
		$this->update_points_per_3min($singer_id, anchor_points_model::redis_field_3min_game, anchor_points_model::points_add_per_game_guess);
	}

	public function on_anchor_finish_game_saw($singer_id)
	{
		$this->update_points_per_3min($singer_id, anchor_points_model::redis_field_3min_game, anchor_points_model::points_add_per_game_saw);
	}

	private function flush_user_speak_1min($singer_id)
	{
		$redis = $this->getRedisMaster();
		$key = $this->get_redis_key_1min_speak($singer_id);

		$list = $redis->hVals($key);
		$total = 0;
		if (!empty($list)) {
			$redis->del($key);
			foreach ($list as $pt) {
				$total += ($pt > anchor_points_model::points_add_per_1min_speak_limit ? anchor_points_model::points_add_per_1min_speak_limit : $pt);
			}
		}

		LogApi::logProcess("anchor_points_model:flush_user_speak_1min singer_id:$singer_id total:$total list:" . json_encode($list));
		if ($total > 0) {
			$this->update_points_per_3min($singer_id, anchor_points_model::redis_field_3min_speak, $total);
		}
	}

	private function flush_user_watch_1min($singer_id)
	{
		$redis = $this->getRedisMaster();
		$key = $this->get_redis_key_1min_watch($singer_id);

		$list = $redis->hVals($key);

		$total = 0;
		if (!empty($list)) {
			$redis->del($key);
			foreach ($list as $pt) {
				$total += ($pt > anchor_points_model::points_add_per_1min_watch_limit ? anchor_points_model::points_add_per_1min_watch_limit : $pt);
			}
		}

		LogApi::logProcess("anchor_points_model:flush_user_watch_1min singer_id:$singer_id total:$total list:" . json_encode($list));
		if ($total > 0) {
			$this->update_points_per_3min($singer_id, anchor_points_model::redis_field_3min_watch, $total);
		}
	}

	private function flush_user_share_1min($singer_id)
	{
		$redis = $this->getRedisMaster();
		$key = $this->get_redis_key_1min_share($singer_id);

		$list = $redis->hVals($key);

		$total = 0;
		if (!empty($list)) {
			$redis->del($key);
			foreach ($list as $pt) {
				$total += ($pt > anchor_points_model::points_add_per_1min_share_limit ? anchor_points_model::points_add_per_1min_share_limit : $pt);
			}
		}

		if ($total > 0) {
			$this->update_points_per_3min($singer_id, anchor_points_model::redis_field_3min_share, $total);
		}
	}

	public function flush_per_1min($singer_id)
	{
		$this->flush_user_speak_1min($singer_id);
		$this->flush_user_watch_1min($singer_id);
		$this->flush_user_share_1min($singer_id);
	}

	public function flush_per_3min($singer_id)
	{
		$key = $this->get_redis_key_3min($singer_id);
		$redis = $this->getRedisMaster();

		$total = 0;
		$m = $redis->hGetAll($key);
		if (!empty($m)) {
			$redis->del($key);

			foreach ($m as $k => $v) {
				switch ($k) {
					case anchor_points_model::redis_field_3min_share:
						$total += ($v > anchor_points_model::points_add_per_3min_share_limit ? anchor_points_model::points_add_per_3min_share_limit : $v);
						break;
					case anchor_points_model::redis_field_3min_watch:
						$total += ($v > anchor_points_model::points_add_per_3min_watch_limit ? anchor_points_model::points_add_per_3min_watch_limit : $v);
						break;
					case anchor_points_model::redis_field_3min_speak:
						$total += ($v > anchor_points_model::points_add_per_3min_speak_limit ? anchor_points_model::points_add_per_3min_speak_limit : $v);
						break;
					case anchor_points_model::redis_field_3min_game:
						$total += $v;
						break;
					case anchor_points_model::redis_field_3min_recv_gift:
						$total += $this->convert_gift_price_2pt($v);
						break;
					case anchor_points_model::redis_field_3min_recv_sunshine:
						$total += $this->convert_gift_sunshine_2pt($v);
						break;
					default:
						break;
				}
			}
		}

		LogApi::logProcess("anchor_points_model:flush_per_3min singer_id:$singer_id ret:$total m:" . json_encode($m));
		return $total;
	}

	public function clear_anchor_points($singer_id)
	{
		$redis = $this->getRedisMaster();
		$key = $this->get_redis_key_1min_speak($singer_id);
		$redis->del($key);

		$key = $this->get_redis_key_1min_watch($singer_id);
		$redis->del($key);

		$key = $this->get_redis_key_1min_share($singer_id);
		$redis->del($key);

		$key = $this->get_redis_key_3min($singer_id);
		$redis->del($key);
	}

}
?>