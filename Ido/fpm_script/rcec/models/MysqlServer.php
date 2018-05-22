<?php

class MysqlServer extends mysqli
{

    public function query ($query, $log = true)
    {
        $timeStart = microtime(true);
        $result = parent::query($query);
        if ($log) {
            $timeCost = round((microtime(true) - $timeStart) * 1000, 2);
            if ($result === false) {
                Logger::fileLog($query . " reason:" . $this->error, $timeCost, 'error');
            } else {
				Logger::fileLog($query, $timeCost, 'info');
				
				// 不再写队列
                //Logger::sendSqlExecStatusLog($query, $timeCost);
            }
        }
        return $result;
    }
}