var id;
function add()
{
	serialized = group_serialize("a","b");
	load_action_smartly('add', serialized,'root_action');
}

function load_action1()
{
	load_action_smartly("action1","","test_result")
}

function load_action2()
{
	load_action_smartly("action2","","test_result")
}

function load_image()
{
	load_action_smartly("image","zephyr2.jpg","test_result")
}

function data_input()
{
	load_action_smartly("datainput","","test_result")
}

function submit_data()
{
	serialized = group_serialize("name","password")
	load_action_smartly("processdata",serialized,"test_result")
}

function process_using_filter()
{
	serialized = group_serialize("name","password")
	load_action_smartly("process_using_filter",serialized,"test_result")
}
function input_filter()
{
	load_action_smartly("sample_form","","test_result")
}

function output_filter()
{
	load_action_smartly("process_output_filter","","test_result")
}

function execute_embedded_script()
{
	load_action_smartly("execute_script","","test_result", true)
}

function sqlite_process()
{
	serialized = group_serialize("name","roll", "age");
	load_action_smartly('sqlite_process',serialized ,'test_result')
	
}

function my_cron_action()
{
	load_action_smartly('report','','test_result');
}

function start_cron_action()
{
	id=run_cron_function(my_cron_action,5000);
}