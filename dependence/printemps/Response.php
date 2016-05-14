<?php
/**
 * @package Printemps
 * @subpackage Response
 * @version 2
 * @link https://github.com/kirainmoe/Printemps
 * @author @kirainmoe <kirainmoe@gmail.com>
 */
class Printemps_Response 
{
	/**
	 * access resource path
	 * 
	 * @static
	 * @var string
	 */
	private static $assets;
	/**
	 * HTTP Status Code and Detail
	 *
	 * @static
	 * @var array
	 */
	public static $statusCode = array(
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',

		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity',

		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		);

	/**
	 * generate redirect url
	 * 
	 * @param  string $redirect 
	 * @return void           
	 */
	public static function generate($redirect)
	{
		$baseURL = Printemps_Request::baseUrl();
		switch (APP_ENTRY_MODE)
		{
			case 1 :
			return $baseURL . '?' . $redirect;
			break;

			case 2:
			default:
			return $baseURL . '/index.php/' . $redirect;
			break;

			case 3:
			return $baseURL . '/' . $redirect;
			break;
		}
	}

	/**
	 * redirect link
	 * 
	 * @param  string $to     
	 * @param  string $method 
	 * @return void         
	 */
	public static function redirect($to, $method = 'header')
	{
		if ($method == 'header')
			header("Location: $to");
		else if ($method == 'javascript')
			echo "<script type=\"text/javascript\">window.location.href=\"$to\"</script>";
	}

	/**
	 * throw HTTP Status Code
	 * 
	 * @param  int $code 
	 * @return void       
	 */
	public static function throwHTTPStatus($code)
	{
		if (isset(self::$statusCode[$code]))
			header("HTTP/1.1 $code " . self::$statusCode[$code]);
	}

	/**
	 * import static content
	 * 
	 * @param  string $item 
	 * @return void       
	 */
	public static function import($item)
	{
		$url = Printemps_Request::baseUrl();
		self::$assets = $url . str_replace(APP_PUBLIC, "", APP_ASSETS);

		if (!is_array($item)) 
			self::doImport($item);
		else
			foreach ($item as $value)
				self::doImport($value);
	}

	/**
	 * do import action
	 * 
	 * @param  string $item 
	 * @return void       
	 */
	public static function doImport($item)
	{
		//Cascading Style Sheets
		if (preg_match("/(.*?)(\.css)$/", $item))
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . self::$assets . "css/$item\">\n";
		//Javascript
		else if(preg_match("/(.*?)(\.js)$/", $item))
			echo "<script type=\"text/javascript\" src=\"" . self::$assets . "js/$item\"></script>\n";
		//React.js JSX File
		else if(preg_match("/(.*?)(\.jsx)$/", $item))
			echo "<script type=\"text/babel\" src=\"" . self::$assets . "js/$item\"></script>\n";	
		//Image file
		else if(preg_match("/(.*?)(\.(png|jpg|jpeg|bmp|ico|tiff|gif))$/", $item))
			echo "<img src=\"" . self::$assets . "img/$item\" alt=\"$item\">\n";
	}
}
