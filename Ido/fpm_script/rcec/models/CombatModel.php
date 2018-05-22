<?php 
class CombatModel extends ModelBase
{	
	public function getMaxCombatCardInfo($uid)
	{
		$ret = null;
		 
		$key = "h_user_max_combat_card:" . $uid % 1024;
		$field = $uid . "";
		 
		$redis = $this->getRedisMaster();
		 
		if (!empty($redis)) {
			$ret = $redis->hGet($key, $field);
			if (!empty($ret)) {
				$ret = json_decode($ret, true);
			}
		}
		
		// if empty $ret. then return and set default value into redis.
		if (empty($ret)) {
			$ret = array(
					'life' => 1886,
					'attack' => 325,
					'current_format_type' => 3,
					'combat' => 257,
					'select_flag' => 1,
					'uid' => $uid,
					'card_order' => '1,3,5,7,9'
			);
		}
		
		return $ret;
	}
	
	public function getCombatAttrFromCache($uid, $card_type)
	{
		$ret = null;
		
		$key = "h_user_combat_attr:" . $uid % 1024;
		$field = "$uid:$card_type";
		
		$redis = $this->getRedisMaster();
		
		if (!empty($redis)) {
			$ret = $redis->hGet($key, $field);
			
			if (!empty($ret)) {
				$ret = json_decode($ret, true);
			}
		}
		
		return $ret;
	}
	
	public function flushCombatAttr2Cache($uid, $card_type, $combat_info)
	{
		$ret = null;
		
		$key = "h_user_combat_attr:" . $uid % 1024;
		$field = "$uid:$card_type";
		
		$redis = $this->getRedisMaster();
		
		if (!empty($redis)) {
			$ret = $redis->hSet($key, $field, json_encode($combat_info));
		}
		
		return $ret;
	}
	
	public function calcUserCombatAttr($uid, $card_info, $active_info, $rich_info)
	{
// 		$ret = array(
// 				'rich_info' => null,
// 				'active_info' => null,
// 				'combat_info' => null,
// 		);
// 		$model_uinfo = new UserInfoModel();
// 		$model_uattr = new UserAttributeModel();
		
// 		$uinfo = $model_uinfo->getInfoById($uid);
// 		$uattr = $model_uattr->getAttrByUid($uid);
		
// 		$active_info = $model_uattr->getActiveLevel($uattr['active_point'], $uid);
// 		$rich_info = $model_uattr->getRichManLevel($uid, $uattr['gift_consume']);
		
		$sys_parameters = new SysParametersModel();
		$p = $sys_parameters->GetSysParameters('161', 'parm1');
		if (empty($p)) {
			$p = 50;
		}
		
		$factor = 912;
		
		$total_attack = $active_info['attack'] + $rich_info['attack'];
		$total_life = $active_info['life'] + $rich_info['life'];
		$total_critical = $active_info['critical'] + $rich_info['critical'];
		$total_avoid = $active_info['avoid'] + $rich_info['avoid'];
		$total_dodge = $active_info['dodge'] + $rich_info['dodge'];
		$total_speed = $active_info['speed'] + $rich_info['speed'];
		$skill_level = $rich_info['skill_level'];
		
		$combat_attack = 45 * $p / $factor * $total_attack;
		$combat_life = 5 * $p / $factor * $total_life;
		$combat_critical = $card_info['attack'] * 0.5/100 *9 * $p / $factor * $total_critical / 10;
		$combat_avoid = $card_info['life'] * 1.6 / 100 * $p / $factor * $total_avoid / 10;
		$combat_dodge = $card_info['life'] * 1.3 / 100 * $p / $factor * $total_dodge / 10;
		$combat_speed = $card_info['attack'] * 1 / 100 * 9 * $p / $factor * $total_speed / 10;
		
		$combat_attr = (int)($combat_attack + $combat_life + $combat_critical + $combat_avoid + $combat_dodge + $combat_speed);
		
		$combat_card = (int)$card_info['combat'];
		$combat_all = $combat_attr + $combat_card;
		$combat_info = array(
				'attack' => $total_attack,
				'life' => $total_life,
				'critical' => $total_critical / 1000,
				'avoid' => $total_avoid / 1000,
				'dodge' => $total_dodge / 1000,
				'speed' => $total_speed / 1000,
				'skill_level' => (int)$skill_level,
				'combat_attr' => $combat_attr,
				'card_combat' => $combat_card,
				'combat_all' => $combat_all
		);
		
// 		$ret['rich_info'] = $rich_info;
// 		$ret['active_info'] = $active_info;
// 		$ret['combat_info'] = $combat_info;
		
// 		return $ret;
		return $combat_info;
	}	
}
?>

