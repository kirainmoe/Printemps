<?php
/**
 * Controller ： 全局控制器文件
 * Printemps Framework : Controller.pri.php
 * 2015 Printemps Framework
 */
class Printemps{
	/**
	 * 初始化数据库操作变量
	 * @var object
	 */
	public $db;
	/**
	 * 初始化数据处理变量
	 * @var object
	 */
	public $data;

	/**
	 * 构造函数
	 */
	function __construct(){
		try {
			$this->db = new Printemps_Database();
			$this->data = new DataProcess();
		} catch (Exception $e) {
			Printemps_Error(500,$e->getMessage());	
		}
		global $param;
	}

	/**
	 * Printemps Framework App 初始化
	 * @param  array  $config 用户配置
	 * @return none
	 */
	public static function Init($initialize = array()){
		/** 对Printemps Framework 做必须的初始化 */

		//设置错误拾取函数
		set_error_handler("Printemps_Error",E_CORE_ERROR ^ E_USER_ERROR);
		set_error_handler("Printemps_Notice",E_WARNING ^ E_NOTICE);

		/** 是否开始SESSION会话 */
		isset($initialize['session']) ? $initialize['session'] == true ? $startSession = true : $startSession = false : $startSession = false;
		if($startSession)
			session_start();

		/** 是否立刻开始路由分发 */
		isset($initialize['router']) ? $initialize['router'] == true ? $startRouter = true : $startRouter = false : $startRouter = true;
		if($startRouter)
			Printemps_Router::Dispatch();
	}
	/**
	 * 加载视图
	 * @param  string $viewName  加载的视图名称
	 * @param  string $className 加载的视图所在类
	 * @return none 
	 */
	public function loadView($viewName,$className = ''){
		$viewPath = APP_UI_PATH;
		if(empty($className)){
			$backtrace = debug_backtrace();
			$className = get_class($backtrace[0]['object']);
			$classPath = str_replace("Controller.temp","/",$className.'.temp');
			file_exists($viewPath.$classPath.$viewName) ? include $viewPath.$classPath.$viewName : Printemps_Error(500,"加载的视图 ".$viewPath.$classPath.$viewName." 不存在哦 : )！",__FILE__,__LINE__);
		}
		else{
			$classPath = str_replace("Controller.temp", "/", $className.'.temp');
			file_exists($viewPath.$classPath.$viewName) ? include $viewPath.$classPath.$viewName : Printemps_Error(500,"加载的视图 ".$viewPath.$classPath.$viewName." 不存在哦 : )！",__FILE__,__LINE__);
		}
	}

	/**
	 * 解析URL并返回相关内容
	 * @param  integer $return 指定返回的内容
	 * @return string          返回解析后的结果
	 */
	public static function parseURL($return = 1){
		//先拿到主机/域名地址
		$host = $_SERVER['HTTP_HOST'];
		//检测HTTPS是否打开
		if(isset($_SERVER['HTTPS'])){
			if($_SERVER['HTTPS'] == "on")
				$agreement = 'https://';
			else
				$agreement = 'http://';
		}
		else{
			$agreement = 'http://';
		}
		//然后拿到端口，如果为80/443则不显示
		if($_SERVER["SERVER_PORT"] == '80' || $_SERVER["SERVER_PORT"] == '443'){
			$port = '';
		}
		else{
			$port = ':'.$_SERVER["SERVER_PORT"];
		}
		$script = $_SERVER['PHP_SELF'];
		//检测QUERT_STRING
		if(!empty($_SERVER['QUERY_STRING'])){
			$param = '?'.$_SERVER['QUERY_STRING'];
		}
		else{
			$param = '';
		}
		//组成获取当前完整的URL地址
		$url = $agreement.$host.$port.$script.$param;
		//获取当前程序目录
		$path = preg_match("/(http.*?:\/\/.*?)(\/index\.php.*?)$/", $url, $res);
		if($return == 1)
			return $res[1].'/';
		else
			return $url;
	}
	/**
	 * 生成静态跳转链接
	 * @param  string $action 要跳转的动作
	 * @return string 	返回处理后的结果
	 */
	public static function locationLink($action){
		if(APP_ENTRY_MODE == 1)
			return self::parseURL().'index.php?act='.$action;
		elseif(APP_ENTRY_MODE == 2)
			return self::parseURL().'index.php/'.$action;
		elseif(APP_ENTRY_MODE == 3)
			return self::parseURL().$action;
	}
	/**
	* 将HTTP协议强制跳转到HTTPS，必须放在head之前。
	* 请慎用该函数，请在仅服务器支持HTTPS时使用。
	* @return header   none
	*/
	public static function forceHttps(){
		if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')
			header("Location:".str_replace("http://","https://",self::parseURL(0)));
	}
}