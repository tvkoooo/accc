<?php

class ChestDebrisGenerate extends ModelBase
{
    public static $CHEST_DEBRIS_MOD_NUMBER = 1024;
    public static $CHEST_DEBRIS_KEY_TTL = 259200;// 3 * 24 * 60 * 60
    
    // a =  6 id 64
    // b = 20 id 97
    // c = 60 id 65
    public $a = 360;// 秒 6 * 60
    public $b =  20;// room
    public $c =  60;// day
    
    public function InitConfigDB()
    {
        $id_1 = 65;
        $id_2 = 97;
        $id_3 = 64;
        // select id,parm1,parm2,parm3 from card.parameters_info where id >= 82 && id <= 90;
        $sql = "select id,parm1,parm2,parm3 from card.parameters_info where id = $id_1 || id = $id_2 || id = $id_3";
        $rows = $this->getDbMain()->query($sql);
        $db_array = array();
        if ( $rows )
        {
            if ( 0 < $rows->num_rows )
            {
                for ($x=0; $x<$rows->num_rows; $x++)
                {
                    $row = $rows->fetch_assoc();
                    // 0  1     2     3
                    // id,parm1,parm2,parm3
                    $db_array[$row['id']] = array('parm1'=>$row['parm1'],'parm2'=>$row['parm2'],'parm3'=>$row['parm3']);
                }
                if(isset($db_array['64'])){$this->a = intval($db_array['64']['parm1']) * 60;}
                if(isset($db_array['97'])){$this->b = intval($db_array['97']['parm1']);}
                if(isset($db_array['65'])){$this->c = intval($db_array['65']['parm1']);}
                
                LogApi::logProcess("a:".$this->a." b:".$this->b." c:".$this->c);
            }
        }
        else
        {
            LogApi::logProcess("ChestDebrisGenerate.InitConfigDB::****************sql:$sql");
        }
        LogApi::logProcess("ChestDebrisGenerate.InitConfigDB::**************** db_array:".json_encode($db_array));
    }
    public function GetItemMysqlDB($uid)
    {
        $value = 0;
        do
        {
            if (0 == $uid)
            {
                // uid is invalid.
                break;
            }    
            $sql = "SELECT debris FROM rcec_main.user_attribute WHERE ( uid = $uid )";
    
            $rows = $this->getDbMain()->query($sql);
            if(!$rows || $rows->num_rows <= 0)
            {
                LogApi::logProcess("ChestDebrisGenerate.CheckingItemMysqlDB excute sql error.sql:$sql");
                break;
            }
            $row = $rows->fetch_assoc();
            $value = $row['debris'];
        }while(FALSE);
        return $value;
    }
    public function AppendItemToDB($uid,$number)
    {
        $url = GlobalConfig::GetUrlCache();
        $url = $url . "/cache/updateUserAttributes?sourceCode=watch";

        $ch = curl_init();
        $curl_opt = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT_MS => 1000,
                CURLOPT_HTTPHEADER => array("Content-Type: application/json"),
                CURLOPT_POSTFIELDS => json_encode(array("uid"=>intval($uid),"debris"=>intval($number)))
        );
        curl_setopt_array($ch, $curl_opt);
        $data = curl_exec($ch);
        curl_close($ch);
        LogApi::logProcess("ChestDebrisGenerate:AppendItemToDB rs:$data rq:" . json_encode($curl_opt));
        return;

        if (empty($number)) {
            return;
        }

        $model_sysparm = new SysParametersModel();

        $limit_upper_total = $model_sysparm->GetSysParameters(254, 'parm1');
        $limit_upper_single = $model_sysparm->GetSysParameters(256, 'parm1');

        $limit_upper_total = empty($limit_upper_total) ? 200 : $limit_upper_total;
        $limit_upper_single = empty($limit_upper_single) ? 100 : $limit_upper_single;

        if ($number > $limit_upper_single) {
            LogApi::logProcess("ChestDebrisGenerate AppendItemToDB [Exception] uid:$uid number:$number limit_upper_single:$limit_upper_single");
            return;
        }

        $sql = "SELECT debris FROM rcec_main.user_attribute WHERE uid=$uid";
        $rows = $this->getDbMain()->query($sql);

        if (empty($rows) || $rows->num_rows == 0) {
            LogApi::logProcess("ChestDebrisGenerate AppendItemToDB failure. sql:$sql");
            return;
        }

        $row = $rows->fetch_assoc();
        $debris_cur = $row['debris'];

        $real_add = $number;
        $real_total = $debris_cur + $number;
        if ($real_total > $limit_upper_total) {
            $real_add = $limit_upper_total - $debris_cur;
            $real_total = $limit_upper_total;
            LogApi::logProcess("ChestDebrisGenerate AppendItemToDB [Exception] uid:$uid number:$number number_cur:$debris_cur limit_upper_total:$limit_upper_total real_add:$real_add total_after_add:$real_total");
        } else {
            $real_add = $number;
            LogApi::logProcess("ChestDebrisGenerate AppendItemToDB [Normal] uid:$uid number:$number number_cur:$debris_cur limit_upper_total:$limit_upper_total real_add:$real_add total_after_add:$real_total");
        }

        $sql = "UPDATE rcec_main.user_attribute SET debris = $real_total WHERE uid = $uid";
        $rows = $this->getDbMain()->query($sql);
        if (empty($rows)) {
            LogApi::logProcess("ChestDebrisGenerate AppendItemToDB failure. sql:$sql");
        }
    }
    public function GetTimecodeLast($uid)
    {
        $value = time();
        $sql = "select time from channellive.live_notify where uid = $uid";
        $rows = $this->getDbMain()->query($sql);
        if ( $rows )
        {
            if ( 0 < $rows->num_rows )
            {
                $row = $rows->fetch_assoc();
                $value = $row['time'];
            }
        }
        else
        {
            LogApi::logProcess("ChestDebrisGenerate.GetTimecodeLast::****************sql:$sql");
        }
        LogApi::logProcess("ChestDebrisGenerate.GetTimecodeLast:: value:".$value);
    }
    public static function HashChestDebrisLimitRoom($timecode,$uid)
    {
        $timeindex = date('Y:m:d',$timecode);
        return "chest:debris:limit:room:$timeindex:$uid";
    }
    public static function HashChestDebrisLimitDay($timecode,$uid)
    {
        $mod = $uid % ChestDebrisGenerate::$CHEST_DEBRIS_MOD_NUMBER;
        $timeindex = date('Y:m:d',$timecode);
        return "chest:debris:limit:day:$timeindex:$mod";
    }
    public static function HashChestDebrisTime($uid)
    {
        $mod = $uid % ChestDebrisGenerate::$CHEST_DEBRIS_MOD_NUMBER;
        return "chest:debris:time:$mod";
    }
    public function Update($uid,$sid,$timecode_start,$timecode_now)
    {
        $redis = $this->getRedisMaster();
        $room_member_info_key = member_list::HashRoomMemberInfoKey($sid);
        // 主播
        $this->UpdateUser($uid, $sid, $timecode_start, $timecode_now);
        $members = $redis->hGetAll($room_member_info_key);
        if (!empty($members))
        {
            foreach ($members as $member => $timecode)
            {
                // 看官
                $this->UpdateUser($member, $sid, $timecode, $timecode_now);
            }            
        }
    }
    public function UpdateUser($uid,$sid,$timecode_start,$timecode_now)
    {
        $redis = $this->getRedisMaster();
        $cdt_key = ChestDebrisGenerate::HashChestDebrisTime($uid);
        $timecode_last = $redis->hGet($cdt_key,$uid);
        if (empty($timecode_last)){$timecode_last = $timecode_start;}
        $timecode_later = $timecode_start > $timecode_last ? $timecode_start : $timecode_last;
        // LogApi::logProcess("ChestDebrisGenerate.UpdateUser"." uid:".$uid." sid:".$sid." timecode_now:".$timecode_now." timecode_start:".$timecode_start." timecode_last:".$timecode_last." timecode_later:".$timecode_later);
        // dt time to total.
        $dt = $timecode_now - $timecode_later;
        if (0 < $dt)
        {
            $n_a = floor((int)$dt / $this->a);
            $n_b = floor((int)$dt % $this->a);
            $real_dt = $n_a * $this->a;
            $timecode_curr = $timecode_later + $real_dt;
            $redis->hSet($cdt_key,$uid,$timecode_curr);
            $redis->expire($cdt_key,ChestDebrisGenerate::$CHEST_DEBRIS_KEY_TTL);
            // append item $n_a.
            $this->AppendItem($uid,$sid,$n_a,$timecode_now);
            
            // LogApi::logProcess("ChestDebrisGenerate.UpdateUser"." uid:".$uid." sid:".$sid." n_a:".$n_a." dt:".$dt." a:".$this->a." n:".$dt / $this->a);
        }
    }
    public function AppendItem($uid,$sid,$number,$timecode)
    {
        if (0 < $number)
        {
            $redis = $this->getRedisMaster();
            $cdlr_key = ChestDebrisGenerate::HashChestDebrisLimitRoom($timecode,$uid);
            $cdld_key = ChestDebrisGenerate::HashChestDebrisLimitDay($timecode,$uid);
            $cdlr_val = $redis->hGet($cdlr_key,$sid);
            if (empty($cdlr_val)){$cdlr_val = 0;}
            $cdld_val = $redis->hGet($cdld_key,$uid);
            if (empty($cdld_val)){$cdld_val = 0;}
            $cdlm_val = $this->GetItemMysqlDB($uid);
            if (empty($cdlm_val)){$cdlm_val = 0;}
            
            $cdlr_rel = $cdlr_val + $number;
            $cdld_rel = $cdld_val + $number;
            $cdlm_rel = $cdlm_val + $number;
            
            $cdlr_las = $cdlr_rel >= $this->b ? $this->b : $cdlr_rel;
            $cdld_las = $cdld_rel >= $this->c ? $this->c : $cdld_rel;
            $cdlm_las = $cdlm_rel >= $this->b ? $this->b : $cdlm_rel;
            
            $cdlr_dtv = $cdlr_las - $cdlr_val;
            $cdld_dtv = $cdld_las - $cdld_val;
            $cdlm_dtv = $cdlm_las - $cdlm_val;
            // max(*_dtv,0)
            $cdlr_dtv = $cdlr_dtv > 0 ? $cdlr_dtv : 0;
            $cdld_dtv = $cdld_dtv > 0 ? $cdld_dtv : 0;
            $cdlm_dtv = $cdlm_dtv > 0 ? $cdlm_dtv : 0;
            // min($cdlr_dtv,$cdld_dtv,$cdlm_dtv)
            $number_dtv = $cdlr_dtv > $cdld_dtv ? $cdld_dtv : $cdlr_dtv;
            $number_dtv = $cdlm_dtv > $number_dtv ? $number_dtv : $cdlm_dtv;
            // min($cdlr_dtv,$number_dtv)
            $cdlr_inc = $cdlr_dtv > $number_dtv ? $number_dtv : $cdlr_dtv;
            // min($cdld_dtv,$number_dtv)
            $cdld_inc = $cdld_dtv > $number_dtv ? $number_dtv : $cdld_dtv; 
            // min($cdlm_dtv,$number_dtv)
            $cdlm_inc = $cdlm_dtv > $number_dtv ? $number_dtv : $cdlm_dtv;
            // LogApi::logProcess("ChestDebrisGenerate.AppendItem"." uid:".$uid." sid:".$sid." cdlr_dtv:".$cdlr_dtv." cdld_dtv:".$cdld_dtv." cdlm_dtv:".$cdlm_dtv." number_dtv:".$number_dtv);
            if (0 != $cdlm_inc )
            {
                $this->AppendItemToDB($uid, $cdlm_inc);
            }
            if (0 != $cdlr_inc)
            {
                $redis->hIncrBy($cdlr_key,$sid,$cdlr_inc);
                $redis->expire($cdlr_key,ChestDebrisGenerate::$CHEST_DEBRIS_KEY_TTL);
            }
            if (0 != $cdld_inc)
            {
                $redis->hIncrBy($cdld_key,$uid,$cdld_inc);
                $redis->expire($cdld_key,ChestDebrisGenerate::$CHEST_DEBRIS_KEY_TTL);
            }            
        }
    }
}