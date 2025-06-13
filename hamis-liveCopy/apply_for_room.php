<?php
	define('SMIS_RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/smis');
	include_once(SMIS_RELATIVE_PATH .  '/includes/smis_header_common.php');
	define('RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/hamis');
	define('FPDF_FONTPATH', RELATIVE_PATH .'/font/');
	require(RELATIVE_PATH . '/fpdf.php');	
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
	$mobileno = $HamisOnline->CCGetSession("primary_mobile");
	$Gender = $HamisOnline->CCGetSession("gender");
	$DegreeCode = $HamisOnline->CCGetSession("DegCode");
	$LevelofStudy = $HamisOnline->CCGetSession("LevelofStudy");
	$UonEmail = $HamisOnline->CCGetSession("Email");
	$Surname = $HamisOnline->CCGetSession("surname");
	$OtherNames = $HamisOnline->CCGetSession("otherNames");
	$AcademicYear = $HamisOnline->Academicyear;
	$StudentCategory =  $HamisOnline->CCGetSession("StudentCategory"); 
	 
include_once(SMIS_RELATIVE_PATH .  '/includes/smis_header.php'); 	
?>
<style>
form{margin:0;padding:0 0 10px 0; border:1px dashed #ccc}fieldset{margin:1em 0;border:none;border-top:1px solid #ccc;}legend{margin:1em 0;padding:0 .5em;color:#036;background:transparent;font-size:1.3em;font-weight:bold;}label{float:left;width:100px;padding:0 1em;text-align:right;}fieldset div{margin-bottom:.5em;padding:0;display:block;}fieldset div input,fieldset div textarea{width:150px;border-top:1px solid #555;border-left:1px solid #555;border-bottom:1px solid #ccc;border-right:1px solid #ccc;padding:1px;color:#333;}fieldset div select{padding:1px;}div.fm-multi div{margin:5px 0;}div.fm-multi input{width:1em;}div.fm-multi label{display:block;width:200px;padding-left:5em;text-align:left;}#fm-submit{clear:both;padding-top:1em;text-align:center;}#fm-submit input{border:1px solid #333;padding:2px 1em;background:#555;color:#fff;font-size:100%;}input:focus,textarea:focus{background:#efefef;color:#000;}fieldset div.fm-req{font-weight:bold;}fieldset div.fm-req label:before{content:"* ";}body{padding:0;margin:20px;color:#333;background:#fff;font:12px arial,verdana,sans-serif;text-align:center;}#container{margin:0 auto;padding:1em;width:350px;text-align:left;}p#fm-intro{margin:0;}div input.disabled {width:150px;border-top:0px solid #fff;border-left:0px solid #fff;border-bottom:0px solid #fff;border-right:0px solid #fff;padding:2px;color:#333;}
</style>
	<div id="left">
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
	<div class="left_article">
	<?php
     $HamisOnline->RoomApplicationForm ($RegistrationNo,$mobileno,$Gender,$DegreeCode,$LevelofStudy,$UonEmail, $AcademicYear);
   ?>
	</div>
</div>
<?php
	include_once(SMIS_RELATIVE_PATH .  '/includes/smis_footer.php');  
?>
