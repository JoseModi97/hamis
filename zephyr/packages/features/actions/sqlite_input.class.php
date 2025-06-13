<?php
class sqlite_input implements action
{
	public $params;
	
	public function execute()
	{
		return array("view_file"=>"sqlite_input","data"=>$data);
	}
}
?>