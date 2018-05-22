<?php
/**
 +------------------------------------------------------------------------------
 * 关键词替换类
 +------------------------------------------------------------------------------
 * @作者：		qimeng <songlv@163.com>
 * @版权声明：	本类只供PHP爱好者学习研究，严禁用于商业用途，否则作者将保留追究法律责任的权利
 * @使用方法：
 *
 *			$badwordfile	= 'badword.src.php';//关键词数组所在文件名
 *			$cachefile	= 'badword.aim.php'; //编译后的目标文件名
 *			$keyword= new keyword($badwordfile,$cachefile);
 *			$myword=$keyword->replace($str,1);
 *
 +------------------------------------------------------------------------------
 */

class keyword{

	var $cachefile=''; //编译文件名
	var $wword='';//编译前的关键词数组
	var $rword='';//编译后的关键词数组

	/**
     +----------------------------------------------------------
     * 构造函数
     +----------------------------------------------------------
     * @access	public
	 * @para	badword   		array		关键词数组
	 * @para	cachefile		string		编译后的目标文件名
     +----------------------------------------------------------
     */
	function keyword($badword,$cachefile='./cache/badword.aim.php'){
		$this->set_badword($badword);
		$this->set_cachefile_path($cachefile);
	}
	/**
	 +----------------------------------------------------------
	 * 设置关键字数组
	 +----------------------------------------------------------
	 * @access	public
	 * @para	badword   		array		关键词数组
	 +----------------------------------------------------------
	 */
	function set_badword($badword){
	    $this->wword = $badword;
	}
	/**
	 +----------------------------------------------------------
	 * 设置缓存文件路径
	 +----------------------------------------------------------
	 * @access	public
	 * @para	cachefile		string		编译后的目标文件名
	 +----------------------------------------------------------
	 */
	function set_cachefile_path($cachefile='./cache/badword.aim.php'){
	    $this->cachefile=$cachefile;
	}
	/**
	 +----------------------------------------------------------
	 * 构建查询结构
	 +----------------------------------------------------------
	 * @access	public
	 * @para	cachefile		string		编译后的目标文件名
	 +----------------------------------------------------------
	 */
	function load_and_compile_rwork(){
		//如果存在编译的文件 直接包含
	    if (file_exists($this->cachefile)){
	        $this->load_cache_file();
	    }else{//否则 重新生成
	        $this->rword = $this->format_word($this->wword);
	        echo count($this->wword);
	    }
	}
	/**
	 +----------------------------------------------------------
	 * 加载查询结构从缓存文件
	 +----------------------------------------------------------
	 * @access	public
	 +----------------------------------------------------------
	 */
	function load_cache_file(){
        include_once($this->cachefile);
        $this->rword = $_badwordcache;
	}
	/**
	 +----------------------------------------------------------
	 * 保存查询结构到缓存文件
	 +----------------------------------------------------------
	 * @access	public
	 +----------------------------------------------------------
	 */
	function safe_cache_file(){
        if($this->cachefile){
    	    $fh = fopen($this->cachefile, 'wb') or die("Error!!");
    	    fwrite($fh, "<?php\r\n\$_badwordcache=".var_export($this->rword,1).";");
        }
	}
	/**
	 +----------------------------------------------------------
	 * 保存查询结构到缓存文件(如果存在则不做操作)
	 +----------------------------------------------------------
	 * @access	public
	 +----------------------------------------------------------
	 */
	function try_safe_cache_file(){
	    if ($this->cachefile && !file_exists($this->cachefile)){
	        $this->safe_cache_file();
	    }
	}
	
	/**
     +----------------------------------------------------------
     * 过滤关键字
     +----------------------------------------------------------
     * @access	public
	 * @para	article		string		文章内容
	 * @para	type		bool		$type=1 标红 $type=2 换成 *
	 * @return	type		string		文章内容替换结果
     +----------------------------------------------------------
     */
	function replace($article,$type=2){
		$len=strlen($article);
		$begin=$end=array();
		for($i=0;$i<$len;$i++){
			if($n=$this->find_keyword($article,$this->rword,$i)){

				$begin[]=$i;
				$end[]=$i+$n;

				//换成*
				if($type==2){
				    $key_w = substr($article,$i,$n);
				    $key_n = mb_strlen($key_w,'utf8');
				    $repla = str_repeat("*",$key_n);
				    $article = substr_replace($article,$repla,$i,$n);
				    /*
					for($n;$n>0;$n--){
						$article{$i}='*';
						$i++;
					}
					*/
				}
				$i=$i+$n;
			}
		}
		//标红
		if($type==1){
			$len=count($begin);
			for($k=$len;$k>=0;$k--){
				if($end[$k])$article=$this->insertstr($article,$end[$k],'</font>');
				if($begin[$k])$article=$this->insertstr($article,$begin[$k],'<font color=red>');
			}
		}
		return	$article;
	}

	/**
     +----------------------------------------------------------
     * 递归查找指定位置是否有关键词
     +----------------------------------------------------------
     * @access	public
     +----------------------------------------------------------
     */
	function find_keyword($article,$rword,$i,$pos=1) {
		if($pos>20)Return false;
		if($rword['key'][$article{$i}]['val']==1)Return $pos;
		if(empty($rword['key'][$article{$i}]['key']))Return false;
		$pos++;
		$rword=$rword['key'][$article{$i}];
		return	$this->find_keyword($article,$rword,$i+1,$pos);

	}


	/**
     +----------------------------------------------------------
     * 将关键词数组转换成符合格式的数组
     +----------------------------------------------------------
     * @access	public
     +----------------------------------------------------------
     */
	function format_word($badword){
		$word=array();
		foreach($badword as $k=>$v){
			$temp='$word';
			$len=strlen($v);
			for($i=0;$i<$len;$i++){
				$temp.="['key']['".$v{$i}."']";
			}
			eval($temp.="['val']=1;");

		}
		return	$word;
	}


	/**
     +----------------------------------------------------------
     * 指定位置插入字符
     +----------------------------------------------------------
     * @access	public
     +----------------------------------------------------------
     */

	function insertstr($str,$pos,$instr){
		return	substr($str,0,$pos).$instr.substr($str,$pos,strlen($str));
	}


}