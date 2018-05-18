<?php

// 推送变更通知需加锁

// 更新游戏状态需加锁 

class game_saw_model extends ModelBase
{
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// error code
	
	const GAME_SAW_ERR_CODE_SUCCESS			= 0;		// 成功	
	const GAME_SAW_ERR_CODE_UNKNOWN			= -1;		// 未知错误
	const GAME_SAW_ERR_CODE_PARAM_ERR		= 201;		// 请求参数错误
	const GAME_SAW_ERR_CODE_LOOT_MISS		= 101;		// 道具/宝箱未抢到
	const GAME_SAW_ERR_CODE_LOOT_TOO_MUCH	= 102;		// 道具/宝箱达到最大可抢夺数量
	const GAME_SAW_ERR_CODE_ATTACK_FORM_NOT_SUPPORT	= 150;	// 不支持的攻击方式
	const GAME_SAW_ERR_CODE_PROP_NOT_SUPPORT		= 151;	// 不支持的道具攻击方式
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// status
	const GAME_SAW_STATUS_APPLY 			= 0;//申请
	const GAME_SAW_STATUS_ENROLL 			= 1;//报名
	const GAME_SAW_STATUS_ING 				= 2;//开启
	const GAME_SAW_STATUS_SUCCESS 			= 3;//成功
	const GAME_SAW_STATUS_FAILURE_TIMEOUT 	= 4;//失败超时
	const GAME_SAW_STATUS_FAILURE_BOMB 		= 5;//失败爆炸
	const GAME_SAW_STATUS_CANCEL			= 6;//游戏报名阶段，主播下播，导致取消
	const GAME_SAW_STATUS_FAILURE_LEAVE		= 7;//游戏过程中主播下播导致游戏失败
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// attack_type
	const GAME_SAW_ATTACK_WATER				= 1;//浇水攻击
	const GAME_SAW_ATTACK_DRILL				= 11;//电钻攻击
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// prop classify
	const GAME_SAW_PROP_CLASSIFY_PROP_SPECIAL	= 7;// 特殊道具
	const GAME_SAW_PROP_CLASSIFY_PROP_NORMAL	= 2;// 普通奖励物品
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	const GAME_SAW_NOTIRY_PER_ATK_TIMES		= 50;//每50次攻击推送hp,temperature 变更通知
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// attack parameter
	const GAME_SAW_PARAM_COOLING_WATER			= 6;//浇水单次降温 10 => 8 => 6
	const GAME_SAW_PARAM_ATK_DRILL				= 10;//电钻单次伤害
	const GAME_SAW_PARAM_ATK_DRILL_INCR_TM		= 0.4;//电钻伤害温度转换比 0.35 => 0.4
	const GAME_SAW_PARAM_ATK_DROP_PRIZE_CHANCE	= 0.0039;//攻击掉落奖品概率 0.005 => 0.004 => 0.0045 => 0.0039
	const GAME_SAW_PARAM_ATK_DROP_PROP_CHANCE 	= 0.00035;//攻击掉落游戏道具概率 0.001 => 0.00035
	// 供测试调试用
	//const GAME_SAW_PARAM_ATK_DROP_PRIZE_CHANCE	= 0.1;//攻击掉落奖品概率 0.005 => 0.004 => 0.0045 => 0.0039
	//const GAME_SAW_PARAM_ATK_DROP_PROP_CHANCE 	= 0.5;//攻击掉落游戏道具概率 0.001 => 0.00035
	
	const GAME_SAW_PARAM_SAW_ATK_SAW			= 50;//电锯单次伤害 25 => 50
	const GAME_SAW_PARAM_SAW_ATK_RATE			= 5;//电锯攻击频率(m次/s) 配置为0.2s攻击一次
	const GAME_SAW_PARAM_SAW_ATK_INCR_TM		= 0.5;//电锯伤害温度转换比
	const GAME_SAW_PARAM_SAW_ATK_DURATION		= 10;//电锯攻击持续时间
	
	const GAME_SAW_PARAM_ICE_BLOCK_DECR_TM		= 0;//冰块降温
	const GAME_SAW_PARAM_ICE_BLOCK_DURATION		= 10;//冰块持续时间
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// prop special id
	// TODO: 根据实际id进行修改
	const GAME_SAW_PROP_ID_ICE_BLOCK			= 20;//特殊道具冰块id
	const GAME_SAW_PROP_ID_SAW					= 10;//特殊道具电锯id
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// prop normal id
	// TODO: 根据实际id进行修改
	const GAME_SAW_PROP_ID_SUN					= 11;//普通物品阳光id
	const GAME_SAW_PROP_ID_TICKET				= 20;//普通物品帮会票id
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// redis expire time
// 	const EXPIRE_TIME_SAW_GAME_STATUS_LOCK		= 200;	// 游戏状态锁过期时间 ms
// 	const EXPIRE_TIME_SAW_GAME_LOOT_PROP_LOCK	= 200;	// 游戏过程中道具抢夺锁过期时间 ms
// 	const EXPIRE_TIME_SAW_GAME_LOOT_BOX_LOCK	= 200;	// 游戏结束后宝箱抢夺锁过期时间 ms
// 	const EXPIRE_TIME_SAW_HP_TM_NOTIFY_LOCK		= 1;	// 游戏过程hp,温度变更通知锁过期时间s
// 	const EXPIRE_TIME_SAW_GAME_LOOT_BOX_USER_LOCK	= 200;	// 用户锁宝箱抢夺锁
	
	
	// redis expire time
	const EXPIRE_TIME_SAW_GAME_STATUS_LOCK		= 1;	// 游戏状态锁过期时间 s
	const EXPIRE_TIME_SAW_GAME_LOOT_PROP_LOCK	= 1;	// 游戏过程中道具抢夺锁过期时间 s
	const EXPIRE_TIME_SAW_GAME_LOOT_BOX_LOCK	= 1;	// 游戏结束后宝箱抢夺锁过期时间 s
	const EXPIRE_TIME_SAW_HP_TM_NOTIFY_LOCK		= 1;	// 游戏过程hp,温度变更通知锁过期时间s
	const EXPIRE_TIME_SAW_GAME_LOOT_BOX_USER_LOCK	= 1;	// 用户锁宝箱抢夺锁
	const EXPIRE_TIME_SAW_PROP_SPECIAL_DROP_CD_MAX = 300;	//游戏道具掉落最大cd时间
	const EXPIRE_TIME_SAW_PROP_SPECIAL_DROP_LOCK = 1;	// 游戏过程中游戏道具掉落锁过期时间s
	const EXPIRE_TIME_SAW_PROP_NORMAL_DROP_LOCK	 = 1;	// 游戏过程中普通奖励掉落锁过期时间s 
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	const REDIS_KEY_SAW_GAME_ID	= 'SAW_GAME_ID_';
	
	const REDIS_KEY_SAW_GAME_INF = 'ANCHOR_SAW_INFO_';
	
	const REDIS_KEY_SAW_GAME_STATUS = 'ANCHOR_SAW_STATUS_';
	
	const REDIS_KEY_SAW_ENROLL = 'SAW_USERS_';
	
	const REDIS_KEY_SAW_HP_NOW = 'SAW_HP_';
	
	const REDIS_KEY_SAW_TEMPERATURE_LIMIT = 'SAW_TEMPERATURE_';
	
	const REDIS_KEY_SAW_TEMPERATURE_NOW = 'SAW_TEMPERATURE_NOW_';
	
	const REDIS_KEY_SAW_SUN_BOX_POOL = 'SAW_SUN_BOXES';
	
	const REDIS_KEY_SAW_LIT_BOX_POOL = 'SAW_SMALL_BOXES';
	
	const REDIS_KEY_SAW_PROP_POOL = 'SAW_PRIZE_POOL';
	
	const REDIS_KEY_SAW_PROP_SPECIAL_POOL = 'SAW_PROPS';
	
	const REDIS_KEY_SAW_DROP_ID = 'SAW_DROP_ID_';
	
	const REDIS_KEY_SAW_PROP_DROP = 'SAW_PROP_DROP_';										// 道具掉落待领取记录
	
	const REDIS_KEY_SAW_BOX_DROP = 'SAW_BOX_DROP_';											// 宝箱掉落待领取记录
	
	const REDIS_KEY_SAW_CONFIG = 'UNION_GAME_SAW_INFO';
	
	const REDIS_KEY_SAW_PROP_NORMAL_DROP_NUMBER = 'SAW_PROP_NORMAL_DROP_NUMBER_';			// 普通奖励已掉落次数

	const REDIS_KEY_SAW_PROP_SPECIAL_DROP_NUMBER = 'SAW_PROP_SPECIAL_DROP_NUMBER_';			// 特殊道具已掉落次数
	
	const REDIS_KEY_SAW_PROP_SPECIAL_IN_USE	= 'SAW_PROP_SPECIAL_IN_USE_';					// 根据此key判断是否可以掉落特殊道具
	
	const REDIS_KEY_SAW_PROP_SPECIAL_ICE_BLOCK_IN_USE = 'SAW_PROP_SPECIAL_ICE_BLOCK_IN_USE_';		// 根据此key判断冰块是否有效
	
	const REDIS_KEY_SAW_USER_ATTACK_TIMES = 'SAW_USER_ATTACK_TIMES_';						// 记录用户攻击次数  hash, TODO: expire
	
	const REDIS_KEY_SAW_ATTACK_TIMES = 'SAW_ATTACK_TIEMS_';									// 记录总攻击次数，用于推送通知		TODO: expire
	
	const REDIS_KEY_SAW_GAME_STATUS_LOCK = 'STR_SAW_GAME_STATUS_LOCK_';						// 游戏状态变更锁
	
	const REDIS_KEY_SAW_LOOT_PROP_LOCK	= 'STR_SAW_GAME_LOOT_PROP_LOCK_';					// 游戏过程中道具抢夺锁
	
	const REDIS_KEY_SAW_LOOT_BOX_LOCK	= 'STR_SAW_GAME_LOOT_BOX_LOCK_';					// 游戏结束后宝箱抢夺锁
	
	const REDIS_KEY_SAW_HP_TM_NOTIFY_LOCK = 'STR_SAW_HP_TM_NOTIFY_LOCK_';					// 锁，是否推送游戏通知
	
	const REDIS_KEY_SAW_LOOT_BOX_USER_LOCK	= 'STR_SAW_GAME_LOOT_BOX_USER_LOCK_';			// 用户抢夺道具锁，道具抢夺有两个锁，一个是用户锁，一个是宝箱锁，先锁用户，再锁宝箱
	
	const REDIS_KEY_SAW_LOOT_BOX_USER_TIMES	= 'STR_SAW_GAME_LOOT_BOX_USER_TIMES_';			// 用户已抢到宝箱的数量
	
	const REDIS_KEY_SAW_LOOT_BOX_TIME_OUT_LOCK = 'SAW_GAME_OVER_BOX_TIME_OUT_LOCK_';		// 判断是否可以继续抢夺宝箱
	
	const REDIS_KEY_SAW_PROP_NORMAL_DROP_TOTAL	= 'SAW_PROP_NORMAL_DROP_TOTAL_';			// 游戏过程中物品奖励掉落次数
	
	const REDIS_KEY_SAW_PROP_SPECIAL_DROP_LOCK = 'SAW_GAME_PROP_SPECIAL_DROP_LOCK_';		// 游戏过程中游戏道具掉落锁
	
	const REDIS_KEY_SAW_PROP_NORMAL_DROP_LOCK = 'SAW_GAME_PROP_NORMAL_DROP_LOCK_';			// 游戏过程中普通奖励掉落锁


	// --------------------------------------tips
	const GAME_SAW_TIPS_OPEN = true; // 是否推送tips开关
	const GAME_SAW_TIPS_INTERVAL = 15; //tips 游戏开始时推送， 每15秒推送一个，直到推送完为止

	const REDIS_KEY_SAW_TIPS_NOTIFY_LOCK = "STR_SAW_GAME_TIPS_NOTIFY_LOCK_";		// 锁，推送tips时机
	const REDIS_KEY_SAW_TIPS_NOTIFY_TIMES = "STR_SAW_GAME_TIPS_NOTIFY_TIMES_";		// 记录tips 通知次数
	
	const EXPIRE_TIME_SAW_TIPS_NOTIFY_TIMES = 300;									// tips 掉落次数key过期时间
	// tips--------------------------------------

	// -------------------------------------电锯卡
	const REDIS_KEY_SAW_CARD_PROP_POOL = 'saw:SAW_CARD_PRIZE_POOL';
	const REDIS_KEY_SAW_CARD_LIT_BOX_POOL = 'saw:SAW_CARD_SMALL_BOXES';
	const REDIS_KEY_SAW_CARD_SUN_BOX_POLL = 'saw:SAW_CARD_SUN_BOXES';
	const REDIS_KEY_SAW_IS_CARD = 'saw:IS_CARD_START:';
	// 电锯卡--------------------------------------

	// ------------------------------------- 物品获得上限
	// type 		:set
	// key 			:saw:SET_USER_PROP_LIMIT:$uid
	// mem 			:$prop_type
	const REDIS_KEY_SAW_USER_PROP_LIMIT = "saw:SET_USER_PROP_LIMIT:";
	// 物品获得上限 -------------------------------------

	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function get_redis_key_saw_id($singer_id)
	{
		return game_saw_model::REDIS_KEY_SAW_GAME_ID . $singer_id;
	}
	public function get_redis_key_saw_game_inf($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_GAME_INF . $game_id;
	}
	public function get_redis_key_saw_game_status($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_GAME_STATUS . $game_id;
	}
	public function get_redis_key_saw_enroll($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_ENROLL . $game_id;
	}
	public function get_redis_key_saw_hp_now($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_HP_NOW . $game_id;
	}
	public function get_redis_key_saw_temperature_limit($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_TEMPERATURE_LIMIT . $game_id;
	}
	public function get_redis_key_saw_temperature_now($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_TEMPERATURE_NOW . $game_id;
	}
	public function get_redis_key_saw_sun_box_pool($game_id)
	{
		if (!empty($game_id) && $this->b_saw_card($game_id)) {
			return game_saw_model::REDIS_KEY_SAW_CARD_SUN_BOX_POLL;
		}
		return game_saw_model::REDIS_KEY_SAW_SUN_BOX_POOL;
	}
	public function get_redis_key_saw_lit_box_pool($game_id)
	{
		if (!empty($game_id) && $this->b_saw_card($game_id)) {
			return game_saw_model::REDIS_KEY_SAW_CARD_LIT_BOX_POOL;
		}
		return game_saw_model::REDIS_KEY_SAW_LIT_BOX_POOL;
	}
	public function get_redis_key_saw_prop_pool($game_id)
	{
		if (!empty($game_id) && $this->b_saw_card($game_id)) {
			return game_saw_model::REDIS_KEY_SAW_CARD_PROP_POOL;
		}
		return game_saw_model::REDIS_KEY_SAW_PROP_POOL;
	}
	public function get_redis_key_saw_prop_special_pool()
	{
		return game_saw_model::REDIS_KEY_SAW_PROP_SPECIAL_POOL;
	}
	public function get_redis_key_saw_drop_id($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_DROP_ID . $game_id;
	}
	public function get_redis_key_saw_box_drop($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_BOX_DROP . $game_id;
	}
	public function get_redis_key_saw_prop_drop($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_PROP_DROP . $game_id;
	}
	public function get_redis_key_saw_prop_drop_number($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_PROP_NORMAL_DROP_NUMBER . $game_id;
	}
	public function get_redis_key_saw_prop_special_drop_number($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_PROP_SPECIAL_DROP_NUMBER . $game_id;
	}
	public function get_redis_key_saw_prop_special_in_use($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_PROP_SPECIAL_IN_USE . $game_id;
	}
	public function get_redis_key_saw_prop_special_ice_block_in_use($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_PROP_SPECIAL_ICE_BLOCK_IN_USE . $game_id;
	}
	public function get_redis_key_saw_user_attack_times($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_USER_ATTACK_TIMES . $game_id;
	}
	public function get_redis_key_saw_attack_times($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_ATTACK_TIMES . $game_id;
	}
	public function get_redis_key_saw_config()
	{
		return game_saw_model::REDIS_KEY_SAW_CONFIG;
	}
	public function get_redis_key_saw_game_status_lock($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_GAME_STATUS_LOCK . $game_id;
	}
	public function get_redis_key_saw_game_loot_prop_lock($game_id, $drop_id)
	{
		return game_saw_model::REDIS_KEY_SAW_LOOT_PROP_LOCK . $game_id . '_' . $drop_id;
	}
	public function get_redis_key_saw_game_loot_box_lock($game_id, $drop_id)
	{
		return game_saw_model::REDIS_KEY_SAW_LOOT_BOX_LOCK . $game_id . '_' . $drop_id;
	}
	public function get_redis_key_saw_game_hp_tm_notify_lock($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_HP_TM_NOTIFY_LOCK . $game_id;
	}
	public function get_redis_key_saw_game_loot_box_user_lock($game_id, $uid)
	{
		return game_saw_model::REDIS_KEY_SAW_LOOT_BOX_USER_LOCK . $game_id . '_' . $uid;
	}
	public function get_redis_key_saw_game_loot_box_user_times($game_id, $uid)
	{
		return game_saw_model::REDIS_KEY_SAW_LOOT_BOX_USER_TIMES . $game_id . '_' . $uid;
	}
	public function get_redis_key_saw_game_loot_box_time_out_lock($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_LOOT_BOX_TIME_OUT_LOCK . $game_id;
	}
	public function get_redis_key_prop_normal_drop_total($game_id) 
	{
		return game_saw_model::REDIS_KEY_SAW_PROP_NORMAL_DROP_TOTAL . $game_id;
	}
	public function get_redis_key_prop_special_drop_lock($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_PROP_SPECIAL_DROP_LOCK . $game_id;
	}
	public function get_redis_key_prop_normal_drop_lock($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_PROP_NORMAL_DROP_LOCK . $game_id;
	}
	public function get_redis_key_saw_tips_notify_lock($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_TIPS_NOTIFY_LOCK . $game_id;
	}
	public function get_redis_key_saw_tips_notify_times($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_TIPS_NOTIFY_TIMES . $game_id;
	}
	public function get_redis_key_saw_is_card($game_id)
	{
		return game_saw_model::REDIS_KEY_SAW_IS_CARD . $game_id;
	}
	public function get_redis_key_user_prop_limit($uid)
	{
		return game_saw_model::REDIS_KEY_SAW_USER_PROP_LIMIT . $uid;
	}
	public function get_saw_tips_array()
	{
		return array (
			"使用电钻攻击血量的时候温度会增加~所以需要使用浇水配合呦~",
			"冰块道具生效后一段时间内攻击血量不会增加温度值",
			"我绝对不会告诉你温度满了箱子会爆炸的",
			"在规定的时间内打爆锁头就会有很多箱子爆出来呦~",
			"电锯道具使用后会迅速减少血量，要注意浇水啊",
			"掉血的时候有概率掉落奖励，谁手快就是谁的！"
		);
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function redis_set_nx_ex($redis, $key, $value, $ex)
	{
		$ret = false;
		
		$ret = $redis->setnx($key, $value);
		if ($ret) {
			$redis->expire($key, $ex);
		}
		
		return $ret;		
	}
	public function redis_set_nx_px($redis, $key, $value, $px)
	{
		$ret = false;
		$ret = $redis->setnx($key, $value);
		
		if ($ret) {
			$redis->pexpire($key, px);
		}
		
		return $ret;
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	public function b_enroll($game_id, $uid)
	{
		$redis = $this->getRedisMaster();
		return $redis->sismember($this->get_redis_key_saw_enroll($game_id), $uid);
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// use prop special
	
	//使用特殊道具
	public function use_prop_special($game_id, $prop_id)
	{
		$ret = false;
		
		$redis = $this->getRedisMaster();
		
		if ($prop_id == game_saw_model::GAME_SAW_PROP_ID_ICE_BLOCK) {
			$ret = $redis->setex($this->get_redis_key_saw_prop_special_ice_block_in_use($game_id), game_saw_model::GAME_SAW_PARAM_ICE_BLOCK_DURATION, 1);
		}
		
		return $ret;
	}
	
	public function prop_special_ice_block_in_use($game_id)
	{
		$ret = false;
		
		$redis = $this->getRedisMaster();
		
		$res = $redis->ttl($this->get_redis_key_saw_prop_special_ice_block_in_use($game_id));
		
		if (!empty($res) && intval($res) > 0) {
			$ret = true;
		}
		
		return $ret;
	}
	
	//特殊道具掉落cd
	public function droped_prop_special($game_id, $prop_id)
	{
		$redis = $this->getRedisMaster();
		
		if ($prop_id == game_saw_model::GAME_SAW_PROP_ID_ICE_BLOCK) {
			$ret = $redis->setex($this->get_redis_key_saw_prop_special_in_use($game_id), game_saw_model::GAME_SAW_PARAM_ICE_BLOCK_DURATION + 3, $prop_id);
		} else if ($prop_id == game_saw_model::GAME_SAW_PROP_ID_SAW) {
			$ret = $redis->setex($this->get_redis_key_saw_prop_special_in_use($game_id), game_saw_model::GAME_SAW_PARAM_SAW_ATK_DURATION + 5, $prop_id);
		}
	}
	
	//是否可以掉落特殊道具
	public function if_can_drop_prop_special($game_id)
	{
		$ret = false;
		
		$r_max = 100000;
		$r_min = 1;
		$r = game_saw_model::GAME_SAW_PARAM_ATK_DROP_PROP_CHANCE * $r_max;
		$rd = rand($r_min, $r_max);
		
		if ($rd > $r) {
			return $ret;
		}
		
		$ret = true;
		
		$redis = $this->getRedisMaster();
		
		$res = $redis->exists($this->get_redis_key_saw_prop_special_in_use($game_id));
		if (!empty($res)) {
			$ret = false;
		}
		
		return $ret;
	}
	
	//是否可以掉落奖品
	public function if_can_drop_prize($game_id)
	{
		$ret = false;
		
		$r_max = 100000;
		$r_min = 1;
		$r = game_saw_model::GAME_SAW_PARAM_ATK_DROP_PRIZE_CHANCE * $r_max;
		$rd = rand($r_min, $r_max);
		
		if ($rd < $r) {
			$ret = true;
		}
		
		return $ret;
	}
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function get_saw_game_id_by_singer_id($singer_id)
	{
		$redis = $this->getRedisMaster();
		return $redis->get($this->get_redis_key_saw_id($singer_id));
	}
	
	public function get_enroll_number($game_id)
	{
		$ret = 0;
		
		$redis = $this->getRedisMaster();
		
		$ret = $redis->scard($this->get_redis_key_saw_enroll($game_id));
		
		$ret = isset($ret)?intval($ret):0;
		
		return $ret;
	}
	
	public function get_hp_now($game_id)
	{
		$ret = 0;
		
		$redis = $this->getRedisMaster();
		
		$ret = $redis->get($this->get_redis_key_saw_hp_now($game_id));
		
		$ret = isset($ret)?doubleval($ret):0.0;
		
		return $ret;
	}
	
	public function get_temperature_now($game_id)
	{
		$ret = 0;
		
		$redis = $this->getRedisMaster();
		
		$ret = $redis->get($this->get_redis_key_saw_temperature_now($game_id));
		
		$ret = isset($ret)?doubleval($ret):0.0;
		
		return $ret;
	}
	
	public function get_temperature_total($game_id)
	{
		$ret = 0;
		
		$redis = $this->getRedisMaster();
		
		$ret = $redis->get($this->get_redis_key_saw_temperature_limit($game_id));
		
		$ret = isset($ret)?doubleval($ret):0.0;
		
		return $ret;
	}
	
	public function get_saw_game_status($game_id)
	{
		$ret = 0;
		
		$redis = $this->getRedisMaster();
		
		$ret = $redis->get($this->get_redis_key_saw_game_status($game_id));
		
		$ret = isset($ret)?doubleval($ret):0;
		
		return $ret;
	}
	
	public function get_saw_game_base_inf($game_id)
	{
		$ret = null;
		
		$redis = $this->getRedisMaster();
		
		$ret = $redis->get($this->get_redis_key_saw_game_inf($game_id));
		
		if (!empty($ret)) {
			$ret = json_decode($ret, true);
		}
		
		return $ret;
	}
	
	public function get_saw_game_drop_id($game_id)
	{
		$ret = 0;
		
		$redis = $this->getRedisMaster();
		$ret = $redis->incr($this->get_redis_key_saw_drop_id($game_id));
				
		return $ret;
	}
	public function get_saw_game_config()
	{
		$redis = $this->getRedisMaster();
		
		$ret = $redis->get($this->get_redis_key_saw_config());
		
		if (!empty($ret)) {
			$ret = json_decode($ret, true);
		}
		
		return $ret;
	}
	public function get_goods_inf_by_id($prop_id)
	{
		$key = "h_goods_inf";
		$field = $prop_id . '';
		
		$redis = $this->getRedisMaster();
		$inf = $redis->hGet($key, $field);
		
		if (!empty($inf)) {
			$inf = json_decode($inf, true);
			return $inf;
		}
		
		$sql= "SELECT * FROM card.goods_info WHERE id=$prop_id";
		
		$db_card = $this->getDbMain();
		
		$rows = $db_card->query($sql);
		
		if (!empty($rows) && $rows->num_rows > 0) {
			$row = $rows->fetch_assoc();
			
			$redis->hSet($key, $field, json_encode($row));
			
			return $row;
		} else {
			return null;
		}
	}
	public function get_prop_special_inf_by_id($prop_id)
	{
		$key = 'h_saw_prop_inf';
		$field = $prop_id . '';
		
		$redis = $this->getRedisMaster();
		$inf = $redis->hGet($key, $field);
		
		if (!empty($inf)) {
			$inf = json_decode($inf, true);
			return $inf;
		}
		
		$sql = "SELECT * FROM saw.game_props WHERE prop_id=$prop_id";
		
		$db_saw = $this->getDbMain();
		
		$rows = $db_saw->query($sql);
		
		if (!empty($rows) && $rows->num_rows > 0) {
			$row = $rows->fetch_assoc();
			$redis->hSet($key, $field, json_encode($row));
			return $row;
		} else {
			return null;
		}
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 道具掉落待领取记录
	public function insert_prop_drop_to_be_loot($game_id, $drop_id, $prop_inf)
	{
		$redis = $this->getRedisMaster();
		
		$ret = $redis->hSetNx($this->get_redis_key_saw_prop_drop($game_id), $drop_id . '', json_encode($prop_inf));
		
		return $ret;
	}
	
	// 宝箱掉落待领取记录
	public function insert_box_drop_to_be_loot($game_id, $drop_id, $box_inf)
	{
		$redis = $this->getRedisMaster();
		$res = $redis->hSetNx($this->get_redis_key_saw_box_drop($game_id), $drop_id . '', json_encode($box_inf));
		return $res;
	}
	
	// get and del
	public function get_prop_drop_to_be_loot($game_id, $drop_id)
	{
		$redis = $this->getRedisMaster();
		
		$key_drop = $this->get_redis_key_saw_prop_drop($game_id);
		$res = $redis->hGet($key_drop, $drop_id . '');
		$redis->hDel($key_drop, $drop_id . '');
		
		return $res;
	}
	
	public function get_box_drop_to_be_loot($game_id, $drop_id)
	{
		$redis = $this->getRedisMaster();
		
		$key_drop = $this->get_redis_key_saw_box_drop($game_id);
		
		$res = $redis->hGet($key_drop, $drop_id . '');
		
		if (!empty($res)) {
			$res = json_decode($res, true);
			$redis->hDel($key_drop, $drop_id . '');
		}
		
		return $res;
	}
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function drop_saw_game_box($game_id, $num, $drop_pool)
	{
		$drops= array();
		
		$redis = $this->getRedisMaster();
		
		$drop_inf = $redis->sMembers($drop_pool);
		
		$drop_infs = array();
		
		if (!empty($drop_inf)) {
			if (is_array($drop_inf)) {
				$drop_infs = $drop_inf;
			} else {
				array_push($drop_infs, $drop_inf);
			}
		}
		
		// 获取配置最低报名人数
		// 获取当前报名人数
		// 计算出掉落系数
		$conf = $this->get_saw_game_config();
		$enroll_lower = $conf['enroll_lower_limit'];
		$enroll_number = $this->get_enroll_number($game_id);
		
		if ($enroll_number < $enroll_lower) {
			$enroll_number = $enroll_lower;
		}
		
		// 公式 (实际人数/报名下限)^0.8
		$drop_factor = pow($enroll_number/$enroll_lower, 0.8);
		
		foreach ($drop_infs as $inf) {
			$inf = json_decode($inf, true);
			
			// base_factor 为-1表示，必掉
			$real_num = 0;
			if ($inf['base_factor'] == -1) {
				$real_num = 1;
			} else {
				$real_num = floor($drop_factor * $inf['base_factor']);
			}
			
			if ($real_num < 1) {
				continue;
			}
			
			for ($i = 0; $i < $real_num; ++$i) {
				$drop_id = $this->get_saw_game_drop_id($game_id);
					
				$inf_f['drop_id'] = $drop_id;
				$inf_f['box_id'] = $inf['box_id'];
				$inf_f['box_name'] = $inf['box_name'];
				$inf_f['box_img'] = $inf['box_name'];
				$inf_f['box_type'] = $inf['type'];
				if ($this->insert_box_drop_to_be_loot($game_id, $drop_id, $inf_f)) {
					array_push($drops, $inf_f);
				}
			}
		}
		
		return $drops;
	}
	
	public function drop_saw_game_sun_box($game_id, $num)
	{
		return $this->drop_saw_game_box($game_id, $num, $this->get_redis_key_saw_sun_box_pool($game_id));
	}
	
	public function drop_saw_game_lit_box($game_id, $num)
	{
		return $this->drop_saw_game_box($game_id, $num, $this->get_redis_key_saw_lit_box_pool($game_id));
	}
	
	public function drop_saw_game_prop_normal($game_id, $num)
	{
		$drop_props = array();
		
		$redis = $this->getRedisMaster();
		
		$key_lock = $this->get_redis_key_prop_normal_drop_lock($game_id);
		
		$rd = rand();
		
		$ok = $this->redis_set_nx_ex($redis, $key_lock, $rd, game_saw_model::EXPIRE_TIME_SAW_PROP_NORMAL_DROP_LOCK);
		
		if (!$ok) {
			return $drop_props;
		}
		
		$conf = $this->get_saw_game_config();
		
		// 判断次数是有已掉完
		$drop_total = $redis->incrBy($this->get_redis_key_prop_normal_drop_total($game_id), 0);
		if ($drop_total >= $conf['props_up']) {
			return $drop_props;
		}
		
		$prop_inf = $redis->sMembers($this->get_redis_key_saw_prop_pool($game_id));
		
		$prop_infs = array();
		
		if (!empty($prop_inf)) {			
			if (is_array($prop_inf)) {
				$prop_infs = $prop_inf;
			} else {
				array_push($prop_infs, $prop_inf);
			}
		}
		
		$rd_prob = rand(1, 10000);
		foreach ($prop_infs as $inf) {
			$inf = json_decode($inf, true);

			$up_prob = isset($inf['probup']) ? $inf['probup'] : 0;
			$low_prob = isset($inf['problow']) ? $inf['problow'] : 0;
			
			if (empty($up_prob) || empty($low_prob)) {
				continue;
			}

			if ($rd_prob < $low_prob || $rd_prob > $up_prob) {
				continue;
			}

			$prop_id = $inf['prize_id'];
			// 根据inf 中的最大数量限制，随机一个数字
			// 加到物品掉落数量缓存中
			// 判断物品掉落数量是否大于物品掉落上限
			// 如果大于上限，判断物品掉落数量-物品掉落上限是否小于本次计算出的掉落数量
			// 如果小于，则掉落该数量的物品
			// 否则不管了
			if ($prop_id == game_saw_model::GAME_SAW_PROP_ID_SUN) {
				$up_limit = $conf['sunshine'];
			} else if ($prop_id == game_saw_model::GAME_SAW_PROP_ID_TICKET) {
				$up_limit = $conf['ticket'];
			} else {
				// 其他物品没有上限限制
				$up_limit = 99999999999;
			}
			
			$drop_up = isset($inf['up'])?$inf['up']:1;
			$drop_low = isset($inf['low'])?$inf['low']:1;
			
			if ($drop_low > $drop_up) {
				$drop_low = $drop_up;
			}
			
			$rand_num = rand($drop_low, $drop_up);
				
			$total = $this->upd_prize_drop_cnt($game_id, $prop_id, $rand_num);
				
			if ($total < $up_limit) {
				$goods_inf = $this->get_goods_inf_by_id($prop_id);
				
				if (!empty($goods_inf)) {
					$drop_id = $this->get_saw_game_drop_id($game_id);
					$inf_f['prop_name'] = $goods_inf['goods_name'];
					$inf_f['prop_icon'] = $goods_inf['goods_icon'];
					$inf_f['drop_id'] = $drop_id;
					$inf_f['prop_id'] = $prop_id;
					$inf_f['prop_type'] = $inf['type'];
					$inf_f['prop_classify'] = game_saw_model::GAME_SAW_PROP_CLASSIFY_PROP_NORMAL;
					$inf_f['prop_num'] = $rand_num;
					$inf_f['attack_rate'] = 0;
					$inf_f['attack_duration'] = 0;
					$inf_f['quality'] = $goods_inf['quality'];
					
					//存储掉落物品至待领取池中
					if ($this->insert_prop_drop_to_be_loot($game_id, $drop_id, $inf_f)) {
						array_push($drop_props, $inf_f);
						
						// 增加总掉落次数
						$redis->incr($this->get_redis_key_prop_normal_drop_total($game_id));
					}
				}
			}
		}
		
		// 释放锁
		if ($redis->get($key_lock) == $rd) {
			$redis->del($key_lock);
		}
		
		return $drop_props;
	}
	
	public function drop_saw_game_prop_special($game_id, $num)
	{	
		$drop_props = array();
		
		$redis = $this->getRedisMaster();
		
		$key_lock = $this->get_redis_key_prop_special_drop_lock($game_id);
		
		$rd = rand();
		
		$ok = $this->redis_set_nx_ex($redis, $key_lock, $rd, game_saw_model::EXPIRE_TIME_SAW_PROP_SPECIAL_DROP_LOCK);
		
		if (!$ok) {
			return $drop_props;
		}
		
		$prop_inf = $redis->sRandMember($this->get_redis_key_saw_prop_special_pool() /*, $num*/);
		
		$prop_infs = array();
		
		if (!empty($prop_inf)) {
			if (is_array($prop_inf)) {
				$prop_infs = json_decode($prop_inf, true);
			} else {
				array_push($prop_infs, json_decode($prop_inf, true));
			}
		}
		$conf = $this->get_saw_game_config();
		$up_limit = $conf['attacks_props_up_num'];
		
		foreach ($prop_infs as $inf) {
			$prop_id = $inf['prop_id'];
				
			$total = $this->upd_prop_special_drop_cnt($game_id, $game_id, 1);
			if ($total > $up_limit) {
				break;
			}
				
			$drop_id = $this->get_saw_game_drop_id($game_id);
			$inf_f['prop_name'] = $inf['name'];
			$inf_f['prop_icon'] = isset($inf['prop_img'])?$inf['prop_img']:"";
			$inf_f['drop_id'] = $drop_id;
			$inf_f['prop_id'] = $prop_id;
			$inf_f['prop_type'] = 0;
			$inf_f['prop_classify'] = game_saw_model::GAME_SAW_PROP_CLASSIFY_PROP_SPECIAL;
			$inf_f['prop_num'] = 1;
			$inf_f['quality'] = 1;
				
			if ($prop_id == game_saw_model::GAME_SAW_PROP_ID_SAW) {
				$inf_f['attack_rate'] = game_saw_model::GAME_SAW_PARAM_SAW_ATK_RATE;
				$inf_f['attack_duration'] = game_saw_model::GAME_SAW_PARAM_SAW_ATK_DURATION;
			} else if ($prop_id == game_saw_model::GAME_SAW_PROP_ID_ICE_BLOCK) {
				$inf_f['attack_rate'] = 0;
				$inf_f['attack_duration'] = game_saw_model::GAME_SAW_PARAM_ICE_BLOCK_DURATION;
			}
		
			// drop_id
			// prop 详情
			if ($this->insert_prop_drop_to_be_loot($game_id, $drop_id, $inf_f)) {
				array_push($drop_props, $inf_f);
				$this->upd_prop_special_drop_cd($game_id, $prop_id, game_saw_model::EXPIRE_TIME_SAW_PROP_SPECIAL_DROP_CD_MAX);
			}
		}
		
		// 释放锁
		if ($redis->get($key_lock) == $rd) {
			$redis->del($key_lock);
		}
		
		return $drop_props;
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 外部+锁
	public function upd_saw_game_hp($game_id, $hp)
	{
		$redis = $this->getRedisMaster();
		
		return $redis->incrBy($this->get_redis_key_saw_hp_now($game_id), $hp);
	}
	
	public function upd_saw_game_temperature($game_id, $tm)
	{
		$redis = $this->getRedisMaster();
		
		// 向上取整
		return $redis->incrBy($this->get_redis_key_saw_temperature_now($game_id), ceil($tm));
	}
	
	public function reset_saw_game_temperature($game_id)
	{
		$redis = $this->getRedisMaster();
		
		return $redis->set($this->get_redis_key_saw_temperature_now($game_id), 0);
	}
	
	public function upd_saw_game_status($game_id, $status)
	{
		$ret = false;
		
		$redis = $this->getRedisMaster();
		$key_status_lock = $this->get_redis_key_saw_game_status_lock($game_id);
		$key_status = $this->get_redis_key_saw_game_status($game_id);
		$rand = rand();
//		if ($redis->set($key_status_lock, $rand, Array('nx', 'px' => game_saw_model::EXPIRE_TIME_SAW_GAME_STATUS_LOCK))) {
		if ($this->redis_set_nx_ex($redis, $key_status_lock, $rand, game_saw_model::EXPIRE_TIME_SAW_GAME_STATUS_LOCK)) {
			if ($status == game_saw_model::GAME_SAW_STATUS_FAILURE_BOMB || 
				$status == game_saw_model::GAME_SAW_STATUS_FAILURE_LEAVE || 
				$status == game_saw_model::GAME_SAW_STATUS_SUCCESS) 
			{
				if ($redis->get($key_status) == game_saw_model::GAME_SAW_STATUS_ING) {
					$ret = $redis->set($this->get_redis_key_saw_game_status($game_id), $status);
				}
					
			} else if ($status == game_saw_model::GAME_SAW_STATUS_CANCEL) {
				if ($redis->get($key_status) == game_saw_model::GAME_SAW_STATUS_ENROLL) {
					$ret = $redis->set($this->get_redis_key_saw_game_status($game_id), $status);
				}
			}
			
			if ($rand == $redis->get($key_status_lock)) {
				$redis->del($key_status_lock);
			}
		}
		
		if ($ret) {
			$sql = "UPDATE saw.game_saw SET game_status=$status WHERE game_id=$game_id";
			$db_saw = $this->getDbMain();
			$rows = $db_saw->query($sql);
			
			if (empty($rows) || $db_saw->affected_rows <= 0) {
				LogApi::logProcess("game_saw_model:upd_saw_game_status db failure. game_id:$game_id status:$status");
			}
			
			// 清除游戏缓存状态
			$this->clear_cache($game_id);
			
			// http通知web
			$url = GlobalConfig::GetUrlPrefix() . "/xcbb_web/business/mobile/saw/game_over?game_id=$game_id&status=$status";
			$ch = curl_init();
			$curl_opt = array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT_MS => 3000
			);
			curl_setopt_array($ch, $curl_opt);
			$data = curl_exec($ch);
			curl_close($ch);
			
			LogApi::logProcess("game_saw_model:upd_saw_game_status notify web game_id:$game_id status:$status rs:$data uri:$url");
		}
		return $ret;		
	}
	
	public function upd_saw_game_user_attack_times($game_id, $uid, $t)
	{
		$redis = $this->getRedisMaster();
		
		return $redis->hIncrBy($this->get_redis_key_saw_user_attack_times($game_id), $uid . '', $t);
	}
	
	public function upd_saw_game_attack_times($game_id, $t)
	{
		$redis = $this->getRedisMaster();
		
		return $redis->incrBy($this->get_redis_key_saw_attack_times($game_id), $t);
	}
	
	public function reset_saw_game_attack_times($game_id)
	{
		$redis = $this->getRedisMaster();
		
		$redis->set($this->get_redis_key_saw_attack_times($game_id), 0);
		
		$redis->expire($this->get_redis_key_saw_attack_times($game_id), 10*60);
	}
	
	public function upd_prop_special_drop_cnt($game_id, $prop_id, $num)
	{
		$redis = $this->getRedisMaster();
		
		return $redis->hIncrBy($this->get_redis_key_saw_prop_special_drop_number($game_id), $prop_id . '', $num);
	}
	
	public function upd_prize_drop_cnt($game_id, $prop_id, $num)
	{
		$redis = $this->getRedisMaster();		
		$ret = $redis->hIncrBy($this->get_redis_key_saw_prop_drop_number($game_id), $prop_id . '', $num);
		return $ret;
	}
	
	public function upd_loot_box_user_times($game_id, $uid, $times)
	{
		$redis = $this->getRedisMaster();
		$key = $this->get_redis_key_saw_game_loot_box_user_times($game_id, $uid);
		$res = $redis->incrBy($key, $times);
		$redis->expire($key, 600);
		return $res;
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////	
	public function water_attack($game_id)
	{
		$tm = 0;
		
		$tm = $this->upd_saw_game_temperature($game_id, 0 - game_saw_model::GAME_SAW_PARAM_COOLING_WATER);
		if ($tm < 0) {
			$this->reset_saw_game_temperature($game_id);
			$tm = 0;
		}
		
		return $tm;
	}
	
	public function drill_attack($game_id)
	{
		// 判断是否冰块有效
		if ($this->prop_special_ice_block_in_use($game_id)) {
			$this->upd_saw_game_hp($game_id, 0 - game_saw_model::GAME_SAW_PARAM_ATK_DRILL);
		} else {
			$tm = game_saw_model::GAME_SAW_PARAM_ATK_DRILL * game_saw_model::GAME_SAW_PARAM_ATK_DRILL_INCR_TM;
			
			$tm_f = $this->upd_saw_game_temperature($game_id, $tm);
			
			if ($tm_f >= $tm) {
				$hp_f = $this->upd_saw_game_hp($game_id, 0 - game_saw_model::GAME_SAW_PARAM_ATK_DRILL);
			}
		}
	}
	
	public function saw_attack($game_id)
	{
		$tm = game_saw_model::GAME_SAW_PARAM_SAW_ATK_INCR_TM * game_saw_model::GAME_SAW_PARAM_SAW_ATK_SAW;
		
		$tm_f = $this->upd_saw_game_temperature($game_id, $tm);
		
		// 这里的判断是否有必要
		// 防止温度减为负，还未即使清0时。攻击温度实质未生效，提高游戏难度
		if ($tm_f >= $tm) {
			$hp_f = $this->upd_saw_game_hp($game_id, 0 - game_saw_model::GAME_SAW_PARAM_SAW_ATK_SAW);
		}		
	}
	
	public function ice_block_attack($game_id)
	{
		$this->use_prop_special($game_id, game_saw_model::GAME_SAW_PROP_ID_ICE_BLOCK);
	}
	
	// 判断游戏是否通过
	public function if_game_pass($game_id)
	{
		$hp_now = $this->upd_saw_game_hp($game_id, 0);
		
		if ($hp_now <= 0) {
			return true;
		}
		
		return false;
	}
	
	// 判断游戏是否爆炸
	public function if_game_bomb($game_id)
	{
		$tm_now = $this->upd_saw_game_temperature($game_id, 0);
		$tm_limit = $this->get_temperature_total($game_id);
		
		if ($tm_now >= $tm_limit) {
			return true;
		} 
		
		return false;
	}
	
	// 判断是否该推送hp,tm变更
	public function if_notify_hp_tm($game_id)
	{
		$redis = $this->getRedisMaster();
		
		$key_lock = $this->get_redis_key_saw_game_hp_tm_notify_lock($game_id);
		
		$r = rand();
		
		// 不主动删除
		//$ok = $redis->set($key_lock, $r, array('nx', 'ex' => game_saw_model::EXPIRE_TIME_SAW_HP_TM_NOTIFY_LOCK));
		$ok = $this->redis_set_nx_ex($redis, $key_lock, $r, game_saw_model::EXPIRE_TIME_SAW_HP_TM_NOTIFY_LOCK);
		
		return $ok;
	}

	// 判断推送tips时机是否达成
	public function if_notify_tips($game_id)
	{
		$redis = $this->getRedisMaster();

		$key_lock = $this->get_redis_key_saw_tips_notify_lock($game_id);

		$r = rand();

		$ok = $this->redis_set_nx_ex($redis, $key_lock, $r, game_saw_model::GAME_SAW_TIPS_INTERVAL);

		return $ok;
	}

	public function get_tips_notify_times($game_id)
	{
		$redis = $this->getRedisMaster();
		$key = $this->get_redis_key_saw_tips_notify_times($game_id);

		$times = $redis->incrBy($key, 1);

		$redis->expire($key, game_saw_model::EXPIRE_TIME_SAW_TIPS_NOTIFY_TIMES);

		return $times;
	}
	
	// 判断宝箱抢夺是否过期
	public function if_loot_box_time_out($game_id)
	{
		$redis = $this->getRedisMaster();
		$key = $this->get_redis_key_saw_game_loot_box_time_out_lock($game_id);
		return $redis->exists($key);
	}
	
	public function insert_loot_record_to_db($game_id, $drop_infs, $uid, $box_id = -1)
	{
		$ret = false;
		
		if (empty($drop_infs)) {
			return $ret;
		}
		
		$prop_type = $drop_infs['prop_type'];
		$prop_id = $drop_infs['prop_id'];
		$prop_num = $drop_infs['prop_num'];
		$prop_icon = $drop_infs['prop_icon'];
		$prop_quality = $drop_infs['quality'];
		
		$sql = "INSERT INTO saw.saw_game_prize (game_id, type, prize_id, size, uid, icon, createtime, box_id, quality) VALUES ($game_id, $prop_type,
				$prop_id,$prop_num,$uid,'$prop_icon',NOW(),$box_id,$prop_quality)";
		
		$db_saw = $this->getDbMain();
		
		$rows = $db_saw->query($sql);
		
		if (empty($rows)) {
			LogApi::logProcess("game_saw_model:insert_loot_record_to_db failure. sql:$sql game_id:$game_id uid:$uid drop_infs:" . json_encode($drop_infs));
		} else {
			$ret = true;
		}
		
		return $ret;
	}
	
	public function loot_prop($uid, $game_id, $drop_id)
	{
		// 需加锁
		// 抢到后如果不是特殊道具则需入库
		$redis = $this->getRedisMaster();
		
		$key_lock = $this->get_redis_key_saw_game_loot_prop_lock($game_id, $drop_id);
		
		$rd = rand();
		
		//$ok = $redis->set($key_lock, 123, array('nx', 'px' => game_saw_model::EXPIRE_TIME_SAW_GAME_LOOT_PROP_LOCK));
		$ok = $this->redis_set_nx_ex($redis, $key_lock, $rd, game_saw_model::EXPIRE_TIME_SAW_GAME_LOOT_PROP_LOCK);
		
		if ($ok) {
			$key_drop = $this->get_redis_key_saw_prop_drop($game_id);
			$res = $redis->hGet($key_drop, $drop_id . '');
			
			$prop_overflow = false;
			if (!empty($res)) {
				$res = json_decode($res, true);
				$prop_overflow = $this->if_prop_limit_overflow($uid, $res['prop_type']);

				if (!$prop_overflow) {
					$redis->hDel($key_drop, $drop_id . '');
				}
			}
			
			if ($redis->get($key_lock) == $rd) {
				$redis->del($key_lock);
			}

			if ($prop_overflow) {
				return null;
			}
			
			if (!empty($res)) {
				if ($res['prop_classify'] == game_saw_model::GAME_SAW_PROP_CLASSIFY_PROP_NORMAL) {
					$this->insert_loot_record_to_db($game_id, $res, $uid);
				} else if ($res['prop_classify'] == game_saw_model::GAME_SAW_PROP_CLASSIFY_PROP_SPECIAL) {
					$this->droped_prop_special($game_id, $res['prop_id']);			
				}
			}
			
			return $res;
		} else {
			return null;
		}
	}
	
	public function loot_box($uid, $game_id, $drop_id)
	{
		$return = array(
				'code' => 0
		);
		
		$redis = $this->getRedisMaster();
		$conf = $this->get_saw_game_config();
		$max_box = $conf['get_boxes'];
		$attack_times = $conf['attacks'];
		
		if ($this->if_loot_box_time_out($game_id)) {
			$return['code'] = game_saw_model::GAME_SAW_ERR_CODE_LOOT_MISS;
			return $return;
		}
		
		// 先判断攻击次数是否达到要求
		$user_attacks = $this->upd_saw_game_user_attack_times($game_id, $uid, 0);
		if ($user_attacks < $attack_times) {
			$return['code'] = game_saw_model::GAME_SAW_ERR_CODE_LOOT_MISS;
			return $return;
		}
		
		$key_lock_user = $this->get_redis_key_saw_game_loot_box_user_lock($game_id, $uid);
		$key_lock_box = $this->get_redis_key_saw_game_loot_box_lock($game_id, $drop_id);
		$key_drop = $this->get_redis_key_saw_box_drop($game_id);
		
		$user_cur_boxes = $this->upd_loot_box_user_times($game_id, $uid, 0);
		
		$r_ul = rand();
		$r_dl = rand();
		
		do {
			if ($user_cur_boxes >= $max_box) {
				// 领取数量已达上限
				$return['code'] = game_saw_model::GAME_SAW_ERR_CODE_LOOT_TOO_MUCH;
				break;
			}
			
			// 获取用户锁
			//$ok = $redis->set($key_lock_user, $r_ul, array('nx', 'px' => game_saw_model::EXPIRE_TIME_SAW_GAME_LOOT_BOX_USER_LOCK));
			$ok = $this->redis_set_nx_ex($redis, $key_lock_user, $r_ul, game_saw_model::EXPIRE_TIME_SAW_GAME_LOOT_BOX_USER_LOCK);
			if (!$ok) {
				// 未抢到
				$return['code'] = game_saw_model::GAME_SAW_ERR_CODE_LOOT_MISS;
				break;
			}
			
			$user_cur_boxes = $this->upd_loot_box_user_times($game_id, $uid, 0);
			if ($user_cur_boxes >= $max_box) {
				// 领取数量已达上限
				$return['code'] = game_saw_model::GAME_SAW_ERR_CODE_LOOT_TOO_MUCH;
				break;
			}
			
			// 获取宝箱掉落锁
			//$ok = $redis->set($key_lock_box, $r_dl, array('nx', 'px' => game_saw_model::EXPIRE_TIME_SAW_GAME_LOOT_BOX_LOCK));
			$ok = $this->redis_set_nx_ex($redis, $key_lock_box, $r_dl, game_saw_model::EXPIRE_TIME_SAW_GAME_LOOT_BOX_LOCK);
			if (!$ok) {
				// 未抢到
				$return['code'] = game_saw_model::GAME_SAW_ERR_CODE_LOOT_MISS;
				break;
			}
			
			// 领取宝箱
			$res = $redis->hGet($key_drop, $drop_id . '');
			
			if (!empty($res)) {
				
				// 从待抢夺池中移除该宝箱
				$redis->hDel($key_drop, $drop_id . '');
				// 增加用户抢夺宝箱数量
				$this->upd_loot_box_user_times($game_id, $uid, 1);
			}
			
			// 先释放宝箱掉落锁
			if ($redis->get($key_lock_box) == $r_dl) {
				$redis->del($key_lock_box);
			}
			// 再释放用户锁
			if ($redis->get($key_lock_user) == $r_ul) {
				$redis->del($key_lock_user);
			}
			
			if (empty($res)) {
				// 未抢到
				$return['code'] = game_saw_model::GAME_SAW_ERR_CODE_LOOT_MISS;
				break;
			}
			
			$res = json_decode($res, true);
			$return['box_inf'] = $res;
			
			$sql = "SELECT * FROM saw.saw_box_prize WHERE box_id=" . $res['box_id'];
			$db_saw = $this->getDbMain();
			$rows = $db_saw->query($sql);
			
			if (!empty($rows)) {
				$row = $rows->fetch_assoc();
					
				while ($row) {
					do {
						$prop_id = $row['prize_id'];
						$prop_inf = $this->get_goods_inf_by_id($prop_id);

						$prop_overflow = $this->if_prop_limit_overflow($uid, $prop_inf['goods_type']);
						if ($prop_overflow) {
							break;
						}
							
						$inf_f['prop_name'] = $prop_inf['goods_name'];
						$inf_f['prop_icon'] = $prop_inf['goods_icon'];
						$inf_f['drop_id'] = $drop_id;
						$inf_f['prop_id'] = $prop_id;
						$inf_f['prop_type'] = $prop_inf['goods_type'];
						$inf_f['prop_classify'] = game_saw_model::GAME_SAW_PROP_CLASSIFY_PROP_NORMAL;
						$inf_f['prop_num'] = $row['size'];
						$inf_f['quality'] = $prop_inf['quality'];
						$return['props'][] = $inf_f;
				
						$this->insert_loot_record_to_db($game_id, $inf_f, $uid, $res['box_id']);
					} while (0);
					$row = $rows->fetch_assoc();
				}
			} else {
				LogApi::logProcess("game_saw_model:loot_box failure db. sql:" . $sql);
			}
		} while (0);
		
		return $return;
	}
	
	// 尝试进行游戏结算
	public function try_saw_settle($game_id, $sid, $singer_id)
	{
		$settle = array();
		
		$status = game_saw_model::GAME_SAW_STATUS_ING;
		
		if ($this->if_game_bomb($game_id)) {
			$status = game_saw_model::GAME_SAW_STATUS_FAILURE_BOMB;
		} else if ($this->if_game_pass($game_id)) {
			$status = game_saw_model::GAME_SAW_STATUS_SUCCESS;
		}
			
		if ($status != game_saw_model::GAME_SAW_STATUS_ING) {
		
			if ($this->upd_saw_game_status($game_id, $status)) {
				$settle['cmd'] = 'saw_game_settlement_nt';
				$settle['game_id'] = $game_id;
				$settle['sid'] = $sid;
				$settle['singer_id'] = $singer_id;
				$settle['game_status'] = $status;
					
				if ($status == game_saw_model::GAME_SAW_STATUS_SUCCESS) {
					$sun_box_drop_ids = $this->drop_saw_game_sun_box($game_id, 1);
					$lit_box_drop_ids = $this->drop_saw_game_lit_box($game_id, 39);
		
					$settle['sun_box_drop'] = $sun_box_drop_ids;
					$settle['lit_box_drop'] = $lit_box_drop_ids;
				}
			}

			// 增加热度积分
			{
				$model_anchor_pt = new anchor_points_model();
				$model_anchor_pt->on_anchor_finish_game_saw($singer_id);
			}		
		}
		
		return $settle;
	}
	
	public function clear_cache($game_id)
	{
		// 清除缓存操作
		$redis = $this->getRedisMaster();
		// 宝箱掉落记录			[1 m]
		$key_box_drop = $this->get_redis_key_saw_box_drop($game_id);
		$redis->expire($key_box_drop, 60);
		
		// drop id 				[1 h]
		$key_drop_id = $this->get_saw_game_drop_id($game_id);
		$redis->expire($key_drop_id, 60*60);
		
		// hp now 				[1 h]
		$key_hp_now = $this->get_redis_key_saw_hp_now($game_id);
		$redis->expire($key_hp_now, 60*60);
		
		// 游戏过程中奖励掉落记录	[1 h]
		$key_prop_drop = $this->get_redis_key_saw_prop_drop($game_id);
		$redis->expire($key_prop_drop, 60*60);
		
		// 普通物品掉落数量		[1 h]
		$key_prop_normal_drop_number = $this->get_redis_key_saw_prop_drop_number($game_id);
		$redis->expire($key_prop_normal_drop_number, 60*60);
		
		// 游戏道具掉落数量		[1 h]
		$key_prop_special_drop_number = $this->get_redis_key_saw_prop_special_drop_number($game_id);
		$redis->expire($key_prop_special_drop_number, 60*60);
		
		// 温度上限				[1 h]
		$key_tm_up_limit = $this->get_redis_key_saw_temperature_limit($game_id);
		$redis->expire($key_tm_up_limit, 60*60);
		
		// 温度当前值			[1 h]
		$key_tm_now = $this->get_redis_key_saw_temperature_now($game_id);
		$redis->expire($key_tm_now, 60*60);
	}
	
	public function upd_prop_special_drop_cd($game_id, $prop_id, $ex)
	{
		$redis = $this->getRedisMaster();
		$ret = $redis->setex($this->get_redis_key_saw_prop_special_in_use($game_id), $ex, $prop_id);
		return $ret;
	}

	public function b_saw_card($game_id)
	{
		$key = $this->get_redis_key_saw_is_card($game_id);
		$redis = $this->getRedisMaster();
		
		$ret = $redis->get($key);

		if (empty($ret) || $ret == "false") {
			return false;
		}

		return true;
	}

	public function if_prop_limit_overflow($uid, $prop_type)
	{
		$key = $this->get_redis_key_user_prop_limit($uid);

		$redis = $this->getRedisMaster();

		$ret = $redis->sIsMember($key, $prop_type);

		if (!empty($ret)) {
			return true;
		}

		return false;
	}
}