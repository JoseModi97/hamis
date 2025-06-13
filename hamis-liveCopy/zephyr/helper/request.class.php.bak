<?php
/**
 * Request class to populate a domain with the parameters from $_REQUEST array
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */

include_once ("../thirdparty/phplog/log_manager.class.php");

class Request {

	public function Request($array, &$Domain) {
		$cls = new ReflectionClass($Domain);
		$props = $cls->getProperties();
		while ($i < count($props))
		{
			$prop_name = $props[(int) $i]->getName();
			$field_name = $prop_name;
			$Domain->$field_name = ($array[$field_name]);
			$i++;
		}
		
		
	}
}
?>