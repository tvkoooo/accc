<?php
// 连麦api接口
class linkcall_pk_api
{   
    // 1.1 主播打开连麦pk功能
    public static function on_linkcallpk_singer_open_function_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_open_function_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            $singers      = array();
            //on_linkcallpk_siger_open_function_rq 包数据，拆解rq包
            $singer_id   = (int)$params['singer_id'];
            $singer_sid  = (int)$params['singer_sid'];
            $pk_open     = (int)$params['pk_open'];
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            $open_state = 0;
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $singer_id || 0 == $singer_sid || !($pk_open == 0 || $pk_open == 1) )
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////

                if ($pk_open == 1)
                {
                    //0首先先查询当前主播的pk状态
                    $time_open = $m->redis_get_online_singer_list_opentime(&$error,$singer_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    //如果之前主播就是开启状态
                    if ($time_open != 0)
                    {
                        //直接设置开启
                        $open_state = 1;
                        break;
                    }
                    //1   查询该主播是否满足连麦pk 条件
                    //1.1 取出连麦pk功能最低星级要求
                    $info_id = linkcall_pk_model::$LINKCALL_PK_SINGER_STAR;
                    $info_value = $m->redis_get_mysql_info(&$error,$info_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    //1.2 取出该主播的星级
                    $singer_cache = array();
                    $singer_redis = new UserAttributeModel();
                    $get_singer_info  = $singer_redis->getAttrByUid($singer_id);
                    $singer_cache["singer_star"]  = (int)$get_singer_info["experience_level"];
                    $singer_star_get = $singer_cache["singer_star"];
                    //1.3 如果该主播不满足，直接报错返回
                    LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_open_function_rq info_value:$info_value singer_star_get:$singer_star_get ");
                    if ($singer_cache["singer_star"] < $info_value)
                    {
                        // 403400022(022)条件不满足，无法开启主播pk
                        $error['code'] = 403400022;
                        $error['desc'] = '条件不满足，无法开启主播pk';
                        break;
                    }
            
                    //2   登记主播信息
                    //2.1 取出主播临时缓存
                    $singer_cache["singer_level"]  = (int)$get_singer_info["active_level"];
                    $user_redis = new UserInfoModel();
                    $get_user_info = $user_redis->getInfoById($singer_id);
                    $singer_cache["singer_icon"]  = $get_user_info["photo"];
                    $singer_cache["singer_nick"]  = $get_user_info["nick"];
                    $singer_cache["singer_id"] = $singer_id ;
                    $singer_cache["singer_sid"] = $singer_sid ;
                    //2.2 登记主播信息
                    $m->redis_set_singer_info(&$error,$singer_id,&$singer_cache);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
            
                    //3   登记服务器满足条件的主播列表
                    $m->redis_set_online_singer_list(&$error,$singer_id,$time_now);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $open_state = 1;
                }
                else
                {
                    //0首先先查询当前主播的pk状态
                    $time_open = $m->redis_get_online_singer_list_opentime(&$error,$singer_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    //如果之前主播就是开启状态
                    if ($time_open == 0)
                    {
                        //直接设置开启
                        $open_state = 0;
                        break;
                    }
                    //1 找到该主播申请的申请列表，对申请过的主播一一进行推送，主播下线了
                    $objsinger_list = array();
                    $m->redis_get_singer_guest_apply_list(&$error,$singer_id,&$objsinger_list);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    if(true == empty($objsinger_list))
                    {
                        // 403400012(012)读取数据为空,不需要进行任何nt操作
            
                    }
                    else
                    {
                        foreach ($objsinger_list as $nt_singer_id)
                        {
                            //1 根据主播id，查找主播sid
                            $nt_singer_cache = array();
                            $m->redis_get_singer_info(&$error,$nt_singer_id,&$nt_singer_cache);
                            if (0 != $error['code'])
                            {
                                //出现了一些逻辑错误
                                break;
                            }
                            $nt_singer_sid = $nt_singer_cache["singer_sid"];
            
                            //给所有申请了的主播推送主播下线了
                            $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_OFFLINE;
                            $m->linkcallpk_singer_nt_singer_pk_info(&$error,$nt_singer_id,$nt_singer_sid,$singer_id,$pk_state);
                            if (0 != $error['code'])
                            {
                                //出现了一些逻辑错误
                                break;
                            }
            
                        }
                    }
            
                    LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_leave_event  singer_id:$singer_id");
                    //2 删除该主播申请列表
                    $m->redis_del_singer_guest_apply_list(&$error,$singer_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
            
                    //3 删除该主播请求列表
                    $m->redis_del_singer_host_link_list(&$error,$singer_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
            
                    //4 该主播在在线连麦pk列表中移除
                    LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_leave_event rem singer_id:$singer_id ");
                    $m->redis_rem_online_singer_list(&$error,$singer_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $open_state = 0;
                }
            
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_open_function_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_singer_open_function_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_singer_open_function_rs';
            $rs['error'] = $error;
            $rs['singer_id'] = (int)$singer_id;
            $rs['time_now'] = $time_now;
            $rs['open_state'] = (int)$open_state;
            
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_open_function_rs guest_id：$singer_id sid:".$singer_sid." rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_open_function_rs guest_id：$singer_id sid:".$singer_sid." return:".json_encode($return));
            return $return;
        }


    }
    
    // 1.2 主播查询当前在线满足条件主播申请列表
    public static function on_linkcallpk_singer_seek_online_list_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_seek_online_list_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            $singers      = array();
            //on_linkcallpk_siger_seek_online_list_rq 包数据，拆解rq包
            $singer_id   = (int)$params['singer_id'];
            $singer_sid  = (int)$params['singer_sid'];
            $page_num    = (int)$params['page_num'];
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $singer_id || 0 == $singer_sid || $page_num < 0)
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////

                //1 取出分页为pag_num 的主播信息发给客户端
                $m->linkcallpk_apply_singer_datas_by_pag_num(&$error,$singer_id,$page_num,&$singers);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_seek_online_list_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_singer_seek_online_list_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_singer_seek_online_list_rs';
            $rs['error'] = $error;
            $rs['guest_id'] = (int)$singer_id;
            $rs['time_now'] = $time_now;
            $rs['singers'] = $singers;
            
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_seek_online_list_rs guest_id：$singer_id sid:".$singer_sid." rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_seek_online_list_rs guest_id：$singer_id sid:".$singer_sid." return:".json_encode($return));
            return $return;
        }    
    }
    
    // 2 主播客场申请pk
    public static function on_linkcallpk_apply_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_apply_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            
            //on_linkcallpk_apply_rq 包数据，拆解rq包
            $guest_id   = (int)$params['guest_id'];//发起主播id
            $guest_sid  = (int)$params['guest_sid'];//发起主播sid
            $host_id    = (int)$params['host_id'];//目标主播id
            $host_sid  = (int)$params['host_sid'];//目的主播sid
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $guest_id || $host_id == 0)
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////

                //1 设置客场主播回包rs的  pk状态   已申请
                $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_APPLYING;
            
                //2 查询该申请的主播是否在线
                $get_apply_time = $m->redis_get_online_singer_list_opentime(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($get_apply_time == 0)
                {
                    //主播下线了
                    $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_OFFLINE;
                }
            
                //3 把发起主播的申请推送给目标主播 nt
                //目标主播收到nt状态是  连线link
                $nt_pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_LINK;
                $m->linkcallpk_singer_nt_singer_pk_info(&$error,$host_id,$host_sid,$guest_id,$nt_pk_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //4 给被操作的主场主播，主场列表增加一个请求（被操作的人请求列表增加一个连线请求）
                $m->redis_set_singer_host_link_list(&$error,$host_id,$guest_id,$time_now);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //5 给主动操作的客场主播，客场列表增加一个申请（主动操作的人申请列表增加一个申请状态）
                $m->redis_set_singer_guest_apply_list(&$error,$guest_id,$host_id,$time_now);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_apply_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_apply_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_apply_rs';
            $rs['error'] = $error;
            $rs['pk_state'] = (int)$pk_state;
            $rs['guest_id'] = (int)$guest_id;
            $rs['host_id'] = (int)$host_id;
            $rs['time_now'] = $time_now;
            
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid return:".json_encode($return));
            return $return;
        }

    }
    
    // 3.1 主播主场连线pk
    public static function on_linkcallpk_link_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_link_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            
            //on_linkcallpk_link_rq 包数据，拆解rq包
            $host_id    = (int)$params['host_id'];//发起主播id
            $host_sid  = (int)$params['host_sid'];//发起主播sid
            $guest_id   = (int)$params['guest_id'];//目标主播id
            $guest_sid  = (int)$params['guest_sid'];//目的主播sid
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            $pk_state = 0;
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid )
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////

                //0 设置主场主播回包rs的  pk状态     已连线
                $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_LINKING;
            
                //1 查询该连线的主播是否在线
                $get_apply_time = $m->redis_get_online_singer_list_opentime(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($get_apply_time == 0)
                {
                    // 403400031(031)对方已离线，连线失败
                    $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_OFFLINE;
                    $error['code'] = 403400031;
                    $error['desc'] = '对方已离线，连线失败';
                    break;
                }
            
                //2 查询该连线的主播是否正在连麦pk
                $get_pkid = $m->redis_get_singer_pkid(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($get_pkid != 0)
                {
                    // 403400025(025)该主播已在PK中，连线失败
                    $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_PKING;
                    $error['code'] = 403400025;
                    $error['desc'] = '该主播已在PK中，连线失败';
                    break;
                }
            
                //3 查询该连线的主播是否正在玩游戏
                $game_api = new game_manager_model();
                $get_game_g = $game_api->get_game_guess_dice_inf($guest_id);
                if ($get_game_g['code'] == 0)
                {
                    // 403400028(028)游戏进行中，无法开启主播PK
                    $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_GAMING;
                    $error['code'] = 403400028;
                    $error['desc'] = '游戏进行中，无法开启主播PK';
                    break;
                }
            
                //4 查询该连线的主播是否正在玩电锯
                $get_game_s = $game_api->get_game_saw_inf($guest_id);
                if ($get_game_s['code'] == 0)
                {
                    // 403400029(029)游戏进行中，无法开启主播PK
                    $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_SAWING;
                    $error['code'] = 403400029;
                    $error['desc'] = '游戏进行中，无法开启主播PK';
                    break;
                }
            
                //5 查询该连线的主播是否有人已经给他申请
                $get_popup_time = $m->redis_get_pk_popup(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //如果取出有时间，说明该主播正在pk准备当中
                if ($get_popup_time)
                {
                    // 403400030(030)主播pk弹窗未处理，但是给客户端的是     该主播已在PK中，连线失败
                    $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_POPUP;
                    $error['code'] = 403400030;
                    $error['desc'] = '该主播已在PK中，连线失败';
                    break;
                }
            
                //6 如果上述情况均为发生，需要推送nt给目标主播，主场主播连线客场主播，给发送的主播pk状态是： 弹窗popup（一个弹窗申请）
                $nt_pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_POPUP;
                $m->linkcallpk_singer_nt_singer_pk_info(&$error,$guest_id,$guest_sid,$host_id,$nt_pk_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //7 目标主播 $guest_id 已经收到一个弹窗，需要记录这个主播的弹窗时间
                $m->redis_set_pk_popup(&$error,$guest_id,$time_now);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //8 目标主播 $guest_id 已经收到一个弹窗，需要记录是谁发给他的弹窗
                $m->redis_set_guest_popup_from_host(&$error,$host_id,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_link_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_link_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_link_rs';
            $rs['error'] = $error;
            $rs['pk_state'] = (int)$pk_state;
            $rs['host_id'] = (int)$host_id;
            $rs['guest_id'] = (int)$guest_id;
            $rs['time_now'] = $time_now;
            
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_link_rs host_id:$host_id host_sid:$host_sid guest_id:$guest_id guest_sid:$guest_sid rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_link_rs host_id:$host_id host_sid:$host_sid guest_id:$guest_id guest_sid:$guest_sid return:".json_encode($return));
            return $return;
        }

    
    }
    
    // 3.2 主播客场确认pk功能
    public static function on_linkcallpk_confirm_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_confirm_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            $pk_info      = array();
            //on_linkcallpk_confirm_rq 包数据，拆解rq包
            $guest_id   = (int)$params['guest_id'];//发起主播id
            $guest_sid  = (int)$params['guest_sid'];//发起主播sid
            $host_id    = (int)$params['host_id'];//目标主播id
            $host_sid  = (int)$params['host_sid'];//目的主播sid
            $op_code   = (int)$params['code'];//主播同意或拒绝的操作码
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            $pk_state = 0;
            $pkid = 0;
            
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid || !($op_code == 1 || $op_code == 2 ))
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////           

                //0.1 需要先去掉主播弹窗限制(弹窗时间)
                $m->redis_rem_pk_popup(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //0.2 需要先去掉主播弹窗限制（发送弹窗的主播）
                $m->redis_rem_guest_popup_from_host(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //1 查询该同意的主播是否还在线
                $get_apply_time = $m->redis_get_online_singer_list_opentime(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($get_apply_time == 0)
                {
                    //主播下线了
                    $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_OFFLINE;
                }
            
                //2 设置  pk状态   要么回复同意连麦，要么是回复拒绝连麦
                if ($op_code == 1)
                {
                    $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_YES;
                    //同意后需要马上建立一个占位pkid
                    //服务器登记一个占位pkid，编译如果客户端断线重连进行重构pk界面
                    {
                        //a   创建占位pkid号
                        $pkid = $m->redis_create_pkid(&$error);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        //b   登记双方主播正式pk
                        $m->redis_set_singer_pkid(&$error,$pkid,$host_id);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        $m->redis_set_singer_pkid(&$error,$pkid,$guest_id);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        //c   登记双方主播初始送礼金额
                        $host_gift  = 0;
                        $guest_gift = 0;
                        $m->redis_set_pking_info_singer_gift(&$error,$host_id,$host_gift);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        $m->redis_set_pking_info_singer_gift(&$error,$guest_id,$guest_gift);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
            
                        //d   写入创建的 pk 信息
                        $pk_info["starttime"] = 0;
                        $pk_info["pkalltime"] = 0;
                        $pk_info["host_id"] = $host_id;
                        $pk_info["host_sid"] = $host_sid;
                        $pk_info["guest_id"] = $guest_id;
                        $pk_info["guest_sid"] = $guest_sid;
                        $pk_info["host_gift"] = $host_gift;
                        $pk_info["guest_gift"] = $guest_gift;
            
                        $m->redis_set_pk_info_use_array(&$error,$pkid,&$pk_info);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        //d  写入创建的占位pk双方信息的状态（创建pk界面）
                        $pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_READY;
                        $m->redis_set_pk_info_process(&$error,$pkid,$pk_process);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
            
                        //e  强制双方主播都会到pk界面
                        $pk_scene = linkcall_pk_model::$LINKCALL_PK_SCENE_PK ;
                        $m->redis_set_singer_scene(&$error,$pk_scene,$host_id);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        $m->redis_set_singer_scene(&$error,$pk_scene,$guest_id);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
            
                        //f  推送场景界面广播给两个房间
                        $m->linkcallpk_room_pk_scene_nt(&$error,$pkid,$host_id,$host_sid);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        $m->linkcallpk_room_pk_scene_nt(&$error,$pkid,$guest_id,$guest_sid);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        //g  推送主客场主播的资料给两个房间
                        $m->linkcallpk_room_pk_singer_info_nt(&$error,$host_id,$host_sid,$guest_id,$guest_sid);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
            
                    }
                }
                else
                {
                    $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_NO;
                    //如果是拒绝，需要在发起连线请求的主播列表当中移除  这个主播的申请连线请求
                    $m->redis_rem_singer_host_link_list(&$error,$host_id,$guest_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
            
                }
            
                //3 把发起主播的申请推送给目标主播 nt
                $m->linkcallpk_singer_nt_singer_pk_info(&$error,$host_id,$host_sid,$guest_id,$pk_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_confirm_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_confirm_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_confirm_rs';
            $rs['error'] = $error;
            $rs['pk_state'] = (int)$pk_state;
            $rs['guest_id'] = (int)$guest_id;
            $rs['host_id'] = (int)$host_id;
            $rs['time_now'] = $time_now;
            $rs['op_code'] = $op_code;
            $rs['pkid'] = (int)$pkid;
            $rs['pk'] = $pk_info;
            
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_confirm_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid op_code:$op_code rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_confirm_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid op_code:$op_code return:".json_encode($return));
            return $return;
        }

    }
    
    // 4 主场主播启动连麦pk（包括结算后再次pk，都是新产生一个pkid，全新的环境来pk）
    public static function on_linkcallpk_start_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_start_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            $pk_info      = array();
            //on_linkcallpk_start_rq 包数据，拆解rq包
            $host_id    = (int)$params['host_id'];//发起主播id
            $host_sid  = (int)$params['host_sid'];//发起主播sid
            $guest_id   = (int)$params['guest_id'];//目标主播id
            $guest_sid  = (int)$params['guest_sid'];//目的主播sid
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            $pk_state = 0;
            $pkid = 0;
            
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid )
                {
                    // 403400020(020)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////

                //A  判断主场是否可以开启连麦（条件1：刚刚创建连麦界面；条件2：客户端做了pk请求结算；条件3：由于双方客户端都断线，系统自己进行了结算）
                //B   首先取出主场主播之前的pkid号
                $get_h_pkid = $m->redis_get_singer_pkid(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $pkid = $get_h_pkid;
                //C   根据pkid 判断当前的pk状态，并获得当前pk信息（如果pkid = 0，取出的pk状态是没有pk）
                $get_pk_info = array();//备注本次取出的pk_info 没有什么用，只用于函数参数签名
                $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;//初始化
                $m->linkcallpk_pk_info_process_by_pkid(&$error,$pkid,&$pk_process,&$get_pk_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //情况一：如果这个pkid对应的pk没有在进行，有可能客户端掉线了，服务器登记的pk状态由于有主播退出导致结束pk。这个客户端再次来查询
                if ($pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK)
                {
                    // 403400026(026)PK结束
                    $error['code'] = 403400026;
                    $error['desc'] = 'PK结束';
                    break;
                }
                //情况二：如果这个pkid对应的pk还在进行，未结束
                if ($pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_PKING)
                {
                    // 403400025(025)该主播已在PK中，连线失败
                    $error['code'] = 403400025;
                    $error['desc'] = '该主播已在PK中，连线失败';
                    break;
                }
                //三种情况下启动pk：1，刚刚创建连麦界面（不需要再生成新的pkid）    2，客户端做了pk请求结算    3，系统自己进行了结算
                //   启用pkid号
                if ($pk_process != linkcall_pk_model::$LINKCALL_PK_PKINFO_READY)
                {
                    //在创建新的PKid前，如果主播直接点击开启连麦pk，默认主播发送了结算按钮，应该把上次的pkid给结束了
                    $new_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_ACCOUNT;
                    $m->redis_set_pk_info_process(&$error,$get_h_pkid,$new_pk_process);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    //如果是已经pk结算后的，需要重新创建一个新的pkid号，老的pkid只用于后续查询使用
                    $pkid = $m->redis_create_pkid(&$error);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                }
            
                //2   取出系统配置给的pk时间  $pkalltime
                $pkalltime = $m->redis_get_mysql_info(&$error,linkcall_pk_model::$LINKCALL_PK_LINK_PKTIME);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //3   刷新双方主播正式pk 新的pkid号
                $m->redis_set_singer_pkid(&$error,$pkid,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->redis_set_singer_pkid(&$error,$pkid,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //4.1  移除双方之前有的送礼金币数据（开始新的一局pk，礼物重新结算,需要删除之前送礼数据）
                $m->redis_rem_pking_info_singer_gift(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                $m->redis_rem_pking_info_singer_gift(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //4.1  移除双方之前有的送礼金币数据（开始新的一局pk，礼物重新结算,重新清零开始）
                $m->redis_set_pking_info_singer_gift(&$error,$host_id,0);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->redis_set_pking_info_singer_gift(&$error,$guest_id,0);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //4.3  删除两个主播有可能的之前     用户送礼列表
                $m->redis_del_user_gift_list(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->redis_del_user_gift_list(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //5   登记pk信息,修改pk状态变为正在pk(包括结算后再次pk，都是新产生一个pkid，全新的环境来pk,因此双方礼物金币都是0)
                $time_now  = time(); //修正系统时间误差
                $starttime = $time_now;
                $pkalltime_3s = $pkalltime * 60 + 3;//备注：启动pk还有个3s的倒计时需要添加进去，取出的时间是分钟单位
                $pk_info["starttime"] = $starttime;
                $pk_info["pkalltime"] = $pkalltime_3s;
                $pk_info["host_id"] = $host_id;
                $pk_info["host_sid"] = $host_sid;
                $pk_info["guest_id"] = $guest_id;
                $pk_info["guest_sid"] = $guest_sid;
                $pk_info["host_gift"] = 0;
                $pk_info["guest_gift"] = 0;
                $m->redis_set_pk_info_use_array(&$error,$pkid,&$pk_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_PKING;
                $m->redis_set_pk_info_process(&$error,$pkid,$pk_process);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //6   推送pk 开启  给 客场主播$guest_id
                $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_START;
                $m->linkcallpk_singer_nt_singer_pk_info(&$error,$guest_id,$guest_sid,$host_id,$pk_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //7   在两个房间进行广播  pk信息 (当前只是推送pk信息，没有送礼信息，因此送礼用户和接收用户都为0，送礼消失为空)
                $user_id = 0 ;//送礼用户是空
                $singer_id = 0;//收礼用户是空
                $m->linkcallpk_room_nt_pk_info(&$error,$pkid,$user_id,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //8  强制双方主播都会到pk界面
                $pk_scene = linkcall_pk_model::$LINKCALL_PK_SCENE_PK ;
                $m->redis_set_singer_scene(&$error,$pk_scene,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->redis_set_singer_scene(&$error,$pk_scene,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //9  推送场景界面广播给两个房间
                $m->linkcallpk_room_pk_scene_nt(&$error,$pkid,$host_id,$host_sid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->linkcallpk_room_pk_scene_nt(&$error,$pkid,$guest_id,$guest_sid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_start_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_confirm_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_start_rs';
            $rs['error'] = $error;
            $rs['host_id'] = (int)$host_id;
            $rs['time_now'] = $time_now;
            $rs['pk_state'] = (int)$pk_state;
            $rs['pkid'] = (int)$pkid;
            $rs['pk'] = $pk_info;
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_start_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_start_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid return:".json_encode($return));
            return $return;
        }

    }
    
    
    // 5.1 主播结算pk（主场和客场主播都发结束请求，按照先到请求，并且满足pk结束时间来结算）
    public static function on_linkcallpk_count_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_count_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            $pk_info      = array();
            //on_linkcallpk_count_rq 包数据，拆解rq包
            $host_id    = (int)$params['host_id'];  //发起主播id
            $host_sid   = (int)$params['host_sid']; //发起主播sid
            $guest_id   = (int)$params['guest_id']; //目标主播id
            $guest_sid  = (int)$params['guest_sid'];//目的主播sid
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            $pk_state = 0;
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid )
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////

                //1  查看之前是否有进行结算，然后结算pk（主场客场都会发结算请求，只按照满足要求最先到的rq来结算，后来那个rq会返回已经结算错误，这个错误客户端需要忽略）
                //1.1 查询该主场主播的   服务器正在pk列表，如果pk列表反馈的pkid = 0，说明当前pk已经结算。
                $get_pkid = $m->redis_get_singer_pkid(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($get_pkid == 0)
                {
                    // 403400026(026)PK结束
                    $error['code'] = 403400026;
                    $error['desc'] = 'PK结束';
                    break;
                }
            
                //1.2 取出pk信息。
                $pkid = $get_pkid;
                $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;//初始化
                $m->linkcallpk_pk_info_process_by_pkid(&$error,$pkid,&$get_pk_process,&$pk_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //2  查询到的是系统已经自动进行结算
                if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_BEYOND)
                {
                    //2.1  强制双方主播都会到pk界面
                    $pk_scene = linkcall_pk_model::$LINKCALL_PK_SCENE_PK ;
                    $m->redis_set_singer_scene(&$error,$pk_scene,$host_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $m->redis_set_singer_scene(&$error,$pk_scene,$guest_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
            
                    //2.2  推送场景界面广播给两个房间
                    $m->linkcallpk_room_pk_scene_nt(&$error,$pkid,$host_id,$host_sid);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $m->linkcallpk_room_pk_scene_nt(&$error,$pkid,$guest_id,$guest_sid);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
            
                    //2.3   推送nt给两个主播，服务器已经进行了结算
                    $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_COUNT;
                    $m->linkcallpk_singer_nt_singer_pk_info(&$error,$guest_id,$guest_sid,$host_id,$pk_state);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $m->linkcallpk_singer_nt_singer_pk_info(&$error,$host_id,$host_sid,$guest_id,$pk_state);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
            
                    //2.4   在两个房间进行广播  pk 信息 (当前只是推送pk结算信息，没有送礼信息，因此送礼用户和接收用户都为0，送礼消失为空)
                    $user_id = 0 ;//送礼用户是空
                    $singer_id = 0;//收礼用户是空
                    $m->linkcallpk_room_nt_pk_info(&$error,$pkid,$user_id,$singer_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    //2.5  刷新这个pkid 的pk 过程状态 改为：已经结算
                    $pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_ACCOUNT;
                    $m->redis_set_pk_info_process(&$error,$pkid,$pk_process);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $error['code'] = 0;
                    $error['desc'] = '';
                    break;
            
                }
                if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_PKING)
                {
                    // 403400023(023)连麦pk还有计时未用完
                    $error['code'] = 403400023;
                    $error['desc'] = '连麦pk还有计时未用完';
                    break;
                }
                if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_ACCOUNT)
                {
                    // 403400024(024)连麦pk已经结算完成，忽略错误
                    $error['code'] = 403400024;
                    $error['desc'] = '连麦pk已经结算完成，忽略错误';
                    break;
                }
                if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK)
                {
                    // 403400026(026)PK结束
                    $error['code'] = 403400026;
                    $error['desc'] = 'PK结束';
                    break;
                }
                if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_READY)
                {
                    // 403400027(027)PK还没有开始
                    $error['code'] = 403400027;
                    $error['desc'] = 'PK还没有开始';
                    break;
                }
            
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_count_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_count_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_count_rs';
            $rs['error'] = $error;
            $rs['time_now'] = $time_now;
            $rs['pk_state'] = (int)$pk_state;
            $rs['pk_state'] = (int)$pkid;
            $rs['pk'] = $pk_info;
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_count_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid pkid:$pkid rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_count_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid pkid:$pkid return:".json_encode($return));
            return $return;
        }

    } 
    
    // 5.2 主播延长连麦pk
    public static function on_linkcallpk_addtime_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_addtime_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            $pk_info      = array();
            //on_linkcallpk_addtime_rq 包数据，拆解rq包
            $host_id    = (int)$params['host_id'];  //发起主播id
            $host_sid   = (int)$params['host_sid']; //发起主播sid
            $guest_id   = (int)$params['guest_id']; //目标主播id
            $guest_sid  = (int)$params['guest_sid'];//目的主播sid
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            $pk_state = 0;
            $pkid = 0;
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid )
                {
                    // 403400020(020)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////

                //A  判断主场是否可以开启延长连麦（条件1：客户端做了pk请求结算；条件2：由于双方客户端都断线，系统自己进行了结算）
                //B   首先取出主场主播之前的pkid号
                $get_h_pkid = $m->redis_get_singer_pkid(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //C   根据pkid 判断当前的pk状态，并获得当前pk信息（如果pkid = 0，取出的pk状态是没有pk）
                $get_pk_info = array();//备注本次取出的pk_info 没有什么用，只用于函数参数签名
                $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;//初始化
                $m->linkcallpk_pk_info_process_by_pkid(&$error,$get_h_pkid,&$pk_process,&$get_pk_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //情况一：如果这个pkid对应的pk没有在进行，有可能客户端掉线了，服务器登记的pk状态由于有主播退出导致结束pk。这个客户端再次来查询
                if ($pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK)
                {
                    // 403400026(026)PK结束
                    $error['code'] = 403400026;
                    $error['desc'] = 'PK结束';
                    break;
                }
                //情况二：如果这个pkid对应的pk还在进行，未结束
                if ($pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_PKING)
                {
                    // 403400023(023)连麦PK还有计时未用完，（备注：需要客户端处理忽略错误码，正常不会出现）
                    $error['code'] = 403400023;
                    $error['desc'] = '连麦PK还有计时未用完';
                    break;
                }
                //两种情况下启动pk：1，客户端做了pk请求结算    2，系统自己进行了结算
                //在创建新的PKid前，如果主播直接点击开启连麦pk，默认主播发送了结算按钮，应该把上次的pkid给结束了
                $new_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_ACCOUNT;
                $m->redis_set_pk_info_process(&$error,$get_h_pkid,$new_pk_process);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //重新创建一个新的pkid号
                $pkid = $m->redis_create_pkid(&$error);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //2   取出系统配置给的pk时间  $pkalltime（延长时间）
                $pkalltime = $m->redis_get_mysql_info(&$error,linkcall_pk_model::$LINKCALL_PK_LINK_ADDTIME);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //3   刷新双方主播正式pk 新的pkid号
                $m->redis_set_singer_pkid(&$error,$pkid,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->redis_set_singer_pkid(&$error,$pkid,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //4  刷新新id情况下，双方主播的送礼金币,即新pkid的双方金币用旧pkid金币更新
            
            
                //5   登记pk信息,修改pk状态变为正在pk(包括结算后再次pk，都是新产生一个pkid，全新的环境来pk,因此双方礼物金币都是0)
                $time_now  = time(); //修正系统时间误差
                $starttime = $time_now;
                $pkalltime_3s = $pkalltime * 60 + 0;//取出的时间是分钟单位
                $pk_info["starttime"] = $starttime;
                $pk_info["pkalltime"] = $pkalltime_3s;
                $pk_info["host_id"] = $host_id;
                $pk_info["host_sid"] = $host_sid;
                $pk_info["guest_id"] = $guest_id;
                $pk_info["guest_sid"] = $guest_sid;
                $pk_info["host_gift"] = $get_pk_info["host_gift"];
                $pk_info["guest_gift"] = $get_pk_info["guest_gift"];
                $m->redis_set_pk_info_use_array(&$error,$pkid,&$pk_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_PKING;
                $m->redis_set_pk_info_process(&$error,$pkid,$pk_process);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //6   推送pk 开启  给 客场主播$guest_id
                $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_ADDTIME;
                $m->linkcallpk_singer_nt_singer_pk_info(&$error,$guest_id,$guest_sid,$host_id,$pk_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //7   在两个房间进行广播  pk信息 (当前只是推送pk信息，没有送礼信息，因此送礼用户和接收用户都为0，送礼消失为空)
                $user_id = 0 ;//送礼用户是空
                $singer_id = 0;//收礼用户是空
                $m->linkcallpk_room_nt_pk_info(&$error,$pkid,$user_id,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //8  强制双方主播都会到pk界面
                $pk_scene = linkcall_pk_model::$LINKCALL_PK_SCENE_PK ;
                $m->redis_set_singer_scene(&$error,$pk_scene,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->redis_set_singer_scene(&$error,$pk_scene,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //9  推送场景界面广播给两个房间
                $m->linkcallpk_room_pk_scene_nt(&$error,$pkid,$host_id,$host_sid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->linkcallpk_room_pk_scene_nt(&$error,$pkid,$guest_id,$guest_sid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_addtime_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_addtime_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_addtime_rs';
            $rs['error'] = $error;
            $rs['host_id'] = (int)$host_id;
            $rs['time_now'] = $time_now;
            $rs['pk_state'] = (int)$pk_state;
            $rs['pkid'] = (int)$pkid;
            $rs['pk'] = $pk_info;
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_addtime_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid pkid:$pkid rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_addtime_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid pkid:$pkid return:".json_encode($return));
            return $return;
        }

    }
    
    // 5.3 主播结束连麦pk
    public static function on_linkcallpk_close_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_close_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            $pk_info      = array();
            //on_linkcallpk_close_rq 包数据，拆解rq包
            $host_id    = (int)$params['host_id'];
            $host_sid   = (int)$params['host_sid'];
            $guest_id   = (int)$params['guest_id'];
            $guest_sid  = (int)$params['guest_sid'];
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            $pk_state = 0;
            $pkid = 0;
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid )
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            
                //1  查看之前是否做过结算（是结算不是结束关闭）
                //1.1 查询该主场主播的   服务器正在pk列表，如果pk列表反馈的pkid = 0，说明当前pk已经结算。
                $get_pkid = $m->redis_get_singer_pkid(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($get_pkid == 0)
                {
                    // 403400026(026)PK结束，可以直接返回客户端接收关闭完成
                    $error['code'] = 0;
                    $error['desc'] = '';
                    break;
                }
                $pkid = $get_pkid;
            
                //1.2 取出pk信息。
                $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;//初始化
                $get_pk_info = array();
                $m->linkcallpk_pk_info_process_by_pkid(&$error,$pkid,&$get_pk_process,&$get_pk_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $pk_info = $get_pk_info;
                //2  观察pk状态做处理
                //2.1 已经结束关闭
                if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK)
                {
                    // 403400026(026)PK结束，可以直接返回客户端接收关闭完成
                    $error['code'] = 0;
                    $error['desc'] = '';
                    break;
                }
                //2.2 刚刚创建pk界面，只需要推送双方主播和广播房间
                if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_READY)
                {
                    //不做任何处理
                }
                //2.3  查询到还在pk（需要提前进行结算，再推送双方主播和广播房间）
                if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_PKING)
                {
                    //a 取出主客场送礼总金额
                    {
                        //取出主场主播最新的礼物总金额
                        $get_host_gift = $m->redis_get_pking_info_singer_gift(&$error,$host_id);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        //取出客场主播最新的礼物总金额
                        $get_guest_gift = $m->redis_get_pking_info_singer_gift(&$error,$guest_id);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                    }
                    //b 刷新最新的 $pk_info 信息
                    $pk_info["host_gift"] = $get_host_gift;
                    $pk_info["guest_gift"] = $get_guest_gift;
                    //c 登记结算$pk_info 到缓存
                    $m->redis_set_pk_info_use_array(&$error,$pkid,&$pk_info);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                }
            
                //2.4  查询到的是系统已经自动进行结算（说明完成了正常的pk）,只需要推送双方主播和广播房间
                if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_BEYOND)
                {
                    //不做任何处理
            
                }
            
                //3  对双方主播做推送和广播
                $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_COUNT;
                $m->linkcallpk_singer_nt_singer_pk_info(&$error,$guest_id,$guest_sid,$host_id,$pk_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->linkcallpk_singer_nt_singer_pk_info(&$error,$host_id,$host_sid,$guest_id,$pk_state);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //4   在两个房间进行广播  pk 信息 (当前只是推送pk结算信息，没有送礼信息，因此送礼用户和接收用户都为0，送礼消失为空)
                $user_id = 0 ;//送礼用户是空
                $singer_id = 0;//收礼用户是空
                $m->linkcallpk_room_nt_pk_info(&$error,$pkid,$user_id,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //5   登记这个pkid已经结束
                $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;
                $m->redis_set_pk_info_process(&$error,$pkid,$get_pk_process);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //6   移除服务器主播对应主播送礼金额数据（双方主播pk结束，双方送礼金币归零）
                $m->redis_rem_pking_info_singer_gift(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->redis_rem_pking_info_singer_gift(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //7   移除服务器双方正在pk的双方pkid
                $m->redis_rem_singer_pkid(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->redis_rem_singer_pkid(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //8   移除服务器双方主播送礼用户列表
                $m->redis_del_user_gift_list(&$error,$host_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $m->redis_del_user_gift_list(&$error,$guest_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_close_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_close_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_close_rs';
            $rs['error'] = $error;
            $rs['host_id'] = (int)$host_id;
            $rs['time_now'] = $time_now;
            $rs['pk_state'] = (int)$pk_state;
            $rs['pkid'] = (int)$pkid;
            $rs['pk'] = $pk_info;
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_close_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid pkid:$pkid rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_close_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid pkid:$pkid return:".json_encode($return));
            return $return;
        }

    }
    
    // 6、用户查询连麦pk主播信息
    public static function on_linkcallpk_user_seek_pk_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_seek_pk_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            $pk_info      = array();
            //on_linkcallpk_user_seek_pk_rq 包数据，拆解rq包
            $singer_id   = (int)$params['singer_id'];
            $singer_sid  = (int)$params['singer_sid'];
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            $pkid = 0;
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $singer_id || 0 == $singer_sid )
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            
                //1    取出该主播是否在pk
                $pkid = $m->redis_get_singer_pkid(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($pkid == 0)
                {
                    //该主播不在pk，pk信息为空
                    break;
                }
                //2    取出该pkid对应的pk信息
                $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;//初始化
                $m->linkcallpk_pk_info_process_by_pkid(&$error,$pkid,&$pk_process,&$pk_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_seek_pk_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_user_seek_pk_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_user_seek_pk_rs';
            $rs['error'] = $error;
            $rs['singer_id'] = (int)$singer_id;
            $rs['time_now'] = $time_now;
            $rs['pkid'] = (int)$pkid;
            $rs['pk'] = $pk_info;
            
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_seek_pk_rs singer_id：$singer_id singer_sid:".$singer_sid." rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_seek_pk_rs singer_id：$singer_id singer_sid:".$singer_sid." return:".json_encode($return));
            return $return;
        }

    }
    
    
    //7、 用户查询连麦pk用户送礼信息(只用于推送最前面的5个列表，用于客户展示最前面的5个人头)
    public static function on_linkcallpk_user_seek_gift_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_seek_gift_rq rq:".json_encode($params));
            $return               = array();
            $error                = array();
            $users                = array();
            //on_linkcallpk_user_seek_gift_rq 包数据，拆解rq包
            $singer_id   = (int)$params['singer_id'];
            $singer_sid  = (int)$params['singer_sid'];
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $singer_id || 0 == $singer_sid )
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            
                //1    取出该主播是否在pk
                $pkid = $m->redis_get_singer_pkid(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($pkid == 0)
                {
                    //该主播不在pk，送礼列表为空
                    // 403400026(026)PK结束
                    $error['code'] = 403400026;
                    $error['desc'] = 'PK结束';
                    break;
                }
                //2    取出该用户查询主播的送礼用户列表
                $user_gift_list = array();
                $m->redis_get_user_gift_5list(&$error,$singer_id,&$user_gift_list);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if (true == empty($user_gift_list))
                {
                    //如果取出数据为空，$user_gift_list返回是空值
                    $error['code'] = 0;
                    $error['desc'] = '';
                    break;
                }
                foreach ($user_gift_list as $user_id_gift)
                {
                    $user_info = array();
                    $user_info["user_gift"] = $user_id_gift["user_gift"];
                    $user_info["user_id"] = $user_id_gift["user_id"];
                    //根据user_di 去取出对应的用户信息
                    $user_id = $user_info["user_id"];
                    $m->linkcallpk_user_info_by_userid(&$error,$singer_id,$user_id,&$user_info);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $users[] = $user_info;
                }
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_seek_gift_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_user_seek_gift_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_user_seek_gift_rs';
            $rs['error'] = $error;
            $rs['singer_id'] = (int)$singer_id;
            $rs['time_now'] = $time_now;
            $rs['pkid'] = $pkid;
            $rs['users'] = $users;
            
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_seek_gift_rs singer_id：$singer_id singer_sid:".$singer_sid." rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_seek_gift_rs singer_id：$singer_id singer_sid:".$singer_sid." return:".json_encode($return));
            return $return;
        }

    }  

    
    // 8、主播查询客场其他主播给自己的连麦pk列表申请的请求列表
    public static function on_linkcallpk_singer_seek_link_list_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_seek_link_list_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            $singers      = array();
            //on_linkcallpk_singer_seek_link_list_rq 包数据，拆解rq包
            $singer_id   = (int)$params['singer_id'];
            $singer_sid  = (int)$params['singer_sid'];
            $page_num    = (int)$params['page_num'];
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $singer_id || 0 == $singer_sid )
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            
                //1 取出分页为pag_num 的主播信息发给客户端（如果已经点击连线的主播，并且这个主播未回复的，需要保留已连线）
                $m->linkcallpk_link_singer_datas_by_pag_num(&$error,$singer_id,$page_num,&$singers);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_seek_link_list_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_user_seek_pk_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_singer_seek_link_list_rs';
            $rs['error'] = $error;
            $rs['singer_id'] = (int)$singer_id;
            $rs['time_now'] = $time_now;
            $rs['singers'] = $singers;
            
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_seek_link_list_rs page_num:$page_num singer_id：$singer_id singer_sid:".$singer_sid." rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_seek_link_list_rs page_num:$page_num inger_id：$singer_id singer_sid:".$singer_sid." return:".json_encode($return));
            return $return;
        }

    }
    
    // 9、用户进入房间 
    public static function on_linkcallpk_user_comein_room_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_comein_room_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            $pk_info      = array();
            $h_users      = array();
            $g_users      = array();
            $h_singer_cache = array ();
            $g_singer_cache = array ();
            //on_linkcallpk_user_comein_room_rq 包数据，拆解rq包
            $singer_id   = (int)$params['singer_id'];
            $singer_sid  = (int)$params['singer_sid'];
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            $pkid = 0;
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $singer_id || 0 == $singer_sid )
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            
                //1    取出该主播是否在pk
                $pkid = $m->redis_get_singer_pkid(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($pkid == 0)
                {
                    //该主播不在pk，pk信息为空
                    break;
                }
                //2    取出该pkid对应的pk信息
                $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;//初始化
                $m->linkcallpk_pk_info_process_by_pkid(&$error,$pkid,&$pk_process,&$pk_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $host_id = $pk_info["host_id"];
                $guest_id = $pk_info["guest_id"];
                //如果取出的信息是双方主播刚刚创建pk , 不需要继续下步查找，直接返回两个空的送礼列表
                if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_READY)
                {
                    break;
                }
                 
                //2    取出主场客场主播的送礼用户列表
                $h_user_gift_list = array();
                $m->redis_get_user_gift_5list(&$error,$host_id,&$h_user_gift_list);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_comein_room_rq host_id:$host_id $h_user_gift_list:".json_encode($h_user_gift_list));
                if (true != empty($h_user_gift_list))
                {
                    foreach ($h_user_gift_list as $h_user_id_gift)
                    {
                        $h_user_info = array();
                        //根据user_di 去取出对应的用户信息
                        $h_user_id = $h_user_id_gift["user_id"];
                        $m->linkcallpk_user_info_by_userid_all(&$error,$host_id,$h_user_id,&$h_user_info);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        $h_users[] = $h_user_info;
                    }
            
                }
                 
                $g_user_gift_list = array();
                $m->redis_get_user_gift_5list(&$error,$guest_id,&$g_user_gift_list);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_comein_room_rq guest_id:$guest_id $g_user_gift_list:".json_encode($g_user_gift_list));
                if (true != empty($g_user_gift_list))
                {
                    foreach ($g_user_gift_list as $g_user_id_gift)
                    {
                        $g_user_info = array();
                        //根据user_di 去取出对应的用户信息
                        $g_user_id = $g_user_id_gift["user_id"];
                        $m->linkcallpk_user_info_by_userid_all(&$error,$guest_id,$g_user_id,&$g_user_info);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        $g_users[] = $g_user_info;
                    }
                }
                 
                //3    取出主场客场主播资料
            
                {
                    //3.1 用$host_id 去获取主播基础信息
                    $m->redis_get_singer_info(&$error,$host_id,&$h_singer_cache);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                     
                    //3.2 用$guest_id 去获取主播基础信息
                    $m->redis_get_singer_info(&$error,$guest_id,&$g_singer_cache);
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
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_comein_room_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_user_comein_room_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_user_comein_room_rs';
            $rs['error'] = $error;
            $rs['singer_id'] = (int)$singer_id;
            $rs['time_now'] = $time_now;
            $rs['pkid'] = (int)$pkid;
            $rs['pk'] = $pk_info;
            $rs['h_singer'] = $h_singer_cache;
            $rs['g_singer'] = $g_singer_cache;
            $rs['h_users'] = $h_users;
            $rs['g_users'] = $g_users;
            
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_comein_room_rs singer_id：$singer_id singer_sid:".$singer_sid." rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_comein_room_rs singer_id：$singer_id singer_sid:".$singer_sid." return:".json_encode($return));
            return $return;
        }

    }
    
    
    // 10、用户离开房间
    // 不影响该功能，忽略
    
    // 11 主播进入房间
    // 主播是断线重连才需要，需要客户端补发接口操作，如果是关播，被迫踢下线，重新开播，应该先开启连麦pk功能，有客户端开启
    public static function on_linkcallpk_singer_comein_room_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_comein_room_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            $pk_info      = array();
            //on_linkcallpk_singer_comein_room_rq 包数据，拆解rq包
            $singer_id   = (int)$params['singer_id'];
            $singer_sid  = (int)$params['singer_sid'];
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            $pkid = 0;
            $functiontime = 0;
            $popup_time = 0;
            $popup_id = 0;
            $popup_live = 0;
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $singer_id || 0 == $singer_sid )
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            
                //0    取出该主播是否还开启着连麦pk功能
                $functiontime = $m->redis_get_online_singer_list_opentime(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($functiontime == 0)
                {
                    //该主播已经关闭pk功能了，直接退出返回
                    break;
                }
                //1    查看主播是否有收到其他主播给的弹窗信息
                $popup_time = $m->redis_get_pk_popup(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                if ($popup_time != 0)
                {
                    //有弹窗信息
                    //1.1    如果有弹窗信息，需要取出是谁给的弹窗信息
                    $popup_id = $m->redis_get_guest_popup_from_host(&$error,$singer_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    //1.2    如果有弹窗信息，判断发给这个弹窗信息的主播是否还在进行有pk（如果他下线了，就去掉这个弹窗所有信息，同理，这个主播也没有pkid号了）
                    $pk_opentime_popupsinger = $m->redis_get_online_singer_list_opentime(&$error,$popup_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    if ($pk_opentime_popupsinger == 0)
                    {
                        //发弹窗的主播都下线了，删掉弹窗信息，直接退出返回
                        $m->redis_rem_pk_popup(&$error,$singer_id);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        $m->redis_rem_guest_popup_from_host(&$error,$singer_id);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        // 令$popup_time  和    $popup_id =0 直接返回退出
                        $popup_time = 0;
                        $popup_id = 0;
                        break;
                    }
            
                    //1.3    如果有弹窗信息，需要给客户端系统给定的弹窗生命周期
                    $id_sys_poptime =linkcall_pk_model::$LINKCALL_PK_LINK_POPUPTIME;
                    $popup_live = $m->redis_get_mysql_info(&$error,$id_sys_poptime);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    //一个主播只能如果有弹窗了，就不可能有pkid号。
                    break;
                }
            
                //2    取出该主播是否在pk
                $pkid = $m->redis_get_singer_pkid(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($pkid != 0)
                {
                    //该主播在PK中，取出pk信息
                    $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;//初始化
                    $m->linkcallpk_pk_info_process_by_pkid(&$error,$pkid,&$pk_process,&$pk_info);
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
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_comein_room_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_singer_comein_room_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_singer_comein_room_rs';
            $rs['error'] = $error;
            $rs['time_now'] = $time_now;
            $rs['functiontime'] = $functiontime;
            $rs['singer_id'] = (int)$singer_id;
            $rs['popup_time'] = (int)$popup_time;
            $rs['popup_id'] = (int)$popup_id;
            $rs['popup_live'] = (int)$popup_live;
            $rs['pkid'] = (int)$pkid;
            $rs['pk'] = $pk_info;
            
            
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_comein_room_rs singer_id：$singer_id singer_sid:".$singer_sid." rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_comein_room_rs singer_id：$singer_id singer_sid:".$singer_sid." return:".json_encode($return));
            return $return;
        }

    }
    
    
    
    // 12 主播离开房间（主播关闭直播间）
    public static function on_linkcallpk_singer_leave_event($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_leave_event nt:".json_encode($params));
            $error = array();
            $error['code'] = 0;
            $error['desc'] = '';
            
            $sid         = (empty($params['singer_sid']))?$params['sid']:$params['singer_sid'];
            $singer_id   = $params['singer_id'];
            do
            {
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            
                //0    取出该主播是否还开启着连麦pk功能
                $pk_opentime = $m->redis_get_online_singer_list_opentime(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if ($pk_opentime == 0)
                {
                    //该主播已经关闭pk功能了，直接退出返回
                    break;
                }
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_leave_event  pk_opentime:$pk_opentime");
                //1    查看主播是否有收到其他主播给的弹窗信息
                $popup_time = $m->redis_get_pk_popup(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_leave_event  popup_time:$popup_time");
                if ($popup_time != 0)
                {
                    //有弹窗信息，需要删掉弹窗信息
                    $m->redis_rem_pk_popup(&$error,$singer_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $m->redis_rem_guest_popup_from_host(&$error,$singer_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
            
                }
            
                //2.1 取出pkid,查看该主播是否在连麦pk当中，取出pkid，根据pkid做细致判断
                $get_pkid = $m->redis_get_singer_pkid(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_leave_event  get_pkid:$get_pkid");
                if ($get_pkid != 0)
                {
            
                    $pkid = $get_pkid;
            
                    //2.2 取出pk信息。
                    $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;//初始化
                    $get_pk_info = array();
                    $m->linkcallpk_pk_info_process_by_pkid(&$error,$pkid,&$get_pk_process,&$get_pk_info);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $pk_info = $get_pk_info;
                    $host_id = $pk_info["host_id"];
                    $guest_id = $pk_info["guest_id"];
                    $host_sid = $pk_info["host_sid"];
                    $guest_sid = $pk_info["guest_sid"];
                    //  观察pk状态做处理
                    //2.3 已经结束关闭
                    if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK)
                    {
                        // 403400026(026)PK结束，可以直接返回客户端接收关闭完成
                        $error['code'] = 0;
                        $error['desc'] = '';
                        break;
                    }
                    //2.4 刚刚创建pk界面，只需要推送双方主播和广播房间
                    if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_READY)
                    {
                        //不做任何处理
                    }
                    //2.5  查询到还在pk（需要提前进行结算，再推送双方主播和广播房间）
                    if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_PKING)
                    {
                        //a 取出主客场送礼总金额
                        {
                            //取出主场主播最新的礼物总金额
                            $get_host_gift = $m->redis_get_pking_info_singer_gift(&$error,$host_id);
                            if (0 != $error['code'])
                            {
                                //出现了一些逻辑错误
                                break;
                            }
                            //取出客场主播最新的礼物总金额
                            $get_guest_gift = $m->redis_get_pking_info_singer_gift(&$error,$guest_id);
                            if (0 != $error['code'])
                            {
                                //出现了一些逻辑错误
                                break;
                            }
                        }
                        //b 刷新最新的 $pk_info 信息
                        $pk_info["host_gift"] = $get_host_gift;
                        $pk_info["guest_gift"] = $get_guest_gift;
                        //c 登记结算$pk_info 到缓存
                        $m->redis_set_pk_info_use_array(&$error,$pkid,&$pk_info);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                    }
            
                    //2.6  查询到的是系统已经自动进行结算（说明完成了正常的pk）,只需要推送双方主播和广播房间
                    if ($get_pk_process == linkcall_pk_model::$LINKCALL_PK_PKINFO_BEYOND)
                    {
                        //不做任何处理
            
                    }
            
                    //2.7  对双方主播做推送和广播
                    $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_COUNT;
                    $m->linkcallpk_singer_nt_singer_pk_info(&$error,$guest_id,$guest_sid,$host_id,$pk_state);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $m->linkcallpk_singer_nt_singer_pk_info(&$error,$host_id,$host_sid,$guest_id,$pk_state);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
            
                    //2.8   在两个房间进行广播  pk 信息 (当前只是推送pk结算信息，没有送礼信息，因此送礼用户和接收用户都为0，送礼消失为空)
            
                    $m->linkcallpk_room_nt_pk_info(&$error,$pkid,0,0);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
            
                    //2.9   登记这个pkid已经结束
                    $get_pk_process = linkcall_pk_model::$LINKCALL_PK_PKINFO_NOPK;
                    $m->redis_set_pk_info_process(&$error,$pkid,$pk_process);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    //2.10   移除服务器双方正在pk的列表
                    $m->redis_rem_singer_pkid(&$error,$host_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $m->redis_rem_singer_pkid(&$error,$guest_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    //2.11   移除服务器主播对应主播送礼金额数据（双方主播pk结束，双方送礼金币归零）
                    $m->redis_rem_pking_info_singer_gift(&$error,$host_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
                    $m->redis_rem_pking_info_singer_gift(&$error,$guest_id);
                    if (0 != $error['code'])
                    {
                        //出现了一些逻辑错误
                        break;
                    }
            
                }
            
                //3 找到该主播申请的申请列表，对申请过的主播一一进行推送，主播下线了
                $objsinger_list = array();
                $m->redis_get_singer_guest_apply_list(&$error,$singer_id,&$objsinger_list);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                if(true == empty($objsinger_list))
                {
                    // 403400012(012)读取数据为空,不需要进行任何nt操作
            
                }
                else
                {
                    foreach ($objsinger_list as $nt_singer_id)
                    {
                        //1 根据主播id，查找主播sid
                        $nt_singer_cache = array();
                        $m->redis_get_singer_info(&$error,$nt_singer_id,&$nt_singer_cache);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
                        $nt_singer_sid = $nt_singer_cache["singer_sid"];
            
                        //给所有申请了的主播推送主播下线了
                        $pk_state = linkcall_pk_model::$LINKCALL_PK_SINGER_OFFLINE;
                        $m->linkcallpk_singer_nt_singer_pk_info(&$error,$nt_singer_id,$nt_singer_sid,$singer_id,$pk_state);
                        if (0 != $error['code'])
                        {
                            //出现了一些逻辑错误
                            break;
                        }
            
                    }
                }
            
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_leave_event  singer_id:$singer_id");
                //4 删除该主播申请列表
                $m->redis_del_singer_guest_apply_list(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //5 删除该主播请求列表
                $m->redis_del_singer_host_link_list(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //6 该主播在在线连麦pk列表中移除
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_leave_event rem singer_id:$singer_id ");
                $m->redis_rem_online_singer_list(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_singer_leave_event error:".json_encode($error));
            }
        }

  
    }
    
    // 13、用户送礼事件
    public static function on_linkcallpk_user_send_gift_event($pkid,$user_id,$giftall,$singer_id)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_send_gift_event pkid:$pkid user_id:$user_id giftall:$giftall singer_id:$singer_id");
            
            do
            {
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            
            
                //1 刷新该用户的缓存信息
                //根据用户id，搜集用户缓存信息
                $user_cache = array();
                $userInfo = new UserInfoModel();
                $get_user_info = $userInfo->getInfoById($user_id);
                $userlevel = new UserAttributeModel();
                $get_user_level = $userlevel->getAttrByUid($user_id);
                $user_cache["user_id"] = $user_id;
                $user_cache["user_level"] = $get_user_level["active_level"];
                $user_cache["user_icon"]  = $get_user_info["photo"] ;
                $user_cache["user_nick"]  = $get_user_info["nick"] ;
                $user_cache["user_wealth"]  = $get_user_level["consume_level"];
                $m->redis_set_user_info(&$error,$user_id,&$user_cache);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                
                //2 根据pkid取出pk信息
                $pk_info = array();
                $m->redis_get_pk_info(&$error,$pkid,&$pk_info);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                $time_now  = time();
                $starttime = $pk_info["starttime"];
                $pkalltime = $pk_info["pkalltime"];
                //如果有3s 倒计时，需要去掉前面3s，不计入礼物计算                
                if ($pkalltime %10 == 3)
                {
                    if ($time_now - $starttime < 3)
                    {
                        //如果有3s 倒计时，需要去掉前面3s，不计入礼物计算
                        break;
                    }
                }
            
                //2 刷新主播送礼用户列表数据
                $m->redis_set_user_gift(&$error,$singer_id,$user_id,$giftall);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //3 刷新该主播礼物总金额
                $m->redis_set_pking_info_singer_gift(&$error,$singer_id,$giftall);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //4 推送两个房间送礼信息
                $m->linkcallpk_room_nt_pk_info(&$error,$pkid,$user_id,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_user_send_gift_event error:".json_encode($error));
            }
            
        }

    }
    
    
    // 14 主播切换直播场景
    public static function on_linkcallpk_pking_switch_scene_rq($params)
    {
        $m = new linkcall_pk_model();
        if (linkcall_pk_model::$LINKCALL_PK_SET_CONTROL)
        {
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_pking_switch_scene_rq rq:".json_encode($params));
            $return       = array();
            $error        = array();
            
            //on_linkcallpk_pking_switch_scene_rq 包数据，拆解rq包
            $singer_id   = (int)$params['singer_id'];
            $singer_sid  = (int)$params['singer_sid'];
            
            //b_error.info  rs回包错误信息default
            $error['code'] = 0;
            $error['desc'] = '';
            $time_now = time();
            $pk_scene = linkcall_pk_model::$LINKCALL_PK_SCENE_PK ;
            $pkid = 0;
            do
            {
                //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
                if (0 == $singer_id || 0 == $singer_sid )
                {
                    // 403400021(021)无效的参数
                    $error['code'] = 403400021;
                    $error['desc'] = '无效的请求参数';
                    break;
                }
                //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            
                //1    取出主播场景状态
                $pk_scene = $m->redis_get_singer_scene(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
                //2    增加新的轮回状态
                $pk_scene_next = (int)(($pk_scene + 1 ) %3 );
            
                //3    保存主播新的场景状态
                $m->redis_set_singer_scene(&$error,$pk_scene_next,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //2    取出该主播的pkid号
                $pkid = $m->redis_get_singer_pkid(&$error,$singer_id);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
                //3    广播给这个主播的房间
                $m->linkcallpk_room_pk_scene_nt(&$error,$pkid,$singer_id,$singer_sid);
                if (0 != $error['code'])
                {
                    //出现了一些逻辑错误
                    break;
                }
            
            
                //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
            }while(FALSE);
            if (0 !=$error['code'])
            {
                LogApi::logProcess("linkcall_pk_api.on_linkcallpk_pking_switch_scene_rq error:".json_encode($error));
            }
            if (403400001 < $error['code'] && $error['code'] < 403400020)
            {
                //屏蔽服务器细节问题，统一发给客户端一个错误码
                $error['code'] = 403400020;
                $error['desc'] = '服务器出现一丢丢问题';
            }
            
            //on_linkcallpk_user_seek_pk_rq 包回包，default
            $rs = array();
            $rs['cmd'] = 'linkcallpk_user_seek_pk_rs';
            $rs['error'] = $error;
            $rs['singer_id'] = (int)$singer_id;
            $rs['time_now'] = $time_now;
            $rs['pk_scene'] = (int)$pk_scene_next;
            $rs['pkid'] = (int)$pkid;
            
            
            $return[] = array
            (
                'broadcast' => 0,// 发rs包
                'data' => $rs,
            );
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_pking_switch_scene_rs singer_id：$singer_id singer_sid:".$singer_sid." rs:".json_encode($rs));
            LogApi::logProcess("linkcall_pk_api.on_linkcallpk_pking_switch_scene_rs singer_id：$singer_id singer_sid:".$singer_sid." return:".json_encode($return));
            return $return;
        }

    }
    
    
    
    
}