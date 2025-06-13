<?php
/**
 * the startup page of zephyr
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */

include_once("helper/zephyr.class.php");
try{
	$zephyr = new zephyr();
	$zephyr->handle_package();
	$zephyr->load_stylesheet();
	$zephyr->load_javascripts();
	$zephyr->initialize_package();

	if ($_GET['pl']!='1')
	{
		$zephyr->process_root_action();
	}
}
catch (Exception $ex)
{
	if ($ex->getCode()==INVALID_PACKAGE_ERROR) //invalid package
	echo $ex->getMessage();
}
?>