 <?php
 
 class linkcall_model extends ModelBase
{ 
    public function __construct ()
    {
        parent::__construct();
    }
    //Linkcall ������  
    public static $LINKCALL_STATE_OPEN       = 0;//�����ܿ���
    public static $LINKCALL_STATE_CLOSED     = 1;//�����ܹر�
    
    public static $LINKCALL_APPLY_DEFAULT    = 0;//�û����� default
    public static $LINKCALL_APPLY_APPLY      = 1;//�û���������
    public static $LINKCALL_APPLY_DESAPPLY   = 2;//�û��˳�����
    public static $LINKCALL_APPLY_OUT        = 3;//�û��Ͽ�����    
    public static $LINKCALL_APPLY_YES        = 4;//����ͬ������
    public static $LINKCALL_APPLY_NO         = 5;//�����ܾ�����
    public static $LINKCALL_APPLY_DEL        = 6;//����ɾ������

    
    public static $LINKCALL_APPLY_MAX_PLAYER = 10;//��������������    
    
    // redis ��������������״̬���棺 
    public static function linkcall_state_searc_center_hash_key()
    {
        return "linkcall:state:searc:center:hash";
    }
    // redis �����û����ݻ���:������ʵʱͬ�������غ���Ч������Ƶ����ѯmysql��
    public static function linkcall_user_data_json_hash_key($sid)
    {
        return "linkcall:user:data:json:hash:$sid";
    }  
    // redis �������û�����������������¼��������ʱ�����
    public static function linkcall_user_data_apply_indexes_zset_key($sid)
    {
        return "linkcall:user:data:apply:indexes:zset:$sid";
    }
    // redis �������û�������ͨ��������¼������ͨʱ�����
    public static function linkcall_user_data_link_indexes_zset_key($sid)
    {
        return "linkcall:user:data:link:indexes:zset:$sid";
    }   
    // redis �������û���������״̬��������¼��������״̬��
    public static function linkcall_user_data_state_indexes_hash_key($sid)
    {
        return "linkcall:user:data:state:indexes:hash:$sid";
    } 

    //1.1    redis д��     ��������������״̬���棺
    public function set_singer_linkcall_state(&$error,$sid,$linkcall_state)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.set_singer_linkcall_state.redis: sid:$sid linkcall_state:$linkcall_state");
                break;
            }
            $key_linkcall_state = linkcall_model::linkcall_state_searc_center_hash_key();
            $redis->hSet($key_linkcall_state,$sid,$linkcall_state);
            
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    // 1.2    redis ����     ��������������״̬���棺
    public function get_singer_linkcall_state(&$error,$sid)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
        $linkcall_state = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.get_singer_linkcall_state.redis: sid:$sid");
                break;
            }
            $key_linkcall_state = linkcall_model::linkcall_state_searc_center_hash_key();
            $v=$redis->hGet($key_linkcall_state,$sid);
            if(true == empty($v))
            {
                // ��ֵ,$V ����Ĭ�Ͽ���״̬
                $v = linkcall_model::$LINKCALL_STATE_OPEN;
            }
            $linkcall_state=$v;
            $error['code'] = 0;
            $error['desc'] = '';            
        }while(0);
        return $linkcall_state;
    }
    
    //2.1  redis д��     �����û����ݻ���:������ʵʱͬ�������غ���Ч������Ƶ����ѯmysql��
    public function set_user_data_json(&$error,$sid,$user_id,&$linkcall_user_data)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.set_user_data_json.redis��sid:$sid user_id:$user_id");
                break;
            }
            $key_linkcall_user_data_json = linkcall_model::linkcall_user_data_json_hash_key($sid); 
            $redis->hSet($key_linkcall_user_data_json,$user_id,json_encode($linkcall_user_data));
            LogApi::logProcess("linkcall_model.set_user_data_json.hset��sid:$sid user_id:$user_id linkcall_user_data_json:".json_encode($linkcall_user_data));
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    } 
    
    //2.2    redis ����     �����û����ݻ���:������ʵʱͬ�������غ���Ч������Ƶ����ѯmysql��
    public function get_user_data_json(&$error,$sid,$user_id,&$linkcall_user_data)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';                
                LogApi::logProcess("linkcall_model.get_user_data_json.redis: sid:$sid user_id:$user_id");
                break;
            }
            $key_linkcall_user_data_json = linkcall_model::linkcall_user_data_json_hash_key($sid);            
            $linkcall_user_data_json = $redis->hGet($key_linkcall_user_data_json,$user_id);
            if(true == empty($linkcall_user_data_json))
            {
                // 200000099(099)��ȡ����Ϊ��
                $error['code'] = 200000099;
                $error['desc'] = '���û���������';
                LogApi::logProcess("linkcall_model.get_user_data_json.hget: sid:$sid user_id:$user_id");
                break;
            }
            LogApi::logProcess("linkcall_model.get_user_data_json��sid:$sid user_id:$user_id linkcall_user_data_json:$linkcall_user_data_json");
            $v = json_decode($linkcall_user_data_json, true);
            if(true == empty($v))
            {
                // 100000001(001)���ʧ��
                $error['code'] = 100000001;
                $error['desc'] = '���ʧ��';
                LogApi::logProcess("linkcall_model.get_user_data_json.hget.json���ʧ��  user_id:$user_id");
                break;
            }
            $linkcall_user_data = $v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    } 
    
    //3.1   redis д��     �������û�����������������¼��������ʱ�����
    public function set_user_apply_time(&$error,$sid,$user_id,$time_apply)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.set_user_apply_time.redis: sid:$sid user_id:$user_id time_apply:$time_apply");
                break;
            }
            $key_linkcall_user_data_apply_indexes = linkcall_model::linkcall_user_data_apply_indexes_zset_key($sid);
            $e=$redis->zAdd($key_linkcall_user_data_apply_indexes, $time_apply, $user_id);
            if(0== $e)
            {
                $error['code'] = 200000003;
                $error['desc'] = '����д������쳣';
                LogApi::logProcess("inkcall_model.set_user_apply_time.zaddд�����ݷ���0: sid:$sid uid:$user_id time_apply:$time_apply");
                break;
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }  

    //3.2   redis ����     ������ָ���û���������������ȡ����ѯ�û�������ʱ�����
    
    public function get_user_apply_time(&$error,$sid,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
        $time_apply = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.get_user_apply_time.redis: sid:$sid user_id:$user_id ");
                break;
            }
            $key_linkcall_user_data_apply_indexes = linkcall_model::linkcall_user_data_apply_indexes_zset_key($sid);    
            $v = $redis->zScore($key_linkcall_user_data_apply_indexes,$user_id);
            if(true == empty($v))
            {
                //���ȡ�������ݣ�����default ֵ 0�������б�������
                $v = 0;
            }
            $time_apply =$v;
            LogApi::logProcess("linkcall_model.get_user_apply_time.zscore: sid:$sid user_id:$user_id time_apply:$time_apply");
            //
            $error['code'] = 0;
            $error['desc'] = '';            
        }while(0);
        return $time_apply;
    }
    

    //3.3   redis ����     �������û�����������������¼��������ʱ�����
    public function get_user_apply_time_index(&$error,$sid,&$apply_list)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.get_user_apply_time_index.redis: sid:$sid ");
                break;
            }
            $key_linkcall_user_data_apply_indexes = linkcall_model::linkcall_user_data_apply_indexes_zset_key($sid);
            $set_number = linkcall_model::$LINKCALL_APPLY_MAX_PLAYER-1;
            $get_apply_list = $redis->zRange($key_linkcall_user_data_apply_indexes,0,$set_number,true);

            //�����ȡ���б�
            foreach ($get_apply_list as $uid => $score)
            {
                $data = array ();
                $data['time_apply'] = $score;
                $data['user_id'] = $uid;
                $apply_list[] = $data;
            }            
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.1   redis д��     �������û�������ͨ��������¼������ͨʱ�����
    public function set_user_link_time(&$error,$sid,$user_id,$time_allow)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.set_user_link_time.redis: sid:$sid user_id:$user_id time_allow:$time_allow");
                break;
            }
            $key_linkcall_user_data_link_indexes = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);
            $e=$redis->zAdd($key_linkcall_user_data_link_indexes,$time_allow, $user_id);
            if(0== $e)
            {
                $error['code'] = 200000003;
                $error['desc'] = '����д������쳣';
                LogApi::logProcess("inkcall_model.set_user_link_time.zaddд�����ݷ���0: sid:$sid uid:$user_id time_allow:$time_allow");
                break;
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.2  redis ����     ������ָ���û���������������ȡ����ѯ�û�������ʱ�����
    public function get_user_link_time(&$error,$sid,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
        $time_allow = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.get_user_link_time.redis: sid:$sid user_id:$user_id");
                break;
            }
            $key_linkcall_user_data_link_indexes = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);    
            $v = $redis->zScore($key_linkcall_user_data_link_indexes,$user_id);
            if(true == empty($v))
            {
                $v = 0;
            }
            $time_allow = $v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $time_allow; 
    }
    
    //4.3   redis ����     �������û�������ͨ��������¼������ͨʱ�����
    public function get_user_link_time_index(&$error,$sid,&$link_list)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.get_user_link_time_index.redis: sid:$sid");
                break;
            }
            $key_linkcall_user_data_link_indexes = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);    
            $get_link_list = $redis->zRange($key_linkcall_user_data_link_indexes,0,1,true);
            //�����ȡ���б�
            foreach ($get_link_list as $uid => $score)
            {
                $data = array ();
                $data['time_allow'] = $score;
                $data['user_id'] = $uid;
                $link_list[] = $data;
            }
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }  
 
    //5.1  redis д��     �������û���������״̬��������¼��������״̬��
    public function set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
    
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.set_user_apply_state.redis: sid:$sid user_id:$user_id linkcall_apply:$linkcall_apply");
                break;
            }
            $key_linkcall_user_data_state_indexes = linkcall_model::linkcall_user_data_state_indexes_hash_key($sid);
            $redis->hSet($key_linkcall_user_data_state_indexes,$user_id,$linkcall_apply);
    
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //5.2   redis ����     �������û���������״̬��������¼��������״̬��
    public function get_user_apply_state(&$error,$sid,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
        $linkcall_apply = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.set_user_apply_state.redis: sid:$sid");
                break;
            }
            $key_linkcall_user_data_state_indexes = linkcall_model::linkcall_user_data_state_indexes_hash_key($sid);
            $v=$redis->hGet($key_linkcall_user_data_state_indexes,$user_id);
            if(true == empty($v))
            {
                // 200000099(099)��ȡ����Ϊ��
                $v = 0;
            }
            $linkcall_apply = $v;
            $error['code'] = 0;
            $error['desc'] = '';

        }while(0);
        return $linkcall_apply;
    } 
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //����ģ��
    
    //6.1   �û������������� 
    public function user_apply_apply_linkcall(&$error,$sid,$singer_id,$user_id,&$data_cache,&$rs)
    {
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_APPLY;
        // 1 ��ѯ���û��Ƿ��Ѿ��������б�
        $time_apply = $this->get_user_apply_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        if (0 != $time_apply)
        {
            // 403300013(013)�û��Ѿ����������б�
            $error['code'] = 403300013;
            $error['desc'] = '�û��Ѿ����������б�';
            break;
        }
        // 2 �鿴60s���Ƿ��ظ�������3�Ρ�
        $err = $this->get_user_apply_time_record(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        if (0 != $err)
        {
            // 403300014(014)�û�60s���Ѿ��ظ�����3��
            $error['code'] = 403300014;
            $error['desc'] = '�û�60s���Ѿ��ظ�����3��';
            break;
        }
        // 3 ��¼�û�
        {
            $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
            $time_apply = time();
            $this->set_user_apply_time(&$error, $sid, $user_id, $time_apply);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
            $this->set_user_data_json(&$error, $sid, $user_id, &$data_cache);
            {
                //������һЩ�߼�����
                break;
            }
        }        
        
        // 4 �����������������
        
        $err = $this->linkcall_apply_singer_nt(&$error,$sid,$user_id,$linkcall_apply,$time_apply,&$data_cache);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }        
    }
    //6.2   �û�ȡ����������
    public function user_apply_desapply_linkcall(&$error,$sid,$singer_id,$user_id,&$data_cache,&$rs)
    { 
        // 3 �����������������
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_DESAPPLY;
        
        // 1 ��ѯ���û��Ƿ��Ѿ��������б�
        $time_apply = $this->get_user_apply_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 2 ȡ�����û��Ļ�����Ϣ
        $this->get_user_data_json(&$error, $sid, $user_id, &$data_cache);
        {
            //������һЩ�߼�����
            break;
        } 
        $err = $this->linkcall_apply_singer_nt(&$error,$sid,$user_id,$linkcall_apply,$time_apply,&$data_cache);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
    }
    //6.3   �û��˳�����
    public function user_apply_out_linkcall(&$error,$sid,$singer_id,$singer_nick,$user_id,&$data_cache,&$rs,&$linkcall_state)
    {
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_OUT;
        
        // 1 ��ѯ���û��Ƿ��Ѿ��������б�
        $time_apply = $this->get_user_apply_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 2 ȡ�����û��Ļ�����Ϣ
        $this->get_user_data_json(&$error, $sid, $user_id, &$data_cache);
        {
            //������һЩ�߼�����
            break;
        }
        // 3 �����˳����������
        $err = $this->linkcall_apply_singer_nt(&$error,$sid,$user_id,$linkcall_apply,$time_apply,&$data_cache);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 4 �㲥ֱ���䣬��ǰ��������״̬    
        
        $this->linkcall_room_state_nt(&$error,$sid,$singer_id,$singer_nick,$linkcall_state,&$data_cache);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }  
    }
    
    //7.1  ������������
    public function singer_apply_yes_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list)
    {
                        
        // 1 ��ѯ���û��Ƿ��Ѿ��������б�
        $time_apply = $this->get_user_apply_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        if (0 == $time_apply)
        {
            // 403300014(014)�û��Ѿ����������б���˶�
            $error['code'] = 403300014;
            $error['desc'] = '�û��Ѿ����������б���˶�';
            break;
        }
        
        // 2 ������������������û�
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_YES;
        $err = $this->linkcall_user_state_nt(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        
        // 3 ��ѯ��ǰ������Ϣ����ǰ�ֶ���������
        $this->get_user_link_time_index(&$error,$sid,&$link_list);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }

        $time_allow     = $user_data['time_allow'] = time();
        // 4 �Ѹ��û����������б�
        $this->set_user_link_time(&$error,$sid,$user_id,$time_allow);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        
        // 5 �㲥ֱ���䣬��ǰ��������״̬
        $linkcall_state = linkcall_model::LINKCALL_STATE_OPEN;
        $this->linkcall_room_state_nt(&$error,$sid,$linkcall_state);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 6 ��ѯ��ǰ������Ϣ����ǰ�ֶ���������,����2�˺��޳����е�ǰ��������
        $link_num = count($link_list);
        if ( 2 == $link_num )
        {
            //��ѯ��ǰ���������б�ȡ����������user_id
            $this->get_user_apply_time_index(&$error,$sid,&$apply_list);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
            //�ò�ѯ����user_id��ȥ���͵���Ӧ���û��������ܾ�����
            $linkcall_apply = linkcall_model::LINKCALL_APPLY_NO;
            foreach ($apply_list as $uid => $score)
            {
                $data_get = array ();
                $data_get['time_apply'] = $score;
                $data_get['user_id'] = $uid ;
                //���� $uidȥ���͸��û�   �����ܾ�
                $this->linkcall_user_state_nt(&$error,$sid,$uid,$linkcall_apply);
                if (0 != $error['code'])
                {
                    //������һЩ�߼�����
                    break;
                }
            }
        }
    }
    
    //7.2   �����ܾ�����
    public function singer_apply_no_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list)
    {
        // 1 �����������������û�
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_NO;
        $this->linkcall_user_state_nt(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            $error['code'] = 403300014;
            $error['desc'] = '�û��Ѿ����������б���˶�';
            //������һЩ�߼�����
            break;
        }
    }
    
    //7.3   �����Ͽ�����
    public function singer_apply_del_linkcall(&$error,$sid,$singer_id,$user_id,&$user_data,&$rs,&$link_list,&$apply_list)
    {
        // 1 ���������˳�������û�
        $linkcall_apply = linkcall_model::LINKCALL_APPLY_DEL;
        $this->linkcall_user_state_nt(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 2 �㲥ֱ���䣬��ǰ��������״̬       
        $linkcall_state = linkcall_model::LINKCALL_APPLY_DEL;
        $this->linkcall_room_state_nt(&$error,$sid,$linkcall_state);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        } 
    }
    
    //8.1   �����û�user_id ƴװ�û� data
    public function linkcall_user_link_list_to_data(&$error,$sid,$user_id,&$data)
    {
        $data_cache = array ();
        //1 ��$user_id ȥ��ȡ�û���������״̬
        $linkcall_apply = $this->get_user_apply_state(&$error, $sid, $user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }  
        //$linkcall_apply Ӧ��ֻ��2�������������ߣ�������������˳�
        if (!($linkcall_apply == linkcall_model::LINKCALL_APPLY_APPLY || $linkcall_apply == linkcall_model::LINKCALL_APPLY_APPLY)) 
        {
            // 403300015(015)�û��Ѿ����������б���˶�
            $error['code'] = 403300015;
            $error['desc'] = '�����û��Ǽ���������״̬�쳣';
            //������һЩ�߼�����
            break;
        }
        //2 ��$user_id ȥ��ȡ�û����������ʱ��
        $time_apply = $this->get_user_apply_time(&$error, $sid, $user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        //3 ����$linkcall_apply ȷ�� �û�����ʱ�����������û�ֻ�����룬û����������ʱ���time_allow = 0��
        if ( $linkcall_apply == linkcall_model::LINKCALL_APPLY_APPLY )
        {
            $time_allow =0 ;
        }
        else
            if ($linkcall_apply == linkcall_model::LINKCALL_APPLY_YES) 
            {
                //2 ��$user_id ȥ��ȡ�û����������ʱ���
                $time_allow = $this->get_user_link_time(&$error, $sid, $user_id);
                if (0 != $error['code'])
                {
                    //������һЩ�߼�����
                    break;
                }
            }
        
        //4 ��$user_id ȥ��ȡ�û�������Ϣ
        $this->get_user_data_json(&$error,$sid,$user_id,&$data_cache);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        //ƴװ�û� data
        $data[] = $data_cache;
        $data['linkcall_apply'] = $linkcall_apply;
        $data['time_apply'] = $time_apply;
        $data['time_allow'] = $time_allow;                
    }    
    
    
    
    
    
    
    
    
    
}