<?php

class display_message implements  action {
	
	/**
	 * this variable stores incoming data
	 *
	 * @var 	string
	 * @access 	public
	 * @since 	1.0
	 */

	public $params ;

	
	public function execute()
	{
		$data = array("message"=>$this->params);
		return array("view_file"=>"messages", "command"=>"void", "data"=>$data);
	}
}
?>