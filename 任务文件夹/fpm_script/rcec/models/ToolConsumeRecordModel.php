<?php

class ToolConsumeRecordInfo
{
    public $now = 0;
    public $uid = 0;
    public $singerUid = 0;
    public $sid = 0;
    public $cid = 0;
    public $tid = 0;
    public $tool_category1 = 0;
    public $tool_category2 = 0;
    public $qty = 0;
    public $buy = 0;
    public $tool_price = 0;
    public $total_coins_cost = 0;
    public $total_receiver_points = 0;
    public $total_receiver_charm = 0;
    public $total_session_points = 0;
    public $total_session_charm = 0;
    public $baseValue = 0;
    public $prizeValue = 0;
    public $backValue = 0;
    public $unionTotalValue = 0;
    public $sysControl = 0;
    public $officialValue = 0;
    public $unionId = 0;
    public $unionValue = 0;
    public $unionBack = 0;
    public $unionPrize = 0;
    public $unionSunValue = 0;
    public $singerSunValue = 0;    
};
class WeekToolConsumeRecordInfo
{
    public $now = 0;
    public $uid = 0;
    public $singerUid = 0;
    public $tid = 0;
    public $tool_category1 = 0;
    public $tool_category2 = 0;
    public $qty = 0;
    public $tool_price = 0;
    public $total_coins_cost = 0;
};

class ToolConsumeRecordModel extends ModelBase
{
	const USER_GOOD_ADD_SRC_BOX 			= 0;//开宝箱
	const USER_GOOD_ADD_SRC_GANG_SIGN_IN 	= 1;//帮会签到
	const USER_GOOD_ADD_SRC_PORTAL 			= 2;//传送门
	const USER_GOOD_ADD_SRC_SAW 			= 3;//电锯
	const USER_GOOD_ADD_SRC_CLIMB_TOWER 	= 4;//爬塔
	const USER_GOOD_ADD_SRC_LORD 			= 5;//擂主
	const USER_GOOD_ADD_SRC_TASK 			= 6;//任务
	const USER_GOOD_ADD_SRC_MARK 			= 7;//市场收入
	const USER_GOOD_ADD_SRC_REDPACKET 		= 8;//红包收入
	const USER_GOOD_ADD_SRC_GANG_EX 		= 9;//帮会兑换
	const USER_GOOD_ADD_SRC_ANCHOR_EX 		= 10;//主播兑换
	const USER_GOOD_ADD_SRC_DICE 			= 11;//骰子
	const USER_GOOD_ADD_SRC_GUESS 			= 12;//猜猜
	
	const GOODS_ID_GOLD						= 10;//物品金币
// 	private $redisMaster = NULL;
    //消费奖励概率
    public $rewardProbabilityList = array(
        array(
            'consume' => 5000, //消费满5000
            'probability' => 5 //概率是5
        ),
        array(
            'consume' => 10000,
            'probability' => 8
        ),
        array(
            'consume' => 50000,
            'probability' => 20
        ),
        array(
            'consume' => 100000,
            'probability' => 28.12
        ),
        array(
            'consume' => 1000000,
            'probability' => 28.12
        ));
    
    //消费奖励概率
    public $rewardConfList = array(
        array(
            'price' => 3000, //价值3000
            'desc' => "1级付费宝箱",
            'boxid' => 991 //宝箱id
        ),
        array(
            'price' => 10000, //价值3000
            'desc' => "2级付费宝箱",
            'boxid' => 992 //宝箱id
        ),
        array(
            'price' => 30000, //价值3000
            'desc' => "3级付费宝箱",
            'boxid' => 993 //宝箱id
        ),
        array(
            'price' => 100000, //价值3000
            'desc' => "4级付费宝箱",
            'boxid' => 994 //宝箱id
        ),
        array(
            'price' => 300000, //价值3000
            'desc' => "5级付费宝箱",
            'boxid' => 995 //宝箱id
        ),
        array(
            'price' => 1000000, //价值3000
            'desc' => "6级付费宝箱",
            'boxid' => 996 //宝箱id
        ));

    public function __construct()
    {
		LogApi::logProcess('***** public function __construct()');
        parent::__construct();
    }
    
    //获得在线赠送的礼物信息
    public function getOnlineGiftInfo(){
    	$hashobj = 'loginsign_giftrule';
    	//获得用户集合
    	$keys = $this->getRedisMaster()->hkeys($hashobj);
    	
    	LogApi::logProcess('**********ToolConsumeRecordModel::getOnlineGiftInfo:keys::'. json_encode($keys));
    	
    	$sortArray = array();
    	foreach ($keys as $key){
    		$tmp = explode(":", $key);
    		$sortArray[] = (int)$tmp[1];
    	}
    	
    	sort($sortArray);
    	
    	$value = array();
    	foreach ($sortArray as $key){
    		$key = '3:'.$key;
    		$value[] = $this->getRedisMaster()->hget($hashobj, $key);
    	}
    	LogApi::logProcess('**********ToolConsumeRecordModel::getOnlineGiftInfo:value::'. json_encode($value));
    	
    	return $value;
    }
    
    //初始化礼物规则表'loginsign_giftrule'
    public function initGiftRule(){
		
		LogApi::logProcess('initGiftRule IN aaaaaaaaaa... ');
		
    	$hashobj = 'loginsign_giftrule';
    	//获得用户集合
		
		try{
			$keys = $this->getRedisMaster()->hkeys($hashobj);
        }catch(Exception $e){
			 
            LogApi::logProcess("!!!!!!!!get exception:".$e->getMessage());
        }
		
		LogApi::logProcess('initGiftRule 0 ');
    	
    	LogApi::logProcess('**********ToolConsumeRecordModel::initGiftRule:keys::'. json_encode($keys));
    	
    	if(empty($keys))
		{
			
			LogApi::logProcess('initGiftRule 1 ');
			
    		//todo:查询产生礼物规则表，并缓存到redis里
    		$query = "select * from loginsign_giftrule t where t.fromtype = 3 order by t.fromnum";
    		$rs = $this->getDbMain()->query($query);
    		
            if ($rs && $rs->num_rows > 0) {
                $row = $rs->fetch_assoc();
                while ($row) {
                    $rows[] = $row;
                    $row = $rs->fetch_assoc();
                }
            }
    		
			LogApi::logProcess('initGiftRule 2 ');
    		LogApi::logProcess('**********ToolConsumeRecordModel::initGiftRule:queryResult::' . json_encode($rows));
    		
    		if (!empty($rows)){
				LogApi::logProcess('initGiftRule 3 ');
    			$tmp = array();
		        foreach ($rows as $row){
		        	$tmp['fromtype'] = $row['fromtype'];
		        	$tmp['fromnum'] = $row['fromnum'];
		        	$tmp['giftid'] = $row['giftid'];
		        	$tmp['giftnum'] = $row['giftnum'];
		        	$tmp['jinbinum'] = $row['jinbinum'];
		        	$tmp['isclosed'] = $row['isclosed'];
		        	$tmp['ruledesc'] = $row['ruledesc'];
		        	$tmp['giftimgurl'] = $row['giftimgurl'];
		        	$tmp['jinbiimgurl'] = $row['jinbiimgurl'];
		        	
		        	if($row['giftid'] == 0){//金币
		        		$tmp['gifttype'] = 0;
		        	}else{//金币礼物
		        		$tmp['gifttype'] = 1;
		        	}
					
					LogApi::logProcess('initGiftRule 4 ');
		        	
		        	$key = $row['fromtype'].':'.$row['fromnum'];
					
					LogApi::logProcess('initGiftRule 5 ');
		        	
		        	$this->getRedisMaster()->hset($hashobj, $key, json_encode($tmp));
					
					LogApi::logProcess('initGiftRule 6 ');
		        	
		        	LogApi::logProcess('**********ToolConsumeRecordModel::initGiftRule:$key::'. $key . '  value::' . json_encode($tmp));
		        }
    		}
    		
			LogApi::logProcess('initGiftRule 7 ');
			
            if($rs){
            	$rs->close();
            }
    		
    	}
		
		LogApi::logProcess('initGiftRule OUT ');
    }
    
    //创建未领取的礼物记录
    public function createGift($uid, $type){
    	
		//查询领取礼物sql
	    $datetime='\''.date('Y-m-d').' 05:00:00\'';
    	$query = "select fromnum from user_signgift_history h  where (date_format(FROM_UNIXTIME(createtime),'%Y-%m-%d %H:%i:%S'))>=$datetime and fromtype = 3 and uid = ".$uid;
    	
    	LogApi::logProcess("************ToolConsumeRecordModel::createGift::query::".$query);
    	
        $rs = $this->getDbMain()->query($query);
    	if ($rs && $rs->num_rows > 0) {
            $row = $rs->fetch_assoc();
            while ($row) {
                $rows[] = $row;
                $row = $rs->fetch_assoc();
            }
        }
        
        if (!empty($rows)){
	        foreach ($rows as $row) {
	        	if($type == $row['fromnum']){ //礼物已经产生，直接返回成功
	        		//礼物已经被领取
	            	return ture;
	        	}
	        }
        }
    
    	$hashobj = 'loginsign_giftrule';
    	//fromtype:fromnum
    	$key = '3:'.$type;
    	//获得用户集合
    	$value = $this->getRedisMaster()->hget($hashobj, $key);
    	
    	LogApi::logProcess('**********ToolConsumeRecordModel::createGift:value::'. $value);
    	
    	$values = array();
    	//
    	if (!empty($value)) {
    		$result = json_decode($value, TRUE);
    		$gifttype = $result['gifttype'];
		    if($gifttype == 1) {
		    	$num = $result['giftnum'];
		    }else{
		    	$num = $result['jinbinum'];
		    }
		    $senddesc = '\''.$result['ruledesc'].'\'';
		    $ts = time();
		    $tid = $result['giftid'];
    		 
            array_push($values, "($uid, 3, $type, $tid, $num, 1, $senddesc, 0, $ts, $gifttype)");
            
	    	//fromtype:礼物来源，1=连续签到天数，2=月度累计签到天数，3=直播间在线时长
	    	//fromnum:赠送所需要数量。可为连续签到次数，月度累计天数，直播间在线时长
	    	//sendtoolid：礼物id 从缓存里读
	    	//sendnum：数量。金币数量，或礼物数量 从缓存里读
	    	//isreceive:是否已经领取。1=没有，2=已经领取。（注意不用0，怕联合查询为空时默认值为0，影响程序判断）
	    	//senddesc:赠送规则说明
	    	//receivetime:领取时间
	    	//createtime:创建时间
	    	//sendtype:赠送礼物类型。0为金币，1=礼物道具
	        $query = "INSERT INTO user_signgift_history (uid,fromtype,fromnum,sendtoolid,sendnum,isreceive,senddesc,receivetime,createtime,sendtype)VALUES" . implode($values, ", ");
	        
	        LogApi::logProcess('**********ToolConsumeRecordModel::createGift:query::' . $query);
	        
	        $rs = $this->getDbMain()->query($query);
	        if(!$rs){
	        	LogApi::logProcess('**********ToolConsumeRecordModel::createGift:插入数据失败.');
	        	// 执行失败
	        	return false;
	        }
    	}
        
        return true;
    }
    
    //消费金币
    public function consumeGoldcoin($uid, $tool, $qty){
        //$tool['price']:礼物价格  
        $total_coins_cost = $tool['price'] * $qty;
        
        // 扣金币
        $query = "UPDATE user_attribute SET jinbi_point = jinbi_point - $total_coins_cost
            WHERE uid =$uid AND jinbi_point >= $total_coins_cost";
       	
       	LogApi::logProcess('**********ToolConsumeRecordModel::consumeGoldcoin::query:'.$query);
       	
        $rs1 = $this->getDbMain()->query($query);
        if (!$rs1) {
        	LogApi::logProcess('**********ToolConsumeRecordModel::consumeGoldcoin:扣除金币失败.');
            return false;
        }
        
        return true;
    }
    
    //消费阳光
    public function consumeSunValue($sid, $uid, $singerid, $sunvalue){
        $redisKeys = array();
        
        $query = "select t.id, t.parm3 from card.parameters_info t where t.id = 37";
        $rows = $this->getDbMain()->query($query);
                
        $value = 0.1;
        
        if ($row = $rows->fetch_assoc()) {
            $id = (int)$row['id'];
            $value = floatval($row['parm3']);
            
            LogApi::logProcess("consumeSunValue row:".json_encode($row));
        } else {
        	LogApi::logProcess("consumeSunValue error sql: $query, result:".json_encode($rows));
        }
        
        $active = $sunvalue*$value;
        
        $model_tool = new ToolModel();
        $expDouble = 1;
        $expDoubleInf = $model_tool->expActiveDoubleCardEffect($uid);
        if (!empty($expDoubleInf)) {
        	$expDouble = $expDoubleInf['multiple'];
        	$active = $active * $expDouble;
        }
        
        LogApi::logProcess("consumeSunValue uid:$uid sunvalue:$sunvalue, index:$value, new active:$active double:$expDouble");
        
        // DBLE
        $db_main = $this->getDbMain();
        $date = date("Y-m-d");
        $sql = "SELECT uid FROM rcec_record.user_active_record WHERE uid=$uid AND createtime='$date'";
        $rows = $db_main->query($sql);
        if (!empty($rows) && $rows->num_rows > 0) {
            $sql = "UPDATE rcec_record.user_active_record SET active_point=active_point+$active";
        } else {
            $sql = "INSERT INTO rcec_record.user_active_record (uid, active_point, createtime) VALUES($uid, $active, '$date')";
        }

        $rows = $db_main->query($sql);
        if (empty($rows) || $db_main->affected_rows <= 0) {
            LogApi::logProcess("[DBLElog] consumeSunValue error sql:$sql");
        }
        
        $userAttrModel = new UserAttributeModel();
        // 用户活跃等级
        {
	        $userAttr = $userAttrModel->getAttrByUid($uid);
	        $userAttrModel->on_user_active_exp_add($uid, $userAttr['active_level'], $userAttr['active_exp'], $active);
        }
        
        // 主播阳光等级
        {
	        $model_channelive = new ChannelLiveModel();
	        $anchor_info = $model_channelive->getSingerAnchorInfo($singerid);
	        $userAttrModel->on_anchor_sun_exp_add($singerid, $anchor_info['level_id'], $anchor_info['anchor_curr_exp'], $sunvalue);
        }
        
        $now = time();
        $model_uinfo = new UserInfoModel();
        $family_id = $model_uinfo->GetSingerFamilyId($singerid);
        $query = "insert into rcec_record.sun_record (sid, uid, zid, time, num, family_id) values($sid, $uid, $singerid, $now, $sunvalue, $family_id)";
        $rs2 = $this->getDbMain()->query($query);
        if (!$rs2) {
            LogApi::logProcess("consumeSunValue error sql:$query");
            //return false;
        }
        
        $redisKeys[] = "user_attribute:{$singerid}";
        $this->getRedisMaster()->del($redisKeys);
        
        return true;
    }
    
    //添加秀币
    public function addCoin($uid, $coinCost){
        
        // 扣秀币
        $query = "UPDATE user_attribute SET coin_balance = coin_balance + $coinCost
            WHERE uid =$uid";
        $redisKey = "user_attribute:{$uid}";
        
       	LogApi::logProcess('**********ToolConsumeRecordModel::addCoin::query:'.$query);
       	
        $rs1 = $this->getDbMain()->query($query);
        if (!$rs1) {
        	LogApi::logProcess('**********ToolConsumeRecordModel::addCoin:添加秀币失败.');
            return false;
        }
        
        $this->getRedisMaster()->del($redisKey);
        
        return true;
    }
    
    //消费奖励
    public function getConsumeRewards($uid, $totalConsume){
        $data = array();
        $money = 0;
        
        for ($i = count($this->rewardConfList) - 1; $i >= 0; $i--) {
            $price = $this->rewardConfList[$i]['price'];
            if ($totalConsume >= $price) {
                $times = floor($totalConsume/$price);
                $remain = $totalConsume-$price*$times;
                $dropid = $this->rewardConfList[$i]['boxid'];
                
                $now = time()*1000;
                $sql = "INSERT INTO card.user_all_box (drop_id,good_id,uid,create_time,status, type) select $dropid, good_id, $uid, $now, 1, 2 from card.treasure_box_info where id = $dropid";
                $rs = $this->getDbMain()->query($sql);
                if ($rs) {
                    $sql = "select LAST_INSERT_ID() as id";
                    $rows = $this->getDbMain()->query($sql);
                     
                    if($rows && $rows->num_rows > 0){
                        $row = $rows->fetch_assoc();
                        $boxid = (int)$row['id'];
                        LogApi::logProcess("执行插入宝箱后，获得的主键id：$boxid, sql:$sql");
                    }else{
                        LogApi::logProcess("getConsumeRewards::获取宝箱id失败。 excute sql error, sql:$sql");
                    }
                    
                    //扣用户财富值
                    $sql = "UPDATE rcec_main.user_attribute SET con_incen_dedu = $remain WHERE uid = $uid";
                    $rs3 = $this->getDbMain()->query($sql);
                    if ($rs3) {
                        $data['boxid'] = $boxid;
                        $data['times'] = $times;
                        $redisKey = "user_attribute:{$uid}";
                        $this->getRedisMaster()->del($redisKey);
                    }else{
                        LogApi::logProcess('exe sql error, sql:'.$sql);
                    }
                }else{
                    LogApi::logProcess('exe sql error, sql:'.$sql);
                }
                
                break;
            }
        }
        return $data;
    }
    
    public function getConsumeRewards2($uid, $totalConsume){
        $data = array();
        $money = 0;
        
        $prices = array();
        $dropids = array();
        //$sql = "select * from card.parameters_info t where t.id in(101,102,103)";
        //$rows = $this->getDbMain()->query($sql);
        //$i = 0;
        //while ($rows && $row = $rows->fetch_assoc()) {
        //    $prices[$i] = (int)$row['parm1'];
        //    $dropids[$i] = (int)$row['parm2'];
        //    $i++;
        //}
        
        //更改宝箱掉落配置表2018-2-7 ,配置表单独列出 card.parameters_info，后续添加新配置无须更改程序，取库间隔1天，平时取redis缓存，失效重新加载库。  
        $key_chest_info = "consume_chest_info_201802_hash";
        $valuechest_info = $this->getRedisMaster()->hget($key_chest_info,0);
        if (!empty($valuechest_info))
        {            
            for($i_red=0;$i_red<50;)
            {
                $valuechest_info_red = $this->getRedisMaster()->hget($key_chest_info,$i_red);
                $data_red = json_decode($valuechest_info_red, true);
                $prices[$i_red]=$data_red['prices'];
                $dropids[$i_red]=$data_red['dropids'];
                //LogApi::logProcess("ljphptest:get redis i_red:$i_red prices[$i_red]:$prices[$i_red]  dropids[$i_red]:$dropids[$i_red]");
                $i_red++;
                $valuechest_info_break = $this->getRedisMaster()->hget($key_chest_info,$i_red);                
                if(empty($valuechest_info_break))
                {
                    //LogApi::logProcess("ljphptest:get redis break:  i_red:$i_red ");
                    break;
                }
            }            
        }
        else
        {
            $i_sql=0;
            $data_sql= array();
            $sql = "select * from card.consume_reward_box";
            $rows = $this->getDbMain()->query($sql);
            while ($rows && $row = $rows->fetch_assoc())
            {
                $data_sql['prices'] =$prices[$i_sql] = (int)$row['value'];
                $data_sql['dropids'] =$dropids[$i_sql] = (int)$row['drop_id'];
                $this->getRedisMaster()->hset($key_chest_info, $i_sql, json_encode($data_sql));
                //LogApi::logProcess("ljphptest:set redis and get i_sql:$i_sql prices[$i_sql]:$prices[$i_sql]  dropids[$i_sql]:$dropids[$i_sql]");
                $i_sql++;                
            }
            $this->getRedisMaster()->expire($key_chest_info,24*60*60);//live_time 24*60*60 s
        }      
   
        $now = time()*1000;
        $date = date("Y-m-d");
        for($j =0,$i = 0; $i < count($prices); $i++){
            if ($totalConsume >= $prices[$i]) {
                
                $key = "user_consume:$uid:$date:".$prices[$i];
                
                //暂时去掉
                $value = $this->getRedisMaster()->get($key);
                if(!empty($value)){
                    continue;
                }
				$key = "user_consume_atom:$uid:$date";
				$field = $prices[$i] . "";
				
				if ($this->getRedisMaster()->hIncrBy($key, $field, 1) != 1) {
					LogApi::logProcess("getConsumeRewards2::Fetch Lock Fail. key:$key, field:$field");
					continue;
				}
				$this->getRedisMaster()->expire($key, 24*60*60);
                
                $sql = "INSERT INTO card.user_all_box (drop_id,good_id,uid,create_time,status, type) select ".$dropids[$i].", good_id, $uid, $now, 1, 2 from card.treasure_box_info where id = ".$dropids[$i];
                $rs = $this->getDbMain()->query($sql);
                if ($rs) {
                    $sql = "select LAST_INSERT_ID() as id";
                    $rows = $this->getDbMain()->query($sql);
                     
                    if($rows && $rows->num_rows > 0){
                        $row = $rows->fetch_assoc();
                        $boxid = (int)$row['id'];
            
                        $data[$j++] = $boxid;            
                        LogApi::logProcess("执行插入宝箱后，获得的主键id：$boxid, sql:$sql");
                    }else{
                    	$this->getRedisMaster()->hIncrBy($key, $field, -1);
                        LogApi::logProcess("getConsumeRewards2::获取宝箱id失败。 excute sql error, sql:$sql");
                    }
                    
                }else{
                	$this->getRedisMaster()->hIncrBy($key, $field, -1);
                    LogApi::logProcess('getConsumeRewards2:: exe sql error, sql:'.$sql);
                }
            }
        }
        
        LogApi::logProcess('getConsumeRewards2:: , data:'.json_encode($data));
        return $data;
    }
    
    //是否有消费奖励
    public function isConsumeRewards($uid, $totalConsume, $giftValue){
        $now = time();
        $key = "consume_reward_time:uid:$uid";
        $value = $this->getRedisMaster()->get($key);
        if(!empty($value)){
            //中奖间隔在1分钟内的不再中奖
            if($now - $value <= 60){
                return false;
            }
        }
        
        $proArr = array();
        $consume = 0;
        $p1 = 0;
        for ($i = count($this->rewardProbabilityList) - 1; $i >= 0; $i--) {
            $consume = $this->rewardProbabilityList[$i]['consume'];
            if ($totalConsume >= $consume) {
                $p1 = $this->rewardProbabilityList[$i]['probability'];
                break;
            }
        }
        
        $key = "uid:$uid:consume:$consume";
        $value = $this->getRedisMaster()->get($key);
        if(!empty($value)){
            $p1 += $value;
        }
        
        $base = $totalConsume-$giftValue;
        if($base > 0){
            $tmp = $giftValue/$base;
            if($p1 > 0 && $tmp <= 0.1){
                $p1 += $tmp*0.4;
            
                $value += $tmp*0.4;
                $this->getRedisMaster()->set($key, $value);
            } 
        }
        
        $p2 = 100-$p1;
        $proArr[0] = $p2;
        $proArr[1] = $p1;
        
        $result = 0;
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);             //抽取随机数
            if ($randNum <= $proCur) {
                $result = $key;                         //得出结果
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        
        if($result){
            $key = "consume_reward_time:uid:$uid";
            $this->getRedisMaster()->set($key, $now);
        }
        
        return (int)$result;
    }
    
    //财富升级奖励
    public function getConsumeUpRewards($uid, $dropid){
        $boxid = 0;
        $now = time()*1000;
        $sql = "INSERT INTO card.user_all_box (drop_id,good_id,uid,create_time,status, type) select $dropid, good_id, $uid, $now, 1, 3 from card.treasure_box_info where id = $dropid";
        $rs = $this->getDbMain()->query($sql);
        if ($rs) {
            $sql = "select LAST_INSERT_ID() as id";
            $rows = $this->getDbMain()->query($sql);
             
            if($rows && $rows->num_rows > 0){
                $row = $rows->fetch_assoc();
                $boxid = (int)$row['id'];
                LogApi::logProcess("执行插入宝箱后，获得的主键id：$boxid, sql:$sql");
            }else{
                LogApi::logProcess("getRichManLevel::获取宝箱id失败。 excute sql error, sql:$sql");
            }
        }
        
        return $boxid;
    }
    
    //消费秀币
    public function consumeCoin($uid, $coinCost){
        
        // 扣秀币
// 		getRedisMaster() = ServiceFactory::getService('redis','master');
		
        $query = "UPDATE user_attribute SET coin_balance = coin_balance - $coinCost
            WHERE uid =$uid AND coin_balance >= $coinCost";
        $redisKey = "user_attribute:{$uid}";
        
       	LogApi::logProcess('**********ToolConsumeRecordModel::consumeCoin::query:'.$query);
       	
        $rs1 = $this->getDbMain()->query($query);
        if(!$rs1){
        	LogApi::logProcess('**********ToolConsumeRecordModel::consumeCoin:扣除秀币失败.');
            return false;
        }
		
		LogApi::logProcess('###################### 0000');
		        
        $this->getRedisMaster()->del($redisKey);
		
		LogApi::logProcess('###################### 11111');
        
        return true;
    }
	
	//$qty:礼物数量
    public function consume($b_active_prop, $uid, $sid, $cid, $tool, $qty, $receiver_uid, $buy, $charmRate = 1, $unionId, $isNew = false, $prop_id = 0)
    {
        $tid = $tool['id'];
        $tool_category1 = $tool['category1'];
        $tool_category2 = $tool['category2'];
        $tool_price = $tool['price'];
        $total_receiver_points = 0;
        $total_receiver_charm = 0;
        $total_session_points = 0;
        $total_session_charm = 0;
        $total_game_point = 0;
        $redisKeys = array();
        //$tool['price']:礼物价格  
        $total_coins_cost = $tool['price'] * $qty;
        
        $b_success = false;
        if ($buy == ToolModel::SPEND_RCCOIN) {
            // 扣秀幣
            $b_success = $this->consume_coins($uid, $sid, $tool, $qty, $receiver_uid);
            if ($b_success) {
                $redisKeys[] = "user_attribute:{$uid}";                
            } else {
                return $b_success;
            }
        } else {
        	$b_success = $this->consume_prop($prop_id, $uid, $receiver_uid, $qty);
        	if ($b_success) {
        		$redisKeys[] = "user_attribute:{$uid}";
        	} else {
        		return $b_success;
        	}
        }

        $model_dispatcher = new evt_dispatch_model();
        $tranid = $model_dispatcher->get_transaction_id(evt_dispatch_model::type_gift_normal, $uid, $receiver_uid);
		
        switch ($tool['category1']) {
            case ToolModel::TYPE_GIFT:
                // 接收方加点卷
                $total_receiver_points = $tool['receiver_points'] * $qty; //礼物对应的秀点*数量
                $total_receiver_charm = floor( $tool['price'] * $qty * 1);
                $total_session_points = $tool['session_points'] * $qty;
                $total_session_charm = $tool['session_charm'] * $qty;
                $activityModel = new ActivityModel();
                $total_game_point = $tool['price'] * $qty;
                
                // DBLE
                $date = "'".date("Y-m-d")."'";
                $db_record = $this->getDbRecord();
                $sql = "SELECT uid FROM anchor_experience_record WHERE uid=$receiver_uid AND createtime=$date";
                $rows = $db_record->query($sql);
                if (!empty($rows) && $rows->num_rows > 0) {
                    $sql = "UPDATE anchor_experience_record SET experience=experience+$total_receiver_charm WHERE uid=$receiver_uid AND createtime=$date";
                } else {
                    $sql = "INSERT INTO anchor_experience_record(uid, experience, createtime) VALUES ($receiver_uid, $total_receiver_charm, $date)";
                }
                $rows = $db_record->query($sql);
                if (empty($rows) || $db_record->affected_rows <= 0) {
                     LogApi::logProcess("[DBLElog] consume sql error:$sql");
                }
                
                $userAttrModel = new UserAttributeModel();
                // 主播经验
                {
                    $model_dispatcher->trigger_evt_anchor_ll_exp_add($receiver_uid, $sid, $tranid, $total_receiver_charm);

					// $anchor_attr = $userAttrModel->getAttrByUid($receiver_uid);
					// $userAttrModel->on_anchor_exp_add($receiver_uid, $anchor_attr['experience_level'], $anchor_attr['anchor_exp'], $total_receiver_charm, $sid);

                }
                // 用户消费经验
                {
                	$gift_consume = floor($tool['price']*$qty) ;
                    $model_dispatcher->trigger_evt_user_rich_exp_add($uid, $sid, $tranid, $gift_consume);

                	// $user_attr = $userAttrModel->getAttrByUid($uid);
                	// $userAttrModel->on_user_consume_exp_add($uid, $user_attr['consume_level'], $user_attr['consume_exp'], $gift_consume, $sid);
                }
				
                // 累積計算月度秀點
                if ($total_receiver_points > 0) {
                    $month = date('Ym', time() - 32400);
                    $this->getRedisMaster()->zIncrBy('receiver_points_' . $month, $total_receiver_points, $receiver_uid);
                }
								
                break;
            case ToolModel::TYPE_EFFECT:
                break;
            default:
                break;
        }
        
        $sqlStr = array(
                'sid' => $sid,
                'cid' => $cid,
                'tid' => $tid,
                'tool_category1' => $tool_category1,
                'tool_category2' => $tool_category2,
                'qty' => $qty,
                'buy' => $buy,
                'tool_price' => $tool_price,
                'total_coins_cost' => $total_coins_cost,
                'total_receiver_points' => $total_receiver_points,
                'total_receiver_charm' => $total_receiver_charm,
                'total_session_points' => $total_session_points,
                'total_session_charm' => $total_session_charm,
                "tranid" => $tranid,
                'b_active_prop' => $b_active_prop
            );
       
        $flag = $this->divideMoney($tool, $total_coins_cost, $uid, $receiver_uid, $unionId, $sqlStr);
        if(!$flag){
            LogApi::logProcess("consume divideMoney failtrue.");
            // return false;
        }
        // 更新redis缓存
        $this->getRedisMaster()->del($redisKeys); 
        
        LogApi::logProcess("*********end consume******,del keys:".json_encode($redisKeys));
        return $b_success;
    }

    /**
     * 送礼物分成
     */
    public function divideMoney($tool, $money, $uid, $singerUid, $unionId, $sqlStr)
    {
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        $inner_num = (int)$user['is_robot'];
        if(2 == $inner_num){
            return true;
        }

        $b_active_prop = $sqlStr['b_active_prop'];
        
        $sid = $sqlStr['sid'];
        $cid = $sqlStr['cid'];
        $tid = $sqlStr['tid'];
        $tool_category1 = $sqlStr['tool_category1'];
        $tool_category2 = $sqlStr['tool_category2'];
        $qty = $sqlStr['qty'];
        $buy = $sqlStr['buy'];
        $tool_price = $sqlStr['tool_price'];
        $total_coins_cost = $sqlStr['total_coins_cost'];
        $total_receiver_points = $sqlStr['total_receiver_points'];
        $total_receiver_charm = $sqlStr['total_receiver_charm'];
        $total_session_points = $sqlStr['total_session_points'];
        $total_session_charm = $sqlStr['total_session_charm'];
        
        //基础分成比例(%)
        $base = $tool['base_percentage'];
        //奖金上线比例(%)
        $prize = $tool['prize_upper_percent'];
        //回馈基金比例(%)
        $back = $tool['back_fund_percent'];
        //公会分成比例(%)
        $union = $tool['union_income_percent'];
        
        LogApi::logProcess("分成比例：：基础分成比例：$base, 奖金上线比例：$prize, 回馈基金比例：$back, 公会分成比例：$union");
        
        //基础分成
        $baseValue = $money*$base/100;
        //奖金上限
        $prizeValue = $money*$prize/100;
        //回馈基金
        $backValue = ($money*$back/100)/2;
        //系统调控基金
        $sysControl = $backValue;

        $unionSunValue = 0;//工会分成
        $unionTotalValue = 0;
        //工会收益
        $unionValue = 0;
        //公会回馈基金
        $unionBack = 0;
        //公会奖金预算
        $unionPrize = 0;
        $officialValue = 0;
        if(!empty($unionId)){
            $unionSunValue = (int)$money;
            //工会分成
            $unionTotalValue = $money*$union/100;
        
            /****工会收入预算****/
//             //工会收益
//             $unionValue = $unionTotalValue * 45 / 100;
//             //公会回馈基金
//             $unionBack = $unionTotalValue * 10 / 100;
//             //公会奖金预算
//             $unionPrize = $unionTotalValue * 45 / 100;

			//工会收益
            $unionValue = $unionTotalValue * 0 / 100;
            //公会回馈基金
            $unionBack = $unionTotalValue * 10 / 100;
            //公会奖金预算
            $unionPrize = $unionTotalValue * 90 / 100;
            
            $official = 100 - $base-$prize-$back-$union;
            $officialValue = $money*$official/100;          
        }else{
            $unionId = 0;
            $official = 100 - $base-$prize-$back;
            //官方收入
            $officialValue = $money*$official/100;
        }
        
        $singerSunValue = (int)$money;
        
        if ($b_active_prop == 1) {
            $backValue = 0;
            $sysControl = 0;
            $unionSunValue = 0;
            $unionTotalValue = 0;
            $unionValue = 0;
            $unionBack = 0;
            $unionPrize = 0;
            $officialValue = 0;
        }
        
        // 向用户行为记录数据库写交易记录
        $family_id = $userInfo->GetSingerFamilyId($singerUid);
        $now = time();
//         $query = "INSERT INTO `tool_consume_record`
//                 (`record_time`, `uid`, `receiver_uid`, `sid`, `cid`, `tool_id`, `tool_category1`, `tool_category2`,
//                 `qty`, `buy`, `tool_price`, `total_coins_cost`, `total_receiver_points`, `total_receiver_charm`,
//                 `total_session_points`, `total_session_charm`, `base_percentage`, `prize_upper_limit`, `give_back_fund`, 
//                 `union_income`, `sys_control_fund`, `sys_income`, `union_id`, `union_earnings`, `union_back_fund`, 
//                 `union_prize_budget`, `union_sun_num`, `anchor_sun_num`, `family_id`)
//                 VALUES ($now,$uid,$singerUid,$sid,$cid,$tid,$tool_category1,$tool_category2,
//                 $qty,$buy,$tool_price,$total_coins_cost,$total_receiver_points,$total_receiver_charm,
//                 $total_session_points,$total_session_charm,$baseValue, $prizeValue, $backValue, 
//                 $unionTotalValue, $sysControl, $officialValue, $unionId, $unionValue, $unionBack, $unionPrize,
//                 $unionSunValue, $singerSunValue, $family_id)";
// //         $flag = $this->pushToMessageQueue('rcec_record', $query);
//         $rs = $this->getDbRecord()->query($query);
//         LogApi::logProcess("送礼分成divideMoney::result:$rs************sql:$query");
//         if(!$rs){
//             LogApi::logProcess("divideMoney is error*******************.");
//             return false;
//         }


        $model_dispatcher = new evt_dispatch_model();

        $evt = new evt_tool_consume_add();
        $evt->uid = intval($uid);
        $evt->receiver_uid = intval($singerUid);
        $evt->sid = intval($sid);
        $evt->cid = intval($cid);
        $evt->tool_id = intval($tid);
        $evt->tool_category1 = intval($tool_category1);
        $evt->tool_category2 = intval($tool_category2);
        $evt->qty = intval($qty);
        $evt->buy = intval($buy);
        $evt->tool_price = intval($tool_price);
        $evt->total_coins_cost = intval($total_coins_cost);
        $evt->total_receiver_points = intval($total_receiver_points);
        $evt->total_receiver_charm = intval($total_receiver_charm);
        $evt->total_session_points = intval($total_session_points);
        $evt->total_session_charm = intval($total_session_charm);
        $evt->base_percentage = floatval($baseValue);
        $evt->prize_upper_limit = floatval($prizeValue);
        $evt->give_back_fund = floatval($backValue);
        $evt->union_income = floatval($unionTotalValue);
        $evt->sys_control_fund = floatval($sysControl);
        $evt->sys_income = floatval($officialValue);
        $evt->union_id = intval($unionId);
        $evt->union_earnings = floatval($unionValue);
        $evt->union_back_fund = floatval($unionBack);
        $evt->union_prize_budget = floatval($unionPrize);
        $evt->union_sun_num = intval($unionSunValue);
        $evt->anchor_sun_num = intval($singerSunValue);
        $evt->family_id = intval($family_id);
        $evt->record_time = intval($now);
        $evt->tid = $sqlStr['tranid'];

        $model_dispatcher->trigger_evt_tool_consume_add($evt);

        if ($b_active_prop == 0) {
            // 主播当天福利金币缓存
            $model_divide = new divide_model();
            $model_divide->add_anchor_divide_in_cache($singerUid, $baseValue, $prizeValue, $backValue, $singerSunValue, $now);

            if(!empty($unionId)){

                // 帮会当天分成缓存
                $model_divide->add_union_divide_in_cache($unionId, $unionValue, $unionBack, $unionPrize, $unionSunValue, $now);
                
                // 帮会金币月收入
                $redis = $this->getRedisMaster();
                $date = date('Ym');
                $key = "h_union_gold_received:$date";
                $redis->hIncrBy($key, $unionId."", $money);
                $redis->expire($key, 32*24*60*60);
            }
        }
        
        LogApi::logProcess("*******************end divideMoney*******************.");
        return true;
    }
    public function AppendToolConsumeRecordInfo($info)
    {
    	$family_id = 0;
    	if ($info->singerUid != 0) {
    		$model_uinfo = new UserInfoModel();
    		$family_id = $model_uinfo->GetSingerFamilyId($info->singerUid);
    	}

        // $model_dispatcher = new evt_dispatch_model();

        // $evt = new evt_tool_consume_add();
        // $evt->uid = $info->uid;
        // $evt->receiver_uid = $info->singerUid;
        // $evt->sid = $info->sid;
        // $evt->cid = $info->cid;
        // $evt->tool_id = $info->tid;
        // $evt->tool_category1 = $info->tool_category1;
        // $evt->tool_category2 = $info->tool_category2;
        // $evt->qty = $info->qty;
        // $evt->buy = $info->buy;
        // $evt->tool_price = $info->tool_price;
        // $evt->total_coins_cost = $info->total_coins_cost;
        // $evt->total_receiver_points = $info->total_receiver_points;
        // $evt->total_receiver_charm = $info->total_receiver_charm;
        // $evt->total_session_points = $info->total_session_points;
        // $evt->total_session_charm = $info->total_session_charm;
        // $evt->base_percentage = $info->baseValue;
        // $evt->prize_upper_limit = $info->prizeValue;
        // $evt->give_back_fund = $info->backValue;
        // $evt->union_income = $info->unionTotalValue;
        // $evt->sys_control_fund = $info->sysControl;
        // $evt->sys_income = $info->officialValue;
        // $evt->union_id = $info->unionId;
        // $evt->union_earnings = $info->unionValue;
        // $evt->union_back_fund = $info->unionBack;
        // $evt->union_prize_budget = $info->unionPrize;
        // $evt->union_sun_num = $info->unionSunValue;
        // $evt->anchor_sun_num = $info->singerSunValue;
        // $evt->family_id = $family_id;
        // $evt->record_time = $info->now;
        // $evt->tid = $model_dispatcher->get_transaction_id(evt_dispatch_model::type_gift_normal, $info->uid, $info->singerUid);

        // $model_dispatcher->trigger_evt_tool_consume_add($evt);

        // return;

    	
        $sql = "INSERT INTO `tool_consume_record`
        (`record_time`, `uid`, `receiver_uid`, `sid`, `cid`, `tool_id`, `tool_category1`, `tool_category2`,
        `qty`, `buy`, `tool_price`, `total_coins_cost`, `total_receiver_points`, `total_receiver_charm`,
        `total_session_points`, `total_session_charm`, `base_percentage`, `prize_upper_limit`, `give_back_fund`,
        `union_income`, `sys_control_fund`, `sys_income`, `union_id`, `union_earnings`, `union_back_fund`,
        `union_prize_budget`, `union_sun_num`, `anchor_sun_num`, `family_id`)
        VALUES ($info->now,$info->uid,$info->singerUid,$info->sid,$info->cid,$info->tid,$info->tool_category1,$info->tool_category2,
        $info->qty,$info->buy,$info->tool_price,$info->total_coins_cost,$info->total_receiver_points,$info->total_receiver_charm,
        $info->total_session_points,$info->total_session_charm,$info->baseValue, $info->prizeValue, $info->backValue,
        $info->unionTotalValue, $info->sysControl, $info->officialValue, $info->unionId, $info->unionValue, $info->unionBack, $info->unionPrize,
        $info->unionSunValue, $info->singerSunValue, $family_id)";
        $rows = $this->getDbRecord()->query($sql);
        LogApi::logProcess("ToolConsumeRecordInfo.AppendToolConsumeRecordInfo rows:$rows************sql:$sql");
        if(!$rows)
        {
            LogApi::logProcess("ToolConsumeRecordInfo.AppendToolConsumeRecordInfo******excute sql error!!!**********sql:$sql");
        }    
    }
    public function AppendWeekToolConsumeRecord($info)
    {
        $sql = "INSERT INTO rcec_record.week_tool_consume_record
        (`record_time`, `uid`, `receiver_uid`, `tool_id`, `tool_category1`, `tool_category2`,
        `qty`, `tool_price`, `total_coins_cost`)
        VALUES ($info->now,$info->uid,$info->singerUid,$info->tid,$info->tool_category1,$info->tool_category2,
        $info->qty,$info->tool_price,$info->total_coins_cost)";
        $rows = $this->getDbRecord()->query($sql);
        LogApi::logProcess("ToolConsumeRecordInfo.AppendWeekToolConsumeRecord rows:$rows************sql:$sql");
        if(!$rows)
        {
            LogApi::logProcess("ToolConsumeRecordInfo.AppendWeekToolConsumeRecord******excute sql error!!!**********sql:$sql");
            return;
        }

        // $model_dispatcher = new evt_dispatch_model();

        // $evt = new evt_week_tool_consume_add();
        // $evt->uid = $info->uid;
        // $evt->receiver_uid = $info->singerUid;
        // $evt->tool_id = $info->tid;
        // $evt->tool_category1 = $info->tool_category1;
        // $evt->tool_category2 = $info->tool_category2;
        // $evt->qty = $info->qty;
        // $evt->tool_price = $info->tool_price;
        // $evt->total_coins_cost = $info->total_coins_cost;
        // $evt->record_time = $info->now;
        // $evt->tid = $model_dispatcher->get_transaction_id(evt_dispatch_model::type_gift_gticket, $info->uid, $info->singerUid);

        // $model_dispatcher->trigger_evt_week_tool_consume_add($evt);

        $day = date("w");
        $timestamp = $info->now;
        $start = 0;
        if($day>=$start){
            $startdate_timestamp = mktime(0,0,0,date('m',$timestamp),date('d',$timestamp)-($day-$start),date('Y',$timestamp));
        } else {
            $startdate_timestamp = mktime(0,0,0,date('m',$timestamp),date('d',$timestamp)-7+$start-$day,date('Y',$timestamp));
        }

        $week_begin = date("Ymd", $startdate_timestamp);

        $key = "week_gift_" . $week_begin . "_" . $info->tid;
        $score = $info->qty;

        $redis = $this->getRedisMaster();

        $redis->zIncrBy($key, $score, $info->singerUid);
        $redis->expire($key, 7 * 24 * 60 * 60);
    }
    public function addChannelGiftInfoOneDay($sid,$gift,$giftNum)
    {
        $key = "ChannelGiftInfoOneDay:".$sid;
        $value = $this->getRedisMaster()->get($key);  
        if ($value !== false) {
            $giftInfo = json_decode($value,true);
            $lasttime =  $giftInfo['lastupdatetime'];
            if (date('ymd') !=  $lasttime) {
                //如果不是同一天
                $this->getRedisMaster()->del($key);
                $data = array();
                $data['gift'] =$gift;
                $data['giftnum'] = $giftNum ;
                $giftInfo[$gift] = $data; 
            }
            else
            {
                if( $giftInfo[$gift] == null )
                {//如果不存在
                    $data = array();
                    $data['gift'] =$gift;
                    $data['giftnum'] = $giftNum ;
                    $giftInfo[$gift] = $data;
                }
                else
                {///已经有人送过了
                    $data = array();
                    $data['gift'] = $gift;
                    $data['giftnum'] = $giftNum +$giftInfo[$gift]['giftnum'];
                    $giftInfo[$gift] = $data;
                }
            }
            $giftInfo['lastupdatetime'] = date('ymd');
            $this->getRedisMaster()->set($key, json_encode($giftInfo));
           
        }
        else
        {
             $data = array();
             $data['gift'] =$gift;
             $data['giftnum'] = $giftNum;
             $giftInfo[$gift] = $data;
             $giftInfo['lastupdatetime'] = date('ymd');
             $this->getRedisMaster()->set($key, json_encode($giftInfo));
        }
    }
    public function getChannelGiftInfoOneDay($sid)
    {
        $key = "ChannelGiftInfoOneDay:".$sid;
        $value = $this->getRedisMaster()->get($key);
        if ($value !== false) {
            $giftInfo = json_decode($value,true);
            $lasttime =  $giftInfo['lastupdatetime'];
            if (date('ymd') ==  $lasttime) {
                ///如果是同一天
                $ret =   $giftInfo;
            }else
            {
                //如果不是同一天，返回一条空的
                 $ret = array();
            }
        }
        else
        {
            $ret = array();
        }
        return $ret;
    }
    public function updateGiftRankOfTop12( $gift , $giftNum )
    {      
        //更新今天的榜
        $key = "GiftRank_Top12_Recive:".date("ymd");
        $value = $this->getRedisMaster()->get($key);  
        if ($value !== false) {
            $giftInfo = json_decode($value,true);
            if($giftInfo[$gift] == null)
            {//如果不存在
                $data = array();
                $data['gift']    = $gift;
                $data['giftnum'] = $giftNum ;
                $giftInfo[$gift] = $data;
            }
            else
            {///已经有人送过了
                $data = array();
                $data['gift']    = $gift;
                $data['giftnum'] = $giftNum + $giftInfo[$gift]['giftnum'];
                $giftInfo[$gift] = $data;
            }
        
            $this->getRedisMaster()->set($key, json_encode($giftInfo));
           
        }
        else
        {
             $data = array();
             $data['gift']    = $gift;
             $data['giftnum'] = $giftNum;
             $giftInfo[$gift] = $data;
             $this->getRedisMaster()->set($key, json_encode($giftInfo));
        }
     
        return ;
    }

    public function getGiftRankOfTop12($defaultarray)
    {
        //榜单取昨天的数据
        $yesterday = date("ymd",strtotime("-1 day"));
        //先从缓存中取，如果取不到再重新计算
        $yesterdayRankkey = "GiftRank_Top12_Rank:".$yesterday;
       
        $result = $this->getRedisMaster()->get($yesterdayRankkey);
        if($result !== false)
        {
            $list = json_decode($result,true);
        }
        else
        {
            //先删除前天的数据
            $beforeYesterday = date("ymd",strtotime("-2 day"));
            $tmpRankkey = "GiftRank_Top12_Recive:".$beforeYesterday;
            $tmpResultkey = "GiftRank_Top12_Rank:".$beforeYesterday;
            $this->getRedisMaster()->del($tmpRankkey);
            $this->getRedisMaster()->del($tmpResultkey);
            $list = array(
                    'name'=>'HOT',
                    'sort_id' => -1,
                    'type' => 99,
                    'list' => array()
            );
            $toolModel = new ToolModel();
            //从昨天的统计结果中计算
            $key = 'GiftRank_Top12_Recive:'.$yesterday;
            $value = $this->getRedisMaster()->get($key);
            if ($value !== false) {
              
                $tmp = json_decode($value,true);
               
                //排序
                $sortlist = $this->array_sort($tmp,'giftnum','dec');
                //取前12位
                $ret = array_slice($sortlist,0,12);
                //生成礼物信息
                foreach ($ret as $toolret) {
                     $tid = $toolret['gift'];
                     $too = $toolModel->getToolByTid($tid);
                     if($too != false)
                        $list['list'][]= $toolModel->getResponseInfo($too);
                }
               
                //存储到redis
                $this->getRedisMaster()->set($yesterdayRankkey, json_encode($list));
            }
            else
            {
                //如果昨天没有消费记录，则hot页和初级页面使用的礼物相同
                $list['list'] = $defaultarray['list'];
            }
            //存储到redis
            $this->getRedisMaster()->set($yesterdayRankkey, json_encode($list));
           
        }
        return $list;
    }
    function array_sort($array,$keys,$type='asc'){  
        //$array为要排序的数组,$keys为要用来排序的键名,$type默认为升序排序  
        $keysvalue = $new_array = array();

        foreach ($array as $k=>$v){  
            $keysvalue[$k] = $v[$keys];  
        }

        if($type == 'asc'){  
            asort($keysvalue);  
        }else{  
            arsort($keysvalue);  
        }

        reset($keysvalue);

        foreach ($keysvalue as $k=>$v){  
            $new_array[$k] = $array[$k];  
        }

        return $new_array;  
        }  
        
    public function consume_coins($uid, $sid, $tool, $qty, $receiver_uid)
    {
        $tid = $tool['id'];
        $tool_price = $tool['price'];
        $total_coins_cost = $tool['price'] * $qty;

        $b_success = false;
        $dbrcec = $this->getDbMain();
        try {
            $dbrcec->query("BEGIN");            
            do {
                $coin_before = 0;
                $sql = "SELECT coin_balance FROM user_attribute WHERE uid = $uid FOR UPDATE";
                $rows = $dbrcec->query($sql);
                if (!empty($rows) && $rows->num_rows > 0) {
                    $row = $rows->fetch_assoc();
                    $coin_before = $row['coin_balance'];
                } else {
                    LogApi::logProcess("consume sql error:$sql");
                    break;
                }
    
                if ($coin_before < $total_coins_cost) {
                    LogApi::logProcess("consume failure:lack of coins.uid:$uid,sid:$sid,recv:$receiver_uid,tool:$tid,price:$tool_price,qty:$qty,totalcost:$total_coins_cost,coins:$coin_before");
                    break;
                }
    
                $sql = "UPDATE user_attribute SET coin_balance = coin_balance - $total_coins_cost WHERE uid = $uid";
                
                $rows = $dbrcec->query($sql);
    
                if (empty($rows) || $dbrcec->affected_rows == 0) {
                    LogApi::logProcess("consume sql error:$sql");                    
                    break;
                }
    
                $sql = "INSERT INTO t_user_consume_gift_bill (uid,sid,recvid,tool_id,tool_price,tool_qty,coin_cost,coin_before,coin_after) VALUES ($uid,$sid,$receiver_uid,$tid,$tool_price,$qty,$total_coins_cost,$coin_before,$coin_before-$total_coins_cost)";
    
                $rows = $dbrcec->query($sql);
                if (!empty($rows)) {
                    $b_success = true;
                } else {
                    LogApi::logProcess("consume sql error:$sql");
                }
            } while (0);
    
            if ($b_success) {
                $dbrcec->query('COMMIT');
            } else {
                $dbrcec->query('ROLLBACK');
            }
        } catch(Exception $e){
            LogApi::logProcess("送礼更新数据异常，执行回滚*******************.");
            $dbrcec->query("ROLLBACK");
                throw $e;
        };
        
        return $b_success;
    }

    public function consume_sun($uid, $sid, $tool, $qty, $receiver_uid)
    {
            $tid = $tool['id'];
            $tool_price = $tool['price'];
            $total_sun_costs = $tool['price'] * $qty;
    
            $b_success = false;
            $dbrcec = $this->getDbMain();
            try {
                $dbrcec->query("BEGIN");            
                do {
                    $sun_before = 0;
                    $sql = "SELECT sun_num FROM user_attribute WHERE uid = $uid FOR UPDATE";
                    $rows = $dbrcec->query($sql);
                    if (!empty($rows) && $rows->num_rows > 0) {
                        $row = $rows->fetch_assoc();
                        $sun_before = $row['sun_num'];
                    } else {
                        LogApi::logProcess("consume sun sql error:$sql");
                        break;
                    }
        
                    if ($sun_before < $total_sun_costs) {
                        LogApi::logProcess("consume sun failure:lack of sun.uid:$uid,sid:$sid,recv:$receiver_uid,tool:$tid,price:$tool_price,qty:$qty,totalcost:$total_sun_costs,sun:$sun_before");
                        break;
                    }
        
                    $sql = "UPDATE user_attribute SET sun_num = sun_num - $total_sun_costs WHERE uid = $uid";
                    
                    $rows = $dbrcec->query($sql);
        
                    if (empty($rows) || $dbrcec->affected_rows == 0) {
                        LogApi::logProcess("consume sun sql error:$sql");                    
                        break;
                    }
        
                    $sql = "INSERT INTO t_user_consume_sunshine_bill (uid,sid,recvid,tool_id,tool_price,tool_qty,sun_cost,sun_before,sun_after) VALUES ($uid,$sid,$receiver_uid,$tid,$tool_price,$qty,$total_sun_costs,$sun_before,$sun_before-$total_sun_costs)";
        
                    $rows = $dbrcec->query($sql);
                    if (!empty($rows)) {
                        $b_success = true;
                    } else {
                        LogApi::logProcess("consume sun sql error:$sql");
                    }
                } while (0);
        
                if ($b_success) {
                    $dbrcec->query('COMMIT');
                } else {
                    $dbrcec->query('ROLLBACK');
                }
            } catch(Exception $e){
                LogApi::logProcess("送阳光礼更新数据异常，执行回滚*******************.");
                $dbrcec->query("ROLLBACK");
                   throw $e;
            };
            return $b_success;
    }
    
    public function consume_prop($prop_id, $uid, $singer_id, $num)
    {    	 
    	$mysql = $this->getDbMain();
    	$b_success = false;
    	
    	try {
    		$mysql->query("BEGIN");
    		do {
    			$num_before = 0;
    			$sql = "SELECT num FROM card.user_goods_info WHERE uid=$uid AND goods_id=$prop_id FOR UPDATE";
    			 
    			$rows = $mysql->query($sql);
    			if (!empty($rows)) {
    				if ($rows->num_rows > 0) {
    					$row = $rows->fetch_assoc();
    					$num_before = $row['num'];
    				}
    			} else {
    				LogApi::logProcess("ToolConsumeRecord::consume_prop sql error:$sql");
    				break;
    			}
    			 
    			if ($num_before < $num) {
    				LogApi::logProcess("ToolConsumeRecord::consume_prop num limit. uid:$uid prop:$prop_id num:$num num_now:$num_before");
    				break;
    			}
    			 
    			$sql = "UPDATE card.user_goods_info SET num = num - $num WHERE ( uid = $uid && goods_id = $prop_id)";
    			$rows = $mysql->query($sql);
    			if(empty($rows)) {
    				LogApi::logProcess("ToolConsumeRecord::consume_prop sql error:$sql");
    				break;
    			}
    			
    			$b_success = true;
    		} while (0);
    		
    		if ($b_success) {
    			$mysql->query("COMMIT");
    		} else {
    			$mysql->query("ROLLBACK");
    		}
    	} catch (Exception $e) {
    		LogApi::logProcess("使用背包礼物数据异常，执行回滚*******************.");
    		$mysql->query("ROLLBACK");
    		throw $e;
    	}
    	
    	return $b_success;
    }
    
    public function on_user_good_add($uid, $good_id, $num, $src)
    {
    	$sql = "INSERT INTO rcec_record.user_good_add_record (uid, good_id, num, update_time, src) VALUES ($uid, $good_id, $num, UNIX_TIMESTAMP(), $src)";
    	$db_rcec = $this->getDbRecord();
    	$rows = $db_rcec->query($sql);
    	if (empty($rows)) {
    		LogApi::logProcess("ToolConsumeRecordModel:on_user_good_add failure:" . $sql);
    	}
    }
}
?>
