<?php
class createdb implements action
{
	public $params;
	
	public function execute()
	{
		$dao = new DAO();
		$dao->execute("create table student (name varchar(200), roll int, age int)");
		$data = array("message"=>"Successfully Created the database with a table 'student'");
		return array("view_file"=>"messages","data"=>$data);
	}
}
?>