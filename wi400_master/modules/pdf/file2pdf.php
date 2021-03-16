<?php
// ******************************************************************************
//	creazione di un pdf partendo dallo spool ricevuto
//
// 	i parametri sono ricevuti in quest'ordine:
// 	$argv[1]	nome membro spool
// 	$argv[2]	todo
// 	$argv[3]	todo
// 	$argv[4]	todo
//	$argv[5]	todo
//
// ----------------------------------------------------------------------------
// Define variable to prevent hacking
define('IN_CB',true);
//require_once('db2_force/DB2400_force_db2.php');
require_once('config.php');
require_once("phpmailer/class.phpmailer.php");

ini_set("memory_limit","20M");
// controllo del debug
if($debug) {
	error_reporting(E_ALL);
	ini_set("display_errors", true);
}

require_once('fpdi.php');

function error ($case,$path)
{
	$pdf=new FPDF();
	$pdf->SetFont('Courier', '', 7);
	$pdf->AddPage();
	$pdf->Write(2,$case);
	$pdf->Output($path,'F');
	die();
}

//   Parametri
//$mbr = trim(substr(@$argv[1],0,10));

//adesso il nome del membro è fisso
//if ($mbr =="" or !$mbr)
$mbr='FRICPHP';
//creazione del nome
$pdf_out=$pathpdf.'example.pdf';

$i5conn = db2_connect($host, $user, $pass, $options);

if(!$i5conn) {
	error("Connessione non riuscita: " . db2_conn_errormsg(),$pdf_out);
}
// crea alias
//$result = db2_exec($i5conn,"CREATE ALIAS $mbr FOR fricphp($mbr)");
$sql="select * from $db_lib.$mbr";
$result = db2_exec($i5conn,$sql);

// apertura oggetto pdf
//$pdf=new FPDI('L',$um,array($feed_w,$feed_h));
$pdf=new FPDI('L',$um,'A4');
$pdf->SetFont($stdfont,'',$dim_font);
//$pdf->SetAutoPageBreak(true,0);
$pdf->SetRightMargin(0);
$pdf->AddFont('3of9', '', '3of9.php');

$pdf->Write($hchar,trim($mbr));

$salto=True;
$count=1;
$salvaset = "";
$salvafam = "";
$salvasub = "";
$integra = "";
$pagina = 1;

//ciclo tutti record
while ($row=db2_fetch_assoc($result)) {
	
    // Verifico se cambia settore per il salto pagina	
	if (!$salto)
	{	
    if ($row['RICSET'] !=$salvaset)
    $salto = True;
	}
	$salvaset = $row['RICSET'];

	
    // Stampa descrizione famiglia 
	if ($row['RICFAM'] != $salvafam) {
	salto_pagina(&$pdf);
	// stampa riga Dettaglio articolo
	$new_wline=$wline;
	$pdf->SetXY($new_wline,$line+=$hline);
	$pdf->Write($hchar,trim($row['RICFAM']. " " .$row['RICDFA']));
	$integra = "";
	}
	$salvafam = $row['RICFAM'];
    // Stampa descrizione sottofamiglia 
	if ($row['RICSUB'] !=$salvasub) {
	salto_pagina(&$pdf);
    // stampa riga Dettaglio articolo
	$new_wline=$wline;
	$pdf->SetXY($new_wline,$line+=$hline);
	$pdf->Write($hchar,trim($row['RICSUB']. " " .$row['RICDSO']));
	}
	$salvasub = $row['RICSUB'];
	// Stampa descrizione tipo assortimento
	if ($row['RICTMD'] =="I" and $integra="") {
	salto_pagina(&$pdf);
	$new_wline=$wline;
	$pdf->SetXY($new_wline,$line+=$hline);
	$pdf->Write($hchar,trim("Assortimento Integrativo/Promo:"));		
	}
	// Nuovo Scaffale
	if ($row['RICROT'] =="S") {	
	salto_pagina(&$pdf);
	$new_wline=$wline;
	$pdf->SetXY($new_wline,$line+=$hline);
	$pdf->Write($hchar,trim("**** NUOVO SCAFFALE ****"));	
	}	
	if ($row['RICROT'] =="F") {	
	salto_pagina(&$pdf);
	$new_wline=$wline;
	$pdf->SetXY($new_wline,$line+=$hline);
	$pdf->Write($hchar,trim("**** FUORI SCAFFALE ****"));	
	}	
	// Controllo se salto pagina
	salto_pagina(&$pdf);
	// stampa riga Dettaglio articolo
	$new_wline=$wline;
	$pdf->SetXY($new_wline,$line+=$hline);
	$pdf->SetFont('Courier','',6);
	$pdf->Write($hchar,trim($row['RICDSA']." ".$row['RICCON']. " ". $row['RICTGR']. " ". number_format($row['RICGRA'])));// descrizione
    // ripristino del font
	$pdf->SetFont($stdfont,'',$dim_font);
	$new_wline+=60;
	$pdf->SetX($new_wline);
	$pdf->Write($hchar,trim($row['RICUMA']));//unità misura
	// - 3
	$new_wline+=3;
	$pdf->SetXY($new_wline+5,$line-1.7);
	$pdf->SetFontSize('6');
	if($row['RIC3RQ']>0) 
	//$pdf->Write($hchar,sprintf('%2.2f',trim($row['RIC3RQ'])));// qtà rich.
	$pdf->Cell(2, 4,sprintf('%2.2f',trim($row['RIC3RQ']),0,0,"R"));// qtà rifor. Utilizzo di cell
	if($row['RIC3CQ']>0) {
		$pdf->SetX($new_wline+15);
		//$pdf->Write($hchar,sprintf('%2.2f',trim($row['RIC3CQ'])));// qtà rifor. Utilizzo di cell
		$pdf->Cell(2, 4,sprintf('%2.2f',trim($row['RIC3CQ']),0,0,"R"));// qtà rifor. Utilizzo di cell
		
	}
	$pdf->SetXY($new_wline+=2,$line+0.8);
	if($row['RIC3RD']>0)
	$pdf->Write($hchar,substr($row['RIC3RD'],6,2)."/".substr($row['RIC3RD'],4,2)."/".substr($row['RIC3RD'],2,2));//data rich
	if($row['RIC3CD']>0) {
		$pdf->SetX($new_wline+10);
		$pdf->Write($hchar,substr($row['RIC3CD'],6,2)."/".substr($row['RIC3CD'],4,2)."/".substr($row['RIC3CD'],2,2));//data rifor.
	}
	// ripristino del font
	$pdf->SetFont($stdfont,'',$dim_font);

	// -2
	$new_wline+=18;
	$pdf->SetXY($new_wline+5,$line-1.5);
	$pdf->SetFontSize('6');
	if($row['RIC2RQ']>0)
	//$pdf->Write($hchar,sprintf('%2.2f',trim($row['RIC2RQ'])));// qtà rich
	$pdf->Cell(2, 4,sprintf('%2.2f',trim($row['RIC2RQ']),0,0,"R"));// qtà rifor. Utilizzo di cell
	if($row['RIC2CQ']>0) {
		$pdf->SetX($new_wline+15);
		//$pdf->Write($hchar,sprintf('%2.2f',trim($row['RIC2CQ'])));// qtà rifor.
		$pdf->Cell(2, 4,sprintf('%2.2f',trim($row['RIC2CQ']),0,0,"R"));// qtà rifor. Utilizzo di cell
	}
	$pdf->SetXY($new_wline+=2,$line+0.8);
	if($row['RIC2RD']>0)
	$pdf->Write($hchar,substr($row['RIC2RD'],6,2)."/".substr($row['RIC2RD'],4,2)."/".substr($row['RIC2RD'],2,2));//data rich.
	if($row['RIC2CD']>0) {
		$pdf->SetX($new_wline+10);
		$pdf->Write($hchar,substr($row['RIC2CD'],6,2)."/".substr($row['RIC2CD'],4,2)."/".substr($row['RIC2CD'],2,2));//data rifor.
	}
	// ripristino del font
	$pdf->SetFont($stdfont,'',$dim_font);

    // -1
	$new_wline+=19;
	$pdf->SetXY($new_wline+5,$line-1.5);
	$pdf->SetFontSize('6');
	if($row['RIC1RQ']>0)
	//$pdf->Write($hchar,sprintf('%2.2f',trim($row['RIC1RQ'])));// qtà rich.
	$pdf->Cell(2, 4,sprintf('%2.2f',trim($row['RIC1RQ']),0,0,"R"));// qtà rifor. Utilizzo di cell
	if($row['RIC1CQ']>0) {
		$pdf->SetX($new_wline+15);
		//$pdf->Write($hchar,sprintf('%2.2f',trim($row['RIC1CQ'])));// qtà rifor.
		$pdf->Cell(2, 4,sprintf('%2.2f',trim($row['RIC1CQ']),0,0,"R"));// qtà rifor. Utilizzo di cell
	}	
	$pdf->SetXY($new_wline+=2,$line+0.8);
	if($row['RIC1RD']>0)
	$pdf->Write($hchar,substr($row['RIC1RD'],6,2)."/".substr($row['RIC1RD'],4,2)."/".substr($row['RIC1RD'],2,2));//data
	if($row['RIC1CD']>0) {
		$pdf->SetX($new_wline+10);
		$pdf->Write($hchar,substr($row['RIC1CD'],6,2)."/".substr($row['RIC1CD'],4,2)."/".substr($row['RIC1CD'],2,2));//data rifor.
	}
	// ripristino del font
	$pdf->SetFont($stdfont,'',$dim_font);

	$new_wline+=55;
	$pdf->SetXY($new_wline,$line-2);
	$pdf->SetFont('arial','B',12);
	$pdf->Write(6,$row['RICCDA']);// codice articolo
	// ripristino del font
	$pdf->SetFont($stdfont,'',$dim_font);

	$new_wline+=20;
	$pdf->SetXY($new_wline,$line-2);
	//$pdf->EAN13($new_wline,$line-7,$row['RICCDA']);// barcode

	$pdf->SetFont('3of9', '', 24);
	//$pdf->Cell($new_wline,$line-7, $row['RICCDA']);
	$pdf->Cell(0,6, " ".$row['RICCDA']." ");

	// ripristino del font
	$pdf->SetFont($stdfont,'',$dim_font);

	// Vend PZ
	$new_wline+=38;
	$pdf->SetX($new_wline);
	//$pdf->Write($hchar,trim($row['RICVPZ'])); Utilizzo di CELL
	$pdf->Cell(2,6,trim($row['RICVPZ']),0,0,"R");

	// Max Sped.
	$new_wline+=15;
	$pdf->SetX($new_wline);
	//$pdf->Write($hchar,trim($row['RICMAX']));  ** Utilizzo di cell per centrare il campo
	$pdf->Cell(2,6,$row['RICMAX'],0,0,"R");
	
	
    // Quantità cartone + Ean Quantità cartone
	$pdf->SetFont($stdfont,'',$dim_font);

	$new_wline+=12;
	$pdf->SetXY($new_wline,$line-2);
	$pdf->SetFont('arial','B',8);
	//$pdf->Write(6,$row['RICPEZ']);// Utilizzo CELL
    $pdf->Cell(2, 6,$row['RICPEZ'],0,0,"R");// RICPEZ Utilizzo di cell	
	// ripristino del font
	$pdf->SetFont($stdfont,'',$dim_font);

	$new_wline+=3;
	$pdf->SetXY($new_wline,$line-2);
	//$pdf->EAN13($new_wline,$line-7,$row['RICCDA']);// barcode

	$pdf->SetFont('3of9', '', 24);
	//$pdf->Cell($new_wline,$line-7, $row['RICCDA']);
	$alfa = $row['RICPEZ'];
	if (strlen($alfa)==1) $alfa = "0000000".$alfa;
	if (strlen($alfa)==2) $alfa = "000000".$alfa;
	if (strlen($alfa)==3) $alfa = "00000".$alfa;
	if (strlen($alfa)==4) $alfa = "0000".$alfa;

	$pdf->Cell(0,6, $alfa);

	// ripristino del font
	$pdf->SetFont($stdfont,'',$dim_font);
	
}
db2_close($i5conn);
$pdf->Output($pdf_out, 'F');

/* // Invio della mail
$mail = new PHPMailer();
$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host = "10.10.1.107 ";
$mail->From = "sistemiinformativi@autogrill.net";
$mail->FromName = "Sistemi Informativi";
$mail->AddAddress("luca.zovi@siri-informatica.it");

$mail->Subject = "Invio fascicolo richieste";
$mail->Body = "Gentile Negozio\ninviamo in allegato il fascicolo richieste in formato PDF.";
$mail->WordWrap = 50;
$mail->AddAttachment($pdf_out);
if(!$mail->Send())
{
	echo 'Message was not sent.';
	echo 'Mailer error: ' . $mail->ErrorInfo;
}
else
{
	echo 'Message has been sent.';
}  */


// Gestione salto pagina
function salto_pagina($pdf)
{
	global $salto, $line, $count, $dim_font, $template_fatt, $stdfont, $row, $pagina;
	
	$count=$count + 1;

	if ($pagina == 1)
	{
			$pdf->AddPage();
			$pdf->SetXY(80 , 75);
	        $pdf->SetFont('arial','B',22);
	        $pdf->Write(22,$row['RICCDE']." ".$row['RICDSE']); 
	}
	
	
	
	if ($salto or $count==20)
	{
	// aggiunge pagina
	$pdf->AddPage();
	// set the sourcefile
	$pdf->setSourceFile($template_fatt);
	// import page 1
	$tplIdx = $pdf->importPage(1);
	// use the imported page and place it
	//$pdf->useTemplate($tplIdx,0,0 ,$feed_w,$feed_h);
	$pdf->useTemplate($tplIdx,0,0);	
    // Intestazione del tabulato con codice e descrizione settore merceologico
	$pdf->SetXY(10 ,6);
	$pdf->SetFont('arial','B',10);
	$pdf->Write(6,$row['RICCDE']." ".$row['RICDSE']. " FASCICOLO RICHIESTE RIFORNIMENTO IN VIGORE DAL ".substr($row['RICDAT'], 6, 2)."/".substr($row['RICDAT'], 4, 2)."/".substr($row['RICDAT'], 0, 2));
	$pdf->SetXY(270 , 5);
	$pdf->Cell(2, 6,$pagina,0,0,"R");// RICPEZ Utilizzo di cell	
    $pdf->SetXY(30 ,12);
	$pdf->SetFont('arial','B',12);
	$pdf->Write(6,$row['RICSET']." ".$row['RICDST']);
	// ripristino del font
	$pdf->SetFont($stdfont,'',$dim_font);
	$salto=False;	
	$line = 35;
	$count = 1;
	$pagina = $pagina + 1;
    }
}
?>
