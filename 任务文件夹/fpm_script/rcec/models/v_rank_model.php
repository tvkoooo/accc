
<?php

// 1、主播收到V星卷数量:
// hash结构  key:v_star_voucher_count  feild:zuid   value:收到v星卷的总数量

// 2、主播收到v星卷的时间戳:
// hash结构  key:v_star_voucher_time   feild:zuid   value:收到v星卷时的时间戳

// 3、主播收到阳光:
// hash结构   key:v_star_anchor_sun_count  feild:zuid  value:主播收到的阳光总数量

// 4、主播收到阳光时的时间戳:
// hash结构  key:v_star_anchor_sun_time   feild:zuid   value:主播收到阳光时的时间戳

// 5、用户送给指定主播星卷数量排行榜(每个主播对应一个有序集合):
// zset结构   key:v_star_voucher_count:zuid   value:uid  score:用户送给该主播的v星卷的数量

// 6、用户送给指定主播阳光数量排行榜(每个主播对应一个有序集合):
// zset结构   key:v_star_anchor_sun_count:zuid  value:uid   score:用户送给指定主播阳光数量

// weChatActivity:startEndTime string  逗号分割

// weChatActivity:anchor     活动主播     set

class v_rank_model extends ModelBase
{
	const PROP_ID_VSTAR = 128;
	const GIFT_TYPE_SUNSHINE = 15;

	public function on_recv_gift($tool_id, $tool_type, $num, $uid, $singer_id)
	{
		if (!$this->if_flush_rank($singer_id)) {
			return;
		}

		if ($tool_type == v_rank_model::GIFT_TYPE_SUNSHINE) {
			$this->on_recv_gift_sunshine($num, $uid, $singer_id);
		} else if ($tool_type == ToolApi::$PROP_TYPE_EFFECT) {
			if ($tool_id == v_rank_model::PROP_ID_VSTAR) {
				$this->on_recv_prop_vstar($num, $uid, $singer_id);
			}
		}
	}

	private function on_recv_prop_vstar($num, $uid, $singer_id)
	{
		$redis = $this->getRedisMaster();

		$k = "v_star_voucher_count";
		$f = $singer_id;
		$v = $num;
		$redis->hIncrBy($k, $f, $v);

		$k = "v_star_voucher_time";
		$f = $singer_id;
		$v = time();
		$redis->hSet($k, $f, $v);

		$k = "v_star_voucher_count:$singer_id";
		$m = $uid;
		$s = $num;
		$redis->zIncrBy($k, $s, $m);
	}

	private function on_recv_gift_sunshine($num, $uid, $singer_id)
	{
		$redis = $this->getRedisMaster();

		$k = "v_star_anchor_sun_count";
		$f = $singer_id;
		$v = $num;
		$redis->hIncrBy($k, $f, $v);

		$k = "v_star_anchor_sun_time";
		$f = $singer_id;
		$v = time();
		$redis->hSet($k, $f, $v);

		$k = "v_star_anchor_sun_count:$singer_id";
		$m = $uid;
		$s = $num;
		$redis->zIncrBy($k, $s, $m);
	}

	private function if_activity_in_motion()
	{
		$redis = $this->getRedisMaster();

		$k = "weChatActivity:startEndTime";
		$se = $redis->get($k);

		if (empty($se)) {
			return false;
		}

		$ret = explode(",", $se);

		if (!empty($ret) && is_array($ret) && count($ret) > 1) {
			$now = time();
			if ($now >= $ret[0] && $now < $ret[1]) {
				return true;
			}
		}

		return false;
	}

	private function if_anchor_in_activity($singer_id)
	{
		$redis = $this->getRedisMaster();

		$k = "weChatActivity:anchor";
		$m = $singer_id . '';
		$ret = $redis->sIsMember($k, $m);

		if (empty($ret)) {
			return false;
		}

		return true;
	}

	private function if_flush_rank($singer_id)
	{
		return ($this->if_activity_in_motion() && $this->if_anchor_in_activity($singer_id));
	}

}
?>