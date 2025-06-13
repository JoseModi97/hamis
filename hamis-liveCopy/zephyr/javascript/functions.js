/**
 * Javascript helper functions for zephyr
 * 
 * @author 		Hasin Hayder
 * @since 		13th April, 2006
 * @package 	Zephyr
 * @version 	Beta 2.0 
 * @copyright 	LGPL
 */

var smart_div;
var cp = new cpaint();

cp.set_transfer_mode('post');
cp.set_response_type('text');
cp.set_debug(false);

// general AJAX response handler
function response(result) {
	document.getElementById('content').innerHTML = result;
}

// internal response handler from CPAINT
function inner_response(result) {
	document.getElementById('result').innerHTML = result;
}

// routine to clean the result <div>
function clr() {
	document.getElementById('result').innerHTML= ' ';
}

function load_action(action, extra)
{
	cp.call("functions/action_loader_functions.php", 'load_action', response, action, extra);
}

function load_callback_action(action, extra, callback)
{
	cp.call("functions/action_loader_functions.php", 'load_action', callback, action, extra);
}

function load_action_smartly(action, extra, div_name, evaluate_script)
{
	smart_div = div_name;
	//alert(div_name);
	try{
		$(smart_div).innerHTML = "<img src='images/loading.gif'>";
	}
	catch(e)
	{
		
	}

	if (!evaluate_script)
		cp.call("functions/action_loader_functions.php", 'load_action', smart_response, action, extra);
	else
		cp.call("functions/action_loader_functions.php", 'load_action', smart_response_with_script, action, extra);
}

function smart_response(result)
{
	document.getElementById(smart_div).innerHTML = result;
}

function smart_response_with_script(result)
{

	document.getElementById(smart_div).innerHTML = result;
	evaluate_script(result);
}

function load_action_value(action, extra, object_name)
{
	smart_div = object_name;
	cp.call("functions/action_loader_functions.php", 'load_action', smart_value, action, extra);
}

function smart_value(result)
{
	//alert(result);
	document.getElementById(smart_div).value = result;
}

function evaluate_script(script_content)
{
	script_content.evalScripts();
}

//serialization function

/**
* serialize()
* this function serialize a javascript array to pass it to php array()
*
* @param 	array
* @return 	string
* undefined bug fixed 19th nov, 05
*/
function serialize(a)
{
	var counter = 0;
	var vardef = "";
	for (var key in a)
	{
		if (key != "reduce" && key !="last" && key !="toArgString")
		{
			counter = counter +1;
			var length = a[key].length;
			if (length == "undefined")
			length = 1;
			vardef = vardef + "s:" + key.length + ":\"" + key + "\";" + "s:" + length + ":\"" + a[key] + "\";";
		}
	}
	var serialized = "a:" + counter + ":{" + vardef + "}";
	return serialized;
}

/**
* group serialize function
* this function helps for serializing variable data
* and to avoid extra hassle
*
* @return 	string
* @param 	string [variable length]
* 
* modified 23rd march, 06
*/
function group_serialize()
{
	arg_counter = 0;
	arg_length =arguments.length;
	data = new Array();
	var vardef = "";
	while(true)
	{
		var temp = arguments[arg_counter];
		//data[temp] = document.getElementById(temp).value;
		data[temp] = $F(temp);
			
		vardef = vardef + "s:" + temp.length + ":\"" + temp + "\";" + "s:" + data[temp].length + ":\"" + data[temp] + "\";";
		arg_counter=arg_counter+1;
		if (arg_counter==arg_length) break;
	}
	serialized ="a:" + arguments.length + ":{" + vardef + "}"; //serialize(data);
	return serialized;
}

/**
* return the element
*
* @return object
* @param string element id
*/
function ge(item) /*get element */
{
	return document.getElementById(item);
}


/**
* run the internal cron daemon of zephyr which can call package functions at a regular interval
*
*/
function run_cron_action(action, additional, callback, interval)
{
	return setInterval(load_callback_action,interval, action, additional, callback);
}

function stop_cron_action(cron_action_id)
{
	cleareInterval(cron_action_id);
}

function run_cron_function(callback, interval)
{
	return setInterval(callback, interval);
}

function stop_cron_function(cron_function_id)
{
	clearInterval(cron_function_id);
}

/* Cookie management Script [Written by Scott Andrew] */
function getCookie( name ) {
	var start = document.cookie.indexOf( name + "=" );
	var len = start + name.length + 1;
	if ( ( !start ) && ( name != document.cookie.substring( 0, name.length ) ) ) {
		return null;
	}
	if ( start == -1 ) return null;
	var end = document.cookie.indexOf( ";", len );
	if ( end == -1 ) end = document.cookie.length;
	return unescape( document.cookie.substring( len, end ) );
}

function setCookie( name, value, expires, path, domain, secure ) {
	var today = new Date();
	today.setTime( today.getTime() );
	if ( expires ) {
		expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date( today.getTime() + (expires) );
	document.cookie = name+"="+escape( value ) +
		( ( expires ) ? ";expires="+expires_date.toGMTString() : "" ) + //expires.toGMTString()
		( ( path ) ? ";path=" + path : "" ) +
		( ( domain ) ? ";domain=" + domain : "" ) +
		( ( secure ) ? ";secure" : "" );
}

function deleteCookie( name, path, domain ) {
	if ( getCookie( name ) ) document.cookie = name + "=" +
			( ( path ) ? ";path=" + path : "") +
			( ( domain ) ? ";domain=" + domain : "" ) +
			";expires=Thu, 01-Jan-1970 00:00:01 GMT";
}

