<?php 

class game_manager_api extends ModelBase
{
	public static function get_live_game_rq($params)
	{
		LogApi::logProcess("game_manager_api:get_live_game_rq " . json_encode($params));
		
		$uid = isset($params['uid'])?$params['uid']:0;
		$sid = isset($params['sid'])?$params['sid']:0;
		$singer_id = isset($params['singer_id'])?$params['singer_id']:0;
		
		$rs = array(
				'cmd' => 'get_live_game_rs',
				'result' => 0,
				'uid' => $params['uid'],
				'sid' => $params['sid'],
				'singer_id' => $params['singer_id']
		);
		
		$mode_game = new game_manager_model();
		
		do {
			//判断是否有摇色子或猜猜
					
			$res_guess_dice = $mode_game->get_game_guess_dice_inf($singer_id);
			if ($res_guess_dice['code'] == 0) {
				$rs['game_id'] = $res_guess_dice['game_id'];
				$rs['game_type'] = $res_guess_dice['game_type'];
				$rs['game_name'] = $res_guess_dice['game_name'];
				$rs['game_img'] = $res_guess_dice['game_img'];
				break;
			}
			
			//如果没有，则判断是否有电锯游戏
			$res_saw = $mode_game->get_game_saw_inf($singer_id);
			if ($res_saw['code'] == 0) {
				$rs['game_id'] = $res_saw['game_id'];
				$rs['game_type'] = $res_saw['game_type'];
				$rs['game_name'] = $res_saw['game_name'];
				$rs['game_img'] = $res_saw['game_img'];
				break;
			}
			
			$rs['result'] = 1;
		} while (0);
		
		$return[] = array (
				'broadcast' => 0,
				'data' => $rs
		);
		
		LogApi::logProcess("game_manager_api:get_live_game_rq rs " . json_encode($return));
		
		return $return;
	}
}