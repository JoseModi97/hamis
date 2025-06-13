<?php
class process_output_filter implements action
{
	public $params;
	
	public function execute()
	{
		$message = "Sample Data";
		$output = array("message"=>$message);
		return array("view_file"=>"messages", "data"=>$output);
	}
}
?>