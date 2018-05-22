<?php 
class hot_rank_model extends ModelBase 
{
	const REDIS_KEY_HOT_RANK = 'z_hot_top_ranking';
	
	const REDIS_KEY_HOT_VALUE_TOP = 'singer_hot_value_top';
	
	const REDIS_KEY_HOT_CARD_USED = 'str_hot_card_used:';
	
	private function get_redis_key_hot_rank()
	{
		return hot_rank_model::REDIS_KEY_HOT_RANK;
	}
	private function get_redis_key_hot_value_top()
	{
		return hot_rank_model::REDIS_KEY_HOT_VALUE_TOP;
	}
	private function get_redis_key_hot_card_used($singer_id)
	{
		return hot_rank_model::REDIS_KEY_HOT_CARD_USED . $singer_id;
	}
	
	private function get_rank_number()
	{
		$key = $this->get_redis_key_hot_rank();
		$redis = $this->getRedisMaster();
		
		return $redis->scard($key);
	}
	private function reset_rank_score($singer_id, $score)
	{
		$key = $this->get_redis_key_hot_rank();
		$mem = $singer_id . '';
		
		$redis = $this->getRedisMaster();
		$redis->zAdd($key, $score, $mem);
	}
	private function get_rank_by_member($singer_id)
	{
		$key = $this->get_redis_key_hot_rank();
		$mem = $singer_id . '';
		
		$redis = $this->getRedisMaster();
		$rank = $redis->zRevRank($key, $mem);
		
		$rank = $rank === false ? -1 : intval($rank);
		
		return $rank;
	}
	private function get_score_by_member($singer_id)
	{
		$key = $this->get_redis_key_hot_rank();
		$mem = $singer_id . '';
		
		$redis = $this->getRedisMaster();
		$score = $redis->zScore($key, $mem);
		$score = $score === false ? -1 : doubleval($score);
		
		return $score;
	}
	private function get_rank_with_score($singer_id)
	{
		$rank = $this->get_rank_by_member($singer_id);
		$score = $this->get_score_by_member($singer_id);
		
		return array(
				'rank' => $rank,
				'score' => $score
		);
	}
	private function get_mem_by_rank($rank)
	{
		$key = $this->get_redis_key_hot_rank();
		
		$redis = $this->getRedisMaster();
		
		return $redis->zrevrange($key, $rank, $rank);
	}
	private function get_mem_by_rank_with_score($rank)
	{
		$mem = $this->get_mem_by_rank($rank);
		$score = -1;
		$mem_id = -1;
		if (!empty($mem)) {
			$mem_id = $mem[0];
			$score = $this->get_score_by_member($mem_id);
		} else {
			$mem_id = -1;
		}
		
		return array (
				'mem' => $mem_id,
				'score' => $score
		);
	}
	//
	public function update_rank_score($singer_id)
	{		
		$hot_card_key = $this->get_redis_key_hot_card_used($singer_id);
		$redis = $this->getRedisMaster();
		
		$hot_card_value = $redis->get($hot_card_key);
		if (empty($hot_card_value)) {
			$hot_card_value = 0;
		}
		
		$model_channel = new ChannelLiveModel();
		$hot_top_value = $model_channel->get_hot_value($singer_id);
		
		if (empty($hot_top_value)) {
			$hot_top_value = 0;
		}
		
		$hot_total = $hot_card_value + $hot_top_value;
		
		$this->reset_rank_score($singer_id, $hot_total);
	}
	
	public function clear_rank($singer_id)
	{
		$key = $this->get_redis_key_hot_rank();
		$mem = $singer_id . '';
		
		$redis = $this->getRedisMaster();
		$redis->zRem($key, $mem);
	}
	public function hot_card_used($singer_id, $hot_value, $timeout)
	{
		$rank_old = $this->get_rank_by_member($singer_id);
		
		if ($rank_old == -1) {
			$rank_old = $this->get_rank_number();
		}
		
		$hot_card_key = $this->get_redis_key_hot_card_used($singer_id);
		$redis = $this->getRedisMaster();
		$redis->incrBy($hot_card_key, $hot_value);
		$redis->expire($hot_card_key, $timeout);
		
		$this->update_rank_score($singer_id);
		$rank_new = $this->get_rank_by_member($singer_id);
		
		if ($rank_new > $rank_old) {
			$rank_old = $rank_new;
		}
		
		$rank_new += 1;
		$rank_old += 1;
		
		
		return array(
				'old' => $rank_old,
				'new' => $rank_new
		);
	}
	public function high_ladder_card_used($singer_id, $rank)
	{
		$result = array(
				'b_changed' => false,
				'rank' => intval($rank)
		);
		
		$singer_rank_inf = $this->get_rank_with_score($singer_id);
		$rank_singer = $singer_rank_inf['rank'];
		$score_singer = $singer_rank_inf['score'];
		
		do {
			if ($rank_singer != -1 && $rank_singer < $rank) {
				$result['rank'] = $rank_singer + 1;
				break;
			}
			
			$rank_20_inf = $this->get_mem_by_rank_with_score($rank-1);
			
			if ($rank_20_inf['mem'] == -1 || $rank_20_inf['score'] == -1) {
				break;
			}
			
			if ($score_singer == -1) {
				$score_singer = 0;
			}
			
			$diff_score = $rank_20_inf['score'] - $score_singer;
			$diff_score = $diff_score < 0 ? 0 : $diff_score;
			$diff_score += 100;
			
			$model_channel = new ChannelLiveModel();
			$model_channel->incrHotValue($singer_id, $diff_score);
			$this->update_rank_score($singer_id);
			
			$result['b_changed'] = true;
			$result['rank'] = intval($rank);
		} while (0);
		
		return $result;
	}
}
?>