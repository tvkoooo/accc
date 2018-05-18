<?php

class SessionAttributeModel extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function getAttrBySid ($sid)
    {
        $key = 'session_attribute:' . $sid;
        $query = "select * from session_attribute where sid = $sid ";
        $rows = $this->read($key, $query, 0, 'dbMain', false);
        if (count($rows) == 1) {
            return $rows[0];
        } else {
            $insert = "INSERT INTO `session_attribute` (`sid`) VALUES ($sid)";
            $this->getDbMain()->query($insert, false);
            return array(
                'sid' => $sid,
                'charm' => '0',
                'point_balance' => '0',
                'total_consumption' => '0'
            );
        }
    }

    public function addPoint ($sid, $point)
    {
        $sid = (int) $sid;
        $coin = (int) $point;
        if ($point <= 0) {
            return false;
        }
        $query = "update session_attribute set point_balance = point_balance + $point where sid = $sid";
        $rs = $this->getDbMain()->query($query);
        if ($rs == true && $this->getDbMain()->affected_rows > 0) {
            $this->cleanCache($sid);
            return true;
        }
        return false;
    }

    public function cleanCache ($sid)
    {
        $key = 'session_attribute:' . $sid;
        $this->clean($key);
    }

    public function getStatusBySid ($sid, $field)
    {
        $key = 'session_status:' . $sid;
        return $this->getRedisSlave()->hGet($key, $field);
    }

    public function setStatusBySid ($sid, $field, $value)
    {
        $key = 'session_status:' . $sid;
        $this->getRedisMaster()->hSet($key, $field, $value);
    }
}
?>