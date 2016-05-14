<?php
/**
 * @package  Printemps
 * @subpackage Model
 * @version 2
 * @link https://github.com/kirainmoe/Printemps/
 * @author @kirainmoe <kirainmoe@icloud.com>
 *
 * @abstract
 */
abstract class Printemps_Model
{
	/**
	 * The table assoicated with the Model
	 * 
	 * @var string
	 */
	protected $tableName;
	/**
	 * Default primary Key
	 * 
	 * @var string
	 */
	protected $primary = 'id';
	/**
	 * Database Object
	 * 
	 * @var object
	 */
	protected $db;
	/**
	 * Self instance
	 *
	 * @static
	 * @var object
	 */
	public static $_instance;

	/**
	 * BaseModel construction
	 */
	function __construct()
	{
		$this->db = Printemps_Db::getInstance();
		self::$_instance = $this;
	}

	/**
	 * Set table name
	 * 
	 * @param string $name 
	 */
	public function setTable($name)
	{
		$this->tableName = $name;
	}

	/**
	 * Set primary key column
	 * 
	 * @param string $key
	 */
	public function setPrimaryKey($key)
	{
		$this->primary = $key;
	}

	/**
	 * Get all data from table
	 * 
	 * @return array
	 */
	public function all()
	{
		$raw = $this->db->select("*")
		->from($this->tableName)
		->exec();

		$fetched = $this->db->fetch($raw);
		return $fetched;
	}

	/**
	 * Get data with conditions from table
	 * 
	 * @param  mixed $condition 
	 * @return array            
	 */
	public function get($condition) 
	{
		$raw = $this->db->select("*")
		->from($this->tableName);

		if (is_array($condition)) {
			$raw = $raw->where($condition);
		} else {
			$raw = $raw->where(array($this->primary => $condition));
		}
		
		$raw = $raw->exec();

		$fetched = $this->db->fetch($raw);
		return $fetched;
	}

	/**
	 * Add row to table
	 * 
	 * @param Array $rows
	 * @return void
	 */
	public function add(Array $rows) 
	{
		$statement = $this->db->insert($this->tableName)
		->rows($rows)
		->exec();
	}

	/**
	 * Update data with conditions from table
	 * 
	 * @param  array  $rows      
	 * @param  mixed  $condition 
	 * @return void            
	 */
	public function update(Array $rows, $condition = array())
	{
		$statement = $this->db->update($this->tableName)
		->set($rows);

		if (is_array($condition)) {
			$statement = $statement->where($condition);
		} else {
			$statement = $statement->where(array($this->primary => $condition));
		}

		$statement->exec();
	}

	/**
	 * Delete data with conditions from table
	 * 
	 * @param  mixed  $condition 
	 * @return void            
	 */
	public function delete($condition = array())
	{
		$statement = $this->db->delete()
		->from($this->tableName);

		if (is_array($condition)) {
			$statement->where($condition);
		} else {
			$statement->where(array($this->primary => $condition));
		}

		$statement->exec();
	}

	/**
	 * Get self instanced object
	 * 
	 * @return object 
	 */
	public static function getInstance()
	{
		return (empty(self::$_instance) && !self::$_instance instanceof self) ? new self() : self::$_instance;
	}
}
