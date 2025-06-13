<?php
class sample_input
{
	public function process($params, $action)
	{
		if ("process_using_filter"==$action)
		{
			$data = unserialize($params);
			foreach ($data as &$dt)
			{
				$dt = "Filtered : ".addslashes($dt);
			}

			$data = serialize($data);
			return $data;
		}
		else
		return $params;
	}
}
?>