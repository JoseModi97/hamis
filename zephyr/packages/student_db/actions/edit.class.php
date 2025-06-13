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

class edit implements action {

	/**
	 * incoming data
	 *
	 * @var string
	 */
	public $params;
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
		$student = $dao->selectBySQL("select * from std where roll='{$this->params}'");	
		$data = array("std"=>$student[0]);
		return array("view_file"=>"edit", "data"=>$data);
	}

}

?>