<?php
class SettingsModel
{

    public function getAll ()
    {
        return array(
            'DEFAULT_SMALL_GIFT_NUMBER' => array(
                'value' => '14,48,49,36,37,43,17,20,19,33,32,45,39,47,18',
                'comment' => '默认开通的小礼物数量'
            ),
            'GIFT_SPECIAL_NUMBER' => array(
                'value' => '100,200,300,520,999,1314',
                'comment' => '当用户送礼物的数量是一个特别的数字就触发一个小特效'
            ),
            'HEART_GIF' => array(
                'value' => 'heart.gif',
                'comment' => '爱心图标'
            ),
            'HEART_ICON' => array(
                'value' => 'heart.png',
                'comment' => '爱心图标'
            ),
            'HEART_TO_CHARM_RATIO' => array(
                'value' => '1',
                'comment' => '一个爱心转换为魅力值的数值'
            ),
            'MAX_HEART_CONVERT_PER_MONTH' => array(
                'value' => '10',
                'comment' => '每月换取爱心的最大额度'
            ),
            'MAX_NUM_LUCKYDRAW' => array(
                'value' => '3',
                'comment' => '每小时时段内最多道具抽奖次数'
            ),
            'MULTI_GIFTING' => array(
                'value' => '0,1,一心一意;1,28,想你;2,66,順順利利;3,128,要抱抱;4,360,想念你;5,520,我愛你;6,777,幸運星;7,999,長長久久;8,1314,一生一世',
                'comment' => '用戶贈送多個禮物的數量組合'
            ),
            'ONMIC_IMAGE_URL' => array(
                'value' => 'http://img.' . GlobalConfig::GetDomainURL() . '/getimg.php?img=',
                'comment' => '用户上麦表演时的头像（后面接图片系统的索引id）'
            ),
            'SILVER_COST_PER_HEART' => array(
                'value' => '10',
                'comment' => '换一个爱心需要的银豆数量'
            ),
            'USER_IMAGE_URL' => array(
                'value' => 'http://api2.' . GlobalConfig::GetDomainURL() . '/user/getimg.php?type=100&uid=',
                'comment' => '用户头像获取的链接（后面需要接上UID）'
            )
        );
    }

    public function getValue ($code)
    {
        $rows = $this->getAll();
        if (isset($rows[$code])) {
            return $rows[$code]['value'];
        } else {
            return false;
        }
    }
}
?>
