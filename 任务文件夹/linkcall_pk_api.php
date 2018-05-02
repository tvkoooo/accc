<?php
// 连麦api接口
class linkcall_pk_api
{   
    // 1.1 主播打开连麦pk功能
    public static function on_linkcallpk_siger_open_function_rq($params)
    {
        LogApi::logProcess("on_linkcallpk_siger_open_function_rq rq:".json_encode($params));
        $return       = array();
        $error        = array();
        $singers      = array();
        //on_linkcallpk_siger_open_function_rq 包数据，拆解rq包
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
                // 4033400021(021)无效的参数
                $error['code'] = 403400021;
                $error['desc'] = '无效的请求参数';
                break;
            }
        //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_pk_model();
            //1   查询该主播是否满足连麦pk 条件
            //1.1 取出连麦pk功能最低星级要求
            $info_id = linkcall_pk_model::$LINKCALL_PK_SINGER_START;
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
            //1.3 如果该主播不满足，直接报错返回
            if ($singer_cache["singer_star"] < $info_value)
            {
                // 4033400022(022)主播未满足连麦PK要求
                $error['code'] = 4033400022;
                $error['desc'] = '主播未满足连麦PK要求';
                break;
            }
            
            //2   登记主播信息
            //2.1 取出主播临时缓存
            $singer_cache["singer_level"]  = (int)$get_singer_info["active_level"];
            $user_redis = new UserInfoModel();
            $get_user_info = $user_redis->getInfoById($singer_id);
            $singer_cache["singer_icon"]  = (int)$get_user_info["photo"];
            $singer_cache["singer_id"] = $singer_id ;
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
            
        //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
            
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcallpk_siger_open_function_rq error:".json_encode($error));
        }
        
        //on_linkcallpk_siger_open_function_rq 包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcallpk_siger_open_function_rs';
        $rs['error'] = $error;
        $rs['guest_id'] = (int)$singer_id;
        $rs['time_now'] = $time_now;

        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcallpk_siger_open_function_rs guest_id：$singer_id sid:".$singer_sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcallpk_siger_open_function_rs guest_id：$singer_id sid:".$singer_sid." return:".json_encode($return));
        return $return;

    }
    
    // 1.2 主播查询当前在线满足条件主播申请列表
    public static function on_linkcallpk_siger_seek_online_list_rq($params)
    {
        LogApi::logProcess("on_linkcallpk_siger_seek_online_list_rq rq:".json_encode($params));
        $return       = array();
        $error        = array();
        $singers      = array();
        //on_linkcallpk_siger_seek_online_list_rq 包数据，拆解rq包
        $singer_id   = (int)$params['singer_id'];
        $singer_sid  = (int)$params['singer_sid'];
        $pag_num    = (int)$params['pag_num'];
    
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
        $time_now = time();
        do
        {
            //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
            if (0 == $singer_id || 0 == $singer_sid || $pag_num < 0)
            {
                // 4033400021(021)无效的参数
                $error['code'] = 403400021;
                $error['desc'] = '无效的请求参数';
                break;
            }
            //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_pk_model();
            //1 取出分页为pag_num 的主播信息发给客户端
            $m->linkcallpk_singer_datas_by_pag_num(&$error,$singer_id,$pag_num,&$singers);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
    
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcallpk_siger_seek_online_list_rq error:".json_encode($error));
        }
    
        //on_linkcallpk_siger_seek_online_list_rq 包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcallpk_siger_seek_online_list_rs';
        $rs['error'] = $error;
        $rs['guest_id'] = (int)$singer_id;
        $rs['time_now'] = $time_now;
        $rs['singers'] = $singers;
    
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcallpk_siger_seek_online_list_rs guest_id：$singer_id sid:".$singer_sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcallpk_siger_seek_online_list_rs guest_id：$singer_id sid:".$singer_sid." return:".json_encode($return));
        return $return;
    
    }
    
    // 2 主播客场申请PK
    public static function on_linkcallpk_apply_rq($params)
    {
        LogApi::logProcess("on_linkcallpk_apply_rq rq:".json_encode($params));
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
                // 4033400021(021)无效的参数
                $error['code'] = 403400021;
                $error['desc'] = '无效的请求参数';
                break;
            }
        //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_pk_model();
            //1   设置   发起主播$guest_id   对   目标主播$host_id  的状态
            //1.1 增加$guest_id  主播变更状态       正在申请
            $add_self_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RQ_APPLYING;
            //1.2 取出$guest_id  主播旧状态
            $get_self_singer_state = $m->redis_get_singer_state(&$error,$guest_id,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //1.3 合成$guest_id  主播新状态         修改千位
            $self_singer_state = $add_self_singer_state + $get_self_singer_state%1000;
            //1.4 设置$guest_id  主播新状态
            $m->redis_set_singer_state(&$error,$self_singer_state,$guest_id,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            //2   设置   目标主播$host_id   对   发起主播$guest_id  的状态
            //2.1 增加$host_id  主播变更状态       允许连线，等待连线
            $add_obj_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RQ_LINK;
            //2.2 取出$host_id  主播旧状态
            $get_obj_singer_state = $m->redis_get_singer_state(&$error,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //2.3 合成$host_id  主播新状态         修改百位
            $obj_singer_state = $get_obj_singer_state/1000 *1000 +$add_obj_singer_state + $get_obj_singer_state%100;
            //2.4 设置$host_id  主播新状态
            $m->redis_set_singer_state(&$error,$obj_singer_state,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            } 
            
            //3 把发起主播的申请推送给目标主播 nt
            $m->linkcallpk_singer_nt_singer_PKinfo(&$error,$host_id,$host_sid,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            //4 给操作的主播主场列表增加一个请求
            $m->redis_set_singer_host_link_list(&$error,$host_id,$guest_id,$time_now);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
        //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
    
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcallpk_apply_rq error:".json_encode($error));
        }
    
        //on_linkcallpk_apply_rq 包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcallpk_apply_rs';
        $rs['error'] = $error;
        $rs['singer_state'] = (int)$self_singer_state;
        $rs['guest_id'] = (int)$guest_id;
        $rs['host_id'] = (int)$host_id;
        $rs['time_now'] = $time_now;
    
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid rs:".json_encode($rs));
        LogApi::logProcess("on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid return:".json_encode($return));
        return $return;    
    }
    
    // 3.1 主播主场连线pk
    public static function on_linkcallpk_link_rq($params)
    {
        LogApi::logProcess("on_linkcallpk_link_rq rq:".json_encode($params));
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
        do
        {
        //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
            if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid )
            {
                // 4033400021(021)无效的参数
                $error['code'] = 403400021;
                $error['desc'] = '无效的请求参数';
                break;
            }
        //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_pk_model();
            //1   设置   发起主播$host_id   对   目标主播$guest_id  的状态
            //1.1 增加$host_id  主播变更状态       正在连线
            $add_self_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RQ_LINKING;
            //1.2 取出$host_id  主播旧状态
            $get_self_singer_state = $m->redis_get_singer_state(&$error,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //1.3 合成$host_id  主播新状态         修改百位
            $self_singer_state = $get_self_singer_state/1000*1000 + $add_self_singer_state + $get_self_singer_state%100;
            //1.4 设置$host_id  主播新状态
            $m->redis_set_singer_state(&$error,$self_singer_state,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            //2   设置   目标主播$guest_id   对   发起主播$host_id  的状态
            //2.1 增加$guest_id  主播变更状态       允许确认，等待确认
            $add_obj_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RQ_DEF_LINK;
            //2.2 取出$guest_id  主播旧状态
            $get_obj_singer_state = $m->redis_get_singer_state(&$error,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //2.3 合成$guest_id  主播新状态         修改个位
            $obj_singer_state = $get_obj_singer_state/10*10 + $add_obj_singer_state;
            //2.4 设置$guest_id  主播新状态
            $m->redis_set_singer_state(&$error,$obj_singer_state,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            } 
            
            //3 把发起主播的同意连线推送给目标主播 nt
            $m->linkcallpk_singer_nt_singer_PKinfo(&$error,$host_id,$host_sid,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }            

        //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
    
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcallpk_link_rq error:".json_encode($error));
        }
    
        //on_linkcallpk_link_rq 包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcallpk_link_rs';
        $rs['error'] = $error;
        $rs['singer_state'] = (int)$self_singer_state;
        $rs['host_id'] = (int)$host_id;
        $rs['guest_id'] = (int)$guest_id;
        $rs['time_now'] = $time_now;
    
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcallpk_apply_rs host_id:$host_id host_sid:$host_sid guest_id:$guest_id guest_sid:$guest_sid rs:".json_encode($rs));
        LogApi::logProcess("on_linkcallpk_apply_rs host_id:$host_id host_sid:$host_sid guest_id:$guest_id guest_sid:$guest_sid return:".json_encode($return));
        return $return;
    
    }
    
    // 3.2 主播客场确认pk功能
    public static function on_linkcallpk_confirm_rq($params)
    {
        LogApi::logProcess("on_linkcallpk_confirm_rq rq:".json_encode($params));
        $return       = array();
        $error        = array();
    
        //on_linkcallpk_confirm_rq 包数据，拆解rq包
        $guest_id   = (int)$params['guest_id'];//发起主播id
        $guest_sid  = (int)$params['guest_sid'];//发起主播sid
        $host_id    = (int)$params['host_id'];//目标主播id
        $host_sid  = (int)$params['host_sid'];//目的主播sid
        $op_code   = (int)$params['op_code'];//主播同意或拒绝的操作码
    
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
        $time_now = time();
        do
        {
        //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
            if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid || !($op_code == 1 || $op_code == 2 ))
            {
                // 4033400021(021)无效的参数
                $error['code'] = 403400021;
                $error['desc'] = '无效的请求参数';
                break;
            }
        //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_pk_model();
            //1   设置   发起主播$guest_id   对   目标主播$host_id  的状态
            //1.1 增加$guest_id  主播变更状态       同意或者拒绝
            if ($op_code == 1) 
            {
                //设置$guest_id该主播的申请状态  申请状态改为   同意连线
                $add_self_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RS_YES_LINK;
            }
            else 
            {
                //设置$guest_id该主播的申请状态  申请状态改为  拒绝连线
                $add_self_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RS_NO_LINK;
            }
            //1.2 取出$guest_id  主播旧状态
            $get_self_singer_state = $m->redis_get_singer_state(&$error,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //1.3 合成$guest_id  主播新状态         修改十位
            $self_singer_state = $get_self_singer_state/100*100 + $add_self_singer_state + $get_self_singer_state%10;
            //1.4 设置$guest_id  主播新状态
            $m->redis_set_singer_state(&$error,$self_singer_state,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            //2   设置   目标主播$host_id   对   发起主播$guest_id  的状态
            //2.1 增加$host_id  主播变更状态       同意或者拒绝
            if ($op_code == 1)
            {
                //设置$host_id 该主播的申请状态  获得状态   同意连线
                $add_obj_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RQ_YES_LINK;
            }
            else
            {
                //设置$host_id 该主播的申请状态  获得状态  拒绝连线
                $add_obj_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RQ_NO_LINK;
            }
            //2.2 取出$host_id  主播旧状态
            $get_obj_singer_state = $m->redis_get_singer_state(&$error,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //2.3 合成$host_id  主播新状态         修改个位
            $obj_singer_state = $get_obj_singer_state/10*10 + $add_obj_singer_state;
            //2.4 设置$host_id  主播新状态
            $m->redis_set_singer_state(&$error,$obj_singer_state,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            //3 把发起主播的同意连线推送给目标主播 nt
            $m->linkcallpk_singer_nt_singer_PKinfo(&$error,$host_id,$host_sid,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
        //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
    
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcallpk_confirm_rq error:".json_encode($error));
        }
    
        //on_linkcallpk_confirm_rq 包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcallpk_confirm_rs';
        $rs['error'] = $error;
        $rs['singer_state'] = (int)$self_singer_state;
        $rs['host_id'] = (int)$host_id;
        $rs['guest_id'] = (int)$guest_id;
        $rs['time_now'] = $time_now;
        $rs['op_code'] = $op_code;
    
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid op_code:$op_code rs:".json_encode($rs));
        LogApi::logProcess("on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid op_code:$op_code return:".json_encode($return));
        return $return;    
    }
    
    // 4 主场主播启动连麦pk（包括结算后再次pk，都是新产生一个pkid，全新的环境来pk）
    public static function on_linkcallpk_start_rq($params)
    {
        LogApi::logProcess("on_linkcallpk_start_rq rq:".json_encode($params));
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
        do
        {
        //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
            if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid )
            {
                // 4033400020(020)无效的参数
                $error['code'] = 403400021;
                $error['desc'] = '无效的请求参数';
                break;
            }
        //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_pk_model();
            //1   创建pkid号
            $pkid = $m->redis_create_pkid(&$error);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //2   取出 $pkalltime
            $pkalltime = $m->redis_get_mysql_info(&$error,linkcall_pk_model::$LINKCALL_PK_LINK_PKTIME);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //3   登记双方主播正式PK
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
            //4.1  登记双方主播正式PK 的初始送礼金额（开始新的一局PK，礼物重新结算）   
            $host_gift  = 0;//主场主播金额是0
            $guest_gift = 0;//客场主播金额是0
            $m->redis_set_PKing_info_singer_gift(&$error,$host_id,$host_gift);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            $m->redis_set_PKing_info_singer_gift(&$error,$guest_id,$guest_gift);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //4.2  删除两个主播有可能的之前     用户送礼列表
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
            //5   登记PK信息(包括结算后再次pk，都是新产生一个pkid，全新的环境来pk,因此双方礼物金币都是0)
            $time_now  = time(); //修正系统时间误差
            $starttime = $time_now;
            $m->redis_set_PK_info(&$error,$pkid,$starttime,$pkalltime,$host_id,$guest_id,$host_gift,$guest_gift);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //6   推送PK 开启  给 客场主播$guest_id
            $m->linkcallpk_singer_nt_singer_PKinfo(&$error,$guest_id,$guest_sid,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //7   在两个房间进行广播  PK (当前只是推送PK信息，没有送礼信息，因此送礼用户和接收用户都为0，送礼消失为空) 
            $user_id = 0 ;//送礼用户是空
            $singer_id = 0;//收礼用户是空
            $m->linkcallpk_room_nt_PKinfo(&$error,$host_id,$host_sid,$guest_id,$guest_sid,$user_id,$singer_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //8   给rs回包取出pk信息            
            $m->linkcallpk_PK_info_by_PKsinger(&$error,$pkid,&$pk_info);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
        //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
    
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcallpk_start_rq error:".json_encode($error));
        }
    
        //on_linkcallpk_confirm_rq 包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcallpk_start_rs';
        $rs['error'] = $error;
        $rs['host_id'] = (int)$host_id;
        $rs['time_now'] = $time_now;
        $rs['pk'] = $pk_info;
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid op_code:$op_code rs:".json_encode($rs));
        LogApi::logProcess("on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid op_code:$op_code return:".json_encode($return));
        return $return;
    }
    
    
    // 5.1 主播结算pk（主场和客场主播都发结束请求，按照先到请求，并且满足pk结束时间来结算）
    public static function on_linkcallpk_count_rq($params)
    {
        LogApi::logProcess("on_linkcallpk_count_rq rq:".json_encode($params));
        $return       = array();
        $error        = array();
        $pk_info      = array();
        //on_linkcallpk_count_rq 包数据，拆解rq包
        $pkid       = (int)$params['pkid'];     //pk 的 id号  
        $host_id    = (int)$params['host_id'];  //发起主播id
        $host_sid   = (int)$params['host_sid']; //发起主播sid
        $guest_id   = (int)$params['guest_id']; //目标主播id
        $guest_sid  = (int)$params['guest_sid'];//目的主播sid
    
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
        $time_now = time();
        do
        {
        //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
            if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid ||$pkid == 0)
            {
                // 4033400021(021)无效的参数
                $error['code'] = 403400021;
                $error['desc'] = '无效的请求参数';
                break;
            }
        //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_pk_model();
            //0  查看之前是否有进行结算（主场客场都会发结算请求，只按照满足要求最先到的rq来结算，后来那个rq会返回已经结算错误，这个错误客户端需要忽略）
            //0.1 查询该主场主播的   服务器正在pk列表，如果pk列表反馈的pkid = 0，说明已经被结算完成了。
            $get_pkid = $m->redis_get_singer_pkid(&$error,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            if ($get_pkid == 0) {
                // 4033400022(022)连麦PK已经结算完成，忽略错误
                $error['code'] = 403400022;
                $error['desc'] = '连麦PK已经结算完成，忽略错误';
                break;
            }       
            //0.2 取出pk信息，查看系统时刻是否已经完成了pk。
            $pk_info  = array();
            $get_pkid = $m->linkcallpk_PK_info_by_PKsinger(&$error,$pkid,&$pk_info);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            if ($pk_info["starttime"] + $pk_info["pkalltime"] > $time_now ) {
                // 4033400024(024)连麦PK还有计时未用完
                $error['code'] = 4033400024;
                $error['desc'] = '连麦PK还有计时未用完';
                break;
            }
            
            //1   设置   双方主播$host_id  和     $guest_id  的状态   结束PK
            //1.1 增加$host_id  主播变更状态       结束PK
            $add_host_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RS_PKCOUNT;
            //1.2 取出$host_id  主播旧状态
            $get_host_singer_state = $m->redis_get_singer_state(&$error,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //1.3 合成$host_id  主播新状态         修改百位
            $host_singer_state = $get_host_singer_state/1000*1000 + $add_host_singer_state + $get_host_singer_state%100;
            //1.4 设置$host_id  主播新状态
            $m->redis_set_singer_state(&$error,$host_singer_state,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //1.5 增加$guest_id  主播变更状态       结束PK
            $add_guest_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RS_PKCOUNT;
            //1.6 取出$guest_id  主播旧状态
            $get_guest_singer_state = $m->redis_get_singer_state(&$error,$guest_id,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //1.7 合成$guest_id  主播新状态         修改百位
            $guest_singer_state = $get_guest_singer_state/1000*1000 + $add_guest_singer_state + $get_guest_singer_state%100;
            //1.8 设置$guest_id  主播新状态
            $m->redis_set_singer_state(&$error,$guest_singer_state,$guest_id,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            //2   推送nt给两个主播，服务器已经进行了结算
            $m->linkcallpk_singer_nt_singer_PKinfo(&$error,$guest_id,$guest_sid,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            $m->linkcallpk_singer_nt_singer_PKinfo(&$error,$host_id,$host_sid,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }            

            //3   在两个房间进行广播  PK (当前只是推送PK结算信息，没有送礼信息，因此送礼用户和接收用户都为0，送礼消失为空)
            $user_id = 0 ;//送礼用户是空
            $singer_id = 0;//收礼用户是空
            $m->linkcallpk_room_nt_PKinfo(&$error,$host_id,$host_sid,$guest_id,$guest_sid,$user_id,$singer_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //4   移除 双方主播  服务器pk信息
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
            //备注，结算的时候只是移除服务器正在PK的主播当中这两个主播，其他数据都必须保留，如果主播选择延长PK，这些信息还需要继续使用 
            
            //5   给rs回包取出pk信息
            $m->linkcallpk_PK_info_by_PKsinger(&$error,$pkid,&$pk_info);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
        //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
    
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcallpk_count_rq error:".json_encode($error));
        }
    
        //on_linkcallpk_count_rq 包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcallpk_count_rs';
        $rs['error'] = $error;
        $rs['time_now'] = $time_now;
        $rs['pk'] = $pk_info;
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid pkid:$pkid rs:".json_encode($rs));
        LogApi::logProcess("on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid pkid:$pkid return:".json_encode($return));
        return $return;
    } 
    
    // 5.2 主播延长连麦pk
    public static function on_linkcallpk_addtime_rq($params)
    {
        LogApi::logProcess("on_linkcallpk_addtime_rq rq:".json_encode($params));
        $return       = array();
        $error        = array();
        $pk_info      = array();
        //on_linkcallpk_addtime_rq 包数据，拆解rq包
        $old_pkid   = (int)$params['pkid'];     //旧的 pk 的 id号
        $host_id    = (int)$params['host_id'];  //发起主播id
        $host_sid   = (int)$params['host_sid']; //发起主播sid
        $guest_id   = (int)$params['guest_id']; //目标主播id
        $guest_sid  = (int)$params['guest_sid'];//目的主播sid
    
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
        $time_now = time();
        do
        {
        //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
            if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid ||$pkid == 0)
            {
                // 4033400020(020)无效的参数
                $error['code'] = 403400021;
                $error['desc'] = '无效的请求参数';
                break;
            }
        //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_pk_model();
            //1   创建pkid号
            $pkid = $m->redis_create_pkid(&$error);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //2   取出 延长时间
            $addtime = $m->redis_get_mysql_info(&$error,linkcall_pk_model::LINKCALL_PK_LINK_ADDTIME);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //3   登记双方主播正式PK 覆盖以前的old_pkid
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
            //4  取出主客场双方礼物值送礼金额（延长pk，因此需要加载之前的pk礼物值）
            $host_gift = $m->redis_get_PKing_info_singer_gift(&$error,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            $guest_gift = $m->redis_get_PKing_info_singer_gift(&$error,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }

            //5   登记PK信息(新产生一个pkid，用之前old_pkid的双方礼物值来填充pk信息)
            $time_now  = time(); //修正系统时间误差
            $starttime = $time_now;
            $m->redis_set_PK_info(&$error,$pkid,$starttime,$addtime,$host_id,$guest_id,$host_gift,$guest_gift);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //6   推送PK 开启  给 客场主播$guest_id
            $m->linkcallpk_singer_nt_singer_PKinfo(&$error,$guest_id,$guest_sid,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //7   在两个房间进行广播  PK (当前只是推送PK信息，没有送礼信息，因此送礼用户和接收用户都为0，送礼消失为空)
            $m->linkcallpk_room_nt_PKinfo(&$error,$host_id,$host_sid,$guest_id,$guest_sid,0,0);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //8   给rs回包取出pk信息
            $m->linkcallpk_PK_info_by_PKsinger(&$error,$pkid,&$pk_info);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
        //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
    
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcallpk_addtime_rq error:".json_encode($error));
        }
    
        //on_linkcallpk_addtime_rq 包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcallpk_addtime_rs';
        $rs['error'] = $error;
        $rs['time_now'] = $time_now;
        $rs['pk'] = $pk_info;
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcallpk_addtime_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid old_pkid:$old_pkid rs:".json_encode($rs));
        LogApi::logProcess("on_linkcallpk_addtime_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid old_pkid:$old_pkid return:".json_encode($return));
        return $return;
    }
    
    // 5.3 主播结束连麦pk
    public static function on_linkcallpk_close_rq($params)
    {
        LogApi::logProcess("on_linkcallpk_close_rq rq:".json_encode($params));
        $return       = array();
        $error        = array();
        $pk_info      = array();
        //on_linkcallpk_close_rq 包数据，拆解rq包
        $pkid       = (int)$params['pkid'];     //pk 的 id号
        $host_id    = (int)$params['host_id'];  //发起主播id
        $host_sid   = (int)$params['host_sid']; //发起主播sid
        $guest_id   = (int)$params['guest_id']; //目标主播id
        $guest_sid  = (int)$params['guest_sid'];//目的主播sid
    
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
        $time_now = time();
        do
        {
        //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
            if (0 == $guest_id ||0 == $guest_sid ||0 == $host_id ||0 == $host_sid ||$pkid == 0)
            {
                // 4033400021(021)无效的参数
                $error['code'] = 403400021;
                $error['desc'] = '无效的请求参数';
                break;
            }
        //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_pk_model();
            //0  查看之前是否有进行结算（主场客场都会发结算请求，只按照满足要求最先到的rq来结算，后来那个rq会返回已经结算错误，这个错误客户端需要忽略）
            //0.1 查询该主场主播的   服务器正在pk列表，如果pk列表反馈的pkid = 0，说明已经被结算完成了。
            $get_pkid = $m->redis_get_singer_pkid(&$error,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            if ($get_pkid == 0) {
                // 4033400023(023)连麦PK已经结算完成，忽略错误
                $error['code'] = 403400023;
                $error['desc'] = '连麦PK已经结算完成，忽略错误';
                break;
            }
    
            //1   设置   双方主播$host_id  和     $guest_id  的状态   结束PK
            //1.1 增加$host_id  主播变更状态       结束PK
            $add_host_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RS_PKOVER;
            //1.2 取出$host_id  主播旧状态
            $get_host_singer_state = $m->redis_get_singer_state(&$error,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //1.3 合成$host_id  主播新状态         修改百位
            $host_singer_state = $get_host_singer_state/1000*1000 + $add_host_singer_state + $get_host_singer_state%100;
            //1.4 设置$host_id  主播新状态
            $m->redis_set_singer_state(&$error,$host_singer_state,$host_id,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //1.5 增加$guest_id  主播变更状态       结束PK
            $add_guest_singer_state = linkcall_pk_model::$LINKCALL_PK_SINGER_RS_PKOVER;
            //1.6 取出$guest_id  主播旧状态
            $get_guest_singer_state = $m->redis_get_singer_state(&$error,$guest_id,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            //1.7 合成$guest_id  主播新状态         修改百位
            $guest_singer_state = $get_guest_singer_state/1000*1000 + $add_guest_singer_state + $get_guest_singer_state%100;
            //1.8 设置$guest_id  主播新状态
            $m->redis_set_singer_state(&$error,$guest_singer_state,$guest_id,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            //2   推送nt给两个主播，服务器已经进行了结算
            $m->linkcallpk_singer_nt_singer_PKinfo(&$error,$guest_id,$guest_sid,$host_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            $m->linkcallpk_singer_nt_singer_PKinfo(&$error,$host_id,$host_sid,$guest_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
            //3   在两个房间进行广播  PK (当前只是推送PK结算信息，没有送礼信息，因此送礼用户和接收用户都为0，送礼消失为空)
            $user_id = 0 ;//送礼用户是空
            $singer_id = 0;//收礼用户是空
            $m->linkcallpk_room_nt_PKinfo(&$error,$host_id,$host_sid,$guest_id,$guest_sid,$user_id,$singer_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            //4   给rs回包取出pk信息
            $m->linkcallpk_PK_info_by_PKsinger(&$error,$pkid,&$pk_info);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            
            //5   移除 双方主播  服务器pk信息
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
            //6  删除两个主播有可能的之前     用户送礼列表
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
            LogApi::logProcess("on_linkcallpk_close_rq error:".json_encode($error));
        }
    
        //on_linkcallpk_close_rq 包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcallpk_close_rs';
        $rs['error'] = $error;
        $rs['time_now'] = $time_now;
        $rs['pk'] = $pk_info;
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid pkid:$pkid rs:".json_encode($rs));
        LogApi::logProcess("on_linkcallpk_apply_rs guest_id:$guest_id guest_sid:$guest_sid host_id:$host_id host_sid:$host_sid pkid:$pkid return:".json_encode($return));
        return $return;
    }
    
    // 6、用户查询连麦pk主播信息
    public static function on_linkcallpk_user_seek_pk_rq($params)
    {
        LogApi::logProcess("on_linkcallpk_user_seek_pk_rq rq:".json_encode($params));
        $return       = array();
        $error        = array();
        $pk           = array();
        //on_linkcallpk_user_seek_pk_rq 包数据，拆解rq包
        $singer_id   = (int)$params['guest_id'];
        $singer_sid  = (int)$params['guest_sid'];
    
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
        $time_now = time();
        do
        {
        //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
            if (0 == $singer_id || 0 == $singer_sid )
            {
                // 4033400021(021)无效的参数
                $error['code'] = 403400021;
                $error['desc'] = '无效的请求参数';
                break;
            }
        //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_pk_model();
            //1    取出该主播是否在PK
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
            $m->linkcallpk_PK_info_by_PKsinger(&$error,$pkid,&$pk);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
    
        //逻辑功能结束//////////////////////////////////////////////////////////////////////////////////////////////
    
        }while(FALSE);
        if (0 !=$error['code'])
        {
            LogApi::logProcess("on_linkcallpk_user_seek_pk_rq error:".json_encode($error));
        }
    
        //on_linkcallpk_user_seek_pk_rq 包回包，default
        $rs = array();
        $rs['cmd'] = 'linkcallpk_user_seek_pk_rs';
        $rs['error'] = $error;
        $rs['singer_id'] = (int)$singer_id;
        $rs['time_now'] = $time_now;
        $rs['pk'] = $pk;
    
        $return[] = array
        (
            'broadcast' => 0,// 发rs包
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcallpk_siger_seek_online_list_rs singer_id：$singer_id singer_sid:".$singer_sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcallpk_siger_seek_online_list_rs singer_id：$singer_id singer_sid:".$singer_sid." return:".json_encode($return));
        return $return;    
    }
    
    
    //7、 用户查询连麦pk用户送礼信息(只用于推送最前面的5个列表，用于客户展示最前面的5个人头)
    public static function on_linkcallpk_user_seek_pk_rq($params)
    {
        LogApi::logProcess("on_linkcallpk_user_seek_pk_rq rq:".json_encode($params));
        $return               = array();
        $error                = array();
        $users                = array();
        //on_linkcallpk_user_seek_pk_rq 包数据，拆解rq包
        $singer_id   = (int)$params['guest_id'];
        $singer_sid  = (int)$params['guest_sid'];
    
        //b_error.info  rs回包错误信息default
        $error['code'] = 0;
        $error['desc'] = '';
        $time_now = time();
        do
        {
            //rq包验证////////////////////////////////////////////////////////////////////////////////////////////////
            if (0 == $singer_id || 0 == $singer_sid )
            {
                // 4033400021(021)无效的参数
                $error['code'] = 403400021;
                $error['desc'] = '无效的请求参数';
                break;
            }
            //逻辑功能/////////////////////////////////////////////////////////////////////////////////////////////////
            $m = new linkcall_pk_model();
            //1    取出该主播是否在PK
            $pkid = $m->redis_get_singer_pkid(&$error,$singer_id);
            if (0 != $error['code'])
            {
                //出现了一些逻辑错误
                break;
            }
            if ($pkid == 0)
            {
                //该主播不在pk，送礼列表为空
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
            LogApi::logProcess("on_linkcallpk_user_seek_pk_rq error:".json_encode($error));
        }
    
        //on_linkcallpk_user_seek_pk_rq 包回包，default
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
        LogApi::logProcess("on_linkcallpk_siger_seek_online_list_rs singer_id：$singer_id singer_sid:".$singer_sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcallpk_siger_seek_online_list_rs singer_id：$singer_id singer_sid:".$singer_sid." return:".json_encode($return));
        return $return;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}