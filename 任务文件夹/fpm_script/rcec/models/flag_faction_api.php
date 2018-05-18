<?php
// 帮派夺旗接口
class flag_faction_api
{    
    // 开启夺旗请求
    public static function on_flag_make_rq($params)
    {        
        LogApi::logProcess("on_flag_make_rq rq:".json_encode($params));
        //
        $sid = $params['sid'];
        $uid = $params['uid'];
        $faction_id = $params['faction_id'];

        $return = array();
        
        $error = array();
        $current = array();
        
        $error['code'] = 0;
        $error['desc'] = '';
        
        $current['faction_id'] = -1;
        $current['faction_name'] = '';
        $current['faction_icon'] = '';
        $current['flag_number'] = 0;
        $current['faction_level'] = 0;
        
        $rs = array();
        $rs['cmd'] = 'flag_make_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['uid'] = $uid;
        $rs['current'] = &$current;
        /////////////////////////////////////////////
        do
        {
            if (0 == $sid || 0 == $uid)
            {
                // 100000301(301)无效的参数
                $error['code'] = 100000301;
                $error['desc'] = '无效的参数';
                break;
            }
            if (0 == $faction_id)
            {
                // 403200001,//(001)有帮会的用户才能开启插旗
                $error['code'] = 403200001;
                $error['desc'] = '有帮会的用户才能开启插旗';
                break;
            }
            $timecode_now = time();
            $faction_current = array();
            $flg_info_faction_id = 0;
            $m = new flag_faction_model();
            $m->redis_room_get_current_info(&$error, &$faction_current, $sid);
            $flg_info_faction_id = $faction_current['faction_id'];
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            // 做一次有效值赋值
            $current['faction_id'] = $flg_info_faction_id;
            if (-1 != $flg_info_faction_id)
            {
                // 当前房间已经处在插旗或者抢旗状态
                break;
            }
            // 将当前房间的插旗状态置为0(已开启插旗)
            $flg_info_faction_id = 0;
            $faction_current = array();
            $faction_current['faction_id'] = $flg_info_faction_id;
            $faction_current['timecode'] = $timecode_now;
            $m->redis_room_set_current_info(&$error, $faction_current, $sid);
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            $m->redis_get_faction_flag_full_info(&$error, &$current, $flg_info_faction_id, $sid);
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            $sys_parameters = new SysParametersModel();
            //
            $base_flag_number = $sys_parameters->GetSysParameters(flag_faction_model::$CONST_PARM_ID_BASE_FLAG_NUMBER, 'parm1');
            // 在直播间发布开启抢旗的消息
            $nt = array();
            $nt['cmd'] = 'flag_make_nt';
            $nt['sid'] = $sid;
            $nt['uid'] = $uid;
            $nt['base_flag_number'] = $base_flag_number;
            $nt['current'] = &$current;
            
            $return[] = array
            (
                'broadcast' => 2,// 直播间通知
                'data' => $nt,
            );
            LogApi::logProcess("on_flag_make_rq uid:".$uid." nt:".json_encode($nt));
        }while(FALSE);
        /////////////////////////////////////////////
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_flag_make_rq uid:".$uid." rs:".json_encode($rs));
        LogApi::logProcess("on_flag_make_rq uid:".$uid." return:".json_encode($return));
        return $return;        
    }
    // 查询夺旗详情请求
    public static function on_flag_details_rq($params)
    {
        LogApi::logProcess("on_flag_details_rq rq:".json_encode($params));
        //
        $sid = $params['sid'];
        $uid = $params['uid'];
        $faction_id = $params['faction_id'];
        
        $flag_icon = "uknown.png";
    
        $return = array();
    
        $error = array();
        $current = array();
        $oneself = array();
    
        $error['code'] = 0;
        $error['desc'] = '';
    
        $current['faction_id'] = -1;
        $current['faction_name'] = '';
        $current['faction_icon'] = '';
        $current['flag_number'] = 0;
        $current['faction_level'] = 0;
        
        $oneself['faction_id'] = -1;
        $oneself['faction_name'] = '';
        $oneself['faction_icon'] = '';
        $oneself['flag_number'] = 0;
        $oneself['faction_level'] = 0;
    
        $rs = array();
        $rs['cmd'] = 'flag_details_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['uid'] = $uid;
        $rs['current'] = &$current;
        $rs['oneself'] = &$oneself;
        $rs['flag_icon'] = $flag_icon;
        $rs['base_flag_number'] = 0;
        $rs['timelength'] = 0;
        $rs['timecode'] = 0;
        $rs['flag_success_plus'] = 0;//夺旗成功加成
        /////////////////////////////////////////////
        do
        {
            if (0 == $sid || 0 == $uid)
            {
                // 100000301(301)无效的参数
                $error['code'] = 100000301;
                $error['desc'] = '无效的参数';
                break;
            }
            // 
            $faction_current = array();
            $flg_info_faction_id = 0;
            $sys_parameters = new SysParametersModel();
            //
            $base_flag_number = $sys_parameters->GetSysParameters(flag_faction_model::$CONST_PARM_ID_BASE_FLAG_NUMBER, 'parm1');
            $base_time_length = $sys_parameters->GetSysParameters(flag_faction_model::$CONST_PARM_ID_FLAG_TIME_LENGTH, 'parm1');
            $flag_success_Plus = $sys_parameters->GetSysParameters(flag_faction_model::$CONST_PARM_ID_FLAG_SUCCESS_PLUS, 'parm1');
            $rs['flag_success_plus'] = $flag_success_Plus;
            $rs['base_flag_number'] = $base_flag_number;
            $rs['timelength'] = $base_time_length;
            
            $timecode_now = time(null);
            //
            $m = new flag_faction_model();
            if(0 != $faction_id)
            {
                // 获取自己的帮派数据
                $m->redis_get_faction_flag_full_info(&$error, &$oneself, $faction_id, $sid);
                if (0 != $error['code'])
                {
                    // 出现了一些逻辑错误
                    break;
                }
            }
            $m->redis_room_get_current_info(&$error, &$faction_current, $sid);
            $flg_info_faction_id = $faction_current['faction_id'];
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            $m->redis_get_faction_flag_full_info(&$error, &$current, $flg_info_faction_id, $sid);
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            // 尝试拿出本房间占旗帮派的结算超时时间
            $trigger_faction_action_timeout = 0;
            $m->redis_room_action_info_near_timecode(&$error, &$trigger_faction_action_timeout, $sid);
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            // 将超时时间点计算出来
            if (true == empty($trigger_faction_action_timeout))
            {
                $trigger_faction_action_timeout = 0; 
                $rs['timecode'] = 0;
            }
            else 
            {
                if ($timecode_now >= $trigger_faction_action_timeout)
                {
                    $rs['timecode'] = 0;
                }
                else
                {
                    $rs['timecode'] = $trigger_faction_action_timeout - $base_time_length;
                }
            }
        }while(FALSE);
        /////////////////////////////////////////////
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_flag_details_rq uid:".$uid." rs:".json_encode($rs));
        LogApi::logProcess("on_flag_details_rq uid:".$uid." return:".json_encode($return));
        return $return;
    }
    // 加入夺旗请求
    public static function on_flag_join_rq($params)
    {
        LogApi::logProcess("on_flag_join_rq rq:".json_encode($params));
        //
        $sid = $params['sid'];
        $uid = $params['uid'];
        $faction_id = $params['faction_id'];
    
        $return = array();
    
        $error = array();
        $current = array();
        $trigger = array();
    
        $error['code'] = 0;
        $error['desc'] = '';
    
        $current['faction_id'] = -1;
        $current['faction_name'] = '';
        $current['faction_icon'] = '';
        $current['flag_number'] = 0;
        $current['faction_level'] = 0;
        
        $trigger['faction_id'] = -1;
        $trigger['faction_name'] = '';
        $trigger['faction_icon'] = '';
        $trigger['flag_number'] = 0;
        $trigger['faction_level'] = 0;
        
        $rs = array();
        $rs['cmd'] = 'flag_join_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['uid'] = $uid;
        $rs['current'] = &$current;
        $rs['trigger'] = &$trigger;
        /////////////////////////////////////////////
        do
        {
            if (0 == $sid || 0 == $uid)
            {
                $error['code'] = 100000301;
                $error['desc'] = '无效的参数';
                break;
            }
            if (0 == $faction_id)
            {
                // 403200002,//(002)有帮会的用户才能加入夺旗
                $error['code'] = 403200002;
                $error['desc'] = '有帮会的用户才能加入夺旗';
                break;
            }
            // 
            $base_flag_number = 0;
            $faction_current = array();
            $flg_info_faction_id = 0;
            $timecode_now = time(null);
            $oneself_number = 0;
            
            $sys_parameters = new SysParametersModel();
            $m = new flag_faction_model();
            //
            $base_flag_number = $sys_parameters->GetSysParameters(flag_faction_model::$CONST_PARM_ID_BASE_FLAG_NUMBER, 'parm1');
            //
            $m->redis_room_get_current_info(&$error, &$faction_current, $sid);
            $flg_info_faction_id = $faction_current['faction_id'];
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            // 做一次有效值赋值
            $current['faction_id'] = $flg_info_faction_id;
            if (-1 == $flg_info_faction_id)
            {
                // 403200003,//(003)在加入前需要先开启夺旗
                $error['code'] = 403200003;
                $error['desc'] = '在加入前需要先开启夺旗';
                break;
            }
            // 将本用户加入到夺旗列表
            $m->redis_user_flag_add(&$error, &$oneself_number, $uid, $sid, $faction_id, $timecode_now);
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            // 做一次夺旗动作触发校验
            $m->handle_user_room_action(&$error, &$return, &$current, &$trigger ,$uid, $sid, $faction_id, $flg_info_faction_id, +1);
            //
            $nt = array();
            $nt['cmd'] = 'flag_join_nt';
            $nt['sid'] = $sid;
            $nt['uid'] = $uid;
            $nt['current'] = &$current;
            $nt['trigger'] = &$trigger;
    
            $return[] = array
            (
                'broadcast' => 2,// 直播间通知
                'data' => $nt,
            );
            LogApi::logProcess("on_flag_join_rq uid:".$uid." nt:".json_encode($nt));
        }while(FALSE);
        /////////////////////////////////////////////
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_flag_join_rq uid:".$uid." rs:".json_encode($rs));
        LogApi::logProcess("on_flag_join_rq uid:".$uid." return:".json_encode($return));
        return $return;
    }
    // 退出夺旗请求
    public static function on_flag_exit_rq($params)
    {
        LogApi::logProcess("on_flag_exit_rq rq:".json_encode($params));
        //
        $sid = $params['sid'];
        $uid = $params['uid'];
        $faction_id = $params['faction_id'];
        
        $return = array();
        
        $error = array();
        $current = array();
        $trigger = array();
        
        $error['code'] = 0;
        $error['desc'] = '';
        
        $current['faction_id'] = -1;
        $current['faction_name'] = '';
        $current['faction_icon'] = '';
        $current['flag_number'] = 0;
        $current['faction_level'] = 0;
        
        $trigger['faction_id'] = -1;
        $trigger['faction_name'] = '';
        $trigger['faction_icon'] = '';
        $trigger['flag_number'] = 0;
        $trigger['faction_level'] = 0;
        
        $rs = array();
        $rs['cmd'] = 'flag_exit_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['uid'] = $uid;
        $rs['current'] = &$current;
        $rs['trigger'] = &$trigger;
        /////////////////////////////////////////////
        do
        {
            if (0 == $sid || 0 == $uid)
            {
                $error['code'] = 100000301;
                $error['desc'] = '无效的参数';
                break;
            }
            if (0 == $faction_id)
            {
                // 403200002,//(002)有帮会的用户才能退出夺旗
                $error['code'] = 403200002;
                $error['desc'] = '有帮会的用户才能加入夺旗';
                break;
            }
            //
            $base_flag_number = 0;
            $faction_current = array();
            $flg_info_faction_id = 0;
            $timecode_now = time(null);
            $oneself_number = 0;
        
            $sys_parameters = new SysParametersModel();
            $m = new flag_faction_model();
            //
            $base_flag_number = $sys_parameters->GetSysParameters(flag_faction_model::$CONST_PARM_ID_BASE_FLAG_NUMBER, 'parm1');
            //
            $m->redis_room_get_current_info(&$error, &$faction_current, $sid);
            $flg_info_faction_id = $faction_current['faction_id'];
            if (0 != $error['code'])
            {
                // 出现了一些逻辑错误
                break;
            }
            // 做一次有效值赋值
            $current['faction_id'] = $flg_info_faction_id;
            if (-1 == $flg_info_faction_id)
            {
                // 403200003,//(003)在加入前需要先开启夺旗
                $error['code'] = 403200003;
                $error['desc'] = '在加入前需要先开启夺旗';
                break;
            }
            // 将本用户加入到夺旗列表
            $m->redis_user_flag_rmv(&$error, &$oneself_number, $uid, $sid, $faction_id);
            // 做一次夺旗动作触发校验
            $m->handle_user_room_action(&$error, &$return, &$current, &$trigger ,$uid, $sid, $faction_id, $flg_info_faction_id, -1);
            //
            $nt = array();
            $nt['cmd'] = 'flag_exit_nt';
            $nt['sid'] = $sid;
            $nt['uid'] = $uid;
            $nt['current'] = &$current;
            $nt['trigger'] = &$trigger;
        
            $return[] = array
            (
                'broadcast' => 2,// 直播间通知
                'data' => $nt,
            );
            LogApi::logProcess("on_flag_exit_rq uid:".$uid." nt:".json_encode($nt));
        }while(FALSE);
        /////////////////////////////////////////////
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_flag_join_rq uid:".$uid." rs:".json_encode($rs));
        LogApi::logProcess("on_flag_join_rq uid:".$uid." return:".json_encode($return));
        return $return;
    }
}
