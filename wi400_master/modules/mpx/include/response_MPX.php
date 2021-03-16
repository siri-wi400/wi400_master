<?php

/*
 * Response di prova per eseguire test dello script per l'invio file ad MPX
 * e decodifica del file PDF inviato per controllo errori di codifica 
 */

$ID = trim($_GET['ID']);

//$myAppBase = "/WI400_VPORRAZZO/";

$mpx_pdf_path = $moduli_path."/mpx/include/";

//include_once "conf/CONV_include.php";
//include_once $conf_path."/CONV_include.php";

/*
 * Variabile per la simulazione di una response
 * TRUE simula l'arrivo corretto di una response
 * FALSE simula l'arrivo di un messaggio di errore (la response vera e propria non è arrivata)
 */
$check = TRUE;

/*
 * Parametri per la response di prova
 * Quando $GlobalCodeR, $SetCodeR, $PdfCodeR, $EnvelopeCodeR sono tutti settati a '0', la response è positiva,
 * altrimenti indica che c'è stato qualche errore 
 * (guardare il manuale di MPX per ulteriori informazioni sui codici di errore nella response)
 */
$GlobalCodeR = '0';
$SetIDR = '-343';
$SetCodeR = '0';
$PdfIDR = '-1';
$PdfCodeR = '0';
$EnvelopeIDR = '-1';
$EnvelopeCodeR = '0';

/* Creazione del documento DOM per la generazione dell'XML da inviare ad MPX */
$dom = new DomDocument('1.0');
/*
 * Non si possono avere più di un elemento di 1° livello
 */
/* Creazione del tag MPX di 1° livello*/
$mpx = $dom->appendChild($dom->createElement('MPX'));
/* Creazione del tag Header di 2° livello con attributi*/
$header = $mpx->appendChild($dom->createElement('Header'));
$field_name = $dom->createAttribute('GlobalCode'); 
$header->appendChild($field_name);
$name = $dom->createTextNode($GlobalCodeR);
$field_name->appendChild($name);
/* Creazione del tag Logs di 2° livello */
$logs = $mpx->appendChild($dom->createElement('Logs'));
/* Creazione dei tag Log di 3° livello */
$log = $logs->appendChild($dom->createElement('Log'));
$log = $logs->appendChild($dom->createElement('Log'));
/* Creazione del tag Set di 2° livello con attributi */
$set = $mpx->appendChild($dom->createElement('Set'));
$field_name = $dom->createAttribute('ID'); 
$set->appendChild($field_name);
$name = $dom->createTextNode($SetIDR);
$field_name->appendChild($name);
$field_name = $dom->createAttribute('SetCode'); 
$set->appendChild($field_name);
$name = $dom->createTextNode($SetCodeR);
$field_name->appendChild($name);
$field_name = $dom->createAttribute('CustomerSetID'); 
$set->appendChild($field_name);
$name = $dom->createTextNode($ID);
$field_name->appendChild($name);
/* Creazione del tag PDF di 3° livello con attributi */
$pdf = $set->appendChild($dom->createElement('Pdf'));
$field_name = $dom->createAttribute('ID'); 
$pdf->appendChild($field_name);
$name = $dom->createTextNode($PdfIDR);
$field_name->appendChild($name);
$field_name = $dom->createAttribute('PdfCode'); 
$pdf->appendChild($field_name);
$name = $dom->createTextNode($PdfCodeR);
$field_name->appendChild($name);
/* Attributo EnvelopeCode in livello PDF in caso di errore*/
/*
$field_name = $dom->createAttribute('EnvelopeCode'); 
$pdf->appendChild($field_name);
$name = $dom->createTextNode($EnvelopeCodeR);
$field_name->appendChild($name);
*/
/*
 * Creazione del tag Envelope di 4° livello con attributi
 * non presente in caso di errore della envelope
 */

$envelope = $pdf->appendChild($dom->createElement('Envelope'));
$field_name = $dom->createAttribute('ID'); 
$envelope->appendChild($field_name);
$name = $dom->createTextNode($EnvelopeIDR);
$field_name->appendChild($name);
$field_name = $dom->createAttribute('EnvelopeCode'); 
$envelope->appendChild($field_name);
$name = $dom->createTextNode($EnvelopeCodeR);
$field_name->appendChild($name);


/* Output XML del documento DOM */
$dom->formatOutput = true;
$responseValue = $dom->saveXML();

/* Creazione di un esempio di header associato alla response */
$r = array();	
if($check == false) {
/* Messaggio di errore composto solo da header */
//	header("HTTP/1.0 204 No Response\r\n");
/* Messaggio di errore composto da header e body */

	$Rbody = "No response";
	$r[] = 'HTTP/1.0 204 No Response';
	/* Messaggio di errore incompleto (solo header) */
//	$r = implode("\r\n",$r) . "\r\n\r\n";
	/* Messaggio di errore completo (header e body) */
	$r = implode("\r\n",$r) . "\r\n\r\n" . $Rbody;
} 
else {
	/* Response */
//	$Rbody = $HTTP_RAW_POST_DATA;
//	header("HTTP/1.0 200 OK\r\n");
    $r[] = 'HTTP/1.0 200 OK';
    $r[] = 'Content-Type: text/html';
    $r[] = 'Host: ' . $settings['mpx_server'] . ':' . $settings['mpx_port'];
    $r[] = 'Content-Length: ' . strlen($responseValue);
    $r[] = 'Connection: close';
    /* Response incompleta (solo header) */
//	$r = implode("\r\n",$r);
	/* Response completa (header e body) */
	$r = implode("\r\n",$r) . "\r\n\r\n" . $responseValue;
//	$r = implode("\r\n",$r) . "\r\n\r\n" . $Rbody;
//	$r = implode("\r\n",$r) . "\r\n\r\n";
}

echo $r;

/* Recupero del post */
$post = $HTTP_RAW_POST_DATA;

/* Carico l'XML e comincio a parsarlo */
$dom = new DomDocument('1.0');

/* Gestione degli errori con chiamata alla funzione errorHandler()*/
$error = "";
set_error_handler("errorHandler");

/* Caricamento del corpo della response XML in un DOM */
$dom->loadXML($post);

restore_error_handler();
if($error!="") throw new SoapFault('wi400WsSiriAtg', "XML non valido:" . $error);

/* Estrazione dei dati di interesse dalla response XML */
$PDF_cod = parseXML($dom);

if(!$PDF_cod) throw new SoapFault('wi400WsSiriAtg', 'XML non contiene parametri validi oppure incompleto');

/* Decodifica del codice in base64 del PDF inviato per controllo errori di codifica */
$decoded = base64_decode($PDF_cod);
/* Salvataggio del file PDF inviato e decodificato */ 
if($settings['save_mpx_pdf']) {
	$namePDF = "Response_" . date('YmdHisu') . "_MPX.pdf";
	$filePDF = $mpx_pdf_path . $namePDF;
	$handle = fopen($filePDF, "w");
	fwrite($handle, $decoded);
	fclose($handle);
}

/* Aggiornamento del file di log */
if($saveLOG == True && $savePDF == True) {
	$log_msg = date('D, d M Y H:i:s T') . " - RESPONSE - ID: $ID";
	$log_msg .= " - Salvato file PDF decodificato - $namePDF\r\n";
	/* fopen() deve essere impostato ad "a" per scrivere sul file senza però riscrivere la stessa riga */
	$log_handle = fopen($log_file, "a");
	fwrite($log_handle, $log_msg);
	fclose($log_handle);
}

function parseXML($dom) {
	$array = array();
		
	/* Cerco se c'è il tag resource */	
	$params = $dom->getElementsByTagName('resource'); // Find Sections
	/* se non c'è cerco il tag PdfByteCode64 */
	if(!isset($params->item(0)->nodeValue) or ($params->item(0)->nodeValue)=="") 
		$params = $dom->getElementsByTagName('PdfByteCode64'); // Find Sections
	/* Se non ho trovato nulla errore */
	if(!isset($params)) return;
	
	return $params->item(0)->nodeValue;
}

function errorHandler($errno, $errstr, $errfile, $errline) {
	global $error;
	 
	$pos = strpos($errstr,"]:") ;   
	if($pos) {   
		$errstr = substr($errstr,$pos+ 2);   
	}   
	$error = $errstr;
}

?>
