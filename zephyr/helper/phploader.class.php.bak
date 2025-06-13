<?
/**
 * this class loads php files from a specific package
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */

class phploader
{
	/**
	 * the root of the project, usually null
	 *
	 * @var string
	 * @access public
	 */
	public $base;

	/**
	 * constructor
	 *
	 * @param 	string basepath
	 * @return 	phploader
	 */
	public function phploader($base = "")
	{
		$this->base = $base;
	}

	/**
	 * load php files necessary for DAO
	 * 
	 * @return void;
	 * @access public
	 */
	public function add_DAO()
	{
		include_once("{$this->base}helper/DAO.php");
		include_once("{$this->base}helper/DatabaseConnector.class.php");
		return;
	}

	/**
	 * load php files necessary for request handler
	 * 
	 * @return void
	 * @access public
	 */
	public function add_requesthandler()
	{
		include_once("{$this->base}helper/request.class.php");
		return;
	}

	/**
	 * load php files necessary for logging
	 *
	 * @return void
	 * @access public
	 */
	public function add_logger()
	{
		include_once("{$this->base}thirdparty/phplog/logger.class.php");
		include_once("{$this->base}thirdparty/phplog/log_manager.class.php");
		return;
	}

	/**
	 * load php files necessary for cpaint
	 * 
	 * @return void
	 * @access public
	 */
	public function add_cpaint()
	{
		include_once("{$this->base}thirdparty/cpaint/cpaint2.inc.php");
		return;
	}
	
	/**
	 * load php files necessary for action handling
	 * 
	 * @return void
	 * @access public
	 */
	public function add_actionhandler()
	{
		include_once("{$this->base}helper/actionloader.class.php");
		include_once("{$this->base}interfaces/action.interface.php");
		return;
	}
	

	
	/**
	 * load php files necessary for smarty
	 * 
	 * @return void
	 * @access public
	 */
	public function add_smarty()
	{
		include_once("{$this->base}thirdparty/smarty/smarty.php");
		include_once("{$this->base}thirdparty/smarty/libs/smarty.class.php");
		return;
	}	

	/**
	 * load php files necessary for adodb
	 * 
	 * @return void
	 * @access public
	 */
	public function add_adodb()
	{
		include_once("{$this->base}thirdparty/adodb/adodb.inc.php");
		return;
	}
	

	/**
	 * load php files necessary for privilege check
	 * 
	 * @return void
	 * @access public
	 */
	public function add_privilege()
	{
		include_once("{$this->base}helper/privilege_singleton.class.php");
		return;
	}	

	/**
	 * load php files necessary for package management
	 * 
	 * @return void
	 * @access public
	 */
	public function add_packagemanager()
	{
		include_once("{$this->base}helper/packagemanager.class.php");
		return;
	}	
	
	/**
	 * load dbinfo relevant to this package
	 * 
	 * @return void
	 * @access public
	 */
	public function add_dbinfo()
	{
		$pm = new packagemanager();
		$file = "{$this->base}packages/{$pm->get_base_path()}/helper/dbinfo.class.php";
		if (file_exists($file))
		include_once($file);
		return;
	}	

	/**
	 * add customized php functions
	 *
	 * @return void
	 * @access public
	 */
	public function add_php()
	{
		$pm = new packagemanager();
		$phps = explode(":",$pm->get_php());
		if (count($phps)>0)
		foreach ($phps as $php)
		{
			$file = "{$this->base}packages/{$pm->get_base_path()}/php/{$php}.php";
			if (file_exists($file))
			include_once($file);
		}
		
	}	
	
	/**
	 * load pear packages
	 */
	public function add_PEAR_packages()
	{
		$pm = new packagemanager();
		$phps = explode(":",$pm->get_pear_packages());
		if (count($phps)>0)
		foreach ($phps as $php)
		{
			$file = "{$php}.php";
			if (file_exists($file))
			include_once($file);
		}		
	}
	
	/**
	 * add abstractdbinfo class
	 */
	public function add_abstractdbinfo() 
	{
		include_once("{$this->base}abstract/abstractdbinfo.abstract.php");
	}
	
	/**
	 * add custom ZephyrException.class.php;
	 * @access public
	 */
	public function add_ZephyrException()
	{
		include_once("{$this->base}helper/ZephyrException.class.php");
	}
	
	/**
	 * include the filter files 
	 *
	 * @param string $filter
	 * @access public
	 * @since beta 2.0
	 */
	public function load_filter($filter)
	{
		$pm = new packagemanager();
		$filter_file = "{$this->base}packages/{$pm->get_base_path()}/filters/{$filter}.class.php";
		include_once($filter_file);
	}
	
}	

?>