<?php
	if($_SERVER["HTTP_HOST"] == 'localhost' || $_SERVER["HTTP_HOST"]=='smis.uonbi.ac.ke'){
		//define('SMIS_RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT'] .'/smis');
		define('SMIS_RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT']);
		define('RELATIVE_PATH', SMIS_RELATIVE_PATH  . '/hamis');
	}else {
		 define('SMIS_RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/smis');
		 //define('RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT']  . '/smis/hamis');
		 define('RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT']  . '/hamis');
	}
		 
	include_once(SMIS_RELATIVE_PATH .  '/includes/smis_header_common.php');
	

	define('FPDF_FONTPATH', RELATIVE_PATH .'/font/');
	require(RELATIVE_PATH . '/fpdf.php');	
	//include RELATIVE_PATH . '/smis_class_inc.php' ;
	

	include RELATIVE_PATH . '/smis_class_inc.php' ;
	
	$Loggedin = $HamisOnline->CheckLoggon();
	$StudentsSessions = $HamisOnline->CCGetSession("StudentSessions");
	

	if($Loggedin && !is_array($StudentsSessions))
	{
		//CCSetSession('regNo',$regNo);
		//CCSetSession('smisPass',$smisPass);
		$Password  = $HamisOnline->CCGetSession("Password")?$HamisOnline->CCGetSession("Password"):$HamisOnline->CCGetSession("smisPass") ; 
		$Username =  $HamisOnline->CCGetSession("Username")?$HamisOnline->CCGetSession("regNo") : $HamisOnline->CCGetSession("regNo")  ; 
		$Result = $HamisOnline->authenticate($Username,$Password);   
		$StudentsSessions = $HamisOnline->CCGetSession("StudentSessions");   
	}elseif(!$Loggedin){
		 $Redirect = 'http://' . $_SERVER["HTTP_HOST"] . '/smis/index.php ';
	//	 @header("Location: $Redirect");
		// exit;
	}
	
	$RegistrationNo = $HamisOnline->CCGetSession("RegNo");
	$Gender = $HamisOnline->CCGetSession("gender");
	$DegreeCode = $HamisOnline->CCGetSession("DegCode");
	$LevelofStudy = $HamisOnline->CCGetSession("LevelofStudy");
	
	
	
	$UonEmail = $HamisOnline->CCGetSession("Email");
	$Surname = $HamisOnline->CCGetSession("surname");
	$OtherNames = $HamisOnline->CCGetSession("otherNames");
	$AcademicYear = $HamisOnline->Academicyear;
	$StudentCategory =  $HamisOnline->CCGetSession("StudentCategory"); 
	$ApplicationSession = $HamisOnline->CCGetParam("session");
	if(strlen($HamisOnline->CCGetParam("session")))
		$_SESSION['session'] = $HamisOnline->CCGetParam("session");
	if($HamisOnline->CCGetParam("home")){
		unset($_SESSION['session']);
		unset($_SESSION['categoryID']);
	}
	
	if($LevelofStudy == 0  && $StudentCategory =='001'){
		$YearofGradaution = $HamisOnline->CheckGraduands($RegistrationNo );
		if(strlen($YearofGradaution))
			$HamisOnline->SetError($HamisOnline->GetError() . "<br>You cannot Apply to be accommodated into the halls of residence because the Nominal roll records indicate you have already finished your programme,for more information contact your Faculty/School or Institute  ");       
		else	
			$HamisOnline->SetError($HamisOnline->GetError() . "<br>Your cannot book a room because your Level of Study for the Academic Year $AcademicYear cannot be determined, for more information contact your Faculty/School or Institute ");
	}elseif(!is_array($StudentsSessions) && $StudentCategory =='001')
		$HamisOnline->SetError($HamisOnline->GetError() . "<br> The students academic sessions information for the academic year $AcademicYear, Degree Programme = $DegreeCode is not set,for more information contact the Student Welfare Authority(SWA) ");
	/*elseif($StudentCategory != '001')
		$DisplayInformation .= '<font size="+1" color="blue">Only Module I students are allowed to book for room using this Online reservation system at the Moment,for more information contact the Student Welfare Authority(SWA)<br></font>';*/
	
		 if($_SESSION['session']=='next'){
			
			 if($HamisOnline->checkGraduatedLeveofStudy($RegistrationNo,$LevelofStudy)===TRUE){
				 if($StudentCategory == '001') {
				 	 if($HamisOnline->CCGetParam("ConfirmRoom") == 'CANCEL'){
					 	 $DisplayInformation .= $HamisOnline->confirmUserLoginForm();
					 }else{
						 $DisplayInformation .= $HamisOnline->ManageBookingResponse($RegistrationNo,$AcademicYear,$StudentSessions,$BookingIsOpen,$BookingStatus);  
						 
						 $DisplayInformation .= $HamisOnline->RoomApplication($StudentsSessions);
						 if($HamisOnline->RoomApplication=='CLOSED' || $HamisOnline->StudentApplicationStatus=== TRUE)
						 {
							//$DisplayInformation .= $HamisOnline->studentBookings($StudentsSessions);
						 }
					 }
				 }
				 else{
					 $DisplayInformation .= '<font size="+1" color="blue">Only Module I students are allowed to book for room, for more information contact the Student Welfare Authority(SWA)<br></font>';
				 }
			}else
				$DisplayInformation .= '<font size="+1" color="blue">You cannot Apply for Accomodation, you have already Graduated.<br></font>';
			//unset($_SESSION['session']);
		}
		 if($_SESSION['session']=='in'){
		 $graduated=$HamisOnline->CheckGraduands($RegistrationNo);//changed the lower line
		 //if(!strlen($HamisOnline->CheckGraduands($RegistrationNo))){ 
		    //if(!strlen($graduated)){
			if($HamisOnline->checkGraduatedLeveofStudy($RegistrationNo,$LevelofStudy)===TRUE){
			
				 if($StudentCategory == '001') {
				 	 if($HamisOnline->CCGetParam("ConfirmRoom") == 'CANCEL'){
					 	$DisplayInformation .= $HamisOnline->confirmUserLoginForm();
					 }else{
						 $DisplayInformation .= $HamisOnline->ManageBookingResponse($RegistrationNo,$AcademicYear,$StudentSessions,$BookingIsOpen,$BookingStatus);    
						 $DisplayInformation .= $HamisOnline->RoomApplication($StudentsSessions);
						 if($HamisOnline->RoomApplication=='CLOSED' || $HamisOnline->StudentApplicationStatus=== TRUE)
						 {
							//$DisplayInformation .= $HamisOnline->studentBookings($StudentsSessions);
						 }
					 }
				 }
				 else{
					 $DisplayInformation .= '<font size="+1" color="blue">Only Module I students are allowed to book for room, for more information contact the Student Welfare Authority(SWA)<br></font>';
				 }
			}else
				$DisplayInformation .= '<font size="+1" color="blue">You cannot Apply for Accomodation, you have already Graduated.<br></font>';
			//unset($_SESSION['session']);
		}
		if($_SESSION['session']=='modII')
		 {
			 if($HamisOnline->checkGraduatedLeveofStudy($RegistrationNo,$LevelofStudy)===TRUE){
				 if($StudentCategory != '001')
				 {
					 $catID =  $HamisOnline->getSpecialStudentCategoryID($RegistrationNo,$AcademicYear);					
					 $HamisOnline->CCSetSession("categoryID", $catID);
					 $DisplayInformation .= $HamisOnline->ManageBookingResponse($RegistrationNo,$AcademicYear,$StudentSessions,$BookingIsOpen,$BookingStatus); 
					   
					 $DisplayInformation .= $HamisOnline->RoomApplication($StudentsSessions);
					 //$HamisOnline->RoomApplication;
					 if($HamisOnline->RoomApplication=='CLOSED' || $HamisOnline->StudentApplicationStatus=== TRUE)
					 {
						//$DisplayInformation .= $HamisOnline->studentBookings($StudentsSessions);
					}
				}
				else{
					 $DisplayInformation .= '<font size="+1" color="blue">Only Module II students are allowed to book for room, for more information contact the Student Welfare Authority(SWA)<br></font>';
				 }
			}else
				$DisplayInformation .= '<font size="+1" color="blue">You cannot Apply for Accomodation, you have already Graduated.<br></font>';
			
			//unset($_SESSION['session']);
		}
		 if($_SESSION['session']=='out')
		 {
			if($HamisOnline->checkGraduatedLeveofStudy($RegistrationNo,$LevelofStudy)===TRUE){ 
				 if($StudentCategory == '001')
				 {
				 	 $HamisOnline->CCSetSession("categoryID", 10);
					 $DisplayInformation .= $HamisOnline->ManageOutOfSessionBookingResponse($RegistrationNo,$AcademicYear,$StudentSessions,$BookingIsOpen,$BookingStatus); 
					  
					  $DisplayInformation .= $HamisOnline->OutOfSessionRoomApplication($StudentsSessions);
					 if($HamisOnline->RoomApplication=='CLOSED' || $HamisOnline->StudentApplicationStatus=== TRUE)
					 {
						//$DisplayInformation .= $HamisOnline->studentBookings($StudentsSessions);
					 }
				 }
				 else{
					 $DisplayInformation .= '<font size="+1" color="blue">Only Module I students are allowed to book for room during out of session period,for more information contact the Student Welfare Authority(SWA)<br></font>';
				 }
			 }else
				$DisplayInformation .= '<font size="+1" color="blue">You cannot Apply for Accomodation, you have already Graduated.<br></font>';
		

		 
		//unset($_SESSION['session']);
		}
	
	/*Check Student Booking Status - Has Booked a room Awaiting Confirmation
								   - Has Already Confrimed 
								   - Has Expired
								   - Is waiting a room
								   - Notified for a room Student was waiting for - Display the room Available and seek the Student Acceptable
								   - Student does not have a room - Bookign is open 
																 - Booking is Closed
	*/
	include_once(SMIS_RELATIVE_PATH .  '/includes/smis_header.php'); 	
	echo '<script language="JavaScript" src="javascriptdunctions.js"></script>';
	echo '<script language="JavaScript" src="DatePicker.js"></script>
			<script language="JavaScript">
				//Date Picker Object Definitions @1-60492070
				var StartDateObj = new Object(); 
				
				StartDateObj.format           = "dd-MMM-yyyy";
				StartDateObj.style            = "/hamis/Themes/Python/Style.css";
				StartDateObj.relativePathPart = "/hamis/";
				
				var DateObj = new Object(); 
				DateObj.format           = "dd-MMM-yyyy";
				DateObj.style            = "/hamis/Themes/Python/Style.css";
				DateObj.relativePathPart = "/hamis/";
			</script>';
	echo '<style type="text/css">
			.asteriks{
			color:#FF0000;
			}
			.alignImage{
			vertical-align:middle;
			}
		</style>'
	
	
?>
	<div id="left">
	<div class="left_articles">
	<?php 
	//echo $LevelofStudy;
	
	if ($Loggedin) {
		if(strlen($_SESSION['session'])==0)
		{
			 echo '<fieldset align = "center" style = "margin-left:10px; width: 95%; color:blue; font-weight:bold;"><legend>Room Application and Confirmation Links</legend>';
			 echo '<table align = "center"><caption>Click to Select</caption><ul>';
			 //echo $LevelofStudy;
			  //print $AcademicYear;
			 // exit;
			  
			 if(($LevelofStudy < 1) && $StudentCategory == '001'){
				 
				// print $AcademicYear;
				 
			 	echo '<tr><td><li><a href=?session=next&AcademicYear=2016/2017>Regular Student Room Application and Confirmation for Academic Year <font color = "red">2016/2017</font></a></td></li></tr>';
				//echo '<tr><td><li><a href=?session=next&AcademicYear=2012/2013>Regular Student Room Application and Confirmation for Academic Year <font color = "red">2012/2013 </font></a></td></li></tr>';
			 }
			 if($StudentCategory == '001' && $LevelofStudy >= 1){
				  //print $AcademicYear;
			  //exit;
				 echo '<tr><td><li><a href=?session=&AcademicYear=2016/2017>Regular Student Room Application and Confirmation for Academic Year <font color = "red">2016/2017 </font></a></td></li></tr>';
				 echo '<tr><td><li><a href=?session=inAcademicYear=2015/2016>Regular Student Room Application and Confirmation for current Academic Year</a></td></li></tr>';
				 echo '<tr><td><li><a href=?session=out>Vacational Room Application and Confirmation for current Academic Year</a></li></td></tr>';
			}
			if($StudentCategory != '001'){
			 	echo '<tr><td><li><a href=?session=modII&AcademicYear=2011/2011>Module II Room Application and Confirmation</a></li></td></tr>';
			}
			 echo '</ul></table>';
			 echo '</fieldset>';
		}
		else{
			echo '<table align = "center">';
			echo '<tr><td><a href=?home=yes ><font size="+1" color="red">Click to go back to menu</font></a></td></tr>';
			echo '</table>';
		}
		echo  $DisplayInformation ;
	} else{ 
		echo printLogonDetails();
	}
	if($HamisOnline->Debug === TRUE){
		print '<h4 color="red">  ';
		print $HamisOnline->ErrorMessages();
		print "</h4>";
	}
	?>
	</div>
</div>
<?	
	function get_client_ip() {
		$ipaddress = '';
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(!empty($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(!empty($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(!empty($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(!empty($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
	 
		return $ipaddress;
	}
	include_once(SMIS_RELATIVE_PATH .  '/includes/smis_footer.php');  
?>
