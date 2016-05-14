<?php
/**
 * Printemps config R/W class
 *
 * @package  Printemps
 * @subpackage  Config
 * @version 2
 * @author kirainmoe <kirainmoe@gmail.com>
 * @link https://github.com/kirainmoe/Printemps/
 */
class Printemps_Config
{
	/**
	 * config info
	 * 
	 * @var array
	 * @static
	 */
	static protected $config = array();

	/**
	 * global param
	 * 
	 * @var array
	 * @static
	 */
	static public $global = array();

	/**
	 * write config
	 *
	 * @static
	 * @return void
	 */
	public static function write()
	{
		$get = func_get_args();
		if(count($get) == 1)
			self::$config = array_merge(self::$config, $get[0]);
		else
		{
			for($i = 0; $i < count($get); $i+=2)
			{
				$name = $get[$i];
				$value = $get[$i+1];
				$tmp = array($name => $value);
				self::$config = array_merge(self::$config, $value);
			}
		}
	}

	/**
	 * read specified config
	 * 
	 * @param  string  $name  config name that you need
	 * @static
	 * @return   mixed
	 */
	public static function read($name)
	{
		return isset(self::$config[$name]) ? self::$config[$name] : false;
	}

	/**
	 * read all config
	 *
	 * @static
	 * @return array
	 */
	public static function readAll()
	{
		return self::$config;
	}

	/**
	 * write global config
	 * @param string $key 
	 * @param string $val 
	 */
	public static function setGlobal($key, $val)
	{
		return self::$global[$key] = $val;
	}
}
