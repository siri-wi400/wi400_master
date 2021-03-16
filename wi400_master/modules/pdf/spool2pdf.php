<?php
// ******************************************************************************
//	creazione di un pdf partendo dallo spool ricevuto
//
// 	i parametri sono ricevuti in quest'ordine:
// 	$argv[1]	nome utente
//  $argv[2]    Identificativo ID record del file con i parametri di conversione
//
// ------------------------------------------------------------------------------
// Includo configurazione per script che girano in modalità batch
require_once "/Siri/config/config_batch.php";

// Per evitare problemi di conversione con grossi spool setto la memoria a 20 megabyte massimi
ini_set("memory_limit","20M");
// controllo del debug
if($debug) {
	error_reporting(E_ALL);
	ini_set("display_errors", true);
}

require_once $routine_path."/FPDF/fpdi.php";

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
$ID = trim(substr(@$argv[2],0,10));
//   Imposto un default per il test
if ($ID=="") $ID = 'P000000006';
// Recupero i i parametri dell'ID della conversione da effettuare
$sql="select * from FPDFCONV WHERE MAIREC='$ID'";
$result = $db->query($sql);
$idrec= $db->fetch_array($result);

if (!$idrec) aggiorna_log($ID, '1', '002', "Informazioni Id $ID non trovate");
// Recupero i parametri del modulo da utilizzare.
$codice_modulo = $idrec['MAIMOD'];
// Se non hanno valorizzato il codice modulo lo imposto con il codice argomento.
if (trim($codice_modulo)=="") $codice_modulo = $idrec['MAIARG'];

$modulo = cercaModulo($codice_modulo);	

// Lettura dello spool filizzato
$sql="select * from $idrec[MAIFIL]";
$result = $db->query($sql);
// Apertura oggetto pdf
$nome = "";
if (trim($idrec['MAINAM'])=="") $nome = trim($idrec['MAIARG']).date("d")."-".date("m")."-".date("Y")."-".date("H")."_".date("i")."_".date("s");
else $nome = trim($idrec['MAINAM']);
// Creazione directory da base path + utente
$utente = $idrec['MAIUSR'];
$pathpdf=$modulo['MODPDP'];
if (trim($utente) == "") $utente="GENERICO";
$pathpdf = $pathpdf.trim($utente)."/";
if (!file_exists($pathpdf)) $risultato = wi400_mkdir($pathpdf);
$pdf_out=$pathpdf.$nome.'.pdf';

$pdf=new FPDI($modulo['MODPPL'],trim($modulo['MODUMI']),trim($modulo['MODPFO']));
$pdf->SetFont(trim($modulo['MODFNA']),'',$modulo['MODFAL']);
$pdf->SetRightMargin(0);
$pdf->SetAutoPageBreak(False);
$pdf->SetCreator('WI400-Siri Informatica s.r.l.');
$pdf->SetAuthor($settings['cliente_installazione']);
$pdf->SetSubject($modulo['MODDES']);

$salto=False;
$count=1;
$pagina = 1;
$old_skip = 9999;
$riga = number_format($modulo['MODRIN']);
$colonna = number_format($modulo['MODCIN']);

//ciclo tutti record
while ($row=$db->fetch_array($result, Null, False)) {
	
        // Valorizzo i dati dello spool
	$skip = substr($row[0],0,3);
	$space  = substr($row[0],3,1);
	$dati  = trim(substr($row[0],4));
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

	$pdf->SetFont(trim($modulo['MODFNA']),'',$modulo['MODFAL']);
	$pdf->SetXY($colonna,$riga);
	$pdf->Write($modulo['MODFAC'], $dati);
	// ripristino del font
	
}
$pdf->Output($pdf_out, 'F');
echo $pdf_out;

// Gestione salto pagina
function salto_pagina()
{
	global $salto, $line, $count, $dim_font, $template_fatt, $stdfont, $row, $pagina, $pdf, $modulo;
	
	$count=$count + 1;
	
	if ($salto)
	{
	// aggiunge pagina
	$pdf->AddPage();
	// Aggiungo il template se definito
	if ($modulo['MODPNA']!="")
	{
	$pdf->setSourceFile(trim($modulo['MODPPA'])."/".trim($modulo['MODPNA']));
	$tplIdx = $pdf->importPage(1);
	$pdf->useTemplate($tplIdx,0,0);	
	}
	// ripristino del font
	$pdf->SetFont(trim($modulo['MODFNA']),'',$modulo['MODFAL']);
	$salto=False;	
	$count = 1;
	$pagina = $pagina + 1;
    }
}
?>

