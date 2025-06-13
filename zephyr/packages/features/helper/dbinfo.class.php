<?php
class dbinfo extends abstractdbinfo
{
	public function __construct()
	{
		$pm = new packagemanager();
		$package_path = $pm->get_package_path(); //physical path to this package
		$this->dbhost = $package_path."/sqlitedb/";
		$this->dbname = "test.sqlite";
		$this->dbtype = "sqlite";
		$this->persist = 1;
	}	
}
?>