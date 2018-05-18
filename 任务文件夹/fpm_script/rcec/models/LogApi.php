<?php

class LogApi
{

    public static $FILE_SEG_SEQ = 0;           // 文件切分序列号
    const LOG_FILE_SIZE = 512;

	public static function logProcess($info) 
	{
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

        $filename = $dir. "/" . "php.log";
        $file_size = filesize($filename);

        // default 512 MB
        if ($file_size < LogApi::LOG_FILE_SIZE * 1024 * 1024) {
            file_put_contents($filename, date('Y-m-d H:i:s')." $info \n", FILE_APPEND);
        } else {
            $file_lock = "/data/vnc_log/vnc/vnc_fpm_script/.file_seg_lock.log";
            $fp = fopen($file_lock, 'w+');

            if (flock($fp, LOCK_EX | LOCK_NB)) {

                while (file_exists(LogApi::history_file_name($filename))) {
                    LogApi::incr_file_seg_seq();
                }

                rename($filename, LogApi::history_file_name($filename));
                file_put_contents($filename, date('Y-m-d H:i:s')." $info \n", FILE_APPEND);

                flock($fp, LOCK_UN);
            } else {
                file_put_contents($file_lock, "[get lock failed] logfile:$filename. content:$info \n", FILE_APPEND);
            }
        }
	}

    public static function history_file_name($filename) 
    {
        return $filename . "_" . LogApi::$FILE_SEG_SEQ;
    }

    public static function incr_file_seg_seq()
    {
        LogApi::$FILE_SEG_SEQ++;
    }
// 	public static function logProcess2($info){
// 		$dir = "/data/vnc_log/vnc/vnc_fpm_script";
//         if(!is_dir($dir)) {
//             mkdir($dir, 0755, true);
//         }
//         file_put_contents($dir."/2.log", date('Y-m-d H:i:s')." $info \n", FILE_APPEND);
// 	}
}

?>