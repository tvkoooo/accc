<?php

class ChannelAttributeModel extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function getAttrByCid ($sid, $cid)
    {
        $key = 'channel_attribute:' . $sid . '_' . $cid;
        $attr = $this->getRedisSlave()->hGetAll($key);
        if (empty($attr)) {
            $this->setBackground($sid, $cid, 0);
            $this->setEffect($sid, $cid, 0);
            $attr = array(
                'background' => 0,
                'effect' => 0
            );
        }
        return $attr;
    }

    public function setBackground ($sid, $cid, $tid)
    {
        $key = 'channel_attribute:' . $sid . '_' . $cid;
        $this->getRedisMaster()->hSet($key, 'background', $tid);
    }

    public function setEffect ($sid, $cid, $tid)
    {
        $key = 'channel_attribute:' . $sid . '_' . $cid;
        $this->getRedisMaster()->hSet($key, 'effect', $tid);
    }

    public function getGiftRecord ($sid, $cid)
    {
        $key = 'channel_attribute:' . $sid . '_' . $cid;
        $json = $this->getRedisSlave()->hGet($key, 'giftrecord');
        return json_decode($json, true);
    }

    public function getVideoEnable ($sid, $cid)
    {
        $key = 'channel_attribute:' . $sid . '_' . $cid;
        $json = $this->getRedisSlave()->hGet($key, 'videoenable');
        return json_decode($json, true);
    }

    public function setVideoEnable ($sid, $cid, $enable)
    {
        $key = 'channel_attribute:' . $sid . '_' . $cid;
        $this->getRedisMaster()->hSet($key, 'videoenable', $enable);
    }

    public function cleanCache ($sid, $cid)
    {
        $key = 'channel_attribute:' . $sid . '_' . $cid;
        $this->clean($key);
    }
}
?>