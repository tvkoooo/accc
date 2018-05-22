<?php

class LoginPacketModel extends ModelBase
{

    private $packets = array(
        1 => array(
            array(
                'giftId' => 99,
                'giftNum' => 10
            )
        ),
        2 => array(
            array(
                'giftId' => 77,
                'giftNum' => 10
            ),
            array(
                'giftId' => 78,
                'giftNum' => 10
            ),
            array(
                'giftId' => 83,
                'giftNum' => 10
            )
        ),
        3 => array(
            array(
                'giftId' => 77,
                'giftNum' => 20
            ),
            array(
                'giftId' => 78,
                'giftNum' => 20
            ),
            array(
                'giftId' => 83,
                'giftNum' => 20
            )
        ),
        4 => array(
            array(
                'giftId' => 77,
                'giftNum' => 30
            ),
            array(
                'giftId' => 78,
                'giftNum' => 30
            ),
            array(
                'giftId' => 83,
                'giftNum' => 30
            )
        ),
        5 => array(
            array(
                'giftId' => 98,
                'giftNum' => 20
            ),
            array(
                'giftId' => 100,
                'giftNum' => 20
            ),
            array(
                'giftId' => 102,
                'giftNum' => 20
            ),
            array(
                'giftId' => 103,
                'giftNum' => 20
            )
        ),
        6 => array(
            array(
                'giftId' => 98,
                'giftNum' => 30
            ),
            array(
                'giftId' => 100,
                'giftNum' => 30
            ),
            array(
                'giftId' => 102,
                'giftNum' => 30
            ),
            array(
                'giftId' => 103,
                'giftNum' => 30
            )
        ),
        7 => array(
            array(
                'giftId' => 98,
                'giftNum' => 40
            ),
            array(
                'giftId' => 100,
                'giftNum' => 40
            ),
            array(
                'giftId' => 102,
                'giftNum' => 40
            ),
            array(
                'giftId' => 103,
                'giftNum' => 40
            )
        )
    );
    
    public function __construct ()
    {
        parent::__construct();
    }

    public function getLoginPackets ()
    {
        $activityModel = new ActivityModel();
        /* if ($activityModel->isActivityOpen()) {
            return $activityModel->getLoginPackets();
        } */
        return $this->packets;
    }

    public function updateLoginPacketStatus ($uid, $currentDate, $continue = false)
    {
        $userAttrModel = new UserAttributeModel();
        $userAttrModel->setStatusByUid($uid, 'last_login_date', $currentDate);
        if ($continue) {
            $userAttrModel->statusIncrease($uid, 'login_count');
        } else {
            $userAttrModel->setStatusByUid($uid, 'login_count', 1);
            $userAttrModel->setStatusByUid($uid, 'login_packet_status', '');
        }
    }

    public function loginPacketSent ($loginPacketStatus, $loginCount)
    {
        if (empty($loginPacketStatus)) {
            return false;
        }
        $max = max(explode(',', $loginPacketStatus));
        if ($loginCount <= $max) {
            return true;
        }
        return false;
    }

    public function loginPacketList ($uid)
    {
        $userAttrModel = new UserAttributeModel();
        $list = array();
        $loginPacketStatus = $userAttrModel->getStatusByUid($uid, 'login_packet_status');
        $packets = $this->getLoginPackets();
        foreach ($packets as $i => $packet) {
            $newPacket = array();
            foreach ($packet as $gift) {
                $newGift = array();
                $toolModel = new ToolModel();
                $giftData = $toolModel->getToolByTid($gift['giftId']);
                $newGift['id'] = $giftData['id'];
                $newGift['name'] = $giftData['name'];
                $newGift['image'] = $giftData['resource'];
                $newGift['num'] = $gift['giftNum'];
                $newPacket[] = $newGift;
            }
            $list[$i]['packet'] = $newPacket;
            $list[$i]['status'] = $this->loginPacketSent($loginPacketStatus, $i);
        }
        return $list;
    }

    public function getLoginPacket ($uid)
    {
        $userAttrModel = new UserAttributeModel();
        $currentDate = date('Ymd');
        $lastLoginDate = $userAttrModel->getStatusByUid($uid, 'last_login_date');
        $loginCount = $userAttrModel->getStatusByUid($uid, 'login_count');
        $loginPacketStatus = $userAttrModel->getStatusByUid($uid, 'login_packet_status');
        if (empty($lastLoginDate)) {
            $this->updateLoginPacketStatus($uid, $currentDate);
        } else {
            $num = (strtotime($currentDate) - strtotime($lastLoginDate)) / 86400;
            if ($num == 0) {
                // 當天
                if ($this->loginPacketSent($loginPacketStatus, $loginCount)) {
                    // 當天已領取的
                } else {
                    // 當天未領取
                }
            } elseif ($num == 1) {
                // 第二天
                if ($loginCount < 7) {
                    // 連續登陸7天內的情況
                    $this->updateLoginPacketStatus($uid, $currentDate, true);
                } else {
                    // 第八天，重新回到第一天
                    $this->updateLoginPacketStatus($uid, $currentDate);
                }
            } elseif ($num > 1) {
                // 連續登陸中斷，重新回到第一天
                $this->updateLoginPacketStatus($uid, $currentDate);
            }
        }
        $packetList = $this->loginPacketList($uid);
        $loginCount = $userAttrModel->getStatusByUid($uid, 'login_count');
        return array(
            'packetList' => $packetList,
            'loginCount' => $loginCount
        );
    }

    public function sendLoginPacket ($uid)
    {
        $userAttrModel = new UserAttributeModel();
        $packets = $this->getLoginPackets();
        $loginCount = $userAttrModel->getStatusByUid($uid, 'login_count');
        $loginPacketStatus = $userAttrModel->getStatusByUid($uid, 'login_packet_status');
        if ($this->loginPacketSent($loginPacketStatus, $loginCount)) {
            return false;
        }
        if (! isset($packets[$loginCount])) {
            return false;
        }
        $toolAccoModel = new ToolAccountModel();
        $packet = $packets[$loginCount];
        foreach ($packet as $giftInfo) {
            $giftId = $giftInfo['giftId'];
            $giftNum = $giftInfo['giftNum'];
            $toolAccoModel->update($uid, $giftId, $giftNum);
            $toolAccoRecordModel = new ToolAccountRecordModel();
            $toolAccoRecordModel->addRecord($uid, '9', $giftId, $giftNum);
        }
        $loginPacketStatus = $userAttrModel->getStatusByUid($uid, 'login_packet_status');
        if (empty($loginPacketStatus)) {
            $loginPacketStatus = $loginCount;
        } else {
            $loginPacketStatus .= ',' . $loginCount;
        }
        $userAttrModel->setStatusByUid($uid, 'login_packet_status', $loginCount);
        return $packet;
    }
}
?>