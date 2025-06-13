<?php
class action2 implements action
{
	public $params;
	
	public function execute()
	{
		$actionName = "Action 2 is loaded";
		$data = array("message"=>$actionName);
		return array("view_file"=>"messages","data"=>$data);
	}
}
?>