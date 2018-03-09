<?php
// ���ɶ���ӿ�
class linkcall_api
{   

    // A ������/�ر�������
    public static function on_linkcall_set_state_rq($params)
    {
        LogApi::logProcess("on_linkcall_set_state_rq rq:".json_encode($params));
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        //linkcall_set_state_rq�����ݣ����rq��
        $sid         = $params['sid'];
        $singer_id   = $params['singer_id'];
        $singer_nick = $params['singer_nick'];        
        $op_code     = $params['op_code']; 

        //b_error.info  rs�ذ�������Ϣdefault
        $error['code'] = 0;
        $error['desc'] = '';
        
        //��ʼ���û�����data
        //linkcall_user_data ��ʼ���û���������
        $user_id     =$data_cache['user_id']     = $user_data['user_id']        = 0;
        $user_level  =$data_cache['user_nick']   = $user_data['user_nick']      ="";
        $user_icon   =$data_cache['user_icon']   = $user_data['user_icon']      ="";
        $user_wealth =$data_cache['user_wealth'] = $user_data['user_wealth']    =0;
        $user_nick   =$data_cache['user_level']  = $user_data['user_level']     =0;
        $is_singer   =$data_cache['is_singer']   = $user_data['is_singer']      =0;
        //��ʼ���û���¼����
        $linkcall_apply = $user_data['linkcall_apply']    =0;
        $time_apply     = $user_data['time_apply']        =0;
        $time_allow     = $user_data['time_allow']        =0;
        
        //linkcall_set_state_rs���ذ���default
        $rs = array();
        $rs['cmd'] = 'linkcall_set_state_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['linkcall_state'] = -1;
        //////////rq����֤////////////////////////////////////////////////////////////////////////////////////////////////        
        do
        {
            if (0 == $sid || (!(0 == $op_code || 1 == $op_code)))
            {
                // 100000301(301)��Ч�Ĳ���
                $error['code'] = 100000301;
                $error['desc'] = '��Ч���������';
                break;
            }
            $m = new linkcall_model();
            //ȡ��������������״̬
            $linkcall_state = $m->get_singer_linkcall_state(&$error,$sid);               
            if (0 != $error['code']) 
            {
                  //������һЩ�߼�����      
                   break;
            }
            //�ж���������״̬�ͱ��������Ƿ����
            if ( $linkcall_state == $op_code)
            {
                // ���������л�������
                $linkcall_state = !$op_code;
                $m->set_singer_linkcall_state(&$error,$sid,$linkcall_state);
                if (0 != $error['code']) 
                {
                      //������һЩ�߼�����      
                       break;
                }
            }
            else
            {
                // ���������������ܿ���״̬�����ڸ�״̬
                $error['code'] = 403300011;
                $error['desc'] = '����������ǰ����Ҫ���õ�����״̬һ��';
                break;
            }
            //�߼�����/////////////////////////////////////////////////////////////////////////////////////////////////
            //�жϣ������ɹر�״̬  $LINKCALL_STATE_CLOSED ��   ����״̬ $LINKCALL_STATE_OPEN
            if (linkcall_model::$LINKCALL_STATE_OPEN == $linkcall_state) 
            {
                //�㲥ֱ���䣬��ǰ��������״̬
                $m->linkcall_room_state_nt(&$error,$sid,$linkcall_state);
            }
            else 
            {
                //�㲥ֱ���䣬��ǰ��������״̬                
                $m->linkcall_room_state_nt(&$error,$sid,$linkcall_state);
                //�������������û����ܾ�����
                $linkcall_apply1 = linkcall_model::$LINKCALL_APPLY_NO;
                $m->linkcall_user_state_nt(&$error,$sid,$user_id,$linkcall_apply1);
                //�������������û����Ͽ�����
                $linkcall_apply2 = linkcall_model::$LINKCALL_APPLY_DEL;
                $m->linkcall_user_state_nt(&$error,$sid,$user_id,$linkcall_apply2);                
            }
        }while(FALSE);
        //rs�ذ�
        $return[] = array
        (
            'broadcast' => 0,// ��rs��
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." return:".json_encode($return));
        return $return;  
    }       

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // B�û������ڣ�����/ȡ��/�˳�����
    public static function on_linkcall_apply_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        LogApi::logProcess("on_linkcall_set_state_rq rq:".json_encode($params));
        //linkcall_set_state_rq�����ݣ����rq��        
        $sid                         = $params['sid'];
        $singer_id                   = $params['singer_id']; 
        $singer_nick                 = $params['singer_nick'];
        $op_code                     = $params['op_code'];
        $user_data                   = $params['data'];        
        //
        //��ʼ���ذ���Ϣ
        //b_error.info  rs�ذ�������Ϣdefault
        $error['code'] = 0;
        $error['desc'] = '';
        
        //��ʼ���û�����data
        //linkcall_user_data ��ʼ���û���������
        
        $user_id     =$data_cache['user_id']     = $user_data['user_id'];
        $user_level  =$data_cache['user_nick']   = $user_data['user_nick'];
        $user_icon   =$data_cache['user_icon']   = $user_data['user_icon'];
        $user_wealth =$data_cache['user_wealth'] = $user_data['user_wealth'];
        $user_nick   =$data_cache['user_level']  = $user_data['user_level'];
        $is_singer   =$data_cache['is_singer']   = $user_data['is_singer'];
        //��ʼ���û���¼����
        $linkcall_apply = $user_data['linkcall_apply']    =0;
        $time_apply     = $user_data['time_apply']        =0;
        $time_allow     = $user_data['time_allow']        =0; 
        
        //linkcall_set_state_rs���ذ���default
        $rs = array();
        $rs['cmd'] = 'on_linkcall_apply_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['time_apply'] = $time_apply;
        $rs['singer_id']  = $singer_id;
        $rs['singer_nick']  = $singer_nick;
        $rs['linkcall_state'] = $linkcall_state = -1;
        //////////rq����֤////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {
            if (0 == $sid || 0== $singer_id || (!(1 == $op_code || 2 == $op_code || 3 == $op_code)))
            {
                // 100000301(301)��Ч�Ĳ���
                $error['code'] = 100000301;
                $error['desc'] = '��Ч���������';
                break;
            }
            $m = new linkcall_model();
            //ȡ��������������״̬
            $linkcall_state = $m->get_singer_linkcall_state(&$error,$sid);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
            //�ж������������Ƿ��Ѿ�����
            if (linkcall_model::$LINKCALL_STATE_CLOSED == $linkcall_state)
            {
                // 403300012(012)����δ��������״̬
                $rs['linkcall_state'] = $linkcall_state;
                $error['code'] = 403300012;
                $error['desc'] = '����δ��������״̬';
                break;
            }
            //�߼����ܣ������ǿ�������״̬��////////////////////////////////////////////////////////////////////////////////
            
            //�龰1���û�������������    1 == $op_code
            {
                if ( 1 == $op_code)
                {
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //�û���������
                    $m->user_apply_apply_linkcall(&$error,$sid,$singer_id,$singer_nick,$user_id,&$time_apply,&$data_cache);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                }
            }

            //�龰2���û�ȡ����������    2 == $op_code
            {
                if ( 2 == $op_code)
                {                       
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //��ȡ��������
                    $m->user_apply_desapply_linkcall(&$error,$sid,$singer_id,$user_id,&$data_cache,&$linkcall_state);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////                    

                }
            }
            
            //�龰3���û��˳�������    3 == $op_code
            {
                if ( 3 == $op_code)
                {
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //�û��˳���������
                    $m->user_apply_out_linkcall(&$error,$sid,$singer_id,$user_id,&$data_cache,&$linkcall_state);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////                   
 
                }
            }  
        
            //�߼����ܣ������ǿ�������״̬������////////////////////////////////////////////////////////////////////////////
             //rs �ذ�ƴװ
            $rs['error'] = &$error;
            $rs['time_apply'] = $time_apply;
            $rs['linkcall_state'] = $linkcall_state; 
        }while(FALSE);
        $return[] = array
        (
            'broadcast' => 0,// ��rs��
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." return:".json_encode($return));
        return $return;      
    }   
    
    
    // C ��������/�ܾ�/ɾ������
    public static function on_linkcall_allow_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        LogApi::logProcess("linkcall_allow_rq rq:".json_encode($params));
        //linkcall_allow_rq�����ݣ����rq��        
        $sid                         = $params['sid'];
        $singer_id                   = $params['singer_id']; 
        $singer_nick                 = $params['singer_nick'];
        $op_code                     = $params['op_code'];
        $user_id                     = $params['user_id'];        
        //
        //��ʼ���ذ���Ϣ
        //b_error.info  rs�ذ�������Ϣdefault
        $error['code'] = 0;
        $error['desc'] = '';
        
        //��ʼ���û�����data
        //linkcall_user_data ��ʼ���û���������
        $user_id     =$data_cache['user_id']     = $user_data['user_id']        = 0;
        $user_level  =$data_cache['user_nick']   = $user_data['user_nick']      ="";
        $user_icon   =$data_cache['user_icon']   = $user_data['user_icon']      ="";
        $user_wealth =$data_cache['user_wealth'] = $user_data['user_wealth']    =0;
        $user_nick   =$data_cache['user_level']  = $user_data['user_level']     =0;
        $is_singer   =$data_cache['is_singer']   = $user_data['is_singer']      =0;
        //��ʼ���û���¼����
        $linkcall_apply = $user_data['linkcall_apply']    =0;
        $time_apply     = $user_data['time_apply']        =0;
        $time_allow     = $user_data['time_allow']        =0;
        
        
        //linkcall_allow_rs���ذ���default
        $rs = array();
        $rs['cmd'] = 'linkcall_allow_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['time_apply'] = 0;
        $rs['singer_id']  = $singer_id;
        $rs['singer_nick']  = $singer_nick;        
        $rs['op_code'] = $op_code;
        $rs['data'] = $user_data;
        //////////rq����֤////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {
            if (0 == $sid || 0== $singer_id || (!(1 == $op_code || 2 == $op_code || 3 == $op_code)) )
            {
                // 100000301(301)��Ч�Ĳ���
                $error['code'] = 100000301;
                $error['desc'] = '��Ч���������';
                break;
            }
            $m = new linkcall_model();            

            //�߼����ܣ������ǿ�������״̬��////////////////////////////////////////////////////////////////////////////////
            //�����û��б�
            $link_list =array();
            //�����û��б�
            $apply_list =array();
            
            //�龰1������������������    1 == $op_code
            {
                if ( 1 == $op_code)
                {
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //������������
                    $m->singer_apply_yes_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    

                 }
            }

            //�龰2�������ܾ���������    2 == $op_code
            {
                if ( 2 == $op_code)
                {   
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //�����ܾ�����
                    $m->singer_apply_no_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////                  
                    

                }
            }
            
            //�龰3������ɾ��������    3 == $op_code
            {
                if ( 3 == $op_code)
                {
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    //����ɾ������
                    $m->singer_apply_del_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    
  
                }
            }  
   
        //�߼����ܣ������ǿ�������״̬������////////////////////////////////////////////////////////////////////////////
        }while(FALSE);
        $return[] = array
        (
            'broadcast' => 0,// ��rs��
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." return:".json_encode($return));
        return $return;      
    }
    
    // D ������ѯ���������б�
    public static function on_linkcall_list_singer_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        LogApi::logProcess("linkcall_list_singer_rq rq:".json_encode($params));
        //linkcall_allow_rq�����ݣ����rq��
        $sid                         = $params['sid'];
        $singer_id                   = $params['singer_id'];
        $singer_nick                 = $params['singer_nick'];

        //
        //��ʼ���ذ���Ϣ
        //b_error.info  rs�ذ�������Ϣdefault
        $error['code'] = 0;
        $error['desc'] = '';
    
        //��ʼ���û�����data
        //linkcall_list_singer_rs ��ʼ���û���������
        $user_id     =$data_cache['user_id']     = $user_data['user_id']        = 0;
        $user_level  =$data_cache['user_nick']   = $user_data['user_nick']      ="";
        $user_icon   =$data_cache['user_icon']   = $user_data['user_icon']      ="";
        $user_wealth =$data_cache['user_wealth'] = $user_data['user_wealth']    =0;
        $user_nick   =$data_cache['user_level']  = $user_data['user_level']     =0;
        $is_singer   =$data_cache['is_singer']   = $user_data['is_singer']      =0;
        //��ʼ���û���¼����
        $linkcall_apply = $user_data['linkcall_apply']    =0;
        $time_apply     = $user_data['time_apply']        =0;
        $time_allow     = $user_data['time_allow']        =0;    
    
        //linkcall_list_singer_rs���ذ���default
        $rs = array();
        $datas =array();        
        $rs['cmd'] = 'linkcall_allow_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['singer_id']  = $singer_id;
        $rs['singer_nick']  = $singer_nick;
        $rs['linkcall_state'] = $linkcall_state = -1;
        $rs['datas'] = $datas;
        //////////rq����֤////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {
            $m = new linkcall_model();
            //�����Ƿ�Ϸ�
            if (0 == $sid || 0== $singer_id  )
            {
                // 100000301(301)��Ч�Ĳ���
                $error['code'] = 100000301;
                $error['desc'] = '��Ч���������';
                break;
            }
            //�����Ƿ�������
            $linkcall_state = $m->get_singer_linkcall_state(&$error,$sid);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
    
            //�߼����ܣ������ǿ�������״̬��////////////////////////////////////////////////////////////////////////////////
            //�����û��б�
            $link_list =array();
            //�����û��б�
            $apply_list =array();
    
            //ȡ�������û��б��û���ƴװdatas
            //��ѯ��ǰ���������б�ȡ����������user_id
            $m->get_user_link_time_index(&$error,$sid,&$link_list);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
            //�ò�ѯ����user_id��ȥƴװdatas
            $linkcall_apply1 = linkcall_model::LINKCALL_APPLY_YES;
            foreach ($link_list as $uid => $score)
            {
                $data_get = array ();
                $data = array ();
                $data_get['time_allow'] = $score;
                $data_get['user_id'] = $uid ;
                //���� $uidȥƴװdata
                $m->linkcall_user_link_list_to_data(&$error,$sid,$uid,$linkcall_apply1,&$data);
                if (0 != $error['code'])
                {
                    //������һЩ�߼�����
                    break;
                }
                $datas[] = $data;
            }
            
            //ȡ�������û��б��û�������ƴװdatas
            //��ѯ��ǰ���������б�ȡ����������user_id
            $m->get_user_apply_time_index(&$error,$sid,&$apply_list);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
            //�ò�ѯ����user_id��ȥƴװdatas
            $linkcall_apply2 = linkcall_model::LINKCALL_APPLY_APPLY;
            foreach ($apply_list as $uid => $score)
            {
                $data_get = array ();
                $data = array ();
                $data_get['time_apply'] = $score;
                $data_get['user_id'] = $uid ;
                //���� $uidȥƴװdata
                $m->linkcall_user_applyt_list_to_data(&$error,$sid,$uid,$linkcall_apply2,&$data);
                if (0 != $error['code'])
                {
                    //������һЩ�߼�����
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

            //�߼����ܣ������ǿ�������״̬������////////////////////////////////////////////////////////////////////////////
        }while(FALSE);
        $return[] = array
        (
            'broadcast' => 0,// ��rs��
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." return:".json_encode($return));
        return $return;
    }
    
    // E �û�������/�û�����ѯ��ǰ����������Ϣ
    public static function on_linkcall_list_user_rq($params)
    {
        $return      = array();
        $user_data  = array();
        $error = array();
        $data_cache = array();
        LogApi::logProcess("linkcall_list_user_rq rq:".json_encode($params));
        //linkcall_allow_rq�����ݣ����rq��
        $sid                         = $params['sid'];
        $singer_id                   = $params['singer_id'];
        $singer_nick                 = $params['singer_nick'];       
    
        //
        //��ʼ���ذ���Ϣ
        //b_error.info  rs�ذ�������Ϣdefault
        $error['code'] = 0;
        $error['desc'] = '';
    
        //��ʼ���û�����data
        //linkcall_list_singer_rs ��ʼ���û���������
        $user_id     =$data_cache['user_id']     = $user_data['user_id']        = 0;
        $user_level  =$data_cache['user_nick']   = $user_data['user_nick']      ="";
        $user_icon   =$data_cache['user_icon']   = $user_data['user_icon']      ="";
        $user_wealth =$data_cache['user_wealth'] = $user_data['user_wealth']    =0;
        $user_nick   =$data_cache['user_level']  = $user_data['user_level']     =0;
        $is_singer   =$data_cache['is_singer']   = $user_data['is_singer']      =0;
        //��ʼ���û���¼����
        $linkcall_apply = $user_data['linkcall_apply']    =0;
        $time_apply     = $user_data['time_apply']        =0;
        $time_allow     = $user_data['time_allow']        =0;
    
        //linkcall_list_singer_rs���ذ���default
        $rs = array();
        $datas =array();
        $rs['cmd'] = 'linkcall_list_user_rs';
        $rs['error'] = &$error;
        $rs['sid'] = $sid;
        $rs['singer_id']  = $singer_id;
        $rs['singer_nick']  = $singer_nick;
        $rs['linkcall_state'] = $linkcall_state = -1;
        $rs['datas'] = $datas;
        //////////rq����֤////////////////////////////////////////////////////////////////////////////////////////////////
        do
        {
            $m = new linkcall_model();
            //�����Ƿ�Ϸ�
            if (0 == $sid || 0== $singer_id  )
            {
                // 100000301(301)��Ч�Ĳ���
                $error['code'] = 100000301;
                $error['desc'] = '��Ч���������';
                break;
            }
            //�����Ƿ�������
            $linkcall_state = $m->get_singer_linkcall_state(&$error,$sid);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
    
            //�߼����ܣ������ǿ�������״̬��////////////////////////////////////////////////////////////////////////////////
            //�����û��б�
            $link_list =array();
    
            //ȡ�������û��б��û���ƴװdatas
            //��ѯ��ǰ���������б�ȡ����������user_id
            $m->get_user_link_time_index(&$error,$sid,&$link_list);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
            //�ò�ѯ����user_id��ȥƴװdatas
            $linkcall_apply = linkcall_model::LINKCALL_APPLY_YES;
            foreach ($link_list as $uid => $score)
            {
                $data_get = array ();
                $data = array ();
                $data_get['time_allow'] = $score;
                $data_get['user_id'] = $uid ;
                //���� $uidȥƴװdata
                $m->linkcall_user_link_list_to_data(&$error,$sid,$uid,$linkcall_apply,&$data);
                if (0 != $error['code'])
                {
                    //������һЩ�߼�����
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
    
            //�߼����ܣ������ǿ�������״̬������////////////////////////////////////////////////////////////////////////////
        }while(FALSE);
        $return[] = array
        (
            'broadcast' => 0,// ��rs��
            'data' => $rs,
        );
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." rs:".json_encode($rs));
        LogApi::logProcess("on_linkcall_set_state_rq sid:".$sid." return:".json_encode($return));
        return $return;
    } 
    
    
    
    
    
    
    
    
}