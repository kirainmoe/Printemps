<?php
/**
 * Printemps Framework 控制器示例文件
 * @className indexController 	--------- 使用驼峰命名法，Controller 首字母大写。当用户访问URI未指定控制器时，PF会自动调用 indexController
 * @extendsClass Printemps		--------- 所有的自定义控制器都需要继承 Printemps 类
 */
class indexController extends Printemps{
	/**
	 * __construct 函数是PHP的构造函数，每个自定义的控制器中都要有一个构造函数
	 * 构造函数会在类控制器被实例化的时候被执行，即使不执行其他动作，你也需要 parent::__construct() 函数来调用父类的构造初始化函数。
	 */
	function __construct(){
		parent::__construct();
	}
	/**
	 * index 函数，当用户的访问URI指定了控制器，而没有指定方法名时，Printemps Framework 将会自动调用 index() 方法
	 * @return none
	 */
	public function index(){
		global $param;
		echo 'If you can see this sentence, that means your Printemps Application works successfully!';
	}
	/**
	 * 其他的函数方法命名可以随意，例如命名为 gay，域名为 localhost
	 * 则访问此方法的地址应该是 http://localhost/index.php/index/gay/
	 * @return none
	 */
	function gay(){
		echo 'Hello Gay!';
	}
}