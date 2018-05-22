<?php
class CarInfoModel extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function getCarInfoByUid ($uid)
    {
        $now = time();
        $key = 'carid:' . $uid;
        $query = "select * from car_buy where uid = $uid and end_time > $now";
        $rows = $this->read($key, $query, 864000);
        $data = array();
        if (count($rows) > 0) {
            foreach ($rows as $tmpRow) {
                if ($tmpRow['end_time'] > $now) {
                    $data[] = $tmpRow;
                }
            }
            return $data;
        }
        return false;
    }

    public function clearCache ($uid, $singerUid)
    {
        $this->getRedisMaster()->del('carid:' . $uid);
    }
}
