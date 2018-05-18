<?php
	// zxy modify 2015-11-30 17:12:10
//	static $gConsumeUpdateTimeMap = array();
//	function ConsumeUpdateTimeMap(){
//		$consumeUpdateTimeMap = $GLOBALS["gConsumeUpdateTimeMap"];
//		
//		if(null == $consumeUpdateTimeMap){
//			$GLOBALS["gConsumeUpdateTimeMap"] = $consumeUpdateTimeMap = array();
//		}
//		return $consumeUpdateTimeMap;//$GLOBALS["gConsumeUpdateTimeMap"];
//	}
class BroadcastModel extends ModelBase {
    
    public function __construct ()
    {
        parent::__construct();
    }
    
	public function putSessionBroadcast($jsonStr, $tsNow) {
		$broadcastKey = 'Broadcast:Session';
		// 查询用户当前的名次
		$this->getRedisMaster()->set($broadcastKey, $jsonStr);
		
		ToolApi :: logProcess('BroadcastModel::putBroadcast key:' . $broadcastKey . ",jsonStr:" . $jsonStr);
	}

	public function getSessionBroadcast() {
		$broadcastKey = 'Broadcast:Session';
		$jsonStr = $this->getRedisMaster()->get($broadcastKey);
		ToolApi :: logProcess('BroadcastModel::getSessionBroadcast jsonStr:' . $jsonStr);
		return $jsonStr;
	}

}
?>
