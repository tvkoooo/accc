<?php

class SingerGuardApplyModel extends ModelBase
{

    protected $priceInfo = array(
        array(
            'duration' => 1,
            'price' => 28800,
            'discount' => ''
        ),
        array(
            'duration' => 3,
            'price' => 68800,
            'discount' => '9.8'
        ),
//        array(
//            'duration' => 6,
//            'price' => 240000,
//            'discount' => '9'
//        ),
        array(
            'duration' => 12,
            'price' => 208800,
            'discount' => '8'
        )
    );

    public function __construct()
    {
        parent::__construct();
    }

    public function getPriceInfo()
    {
        return $this->priceInfo;
    }

    public function getPriceByDuration($duration)
    {
        $priceInfo = $this->getPriceInfo();
        foreach ($priceInfo as $info) {
            if ($info['duration'] == $duration) {
                return $info['price'];
            }
        }
        return false;
    }

    public function addApplyRecord($uid, $singerUid, $duration, $price, $sid)
    {
        $now = time();
        $query = "insert into singer_guard_apply 
            set uid = $uid, sid = $sid, singer_uid = $singerUid, duration = $duration, price = $price,
            status = 2, apply_time = $now, handle_time = $now ";
        $rs = $this->getDbMain()->query($query);
        if ($rs) {
            $this->clearCache($uid, $singerUid);
        }
    }

    public function updateApplyStatus($id, $status)
    {
        $query = "select * from singer_guard_apply where id = $id and status = 1";
        $rs = $this->getDbMain()->query($query);
        if (!$rs || $rs->num_rows == 0) {
            return false;
        }
        $row = $rs->fetch_assoc();
        $uid = $row['uid'];
        $singerUid = $row['singer_uid'];
        $now = time();
        $query = "update singer_guard_apply set status = $status, handle_time = $now where id = $id";
        $rs = $this->getDbMain()->query($query);
        if ($rs) {
            $this->clearCache($uid, $singerUid);
        }
        return $row;
    }

    public function clearCache($uid, $singerUid)
    {
        $this->getRedisMaster()->del('singer_guard_apply:' . $uid);
        $this->getRedisMaster()->del('singer_guard_apply_for:' . $uid);
        $this->getRedisMaster()->del('singer_guard_apply:' . $singerUid);
        $this->getRedisMaster()->del('singer_guard_apply_for:' . $singerUid);
    }

    public function hasNotHanlded($uid, $singerUid)
    {
        $query = "select id from singer_guard_apply where uid = $uid and singer_uid = $singerUid and status = 1 ";
        $rs = $this->getDbMain()->query($query);
        if ($rs->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getList($uid, $isSinger = false)
    {
        if ($isSinger) {
            $key = 'singer_guard_apply_for:' . $uid;
            $query = "select * from singer_guard_apply where singer_uid = $uid ";
        } else {
            $key = 'singer_guard_apply:' . $uid;
            $query = "select * from singer_guard_apply where uid = $uid ";
        }
        $rows = $this->read($key, $query);
        if (count($rows) > 0) {
            return $rows;
        }
        return false;
    }

    public function getApplyListInfo($uid, $isSinger = false)
    {
        $userAttrModel = new UserAttributeModel();
        $newList = array();
        $list = $this->getList($uid, $isSinger);
        if ($list) {
            foreach ($list as $guard) {
                if ($guard['status'] == 2) {
                    continue; // 通過狀態的不返回
                }
                $info = array();
                $info['id'] = $guard['id'];
                $info['uid'] = $guard['uid'];
                $info['singerUid'] = $guard['singer_uid'];
                $info['status'] = $guard['status'];
                $info['applyTime'] = $guard['apply_time'];
                $info['duration'] = $guard['duration'];
                $info['price'] = $guard['price'];
                $info['handleTime'] = $guard['handle_time'];
                $userInfo = $userAttrModel->getUserInfo($guard['uid']);
                $info['nick'] = $userInfo['nick'];
                $userInfo = $userAttrModel->getUserInfo($guard['singer_uid']);
                $info['singerNick'] = $userInfo['nick'];
                $newList[] = $info;
            }
        }
        return $newList;
    }
}
