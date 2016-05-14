<?php
/**
 * Printemps action router class
 *
 * @package  Printemps
 * @subpackage  Router
 * @version 2
 * @author kirainmoe <kirainmoe@gmail.com>
 * @link https://github.com/kirainmoe/Printemps
 */
class Printemps_Router
{
	/**
	 * custom dispatch rules
	 * 
	 * @static
	 * @var array
	 */
	public static $custom;

	/**
	 * requested controller and method
	 *
	 * @static
	 * @var array
	 */
	public static $requested;

	/**
	 * start router distributing
	 * 
	 * @return void
	 */
	public static function dispatch()
	{
		/* load custom router rules */
		self::$custom = Printemps_Config::read("router");

		$mode = APP_ENTRY_MODE;
		switch($mode)
		{
			case 1:
				$request = $_SERVER['QUERY_STRING'];
				break;

			case 2:
			case 3:
				isset($_SERVER['PATH_INFO']) ? $request = $_SERVER['PATH_INFO'] : $request = '';
				break;
		}
		$request = explode("/", $request);
		array_splice($request, 0, 1);				//remove first element of request array

		if (isset($request[0]) && !empty($request[0])) {

			self::$requested['controller'] = isset(self::$custom['class'][$request[0]]) ? self::$custom['class'][$request[0]] : $request[0];

			if (isset($request[1]) && !empty($request[1])) {
				self::$requested['method'] = isset(self::$custom['method'][self::$requested['controller'].':'.$request[1]]) ? self::$custom['method'][self::$requested['controller'].':'.$request[1]] : $request[1];

				/** Write params */
				if (isset($request[2]) && $request[2] != '') {
					for($i = 2; $i < count($request) - 1; $i += 2) 
					{
						$name = $request[$i];
						$value = isset($request[$i + 1]) ? $request[$i + 1] : "";
						Printemps_Config::setGlobal($name, $value);
					}
				}
			}
			else
				self::$requested['method'] = "index";

		}
		else {

			self::$requested['controller'] = "index";
			self::$requested['method'] = "index";

		}

		self::$requested['fullname'] = self::$requested['controller'] . 'Controller';

		/* check if controller doesn't exist */
		if(!class_exists(self::$requested['fullname']))
			Printemps_Exception::halt("Requested Controller : " . self::$requested['fullname'] . " doesn't exist." );

		$called = new ReflectionClass(self::$requested['fullname']);			//create a ReflectionClass
		$called = $called->newInstance();																		//Instance a ReflectionClass

		/* check if method doesn't exist */
		if(!method_exists($called, self::$requested['method']))
			Printemps_Exception::halt("Requested Method : " . self::$requested['method'] . "() doesn't exist." );

		call_user_func(array($called, self::$requested['method']));					//Call requested method
	}

	/**
	 * edit custom dispatch rule
	 * for example: 
	 * rewrite('example','index','class');								//point example to indexController
	 * rewrite('index:example', 'index', 'method');				//point index:example to indexController->index()
	 * 
	 * @param  string $from 
	 * @param  string $to   
	 * @param  string $type class / method
	 * @return mixed
	 */
	public static function rewrite($from, $to, $type)
	{
		$type = strtolower($type);
		if( $type == 'class' )
			self::$custom['class'][$from] = $to;

		elseif( $type == 'method' )
			self::$custom['method'][$from] = $to;

		else
			return false;
	}
}
