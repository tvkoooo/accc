<?php

/**
 * 秀場的基礎組件：飄帶公告、彈窗公告、主播大廳、排行榜、喇叭、每日任務、砸蛋抽獎、廣告、連續登陸獎勵、轉盤抽獎 ...
 */
class WidgetApi
{

    /**
     * 閱讀公告時間登記
     */
    public static function readNotice($params)
    {
        $result = array(
            'cmd' => 'RReadNotice'
        );
        $uid = $params['uid'];
        $userAttrModel = new UserAttributeModel();
        $lastTimeReadNotice = time();
        $userAttrModel->setStatusByUid($uid, 'last_time_read_notice', $lastTimeReadNotice);
        $result['lastTimeReadNotice'] = $lastTimeReadNotice;
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    /**
     * 喇叭公告,彈窗公告,直播大廳歌手
     * 注意：服務端定時調次接口，所以沒有uid、sid等狀態信息
     */
    public static function notice($params)
    {
        $config = Config::getConfig();
        $flashVersion = $config['data_center']['flash_version'];
        $model = new ModelBase();
        // 喇叭公告
        $noticeList = $model->getRedisMaster()->hGetAll('show:notice');
        $notice = array();
        if ($noticeList) {
            foreach ($noticeList as $noticeItem) {
                $noticeMember = json_decode($noticeItem, true);
                if ($noticeMember['start_at'] < time() && $noticeMember['end_at'] > time()) {
                    $notice[] = $noticeMember;
                }
            }
        }
        // 彈窗公告
        $infoList = $model->getRedisMaster()->hGetAll('show:info');
        $info = array();
        if ($infoList) {
            foreach ($infoList as $infoItem) {
                $info[] = json_decode($infoItem, true);
            }
        }
        // 直播大廳歌手
        $singer = array();
        $singerList = $model->getRedisMaster()->get('show:singer');
        if ($singerList) {
            $singer = json_decode($singerList, true);
        }
        $return[] = array(
            'broadcast' => 3,
            'data' => array(
                'cmd' => 'BBroadcast',
                'version' => $flashVersion,
                'notice' => $notice,
                'info' => $info,
                'singer' => $singer
            )
        );
        return $return;
    }

    /**
     * 直播大廳
     */
    public static function getVideoSinger($params)
    {
        $result = array(
            'cmd' => 'RGetVideoSinger',
            'result' => 0
        );
        $model = new ModelBase();
        $singer = array();
        $singerList = $model->getRedisMaster->get('show:singer');
        if ($singerList) {
            $singer = json_decode($singerList, true);
        }
        $result['singer'] = $singer;
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    /**
     * 排行榜
     */
    public static function getRanking($params, $type)
    {
        $result = array(
            'cmd' => 'RGetRanking',
            'list' => array()
        );
        if (empty($params['uid_onmic'])) {
            $result['result'] = 103; // 没有麦上表演者信息
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        $rankModel = new RankingModel();
        switch ($type) {
            case 'gift':
                $ranking = $rankModel->getGiftRankingByUid($params['uid_onmic']);
                break;
            default:
                $ranking = array();
                break;
        }
        if ($ranking) {
            $result['list'] = $ranking;
        }
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }
    //设置求看数
    public static function setShowHeart($param)
    {
        $params['returnCmd'] = 'RSetShowHeart';
        $returnResult = array(
            'cmd' => $params['returnCmd'],
            'result' => 0
        );
        $uid = (int)$param['uid'];
        $str = $param['jsonstr'];
        $userAttrModel = new UserAttributeModel();
        $userAttrModel->setShowHeartInfo($uid,$str);

        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
	return $return;


    }
    //请求求看信息
    public static function getShowHeart($param)
    {
        $params['returnCmd'] = 'RGetShowHeart';
        $returnResult = array(
            'cmd' => $params['returnCmd'],
            'result' => 0
        );
        $uid = (int)$param['singer_uid'];
        $userAttrModel = new UserAttributeModel();
        $jsonstr = $userAttrModel->getShowHeartInfo($uid);
        $num = $userAttrModel->getShowHeart($uid);
        $returnResult['jsonstr'] = $jsonstr;
        $returnResult['num'] = $num;
         $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
	return $return;

    }
    //发送求看消息
    public static function sendShowHeart($params)
    {
        $params['returnCmd'] = 'RSendShowHeart';
        $params['broadcastCmd'] = 'BSendShowHeart';
        $returnResult = array(
            'cmd' => $params['returnCmd'],
            'result' => 0
        );
        $broadcastResult = array(
            'cmd' => $params['broadcastCmd'],
            'num' => 0
        );
        $uid = (int)$params['uid'];
        $singerUid = (int)$params['uid_onmic']; // 接收方用户id
        if (empty($singerUid)) {
            $returnResult['result'] = 103; // 接收方用户id不能为空
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        if ($uid == $singerUid) {
            $returnResult['result'] = 113; // 不能给自己送
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }

        $userAttrModel = new UserAttributeModel();
        $userAttrModel->addShowHeart($singerUid,1);
        $num = $userAttrModel->getShowHeart($singerUid);
        $broadcastResult['num'] = $num;
      
        $return[] = array(
            'broadcast' => 1,
            'data' => $broadcastResult
        );
        return $return;
    }
    public static function sendHeart($params)
    {
        $params['returnCmd'] = 'RSendHeart';
        $params['broadcastCmd'] = 'BHeart';
        $returnResult = array(
            'cmd' => $params['returnCmd'],
            'result' => 0
        );
        $broadcastResult = array(
            'cmd' => $params['broadcastCmd'],
            'receiver' => $params['uid_onmic'],
            'receiverNick' => $params['receiver'],
            'list' => array()
        );
        $uid = (int)$params['uid'];
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $vipInfo = $userAttrModel->getVipInfo($userAttr);
        $richManInfo = $userAttrModel->getRichManLevel($userAttr['uid'], $userAttr['gift_consume'], $userAttr['consume_level']);
        $senderNick = $params['sender'];
        $singerUid = (int)$params['uid_onmic']; // 接收方用户id
        if (empty($singerUid)) {
            $returnResult['result'] = 103; // 接收方用户id不能为空
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        if ($uid == $singerUid) {
            $returnResult['result'] = 113; // 不能给自己送爱心
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $singerAttr = $userAttrModel->getAttrByUid($singerUid);
        $qty = 1; // 赠送的爱心数量
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $settings = new SettingsModel();
        if ($userAttr['heart'] < $qty) {
            $returnResult['result'] = 108;
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $charm = $qty * $settings->getValue('HEART_TO_CHARM_RATIO');
        $receiverAttr = $userAttrModel->getAttrByUid($params['uid_onmic']);
        $heartSendModel = new HeartSendRecordModel();
        $result = $heartSendModel->send($uid, $singerUid, $qty, $charm);
        if ($result == 0) {
            $returnResult['result'] = $result; // 赠送爱心成功!
            $return[] = array(
                'broadcast' => 0,
                'data' => $returnResult
            );
            $broadcastResult['charm'] = $receiverAttr['charm'] + $charm;
            $broadcastResult['list'][] = array(
                'sender' => $uid,
                'senderNick' => $senderNick,
                'vip' => $vipInfo['vip'],
                'richManLevel' => $richManInfo['richManLevel'],
                'richManTitle' => $richManInfo['richManTitle'],
                'richManStart' => $richManInfo['richManStart']
            );
            $return[] = array(
                'broadcast' => 1,
                'data' => $broadcastResult
            );
            if ($singerAttr) {
                $currentExpe = $singerAttr['experience'];
                $newExpe = $currentExpe + 100 * $qty;
                $levelChange = $userAttrModel->getExperienceChange($currentExpe, $newExpe);
                if ($levelChange) {
                    $levelChange['cmd'] = 'BSingerLevelUp';
                    $levelChange['singerUid'] = $singerUid;
                    $return[] = array(
                        'broadcast' => 1,
                        'data' => $levelChange
                    );
                }
            }
            return $return;
        } else {
            $returnResult['result'] = $result;
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
    }
    public static function convertHeart($params)
    {
        $returnResult = array(
            'cmd' => 'RConvertHeart',
            'result' => 0
        );
        $userAttrModel = new UserAttributeModel();
        $uid = (int)$params['uid'];
        // 嘗試獲取鎖
        if ($userAttrModel->statusIncrease($uid, 'heart_convert_lock') > 1) {
            $userAttrModel->statusIncrease($uid, 'heart_convert_lock', -1);
            $returnResult['result'] = 125; // 請勿使用外掛軟體刷機
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $qty = 1; // 换取的爱心数量
        if (!empty($params['num'])) {
            $qty = (int)$params['num'];
        }
        $userFbModel = new UserFacebookInfoModel();
        // 判斷是否綁定fb
        if (!$userFbModel->isFbBound($uid)) {
            $returnResult['result'] = 134; // 没有綁定fb
            // 釋放鎖
            $userAttrModel->setStatusByUid($uid, 'heart_convert_lock', 0);
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $userInfoModel = new UserInfoModel();
        $silver = $userInfoModel->getSilver($uid);
        $returnResult['silver'] = $silver;
        $settings = new SettingsModel();
        $silverNeeded = $qty * $settings->getValue('SILVER_COST_PER_HEART');
        // 判断是否有足够的银豆
        if ($silver < $silverNeeded) {
            $returnResult['result'] = 109; // 没有足够的银豆
            // 釋放鎖
            $userAttrModel->setStatusByUid($uid, 'heart_convert_lock', 0);
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // 愛心還沒有用完，不能兌換新的愛心。
        $heartConvertRecordModel = new HeartConvertRecordModel();
        $userAttr = $userAttrModel->getAttrByUid($params['uid']);
        if ($userAttr['heart'] > 0) {
            $returnResult['result'] = 124; // 愛心還沒有用完，不能兌換新的愛心。
            // 釋放鎖
            $userAttrModel->setStatusByUid($uid, 'heart_convert_lock', 0);
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // 距離上一次換愛心不超過3天，不可以兑换
        $hour = $heartConvertRecordModel->convertInterval($uid);
        if ($hour) {
            $returnResult['result'] = 119;
            $returnResult['hour'] = $hour;
            // 釋放鎖
            $userAttrModel->setStatusByUid($uid, 'heart_convert_lock', 0);
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // 兑换
        $result = $heartConvertRecordModel->convert($uid, $silverNeeded, $qty);
        if ($result != 0) {
            $returnResult['result'] = $result;
            // 釋放鎖
            $userAttrModel->setStatusByUid($uid, 'heart_convert_lock', 0);
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $returnResult['silver'] = $silver - $silverNeeded;
        $returnResult['num'] = $userAttr['heart'] + $qty;
        // 釋放鎖
        $userAttrModel->setStatusByUid($uid, 'heart_convert_lock', 0);
        return array(
            array(
                'broadcast' => 0,
                'data' => $returnResult
            )
        );
    }

    public static function smashEgg($params)
    {
        $returnResult = array(
            'cmd' => 'RSmashEgg',
            'result' => 0
        );
        $uid = $params['uid'];
        $eggKey = $params['egg'];
        $userAttrModel = new UserAttributeModel();
        $luckyDrawModel = new LuckyDrawModel();
        $result = $luckyDrawModel->smashEgg($uid, $eggKey);
        if (is_numeric($result)) {
            $returnResult['result'] = $result;
            $return[] = array(
                'broadcast' => 0,
                'data' => $returnResult
            );
        } else {
            $returnResult['eggs'] = $luckyDrawModel->refreshEgg($uid);
            $returnResult['coinBalance'] = $userAttrModel->getAttrByUid($uid, 'coin_balance');
            $return[] = array(
                'broadcast' => 0,
                'data' => array_merge($returnResult, $result)
            );
            if ($result['value'] >= 100) {
                $broadcastResult = array(
                    'cmd' => 'BSmashEgg',
                    'sender' => $uid,
                    'senderNick' => $params['sender'],
                    'luckdrawResult' => $result['amount'] . '個禮物，價值' . $result['value'] . '秀幣'
                );
                $return[] = array(
                    'broadcast' => 2,
                    'data' => $broadcastResult
                );
            }
        }
        return $return;
    }

    public static function refreshEgg($params)
    {
        $returnResult = array(
            'cmd' => 'RRefreshEgg',
            'result' => 0
        );
        $uid = $params['uid'];
        $force = $params['force'];
        $userAttrModel = new UserAttributeModel();
        $luckyDrawModel = new LuckyDrawModel();
        if ($force) {
            if (!$userAttrModel->deductCoin($uid, 1)) {
                $returnResult['result'] = 129;
                $return[] = array(
                    'broadcast' => 0,
                    'data' => $returnResult
                );
                return $return;
            }
            $luckyDrawModel->addRefreshRecord($uid);
        } else {
            $refreshEggTime = $userAttrModel->getStatusByUid($uid, 'refresh_egg_time');
            if (!empty($refreshEggTime)) {
                $realDuration = time() - $refreshEggTime;
                $duration = $luckyDrawModel->refreshDuration * 60 * 0.9;
                if ($realDuration < $duration) {
                    $returnResult['result'] = 130;
                    $return[] = array(
                        'broadcast' => 0,
                        'data' => $returnResult
                    );
                    return $return;
                }
            }
        }
        $returnResult['eggs'] = $luckyDrawModel->refreshEgg($uid);
        $returnResult['coinBalance'] = $userAttrModel->getAttrByUid($uid, 'coin_balance');
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function getEggInfo($params)
    {
        $returnResult = array(
            'cmd' => 'RGetEggInfo',
            'result' => 0
        );
        $uid = $params['uid'];
        $luckyDrawModel = new LuckyDrawModel();
        $returnResult = array(
            'eggs' => $luckyDrawModel->getEggInfo($uid),
            'refreshDuration' => $luckyDrawModel->refreshDuration,
            'refreshCost' => $luckyDrawModel->refreshCost
        );
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function getSpeakerInfo($params)
    {
        $returnResult = array(
            'cmd' => 'RGetSpeakerInfo',
            'result' => 0
        );
        $returnResult = array(
            'price' => SpecialToolModel::SPEAKER_PRICE,
            'maxWordCount' => SpecialToolModel::SPEAKER_MAX_WORD_COUNT
        );
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function getGiftBoxInfo($params)
    {
        $returnResult = array(
            'cmd' => 'RGetEggInfo',
            'result' => 0
        );
        $giftBoxModel = new GiftBoxModel();
        $returnResult = array(
            'boxs' => $giftBoxModel->getBoxList(),
            'duration' => $giftBoxModel->duration
        );
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function speaker($params)
    {
        $returnResult = array(
            'cmd' => 'RSpeaker',
            'result' => 0
        );
        $specialToolModel = new SpecialToolModel();
        $uid = $params['uid'];
        $sid = $params['sid'];
        $cid = $params['cid'];
        $senderNick = $params['sender'];
        $uidOnmic = isset($params['uid_onmic']) ? $params['uid_onmic'] : 0;
        // $price = SpecialToolModel::SPEAKER_PRICE;
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $vipInfo = $userAttrModel->getVipInfo($userAttr);
        $richManInfo = $userAttrModel->getRichManLevel($userAttr['uid'], $userAttr['gift_consume'], $userAttr['consume_level']);
        $price = $vipInfo['speakerPrice'];
        $message = $params['message'];
        if (!$userAttrModel->deductCoin($uid, $price)) {
            $returnResult['result'] = 132;
            $return[] = array(
                'broadcast' => 0,
                'data' => $returnResult
            );
            return $return;
        }
        if (mb_strlen($message, 'utf8') > SpecialToolModel::SPEAKER_MAX_WORD_COUNT) {
            $returnResult['result'] = 133;
            $return[] = array(
                'broadcast' => 0,
                'data' => $returnResult
            );
            return $return;
        }
        $specialToolModel->addRecord(SpecialToolModel::SPEAKER_ID, $uid, $sid, $cid, $uidOnmic, $price, $message);
        $returnResult['coinBalance'] = $userAttr['coin_balance'];
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        $return[] = array(
            'broadcast' => 1,
            'data' => array(
                'cmd' => 'BSpeaker',
                'sender' => $uid,
                'senderNick' => $senderNick,
                'vip' => $vipInfo['vip'],
                'richManLevel' => $richManInfo['richManLevel'],
                'richManTitle' => $richManInfo['richManTitle'],
                'richManStart' => $richManInfo['richManStart'],
                'message' => $message
            )
        );
        return $return;
    }

    public static function openGiftBox($params)
    {
        $returnResult = array(
            'cmd' => 'ROpenGiftBox',
            'result' => 0
        );
        $uid = $params['uid'];
        $box = $params['box'];
        $singerUid = $params['singer'];
        $giftBoxModel = new GiftBoxModel();
        $userAttrModel = new UserAttributeModel();
        // 獎勵先到先得
        if ($userAttrModel->statusIncrease($singerUid, 'box_user_num', -1) > 0) {
            // 抽獎
            $result = $giftBoxModel->openLuckyGiftBox($uid, $box, $singerUid);
            if (!$result) {
                // 安慰獎：1個小禮物
                $result = $giftBoxModel->openGiftBox($uid, $box);
            } else {
                // 中獎廣播
                $broadcastResult = array(
                    'cmd' => 'BBroadcast',
                    'giftBox' => array(
                        'sender' => $uid,
                        'senderNick' => $params['sender'],
                        'message' => $result['amount'] . '個' . $result['giftName']
                    )
                );
                $return[] = array(
                    'broadcast' => 1,
                    'data' => $broadcastResult
                );
            }
        } else {
            $result = array(
                'amount' => 0,
                'gift' => 0,
                'giftName' => ''
            );
        }
        // Logger::fileLog("$uid 要開歌手 $singerUid 的 $box 級寶箱 => " .
        // $result['amount'] . "個" . $result['giftName']);
        $returnResult = array_merge($returnResult, $result);
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function getTaskInfo($params)
    {
        $returnResult = array(
            'cmd' => 'RGetTaskInfo',
            'result' => 0
        );
        $uid = $params['uid'];
        $recUid = $params['uid_onmic'];
        $taskModel = new TaskModel();
        $returnResult['info'] = $taskModel->getTaskInfo($uid, $recUid);
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function getTaskReward($params)
    {
        $returnResult = array(
            'cmd' => 'RGetTaskReward',
            'result' => 0
        );
        $uid = $params['uid'];
        $singerUid = $params['uid_onmic'];
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        $userAttrModel = new UserAttributeModel();
        if ($userAttrModel->statusIncrease($uid, 'task_reward_lock') > 1) {
            $userAttrModel->statusIncrease($uid, 'task_reward_lock', -1);
            $returnResult['result'] = 125; // 請勿使用外掛軟體刷機
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // 領取獎勵的經驗
        $taskId = $params['id'];
        $taskModel = new TaskModel();
        $task = $taskModel->getTasks($taskId);
        $rsCode = $taskModel->sendTaskReward($uid, $task);
        if ($rsCode > 0) {
            // 釋放鎖
            $userAttrModel->setStatusByUid($uid, 'task_reward_lock', 0);
            $returnResult['result'] = $rsCode;
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        // 歌手等級變化
        if ($uid == $singerUid) {
            $currentExpe = $userAttr['experience'];
            $newExpe = $currentExpe + $task['reward'];
            $levelChange = $userAttrModel->getExperienceChange($currentExpe, $newExpe);
            if ($levelChange) {
                $levelChange['cmd'] = 'BSingerLevelUp';
                $levelChange['singerUid'] = $singerUid;
                $return[] = array(
                    'broadcast' => 1,
                    'data' => $levelChange
                );
            }
        }
        $returnResult['id'] = $taskId;
        // 釋放鎖
        $userAttrModel->setStatusByUid($uid, 'task_reward_lock', 0);
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function getRankInfo($params)
    {
        $returnResult = array(
            'cmd' => 'RGetRankInfo',
            'result' => 0
        );
        $date = '_' . date('Ymd');
        $week = '_' . date('W');
        $month = '_' . date('Ym');
        $lastDate = '_' . date('Ymd', mktime(0, 0, 0, date("n"), date("j") - 1, date("Y")));
        $lastWeek = '_' . date('W', mktime(0, 0, 0, date("n"), date("j") - 7, date("Y")));
        $lastMonth = '_' . date('Ym', mktime(0, 0, 0, date("n") - 1, date("j"), date("Y")));
        $uid = $params['uid'];
        $rankingModel = new RankingModel();
        // 收禮送禮排行榜
        $size = 10;
        $returnResult['rankDayReceiveGift'] = $rankingModel->getRankList('rank_day_receive_gift' . $date, $size);
        $returnResult['rankDaySendGift'] = $rankingModel->getRankList('rank_day_send_gift' . $date, $size);
        $returnResult['rankWeekReceiveGift'] = $rankingModel->getRankList('rank_week_receive_gift' . $week, $size);
        $returnResult['rankWeekSendGift'] = $rankingModel->getRankList('rank_week_send_gift' . $week, $size);
        $returnResult['rankMonthReceiveGift'] = $rankingModel->getRankList('rank_month_receive_gift' . $month, $size);
        $returnResult['rankMonthSendGift'] = $rankingModel->getRankList('rank_month_send_gift' . $month, $size);
        // 收禮送禮排行榜-往期
        $returnResult['rankLastDayReceiveGift'] = $rankingModel->getRankList('rank_day_receive_gift' . $lastDate);
        $returnResult['rankLastDaySendGift'] = $rankingModel->getRankList('rank_day_send_gift' . $lastDate);
        $returnResult['rankLastWeekReceiveGift'] = $rankingModel->getRankList('rank_week_receive_gift' . $lastWeek);
        $returnResult['rankLastWeekSendGift'] = $rankingModel->getRankList('rank_week_send_gift' . $lastWeek);
        $returnResult['rankLastMonthReceiveGift'] = $rankingModel->getRankList('rank_month_receive_gift' . $lastMonth);
        $returnResult['rankLastMonthSendGift'] = $rankingModel->getRankList('rank_month_send_gift' . $lastMonth);
        // 個人
        $returnResult['myRankDayReceiveGift'] = $rankingModel->getRankByUid('rank_day_receive_gift' . $date, $uid);
        $returnResult['myRankDaySendGift'] = $rankingModel->getRankByUid('rank_day_send_gift' . $date, $uid);
        $returnResult['myRankWeekReceiveGift'] = $rankingModel->getRankByUid('rank_week_receive_gift' . $week, $uid);
        $returnResult['myRankWeekSendGift'] = $rankingModel->getRankByUid('rank_week_send_gift' . $week, $uid);
        $returnResult['myRankMonthReceiveGift'] = $rankingModel->getRankByUid('rank_month_receive_gift' . $month, $uid);
        $returnResult['myRankMonthSendGift'] = $rankingModel->getRankByUid('rank_month_send_gift' . $month, $uid);
        // 返回
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function getLoginPacket($params)
    {
        $returnResult = array(
            'cmd' => 'RGetLoginPacket',
            'result' => 0
        );
        $uid = $params['uid'];
        $loginPacketModel = new LoginPacketModel();
        $data = $loginPacketModel->getLoginPacket($uid);
        $userFbModel = new UserFacebookInfoModel();
        $data['isFbBound'] = $userFbModel->isFbBound($uid) ? true : false;
        if (empty($data['packetList'])) {
            $returnResult['result'] = 146; // 每日登陸禮包已經領取
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        } else {
            $returnResult['data'] = $data;
        }
        // 返回
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    public static function sendLoginPacket($params)
    {
        $returnResult = array(
            'cmd' => 'RSendLoginPacket',
            'result' => 0
        );
        $uid = $params['uid'];
        $userAttrModel = new UserAttributeModel();
        // 判斷是否綁定fb
        $userFbModel = new UserFacebookInfoModel();
        if (!$userFbModel->isFbBound($uid)) {
            $userAttrModel->setStatusByUid($uid, 'daily_login_packet_lock', 0);
            $returnResult['result'] = 134; // 没有綁定fb
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        if ($userAttrModel->statusIncrease($uid, 'daily_login_packet_lock') > 1) {
            $userAttrModel->statusIncrease($uid, 'daily_login_packet_lock', -1);
            $returnResult['result'] = 125; // 請勿使用外掛軟體刷機
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        $loginPacketModel = new LoginPacketModel();
        $packet = $loginPacketModel->sendLoginPacket($uid);
        if (empty($packet)) {
            $userAttrModel->setStatusByUid($uid, 'daily_login_packet_lock', 0);
            $returnResult['result'] = 146; // 每日登陸禮包已經領取
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        } else {
            $returnResult['packet'] = $packet;
        }
        // 返回
        $userAttrModel->setStatusByUid($uid, 'daily_login_packet_lock', 0);
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    /**
     * 獲取廣告
     */
    public static function getAds($param)
    {
        $returnResult = array(
            'cmd' => 'RGetAds',
            'result' => 0
        );
        $model = new ModelBase();
        $infoList = $model->getRedisMaster->hGetAll('advertise_in_flash');
        $info = array();
        if ($infoList) {
            foreach ($infoList as $infoItem) {
                $info[] = json_decode($infoItem, true);
            }
        }
        $returnResult['info'] = $info;
        return array(
            array(
                'broadcast' => 0,
                'data' => $returnResult
            )
        );
    }

    /**
     * 獲取幸運轉盤
     */
    public static function getLuckyDail($params)
    {
        $returnResult = array(
            'cmd' => 'RGetLuckyDail',
            'result' => 0
        );
        $uid = $params['uid'];
        $luckyDailModel = new LuckyDailModel();
        $returnResult['packetList'] = $luckyDailModel->getGiftPacket($uid);
        $returnResult['canDail'] = $luckyDailModel->canDail($uid);
        return array(
            array(
                'broadcast' => 0,
                'data' => $returnResult
            )
        );
    }

    /**
     * 轉動轉盤抽獎
     */
    public static function drawLuckyDail($params)
    {
        $returnResult = array(
            'cmd' => 'RDrawLuckyDail',
            'result' => 0
        );
        $uid = $params['uid'];
        $luckyDailModel = new LuckyDailModel();
        $packet = $luckyDailModel->drawDail($uid);
        if (empty($packet)) {
            $returnResult['result'] = 160; // 每天只能參加一次轉盤抽獎
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $returnResult
                )
            );
        }
        if ($packet['num'] > 188) {
            $broadcastResult = array(
                'cmd' => 'BDrawLuckyDail',
                'sender' => $uid,
                'senderNick' => $params['sender'],
                'giftId' => $packet['id'],
                'giftNum' => $packet['num']
            );
            $return[] = array(
                'broadcast' => 2,
                'data' => $broadcastResult
            );
        }
        $returnResult['packet'] = $packet;
        $return[] = array(
            'broadcast' => 0,
            'data' => $returnResult
        );
        return $return;
    }

    /**
     * 獲取幸運主播
     */
    public static function getLuckySinger($params)
    {
        $result = array(
            'cmd' => 'RGetLuckySinger',
            'result' => 0
        );
        $model = new ModelBase();
        $singer = array();
        $luckyList = array();
        $singerList = $model->getRedisMaster->get('show:singer');
        if ($singerList) {
            $singer = json_decode($singerList, true);
            if (count($singer) >= 2) {
                $luckySinger = array_rand($singer, 2);
                $luckyList[] = $singer[$luckySinger[0]];
                $luckyList[] = $singer[$luckySinger[1]];
            }
        }
        if (empty($luckyList)) {
            $recomList = $model->getRedisMaster->get('home_page_recommend_list');
            if ($recomList) {
                $recom = array_keys(json_decode($recomList, true));
                $luckySinger = array_rand($recom, 2);
                $userImage = new UserImageModel();
                $luckyList[] = array(
                    'uid' => $recom[$luckySinger[0]],
                    'img' => $userImage->getDefaultImage($recom[$luckySinger[0]])
                );
                $luckyList[] = array(
                    'uid' => $recom[$luckySinger[1]],
                    'img' => $userImage->getDefaultImage($recom[$luckySinger[1]])
                );
            }
        }
        $newLuckyList = array();
        foreach ($luckyList as $luckyItem) {
            $newLuckyItem['uid'] = $luckyItem['uid'];
            $newLuckyItem['sid'] = isset($luckyItem['sid']) ? $luckyItem['sid'] : '';
            $newLuckyItem['cid'] = isset($luckyItem['cid']) ? $luckyItem['cid'] : '';
            $newLuckyItem['img'] = $luckyItem['img'];
            $newLuckyItem['userNum'] = isset($luckyItem['online']) ? $luckyItem['online'] : '';
            $newLuckyItem['url'] = 'http://www.showoo.cc/rcec/index.php?cmd=showPersonalHome&uid=' .
                $luckyItem['uid'];
            $newLuckyList[] = $newLuckyItem;
        }
        $result['luckySinger'] = $newLuckyList;
        // 返回结果
        return array(
            array(
                'broadcast' => 0,
                'data' => $result
            )
        );
    }

    public static function ikalaVerify($params)
    {
        $result = array(
            'cmd' => 'RIkalaVerify',
            'result' => 0
        );
        $uid = $params['uid'];
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        if (!$userAttr['auth']) {
            $result['result'] = 161; // 非認證主播不能使用點播
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }
        $rcid = sha1($uid . '@showoo.cc');
        $getNidUrl = 'http://rc.ikala.tv/getnonce.php?id=' . $rcid;
        $nidData = file_get_contents($getNidUrl);
        if ($nidData) {
            $nidData = json_decode($nidData, true);
            $nid = $nidData['nid'];
            $cnid = sha1(time());
            $checksum = sha1($nid . $cnid . 'IKALA_RC_#$%');
            $playerUrl = "http://rc.ikala.tv/verify.php?id=" . urlencode($rcid)
                . "&cnid=" . urlencode($cnid) . "&checksum=" . urlencode($checksum);
            $result['playerUrl'] = $playerUrl;
            Logger::logToDataFile('ikala.log', time() .',' . $uid . ',' . $playerUrl);
            // 返回结果
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        } else {
            $result['result'] = 162; // 點播繁忙，請從試
            return array(
                array(
                    'broadcast' => 0,
                    'data' => $result
                )
            );
        }

    }
}

?>
