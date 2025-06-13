<?php
/**
 * load a db_domain model from current package context
 * 
 * @param 	string
 * @since 	beta 2.0
 * @return 	void
 */
function load_db_domain($domain)
{
	$pm = new packagemanager();
	include_once("../packages/{$pm->get_base_path()}/db_domains/{$domain}.php");
}

/**
 * process the global $_PARAMS and automatically load input data into db_domain
 *
 * @param 	string domain model name
 * @since 	beta 2.0
 * @return 	object domain_model
 */
function auto_fill_domain($domain)
{
	global $_PARAMS;
	$db_domain = new $domain();
	new Request($_PARAMS, $db_domain);
	return $db_domain;
}
?>