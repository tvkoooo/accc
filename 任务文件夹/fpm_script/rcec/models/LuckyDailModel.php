<?php

class LuckyDailModel extends ModelBase
{
    public function __construct ()
    {
        parent::__construct();
    }

    public function getGiftPacket($uid)
    {
        $giftId = array(
            77, 78, 79, 83, 99, 104, 105, 106
        );
        $giftNum = array(
            1, 10, 30, 66, 188, 360, 520, 777
        );
        shuffle($giftId);
        $giftId = array_slice($giftId, 0, 8);
        $packet = array();
        foreach ($giftNum as $key => $num) {
            $id = $giftId[$key];
            $packet[] = array(
                'id' => $id,
                'num' => $num
            );
        }
        $userAttrModel = new UserAttributeModel();
        $userAttrModel->setStatusByUid($uid, 'dail_gift_packet', json_encode($packet));
        return $packet;
    }

    public function canDail($uid)
    {
        $userAttrModel = new UserAttributeModel();
        $date = date('Ymd');
        $lastDailDate = $userAttrModel->getStatusByUid($uid, 'last_dail_date');
        if (!empty($lastDailDate) && $lastDailDate == $date) {
            return false;
        }
        return true;
    }

    private function shake()
    {
        $packages = array(
            1 => 0.15,
            10 => 0.35,
            30 => 0.25,
            66 => 0.15,
            188 => 0.04,
            360 => 0.03,
            520 => 0.02,
            777 => 0.01
        );
        $max = 10000;
        $number = rand(1, $max);
        $start = 1;
        foreach ($packages as $key => $rate) {
            $end = $start + $max * $rate - 1;
            if ($number >= $start && $number <= $end) {
                return $key;
            }
            $start = $end + 1;
        }
        return 1;
    }

    public function drawDail($uid)
    {
        if (!$this->canDail($uid)) {
            return false;
        }
        $userAttrModel = new UserAttributeModel();
        $packet = $userAttrModel->getStatusByUid($uid, 'dail_gift_packet');
        if (empty($packet)) {
            $packet = json_encode($this->getGiftPacket($uid));
        }
        $date = date('Ymd');
        $userAttrModel->setStatusByUid($uid, 'last_dail_date', $date);
        $packet = json_decode($packet, true);
        
        $userFbModel = new UserFacebookInfoModel();
        if (!$userFbModel->isFbBound($uid)) {
            $num = 1;
        } else {
            $num = $this->shake();
        }
        foreach ($packet as $gift) {
            if ($gift['num'] == $num) {
                $result = $gift;
                break;
            }
        }
        $toolAccoModel = new ToolAccountModel();
        $toolAccoModel->update($uid, $result['id'], $result['num']);
        $toolAccoRecordModel = new ToolAccountRecordModel();
        $toolAccoRecordModel->addRecord($uid, '9', $result['id'], $result['num']);
        return $result;
    }
}