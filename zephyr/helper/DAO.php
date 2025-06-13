<?php
/**
 * Data Access Object which inserts, updates and deletes thru every possible model
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */

class DAO
{
	/**
	* @var	 	object
	* @access 	private
	* @since 	PR 1.0
	*/
	private $model;
	/**
	* @var	 	string
	* @access 	private
	* @since 	PR 1.0
	*/
	private $dbinfo;
	/**
	* @var	 	string
	* @access 	private
	* @since 	PR 1.0
	*/
	private $dbtype;

	/**
	* PHP5 Constructor
	* function constructor
	* load the model class and set it internally for reflection
	* 
	* @param 	object $model
	* @return	data access object
	* @access 	public
	* @since 	PR 1.0
	*/
	public function __construct(&$model=null, $dbinfo_class_name="dbinfo")
	{
		$model_name = get_class($model);
		//include_once("data_models/{$model_name}.php");
		$this->model = &$model;
		//set the datasource class name
		$this->dbinfo = $dbinfo_class_name;
		$pm = new packagemanager();
		include_once("../packages/{$pm->get_base_path()}/helper/{$dbinfo_class_name}.class.php");
		$__dbinfo = new $dbinfo_class_name(); //dbinfo();
		$this->dbtype = $__dbinfo->get_dbtype();

	}

	/**
	* function insert()
	* this reflects the available properties in the class and iterates thru them
	* then creates the insert query
	* and then execute it via DataBaseConnector
	* 
	* @param 	void
	* @return 	string 
	* @access 	public
	* @since 	PR 1.0
	*/
	public function insert(){

		//get the reflection class
		$cls = new ReflectionClass($this->model);
		$props = $cls->getProperties();
		while ($i < count($props))
		{
			$prop_name = $props[(int) $i]->getName();
			if ($this->dbtype!="sqlite")
			$field_name .= "`".$prop_name. "`, " ;
			else
			$field_name .= "".$prop_name. ", " ;
			$field_value .= "'". addslashes($this->model->$prop_name) ."'". ", ";
			$i++;
		}
		$field_name = substr($field_name, 0,  -2);
		$field_value = substr($field_value, 0 , -2);
		if ($this->dbtype=="sqlite")
		$insert_query = "insert into ".get_class($this->model)." ({$field_name}) values ({$field_value})";
		else
		$insert_query = "insert into `".get_class($this->model)."` ({$field_name}) values ({$field_value})";
		try{
			$result = DatabaseConnector::new_instance($this->dbinfo)->execute($insert_query);
		}
		catch (Exception $db_exception)
		{
			if ($db_exception->getCode()==DB_CONNECTION_FAILURE)  die("Cannot connect to database. Please check your dsn");
			$error_message = DatabaseConnector::new_instance($this->dbinfo)->ErrorMsg();
			//$lg->log($error_message, "DAO.php : Try");
		}
		if ($result===false)
		{
			$error_message = DatabaseConnector::new_instance($this->dbinfo)->ErrorMsg();
			throw new exception("Cannot insert data, there is an error. The error message is <font color='red'>{$error_message}</font>");
		}
		$this->flush(); //flush the model to avoid scope of data repeating
		return $insert_query;

	}

	/**
	* function update()
	* this reflects the available properties in the class and iterates thru them
	* then creates the update query, only non-empty values
	* and then execute it via DataBaseConnector
	* 
	* @param 	string $clause 
	* @return 	string 
	* @access 	public
	* @since 	PR 1.0
	*/
	public function update($clause){

		//get the reflection class
		$cls = new ReflectionClass($this->model);
		$props = $cls->getProperties();
		while ($i < count($props))
		{
			$prop_name = $props[(int) $i]->getName();
			$field_name = $prop_name ;
			$field_value = $this->model->$prop_name;
			if (!empty($field_value))
			{
				$update_string .= " {$field_name} = '{$field_value}',";
			}
			$i++;
		}
		$update_string = substr($update_string, 0 , -1);
		$update_query .= "update ".get_class($this->model)." set {$update_string} where {$clause}";
		try{
			$result=DatabaseConnector::new_instance($this->dbinfo)->execute($update_query);
		}
		catch (Exception $db_exception)
		{
			if ($db_exception->getCode()==DB_CONNECTION_FAILURE)  die("Cannot connect to database. Please check your dsn");
		}
		if ($result===false)
		{
			$error_message = DatabaseConnector::new_instance($this->dbinfo)->ErrorMsg();
			throw new exception("Cannot update data, there is an error. The error message is <font color='red'>{$error_message}</font>");

		}

		// flush the model to avoid scope of data repeating
		$this->flush();
		return $update_query;

	}

	/**
	* just makes every variable of the model to empty
	* 
	* @return 	void
	* @access 	private
	* @since 	PR 1.0
	*/
	private function flush()
	{
		while ($i < count($props))
		{
			$prop_name = $props[(int) $i]->getName();
			$field_name = $prop_name ;
			$this->model->$prop_name = ""; //flushed
			$i++;
		}
	}

	/**
	* function delete()
    * just delete the record based on clause
    * 
    * @param 	string $clause
    * @return 	void
    * @access 	public
    * @since 	PR 1.0
	*/	

	public function delete($clause)
	{
		$delete_query = "delete from ".get_class($this->model)." where {$clause}";
		try{
			$result = DatabaseConnector::new_instance($this->dbinfo)->execute($delete_query);
		}
		catch (Exception $db_exception)
		{
			if ($db_exception->getCode()==DB_CONNECTION_FAILURE)  die("Cannot connect to database. Please check your dsn");
		}
		return $delete_query;
	}

	/**
	* function selectBySQL()
	* this function executes a query and return teh whole rowset in a array
	* for best performance use LIMIT keyword
	* 
	* @param 	string $query
	* @return 	array
	* @access 	public
	* @since 	PR 1.0
	*/
	public function selectBySQL($query)
	{
		$data_store = array();
		$select_query = $query;
		try{
			$result = DatabaseConnector::new_instance($this->dbinfo)->execute($select_query);
		}
		catch (Exception $db_exception)
		{
			if ($db_exception->getCode()==DB_CONNECTION_FAILURE)  die("Cannot connect to database. Please check your dsn");
		}
		if ($result===false)
		{
			$error_message = DatabaseConnector::new_instance($this->dbinfo)->ErrorMsg();
			throw new exception("Cannot load data, there is an error. The error message is <font color='red'>{$error_message}</font>");
		}
		//populate the array with data
		while ($row = $result->fetchRow())
		{
			$data_store[] = $row;
		}
		return $data_store; //return the array

	}

	/**
	 * just executes a query 
	 * @since Beta2
	 * @access public
	 * @param string
	 * @return void
	 */
	public function execute($query)
	{
		DatabaseConnector::new_instance($this->dbinfo)->execute($query);
	}

	/**
	 * this function call any aggregate function and return teh result
	 *
	 * @since 	preview release 6.00
	 * @param 	string function name
	 * @param 	array column names
	 * @param 	table name
	 * @param 	string where clause
	 * @return 	string
	 */
	public function aggregator($function, $columns, $table, $condition=null)
	{
		$where_clause = empty($condition)? null: "where {$clause}";
		$my_columns = implode(",", $columns);
		$query = "select {$function}({$my_columns}) from {$table} {$where_clause}";
		$result = $this->selectBySQL($query);
		return $result[0][0];
	}

}
?>