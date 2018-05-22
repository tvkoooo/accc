<?php

/**
 * 秀場的遊戲組件：飛行棋(flight chess)
 */
class GameApi
{
    public static $SYS_PARM_ID_PREHEAT_TIME = 41;
    //玩家异常处理
    public static function dealPlayerExceptionOver($singerid)
    {
        /* LogApi::logProcess("begin GameApi::dealPlayerExceptionOver****************singerid:$singerid");
        $gameModel = new GameModel();
        $data = $gameModel->dealExceptionOver($singerid);
        if(empty($data)){
            return null;
        }

        //本场游戏id
        $id = (int)$data['id'];
        //游戏id
        $game_id = (int)$data['game_id'];
        //本场游戏状态 0:发起状态  1:游戏中
        $status = (int)$data['status'];
        //游戏类型 1:用户购买门票 2:主播发放金币
        $type = (int)$data['type'];


        if(0 == $status){//发起状态
            if(1 == $type){//用户购买门票
                $params = array();
                $params['id'] = $id;
                $params['singerid'] = $singerid;
                $params['giftId'] = $gameModel->getGameGiftId($id);
                GameApi::cancelDiceGame($params);
            }else if(2 == $type){//主播发放金币
                $params = array();
                $params['id'] = (int)$id;
                $params['singerid'] = (int)$singerid;
                GameApi::cancelGame($params);
            }
        } */
    }
    
    //主播异常处理
    public static function dealExceptionOver($singerid, &$return)
    {
        LogApi::logProcess("begin GameApi::dealExceptionOver****************singerid:$singerid");
        $result = array();
        $gameModel = new GameModel();
        $gameModel->clearPreheat($singerid);        
        $data = $gameModel->dealExceptionOver($singerid);
        if(empty($data)){
            LogApi::logProcess("GameApi::dealExceptionOver:没有获得到游戏id****************singerid:$singerid");
            return $result;
        }
        LogApi::logProcess("GameApi::dealExceptionOver****************data:".json_encode($data));
        
        //本场游戏id
        $id = $data['id'];
        //游戏id
        $game_id = $data['game_id'];
        //本场游戏状态 0:发起状态  1:游戏中
        $status = $data['status'];
        //游戏类型 1:用户购买门票 2:主播发放金币
        $type = $data['type'];


        if(0 == $status){//发起状态
            if(1 == $type){//主播发放金币
                $params = array();
                $params['id'] = (int)$id;
                $params['singerid'] = (int)$singerid;
                $return = GameApi::cancelGame($params);
            }else if(2 == $type){//用户购买门票
                $params = array();
                $params['id'] = $id;
                $params['singerid'] = $singerid;
                $params['giftId'] = $gameModel->getGameGiftId($id);
                $return = GameApi::cancelDiceGame($params);
            }
        }else if(1 == $status){//游戏中
            if(1 == $type){//主播发放金币
            	$params = array();
            	$params['singerId'] = (int)$singerid;
            	$params['id'] = (int)$id;
            	$return = GameApi::overGuessGame($params);
            }else if(2 == $type){//用户购买门票
            	$params = array();
            	$params['singerId'] = (int)$singerid;
            	$params['id'] = (int)$id;
            	$return = GameApi::overDiceGame($params);
            }
        }
                
        LogApi::logProcess("end GameApi::dealExceptionOver****************singerid:$singerid");
        return $result;
    }
    
    //结束摇骰子游戏
    public static function overDiceGame($params)
    {
        LogApi::logProcess("begin GameApi::overDiceGame****************".json_encode($params));
        $id = intval($params['id']);
        $singerid = intval($params['singerId']);

        $return = array();
        
        $rs = array();
        $rs['cmd'] = 'ROverDiceGame';
        $rs['id'] = $id;
        $rs['ranking'] = array();
        $rs['result'] = 0;
        
        $broadcast = array(
            'cmd' => 'BOverDiceGame',
            'id' => $id,
            'ranking' => array(),
            'result' => 0
        );
        
        $gameModel = new GameModel();
        $times = $gameModel->LockGameOper($id, $singerid, "POverDiceGame", 10);
        $sendRsp = true;
        
        do 
        {
        	if ($times == 1) {
        		$stat = $gameModel->GetGameStatus($id);
        		if (!$stat ||  $stat != 2) {
                    $ranking = $gameModel->GetGameRanking($id);
                    $broadcast['ranking'] = $ranking;
                    $rs['ranking'] = $ranking;
                    $return[] = array(
                        'broadcast' => 1,
                        'data' => $broadcast
                    );
        			LogApi::logProcess("GameApi::overDiceGame Fetch Lock but game already finished:" . json_encode($params) . " stat:" . $stat);
                    break;
        		}
        	} else {
        		LogApi::logProcess("GameApi::overDiceGame Fetch Lock Fail:" . json_encode($params));
        		$sendRsp = false;
        		break;
        	}
        	
        	$gameModel->ClearUserGameRelById($id);
        	
            $model_anchor_pt = new anchor_points_model();
            $overrst = $gameModel->overDiceGame($singerid, $id, &$return);
			if ($overrst ['errcode'] == 0) {
                $model_anchor_pt->on_anchor_finish_game_dice($singerid);
				if (! empty ( $overrst ['uids'] )) {
					foreach ( $overrst ['uids'] as $item ) {
						$return [] = array (
								'broadcast' => 5,
								'data' => array (
										'uid' => ( int ) $item,
										'target_type' => 17, // 17为参与互动
										'num' => 1,
										'extra_param' => 5 
								) 
						);
					}
				}
			} else if ($overrst ['errcode'] == 1) { // 过早结束游戏，需返还用户金币
                $model_anchor_pt->on_anchor_finish_game_dice($singerid);
				$giftId = $gameModel->getGameGiftId ( $id );
				
				$toolModel = new ToolModel ();
				$tool = $toolModel->getToolByTid ( $giftId );
				
				$size = $gameModel->getEnrollUserSize ( $id );
				LogApi::logProcess ( "GameApi::overDiceGame 过早结束****************报名人数:$size" );
				
				for($i = 0; $i < $size; $i ++) {
					$uid = $gameModel->popEnrollUid ( $id );
					LogApi::logProcess ( "GameApi::overDiceGame 过早结束****************返还用户($uid)报名费用:" . $tool ['price'] );
					$toolConsumRecordModel = new ToolConsumeRecordModel ();
					$success = $toolConsumRecordModel->addCoin ( $uid, $tool ['price'] );
					if (empty ( $success )) {
						LogApi::logProcess ( "GameApi::overDiceGame 过早结束****************返还用户($uid)的报名费用失败!!!" );
					}
				}
				
				// TODO： 返还主播发起金币？？
				$gameModel->ClearDiceGameCache ( $id, $singerid );
				$rs ['result'] = 104;
				$rs ['errmsg'] = "由于参与游戏的人数不足1人，结束游戏后将返还玩家本场游戏全部消费。";
				$broadcast ['result'] = 104;
				$broadcast ['errmsg'] = "游戏结算失败，将返还您本场游戏的全部消费。";
			} else {
				$rs ['result'] = - 1;
				$rs['errmsg'] = "游戏结算失败";
				$broadcast ['result'] = - 1;
				$broadcast['errmsg'] = "游戏结算失败";
			}  
            
//             $ranking = $gameModel->overDiceGame($singerid, $id);
//             if(empty($ranking))
//             {
//                 $rs['result'] = -1; // 结束失败
//                 $broadcast['result'] = -1; 
//             }elseif (!empty($ranking['items'])){
//                 foreach ($ranking['items'] as $item)
//                 {
//                     $return[] = array(
//                         'broadcast' => 5,
//                         'data' => array(
//                         'uid' => (int)$item['uid'],
//                         'target_type' => 17,//17为参与互动
//                         'num' => 1,
//                         'extra_param' =>5
//                         )
//                     );
//                 }
//             }
            
            $ranking = array(
            		'items' => $overrst['items']
            );
            $rs['ranking'] = $ranking;
            $broadcast['ranking'] = $ranking;
            $gameModel->SaveGameRanking($id, $ranking);
            /* if(empty($ranking['items']) || 0 == count($ranking['items']))
            {
                break;
            } */
            
            /* $return[] = array(
                'broadcast' => 1,
                'data' => $broadcast
            ); */
            
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$singerid,
                    'target_type' => 16,//16为发起互动
                    'num' => 1,
                    'extra_param' =>5
                )
            );
            
            $return[] = array(
            		'broadcast' => 1,
            		'data' => $broadcast
            );
        }while (FALSE);
        
        if ($gameModel->UnLockGameOper($id, $singerid, "POverDiceGame") <= 0) {
        	//$gameModel->DestoryLockGameOper($id, $singerid, "POverDiceGame");
        }
        
        if ($sendRsp) {
        	$return[] = array
        	(
        			'broadcast' => 0,
        			'data' => $rs
        	);
        }

        LogApi::logProcess("end GameApi::overDiceGame****************".json_encode($return));
        return $return;
    }
    
    //用户开始摇骰子
    public static function doDiceGame($params)
    {
        LogApi::logProcess("begin GameApi::doDiceGame****************params:".json_encode($params));
        
        $id = intval($params['id']);
        //0:玩家 1:主播
        $type = intval($params['type']);
        $uid = intval($params['uid']);
        $gameid = intval($params['gameid']);
        $giftId = intval($params['giftid']);
        $point = intval($params['point']);
        $singerid = intval($params['uid_onmic']);


		//点数改为服务端计算
		$point = 0;
		$strPoints = "";
		for($iPos = 0; 5 > $iPos; ++$iPos)
		{
		    $pointTmp = rand(1, 6);
		    $point += $pointTmp;
		    
		    $strPoints = $strPoints.$pointTmp;
		    if(5 > $iPos + 1)
		    {
		        $strPoints = $strPoints.",";
		    }
		}

        
        $return = array();
        
        $rs = array
        (
            'cmd' => 'RDoDiceGame',
            'id' => $id,
            'uid' => $uid,
            'num' => 0,
            'money' => 0,
            'result' => 0,
            'point' => $strPoints
        );
        
        do 
        {
            $gameModel = new GameModel();
            
            // 判断游戏是否还在进行
            $curgameid = $gameModel->GetCurGameId($id, 1);
            if ($curgameid == -1 || $curgameid != $gameid) {
            	$rs['result'] = 105;
            	$rs['errmsg'] = "游戏已结束或取消";
            	LogApi::logProcess("begin GameApi::doDiceGame***游戏已结束或取消*************".json_encode($rs));
            	 
            	$return[] = array
            	(
            			'broadcast' => 0,
            			'data' => $rs,
            	);
            	return $return;
            }
            
            $gameKey = "game:".$id;
            $ismember = $gameModel->getRedisMaster()->sismember($gameKey,$uid);
            
            if (0 == $ismember && $singerid != $uid)
            {
                $rs['result'] = -1;// 无效操作
                $rs['errmsg'] = "摇一摇失败~ 请重试~";
                LogApi::logProcess("begin GameApi::doDiceGame***用户没有报名*************".json_encode($rs));
                
                $return[] = array
                (
                    'broadcast' => 0,
                    'data' => $rs,
                );
                return $return;
            }
            
            if (!$gameModel->IfCanRollDice($id, $uid)) {
            	$rs['result'] = 102; // 摇色子次数超出限制
            	$rs['errmsg'] = "大神，神也只能摇" . $gameModel->ROLL_DICE_COUNT_LIMIT ."次哦";
            	$return[] = array
                (
                    'broadcast' => 0,
                    'data' => $rs,
                );
                return $return;
            }
        
            $toolModel = new ToolModel();
            $tool = $toolModel->getToolByTid($giftId);
            $userAttrModel = new UserAttributeModel();
            $userAttr = $userAttrModel->getAttrByUid($uid);
            
            $num = $gameModel->getDiceTimes($id, $uid);
            $num += 1;
            
            $money = 0;
            $count = 0;
            if($num == 1){//报名时已经交过费用，第一次摇就不需要再扣费了
                if(0 == $type){
                    $money = 0;
                }else{//主播没有报名，所以每次摇都要花钱
                    $money = $tool['price'];
                    $count = 1;
                }
            }else if($num <= 3 && $num >=2){//消耗门票x1
                $money = $tool['price'];
                $count = 1;
            }else if ($num <= 7 && $num >=4 ){//消耗门票数量X2
                $money = $tool['price']*2;
                $count = 2;
            }else if ($num <= 10 && $num >= 8){//消耗门票数量X3
                $money = $tool['price']*3;
                $count = 3;
            }else if ($num >= 11){//消耗门票数量X4
                $money = $tool['price']*4;
                $count = 4;
            }
            $rs['num'] = $num;
            
            // 看用户的秀币是否足够买要求的数量的道具
            if ($userAttr['coin_balance'] < $money) 
            {
                $rs['result'] = 101; // 用户秀币不足
                $rs['errmsg'] = "金币不足~";
                LogApi::logProcess("begin GameApi::doDiceGame***用户秀币不足1*************".json_encode($rs));
                
                $return[] = array
                (
                    'broadcast' => 0,
                    'data' => $rs,
                );
                return $return;
            }
            
            //扣除报名用户对应的秀币
            $toolConsumRecordModel = new ToolConsumeRecordModel();
            $success = $toolConsumRecordModel->consumeCoin($uid, $money);
            if (empty($success) || !$success) 
            {
                $rs['result'] = 101; // 用户秀币不足
                $rs['errmsg'] = "金币不足~";
                LogApi::logProcess("begin GameApi::doDiceGame***用户秀币不足2*************".json_encode($rs));
                
                $return[] = array
                (
                    'broadcast' => 0,
                    'data' => $rs,
                );
                return $return;
            }
            
            //扣除礼物（暂时不用）注意：如果扣秀币就不能扣礼物
            /* $toolAccountModel = new ToolAccountModel();
             if(!$toolAccountModel->consume($uid, $giftId, 1)){
             return false;
             } */
            
            //把数据存入数据库
            $totalMoney = $gameModel->doDiceGame($id, $uid, $point, $num, $money, $giftId, $tool['price']);
            if(empty($totalMoney) || !$totalMoney)
            {
                //事务回滚
                //金币回滚
                $toolConsumRecordModel->addCoin($uid, $money);
            
                $rs['result'] = -1; // 用户秀币不足
                $rs['errmsg'] = "摇一摇失败~ 请重试~";
                LogApi::logProcess("begin GameApi::doDiceGame***执行数据回滚*************".json_encode($rs));
                $return[] = array
                (
                    'broadcast' => 0,
                    'data' => $rs,
                );
                return $return;
            }
            
            // 成功摇色子后才增加摇骰子次数
            $num = $gameModel->addDiceTimes($id, $uid, 1);
            $gameModel->addDiceTicketCount($id, $count);
            
            $nt = array
            (
                'cmd' => 'BDoDiceGame',
                'id' => $id,
                'money' => $totalMoney
            );
            $return[] = array
            (
                'broadcast' => 1, //全直播间
                'data' => $nt
            );            
            $rs['money'] = $totalMoney;
            
        }while (false);
        $return[] = array
        (
            'broadcast' => 0,
            'data' => $rs,
        );

        LogApi::logProcess("end GameApi::doDiceGame****************".json_encode($return));

        return $return;
    }
    
    //主播开始骰子游戏
    public static function startDiceGame($params)
    {
        LogApi::logProcess("begin GameApi::startDiceGame****************params:".json_encode($params));
        $id = intval($params['id']);
        $singerid = intval($params['singerid']);
        $gameid = intval($params['gameid']);
        
        $result = array(
            'cmd' => 'RStartDiceGame',
            'id' => $id,
            'singerid' => $singerid,
            'result' => 0
        );
        $man_floor = 0;
        $money = 0;
        $gameModel = new GameModel();
        
        $times = $gameModel->LockGameOper($id, $singerid, "PStartDiceGame", 10);
        $sendRsp = true;
        do {
        	if ($times == 1) {
        		$stat = $gameModel->GetGameStatus($id);
        		$money = $gameModel->GetDiceGameMoney($id);
        		if (!$stat || $stat != 1) {
        			LogApi::logProcess("GameApi::startDiceGame 重复开始游戏1:" .json_encode($params) . " stat:" . $stat);
        			break;
        		}
        	} else {
        		LogApi::logProcess("GameApi::startDiceGame Fetch Lock Fail:" .json_encode($params));
        		$sendRsp = false;
        		break;
        	}
        	
        	$startrs = $gameModel->startDiceGame($id, $singerid, $gameid, &$man_floor);
        	
        	$money = $startrs['money'];
        	
        	if ($startrs['errcode'] != 0) {
        		$result['result'] = $startrs['errcode'];
        		switch ($startrs['errcode']) {
        			case -1:
        				$result['errmsg'] = "开始游戏失败";
        				break;
        			case 1:
        				$result['man_floor'] = $man_floor;
        				$result['errmsg'] = "参与游戏人数少于" . $man_floor . "，不能开始游戏";
        				break;
        			default:
        		}
        		break;
        	}
        } while (0);
        
        if ($gameModel->UnLockGameOper($id, $singerid, "PStartDiceGame") <= 0) {
        	//$gameModel->DestoryLockGameOper($id, $singerid, "PStartDiceGame");
        }
        
        if ($sendRsp) {
        	$return[] = array(
        			'broadcast' => 0,
        			'data' => $result
            );
            
            if ($result['result'] == 0) {
                $broadcastResult = array(
        			'cmd' => 'BStartDiceGame',
        			'id' => $id,
        			'singerid' => $singerid,
        			'gameid' => $gameid,
        			'money' => $money
                );
                
                $return[] = array(
                        'broadcast' => 1, //全直播间
                        'data' => $broadcastResult
                );
            }
        }
       
        LogApi::logProcess("end GameApi::startDiceGame****************".json_encode($return));
        
        return $return; 
    }
    
    //主播取消摇骰子游戏
    public static function cancelDiceGame($params)
    {
        LogApi::logProcess("begin GameApi::cancelDiceGame****************".json_encode($params));
        $id = intval($params['id']);
        $singerid = intval($params['singerid']);
        $giftId = intval($params['giftId']);
        
        $toolModel = new ToolModel();
        $tool = $toolModel->getToolByTid($giftId);
        
        $gameModel = new GameModel();
        
        $times = $gameModel->LockGameOper($id, $singerid, "PCancelDiceGame", 10);
        $sendRsp = true;
        
        $result = array(
            'cmd' => 'RCancelDiceGame',
            'singerid' => $singerid,
            'result' => 0
        );

        do {
        	if ($times == 1) {
        		$stat = $gameModel->GetGameStatus($id);
        		if (!$stat || $stat != 1) {
                    LogApi::logProcess("GameApi::cancelDiceGame****************重复取消游戏:" . json_encode($params) . " stat:" . $stat);              
        			break;
        		}
        	} else {
        		LogApi::logProcess("GameApi::cancelDiceGame****************Fetch Lock Fail:" . json_encode($params));
        		$sendRsp = false;
        		break;
        	}
        	
        	// 取消已报名用户的状态
        	$gameModel->ClearUserGameRelById($id);    
        	
        	$size = $gameModel->getEnrollUserSize($id);
        	LogApi::logProcess("GameApi::cancelDiceGame****************报名人数:$size");
        	for($i = 0; $i < $size; $i++){
        		$uid = $gameModel->popEnrollUid($id);
        		LogApi::logProcess("GameApi::cancelDiceGame****************返还用户($uid)报名费用:".$tool['price']);
        	
        		if(empty($uid)){
        			continue;
        		}
        	
        		//返还用户的报名费
        		$toolConsumRecordModel = new ToolConsumeRecordModel();
        		$success = $toolConsumRecordModel->addCoin($uid, $tool['price']);
        		if(empty($success)){
        			LogApi::logProcess("GameApi::cancelDiceGame****************返还用户($uid)的报名费用失败!!!");
        		}
        	}

        	//上头条
        	$gameModel->SubSidDiceAddSysGoldCount($singerid, 1);
        	
        	//清理缓存
        	$flag = $gameModel->cancelDiceGame($singerid, $id);
        	if(empty($flag)){
        		$result['result'] = -1;
        	}
        	
        	if ($result['result'] == 0) {
        		$gameModel->ClearDiceGameCache($id, $singerid);
        	}
        } while(0);
        
        if ($gameModel->UnLockGameOper($id, $singerid, "PCancelDiceGame") <= 0) {
        	//$gameModel->DestoryLockGameOper($id, $singerid, "PCancelDiceGame");
        }
        
        if ($sendRsp) {
        	$return[] = array(
        			'broadcast' => 0,
        			'data' => $result
            );
            
            if ($result['result'] == 0) {
                $broadcastResult = array(
        			'cmd' => 'BCancelDiceGame',
        			'id' => $id,//该场游戏的唯一标识
        			'singerid' => $singerid
        	    );

        	    $return[] = array(
        			'broadcast' => 1, //全直播间
        			'data' => $broadcastResult
        	    );
            }
        }
        
        LogApi::logProcess("end GameApi::cancelDiceGame****************".json_encode($return));
        
        return $return;
    }
    
    //用户报名摇骰子游戏
    public static function enrollDiceGame($params)
    {
        LogApi::logProcess("enrollDiceGame::*******报名摇骰子,params:".json_encode($params));
        $id = intval($params['id']);
        $uid = intval($params['uid']);
        $singerid = intval($params['singerid']);
        $gameid = intval($params['gameid']);
        $giftId = intval($params['giftId']);
        
        $result = array(
            'cmd' => 'REnrollDiceGame',
            'uid' => $uid,
        	'id' => $id,
            'result' => 0
        );
        
        // 利用redis incr的原子操作来保证用户同时多次请求时只处理一次
        $gameModel = new GameModel();
        $times = $gameModel->LockGameOper($id, $uid, "PEnrollDiceGame", 10);
        do {
            if ($times != 1) {
                $result['result'] = 107;
                $result['errmsg'] = "请求正在处理中";
                LogApi::logProcess("enrollDiceGame::*************discard***" . json_encode($params));
                break;
            }

            if ($id < 0) {
                $result['result'] = -1; // 游戏id为负数
                $result['errmsg'] = "报名失败~";
                LogApi::logProcess("enrollDiceGame::*************游戏id为负数***".json_encode($result));
                break;
            }

            // 判断游戏状态
            $curgameid = $gameModel->GetCurGameId($id, 0);
            if ($curgameid == -1 || $curgameid != $gameid) {
        	    $result['result'] = 105;
        	    $result['errmsg'] = "报名失败，游戏已开始结束或取消";
        	    LogApi::logProcess("enrollDiceGame***游戏已结束或取消*************".json_encode($result));
                break;
            }
        
            // 判断是否已经报名
            if ($gameModel->IfEntered($id, $uid)) {
        	    $result['result'] = 106;
        	    $result['errmsg'] = "已经报名过啦";
                break;
            }
        
            if (!$gameModel->IfCanEnRollDice($id, $uid)) {
        	    $result['result'] = 103;
        	    $result['errmsg'] = "本次游戏人满了，下次请早";        	 
                break;
            }
            
            // 判断用户是否已经报名其他游戏
            $key = "user:$uid" . ":enroll:game";            
            $oldId = $gameModel->getRedisMaster()->get($key);
            if (!empty($oldId)) {
            	// 如果游戏还未开始或结束
            	if ($gameModel->GetGameStatus($oldId) == 1) {
            		$result['result'] = -1;
            		$result['errmsg'] = "您已报名了其他游戏";
            		LogApi::logProcess("enrollDiceGame******已报名其他游戏******uid:$uid cur_id:$id another_id:$oldId");
            		break;
            	}
            }
        
            $toolModel = new ToolModel();
            $tool = $toolModel->getToolByTid($giftId);
            $userAttrModel = new UserAttributeModel();
            $userAttr = $userAttrModel->getAttrByUid($uid);
            
            LogApi::logProcess('礼物价格：'.$tool['price'].' 用户'.$uid.'信息：'.json_encode($userAttr));

            // 看用户的秀币是否足够买要求的数量的道具
            if ($userAttr['coin_balance'] < $tool['price']) {
                $result['result'] = 101; // 用户秀币不足
                $result['errmsg'] = "金币不足~";
                LogApi::logProcess("enrollDiceGame::*************用户秀币不足1***".json_encode($result));
                break;
            }
        
            //扣除报名用户对应的秀币
            $toolConsumRecordModel = new ToolConsumeRecordModel();
            $success = $toolConsumRecordModel->consumeCoin($uid, $tool['price']);
            if (empty($success) || !$success) {
                $result['result'] = 101; // 用户秀币不足
                $result['errmsg'] = "金币不足~";
                LogApi::logProcess("enrollDiceGame::*************用户秀币不足2***".json_encode($result));
                break;
            }
        
        //扣除礼物（暂时不用）注意：如果扣秀币就不能扣礼物
        /* $toolAccountModel = new ToolAccountModel();
        if(!$toolAccountModel->consume($uid, $giftId, 1)){
            return false;
        } */
        
            $data = $gameModel->enrollDiceGame($id, $singerid, $gameid, $uid, $tool['price']);
            $userCount = 0;
            $money = 0;
            if(!empty($data) && $data['errcode'] == 0){
                $userCount = $data['userCount'];
                $money = $data['money'];
            } else {
            	$result['result'] = -1;
            	$result['errmsg'] = "报名失败~";
            	LogApi::logProcess("enrollDiceGame::data:" . json_encode($data) .  " result" . json_encode($result));
            }
        
//         if($userCount < 5){
//             $money = $tool['price'] * 5;
//         }
        
            $broadcastResult = array(
                'cmd' => 'BEnrollDiceGame',
                'singerid' => $singerid,
                'gameid' => $gameid,
        	    'id' => $id,
                'uid' => $uid,
                'userCount' => $userCount,
                'money' => $money,
        	    'enterLimit' => $gameModel->ROLL_DICE_ENTER_LIMIT
            );
            $return[] = array(
                'broadcast' => 1, //全直播间
                'data' => $broadcastResult
            );
        } while(0);

        // 解锁并清除
        if (0 >= $gameModel->UnLockGameOper($id, $uid, "PEnrollDiceGame")) {
            //$gameModel->DestoryLockGameOper($id, $uid, "PEnrollDiceGame");
        }

        $return[] = array(
            'broadcast' => 0,
            'data' => $result
            );
        /* $return[] = array(
            'broadcast' => 5,
            'data' => array(
                'uid' => (int)$uid,
                'target_type' => 17,//17为参与互动
                'num' => 1,
                'extra_param' =>$gameid
            )
        ); */
        
        LogApi::logProcess('end enrollDiceGame::用户报名摇骰子游戏，data:'.json_encode($return));
        
        return $return; 
    }
    
    //主播发起摇骰子游戏
    public static function launchDiceGame($params)
    {
        LogApi::logProcess("begin launchDiceGame::****************".json_encode($params));
        
        $singerid = intval($params['singerid']);
        $sid = intval($params['sid']);
        $gameid = intval($params['gameid']);
        $giftId = intval($params['giftId']);
        $money = intval($params['money']);
        
        $preheat = intval(isset($params['preheat'])?$params['preheat']:0);
        $launch_uid = intval(isset($params['launch_uid'])?$params['launch_uid']:0);
        $preheat_id = intval(isset($params['preheat_id'])?$params['preheat_id']:0);
        
        $result = array(
            'cmd' => 'RLaunchDiceGame',
            'singerid' => $singerid,
            'result' => 0,
            'launch_uid' => $launch_uid,
        );
        
        if(empty($params['singerid'])
            || empty($params['sid'])
            || empty($params['gameid'])
            || empty($params['giftId'])){
            $result['result'] = -1;
            LogApi::logProcess("launchDiceGame::*************error0***".json_encode($result));
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }

        if (empty($preheat_id) && ($money < 0 || $money > 10000)) {
            $result['result'] = -1;
            $result['errmsg'] = "输入数值已超出限制";
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }

        $model_tool = new ToolModel();
        $tool_inf = $model_tool->getToolByTid($giftId);

        if (!empty($tool_inf) && ($tool_inf['price'] > 2000)) {
            $result['result'] = -1;
            $result['errmsg'] = "你选择的门票太大了，换个小一点的吧";
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        
        //根据sid获得主播id
        $channelLiveModel = new ChannelLiveModel();
        $session = $channelLiveModel->getSessionInfo($sid);
        
        if($singerid != (int)$session['owner']){
            $result['result'] = -1;
            LogApi::logProcess("launchDiceGame::*************error1***".json_encode($result));
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        
        $sendRsp = true;
        
        $gameModel = new GameModel();
        $times = $gameModel->LockGameOper($singerid, $singerid, "PLaunchDiceGame", 10);        
        do {
        	if ($times == 1) {
        		if ($gameModel->GetSingerGameStatus($singerid) == 1) {
                    LogApi::logProcess("launchDiceGame::*************重复发起游戏***" . json_encode($params));
                    $result['result'] = -3;
        			break;
        		}
        	} else {
        		LogApi::logProcess("launchDiceGame::*************Fetch Lock Fail***" . json_encode($params));
        		$sendRsp = false;
        		break;
        	}
        	
        	$game = $gameModel->getGameInfo($gameid);
        	if(empty($game)){
        		$result['result'] = -1;
        		LogApi::logProcess("launchDiceGame::*************error2***".json_encode($result));
        		break;
        	}
        	$result['gameimgurl'] = (string)(empty($game['img_name']) ? "" : $game['img_name']);
        	
        	$userLimitParm = new UserLimitParm();
        	$id = $gameModel->launchDiceGame($userLimitParm,$singerid, $gameid, $money, $giftId, $preheat,$launch_uid,$preheat_id);
        	$result['curr_number'] = $userLimitParm->curr_number;
        	$result['limi_number'] = $userLimitParm->limi_number;
        	if(0 > $id){
        		$result['result'] = $id; // 发起你动我猜游戏失败
        		switch ($id) {
        			case -1:
        				$result['errmsg'] = "互动游戏数据错误";
        				break;
        			case -2:
        				$result['errmsg'] = "今日互动游戏次数达到上限";
        				break;
        			case -3:
        				$result['errmsg'] = "只能同时进行一场游戏";
        				break;
        			case -4:
        				$result['errmsg'] = "有一场游戏在预热";
        				break;
        			case -5:
        				$result['errmsg'] = "无效的用户发起权限";
        				break;
        			default:
        		}
        		LogApi::logProcess("launchDiceGame::*************error3***".json_encode($result));
        		break;
        	}
        	
        	$gameModel->clearPreheat($singerid);
        	
        	$toolModel = new ToolModel();
        	$tool = $toolModel->getToolByTid($giftId);
        	//该场游戏的唯一标识
        	$result['id'] = $id;
        	$result['enterLimit'] = $gameModel->ROLL_DICE_ENTER_LIMIT;
        	$result['extraMoney'] = $gameModel->GetDiceAddSysGold($id);
        	
        	//$totalMoney = $tool['price']*5;
        	$totalMoney = $gameModel->GetDiceInitMoney($id, $tool['price']);
        	
        	$broadcastResult = array(
        			'cmd' => 'BLaunchDiceGame',
        			'id' => $id,//该场游戏的唯一标识
        			'singerid' => $singerid,
        			'gameid' => $gameid,
        			'gameName' => $game['name'],
        			'imgurl' => $game['img_name'],
        			'giftId' => $giftId,
        			'giftName' => $tool['name'],
        			'giftMoney' => (int)$tool['price'],
        			'totalMoney' => $totalMoney,
        			'enterLimit' => $gameModel->ROLL_DICE_ENTER_LIMIT,
        			'extraMoney' => $gameModel->GetDiceAddSysGold($id)
        	);
        	
        	$return[] = array(
        			'broadcast' => 1, //全直播间
        			'data' => $broadcastResult
        	);

        } while (0);
        
        // 解锁并清除
        if (0 >= $gameModel->UnLockGameOper($singerid, $singerid, "PLaunchDiceGame")) {
        	//$gameModel->DestoryLockGameOper($singerid, $singerid, "PLaunchDiceGame");
        }
        
        // 这个回包都发给主播 
        if ($sendRsp) {
        	$return[] = array
        	(
        			'broadcast' => 6,
        			'target_uid' => $singerid,
        			'data' => $result// 发给主播
        	);
        }
                
        LogApi::logProcess("end launchDiceGame::****************".json_encode($return));
        
        return $return;         
    }
    
    /*******************************************以下是你动我猜游戏******************************************/
    //结束你动我猜游戏
    public static function overGuessGame($params)
    {
        LogApi::logProcess("begin overGuessGame::****************params:".json_encode($params));
        $singerId = intval($params['singerId']);
        $id = intval($params['id']);

        $return = array();
        
        $rs = array();
        $rs['cmd'] = 'ROverGuessGame';
        $rs['id'] = $id;
        $rs['ranking'] = array();
        $rs['result'] = 0;
        
        $broadcast = array(
            'cmd' => 'BOverGuessGame',
            'ranking' => array(),
            'result' => 0
        );
        
        $gameModel = new GameModel();
        $times = $gameModel->LockGameOper($id, $singerId, "POverGuessGame", 10);
        $sendRsp = true;
        do 
        {
        	if ($times == 1) {
        		$stat = $gameModel->GetGameStatus($id);
        		if (!$stat || $stat != 2) {
                    $ranking = $gameModel->GetGameRanking($id);
                    $broadcast['ranking'] = $ranking;
                    $rs['ranking'] = $ranking;
                    $return[] = array(
                        'broadcast' => 1,
                        'data' => $broadcast
                    );        
        			LogApi::logProcess("GameApi::overGuessGame Fetch Lock but game already finished:" . json_encode($params) . " stat:" . $stat);
        			break;
        		}
        	} else {
        		LogApi::logProcess("GameApi::overGuessGame Fetch Lock Fail:" . json_encode($params));
        		$sendRsp = false;
        		break;
        	}
        	
        	$gameModel->ClearUserGameRelById($id);
        	
            $ranking = $gameModel->overGuessGame($singerId, $id, &$return);
            if(empty($ranking))
            {
                $rs['result'] = -1; // 结束失败
                $broadcast['result'] = -1;
                //break;
            }elseif (!empty($ranking['uids'])){
                foreach ($ranking['uids'] as $item)
                {
                    $return[] = array(
                        'broadcast' => 5,
                        'data' => array(
                        'uid' => (int)$item,
                        'target_type' => 17,//17为参与互动
                        'num' => 1,
                        'extra_param' =>4
                        )
                    );
                }
            }
            
            $rs['ranking'] = $ranking;
            
            $broadcast['ranking'] = $ranking;
            $gameModel->SaveGameRanking($id, $ranking);            
            /* if(empty($ranking['items']) || 0 == count($ranking['items']))
            {
                break;
            } */
            if ($rs['result'] == 0) {
                $model_anchor_pt = new anchor_points_model();
                $model_anchor_pt->on_anchor_finish_game_guess($singerId);
            	$gameModel->ClearGameStatus($id);
            }
            
            $return[] = array(
                'broadcast' => 1,
                'data' => $broadcast
            );
            
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$singerId,
                    'target_type' => 16,//16为发起互动
                    'num' => 1,
                    'extra_param' =>4
                )
            );
        }while (FALSE);
        
        if ($gameModel->UnLockGameOper($id, $singerId, "ROverGuessGame") <= 0) {
        	//$gameModel->DestoryLockGameOper($id, $singerId, "ROverGuessGame");
        }

        if ($sendRsp) {
        	$return[] = array
        	(
        			'broadcast' => 0,
        			'data' => $rs
        	);
        }
        
        LogApi::logProcess("end overGuessGame::****************".json_encode($return));
        return $return;
    }
    
    //主播表演下一题
    public static function actNextQuestion($params)
    {
        LogApi::logProcess("begin actNextQuestion::****************params:".json_encode($params));
        LogApi::logProcess("actNextQuestion:1");
        $id = intval($params['id']);
        $question_seq = intval($params['question_seq']);
        
        LogApi::logProcess("actNextQuestion:2");
        
        $gameModel = new GameModel();
        
        LogApi::logProcess("actNextQuestion:3");
        $gameModel->actNextQuestion($id, $question_seq);
        LogApi::logProcess("actNextQuestion:4");
        
        $result = array(
            'cmd' => 'RActNextQuestion',
            'id' => $id,
            'question_seq' => $question_seq,
            'result' => 0
        );
        
        $broadcastResult = array(
            'cmd' => 'BActNextQuestion',
            'id' => $id,//该场游戏的唯一标识
            'question_seq' => $question_seq
        );
        
        $return[] = array(
            'broadcast' => 0,
            'data' => $result
        );
        $return[] = array(
            'broadcast' => 1, //全直播间
            'data' => $broadcastResult
        );
        
        LogApi::logProcess("end actNextQuestion::****************return:".json_encode($return));
        
        return $return; 
    }
    
    //用户开始做猜题游戏
    public static function doGuessGame($params)
    {
        LogApi::logProcess("end GameModel::doGuessGame****************params".json_encode($params));
        $uid = intval($params['uid']);
        $id = intval($params['id']);
        $question_seq = intval($params['question_seq']);
        $user_answer = $params['user_answer'];
        $question_answer = $params['question_answer'];
        $cost_time = intval($params['cost_time']);
        
        $result = array(
            'cmd' => 'RDoGuessGame',
            'uid' => $uid,
        	'id' => $id,
            'score' => 0,
            'isFinish' => 0,
            'result' => 0
        );
        
        $gameModel = new GameModel();
        // 判断游戏是否还在进行
        $curgameid = $gameModel->GetCurGameId($id, 1);
        if ($curgameid == -1 || $curgameid != GameModel::$GAME_ID_GUESS) {
        	$result['result'] = 105;
        	$result['errmsg'] = "游戏已结束或取消";
        	LogApi::logProcess("begin GameApi::doDiceGame***游戏已结束或取消*************".json_encode($result));
        
        	$return[] = array
        	(
        			'broadcast' => 0,
        			'data' => $result,
        	);
        	return $return;
        }
        
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        
        $broadcast = array(
            'cmd' => 'BDoGuessGame',
            'uid' => $uid,
        	'id' => $id,
            'nick' => $user['nick'],
            'question_seq' => $question_seq,
            'user_answer' => $user_answer,
            'score' => 0,
            'isFinish' => 0,
            'result' => 0
        );
        
        $score = $gameModel->saveAnswer($id, $uid, $question_seq, $user_answer, $question_answer, $cost_time);
        $result['score'] = $score;
        // the $broadcast package score might assign the same value.
        $broadcast['score'] = $score;
        if(empty($score) || !$score){
            $result['result'] = -1;
        }
        
        $flag = $gameModel->isFinishGuessGameItem($id, $question_seq, $uid);
        if(!empty($flag) && ($gameModel->getNextQuestionSeq($id) == $question_seq)){
            $result['isFinish'] = 1;
            $broadcast['isFinish'] = 1;
        }
        
        $return[] = array(
            'broadcast' => 0,
            'data' => $result
        );
        
        $return[] = array(
            'broadcast' => 1,
            'data' => $broadcast
        );
        
        LogApi::logProcess("end GameModel::doGuessGame::****************return:".json_encode($return));
        return $return;
    }
    //主播开始你动我猜游戏
    public static function startGuessGame($params)
    {
        LogApi::logProcess("begin GameApi::startGuessGame****************params:".json_encode($params));
        $id = intval($params['id']);
        $singerid = intval($params['singerid']);
        $gameid = intval($params['gameid']);
        $money = intval($params['money']);
        
        $result = array(
            'cmd' => 'RStartGuessGame',
            'id' => $id,
            'singerid' => $singerid,
            'result' => 0
        );

        $man_floor = 0;
        $gameModel = new GameModel();

        $broadcastResult = array(
            'cmd' => 'BStartGuessGame',
            'id' => $id,
            'singerid' => $singerid,
            'gameid' => $gameid,
            'money' => $money,
            'timeLimit' => $gameModel->GUESS_TIME_LIMIT
        );
        
        $times = $gameModel->LockGameOper($id, $singerid, "PStartGuessGame", 10);
        $sendRsp = true;
        
        do {
        	if ($times == 1) {
        		$stat = $gameModel->GetGameStatus($id);
        		if (!$stat || $stat != 1) {
                    $broadcastResult['quetions'] = $gameModel->GetGuessGameQues($id);
        			LogApi::logProcess("GameApi::startGuessGame 重复开始游戏1:" .json_encode($params) . " stat:" . $stat);
        			break;
        		}
        	} else {
        		LogApi::logProcess("GameApi::startGuessGame Fetch Lock Fail:" . json_encode($params));
        		$sendRsp = false;
        		break;
        	}
        	
        	$flag = $gameModel->startGame($id, $singerid, $gameid, $money, &$man_floor);
        	if ($flag != 0) {
        		$result['result'] = $flag;
        		switch ($flag) {
        			case -1:
        				$result['errmsg'] = "开始游戏失败";
        				break;
        			case 1:
        				$result['man_floor'] = $man_floor;
        				$result['errmsg'] = "参与游戏人数少于" . $man_floor . "，不能开始游戏";
        				break;
        			default:
        		}
        		break;
        	}
        	
        	$quetions = $gameModel->getGuessQuetions($singerid, $id);
            $result['quetions'] = $quetions;
            $gameModel->SaveGuessGameQues($id, $quetions);
        	$broadcastResult['quetions'] = $quetions;
        } while (0);

        if ($gameModel->UnLockGameOper($id, $singerid, "PStartGuessGame") <= 0) {
        	//$gameModel->DestoryLockGameOper($id, $singerid, "PStartGuessGame");
        }
        
        if ($sendRsp) {
        	$return[] = array(
        			'broadcast' => 0,
        			'data' => $result
            );
            
            if ($result['result'] == 0) {
                $return[] = array(
                        'broadcast' => 1, //全直播间
                        'data' => $broadcastResult
                );
            }
        }
        
        LogApi::logProcess("end startGame::****************".json_encode($return));
        
        return $return; 
    }
    
    //用户报名参加游戏
    public static function enrollGame($params)
    {
        LogApi::logProcess("begin GameApi::enrollGame****************params".json_encode($params));
        $id = intval($params['id']);
        $uid = intval($params['uid']);
        $singerid = intval($params['singerid']);
        $gameid = intval($params['gameid']);
        
        $result = array(
            'cmd' => 'REnrollGame',
            'uid' => $uid,
        	'id' => $id,
            'result' => 0
        );
        
        $gameModel = new GameModel();
        $times = $gameModel->LockGameOper($id, $uid, "PEnrollGame", 10);
        
        do {
            if ($times != 1) {
                $result['result'] = 107;
                $result['errmsg'] = "请求正在处理中";
                LogApi::logProcess("enrollGame***discard*************" . json_encode($params));
                break;
            }

            // 判断游戏状态
            $curgameid = $gameModel->GetCurGameId($id, 0);
            if ($curgameid == -1 || $curgameid != $gameid) {
        	    $result['result'] = 105;
        	    $result['errmsg'] = "报名失败，游戏已开始结束或取消";
        	    LogApi::logProcess("enrollGame***游戏已结束或取消*************".json_encode($result));
                break;
            }
        
            // 判断是否已经报名
            if ($gameModel->IfEntered($id, $uid)) {
        	    $result['result'] = 106;
        	    $result['errmsg'] = "已经报名过啦";
                break;
            }
            
            // 判断用户是否已经报名其他游戏
            $key = "user:$uid" . ":enroll:game";            
            $oldId = $gameModel->getRedisMaster()->get($key);
            if (!empty($oldId)) {
            	// 如果游戏还未开始或结束
            	if ($gameModel->GetGameStatus($oldId) == 1) {
            		$result['result'] = -1;
            		$result['errmsg'] = "您已报名了其他游戏";
            		LogApi::logProcess("enrollGame******已报名其他游戏******uid:$uid cur_id:$id another_id:$oldId");
            		break;
            	}
            }
        
            $num = $gameModel->enrollGame($id, $singerid, $gameid, $uid);
        
            $broadcastResult = array(
                'cmd' => 'BEnrollGame',
                'singerid' => $singerid,
                'gameid' => $gameid,
        	    'id' => $id,
                'uid' => $uid,
                'userCount' => $num
            );
        
            $return[] = array(
                'broadcast' => 1, //全直播间
                'data' => $broadcastResult
            );
        } while(0);
     	
        // 解锁并清除
        if (0 >= $gameModel->UnLockGameOper($id, $uid, "PEnrollDiceGame")) {
            //$gameModel->DestoryLockGameOper($id, $uid, "PEnrollDiceGame");
        }

        $return[] = array(
            'broadcast' => 0,
            'data' => $result
            );

        /* $return[] = array(
            'broadcast' => 5,
            'data' => array(
                'uid' => (int)$uid,
                'target_type' => 17,//17为参与互动
                'num' => 1,
                'extra_param' =>$gameid
            )
        ); */
        
        LogApi::logProcess('end GameApi::enrollGame用户报名你演我猜游戏，return:'.json_encode($return));
        
        return $return; 
    }
    
    //主播关闭你动我猜游戏
    public static function cancelGame($params)
    {
        LogApi::logProcess('begin GameApi::cancelGame:*****主播关闭你动我猜游戏*******params:'.json_encode($params));
        $id = intval($params['id']);
        $singerid = intval($params['singerid']);

        $result = array(
            'cmd' => 'RCancelGame',
            'singerid' => $singerid,
            'result' => 0
        );
        
        $gameModel = new GameModel();
        
        $times = $gameModel->LockGameOper($id, $singerid, "PCancelGame", 10);
        $sendRsp = true;
        do {
        	if ($times == 1) {
        		$stat = $gameModel->GetGameStatus($id);
        		if (!$stat || $stat != 1) {
        			LogApi::logProcess("GameApi::cancelGame:****重复取消游戏:" . json_encode($params). " stat:" . $stat);
        			break;
        		}
        	} else {
        		LogApi::logProcess("GameApi::cancelGame:****Fetch Lock Fail:" . json_encode($params));
        		$sendRsp = false;
        		break;
        	}
        	
        	$gameModel->ClearUserGameRelById($id);
        	
        	$flag = $gameModel->cancelGuessGame($singerid, $id);
        	if(empty($flag)){
        		$result['result'] = -1;
        		LogApi::logProcess('GameApi::cancelGame:*****主播关闭你动我猜游戏失败*******result:'.json_encode($result));
        		break;
        	}
        	
        	if ($result['result'] == 0) {
        		$gameModel->ClearGameStatus($id);
        	}
        } while (0);
        
        if ($gameModel->UnLockGameOper($id, $singerid, "PCancelGame") <= 0) {
        	//$gameModel->DestoryLockGameOper($id, $singerid, "PCancelGame");
        }
        
       	if ($sendRsp) {
       		$return[] = array(
       				'broadcast' => 0,
       				'data' => $result
            );
            
            if ($result['result'] == 0) {
                $broadcastResult = array(
        			'cmd' => 'BCancelGame',
        			'id' => $id,//该场游戏的唯一标识
        			'singerid' => $singerid
        	    );
        	
                $return[] = array(
                        'broadcast' => 1, //全直播间
                        'data' => $broadcastResult
                );
            }
       	}
        
        LogApi::logProcess('end GameApi::cancelGame:*****主播关闭你动我猜游戏*******return:'.json_encode($return));
        
        return $return;
    }
    
    //主播发起你动我猜游戏
    public static function launchGuessGame($params)
    {
        LogApi::logProcess("begin GameApi::launchGuessGame****************".json_encode($params));
        $singerid = intval($params['singerid']);
        $sid = intval($params['sid']);
        $gameid = intval($params['gameid']);
        $money = intval($params['money']);
        //0：非预热 1：预热
        $preheat = intval(isset($params['preheat'])?$params['preheat']:0);
        $launch_uid = intval(isset($params['launch_uid'])?$params['launch_uid']:0);
        $preheat_id = intval(isset($params['preheat_id'])?$params['preheat_id']:0);
        
        $result = array(
            'cmd' => 'RLaunchGuessGame',
            'singerid' => $singerid,
            'result' => 0,
            'launch_uid' => $launch_uid,
        );
        
        if(empty($params['singerid'])
            || empty($params['sid'])
            || empty($params['gameid'])){
                $result['result'] = -1;
                LogApi::logProcess("launchGuessGame::*************error0***".json_encode($result));
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
        }
        
        if($money < 100 || $money > 10000){
                $result['result'] = -1;
                $result['errmsg'] = "输入数值已超出限制";
                LogApi::logProcess("launchGuessGame::*************error1***".json_encode($result));
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
        }
        
        //根据sid获得主播id
        $channelLiveModel = new ChannelLiveModel();
        $session = $channelLiveModel->getSessionInfo($sid);
        
        if($singerid != (int)$session['owner']){
            $result['result'] = -1;
            $result['errmsg'] = "互动游戏错误";
            LogApi::logProcess("launchGuessGame::*************error2***".json_encode($result));
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        
        $gameModel = new GameModel();
        $times = $gameModel->LockGameOper($singerid, $singerid, "PLaunchGuessGame", 10);
        $sendRsp = true;
        do {
        	if ($times == 1) {
        		 if ($gameModel->GetSingerGameStatus($singerid) == 1) {
                     LogApi::logProcess("launchGuessGame::*************重复发起游戏***" . json_encode($params));
                     $result['result'] = -3;
        		 	break;
        		 }
        	} else {
        		LogApi::logProcess("launchGuessGame::******* Fetch Lock Fail:" . json_encode($params));
        		$sendRsp = false;
        		break;
        	}
        	
        	$game = $gameModel->getGameInfo($gameid);
        	if(empty($game)){
        		$result['result'] = -1; // 发起你动我猜游戏失败
        		$result['errmsg'] = "互动游戏错误：获取游戏信息失败";
        		break;
        	}
        	$result['gameimgurl'] = (string)(empty($game['img_name']) ? "" : $game['img_name']);
        	
        	$userLimitParm = new UserLimitParm();
        	$id = $gameModel->launchGame($userLimitParm,$singerid, $gameid, $money, $preheat,$launch_uid,$preheat_id);
        	$result['curr_number'] = $userLimitParm->curr_number;
        	$result['limi_number'] = $userLimitParm->limi_number;
        	if(0 > $id){
        		$result['result'] = $id; // 发起你动我猜游戏失败
        		switch ($id) {
        			case -1:
        				$result['errmsg'] = "互动游戏数据错误：发起游戏失败";
        				break;
        			case -2:
        				$result['errmsg'] = "今日互动游戏次数达到上限";
        				break;
        			case -3:
        				$result['errmsg'] = "只能同时进行一场游戏";
        				break;
        			case -4:
        				$result['errmsg'] = "有一场游戏在预热";
        				break;
        			case -5:
        				$result['errmsg'] = "无效的用户发起权限";
        				break;
        			default:
        		}
        	
        		LogApi::logProcess("GameApi::launchGuessGame*****获得本场游戏id失败***********".json_encode($result));
        		break;
        	}
        	//该场游戏的唯一标识
        	$result['id'] = $id;
        	
        	$gameModel->clearPreheat($singerid);
        	
        	$money = $gameModel->GetGuessInitMoney($id);
        	
        	$broadcastResult = array(
        			'cmd' => 'BLaunchGuessGame',
        			'id' => $id,//该场游戏的唯一标识
        			'singerid' => $singerid,
        			'gameid' => $gameid,
        			'gameName' => $game['name'],
        			'imgurl' => $game['img_name'],
        			'money' => $money
        	);
        	$return[] = array(
        			'broadcast' => 1, //全直播间
        			'data' => $broadcastResult
        	);
        } while (0);

        // 解锁并清除
        if (0 >= $gameModel->UnLockGameOper($singerid, $singerid, "PLaunchGuessGame")) {
        	//$gameModel->DestoryLockGameOper($singerid, $singerid, "PLaunchGuessGame");
        }
        
        if ($sendRsp) {
        	// 这个回包都发给主播
        	$return[] = array
        	(
        			'broadcast' => 6,
        			'target_uid' => $singerid,
        			'data' => $result// 发给播主
        	);        	
        }        
        LogApi::logProcess("end GameApi::launchGuessGame****************".json_encode($return));
        
        return $return; 
    }
/*******************************************结束你动我猜游戏******************************************/
    
    public static function fcGetChess($params)
    {
        $returnResult = array(
            'cmd' => 'RFcGetChess',
            'result' => 0
        );
        $uid = $params['uid'];
        $activity = new ActivityModel();
        $returnResult['dailyPacket'] = $activity->getDailyPacket($uid);
        $returnResult['hourlyPacket'] = $activity->getHourlyPacket($uid);
        $fcModel = new FlightChessModel();
        $returnResult['chess'] = $fcModel->getChess();
        $returnResult['rewardBox'] = $fcModel->getRewardBox();
        $returnResult['info'] = $fcModel->getInfoByUid($uid);
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function fcThrowDice($params)
    {
        $game = FlightChessModel::$gameId;
        $type = FlightChessModel::$deductGamePoint;
        $cost = FlightChessModel::$cost;
        $returnResult = array(
            'cmd' => 'RFcThrowDice',
            'result' => 0
        );
        $uid = $params['uid'];
        $userAttributeModel = new UserAttributeModel();
        $deduction = $userAttributeModel->deductGamePoint($uid, $cost, $type, $game);
        if ($deduction) {
            $fcModel = new FlightChessModel();
            $result = $fcModel->throwDice($uid);
            $returnResult['info'] = $fcModel->getInfoByUid($uid);
            if (!empty($result['boxName'])) {
                $return[] = array(
                    'broadcast' => 2,
                    'data' => array(
                        'cmd' => 'BBroadcast',
                        'fc' => array(
                            'sender' => $uid,
                            'senderNick' => $params['sender'],
                            'text' => $result['boxName']
                        )
                    )
                );
                unset($result['boxName']);
            }
            $returnResult['info'] += $result;
        } else {
            $returnResult['result'] = 161;
        }

        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function fcBuyGamePoint($params)
    {
        $returnResult = array(
            'cmd' => 'RFcBuyGamePoint',
            'result' => 0
        );
        $uid = (int)$params['uid'];
        $point = (int)$params['point'];
        $fcModel = new FlightChessModel();
        $result = $fcModel->buyGamePoint($uid, $point);
        if ($result) {
            $userAttributeModel = new UserAttributeModel();
            $userAttribute = $userAttributeModel->getAttrByUid($uid);
            $returnResult['coinBalance'] = $userAttribute['coin_balance'];
            $returnResult['gamePoint'] = $userAttribute['game_point'];
        } else {
            $returnResult['result'] = 162;
        }
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function getLuckyShakeTable($withProbability = false)
    {
        $result = array(
            'first' => array(
                'coin' => 888
            ),
            'second' => array(
                'coin' => 88
            ),
            'third' => array(
                'coin' => 8
            ),
            'fourth' => array(
                'coin' => 5
            ),
        );
        if ($withProbability) {
            $result['shake'] = array(
                'begin' => 1,
                'end' => 300000
            );
            $result['fourth']['set'] = array(
                'begin' => 40001,
                'end' => 50000
            );
            $result['third']['set'] = array(
                'begin' => 1,
                'end' => 20000
            );
            $result['second']['set'] = array(
                'begin' => 30001,
                'end' => 31000
            );
            $result['first']['set'] = array(
                'begin' => 50001,
                'end' => 50050
            );
        }
        return $result;

    }

    private function inLuckySet($num,$set){
        if($num >= $set['begin'] && ($num <= $set['end'])){
            return true;
        }else{
            return false;
        }
    }

    private function luckyShakeDuring($uid){
        return 5;
        $luckyShakeRewardPoolKey = 'annual_lucky_shake_reward_' . date('Ymd');
        $luckyShakeTable = self::getLuckyShakeTable(true);
        $num = rand($luckyShakeTable['shake']['begin'],$luckyShakeTable['shake']['end']);
        if(self::inLuckySet($num,$luckyShakeTable['third']['set'])){
            $field = 'third';
        }elseif(self::inLuckySet($num,$luckyShakeTable['second']['set'])){
            $field = 'second';
        }elseif(self::inLuckySet($num,$luckyShakeTable['first']['set'])){
            $field = 'first';
        }elseif(self::inLuckySet($num,$luckyShakeTable['fourth']['set'])){
            $field = 'fourth';
        }else{
            return 0;
        }
        if(!empty($field)){
            try{
                $redisMaster = ServiceFactory::getService('redis','master');
                $canReward = $redisMaster->hincrby($luckyShakeRewardPoolKey,$field,-1);
                if($canReward >= 0){
                    $now = time();
                    $coinResult = $luckyShakeTable[$field]['coin'];
                    $logMessage = "$uid,$coinResult,$now";
                    Logger::logToDataFile('annual_lucky_shake.log',$logMessage);
                    return $luckyShakeTable[$field]['coin'];
                }
            }catch(Exception $e){
                return 106;
            }

        }
        return 0;
    }

    public static function luckyShake($params)
    {
        $returnResult = array(
            'cmd' => 'RLuckyShake',
            'result' => 0
        );
        if (time() < strtotime("20131218 18:18:18")) {
            $returnResult['result'] = 163; // 活動尚未開始
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $uid = $params['uid'];
        $userAttrModel = new UserAttributeModel();
        if ($userAttrModel->statusIncrease($uid, 'lucky_shake_lock') > 1) {
            $userAttrModel->statusIncrease($uid, 'lucky_shake_lock', -1);
            $returnResult['result'] = 125; // 請勿使用外掛軟體刷機
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $point = 50;
        $deduction = $userAttrModel->deductGamePoint($uid, $point, 2, 2);
        if ($deduction) {
            $num = self::luckyShakeDuring($uid);
            if($num == 106){
                $userAttrModel->statusIncrease($uid, 'lucky_shake_lock', -1);
                $returnResult['result'] = $num;
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $returnResult
                    )
                );
            }
            if ($num > 0) {
                $broadcastResult = array(
                    'cmd' => 'BLuckyShake',
                    'sender' => $uid,
                    'senderNick' => $params['sender'],
                    'num' => $num
                );
                $return[] = array(
                    'broadcast' => 2,
                    'data' => $broadcastResult
                );
                $recordParam = array(
                    'source' => 6,
                    'coin' => $num,
                    'point' => 0,
                );
                if($num == 5){
                    $activityModel = new ActivityModel();
                    $activityModel->sendVipReward($uid,$num,5);
                }else{
                    $userAttrModel->addCoin($uid, $num, $recordParam);
                    if($num == 888){
                        $activityModel = new ActivityModel();
                        $activityModel->sendBadgeReward($uid,197);
                    }
                }
            }
            $returnResult['num'] = $num;
            $gamePoint = $userAttrModel->getAttrByUid($uid, 'game_point');
            $returnResult['count'] = floor($gamePoint / $point);
        } else {
            $returnResult['result'] = 161;
        }
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        $userAttrModel->statusIncrease($uid, 'lucky_shake_lock', -1);
        return $return;
    }

    // 用户递交发起互动游戏申请请求递交
    public static function OnPUserLaunchGame($params)
    {        
        LogApi::logProcess("OnPUserLaunchGame rq:".json_encode($params));
        //
        $singerid = $params['singerid'];
        $launchid = $params['launchid'];
        $sid = $params['sid'];
        $gameid = $params['gameid'];
        
        $uid = $launchid;
        
        $return = array();
        
        $rs = array();
        $rs['cmd'] = 'RUserLaunchGame';
        $rs['code'] = 0;
        $rs['desc'] = '';
        $rs['singerid'] = $singerid;
        $rs['launchid'] = $launchid;
        $rs['sid'] = $sid;
        $rs['gameid'] = $gameid;
        /////////////////////////////////////////////
        do
        {
            $timecode_now = time();
            $gulm = new GameUserLaunchModel();
            $can_submit = $gulm->CheckingUserLaunchSubmit($sid, $launchid, $gameid, $timecode_now);
            if (0 != $can_submit)
            {
                LogApi::logProcess("OnPUserLaunchGame uid:".$uid." can_submit:".$can_submit);
                // 3007001(1)已经递交了本类游戏申请
                $rs['code'] = 3007001;
                $rs['desc'] = '已经递交了本类游戏申请';
                break;
            }            
            $mysql_err = $gulm->CheckingItemMysqlDB($launchid, $gameid, GameUserLaunchModel::$SINGER_USERLAUNCH_SELECT_COST_ITEM_NUM);
            if (0 != $mysql_err)
            {
                LogApi::logProcess("OnPUserLaunchGame uid:".$uid." mysql_err:".$mysql_err);
                // 2007001(1)互动道具数量不够
                $rs['code'] = 2007001;
                $rs['desc'] = '互动道具数量不够';
                break;
            }
            $launch = array();
            $gulm->UserLaunchSubmit($singerid,$sid, $launchid, $gameid, $timecode_now, &$launch);
            LogApi::logProcess("OnPUserLaunchGame json_encode(launch):".json_encode($launch));
            //
            $nt = array();
            $nt['cmd'] = 'BUserLaunchGame';
            $nt['singerid'] = $singerid;
            $nt['sid'] = $sid;
            $nt['launch'] = $launch;
            
            $return[] = array
            (
                'broadcast' => 6,
                'target_uid' => $singerid,
                'data' => $nt// 发给播主
            );
            LogApi::logProcess("OnPUserLaunchGame uid:".$uid." nt:".json_encode($nt));
        }while(FALSE);
        /////////////////////////////////////////////
        $return[] = array
        (
            'broadcast' => 0,
            'data' => $rs// 发rs包
        );        
        LogApi::logProcess("OnPUserLaunchGame uid:".$uid." rs:".json_encode($rs));
        LogApi::logProcess("OnPUserLaunchGame uid:".$uid." return:".json_encode($return));
        return $return;        
    }
    // 播主同意某申请请求
    public static function OnPSingerApplyLaunch($params)
    {      
        LogApi::logProcess("OnPSingerApplyLaunch rq:".json_encode($params));
        //
        $singerid = $params['singerid'];
        $launchid = $params['launchid'];
        $sid = $params['sid'];        
        $gameid = $params['gameid'];
        
        $uid = $launchid;
        
        $return = array();
        
        $rs = array();
        $rs['cmd'] = 'RSingerApplyLaunch';
        $rs['code'] = 0;
        $rs['desc'] = '';
        $rs['singerid'] = $singerid;
        $rs['launchid'] = $launchid;
        $rs['sid'] = $sid;   
        $rs['gameid'] = $gameid;
        $rs['select_timeout'] = GameUserLaunchModel::$SINGER_USERLAUNCH_SELECT_TIMEOUT;
        /////////////////////////////////////////////
        do
        {
            $timecode_now = time();
            
            $spm = new SysParametersModel();
            $preheat_time = $spm->GetSysParameters(GameApi::$SYS_PARM_ID_PREHEAT_TIME, "parm1");
            
            $gulm = new GameUserLaunchModel();
            $can_select = $gulm->CheckingSingerSelectSubmit($sid, $timecode_now);
            if (0 != $can_select)
            {
                LogApi::logProcess("OnPSingerApplyLaunch uid:".$uid." can_select:".$can_select);
                // 3007002(2)正在等发起者确认
                $rs['code'] = 3007002;
                $rs['desc'] = '正在等发起者确认';
                break;
            }
            $launch = $gulm->GetSingerUserlaunchGameLaunch($sid,$launchid,$gameid);
            if (NULL == $launch)
            {
                LogApi::logProcess("OnPSingerApplyLaunch uid:".$uid." launch:".$launch);
                // 3007003(3)无效的用户发起记录
                $rs['code'] = 3007003;
                $rs['desc'] = '无效的用户发起记录';
                break;
            }
            $cost_number = GameUserLaunchModel::$SINGER_USERLAUNCH_SELECT_COST_ITEM_NUM;
            $mysql_err = $gulm->UpdateItemMysqlDB($launchid, $gameid, -$cost_number);
            if (0 != $mysql_err)
            {
                LogApi::logProcess("OnPSingerApplyLaunch uid:".$uid." mysql_err:".$mysql_err);
                // 2007002(2)扣除互动道具失败
                $rs['code'] = 2007002;
                $rs['desc'] = '扣除互动道具失败';
                break;
            }
            $gulm->SingerSelectSubmit($sid,$launchid,$gameid,$launch->timecode,$timecode_now);
            
            $nt = array();
            $nt['cmd'] = 'BSingerApplyLaunch';
            $nt['singerid'] = $singerid;
            $nt['sid'] = $sid;
            $nt['preheat_time'] = $preheat_time;
            $nt['launch'] = $launch;
            
            $return[] = array
            (
                'broadcast' => 6,
                'target_uid' => $launchid,
                'data' => $nt// 发给发起者
            );
            LogApi::logProcess("OnPSingerApplyLaunch uid:".$uid." nt:".json_encode($nt));
        }while(FALSE);
        /////////////////////////////////////////////
        $return[] = array
        (
            'broadcast' => 0,
            'data' => $rs
        );
        LogApi::logProcess("OnPSingerApplyLaunch uid:".$uid." rs:".json_encode($rs));
        LogApi::logProcess("OnPSingerApplyLaunch uid:".$uid." return:".json_encode($return));
        return $return;        
    }
    
    public static  function clean_game_info_rq($params)
    {
    	LogApi::logProcess("GameApi:clean_game_info_rq rq:".json_encode($params));
    	
    	$uid = isset($params['uid'])?$params['uid']:0;
    	$sid = isset($params['sid'])?$params['sid']:0;
    	$game_id = isset($params['game_id'])?$params['game_id']:0;
    	
    	$result = array(
    			'cmd' => 'clean_game_info_rs',
    			'uid' => intval($uid),
    			'sid' => intval($sid),
    			'result' => 0,
    			'game_id' => $game_id
    	);
    	
    	if (empty($uid) || empty($sid)) {
    		$result['result'] = 201;
    		break;
    	}
    	
    	$return = array();
    	GameApi::dealExceptionOver($uid, &$return);
    	$return[] = array(
    			'broadcast' => 0,
    			'data' => $result
    	);
    	LogApi::logProcess("GameApi:clean_game_info_rq rs:" . json_encode($return));
    	return $return;
    }

	public static function QueryDiceSystemGold($params)
	{
		LogApi::logProcess("GameApi:QueryDiceSystemGold rq:".json_encode($params));
    	
    	$uid = isset($params['uid'])?$params['uid']:0;
    	$sid = isset($params['sid'])?$params['sid']:0;

		$result = array(
    			'cmd' => 'RQueryDiceSystemGold',
    			'uid' => intval($uid),
    			'result' => 0,
    			'type' => 1,
    	);

		$num = 0;
		$sysGold = 0;
		$gameModel = new GameModel();
    	if(!$gameModel->CheckSidDiceCanAddSysGold($sid, $uid, $num, $sysGold) || 0 != $num)
    	{
			$result["hasSysGold"] = 0;
			$result["sysGoldCount"] = 0;
    	}
		else
		{
			$result["hasSysGold"] = 1;
			$result["sysGoldCount"] = $sysGold;
		}

		LogApi::logProcess("GameApi:QueryDiceSystemGold uid: $uid  sid: $sid  num: " . $num."  sysGold:".$sysGold);

		$return = array();
		$return[] = array(
    			'broadcast' => 0,
    			'data' => $result
    	);

		LogApi::logProcess("GameApi:QueryDiceSystemGold rs:" . json_encode($return));
    	return $return;
	}

    
}