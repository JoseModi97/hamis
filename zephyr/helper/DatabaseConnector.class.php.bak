<?
/**
 * Database connector, a singleton of adoDB
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */

// constants
define("DB_CONNECTION_FAILURE","DB404");

// inclusion
include_once("../helper/phploader.class.php");
$pl = new phploader("../");
$pl->add_adodb();
$pl->add_logger();

class DatabaseConnector
{
	public static $db;
	public static function new_instance($dbinfo_class_name = "dbinfo"){
		$pm = new packagemanager();
		include_once("../packages/{$pm->get_base_path()}/helper/{$dbinfo_class_name}.class.php");
		$dbinfo = new $dbinfo_class_name(); //dbinfo();
		if(!DatabaseConnector::$db[$dbinfo_class_name]) {
			switch ($dbinfo->get_dbtype())
			{
				case "mysql":
				$dsn = "{$dbinfo->get_dbtype()}://{$dbinfo->get_dbuser()}:{$dbinfo->get_dbpwd()}@{$dbinfo->get_dbhost()}/{$dbinfo->get_dbname()}?persist={$dbinfo->get_persist()}";
				break;
				
				case "sqlite":
				$db_path = urlencode($dbinfo->get_dbhost().$dbinfo->get_dbname());
				$dsn = "{$dbinfo->get_dbtype()}://{$db_path}/?persist={$dbinfo->get_persist()}";
				break;

				
			}

			DatabaseConnector::$db[$dbinfo_class_name] = & NewAdoConnection($dsn);
			if (!DatabaseConnector::$db[$dbinfo_class_name])
			{
				throw new Exception(DB_CONNECTION_FAILURE);
			}
		}

		return DatabaseConnector::$db[$dbinfo_class_name];
	}

}	