<?
/**
 * helper routines to load actions. 
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */

//include helper classes
error_reporting(E_ALL ^ (E_WARNING | E_NOTICE) );
include_once("../helper/phploader.class.php");
include_once("general_functions.php");
$pl = new phploader("../");
$pl->add_cpaint();
$pl->add_abstractdbinfo();
$pl->add_DAO();
$pl->add_logger();

$pl->add_requesthandler();
$pl->add_actionhandler();
$pl->add_smarty();
$pl->add_packagemanager();
$pl->add_dbinfo();
$pl->add_php();
$pl->add_PEAR_packages();


//global parameters
global $_PARAMS;
//initialize cpaint server
$cp = new cpaint();

//register functions
$cp->register("load_action");
if ($avoid_cpaint==false)
{
	$cp->start("ISO-8859-1"); //added 23rd March, 06 to solve the bug of passing non printable characters.
}
$avoid_cpaint = false;
//return
$cp->return_data();


/**
 * function load_action()
 * load the action and return the rendered output to CPAINT
 * 
 * @param  string $action
 * @param  string $additional aditional parameters in an array
 * @param  boolean $return determine whether the output should return (true) or suppress
 * @return void
 */
function load_action($action, $additional = null, $return=false)
{
	global $cp;
	global $pl;

	//pre action processor
	$pm = new packagemanager();
	$paps = explode(":",$pm->get_pre_action_processor());
	if (count($paps)>0)
	foreach ($paps as $pap)
	{
		if (!empty($pap))
		{
			include_once("../packages/{$pm->get_base_path()}/helper/{$pap}.class.php");
			$pre_action_processor = new $pap();
			if (!$pre_action_processor->execute($action))
			{
				$action = "error";
				$additional = $pre_action_processor->error_message;
				break;
			}

		}
	}

	//end pre action processor
	
	// initialize the action loader
	$al = new actionloader($action);
	$additional = stripslashes($additional);
	
	//process input filters
	$ifs = split(":",$pm->get_input_filters());
	if (count($ifs)>0)
	{
		foreach ($ifs as $input_filter)
		{
			if (!empty($input_filter))
			{
				$pl->load_filter($input_filter);
				$if = new $input_filter();
				$additional=$if->process($additional, $action);
				//echo $additional;
			}
		}
	}
	
	//pass the addional incloming data to actionloader object and process it
	$al->params = ($additional);
	$al->process();
	// return the rendered view to CPAINT
	$data = $al->render();
    echo $data;
	//process output filters
	$ofs = split(":",$pm->get_output_filters());
	if (count($ofs)>0)
	{
		foreach ($ofs as $output_filter)
		{
			if (!empty($output_filter))
			{
				$pl->load_filter($output_filter);
				$of = new $output_filter();
				$data=$of->process($data, $action);
			}
		}
	}	
	if ($return=='1') return $data;
	$cp->set_data($data);
	return;

}

/**
 * this function helps to load package action from an internal zephyr action
 * 
 * @param  string $action
 * @param  string $additional adtionally parameters in an array
 * @param  boolean $return determine whether the output should return (true) or suppress
 * @return void
 */
function load_action_internally($action, $additional = null, $return=false)
{
	load_action($action, $additional, $return);
}
?>