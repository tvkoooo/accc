<?php
class UnionTicketInfo
{
    public $goods_name = "";
    public $goods_icon = "";
    public $convert = 50;
}
class ToolModel extends ModelBase
{

	//进入直播间倒计时key
	const KEY_COUNTDOWNTIME = 'countdowntime';

    /**
     * 道具分類
     */
    const TYPE_GIFT = 1;

    const TYPE_EFFECT = 2;

    const TYPE_STAGE = 3;

    const SMALL_GIFT = 10;

    const BIG_GIFT = 11;

    const SOUND_EFFECT = 20;

    const CHANGE_SOUND = 21;

    const LIGHT_EFFECT = 22;

    const INTERACTION = 23;

    const BACKGROUND = 24;

    const STAGE = 30;

    /**
     * 道具可用(1) 不可用(0)
     */
    const STATE_ON = 1;

    const STATE_OFF = 0;

    /**
     * 支付类型
     */
    const SPEND_PACKET = 0;

    const SPEND_RCCOIN = 1;

//    const IMAGE_HOST = 'http://showimg.raidtalk.com.tw/images/';
//	const IMAGE_HOST = DEF_IMAGE_HOST;
	public static function GetImageHost(){
		return 'http://www.' . GlobalConfig::GetDomainURL() . '/live/images/';
	}
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 按一级分类获取开放的道具的列表
     *
     * @param int $cate1
     */
    public function getToolListByCategory($cate1, $orderBy = '', $giftDiscount = 0)
    {
        LogApi::logProcess('getToolListByCategory 1');
        $key = 'tool:category1_' . $cate1;
        $query = "select * from tool where closed = 0 and category1 = $cate1 $orderBy";
        $tmpRows = $this->read($key, $query);
        
        LogApi::logProcess('getToolListByCategory 2');
        
        $rows = array();
        foreach ($tmpRows as $tmpRow) {
            if ($tmpRow['category1'] == ToolModel::TYPE_GIFT && $giftDiscount > 0) {
                $tmpRow['price'] = ceil($tmpRow['price'] * $giftDiscount);
            }
            
//        	$tid = $tmpRow['id'];
//        	$price = $tmpRow['price'];
//        	$name = $tmpRow['name'];
//			file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "consume tool tid:$tid,price:$price,count:$giftDiscount,name:$name\n", FILE_APPEND);
            $rows[] = $tmpRow;
        }
        LogApi::logProcess('getToolListByCategory 3');
        return $rows;
    }

    public function getAllTools()
    {
        $key = 'tool:all';
        $query = "select * from tool where closed = 0 and category2 = 17";
        $rows = $this->read($key, $query);
        $tools = array();
        foreach ($rows as $row) {
            $tool = array();
            $tool['id'] = $row['id'];
            $tool['name'] = $row['name'];
            $tool['des'] = $row['description'];
            $tool['icon'] = $row['icon'];
            $tool['resource'] = $row['resource'];
            $tool['category'] = $row['category1'];
            $tool['type'] = $row['category2'];
            $tool['price'] = $row['price'];
            $tool['consumeType'] = $row['consume_type'];
            $tool['canBuy'] = $row['packet_only'] ? 0 : 1;
            $tool['isHot'] = $row['is_hot'];
            $tool['isNew'] = $row['create_time'] > strtotime('-3 days') ? 1 : 0;
            $tool['label'] = explode(',', $row['label']);
	        $tool['sort_id'] = $row['sort_id'];
	        $tool['gift_type'] = $row['gift_type'];
            $tools[] = $tool;
        }
        
        //LogApi::logProcess("getAllTools::*********************.".json_encode($tools));
        return $tools;
    }

    public function getToolByTid($tid, $giftDiscount = 0)
    {
        $key = 'tool:' . $tid;
        $query = "select * from tool where id = $tid ";
        $rows = $this->read($key, $query);
        $tmpRows = $rows;
        $rows = array();
        foreach ($tmpRows as $tmpRow) {
            if ($tmpRow['category1'] == ToolModel::TYPE_GIFT && $giftDiscount > 0) {
                $tmpRow['price'] = ceil($tmpRow['price'] * $giftDiscount);
            }
            $rows[] = $tmpRow;
        }
        if (count($rows) == 1) {
            return $rows[0];
        } else {
            return false;
        }
    }

    public function dataCleanUp($tool)
    {
        $userAttrModel = new UserAttributeModel();
        $tmpTool = array();
        $tmpTool['id'] = $tool['id'];
        $tmpTool['name'] = $tool['name'];
        $tmpTool['points'] = $tool['receiver_points'];
        $tmpTool['charmValue'] = $tool['receiver_charm'];
        $tmpTool['closeValue'] = $tool['receiver_charm'];
        $giftConsume = ($tool['receiver_points'] > 0 ? $tool['price'] * 5 : $tool['price']);
        $tmpTool['richValue'] = $giftConsume;
        $tmpTool['description'] = $tool['description'];
        $tmpTool['imageUrl'] = $tool['icon'];
        $tmpTool['resource'] = $tool['resource'];
        $tmpTool['type'] = $tool['category2'];
        $tmpTool['price'] = $tool['price'];
        $tmpTool['isHot'] = $tool['is_hot'];
        $tmpTool['packetOnly'] = $tool['packet_only'];
        $experienceData = $userAttrModel->getExperienceLevel($tool['min_charm']);
        $tmpTool['minSingerLevel'] = $experienceData['singerLevel'];
        if ($tool['closed']) {
            $tmpTool['canUse'] = ToolModel::STATE_OFF;
        } else {
            $tmpTool['canUse'] = ToolModel::STATE_ON;
        }
        return $tmpTool;
    }

    public function getResponseInfo($tool)
    {
        $userAttrModel = new UserAttributeModel();
        $tmpTool = array();
        $tmpTool['id'] = $tool['id'];
        $tmpTool['name'] = $tool['name'];
        $tmpTool['des'] = $tool['name'] . ":" . $tool['price'] . "  GOLD";
        if ($tool['consume_type'] == 1) {
            $tmpTool['des'] .= "包月\n";
        } else {
            $tmpTool['des'] .= "\n";
        }
        if ($tool['category1'] == ToolModel::TYPE_GIFT) {
	/*
            $tmpTool['des'] .= "主播獲得:" . $tool['receiver_points'] . "秀點\n";
            $tmpTool['des'] .= "主播獲得:" . $tool['receiver_charm'] . "魅力\n";
            $tmpTool['des'] .= "守護獲得:" . $tool['receiver_charm'] . "親密值\n";
            $giftConsume = ($tool['receiver_points'] > 0 ? $tool['price'] * 5 : $tool['price']);
            $tmpTool['des'] .= "用戶獲得:" . $giftConsume . "財富值\n";
	*/
        } elseif ($tool['category1'] == ToolModel::TYPE_EFFECT) {
            $experienceData = $userAttrModel->getExperienceLevel($tool['min_charm']);
            $tmpTool['des'] .= "等級需達:LV" . $experienceData['singerLevel'] . "（" . $experienceData['singerTitle'] . "）\n";
        } elseif ($tool['category1'] == ToolModel::TYPE_STAGE) {
            $experienceData = $userAttrModel->getExperienceLevel($tool['min_charm']);
            $tmpTool['des'] .= "等級需達:LV" . $experienceData['singerLevel'] . "（" . $experienceData['singerTitle'] . "）\n";
        }
        $tmpTool['des'] .= $tool['description'];
        $tmpTool['imageUrl'] = $tool['icon'];
        $tmpTool['resource'] = $tool['resource'];
        $tmpTool['type'] = $tool['category2']; // 二级分类（类型）
        $tmpTool['price'] = $tool['price'];
        $tmpTool['weekStar'] = $tool['weekStar'];//是否周星礼物 1:是 0 不是 2上头条礼物
        $tmpTool['consumeType'] = $tool['consume_type']; // 消費類型（1包月消費，0直接消費）
        $tmpTool['packetOnly'] = $tool['packet_only'];
        if (isset($tool['multi_gifting']) && ($tool['multi_gifting']!="")) {
            $tmpTool['multi_gifting'] = explode(',', $tool['multi_gifting']);
        }
        if ($tool['closed']) {
            $tmpTool['canUse'] = ToolModel::STATE_OFF;
        } else {
            $tmpTool['canUse'] = ToolModel::STATE_ON;
        }
	$tmpTool['sort_id'] = $tool['sort_id'];
	$tmpTool['gift_type'] = $tool['gift_type'];
        return $tmpTool;
    }

    public function getHeartTool($uid)
    {
        $userAttrModel = new UserAttributeModel();
        $tmpTool = array();
        $tmpTool['id'] = 0;
        $tmpTool['name'] = '愛心';
        $tmpTool['des'] = "給你喜歡的歌手贈送愛心，\n讓他或她增加表演魅力吧";
        $tmpTool['imageUrl'] = '2013070216525334.png';
        $tmpTool['resource'] = '2013070216525334.png';
        $tmpTool['type'] = 0;
        $tmpTool['price'] = 0;
        $tmpTool['canUse'] = ToolModel::STATE_ON;
        // 愛心額外的字段
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $tmpTool['num'] = $userAttr['heart'];
        return $tmpTool;
    }
    
    //缓存所有用户送礼排行
    public function gift_zIncrBy($giftid, $uid, $num)
    {
        $key = "gift_top:$giftid";
        $this->getRedisMaster()->zIncrBy($key, $num, $uid);
        
        // $top3 = $this->getRedisMaster()->zrevrange($key, 0, 2);
        
        // return $top3;
    }
    //获取礼物榜前3排行用户
    public function getTop3($giftid, $uid)
    {       
        $key = "gift_top:$giftid";
        /*
        $this->getRedisMaster()->zIncrBy($key, $num, $uid);
        */
        
        $top3 = $this->getRedisMaster()->zrevrange($key, 0, 2);
    
        return $top3;
    }
    //尝试更新周星记录
    public function UpdateWeekToolRecord($uid,$zid,$tool_id,$dt_point,$dt_number)
    {
        $sql = "update cms_manager.week_tool_record set point = point + $dt_point,total = total + $dt_number where ( uid = $uid && zid = $zid && toolId = $tool_id )";
        $rows = $this->getDbMain()->query($sql);
        if ( !$rows )
        {
            LogApi::logProcess("UpdateWeekToolRecord::****************sql:$sql");
        }
        else 
        {
            LogApi::logProcess("UpdateWeekToolRecord uid:".$uid." zid:".$zid." tool_id:".$tool_id." dt_point:".$dt_point." dt_number:".$dt_number);
        }
    }
    public function GetWeekToolByUid($uid)
    {
        $key = "anchor_week_signup";
        $field = $uid;

        $redis = $this->getRedisMaster();
        $value = $redis->hGet($key, $field);

        if (empty($value)) {
            $value = 0;
        }

        return $value;
    }
    public function GetUnionTicketInfo($info)
    {
        $id = 20;
        $sql = "SELECT gi.goods_name as name,CONCAT('/PubImgSour/game_pic/images/',rfi.folder_path,'/',gi.goods_icon,'.png') as icon,gi.param1 FROM card.goods_info gi
        LEFT JOIN card.resoure_folder_info rfi ON rfi.id=gi.path_id
        where gi.id = $id";
        $sql = "SELECT gi.goods_name as name,CONCAT('/',gi.goods_icon,'.png') as icon,gi.param1 FROM card.goods_info gi
        where gi.id = $id";
        $rows = $this->getDbMain()->query($sql);
        if ( $rows )
        {
            if ( 0 < $rows->num_rows )
            {
                $row = $rows->fetch_assoc();
                $info->goods_name = $row['name'];
                $info->goods_icon = $row['icon'];
                $info->convert = intval($row['param1']);
            }
        }
        else
        {
            LogApi::logProcess("GetUnionTicketConvert::****************sql:$sql");
        }
    }

    // 获取连续送礼次数
    public function IncrAndGetSerialNumber($uid, $sendTime)
    {
        $key = "serialnumber:$uid";
        $field = "sendtime:$sendTime";

        $num = $this->getRedisMaster()->hIncrBy($key, $field, 1);
        $this->getRedisMaster()->expire($key, 60);

        return $num;
    }
    
    public function AtomEnsure($key, $field, $span, $timeout = 0) 
    {
    	$num = $this->getRedisMaster()->hIncrBy($key, $field, $span);
    	if (!empty($timeout)) {
    		$this->getRedisMaster()->expire($key, $timeout);
    	}
    	
    	return $num;
    }
    
    public function AtomEnsureEx($key, $field, $span, $timeout = 0)
    {
    	$num = $this->getRedisMaster()->hIncrBy($key, $field, $span);
    	if (!empty($timeout)) {
    		$this->getRedisMaster()->expireAt($key, $timeout);
    	}
    	 
    	return $num;
    }
    
    public function getGoodsInfo($goods_id)
    {
    	$goods_inf = null;
    	$db_main = $this->getDbMain();
    	$sql = "SELECT * FROM card.goods_info WHERE id=$goods_id";
    	$rows = $db_main->query($sql);
    	
    	if (!empty($rows) && $rows->num_rows > 0) {
    		$goods_inf = $rows->fetch_assoc();
    	}
    	
    	return $goods_inf;
    }
    
    public function getNewerGiftCountUsed($uid)
    {
    	$ret = null;
    	
    	$now = time();
    	$key = "h_newer_gift_used:" . date('Ymd',$now) . ":" . $uid%1024;
    	
    	$redis = $this->getRedisMaster();
    	if (!empty($redis)) {
    		$ret = $redis->hGet($key, $uid . "");
    	}
    	
    	return $ret;
    }
    
    public function addNewerGiftCountUsed($uid)
    {
    	$now = time();
    	$key = "h_newer_gift_used:" . date('Ymd',$now) . ":" . $uid%1024;
    	
    	$redis = $this->getRedisMaster();
    	if (!empty($redis)) {
    		$redis->hIncrBy($key, $uid . "", 1);
    		$todayend = strtotime(date('Y-m-d'.'00:00:00',$now+3600*24));
    		$redis->expireAt($key, $todayend);
    	}
    }
    
    public function useExpActiveDoubleCard($uid, $prop_id, $num, $double_num, $duration)
    {
    	$err_code = $this->consume_prop($uid, $prop_id, $num);
    	
    	if ($err_code != 0) {
    		return $err_code;
    	}
    	
    	do {
    		$key = "str_exp_active_double:$uid";
    		$time = time();
    		 
    		$redis = $this->getRedisMaster();
    		if (empty($redis)) {
    			LogApi::logProcess("ToolModel::useExpActiveDoubleCard Failure redis null. uid:$uid prop:$prop_id num:$num");
    			$err_code = -1;
    			break;
    		}
    		 
    		$val = array(
    				'timestamp' => $time,
    				'duration' => $duration,
    				'multiple' => $double_num
    		);
    		 
    		if (!$redis->setex($key, $duration, json_encode($val))) {
    			LogApi::logProcess("ToolModel::useExpActiveDoubleCard Failure setex fail. uid:$uid prop:$prop_id num:$num");
    			$err_code = -1;
    		}
    	} while (0);

    	return $err_code;
    }
    
    public function expActiveDoubleCardEffect($uid)
    {
    	$key = "str_exp_active_double:$uid";
    	
    	$redis = $this->getRedisMaster();
    	
    	$val = $redis->get($key);
    	
    	if (!empty($val)) {
    		$val = json_decode($val, true);
    	}
    	
    	return $val;
    }
    
    public function useHotCard($uid, $prop_id, $num, $hot_value, $singer_id, $expire_time = 1800)
    {
    	$return = array(
    			'code' => 0
    	);
    	
    	$err_code = $this->consume_prop($uid, $prop_id, $num);
    	
    	if ($err_code == 0) {
    		$model_hot_rank = new hot_rank_model();
    		$res = $model_hot_rank->hot_card_used($singer_id, $hot_value * $num, $expire_time);
    		$return['rank_old'] = $res['old'];
    		$return['rank_new'] = $res['new'];
    	
    	}
    	
    	$return['code'] = $err_code;
    	
    	return $return;
    }
    
    public function consume_prop($uid, $prop_id, $num)
    {
 		$err_code = 0;
    	
    	$mysql = $this->getDbMain();
    	
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
    			$err_code = -1;
    			LogApi::logProcess("ToolModel::consume_prop sql error:$sql");
    			break;
    		}
    		
    		if ($num_before < $num) {
    			LogApi::logProcess("ToolModel::consume_prop num limit. uid:$uid prop:$prop_id num:$num num_now:$num_before");
    			$err_code = 122;	// 数量不足
    			break;
    		}
    		
    		$sql = "UPDATE card.user_goods_info SET num = num - $num WHERE ( uid = $uid && goods_id = $prop_id)";
    		$rows = $mysql->query($sql);
    		if(empty($rows)) {
    			LogApi::logProcess("ToolModel::consume_prop sql error:$sql");
    			$err_code = -1;
    			break;
    		}
    		
    		$err_code = 0;
    		
    	} while (0);
    	
    	if ($err_code == 0) {
    		$mysql->query("COMMIT");
    	} else {
    		$mysql->query("ROLLBACK");
    	}

    	return $err_code;
    }
    
    public function use_high_ladder_card($uid, $prop_id, $num, $singer_id, $rank)
    {
    	$err_code = $this->consume_prop($uid, $prop_id, $num);
    	if ($err_code != 0) {
    		return $err_code;
    	}
    	
    	$model_hot_rank = new hot_rank_model();
    	
    	$res = $model_hot_rank->high_ladder_card_used($singer_id, $rank);
    	
    	$return = array (
    			'code' => $err_code,
    			'b_changed' => $res['b_changed'],
    			'rank' => $res['rank']
    	);
    	
    	return $return;
    }

    // 判断主播本周收到的帮会票数量是否超过上限
    public function if_anchor_gticket_outof_limit($anchor_id, $number)
    {
        $day = date("w");
        $timestamp = time();
        $start = 0;
        if($day>=$start){
            $startdate_timestamp = mktime(0,0,0,date('m',$timestamp),date('d',$timestamp)-($day-$start),date('Y',$timestamp));
        } else {
            $startdate_timestamp = mktime(0,0,0,date('m',$timestamp),date('d',$timestamp)-7+$start-$day,date('Y',$timestamp));
        }

        $week_begin = date("Ymd", $startdate_timestamp);

        $key = "h_anchor_gticket_limit:$week_begin";
        $field = $anchor_id;

        $redis = $this->getRedisMaster();
        $rs = $redis->hIncrBy($key, $field, $number);

        $redis->expire($key, 10*24*60*60);

        $sys_parameters = new SysParametersModel();
        $limit = $sys_parameters->GetSysParameters(266, 'parm1');
        if (empty($limit)) {
            $limit = 10;//10000;
        }

        if ($rs >= $limit+1) {
            return true;
        }

        return false;
    }

    public function b_week_star($anchor_id)
    {
        $key = "last_tool_week_uids";

        $redis = $this->getRedisMaster();

        $ret = $redis->hGet($key, $anchor_id);

        if (!empty($ret)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function get_week_star_cheif_top3()
    {
        $key = "week_gift_total_top";
        $redis = $this->getRedisMaster();

        return $redis->zrevrange($key, 0, 2);
    }

    public function use_recommend_card($uid, $anchor_id, $prop_id, $num)
    {
        $err_code = 0;
        
        $mysql = $this->getDbMain();
        
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
                $err_code = -1;
                LogApi::logProcess("ToolModel::use_recommend_card sql error:$sql");
                break;
            }
            
            if ($num_before < $num) {
                LogApi::logProcess("ToolModel::use_recommend_card num limit. uid:$uid prop:$prop_id num:$num num_now:$num_before");
                $err_code = 122;    // 数量不足
                break;
            }
            
            $sql = "UPDATE card.user_goods_info SET num = num - $num WHERE ( uid = $uid && goods_id = $prop_id)";
            $rows = $mysql->query($sql);
            if(empty($rows)) {
                LogApi::logProcess("ToolModel::use_recommend_card sql error:$sql");
                $err_code = -1;
                break;
            }

            $sql = "INSERT INTO card.recommend_card_record (card_from, card_to, goods_id, num, createdate, status) VALUES ($uid, $anchor_id, $prop_id, $num, NOW(), 1)";
            $rows = $mysql->query($sql);
            if (empty($rows) || $mysql->affected_rows <= 0) {
                LogApi::logProcess("ToolModel::use_recommend_card sql error:$sql");
                $err_code = -1;
                break;
            }

            $key = "recommend_card:$uid";
            $field = $prop_id;
            $val = $num_before - $num;
            $redis = $this->getRedisMaster();
            $ret = $redis->hSet($key, $field, $val);
            if ($ret === false) {
                LogApi::logProcess("ToolModel::use_recommend_card redis error. key:$key field:$field val:$val");
                $err_code = -1;
                break;
            }

            $key = "h_recommend_card_recv:$anchor_id";
            $ret = $redis->hIncrBy($key, $field, $num);
            if ($ret === false) {
                LogApi::logProcess("ToolModel::use_recommend_card redis error. key:$key field:$field val:$val");
                $err_code = -1;
                break;
            }
            
            $err_code = 0;
        } while (0);
        
        if ($err_code == 0) {
            $mysql->query("COMMIT");
        } else {
            $mysql->query("ROLLBACK");
        }

        return $err_code;

    }
}

?>
