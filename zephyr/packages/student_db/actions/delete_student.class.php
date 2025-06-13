<?php
/**
 * verifystd.class.php
 * auto generated code for zephyr using 'generator' version 2.00
 * this will process variable passed by javascript
 *
 * @author 		Hasin Hayder
 * @since 		26th Dec, 2005
 * @copyright 	LGPLed by Hasin Hayder
 * @package 	std
 * @version 	1.0
 */

load_db_domain("std");

class delete_student implements action {

	/**
	 * this variable stores incoming parameters
	 *
	 * @var 	string
	 * @access 	public
	 * @since 	1.0
	 */

	public $params ;

	/**
	 * function execute()
	 * this function returns the filename of view and what is the next action
	 *
	 * @return 	array
	 * @access 	public
	 * since 	1.0
	 */ 

	public function execute()
	{
		$cls = new std();
		$dao = new DAO($cls);
		try{
			$query = $dao->delete("roll = '{$this->params}'");
		}
		catch(Exception $e)
		{
			$data = array("message"=>$e->getMessage());
			return array("view_file"=>"messages", "data"=>$data);
		}
		
		$students = $dao->selectBySQL("select * from std");
		$data = array("students"=>$students);
		return array("view_file"=>"liststd", "data"=>$data);
	}

	public function get_view()
	{

	}
}

?>