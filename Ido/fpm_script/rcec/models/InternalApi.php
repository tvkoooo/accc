<?php

class InternalApi
{

    /**
     * 握手
     */
    public static function handShake ($params)
    {
        LogApi::logProcess('****************************************PHandshake************************************************************************');
        
        $result = array(
            'cmd' => 'RHandshake',
            'result' => 0
        );

        LogApi::logProcess('****************************************RHandshake************************************************************************');
        
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    /**
     * 初始化秀場數據
     */
    public static function initShowWidget ($params)
    {
        $result = array(
            'cmd' => 'RInitShowWidget',
            'imageHost' => ToolModel::GetImageHost()
        );
        $info = UserApi::getUserInfo($params, true);
        $result['UserInfo'] = $info[0]['data'];
        $info = ToolApi::getGiftList($params);
        $result['GiftList'] = $info[0]['data'];
        $info = ToolApi::getShowTools($params);
        $result['ShowTool'] = $info[0]['data'];
        $info = ToolApi::getToolsFromPacket($params, 1);
        $result['GiftPacket'] = $info[0]['data'];
        $info = ToolApi::getToolsFromPacket($params, 2);
        $result['ToolPacket'] = $info[0]['data'];
        $info = WidgetApi::getEggInfo($params);
        $result['EggInfo'] = $info[0]['data'];
        $info = WidgetApi::getSpeakerInfo($params);
        $result['SpeakerInfo'] = $info[0]['data'];
        $info = WidgetApi::getGiftBoxInfo($params);
        $result['GiftBoxInfo'] = $info[0]['data'];
        $info = WidgetApi::notice($params);
        $result['BroadcastInfo'] = $info[0]['data'];
        if (! empty($params['uid_onmic'])) {
            $info = UserApi::getSingerInfo($params);
            $result['SingerInfo'] = $info[0]['data'];
            $info = WidgetApi::getRanking($params, 'gift');
            $result['Ranking'] = $info[0]['data'];
        }
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    /**
     * 初始化環境
     */
    public static function initEnv ($params)
    {
		LogApi::logProcess('begin PInitEnv IN... ');
		$return = array();
        $rs = array(
            'cmd' => 'RInitEnv',
            'imageHost' => ToolModel::GetImageHost(),
			'audienceBadgeId' => array(152,94,95,96)
        );
        
		// LogApi::logProcess('initEnv 0 ');
        //初始化礼物规则表
        // ToolApi::initGiftRule();
        
		// LogApi::logProcess('initEnv 1 ');
        // $info = ToolApi::getGiftList($params);
		// LogApi::logProcess('initEnv 2 ');
        //GiftList：所有礼物，即：所有分类礼物的集合
        // $rs['GiftList'] = $info[0]['data'];
        $info = ToolApi::getShowTools($params);
		// LogApi::logProcess('initEnv 3 ');
        //ShowTool：礼物分类信息：如热门，初级
        $rs['ShowTool'] = $info[0]['data'];
		// LogApi::logProcess('initEnv 4 ');
        //$info = WidgetApi::notice($params);
        //$result['BroadcastInfo'] = $info[0]['data'];
        if (! empty($params['uid_onmic'])) {
			// LogApi::logProcess('initEnv 5 '); //zzzzzzzzz
            $info = UserApi::getSingerInfo($params);
			// LogApi::logProcess('initEnv 5-1 ');
            $rs['SingerInfo'] = $info[0]['data'];
			// LogApi::logProcess('initEnv 5-2 ');
			// LogApi::logProcess('initEnv 6 ');
        }
        $return[] = array
        (
        
            'broadcast' => 0,
            'data' => $rs,
        );
        //
//         $singerUid = (int)$params['uid_onmic'];
//         $sid = (int)$params['sid'];
        
//         $lightFinal = 0;
//         $moneyFinal = 0;
        
//         $charismaModel = new CharismaModel();
//         $channelLiveModel = new ChannelLiveModel();
//         $anchorInfo = $channelLiveModel->getSingerAnchorInfo($singerUid);
//         $moneyFinal = $charismaModel->GetSingerMoneyCount($singerUid);
//         if(!empty($anchorInfo))
//         {
//             $lightFinal = (int)$anchorInfo['anchor_current_experience'];
//         }
        
//         $money_const = 0;
//         $money_nt = array();
//         $money_nt['cmd'] = 'BSingerAttrMoneyUpdate';
        
//         $money_nt['uid'] = $singerUid;
//         $money_nt['sid'] = $sid;
//         $money_nt['moneyFinal'] = (int)$moneyFinal;
//         $money_nt['moneyDelta'] = (int)$money_const;
        
//         $return[] = array
//         (
//             'broadcast' => 0,
//             'data' => $money_nt,
//         );
        
//         $light_const = 0;
//         $light_nt = array();
//         $light_nt['cmd'] = 'BSingerAttrLightUpdate';
        
//         $light_nt['uid'] = $singerUid;
//         $light_nt['sid'] = $sid;
//         $light_nt['lightFinal'] = (int)$lightFinal;
//         $light_nt['lightDelta'] = (int)$light_const;
        
//         $return[] = array
//         (
//             'broadcast' => 0,
//             'data' => $light_nt,
//         );
        // LogApi::logProcess('***************************************end InitEnv************************************************************************');
        LogApi::logProcess('PInitEnv:'.json_encode($return));
        // 返回结果

        return $return;
    }

    /**
     * 初始化用戶信息
     */
    public static function initUser ($params)
    {
        $result = array(
            'cmd' => 'RInitUser'
        );
        $info = UserApi::getUserInfo($params, true);
        $result['UserInfo'] = $info[0]['data'];
        $info = ActivityApi::getActivityDailyPacket($params);
        if(!empty($info)){
            $result['UserInfo']['activityDailyPacketGiftNum'] = $info[0]['data']['result']['giftNum'];
        }
        $info = ToolApi::getToolsFromPacket($params, 1);
        $result['GiftPacket'] = $info[0]['data'];
        $info = ToolApi::getToolsFromPacket($params, 2);
        $result['ToolPacket'] = $info[0]['data'];
        $info = WidgetApi::getEggInfo($params);
        $result['EggInfo'] = $info[0]['data'];
        $info = WidgetApi::getSpeakerInfo($params);
        $result['SpeakerInfo'] = $info[0]['data'];
        $info = WidgetApi::getGiftBoxInfo($params);
        $result['GiftBoxInfo'] = $info[0]['data'];

        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    /**
     * 記錄活躍用戶
     */
    public static function logActiveUser ($params)
    {
        $result = array(
            'cmd' => 'RLogActiveUser',
            'result' => 0
        );
        $uid = $params['uid'];
        $version = ! empty($params['version']) ? $params['version'] : '';
        $userAttrModel = new UserAttributeModel();
        $userAttrModel->logActiveUser($uid, $version);
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    /**
     * 獲取服務器時間戳
     */
    public static function getTimestamp ($params)
    {
        $result = array(
            'cmd' => 'RGetTimestamp',
            'timestamp' => time(),
            'result' => 0
        );
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function getVideoEnable ($params)
    {
        $chanAttrModel = new ChannelAttributeModel();
        $result = $chanAttrModel->getVideoEnable($params['sid'], $params['cid']);
        return $result ? true : false;
    }

    public static function setVideoEnable ($params)
    {
        $chanAttrModel = new ChannelAttributeModel();
        $chanAttrModel->setVideoEnable($params['sid'], $params['cid'], $params['enable']);
        return true;
    }
}
?>