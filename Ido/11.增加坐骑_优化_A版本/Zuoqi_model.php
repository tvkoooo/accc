<?php

class Zuoqi_model extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function redis_find_user_zuoqi_goodid($user_id,&$zuoqiid,&$goods_info)
    {
        $goods_info = NULL;
        $zuoqiid    = 0;
        $user_zuoqi_key = 0;
        if (0 == $user_id) 
        {
            LogApi::logProcess("Zuoqi_model.redis_find_user_zuoqi_goodid. ERROR user_id = 0");
            return ;
        }

        $this->redis_find_user_zuoqi_goodid_key($user_id,&$user_zuoqi_key);
        if (0 == $user_zuoqi_key) 
        {
            $zuoqiid = (int)$user_zuoqi_key;
            return;
        }
        $zuoqiid = (int)$user_zuoqi_key;
        $this->redis_find_goods_info_by_zuoqiid($zuoqiid,&$goods_info);        
    }
    
    public function redis_set_user_zuoqi_goodid_key(&$zuoqi_goodid_key_set)
    {
        $redis = $this->getRedisMaster();
        $db_card = $this->getDbMain();
        $sql = "SELECT * FROM card.goods_info where goods_type=66;";
        $db_card = $this->getDbMain();
        $rows = $db_card->query($sql);
        if (!empty($rows) && $rows->num_rows > 0)
        {
            for ($x=0; $x<$rows->num_rows; $x++)
            {
                $goods_info = array();
                $row = $rows->fetch_assoc();
                $zuoqiid  = $row["id"];
                $zuoqi_goodid_key_set[]= $zuoqiid;
                $key = "USER_HORSE_S_goodid_key";
                $redis->hSet($key,$zuoqiid,json_encode($row));
                $redis->expire($key,600);
            }
        }
        else
        {
            LogApi::logProcess("Zuoqi_model.redis_set_user_zuoqi_goodid_key. set key:USER_HORSE_S_goodid_key Fail");
        }        
    }
    public function redis_get_user_zuoqi_goodid_key(&$zuoqi_goodid_key_set)
    {
        $zuoqi_goodid_key_set = NULL;
        $redis = $this->getRedisMaster();
        $key = "USER_HORSE_S_goodid_key";
        $get_key = $redis->hKeys($key);
        if (empty($get_key))
        {
            $this->redis_set_user_zuoqi_goodid_key($zuoqi_goodid_key_set);
        }
        else
        {
            foreach ($get_key as $k => $v)
            {
                $zuoqi_goodid_key_set[] = $v;
            }            
        }        
    }
    
    public function redis_find_user_zuoqi_goodid_key($user_id,&$user_zuoqi_key)
    {
        $redis = $this->getRedisMaster();
        $zuoqi_goodid_key_set = array();
        $user_zuoqi_key = 0;
        $this->redis_get_user_zuoqi_goodid_key(&$zuoqi_goodid_key_set);
        if (!empty($zuoqi_goodid_key_set))
        {
            foreach ($zuoqi_goodid_key_set as $zuoqi_id)
            {
                $key = "USER:HORSE:S:$user_id:$zuoqi_id";
                $get_key = $redis->get($key);
                if (empty($get_key))
                {
                    continue;
                }
                $user_zuoqi_key = ($user_zuoqi_key > $get_key)?$user_zuoqi_key:$get_key;
            }
        }
    }
    
    
    public function redis_find_goods_info_by_zuoqiid($zuoqiid,&$goods_info)
    {
        $redis = $this->getRedisMaster();
        $key = "USER_HORSE_S_goodid_key";
        $get_key = $redis->hGet($key,$zuoqiid);
        $zuoqi_goodid_key_set = array();
        if (empty($get_key))
        {
            $this->redis_set_user_zuoqi_goodid_key(&$zuoqi_goodid_key_set);
            $goods_info = $zuoqi_goodid_key_set;
        }
        else 
        {
            $v = json_decode($get_key, true);
            if(true == empty($v))
            {
                LogApi::logProcess("Zuoqi_model.redis_find_goods_info_by_zuoqiid. USER_HORSE_S_goodid_key json_decode ERROR");
                $this->redis_set_user_zuoqi_goodid_key(&$zuoqi_goodid_key_set);
                $goods_info = $zuoqi_goodid_key_set;
            }
            else 
            {
                $goods_info = $v;
            }
            
        }          
    }
    
    
    
    
    
    
    
}
?>