<?php

class Config
{

    public static $_config = array();

    public static function getConfig ()
    {
        if (empty(self::$_config)) 
        {
            // self::$_config = include RCEC_ROOT . '/config.php';
            self::$_config = GlobalConfig::GetDB();
        }
        return self::$_config;
    }
    // 获取礼物消费为用户增加财富等级的计算比例
    public static function getGiftConsumeRichLevelScale(){
    	return 1.0;
    }
    // 获取送礼物返给主播的秀点比例
    public static function getGiftConsumeShowPointScale(){
    	return 0.5;
    }
    // 获取开通守护返给主播的秀点比例
    public static function getGaurdConsumeShowPointScale(){
    	return 0.5;
    }
    public static function getGiftConsumeExperience(){
    	return 1.0;
    }
}
