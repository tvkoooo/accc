<?php
/*
class SessionPermissionModel extends ModelBase
{

    const NUL_ROLE = 0;

    const NORMAL = 25; // 普通人员

    const DELETED = 50; // unuse

    const TMPVIP = 66; // unuse

    const VIP = 88; // unuse

    const MEMBER = 100; // 会员

    const CMANAGER = 150; // 频道管理员

    const PMANAGER = 175; // 频道管理员

    const MANAGER = 200; // 管理员

    const OWNER = 255; // 创建者

    const KEFU = 300; // 客服

    const SA = 1000; // 超级管理员

    public function __construct ()
    {
        parent::__construct();
    }

    public function getPerm ($sid, $permCode)
    {
        $key = 'session_permission:' . $sid . '_' . $permCode;
        $query = "select * from session_permission where sid=$sid and permission_code = '$permCode'";
        $rows = $this->read($key, $query);
        if (count($rows) == 1) {
            return $rows[0];
        } else {
            return false;
        }
    }

    public function hasPerm ($sid, $permCode)
    {
        $perm = $this->getPerm($sid, $permCode);
        if (empty($perm)) {
            return false;
        }
        $now = time();
        if ($now < $perm['time_end']) {
            return true;
        } else {
            $this->cleanCache($sid, $permCode);
            return false;
        }
    }

    public function cleanCache ($sid, $permCode)
    {
        $key = 'session_permission:' . $sid . '_' . $permCode;
        $this->clean($key);
    }
}
*/
?>