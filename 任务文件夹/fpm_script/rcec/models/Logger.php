<?php

class Logger
{

    const OPEN_SENDLOG = true; // 默認關閉遠程日誌
    const OPEN_FILELOG = true; // 默認打開文件日誌
    public static $FILE_SEG_SEQ = 0;           // 文件切分序列号
    const LOG_FILE_SIZE = 512;
    public static function getRedis ()
    {
        $config = Config::getConfig();
        $conf = $config['redis']['log'];
        $service = new RedisServer();
        $service->connect($conf[0], $conf[1], 2); // timeout为2秒
        if (! empty($conf[2])) {
            $service->auth($conf[2]);
        }
        return $service;
    }

    public static function sendDataCenterCommandLog ($command, $timeCost, $maxTime = 0)
    {
        if (! self::OPEN_SENDLOG) {
            return true;
        }
        if ($timeCost >= $maxTime) {
            $config = Config::getConfig();
            $dataCenterIp = $config['data_center']['ip'];
            $redisLog = self::getRedis();
            $logMsg = array();
            $logMsg['time'] = time();
            $logMsg['cost_time'] = $timeCost;
            $logMsg['command'] = $command;
            $logMsg['data_center_ip'] = $dataCenterIp;
            $redisLog->lPush('data_center_command_list', json_encode($logMsg));
        }
    }

    public static function sendSqlExecStatusLog ($query, $timeCost, $maxTime = 100)
    {
        if (! self::OPEN_SENDLOG) {
            return true;
        }
        if ($timeCost >= $maxTime) {
            $redisLog = self::getRedis();
            $logMsg = array();
            $logMsg['time'] = time();
            $logMsg['cost_time'] = $timeCost;
            $logMsg['sql'] = $query;
            $redisLog->lPush('sql_exec_status_list', json_encode($logMsg));
        }
    }

    public static function sendServerConnectLog ($from, $to, $serverType, $timeCost, $maxTime = 100)
    {
        if (! self::OPEN_SENDLOG) {
            return true;
        }
        if ($timeCost >= $maxTime) {
            $redisLog = self::getRedis();
            $logMsg = array();
            $logMsg['time'] = time();
            $logMsg['cost_time'] = $timeCost;
            $logMsg['from'] = $from;
            $logMsg['to'] = $to;
            $logMsg['server_type'] = $serverType;
            $redisLog->lPush('server_connect_list', json_encode($logMsg));
        }
    }

    public static function fileLog ($message, $timeCost = 0, $fileName = 'info', $maxTime = 0)
    {
        if (! self::OPEN_FILELOG) {
            return true;
        }
        if ($timeCost < $maxTime) {
            return true;
        }
        
        $dir = "/data/vnc_log/vnc/vnc_fpm_script";
        if(!is_dir($dir)) 
        {
            mkdir($dir, 0755, true);
        }

        $date = date("Y-m-d");
        $dir = $dir . "/" . $date;
        if(!is_dir($dir)) 
        {
            mkdir($dir, 0755, true);
        }

        //$file = RCEC_ROOT . '/tmp/' . $fileName . '.' . date('Ymd') . '.log';
        $file = $dir . "/" . $fileName . '.log';
        $timeCost = $timeCost ? "($timeCost ms)" : "";
        $logMsg = date('H:i:s') . " $timeCost $message\n";

        $file_size = filesize($file);

        // default 512 MB
        if ($file_size < Logger::LOG_FILE_SIZE * 1024 * 1024) {
            file_put_contents($file, $logMsg, FILE_APPEND);
        } else {
            $file_lock = "/data/vnc_log/vnc/vnc_fpm_script/.file_seg_lock.log";
            $fp = fopen($file_lock, 'w+');

            if (flock($fp, LOCK_EX | LOCK_NB)) {

                while (file_exists(Logger::history_file_name($file))) {
                    Logger::incr_file_seg_seq();
                }

                rename($file, Logger::history_file_name($file));

                file_put_contents($file, $logMsg, FILE_APPEND);
                flock($fp, LOCK_UN);

            } else {
                file_put_contents($file_lock, "[get lock failed] logfile:$file. content:$logMsg", FILE_APPEND);
            }
        }
    }

    public static function logToDataFile ($fileName, $data)
    {
        $path = RCEC_ROOT . '/data_files/' . date('Ymd');
        if (! is_dir($path)) {
            @mkdir($path);
        }
        $file = $path . '/' . $fileName;
        $data = $data . "\n";
        @file_put_contents($file, $data, FILE_APPEND);

        $file_size = filesize($file);

        // default 512 MB
        if ($file_size < Logger::LOG_FILE_SIZE * 1024 * 1024) {
            file_put_contents($file, $data, FILE_APPEND);
        } else {
            $file_lock = "/data/vnc_log/vnc/vnc_fpm_script/.file_seg_lock.log";
            $fp = fopen($file_lock, 'w+');

            if (flock($fp, LOCK_EX | LOCK_NB)) {

                while (file_exists(Logger::history_file_name($file))) {
                    Logger::incr_file_seg_seq();
                }

                rename($file, Logger::history_file_name($file));

                file_put_contents($file, $data, FILE_APPEND);
                flock($fp, LOCK_UN);
            } else {
                file_put_contents($file_lock, "[get lock failed] logfile:$file. content:$data", FILE_APPEND);
            }
        }
    }

    public static function history_file_name($filename) 
    {
        return $filename . "_" . Logger::$FILE_SEG_SEQ;
    }

    public static function incr_file_seg_seq()
    {
        Logger::$FILE_SEG_SEQ++;
    }

}
?>