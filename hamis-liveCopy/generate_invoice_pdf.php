<?php
	define('SMIS_RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/smis');
	//define font for FPDF to work
	define('FPDF_FONTPATH', $_SERVER['DOCUMENT_ROOT'] .'/hamis/font/');
	include_once(SMIS_RELATIVE_PATH .  '/includes/smis_header_common.php');
	define('RELATIVE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/hamis');
	
	include RELATIVE_PATH . '/smis_class_inc.php' ;
	$Loggedin = $HamisOnline->CheckLoggon();
	$AcademicYear = $HamisOnline->Academicyear;
	$AcademicYearPrint = str_replace('/','-',$AcademicYear); 
	$RegistrationNo = $HamisOnline->CCGetSession("RegNo"); 

	$BookeRoomDetails = $HamisOnline->getBookingDetails($RegistrationNo,$AcademicYear);
	$textFile = $HamisOnline->PrintBookingInformation($BookeRoomDetails,$IsBookingOpen); 
	require(RELATIVE_PATH . '/fpdf.php');
	
	$RegistrationNo = $HamisOnline->CCGetSession("RegNo"); 
	$surname = $HamisOnline->CCGetSession("surname"); 
	$othernames = $HamisOnline->CCGetSession("otherNames"); 
	$DisplayName = $surname." ".$othernames;
	$RegistrationNo = str_replace('/','-',$RegistrationNo);
	$NowDate = date('Ymd');  
	$TextFile = "invoices/".$AcademicYearPrint."-".$RegistrationNo."-".$NowDate.".txt";
	//$TextFile = "invoices/".$RegistrationNo."-".$NowDate.".txt";

class PDF extends FPDF
{
	function Header()
	{
		//Logo
		global $title;
		$this->SetY(15);
		$this->Image('invoices/logo.gif',100,8,25);
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
		$this->SetY(-15);
		//Arial italic 8
		$this->SetFont('Arial','I',8);
		//Page number
		$this->Cell(0,10,'Printed On '.date('l jS \of F Y h:i:s A').'',0,0,'C');
	}
}

function GeneratePdf($RegistrationNo,$DisplayName,$AcademicYear){
	$pdf=new PDF();
	$title=ucwords(strtolower('INVOICE FOR: '. $RegistrationNo . ", ".$DisplayName.", ACADEMIC YEAR - ".$AcademicYear));
	$pdf->SetTitle($title);
	//Column titles
	$header=array('Hall','Room No','Semester','Daily Charge','Total Amount');
	//Data loading
	$data=$pdf->LoadData($TextFile);
	
	//$data=$pdf->LoadData('countries.txt');
	$pdf->SetFont('Arial','',7);
	$pdf->AddPage();
	$pdf->BasicTable($header,$data);
	//$pdf->AddPage();
	//$pdf->ImprovedTable($header,$data);
	//$pdf->AddPage();
	//$pdf->FancyTable($header,$data);
	$pdf->Output();
}
?>