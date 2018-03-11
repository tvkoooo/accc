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
    
    public static $LINKCALL_APPLY_COUNT_MAX  = 3;//��������������
    public static $LINKCALL_LINK_COUNT_MAX   = 2;//���������������
    public static $LINKCALL_EXP_TIME         = 3*24*60*60;//Ĭ������redis�޲�����󻺴�ʱ����3�죩
    public static $LINKCALL_EXP_60_STIME     = 60;//Ĭ������������60s�ڣ���3�������ж�Ϊɧ��
    
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
    // redis �������û�����60s�ظ������ж�
    public static function linkcall_user_data_apply_indexes_60s_zset_key($sid,$uid)
    {
        return "linkcall:user:data:apply:indexes:60s:zset:$sid:$uid";
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
    public function set_user_data_json(&$error,$sid,$user_id,&$data_cache)
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
            $exp_time =linkcall_model::$LINKCALL_EXP_TIME;
            $key_linkcall_user_data_json = linkcall_model::linkcall_user_data_json_hash_key($sid); 
            $redis->hSet($key_linkcall_user_data_json,$user_id,json_encode($data_cache));
            $redis->expire($key_linkcall_user_data_json,$exp_time);
            LogApi::logProcess("linkcall_model.set_user_data_json.hset��sid:$sid user_id:$user_id data_cache:".json_encode($data_cache));
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    } 
    
    //2.2    redis ����     �����û����ݻ���:������ʵʱͬ�������غ���Ч������Ƶ����ѯmysql��
    public function get_user_data_json(&$error,$sid,$user_id,&$data_cache)
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
            $data_cache = $v;
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    } 
    
    //2.3    redis ɾ��     �����������û����ݻ���
    public function del_user_data_json(&$error,$sid)
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
                LogApi::logProcess("linkcall_model.set_user_data_json.redis��sid:$sid ");
                break;
            }
            $key_linkcall_user_data_json = linkcall_model::linkcall_user_data_json_hash_key($sid);
            $redis->del($key_linkcall_user_data_json);
            LogApi::logProcess("linkcall_model.del_user_data_json.del��sid:$sid ");
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
            $exp_time =linkcall_model::$LINKCALL_EXP_TIME;
            $key_linkcall_user_data_apply_indexes = linkcall_model::linkcall_user_data_apply_indexes_zset_key($sid);
            $e=$redis->zAdd($key_linkcall_user_data_apply_indexes, $time_apply, $user_id);
            $redis->expire($key_linkcall_user_data_apply_indexes,$exp_time);
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
    
    //3.4   redis ɾ��     ������û���������
    public function del_user_apply_time(&$error,$sid,$user_id)
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
                LogApi::logProcess("linkcall_model.del_user_apply_time.redis: sid:$sid user_id:$user_id");
                break;
            }
            $key_linkcall_user_data_link_indexes = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);
            $e=$redis->zRem($key_linkcall_user_data_link_indexes, $user_id);
            if(0== $e)
            {
                $error['code'] = 200000003;
                $error['desc'] = '����ɾ�������쳣';
                LogApi::logProcess("inkcall_model.del_user_apply_time.zRemɾ�����ݷ���0: sid:$sid uid:$user_id");
                break;
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    //3.5   redis ɾ��     �����������û�������������
    public function del_user_apply_time_index(&$error,$sid)
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
                LogApi::logProcess("linkcall_model.del_user_apply_time_index.redis��sid:$sid ");
                break;
            }
            $key_linkcall_user_data_link_indexes = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);
            $redis->del($key_linkcall_user_data_link_indexes);
            LogApi::logProcess("linkcall_model.del_user_apply_time_index.del��sid:$sid ");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //3.6   redis д��     60s�ڵ��û������¼
    public function set_user_apply_time_60s(&$error,$sid,$user_id,$time_apply)
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
                LogApi::logProcess("linkcall_model.set_user_apply_time_60s.redis: sid:$sid user_id:$user_id time_apply:$time_apply");
                break;
            }
            $exp_time =linkcall_model::$LINKCALL_EXP_60_STIME;
            $key_linkcall_user_data_apply_indexes_60s= linkcall_model::linkcall_user_data_apply_indexes_60s_zset_key($sid,$user_id);
            $e=$redis->zAdd($key_linkcall_user_data_apply_indexes_60s, $time_apply, $time_apply);
            $redis->expire($key_linkcall_user_data_apply_indexes_60s,$exp_time);
            if(0== $e)
            {
                $error['code'] = 200000003;
                $error['desc'] = '����д������쳣';
                LogApi::logProcess("inkcall_model.set_user_apply_time_60s.zaddд�����ݷ���0: sid:$sid uid:$user_id time_apply:$time_apply");
                break;
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //3.7   redis ����     60s�ڵ��û������¼����    
    public function get_user_apply_time_60s_count(&$error,$sid,$user_id)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
        $num_apply = 0;
        $time_now= time();
        $time_60s_ago =$time_now -60;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.get_user_apply_time_60s.redis: sid:$sid user_id:$user_id ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_apply_indexes_60s_zset_key($sid,$user_id);
            $v = $redis->zCount($key,$time_60s_ago,$time_now);
            if(true == empty($v))
            {
                //���ȡ�������ݣ�����default ֵ 0�������б�������
                $v = 0;
            }
            $num_apply =$v;
            LogApi::logProcess("linkcall_model.get_user_apply_time_60s.zCount: sid:$sid user_id:$user_id num_apply:$num_apply");
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $num_apply;
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
            $exp_time =linkcall_model::$LINKCALL_EXP_TIME;
            $key_linkcall_user_data_link_indexes = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);
            $e=$redis->zAdd($key_linkcall_user_data_link_indexes,$time_allow, $user_id);
            $redis->expire($key_linkcall_user_data_link_indexes,$exp_time);
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
    
    //4.4   redis ɾ��     �������û�������ͨ����
    public function del_user_link_time(&$error,$sid,$user_id)
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
                LogApi::logProcess("linkcall_model.del_user_link_time.redis: sid:$sid user_id:$user_id");
                break;
            }
            $key_linkcall_user_data_link_indexes = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);
            $e=$redis->zRem($key_linkcall_user_data_link_indexes, $user_id);
            if(0== $e)
            {
                $error['code'] = 200000003;
                $error['desc'] = '����ɾ�������쳣';
                LogApi::logProcess("inkcall_model.del_user_link_time.zRemɾ�����ݷ���0: sid:$sid uid:$user_id");
                break;
            }
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    
    //4.5   redis ����     ��ǰ����ȫ���û��������Ӽ�¼����
    public function get_user_link_time_count(&$error,$sid)
    {
        $error['code'] = -1;
        $error['desc'] = 'δ֪����';
        $num_link = 0;
        do
        {
            $redis = $this->getRedisMaster();
            if(null == $redis)
            {
                // 100000701(701)�������ݿ�Ͽ�����
                $error['code'] = 100000701;
                $error['desc'] = 'redis���ݿ�Ͽ�����';
                LogApi::logProcess("linkcall_model.get_user_link_time_count.redis: sid:$sid ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);
            $v = $redis->zCount($key,0,-1);
            if(true == empty($v))
            {
                //���ȡ�������ݣ�����default ֵ 0�������б�������
                $v = 0;
            }
            $num_link =$v;
            LogApi::logProcess("linkcall_model.get_user_apply_time_60s.zCount: sid:$sid num_link:$num_link");
            //
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
        return $num_link;
    }
    
    //4.6   redis ɾ��     �����������û�������������
    public function del_user_link_time_index(&$error,$sid)
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
                LogApi::logProcess("linkcall_model.del_user_link_time_index.redis��sid:$sid ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_link_indexes_zset_key($sid);
            $redis->del($key);
            LogApi::logProcess("linkcall_model.del_user_link_time_index.del��sid:$sid ");
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
            $exp_time =linkcall_model::$LINKCALL_EXP_TIME;
            $key_linkcall_user_data_state_indexes = linkcall_model::linkcall_user_data_state_indexes_hash_key($sid);
            $redis->hSet($key_linkcall_user_data_state_indexes,$user_id,$linkcall_apply);
            $redis->expire($key_linkcall_user_data_link_indexes,$exp_time);
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
    
    //5.3   redis ɾ��     �����������û���������״̬����
    public function del_user_apply_state(&$error,$sid)
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
                LogApi::logProcess("linkcall_model.del_user_apply_state.redis��sid:$sid ");
                break;
            }
            $key = linkcall_model::linkcall_user_data_state_indexes_hash_key($sid);
            $redis->del($key);
            LogApi::logProcess("linkcall_model.del_user_apply_state.del��sid:$sid ");
            $error['code'] = 0;
            $error['desc'] = '';
        }while(0);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //����ģ��
    
    //6.1   �û������������� 
    public function user_apply_apply_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$data_cache,&$linkcall_apply,&$linkcall_state)
    {
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
        $num_apply = $this->get_user_apply_time_60s_count(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        if ($num_apply >= linkcall_model::$LINKCALL_APPLY_COUNT_MAX)
        {
            // 403300014(014)�û�60s���Ѿ��ظ�����3��
            $error['code'] = 403300014;
            $error['desc'] = '�û�60s���Ѿ��ظ�����3��';
            break;
        }
        
        // 3 ��ѯ��ǰ��������������������        
        $num_link =$this->get_user_link_time_count(&$error,$sid);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }        
        if ($num_link >= linkcall_model::$LINKCALL_LINK_COUNT_MAX)
        {
            // 403300016(016)��ǰ���������������ֵ����˶�
            $error['code'] = 403300016;
            $error['desc'] = '��ǰ���������������ֵ����˶�';
            break;
        }
        
        // 4 ��¼�û�
        {
            //��¼�û���������״̬��
            $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
            //��¼�û���������ʱ�䡣
            $time_apply = time();
            $this->set_user_apply_time(&$error, $sid, $user_id, $time_apply);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
            //��¼�û�60s��������ʱ�����󾯸档
            $this->set_user_apply_time_60s(&$error, $sid, $user_id, $time_apply);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }           
            
            //��¼�û��������ݻ���
            $this->set_user_data_json(&$error, $sid, $user_id, &$data_cache);
            {
                //������һЩ�߼�����
                break;
            }
        }        
        
        // 5 �����������������
        $this->linkcall_apply_singer_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }        
    }
    //6.2   �û�ȡ����������
    public function user_apply_desapply_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_apply,&$linkcall_state)
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
        // 2 �����û�ȡ�����󣬵���������nt
        $err = $this->linkcall_apply_singer_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 3 ɾ���û������������
        $this->del_user_apply_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 4 ��¼�û���������״̬��
        $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
    }
    //6.3   �û��˳�����
    public function user_apply_out_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_apply,&$linkcall_state)
    {
        // 1 ��ѯ���û��Ƿ��Ѿ��������б�
        $time_allow = $this->get_user_link_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        if ( 0 == $time_allow) 
        {
            // 403300017(017)���û����������б�
            $error['code'] = 403300017;
            $error['desc'] = '�û��Ѿ����������б���˶�';
            break;
        }
        // 2 �����û��˳����󣬵���������nt
        $err = $this->linkcall_apply_singer_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        
        // 3 ɾ���û����������ӱ�
        $this->del_user_link_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        
        // 4 �㲥ֱ���䣬��ǰ��������״̬   
        $this->linkcall_room_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        } 
        // 5 ��¼�û���������״̬��
        $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
    }
    
    //7.1  ������������
    public function singer_apply_yes_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_state,&$linkcall_apply)
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
        
        // 2 ��ѯ��ǰ������������ǰ�ж���������
        $num_link =$this->get_user_link_time_count(&$error,$sid);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        if ($num_link >= linkcall_model::$LINKCALL_LINK_COUNT_MAX)
        {
            // 403300016(016)��ǰ���������������ֵ����˶�
            $error['code'] = 403300016;
            $error['desc'] = '��ǰ���������������ֵ����˶�';
            break;
        }
        
        // 3.1 �Ѹ��û��������������б�
        $time_allow = time();
        $this->set_user_link_time(&$error,$sid,$user_id,$time_allow);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        } 
        
        // 3.2 �Ѹ��û�ɾ�����������б�
        $this->del_user_apply_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        
        // 3.3 ��¼�û���������״̬
        $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        
        // 4 ������������������û�        
        $this->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }

        // 5 �㲥ֱ���䣬��ǰ��������״̬
        $this->linkcall_room_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 6 $link_num ���ڱ�������ǰ��ѯͳ������,����������󣬳��� ���ֵ��2�ˣ�-1�˺��޳����е�ǰ��������
        if ( $num_link == linkcall_model::$LINKCALL_LINK_COUNT_MAX - 1)
        {            
            //��ѯ��ǰ���������б�ȡ����������user_id
            $apply_list=array();
            $this->get_user_apply_time_index(&$error,$sid,&$apply_list);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
            //�ò�ѯ����user_id��ȥ���͵���Ӧ���û��������ܾ�����
            $linkcall_apply_for = linkcall_model::LINKCALL_APPLY_NO;
            foreach ($apply_list as $uid => $score)
            {
                $data_get = array ();
                $data_get['time_apply'] = $score;
                $data_get['user_id'] = $uid ;
                //���� $uid�޸ĵ�ǰ�û�������״̬Ϊ   �����ܾ�
                $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply_for);
                if (0 != $error['code'])
                {
                    //������һЩ�߼�����
                    break;
                }
                //���� $uidȥ���͸��û�   �����ܾ�     
                $this->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);                
                if (0 != $error['code'])
                {
                    //������һЩ�߼�����
                    break;
                }
            }
            // ���������������ֵ���������뱻���ܾ����룬��������б�
            $this->del_user_apply_time_index(&$error,$sid);
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
        }
    }
    
    //7.2   �����ܾ�����
    public function singer_apply_no_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_state,&$linkcall_apply)
    {
        // 1 ��¼�û���������״̬��
        $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 2 �����������������û�
        $this->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 3 ɾ�����û��������¼��
        $this->del_user_apply_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
    }
    
    //7.3   �����Ͽ�����
    public function singer_apply_del_linkcall(&$error,&$return,$sid,$singer_id,$singer_nick,$user_id,&$linkcall_state,&$linkcall_apply)
    {
        // 1 ��¼�û���������״̬��
        $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 2 ���������Ͽ�������Ϣ���û�        
        $this->linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 2 �㲥ֱ���䣬��ǰ��������״̬      
        $this->linkcall_room_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 3 ɾ�����û������Ӽ�¼��
        $this->del_user_link_time(&$error,$sid,$user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        // 4 ��¼�û���������״̬��
        $this->set_user_apply_state(&$error,$sid,$user_id,$linkcall_apply);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
    }
    
    //8.1   �����û�user_id ƴװ�û� data
    public function linkcall_userdata_by_uid(&$error,$sid,$user_id,&$data)
    {
        $data_cache = array ();
        //1 ��$user_id ȥ��ȡ�û���������״̬
        $linkcall_apply = $this->get_user_apply_state(&$error, $sid, $user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }  
        
        //2 ��$user_id ȥ��ȡ�û����������ʱ��  ������û��������Ͽ������Լ��˳��������� ����$time_apply = 0��
        $time_apply = $this->get_user_apply_time(&$error, $sid, $user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        //3 ����$linkcall_apply ȷ�� �û�����ʱ�����û����������ʱ���time_allow = 0��
        $time_allow = $this->get_user_link_time(&$error, $sid, $user_id);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
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
    
    //8.2  ƴװ�����û�����daras
    public function linkcall_link_all_user_datas(&$error,$sid,&$datas)
    {
        // 1 ��ѯ��ǰ����������û�id
        $link_list=array();
        $this->get_user_link_time_index(&$error,$sid,&$link_list);
        // 2.ƴװdata�� ����datas
        foreach ($link_list as $uid => $score)
        {
            $data=array();
            $this->linkcall_userdata_by_uid(&$error,$sid,$uid,&$data);
            $datas[] = $data;
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
        }    
    }
    
    //8.3  ƴװ���������û�����datas
    public function linkcall_apply_all_user_datas(&$error,$sid,&$datas)
    {
        // 1 ��ѯ��ǰ����������û�id
        $apply_list=array();
        $this->get_user_apply_time_index(&$error,$sid,&$apply_list);
        // 2.ƴװdata�� ����datas
        foreach ($apply_list as $uid => $score)
        {
            $data=array();
            $this->linkcall_userdata_by_uid(&$error,$sid,$uid,&$data);
            $datas[] = $data;
            if (0 != $error['code'])
            {
                //������һЩ�߼�����
                break;
            }
        }
    }
    
    //9.1   ���ͷ���֪ͨ
    public function linkcall_room_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state)
    {
        // ƴװnt����ذ�
        $nt=array();
        $datas=array();
        $nt['sid'] = $sid;
        $nt['singer_id'] = $singer_id;
        $nt['singer_nick'] = $singer_nick;
        $nt['linkcall_state'] = $linkcall_state;
        $this->linkcall_link_all_user_datas(&$error,$sid,&$datas);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        $nt['datas'] = $datas;
        
        //nt��
        $return[] = array
        (
            'broadcast' => 6,// ��nt��
            'data' => $nt,
        );
        LogApi::logProcess("linkcall_room_state_nt sid:".$sid." nt:".json_encode($nt));
    }
    
    //9.2   �����û�֪ͨ
    public function linkcall_user_state_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id)
    {
        // ƴװnt�û��ذ�
        $nt=array();
        $data=array();
        $nt['sid'] = $sid;
        $nt['singer_id'] = $singer_id;
        $nt['singer_nick'] = $singer_nick;
        $nt['linkcall_state'] = $linkcall_state;
        $this->linkcall_userdata_by_uid(&$error,$sid,$user_id,&$data);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        $nt['data'] = $data;
        
        //nt��
        $return[] = array
        (
            'broadcast' => 0,// ��nt��
            'user_id' => $user_id,
            'data' => $nt,
        );
        LogApi::logProcess("linkcall_room_state_nt sid:".$sid."user_id:".$user_id." nt:".json_encode($nt));
    
    }
    
    //9.3   ��������֪ͨ
    public function linkcall_apply_singer_nt(&$error,&$return,$sid,$singer_id,$singer_nick,$linkcall_state,$user_id)
    {
       // ƴװnt�����ذ�
        $nt=array();
        $data=array();
        $nt['sid'] = $sid;
        $nt['singer_id'] = $singer_id;
        $nt['singer_nick'] = $singer_nick;
        $nt['linkcall_state'] = $linkcall_state;
        $this->linkcall_userdata_by_uid(&$error,$sid,$user_id,&$data);
        if (0 != $error['code'])
        {
            //������һЩ�߼�����
            break;
        }
        $nt['data'] = $data;
        
        //nt��
        $return[] = array
        (
            'broadcast' => 0,// ��nt��
            'user_id' => $singer_id,
            'data' => $nt,
        );
        LogApi::logProcess("linkcall_room_state_nt sid:".$sid."user_id:".$user_id." nt:".json_encode($nt));       
    
    }
    
    
    
}