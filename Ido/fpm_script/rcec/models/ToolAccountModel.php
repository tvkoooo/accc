<?php

class ToolAccountModel extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function getTool ($uid, $toolId = 0, $endTime = 0)
    {
        $key = 'tool_account:' . $uid;
        $query = "select * from tool_account where uid=$uid and disable=0 and tool_qty>0";
        //if($toolId != 0){
        //	$query = $query . " and tool_id=$toolId";
        //}
        //if($endTime != 0){
        //	$query = $query . " and end_time=$endTime";
        //}
        // 用于测试，主动该修改数据库时的兼容
        //$this->cleanCache($uid);
        ToolApi::logProcess('ToolAccountModel::getTool query:' . $query);
        $rows = $this->read($key, $query);
        $count = count($rows);
        if ($count > 0) {
    		$return = array();
        	if($toolId != 0){
        		// 排除
        		for($i=0; $i<$count; $i++){
        			$row = $rows[$i];
        			if(intval($row['tool_id']) == $toolId
						&& intval($row['end_time']) == $endTime){
	        			$toolAccount = array(
	        				'uid' => intval($row['uid']),
	        				'tool_id' => intval($row['tool_id']),
	        				'tool_qty' => intval($row['tool_qty']),
	        				'begin_time' => intval($row['begin_time']),
	        				'end_time' => intval($row['end_time'])
	        			);
        				$return[] = $toolAccount;
        			}
        		}
        	}else{
        		for($i=0; $i<$count; $i++){
        			$row = $rows[$i];
        			if(intval($row['end_time']) == $endTime){
        				$toolAccount = array(
	        				'uid' => intval($row['uid']),
	        				'tool_id' => intval($row['tool_id']),
	        				'tool_qty' => intval($row['tool_qty']),
	        				'begin_time' => intval($row['begin_time']),
	        				'end_time' => intval($row['end_time'])
	        			);
        				$return[] = $toolAccount;
        			}
        		}
        	}
            return $return;
        }
        return false;
    }

    public function hasTool ($uid, $toolId, $qty = 0, $endTime = 0)
    {
        $tool = $this->getTool($uid, $toolId, $endTime);
        $count = $this->hasToolByPacketInfo($tool, $qty);
        if($count){
        }else{
        	if($qty <= 1){
            	$this->cleanCache($uid);
        	}
        }
        return $count;
    }
    public function hasToolByPacketInfo($toolAccountList, $qty){
    	if (empty($toolAccountList)) {
            return false;
        }
        $toolAccountCount = count($toolAccountList);
    	if ($toolAccountCount <= 0) {
    		return false;
    	}
    	$toolQty = 0;
    	for($i = 0; $i<$toolAccountCount; $i++){
    		$toolQty += $toolAccountList[$i]['tool_qty'];
    	}
        if ($toolQty < $qty) {
        	return false;
        }
        return $toolQty;
    }

    public function cleanCache ($uid)
    {
        $key = 'tool_account:' . $uid;
        $this->clean($key);
    }

    public function update ($uid, $toolId, $qty, $durationTime = 0)
    {
        if ($qty <= 0) {
            return false;
        }
        $beginTime = now();
        $endTime = 0;
        if($durationTime != 0){
        	$endTime = $beginTime + $durationTime;
        }

        // DBLE
        $db_main = $this->getDbMain();
        $sql = "SELECT uid FROM tool_account WHERE uid=$uid AND tool_id=$toolId AND end_time=$endTime AND disable=0";
        $rows = $db_main->query($sql);

        if (!empty($rows) && $rows->num_rows > 0) {
            $sql = "UPDATE tool_account SET tool_qty=tool_qty+$qty WHERE uid=$uid AND tool_id=$toolId AND end_time=$endTime AND disable=0";
        } else {
            $sql = "INSERT INTO tool_account (uid, tool_id, tool_qty, begin_time, end_time, disable, create_time) VALUES ($uid, $toolId, $qty, $beginTime, $endTime, 0, now())";
        }

        $rows = $db_main->query($sql);

        if (empty($rows) || $db_main->affected_rows <= 0) {
            LogApi::logProcess("[DBLElog] ToolAccountModel:update sql error:$sql");
        }

        $this->cleanCache($uid);
        return true;
    }

    public function consume ($uid, $toolId, $qty, $endTime = 0){
    	return $this->remove($uid, $toolId, $qty, $endTime);
    }
    public function remove ($uid, $toolId, $qty, $endTime = 0)
    {
        if ($qty <= 0) {
            return false;
        }
        $query = "UPDATE tool_account SET tool_qty = tool_qty - $qty 
            WHERE uid = $uid AND tool_id = $toolId AND end_time = $endTime AND tool_qty >= $qty";
        $result = $this->getDbMain()->query($query);
        if ($result) {
            $this->cleanCache($uid);
        }
        return $result;
    }
}
?>