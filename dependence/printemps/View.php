<?php
/**
 * @package  Printemps
 * @subpackage View
 * @version 2
 * @link https://github.com/kirainmoe/Printemps/
 * @author @kirainmoe <kirainmoe@icloud.com>
 */
class Printemps_View 
{
	/**
	 * assign value
	 * 
	 * @var array
	 */
	protected $data = array();

	/**
	 * self instance
	 * 
	 * @var object
	 * @static
	 */
	public static $_instance;

	function __construct()
	{
		$this->path = APP_DEPENDENCE . 'views/';
		$this->class = Printemps_Router::$requested['controller'];
		$this->method = Printemps_Router::$requested['method'];

		self::$_instance = $this;
	}

	/**
	 * assign value
	 * 
	 * @return mixed 
	 */
	public function assign()
	{
		$arg = func_get_args();

		if (count($arg) == 0)				//no meaning
			return $this;

		elseif (count($arg) == 1)		//array($key => $value)
			$this->data = array_merge($this->data, $arg[0]);

		else 						//func($key, $value)
			$this->data[$arg[0]] = $arg[1];

		return $this;
	}

	/**
	 * display template (view)
	 * 
	 * @param  string $template custom template path
	 * @param  string $suffix   template file suffix (html is default)
	 * @return void
	 */
	public function display($template = '', $suffix = 'html')
	{
		extract($this->data);				//made $this->data as variables

		if (empty($template)) {			//template isn't specified
			$template = $this->path . $this->class . '/' . $this->method . '.' . $suffix;
			$c = self::compile($template, $this->class, $this->method, $suffix);
		}
		else {											//specified template file
			$arr = explode("/", $template);
			$template = $this->path . $template . '.' . $suffix;
			$c = self::compile($template, $arr[0], $arr[1], $suffix);
		}

		if( $c )											//if compiled success ? 
			include_once $c;
		else
			include_once $template;			//require template file
	}

	/**
	 * compile template-file to executable-script
	 * 
	 * @param  string $filepath   
	 * @param  string $controller 
	 * @param  string $template   
	 * @param  string $suffix     
	 * @return mixed
	 */
	public function compile($filepath, $controller, $template, $suffix)
	{
		/* try to read file */
		$file = @file_get_contents($filepath);
		if(!$file) {
			Printemps_Exception::notice("Can not read template file : " . $filepath . ".");
			return false;
		}

		/* begin compiling */
		$content = $file;

		$partten = array(
			'/\{(\$.*?)\}/',		/* {$variable} => <?php echo $variable; ?> */

			/* {include path='file.tpl'} => <?php include_once 'file.tpl' ?> */
			"/\{include.*?path=['|\"](.*?)['|\"].*?suffix=['|\"'](.*?)['|\"]\}/",

			/* {!if condition} do {!else} do {!endif} */
			"/\{!if(.*?)\}/", 
			"/\{!endif\}/", 
			"/\{!else\}/",

			/* {&repeat time=?} do {&endrepeat} */
			"/\{&repeat time=(.*?)\}/",
			"/\{&endrepeat\}/",

			/* {&while condition=(?)} do {&endwhile} */
			"/\{&while condition=\((.*?)\) update=\((.*?)\)\}/",
			"/\{&endwhile\}/",

			/* Custom php codes */
			"/&&(.*?)&&/",

			/* Link redirecting */
			"/\{go=['|\"](.*?)['|\"]\}/"
		);

		$replacement = array(
			"<?php echo $1;?>",
			"<?php Printemps_View::getInstance()->display(\"$1\", \"$2\"); ?>",
			"<?php if ($1): ?>",
			"<?php endif; ?>",
			"<?php else: ?>",
			"<?php for (\$i = 0; \$i < $1; \$i++) { ?>",
			"<?php } ?>",
			"<?php while ($1): $2 ?>",
			"<?php endwhile; ?>",
			"<?php $1 ?>",
			"<?php echo Printemps_Response::generate(\"$1\"); ?>"
			);
		
		$content = preg_replace($partten, $replacement, $content);
		
		
		$compiled = Printemps_Config::read("APP_CACHES") . $controller . '/' . $template . '.' . $suffix . '.php';
		if(!is_dir(Printemps_Config::read("APP_CACHES") . $controller)) {
			mkdir(Printemps_Config::read("APP_CACHES") . $controller, 755);
		}

		$newfile = @fopen($compiled, "w+", true);
		if(!$newfile) {
			Printemps_Exception::notice("Can not create / open template file : " . $compiled . ".");
			return false;
		}

		$writer = @fwrite($newfile, $content);
		if(!$writer) {
			Printemps_Exception::notice("Can not write template file : " . $compiled . ".");
			return false;
		}

		return $compiled;				//return compiled file address

	}

	/**
	 * get self instance
	 * 
	 * @return object 
	 */
	public static function getInstance()
	{
		return (!empty(self::$_instance) && self::$_instance instanceof self) ? self::$_instance : new self();
	}
}
