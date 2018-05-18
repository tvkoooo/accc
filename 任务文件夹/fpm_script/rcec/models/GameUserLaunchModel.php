<?php

class GameUserLaunchModel extends ModelBase
{
    public static $SINGER_USERLAUNCH_GAME_MOD_NUMBER = 1024;
    public static $SINGER_USERLAUNCH_SELECT_TIMEOUT = 180;// 3 * 60
    
    public static $SINGER_USERLAUNCH_SELECT_COST_ITEM_NUM = 1;// 主播发起消耗特殊道具数量
    public static $SINGER_USERLAUNCH_SELECT_COST_ITEM_TYPE = 18;// 主播发起消耗特殊道具种类
    
    // public static $GAME_ID_GUESS = 4;
    // public static $GAME_ID_DICE = 5;
    // public static $GAME_ID_DEFEND = 6;
    public static $SINGER_USERLAUNCH_MAP = array
    (
        '4'=>21,// $GAME_ID_GUESS
        '5'=>22,// $GAME_ID_DICE
        // '6'=>00,// $GAME_ID_DEFEND
    );
  
    public static function HashSingerUserlaunchGameLaunchKey($sid)
    {
        return "singer:userlaunch:game:launch:$sid";
    }
    public static function HashSingerUserlaunchGameSelectKey($sid)
    {
        $mod = $sid % GameUserLaunchModel::$SINGER_USERLAUNCH_GAME_MOD_NUMBER;        
        return "singer:userlaunch:game:select:$mod";
    }
    public static function LaunchItemInit(&$launch)
    {
        $launch['launchid'] = (int)(0);
        $launch['gameid'] = (int)(0);
        $launch['timecode'] = (int)(0);
    
        $launch['isguard'] = (int)(0);
    
        $launch['nick'] = (string)("");
        $launch['photo'] = (string)("");
    
        $launch['consume_level'] = (int)(int)(0);
        $launch['active_level'] = (int)(int)(0);
    
        $launch['gamename'] = (string)("");
        $launch['gameimgurl'] = (string)("");
    }

    public function UserLaunchSubmit($singerid,$sid,$uid,$gameid,$timecode,&$launch)
    {
        $key = GameUserLaunchModel::HashSingerUserlaunchGameLaunchKey($sid);
        $member = (string)($uid.':'.$gameid);
        
        $uim = new UserInfoModel();
        $user_info = $uim->getInfoById($uid);
        $uam = new UserAttributeModel();
        $user_attr = $uam->getAttrByUid($uid);
        $gm = new GameModel();
        $game_info = $gm->getGameInfo($gameid);
        
        GameUserLaunchModel::LaunchItemInit(&$launch);
        $launch['launchid'] = $uid;
        $launch['gameid'] = $gameid;
        $launch['timecode'] = $timecode;      
        
        $launch['isguard'] = (int)($this->GetUserIsSingerGuard($singerid,$uid));
        
        $launch['nick'] = (string)(empty($user_info['nick']) ? "" : $user_info['nick']);
        $launch['photo'] = (string)(empty($user_info['photo']) ? "" : $user_info['photo']);
        
        $launch['consume_level'] = (int)(empty($user_attr['consume_level']) ? 0 : $user_attr['consume_level']);
        $launch['active_level'] = (int)(empty($user_attr['active_level']) ? 0 : $user_attr['active_level']);
        
        $launch['gamename'] = (string)(empty($game_info['name']) ? "" : $game_info['name']);
        $launch['gameimgurl'] = (string)(empty($game_info['img_name']) ? "" : $game_info['img_name']);
        $this->getRedisMaster()->hSet($key,$member,json_encode($launch));
    }
    public function GetUserIsSingerGuard($singerid,$uid)
    {
        $singerGuardModel = new SingerGuardModel();
        $endTime = $singerGuardModel->getGuardEndTime($uid, $singerid);
        $guardType = $singerGuardModel->getGuardType($uid, $singerid);
        
        $now = time();
        $isguard = 0 ;
        if (!empty($endTime) && $endTime > $now)
        {
            if(1 == $guardType || 2 == $guardType || 3 == $guardType){
                //守护有效
                $isguard = 1;
            }
        }
        return $isguard;
    }
    public function SingerSelectSubmit($sid,$launchid,$gameid,$timecode_submit,$timecode_select)
    {
        $key = GameUserLaunchModel::HashSingerUserlaunchGameSelectKey($sid);
        $value = $this->getRedisMaster()->hGet($key,$sid);
        
        $l_obj = array();
        $l_obj['launchid'] = $launchid;
        $l_obj['gameid'] = $gameid;
        $l_obj['timecode_submit'] = $timecode_submit;
        $l_obj['timecode_select'] = $timecode_select;
        
        $this->getRedisMaster()->hSet($key,$sid,json_encode($l_obj));
    }
    public function UserEnter($sid,$uid)
    {
        // here do nothing.
    }
    public function UserLeave($sid,$uid)
    {
        {
            $key = GameUserLaunchModel::HashSingerUserlaunchGameLaunchKey($sid);
            foreach (GameUserLaunchModel::$SINGER_USERLAUNCH_MAP as $gameid=>$goodsid)
            {
                $member = (string)($uid.':'.$gameid);
                $this->getRedisMaster()->hDel($key,$member);
            }
        }
        {
            $key = GameUserLaunchModel::HashSingerUserlaunchGameSelectKey($sid);
            $value = $this->getRedisMaster()->hGet($key,$sid);
            do 
            {
                if (empty($value))
                {
                    break;
                }
                $l_obj = json_decode($value);
                if ($l_obj->launchid != $uid)
                {
                    break;
                }                
                $this->getRedisMaster()->hDel($key,$sid);
            }while(FALSE);
        }
    }
    public function SingerEnter($sid,$uid)
    {
        // here do nothing.
    }
    public function SingerLeave($sid,$uid)
    {
        {
            $key = GameUserLaunchModel::HashSingerUserlaunchGameLaunchKey($sid);
            $this->getRedisMaster()->del($key);
        }
        {
            $key = GameUserLaunchModel::HashSingerUserlaunchGameSelectKey($sid);
            $this->getRedisMaster()->hDel($key,$sid);
        }
    }
    public function ApplyUserPermission($sid,$uid,$gameid)
    {
        {
            $key = GameUserLaunchModel::HashSingerUserlaunchGameLaunchKey($sid);
            $member = (string)($uid.':'.$gameid);
            $this->getRedisMaster()->hDel($key,$member);
        }
        {
            $key = GameUserLaunchModel::HashSingerUserlaunchGameSelectKey($sid);
            $value = $this->getRedisMaster()->hGet($key,$sid);
            do
            {
                if (empty($value))
                {
                    break;
                }
                $l_obj = json_decode($value);
                if ($l_obj->launchid != $uid)
                {
                    break;
                }
                $this->getRedisMaster()->hDel($key,$sid);
            }while(FALSE);
        }        
    }
    public function GetSingerUserlaunchGameLaunch($sid,$uid,$gameid)
    {
        $l_obj = NULL;
        $key = GameUserLaunchModel::HashSingerUserlaunchGameLaunchKey($sid);
        $member = (string)($uid.':'.$gameid);
        do
        {
            $value = $this->getRedisMaster()->hGet($key,$member);
            if (empty($value))
            {
                // not flag here.
                break;
            }
            $l_obj = json_decode($value);
        }while(FALSE);
        return $l_obj;        
    }
    // $sid room id.
    // $uid submit user.    
    // return 0 success.
    // -1 unknown error.
    // >0 error occur.
    public function CheckUserPermission($sid,$uid,$gameid,$timecode_now)
    {
        $errcode = -1;
        $key = GameUserLaunchModel::HashSingerUserlaunchGameSelectKey($sid);
        do
        {
            $value = $this->getRedisMaster()->hGet($key,$sid);
            if (empty($value))
            {
                // not flag here.
                $errcode = 1;
                break;
            }
            $l_obj = json_decode($value);
            if (NULL == $l_obj)
            {
                // obj is NULL.
                $errcode = 2;
                break;
            }
            if ($l_obj->launchid != $uid)
            {
                // uid not match.
                $errcode = 3;
                break;
            }
            if ($l_obj->gameid != $gameid)
            {
                // gameid not match.
                $errcode = 4;
                break;
            }
            $dt = $timecode_now - $l_obj->timecode_select;
            if ($dt >= GameUserLaunchModel::$SINGER_USERLAUNCH_SELECT_TIMEOUT)
            {
                // timeout permission invalid.
                $errcode = 5;
                break;
            }
            $errcode = 0;
        }while(FALSE);
        return $errcode;
    }
    // return 0 success.
    // -1 unknown error.
    // >0 error occur. 
    public function CheckingUserLaunchSubmit($sid,$uid,$gameid,$timecode_now)
    {
        $errcode = -1;
        $key = GameUserLaunchModel::HashSingerUserlaunchGameLaunchKey($sid);
        $member = (string)($uid.':'.$gameid);
        do
        {
            $value = $this->getRedisMaster()->hGet($key,$member);
            if (empty($value))
            {
                // not flag here.
                $errcode = 0;
                break;
            }
            $l_obj = json_decode($value);
            if (NULL == $l_obj)
            {
                // obj is NULL.
                $errcode = 0;
                break;
            }
            $dt = $timecode_now - $l_obj->timecode;
            if ( GameUserLaunchModel::$SINGER_USERLAUNCH_SELECT_TIMEOUT <= $dt )
            {
                // not timeout select invalid.
                $errcode = 0;
                break;
            }
            $errcode = 1;
        }while(FALSE);
        return $errcode;
    }
    // return 0 success.
    // -1 unknown error.
    // >0 error occur.
    public function CheckingSingerSelectSubmit($sid,$timecode_now)
    {
        $errcode = -1;
        $key = GameUserLaunchModel::HashSingerUserlaunchGameSelectKey($sid);
        do
        {
            $value = $this->getRedisMaster()->hGet($key,$sid);
            if (empty($value))
            {
                // not flag here.
                $errcode = 0;
                break;
            }
            $l_obj = json_decode($value);
            if (NULL == $l_obj)
            {
                // obj is NULL.
                $errcode = 0;
                break;
            }
            $dt = $timecode_now - $l_obj->timecode_select;
            if ( GameUserLaunchModel::$SINGER_USERLAUNCH_SELECT_TIMEOUT <= $dt )
            {
                // not timeout select invalid.
                $errcode = 0;
                break;
            }
            $errcode = 1;
        }while(FALSE);
        return $errcode;
    }
    // return 0 success.
    // -1 unknown error.
    // >0 error occur.
    public function CheckingItemMysqlDB($uid,$gameid,$delta)
    {
        $errcode = -1;
        do
        {
            if (0 == $delta)
            {
                // update nothing.
                $errcode = 0;
                break;
            }
            $goods_id = GameUserLaunchModel::$SINGER_USERLAUNCH_MAP[$gameid];
            if (empty($goods_id))
            {
                // not valid item.
                LogApi::logProcess("GameUserLaunchModel.CheckingItemMysqlDB not valid item gameid:".$gameid);
                $errcode = 1;
                break;
            }
            $goods_type = GameUserLaunchModel::$SINGER_USERLAUNCH_SELECT_COST_ITEM_TYPE;
        
            $sql = "SELECT num FROM card.user_goods_info WHERE ( uid = $uid && goods_id = $goods_id )";
        
            $rows = $this->getDbRecord()->query($sql);
            if(!$rows || $rows->num_rows <= 0)
            {
                LogApi::logProcess("GameUserLaunchModel.CheckingItemMysqlDB excute sql error.sql:$sql");
                $errcode = 2;
                break;
            }
            $row = $rows->fetch_assoc();
            $num = $row['num'];
            if ($delta > $num)
            {
                // not enough item.
                $errcode = 3;
                break;
            }
            $errcode = 0;
        }while(FALSE);
        return $errcode;
    }
    // return 0 success.
    // -1 unknown error.
    // >0 error occur.
    public function UpdateItemMysqlDB($uid,$gameid,$delta)
    {
        $errcode = -1;
        do 
        {
            if (0 == $delta)
            {
                // update nothing.
                $errcode = 0;
                break;
            }
            $goods_id = GameUserLaunchModel::$SINGER_USERLAUNCH_MAP[$gameid];
            if (empty($goods_id))
            {
                // not valid item.
                LogApi::logProcess("GameUserLaunchModel.UpdateItemMysqlDB not valid item gameid:".$gameid);
                $errcode = 1;
                break;
            }
            $goods_type = GameUserLaunchModel::$SINGER_USERLAUNCH_SELECT_COST_ITEM_TYPE;
            $sql = "UPDATE card.user_goods_info SET num = num - $delta WHERE ( uid = $uid && goods_id = $goods_id && goods_type = $goods_type && num >= $delta )";   
            $rows = $this->getDbRecord()->query($sql);
            if(!$rows)
            {
                LogApi::logProcess("GameUserLaunchModel.UpdateItemMysqlDB excute sql error.sql:$sql");
                $errcode = 2;
                break;
            }
            LogApi::logProcess("GameUserLaunchModel.UpdateItemMysqlDB update uid:".$uid." goods_id:".$goods_id." delta".$delta);
            $errcode = 0;
        }while(FALSE);
        return $errcode;
    }
}
?>
