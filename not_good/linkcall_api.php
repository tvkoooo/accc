<?php
// 帮派夺旗接口
class linkcall_api
{   

    // A 主播打开/关闭连麦功能
    public static function on_linkcall_set_state_rq($params)
    {
        LogApi::logProcess("on_linkcall_set_state_rq rq:".json_encode($params));
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        //linkcall_set_state_rq包数据，拆解rq包
        $sid         = $params['sid'];
        $singer_id   = $params['singer_id'];
        $singer_nick = $params['singer_nick'];        
        $op_code     = $params['op_code']; 

        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
        
        //初始化用户数据data
        //linkcall_user_data 初始化用户缓存数据
        $user_id     =$data_cache['user_id']     = $user_data['user_id']        = 0;
        $user_level  =$data_cache['user_nick']   = $user_data['user_nick']      ="";
        $user_icon   =$data_cache['user_icon']   = $user_data['user_icon']      ="";
        $user_wealth =$data_cache['user_wealth'] = $user_data['user_wealth']    =0;
        $user_nick   =$data_cache['user_level']  = $user_data['user_level']     =0;
        $is_singer   =$data_cache['is_singer']   = $user_data['is_singer']      =0;
        //初始化用户记录数据
        $linkcall_apply = $user_data['linkcall_apply']    =0;
        $time_apply     = $user_data['time_apply']        =0;
        $time_allow     = $user_data['time_allow']        =0;
        
        //linkcall_set_state_rs包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcall_set_state_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['linkcall_state'] = -1;
        //////////rq包验证////////////////////////////////////////////////////////////////////////////////////////////////        
        do
        {
            if (0 == $sid || (!(0 == $op_code || 1 == $op_code)))
            {
                // 100000301(301)无效的参数
                $error['code'] = 100000301;
                $error['desc'] = '无效的请求参数';
                break;
            }
            $m = new linkcall_model();
            //取出主播连麦运行状态
            $linkcall_state = $m->get_singer_linkcall_state(&$error,$sid);               
            if (0 != $error['code']) 
            {
                  //出现了一些逻辑错误      
                   break;
            }
            //判断主播运行状态和本次请求是否合理
            if ( $linkcall_state == $op_code)
            {
                // 合理，主播切换连麦功能
                $linkcall_state = !$op_code;
                $m->set_singer_linkcall_state(&$error,$sid,$linkcall_state);
                if (0 != $error['code']) 
                {
                      //出现了一些逻辑错误      
                       break;
                }
            }
            else
            {
                // 不合理，主播连麦功能开关状态本身处于该状态
                $error['code'] = 403300011;
                $error['desc'] = '连麦：主播当前和需要设置的运行状态一致';
                break;
            }
            //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            //判断：主播由关闭状态  $LINKCALL_STATE_CLOSED 到   开启状态 $LINKCALL_STATE_OPEN
            if (linkcall_model::$LINKCALL_STATE_OPEN == $linkcall_state) 
            {
                //广播直播间，当前连麦连接状态
                $m->linkcall_room_state_nt(&$error,$sid,$linkcall_state);
            }
            else 
            {
                //广播直播间，当前连麦连接状态                
                $m->linkcall_room_state_nt(&$error,$sid,$linkcall_state);
                //单播连麦申请用户，拒绝申请
                $linkcall_apply1 = linkcall_model::$LINKCALL_APPLY_NO;
                $m->linkcall_user_state_nt(&$error,$sid,$user_id,$linkcall_apply1);
                //单播连麦连接用户，断开连接
                $linkcall_apply2 = linkcall_model::$LINKCALL_APPLY_DEL;
                $m->linkcall_user_state_nt(&$error,$sid,$user_id,$linkcall_apply2);                
            }
        }while(FALSE);
        //rs回包
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." return:".json_encode($return));
        return $return;  
    }       

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // B用户（听众）发起/取消/退出连麦
    public static function on_linkcall_apply_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        LogApi::logProcess("on_linkcall_set_state_rq rq:".json_encode($params));
        //linkcall_set_state_rq包数据，拆解rq包        
        $sid                         = $params['sid'];
        $singer_id                   = $params['singer_id']; 
        $singer_nick                 = $params['singer_nick'];
        $op_code                     = $params['op_code'];
        $user_data                   = $params['data'];        
        //
        //初始化回包信息
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
        
        //初始化用户数据data
        //linkcall_user_data 初始化用户缓存数据
        
        $user_id     =$data_cache['user_id']     = $user_data['user_id'];
        $user_level  =$data_cache['user_nick']   = $user_data['user_nick'];
        $user_icon   =$data_cache['user_icon']   = $user_data['user_icon'];
        $user_wealth =$data_cache['user_wealth'] = $user_data['user_wealth'];
        $user_nick   =$data_cache['user_level']  = $user_data['user_level'];
        $is_singer   =$data_cache['is_singer']   = $user_data['is_singer'];
        //初始化用户记录数据
        $linkcall_apply = $user_data['linkcall_apply']    =0;
        $time_apply     = $user_data['time_apply']        =0;
        $time_allow     = $user_data['time_allow']        =0; 
        
        //linkcall_set_state_rs包回包，default
        $rs = array();
        $rs['cmd'] = 'on_linkcall_apply_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['time_apply'] = $time_apply;
        $rs['singer_id']  = $singer_id;
        $rs['singer_nick']  = $singer_nick;
        $rs['linkcall_state'] = $linkcall_state = -1;
        //////////rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {
            if (0 == $sid || 0== $singer_id || (!(1 == $op_code || 2 == $op_code || 3 == $op_code)))
            {
                // 100000301(301)无效的参数
                $error['code'] = 100000301;
                $error['desc'] = '无效的请求参数';
                break;
            }
            $m = new linkcall_model();
            //取出主播连麦运行状态
            $linkcall_state = $m->get_singer_linkcall_state(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //判断主播连麦功能是否已经开启
            if (linkcall_model::$LINKCALL_STATE_CLOSED == $linkcall_state)
            {
                // 403300012(012)主播未开启连麦状态
                $rs['linkcall_state'] = $linkcall_state;
                $error['code'] = 403300012;
                $error['desc'] = '主播未开启连麦状态';
                break;
            }
            //逻辑功能（主播是开启连麦状态）////////////////////////////////////////////////////////////////////////////////
            
            //情景1：用户发起连麦申请    1 == $op_code
            {
                if ( 1 == $op_code)
                {
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //用户发起申请
                    $m->user_apply_apply_linkcall(&$error,$sid,$singer_id,$singer_nick,$user_id,&$time_apply,&$data_cache);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                }
            }

            //情景2：用户取消连麦申请    2 == $op_code
            {
                if ( 2 == $op_code)
                {                       
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //用取消起申请
                    $m->user_apply_desapply_linkcall(&$error,$sid,$singer_id,$user_id,&$data_cache,&$linkcall_state);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////                    

                }
            }
            
            //情景3：用户退出连麦功能    3 == $op_code
            {
                if ( 3 == $op_code)
                {
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //用户退出连麦连接
                    $m->user_apply_out_linkcall(&$error,$sid,$singer_id,$user_id,&$data_cache,&$linkcall_state);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////                   
 
                }
            }  
        
            //逻辑功能（主播是开启连麦状态）结束////////////////////////////////////////////////////////////////////////////
             //rs 回包拼装
            $rs['error'] = &$error;
            $rs['time_apply'] = $time_apply;
            $rs['linkcall_state'] = $linkcall_state; 
        }while(FALSE);
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." return:".json_encode($return));
        return $return;      
    }   
    
    
    // C 主播允许/拒绝/删除连麦
    public static function on_linkcall_allow_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        LogApi::logProcess("linkcall_allow_rq rq:".json_encode($params));
        //linkcall_allow_rq包数据，拆解rq包        
        $sid                         = $params['sid'];
        $singer_id                   = $params['singer_id']; 
        $singer_nick                 = $params['singer_nick'];
        $op_code                     = $params['op_code'];
        $user_id                     = $params['user_id'];        
        //
        //初始化回包信息
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
        
        //初始化用户数据data
        //linkcall_user_data 初始化用户缓存数据
        $user_id     =$data_cache['user_id']     = $user_data['user_id']        = 0;
        $user_level  =$data_cache['user_nick']   = $user_data['user_nick']      ="";
        $user_icon   =$data_cache['user_icon']   = $user_data['user_icon']      ="";
        $user_wealth =$data_cache['user_wealth'] = $user_data['user_wealth']    =0;
        $user_nick   =$data_cache['user_level']  = $user_data['user_level']     =0;
        $is_singer   =$data_cache['is_singer']   = $user_data['is_singer']      =0;
        //初始化用户记录数据
        $linkcall_apply = $user_data['linkcall_apply']    =0;
        $time_apply     = $user_data['time_apply']        =0;
        $time_allow     = $user_data['time_allow']        =0;
        
        
        //linkcall_allow_rs包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcall_allow_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['time_apply'] = 0;
        $rs['singer_id']  = $singer_id;
        $rs['singer_nick']  = $singer_nick;        
        $rs['op_code'] = $op_code;
        $rs['data'] = $user_data;
        //////////rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {
            if (0 == $sid || 0== $singer_id || (!(1 == $op_code || 2 == $op_code || 3 == $op_code)) )
            {
                // 100000301(301)无效的参数
                $error['code'] = 100000301;
                $error['desc'] = '无效的请求参数';
                break;
            }
            $m = new linkcall_model();            

            //逻辑功能（主播是开启连麦状态）////////////////////////////////////////////////////////////////////////////////
            //连接用户列表
            $link_list =array();
            //申请用户列表
            $apply_list =array();
            
            //情景1：主播允许连麦申请    1 == $op_code
            {
                if ( 1 == $op_code)
                {
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //主播允许申请
                    $m->singer_apply_yes_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    

                 }
            }

            //情景2：主播拒绝连麦申请    2 == $op_code
            {
                if ( 2 == $op_code)
                {   
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //主播拒绝申请
                    $m->singer_apply_no_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////                  
                    

                }
            }
            
            //情景3：主播删除连麦功能    3 == $op_code
            {
                if ( 3 == $op_code)
                {
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //主播删除连接
                    $m->singer_apply_del_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    
  
                }
            }  
   
        //逻辑功能（主播是开启连麦状态）结束////////////////////////////////////////////////////////////////////////////
        }while(FALSE);
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." return:".json_encode($return));
        return $return;      
    }
    
    // D 主播查询最新申请列表
    public static function on_linkcall_list_singer_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        LogApi::logProcess("linkcall_list_singer_rq rq:".json_encode($params));
        //linkcall_allow_rq包数据，拆解rq包
        $sid                         = $params['sid'];
        $singer_id                   = $params['singer_id'];
        $singer_nick                 = $params['singer_nick'];

        //
        //初始化回包信息
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
    
        //初始化用户数据data
        //linkcall_list_singer_rs 初始化用户缓存数据
        $user_id     =$data_cache['user_id']     = $user_data['user_id']        = 0;
        $user_level  =$data_cache['user_nick']   = $user_data['user_nick']      ="";
        $user_icon   =$data_cache['user_icon']   = $user_data['user_icon']      ="";
        $user_wealth =$data_cache['user_wealth'] = $user_data['user_wealth']    =0;
        $user_nick   =$data_cache['user_level']  = $user_data['user_level']     =0;
        $is_singer   =$data_cache['is_singer']   = $user_data['is_singer']      =0;
        //初始化用户记录数据
        $linkcall_apply = $user_data['linkcall_apply']    =0;
        $time_apply     = $user_data['time_apply']        =0;
        $time_allow     = $user_data['time_allow']        =0;    
    
        //linkcall_list_singer_rs包回包，default
        $rs = array();
        $datas =array();        
        $rs['cmd'] = 'linkcall_allow_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['singer_id']  = $singer_id;
        $rs['singer_nick']  = $singer_nick;
        $rs['linkcall_state'] = $linkcall_state = -1;
        $rs['datas'] = $datas;
        //////////rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {
            $m = new linkcall_model();
            //参数是否合法
            if (0 == $sid || 0== $singer_id  )
            {
                // 100000301(301)无效的参数
                $error['code'] = 100000301;
                $error['desc'] = '无效的请求参数';
                break;
            }
            //主播是否开启连麦
            $linkcall_state = $m->get_singer_linkcall_state(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            //逻辑功能（主播是开启连麦状态）////////////////////////////////////////////////////////////////////////////////
            //连接用户列表
            $link_list =array();
            //申请用户列表
            $apply_list =array();
    
            //取出连接用户列表用户，拼装datas
            //查询当前连麦连接列表，取出连麦申请user_id
            $m->get_user_link_time_index(&$error,$sid,&$link_list);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //用查询到的user_id，去拼装datas
            $linkcall_apply1 = linkcall_model::LINKCALL_APPLY_YES;
            foreach ($link_list as $uid => $score)
            {
                $data_get = array ();
                $data = array ();
                $data_get['time_allow'] = $score;
                $data_get['user_id'] = $uid ;
                //根据 $uid去拼装data
                $m->linkcall_user_link_list_to_data(&$error,$sid,$uid,$linkcall_apply1,&$data);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $datas[] = $data;
            }
            
            //取出申请用户列表用户，继续拼装datas
            //查询当前连麦申请列表，取出连麦申请user_id
            $m->get_user_apply_time_index(&$error,$sid,&$apply_list);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //用查询到的user_id，去拼装datas
            $linkcall_apply2 = linkcall_model::LINKCALL_APPLY_APPLY;
            foreach ($apply_list as $uid => $score)
            {
                $data_get = array ();
                $data = array ();
                $data_get['time_apply'] = $score;
                $data_get['user_id'] = $uid ;
                //根据 $uid去拼装data
                $m->linkcall_user_applyt_list_to_data(&$error,$sid,$uid,$linkcall_apply2,&$data);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $datas[] = $data;
            }
            $rs['cmd'] = 'linkcall_allow_rs';
            $rs['error'] = &$error;
            $rs['sid'] = $sid;
            $rs['singer_id']  = $singer_id;
            $rs['singer_nick']  = $singer_nick;
            $rs['linkcall_state'] = $linkcall_state ;
            $rs['datas'] = $datas;

            //逻辑功能（主播是开启连麦状态）结束////////////////////////////////////////////////////////////////////////////
        }while(FALSE);
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." return:".json_encode($return));
        return $return;
    }
    
    // E 用户（主播/用户）查询当前最新连麦信息
    public static function on_linkcall_list_user_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        LogApi::logProcess("linkcall_list_user_rq rq:".json_encode($params));
        //linkcall_allow_rq包数据，拆解rq包
        $sid                         = $params['sid'];
        $singer_id                   = $params['singer_id'];
        $singer_nick                 = $params['singer_nick'];       
    
        //
        //初始化回包信息
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
    
        //初始化用户数据data
        //linkcall_list_singer_rs 初始化用户缓存数据
        $user_id     =$data_cache['user_id']     = $user_data['user_id']        = 0;
        $user_level  =$data_cache['user_nick']   = $user_data['user_nick']      ="";
        $user_icon   =$data_cache['user_icon']   = $user_data['user_icon']      ="";
        $user_wealth =$data_cache['user_wealth'] = $user_data['user_wealth']    =0;
        $user_nick   =$data_cache['user_level']  = $user_data['user_level']     =0;
        $is_singer   =$data_cache['is_singer']   = $user_data['is_singer']      =0;
        //初始化用户记录数据
        $linkcall_apply = $user_data['linkcall_apply']    =0;
        $time_apply     = $user_data['time_apply']        =0;
        $time_allow     = $user_data['time_allow']        =0;
    
        //linkcall_list_singer_rs包回包，default
        $rs = array();
        $datas =array();
        $rs['cmd'] = 'linkcall_list_user_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['singer_id']  = $singer_id;
        $rs['singer_nick']  = $singer_nick;
        $rs['linkcall_state'] = $linkcall_state = -1;
        $rs['datas'] = $datas;
        //////////rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {
            $m = new linkcall_model();
            //参数是否合法
            if (0 == $sid || 0== $singer_id  )
            {
                // 100000301(301)无效的参数
                $error['code'] = 100000301;
                $error['desc'] = '无效的请求参数';
                break;
            }
            //主播是否开启连麦
            $linkcall_state = $m->get_singer_linkcall_state(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            //逻辑功能（主播是开启连麦状态）////////////////////////////////////////////////////////////////////////////////
            //连接用户列表
            $link_list =array();
    
            //取出连接用户列表用户，拼装datas
            //查询当前连麦连接列表，取出连麦申请user_id
            $m->get_user_link_time_index(&$error,$sid,&$link_list);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //用查询到的user_id，去拼装datas
            $linkcall_apply = linkcall_model::LINKCALL_APPLY_YES;
            foreach ($link_list as $uid => $score)
            {
                $data_get = array ();
                $data = array ();
                $data_get['time_allow'] = $score;
                $data_get['user_id'] = $uid ;
                //根据 $uid去拼装data
                $m->linkcall_user_link_list_to_data(&$error,$sid,$uid,$linkcall_apply,&$data);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $datas[] = $data;
            }

            $rs['cmd'] = 'linkcall_allow_rs';
            $rs['error'] = &$error;
            $rs['sid'] = $sid;
            $rs['singer_id']  = $singer_id;
            $rs['singer_nick']  = $singer_nick;
            $rs['linkcall_state'] = $linkcall_state ;
            $rs['datas'] = $datas;
    
            //逻辑功能（主播是开启连麦状态）结束////////////////////////////////////////////////////////////////////////////
        }while(FALSE);
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." return:".json_encode($return));
        return $return;
    } 
    
    
    
    
    
    
    
    
}