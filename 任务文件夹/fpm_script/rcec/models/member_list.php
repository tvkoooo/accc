<?php

class member_list
{
    public static $USER_MOD_NUMBER = 1024;
    
    public static function HashUserListInfoKey($sid)
    {
        return "roomuserlistinfo:$sid";
    }
    
    public static function UserWatchRoomTimeKey($uid)
    {
        return "userwatchtime:$uid";
    }
    // hash uid --> room id
    public static function HashUserAtRoomKey($uid)
    {
        $mod = $uid % member_list::$USER_MOD_NUMBER;
        return "room:useratroom:$mod";
    }
    // hash room id all uid.
    public static function HashRoomMemberInfoKey($sid)
    {
        return "room:memberinfo:$sid";
    }
    // zset room id all uid.
    public static function ZsetRoomMemberNewInfoKey($sid)
    {
        return "zset:room:membernewinfo:$sid";
    }
}
