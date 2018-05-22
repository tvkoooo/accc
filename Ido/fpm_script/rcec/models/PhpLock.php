<?php
 /*************************************************************************
 *file lock
 *@author Zeal Li
 *http://www.zeali.net/
 *
 *************************************************************************/
class PhpLock
{
     /*
     *lock_thisfile：获得独享锁
    *@param $tmpFileStr 用来作为共享锁文件的文件名（可以随便起一个名字）
    *@param $locktype 锁类型，缺省为false(非阻塞型，也就是一旦加锁失败则直接返回false),设置为true则会一直等待加锁成功才返回
    *@return 如果加锁成功，则返回锁实例(当使用unlock_thisfile方法的时候需要这个参数)，加锁失败则返回false.
     */
    
    public static function lock_thisfile($tmpFileStr,$flag=false){
    
        if($flag == false){
            $locktype = LOCK_EX|LOCK_NB;
        }else{
            $locktype = LOCK_EX;
        }
    
        $can_write = 0;
    
//         $lockfp = @fopen($tmpFileStr.".lock","w+");
        $lockfp = fopen($tmpFileStr, 'w+');
    
        if($lockfp){
            LogApi::logProcess('****************打开锁文件，准备加锁');
            $can_write = flock($lockfp,$locktype);
            LogApi::logProcess('****************完成加锁，返回：'.$can_write);
        }else {
            LogApi::logProcess('****************打开锁文件失败，返回结果：'.$lockfp);
        }
    
        if($can_write){
            LogApi::logProcess('****************加锁成功');
            return $lockfp;
    
        }
        else{
            LogApi::logProcess('****************加锁失败');
            if($lockfp){
                fclose($lockfp);
                LogApi::logProcess('****************关闭打开的锁文件');
//                 @unlink($tmpFileStr.".lock");
            }
    
            return false;
    
        }
    
    }
    
    /** 
     *unlock_thisfile：对先前取得的锁实例进行解锁
    *@param $fp lock_thisfile方法的返回值
    *@param $tmpFileStr 用来作为共享锁文件的文件名（可以随便起一个名字）
    */
    public static function unlock_thisfile($fp){
    
        flock($fp,LOCK_UN);
    
        fclose($fp);
    
//         @unlink($tmpFileStr.".lock");
    
    } 
}
 ?>
