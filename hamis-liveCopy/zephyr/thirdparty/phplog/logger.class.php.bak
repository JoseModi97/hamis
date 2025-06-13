<?
/**
 * logger.class.php
 * logger object to log debug messages
 *
 * @author 		Hasin Hayder
 * @since 		10th Nov, 20005
 * @copyright 	LGPLed by Hasin Hayder
 * @package 	Zephyr
 * @version 	1.0
 */

define("LOG_CRITICAL", 1);
define("LOG_FATAL",2);
define("LOG_NORMAL",3);

class logger
{	
	
	/**
	 * filename to store logs
	 *
	 * @var string
	 * @access private
	 */
	private $file;
	
	/**
	 * constructor logger()
	 *
	 * @param string filename to store logs
	 * @param boolean should we use a new file for logs?
	 * @return logger
	 * @access private
	 */
	
	public function logger($file = "out.log", $kill = false)
	{	
		if($kill)
		unlink($file);
		$this->file=fopen($file, "a");
	}
	
	/**
	 * destructor, closes the file handle
	 *
	 * @return void
	 * @access private
	 */
	
	public function __destruct()
	{
		fclose($this->file);
		unset($this->file);
	}
	
	/**
	 * this function logs the message to the log file
	 *
	 * @param string $message
	 * @param string $caller the caller routine
	 * @param string $state, one of LOG_CRITICAL, LOG_FATAL, LOG_NORMAL
	 */
	public function log( $message,   $caller = "void", $state = LOG_NORMAL)
	{
		$time = date("M d, Y");
		$output = sprintf("[%-12s] [%-1s] [%-30s] %s \r\n", $time, $state, $caller, $message);
		fwrite($this->file, $output);
	}
}
?>