<?php

class SysParametersModel extends ModelBase
{    
    public function GetSysParameters($id,$field)
    {
        $value = $this->GetSysParametersRedis($id,$field);
        if (empty($value))
        {
            $value = $this->GetSysParametersMysql($id,$field);
        }
        if (empty($value))
        {
            $value = 0;
        }
        return $value;
    }
    public function GetSysParametersRedis($id,$field)
    {
        $value = NULL;
    
        $sys_parameters_key = 'sys_parameters';
        $elem_string = $this->getRedisMaster()->hGet($sys_parameters_key,$id);
        if (empty($elem_string))
        {
            $value = NULL;
        }
        else
        {
            LogApi::logProcess("GetSysParametersRedis elem_string:$elem_string");
            $elem = json_decode($elem_string, true);
            $value = $elem[$field];
        }
        LogApi::logProcess("GetSysParametersRedis value:$value");
        return $value;
    }
    public function GetSysParametersMysql($id,$field)
    {
        $value = NULL;
    
        $id_min = $id;
        $id_max = $id;
        // select id,parm1,parm2,parm3 from card.parameters_info where id >= 82 && id <= 90;
        $sql = "select id,parm1,parm2,parm3 from card.parameters_info where id = $id";
        $rows = $this->getDbMain()->query($sql);
        $db_array = array();
        if ( $rows )
        {
            if ( 0 < $rows->num_rows )
            {
                for ($x=0; $x<$rows->num_rows; $x++)
                {
                    $row = $rows->fetch_assoc();
                    // 0  1     2     3
                    // id,parm1,parm2,parm3
                    $db_array[$row['id']] = array('parm1'=>$row['parm1'],'parm2'=>$row['parm2'],'parm3'=>$row['parm3']);
                }
                $u = $db_array[$id];
                if (!empty($u))
                {
                    $value = $u[$field];
                }
                else 
                {
                    LogApi::logProcess("GetSysParametersMysql unknown id:$id");
                }
            }
        }
        else
        {
            LogApi::logProcess("GetSysParametersMysql sql:$sql");
        }
        LogApi::logProcess("GetSysParametersMysql value:$value");
        return $value;
    }
    
}
?>
