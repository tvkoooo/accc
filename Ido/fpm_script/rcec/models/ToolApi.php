<?php

require RCEC_ROOT . "/../util/Utils.php";

class ToolApi
{    
    public static $SEND_BARRAGE_COST = 10;// 发送弹幕消耗10秀币
    
    public static $TOOL_GANG_ITEM_TYPE = 100;// (礼物)帮会票类型
    public static $TOOL_GANG_ITEM_ID = 20;// (礼物)帮会票id

    public static $PACK_ITEM_TYPE = 100;// (包裹)帮会票类型
    public static $PACK_ITEM_ID = 20;// (包裹)帮会票id
    
    public static $PROP_TYPE_NORMAL = 101; // 普通道具类型，即服务器只做记录及转发，不做任何其他特殊效果，或分成计算
    
    public static $PROP_TYPE_EFFECT = 11; // 特效道具类型
    
    public static $EXP_ACTIVE_DOUBLE_CARD = 1;	// 活跃经验双倍卡
    
	//初始化直播间赠送的礼物规则表（loginsign_giftrule）到redis缓存中
	public static function initGiftRule(){
		$toolConsumeRecordModel = new ToolConsumeRecordModel();
		$toolConsumeRecordModel->initGiftRule();
		return;
	}
	//zkay 主播阳光值
	public static function GetAnchorSun($params){
	    LogApi::logProcess("GetAnchorSun 1");
	    $singerUid = $params['singerid'];
	    
	    $result = array(
	        'cmd' => 'RGetAnchorSun',
	        'singerid' => $singerUid
	    );
	    
	    $charismaModel = new CharismaModel();
	    $rcevMoney = $charismaModel->getAnchorSun($singerUid);
	    $result['rcevMoney'] = $rcevMoney;
	    
	    LogApi::logProcess("GetAnchorSun 2");
	    $channelLiveModel = new ChannelLiveModel();
    	$anchorInfo = $channelLiveModel->getSingerAnchorInfo($singerUid);
    	LogApi::logProcess("GetAnchorSun 3");
    	$sunshineTotal = 0;
    	$level_id = 1;
    	$moneyFinal = $charismaModel->GetSingerMoneyCount($singerUid);
    	if(!empty($anchorInfo)){
    	    LogApi::logProcess("GetAnchorSun 4");
    	    $sunshineTotal = (int)$anchorInfo['anchor_current_experience'];
    	    $level_id = (int)$anchorInfo['level_id'];
    	}
    	LogApi::logProcess("GetAnchorSun 5");
    	
        $result['sun_num'] = $sunshineTotal;
        $result['level'] = $level_id;
        
        $result['moneyFinal'] = (int)$moneyFinal;
        $result['lightFinal'] = (int)$sunshineTotal;
        
        LogApi::logProcess('end GetAnchorSun!!! return data:'.json_encode($result));
	    
	    $return[] = array
	    (
            'broadcast' => 0,
            'data' => $result
	    );	    
	    return $return;
	}
    public static function getGiftList($params)
    {
		LogApi::logProcess('getGiftList IN');
        $result = array(
            'cmd' => 'RGetGiftList',
            'imageHost' => ToolModel::GetImageHost(),
            'list' => array(),
            'multi_gifting' => array()
        );
		
		LogApi::logProcess('getGiftList 0');
        // 参数校验
        $cate1 = 1;
        // 获取二级分类列表
        $toolCateModel = new ToolCategoryModel();
		LogApi::logProcess('getGiftList 1');
        $toolCategory = $toolCateModel->getSubCategory($cate1);
		LogApi::logProcess('getGiftList 2');
        // 按一级分类获取全部开放的道具
        $toolModel = new ToolModel();
		LogApi::logProcess('getGiftList 3');
        $toolList = $toolModel->getToolListByCategory($cate1, 'order by id desc');
		LogApi::logProcess('getGiftList 4');
        // 对于每一个道具，判断是否权限道具，如果是，判断群是否开通此权限
        $tmpToolList = array();
        foreach ($toolList as $tool) {
            $tmpTool = $toolModel->getResponseInfo($tool);
            // 按二级分类来归类和保存
            $tmpToolList[$tool['category2']][] = $tmpTool;
        }
		LogApi::logProcess('getGiftList 5');
        $settings = new SettingsModel();    
		LogApi::logProcess('getGiftList 6');		
        foreach ($toolCategory as $cate) {
            $tmpCate = array();
            $tmpCate['type'] = $cate['id'];
            $tmpCate['name'] = $cate['name'];
	        $tmpCate['sort_id'] = $cate['sort_id'];
            if (isset($tmpToolList[$cate['id']])) {
                $tmpCate['list'] = $tmpToolList[$cate['id']];
            } else {
                $tmpCate['list'] = array();
            }
            
            $result['list'][] = $tmpCate;
        }
		LogApi::logProcess('getGiftList 7');
        
        //加入热销礼物的分页
        /*
        $tmpTools = new ToolConsumeRecordModel();
       
        $hotgift = $tmpTools->getGiftRankOfTop12($result['list'][0]);
      
        $result['list'][] = $hotgift;
        */
        //加入结束

        $multiGiftingList = explode(';', $settings->getValue('MULTI_GIFTING'));        
		LogApi::logProcess('getGiftList 8');
        foreach ($multiGiftingList as $multiGifting) {
            $mgRow = explode(',', $multiGifting);
            $result['multi_gifting'][] = array(
                'id' => $mgRow[0],
                'num' => $mgRow[1],
                'des' => $mgRow[2]
            );
        }
		LogApi::logProcess('getGiftList 9');
		
		LogApi::logProcess('getGiftList OUT');
        
        
        
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function getShowTools($params)
    {
        $result['cmd'] = 'RGetShowTool';
        $result['list'] = array();
        // 按一级分类获取全部开放的道具
        $toolModel = new ToolModel();
        $toolList = $toolModel->getToolListByCategory(ToolModel::TYPE_EFFECT, 'order by id desc');
        foreach ($toolList as $tool) {
            $tmpTool = $toolModel->getResponseInfo($tool);
            $result['list'][] = $tmpTool;
        }
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function getToolState($params)
    {
        $result['cmd'] = 'RGetToolState';
        $result['list'] = array();
        if (empty($params['uid_onmic'])) {
            $result['result'] = 103; // 没有麦上表演者信息
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        // 参数校验
        //$sid = (int)$params['sid'];
        $uid = (int)$params['uid_onmic']; // 麥上表演者
        $toolModel = new ToolModel();
        $toolSubsModel = new ToolSubscriptionModel();
        $userAttrModel = new UserAttributeModel();
        //$userAttr = $userAttrModel->getAttrByUid($uid);
        $toolList = $toolModel->getToolListByCategory(ToolModel::TYPE_EFFECT, 'order by id desc');
        $tmpToolList = array();
        foreach ($toolList as $tool) {
            if ($tool['consume_type'] == 1) {
                $tmpTool['id'] = $tool['id'];
                if ($toolSubsModel->hasTool($uid, $tool['id'])) {
                    $tmpTool['state'] = 1;
                } else {
                    $tmpTool['state'] = 0;
                }
                // 對於背景
                if ($tool['category2'] == ToolModel::BACKGROUND &&
                    $userAttrModel->getStatusByUid($uid, 'background') == $tool['id']
                ) {
                    $tmpTool['state'] = 2;
                }
                $tmpToolList[] = $tmpTool;
            }
        }
        $result['list'] = $tmpToolList;
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

	private static function _getToolsFromPacket($toolModel, $toolAccountModel, & $result, $cate1, $uid){
		$toolAccountList = $toolAccountModel->getTool($uid);
        if ($toolAccountList) {
            foreach ($toolAccountList as $toolAccount) {
                $tool = $toolModel->getToolByTid($toolAccount['tool_id']);
                if ($tool['category1'] == $cate1) {
                	$tmpTool = $toolModel->getResponseInfo($tool);
                	if($cate1 == 1){
                		$tmpTool['giftAccount'] = $toolAccount;
                	}else if($cate1 == 2){
                		$tmpTool['toolAccount'] = $toolAccount;
                	}
                	
                    $result['list'][] = $tmpTool;
                }
            }
        }
	}
    public static function getToolsFromPacket($params, $cate1)
    {
        if ($cate1 == 1) {
            $result['cmd'] = 'RGetGiftPacket';
        } elseif ($cate1 == 2) {
            $result['cmd'] = 'RGetToolPacket';
        }
	//test
	// $result['cmd'] = 'RGetGiftPacket';
	//test end
        $uid = $params['uid'];
        $result['list'] = array();
        $toolModel = new ToolModel();
//        if ($cate1 == ToolModel::TYPE_GIFT) {
//            // 特別處理：在禮物包裹裏面第一個位置放置用戶的愛心
//            $result['list'][0] = $toolModel->getHeartTool($uid);
//        }
        $toolAccountModel = new ToolAccountModel();
        ToolApi::_getToolsFromPacket($toolModel, $toolAccountModel, $result, $cate1, $uid);
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }
    public static function addToolsToPacket($params, $cate1){
    	if ($cate1 == 1) {
            $result['cmd'] = 'RAddGiftToPacket';
        } elseif ($cate1 == 2) {
            $result['cmd'] = 'RAddToolToPacket';
        }
        $uid = $params['uid'];
        $toolId = $params['tool_id'];
        $toolCount = $params['tool_qty'];
        $durationTime = $params['duration_time'];
        if(null == $durationTime){
        	$durationTime = 0;
        }
        $result['list'] = array();
        $toolModel = new ToolModel();
        $toolAccountModel = new ToolAccountModel();
        
        if(!$toolAccountModel->update($uid, $toolId, $toolCount, $durationTime)){
        	$result['result'] = 101;
	        return array(
	            array(
	                'broadcast' => 0,
	                'data' => $result
	            )
	        );
        }
        ToolApi::_getToolsFromPacket($toolModel, $toolAccountModel, $result, $cate1, $uid);
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }
    public static function sendGift($params)
    {
        ToolApi::logProcess('ToolApi::sendGift entry...');
    	//file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "tool sendGift in\n", FILE_APPEND);   
        $params['returnCmd'] = 'RSendGift';
        $params['broadcastCmd'] = 'BSendGift';
        $result = array(
            'cmd' => $params['returnCmd'],
            'id' => $params['id'],
            'result' => 0
        );
        $broadcastResult = array(
            'cmd' => $params['broadcastCmd'],
            'receiver' => $params['uid_onmic'],
            'receiverNick' => $params['receiver'],
            'list' => array()
        );
        $uid = (int)$params['uid'];
        $senderNick = $params['sender'];
        $sid = (int)$params['sid'];
        $cid = (int)$params['cid'];
        $videoOpen = !empty($params['videoOpen']) ? $params['videoOpen'] : 0;
        // 判断道具是否有效
        $tid = (int)$params['id']; // 道具id
        $toolModel = new ToolModel();
        $tool = $toolModel->getToolByTid($tid);
        if (empty($tool) || $tool['closed']) {
            $result['result'] = 100; // 道具不存在或已关闭
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }

        // 判斷送禮數量是否正確
        if (!empty($params['num'])) {
            if ($params['num'] <= 0) {
                $result['result'] = 122; // 數量小於0
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
            $qty = (int)$params['num'];
        } else {
            $qty = 1; // 使用（赠送）的道具数，默认是1
        }
        // 判斷是否有主播
        if (empty($params['uid_onmic'])) {
            $result['result'] = 103; // 麥上沒有主播
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        $singerUid = (int)$params['uid_onmic'];
        if ($uid == $singerUid) {
            $result['result'] = 107; // 不能给自己送礼物
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        $buy = empty($params['buy']) ? 0 : 1; // 即买即用（送），默认是买
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $vipInfo = $userAttrModel->getVipInfo($userAttr);
        $richManInfo = $userAttrModel->getRichManLevel($userAttr['uid'], $userAttr['gift_consume'], $userAttr['consume_level']);
        $singerAttr = $userAttrModel->getAttrByUid($singerUid);
        $singerGuardModel = new SingerGuardModel();
        // 爵位尊享禮物
        if (!empty($tool['min_rich']) && $userAttr['gift_consume'] < $tool['min_rich']) {
            $result['result'] = 159; // 爵位等級不足，無法贈送爵位尊享禮物
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        // 主播守護專屬禮物
        if ($tool['min_close'] > 0) {
            // 判斷是否守護
            $singerGuardCode = $singerGuardModel->closeEnough($uid, $singerUid, $tool['min_close']);
            if ($singerGuardCode > 0) {
                $result['result'] = $singerGuardCode; // 您和主播的親密度不夠，不能贈送守護專屬禮物
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
        }
        // 確定是否夠錢或包裹有
        if ($buy) {
            // 看用户的秀币是否足够买要求的数量的道具
            $coinNeed = $tool['price'] * $qty;
            if ($userAttr['coin_balance'] < $coinNeed) {
                $result['result'] = 101; // 用户秀币不足
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
        } else {
            // 看用户的名下是否有足够数量的道具
            $toolAccountModel = new ToolAccountModel();
            $toolAccountQty = $toolAccountModel->hasTool($uid, $tid, $qty);
            if (empty($toolAccountQty)) {
                $result['result'] = 102; // 包裹中道具数量不足
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
        }
        // 守護送禮魅力加成
        $charmRate = 1;
        $closeValue = $singerGuardModel->getCloseValue($uid, $singerUid);
        if ($closeValue !== false) {
            $closeLevel = $singerGuardModel->getCloseLevel($closeValue);
            $charmRate = $singerGuardModel->getCharmRate($closeLevel['closeLevel']);
        }
        // 冷門時段魅力加成
        $currentHour = date('G');
        if ($videoOpen && !empty($singerAttr['auth']) && $currentHour > 3 && $currentHour < 16) {
            if ($currentHour < 12) {
                $charmRate += 0.2;
            } else {
                $charmRate += 0.1;
            }
        }
        $isNew = $userAttr['gift_consume'] > 0 ? 0 : 1;
        $toolConsumeRecordModel = new ToolConsumeRecordModel();
        $success = $toolConsumeRecordModel->consume($uid, $sid, $cid, $tool, $qty, $singerUid, $buy, $charmRate, $isNew);
        //file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "tool gift consume result $success\n", FILE_APPEND);       
        if ($success) {
            //更新房间缓存的等级信息
            //file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "tool gift --uid=$uid,sid=$sid\n", FILE_APPEND);
            $userAttrModel->addChannelUserLevelinfoToRedis($sid,$uid);
            //添加结束

            $giftValue = $qty * $tool['price'];
            $charmValue = floor($tool['receiver_charm'] * $qty * $charmRate);
            //
            $userInfo = new UserInfoModel();
            $vipLevel = $userInfo->getVipLevel($uid);            
            
            // 優化：贈送禮物廣播包
            $broadcastResult['list'][0] = array(
                'uid' => $uid,
                'type' => $tool['category2'],
                'id' => $tool['id'],
                'num' => $qty,
                'giftValue' => $giftValue,
                'charmValue' => $charmValue,
                'isNew' => $isNew,
                'vipLevel' => $vipLevel,
                '#nick' => $senderNick,
                '#vip' => $vipInfo['vip'],
                'richLevel' => $richManInfo['richManLevel'],
                '#richStart' => $richManInfo['richManStart'],
                '#closeLevel' => empty($closeLevel['closeLevel']) ? 0 : $closeLevel['closeLevel'],
                '#vipLevel' => $vipLevel
            );
            $result['result'] = 0; // 赠送礼物成功!
            $result['buy'] = $buy;
            if (!$buy && isset($toolAccountQty)) {
                $result['num'] = intval($toolAccountQty) - $qty;
                if ($result['num'] < 0) {
                    $result['num'] = 0;
                }
            }
            $result['coinBalance'] = $userAttrModel->getAttrByUid($uid, 'coin_balance');
            $return = array();
            $return[] = array(
                'broadcast' => 0,
                'data' => $result
            );
            $return[] = array(
                'broadcast' => 1,
                'data' => $broadcastResult
            );
			$tsNow = time();
			$isAllPlatform = false;
            // 全區廣播
            if ($tool['price'] * $qty >= 1314) {
                $return[] = array(
                    'broadcast' => 3,
                    'data' => array(
                        'cmd' => 'BBroadcast',
                        'gift' => array(
                            'receiver' => $params['uid_onmic'],
                            'receiverNick' => $params['receiver'],
                            'vip' => $vipInfo['vip'],
                            'richManLevel' => $richManInfo['richManLevel'],
                            'richManTitle' => $richManInfo['richManTitle'],
                            'richManStart' => $richManInfo['richManStart'],
                            'sender' => $uid,
                            'senderNick' => $senderNick,
                            'type' => $tool['category2'],
						    'id' => $tool['id'],
						    'icon' => (string)$tool['icon'],
						    'gift_name' => $tool['name'],
						    'resource' => $tool['resource'],
						    'ts' => $tsNow,
						    'num' => $qty,
			                'sid' => $sid
						    )
                    )
                );
                $isAllPlatform = true;
            }
            // 记录全局的送礼信息
            $coinCost = $tool['price'] * $qty;
            $giftInfo = array(
                'receiver' => $params['uid_onmic'],
                'receiverNick' => $params['receiver'],
                'vip' => $vipInfo['vip'],
                'richManLevel' => $richManInfo['richManLevel'],
                'richManTitle' => $richManInfo['richManTitle'],
                'richManStart' => $richManInfo['richManStart'],
                'sender' => $uid,
                'senderNick' => $senderNick,
                'type' => $tool['category2'],
                'id' => $tool['id'],
				'icon' => $tool['icon'],
                'resource' => $tool['resource'],
				'gift_name' => $tool['name'],
                'num' => $qty,
                'ts' => $tsNow,
                'sid' => $sid
            );
            $giftPersistDisplayTool = new GiftPersistDisplayTool();
            
            // 不再写队列
            //$giftPersistDisplayTool->addGlobalGiftSendInfo($giftInfo, $coinCost);
            if($isAllPlatform){
            	$giftPersistDisplayTool->putAllPlatformGiftInfo($giftInfo);
            }

            // 活動排位變化
            $activityModel = new ActivityModel();
            $change = $activityModel->getRankChange($uid, $singerUid, $tool, $qty);
            if ($change) {
                $return[] = array(
                    'broadcast' => 1,
                    'data' => $change
                );
            }
            // 歌手等級變化
            $currentExpe = $singerAttr['experience'];
            $newExpe = $currentExpe + $tool['receiver_charm'] * $qty;
            $singerAttrNew = $userAttrModel->getAttrByUid($singerUid);
            $levelChange = $userAttrModel->getExperienceChange($singerAttr['experience_level'], $singerAttrNew['experience_level']);
            if ($levelChange) {
                $levelChange['cmd'] = 'BSingerLevelUp';
                $levelChange['singerUid'] = $singerUid;
                $return[] = array(
                    'broadcast' => 1,
                    'data' => $levelChange
                );
            }
            // 更新秀場排行榜、守護親密值
            $rankingModel = new RankingModel();
            $closeValue = $qty * $tool['receiver_charm'];
            $rankingModel->pushToMq($uid, $singerUid, $giftValue, $closeValue, $tool['id']);
            // 秀場貴賓席
            $vipChair = $rankingModel->updateVipChair($uid, $singerUid, $giftValue);
            if ($vipChair) {
                $vipChair['cmd'] = 'BVipChair';
                $vipChair['senderNick'] = $senderNick;
                $return[] = array(
                    'broadcast' => 1,
                    'data' => $vipChair
                );
            }
            // 主播粉絲榜
            $fansList = $rankingModel->updateFansRank($uid, $singerUid, $giftValue);
            if ($fansList) {
                $fansList['cmd'] = 'BFansRank';
                $fansList['senderNick'] = $senderNick;
                $return[] = array(
                    'broadcast' => 1,
                    'data' => $fansList
                );
            }
            
            // 更新房间内的贡献值排行表          
            $isRankChg = false;
            $rankList = $rankingModel->updateSidUserConsumeRank($sid, $uid, $singerUid, $giftValue, $tsNow, $isRankChg);
            if($rankList) {
              $rankList['cmd'] = 'RGetRankList';
              $rankList['senderNick'] = $senderNick;
              $return[] = array(
                  'broadcast' => 1,
                  'data' => $rankList
              );
            }
            
            if ($isRankChg !== false) {
                $return[] = array(
                    'broadcast' => 1,
                    'data' => array(
                        'cmd' => 'BBroadcast',
                        'rank' => array(
                            'uid' => $uid,
                            'nick' => $senderNick
                        )
                    )
                );
            }
			
            // 返回
            return $return;
        } else {
            if ($buy == ToolModel::SPEND_RCCOIN) {
                $result['result'] = 101; // 用户RC币不足
            } else {
                $result['result'] = 102; // 包裹中道具数量不足
            }
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
    }
    
    //发送金币礼物
    public static function sendGoldGift($params, $userAttr, $tool, $result, $qty){
        LogApi::logProcess('************sendGoldGift*************');
        $userAttrModel = new UserAttributeModel();
        
        $uid = (int)$params['uid'];

        // 看用户的金币是否足够送要求的数量的道具
        $coinNeed = $tool['price'] * $qty;
        if ($userAttr['jinbi_point'] < $coinNeed) {
            $result['result'] = 201; // 用户金币不足
        	LogApi::logProcess('************sendGoldGift::用户金币不足*************uid:'.$uid);
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        //减去发送用户的金币值
        /*
        try{
        	$toolConsumRecordModel = new ToolConsumeRecordModel();
	        $toolConsumRecordModel->getDbMain()->query("BEGIN", false);
	        $success = $toolConsumRecordModel->consumeGoldcoin($uid, $tool, $qty);
		    $userAttrModel->getDbMain()->query("COMMIT", false);
        }catch(Exception $e){
        	LogApi::logProcess('************sendGoldGift::ROLLBACK*************uid:'.$uid);
       		$userAttrModel->getDbMain()->query("ROLLBACK", false);
       		throw $e;
        }
        */
        $toolConsumRecordModel = new ToolConsumeRecordModel();
        $success = $toolConsumRecordModel->consumeGoldcoin($uid, $tool, $qty);
        //todo:增加主播的金币礼物经验值：：以后做
        
        $return = array();
        if($success){
        	$result['result'] = 0; // 使用道具或赠送礼物成功!
            
	        // 優化：贈送禮物廣播包
	        $broadcastResult['list'][0] = array(
	            'uid' => $uid,
	            'type' => $tool['category2'],
	            'id' => $tool['id'],
	            'num' => $qty,
	            'giftValue' => $coinNeed,
	            'nick' => $params['sender'],
	    		'giftName' => $tool['name']
	        );
	        
	        $return[] = array(
	            'broadcast' => 1, //全直播间
	            'data' => $broadcastResult
	        );
        }else{
        	$result['result'] = 201; // 用户金币不足
        }
        
        $return[] = array(
            'broadcast' => 0,
            'data' => $result
        );
        
        LogApi::logProcess('************sendGoldGift::end*************return:'.json_encode($return));
        
        return $return;
    }
    
    //发送阳光礼物
    public static function sendSunGift($params, $userAttr, $tool, &$result, $broadcastResult){
        LogApi::logProcess('************sendSunGift*************');
        $userAttrModel = new UserAttributeModel();
        
        $sid = (int)$params['sid'];
        $uid = (int)$params['uid'];
        $singerid = (int)$params['uid_onmic'];
        //0:单送 1:连送
        $flag = intval($params['flag']);
        //连送数量
        $serialNum = $params['serialNum'];
        
        $sunvalue = (int)$tool['price'];
        
        $sendTime = $params['sendTime'];
        
        // 新手阳光礼物
        $toolModel = new ToolModel();
        if ($tool['id'] == 450) {
        	$sys_parameters = new SysParametersModel();
        	
        	// 获取配置信息
        	$newer_gift_per_day = $sys_parameters->GetSysParameters(208, 'parm1');
        	$newer_gift_per_day = isset($newer_gift_per_day)?$newer_gift_per_day:3;
        	
        	// 获取已使用信息
        	$newer_gift_used = $toolModel->getNewerGiftCountUsed($uid);
        	$newer_gift_used = isset($newer_gift_used)?$newer_gift_used:0;
        	
        	if ($newer_gift_used >= $newer_gift_per_day) {
        		$result['result'] = 301;
        		return array(
        				array(
        						'broadcast' => 0,
        						'data' => $result	
        				)	
        		);
        	}
        } else {        // 看用户的金币是否足够送要求的数量的道具
        	if ($userAttr['sun_num'] < $sunvalue) {
        		$result['result'] = 301; // 用户阳光值不足
        		return array(
        				array(
        						'broadcast' => 0,
        						'data' => $result
        				)
        		);
        	}
        }
        
        $qty = 1;
        if (!empty($params['num'])) {
            $params['num'] = intval($params['num']);
            if ($params['num'] <= 0) {
                LogApi::logProcess('ToolApi::useTool*************** 礼物量小于0');
                $result['result'] = 122; // 數量小於0
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
            $qty = (int)$params['num']; //礼物数量
        }
        
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        //活跃等级
        $userAttrModel = new UserAttributeModel();
        $oldUserAttr = $userAttrModel->getAttrByUid($uid);
        $oldActiveValue = (int)$oldUserAttr['active_point'];
        $oldActiveManInfo = $userAttrModel->getActiveLevel($oldActiveValue, $uid, 0);
        $oldActiveLevel = (int)$oldActiveManInfo['activeManLevel'];
        
        $channelLiveModel = new ChannelLiveModel();
        $oldAnchorInfo = $channelLiveModel->getSingerAnchorInfo($singerid);
        $oldLevel_id = 1;
        if(!empty($oldAnchorInfo)){
            $oldLevel_id = (int)$oldAnchorInfo['level_id'];
        }
        
        $toolConsumRecordModel = new ToolConsumeRecordModel();
        if ($tool['id'] == 450) {
        	$success = true;
        } else {
        	$success = $toolConsumRecordModel->consume_sun($uid, $sid, $tool, $qty, $singerid);
        }
        if ($success) {
            $success = $toolConsumRecordModel->consumeSunValue($sid, $uid, $singerid, $sunvalue);            
        } else {
            $result['result'] = 302; // sql error
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        
        $return = array();
        
        if($success){
        	
        	// 主播阳光收益相关
        	{
        		$model_sunincome_task = new sun_income_task_model();
        		$model_sunincome_task->on_sun_received($singerid);
        	}

            // v 星排行
            {
                $model_v_rank = new v_rank_model();
                $model_v_rank->on_recv_gift($tool['id'], $tool['category2'], $qty*$tool['price'], $uid, $singerid);
            }
        	
            $serialNum = $toolModel->IncrAndGetSerialNumber($uid, $sendTime);
        	$result['result'] = 0; // 使用道具或赠送礼物成功!
        	
        	LogApi::logProcess('ToolApi::useTool 开始送礼逻辑, toolid:'.$tool['id'].' 主播id:'.$singerid);
        	$return[] = array(
        	    'broadcast' => 5,
        	    'data' => array(
        	        'uid' => (int)$uid,
        	        'target_type' => 42,//送礼
        	        'num' => (int)$qty,
        	        'extra_param' =>(int)$tool['id']
        	    )
        	);
        	$return[] = array(
        	    'broadcast' => 5,
        	    'data' => array(
        	        'uid' => (int)$singerid,
        	        'target_type' => 43,//收礼
        	        'num' => (int)$qty,
        	        'extra_param' =>(int)$tool['id']
        	    )
        	);
        	
        	/*********送所有礼物***********/
        	$return[] = array(
        	    'broadcast' => 5,
        	    'data' => array(
        	        'uid' => (int)$uid,
        	        'target_type' => 44,//送礼
        	        'num' => (int)$qty,
        	        'extra_param' =>0
        	    )
        	);
        	$return[] = array(
        	    'broadcast' => 5,
        	    'data' => array(
        	        'uid' => (int)$singerid,
        	        'target_type' => 45,//收礼
        	        'num' => (int)$qty,
        	        'extra_param' =>0
        	    )
        	);
        	/*********end送所有礼物***********/
        	
        	$return[] = array(
        	    'broadcast' => 5,
        	    'data' => array(
        	        'uid' => (int)$uid,
        	        'target_type' => 41,//送阳光礼物
        	        'num' => $sunvalue*$qty,
        	        'extra_param' =>(int)$tool['id']
        	    )
        	);
        	
        	$newUserAttr = $userAttrModel->getAttrByUid($uid);
        	$newActiveValue = (int)$newUserAttr['active_point'];
        	$newActiveManInfo = $userAttrModel->getActiveLevel($newActiveValue, $uid, 0);
        	$newActiveLevel = (int)$newActiveManInfo['activeManLevel'];
            
        	if($newActiveLevel > $oldActiveLevel){
        		$key = "user_active_levelup:$uid";
        		$field = "old:$oldActiveLevel" . "new:$newActiveLevel";
        		if ($toolModel->AtomEnsure ($key, $field, 1, 180 ) != 1) {
        			LogApi::logProcess ( "ToolApi::useTool::user_active_levelup Fetch Lock Fail. key:$key, field:$field");
        		} else {
        			
        			// combat
        			$rich_info = $userAttrModel->getRichManLevel($uid, $newUserAttr['gift_consume'], $newUserAttr['consume_level']);
        			
        			$model_combat = new CombatModel();
        			$card_info = $model_combat->getMaxCombatCardInfo($uid);
        			//$old_combat_info = $model_combat->getCombatAttrFromCache($uid, $card_info['current_format_type']);
        			//if (empty($old_combat_info)) {
        			$old_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $oldActiveManInfo, $rich_info);
        			//}
        				
        			$new_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $newActiveManInfo, $rich_info);
        			$model_combat->flushCombatAttr2Cache($uid, $card_info['current_format_type'], $new_combat_info);
        			
        			//TODO
        			$levelChange = array();
        			$levelChange['cmd'] = 'BUserActiveLevelUp';
        			$levelChange['uid'] = $uid;
        			$levelChange['nick'] = $user['nick'];
        			$levelChange['oldLevel'] = $oldActiveLevel;
        			$levelChange['newLevel'] = $newActiveLevel;
        			$levelChange['display_id'] = $newActiveManInfo['display_id'];
        			$levelChange['combatOld'] = $old_combat_info;
        			$levelChange['combatNew'] = $new_combat_info;
        			$levelChange['photo'] = $user['photo'];
        			
        			//end add
        			$return[] = array(
        					'broadcast' => 1,
        					'data' => $levelChange
        			);
        		}
        		
        		$sys_parameters = new SysParametersModel();
        		$newer_active_level = $sys_parameters->GetSysParameters(205, 'parm1');
        		
        		if ($newActiveLevel >= $newer_active_level) {
        			$return[] = array(
        					'broadcast' => 5,
        					'data' => array(
        							'uid' => (int)$uid,
        							'target_type' => 49,//迎新活跃等级
        							'num' => (int)$newActiveLevel,
        							'extra_param' => 0
        					)
        			);
        		}
        	}
        	
        	//获取主播最新阳光总值
        	$anchorInfo = $channelLiveModel->getSingerAnchorInfo($singerid);
        	$sunshineTotal = 0;
        	$level_id = 1;
        	if(!empty($anchorInfo)){
        	    $sunshineTotal = (int)$anchorInfo['anchor_current_experience'];
        	    $level_id = (int)$anchorInfo['level_id'];
        	}
        	
        	$singer = $userInfo->getInfoById($singerid);
        	if($level_id > $oldLevel_id){
        		$key = "singer_reward_levelup:$singerid";
        		$field = "old:$oldLevel_id" . "new:$level_id";
        		if ($toolModel->AtomEnsure ($key, $field, 1, 180 ) != 1) {
        			LogApi::logProcess ( "ToolApi::useTool::singer_reward_levelup Fetch Lock Fail. key:$key, field:$field");
        		} else {
        			$singerUp = array();
        			$singerUp['cmd'] = 'BSingerRewardUpdate';
        			
        			$singerUp['uid'] = $singerid;
        			$singerUp['sid'] = $sid;
        			$singerUp['nick'] = $singer['nick'];
        			$singerUp['old_level'] = $oldLevel_id;
        			$singerUp['new_level'] = $level_id;
        			
        			$return[] = array
        			(
        					'broadcast' => 1, //全直播间
        					'data' => $singerUp,
        			);
        			 
                    // 发送消息至粉丝群
                    $summary = "恭喜" . $singerUp['nick'] . "阳光等级升为" . $level_id . "级，主播感受到了满满的小太阳之爱~";
                    $text = "<font color='#8ca0c8'>恭喜</font> <font color='#b4b4ff'>" . $singerUp['nick'] . "</font> <font color='#beaa78'>阳光等级</font> <font color='#8ca0c8'>升为 " . $level_id . "级，主播感受到了满满的小太阳之爱~</font>";
                    $msg = array(
                        'group_id' => $singerid,
                        'content' => array(
                                'type' => 0,
                                'text' => $summary,
                                'msgs' => array(
                                    0 => array(
                                        'content' => $text,
                                    )
                                ),
                                'summary' => $summary
                        )
                    );
        			 
        			$tmpKey = "zbrewardup:$singerid" . ":" . time();
        			$userAttrModel->getRedisMaster()->set($tmpKey, json_encode($msg));
        			 
        			$url = GlobalConfig::GetSendGrpMsgURL() . $tmpKey;
        			$ch = curl_init();
        			$curl_opt = array(
        					CURLOPT_URL => $url,
        					CURLOPT_RETURNTRANSFER => true,
        					CURLOPT_TIMEOUT_MS => 1000
        			);
        			curl_setopt_array($ch, $curl_opt);
        			$data = curl_exec($ch);
        			curl_close($ch);
        			LogApi::logProcess("ToolApi::sendSunGift***************rediskey:$tmpKey send msg rsp:$data");
        		}    	    
        	}
        	
	        // 優化：贈送禮物廣播包
	        $broadcastResult['list'][0] = array(
	            'uid' => $uid,
	            'type' => $tool['category2'],
	            'id' => $tool['id'],
	            'num' => 1,
	            'giftValue' => $sunvalue,
	            'nick' => $params['sender'],
	    		'giftName' => $tool['name'],
                'sunshine' => $sunvalue,
                'sunshineTotal' => $sunshineTotal,
	            'level' => $level_id,
	            'flag' => $flag,
	            'imgUrl' => (string)$tool['icon'],
	            'sendTime' => $sendTime,
	            'photo' => $user['photo'],
	            'serialNum' => $serialNum,
	        	'effect_id' => isset($tool['effect_id']) ? intval($tool['effect_id']):0,
	        	'show_time' => isset($tool['show_time']) ? intval($tool['show_time']):0
	        );
	        
	        $return[] = array(
	            'broadcast' => 1, //全直播间
	            'data' => $broadcastResult
	        );
	        
            //阳光礼物上跑马灯
            {
                //获取发包数据
                $vipInfo = $userAttrModel->getVipInfo($userAttr);
                $richManInfo = $userAttrModel->getRichManLevel($userAttr['uid'], $userAttr['gift_consume'], $userAttr['consume_level']);
                $senderNick = $params['sender'];
                $tsNow = time();
                $num= 1;

                // 全區廣播（超过50块钱即：5000秀币）
                $sys_parameters = new SysParametersModel();
                $pmdPrice = $sys_parameters->GetSysParameters(251, 'parm1');
                if (empty($pmdPrice)) {
                    $pmdPrice = 8000;
                }
                 
                $pmd = false;                
                $totalPrice = $sunvalue; 
                //单次阳光价值大于触发条件，每次都出现
                if($totalPrice >= $pmdPrice){
                  $pmd = true;
                }
                //当单次阳光不满足触发条件，但是连送情况下累计可以触发
                if($totalPrice < $pmdPrice)
                {
                    $tiantou=( 0 == $pmdPrice % $totalPrice) ? 0 : 1;//判断次数是否可以整除，如果可以，添头为0，如果不可以，添头为1
                    if( 0 ==$serialNum %( $pmdPrice/$totalPrice + $tiantou ) ){
                        $pmd = true;
                    }
                }

                if ($pmd) {     
                    $honors = array();
                    $gifts = $toolModel->getAllTools();
                    foreach ($gifts as $g){
                        $top3 = $toolModel->getTop3($g['id'], $uid);
                        $honor = 0;
                        $index = 0;
                        foreach ($top3 as $top){
                            $index++;
                            if($top == $uid){
                                $honor = $index;
                                break;
                            }
                        }
                        if($honor){
                            $item = array();
                            $item['tid'] = $g['id'];
                            $item['img'] = $g['icon'];
                            $item['index'] = $honor;
                            $honors['items'][] = $item;
                        }
                    }
                    
                    $pmd_lvl2_price = $sys_parameters->GetSysParameters(227, 'parm1');
                    if (empty($pmd_lvl2_price)) {
                        $pmd_lvl2_price = 99900;
                    }
                
                    $toollevel = 0;            
                    //if($totalPrice >= $pmd_lvl2_price){
                    //  $toollevel = 1;
                    //}
                    //连送情况下，连送累计金额大于2级跑马灯下限金额
                    if( ($totalPrice * $serialNum ) >= $pmd_lvl2_price){
                      $toollevel = 0;
                    }           
                
                    $buffer_object = array(
                            'receiver' => $params['uid_onmic'],
                            'receiverNick' => $params['receiver'],
                            'vip' => $vipInfo['vip'],
                            'richManLevel' => $richManInfo['richManLevel'],
                            'richManTitle' => $richManInfo['richManTitle'],
                            'richManStart' => $richManInfo['richManStart'],
                            'sender' => $uid,
                            'senderNick' => $senderNick,
                            'type' => $tool['category2'],
                            'id' => $tool['id'],
                            'icon' => $tool['icon'],
                            'resource' => $tool['resource'],
                            'gift_name' => $tool['name'],
                            'ts' => $tsNow,
                            'num' => $num,
                            'honors' => $honors,
                            'toolLevel' => $toollevel,
                            'sid' => $sid,
                            'serialNum'=>$serialNum
                    );
                
                    $return[] = array(
                            'broadcast' => 4,
                            'data' => array(
                                    'cmd' => 'BBroadcast',
                                    'type' => 1,//BBroadcastGift type
                                    'gift'=>$buffer_object,
                            )
                    );
                    $isAllPlatform = true;
		       }
            }


	        // 补发主播阳光统计更新包
	        $light_nt = array();
	        $light_nt['cmd'] = 'BSingerAttrLightUpdate';
	        
	        $light_nt['uid'] = $singerid;
	        $light_nt['sid'] = $sid;
	        $light_nt['lightFinal'] = $sunshineTotal;
	        $light_nt['lightDelta'] = $sunvalue;
	        
	        $return[] = array
	        (
	            'broadcast' => 1, //全直播间
	            'data' => $light_nt,
	        );
	        
	        do {
	        	// 计算主播分成比例变化
	        	if (empty($anchorInfo) || empty($oldAnchorInfo)) {
        			LogApi::logProcess('ToolApi::sendSunGift 计算分成比例失败***主播id:'.$singerid . '***getSingerAnchorInfo failure');
        			break;
	        	}
	        	 
	        	if ($oldLevel_id == 5 || $level_id == 5) {
	        		LogApi::logProcess('ToolApi::sendSunGift 计算分成比例失败***主播id:'.$singerid . '***oldlevel:' . $oldLevel_id . ' level:' . $level_id);
	        		break;
	        	}
	        	 
	        	$alinfo = array();
	        	$rows = $toolConsumRecordModel->getDbRaidcall()->query("select * from raidcall.anchor_level_info");
	        	if ($rows && $rows->num_rows > 0) {
	        		$row = $rows->fetch_assoc();
	        		while ($row) {
	        			$item = array();
	        			$item['upgrade_experience'] = $row['upgrade_experience'];
	        			$item['bonus_scale'] = $row['bonus_scale'];
	        			$alinfo[$row['anchor_level']] = $item;
	        			$row = $rows->fetch_assoc();
	        		}
	        	}
	        	 
	        	if (empty($alinfo)) {
        			LogApi::logProcess('ToolApi::sendSunGift 计算分成比例失败***主播id:'.$singerid . '***select anchor_level_info failure');
	        		break;
	        	}
	        	 
	        	if (empty($alinfo[$oldLevel_id]) || empty($alinfo[$oldLevel_id+1])
	        			|| empty($alinfo[$level_id]) || empty($alinfo[$level_id+1])) {
	        		LogApi::logProcess('ToolApi::sendSunGift 计算分成比例失败***主播id:'.$singerid . '***anchor_level_info invild');		 
	        		break;		 
	        	}
	        	
                LogApi::logProcess("ToolApi::sendSunGift 计算分成比例 oldAnchorInfo:[" . json_encode($oldAnchorInfo) . "] newAnchorInfo:[" . json_encode($anchorInfo) . "] alinfo:[" . json_encode($alinfo) . "]");
				$oldRatio = ($oldAnchorInfo ['anchor_current_experience'] - $alinfo [$oldLevel_id] ['upgrade_experience']) / ($alinfo [$oldLevel_id + 1] ['upgrade_experience'] - $alinfo [$oldLevel_id] ['upgrade_experience']) * ($alinfo [$oldLevel_id + 1]['bonus_scale'] / 100 - $alinfo [$oldLevel_id]['bonus_scale'] / 100) * 0.62 + $alinfo [$oldLevel_id]['bonus_scale'] / 100;
				
				$oldRatio *= 0.2;
				$oldRatio = floor ( $oldRatio * 100 * 100 );
				
				$ratio = ($anchorInfo ['anchor_current_experience'] - $alinfo [$level_id] ['upgrade_experience']) / ($alinfo [$level_id + 1] ['upgrade_experience'] - $alinfo [$level_id] ['upgrade_experience']) * ($alinfo [$level_id + 1]['bonus_scale'] / 100 - $alinfo [$level_id]['bonus_scale'] / 100) * 0.62 + $alinfo [$level_id]['bonus_scale'] / 100;
				
				$ratio *= 0.2;
				$ratio = floor ( $ratio * 100 * 100 );
				
				if ($ratio < 0 || $oldRatio < 0 || $oldRatio > $ratio) {
					LogApi::logProcess('ToolApi::sendSunGift 计算分成比例失败***主播id:'.$singerid . '***old divid scale:' . $oldRatio . ' divid scale:' . $ratio);						
        			break;
				}
				
				$dividScale = $ratio - $oldRatio;
				$dividScale = $dividScale / 100;
				
				if ($dividScale >= 0.01) {
					
					$dividScaleNtfy = array (
							'cmd' => 'BSingerIncDividScale',
							'singerid' => $singerid,
							'scale' => $dividScale
					);
					
					$return[] = array (
							'broadcast' => 6,
							'target_uid' => $singerid,
							'data' => $dividScaleNtfy// 发给主播
					);
				}
	        } while(0);
	        
        }else{
        	$result['result'] = 303; // 用户金币不足
        }
        
        $result['totalSun'] = (int)$newUserAttr['sun_num'];
        $result['money'] = (int)$newUserAttr['coin_balance'];
        
        $return[] = array(
            'broadcast' => 0,
            'data' => $result
        );
        
        if ($tool['id'] == 450 && $result['result'] == 0) {
        	$toolModel->addNewerGiftCountUsed($uid);
        }
        
        LogApi::logProcess('************sendSunGift::end*************return:'.json_encode($return));
        
        return $return;
    }

    public static function sendSunGiftV3($params, $userAttr, $tool, &$result, &$broadcastResult){
        LogApi::logProcess('************sendSunGiftV3*************');
        $userAttrModel = new UserAttributeModel();
        
        $sid = (int)$params['sid'];
        $uid = (int)$params['uid'];
        $singerid = (int)$params['uid_onmic'];
        //0:单送 1:连送
        $flag = intval($params['flag']);
        //连送数量
        $serialNum = $params['serialNum'];
        
        $sunvalue = (int)$tool['price'];
        
        $sendTime = $params['sendTime'];
        
        // 新手阳光礼物
        $toolModel = new ToolModel();
        if ($tool['id'] == 450) {

            // 葵花籽赠送逻辑
            do {
                $src = isset($params['src'])?$params['src']:'';
                if ($src == 'prop') {
                    $prop_id = $params['id'];
                    $num = (int)$params['num'];
                    $model_tool_consume = new ToolConsumeRecordModel();
                    $b_success = $model_tool_consume->consume_prop($prop_id, $uid, $singerid, $num);

                    if (!$b_success) {
                        $result['result'] = 122;
                        break;
                    }
                } else {
                    $sys_parameters = new SysParametersModel();
                
                    // 获取配置信息
                    $newer_gift_per_day = $sys_parameters->GetSysParameters(208, 'parm1');
                    $newer_gift_per_day = isset($newer_gift_per_day)?$newer_gift_per_day:3;
                    
                    // 获取已使用信息
                    $newer_gift_used = $toolModel->getNewerGiftCountUsed($uid);
                    $newer_gift_used = isset($newer_gift_used)?$newer_gift_used:0;
                    
                    if ($newer_gift_used >= $newer_gift_per_day) {
                        $result['result'] = 301;
                        break;
                    }
                }
                $result['result'] = 0;
            } while (0);
            if ($result['result'] != 0) {
                return array(
                        array(
                                'broadcast' => 0,
                                'data' => $result   
                        )   
                );
            }
        } else {        // 看用户的金币是否足够送要求的数量的道具
            if ($userAttr['sun_num'] < $sunvalue) {
                $result['result'] = 301; // 用户阳光值不足
                return array(
                        array(
                                'broadcast' => 0,
                                'data' => $result
                        )
                );
            }
        }
        
        $qty = 1;
        if (!empty($params['num'])) {
            $params['num'] = intval($params['num']);
            if ($params['num'] <= 0) {
                LogApi::logProcess('ToolApi::useTool*************** 礼物量小于0');
                $result['result'] = 122; // 數量小於0
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
            $qty = (int)$params['num']; //礼物数量
        }
        
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        //活跃等级
        $userAttrModel = new UserAttributeModel();
        $oldUserAttr = $userAttrModel->getAttrByUid($uid);
        $oldActiveValue = (int)$oldUserAttr['active_point'];
        $oldActiveManInfo = $userAttrModel->getActiveLevel($oldActiveValue, $uid, 0);
        $oldActiveLevel = (int)$oldActiveManInfo['activeManLevel'];
        
        $channelLiveModel = new ChannelLiveModel();
        $oldAnchorInfo = $channelLiveModel->getSingerAnchorInfo($singerid);
        $oldLevel_id = 1;
        if(!empty($oldAnchorInfo)){
            $oldLevel_id = (int)$oldAnchorInfo['level_id'];
        }
        
        $toolConsumRecordModel = new ToolConsumeRecordModel();
        if ($tool['id'] == 450) {
            $success = true;
        } else {
            $success = $toolConsumRecordModel->consume_sun($uid, $sid, $tool, $qty, $singerid);
        }
        if ($success) {
            $success = $toolConsumRecordModel->consumeSunValue($sid, $uid, $singerid, $sunvalue);            
        } else {
            $result['result'] = 302; // sql error
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        
        $return = array();
        
        if($success){
            
            // 主播阳光收益相关
            {
                $model_sunincome_task = new sun_income_task_model();
                $model_sunincome_task->on_sun_received($singerid);
            }

            // 热度积分
            {
                $model_anchor_pt = new anchor_points_model();
                $model_anchor_pt->on_anchor_recv_sunshine($singerid, $sunvalue);
            }

            // v 星排行
            {
                $model_v_rank = new v_rank_model();
                $model_v_rank->on_recv_gift($tool['id'], $tool['category2'], $qty*$tool['price'], $uid, $singerid);
            }
            
            $serialNum = $toolModel->IncrAndGetSerialNumber($uid, $sendTime);
            $result['result'] = 0; // 使用道具或赠送礼物成功!
            
            LogApi::logProcess('ToolApi::useTool 开始送礼逻辑, toolid:'.$tool['id'].' 主播id:'.$singerid);
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 42,//送礼
                    'num' => (int)$qty,
                    'extra_param' =>(int)$tool['id']
                )
            );
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$singerid,
                    'target_type' => 43,//收礼
                    'num' => (int)$qty,
                    'extra_param' =>(int)$tool['id']
                )
            );
            
            /*********送所有礼物***********/
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 44,//送礼
                    'num' => (int)$qty,
                    'extra_param' =>0
                )
            );
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$singerid,
                    'target_type' => 45,//收礼
                    'num' => (int)$qty,
                    'extra_param' =>0
                )
            );
            /*********end送所有礼物***********/
            
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 41,//送阳光礼物
                    'num' => $sunvalue*$qty,
                    'extra_param' =>(int)$tool['id']
                )
            );
            
            $newUserAttr = $userAttrModel->getAttrByUid($uid);
            $newActiveValue = (int)$newUserAttr['active_point'];
            $newActiveManInfo = $userAttrModel->getActiveLevel($newActiveValue, $uid, 0);
            $newActiveLevel = (int)$newActiveManInfo['activeManLevel'];
            
            if($newActiveLevel > $oldActiveLevel){
                $key = "user_active_levelup:$uid";
                $field = "old:$oldActiveLevel" . "new:$newActiveLevel";
                if ($toolModel->AtomEnsure ($key, $field, 1, 180 ) != 1) {
                    LogApi::logProcess ( "ToolApi::useTool::user_active_levelup Fetch Lock Fail. key:$key, field:$field");
                } else {
                    
                    // combat
                    $rich_info = $userAttrModel->getRichManLevel($uid, $newUserAttr['gift_consume'], $newUserAttr['consume_level']);
                    
                    $model_combat = new CombatModel();
                    $card_info = $model_combat->getMaxCombatCardInfo($uid);
                    //$old_combat_info = $model_combat->getCombatAttrFromCache($uid, $card_info['current_format_type']);
                    //if (empty($old_combat_info)) {
                    $old_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $oldActiveManInfo, $rich_info);
                    //}
                        
                    $new_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $newActiveManInfo, $rich_info);
                    $model_combat->flushCombatAttr2Cache($uid, $card_info['current_format_type'], $new_combat_info);
                    
                    //TODO
                    $levelChange = array();
                    $levelChange['cmd'] = 'BUserActiveLevelUp';
                    $levelChange['uid'] = $uid;
                    $levelChange['nick'] = $user['nick'];
                    $levelChange['oldLevel'] = $oldActiveLevel;
                    $levelChange['newLevel'] = $newActiveLevel;
                    $levelChange['display_id'] = $newActiveManInfo['display_id'];
                    $levelChange['combatOld'] = $old_combat_info;
                    $levelChange['combatNew'] = $new_combat_info;
                    $levelChange['photo'] = $user['photo'];
                    
                    //end add
                    $return[] = array(
                            'broadcast' => 1,
                            'data' => $levelChange
                    );
                }
                
                $sys_parameters = new SysParametersModel();
                $newer_active_level = $sys_parameters->GetSysParameters(205, 'parm1');
                
                if ($newActiveLevel >= $newer_active_level) {
                    $return[] = array(
                            'broadcast' => 5,
                            'data' => array(
                                    'uid' => (int)$uid,
                                    'target_type' => 49,//迎新活跃等级
                                    'num' => (int)$newActiveLevel,
                                    'extra_param' => 0
                            )
                    );
                }
            }
            
            //获取主播最新阳光总值
            $anchorInfo = $channelLiveModel->getSingerAnchorInfo($singerid);
            $sunshineTotal = 0;
            $level_id = 1;
            if(!empty($anchorInfo)){
                $sunshineTotal = (int)$anchorInfo['anchor_current_experience'];
                $level_id = (int)$anchorInfo['level_id'];
            }
            
            $singer = $userInfo->getInfoById($singerid);
            if($level_id > $oldLevel_id){
                $key = "singer_reward_levelup:$singerid";
                $field = "old:$oldLevel_id" . "new:$level_id";
                if ($toolModel->AtomEnsure ($key, $field, 1, 180 ) != 1) {
                    LogApi::logProcess ( "ToolApi::useTool::singer_reward_levelup Fetch Lock Fail. key:$key, field:$field");
                } else {
                    $singerUp = array();
                    $singerUp['cmd'] = 'BSingerRewardUpdate';
                    
                    $singerUp['uid'] = $singerid;
                    $singerUp['sid'] = $sid;
                    $singerUp['nick'] = $singer['nick'];
                    $singerUp['old_level'] = $oldLevel_id;
                    $singerUp['new_level'] = $level_id;
                    
                    $return[] = array
                    (
                            'broadcast' => 1, //全直播间
                            'data' => $singerUp,
                    );
                     
                    // 发送消息至粉丝群
                    $summary = "恭喜" . $singerUp['nick'] . "阳光等级升为" . $level_id . "级，主播感受到了满满的小太阳之爱~";
                    $text = "<font color='#8ca0c8'>恭喜</font> <font color='#b4b4ff'>" . $singerUp['nick'] . "</font> <font color='#beaa78'>阳光等级</font> <font color='#8ca0c8'>升为 " . $level_id . "级，主播感受到了满满的小太阳之爱~</font>";
                    $msg = array(
                        'group_id' => $singerid,
                        'content' => array(
                                'type' => 0,
                                'text' => $summary,
                                'msgs' => array(
                                    0 => array(
                                        'content' => $text,
                                    )
                                ),
                                'summary' => $summary
                        )
                    );
                     
                    $tmpKey = "zbrewardup:$singerid" . ":" . time();
                    $userAttrModel->getRedisMaster()->set($tmpKey, json_encode($msg));
                     
                    $url = GlobalConfig::GetSendGrpMsgURL() . $tmpKey;
                    $ch = curl_init();
                    $curl_opt = array(
                            CURLOPT_URL => $url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT_MS => 1000
                    );
                    curl_setopt_array($ch, $curl_opt);
                    $data = curl_exec($ch);
                    curl_close($ch);
                    LogApi::logProcess("ToolApi::sendSunGiftV3***************rediskey:$tmpKey send msg rsp:$data");
                }           
            }
            
            // 優化：贈送禮物廣播包
            $broadcastResult['list'][0] = array(
                'uid' => $uid,
                'type' => $tool['category2'],
                'id' => $tool['id'],
                'num' => 1,
                'giftValue' => $sunvalue,
                'nick' => $params['sender'],
                'giftName' => $tool['name'],
                'sunshine' => $sunvalue,
                'sunshineTotal' => $sunshineTotal,
                'level' => $level_id,
                'flag' => $flag,
                'imgUrl' => (string)$tool['icon'],
                'sendTime' => $sendTime,
                'photo' => $user['photo'],
                'serialNum' => $serialNum,
                'effect_id' => isset($tool['effect_id']) ? intval($tool['effect_id']):0,
                'show_time' => isset($tool['show_time']) ? intval($tool['show_time']):0
            );
            
            $return[] = array(
                'broadcast' => 1, //全直播间
                'data' => $broadcastResult
            );
            
            //阳光礼物上跑马灯
            {
                //获取发包数据
                $vipInfo = $userAttrModel->getVipInfo($userAttr);
                $richManInfo = $userAttrModel->getRichManLevel($userAttr['uid'], $userAttr['gift_consume'], $userAttr['consume_level']);
                $senderNick = $params['sender'];
                $tsNow = time();
                $num= 1;

                // 全區廣播（超过50块钱即：5000秀币）
                $sys_parameters = new SysParametersModel();
                $pmdPrice = $sys_parameters->GetSysParameters(251, 'parm1');
                if (empty($pmdPrice)) {
                    $pmdPrice = 8000;
                }
                 
                $pmd = false;                
                $totalPrice = $sunvalue; 
                //单次阳光价值大于触发条件，每次都出现
                if($totalPrice >= $pmdPrice){
                  $pmd = true;
                }
                //当单次阳光不满足触发条件，但是连送情况下累计可以触发
                if($totalPrice < $pmdPrice)
                {
                    $tiantou=( 0 == $pmdPrice % $totalPrice) ? 0 : 1;//判断次数是否可以整除，如果可以，添头为0，如果不可以，添头为1
                    if( 0 ==$serialNum %( $pmdPrice/$totalPrice + $tiantou ) ){
                        $pmd = true;
                    }
                }

                if ($pmd) {     
                    $honors = array();
                    $gifts = $toolModel->getAllTools();
                    foreach ($gifts as $g){
                        $top3 = $toolModel->getTop3($g['id'], $uid);
                        $honor = 0;
                        $index = 0;
                        foreach ($top3 as $top){
                            $index++;
                            if($top == $uid){
                                $honor = $index;
                                break;
                            }
                        }
                        if($honor){
                            $item = array();
                            $item['tid'] = $g['id'];
                            $item['img'] = $g['icon'];
                            $item['index'] = $honor;
                            $honors['items'][] = $item;
                        }
                    }
                    
                    $pmd_lvl2_price = $sys_parameters->GetSysParameters(227, 'parm1');
                    if (empty($pmd_lvl2_price)) {
                        $pmd_lvl2_price = 99900;
                    }
                
                    $toollevel = 0;            
                    //if($totalPrice >= $pmd_lvl2_price){
                    //  $toollevel = 1;
                    //}
                    //连送情况下，连送累计金额大于2级跑马灯下限金额
                    if( ($totalPrice * $serialNum ) >= $pmd_lvl2_price){
                      $toollevel = 0;
                    }


                    // 星老勋章
                    $star_chief_rank = 0;
                    $star_chief_index = 0;
                    $week_star_top3 = $toolModel->get_week_star_cheif_top3();
                    if (!empty($week_star_top3) && is_array($week_star_top3)) {
                        foreach ($week_star_top3 as $m) {
                            $star_chief_index++;
                            if ($uid == $m) {
                                $star_chief_rank = $star_chief_index;
                                break;
                            }
                        }
                    }
                
                    $buffer_object = array(
                            'receiver' => $params['uid_onmic'],
                            'receiverNick' => $params['receiver'],
                            'vip' => $vipInfo['vip'],
                            'richManLevel' => $richManInfo['richManLevel'],
                            'richManTitle' => $richManInfo['richManTitle'],
                            'richManStart' => $richManInfo['richManStart'],
                            'sender' => $uid,
                            'senderNick' => $senderNick,
                            'type' => $tool['category2'],
                            'id' => $tool['id'],
                            'icon' => $tool['icon'],
                            'resource' => $tool['resource'],
                            'gift_name' => $tool['name'],
                            'ts' => $tsNow,
                            'num' => $num,
                            'honors' => $honors,
                            'toolLevel' => $toollevel,
                            'sid' => $sid,
                            'serialNum'=>$serialNum,
                            'b_week_star' => $toolModel->b_week_star($singerid) ? 1 : 0,
                            'star_chief_rank' => $star_chief_rank

                    );
                
                    $return[] = array(
                            'broadcast' => 4,
                            'data' => array(
                                    'cmd' => 'BBroadcast',
                                    'type' => 1,//BBroadcastGift type
                                    'type2' => 101,
                                    'gift'=>$buffer_object,
                            )
                    );
                    $isAllPlatform = true;
               }
            }


            // 补发主播阳光统计更新包
            $light_nt = array();
            $light_nt['cmd'] = 'BSingerAttrLightUpdate';
            
            $light_nt['uid'] = $singerid;
            $light_nt['sid'] = $sid;
            $light_nt['lightFinal'] = $sunshineTotal;
            $light_nt['lightDelta'] = $sunvalue;
            
            $return[] = array
            (
                'broadcast' => 1, //全直播间
                'data' => $light_nt,
            );
            
            do {
                // 计算主播分成比例变化
                if (empty($anchorInfo) || empty($oldAnchorInfo)) {
                    LogApi::logProcess('ToolApi::sendSunGiftV3 计算分成比例失败***主播id:'.$singerid . '***getSingerAnchorInfo failure');
                    break;
                }
                 
                if ($oldLevel_id == 5 || $level_id == 5) {
                    LogApi::logProcess('ToolApi::sendSunGiftV3 计算分成比例失败***主播id:'.$singerid . '***oldlevel:' . $oldLevel_id . ' level:' . $level_id);
                    break;
                }
                 
                $alinfo = array();
                $rows = $toolConsumRecordModel->getDbRaidcall()->query("select * from raidcall.anchor_level_info");
                if ($rows && $rows->num_rows > 0) {
                    $row = $rows->fetch_assoc();
                    while ($row) {
                        $item = array();
                        $item['upgrade_experience'] = $row['upgrade_experience'];
                        $item['bonus_scale'] = $row['bonus_scale'];
                        $alinfo[$row['anchor_level']] = $item;
                        $row = $rows->fetch_assoc();
                    }
                }
                 
                if (empty($alinfo)) {
                    LogApi::logProcess('ToolApi::sendSunGift 计算分成比例失败***主播id:'.$singerid . '***select anchor_level_info failure');
                    break;
                }
                 
                if (empty($alinfo[$oldLevel_id]) || empty($alinfo[$oldLevel_id+1])
                        || empty($alinfo[$level_id]) || empty($alinfo[$level_id+1])) {
                    LogApi::logProcess('ToolApi::sendSunGift 计算分成比例失败***主播id:'.$singerid . '***anchor_level_info invild');       
                    break;       
                }
                
                LogApi::logProcess("ToolApi::sendSunGift 计算分成比例 oldAnchorInfo:[" . json_encode($oldAnchorInfo) . "] newAnchorInfo:[" . json_encode($anchorInfo) . "] alinfo:[" . json_encode($alinfo) . "]");
                $oldRatio = ($oldAnchorInfo ['anchor_current_experience'] - $alinfo [$oldLevel_id] ['upgrade_experience']) / ($alinfo [$oldLevel_id + 1] ['upgrade_experience'] - $alinfo [$oldLevel_id] ['upgrade_experience']) * ($alinfo [$oldLevel_id + 1]['bonus_scale'] / 100 - $alinfo [$oldLevel_id]['bonus_scale'] / 100) * 0.62 + $alinfo [$oldLevel_id]['bonus_scale'] / 100;
                
                $oldRatio *= 0.2;
                $oldRatio = floor ( $oldRatio * 100 * 100 );
                
                $ratio = ($anchorInfo ['anchor_current_experience'] - $alinfo [$level_id] ['upgrade_experience']) / ($alinfo [$level_id + 1] ['upgrade_experience'] - $alinfo [$level_id] ['upgrade_experience']) * ($alinfo [$level_id + 1]['bonus_scale'] / 100 - $alinfo [$level_id]['bonus_scale'] / 100) * 0.62 + $alinfo [$level_id]['bonus_scale'] / 100;
                
                $ratio *= 0.2;
                $ratio = floor ( $ratio * 100 * 100 );
                
                if ($ratio < 0 || $oldRatio < 0 || $oldRatio > $ratio) {
                    LogApi::logProcess('ToolApi::sendSunGift 计算分成比例失败***主播id:'.$singerid . '***old divid scale:' . $oldRatio . ' divid scale:' . $ratio);                       
                    break;
                }
                
                $dividScale = $ratio - $oldRatio;
                $dividScale = $dividScale / 100;
                
                if ($dividScale >= 0.01) {
                    
                    $dividScaleNtfy = array (
                            'cmd' => 'BSingerIncDividScale',
                            'singerid' => $singerid,
                            'scale' => $dividScale
                    );
                    
                    $return[] = array (
                            'broadcast' => 6,
                            'target_uid' => $singerid,
                            'data' => $dividScaleNtfy// 发给主播
                    );
                }
            } while(0);
            
        }else{
            $result['result'] = 303; // 用户金币不足
        }
        
        $result['totalSun'] = (int)$newUserAttr['sun_num'];
        $result['money'] = (int)$newUserAttr['coin_balance'];
        
        $return[] = array(
            'broadcast' => 0,
            'data' => $result
        );
        
        if ($tool['id'] == 450 && $result['result'] == 0) {
            $toolModel->addNewerGiftCountUsed($uid);
        }
        
        LogApi::logProcess('************sendSunGiftV3::end*************return:'.json_encode($return));
        
        return $return;
    }
    //发送帮会礼物
    public static function sendGangGift($params, $userAttr, $result, $broadcastResult)
    {
        LogApi::logProcess('************sendGangGift*************');
        $return = array();
        do 
        {
            $userAttrModel = new UserAttributeModel();
            $toolModel = new ToolModel();
            
            $sid = (int)$params['sid'];
            $uid = (int)$params['uid'];
            $singerid = (int)$params['uid_onmic'];  
            $id = $params['id'];
            
            $inf_goods = $toolModel->getGoodsInfo($id);
            if (empty($inf_goods)) {
            	$result['result'] = 100;
            	LogApi::logProcess("ToolApi sendGangGift get goods info failure." );
            	break;
            }
            
            //0:单送 1:连送
            $flag = intval($params['flag']);
            //连送数量
            $serialNum = $params['serialNum'];
            
            $sunvalue = 0;
            
            $sendTime = $params['sendTime'];
            $nick = $params['sender'];
            
            $id = $params['id'];
            $type = $params['type'];
            
            $userInfo = new UserInfoModel();
            $user = $userInfo->getInfoById($uid);
            
            $userAttr = $userAttrModel->getAttrByUid($uid);
            
            $buy = 1; // 即买即用（送），默认是买
            if (empty($params['buy'])) 
            {
                $buy = 0; // 使用包裹里面的道具
            }
            $result['buy'] = $buy;
            $result['money'] = (int)$userAttr['coin_balance'];
            $result['totalSun'] = (int)$userAttr['sun_num'];
            $result['coinBalance'] = (int)$userAttr['coin_balance'];
            $result['luckyShakeCount'] = (int)$userAttrModel->getActivity($userAttr);
            
            //获取主播最新阳光总值
            $channelLiveModel = new ChannelLiveModel();
            $anchorInfo = $channelLiveModel->getSingerAnchorInfo($singerid);
            $sunshineTotal = 0;
            $level_id = 1;
            if(!empty($anchorInfo))
            {
                $sunshineTotal = (int)$anchorInfo['anchor_current_experience'];
                $level_id = (int)$anchorInfo['level_id'];
            }
            $uti = new UnionTicketInfo();
            $toolModel->GetUnionTicketInfo($uti);
            
            $num = $params['num'];
            if (0 == $num)
            {
                $result['result'] = 0;
                break;
            }
            $delta = $num;
            $goods_id = ToolApi::$PACK_ITEM_ID;
            $goods_type = ToolApi::$PACK_ITEM_TYPE;
            $sql = "UPDATE card.user_goods_info SET num = num - $delta WHERE ( uid = $uid && goods_id = $goods_id && goods_type = $goods_type && num >= $delta )";
            $mysql = $toolModel->getDbRecord();
            $rows = $mysql->query($sql);
            LogApi::logProcess("mysql->affected_rows:".$mysql->affected_rows);
            LogApi::logProcess("info GameModel::sendGangGift rows:".json_encode($rows));
            // LogApi::logProcess("info GameModel::sendGangGift****************sql:$sql");
            if(!$rows || 0 >= $mysql->affected_rows)
            {
                LogApi::logProcess("error GameModel::sendGangGift****************sql:$sql");
                $result['result'] = 122; // 數量小於0
                break;
            }
            // LogApi::logProcess("GameModel::sendGangGift 1.");

            // 累积帮会票红包触发条件
            RedPacketApi::on_gticket_received($sid, $singerid, $uid, $num);

            $serialNum = $toolModel->IncrAndGetSerialNumber($uid, $sendTime);
            $broadcastResult['list'][0] = array
            (
                'uid' => $uid,
                'type' => $type,
                'id' => $id,
                'num' => 1,
                'giftValue' => $sunvalue,
                'nick' => $nick,
                'giftName' => $uti->goods_name,
                'sunshine' => $sunvalue,
                'sunshineTotal' => $sunshineTotal,
                'level' => $level_id,
                'flag' => $flag,
                'imgUrl' => $uti->goods_icon,
                'sendTime' => $sendTime,
                'photo' => $user['photo'],
                'serialNum' => $serialNum,
            	'effect_id' => isset($inf_goods['effect_id']) ? intval($inf_goods['effect_id']):0,
            	'show_time' => isset($inf_goods['show_time']) ? intval($inf_goods['show_time']):0
            );
            $return[] = array
            (
                'broadcast' => 1, //全直播间
                'data' => $broadcastResult
            );
            
            // LogApi::logProcess("GameModel::sendGangGift 2.");
            $weekTool = $toolModel->GetWeekToolByUid($singerid);
            if (0 == $weekTool)
            {
                $result['result'] = 401;// 主播没有报名周星
                break;
            }
            // LogApi::logProcess("GameModel::sendGangGift 3.");
            $userAttrModel = new UserAttributeModel();
            $userAttr = $userAttrModel->getAttrByUid($uid);
            $vipInfo = $userAttrModel->getVipInfo($userAttr);
            //$richManInfo = $userAttrModel->getRichManLevel($userAttr['uid'], $userAttr['gift_consume']);
            // LogApi::logProcess("GameModel::sendGangGift 4.");
            // 判断道具是否有效
            $tool = $toolModel->getToolByTid($weekTool, $vipInfo['giftDiscount']);
            $price = $tool['price'];
            if (0 == $price)
            {
                $result['result'] = 401;// 主播没有报名周星礼物,什么也不做
                break;
            }
            // LogApi::logProcess("GameModel::sendGangGift 5.");
            $number = ( $num * $uti->convert ) / $price;            
            $jf = $num * $uti->convert;
            if ($toolModel->if_anchor_gticket_outof_limit($singerid, $num)) {
                $number = 0;
                $jf = 0;
                $result['gticket_overflow'] = 1;
                $result['tips'] = "本周直播间帮会票收入已达上限，赠送不再增加主播周星积分，可以增加个人周星任务积分";
            } else {
                $result['gticket_overflow'] = 0;
            }
            // LogApi::logProcess("GameModel::sendGangGift 6.");
            //加周星记录
            $toolModel->UpdateWeekToolRecord($uid, $singerid, $weekTool, $jf, floor( $number ) );
            
            $toolConsumRecordModel = new ToolConsumeRecordModel();
            
            $userInfo = new UserInfoModel();
            $user = $userInfo->getInfoById($singerid);
            
            $unionId = (int)$user['union_id'];
            $sid = $userInfo->GetSidByUid($singerid);
            // LogApi::logProcess("GameModel::sendGangGift 7.");
            $category1 = $tool['category1'];
            $category2 = $tool['category2'];
            // 计入消费记录
            $info = new WeekToolConsumeRecordInfo();
            $info->now = time();
            $info->uid = $uid;
            $info->singerUid = $singerid;
            $info->tid = $weekTool;// 道具id
            $info->tool_category1 = $category1;// 道具一级目录
            $info->tool_category2 = $category2;// 道具二级目录
            $info->qty = $number;// 数量
            $info->tool_price = $price;
            $info->total_coins_cost = $uti->convert;
            // LogApi::logProcess("GameModel::sendGangGift 8.");
            $toolConsumRecordModel->AppendWeekToolConsumeRecord($info);  
            // LogApi::logProcess("GameModel::sendGangGift 9.");
        }while(FALSE);
        $return[] = array
        (
            'broadcast' => 0,
            'data' => $result
        );
        LogApi::logProcess("sendGangGift uid:".$uid." return:".json_encode($return));
        return $return;
    }


    public static function sendGangGiftV3($params, $userAttr, $result, &$broadcastResult)
    {
        LogApi::logProcess('************sendGangGiftV3*************');
        $return = array();
        do 
        {
            $userAttrModel = new UserAttributeModel();
            $toolModel = new ToolModel();
            
            $sid = (int)$params['sid'];
            $uid = (int)$params['uid'];
            $singerid = (int)$params['uid_onmic'];  
            $id = $params['id'];
            
            $inf_goods = $toolModel->getGoodsInfo($id);
            if (empty($inf_goods)) {
                $result['result'] = 100;
                LogApi::logProcess("ToolApi sendGangGiftV3 get goods info failure." );
                break;
            }
            
            //0:单送 1:连送
            $flag = intval($params['flag']);
            //连送数量
            $serialNum = $params['serialNum'];
            
            $sunvalue = 0;
            
            $sendTime = $params['sendTime'];
            $nick = $params['sender'];
            
            $id = $params['id'];
            $type = $params['type'];
            
            $userInfo = new UserInfoModel();
            $user = $userInfo->getInfoById($uid);
            
            $userAttr = $userAttrModel->getAttrByUid($uid);
            
            $buy = 1; // 即买即用（送），默认是买
            if (empty($params['buy'])) 
            {
                $buy = 0; // 使用包裹里面的道具
            }
            $result['buy'] = $buy;
            $result['money'] = (int)$userAttr['coin_balance'];
            $result['totalSun'] = (int)$userAttr['sun_num'];
            $result['coinBalance'] = (int)$userAttr['coin_balance'];
            $result['luckyShakeCount'] = (int)$userAttrModel->getActivity($userAttr);
            
            //获取主播最新阳光总值
            $channelLiveModel = new ChannelLiveModel();
            $anchorInfo = $channelLiveModel->getSingerAnchorInfo($singerid);
            $sunshineTotal = 0;
            $level_id = 1;
            if(!empty($anchorInfo))
            {
                $sunshineTotal = (int)$anchorInfo['anchor_current_experience'];
                $level_id = (int)$anchorInfo['level_id'];
            }
            $uti = new UnionTicketInfo();
            $toolModel->GetUnionTicketInfo($uti);
            
            $num = $params['num'];
            if (0 == $num)
            {
                $result['result'] = 0;
                break;
            }
            $delta = $num;
            $goods_id = ToolApi::$PACK_ITEM_ID;
            $goods_type = ToolApi::$PACK_ITEM_TYPE;
            $sql = "UPDATE card.user_goods_info SET num = num - $delta WHERE ( uid = $uid && goods_id = $goods_id && goods_type = $goods_type && num >= $delta )";
            $mysql = $toolModel->getDbRecord();
            $rows = $mysql->query($sql);
            LogApi::logProcess("mysql->affected_rows:".$mysql->affected_rows);
            LogApi::logProcess("info GameModel::sendGangGiftV3 rows:".json_encode($rows));
            // LogApi::logProcess("info GameModel::sendGangGift****************sql:$sql");
            if(!$rows || 0 >= $mysql->affected_rows)
            {
                LogApi::logProcess("error GameModel::sendGangGiftV3****************sql:$sql");
                $result['result'] = 122; // 數量小於0
                break;
            }
            // LogApi::logProcess("GameModel::sendGangGift 1.");

            // 累积帮会票红包触发条件
            $rp_result = RedPacketApi::on_gticket_received($sid, $singerid, $uid, $num);

            $serialNum = $toolModel->IncrAndGetSerialNumber($uid, $sendTime);
            $broadcastResult['list'][0] = array
            (
                'uid' => $uid,
                'type' => $type,
                'id' => $id,
                'num' => 1,
                'giftValue' => $sunvalue,
                'nick' => $nick,
                'giftName' => $uti->goods_name,
                'sunshine' => $sunvalue,
                'sunshineTotal' => $sunshineTotal,
                'level' => $level_id,
                'flag' => $flag,
                'imgUrl' => $uti->goods_icon,
                'sendTime' => $sendTime,
                'photo' => $user['photo'],
                'serialNum' => $serialNum,
                'effect_id' => isset($inf_goods['effect_id']) ? intval($inf_goods['effect_id']):0,
                'show_time' => isset($inf_goods['show_time']) ? intval($inf_goods['show_time']):0
            );
//             $return[] = array
//             (
//                 'broadcast' => 1, //全直播间
//                 'data' => $broadcastResult
//             );

            if (!empty($rp_result)) {
                $return[] = $rp_result;
            }
            
            // LogApi::logProcess("GameModel::sendGangGift 2.");
            $weekTool = $toolModel->GetWeekToolByUid($singerid);
            if (0 == $weekTool)
            {
                $result['result'] = 401;// 主播没有报名周星
                break;
            }
            // LogApi::logProcess("GameModel::sendGangGift 3.");
            $userAttrModel = new UserAttributeModel();
            $userAttr = $userAttrModel->getAttrByUid($uid);
            $vipInfo = $userAttrModel->getVipInfo($userAttr);
            //$richManInfo = $userAttrModel->getRichManLevel($userAttr['uid'], $userAttr['gift_consume']);
            // LogApi::logProcess("GameModel::sendGangGift 4.");
            // 判断道具是否有效
            $tool = $toolModel->getToolByTid($weekTool, $vipInfo['giftDiscount']);
            $price = $tool['price'];
            if (0 == $price)
            {
                $result['result'] = 401;// 主播没有报名周星礼物,什么也不做
                break;
            }
            // LogApi::logProcess("GameModel::sendGangGift 5.");
            $number = ( $num * $uti->convert ) / $price;            
            $jf = $num * $uti->convert;
            if ($toolModel->if_anchor_gticket_outof_limit($singerid, $num)) {
                $number = 0;
                $jf = 0;
                $result['gticket_overflow'] = 1;
                $result['tips'] = "本周直播间帮会票收入已达上限，赠送不再增加主播周星积分，可以增加个人周星任务积分";
            } else {
                $result['gticket_overflow'] = 0;
            }
            // LogApi::logProcess("GameModel::sendGangGift 6.");
            //加周星记录
            $toolModel->UpdateWeekToolRecord($uid, $singerid, $weekTool, $jf, floor( $number ) );
            
            $toolConsumRecordModel = new ToolConsumeRecordModel();
            
            $userInfo = new UserInfoModel();
            $user = $userInfo->getInfoById($singerid);
            
            $unionId = (int)$user['union_id'];
            $sid = $userInfo->GetSidByUid($singerid);
            // LogApi::logProcess("GameModel::sendGangGift 7.");
            $category1 = $tool['category1'];
            $category2 = $tool['category2'];
            // 计入消费记录
            $info = new WeekToolConsumeRecordInfo();
            $info->now = time();
            $info->uid = $uid;
            $info->singerUid = $singerid;
            $info->tid = $weekTool;// 道具id
            $info->tool_category1 = $category1;// 道具一级目录
            $info->tool_category2 = $category2;// 道具二级目录
            $info->qty = $number;// 数量
            $info->tool_price = $price;
            $info->total_coins_cost = $uti->convert;
            // LogApi::logProcess("GameModel::sendGangGift 8.");
            $toolConsumRecordModel->AppendWeekToolConsumeRecord($info);  
            // LogApi::logProcess("GameModel::sendGangGift 9.");
        }while(FALSE);
        $return[] = array
        (
            'broadcast' => 0,
            'data' => $result
        );
        LogApi::logProcess("sendGangGiftV3 uid:".$uid." return:".json_encode($return));
        return $return;
    }
    //公聊
    public static function textChat($params)
    {
        LogApi::logProcess('room textchat::uid:'.$params['uid'].' sid:'.$params['sid'].' params:'.json_encode($params));
        
        $sid = $params['sid'];
        $uid = $params['uid'];
        $singerUid = $params['singerid'];
        $singerGuardModel = new SingerGuardModel();
        $endTime = $singerGuardModel->getGuardEndTime($uid, $singerUid);
        $guardType = $singerGuardModel->getGuardType($uid, $singerUid);
        $guardName = "";
        
        $now = time();
        $isguard = 0 ;
        if (!empty($endTime) && $endTime > $now) {
            if(1 == $guardType || 2 == $guardType || 3 == $guardType){
                //守护有效
                $isguard = 1;
            }
            
            if (1 == $guardType || 2 == $guardType || 3 == $guardType || 10 == $guardType || 11 == $guardType) {
            	$guardName = $singerGuardModel->getGuardName($singerUid, $guardType);
            }
        }
        
        $return = array();
        
        $result = array(
            'cmd' => 'RTextChat',
            'uid' => $uid,
            'result' => 0
        );
        
        $channelLiveModel = new ChannelLiveModel();
        $flag = $channelLiveModel->isDisableText($sid, $uid);
        
        if($flag && 0 == $isguard){
            //被禁言
            $result['result'] = 1;
            LogApi::logProcess($uid.'*******被禁言***textChat::');
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        //
        {
            //根据用户id，获取用户属性
            $userinfo_modle = new UserInfoModel();
            $info_user = $userinfo_modle->getInfoById($uid);
            // 触发机器人说话的监听流程
            $rtm = new robot_talk_model();
            $rtm->on_robot_listen_set_redis($sid,$info_user);
        }
        //用户发言会增加主播热度值
        if($singerUid != $uid){
            $model_anchor_pt = new anchor_points_model();
            $model_anchor_pt->on_user_speak($singerUid, $uid);
            
            //任务数据
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 8,//8为直播间发言
                    'num' => 1,
                    'extra_param' => 0
                )
            );
            
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$singerUid,
                    'target_type' => 20,//20为直播间发言总数
                    'num' => 1,
                    'extra_param' => 0
                )
            );
            /*//去掉粉丝团任务 
            if($singerGuardModel->isDiehard($singerUid, $uid)){
                $return[] = array(
                    'broadcast' => 5,
                    'data' => array(
                        'uid' => (int)$uid,
                        'target_type' => 8,//8为直播间发言
                        'num' => 1,
                        'extra_param' => (int)$singerUid
                    )
                );
            } */
        }
        
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        
        $singerUser = $userInfo->getInfoById($singerUid);
        $isUnionGuard = $channelLiveModel->isUnionGuard($singerUid, $uid);
        if($isUnionGuard){
            //更新主播所在帮会信息
            $userInfo->updateUnionSpeakCount($user['union_id'], $singerUid);
        }
        
        $userAttrModel = new UserAttributeModel();
//         $userAttrModel->sayCurDayTimes($uid);
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $activeManInfo = $userAttrModel->getActiveLevel($userAttr['active_point'], $uid, 0);
        
        LogApi::logProcess('**********textChat::userAttr:'.json_encode($userAttr));
        LogApi::logProcess('**********textChat::active:'.json_encode($activeManInfo));
        
        $txt = WordConfig::filterWord($params['context']);
        LogApi::logProcess('**********textChat::过滤后的文本内容:'.$txt);
        if(empty($txt)){
            $txt = $params['context'];
        }
        
        $tailLight = $userAttrModel->getTaillight($uid);
        
        $official = isset($userAttr['official'])?intval($userAttr['official']):0;
        $broadcastResult = array(
            'cmd' => 'BTextChat',
            'uid' => $uid,
            'sid' => $sid,
            'nickname' => $user['nick'],
            'isguard' => $isguard,
            'guardType' => $guardType,
        	'guardName' => $guardName,
            'activeLevel' => $activeManInfo['activeManLevel'],
            'activeLevelEffect' => $activeManInfo['activeManEffect'],
            'context' => $txt,
            'identity' => (int)$user['identity'],
        	'official' => $official
        );
        
        if (!empty($tailLight)) {
        	$tailLight = json_decode($tailLight);
        	$broadcastResult['tailLight']['ranking'] = (int)$tailLight->rank;
        	$broadcastResult['tailLight']['rankType'] = $tailLight->rankType;
        	$broadcastResult['tailLight']['timeType'] = $tailLight->statisticsType;
        }

        // 新人标签
        
        $is_robot = (int)$user['is_robot'];
        $new = 0;
        
        if (!$is_robot) {
        	if (isset($userAttr['new'])) {
        		$new = $userAttr['new'];
        	} else {
        		$new = UserApi::isNewerByUattrinfo($userAttr, $user);
        	}
        }

        $broadcastResult['new'] = $new;

        $return[] = array(
            'broadcast' => 0, 
            'data' => $result
        );
        
        $return[] = array(
            'broadcast' => 1, //全直播间
            'data' => $broadcastResult
        );
        
        LogApi::logProcess('**********textChat::'.json_encode($return));
        
        return $return;
    }
    
    //@人指令
    public static function atMessage($params)
    {
        LogApi::logProcess('**********atMessage::'.json_encode($params));
        $fUif = $params['fromUid'];
        $singerUid = $params['singerid'];
        $sid = $params['sid'];
        $toUid = $params['toUid'];
        $singerGuardModel = new SingerGuardModel();
        $endTime = $singerGuardModel->getGuardEndTime($fUif, $singerUid);
        $guardType = $singerGuardModel->getGuardType($fUif, $singerUid);
        $guardName = "";
        
        $now = time();
        $isguard = 0 ;
        if (!empty($endTime) && $endTime > $now) {
            if(1 == $guardType || 2 == $guardType || 3 == $guardType){
                //守护有效
                $isguard = 1;
            }
            if (1 == $guardType 	|| 2 == $guardType 		||
            		3 == $guardType || 10 == $guardType 	||
            		11 == $guardType) {
            			$guardName = $singerGuardModel->getGuardName($singerUid, $guardType);
            }
        }
//         $activeLevel = 0;//活跃等级
        $userAttrModel = new UserAttributeModel();
//         $userAttrModel->sayCurDayTimes($fUif);
        $userAttr = $userAttrModel->getAttrByUid($fUif);
        $activeManInfo = $userAttrModel->getActiveLevel($userAttr['active_point'], $fUif, 0);
        
        $userInfo = new UserInfoModel();
        $user_f = $userInfo->getInfoById($fUif);
        $user_t = $userInfo->getInfoById($toUid);
        
        $tailLight = $userAttrModel->getTaillight($fUif);
        
        $official = isset($userAttr['official'])?intval($userAttr['official']):0;
        
        //
        {
            // 触发机器人说话的监听流程
            $rtm = new robot_talk_model();
            $rtm->on_robot_listen_set_redis($sid,$user_f);
        }
        $broadcastResult = array(
            'cmd' => 'BAtMessage',
            'sid' => $sid,
            'singerid' => $singerUid,
            'fromUid' => $fUif,
            'fromNickname' => $params['fromNickname'],
            'context' => $params['context'],
            'isguard' => $isguard,
            'guardType' => $guardType,
        	'guardName' => $guardName,
            'activeLevel' => $activeManInfo['activeManLevel'],
            'activeLevelEffect' => $activeManInfo['activeManEffect'],
            'toUid' => $toUid,
            'toNickname' => $params['toNickname'],
            'identity' => (int)$user_f['identity'],
            'identity_to' => (int)$user_t['identity'],
        	'official' => $official
        );
        
        if (!empty($tailLight)) {
        	$tailLight = json_decode($tailLight);
        	$broadcastResult['tailLight']['ranking'] = (int)$tailLight->rank;
        	$broadcastResult['tailLight']['rankType'] = $tailLight->rankType;
        	$broadcastResult['tailLight']['timeType'] = $tailLight->statisticsType;
        }

        // 新人标签
        $is_robot = (int)$user_f['is_robot'];
        $new = 0;
        
        if (!$is_robot) {
        	if (isset($userAttr['new'])) {
        		$new = $userAttr['new'];
        	} else {
        		$new = UserApi::isNewerByUattrinfo($userAttr, $user_f);
        	}
        }

        $broadcastResult['new'] = $new;
        
        $return = array();
        $return[] = array(
            'broadcast' => 1, //全直播间
            'data' => $broadcastResult
        );
        
        if($singerUid != $fUif){
            $model_anchor_pt = new anchor_points_model();
            $model_anchor_pt->on_user_speak($singerUid, $fUif);
        	//任务数据
        	$return[] = array(
        			'broadcast' => 5,
        			'data' => array(
        					'uid' => (int)$fUif,
        					'target_type' => 8,//8为直播间发言
        					'num' => 1,
        					'extra_param' => 0
        			)
        	);
        
        	$return[] = array(
        			'broadcast' => 5,
        			'data' => array(
        					'uid' => (int)$singerUid,
        					'target_type' => 20,//20为直播间发言总数
        					'num' => 1,
        					'extra_param' => 0
        			)
        	);
        }
        
        LogApi::logProcess('**********return atMessage::'.json_encode($return));
        
        return $return;
    }
    
    //发送弹幕
    public static function sendBarrage($params)
    {
        LogApi::logProcess('**********begin sendBarrage::'.json_encode($params));
        
        $txt = WordConfig::filterWord($params['context']);
        LogApi::logProcess('**********sendBarrage::过滤后的文本内容:'.$txt);
        if(empty($txt)){
            $txt = $params['context'];
        }
        
        $sid = (int)$params['sid'];
        $uid = (int)$params['uid'];
        $singerid = (int)$params['uid_onmic'];
        if(!empty($params['singerid'])){
            $singerid = (int)$params['singerid'];
        }
        
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        //
        {
            // 触发机器人说话的监听流程
            $rtm = new robot_talk_model();
            $rtm->on_robot_listen_set_redis($sid,$user);
        }
        $result = array(
            'cmd' => 'RSendBarrage',
            'context' => $txt,
            'uid' => $params['uid'],
            'singerid' => $singerid,
            'sid' => $sid,
            'nickname' => $user['nick'],
            'result' => 0
        );
        
        $singerGuardModel = new SingerGuardModel;
        $guardType = $singerGuardModel->getGuardType($uid, $singerid);
        $guardName = "";
        
        if (1 == $guardType || 2 == $guardType || 3 == $guardType || 10 == $guardType || 11 == $guardType) {
        	$guardName = $singerGuardModel->getGuardName($singerid, $guardType);
        }
        
        if ($userAttr['coin_balance'] < ToolApi::$SEND_BARRAGE_COST) {
            $result['result'] = 101; // 用户秀币不足
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        
        
        $channelLiveModel = new ChannelLiveModel();
        $flag = $channelLiveModel->isDisableText($sid, $uid);
        
        if($flag){
            //被禁言
            $result['result'] = 1;
            LogApi::logProcess($uid.'*******被禁言***textChat::');
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        
        
        
        $toolConsumRecordModel = new ToolConsumeRecordModel();
        $success = $toolConsumRecordModel->consumeCoin($uid, ToolApi::$SEND_BARRAGE_COST);
        
        if(empty($success) || !$success){
            $result['result'] = -1;
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        
        //增加活跃度
//         $userAttrModel->sayCurDayTimes($uid);
        $userAttr = $userAttrModel->getAttrByUid($uid);
        
        $result['money'] = $userAttr['coin_balance'];
        
        $activeManInfo = $userAttrModel->getActiveLevel($userAttr['active_point'], $uid, 0);
        
        //TODO:阳光
        $charismaModel = new CharismaModel();
        $sunCount = $charismaModel->anchorSunshine($singerid,1);
        //$sunCount = $charismaModel->getAnchorSun($singerid);
        // 新人
        $is_robot = (int)$user['is_robot'];
 
        $new = 0;
        
        if (!$is_robot) {
        	if (isset($userAttr['new'])) {
        		$new = $userAttr['new'];
        	} else {
        		$new = UserApi::isNewerByUattrinfo($userAttr, $user);
        	}	
        }
        
        $tailLight = $userAttrModel->getTaillight($uid);
        $official = isset($userAttr['official'])?intval($userAttr['official']):0;
        
        $broadcastResult = array(
            'cmd' => 'BSendBarrage',
            'uid' => $params['uid'],
            'singerid' => $singerid,
            'sid' => $sid,
            'nickname' => $user['nick'],
            'context' => $txt,
            'identity' => (int)$user['identity'],
        	'guardName' => $guardName,
            'guardType' => $guardType,
            'activeLevel' => $activeManInfo['activeManLevel'],
            'activeLevelEffect' => $activeManInfo['activeManEffect'],
            'photo' => $user['photo'],
            'sunCount' => $sunCount,
        	'new' => $new,
        	'official' => $official
        );

        if (!empty($tailLight)) {
            $tailLight = json_decode($tailLight);
            $broadcastResult['tailLight']['ranking'] = (int)$tailLight->rank;
            $broadcastResult['tailLight']['rankType'] = $tailLight->rankType;
            $broadcastResult['tailLight']['timeType'] = $tailLight->statisticsType;
        }
        
        $return = array();
        $return[] = array(
            'broadcast' => 0,
            'data' => $result
        );
        $return[] = array(
            'broadcast' => 1, //全直播间
            'data' => $broadcastResult
        );
        //用户发言会增加主播热度值
        if($singerid != $uid){
            $model_anchor_pt = new anchor_points_model();
            $model_anchor_pt->on_user_speak($singerid, $uid);
        
            //任务数据
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 8,//8为直播间发言
                    'num' => 1,
                    'extra_param' => 0
                )
            );
        
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$singerid,
                    'target_type' => 20,//20为直播间发言总数
                    'num' => 1,
                    'extra_param' => 0
                )
            );
        /* //去掉粉丝团任务
            if($singerGuardModel->isDiehard($singerid, $uid)){
                $return[] = array(
                    'broadcast' => 5,
                    'data' => array(
                        'uid' => (int)$uid,
                        'target_type' => 8,//8为直播间发言
                        'num' => 1,
                        'extra_param' => (int)$singerid
                    )
                );
            } */
        }
        
        $unionId = (int)$user['union_id'];
        $sid = $userInfo->GetSidByUid($singerid);
        // 计入消费记录
        $info = new ToolConsumeRecordInfo();
        $info->now = time();
        $info->uid = $uid;
        $info->singerUid = $singerid;
        $info->sid = $sid;// 房间id
        $info->cid = 1;// 频道id
        $info->tid = 0;// 道具id 弹幕为0
        $info->tool_category1 = 0;// 道具一级目录 弹幕为0
        $info->tool_category2 = 0;// 道具二级目录 弹幕为0
        $info->qty = 1;// 数量 弹幕为1
        $info->buy = 0;// 是不是直接在商城买的 弹幕为0
        $info->tool_price = ToolApi::$SEND_BARRAGE_COST;
        $info->total_coins_cost = ToolApi::$SEND_BARRAGE_COST;
        $info->total_receiver_points = 0;// 接收这产生的秀点 弹幕为0
        $info->total_receiver_charm = ToolApi::$SEND_BARRAGE_COST;
        $info->total_session_points = 0;// 弹幕为0
        $info->total_session_charm = 0;// 弹幕为0
        $info->baseValue = 0;// 主播基础分成
        $info->prizeValue = 0;// 主播奖金上限(主播奖金预算增加值)
        $info->backValue = 0;// 主播回馈基金分成值
        $info->unionTotalValue = 0;// 公会分成值 弹幕为0
        $info->sysControl = 0;// 系统调控基金分成值 弹幕为0
        $info->officialValue = ToolApi::$SEND_BARRAGE_COST;// 官方收入分成值 100%
        $info->unionId = $unionId;// 公会id
        $info->unionValue = 0;// 公会收入预算-公会收益 弹幕为0
        $info->unionBack = 0;// 公会收入预算-公会回馈基金 弹幕为0
        $info->unionPrize = 0;// 公会收入预算-公会奖金预算 弹幕为0
        $info->unionSunValue = 0;// 公会增加阳光值 弹幕为0
        $info->singerSunValue = 0;// 主播增加阳光值 弹幕为0
        
        $toolConsumRecordModel->AppendToolConsumeRecordInfo($info);
        
        LogApi::logProcess('**********end sendBarrage::'.json_encode($return));
        
        return $return;
    }

    public static function useTool($params, $type)
    {
        LogApi::logProcess('ToolApi::useTool entry...'.json_encode($params));
        $now_millisecond = timeclock::getMillisecond();
        if ($type == 1) {
	        $params['returnCmd'] = 'RSendGift';
            $params['broadcastCmd'] = 'BSendGift';
        } elseif ($type == 2) {
            $params['returnCmd'] = 'RSetEffect';
            $params['broadcastCmd'] = 'BEffect';
        }
        
        $sendTime = empty($params['sendTime']) ? time() : $params['sendTime'];
        $params['sendTime'] = $sendTime;
        
        //0:单送 1:连送
        $flag = intval($params['flag']);
        //连送数量
        $serialNum = empty($params['serialNum']) ? 0 : (int)$params['serialNum'];
        
        $result = array(
            'cmd' => $params['returnCmd'],
            'id' => $params['id'],
            'uid' => $params['uid'],
            'sendTime' => $sendTime,
            'flag' => $flag,
            'result' => 0
        );
        $broadcastResult = array(
            'cmd' => $params['broadcastCmd'],
            'receiver' => $params['uid_onmic'],
            'receiverNick' => $params['receiver'],
            'list' => array()
        );

        $uid = (int)$params['uid'];
        $senderNick = $params['sender'];
        
        $sid = (int)$params['sid'];
        $cid = (int)$params['cid'];
        $num = (int)$params['num'];
        
        $uinfoModel = new UserInfoModel();
        $uinfo = $uinfoModel->getInfoById($uid);
        if ($uinfo && !empty($uinfo['nick'])) {
        	$senderNick = $uinfo['nick'];
        	$params['sender'] = $senderNick;
        }
        
        $videoOpen = !empty($params['videoOpen']) ? $params['videoOpen'] : 0;
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $vipInfo = $userAttrModel->getVipInfo($userAttr);
        //$richManInfo = $userAttrModel->getRichManLevel($userAttr['uid'], $userAttr['gift_consume']);
        //类型为ToolApi::$GANG_TYPE，表示帮会礼物
        if((int)$params['type'] == ToolApi::$TOOL_GANG_ITEM_TYPE && (int)$params['id'] == ToolApi::$TOOL_GANG_ITEM_ID){
            return ToolApi::sendGangGift($params, $userAttr, $result, $broadcastResult);
        }
        
        // 这里其实应该根据type进行道具处理，但由于客户端上行type有问题，所以暂时强制对id进行判断
        if (intval($params['id']) == 24 || intval($params['type'] == ToolApi::$PROP_TYPE_NORMAL) || intval($params['type'] == ToolApi::$PROP_TYPE_EFFECT)) {
        	return ToolApi::sendPropGift($params, $userAttr, $result, $broadcastResult);
        }
        
        // 判断道具是否有效
        $tid = (int)$params['id']; // 道具id
        $toolModel = new ToolModel();
        $tool = $toolModel->getToolByTid($tid, $vipInfo['giftDiscount']);

        $toolAccountList = null;
        
        $giftCount = $vipInfo['giftDiscount'];
        $price = $tool['price'];
        
        // 大礼物不能组送，大礼物价格500及以上
        if ($price >= 500) {
        	$params['num'] = 1;
        	$num = 1;
        }
		file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "consume tool tid:$tid,count:$giftCount,price:$price\n", FILE_APPEND);
		// zxy modify 2015-12-29 21:14:13 禁用部分用户
		//if($uid < 0 || $uid >= 10100000 || $uid == 10003266 || $uid == 10003260 || $uid == 10003258 || $uid==10000750){
		if($uid < 0 || $uid == 10003266 || $uid == 10003260 || $uid == 10003258 || $uid==10000750){
			$result['result'] = 100; // 道具不存在或已关闭
			LogApi::logProcess('ToolApi::useTool*************** 用户被禁止送礼');
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
		}
        if (empty($tool) || $tool['closed']) {
            $result['result'] = 100; // 道具不存在或已关闭
            LogApi::logProcess('ToolApi::useTool*************** 道具不存在或已关闭');
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        if ($tool['category1'] != $type) {
            $result['result'] = 120; // 使用了錯誤的接口
            LogApi::logProcess('ToolApi::useTool*************** 使用了錯誤的接口');
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        if (!empty($params['num'])) {
            $params['num'] = intval($params['num']);
            if ($params['num'] <= 0) {
                LogApi::logProcess('ToolApi::useTool*************** 礼物量小于0');
                $result['result'] = 122; // 數量小於0
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
            $qty = (int)$params['num']; //礼物数量
        } else {
            $qty = 1; // 使用（赠送）的道具数，默认是1
        }
        $singerUid = 0; // 接收方用户id
        if (!empty($params['uid_onmic'])) {
            $singerUid = (int)$params['uid_onmic'];
            $singerAttr = $userAttrModel->getAttrByUid($singerUid);
        }
        
        //2016.8.3 liuhw add 当主播未开播时，去获得主播id，根据sid获得singerUid
        /* if (empty($singerUid)) {
            //根据sid去sess_info表里获得房间和主播对应关系
            $channelLiveModel = new ChannelLiveModel();
            $session = $channelLiveModel->getSessionInfo($sid);
            
            if(!empty($session)){
                $singerUid = $session['owner'];
                $singerAttr = $userAttrModel->getAttrByUid($singerUid);
                
        LogApi::logProcess("==================singerUid..$singerUid".json_encode($singerAttr));
            }
        } *///end
        
        $buy = 1; // 即买即用（送），默认是买
        if (empty($params['buy'])) {
            $buy = 0; // 使用包裹里面的道具
        }
        $singerGuardModel = new SingerGuardModel();
        if ($tool['category1'] == ToolModel::TYPE_GIFT) {
            if (empty($singerUid)) {
                LogApi::logProcess('ToolApi::useTool*************** 接收方用户id为空');
                $result['result'] = 103; // 接收方用户id不能为空
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
            if ($uid == $singerUid) {
                $result['result'] = 107; // 不能给自己送礼物
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
            
            // 爵位尊享禮物
            if (!empty($tool['min_rich']) && $userAttr['gift_consume'] < $tool['min_rich']) {
                $result['result'] = 159; // 爵位等級不足，無法贈送爵位尊享禮物
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
            // 主播守護專屬禮物
            if ($tool['min_close'] > 0) {
                // 判斷是否守護
                $singerGuardCode = $singerGuardModel->closeEnough($uid, $singerUid, $tool['min_close']);
                if ($singerGuardCode > 0) {
                    $result['result'] = $singerGuardCode; // 您和主播的親密度不夠，不能贈送守護專屬禮物
                    return array(
                        array(
                            'broadcast' => 0,
                            'data' => $result
                        )
                    );
                }
            }
        } elseif ($tool['category1'] == ToolModel::TYPE_EFFECT) {
            if ($userAttr['experience'] < $tool['min_charm']) {
                $result['result'] = 104; // 用户表演魅力值不足，无法使用此道具
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
            if ($uid != $singerUid) {
                $result['result'] = 111; // 只有表演者才能用表演道具
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
            if ($tool['category2'] == ToolModel::LIGHT_EFFECT) {
                if ($userAttrModel->getStatusByUid($uid, 'effect') == $tid) {
                    $result['result'] = 131; // 道具正在使用中
                    return array(
                        array(
                            'broadcast' => 0,
                            'data' => $result
                        )
                    );
                }
            }
		$tsNow = time();	
            // 對於包月道具(背景)
            if ($tool['consume_type'] == 1) {
                $toolSubsModel = new ToolSubscriptionModel();
                if ($toolSubsModel->hasTool($uid, $tid)) {
                    // 设置背景
                    if ($tool['category2'] == ToolModel::BACKGROUND) {
                        $userAttrModel->setStatusByUid($uid, 'background', $tid);
                    }
                    $broadcastResult['list'][0] = array(
                        'sender' => $uid,
                        'senderNick' => $senderNick,
                        'type' => $tool['category2'],
                        'id' => $tool['id'],
			            'icon' => (string)$tool['icon'],
                        'resource' => $tool['resource'],
			            'gift_name' => $tool['name'],
                        'num' => $qty,
			            'ts' => $tsNow
                    );
                    $result['result'] = 0; // 使用道具或赠送礼物成功!
                    $result['state'] = 2;
                    return array(
                        array(
                            'broadcast' => 1,
                            'data' => $broadcastResult
                        ),
                        array(
                            'broadcast' => 0,
                            'data' => $result
                        )
                    );
                } else {
                    if ($tool['price'] <= 0 && $buy == 1) {
                        $result['result'] = 147; // 活動道具，不可以購買
                        return array(
                            array(
                                'broadcast' => 0,
                                'data' => $result
                            )
                        );
                    }
                }
            }
        }
        
        //LogApi::logProcess('*************************type::'.$params['type']);
        /* //如果类型为18则表示为金币礼物
        if((int)$params['type'] == 18 && $buy !== 0){
        	return ToolApi::sendGoldGift($params, $userAttr, $tool, $result, $qty);
        } */
        
        //类型为15，表示阳光礼物
        if((int)$params['type'] == 15){
            return ToolApi::sendSunGift($params, $userAttr, $tool, &$result, $broadcastResult);
        }
        
        // 確定是否夠錢或包裹有
        if ($buy == ToolModel::SPEND_RCCOIN) {
            // 看用户的秀币是否足够买要求的数量的道具
            $coinNeed = $tool['price'] * $qty;
            if ($userAttr['coin_balance'] < $coinNeed) {
                LogApi::logProcess('用户：'.$uid.'的秀币为:'.$userAttr['coin_balance'].'，礼物价格：'.$tool['price'].'，礼物数量：'.$qty);
                $result['result'] = 101; // 用户秀币不足
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
        } else {
            // 看用户的名下是否有足够数量的道具
            $endTime = $params['endTime'];
            $toolAccountModel = new ToolAccountModel();
            $toolAccountList = $toolAccountModel->getTool($uid, $tid, $endTime);
            $toolAccountQty = $toolAccountModel->hasToolByPacketInfo($toolAccountList, $qty);
            $toolAccountCount = $toolAccountList ? count($toolAccountList) : 0;
            
        	ToolApi::logProcess('ToolApi::useTool uid:' . $uid . ',tid:' . $tid . ',endTime:' . $endTime . ',count:' . count($toolAccountList));
            if (empty($toolAccountQty)) {
                $result['result'] = 102; // 包裹中道具数量不足
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
            if($toolAccountCount > 1 || $toolAccountCount <= 0){
            	$result['result'] = 160; // 包裹中数据异常
                return array(
                    array(
                        'broadcast' => 0,
                        'data' => $result
                    )
                );
            }
            $toolAccountQty = intval($toolAccountQty);
        }
        // 使用道具或赠送礼物
        $charmRate = 1;
        $closeValue = $singerGuardModel->getCloseValue($uid, $singerUid);
        if ($closeValue !== false) {
            $closeLevel = $singerGuardModel->getCloseLevel($closeValue);
            $charmRate = $singerGuardModel->getCharmRate($closeLevel['closeLevel']);
        }
        // 冷門時段魅力加成
        $currentHour = date('G');
        if ($videoOpen && !empty($singerAttr['auth']) && $currentHour > 3 && $currentHour < 16) {
            if ($currentHour < 12) {
                $charmRate += 0.2;
            } else {
                $charmRate += 0.1;
            }
        }
		$tsNow = time();
        $toolConsumRecordModel = new ToolConsumeRecordModel();
        $isNew = $userAttr['gift_consume'] > 0 ? 0 : 1;
        $success = false;
        //4月7日:liuhw add
        //计算送礼物之前的财富等级
        $oldUserAttr = $userAttrModel->getAttrByUid($uid);
        $oldRichManInfo = $userAttrModel->getRichManLevel($uid, $oldUserAttr['gift_consume'], $oldUserAttr['consume_level']);
        //end add

        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        $singerUser = $userInfo->getInfoById($singerUid);
        
        $consumeBegin = timeclock::getMillisecond();

	    $success = $toolConsumRecordModel->consume($uid, $sid, $cid, $tool, $qty, $singerUid, $buy, $charmRate, $user['union_id'], $isNew);
        LogApi::logProcess('ToolApi::useTool end consum. return:'.$success);
        
        $consumeEnd = timeclock::getMillisecond();
        
        $sendGiftBegin = timeclock::getMillisecond();
        if ($success) {
        	$incrSeriaNumBegin = timeclock::getMillisecond();
            $serialNum = $toolModel->IncrAndGetSerialNumber($uid, $sendTime);
            $incrSeriaNumEnd = timeclock::getMillisecond();
            {
                $tid = $tool['id'];
                $category1 = $tool['category1'];
                $category2 = $tool['category2'];
                $price = $tool['price'];
                $number = $num;
                $total_coins_cost = $number * $price;
                $weekTool = $toolModel->GetWeekToolByUid($singerUid);
                if ($tid == $weekTool)
                {
                    $info = new WeekToolConsumeRecordInfo();
                    $info->now = time();
                    $info->uid = $uid;
                    $info->singerUid = $singerUid;
                    $info->tid = $weekTool;// 道具id
                    $info->tool_category1 = $category1;// 道具一级目录
                    $info->tool_category2 = $category2;// 道具二级目录
                    $info->qty = $number;// 数量
                    $info->tool_price = $price;
                    $info->total_coins_cost = $total_coins_cost;
                    $toolConsumRecordModel->AppendWeekToolConsumeRecord($info);
                }
            }            
            $return = array();
            LogApi::logProcess('ToolApi::useTool 开始送礼逻辑, toolid:'.$tool['id'].' 主播id:'.$singerUid);
            //
            {
                $gift_gold = $total_coins_cost;
                // 触发机器人说话的送礼说话流程
                $rtm = new robot_talk_model();
                $rtm->on_user_send_gift_room(&$return,$sid,$singerUser,$user,$tool,$params);
            }
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 6,//送礼
                    'num' => (int)$qty,
                    'extra_param' =>(int)$tool['id']
                )
            );
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$singerUid,
                    'target_type' => 7,//收礼
                    'num' => (int)$qty,
                    'extra_param' =>(int)$tool['id']
                )
            );
            
            /*********送所有礼物***********/
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 44,//送礼
                    'num' => (int)$qty,
                    'extra_param' =>0
                )
            );
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$singerUid,
                    'target_type' => 45,//收礼
                    'num' => (int)$qty,
                    'extra_param' =>0
                )
            );
            /*********end送所有礼物***********/
            
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                    'uid' => (int)$uid,
                    'target_type' => 18,//累计送礼
                    'num' => (int)$total_coins_cost,
                    'extra_param' =>(int)$tool['id']
                )
            );
            /* //去掉粉丝团任务
            if($singerGuardModel->isDiehard($singerUid, $uid)){
                $return[] = array(
                    'broadcast' => 5,
                    'data' => array(
                        'uid' => (int)$uid,
                        'target_type' => 6,//
                        'num' => 1,
                        'extra_param' => (int)$singerUid
                    )
                );
            } */
            /* $taskModel = new TaskModel();
            //主播每日收礼任务
            $taskModel->statRecvGiftDayTask($singerUid, $tool['id'], $qty);
            LogApi::logProcess('ToolApi::useTool ************end statRecvGiftDayTask');
            //主播开启收礼任务
            $taskModel->statSingerOpenTask($singerUid, $tool['id'], $qty);
            LogApi::logProcess('ToolApi::useTool ************end statSingerStartTask');
            //主播收礼主线任务
            $taskModel->statSingerRecvGiftMainTask($singerUid, $tool['id'], $qty);
            LogApi::logProcess('ToolApi::useTool ************end statRecvGiftMainTask');
            //用户每日发送礼物主线任务
            $taskModel->statUserSendGiftMainTask($uid, $qty);
            LogApi::logProcess('ToolApi::useTool ************end statUserSendGiftMainTask'); */
            
        	$giftValue = $qty * $tool['price'];
        	$hotPoint = $qty * $tool['gift_point_hot'];
        	
        	$channelLiveModel = new ChannelLiveModel();
        	//增加热点
        	// 原有使用礼物静态id获取热点的逻辑改为礼物价值获取热点
        	//$channelLiveModel->addHotPoint($singerUid, $hotPoint);
        	$channelLiveModel->giftHotPoint($singerUid,$giftValue);  
        	//增加新星
        	$channelLiveModel->giftNewPoint($singerUid,$giftValue);
        	//增加亲密度
        	$channelLiveModel->addGiftIntimacy($singerUid, $uid);
        	//加周星记录
        	$toolModel->UpdateWeekToolRecord($uid, $singerUid, $tid, $price*$num, $num);
        	
        	LogApi::logProcess('++++++++++++++用户所在帮会id：' . $user['union_id'] . ' 主播所在帮会id：' . $singerUser['union_id']);
        	//送礼用户与主播在同一帮会才给该帮会增加阳光值
        	$isUnionGuard = $channelLiveModel->isUnionGuard($singerUid, $uid);
        	if($isUnionGuard){
        	   $userInfo->updateUnionSunNum($user['union_id'], $singerUid, $giftValue);
        	}
        	//获得主播魅力值
        	$charismaModel = new CharismaModel();
        	
        	$charisma = 0;
        	// 弃用
        	//$charisma = $charismaModel->updateCharisma($sid, $uid, $singerUid, $giftValue, $tsNow);
        	LogApi::logProcess('主播魅力值：' . $charisma . '秀币值：' . $giftValue);
        	$sunCount = $charismaModel->anchorSunshine($singerUid,$giftValue);
        	$Sunshine = $giftValue;
        	//$sunCount = $charismaModel->getAnchorSun($singerUid);
             //更新房间缓存的等级信息
           // file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "tool gift --uid=$uid,sid=$sid\n", FILE_APPEND);
            $userAttrModel->addChannelUserLevelinfoToRedis($sid,$uid);
            //添加结束
            
            //增加活跃度值
//             $userAttrModel->addActivePointBySendGift($uid);
            //判断是否为守护
            $singerGuardModel = new SingerGuardModel();
            $endTime = $singerGuardModel->getGuardEndTime($uid, $singerUid);
            $guardType = $singerGuardModel->getGuardType($uid, $singerUid);
            $now = time();
            $isGuard = 0 ;
            if (!empty($endTime) && $endTime > $now) {
                if(1 == $guardType || 2 == $guardType || 3 == $guardType){
                    //守护有效
                    $isGuard = 1;
                }
            }
            
            // 分两步，第一步更新榜单分值。
            // 礼物勋章过滤官方用户
            if ($uid != 20000000 && $uid != 20015113) {
            	$toolModel->gift_zIncrBy($tid, $uid, $qty);
            }
            // 第二部获取前三榜单用户
            $top3 = $toolModel->getTop3($tid, $uid);
            $honor = 0;
            $index = 0;
            if(!empty($top3)){
                foreach ($top3 as $top){
                    $index++;
                    if($top == $uid){
                        $honor = $index;
                        break;
                    }
                }
            }
                        
            //重新计算财富等级
            $userAttr = $userAttrModel->getAttrByUid($uid);
            $newSingerAttr = $userAttrModel->getAttrByUid($singerUid);
            $richManInfo = $userAttrModel->getRichManLevel($uid, $userAttr['gift_consume'], $userAttr['consume_level']);
            $result['money'] = (int)$userAttr['coin_balance'];
            $result['totalSun'] = (int)$userAttr['sun_num'];
            //计算结束
            
            //消费奖励
            /* $isConRewards = $toolConsumRecordModel->isConsumeRewards($uid, $userAttr['con_incen_dedu'], $giftValue);
            if($isConRewards){
                $rewardinfo = $toolConsumRecordModel->getConsumeRewards($uid, $userAttr['con_incen_dedu']);
                if(!empty($rewardinfo)){
                    $return[] = array(
                        'broadcast' => 0,
                        'data' => array(
                            'cmd' => 'BConsumeAward',
                            'uid' => $uid,
                            'boxid' => $rewardinfo['boxid'],
                            'times' => $rewardinfo['times']
                        )
                    );
                }
            } */
            $consumeAwardBegin = timeclock::getMillisecond();
            /*******$userAttr['con_incen_dedu']每天凌晨清零********/
            $rewardinfo = $toolConsumRecordModel->getConsumeRewards2($uid, $userAttr['con_incen_dedu']);
            if(!empty($rewardinfo)){
                $return[] = array(
                    'broadcast' => 0,
                    'data' => array(
                        'cmd' => 'RConsumeAward',
                        'uid' => $uid,
                        'boxids' => $rewardinfo,
                        'times' => 1
                    )
                );
                
                LogApi::logProcess('****************PSendGift:: 消费奖励宝箱：'.json_encode($return));
            }
            
            /***************/
            $consumeAwardEnd = timeclock::getMillisecond();
            
            $myGuard = $singerGuardModel->getMyGuardList($uid);
            
            $charmValue = floor($qty * $tool['receiver_charm'] * $charmRate);
            //
            $vipLevel = $userInfo->getVipLevel($uid);
            
            LogApi::logProcess('uid：' . $uid . 'oldRichLevel：' . $oldRichManInfo['richManLevel'] . 'newRichLevel：' . $richManInfo['richManLevel']);
				// 4月7日:liuhw add
			$richManBegin = timeclock::getMillisecond();
			if ($richManInfo ['richManLevel'] > $oldRichManInfo ['richManLevel']) {
				/*
				 * $return[] = array(
				 * 'broadcast' => 1,
				 * 'data' => array(
				 * 'cmd' => 'BRichManLevelUp',
				 * 'uid' => $uid,
				 * 'nick' => $senderNick,
				 * 'oldLevel' => $oldRichManInfo['richManLevel'],
				 * 'newLevel' => $richManInfo['richManLevel'],
				 * 'newLevelTitle' => $richManInfo['richManTitle']
				 * )
				 * );
				 */
				do {
					// 更新用户荣耀值
					$model_glory = new GloryModel();
					$model_glory->gloryAdd($uid, $richManInfo['glory'] - $oldRichManInfo['glory']);
					
					$oldLevel = empty ( $oldRichManInfo ['richManLevel'] ) ? 0 : $oldRichManInfo ['richManLevel'];
					$newLevel = empty ( $richManInfo ['richManLevel'] ) ? 0 : $richManInfo ['richManLevel'];
					$key = "richman_levelup_award:$uid";
					$field = "old:$oldLevel" . "new:$newLevel";
					if ($toolModel->AtomEnsure ($key, $field, 1, 180 ) != 1) {
						LogApi::logProcess ( "****************PSendGift::RichManLevelUpAward Fetch Lock Fail. key:$key, field:$field");
						break;
					}
					
					$return [] = array (
							'broadcast' => 4,
							'data' => array (
									'cmd' => 'BBRichManLevelUp',
									'uid' => $uid,
									'nick' => $senderNick,
									'oldLevel' => $oldRichManInfo ['richManLevel'],
									'newLevel' => $richManInfo ['richManLevel'],
									'newLevelTitle' => $richManInfo ['richManTitle'],
									'photo' => $user ['photo'] 
							) 
					);
					
					$dropid = $richManInfo ['boxid'];
					$is_all = $richManInfo ['is_all'];
					$levelData = array ();
					if (empty ( $dropid )) {
						$levelData = $userAttrModel->getUpLevelBoxid ( $oldRichManInfo ['richManLevel'], $richManInfo ['richManLevel'] );
						if (! empty ( $levelData )) {
							$dropid = $levelData ['boxid'];
							$is_all = $levelData ['is_all'];
						}
					}
					$boxid = 0;
					if (! empty ( $dropid )) {
						// 财富升级奖励
						$boxid = $toolConsumRecordModel->getConsumeUpRewards ( $uid, $dropid );
					}
					
					// combat
					$active_info = $userAttrModel->getActiveLevel($userAttr['active_point'], $uid, 0);
					$model_combat = new CombatModel();
					$card_info = $model_combat->getMaxCombatCardInfo($uid);
					//$old_combat_info = $model_combat->getCombatAttrFromCache($uid, $card_info['current_format_type']);
					//if (empty($old_combat_info)) {
					$old_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $active_info, $oldRichManInfo);
					//}
					
					$new_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $active_info, $richManInfo);
					$model_combat->flushCombatAttr2Cache($uid, $card_info['current_format_type'], $new_combat_info);
					
					$return [] = array (
							'broadcast' => 1,
							'data' => array (
									'cmd' => 'BRichManLevelUpAward',
									'uid' => $uid,
									'nick' => $senderNick,
									'oldLevel' => $oldRichManInfo ['richManLevel'],
									'oldLevelTitle' => $oldRichManInfo ['richManTitle'],
									'oldChildLevel' => $oldRichManInfo ['child_level'],
									'newLevel' => $richManInfo ['richManLevel'],
									'newLevelTitle' => $richManInfo ['richManTitle'],
									'newChildLevel' => $richManInfo ['child_level'],
									'richManEffect' => $richManInfo ['richManEffect'],
									'isAll' => ( int ) $is_all,
									'boxid' => $boxid,
									'photo' => $user ['photo'],
									'combatOld' => $old_combat_info,
									'combatNew' => $new_combat_info
							) 
					);
					
					// 迎新任务
					if ($richManInfo ['richManLevel'] == 1) {
						$return[] = array(
								'broadcast' => 5,
								'data' => array(
										'uid' => (int)$uid,
										'target_type' => 50,//迎新财富等级
										'num' => 1,
										'extra_param' => 0
								)
						);
					}
					LogApi::logProcess ( '****************PSendGift:: shi fou sheng ji：' . json_encode ( $return ) );
				} while ( 0 );
			}
			$richManEnd = timeclock::getMillisecond();
            
            //end add
            
            if ($type == 1) {
                // 優化：贈送禮物廣播包
                $broadcastResult['list'][0] = array(
                    'uid' => $uid,
                    'type' => $tool['category2'],
                    'id' => $tool['id'],
                    'sendTime' => $sendTime,
                    'flag' => $flag,
                    'serialNum' => $serialNum,
                    'num' => $qty,
                    'giftValue' => $giftValue,
                    'charmValue' => $charmValue,
                    'isNew' => $isNew,
                    'isGuard' => $isGuard,
                    'guardType' => $guardType,
                    'honor' => $honor, //0：非top3中， 1：第一名 2：第二名 3：第三名
                    'vipLevel' => $vipLevel,
                    'nick' => $senderNick,
                    'vip' => $vipInfo['vip'],
                    'richLevel' => $richManInfo['richManLevel'],
                    'richStart' => $richManInfo['richManStart'],
                    'closeLevel' => empty($closeLevel['closeLevel']) ? 0 : $closeLevel['closeLevel'],
                    'myGuard' => $myGuard,
                    'charisma' => $charisma,
		    		'giftName' => $tool['name'],
		    		'imgUrl' => (string)$tool['icon'],
//                     'sunshine' => $Sunshine,
//                     'sunshineTotal' => $sunCount,
		    		'singerChannelPoint' => $newSingerAttr['channel_point'], //主播对应的秀点
                    'identity' => (int)$user['identity'],
                    'photo' => $user['photo']
                );
                
                LogApi::logProcess('****************PSendGift:: 直播间广播：'.json_encode($broadcastResult));
                
                // 增加主播直播间内秀币统计值
                $money_const = $giftValue;
                $moneyFinal = $charismaModel->AddSingerMoneyCount($singerUid, $money_const);
                $money_nt = array();
                $money_nt['cmd'] = 'BSingerAttrMoneyUpdate';
                
                $money_nt['uid'] = $singerUid;
                $money_nt['sid'] = $sid;
                $money_nt['moneyFinal'] = (int)$moneyFinal;
                $money_nt['moneyDelta'] = (int)$money_const;
                
                $return[] = array
                (
                    'broadcast' => 1, //全直播间
                    'data' => $money_nt,
                );
                // dump money_nt.
                LogApi::logProcess('money_nt:'.json_encode($money_nt));
            } else {
                // 使用道具廣播包
                $broadcastResult['list'][0] = array(
                    'sender' => $uid,
                    'senderNick' => $senderNick,
                    'type' => $tool['category2'],
				    'id' => $tool['id'],
				    'icon' => (string)$tool['icon'],
				    'gift_name' => $tool[name],
				    'resource' => $tool['resource'],
				    'num' => $qty,
				    'ts' => $tsNow
				    );
            }
            $result['result'] = 0; // 使用道具或赠送礼物成功!
            $result['buy'] = $buy;
            if ($tool['consume_type'] == 1) {
                $result['state'] = 2;
            }
            if ($buy == ToolModel::SPEND_PACKET) {
            	// 使用包裹道具成功
            	$toolAccount = $toolAccountList[0];
            	$toolAccount['tool_qty'] = $toolAccountQty - $qty;
            	if($type == 1){
                	$result['giftAccount'] = $toolAccount;
            	}else if($type == 2){
                	$result['toolAccount'] = $toolAccount;
            	}
            }
            $userAttr = $userAttrModel->getAttrByUid($uid);
            $result['coinBalance'] = $userAttr['coin_balance'];
            $result['luckyShakeCount'] = $userAttrModel->getActivity($userAttr);
            
            $return[] = array(
                'broadcast' => 0,
                'data' => $result
            );
            $return[] = array(
                'broadcast' => 1, //全直播间
                'data' => $broadcastResult
            );
            // 寶箱互動道具，當達到一定的累積數據時，會爆出很多小寶箱，用戶打開寶箱會獲得小禮物獎勵，先到先得
            /*
		if ($tool['category1'] == ToolModel::TYPE_GIFT) {
                $receivedCoins = $userAttrModel->statusIncrease($singerUid, 'received_coins', $tool['price'] * $qty);
                $giftBoxModel = new GiftBoxModel();
                $boxType = $giftBoxModel->explodeBox($receivedCoins, $singerUid);
                if ($boxType) {
                    $return[] = array(
                        'broadcast' => 1,
                        'data' => array(
                            'cmd' => 'BGiftBox',
                            'singer' => $singerUid,
                            'receivedCoins' => $receivedCoins,
                            'boxType' => $boxType
                        )
                    );
                }
            }
*/
            LogApi::logProcess('gift_type:' . $tool['category1']);
			$isAllPlatform = false;
            // 全區廣播（超过50块钱即：5000秀币）
			$sys_parameters = new SysParametersModel();
			$pmdPrice = $sys_parameters->GetSysParameters(226, 'parm1');
			if (empty($pmdPrice)) {
				$pmdPrice = 5000;
			}
			
            $pmd = false;
            $totalPrice = $tool['price'] * $qty;
            if($totalPrice >= $pmdPrice){
                $pmd = true;
            }
            //0:单送 1:连送
            /* if($flag){
                $totalPrice = $serialNum * $tool['price'] * $qty;
                if($totalPrice >= 10000){
                    $pmd = true;
                }
            }else{
                if($totalPrice >= 10000){
                    $pmd = true;
                }
            } */
            if ($tool['category1'] == ToolModel::TYPE_GIFT && $pmd) {
                LogApi::logProcess("进入跑马灯广播*********************.");
                
                $honors = array();
                $gifts = $toolModel->getAllTools();
                foreach ($gifts as $g){
                    $top3 = $toolModel->getTop3($g['id'], $uid);
                    $honor = 0;
                    $index = 0;
                    foreach ($top3 as $top){
                        $index++;
                        if($top == $uid){
                            $honor = $index;
                            break;
                        }
                    }
                    if($honor){
                        $item = array();
                        $item['tid'] = $g['id'];
                        $item['img'] = $g['icon'];
                        $item['index'] = $honor;
                        $honors['items'][] = $item;
                    }
                }
                
                LogApi::logProcess("进入跑马灯广播::获得用户对应的礼物勋章：******".json_encode($honors));
                
                $pmd_lvl2_price = $sys_parameters->GetSysParameters(227, 'parm1');
                if (empty($pmd_lvl2_price)) {
                	$pmd_lvl2_price = 99900;
                }
                
                $toollevel = 0;
                if($totalPrice >= $pmd_lvl2_price){
                    $toollevel = 1;
                }
                
                $buffer_object = array(
                    'receiver' => $params['uid_onmic'],
                    'receiverNick' => $params['receiver'],
                    'vip' => $vipInfo['vip'],
                    'richManLevel' => $richManInfo['richManLevel'],
                    'richManTitle' => $richManInfo['richManTitle'],
                    'richManStart' => $richManInfo['richManStart'],
                    'sender' => $uid,
                    'senderNick' => $senderNick,
                    'type' => $tool['category2'],
                    'id' => $tool['id'],
                    'icon' => $tool['icon'],
                    'resource' => $tool['resource'],
                    'gift_name' => $tool['name'],
                    'ts' => $tsNow,
                    'num' => $qty,
                    'honors' => $honors,
                    'toolLevel' => $toollevel,
                    'sid' => $sid
                );
                
                $return[] = array(
                    'broadcast' => 4,
                    'data' => array(
                        'cmd' => 'BBroadcast',
                        'type' => 1,//BBroadcastGift type
                        'gift'=>$buffer_object,
                    )
                );
                $isAllPlatform = true;
            }
            
            LogApi::logProcess("发送礼物******1");
            // 记录全局的送礼信息
            if ($tool['category1'] == ToolModel::TYPE_GIFT) {
            	  $coinCost = $tool['price'] * $qty;
            	  $tsNow = time();
            	  $giftInfo = array(
                            'receiver' => $params['uid_onmic'],
                            'receiverNick' => $params['receiver'],
                            'vip' => $vipInfo['vip'],
                            'richManLevel' => $richManInfo['richManLevel'],
                            'richManTitle' => $richManInfo['richManTitle'],
                            'richManStart' => $richManInfo['richManStart'],
                            'sender' => $uid,
                            'senderNick' => $senderNick,
                            'type' => $tool['category2'],
                            'id' => $tool['id'],
						    'icon' => $tool['icon'],
						    'resource' => $tool['resource'],
						    'gift_name' => $tool['name'],
						    'num' => $qty,
						    'ts' => $tsNow,
                            'sid' => $sid,
                            'coinCost' => $coinCost
			    );
            	$giftPersistDisplayTool = new GiftPersistDisplayTool();
            	
            	// 不再写队列
            	//$giftPersistDisplayTool->addGlobalGiftSendInfo($giftInfo, $coinCost, $tsNow);
            	if($isAllPlatform){
            		$giftPersistDisplayTool->putAllPlatformGiftInfo($giftInfo);
            	}
            }
            LogApi::logProcess("发送礼物******2");
            // 活動排位變化
            /* $activityModel = new ActivityModel();
            $change = $activityModel->getRankChange($uid, $singerUid, $tool, $qty);
            if ($change) {
                $return[] = array(
                    'broadcast' => 1,
                    'data' => $change
                );
            } */
            
            LogApi::logProcess("发送礼物******3");
            
            $singerLevelBegin = timeclock::getMillisecond();
            // 任務累加及歌手等級變化
            if ($tool['category1'] == ToolModel::TYPE_GIFT) {
                //$taskModel = new TaskModel();
                //$taskModel->updateTaskProcess($uid, $singerUid, $tool['id'], $qty);
                if (!empty($singerAttr)) {
                    $currentExpe = $singerAttr['experience'];
                    $newExpe = $newSingerAttr['experience'];//$currentExpe + $tool['receiver_charm'] * $qty;
                    $levelChange = $userAttrModel->getExperienceChange($currentExpe, $newExpe);
                    if ($levelChange) {
                        $levelChange['cmd'] = 'BSingerLevelUp';
                        $levelChange['singerUid'] = $singerUid;
                    	//liuhw add 获得主播变化之前的等级和变化之后的等级
                    	$oldLevelInfo = $userAttrModel->getExperienceLevel($currentExpe);
                    	$newLevelInfo = $userAttrModel->getExperienceLevel($newExpe);
                    	
                    	
                    	$key = "singer_levelup:$singerUid";
                    	$field = "old:" . $oldLevelInfo['singerLevel'] . "new:" . $newLevelInfo['singerLevel'];
                    	if ($toolModel->AtomEnsure ($key, $field, 1, 180 ) != 1) {
                    		LogApi::logProcess ( "ToolApi::useTool::singer_levelup Fetch Lock Fail. key:$key, field:$field");
                    	} else {
                    		$levelConf = $userAttrModel->getAnchorLevelName($newLevelInfo['singerLevel']);
                    		if ($levelConf) {
                    			$levelChange['levelName'] = $levelConf['name'];
                    			$levelChange['subLevel'] = $newLevelInfo['singerLevel'] - $levelConf['levelStart'] + 1;
                    		} else {
                    			$levelChange['levelName'] = "无敌";
                    			$levelChange['subLevel'] = 1;
                    		}
                    		 
                    		$levelChange['oldLevel'] = $oldLevelInfo['singerLevel'];
                    		$levelChange['newLevel'] = $newLevelInfo['singerLevel'];
                    		$levelChange['nick'] = $params['receiver'];
                    		$levelChange['newLevelTitle'] = $newLevelInfo['singerTitle'];
                    		$levelChange['display_id'] = $newLevelInfo['display_id'];
                    		//end add
                    		$return[] = array(
                    				'broadcast' => 1,
                    				'data' => $levelChange
                    		);
                    		
                    		
                    		
                    		// modified by yukl 20170620 迷之信令
                    		/*$levelChange['cmd'] = 'BBSingerLevelUp';
                    		 $return[] = array(
                    		 'broadcast' => 4,
                    		 'data' => $levelChange
                    		 );*/
                    		
                            // 发送消息至粉丝群
                            $summary = "恭喜" . $levelChange['nick'] . "主播等级升为" . $levelChange['levelName'] . $levelChange['subLevel'] . "级，直播更有动力了呢";
                            $text = "<font color='#8ca0c8'>恭喜</font> <font color='#b4b4ff'>" . $levelChange['nick'] . "</font> <font color='#beaa78'>主播等级</font> <font color='#8ca0c8'>升为 " . $levelChange['levelName'] . $levelChange['subLevel'] . "级，直播更有动力了呢！</font>";                            
                            $msg = array(
                                'group_id' => $singerUid,
                                'content' => array(
                                        'type' => 0,
                                        'text' => $summary,
                                        'msgs' => array(
                                            0 => array(
                                                'content' => $text,
                                            )
                                        ),
                                        'summary' => $summary
                                )
                            );
                    		
                    		$tmpKey = "zblevel:$singerUid" . ":" . time();
                    		$userAttrModel->getRedisMaster()->set($tmpKey, json_encode($msg));
                    		
                    		$url = GlobalConfig::GetSendGrpMsgURL() . $tmpKey;
                    		$ch = curl_init();
                    		$curl_opt = array(
                    				CURLOPT_URL => $url,
                    				CURLOPT_RETURNTRANSFER => true,
                    				CURLOPT_TIMEOUT_MS => 1000
                    		);
                    		curl_setopt_array($ch, $curl_opt);
                    		$data = curl_exec($ch);
                    		curl_close($ch);
                    		LogApi::logProcess("ToolApi::useTool***************rediskey:$tmpKey send msg rsp:$data");
                    		
                    		$model_glory = new GloryModel();
                    		$model_glory->anchorCharmAdd($singerUid, $newLevelInfo['charm'] - $oldLevelInfo['charm']);
                    	}                  	                  	
                    }
                }
            }
            $singerLevelEnd = timeclock::getMillisecond();
            LogApi::logProcess("发送礼物******4");
            /*
            // 更新排行榜以及守護親密值
            if ($tool['category1'] == ToolModel::TYPE_GIFT) {
                $rankingModel = new RankingModel();
                $closeValue = $qty * $tool['receiver_charm'];
                $rankingModel->pushToMq($uid, $singerUid, $giftValue, $closeValue, $tool['id']);
                
                LogApi::logProcess("发送礼物******4-1");
                
                // 更新房间内的贡献值排行表
                $isRankChg = false;
                $rankList = $rankingModel->updateSidUserConsumeRank($sid, $uid, $singerUid, $giftValue, $tsNow, $isRankChg);
                if($rankList) {
                  $rankList['cmd'] = 'RGetRankList';
                  $rankList['senderNick'] = $senderNick;
                  $return[] = array(
                        'broadcast' => 1,
                        'data' => $rankList
                  );
                }
                
                LogApi::logProcess("发送礼物******4-2");
                
                if ($isRankChg !== false) {
                    $return[] = array(
                        'broadcast' => 1,
                        'data' => array(
                            'cmd' => 'BBroadcast',
                            'rank' => array(
                                'uid' => $uid,
                                'nick' => $senderNick
                            )
                        )
                    );
                }
                
                LogApi::logProcess("发送礼物******4-3");
				$rankingModel->updatePromotionsRank($singerUid, $tool['id'], $giftValue, $tsNow);
				
				LogApi::logProcess("发送礼物******4-4");
            }
            */
            LogApi::logProcess("发送礼物******5");
            $sendGiftEnd = timeclock::getMillisecond();
            $dt_millisecond = timeclock::getMillisecond() - $now_millisecond;
            $consumeInterval = $consumeEnd - $consumeBegin;
            $consumeAwardInterval = $consumeAwardEnd - $consumeAwardBegin;
            $richManInterval = $richManEnd - $richManBegin;
            $singerLevelInterval = $singerLevelEnd - $singerLevelBegin;
            $incrSeriaNumInterval = $incrSeriaNumEnd - $incrSeriaNumBegin;
            $sendGiftInterval = $sendGiftEnd - $sendGiftBegin;
            $begin2ConsumeInterval = $consumeEnd - $now_millisecond;
            
            LogApi::logProcess("serialNum:$serialNum sendTime:$sendTime uid:$uid recver:$singerUid 送礼花费时间:: $dt_millisecond(ms) 消费时间::$consumeInterval 消费宝箱时间::$consumeAwardInterval 财富等级时间::$richManInterval 主播等级提升时间::$singerLevelInterval incr时间::$incrSeriaNumInterval begine2consume:$begin2ConsumeInterval aftconsume:$sendGiftInterval " . "pid:" . getmypid());
            return $return;
        } else {
            if ($buy == ToolModel::SPEND_RCCOIN) {
                $result['result'] = 110; // 用户RC币不足
            } else {
                $result['result'] = 120; // 包裹中道具数量不足
            }
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
    }
    
    public static function getCharisma($params)
    {
    	/*
    	$result = array(
            'cmd' => 'RGetCharisma',
            'list' => array(),
        );
        */
        $result = array();
        $result['cmd'] = 'RGetCharisma';
        
        $singerUid = 0; // 接收方用户id
        
        LogApi::logProcess('*************cmd: PGetCharisma**********uid:' . $params['uid'] );
        
        if (!empty($params['uid'])) {
            $singerUid = (int)$params['uid'];
            
    		$charismaModel = new CharismaModel();
    		$result['totalCharisma'] = $charismaModel->getCharismaNew($singerUid);
        	$result['result'] = 0;
        }else{
        	$result['result'] = 1;
        	$result['totalCharisma'] = 0;
        }
        
        //BroadType::USER
        
		LogApi::logProcess('*************cmd: PGetCharisma ToolApi::getCharismaNew**********uid:' . $singerUid . ' totalCharisma: ' . $result['totalCharisma'] );
		
        return array(
            array(
                 'broadcast' => 1,
                 'data' => $result
            )
        );
    }

    public static function getShop($params)
    {
        $result = array(
            'cmd' => 'RGetShop',
            'list' => array(),
        );
        $cate1 = 1;
        $toolModel = new ToolModel();
        $toolList = $toolModel->getToolListByCategory($cate1, 'order by id desc');
        foreach ($toolList as $tool) {
            $tmpTool = $toolModel->dataCleanUp($tool, true);
            // 按label来归类和保存
            $result['list'][$tool['label']][] = $tmpTool;
        }
        $toolCategoryModel = new ToolCategoryModel();
        $result['labels'] = $toolCategoryModel->getAllLabel();
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function buyTool($params)
    {
        $returnResult = array(
            'cmd' => 'RBuyTool',
            'result' => 0
        );
        $uid = (int)$params['uid'];
        $tid = (int)$params['id'];
        $qty = (int)$params['num'];
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $vipInfo = $userAttrModel->getVipInfo($userAttr);
        $toolModel = new ToolModel();
        $tool = $toolModel->getToolByTid($tid, $vipInfo['giftDiscount']);
        // 判断道具是否有效
        if (empty($tool) || $tool['closed']) {
            $result['result'] = 100; // 道具不存在或已关闭
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        // 判斷數量
        if ($qty <= 0) {
            $result['result'] = 122; // 數量小於0
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        // 扣秀幣
        $totalCost = $tool['price'] * $qty;
        if (!$userAttrModel->deductCoin($uid, $totalCost)) {
            $returnResult['result'] = 101;
            $return[] = array(
                'broadcast' => 0,
                'data' => $returnResult
            );
            return $return;
        }
        // 判斷道具是按個數還是按時間
        if ($tool['consume_type'] == 1) {
            $toolSubsModel = new ToolSubscriptionModel();
            $toolSubsModel->update($uid, $tid, $qty);
        } else {
            $toolAccoModel = new ToolAccountModel();
            $toolAccoModel->update($uid, $tid, $qty);
        }
        // 寫購買記錄
        $toolBuyRecordModel = new ToolBuyRecordModel();
        $toolBuyRecordModel->addRecord($uid, $tool, $qty);
        // 返回結果
        $returnResult['id'] = $tid;
        $returnResult['num'] = $qty;
        $returnResult['coinBalance'] = $userAttr['coin_balance'] - $totalCost;
        return array(
            array(
                'broadcast' => 0,
                'data' => $returnResult
            )
        );
    }

    public static function getChip($params)
    {
        $returnResult = array(
            'cmd' => 'RGetChip',
            'result' => 0
        );
        $key = 'mooncake';
        $toolMergeModel = new ToolMergeModel();
        $rule = $toolMergeModel->getMergeRule($key);
        $returnResult['rule'] = $rule;
        $toolAccoModel = new ToolAccountModel();
        $uid = $params['uid'];
        $chipList = array();
        foreach ($rule['chips'] as $chip => $num) {
            $rows = $toolAccoModel->getTool($uid, $chip);
            if(!$rows || count($rows) <= 0){
            	continue;
            }
            $row = $rows[0];
            if ($row) {
                $tmpRow = array(
                    'id' => $row['tool_id'],
                    'num' => $row['tool_qty']
                );
            } else {
                $tmpRow = array(
                    'id' => $chip,
                    'num' => 0
                );
            }
            $chipList[] = $tmpRow;
        }
        $returnResult['chipList'] = $chipList;
        $userAttrModel = new UserAttributeModel();
        $returnResult['coinBalance'] = $userAttrModel->getAttrByUid($uid, 'coin_balance');
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function mergeChip($params)
    {
        $returnResult = array(
            'cmd' => 'RMergeChip',
            'result' => 0
        );
        $userAttrModel = new UserAttributeModel();
        $toolMergeModel = new ToolMergeModel();
        $rule = $toolMergeModel->getMergeRule('mooncake');
        $toolAccoModel = new ToolAccountModel();
        $key = 'mooncake';
        $new = $params['id'];
        $uid = $params['uid'];
        $returnResult['result'] = $toolMergeModel->merge($uid, $key, $new);
        $returnResult['coinBalance'] = $userAttrModel->getAttrByUid($uid, 'coin_balance');
        $chipList = array();
        foreach ($rule['chips'] as $chip => $num) {
            $row = $toolAccoModel->getTool($uid, $chip);
            if ($row) {
                $tmpRow = array(
                    'id' => $row['tool_id'],
                    'num' => $row['tool_qty']
                );
                $chipList[] = $tmpRow;
            }
        }
        $returnResult['chipList'] = $chipList;
        $giftList = array();
        foreach ($rule['coins'] as $gift => $num) {
            $rows = $toolAccoModel->getTool($uid, $gift);
            if(!$rows || count($rows) <= 0){
            	continue;
            }
            $row = $rows[0];
            if ($row) {
                $tmpRow = array(
                    'id' => $row['tool_id'],
                    'num' => $row['tool_qty']
                );
                $giftList[] = $tmpRow;
            }
        }
        $returnResult['giftList'] = $giftList;
        //$activityModel = new ActivityModel();
        //$returnResult['mergeListScore'] = $activityModel->getMergeScore($uid);
        return array(
            array(
                'broadcast' => 0,
                'data' => $returnResult
            )
        );
    }

    public static function getStage($params)
    {
        $result['cmd'] = 'RGetStage';
        $result['list'] = array();
        // 按一级分类获取全部开放的道具
        $toolModel = new ToolModel();
        $toolList = $toolModel->getToolListByCategory(ToolModel::TYPE_STAGE);
        foreach ($toolList as $tool) {
            $tmpTool = $toolModel->getResponseInfo($tool);
            $result['list'][] = $tmpTool;
        }
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function setStage($params)
    {
        $params['returnCmd'] = 'RSetStage';
        $params['broadcastCmd'] = 'BStage';
        $uid = $params['uid'];
        $tid = $params['id'];
        $singerUid = $params['uid_onmic'];
        $toolModel = new ToolModel();
        $tool = $toolModel->getToolByTid($tid);
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);


        if ($userAttr['experience'] < $tool['min_charm']) {
            $result['result'] = 104; // 用户表演魅力值不足，无法使用此道具
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        if ($uid != $singerUid) {
            $result['result'] = 111; // 只有表演者才能用表演道具
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        if ($userAttrModel->getStatusByUid($uid, 'stage') == $tid) {
            $result['result'] = 131; // 道具正在使用中
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
	$tsNow = time();
        $userAttrModel->setStatusByUid($uid, 'stage', $tid);
        $broadcastResult['list'][0] = array(
            'sender' => $uid,
            'senderNick' => $params['sender'],
            'id' => $tool['id'],
	    'type' => $tool['category2'],
	    'icon' => $tool[icon],
	    'gift_name' => $tool[name],
	    'resource' => $tool['resource'],
	    'ts' => $tsNow
	    );
        $result['result'] = 0; // 使用道具或赠送礼物成功!
        $result['state'] = 2;
        return array(
            array(
                'broadcast' => 1,
                'data' => $broadcastResult
            ),
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }
	
	public static function getSidConsumeRank($params)
  {
    
    $result = array(
        'cmd' => 'RGetRankList',
        'sid' => (int)$params['sid'],
        'uid' => (int)$params['uid']
    );
    
    $sid = (int)$params['sid'];
    $type = (int)$params['what'];
    $singerUid = (int)$params['uid'];		
    ToolApi::logProcess('ToolApi::getSidConsumeRank entry... sid:' . $sid . ',type:' . $type . ',singerUid:' . $singerUid);
//    $date = 'DataSidUsrConsumeRank:' . $sid . '_' . date('Ymd');
//    $week = 'WeekSidUsrConsumeRank:' . $sid . '_' . date('W');
//    $month = 'MonthSidUsrConsumeRank:' . $sid . '_' . date('Ym');
    $rankModel = new RankingModel();
		
    if(1 == $type)
    {
      //日榜
      //$result['dayRankList'] = $rankModel->getRankList2($date, $singerUid);	
      $result['dayRankList'] = $rankModel->getSidUserConsumeDayRank($sid, $singerUid);
    }
    else if(2 == $type)
    {
      //周榜
      $result['weekRankList'] = $rankModel->getSidUserConsumeWeekRank($sid, $singerUid);
    }
    else if(3 == $type)
    {
      //月榜
      $result['monthRankList'] = $rankModel->getSidUserConsumeMonthRank($sid, $singerUid);
    }
    else if(0 == $type)
    {
      //所有的
      //$result['dayRankList'] = $rankModel->getSidUserConsumeDayRank($sid, $singerUid);
      $result['weekRankList'] = $rankModel->getSidUserConsumeWeekRank($sid, $singerUid);
      //$result['monthRankList'] = $rankModel->getSidUserConsumeMonthRank($sid, $singerUid);
    }

    // 返回结果
    return array(
        array(
            'broadcast' => 0,
            'data' => $result
        )
    );
  }
 
  public static function getGiftDisplayInfo($params)
  {
    ToolApi::logProcess('ToolApi::getGiftDisplayInfo entry...');
    
//    $result = array(
//        'cmd' => 'RGetGiftDisplayInfo',
//        'result' => 0
//    );
    
//    $return[] = array(
//        'broadcast' => 0,
//        'data' => $result
//    );
      
    $num = 10;    
    if (!empty($params['num'])) {
    	$num = (int)$params['num'];
    }
    
    $latestTs = 0;    
    if (!empty($params['latest_ts'])) {
    	$latestTs = (int)$params['latest_ts'];
    }
    
    $lastTs = 0;    
    if (!empty($params['last_ts'])) {
    	$lastTs = (int)$params['last_ts'];
    }
    
    $giftPersistDisplayTool = new GiftPersistDisplayTool();
    $retList = $giftPersistDisplayTool->getGlobalGiftSendInfo($num, $latestTs, $lastTs);

    if(empty($retList)) {
        $return[] = array(
            'broadcast' => 0,
            'bcCount' => 0,
            'giftTs' => 0,
            'data' => array(
                'cmd' => 'RGetGiftDisplayInfo',
                'result' => 1,
            )
        );
        return $return;
    }
    
    foreach ($retList as $retItor => $giftInfo) {
    	  $oneGiftInfo = json_decode($giftInfo, true);
    	  $ts = 0;
    	  $bcCount = 1;
    	  $coinCost = 0;
    	  if(!empty($oneGiftInfo['coinCost'])) {
    	  	$coinCost = (int)$oneGiftInfo['coinCost'];
    	  }
    	  if($coinCost >= 1314) {
    	  	$bcCount = 3;
    	  }  
    	  if(!empty($oneGiftInfo['ts'])) {
    	  	$ts = (int)$oneGiftInfo['ts'];
    	  }
        $return[] = array(
            'broadcast' => 0,
            'bcCount' => $bcCount,
            'giftTs' => $ts,
            'data' => array(
                'cmd' => 'RGetGiftDisplayInfo',
                'result' => 0,
                'gift' => $oneGiftInfo
            )
        );
    }
    
    return $return;  
  }
  public static function getAllPlatformGiftSendInfo($params){
    $num = 3;    
    if (!empty($params['num'])) {
    	$num = (int)$params['num'];
    }
    $giftPersistDisplayTool = new GiftPersistDisplayTool();
    $giftInfo = $giftPersistDisplayTool->getAllPlatformGiftInfo($num);

    if(null == $giftInfo) {
        $return[] = array(
            'broadcast' => 0,
            'bcCount' => 0,
            'giftTs' => 0,
            'data' => array(
                'cmd' => 'RGetAllPlatformGiftSendInfo',
                'result' => 1,
            )
        );
        return $return;
    }
    $return[] = array(
        'broadcast' => 0,
        'data' => array(
            'cmd' => 'RGetAllPlatformGiftSendInfo',
            'result' => 0,
            'gift' => $giftInfo
        )
    );
    return $return;
  }
  public static function reloadRoomRankInfo($params){
	  $return = array();
	  $rankingModel = new RankingModel();
	  $rankingModel->reloadRankListFromDB();

      $return[] = array(
          'broadcast' => 0,
          'data' => array(
              'cmd' => 'RReloadRoomRankInfo',
              'success' => true
          )
      );
	return $return;
  }
  public static function addRoomRankInfo($params){
	  $return = array();
	  $rankingModel = new RankingModel();
	  $rankingModel->addRoomRankInfo();

      $return[] = array(
          'broadcast' => 0,
          'data' => array(
              'cmd' => 'RReloadRoomRankInfo',
              'success' => true
          )
      );
	return $return;
  }
	
	public static function logProcess($info) {
        //return;
	$dir = "/data/vnc_log/vnc/vnc_fpm_script";
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir."/tool.log", date('Y-m-d H:i:s')." $info \n", FILE_APPEND);
  }
  
 public static function sendPropGift($params, $userAttr, $result, $broadcastResult)
    {
        LogApi::logProcess("ToolApi sendPropGift rq:" . json_encode($params));
        $return = array();
        do 
        {
            $userAttrModel = new UserAttributeModel();
            $toolModel = new ToolModel();
            
            $sid = (int)$params['sid'];
            $uid = (int)$params['uid'];
            $singerid = (int)$params['uid_onmic'];  
            $flag = intval($params['flag']);
            $serialNum = $params['serialNum'];            
            $sendTime = $params['sendTime'];
            $nick = $params['sender'];
            
            $id = $params['id'];
            $type = $params['type'];
            if ($id == 24) {
            	$type = 101;
            }
            
            $inf_goods = $toolModel->getGoodsInfo($id);
            if (empty($inf_goods)) {
            	$result['result'] = 100;
            	LogApi::logProcess("ToolApi sendPropGift get goods info failure." );
            	break;
            }
            
            $userInfo = new UserInfoModel();
            $user = $userInfo->getInfoById($uid);
            $userAttr = $userAttrModel->getAttrByUid($uid);
            
            $result['buy'] = 0;
            $result['money'] = (int)$userAttr['coin_balance'];
            $result['totalSun'] = (int)$userAttr['sun_num'];
            $result['coinBalance'] = (int)$userAttr['coin_balance'];
            
            $num = $params['num'];
            if (0 == $num)
            {
                $result['result'] = 0;
                break;
            }
   
            $sql = "UPDATE card.user_goods_info SET num = num - $num WHERE ( uid = $uid && goods_id = $id && num >= $num )";
            $mysql = $toolModel->getDbRecord();
            $rows = $mysql->query($sql);
            
            if(empty($rows) || 0 >= $mysql->affected_rows)
            {
                LogApi::logProcess("ToolApi sendPropGift failure. sql:" . $sql);
                $result['result'] = 122; // 數量小於0
                break;
            }

            // v 星排行
            {
                $model_v_rank = new v_rank_model();
                $model_v_rank->on_recv_gift($id, $type, $num, $uid, $singerid);
            }

            $serialNum = $toolModel->IncrAndGetSerialNumber($uid, $sendTime);
            $broadcastResult['list'][0] = array
            (
                'uid' => $uid,
                'type' => $type,
                'id' => $id,
                'num' => $num,
                'giftValue' => 0,
                'nick' => $nick,
                'giftName' => $inf_goods['goods_name'],
                'flag' => $flag,
                'imgUrl' => $inf_goods['goods_icon'],
                'sendTime' => $sendTime,
                'photo' => $user['photo'],
                'serialNum' => $serialNum,
            	'effect_id' => isset($inf_goods['effect_id']) ? intval($inf_goods['effect_id']):0,
            	'show_time' => isset($inf_goods['show_time']) ? intval($inf_goods['show_time']):0
            );
            
            $return[] = array (
	                'broadcast' => 1, //全直播间
	                'data' => $broadcastResult
            );
            
            $unionId = (int)$user['union_id'];
            
            $toolConsumRecordModel = new ToolConsumeRecordModel();
            
            $total_cost = 0;
            
            // 月饼券
            if ($id == 24) {
            	$total_cost = 10;
            }
            
            $info = new ToolConsumeRecordInfo();
            $info->now = time();
            $info->uid = $uid;
            $info->singerUid = $singerid;
            $info->sid = $sid;// 房间id
            $info->cid = 1;// 频道id
            $info->tid = $id;// 道具id 弹幕为0
            $info->tool_category1 = 0;// 道具一级目录 
            $info->tool_category2 = 0;// 道具二级目录
            $info->qty = $num;// 数量 弹幕为1
            $info->buy = 0;// 是不是直接在商城买的 弹幕为0
            $info->tool_price = 0;
            $info->total_coins_cost = $total_cost;
            $info->total_receiver_points = 0;// 接收这产生的秀点 
            $info->total_receiver_charm = 0;
            $info->total_session_points = 0;// 
            $info->total_session_charm = 0;// 
            $info->baseValue = 0;// 主播基础分成
            $info->prizeValue = 0;// 主播奖金上限(主播奖金预算增加值)
            $info->backValue = 0;// 主播回馈基金分成值
            $info->unionTotalValue = 0;// 公会分成值
            $info->sysControl = 0;// 系统调控基金分成值 
            $info->officialValue = 0;// 官方收入分成值 
            $info->unionId = $unionId;// 公会id
            $info->unionValue = 0;// 公会收入预算-公会收益 
            $info->unionBack = 0;// 公会收入预算-公会回馈基金 
            $info->unionPrize = 0;// 公会收入预算-公会奖金预算 
            $info->unionSunValue = 0;// 公会增加阳光值 
            $info->singerSunValue = 0;// 主播增加阳光值 
            
            $toolConsumRecordModel->AppendToolConsumeRecordInfo($info);
        }while(FALSE);
        
        $return[] = array (
	            'broadcast' => 0,
	            'data' => $result
        );
        LogApi::logProcess("ToolApi sendPropGift rs:".json_encode($return));
        return $return;
    }

    public static function sendPropGiftV3($params, $userAttr, $result, &$broadcastResult)
    {
        LogApi::logProcess("ToolApi sendPropGiftV3 rq:" . json_encode($params));
        $return = array();
        do 
        {
            $userAttrModel = new UserAttributeModel();
            $toolModel = new ToolModel();
            
            $sid = (int)$params['sid'];
            $uid = (int)$params['uid'];
            $singerid = (int)$params['uid_onmic'];  
            $flag = intval($params['flag']);
            $serialNum = $params['serialNum'];            
            $sendTime = $params['sendTime'];
            $nick = $params['sender'];
            
            $id = $params['id'];
            $type = $params['type'];
            if ($id == 24) {
                $type = 101;
            }
            
            $inf_goods = $toolModel->getGoodsInfo($id);
            if (empty($inf_goods)) {
                $result['result'] = 100;
                LogApi::logProcess("ToolApi sendPropGiftV3 get goods info failure." );
                break;
            }
            
            $userInfo = new UserInfoModel();
            $user = $userInfo->getInfoById($uid);
            $userAttr = $userAttrModel->getAttrByUid($uid);
            
            $result['buy'] = 0;
            $result['money'] = (int)$userAttr['coin_balance'];
            $result['totalSun'] = (int)$userAttr['sun_num'];
            $result['coinBalance'] = (int)$userAttr['coin_balance'];
            
            $num = $params['num'];
            if (0 == $num)
            {
                $result['result'] = 0;
                break;
            }
   
            $sql = "UPDATE card.user_goods_info SET num = num - $num WHERE ( uid = $uid && goods_id = $id && num >= $num )";
            $mysql = $toolModel->getDbRecord();
            $rows = $mysql->query($sql);
            
            if(empty($rows) || 0 >= $mysql->affected_rows)
            {
                LogApi::logProcess("ToolApi sendPropGiftV3 failure. sql:" . $sql);
                $result['result'] = 122; // 數量小於0
                break;
            }

            // v 星排行
            {
                $model_v_rank = new v_rank_model();
                $model_v_rank->on_recv_gift($id, $type, $num, $uid, $singerid);
            }

            $serialNum = $toolModel->IncrAndGetSerialNumber($uid, $sendTime);
            $broadcastResult['list'][0] = array
            (
                'uid' => $uid,
                'type' => $type,
                'id' => $id,
                'num' => $num,
                'giftValue' => 0,
                'nick' => $nick,
                'giftName' => $inf_goods['goods_name'],
                'flag' => $flag,
                'imgUrl' => $inf_goods['goods_icon'],
                'sendTime' => $sendTime,
                'photo' => $user['photo'],
                'serialNum' => $serialNum,
                'effect_id' => isset($inf_goods['effect_id']) ? intval($inf_goods['effect_id']):0,
                'show_time' => isset($inf_goods['show_time']) ? intval($inf_goods['show_time']):0
            );
            
//             $return[] = array (
//                     'broadcast' => 1, //全直播间
//                     'data' => $broadcastResult
//             );
            
            $unionId = (int)$user['union_id'];
            
            $toolConsumRecordModel = new ToolConsumeRecordModel();
            
            $total_cost = 0;
            
            // 月饼券
            if ($id == 24) {
                $total_cost = 10;
            }
            
            $info = new ToolConsumeRecordInfo();
            $info->now = time();
            $info->uid = $uid;
            $info->singerUid = $singerid;
            $info->sid = $sid;// 房间id
            $info->cid = 1;// 频道id
            $info->tid = $id;// 道具id 弹幕为0
            $info->tool_category1 = 0;// 道具一级目录 
            $info->tool_category2 = 0;// 道具二级目录
            $info->qty = $num;// 数量 弹幕为1
            $info->buy = 0;// 是不是直接在商城买的 弹幕为0
            $info->tool_price = 0;
            $info->total_coins_cost = $total_cost;
            $info->total_receiver_points = 0;// 接收这产生的秀点 
            $info->total_receiver_charm = 0;
            $info->total_session_points = 0;// 
            $info->total_session_charm = 0;// 
            $info->baseValue = 0;// 主播基础分成
            $info->prizeValue = 0;// 主播奖金上限(主播奖金预算增加值)
            $info->backValue = 0;// 主播回馈基金分成值
            $info->unionTotalValue = 0;// 公会分成值
            $info->sysControl = 0;// 系统调控基金分成值 
            $info->officialValue = 0;// 官方收入分成值 
            $info->unionId = $unionId;// 公会id
            $info->unionValue = 0;// 公会收入预算-公会收益 
            $info->unionBack = 0;// 公会收入预算-公会回馈基金 
            $info->unionPrize = 0;// 公会收入预算-公会奖金预算 
            $info->unionSunValue = 0;// 公会增加阳光值 
            $info->singerSunValue = 0;// 主播增加阳光值 
            
            $toolConsumRecordModel->AppendToolConsumeRecordInfo($info);
        }while(FALSE);
        
        $return[] = array (
                'broadcast' => 0,
                'data' => $result
        );
        LogApi::logProcess("ToolApi sendPropGiftV3 rs:".json_encode($return));
        return $return;
    }
    
    // 使用直播间道具
    public static function useRoomProp($params)
    {
    	LogApi::logProcess("ToolApi::userRoomProp recv:" . json_encode($params));
    	
    	$result = array(
    			'cmd' => 'RUseRoomProp',
    			'uid' => $params['uid'],
    			'sid' => $params['sid'],
    			'singer_id' => $params['singer_id'],
    			'prop_id' => $params['prop_id'],
    			'prop_type' => $params['prop_type'],
    			'prop_num' => $params['prop_num'],
    			'result' => 0
    	);
    	
    	$uid = isset($params['uid'])?$params['uid']:0;
    	$singer_id = isset($params['singer_id'])?$params['singer_id']:0;
    	$sid = isset($params['sid'])?$params['sid']:0;
    	$count = isset($params['prop_num'])?$params['prop_num']:0;
    	$id = isset($params['prop_id'])?$params['prop_id']:0;
    	$type = isset($params['prop_type'])?$params['prop_type']:0;
    	
    	$nt_hot_card = array();
    	$nt_rank_card = array();
        $nt_gift = array();
    	 
    	do {
    		if (empty($uid) || empty($singer_id) || empty($sid) || empty($count) || empty($id) || empty($type)) {
    			// param error
    			$result['result'] = 201;
    			break;
    		}
    		 
    		$model_tool = new ToolModel();
    		$prop_inf = $model_tool->getGoodsInfo($id);
    		
    		if ($prop_inf['ues_type'] != 2) {
    			// can not used in room
    			$result['result'] = 101;
    			break;
    		}

            $model_uinf = new UserInfoModel();
            $u_inf = $model_uinf->getInfoById($uid);
            $s_inf = $model_uinf->getInfoById($singer_id);
    		
    		if ($id == 31) {
    			$result['result'] = $model_tool->useExpActiveDoubleCard($uid, $id, $count, intval($prop_inf['param2']), intval($prop_inf['param1'] * 60));
    		} else if ($type == 5) {
    			$model_sys_conf = new SysParametersModel();
    			$duration = $model_sys_conf->GetSysParameters(235, 'parm1');
    			if (!empty($duration)) {
    				$duration *= 60;
    			} else {
    				$duration = 30 * 60;
    			}
    			$res = $model_tool->useHotCard($uid, $id, $count, intval($prop_inf['param2']), $singer_id, $duration);
    			$result['result'] = $res['code'];
    			
    			if ($res['code'] == 0) {
    				$nt_hot_card['cmd'] = 'hot_value_card_used_nt';
    				$nt_hot_card['uid'] = $uid;
    				$nt_hot_card['sid'] = $sid;
    				$nt_hot_card['singer_id'] = $singer_id;
    				$nt_hot_card['rank_old'] = intval($res['rank_old']);
    				$nt_hot_card['rank_new'] = intval($res['rank_new']);
    				$nt_hot_card['unick'] = $u_inf['nick'];
    				$nt_hot_card['snick'] = $s_inf['nick'];
    				$nt_hot_card['prop_id'] = $id;
    				$nt_hot_card['prop_type'] = $type;
    			}
    		} else if ($type == 23) {
    			$res = $model_tool->use_high_ladder_card($uid, $id, $count, $singer_id, $prop_inf['param2']);
    			$result['result'] = $res['code'];
    			
    			if ($res['code'] == 0) {
    				$nt_rank_card['cmd'] = 'high_ladder_card_used_nt';
    				$nt_rank_card['uid'] = $uid;
    				$nt_rank_card['sid'] = $sid;
    				$nt_rank_card['singer_id'] = $singer_id;
    				$nt_rank_card['b_changed'] = $res['b_changed'];
    				$nt_rank_card['rank'] = $res['rank'];
    				$nt_rank_card['prop_id'] = $id;
    				$nt_rank_card['prop_type'] = $type;
    				$nt_rank_card['unick'] = $u_inf['nick'];
    			}
    		} else if ($type == 7) {  
            // 推荐卡
                $result['result'] = $model_tool->use_recommend_card($uid, $singer_id, $id, $count);
            } else {
    			$result['result'] = 102;
    		}

            if ($result['result'] == 0 && $prop_inf['news_typ'] == 1) {
                $sendTime = time() * 1000;
                $nt_gift['cmd'] = 'BSendGift';
                $nt_gift['receiver'] = $singer_id;
                $nt_gift['receiverNick'] = $s_inf['nick'];
                $nt_gift['list'][0] = array(
                    'uid' => $uid,
                    'type' => $type,
                    'id' => $id,
                    'num' => $count,
                    'giftValue' => 0,
                    'nick' => $u_inf['nick'],
                    'giftName' => $prop_inf['goods_name'],
                    'flag' => 0,
                    'imgUrl' => $prop_inf['goods_icon'],
                    'sendTime' => $sendTime.'',
                    'photo' => $u_inf['photo'],
                    'serialNum' => 1,
                    'effect_id' => isset($prop_inf['effect_id']) ? intval($prop_inf['effect_id']):0,
                    'show_time' => isset($prop_inf['show_time']) ? intval($prop_inf['show_time']):0
                );
            }
    	} while (0);
    	
      	$return[] = array(
      			'broadcast' => 0, 
      			'data' => $result
        );
      	
      	if (!empty($nt_hot_card)) {
      		$return[] = array(
      				'broadcast' => 1,
      				'data' => $nt_hot_card
      		);
      	}
      	
      	if (!empty($nt_rank_card)) {
      		$return[] = array(
      				'broadcast' => 1,
      				'data' => $nt_rank_card
      		);
      	}

        if (!empty($nt_gift)) {
            $return[] = array(
                    'broadcast' => 1,
                    'data' => $nt_gift
            );
        }
    	
    	LogApi::logProcess("ToolApi::userRoomProp send:" . json_encode($return));
    	
    	return $return;
    }
    
    public static function useToolV2($params)
    {
        LogApi::logProcess('ToolApi::useToolV2 rq:' . json_encode($params));
    	$sendTime = empty($params['sendTime']) ? time() : $params['sendTime'];
    	$params['sendTime'] = $sendTime;
    	$serialNum = empty($params['serialNum']) ? 0 : (int)$params['serialNum'];
    	$flag = intval($params['flag']);
    	$prop_id = isset($params['id'])?intval($params['id']):0;
    	$prop_type = isset($params['type'])?intval($params['type']):0;
    	$uid = $params['uid'];
    	
    	$result = array(
    			'cmd' => 'RSendGift',
    			'id' => $params['id'],
    			'uid' => $params['uid'],
    			'sendTime' => $sendTime,
    			'flag' => $flag,
    			'result' => 0
    	);
    	$broadcastResult = array(
    			'cmd' => 'BSendGift',
    			'receiver' => $params['uid_onmic'],
    			'receiverNick' => $params['receiver'],
    			'list' => array()
    	);
    	
    	$return[] = array();
    	$return_ext = array();
    	
    	do {
    		if($uid < 0 || $uid == 10003266 || $uid == 10003260 || $uid == 10003258 || $uid==10000750){
    			$result['result'] = 100; // 道具不存在或已关闭
    			break;
    		}
    		
    		$singerUid = 0; // 接收方用户id
    		if (!empty($params['uid_onmic'])) {
    			$singerUid = (int)$params['uid_onmic'];
    		}
    		 
    		if (empty($singerUid)) {
    			$result['result'] = 103; // 接收方用户id不能为空
    			break;
    		}
    		
    		if ($uid == $singerUid) {
    			$result['result'] = 107; // 不能给自己送礼物
    			break;
    		}
    		 
    		if (!empty($params['num'])) {
    			$params['num'] = intval($params['num']);
    			if ($params['num'] <= 0) {
    				$result['result'] = 122; // 數量小於0
    				break;
    			}
    		} else {
    			$params['num'] = 1;
    		}
    		// TODO: userAttr
    		$model_uattr = new UserAttributeModel();
    		$userAttr = $model_uattr->getAttrByUid($uid);
    		
    		// 道具处理逻辑:帮会票，特效道具，普通道具
    		if($prop_type == ToolApi::$TOOL_GANG_ITEM_TYPE && $prop_id == ToolApi::$TOOL_GANG_ITEM_ID){
    			$return = ToolApi::sendGangGift($params, $userAttr, $result, $broadcastResult);
    			break;
    		} else if ($prop_id == 24 || $prop_type == ToolApi::$PROP_TYPE_NORMAL || $prop_type == ToolApi::$PROP_TYPE_EFFECT) {
    			// 这里其实应该根据type进行道具处理，但由于客户端上行type有问题，所以暂时强制对id进行判断
    			$return = ToolApi::sendPropGift($params, $userAttr, $result, $broadcastResult);
    			break;
    		}
    		
    		$model_tool = new ToolModel();
    		$tool_id = $prop_id;
    		$tool_type = $prop_type;
    		if ($prop_type == 22) {
    			// 获取道具对应的礼物id
    			$prop_inf = $model_tool->getGoodsInfo($prop_id);
    			if (!empty($prop_inf)) {
    				$tool_id = $prop_inf['param1'];
    			}
    			$params['buy'] = 0;

                $params['active_prop'] = $prop_inf['param2'];
    		}
    		 
    		// 根据礼物id获取礼物信息
    		$tool_inf = $model_tool->getToolByTid($tool_id);
    		if (empty($tool_inf) || $tool_inf['closed']) {
    			$result['result'] = 100; // 道具不存在或已关闭
    			LogApi::logProcess('ToolApi::useTool*************** 道具不存在或已关闭');
    			break;
    		}
    		
    		$tool_type = $tool_inf['category2'];
    		
    		//触发机器人说话的送礼说话流程
    		{
    		    if($tool_type == 17)
    		    {
        		    $sid = $params['sid'];//房间号        		    
        		    $model_uinfo = new UserInfoModel();
        		    $user_info = $model_uinfo->getInfoById($uid);//用户信息
        		    $singer_info = $model_uinfo->getInfoById($singerUid);//主播信息
        		    // 触发机器人说话的送礼说话流程
        		    $rtm = new robot_talk_model();
        		    $rtm->on_user_send_gift_room(&$return_ext,$sid,$singer_info,$user_info,$tool_inf,$params);
    		    }    		    
    		}
    		
    		if ($tool_type == 15) {	// 阳光礼物
    			$toolModel = new ToolModel();
    			$tool = $toolModel->getToolByTid($tool_id);
    			$return = ToolApi::sendSunGift($params, $userAttr, $tool, &$result, $broadcastResult);
    			break;
    		} else if ($tool_type == 17 ) {	// 礼物
    			$return = ToolApi::sendGiftNormal($params, $tool_inf, &$result, $broadcastResult);
    			break;
    		} else {  
    			// TODO: return error number
    			// nothing but log
    		}
    	} while (0);
    	
    	if (empty($return)) {
    		$return[] = array(
    				'broadcast' => 0,
    				'data' => $result
    		);
    	}
    	
    	if ($result['result'] == 0) {
    		$return = array_merge($return, $return_ext);
    	}
    	
    	LogApi::logProcess('ToolApi::useToolV2 rs:' . json_encode($return));
    	
    	return $return;
    }
    

   public static function useToolV3($params)
    {
        LogApi::logProcess('ToolApi::useToolV3 rq:' . json_encode($params));  
        
        $sendTime = empty($params['sendTime']) ? time() : $params['sendTime'];
        $params['sendTime'] = $sendTime;
        $serialNum = empty($params['serialNum']) ? 0 : (int)$params['serialNum'];
        $flag = intval($params['flag']);
        $prop_id = isset($params['id'])?intval($params['id']):0;
        $prop_type = isset($params['type'])?intval($params['type']):0;
        $uid = $params['uid'];
        
        $result = array(
                'cmd' => 'RSendGift',
                'id' => $params['id'],
                'uid' => $params['uid'],
                'sendTime' => $sendTime,
                'flag' => $flag,
                'result' => 0
        );
        $broadcastResult = array(
                'cmd' => 'BSendGift',
                'receiver' => $params['uid_onmic'],
                'receiverNick' => $params['receiver'],
                'list' => array()
        );
        
        $return[] = array();
        $return_ext = array();
        

        do {
            if($uid < 0 || $uid == 10003266 || $uid == 10003260 || $uid == 10003258 || $uid==10000750){
                $result['result'] = 100; // 道具不存在或已关闭
                break;
            }
            
            $singerUid = 0; // 接收方用户id
            if (!empty($params['uid_onmic'])) {
                $singerUid = (int)$params['uid_onmic'];
            }
             
            if (empty($singerUid)) {
                $result['result'] = 103; // 接收方用户id不能为空
                break;
            }
            
            if ($uid == $singerUid) {
                $result['result'] = 107; // 不能给自己送礼物
                break;
            }
             
            if (!empty($params['num'])) {
                $params['num'] = intval($params['num']);
                if ($params['num'] <= 0) {
                    $result['result'] = 122; // 數量小於0
                    break;
                }
            } else {
                $params['num'] = 1;
            }

            //增加一个连麦pk判断，如果返回值 $pk_singer_id或者$pk_singer_sid 不是0，说明这个送礼用户正在pk房间送礼，并找出另一个房间 $pk_singer_sid
            $pk_singer_sid = 0;
            $pk_singer_id = 0;
            $pkid = 0;
            {
                //有pkid不代表在pk，有可能在创建pk界面，需要核对里面的PK起止时刻和系统时间
                $linkcallpk_api = new linkcall_pk_model();          
                $linkcallpk_api->linkcallpk_find_pk_singer_by_singerid($singerUid,&$pk_singer_id,&$pk_singer_sid,&$pkid);
                LogApi::logProcess("linkcall_pk_model.linkcallpk_find_pk_singer_by_singerid pk_singer_id:$pk_singer_id pk_singer_sid:$pk_singer_sid pkid:$pkid");
            }
            
            // TODO: userAttr
            $model_uattr = new UserAttributeModel();
            $userAttr = $model_uattr->getAttrByUid($uid);
            
            // 道具处理逻辑:帮会票，特效道具，普通道具
            if($prop_type == ToolApi::$TOOL_GANG_ITEM_TYPE && $prop_id == ToolApi::$TOOL_GANG_ITEM_ID){
                $return = ToolApi::sendGangGiftV3($params, $userAttr, $result, &$broadcastResult);
                break;
            } else if ($prop_id == 24 || $prop_type == ToolApi::$PROP_TYPE_NORMAL || $prop_type == ToolApi::$PROP_TYPE_EFFECT) {
                // 这里其实应该根据type进行道具处理，但由于客户端上行type有问题，所以暂时强制对id进行判断
                $return = ToolApi::sendPropGiftV3($params, $userAttr, $result, &$broadcastResult);
                break;
            }
            
            $model_tool = new ToolModel();
            $tool_id = $prop_id;
            $tool_type = $prop_type;
            if ($prop_type == 22) {
                // 获取道具对应的礼物id
                $prop_inf = $model_tool->getGoodsInfo($prop_id);
                if (!empty($prop_inf)) {
                    $tool_id = $prop_inf['param1'];
                }
                $params['buy'] = 0;
                $params['src'] = 'prop';
                $params['active_prop'] = $prop_inf['param2'];
            }
             
            // 根据礼物id获取礼物信息
            $tool_inf = $model_tool->getToolByTid($tool_id);
            if (empty($tool_inf) || $tool_inf['closed']) {
                $result['result'] = 100; // 道具不存在或已关闭
                LogApi::logProcess('ToolApi::useToolV3*************** 道具不存在或已关闭');
                break;
            }
            
            $tool_type = $tool_inf['category2'];
            
            //触发机器人说话的送礼说话流程
            {
                if($tool_type == 17)
                {
                    $sid = $params['sid'];//房间号                 
                    $model_uinfo = new UserInfoModel();
                    $user_info = $model_uinfo->getInfoById($uid);//用户信息
                    $singer_info = $model_uinfo->getInfoById($singerUid);//主播信息
                    // 触发机器人说话的送礼说话流程
                    $rtm = new robot_talk_model();
                    $rtm->on_user_send_gift_room(&$return_ext,$sid,$singer_info,$user_info,$tool_inf,$params);
                }               
            }
            
            
            if ($tool_type == 15) { // 阳光礼物
                $toolModel = new ToolModel();
                $tool = $toolModel->getToolByTid($tool_id);
                $return = ToolApi::sendSunGiftV3($params, $userAttr, $tool, &$result, &$broadcastResult);
                break;
            } else if ($tool_type == 17 ) { // 礼物
                $return = ToolApi::sendGiftNormalV3($params, $tool_inf, &$result, &$broadcastResult);
                break;
            } else {  
                // TODO: return error number
                // nothing but log
            }
        } while (0);
        
        if (empty($return)) {
            $return[] = array(
                    'broadcast' => 0,
                    'data' => $result
            );
        }
        
        if ($result['result'] == 0) {
            
            //送礼广播回包（'cmd' => 'BSendGift'）增加一个pkid号字段（时间：20180516，工程：连麦pk送礼逻辑跨房间推送新增）
            $broadcastResult['pkid'] = (int)0;

            //如果有连麦pk情况，需要创建一个连麦pk跨房间多播
            if ($pk_singer_sid != 0)
            {
                $broadcastResult['pkid'] = (int)$pkid;
                $m = new cback_channel_model();
                $m->broadcast($pk_singer_sid, $broadcastResult);
                LogApi::logProcess("linkcall_pk_model.sendgift pk_singer_sid:$pk_singer_sid broadcastResult:".json_encode($broadcastResult));
                $linkcallpk_api = new linkcall_pk_api();
                $gift_price = (int)$tool_inf['price'];
                $gift_num   = (int)$params['num'];
                $giftall    =  $gift_price * $gift_num;
                //登记连麦pk礼物信息
                $linkcallpk_api->on_linkcallpk_user_send_gift_event($pkid,$uid,$giftall,$singerUid);
            }
            //送礼正常情况下房间广播
            $return[] = array(
                'broadcast' => 1, //全直播间
                'data' => $broadcastResult
            );
            
            $return = array_merge($return, $return_ext);
        }

        
        LogApi::logProcess('ToolApi::useToolV3 rs:' . json_encode($return));
        
        return $return;
    }

    public static function  sendGiftNormal($params, $tool_inf, &$result, $broadcastResult)
    {
    	$sendTime = $params['sendTime'];
    	$serialNum = empty($params['serialNum']) ? 0 : (int)$params['serialNum'];
    	$prop_id = isset($params['id'])?intval($params['id']):0;
    	$prop_type = isset($params['type'])?intval($params['type']):0;
    	$uid = (int)$params['uid'];
    	$sid = (int)$params['sid'];
    	$cid = (int)$params['cid'];
    	$num = (int)$params['num'];
    	$flag = intval($params['flag']);
    	$videoOpen = !empty($params['videoOpen']) ? $params['videoOpen'] : 0;
    	$senderNick = $params['sender'];

        $b_active_prop = isset($params['active_prop']) ? intval($params['active_prop']) : 0;
    	 
    	$uinfoModel = new UserInfoModel();
    	$uinfo = $uinfoModel->getInfoById($uid);
    	if ($uinfo && !empty($uinfo['nick'])) {
    		$senderNick = $uinfo['nick'];
    		$params['sender'] = $senderNick;
    	}
    	
    	$userAttrModel = new UserAttributeModel();
    	$userAttr = $userAttrModel->getAttrByUid($uid);
    	$vipInfo = $userAttrModel->getVipInfo($userAttr);
    	$userInfo = new UserInfoModel();
    	$user = $userInfo->getInfoById($uid);
    	
    	$tid = (int)$tool_inf['id']; // 道具id    	
    	$price = $tool_inf['price'];
    	
    	// 大礼物不能组送，大礼物价格500及以上
    	if ($price >= 500) {
    		$params['num'] = 1;
    		$num = 1;
    	}
    	$singerUid = 0; // 接收方用户id
    	if (!empty($params['uid_onmic'])) {
    		$singerUid = (int)$params['uid_onmic'];
    		$singerAttr = $userAttrModel->getAttrByUid($singerUid);
    	}
    	
    	$buy = isset($param['buy'])?intval($param['buy']):1;
    	
    	$buy = 1; // 即买即用（送），默认是买
    	if (empty($params['buy'])) {
    		$buy = 0; // 使用包裹里面的道具
    	}
    	
    	// 使用道具或赠送礼物
    	
    	$singerGuardModel = new SingerGuardModel();
    	$charmRate = 1;
    	$closeValue = $singerGuardModel->getCloseValue($uid, $singerUid);
    	if ($closeValue !== false) {
    		$closeLevel = $singerGuardModel->getCloseLevel($closeValue);
    		$charmRate = $singerGuardModel->getCharmRate($closeLevel['closeLevel']);
    	}
    	// 冷門時段魅力加成
    	$currentHour = date('G');
    	if ($videoOpen && !empty($singerAttr['auth']) && $currentHour > 3 && $currentHour < 16) {
    		if ($currentHour < 12) {
    			$charmRate += 0.2;
    		} else {
    			$charmRate += 0.1;
    		}
    	}
    	$tsNow = time();
    	$toolConsumRecordModel = new ToolConsumeRecordModel();
    	$isNew = $userAttr['gift_consume'] > 0 ? 0 : 1;
    	$success = false;
    	//4月7日:liuhw add
    	//计算送礼物之前的财富等级
    	$oldUserAttr = $userAttrModel->getAttrByUid($uid);
    	$oldRichManInfo = $userAttrModel->getRichManLevel($uid, $oldUserAttr['gift_consume'], $oldUserAttr['consume_level']);
    	//end add
       	
    	$success = $toolConsumRecordModel->consume($b_active_prop, $uid, $sid, $cid, $tool_inf, $num, $singerUid, $buy, $charmRate, $uinfo['union_id'], $isNew, $prop_id);
    	LogApi::logProcess('ToolApi::useTool end consum. return:'.$success);
    	
    	if (!$success) {
    		// TODO:
    		if ($buy == ToolModel::SPEND_RCCOIN) {
    			$result['result'] = 110; // 用户RC币不足
    		} else {
    			$result['result'] = 122; // 包裹中道具数量不足
    		}
    		return array(
    				array(
    						'broadcast' => 0,
    						'data' => $result
    				)
    		);
    	}
    	
    	// 主播阳光收益相关
    	{
	    	$model_sunincome_task = new sun_income_task_model();
	    	//$model_sunincome_task->income_gold($singerUid, $price * $num, date('Ym'));
	    	$model_sunincome_task->on_gift_received($singerUid, $price * $num);
    	}
    	

    	$toolModel = new ToolModel();
    	$serialNum = $toolModel->IncrAndGetSerialNumber($uid, $sendTime);
    	{
    		$tid = $tool_inf['id'];
    		$category1 = $tool_inf['category1'];
    		$category2 = $tool_inf['category2'];
    		$price = $tool_inf['price'];
    		$number = $num;
    		$total_coins_cost = $number * $price;
    		$weekTool = $toolModel->GetWeekToolByUid($singerUid);
    		if ($tid == $weekTool)
    		{
    			$info = new WeekToolConsumeRecordInfo();
    			$info->now = time();
    			$info->uid = $uid;
    			$info->singerUid = $singerUid;
    			$info->tid = $weekTool;// 道具id
    			$info->tool_category1 = $category1;// 道具一级目录
    			$info->tool_category2 = $category2;// 道具二级目录
    			$info->qty = $number;// 数量
    			$info->tool_price = $price;
    			$info->total_coins_cost = $total_coins_cost;
    			$toolConsumRecordModel->AppendWeekToolConsumeRecord($info);
    		}
    	}
    	$return = array();
    	$return[] = array(
    			'broadcast' => 5,
    			'data' => array(
    					'uid' => (int)$uid,
    					'target_type' => 6,//送礼
    					'num' => (int)$num,
    					'extra_param' =>(int)$tool_inf['id']
    			)
    	);
    	$return[] = array(
    			'broadcast' => 5,
    			'data' => array(
    					'uid' => (int)$singerUid,
    					'target_type' => 7,//收礼
    					'num' => (int)$num,
    					'extra_param' =>(int)$tool_inf['id']
    			)
    	);
    	
    	/*********送所有礼物***********/
    	$return[] = array(
    			'broadcast' => 5,
    			'data' => array(
    					'uid' => (int)$uid,
    					'target_type' => 44,//送礼
    					'num' => (int)$num,
    					'extra_param' =>0
    			)
    	);
    	$return[] = array(
    			'broadcast' => 5,
    			'data' => array(
    					'uid' => (int)$singerUid,
    					'target_type' => 45,//收礼
    					'num' => (int)$num,
    					'extra_param' =>0
    			)
    	);
    	/*********end送所有礼物***********/
    	
    	$return[] = array(
    			'broadcast' => 5,
    			'data' => array(
    					'uid' => (int)$uid,
    					'target_type' => 18,//累计送礼
    					'num' => (int)$total_coins_cost,
    					'extra_param' =>(int)$tool_inf['id']
    			)
    	);
    	
    	$giftValue = $num * $tool_inf['price'];
    	$hotPoint = $num * $tool_inf['gift_point_hot'];
    	 
    	$channelLiveModel = new ChannelLiveModel();
    	//增加热点
    	// 原有使用礼物静态id获取热点的逻辑改为礼物价值获取热点
    	//$channelLiveModel->addHotPoint($singerUid, $hotPoint);
    	$channelLiveModel->giftHotPoint($singerUid,$giftValue);
    	
    	//增加新星
    	$channelLiveModel->giftNewPoint($singerUid,$giftValue);
    	
    	//增加亲密度
    	$channelLiveModel->addGiftIntimacy($singerUid, $uid);
    	
    	//加周星记录
    	$toolModel->UpdateWeekToolRecord($uid, $singerUid, $tid, $price*$num, $num);
    	 
    	//送礼用户与主播在同一帮会才给该帮会增加阳光值
        if ($b_active_prop == 0) {
        	$isUnionGuard = $channelLiveModel->isUnionGuard($singerUid, $uid);
        	if($isUnionGuard){
        		$userInfo->updateUnionSunNum($user['union_id'], $singerUid, $giftValue);
        	}
        }
    	
    	//获得主播魅力值
    	$charismaModel = new CharismaModel();
    	$charisma = 0;
    	$sunCount = $charismaModel->anchorSunshine($singerUid,$giftValue);
    	$Sunshine = $giftValue;
    	$userAttrModel->addChannelUserLevelinfoToRedis($sid,$uid);
    	//添加结束
    	
    	//判断是否为守护
    	$singerGuardModel = new SingerGuardModel();
    	$endTime = $singerGuardModel->getGuardEndTime($uid, $singerUid);
    	$guardType = $singerGuardModel->getGuardType($uid, $singerUid);
    	$now = time();
    	$isGuard = 0 ;
    	if (!empty($endTime) && $endTime > $now) {
    		if(1 == $guardType || 2 == $guardType || 3 == $guardType){
    			//守护有效
    			$isGuard = 1;
    		}
    	}
    	
    	// 分两步，第一步更新榜单分值。
    	// 礼物勋章过滤官方用户
    	if ($uid != 20000000 && $uid != 20015113) {
    		$toolModel->gift_zIncrBy($tid, $uid, $num);
    	}
    	
    	// 第二部获取前三榜单用户
    	$top3 = $toolModel->getTop3($tid, $uid);
    	$honor = 0;
    	$index = 0;
    	if(!empty($top3)){
    		foreach ($top3 as $top){
    			$index++;
    			if($top == $uid){
    				$honor = $index;
    				break;
    			}
    		}
    	}
    	
    	//重新计算财富等级
    	$userAttr = $userAttrModel->getAttrByUid($uid);
    	$newSingerAttr = $userAttrModel->getAttrByUid($singerUid);
    	$richManInfo = $userAttrModel->getRichManLevel($uid, $userAttr['gift_consume'], $userAttr['consume_level']);
    	$result['money'] = (int)$userAttr['coin_balance'];
    	$result['totalSun'] = (int)$userAttr['sun_num'];

    	$consumeAwardBegin = timeclock::getMillisecond();
    	/*******$userAttr['con_incen_dedu']每天凌晨清零********/
        $model_user_gold = new user_gold_model();
        $total_con_incen =  $model_user_gold->con_incen_dedu_add($uid, $price * $num);
    	$rewardinfo = $toolConsumRecordModel->getConsumeRewards2($uid, $total_con_incen);
    	if(!empty($rewardinfo)){
    		$return[] = array(
    				'broadcast' => 0,
    				'data' => array(
    						'cmd' => 'RConsumeAward',
    						'uid' => $uid,
    						'boxids' => $rewardinfo,
    						'times' => 1
    				)
    		);    	
    	}
    	
    	/***************/
    	
    	$myGuard = $singerGuardModel->getMyGuardList($uid);
    	
    	$charmValue = floor($num * $tool_inf['receiver_charm'] * $charmRate);
    	//
    	$vipLevel = $userInfo->getVipLevel($uid);
    	
    	LogApi::logProcess('uid：' . $uid . 'oldRichLevel：' . $oldRichManInfo['richManLevel'] . 'newRichLevel：' . $richManInfo['richManLevel']);
    	// 4月7日:liuhw add
    	$richManBegin = timeclock::getMillisecond();
    	if ($richManInfo ['richManLevel'] > $oldRichManInfo ['richManLevel']) {
    		do {
    			// 更新用户荣耀值
    			$model_glory = new GloryModel();
    			$model_glory->gloryAdd($uid, $richManInfo['glory'] - $oldRichManInfo['glory']);
    				
    			$oldLevel = empty ( $oldRichManInfo ['richManLevel'] ) ? 0 : $oldRichManInfo ['richManLevel'];
    			$newLevel = empty ( $richManInfo ['richManLevel'] ) ? 0 : $richManInfo ['richManLevel'];
    			$key = "richman_levelup_award:$uid";
    			$field = "old:$oldLevel" . "new:$newLevel";
    			if ($toolModel->AtomEnsure ($key, $field, 1, 180 ) != 1) {
    				LogApi::logProcess ( "****************PSendGift::RichManLevelUpAward Fetch Lock Fail. key:$key, field:$field");
    				break;
    			}
    				
    			$return [] = array (
    					'broadcast' => 4,
    					'data' => array (
    							'cmd' => 'BBRichManLevelUp',
    							'uid' => $uid,
    							'nick' => $senderNick,
    							'oldLevel' => $oldRichManInfo ['richManLevel'],
    							'newLevel' => $richManInfo ['richManLevel'],
    							'newLevelTitle' => $richManInfo ['richManTitle'],
    							'photo' => $user ['photo']
    					)
    			);
    				
    			$dropid = $richManInfo ['boxid'];
    			$is_all = $richManInfo ['is_all'];
    			$levelData = array ();
    			if (empty ( $dropid )) {
    				$levelData = $userAttrModel->getUpLevelBoxid ( $oldRichManInfo ['richManLevel'], $richManInfo ['richManLevel'] );
    				if (! empty ( $levelData )) {
    					$dropid = $levelData ['boxid'];
    					$is_all = $levelData ['is_all'];
    				}
    			}
    			$boxid = 0;
    			if (! empty ( $dropid )) {
    				// 财富升级奖励
    				$boxid = $toolConsumRecordModel->getConsumeUpRewards ( $uid, $dropid );
    			}
    				
    			// combat
    			$active_info = $userAttrModel->getActiveLevel($userAttr['active_point'], $uid, 0);
    			$model_combat = new CombatModel();
    			$card_info = $model_combat->getMaxCombatCardInfo($uid);
    			//$old_combat_info = $model_combat->getCombatAttrFromCache($uid, $card_info['current_format_type']);
    			//if (empty($old_combat_info)) {
    			$old_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $active_info, $oldRichManInfo);
    			//}
    				
    			$new_combat_info = $model_combat->calcUserCombatAttr($uid, $card_info, $active_info, $richManInfo);
    			$model_combat->flushCombatAttr2Cache($uid, $card_info['current_format_type'], $new_combat_info);
    				
    			$return [] = array (
    					'broadcast' => 1,
    					'data' => array (
    							'cmd' => 'BRichManLevelUpAward',
    							'uid' => $uid,
    							'nick' => $senderNick,
    							'oldLevel' => $oldRichManInfo ['richManLevel'],
    							'oldLevelTitle' => $oldRichManInfo ['richManTitle'],
    							'oldChildLevel' => $oldRichManInfo ['child_level'],
    							'newLevel' => $richManInfo ['richManLevel'],
    							'newLevelTitle' => $richManInfo ['richManTitle'],
    							'newChildLevel' => $richManInfo ['child_level'],
    							'richManEffect' => $richManInfo ['richManEffect'],
    							'isAll' => ( int ) $is_all,
    							'boxid' => $boxid,
    							'photo' => $user ['photo'],
    							'combatOld' => $old_combat_info,
    							'combatNew' => $new_combat_info
    					)
    			);
    				
    			// 迎新任务
    			if ($richManInfo ['richManLevel'] >= 1) {
    				$return[] = array(
    						'broadcast' => 5,
    						'data' => array(
    								'uid' => (int)$uid,
    								'target_type' => 50,//迎新财富等级
    								'num' => 1,
    								'extra_param' => 0
    						)
    				);
    			}
    			LogApi::logProcess ( '****************PSendGift:: shi fou sheng ji：' . json_encode ( $return ) );
    		} while ( 0 );
    	}
    	
    	$broadcastResult['list'][0] = array(
    			'uid' => $uid,
    				'type' => $tool_inf['category2'],
    				'id' => $tool_inf['id'],
    				'sendTime' => $sendTime,
    				'flag' => $flag,
    				'serialNum' => $serialNum,
    				'num' => $num,
    				'giftValue' => $giftValue,
    				'charmValue' => $charmValue,
    				'isNew' => $isNew,
    				'isGuard' => $isGuard,
    				'guardType' => $guardType,
    				'honor' => $honor, //0：非top3中， 1：第一名 2：第二名 3：第三名
    				'vipLevel' => $vipLevel,
    				'nick' => $senderNick,
    				'vip' => $vipInfo['vip'],
    				'richLevel' => $richManInfo['richManLevel'],
    				'richStart' => $richManInfo['richManStart'],
    				'closeLevel' => empty($closeLevel['closeLevel']) ? 0 : $closeLevel['closeLevel'],
    				'myGuard' => $myGuard,
    				'charisma' => $charisma,
    				'giftName' => $tool_inf['name'],
    				'imgUrl' => (string)$tool_inf['icon'],
    				//                     'sunshine' => $Sunshine,
    				//                     'sunshineTotal' => $sunCount,
    				'singerChannelPoint' => $newSingerAttr['channel_point'], //主播对应的秀点
    				'identity' => (int)$user['identity'],
    				'photo' => $user['photo'],
    				'effect_id' => isset($tool_inf['effect_id']) ? intval($tool_inf['effect_id']):0,
    				'show_time' => isset($tool_inf['show_time']) ? intval($tool_inf['show_time']):0
    	);
    	
    	
    	// 增加主播直播间内秀币统计值
    	$money_const = $giftValue;
    	$moneyFinal = $charismaModel->AddSingerMoneyCount($singerUid, $money_const);
    	$money_nt = array();
    	$money_nt['cmd'] = 'BSingerAttrMoneyUpdate';
    	$money_nt['uid'] = $singerUid;
    	$money_nt['sid'] = $sid;
    	$money_nt['moneyFinal'] = (int)$moneyFinal;
    	$money_nt['moneyDelta'] = (int)$money_const;
    	
    	$return[] = array (
    				'broadcast' => 1, //全直播间
    				'data' => $money_nt,
    	);
    		
    	$result['result'] = 0; // 使用道具或赠送礼物成功!
    	$result['buy'] = $buy;
    	if ($tool_inf['consume_type'] == 1) {
    		$result['state'] = 2;
    	}

    	$userAttr = $userAttrModel->getAttrByUid($uid);
    	$result['coinBalance'] = $userAttr['coin_balance'];
    	$result['luckyShakeCount'] = $userAttrModel->getActivity($userAttr);
    	
    	$return[] = array(
    			'broadcast' => 0,
    			'data' => $result
    	);
    	LogApi::logProcess('gift_type:' . $tool_inf['category1']);
    	$isAllPlatform = false;
    	// 全區廣播（超过50块钱即：5000秀币）
    	$sys_parameters = new SysParametersModel();
    	$pmdPrice = $sys_parameters->GetSysParameters(226, 'parm1');
    	if (empty($pmdPrice)) {
    		$pmdPrice = 5000;
    	}
    	 
    	$pmd = false;
    	$totalPrice = $tool_inf['price'] * $num;


        //单次金币价值大于触发条件，每次都出现
        if($totalPrice >= $pmdPrice){
          $pmd = true;
        }
        //当单次阳光不满足触发条件，但是连送情况下累计可以触发
        if($totalPrice < $pmdPrice)
        {
            $tiantou=( 0 == $pmdPrice % $totalPrice) ? 0 : 1;//判断次数是否可以整除，如果可以，添头为0，如果不可以，添头为1
            if( 0 ==$serialNum %( $pmdPrice/$totalPrice + $tiantou ) ){
                $pmd = true;
            }
        }

    	if ($pmd) {    	
    		$honors = array();
    		$gifts = $toolModel->getAllTools();
    		foreach ($gifts as $g){
    			$top3 = $toolModel->getTop3($g['id'], $uid);
    			$honor = 0;
    			$index = 0;
    			foreach ($top3 as $top){
    				$index++;
    				if($top == $uid){
    					$honor = $index;
    					break;
    				}
    			}
    			if($honor){
    				$item = array();
    				$item['tid'] = $g['id'];
    				$item['img'] = $g['icon'];
    				$item['index'] = $honor;
    				$honors['items'][] = $item;
    			}
    		}
    		
    		$pmd_lvl2_price = $sys_parameters->GetSysParameters(227, 'parm1');
    		if (empty($pmd_lvl2_price)) {
    			$pmd_lvl2_price = 99900;
    		}
    	
    		$toollevel = 0;            
    		//if($totalPrice >= $pmd_lvl2_price){
    		//	$toollevel = 1;
    		//}
            //连送情况下，连送累计金额大于2级跑马灯下限金额
            if( ($totalPrice * $serialNum ) >= $pmd_lvl2_price){
              $toollevel = 1;
            }           
    	
    		$buffer_object = array(
    				'receiver' => $params['uid_onmic'],
    				'receiverNick' => $params['receiver'],
    				'vip' => $vipInfo['vip'],
    				'richManLevel' => $richManInfo['richManLevel'],
    				'richManTitle' => $richManInfo['richManTitle'],
    				'richManStart' => $richManInfo['richManStart'],
    				'sender' => $uid,
    				'senderNick' => $senderNick,
    				'type' => $tool_inf['category2'],
    				'id' => $tool_inf['id'],
    				'icon' => $tool_inf['icon'],
    				'resource' => $tool_inf['resource'],
    				'gift_name' => $tool_inf['name'],
    				'ts' => $tsNow,
    				'num' => $num,
    				'honors' => $honors,
    				'toolLevel' => $toollevel,
    				'sid' => $sid,
                    'serialNum'=>$serialNum
    		);
    	
    		$return[] = array(
    				'broadcast' => 4,
    				'data' => array(
    						'cmd' => 'BBroadcast',
    						'type' => 1,//BBroadcastGift type
    						'gift'=>$buffer_object,
    				)
    		);
    		$isAllPlatform = true;

            // 上跑马灯任务
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                        'uid' => (int)$uid,
                        'target_type' => 57, //上跑马灯
                        'num' => 1,
                        'extra_param' =>0
                )
            );
    	}
    	
    	{
    		// 组送特效
    		$broadcastResult['list'][0]['group_effect'] = 0;
    		if ($num > 1) {
    			if ($pmd) {
    				$broadcastResult['list'][0]['group_effect'] = 20096;
    			} else {
    				$broadcastResult['list'][0]['group_effect'] = 20095;
    			}
    		}
    	}
    	
    	$return[] = array(
    			'broadcast' => 1, //全直播间
    			'data' => $broadcastResult
    	);
    	
    	// 任務累加及歌手等級變化
    	if (!empty($singerAttr)) {
    		
    		$currentExpe = $singerAttr['experience'];
    		$newExpe = $newSingerAttr['experience'];//$currentExpe + $tool['receiver_charm'] * $qty;
    		$levelChange = $userAttrModel->getExperienceChange($singerAttr['experience_level'], $newSingerAttr['experience_level']);
    		if ($levelChange) {
    			$levelChange['cmd'] = 'BSingerLevelUp';
    			$levelChange['singerUid'] = $singerUid;
    			//liuhw add 获得主播变化之前的等级和变化之后的等级
    			$oldLevelInfo = $userAttrModel->getExperienceLevel($singerAttr['experience_level']);
    			$newLevelInfo = $userAttrModel->getExperienceLevel($newSingerAttr['experience_level']);
    	
    	
    			$key = "singer_levelup:$singerUid";
    			$field = "old:" . $oldLevelInfo['singerLevel'] . "new:" . $newLevelInfo['singerLevel'];
    			if ($toolModel->AtomEnsure ($key, $field, 1, 180 ) != 1) {
    				LogApi::logProcess ( "ToolApi::useTool::singer_levelup Fetch Lock Fail. key:$key, field:$field");
    			} else {
    				$levelConf = $userAttrModel->getAnchorLevelName($newLevelInfo['singerLevel']);
    				if ($levelConf) {
    					$levelChange['levelName'] = $levelConf['name'];
    					$levelChange['subLevel'] = $newLevelInfo['singerLevel'] - $levelConf['levelStart'] + 1;
    				} else {
    					$levelChange['levelName'] = "无敌";
    					$levelChange['subLevel'] = 1;
    				}
    					
    				$levelChange['oldLevel'] = $oldLevelInfo['singerLevel'];
    				$levelChange['newLevel'] = $newLevelInfo['singerLevel'];
    				$levelChange['nick'] = $params['receiver'];
    				$levelChange['newLevelTitle'] = $newLevelInfo['singerTitle'];
    				$levelChange['display_id'] = $newLevelInfo['display_id'];
    				//end add
    				$return[] = array(
    						'broadcast' => 1,
    						'data' => $levelChange
    				);
    	
    	
    	
    				// modified by yukl 20170620 迷之信令
    				/*$levelChange['cmd'] = 'BBSingerLevelUp';
    				 $return[] = array(
    				 'broadcast' => 4,
    				 'data' => $levelChange
    				 );*/
    	
    				// 发送消息至粉丝群
    				$summary = "恭喜" . $levelChange['nick'] . "主播等级升为" . $levelChange['levelName'] . $levelChange['subLevel'] . "级，直播更有动力了呢";
    				$text = "<font color='#8ca0c8'>恭喜</font> <font color='#b4b4ff'>" . $levelChange['nick'] . "</font> <font color='#beaa78'>主播等级</font> <font color='#8ca0c8'>升为 " . $levelChange['levelName'] . $levelChange['subLevel'] . "级，直播更有动力了呢！</font>";
    				$msg = array(
    						'group_id' => $singerUid,
    						'content' => array(
    								'type' => 0,
    								'text' => $summary,
    								'msgs' => array(
    										0 => array(
    												'content' => $text,
    										)
    								),
    								'summary' => $summary
    						)
    				);
    	
    				$tmpKey = "zblevel:$singerUid" . ":" . time();
    				$userAttrModel->getRedisMaster()->set($tmpKey, json_encode($msg));
    	
    				$url = GlobalConfig::GetSendGrpMsgURL() . $tmpKey;
    				$ch = curl_init();
    				$curl_opt = array(
    						CURLOPT_URL => $url,
    						CURLOPT_RETURNTRANSFER => true,
    						CURLOPT_TIMEOUT_MS => 1000
    				);
    				curl_setopt_array($ch, $curl_opt);
    				$data = curl_exec($ch);
    				curl_close($ch);
    				LogApi::logProcess("ToolApi::useTool***************rediskey:$tmpKey send msg rsp:$data");
    	
    				$model_glory = new GloryModel();
    				$model_glory->anchorCharmAdd($singerUid, $newLevelInfo['charm'] - $oldLevelInfo['charm']);
    			}
    		}
    	}
    	return $return;
    }


    public static function  sendGiftNormalV3($params, $tool_inf, &$result, &$broadcastResult)
    {
        $sendTime = $params['sendTime'];
        $serialNum = empty($params['serialNum']) ? 0 : (int)$params['serialNum'];
        $prop_id = isset($params['id'])?intval($params['id']):0;
        $prop_type = isset($params['type'])?intval($params['type']):0;
        $uid = (int)$params['uid'];
        $sid = (int)$params['sid'];
        $cid = (int)$params['cid'];
        $num = (int)$params['num'];
        $flag = intval($params['flag']);
        $videoOpen = !empty($params['videoOpen']) ? $params['videoOpen'] : 0;
        $senderNick = $params['sender'];
        
        $b_active_prop = isset($params['active_prop']) ? intval($params['active_prop']) : 0;

        $uinfoModel = new UserInfoModel();
        $uinfo = $uinfoModel->getInfoById($uid);
        if ($uinfo && !empty($uinfo['nick'])) {
            $senderNick = $uinfo['nick'];
            $params['sender'] = $senderNick;
        }
        
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $vipInfo = $userAttrModel->getVipInfo($userAttr);
        $userInfo = new UserInfoModel();
        $user = $userInfo->getInfoById($uid);
        
        $tid = (int)$tool_inf['id']; // 道具id        
        $price = $tool_inf['price'];
        
        // 大礼物不能组送，大礼物价格500及以上
        if ($price >= 500) {
            $params['num'] = 1;
            $num = 1;
        }
        $singerUid = 0; // 接收方用户id
        if (!empty($params['uid_onmic'])) {
            $singerUid = (int)$params['uid_onmic'];
            $singerAttr = $userAttrModel->getAttrByUid($singerUid);
        }
        
        $buy = isset($param['buy'])?intval($param['buy']):1;
        
        $buy = 1; // 即买即用（送），默认是买
        if (empty($params['buy'])) {
            $buy = 0; // 使用包裹里面的道具
        }
        
        // 使用道具或赠送礼物
        
        $singerGuardModel = new SingerGuardModel();
        $charmRate = 1;
        $closeValue = $singerGuardModel->getCloseValue($uid, $singerUid);
        if ($closeValue !== false) {
            $closeLevel = $singerGuardModel->getCloseLevel($closeValue);
            $charmRate = $singerGuardModel->getCharmRate($closeLevel['closeLevel']);
        }
        // 冷門時段魅力加成
        $currentHour = date('G');
        if ($videoOpen && !empty($singerAttr['auth']) && $currentHour > 3 && $currentHour < 16) {
            if ($currentHour < 12) {
                $charmRate += 0.2;
            } else {
                $charmRate += 0.1;
            }
        }
        $tsNow = time();
        $toolConsumRecordModel = new ToolConsumeRecordModel();
        $isNew = $userAttr['gift_consume'] > 0 ? 0 : 1;
        $success = false;
        //4月7日:liuhw add
        //计算送礼物之前的财富等级
        $oldUserAttr = $userAttrModel->getAttrByUid($uid);
        $oldRichManInfo = $userAttrModel->getRichManLevel($uid, $oldUserAttr['gift_consume'], $oldUserAttr['consume_level']);
        //end add
        
        $success = $toolConsumRecordModel->consume($b_active_prop, $uid, $sid, $cid, $tool_inf, $num, $singerUid, $buy, $charmRate, $uinfo['union_id'], $isNew, $prop_id);
        LogApi::logProcess('ToolApi::useTool end consum. return:'.$success);
        
        if (!$success) {
            // TODO:
            if ($buy == ToolModel::SPEND_RCCOIN) {
                $result['result'] = 110; // 用户RC币不足
            } else {
                $result['result'] = 122; // 包裹中道具数量不足
            }
            return array(
                    array(
                            'broadcast' => 0,
                            'data' => $result
                    )
            );
        }
        
        // 主播阳光收益相关
        {
            $model_sunincome_task = new sun_income_task_model();
            //$model_sunincome_task->income_gold($singerUid, $price * $num, date('Ym'));
            $model_sunincome_task->on_gift_received($singerUid, $price * $num);
        }
        

        $toolModel = new ToolModel();
        $serialNum = $toolModel->IncrAndGetSerialNumber($uid, $sendTime);
        {
            $tid = $tool_inf['id'];
            $category1 = $tool_inf['category1'];
            $category2 = $tool_inf['category2'];
            $price = $tool_inf['price'];
            $number = $num;
            $total_coins_cost = $number * $price;
            $weekTool = $toolModel->GetWeekToolByUid($singerUid);
            if ($tid == $weekTool)
            {
                $info = new WeekToolConsumeRecordInfo();
                $info->now = time();
                $info->uid = $uid;
                $info->singerUid = $singerUid;
                $info->tid = $weekTool;// 道具id
                $info->tool_category1 = $category1;// 道具一级目录
                $info->tool_category2 = $category2;// 道具二级目录
                $info->qty = $number;// 数量
                $info->tool_price = $price;
                $info->total_coins_cost = $total_coins_cost;
                $toolConsumRecordModel->AppendWeekToolConsumeRecord($info);
            }
        }
        $return = array();
        $return[] = array(
                'broadcast' => 5,
                'data' => array(
                        'uid' => (int)$uid,
                        'target_type' => 6,//送礼
                        'num' => (int)$num,
                        'extra_param' =>(int)$tool_inf['id']
                )
        );
        $return[] = array(
                'broadcast' => 5,
                'data' => array(
                        'uid' => (int)$singerUid,
                        'target_type' => 7,//收礼
                        'num' => (int)$num,
                        'extra_param' =>(int)$tool_inf['id']
                )
        );
        
        /*********送所有礼物***********/
        $return[] = array(
                'broadcast' => 5,
                'data' => array(
                        'uid' => (int)$uid,
                        'target_type' => 44,//送礼
                        'num' => (int)$num,
                        'extra_param' =>0
                )
        );
        $return[] = array(
                'broadcast' => 5,
                'data' => array(
                        'uid' => (int)$singerUid,
                        'target_type' => 45,//收礼
                        'num' => (int)$num,
                        'extra_param' =>0
                )
        );
        /*********end送所有礼物***********/
        
        $return[] = array(
                'broadcast' => 5,
                'data' => array(
                        'uid' => (int)$uid,
                        'target_type' => 18,//累计送礼
                        'num' => (int)$total_coins_cost,
                        'extra_param' =>(int)$tool_inf['id']
                )
        );
        
        $giftValue = $num * $tool_inf['price'];
        $hotPoint = $num * $tool_inf['gift_point_hot'];
         
        $channelLiveModel = new ChannelLiveModel();
        //增加热点
        // 原有使用礼物静态id获取热点的逻辑改为礼物价值获取热点
        //$channelLiveModel->addHotPoint($singerUid, $hotPoint);
        $model_anchor_pt = new anchor_points_model();
        $model_anchor_pt->on_anchor_recv_gift($singerUid, $giftValue);
        
        //增加亲密度
        $channelLiveModel->addGiftIntimacy($singerUid, $uid);
        
        //加周星记录
        $toolModel->UpdateWeekToolRecord($uid, $singerUid, $tid, $price*$num, $num);
         
        //送礼用户与主播在同一帮会才给该帮会增加阳光值
        if ($b_active_prop == 0) {
            $isUnionGuard = $channelLiveModel->isUnionGuard($singerUid, $uid);
            if($isUnionGuard){
                $userInfo->updateUnionSunNum($user['union_id'], $singerUid, $giftValue);
            }
        }
        
        //获得主播魅力值
        $charismaModel = new CharismaModel();
        $charisma = 0;
        $sunCount = $charismaModel->anchorSunshine($singerUid,$giftValue);
        $Sunshine = $giftValue;
        $userAttrModel->addChannelUserLevelinfoToRedis($sid,$uid);
        //添加结束
        
        //判断是否为守护
        $singerGuardModel = new SingerGuardModel();
        $endTime = $singerGuardModel->getGuardEndTime($uid, $singerUid);
        $guardType = $singerGuardModel->getGuardType($uid, $singerUid);
        $now = time();
        $isGuard = 0 ;
        if (!empty($endTime) && $endTime > $now) {
            if(1 == $guardType || 2 == $guardType || 3 == $guardType){
                //守护有效
                $isGuard = 1;
            }
        }
        
        // 分两步，第一步更新榜单分值。
        // 礼物勋章过滤官方用户
        if ($uid != 20000000 && $uid != 20015113) {
            $toolModel->gift_zIncrBy($tid, $uid, $num);
        }
        
        // 第二部获取前三榜单用户
        $top3 = $toolModel->getTop3($tid, $uid);
        $honor = 0;
        $index = 0;
        if(!empty($top3)){
            foreach ($top3 as $top){
                $index++;
                if($top == $uid){
                    $honor = $index;
                    break;
                }
            }
        }
        
        //重新计算财富等级
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $newSingerAttr = $userAttrModel->getAttrByUid($singerUid);
        $richManInfo = $userAttrModel->getRichManLevel($uid, $userAttr['gift_consume'], $userAttr['consume_level']);
        $result['money'] = (int)$userAttr['coin_balance'];
        $result['totalSun'] = (int)$userAttr['sun_num'];

        $consumeAwardBegin = timeclock::getMillisecond();
        /*******$userAttr['con_incen_dedu']每天凌晨清零********/
        $model_user_gold = new user_gold_model();
        $total_con_incen =  $model_user_gold->con_incen_dedu_add($uid, $price * $num);
        $rewardinfo = $toolConsumRecordModel->getConsumeRewards2($uid, $total_con_incen);
        if(!empty($rewardinfo)){
            $return[] = array(
                    'broadcast' => 0,
                    'data' => array(
                            'cmd' => 'RConsumeAward',
                            'uid' => $uid,
                            'boxids' => $rewardinfo,
                            'times' => 1
                    )
            );      
        }
        
        /***************/
        
        $myGuard = $singerGuardModel->getMyGuardList($uid);
        
        $charmValue = floor($num * $tool_inf['receiver_charm'] * $charmRate);
        //
        $vipLevel = $userInfo->getVipLevel($uid);

       
        $broadcastResult['list'][0] = array(
                'uid' => $uid,
                    'type' => $tool_inf['category2'],
                    'id' => $tool_inf['id'],
                    'sendTime' => $sendTime,
                    'flag' => $flag,
                    'serialNum' => $serialNum,
                    'num' => $num,
                    'giftValue' => $giftValue,
                    'charmValue' => $charmValue,
                    'isNew' => $isNew,
                    'isGuard' => $isGuard,
                    'guardType' => $guardType,
                    'honor' => $honor, //0：非top3中， 1：第一名 2：第二名 3：第三名
                    'vipLevel' => $vipLevel,
                    'nick' => $senderNick,
                    'vip' => $vipInfo['vip'],
                    'richLevel' => $richManInfo['richManLevel'],
                    'richStart' => $richManInfo['richManStart'],
                    'closeLevel' => empty($closeLevel['closeLevel']) ? 0 : $closeLevel['closeLevel'],
                    'myGuard' => $myGuard,
                    'charisma' => $charisma,
                    'giftName' => $tool_inf['name'],
                    'imgUrl' => (string)$tool_inf['icon'],
                    //                     'sunshine' => $Sunshine,
                    //                     'sunshineTotal' => $sunCount,
                    'singerChannelPoint' => $newSingerAttr['channel_point'], //主播对应的秀点
                    'identity' => (int)$user['identity'],
                    'photo' => $user['photo'],
                    'effect_id' => isset($tool_inf['effect_id']) ? intval($tool_inf['effect_id']):0,
                    'show_time' => isset($tool_inf['show_time']) ? intval($tool_inf['show_time']):0
        );
        
        
        // 增加主播直播间内秀币统计值
        $money_const = $giftValue;
        $moneyFinal = $charismaModel->AddSingerMoneyCount($singerUid, $money_const);
        $money_nt = array();
        $money_nt['cmd'] = 'BSingerAttrMoneyUpdate';
        $money_nt['uid'] = $singerUid;
        $money_nt['sid'] = $sid;
        $money_nt['moneyFinal'] = (int)$moneyFinal;
        $money_nt['moneyDelta'] = (int)$money_const;
        
        $return[] = array (
                    'broadcast' => 1, //全直播间
                    'data' => $money_nt,
        );
            
        $result['result'] = 0; // 使用道具或赠送礼物成功!
        $result['buy'] = $buy;
        if ($tool_inf['consume_type'] == 1) {
            $result['state'] = 2;
        }

        $userAttr = $userAttrModel->getAttrByUid($uid);
        $result['coinBalance'] = $userAttr['coin_balance'];
        $result['luckyShakeCount'] = $userAttrModel->getActivity($userAttr);
        
        $return[] = array(
                'broadcast' => 0,
                'data' => $result
        );
        LogApi::logProcess('gift_type:' . $tool_inf['category1']);
        $isAllPlatform = false;
        // 全區廣播（超过50块钱即：5000秀币）
        $sys_parameters = new SysParametersModel();
        $pmdPrice = $sys_parameters->GetSysParameters(226, 'parm1');
        if (empty($pmdPrice)) {
            $pmdPrice = 5000;
        }
         
        $pmd = false;
        $totalPrice = $tool_inf['price'] * $num;


        //单次金币价值大于触发条件，每次都出现
        if($totalPrice >= $pmdPrice){
          $pmd = true;
        }
        //当单次阳光不满足触发条件，但是连送情况下累计可以触发
        if($totalPrice < $pmdPrice)
        {
            $tiantou=( 0 == $pmdPrice % $totalPrice) ? 0 : 1;//判断次数是否可以整除，如果可以，添头为0，如果不可以，添头为1
            if( 0 ==$serialNum %( $pmdPrice/$totalPrice + $tiantou ) ){
                $pmd = true;
            }
        }

        if ($pmd) {     
            $honors = array();
            $gifts = $toolModel->getAllTools();
            foreach ($gifts as $g){
                $top3 = $toolModel->getTop3($g['id'], $uid);
                $honor = 0;
                $index = 0;
                foreach ($top3 as $top){
                    $index++;
                    if($top == $uid){
                        $honor = $index;
                        break;
                    }
                }
                if($honor){
                    $item = array();
                    $item['tid'] = $g['id'];
                    $item['img'] = $g['icon'];
                    $item['index'] = $honor;
                    $honors['items'][] = $item;
                }
            }
            
            $pmd_lvl2_price = $sys_parameters->GetSysParameters(227, 'parm1');
            if (empty($pmd_lvl2_price)) {
                $pmd_lvl2_price = 99900;
            }
        
            $toollevel = 0;            
            //if($totalPrice >= $pmd_lvl2_price){
            //  $toollevel = 1;
            //}
            //连送情况下，连送累计金额大于2级跑马灯下限金额
            if( ($totalPrice * $serialNum ) >= $pmd_lvl2_price){
              $toollevel = 1;
            }

            // 星老勋章
            $star_chief_rank = 0;
            $star_chief_index = 0;
            $week_star_top3 = $toolModel->get_week_star_cheif_top3();
            if (!empty($week_star_top3) && is_array($week_star_top3)) {
                foreach ($week_star_top3 as $m) {
                    $star_chief_index++;
                    if ($uid == $m) {
                        $star_chief_rank = $star_chief_index;
                        break;
                    }
                }
            }
        
            $buffer_object = array(
                    'receiver' => $params['uid_onmic'],
                    'receiverNick' => $params['receiver'],
                    'vip' => $vipInfo['vip'],
                    'richManLevel' => $richManInfo['richManLevel'],
                    'richManTitle' => $richManInfo['richManTitle'],
                    'richManStart' => $richManInfo['richManStart'],
                    'sender' => $uid,
                    'senderNick' => $senderNick,
                    'type' => $tool_inf['category2'],
                    'id' => $tool_inf['id'],
                    'icon' => $tool_inf['icon'],
                    'resource' => $tool_inf['resource'],
                    'gift_name' => $tool_inf['name'],
                    'ts' => $tsNow,
                    'num' => $num,
                    'honors' => $honors,
                    'toolLevel' => $toollevel,
                    'sid' => $sid,
                    'serialNum'=>$serialNum,
                    'b_week_star' => $toolModel->b_week_star($singerUid) ? 1 : 0,
                    'star_chief_rank' => $star_chief_rank
            );
        
            $return[] = array(
                    'broadcast' => 4,
                    'data' => array(
                            'cmd' => 'BBroadcast',
                            'type' => 1,//BBroadcastGift type
                            'gift'=>$buffer_object,
                    )
            );
            $isAllPlatform = true;

            // 上跑马灯任务
            $return[] = array(
                'broadcast' => 5,
                'data' => array(
                        'uid' => (int)$uid,
                        'target_type' => 57, //上跑马灯
                        'num' => 1,
                        'extra_param' =>0
                )
            );
        }
        
        {
            // 组送特效
            $broadcastResult['list'][0]['group_effect'] = 0;
            if ($num > 1) {
                if ($pmd) {
                    $broadcastResult['list'][0]['group_effect'] = 20096;
                } else {
                    $broadcastResult['list'][0]['group_effect'] = 20095;
                }
            }
        }
        
//         $return[] = array(
//                 'broadcast' => 1, //全直播间
//                 'data' => $broadcastResult
//         );
//         //创建一个连麦pk跨房间多播
//         if ($pk_singer_sid != 0)
//         {
//             $m = new cback_channel_model();
//             $m->broadcast($pk_singer_sid, $broadcastResult);
//             LogApi::logProcess("linkcall_pk_model.sendgift pk_singer_sid:$pk_singer_sid broadcastResult:".json_encode($broadcastResult));
//         }
        return $return;
    }
}

?>
