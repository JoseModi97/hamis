<?php
session_start();
//ob_start("ob_gzhandler");
//Include Files @0-7C1B9599
//E_ERROR E_ALL
define("RelativePath" ,$_SERVER['DOCUMENT_ROOT'] ."/swa") ;
error_reporting(E_ALL &~E_NOTICE); # report all errors
//error_reporting(E_ERROR); # report all errors

ini_set("display_errors", "2"); # but do not echo the errors
define('ADODB_ERROR_LOG_TYPE',3);
define('ADODB_ERROR_LOG_DEST',$_SERVER['DOCUMENT_ROOT']. '/swa/errors.log');
//include($_SERVER['DOCUMENT_ROOT'].'/adodb/adodb-exceptions.inc.php');
include($_SERVER['DOCUMENT_ROOT'].'/adodb/adodb-errorhandler.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/adodb/adodb-pager.inc.php');
include($_SERVER['DOCUMENT_ROOT']. '/adodb/adodb.inc.php');
include($_SERVER['DOCUMENT_ROOT'].'/adodb/tohtml.inc.php');
//include(RelativePath . "/Classes.php");
$ADODB_CACHE_DIR = RelativePath .  '/tmp';


/*
error_reporting(E_ALL); # report all errors
PutEnv("ORACLE_SID=webdb");
PutEnv("ORACLE_HOME=/appl/opt/oracle/product/9.2.0");
PutEnv("TNS_ADMIN=/appl/opt/oracle/product/9.2.0/network/admin");
PutEnv("NLS_TERRITORY=AMERICA");
PutEnv("NLS_LANGUAGE=AMERICAN");
PutEnv("NLS_NCHAR_CHARACTERSET=AL16UTF16");
PutEnv("NLS_RDBMS_VERSION=9.2.0.1.0");
*/
//$tnsName='(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 10.2.21.1)(PORT = 1521))) (CONNECT_DATA = (SERVICE_NAME = proddb)))' ;

	if($_SERVER["HTTP_HOST"] == '10.2.21.87' || $_SERVER["HTTP_HOST"]=='smis.uonbi.ac.ke'){
		$vServerIP = "proddb.uonbi.ac.ke"; $vServerSID = "proddb"; $DbPort = 1521 ;
	}else{
		$vServerIP = "umis2.uonbi.ac.ke"; $vServerSID = "webdb"; $DbPort = 1521 ;
	}
		
$tnsName = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS =  (PROTOCOL = TCP)(Host = $vServerIP)(Port = $DbPort))) CONNECT_DATA = (SID = $vServerSID)))" ;
		  
//putenv("ORACLE_HOME=/opt/oracle/product/9.2.0");
//putenv("ORACLE_SID=WEBDB");

$dsn = 'oci8://'.CCGetUserID().':'.CCGetUserPassword().'@'.$tnsName ;
//$db = NewADOConnection('oci8');

$LoggedOn = CCGetLogonStatus();
$loggedonUser = 'webuser';
$logonPassword = 'webuser';

//echo "<h1><font color=red>Just about ot loggin </font></h1>";

if ($LoggedOn)
{
	if($_SERVER["HTTP_HOST"] == '10.2.21.87' || $_SERVER["HTTP_HOST"]=='smis.uonbi.ac.ke')
		$dsn = "oci8://$loggedonUser:$logonPassword@proddb.uonbi.ac.ke/proddb"; 
	else
		$dsn = "oci8://$loggedonUser:$logonPassword@umis2.uonbi.ac.ke/webdb"; 
	$db = NewADOConnection($dsn) or die ($db->ErrorMsg());
	//$db->Connect($tnsName,$loggedonUser,$logonPassword);
}
if(is_object($db))
if (($db->IsConnected()) && (LoggedOn))
{
	$rs = $db->execute("ALTER SESSION SET NLS_DATE_FORMAT = 'DD-MON-YYYY'" );
	//$rs = $db->execute("SELECT USER FROM DUAL");
	$db->SetFetchMode(ADODB_FETCH_ASSOC);
}
	//echo "<h1><font color=red>after loggin </font></h1>";
//Initialize Common Variables @0-1C58E271
define("TemplatePath", "./");
define("ServerURL", "http://smis.uonbi.ac.ke/swa/");
define("SecureURL", "");
$ShortWeekdays = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
$Weekdays = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
$ShortMonths =  array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
$Months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
define("ccsInteger", 1);
define("ccsFloat", 2);
define("ccsText", 3);
define("ccsDate", 4);
define("ccsBoolean", 5);
define("ccsMemo", 6);

define("ccsGet", 1);
define("ccsPost", 2);

define("ccsTimestamp", 0);
define("ccsYear", 1);
define("ccsMonth", 2);
define("ccsDay", 3);
define("ccsHour", 4);
define("ccsMinute", 5);
define("ccsSecond", 6);
define("ccsMilliSecond", 7);
define("ccsAmPm", 8);
define("ccsShortMonth", 9);
define("ccsFullMonth", 10);
define("ccsWeek", 11);
define("ccsGMT", 12);
define("ccsAppropriateYear", 13);
//End Initialize Common Variables




 


//CCToHTML @0-93F44B0D
function CCToHTML($Value)
{
  return htmlspecialchars($Value);
}
//End CCToHTML

//CCToURL @0-88FAFE26
function CCToURL($Value)
{
  return urlencode($Value);
}
//End CCToURL

//CCGetEvent @0-C548DA85
function CCGetEvent($events, $event_name)
{
  $result = true;
  $function_name = (is_array($events) && isset($events[$event_name])) ? $events[$event_name] : "";
  if($function_name && function_exists($function_name))
	$result = call_user_func ($function_name);
  return $result;  
}
//End CCGetEvent

//CCGetValueHTML @0-0180C79D
function CCGetValueHTML($db, $fieldname)
{
  return htmlspecialchars($db->f($fieldname));
}
//End CCGetValueHTML

//CCGetValue @0-EAF96C23
function CCGetValue($db, $fieldname)
{
  return $db->f($fieldname);
}
//End CCGetValue

//CCGetSession @0-9BBC6D71
function CCGetSession($parameter_name)
{
	global $HTTP_SESSION_VARS;
	return isset($HTTP_SESSION_VARS[$parameter_name]) ? $HTTP_SESSION_VARS[$parameter_name] : "";
}
//End CCGetSession

//CCSetSession @0-0F088E96
function CCSetSession($param_name, $param_value)
{
	global $HTTP_SESSION_VARS;
	global ${$param_name};
	if(session_is_registered($param_name))
		session_unregister($param_name);
	${$param_name} = $param_value;
	session_register($param_name);
	$HTTP_SESSION_VARS[$param_name] = $param_value;
}
//End CCSetSession

//CCGetCookie @0-705AF8CB
function CCGetCookie($parameter_name)
{
	global $HTTP_COOKIE_VARS;
	return isset($HTTP_COOKIE_VARS[$parameter_name]) ? $HTTP_COOKIE_VARS[$parameter_name] : "";
}
//End CCGetCookie

//CCSetCookie @0-1E0B074A
function CCSetCookie($parameter_name, $param_value)
{
  setcookie ($parameter_name, $param_value, time() + 3600 * 24 * 366);  
}
//End CCSetCookie

//CCStrip @0-34A0B0A2
function CCStrip($value)
{
  if(get_magic_quotes_gpc() != 0)
  {
	if(is_array($value))  
	  for($j = 0; $j < sizeof($value); $j++)
		$value[$j] = stripslashes($value[$j]);
	else
	  $value = stripslashes($value);
  }
  return $value;
}
//End CCStrip

//CCGetParam @0-7BF95E76
function CCGetParam($parameter_name, $default_value = "")
{
	global $HTTP_POST_VARS;
	global $HTTP_GET_VARS;
	$parameter_value = "";
	if(isset($HTTP_POST_VARS[$parameter_name]))
		$parameter_value = CCStrip($HTTP_POST_VARS[$parameter_name]);
	else if(isset($HTTP_GET_VARS[$parameter_name]))
		$parameter_value = CCStrip($HTTP_GET_VARS[$parameter_name]);
	else
		$parameter_value = $default_value;
	return trim($parameter_value);
}
//End CCGetParam

//CCGetParam @0-3E113D15
function CCGetParamStartsWith($prefix)
{
	global $HTTP_POST_VARS;
	global $HTTP_GET_VARS;
	$parameter_name = "";
	foreach($HTTP_POST_VARS as $key => $value) {
		if(preg_match ("/^" . $prefix . "_\d+$/i", $key)) {
			$parameter_name = $key;
			break;
		}
	}
	if($parameter_name === "") {
		foreach($HTTP_GET_VARS as $key => $value) {
			if(preg_match ("/^" . $prefix . "_\d+$/i", $key)) {
				$parameter_name = $key;
				break;
			}
		}
	}
	return $parameter_name;
}
//End CCGetParam

//CCGetFromPost @0-EF0A789E
function CCGetFromPost($parameter_name, $default_value = "")
{
	global $HTTP_POST_VARS;
	return isset($HTTP_POST_VARS[$parameter_name]) ? CCStrip($HTTP_POST_VARS[$parameter_name]) : $default_value;
}
//End CCGetFromPost

//CCGetFromGet @0-F3558ED3
function CCGetFromGet($parameter_name, $default_value = "")
{
	global $HTTP_GET_VARS;
	return isset($HTTP_GET_VARS[$parameter_name]) ? CCStrip($HTTP_GET_VARS[$parameter_name]) : $default_value;
}
//End CCGetFromGet

//CCToSQL @0-422F5B92
function CCToSQL($Value, $ValueType)
{
  if(!strlen($Value))
  {
	return "NULL";
  }
  else
  {
	if($ValueType == ccsInteger || $ValueType == ccsFloat)
	{
	  return doubleval(str_replace(",", ".", $Value));
	}
	else
	{
	  return "'" . str_replace("'", "''", $Value) . "'";
	}
  }
}
//End CCToSQL

//CCDLookUp @0-23CF9CA0
function CCDLookUp($field_name, $table_name, $where_condition, $db)
{
  $sql = "SELECT " . $field_name . ($table_name ? " FROM " . $table_name : "") . ($where_condition ? " WHERE " . $where_condition : "");
  return CCGetDBValue($sql, $db);
}
//End CCDLookUp

//CCGetDBValue @0-A9E11F29
function CCGetDBValue($sql, $db)
{
  $db->query($sql);
  $dbvalue = $db->next_record() ? $db->f(0) : "";
  return $dbvalue;  
}
//End CCGetDBValue

//CCGetListValues @0-140A4942
function CCGetListValues($db, $sql, $where = "", $order_by = "", $bound_column = "", $text_column = "", $dbformat = "", $datatype = "", $errorclass = "", $fieldname = "")
{
	$values = "";
	if(!strlen($bound_column))
		$bound_column = 0;
	if(!strlen($text_column))
		$text_column = 1;
	if(strlen($where))
		$sql .= " WHERE " . $where;
	if(strlen($order_by))
		$sql .= " ORDER BY " . $order_by;
	$db->query($sql);
	if ($db->next_record())
	{
		do
		{
			$bound_column_value = $db->f($bound_column);
			list($bound_column_value, $errorclass) = CCParseValue($bound_column_value, $dbformat, $datatype, $errorclass, $fieldname);
			$values[] = array($bound_column_value, $db->f($text_column));
		} while ($db->next_record());
	}
	$db->close();
	$result = ($errorclass == "") ? $values : array($values, $errorclass);
	return $result;
}

//End CCGetListValues

//CCParseValue @0-20E4BC5B
  function CCParseValue($ParsingValue, $Format, $DataType, $ErrorClass, $FieldName)
  {
	$varResult = "";
	if(CCCheckValue($ParsingValue, $DataType))
	  $varResult = $ParsingValue;
	else if(strlen($ParsingValue))
	{
	  switch ($DataType)
	  {
		case ccsDate:
		  $DateValidation = true;
		  if (CCValidateDateMask($ParsingValue, $Format)) {
			$varResult = CCParseDate($ParsingValue, $Format);
			if(!CCValidateDate($varResult)) {
			  $DateValidation = false;
			  $varResult = "";
			}
		  } else {
			$DateValidation = false;
		  }
		  if(!$DateValidation && $ErrorClass->Count() == 0) {
			if (is_array($Format)) {
			  $FormatString = join("", $Format);
			  $ErrorClass->addError("The value in field " . $FieldName . " is not valid. Use the following format: " . $FormatString . ".");
			} else {
			  $ErrorClass->addError("The value in field " . $FieldName . " is not valid.");
			}
		  }
		  break;
		case ccsBoolean:
		  if (CCValidateBoolean($ParsingValue, $Format)) {
			$varResult = CCParseBoolean($ParsingValue, $Format);
		  } else if($ErrorClass->Count() == 0) {
			if (is_array($Format)) {
			  $FormatString = CCGetBooleanFormat($Format);
			  $ErrorClass->addError("The value in field " . $FieldName . " is not valid. Use the following format: " . $FormatString . ".");
			} else {
			  $ErrorClass->addError("The value in field " . $FieldName . " is not valid.");
			}
		  }
		  break;
		case ccsInteger:
		  if (CCValidateNumber($ParsingValue, $Format))
			$varResult = CCParseInteger($ParsingValue, $Format);
		  else if($ErrorClass->Count() == 0)
			$ErrorClass->addError("The value in field " . $FieldName . " is not valid.");
		  break;
		case ccsFloat:
		  if (CCValidateNumber($ParsingValue, $Format))
			$varResult = CCParseFloat($ParsingValue, $Format);
		  else if($ErrorClass->Count() == 0)
			$ErrorClass->addError("The value in field " . $FieldName . " is not valid.");
		  break;
		case ccsText:
		case ccsMemo:
		  $varResult = strval($ParsingValue);
		  break;
	  }
	}

	return array($varResult, $ErrorClass);
  }

//End CCParseValue

//CCFormatValue @0-14D74CBF
  function CCFormatValue($Value, $Format, $DataType)
  {
	switch($DataType)
	{
	  case ccsDate:
		$Value = CCFormatDate($Value, $Format);
		break;
	  case ccsBoolean:
		$Value = CCFormatBoolean($Value, $Format);
		break;
	  case ccsInteger:
	  case ccsFloat:
		$Value = CCFormatNumber($Value, $Format);
		break;
	  case ccsText:
	  case ccsMemo:
		$Value = strval($Value);
		break;
	}
	return $Value;
  }

//End CCFormatValue

//CCBuildSQL @0-AD00EEB4
function CCBuildSQL($sql, $where = "", $order_by = "")
{
	if(strlen($where))
		$sql .= " WHERE " . $where;
	if(strlen($order_by))
		$sql .= " ORDER BY " . $order_by;
	return $sql;
}

//End CCBuildSQL

//CCGetRequestParam @0-1C3CB87C
function CCGetRequestParam($ParameterName, $Method)
{
	global $HTTP_POST_VARS;
	global $HTTP_GET_VARS;
	$ParameterValue = "";
	if($Method == ccsGet && isset($HTTP_GET_VARS[$ParameterName]))
		$ParameterValue = CCStrip($HTTP_GET_VARS[$ParameterName]);
	else if($Method == ccsPost && isset($HTTP_POST_VARS[$ParameterName]))
		$ParameterValue = CCStrip($HTTP_POST_VARS[$ParameterName]);
	return $ParameterValue;
}
//End CCGetRequestParam

//CCGetQueryString @0-F67D7840
function CCGetQueryString($CollectionName, $RemoveParameters)
{
	global $HTTP_POST_VARS;
	global $HTTP_GET_VARS;
	$querystring = "";
	$postdata = "";
	if($CollectionName == "Form")
		$querystring = CCCollectionToString($HTTP_POST_VARS, $RemoveParameters);
	else if($CollectionName == "QueryString")
		$querystring = CCCollectionToString($HTTP_GET_VARS, $RemoveParameters);
	else if($CollectionName == "All")
	{
		$querystring = CCCollectionToString($HTTP_GET_VARS, $RemoveParameters);
		$postdata = CCCollectionToString($HTTP_POST_VARS, $RemoveParameters);
		if(strlen($postdata) > 0 && strlen($querystring) > 0)
			$querystring .= "&" . $postdata;
		else
			$querystring .= $postdata;
	}
	else
		die("1050: Common Functions. CCGetQueryString Function. " .
			"The CollectionName contains an illegal value.");
	return $querystring;
}
//End CCGetQueryString

//CCCollectionToString @0-883F2B49
function CCCollectionToString($ParametersCollection, $RemoveParameters)
{
  $Result = ""; 
  if(is_array($ParametersCollection))
  {
	reset($ParametersCollection);
	foreach($ParametersCollection as $ItemName => $ItemValues)
	{
	  $Remove = false;
	  if(is_array($RemoveParameters))
	  {
		for($I = 0; $I < sizeof($RemoveParameters); $I++)
		{
		  if($RemoveParameters[$I] == $ItemName)
		  {
			$Remove = true;
			break;
		  }
		}
	  }
	  if(!$Remove)
	  {
		if(is_array($ItemValues))
		  for($J = 0; $J < sizeof($ItemValues); $J++)
			$Result .= "&" . $ItemName . "[]=" . urlencode(CCStrip($ItemValues[$J]));
		else
		   $Result .= "&" . $ItemName . "=" . urlencode(CCStrip($ItemValues));
	  }
	}
  }

  if(strlen($Result) > 0)
	$Result = substr($Result, 1);
  return $Result;
}
//End CCCollectionToString

//CCMergeQueryStrings @0-88AB1C5F
function CCMergeQueryStrings($LeftQueryString, $RightQueryString = "")
{
  $QueryString = $LeftQueryString; 
  if($QueryString === "")
	$QueryString = $RightQueryString;
  else
	$QueryString .= "&" . $RightQueryString;
  
  return $QueryString;
}
//End CCMergeQueryStrings

//CCAddParam @0-C2CDA2BB
function CCAddParam($querystring, $ParameterName, $ParameterValue)
{
	global $HTTP_GET_VARS;
	$Result = "";
	$querystring = "&" . $querystring;
	$CurrentParameterValue = isset($HTTP_GET_VARS[$ParameterName]) ? $HTTP_GET_VARS[$ParameterName] : "";
	$Result = str_replace("&" . $ParameterName . "=" . urlencode($CurrentParameterValue), "", $querystring);
	$Result .= "&" . $ParameterName . "=" . urlencode($ParameterValue);
	$Result = str_replace("&&", "&", $Result);
	if (substr($Result, 0, 1) == "&")
		$Result = substr($Result, 1);
	return $Result;
}
//End CCAddParam

//CCRemoveParam @0-9355EFB5
function CCRemoveParam($querystring, $ParameterName)
{
	global $HTTP_GET_VARS;
	$Result = "";
	$Result = str_replace($ParameterName . "=" . urlencode($HTTP_GET_VARS[$ParameterName]), "", $querystring);
	$Result = str_replace("&&", "&", $Result);
	if (substr($Result, 0, 1) == "&")
		$Result = substr($Result, 1);
	return $Result;
}
//End CCRemoveParam

//CCGetOrder @0-27B4AC18
function CCGetOrder($DefaultSorting, $SorterName, $SorterDirection, $MapArray)
{
  if(is_array($MapArray) && isset($MapArray[$SorterName]))
	if(strtoupper($SorterDirection) == "DESC")
	  $OrderValue = ($MapArray[$SorterName][1] != "") ? $MapArray[$SorterName][1] : $MapArray[$SorterName][0] . " DESC";
	else
	  $OrderValue = $MapArray[$SorterName][0];
  else
	$OrderValue = $DefaultSorting;

  return $OrderValue;
}
//End CCGetOrder

//CCGetDateArray @0-F86CA386
function CCGetDateArray($timestamp = "")
{
  $DateArray = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
  if(!strlen($timestamp) && !is_int($timestamp)) {
	$timestamp = time();
  }

  $DateArray[ccsTimestamp] = $timestamp;
  $DateArray[ccsYear] = date("Y", $timestamp);
  $DateArray[ccsMonth] = date("n", $timestamp);
  $DateArray[ccsDay] = date("j", $timestamp);
  $DateArray[ccsHour] = date("G", $timestamp);
  $DateArray[ccsMinute] = date("i", $timestamp);
  $DateArray[ccsSecond] = date("s", $timestamp);

  return $DateArray;
}
//End CCGetDateArray

//CCFormatDate @0-788221F6
function CCFormatDate($DateToFormat, $FormatMask)
{
  global $ShortWeekdays;
  global $Weekdays;
  global $ShortMonths;
  global $Months;

  if(!is_array($DateToFormat) && strlen($DateToFormat))
	$DateToFormat = CCGetDateArray($DateToFormat);

  if(is_array($FormatMask) && is_array($DateToFormat))
  {
	$masks = array(
	  "GeneralDate" => "n/j/y, h:i:s A", "LongDate" => "l, F j, Y",
	  "ShortDate" => "n/j/y", "LongTime" => "g:i:s A",
	  "ShortTime" => "H:i", "d" => "j", "dd" => "d",
	  "m" => "n", "mm" => "m", 
	  "h" => "g", "hh" => "h", "H" => "G", "HH" => "H",
	  "nn" => "i", "ss" => "s", "AM/PM" => "A", "am/pm" => "a"
	);
	$FormattedDate = "";
	for($i = 0; $i < sizeof($FormatMask); $i++)
	{
	  if(isset($masks[$FormatMask[$i]]))
	  {
		$FormattedDate .= date($masks[$FormatMask[$i]], $DateToFormat[ccsTimestamp]);
	  }
	  else
	  {
		switch ($FormatMask[$i])
		{
		  case "yy": 
			$FormattedDate .= substr($DateToFormat[ccsYear], 2);
			break;
		  case "yyyy": 
			$FormattedDate .= $DateToFormat[ccsYear];
			break;
		  case "ddd": 
			$FormattedDate .= $ShortWeekdays[date("w", $DateToFormat[ccsTimestamp])];
			break;
		  case "dddd": 
			$FormattedDate .= $Weekdays[date("w", $DateToFormat[ccsTimestamp])];
			break;
		  case "w": 
			$FormattedDate .= (date("w", $DateToFormat[ccsTimestamp]) + 1);
			break;
		  case "ww": 
			$FormattedDate .= ceil((6 + date("z", $DateToFormat[ccsTimestamp]) - date("w", $DateToFormat[ccsTimestamp])) / 7);
			break;
		  case "mmm": 
			$FormattedDate .= $ShortMonths[date("n", $DateToFormat[ccsTimestamp]) - 1];
			break;
		  case "mmmm":
			$FormattedDate .= $Months[date("n", $DateToFormat[ccsTimestamp]) - 1];
			break;
		  case "q":
			$FormattedDate .= ceil(date("n", $DateToFormat[ccsTimestamp]) / 3);
			break;
		  case "y":
			$FormattedDate .= (date("z", $DateToFormat[ccsTimestamp]) + 1);
			break;
		  case "n": 
			$FormattedDate .= intval(date("i", $DateToFormat[ccsTimestamp]));
			break;
		  case "s":
			$FormattedDate .= intval(date("s", $DateToFormat[ccsTimestamp]));
			break;
		  case "S": 
			$FormattedDate .= $DateToFormat[ccsMilliSecond];
			break;
		  case "A/P":
			$am = date("A", $DateToFormat[ccsTimestamp]);
			$FormattedDate .= $am[0];
			break;
		  case "a/p":
			$am = date("a", $DateToFormat[ccsTimestamp]);
			$FormattedDate .= $am[0];
			break;
		  case "GMT":
			$gmt = date("Z", $DateToFormat[ccsTimestamp]) / (60 * 60);
			if($gmt >= 0) $gmt = "+" . $gmt;
			$FormattedDate .= $gmt;
			break;
		  default:
			$FormattedDate .= $FormatMask[$i];
			break;
		}
	  }
	}
  }
  else
  {
	$FormattedDate = "";
  }
  return $FormattedDate;
}
//End CCFormatDate

//CCValidateDate @0-815AEC07
function CCValidateDate($ValidatingDate)
{
  $IsValid = true;
  if(is_array($ValidatingDate) && 
	$ValidatingDate[ccsMonth] != 0 && 
	$ValidatingDate[ccsDay] != 0 && 
	$ValidatingDate[ccsYear] != 0) 
  {
	$IsValid = checkdate($ValidatingDate[ccsMonth], $ValidatingDate[ccsDay], $ValidatingDate[ccsYear]);
  }

  return $IsValid;
}
//End CCValidateDate

//CCValidateDateMask @0-6A1F5673
function CCValidateDateMask($ValidatingDate, $FormatMask)
{
  $IsValid = true;
  if(is_array($FormatMask) && strlen($ValidatingDate))
  {
	$RegExp = CCGetDateRegExp($FormatMask);
	$IsValid = preg_match($RegExp[0], $ValidatingDate, $matches);
  }

  return $IsValid;
}
//End CCValidateDateMask

//CCParseDate @0-88C388A8
function CCParseDate($ParsingDate, $FormatMask)
{
  global $ShortMonths;
  global $Months;

  if(is_array($FormatMask) && strlen($ParsingDate))
  {
	$DateArray = array(0, "00", 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	$RegExp = CCGetDateRegExp($FormatMask);
	$IsValid = preg_match($RegExp[0], $ParsingDate, $matches);
	for($i = 1; $i < sizeof($matches); $i++)
	  $DateArray[$RegExp[$i]] = $matches[$i];

	if($DateArray[ccsMonth] == 0 && ($DateArray[ccsFullMonth] != strval(0) || $DateArray[ccsShortMonth] != strval(0)))
	{
	  if($DateArray[ccsFullMonth] != strval(0))
		$DateArray[ccsMonth] = CCGetIndex($Months, $DateArray[ccsFullMonth], true) + 1;
	  else if($DateArray[ccsShortMonth] != strval(0))
		$DateArray[ccsMonth] = CCGetIndex($ShortMonths, $DateArray[ccsShortMonth], true) + 1;
	}

	if(intval($DateArray[ccsDay]) == 0) { $DateArray[ccsDay] = 1; }

	if($DateArray[ccsHour] < 12 && strtoupper($DateArray[ccsAmPm][0]) == "P")
	  $DateArray[ccsHour] += 12;

	if($DateArray[ccsHour] == 12 && strtoupper($DateArray[ccsAmPm][0]) == "A")
	  $DateArray[ccsHour] = 0;

	if(strlen($DateArray[ccsYear]) == 2)
	  if($DateArray[ccsYear] < 70)
		$DateArray[ccsYear] = "20" . $DateArray[ccsYear];
	  else
		$DateArray[ccsYear] = "19" . $DateArray[ccsYear];
	  
	if($DateArray[ccsYear] < 1971 && $DateArray[ccsYear] > 0)
	  $DateArray[ccsAppropriateYear] = $DateArray[ccsYear] + intval((2000 - $DateArray[ccsYear]) / 28) * 28;
	else if($DateArray[ccsYear] > 2030)
	  $DateArray[ccsAppropriateYear] = $DateArray[ccsYear] - intval(($DateArray[ccsYear] - 2000) / 28) * 28;
	else      
	  $DateArray[ccsAppropriateYear] = $DateArray[ccsYear];

	$DateArray[ccsTimestamp] = mktime ($DateArray[ccsHour], $DateArray[ccsMinute], $DateArray[ccsSecond], $DateArray[ccsMonth], $DateArray[ccsDay], $DateArray[ccsAppropriateYear]);
	if($DateArray[ccsTimestamp] < 0) $ParsingDate = "";
	else $ParsingDate = $DateArray;
	
  }

  return $ParsingDate;
}
//End CCParseDate

//CCGetDateRegExp @0-BE174EDB
function CCGetDateRegExp($FormatMask)
{
  global $ShortWeekdays;
  global $Weekdays;
  global $ShortMonths;
  global $Months;

  $RegExp = false;
  if(is_array($FormatMask))
  {
	$masks = array(
	  "GeneralDate" => array("(\d{1,2})\/(\d{1,2})\/(\d{2}),?\s*(\d{2})?:?(\d{2})?:?(\d{2})?\s*(AM|PM)?", ccsMonth, ccsDay, ccsYear, ccsHour, ccsMinute, ccsSecond, ccsAmPm), 
	  "LongDate" => array("(" . join("|", $Weekdays) . "), (" . join("|", $Months) . ") (\d{1,2}), (\d{4})", ccsWeek, ccsFullMonth, ccsDay, ccsYear),
	  "ShortDate" => array("(\d{1,2})\/(\d{1,2})\/(\d{2})", ccsMonth, ccsDay, ccsYear), 
	  "LongTime" => array("(\d{1,2}):(\d{2}):(\d{2})\s*(AM|PM)?", ccsHour, ccsMinute, ccsSecond, ccsAmPm),
	  "ShortTime" => array("(\d{2}):(\d{2})", ccsHour, ccsMinute), 
	  "d" => array("(\d{1,2})", ccsDay), 
	  "dd" => array("(\d{2})", ccsDay), 
	  "ddd" => array("(" . join("|", $ShortWeekdays) . ")", ccsWeek), 
	  "dddd" => array("(" . join("|", $Weekdays) . ")", ccsWeek), 
	  "w" => array("\d"), "ww" => array("\d{1,2}"),
	  "m" => array("(\d{1,2})", ccsMonth), "mm" => array("(\d{2})", ccsMonth), 
	  "mmm" => array("(" . join("|", $ShortMonths) . ")", ccsShortMonth), 
	  "mmmm" => array("(" . join("|", $Months) . ")", ccsFullMonth),
	  "y" => array("\d{1,3}"), "yy" => array("(\d{2})", ccsYear), 
	  "yyyy" => array("(\d{4})", ccsYear), "q" => array("\d"),
	  "h" => array("(\d{1,2})", ccsHour), "hh" => array("(\d{2})", ccsHour), 
	  "H" => array("(\d{1,2})", ccsHour), "HH" => array("(\d{2})", ccsHour),
	  "n" => array("(\d{1,2})", ccsMinute), "nn" => array("(\d{2})", ccsMinute), 
	  "s" => array("(\d{1,2})", ccsSecond), "ss" => array("(\d{2})", ccsSecond), 
	  "AM/PM" => array("(AM|PM)", ccsAmPm), "am/pm" => array("(am|pm)", ccsAmPm), 
	  "A/P" => array("(A|P)", ccsAmPm), "a/p" => array("(a|p)", ccsAmPm),
	  "GMT" => array("([\+\-]\d{2})", ccsGMT), "S" => array("(\d{1,3})", ccsMilliSecond)
	);
	$RegExp[0] = "";
	$RegExpIndex = 1;
	$is_date = false; $is_datetime = false;
	for($i = 0; $i < sizeof($FormatMask); $i++)
	{
	  if(isset($masks[$FormatMask[$i]]))
	  {
		$MaskArray = $masks[$FormatMask[$i]];
		if($i == 0 && ($MaskArray[1] == ccsYear || $MaskArray[1] == ccsMonth 
		  || $MaskArray[1] == ccsFullMonth || $MaskArray[1] == ccsWeek || $MaskArray[1] == ccsDay))
		  $is_date = true;
		else if($is_date && !$is_datetime && $MaskArray[1] == ccsHour)
		  $is_datetime = true;
		$RegExp[0] .= $MaskArray[0];
		if($is_datetime) $RegExp[0] .= "?";
		for($j = 1; $j < sizeof($MaskArray); $j++)
		  $RegExp[$RegExpIndex++] = $MaskArray[$j];
	  }
	  else
	  {
		if($is_date && !$is_datetime && $i < sizeof($FormatMask) && $masks[$FormatMask[$i + 1]][1] == ccsHour)
		  $is_datetime = true;
		$RegExp[0] .= CCAddEscape($FormatMask[$i]);
		if($is_datetime) $RegExp[0] .= "?";
	  }
	}
	$RegExp[0] = str_replace(" ", "\s*", $RegExp[0]);
	$RegExp[0] = "/^" . $RegExp[0] . "$/i";
  }

  return $RegExp;
}
//End CCGetDateRegExp

//CCAddEscape @0-06D50C27
function CCAddEscape($FormatMask)
{
  $meta_characters = array("\\", "^", "\$", ".", "[", "|", "(", ")", "?", "*", "+", "{", "-", "]", "/");
  for($i = 0; $i < sizeof($meta_characters); $i++)
	$FormatMask = str_replace($meta_characters[$i], "\\" . $meta_characters[$i], $FormatMask);
  return $FormatMask;
}
//End CCAddEscape

//CCGetIndex @0-8DB8E12C
function CCGetIndex($ArrayValues, $Value, $IgnoreCase = true)
{
  $index = false;
  for($i = 0; $i < sizeof($ArrayValues); $i++)
  {
	if(($IgnoreCase && strtoupper($ArrayValues[$i]) == strtoupper($Value)) || ($ArrayValues[$i] == $Value))
	{
	  $index = $i;
	  break;
	}
  }
  return $index;
}
//End CCGetIndex

//CCFormatNumber @0-B39A1596
function CCFormatNumber($NumberToFormat, $FormatArray)
{
  $Result = "";
  if(is_array($FormatArray) && strlen($NumberToFormat))
  {
	$IsExtendedFormat = $FormatArray[0];
	$IsNegative = ($NumberToFormat < 0);
	$NumberToFormat = abs($NumberToFormat);
	$NumberToFormat *= $FormatArray[7];
  
	if($IsExtendedFormat) // Extended format
	{
	  $DecimalSeparator = $FormatArray[2];
	  $PeriodSeparator = $FormatArray[3];
	  $ObligatoryBeforeDecimal = 0;
	  $DigitsBeforeDecimal = 0;
	  $BeforeDecimal = $FormatArray[5];
	  $AfterDecimal = $FormatArray[6];
	  if(is_array($BeforeDecimal)) {
		for($i = 0; $i < sizeof($BeforeDecimal); $i++) {
		  if($BeforeDecimal[$i] == "0") {
			$ObligatoryBeforeDecimal++;
			$DigitsBeforeDecimal++;
		  } else if($BeforeDecimal[$i] == "#") 
			$DigitsBeforeDecimal++;
		}
	  }
	  $ObligatoryAfterDecimal = 0;
	  $DigitsAfterDecimal = 0;
	  if(is_array($AfterDecimal)) {
		for($i = 0; $i < sizeof($AfterDecimal); $i++) {
		  if($AfterDecimal[$i] == "0") {
			$ObligatoryAfterDecimal++;
			$DigitsAfterDecimal++;
		  } else if($AfterDecimal[$i] == "#")
			$DigitsAfterDecimal++;
		}
	  }
  
	  $NumberToFormat = number_format($NumberToFormat, $DigitsAfterDecimal, ".", "");
	  $NumberParts = explode(".", $NumberToFormat);

	  $LeftPart = $NumberParts[0];
	  if($LeftPart == "0") $LeftPart = "";
	  $RightPart = isset($NumberParts[1]) ? $NumberParts[1] : "";
	  $j = strlen($LeftPart);
	
	  if(is_array($BeforeDecimal))
	  {
		$RankNumber = 0;
		$i = sizeof($BeforeDecimal);
		while ($i > 0 || $j > 0)
		{
		  if(($i > 0 && ($BeforeDecimal[$i - 1] == "#" || $BeforeDecimal[$i - 1] == "0")) || ($j > 0 && $i < 1)) {
			$RankNumber++;
			$CurrentSeparator = ($RankNumber % 3 == 1 && $RankNumber > 3 && $j > 0) ? $PeriodSeparator : "";
			if($ObligatoryBeforeDecimal > 0 && $j < 1)
			  $Result = "0" . $CurrentSeparator . $Result;
			else if($j > 0)
			  $Result = $LeftPart[$j - 1] . $CurrentSeparator . $Result;
			$j--;
			$ObligatoryBeforeDecimal--;
			$DigitsBeforeDecimal--;
			if($DigitsBeforeDecimal == 0 && $j > 0)
			  for(;$j > 0; $j--)
			  {
				$RankNumber++;
				$CurrentSeparator = ($RankNumber % 3 == 1 && $RankNumber > 3 && $j > 0) ? $PeriodSeparator : "";
				$Result = $LeftPart[$j - 1] . $CurrentSeparator . $Result;
			  }
		  }
		  else if ($i > 0) {
			$BeforeDecimal[$i - 1] = str_replace("##", "#", $BeforeDecimal[$i - 1]);
			$BeforeDecimal[$i - 1] = str_replace("00", "0", $BeforeDecimal[$i - 1]);
			$Result = $BeforeDecimal[$i - 1] . $Result;
		  }
		  $i--;
		}
	  }

	  // Left part after decimal
	  $RightResult = "";
	  $IsRightNumber = false;
	  if(is_array($AfterDecimal))
	  {
		$IsZero = true;
		for($i = sizeof($AfterDecimal); $i > 0; $i--) {
		  if($AfterDecimal[$i - 1] == "#" || $AfterDecimal[$i - 1] == "0") {
			if($DigitsAfterDecimal > $ObligatoryAfterDecimal) {
			  if($RightPart[$DigitsAfterDecimal - 1] != "0") 
				$IsZero = false;
			  if(!$IsZero)
			  {
				$RightResult = $RightPart[$DigitsAfterDecimal - 1] . $RightResult;
				$IsRightNumber = true;
			  }
			} else {
			  $RightResult = $RightPart[$DigitsAfterDecimal - 1] . $RightResult;
			  $IsRightNumber = true;
			}
			$DigitsAfterDecimal--;
		  } else {
			$AfterDecimal[$i - 1] = str_replace("##", "#", $AfterDecimal[$i - 1]);
			$AfterDecimal[$i - 1] = str_replace("00", "0", $AfterDecimal[$i - 1]);
			$RightResult = $AfterDecimal[$i - 1] . $RightResult;
		  }
		}
	  }
	
	  if($IsRightNumber)
		$Result .= $DecimalSeparator ;

	  $Result .= $RightResult;

	  if(!$FormatArray[4] && $IsNegative && $Result)
		$Result = "-" . $Result;
	}
	else // Simple format
	{
	  if(!$FormatArray[4] && $IsNegative)
		$Result = "-" . $FormatArray[5] . number_format($NumberToFormat, $FormatArray[1], $FormatArray[2], $FormatArray[3]) . $FormatArray[6];
	  else
		$Result = $FormatArray[5] . number_format($NumberToFormat, $FormatArray[1], $FormatArray[2], $FormatArray[3]) . $FormatArray[6];
	}

	if(!$FormatArray[8])
	  $Result = htmlspecialchars($Result);

	if(strlen($FormatArray[9]))
	  $Result = "<FONT COLOR=\"" . $FormatArray[9] . "\">" . $Result . "</FONT>";
  }
  else
  {
	$Result = $NumberToFormat;
  }

  return $Result;
}
//End CCFormatNumber

//CCValidateNumber @0-D53857C4
function CCValidateNumber($NumberValue, $FormatArray)
{
  $is_valid = true;
  if(strlen($NumberValue))
  {
	$NumberValue = CCCleanNumber($NumberValue, $FormatArray);
	$is_valid = is_numeric($NumberValue);
  }
  return $is_valid;
}

//End CCValidateNumber

//CCParseNumber @0-51D95F29
function CCParseNumber($NumberValue, $FormatArray, $DataType)
{
  $NumberValue = CCCleanNumber($NumberValue, $FormatArray);
  if(is_array($FormatArray) && strlen($NumberValue))
  {

	if($FormatArray[4]) // Is use parenthesis
	  $NumberValue = - abs(doubleval($NumberValue));

	$NumberValue /= $FormatArray[7];
  }

  if(strlen($NumberValue))
  {
	if($DataType == ccsFloat)
	  $NumberValue = doubleval($NumberValue);
	else
	  $NumberValue = round($NumberValue, 0);
  }

  return $NumberValue;
}
//End CCParseNumber

//CCCleanNumber @0-2A278526
function CCCleanNumber($NumberValue, $FormatArray)
{
  if(is_array($FormatArray))
  {
	$IsExtendedFormat = $FormatArray[0];

	if($IsExtendedFormat) // Extended format
	{

	  $BeforeDecimal = $FormatArray[5];
	  $AfterDecimal = $FormatArray[6];
	
	  if(is_array($BeforeDecimal))
	  {
		for($i = sizeof($BeforeDecimal); $i > 0; $i--) {
		  if($BeforeDecimal[$i - 1] != "#" && $BeforeDecimal[$i - 1] != "0") 
		  {
			$search = $BeforeDecimal[$i - 1];
			$search = ($search == "##" || $search == "00") ? $search[0] : $search;
			$NumberValue = str_replace($search, "", $NumberValue);
		  }
		}
	  }

	  if(is_array($AfterDecimal))
	  {
		for($i = sizeof($AfterDecimal); $i > 0; $i--) {
		  if($AfterDecimal[$i - 1] != "#" && $AfterDecimal[$i - 1] != "0") 
		  {
			$search = $AfterDecimal[$i - 1];
			$search = ($search == "##" || $search == "00") ? $search[0] : $search;
			$NumberValue = str_replace($search, "", $NumberValue);
		  }
		}
	  }
	}
	else // Simple format
	{
	  if(strlen($FormatArray[5]))
		$NumberValue = str_replace($FormatArray[5], "", $NumberValue);
	  if(strlen($FormatArray[6]))
		$NumberValue = str_replace($FormatArray[6], "", $NumberValue);
	}

	if(strlen($FormatArray[3]))
	  $NumberValue = str_replace($FormatArray[3], "", $NumberValue); // Period separator
	if(strlen($FormatArray[2]))
	  $NumberValue = str_replace($FormatArray[2], ".", $NumberValue); // Decimal separator

	if(strlen($FormatArray[9]))
	{
	  $NumberValue = str_replace("<FONT COLOR=\"" . $FormatArray[9] . "\">", "", $NumberValue);
	  $NumberValue = str_replace("</FONT>", "", $NumberValue);
	}
  }
  $NumberValue = str_replace(",", ".", $NumberValue); // Decimal separator

  return $NumberValue;
}
//End CCCleanNumber

//CCParseInteger @0-FDF2EE85
function CCParseInteger($NumberValue, $FormatArray)
{
  return CCParseNumber($NumberValue, $FormatArray, ccsInteger);
}
//End CCParseInteger

//CCParseFloat @0-C9EAEA95
function CCParseFloat($NumberValue, $FormatArray)
{
  return CCParseNumber($NumberValue, $FormatArray, ccsFloat);
}
//End CCParseFloat

//CCValidateBoolean @0-7BAB2020
function CCValidateBoolean($BooleanValue, $Format)
{
  $Result = true;

  if(is_array($Format))
  {
	if(strtolower($BooleanValue) != strtolower($Format[0]) 
	  && strtolower($BooleanValue) != strtolower($Format[1]) 
	  && strtolower($BooleanValue) != strtolower($Format[2])
	)
	  $Result = false;
  }

  return $Result;
}
//End CCValidateBoolean

//CCFormatBoolean @0-5B3F5CF9
function CCFormatBoolean($BooleanValue, $Format)
{
  $Result = $BooleanValue;

  if(is_array($Format))
  {
	if($BooleanValue == 1)
	  $Result = $Format[0];
	else if(strval($BooleanValue) == "0" || $BooleanValue === false)
	  $Result = $Format[1];
	else
	  $Result = $Format[2];
  }

  return $Result;
}
//End CCFormatBoolean

//CCParseBoolean @0-0C7716BC
function CCParseBoolean($Value, $Format)
{
  $Result = $Value;
  if(is_array($Format))
  {
	if(strtolower(strval($Value)) == strtolower(strval($Format[0])))
	  $Result = true;
	else if(strtolower(strval($Value)) == strtolower(strval($Format[1])))
	  $Result = false;
	else
	  $Result = "";
  }
  return $Result;
}
//End CCParseBoolean

//CCGetBooleanFormat @0-B9D3DA0C
function CCGetBooleanFormat($Format)
{
  $FormatString = "";
  if(is_array($Format))
  {
	for($i = 0; $i < sizeof($Format); $i++) {
	  if(strlen($Format[$i])) {
		if(strlen($FormatString)) $FormatString .= ";";
		$FormatString .= $Format[$i];
	  }
	}
  }
  return $FormatString;
}
//End CCGetBooleanFormat

//CCCheckSSL @0-F72881AB
function CCCheckSSL()
{
	$HTTPS = strtolower(getenv("HTTPS"));
	if($HTTPS != "on")
	{
		echo "SSL connection error. This page can be accessed only via secured connection.";
		exit;
	}
}
//End CCCheckSSL

//CCSecurityRedirect @0-4A99366E
function CCSecurityRedirect($GroupsAccess, $URL)
{
	$ReturnPage = getenv("REQUEST_URI");
	if(!strlen($ReturnPage)) {
		$ReturnPage = getenv("SCRIPT_NAME");
		$QueryString = CCGetQueryString("QueryString", "");
		if($QueryString !== "")
			$ReturnPage .= "?" . $QueryString;
	}
	$ErrorType = CCSecurityAccessCheck($GroupsAccess);
	if($ErrorType != "success")
	{
		if(!strlen($URL))
			$Link = ServerURL . "index.php";
		else
			$Link = $URL;
		header("Location: " . $Link . "?ret_link=" . urlencode($ReturnPage) . "&type=" . $ErrorType);
		exit;
	}
}
//End CCSecurityRedirect

//CCSecurityAccessCheck @0-7B496647
function CCSecurityAccessCheck($GroupsAccess)
{
	$ErrorType = "success";
	if(!strlen(CCGetUserID()))
	{
		$ErrorType = "notLogged";
	}
	else
	{
		$GroupID = CCGetGroupID();
		if(!strlen($GroupID))
		{
			$ErrorType = "groupIDNotSet";
		}
		else
		{
			if(!CCUserInGroups($GroupID, $GroupsAccess))
				$ErrorType = "illegalGroup";
		}
	}
	return $ErrorType;
}
//End CCSecurityAccessCheck

//CCGetUserID @0-6FAFFFAE
function CCGetUserID()
{
	return CCGetSession("UserID");
}
//End CCGetUserID

//CCGetGroupID @0-89F10997
function CCGetGroupID()
{
	return CCGetSession("GroupID");
}
//End CCGetGroupID

//CCGetUserLogin @0-ACD25564
function CCGetUserLogin()
{
	return CCGetSession("UserLogin");
}
//End CCGetUserLogin

//CCGetUserPassword @0-D67B1DE1
function CCGetUserPassword()
{
	return CCGetSession("PassWord");
}
//End CCGetUserPassword

//CCGetUserNames 
function CCGetUserNames()
{
	global $db;
	$Usersnames ='';
	$Usersnames = CCGetSession("SurName").  ' ' .  CCGetSession("OtherNames") ;
	if(!strlen($Usersnames))
	{
		$GetUsersNames = $db->GetRow("SELECT SWA.HAMIS_USERS.SURNAME,SWA.HAMIS_USERS.OTHER_NAMES 
							FROM SWA.HAMIS_USERS
							WHERE SWA.HAMIS_USERS.USER_NAME LIKE USER");
							
			CCSetSession("SurName", $GetUsersNames[SURNAME]);
			CCSetSession("OtherNames",$GetUsersNames[OTHER_NAMES]);	
			$Usersnames = $GetUsersNames[SURNAME] .'  ' . $GetUsersNames[OTHER_NAMES]  ;
			
	}
	return $Usersnames;
}
//End CCGetUserNames

//CCGetUserPrinter 
function CCGetUserPrinter()
{
	return CCGetSession("Printer");
}
//End CCGetUserPrinter

//CCGetUserStation 
function CCGetUserStation()
{
	return CCGetSession("Station");
}
//End CCGetUserStation

// Get the login server
function CCGetServer()
{
	return CCGetSession("server");
}
//End getthe login server

// Get the academic year
function CCGetAcademicYear()
{
	return CCGetSession("ACADEMIC_YEAR");
}
// end get academic year

// Get the Semester
function CCGetSemester()
{
	return CCGetSession("SEMESTER");
}
// end get Semester 

// Get the global error
function CCGetErrorr()
{
	return CCGetSession("GLOBAL_ERROR");
}
//End get global error

// Get whether to check Nomil Roll Status
function CCGetStudentStatus()
{
	return CCGetSession("STUDENT_STATUS");
}
// End check Student Status

// Get whether to check SWA Balances
function CCGetCheckSwaBalances()
{
	return CCGetSession("CHECKSWA_BALANCES");
}
// End whetehr to check SWA balances

// Get whether to check SWA Balances
function CCGetStudentCategoryID()
{
	return CCGetSession("CATEGORY_ID");
}
// End whetehr to check SWA balances

// Get the current Smu Name
function  CCGetCurrentSmuName()
{
		return CCGetSession("SMU_NAME");
}
// End Get current SMu Name

// Get the current Smu Name
function  CCGetProfileSmuName()
{
		return CCGetSession("PROFILE_SMUCODE");
}
// End Get current SMu Name

// Get the current Smu Code
function  CCGetCurrentSmuCode()
{
		return CCGetSession("SMU_CODE");
}
// End Get current SMu Code


// Get the current set hall code
function CCGetCurrentHall()
{
	return CCGetSession("CURRENT_HALL");
}
// End get current  hall code

// Get the current set hall Name
function CCGetCurrentHallName()
{
	return CCGetSession("HALL_NAME");
}
// End get current  hall Name

// Retreave current registration No
function CCGetCurrentRegNo()
{
	 return CCGetSession("RegNo");
}
// End set Current student registration number

// Set the Current Room Number
function CCGetCurrentRoomNo()
{
	 return CCGetSession("RoomNo");
}


// Set the Current Receipt No.
function CCGetReceiptNo()
{
	 return CCGetSession("ReceiptNO");
}
// End set Current Receipt No.

// Get the whether to check student clearance
function CCGetCheckClearance()
{
	return CCGetSession("CLEARANCE");
}
// End Get Stident Clearance


// Get curretn Refferer
function CCGetReferrer()
{
	 return CCGetSession("REFFER");
}
// End set Current Receipt No.

// Get curretn Refferer
function CCGetLogonStatus()
{
	 return CCGetSession("LogonSuccess");
}
// End set Current Receipt No.


// A function to dislay Debug Message
function DisplayDebugMessage($message)
{
	if (CCGetUserID()== 'PKARIUKI')
	{
		return "<h4> $message </h4>" ;
	}
}
// En d function to display debug message
//CCUserInGroups @0-51407098
function CCUserInGroups($GroupID, $GroupsAccess)
{
	$Result = "";
	if(strlen($GroupsAccess))
	{
		$GroupNumber = intval($GroupID);
		while(!$Result && $GroupNumber > 0)
		{
			$Result = (strpos(";" . $GroupsAccess . ";", ";" . $GroupNumber . ";") !== false);
			$GroupNumber--;
		}
	}
	else
	{
		$Result = true;
	}
	return $Result;
}
//End CCUserInGroups

//CCLoginUser @0-FC1E1C53
function CCLoginUser($login, $password)
{
	CCSetSession("server", $server);
	global $db ;
	global $tnsName;
	if(is_object($db))
	{
		if (!$db->IsConnected())
		{
			if($_SERVER["HTTP_HOST"] == '10.2.21.87' || $_SERVER["HTTP_HOST"]=='smis.uonbi.ac.ke')
				$dsn = "oci8://$login:$password@proddb.uonbi.ac.ke/proddb"; 
			else
				$dsn = "oci8://$login:$password@umis2.uonbi.ac.ke/webdb"; 	
			$db = NewADOConnection($dsn);
			//$db->Connect($tnsName,$login,$password);
		}
	}
	else
	{
			if($_SERVER["HTTP_HOST"] == '10.2.21.87' || $_SERVER["HTTP_HOST"]=='smis.uonbi.ac.ke')
				$dsn = "oci8://$login:$password@proddb.uonbi.ac.ke/proddb"; 
			else
				$dsn = "oci8://$login:$password@umis2.uonbi.ac.ke/webdb"; 	
			$db = NewADOConnection($dsn);
	}
	
	if(is_object($db))
		if ($db->IsConnected())
		{
				CCSetSession("UserID",strtoupper($login));
				CCSetSession("PassWord",$password);
				CCSetLogonStatus(1);	 		
		}
		
	$login = strtoupper($login) ;
	$SQL = "SELECT USER_NAME,SURNAME,OTHER_NAMES, PRINTER,STATION, USER_GROUP,STATUS FROM SWA.HAMIS_USERS WHERE USER_NAME='$login'";
	$rs = $db->Execute($SQL);
		if($arr = $rs->FetchRow())
		{
			if($arr[STATUS]=='ACTIVE'){
				CCSetSession("GroupID", $arr[USER_GROUP]);
				CCSetSession("SurName", $arr[SURNAME]);
				CCSetSession("OtherNames",$arr[OTHER_NAMES]);
				CCSetSession("Printer", $arr[PRINTER]);
				CCSetSession("Station", $arr[STATION]);
				CCSetSession("GroupID", $arr[USER_GROUP]);
				CCSetLogonStatus(1);
			}
			else{
			  header("Location: logout.php?Msg=DISABLED");
			  exit;
			}
					
		}else{
		  header("Location: logout.php?Msg=DISABLED");
		  exit;
		}
	
		$LoggedInSurName =  $arr['SURNAME'] ;
		$SQL = "SELECT *  FROM SWA.USER_PROFILE WHERE UPPER(USER_NAME)=UPPER('$login')";
		$rs = $db->Execute($SQL);
		if($arr = $rs->FetchRow())
		{
			CCSetSession("ACADEMIC_YEAR", $arr[ACADEMIC_YEAR]);
			CCSetSession("SEMESTER", $arr[SEMESTER]);
			CCSetSession("STUDENT_STATUS", $arr[STUDENT_STATUS]);
			CCSetSession("CHECKSWA_BALANCES", $arr[SWA_BALANCE]);
			CCSetSession("CATEGORY_ID", $arr[CATEGORY_ID]);
			CCSetSession("SMU_CODE", $arr[SMU_CODE]);
			CCSetSession("ROOM_DISPLAY", $arr[ROOM_DISPLAY]);
			CCSetSession("CLEARANCE", $arr[STUDENT_CLEARANCE]);
			if(!empty($arr[SMU_CODE]))
				CCSetSession("PROFILE_SMUCODE",$arr[SMU_CODE]);			
			else
				 CCSetSession("PROFILE_SMUCODE",1);		
		}
		
		return $LoggedInSurName;

}
//End CCLoginUser

function SetAcademicYear($Year,$Semester)
{
	 CCSetSession("ACADEMIC_YEAR", $Year);
	 CCSetSession("SEMESTER", $Semester);
}
// End Set the Academic year and semester in User

// Set the Global error Information
function SetError($error)
{
	 CCSetSession("GLOBAL_ERROR", $error);
}
//End set global Error information

// Set Student Status
function StudentStatus($status)
{
	 CCSetSession("STUDENT_STATUS", $status);
}
// End Set Student status

// Set Whether to ceck SWA balances
function CheckSwaBalances($CheckBalances)
{
	 CCSetSession("CHECKSWA_BALANCES", $CheckBalances);
}
// End Setting Whether to ceck SWA balances

// Set Whether to ceck SWA balances
function SetStudentCategoryID($CategoryId)
{
	 CCSetSession("CATEGORY_ID", $CategoryId);
}
// End Setting Whether to ceck SWA balances

// Set the current Smu
function SetCurrentSmu($SmuName,$SmuCode)
{
	CCSetSession("SMU_NAME", $SmuName);
	CCSetSession("SMU_CODE", $SmuCode);
}
// End Set Current Smu

// Set the current Smu Name
function SetProfileSmuName($SmuName,$SmuCode)
{
		return CCSetSession("PROFILE_SMUNAME",$SmuName);
		return CCSetSession("PROFILE_SMUCODE",$SmuCode);
}
// End Set current SMu Name


// Set the whether to check student clearance
function SetCheckClearance($Clearance)
{
	CCSetSession("CLEARANCE", $Clearance);
}
// End Set Stident Clearance

// Set the Current selected Hall code
function SetCurrentHall($HallId)
{
	 CCSetSession("CURRENT_HALL", $HallId);
}
// End set Current Hall code

// Set the Current selected Hall code
function SetCurrentHallName($HallName)
{
	 CCSetSession("HALL_NAME", $HallName);
}
// End set Current Hall code

// Set the Current student registration number
function SetCurrentRegNo($RegNo)
{
	 CCSetSession("RegNo", $RegNo);
}
// End set Current student registration number

// Set the Current Room Number
function SetCurrentRoomNo($RoomNo)
{
	 CCSetSession("RoomNo", $RoomNo);
}
// End set Current Room Number

// Set the Current Receipt No.
function SetReceiptNo($ReceiptNo)
{
	 CCSetSession("ReceiptNO", $ReceiptNo);
}
// End set Current Receipt No.

// Set the Current Receipt No.
function SetReferrer($Reffer)
{
	 CCSetSession("REFFER", $Reffer);
}
// End set Current Receipt No.

// Set Oracle Ora ID
function SetOracleOraLogInID($CurrentID)
{
	 CCSetSession("ORA_ORACLE_ID", $CurrentID);
}
// End set Set Oracle ID.

function CCSetLogonStatus($Setvalue)
{
	 CCSetSession("LogonSuccess", $Setvalue);
}
// End set Current Receipt No.

// Set Oracle Oci ID
function SetOracleOciLogInID($CurrentID)
{
	 CCSetSession("OCI_ORACLE_ID", $CurrentID);
}
// End set Set Oracle ID.

// Get the Oracle Oci ID.
function CCGetOracleOraLogInID()
{
	 return CCGetSession("OCI_ORACLE_ID");
}
// End get  the Oracle Oci ID.

// Get the Oracle Oci ID.
function CCGetOracleOciLogInID()
{
	 return CCGetSession("OCI_ORACLE_ID");
}
// End get  the Oracle Oci ID.

// Get the Oracle Oci ID.
function CCGetSmisUser()
{
	global $db;
	if(!CCGetSession("SMIS_USER"))
	{
		$LoggedOnUser = $db->GetRow("SELECT ALL MUTHONI.USERS.USERID, MUTHONI.USERS.SURNAME, MUTHONI.USERS.OTHER_NAMES, 
									MUTHONI.USERS.COL_CODE
									 FROM MUTHONI.USERS
									 WHERE MUTHONI.USERS.USERID=USER");
		CCSetSession("SMIS_USER", $LoggedOnUser[OTHER_NAMES] .' ' . $LoggedOnUser[SURNAME] . '&nbsp;(' . $LoggedOnUser[COL_CODE].')');									 
	}
	return CCGetSession("SMIS_USER") ;;
} // Get SMIS USer							 


//CCLogoutUser @0-55C59DC5
function CCLogoutUser()
{
	CCSetSession("UserID", "");
	CCSetSession("PassWord","");
	CCSetSession("UserLogin", "");
	CCSetSession("GroupID", "");
	CCSetSession("SurName", "");
	CCSetSession("OtherNames","");
	CCSetSession("Printer","");
	CCSetSession("Station", "");
	CCSetSession("GroupID","");
	CCSetSession("server","");
	CCSetLogonStatus(0);
	SetCheckClearance("");
	session_destroy();
	
}
//End CCLogoutUser

//CCServerAccess 

function CCServerAccess($db)
{
		$pingserver =  `ping -c 1 $db->DBHost`;

		$pingserver = strstr($pingserver,"transmitted,");
		
		$rest = substr($pingserver,13,1); // extract the number of packets received
		
		$maxtime =substr($pingserver,81,3); // extract the maximum time it takes to receive a packet

		 $error = '';							
		
		if ($rest < 1)
		{
				$error =  "<br><br><H2><font color=\"red\">Sorry The Hostel Administration Management Information System(HAMIS)  is " .
						  " unreachable ... <blink>Please try  later...</blink></font></H2> <h3>If you have any queries direct them " .
						  " to <a href=\"mailto:hamis@uonbi.ac.ke\">hamis@uonbi.ac.ke</a><h3>" ;
			
				return $error;
		}
		else
		 {
				if ($maxtime > 100)
				{
						 $error = "<H2>Access time to the host slow</H2>" ;
						 return $error;
				}
				return $error;
		}  
		return $error;
}

function initcap($StringVar)
{
	return ucwords(strtolower($StringVar));
}

//End 
function CheckLoggon()
{
	$IsLogedOn = CCGetLogonStatus();
	$Redirect = 'http://' . $_SERVER["HTTP_HOST"] . '/swa/index.php ';
	if (!$IsLogedOn)
	{
		header("Location: $Redirect");
		exit;
		return 0;
	}
	else
		return 1;
}

function print_r_html($arr, $style = "display: none; margin-left: 10px;")
{ static $i = 0; $i++;
  echo "\n<div id=\"array_tree_$i\" class=\"array_tree\">\n";
  foreach($arr as $key => $val)
  { switch (gettype($val))
   { case "array":
	   echo "<a onclick=\"document.getElementById('";
	   echo "array_tree_element_$i" . "').style.display = ";
	   echo "document.getElementById('array_tree_element_$i";
	   echo "').style.display == 'block' ?";
	   echo "'none' : 'block';\"\n";
	   echo "name=\"array_tree_link_$i\" href=\"#array_tree_link_$i\">".htmlspecialchars($key)."</a><br />\n";
	   echo "<div class=\"array_tree_element_\" id=\"array_tree_element_$i\" style=\"$style\">";
	   echo print_r_html($val);
	   echo "</div>";
	 break;
	 case "integer":
	   echo "<b>".htmlspecialchars($key)."</b> => <i>".htmlspecialchars($val)."</i><br />";
	 break;
	 case "double":
	   echo "<b>".htmlspecialchars($key)."</b> => <i>".htmlspecialchars($val)."</i><br />";
	 break;
	 case "boolean":
	   echo "<b>".htmlspecialchars($key)."</b> => ";
	   if ($val)
	   { echo "true"; }
	   else
	   { echo "false"; }
	   echo  "<br />\n";
	 break;
	 case "string":
	   echo "<b>".htmlspecialchars($key)."</b> => <code>".htmlspecialchars($val)."</code><br />";
	 break;
	 default:
	   echo "<b>".htmlspecialchars($key)."</b> => ".gettype($val)."<br />";
	 break; }
   echo "\n"; }
  echo "</div>\n"; 
 }
function print_html_r2( $aData ) { 
   echo nl2br( eregi_replace( " ", " ", print_r( $data, TRUE ) ) );    
} 

function GetUserRoles()
{
	global $db;
	$UserRolesArray =  $db->GetArray("select GRANTED_ROLE   from user_role_privs");
	$UserRoles = array();
	foreach($UserRolesArray as $userKey => $uservalue)
		array_push($UserRoles,$uservalue[GRANTED_ROLE]);
		
	return $UserRoles;
}

function getRolePrivs($pUserRole) {
 global $db;

//Queries to be executed
 $UserRolesSQL ="SELECT UNIQUE  GRANTED_ROLE FROM USER_ROLE_PRIVS
	WHERE (GRANTED_ROLE = UPPER('$pUserRole') OR GRANTED_ROLE = 'SMIS_DEVELOPER') " ;
// Parse the fee item detail query
 $UserRoles = $db->GetArray($UserRolesSQL);

	
 if(count($UserRoles)) 
	  return 1;
  else 
	return 0;
}
//End getRolePrivs($pUserRole)
?>
