<?
class image implements action
{
	public $params;
	
	public function execute()
	{
		$image_name = $this->params;
		$data = array("image_name"=>$image_name);
		return array("view_file"=>"images","data"=>$data);
	}
}
?>