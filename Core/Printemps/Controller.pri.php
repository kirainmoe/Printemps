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
	 * 构造函数
	 */
	function __construct(){
		try {
			$this->db = new Printemps_Database();
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
}