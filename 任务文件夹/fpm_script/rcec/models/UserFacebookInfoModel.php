<?php

class UserFacebookInfoModel extends ModelBase
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function isFbBound ($uid)
    {
        $req = new HttpRequestRcdb();
        $data = $req->check_bind($uid);
        return $data;
    }
}
