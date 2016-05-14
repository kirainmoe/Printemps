<?php
/**
 * Printemps Database Class
 *
 * @package  Printemps
 * @subpackage  Db
 * @version  2
 * @author   kirainmoe<kirainmoe@gmail.com>
 * @link     https://github.com/kirainmoe/Printemps/
 */
class Printemps_Db
{
    /**
     * private database connection
     * @var object
     */
    private $d;
    /**
     * current connection number
     * @var int
     */
    private $current;
    /**
     * mysql connections pool
     * @var object
     */
    private $pool;
    /**
     * save config info
     * @var array
     */
    private $config;
    /**
     * mysql query builder
     * @var  array
     */
    private $build;
    /**
     * for pdo query preparation
     * @var object
     */
    private $prepare;
    /**
     * for pdo action
     * @var string
     */
    public $action;
    /**
     * full query string
     * @var string
     */
    public $query;
    /**
     * last query result
     * @var object
     */
    public $last;
    /**
     * self instance
     * @static
     * @var object
     */
    public static $_instance;

    /**
     * Printemps_Db constructor.
     *
     * @param $config database config
     * @return object
     */
    function __construct($config)
    {
        $this->current = 0;
        $this->pool = array();
        $this->connect($config, true);
        self::$_instance = $this;
        return $this->d;
    }

    /**
     * connect to a new mysql server
     *
     * @param  array $config config content
     * @param  bool $replace whether to replace current default connection
     * @return void
     */
    public function connect($config, $replace = true)
    {
        $this->config = $config;                //save config file for further using
        if (!isset($config['method']))
            Printemps_Exception::halt("Database connect method has not been set.");

        switch ($config['method']) {

            case "pdo":
            $strings = "mysql:host=" . $config['host'] . ";dbname=" . $config['name'];
            $user = $config['user'];
            $password = $config['password'];
            try{
                $database = new PDO($strings, $user, $password);                    
            }
            catch(Exception $e)
            {
                Printemps_Exception::halt("Failed connect to database through PDO Driver.");
            }
            $database->query("SET NAMES " . $config['encode']);
            $this->pushme($database, $replace);
            break;

            case "mysqli":
            $database = new mysqli($config['host'],
                $config['user'],
                $config['password'],
                $config['name'],
                $config['port']
                );

            $database->query("SET NAMES " . $config['encode']);
            $this->pushme($database, $replace);
            break;

            case "mysql":
            $database = mysql_connect($config['host'] . ':' . $config['port'], $config['user'], $config['password']);
            mysql_select_db($config['name'], $database);
            mysql_query("SET NAMES " . $config['encode'], $database);
            $this->pushme($database, $replace);
            break;

        }
    }

    /**
     * push the sql server connection to $this->pool
     *
     * @param  object $database mysql connection(pdo,mysqli or mysql)
     * @param  bool $replace whether to replace current default connection
     * @return bool
     */
    private function pushme($database, $replace)
    {
        array_push($this->pool, $database);
        $count = count($this->pool);
        if ($replace) {
            $this->d = $this->pool[$count - 1];
            $this->current = $count - 1;
        }
        return true;
    }

    /**
     * outside query interface
     *
     * @return mixed
     */
    public function query()
    {
        $args = func_get_args();
        if (empty($args)) {
            $ret = empty($this->query) ? false : $this->doQuery($this->query);
        } else {
            $ret = $this->doQuery($args[0]);
        }
        return $ret;
    }

    /**
     * query sub function : to execute query
     *
     * @param  string $query mysql query
     * @return mixed
     */
    private function doQuery($query)
    {
        switch ($this->config['method']) {
            case "pdo":
            $prep = $this->d->prepare($query);
            $res = $prep->execute();
            $this->last = $prep;
            return $prep;
            break;

            case "mysqli":
            $res = $this->d->query($query);
            $this->last = $res;
            return $res;
            break;

            case "mysql":
            $res = mysql_query($query);
            $this->last = $res;
            return $res;
            break;
        }
    }

    /**
     * build query and execute it
     *
     * @return object
     */
    public function exec()
    {
        $i = $this->build;
        switch ($this->action) {
            case "select":
                $final = $i['select'] . " " . $i['from'] . " " . $i['where'] . " " . $i['order'] . " " . $i['limit'];               //for SELECT
                break;

                case "insert":
                $final = $i['insert'] . " " . $i['rows'];                                   //for INSERT
                break;

                case "update":
                $final = $i['update'] . " " . $i['set'] . " " . $i['where'] . " " . $i['limit'];                                //for UPDATE
                break;

                case "count":
                case "have":
                $sql = $this->query("SELECT COUNT(" . $i['column'] . ") AS `total` FROM " . $i['table'] . " " . $this->build['where']
                    . $this->build['order'] . $this->build['limit']);                                       //for count() and have()
                $res = $this->fetch($sql);
                if ($this->action == "have")
                    return intval($res['total']) > 0 ? true : false;
                else
                    return $res['total'];
                break;

                case "delete":
                $final = $i['delete'] . " " . $i['from'] . " " . $i['where'] . " " . $i['order'] . " " . $i['limit'];           //for DELETE
                break;

                case "create":
                if (strstr($i['object'], "DATABASE"))
                    $i['object'] .= " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ";                 //if specified is DATABASE, set name as utf8
                $final = $i['create'] . $i['object'] . $i['condition'];                     //for CREATE
                break;

                case "drop":
                $final = $i['drop'] . $i['object'];             //for DROP
                break;

                case "truncate":
                $final = $i['truncate'] . $i['object'];      //for TRUNCATE
                break;
            }
            $this->query = $final;
            $res = $this->query();
            return $res;
        }

    /**
     * fetch query result
     *
     * @return mixed
     */
    public function fetch()
    {
        $args = func_get_args();
        $qres = empty($args) ? $this->last : $args[0];
        $fm = isset($args[1]) ? $args[1] : "default";                        //fetch method
        if (!empty($qres)) {
            switch ($this->config['method']) {
                case "pdo":
                switch ($fm) {
                    case "default":
                    case "object":
                    default:
                    $fetch = $qres->fetchAll();
                    break;

                    case "row":
                    $fetch = $qres->fetchAll(PDO::FETCH_COLUMN);
                    break;

                    case "array":
                    case "assoc":
                    $fetch = $qres->fetchAll(PDO::FETCH_ASSOC);
                    break;
                }
                return $fetch;
                break;

                case "mysqli":
                switch ($fm) {
                    case "default":
                    case "assoc":
                    default:
                    $fetchRes = array();
                    while ($res = $fetch = mysqli_fetch_array($qres, MYSQLI_ASSOC)) {
                        array_push($fetchRes, $res);
                    }
                    return $fetchRes;
                    break;

                    case "row":
                    $fetchRes = array();
                    while ($res = $fetch = mysqli_fetch_row($qres)) {
                        array_push($fetchRes, $res);
                    }
                    return $fetchRes;
                    break;

                    case "array":
                    $fetchRes = array();
                    while ($res = $fetch = mysqli_fetch_array($qres)) {
                        array_push($fetchRes, $res);
                    }
                    return $fetchRes;
                    break;

                    case "object":
                    $fetchRes = array();
                    while ($res = $fetch = mysqli_fetch_object($qres, MYSQLI_ASSOC)) {
                        array_push($fetchRes, $res);
                    }
                    return $fetchRes;
                    break;
                }
                return $fetch;
                break;

                case "mysql":
                switch ($fm) {
                    case "default":
                    case "assoc":
                    default:
                    $fetchRes = array();
                    while ($res = $fetch = mysql_fetch_array($qres, MYSQLI_ASSOC)) {
                        array_push($fetchRes, $res);
                    }
                    return $fetchRes;
                    break;

                    case "row":
                    $fetchRes = array();
                    while ($res = $fetch = mysql_fetch_row($qres)) {
                        array_push($fetchRes, $res);
                    }
                    return $fetchRes;
                    break;

                    case "array":
                    $fetchRes = array();
                    while ($res = $fetch = mysql_fetch_array($qres)) {
                        array_push($fetchRes, $res);
                    }
                    return $fetchRes;
                    break;

                    case "object":
                    $fetchRes = array();
                    while ($res = $fetch = mysql_fetch_object($qres)) {
                        array_push($fetchRes, $res);
                    }
                    return $fetchRes;
                    break;
                }
                return $fetch;
                break;
            }
        } else {
            return false;
        }
    }

    /**
     * select action series : specify object of selection
     *
     * @return object
     */
    public function select()
    {
        $this->reset();                        //reset to prepare new query
        $this->beforeBuild('select');        //define Query Builder variables , do not care whether it will be used
        $args = func_get_args();            //get user args
        $sel = "";
        if (!isset($args[0]) || empty($args[0]))
            $sel = "*";
        else {
            foreach ($args as $key => $value) {
                $separate = ($value != end($args)) ? true : false;
                $value = ($value == "*") ? $value : "`" . addslashes($value) . "` ";
                if ($separate) $value .= ", ";
                $sel .= $value;
            }
        }
        $this->build['select'] = "SELECT " . $sel . " ";
        return $this;                                    //return this object handle
    }

    /**
     * select action series : select from which table
     *
     * @return object
     */
    public function from()
    {
        $args = func_get_args();
        $from = "FROM ";
        foreach($args as $value)
        {
        	$value = "`".$value."` ";
        	$from .= $value;
        }
        $from .= " ";
        $this->build['from'] = $from;
        return $this;
    }

    /**
     * query builder : build WHERE condition query
     *
     * @return object
     */
    public function where()
    {
        $args = func_get_args();
        if (!isset($args[0]))
            return false;

        $condition = $args[0];
        if (is_array($condition)) {
            $tmp = "WHERE ";
            foreach ($condition as $key => $value) {
                $str = "`" . addslashes($key) . "` = \"" . addslashes($value) . "\" ";
                if (end($condition) != $value)
                    $str .= "AND ";
                $tmp .= $str;
            }
            $this->build['where'] = $tmp;
        } else {
            $cp = array();        //conditions pool
            $query = "";
            for ($i = 1; $i <= count($args) - 1; $i++)
                array_push($cp, $args[$i]);

            /* Processing unknown value */
            $replacement = "waiting_replace_".time();
            $args[0] = str_replace("?", $replacement, $args[0]);

            foreach ($cp as $value) {
                $value = '"' . addslashes($value) . '"';
                $args[0] = $this->replaceOnce($replacement, $value, $args[0]);
            }
            $query = "WHERE " . $args[0];
            $this->build['where'] = $query;
        }
        return $this;
    }

    /**
     * query builder : build ORDER BY conditions
     * 
     * @return object
     */
    public function order()
    {
        $args = func_get_args();
        $rank = $args[0];
        $tmp = "ORDER BY ";
        foreach ($args as $key => $value) {
            if ($value == $args[0])
                continue;

            $separate = ($value != end($args)) ? true : false;
            $value = ($value == "*") ? $value : "`" . addslashes($value) . "` ";
            if ($separate) $value .= ", ";
            $tmp .= $value;
        }
        $tmp .= $rank;
        $this->build['order'] = $tmp;
        return $this;
    }

    /**
     * query builder : build LIMIT conditions
     * @param  string $limit limit conditions
     * @return object
     */
    public function limit($limit)
    {
        $this->build['limit'] = " LIMIT " . addslashes($limit);
        return $this;
    }

    /**
     * insert action : specify insert table
     *
     * @param  string $table table name
     * @return object
     */
    public function insert($table)
    {
        $this->reset();
        $this->beforeBuild('insert');

        $this->build['insert'] = "INSERT INTO `" . $table . "` ";
        return $this;
    }

    /**
     * insert action : specify insert content
     *
     * @return object
     */
    public function rows()
    {
        $row = func_get_args();
        if (!isset($row[0]))
            return false;

        $column = "";
        $values = "";

        foreach ($row[0] as $key => $val) {
            $column .= "`" . addslashes($key) . "` ";
            $values .= '"' . addslashes($val) . '"';
            if ($val != end($row[0])) {
                $column .= ", ";
                $values .= ", ";
            }
        }
        $sql = "({$column}) VALUES ($values) ";
        $this->build['rows'] = $sql;
        return $this;
    }

    /**
     * update action : specify table
     *
     * @param  string $table table name
     * @return object
     */
    public function update($table)
    {
        $this->reset();
        $this->beforeBuild("update");
        $this->build['update'] = "UPDATE `" . addslashes($table) . "` ";
        return $this;
    }

    /**
     * update action : set update content
     *
     * @return  object
     */
    public function set()
    {
        $args = func_get_args();
        if (!isset($args[0]))
            return false;

        $condition = $args[0];
        if (is_array($condition)) {
            $tmp = "SET ";
            foreach ($condition as $key => $value) {
                $str = "`" . addslashes($key) . "` = \"" . addslashes($value) . "\" ";
                if (end($condition) != $value)
                    $str .= ", ";
                $tmp .= $str;
            }
            $this->build['set'] = $tmp;
        } else {
            $cp = array();        //conditions pool
            $query = "";
            for ($i = 1; $i <= count($args) - 1; $i++)
                array_push($cp, $args[$i]);
            foreach ($cp as $value) {
                $value = '"' . addslashes($value) . '" ';
                $args[0] = $this->replaceOnce("?", $value, $args[0]);
            }
            $query = "SET " . $args[0];
            $this->build['set'] = $query;
        }
        return $this;
    }

    /**
     * count a column by specified conditions
     *
     * @param  string $table specified table
     * @param  string $column count which column?
     * @return object
     */
    public function count($table, $column = "*")
    {
        $table = addslashes($table);
        $column = addslashes($column);
        $this->reset();
        $this->beforeBuild("count");
        $args = func_get_args();
        $this->build['table'] = $table;
        $this->build['column'] = $column;
        return $this;
    }

    /**
     * judge whether a specified table and condition have record
     *
     * @param  string $table specified table
     * @param  string $column count which column?
     * @return object
     */
    public function have($table, $column = "*")
    {
        $table = addslashes($table);
        $column = addslashes($column);
        $this->reset();
        $this->beforeBuild("have");
        $args = func_get_args();
        $this->build['table'] = $table;
        $this->build['column'] = $column;
        return $this;
    }

    /**
     * delect action : before DELETE
     *
     * @return object
     */
    public function delete()
    {
        $this->reset();
        $this->beforeBuild("delete");
        $this->build['delete'] = "DELETE ";
        return $this;
    }

    /**
     * create action : before CREATE
     *
     * @return object
     */
    public function create()
    {
        $this->reset();
        $this->beforeBuild("create");
        $this->build['create'] = "CREATE ";
        return $this;
    }

    /**
     * query builder : DATABASE
     *
     * @return object
     */
    public function database()
    {
        @$dbname = func_get_arg(0);
        $this->build['object'] = "DATABASE `" . $dbname . "` ";
        return $this;
    }

    /**
     * query builder : TABLE
     *
     * @return object
     */
    public function table()
    {
        @$tbname = func_get_arg(0);
        $this->build['object'] = "TABLE `" . $tbname . "` ";
        return $this;
    }

    /**
     * query builder : table-building conditions
     *
     * @param  array $cols
     * @return object
     */
    public function condition($cols = array())
    {
        if (empty($cols)) return false;
        if (!isset($this->build['object']) || strstr($this->build['object'], "DATABASE")) return $this;
        $tmp = $plus = "";
        if (isset($cols['PRIMARY KEY'])) {
            $plus .= ', PRIMARY KEY(`' . $cols['PRIMARY KEY'] . '`) ';
            unset($cols['PRIMARY KEY']);
        }
        if (isset($cols['__BUILD_ADDITIONS'])) {
            if (!empty($cols['__BUILD_ADDITIONS']))
                $plus .= "," . $cols['__BUILD_ADDITIONS'] . " ";
            unset($cols['__BUILD_ADDITIONS']);
        }
        foreach ($cols as $key => $value) {
            $tmp .= " `" . addslashes($key) . "` " . addslashes($value);
            if (end($cols) != $value)
                $tmp .= ", ";
        }
        $tmp .= $plus;
        $this->build['condition'] = "(" . $tmp . ")";
        return $this;
    }

    /**
     * drop action : before DROP
     * warning : this may be a dangerous action
     *
     * @return object
     */
    public function drop()
    {
        $this->reset();
        $this->beforeBuild("drop");
        $this->build['drop'] = "DROP ";
        return $this;
    }

    public function truncate()
    {
        $this->reset();
        $this->beforeBuild("truncate");
        $this->build['truncate'] = "TRUNCATE ";
        return $this;
    }

    /**
     * reset the value of variable
     *
     * @param  boolean $all clean all variables?
     * @return void
     */
    public function reset($all = false)
    {
        $this->query = "";                //reset query string
        $this->build = array();            //reset query builder
        $this->action = "";                //reset action
        $this->prepare = false;            //if connected by pdo class, reset pdo prepartion
        if ($all == true)
            $this->last = false;
    }

    /**
     * get self instanced object
     * 
     * @return object
     */
    public static function getInstance()
    {
        return (empty(self::$_instance) && !self::$_instance instanceof self) ? 
        new self(Printemps_Config::read('database')) : 
        self::$_instance;
    }

    private function replaceOnce($needle, $replace, $haystack)
    {
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            return $haystack;
        }
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }

    private function beforeBuild($action)
    {
        $this->action = $action;
        switch ($action) {
            case "select":
                $this->build = array("select" => "", "from" => "", "where" => "", "order" => "", "limit" => "");        //for SELECT
                break;

                case "insert":
                $this->build = array("insert" => "", "rows" => "");            //for INSERT
                break;

                case "update":
                $this->build = array("update" => "", "set" => "", "where" => "", "limit" => "");        //for UPDATE
                break;

                case "count":
                case "have":
                $this->build = array("table" => "", "column" => "", "where" => "", "order" => "", "limit" => "");        //for have() or count()
                break;

                case "delete":
                $this->build = array("delete" => "", "from" => "", "where" => "", "order" => "", "limit" => "");        //for DELETE
                break;

                case "create":
                $this->build = array("create" => "", "object" => "", "condition" => "");            //for CREATE
                break;

                case "drop":
                $this->build = array("drop" => "", "object" => "");                //for DROP
                break;

                case "truncate":
                $this->build = array("truncate" => "", "object" => "");        //for TRUNCATE
                break;
            }
        }
    }
