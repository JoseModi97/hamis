<?php
class action1 implements action
{
	public $params;
	
	public function execute()
	{
		$actionName = "Action 1 is loaded";
		$data = array("message"=>$actionName);
		return array("view_file"=>"messages","data"=>$data);
	}
}
?>