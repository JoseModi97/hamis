<?php
class process_using_filter implements action
{
	public $params;
	
	public function execute()
	{
		global $_PARAMS;
		$data = $_PARAMS; //unserialize($this->params);
		$name = $data['name'];
		$password = $data['password'];
		$message = "You submit <br/>Name = <b>{$name}</b> and <br/>Password = <b>{$password}</b><br/><br/> Data Processed using Input Filter";
		$output = array("message"=>$message);
		return array("view_file"=>"messages", "data"=>$output);
	}
}
?>