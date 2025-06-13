<?
/**
 * log_manager.class.php
 * logger singleton
 *
 * @author Hasin Hayder
 * @since 10th Nov, 20005
 * @copyright LGPLed by Hasin Hayder
 * @package Zephyr
 * @version 1.0
 */

include_once("logger.class.php");
class log_manager
{
	public static $logger;
	
	/**
	 * constructor
	 *
	 * @return void
	 */
	private function log_manager(){
		
	}
	
	/**
	 * function new_instance()
	 * this function generates the logger singleton and return it
	 *
	 * @return object logger
	 */
	public static function factory($file = "out.log", $kill=false)
	{
		if (!log_manager::$logger)
		{
			log_manager::$logger = new logger($file, $kill);
		}
		
		return log_manager::$logger;
	}
	
}
?>