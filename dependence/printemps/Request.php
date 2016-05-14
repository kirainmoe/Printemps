<?php
/**
 * @package Printemps
 * @subpackage Request
 * @version 2
 * @link https://github.com/kirainmoe/Printemps
 * @author @kirainmoe <kirainmoe@gmail.com>
 */
class Printemps_Request
{
	/**
	 * self instance
	 * 
	 * @static
	 * @var object
	 */
	public static $_instance;

	function __construct()
	{
		self::$_instance = $this;
	}

	/**
	 * judge if HTTPS is active
	 * 
	 * @return boolean 
	 */
	public static function isSecure()
	{
		if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
			return true;
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO']) {
			return true;
		} elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
			return true;
		}
		return false;
	}

	/**
	* get full URL
	*
	* @static
	* @return string
	*/
	public static function fullUrl()
	{
		$baseURL = self::baseUrl();
		$uri = $_SERVER['REQUEST_URI'];		//requested uri		
		$qs = empty($_SERVER['QUERY_STRING']) ? "" : "?" . $_SERVER['QUERY_STRING'];		//query string
		$fullURL = $baseURL . $uri . $qs;		/* make full url */
		return $fullURL;
	}

	/**
	 * get base URL
	 * 
	 * @static
	 * @return string 
	 */
	public static function baseUrl()
	{
		$host = $_SERVER['HTTP_HOST'];		//hostname
		$protocol = self::isSecure() ? "https://" : "http://";		//protocol
		$baseURL = $protocol . $host;		/* make base url */
		return $baseURL;
	}

	/**
	 * get request method
	 * 
	 * @return string 
	 */
	public function getRequestMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * receive GET/POST/PUT data
	 * 
	 * @param  string $name 
	 * @return mixed       
	 */
	public function getData($name)
	{
		//POST
		if (isset($_POST[$name]))
			return $this->makeArray($_POST[$name], "POST");

		//GET
		else if (isset($_GET[$name]))
			return $this->makeArray($_GET[$name], "GET");

		//PUT
		else if ($this->getRequestMethod() === 'PUT') {
			$_PUT = array();
			parse_str(file_get_contents('php://input'), $_PUT);			//parse string from raw data
			return $this->makeArray($_PUT[$name], "PUT");
		}

		else
			return NULL;
	}

	/**
	 * for getData() : throw array included data
	 * 
	 * @param  string $value  
	 * @param  string $method 
	 * @return array         
	 */
	public function makeArray($value, $method)
	{
		$array = array(
			"value" => $value,
			"method" => $method,
			"recvtime" => time()
			);
		return $array;
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

	/**
	 * Judge if XHTTPRequest method is AJAX
	 * 
	 * @return boolean
	 */
	public function isAjax() 
	{
		return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) ? true : false;
	}

	/**
	 * PJAX : HTML5 pushState & AJAX
	 * 
	 * @return boolean 
	 */
	public function isPjax()
	{
		return array_key_exists('HTTP_X_PJAX', $_SERVER) && $_SERVER['HTTP_X_PJAX'];
	}

	/**
	 * Judge if requested by a cellphone
	 * 
	 * @return boolean 
	 */
	public function isMobile()
	{
		static $is_mobile;

		if( isset($is_mobile))
			return $is_mobile;

		if( empty($_SERVER['HTTP_USER_AGENT'])) {
			$is_mobile =false;
		} 
		else if (strpos($_SERVER['HTTP_USER_AGENT'],'Mobile') !== false || 
			strpos($_SERVER['HTTP_USER_AGENT'],'Android') !== false || 
			strpos($_SERVER['HTTP_USER_AGENT'],'Silk/') !== false ||
			strpos($_SERVER['HTTP_USER_AGENT'],'Kindle') !== false ||
			strpos($_SERVER['HTTP_USER_AGENT'],'BlackBerry') !== false ||
			strpos($_SERVER['HTTP_USER_AGENT'],'Opera Mini') !== false) {
			$is_mobile =true;
		} else {
			$is_mobile =false;
		}

		return $is_mobile;
	}
}
