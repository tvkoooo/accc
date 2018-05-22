<?php

class UserLimitParm
{
    public $curr_number = 0;
    public $limi_number = 0;
}
class GameModel extends ModelBase
{
    public static $GAME_USER_MOD_NUMBER = 1024;
    public static $GAME_STATUS_RESET_TIME = 900;// 游戏状态重置最大时间 15 * 60
    
    /******游戏类型*******/
    //你动我猜游戏
    const TYPE_GUESS = 1;
    //摇骰子游戏
    const TYPE_DICE = 1;
    
    public static $USER_ACTIVITY_LIMIT_TTL = 259200;// 3 * 24 * 60 * 60
    // 4	你演我猜
    // 5	摇骰子
    // 6	保卫主播
    public $USER_ACTIVITY_LIMIT_NUMBER = array(
        '4' => 10,
        '5' => 10,
        '6' => 10,
    );// default is 10.          96.
    public $USER_ACTIVITY_INTERACTIVE_LIMIT_NUMBER = 30;// default 30.
    
    public static $USER_ACTIVITY_INTERACTIVE_LIMIT_MOD_NUMBER = 1024;
    public static $USER_ACTIVITY_INTERACTIVE_LIMIT_TTL = 259200;// 3 * 24 * 60 * 60
    
    public static $GAME_ID_GUESS = 4;
    public static $GAME_ID_DICE = 5;
    public static $GAME_ID_DEFEND = 6;
   
    // 摇色子游戏报名用户数上限
    public $ROLL_DICE_ENTER_LIMIT = 15;
    
    // 用户摇色子次数上限
    public $ROLL_DICE_COUNT_LIMIT = 10;
    
    // 答题时间
    public $GUESS_TIME_LIMIT = 20;
   
    
    public function __construct ()
    {
        parent::__construct();
    }
    public static function HashUserActivityLimitKey($uid,$timecode)
    {
        $timeindex = date('Y:m:d',$timecode);
        return "activity:user:limit:$timeindex:$uid";
    }
    public static function HashUserInteractiveActivityLimitKey($uid,$timecode)
    {
        $timeindex = date('Y:m:d',$timecode);
        $mod = $uid % GameModel::$USER_ACTIVITY_INTERACTIVE_LIMIT_MOD_NUMBER;
        return "activity:interactive:limit:$timeindex:$mod";
    }
    public function GetUserActivityLimitNumber($singerid,$gameid)
    {
        $value = 10;
        $value = $this->USER_ACTIVITY_LIMIT_NUMBER[$gameid];
        $value = (!$value || 0 == $value ) ? 10 : $value;
        return $value;
    }
    public function InsertUserActivityInteractive($singerid,$gameid,$uid)
    {        
        $timecode = time();
        $sql = "insert rcec_record.activity_interactive_award(zid,game_id,uid,time) values($singerid,$gameid,$uid,unix_timestamp())";
        $rows = $this->getDbMain()->query($sql);
        if(!$rows)
        {
            LogApi::logProcess("InsertUserActivityInteractive::*********sql error!!!*******sql:$sql");
        }
        LogApi::logProcess("InsertUserActivityInteractive singerid:".$singerid." gameid:".$gameid." uid:".$uid);
    }    
    public function InsertUserActivityInteractiveCheck($singerid,$gameid,$uid)
    {
        $rt = -1;
        $timecode = time();
        $key = GameModel::HashUserInteractiveActivityLimitKey($uid, $timecode);
        $curr_number = $this->getRedisMaster()->hGet($key, $uid);
        if (empty($curr_number)){$curr_number = 0;}
        $limi_number = $this->USER_ACTIVITY_INTERACTIVE_LIMIT_NUMBER;
        LogApi::logProcess("GameModel::InsertUserActivityInteractiveCheck: uid:$uid curr_number:$curr_number limi_number:$limi_number");
        if ($curr_number < $limi_number)
        {
            $this->getRedisMaster()->hIncrBy($key, $uid, 1);
            $this->getRedisMaster()->expire($key,GameModel::$USER_ACTIVITY_INTERACTIVE_LIMIT_TTL);
            $this->InsertUserActivityInteractive($singerid, $gameid, $uid);
            $rt = 0;
        }
        return $rt;
    }
    public function InsertUserReddot($uid, $tabType, $subType, $timeStamp)
    {
        // DBLE
        $db_main = $this->getDbMain();
        $sql = "SELECT uid, tab_type, sub_type FROM card.user_reddot_status_info WHERE uid=$uid AND tab_type=$tabType AND sub_type=$subType";

        $rows = $db_main->query($sql);

        if (!empty($rows) && $rows->num_rows > 0) {
            $sql = "UPDATE card.user_reddot_status_info SET recent_timestamp=$timeStamp WHERE uid=$uid AND tab_type=$tabType AND sub_type=$subType";
        } else {
            $sql = "INSERT INTO card.user_reddot_status_info(uid,tab_type,sub_type,recent_timestamp) VALUES($uid,$tabType,$subType,$timeStamp)";
        }

        $rows = $db_main->query($sql);
        if (empty($rows) || $db_main->affected_rows <= 0) {
            LogApi::logProcess("[DBLElog] GameModel:InsertUserReddot sql error:$sql");
        } else {
            LogApi::logProcess("GameModel:InsertUserReddot uid:".$uid." tabType:".$tabType." subType:".$subType." timeStamp:".$timeStamp);  
        }
    }
    public function InitConfigDB()
    {
        /*$id_min = 94;
        $id_max = 96;
        */
        // select id,parm1,parm2,parm3 from card.parameters_info where id >= 95 && id <= 96;
        //$sql = "select id,parm1,parm2,parm3 from card.parameters_info where id >= $id_min && id <= $id_max";
    	$ids = array(94, 95, 96, 120, 121);
        $sql = "select id,parm1,parm2,parm3 from card.parameters_info where id in (";
        
        $len = count($ids);
        for ($i=0; $i<$len; ++$i) {
        	$sql .= $ids[$i];
        	if ($i < $len-1) {
        		$sql .= ',';
        	}
        }
        $sql .= ')';
        
        $rows = $this->getDbMain()->query($sql);
        $db_array = array();
        if ( $rows )
        {
            if ( 0 < $rows->num_rows )
            {
                for ($x=0; $x<$rows->num_rows; $x++)
                {
                    $row = $rows->fetch_assoc();
                    // 0  1     2     3
                    // id,parm1,parm2,parm3
                    $db_array[$row['id']] = array('parm1'=>$row['parm1'],'parm2'=>$row['parm2'],'parm3'=>$row['parm3']);
                }
                if(isset($db_array['94'])){$this->USER_ACTIVITY_INTERACTIVE_LIMIT_NUMBER = $db_array['94']['parm1'];}
                if(isset($db_array['95'])){$this->USER_ACTIVITY_LIMIT_NUMBER['4'] = $db_array['95']['parm1'];}
                if(isset($db_array['96'])){$this->USER_ACTIVITY_LIMIT_NUMBER['5'] = $db_array['96']['parm1'];}
                if(isset($db_array['120'])){$this->ROLL_DICE_ENTER_LIMIT = $db_array['120']['parm1'];};
                if(isset($db_array['121'])){$this->ROLL_DICE_COUNT_LIMIT = $db_array['121']['parm1'];};
            }
        }
        LogApi::logProcess("begin GameModel::InitConfigDB db_array:".json_encode($db_array));
    }
    
    /**
     * 主播结束你动我猜游戏
     * @param unknown $id 该场游戏的唯一标识
     */
    public function overDiceGame($singerid, $id, &$return)
    {
        LogApi::logProcess("begin GameModel::overDiceGame*********");
        // clear the flag.
        $this->SetSingerGameStatus($singerid,0);
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
        
        $gameid = GameModel::$GAME_ID_DICE;
        
        $userInfo = new UserInfoModel();
        
        // init config frome db.
        $this->InitConfigDB();
		
        $result = array(
        		'errcode' => 0
        );
        
        do {
        	$sql = "update cms_manager.current_game set status = 2 where id = $id";
        	$mysql = $this->getDbChannellive();
        	$rows = $mysql->query($sql);
        	// $rows = $this->getDbChannellive()->query($sql);
        	if(!$rows /*|| 0 == $mysql->affected_rows*/) // 如果其他环节处理失败，保证再次请求时这里可以通过
        	{
        		LogApi::logProcess("GameModel::overDiceGame****excut sql error!!!**********sql:$sql");
        		$result['errcode'] = -1;
        		break;
        	}
        	
        	$gameKey = "diceGameMoney:".$id;
        	//总奖金数
        	$money = $this->getRedisMaster()->get($gameKey);
        	
        	//抽成
        	/* $tickets = $this->addDiceTicketCount($id, 0);
        	 $cost = 0;
        	 if($tickets > 40 && $tickets <= 100 && $money > 500){
        	 $cost = $money*5/100;
        	 $money -= $cost;
        	 }else if($tickets > 100 && $tickets <= 200 && $money > 1000){
        	 $cost = $money*10/100;
        	 $money -= $cost;
        	 }else if($tickets > 200 && $money > 2000){
        	 $cost = $money*20/100;
        	 $money -= $cost;
        	 }
        	
        	 //舍去小数部分
        	 $cost = floor($cost);
        	 $money = floor($money);*/

			 //新的抽成逻辑, 直接抽成8%
			 {
				 $cost = floor($money*8/100);
	    	 	 $money -= $cost;

	    	 	 //舍去小数部分
	        	 $cost = floor($cost);
	        	 $money = floor($money);

	        	 if(0 > $money) $money = 0;
			 }
        	 
        	
        	$timecode = time();
        	//
        	$sql = "select distinct user_id from cms_manager.dice_game_result where current_game_id=$id";
        	$rows = $this->getDbChannellive()->query($sql);
        	if (null != $rows && 0 < $rows->num_rows)
        	{
        		$row = $rows->fetch_assoc();        		
        		while ($row) 
        		{
        			$uid = ( int ) $row ['user_id'];
        			 
        			$result['uids'][] = $uid;
        			 
//         			// check and insert.
//         			$iuaic = $this->InsertUserActivityInteractiveCheck ( $singerid, $gameid, $uid );
//         			if(0 == $iuaic)
//         			{
//         			    // 互动游戏
//         			    // tableType 0，subType 10
//         			    $tabType = 0;
//         			    $subType = 10;
//         			    $timeStamp = $timecode;
//         			    $this->InsertUserReddot($uid, $tabType, $subType, $timeStamp);
        			     
//         			    $nt = array();
//         			    $nt['cmd'] = 'redDotNotify';
//         			    $nt['tabType'] = $tabType;
//         			    $nt['subType'] = $subType;
//         			    $nt['timeStamp'] = $timeStamp;
        			     
//         			    $return[] = array
//         			    (
//         			        'broadcast' => 7,// 发给linkd
//         			        'target_uid' => $uid,
//         			        'data' => $nt,// 发给发起者
//         			    );
//         			}
        			//
        			$row = $rows->fetch_assoc();        			 
        		}
        	}
        	//
        	
        	//奖金分配比例数组
        	$per = array(30, 20, 15, 5, 5, 5, 5, 5, 5, 5);
        	
        	//获得名次分组
        	$sql = "select count(u.points) as count, u.points from cms_manager.dice_game_result u
        	where u.current_game_id=$id group by u.points ORDER BY u.points desc";
        	$rows = $this->getDbChannellive()->query($sql);
        	$count = 0;
        	
        	if (!$rows) {
        		LogApi::logProcess("GameModel::overDiceGame****excut sql error!!!**********sql:$sql");
        		$result['errcode'] = -1;
        		break;
        	}

        	if ($rows->num_rows > 0) {
        		$row = $rows->fetch_assoc();
        		$group = array();
        	
        		//$count = 0;
        		$i = 0;
        		while ($row) {
        			if($count >= 10){
        				break;
        			}
        	
        			$group[$i++] = $row['count'];
        			$count += (int)$row['count'];
        			$row = $rows->fetch_assoc();
        		}
        	}else{
        		LogApi::logProcess("GameModel::overDiceGame****(1)过早结束游戏**********sql:$sql");
        		$result['errcode'] = 1;
        		break;
        	}
        	
        	LogApi::logProcess("GameModel::overDiceGame****money:$money*****获得名次分组*****group:".json_encode($group)."****sql:$sql");
        	
        	//排名
        	$sql = "select u.user_id, r.nick, u.points, c.money from cms_manager.dice_game_result u
        	left join cms_manager.current_game c on c.id = u.current_game_id
        	left join raidcall.uinfo r on u.user_id = r.id
        	where u.current_game_id=$id ORDER BY u.points desc";
        	$rows = $this->getDbChannellive()->query($sql);
        	
        	if (!$rows) {
        		LogApi::logProcess("GameModel::overDiceGame****excut sql444 error!!!**********sql:$sql");
        		$result['errcode'] = -1;
        		break;
        	}
        	if (0 < $rows->num_rows) {
        		$i = 0;
        		$len = count ( $group );
        		$indexTmp = 0;
        		$a = 0;
        		
        		while ( $i < $len ) { // 分组数
        			// 每一组有多少个点数相同的用户
        			$num = $group [$i ++];
        	
        			$index = 0;
        	
        			$perTmp = 0;
        			for(; ($a - $indexTmp) < $num; $a ++) { // 奖金分配比例
        				$perTmp += $per [$a];
        			}
        			$perTmp = $perTmp / $num;
        	
        			// 就是为了while($row && $index++ < $num)中的$row第一次执行时为真
        			$row = 1;
        	
        			$redisKeys = array ();
        			$values = array ();
                    $ms = array();
        			while ( $row && $index ++ < $num ) { // 同一组（并列排名）
        				$row = $rows->fetch_assoc ();
        				$item = array ();
        				$item ['index'] = ++ $indexTmp;
        				$item ['uid'] = $row ['user_id'];
        				$item ['nick'] = $row ['nick'];
        				$item ['points'] = $row ['points'];
        				$value = floor ( $money * $perTmp / 100 );
        				$item ['money'] = $value;
        					
        				$user = $userInfo->getInfoById ( $row ['user_id'] );
        				$item ['photo'] = $user ['photo'];
        					
        				$result ['items'] [] = $item;
        					
        				$uid = ( int ) $row ['user_id'];

        				$redisKeys [] = "user_attribute:{$uid}";

                        $values[$uid] = $value;
        			}
        			
                    // DBLE
        			$ids = implode(',', array_keys($values));
        			$sql = "UPDATE rcec_main.user_attribute SET coin_balance = CASE uid ";
        			foreach ($values as $id => $value) {
                        $sql .= "WHEN $id THEN coin_balance+$value ";
        			}
        			$sql .= "END WHERE uid IN ($ids)";

        			$flag = $this->getDbMain ()->query ( $sql );
        			if (! $flag) {
        				$result['errcode'] = -1;
        				LogApi::logProcess ( "[DBLElog] GameModel::overDiceGame**更新用户秀币值失败!!!**********sql:$sql" );
        				break;
        			}
        			$this->getRedisMaster ()->del ( $redisKeys );
        		}
        	} else {
        		LogApi::logProcess("GameModel::overDiceGame****(2)过早结束游戏**********sql:$sql");
        		$result['errcode'] = 1;
        		break;
        	}

        	/* $query = "UPDATE rcec_main.user_attribute SET jinbi_point = jinbi_point+$cost WHERE uid = $singerid";
        	 $rs = $this->getDbMain()->query($query);
        	 if (!$rs) {
        	 LogApi::logProcess("GameModel::overDiceGame****更新主播分成失败 !!!**********sql:$query");
        	 return false;
        	 } */
        	
        	// 如果结束失败不清除缓存，如果过早结束游戏不在这里清缓存
        	if ((int)$result['errcode'] != 0) {
        		break;
        	}
        	
        	// user good add
        	$toolConsumRecordModel = new ToolConsumeRecordModel();
        	foreach ($result['items'] as $val) {
        		$toolConsumRecordModel->on_user_good_add($val['uid'], ToolConsumeRecordModel::GOODS_ID_GOLD, $val['money'], ToolConsumeRecordModel::USER_GOOD_ADD_SRC_DICE);
        	}
        	
        	$value = 0;
        	for($i = $count; $i < 10; $i++){
        		$value += floor($money*$per[$i]/100);
        	}
        	 
        	$info = new ToolConsumeRecordInfo();
        	$info->now = time();
        	$info->uid = 0;
        	$info->singerUid = 0;
        	$info->sid = 0;// 房间id
        	$info->cid = 0;// 频道id
        	$info->tid = 0;// 道具id 弹幕为0
        	$info->tool_category1 = 0;// 道具一级目录 弹幕为0
        	$info->tool_category2 = 0;// 道具二级目录 弹幕为0
        	$info->qty = 0;// 数量
        	$info->buy = 0;// 是不是直接在商城买的 弹幕为0
        	$info->tool_price = 0;
        	$info->total_coins_cost = 0;
        	$info->total_receiver_points = 0;// 接收这产生的秀点 弹幕为0
        	$info->total_receiver_charm = 0;
        	$info->total_session_points = 0;// 弹幕为0
        	$info->total_session_charm = 0;// 弹幕为0
        	$info->baseValue = 0;// 主播基础分成
        	$info->prizeValue = 0;// 主播奖金上限(主播奖金预算增加值)
        	$info->backValue = 0;// 主播回馈基金分成值
        	$info->unionTotalValue = 0;// 公会分成值 弹幕为0
        	$info->sysControl = 0;// 系统调控基金分成值 弹幕为0
        	$info->officialValue = $value;// 官方收入分成值 100%
        	$info->unionId = 0;// 公会id
        	$info->unionValue = 0;// 公会收入预算-公会收益 弹幕为0
        	$info->unionBack = 0;// 公会收入预算-公会回馈基金 弹幕为0
        	$info->unionPrize = 0;// 公会收入预算-公会奖金预算 弹幕为0
        	$info->unionSunValue = 0;// 公会增加阳光值 弹幕为0
        	$info->singerSunValue = 0;// 主播增加阳光值 弹幕为0
        	 
        	$toolConsumRecordModel->AppendToolConsumeRecordInfo($info);
        	
        	$this->ClearDiceGameCache($id, $singerid);
        	 
        } while(0);
                
        if ($result['errcode'] == 1) {
        	$diceGameInitMoneyKey = "diceGameInitMoney:$id";
        	$initMoney = $this->getRedisMaster()->get($diceGameInitMoneyKey);
        	if (!empty($initMoney)) {
        		$gameKey = "gamePreheat:".$id;
        		if ($this->getRedisMaster()->get($gameKey)) {
        			$sql = "select id from rcec_record.sys_income_record";
        			$rows = $this->getDbRecord()->query($sql);
        			if ($rows && $rows->num_rows > 0) {
        				$row = $rows->fetch_assoc();
        				$index = $row['id'];

                        // DBLE
                        $sql = "UPDATE rcec_record.sys_income_record SET sys_income=sys_income+$initMoney, updatetime=NOW() WHERE id=$index";

        				$rows = $this->getDbRecord()->query($sql);
        				if (empty($rows)) {
        					LogApi::logProcess("[DBLElog] GameModel::overDiceGame*******excute sql error!!!*********sql:$sql");
        				}
        	
        				$sql = "insert into rcec_record.sys_income_record_detail (sys_income, type, updatetime, from_uid) values ($initMoney, 4, NOW(), $singerid)";
        				$rows = $this->getDbRecord()->query($sql);
        				if (empty($rows)) {
        					LogApi::logProcess("GameModel::overDiceGame*******excute sql error!!!*********sql:$sql");
        				}
        			}
        			 
        		} else {
        			$sql = "update rcec_record.anchor_percentage_record set anchor_back_fund = anchor_back_fund + $initMoney where uid = $singerid";
        			$flag = $this->getDbChannellive()->query($sql);
        			if (!$flag) {
        				LogApi::logProcess("GameModel::overDiceGame*******excute sql error!!!*********sql:$sql");
        				$result['errcode'] = -1;
        			} else {
                        $model_divide = new divide_model();
                        $model_divide->insert_anchor_divide_back_bill($singerid, divide_model::TYPE_GAME_DICE, 0, $initMoney, 0, $id);
                    }
        		}
        	}
        }
       
        LogApi::logProcess("end GameModel::overDiceGame****************result:".json_encode($result));
        return $result;
    }
    
    /**
     * 主播结束你动我猜游戏
     * @param unknown $id 该场游戏的唯一标识
     */
    public function overGuessGame($singerId, $id, &$return)
    {
        LogApi::logProcess("begin overGuessGame::***************singerId:$singerId, id:$id");
        // clear the flag.
        $this->SetSingerGameStatus($singerId,0);
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
		
        $gameid = GameModel::$GAME_ID_GUESS;
        // init config frome db.
        $this->InitConfigDB();
        
        $userInfo = new UserInfoModel();
        
        $result = array();
        $sql = "update cms_manager.current_game set status = 2 where id = $id";

        $mysql = $this->getDbChannellive();
        $rows = $mysql->query($sql);
        // LogApi::logProcess("overGuessGame mysql->affected_rows:".$mysql->affected_rows);
        // $rows = $this->getDbChannellive()->query($sql);
        if(!$rows || 0 == $mysql->affected_rows)
        {
            LogApi::logProcess("overGuessGame::*********sql error!!!*******sql:$sql");
            $result['items'] = array();
            return $result;
        }
        $timecode = time();
        //
        $sql = "select distinct user_id from cms_manager.user_game_answer where current_game_id=$id";
        $rows = $this->getDbChannellive()->query($sql);
        if (null != $rows && 0 < $rows->num_rows)
        {
            $row = $rows->fetch_assoc();
            while ($row) 
            {
            	$uid = ( int ) $row ['user_id'];
            	$result['uids'][] = $uid;
            	
//             	// check and insert.
//             	$iuaic = $this->InsertUserActivityInteractiveCheck ( $singerId, $gameid, $uid );
//             	if(0 == $iuaic)
//             	{
//             	    // 互动游戏
//             	    // tableType 0，subType 10
//             	    $tabType = 0;
//             	    $subType = 10;
//             	    $timeStamp = $timecode;
//             	    $this->InsertUserReddot($uid, $tabType, $subType, $timeStamp);
            	    
//             	    $nt = array();
//             	    $nt['cmd'] = 'redDotNotify';
//             	    $nt['tabType'] = $tabType;
//             	    $nt['subType'] = $subType;
//             	    $nt['timeStamp'] = $timeStamp;
            	    
//             	    $return[] = array
//             	    (
//             	        'broadcast' => 7,// 发给linkd
//             	        'target_uid' => $uid,
//             	        'data' => $nt,// 发给发起者
//             	    );
//             	}
            	//
            	$row = $rows->fetch_assoc();
            }
        }
        //
        $sql = "select * from (select u.user_id, r.nick, sum(u.score) as totalscore,
            sum(u.take_time) as total_take_time, c.money from cms_manager.user_game_answer u 
            left join cms_manager.current_game c on c.id = u.current_game_id
            left join raidcall.uinfo r on u.user_id = r.id
            where u.current_game_id=$id and u.score != 0 GROUP BY u.user_id) u1 ORDER BY u1.totalscore desc";
        
        // if score == 0 we not return it.
        // note:if member list is empty,show a massage for it.
        /*
        $sql = "select * from (select u.user_id, r.nick, sum(u.score) as totalscore,
        sum(u.take_time) as total_take_time, c.money from cms_manager.user_game_answer u
        left join cms_manager.current_game c on c.id = u.current_game_id
        left join raidcall.uinfo r on u.user_id = r.id
        where u.current_game_id=$id GROUP BY u.user_id) u1 ORDER BY u1.totalscore desc";
        */
        
        $rows = $this->getDbChannellive()->query($sql);
        
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $index = 0;
            $redisKeys = array();
            $values = array();
            //只取前10名
            while ($row && $index < 11) {
                switch (++$index){
                    case 1:
                        $money = 33*$row['money']/100;
                        break;
                    case 2:
                        $money = 21*$row['money']/100;
                        break;
                    case 3:
                        $money = 14*$row['money']/100;
                        break;
                    case 4:
                        $money = 8*$row['money']/100;
                        break;
                    case 5:
                        $money = 7*$row['money']/100;
                        break;
                    case 6:
                        $money = 5.5*$row['money']/100;
                        break;
                    case 7:
                        $money = 4*$row['money']/100;
                        break;
                    case 8:
                        $money = 3*$row['money']/100;
                        break;
                    case 9:
                        $money = 2.5*$row['money']/100;
                        break;
                    case 10:
                        $money = 2*$row['money']/100;
                        break;      
                }
                
                //舍去小数部分
                $money = floor($money);
                
                $item = array();
                $item['index'] = $index;
                $item['uid'] = $row['user_id'];
                $item['nick'] = $row['nick'];
                $item['totalscore'] = $row['totalscore'];
                $item['money'] = $money;
                
                $user = $userInfo->getInfoById($row['user_id']);
                $item['photo'] = $user['photo'];
                
                $result['items'][] = $item;
                
                $uid = (int)$row['user_id'];
                $values[$uid] = $money;
                
                $redisKeys[] = "user_attribute:{$uid}";
                $row = $rows->fetch_assoc();
            }

            //begin 多余的钱计入官方收入
            //奖金分配比例数组
            $per = array(33, 21, 14, 8, 7, 5.5, 4, 3, 2.5, 2);
            $value = 0;
            for($i = $index; $i < 10; $i++){
                $value += floor($row['money']*$per[$i]/100);
            }
             
            $toolConsumRecordModel = new ToolConsumeRecordModel();
            $info = new ToolConsumeRecordInfo();
            $info->now = time();
            $info->uid = 0;
            $info->singerUid = 0;
            $info->sid = 0;// 房间id
            $info->cid = 0;// 频道id
            $info->tid = 0;// 道具id 弹幕为0
            $info->tool_category1 = 0;// 道具一级目录 弹幕为0
            $info->tool_category2 = 0;// 道具二级目录 弹幕为0
            $info->qty = 0;// 数量
            $info->buy = 0;// 是不是直接在商城买的 弹幕为0
            $info->tool_price = 0;
            $info->total_coins_cost = 0;
            $info->total_receiver_points = 0;// 接收这产生的秀点 弹幕为0
            $info->total_receiver_charm = 0;
            $info->total_session_points = 0;// 弹幕为0
            $info->total_session_charm = 0;// 弹幕为0
            $info->baseValue = 0;// 主播基础分成
            $info->prizeValue = 0;// 主播奖金上限(主播奖金预算增加值)
            $info->backValue = 0;// 主播回馈基金分成值
            $info->unionTotalValue = 0;// 公会分成值 弹幕为0
            $info->sysControl = 0;// 系统调控基金分成值 弹幕为0
            $info->officialValue = $value;// 官方收入分成值 100%
            $info->unionId = 0;// 公会id
            $info->unionValue = 0;// 公会收入预算-公会收益 弹幕为0
            $info->unionBack = 0;// 公会收入预算-公会回馈基金 弹幕为0
            $info->unionPrize = 0;// 公会收入预算-公会奖金预算 弹幕为0
            $info->unionSunValue = 0;// 公会增加阳光值 弹幕为0
            $info->singerSunValue = 0;// 主播增加阳光值 弹幕为0
             
            $toolConsumRecordModel->AppendToolConsumeRecordInfo($info);
            //end
            
            // DBLE
            $ids = implode(',', array_keys($values));
            $sql = "UPDATE rcec_main.user_attribute SET coin_balance = CASE uid ";
            foreach ($values as $id => $value) {
                $sql .= "WHEN $id THEN coin_balance+$value ";
            }
            $sql .= "END WHERE uid IN ($ids)"; 
            $flag = $this->getDbMain()->query($sql);
            if(!$flag){
                LogApi::logProcess("[DBLElog] overGuessGame::**excute sql error!!!**************sql:$sql");
            }
            $this->getRedisMaster()->del($redisKeys);
            LogApi::logProcess("overGuessGame::**更新用户秀币值*********flag:$flag*****sql:$sql");
        }else{
            $result['items'] = array();
            LogApi::logProcess("overGuessGame::**excute sql error!!!**************sql:$sql");
        }
        
        // user good add
        $model_tool_consume_record = new ToolConsumeRecordModel();
        if (!empty($result['items'])) {
        	foreach ($result['items'] as $val) {
        		$model_tool_consume_record->on_user_good_add($val['uid'], ToolConsumeRecordModel::GOODS_ID_GOLD, $val['money'], ToolConsumeRecordModel::USER_GOOD_ADD_SRC_GUESS);
        	}
        }
        
        //删除主播发起游戏后参与的用户缓存
        $gameKey = "game:".$id;
        $this->getRedisMaster()->del($gameKey);
        //删除游戏执行的题号
        $gamekey = "gameNext:".$id;
        $this->getRedisMaster()->del($gamekey);
        
        $gamekey = "DoGuessGame:$id:*";
        $this->getRedisMaster()->del($gamekey);
        
        LogApi::logProcess("overGuessGame::****************result:".json_encode($result));
        
        return $result;
    }
    
    /**
     * 玩家开始摇骰子
     * @param unknown $id 本场游戏唯一标识
     * @param unknown $uid 玩家用户id
     * @param unknown $point 骰子点数
     * @param unknown $num 摇骰子次数
     */
    public function doDiceGame($id, $uid, $point, $num, $money, $giftId, $giftMoney)
    {
        LogApi::logProcess("begin GameModel::doDiceGame****************");
		
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
        
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        $inner_num = (int)$user['is_robot'];
        if(2 == $inner_num){
            $money = 0;
        }
        
        $now = time();

        // DBLE
        $db_cms = $this->getDbChannellive();

        $sql = "SELECT current_game_id FROM cms_manager.dice_game_result WHERE current_game_id=$id AND user_id=$uid";
        $rows = $db_cms->query($sql);

        if (!empty($rows) && $rows->num_rows > 0) {
            $sql = "UPDATE cms_manager.dice_game_result SET points=$point, number=$num, update_time=$now, money=money+$giftMoney WHERE current_game_id=$id AND user_id=$uid";
        } else {
            $sql = "INSERT INTO cms_manager.dice_game_result(current_game_id, user_id, points, number, update_time, gift_id, money) VALUES($id, $uid, $point, $num, $now, $giftId, $money)";
        }

        $rows = $db_cms->query($sql);
        if(!$rows || $db_cms->affected_rows <= 0){
            LogApi::logProcess("[DBLElog] GameModel::doDiceGame**exceut sql error!!!**************sql:$sql");
            return false;
        }
        
        //加入到总奖金池里
        $sql = "update cms_manager.current_game set money = money + $money where id = $id";
        $rows = $this->getDbChannellive()->query($sql);
        if(!$rows){
            LogApi::logProcess("GameModel::doDiceGame**exceut sql error!!!**************sql:$sql");
            return false;
        }
        
        $gameKey = "diceGameMoney:".$id;
        $totalMoney = $this->getRedisMaster()->incrBy($gameKey, $money);
        
        LogApi::logProcess("end GameModel::doDiceGame****************");
        
        return $totalMoney;
    }
    
    /**
     * 摇骰子游戏总共门票数
     * @param unknown $id 当前游戏id
     * @param unknown $count 门票数量
     */
    public function addDiceTicketCount($id, $count)
    {
        //门票数
        $gameCountKey = "diceGameTickets:".$id;
        $count = $this->getRedisMaster()->incrBy($gameCountKey, $count);
        
        return $count;
    }
    
    //获得指定用户摇骰子次数
    public function addDiceTimes($id, $uid, $count)
    {
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
		
        $timesKey = 'diceGame_times:'.$id;
        //摇骰子次数
        $num = $this->getRedisMaster()->zIncrBy($timesKey, $count, $uid);
        
        return $num;
    }

    public function getDiceTimes($id, $uid)
    {
        $timesKey = 'diceGame_times:'.$id;
        //摇骰子次数
        $num = $this->getRedisMaster()->zScore($timesKey, $uid);
        
        return $num;
    }
    
    /**
     * 保存你猜我动游戏用户答案
     * @param unknown $gameid:游戏id
     * @param unknown $uid:用户id
     * @param unknown $question_seq:问题序号
     * @param unknown $user_answer:用户选择的答案
     * @param unknown $question_answer:问题答案(系统定的)
     * @param unknown $cost_time:答题耗时(毫秒)
     */
    public function saveAnswer($id, $uid, $question_seq, $user_answer, $question_answer, $cost_time)
    {   
        LogApi::logProcess("begin saveAnswer****************");
        //答题正确=1000+答题剩余时间*100（0.01秒）
        if($user_answer == $question_answer){
            $score = 1000+(30*1000-$cost_time);//*100;
        }else{
            $score = 0;
        }
        // make sure the score >= 0.30*1000-$cost_time can be < 0.
        if(0 > $score)
        {
            $score = 0;
        }
        $now = time();
        $sql = "insert into cms_manager.user_game_answer(current_game_id, user_id, score, question_seq, user_answer, question_answer,
        take_time, create_time) values($id, $uid, $score, $question_seq, \"$user_answer\", \"$question_answer\", $cost_time, $now)";
        $rows = $this->getDbChannellive()->query($sql);
        if(!$rows){
            LogApi::logProcess("saveAnswer::excue sql error!!!****************sql:$sql");
            return false;
        }
        
        LogApi::logProcess("saveAnswer::score::($score)****************sql:$sql");
        
        return $score;
    }
    
    //获得游戏信息
    public function getGameInfo($gameid)
    {
//         $result = array();
        //获得题库总条数
        $sql = "select * from cms_manager.games where id=$gameid";
        
        $rows = $this->getDbChannellive()->query($sql);
        
        $row = array();
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            /* //游戏名称
            $result['name'] = $row['name'];
            //图片名称
            $result['img_name'] = $row['img_name'];
            //状态 1:启用  2:停用
            $result['status'] = $row['status'];
            //游戏类型 1:用户购买门票 2:主播发放金币
            $result['type'] = $row['type']; */
        }
        
        LogApi::logProcess("getGameInfo::****************resulet:".json_encode($row));
       
        return $row;
    }
    
    //主播表演下一题
    public function actNextQuestion($id, $question_seq)
    {
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
		
        $gamekey = "gameNext:".$id;
        $this->getRedisMaster()->set($gamekey, $question_seq);
    }

    public function getNextQuestionSeq($id) {
        $gamekey = "gameNext:".$id;
        return $this->getRedisMaster()->get($gamekey);
    }
    
    //用户加入骰子游戏
    public function enrollDiceGame($id, $singerid, $gameid, $uid, $money)
    {
        LogApi::logProcess("begin enrollDiceGame****************");
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
        
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        $inner_num = (int)$user['is_robot'];
        if(2 == $inner_num){
            $money = 0;
        }
        
        $result = array(
        		'errcode' => 0
        );
        $size = $this->enrollGame($id, $singerid, $gameid, $uid);

        $size = isset($size) ? $size : 0;
        
        $sql = "update cms_manager.current_game set game_id=$gameid, zbid=$singerid, play_number=$size, money=money+$money where id=$id and status=0";
        $mysql = $this->getDbChannellive();
        $rows = $mysql->query($sql);
        
        if(!$rows || $mysql->affected_rows == 0){
        	$result['errcode'] = -1;
            LogApi::logProcess("enrollDiceGame::***excute sql error!!!*************sql:$sql");
            return false;
        }
        
        // 如果人数大于5则增加初始奖金池奖金
        $man_floor = $this->GetGameManfloor();
        if ($size > $man_floor) {
        	$gameKey = "diceGameMoney:".$id;
        	$totalMoney = $this->getRedisMaster()->incrBy($gameKey, $money);
        } else {
        	$gameKey = "diceGameMoney:".$id;
        	$totalMoney = $this->getRedisMaster()->get($gameKey);
        }
        
        $result['userCount'] = $size;
        $result['money'] = $totalMoney;
        
        //门票数
        $gameCountKey = "diceGameTickets:".$id;
        $this->getRedisMaster()->incrBy($gameCountKey, 1);
        
        LogApi::logProcess("end enrollDiceGame::****************用户($uid)参加游戏($id),共有($size)人报名了该游戏, 奖金总额($totalMoney)");
        
        return $result;
    }
    
    //用户是否参与游戏
//     public function isEnrollGame($id, $uid)
//     {
//         $gameKey = "game:".$id;
//         //TODO:
//     }

    //用户做你演我猜游戏，判断该题目是否做完
    public function isFinishGuessGameItem($id, $question_seq, $uid)
    {
        $gameKey = "game:".$id;
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
        $size = $this->getRedisMaster()->sCard($gameKey);
        
        $key = "DoGuessGame:$id:$question_seq";
        $this->getRedisMaster()->sAdd($key, $uid);
        $num = $this->getRedisMaster()->sCard($key);
        
        if($num == $size){
            LogApi::logProcess("isFinishGuessGameItem::****************id:$id seq:$question_seq 最后一题.");
            return true;
        }
        LogApi::logProcess("isFinishGuessGameItem::****size:$size num:$num************id:$id seq:$question_seq 不是最后一题.");
        
        return false;
    }
    
    //用户加入游戏
    public function enrollGame($id, $singerid, $gameid, $uid)
    {
        $gameKey = "game:".$id;
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
        $this->getRedisMaster()->sAdd($gameKey, $uid);
        //$size = getRedisMaster()->sSize($gameKey);
        $size = $this->getRedisMaster()->sCard($gameKey);
        
        $key = "user:$uid" . ":enroll:game";
        $this->getRedisMaster()->set($key, $id);
        $this->getRedisMaster()->expire($key, 30*60);
        
        LogApi::logProcess("enrollGame::****************用户($uid)参加游戏($id),共有($size)人报名了该游戏");
        
        return $size;
    }
    
    //根据主播id获得当前主播正在玩的游戏id
    public function getGameIdBySingerId($singerId)
    {
        $gameIdKey = "gameId:".$singerId;
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
        $id = $this->getRedisMaster()->get($gameIdKey);
        
        return $id;
    }
    
    //当主播异常退出时，游戏模块的处理
    public function dealExceptionOver($singerId)
    {
        LogApi::logProcess("begin GameModel::dealExceptionOver****************singerId:$singerId");
        $gameIdKey = "gameId:".$singerId;
        $id = $this->getRedisMaster()->get($gameIdKey);
        
        if(empty($id)){
            LogApi::logProcess("GameModel::dealExceptionOver****************没有找到主播($singerId)的游戏id");
            return false;
        }
        
        $sql = "select c.id, c.game_id, c.status, g.type from cms_manager.current_game c 
                left join cms_manager.games g on g.id = c.game_id where c.id = $id";
        $rows = $this->getDbChannellive()->query($sql);
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
//             $id = $row['id'];
//             $status = $row['status'];
//             $type = $row['type'];
            return $row;
        }else {
            LogApi::logProcess("GameModel::dealExceptionOver： excue sql error!****************sql:$sql");
            return false;
        }
        
    }
    
    //用户加入游戏
    public function getEnrollUserSize($id)
    {
        $gameKey = "game:".$id;
        //$size = getRedisMaster()->sSize($gameKey);
        $size = $this->getRedisMaster()->sCard($gameKey);
        
        return $size;
    }
    
    //获得报名参加游戏的人员id信息
    public function popEnrollUid($id)
    {
        $gameKey = "game:".$id;
        $uid = $this->getRedisMaster()->sPop($gameKey);
        
        return $uid;
    }
    
    //清楚预热缓存，更新数据库
    public function clearPreheat($singerid)
    {
    	$this->ClearPreheatingInfoRedis($singerid);
        $limit = 1000;
        $sql = "UPDATE cms_manager.game_ready_info SET status = 0 WHERE uid = $singerid limit $limit";
        $rows = $this->getDbChannellive()->query($sql);
        if(!$rows)
        {
           LogApi::logProcess("GameModel::clearPreheat*******excute sql error!!!*********sql:$sql");
        }
    }
    
    //主播取消摇骰子游戏
    public function cancelDiceGame($singerid, $id)
    {
    	// reset the flag.
    	$this->SetSingerGameStatus($singerid,0);
    	$this->ClearSingerGameStatusMysql($singerid);
    	//
    	
    	$flag = $this->cancelGame($id);
    	 
    	if (!$flag) {
    		return $flag;
    	}

    	$diceGameInitMoneyKey = "diceGameInitMoney:$id";
    	$initMoney = $this->getRedisMaster()->get($diceGameInitMoneyKey);
    	if (!empty($initMoney)) {
    		$gameKey = "gamePreheat:".$id;
    		if ($this->getRedisMaster()->get($gameKey)) {
    			$sql = "select id from rcec_record.sys_income_record";
    			$rows = $this->getDbRecord()->query($sql);
    			if ($rows && $rows->num_rows > 0) {
    				$row = $rows->fetch_assoc();
    				$index = $row['id'];
    				
                    // DBLE
                    $sql = "UPDATE rcec_record.sys_income_record SET sys_income=sys_income+$initMoney, updatetime=NOW() WHERE id=$index";
    				$rows = $this->getDbRecord()->query($sql);
    				if (empty($rows)) {
    					LogApi::logProcess("[DBLElog] GameModel::cancelDiceGame*******excute sql error!!!*********sql:$sql");
    				}
    				
    				$sql = "insert into rcec_record.sys_income_record_detail (sys_income, type, updatetime, from_uid) values ($initMoney, 4, NOW(), $singerid)";
    				$rows = $this->getDbRecord()->query($sql);
    				if (empty($rows)) {
    					LogApi::logProcess("GameModel::cancelDiceGame*******excute sql error!!!*********sql:$sql");	
    				}
    			}
    			
    		} else {
    			$sql = "update rcec_record.anchor_percentage_record set anchor_back_fund = anchor_back_fund + $initMoney where uid = $singerid";
    			$flag = $this->getDbChannellive()->query($sql);
    			if (!$flag) {
    				LogApi::logProcess("GameModel::cancelDiceGame*******excute sql error!!!*********sql:$sql");
    				return $flag;
    			} 

                $model_divide = new divide_model();
                $model_divide->insert_anchor_divide_back_bill($singerid, divide_model::TYPE_GAME_DICE, 0, $initMoney, 0, $id);
    		}
    	}
        
        $gameKey = "diceGameMoney:".$id;
        $this->getRedisMaster()->del($gameKey);
        
        $gameKey = "diceGameInitMoney:$id";
        $this->getRedisMaster()->del($gameKey);
        
        $gameKey = "gamePreheat:$id";
        $this->getRedisMaster()->del($gameKey);
        return $flag;
    }
    
    //主播取消游戏，删除报名人，并从数据库中删除该游戏
    public function cancelGame($id)
    {
        $gameKey = "game:".$id;
        $size = $this->getRedisMaster()->sCard($gameKey);
        
        LogApi::logProcess("begin GameModel::cancelGame****************共有($size)人报名了该游戏");
        
        $sql = "delete from cms_manager.current_game where id=$id";
        $rows = $this->getDbChannellive()->query($sql);
        if(!$rows){
            LogApi::logProcess("GameModel::cancelGame*******excute sql error!!!*********sql:$sql");
            return false;
        }
        
        $this->getRedisMaster()->del($gameKey);
        $size = $this->getRedisMaster()->sCard($gameKey);
        
        LogApi::logProcess("end GameModel::cancelGame********清理缓缓后人数($size)********");
        
        return true;
    }
    
    //主播取消游戏，删除报名人，并从数据库中删除该游戏
    public function cancelGuessGame($singerid, $id)
    {
        LogApi::logProcess("begin GameModel::cancelGuessGame*****singerid:$singerid, id:$id");
        // reset the flag.
        $this->SetSingerGameStatus($singerid,0);
        $this->ClearSingerGameStatusMysql($singerid);
        
        $gameKey = "game:".$id;
        $size = $this->getRedisMaster()->sCard($gameKey);
        LogApi::logProcess("start GameModel::cancelGuessGame****************共有($size)人报名了该游戏");
        
        $sql = "delete from cms_manager.current_game where id=$id";
        $rows = $this->getDbChannellive()->query($sql);
        if(!$rows){
            LogApi::logProcess("GameModel::cancelGuessGame*****excute sql error!!!***********sql:$sql");
        }
        
        $this->getRedisMaster()->del($gameKey);
        $size = $this->getRedisMaster()->sCard($gameKey);
        
        $gamePreheatKey = "gamePreheat:".$id;
        $preheat = $this->getRedisMaster()->get($gamePreheatKey);
        if($preheat){
            LogApi::logProcess("end GameModel::cancelGuessGame********预热********");
            return true;
        }
        $guessGameMoneyKey = "guessGameMoney:$id";
        $money = $this->getRedisMaster()->get($guessGameMoneyKey);
        
        if (!empty($money)) {
            $sql = "update rcec_record.anchor_percentage_record set anchor_back_fund = anchor_back_fund + $money where uid = $singerid";
            $flag = $this->getDbChannellive()->query($sql);
            //if(!$flag || getDbChannellive()->affected_rows <= 0){
            if(!$flag){
                LogApi::logProcess("GameModel::cancelGuessGame*******excute sql error!!!*********sql:$sql");
                return false;
            }

            $model_divide = new divide_model();
            $model_divide->insert_anchor_divide_back_bill($singerid, divide_model::TYPE_GAME_GUESS, 0, $money, 0, $id);
        } else {
            LogApi::logProcess("GameModel::cancelGuessGame*******guessMoney empty*********key:$guessGameMoneyKey");
        }

        //TODO:福利金币消费记录
        
        $redisKey = "user_attribute:{$singerid}";
        $this->getRedisMaster()->del($redisKey);
        
        LogApi::logProcess("end GameModel::cancelGuessGame********还有($size)人报名了该游戏********");
        
        return true;
    }
    
    //获得主播摇骰子游戏中礼物门票id
    public function getGameGiftId($id)
    {
        //保存主播当前正在玩的摇骰子游戏所送的礼物id
        $gameGiftIdKey = "gameGiftId:".$id;
        $giftId = $this->getRedisMaster()->get($gameGiftIdKey);
        
        return $giftId;
    }
    public function GetPreheatMoney($singerid,$gameid,$preheat_id)
    {
        $value = 0;
        LogApi::logProcess("GameModel::GetPreheatMoney"." singerid:".$singerid." gameid:".$gameid." preheat_id:".$preheat_id);
        //
        if (0 == $preheat_id)
        {
            $status = 1;
            $limit = 1;
            $sql = "SELECT gold FROM cms_manager.game_ready_info WHERE ( uid = $singerid && game_id = $gameid ) ORDER BY time DESC limit $limit";
            $mysql = $this->getDbChannellive();
            $rows = $mysql->query($sql);
            do
            {
                if(!$rows)
                {
                    LogApi::logProcess("GameModel::GetPreheatMoney:******excute sql error!!!**********sql:$sql");
                    $value = 0;
                    break;
                }
                if (0 == $mysql->affected_rows)
                {
                    $value = 0;
                    break;
                }
                $row = $rows->fetch_assoc();
                $value = (int)$row['gold'];
            }while (FALSE);
        }
        else 
        {
            $status = 1;
            $limit = 1;
            $sql = "SELECT gold FROM cms_manager.game_ready_info WHERE ( id = $preheat_id) ORDER BY time DESC limit $limit";
            $mysql = $this->getDbChannellive();
            $rows = $mysql->query($sql);
            do
            {
                if(!$rows)
                {
                    LogApi::logProcess("GameModel::GetPreheatMoney:******excute sql error!!!**********sql:$sql");
                    $value = 0;
                    break;
                }
                if (0 == $mysql->affected_rows)
                {
                    $value = 0;
                    break;
                }
                $row = $rows->fetch_assoc();
                $value = (int)$row['gold'];
            }while (FALSE);
        }
        return $value;
    }
    //主播发起摇骰子游戏
    public function launchDiceGame($userLimitParm,$singerid, $gameid, $money, $giftId, $preheat,$launch_uid,$preheat_id)
    {
        $id = $this->launchGame($userLimitParm,$singerid, $gameid, $money, $preheat,$launch_uid,$preheat_id);
        
//         if(-1 == $id)
//         {
//             return $id;
//         }
        if ( 0 > $id )
        {
            return $id;
        }
        //保存主播当前正在玩的摇骰子游戏所送的礼物id
        $gameGiftIdKey = "gameGiftId:".$id;
        $this->getRedisMaster()->set($gameGiftIdKey, $giftId);
        
//         $gameKey = "diceGameMoney:".$id;
//         $this->getRedisMaster()->incr($gameKey, $money);
                        
        return $id;
    }
    
    //主播发起游戏
    public function launchGame($userLimitParm,$singerid, $gameid, $money, $preheat,$launch_uid,$preheat_id)
    {
        $timecode = time();
        $uim = new UserInfoModel();
        $sid = $uim->GetSidByUid($singerid);
        if (-1 == $sid)
        {
            // data is invalid.
            return -1;
        }
        
        // 如果电锯游戏正在进行，则返回失败
        $model_gm = new game_manager_model();
        if ($model_gm->if_game_saw_ing($singerid)) {
        	return -3;
        }
        
        $this->InitConfigDB();
        
        // old version $launch_uid is 0,means singer launch.
        if (0 == $launch_uid){$launch_uid = $singerid;}
        // check user launch permission.
        if ($singerid != $launch_uid)
        {
            $gulm = new GameUserLaunchModel();
            $flag = $gulm->CheckUserPermission($sid, $launch_uid, $gameid, $timecode);
            if (0 == $flag)
            {
                // 使用权限
                $gulm->ApplyUserPermission($sid,$launch_uid,$gameid);
            }
            else 
            {
                // 无效的用户发起权限.
                return -5;
            }
        }
        else 
        {
            // check singer user limit.
            //$this->InitConfigDB();
            $user_activity_limit_key = GameModel::HashUserActivityLimitKey($singerid,$timecode);
            $curr_number = $this->getRedisMaster()->hGet($user_activity_limit_key, $gameid);
            if (empty($curr_number)){$curr_number = 0;}
            $limi_number = $this->GetUserActivityLimitNumber($singerid,$gameid);
            LogApi::logProcess("GameModel::launchGame: gameid:$gameid curr_number:$curr_number limi_number:$limi_number");
            $userLimitParm->curr_number = $curr_number;
            $userLimitParm->limi_number = $limi_number;
            if ($curr_number >= $limi_number)
            {
                // -1 error means taday this activity is limit.
                return -2;
            }
            // when game start we hIncrBy the current number.launch game not hIncrBy number.just checking.
            // $userLimitParm->curr_number = $this->getRedisMaster()->hIncrBy($user_activity_limit_key, $gameid, 1);
            // $this->getRedisMaster()->expire($user_activity_limit_key,GameModel::$USER_ACTIVITY_LIMIT_TTL);
            // if(empty($userLimitParm->curr_number)){$userLimitParm->curr_number = $curr_number;}
        }        
        if (0 == $preheat)
        {
            // check preheat.
            $anchor_active_game_key = GameModel::StringAnchorActiveGameKey($singerid);
            $preheat_key_ttl = $this->getRedisJavaUtil()->ttl($anchor_active_game_key);
            if (!empty($preheat_key_ttl) && 0 < $preheat_key_ttl)
            {
                // 有一场游戏在预热.
                return -4;
            }
        }       
        else 
        {
            // 如果是预热,从预热表中取出预热奖金数量。
            $money = $this->GetPreheatMoney($singerid,$gameid,$preheat_id);
            LogApi::logProcess("GameModel::launchGame preheat info"." singerid:".$singerid." gameid:".$gameid." money:".$money." preheat:".$preheat." launch_uid:".$launch_uid);
            // 如果是预热,清除预热信息,将倒计时结束掉.
            $this->ClearPreheatingInfo($singerid);
        }
        $status = $this->GetSingerGameStatus($singerid);
        if (1 == $status)
        {
            // 只能同时进行一场游戏.
            return -3;
        }
        else
        {
            $this->SetSingerGameStatus($singerid,1);
        }
        /* $sql = "select id from cms_manager.game_seq";
        $rows = getDbChannellive()->query($sql);
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $id = $row['id'];
        }
        
        $newId = $id+1;
        $sql = "update cms_manager.game_seq set id = $newId";
        $rows = getDbChannellive()->query($sql);
        if(!$rows){
            LogApi::logProcess("error launchGame::****************sql:$sql");
            return -1;
        } */
        
        $sql = "insert into cms_manager.current_game(game_id, zbid, status, play_number, money)
                values($gameid, $singerid, 0, 0, $money)";
        $rows = $this->getDbChannellive()->query($sql);
        if(!$rows){
            LogApi::logProcess("GameModel::launchGame:******excute sql error!!!**********sql:$sql");
            return -1;
        }        
        LogApi::logProcess("GameModel::launchGame:****************sql:$sql");
        
        $id = 0;
        $sql = "select LAST_INSERT_ID() as id";
        $rs = $this->getDbChannellive()->query($sql);
        if($rs && $rs->num_rows > 0) {
            $row = $rs->fetch_assoc();
            $id = (int)$row['id'];
        }

        if ($singerid == $launch_uid)
        {
            if (0 == $preheat)
            {
                // 扣播主福利金币
                if(0 < $money && 0 != $singerid)
                {
                    $model_divide = new divide_model();
                    $back_cache = $model_divide->get_anchor_divide_back_from_cache($singerid);
                    $backValue = $back_cache;

                    $back_before = 0;
                    $sql = "select anchor_back_fund from rcec_record.anchor_percentage_record t where uid = $singerid";
                    $rows = $this->getDbMain()->query($sql);
                    if ($rows && $rows->num_rows > 0) {
                        $row = $rows->fetch_assoc();
                        $back_before = (int)$row['anchor_back_fund'];
                        $backValue += $back_before;
                    }
                
                    if($money <= $backValue){
                        $sql = "update rcec_record.anchor_percentage_record set anchor_back_fund = anchor_back_fund - $money where uid = $singerid";// and anchor_back_fund > $money";
                        $flag = $this->getDbChannellive()->query($sql);
                        if(!$flag)
                        {
                            LogApi::logProcess("GameModel::launchGame:******excute sql error!!!**********sql:$sql");
                            return -1;
                        }

                        $model_divide->insert_anchor_divide_back_bill($singerid, $gameid, $back_before, 0-$money, 0, $id);
                        //TODO:福利金币消费记录
                        LogApi::logProcess("GameModel::launchGame id:$id uid:".$singerid." rcec_record.anchor_percentage_record.anchor_back_fund"." delta:-".$money . " back_cache:$back_cache back_before:$back_before");
                    }
                }
            }
        }
        else 
        {
            // 扣用户秀币
            if(0 < $money && 0 != $launch_uid)
            {
                $sql = "update rcec_main.user_attribute set coin_balance = coin_balance - $money where ( uid = $launch_uid && coin_balance >= $money )";
                $rows = $this->getDbChannellive()->query($sql);
                // if sql is update,query return true/false.
                if(!$rows)
                {
                    LogApi::logProcess("GameModel::launchGame:******excute sql error!!!**********sql:$sql");
                    return -1;
                }
                LogApi::logProcess("GameModel::launchGame uid:".$launch_uid." rcec_main.user_attribute.coin_balance"." delta:-".$money);
                // 
                $toolConsumRecordModel = new ToolConsumeRecordModel();
                
                $user = $uim->getInfoById($launch_uid);
                
                $unionId = (int)$user['union_id'];
                // 计入消费记录
                $info = new ToolConsumeRecordInfo();
                $info->now = time();
                $info->uid = $launch_uid;
                $info->singerUid = $singerid;
                $info->sid = $sid;// 房间id
                $info->cid = 1;// 频道id
                $info->tid = 0;// 道具id 互动游戏为0
                $info->tool_category1 = 0;// 道具一级目录 互动游戏为0
                $info->tool_category2 = 0;// 道具二级目录 互动游戏为0
                $info->qty = 1;// 数量 互动游戏为1
                $info->buy = 0;// 是不是直接在商城买的 互动游戏为0
                $info->tool_price = $money;
                $info->total_coins_cost = $money;
                $info->total_receiver_points = 0;// 接收这产生的秀点 互动游戏为0
                $info->total_receiver_charm = $money;
                $info->total_session_points = 0;// 互动游戏为0
                $info->total_session_charm = 0;// 互动游戏为0
                $info->baseValue = 0;// 主播基础分成
                $info->prizeValue = 0;// 主播奖金上限(主播奖金预算增加值)
                $info->backValue = 0;// 主播回馈基金分成值
                $info->unionTotalValue = 0;// 公会分成值 互动游戏为0
                $info->sysControl = 0;// 系统调控基金分成值 互动游戏为0
                $info->officialValue = $money;// 官方收入分成值 100%
                $info->unionId = $unionId;// 公会id
                $info->unionValue = 0;// 公会收入预算-公会收益 互动游戏为0
                $info->unionBack = 0;// 公会收入预算-公会回馈基金 互动游戏为0
                $info->unionPrize = 0;// 公会收入预算-公会奖金预算 互动游戏为0
                $info->unionSunValue = 0;// 公会增加阳光值 互动游戏为0
                $info->singerSunValue = 0;// 主播增加阳光值 互动游戏为0
                
                $toolConsumRecordModel->AppendToolConsumeRecordInfo($info);                
            }
        }
        
        $redisKey = "user_attribute:{$singerid}";
        $this->getRedisMaster()->del($redisKey);
        
        //保存主播当前正在玩的游戏id
        $gameIdKey = "gameId:".$singerid;
        $this->getRedisMaster()->set($gameIdKey, $id);
        
        //保存主播当前正在玩的游戏是否从预热入口
        $gamePreheatKey = "gamePreheat:".$id;
        $this->getRedisMaster()->set($gamePreheatKey, $preheat);
        
        // 如果摇色子，保存初始奖金：可以是预热奖金，可以是发起奖金
        if ($gameid == GameModel::$GAME_ID_DICE && !empty($money)) {
        	$diceGameInitMoney = "diceGameInitMoney:$id";
        	$this->getRedisMaster()->set($diceGameInitMoney, $money);
        }        

        //如果此主播当前可以添加系统金币，添加此金币到diceGameInitMoney
        if ($gameid == GameModel::$GAME_ID_DICE)
        {
			$num = 0;
			$sysGold = 0;
	    	if($this->CheckSidDiceCanAddSysGold($sid, $singerid, $num, $sysGold) && 0 == $num)
	    	{
				$this->SetDiceAddSysGold($id, $sysGold);
				

				//如果正常取消息，可以不扣次数，但预热了则次数扣除
				if(0 != $preheat)//预热方式 
				{
					$this->AddSidDiceAddSysGoldCount($sid, $singerid, 999);
				}
				else
				{
					$this->AddSidDiceAddSysGoldCount($sid, $singerid, 1);
					$money += $sysGold;
				}

				LogApi::logProcess("GameModel::launchGame sid: $sid  singerid:$singerid add dice sysGold:$sysGold money:$money preheat:$preheat");
	    	}
        }
        
        $guessGameMoneyKey = "guessGameMoney:$id";
        $this->getRedisMaster()->set($guessGameMoneyKey, $money);
        
        $diceGameMoneyKey = "diceGameMoney:".$id;
        $this->getRedisMaster()->set($diceGameMoneyKey, $money);
        
        $this->SetGameStatus($id, 1);
        
        LogApi::logProcess("end GameModel::launchGame****************该场游戏的唯一标识:$id");
        
        return $id;
    }
    
    //主播开始摇骰子游戏
    public function startDiceGame($id, $singerid, $gameid, &$man_floor)
    {
        LogApi::logProcess("begin GameModel::startDiceGame****************");
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
		
        $gameKey = "diceGameMoney:".$id;
        $money = $this->getRedisMaster()->get($gameKey);
        
        $flag = $this->startGame($id, $singerid, $gameid, $money, &$man_floor);
        if($flag != 0){
            LogApi::logProcess("GameModel::startDiceGame error!!!****************");
        } else {
        	LogApi::logProcess("end GameModel::startDiceGame****************money:$money"); 
        }
        
        return array(
        		'errcode' => $flag, 
        		'money' => $money
        );
    }
    
    //主播开始游戏
    public function startGame($id, $singerid, $gameid, $money, &$man_floor)
    {
        LogApi::logProcess("begin GameModel::startGame****************");
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
		
        $gameKey = "game:".$id;
        //$size = getRedisMaster()->sSize($gameKey);
        $size = $this->getRedisMaster()->sCard($gameKey);
        
        $topMan = 1;
        $query = "select * from card.parameters_info t where t.id = 115";
        $rows = $this->getDbMain()->query($query);
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $topMan = (int)$row['parm1'];
        }
        $man_floor = $topMan;
        //TODO:暂时屏蔽
        if($size < $topMan)
        {
            //如果小于10人则不能开始游戏
//             return false;
			return 1;
        }
        
        $now = time();

        // DBLE
        $sql = "UPDATE cms_manager.current_game SET status=1, money=$money, play_number=$size, start_time=$now WHERE id=$id";
        $db_channel = $this->getDbChannellive();
        $rows = $db_channel->query($sql);
        if(!$rows || $db_channel->affected_rows <= 0){
            LogApi::logProcess("[DBLElog] error GameModel::startGame****************sql:$sql");
            //return false;
            return -1;
        }
        
        LogApi::logProcess("end GameModel::startGame****************");

        $this->ClearPreheatingInfo($singerid);
        // when game start we hIncrBy the current number.
        $user_activity_limit_key = GameModel::HashUserActivityLimitKey($singerid,$now);
        $this->getRedisMaster()->hIncrBy($user_activity_limit_key, $gameid, 1);
        $this->getRedisMaster()->expire($user_activity_limit_key,GameModel::$USER_ACTIVITY_LIMIT_TTL);
        $this->SetGameStatus($id, 2);
        //return true;
        return 0;
    }
    
    //随机抽取你动我猜题库中的题
    public function getGuessQuetions($singerid, $id)
    {
        //获得题库总条数
        $sql = "select min(id) as id, count(id) as total from cms_manager.move_guess_questions";
        
        $rows = $this->getDbChannellive()->query($sql);
         
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $total = $row['total'];
            $seq_id = $row['id'];
        }
        
        $numbers = range ($seq_id,$total);
        //shuffle 将数组顺序随即打乱
        shuffle ($numbers);
        //array_slice 取该数组中的某一段
        $nums = array_slice($numbers,0,10);
        
        $in_sql = implode(',', $nums);
        $sql = "select id, question from cms_manager.move_guess_questions where id in($in_sql) and status = 1";
        $rows = $this->getDbChannellive()->query($sql);
        
        LogApi::logProcess("getGuessQuetions::****************sql:$sql");
         
        $result = array();
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            while ($row) {
                //选项
                $question = $row['question'];
                $questionArr = explode(',', $question);
                $index = mt_rand(0, sizeof($questionArr)-1);
                //获得题目
                $answer = $questionArr[$index];
                
                $item = array();
                $item['id'] = $row['id'];
                $item['question'] = $question;
                $item['answer'] = $answer;
                $result['items'][] = $item;
                
                $row = $rows->fetch_assoc();
            }
        }
        
        //把生成的题目保存到redis里，当结束时，清空
        $gamekey = "game_questions:".$id;
        
        $this->getRedisMaster()->set($gamekey, json_encode($result));
        
        return $result;
    }
    public static function HashSingerGameStatusKey($uid)
    {
        $mod = (int)($uid % GameModel::$GAME_USER_MOD_NUMBER);
        return "singer:game:status:$mod";
    }
    public function GetSingerGameStatus($uid)
    {
        $status = 0;
        
        $key = GameModel::HashSingerGameStatusKey($uid);
        do 
        {
            $obj_string = $this->getRedisMaster()->hGet($key,$uid);
            if (empty($obj_string))
            {
                break;
            }
            $obj = json_decode($obj_string);
            LogApi::logProcess("GetSingerGameStatus status:".$obj_string);
            if(
                !property_exists($obj,"status") || 
                !property_exists($obj,"timecode"))
            {
                break;
            }
            $timecode_now = time();
            $status = (int)$obj->status;
            $timecode = (int)$obj->timecode;
            $dt = $timecode_now - $timecode;
            if (empty($status) || $dt >= GameModel::$GAME_STATUS_RESET_TIME)
            {
                $status = 0;
            }            
        }while(FALSE);
        return $status;
    }
    public function SetSingerGameStatus($uid,$status)
    {
        $obj = array
        (
            'status'=>$status,
            'timecode'=>time(),
        );
        $key = GameModel::HashSingerGameStatusKey($uid);
        $status = $this->getRedisMaster()->hSet($key,$uid,json_encode($obj));
    }
    // uid singer id.
    public function ClearPreheatingInfo($uid)
    {
        $this->ClearPreheatingInfoRedis($uid);
        $this->ClearPreheatingInfoMySQL($uid);
    }
    public static function StringAnchorActiveGameKey($uid)
    {
        return "anchor_active_game_$uid";
    }
    public function ClearPreheatingInfoRedis($uid)
    {
        $key = GameModel::StringAnchorActiveGameKey($uid);
        $this->getRedisJavaUtil()->del($key);
        $this->getRedisMaster()->del($key);
        
        $key = "game:ready:count_down:$uid";
        $this->getRedisMaster()->del($key);
    }
    public function ClearPreheatingInfoMySQL($uid)
    {
        $clear_limit = 1000;
        $sql = "UPDATE cms_manager.game_ready_info gri SET gri.status = 0 WHERE gri.uid = $uid ORDER BY gri.time DESC LIMIT $clear_limit";
        $rows = $this->getDbMain()->query($sql);
        if ( !$rows )
        {
            LogApi::logProcess("ClearPreheatingInfoMySQL::****************sql:$sql");
        }
    }
    public function ClearSingerGameStatusMysql($uid)
    {        
        // 0:发起游戏 1:游戏中 2:游戏结束 3:备战中
        $close_status = 2;
        $clear_limit = 1000;
        $sql = "UPDATE cms_manager.current_game gri SET gri.status = $close_status WHERE gri.zbid = $uid ORDER BY gri.id DESC LIMIT $clear_limit";
        $rows = $this->getDbMain()->query($sql);
        if ( !$rows )
        {
            LogApi::logProcess("ClearSingerGameStatusMysql::****************sql:$sql");
        }        
    }
    
    public function IfCanRollDice($id, $uid)
    {
    	$this->InitConfigDB();
    	if ($this->ROLL_DICE_COUNT_LIMIT == -1) {
    		return true;
    	}
    	
    	$timesKey = 'diceGame_times:'.$id;
    	$num = $this->getRedisMaster()->zScore($timesKey, $uid);
    	
    	if ($this->ROLL_DICE_COUNT_LIMIT > $num) {
    		return true;
    	}
    	
    	return false;
    }
    
    // 是否可以报名摇色子游戏
    public function IfCanEnRollDice($id, $uid)
    {
    	$this->InitConfigDB();
    	if ($this->ROLL_DICE_ENTER_LIMIT == -1) {
    		return true;
    	}
    	
    	$gameKey = "game:".$id;
    	$size = $this->getRedisMaster()->sCard($gameKey);
    	
    	if ($this->ROLL_DICE_ENTER_LIMIT > $size) {
    		return true;
    	}
    	
    	return false;
    }
    
    // 根据当前正在进行的游戏标识，获取游戏id
    public function GetCurGameId($id, $status)
    {
    	$game_id = -1;
    	$sql = "select game_id from cms_manager.current_game where id = $id and status=$status";
    	$mysql = $this->getDbChannellive();
    	$rows = $mysql->query($sql);
    	if ($rows && $rows->num_rows > 0) {
    		$row = $rows->fetch_assoc();
    		$game_id = $row['game_id'];
    	}
    	
    	return $game_id;
    }
    
    public function GetSingerLaunchGame($singerid, $uid)
    {
    	// 判断主播是否正在游戏
    	$result =  array();
    	do { 
    		// 获取当前游戏信息
    		$id = $this->getGameIdBySingerId($singerid);
    		if (empty($id)) {
    			break;
    		}
    		 
    		$game_id = $this->GetCurGameId($id, 0);
    		if ($game_id == -1) {
    			break;
    		}
    		 
    		$row = $this->getGameInfo($game_id);
    		if (empty($row) || $row['status'] != 1) {
    			break;
    		}
    		
    		$gameKey = "game:".$id;
    		$size = $this->getRedisMaster()->sCard($gameKey);
    		$enterd = $this->getRedisMaster()->sIsMember($gameKey, $uid);
    		
    		$gameGiftIdKey = "gameGiftId:".$id;
    		$giftId = $this->getRedisMaster()->get($gameGiftIdKey);
    		
    		if ($game_id == GameModel::$GAME_ID_DICE) {
    			$result['giftId'] = $giftId;
    			$this->InitConfigDB();
    			$result['enterLimit'] = $this->ROLL_DICE_ENTER_LIMIT;
    			$diceGameMoneyKey = "diceGameMoney:".$id;
    			$totalMoney = $this->getRedisMaster()->get($diceGameMoneyKey);
    		} else if ($game_id == GameModel::$GAME_ID_GUESS) {
    			$guessGameMoneyKey = "guessGameMoney:$id";
    			$totalMoney = $this->getRedisMaster()->get($guessGameMoneyKey);
    		}
    		
    		$result['id'] = (int)$id;
    		$result['singerid'] = $singerid;
    		$result['gameid'] = (int)$game_id;
    		$result['gameName'] = $row['name'];
    		$result['imgurl'] = $row['img_name'];
    		$result['gameType'] = $row['type'];
    		$result['entered'] = $enterd;
    		$result['userCount'] = $size;
    		$result['totalMoney'] = (int)$totalMoney;
    		$result['extraMoney'] = $this->GetDiceAddSysGold($id);
    	} while (0);
    	 
    	return $result;
    }
    
	// 获取摇色子游戏初始奖金:预热奖金+5*giftPrice
	// 只有主播发起游戏时调用一次
    public function GetDiceInitMoney($id, $giftPrice)
    {
    	$man_floor = $this->GetGameManfloor();
    	$diceGameMoneyKey = "diceGameMoney:".$id;
    	$totalMoney = $this->getRedisMaster()->incrBy($diceGameMoneyKey, $giftPrice*$man_floor);
    	return (int)$totalMoney;
    }
    
    // 获取你演我猜游戏初始奖金：有可能是预热奖金也有可能是主播发起奖金
    public function GetGuessInitMoney($id) 
    {
    	$guessGameMoneyKey = "guessGameMoney:$id";
    	return (int)$this->getRedisMaster()->get($guessGameMoneyKey);
    }
    
    // 获取小游戏开始所需最少人数
    private function GetGameManfloor()
    {
    	$topMan = 1;
    	$query = "select * from card.parameters_info t where t.id = 115";
    	$rows = $this->getDbMain()->query($query);
    	if ($rows && $rows->num_rows > 0) {
    		$row = $rows->fetch_assoc();
    		$topMan = (int)$row['parm1'];
    	}
    	
    	return $topMan;
    }
    
    // 清除摇色子缓存信息
    public function ClearDiceGameCache($id, $singerid)
    {
    	// 清空 奖金池
    	$gameKey = "diceGameMoney:".$id;
    	$this->getRedisMaster()->del($gameKey);
    	
    	// 清空初始奖金
    	$gameKey = "diceGameInitMoney:$id";
    	$this->getRedisMaster()->del($gameKey);
    	
    	// 清空已报名人数信息
    	$gameKey = "game:".$id;
    	$this->getRedisMaster()->del($gameKey);
    	
    	// 清空已摇次数信息
    	$gameKey = 'diceGame_times:'.$id;
    	$this->getRedisMaster()->del($gameKey);
    	 
    	// 清空门票信息
    	$gameKey = "diceGameTickets:".$id;
    	$this->getRedisMaster()->del($gameKey);
    	
    	// 清空预热标志
    	$gameKey = "gamePreheat:".$id;
    	$this->getRedisMaster()->del($gameKey);
    	
    	// 清空游戏状态
    	$this->SetSingerGameStatus($singerid, 0);
    	
    	$this->ClearGameStatus($id);
    }
    
    // 用户是否已经报名
    public function IfEntered($id, $uid)
    {
    	$gameKey = "game:".$id;
    	return $this->getRedisMaster()->sIsMember($gameKey, $uid);
    }

    public function LockGameOper($id, $uid, $field, $timeout = 0)
    {
        $key = "lockGame:$uid:" . $id;
        $num = $this->getRedisMaster()->hIncrBy($key, $field, 1);
        if (!empty($timeout)) {
            $this->getRedisMaster()->expire($key, $timeout);
        }
        return $num;
    }

    public function UnLockGameOper($id, $uid, $field)
    {
        $key = "lockGame:$uid:" . $id;
        return $this->getRedisMaster()->hIncrBy($key, $field, -1);
    }

    public function DestoryLockGameOper($id, $uid, $field)
    {
        $key = "lockGame:$uid:" . $id;
        $this->getRedisMaster()->hDel($key, $field);
    }
    
    // status: 1-发起	2-已开始		3-已取消		4-已结束
    public function SetGameStatus($id, $stat)
    {
    	$key = "game:current:status:$id";
    	return $this->getRedisMaster()->set($key, $stat);
    }
    
    public function GetGameStatus($id)
    {
    	$key = "game:current:status:$id";
    	return $this->getRedisMaster()->get($key);
    }
    
    public function ClearGameStatus($id)
    {
    	$key = "game:current:status:$id";
    	return $this->getRedisMaster()->del($key);
    }
    
    public function ClearUserGameRelById($id)
    {
    	$gameKey = "game:".$id;
    	$mems = $this->getRedisMaster()->sMembers($gameKey);
    	foreach ($mems as $uid) {
    		$key = "user:$uid" . ":enroll:game";
    		$this->getRedisMaster()->del($key);
    	}	
    }

    public function SaveGameRanking($id, $data)
    {
        $key = "game:ranking:". $id;
        $j_data = json_encode($data);
        $this->getRedisMaster()->setex($key, 60*60, $j_data);
    }

    public function GetGameRanking($id)
    {
        $key = "game:ranking:". $id;
        $j_data = $this->getRedisMaster()->get($key);

        if (!empty($key)) {
            return json_decode($j_data);
        }

        return null;
    }

    public function SaveGuessGameQues($id, $data)
    {
        $key = "game:guess:qs". $id;
        $j_data = json_encode($data);
        $this->getRedisMaster()->setex($key, 60*60*24, $j_data);
    }

    public function GetGuessGameQues($id)
    {
        $key = "game:guess:qs". $id;
        $j_data = $this->getRedisMaster()->get($key);

        if (!empty($key)) {
            return json_decode($j_data);
        }

        return null;
    }
    
    public function GetDiceGameMoney($id)
    {
    	$gameKey = "diceGameMoney:".$id;
    	return $this->getRedisMaster()->get($gameKey);
    }

	//得到筛子添加系统金币对应配制信息
	public function GetDiceSysGoldConfigInfo()
	{
		$result = 0;
		$ConfigInfo = Array();
	
		//从redis中查找
		$key = "DiceSysGoldConfigInfo";
    	$strConfigInfo = $this->getRedisMaster()->get($key);
		if(empty($strConfigInfo))
		{	
	        $sql = "SELECT start_hour, end_hour, sys_gold FROM card.`dice_sys_gold_parameters`";
	        $rows = $this->getDbMain()->query($sql);
	        $db_array = array();
	        if ( $rows )
	        {
	            if ( 0 < $rows->num_rows )
	            {
	                for ($x=0; $x < $rows->num_rows; $x++)
	                {
	                    $row = $rows->fetch_assoc();

						$itemInfo = Array();
						$itemInfo["start_hour"] = $row['start_hour'];
						$itemInfo["end_hour"] = $row['end_hour'];
						$itemInfo["sys_gold"] = $row['sys_gold'];

						$ConfigInfo[] = $itemInfo;
	                }
	                $this->getRedisMaster()->set($key, json_encode($ConfigInfo));

	                LogApi::logProcess("GameModel::GetDiceSysGoldConfigInfo db ConfigInfo:".json_encode($ConfigInfo));
	            }
	        }
		}
		else
		{
			$ConfigInfo = json_decode($strConfigInfo);
		}		
	
		//找到当前时间点的配制
		$hour = date("H");
		$len = count($ConfigInfo);
        for($iPos = 0; $len > $iPos; ++$iPos)
        {
			if($hour >= $ConfigInfo[$iPos]->start_hour && $hour < $ConfigInfo[$iPos]->end_hour)
			{
				$result = $ConfigInfo[$iPos]->sys_gold;
				break;
			}
        }

        LogApi::logProcess("GameModel::GetDiceSysGoldConfigInfo sysGold:".$result);

		return $result;
	}

	//得到筛子对应上头条相关的房间信息对应的key
	public function GetDiceHeadLineSidInfoKey()
	{
		$resultKey = "";
	
		$headlineStartTime = $this->getRedisMaster()->get("headlineStartTime");
		if(empty($headlineStartTime) || (intval(strtotime("today")) > intval($headlineStartTime) || intval(strtotime("tomorrow")) <= intval($headlineStartTime)))
		{
			LogApi::logProcess("GameModel::GetDiceHeadLineSidInfoKey failed! headlineStartTime:".$headlineStartTime);
			return $resultKey;
		}
		
		$resultKey = "DiceHeadLineSidInfoKey:".$headlineStartTime;
		
		return $resultKey;
	}
	
	//判断某主播此次开筛子是否上可以添加系统金币, 并得到其在此次上头条后他的直播间摇筛子次数
	public function CheckSidDiceCanAddSysGold($sid, $singerID, &$num, &$sysGold)
	{
		$num = 0;
		$sysGold = 0;
		
		//判断此时段是否可以加系统金币
		$sysGold = $this->GetDiceSysGoldConfigInfo();
		if(empty($sysGold))
		{
			return false;
		}

		//是否主播上了头条
		$key = "";
		$headlineUid = 0;
		{
			$key = $this->GetDiceHeadLineSidInfoKey();
			if(empty($key))
			{
				return false;
			}

			$expireTime = $this->getRedisMaster()->get("length");
			if(empty($expireTime))
			{
				return false;
			}

			$headlineUid = $this->getRedisMaster()->get("anchorTopLine");
			if(empty($headlineUid) || intval($headlineUid) != intval($singerID))
			{
				LogApi::logProcess("GameModel::CheckSidDiceCanAddSysGold headlineUid:".$headlineUid);
				return false;
			}
		}

		$numValue = $this->getRedisMaster()->hGet($key, $singerID);
		if(!empty($numValue))
		{
			$num = intval($numValue);
		}		

		return true;
	}

	//增加主播此上头条期间的摇筛子次数
	public function AddSidDiceAddSysGoldCount($sid, $singerID, $num)
	{
		$key = $this->GetDiceHeadLineSidInfoKey();
		if(empty($key))
		{
			return false;
		}

		$expireTime = $this->getRedisMaster()->get("length");
		if(empty($expireTime))
		{
			return false;
		}

		$headlineUid = $this->getRedisMaster()->get("anchorTopLine");
		if(empty($headlineUid) || intval($headlineUid) != intval($singerID))
		{
			LogApi::logProcess("GameModel::AddSidDiceAddSysGoldCount headlineUid:".$headlineUid);
			return false;
		}

		$this->getRedisMaster()->hIncrBy($key, $singerID, $num);
		$this->getRedisMaster()->expire($key, $expireTime * 60);

		LogApi::logProcess("GameModel::AddSidDiceAddSysGoldCount key: $key  singerID: $singerID");

		return true;
	}

	//减少主播此上头条期间的摇筛子次数（筛子游戏取消息）
	public function SubSidDiceAddSysGoldCount($singerID, $num)
	{
		$key = $this->GetDiceHeadLineSidInfoKey();
		if(empty($key))
		{
			return false;
		}

		$expireTime = $this->getRedisMaster()->get("length");
		if(empty($expireTime))
		{
			return false;
		}

		//是否是此主播上头条
		$headlineUid = $this->getRedisMaster()->get("anchorTopLine");
		if(empty($headlineUid) || intval($headlineUid) != intval($singerID))
		{
			LogApi::logProcess("GameModel::SubSidDiceAddSysGoldCount headlineUid:".$headlineUid);
			return false;
		}

		//检测是否存在此$sid
		if(0 == intval($this->getRedisMaster()->hExists($key, $singerID)))
		{
			LogApi::logProcess("GameModel::SubSidDiceAddSysGoldCount not hExists! singerID: $singerID");
			return false;
		}

		$this->getRedisMaster()->hIncrBy($key, $singerID, 0 - $num);
		$this->getRedisMaster()->expire($key, $expireTime * 60);

		LogApi::logProcess("GameModel::SubSidDiceAddSysGoldCount key: $key  singerID: $singerID");

		return true;
	}
	
	//得到系统增加的筛子金币（上头条业务相关）对应的key
	public function GetDiceAddSysGoldKey($id)
	{
		return "diceGameHeadlineSysGold:$id";
	}

	//设置系统增加的筛子金币（上头条业务相关）对应值
	public function SetDiceAddSysGold($id, $sysGold)
	{
		$key = $this->GetDiceAddSysGoldKey($id);
		$this->getRedisMaster()->set($key, $sysGold);
		$this->getRedisMaster()->expire($key, 3600 * 24);

		return true;
	}

	//得到系统增加的筛子金币（上头条业务相关）对应值
    public function GetDiceAddSysGold($id)
	{
		$key = $this->GetDiceAddSysGoldKey($id);
		$sysGold = $this->getRedisMaster()->get($key);
		if(empty($sysGold)) $sysGold = 0;

		LogApi::logProcess("GameModel::GetDiceAddSysGold sysGold: $sysGold");

		return intval($sysGold);
	}
}
?>
