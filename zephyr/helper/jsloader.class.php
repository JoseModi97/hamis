<?php
/**
 * this class loads javascript files for a specific package
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */

class jsloader
{
	/**
	 * the root of the project, usually null
	 *
	 * @var string
	 * @access public
	 */
	public $base = "";
	
	/**
	 * load javascript files for jscalendar package
	 * 
	 * @return void;
	 * @access public
	 */
	public function add_jscalendar()
	{
		echo "<script type='text/javascript' src='{$this->base}thirdparty/jscalendar/calendar.js'></script>";
		echo "<script type='text/javascript' src='{$this->base}thirdparty/jscalendar/lang/calendar-en.js'></script>";
		return;
	}
	
	/**
	 * add overlib package
	 * 
	 * @return void
	 * @access public
	 */
	public function add_overlib()
	{
		echo "<script type='text/javascript' src='{$this->base}thirdparty/overlib/overlib.js'><!-- overLIB (c) Erik Bosrup --></script>";
		return;
	}
	
	/**
	 * add fValidate package
	 *
	 * @return void
	 * @access public
	 */
	public function add_fvalidate()
	{
		echo "<script type='text/javascript' src='{$this->base}thirdparty/fValidate/fValidate.config.js'></script>";
		echo "<script type='text/javascript' src='{$this->base}thirdparty/fValidate/fValidate.core.js'></script>";
		echo "<script type='text/javascript' src='{$this->base}thirdparty/fValidate/fValidate.lang-enUS.js'></script>";
		echo "<script type='text/javascript' src='{$this->base}thirdparty/fValidate/fValidate.validators.js'></script>";
		return;
	}
	
	/**
	 * add cpaint libraries
	 * 
	 * @return void
	 * @access public
	 */
	public function add_cpaint()
	{
		echo "<script type='text/javascript' src='{$this->base}thirdparty/cpaint/cpaint2.inc.compressed.js'></script>";
		echo "<script type='text/javascript' src='{$this->base}javascript/functions.js'></script>";
		return;
	}
}

?>