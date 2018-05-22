<?php

class ToolSubscriptionModel extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function getTool ($uid, $toolId = 0)
    {
        $key = 'tool_subscription:' . $uid;
        $query = "select * from tool_subscription where uid = $uid";
        $rows = $this->read($key, $query);
        if (count($rows) > 0) {
            if ($toolId) {
                foreach ($rows as $row) {
                    if ($row['tool_id'] == $toolId) {
                        return $row;
                    }
                }
            } else {
                return $rows;
            }
        }
        return false;
    }

    public function hasTool ($uid, $toolId)
    {
        if (empty($toolId)) {
            return false;
        }
        $tool = $this->getTool($uid, $toolId);
        if (empty($tool)) {
            return false;
        }
        if (time() < $tool['expire']) {
            return true;
        } else {
            $this->cleanCache($uid);
            return false;
        }
    }

    public function cleanCache ($uid)
    {
        $key = 'tool_subscription:' . $uid;
        $this->clean($key);
    }

    public function update ($uid, $toolId, $qty)
    {
        $subsInfo = $this->getTool($uid, $toolId);
        if ($subsInfo['expire'] > time()) {
            $expire = $subsInfo['expire'] + $qty * 86400;
        } else {
            $expire = time() + $qty * 86400;
        }

        // DBLE
        $db_main = $this->getDbMain();
        $sql = "SELECT uid FROM tool_subscription WHERE uid=$uid AND tool_id=$toolId";
        $rows = $db_main->query($sql);
        if (!empty($rows) && $rows->num_rows > 0) {
            $sql = "UPDATE tool_subscription SET expire=$expire WHERE uid=$uid AND tool_id=$toolId";
        } else {
            $sql = "INSERT INTO tool_subscription (uid, tool_id, expire) VALUES ($uid, $toolId, $expire)";
        }
        $rows = $db_main->query($sql);

        if (empty($rows) || $db_main->affected_rows <= 0) {
            LogApi::logProcess("[DBLElog] ToolSubscriptionModel:update sql error:$sql");
        }

        $this->cleanCache($uid);
        return true;
    }
}
?>