<?
class processdata implements action
{
	public $params;
	
	public function execute()
	{
		global $_PARAMS;
		$data = $_PARAMS; //unserialize($this->params);
		$name = $data['name'];
		$password = $data['password'];
		$message = "You submit Name = <b>{$name}</b> and Password = <b>{$password}</b>";
		$output = array("message"=>$message);
		return array("view_file"=>"messages", "data"=>$output);
	}
}
?>