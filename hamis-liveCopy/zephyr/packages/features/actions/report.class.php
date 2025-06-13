<?php
class report implements action
{
	public $params;
	
	public function execute()
	{
		$dao = new DAO();
		$students = $dao->selectBySQL("select * from student");
		$data = array("students"=>$students);
		return array("view_file"=>"report","data"=>$data);
	}
}
?>