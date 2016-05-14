<?php
/**
 * Printemps : A light and beautiful PHP Framework
 *
 * Copyright(c) 2016 MoeFront Studio
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package Printemps
 * @version 2
 * @link https://github.com/kirainmoe/Printemps/
 * @author @kirainmoe <kirainmoe@gmail.com>
 * @subpackage Printemps
 */


class Printemps {
	/**
	 * All about Printemps
	 *
	 * @var array
	 */
	private $__printemps;
	/**
	 * instance of Printemps
	 * @var object
	 */
	public static $_instance;

	/**
	 * instance construction
	 *
	 * @return void
	 */
	function __construct() 
	{
		self::$_instance = $this;

		ini_set('date.timezone', 'Asia/Shanghai');
		//ini_set('display_errors', 'no');

		$this->__printemps['start'] = mktime(true);
		$_dbconfig = Printemps_Config::read('database');

		if ($_dbconfig) 
			$this->db = Printemps_Db::getInstance();

		$this->view = Printemps_View::getInstance();
		$this->request = Printemps_Request::getInstance();
	}

	/**
	 * Printemps initialization function
	 * 
	 * @param  array  $cfg config options
	 * @return bool  if Printemps has succeessfully inited, return true
	 */
	public static function init($cfg = array()) 
	{
		$basepath = dirname(__FILE__);
		/* default config file */
		$_default = array(
			/** Application root directory */
			"APP_ROOT_DIR" => $basepath,

			/** Application dependence directory */
			"APP_DEPENDENCE" => $basepath . '/dependence/', 


			/** Application views directory */
			"APP_VIEWS" => $basepath . '/dependence/views/', 

			/** Compiled template cache directory */
			"APP_CACHES" => $basepath . '/dependence/caches/',	

			/** App public directory */
			"APP_PUBLIC" => $basepath . '/public',

			/** Application static file directory */
			"APP_ASSETS" => $basepath . '/public/assets/', 

			/** Application name */
			"APP_NAME" => "Printemps Application", 

			/** Application version */
			"APP_VERSION" => "1", 

			/** In development environment (true) or not */
			"APP_DEBUG_MODE" => false, 

			/**
			 * Application entry mode , 1 is the default.
			 * 1 for Query String mode, 2 for Pathinfo mode, 3 for Rewrite mode.
			 * You should configure your Web Server correctly before you use 2 or 3.
			 */
			"APP_ENTRY_MODE" => 1,

			/** Custom error handler, only function is supported, false as default. */
			"APP_ERROR_HANDLER" => false, 
			);

		foreach ($_default as $key => $value) 
		{
			if (isset($cfg['initial'][$key])) 
				define($key, $cfg['initial'][$key]);
			else 
				define($key, $value);
		}

		/** Register Autoload  method*/
		self::registerAutoload();

		if (APP_ERROR_HANDLER)
			set_error_handler(APP_ERROR_HANDLER, E_CORE_ERROR ^ E_USER_ERROR);
		else
			set_error_handler(array("Printemps_Exception", "halt"), E_CORE_ERROR ^ E_USER_ERROR);

		set_error_handler(array("Printemps_Exception", "notice"), E_WARNING ^ E_NOTICE);			//for notice / warning
		set_exception_handler(array("Printemps_Exception", "halt"));		//for exception

		Printemps_Config::write($_default);
		Printemps_Config::write($cfg);

		isset($cfg['router']) && $cfg['router'] ? Printemps_Router::dispatch() : false;

		$_printemps = Printemps::getInstance();
		return $_printemps;
	}

	/**
	 * register spl_autoload method to load class file
	 * 
	 * @return void
	 */
	public static function registerAutoload()
	{
		function autoload_subfunc($class) {
			/** try to load Printemps key classes, these will have a prefix of `Printemps_` */
			if (preg_match("/^(Printemps_).*?/", $class)) {
				$class = str_replace("Printemps_", "", $class);
				$pathfile = APP_DEPENDENCE . 'printemps/' . $class . '.php';
			}

			/** Unknown class may be a Controller with `Controller` suffix ? */
			else if (preg_match("/.*?(Controller)$/", $class)) {
				if (file_exists(APP_DEPENDENCE . 'controller/' . $class . '.php'))
					$pathfile = APP_DEPENDENCE . 'controller/' . $class . '.php';
				else
					Printemps_Exception::halt("Can't find controller file $class.php - does it really exist?");
			}

			/** May be a model with `Model` suffix? */
			else if (preg_match("/.*?(Model)$/", $class)) {
				if (file_exists(APP_DEPENDENCE . 'model/' . $class . '.php'))
					$pathfile = APP_DEPENDENCE . 'model/' . $class . '.php';
				else
					Printemps_Exception::halt("Can't find model file $class.php - does it really exist?");
			}

			/** It must be extension! */
			else if(file_exists(APP_DEPENDENCE . 'extension/' . $class . '.php')) {
				$pathfile = APP_DEPENDENCE . 'extension/' . $class . '.php';
			}

			/** If all method fail to load... */
			else
				Printemps_Exception::halt("Can't find class file $class.php although we have tried our best.");

			require_once $pathfile;

		}
		spl_autoload_register("autoload_subfunc");
	}
	
	/**
	 * get self instanced object
	 * 
	 * @return object
	 */
	public static function getInstance()
	{
		return (empty(self::$_instance) && !self::$_instance instanceof self) ? new self() : self::$_instance;
	}
}
