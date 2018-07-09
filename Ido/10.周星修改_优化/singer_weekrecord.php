<?

//$path=dirname(__FILE__);
//include_once "$path/../../bases/GlobalConfig.php";
//include_once "$path/../../session/member/member_list.php" ;

// simple channel info table 
class singer_weekrecord{
	var $redis;
	var $db;
	
	public function __construct($redis, $db) {
		$this->redis = $redis;
		$this->db = $db;
	}

	//主播任务task_id 寻找任务类型t_id
	public function data_GetTidByTaskid($task_id){
	    $logfile=basename(__FILE__, '.php');
	    $t_id = 0;
	    $query = "SELECT t_id FROM card.task_info WHERE id = $task_id";
	
	    $rs = $this->db->query($query);
	    if($this->db->num_rows() > 0 ) {
            $row = $this->db->fetch_array($rs);
	        $t_id = intval($row['t_id']);	        
	    }
	    else
	    {
	        logs::addLog("singer_weekrecord::数据库读库失败******uid:$task_id, query:$query", $logfile);
	    }
	    return $t_id;
	}
	
	
	//主播uid获得主播周星礼物id
	public function data_GetWeekToolByUid($uid){
		$logfile=basename(__FILE__, '.php');
		$value = 0;
		$query = "SELECT weekTool FROM raidcall.anchor_info WHERE uid = $uid";

		$rs = $this->db->query($query);
		if($this->db->num_rows() > 0 ) {
            $row = $this->db->fetch_array($rs);
			$value = intval($row['weekTool']);
		}
		else 
        {
            logs::addLog("singer_weekrecord::数据库读库失败******uid:$uid, query:$query", $logfile);
        }
		return $value;
	}

	//根据物品id查询物品信息
	public function data_GetToolInfoByToolid($tool_id){
	    $logfile=basename(__FILE__, '.php');
	    $toolInfo = array();
	    //limit 0,30;只用于数据库容错，实际只有一条数据
	    $query = "SELECT price, name, category1, category2, gift_point_hot FROM rcec_main.`tool` WHERE id = $tool_id limit 0,30;";	
	    $rs = $this->db->query($query);
	    if($this->db->num_rows() > 0 ) {
            $row = $this->db->fetch_array($rs);
            $toolInfo = $row;

	    }
	    else
	    {
	        logs::addLog("singer_weekrecord::数据库读库失败******tool_id:$tool_id, query:$query", $logfile);
	    }
	    return $toolInfo;
	}
	
	//更加主播id查询主播sid
	public function data_GetSidBySingerid($singer_id){
	    $logfile=basename(__FILE__, '.php');
	    $sid = 0;
	    //limit 0,30;只用于数据库容错，实际只有一条数据
	    $query = "SELECT sid FROM raidcall.sess_info WHERE owner = $singer_id limit 0,30;";
	    $rs = $this->db->query($query);
	    if($this->db->num_rows() > 0 ) {
            $row = $this->db->fetch_array($rs);
	        $sid = $row["sid"];	        
	    }
	    else
	    {
	        logs::addLog("singer_weekrecord::数据库读库失败******singer_id:$singer_id, query:$query", $logfile);
	    }
	    return $sid;
	}

    public function data_AppendWeekToolConsumeRecord($info)
    {
		$logfile=basename(__FILE__, '.php');
        $query = "INSERT INTO rcec_record.week_tool_consume_record
        (`record_time`, `uid`, `receiver_uid`, `tool_id`, `tool_category1`, `tool_category2`,
        `qty`, `tool_price`, `total_coins_cost`)
        VALUES ($info->now,$info->uid,$info->singerUid,$info->tid,$info->tool_category1,$info->tool_category2,
        $info->qty,$info->tool_price,$info->total_coins_cost)";
        $rs = $this->db->query($query);
		logs::addLog("singer_weekrecord::data_AppendWeekToolConsumeRecord ****query:$query", $logfile);
        if(!$rs)
        {
			logs::addLog("singer_weekrecord::AppendWeekToolConsumeRecord******excute sql error!!!****singerUid:$info->singerUid***tid:$info->tid***query:$query", $logfile);
            return;
        }
		
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
		
        $this->redis->zIncrBy($key, $score, $info->singerUid);
        $this->redis->expire($key, 7 * 24 * 60 * 60);
		
	}
	
	public function data_GetSingerTaskGoldByTid($Tid){
	    $logfile=basename(__FILE__, '.php');
	    $gold = 0;
	    $num = 0;
	    $goodid = 0;
	    $price =0;
	    //主播终极任务tid 是 23005，奖励是阳光浴8000阳光，写死转换为3000金币
	    if (23005 == $Tid)
	    {
	        $gold = 3000;
	        return $gold;
	    }
	    
	    $query = "select target_params1 as num,target_params2 as goodid from card.task_conf where id=$Tid;";	
	    $rs = $this->db->query($query);
	    if($this->db->num_rows() > 0 ) {
            $row = $this->db->fetch_array($rs);
            $num = intval($row['num']);
            $goodid = intval($row['goodid']);
	    }
	    else
	    {
	        logs::addLog("singer_weekrecord::数据库读库失败******Tid:$Tid, query:$query", $logfile);
	    }
	    if (0 == $goodid)
	    {
	        return $gold;
	    }
	    $query = "select price from rcec_main.`tool` where id=$goodid;";
	    $rs = $this->db->query($query);
	    if($this->db->num_rows() > 0 ) {
            $row = $this->db->fetch_array($rs);
            $price = intval($row['price']);
	    }
	    else
	    {
	        logs::addLog("singer_weekrecord::数据库读库失败******Tid:$Tid, query:$query", $logfile);
	    }
	    $gold = $num * $price; 
	    return $gold;
	}
	
	

}

class data_WeekToolConsumeRecordInfo
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
?>
