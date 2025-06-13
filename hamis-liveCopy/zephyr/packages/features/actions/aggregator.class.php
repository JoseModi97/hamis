<?php
class aggregator implements action
{
	public $params;
	
	public function execute()
	{
		$dao = new DAO();
		$aggregated_result = sprintf("%3.2f",$dao->aggregator("avg",array("age"),"student"));
		$data = array("message"=>"Average age of all these students is : {$aggregated_result} year(s)");
		return array("view_file"=>"messages","data"=>$data);
	}
}
?>