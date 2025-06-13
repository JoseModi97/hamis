<?php
class home implements action
{
	public $params;
	
	public function execute()
	{
		return array("view_file"=>"home");
	}
	
}
?>