<?php

class ToolMergeModel extends ModelBase
{

    private $rules = array(
        'mooncake' => array(
            'chips' => array(
                137 => 1,
                138 => 1,
                139 => 1
            ),
            'coins' => array(
                134 => 0,
                135 => 10,
                136 => 50
            )
        )
    );
    
    public function __construct ()
    {
        parent::__construct();
    }

    public function getMergeRule ($key)
    {
        return $this->rules[$key];
    }

    public function merge ($uid, $ruleKey, $new, $qty = 1)
    {
        $toolAccoModel = new ToolAccountModel();
        $rule = $this->getMergeRule($ruleKey);
        foreach ($rule['chips'] as $id => $num) {
            $num = $num * $qty;
            if (! $toolAccoModel->hasTool($uid, $id, $num)) {
                return 156; // 碎片不足，無法合成
            }
        }
        if (! isset($rule['coins'][$new])) {
            return 157; // 道具不存在
        }
        $coin = $rule['coins'][$new] * $qty;
        if ($coin > 0) {
            $userAttrModel = new UserAttributeModel();
            if (! $userAttrModel->deductCoin($uid, $coin)) {
                return 158; // 秀幣不足，無法合成
            }
        }
        foreach ($rule['chips'] as $id => $num) {
            $num = $num * $qty;
            if (! $this->toolAccoModel->remove($uid, $id, $num)) {
                return 156; // 碎片不足，無法合成
            }
        }
        // 增加新禮物
        $this->toolAccoModel->update($uid, $new, $qty);
        $toolAccoRecordModel = new ToolAccountRecordModel();
        $toolAccoRecordModel->addRecord($uid, 11, $new, $qty);
        // 發送消息到隊列
        if ($new == 134)
            $score = $qty;
        elseif ($new == 135)
            $score = 10 * $qty;
        elseif ($new == 136)
            $score = 50 * $qty;
        $message = json_encode(
                array(
                    'type' => 1,
                    'uid' => $uid,
                    'score' => $score
                ));
        $activityModel = new ActivityModel();
        $activityModel->pushToMq($message);
        return 0;
    }
}