<?php
class execute_script implements action
{
	public $params;
	
	public function execute()
	{
		$script = "<script id='auto_script'>alert ('Hi');</script> Embedded script says you \"hi\"";
		$data = array("message"=>$script);
		return array("view_file"=>"messages","data"=>$data);
	}
}
?>