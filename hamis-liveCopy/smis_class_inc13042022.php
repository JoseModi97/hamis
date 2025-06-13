<?php
session_start();

$HamisOnline = new smis('2010/2011');
$ApplicantBtn = CCGetParam("ApplicantBtn");

class smis  {
	var $Ora_Db;
	var $MysqlSmisDB;
	var $MysqlHamisDB;
	var $LoggedOn ;
	var $Academicyear;
	var $AcademicYearStartDate;
	var $AcademicYearDate ; 
	var $DaysBetween =30; 
	var $ConfimationDays = 5 ; 
	var $DataConfig;
	var $Debug = FALSE;
	var $RoomApplicationStatus = FALSE;
	var $StudentApplicationStatus = FALSE;
	var $RoomBookingStatus = FALSE;
	var $RoomApplicationError = array();
    var $UpdateFormDisplayed = FALSE;
	var $AllocatedHall ='';
	

	
	
    public function   smis($AcademicYear){
		ini_set("display_errors", "2"); # but do not echo the errors
		define('ADODB_ERROR_LOG_TYPE',3);
		
		$ADODB_CACHE_DIR = RelativePath .  '/tmp';
		if($_SERVER["HTTP_HOST"] == '10.2.21.87' || $_SERVER["HTTP_HOST"]=='smis.uonbi.ac.ke')  
			$tnsName="(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 10.2.20.30)(PORT = 1521))) (CONNECT_DATA = (SERVICE_NAME = proddb)))" ; 
		else
			$tnsName="(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 10.2.20.30)(PORT = 1521))) (CONNECT_DATA = (SERVICE_NAME = proddb)))" ;
		
		
		if(function_exists(NewADOConnection)) {
			$dsn = 'oci8://'.$user.':'.$user.'@'.$tnsName ;//oci8po //oci8
			$this->Ora_Db = NewADOConnection('oci8');
			
		   if($_SERVER["HTTP_HOST"] == '10.2.21.87' || $_SERVER["HTTP_HOST"]=='smis.uonbi.ac.ke') 
				$MysqlSmisDsn = "mysql://hamis_user:hamis_09@localhost/smis";  
		   else 	
				$MysqlSmisDsn = "mysql://hamis_user:hamis_09@10.2.21.87/smis";
			//$this->MysqlSmisDB = NewADOConnection($MysqlSmisDsn);
			
		   if($_SERVER["HTTP_HOST"] == '10.2.21.87' || $_SERVER["HTTP_HOST"] == 'smis.uonbi.ac.ke') 	
			 $MysqlHamisDsn = "mysql://hamis_user:hamis_09@localhost/hamis";
		   else
			  $MysqlHamisDsn = "mysql://hamis_user:hamis_09@10.2.21.87/hamis"; 
			//$this->MysqlHamisDB = NewADOConnection($MysqlHamisDsn);
			
			$this->MysqlSmisDB = NewADOConnection('mysql');
			
			$this->MysqlHamisDB = NewADOConnection('mysql');
			
			$loggedonUser ="webuser";// CCGetUserID();
			$logonPassword = "webuser";//CCGetUserPassword();
			
		   // echo "Error " . $Ora_Db->ErrorMsg();
			$this->Ora_Db->Connect($tnsName,$loggedonUser,$logonPassword);
			$this->Ora_Db->SetFetchMode(ADODB_FETCH_ASSOC); 
			if($_SERVER["HTTP_HOST"] == '10.2.21.87' || $_SERVER["HTTP_HOST"]=='smis.uonbi.ac.ke') 
				$this->MysqlHamisDB->Connect('localhost','hamis_user','hamis_09','hamis'); 
			else
				$this->MysqlHamisDB->Connect('10.2.21.87','hamis_user','hamis_09','hamis');
				
			$this->MysqlHamisDB->SetFetchMode(ADODB_FETCH_ASSOC);
			
			if($_SERVER["HTTP_HOST"] == '10.2.21.87' || $_SERVER["HTTP_HOST"]=='smis.uonbi.ac.ke')
				 $this->MysqlSmisDB->Connect('localhost','hamis_user','hamis_09','smis');  
			else
				$this->MysqlSmisDB->Connect('10.2.21.87','hamis_user','hamis_09','smis');
			$this->MysqlSmisDB->SetFetchMode(ADODB_FETCH_ASSOC);
			
			$this->Ora_Db->execute("ALTER SESSION SET NLS_DATE_FORMAT = 'DD-MON-YYYY'" );
			//$MysqlSmisDB = NewADOConnection($MysqlDsn) or die($MysqlSmisDB->ErrorMsg());
			 $this->Academicyear     = $AcademicYear;
			 $this->GetSystemConfigurations($this->Academicyear);
		}
	} // connect_smis end fucntion 
	
    public function GetSystemConfigurations($Academicyear){
	    $SQLStmt = "SELECT Academic_year_start_date , academic_year_date ,days_between , confimation_days , data_config
			     FROM hamis.room_admin_data
			     WHERE academic_year = '$Academicyear' ";
			     
       $ConfigInfo = $this->MysqlHamisDB->GetRow($SQLStmt );
       if(count($ConfigInfo)){
		    $this->AcademicYearStartDate =$ConfigInfo['Academic_year_start_date']; 
		    $this->AcademicYearDate=  $ConfigInfo['academic_year_date']; 
		    $this->DaysBetween = $ConfigInfo['days_between']; 
		    $this->ConfimationDays = $ConfigInfo['confimation_days'];  
		    $this->DataConfig = $ConfigInfo['data_config'];  
       }
       else{ 
		    $this->DaysBetween = 30; 
		    $this->ConfimationDays = 5;  
       }
    }   // function

    public function   GetMySQLDB() {
	    return $this->MysqlSmisDB;
    }


	    // Set the Global error Information
    public 	function  SetError($error){
	     $this->CCSetSession("GLOBAL_ERROR", $error);
    }
    //End set global Error information

    public function GetError(){
      return $this->CCGetSession("GLOBAL_ERROR");
    }
	

    public function   getPrevioustudyLevel($regNo,$AcademicYear) {
		    $studyLevel=0;
		    if (is_object($this->Ora_Db)) {
			    $Stmt = "SELECT DISTINCT
						    MUTHONI.ACADEMIC_YEAR_PROGRESS.LEVEL_OF_STUDY
						    FROM MUTHONI.ACADEMIC_YEAR_PROGRESS
						    WHERE (MUTHONI.ACADEMIC_YEAR_PROGRESS.ACADEMIC_YEAR='$AcademicYear'
						    AND MUTHONI.ACADEMIC_YEAR_PROGRESS.REGISTRATION_NUMBER='$regNo') ";
						    
			    $studyLevel = $this->Ora_Db->GetOne($Stmt);

		    } 
	    //$fp =fopen("logs/debugmessing.txt",'a+');
	    //fputs($fp,$Stmt);
	    //fputs($fp,"\n\n AcademicYear = $AcademicYear 	,	studyLevel = 	$studyLevel \n\n");
	    //fclose($fp);
	    return $studyLevel ;

    }

    public function   getCurrentStudyLevel($regNo,$AcademicYear,$SecondFunctionCall=0) {

	    //$fp =fopen("logs/debugmessing.txt",'w');
	    
	    //fwrite($fp,var_dump($this->Ora_Db));
	    
	    if (is_object($this->Ora_Db)) {
		    $Stmt = "SELECT DISTINCT
					    MUTHONI.ACADEMIC_YEAR_PROGRESS.LEVEL_OF_STUDY
					    FROM MUTHONI.ACADEMIC_YEAR_PROGRESS
					    WHERE (MUTHONI.ACADEMIC_YEAR_PROGRESS.ACADEMIC_YEAR='$AcademicYear'
					    AND MUTHONI.ACADEMIC_YEAR_PROGRESS.REGISTRATION_NUMBER='$regNo') ";
					    
		    $studyLevel = $this->Ora_Db->GetOne($Stmt);				
    } 
	    
	    
	    if (!$studyLevel) 
		    $studyLevel = 0 ;
	    
		    
		    if(!$studyLevel && $SecondFunctionCall == 0 ) {
			    $LastAcademicYear = getPreviousacademicYear($AcademicYear)   ;
			    $LastAcademicYearLevelofStudy = $this->getPrevioustudyLevel($regNo,$LastAcademicYear)  ;
			    //fwrite($fp,"\n Last Academic Year Level of LastAcademicYearLevelofStudy = $LastAcademicYearLevelofStudy ");	
			    if ($LastAcademicYearLevelofStudy && $LastAcademicYearLevelofStudy <= 5 ) 
				     $studyLevel = $LastAcademicYearLevelofStudy + 1 ;	
					      
		    }
		    
		    
		    //fwrite($fp,"\n Level of Study for Academic Year $AcademicYear being returned = $studyLevel ");
		    //fclose($fp);
		     return  $studyLevel;
    }

    public function   getOtheStudentdetails($regNo){
	    $OtherDatailsSql = "SELECT DISTINCT MUTHONI.UON_STUDENTS.SEX,STC_STUDENT_CATEGORY_ID,D_PROG_DEGREE_CODE ,     
					    MUTHONI.UON_STUDENTS.MARITAL_STATUS, MUTHONI.UON_STUDENTS.STUDENT_STATUS,STUDENT_ADDRESS,DEGREE_PROGRAMMES.DURATION
					    FROM MUTHONI.UON_STUDENTS,MUTHONI.DEGREE_PROGRAMMES 
					    WHERE (MUTHONI.UON_STUDENTS.REGISTRATION_NUMBER='$regNo')
					       AND(DEGREE_PROGRAMMES.DEGREE_CODE = UON_STUDENTS.D_PROG_DEGREE_CODE )";
					    
	    $OtherDatailsInfo = $this->Ora_Db->GetRow($OtherDatailsSql);
					    
	    return   $OtherDatailsInfo ;
					    
    }

    public function CheckGraduands($regNo){
	     $GraduandsCheckSQL = "select ACADEMIC_YEAR   from muthoni.graduands where REGISTRATION_NUMBER ='$regNo'" ;
	     $GraduandsCheck = $this->Ora_Db->GetOne($GraduandsCheckSQL);  
	     return   $GraduandsCheck;
    }

    public function   getStudentGroup($regNo) {
	    $groupCode = "" ;
	    $SQLQuery = "SELECT
		    students.group_code
		    FROM  students
		    WHERE students.registration_number='$regNo' ";
		    
		    $groupCode =  $this->MysqlSmisDB->GetOne($SQLQuery);
	     return  $groupCode;
    }
		
	
    public	function  getStudentInfo($regno){
	    $sql ="select 
		    students.registration_number,
		    students.other_names,
		    students.surname,
		    students.national_id,
			    now(),
		    students.primary_email as email,
		    students.sec_email,
		    students.uon_email,
			    students.password,
			    'useradmin' as howcreated,
			    '' as acceslevel,
			    primary_mobile as cellnumber ,
			    sec_mobile
			    from smis.students
			    where registration_number like '$regno'";   
			    
	    $studentInfo = $this->MysqlSmisDB->GetRow($sql);
	    $otherStudInfo = $this->getOtheStudentdetails($regno);
	    if(is_array($otherStudInfo)){
		    foreach($otherStudInfo as $key => $value)
			    $studentInfo[$key] =  $value ; 
	    }	
      $studentInfo['email'] = $studentInfo['email']  ? $studentInfo['email'] : $studentInfo['sec_email']  ;
      if(strlen($studentInfo['email']))
	    $studentInfo['email'] = $studentInfo['uon_email']  	;
	    
      $studentInfo['cellnumber'] =$studentInfo['cellnumber']  ? $studentInfo['cellnumber'] : $studentInfo['sec_mobile']  ;	

	    
	    return $studentInfo;		
    }


    //CCGetSession @0-9BBC6D71

    public 	public function  CCGetSession($parameter_name){
	    global $HTTP_SESSION_VARS,$_SESSION;
	    return isset($HTTP_SESSION_VARS[$parameter_name]) ? $HTTP_SESSION_VARS[$parameter_name] : $_SESSION[$parameter_name];
    }
    //End CCGetSession

    //CCSetSession @0-0F088E96
    public function   CCSetSession($param_name, $param_value){
	    global $HTTP_SESSION_VARS,$_SESSION;
	    global ${$param_name};
	    if(session_is_registered($param_name))
		    session_unregister($param_name);
	    ${$param_name} = $param_value;
	    session_register($param_name);
	    $HTTP_SESSION_VARS[$param_name] = $param_value;
	    $_SESSION[$param_name] = $param_value; 
    }
    //End CCSetSession




    //CCStrip @0-34A0B0A2
    public function   CCStrip($value){
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
    public function   CCGetParam($parameter_name, $default_value = ""){
	    global $HTTP_POST_VARS,$HTTP_GET_VARS, $_POST,$_GET ;

	    $parameter_value = $$parameter_name;
	    if(isset($HTTP_POST_VARS[$parameter_name]) || isset($_POST[$parameter_name])){
		    $parameter_value = $this->CCStrip($HTTP_POST_VARS[$parameter_name])?$this->CCStrip($HTTP_POST_VARS[$parameter_name]) :$_POST[$parameter_name] ;
	    }elseif(isset($HTTP_GET_VARS[$parameter_name]) || isset($_GET[$parameter_name]))
		    $parameter_value = $this->CCStrip($HTTP_GET_VARS[$parameter_name])?$this->CCStrip($HTTP_GET_VARS[$parameter_name]) : $_GET[$parameter_name];
	    else
		    $parameter_value = $default_value;
		    
	    return trim($parameter_value);
    }
    //End CCGetParam
	    
	    
    //CCDLookUp @0-23CF9CA0
    public function   CCDLookUp($field_name, $table_name, $where_condition, $Ora_Db){
      $sql = "SELECT " . $field_name . ($table_name ? " FROM " . $table_name : "") . ($where_condition ? " WHERE " . $where_condition : "");
      return CCGetDBValue($sql, $Ora_Db);
    }
    //End CCDLookUp


    // Get curretn Refferer
    public function   CCGetLogonStatus(){
	     return $this->CCGetSession("LogonSuccess");
    }
    // End set Current Receipt No.


    // A public function  to dislay Debug Message
    public 	public function  DisplayDebugMessage($message){
	    if (CCGetUserID()== 'PKARIUKI')
	    {
		    return "<h4> $message </h4>" ;
	    }
    }
    // End public function  to display debug message


    public function   CCSetLogonStatus($Setvalue){
	     $this->CCSetSession("LogonSuccess", $Setvalue);
    }
    // End set Current Receipt No.



    //CCServerAccess 
    public 	public function  CCServerAccess($Ora_Db){
		    $pingserver =  `ping -c 1 $Ora_Db->DBHost`;

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

    public function   initcap($StringVar){
	    return ucwords(strtolower($StringVar));
    }

    //End 
    public function   CheckLoggon(){
	    $IsLogedOn = $this->CCGetSession("LogonSuccess")?$this->CCGetSession("LogonSuccess"):$this->CCGetSession("smisLogonSuccess") ;
	    $Redirect = 'http://' . $_SERVER["HTTP_HOST"] . '/index.php ';
	    if ($IsLogedOn == 'LOGGEDIN' || $IsLogedOn === TRUE || $IsLogedOn == TRUE  )
	    {
		    $this->SetError('');
	       return 1;	
	    }else{
		    //print " <h1>Loggedin = $IsLogedOn </h1>" ;  exit;
		    @header("Location: $Redirect");
		    exit;
		    return 0;
		    
	    }
		    
    }

    public 	public function  print_r_html($arr, $style = "display: none; margin-left: 10px;"){ 
       static $i = 0; $i++;
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
    public function  print_html_r2( $aData ) { 
       echo nl2br( eregi_replace( " ", " ", print_r( $data, TRUE ) ) );    
    } 

    //from Smis Main Page
    public function   getParameter($paramName) {
	    global $HTTP_GET_VARS;
	    global $HTTP_POST_VARS;
	    $paramValue="";
	    $paramValue = (!empty($HTTP_GET_VARS[$paramName])) ? $HTTP_GET_VARS[$paramName] : "";
	     if(!($paramValue))
		     $paramValue = (!empty($HTTP_POST_VARS[$paramName])) ? $HTTP_POST_VARS[$paramName] : "";
	    $paramValue = trim($paramValue);
	    return $paramValue;
    }

    public function  set_docheader_center($psubtitle) {
	    $retheadcontents = "";
	    //CheckLoggon(); 
	    global $Ora_Db;
	    return 1;
    }

    public function  set_footercontents() {
		    $this->CCSetSession("uon_email", $StudentInfo['uon_email']);
		    $this->CCSetSession("sec_email", $StudentInfo['sec_email']);		$retfootcontents = "";
	    $retfootcontents .= "</TD> </TR>\n";
	    $retfootcontents .= " <TR align=\"left\" > <TD class=\"footer_info\"> \n";
	    $retfootcontents .= "<hr size=2 color=\"#004E73\" width=\"100%\"> \n";
	    $retfootcontents .= "<p><center>If there are any errors/ommissions or comments, please contact \n";
	    $retfootcontents .= "<a href=/websmis/email.php>SMIS Team , missupport@uonbi.ac.ke </a></center></p> \n";
	    $retfootcontents .= "</TD> </TR>\n";
	    $retfootcontents .= " </TABLE> \n";
	    return $retfootcontents;
    }

    public function  docClosingTags() {
	    $RetClosingTags = "";
	    $RetClosingTags .= "</LEFT> </BODY> </HTML> ";
	    return $RetClosingTags;
    }

    public function  ClosingTags() {
	    $RetClosingTags = "";
	    $RetClosingTags .= "</TD> </TR>\n";
	    $RetClosingTags .= " </TABLE> \n";

	    $RetClosingTags .= "</CENTER> </BODY> </HTML> ";
	    return $RetClosingTags;
    }


    public function ConvertTimeStampToOracleDate($TimeInTimeStamp){
	    return date("d-M-Y",$TimeInTimeStamp);
    }

    public function ConvertTimeStampToMysqlDate($TimeInTimeStamp){
	    return date("Y-m-d",$TimeInTimeStamp);
    }
	    

    public function conversecondtoDays($TimeinSeconds){
	    $SecondsInOneDay = 60*60*24 ;
	    return floor($TimeinSeconds/$SecondsInOneDay);
    }

    public function convertdaysToSeconds($TimeinDays){
	    $SecondsInOneDay = 60*60*24 ;
	    return $TimeinDays * $SecondsInOneDay ;
    }

    public function ConvertOracledate($OracleDate){
	    list($day,$Month,$Year) = split('[-]',$OracleDate);
	    $NumericMonth = $this->ConvertMonthtoNumeric($Month) ;
		    $DateTimestamp = mktime(0,0,0,$NumericMonth,$day,$Year);
	    return $DateTimestamp;
    }
    public function ConvertMysqldate($MysqlDate){
	    list($Year,$Month,$day) = split('[-]',$MysqlDate);
	    $NumericMonth = $this->ConvertMonthtoNumeric($Month) ;
	    
	    $DateTimestamp = @mktime(0,0,0,$NumericMonth,$day,$Year);
	    return $DateTimestamp;
    }

    public function ConvertMonthtoNumeric($Month){
	    $Month = trim(strtoupper($Month));
	    switch($Month){
		    case 'JAN':
			    $Mon = 1;
		    break;
		    case 'FEB':
			    $Mon = 2;
			    
		    break;
		    case 'MAR':
			    $Mon = 3;
		    break;
		    case 'APR':
			    $Mon = 4;
		    break;
		    case 'MAY':
			    $Mon = 5;
		    break;
		    case 'JUN':
			    $Mon = 6;
		    break;	
		    case 'JUL':
			    $Mon = 7;
		    break;
		    case 'AUG':
			    $Mon = 8;
		    break;
		    case 'SEP':
			    $Mon = 9;
		    break;
		    case 'OCT':
			    $Mon = 10;
		    break;
		    case 'NOV':
			    $Mon = 11;
		    break;
		    case 'DEC':
			    $Mon = 12;
		    break;	
		    default:
		    $Mon = $Month ;					
	    }
	    return (int)$Mon ;
    }

    public function ConvertNumerictoVarchar($NumSem){
	    $Semester = '' ;
	    switch($NumSem){
		    case 1:
			    $Semester = 'FIRST';
		    break;
		    case 2:
			    $Semester = 'SECOND';
		    break;
		    CASE 3:
			    $Semester = 'THIRD';
		    break;		
		    CASE 4:
			    $Semester = 'FOURTH';
		    break;	
		    default:
			    $Semester = 'FIFTH';	
		    break;
	    }
	    return   $Semester ;
    }

    public function AddAvailableRooms($HallCode,$RoomNO,$StartDate,$EndDate,$AcademicYear,$Slot){


	    $CheckDuplicateSQL = "select hall_code from hamis.available_rooms
						    where (hall_code='$HallCode' 
						    and room_no='$RoomNO'
						    and date(start_date)=date('$StartDate')
						    and date(end_date) ='$EndDate'  
						    and academic_year='$AcademicYear' 
						    and slot ='$Slot') " ;

	    //print "<h2>$CheckDuplicateSQL</h2>";
	    
	    $DuplicateHallCode =  $this->MysqlHamisDB->GetOne($CheckDuplicateSQL);

	    //print "<h2>CheckDuplicate = " .$DuplicateHallCode." </h2>";
	    if(!strlen($DuplicateHallCode))	{							
		    $AddAvailableRoomSQL = "insert into hamis.available_rooms(hall_code,room_no,start_date,end_date,academic_year,slot,status,reserve_group)  
								    values('$HallCode','$RoomNO','$StartDate','$EndDate','$AcademicYear','$Slot','AVAILABLE',0)"; 
								    
		    //print "<h3>$AddAvailableRoomSQL</h3>";// exit;
		    $Result = $this->MysqlHamisDB->Execute($AddAvailableRoomSQL);
		    
		    if($Result){
			    //print "<h4>Added $HallCode,$RoomNO,$StartDate,$EndDate</h4> " ;
			    //$AvaiableRoomsByHall .= "<tr><td>$HallCode</td><td>$RoomNO</td><td>$StartDate</td><td>$EndDate</td><td>$AcademicYear</td><td>$Slot</td><td>Added</td></tr>"; 
			    return TRUE;
		    }else{
			    //print "<h4>Not Added $HallCode,$RoomNO,$StartDate,$EndDate</h4> " ;
			    //$AvaiableRoomsByHall .= "<tr><td>$HallCode</td><td>$RoomNO</td><td>$StartDate</td><td>$EndDate</td><td>$AcademicYear</td><td>$Slot</td><td><font color=\"red\">Not Added</font></td></tr>"; 
			    return FALSE;
		    }
	    } // If The slot is not a duplicate
	    else{
		    //print "<h4>Duplicate $HallCode,$RoomNO,$StartDate,$EndDate</h4> " ;
		    //$AvaiableRoomsByHall .= "<tr><td>$HallCode</td><td>$RoomNO</td><td>$StartDate</td><td>$EndDate</td><td>$AcademicYear</td><td>$Slot</td><td><font color=\"red\">Duplicate</font></td></tr>"; 
		    return  FALSE;
	    }
    }

    public function DisableavailableSlot($HallCode,$RoomNO,$StartDate,$EndDate,$AcademicYear,$Slot){
		     $UpdateSQl = "UPDATE hamis.available_rooms set status = 'NOT AVAILABLE'  
				      WHERE (hall_code='$HallCode' 
						    and room_no='$RoomNO'
						    and (date(start_date)=date('$StartDate')
						    and date(end_date) =date('$EndDate')) 
						    and academic_year='$AcademicYear' 
						    and slot ='$Slot')";
						    
		    $Result =  $this->MysqlHamisDB->Execute($UpdateSQl);
    }

    public function  authenticate($Username,$Passsword){
	    $SQl = "SELECT
		    registration_number
		    , national_id
		    , other_names
		    , surname
		    , prog_id
		    , primary_mobile
		    , sec_mobile
		    , primary_email
		    , sec_email
		    , uon_email
	    FROM
		    smis.students
	    WHERE (password ='$Passsword' and registration_number ='$Username')";
	    
	    $StudentInfo = $this->MysqlSmisDB->GetRow($SQl);
	    if(strlen($StudentInfo['registration_number'])){
		    $this->CCSetSession("RegNo", $StudentInfo['registration_number']);
		    $this->CCSetSession("prog_id", $StudentInfo['prog_id']);
		    
		    $MobileNo =  ($StudentInfo['primary_mobile'] != NULL && strlen($StudentInfo['primary_mobile']))  ? $StudentInfo['primary_mobile'] : $StudentInfo['sec_mobile']  ; 
		    $this->CCSetSession("primary_mobile", $MobileNo);
			    
		    $Email = ($StudentInfo['uon_email'] != NULL && strlen($StudentInfo['uon_email']))  ? $StudentInfo['uon_email'] : $StudentInfo['primary_email']  ;
		    $Email = ($Email != 'NULL' && strlen($Email))  ? $Email : $studentInfo['sec_email ']  ;

	    
		    $this->CCSetSession("primary_email", $StudentInfo['primary_email']);
		    $this->CCSetSession("uon_email", $StudentInfo['uon_email']);
		    $this->CCSetSession("Email", $Email);
		    $this->CCSetSession("sec_email", $StudentInfo['sec_email']);
		    $this->CCSetSession("surname", $StudentInfo['surname']);
		    $this->CCSetSession("otherNames", $StudentInfo['other_names']);
		    $this->CCSetSession("LogonSuccess", 'LOGGEDIN');  
		    $this->CCSetSession("Password", $Passsword);  
		    $this->CCSetSession("Username", $Username); 
		    $this->CCSetSession("MobileNo", $MobileNo);
		    //$this->CCSetLogonStatus(1);
		    $LevelofStudy = $this->getCurrentStudyLevel($StudentInfo['registration_number'],$this->Academicyear);
	    
		    $OtherStudDetails = $this->getOtheStudentdetails($StudentInfo['registration_number']);
		    $StudentAddress =  $OtherStudDetails['STUDENT_ADDRESS']  ;
		    $CourseDuration = $OtherStudDetails['DURATION']  ;
		    
		    if(is_numeric($CourseDuration) && is_numeric($LevelofStudy))
			    if($LevelofStudy  > $CourseDuration )
				      $LevelofStudy = 0;
		    
	       // $fp =fopen("logs/debugmessing.txt",'w');
	    
	       // fwrite($fp,"Returned Level of Study for $Username LevelofStudy =  $LevelofStudy, Duration = $CourseDuration");
		    // fclose($fp);	
		      
		    $AddressRecord = split('[ ]',$StudentAddress);
		    if(count($AddressRecord)>=3){
			    $PostalCode =$AddressRecord[1] ;
			    $PostalAddress =$AddressRecord[0] ;
		    }elseif(count($AddressRecord)<=2) {
		       $PostalAddress =$AddressRecord[0] ;	
		    }
		    $this->CCSetSession("DegreeDuration", $CourseDuration);
		    $this->CCSetSession("PostalAddress", $PostalAddress);
		    $this->CCSetSession("PostalCode", $PostalCode);  
			    
		    $this->CCSetSession("LevelofStudy", $LevelofStudy);
		    $this->CCSetSession("gender", $OtherStudDetails['SEX']);
		    $this->CCSetSession("DegCode", $OtherStudDetails['D_PROG_DEGREE_CODE']);
		    $this->CCSetSession("StudentCategory", $OtherStudDetails['STC_STUDENT_CATEGORY_ID']);     
		    $SetStudentsSessions = $this->GetStudentSesions($OtherStudDetails['D_PROG_DEGREE_CODE'],$this->Academicyear,$LevelofStudy,$OtherStudDetails['SEX']);
		    $GroupAllotment = $this->GetGroupRoomsAllotement($OtherStudDetails['D_PROG_DEGREE_CODE'],$OtherStudDetails['SEX'],$LevelofStudy,$this->Academicyear)	;	
		    $ReserveGroupMember = $this-> GetReserveGroupMembership($Username)    ;
		    $this->CCSetSession("ReserveMembership", $ReserveGroupMember);   
		    return $StudentInfo; 
	    }
	    else{ 
		    $this->CCSetLogonStatus(FALSE);
		    session_destroy();
		    return FALSE;
	    }
	    
    }
	
	
	public function moduleIIStudAppForm($RegistrationNo,$MobileNo,$Gender,$DegreeCode,$LevelofStudy,$UonEmail, $AcademicYear, $ApplicationStatus,$vcategoryId)
	{
		//global $db;
		/* $Surname = $this->CCGetSession("surname");

		$OtherNames = $this->CCGetSession("otherNames");
		$FullNames = ucwords(strtolower($Surname." ".$OtherNames));
		$LevelofStudy = $this->CCGetSession("LevelofStudy");
		$MobileNo    = $this->CCGetSession("primary_mobile");
		$UonEmail = $this->CCGetSession("Email");
			
		$ApplicationForm  = '' ;
		$PostMobileNo = $this->CCGetParam("PostMobileNo") ? $this->CCGetParam("PostMobileNo") : $MobileNo ;
		$PostEmail = $this->CCGetParam("PostEmail") ? $this->CCGetParam("PostEmail") : $UonEmail;
		$PostalCodeFld = $this->CCGetParam("PostalCodeFld");
		$PostalAddressFld = $this->CCGetParam("PostalAddressFld");
		$RoomTypePreference = $this->CCGetParam("RoomTypePreference");
		$AppLevelofStudy = $this->CCGetParam("AppLevelofStudy") ? $this->CCGetParam("AppLevelofStudy") : $LevelofStudy ;*/
		
		$StudentRecord = $this->getSMISStudentDetails($RegistrationNo,$AcademicYear) ;
	
		//$vcategoryId =  CCGetParam("vcategoryId");
		
		
		
		if(!strlen($StudentRecord['DEGREE_CODE']))
			$StudentDoesExist = TRUE;
		else
		   $StudentDoesExist = FALSE;
				
		if($StudentRecord['CATEGORY'] == '001')
			$ModuleIIStudents = FALSE;
			
		else
			$ModuleIIStudents = TRUE;
			
		
		$Names =  $StudentRecord ['NAMES']  ;
		$DegreeCode = $StudentRecord ['DEGREE_CODE']  ;																					;
		//Fetch the Applicants Information		
		$ApplicantPersonalInfo = $this->MysqlHamisDB->GetRow("SELECT * FROM hamis.room_applicant  
											  WHERE (registration_number = '$vregno' 
												  AND academic_year='$AcademicYear')");
	
		$vregno = $RegistrationNo;
		$ApplicantID = $ApplicantPersonalInfo['applicant_id'];	
		$AcademicYear = $ApplicantPersonalInfo['academic_year'] ? $ApplicantPersonalInfo['academic_year'] : $AcademicYear;	
		$vlevel = $ApplicantPersonalInfo['level_of_study'] ? $ApplicantPersonalInfo['level_of_study'] : $StudentRecord['LEVEL_OF_STUDY'] ;	
		$vmobile = $ApplicantPersonalInfo['mobile_number'] ?$ApplicantPersonalInfo['mobile_number'] : $StudentRecord['MOBILE'];	
		$vemail = $ApplicantPersonalInfo['email'] ? $ApplicantPersonalInfo['email'] : $StudentRecord['EMAIL'];;		
		$vpostcode = $ApplicantPersonalInfo['postal_code'] ? $ApplicantPersonalInfo['postal_code'] : $StudentRecord['POSTAL_CODE'];
		$vpostaddress = $ApplicantPersonalInfo['postal_address'] ? $ApplicantPersonalInfo['postal_address'] : $StudentRecord['POSTAL_ADDRESS'];		
		$AppDate = $ApplicantPersonalInfo['date_applied']?$ApplicantPersonalInfo['date_applied'] : date('d-M-Y');		
		$Apptime = $ApplicantPersonalInfo['time_applied'];	
		$vstatus = $ApplicantPersonalInfo['room_application_status'];	
		$vpreference = $ApplicantPersonalInfo['room_type_preference'];	
		$ApplicationPostion = $ApplicantPersonalInfo['application_position'];
		$vapplicantyr = $ApplicantPersonalInfo['Applicant_year'];	
		$vgender = $ApplicantPersonalInfo['gender'] ? $ApplicantPersonalInfo['gender'] : $StudentRecord ['GENDER'];	
		$synchronizationflag = $ApplicantPersonalInfo['synchronization_flag'];
		//$vcategoryId = $ApplicantPersonalInfo['category_id'] ? $ApplicantPersonalInfo['category_id'] :$vcategoryId;
		
		//print $vcategoryId."kkk";
		
		
		//$SMUlist = $this->MysqlHamisDB->GetArray("SELECT SMU_CODE,SMU_NAME FROM SWA.SMUS WHERE SMU_CODE IN('9','10')	ORDER BY  SMU_NAME ASC");
			$htmlSelect .= '<option value="9" >Select Location</option>';
			
			$Selected = 'selected';
			if($vallsmu== 9)
				$htmlSelect .= '<option value="9" $Selected>LOWER KABETE</option>';
			else
				$htmlSelect .= '<option value="9">LOWER KABETE</option>';
				
			if($vallsmu== 10)
				$htmlSelect .= '<option value="10" $Selected>KIKUYU</option>';
			else
				$htmlSelect .= '<option value="10" >KIKUYU</option>';
			
		$phpSelf = $_SERVER['PHP_SELF'];
		$form ='<form name="ApplicationForm"  method="post" action='.$phpSelf.'  onSubmit="page_OnSubmit();">';
		$form .='<table width="100%" cellpadding=0 cellspacing=0 border=0>';
		$form .='<tr><td width="99%" align=center><basefont size=3><div align="center"><div class="TabView" id="TabView">';
		$form .='<div class="Pages" style="width:$ScreenWidthpx; height:$Screenheightpx; align: left;">';
		$form .='<div class="Page">';
	    $form .='<div class="Pad">';
		$form .='<table  cellspacing="1" cellpadding="3" class="FacetFormTABLE" align="center">';
		$form .='<tr><td colspan="4" class="FacetFormHeaderFont"><span class="style3 style5"><strong>Applicant\'s Personal Details</strong></span></td></tr>';
		$form .='<tr><td class="FacetColumnTD"><strong>Names</strong></td><td colspan="3" class="FacetDataTD">&nbsp;<font color ="orange"><b>"'.$Names.'"</b></font></td></tr>';
		$form .='<tr><td class="FacetColumnTD"><strong>Registration Number <strong><span class="asteriks" >*</span></strong></strong></td>';
		$form .='<td class="FacetDataTD"><input name="vregno" type="text" class="FacetInput" id="vregno" value="'.$vregno.'" size="20" readonly="true">';
		$form .='<input name="ApplicantID" type="hidden" id="ApplicantID" value="'.$ApplicantID .'"></td>';
		$form .='<td class="FacetColumnTD"><strong>Academic Year <strong><span class="asteriks">*</span></strong></td>';
		$form .='<td class="FacetDataTD"><input name="AcademicYear" type="text" class="FacetInput" id="AcademicYear" value="'.$AcademicYear.'" size="20" readonly="true"></td></tr>';
		//$form .='<input name="vlevel" type="hidden" id="vlevel" value="'.$vlevel .'">		';
		$form .='<tr><td class="FacetColumnTD"><strong>Gender</strong></td>
			<td class="FacetDataTD" colspan =0>
			<select name="vgender" class="FacetSelect" id="vgender">
				$MaleSelected ="";
				$FemaleSelected ="" ;
				if($vgender  =="M")
					$MaleSelected = " SELECTED " ;
				elseif($vgender  =="F")
					$FemaleSelected = " SELECTED " ;
				<OPTION value="M" $MaleSelected>MALE </OPTION>
				<OPTION value="F" $FemaleSelected>FEMALE </OPTION>
			</select></td><td class="FacetColumnTD"><strong>Mobile Number</strong></td>
			<td class="FacetDataTD"><input name="vmobile" type="text" class="FacetInput" id="vmobile" value="'.$vmobile.'" size="15" maxlength="10"></td></tr>';
		$form .='<tr>
			<td class="FacetColumnTD"><strong>Postal Adress <strong><span class="asteriks">*</span></strong></strong></td>
			<td class="FacetDataTD" cclass="FacetDataTD"><input name="vpostaddress" type="text" class="FacetInput" id="vpostaddress" value="'.$vpostaddress.'" size="25"></td>
			<td class="FacetColumnTD"><strong>Postal Code <span class="asteriks">*</span></strong></td>
			<td class="FacetDataTD"><input name="vpostcode" type="text" class="FacetInput" id="vpostcode" value="'.$vpostcode.'" size="20"></td>
		</tr>';
		 $form .='<tr>
			<td class="FacetColumnTD"><strong>E-mail</strong></td>
			<td class="FacetDataTD"><input name="vemail" type="text" class="FacetInput" id="vemail" value="'.$vemail.'" size="40">
			
		  </tr>';
		 $form .='<tr><td colspan="4" class="FacetFormHeaderFont"><hr width="100%"></td> </tr>';
		 $form .='<tr><td colspan="4" class="FacetColumnTDSeperator"><strong>Applicant Session Dates</strong></strong></td></tr>';					
		 $form .='<tr>
			<td class="FacetColumnTD"><strong>Start Date<strong><span class="asteriks">*</span></strong></strong></td>
			<td class="FacetDataTD">
			<input name="StartDate" type="text" class = "alignImage" id="StartDate" value="'.$StartDate.'" size="15">
			 <a class="FacetDataLink" href="javascript:showDatePicker(\'DateObj\',\'ApplicationForm\',\'StartDate\');"><img border="0" class = "alignImage" src="/hamis/Themes/DatePicker/DatePicker1.gif"></a>	</td>
									 
			 <td class="FacetColumnTD"><strong>End Date<strong><span class="asteriks">*</span></strong></strong></td>
			<td class="FacetDataTD">
			<input name="EndDate" type="text" class = "alignImage" id="EndDate" value="'.$EndDate.'" size="15">
			 <a class="FacetDataLink" href="javascript:showDatePicker(\'DateObj\',\'ApplicationForm\',\'EndDate\');"><img border="0" class = "alignImage" src="/hamis/Themes/DatePicker/DatePicker1.gif"></a>	</td>
			</tr>';
		$form .='<tr> <td class="FacetColumnTD"><strong>How will you pay?<span class="asteriks">*</span></td>
				<td class="FacetDataTD" colspan="3">
				<select name="vpayment" class="FacetSelect" id="vpayment">
				
					$MonthlySelected ="";
					$LumpsumSelected = "" ;
					if($vpayment  =="PER MONTH")
						$MonthlySelected = " SELECTED " ;
					elseif($vpayment  =="LUMPSUM")
						$LumpsumSelected = " SELECTED " ;
				
					<OPTION value="LUMPSUM" $LumpsumSelected>LUMPSUM </OPTION>
					
					<OPTION value="PER MONTH" $MonthlySelected>PER MONTH </OPTION>
				</select>	</td></tr>';
		 $form .='<tr><td colspan="4" class="FacetFormHeaderFont"><hr width="100%"></td> </tr>';
		 $form .='<tr><td colspan="4" class="FacetColumnTDSeperator"><strong>Applicant Location choices</strong></td></tr>';
		 $form .='<tr> <td class="FacetColumnTD" > <strong>Location Choice  <span class="asteriks">*</span> </strong> </td> <td ><select name="vallsmu" id="vallsmu" multiple="multiple" size="5">';
		 
		 $SMUlist = $this->MysqlHamisDB->GetArray("SELECT SMU_CODE,SMU_NAME FROM hamis.smus WHERE SMU_CODE IN('9','10','12')	ORDER BY  SMU_NAME ASC");
						$Default = false;
							foreach($SMUlist as $SMUkey =>$SMUValue)
							{
								if($vallsmu== $SMUValue['SMU_CODE'])
								{
									$Default = true;
									$Selected = ' SELECTED ';
								}
								else
									$Selected = ' ';
								$form .='<option value='.$SMUValue['SMU_CODE'].' $Selected>'.$SMUValue['SMU_NAME'].'</option>';
							}
							if(!$Default)
							{
							
								$form .='<option value="0" SELECTED>Select SMU</option>';
							
							}
			 $form .='</select></td>'; 
			 $form .='<td colspan="2"><input type="button" name="smurightbutton" id="smurightbutton" onClick="smurightbutton_OnClick();" value=">>" ><br>
						<input type="button" name="smuleftbutton" id="smuleftbutton" onClick="smuleftbutton_OnClick();" value="<<" >
						 <select name="vselectedsmu" id="vselectedsmu" multiple="multiple" size="5"> </select>
						 <input name="PickSmus" type="hidden" value="" id="PickSmus">
						 
						 </td></tr>';
		// $form .=$htmlSelect;
		
		 
		
		  $form .='<tr><td colspan="4" class="FacetFormHeaderFont"><hr width="100%"></td> </tr>';	
		  $form .='<tr> <input type="hidden" name="vcategoryId" value='.$vcategoryId.'>
			<td colspan="4" class="FacetDataTD" align="center"><input type="submit" name="BtnRoomModuleIIApplication" value="Save Applicant Details" class="FacetButton" onClick="window.document.ApplicationForm.VisibleTab.value=\"1\"; window.document.ApplicationForm.submit();"></td>
		  </tr>';
		  $form .='</table></div></div>';			
		  $form .='</form>';
		return $form;
	}
	
	
	public function getSMISStudentDetails($RegistrationNo,$AcademicYear){
		//global $MysqlsmisDB ;
		///Get Applicant Name and Level of Study
		/*$StudentsRecord = $this->$MysqlsmisDB->GetRow("SELECT UON_STUDENTS.STC_STUDENT_CATEGORY_ID, UON_STUDENTS.D_PROG_DEGREE_CODE, UON_STUDENTS.SEX,
									UON_STUDENTS.STUDENT_ADDRESS
									FROM MUTHONI.UON_STUDENTS
									WHERE  UON_STUDENTS.REGISTRATION_NUMBER='$RegistrationNo'") ;
									
	   
		 $AddressRecord = trim(CleanAddress($StudentsRecord['STUDENT_ADDRESS']));
		 if(eregi('([0-9A-Za-z]{2,30})([#,:;* &-])([0-9A-Za-z]{0,30})([#,:;* &-])([0-9A-Za-z]{0,30})',$AddressRecord,$StudentAddress ))	 
			if(strlen($StudentAddress[1])>=2){
				$PostalCode =$StudentAddress[3] ;
				$PostalAddress =$StudentAddress[1] ;
			}elseif(count($StudentAddress[1])<=2) {
			   $PostalAddress = $StudentAddress[0] ;	
			}
			
		$ApplicantNominalDatails['LEVEL_OF_STUDY'] = 	getCurrentStudyLevel($RegistrationNo,$AcademicYear)    ;
		  
		$ApplicantNominalDatails['DEGREE_CODE'] = 	$StudentsRecord ['D_PROG_DEGREE_CODE']; 
		$ApplicantNominalDatails['POSTAL_CODE'] = 	$PostalAddress;  
		$ApplicantNominalDatails['POSTAL_ADDRESS'] = 	$PostalCode; 
		$ApplicantNominalDatails['CATEGORY'] =$StudentsRecord ['STC_STUDENT_CATEGORY_ID'];  */
		
		$StudentsRecord = $this->MysqlSmisDB->GetRow("select surname,other_names,primary_mobile, sec_mobile, primary_email,sec_email,uon_email,gender FROM smis.students WHERE registration_number ='$RegistrationNo'");
		$ApplicantNominalDatails['GENDER'] = 	$StudentsRecord ['gender'];
		$ApplicantNominalDatails['LEVEL_OF_STUDY'] = 	getCurrentStudyLevel($RegistrationNo,$AcademicYear)    ; 
		$ApplicantNominalDatails['NAMES']  = $StudentsRecord['surname'] . ' ' .$StudentsRecord['other_names'];	
		$ApplicantNominalDatails['EMAIL']  = $StudentsRecord['primary_email'] ? $StudentsRecord['primary_email']  : $StudentsRecord['uon_email'] ;
		$ApplicantNominalDatails['EMAIL'] = $ApplicantNominalDatails['EMAIL'] ? $ApplicantNominalDatails['EMAIL'] : $StudentsRecord['sec_email'] ;
		$ApplicantNominalDatails['MOBILE']  = $StudentsRecord['primary_mobile'] ? $StudentsRecord['primary_mobile']  : $StudentsRecord['sec_mobile'] ;	
		
		return   $ApplicantNominalDatails;
				
	}
	
	function CleanAddress($Address){
		$Address = str_replace('P.O','',$Address);
		$Address = str_replace('BOX','',$Address); 
		$Address = str_replace('  ',' ',$Address); 
		return trim($Address);
	}
	
    function GetStudentSesions($DegreeCode,$academicYear,$YoS){
	    global $db;
	    //$fp =fopen("logs/debugmessing.txt",'a+');
	    $StudentcAcademicSessionSQL = "SELECT
									    SEMESTER
									    , START_DATE
									    , END_DATE
									    , BOOKING_START_DATE
									    , BOOKING_END_DATE
									    , ACADEMIC_YEAR
								       ,(CASE SEMESTER WHEN 'FIRST' THEN 1 WHEN 'SECOND' THEN 2 WHEN 'THIRD' THEN 3 WHEN 'FOURTH' THEN 4 ELSE 5 END) AS NUMSEM 
								    FROM
									    hamis.student_academic_sessions
								    WHERE DEGREE_CODE = '$DegreeCode' 
									    AND YEAR_OF_STUDY = $YoS
									    AND ACADEMIC_YEAR  = '$academicYear'
								    ORDER BY  NUMSEM ASC" ;
	    //fwrite($fp,$StudentcAcademicSessionSQL);								
	    $StudentcAcademicSessionDates = $this->MysqlHamisDB->GetArray($StudentcAcademicSessionSQL);
	    
	    if(!$StudentcAcademicSessionDates)
		    return FALSE;
		    
	    foreach($StudentcAcademicSessionDates as $SessionKey=>$SessionInfo){
		    $SessionsStudent[$SessionInfo['NUMSEM']]['START_DATE'] = $SessionInfo['START_DATE'] ;
		    $SessionsStudent[$SessionInfo['NUMSEM']]['END_DATE'] = $SessionInfo['END_DATE'] ;
	    }

	    if(is_array($SessionsStudent)){
		    $this->CCSetSession("StudentSessions",$SessionsStudent);  
		    return $SessionsStudent ;
	    }else {
		    $this->SetError($this->GetError() .  "<br> The Session for DegreeCode = $DegreeCode, in AcademicYear = $academicYear,YoS = $YoS is not Available ");  
		    return FALSE;
	    } 
	    //fclose($fp);
    }

    function GetReserveGroupMembership($RegistrationNo){
	    $SQL= "SELECT CATEGORY_ID 
			    FROM hamis.student_special_cases
			    WHERE REGISTRATION_NUMBER = '$RegistrationNo' 
			    ORDER BY  DATE_ENTERED DESC "  ;
			    
	    $ReserveGroup = $this->MysqlHamisDB->GetOne($SQL); 
	    
	    if($ReserveGroup !== FALSE && strlen($ReserveGroup))  
		    return ",$ReserveGroup"  ;
	    else
	     return '';
	    
    }


    function GetGroupRoomsAllotement($DegreeCode,$Gender,$LevelofStudy,$AcademicYear){
	    
		    if($Gender=='M')
			    $SelectGender = 'male' ;
		    elseif($Gender=='F')
			    $SelectGender = 'female' ;
			    
	       $GroupRoomsAllotmentSQL = "SELECT  accommodation,	$SelectGender as GenderAllocation 
								      FROM hamis.group_room_allocation  
								      WHERE (degree_code = '$DegreeCode'  AND 	level_of_study = '$LevelofStudy' 	AND academic_year = '$AcademicYear') ";
								      
		    $GroupRoomsAllotmentInfo = $this->MysqlHamisDB->GetRow($GroupRoomsAllotmentSQL);   
		    $this->CCSetSession("GenderAllotment",$GroupRoomsAllotmentInfo['GenderAllocation']); 
		    $this->CCSetSession("GroupAllotment",$GroupRoomsAllotmentInfo['accommodation']); 
		    return  $GroupRoomsAllotmentInfo;
    }

    public function GetCurrentGroupOccupation($DegreeCode,$Gender,$LevelofStudy,$AcademicYear){
		     $CurrentGroupOccupationSQL = "SELECT count(distinct reg_no) AS currently_booked
										    FROM hamis.student_room_bookings
										    WHERE degree_code='$DegreeCode' 
											      AND level_of_study='$LevelofStudy' 
											      AND academic_year='$AcademicYear'
											      AND gender='$Gender'
										    and (booking_status='BOOKED' OR booking_status='CONFIRMED')"  ;
		     $CurrentGroupOccupation =  $this->MysqlHamisDB->GetOne($CurrentGroupOccupationSQL);
			 
		     return $CurrentGroupOccupation;
    }

    function CheckOneroomScenerio($StudentSessions,$Gender,$DegCode,$YoS){
	    $CompersionFields = "available_rooms.hall_code, available_rooms.room_no, available_rooms.slot " ;
	    $Reservemember = $this->CCGetSession("ReserveMembership");     	
	    $OneSemesterSQL = "SELECT distinct available_rooms.hall_code, available_rooms.room_no, available_rooms.slot
					    FROM hamis.available_rooms,hamis.degree_halls 
					    WHERE (degree_halls.gender ='$Gender'
						    AND degree_halls.degree_code ='$DegCode'
						    AND degree_halls.year_of_study = $YoS
						    AND status ='AVAILABLE' and reserve_group  in(0,$Reservemember))
						    and (available_rooms.hall_code = degree_halls.hall_code)" ;
						    
	       if(is_array($StudentSessions)){	
		    asort($StudentSessions);
		    $WhereClause = '';
		    $OuterLoopCounter = 1;
		    
		    $IntersectSQL ='';
		    if(is_array($StudentSessions))
		    foreach($StudentSessions as $SemesterKey => $SemesterInfo){
			    $InnerLoopCount = 1;
			    $WhereClause = '';
			    if(is_array($SemesterInfo))
			    foreach($SemesterInfo as $key => $value){	
				    if(!strlen($WhereClause)){
					    if($key =='START_DATE')
						    $WhereClause .= "( hamis.available_rooms." . strtolower($key) ."<='". $value .  "')" ; 
					    elseif($key =='END_DATE')
						    $WhereClause .= "( available_rooms." . strtolower($key) . ">='" .$value .  "')" ; 
				    }else{
					    if( $key=='START_DATE'){
						    if($InnerLoopCount ==1)
							    if($OuterLoopCounter > 1)
								    $WhereClause .= " and (available_rooms." .strtolower($key) ."<='". $value  .  "')" ; 
							    else
							      $WhereClause .= " AND (available_rooms." .strtolower($key) ."<='". $value  .  "')" ;  	
						    else
							    $WhereClause .= " AND (available_rooms." .strtolower($key) ."<='". $value  .  "')" ; 
					    }elseif( $key=='END_DATE'){
						    if($InnerLoopCount ==1)
							    if($OuterLoopCounter >1 )
								    $WhereClause .= " and (available_rooms." .  strtolower($key) .">='". $value  .  "')" ;
							    else
								    $WhereClause .= " AND ((available_rooms." .  strtolower($key) .">='". $value  .  "')" ;    
						    else
							    $WhereClause .= " AND (available_rooms." .strtolower($key) .">='". $value  .  "')" ;
					    }	
				    }
				    $InnerLoopCount++;
		    }  
			
		    if(strlen($WhereClause)){
			    if(strlen($IntersectSQL))
				     $IntersectSQL .= ' AND (' . $CompersionFields .') IN (' . 
									    $OneSemesterSQL . ' AND ('. $WhereClause .')
									    )'   ;
			    else 
				      $IntersectSQL .=  $OneSemesterSQL . ' AND ('. $WhereClause .')'   ; 
		    }
		     $OuterLoopCounter++;  
	    } // outer foreach	  	
	    }else{ // if Sessions are in an Array
		    $this->SetError( $this->GetError() . " <br>There are no Available Session for this Student Sessions = " . $StudentSessions . ",Gender = $Gender,DegCode $DegCode,YoS = $YoS ");
		    return FALSE ; // No Session Information
	    }
					    
								     
	    if(strlen($IntersectSQL)){
		    $IntersectSQL .= " order by available_rooms.hall_code asc, available_rooms.room_no asc " ;
		    $AvailableRooms = $this->MysqlHamisDB->GetArray($IntersectSQL); 
		    return $AvailableRooms; 
	    }
	    else {
		    $this->SetError( $this->GetError() . " <br>IntersectSQL Variable is empty Student Sessions = " . $StudentSessions . ",Gender = $Gender,DegCode $DegCode,YoS = $YoS ");
		    return FALSE;
	    }
						    
    }


    function CheckManyroomScenerio($StudentsSessions,$Gender,$DegreeCode,$LevelofStudy){

	    $Reservemember = $this->CCGetSession("ReserveMembership");  
	    $OneSemesterSQL = "SELECT distinct available_rooms.hall_code, available_rooms.room_no, available_rooms.slot
					    FROM hamis.available_rooms,hamis.degree_halls 
					    WHERE (degree_halls.gender ='$Gender'
						    AND degree_halls.degree_code ='$DegreeCode'
						    AND degree_halls.year_of_study = $LevelofStudy
						    AND status ='AVAILABLE' and reserve_group  in(0, $Reservemember))
						    and (available_rooms.hall_code = degree_halls.hall_code)" ;
						    
	       if(is_array($StudentsSessions)){	
			    asort($StudentsSessions);
			    $WhereClause = '';
			    if(is_array($StudentsSessions))
			    foreach($StudentsSessions as $SemesterKey => $SemesterInfo){
				    $WhereClause = '';
				    if(is_array($SemesterInfo))
				    foreach($SemesterInfo as $key => $value){	
					    if(!strlen($WhereClause)){
						    if($key =='START_DATE')
							    $WhereClause .= "( hamis.available_rooms." . strtolower($key) ."<='". $value .  "')" ; 
						    elseif($key =='END_DATE')
							    $WhereClause .= "(available_rooms." . strtolower($key) . ">='" .$value .  "')" ; 
					    }else{
						    if( $key=='START_DATE')
									    $WhereClause .= " AND (available_rooms." .strtolower($key) ."<='". $value  .  "')" ; 
						    elseif($key=='END_DATE')
								    $WhereClause .= " AND (available_rooms." .strtolower($key) .">='". $value  .  "')" ;
					    }

			    }  
			    if(strlen($WhereClause)){
					     $ManySemesterSQL  =  $OneSemesterSQL . ' AND ('. $WhereClause .') order by available_rooms.hall_code asc, available_rooms.room_no asc'  ;
					     $AvailableRooms[$SemesterKey] = $this->MysqlHamisDB->GetArray($ManySemesterSQL); 
			    }else{ 
					    $this->SetError( $this->GetError() . " <br>CheckManyroomScenerio function : WhereClause Variable is empty Student Sessions = " . array_values($StudentSessions) . ",Gender = $Gender,DegCode $DegCode,YoS = $YoS ");
					     $AvailableRooms[$SemesterKey] = FALSE ; 
			    }
		    } // outer foreach	
		    return  $AvailableRooms ; 	
	    }else { // if Sessions are in an Array	
		    
		    $this->SetError( $this->GetError() . " <br> CheckManyroomScenerio Function : There are no Available Session for this Student Sessions = " . array_values($StudentSessions) . ",Gender = $Gender,DegCode $DegCode,YoS = $YoS ");
		    return FALSE;
	    }
	     return   FALSE;
    }

    function PrepareSessionClause($StudentSession){
		    if(is_array($StudentSession)){
		    asort($StudentSession);
		    $WhereClause = '';
		    $OuterLoopCounter = 1;
		    if(is_array($StudentSession))
		    foreach($StudentSession as $SemesterKey => $SemesterInfo){
			    $InnerLoopCount = 1;
			    if(is_array($SemesterInfo))
			    foreach($SemesterInfo as $key => $value){	
				    if(!strlen($WhereClause)){
					    if($InnerLoopCount ==1)
						    $WhereClause .= '(' ;
					    if($key =='START_DATE')
						    $WhereClause .= "( hamis.available_rooms." . strtolower($key) ."<='". $value .  "')" ; 
					    elseif($key =='END_DATE')
						    $WhereClause .= "( hamis.available_rooms." . strtolower($key) . "<='" .$value .  "')" ; 
				    }else{
					    if( $key=='START_DATE'){
						    if($InnerLoopCount ==1)
							    if($OuterLoopCounter > 1)
								    $WhereClause .= " OR (( hamis.available_rooms." .strtolower($key) ."<='". $value  .  "')" ; 
							    else
							      $WhereClause .= " AND (( hamis.available_rooms." .strtolower($key) ."<='". $value  .  "')" ;  	
						    else
							    $WhereClause .= " AND ( hamis.available_rooms." .strtolower($key) ."<='". $value  .  "')" ; 
					    }elseif( $key=='END_DATE'){
						    if($InnerLoopCount ==1)
							    if($OuterLoopCounter >1 )
								    $WhereClause .= " OR (( hamis.available_rooms." .  strtolower($key) .">='". $value  .  "')" ;
							    else
								    $WhereClause .= " AND (( hamis.available_rooms." .  strtolower($key) .">='". $value  .  "')" ;    
						    else
							    $WhereClause .= " AND ( hamis.available_rooms." .strtolower($key) .">='". $value  .  "')" ;
							     
				    }	
			    }
			    $InnerLoopCount++;
		    }  
		     $WhereClause .= ')' ;
		     $OuterLoopCounter++;  
	    } // outer foreach	  	
	    } // if Sessions are in an Array
	    
	    return $WhereClause ;
	    }
	    
	function displayAvailablerooms($HallCode, $StudentSessions){
			return null;
	
	}
	
	public function GetHallName($HallCode){
	   $HallSQL =	"SELECT hall_name
			FROM hamis.halls
			WHERE (hall_code ='$HallCode')";
		$HallName = $this->MysqlHamisDB->GetOne("$HallSQL");	
		return   $HallNam;
	}
	
	public function BookARoom($HallCode,$RoomNo,$Slot,$AcademicYear,$StudentSessions){
		$GetSelectedRoomSQL = "SELECT start_date, end_date
								FROM
									hamis.available_rooms
								WHERE (hall_code ='$HallCode'
									AND room_no ='$RoomNo'
									AND slot ='$Slot'
									AND status = 'AVAILABLE')
									";
			
		if(is_array($StudentSessions)){
			$OuterLoopCounter = 1;
			foreach($StudentSessions as $SemesterKey => $SemesterInfo){
					$InnerLoopCount = 1;
					$WhereClause = '';
					if(is_array($SemesterInfo))
					foreach($SemesterInfo as $key => $value){	
						if(!strlen($WhereClause)){
							if($InnerLoopCount ==1)
								$WhereClause .= '(' ;
							if($key =='START_DATE')
								$WhereClause .= "( hamis.available_rooms." . strtolower($key) ."<='". $value .  "')" ; 
							elseif($key =='END_DATE')
								$WhereClause .= "( hamis.available_rooms." . strtolower($key) . "<='" .$value .  "')" ; 
						}else{
							if( $key=='START_DATE'){
								if($InnerLoopCount ==1)
									if($OuterLoopCounter > 1)
										$WhereClause .= " OR (( hamis.available_rooms." .strtolower($key) ."<='". $value  .  "')" ; 
									else
									  $WhereClause .= " AND (( hamis.available_rooms." .strtolower($key) ."<='". $value  .  "')" ;  	
								else
									$WhereClause .= " AND ( hamis.available_rooms." .strtolower($key) ."<='". $value  .  "')" ; 
							}elseif( $key=='END_DATE'){
								if($InnerLoopCount ==1)
									if($OuterLoopCounter >1 )
										$WhereClause .= " OR (( hamis.available_rooms." .  strtolower($key) .">='". $value  .  "')" ;
									else
										$WhereClause .= " AND (( hamis.available_rooms." .  strtolower($key) .">='". $value  .  "')" ;    
								else
									$WhereClause .= " AND ( hamis.available_rooms." .strtolower($key) .">='". $value  .  "')" ;
									 
						}	
					}
					$InnerLoopCount++;
				}  
				 $WhereClause .= ')' ;
				 
				 if(strlen($WhereClause )){
						$GetSelectedSemRoomSQL = $GetSelectedRoomSQL . " AND 	$WhereClause "    ;
						$TimetobeConsumed[$SemesterKey] = $this->MysqlHamisDB->GetRow("$GetSelectedSemRoomSQL");			
				 }
				 $OuterLoopCounter++;  
			} // outer foreach
			
			$AllAvailable = TRUE;
			if(is_array($StudentSessions))
			foreach($StudentSessions as $key =>$SemesterInfo) {
				if(!is_array($TimetobeConsumed[$key]) && count($TimetobeConsumed[$key]))
				{
					$AllAvailable = FALSE;
				}
			}
			
			if($AllAvailable === TRUE){
				// Book the Student and update the Time Slot in the Available Tables Accordingly
				//$Result = $this->DisableavailableSlot($HallCode,$RoomNo,$StartDate,$EndDate,$AcademicYear,$Slot);
				$PreviousSemStartDate =0;
				$PreviousSemEndDate = 0 ;
				$SessionCount = 1;
				if(is_array($StudentSessions))
				foreach($StudentSessions as $key =>$SemesterInfo) {
				   if(strlen($SemesterInfo['START_DATE']) && strlen($SemesterInfo['END_DATE']))	{
						$Result = $this->DisableavailableSlot($HallCode,$RoomNo,$TimetobeConsumed[$key]['start_date'],$TimetobeConsumed[$key]['end_date'],$AcademicYear,$Slot);
						$roomAvailableStartDate = $this->ConvertMysqldate($TimetobeConsumed[$key]['start_date']) ;		
						$roomAvailableEndDate = $this->ConvertMysqldate($TimetobeConsumed[$key]['end_date'])	;	
						
								  
						$StudentSessionStartDate = $this->ConvertMysqldate($SemesterInfo['START_DATE'])  ;
						 $StudentSessionEndDate = $this->ConvertMysqldate($SemesterInfo['END_DATE'] ) ;	
						 if(($this->conversecondtoDays(($StudentSessionStartDate - $roomAvailableStartDate)) >= $this->DaysBetween) && $SessionCount ==1 ){
								$EndDateTSP = $this->ConvertTimeStampToMysqlDate($StudentSessionStartDate  - $this->convertdaysToSeconds(1));  
								$Success =  $this->AddAvailableRooms($HallCode,$RoomNo,$TimetobeConsumed[$key]['start_date'],$EndDateTSP,$AcademicYear,$Slot)   ;
						 }
						 
						 if(($this->conversecondtoDays(($roomAvailableEndDate - $StudentSessionEndDate)) >= $this->DaysBetween) && $SessionCount == count($StudentSessions) ) {
							  $StartDateTSP = $this->ConvertTimeStampToMysqlDate($StudentSessionEndDate  + $this->convertdaysToSeconds(1));
							  $Success =  $this->AddAvailableRooms($HallCode,$RoomNo,$StartDateTSP,$TimetobeConsumed[$key]['end_date'],$AcademicYear,$Slot)   ;  
						 }
						 
						 if($PreviousSemEndDate != 0){
							 $DaysBetweenSemester =  $this->conversecondtoDays($StudentSessionStartDate - $PreviousSemEndDate);
							 
							 if($DaysBetweenSemester >= $this->DaysBetween ){
								   $StartDateTSP = $this->ConvertTimeStampToMysqlDate($PreviousSemEndDate  + $this->convertdaysToSeconds(1));  
								   $EndDateTSP =  $this->ConvertTimeStampToMysqlDate($StudentSessionStartDate  - $this->convertdaysToSeconds(1));
								   $Success =  $this->AddAvailableRooms($HallCode,$RoomNo,$StartDateTSP,$EndDateTSP,$AcademicYear,$Slot)   ;	 
							 }
						 }
						 //Book the Students for Given Sessions
						 $Semester = $this->ConvertNumerictoVarchar($key);
						 
						 $Successiful = $this->AddRoomBooking($HallCode,$RoomNo,$Slot,$AcademicYear,$Semester,$SemesterInfo['START_DATE']  ,$SemesterInfo['END_DATE']);
						 if($Successiful == FALSE)
							return FALSE;
						$PreviousSemStartDate   =  $StudentSessionStartDate ;
						$PreviousSemEndDate     =  $StudentSessionEndDate ;
						$SessionCount++;
					}
				 }
			}else{
			return  'ROOM TAKEN' ; 
			}
		}else{
			$this->SetError($this->GetError() .  "<br>Function BookARoom :  The Valuable {$StudentSessions} is Empty ");  
			return FALSE; 
		}
		  return TRUE;	
	} // End Function BookARoom
	
	public function AddRoomBooking($HallCode,$RoomNo,$Slot,$AcademicYear,$Semester,$Startdate,$endDate,$BookingStatus='BOOKED')    {
		$RegistrationNo = $this->CCGetSession("RegNo"); 
		$DegreeCode = $this->CCGetSession("DegCode"); 
		$LevelofStudy = $this->CCGetSession("LevelofStudy"); 
		$Gender =      $this->CCGetSession("gender"); 
		$CurrentTimStamp = date('Y-m-d H:i:s')  ;
		$RoomCharge = $this->getRoomCharge($HallCode,$RoomNo)  ;
		$UuID = $RegistrationNo. '_' . $HallCode. '_' .$RoomNo. '_' .$Slot. '_' .$Startdate. '_' .$endDate.'_BOOKED';
		$CheckDuplicate =  $this->MysqlHamisDB->GetOne("SELECT  reg_no FROM hamis.student_room_bookings WHERE uu_id ='$UuID'");
		if(!strlen($CheckDuplicate))  {
			$InvoiceAmount = ($this->DaysinSemester($Startdate,$endDate) - $this->GetBreakDaysInSemsters($DegreeCode,$Startdate,$endDate,$Semester,$AcademicYear,$LevelofStudy))  * $RoomCharge;
			$InsertSQL = "INSERT INTO hamis.student_room_bookings(reg_no, hall_code, room_no , slot_no , date_booked , time_booked , invoice_amout , booking_status , academic_year , semester , start_date, end_date, uu_id,degree_code, level_of_study,gender,daily_room_charge,synchronization_flag)
						 VALUES('$RegistrationNo','$HallCode','$RoomNo','$Slot',NOW(),NOW(),$InvoiceAmount,'$BookingStatus','$AcademicYear','$Semester','$Startdate','$endDate','$UuID' ,'$DegreeCode',$LevelofStudy,'$Gender',$RoomCharge,1)  " ;
		   $Result = $this->MysqlHamisDB->Execute($InsertSQL )	 ;
		   if(!$Result)
			return FALSE;
		   else
			return TRUE;
		}else
			return FALSE;
	}
	
	
	public function getRoomCharge($HallCode,$RoomNo,$Module=1) {
		if($Module == 1){
			$RoomChargeSQL = "SELECT
				room_types.CHARGE_PER_DAY
				FROM
					hamis.rooms
					INNER JOIN hamis.room_types 
						ON (rooms.ROOM_TYPE_CODE = room_types.ROOM_TYPE_CODE)
				WHERE (rooms.ROOM_NO ='$RoomNo'
					AND rooms.HALL_CODE ='$HallCode')";
					
			$RoomCharge = $this->MysqlHamisDB->GetOne($RoomChargeSQL);		
			return $RoomCharge;
		}else
			return 200;
	}
	
	public function DaysinSemester($StartDate,$EndDate){
		  $StartDateTmS = $this->ConvertMysqldate($StartDate); 
		  $EndDateTimeSTP =$this->ConvertMysqldate($EndDate);   
		  $DaysInSem =  $this->conversecondtoDays(($EndDateTimeSTP  - $StartDateTmS))+ 1 ;
		  return $DaysInSem ;
	}
	
	public function GetBreakDaysInSemsters($StudDegreeCode,$Start_date,$ExitDate,$v_sem,$AcademiYear,$LevelofStudy){
		
			 $HolidayWhere = "(ACADEMIC_YEAR='$AcademiYear') and (SEMESTER = '$v_sem') 
			   and (DEGREE_CODE ='$StudDegreeCode')  and (LEVEL_OF_STUDY = $LevelofStudy or LEVEL_OF_STUDY= 0)
			   AND ((date(END_DATE) < date('$ExitDate')) 
			   AND (date(START_DATE) > date('$Start_date'))) " ;		   

			 $HolidaySQL = " SELECT START_DATE,END_DATE  from hamis.academic_breaks where $HolidayWhere " ;


		 $rs = $this->MysqlHamisDB->GetArray($HolidaySQL );
		 $CumulativeBreakDays = 0;
		 if(is_array($rs))
		 foreach($rs as $breakKey => $BreakInfo){
			 $SemStartDate = $this->ConvertTimeStampToMysqlDate($BreakInfo['START_DATE']);
			 $SemEndDate = $this->ConvertTimeStampToMysqlDate($BreakInfo['END_DATE']);
			 $BreakDays = $this->conversecondtoDays(($SemEndDate -  $SemEndDate)) - 1 ;
			 $CumulativeBreakDays = $CumulativeBreakDays +   $BreakDays ;
		 }
		 return $CumulativeBreakDays;
	}
	

	public function checkcancelledBooing($BookingInfo){
		
		$Cancelled = FALSE;
		if(is_array($BookingInfo) && count($BookingInfo) > 0){
			$BookingStatus = array();
			foreach($BookingInfo as $SemBookingKey =>$SemesterBookingInfo){
				array_push($BookingStatus,$SemesterBookingInfo['booking_status']);
			}
			if(in_array('CANCEL',$BookingStatus) && (!in_array('BOOKED',$BookingStatus) || !in_array('CONFIRMED',$BookingStatus)) )
			 $Cancelled = TRUE;	
		}
		return $Cancelled ;
	}
	
	
	public function getBookingDetails($RegNo,$AcademicYear){
		$vstatus = $this->CCGetParam("vstatus");
		$bookstatusquery="SELECT SRB.booking_id, SRB.reg_no, SRB.hall_code,HL.hall_name, SRB.room_no, SRB.semester,
						SRB.booking_status,SRB.invoice_amout, SRB.academic_year, SRB.daily_room_charge ,date_booked ,time_booked,
						start_date,end_date 
						FROM hamis.student_room_bookings SRB ,hamis.halls HL 
						WHERE (SRB.reg_no='$RegNo' AND SRB.academic_year='$AcademicYear' and PUBLISH = 1 and SRB.booking_status in('BOOKED','CONFIRMED','CANCEL','BOOKED_QUEUE'))	
							  AND(SRB.hall_code=HL.hall_code)
						ORDER BY date_booked DESC ,time_booked DESC,SRB.booking_status DESC,SRB.academic_year DESC ";
		$BookedRoomRS = $this->MysqlHamisDB->GetArray($bookstatusquery);
		if(is_array($BookedRoomRS) && count($BookedRoomRS) > 0){
			foreach($BookedRoomRS as $key=>$BookedRoomInfo){	
				$BookedRoomList[$key][$BookedRoomInfo['semester']] = $BookedRoomInfo ;
				$this->AllocatedHall[$key] = $BookedRoomInfo['hall_code'] ;
			}
			return $BookedRoomList  ;
		}if(is_array($BookedRoomRS) && count($BookedRoomRS)==0)
			return   $BookedRoomRS;
		else
			return FALSE;
	}
	
	public function DisplayBookingInformation($BookedRoomInfo,$BookingStatus){
		$RegistrationNo = $this->CCGetSession("RegNo");
		$Gender = $this->CCGetSession("gender");
		$UonEmail = $this->CCGetSession("Email");
		$Surname = $this->CCGetSession("surname");
		$OtherNames = $this->CCGetSession("otherNames");
		$LevelofStudy = $this->CCGetSession("LevelofStudy");
		
		
		$ConfimByDate=$this->CheckIndividualConfirmDate($RegistrationNo);
		
        if($BookingStatus['DAYS_REMAINING'] >= 0 && $BookingStatus['STATUS'] == 'OPEN' ){
            $ConfirmationDeadline = "The Deadline for room confirmation is <b>{$BookingStatus['CLOSING_DATE']} </b> , you have {$BookingStatus['DAYS_REMAINING']} days remaining to confirm this room. If you do not confirm this room before {$BookingStatus['CLOSING_DATE']} you shall be deemed to have rejected the offer and the room will be re-allotted to another student."  ;
        }elseif(($BookingStatus['STATUS'] == 'CLOSED') && ($ConfimByDate === TRUE)){            
            $ConfirmationDeadline = "You have $ConfimByDate number of days remaining from today to confirm your room, If you do not confirm this room, you shall be deemed to have rejected the offer and the room will be re-allotted to another student."  ; 
        }elseif($BookingStatus['STATUS'] == 'CLOSED'){            
            $ConfirmationDeadline = "The Deadline for room confirmation was <b>{$BookingStatus['CLOSING_DATE']}</b> , If you did not confirm the allotted room before the deadline you have be deemed to have rejected the offer and the room will be re-allotted to another student."  ; 
        }  
        
        
		$BalanceBf = $this->Ora_Db->GetOne("SELECT SWA_ADMIN.GetBalance('$RegistrationNo') AS BALANCE FROM DUAL ");
        
        if(is_array($BookedRoomInfo)){
			 if(count($BookedRoomInfo)<1)
			   return FALSE;	
		   /*
			if($LevelofStudy != 1) { 
                $RoomAppplicationRecord = $this->getRoomApplicationDetails($RegistrationNo,$this->Academicyear); 
            if(is_array($RoomAppplicationRecord) && count($RoomAppplicationRecord)>0){
                if($this->UpdateFormDisplayed === FALSE)
                    $DisplayInformation .= $this->DisplayApplicationInformation($RoomAppplicationRecord,$ApplicationStatus)  ;
            } 
                return    $DisplayInformation;
                $BookedRoonDisplay .= '<table cellspacing="0" cellpadding="3" border="1" align="center">
                   <tr><th colspan="2"> 
                        <b>Room Application  for '. $RoomAppplicationRecord['academic_year'] . ' Academic year </b> has been Closed. 
                    </th></tr>' ;
                $BookedRoonDisplay .= '</table>'; 
                 
				//$BookedRoonDisplay = '<table cellspacing="0" cellpadding="3" border="1" align="center">' ;			
				//$BookedRoonDisplay .=' <h2>The room Alotment is currently in progress, You will be contacted through the email address that you provided at applicantion once the process has been completed</h2>' ;
				//$BookedRoonDisplay .=  '</table>' ;
				//return $BookedRoonDisplay ;
			}else{ */
				$BookedRoonDisplay = '<table cellspacing="0" cellpadding="3" border="1" align="center">' ;			
					
			//}
			$TimeStamp = date('l,d-M-Y H:i:s');
			$BookedRoonDisplay .= '<caption>
									
									<b>Student Room Reservation Details for </b>' . " $RegistrationNo  $Surname  $OtherNames as at: <b>$TimeStamp </b><br>$ConfirmationDeadline<br>All Correspondences regarding your booking will be emailed through <a href=\"mailto:$UonEmail\">$UonEmail</a> " .
								'</caption>' ;
			
			$RoomCharge = 0;
			
			$ConfirmableBookingStatus = array('BOOKED','BOOKED_QUEUE'); // Status in the bOoking table that warrant Confirmation
			$BookedRoonDisplay .= '<tr>
										<th>Hall  </th>
										<th>Room No. </th>
										<th>Academic Year </th>
										<th>Semester</th>
										<th>Start Date</th> 
										<th>Start Date</th> 
										<th>Booked on</th>  
										<th>Booking Status</th>
										<th>Charge Per Day</th>
										<th>Invoice Amount</th>
								 </tr>';
           
            $RecentAllocation = FALSE; 
            $PreviousStatus = '';
            $FooterDisplayed = FALSE;
			if(is_array($BookedRoomInfo))
            foreach($BookedRoomInfo as $Key => $EachSemesterBookingInfo)  {

			    foreach($EachSemesterBookingInfo as $Semeseter => $SemesterBookingInfo){
                    $StudentBookingStatus =  $SemesterBookingInfo['booking_status']  ; 
                    if($PreviousStatus != $StudentBookingStatus) {                    
                        if(strlen($PreviousStatus)){
                            $BookedRoonDisplay .= $this->GetRoomBookingFooter($PreviousStatus,$BookingStatus,$RoomCharge,$BalanceBf,$ConfirmableBookingStatus,$RegistrationNo) ;
                            $FooterDisplayed = TRUE;
                        }    
                           $PreviousStatus =  $StudentBookingStatus ;
                    }
				    $BookedRoonDisplay .= '<tr>	<td> Hall ' 	. $SemesterBookingInfo['hall_code'] .'('.$SemesterBookingInfo['hall_name'].')</td> 
									    <td>'	. $SemesterBookingInfo['room_no'] .'</td>
									    <td>'	. $SemesterBookingInfo['academic_year'] .'</td>
									    <td>'	. $SemesterBookingInfo['semester'] .'</td> 
									     <td>'	. $SemesterBookingInfo['start_date'] .'</td>   
									     <td>'	. $SemesterBookingInfo['end_date'] .'</td>   
									     <td nowrap>'	. $SemesterBookingInfo['date_booked'] . ' ' . $SemesterBookingInfo['time_booked'] . '</td>   
									    <td>'	. $SemesterBookingInfo['booking_status'] .'</td>
									    <td align="center">'	. number_format($SemesterBookingInfo['daily_room_charge'],2) .'</td>
									    <td align="right">'	. number_format($SemesterBookingInfo['invoice_amout'],2) .'</td>  
						       </tr>' ;	
				    $FooterDisplayed = FALSE;
                    $DaysToConfirmDays = $this->convertdaysToSeconds($this->ConfimationDays) ;
                   
                    $DateBooked =   $this->ConvertMysqldate($SemesterBookingInfo['date_booked']) ;
                    $Today =    $this->ConvertMysqldate(date('Y-n-d')) ;
                    
                    $DaysLapsedTillBooking =   ($DateBooked + $DaysToConfirmDays) - $Today  ;
                                    
                    if($DaysLapsedTillBooking > 0)
                       $RecentAllocation = TRUE;
                                
				    
				    
				    if($StudentBookingStatus != 'CANCEL')
					    $RoomCharge = $RoomCharge +  $SemesterBookingInfo['invoice_amout'] ;
					    
			    }
                
            }
			 // Display Booking Footer  
             if(($PreviousStatus != $StudentBookingStatus) || $FooterDisplayed === FALSE) {
                    $StudentBookingStatus =  $StudentBookingStatus ;
                    if(strlen($PreviousStatus))
                        $BookedRoonDisplay .= $this->GetRoomBookingFooter($StudentBookingStatus,$BookingStatus,$RoomCharge,$BalanceBf,$ConfirmableBookingStatus,$RegistrationNo) ;
             } 	
			 $BookedRoonDisplay .=  '</table>' ;
			 return  $BookedRoonDisplay; 
		}else{
			return FALSE;
		}
	}
	
    public function GetRoomBookingFooter($StudentBookingStatus,$BookingStatus,$RoomCharge,$BalanceBf,$ConfirmableBookingStatus,$RegistrationNo){
	
        $BookedRoomfooterDisplay ='' ;
            if(in_array($StudentBookingStatus,$ConfirmableBookingStatus)){ 
                 $BookedRoomfooterDisplay .=  '<tr>
                                <td colspan="9">Balance B/f</td>
                                <td align="right">'. number_format($BalanceBf,2) .'</td>
                          </tr>' ;  
                
                $RoomCharge += $BalanceBf ; 
                
                if($RoomCharge < 0)
                    $RoomCharge = 0;
                     
                $BookedRoomfooterDisplay .=  '<tr>
                                        <td colspan="9">Invoiced Amount Due</td>
                                        <td align="right">'. number_format($RoomCharge,2) .'</td>
                                  </tr>' ;
             }elseif($StudentBookingStatus  == 'CONFIRMED'){
                $BookedRoomfooterDisplay .=  '<tr>
                                <td colspan="9">Current Balance</td>
                                <td align="right">'. number_format($BalanceBf,2) .'</td>
                          </tr>' ;  
             }
             
            //echo $BookingStatus['STATUS'];
			$ConfimByDate=$this->CheckIndividualConfirmDate($RegistrationNo);
			echo $ConfimByDate;
            if((in_array($StudentBookingStatus,$ConfirmableBookingStatus)) && ($BookingStatus['STATUS'] == 'OPEN' || $RecentAllocation === TRUE || $ConfimByDate === TRUE)){
                         $BookedRoomfooterDisplay .=  '<tr><td colspan="4" align="center"><a href="'.$_SERVER['PHP_SELF'] . '?ConfirmRoom=YES"> Confirm Booking</a></td>                                             
                                             <td colspan="3" align="center"><a href="'.$_SERVER['PHP_SELF'] . '?Invoice=INVOICE" target="_blank"> Get Invoice </a></td>
                                             <td colspan="3" align="center"><a href="'.$_SERVER['PHP_SELF'] . '?ConfirmRoom=CANCEL"> Cancel Booking</a></td> 
                                             </tr>' ;
            }
            if($StudentBookingStatus  == 'CONFIRM_WAITING'){
                     $BookedRoomfooterDisplay .=  '<tr>
                            <td colspan="4" align="center"><a href="'.$_SERVER['PHP_SELF'] . '?QueueAcceptOfferedRoom=YES">Accept Offered Room</a></td>
                            <td colspan="3" align="center"><a href="'.$_SERVER['PHP_SELF'] . '?QueueAcceptOfferedRoom=NO">Reject Offered Room</a></td>
                            <td colspan="3" align="center"><a href="'.$_SERVER['PHP_SELF'] . '?Invoice=INVOICE" target="_blank"> Get Invoice </a></td> 
                            </tr>' ;
            }
            if($StudentBookingStatus=='CONFIRMED') {
                $BookedRoomfooterDisplay .=  '<tr>
                                        <td colspan="10" align="left">You have been allocated the room whose details appears above upon your confirnmation and acceptance of the same. The room allocation takes effect on the dates indicated </td>
                                    </tr>' ;
             
            }
            if($StudentBookingStatus=='CANCEL') {
                $BookedRoomfooterDisplay .=  '<tr>
                                        <td colspan="10" align="left"><font color="red">You rejected the room offer that SWA had given you. Kindly note that you will not be consindered for any other accomodation this academic year </font></td>
                                    </tr>' ;
             
            } 
            if($StudentBookingStatus=='BOOKED' && $BookingStatus['STATUS'] == 'CLOSED') {
                $BookedRoomfooterDisplay .=  '<tr>
                                        <td colspan="10" align="left"><font color="red">hough you have had been allocated the above room, you did not confirm it within the stipulated timeframe. <br> You are advised that this room will be re-allocated to another student from the waiting list</font></td>
                                    </tr>' ;
            } 
            
            return $BookedRoomfooterDisplay;
    }
    
	public function CheckQueue($RegistrationNo,$AcademicYear){
		$QueuetatusSQL="SELECT queue_id,waiting_date, waiting_time,status FROM hamis.room_waiting
						WHERE reg_no='$RegistrationNo' AND academic_year='$AcademicYear' order by waiting_date desc, 	waiting_time desc	";

		$QueuetatusRS = $this->MysqlHamisDB->GetRow($QueuetatusSQL);
		if(is_array($QueuetatusRS) && count($QueuetatusRS) > 0){
			return $QueuetatusRS  ;
		}elseif(is_array($QueuetatusRS) && count($QueuetatusRS)==0 )
			return   "NOT_QUEUED"    ;
			
		else
			return FALSE;
	}
	
	public function displayQueueDisplay($QueuetatusInfo)  {
		if(is_array($QueuetatusInfo)){
			 if(count($QueuetatusInfo)<1)
			   return FALSE;	
		
			$QueueInfo = '<table cellspacing="0" cellpadding="3" border="1" align ="center">' ;
			$QueueInfo .= '<caption>Room Queueing Information </caption>'   ;
			$QueueInfo .=  '<tr><td nowrap>QueQue Postion </td><td nowrap >Date Queued </td><td nowrap>Time Queued </td><td nowrap>Queue Status </td></tr>';
			$RoomCharge = 0;
			$QueueInfo .= '<tr>	<td>' 	. $QueuetatusInfo['queue_id'] .'</td> 
						<td>' 	. $QueuetatusInfo['waiting_date'] .'</td> 
						<td>'	. $QueuetatusInfo['waiting_time'] .'</td>
						<td>'	. $QueuetatusInfo['status'] .'</td>  
			   </tr>' ;	
			   
			  if($QueuetatusInfo['status'] =='NOTIFIED')
				$QueueInfo .=  '<tr><td colspan="4" align="center">' . $this->DisplayBookingNofitication($QueuetatusInfo['queue_id']) . '</td></tr>' ;
			  elseif($QueuetatusInfo['status'] =='WAITING')
				$QueueInfo .=  '<tr><td colspan="4" align="center"> <a href="'.$_SERVER['PHP_SELF'] . '?CancelQue=YES">Quit from Queue</a> </td></tr>' ;  
		  
			$QueueInfo .= '</table>';		
			  return  $QueueInfo;
		}else{
			return FALSE;
		}	
		
	}
	
	public function DisplayBookingNofitication($QueID) {
		$NotificationSQL="SELECT notification_id,queue_id, notification_date,message FROM hamis.booking_notification
						 WHERE queue_id='$QueID' ";

		$NotificationRS = $this->MysqlHamisDB->GetRow($NotificationSQL);
		if(is_array($NotificationRS) && count($NotificationRS) > 0){
			$NotoficationMsg = '<table cellspacing="0" cellpadding="3" border="1">' ;
			$NotoficationMsg .= '<tr><td>'.$NotificationRS['notification_id'].'</td>
					   <td>'.$NotificationRS['queue_id'].'</td>
					   <td>'.$NotificationRS['notification_date'].'</td>
					   <td>'.$NotificationRS['message'].'</td>      
			</tr>'  ;
			$NotoficationMsg .= '</table>' ;
			 return $NotoficationMsg  ;
		}else
			return FALSE;
	}
	
	public function CheckBookingStatus($AcademicYear,$DegreeCode,$LevelofStudy){
      $NotificationSQL="SELECT MAX(BOOKING_END_DATE) as CLOSING_DATE
                        FROM hamis.student_academic_sessions
                         WHERE DEGREE_CODE='$DegreeCode' 
                             AND ACADEMIC_YEAR='$AcademicYear' 
                             AND YEAR_OF_STUDY=$LevelofStudy
                             AND BOOKING_START_DATE <= NOW() "; 
        $ClosingDate =  $this->MysqlHamisDB->GetOne($NotificationSQL);
        if($ClosingDate) {
              $ClosingDateTPS = $this->ConvertMysqldate($ClosingDate)  ;
              $Today =    $this->ConvertMysqldate(date('Y-m-d')) ;
              $DaysRemaining =   $this->conversecondtoDays($ClosingDateTPS - $Today) ;
              if($Today > $ClosingDateTPS )
                return 'CLOSED' ;
              else 
                return array("STATUS"=>'OPEN',"CLOSING_DATE"=>$ClosingDate,"DAYS_REMAINING"=>$DaysRemaining) ;
        }else{
                  $NotificationSQL="SELECT MIN(BOOKING_START_DATE) as CLOSING_DATE
                        FROM hamis.student_academic_sessions
                         WHERE DEGREE_CODE='$DegreeCode' 
                             AND ACADEMIC_YEAR='$AcademicYear' 
                             AND YEAR_OF_STUDY=$LevelofStudy
                             AND BOOKING_START_DATE >= NOW()";
                  $BookinStartDate =  $this->MysqlHamisDB->GetOne($NotificationSQL); 
                  if($BookinStartDate) {
                    $BookingStartDateTPS = $this->ConvertMysqldate($BookinStartDate)  ;
                    $Today =    $this->ConvertMysqldate(date('Y-m-d')) ;
                    $DaysRemaining =   $this->conversecondtoDays($BookingStartDateTPS - $Today) ; 
                    return array("STATUS"=>'WAITING',"STARTING_DATE"=>$BookinStartDate,"DAYS_REMAINING"=>$DaysRemaining) ;  
                  }
            return 'NO SET DATE';
        }
    }
	
	public function CheckModuleIIBookingStatus($catID){
      $NotificationSQL="SELECT MAX(CONFIRMATION_END_DATE) as CLOSING_DATE
                        FROM hamis.other_category_session
                         WHERE CATEGORY_ID = '$catID'
						 	   AND CONFIRMATION_START_DATE <= NOW()";   
							   
        $ClosingDate =  $this->MysqlHamisDB->GetOne($NotificationSQL);
        if($ClosingDate) {
              $ClosingDateTPS = $this->ConvertMysqldate($ClosingDate)  ;
              $Today =    $this->ConvertMysqldate(date('Y-m-d')) ;
              $DaysRemaining =   $this->conversecondtoDays($ClosingDateTPS - $Today) ;
              if($Today > $ClosingDateTPS )
                return 'CLOSED' ;
              else 
			  {
                return array("STATUS"=>'OPEN',"CLOSING_DATE"=>$ClosingDate,"DAYS_REMAINING"=>$DaysRemaining) ;
			  }
        }else{
                  $NotificationSQL="SELECT MIN(CONFIRMATION_START_DATE) as CLOSING_DATE
                        FROM hamis.other_category_session
                         WHERE CATEGORY_ID = '$catID'
						 	   AND CONFIRMATION_START_DATE >= NOW()";
                  $BookinStartDate =  $this->MysqlHamisDB->GetOne($NotificationSQL); 
                  if($BookinStartDate) {
                    $BookingStartDateTPS = $this->ConvertMysqldate($BookinStartDate)  ;
                    $Today =    $this->ConvertMysqldate(date('Y-m-d')) ;
                    $DaysRemaining =   $this->conversecondtoDays($BookingStartDateTPS - $Today) ; 
                    return array("STATUS"=>'WAITING',"STARTING_DATE"=>$BookinStartDate,"DAYS_REMAINING"=>$DaysRemaining) ;  
                  }
            return 'NO SET DATE';
        }
    }
	
	//function to allow individual confirmation dates to be set
	public function CheckIndividualConfirmDate($RegNo){
	  global $AcademicYear;
      $IndividualSQL="SELECT DISTINCT(CONFIRM_BY_DATE) as CLOSING_DATE
                        FROM hamis.student_room_bookings
                        WHERE reg_no='$RegNo'
						AND academic_year='$AcademicYear'";   
						
						//print $IndividualSQL; 
        $IndClosingDate =  $this->MysqlHamisDB->GetOne($IndividualSQL);
        if($IndClosingDate) {
			$IndClosingDateTPS = $this->ConvertMysqldate($IndClosingDate)  ;
			 $Today =    $this->ConvertMysqldate(date('Y-m-d')) ;
              $IndDaysRemaining =   $this->conversecondtoDays($IndClosingDateTPS - $Today); 
				   if ($Today > $IndClosingDateTPS )
					 return FALSE;
				   else 
				  return TRUE;
           }
		return FALSE;
    }
	
	//function to select the needy from student special cases table to allow them to confirm evenif they have college balance
	public function GetSpecialCase($RegNo){
	  global $AcademicYear;
      $NeedycaseSQL="SELECT registration_number
                        FROM hamis.student_special_cases
                        WHERE registration_number='$RegNo'
						AND academic_year='$AcademicYear'
						AND category_id=3";   
						
       $Needycase=$this->MysqlHamisDB->GetOne($NeedycaseSQL);
        if($Needycase) 
	 	  return TRUE;
		 else 
		  return FALSE;
         
		return FALSE;
    }
	
	public function ProcessSelectedRoom($RegistrationNo,$AcademicYear,$StudentSessions,$IsBookingOpen,$ScenarioUsed=1,$SemesterAvailable){
		
	   //	$ScenarioUsed,$SemesterAvailable
	   if(!is_array($StudentSessions))
			$this->SetError($this->GetError() .  "<br>Function ProcessSelectedRoom :  The Valuable StudentSessions is not a array  "); 
			
		$this->MysqlHamisDB->StartTrans();
		$Success = FALSE;
		$Message = '';
		if($ScenarioUsed == 1){
			$RoomSlot = $this->CCGetParam("RoomSlot");
			list($HallCode,$RoomNo,$SlotNo) = split('[_]',$RoomSlot)  ;
			
			if(strlen($RoomSlot)){
				$Result = $this->BookARoom($HallCode,$RoomNo,$SlotNo,$AcademicYear,$StudentSessions);				
				if($Result === FALSE && $BookeRoomDetails ===FALSE){
				   if($Result=='ROOM TAKEN') {
						 $Message .= 'The room you are trying to Book has been booked by someone else already <br>';
						 
				   }else
						$Message .= "There problem occured which prevented you from gettign a room <br>";
				}					
			  } else{
					$this->SetError($this->GetError() .  "<br>Function ProcessSelectedRoom :  The Valuable RoomSlot was Empty ");   
					$Success = FALSE ; 
			  }
		}elseif($ScenarioUsed == 2){
		  $SemestersList = split('[\_]',$SemesterAvailable);
		  foreach($SemestersList as $key => $SemesterValue){
			 $RoomSlotFldName =  'RoomSlot_' . $SemesterValue ;
			 $RoomSlot  = $this->CCGetParam("$RoomSlotFldName"); 
			 list($HallCode,$RoomNo,$SlotNo) = split('[_]',$RoomSlot)  ;
			 
			 $PartialStudentSessions = array();
			 $PartialStudentSessions[$SemesterValue]  = $StudentSessions[$SemesterValue];
			 if(strlen($RoomSlot)){
				//$Message .= "<h1> You Have selected $RoomSlot </h1> ";
				$Result = $this->BookARoom($HallCode,$RoomNo,$SlotNo,$AcademicYear,$PartialStudentSessions);					
				if($Result === FALSE && $BookeRoomDetails === FALSE){
				   if($Result=='ROOM TAKEN') {
						 $Message .= "The room( Hall : $HallCode,Room : $RoomNo, Bed Space: $SlotNo) you are trying to Book has been booked by someone else already <br>";
						 
				   }else
						$Message .= "There problem occured which prevented you from getting a room <br>";
						
				   $this->MysqlHamisDB->FailTrans(); 
				   
				}					
			  } else{
					$this->SetError($this->GetError() .  "<br>Function ProcessSelectedRoom :  The Valuable RoomSlot was Empty ");
					$this->MysqlHamisDB->FailTrans();    
					$Success = FALSE ; 
			  }			 
		  }	 // For each Semester in Split Semester 
		} // When Room is available in different Rooms for different Semseters
		
		if(!strlen($Message)) {
			$this->MysqlHamisDB->CompleteTrans();
			$Success = TRUE ;
			$BookeRoomDetails = $this->getBookingDetails($RegistrationNo,$AcademicYear);
			$this->DisplayBookingInformation($BookeRoomDetails,$IsBookingOpen); 
		}else{
			$this->MysqlHamisDB->FailTrans();
			$this->MysqlHamisDB->CompleteTrans();
			return  $Message;
		}
		
		return 	$Success;
		
}   // Fucntion   ProcessSelectedRoom
	
	    
    public function GetAvailableRooms($StudentsSessions,$Gender,$DegreeCode,$LevelofStudy) {

	    $OneRoomScenario = $this->CheckOneroomScenerio($StudentsSessions,$Gender,$DegreeCode,$LevelofStudy) ;
	    if($OneRoomScenario !== FALSE && (count($OneRoomScenario)>=1)){
	    if(is_array($OneRoomScenario)){
		    //print "Student can be accomodated in the same room for first and Second Semester ";
		    $AvailableRoomDetails =  '<form action="' . $_SERVER['PHP_SELF'] .'" method="post" enctype="multipart/form-data" name="bookableRooms">';
		    $AvailableRoomDetails .=  '<div style="width:100%; border:1px solid #ccc; height:400px">' ;
		    $AvailableRoomDetails .= '<span style="color:#333333; font:Arial, Helvetica, sans-serif; font-size:12px;font-weight:bold; align:center">Choose a room from the Slots available Below </span><br>';
		    $previousHall = '';
		    $SlotCount = 1;
		    if(is_array($OneRoomScenario))
		    foreach($OneRoomScenario as $AvailableRoomKey => $AvailableRooomValue){
			    if($previousHall  !=  $AvailableRooomValue['hall_code']){
				    if(strlen($previousHall))
					    print '</div>' ;
				    $AvailableRoomDetails .= '<div style="float:left; width:150px; border:1px solid #000; height:350px; overflow:auto; margin-right:10px">' ;
				    $AvailableRoomDetails .= '<span style="color:#333333; font:Arial, Helvetica, sans-serif; font-size:14px;font-weight:bold">HALL ' . $AvailableRooomValue['hall_code'] .  $this->GetHallName($HallCode) . '</span><br>'    ;
				    $previousHall = $AvailableRooomValue['hall_code'] ;
			    }
				    $FieldValue = "{$AvailableRooomValue['hall_code']}_{$AvailableRooomValue['room_no']}_{$AvailableRooomValue['slot']}" ;
				    
				    $AvailableRoomDetails .= "<input name=\"RoomSlot\" type=\"radio\" value=\"$FieldValue\" /> {$AvailableRooomValue['room_no']} Bed No.{$AvailableRooomValue['slot']} <br>";
			    
			    }
			    $SlotCount++ ;
		    }
		    
		    if(strlen($previousHall))
				    $AvailableRoomDetails .= '</div>' ;
					    
		    $AvailableRoomDetails .= "</div>" ;
		    $AvailableRoomDetails .= "<div style=\"float:none; 800px; padding-left:500px\">";			
		    $AvailableRoomDetails .= "<input type=\"hidden\" name=\"RoomCount\" value=\"$SlotCount\">" ;
		    $AvailableRoomDetails .= "<input type=\"hidden\" name=\"ScenarioUsed\" value=\"1\">" ; 
		    $AvailableRoomDetails .= "<input type=\"submit\" value=\"book Room\" name=\"BookRoomBtn\">" ;
		    $AvailableRoomDetails .= '</div>' ;
		    $AvailableRoomDetails .= '</form>'; 
	    
    }elseif($OneRoomScenario !== FALSE && (count($OneRoomScenario)== 0)){ 
	    // Student cannot get one room for First and Second Semester	 
		    $DifferentRoomsperSem = $this->CheckManyroomScenerio($StudentsSessions,$Gender,$DegreeCode,$LevelofStudy) ;
	    if( $this->CheckifEachSubArrayhasValues($DifferentRoomsperSem)  == $this->CheckifEachSubArrayhasValues($StudentsSessions)){ // Every session can be accomodate in different Rooms
		    $AvailableRoomDetails .= " There is no one Room which you can be accomodated for all your Semesters for this academic year <br>"; 
		    $AvailableRoomDetails .= $this->ShowDifferentRoomPerSem($DifferentRoomsperSem);
	    }elseif($this->countmultdimensionalArray($DifferentRoomsperSem) > 0){
		    $AvailableRoomDetails .= " There is no one Room which you can be accomodated for all your Semesters for this academic year. However you can choose a room for the Semster(s) fro which space is Available below) <br>"; 
		    $AvailableRoomDetails .= $this->ShowDifferentRoomPerSem($DifferentRoomsperSem); // To be decided whether it's acceptable or do you go to the waiting list
	    }elseif($DifferentRoomsperSem !== FALSE && $this->countmultdimensionalArray($DifferentRoomsperSem) <= 0 ){
		    $AvailableRoomDetails .= '<span style="color:#FF0000; font:Arial, Helvetica, sans-serif; font-size:18px;font-weight:bold; align:center">There is not a single available room for which you can be accomodated </span>'    ;
		    $AvailableRoomDetails .= $this->ShowRequestToQueue() ;  
	    }else{
		    $AvailableRoomDetails .= '<span style="color:#FF0000; font:Arial, Helvetica, sans-serif; font-size:18px;font-weight:bold; align:center"> The system Encountered an Error when Checking qualified rooms </span>' ;
	    }
    }elseif($OneRoomScenario === FALSE ){
	    $AvailableRoomDetails .= '<span style="color:#FF0000; font:Arial, Helvetica, sans-serif; font-size:18px;font-weight:bold; align:center">The System Encountered an Error when Trying to get the Rooms which are available for your Booking </span>'   ;
    }else{
	     if($StudentsSessions !== FALSE)
	     {
		     $FilterClause = $this->PrepareSessionClause($StudentsSessions);
		     if(strlen($FilterClause))
			    $AdditionalWhereClause = " AND ($FilterClause) " ;
			    
			    // Degree halls
			    $DegreeHallRoomsSQLStmt = "SELECT distinct degree_halls.hall_code,halls.hall_name
								    FROM 	hamis.degree_halls,hamis.halls ,hamis.available_rooms    
								    WHERE (degree_halls.gender ='$Gender'
									    AND degree_halls.degree_code ='$DegreeCode'
									    AND degree_halls.year_of_study = '$LevelofStudy') $AdditionalWhereClause 
									    AND ((degree_halls.hall_code = available_rooms.room_no) AND (halls.hall_code = available_rooms.hall_code) AND (halls.hall_code =degree_halls.hall_code) )   ";
		    
		    //print "<h1>$DegreeHallRoomsSQLStmt</h1>";
		     $AvaliableHalls = $this->MysqlHamisDB->GetArray($DegreeHallRoomsSQLStmt);
		     
		     $AvailableRoomDetails . '<form action="' . $_SERVER['PHP_SELF'] .'" method="post" enctype="multipart/form-data" name="boonRooms">';
		     $AvailableRoomDetails . '<select name="SelectHallCode">' ;
		     if(is_array($AvaliableHalls))	
		     foreach($AvaliableHalls as $key => $HallInfo){	
			    $AvailableRoomDetails . '<option value="' .$HallInfo['hall_code'].'">'.$HallInfo['hall_name'].'</option>';
		     }	
		    $AvailableRoomDetails . '</select>' ; 
		    $AvailableRoomDetails . '<input type="submit" value="Select Hall" name="SelectHallBtn">'	;
		    $AvailableRoomDetails . '</form>' ;		
	    }
    }

    return  $AvailableRoomDetails ;
    }

    function countmultdimensionalArray($MuiltDimesionArray){
       $FirstLevel = count($MuiltDimesionArray);
       $SecondLevel = count($MuiltDimesionArray, COUNT_RECURSIVE);
       $ActualElelmentCount = $SecondLevel -  	   $FirstLevel ;
       return $ActualElelmentCount ;
    }	

    function CheckifEachSubArrayhasValues($MuiltDimesionArray){
       $SubArrayWithValueCount = 0;
       foreach  ($MuiltDimesionArray as $Key => $SubArray){
	       if(count($SubArray, COUNT_RECURSIVE))
			       $SubArrayWithValueCount++; 
       }
       return $SubArrayWithValueCount;
    }

    public function ShowDifferentRoomPerSem($DifferentRoomsperSem){

		    
	    if(is_array($DifferentRoomsperSem) && $this->countmultdimensionalArray($DifferentRoomsperSem)> 0 ) {
		    $AvailableRoomDetails .= '<form action="' . $_SERVER['PHP_SELF'] .'" method="post" enctype="multipart/form-data" name="bookableRooms">';	
	    
	    $RoomForAvailableSem = 0  ;
	    foreach($DifferentRoomsperSem as $SemesterNum=>$SemesterAvailableRooms){
	    
		    $SlotCount = 0;
		    if($RoomForAvailableSem == 0)
			    $RoomForAvailableSem = $SemesterNum;
		    else
			    $RoomForAvailableSem .= '_' . $SemesterNum;
			     
		    $RommCountFld = 'RoomCount_' .$SemesterNum ; 
		    $EachRoomFld = 'RoomSlot_' .  $SemesterNum ; 
		    
		    $AvailableRoomDetails .= '<div style="width:100%; border:1px solid #ccc; clear:both">' ;	
		     $AvailableRoomDetails .= '<span style="color:#333333; font:Arial, Helvetica, sans-serif; font-size:12px;font-weight:bold; align:center">Choose a room from the Slots available Below for your '.$this->ConvertNumerictoVarchar($SemesterNum).' Semester</span><br>';
		    if(is_array($SemesterAvailableRooms)){ 
			    $previousHall = '';
			    foreach($SemesterAvailableRooms as $AvailableRoomKey => $AvailableRooomValue){
								    
				    $SlotCount = 1;
				    if($previousHall  !=  $AvailableRooomValue['hall_code']){
					    if(strlen($previousHall))
						    $AvailableRoomDetails .= '</div>' ;
					    $AvailableRoomDetails .= '<div style="float:left; width:150px; border:1px solid #000; height:200px; overflow:auto; margin-right:10px">' ;
					    $AvailableRoomDetails .= '<span style="color:#333333; font:Arial, Helvetica, sans-serif; font-size:14px;font-weight:bold">HALL ' . $AvailableRooomValue['hall_code'] .  $this->GetHallName($HallCode) . '</span><br>'    ;
					    $previousHall = $AvailableRooomValue['hall_code'] ;
				    }
				    $FieldValue = "{$AvailableRooomValue['hall_code']}_{$AvailableRooomValue['room_no']}_{$AvailableRooomValue['slot']}" ;
				    $AvailableRoomDetails .= "<input name=\"$EachRoomFld\" type=\"radio\" value=\"$FieldValue\" /> {$AvailableRooomValue['room_no']} Bed No.{$AvailableRooomValue['slot']} <br>";

			    }
		    }   // If There rooms for the current Semester
			    $SlotCount++ ; 	 
			    $AvailableRoomDetails .= "<input type=\"hidden\" name=\"$RommCountFld\" value=\"$SlotCount\">" ;  
			    if(strlen($previousHall))
				    $AvailableRoomDetails .= '</div>' ;
				    
			    $AvailableRoomDetails .= '</div>' ; 
			    $AvailableRoomDetails .= '<br clear="all">' ; 
		    }
	    
		    
		    $AvailableRoomDetails .= "<div style=\"float:none; 800px; padding-left:500px\">";			
		    $AvailableRoomDetails .= "<input type=\"submit\" value=\"book Room\" name=\"BookRoomBtn\">" ;
		    $AvailableRoomDetails .= "<input type=\"hidden\" name=\"ScenarioUsed\" value=\"2\">" ; 
		    $AvailableRoomDetails .= "<input type=\"hidden\" name=\"SemesterAvailable\" value=\"$RoomForAvailableSem\">" ;
		    $AvailableRoomDetails .= '</div>' ;
		    
		    $AvailableRoomDetails .= '</form>';
	    }  // If Many Rooms Available

		    
		    return 	$AvailableRoomDetails;
    }


    public  function ConsolidateTimeSlot($hall_code,$room_no,$slot_no,$AcademicYear,$startdate, $enddate){
	    $startdateTSP = $this->ConvertMysqldate($startdate);
	    $enddateTSP =  $this->ConvertMysqldate($enddate);
	    
	    $PrevTimeSliceStartDate  = $this->ConvertTimeStampToMysqlDate($startdateTSP - $this->convertdaysToSeconds(1));
	    $NextimeSliceStEndDate =  $this->ConvertTimeStampToMysqlDate($enddateTSP + $this->convertdaysToSeconds(1));

	    $TimeSlotsCount = 0;
	    
	    $PrevTimeSliceSQL = "SELECT start_date, end_date
						    FROM hamis.available_rooms
						    WHERE (date(end_date) = date('$PrevTimeSliceStartDate') )  AND status = 'AVAILABLE' 
							    AND hall_code ='$hall_code'
							    AND room_no ='$room_no'
							    AND slot ='$slot_no'
							    AND academic_year ='$AcademicYear' ";
						    
	    $PrevTimeSliceRS = $this->MysqlHamisDB->GetRow($PrevTimeSliceSQL);
	    
	    if(strlen($PrevTimeSliceRS ['start_date']))
	    {
		    $OutertimeSliceStarDate = $PrevTimeSliceRS ['start_date'] ;
		    
		    $TimeSlotsCount = $TimeSlotsCount +1 ;
		    $RemoveTimeSlotSQL[$TimeSlotsCount] = "DELETE FROM hamis.available_rooms
										    WHERE date(start_date)  =date'{$PrevTimeSliceRS[start_date]}') 
										    and date(end_date) = date('{$PrevTimeSliceRS[end_date]}') 
										    AND hall_code ='$hall_code'
										    AND room_no ='$room_no'
										    AND slot ='$slot_no'
										    AND academic_year ='$AcademicYear'";
										    
	    }else{
		    $OutertimeSliceStarDate = $startdate;
	    }
	    
	    $NextTimeSliceSQL = "SELECT start_date, end_date
						    FROM hamis.available_rooms
						    WHERE (date(start_date) = date('$NextimeSliceStEndDate') )  AND status = 'AVAILABLE'
						    AND hall_code ='$hall_code'
						    AND room_no ='$room_no'
						    AND slot ='$slot_no'
						    AND academic_year ='$AcademicYear'";							

	    $NextTimeSliceRS = $this->MysqlHamisDB->GetRow($NextTimeSliceSQL);  
	    if(strlen($PrevTimeSliceRS ['end_date']))
	    {
		    $OutertimeSliceEndDate = $PrevTimeSliceRS ['end_date'] ;
		    $TimeSlotsCount = $TimeSlotsCount +1 ;
		    $RemoveTimeSlotSQL[$TimeSlotsCount] = "DELETE FROM hamis.available_rooms
										    WHERE date(start_date)  = date'{$NextTimeSliceRS[start_date]}')  
										    and date(end_date) = date('{$NextTimeSliceRS[end_date]}') 
										    AND hall_code ='$hall_code'
										    AND room_no ='$room_no'
										    AND slot ='$slot_no'
										    AND academic_year ='$AcademicYear'";			
	    }else{
		    $OutertimeSliceEndDate = $enddate ;
	    }
	    
	    //Check any Time Slice within the Start and ENd date
	    $SandwichSliceSQL = "SELECT start_date, end_date, status
				    FROM hamis.available_rooms
				    WHERE date(start_date)  between (date('$PrevTimeSliceStartDate')   and  date('$NextimeSliceStEndDate')) 
				    AND hall_code ='$hall_code'
				    AND room_no ='$room_no'
				    AND slot ='$slot_no'
				    AND academic_year ='$AcademicYear'";		
	    
	    $SandwichSliceRS = $this->MysqlHamisDB->GetRow($SandwichSliceSQL); 
	    $SandWichSliceTaken = FALSE;
	    $TakeSliceCount = 0;
	    if(is_array($SandwichSliceRS))
	    foreach($SandwichSliceRS as $SanwichKey => $SandwichSliceInfo)
	    {
		    if(($SandwichSliceInfo['status'] == 'AVAILABLE') || ($SandwichSliceInfo['status'] == 'WAITING CONFIRMATION') ){
			    $TakeSliceCount = $TakeSliceCount + 1; 
			    $SliceTaken[$TakeSliceCount]['start_date'] = $this->ConvertTimeStampToMysqlDate($SandwichSliceInfo['start_date']); 
			    $SliceTaken[$TakeSliceCount]['end_date'] =  $this->ConvertTimeStampToMysqlDate($SandwichSliceInfo['end_date']); 
			    $SandWichSliceTaken = TRUE;
		    }else{
			    $RemoveTimeSlotSQL[$TimeSlotsCount] = "DELETE FROM hamis.available_rooms
										    WHERE date(start_date) = date('{$SandwichSliceInfo[start_date]}')  
										    and date(end_date) = date('{$SandwichSliceInfo[end_date]}') 
										    AND hall_code ='$hall_code'
										    AND room_no ='$room_no'
										    AND slot ='$slot_no'
										    AND academic_year ='$AcademicYear'";
		    }
		    
	    } // For Each Sandwitch Time Slice
	    
	    $this->MysqlHamisDB->StartTrans();
	    if($SandWichSliceTaken === FALSE){
		    if(is_array($RemoveTimeSlotSQL))
		    foreach($RemoveTimeSlotSQL as $key => $SandwichSQLs)
		    {
			    $this->MysqlHamisDB->execute($SandwichSQLs);
		    }
		    //Create the New time Slice
		    $Success = $this->AddAvailableRooms($hall_code,$room_no,$OutertimeSliceStarDate,$OutertimeSliceEndDate,$academicYear,$slot_no);
		    $NewTimeSlice[0]=array('start_date'=>$OutertimeSliceStarDate,'end_date'=>$OutertimeSliceEndDate);
	    }else{ // when there is an already booked/confrimed/waited Time Sclice
		    asort($SliceTaken);
		    for($i=1; $i<=$TakeSliceCount; $i++){
			    if($i==1){
				    $StartDateTPS = $this->ConvertMysqldate($OutertimeSliceStarDate) ;
				    $EndDateTPS =  $SliceTaken[$i]['start_date'];
				    $StartDate = $this->ConvertTimeStampToMysqlDate($StartDateTPS) ;
				    $EndDate = $this->ConvertTimeStampToMysqlDate($SliceTaken[$i]['start_date'] -  $this->convertdaysToSeconds(1)) ;
				    if(conversecondtoDays($StartDateTPS - $StartDateTPS ) >= $this->DaysBetween){
					    $Success = $this->AddAvailableRooms($hall_code,$room_no,$StartDate ,$EndDate,$academicYear,$slot_no);
					    $NewTimeSlice[1] =array('start_date'=>$StartDate,'end_date'=>$EndDate);
				    }
			    }elseif($i==$TakeSliceCount){
					    $StartDateTPS = $SliceTaken[$i]['end_date'];
					    $EndDateTPS =  $this->ConvertMysqldate($OutertimeSliceEndDate);
					    $EndDate =$OutertimeSliceEndDate;
					    $StartDate =  $this->ConvertTimeStampToMysqlDate($StartDateTPS +  $this->convertdaysToSeconds(1)) ;
					    if(conversecondtoDays($EndDateTPS - $StartDateTPS) >= $this->DaysBetween){
						    $Success = $this->AddAvailableRooms($hall_code,$room_no,$StartDate ,$EndDate,$academicYear,$slot_no);
						    $NewTimeSlice[2] =array('start_date'=>$StartDate,'end_date'=>$EndDate);
					    }
			    }else{ // Else $i==$TakeSliceCount
				    $PreviousEndTimeTPS = $SliceTaken[$i-1]['end_date'];
				    $currentStartTimeTPS = $SliceTaken[$i]['start_date']; 
				    $currentEndTimeTPS  = $SliceTaken[$i]['end_date'];
				    if(conversecondtoDays($currentStartTimeTPS-$PreviousEndTimeTPS) >=$this->DaysBetween){
					    $StartDate = $this->ConvertTimeStampToMysqlDate($PreviousEndTimeTPS + $this->convertdaysToSeconds(1)) ; 
					    $EndDate =  $this->ConvertTimeStampToMysqlDate($currentStartTimeTPS - $this->convertdaysToSeconds(1));
					    $Success = $this->AddAvailableRooms($hall_code,$room_no,$StartDate ,$EndDate,$academicYear,$slot_no);
					    $NewTimeSlice[3] =array('start_date'=>$StartDate,'end_date'=>$EndDate);
				    } 
			    }
			    
		    }// End for each of the taken Sandwich time slice			
	    } //else(Sandwich time slots taken
	    $this->MysqlHamisDB->CompleteTrans();
	    
	    return $NewTimeSlice;
    } // Function Consolidate time slice

    public  function confirm_booking($RegNo, $AcademicYear,$ConfirmLevel='ALLOCATE') { //CONFIRM_WAITING
		     $SQL = "SELECT booking_id, reg_no, academic_year,semester,degree_code, level_of_study,start_date,end_date,
						    hall_code,room_no,slot_no,daily_room_charge,date_booked,booking_status,invoice_amout
				     FROM hamis.student_room_bookings 
				     WHERE (((reg_no = '$RegNo') AND academic_year ='$AcademicYear') 
						    AND (booking_status = 'BOOKED' OR booking_status = 'BOOKED_QUEUE' or booking_status = 'CONFIRM_WAITING')) ";
				     
		     $bookedrooms  = $this->MysqlHamisDB->GetArray($SQL);
		     $TotalInvoiceAmount = 0;
		     $SpecialCase = FALSE ;
		     if(is_array($bookedrooms)) {
			    $ReasonfornotConfirming =array();
			    $MysqlUpdates = array()  ;
			    $OracleUpdates= array();				  
			    foreach ($bookedrooms as $key => $bookedroom) {
			       $BookingID = $bookedroom['booking_id'];
			       $RegNo = $bookedroom['reg_no'];
			       $AcademicYear = $bookedroom['academic_year'];
			       $DegreeCode = $bookedroom['degree_code'];
			       $LevelOfStudy = $bookedroom['level_of_study'];
			       $DateBooked = $bookedroom['date_booked'];
			       $BookingStatus = $bookedroom['booking_status'];
			       $DateAllocated = $bookedroom['start_date'];
			       $ExitDate = $bookedroom['end_date'];
			       $Semester= $bookedroom['semester'];
			       $HallCode =$bookedroom['hall_code'];
			       $RoomNo = $bookedroom['room_no'];
			       $DailyCharge =$bookedroom['daily_room_charge'];
			       $InvoiceAmount = $bookedroom['invoice_amout'];  
				   $BedNo  = $bookedroom['slot_no'];
			       
			       
			    if($BookingStatus == 'CONFIRM_WAITING'){
				    $SQL = "UPDATE hamis.student_room_bookings SET booking_status = 'BOOKED_QUEUE' ,synchronization_flag=1 WHERE booking_id = $BookingID"  ;
				    array_push($MysqlUpdates,$SQL);  //$this->MysqlHamisDB->execute($SQL);
				    continue;
			    }				   
			      
			     if(($key ==0) && ($BookingStatus ='BOOKED')){
				    $InNominalroll = $this->NorminalRollSign($RegNo,$AcademicYear) ;
					//added 
					$SpecialNeed=$this->GetSpecialCase($RegNo);
					 if($SpecialNeed === TRUE)
					    $SpecialNeed = TRUE ;
				    else
					     $SpecialNeed = FALSE ; 
				   //end added
				    $Module = $this->TranslateStudentCategory($InNominalroll['Category']);
//changed the following line
					if($Module == 1 || $Module == 2)
					{
						if((!$InNominalroll['InNominalRoll'] || $InNominalroll['Balance'] > 0) && ($SpecialNeed === FALSE)) {
							array_push($ReasonfornotConfirming,'NORMINAL_ROLL'); 
							if($InNominalroll['Balance'] > 0)
								$this->SetError( $this->GetError() .  "  <br>You have have not Paid Tutition Fees of " . number_format($InNominalroll['Balance'],2) . "<br> Ensure that you clear All the outstanding Tuition Fees before confirming the allotted room") ;						
						}
					}
				    $StudentMisconduct = $this->StudentMisconducts($RegNo)    ;

				    if ($StudentMisconduct === TRUE){
					    array_push($ReasonfornotConfirming,'MISCONDUCT'); 
				    }  

				    $Balance = $this->GetSwaBalance($RegNo,$AcademicYear)  ;
				    $SpecialCase = $this->StudentSpecialCase($RegNo,$AcademicYear);
				    if($SpecialCase === TRUE)
					    $SpecialCase = TRUE ;
				    else
					     $SpecialCase = FALSE ;  
				    
				    $ClearedfromRoom = $this->CheckRoomClearance($RegNo,FALSE) ;
                    
				    if($ClearedfromRoom === FALSE){
					    array_push($ReasonfornotConfirming,'CLEARANCE'); 
				    }	
			     } // Check the Constant Conditions only Onces for all the Semesters/Rooms Booked  
			     
			     $TotalInvoiceAmount =  $TotalInvoiceAmount + $InvoiceAmount ;
                 
			      if  ($BookingStatus == 'BOOKED') {	
					    $OracleExitDate = $this->ConvertTimeStampToOracleDate($this->ConvertMysqldate($ExitDate))  ;
					    $OracleDateAlocated  = $this->ConvertTimeStampToOracleDate($this->ConvertMysqldate($DateAllocated));
					    $AllocateSQL = $this->SaveAllocation($RegNo,$Semester,$AcademicYear,$OracleDateAlocated,$HallCode,$RoomNo,$OracleExitDate,$DailyCharge,$Module,FALSE,$BedNo);
					    array_push($OracleUpdates,$AllocateSQL) ;
					    $SQL = "UPDATE hamis.student_room_bookings SET booking_status = 'CONFIRMED',synchronization_flag=1,date_confirmed = now(), time_confirmed = now() WHERE booking_id = $BookingID "  ;
					    array_push($MysqlUpdates,$SQL); //$this->MysqlHamisDB->execute($SQL);
			      } elseif ($BookingStatus == 'BOOKED_QUEUE') {
					    $SQL = "SELECT booking_notification.notification_date, room_waiting.reg_no, room_waiting.academic_year, room_waiting.degree_code, room_waiting.level_of_study
							    FROM hamis.booking_notification, hamis.room_waiting 
							    WHERE (room_waiting.reg_no ='$RegNo'
							    AND room_waiting.academic_year ='$AcademicYear'
							    AND room_waiting.degree_code ='$DegreeCode'
							    AND room_waiting.level_of_study ='$LevelOfStudy')
							    AND (DATE_ADD(notification_date, INTERVAL 3 DAY)) <=  date_format(now(),'%Y-%m-d%')
                                AND (booking_notification.queue_id = room_waiting.queue_id) ";
					    $result  = $this->MysqlHamisDB->execute($SQL);
					    if (strlen($result)) {
							    $AllocateSQL = $this->SaveAllocation($RegNo,$Semester,$AcademicYear,$OracleDateAlocated,$HallCode,$RoomNo,$OracleExitDate,$DailyCharge,$Module,FALSE,$BedNo);
							    array_push($OracleUpdates,$AllocateSQL) ; 
							    $SQL = "UPDATE hamis.student_room_bookings SET booking_status = 'CONFIRMED',synchronization_flag=1,date_confirmed = now(), time_confirmed = now() WHERE booking_id = $BookingID"      ; /* Update this Record INhe SWA.STUDENT_ROOM_ALLOCATION */
							    array_push($MysqlUpdates,$SQL); //$this->MysqlHamisDB->execute($SQL);
					    }
			      }
			      
		       }//end foreach
		       
		      // $TotalInvoiceAmount = $TotalInvoiceAmount + $Balance;
		       
		       if(($Balance > 0) && ($SpecialCase === FALSE)){
				    array_push($ReasonfornotConfirming,'BALANCE');
				    $this->SetError( $this->GetError() .  "  You have have not Paid Accomodation Fees of " . number_format($Balance,2) . "<br> Ensure that you clear All the outstanding Accomodation Fees including the fees chrged for the current booking before confirming the room") ;
		       } 
		       
		       if((count($ReasonfornotConfirming) == 0) || ($BookingStatus == 'CONFIRM_WAITING')){
			       $this->Ora_Db->StartTrans();
			       $OracleErrorConter = 0;
			       foreach($OracleUpdates as $OracleSQL){
					    $result = $this->Ora_Db->Execute($OracleSQL)  ;
					    if($this->Ora_Db->ErrorNo() != 0)
						    $OracleErrorConter++;
				    } 

				    
				    $this->MysqlHamisDB->StartTrans();
				    $MysqlErrorConter = 0  ;
				    
				    foreach($MysqlUpdates as $MySQLSQL){ 
					     $result = $this->MysqlHamisDB->Execute($MySQLSQL) ;
					     if($this->MysqlHamisDB->ErrorNo() != 0)
						    $MysqlErrorConter++;
				    }
				    
				    if(($MysqlErrorConter > 0) || ($OracleErrorConter > 0)) {   // Both Transactions Fails is neither is successiful
					    $this->MysqlHamisDB->FailTrans();
					    $this->Ora_Db->FailTrans();  
				    }	
					    
				    $this->Ora_Db->CompleteTrans(); 
				    $this->MysqlHamisDB->CompleteTrans(); 
				    
			    }else{//if there is no Reason to Stop Room Confirmation
				    return FALSE ;
			    }
			    
		     } // if The Student had booked a room
	    } //end function
	    
    public function TranslateStudentCategory($CategoryCode){
	    $StudentCategory = 1;
	    switch($CategoryCode){
		    case '001':
			    $StudentCategory = 1;
		    break;
		    case '002':
			    $StudentCategory = 2;
		    break;
			case '003':
			    $StudentCategory = 3;
		    break;
		    default:
			    $StudentCategory = 3;
			    break;
	    }
	    return $StudentCategory ;
    }	

    public function SaveAllocation($RegistratioNo,$Semester,$AcademicYear,$DateAllocated,$HallCode,$RoomNo,$ExitDate,$DailyCharge,$Module=1,$ExecuteSQL=TRUE,$BedNo){
	     
		    if (!$Module)
			    return FALSE;
			    
		    $DailyCharge = (int) $DailyCharge ;
			    
		    if($DailyCharge <= 0) 
			    $DailyCharge = 150;
	    
			    $RegistrationNumber = trim($RegistratioNo);
			    $Semester = trim($Semester);
			    $AcademicYear = trim($AcademicYear);
			    $DateAllocated = trim($DateAllocated);
			    $HallCode= trim($HallCode);
			    $RoomNo = trim($RoomNo);
			    $ExitDate = trim($ExitDate);
			    $Nature = 'FRESH' ; 
			    $Occupancy = 0;
			    // Save Student room Allocation
	    
		       if ($Module == 1)
			     {
				    $Occupancy = (int) $Occupancy;
				    $DailyCharge = (float) $DailyCharge ;
				    
				    $StudentAllocationSQL = "INSERT INTO SWA.STUDENT_ROOM_ALLOCATIONS (REGISTRATION_NUMBER,ROOM_NO,HALL_CODE,SEMESTER,
									    ACADEMIC_YEAR,DATE_ALLOCATED,NATURE_OF_ALLOCATION,CHARGE_PER_DAY,OCCUPANCY,USER_NAME,BED_NO) VALUES('$RegistrationNumber',
									    '$RoomNo','$HallCode','$Semester','$AcademicYear',
									    to_date('$DateAllocated','DD-MON-YYYY'),'$Nature',$DailyCharge,$Occupancy,user,$BedNo)";
								    
			    }
			    elseif($Module == 3)
			    {
				    $StudentAllocationSQL ="INSERT INTO SWA.MODULEII_ROOM_ALLOCATIONS(REGISTRATION_NUMBER,ROOM_NO,
										    HALL_CODE,SEMESTER,ACADEMIC_YEAR,DATE_ALLOCATED,CURRENT_STATUS,CHARGE_PER_DAY,DATE_OF_EXIT) 
										    VALUES('$RegistrationNumber','$RoomNo','$HallCode',
										    '$Semester', '$AcademicYear',TO_DATE('$DateAllocated','DD-MON-YYYY'),
										    upper('$Nature'), $DailyCharge,TO_DATE('$ExitDate','DD-MON-YYYY'))";
										//echo $StudentAllocationSQL;
			    }							   
								    
			    //echo $StudentAllocationSQL;		
			    if($ExecuteSQL === TRUE) {
				    $Result = $this->Ora_Db->Execute("$StudentAllocationSQL");   
				    if($Result )					   
					    return TRUE;
				    else
					    return FALSE;
			    }else{
				    return   $StudentAllocationSQL ;
			    }
    } // End the Save Allocation Function

	    
    public function checkIfNotificationIsExpired($AcademicYear){
			    
			    $SQL = "SELECT days_between FROM hamis.room_admin_data ORDER BY academic_year_date DESC LIMIT 1 OFFSET 0 ";
			    $DaysBetween = $this->MysqlHamisDB->GetOne( $SQL );
			    $DaysBetween = $DaysBetween?$DaysBetween : 3; 
			    $SQLCount =  "SELECT count(queue_id) AS Count FROM hamis.room_waiting";
			    $resultQueueCount = $this->MysqlHamisDB->Execute($SQLCount);
			    $QueueCount = $resultQueueCount['Count'];
			    $SQL = "SELECT room_waiting.queue_id, room_waiting.reg_no, room_waiting.waiting_date, room_waiting.waiting_time, room_waiting.status, room_waiting.academic_year, room_waiting.degree_code, room_waiting.gender, room_waiting.level_of_study 
					    FROM hamis.booking_notification LEFT JOIN hamis.room_waiting ON (booking_notification.queue_id = room_waiting.queue_id)
					    WHERE (DATE_ADD(notification_date, INTERVAL $DaysBetween DAY) AS notication_date_expiry ) <= NOW() AND room_waiting.status ='NOTIFIED' ";
					    
			    $results  = $this->MysqlHamisDB->getArray($SQL);
			    $newQueueID = $QueueCount + 1;
			    if(is_array($results))
			    foreach ($results as $result)
			    {
			       $QeueID = $result['queue_id'];
			       
			       $SQLUpdate = "UPDATE room_waiting SET  status='EXPIRED' WHERE queue_id = $QeueID ";
			       $SQLInsert ="INSERT INTO room_waiting(reg_no, waiting_date, waiting_time, status, academic_year, degree_code, gender, level_of_study) 
							     VALUES ('{$result['reg_no']}','{$result['waiting_date']}','{$result['waiting_date']}','{$result['waiting_time']}','WAITING','{$result['academic_year']}','{$result['degree_code']}','{$result['gender']}','{$result['level_of_study']}')";
			       $this->MysqlHamisDB->StartTrans();
			       $this->MysqlHamisDB->Execute($SQLUpdate);
			       $this->MysqlHamisDB->Execute($SQLInsert);
			       $this->MysqlHamisDB->CompleteTrans();
			       
			     }  
    }

    //Check whether the student has signed the Nominal roll
    public  function NorminalRollSign($RegNo,$academicYear){
		    $StudentInfo = array("RegistrationNumber","Surname"=>"","OtherNames"=>"","InNominalRoll"=>1,"Category"=>"","Status"=>"","Balance"=>0);
		    $RegNo= strtoupper(trim($RegNo));
		    $AcadYear = $academicYear;
            $StudentInfo['InNominalRoll'] = 0;
	       $CheckNominalRollWhere="(REGISTRATION_NUMBER LIKE '$RegNo') " ;
	       $CheckNominalRollSQL ="SELECT REGISTRATION_NUMBER,STC_STUDENT_CATEGORY_ID,STUDENT_STATUS, " .
							    "SURNAME, OTHER_NAMES,swa_admin.GetSmisBalance('$RegNo') AS BALANCE " .
							    "FROM MUTHONI.UON_STUDENTS " ;
							    
							    
	     //debug
	     //echo  $CheckNominalRollSQL  . " WHERE " . $CheckNominalRollWhere ;
		     
		     $rs = $this->Ora_Db->Execute("$CheckNominalRollSQL where $CheckNominalRollWhere order by REGISTRATION_NUMBER asc ");    
		      
		     $OccupiedRoom = '' ;
		     if ($arr = $rs->FetchRow()){
				     $StudentInfo['RegistrationNumber']= $arr['REGISTRATION_NUMBER'];
				     $StudentInfo['Category']=  $arr['STC_STUDENT_CATEGORY_ID'];
				     $StudentInfo['Status']  = $arr['STUDENT_STATUS'];
				     $StudentInfo['Surname'] =  $arr['SURNAME'];
				     $StudentInfo['OtherNames'] =	 $arr['OTHER_NAMES'];	
				     $StudentInfo['Balance'] =  $arr['BALANCE'];
                     $StudentInfo['InNominalRoll'] = 1;      
			    
		     }else{
			     $StudentInfo['InNominalRoll'] = 0;	
			     $this->SetError($this->GetError() .  'Student Reg No.<font color="red"> '. $RegNo .'</font> Does not Exist in the Nominal Roll ') ;
			     return   $StudentInfo;
	      }

	     return $StudentInfo;
    }
    // End Checking Nomil roll


    //Check whether the Student Has a record of Misconduct
    public function StudentMisconducts($RegNo){

	    //$CheckMisconduct->SetCountSQL("SELECT count(*) FROM SWA.STUDENTS_MISCONDUCTS  ");
	    $CheckMisconductWhere = " AND (REGISTRATION_NUMBER ='$RegNo') " ;			
	    $CheckMisconductSQL ="SELECT add_months(PUNISHMENT_DATE,PUNISHMENT_DURATION) AS EXPIRELY_DATE, " .
							    "MISCONDUCT_DESCRIPTION,PUNISHMENT_NATURE " .
							    "FROM SWA.STUDENTS_MISCONDUCTS WHERE (add_months(PUNISHMENT_DATE,PUNISHMENT_DURATION) > SYSDATE) " ;
							    
							    
       // echo "$CheckMisconductSQL where $CheckMisconductWhere";
	      $rs = $this->Ora_Db->Execute("$CheckMisconductSQL  $CheckMisconductWhere");    

	     $OccupiedRoom = '' ;
	     if ($arr = $rs->FetchRow())
	     {
			     $PunishementEpirelydate = $arr['EXPIRELY_DATE'];
			     $PunishementDescription = $arr['MISCONDUCT_DESCRIPTION'];
			     $Punishementnature = $arr['PUNISHMENT_NATURE'];	
			     $this->SetError($this->GetError() .  "Student Reg No. $RegNo  Has a registerd Misconduct of $PunishementDescription and was punished by $Punishementnature from Hall of residence until  $PunishementEpirelydate") ;
			     return TRUE;
	     }
	    else
		    return FALSE;
    }// end checking Student Miconducts

    // Function Check SWA Current Balances Excluding any Amount Invoiced as a result of Booking 
    public function GetSwaBalance($RegNo,$AcademicYear){
	    $rs = $this->Ora_Db->execute("SELECT SWA_ADMIN.GetBalance('$RegNo') AS BALANCE FROM DUAL ");
	    $Balance = 0; 
	    if ($arr = $rs->FetchRow()) {	
		    $Balance = $arr[BALANCE] ; // current_balance($reg_value,false);
	    }
	    
	    $SQL  ="SELECT SUM(invoice_amout) AS charged_amount FROM hamis.student_room_bookings
                WHERE (reg_no ='$RegNo' AND academic_year ='$AcademicYear' AND booking_status ='BOOKED')";
	      
	    $ChargedAmount = $this->MysqlHamisDB->GetOne($SQL ); 
	     $ChargedAmount = $Balance + $ChargedAmount ; 
	    return $ChargedAmount;
    } // End Function GetSwaBalance


    // Check whether a student has a special case
    public  function StudentSpecialCase($RegNo,$AcademicYear){

		    $StudentSpecialCaseWhere = " AND (REGISTRATION_NUMBER ='$RegNo') 
									    AND ACADEMIC_YEAR='$AcademicYear' ";
									    
		    $StudentSpecialCaseSQL ="SELECT add_months(DATE_ENTERED,DURATIONS_IN_MONTHS) AS EXPIRELY_DATE, " .
							    "REASON " .
							    "FROM SWA.STUDENT_SPECIAL_CASES WHERE  (add_months(DATE_ENTERED,DURATIONS_IN_MONTHS) > SYSDATE) " ;
							    
	     $rs = $this->Ora_Db->Execute("$StudentSpecialCaseSQL  $StudentSpecialCaseWhere order by REGISTRATION_NUMBER asc");    

	     $reason = '';
	    if ($arr = $rs->FetchRow()){
		     $SpecialCaseEpirelydate =  $arr['EXPIRELY_DATE'];
		     $SpecialCaseReason =  $arr['REASON'];
		     $reason = "Student Reg No. $RegNo  Has a registered special case of $SpecialCaseReason until  $SpecialCaseEpirelydate<br>"	;
		     $this->SetError($this->GetError() .  $reason);              
		     return TRUE;
	    }
	    else{
		    return FALSE;
	    }
    } // end Check student special case

    //Function Check Student Clearance
    function CheckRoomClearance($RegNo, $CCGetCheckClearance = FALSE){
	    if($CCGetCheckClearance === TRUE){
		    //Check whether the student has cleared form any other previously occupied room
		    $CheckClearanceSQL ="SELECT HL.HALL_CODE, HL.HALL_NAME,SRA.ROOM_NO FROM SWA.STUDENT_ROOM_ALLOCATIONS SRA,SWA.HALLS HL
							    WHERE SRA.HALL_CODE = HL.HALL_CODE AND SRA.CLEARANCE='UNCLEARED' 
							    AND REGISTRATION_NUMBER='$RegNo'
							    ORDER BY DATE_ALLOCATED DESC ";
											    
		    $rs = $this->Ora_Db->GetOne("$CheckClearanceSQL");
		    //$message[Clearance] = "<h3>$CheckClearanceSQL</h3>";
		    
		    if ($arr = $rs->FetchRow()){
			    $reason = " You have not cleared from a Previous Room(Hall {$arr[HALL_CODE]} {$arr[HALL_NAME]} Room  {$arr[ROOM_NO]} ), Kindly clear with the Hall Officer before seesking another room for this academic year <br>" ;
			    $this->SetError($this->GetError() . $reason);	
			    return FALSE  ;
		    }else{
			    return TRUE;
		    }
	    
	    } else{// checking whether the student has cleared is the user profile so dictate
		    return TRUE;
	    }
    }  // End Function CheckRoomClearance
	    
    // Check whether this student has a room
    public 	function HasRoom($RegNo,$AcademicYear){

		    
		    $StudentHasRoomWhere = "((EXIT_DATE IS NULL OR EXIT_DATE > SYSDATE)
							       AND (SRA.HALL_CODE=HL.HALL_CODE)  
							       AND (REGISTRATION_NUMBER ='$RegNo') 
							       AND (SRA.ACADEMIC_YEAR='$AcademicYear')) " ;
							       

	      $StudentHasRoomSQL ="SELECT SRA.ROOM_NO,SRA.HALL_CODE,HL.HALL_NAME " .
					       "FROM SWA.STUDENT_ROOM_ALLOCATIONS SRA,SWA.HALLS HL " ;
					       
	      $ModuleIIRooms = "SELECT MRA.ROOM_NO,MRA.HALL_CODE,HL.HALL_NAME FROM SWA.MODULEII_ROOM_ALLOCATIONS MRA,SWA.HALLS HL  
						    WHERE ((MRA.DATE_OF_EXIT IS NULL OR  MRA.DATE_OF_EXIT > SYSDATE) AND (MRA.REGISTRATION_NUMBER='$RegNo')
						      AND (MRA.HALL_CODE=HL.HALL_CODE) AND MRA.ACADEMIC_YEAR='$AcademicYear' ) ";
	    
	    //$stmt ="$StudentHasRoomSQL where $StudentHasRoomWhere UNION $ModuleIIRooms "; 
       /*
	    $stmt = $db->PrepareSP("begin swa_admin.TrialPackage.StudenthasRoom.GetSmucollection(:StudentinRoom,:RegNo) ; end;") ;
	    $db->InParameter($stmt,$RegNo,'RegNo');
	    $rs = $db->ExecuteCursor($stmt,'StudentinRoom');
	    */
	    //Debug
	    // echo "<h1>$StudentHasRoomSQL where $StudentHasRoomWhere order by SRA.ROOM_NO asc</h1>" ;
						       
	    // $rs = $db->Execute("$StudentHasRoomSQL where $StudentHasRoomWhere order by SRA.ROOM_NO asc"); 
	     $rs = $this->Ora_Db->Execute("$StudentHasRoomSQL where $StudentHasRoomWhere UNION $ModuleIIRooms ");
	     $OccupiedRoom = '' ;
	     if($arr = $rs->FetchRow())
	     {
			     $RoomNo = $arr[ROOM_NO];
			     $HallCode  =$arr[HALL_CODE];
			     $HallName  = $arr[HALL_NAME];
			    
	     }
	     
	     if (!empty($RoomNo) || !empty($HallCode)) {	 
		    $OccupiedRoom['ROOMINFO'] = "This student Already has a room in Hall $HallCode $HallName Room No. $RoomNo " ;
		    $OccupiedRoom['HASROOM'] = TRUE 	 ;
	     }else{
		    $OccupiedRoom['ROOMINFO'] = "" ;
		    $OccupiedRoom['HASROOM'] = FALSE  ;	 
	     }
	     
	     return $OccupiedRoom;

    }
    //end checking if student has a room

    function   getStudentCategory($RegistrationNo) {
	    $CategoryId = "" ;
	    $SQLQuery = "SELECT ALL MUTHONI.UON_STUDENTS.STC_STUDENT_CATEGORY_ID FROM MUTHONI.UON_STUDENTS
				     WHERE students.registration_number='$regNo' ";
	    
	    $CategoryId =  $this->Ora_Db->GetOne($SQLQuery);
	    
	     return  $CategoryId;
    }


    public function CancelRoomBooking($RegNo,$AcademicYear,$CancelReason='CANCEL') {
      $SQL = "SELECT student_room_bookings.booking_id, student_room_bookings.reg_no, student_room_bookings.hall_code, 
			      student_room_bookings.room_no , student_room_bookings.slot_no ,
			       student_room_bookings.start_date, student_room_bookings.end_date
		     FROM hamis.student_room_bookings 
		     WHERE ((student_room_bookings.academic_year ='$AcademicYear'
				    AND (student_room_bookings.booking_status ='BOOKED' OR student_room_bookings.booking_status ='CONFIRM_WAITING')
				    AND student_room_bookings.reg_no ='$RegNo' )) ";
				    
      $nonConfirmedRooms = $this->MysqlHamisDB->GetArray($SQL);
      $nonConfirmStatus = FALSE ;
	    if($nonConfirmedRooms && count($nonConfirmedRooms) > 0) { 
            $this->MysqlHamisDB->StartTrans(); 
	      $nonConfirmStatus = TRUE ;
	      if(is_array($nonConfirmedRooms))
		    foreach($nonConfirmedRooms as $nonConfirmedRoom=>$RoomInfo)
		    {
				    // get room info
				    $booking_id = $RoomInfo['booking_id'];
				    $reg_no = $RoomInfo['reg_no'];
				    $hall_code = $RoomInfo['hall_code'];
				    $room_no = $RoomInfo['room_no'];
				    $slot_no = $RoomInfo['slot_no'];
				    $startdate = $RoomInfo['start_date'];
				    $enddate = $RoomInfo['end_date'];
				     
				    
				    if($CancelReason=='REJECT_OFFER'){
					    $QueueSQL = "SELECT room_waiting.queue_id, room_waiting.reg_no, room_waiting.waiting_date, room_waiting.waiting_time, 
								    room_waiting.status, room_waiting.academic_year, room_waiting.degree_code, room_waiting.gender, room_waiting.level_of_study 
						    FROM hamis.booking_notification LEFT JOIN hamis.room_waiting ON (booking_notification.queue_id = room_waiting.queue_id)
						    WHERE (room_waiting.status ='NOTIFIED') and (room_waiting.reg_no ='$RegNo') and (room_waiting.academic_year='$AcademicYear')";
					    
					    $QueuedRecord = $this->MysqlHamisDB->GetRow($QueueSQL);
					    if(strlen($QueuedRecord['reg_no'])){
						    $SQLUpdate = "UPDATE room_waiting SET  status='EXPIRED' WHERE queue_id = {$QueuedRecord['queue_id']} ";
						    $SQLInsert ="INSERT INTO room_waiting(reg_no, waiting_date, waiting_time, status, academic_year, degree_code, gender, level_of_study) 
									     VALUES ('{$QueuedRecord['reg_no']}',now(),now(),'WAITING','{$QueuedRecord['academic_year']}','{$QueuedRecord['degree_code']}','{$QueuedRecord['gender']}','{$QueuedRecord['level_of_study']}')";
						    
						    $this->MysqlHamisDB->Execute($SQLUpdate);
						    $this->MysqlHamisDB->Execute($SQLInsert);
					    }
				    } 
				    
				    $CheckDuplicate = 'TRUE' ; 
				    $CancelTimeCount = 1;
				    while(strlen($CheckDuplicate)) { 
					    $UuID = $reg_no. '_' . $hall_code. '_' .$room_no. '_' .$slot_no. '_' .$startdate. '_' .$enddate.'_' . $CancelReason.'_' . $CancelTimeCount ;
					    $CheckDuplicate =  $this->MysqlHamisDB->GetOne("SELECT  reg_no FROM hamis.student_room_bookings WHERE uu_id ='$UuID'");						
					    $CancelTimeCount++; 
				    }
				    
				    $SQLRoomBookingUpdate = "UPDATE hamis.student_room_bookings SET booking_status='$CancelReason',uu_id='$UuID',date_confirmed = now(), time_confirmed = now(),synchronization_flag=1  
										    WHERE reg_no = '$reg_no' and booking_id = $booking_id ";
	      
				    $this->MysqlHamisDB->Execute($SQLRoomBookingUpdate);
				    $NewTimeSlice = $this->ConsolidateTimeSlot($hall_code,$room_no,$slot_no,$AcademicYear,$startdate, $enddate); 
				    
		    }
            $this->MysqlHamisDB->CompleteTrans(); 		
	    }
    } // End Function 	checkIfRoomConfirmed 

    public function ManageBookingResponse($RegistrationNo,$AcademicYear,$StudentSessions,$IsBookingOpen,$BookingStatus){
	    $DegreeCode = $this->CCGetSession("DegCode");
	    $LevelofStudy = $this->CCGetSession("LevelofStudy");
	    $Gender = $this->CCGetSession("gender");  
		    
	    if(!is_array($StudentSessions)){
		    //$this->SetError($this->GetError() . "<br> Function : ManageBookingResponse The variable  StudentSessions is not an array ");
		    $RegistrationNo = $this->CCGetSession("RegNo");			
		    //$this->SetError($this->GetError() . "<br> Function : ManageBookingResponse The variable  DegreeCode = $DegreeCode,AcademicYear = $AcademicYear,LevelofStudy = $LevelofStudy ");
		    $StudentSessions = $this->GetStudentSesions($DegreeCode,$AcademicYear,$LevelofStudy);
		    //print_r($SetSeessions);
		    //$this->SetError($this->GetError() . "<br> Function : ManageBookingResponse The variable  SetSeessions =". array_walk($SetSeessions, 'flatten_array') );
	    }
	    
	    $BookRoomBtn = $this->CCGetParam("BookRoomBtn"); 
	    $RoomCount = $this->CCGetParam("RoomCount");
	    $ConfirmRoom = $this->CCGetParam("ConfirmRoom");
	    $QueueAcceptOfferedRoom = $this->CCGetParam("QueueConfirmRoom");
	    $ScenarioUsed = $this->CCGetParam("ScenarioUsed");  
	    $SemesterAvailable =  $this->CCGetParam("SemesterAvailable"); 
	    $Invoice  =  $this->CCGetParam("Invoice");  // =INVOICE
	    $BtnQueResponce = $this->CCGetParam("BtnQueResponce");   
	    $BtnRoomApplication = $this->CCGetParam("BtnRoomApplication");
	    $BtnRoomContApplication = $this->CCGetParam("BtnRoomContApplication");
		$BtnRoomModuleIIApplication = $this->CCGetParam("BtnRoomModuleIIApplication");
        $BtnRoomContUpdateApplication  = $this->CCGetParam("BtnRoomContUpdateApplication");
	    $CancelQue = $this->CCGetParam("CancelQue");  
	    $UpdateApplication  = $this->CCGetParam("UpdateApplication"); 
		$UpdateModuleIIApplication  = $this->CCGetParam("UpdateModuleIIApplication");  
		$BtnRoomModIIUpdateApplication = $this->CCGetParam("BtnRoomModIIUpdateApplication");  
        $ApplicantID =  $this->CCGetParam("ApplicationID");
          
	    if(!is_array($StudentSessions) || count($StudentSessions) < 1)       
		    $StudentSessions = $this->CCGetParam("StudentSessions");  
		       
	    
	    if($Invoice == 'INVOICE'){
		    $DisplayName = $this->CCGetSession("surname") . " " .  $this->CCGetSession("otherNames") ;
		    
		    $this->GeneratePdf($RegistrationNo,$DisplayName,$AcademicYear,$BookingStatus);
	    }
	    
	    
	    if($BtnRoomApplication){
		      $DisplayInformation .= $this->ProcessRoomApplication($RegistrationNo,$AcademicYear);
	    }
	    
	    if($BtnRoomContApplication){
		      $DisplayInformation .= $this->ProcessContinuingRoomApplication($RegistrationNo,$AcademicYear);
	    }
		
		if($BtnRoomModuleIIApplication){
		      $DisplayInformation .= $this->ProcessModuleIIRoomApplication($RegistrationNo,$AcademicYear);
	    }
	    
	    if(strlen($BookRoomBtn)){
		       $DisplayInformation .= $this->ProcessSelectedRoom($RegistrationNo,$AcademicYear,$StudentSessions,$IsBookingOpen,$ScenarioUsed,$SemesterAvailable)	;
	     }
         
	    if($BtnRoomContUpdateApplication){
            $DisplayInformation .= $this->ProcessContinuingRoomApplication($RegistrationNo,$AcademicYear,TRUE);   //Process Updated form  submitted      
        } 
		
		if($BtnRoomModIIUpdateApplication){
            $DisplayInformation .= $this->ProcessModIIRoomApplication($RegistrationNo,$AcademicYear,TRUE);   //Process Updated form  submitted      
        }
        
        if($UpdateApplication){
           $DisplayInformation .= $this->UpdateRoomApplicationForm ($RegistrationNo,$AcademicYear,$ApplicantID,$ApplicationStatus) ;      //Call the Udate Form
        }
		
		 if($UpdateModuleIIApplication){
           $DisplayInformation .= $this->UpdateModIIRoomApplicationForm ($RegistrationNo,$AcademicYear,$ApplicantID,$ModIIApplicationStatus) ;      //Call the Udate Form
        }
        
	     if($ConfirmRoom == 'YES'){
		    $Result = $this->confirm_booking($RegistrationNo,$AcademicYear,'ALLOCATE');
		    if($Result === FALSE)
			    $DisplayInformation .=   $this->GetError();
		    else
			    $DisplayInformation .=   ' Room Confirmed <br>'; 
	     }elseif($ConfirmRoom == 'CANCEL') {
		    $DisplayInformation .= $this->CancelRoomBooking($RegistrationNo,$AcademicYear,'CANCEL'); // Cancel the Booking, Same as Expiring it
	     }
	     
	     if($QueueAcceptOfferedRoom == 'YES')  {
		    $DisplayInformation .= $this->confirm_booking($RegistrationNo,$AcademicYear,'CONFIRM_WAITING') ;
	     }elseif($QueueAcceptOfferedRoom == 'NO') {
		    $DisplayInformation .= $this->CancelRoomBooking($RegistrationNo,$AcademicYear,'REJECT_OFFER');// Cancel the Booking, Same as Expiring it
	     }
	     
	     if(strlen($BtnQueResponce)) {
		     $DisplayInformation .= $this->QueStudent($RegistrationNo,$AcademicYear,$DegreeCode,$LevelofStudy,$Gender) ;
	     }
	     
	     if($CancelQue=='YES'){	
		     $DisplayInformation .= $this->CancelBooking($RegistrationNo,$AcademicYear)	 ;	 
	     }
	     
	     return  $DisplayInformation ;
    }	


    public function CancelBooking($RegistrationNo,$AcademicYear){
	    $CurrentQuedSQL = "SELECT queue_id   
						    FROM hamis.room_waiting 
						    WHERE (reg_no = '$RegistrationNo' 
						    AND status ='WAITING'  
						    AND academic_year ='$AcademicYear') " ;
						    
	    $QueID =  $this->MysqlHamisDB->GetOne($CurrentQuedSQL); 
	    
	    $SQLUpdate =  " UPDATE hamis.room_waiting SET  status='EXPIRED' WHERE queue_id = $QueID ";
	    $Result =  $this->MysqlHamisDB->GetOne($SQLUpdate);
	    if(!$Result)
		    return FALSE;
	    else
		    return TRUE;
    }

    public function QueStudent($RegistrationNo,$AcademicYear,$DegreeCode,$LevelofStudy,$Gender){
	    
	    if(strlen($RegistrationNo) && strlen($AcademicYear) && strlen($DegreeCode) && strlen($LevelofStudy) && strlen($Gender) )  {
		    $CheckDuplicateSQL = "SELECT queue_id FROM hamis.room_waiting WHERE reg_no='$RegistrationNo' AND academic_year='$AcademicYear' AND status='WAITING'" ;
		    $AlreadyQueued = $this->MysqlHamisDB->GetOne($CheckDuplicateSQL);
		    if(!strlen($AlreadyQueued)) {
			    $QueueStudentSQl = "INSERT INTO hamis.room_waiting(reg_no,waiting_date,waiting_time,status,academic_year,degree_code,gender,level_of_study)
							    VALUES('$RegistrationNo',now(),now(),'WAITING','$AcademicYear','$DegreeCode','$Gender','$LevelofStudy')";
			    $Result = $this->MysqlHamisDB->Execute($QueueStudentSQl); 	
			    if($Result)
				    return '';
			    else
				    return "Error Occured when trying to Queue your request for Notification when a room becomes available <br> "; 
	    }else{
		    return "You are already Qued for a notfication <br> ";
	    }
	    
	    }else
		    return "Your request could not be processed becase Your Nominal roll Data is not updated for the $AcademicYear Academic Year<br>";	
    }	

    public function   StartRoomBookingProcess($RegistrationNo,$AcademicYear,$StudentsSessions,$Gender,$DegreeCode,$LevelofStudy){
	    /* Remember to Check if the Student Qualifies */
		      $BookingMesg = ''; 		
		      $HasRoom = $this->HasRoom($RegistrationNo,$AcademicYear)   ;
		      
		      if($HasRoom['HASROOM'] === TRUE)
			     $BookingMesg .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 16px; font-weight: bold; align:center">' . $HasRoom['ROOMINFO'] . '</span>';
		      else{
				    $CurrentRoomOccupation = $this->GetCurrentGroupOccupation($DegreeCode,$Gender,$LevelofStudy,$AcademicYear) ;
				    $CurrentGenderAllotedSpaces =   $this->CCGetSession("GenderAllotment");  
				    $CurrentGroupAllotedSpaces =   $this->CCGetSession("GroupAllotment"); 
				    if($CurrentRoomOccupation < $CurrentGenderAllotedSpaces)  
					    $BookingMesg .= $this->GetAvailableRooms($StudentsSessions,$Gender,$DegreeCode,$LevelofStudy)   ;  // Display room that can be booked, or promt Stsudnet to Enter Waiting List
				    else {
					    $BookingMesg .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 16px; font-weight: bold; align:center">The rooms allotted to your group for '.$AcademicYear .' have been Exhausted. <br></span>';
					    $BookingMesg .= $this->ShowRequestToQueue() ;
				    }
		       }	
		      return $BookingMesg ;
	    
    }

    public function ShowRequestToQueue(){
	    $QueingInfo = '<form id="RequesttoQueFrm" name="RequesttoQueFrm" method="post" action="' . $_SERVER['PHP_SELF'] .'">
				      <table width="200" border="1" align="center" cellpadding="3" cellspacing="0">
				      <caption><strong>Would you like to be Queued and Notified when a Room Becomes available?</strong></caption>   <tr>
					      <td><label>
						    <input type="radio" name="QueueResponce" value="YES" />
						    YES</label>
						    &nbsp; </td>
					      <td><label>
						    <input type="radio" name="QueueResponce" value="NO" />
						    NO</label></td>
					    </tr>
				      <tr>
					    <td colspan="2" align="center"><label>
					      <input name="BtnQueResponce" type="submit" id="BtnQueResponce" value="Enter Waiting List" />
					    </label></td>
					    </tr>
				      </table>
				    </form>';
				    
	    return $QueingInfo;
	    
    }

    public function ErrorMessages(){
	    
	    $ErrorMsg =  '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 16px; font-weight: bold; align:center">' . $this->GetError() . '</span>';
	    return $ErrorMsg;
    }

    public function CheckApplicationStatus($AcademicYear,$DegreeCode,$LevelofStudy){
		  $NotificationSQL="SELECT MAX(APPLICATION_END_DATE) as CLOSING_DATE,MAX(APPLICATION_START_DATE) AS START_DATE
							FROM hamis.student_academic_sessions
							 WHERE DEGREE_CODE='$DegreeCode' and ACADEMIC_YEAR='$AcademicYear' and YEAR_OF_STUDY=$LevelofStudy";
							
			$ClosingDate =  $this->MysqlHamisDB->GetRow($NotificationSQL);
			if(strlen($ClosingDate['CLOSING_DATE'])) {
				  $ClosingDateTPS = $this->ConvertMysqldate($ClosingDate['CLOSING_DATE'])  ;
				  $StartingDateTPS =  $this->ConvertMysqldate($ClosingDate['START_DATE'])  ;
				  $Today =    $this->ConvertMysqldate(date('Y-m-d') )  ;
				 // $Today  = $Today  + (60*60*7);
				if($Today > $ClosingDateTPS){
					return 'CLOSED' ;		
				}elseif($Today <  $StartingDateTPS){
					 $DaysRemaining =   $this->conversecondtoDays($StartingDateTPS - $Today) ;
					 return array("STATUS"=>'PENDING',"START_DATE"=>$ClosingDate['START_DATE'],"CLOSING_DATE"=>$ClosingDate['CLOSING_DATE'],"DAYS_REMAINING"=>$DaysRemaining) ;  
				 }else{
					 $DaysRemaining =   $this->conversecondtoDays($ClosingDateTPS - $Today) ;
					return array("STATUS"=>'OPEN',"CLOSING_DATE"=>$ClosingDate['CLOSING_DATE'],"DAYS_REMAINING"=>$DaysRemaining) ;
				}
			}else{
				return 'NO SET DATE';
			}
    }

   public function checkModuleIIApplicationStatus($Cat_ID){
   		$NotificationSQL="SELECT MAX(APPLICATION_START_DATE) as CLOSING_DATE,MAX(APPLICATION_END_DATE) as CLOSING_DATE
								FROM hamis.other_category_session
								 WHERE CATEGORY_ID='$Cat_ID'";
								
				$ClosingDate =  $this->MysqlHamisDB->GetRow($NotificationSQL);
				if(strlen($ClosingDate['CLOSING_DATE'])) {
					  $ClosingDateTPS = $this->ConvertMysqldate($ClosingDate['CLOSING_DATE'])  ;
					  $StartingDateTPS =  $this->ConvertMysqldate($ClosingDate['START_DATE'])  ;
					  $Today =    $this->ConvertMysqldate(date('Y-m-d') )  ;
					 // $Today  = $Today  + (60*60*7);
					if($Today > $ClosingDateTPS){
						return 'CLOSED' ;		
					}elseif($Today <  $StartingDateTPS){
						 $DaysRemaining =   $this->conversecondtoDays($StartingDateTPS - $Today) ;
						 return array("STATUS"=>'PENDING',"START_DATE"=>$ClosingDate['START_DATE'],"CLOSING_DATE"=>$ClosingDate['CLOSING_DATE'],"DAYS_REMAINING"=>$DaysRemaining) ;  
					 }else{
						 $DaysRemaining =   $this->conversecondtoDays($ClosingDateTPS - $Today) ;
						return array("STATUS"=>'OPEN',"CLOSING_DATE"=>$ClosingDate['CLOSING_DATE'],"DAYS_REMAINING"=>$DaysRemaining) ;
					}
				}else{
					return 'NO SET DATE';
				}
   }
   
  
   public function ProcessModuleIIRoomApplication($RegistrationNo,$AcademicYear,$UpdateStatus=FALSE){
	    $ApplicationResponce = '';
	    $todayis = date("Y-m-d");  
	    $nowtime = date("H:i:s"); 
	    $vlevel =trim(CCGetParam('vlevel'));
		
		$vregno =trim(CCGetParam('vregno'));
		$vmobile =trim(CCGetParam('vmobile'));	
		$vemail =trim(CCGetParam('vemail'));
		if(strlen($vemail))
			if($this->ValidateEmail($vemail)===FALSE)
			   $this->RoomApplicationError['vemail'] = "<span style='color:red'>Enter a valid Email of leave the Field Blank if the Applicant has not provided a valid Email</span>";
		$vpostcode =trim(CCGetParam('vpostcode'));
		if(!strlen($vpostcode))
		   $this->RoomApplicationError['vpostcode']  = " <span style='color:red'>The Postal Code of Can't be Empty</span> " ; 	
		$vpostaddress =trim(CCGetParam('vpostaddress'));
		if(!strlen($vpostaddress))
		  $this->RoomApplicationError['vpostaddress']  = " <span style='color:red'> The Postal Address of Can't be Empty </span>" ; 
		$vstatus ='APPLIED';
		
		$vcategoryId = CCGetParam('vcategoryId');
		$vgender =trim(CCGetParam('vgender'));
		if(!strlen($vgender))
			$this->RoomApplicationError['vgender']  = " <span style='color:red'>The Applicant Gender Can't be Empty </span>" ; 
		$vapplicantyr=$vregno.'_'.$AcademicYear;
		$ApplicationPostion = rand(1,10000000);
		$synchronizationflag=1;
		$Apptime='';
	
		//Get Applicant session dates
		$StartDate = CCGetParam("StartDate");	
		if(!strlen($StartDate))
			$this->RoomApplicationError['StartDate']  = " <span style='color:red'>The Start Date Can't be Empty </span>" ; 
		$EndDate = CCGetParam("EndDate");	
		if(!strlen($EndDate))
			$this->RoomApplicationError['EndDate']  = " <span style='color:red'>The End Date Can't be Empty </span" ; 
		
		//Get Applicant SMU choices
		$vsmuchoiceid = CCGetParam('vsmuchoiceid');	
		$vallsmu = CCGetParam("PickSmus") ;
		if(!strlen($vallsmu))
			$this->RoomApplicationError['vallsmu']  = " <span style='color:red'> The Location Choices Can't be Empty </span>" ; 
		$vpayment=  CCGetParam('vpayment');
		if(!strlen($vpayment))
			$this->RoomApplicationError['vpayment']  = " <span style='color:red'> The How to Pay Can't be Empty </span>" ; 
		$DegreeDuration   = $this->CCGetSession("DegreeDuration");
        
	    $FormFillComplete = TRUE;
	    /*if(is_numeric($vlevel) && ($vlevel > $DegreeDuration)) {
            $FormFillComplete = FALSE; 
		    $this->RoomApplicationError['AppLevelofStudy']  = "<span style='color:red'> The Nominal Roll Record indicate that you have finished your Degree Programme</span>"; 
	    }*/
	    //Check Duplicate Application 
        $ApplicantYear =  $RegistrationNo . "_" . $AcademicYear ;         
        
        $AlreadyApplied =  $this->MysqlHamisDB->GetOne("SELECT registration_number 
                                                        FROM hamis.room_applicant 
                                                        WHERE Applicant_year = '$ApplicantYear'");
        
	   if($this->CheckifErrors($this->RoomApplicationError) === FALSE){	                
            
            $PostMobileNo  = $this->MysqlHamisDB->Quote($PostMobileNo); 
            $PostEmail   = $this->MysqlHamisDB->Quote($PostEmail ); 
            $PostalCodeFld  = $this->MysqlHamisDB->Quote($PostalCodeFld); 
            $PostalAddressFld =  $this->MysqlHamisDB->Quote($PostalAddressFld);															
		    if(!strlen($AlreadyApplied)){ 
			    $this->MysqlHamisDB->StartTrans();
                $ApplicationPostion = rand(1,1000000); 
				$ApplicantID = $this->processRoomApplicant($vregno,$AcademicYear,$vlevel,$vmobile,$vemail,$vpostcode,$vpostaddress,$ApplicationPostion,$vgender,$vcategoryId);
				if(strlen($ApplicantID))
				{
					$ApplicantsessionResponce = $this->ApplicantOtherDetails($ApplicantID,$StartDate,$EndDate,$vpayment);
					$Applicantchoice1Responce = $this->ApplicantSMUchoices($ApplicantID,$vsmuchoiceid,$vsmucode,$vchoiceno);
				}
		      if($ApplicantsessionResponce && $Applicantchoice1Responce){
				    $ApplicationResponce .= "<span style=\"color:red\">Your Room Application has been received. You will be notitied by email: $PostEmail  if you have been succesfull alloted a room or not.</span>";
		      }else
				    $ApplicationResponce .= "<span style=\"color:red\">There was an error while trying to accept your room application. Kindly refill the form and be sure to fill all the provided fields.<span>";
					     
		      $this->MysqlHamisDB->CompleteTrans();	
			}elseif($UpdateStatus===TRUE){
			 //Update Records
				 if( $ApplicantID){
				  $UpdateSQL = "UPDATE hamis.room_applicant SET mobile_number=$PostMobileNo ,email=$PostEmail,postal_code=$PostalCodeFld,postal_address=$PostalAddressFld,room_type_preference='$RoomTypePreference', synchronization_flag=1
								WHERE  applicant_id = $ApplicantID "  ;
				  $RoomApplicationResult = $this->MysqlHamisDB->Execute($UpdateSQL);
					 if($RoomApplicationResult){
							$ApplicationResponce .= "<span style=\"color:red\">Your Room Application updates have been effected. You will be notitied by email: $PostEmail  if you have been succesfull alloted a room or not.</span>";
					  }else
							$ApplicationResponce .= "<span style=\"color:red\">There was an error while trying to accept your room application. Kindly refill the form and be sure to fill all the provided fields.<span>";
				}else{
					$ApplicationResponce .= "<span style=\"color:red\">There was an error while trying to update your room application. Kindly refill the form and be sure to fill all the provided fields.<span>"; 
				}
			}else{
				 $this->StudentApplicationStatus = TRUE ;
				 $ApplicationResponce .= "<span style=\"color:red\">You have already applied for a room, You can only apply once. in the $AcademicYear academic Year</span>";
				 
			}	
      }else{
           $this->UpdateFormDisplayed = TRUE;
           return $this->UpdateModIIRoomApplicationForm ($RegistrationNo,$AcademicYear,$ApplicantID,$ApplicationStatus);
      } 
	    return  $ApplicationResponce;			
    }
   
   
   public function processRoomApplicant($RegistrationNo,$AcademicYear,$AppLevelofStudy,$PostMobileNo,$PostEmail,$PostalCode,$PostalAddress, $ApplicationPostion,$Gender,$vcategoryId){
	global $MysqlHamisDB;
	
	//Check Duplicate Application
	 
	$ApplicantID = 0; 
	
	$ApplicantYear = $RegistrationNo . '_' . $AcademicYear ; 
	
	$CheckDuplicateSQL = "SELECT applicant_id 
						  FROM hamis.room_applicant 
						  WHERE Applicant_year='$ApplicantYear' ";
													
	$ApplicantExist = $this->MysqlHamisDB->GetOne($CheckDuplicateSQL);
													

														
	if(!strlen($ApplicantExist)){
	
		$PostEmail = $this->MysqlHamisDB->Quote($PostEmail);
		$PostalAddress = $this->MysqlHamisDB->Quote($PostalAddress);
		$PostalCode = $this->MysqlHamisDB->Quote($PostalCode);
		
		$ApplicantUpdateSQL ="INSERT INTO hamis.room_applicant(registration_number, academic_year, level_of_study, mobile_number, email, postal_code, postal_address, date_applied, time_applied, room_application_status, room_type_preference,application_position,Applicant_year,gender,synchronization_flag,category_id) 
					 VALUES ('$RegistrationNo','$AcademicYear','$AppLevelofStudy','$PostMobileNo',$PostEmail,$PostalCode,$PostalAddress, now() ,now(), 'APPLIED', '',$ApplicationPostion,'$ApplicantYear','$Gender',1,$vcategoryId)";
		 $RoomApplicationResult = $this->MysqlHamisDB->Execute($ApplicantUpdateSQL);
		 if($RoomApplicationResult){
			$ApplicationResponce = TRUE;
			$ApplicantID =  $this->MysqlHamisDB->GetOne("SELECT applicant_id 
													FROM hamis.room_applicant 
													WHERE  Applicant_year = '$ApplicantYear'");
		} 		
	}else{
	  $ApplicantID = $this->ApplicantExist ;
		$PostEmail = $this->MysqlHamisDB->Quote($PostEmail);
		$PostalAddress = $this->MysqlHamisDB->Quote($PostalAddress);
		$PostalCode = $this->MysqlHamisDB->Quote($PostalCode);
			$ApplicantUpdateSQL = "UPDATE hamis.room_applicant set level_of_study=$AppLevelofStudy, mobile_number='$PostMobileNo', email=$PostEmail, 
						postal_code=$PostalCode, postal_address=$PostalAddress,application_position=$ApplicationPostion,gender='$Gender'
						WHERE Applicant_year ='$ApplicantYear'";	
			$RoomApplicationResult = $this->MysqlHamisDB->Execute($ApplicantUpdateSQL);   						
	}
	
	//Synchronise the Same Data with the Mysql Data			
	return  $ApplicantID;
			
}
   
   public function  ApplicantSMUchoices($ApplicantID,$vsmuchoiceid,$vsmucode,$vchoiceno){
		global $MysqlHamisDB , $db; 
		$PickSmus= CCGetParam("PickSmus");
		$smuchoice = explode(',', $PickSmus);
		$smucount=0;
		//print_r($smuchoice);
	   while($smucount<count($smuchoice))
	   {
		   $choiceno=$smucount+1;
		  
		   $CheckExitence = $this->MysqlHamisDB->GetOne("select applicant_id from hamis.smu_choices where  applicant_id = '$ApplicantID' and smu_code='$smuchoice[$smucount]'");
			if(!$CheckExitence){ // Add the applicant Details
				$ApplicantchoiceMYSQL = "insert into  hamis.smu_choices(smu_code,choice_number,applicant_id)
										values('$smuchoice[$smucount]','$choiceno',$ApplicantID)";
				$MysqlResponce =$this->MysqlHamisDB->Execute($ApplicantchoiceMYSQL); 
			}
			++$smucount;	
		}	
		
		if($MysqlResponce === FALSE ){
			return FALSE ;		
		}else{
			return TRUE;
		} 
	}	
	
	public function  ApplicantOtherDetails($ApplicantID,$StartDate,$EndDate,$vpayment){
		
		$CheckExitence = $this->MysqlHamisDB->GetOne("select applicant_id from hamis.other_student_detail where  applicant_id = $ApplicantID");
		
		
		$vstartdate=$this->ConvertTimeStampToMysqlDate($this->ConvertOracledate($StartDate));
		$venddate=$this->ConvertTimeStampToMysqlDate($this->ConvertOracledate($EndDate));
				
		if(!$CheckExitence){ // Add the applicant Details
			$ApplicantDetailMYSQL = "insert into  hamis.other_student_detail(applicant_id,start_date,end_date,how_to_pay,approved)
									values($ApplicantID,'$vstartdate','$venddate','$vpayment','P')";
			$MysqlResponce =$this->MysqlHamisDB->Execute($ApplicantDetailMYSQL); 
			
		}else{
			$ApplicantDetailMYSQL = " update hamis.other_student_detail set start_date='$vstartdate',end_date='$venddate',how_to_pay='$vpayment' ,approved='$vapproved'
									  where applicant_id = $ApplicantID";
									 // print $ApplicantDetailMYSQL;
			$MysqlResponce =$this->MysqlHamisDB->Execute($ApplicantDetailMYSQL); 		  
		}
		
		if($MysqlResponce === FALSE){
			return FALSE ;		
		}else{
			return TRUE;
		} 
		
}

	
    public function ProcessContinuingRoomApplication($RegistrationNo,$AcademicYear,$UpdateStatus=FALSE){
	    $ApplicationResponce = '';
	    $todayis = date("Y-m-d");  
	    $nowtime = date("H:i:s"); 
	    $Gender =  $this->CCGetSession("gender");  
	    $Surname = $this->CCGetSession("surname");
	    $OtherNames = $this->CCGetSession("otherNames");
	    $FullNames = ucwords(strtolower($Surname." ".$OtherNames));
        $UonEmail = $this->CCGetSession("Email"); 
         
	    $LevelofStudy = $this->CCGetSession("LevelofStudy");
	    $DegreeDuration   = $this->CCGetSession("DegreeDuration");
	    $MobileNo    = $this->CCGetSession("primary_mobile"); 
	    $PostMobileNo = $this->CCGetParam("PostMobileNo") ? $this->CCGetParam("PostMobileNo"): $MobileNo;
	    $PostEmail = $this->CCGetParam("PostEmail")?$this->CCGetParam("PostEmail") : $UonEmail;
	    $PostalCodeFld = $this->CCGetParam("PostalCodeFld");
	    $PostalAddressFld = $this->CCGetParam("PostalAddressFld");
	    $RoomTypePreference = $this->CCGetParam("RoomTypePreference");
	    $AppLevelofStudy = $this->CCGetParam("AppLevelofStudy")?$this->CCGetParam("AppLevelofStudy") : $LevelofStudy ;
	    $PostalAddress =    $this->CCGetParam("PostalAddress");
	    $PostalCode = $this->CCGetParam("PostalCode"); 
	    $accept_terms = $this->CCGetParam("accept_terms"); 
	    $ApplicantID =   $this->CCGetParam("ApplicantID");
        
	    $FormFillComplete = TRUE;
	    if(is_numeric($LevelofStudy) && ($LevelofStudy > $DegreeDuration)) {
            $FormFillComplete = FALSE; 
		    $this->RoomApplicationError['AppLevelofStudy']  = "<span style='color:red'>The Nominal Roll Record indicate that you have finished your Degree Programme</span>"; 
	    }
	    //validate  Postal address field
	    if (!strlen($PostalAddressFld)) {
            $FormFillComplete = FALSE; 
		    $this->RoomApplicationError['PostalAddressFld']  = "<span style='color:red'>Please Enter Your Postal Address</span>";
	    }
	    //validate  Postal lebel of study field
	    if ($AppLevelofStudy =='') {
		    $FormFillComplete = FALSE;
		    $this->RoomApplicationError['AppLevelofStudy'] =  "<span style='color:red'>Please Enter Your Level of Study</span>";
	    }	
				    
	    //validate  Room type preference field
	    if ($RoomTypePreference =='') {
		    $FormFillComplete = FALSE;
		    $this->RoomApplicationError['RoomTypePreference'] =  "<span style='color:red'>Please Select Your Room Type Preference</span>";
	    }	
	    
	    //validate Mobile No field
	    if ($PostMobileNo =='') {
		    $FormFillComplete = FALSE;
		    $this->RoomApplicationError['PostMobileNo'] =  "<span style='color:red'>Please Enter Your Mobile Number</span>";
	    }	
        
        if(!strlen($accept_terms)){
		    $FormFillComplete = FALSE;
		    $this->RoomApplicationError['accept_terms'] =  "<span style='color:red'>You mUst accept the terms and condition or room application </span>";
	    }
        
        if(!strlen($PostEmail)){
             $FormFillComplete = FALSE;
             $this->RoomApplicationError['PostEmail'] =  "<span style='color:red'>You Must eneter an Email address through which you will be notified about your room application outcome </span>";
        }
	    //Check Duplicate Application 
        $ApplicantYear =  $RegistrationNo . "_" . $AcademicYear ;         
        
        $AlreadyApplied =  $this->MysqlHamisDB->GetOne("SELECT registration_number 
                                                        FROM hamis.room_applicant 
                                                        WHERE Applicant_year = '$ApplicantYear'");
                                                        
        
	    if ($FormFillComplete == TRUE) {	                
            
            $PostMobileNo  = $this->MysqlHamisDB->Quote($PostMobileNo); 
            $PostEmail   = $this->MysqlHamisDB->Quote($PostEmail ); 
            $PostalCodeFld  = $this->MysqlHamisDB->Quote($PostalCodeFld); 
            $PostalAddressFld =  $this->MysqlHamisDB->Quote($PostalAddressFld);															
		    if(!strlen($AlreadyApplied)){ 
			    $this->MysqlHamisDB->StartTrans();
                $ApplicationPostion = rand(1,1000000); 
			    
			    $SQLInsert ="INSERT INTO hamis.room_applicant(registration_number, academic_year, level_of_study, mobile_number, email, postal_code, postal_address, date_applied, time_applied, room_application_status, room_type_preference,application_position,Applicant_year,gender,synchronization_flag) 
											     VALUES ('$RegistrationNo','$AcademicYear','$AppLevelofStudy',$PostMobileNo,$PostEmail,$PostalCodeFld,$PostalAddressFld, now() ,now(), 'APPLIED', '$RoomTypePreference',$ApplicationPostion,'$ApplicantYear','$Gender',1)";
		     
			     $RoomApplicationResult = $this->MysqlHamisDB->Execute($SQLInsert);
			     
			     //Get Applicant ID
			    $ApplicantID  = $this->MysqlHamisDB->GetOne("SELECT applicant_id 
															    FROM hamis.room_applicant 
															    WHERE Applicant_year ='$ApplicantYear'");

					    
		      if($RoomApplicationResult){
				    $ApplicationResponce .= "<span style=\"color:red\">Your Room Application has been received. You will be notitied by email: $PostEmail  if you have been succesfull alloted a room or not.</span>";
		      }else
				    $ApplicationResponce .= "<span style=\"color:red\">There was an error while trying to accept your room application. Kindly refill the form and be sure to fill all the provided fields.<span>";
					     
		      $this->MysqlHamisDB->CompleteTrans();	
	    }elseif($UpdateStatus===TRUE){
         //Update Records
             if( $ApplicantID){
              $UpdateSQL = "UPDATE hamis.room_applicant SET mobile_number=$PostMobileNo ,email=$PostEmail,postal_code=$PostalCodeFld,postal_address=$PostalAddressFld,room_type_preference='$RoomTypePreference', synchronization_flag=1
                            WHERE  applicant_id = $ApplicantID "  ;
              $RoomApplicationResult = $this->MysqlHamisDB->Execute($UpdateSQL);
                 if($RoomApplicationResult){
                        $ApplicationResponce .= "<span style=\"color:red\">Your Room Application updates have been effected. You will be notitied by email: $PostEmail  if you have been succesfull alloted a room or not.</span>";
                  }else
                        $ApplicationResponce .= "<span style=\"color:red\">There was an error while trying to accept your room application. Kindly refill the form and be sure to fill all the provided fields.<span>";
            }else{
                $ApplicationResponce .= "<span style=\"color:red\">There was an error while trying to update your room application. Kindly refill the form and be sure to fill all the provided fields.<span>"; 
            }
        }else{
		     $this->StudentApplicationStatus = TRUE ;
		     $ApplicationResponce .= "<span style=\"color:red\">You have already applied for a room, You can only apply once. in the $AcademicYear academic Year</span>";
             
	    }	
      }else{
           $this->UpdateFormDisplayed = TRUE;
           return $this->UpdateRoomApplicationForm ($RegistrationNo,$AcademicYear,$ApplicantID,$ApplicationStatus);
      } 
	    return  $ApplicationResponce;			
    }
	
	public function ValidateEmail($email){
		$ValidEmail = FALSE ;
		if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) { 
		  $ValidEmail = TRUE; 
		} 
		return  $ValidEmail ;
	
	} 
	public function ProcessModIIRoomApplication($RegistrationNo,$AcademicYear,$UpdateStatus=FALSE){
	    $ApplicationResponce = '';
	    $todayis = date("Y-m-d");  
	    $nowtime = date("H:i:s"); 
	    $vlevel =trim(CCGetParam('vlevel'));
		if(!strlen($vlevel))
			  $this->RoomApplicationError['vlevel']  = " <span style='color:red'>The Level of Study Can't be Empty </span>" ;
		$vregno =trim(CCGetParam('vregno'));
		$vmobile =trim(CCGetParam('vmobile'));	
		$vemail =trim(CCGetParam('vemail'));
		if(strlen($vemail))
			if($this->ValidateEmail($vemail)===FALSE)
			   $this->RoomApplicationError['vemail'] = " <span style='color:red'>Enter a valid Email of leave the Field Blank if the Applicant has not provided a valid Email</span>";
		$vpostcode =trim(CCGetParam('vpostcode'));
		if(!strlen($vpostcode) )
		   $this->RoomApplicationError['vpostcode']  = " <span style='color:red'>The Postal Code of Can't be Empty </span>" ; 	
		$vpostaddress =trim(CCGetParam('vpostaddress'));
		if(!strlen($vpostaddress))
		  $this->RoomApplicationError['vpostaddress']  = " <span style='color:red'>The Postal Address of Can't be Empty </span>" ; 
		$vstatus ='APPLIED';
		
		$vcategoryId = CCGetParam('vcategoryId');
		$vgender =trim(CCGetParam('vgender'));
		if(!strlen($vgender))
		  $this->RoomApplicationError['vgender']  = " <span style='color:red'>The Gender Can't be Empty </span>" ; 
		$StartDate =trim(CCGetParam('StartDate'));
		if(!strlen($StartDate))
		  $this->RoomApplicationError['StartDate']  = " <span style='color:red'>The Start Date Can't be Empty </span>" ; 
		$EndDate =trim(CCGetParam('EndDate'));
		if(!strlen($EndDate))
		  $this->RoomApplicationError['EndDate']  = " <span style='color:red'>The End Date Can't be Empty </span>" ; 
		$vpayment =trim(CCGetParam('vpayment'));
		if(!strlen($vpayment))
		  $this->RoomApplicationError['vpayment']  = " <span style='color:red'>The How to Pay Can't be Empty </span>" ; 
		$ApplicantID = CCGetParam('ApplicantID');
		if(!strlen($vgender))
			$this->RoomApplicationError['vgender']  = " <span style='color:red'>The Applicant Gender Can't be Empty </span>" ; 
		$PickSmus= CCGetParam("PickSmus");
		if(!strlen($PickSmus))
			$this->RoomApplicationError['PickSmus']  = " <span style='color:red'>The Location Choices Can't be Empty </span>" ; 
		$vapplicantyr=$vregno.'_'.$AcademicYear;
		$ApplicationPostion = rand(1,10000000);
		$synchronizationflag=1;
		$Apptime='';
        
	    
	    //Check Duplicate Application 
        $ApplicantYear =  $RegistrationNo . "_" . $AcademicYear ;         
        
        $AlreadyApplied =  $this->MysqlHamisDB->GetOne("SELECT registration_number 
                                                        FROM hamis.room_applicant 
                                                        WHERE Applicant_year = '$ApplicantYear'");
														
	  if($this->CheckifErrors($this->RoomApplicationError) === FALSE){
         //Update Records
             if( $ApplicantID){
			  $this->MysqlHamisDB->StartTrans();
              $UpdateSQL = "UPDATE hamis.room_applicant SET mobile_number='$vmobile' ,email='$vemail',postal_code='$vpostcode',postal_address='$vpostaddress', synchronization_flag=1
                            WHERE  applicant_id = $ApplicantID ";
              $RoomApplicationResult = $this->MysqlHamisDB->Execute($UpdateSQL);
			  if($RoomApplicationResult)
			  {
			  		$vstartdate=$this->ConvertTimeStampToMysqlDate($this->ConvertOracledate($StartDate));
					$venddate=$this->ConvertTimeStampToMysqlDate($this->ConvertOracledate($EndDate));
			  		$UpdateSQL = "UPDATE hamis.other_student_detail SET start_date='$vstartdate' ,end_date='$venddate',how_to_pay ='$vpayment'
                            WHERE  applicant_id = $ApplicantID ";
             		$RoomApplicationResult = $this->MysqlHamisDB->Execute($UpdateSQL);
			  }
			  if($RoomApplicationResult)
			  {
			  		
					$smuchoice = explode(',', $PickSmus);
					$smucount=0;
					//print_r($smuchoice);
					$deleteSQL =  $this->MysqlHamisDB->Execute("delete from hamis.smu_choices where applicant_id = $ApplicantID");
				   while($smucount<count($smuchoice))
				   {
					   	$choiceno=$smucount+1;
						$ApplicantchoiceMYSQL = "insert into  hamis.smu_choices(smu_code,choice_number,applicant_id)
										values('$smuchoice[$smucount]','$choiceno',$ApplicantID)";
						$RoomApplicationResult = $this->MysqlHamisDB->Execute($ApplicantchoiceMYSQL);						  
						++$smucount;	
					}
             		
			  }
                 if($RoomApplicationResult){
                        $ApplicationResponce .= "<span style=\"color:red\">Your Room Application updates have been effected. You will be notitied by email: $vemail  if you have been succesfull alloted a room or not.</span>";
                  }else
                        $ApplicationResponce .= "<span style=\"color:red\">There was an error while trying to accept your room application. Kindly refill the form and be sure to fill all the provided fields.<span>";
            }else{
                $ApplicationResponce .= "<span style=\"color:red\">There was an error while trying to update your room application. Kindly refill the form and be sure to fill all the provided fields.<span>"; 
            }
      }else{
           $this->UpdateFormDisplayed = TRUE;
           return $this->UpdateModIIRoomApplicationForm ($RegistrationNo,$AcademicYear,$ApplicantID,$ApplicationStatus);
      } 
	    return  $ApplicationResponce;			
    }
	
    public function ProcessRoomApplication($RegistrationNo,$AcademicYear){

	    $ApplicationResponce = '';
	    $todayis = date("Y-m-d");  
	    $nowtime = date("H:i:s"); 
	    $Gender =  $this->CCGetSession("gender");  
	    $Surname = $this->CCGetSession("surname");
	    $OtherNames = $this->CCGetSession("otherNames");
	    $FullNames = ucwords(strtolower($Surname." ".$OtherNames));
	    $LevelofStudy = $this->CCGetSession("LevelofStudy");
	    $DegreeDuration   = $this->CCGetSession("DegreeDuration");
	    $MobileNo    = $this->CCGetSession("primary_mobile"); 
	    $PostMobileNo = $this->CCGetParam("PostMobileNo") ? $this->CCGetParam("PostMobileNo"): $MobileNo;
	    $PostEmail = $this->CCGetParam("PostEmail");
	    $PostalCodeFld = $this->CCGetParam("PostalCodeFld");
	    $PostalAddressFld = $this->CCGetParam("PostalAddressFld");
	    $RoomTypePreference = $this->CCGetParam("RoomTypePreference");
	    $AppLevelofStudy = $this->CCGetParam("AppLevelofStudy")?$this->CCGetParam("AppLevelofStudy") : $LevelofStudy ;
	    $PostalAddress =    $this->CCGetParam("PostalAddress");
	    $PostalCode = $this->CCGetParam("PostalCode"); 
	    $OphanStatus = $this->CCGetParam("OphanStatus"); 
	    $SingleFamilyStatus = $this->CCGetParam("SingleFamilyStatus");  
	    $HelbLoanAmount   =   $this->CCGetParam("HelbLoanAmount");  
	    $AmountCanPay   =     $this->CCGetParam("AmountCanPay"); 
	    $RequireBursary   =   $this->CCGetParam("RequireBursary");	
	    $SponsorType =        $this->CCGetParam("SponsorType");  
	    $SponsorNames =       $this->CCGetParam("SponsorNames"); 
	    $SponsorAddress =     $this->CCGetParam("SponsorAddress");	
	    $SponsorTelephone =   $this->CCGetParam("SponsorTelephone");	
	    $ImpairementStatus =  $this->CCGetParam("ImpairementStatus");	
      
	    $Fathersurname  =   $this->CCGetParam("Fathersurname");
	    $FatherotherNames  =   $this->CCGetParam("FatherotherNames");
	    $FatherID  =   $this->CCGetParam("FatherID"); 
	    $FatherPIN  =   $this->CCGetParam("FatherPIN"); 
	    $FatherHighestEducationLevel  =   $this->CCGetParam("FatherotherNames");    
	    $FatherEmployedStatus =     $this->CCGetParam("FatherEmployedStatus"); 
	    $FatherOccupation =    $this->CCGetParam("FatherOccupation"); 
	    $FatherEmployerBusiness  =    $this->CCGetParam("FatherEmployerBusiness"); 
	    $FatherGrossMonthlySalary =   $this->CCGetParam("FatherGrossMonthlySalary");	
	    $FatherBusinessEarnings  =  $this->CCGetParam("FatherBusinessEarnings");
	    $FatherFarmingEarnings 	=  $this->CCGetParam("FatherFarmingEarnings"); 
	    $FatherFarmingEarnings 	=  $this->CCGetParam("FatherFarmingEarnings");
	    $FatherMonthlyPension =   $this->CCGetParam("FatherMonthlyPension");  
	     
	    $Mothersurname  =   $this->CCGetParam("Mothersurname"); 
	    $MotherotherNames  =   $this->CCGetParam("MotherotherNames"); 
	    $MotherID  =   $this->CCGetParam("MotherID"); 
	    $MotherPIN  =   $this->CCGetParam("MotherPIN"); 
	    $MotherHighestEducationLevel =   $this->CCGetParam("MotherHighestEducationLevel"); 
	    $MotherEmployedStatus =     $this->CCGetParam("MotherEmployedStatus"); 
	    $MotherOccupation =    $this->CCGetParam("MotherOccupation"); 
	    $MotherEmployerBusiness  =    $this->CCGetParam("MotherEmployerBusiness");  
	    $MotherGrossMonthlySalary =   $this->CCGetParam("MotherGrossMonthlySalary");	
	    $MotherBusinessEarnings  =  $this->CCGetParam("MotherBusinessEarnings");		
	    $MotherFarmingEarnings    =  $this->CCGetParam("MotherFarmingEarnings");	
	    $MotherMonthlyPension    =  $this->CCGetParam("MotherMonthlyPension");	


	      $FormType  =  $this->CCGetParam("FormType");	
	    /******************************************************************** Begin Form Validation *********************************************************/	
		    $FormFillComplete = TRUE;
		    if(is_numeric($LevelofStudy) && ($LevelofStudy > $DegreeDuration)) {
			    $this->RoomApplicationError['AppLevelofStudy']  = "<span style='color:red'>The Nominal Roll Record indicate that you have finished your Degree Programme</span>"; 
		    }
		    //validate  Postal address field
		    if ($PostalAddressFld =='') {
			    $this->RoomApplicationError['PostalAddressFld']  = "<span style='color:red'>Please Enter Your Postal Address</span>";
		    }	
	      else  {
			    $PostalAddressFldErr ='';
		    }
		    //validate  Postal lebel of study field
		    if ($AppLevelofStudy =='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['AppLevelofStudy'] =  "<span style='color:red'>Please Enter Your Level of Study</span>";
		    }	
					    
		    //validate  Room type preference field
		    if ($RoomTypePreference =='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['RoomTypePreference'] =  "<span style='color:red'>Please Select Your Room Type Preference</span>";
		    }	
		    
		    //validate Mobile No field
		    if ($PostMobileNo =='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['PostMobileNo'] =  "<span style='color:red'>Please Enter Your Mobile Number</span>";
		    }	
		    
		    //validate Orphan Status  field
		    if ($OphanStatus =='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['OphanStatus'] = "<span style='color:red'>Please provide the information of you are an orphan or not</span>";
		    }	
		    
		    //validate Single parenthood status  field
		    if ($SingleFamilyStatus =='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['SingleFamilyStatus'] =   "<span style='color:red'>Please provide the information of you are a single parent or not</span>";
		    }	
		    
		    //validate Helb Loan Amount
		    if ($HelbLoanAmount  =='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['HelbLoanAmount'] =  "<span style='color:red'>Please select the HelB Loan amount you are applying for</span>";
		    }	
	    
		    //validate Amount Can Pay
		    if ($AmountCanPay  =='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['AmountCanPay'] =  "<span style='color:red'>Please provide the information of how much fees amount you can raise?</span>";
		    }	
	    
		    //validate Bursary Requirement
		    if ($RequireBursary   =='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['RequireBursary'] =  "<span style='color:red'>Please provide the information if require bursary or not?</span>";
		    }
		    
	    //validate Father other Names information
		    $HASFATHER = FALSE;
		    if ((strlen($Fathersurname)) && (!strlen($FatherotherNames))) {
		      $HASFATHER = TRUE;
		      $FormFillComplete = FALSE;
		      $this->RoomApplicationError['FatherotherNames'] =   "<span style='color:red'>Please Enter Your Father Other Names</span>";
		    }
	    //validate Father Surname information
		    if ((strlen($FatherotherNames)) && (!strlen($Fathersurname))) {
		      $HASFATHER = TRUE;
		      $FormFillComplete = FALSE;
		      $this->RoomApplicationError['Fathersurname'] = "<span style='color:red'>Please Enter Your Father's Surname/span>";
		    }
		    
	    if(strlen($Fathersurname) && strlen($FatherotherNames)){
		    $HASFATHER = TRUE;
	    }	
	    //if has Father validate other important details about Father	
	     if ($HASFATHER == TRUE) {
	       // validate Father employment status
		    if ($FatherEmployedStatus=='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['FatherEmployedStatus'] =   "<span style='color:red'>Please provide your Fathers employment status</span>";
		    }
		    
	       // validate Father occupation 
		    if ($FatherOccupation=='') {
			    $FormFillComplete = FALSE;
			    $ErrorMsg['FatherOccupation'] =  "<span style='color:red'>Please select your Fathers Occupation</span>";
		    }
	     
	       // validate Father employer/business
		    if ($FatherEmployerBusiness=='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['FatherEmployerBusiness'] =  "<span style='color:red'>Please Enter the  name of your Father's Employer or Business</span>";
		    }
		    
		    //validate Father's income 
		     if (($FatherGrossMonthlySalary =='') && ($FatherBusinessEarnings  =='') && ($FatherFarmingEarnings  =='') && ($FatherMonthlyPension  ==''))
		      {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['Earning'] = "<span style='color:red'>Please Provide atleast fill either your Fathers Gross Monthy Salary, Business earning, Farm earnings or Pension to indicate your Father's Income</span>";
		     }	
	    } 
	    
	    //validate Mother other Names information
		    $HASMother = FALSE;
		    if ((strlen($Mothersurname)) && (!strlen($MotherotherNames))) {
		      $HASMother = TRUE;
		      $FormFillComplete = FALSE;
		      $this->RoomApplicationError['Mothersurname'] = "<span style='color:red'>Please Enter Your Mother Other Names</span>";
		    }
	    //validate Mother Surname information
		    if (($MotherotherNames  !='') && ($Mothersurname  =='')) {
		      $HASMother = TRUE;
		      $FormFillComplete = FALSE;
		      $this->RoomApplicationError['Mothersurname'] = "<span style='color:red'>Please Enter Your Mother's Surname/span>";
		    }
		    
		    if(strlen($Mothersurname) && strlen($MotherotherNames)){
			    $HASMother = TRUE;
		    }
	    
	    //if has Mother validate other important details about Mother	
	     if ($HASMother == TRUE) {
	       // validate Mother employment status
		    if ($MotherEmployedStatus=='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['MotherEmployedStatus'] = "<span style='color:red'>Please provide your Mothers employment status</span>";
		    }
		    
	       // validate Mother occupation 
		    if ($MotherOccupation=='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['MotherOccupation'] = "<span style='color:red'>Please select your Mothers Occupation</span>";
		    }
	     
	       // validate Mother employer/business
		    if ($MotherEmployerBusiness=='') {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['MotherEmployerBusiness'] = "<span style='color:red'>Please Enter the  name of your Mother's Employer or Business</span>";
		    }
		    
		    //validate Mother's income 
		     if (($MotherGrossMonthlySalary =='') && ($MotherBusinessEarnings  =='') && ($MotherFarmingEarnings  =='') && ($MotherMonthlyPension  ==''))
		      {
			    $FormFillComplete = FALSE;
			    $this->RoomApplicationError['MotherGrossMonthlySalary'] = "<span style='color:red'>Please Provide atleast either your Mothers Gross Monthy Salary, Business earning, Farm earnings or Pension to indicate your Mother's Income</span>";
		     }	
	    } 	
	    /*************************************************** End Form Validation *********************************************************************************/	

	    //Check Duplicate Application 
	    $ApplicantYear =  $RegistrationNo . "_" . $AcademicYear ; 
	    
	    
	    $AlreadyApplied =  $this->MysqlHamisDB->GetOne("SELECT registration_number 
													    FROM hamis.room_applicant 
													    WHERE Applicant_year = '$ApplicantYear'");
	    
	    if ($FormFillComplete == TRUE) {
														    
		    if(!strlen($AlreadyApplied)){ 
			    $this->MysqlHamisDB->StartTrans();
			    $ApplicationPostion = rand(1,1000000);
			    $PostMobileNo  = $this->MysqlHamisDB->Quote($PostMobileNo); 
			    $PostEmail  = $this->MysqlHamisDB->Quote($PostEmail); 
			    $PostalCodeFld  = $this->MysqlHamisDB->Quote($PostalCodeFld); 
			    $PostalAddressFld =  $this->MysqlHamisDB->Quote($PostalAddressFld); 
			    
			    $SQLInsert ="INSERT INTO hamis.room_applicant(registration_number, academic_year, level_of_study, mobile_number, email, postal_code, postal_address, date_applied, time_applied, room_application_status, room_type_preference,application_position,Applicant_year,gender,synchronization_flag) 
											     VALUES ('$RegistrationNo','$AcademicYear','$AppLevelofStudy',$PostMobileNo,$PostEmail,$PostalCodeFld,$PostalAddressFld, now() ,now(), 'APPLIED', '$RoomTypePreference',$ApplicationPostion,'$ApplicantYear','$Gender',1)";
		     
			     $RoomApplicationResult = $this->MysqlHamisDB->Execute($SQLInsert);
			     
		     //Get Applicant ID
		    $ApplicantID  = $this->MysqlHamisDB->GetOne("SELECT applicant_id 
														    FROM hamis.room_applicant 
														    WHERE Applicant_year ='$ApplicantYear'");

		    if($FormType =='FIRSTYEAR'){															
			     //Insert Applicant Single parenthood and If Orphan Detail					 
			    $SQLInsertApplicantDetail ="INSERT INTO hamis.applicant_detail(applicant_id, single_parent, orphan,medical_status) 
													     VALUES ('$ApplicantID','$SingleFamilyStatus','$OphanStatus','$ImpairementStatus')";
			    $RoomApplicantDetailResult = $this->MysqlHamisDB->Execute($SQLInsertApplicantDetail);
															    
			    //Insert Applicant Helb Detail
			    $SQLInsertApplicantHelB ="INSERT INTO hamis.applicant_helb(applicant_id, loan_amount, amount_canpay,require_bursary) 
													     VALUES ('$ApplicantID','$HelbLoanAmount','$AmountCanPay','$RequireBursary')";
													     
			    $RoomApplicantHelbResult = $this->MysqlHamisDB->Execute($SQLInsertApplicantHelB);
							    
						     
			    if ($HASFATHER === TRUE)  {
			     //Insert Father Parent Details
				     $Fathersurname  = $this->MysqlHamisDB->Quote($Fathersurname);
				     $FatherotherNames =  $this->MysqlHamisDB->Quote($FatherotherNames);
				     $FatherID =  $this->MysqlHamisDB->Quote($FatherID); 
				     $FatherPIN =  $this->MysqlHamisDB->Quote($FatherPIN);
				     $FatherEmployerBusiness =  $this->MysqlHamisDB->Quote($FatherEmployerBusiness); 
				     
				    $SQLInsertApplicantFather ="INSERT INTO hamis.applicant_parent(applicant_id, surname, other_names,idno, pin_no, education_level, employed, gender, occupation_id, name_employer, monthly_gross, business_annual, farming_annual, monthly_pension, applicant_parent) 
														     VALUES ('$ApplicantID',$Fathersurname,$FatherotherNames,$FatherID,$FatherPIN,'$FatherHighestEducationLevel', '$FatherEmployedStatus', 'M','$FatherOccupation',$FatherEmployerBusiness,'$FatherGrossMonthlySalary','$FatherBusinessEarnings','$FatherFarmingEarnings','$FatherMonthlyPension','Father')";
														     
				    $RoomApplicantFatherResult = $this->MysqlHamisDB->Execute($SQLInsertApplicantFather);
					    
			     }	
			     
			     if ($HASMother === TRUE) {
				       //Insert Mother Parent Details
					     $Mothersurname  = $this->MysqlHamisDB->Quote($Mothersurname);
					     $MotherotherNames =  $this->MysqlHamisDB->Quote($MotherotherNames);
					     $MotherID =  $this->MysqlHamisDB->Quote($MotherID); 
					     $MotherPIN =  $this->MysqlHamisDB->Quote($MotherPIN);
					     $MotherEmployerBusiness =  $this->MysqlHamisDB->Quote($MotherEmployerBusiness); 
					    $SQLInsertApplicantMother ="INSERT INTO hamis.applicant_parent(applicant_id, surname, other_names,idno, pin_no, education_level, employed, gender, occupation_id, name_employer, monthly_gross, business_annual, farming_annual, monthly_pension, applicant_parent) 
															     VALUES ('$ApplicantID',$Mothersurname,$MotherotherNames,$MotherID,$MotherPIN,'$MotherHighestEducationLevel', '$MotherEmployedStatus', 'F','$MotherOccupation',$MotherEmployerBusiness,'$MotherGrossMonthlySalary','$MotherBusinessEarnings','$MotherFarmingEarnings','$MotherMonthlyPension','Mother')";
					    $RoomApplicantMotherResult = $this->MysqlHamisDB->Execute($SQLInsertApplicantMother);
			     }
				    
			     if (strlen($SponsorType)) {
					    $SponsorNames  = $this->MysqlHamisDB->Quote($SponsorNames);
					    $SponsorAddress  = $this->MysqlHamisDB->Quote($SponsorAddress);   
					    $SQLInsertApplicantSponsorType ="INSERT INTO hamis.applicant_sponsor(applicant_id, sponsor_id,sponsor_name,telephone_no,post_code) 
															     VALUES ('$ApplicantID','$SponsorType',$SponsorName','$SponsorTelephone', $SponsorAddress)";
					    $RoomApplicantSponsorResult = $this->MysqlHamisDB->Execute($SQLInsertApplicantSponsorType);
				    
			     } 
		    } // Details for the First year Applicants
					    
		      if($RoomApplicationResult){
					    $ApplicationResponce .= "<span style=\"color:red\">Your Room Application has been received. You will be notitied by email: $PostEmail  if you have been succesfull alloted a room or not.</span>";
		      }else
					     $ApplicationResponce .= "<span style=\"color:red\">There was an error while trying to accept your room application. Kindly refill the form and be sure to fill all the provided fields.<span>";
					     
		      $this->MysqlHamisDB->CompleteTrans();	
	    }else{
				      $this->StudentApplicationStatus = TRUE ;
				     $ApplicationResponce .= "<span style=\"color:red\">You have already applied for a room, You can only apply once. in the $AcademicYear academic Year</span>";
	    }
		    
       
      } 
	    return  $ApplicationResponce;
	    
    }

    public function LevelofStudyList($objectName, $DefaultLevel){
	    $DegreeDuration   = $this->CCGetSession("DegreeDuration");
	    $OptionsDisplay = '<select name =' . $objectName . '>' ;
	    $ValueSelected = FALSE;
	    
	    for($i=1; $i<=$DegreeDuration; $i++){
		    if($i == $DefaultLevel){
			    $SelectOption = ' SELECTED '    ;
			    $ValueSelected = TRUE ; 
		    } else
		       $SelectOption = '  '    ;   	
		       
	       $OptionsDisplay .= 	 '<option value="'. $i . '"' . $SelectOption .' >'. $i .'</option>'  ;	
	    }
	    
	    if($ValueSelected === FALSE)
	      $OptionsDisplay .= 	 '<option value="" selected>Select Level of Study</option>'  ;	
		    
	    $OptionsDisplay .= '</select>' ; 
	    
	    return    $OptionsDisplay;
    }

    public function RoomApplicationForm ($RegistrationNo,$mobileno,$Gender,$DegreeCode,$LevelofStudy,$UonEmail, $AcademicYear,$ApplicationStatus) {

    $Surname = $this->CCGetSession("surname");

    $OtherNames = $this->CCGetSession("otherNames");
    $FullNames = ucwords(strtolower($Surname." ".$OtherNames));
    $LevelofStudy = $this->CCGetSession("LevelofStudy");
    $MobileNo    = $this->CCGetSession("primary_mobile");
    $UonEmail = $this->CCGetSession("Email");
	    
    $ApplicationForm  = '' ;
    $PostMobileNo = $this->CCGetParam("PostMobileNo") ? $this->CCGetParam("PostMobileNo") : $MobileNo ;
    $PostEmail = $this->CCGetParam("PostEmail") ? $this->CCGetParam("PostEmail") : $UonEmail;
    $PostalCodeFld = $this->CCGetParam("PostalCodeFld");
    $PostalAddressFld = $this->CCGetParam("PostalAddressFld");
    $RoomTypePreference = $this->CCGetParam("RoomTypePreference");
    $AppLevelofStudy = $this->CCGetParam("AppLevelofStudy") ? $this->CCGetParam("AppLevelofStudy") : $LevelofStudy ;


    if (!strlen($PostEmail))
	    $emailtextbox = '<input type="text" id="PostEmail" name="PostEmail" size="40" value="'.$PostEmail.'">';
    else
	    $emailtextbox = '<input type="text" id="PostEmail" name="PostEmail" size="40" value="'.$UonEmail.'" readonly="true" />';
    if (!strlen($MobileNo))
	    $mobiletextbox = '<input type="text" id="PostMobileNo" name="PostMobileNo" value="'.$PostMobileNo.'">';
    else
	    $mobiletextbox = '<input type="text" id="PostMobileNo" name="PostMobileNo" value="'.$MobileNo.'" readonly="true">';


    $ApplicationForm .= '<form  action="' .$_SERVER['PHP_SELF']. '" method="post" enctype="multipart/form-data" name="ApplyForRoom"> 
						    <table cellpadding="3" cellspacing="0" border="1">
						    <caption>
							    <h2 align="center">Room Application For: ' . $AcademicYear .'</h2>
							    <h3>The Room Application Deadline is : ' .$ApplicationStatus['CLOSING_DATE'].', There are '. $ApplicationStatus['DAYS_REMAINING'] .' Days Remaining to the End of the Room Application Period</h3>
						    </caption>
						    <tr><td colspan="4"><strong>Personal Information</strong></td></tr>
						    <tr>
							    <td><strong>Full Names</strong></td>
							    <td>'. $FullNames . '</td>
						    </tr>
						    <tr>
							    <td><strong>Registration No</strong></td>
							    <td><input  id="regno" name="regno" type="hidden" value ="'.$RegistrationNo.'">' .$RegistrationNo . '</td>
						    </tr>
						    <tr>
							    <td><strong>Academic Year</strong></td>
							    <td><input  id="AcademicYear" name="AcademicYear" type="hidden" value ="'.$AcademicYear.'">'.$AcademicYear.'</td>
						    </tr>
						    <tr>
							    <td><strong><strong>Year of Study</strong></strong></td>
							    <td> <input type="hidden" name="AppLevelofStudy" value="' . $AppLevelofStudy .'"> Current Level of Study =>  ' . $AppLevelofStudy . ' ' . $this->RoomApplicationError['AppLevelofStudy'] . '</td>
						    </tr>
						    <tr>
							    <td><strong>Mobile No</strong></td>
							    <td>' . $mobiletextbox . ' '.$this->RoomApplicationError['PostMobileNo'].'</td>
						    </tr>
						    <tr>
							    <td><strong>Email</strong></td>
							    <td>' . $emailtextbox . ' '.$this->RoomApplicationError['PostEmail'].'</td>
						    </tr>
						    <tr>
							    <td><strong>Postal Code</strong></td>
							    <td><input id="PostCode" name="PostalCodeFld" type="text" value="'.$PostalCodeFld.'"> '.$this->RoomApplicationError['PostalCodeFld'].'</td>
						    </tr>
						    <tr>
							    <td><strong>Postal Address</strong></td>
							    <td><input id="PostalAddressFld" name="PostalAddressFld" type="text" value="'.$PostalAddressFld.'"/> '.$this->RoomApplicationError['PostalAddressFld'].' </td>
						    </tr>
						    <tr>
							    <td><strong>Room Preference</strong></td>
							    <td>'. $this->GetAvailableRoomTypesForStudent($DegreeCode,$Gender,$LevelofStudy) .'</td>
						    </tr>
							    <tr>
							    <td><input  type="checkbox" value="ACCEPT" name="accept_terms">
							    </td>
							    <td ><b>Check this box if you accept <a href="terms_conditions.pdf" target="_blank">SWA Room Accomodation Terms and Conditions</a></b>'.$this->RoomApplicationError['accept_terms'].'</td>
							    </tr>
						    <tr>
						    <td>
							    <input  type="hidden" name="FormType" value="CONTINUING">
							    <input type="submit" name="BtnRoomContApplication" value="Apply" />
							    </td>
						    <td><input type="reset" name="BtnClearForm" value="Reset"></td>
						    </tr>
					    </table>
					    </form>';
					    
		    return 	$ApplicationForm;
	    
    }

    public function UpdateRoomApplicationForm ($RegistrationNo,$AcademicYear,$ApplicantID,$ApplicationStatus) {
        $SQL = "SELECT applicant_id,registration_number,academic_year,level_of_study,mobile_number,email,
                postal_code,postal_address,date_applied,time_applied,room_application_status,room_type_preference,
                application_position,Applicant_year,gender,synchronization_flag 
                FROM hamis.room_applicant 
                WHERE applicant_id ='$ApplicantID'"  ;
                
        $ApplicationRecord = $this->MysqlHamisDB->GetRow($SQL ); 

        $Surname = $this->CCGetSession("surname");
        $OtherNames = $this->CCGetSession("otherNames");
        $FullNames = ucwords(strtolower($Surname." ".$OtherNames));
        $LevelofStudy = $ApplicationRecord['level_of_study'] ? $ApplicationRecord['level_of_study'] : $this->CCGetSession("LevelofStudy");
        $MobileNo    = $ApplicationRecord['mobile_number'] ? $ApplicationRecord['mobile_number']  : $this->CCGetSession("primary_mobile");
        $UonEmail =  $ApplicationRecord['email'] ? $ApplicationRecord['email']  : $this->CCGetSession("Email"); 
        $PostalCode = $ApplicationRecord['postal_code'];
        $PostalAddress =  $ApplicationRecord['postal_address'];
        $room_type_preference   =  $ApplicationRecord['room_type_preference'];    
        $AppGender = $this->CCGetSession("gender");  ;
            
        $ApplicationForm  = '' ;
        $PostMobileNo = $this->CCGetParam("PostMobileNo") ? $this->CCGetParam("PostMobileNo") : $MobileNo ;
        $PostEmail = $this->CCGetParam("PostEmail") ? $this->CCGetParam("PostEmail") : $UonEmail;
        $PostalCodeFld = $this->CCGetParam("PostalCodeFld") ? $this->CCGetParam("PostalCodeFld") : $PostalCode;
        $PostalAddressFld = $this->CCGetParam("PostalAddressFld") ? $this->CCGetParam("PostalAddressFld") : $PostalAddress;
        $RoomTypePreference = $this->CCGetParam("RoomTypePreference") ? $this->CCGetParam("RoomTypePreference") : $room_type_preference;
        $AppLevelofStudy = $this->CCGetParam("AppLevelofStudy") ? $this->CCGetParam("AppLevelofStudy") : $LevelofStudy ;
        $DegreeCode = $this->CCGetSession("DegCode");


        $emailtextbox = '<input type="text" id="PostEmail" name="PostEmail" size="40" value="'.$PostEmail.'">';

        $mobiletextbox = '<input type="text" id="PostMobileNo" name="PostMobileNo" value="'.$PostMobileNo.'" >';


    $ApplicationForm .= '<form  action="' .$_SERVER['PHP_SELF']. '" method="post" enctype="multipart/form-data" name="ApplyForRoom"> 
                            <table cellpadding="3" cellspacing="0" border="1">
                            <caption>
                                <h2 align="center">Room Application For: ' . $AcademicYear .'</h2>
                                <h3>The Room Application Deadline is : ' .$ApplicationStatus['CLOSING_DATE'].', There are '. $ApplicationStatus['DAYS_REMAINING'] .' Days Remaining to the End of the Room Application Period</h3>
                            </caption>
                            <tr><td colspan="4"><strong>Personal Information</strong></td></tr>
                            <tr>
                                <td><strong>Full Names</strong></td>
                                <td>'. $FullNames . '</td>
                            </tr>
                            <tr>
                                <td><strong>Registration No</strong></td>
                                <td>
                                    <input  id="regno" name="regno" type="hidden" value ="'.$RegistrationNo.'">' .$RegistrationNo . '
                                    <input  id="ApplicantID" name="ApplicantID" type="hidden" value ="'.$ApplicantID.'">
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Academic Year</strong></td>
                                <td><input  id="AcademicYear" name="AcademicYear" type="hidden" value ="'.$AcademicYear.'">'.$AcademicYear.'</td>
                            </tr>
                            <tr>
                                <td><strong><strong>Year of Study</strong></strong></td>
                                <td><input type="hidden" name="AppLevelofStudy" value="' .  $AppLevelofStudy .'"> Current Level of Study => ' . $AppLevelofStudy . '  '  . $this->RoomApplicationError['AppLevelofStudy'].'</td>
                            </tr>
                            <tr>
                                <td><strong>Mobile No</strong></td>
                                <td>' . $mobiletextbox . ' '.$this->RoomApplicationError['PostMobileNo'].'</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>' . $emailtextbox . ' '.$this->RoomApplicationError['PostEmail'].'</td>
                            </tr>
                            <tr>
                                <td><strong>Postal Code</strong></td>
                                <td><input id="PostCode" name="PostalCodeFld" type="text" value="'.$PostalCodeFld.'"> '.$this->RoomApplicationError['PostalCodeFld'].'</td>
                            </tr>
                            <tr>
                                <td><strong>Postal Address</strong></td>
                                <td><input id="PostalAddressFld" name="PostalAddressFld" type="text" value="'.$PostalAddressFld.'"/> '.$this->RoomApplicationError['PostalAddressFld'].' </td>
                            </tr>
                            <tr>
                                <td><strong>Room Preference</strong></td>
                                <td>'. $this->GetAvailableRoomTypesForStudent($DegreeCode,$AppGender,$AppLevelofStudy) .' ' .$this->RoomApplicationError['RoomTypePreference'].'</td>
                            </tr>
                                <tr>
                                <td><input  type="checkbox" value="ACCEPT" name="accept_terms">
                                </td>
                                <td ><b>Check this box if you accept <a href="terms_conditions.pdf" target="_blank">SWA Room Accomodation Terms and Conditions</a></b>'.$this->RoomApplicationError['accept_terms'].'</td>
                                </tr>
                            <tr>
                            <td align="center" colspan="2">
                                <input  type="hidden" name="FormType" value="CONTINUING">
                                <input type="submit" name="BtnRoomContUpdateApplication" value="Apply" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="submit" name="BtnCancelUpdate" value="Cancel"></td>
                            </tr>
                        </table>
                        </form>';
            $this->UpdateFormDisplayed   = TRUE;                      
            return     $ApplicationForm;
        
    }
	
	 public function UpdateModIIRoomApplicationForm ($RegistrationNo,$AcademicYear,$ApplicantID,$ModIIApplicationStatus) {
        $ModIIApplicationStatus = $this->checkModuleIIApplicationStatus(9);
		$SQL = "SELECT ra.applicant_id,ra.registration_number, ra.academic_year ,ra.level_of_study, ra.mobile_number ,ra.email, ra.postal_code, ra.postal_address ,
							    ra.date_applied, ra.time_applied, ra.room_application_status, ra.academic_year, 
								osd.start_date,osd.end_date,sc.smu_code,osd.how_to_pay
					    FROM hamis.room_applicant ra,hamis.smu_choices sc,hamis.other_student_detail osd
					    WHERE (ra.applicant_id = '$ApplicantID'
								AND ra.applicant_id = sc.applicant_id
								AND ra.applicant_id = osd.applicant_id)"  ;
                
        $ApplicationRecord = $this->MysqlHamisDB->GetRow($SQL );
		$Names = ucwords(strtolower($this->CCGetSession("surname").' '.$this->CCGetSession("otherNames")));
		$vregno = $RegistrationNo;
        $vlevel = $ApplicationRecord['level_of_study'] ? $ApplicationRecord['level_of_study'] : $this->CCGetSession("LevelofStudy");
        $vmobile    = $ApplicationRecord['mobile_number'] ? $ApplicationRecord['mobile_number']  : $this->CCGetSession("primary_mobile");
        $vemail =  $ApplicationRecord['email'] ? $ApplicationRecord['email']  : $this->CCGetSession("Email"); 
        $vpostcode = $ApplicationRecord['postal_code'];
        $vpostaddress =  $ApplicationRecord['postal_address'];
		$StartDate =  $ApplicationRecord['start_date'];
		$EndDate =  $ApplicationRecord['end_date'];
		$vallsmu = $ApplicationRecord['smu_code'];
        $AppGender = $this->CCGetSession("gender");  
		$vpayment =  $ApplicationRecord['how_to_pay'];
            
        $ApplicationForm  = '' ;
        $PostMobileNo = $this->CCGetParam("PostMobileNo") ? $this->CCGetParam("PostMobileNo") : $MobileNo ;
        $PostEmail = $this->CCGetParam("PostEmail") ? $this->CCGetParam("PostEmail") : $UonEmail;
        $PostalCodeFld = $this->CCGetParam("PostalCodeFld") ? $this->CCGetParam("PostalCodeFld") : $PostalCode;
        $PostalAddressFld = $this->CCGetParam("PostalAddressFld") ? $this->CCGetParam("PostalAddressFld") : $PostalAddress;
        $RoomTypePreference = $this->CCGetParam("RoomTypePreference") ? $this->CCGetParam("RoomTypePreference") : $room_type_preference;
        $AppLevelofStudy = $this->CCGetParam("AppLevelofStudy") ? $this->CCGetParam("AppLevelofStudy") : $LevelofStudy ;
        $DegreeCode = $this->CCGetSession("DegCode");
		

        $emailtextbox = '<input type="text" id="PostEmail" name="PostEmail" size="40" value="'.$PostEmail.'">';

        $mobiletextbox = '<input type="text" id="PostMobileNo" name="PostMobileNo" value="'.$PostMobileNo.'" >';
		$htmlSelect .= '<option value="9" >Select Location</option>';
			
			$Selected = 'selected';
			if($vallsmu== 9)
				$htmlSelect .= '<option value="9" selected = "selected">LOWER KABETE</option>';
			else
				$htmlSelect .= '<option value="9">LOWER KABETE</option>';
				
			if($vallsmu== 10)
				$htmlSelect .= '<option value="10" selected =  "selected">KIKUYU</option>';
			else
				$htmlSelect .= '<option value="10" >KIKUYU</option>';
			
		$phpSelf = $_SERVER['PHP_SELF'];
		$form ='<form name="ApplicationForm" onSubmit="return page_OnSubmit();" method="post" action='.$phpSelf.'>';
		$form .='<table width="100%" cellpadding=0 cellspacing=0 border=0>';
		$form .=' <caption>
					<h2 align="center">Room Application For: ' . $AcademicYear .'</h2>
					<h3>The Room Application Deadline is : ' .$ModIIApplicationStatus['CLOSING_DATE'].', There are '. $ModIIApplicationStatus['DAYS_REMAINING'] .' Days Remaining to the End of the Room Application Period</h3>
				</caption>';
		$form .='<tr><td width="99%" align=center><basefont size=3><div align="center"><div class="TabView" id="TabView">';
		$form .='<div class="Pages" style="width:$ScreenWidthpx; height:$Screenheightpx; align: left;">';
		$form .='<div class="Page">';
	    $form .='<div class="Pad">';
		$form .='<table  cellspacing="1" cellpadding="3" class="FacetFormTABLE" align="center">';
		$form .='<tr><td colspan="4" class="FacetFormHeaderFont"><span class="style3 style5"><strong>Applicant\'s Personal Details</strong></span></td></tr>';
		$form .='<tr><td class="FacetColumnTD"><strong>Names</strong></td><td colspan="3" class="FacetDataTD">&nbsp;<font color ="orange"><b>"'.$Names.'"</b></font></td></tr>';
		$form .='<tr><td class="FacetColumnTD"><strong>Registration Number <strong><span class="asteriks" >*</span></strong></td>';
		$form .='<td class="FacetDataTD"><input name="vregno" type="text" class="FacetInput" id="vregno" value="'.$vregno.'" size="20" readonly="true">';
		$form .='<input name="ApplicantID" type="hidden" id="ApplicantID" value="'.$ApplicantID .'"></td></tr>';
		$form .='<tr><td class="FacetColumnTD"><strong>Academic Year <strong><span class="asteriks">*</span></strong></td>';
		$form .='<td class="FacetDataTD"><input name="AcademicYear" type="text" class="FacetInput" id="AcademicYear" value="'.$AcademicYear.'" size="20" readonly="true"></td></tr>';
		
		$form .='<input type = "hidden" name="vlevel" class="FacetSelect" id="vlevel" value="'.$vlevel.'">
				<tr><td class="FacetColumnTD"><strong>Mobile Number</strong></td>
				<td class="FacetDataTD"><input name="vmobile" type="text" class="FacetInput" id="vmobile" value="'.$vmobile.'" size="15" maxlength="10">'.$this->RoomApplicationError['vmobile'].'</td> </tr>';
		$form .='<tr>
			<td class="FacetColumnTD"><strong>Postal Adress <strong><span class="asteriks"><sup>*</sup></span></strong></strong></td>
			<td class="FacetDataTD" cclass="FacetDataTD"><input name="vpostaddress" type="text" class="FacetInput" id="vpostaddress" value="'.$vpostaddress.'" size="25">'.$this->RoomApplicationError['vpostaddress'].'</td></tr>
			<tr><td class="FacetColumnTD"><strong>Postal Code<strong><span class="style4"></span></strong></strong></td>
			<td class="FacetDataTD"><input name="vpostcode" type="text" class="FacetInput" id="vpostcode" value="'.$vpostcode.'" size="20">'.$this->RoomApplicationError['vpostcode'].'</td>
		</tr>';
		 $form .='<tr>
			<td class="FacetColumnTD"><strong>E-mail</strong></td>
			<td class="FacetDataTD"><input name="vemail" type="text" class="FacetInput" id="vemail" value="'.$vemail.'" size="40">'.$this->RoomApplicationError['vemail'].'</td></tr>
			<tr><td class="FacetColumnTD"><strong>Gender</strong></td>
			<td class="FacetDataTD" colspan =3>
			<select name="vgender" class="FacetSelect" id="vgender">
			
				$MaleSelected ="";
				$FemaleSelected ="" ;
				if($vgender  =="M")
					$MaleSelected = " SELECTED " ;
				elseif($vgender  =="F")
					$FemaleSelected = " SELECTED " ;
				<OPTION value="M" $MaleSelected>MALE </OPTION>
				<OPTION value="F" $FemaleSelected>FEMALE </OPTION>
			</select>	'.$this->RoomApplicationError['vgender'].'</td>
		  </tr>';
		 $form .='<tr><td colspan="4" class="FacetFormHeaderFont"><hr width="100%"></td> </tr>';
		 $form .='<tr><td colspan="4" class="FacetColumnTDSeperator"><strong>Applicant Session Dates</strong></strong></td></tr>';					
		 $form .='<tr>
			<td class="FacetColumnTD"><strong>Start Date<strong><span class="asteriks">*</span></strong></td>
			<td class="FacetDataTD">
			<input name="StartDate" type="text" class="FacetInput" id="StartDate" value="'.$StartDate.'" size="15">
			 <a class="FacetDataLink" href="javascript:showDatePicker(\'DateObj\',\'ApplicationForm\',\'StartDate\');"><img border="0" src="/swa/Themes/DatePicker/DatePicker1.gif"></a>'.$this->RoomApplicationError['StartDate'].'	</td></tr>
									 
			 <tr><td class="FacetColumnTD"><strong>End Date<strong><span class="asteriks">*</span></strong></td>
			<td class="FacetDataTD">
			<input name="EndDate" type="text" class="FacetInput" id="EndDate" value="'.$EndDate.'" size="15">
			 <a class="FacetDataLink" href="javascript:showDatePicker(\'DateObj\',\'ApplicationForm\',\'EndDate\');"><img border="0" src="/swa/Themes/DatePicker/DatePicker1.gif"></a>'.$this->RoomApplicationError['EndDate'].'</td>
			</tr>';
		$form .='<tr> <td class="FacetColumnTD"><strong>How will you pay?<span class="asteriks" >*</span></strong></td>
				<td class="FacetDataTD" colspan="3">
				<select name="vpayment" class="FacetSelect" id="vpayment">';
					if($vpayment  =="PER MONTH"){
						
						$form .='<OPTION value="PER MONTH" "SELECTED">PER MONTH </OPTION>';
					}
					else{
						
						$form .='<OPTION value="PER MONTH" >PER MONTH </OPTION>';
					}
						
					if($vpayment  =="LUMPSUM"){
						$form .='<OPTION value="LUMPSUM" "SELECTED">LUMPSUM </OPTION>';
					}
					else{
						$form .='<OPTION value="LUMPSUM" </OPTION>';
					}
		 $form .='</select>	'.$this->RoomApplicationError['vpayment'].'</td></tr>';
		 $form .='<tr><td colspan="4" class="FacetFormHeaderFont"><hr width="100%"></td> </tr>';
		 
		 $form .='<tr><td colspan="4" class="FacetColumnTDSeperator"><strong>Applicant Location choices</strong>' .$this->RoomApplicationError['PickSmus'].'</td></tr>';
		 $form .='<tr> <td class="FacetColumnTD" > <strong>Location Choices  <span class="asteriks" >*</span> </strong> </td> <td ><select name="vallsmu" id="vallsmu" multiple="multiple" size="5">';
		 $SMUlist = $this->MysqlHamisDB->GetArray("SELECT SMU_CODE,SMU_NAME FROM hamis.smus WHERE SMU_CODE IN('9','10','12')	ORDER BY  SMU_NAME ASC");
						$Default = false;
							foreach($SMUlist as $SMUkey =>$SMUValue)
							{
								if($vallsmu== $SMUValue['SMU_CODE'])
								{
									$Default = true;
									$Selected = ' SELECTED ';
								}
								else
									$Selected = ' ';
								$form .='<option value='.$SMUValue['SMU_CODE'].' $Selected>'.$SMUValue['SMU_NAME'].'</option>';
							}
							if(!$Default)
							{
							
								$form .='<option value="0" SELECTED>Select SMU</option>';
							
							}
			 $form .='</select></td>'; 
			 $form .='<td colspan="2"><input type="button" name="smurightbutton" id="smurightbutton" onClick="smurightbutton_OnClick();" value=">>" ><br>
						<input type="button" name="smuleftbutton" id="smuleftbutton" onClick="smuleftbutton_OnClick();" value="<<" >
						 <select name="vselectedsmu" id="vselectedsmu" multiple="multiple" size="5"> </select>
						 <input type="hidden" name="PickSmus"  value="" id="PickSmus">
						 
						</td></tr>';
		// $form .=$htmlSelect;
		
		  $form .=$this->RoomApplicationError['vpayment'].'</td></tr>';
		
		  $form .='<tr><td colspan="4" class="FacetFormHeaderFont"><hr width="100%"></td> </tr>';	
		  
		  $form .='<tr> <input type="hidden" name="vcategoryId" value='.$vcategoryId.'>
			<td colspan="4" class="FacetDataTD" align="center"><input type="submit" name="BtnRoomModIIUpdateApplication" value="Save Changes" class="FacetButton" onClick="window.document.ApplicationForm.VisibleTab.value=\"1\"; window.document.ApplicationForm.submit();"></td>
		  </tr>';
		  $form .='</table></div></div>';			
		  $form .='</form>';
		   $this->UpdateFormDisplayed   = TRUE;      
		return $form;

/*
    $ApplicationForm .= '<form  action="' .$_SERVER['PHP_SELF']. '" method="post" enctype="multipart/form-data" name="ApplyForRoom"> 
                            <table cellpadding="3" cellspacing="0" border="1">
                            <caption>
                                <h2 align="center">Room Application For: ' . $AcademicYear .'</h2>
                                <h3>The Room Application Deadline is : ' .$ApplicationStatus['CLOSING_DATE'].', There are '. $ApplicationStatus['DAYS_REMAINING'] .' Days Remaining to the End of the Room Application Period</h3>
                            </caption>
                            <tr><td colspan="4"><strong>Personal Information</strong></td></tr>
                            <tr>
                                <td><strong>Full Names</strong></td>
                                <td>'. $FullNames . '</td>
                            </tr>
                            <tr>
                                <td><strong>Registration No</strong></td>
                                <td>
                                    <input  id="regno" name="regno" type="hidden" value ="'.$RegistrationNo.'">' .$RegistrationNo . '
                                    <input  id="ApplicantID" name="ApplicantID" type="hidden" value ="'.$ApplicantID.'">
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Academic Year</strong></td>
                                <td><input  id="AcademicYear" name="AcademicYear" type="hidden" value ="'.$AcademicYear.'">'.$AcademicYear.'</td>
                            </tr>
                            <tr>
                                <td><strong><strong>Year of Study</strong></strong></td>
                                <td><input type="hidden" name="AppLevelofStudy" value="' .  $AppLevelofStudy .'"> Current Level of Study => ' . $AppLevelofStudy . '  '  . $this->RoomApplicationError['AppLevelofStudy'].'</td>
                            </tr>
                            <tr>
                                <td><strong>Mobile No</strong></td>
                                <td>' . $mobiletextbox . ' '.$this->RoomApplicationError['PostMobileNo'].'</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>' . $emailtextbox . ' '.$this->RoomApplicationError['PostEmail'].'</td>
                            </tr>
                            <tr>
                                <td><strong>Postal Code</strong></td>
                                <td><input id="PostCode" name="PostalCodeFld" type="text" value="'.$PostalCodeFld.'"> '.$this->RoomApplicationError['PostalCodeFld'].'</td>
                            </tr>
                            <tr>
                                <td><strong>Postal Address</strong></td>
                                <td><input id="PostalAddressFld" name="PostalAddressFld" type="text" value="'.$PostalAddressFld.'"/> '.$this->RoomApplicationError['PostalAddressFld'].' </td>
                            </tr>
                            <tr>
                                <td><strong>Room Preference</strong></td>
                                <td>'. $this->GetAvailableRoomTypesForStudent($DegreeCode,$AppGender,$AppLevelofStudy) .' ' .$this->RoomApplicationError['RoomTypePreference'].'</td>
                            </tr>
                                <tr>
                                <td><input  type="checkbox" value="ACCEPT" name="accept_terms">
                                </td>
                                <td ><b>Check this box if you accept <a href="terms_conditions.pdf" target="_blank">SWA Room Accomodation Terms and Conditions</a></b>'.$this->RoomApplicationError['accept_terms'].'</td>
                                </tr>
                            <tr>
                            <td align="center" colspan="2">
                                <input  type="hidden" name="FormType" value="CONTINUING">
                                <input type="submit" name="BtnRoomContUpdateApplication" value="Apply" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="submit" name="BtnCancelUpdate" value="Cancel"></td>
                            </tr>
                        </table>
                        </form>';
            $this->UpdateFormDisplayed   = TRUE;                      
            return     $ApplicationForm;*/
        
    }

    public function RoomApplicationFormFirstYears($RegistrationNo,$mobileno,$Gender,$DegreeCode,$LevelofStudy,$UonEmail, $AcademicYear,$ApplicationStatus) {
	    
    $Surname = $this->CCGetSession("surname");
    $OtherNames = $this->CCGetSession("otherNames");
    $FullNames = ucwords(strtolower($Surname." ".$OtherNames));
    $LevelofStudy = $this->CCGetSession("LevelofStudy");
    $MobileNo    = $this->CCGetSession("primary_mobile"); 
    $PostMobileNo = $this->CCGetParam("PostMobileNo");
    $PostEmail = $this->CCGetParam("PostEmail");
    $PostalCodeFld = $this->CCGetParam("PostalCodeFld");
    $PostalAddressFld = $this->CCGetParam("PostalAddressFld");
    $RoomTypePreference = $this->CCGetParam("RoomTypePreference");
    $AppLevelofStudy = $this->CCGetParam("AppLevelofStudy") ? $this->CCGetParam("AppLevelofStudy") : 	$LevelofStudy ;
    $PostalAddress =    $this->CCGetParam("PostalAddress");
    $PostalCode = $this->CCGetParam("PostalCode"); 
    $OphanStatus = $this->CCGetParam("OphanStatus"); 
    $SingleFamilyStatus = $this->CCGetParam("SingleFamilyStatus");  
    $HelbLoanAmount   =   $this->CCGetParam("HelbLoanAmount");  
    $AmountCanPay   =     $this->CCGetParam("AmountCanPay"); 
    $RequireBursary   =   $this->CCGetParam("RequireBursary");	
    $SponsorType =        $this->CCGetParam("SponsorType");  
    $SponsorNames =       $this->CCGetParam("SponsorNames"); 
    $SponsorAddress =     $this->CCGetParam("SponsorAddress");	
    $SponsorTelephone =   $this->CCGetParam("SponsorTelephone");
    $ImpairementStatus =     $this->CCGetParam("ImpairementStatus"); 	

    $Fathersurname  =   $this->CCGetParam("Fathersurname");
    $FatherotherNames  =   $this->CCGetParam("FatherotherNames");
    $FatherID  =   $this->CCGetParam("FatherID"); 
    $FatherPIN  =   $this->CCGetParam("FatherPIN"); 
    $FatherHighestEducationLevel  =   $this->CCGetParam("FatherHighestEducationLevel");    
    $FatherEmployedStatus =     $this->CCGetParam("FatherEmployedStatus"); 
    $FatherOccupation =    $this->CCGetParam("FatherOccupation"); 
    $FatherEmployerBusiness  =    $this->CCGetParam("FatherEmployerBusiness"); 
    $FatherGrossMonthlySalary =   $this->CCGetParam("FatherGrossMonthlySalary");	
    $FatherBusinessEarnings  =  $this->CCGetParam("FatherBusinessEarnings");
    $FatherFarmingEarnings 	=  $this->CCGetParam("FatherFarmingEarnings"); 
    $FatherFarmingEarnings 	=  $this->CCGetParam("FatherFarmingEarnings");
    $FatherMonthlyPension =   $this->CCGetParam("FatherMonthlyPension");  
     
    $Mothersurname  =   $this->CCGetParam("Mothersurname"); 
    $MotherotherNames  =   $this->CCGetParam("MotherotherNames"); 
    $MotherID  =   $this->CCGetParam("MotherID"); 
    $MotherPIN  =   $this->CCGetParam("MotherPIN"); 
    $MotherHighestEducationLevel =   $this->CCGetParam("MotherHighestEducationLevel"); 
    $MotherEmployedStatus =     $this->CCGetParam("MotherEmployedStatus"); 
    $MotherOccupation =    $this->CCGetParam("MotherOccupation"); 
    $MotherEmployerBusiness  =    $this->CCGetParam("MotherEmployerBusiness");  
    $MotherGrossMonthlySalary =   $this->CCGetParam("MotherGrossMonthlySalary");	
    $MotherBusinessEarnings  =  $this->CCGetParam("MotherBusinessEarnings");		
    $MotherFarmingEarnings    =  $this->CCGetParam("MotherFarmingEarnings");	
    $MotherMonthlyPension    =  $this->CCGetParam("MotherMonthlyPension");	
    $BtnRoomApplication = $this->CCGetParam("BtnRoomApplication");

    if (!strlen($UonEmail))
	    $emailtextbox = '<input type="text" id="PostEmail" name="PostEmail" / value="'.$Email.'">';
    else
	    $emailtextbox = '<input type="hidden" id="PostEmail" name="PostEmail" value="'.$UonEmail.'">' . $UonEmail ;
    if (!strlen($MobileNo))
	    $mobiletextbox = '<input type="text" id="PostMobileNo" name="PostMobileNo" value="'.$PostMobileNo.'">';
    else
	    $mobiletextbox = '<input type="hidden" id="PostMobileNo" name="PostMobileNo" value="'. $MobileNo . '" >' . $MobileNo;

    $ApplicationForm  = '' ; 
    $ApplicationForm .=  '<form name="ApplyForRoom" method="post" action=" ' . $_SERVER['PHP_SELF'] .'">
					      <table  cellspacing="3" cellpadding="3" class="FacetFormTABLE" align="center" style ="background-color: #F4F4F4; 	margin-left: 0px; 	margin-top: 0px;">
					      <caption class="FacetFieldCaptionTD">
							    <h2>Room Application For:'. $AcademicYear .'</h2>
							     <h3>The Room Application Deadline is : ' .$ApplicationStatus['CLOSING_DATE'].', There are '. $ApplicationStatus['DAYS_REMAINING'] .' Days Remaining to the End of the Room Application Period</h3>
					      </caption> 
						    <tr>
						      <td colspan="4" class="FacetFormHeaderFont"><span style="color: #444; font-size: large"><strong>Applicant\'s Personal Details</strong></span><hr> </td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Surname</strong></td>
						      <td class="FacetDataTD"><input name="Surname" type="hidden" class="FacetInput" id="Surname" value="'. $Surname .'" size="15">' . $Surname .'
							      <input name="ApplicantNo" type="hidden" id="ApplicantNo" value="' . $ApplicantNo .'"></td>
						      <td class="FacetColumnTD"><strong>OtherNames in full <strong><span class="style4"></span></strong></strong></td>
						      <td class="FacetDataTD"><input name="OtherNames" type="hidden" class="FacetInput" id="OtherNames" value="' . $OtherNames .'" size="20">'
							      . $OtherNames .'</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Postal Adress <strong><span class="style4"><sup>*</sup></span></strong></strong></td>
						      <td class="FacetDataTD" cclass="FacetDataTD"> <input name="PostalAddressFld" type="text" class="FacetInput" id="PostalAddressFld" value="' . $PostalAddressFld .'" size="25">'. $this->DisplayFormError($this->RoomApplicationError['PostalAddressFld']).'</td>
						      <td class="FacetColumnTD"><strong>Postal Code <strong><span class="style4"></span></strong></strong></td>
						      <td class="FacetDataTD"><input name="PostalCodeFld" type="text" class="FacetInput" id="PostalCodeFld" value="' . $PostalCodeFld .'" size="20">'.$this->DisplayFormError($this->RoomApplicationError['PostalCodeFld']).'</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Level of Study <strong><span class="style4"><sup>*</sup></span></strong></strong></td>
						      <td class="FacetDataTD"><input type="text" name ="AppLevelofStudy" value="'. $AppLevelofStudy.'">'. ' Currently Level:(' . $LevelofStudy . ') '.$this->DisplayFormError($this->RoomApplicationError['AppLevelofStudy']).'</td>
						      <td class="FacetColumnTD"><strong>Room Type Preference <strong><span class="style4"><sup>*</sup></span></strong></strong></td>
						      <td class="FacetDataTD">' .
							    $this->GetAvailableRoomTypesForStudent($DegreeCode,$Gender,$LevelofStudy,$RoomTypePreference) . ' ' . $this->DisplayFormError($this->RoomApplicationError['RoomTypePreference']). '
                              </td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Mobile No. <strong><span class="style4"><sup>*</sup></span></strong></strong></td>
						      <td class="FacetDataTD"><input name="PostMobileNo" type="text" class="FacetInput" id="PostMobileNo" value="' . $PostMobileNo .'" size="15"> ' . $this->DisplayFormError($this->RoomApplicationError['PostMobileNo']). '</td>
						      <td class="FacetColumnTD"><strong>E-mail</strong></td>
						      <td class="FacetDataTD"><input name="PostEmail" type="text" class="FacetInput" id="PostEmail" value="' . $PostEmail .'" size="20"> ' . $this->DisplayFormError($this->RoomApplicationError['PostEmail']). '</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Are you an Ophan <strong><span class="style4"><sup>*</sup></span></strong></strong></td>
						      <td class="FacetDataTD">';
						      switch($OphanStatus) {
							      case 1:
								    $SelectOrphanYes =' CHECKED ';
								    break;
							     case 0:
								    $SelectOrphanNo  =' CHECKED ';
								    break;
						      }
						      $ApplicationForm .= '<input name="OphanStatus" type="radio" class="FacetInput" id="OphanStatus"  value="1" '.$SelectOrphanYes .'>
							    YES
							    <input name="OphanStatus" type="radio" class="FacetInput" id="OphanStatus"  value="0" '.$SelectOrphanNo .'>
							    NO ' . $this->DisplayFormError($this->RoomApplicationError['OphanStatus']). '</td>
						      <td class="FacetColumnTD"><strong>Are you from a Single Parent <strong><span class="style4"><sup>*</sup></span></strong></strong></td>
						      <td class="FacetDataTD">';
						      switch($SingleFamilyStatus ) {
							      case 1:
								    $SelectYes =' CHECKED ';
								    break;
							     case 0:
								    $SelectNo  =' CHECKED ';
								    break;
						      }
						      $ApplicationForm .= '<input name="SingleFamilyStatus" type="radio" class="FacetInput" id="radio" value="1" '.$SelectYes .'>
							    YES
							    <input name="SingleFamilyStatus" type="radio" class="FacetInput" id="radio"value="0" '.$SelectNo .'>
							    NO  ' . $this->DisplayFormError($this->RoomApplicationError['SingleFamilyStatus']). '</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>If impaired (tick)&nbsp;&nbsp;</strong></td>
						      <td colspan="3" class="FacetDataTD">' ;
						      switch($ImpairementStatus ) {
							      case 1:
								    $SelectVisual =' CHECKED ';
								    break;
							     case 2:
								    $SelectPhysical  =' CHECKED ';
								    break;
							     case 3:
								    $SelectHearing = ' CHECKED ';
								    break;	
							     case 4:
								    $SelectOthers = ' CHECKED ';
								    break;	
						      }
						      
						      $ApplicationForm .= '<input name="ImpairementStatus" type="radio" class="FacetInput" id="OphanStatus" value="1" '.$SelectVisual .'>
							    VISUAL
							    <input name="ImpairementStatus" type="radio" class="FacetInput" id="OphanStatus" value="2" '.$SelectPhysical .'>
							    PHYSICAL
							    <input name="ImpairementStatus" type="radio" class="FacetInput" id="radio" value="3" '.$SelectHearing .'>
							    HEARING
							    <input name="ImpairementStatus" type="radio" class="FacetInput" id="radio" value="4"  '.$SelectOthers .'>
							    OTHER ' . $this->DisplayFormError($this->RoomApplicationError['ImpairementStatus']). '</td>
						    </tr>
						    <tr>
						      <td class="FacetFormHeaderFont" colspan="4"><span style="color: #444; font-size: large"><strong>HELB Loan and Bursary  (Per Annum) Status</strong></span><hr></td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>How much loan are you <br>
						      applying from HELB?&nbsp; <strong><span class="style4"><sup>*</sup></span></strong></strong></td>
						      <td class="FacetDataTD"> <select name="HelbLoanAmount" class="FacetSelect" id="HelbLoanAmount">
							      <option value="0">None</option>
							      <option value="35000">35,000</option>
							      <option value="40000">40,000</option>
							      <option value="45000">45,000</option>
							      <option value="50000">50,000</option>
							      <option value="55000">55,000</option>
							      <option value="60000">60,000</option>
							    </select> ' . $this->DisplayFormError($this->RoomApplicationError['HelbLoanAmount']). '
						      </td>
						      <td class="FacetColumnTD"><strong>How much can your family<br>
							    raise towards your fees? <strong><span class="style4"><sup>*</sup></span></strong></strong></td>
						      <td class="FacetDataTD"> <input name="AmountCanPay" class="Facetinput" id="AmountCanPay" type="text" value="'. $AmountCanPay .'">' . $this->DisplayFormError($this->RoomApplicationError['AmountCanPay']). '</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Do you require bursary? <strong><span class="style4"><sup>*</sup></span></strong></strong></td>
						      <td class="FacetDataTD">';
						      switch($RequireBursary) {
							      case 1:
								    $SelectRBYes =' CHECKED ';
								    break;
							     case 0:
								    $SelectRBNo  =' CHECKED ';
								    break;
						      }
						     $ApplicationForm .= ' <input name="RequireBursary" class="FacetSelect" id="RequireBursary" type="radio" value="1" '. $SelectRBYes .'>
							    YES
							    <input name="RequireBursary" class="FacetSelect" id="RequireBursary" type="radio" value="0" '. $SelectRBNo .'>
							    NO  ' . $this->DisplayFormError($this->RoomApplicationError['RequireBursary']). '  </td>
						      <td class="FacetColumnTD">&nbsp;</td>
						      <td class="FacetDataTD">&nbsp;</td>
						    </tr>
						    <tr>
						      <td colspan="4" class="FacetFormHeaderFont"><span style="color: #444; font-size: large"><strong>Details  of Parents</strong></span><hr> </td>
						    <tr>
						      <td colspan="2" class="FacetColumnTD"><div align="center"><strong>Father</strong></div></td>
						      <td colspan="2" class="FacetColumnTD"><div align="center"><strong>Mother</strong></div></td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Surname</strong></td>
						      <td class="FacetDataTD"> <input name="Fathersurname" type="text" class="FacetInput" id="Fathersurname" value="' . $Fathersurname .'" >  ' . $this->DisplayFormError($this->RoomApplicationError['Fathersurname']). ' 
						      <td class="FacetColumnTD"><strong>Surname</strong></td>
						      <td class="FacetDataTD">
						       <input name="Mothersurname" type="text" class="FacetInput" id="Mothersurname" value="' . $Mothersurname . '" > ' . $this->DisplayFormError($this->RoomApplicationError['Mothersurname']). '</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>OtherNames in full</strong></td>
						      <td class="FacetDataTD"><input name="FatherotherNames" type="text" class="FacetInput" id="FatherotherNames" value="' . $FatherotherNames .'" > ' . $this->DisplayFormError($this->RoomApplicationError['FatherotherNames']). '
						      <td class="FacetColumnTD"><strong>OtherNames in full</strong></td>
						      <td class="FacetDataTD"> <input name="MotherotherNames" type="text" class="FacetInput" id="MotherotherNames" value="' . $MotherotherNames . '" >' . $this->DisplayFormError($this->RoomApplicationError['MotherotherNames']). '</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>National ID Number</strong></td>
						      <td class="FacetDataTD"><input name="FatherID" type="text" class="FacetInput" id="FatherID" value="' . $FatherID .'" >  ' . $this->DisplayFormError($this->RoomApplicationError['FatherID']). '
						      <td class="FacetColumnTD"><strong>National ID Number</strong></td>
						      <td class="FacetDataTD"><input name="MotherID" type="text" class="FacetInput" id="MotherID" value="' . $MotherID . '" > ' . $this->DisplayFormError($this->RoomApplicationError['MotherID']). ' </td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>PIN Number</strong></td>
						      <td class="FacetDataTD"><input name="FatherPIN" type="text" class="FacetInput" id="FatherPIN" value="' . $FatherPIN .'" >' . $this->DisplayFormError($this->RoomApplicationError['FatherPIN']). '
						      <td class="FacetColumnTD"><strong>PIN Number</strong></td>
						      <td class="FacetDataTD"><input name="MotherPIN" type="text" class="FacetInput" id="MotherPIN" value="' . $MotherPIN . '" >' . $this->DisplayFormError($this->RoomApplicationError['MotherPIN']). '</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Highest level of Education&nbsp;</strong>&nbsp; </td>
						      <td class="FacetDataTD"><select name="FatherHighestEducationLevel" class="FacetSelect" id="FatherHighestEducationLevel">
							      <option value="NONE">None</option>
							      <option value="PRIMARY">Primary</option>
							      <option value="SECONDARY">Secondary</option>
							      <option value="TERTIARY">Tertiary</option>
							      <option value="UNIVERSITY">University</option>
							    </select>' . $this->DisplayFormError($this->RoomApplicationError['FatherHighestEducationLevel']). '
						      <td class="FacetColumnTD"><strong>Highest level of Education&nbsp;</strong>&nbsp; </td>
						      <td class="FacetDataTD"><select name="MotherHighestEducationLevel" class="FacetSelect" id="MotherHighestEducationLevel">
							      <option value="NONE">None</option>
							      <option value="PRIMARY">Primary</option>
							      <option value="SECONDARY">Secondary</option>
							      <option value="TERTIARY">Tertiary</option>
							      <option value="UNIVERSITY">University</option>
						      </select>' . $this->DisplayFormError($this->RoomApplicationError['MotherHighestEducationLevel']). '
						      </td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Employed?</strong></td>
						      <td class="FacetDataTD">';
						       switch($FatherEmployedStatus) {
							      case 1:
								    $SelectEmployedYes =' CHECKED ';
								    break;
							     case 0:
								    $SelectEmployedNo  =' CHECKED ';
								    break;
						      }
						     $ApplicationForm .='<input name="FatherEmployedStatus" type="radio" class="FacetInput" id="FatherEmployedStatus" value="1" '.$SelectEmployedYes.' >
							    YES
							    <input name="FatherEmployedStatus" type="radio" class="FacetInput" id="FatherEmployedStatus" value="0" '.$SelectEmployedNo.' >
							    NO  ' . $this->DisplayFormError($this->RoomApplicationError['FatherEmployedStatus']). '
						      <td class="FacetColumnTD"><strong>Employed</strong><strong>?</strong></td>
						      <td class="FacetDataTD">'.$MotherEmployedStatusFldErr.' ';
						       switch($MotherEmployedStatus) {
							      case 1:
								    $SelectEmployedYes =' CHECKED ';
								    break;
							     case 0:
								    $SelectEmployedNo  =' CHECKED ';
								    break;
						      }
						      $ApplicationForm .='<input name="MotherEmployedStatus" type="radio" class="FacetInput" id="radio2" value="1" '.$SelectEmployedYes.' >
							    YES
							    <input name="MotherEmployedStatus" type="radio" class="FacetInput" id="radio2" value="0" '.$SelectEmployedNo.' >
							    NO ' . $this->DisplayFormError($this->RoomApplicationError['MotherEmployedStatus']). '</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Occupation/Profession </strong></td>
						      <td class="FacetDataTD"><select name="FatherOccupation" type="text" class="FacetInput" id="FatherOccupation">
						      <option value="">Select...</option>'
							     . $this->GetProffesionsOccupations($FatherOccupation).
							    '</select>  ' . $this->DisplayFormError($this->RoomApplicationError['FatherOccupation']). '
						      <td class="FacetColumnTD"><strong>Occupation/Profession </strong></td>
						      <td class="FacetDataTD"><select name="MotherOccupation" type="text" class="FacetInput" id="MotherOccupation"><option value="">Select...</option>
								    '.$this->GetProffesionsOccupations($MotherOccupation).'
						      </select> ' . $this->DisplayFormError($this->RoomApplicationError['MotherOccupation']). ' 
						      </td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Name of Employer/business</strong></td>
						      <td class="FacetDataTD"><input name="FatherEmployerBusiness" type="text" class="FacetInput" id="FatherEmployerBusiness" value="' . $FatherEmployerBusiness . '" >' . $this->DisplayFormError($this->RoomApplicationError['FatherEmployerBusiness']). ' </td>
						      <td  class="FacetColumnTD"><strong>Name of Employer/business</strong></td>
						      <td class="FacetDataTD"><input name="MotherEmployerBusiness" type="text" class="FacetInput" id="MotherEmployerBusiness" value="' . $MotherEmployerBusiness . '" >' . $this->DisplayFormError($this->RoomApplicationError['MotherEmployerBusiness']). '</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Gross Salary (Monthly) KSh</strong></td>
						      <td class="FacetDataTD"><input name="FatherGrossMonthlySalary" type="text" class="FacetInput" id="FatherGrossMonthlySalary" value="' . $FatherGrossMonthlySalary .'" >   ' . $this->DisplayFormError($this->RoomApplicationError['FatherGrossMonthlySalary']). '
						      <td class="FacetColumnTD"><strong>Gross Salary (Monthly) KSh</strong></td>
						      <td class="FacetDataTD">'.$MotherIcomeFldErr.' <input name="MotherGrossMonthlySalary" type="text" class="FacetInput" id="MotherGrossMonthlySalary" value="' . $MotherGrossMonthlySalary .'" >' . $this->DisplayFormError($this->RoomApplicationError['MotherGrossMonthlySalary']). '</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Business (Annual) KSh</strong></td>
						      <td class="FacetDataTD"><input name="FatherBusinessEarnings" type="text" class="FacetInput" id="FatherBusinessEarnings" value="' . $FatherBusinessEarnings . '" > ' . $this->DisplayFormError($this->RoomApplicationError['FatherBusinessEarnings']). '
						      <td class="FacetColumnTD"><strong>Business (Annual) KSh</strong></td>
						      <td class="FacetDataTD"><input name="MotherBusinessEarnings" type="text" class="FacetInput id="motherbusinessearnings" value="' . $MotherBusinessEarnings .'" >' . $this->DisplayFormError($this->RoomApplicationError['motherbusinessearnings']). '  </td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Farming (Annual)KSh. </strong></td>
						      <td class="FacetDataTD"><input name="FatherFarmingEarnings" type="text" class="FacetInput" id="FatherFarmingEarnings" value="' . $FatherFarmingEarnings . '" > ' . $this->DisplayFormError($this->RoomApplicationError['FatherFarmingEarnings']). '
						      <td class="FacetColumnTD"><strong>Farming (Annual)KSh</strong><strong>.</strong></td>
						      <td class="FacetDataTD"><input name="MotherFarmingEarnings" type="text" class="FacetInput" id="MotherFarmingEarnings" value="' . $MotherFarmingEarnings .'" >' . $this->DisplayFormError($this->RoomApplicationError['MotherFarmingEarnings']). ' </td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Pension (Monthly)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;KSh.</strong></td>
						      <td class="FacetDataTD"><input name="FatherMonthlyPension" type="text" class="FacetInput" id="FatherMonthlyPension" value="' . $FatherMonthlyPension .'" > ' . $this->DisplayFormError($this->RoomApplicationError['FatherMonthlyPension']). '
						      <td class="FacetColumnTD"><strong>Pension (Monthly)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;KSh.</strong></td>
						      <td class="FacetDataTD"><input name="MotherMonthlyPension" type="text" class="FacetInput" id="MotherMonthlyPension" value="' . $MotherMonthlyPension .'" >' . $this->DisplayFormError($this->RoomApplicationError['MotherMonthlyPension']). '</td>
						    </tr>
						    <tr>
						      <td colspan=4" class="FacetFormHeaderFont"><span style="color: #444; font-size: large"><strong>Sponsorship Status</strong></span><hr></td>
						    <tr>
						      <td class="FacetColumnTD"><strong>I</strong><strong>f  both parents deceased who <br>
						      has been paying your fees?</strong></td>
						      <td class="FacetDataTD">'.$this->GetSponsorType ($SponsorType);
							    
						      $ApplicationForm .= ' ' . $this->DisplayFormError($this->RoomApplicationError['SponsorType']). '<td class="FacetColumnTD"><strong>Guardian/Sponsor/Public<br> 
						      trustee </strong></td>
						      <td class="FacetDataTD"><input name="SponsorNames" type="text" class="FacetInput" id="SponsorFNames" value="' . $SponsorNames .'" > ' . $this->DisplayFormError($this->RoomApplicationError['SponsorFNames']). '</td>
						    </tr>
						    <tr>
						      <td class="FacetColumnTD"><strong>Guardian/Sponsor/Public <br>
						      trustee </strong><strong> Address </strong></td>
						      <td class="FacetDataTD"><input name="SponsorAddress" type="text" class="FacetInput" id="SponsorAddress" value="' . $SponsorAddress .'" > ' . $this->DisplayFormError($this->RoomApplicationError['SponsorAddress']). '
						      <td class="FacetColumnTD"><strong>Guardian/Sponsor/Public<br>
							    trustee </strong><strong>Telphone No </strong></td>
						      <td class="FacetDataTD"><input name="SponsorTelephone" type="text" class="FacetInput" id="SponsorTelephone" value="' . $SponsorTelephone .'" > ' . $this->DisplayFormError($this->RoomApplicationError['SponsorTelephone']). ' </td>
						    </tr>
						    <tr>
						      <td colspan="4" class="FacetDataTD" align="center">
									    <input  type="hidden" name="FormType" value="FIRSTYEAR" />
								       <input type="submit" name="BtnRoomApplication" value="Apply for a Room" class="FacetButton" >
								       <input name="submitted" value=1 type="hidden">
								     </td>
						    </tr>
					      </table>
					    </form>' ;
		    return 	$ApplicationForm;
	    
    }  

    public function DisplayFormError($FormError){
    $FormError = '<font color="red">' . $FormError . '</font> '   ;
    return  $FormError;
    }

    public function GetProffesionsOccupations ($DefaultOccupation=0)  {
    $SQL  = "SELECT proffessionals_occupation_ID,preffession_Occupation_name FROM hamis.professions_occupations";
    $rs =  $this->MysqlHamisDB->GetArray($SQL );
    $ProfessionsOccupationsDisplayInfo = '';
     if(is_array($rs)) {
	       foreach($rs as $Key => $POInfo){
                    if($DefaultOccupation == $POInfo['proffessionals_occupation_ID'])
                        $SelectedOccupation = ' SELECTED ' ;
                    else
                        $SelectedOccupation = '  ' ;
                        
				    $ProfessionsOccupationsDisplayInfo .=  '<option value="' . $POInfo['proffessionals_occupation_ID'].'" ' . $SelectedOccupation .' >'. ucwords(strtolower($POInfo['preffession_Occupation_name']))."</option>";
		    }
       }
      return 	$ProfessionsOccupationsDisplayInfo;
    }

    public function GetSponsorType ($DefaulType='')  {
    $SQL  = "SELECT * FROM hamis.sponsor_type";
    $rs =  $this->MysqlHamisDB->GetArray($SQL );
    $SponsorTypeDisplayInfo = '';
     if(is_array($rs)) {
	       foreach($rs as $Key => $SponsorInfo){
		       if($DefaulType == $SponsorInfo['sponsor_type'] )
				    $CheckedStatus = ' checked ' ;
		       else
				     $CheckedStatus = '  ' ;
				     
				    $SponsorTypeDisplayInfo .= $SponsorInfo['sponsor_type'];
				    $SponsorTypeDisplayInfo .=  ' <input name="SponsorType" type="radio" value="' . $SponsorInfo['sponsor_id'].'" '. $CheckedStatus. ' >';
		    }
       }
      return 	$SponsorTypeDisplayInfo;
    }

    public function GetAvailableRoomTypesForStudent($DegreeCode,$Gender,$LevelofStudy,$DefaulValue=''){
    $Gender = $this->CCGetSession("gender");
    $DegreeCode = $this->CCGetSession("DegCode");
    $LevelofStudy = $this->CCGetSession("LevelofStudy");
    $GetRoomTypes = "SELECT DISTINCT
					    room_types.OCCUPANCY
					    , room_types.CHARGE_PER_DAY
				    FROM
					    hamis.rooms
					    INNER JOIN hamis.room_types 
						    ON (rooms.ROOM_TYPE_CODE = room_types.ROOM_TYPE_CODE)
					    INNER JOIN hamis.halls 
						    ON (rooms.HALL_CODE = halls.hall_code)
					    INNER JOIN hamis.degree_halls 
						    ON (rooms.HALL_CODE = degree_halls.hall_code)
				    WHERE 
					    hamis.degree_halls.gender = '$Gender' 
					    AND hamis.degree_halls.degree_code = '$DegreeCode' 
					    AND hamis.degree_halls.year_of_study = $LevelofStudy 
					    AND  hamis.room_types.CHARGE_PER_DAY != 10.00 					
				    ORDER BY hamis.room_types.CHARGE_PER_DAY, hamis.room_types.OCCUPANCY ASC";

    $rs =  $this->MysqlHamisDB->GetArray($GetRoomTypes);
    $RoomTypesDisplayInfo = '';
     if(is_array($rs)) {
		    $RoomTypesDisplayInfo .= "<select id=\"RoomTypePreference\" name=\"RoomTypePreference\">";
	       foreach($rs as $Key => $RoomTypeInfo){
                    $RoomTypeValue =  $RoomTypeInfo['OCCUPANCY'].','. $RoomTypeInfo['CHARGE_PER_DAY'] ;
                    if($DefaulValue == $RoomTypeValue )
                        $Selected  =' SELECTED ' ;
                    else
                       $Selected  ='  ' ;   
                       
				    $RoomTypesDisplayInfo .=  '<option value="' . $RoomTypeInfo['OCCUPANCY'].','. $RoomTypeInfo['CHARGE_PER_DAY'].'" '. $Selected .'>';
			       if ($RoomTypeInfo['OCCUPANCY']==1)
					    $whattoprint ="SINGLE";
				     else  
					    $whattoprint ="SHARED ROOM FOR ".$RoomTypeInfo['OCCUPANCY']." persons";
				    $RoomTypesDisplayInfo .= "$whattoprint @  KES ". $RoomTypeInfo['CHARGE_PER_DAY']."</option>";
		    }
		    $RoomTypesDisplayInfo .= "</select>";	
       }
      return 	$RoomTypesDisplayInfo;
    }

    public function getRoomApplicationDetails($RegNo,$AcademicYear){
	    $vstatus = $this->CCGetParam("vstatus");
	    $bookstatusquery="SELECT applicant_id,registration_number, academic_year ,level_of_study, mobile_number ,email, postal_code, postal_address ,
							    date_applied, time_applied, room_application_status, room_type_preference,academic_year 
					    FROM hamis.room_applicant  
					    WHERE (registration_number='$RegNo' AND academic_year='$AcademicYear')";

	    $BookedRoomRS = $this->MysqlHamisDB->GetRow($bookstatusquery);
	    if(is_array($BookedRoomRS) && count($BookedRoomRS) > 0){
		    return $BookedRoomRS  ;
	    }if(is_array($BookedRoomRS) && count($BookedRoomRS)==0)
		    return   $BookedRoomRS;
	    else
		    return FALSE;
    }
	
	public function getRoomModuleIIApplicationDetails($RegNo,$AcademicYear){
	    $vstatus = $this->CCGetParam("vstatus");
	    $bookstatusquery="SELECT ra.applicant_id,ra.registration_number, ra.academic_year ,ra.level_of_study, ra.mobile_number ,ra.email, ra.postal_code, ra.postal_address ,
							    ra.date_applied, ra.time_applied, ra.room_application_status, ra.academic_year, 
								osd.start_date,osd.end_date,sc.smu_code,osd.how_to_pay
					    FROM hamis.room_applicant ra,hamis.smu_choices sc,hamis.other_student_detail osd
					    WHERE (registration_number='$RegNo' AND academic_year='$AcademicYear'
								AND ra.applicant_id = sc.applicant_id
								AND ra.applicant_id = osd.applicant_id)";
	    $BookedRoomRS = $this->MysqlHamisDB->GetRow($bookstatusquery);
	    if(is_array($BookedRoomRS) && count($BookedRoomRS) > 0){
		    return $BookedRoomRS  ;
	    }if(is_array($BookedRoomRS) && count($BookedRoomRS)==0)
		    return   $BookedRoomRS;
	    else
		    return FALSE;
    }

    function DisplayApplicationInformation($RoomAppplicationRecord,$ApplicationStatus) {
	    $applicationDispalyInfo ='';
        $LevelofStudy = $this->CCGetSession("LevelofStudy"); 
        
        $ApplicationIsOpen = is_array($ApplicationStatus)? $ApplicationStatus['STATUS']  : $ApplicationStatus  ;
	    if(is_array($RoomAppplicationRecord) && count($RoomAppplicationRecord)>0){
		    $Surname = $this->CCGetSession("surname");
		    $OtherNames = $this->CCGetSession("otherNames");
		    list($RoomOccupancy,$DailyCost)     = split('[,]',$RoomAppplicationRecord['room_type_preference'])  ;
		    if($RoomOccupancy==1)
			    $RoomPreferenceTxt = "Single Room @ $DailyCost";
		    else
		      $RoomPreferenceTxt = "Shared Room for $RoomOccupancy Occupants @  $DailyCost"; 
		      
		    $applicationDispalyInfo = '<table  border="1" cellpadding="5" cellspacing="0" align="center">' ;
            
            
          $applicationDispalyInfo .= '<tr><th colspan="2"> 
                    <b>Your aplication for a room in the '. $RoomAppplicationRecord['academic_year'] . ' Academic year </b> 
                    has been considered. However, your were <u>not</u> successful  in securing  accommodation in the University halls of residence. 
                    Your Request for accomodation has been queued for consinderation should any space become available in the course of the academic year
                </th></tr>' ;
                $applicationDispalyInfo .= '</table>';    
                return $applicationDispalyInfo; 
            
            
   
             $applicationDispalyInfo .= '<tr><th colspan="2"> 
                        <b>Your aplication for a room in the '. $RoomAppplicationRecord['academic_year'] . ' Academic year </b>                         
                    </th></tr>' ;  
		    $applicationDispalyInfo .='<tr><td> <b>Name</b></td><td>'.$Surname .' ' . $OtherNames.'</td></tr>' ;
		    $applicationDispalyInfo .='<tr><td> <b>Registration No.</b></td><td>'. $RoomAppplicationRecord['registration_number'].'</td></tr>' ;
		    $applicationDispalyInfo .='<tr><td> <b>Level of Study.</b></td><td>'. $RoomAppplicationRecord['level_of_study'].'</td></tr>' ;    
		    $applicationDispalyInfo .='<tr><td> <b>Mobile No.</b></td><td>'. $RoomAppplicationRecord['mobile_number'].'</td></tr>' ; 
		    $applicationDispalyInfo .='<tr><td> <b>Email</b></td><td>'. $RoomAppplicationRecord['email'].'</td></tr>' ; 
		    $applicationDispalyInfo .='<tr><td> <b>Postal Code</b></td><td>'. $RoomAppplicationRecord['postal_code'].'</td></tr>' ;  
		    $applicationDispalyInfo .='<tr><td> <b>Postal Address</b></td><td>'. $RoomAppplicationRecord['postal_address'].'</td></tr>' ;  
		    $applicationDispalyInfo .='<tr><td> <b>Date Applied</b></td><td>'. $RoomAppplicationRecord['date_applied'].'</td></tr>' ;  
		    $applicationDispalyInfo .='<tr><td> <b>Application Status</b></td><td>APPLIED</td></tr>' ; 
		    $applicationDispalyInfo .='<tr><td> <b>Room Preference</b></td><td>'. $RoomPreferenceTxt . '</td></tr>' ;  
            if($ApplicationIsOpen =='OPEN' && $RoomAppplicationRecord['level_of_study'] > 1){
                $applicationDispalyInfo .='<tr><td colspan="2"><a href="' . $_SERVER['PHP_SELF'] .'?ApplicationID=' . $RoomAppplicationRecord['applicant_id'] .'&UpdateApplication=Update"> <b> Click here to Update your  Room Allplication Information</b><a></td></tr>' ;   
            } 
		    $applicationDispalyInfo .= '</table>';
	    }	 
	    return $applicationDispalyInfo;  
    }	
	
	function DisplayModIIApplicationInformation($RoomAppplicationRecord,$ModIIApplicationStatus) {
	    $applicationDispalyInfo ='';
        $LevelofStudy = $this->CCGetSession("LevelofStudy"); 
        
        $ApplicationIsOpen = is_array($ModIIApplicationStatus)? $ModIIApplicationStatus['STATUS']  : $ModIIApplicationStatus  ;
	    if(is_array($RoomAppplicationRecord) && count($RoomAppplicationRecord)>0){
		    $Surname = $this->CCGetSession("surname");
		    $OtherNames = $this->CCGetSession("otherNames");
		    list($RoomOccupancy,$DailyCost)     = split('[,]',$RoomAppplicationRecord['room_type_preference'])  ;
		    if($RoomOccupancy==1)
			    $RoomPreferenceTxt = "Single Room @ $DailyCost";
		    else
		      $RoomPreferenceTxt = "Shared Room for $RoomOccupancy Occupants @  $DailyCost"; 
		      
		    $applicationDispalyInfo = '<table  border="1" cellpadding="5" cellspacing="0" align="center">' ;
            
          /*  
           $applicationDispalyInfo .= '<tr><th colspan="2"> 
                    <b>Your aplication for a room in the '. $RoomAppplicationRecord['academic_year'] . ' Academic year </b> 
                    has been considered. However, your were <u>not</u> successful  in securing  accommodation in the University halls of residence. 
                    Your Request for accomodation has been queued for consinderation should any space become available in the course of the academic year
                </th></tr>' ;
                $applicationDispalyInfo .= '</table>';    
                return $applicationDispalyInfo; */
            
            
   
             $applicationDispalyInfo .= '<tr><th colspan="2"> 
                        <b>Your aplication for a room in the '. $RoomAppplicationRecord['academic_year'] . ' Academic year </b>                         
                    </th></tr>' ;  
		    $applicationDispalyInfo .='<tr><td> <b>Name</b></td><td>'.$Surname .' ' . $OtherNames.'</td></tr>' ;
		    $applicationDispalyInfo .='<tr><td> <b>Registration No.</b></td><td>'. $RoomAppplicationRecord['registration_number'].'</td></tr>' ;   
		    $applicationDispalyInfo .='<tr><td> <b>Mobile No.</b></td><td>'. $RoomAppplicationRecord['mobile_number'].'</td></tr>' ; 
		    $applicationDispalyInfo .='<tr><td> <b>Email</b></td><td>'. $RoomAppplicationRecord['email'].'</td></tr>' ; 
		    $applicationDispalyInfo .='<tr><td> <b>Postal Code</b></td><td>'. $RoomAppplicationRecord['postal_code'].'</td></tr>' ;  
		    $applicationDispalyInfo .='<tr><td> <b>Postal Address</b></td><td>'. $RoomAppplicationRecord['postal_address'].'</td></tr>' ;  
		    $applicationDispalyInfo .='<tr><td> <b>Date Applied</b></td><td>'. $RoomAppplicationRecord['date_applied'].'</td></tr>' ;  
		    $applicationDispalyInfo .='<tr><td> <b>Application Status</b></td><td>APPLIED</td></tr>' ;
			$applicationDispalyInfo .='<tr><td> <b>Start Date</b></td><td>'. $RoomAppplicationRecord['start_date'].'</td></tr>' ;  
			$applicationDispalyInfo .='<tr><td> <b>End Date</b></td><td>'. $RoomAppplicationRecord['end_date'].'</td></tr>' ;  
			$applicationDispalyInfo .='<tr><td> <b>How to Pay</b></td><td>'. $RoomAppplicationRecord['how_to_pay'].'</td></tr>' ;  
		    $applicationDispalyInfo .='<tr><td> <b>Location Choice</b></td><td>'.$this->getSMU($RoomAppplicationRecord['applicant_id']).'</td></tr>' ; 
            if($ApplicationIsOpen =='OPEN' ){
                $applicationDispalyInfo .='<tr><td colspan="2"><a href="' . $_SERVER['PHP_SELF'] .'?ApplicationID=' . $RoomAppplicationRecord['applicant_id'] .'&UpdateModuleIIApplication=Update"> <b> Click here to Update your  Room Allplication Information</b><a></td></tr>' ;   
            } 
		    $applicationDispalyInfo .= '</table>';
	    }	 
	    return $applicationDispalyInfo;  
    }
	public function getSMU($applicantID){
		$CheckMissingSMU_Code = 0;
		$appSmu = $this->MysqlHamisDB->GetArray("SELECT smu_code FROM hamis.smu_choices WHERE applicant_id = '$applicantID'");
		foreach($appSmu as $key=>$value){
			if(strlen($value['smu_code']))
			{
				$smuName = $this->MysqlHamisDB->GetOne("SELECT SMU_NAME FROM hamis.smus WHERE smu_code = ".$value['smu_code']);
				if($key == 0){
					$name=$smuName;
				}
				else{
					$name.=','.$smuName;
				}
			}
			else{
				$CheckMissingSMU_Code = 1;
			}
		}
		
		if($CheckMissingSMU_Code == 1)
		{
			$name = " <span style = 'color : red; text-align : center;'><strong>Missing location of your choice,<br>Please Select Location</strong></span>";
		}
		return $name;
	}
	
    public	function RoomApplication($StudentsSessions){
	    $RegistrationNo = $this->CCGetSession("RegNo");
	    $Gender = $this->CCGetSession("gender");
	    $DegreeCode = $this->CCGetSession("DegCode");
	    $LevelofStudy = $this->CCGetSession("LevelofStudy");
	    $UonEmail = $this->CCGetSession("Email");
	    $Surname = $this->CCGetSession("surname");
	    $OtherNames = $this->CCGetSession("otherNames");
	    $MobileNo = $this->CCGetSession("primary_mobile");    
	    $AcademicYear = $this->Academicyear;
	    $StudentCategory =  $this->CCGetSession("StudentCategory"); 
	     
		 if($StudentCategory == '001')
		 {
			 $ApplicationStatus = $this->CheckApplicationStatus($AcademicYear,$DegreeCode,$LevelofStudy);
			
			 $ApplicationIsOpen = is_array($ApplicationStatus)? $ApplicationStatus['STATUS']  : $ApplicationStatus  ;	
			 
			 $OutOfSessionApplicationStatus = $this->checkModuleIIApplicationStatus(9);
			
			 $OutOfSessionApplicationIsOpen = is_array($OutOfSessionApplicationStatus)? $OutOfSessionApplicationStatus['STATUS']  : $ApplicationStatus  ;	
			// $ApplicationIsOpen = 'OPEN';
			 $this->RoomApplication = $ApplicationIsOpen; 
			 $CheckifStudentBooked = $this->studentBookings($StudentsSessions,TRUE) ; 
			 if($this->RoomBookingStatus === TRUE){ 
				 //Check if Booking has taken Place  
				 $DisplayInformation .=  $CheckifStudentBooked ;
			 }else{		 
				 if( $ApplicationIsOpen == 'OPEN'){
				 	
					$RoomAppplicationRecord = $this->getRoomApplicationDetails($RegistrationNo,$AcademicYear); 
					if(is_array($RoomAppplicationRecord) && count($RoomAppplicationRecord)>0){
						if($this->UpdateFormDisplayed === FALSE)
							$DisplayInformation .= $this->DisplayApplicationInformation($RoomAppplicationRecord,$ApplicationStatus)  ;
					}else{
						if($LevelofStudy > 1)
						{
							$DisplayInformation .= $this->RoomApplicationForm($RegistrationNo,$MobileNo,$Gender,$DegreeCode,$LevelofStudy,$UonEmail, $AcademicYear,		 $ApplicationStatus);
							//$DisplayInformation .= $this->out_of_session_application_form($RegistrationNo,$MobileNo,$Gender,$DegreeCode,$LevelofStudy,$UonEmail, $AcademicYear,		 $ApplicationStatus);
						}else
							$DisplayInformation .= $this->RoomApplicationFormFirstYears($RegistrationNo,$MobileNo,$Gender,$DegreeCode,$LevelofStudy,$UonEmail, $AcademicYear,$ApplicationStatus);					
					}
				 } elseif($ApplicationIsOpen == 'CLOSED'){
					 $RoomAppplicationRecord = $this->getRoomApplicationDetails($RegistrationNo,$AcademicYear);
					 if(is_array($RoomAppplicationRecord) && count($RoomAppplicationRecord)>0){
						$DisplayInformation .= $this->DisplayApplicationInformation($RoomAppplicationRecord,$ApplicationStatus)  ;
					 }else{
						  $DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Room Application for ' . $AcademicYear .' Academic Year has been closed <br> If you did not apply within the stipulated timeframe, you will not be consindered for accomodation </span>';    
					}
				}elseif($ApplicationIsOpen == 'PENDING'){	
						$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Room Application for ' . $AcademicYear .' Academic Year for regular students will Starts on ' .		 $ApplicationStatus['START_DATE'] .' to   ' . $ApplicationStatus['CLOSING_DATE'] .' <br> Please login  into the system then to apply for accomodation <br> the are ' .$ApplicationStatus['DAYS_REMAINING'] . ' days Remaining to the start on the process</span>';    
				}elseif($ApplicationIsOpen == 'NO SET DATE' && $StudentCategory =='001'){			
					$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Room Application for ' . $AcademicYear .' Academic Year has not been set yet<br> Kindly consult the Student Welfare Authority for more Details(SWA)</span>';    
				}else{
					$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Consult Student Welfare Authority(SWA) on the information about booking for a room </span>';    
				}
			 }
		 }else{
		 	 $vcategoryId = 9;
			 $ModIIApplicationStatus = $this->checkModuleIIApplicationStatus($vcategoryId);
			 $ModIIApplicationIsOpen = is_array($ModIIApplicationStatus)? $ModIIApplicationStatus['STATUS']  : $ModIIApplicationStatus  ;
			 $this->RoomApplication = $ModIIApplicationIsOpen; 
			 $CheckifStudentBooked = $this->studentBookings($StudentsSessions,TRUE) ; 
			 if($this->RoomBookingStatus === TRUE){ 
				 //Check if Booking has taken Place  
				 $DisplayInformation .=  $CheckifStudentBooked ;
			 }else{		 
				 if( $ModIIApplicationIsOpen == 'OPEN'){
					$RoomAppplicationRecord = $this->getRoomModuleIIApplicationDetails($RegistrationNo,$AcademicYear); 
					//$RoomAppplicationRecord = $this->getRoomApplicationDetails($RegistrationNo,$AcademicYear); 
					if(is_array($RoomAppplicationRecord) && count($RoomAppplicationRecord)>0){
						if($this->UpdateFormDisplayed === FALSE)
						{
							$DisplayInformation .= $this->DisplayModIIApplicationInformation($RoomAppplicationRecord,$ModIIApplicationStatus)  ;
						}
					}else{
						 $DisplayInformation .= $this->moduleIIStudAppForm($RegistrationNo,$MobileNo,$Gender,$DegreeCode,$LevelofStudy,$UonEmail, $AcademicYear,$ModIIApplicationStatus,$vcategoryId);					
					}
				 } elseif($ModIIApplicationIsOpen == 'CLOSED'){
					 $RoomAppplicationRecord = $this->getRoomModuleIIApplicationDetails($RegistrationNo,$AcademicYear);
					 if(is_array($RoomAppplicationRecord) && count($RoomAppplicationRecord)>0){
						$DisplayInformation .= $this->DisplayModIIApplicationInformation($RoomAppplicationRecord,$ModIIApplicationStatus);
					 }else{
						  $DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Room Application for ' . $AcademicYear .' Academic Year has been closed <br> If you did not apply within the stipulated timeframe, you will not be consindered for accomodation </span>';    
					}
				}elseif($ApplicationIsOpen == 'PENDING'){	
						$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Room Application for ' . $AcademicYear .' Academic Year for regular students will Starts on ' .		 $ApplicationStatus['START_DATE'] .' to   ' . $ApplicationStatus['CLOSING_DATE'] .' <br> Please login  into the system then to apply for accomodation <br> the are ' .$ApplicationStatus['DAYS_REMAINING'] . ' days Remaining to the start on the process</span>';    
				}elseif($ApplicationIsOpen == 'NO SET DATE' && $StudentCategory =='001'){			
					$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Room Application for ' . $AcademicYear .' Academic Year has not been set yet<br> Kindly consult the Student Welfare Authority for more Details(SWA)</span>';    
				}else{
					$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Consult Student Welfare Authority(SWA) on the information about booking for a room </span>';    
				}
			 }
		 }
		 
       return $DisplayInformation ;	 
    }

	 public function CheckifErrors($ErrorMsg){
		$ErrorPresence = FALSE;
		if(is_array($ErrorMsg))
		foreach($ErrorMsg as $Errorkey => $ErrorValue){
			if(strlen($ErrorValue)){
				$ErrorPresence = TRUE ;
				break;
			}		
		}	
		return $ErrorPresence ;	
	} 
	
    public function studentBookings($StudentsSessions,$Checking=FALSE){
	    $RegistrationNo = $this->CCGetSession("RegNo");
	    $Gender = $this->CCGetSession("gender");
	    $DegreeCode = $this->CCGetSession("DegCode");
	    $LevelofStudy = $this->CCGetSession("LevelofStudy");
	    $UonEmail = $this->CCGetSession("Email");
	    $Surname = $this->CCGetSession("surname");
	    $OtherNames = $this->CCGetSession("otherNames");
		$StudentCategory =  $this->CCGetSession("StudentCategory"); 
	    $AcademicYear = $this->Academicyear;
	    
		if($StudentCategory == '001')
		{
			 $BookingStatus = $this->CheckBookingStatus($AcademicYear,$DegreeCode,$LevelofStudy); // CheckBookingStatus($AcademicYear,$DegreeCode,$LevelofStudy);
			 
			// print "<h1>" ;
			 //print_r($BookingStatus);
			 //print "</h1>";
			 $BookingIsOpen = is_array($BookingStatus)? $BookingStatus['STATUS']  : $BookingStatus  ;	  
			 $BookedRoom = $this->getBookingDetails($RegistrationNo,$AcademicYear) ; 	     
			 
			 if((is_array($BookingStatus) && $BookingStatus['STATUS'] == 'OPEN') || count($BookedRoom) > 0 ){
				   // Check student Booked Room
				 if($BookedRoom !== FALSE && ( is_array($BookedRoom) && count($BookedRoom)< 1 )) {
					 $StudentInWaitinList = $this->CheckQueue($RegistrationNo,$AcademicYear);  // Check if Student is Waiting for a room
					 if($StudentInWaitinList !== FALSE && ( is_array($StudentInWaitinList) && count($StudentInWaitinList)>0) ){
						 $this->RoomBookingStatus = TRUE;
						 $DisplayInformation .= $this->displayQueueDisplay($StudentInWaitinList)   ;
						 if($StudentInWaitinList['status']=='EXPIRED')
							$DisplayInformation .= $this->StartRoomBookingProcess($RegistrationNo,$AcademicYear,$StudentsSessions,$Gender,$DegreeCode,$LevelofStudy)  ; 	
					 }
					 if($StudentInWaitinList == 'NOT_QUEUED' && $Checking === FALSE)  {
						$DisplayInformation .= $this->StartRoomBookingProcess($RegistrationNo,$AcademicYear,$StudentsSessions,$Gender,$DegreeCode,$LevelofStudy)  ; 
					 }
					 // display the Waiting Information
				 }elseif(is_array($BookedRoom) && ( is_array($BookedRoom) && count($BookedRoom) > 0) ) {
					   $this->RoomBookingStatus = TRUE; 
					   $DisplayInformation .= $this->DisplayBookingInformation($BookedRoom,$BookingStatus); // Display Room Booking Information
					   $BookingCancelled = $this->checkcancelledBooing($BookedRoom)  ;
						//if($BookingCancelled === TRUE)
						   //$DisplayInformation .= $this->StartRoomBookingProcess($RegistrationNo,$AcademicYear,$StudentsSessions,$Gender,$DegreeCode,$LevelofStudy)  ;	
				 }else{ 
					 if($Checking === FALSE)  
						$DisplayInformation .= $this->StartRoomBookingProcess($RegistrationNo,$AcademicYear,$StudentsSessions,$Gender,$DegreeCode,$LevelofStudy)  ;
				 }
			 }elseif($BookingStatus['STATUS'] == 'CLOSED') {
				 $BookedRoom = $this->getBookingDetails($RegistrationNo,$AcademicYear) ;   // Check student Booked Room  
				 if($BookedRoom === FALSE || count($BookedRoom) ==0){
					$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 16px; font-weight: bold; align:center">Room Booking for ' . $AcademicYear . ' academic year has been closed</span>';  
				 }else{
					$DisplayInformation .= $this->DisplayBookingInformation($BookedRoom,$BookingStatus);  // Display Room Booking Information  
				 }
			 }elseif($BookingStatus['STATUS'] == 'NO SET DATE' && $StudentCategory =='001'){
				$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Room Booking for ' . $AcademicYear .' Academic Year has not been set </span>';    
			 }elseif($BookingStatus['STATUS'] == 'WAITING' && $StudentCategory =='001'){
			  $DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Student Welfare Authority(SWA) will communicate the result of your room application on '. $BookingStatus['STARTING_DATE'] .' There are about ' . $BookingStatus['DAYS_REMAINING'] .' to go</span>';       
			 } else{
				$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">For any information partaining room application contact Student Welfare Authority(SWA) </span>';    
			 }
		}
		
		else
		{
			 $categoryID = 9;
			 $BookingStatus = $this->CheckModuleIIBookingStatus($categoryID); // CheckBookingStatus($AcademicYear,$DegreeCode,$LevelofStudy);
			 $BookingIsOpen = is_array($BookingStatus)? $BookingStatus['STATUS']  : $BookingStatus  ;	
			 $BookedRoom = $this->getBookingDetails($RegistrationNo,$AcademicYear) ;
			 if(count($BookedRoom) > 0 ){
				   // Check student Booked Room
				 if($BookedRoom !== FALSE && ( is_array($BookedRoom) && count($BookedRoom)< 1 )) {
					 $StudentInWaitinList = $this->CheckQueue($RegistrationNo,$AcademicYear);  // Check if Student is Waiting for a room
					 if($StudentInWaitinList !== FALSE && ( is_array($StudentInWaitinList) && count($StudentInWaitinList)>0) ){
						 $this->RoomBookingStatus = TRUE;
						 $DisplayInformation .= $this->displayQueueDisplay($StudentInWaitinList)   ;
						 if($StudentInWaitinList['status']=='EXPIRED')
							$DisplayInformation .= $this->StartRoomBookingProcess($RegistrationNo,$AcademicYear,$StudentsSessions,$Gender,$DegreeCode,$LevelofStudy)  ; 	
					 }
					 if($StudentInWaitinList == 'NOT_QUEUED' && $Checking === FALSE)  {
						$DisplayInformation .= $this->StartRoomBookingProcess($RegistrationNo,$AcademicYear,$StudentsSessions,$Gender,$DegreeCode,$LevelofStudy)  ; 
					 }
					 // display the Waiting Information
				 }elseif(( is_array($BookedRoom) && count($BookedRoom) > 0) ) {
					   $this->RoomBookingStatus = TRUE; 
					   $DisplayInformation .= $this->DisplayBookingInformation($BookedRoom,$BookingStatus); // Display Room Booking Information
					   $BookingCancelled = $this->checkcancelledBooing($BookedRoom)  ;
						//if($BookingCancelled === TRUE)
						   //$DisplayInformation .= $this->StartRoomBookingProcess($RegistrationNo,$AcademicYear,$StudentsSessions,$Gender,$DegreeCode,$LevelofStudy)  ;	
				 }else{ 
					 if($Checking === FALSE)  
						$DisplayInformation .= $this->StartRoomBookingProcess($RegistrationNo,$AcademicYear,$StudentsSessions,$Gender,$DegreeCode,$LevelofStudy)  ;
				 }
			 }elseif($BookingStatus['STATUS'] == 'CLOSED') {
				 $BookedRoom = $this->getBookingDetails($RegistrationNo,$AcademicYear) ;   // Check student Booked Room  
				 if($BookedRoom === FALSE || count($BookedRoom) ==0){
					$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 16px; font-weight: bold; align:center">Room Booking for ' . $AcademicYear . ' academic year has been closed</span>';  
				 }else{
					$DisplayInformation .= $this->DisplayBookingInformation($BookedRoom,$BookingStatus);  // Display Room Booking Information  
				 }
			 }elseif($BookingStatus['STATUS'] == 'NO SET DATE' && $StudentCategory =='001'){
				$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Room Booking for ' . $AcademicYear .' Academic Year has not been set </span>';    
			 }elseif($BookingStatus['STATUS'] == 'WAITING' && $StudentCategory =='001'){
			  $DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">Student Welfare Authority(SWA) will communicate the result of your room application on '. $BookingStatus['STARTING_DATE'] .' There are about ' . $BookingStatus['DAYS_REMAINING'] .' to go</span>';       
			 } else{
				$DisplayInformation .= '<span style="color: #FF0066; font-family: "Times New Roman", Times, serif; font-size: 20px; font-weight: bold; align:center">For any information partaining room application contact Student Welfare Authority(SWA) </span>';    
			 }
		}
	     return $DisplayInformation ;
    }


    function getInvoiceTitle (){
	    $AcademicYear = $this->Academicyear;
	    $RegistrationNo = $this->CCGetSession("RegNo");
	    $DisplayName = $this->CCGetSession("surname") . " " .  $this->CCGetSession("otherNames") ;
	    $title = ucwords(strtolower('INVOICE FOR: '. $RegistrationNo . ", ".$DisplayName.", ACADEMIC YEAR - ".$AcademicYear));
	    return $title;
    }

    function GeneratePdf($RegistrationNo,$DisplayName,$AcademicYear,$BookingStatus){
	    $BookeRoomDetails = $this->getBookingDetails($RegistrationNo,$AcademicYear);
	    $textFile = $this->PrintBookingInformation($BookeRoomDetails,$IsBookingOpen);
	    $AcademicYearPrint = str_replace('/','-',$AcademicYear);  
	    $RegistrationNo = str_replace('/','-',$RegistrationNo);
	    $NowDate = date('Ymd');  
	    $TextFile = "invoices/".$AcademicYearPrint."-".$RegistrationNo."-".$NowDate.".txt";
	    $pdf=new PDF();
	    global $title;
	    
	    //$title = ucwords(strtolower('INVOICE FOR: '. $RegistrationNo . ", ".$DisplayName.", ACADEMIC YEAR - ".$AcademicYear));
	    $pdf->SetTitle("meine");
	    //$pdf->Header($title);
	    //Column titles
	    $header=array('Hall','Room No','Semester','Daily Charge','Total Amount');
	    //Data loading
	    $data=$pdf->LoadData($TextFile);
	    
	    //$data=$pdf->LoadData('countries.txt');
	    $pdf->SetFont('Arial','',7);
	    $pdf->AddPage();
      $pdf->Image('invoices/logo.gif',85,2,30);
       $pdf->addSociete( "Student Welfare Authority",
			      "University of Nairobi\n" .
			      "P.O Box 30197, 00100, G.P.O\n".
			      "Nairobi\n" .
			      "Telephone: 318262\n" .
			      "Email: swa@uonbi.ac.ke\n" .
			      "Website: http://www.uonbi.ac.ke "  );
	    $pdf->temporaire( "INVOICE" );
	    $todayis =date('d.m.y');
	    $pdf->addDate($todayis);
	    $pdf->addClient('STUDENT');
	    $pdf->addPageNumber("1");

	    $title = $this->getInvoiceTitle();
	    $pdf->addClientAdresse($title);
	    $pdf->BasicTable($header,$data);
	    $pdf->Output();
    }

    public function PrintBookingInformation($BookedRoomInfo){				
	    if(is_array($BookedRoomInfo)){
		     if(count($BookedRoomInfo)<1)
		       return FALSE;	

		    if(is_array($BookedRoomInfo))
		    
		   
		    $AcademicYear = $this->Academicyear;
		    
		    $RegistrationNo = $this->CCGetSession("RegNo");  
            $RegistrationNo = str_replace('-','/',$RegistrationNo);  
			$Balance = $this->GetSwaBalance($RegistrationNo,$AcademicYear);
            $AcademicYear = str_replace('/','-',$AcademicYear);
		    $RegistrationNo = str_replace('/','-',$RegistrationNo);
		    $NowDate = date('Ymd');  
		    $TextFile = $AcademicYear."-".$RegistrationNo."-".$NowDate.".txt";
		    $fp=fopen("invoices/$TextFile","w+");
		    $RoomCharge = 0;
		    $myemptyvar ="";
		    
		      if( $Balance < 0){
			    $balancetype ="Balance Brought  Forward";
		      }
		      else{
			    $balancetype ="Balance Carried  Forward";
		      }
		    foreach($BookedRoomInfo as $Semeseter => $SemesterBookingInfo){
			    $RoomCharge = $RoomCharge +  $SemesterBookingInfo['invoice_amout'] ;
			    $AmountPayable =   $Balance ;
				if( $AmountPayable < 0)
					 $AmountPayable =0 ;
			    fwrite($fp,ucwords(strtolower($SemesterBookingInfo['hall_name'])).";");
			    fwrite($fp,$SemesterBookingInfo['room_no'].";");
			    fwrite($fp,$SemesterBookingInfo['semester'].";");
			    fwrite($fp,number_format($SemesterBookingInfo['daily_room_charge'],2).";");
			    fwrite($fp, number_format($SemesterBookingInfo['invoice_amout'],2));
			    fwrite($fp,"\n");
		    }
	     
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp,"\n");
		    
		    fwrite($fp,"Total Invoice Amount: ;");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp, number_format($RoomCharge,2));
		    fwrite($fp,"\n");
		    
		    fwrite($fp,$balancetype." ;");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp, number_format($Balance,2));
		    fwrite($fp,"\n");
		    
		    fwrite($fp,"Amount Payable: ;");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp,$myemptyvar.";");
		    fwrite($fp, number_format($AmountPayable,2));
		    fwrite($fp,"\n");

	    }
    }
		    
    } // Class


    // Pdf Genrating Class

    class PDF extends FPDF
    {
    /*
    var $DaysRemaining ;
    var $BookingEndDate ;
    var $BookingStatus;

    function PDF ($BookingStatus){
	    $this->DaysRemaining =  $BookingStatus['DAYS_REMAINING'];
	    $this->BookingEndDate = $BookingStatus['CLOSING_DATE'];
	    $this->BookingStatus = $BookingStatus['STATUS']; 
    }
    */
    function Header()
    {
    //global $title;
    //$HamisOnline = new smis('2009/2010');
     // $title = $HamisOnline->getInvoiceTitle();
	    //$this->SetTitle( $title);

	    $this->SetY(15);
	    //$this->Image('invoices/logo.gif',100,8,25);
	    //Arial bold 15
	    $this->SetFont('Arial','B',9);
	    //Move to the right
	    $this->Cell(80);
	    $this->Cell(30,30,$title ,0,0,'C');
	    //Line break
	    $this->Ln(20);
    }

    //Load data
    function LoadData($file)
    {
	    //Read file lines
	    $lines=file($file);
	    $data=array();
	    foreach($lines as $line)
		    $data[]=explode(';',chop($line));
	    return $data;
    }

    //Simple table
    function BasicTable($header,$data)
    {
	    //Header
	    $this->SetY(59);
	    $this->SetFont('Arial','I',7);
	    foreach($header as $col)
		    $this->Cell(39,7,$col,1);
	    $this->Ln();
	    //Data
	     
	    foreach($data as $row)
	    {
		    foreach($row as $col)
		    $this->Cell(39,5,$col,1);
		    $this->Ln();	
	    }
    }
    //Page footer
    function Footer()
    {
	    //Position at 1.5 cm from bottom
	    $this->SetY(-200);
	    //Arial italic 8
	    //$this->SetFont('Arial','I',10);
	    //Page number
	    $this->Cell(0,10,"EO&E");
	    $this->SetY(-197);
	    //Arial italic 8
	    //$this->SetFont('Arial','I',7);
	    //Page number
	    $this->Cell(0,10,"This invoice is payable to the following Account, Bank: Barclays Bank Of Kenya, Branch : Market Branch, Account Name: Student Welfare Authority, Account No: 8245590");
	    $this->SetY(-193);
	    //$this->SetFont('Arial','I',7);
	    //$this->Cell(0,10,"Kindly confirm this room before the end of the Booking period on " . $this->BookingEndDate  . " You only have ". $this->DaysRemaining ." remaining before you forfeit the room ");
	    
	    }
    // private variables
    var $colonnes;
    var $format;
    var $angle=0;

    // private functions
    function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
    $k = $this->k;
    $hp = $this->h;
    if($style=='F')
	    $op='f';
    elseif($style=='FD' || $style=='DF')
	    $op='B';
    else
	    $op='S';
    $MyArc = 4/3 * (sqrt(2) - 1);
    $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
    $xc = $x+$w-$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

    $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
    $xc = $x+$w-$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
    $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
    $xc = $x+$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
    $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
    $xc = $x+$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
    $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
    $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
    $h = $this->h;
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
					    $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }

    function Rotate($angle, $x=-1, $y=-1)
    {
    if($x==-1)
	    $x=$this->x;
    if($y==-1)
	    $y=$this->y;
    if($this->angle!=0)
	    $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
    {
	    $angle*=M_PI/180;
	    $c=cos($angle);
	    $s=sin($angle);
	    $cx=$x*$this->k;
	    $cy=($this->h-$y)*$this->k;
	    $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
    }
    }

    function _endpage()
    {
    if($this->angle!=0)
    {
	    $this->angle=0;
	    $this->_out('Q');
    }
    parent::_endpage();
    }

    // public functions
    function sizeOfText( $texte, $largeur )
    {
    $index    = 0;
    $nb_lines = 0;
    $loop     = TRUE;
    while ( $loop )
    {
	    $pos = strpos($texte, "\n");
	    if (!$pos)
	    {
		    $loop  = FALSE;
		    $ligne = $texte;
	    }
	    else
	    {
		    $ligne  = substr( $texte, $index, $pos);
		    $texte = substr( $texte, $pos+1 );
	    }
	    $length = floor( $this->GetStringWidth( $ligne ) );
	    $res = 1 + floor( $length / $largeur) ;
	    $nb_lines += $res;
    }
    return $nb_lines;
    }

    // Company
    function addSociete( $nom, $adresse )
    {
    $x1 = 10;
    $y1 = 20;
    //Positionnement en bas
    $this->SetXY( $x1, $y1 );
    $this->SetFont('Arial','B',12);
    $length = $this->GetStringWidth( $nom );
    $this->Cell( $length, 2, $nom);
    $this->SetXY( $x1, $y1 + 4 );
    $this->SetFont('Arial','',10);
    $length = $this->GetStringWidth( $adresse );
    //Coordonn?es de la soci?t?
    $lignes = $this->sizeOfText( $adresse, $length) ;
    $this->MultiCell($length, 4, $adresse);
    }

    // Label and number of invoice/estimate
    function fact_dev( $libelle, $num )
    {
    $r1  = $this->w - 80;
    $r2  = $r1 + 68;
    $y1  = 6;
    $y2  = $y1 + 2;
    $mid = ($r1 + $r2 ) / 2;

    $texte  = $libelle . " EN " . EURO . " N? : " . $num;    
    $szfont = 12;
    $loop   = 0;

    while ( $loop == 0 )
    {
       $this->SetFont( "Arial", "B", $szfont );
       $sz = $this->GetStringWidth( $texte );
       if ( ($r1+$sz) > $r2 )
	      $szfont --;
       else
	      $loop ++;
    }

    $this->SetLineWidth(0.1);
    $this->SetFillColor(192);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
    $this->SetXY( $r1+1, $y1+2);
    $this->Cell($r2-$r1 -1,5, $texte, 0, 0, "C" );
    }

    // Estimate
    function addDevis( $numdev )
    {
    $string = sprintf("DEV%04d",$numdev);
    $this->fact_dev( "Devis", $string );
    }

    // Invoice
    function addFacture( $numfact )
    {
    $string = sprintf("FA%04d",$numfact);
    $this->fact_dev( "Facture", $string );
    }

    function addDate( $date )
    {
    $r1  = $this->w - 61;
    $r2  = $r1 + 30;
    $y1  = 17;
    $y2  = $y1 ;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,5, "DATE", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$date, 0,0, "C");
    }

    function addClient( $ref )
    {
    $r1  = $this->w - 31;
    $r2  = $r1 + 19;
    $y1  = 17;
    $y2  = $y1;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,5, "CLIENT", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$ref, 0,0, "C");
    }

    function addPageNumber( $page )
    {
    $r1  = $this->w - 80;
    $r2  = $r1 + 19;
    $y1  = 17;
    $y2  = $y1;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,5, "PAGE", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$page, 0,0, "C");
    }

    // Client address
    function addClientAdresse( $adresse )
    {
    $r1     = $this->w - 80;
    $r2     = $r1 + 68;
    $y1     = 40;
    $this->SetXY( $r1, $y1);
    $this->MultiCell( 60, 4, $adresse);
    }

    // Mode of payment
    function addReglement( $mode )
    {
    $r1  = 10;
    $r2  = $r1 + 60;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1+1 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,4, "MODE DE REGLEMENT", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1 + 5 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$mode, 0,0, "C");
    }

    // Expiry date
    function addEcheance( $date )
    {
    $r1  = 80;
    $r2  = $r1 + 40;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2 - $r1)/2 - 5 , $y1+1 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,4, "DATE D'ECHEANCE", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5 , $y1 + 5 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$date, 0,0, "C");
    }

    // VAT number
    function addNumTVA($tva)
    {
    $this->SetFont( "Arial", "B", 10);
    $r1  = $this->w - 80;
    $r2  = $r1 + 70;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + 16 , $y1+1 );
    $this->Cell(40, 4, "TVA Intracommunautaire", '', '', "C");
    $this->SetFont( "Arial", "", 10);
    $this->SetXY( $r1 + 16 , $y1+5 );
    $this->Cell(40, 5, $tva, '', '', "C");
    }

    function addReference($ref)
    {
    $this->SetFont( "Arial", "", 10);
    $length = $this->GetStringWidth( "R?f?rences : " . $ref );
    $r1  = 10;
    $r2  = $r1 + $length;
    $y1  = 92;
    $y2  = $y1+5;
    $this->SetXY( $r1 , $y1 );
    $this->Cell($length,4, "R?f?rences : " . $ref);
    }

    function addCols( $tab )
    {
    global $colonnes;

    $r1  = 10;
    $r2  = $this->w - ($r1 * 2) ;
    $y1  = 100;
    $y2  = $this->h - 50 - $y1;
    $this->SetXY( $r1, $y1 );
    $this->Rect( $r1, $y1, $r2, $y2, "D");
    $this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
    $colX = $r1;
    $colonnes = $tab;
    while ( list( $lib, $pos ) = each ($tab) )
    {
	    $this->SetXY( $colX, $y1+2 );
	    $this->Cell( $pos, 1, $lib, 0, 0, "C");
	    $colX += $pos;
	    $this->Line( $colX, $y1, $colX, $y1+$y2);
    }
    }

    function addLineFormat( $tab )
    {
    global $format, $colonnes;

    while ( list( $lib, $pos ) = each ($colonnes) )
    {
	    if ( isset( $tab["$lib"] ) )
		    $format[ $lib ] = $tab["$lib"];
    }
    }

    function lineVert( $tab )
    {
    global $colonnes;

    reset( $colonnes );
    $maxSize=0;
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
	    $texte = $tab[ $lib ];
	    $longCell  = $pos -2;
	    $size = $this->sizeOfText( $texte, $longCell );
	    if ($size > $maxSize)
		    $maxSize = $size;
    }
    return $maxSize;
    }

    // add a line to the invoice/estimate
    /*    $ligne = array( "REFERENCE"    => $prod["ref"],
				      "DESIGNATION"  => $libelle,
				      "QUANTITE"     => sprintf( "%.2F", $prod["qte"]) ,
				      "P.U. HT"      => sprintf( "%.2F", $prod["px_unit"]),
				      "MONTANT H.T." => sprintf ( "%.2F", $prod["qte"] * $prod["px_unit"]) ,
				      "TVA"          => $prod["tva"] );
    */
    function addLine( $ligne, $tab )
    {
    global $colonnes, $format;

    $ordonnee     = 10;
    $maxSize      = $ligne;

    reset( $colonnes );
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
	    $longCell  = $pos -2;
	    $texte     = $tab[ $lib ];
	    $length    = $this->GetStringWidth( $texte );
	    $tailleTexte = $this->sizeOfText( $texte, $length );
	    $formText  = $format[ $lib ];
	    $this->SetXY( $ordonnee, $ligne-1);
	    $this->MultiCell( $longCell, 4 , $texte, 0, $formText);
	    if ( $maxSize < ($this->GetY()  ) )
		    $maxSize = $this->GetY() ;
	    $ordonnee += $pos;
    }
    return ( $maxSize - $ligne );
    }

    function addRemarque($remarque)
    {
    $this->SetFont( "Arial", "", 10);
    $length = $this->GetStringWidth( "Remarque : " . $remarque );
    $r1  = 10;
    $r2  = $r1 + $length;
    $y1  = $this->h - 45.5;
    $y2  = $y1+5;
    $this->SetXY( $r1 , $y1 );
    $this->Cell($length,4, "Remarque : " . $remarque);
    }

    function addCadreTVAs()
    {
    $this->SetFont( "Arial", "B", 8);
    $r1  = 10;
    $r2  = $r1 + 120;
    $y1  = $this->h - 40;
    $y2  = $y1+20;
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $y1+4, $r2, $y1+4);
    $this->Line( $r1+5,  $y1+4, $r1+5, $y2); // avant BASES HT
    $this->Line( $r1+27, $y1, $r1+27, $y2);  // avant REMISE
    $this->Line( $r1+43, $y1, $r1+43, $y2);  // avant MT TVA
    $this->Line( $r1+63, $y1, $r1+63, $y2);  // avant % TVA
    $this->Line( $r1+75, $y1, $r1+75, $y2);  // avant PORT
    $this->Line( $r1+91, $y1, $r1+91, $y2);  // avant TOTAUX
    $this->SetXY( $r1+9, $y1);
    $this->Cell(10,4, "BASES HT");
    $this->SetX( $r1+29 );
    $this->Cell(10,4, "REMISE");
    $this->SetX( $r1+48 );
    $this->Cell(10,4, "MT TVA");
    $this->SetX( $r1+63 );
    $this->Cell(10,4, "% TVA");
    $this->SetX( $r1+78 );
    $this->Cell(10,4, "PORT");
    $this->SetX( $r1+100 );
    $this->Cell(10,4, "TOTAUX");
    $this->SetFont( "Arial", "B", 6);
    $this->SetXY( $r1+93, $y2 - 8 );
    $this->Cell(6,0, "H.T.   :");
    $this->SetXY( $r1+93, $y2 - 3 );
    $this->Cell(6,0, "T.V.A. :");
    }

    function addCadreEurosFrancs()
    {
    $r1  = $this->w - 70;
    $r2  = $r1 + 60;
    $y1  = $this->h - 40;
    $y2  = $y1+20;
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1+20,  $y1, $r1+20, $y2); // avant EUROS
    $this->Line( $r1+20, $y1+4, $r2, $y1+4); // Sous Euros & Francs
    $this->Line( $r1+38,  $y1, $r1+38, $y2); // Entre Euros & Francs
    $this->SetFont( "Arial", "B", 8);
    $this->SetXY( $r1+22, $y1 );
    $this->Cell(15,4, "EUROS", 0, 0, "C");
    $this->SetFont( "Arial", "", 8);
    $this->SetXY( $r1+42, $y1 );
    $this->Cell(15,4, "FRANCS", 0, 0, "C");
    $this->SetFont( "Arial", "B", 6);
    $this->SetXY( $r1, $y1+5 );
    $this->Cell(20,4, "TOTAL TTC", 0, 0, "C");
    $this->SetXY( $r1, $y1+10 );
    $this->Cell(20,4, "ACOMPTE", 0, 0, "C");
    $this->SetXY( $r1, $y1+15 );
    $this->Cell(20,4, "NET A PAYER", 0, 0, "C");

    }

    // remplit les cadres TVA / Totaux et la remarque
    // params  = array( "RemiseGlobale" => [0|1],
    //                      "remise_tva"     => [1|2...],  // {la remise s'applique sur ce code TVA}
    //                      "remise"         => value,     // {montant de la remise}
    //                      "remise_percent" => percent,   // {pourcentage de remise sur ce montant de TVA}
    //                  "FraisPort"     => [0|1],
    //                      "portTTC"        => value,     // montant des frais de ports TTC
    //                                                     // par defaut la TVA = 19.6 %
    //                      "portHT"         => value,     // montant des frais de ports HT
    //                      "portTVA"        => tva_value, // valeur de la TVA a appliquer sur le montant HT
    //                  "AccompteExige" => [0|1],
    //                      "accompte"         => value    // montant de l'acompte (TTC)
    //                      "accompte_percent" => percent  // pourcentage d'acompte (TTC)
    //                  "Remarque" => "texte"              // texte
    // tab_tva = array( "1"       => 19.6,
    //                  "2"       => 5.5, ... );
    // invoice = array( "px_unit" => value,
    //                  "qte"     => qte,
    //                  "tva"     => code_tva );
    function addTVAs( $params, $tab_tva, $invoice )
    {
    $this->SetFont('Arial','',8);

    reset ($invoice);
    $px = array();
    while ( list( $k, $prod) = each( $invoice ) )
    {
	    $tva = $prod["tva"];
	    @ $px[$tva] += $prod["qte"] * $prod["px_unit"];
    }

    $prix     = array();
    $totalHT  = 0;
    $totalTTC = 0;
    $totalTVA = 0;
    $y = 261;
    reset ($px);
    natsort( $px );
    while ( list($code_tva, $articleHT) = each( $px ) )
    {
	    $tva = $tab_tva[$code_tva];
	    $this->SetXY(17, $y);
	    $this->Cell( 19,4, sprintf("%0.2F", $articleHT),'', '','R' );
	    if ( $params["RemiseGlobale"]==1 )
	    {
		    if ( $params["remise_tva"] == $code_tva )
		    {
			    $this->SetXY( 37.5, $y );
			    if ($params["remise"] > 0 )
			    {
				    if ( is_int( $params["remise"] ) )
					    $l_remise = $param["remise"];
				    else
					    $l_remise = sprintf ("%0.2F", $params["remise"]);
				    $this->Cell( 14.5,4, $l_remise, '', '', 'R' );
				    $articleHT -= $params["remise"];
			    }
			    else if ( $params["remise_percent"] > 0 )
			    {
				    $rp = $params["remise_percent"];
				    if ( $rp > 1 )
					    $rp /= 100;
				    $rabais = $articleHT * $rp;
				    $articleHT -= $rabais;
				    if ( is_int($rabais) )
					    $l_remise = $rabais;
				    else
					    $l_remise = sprintf ("%0.2F", $rabais);
				    $this->Cell( 14.5,4, $l_remise, '', '', 'R' );
			    }
			    else
				    $this->Cell( 14.5,4, "ErrorRem", '', '', 'R' );
		    }
	    }
	    $totalHT += $articleHT;
	    $totalTTC += $articleHT * ( 1 + $tva/100 );
	    $tmp_tva = $articleHT * $tva/100;
	    $a_tva[ $code_tva ] = $tmp_tva;
	    $totalTVA += $tmp_tva;
	    $this->SetXY(11, $y);
	    $this->Cell( 5,4, $code_tva);
	    $this->SetXY(53, $y);
	    $this->Cell( 19,4, sprintf("%0.2F",$tmp_tva),'', '' ,'R');
	    $this->SetXY(74, $y);
	    $this->Cell( 10,4, sprintf("%0.2F",$tva) ,'', '', 'R');
	    $y+=4;
    }

    if ( $params["FraisPort"] == 1 )
    {
	    if ( $params["portTTC"] > 0 )
	    {
		    $pTTC = sprintf("%0.2F", $params["portTTC"]);
		    $pHT  = sprintf("%0.2F", $pTTC / 1.196);
		    $pTVA = sprintf("%0.2F", $pHT * 0.196);
		    $this->SetFont('Arial','',6);
		    $this->SetXY(85, 261 );
		    $this->Cell( 6 ,4, "HT : ", '', '', '');
		    $this->SetXY(92, 261 );
		    $this->Cell( 9 ,4, $pHT, '', '', 'R');
		    $this->SetXY(85, 265 );
		    $this->Cell( 6 ,4, "TVA : ", '', '', '');
		    $this->SetXY(92, 265 );
		    $this->Cell( 9 ,4, $pTVA, '', '', 'R');
		    $this->SetXY(85, 269 );
		    $this->Cell( 6 ,4, "TTC : ", '', '', '');
		    $this->SetXY(92, 269 );
		    $this->Cell( 9 ,4, $pTTC, '', '', 'R');
		    $this->SetFont('Arial','',8);
		    $totalHT += $pHT;
		    $totalTVA += $pTVA;
		    $totalTTC += $pTTC;
	    }
	    else if ( $params["portHT"] > 0 )
	    {
		    $pHT  = sprintf("%0.2F", $params["portHT"]);
		    $pTVA = sprintf("%0.2F", $params["portTVA"] * $pHT / 100 );
		    $pTTC = sprintf("%0.2F", $pHT + $pTVA);
		    $this->SetFont('Arial','',6);
		    $this->SetXY(85, 261 );
		    $this->Cell( 6 ,4, "HT : ", '', '', '');
		    $this->SetXY(92, 261 );
		    $this->Cell( 9 ,4, $pHT, '', '', 'R');
		    $this->SetXY(85, 265 );
		    $this->Cell( 6 ,4, "TVA : ", '', '', '');
		    $this->SetXY(92, 265 );
		    $this->Cell( 9 ,4, $pTVA, '', '', 'R');
		    $this->SetXY(85, 269 );
		    $this->Cell( 6 ,4, "TTC : ", '', '', '');
		    $this->SetXY(92, 269 );
		    $this->Cell( 9 ,4, $pTTC, '', '', 'R');
		    $this->SetFont('Arial','',8);
		    $totalHT += $pHT;
		    $totalTVA += $pTVA;
		    $totalTTC += $pTTC;
	    }
    }

    $this->SetXY(114,266.4);
    $this->Cell(15,4, sprintf("%0.2F", $totalHT), '', '', 'R' );
    $this->SetXY(114,271.4);
    $this->Cell(15,4, sprintf("%0.2F", $totalTVA), '', '', 'R' );

    $params["totalHT"] = $totalHT;
    $params["TVA"] = $totalTVA;
    $accompteTTC=0;
    if ( $params["AccompteExige"] == 1 )
    {
	    if ( $params["accompte"] > 0 )
	    {
		    $accompteTTC=sprintf ("%.2F", $params["accompte"]);
		    if ( strlen ($params["Remarque"]) == 0 )
			    $this->addRemarque( "Accompte de $accompteTTC Euros exig? ? la commande.");
		    else
			    $this->addRemarque( $params["Remarque"] );
	    }
	    else if ( $params["accompte_percent"] > 0 )
	    {
		    $percent = $params["accompte_percent"];
		    if ( $percent > 1 )
			    $percent /= 100;
		    $accompteTTC=sprintf("%.2F", $totalTTC * $percent);
		    $percent100 = $percent * 100;
		    if ( strlen ($params["Remarque"]) == 0 )
			    $this->addRemarque( "Accompte de $percent100 % (soit $accompteTTC Euros) exig? ? la commande." );
		    else
			    $this->addRemarque( $params["Remarque"] );
	    }
	    else
		    $this->addRemarque( "Dr?le d'acompte !!! " . $params["Remarque"]);
    }
    else
    {
	    if ( strlen ($params["Remarque"]) > 0 )
		    $this->addRemarque( $params["Remarque"] );
    }
    $re  = $this->w - 50;
    $rf  = $this->w - 29;
    $y1  = $this->h - 40;
    $this->SetFont( "Arial", "", 8);
    $this->SetXY( $re, $y1+5 );
    $this->Cell( 17,4, sprintf("%0.2F", $totalTTC), '', '', 'R');
    $this->SetXY( $re, $y1+10 );
    $this->Cell( 17,4, sprintf("%0.2F", $accompteTTC), '', '', 'R');
    $this->SetXY( $re, $y1+14.8 );
    $this->Cell( 17,4, sprintf("%0.2F", $totalTTC - $accompteTTC), '', '', 'R');
    $this->SetXY( $rf, $y1+5 );
    $this->Cell( 17,4, sprintf("%0.2F", $totalTTC * EURO_VAL), '', '', 'R');
    $this->SetXY( $rf, $y1+10 );
    $this->Cell( 17,4, sprintf("%0.2F", $accompteTTC * EURO_VAL), '', '', 'R');
    $this->SetXY( $rf, $y1+14.8 );
    $this->Cell( 17,4, sprintf("%0.2F", ($totalTTC - $accompteTTC) * EURO_VAL), '', '', 'R');
    }

    // add a watermark (temporary estimate, DUPLICATA...)
    // call this method first
    function temporaire( $texte )
    {
    $this->SetFont('Arial','B',50);
    $this->SetTextColor(203,203,203);
    $this->Rotate(45,55,190);
    $this->Text(55,190,$texte);
    $this->Rotate(0);
    $this->SetTextColor(0,0,0);
    }

    }


    function flatten_array($item2, $key)
    {
    return "$key->$item2<br/>\n";
    }

    function getPreviousacademicYear($AcademicYear){
    list($PreviousYear,$NextYear) = split('[/]',$AcademicYear) ;
    return  ($PreviousYear-1) . '/' . ($NextYear-1) ;
    }
?>