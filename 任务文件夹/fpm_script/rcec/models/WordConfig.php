<?php

include_once 'keyword.class.php';
include_once 'timeclock.php';

class WordConfig
{
    private $_content = array();
    private $_keyword = null;
    //保存类实例的静态成员变量
    private static $_instance;
    
    //private标记的构造方法
    private function __construct(){
        echo 'This is a Constructed method;';
    }
    
    //创建__clone方法防止对象被复制克隆
    public function __clone(){
        trigger_error('Clone is not allow!',E_USER_ERROR);
    }
    
    //单例方法,用于访问实例的公共的静态方法
    public static function Instance(){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    public function loadConfig ()
    {        
        LogApi::logProcess("begin getConfig************:".empty($this->_content));
        if (empty($this->_content)) {
            //$now = timeclock::getMillisecond();
            
			$path = RCEC_ROOT . "/models/";
            //$path = "/data/trunk/video/trunk/applications/datacenter_35/build/target/Linux_Debug64/script/rcec/models/";
            $content = file_get_contents($path."word.txt");
            $this->_content = explode("\r\n", $content);
//             LogApi::logProcess("加载文本************content:".json_encode(self::$_content));
            /*
            $t = timeclock::getMillisecond() - $now;
            LogApi::logProcess("加载关键字花费时间:: $t(ms)");

            $now = timeclock::getMillisecond();
            
            $this->_keyword = new keyword($this->_content,"/data/vnc_log/vnc/vnc_fpm_script/badword.aim.php");
            $this->_keyword->load_and_compile_rwork();
            $this->_keyword->try_safe_cache_file();
            
            $t = timeclock::getMillisecond() - $now;
            LogApi::logProcess("构建查询树花费时间:: $t(ms)");     
            */
        }
        
        return $this->_content;
    }
    //屏蔽关键字
    static public function filterWord($str){
        $word_config = WordConfig::Instance();
        return $word_config->filterWord_impl($str);
    }    
    //屏蔽关键字（实现）
    public function filterWord_impl($str){
        LogApi::logProcess("要过滤的文本:$str");
        
        $now = timeclock::getMillisecond();
        
        $this->loadConfig();        

        $t = timeclock::getMillisecond() - $now;
        LogApi::logProcess("加载配置项花费时间:: $t(ms)");
        $now = timeclock::getMillisecond();
        /*
        $str = $this->_keyword->replace($str);
        */
        
        foreach ($this->_content as $reg){
            if(empty($reg)){
                continue;
            }

//             $pattern = "/[u4e00-u9fa5]{0,0}(".$reg.")/";
//             LogApi::logProcess("正则表达式:: $pattern");
//             $str = preg_replace($pattern,"*",$str);

            // not use slow preg_replace will fast.
//             $key_n = mb_strlen($reg,'utf8');
//             $repla = str_repeat("*",$key_n);
//             $str = str_replace($reg,$repla,$str);
            $str = str_replace($reg,"*",$str);
        }
        
        $t = timeclock::getMillisecond() - $now;        
        LogApi::logProcess("屏蔽关键字花费时间:: $t(ms)");
        
        return $str;
    }
}
