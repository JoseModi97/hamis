<?
	if($_SERVER["HTTP_HOST"] == '10.2.21.87' || $_SERVER["HTTP_HOST"]=='smis.uonbi.ac.ke'){
		define('SMIS_RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT']);
		define('RELATIVE_PATH', SMIS_RELATIVE_PATH  . '/hamis');
	}else {
		 define('SMIS_RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/smis');
		 define('RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT']  . '/smis/hamis');
	}
	//$HamisOnline->CCSetSession('smisMenuGroup','swa');
	$_SESSION['smisMenuGroup'] = 'swa';	 
	include_once(SMIS_RELATIVE_PATH .  '/includes/smis_header_common.php');
	

	define('FPDF_FONTPATH', RELATIVE_PATH .'/font/');
	require(RELATIVE_PATH . '/fpdf.php');	
	include RELATIVE_PATH . '/smis_class_inc.php' ;
	
	$Loggedin = $HamisOnline->CheckLoggon();
	$StudentsSessions = $HamisOnline->CCGetSession("StudentSessions");
	
	
	$RegistrationNo = $HamisOnline->CCGetSession("RegNo");  
    $Username =  $HamisOnline->CCGetSession("Username")  ;
	if(($Loggedin && !is_array($StudentsSessions)) || $RegistrationNo != $Username ){
		//CCSetSession('regNo',$regNo);
		//CCSetSession('smisPass',$smisPass);
		$Password  = $HamisOnline->CCGetSession("Password")?$HamisOnline->CCGetSession("Password"):$HamisOnline->CCGetSession("smisPass") ; 
		$Username =  $HamisOnline->CCGetSession("Username")?$HamisOnline->CCGetSession("regNo") : $HamisOnline->CCGetSession("regNo")  ; 
		$Result = $HamisOnline->authenticate($Username,$Password);   
		$StudentsSessions = $HamisOnline->CCGetSession("StudentSessions");   
	}elseif(!$Loggedin){
		 $Redirect = 'http://' . $_SERVER["HTTP_HOST"] . '/smis/index.php ';
		 @header("Location: $Redirect");
		 exit;
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
	
	if($LevelofStudy == 0  && $StudentCategory =='001'){
		$YearofGradaution = $HamisOnline->CheckGraduands($RegistrationNo );
		if(strlen($YearofGradaution))
			$HamisOnline->SetError($HamisOnline->GetError() . "<br>You cannot Apply to be accomodated into the halls of residence because the Nominal roll records indicate you have already finished your programme,for more information contact your Faculty/School or Institute  ");       
		else	
			$HamisOnline->SetError($HamisOnline->GetError() . "<br>Your cannot book a room because your Level of Study for the Academic Year $AcademicYear cannot be determined, for more information contact your Faculty/School or Institute ");
	}elseif(!is_array($StudentsSessions) && $StudentCategory =='001')
		$HamisOnline->SetError($HamisOnline->GetError() . "<br> The students academic sessions information for the academic year $AcademicYear, Degree Programme = $DegreeCode is not set,for more information contact the Student Welfare Authority(SWA) ");
	elseif($StudentCategory != '001')
		$DisplayInformation .= '<font size="+1" color="blue">Only Module I students are allowed to book for room using this Online reservation system at the Moment,for more information contact the Student Welfare Authority(SWA)<br></font>';
	
    if($StudentCategory == '001'){	
	     $DisplayInformation .= $HamisOnline->ManageBookingResponse($RegistrationNo,$AcademicYear,$StudentSessions,$BookingIsOpen,$BookingStatus); 
	       
	     $DisplayInformation .= $HamisOnline->RoomApplication($StudentsSessions);
	 }
	 //if($HamisOnline->RoomApplication=='CLOSED' || $HamisOnline->StudentApplicationStatus=== TRUE)
	  //$DisplayInformation .= $HamisOnline->studentBookings($StudentsSessions);
	 
	 
	
	/*Check Student Booking Status - Has Booked a room Awaiting Confirmation
								   - Has Already Confrimed 
								   - Has Expired
								   - Is waiting a room
								   - Notified for a room Student was waiting for - Display the room Available and seek the Student Acceptable
								   - Student does not have a room - Bookign is open 
																 - Booking is Closed
	*/
	include_once(SMIS_RELATIVE_PATH .  '/includes/smis_header.php'); 	
?>
	<div id="left">
	<div class="left_articles">
	<?php 
	if ($Loggedin) {
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
    <div align="center"> For any correspondence on online room application write to <a href="mailto:hamis@uonbi.ac.ke">hamis@uonbi.ac.ke<a><br /> <br /><p align="center"><a href="terms_conditions.pdf" target="_blank">SWA Room Accomodation Terms and Conditions</p></a></div>
	</div>
</div>
<?
	include_once(SMIS_RELATIVE_PATH .  '/includes/smis_footer.php');  
?>
