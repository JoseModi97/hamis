<?
class sample_output
{
	public function process($content, $action)
	{
		if ("process_output_filter"==$action)
		{
			$data = "<b>Filtered using Output Filter</b> <br/><br/>".$content;
			return $data;
		}
		else
		return $content;
	}
}
?>