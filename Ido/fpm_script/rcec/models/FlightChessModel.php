<?php

class FlightChessModel extends ModelBase
{

    private $sortedSetKey = 'flight_chess_rank';
    private $sendKey = 'activity_send_list';

    public static $cost = 1000;

    private $gamePointPrice = 500;

    public static $gameId = 1;

    public static $luckyGamePoint = 2;

    public static $boughtGamePoint = 3;

    public static $deductGamePoint = 4;

    public static $dailyGamePoint = 5;

    private $chess = array(
        0 => array(
            'text' => '睡覺中，免打擾|被下定身術，原地不動|見到不雅照，瞬間石化不動|途中遇大雨，暫停躲雨|天外飛來一球，倒地不起',
            'action' => '',
            'param' => 0
        ),
        1 => array(
            'text' => '拾金不昧|吃到烤肉|買到好吃的鳳梨酥|幫媽媽洗碗|把傘讓給別人自己淋雨回家',
            'action' => 'move',
            'param' => 1
        ),
        2 => array(
            'text' => '睡覺中，免打擾|被下定身術，原地不動|見到不雅照，瞬間石化不動|途中遇大雨，暫停躲雨|天外飛來一球，倒地不起',
            'action' => '',
            'param' => 0
        ),
        3 => array(
            'text' => '被外星人綁架|蔑視電腦|沒有每天登陸RC|褲子被劃破|踩到香蕉皮滑倒',
            'action' => 'move',
            'param' => -1
        ),
        4 => array(
            'text' => '睡覺中，免打擾|被下定身術，原地不動|見到不雅照，瞬間石化不動|途中遇大雨，暫停躲雨|天外飛來一球，倒地不起',
            'action' => '',
            'param' => 0
        ),
        5 => array(
            'text' => '扶老爺爺過馬路|撿到一萬元|當選學生會會長|交稅成功',
            'action' => 'move',
            'param' => 2
        ),
        6 => array(
            'text' => '在路邊撿到1000元 |收到巧克力糖果|萬聖節扮鬼嚇到鬼|玩RC遇到違規分子舉報成功|給賣上主播掌聲鼓勵',
            'action' => 'bonus',
            'param' => 1000
        ),
        7 => array(
            'text' => '睡覺中，免打擾|被下定身術，原地不動|見到不雅照，瞬間石化不動|途中遇大雨，暫停躲雨|天外飛來一球，倒地不起',
            'action' => '',
            'param' => 0
        ),
        8 => array(
            'text' => '桃園市房屋鬧鬼，返回起點|超級颱風襲擊花蓮，返回起點|鋼鐵俠PK蝙蝠俠，返回起點|RC伺服器重啟，返回起點|你被人黑了，返回起點|遇上海底地震，返回起點',
            'action' => 'move',
            'param' => -8
        ),
        9 => array(
            'text' => '掉進水溝就醫|你家小狗亂大小便|欺善怕惡|偷看18禁電影',
            'action' => 'move',
            'param' => -2
        ),
        10 => array(
            'text' => '發工資|花蓮旅遊|終於升職加薪|帶弟弟去萬聖節鬼屋',
            'action' => 'move',
            'param' => 3
        ),
        11 => array(
            'text' => '在路邊撿到1000元 |收到巧克力糖果|萬聖節扮鬼嚇到鬼|玩RC遇到違規分子舉報成功|給賣上主播掌聲鼓勵',
            'action' => 'bonus',
            'param' => 1000
        ),
        12 => array(
            'text' => '拾金不昧|吃到烤肉|買到好吃的鳳梨酥|幫媽媽洗碗|把傘讓給別人自己淋雨回家',
            'action' => 'move',
            'param' => 1
        ),
        13 => array(
            'text' => '睡覺中，免打擾|被下定身術，原地不動|見到不雅照，瞬間石化不動|途中遇大雨，暫停躲雨|天外飛來一球，倒地不起',
            'action' => '',
            'param' => 0
        ),
        14 => array(
            'text' => '到高雄逛夜市|週末K歌|玩冒險豔遇吸血鬼|滿13歲可以玩RC|給秀場主播送了愛心',
            'action' => 'move',
            'param' => 4
        ),
        15 => array(
            'text' => '睡覺中，免打擾|被下定身術，原地不動|見到不雅照，瞬間石化不動|途中遇大雨，暫停躲雨|天外飛來一球，倒地不起|遇到RC維護，休息一天',
            'action' => '',
            'param' => 0
        ),
        16 => array(
            'text' => '不扶老奶奶過馬路|騎機車摔傷住院|交不起房租|鬼屋遇到倒楣鬼|在公屏發不雅表情',
            'action' => 'move',
            'param' => -3
        ),
        17 => array(
            'text' => '在路邊撿到1000元 |收到巧克力糖果|萬聖節扮鬼嚇到鬼|玩RC遇到違規分子舉報成功|給賣上主播掌聲鼓勵',
            'action' => 'bonus',
            'param' => 1000
        ),
        18 => array(
            'text' => '睡覺中，免打擾|被下定身術，原地不動|見到不雅照，瞬間石化不動|途中遇大雨，暫停躲雨|天外飛來一球，倒地不起',
            'action' => '',
            'param' => 0
        ),
        19 => array(
            'text' => '中大樂透|前往香港看演唱會|考上世界名牌大學|給秀場主播送了萬聖節禮物|身手敏捷抓到小偷',
            'action' => 'move',
            'param' => 5
        ),
        20 => array(
            'text' => '掉進水溝就醫|你家小狗亂大小便|欺善怕惡|偷看18禁電影',
            'action' => 'move',
            'param' => -2
        ),
        21 => array(
            'text' => '在路邊撿到1000元 |收到巧克力糖果|萬聖節扮鬼嚇到鬼|玩RC遇到違規分子舉報成功|給賣上主播掌聲鼓勵',
            'action' => 'bonus',
            'param' => 1000
        ),
        22 => array(
            'text' => '騎機車未帶安全帽|這個月沒交房租|未滿18逛夜店|隨地扔垃圾|走在路上突然滾進水溝',
            'action' => 'move',
            'param' => -4
        ),
        23 => array(
            'text' => '睡覺中，免打擾|被下定身術，原地不動|見到不雅照，瞬間石化不動|途中遇大雨，暫停躲雨|天外飛來一球，倒地不起',
            'action' => '',
            'param' => 0
        )
    );

    private $rewardBox = array(
        array(
            'text' => '成功闖過10000關，獲得吸血鬼專屬徽章和905000魅力值',
            'score' => 10000,
            'charm' => 905000,
            'badge' => 171,
            'id' => 5
        ),
        array(
            'text' => '成功闖過3000關，獲得蝙蝠俠專屬徽章和565000魅力值',
            'score' => 3000,
            'charm' => 565000,
            'badge' => 170,
            'id' => 4
        ),
        array(
            'text' => '成功闖過600關，獲得小黑喵專屬徽章和335000魅力值',
            'score' => 600,
            'charm' => 335000,
            'badge' => 169,
            'id' => 3
        ),
        array(
            'text' => '成功闖過120關，獲得貓頭鷹專屬徽章和170000魅力值',
            'score' => 120,
            'charm' => 170000,
            'badge' => 168,
            'id' => 2
        ),
        array(
            'text' => '成功闖過24關，獲得骷髏頭專屬徽章和75000魅力值',
            'score' => 24,
            'charm' => 75000,
            'badge' => 167,
            'id' => 1
        ),
    );
    
    public function __construct ()
    {
        parent::__construct();
    }

    // 獲取棋盤配置
    public function getChess()
    {
        return $this->chess;
    }

    // 獲取關卡
    public function getRewardBox()
    {
        return $this->rewardBox;
    }

    //獲取附近的人的信息
    public function getPeopleAround($uid, $score)
    {
        $length = count($this->chess);
        $resultArray = array();
        $end = ceil($score / $length) * $length - 1;
        $start = $score + 1;
        if ($start <= $end) {
            $param = array('withscores' => true, 'limit' => array(0, 10));
            $list = $this->getRedisMaster()->zrangebyscore($this->sortedSetKey, $start, $end, $param);
            if (!empty($list)) {
                $userInfoModel = new UserInfoModel();
                foreach ($list as $key => $value) {
                    $nick = $userInfoModel->getNickName($key);
                    $position = $value % $length;
                    $resultArray[] = array(
                        'position' => $position,
                        'uid' => $key,
                        'nick' => $nick
                    );
                }
            }
        }
        return $resultArray;
    }

    // 搖骰子
    public function throwDice($uid)
    {
        $type = self::$luckyGamePoint;
        $userInfo = $this->getInfoByUid($uid);
        $num = rand(1, 6);
        $distance = $num;
        $score = $userInfo['score'] + $num;
        $newPosition = $score % count($this->chess);
        $chess = $this->chess[$newPosition];
        switch ($chess['action']) {
            case 'move':
                $distance = $num + $chess['param'];
                $score = $score + $chess['param'];
                break;
            case 'bonus':
                $userAttributeModel = new UserAttributeModel();
                $userAttributeModel->addGamePoint($uid, $chess['param'], self::$gameId, $type);
                break;
            default:
                break;
        }
        $this->getRedisMaster()->zIncrBy($this->sortedSetKey, $distance, $uid);
        $this->getRedisMaster()->zIncrBy($this->sortedSetKey . '_' . date('Ymd'), $distance, $uid);
        $returnArray = array(
            'diceNum' => $num,
            'action' => $chess,
            'peopleAround' => $this->getPeopleAround($uid, $score)
        );
        foreach ($this->rewardBox as $value) {
            if ($score >= $value['score'] && $userInfo['score'] < $value['score']) {
                $this->sendReward($uid, $value);
                $returnArray['boxId'] = $value['id'];
                $returnArray['boxName'] = $value['text'];
                break;
            }
        }
        return $returnArray;

    }

    // 取用戶信息
    public function getInfoByUid($uid)
    {
        $userAttrModel = new UserAttributeModel();
        $info = array();
        $count = count($this->chess);
        $info['rank'] = $this->getRank($this->sortedSetKey, $uid);
        $info['dateRank'] = $this->getRank($this->sortedSetKey . '_' . date('Ymd'), $uid);
        $info['score'] = $this->getScore($this->sortedSetKey, $uid);
        $info['gamePoint'] = $userAttrModel->getAttrByUid($uid, 'game_point');
        $info['position'] = $info['score'] % $count;
        $info['cycle'] = floor($info['score'] / $count) + 1;
        $info['contribution'] = $this->getScore($this->sendKey, $uid);
        return $info;
    }

    // 取排名
    public function getRank($key, $uid)
    {
        $rank = $this->getRedisSlave()->zRevRank($key, $uid);
        if ($rank === false) {
            return false;
        } else {
            return $rank + 1;
        }
    }

    // 取關卡分數
    public function getScore($key, $uid)
    {
        $score = $this->getRedisSlave()->zScore($key, $uid);
        if (empty($score)) {
            return 0;
        } else {
            return $score;
        }
    }

    public function buyGamePoint($uid, $point)
    {
        $type = self::$boughtGamePoint;
        $coin = $point * $this->gamePointPrice;
        $userAttributeModel = new UserAttributeModel();
        return $userAttributeModel->addGamePoint($uid, $point, self::$gameId, $type, $coin);
    }

    public function sendReward($uid, $reward)
    {
        $userAttributeModel = new UserAttributeModel();
        if (!empty($reward['charm'])) {
            $userAttributeModel->addExperienceByUid($uid, $reward['charm']);
        }
        if (!empty($reward['badge'])) {
            $this->sendBadge($uid, $reward['badge']);
        }
        if ($reward['id'] == 5) {
            $toolSubsModel = new ToolSubscriptionModel();
            $toolSubsModel->update($uid, 157, 30);
        }
    }
}