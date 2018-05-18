<?php

class UserAnchorSignupWeek extends ModelBase
{
    public static $ANCHOR_WEEK_SIGNUP_KEY = "anchor_week_signup";
    public static $ANCHOR_DEFAULT_WEEK_DAY = 7;
    public $week_day = 7;
    
    public $week_day_unix_min = 0;
    public $week_day_unix_max = 0;
    
    public $uid = 0;//用户id
    public $sid = 0;//房间id
    public $zid = 0;//播主id
    
    public $canweekStar = 0;
    public $weekStar = 0;
    public $weektool_id = 0;
    public $week_ranking = 0;
    public $weektool_img = '';  
    public $weektool_name = '';
    
    public function GetDataFramDB($timecode)
    {
        LogApi::logProcess("UserAnchorSignupWeek.GetDataFramDB begin.");
        $this->GetCanweekStarFramDB($timecode);
        LogApi::logProcess("UserAnchorSignupWeek.GetDataFramDB 1");
        $this->GetWeekStarFramDB();
        LogApi::logProcess("UserAnchorSignupWeek.GetDataFramDB 2");
        $this->GetWeekToolIdFromDB();
        LogApi::logProcess("UserAnchorSignupWeek.GetDataFramDB 3");
        $this->GetWeekToolIconFromDB();
        LogApi::logProcess("UserAnchorSignupWeek.GetDataFramDB 4");
        $this->GetWeekRankFromDB($timecode);
        LogApi::logProcess("UserAnchorSignupWeek.GetDataFramDB end.");
    }
    public function GetCanweekStarFramDB($timecode)
    {
        $this->canweekStar = $this->GetIsCanUserAnchorSignupWeek($timecode);
    }
    public function GetWeekStarFramDB()
    {
        $this->weekStar = $this->GetIsUserAnchorSignupWeek($this->zid);
        if (empty($this->weekStar))
        {
            $this->weekStar = 0;
        }
        else 
        {
            $this->weekStar = 1;
        }
    }

    public function GetWeekToolIdFromDB()
    {       
        $value = 0;
        $sql = "select weekTool from raidcall.anchor_info where uid = $this->zid";
        $rows = $this->getDbMain()->query($sql);
        if ( $rows )
        {
            if ( 0 < $rows->num_rows )
            {
                $row = $rows->fetch_assoc();
                $value = intval($row['weekTool']);
            }
        }
        else
        {
            LogApi::logProcess("GetWeekToolIdFromDB::****************sql:$sql");
        }
        LogApi::logProcess("GetWeekToolIdFromDB::**************** value:$value");
        $this->weektool_id = $value;
    }
    public function GetWeekToolIconFromDB()
    {
        $value_name = '';
        $value_icon = '';
        
        if (0 != $this->weektool_id)
        {
            $sql = "select name,icon from rcec_main.tool where id = $this->weektool_id";
            $rows = $this->getDbMain()->query($sql);
            if ( $rows )
            {
                if ( 0 < $rows->num_rows )
                {
                    $row = $rows->fetch_assoc();
                    $value_name = $row['name'];
                    $value_icon = $row['icon'];
                }
            }
            else
            {
                LogApi::logProcess("GetWeekToolIconFromDB::****************sql:$sql");
            }             
        }
        LogApi::logProcess("GetWeekToolIconFromDB::**************** value_name:$value_name"." value_icon:$value_icon");
        $this->weektool_img = $value_icon;
        $this->weektool_name = $value_name;
    }
    public function GetWeekRankFromDB($timecode)
    {
        $value = -1;
        $this->GetNowWeekTime($timecode);
        if (0 != $this->weektool_id)
        {
            // 报名但是没有人送礼不计入榜单
            $sql = "SELECT
	A.receiver_uid as zuid,
	sum(A.total_coins_cost) AS ztotal
FROM
	rcec_record.week_tool_consume_record A
	LEFT JOIN rcec_main.tool t on t.id = A.tool_id
	LEFT JOIN raidcall.uinfo uin on uin.id 

 = A.receiver_uid
WHERE
	A.tool_id =$this->weektool_id
AND A.receiver_uid in (select uid from raidcall.anchor_info where weekTool = $this->weektool_id)
AND A.record_time >=$this->week_day_unix_min
AND A.record_time <=$this->week_day_unix_max
GROUP BY
	zuid
ORDER BY
	ztotal DESC";
            $rows = $this->getDbMain()->query($sql);
            if ( $rows )
            {
                if ( 0 < $rows->num_rows )
                {
                    for ($x=0; $x<$rows->num_rows; $x++)
                    {
                        $row = $rows->fetch_assoc();
                        
                        // for debug.
                        // LogApi::logProcess("GetWeekRankFromDB::**************** row:".json_encode($row));
                        
                        // rownum ID zuid ztotal
                        $zuid = intval($row['zuid']);
                        if ($zuid == $this->zid)
                        {
                            // rownum 榜单从0开始。
                            $value = $x+1;
                        }
                    }
                }
            }
            else
            {
                LogApi::logProcess("GetWeekRankFromDB::****************sql:$sql");
            }            
        }
        // for debug.
        // LogApi::logProcess("GetWeekRankFromDB::****************sql:$sql");
        
        LogApi::logProcess("GetWeekRankFromDB::**************** value:$value");
        $this->week_ranking = $value;
    }
    public function GetIsUserAnchorSignupWeek($uid)
    {
        $weekTool = 0;
        $sql = "SELECT weekTool FROM raidcall.anchor_info WHERE uid = $uid;";
        $rows = $this->getDbMain()->query($sql);
        if ( $rows )
        {
            if ( 0 < $rows->num_rows )
            {
                $row = $rows->fetch_assoc();
                $weekTool = intval($row['weekTool']);
            }  
        }
        LogApi::logProcess("GetIsUserAnchorSignupWeek uid:$uid weekTool:$weekTool");
        return $weekTool;
        // return $this->getRedisMaster()->hGet(UserAnchorSignupWeek::$ANCHOR_WEEK_SIGNUP_KEY,$uid);
    }
    //可以报名周星 0：否  1：是
    public function GetIsCanUserAnchorSignupWeek($timecode)
    {
        $value = 0;
        $this->GetUserAnchorSignupWeekDay();//[1,7]
        $week_day = $this->week_day;
        $week_now = date('w',$timecode);// [0,6]
        $week_now ++;//[1,7] 1 2 3
        // [2,$week_day-1]
        $d_min = 2;// 最少星期一
        $d_max = $week_day - 1;// 配置星期编号前一天

        if ($d_min <= $week_now && $week_now <= $d_max)
        {
            $value = 1;
        }
        else
        {
            $value = 0;
        }
        // mktime(hour,minute,second,month,day,year,is_dst);
//         $day_now_00 = mktime(0, 0, 0, date("m",$timecode)  , date("d",$timecode), date("Y",$timecode));
//         $day_second = 24 * 60 * 60;
//         $this->week_day_unix_min = $day_now_00 - ( $week_now - $d_min     ) * $day_second;
//         $this->week_day_unix_max = $day_now_00 + ( $d_max - $week_now + 1 ) * $day_second;
//         $unix_now_string = date('Y-m-d H:i:s',$timecode);
//         $unix_min_string = date('Y-m-d H:i:s',$this->week_day_unix_min);
//         $unix_max_string = date('Y-m-d H:i:s',$this->week_day_unix_max);
//         LogApi::logProcess("GetIsCanUserAnchorSignupWeek::**************** $unix_now_string [$unix_min_string,$unix_max_string]");
        return $value;
    }
    public function GetNowWeekTime($timecode)
    {
        // $this->GetUserAnchorSignupWeekDay();//[1,7]
        $week_day = $this->week_day;
        $week_now = date('w',$timecode);// [0,6]
        $week_now ++;//[1,7] 1 2 3
        // [2,$week_day-1]
        $d_min = 2;// 最少星期一
        $d_max = $week_day - 1;// 配置星期编号前一天
        
        // mktime(hour,minute,second,month,day,year,is_dst);
        $day_now_00 = mktime(0, 0, 0, date("m",$timecode)  , date("d",$timecode), date("Y",$timecode));
        $day_second = 24 * 60 * 60;
        $this->week_day_unix_min = $day_now_00 - ( $week_now - $d_min     ) * $day_second;
        $this->week_day_unix_max = $timecode;
        $unix_now_string = date('Y-m-d H:i:s',$timecode);
        $unix_min_string = date('Y-m-d H:i:s',$this->week_day_unix_min);
        $unix_max_string = date('Y-m-d H:i:s',$this->week_day_unix_max);
        LogApi::logProcess("GetNowWeekTime $unix_now_string [$unix_min_string,$unix_max_string]");
    }
    // 7 是星期六 
    public function GetUserAnchorSignupWeekDay()
    {
        $this->week_day = $this->GetUserAnchorSignupWeekDayRedis();
        if (empty($this->week_day))
        {
            $this->week_day = $this->GetUserAnchorSignupWeekDayDB();
        }
        if (empty($this->week_day))
        {
            $this->week_day = UserAnchorSignupWeek::$ANCHOR_DEFAULT_WEEK_DAY;
        }
    }
    public function GetUserAnchorSignupWeekDayRedis()
    {
        $value = NULL;
        
        $sys_parameters_key = 'sys_parameters';
        $signup_week_day_id = 79;
        $elem_string = $this->getRedisMaster()->hGet($sys_parameters_key,$signup_week_day_id);
        if (empty($elem_string))
        {
            $value = NULL;
        }
        else
        {
            LogApi::logProcess("GetUserAnchorSignupWeekDayRedis::**************** elem_string:$elem_string");
            $elem = json_decode($elem_string, true);
            $value = $elem['parm3'];            
        }
        LogApi::logProcess("GetUserAnchorSignupWeekDayRedis::**************** value:$value");
        return $value;
    }
    public function GetUserAnchorSignupWeekDayDB()
    {
        $value = NULL;

        $id_min = 79;
        $id_max = 79;
        // select id,parm1,parm2,parm3 from card.parameters_info where id >= 82 && id <= 90;
        $sql = "select id,parm1,parm2,parm3 from card.parameters_info where id >= $id_min && id <= $id_max";
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
                    $db_array[$row[0]] = array('parm1'=>$row['parm1'],'parm2'=>$row['parm2'],'parm3'=>$row['parm3']);
                }
                $value = $db_array['79']['parm3'];
            }
        }
        else
        {
            LogApi::logProcess("GetUserAnchorSignupWeekDayDB::****************sql:$sql");
        }
        LogApi::logProcess("GetUserAnchorSignupWeekDayDB::**************** value:$value");
        return $value;
    }
}