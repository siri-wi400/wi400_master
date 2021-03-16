<?php
$azione = $actionContext->getAction();
if($actionContext->getForm()=="WRITE") {
	//
	$session_id = $_GET['session_id'];
	$function_key = $_GET['function'];
	require_once $routine_path."/classi/wi400_5250Session.cls.php";
	$Sessione5250 = new wi400_5250Session(session_id());
	
	$display = new wi400_5250Display(session_id());
	// Prendo la posizione del cursore
	$hex = "0A35";
	// @todo da implementare
	$hex .=$function_key;
	// @todo Controllo che il tasto di funzione sia tra quelli previsti
	$dati = $Sessione5250->writeTerminalData($hex);
	// @verifico i dati di ritorno per comunicare eventuali errori
	$value ="";
	$errore="";
	$messaggio="";
}	
if($actionContext->getForm()=="READ") {
	
}