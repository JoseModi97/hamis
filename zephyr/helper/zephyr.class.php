<?php
/**
 * zephyr controller
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */

//define Error Constants
define("INVALID_PACKAGE_ERROR","PKG404");
//load php package loader
include_once("helper/phploader.class.php");
$pl = new phploader();
$pl->add_packagemanager();
$pl->add_ZephyrException();
global $avoid_cpaint;
class zephyr
{
	public function __construct()
	{
		//constructor
		echo "<html>";
		echo "<head>";
	}

	/**
	 * handle the incoming request and load requested package
	 *
	 * @return void;
	 */
	public function handle_package()
	{
		//global $avoid_cpaint;
		$package = $_GET['package']; //supplied automatically from .htaccess
		if(empty($package)) $package="features";
		if (!is_dir("packages/{$package}"))
		{
			$ex = new ZephyrException("This package '{$package}' is not a valid package", INVALID_PACKAGE_ERROR);
			throw $ex;
		}
		$pm = new packagemanager($package);
		$permalink=$_GET['pl'];
		if ($permalink =='1')
		{
			//well, it is a permalink request.
			//we must load the action_loader inside a dir for path problem

			global $avoid_cpaint;
			$avoid_cpaint = true;
			chdir("helper"); //just a small trick for permalink
			include_once("../functions/action_loader_functions.php"); //assume we are inside a dir
			$action=$_GET['action'];
			if (empty($ac)) $action= $pm->get_root_action();
			$additional = $_GET['ad'];
			$rendered_output = load_action($action,$additional,true);
			echo $rendered_output;
			die("<p><br /> generated using <a href='http://zephyr-php.sourceforge.net'>zephyr ajax based framework for PHP5</a></p>");
		}
	}

	/**
	 * initialize each package
	 *
	 * @return void
	 */
	public function initialize_package()
	{
		$pm = new packagemanager();
		$file = "packages/{$pm->get_base_path()}/helper/initializer.class.php";
		if(file_exists($file))
		{
			include("packages/{$pm->get_base_path()}/helper/initializer.class.php");
			$package_initializer = new  initializer();
			$package_initializer->initiate();
		}
	}

	/**
	 * load the initial template
	 *
	 * @return void
	 */
	public function render()
	{
		$pm = new packagemanager();
		echo "<div id='root_action'>";
		include("packages/{$pm->get_base_path()}/views/{$pm->get_root_template()}");
		echo "</div>";
	}

	/**
	 * load package specific style sheets
	 *
	 * @return void;
	 */
	public function load_stylesheet()
	{
		$pm = new packagemanager();
		$css_file = "packages/{$pm->get_base_path()}/styles/{$pm->get_stylesheet()}.css";
		if (file_exists($css_file))
		{
			echo "<style type='text/css' media='all'>\n";
			echo "@import url('{$css_file}');\n";
			echo "</style>\n";
		}

	}
	/**
	 * load user defined php function file
	 *
	 * @return void;
	 */
	public function load_php()
	{
		$pm = new packagemanager();
		$php_file ="packages/{$pm->get_base_path()}/php/{$pm->get_php()}";
		if (file_exists($php_file))
		include($php_file);

	}

	public function process_root_action()
	{
		$pm = new packagemanager();
		$root_action = $pm->get_root_action();
		$root_template = $pm->get_root_template();
		if (!empty($root_action)){
			echo "<div id='root_action'></div>";
			echo "<script>window.onload=load_action_smartly('{$root_action}','null','root_action',1);</script>";
			echo "</body>";
			echo "</html>";
		}
		elseif(!empty($root_template))
		{
			$this->render();
			echo "</body>";
			echo "</html>";
		}
		else
		{
			die("you must supply atleast &lt;root_template&gt; or &lt;root_action&gt; in your package definition file to initialize the package. ");
		}
	}

	/**
	 * load necessary javascripts for zephyr
	 *
	 * @return void
	 */
	public function load_javascripts()
	{
		$pm = new packagemanager();
		echo "<!-- Zephyr has Builtin Prototype support since 23rd March, 06 --!>";
		echo '<script type="text/javascript" src="javascript/prototype.js"></script>';
		$jss = explode(":",$pm->get_javascript());
		if (count($jss)>0)
		{
			foreach ($jss as $js)
			{
				$js_file = "packages/{$pm->get_base_path()}/javascript/{$js}.js";
				if (file_exists($js_file))
				echo "<script type='text/javascript' src='{$js_file}'></script>";
			}
		}
		$declaration = <<< EOD
		<script type="text/javascript" src="thirdparty/cpaint/cpaint2.inc.compressed.js"></script>
		<script type="text/javascript" src="javascript/functions.js"></script>
		</head>
		<body>
		
EOD;
		echo $declaration;

	}
}
?>
