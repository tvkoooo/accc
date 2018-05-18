<?php
	// liuhw add 2016-03-02
class CharismaModel extends ModelBase {
    
    public static $SINGER_COUNT_MOD_NUMBER = 1024;
    
    public function __construct ()
    {
        parent::__construct();
    }
    public static function HashSingerMoneyCountKey($uid)
    {
        $mod = $uid % CharismaModel::$SINGER_COUNT_MOD_NUMBER;
        return "singer:money:count:$mod";
    }
    public function AddSingerMoneyCount($uid,$delta)
    {
        $value = 0;
        if (0 != $uid)
        {
            $key = CharismaModel::HashSingerMoneyCountKey($uid);
            $value = $this->getRedisMaster()->hIncrBy($key,$uid,$delta);
            if(empty($value)){$value = 0;}
        }
        return $value;
    }
    public function GetSingerMoneyCount($uid)
    {
        $value = 0;
        if (0 != $uid)
        {
            $key = CharismaModel::HashSingerMoneyCountKey($uid);
            $value = $this->getRedisMaster()->hGet($key,$uid);
            if(empty($value)){$value = 0;}
        }
        return $value;
    }
	private function generateCharismaRecord($value){
		return '' . $value . ':' . Utils::guid();
	}
	
	//获得主播阳光总值
	public function getAnchorSun($singerUid){
	    $scoreKey = 'anchorSunshine:score';
        $num = $this->getRedisMaster()->zScore($scoreKey,$singerUid);
        if(empty($num)){
            $num = 0;
        }
        return $num;
	}
	
	// zokay:阳光值 （有序集合）
	public function anchorSunshine($singerUid,$score){
		
	    $scoreKey = 'anchorSunshine:score';
	    $total = $this->getRedisMaster()->zIncrBy($scoreKey,$score, $singerUid);
	    return $total;
	}
	
	//返回秀币所对应的魅力值
	public function updateCharisma($sid, $uid, $singerUid, $value, $tsTime) {
		//主播魅力值总key（通过该key可以获得到主播对应的魅力总值）
		$charismaKey = 'charisma:' . $singerUid;
		//单条记录key
		$charismaRecord = 'charismaRecord:' . $singerUid;
		//超时时间
		$outTime = $tsTime-60*60*24*7;
		// 生成序列化记录（只是为了防止重复）
		$record = $this->generateCharismaRecord($value);
	    // 将当前魅力值计入主播魅力值记录里（record作为key不能重复）
		$this->getRedisRankMaster()->zIncrBy($charismaRecord, $tsTime, $record);	
					
		// 魅力值总值
		$totalValue = $this->getRedisRankMaster()->incr($charismaKey, $value);
		
		// 获取该主播当前时间之前的的魅力值记录数据
		$outTimeRecords = $this->getRedisRankSlave()->zRangeByScore($charismaRecord, 0, $outTime, array('withscores' => TRUE));
		if($outTimeRecords && count($outTimeRecords) > 0){
			// 遍历超时记录
			foreach ($outTimeRecords as $outTimeRecord => $score){
				$recordInfo = explode(':', $outTimeRecord);
				// 移除主播7天前的魅力值数据
				$totalValue = $this->getRedisRankMaster()->incr($charismaKey, 0-intval($recordInfo[0]));
		    }
			// 移除该区间的数据
			$this->getRedisRankSlave()->zRemRangeByScore($charismaRecord, 0, $outTime);
		}
		// 测试（30秒后，该条数据，将被减掉）
		//getRedisRankMaster()->zIncrBy($charismaRecord, $tsTime-7*24*60*60+30, $record);
		
		//return $totalValue;
		return $value;
	}
	// 获取主播魅力总值（需要判断是否有超过7天的数据，如果有则删除）
	public function getCharismaNew($singerUid){
		$tsTime = time();
		$charismaKey = 'charisma:' . $singerUid;
		$charismaRecord = 'charismaRecord:' . $singerUid;
		$outTime = $tsTime-60*60*24*7;				
		// 魅力值总值
		$totalValue = $this->getRedisRankMaster()->incr($charismaKey, 0);
		
		// 获取该主播获取的魅力值记录数据
		$outTimeRecords = $this->getRedisRankSlave()->zRangeByScore($charismaRecord, 0, $outTime, array('withscores' => TRUE));
		if($outTimeRecords && count($outTimeRecords) > 0){
			// 遍历超时记录
			foreach ($outTimeRecords as $outTimeRecord => $score){
				$recordInfo = explode(':', $outTimeRecord);
				// 移除主播7天前的魅力值数据
				$totalValue = $this->getRedisRankMaster()->incr($charismaKey, 0-intval($recordInfo[0]));
		    }
			// 移除该区间的数据
			$this->getRedisRankSlave()->zRemRangeByScore($charismaRecord, 0, $outTime);
		}
		// 测试（30秒后，该条数据，将被减掉）
		//getRedisRankMaster()->zIncrBy($charismaRecord, $tsTime-7*24*60*60+30, $record);
		
		//$root = array();
		//$root['charisma'] = $totalValue;
		
		LogApi::logProcess('*************cmd: PGetCharisma::getCharismaNew**********uid:' . $singerUid . ' totalCharisma: ' . $totalValue );
		
		return $totalValue;
	}
}
?>
