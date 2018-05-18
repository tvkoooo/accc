<?php

class UserRoomTimeTotalModel extends ModelBase
{
    public static $LORD_USER_MOD_NUMBER = 1024;
    public static $USER_ROOM_TIME_MOD_NUMBER = 1024;
    public static $USER_ROOM_TIME_KEY_TTL = 259200;// 3 * 24 * 60 * 60
    public static $SUN_DROP_AWARD_TTL = 604800;// 7 * 24 * 60 * 60
    public static $SINGER_IDENTITY = 2;// 2 主播
    public static $LORD_COUNT_TTL = 259200;// 3 * 24 * 60 * 60
    
    //public $a = 900;// 秒 15 * 60
    public $a = 120;// 秒 2 * 60
    public $b = 8;// 每天8次掉落阳光
    
    public static function LordUserSunshuneValue($value)
    {
        return $value * 2;
    }
    public static function HashLordUserKey($sid)
    {
        $mod = $sid % UserRoomTimeTotalModel::$LORD_USER_MOD_NUMBER;
        return "lord:room:$mod";
    }
    public static function ZsetCountLoadLightKey($sid)
    {
        return "lord:count:light:$sid";
    }
    public static function HashUserRoomTotalUpdateTime($uid)
    {
        $mod = $uid % UserRoomTimeTotalModel::$USER_ROOM_TIME_MOD_NUMBER;
        return "user:room:total:updatetime:$mod";
    }
    public static function HashUserRoomTotalRealTime($uid)
    {
        $mod = $uid % UserRoomTimeTotalModel::$USER_ROOM_TIME_MOD_NUMBER;
        return "user:room:total:realtime:$mod";
    }
    public static function ListUserSunTimes($uid)
    {
        return "user_sun_times:$uid";
    }
    public static function ListUserSunTimesTmp($uid)
    {
        return "user_sun_times_tmp:$uid";
    }
    public function InitConfigDB()
    {
        
    }
    public function GetUserRoomTotalTime($uid,$timecode_now)
    {
        $cdt_key = UserRoomTimeTotalModel::HashUserRoomTotalUpdateTime($uid);
        $real_key = UserRoomTimeTotalModel::HashUserRoomTotalRealTime($uid);
        $redis = $this->getRedisMaster();
        
        $timecode_last = $redis->hGet($cdt_key,$uid);
        if (empty($timecode_last)){$timecode_last = $timecode_now;}        
        $real_time = $redis->hGet($real_key,$uid);
        if (empty($real_time)){$real_time = 0;}
        $dt = $timecode_now - $timecode_last;
        return $real_time + $dt;
    }
    public function GetNoRecvSunValue($uid,$accept)
    {
        //保存的次数
        $key = UserRoomTimeTotalModel::ListUserSunTimesTmp($uid);
        // $data_arr = $this->getRedisMaster()->lrange($key,0,$this->getRedisMaster()->llen($key));
        $data_arr = $this->getRedisMaster()->lrange($key,0,-1);
        
        LogApi::logProcess("UserRoomTimeTotalModel.GetNoRecvSunValue key:$key, lrange return:".json_encode($data_arr));
        //把阳光值加入到数据库中
        $value = 0;
        foreach ($data_arr as $data)
        {
            $l_obj = json_decode($data);
            LogApi::logProcess("UserRoomTimeTotalModel.GetNoRecvSunValue data :$data");
            // {"sid":101015,"islord":1,"sunvalue":29}
            if(
                !property_exists($l_obj,"sid") ||
                !property_exists($l_obj,"islord") ||
                !property_exists($l_obj,"sunvalue"))
            {
                // old version.
                $value += (int)$data;
            }
            else
            {
                // new version.
                $sid = (int)$l_obj->sid;
                $islord = (int)$l_obj->islord;
                $sunvalue = (int)$l_obj->sunvalue;
                if ( 1 == $islord )
                {
                    $sunvalue_new = UserRoomTimeTotalModel::LordUserSunshuneValue($sunvalue);
                    $dt_value = $sunvalue_new - $sunvalue;
                    $sunvalue = $sunvalue_new;
                    if(1 == $accept)
                    {
                        // count data.
                        $key_light = UserRoomTimeTotalModel::ZsetCountLoadLightKey($sid);
                        $this->getRedisMaster()->zIncrBy($key_light,$dt_value,$uid);
                        $this->getRedisMaster()->expire($key_light,UserRoomTimeTotalModel::$LORD_COUNT_TTL);
                    }                    
                }
        
                $value += $sunvalue;
            }
        }
        return $value;
    }
    //获得阳光日产出值
    public function GetSunBaseValue($uid)
    {
        //TODO:从周哥处获得
        $key = "userCharm:$uid";
        $base = $this->getRedisMaster()->get($key);
    
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
    
        $activeLevel = (int)$userAttr['active_level'];
        $query = "select t.id, t.parm3 from card.parameters_info t where t.id in (30, 31, 32, 33, 34, 35)";
        $rows = $this->getDbMain()->query($query);
    
        $a = 1;
        $b = 30;
        $c = 120;
        $x = 0;
        $y = 0.3;
        $z = 1;
        while ($row = $rows->fetch_assoc()) 
        {
            $id = (int)$row['id'];
            $value = (int)$row['parm3'];
            switch ($id)
            {
                case 30: //
                    $a = $value;
                    break;
                case 31: //
                    $b = $value;
                    break;
                case 32: //
                    $c = $value;
                    break;
                case 33: //
                    $x = $value;
                    break;
                case 34: //
                    $y = $value;
                    break;
                case 35: //
                    $z = $value;
                    break;
            }
        }
    
        //获得阳光加成值
        $sunjc = 0;
        if($activeLevel > $b)
        {
            $sunjc = $y+($activeLevel-$b)*($z - $y)/($c-$b);
        }
        else
        {
            $sunjc = ($activeLevel-$a)*($y-$x)/($b-$a)+$x;
        }
    
        $totalValue = $base*(1+$sunjc);
    
        return $totalValue;
    }
    //产生阳光
    public function CreateSunShine($return,$uid,$sid)
    {
        do 
        {
            $userInfo = new UserInfoModel();
            $user = $userInfo->getInfoById($uid);
            $identity = (int)$user['identity'];
            //主播不产生阳光
            if(UserRoomTimeTotalModel::$SINGER_IDENTITY == $identity)
            {
                break;
            }
            
            //保存的次数
            $sunTimesKey = UserRoomTimeTotalModel::ListUserSunTimes($uid);
            //用于领取阳光用
            $sunTimesKey_tmp = UserRoomTimeTotalModel::ListUserSunTimesTmp($uid);
            $times = $this->getRedisMaster()->llen($sunTimesKey);
            if(empty($times)){ $times = 0; }
            //暂时注释掉
            $limit = $this->b;
            if($times >= $limit)
            {
                LogApi::logProcess("UserRoomTimeTotalModel.CreateSunShine error*****userid:$uid out $limit times.");
                break;
            }
            /* $sunFloatKey = "user_sunfloat:$uid";
             $sunfloat = getRedisMaster()->get($sunFloatKey);
             if(empty($sunfloat)){
             $sunfloat = 0;
             } */
            //获得日产出量
            $baseSun = $this->GetSunBaseValue($uid);
            
            $sql = "select t.parm3 as ratio from card.parameters_info t where t.id = 48";
            $rows = $this->getDbMain()->query($sql);
            if (!$rows) 
            {
                LogApi::logProcess("UserRoomTimeTotalModel.CreateSunShine error user($uid), sql:$sql");
                break;
            }
            $row = $rows->fetch_assoc();
            $ratio = floatval($row['ratio']);
            //$times从0开始
            $sunvalue = (0.125-3.5*$ratio+$times*$ratio)*$baseSun;//+floatval($sunfloat/100);
            
            $sunvalue = ceil($sunvalue);
            /* $sunfloat = number_format($sunvalue, 2);
             $sunfloat = end(explode('.', $sunfloat)); */
            
            //getRedisMaster()->set($sunFloatKey, $sunfloat);
            /* //用户阳光总值key
             $sunKey = "user_sunvalue:$uid";
             getRedisMaster()->zIncrBy($sunKey, $sunvalue, $uid); */
            
            //         $query = "UPDATE rcec_main.user_attribute SET sun_num = sun_num + $sunvalue WHERE uid = $uid";
            
            //         $rs1 = getDbMain()->query($query);
            //         if (!$rs1) {
            //             LogApi::logProcess("***CreateSunShine error*****user($uid), sql:$query");
            //         }
            
            $redisKey = "user_attribute:{$uid}";
            $this->getRedisMaster()->del($redisKey);
            
            //每次保存的阳光数
            $this->getRedisMaster()->lpush($sunTimesKey, $sunvalue);
            //TODO:如果是擂主，则产生阳光翻倍（擂主需要在session 的 Channel::setBattleArenaWinner方法里设置擂主uid：_curBattleArenaWinnerUid）
            $lord_user_key = UserRoomTimeTotalModel::HashLordUserKey($sid);
            $lord_id = $this->getRedisMaster()->hGet($lord_user_key,$sid);
            if (empty($lord_id)){$lord_id = 0;}
            $islord = ( $uid == $lord_id && 0 != $sid ) ? 1 : 0;
            $sunvalue_finally = $sunvalue;
            if (1 == $islord)
            {
                $sunvalue_finally = UserRoomTimeTotalModel::LordUserSunshuneValue($sunvalue);
            }
            $l_obj = array
            (
                'sid'=>$sid,
                'islord'=>$islord,
                'sunvalue'=>$sunvalue,
            );
            // $this->getRedisMaster()->lpush($sunTimesKey_tmp, $sunvalue);
            $this->getRedisMaster()->lpush($sunTimesKey_tmp, json_encode($l_obj));
            $this->getRedisMaster()->expire($sunTimesKey,UserRoomTimeTotalModel::$SUN_DROP_AWARD_TTL);
            $this->getRedisMaster()->expire($sunTimesKey_tmp,UserRoomTimeTotalModel::$SUN_DROP_AWARD_TTL);
            
            LogApi::logProcess("UserRoomTimeTotalModel.CreateSunShine ".json_encode($l_obj));
            
            //向前端返回本次产生的阳光数
            $return[] = array
            (
                'data' => array
                (
                    'cmd' => 'BCreateSunshune',
                    'uid' => (int)$uid,
                    'sun_num' => $sunvalue_finally,
                    'isRoom' => true,
                ),
            );
        }while(FALSE);
    }
    //用户领取阳光
    public function AcceptSunValue($uid)
    {
        //保存的次数
        $key = UserRoomTimeTotalModel::ListUserSunTimesTmp($uid);
        // $data_arr = $this->getRedisMaster()->lrange($key,0,$this->getRedisMaster()->llen($key));
        $data_arr = $this->getRedisMaster()->lrange($key,0,-1);
    
        LogApi::logProcess("UserRoomTimeTotalModel.AcceptSunValue :: key:$key, lrange return:".json_encode($data_arr));
        //把阳光值加入到数据库中
        $value = $this->GetNoRecvSunValue($uid,1);
        if (0 < $value)
        {
            $sql = "UPDATE rcec_main.user_attribute SET sun_num = sun_num + $value WHERE uid =$uid";
            $rows = $this->getDbChannellive()->query($sql);
            if (!$rows) 
            {
                LogApi::logProcess("UserRoomTimeTotalModel.AcceptSunValue exe sql error, sql:$sql");
                return false;
            }
            $userkey = "user_attribute:{$uid}";
            $this->getRedisMaster()->del($userkey);
        }
        $this->getRedisMaster()->del($key);
    }
    public function AppendItemToRedisDB($return,$sid,$uid,$number)
    {
        $redis = $this->getRedisMaster();
        for ($x=0; $x<=$number; $x++)
        {
            if ($uid == 10001094)
            {
                $sunTimesKey = UserRoomTimeTotalModel::ListUserSunTimes($uid);
                $redis->lPush($sunTimesKey,1);
            }
            // $this->CreateSunShine($return,$uid,$sid);
        }
    }
    public function Update($return,$uid,$sid,$timecode_start,$timecode_now)
    {
        $redis = $this->getRedisMaster();
        $room_member_info_key = member_list::HashRoomMemberInfoKey($sid);
        // 主播
        // $this->UpdateUser($return,$uid, $sid, $timecode_start, $timecode_now);
        $members = $redis->hGetAll($room_member_info_key);
        if (!empty($members))
        {
            foreach ($members as $member => $timecode)
            {
                // 看官
                $this->UpdateUser($return,$member, $sid, $timecode, $timecode_now);
            }            
        }
    }
    public function UpdateUser($return,$uid,$sid,$timecode_start,$timecode_now)
    {
        $redis = $this->getRedisMaster();
        $cdt_key = UserRoomTimeTotalModel::HashUserRoomTotalUpdateTime($uid);
        $timecode_last = $redis->hGet($cdt_key,$uid);
        if (empty($timecode_last)){$timecode_last = $timecode_start;}
        $timecode_later = $timecode_start > $timecode_last ? $timecode_start : $timecode_last;
        LogApi::logProcess("UserRoomTimeTotalModel.UpdateUser"." uid:".$uid." sid:".$sid." timecode_now:".$timecode_now." timecode_start:".$timecode_start." timecode_last:".$timecode_last." timecode_later:".$timecode_later);
        // dt time to total.
        $dt = $timecode_now - $timecode_later;
        if (0 < $dt)
        {
            $n_a = floor((int)$dt / $this->a);
            $n_b = floor((int)$dt % $this->a);
            $real_dt = $n_a * $this->a;
            $timecode_curr = $timecode_later + $real_dt;
            $redis->hSet($cdt_key,$uid,$timecode_curr);
            $redis->expire($cdt_key,UserRoomTimeTotalModel::$USER_ROOM_TIME_KEY_TTL);
            $real_key = UserRoomTimeTotalModel::HashUserRoomTotalRealTime($uid);
            $redis->hIncrBy($real_key,$uid,$real_dt);
            // append item $n_a.
            $this->AppendItem($return,$uid,$sid,$n_a,$timecode_now);
            
            LogApi::logProcess("UserRoomTimeTotalModel.UpdateUser"." uid:".$uid." sid:".$sid." n_a:".$n_a." dt:".$dt." a:".$this->a." n:".$dt / $this->a);
        }
    }
    public function AppendItem($return,$uid,$sid,$number,$timecode)
    {
        if (0 < $number)
        {
            $redis = $this->getRedisMaster();
            $cdlr_key = UserRoomTimeTotalModel::ListUserSunTimesTmp($uid);
            $cdlr_val = $redis->lLen($cdlr_key);
            if (empty($cdlr_val)){$cdlr_val = 0;}
            
            $cdlr_rel = $cdlr_val + $number;
            
            $cdlr_las = $cdlr_rel >= $this->b ? $this->b : $cdlr_rel;
            
            $cdlr_dtv = $cdlr_las - $cdlr_val;
            // min($cdlr_dtv,$cdld_dtv)
            $number_dtv = $cdlr_dtv;           
            LogApi::logProcess("UserRoomTimeTotalModel.AppendItem"." uid:".$uid." sid:".$sid." number_dtv:".$number_dtv." cdlr_las:".$cdlr_las." cdlr_val:".$cdlr_val." cdlr_rel:".$cdlr_rel);
            if (0 != $number_dtv )
            {
                $this->AppendItemToRedisDB($return,$sid,$uid,$number);
            }           
        }
    }
}