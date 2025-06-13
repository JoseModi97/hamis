<?php
/**
 * packagemanager.class.php
 * this class loads all the package informations and defines the action path
 * this really helps deploying applications in zephyr and also seperate
 * applications totally from the main zephyr engine
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */
session_start();
class packagemanager
{
	/**
	 * constructor
	 *
	 * @param string 
	 * @return packagemanager
	 */
	public function __construct($package=null, $logout=null)
	{
		if($package==null && empty($_SESSION['__package']))
		{
			throw new Exception("You must supply a package name for the first time", 20001);
		}
		elseif($package!=null) 
		{
			//we got new package
			$_SESSION['__package']= $package;
			$package_definition = "packages/{$package}/package.xml";
			if($logout)
			{
				$package_definition = "../packages/{$package}/package.xml";
			}
			
			$xml = simplexml_load_file($package_definition);

			$_SESSION['__root_template']= $xml->root_template[0]."";
			$_SESSION['__compile'] = $xml->compile[0]."";
			
			// load multiple pre_action_processor
			foreach ($xml->pre_action_processor as $pap)
			{
				$paps[] = $pap.""; 
			}
			if (count($paps)>0)
			$paps = implode(":", $paps);
			
			$_SESSION['__pre_action_processor']=$paps;  //$xml->pre_action_processor[0]."";
			// load multiple javascript files
			foreach ($xml->javascript as $js)
			{
				$jss[] = $js.""; 
			}
			if (count($jss)>0)
			$jss = implode(":", $jss);
			$_SESSION['__javascript']=$jss;
			$_SESSION['__overlib']=$xml->system_packages[0]->overlib[0]."";
			$_SESSION['__jscalendar']=$xml->system_packages[0]->jscalendar[0]."";
			$_SESSION['__title']=$xml->name[0]."";
			$_SESSION['__version']=$xml->version[0]."";
			$_SESSION['__css']=$xml->css[0]."";
			$_SESSION['__root_action']=$xml->root_action[0]."";
			
			// load multiple pre_action_processor
			foreach ($xml->pear as $pear)
			{
				$pears[] = $pear.""; 
			}
			if (count($pears)>0)
			$pears = implode(":", $pears);			
			$_SESSION['__pear'] = $pears;
			// load multiple php files
			foreach ($xml->php as $php)
			{
				$phps[] = $php.""; 
			}
			if (count($phps)>0)
			$phps = implode(":", $phps);
			$_SESSION['__php']=$phps; //$xml->php[0]."";
			$_SESSION['__use_smarty_plugin']=$xml->smarty_plugin[0]."";
			
			// load multiple input filters
			foreach ($xml->input_filter as $if)
			{
				$ifs[] = $if.""; 
			}
			if (count($ifs)>0)
			$ifs = implode(":", $ifs);	
			$_SESSION['__ifs']=$ifs; //$xml->php[0]."";	
			
			// load multiple output filters
			foreach ($xml->output_filter as $of)
			{
				$ofs[] = $of.""; 
			}
			if (count($ofs)>0)
			$ofs = implode(":", $ofs);	
			$_SESSION['__ofs']=$ofs; 		
			
		}
	}
	
	/**
	 * return the root template
	 *
	 * @access public
	 * @return string
	 */
	public function get_root_template()
	{
		return $_SESSION['__root_template'];
	}
	
	/**
	 * return the input filters in a ":" delimited string
	 *
	 * @access public
	 * @return string
	 */
	public function get_input_filters()
	{
		return $_SESSION['__ifs'];
	}	
	
	/**
	 * return the output filters in a ":" delimited string
	 *
	 * @access public
	 * @return string
	 */
	public function get_output_filters()
	{
		return $_SESSION['__ofs'];
	}	
	
	/**
	 * is compilation necessary for this root_template
	 *
	 * @access public
	 * @return boolean
	 */
	public function is_compile_required()
	{
		if($_SESSION['__compile']=='true') return true;
		return false;
	}
	
	/**
	 * return the name of pre_action_processor class
	 * because we must call it's execute method before loading any action
	 * this is extremely usefull when something have to be done before an
	 * action loads. like privilege management. if the execute method returns
	 * true then action loader will load the desired action. otherwise it will
	 * die with a custom error message provided by this class. 
	 * 
	 * @access public
	 * @return string
	 */
	public function get_pre_action_processor()
	{
		return $_SESSION['__pre_action_processor'];
	}
	
	
	/**
	 * return the base path for this application
	 *
	 * @access public
	 * @return string
	 */
	public function get_base_path()
	{
		return "{$_SESSION['__package']}";
	}
	
	/**
	 * return the base path for this application
	 *
	 * @access public
	 * @return string
	 */
	public function get_javascript()
	{
		return "{$_SESSION['__javascript']}";
	}	
	
	/**
	 * return if overlib is needed or not
	 * 
	 * @return boolean
	 */
	public function require_overlib()
	{
		if ($_SESSION['__overlib']=='1')
		return true;
		return false;
	}
	
	/**
	 * return if jscalendar is needed or not
	 * 
	 * @return boolean
	 */
	public function require_jscalendar()
	{
		if ($_SESSION['__jscalendar']=='1')
		return true;
		return false;
	}	

	/**
	 * return package title
	 * 
	 * @return string
	 */
	public function get_title()
	{
		return $_SESSION['__title'];
	}	
	
	/**
	 * return package version
	 * 
	 * @return string
	 */
	public function get_version()
	{
		return $_SESSION['__version'];
	}	

	/**
	 * return stylesheet
	 * 
	 * @return string
	 */
	public function get_stylesheet()
	{
		return $_SESSION['__css'];
	}
	
	/**
	 * return root action
	 * 
	 * @return string
	 */
	public function get_root_action()
	{
		return $_SESSION['__root_action'];
	}	

	/**
	 * return user defined php script
	 * 
	 * @return string
	 */
	public function get_php()
	{
		return $_SESSION['__php'];
	}

	/**
	 * return pear packages
	 * 
	 * @return string
	 */
	public function get_pear_packages()
	{
		return $_SESSION['__pear'];
	}
	
	/**
	 * return whether to use package specific user defined smarty plugins or not 
	 * 
	 * @return boolean
	 */
	public function use_smarty_plugin()
	{
		return $_SESSION['__use_smarty_plugin'];
	}
	
	/**
	 * return the physical path of current package
	 * this is extremely usefull when you want to manipulate file 
	 * specially sqlite database at the current package context
	 *
	 * @return 	string
	 * @since 	beta 2.0
	 */
	public function get_package_path()
	{
		$root_path= dirname(dirname(__FILE__))."/packages/".$this->get_base_path();
		//$root_path = str_replace("\\", "/", $root_path);
		return $root_path;
	}

}
?>