<?php
class add implements action
{
	public $params;
	
	public function execute()
	{
		global $_PARAMS;
		$result = $_PARAMS['a'] + $_PARAMS['b'];
		$data = array("message"=>$result);
		return array("view_file"=>"messages","data"=>$data);
	}
}
?>