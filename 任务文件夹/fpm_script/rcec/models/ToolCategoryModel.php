<?php

class ToolCategoryModel extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function getAll ()
    {
        $key = 'tool_category:all';
        //只获取新版本类别数据：id=17
        $query = "select * from tool_category where id in (17) order by sort_id";
        return $this->read($key, $query);
    }

    public function getSubCategory ($cate1)
    {
        $result = array();
        $cateList = $this->getAll();
        foreach ($cateList as $cate) {
            if ($cate['parent'] == $cate1) {
                $result[] = $cate;
            }
        }
        return $result;
    }

    public function getAllLabel ()
    {
        $key = 'tool_label:all';
        $query = "select * from tool_label";
        return $this->read($key, $query);
    }
}
?>
