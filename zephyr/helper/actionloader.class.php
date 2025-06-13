<?
/**
 * common actionloader class, loads actions
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */

session_start();
class actionloader
{

	/**
	 * This is the action name to load
	 * used by constructor()
	 *
	 * @var 	string
	 * @since 	1.0
	 * @access 	private
	 */
	private $action;

	/**
	 * This is the nameof view file
	 *
	 * @var 	string
	 * @since 	1.0
	 * @access 	private
	 */
	private $view;

	/**
	 * this is the variable to determine whther this is an insert action or edit action
	 * coz they will call different handlers. 
	 *
	 * @var 	string
	 * @access 	private
	 * @since 	1.1, 10th Nov, 05
	 */

	private $command;

	/**
	 * this is the data array to pass to template via smarty engine
	 *
	 * @var 	array
	 * @access 	private
	 * @since 	1.1, 10th Nov, 05
	 */

	private $data;

	/**
	 * this is the incoming data from view file. necessary when some additional data are to pass to action
	 * this may contain a serialized javascript array.
	 *
	 * @var 	string
	 * @access 	public
	 * @since 	1.1, 10th Nov, 05
	 */

	public $params;

	/**
	 * instance of smarty 
	 *
	 * @var object
	 * @access private
	 */
	private $temp_smarty;

	/**
	 * PHP5 constructor
	 * 
	 * @param  string $action
	 * @return string rendered output
	 */
	public function __construct($action)
	{
		//echo $action;
		$pm = new packagemanager();
		$action_file = "../packages/{$pm->get_base_path()}/actions/{$action}.class.php";

		if (!file_exists($action_file))
		{
			// that means its an internal zephyr action
			$action_file = "../internal/actions/{$action}.class.php";
		}
		require_once($action_file);
		$this->action = new $action();

		// instantiate a singleton of smarty
		$this->temp_smarty = smarty_x::new_instance();
		// override the smarty templates dir and compiled dir setting, coz its now loading from inside
		$temp_folder_path = "../temp/".$pm->get_base_path();
		if (!file_exists($temp_folder_path))
		{
			mkdir($temp_folder_path);
		}
		$this->temp_smarty->template_dir="../packages/{$pm->get_base_path()}/views";
		$this->temp_smarty->compile_dir = $temp_folder_path;//"../temp";
		if ($pm->use_smarty_plugin())
		{
			$this->temp_smarty->plugins_dir = "../packages/{$pm->get_base_path()}/plugins";
		}

		/**
		 * a global smarty variable which is usable from every view
		 * @since beta 1
		 */
		$this->temp_smarty->assign("base","packages/".$pm->get_base_path());
		$this->temp_smarty->assign("images","packages/".$pm->get_base_path()."/images");
		
		/**
		 * @version 	1.0.2
		 * @since 		23rd March, 06
		 */
		$this->temp_smarty->assign("PACKAGE_ROOT","packages/".$pm->get_base_path());
		$this->temp_smarty->assign("IMAGES","packages/".$pm->get_base_path()."/images");
		$this->temp_smarty->assign("STYLES","packages/".$pm->get_base_path()."/styles");
		$this->temp_smarty->assign("SCRIPTS","packages/".$pm->get_base_path()."/javascripts");
	}

	/**
	 * function render()
	 * this function renders the view and return the html
	 * 
	 * @access public
	 * @return string
	 */
	public function render()
	{
		// asign the data to templates
		$this->temp_smarty->assign("todo", $this->command);
		while (list($key,  $val) = each($this->data))
		{
			$this->temp_smarty->assign($key, $val);
		}
		// perform the rendering
		$view = "{$this->view}.tpl";
		$view_path = $this->temp_smarty->template_dir."/".$view;
		if(!file_exists($view_path))
		{
			// it is an internal view, so grab it from /internal/views directory
			$this->temp_smarty->template_dir="../internal/views";
		}


		$output = $this->temp_smarty->fetch($view);
		return $output;
	}

	/**
	 * this function process the action, sets additional input parameters and retrieve assignable data for smarty engine
	 *
	 * @access public	
	 * @return void
	 * @since 1.1, 10th Nov, 05
	 */

	public function process()
	{
		global $_PARAMS;
		$__unserialized_params = unserialize($this->params);
		if (is_array($__unserialized_params))
		{
			$_PARAMS = $__unserialized_params;
		}
		else
		{
			$_PARAMS = $this->params;
		}
		// pass the extra input data to action object
		$this->action->params=$this->params;
		// get both action and command
		$view_command_data = $this->action->execute();
		// name of the view file
		$this->view = $view_command_data['view_file'];
		//the command to perform, either 'insert', 'update' or 'void'
		$this->command = $view_command_data['command'];
		// extra output data in an array
		$this->data = $view_command_data['data'];
	}

}
?>