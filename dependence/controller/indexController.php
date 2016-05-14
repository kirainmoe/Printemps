<?php
/**
 * This is a Controller usage of Printemps 2
 * We recommanded you that when you constant a controller, it should extends from Printemps.
 * If you want to constant a controller, just do like this.
 */
class indexController extends Printemps 
{
	/**
	 * controller constuction
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * index method
	 * @return void 
	 */
	function index() {
		
		/* Call views */
		$this->view->assign("title", "Printemps 2")    /* Use View->assign() method to assign variable */
		->assign(array("description" => "Waiting to meet a miracle."))
		->display();    /* Call display() method to desplay */

	}
}