<?php 
class GloryModel extends ModelBase
{
	// 用户荣耀值增加
	public function gloryAdd($uid, $glory_value)
	{
		$key = "user:glory";
		$field = $uid . "";
		
		$redis = $this->getRedisMaster();
		if (!empty($redis)) {
			$redis->hIncrBy($key, $field, $glory_value);
		}
	}
	
	// 主播魅力值增加
	public function anchorCharmAdd($uid, $charm_value)
	{
		$key = "anchor:glory";
		$field = $uid . "";
		
		$redis = $this->getRedisMaster();
		if (!empty($redis)) {
			$redis->hIncrBy($key, $field, $charm_value);
		}
	}
	
	// 单个用户荣耀值获取
	public function gloryGet($uid, $money_level)
	{
		$ret = null;
		
		$key = "user:glory";
		$field = $uid . "";
		
		$redis = $this->getRedisMaster();
		if (!empty($redis)) {
			$ret = $redis->hGet($key, $field);
			$ret = intval($ret);
		}
		
		if (empty($ret)) {
			$ret = $this->getMoneyLevelGlory($money_level);
			$redis->hSetNx($key, $field, $ret);
		}
		
		return $ret;
	}
	
	// 获取多个用户荣耀值
	public function gloryMGet($uids)
	{
		$ret = null;
		
		$key = "user:glory";
		
		$redis = $this->getRedisMaster();
		
		if (!empty($redis)) {
			$ret = $redis->hMget($key, $uids);
		}
		
		return $ret;
	}
	
	// 从缓存获取房间荣耀-阳光等级配置
	public function getRoomSunConfFromCache()
	{
		$item = array();
		$key = "h_conf_room_glory_level";
		$redis = $this->getRedisMaster();
		$ret = $redis->hGetAll($key);
		
		if (!empty($ret)) {
			foreach ($ret as $field=>$value) {
				$item[(int)$field] = json_decode($value, true);
			}
		}
		
		return $item;
	}
	
	// 获取房间荣耀-阳光等级配置
	public function getRoomSunConf()
	{
		$items = $this->getRoomSunConfFromCache();
		if (!empty($items)) {
			return $items;
		}
		
		$items = array();
		
		$db_card = $this->getDbMain();
		$redis = $this->getRedisMaster();
		
		$key = "h_conf_room_glory_level";
		$sql = "SELECT * FROM card.room_sun_level";
		$rows = $db_card->query($sql);
		
		if (!empty($rows)) {
			$row = null;
			$row = $rows->fetch_assoc();
			
			while (!empty($row)) {
				$items[(int)$row['level']] = $row;
				$redis->hSet($key, $row['level'] . "", json_encode($row));
				$row = $rows->fetch_assoc();
			}
		}
		
		return $items;
	}
	
	// 获取房间荣耀值信息
	public function getRoomGloryTotal($sid)
	{
		$ret = 0;
		
		$key = "h_room_glory_inf";
		$field1 = "$sid:glory_total";
		
		$redis = $this->getRedisMaster();
		if (!empty($redis)) {
			$ret = $redis->hGet($key, $field1);
		}
		
		return $ret;
	}
	
	// 设置房间荣耀值信息
	public function setRoomGloryTotal($sid, $glory_total)
	{
		$ret = null;
		
		$key = "h_room_glory_inf";
		$field1 = "$sid:glory_total";
		
		$redis = $this->getRedisMaster();
		
		if (!empty($redis)) {
			$ret = $redis->hSet($key, $field1, $glory_total);
		}
		
		return $ret;
	}
	
	public function incrRoomGloryTotal($sid, $glory)
	{
		$ret = null;
		
		$key = "h_room_glory_inf";
		$field1 = "$sid:glory_total";
		
		$redis = $this->getRedisMaster();
		
		if (!empty($redis)) {
			$ret = $redis->hIncrBy($key, $field1, $glory);
		}
		
		return $ret;
	}
	
	// 
	public function calcRoomSunShineLv($sid, $glory_total)
	{	
		$ret = array(
				'glory_total' => intval($glory_total),
				'max_room_sunshine_lv' => 0,
				'cur_room_sunshine_lv' => 0,
				'user_sunshine_plus' => 0,
				'anchor_sunshine_plus' => 0,
				'last_uptime' => 0,
				'sid' => $sid
		);
		
		$conf_glory_inf = $this->getRoomSunConf();
		
		do {
			if (empty($conf_glory_inf)) {
				break;
			}
			
			$size = count($conf_glory_inf);
			$ret['max_room_sunshine_lv'] = $size - 1;
			for ($i=$size-1; $i>=0; --$i) {
				if ($glory_total >= $conf_glory_inf[$i]['glory_value']) {
					$ret['cur_room_sunshine_lv'] = $i;
					$ret['user_sunshine_plus'] = $conf_glory_inf[$i]['user_sun_plus'] / 100;
					$ret['anchor_sunshine_plus'] = $conf_glory_inf[$i]['anchor_sun_plus'] / 100;
					$ret['last_uptime'] = $this->getMillisecond();
					break;
				}
			}
		} while (0);
		
		return $ret;
	}
	
	private function getMillisecond() {
		list($t1, $t2) = explode(' ', microtime());
		return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
	}
	
	public function onUserLeave($sid, $uid, $money_level)
	{
		$flag = false;
		$key = "set_glory_room_users:$sid";
		$redis = $this->getRedisMaster();
		if (!empty($redis)) {
			$flag = $redis->srem($key, $uid);
		}
		
		if (!$flag) {
			return null;
		}
		
		// 用户荣耀值
		$user_glory_total = $this->gloryGet($uid, $money_level);
		$user_glory_total = isset($user_glory_total)?$user_glory_total:0;
	
		// 直播间荣耀总值
		$room_glory_total = $this->incrRoomGloryTotal($sid, 0-$user_glory_total);
		if ($room_glory_total < 0) {
			$room_glory_total = 0;
		}
	
		$room_glory_inf = $this->calcRoomSunShineLv($sid, $room_glory_total);
	
		$room_glory_inf['cmd'] = 'BUpdateRoomSunshineLv';
		return array(
				'broadcast' => 1,
				'data' => $room_glory_inf
		);
	}
	
	public function onUserEnter($sid, $uid, $singer_id, $money_level)
	{
		$flag = false;
		$key = "set_glory_room_users:$sid";
		$redis = $this->getRedisMaster();
		if (!empty($redis)) {
			$flag = $redis->sAdd($key, $uid);
		}
		
		$user_glory_total = 0;
		$user_glory_lv = 0;
		$ret = array(
				'user_glory_inf' => array(
						'glory_total' => 0,
						'glory_lv' => 0
				),
				'room_glory_inf' => null
		);
		if ($uid != $singer_id) {
			$user_glory_total = $this->gloryGet($uid, $money_level);
			$user_glory_total = isset($user_glory_total)?$user_glory_total:0;
			 
			$sys_parameters = new SysParametersModel();
			$conf_user_glory_lv1 = $sys_parameters->GetSysParameters(172, 'parm1');
			$conf_user_glory_lv2 = $sys_parameters->GetSysParameters(173, 'parm1');
			$conf_user_glory_lv3 = $sys_parameters->GetSysParameters(174, 'parm1');
			 
			if ($user_glory_total >= $conf_user_glory_lv3) {
				$user_glory_lv = 3;
			} else if ($user_glory_total >= $conf_user_glory_lv2) {
				$user_glory_lv = 2;
			} else if ($user_glory_total >= $conf_user_glory_lv1) {
				$user_glory_lv = 1;
			} else {
				$user_glory_lv = 0;
			}
			
			$ret['user_glory_inf']['glory_total'] = (int)$user_glory_total;
			$ret['user_glory_inf']['glory_lv'] = (int)$user_glory_lv;
		}
		
		if ($flag) {
			// 直播间荣耀总值
			$room_glory_total = $this->incrRoomGloryTotal($sid, $user_glory_total);
			$room_glory_inf = $this->calcRoomSunShineLv($sid, $room_glory_total);
			$room_glory_inf['cmd'] = 'BUpdateRoomSunshineLv';
			$ret['room_glory_inf'] = array(
					'broadcast' => 1,
					'data' => $room_glory_inf
			);
		}
		
		return $ret;
	}
	
	public function onUpdate($sid, $users)
	{
		$ret = array();
		$cur_room_glory_total = $this->getRoomGloryTotal($sid);
		$cur_room_glory_inf = $this->calcRoomSunShineLv($sid, $cur_room_glory_total);
		 
		$array_user_glorys = $this->gloryMGet($users);
		$real_room_glory_total = 0;
		 
		if (!empty($array_user_glorys)) {
			foreach ($array_user_glorys as $glory_uid => $glory) {
				$real_room_glory_total += $glory;
			}
		}
		 
		if ($cur_room_glory_total != $real_room_glory_total) {
			$this->setRoomGloryTotal($sid, $real_room_glory_total);
		}
		 
		$real_room_glory_inf = $this->calcRoomSunShineLv($sid, $real_room_glory_total);
		 
		if ($cur_room_glory_inf['cur_room_sunshine_lv'] != $real_room_glory_inf['cur_room_sunshine_lv']) {
			// send notify.
			$real_room_glory_inf['isRoom'] = true;
			$real_room_glory_inf['cmd'] = 'BUpdateRoomSunshineLv';
			
			$ret['data'] = $real_room_glory_inf;
			$ret['broadcast'] = 1;
		}
		
		return $ret;
	}
	
	public function getMoneyLevelGlory($level)
	{
		$glory = 0;
		
		$key = "h_user_money_level_glory";
		$redis = $this->getRedisMaster();
		
		if (!empty($redis)) {
			$glory = $redis->hGet($key, $level);
		}
		
		if (0 == $glory) {
			$sql = "SELECT glory FROM cms_manager.user_money WHERE money_level=$level";
			$db_cms = $this->getDbMain();
			
			$rows = $db_cms->query($sql);
			
			if (!empty($rows)) {
				$row = $rows->fetch_assoc();
				if (!empty($row)) {
					$glory = $row['glory'];
					$redis->hSet($key, $level, $glory);
				}
			}
		}
		
		return $glory;
	}
}
?>