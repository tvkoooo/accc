<?php 

class game_manager_model extends ModelBase
{
	const REDIS_KEY_ANCHOR_SAW_LOCK = 'ANCHOR_SAW_LOCK_';			// 电锯游戏锁，该做从游戏申请到游戏结束过程中一直存在
	
	public function get_game_guess_dice_inf($singer_id)
	{
		$return = array(
				'code' => 0
		);
		
		$model_game = new GameModel();
		
		do {
			$id = $model_game->getGameIdBySingerId($singer_id);
			if (empty($id)) {
				$return['code'] = -1;
				break;
			}
				
			$game_id = $model_game->GetCurGameId($id, 0);
			if ($game_id == -1) {
				$return['code'] = -1;
				break;
			}
				
			$row = $model_game->getGameInfo($game_id);
			if (empty($row) || $row['status'] != 1) {
				$return['code'] = -1;
				break;
			}
			
			$return['game_id'] = intval($id);
			$return['game_type'] = intval($game_id);
			$return['game_name'] = $row['name'];
			$return['game_img'] = $row['img_name'];
		} while (0);

		return $return;
	}
	
	public function get_game_saw_inf($singer_id)
	{
		$return = array(
				'code' => 0
		);
		
		$model_saw = new game_saw_model();
		
		do {
			$game_id = $model_saw->get_saw_game_id_by_singer_id($singer_id);
			
			if ($game_id === false) {
				$return['code'] = -1;
				return $return;
			}
			
			$game_status =  $model_saw->get_saw_game_status($game_id);
			
			if (($game_status != game_saw_model::GAME_SAW_STATUS_ENROLL) && ($game_status != game_saw_model::GAME_SAW_STATUS_ING)) {
				$return['code'] = -1;
				break;
			}
			
			$game_inf = $model_saw->get_saw_game_base_inf($game_id);
			
			if (empty($game_inf)) {
				$return['code'] = -1;
				break;
			}
			
			$return['game_id'] = intval($game_id);
			$return['game_type'] = intval($game_inf['game_type']);
			$return['game_name'] = $game_inf['game_name'];
			$return['game_img'] = isset($game_inf['game_img'])?$game_inf['game_img']:"";
		} while (0);
		
		return $return;
	}
	
	public function if_game_saw_ing($singer_id)
	{
		$redis = $this->getRedisMaster();
		
		return $redis->exists(game_manager_model::REDIS_KEY_ANCHOR_SAW_LOCK . $singer_id);
	}
}
