<?php
class sample_form implements action
{
	public $params;
	
	public function execute()
	{
		return array("view_file"=>"input_filter");
	}
}
?>