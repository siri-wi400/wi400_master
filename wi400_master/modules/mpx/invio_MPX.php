<?php 

/*
 * Applicazione per l'invio di file PDF ad MPX
 * 
 * I file XML generati ed accumulati per l'invio ad MPX vengono raggruppati in un unico file XML e cancellati,
 * mentre il file XML così ottenuto viene inviato ad MPX
 */

/*
 * Indicazione del tipo di funzionamento: in batch o no -
 * necessario per il recupero dei parametri a seconda del tipo utilizzato
 * e per fare in modo che quando avviene un errore in modalità batch venga interrotta l'esecuzione,
 * ma che in altra modalità (lancio da programma mpx_invio.php in WI400) non si sbianchi lo schermo 
 */ 
$isBatch = false;

if($NotBatch!==true) {
	// Recupero la lista delle librerie dell'interattivo 	
	if(isset($argv) && !empty($argv))
		$INT_LIBRARY = explode(";" ,trim($argv[2]));
	else if(isset($batchContext))
		$INT_LIBRARY = explode(";", $batchContext->lista_librerie);
	
	$isBatch = true;
}

$classe = "wi400invioMPX.cls.php";
require_once $routine_path . "/classi/".$classe;

// Istanzio la classe wi400invioMPX
$invioMPX = new wi400invioMPX("", $db, $isBatch);

$post_results = $invioMPX->httpPost();
	
if(!is_string($post_results)){
	if($isBatch) 
		die();
} 
else {
	// Parse della response
	$resultMpx = $invioMPX->parse_XML_res();
}

if($isBatch) 
	die();

?>