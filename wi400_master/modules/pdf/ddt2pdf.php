<?php
// ******************************************************************************
//	Creazione di un PDF partendo da uno spool filizzato con *PRTCTL	
//
//      Versione 1.00 Beta Cipac
//      Autore   Luca Zovi
//
// 	I parametri sono ricevuti in quest'ordine:
// 	$argv[1]	ID Spool
//
// ----------------------------------------------------------------------------

// Define variable to prevent hacking
define('IN_CB',true);
require_once('ddtconfig.php');
require_once "$varie_path/phpmailer/class.phpmailer.php";
require_once $_SERVER['DOCUMENT_ROOT']."/WI400_SVIL/Routine/Generali/init_server_settings.php";
require_once "$routine_path/Generali/common.php";
require_once "$routine_path/DB/".$settings['database'].".class.php";
require_once "$routine_path/Generali/init_siri.php";

// Per evitare problemi di conversione con grossi spool setto la memoria a 20 megabyte massimi
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
$ID = trim(substr(@$argv[1],0,10));
//   Imposto un default per il test
if ($ID=="") $ID = 'P000000005';

//$i5conn = db2_connect($host, $user, $pass, $options);
$i5conn = db2_connect($host, $user, $pass);

if(!$i5conn) {
	error("Connessione non riuscita: " . db2_conn_errormsg(),$pdf_out);
	aggiorna_log($ID, '1', '001', db2_conn_errormsg());
}

// Per richiamo di RCARBODY
    $description = array(
	array("Name"=>"ARGOMENTO", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"THEBODY", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2000")
	);

// Recupero i i parametri dell'ID della conversione da effettuare
$sql="select * from $db_lib.FPDFCONV WHERE MAIREC='$ID'";
$result = db2_exec($i5conn,$sql);
$idrec=db2_fetch_assoc($result);

if (!$idrec) aggiorna_log($ID, '1', '002', "Informazioni Id $ID non trovate");
// Recupero i parametri del modulo da utilizzare.
$codice_modulo = $idrec['MAIMOD'];
// Se non hanno valorizzato il codice modulo lo imposto con il codice argomento.
if (trim($codice_modulo)=="") $codice_modulo = $idrec['MAIARG'];
$sql="select * from EURFIL.CPC_MODULI WHERE MODNAM='$codice_modulo'";
$result = db2_exec($i5conn,$sql);
$modulo=db2_fetch_assoc($result);
// Se non trovo provo con il default, altrimenti lascio che esploda tutto
if(!$modulo)
{
$codice_modulo = "*DEFAULT";
$sql="select * from EURFIL.CPC_MODULI WHERE MODNAM='$codice_modulo'";
$result = db2_exec($i5conn,$sql);
$modulo=db2_fetch_assoc($result);	
}	
// Lettura dello spool filizzato
$sql="select * from $db_lib.$idrec[MAIFIL]";
$result = db2_exec($i5conn,$sql);
if (!$result) aggiorna_log($ID, '1', '003', "File $idrec[MAIFIL] non trovato");
// Apertura oggetto pdf
$nome = "";
if (trim($idrec['MAINAM'])=="") $nome = trim($idrec['MAIARG']).date("d")."-".date("m")."-".date("Y")."-".date("H")."_".date("i")."_".date("s");
else $nome = trim($idrec['MAINAM']);
// Creazione directory da base path + utente
$utente = $idrec['MAIUSR'];
if (trim($utente) == "") $utente="GENERICO";
$pathpdf = $pathpdf.trim($utente)."/";
if (!file_exists($pathpdf)) $risultato = wi400_mkdir($pathpdf);
$pdf_out=$pathpdf.$nome.'.pdf';
//$pdf_out_zip=$pathpdf.$nome.".zip";
$pdf_out_2=$nome.'.pdf';
$pdf_out_zip=$nome.".zip";


$pdf=new FPDI($modulo['MODPPL'],trim($modulo['MODUMI']),trim($modulo['MODPFO']));
$pdf->SetFont(trim($modulo['MODFNA']),'',$modulo['MODFAL']);
$pdf->SetRightMargin(0);
$pdf->SetAutoPageBreak(False);
$pdf->SetCreator('WI400-Siri Informatica s.r.l.');
$pdf->SetAuthor('Sistemi Informativi Al.Fi');
$pdf->SetSubject($modulo['MODDES']);
$pdf->AddFont('ean8', '', 'ean8.php');

// Impostazione variabili iniziali
$salto=False;
$count=1;
$pagina = 1;
$old_skip = 9999;
$riga = number_format($modulo['MODRIN']);
//ciclo tutti record
while ($row=db2_fetch_assoc($result)) {
	
        // Valorizzo i dati dello spool
	$skip = substr($row['RECORD'],0,3);
	$space  = substr($row['RECORD'],3,1);
	$dati  = substr($row['RECORD'],4);

        // Controllo il salto pagina da SKIP
	if ((trim($skip) <> "") and ($skip <= $old_skip))  {
	$salto = True;	
	salto_pagina();		
	$riga = number_format($modulo['MODRIN']);
	}	
         // Calcolo dove mi devo posizionare con la riga di stampa
        if (trim($skip) <> "")  {
        $old_skip = $skip;
        $riga = ($modulo['MODIAL'] * $skip) + $modulo['MODRIN'];
        }
        else {
          $riga = $riga + ($modulo['MODIAL'] * $space);	
        }	
	// stampa riga Dettaglio articolo
        
	$new_wline= $modulo['MODCIN'];;
	$pdf->SetXY($new_wline,$riga);
	//$pdf->Write($modulo['MODFAC'], $dati);
	$artico = substr($dati, 0 , 7);
        if (trim(substr($codice_modulo,0 , 3))=="LGO")
        {
        // Se sono in presenza della riga articolo
        $altro = True;
        if (strlen(trim($artico))==7 and is_numeric($artico))
        {
	       $pdf->SetFont(trim($modulo['MODFNA']),'B',$modulo['MODFAL']);
        $pdf->Write($modulo['MODFAC'], substr($dati,0,64));
	       $pdf->SetFont(trim($modulo['MODFNA']),'',$modulo['MODFAL']);
	       $pdf->SetXY(98,$riga);
        $pdf->Write($modulo['MODFAC'], substr($dati,64,100));
	// Generazione barcode con nuovo metodo.
	$pdf->SetFont('ean8','',22);
	$myean8 = ean8_decode(substr($artico,1,6).'0');
	$pdf->SetXY(276,$riga);
        $pdf->Write($modulo['MODFAC'], $myean8);
	// Ripristino font
	$pdf->SetFont(trim($modulo['MODFNA']),'',$modulo['MODFAL']);
	// FINE MODIFICHE NUOVO BARCODE
	//$nome = createbar($artico);
        $altro = False;
        }
        // Se sono in presenza della riga Famiglia
        if (substr($dati,0,8)=='FAMIGLIA')
        {
	       $pdf->SetFont(trim($modulo['MODFNA']),'U',$modulo['MODFAL']);
        $pdf->Write($modulo['MODFAC'], trim($dati));
        $altro = False;
        }
        // Se sono in presenza della riga Sottofamiglia
        if (substr($dati,0,13)=='Sottofamiglia')
        {
	       $pdf->SetFont(trim($modulo['MODFNA']),'I',$modulo['MODFAL']);
        $pdf->Write($modulo['MODFAC'], trim($dati));
        $altro = False;
        }
        if($altro) $pdf->Write($modulo['MODFAC'], $dati);
        }
 else $pdf->Write($modulo['MODFAC'], $dati);
	// ripristino del font
	$pdf->SetFont(trim($modulo['MODFNA']),'',$modulo['MODFAL']);

	
}
// Generazione PDF
$pdf->Output($pdf_out, 'F');
if ($idrec['MAIZIP']=='S')
{
copy($pdf_out, $pdf_out_2);
compress($pdf_out_2, $pdf_out_zip);
}

// Invio della mail
if ($idrec['MAIEMA']=="S")
{
$mail = new PHPMailer();
$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host = $mail_host;
$mail->From = $idrec['MAIFRM'];
$mail->FromName = "Sistemi Informativi CIPAC";
// Controllo se sono in presenza di una lista distribuzione
$distr=substr($idrec['MAITOR'],0,6);
if (strtoupper($distr)=="DISTR:")
{
	if ($idrec['MAIAMB'] =='P') $db_lib = $fil_produzione;
	else $file = $fil_test;
	// Cerco le caratteristiche della lista di distribuzione
	$lista = substr($idrec['MAITOR'],6,10);
	$sql="select * from $file.FTABFAXL WHERE TFAXSG='FAXL' AND TFAXCD='$lista'";
	$result = db2_exec($i5conn,$sql);
	$tabella=db2_fetch_assoc($result);
	if (!$tabella) aggiorna_log($ID, '1', '005', "Lista $lista non trovata");
        $sql="select * from $file.F298MAIL WHERE FAXCDL='".trim($lista)."'";
	$result = db2_exec($i5conn,$sql);	
	while ($email=db2_fetch_assoc($result)) {
		if ($email['FAXST1']=='E' AND $email['FAXSTA']=='1')
		{
		if ($tabella['TFAXS1']!='S') $mail->AddAddress($email['FAXEMA']);
		else
		{
			if ($email['FAXS01']=='T') $mail->AddAddress($email['FAXEMA']);
			if ($email['FAXS01']=='C') $mail->AddCC($email['FAXEMA']);
		}	 	
		}	
        }		
}
else $mail->AddAddress($idrec['MAITOR']);

$mail->Subject = $idrec['MAISBJ'];
// Reperisco il body
    //$connzend = i5_connect("10.26.2.2" ,"QPGMR", "QQW");
    $pgm = i5_program_prepare("SIRIPHP/RCARBODY", $description);
    if (!$pgm) die("<br>Program prepare errno=".i5_errno()." msg=".i5_errormsg()); 
	// Impostazione parametri ed esecuizione del programma
	$parameter = array(
	"ARGOMENTO"=>$idrec['MAIARG'],
	"THEBODY"=>" "
	);
	$parmOut = array(
	"ARGOMENTO"=>"ARGOMENTO",
	"THEBODY"=>"THEBODY"
	);
        $ret = i5_program_call($pgm, $parameter, $parmOut);
      
$mail->Body = trim($THEBODY);
$mail->WordWrap = 50;
// Allego allegato normale o compresso
if ($idrec['MAIZIP']=='S')
$mail->AddAttachment($pdf_out_zip);
else $mail->AddAttachment($pdf_out);
 
if(!$mail->Send())
{
	aggiorna_log($ID, '1', '004', $mail->ErrorInfo);
}
else
{
	aggiorna_log($ID, '1', '000', "Mail inviata con successo.");
}  
}  
aggiorna_log($ID, '1', '000', "Conversione effettuata con successo.");
// Rimozione dei file creati e generati temporanemente
if ($idrec['MAIZIP']=='S')
{
unlink('/'.$pdf_out_2);
unlink('/'.$pdf_out_zip);
}
else
{
unlink($pdf_out);
}  
  
// Chiusura connessione con il DB
db2_close($i5conn);
// Gestione salto pagina
function salto_pagina()
{
	global $salto, $line, $count, $dim_font, $template_fatt, $stdfont, $row, $pagina, $pdf, $modulo;
	
	$count=$count + 1;
	
	if ($salto)
	{
	// aggiunge pagina
	$pdf->AddPage();
	// set the sourcefile
	//$pdf->setSourceFile($template_fatt);
	$pdf->setSourceFile(trim($modulo['MODPDP']).trim($modulo['MODPDN']));
	// import page 1
	$tplIdx = $pdf->importPage(1);
	$pdf->useTemplate($tplIdx,0,0);	
	// ripristino del font
	$pdf->SetFont(trim($modulo['MODFNA']),'',$modulo['MODFAL']);
	$salto=False;	
	$count = 1;
	$pagina = $pagina + 1;
    }
}
// Aggiornamento del log
function aggiorna_log($ID, $stato, $err, $deserr)
{
	global $i5conn, $idrec, $db_lib, $pdf_out;
	
        $risp = $idrec['MAIRIS'] + 1;
	$sql="UPDATE $db_lib.FPDFCONV SET MAISTA ='$stato', MAIERR = '$err', MAIDER = '$deserr', MAIRIS =".$risp.", MAIELA = '"
	.date("Y")."-".date("m")."-".date("d")."-".date("H").".".date("i").".".date("s").".00000'".
	", MAIATC='".trim($pdf_out)."'WHERE MAIREC='$ID'";
	$result = db2_exec($i5conn,$sql);
	exit($err." ".$deserr);
}
function rtvArtEan($articolo, $datval)
{
 $datavalidita = $datval;

$description = array(
array("Name"=>"CODICE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"7"),
array("Name"=>"DATINV", "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"8.0"),
array("Name"=>"TRACCIATO", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"100"),
array("Name"=>"NREL", "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"9.0"),
array("Name"=>"FLAG", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"));
$codice = str_split($articolo);
// Mi preparo ad eseguire il programma
$pgm = i5_program_prepare("*LIBL/RTLEA3", $description);
// Impostazione parametri ed esecuizione del programma
$parameter = array(
"CODICE"=>$articolo,
"DATINV"=>$datavalidita,
"TRACCIATO"=>" ",
"NREL"=>0.0,
"FLAG"=>"0"
);
$parmOut = array(
"CODICE"=>"CODICE",
"DATINV"=>"DATINV",
"TRACCIATO"=>"TRACCIATO",
"NREL"=>"NREL",
"FLAG"=>"FLAG"
);
$ret = i5_program_call($pgm, $parameter, $parmOut);

$descrizione =  substr($TRACCIATO, 13, 13); 
return $descrizione;
}  

function createbar($artico)
        {
       	global $pdf, $riga;
       	static $count;
       	
       	require_once("barcode.inc.php");
        $count = $count + 1;
        $ean = substr($artico, 1,6)."0";
        $bar= new BARCODE();
        $bar->setSymblogy('EAN-8');
	$bar->setHeight(19);
	$bar->setScale(1);
	$bar->setFont('Courier.ttf');
	$name = 'temporary'.$count;
	$bar->setHexColor("#000000","#FFFFFF");
        $return = $bar->genBarCode($ean,'jpg',$name);
        $pdf->Image($name.'.jpg', 270, $riga );
        unlink($name.'.jpg');
        
        }
function compress( $srcFileName, $dstFileName )
{

    include("zip.lib.php");
    $ziper = new zipfile();
    $ziper->addFiles(array($srcFileName));  //array of files
    $ziper->output($dstFileName); 
}        

        
?>

