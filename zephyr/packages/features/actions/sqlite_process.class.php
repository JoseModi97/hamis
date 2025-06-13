<?
load_db_domain("student");
class sqlite_process implements action
{
	public $params;
	
	public function execute()
	{
		global $_PARAMS;
		$std = auto_fill_domain("student");
		$dao = new DAO($std);
		$query = $dao->insert();
		$data = array("message"=>"Successfully Inserted data");
		return array("view_file"=>"messages","data"=>$data);
	}
}
?>