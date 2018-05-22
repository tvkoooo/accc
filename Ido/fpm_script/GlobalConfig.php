<?php
class GlobalConfig
{    
    public static $SERVER_ID = 0;// make sure all error explicitly appeared.
    
    public static function assign_server_id($svid_data)
    {
        if (!empty($svid_data))
        {
            if(is_array($svid_data))
            {
                if (isset($svid_data['svid']))
                {
                    GlobalConfig::$SERVER_ID = $svid_data['svid'];
                }
            }
            else
            {
                if (property_exists($svid_data,"svid"))
                {
                    GlobalConfig::$SERVER_ID = $svid_data->svid;
                }
            }
        }
    }
    public static $__static_config = array
    (
        // 开发-1
        1 => array
        (
            'DomainURL' => 'xiuktv.com',
            'SendGrpMsgURL' => 'http://www.xiuktv.com/xcbb_web/business/mobile/groupchat/groupSendMsg?key=',
            'SingerPlayStartPushURL' => 'http://www.xiuktv.com/xcbb_web/business/mobile/jpush/livePushFans?uid=',
            'RedPacketFansPushURL' => 'http://www.xiuktv.com/xcbb_web/mobile/api/redpack/fans/push',
            'SendSysMsgURL' => 'http://www.xiuktv.com/xcbb_web/business/mobile/groupchat/systemSendMsg?key=',
            'UrlPrefix' => 'http://www.xiuktv.com',
            'CacheUrl' => "http://10.172.186.58:8081",
            'db' => array
            (
                'mysql' => array
                (
                    'rcec_main' => array
                    (
                        '10.80.50.200',
                        'dble',
                        'test123',
                        'rcec_main',
                        '8066',
                    ),
                    'rcec_record' => array
                    (
						'10.80.50.200',
                        'dble',
                        'test123',
                        'rcec_record',
                        '8066',
                    ),
                    'flower' => array
                    (
						'10.80.50.200',
                        'dble',
                        'test123',
                        'flower',
                        '8066',
                    ),
                    'channellive' => array
                    (
						'10.80.50.200',
                        'dble',
                        'test123',
                        'channellive',
                        '8066',
                    ),
                    'raidcall' => array
                    (
						'10.80.50.200',
                        'dble',
                        'test123',
                        'raidcall',
                        '8066',
                    ),
                ),
                'redis' => array
                (
                    'master' => array
                    (
                        '60.205.151.85',
                        '18001',
                        'xcRed.,0505',
                    ),
                    'slave' => array
                    (
                        '60.205.151.85',
                        '18001',
                        'xcRed.,0505',
                    ),
                    'mq' => array
                    (
                        '60.205.151.85',
                        '18001',
                        'xcRed.,0505',
                    ),
                    'log' => array
                    (
                        '60.205.151.85',
                        '18001',
                        'xcRed.,0505',
                    ),
                    'rank_master' => array
                    (
                        '60.205.151.85',
                        '18001',
                        'xcRed.,0505',
                    ),
                    'rank_slave' => array
                    (
                        '60.205.151.85',
                        '18001',
                        'xcRed.,0505',
                    ),
                    'java_util' => array
                    (
                        '123.57.154.117',
                        '6379',
                        'xcRed.,0505',
                    ),
                    'cback' => array
                    (
                        '123.57.154.117',
                        '6379',
                        'xcRed.,0505',
                    ),

                ),
                'data_center' => array
                (
                    'ip' => '127.0.0.1',
                    'flash_version' => '3.0.17',
                ),
            ),
        ),
        // 内测1-2
        2 => array
        (
            'DomainURL' => 'xiuktv.com',
            'SendGrpMsgURL' => 'http://47.93.122.164/xcbb_web/business/mobile/groupchat/groupSendMsg?key=',
            'SingerPlayStartPushURL' => 'http://47.93.122.164/xcbb_web/business/mobile/jpush/livePushFans?uid=',
            'RedPacketFansPushURL' => 'http://47.93.122.164/xcbb_web/mobile/api/redpack/fans/push',
            'SendSysMsgURL' => 'http://47.93.122.164/xcbb_web/business/mobile/groupchat/systemSendMsg?key=',
            'UrlPrefix' => 'http://47.93.122.164',
            'CacheUrl' => "http://10.30.55.64:8081",
            'db' => array
            (
                'mysql' => array
                (
                    'rcec_main' => array
                    (
                        '172.17.48.92',
                        'XcbbNeiCe',
                        'XcNei,.0526',
                        'rcec_main',
                        '3306',
                    ),
                    'rcec_record' => array
                    (
                        '172.17.48.92',
                        'XcbbNeiCe',
                        'XcNei,.0526',
                        'rcec_record',
                        '3306',
                    ),
                    'flower' => array
                    (
                        '172.17.48.92',
                        'XcbbNeiCe',
                        'XcNei,.0526',
                        'flower',
                        '3306',
                    ),
                    'channellive' => array
                    (
                        '172.17.48.92',
                        'XcbbNeiCe',
                        'XcNei,.0526',
                        'channellive',
                        '3306',
                    ),
                    'raidcall' => array
                    (
                        '172.17.48.92',
                        'XcbbNeiCe',
                        'XcNei,.0526',
                        'raidcall',
                        '3306',
                    ),
                ),
                'redis' => array
                (
                    'master' => array
                    (
                        'redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@0526',
                    ),
                    'slave' => array
                    (
                        'redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@0526',
                    ),
                    'mq' => array
                    (
                        'redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@0526',
                    ),
                    'log' => array
                    (
                        'redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@0526',
                    ),
                    'rank_master' => array
                    (
                        'rank.redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@0526',
                    ),
                    'rank_slave' => array
                    (
                        'rank.redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@0526',
                    ),
                    'java_util' => array
                    (
                        '10.163.8.62',
                        '6379',
                        'xcTeRed#@0526',
                    ),
                    'cback' => array
                    (
                        '10.163.8.62',
                        '6379',
                        'xcTeRed#@0526',
                    ),
                ),
                'data_center' => array
                (
                    'ip' => '127.0.0.1',
                    'flash_version' => '3.0.17',
                ),
            ),
        ),
        // 内测2-3
        3 => array
        (
            'DomainURL' => 'xiuktv.com',
            'SendGrpMsgURL' => 'http://39.107.60.144/xcbb_web/business/mobile/groupchat/groupSendMsg?key=',
            'SingerPlayStartPushURL' => 'http://39.107.60.144/xcbb_web/business/mobile/jpush/livePushFans?uid=',
            'RedPacketFansPushURL' => 'http://39.107.60.144/xcbb_web/mobile/api/redpack/fans/push',
            'SendSysMsgURL' => 'http://39.107.60.144/xcbb_web/business/mobile/groupchat/systemSendMsg?key=',
            'UrlPrefix' => 'http://39.107.60.144',
            'CacheUrl' => "http://10.31.152.98:8081",
            'db' => array
            (
                'mysql' => array
                (
                    'rcec_main' => array
                    (
                        '10.31.25.58',
                        'pro_cpp',
                        'Vnc2018_Cpp',
                        'rcec_main',
                        '3306',
                    ),
                    'rcec_record' => array
                    (
                        '10.31.25.58',
                        'pro_cpp',
                        'Vnc2018_Cpp',
                        'rcec_record',
                        '3306',
                    ),
                    'flower' => array
                    (
                        '10.31.25.58',
                        'pro_cpp',
                        'Vnc2018_Cpp',
                        'flower',
                        '3306',
                    ),
                    'channellive' => array
                    (
                        '10.31.25.58',
                        'pro_cpp',
                        'Vnc2018_Cpp',
                        'channellive',
                        '3306',
                    ),
                    'raidcall' => array
                    (
                        '10.31.25.58',
                        'pro_cpp',
                        'Vnc2018_Cpp',
                        'raidcall',
                        '3306',
                    ),
                ),
                'redis' => array
                (
                    'master' => array
                    (
                        '10.31.152.98',
                        '6379',
                        'xcTeRed#@0526',
                    ),
                    'slave' => array
                    (
                        '10.31.152.98',
                        '6379',
                        'xcTeRed#@0526',
                    ),
                    'mq' => array
                    (
                        '10.31.152.98',
                        '6379',
                        'xcTeRed#@0526',
                    ),
                    'log' => array
                    (
                        '10.31.152.98',
                        '6379',
                        'xcTeRed#@0526',
                    ),
                    'rank_master' => array
                    (
                        '10.31.152.98',
                        '6379',
                        'xcTeRed#@0526',
                    ),
                    'rank_slave' => array
                    (
                        '10.31.152.98',
                        '6379',
                        'xcTeRed#@0526',
                    ),
                    'java_util' => array
                    (
                        '10.31.152.98',
                        '6379',
                        'xcTeRed#@0526',
                    ),
                    'cback' => array
                    (
                        '10.31.152.98',
                        '6379',
                        'xcTeRed#@0526',
                    ),
                ),
                'data_center' => array
                (
                    'ip' => '127.0.0.1',
                    'flash_version' => '3.0.17',
                ),
            ),
        ),
        // 正式-9
        9 => array
        (
            'DomainURL' => 'xcbobo.com',
            'SendGrpMsgURL' => 'https://www.xcbobo.com/xcbb_web/business/mobile/groupchat/groupSendMsg?key=',
            'SingerPlayStartPushURL' => 'https://www.xcbobo.com/xcbb_web/business/mobile/jpush/livePushFans?uid=',
            'RedPacketFansPushURL' => 'https://www.xcbobo.com/xcbb_web/mobile/api/redpack/fans/push',
            'SendSysMsgURL' => 'https://www.xcbobo.com/xcbb_web/business/mobile/groupchat/systemSendMsg?key=',
            'UrlPrefix' => 'https://www.xcbobo.com',
            'CacheUrl' => "http://10.81.133.67:8081",
            'db' => array
            (
                'mysql' => array
                (
                    'rcec_main' => array
                    (
                        '10.30.96.104',
                        'xcbbPro',
                        'xcbbRes#@,.0527',
                        'rcec_main',
                        '3306',
                    ),
                    'rcec_record' => array
                    (
                        '10.30.96.104',
                        'xcbbPro',
                        'xcbbRes#@,.0527',
                        'rcec_record',
                        '3306',
                    ),
                    'flower' => array
                    (
                        '10.30.96.104',
                        'xcbbPro',
                        'xcbbRes#@,.0527',
                        'flower',
                        '3306',
                    ),
                    'channellive' => array
                    (
                        '10.30.96.104',
                        'xcbbPro',
                        'xcbbRes#@,.0527',
                        'channellive',
                        '3306',
                    ),
                    'raidcall' => array
                    (
                        '10.30.96.104',
                        'xcbbPro',
                        'xcbbRes#@,.0527',
                        'raidcall',
                        '3306',
                    ),
                ),
                'redis' => array
                (
                    'master' => array
                    (
                        'redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@9527',
                    ),
                    'slave' => array
                    (
                        'redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@9527',
                    ),
                    'mq' => array
                    (
                        'redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@9527',
                    ),
                    'log' => array
                    (
                        'redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@9527',
                    ),
                    'rank_master' => array
                    (
                        'rank.redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@9527',
                    ),
                    'rank_slave' => array
                    (
                        'rank.redis.db.raidcall.com',
                        '18000',
                        'xcTeRed#@9527',
                    ),
                    'java_util' => array
                    (
                        'redis.java.raidcall.com',
                        '6379',
                        'Rele,.Redis#',
                    ),
                    'cback' => array
                    (
                        'redis.java.raidcall.com',
                        '6379',
                        'Rele,.Redis#',
                    ),
                ),
                'data_center' => array
                (
                    'ip' => '127.0.0.1',
                    'flash_version' => '3.0.17',
                ),
            ),
        ),
    );
    
	public static function GetDomainURL()
	{
	    return GlobalConfig::$__static_config[GlobalConfig::$SERVER_ID]['DomainURL'];
	}
	
	public static function GetSendGrpMsgURL()
	{
	    return GlobalConfig::$__static_config[GlobalConfig::$SERVER_ID]['SendGrpMsgURL'];
	}
	
	public static function GetSingerPlayStartPushURL() 
	{
	    return GlobalConfig::$__static_config[GlobalConfig::$SERVER_ID]['SingerPlayStartPushURL'];
	}
	
	public static function GetRedPacketFansPushURL() 
	{
	    return GlobalConfig::$__static_config[GlobalConfig::$SERVER_ID]['RedPacketFansPushURL'];
	}
	
	public static function GetSendSysMsgURL() 
	{
	    return GlobalConfig::$__static_config[GlobalConfig::$SERVER_ID]['SendSysMsgURL'];
	}
	
	public static function GetUrlPrefix() 
	{
	    return GlobalConfig::$__static_config[GlobalConfig::$SERVER_ID]['UrlPrefix'];
	}
	public static function GetDB()
	{
	    return GlobalConfig::$__static_config[GlobalConfig::$SERVER_ID]['db'];
	}
	
	public static function GetGProcs()
	{
		return 4;
	}

    public static function GetCbackQueueLinkd()
    {
        return "vnc:queue:link:cback:002";
    }

    public static function GetCbackQueueChannel()
    {
        return "vnc:queue:live:cback:002";
    }

    public static function GetCbackQueueTask()
    {
        return "vnc:task:event";
    }

    public static function GetUrlCache()
    {
        return GlobalConfig::$__static_config[GlobalConfig::$SERVER_ID]['CacheUrl'];
    }
}
?>
