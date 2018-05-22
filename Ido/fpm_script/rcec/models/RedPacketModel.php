<?php
class RedPacketModel extends ModelBase
{
	// 红包类型
	const TYPE_LUCKY_RP 	= 1;	//手气红包
	const TYPE_FANS_RP 		= 2;	//粉丝红包
	const TYPE_SHARE_RP 	= 3;	//分享红包
	const TYPE_GTICKET_RP 	= 4;	//帮会票红包
	
	// 红包状态
	const STATUS_INITIAL 		= 0;	//初始状态
	const STATUS_ACTIVE 		= 3;	//激活状态
	const STATUS_ACTIVE_FAIL 	= 4;	//激活失败
	const STATUS_END			= 6;	//抢完
	const STATUS_EXPIRE 		= 9;	//过期
	
	// function for 配置读取
// 	public static $CONF_LUCKY_RP_MONEY_LOWER_LIMIT	= 100;
// 	public static $CONF_LUCKY_RP_MONEY_UPPER_LIMIT	= 5000;
// 	public static $CONF_LUCKY_RP_COUNT_LOWER_LIMIT	= 1;
// 	public static $CONF_LUCKY_RP_COUNT_UPPER_LIMIT	= 100;
// 	public static $CONF_LUCKY_RP_EXPIRE_TIME = 10;			//过期时间(分)
	
// 	public static $CONF_FANS_RP_MONEY_LOWER_LIMIT = 1000;
// 	public static $CONF_FANS_RP_MONEY_UPPER_LIMIT = 10000;
// 	public static $CONF_FANS_RP_ACTIVE_TIME	= 5;			//激活时间(分)
// 	public static $CONF_FANS_RP_EXPIRE_TIME = 10;			//过期时间(分)
// 	public static $CONF_FANS_RP_COUNT_BASE = 10;			//基础红包个数
	
// 	public static $CONF_SHARE_RP_MONEY_LOWER_LIMIT = 100;
// 	public static $CONF_SHARE_RP_MONEY_UPPER_LIMIT = 5000;
// 	public static $CONF_SHARE_RP_COUNT_LOWER_LIMIT = 10;
// 	public static $CONF_SHARE_RP_COUNT_UPPER_LIMIT = 100;
// 	public static $CONF_SHARE_RP_ACTIVE_COUNT_BASE = 10;	//激活人数
// 	public static $CONF_SHARE_RP_ACTIVE_TIMEOUT = 30;		//激活过期时间(分)
// 	public static $CONF_SHARE_RP_EXPIRE_TIME = 10;			//激活后过期时间(分)
	
	
	public static $CONF_LUCKY_RP_MONEY_LOWER_LIMIT	= -1;
	public static $CONF_LUCKY_RP_MONEY_UPPER_LIMIT	= -1;
	public static $CONF_LUCKY_RP_COUNT_LOWER_LIMIT	= -1;
	public static $CONF_LUCKY_RP_COUNT_UPPER_LIMIT	= -1;
	public static $CONF_LUCKY_RP_EXPIRE_TIME = -1;			//过期时间(分)
	public static $CONF_LUCKY_RP_TAX = 0;					//抽成百分点
	public static $CONF_LUCKY_RP_DUTY_FREE = 0;				//免税额度
	
	public static $CONF_FANS_RP_MONEY_LOWER_LIMIT = -1;
	public static $CONF_FANS_RP_MONEY_UPPER_LIMIT = -1;
	public static $CONF_FANS_RP_ACTIVE_TIME	= -1;			//激活时间(分)
	public static $CONF_FANS_RP_EXPIRE_TIME = -1;			//过期时间(分)
	public static $CONF_FANS_RP_COUNT_BASE = -1;			//基础红包个数
	public static $CONF_FANS_RP_TAX = 0;					//抽成百分点
	public static $CONF_FANS_RP_DUTY_FREE = 0;				//免税额度
	
	public static $CONF_SHARE_RP_MONEY_LOWER_LIMIT = -1;
	public static $CONF_SHARE_RP_MONEY_UPPER_LIMIT = -1;
	public static $CONF_SHARE_RP_COUNT_LOWER_LIMIT = -1;
	public static $CONF_SHARE_RP_COUNT_UPPER_LIMIT = -1;
	public static $CONF_SHARE_RP_ACTIVE_COUNT_BASE = -1;	//激活人数
	public static $CONF_SHARE_RP_ACTIVE_TIMEOUT = -1;		//激活过期时间(分)
	public static $CONF_SHARE_RP_EXPIRE_TIME = -1;			//激活后过期时间(分)
	public static $CONF_SHARE_RP_TAX = 0;					//抽成百分点
	public static $CONF_SHARE_RP_DUTY_FREE = 0;				//免税额度

	public static $CONF_GTICKET_RP_MONEY_LOWER_LIMIT = -1;
	public static $CONF_GTICKET_RP_MONEY_UPPER_LIMIT = -1;
	public static $CONF_GTICKET_RP_COUNT_LOWER_LIMIT = -1;	//最少帮会票数量
	public static $CONF_GTICKET_RP_COUNT_UPPER_LIMIT = -1;	//最大帮会票数量
	public static $CONF_GTICKET_RP_EXPIRE_TIME = -1;
	public static $CONF_GTICKET_RP_TAX = 0;					//抽成百分点
	public static $CONF_GTICKET_RP_DUTY_FREE = 0;			//免税额度
	
	public static $ERR_CODE_SUCCESS	= 0;			//成功
	public static $ERR_CODE_UNKNOWN = -1;			//未知错误
	public static $ERR_CODE_LACK_OF_MONEY = 101;	//金币不足
	public static $ERR_CODE_BAD_REQ_PARAM = 201;	//请求参数错误
	public static $ERR_CODE_PICK_END = 301;		//已领完
	public static $ERR_CODE_PICK_EXPIRE	= 302;	//红包过期
	public static $ERR_CODE_ACTIVE_FALSE = 303;	//未激活
	public static $ERR_CODE_NOT_FANS = 401;		//非粉丝不能领
	public static $ERR_CODE_FANS_LATE = 402;	//粉晚了不能领
	public static $ERR_CODE_GTICKET_NO_CONTRIBUTE = 403;//未赠送帮会票不能领取

	public function redis_set_nx_ex($key, $value, $ex)
	{
		$redis = $this->getRedisMaster();

		$ret = false;
		
		$ret = $redis->setnx($key, $value);
		if ($ret) {
			$redis->expire($key, $ex);
		}
		
		return $ret;		
	}
	
	// 分享红包激活信息存为单独的key
	// 红包的过期信息单独存
	// 粉丝红包的激活信息单独存
	
	//////////////////////////////////////////////////////////////////
	// type		:hash
	// type		:h_rp_expire_cdt:$rp_id mode 1024
	// field	:$rp_id
	// value	:expire time
	//
	// desc		:红包过期条件判断
	//////////////////////////////////////////////////////////////////
	const KEY_RP_EXPIRE_CDT	= "h_rp_expire_cdt:";
	private function getRedPacketExpireCdtKey($rp_id)
	{
		return self::KEY_RP_EXPIRE_CDT . ($rp_id % 1024);
	}
	public function setRedPacketExpireCdtInCache($rp_id, $expire_time)
	{
		$key = $this->getRedPacketExpireCdtKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hSet($key, $field, intval($expire_time));
	}
	public function getRedPacketExpireCdtInCache($rp_id)
	{
		$key = $this->getRedPacketExpireCdtKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hGet($key, $field);
	}
	public function delRedPacketExpireCdtInCache($rp_id)
	{
		$key = $this->getRedPacketExpireCdtKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hDel($key, $field);
	}
	//end
	
	//////////////////////////////////////////////////////////////////
	// type		:hash
	// type		:h_rp_share_active_expire_cdt:$rp_id mode 1024
	// field	:$rp_id
	// value	:active expire time
	//
	// desc		:分享红包激活过期时间
	//////////////////////////////////////////////////////////////////
	const KEY_RP_SHARE_ACTIVE_EXPIRE_CDT = "h_rp_share_active_expire_cdt:";
	private function getShareRedPacketActiveExpireCdtKey($rp_id)
	{
		return self::KEY_RP_SHARE_ACTIVE_EXPIRE_CDT . ($rp_id % 1024);
	}
	public function setShareRedPacketActiveExpireCdtInCache($rp_id, $active_expire_time)
	{
		$key = $this->getShareRedPacketActiveExpireCdtKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hSet($key, $field, intval($active_expire_time));
	}
	public function getShareRedPacketActiveExpireCdtInCache($rp_id)
	{
		$key = $this->getShareRedPacketActiveExpireCdtKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hGet($key, $field);
	}
	public function delShareRedPacketActiveExpireCdtInCache($rp_id)
	{
		$key = $this->getShareRedPacketActiveExpireCdtKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hDel($key, $field);
	}
	//end
	
	
	//////////////////////////////////////////////////////////////////
	// type		:hash
	// key		:h_rp_share_active_cdt:$rp_id mode 1024
	// field	:$rp_id
	// value	:count of shared
	//
	// desc		:分享红包激活判断
	//////////////////////////////////////////////////////////////////
	const KEY_RP_SHARE_ACTIVE_CDT = "h_rp_share_active_cdt:";
	private function getShareRedPacketActiveCdtKey($rp_id)
	{
		return self::KEY_RP_SHARE_ACTIVE_CDT . ($rp_id % 1024);
	}
	public function updateShareRedPacketActiveCdtInCache($rp_id, $count)
	{
		$key = $this->getShareRedPacketActiveCdtKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hIncrBy($key, $field, $count);
	}
	public function delShareRedPacketActiveCdtInCache($rp_id)
	{
		$key = $this->getShareRedPacketActiveCdtKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hDel($key, $field);
	}
	//end
	
	//////////////////////////////////////////////////////////////////
	// type		:set
	// key		:set_rp_share_uids:$rp_id
	// value	:uid
	//
	// desc		:分享人员
	//////////////////////////////////////////////////////////////////
	const KEY_RP_SHARE_UIDS	= "set_rp_share_uids:";
	private function getShareRedPacketShareUidsKey($rp_id)
	{
		return self::KEY_RP_SHARE_UIDS . $rp_id;
	}
	public function addShareRedPacketShareUid($rp_id, $uid)
	{
		$key = $this->getShareRedPacketShareUidsKey($rp_id);
		return $this->getRedisMaster()->sAdd($key, $uid);
	}
	public function countShareRedPacketShareUids($rp_id)
	{
		$key = $this->getShareRedPacketShareUidsKey($rp_id);
		return $this->getRedisMaster()->scard($key);
	}
	public function delSharePacketShareUid($rp_id)
	{
		$key = $this->getShareRedPacketShareUidsKey($rp_id);
		return $this->getRedisMaster()->del($key);
	}
	//end
	
	//////////////////////////////////////////////////////////////////
	// type		:hash
	// key		:h_rp_fans_active_cdt:$sid
	// field	:$id
	// value	:active time
	//
	// desc		:粉丝红包激活条件
	//////////////////////////////////////////////////////////////////
	const KEY_RP_FANS_ACTIVE_CDT = "h_rp_fans_active_cdt:";
	private function getFansRedPacketActiveCdtKey($rp_id)
	{
		return self::KEY_RP_FANS_ACTIVE_CDT . ($rp_id % 1024);
	}
	public function setFansRedPacketActiveCdtInCache($rp_id, $active_time)
	{
		$key = $this->getFansRedPacketActiveCdtKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hSet($key, $field, intval($active_time));
	}
	public function getFansRedPacketActiveCdtInCache($rp_id)
	{
		$key = $this->getFansRedPacketActiveCdtKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hGet($key, $field);
	}
	public function delFansRedPacketActiveCdtInCache($rp_id)
	{
		$key = $this->getFansRedPacketActiveCdtKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hDel($key, $field);
	}
	
	//////////////////////////////////////////////////////////////////
	// type		:hash
	// key		:h_rp_fans_enroll_count:$rp_id mode 1024
	// field	:$rp_id
	// value	:count of enroll fans
	//
	// desc		:粉丝报名记录
	//////////////////////////////////////////////////////////////////
	const KEY_RP_FANS_ENROLL_COUNT = "h_rp_fans_enroll_count:";
	private function getFansEnrollCountKey($rp_id)
	{
		return self::KEY_RP_FANS_ENROLL_COUNT . ($rp_id % 1024);
	}
	public function updateFansEnrollCount($rp_id, $size)
	{
		$key = $this->getFansEnrollCountKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hIncrBy($key, $field, $size);
	}
	public function delFansEnrollCount($rp_id)
	{
		$key = $this->getFansEnrollCountKey($rp_id);
		$field = $rp_id . "";
		return $this->getRedisMaster()->hDel($key, $field);
	}
	//end

	//////////////////////////////////////////////////////////////////
	// type		:hash
	// key		:h_rp_record:$sid
	// field	:$id
	// value	:json_encode(rp_inf)
	const KEY_RP_RECORD = "h_rp_record:";
	public function updateRedPacketInCache($data)
	{
		$sid = $data['sid'];
		$rp_id = $data['id'];
		$key = self::KEY_RP_RECORD . $sid;
		$field = $rp_id . "";
		$this->getRedisMaster()->hSet($key, $field, json_encode($data));
	}
	public function getRedPacketListFromCache($sid)
	{
		$key = self::KEY_RP_RECORD . $sid;
		return $this->getRedisMaster()->hVals($key);
	}
	public function getRedPacketFromCache($sid, $rp_id)
	{
		$key = self::KEY_RP_RECORD . $sid;
		$field = $rp_id . "";
		return $this->getRedisMaster()->hGet($key, $field);
	}
	public function delRedPacketFromCache($sid, $rp_id)
	{
		$key = self::KEY_RP_RECORD . $sid;
		$field = $rp_id . "";
		return $this->getRedisMaster()->hDel($key, $field);
	}
	
	// type		:set
	// key		:set_rp_picked:$rp_id
	// value	:$uid
	const KEY_RP_PICK_RECORD = "set_rp_picked_record:";
	public function pickRecordCache($rp_id, $uid)
	{
		$key = self::KEY_RP_PICK_RECORD . $rp_id;
		return $this->getRedisMaster()->sAdd($key, $uid);
	}
	public function bPicked($rp_id, $uid)
	{
		$key = self::KEY_RP_PICK_RECORD . $rp_id;
		return $this->getRedisMaster()->sismember($key, $uid);
	}
	public function delPickRecordCache($rp_id)
	{
		$key = self::KEY_RP_PICK_RECORD . $rp_id;
		return $this->getRedisMaster()->del($key);
	}

	//////////////////////////////////////////////////////////////////
	// type        :hash
	// key         :h_gticket_rp_user_target:$rp_id
	// field       :$uid
	// value       :$target_number
	// 用户帮会票贡献数量
	const KEY_RP_GTICKET_USER_TARGET = "h_gticket_rp_user_target:";
	public function contributeGTicket($rp_id, $uid, $num) 
	{
		$key = self::KEY_RP_GTICKET_USER_TARGET . $rp_id;
		$field = strval($uid);
		return $this->getRedisMaster()->hIncrBy($key, $field, $num);
	}
	public function consumeGTicket($rp_id, $uid)
	{
		$key = self::KEY_RP_GTICKET_USER_TARGET . $rp_id;
		$field = strval($uid);
		return $this->getRedisMaster()->hDel($key, $field);
	}
	public function getGTicketByUser($rp_id, $uid)
	{
		$key = self::KEY_RP_GTICKET_USER_TARGET . $rp_id;
		$field = strval($uid);
		return $this->getRedisMaster()->hGet($key, $field);
	}
	public function clearGTicketUserContributed($rp_id)
	{
		$key = self::KEY_RP_GTICKET_USER_TARGET . $rp_id;
		return $this->getRedisMaster()->del($key);
	}

	//////////////////////////////////////////////////////////////////
	// type        :str
	// key         :str_gticket_rp_current:$rp_id
	// value 	   :$number
	// 帮会票贡献总数
	const KEY_RP_GTICKET_CURRENT = "str_gticket_rp_current:";
	public function incrToalGTickets($rp_id, $number)
	{
		$key = self::KEY_RP_GTICKET_CURRENT . $rp_id;
		return $this->getRedisMaster()->incrBy($key, $number);
	}
	public function clearToalGTickets($rp_id)
	{
		$key = self::KEY_RP_GTICKET_CURRENT . $rp_id;
		return $this->getRedisMaster()->del($key);
	}
	//////////////////////////////////////////////////////////////////
	// type 		:string
	// key 			:str_gticket_target_number:$rp_id
	// value 		:$target_number
	// expire time 	:$conf_expire_time+100
	const KEY_RP_GTICKET_TARGET_NUMBER = "str_gticket_target_number:";
	public function setGTicketTargetNumber($rp_id, $number, $expire_time)
	{
		$key = self::KEY_RP_GTICKET_TARGET_NUMBER . $rp_id;
		return $this->getRedisMaster()->setex($key, $expire_time, $number);
	}

	public function getGTicketTargetNumber($rp_id)
	{
		$key = self::KEY_RP_GTICKET_TARGET_NUMBER . $rp_id;
		$rt = $this->getRedisMaster()->get($key);

		if (empty($rt)) {
			$rt = 10; // default value.
		}

		return $rt;
	}

	public function clearGTicketTargetNumber($rp_id)
	{
		$key = self::KEY_RP_GTICKET_TARGET_NUMBER . $rp_id;
		return $this->getRedisMaster()->del($key);
	}

	// 用户领取红包锁
	public function pickLock($uid, $rp_id)
	{
		$rd = rand();
		$key = "lock_rp_pick:$uid:$rp_id";
		$ok = $this->redis_set_nx_ex($key, $rd, 1);
		return array('ok' => $ok, 'rd' => $rd);
	}
	
	public function pickUnLock($uid, $rp_id, $value)
	{
		$key = "lock_rp_pick:$uid:$rp_id";
		$redis = $this->getRedisMaster();

		if ($redis->get($key) == $value) {
			$redis->del($key);
		}
	}
	
	// 红包分配算法
	public function getRandMoney($money_left, $count_left)
	{
		if ($count_left == 1) {
			return $money_left;
		}
		
		$min = 1;
		$max = 1;
		if ($money_left > $count_left) {
			$max = floor($money_left/$count_left*2);
		}
		$max = $max < $min ? $min : $max;
		
		$rad = rand(1,99);
		$rad = $rad / 100;
		
		$money = floor($rad * $max);
		$money = $money < $min ? $min : $money;
		
		return $money;
	}

	// 帮会票红包分配方法
	public function getGTicketMoney($money_total, $money_left, $ticket_target, $user_target)
	{
		// 每个帮会票的价值
		$per_ticket_value = intval(floor($money_total / $ticket_target));
		$user_should_picked = $user_target * $per_ticket_value;
		$user_should_picked = $user_should_picked > $money_left ? $money_left : $user_should_picked;

		return $user_should_picked;
	}
	
	//
	public function getRedPacketRecordFromDB($rp_id)
	{
		$db_main = $this->getDbMain();
		$sql = "SELECT * FROM t_red_packet_record WHERE id=$rp_id";
		$rows = $db_main->query($sql);
		if (!empty($rows) && $rows->num_rows > 0) {
			return $rows->fetch_assoc();
		} else {
			return null;
		}
	}
	
	// new packet只需要一个就够了
	public function newRedPacket($sid,$uid,$type,$status,$money_total,$count_total,$summary)
	{
		// 判断金币是否足够
		// 扣钱
		// 记录红包信息
		// 记录发送红包流水信息
		// 返回
		$return = array(
			'code'	=> RedPacketModel::$ERR_CODE_UNKNOWN,
		);
		$now = time();
		$db_main = $this->getDbMain();
		$b_success = false;
		try {
			$db_main->query("BEGIN");
			do {
				$money_before = 0 ;
				$sql = "SELECT coin_balance FROM user_attribute WHERE uid = $uid FOR UPDATE";
				$rows = $db_main->query($sql);
				if (empty($rows) || $rows->num_rows <= 0) {
					LogApi::logProcess("RedPacketModel:newRedPacket failure. sql:$sql");
					break;
				}
		
				$row = $rows->fetch_assoc();
				$money_before = $row['coin_balance'];
				if ($money_before < $money_total) {
					$return['code'] = RedPacketModel::$ERR_CODE_LACK_OF_MONEY;
					LogApi::logProcess("RedPacketModel:newRedPacket failure:lack of money. uid:$uid sid:$sid type:$type money_total:$money_total count_total:$count_total");
					break;
				}
				$money_after = $money_before - $money_total;
				
				$sql = "UPDATE user_attribute SET coin_balance = coin_balance - $money_total WHERE uid = $uid";
				$rows = $db_main->query($sql);
				if (empty($rows) || $db_main->affected_rows == 0) {
					LogApi::logProcess("RedPacketModel:newRedPacket failure. sql:$sql");
					break;
				}
				
				// 记录红包
				$sql = "INSERT INTO t_red_packet_record";
				$sql .= " (sid,uid,type,status,money_total,count_total,money_picked,count_picked,summary,create_time,last_uptime)";
				$sql .= " VALUES($sid,$uid,$type,$status,$money_total,$count_total,0,0,'$summary',$now,$now)";
				$rows = $db_main->query($sql);
				if (empty($rows)) {
					LogApi::logProcess("RedPacketModel:newRedPacket failure. sql:$sql");
					break;
				}
				
				//记录红包发送流水
				$sql = "SELECT * FROM t_red_packet_record where id=LAST_INSERT_ID()";
				$rows = $db_main->query($sql);
				if (empty($rows) || $rows->num_rows <= 0) {
					LogApi::logProcess("RedPacketModel:newRedPacket failure. sql:$sql");
					break;
				}
				$inf_rp = $rows->fetch_assoc();
				
				$rp_id = $inf_rp['id'];
				$sql = "INSERT INTO t_red_packet_send_bill (rp_id,type,money_total,money_before,money_after,uid)";
				$sql .= " VALUES ($rp_id, $type, $money_total, $money_before, $money_after, $uid)";
				$rows = $db_main->query($sql);
				if (empty($rows)) {
					LogApi::logProcess("RedPacketModel:newRedPacket failure. sql:$sql");
					break;
				}
				
				$b_success = true;
				$return['data'] = $inf_rp;
			} while(0);
			
			if ($b_success) {
				$db_main->query("COMMIT");
				$return['code'] = RedPacketModel::$ERR_CODE_SUCCESS;
			} else {
				$db_main->query("ROLLBACK");
			}
		}catch (Exception $e) {
			$db_main->query("ROLLBACK");
			$return['code'] = RedPacketModel::$ERR_CODE_UNKNOWN;
			$expMsg = $e->getMessage();
			LogApi::logProcess("RedPacketModel:newRedPacket catch exception:$expMsg");
		}
		
		return $return;
	}
	
	// 抢红包
	public function pickRedPacket($uid, $sid, $rp_id, $tax, $duty_free)
	{
		$now = time();
		$return = array(
			'code' => RedPacketModel::$ERR_CODE_UNKNOWN
		);
		
		// 先判断有无领过
		// 如果已经领过了，返回成功
		$db_main = $this->getDbMain();
		$pay_tax = 0;
		
		// 如果未领取过，判断红包状态
		$b_success = false;
		try {
			$db_main->query("BEGIN");
			do {
				$sql = "SELECT * FROM t_red_packet_record WHERE id=$rp_id FOR UPDATE";
				$rows = $db_main->query($sql);
				if (empty($rows) || $rows->num_rows <= 0) {
					LogApi::logProcess("RedPacketModel:pickRedPacket failure. sql:$sql");
					break;
				}
					
				$inf_rp = $rows->fetch_assoc();
				$return['data']['inf_rp'] = $inf_rp;
				$return['code'] = $this->statusErrcode($inf_rp['status']);
					
				if ($inf_rp['status'] != RedPacketModel::STATUS_ACTIVE) {
					break;
				}
					
				$money_left = $inf_rp['money_total'] - $inf_rp['money_picked'];
				$count_left = $inf_rp['count_total'] - $inf_rp['count_picked'];
				$money = $this->getRandMoney($money_left, $count_left);

				// 抽成计算
				$pay_tax = $this->calcTax($money, $duty_free, $tax);
				$money_real_picked = $money - $pay_tax;
				$return['data']['money_picked'] = $money_real_picked;
					
				// 更新用户金币
				$money_before = 0 ;
				$sql = "SELECT coin_balance FROM user_attribute WHERE uid = $uid FOR UPDATE";
				$rows = $db_main->query($sql);
				if (empty($rows) || $rows->num_rows <= 0) {
					LogApi::logProcess("RedPacketModel:pickRedPacket failure. sql:$sql");
					break;
				}
				$row = $rows->fetch_assoc();
				$money_before = $row['coin_balance'];
					
				$sql = "UPDATE user_attribute SET coin_balance = coin_balance + $money_real_picked WHERE uid = $uid";
				$rows = $db_main->query($sql);
				if (empty($rows) || $db_main->affected_rows == 0) {
					LogApi::logProcess("RedPacketModel:pickRedPacket failure. sql:$sql");
					break;
				}
					
				// 更新红包信息
				$inf_rp['money_picked'] += $money;
				$inf_rp['count_picked'] += 1;
				if ($inf_rp['count_picked'] == $inf_rp['count_total']) {
					$inf_rp['status'] = RedPacketModel::STATUS_END;
				}
					
				$money_picked = $inf_rp['money_picked'];
				$count_picked = $inf_rp['count_picked'];
				$status = $inf_rp['status'];
				$type = $inf_rp['type'];
					
				$sql = "UPDATE t_red_packet_record";
				$sql .= " SET status=$status, money_picked=$money_picked, count_picked=$count_picked, last_uptime=$now";
				$sql .= " WHERE id=$rp_id";
				$rows = $db_main->query($sql);
				if (empty($rows) || $db_main->affected_rows == 0) {
					LogApi::logProcess("RedPacketModel:pickRedPacket failure. sql$sql");
					break;
				}
				$inf_rp['last_uptime'] = $now;
					
				// 插入领取流水
				$sql = "INSERT INTO t_red_packet_pick_bill (rp_id,type,money_picked,money_before,money_after,uid)";
				$sql .= " VALUES ($rp_id, $type, $money_real_picked, $money_before, $money_before+$money_real_picked, $uid)";
				$rows = $db_main->query($sql);
				if (empty($rows)) {
					LogApi::logProcess("RedPacketModel:pickRedPacket failure. sql:$sql");
					break;
				}
				
				// 插入领取记录
				$sql = "INSERT INTO t_red_packet_pick_record (rp_id,uid,money_picked)";
				$sql .= " VALUES ($rp_id, $uid, $money_real_picked)";
				$rows = $db_main->query($sql);
				if (empty($rows)) {
					LogApi::logProcess("RedPacketModel:pickRedPacket failure. sql:$sql");
					break;
				}
				
				// 获取领取列表
				$sql = "SELECT id,rp_id,uid,money_picked,UNIX_TIMESTAMP(create_time) as create_time FROM t_red_packet_pick_record WHERE id=LAST_INSERT_ID()";
				$rows = $db_main->query($sql);
				
				if (!empty($rows) && $rows->num_rows > 0) {
					$return['data']['pick_item'] = $rows->fetch_assoc();
				} else {
					LogApi::logProcess("RedPacketModel:pickRedPacket failure. sql:$sql");
				}
				
				$return['data']['inf_rp'] = $inf_rp;
				$b_success = true;

			} while (0);
			
			if ($b_success) {
				$return['code'] = RedPacketModel::$ERR_CODE_SUCCESS;
				$db_main->query("COMMIT");
			} else {
				$db_main->query("ROLLBACK");
			}
		} catch (Exception $e) {
			$db_main->query("ROLLBACK");
			$return['code'] = RedPacketModel::$ERR_CODE_UNKNOWN;
			$expMsg = $e->getMessage();
			LogApi::logProcess("RedPacketModel:pickRedPacket catch exception:$expMsg");
		}
		
		if ($b_success) {
			if ($pay_tax > 0) {
				$this->recordTax2Sys($uid, $pay_tax);
			}

			$model_tool_consume_record = new ToolConsumeRecordModel();
			$model_tool_consume_record->on_user_good_add($uid, ToolConsumeRecordModel::GOODS_ID_GOLD, $return['data']['money_picked'], ToolConsumeRecordModel::USER_GOOD_ADD_SRC_REDPACKET);
		}
		
		return $return;
	}

	// 抢帮会票红包
	public function pickGTicketRedPacket($uid, $sid, $rp_id, $user_target, $ticket_target, $tax, $duty_free)
	{
		$now = time();
		$return = array(
			'code' => RedPacketModel::$ERR_CODE_UNKNOWN
		);
		
		$db_main = $this->getDbMain();
		$pay_tax = 0;
		$b_success = false;
		try {
			$db_main->query("BEGIN");
			do {
				$sql = "SELECT * FROM t_red_packet_record WHERE id=$rp_id FOR UPDATE";
				$rows = $db_main->query($sql);
				if (empty($rows) || $rows->num_rows <= 0) {
					LogApi::logProcess("RedPacketModel:pickGTicketRedPacket failure. sql:$sql");
					break;
				}					
				$inf_rp = $rows->fetch_assoc();
				$return['data']['inf_rp'] = $inf_rp;
				$return['code'] = $this->statusErrcode($inf_rp['status']);
					
				if ($inf_rp['status'] != RedPacketModel::STATUS_ACTIVE) {
					break;
				}

				$money_left = $inf_rp['money_total'] - $inf_rp['money_picked'];
				$money = $this->getGTicketMoney($inf_rp['money_total'], $money_left, $ticket_target, $user_target);

				// 抽成计算
				$pay_tax = $this->calcTax($money, $duty_free, $tax);
				$money_real_picked = $money - $pay_tax;
				$return['data']['money_picked'] = $money_real_picked;
				LogApi::logProcess("RedPacketModel pickGTicketRedPacket uid:$uid rp_id:$rp_id money_left:$money_left money:$money pay_tax:$pay_tax real_picked:$money_real_picked");
					
				// 更新用户金币
				$money_before = 0 ;
				$sql = "SELECT coin_balance FROM user_attribute WHERE uid = $uid FOR UPDATE";
				$rows = $db_main->query($sql);
				if (empty($rows) || $rows->num_rows <= 0) {
					LogApi::logProcess("RedPacketModel:pickGTicketRedPacket failure. sql:$sql");
					break;
				}
				$row = $rows->fetch_assoc();
				$money_before = $row['coin_balance'];
					
				$sql = "UPDATE user_attribute SET coin_balance = coin_balance + $money_real_picked WHERE uid = $uid";
				$rows = $db_main->query($sql);
				if (empty($rows) || $db_main->affected_rows == 0) {
					LogApi::logProcess("RedPacketModel:pickGTicketRedPacket failure. sql:$sql");
					break;
				}
					
				// 更新红包信息
				$inf_rp['money_picked'] += $money;
				$inf_rp['count_picked'] += 1;

				if ($money_left == $money) {
					$inf_rp['status'] = RedPacketModel::STATUS_END;
				}
					
				$money_picked = $inf_rp['money_picked'];
				$count_picked = $inf_rp['count_picked'];
				$status = $inf_rp['status'];
				$type = $inf_rp['type'];
					
				$sql = "UPDATE t_red_packet_record";
				$sql .= " SET status=$status, money_picked=$money_picked, count_picked=$count_picked, last_uptime=$now";
				$sql .= " WHERE id=$rp_id";
				$rows = $db_main->query($sql);
				if (empty($rows) || $db_main->affected_rows == 0) {
					LogApi::logProcess("RedPacketModel:pickGTicketRedPacket failure. sql$sql");
					break;
				}
				$inf_rp['last_uptime'] = $now;
					
				// 插入领取流水
				$sql = "INSERT INTO t_red_packet_pick_bill (rp_id,type,money_picked,money_before,money_after,uid)";
				$sql .= " VALUES ($rp_id, $type, $money_real_picked, $money_before, $money_before+$money_real_picked, $uid)";
				$rows = $db_main->query($sql);
				if (empty($rows)) {
					LogApi::logProcess("RedPacketModel:pickGTicketRedPacket failure. sql:$sql");
					break;
				}
				
				// 插入领取记录
				$sql = "INSERT INTO t_red_packet_pick_record (rp_id,uid,money_picked)";
				$sql .= " VALUES ($rp_id, $uid, $money_real_picked)";
				$rows = $db_main->query($sql);
				if (empty($rows)) {
					LogApi::logProcess("RedPacketModel:pickGTicketRedPacket failure. sql:$sql");
					break;
				}
				
				// 获取领取列表
				$sql = "SELECT id,rp_id,uid,money_picked,UNIX_TIMESTAMP(create_time) as create_time FROM t_red_packet_pick_record WHERE id=LAST_INSERT_ID()";
				$rows = $db_main->query($sql);
				
				if (!empty($rows) && $rows->num_rows > 0) {
					$return['data']['pick_item'] = $rows->fetch_assoc();
				} else {
					LogApi::logProcess("RedPacketModel:pickGTicketRedPacket failure. sql:$sql");
				}
				
				$return['data']['inf_rp'] = $inf_rp;
				$b_success = true;

			} while (0);
			
			if ($b_success) {
				$return['code'] = RedPacketModel::$ERR_CODE_SUCCESS;
				$db_main->query("COMMIT");
			} else {
				$db_main->query("ROLLBACK");
			}
		} catch (Exception $e) {
			$db_main->query("ROLLBACK");
			$return['code'] = RedPacketModel::$ERR_CODE_UNKNOWN;
			$expMsg = $e->getMessage();
			LogApi::logProcess("RedPacketModel:pickGTicketRedPacket catch exception:$expMsg");
		}
		
		if ($b_success) {
			if ($pay_tax > 0) {
				$this->recordTax2Sys($uid, $pay_tax);
			}
			$model_tool_consume_record = new ToolConsumeRecordModel();
			$model_tool_consume_record->on_user_good_add($uid, ToolConsumeRecordModel::GOODS_ID_GOLD, $return['data']['money_picked'], ToolConsumeRecordModel::USER_GOOD_ADD_SRC_REDPACKET);
		}
		
		return $return;
	}

	// 红包过期(包括激活过期)
	public function redPacketExpire($rp_id, $stat = RedPacketModel::STATUS_EXPIRE)
	{
		$return = array (
			'code' => RedPacketModel::$ERR_CODE_UNKNOWN
		);
		
		$now = time();
		$b_success = false;
		$db_main = $this->getDbMain();
		try {
			$db_main->query("BEGIN");
			do {
				$sql = "SELECT * FROM t_red_packet_record WHERE id=$rp_id FOR UPDATE";
				$rows = $db_main->query($sql);
				if (empty($rows) || $rows->num_rows<=0) {
					LogApi::logProcess("RedPacketModel:redPacketExpire failure. sql:$sql");
					break;
				}
				
				$inf_rp = $rows->fetch_assoc();
				$inf_rp['status'] = $stat;
				$inf_rp['last_uptime'] = $now;
				// 更新红包信息
				$sql = "UPDATE t_red_packet_record SET status=$stat, last_uptime=$now WHERE id=$rp_id";
				$rows = $db_main->query($sql);
				if (empty($rows) || $db_main->affected_rows == 0) {
					LogApi::logProcess("RedPacketModel:redPacketExpire failure. sql:$sql");
					break;
				}
				
				$uid = $inf_rp['uid'];
				$money_left = $inf_rp['money_total'] - $inf_rp['money_picked'];
				$money_total = $inf_rp['money_total'];
				$count_total = $inf_rp['count_total'];
				$money_picked = $inf_rp['money_picked'];
				$count_picked = $inf_rp['count_picked'];
				$rp_type = $inf_rp['type'];
				
				// 金币返还
				$money_before = 0;
				$sql = "SELECT coin_balance FROM user_attribute WHERE uid = $uid FOR UPDATE";
				$rows = $db_main->query($sql);
				if (empty($rows) || $rows->num_rows <= 0) {
					LogApi::logProcess("RedPacketModel:redPacketExpire failure. sql:$sql");
					break;
				}
				$row = $rows->fetch_assoc();
				$money_before = $row['coin_balance'];
					
				$sql = "UPDATE user_attribute SET coin_balance = coin_balance + $money_left WHERE uid = $uid";
				$rows = $db_main->query($sql);
				if (empty($rows) || $db_main->affected_rows == 0) {
					LogApi::logProcess("RedPacketModel:redPacketExpire failure. sql:$sql");
					break;
				}
				
				// 返回流水
				$sql = "INSERT INTO t_red_packet_fallback_bill";
				$sql .= " (rp_id,type,money_total,count_total,money_picked,count_picked,money_fallback,money_before,money_after,uid)";
				$sql .= " VALUES ($rp_id, $rp_type, $money_total, $count_total, $money_picked, $count_picked, $money_left, $money_before, $money_before+$money_left, $uid)";
				$rows = $db_main->query($sql);
				if (empty($rows)) {
					LogApi::logProcess("RedPacketModel:redPacketExpire failure. sql:$sql");
					break;
				}
				
				// 清理缓存
				$user_attr = new UserAttributeModel();
				$user_attr->cleanCache($uid);
				
				$return['data'] = $inf_rp;
				$b_success = true;
			} while (0);
			
			if ($b_success) {
				$return['code'] = RedPacketModel::$ERR_CODE_SUCCESS;
				$db_main->query("COMMIT");
			} else {
				$db_main->query("ROLLBACK");
			}
		} catch (Exception $e) {
			$db_main->query("ROLLBACK");
			$return['code'] = RedPacketModel::$ERR_CODE_UNKNOWN;
			$expMsg = $e->getMessage();
			LogApi::logProcess("RedPacketModel:pickRedPacket catch exception:$expMsg");
		}
		
		return $return;
	}
	
	// 粉丝红包激活
	public function fansRedPacketActive($rp_id, $count_total)
	{
		$now = time();
		$b_success = false;
		$db_main = $this->getDbMain();
		
		$return = array (
			'code' => RedPacketModel::$ERR_CODE_UNKNOWN
		);
		
		try {
			$db_main->query("BEGIN");
			do {
				$sql = "SELECT * FROM t_red_packet_record WHERE id=$rp_id FOR UPDATE";
				$rows = $db_main->query($sql);
				if (empty($rows) || $rows->num_rows <= 0) {
					LogApi::logProcess("RedPacketModel:fansRedPacketActive failure. sql:$sql");
					break;
				}
				
				$inf_rp = $rows->fetch_assoc();
				$inf_rp['status'] = RedPacketModel::STATUS_ACTIVE;
				$inf_rp['last_uptime'] = $now;
				$inf_rp['count_total'] = $count_total;
				$status = $inf_rp['status'];
				
				$sql = "UPDATE t_red_packet_record SET status=$status, count_total=$count_total, last_uptime=$now WHERE id=$rp_id";
				$rows = $db_main->query($sql);
				if (empty($rows) || $db_main->affected_rows == 0) {
					LogApi::logProcess("RedPacketModel:fansRedPacketActive failure. sql:$sql");
					break;
				}
				
				$return['data'] = $inf_rp;
				$b_success = true;
			} while(0);
			
			if ($b_success) {
				$return['code'] = RedPacketModel::$ERR_CODE_SUCCESS;
				$db_main->query("COMMIT");
			} else {
				$db_main->query("ROLLBACK");
			}
		} catch (Exception $e) {
			$db_main->query("ROLLBACK");
			$return['code'] = RedPacketModel::$ERR_CODE_UNKNOWN;
			$expMsg = $e->getMessage();
			LogApi::logProcess("RedPacketModel:fansRedPacketActive catch exception:$expMsg");
		}
		
		return $return;
	}
	
	// 分享红包激活
	public function shareRedPacketActive($rp_id, $count_total)
	{
		$now = time();
		$b_success = false;
		$db_main = $this->getDbMain();
	
		$return = array (
				'code' => RedPacketModel::$ERR_CODE_UNKNOWN
		);
	
		try {
			$db_main->query("BEGIN");
			do {
				$sql = "SELECT * FROM t_red_packet_record WHERE id=$rp_id FOR UPDATE";
				$rows = $db_main->query($sql);
				if (empty($rows) || $rows->num_rows <= 0) {
					LogApi::logProcess("RedPacketModel:shareRedPacketActive failure. sql:$sql");
					break;
				}
	
				$inf_rp = $rows->fetch_assoc();
				$inf_rp['status'] = RedPacketModel::STATUS_ACTIVE;
				$inf_rp['last_uptime'] = $now;
				$status = $inf_rp['status'];
	
				$sql = "UPDATE t_red_packet_record SET status=$status, last_uptime=$now WHERE id=$rp_id";
				$rows = $db_main->query($sql);
				if (empty($rows) || $db_main->affected_rows == 0) {
					LogApi::logProcess("RedPacketModel:shareRedPacketActive failure. sql:$sql");
					break;
				}
	
				$return['data'] = $inf_rp;
				$b_success = true;
			} while(0);
				
			if ($b_success) {
				$return['code'] = RedPacketModel::$ERR_CODE_SUCCESS;
				$db_main->query("COMMIT");
			} else {
				$db_main->query("ROLLBACK");
			}
		} catch (Exception $e) {
			$db_main->query("ROLLBACK");
			$return['code'] = RedPacketModel::$ERR_CODE_UNKNOWN;
			$expMsg = $e->getMessage();
			LogApi::logProcess("RedPacketModel:shareRedPacketActive catch exception:$expMsg");
		}
	
		return $return;
	}
	
	// 获取领取列表
	public function getPickItems($item_id, $rp_id, $size, $flag = false)
	{
		$return = array (
			'code' => RedPacketModel::$ERR_CODE_UNKNOWN
		);
		
		$db_main = $this->getDbMain();
		if (!empty($item_id)) {
			$sql = "SELECT id,rp_id,uid,money_picked,UNIX_TIMESTAMP(create_time) as create_time FROM t_red_packet_pick_record WHERE rp_id=$rp_id";
			if ($flag){
				$sql .= " AND id <= $item_id";
			} else {
				$sql .= " AND id < $item_id";
			}
			$sql .= "  ORDER BY id DESC LIMIT $size";
		} else {
			$sql = "SELECT id,rp_id,uid,money_picked,UNIX_TIMESTAMP(create_time) as create_time FROM t_red_packet_pick_record WHERE rp_id=$rp_id ORDER BY id DESC LIMIT $size";
		}
		
		$rows = $db_main->query($sql);
		if (!empty($rows) && $rows->num_rows > 0) {
			$i = 0;
			$row = null;
			do {
				$row = $rows->fetch_assoc();
				if (!empty($row)) {
					$return['data']['list_pick_items'][$i++] = $row;
				}
			} while (!empty($row));
					
			$return['code'] = RedPacketModel::$ERR_CODE_SUCCESS;
		}
		
		return $return;
	}
	
	// 获取领取记录
	public function getRedPacketPickRecord($uid, $rp_id)
	{
		// 先从redis中查询
		// 再从数据库中查询
		// 最后存储数据到redis并设置过期时间
		$return = null;
		do {
			$key = "str_rp_pick_record:$uid:$rp_id";
			$return = $this->getRedisMaster()->get($key);
			if (!empty($return)) {
				$return = json_decode($return, true);
				break;
			}
			$sql = "SELECT id,rp_id,uid,money_picked,UNIX_TIMESTAMP(create_time) as create_time FROM t_red_packet_pick_record WHERE rp_id=$rp_id AND uid=$uid order by id desc limit 1";
			$db_main = $this->getDbMain();
			$rows = $db_main->query($sql);
			if (!empty($rows) && $rows->num_rows > 0) {
				$return = $rows->fetch_assoc();
				$this->getRedisMaster()->setex($key, 600, json_encode($return));
			}
		} while (0);
		
		return $return;
	}
	
	// 分享通知请求
	public static function shareReq()
	{
		
	}
		
	public function loadLuckyRedPacketConf()
	{		
		//$conf = $this->getRedisMaster()->hGet("redPackConfig", RedPacketModel::TYPE_LUCKY_RP);
		if (empty($conf)) {
			$db_card = $this->getDbMain();
			$sql = "SELECT * FROM card.red_pack_config WHERE type=" . RedPacketModel::TYPE_LUCKY_RP;
			$rows = $db_card->query($sql);
			if (!empty($rows) && $rows->num_rows > 0) {
				$conf = $rows->fetch_assoc();
			}
		} else {
			$conf = json_decode($conf, true);
		}
		
		if (!empty($conf)) {
			RedPacketModel::$CONF_LUCKY_RP_MONEY_UPPER_LIMIT = $conf['amountmax'];
			RedPacketModel::$CONF_LUCKY_RP_MONEY_LOWER_LIMIT = $conf['amountmin'];
			RedPacketModel::$CONF_LUCKY_RP_EXPIRE_TIME		 = $conf['expire_time'];
			RedPacketModel::$CONF_LUCKY_RP_COUNT_UPPER_LIMIT = $conf['nummax'];
			RedPacketModel::$CONF_LUCKY_RP_COUNT_LOWER_LIMIT = $conf['nummin'];
			RedPacketModel::$CONF_LUCKY_RP_TAX				 = $conf['tax'];
			RedPacketModel::$CONF_LUCKY_RP_DUTY_FREE		 = $conf['duty_free'];
		}		
	}
	
	public function loadFansRedPacketConf()
	{	// todo:	
		//$conf = $this->getRedisMaster()->hGet("redPackConfig", RedPacketModel::TYPE_FANS_RP);
		if (empty($conf)) {
			$db_card = $this->getDbMain();
			$sql = "SELECT * FROM card.red_pack_config WHERE type=" . RedPacketModel::TYPE_FANS_RP;
			$rows = $db_card->query($sql);
			if (!empty($rows) && $rows->num_rows > 0) {
				$conf = $rows->fetch_assoc();
			}
		} else {
			$conf = json_decode($conf, true);
		}
		
		$ttt = array();
		if (!empty($conf)) {			
			RedPacketModel::$CONF_FANS_RP_ACTIVE_TIME			= $conf['active_time'];
			RedPacketModel::$CONF_FANS_RP_COUNT_BASE			= $conf['nummin'];
			RedPacketModel::$CONF_FANS_RP_EXPIRE_TIME			= $conf['expire_time'];
			RedPacketModel::$CONF_FANS_RP_MONEY_LOWER_LIMIT		= $conf['amountmin'];
			RedPacketModel::$CONF_FANS_RP_MONEY_UPPER_LIMIT		= $conf['amountmax'];
			RedPacketModel::$CONF_FANS_RP_TAX					= $conf['tax'];
			RedPacketModel::$CONF_FANS_RP_DUTY_FREE				= $conf['duty_free'];
		}		
	}
	
	public function loadShareRedPacketConf()
	{		
		//$conf = $this->getRedisMaster()->hGet("redPackConfig", RedPacketModel::TYPE_SHARE_RP);
		if (empty($conf)) {
			$db_card = $this->getDbMain();
			$sql = "SELECT * FROM card.red_pack_config WHERE type=" . RedPacketModel::TYPE_SHARE_RP;
			$rows = $db_card->query($sql);
			if (!empty($rows) && $rows->num_rows > 0) {
				$conf = $rows->fetch_assoc();
			}
		} else {
			$conf = json_decode($conf, true);
		}
		
		$ttt = array();
		if (!empty($conf)) {
			RedPacketModel::$CONF_SHARE_RP_ACTIVE_COUNT_BASE	= $conf['active_base_count'];
			RedPacketModel::$CONF_SHARE_RP_ACTIVE_TIMEOUT		= $conf['active_expire_time'];
			RedPacketModel::$CONF_SHARE_RP_COUNT_LOWER_LIMIT	= $conf['nummin'];
			RedPacketModel::$CONF_SHARE_RP_COUNT_UPPER_LIMIT	= $conf['nummax'];
			RedPacketModel::$CONF_SHARE_RP_EXPIRE_TIME			= $conf['expire_time'];
			RedPacketModel::$CONF_SHARE_RP_MONEY_LOWER_LIMIT	= $conf['amountmin'];
			RedPacketModel::$CONF_SHARE_RP_MONEY_UPPER_LIMIT	= $conf['amountmax'];
			RedPacketModel::$CONF_SHARE_RP_TAX					= $conf['tax'];
			RedPacketModel::$CONF_SHARE_RP_DUTY_FREE			= $conf['duty_free'];
		}
	}

	public function loadGTicketRedPacketConf()
	{
		//$conf = $this->getRedisMaster()->hGet("redPackConfig", RedPacketModel::TYPE_GTICKET_RP);
		if (empty($conf)) {
			$db_card = $this->getDbMain();
			$sql = "SELECT * FROM card.red_pack_config WHERE type=" . RedPacketModel::TYPE_GTICKET_RP;
			$rows = $db_card->query($sql);
			if (!empty($rows) && $rows->num_rows > 0) {
				$conf = $rows->fetch_assoc();
			}
		} else {
			$conf = json_decode($conf, true);
		}
		
		$ttt = array();
		if (!empty($conf)) {
			RedPacketModel::$CONF_GTICKET_RP_MONEY_LOWER_LIMIT	= $conf['amountmin'];
			RedPacketModel::$CONF_GTICKET_RP_MONEY_UPPER_LIMIT	= $conf['amountmax'];
			RedPacketModel::$CONF_GTICKET_RP_COUNT_LOWER_LIMIT	= $conf['nummin'];
			RedPacketModel::$CONF_GTICKET_RP_COUNT_UPPER_LIMIT	= $conf['nummax'];
			RedPacketModel::$CONF_GTICKET_RP_EXPIRE_TIME		= $conf['expire_time'];
			RedPacketModel::$CONF_GTICKET_RP_TAX				= $conf['tax'];
			RedPacketModel::$CONF_GTICKET_RP_DUTY_FREE			= $conf['duty_free'];
		}
	}
	
	public function clearRedPacketCache($sid, $rp_id)
	{
		$this->delRedPacketFromCache($sid, $rp_id);
		$this->delRedPacketExpireCdtInCache($rp_id);
		$this->delPickRecordCache($rp_id);
	}
	
	public function clearShareRedPacketCache($rp_id)
	{
		$this->delSharePacketShareUid($rp_id);
		$this->delShareRedPacketActiveCdtInCache($rp_id);
		$this->delShareRedPacketActiveExpireCdtInCache($rp_id);
	}
	
	public function clearFansRedPacketCache($rp_id)
	{
		$this->delFansEnrollCount($rp_id);
		$this->delFansRedPacketActiveCdtInCache($rp_id);
	}
	
	// 获取fans_id 及被关注者uid的信息
	public function getFansInfFromDB($uid, $fans_id)
	{
		$db_cms = $this->getDbMain();
		$sql = "SELECT id, type, UNIX_TIMESTAMP(update_time) AS update_time, UNIX_TIMESTAMP(create_time) AS create_time FROM cms_manager.follow_user_record WHERE uid=$fans_id AND fid=$uid AND type=1";
		$rows = $db_cms->query($sql);
		if (!empty($rows) && $rows->num_rows > 0) {
			return $rows->fetch_assoc();
		} else {
			return null;
		}
	}
	
	// 根据用户fans_id获取被关注者们的信息
	public function getFansInfsFromDB($fans_id, $uids)
	{
		$return = array(
			'code' => RedPacketModel::$ERR_CODE_SUCCESS,
			'data' => array()
		);
		$db_cms = $this->getDbMain();
		$sql = "SELECT id, fid, UNIX_TIMESTAMP(create_time) as create_time, UNIX_TIMESTAMP(update_time) as update_time, type FROM cms_manager.follow_user_record WHERE uid=$fans_id AND fid in(";
		
		$count = count($uids);
		for ($i=0; $i<$count; ++$i) {
			$sql .= $uids[$i];
			if ($i < $count-1) {
				$sql .= ",";
			}
		}
		$sql .= ")";
		
		$rows = $db_cms->query($sql);
		
		if (empty($rows)) {
			$return['code'] = RedPacketModel::$ERR_CODE_UNKNOWN;
		} else if ($rows->num_rows > 0){
			$row = null;
			do {
				$row = $rows->fetch_assoc();
				if (!empty($row)) {
					$item = array(
							'uid' => $fans_id,
							'fuid' => $row['fid'],
							'create_time' => $row['create_time'],
							'last_uptime' => $row['update_time'],
							'type' => $row['type']
					);
					array_push($return['data'], $item);
				}
			} while (!empty($row));
		}
		
		return $return;
	}
	
	// 获取关注uid的用户集合
	public function getFansIdsFromDB($uid)
	{
		$return = array (
				'code' => RedPacketModel::$ERR_CODE_SUCCESS,
				'data' => array()
		);
		
		$db_cms = $this->getDbMain();
		$sql = "SELECT uid FROM cms_manager.follow_user_record WHERE fid=$uid AND type=1";
		$rows = $db_cms->query($sql);
		if (empty($rows)) {
			$return['code'] = RedPacketModel::$ERR_CODE_UNKNOWN;
		} else if ($rows->num_rows > 0) {
			$row = null;
			do {
				$row = $rows->fetch_assoc();
				if (!empty($row)) {
					array_push($return['data'], intval($row['uid']));
				}
			} while (!empty($row));
		}
		
		return $return;
	}
	
	public function sendSysMsg($uid, $rp_id, $msg)
	{
		$url = GlobalConfig::GetSendSysMsgURL();
		
		$content = array(
				'content' => $msg,
				'uids' => $uid . "|"
		);
		
		$redis_key = "rp_back_sys_msg:$rp_id";
		$content = json_encode($content);
		
		$this->getRedisMaster()->set($redis_key, $content);
		
		$url .= $redis_key;
		$ch = curl_init();
		$curl_opt = array(
				CURLOPT_URL =>$url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT_MS => 1000
		);
		curl_setopt_array($ch, $curl_opt);
		$data = curl_exec($ch);
		curl_close($ch);

		LogApi::logProcess("RedPacketModel:sendSysMsg redis key:$content msg:$content rs:$data");
	}
	
	public function formatRedPacketSysMsg($inf_rp, $old_rp_status)
	{
		$type = $inf_rp['type'];
		$sid = $inf_rp['sid'];
		$uid = $inf_rp['uid'];
		$status = $inf_rp['status'];
		$create_time = $inf_rp['create_time'];
		$money_total = $inf_rp['money_total'];
		$money_picked = $inf_rp['money_picked'];
		$count_total = $inf_rp['count_total'];
		$count_picked = $inf_rp['count_picked'];
		$snick = null;
		$money_left = $money_total - $money_picked;
		$money_left = $money_left>0?$money_left:0;
		
		$channellive_model = new ChannelLiveModel();
		$channel_info = $channellive_model->getSessionInfo($sid);
		if (!empty($channel_info)) {
			$singer_id = $channel_info['owner'];
			$uinfo_model = new UserInfoModel();
			$uinfo = $uinfo_model->getInfoById($singer_id);
			$snick = $uinfo['nick'];
		}
		
		$date = date('Y-m-d', $create_time);
		$temp = explode("-", $date);
		$date_str = ($temp[0] + 0) . "年" . ($temp[1] + 0) . "月" . ($temp[2] + 0) . "日";
		$msg = null;
		if ($type == RedPacketModel::TYPE_LUCKY_RP) {
			$msg = "尊敬的用户，您于$date_str" . "在$snick" . "房间发的手气红包，";
			$msg .= "已领取" . $count_picked . "/" . $count_total . "个，";
			$msg .= "共" . $money_picked . "/" . $money_total . "金币，";
			$msg .= "剩余的" . $money_left . "金币将退还至您的账户中，请查收。";
		} else if ($type == RedPacketModel::TYPE_FANS_RP) {
			$msg = "尊敬的用户，您于$date_str" . "在$snick" . "房间发的粉丝红包，";
			$msg .= "已领取" . $money_picked . "/" . $money_total . "金币，";
			$msg .= "剩余的" . $money_left . "金币将退还至您的账户中，请查收。";
		} else if ($type == RedPacketModel::TYPE_SHARE_RP) {
			if ($old_rp_status == RedPacketModel::STATUS_INITIAL) {
				$msg = "尊敬的用户，您于$date_str" . "在$snick" . "房间发的分享红包，";
				$msg .= "由于未达到分享次数红包激活失败，" . $money_total . "金币将退还至您的账户中。";
			} else {
				$msg = "尊敬的用户，您于$date_str" . "在$snick" . "房间发的分享红包，";
				$msg .= "已领取" . $count_picked . "/" . $count_total . "个，";
				$msg .= "共" . $money_picked . "/" . $money_total . "金币，";
				$msg .= "剩余的" . $money_left . "金币将退还至您的账户中，请查收。";
			}
		} else if ($type == RedPacketModel::TYPE_GTICKET_RP) {
			$msg = "尊敬的用户，您于$date_str" . "在$snick" . "房间发的帮会票红包，";
			$msg .= "已领取" . $money_picked . "/" . $money_total . "金币，";
			$msg .= "剩余的" . $money_left . "金币将退还至您的账户中，请查收。";
		}
		
		LogApi::logProcess("RedPacketModel formatRedPacketSysMsg str:$msg");
		return $msg;
	}
	
	public function statusErrcode($status)
	{
		$errcode = RedPacketModel::$ERR_CODE_UNKNOWN;
		switch ($status) {
			case RedPacketModel::STATUS_INITIAL:
				$errcode = RedPacketModel::$ERR_CODE_ACTIVE_FALSE;
				break;
			case RedPacketModel::STATUS_EXPIRE:
				$errcode = RedPacketModel::$ERR_CODE_PICK_EXPIRE;
				break;
			case RedPacketModel::STATUS_END:
				$errcode = RedPacketModel::$ERR_CODE_PICK_END;
				break;
		}
		
		return $errcode;
	}
	
	public function convertRedPacketInf(&$inf_rp)
	{
		$inf_rp['id'] = intval($inf_rp['id']);
		$inf_rp['sid'] = intval($inf_rp['sid']);
		$inf_rp['uid'] = intval($inf_rp['uid']);
		$inf_rp['type'] = intval($inf_rp['type']);
		$inf_rp['status'] = intval($inf_rp['status']);
		$inf_rp['money_total'] = intval($inf_rp['money_total']);
		$inf_rp['money_picked'] = intval($inf_rp['money_picked']);
		$inf_rp['count_total'] = intval($inf_rp['count_total']);
		$inf_rp['count_picked'] = intval($inf_rp['count_picked']);
		$inf_rp['create_time'] = intval($inf_rp['create_time']);
		$inf_rp['last_uptime'] = intval($inf_rp['last_uptime']);
		
		if (isset($inf_rp['active_time'])) {
			$inf_rp['active_time'] = intval($inf_rp['active_time']);
		}
		
		if (isset($inf_rp['conf_expire_time'])) {
			$inf_rp['conf_expire_time'] = intval($inf_rp['conf_expire_time']);
		}
		
		if (isset($inf_rp['conf_active_time'])) {
			$inf_rp['conf_expire_time'] = intval($inf_rp['conf_expire_time']);	
		}
		
		if (isset($inf_rp['conf_active_expire_time'])) {
			$inf_rp['conf_active_expire_time'] = intval($inf_rp['conf_active_expire_time']);
		}
		
		if (isset($inf_rp['times_shared'])) {
			$inf_rp['times_shared'] = intval($inf_rp['times_shared']);
		}
		
		if (isset($inf_rp['times_shared_max'])) {
			$inf_rp['times_shared_max'] = intval($inf_rp['times_shared_max']);
		}
		
		if (isset($inf_rp['singer_id'])) {
			$inf_rp['singer_id'] = intval($inf_rp['singer_id']);
		}

		if (isset($inf_rp['target_tickets'])) {
			$inf_rp['target_tickets'] = intval($inf_rp['target_tickets']);
		}

		if (isset($inf_rp['current_tickets'])) {
			$inf_rp['current_tickets'] = intval($inf_rp['current_tickets']);
		}
	}

	public function calcTax($money_picked, $base, $tax)
	{
		$pay_tax = 0;

		if ($money_picked > $base) {
			$pay_tax = $money_picked * ($tax / 100);
			$pay_tax = ceil($pay_tax);
		}

		return $pay_tax;
	}

	public function recordTax2Sys($uid, $pay_tax)
	{
		LogApi::logProcess("RedPacketModel recordTax2Sys uid:$uid pay_tax:$pay_tax");
		$db_rcec = $this->getDbRecord();
		$sql = "INSERT INTO rcec_record.sys_income_record_detail (sys_income, sys_control_fund, type, updatetime, from_uid) ";
		$sql .= "VALUES($pay_tax, 0, 117, NOW(), $uid)";
		$rows = $db_rcec->query($sql);

		if (empty($rows) || $db_rcec->affected_rows == 0) {
			LogApi::logProcess("RedPacketModel recordTax2Sys failure. sql:$sql");
		}

		$sql = "UPDATE rcec_record.sys_income_record SET sys_income=sys_income+$pay_tax, updatetime=NOW() WHERE id>=0";
		$rows = $db_rcec->query($sql);
		if (empty($rows) || $db_rcec->affected_rows == 0) {
			LogApi::logProcess("RedPacketModel recordTax2Sys failure. sql:$sql");
		}
	}
}