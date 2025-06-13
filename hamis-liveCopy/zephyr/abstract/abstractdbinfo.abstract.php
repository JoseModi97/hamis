<?php
/**
 * abstract database info
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */
abstract class abstractdbinfo
{
	/**
	 * database host
	 *
	 * @var string
	 */
	protected $dbhost;
	
	/**
	 * datavase name
	 *
	 * @var string
	 */
	protected $dbname;
	
	/**
	 * database user
	 *
	 * @var string
	 */
	protected $dbuser;
	
	/**
	 * database password
	 *
	 * @var string
	 */
	protected $dbpwd;

	/**
	 * database type
	 *
	 * @var string
	 */
	protected $dbtype;
		
	/**
	 * should persist? 1 or 0
	 *
	 * @var integer
	 */
	protected $persist;
		

	/**
	 * return database host
	 *
	 * @return string
	 */
	public function get_dbhost()
	{
		return $this->dbhost;
	}
	
	/**
	 * return database name
	 *
	 * @return string
	 */
	public function get_dbname()
	{
		return $this->dbname;
	}
	
	/**
	 * return db user
	 *
	 * @return string
	 */
	public function get_dbuser()
	{	
		return $this->dbuser;
	}
	
	/**
	 * return database password
	 *
	 * @return string
	 */
	public function get_dbpwd()
	{
		return $this->dbpwd;
	}

	/**
	 * return database type
	 *
	 * @return string
	 */
	public function get_dbtype()
	{
		return $this->dbtype;
	}	
	
	/**
	 * return persistency condition
	 *
	 * @return integer
	 */
	public function get_persist()
	{
		return $this->persist;
	}		
}
?>