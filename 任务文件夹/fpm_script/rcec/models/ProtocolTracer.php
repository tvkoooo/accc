<?php
class ProtocolTracer{
	private static $instance;
	private static $MAX_PROTOCOL_TIMEOUT_TIME = 150; //ms
	private $_nextProtocolSeqNum = 0;
	private $_pengingProtocols = array();
	
	public static function getInstance(){
		if (!self::$instance){
			self::$instance = new self();
			LogApi::logProcess("[ProtocolTracer::getInstance] new one");
		}
		return self::$instance;
	}
	private function __construct(){}
	private function __clone(){}
	function getNextSeqNum(){
		$thisSN = $this->_nextProtocolSeqNum;
		$this->_nextProtocolSeqNum = $this->_nextProtocolSeqNum + 1;
		return $thisSN;
	}
	function add($seqNum, $cmd_string, $cmd_data, $now){
		$item[] = array("cmd"=>$cmd_string, "cmd"=>$cmd_data, "begin_time"=>$now, "done_time"=>0);
		$_pengingProtocols[$seqNum] = $item; 
		LogApi::logProcess("[ProtocolTracer::add] seqNum=".$seqNum.", cmd=".$cmd_string.", data=".$cmd_data.", begin_time=".$now);
	}
	function done($seqNum, $now){
		if (!array_key_exists($seqNum ,$_pengingProtocols)){
			return;
		}

		$value = $_pengingProtocols[$seqNum];
		if (!array_key_exists("done_time",$value)){
			return;
		}
		$value["done_time"] = $now;
		
		LogApi::logProcess("[ProtocolTracer::done] seqNum=".$seqNum.", now=".$now);
	}
	
	function check($now){
		foreach($_pengingProtocols as $key => $value){
			if ($value["done_time"] !== 0){//已经处理完成了
				$used = $now - $value["done_time"];
				if ($used >= $MAX_PROTOCOL_TIMEOUT_TIME){
					LogApi::logProcess("[ProtocolTracer::check] WARN protocol done but used ".$used."ms");
				}
				
				//删除该项
				unset($_pengingProtocols[$key]);
			}
			else{//还没处理完
				$pass = $now - $value["begin_time"];
				if ($pass >= $MAX_PROTOCOL_TIMEOUT_TIME){
					LogApi::logProcess("[ProtocolTracer::check] ERROR protocol not done after ".$pass."ms");
				
					//删除该项
					unset($_pengingProtocols[$key]);
				}
			}
		}
	}

}

?>