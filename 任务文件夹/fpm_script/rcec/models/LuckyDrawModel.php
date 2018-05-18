<?php

class LuckyDrawModel extends ModelBase
{

    public $refreshDuration = 5;

    public $refreshCost = 1;

    private $eggs = array(
        'bronze' => array(
            'price' => 8,
            'priceLow' => 3,
            'priceType' => 1, // 1=銀豆,2=秀幣
            'rate' => 0.25,
            'bestBonus' => 520,
            'package' => array(
                'GP0' => 0.299,
                'GP1Y' => 0.6,
                'GP10Y' => 0.1,
                'GP520Y' => 0.001
            )
        ),
        'silver' => array(
            'price' => 11,
            'priceLow' => 11,
            'priceType' => 2, // 1=銀豆,2=秀幣
            'rate' => 0.25,
            'bestBonus' => 660,
            'package' => array(
                'GP10D' => 0.4,
                'GP520D' => 0.078,
                'GP10B' => 0.5,
                'GP100B' => 0.02,
                'GP660B' => 0.002
            )
        ),
        'glod' => array(
            'price' => 168,
            'priceLow' => 168,
            'priceType' => 2, // 1=銀豆,2=秀幣
            'rate' => 0.25,
            'bestBonus' => 3600,
            'package' => array(
                'GP10D' => 0.13,
                'GP520D' => 0.1,
                'GP10B' => 0.3,
                'GP100B' => 0.4,
                'GP660B' => 0.05,
                'GP3600B' => 0.02
            )
        ),
        'premium' => array(
            'price' => 2688,
            'priceLow' => 2688,
            'priceType' => 2, // 1=銀豆,2=秀幣
            'rate' => 0.25,
            'bestBonus' => 26280,
            'package' => array(
                'GP1560D' => 0.27,
                'GP1880B' => 0.5,
                'GP3600B' => 0.2,
                'GP26280B' => 0.03
            )
        )
    );

    private $packages = array(
        'GP0' => array(
            'value' => 0,
            'amount' => 0,
            'list' => ''
        ),
        'GP1Y' => array(
            'value' => 1,
            'amount' => 1,
            'list' => '99'
        ),
        'GP10Y' => array(
            'value' => 10,
            'amount' => 10,
            'list' => '99'
        ),
        'GP520Y' => array(
            'value' => 520,
            'amount' => 520,
            'list' => '99'
        ),
        'GP10D' => array(
            'value' => 10,
            'amount' => 10,
            'list' => '77,78,79,83'
        ),
        'GP520D' => array(
            'value' => 520,
            'amount' => 520,
            'list' => '77,78,79,83'
        ),
        'GP1560D' => array(
            'value' => 1560,
            'amount' => 520,
            'list' => '98,100'
        ),
        'GP10B' => array(
            'value' => 10,
            'amount' => 1,
            'list' => '80,81,84'
        ),
        'GP100B' => array(
            'value' => 100,
            'amount' => 10,
            'list' => '80,81,84'
        ),
        'GP660B' => array(
            'value' => 660,
            'amount' => 66,
            'list' => '80,81,84'
        ),
        'GP1880B' => array(
            'value' => 1880,
            'amount' => 188,
            'list' => '80,81,84'
        ),
        'GP5200B' => array(
            'value' => 5200,
            'amount' => 520,
            'list' => '80,81,84'
        ),
        'GP3600B' => array(
            'value' => 3600,
            'amount' => 360,
            'list' => '80,81,84'
        ),
        'GP26280B' => array(
            'value' => 26280,
            'amount' => 1314,
            'list' => '82,85'
        )
    );
    
    public function __construct ()
    {
        parent::__construct();
    }

    public function getEggs ()
    {
        $activityModel = new ActivityModel();
        if ($activityModel->isActivityOpen()) {
            return $activityModel->getEggs();
        }
        return $this->eggs;
    }

    public function getPackages ()
    {
        $activityModel = new ActivityModel();
        if ($activityModel->isActivityOpen()) {
            return $activityModel->getPackages();
        }
        return $this->packages;
    }

    private function draw ($packages)
    {
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
        return array_rand($packages);
    }

    public function addRecord ($uid, $egg, $price, $price_type, $package, $gift_id, $gift_amount)
    {
        $now = time();
        $query = "INSERT INTO `smash_egg_record` (`record_time`, `uid`, `egg`, `price`, `price_type`, `package`, `gift_id`, `gift_amount`) 
                VALUES ($now, $uid, '$egg', $price, $price_type, '$package', $gift_id, $gift_amount)";
        $this->pushToMessageQueue('rcec_record', $query);
    }

    public function addRefreshRecord ($uid, $price = 1)
    {
        $now = time();
        $query = "INSERT INTO `refresh_egg_record` (`record_time`, `uid`, `price`)
        VALUES ($now, $uid, $price)";
        $this->pushToMessageQueue('rcec_record', $query);
    }

    public function refreshEgg ($uid)
    {
        $eggs = array();
        $result = array();
        foreach ($this->getEggs() as $key => $egg) {
            $eggs[$key] = $egg['rate'];
        }
        for ($i = 0; $i < 3; $i ++) {
            $result[] = $this->draw($eggs);
        }
        
        $userAttrModel = new UserAttributeModel();
        $userAttrModel->setStatusByUid($uid, 'refresh_egg_time', time());
        $userAttrModel->setStatusByUid($uid, 'eggs', implode(',', $result));
        return $result;
    }

    public function smashEgg ($uid, $eggKey)
    {
        $eggs = $this->getEggs();
        if (! isset($eggs[$eggKey])) {
            return 126;
        }
        $egg = $eggs[$eggKey];
        // 扣銀豆的砸蛋消費，必須要先綁定fb
        $userFbModel = new UserFacebookInfoModel();
        if ($egg['priceType'] == 1 && $userFbModel->isFbBound($uid)) {
            $eggPrice = $egg['priceLow'];
        } else {
            $eggPrice = $egg['price'];
        }
        // 扣銀豆或秀幣
        $fbTips = '';
        if ($egg['priceType'] == 1) {
            if ($eggPrice > $egg['priceLow']) {
                $fbTips = '如果串聯了facebook，就只需花費' . $egg['priceLow'] . '銀豆哦。';
            }
            $userInfoModel = new UserInfoModel();
            if (! $userInfoModel->updateSilver($uid, $eggPrice)) {
                return 127;
            }
        } else {
            $userAttrModel = new UserAttributeModel();
            if (! $userAttrModel->deductCoin($uid, $eggPrice)) {
                return 128;
            }
        }
        // 抽禮包
        $packageKey = $this->draw($egg['package']);
        $packages = $this->getPackages();
        $giftPackage = $packages[$packageKey];
        // 發放禮物
        if ($giftPackage['amount'] > 0 && ! empty($giftPackage['list'])) {
            $giftArray = explode(',', $giftPackage['list']);
            $giftId = $giftArray[array_rand($giftArray)];
            
            $toolAccoModel = new ToolAccountModel();
            $toolAccoModel->update($uid, $giftId, $giftPackage['amount']);
            $toolAccoRecordModel = new ToolAccountRecordModel();
            $toolAccoRecordModel->addRecord($uid, '1', $giftId, $giftPackage['amount']);
            $toolModel = new ToolModel();
            $tool = $toolModel->getToolByTid($giftId);
            $giftName = $tool['name'];
        } else {
            $giftId = 0;
            $giftName = '';
        }
        // 寫抽獎記錄
        $this->addRecord($uid, $eggKey, $eggPrice, $egg['priceType'], $packageKey, $giftId, $giftPackage['amount']);
        $this->pushToMq($uid, $eggKey, $this->isBestBonus($egg['package'], $packageKey));
        // 返回結果
        return array(
            'value' => $giftPackage['value'],
            'amount' => $giftPackage['amount'],
            'gift' => $giftId,
            'giftName' => $giftName,
            'fbTips' => $fbTips,
            'showAvator' => false
        );
    }

    public function getEggInfo ($uid)
    {
        $userFbModel = new UserFacebookInfoModel();
        $info = array();
        foreach ($this->getEggs() as $key => $egg) {
            if ($egg['priceType'] == 1 && $userFbModel->isFbBound($uid)) {
                $eggPrice = $egg['priceLow'];
            } else {
                $eggPrice = $egg['price'];
            }
            $info[] = array(
                'type' => $key,
                'price' => $eggPrice,
                'priceType' => $egg['priceType'],
                'bestBonus' => $egg['bestBonus']
            );
        }
        return $info;
    }

    public function isBestBonus ($package, $packageKey)
    {
        $keys = array_keys($package);
        $i = count($keys) - 1;
        if ($keys[$i] == $packageKey) {
            return 1;
        } else {
            return 0;
        }
    }

    public function pushToMq ($uid, $eggKey, $bestBonus)
    {
        /*
        if ($eggKey !== 'bronze') {
            $message = "$uid,$eggKey,$bestBonus";
            getRedisMq()->lPush('egg_mq', $message);
        }
        */
    }
}