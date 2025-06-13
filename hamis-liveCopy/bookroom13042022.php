<?
	if($_SERVER["HTTP_HOST"] == '10.2.21.87' || $_SERVER["HTTP_HOST"]=='smis.uonbi.ac.ke'){
		define('SMIS_RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT']);
		define('RELATIVE_PATH', SMIS_RELATIVE_PATH  . '/hamis');
	}else {
		 define('SMIS_RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/smis');
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
	/*elseif($StudentCategory != '001')
		$DisplayInformation .= '<font size="+1" color="blue">Only Module I students are allowed to book for room using this Online reservation system at the Moment,for more information contact the Student Welfare Authority(SWA)<br></font>';*/
		
	 $DisplayInformation .= $HamisOnline->ManageBookingResponse($RegistrationNo,$AcademicYear,$StudentSessions,$BookingIsOpen,$BookingStatus); 
	   
	 $DisplayInformation .= $HamisOnline->RoomApplication($StudentsSessions);
	 if($HamisOnline->RoomApplication=='CLOSED' || $HamisOnline->StudentApplicationStatus=== TRUE)
	 {
	 	//$DisplayInformation .= $HamisOnline->studentBookings($StudentsSessions);
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
	<script language="javascript">

		function smurightbutton_OnClick()
		{
			var result;
			
		//End page_CHECK_VALIDITY_rightbutton_OnClick
		
		//Custom Code @48-2A29BDB7
			// -------------------------
		   var Allsmu=document.getElementById("vallsmu");
		   var selectsmu=document.getElementById("vselectedsmu");
		   for (var i=0; i < Allsmu.length; i++) 
		   {
						if (Allsmu.options[i].selected == true) 
						{   
								   selectsmu.options[selectsmu.length] = new Option(Allsmu.options[i].text, Allsmu.options[i].value); 
								   Allsmu.options[i] = null;
								   i=i-1;
						}
				}
			// -------------------------
		//End Custom Code
		
		//Close page_CHECK_VALIDITY_rightbutton_OnClick @16-BC33A33A
			return result;
		}
		
		function smuleftbutton_OnClick()
		{
		  var result;
		//End page_CHECK_VALIDITY_leftbutton_OnClick
		
		//Custom Code @49-2A29BDB7
			// -------------------------
				var Allsmu=document.getElementById("vallsmu");
				var selectsmu=document.getElementById("vselectedsmu");
				for (var i=0; i < selectsmu.length; i++) 
				{
					if (selectsmu.options[i].selected == true) 
					{
						Allsmu.options[Allsmu.length] =  new Option(selectsmu.options[i].text, selectsmu.options[i].value);
						selectsmu.options[i] = null;
						i=i-1;
					}
				}
				return result;
		}
		
		
		function page_OnSubmit()
		{
			 var result;
			 var selectsmu = document.getElementById("vselectedsmu");
			
			 //window.document.ApplicationForm.submitted.value=1;
				 var selectsmu = document.getElementById("vselectedsmu");
				 document.ApplicationForm.PickSmus.value="";
				 
				for (i=0; i < selectsmu.options.length; i++) 
				 {
						 if (document.ApplicationForm.PickSmus.value!= "") 
							{
									document.ApplicationForm.PickSmus.value = document.ApplicationForm.PickSmus.value + ",";
									
							}
					 document.ApplicationForm.PickSmus.value = document.ApplicationForm.PickSmus.value + selectsmu.options[i].value;
				 }	
			// -------------------------
				result = true;
		//End Custom Code
		
		//Close page_CHECK_VALIDITY_OnSubmit @9-BC33A33A
			return result;
		}
		
		
		</script>
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
	</div>
</div>
<?
	include_once(SMIS_RELATIVE_PATH .  '/includes/smis_footer.php');  
?>
