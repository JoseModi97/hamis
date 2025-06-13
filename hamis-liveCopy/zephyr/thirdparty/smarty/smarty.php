<?php
/**
 * smarty.class.php
 * smarty singleton
 *
 * @author 		Hasin Hayder
 * @since 		29th October, 20005
 * @copyright 	LGPL
 * @package 	Zephyr
 * @version 	1.0
 */
class smarty_x
{
	public static $smarty;
	
	/**
	 * constructor
	 *
	 * @return void
	 */
	private function smarty_x(){
		
	}
	
	/**
	 * function new_instance()
	 * this function generates the smarty singleton and return it
	 *
	 * @return object smarty
	 */
	public static function new_instance()
	{
		if (!smarty_x::$smarty)
		{
			smarty_x::$smarty = new Smarty();
			smarty_x::$smarty->template_dir="views";
			smarty_x::$smarty->compile_dir = "temp";
		}
		
		return smarty_x::$smarty;
	}
	
}
?>