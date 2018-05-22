<?php
class sun_income_task_model extends ModelBase
{
	// 可能存在的问题，如果缓存中主播的任务开启信息丢失了，可能会导致该任务无法完成，此时需要人工介入
	
	const REDIS_KEY_ANCHOR_SUNINCOME_TASK_OPEN = "h_anchor_sunincome_task_o";			//阳光收益任务开启条件
	const REDIS_KEY_ANCHOR_SUNINCOME_TASK_FINISHED = "h_anchor_sunincome_task_f";		//阳光收益任务完成条件
	const REDIS_KEY_ANCHOR_GOLD_RECEIVED = "h_anchor_gold_received";					//主播每月金币收入
	const REDIS_KEY_ANCHOR_SUNINCOME_TASK_INFO_SYS_CONF = "h_suninicome_task_info_sys";	//阳光收益任务配置
	const REDIS_KEY_ANCHOR_SUNINCOME_TASK_OPEN_LOCK = "str_sunincome_task_open_lock";	//任务开启并发控制锁
	const REDIS_KEY_ANCHOR_SUNINCOME_TASK_FINISHED_LOCK = "str_sunincome_task_finished_lock"; //任务完成并发控制锁
	const REDIS_KEY_ANCHOR_SUNINCOME_TASK_INFO_OPENED = "h_anchor_sunincome_task_info_opened"; //主播已开启阳光收益任务信息
	
	const LOCK_TIME_ANCHOR_SUNINCOME_TASK_OPEN = 1;							//任务开启并发控制锁时间(s)
	const LOCK_TIME_ANCHOR_SUNINCOME_TASK_FINISHED = 1;						//任务完成并发控制锁时间(s)
	
	const STATE_SUNINCOME_TASK_UNOPEN = 1;									//任务状态，未开启
	const STATE_SUNINCOME_TASK_ING = 3;										//任务状态，进行中
	const STATE_SUNINCOME_TASK_FINISHED = 5;								//任务状态，已完成
	const STATE_SUNINCOME_TASK_FINISHED_AUTO = 7;							//任务状态，自动完成
	
	const OPEN_FROM_SUNINCOME_TASK_UNKONWN = 0;								//任务开启来源，未知
	const OPEN_FROM_SUNINCOME_TASK_GIFT = 1;								//任务开启来源，送礼
	const OPEN_FROM_SUNINCOME_TASK_GUARD = 11;								//任务开启来源，守护
	
	const FINISHED_FROM_SUNINCOME_TASK_UNKONWN = 0;							//任务完成来源，未知
	const FINISHED_FROM_SUNINCOME_TASK_GIFT_SUN = 1;						//任务完成来源，阳光礼物
	const FINISHED_FROM_SUNINCOME_TASK_TASK_REWARD = 3;						//任务完成来源，任务奖励
	const FINISHED_FROM_SUNINCOME_TASK_FANS_GROUP = 5;						//任务完成来源，粉丝群阳光
	const FINISHED_FROM_SUNINCOME_TASK_GIFT = 7;							//任务完成来源，送礼(主播阳光已达到完成条件，当任务开启时立刻完成)
	const FINISHED_FROM_SUNINCOME_TASK_GUARD = 9; 							//任务完成来源，守护(主播阳光已达到完成条件，当任务开启时立刻完成)
	const FINISHED_FROM_SUNINCOME_TASK_GIFT_NEW_ANCHOR = 11;				//任务完成来源，送礼(新人主播)
	const FINISHED_FROM_SUNINCOME_TASK_GUARD_NEW_ANCHOR = 13;				//任务完成来源，守护(新人主播)
	
	const EXPIRE_ANCHOR_SUNINCOME_TASK_INFO_OPENED = 2764800;				//主播已开启任务信息过期时间，32天
	
	private function get_redis_key_anchor_sunincome_task_open($uid, $date)
	{
		return sun_income_task_model::REDIS_KEY_ANCHOR_SUNINCOME_TASK_OPEN . ":" . $date . ":" . ($uid % 1024);
	}
	private function get_redis_key_anchor_sunincome_task_finished($uid, $date)
	{
		return sun_income_task_model::REDIS_KEY_ANCHOR_SUNINCOME_TASK_FINISHED . ":" . $date . ":" . ($uid % 1024);
	}
	private function get_redis_key_anchor_gold_received($uid, $date)
	{
		return sun_income_task_model::REDIS_KEY_ANCHOR_GOLD_RECEIVED . ":" . $date . ":" . ($uid % 1024);
	}
	private function get_redis_key_sunincome_task_info_sys_conf()
	{
		return sun_income_task_model::REDIS_KEY_ANCHOR_SUNINCOME_TASK_INFO_SYS_CONF;
	}
	private function get_redis_key_anchor_sunincome_task_open_lock($uid, $date)
	{
		return sun_income_task_model::REDIS_KEY_ANCHOR_SUNINCOME_TASK_OPEN_LOCK . ":" . $date . ":" . $uid;
	}
	private function get_redis_key_anchor_sunincome_task_finished_lock($uid, $date)
	{
		return sun_income_task_model::REDIS_KEY_ANCHOR_SUNINCOME_TASK_FINISHED_LOCK . ":" . $date . ":" . $uid;
	}
	private function get_redis_key_anchor_sunincome_task_info_opened($uid, $date)
	{
		return sun_income_task_model::REDIS_KEY_ANCHOR_SUNINCOME_TASK_INFO_OPENED . ":" . $date . ":" . $uid;
	}
	
	private function redis_set_nx_ex($redis, $key, $value, $ex)
	{
		$ret = false;
	
		$ret = $redis->setnx($key, $value);
		if ($ret) {
			$redis->expire($key, $ex);
		}
	
		return $ret;
	}
	
	private function insert_sunincome_record($uid, $date, $inf, $src)
	{
		$ret = false;
		
		$tn = time();
		
		$level = $inf['level'];
		$gold_need = $inf['gold_need'];
		$sun_need = $inf['sun_need'];
		$diamond_reward = $inf['diamond_reward'];
		$task_name = $inf['task_name'];
		$task_target = $inf['task_target'];
		$unlock_target = $inf['unlock_target'];
		$finished_from = sun_income_task_model::FINISHED_FROM_SUNINCOME_TASK_UNKONWN;
		$state = sun_income_task_model::STATE_SUNINCOME_TASK_ING;
		
		$sql = "INSERT IGNORE INTO rcec_record.t_anchor_sun_income_task_record 
				(uid,start_month,level,gold_need,sun_need,diamond_reward,task_name,task_target,unlock_target,state,create_time,last_uptime,open_from,finish_from) VALUES 
				($uid,'$date',$level,$gold_need,$sun_need,$diamond_reward,'$task_name','$task_target','$unlock_target',$state,$tn,$tn,$src,$finished_from)";
		
		$db_rcec = $this->getDbRecord();

		$rows = $db_rcec->query($sql);		
		if (empty($rows)) {
			LogApi::logProcess("sun_income_task_model:insert_sunincome_record failure. sql:$sql");
		} else {
			$ret = true;
		}
		
		return $ret;
	}
	private function finish_sunincome_record($uid, $date, $inf, $src)
	{
		// TODO: 是否使用事务
		$ret = false;
		$tn = time();
		$i_state = sun_income_task_model::STATE_SUNINCOME_TASK_ING;
		if ($src == sun_income_task_model::FINISHED_FROM_SUNINCOME_TASK_GIFT_NEW_ANCHOR || 
			$src == sun_income_task_model::FINISHED_FROM_SUNINCOME_TASK_GUARD_NEW_ANCHOR) {

			$f_state = sun_income_task_model::STATE_SUNINCOME_TASK_FINISHED_AUTO;
		} else {
			$f_state = sun_income_task_model::STATE_SUNINCOME_TASK_FINISHED;
		}

		$level = $inf['level'];
		$money_income = $inf['diamond_reward'];
		$db_rcec = $this->getDbRecord();
		
		try {
			$db_rcec->query("BEGIN");
			do {
				$sql = "INSERT IGNORE INTO rcec_record.t_anchor_sun_income_task_bill (uid,start_month,level,diamond_reward) VALUES ($uid,'$date',$level,$money_income)";
				$rows = $db_rcec->query($sql);

				if (empty($rows)) {
					LogApi::logProcess("sun_income_task_model:finish_sunincome_record failure. sql:$sql");
					break;
				}

				if ($db_rcec->affected_rows == 0) {
					$ret = true;
					break;
				}

				$sql = "UPDATE rcec_record.anchor_percentage_record SET anchor_income=anchor_income + $money_income,updatetime=NOW() WHERE uid=$uid";
				$rows = $db_rcec->query($sql);
				
				if (empty($rows) || $db_rcec->affected_rows == 0) {
					LogApi::logProcess("sun_income_task_model:finish_sunincome_record failure. sql:$sql");
					break;
				}
				
				$sql = "UPDATE rcec_record.t_anchor_sun_income_task_record SET state=$f_state,finish_from=$src,last_uptime=$tn WHERE uid=$uid AND start_month=$date AND level=$level";
				$rows = $db_rcec->query($sql);
				
				if (empty($rows) || $db_rcec->affected_rows == 0) {
					LogApi::logProcess("sun_income_task_model:finish_sunincome_record failure. sql:$sql");
					break;
				}
				

				$ret = true;
			} while (0);
			
			if ($ret) {
				$db_rcec->query("COMMIT");
			} else {
				$db_rcec->query("ROLLBACK");
			}
		} catch (Exception $e) {
			$db_rcec->query("ROLLBACK");
			LogApi::logProcess("sun_income_task_model:finish_sunincome_record exception. emsg:" . $e->getMessage());
		}
		
		return $ret;
	}
	
	
	private function get_sys_task_inf()
	{
		$item = array();
		
		$key = $this->get_redis_key_sunincome_task_info_sys_conf();
		$redis = $this->getRedisMaster();
		
		$ret = $redis->hGetAll($key);
		
		if (!empty($ret)) {
			foreach ($ret as $field=>$value) {
				$item[(int)$field] = json_decode($value, true);
			}
			
			return $item;
		}
		
		$sql = "SELECT * FROM card.anchor_sun_task";
		
		$db_card = $this->getDbMain();
		$rows = $db_card->query($sql);
		
		if (!empty($rows)) {
			$row = null;
			$row = $rows->fetch_assoc();
				
			while (!empty($row)) {
				$item[(int)$row['level']] = $row;
				$redis->hSet($key, $row['level'] . "", json_encode($row));
				$row = $rows->fetch_assoc();
			}
		}
		
		return $item;
	}
	
	private function get_task_level_opened_max($uid, $date)
	{
		$key = $this->get_redis_key_anchor_sunincome_task_open($uid, $date);
		$field = strval($uid);
		
		$redis = $this->getRedisMaster();
		
		return $redis->hGet($key, $field);
	}
	private function set_task_level_opened_next($uid, $date)
	{
		$key = $this->get_redis_key_anchor_sunincome_task_open($uid, $date);
		$field = strval($uid);
	
		$redis = $this->getRedisMaster();
	
		return $redis->hIncrBy($key, $field, 1);
	}
	private function set_task_level_opened($uid, $date, $level)
	{
		$key = $this->get_redis_key_anchor_sunincome_task_open($uid, $date);
		$field = strval($uid);
	
		$redis = $this->getRedisMaster();
	
		return $redis->hSet($key, $field, $level);
	}
	
	private function get_task_level_finished_max($uid, $date)
	{
		$key = $this->get_redis_key_anchor_sunincome_task_finished($uid, $date);
		$field = strval($uid);
		
		$redis = $this->getRedisMaster();
		
		return $redis->hGet($key, $field);
	}
	private function set_task_level_finished_next($uid, $date)
	{
		$key = $this->get_redis_key_anchor_sunincome_task_finished($uid, $date);
		$field = strval($uid);
		
		$redis = $this->getRedisMaster();
		
		return $redis->hIncrBy($key, $field, 1);
	}
	private function set_task_level_finished($uid, $date, $level)
	{
		$key = $this->get_redis_key_anchor_sunincome_task_finished($uid, $date);
		$field = strval($uid);
		
		$redis = $this->getRedisMaster();
		
		return $redis->hSet($key, $field, $level);
	}
	
	private function get_task_info_opened($uid, $date, $leve)
	{
		$key = $this->get_redis_key_anchor_sunincome_task_info_opened($uid, $date);
		$field = strval($leve);
		
		$redis = $this->getRedisMaster();
		
		$ret = $redis->hGet($key, $field);
		
		if ($ret) {
			$ret = json_decode($ret, true);
		}
		
		return $ret;
	}
	private function get_task_info_opened_force($uid, $date, $level)
	{
		$inf = $this->get_task_info_opened($uid, $date, $level);

		if (!empty($inf)) {
			return $inf;
		}

		$redis = $this->getRedisMaster();
		$key = $this->get_redis_key_sunincome_task_info_sys_conf();

		$sql = "SELECT level,gold_need,sun_need,diamond_reward,task_name,task_target,unlock_target FROM rcec_record.t_anchor_sun_income_task_record WHERE uid=$uid AND start_month=$date AND level=$level";
		
		$db_rcec = $this->getDbRecord();
		$rows = $db_rcec->query($sql);

		if (!empty($rows)) {
			$row = null;
			$row = $rows->fetch_assoc();
			
			if (!empty($row)) {
				$this->set_task_info_opened($uid, $date, $row);
				return $row;
			}
		}

		return null;
	}
	private function set_task_info_opened($uid, $date, $inf)
	{
		$key = $this->get_redis_key_anchor_sunincome_task_info_opened($uid, $date);
		$field = strval($inf['level']);
		
		$redis = $this->getRedisMaster();
		
		$ret = $redis->hSet($key, $field, json_encode($inf));
		$redis->expire($key, sun_income_task_model::EXPIRE_ANCHOR_SUNINCOME_TASK_INFO_OPENED);
		
		return $ret;
	}
	private function del_task_info_opened($uid, $date, $level)
	{
		$key = $this->get_redis_key_anchor_sunincome_task_info_opened($uid, $date);
		$field = strval($level);
		
		$redis = $this->getRedisMaster();
		
		return $redis->hDel($key, $field);
	}
	private function if_can_finished_auto($uid, $date)
	{	
		$flag = false;
		
		$cur_month_day = $date . '11';
		$last_month_day_time = strtotime("-1 month", strtotime($cur_month_day));
		$sql = "SELECT * FROM channellive.t_anchor_first_open WHERE uid=$uid AND time_first_open >= $last_month_day_time";
		
		$db_channel = $this->getDbMain();
		$rows = $db_channel->query($sql);
		
	    if ($rows && $rows->num_rows > 0) {
	    	$flag = true;
	    }
		
	    return $flag;
	}
	
	public function income_gold($uid, $gold, $date)
	{
		$key = $this->get_redis_key_anchor_gold_received($uid, $date);
		$field = strval($uid);
		
		$redis = $this->getRedisMaster();
		
		return $redis->hIncrBy($key, $field, $gold);
	}
	
	private function task_open($uid, $date, $inf, $open_from)
	{
		// 需加锁
		$ret = false;
		$redis = $this->getRedisMaster();
		
		$key_lock = $this->get_redis_key_anchor_sunincome_task_open_lock($uid, $date);
		
		$rd = rand();
		
		$ok = $this->redis_set_nx_ex($redis, $key_lock, $rd, sun_income_task_model::LOCK_TIME_ANCHOR_SUNINCOME_TASK_OPEN);
		
		if ($ok) {
			// 并不保证该次一定成功
			$cur_task_opened_max = $this->get_task_level_opened_max($uid, $date);
			$cur_task_opened_max = empty($cur_task_opened_max) ? 0 : intval($cur_task_opened_max);
			
			if ($inf['level'] > $cur_task_opened_max) {
				if ($this->insert_sunincome_record($uid, $date, $inf, $open_from)) {
					$ret = true;
					// 如果任务id比缓冲中存储的连续最大任务id大1，则更新缓存。该判断条件为了防止漏掉中间开启失败的任务。
					// if ($inf['level'] - 1 == $cur_task_opened_max) {
					// 	$this->set_task_level_opened_next($uid, $date);
					// }
					
					$this->set_task_level_opened($uid, $date, $inf['level']);
					// 主播任务开启缓存信息
					if (false === $this->set_task_info_opened($uid, $date, $inf)) {
						LogApi::logProcess("sun_income_task_model:task_open set_task_info_opened failure. uid:$uid date:$date inf:" . json_encode($inf));
					}
				}
			}
			
			if ($redis->get($key_lock) == $rd) {
				$redis->del($key_lock);
			}
		}
		
		return $ret;
	}
	
	private function task_finish($uid, $date, $inf, $finished_from)
	{
		// 需加锁
		$ret = false;
		$redis = $this->getRedisMaster();
		
		$key_lock = $this->get_redis_key_anchor_sunincome_task_finished_lock($uid, $date);
		
		$rd = rand();
		
		$ok = $this->redis_set_nx_ex($redis, $key_lock, $rd, sun_income_task_model::LOCK_TIME_ANCHOR_SUNINCOME_TASK_FINISHED);
		
		if ($ok) {
			// 并不保证该次一定成功
			$cur_task_finished_max = $this->get_task_level_finished_max($uid, $date);
			$cur_task_finished_max = empty($cur_task_finished_max) ? 0 : intval($cur_task_finished_max);
			
			if ($inf['level'] > $cur_task_finished_max) {
				if ($this->finish_sunincome_record($uid, $date, $inf, $finished_from)) {
					$ret = true;
					// 如果任务id比缓存中存储的连续最大任务id大1，则更新缓存。该判断条件为了防止漏掉中间完成失败的任务。
					$this->set_task_level_finished($uid, $date, $inf['level']);
					$this->del_task_info_opened($uid, $date, $inf['level']);

					// if ($inf['level'] - 1 == $cur_task_finished_max) {
					// 	$this->set_task_level_finished_next($uid, $date);
						
					// 	// 清除任务开启缓存信息
					// 	$this->del_task_info_opened($uid, $date, $inf['level']);
					// }					
				}
			}
			
			if ($redis->get($key_lock) == $rd) {
				$redis->del($key_lock);
			}
		}
		
		return $ret;
	}
	
	// return array of level
	private function b_task_open($uid, $gold_total, $date, $open_from)
	{
		$rs = array();
		
		$cur_task_opened_max = $this->get_task_level_opened_max($uid, $date);
		$cur_task_opened_max = empty($cur_task_opened_max) ? 0 : intval($cur_task_opened_max);
		
		$next_level = $cur_task_opened_max + 1;
		
		$task_conf_infs = $this->get_sys_task_inf();
		
		if (empty($task_conf_infs)) {
			LogApi::logProcess("sun_income_task_model:b_task_open get_sys_task_inf failure. uid:$uid gold_total:$gold_total date:$date");
			return $rs;
		}
		
		while (!empty($task_conf_infs[$next_level])) {
			if ($gold_total >= $task_conf_infs[$next_level]['gold_need']) {
				if ($this->task_open($uid, $date, $task_conf_infs[$next_level], $open_from)) {
					array_push($rs, $next_level);
				}
			} else {
				break;
			}
			$next_level++;
		}
		
		LogApi::logProcess("sun_income_task_model:b_task_open. uid:$uid glod_total:$gold_total date:$date from:$open_from openids:" . json_encode($rs));
		return $rs;
	}
	
	// return array of level
	private function b_task_finished($uid, $sun_total, $date, $finished_from)
	{
		$rs = array();
		
		$cur_task_finished_max = $this->get_task_level_finished_max($uid, $date);
		$cur_task_finished_max = empty($cur_task_finished_max) ? 0 : intval($cur_task_finished_max);
		
		$next_level = $cur_task_finished_max + 1;
		
		$redis = $this->getRedisMaster();
		
		$inf = $this->get_task_info_opened_force($uid, $date, $next_level);
		
		while (!empty($inf)) {
			if ($sun_total >= $inf['sun_need']) {
				if ($this->task_finish($uid, $date, $inf, $finished_from)) {
					array_push($rs, $next_level);
				}
			} else {
				break;
			}
			$next_level++;
			$inf = $this->get_task_info_opened_force($uid, $date, $next_level);
		}
		
		LogApi::logProcess("sun_income_task_model:b_task_finished. uid:$uid sun_total:$sun_total date:$date from:$finished_from finishids:" . json_encode($rs));
		
		return $rs;
	}
	
	private function new_anchor_task_finished($uid, $date, $finished_from)
	{
		$rs = array();
		
		$cur_task_finished_max = $this->get_task_level_finished_max($uid, $date);
		$cur_task_finished_max = empty($cur_task_finished_max) ? 0 : intval($cur_task_finished_max);
		
		$next_level = $cur_task_finished_max + 1;
		
		$redis = $this->getRedisMaster();
		
		$inf = $this->get_task_info_opened($uid, $date, $next_level);
		
		while (!empty($inf)) {
			if ($this->task_finish($uid, $date, $inf, $finished_from)) {
				array_push($rs, $next_level);
			}
			$next_level++;
			$inf = $this->get_task_info_opened($uid, $date, $next_level);
		}
		
		LogApi::logProcess("sun_income_task_model:new_anchor_task_finished. uid:$uid date:$date from:$finished_from finishids:" . json_encode($rs));
		
		return $rs;
	}
	
	public function on_gift_received($uid, $money)
	{
		$start_date = date('Ym');
		$gold_month = $this->income_gold($uid, $money, $start_date);
		
		$open_ids = $this->b_task_open($uid, $gold_month, $start_date, sun_income_task_model::OPEN_FROM_SUNINCOME_TASK_GIFT);
		
		if (!empty($open_ids)) {
			if ($this->if_can_finished_auto($uid, $start_date)) {
				$this->new_anchor_task_finished($uid, $start_date, sun_income_task_model::FINISHED_FROM_SUNINCOME_TASK_GIFT_NEW_ANCHOR);
			} else {
				$model_channellive = new ChannelLiveModel();
				$anchor_inf = $model_channellive->getSingerAnchorInfo($uid);
				if (empty($anchor_inf)) {
					return;
				}
				$sun_total = $anchor_inf['anchor_current_experience'];
				$this->b_task_finished($uid, $sun_total, $start_date, sun_income_task_model::FINISHED_FROM_SUNINCOME_TASK_GIFT);
			}
		}
	}
	
	public function on_sun_received($uid)
	{
		$model_channellive = new ChannelLiveModel();
		$anchor_inf = $model_channellive->getSingerAnchorInfo($uid);
		
		if (empty($anchor_inf)) {
			return;
		}
		
		$start_date = date('Ym');
		$sun_total = $anchor_inf['anchor_current_experience'];
		
		$this->b_task_finished($uid, $sun_total, $start_date, sun_income_task_model::FINISHED_FROM_SUNINCOME_TASK_GIFT_SUN);
	}

	public function on_guard_received($uid, $money)
	{
		$start_date = date('Ym');
		$gold_month = $this->income_gold($uid, $money, $start_date);
		
		$open_ids = $this->b_task_open($uid, $gold_month, $start_date, sun_income_task_model::OPEN_FROM_SUNINCOME_TASK_GUARD);
		
		if (!empty($open_ids)) {
			if ($this->if_can_finished_auto($uid, $start_date)) {
				$this->new_anchor_task_finished($uid, $start_date, sun_income_task_model::FINISHED_FROM_SUNINCOME_TASK_GUARD_NEW_ANCHOR);
			} else {
				$model_channellive = new ChannelLiveModel();
				$anchor_inf = $model_channellive->getSingerAnchorInfo($uid);
				if (empty($anchor_inf)) {
					return;
				}
				$sun_total = $anchor_inf['anchor_current_experience'];
				$this->b_task_finished($uid, $sun_total, $start_date, sun_income_task_model::FINISHED_FROM_SUNINCOME_TASK_GUARD);
			}
		}
	}

	public function on_fans_group_sun_peek($uid)
	{
		$model_channellive = new ChannelLiveModel();
		$anchor_inf = $model_channellive->getSingerAnchorInfo($uid);
		
		if (empty($anchor_inf)) {
			return;
		}
		
		$start_date = date('Ym');
		$sun_total = $anchor_inf['anchor_current_experience'];
		
		$this->b_task_finished($uid, $sun_total, $start_date, sun_income_task_model::FINISHED_FROM_SUNINCOME_TASK_FANS_GROUP);
	}

	public function on_task_sun_award($uid)
	{
		$model_channellive = new ChannelLiveModel();
		$anchor_inf = $model_channellive->getSingerAnchorInfo($uid);
		
		if (empty($anchor_inf)) {
			return;
		}
		
		$start_date = date('Ym');
		$sun_total = $anchor_inf['anchor_current_experience'];
		
		$this->b_task_finished($uid, $sun_total, $start_date, sun_income_task_model::FINISHED_FROM_SUNINCOME_TASK_TASK_REWARD);
	}

	public function on_test($uid, $level)
	{
		$date = date("Ym");
		$sql = "INSERT IGNORE INTO test.t_anchor_sun_income_task_bill (uid,start_month,level,diamond_reward) VALUES ($uid,'$date',$level,1000)";
		$db = $this->getDbRecord();
		$rows = $db->query($sql);
				
		if (!empty($rows)) {
			LogApi::logProcess("sun_income_task_model:finish_sunincome_record sql:$sql affect_rows:" . $db->affected_rows);
		}

	}
}
?>