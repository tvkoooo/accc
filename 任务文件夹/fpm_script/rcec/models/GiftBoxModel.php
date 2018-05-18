<?php

class GiftBoxModel extends ModelBase
{

    public $duration = 15; // 小寶箱顯示的秒數
    public $boxs = array(
        1 => array(
            'explodeBoxAt' => 660,
            'boxType' => 1,
            'userNum' => 11,
            'giftNum' => 1,
            'rate' => 0.01,
            'luckyRate' => 0.1,
            'giftList' => '77,78,79,83',
            'luckyGiftList' => '14,17,20,24,35,36,37,46,47,48,49,80,81,84'
        ),
        2 => array(
            'explodeBoxAt' => 1314,
            'boxType' => 2,
            'userNum' => 21,
            'giftNum' => 1,
            'rate' => 0.02,
            'luckyRate' => 0.05,
            'giftList' => '77,78,79,83',
            'luckyGiftList' => '14,17,20,24,35,36,37,46,47,48,49,80,81,84'
        ),
        3 => array(
            'explodeBoxAt' => 5200,
            'boxType' => 3,
            'userNum' => 51,
            'giftNum' => 1,
            'rate' => 0.03,
            'luckyRate' => 0.02,
            'giftList' => '77,78,79,83',
            'luckyGiftList' => '14,17,20,24,35,36,37,46,47,48,49,80,81,84'
        ),
        4 => array(
            'explodeBoxAt' => 13140,
            'boxType' => 4,
            'userNum' => 81,
            'giftNum' => 1,
            'rate' => 0.04,
            'luckyRate' => 0.012,
            'giftList' => '77,78,79,83',
            'luckyGiftList' => '14,17,20,24,35,36,37,46,47,48,49,80,81,84'
        ),
        5 => array(
            'explodeBoxAt' => 33440,
            'boxType' => 5,
            'userNum' => 101,
            'giftNum' => 1,
            'rate' => 0.05,
            'luckyRate' => 0.01,
            'giftList' => '77,78,79,83',
            'luckyGiftList' => '14,17,20,24,35,36,37,46,47,48,49,80,81,84'
        )
    );
    
    public function __construct ()
    {
        parent::__construct();
    }

    public function getBoxList ()
    {
        $result = array();
        foreach ($this->boxs as $box) {
            $result[] = $box;
        }
        return $result;
    }

    public function explodeBox ($receivedCoins, $singerUid)
    {
        $userAttrModel = new UserAttributeModel();
        for ($i = count($this->boxs); $i > 0; $i --) {
            $box = $this->boxs[$i];
            if ($receivedCoins >= $box['explodeBoxAt']) {
                $exploded = $userAttrModel->getStatusByUid($singerUid, 'box_explode_' . $i);
                if ($exploded) {
                    return false;
                }
                $userNum = $box['userNum'];
                $boxType = $box['boxType'];
                $explodeBoxAt = $box['explodeBoxAt'];
                for ($j = $i - 1; $j > 0; $j --) {
                    if ($userAttrModel->getStatusByUid($singerUid, 'box_explode_' . $j) == 1) {
                        $userNum += $this->boxs[$j]['userNum'];
                    }
                }
                $userAttrModel->setStatusByUid($singerUid, 'box_explode_' . $boxType, 1);
                $userAttrModel->setStatusByUid($singerUid, 'box_user_num', $userNum);
                // 通過主播累加的秀點來計算發給用戶的禮物數量
                $receivedPoints = $userAttrModel->getStatusByUid($singerUid, 'received_points');
                $luckyGiftNum = floor($receivedPoints * $box['rate'] * 0.1);
                if ($luckyGiftNum < 0) {
                    $luckyGiftNum = 0;
                    $userAttrModel->setStatusByUid($singerUid, 'received_points', 0);
                }
                $userAttrModel->setStatusByUid($singerUid, 'box_lucky_gift_num_' . $boxType, $luckyGiftNum);
                // 爆完最高一級的寶箱后，重置所有狀態
                if ($boxType == count($this->boxs)) {
                    $this->initGiftBox($singerUid, false);
                }
                // Logger::fileLog("explodeBox ($receivedCoins, $singerUid) 觸發爆箱
                // " . $box['explodeBoxAt']);
                return $boxType;
            }
        }
        return false;
    }

    public function openGiftBox ($uid, $box)
    {
        $toolModel = new ToolModel();
        $boxInfo = $this->boxs[$box];
        $giftArray = explode(',', $boxInfo['giftList']);
        $giftId = $giftArray[array_rand($giftArray)];
        $giftNum = $boxInfo['giftNum'];
        
        $toolAccoModel = new ToolAccountModel();
        $toolAccoModel->update($uid, $giftId, $giftNum);
        $toolAccoRecordModel = new ToolAccountRecordModel();
        $toolAccoRecordModel->addRecord($uid, '4', $giftId, $giftNum);
        $tool = $toolModel->getToolByTid($giftId);
        // Logger::fileLog("openGiftBox $uid 獲得小禮物 $giftNum " . $tool['name']);
        return array(
            'amount' => $giftNum,
            'gift' => $giftId,
            'giftName' => $tool['name']
        );
    }

    public function openLuckyGiftBox ($uid, $box, $singerUid)
    {
        $boxInfo = $this->boxs[$box];
        // 先按一定幾率抽獎
        if (rand(1, 1000) > $boxInfo['luckyRate'] * 1000) {
            return false;
        }
        $giftArray = explode(',', $boxInfo['luckyGiftList']);
        $giftId = $giftArray[array_rand($giftArray)];
        // 通過主播累加的秀點來計算發給用戶的禮物數量
        $userAttrModel = new UserAttributeModel();
        $luckyGiftNum = $userAttrModel->getStatusByUid($singerUid, 'box_lucky_gift_num_' . $boxInfo['boxType']);
        if ($luckyGiftNum <= 0) {
            // Logger::fileLog("openLuckyGiftBox 秀點低，禮物數量小於0");
            return false;
        }
        $toolAccoModel = new ToolAccountModel();
        $toolAccoModel->update($uid, $giftId, $luckyGiftNum);
        $toolAccoRecordModel = new ToolAccountRecordModel();
        $toolAccoRecordModel->addRecord($uid, '4', $giftId, $luckyGiftNum);
        $toolModel = new ToolModel();
        $tool = $toolModel->getToolByTid($giftId);
        // Logger::fileLog("openLuckyGiftBox $uid 中大獎 $luckyGiftNum " .
        // $tool['name']);
        return array(
            'amount' => $luckyGiftNum,
            'gift' => $giftId,
            'giftName' => $tool['name']
        );
    }

    public function initGiftBox ($singerUid, $cleanAll = true)
    {
        $userAttrModel = new UserAttributeModel();
        $userAttrModel->delStatusByUid($singerUid, 'received_coins');
        $userAttrModel->delStatusByUid($singerUid, 'received_points');
        if ($cleanAll) {
            $userAttrModel->delStatusByUid($singerUid, 'box_user_num');
        }
        for ($i = count($this->boxs); $i > 0; $i --) {
            $userAttrModel->delStatusByUid($singerUid, 'box_explode_' . $i);
            if ($cleanAll) {
                $userAttrModel->delStatusByUid($singerUid, 'box_lucky_gift_num_' . $i);
            }
        }
    }
    // public function reloadGiftBox ($singerUid, $receivedCoins)
    // {
    // $this->userAttrModel->statusIncrease($singerUid, 'received_coins',
// $receivedCoins);
    // $this->userAttrModel->statusIncrease($singerUid, 'received_points',
// $receivedCoins * 0.4);
    // for ($i = count($this->boxs); $i > 0; $i --) {
    // $this->userAttrModel->delStatusByUid($singerUid, 'box_explode_' . $i);
    // }
    // }
}