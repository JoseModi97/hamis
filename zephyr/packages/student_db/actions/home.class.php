<?php
/**
 * newstd.class.php
 * auto generated code for zephyr using 'generator' version 2.00
 * this will just pass the view file name & command type 
 *
 * @author 		Hasin Hayder
 * @since 		15th Nov, 2005
 * @copyright 	LGPL
 * @package 	Zephyr
 * @version 	1.0
 */

class home implements action {

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
		$dao = new DAO();
		$students = $dao->selectBySQL("select * from std");
		$data = array("students"=>$students);
		return array("view_file"=>"std", "data"=>$data);
	}

	public function get_view()
	{

	}
}

?>