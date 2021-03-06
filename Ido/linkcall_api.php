<?php
// 连麦api接口
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
        $sid         = (int)$params['sid'];
        $singer_id   = (int)$params['singer_id'];
        $singer_nick = $params['singer_nick'];        
        $op_code     = (int)$params['op_code']; 

        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';      

        $num_apply= 0;
        $num_link = 0;

        //////////rq包验证////////////////////////////////////////////////////////////////////////////////////////////////        
        do
        {
            if (0 == $sid || (!(0 == $op_code || 1 == $op_code)))
            {
                // 403300010(010)无效的参数
                $error['code'] = 403300010;
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
                // 合理，主播切换连麦功能,保存主播本操作连麦状态

                $linkcall_state =  $op_code ? 0: 1;
                $m->set_singer_linkcall_state(&$error,$sid,$linkcall_state);
                if (0 != $error['code']) 
                {
                      //出现了一些逻辑错误      
                       break;
                }
            }
            else
            {
                // 不合理，主播连麦功能开关状态本身处于该状态，打出错误日志，并容错重置错误，退出不执行本次剩余操作
                $error['code'] = 403300011;
                $error['desc'] = '当前和需要设置的运行状态一致';
                LogApi::logProcess("on_linkcall_set_state_rq error:".json_encode($error));
                $error['code'] = 0;
                $error['desc'] = '';
                break;
            }
            //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            //判断：主播由关闭状态  $LINKCALL_STATE_CLOSED 到   开启状态 $LINKCALL_STATE_OPEN

            if (linkcall_model::$LINKCALL_STATE_OPEN == $linkcall_state) 
            {
                // 1 广播直播间，当前连麦连接状态

                $m->linkcall_room_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
            else 
            {
               
                // 2 单播当前连麦所有申请用户，拒绝申请
                {
                    // 查询当前连麦申请个数
                    $num_apply = $m->get_user_apply_index_count(&$error,$sid);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }

                    // A 查询当前连麦申请列表，取出连麦申请user_id
                    $apply_list=array();
                    $m->get_user_apply_index(&$error,$sid,&$apply_list);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }

                    // B 用查询到的user_id，登记用户申请记录，去推送到相应的用户，主播拒绝申请
                    $linkcall_apply1 = linkcall_model::$LINKCALL_APPLY_NO;
                    foreach ($apply_list as $uid )
                    {

                        //根据 $uid登记用户状态   主播拒绝
                        $m->set_user_apply_state(&$error,$sid,$uid,$linkcall_apply1);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        //根据 $uid去推送给用户   主播拒绝
                        $m->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$uid);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        } 
                        //根据  $uid 登记这些用户被主播拒绝，登记用户连麦不成功
                        $m->linkcall_mysql_log_desapply(&$error,$sid,$singer_id,$uid);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }                       

                    }
                }

                // 3 单播连麦连接所有用户，断开连接
                {
                    // 查询当前连麦连接个数
                    $num_link = $m->get_user_link_index_count(&$error,$sid);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    
                    // A 查询当前连麦link 连接列表，取出连麦申请user_id
                    $link_list=array();
                    $m->get_user_link_index(&$error,$sid,&$link_list);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    // B 用查询到的user_id，去推送到相应的用户，主播删除连麦
                    $linkcall_apply2 = linkcall_model::$LINKCALL_APPLY_DEL;                    
                    foreach ($link_list as $uid )
                    {                       
                        //根据 $uid登记用户   主播删除
                        $m->set_user_apply_state(&$error,$sid,$uid,$linkcall_apply2);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        //根据 $uid去推送给用户   主播删除
                        $m->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$uid);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        
                        //由于主播关麦，连麦用户被动下麦，登记用户成功连麦数据
                        $m->linkcall_mysql_log_link_over(&$error,$sid,$singer_id,$uid);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                    }
                    
                }

                //1 清空当前所有申请列表
                $m->del_user_apply_index(&$error,$sid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }

                //2 清空当前所有连接列表
                $m->del_user_link_index(&$error,$sid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }               
                //3 广播直播间，当前连麦连接状态
                $m->linkcall_room_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //4 清空该房间所有申请数据
                $m->del_user_apply_time(&$error,$sid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                
                //5 清空该房间所有连接数据
                $m->del_user_link_time(&$error,$sid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }

            //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////

        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcall_set_state_rq error:".json_encode($error));
        }
  
        //linkcall_set_state_rs包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcall_set_state_rs';
        $rs['error'] = $error;
        $rs['sid'] = (int)$sid;
        $rs['linkcall_state'] = (int)$linkcall_state;
        $rs['op_code'] = (int)$op_code;
        $rs['num_apply'] = (int)$num_apply;
        $rs['num_link'] = (int)$num_link;     
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_set_state_rs sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_set_state_rs sid:".$sid." return:".json_encode($return));
        return $return;  
    }       


    
    // B用户（听众）发起/取消/退出连麦
    public static function on_linkcall_apply_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        LogApi::logProcess("on_linkcall_apply_rq rq:".json_encode($params));
        $sid                         = 0;
        $singer_id                   = 0;
        $singer_nick                 = '';
        $op_code                     = 0;      
        
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
        $data_cache['user_id']     = 0;
        $data_cache['user_nick']   = '';
        $data_cache['user_icon']   = '';
        $data_cache['user_wealth'] = 0;
        $data_cache['user_level']  = 0;
        $data_cache['is_singer']   = 0;
        
        $data_cache['user_id']     = (int)$user_data['user_id'];
        $data_cache['user_nick']   = $user_data['user_nick'];
        $data_cache['is_singer']   = (int)$user_data['is_singer'];
        
        $user_id     =(int)$data_cache['user_id'];
        $user_nick  =$data_cache['user_nick'];
        $is_singer   =(int)$data_cache['is_singer'];
        //初始化用户记录数据
        $linkcall_apply = $user_data['linkcall_apply']    =0;
        $time_apply     = $user_data['time_apply']        =0;
        $time_allow     = $user_data['time_allow']        =0;      
        
        
        $num_apply = 0;
        $num_link  = 0;
        //////////rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {
            if (0 == $sid || 0== $singer_id || (!(1 == $op_code || 2 == $op_code || 3 == $op_code|| 4 == $op_code)) || (!(0 ==$is_singer || 1 ==$is_singer)))
            {
                // 403300010(010)无效的参数
                $error['code'] = 403300010;
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
                // 403300021(21)主播未开启连麦功能
                $rs['linkcall_state'] = $linkcall_state;
                $error['code'] = 403300021;
                $error['desc'] = '主播未开启连麦功能';
                break;
            }
            
            //逻辑功能（主播是开启连麦状态）////////////////////////////////////////////////////////////////////////////////
            $linkcall_state = linkcall_model::$LINKCALL_STATE_OPEN;
            //情景1：用户发起连麦申请    1 == $op_code
            if ( 1 == $op_code)
            {
                //用户发起申请
                $time_allow =0;
                $linkcall_apply = linkcall_model::$LINKCALL_APPLY_APPLY;
                $m->user_apply_apply_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$data_cache,&$linkcall_apply,&$linkcall_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
            
            //情景2：用户取消连麦申请    2 == $op_code
            if ( 2 == $op_code)
            {                       
                //用取消起申请
                $time_allow =0;
                $linkcall_apply = linkcall_model::$LINKCALL_APPLY_DESAPPLY;
                $m->user_apply_desapply_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_apply,&$linkcall_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
            
            //情景3：用户退出连麦功能    3 == $op_code
            if ( 3 == $op_code)
            {
                //用户退出连麦连接
                $linkcall_apply = linkcall_model::$LINKCALL_APPLY_OUT;
                $m->user_apply_out_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_apply,&$linkcall_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
            //情景4：用户退出连麦功能    4 == $op_code
            if ( 4 == $op_code)
            {
                //用户30s到期后主播拒绝
                $linkcall_apply = linkcall_model::$LINKCALL_APPLY_NO;
                $m->user_apply_desapply_30s_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_apply,&$linkcall_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
        
            //逻辑功能（主播是开启连麦状态）结束//////////////////////////////////////////////////////////////
            //rs 回包拼装
            //取出用户连麦申请时间
            $time_apply = $m->get_user_apply_time(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //取出用户连麦连接时间
            $time_allow = $m->get_user_link_time(&$error,$sid,$user_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //取出用户申请个数
            $num_apply = $m->get_user_apply_index_count(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //取出用户连接个数
            $num_link = $m->get_user_link_index_count(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcall_apply_rq error:".json_encode($error));
        }
        $time_now = time();
        $rs = array();
        $rs['cmd'] = 'linkcall_apply_rs';
        $rs['error'] = $error;
        $rs['sid'] = (int)$sid;
        $rs['time_apply'] = (int)$time_apply;
        $rs['time_allow'] = (int)$time_allow;
        $rs['time_now'] = (int)$time_now;
        $rs['singer_id'] =  (int)$singer_id;
        $rs['singer_nick'] = $singer_nick;
        $rs['linkcall_state'] = (int)$linkcall_state;
        $rs['op_code'] = (int)$op_code;
        $rs['num_apply'] = (int)$num_apply;
        $rs['num_link'] = (int)$num_link; 

        
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_apply_rs sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_apply_rs sid:".$sid." return:".json_encode($return));
        return $return;      
    }   
    
    
    // C 主播允许/拒绝/删除连麦
    public static function on_linkcall_allow_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        LogApi::logProcess("on_linkcall_allow_rq rq:".json_encode($params));

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
        $data_uid =array();
        $num_link =0 ;
        $num_apply = 0;
        
        //redis 错误码，新容错功能
        $error_redis = array();
        $error_redis['code'] = 0;
        $error_redis['desc'] = '';
        //////////rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {

            if (0 == $sid || 0== $singer_id || (!(1 == $op_code || 2 == $op_code || 3 == $op_code)) || 0 == $user_id)
            {
                // 403300010(010)无效的参数
                $error['code'] = 403300010;
                $error['desc'] = '无效的请求参数';
                break;
            }
            $m = new linkcall_model();

            //逻辑功能（主播是开启连麦状态）////////////////////////////////////////////////////////////////////////////////
            $linkcall_state = linkcall_model::$LINKCALL_STATE_OPEN;

            //情景1：主播允许连麦申请    1 == $op_code
            if ( 1 == $op_code)
            {
                //主播允许申请

                $linkcall_apply = linkcall_model::$LINKCALL_APPLY_YES;
                $m->singer_apply_yes_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_state,&$linkcall_apply);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }

            //情景2：主播拒绝连麦申请    2 == $op_code
            if ( 2 == $op_code)
            {   
                //主播拒绝申请
                $linkcall_apply = linkcall_model::$LINKCALL_APPLY_NO;
                $m->singer_apply_no_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_state,&$linkcall_apply);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }

            
            //情景3：主播删除连麦功能    3 == $op_code
            if ( 3 == $op_code)
            {
                //主播删除连接
                $linkcall_apply = linkcall_model::$LINKCALL_APPLY_DEL;
                $m->singer_apply_del_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_state,&$linkcall_apply);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }


        //逻辑功能（主播是开启连麦状态）结束////////////////////////////////////////////////////////////////////////////            
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcall_allow_rq error:".json_encode($error));
        }
        //容错拼装/////////////////////////////////////////////////////////////////////////////////////////////////////
        //拼装回包rs
        do 
        {
            //取出该用户 uid data
            if (0 ==$error['code']) 
            {
                $m->linkcall_userdata_by_uid(&$error_redis,$sid,$user_id,&$data_uid);
                if (0 != $error_redis['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
            else 
            {
                //推送房间广播
                $m->linkcall_room_state_nt(&$error_redis,&$return,$sid,$singer_id,$singer_nick,$linkcall_state);
                if (0 != $error_redis['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //容错处理，当出客户端现重复操作，时，data带回操作的用户user_id
                $data_uid['user_id'] = (int)$user_id;
            }

            //取出当前房间的连接总数
            $num_link = $m->get_user_link_index_count(&$error_redis,$sid);
            if (0 != $error_redis['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //取出当前房间的申请总数
            $num_apply = $m->get_user_apply_index_count(&$error_redis,$sid);
            if (0 != $error_redis['code'])
            {
                //出现了一些逻辑错误
                break;
            }

        }while(FALSE);
        if (0 !=$error_redis['code'])
        {
            LogApi::logProcess("on_linkcall_allow_rq error_redis:".json_encode($error_redis));
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////
        if (0 != $error_redis['code'])
        {
            //出现了一些基础错误
            $error['code'] = $error_redis['code'];
            $error['desc'] = $error_redis['desc'];
        
        }
        $data_uid['time_now'] = time();
        //linkcall_allow_rs包回包
        $rs = array();
        $rs['cmd'] = 'linkcall_allow_rs';
        $rs['error'] = $error;
        $rs['sid'] = (int)$sid;
        $rs['singer_id']  = (int)$singer_id;
        $rs['singer_nick']  = $singer_nick;
        $rs['op_code'] = (int)$op_code;
        $rs['num_link'] =(int)$num_link;
        $rs['num_apply'] =(int)$num_apply;        
        $rs['data'] = $data_uid;
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_allow_rs sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_allow_rs sid:".$sid." return:".json_encode($return));
        return $return;
        
    }
    
    // D 主播查询最新申请列表
    public static function on_linkcall_list_singer_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        LogApi::logProcess("on_linkcall_list_singer_rq rq:".json_encode($params));
        //linkcall_allow_rq包数据，拆解rq包
        $sid                         = $params['sid'];
        $singer_id                   = $params['singer_id'];        
        $singer_nick                 = $params['singer_nick'];

        //
        //初始化回包信息
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
        $datas = array();
        //////////rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {
            $m = new linkcall_model();
            //参数是否合法
            if (0 == $sid || 0== $singer_id  )
            {
                // 403300010(010)无效的参数
                $error['code'] = 403300010;
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
            if ($linkcall_state == linkcall_model::$LINKCALL_STATE_CLOSED) 
            {
                //主播是关闭连麦状态，直接返回当前连麦状态，连麦数据为空
                $rs['datas'] = $datas;
            }
            else
            {
                $m = new linkcall_model();
                //逻辑功能（主播是开启连麦状态）////////////////////////////////////////////////////////////////////////////////
                
                //1 取出该主播所有连接用户datas
                $m->linkcall_link_all_user_datas(&$error,$sid,&$datas);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //2 取出该主播所有申请用户datas
                $m->linkcall_apply_all_user_datas(&$error,$sid,&$datas);
                if (0 != $error['code'])
                {
                    
                    //出现了一些逻辑错误
                    break;
                }  

            }

            //逻辑功能（主播是开启连麦状态）结束////////////////////////////////////////////////////////////////////////////
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcall_list_singer_rq error:".json_encode($error));
        }
        $rs = array();
        $rs['cmd'] = 'linkcall_list_singer_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['singer_id']  = $singer_id;
        $rs['singer_nick']  = $singer_nick;
        $rs['linkcall_state'] = $linkcall_state ;
        $rs['datas'] = $datas;
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_list_singer_rs sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_list_singer_rs sid:".$sid." return:".json_encode($return));
        return $return;
    }
    

    // E 用户（主播/用户）查询当前最新连麦信息
    public static function on_linkcall_list_user_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $error['code'] = 0;
        $error['desc'] = '';
        $data_cache = array();
        LogApi::logProcess("on_linkcall_list_user_rq rq:".json_encode($params));
        //linkcall_allow_rq包数据，拆解rq包
        $sid                         = $params['sid'];
        $singer_id                   = $params['singer_id'];
        //查表取出用户和主播信息
        $userInfo = new UserInfoModel();
        //$info_user = $userInfo->getInfoById($user_id);
        $info_singer = $userInfo->getInfoById($singer_id);
        $singer_nick = $info_singer['nick'];      
    
        //
        //初始化回包信息
        //b_error.info  rs回包错误信息default

        $datas =array();

        //////////rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {
            $m = new linkcall_model();
            //参数是否合法
            if (0 == $sid || 0== $singer_id  )
            {
                // 403300010(010)无效的参数
                $error['code'] = 403300010;
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
            if ($linkcall_state == linkcall_model::$LINKCALL_STATE_CLOSED)
            {
                //主播关闭连麦，直接返回当前连麦状态，连麦数据为空  
                $rs['datas'] = $datas;
            }
            else 
            {
                $m = new linkcall_model();
                //逻辑功能（主播是开启连麦状态）////////////////////////////////////////////////////////////////////////////////
                
                //1 取出该主播所有连接用户datas
                $m->linkcall_link_all_user_datas(&$error,$sid,&$datas);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                } 
            }

            //逻辑功能（主播是开启连麦状态）结束////////////////////////////////////////////////////////////////////////////
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcall_list_user_rq error:".json_encode($error));
        }
        //拼装回包
        //linkcall_list_singer_rs包回包
        $rs = array();
        $rs['cmd'] = 'linkcall_list_user_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['singer_id']  = $singer_id;
        $rs['singer_nick']  = $singer_nick;
        $rs['linkcall_state'] = $linkcall_state ;
        $rs['datas'] = $datas;
        
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_list_user_rs sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_list_user_rs sid:".$sid." return:".json_encode($return));
        return $return;
    } 
    
    
    // 1 用户进入直播间
    public static function on_linkcall_user_in($params)
    {
        //客户端用户进入查询连麦信息
        //$m = new linkcall_api();
        //$m->on_linkcall_list_user_rq($params);
    }
    
    
    // 2 用户退出直播间
    public static function on_linkcall_user_out($params,&$return)
    {
        LogApi::logProcess("on_linkcall_user_out nt:".json_encode($params));
        $error = array();
        $error['code'] = 0;
        $error['desc'] = '';
        $sid = $params['sid'];
        $user_id = $params['uid'];
        $singer_id   = $params['singer_id'];
        //查表取出用户和主播信息
        $userInfo = new UserInfoModel();
        //$info_user = $userInfo->getInfoById($user_id);
        $info_singer = $userInfo->getInfoById($singer_id);
        $singer_nick = $info_singer['nick'];
        //用户是否在连麦功能当中
        do
        {
            $m = new linkcall_model();
            //查询连麦状态表
            $linkcall_state = $m->get_singer_linkcall_state(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //查询自己是否在连麦申请列表中
            $is_apply = $m->find_user_apply_index(&$error,$sid,$user_id);
            if (false == $is_apply )
            {
                //不在申请列表，登记不在申请列表

            }
            else 
            {
                //如果在申请列表，用取消起申请
                $linkcall_apply = linkcall_model::$LINKCALL_APPLY_DESAPPLY;
                $m->user_apply_desapply_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_apply,&$linkcall_state);
            }
                
            //查询自己是否在连麦连接列表中
            
            $is_allow = $m->find_user_link_index(&$error,$sid,$user_id);            
            if (false == $is_allow )
            {
                //不在连接列表，登记不在连接列表
                
            }
            else
            {
                //用退出连麦                
                $linkcall_apply = linkcall_model::$LINKCALL_APPLY_OUT;
                $m->user_apply_out_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_apply,&$linkcall_state);
            }   
        }while(FALSE); 
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcall_user_out error:".json_encode($error));
        }
        LogApi::logProcess("on_linkcall_user_out sid:".$sid." return:".json_encode($return));
        
    }
    
    // 3 主播户进入直播间
    public static function on_linkcall_singer_start($params)
    {
        //客户端主播进入查询连麦信息
        //主播查询最新申请列表        
        //$m = new linkcall_api();
        //$m->on_linkcall_list_singer_rq($params);
    }
    
    
    // 4 主播退出直播间
    public static function on_linkcall_singer_over($params,&$return)
    {
        $error = array();
        $error['code'] = 0;
        $error['desc'] = '';
        LogApi::logProcess("on_linkcall_singer_over nt:".json_encode($params));
        $sid         = $params['sid'];
        $singer_id   = $params['singer_id'];
        //查表取出用户和主播信息
        $userInfo = new UserInfoModel();
        $info_singer = $userInfo->getInfoById($singer_id);
        $singer_nick = $info_singer['nick'];
 
        //查询主播是否在连麦当中        
        do
        {
            //1拒绝所有连麦///////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_model();
            //查询连麦状态表
            $linkcall_state = $m->get_singer_linkcall_state(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            
            //查询当前连麦申请列表，取出连麦申请user_id
            $apply_list=array();
            $m->get_user_apply_index(&$error,$sid,&$apply_list);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //用查询到的user_id，去推送到相应的用户，主播拒绝申请
            $linkcall_apply_for_a = linkcall_model::$LINKCALL_APPLY_NO;
            foreach ($apply_list as $uid)
            {
                $data_get = array ();
                //根据 $uid修改当前用户的申请状态为   主播拒绝
                $m->set_user_apply_state(&$error,$sid,$uid,$linkcall_apply_for_a);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //根据 $uid去推送给用户   主播拒绝     
                $m->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$uid);                
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //根据 $uid去推送给主播   主播拒绝
                $m->linkcall_apply_singer_nt($error, $return, $sid, $singer_id, $singer_nick, $linkcall_state, $uid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                
                //由于主播下麦，申请用户被动拒绝，登记用户连麦不成功
                $m->linkcall_mysql_log_desapply(&$error,$sid,$singer_id,$uid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
            // 由于主退出连麦，清空申请列表
            $m->del_user_apply_index(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //2断开所有连麦////////////////////////////////////////////////////////////////////////////////////////////
            //查询当前连麦申请列表，取出连麦申请user_id
            $allow_list=array();
            $m->get_user_link_index(&$error,$sid,&$allow_list);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //用查询到的user_id，去推送到相应的用户，主播断开连麦
            $linkcall_apply_for_l = linkcall_model::$LINKCALL_APPLY_DEL;
            foreach ($allow_list as $uid)
            {
                //根据 $uid修改当前用户的申请状态为   主播断开
                $m->set_user_apply_state(&$error,$sid,$uid,$linkcall_apply_for_l);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //根据 $uid去推送给用户   主播断开
                $m->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$uid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //根据 $uid去推送给主播   主播断开
                $m->linkcall_apply_singer_nt($error, $return, $sid, $singer_id, $singer_nick, $linkcall_state, $uid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //由于主播下麦，用户被动下麦，登记用户成功连麦数据
                $m->linkcall_mysql_log_link_over(&$error,$sid,$singer_id,$uid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            }
            // 由于主退出连麦，清空连麦列表
            $m->del_user_link_index(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            // 推送当前房间的连麦状态
            $m->linkcall_room_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //清空缓存////////////////////////////////////////////////////////////////////////////////////////////
            //1 清空房间用户缓存
            $m->del_user_data_json(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
 
            //2 清空房间用户申请状态
            $m->del_user_apply_state(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }  
            
            //3 清空房间连麦申请时间
            $m->del_user_apply_time(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            //4 清空房间连麦连接时间
            $m->del_user_link_time(&$error,$sid);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcall_singer_over error:".json_encode($error));;
        }
        LogApi::logProcess("on_linkcall_singer_over sid:".$sid." return:".json_encode($return));
    }
    
    
}