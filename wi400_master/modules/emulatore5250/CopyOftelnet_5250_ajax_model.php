<?php
require_once "telnet_5250_class.php";
if($actionContext->getForm()=="WRITE") {
	//
	$session_id = $_GET['SESSION_ID'];
	$function_key = $_GET['FUNCTION_KEY'];
	$Sessione5250 = new wi400AS400Session($session_id);
	$display= wi400AS400Func::loadDisplay($session_id);
	// @TODO Capire cosa si aspetta il video .. tutto o solo i campi modificati
	// Prendo la posizione del cursore
	$hex = "0101"; // Default posizione cursore
	// Reperisco il campo selezionato da campo selezionato se passato
	if ($_REQUEST['CAMPO_SELEZIONATO']!="") {
		$display->getFocusedField($_REQUEST['CAMPO_SELEZIONATO'], $_REQUEST['CARATTERI']);
		//$focusedField =$display->getFieldById($_REQUEST['CAMPO_SELEZIONATOI']);
		// @todo Ciclare su tutti i campi dei commands
		/*$campo = $display->getFields($_REQUEST['CAMPO_SELEZIONATO']);
		if ($campo) {
			$riga = $campo->getXposition();
			$colonna = $campo->getYposition();
			if (isset($_REQUEST['CARATTERI']) && is_numeric($_REQUEST['CARATTERI'])) {
				$colonna = $colonna + $_REQUEST['CARATTERI'];
			}
			$riga = sprintf('%02s',dechex($riga));
			$colonna = sprintf('%02s',dechex($colonna));
			$hex = strtoupper($riga.$colonna);
		}*/
	}
	// @todo da implementare
	$hex .=$function_key;
	$modifystring = $display->getModifiedString($_REQUEST);
	$hex .=$modifystring;
	// @todo Controllo che il tasto di funzione sia tra quelli previsti
	$dati = $Sessione5250->writeTerminalData($hex);
	// @verifico i dati di ritorno per comunicare eventuali errori
	// Leggo un prima sequenza di dati per tornare già una risposta del 5250 interrogato
	$dati = $Sessione5250->readTerminalData();
	$obj = $Sessione5250->parseDataStream($dati);
	// Verifico se esiste un comando di query e quindi rispondo
	if ($obj->getCommandByType("F3")) {
		$hex ="0000880044D9708006000302000000000000000000000000000000000001F3F1F7F9F0F0F20101000000701201F40000007B3100000FC800000000000000000000000000000000";
		$dati = $Sessione5250->writeTerminalData($hex);
		// Leggo i dati
		$dati = $Sessione5250->readTerminalData();
		//$obj = $Sessione5250->parseDataStream($dati);
	}
	// Scrivo la stringa di conferma a proseguire
	/*if ($login==True) {
		$hex ="0000880044D9708006000302000000000000000000000000000000000001F3F1F7F9F0F0F20101000000701201F40000007B3100000FC800000000000000000000000000000000";
		$dati = $Sessione5250->writeTerminalData($hex);
		// Leggo i dati
		$dati = $Sessione5250->readTerminalData();
		$obj = $Sessione5250->parseDataStream($dati);
	}*/
	$obj = $Sessione5250->parseDataStream($dati);
	// Invio il nuovo pannello
	$display->setStreamObj($obj);
	//echo var_dump($obj);die();
	$display->executeCommand();
	$html = $display->display();
	//die($html);
	// PER DEBUG RITORNO ANCHE I DATI CHE IL 5250 MI TORNA INDIETRO
	$value ="";
	$errore="";
	$messaggio="";
	$html = utf8_encode($html);
}
if($actionContext->getForm()=="READ") {
	
}