<?php
/**
 * This is a Model usage of Printemps 2.
 * Custom Model should extends from Printemps_Model.
 * If you want to constant a Model, just do like this.
 */
class indexModel extends Printemps_Model
{
	/**
	 * specify table name
	 * 
	 * @var string
	 */
	protected $tableName = "test";

	/**
	 * primary column name
	 * 
	 * @var string
	 */
	protected $primary = "id";
}