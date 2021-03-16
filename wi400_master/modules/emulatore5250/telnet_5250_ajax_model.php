<?php
require_once "telnet_5250_class.php";
if($actionContext->getForm()=="WRITE") {
	//
	$session_id = $_GET['SESSION_ID'];
	// Cicolo di lettura della maschera
	$Sessione5250 = new wi400AS400Session($session_id);
	// Manager del display video, gli faccio sapere che sono dentro l'AJAX
	$display = $Sessione5250->manage5250(True);
	$display->setDisposeContainer(false);
	$display->setDisposeFunctionButton(false);

	// PER DEBUG RITORNO ANCHE I DATI CHE IL 5250 MI TORNA INDIETRO
	$html = $display->display();
	$value ="";
	$errore="";
	$messaggio="";
	$complete="";
	$dati="";
	//$html = utf8_encode($html);
}
if($actionContext->getForm()=="CLOSE") {
	
	$html = "";
	$session_id = $_GET['SESSION_ID'];
	$Sessione5250 = new wi400AS400Session($session_id);
	$Sessione5250->closeTerminalConnection();
	die("CLOSED");
}
if($actionContext->getForm()=="ESTRAI_SUBFILE") {
	
	$html = "";
	$session_id = $_GET['SESSION_ID'];
	$Sessione5250 = new wi400AS400Session($session_id);
	$id = $Sessione5250->estraiCurrentSubfile();
	$display = wi400AS400Func::loadDisplay($session_id);
	$display->setIdExtraction($id);
	$display->saveDisplay();

	// Richiamo programma di estrazione scrivendo l'ID
	
	die("EXTRACTED");
}
if($actionContext->getForm()=="READ") {
	
	$html = "";
	$session_id = $_GET['SESSION_ID'];
	$Sessione5250 = new wi400AS400Session($session_id);
	// Controllo se la connessione è stata Cancellata lato SERVER devo disconnettere tutto
	$status = $Sessione5250->getConnectionStatus();
	$complete ="";
	if ($status=="C") {
		$complete="*CANCEL";		
	} else {
		$display= wi400AS400Func::loadDisplay($session_id);
		$dati = $Sessione5250->readTerminalData("0");
		if ($dati['CDATA']!="") {
			$stream = "3".$dati['CDATA'];
			$sepa="!";
			$obj = $Sessione5250->parseDataStream($stream);
			$display->setStreamObj($obj);
			//echo var_dump($obj);die();
			$display->executeCommand();
			$resolution = $Sessione5250->getResolutionRow()."x".$Sessione5250->getResolutionCol();
			$display->setResolution($resolution);
			$display->setDisposeContainer(false);
			$display->setDisposeFunctionButton(false);
			$html = $display->display();
		}
	}
	// Verifico se sto estrando qualcosa
	$id = "";
	if(isset($display)) {
		$id = $display->getIdExtraction();
		if ($id!="") {
			$where = array(
				'PGMID' => $id,
				'PGMUSR' => $_SESSION['user']
			);
			$stmt_update_zextcapm = $db->prepare("UPDATE", "ZEXTCAPM", $where, array('PGMSTS'));
			$rs = $db->execute($stmt_update_zextcapm, array('3'));
			
			if($rs) {
				$display->setIdExtraction("");
				$display->saveDisplay();
			}
		}
	}
	// Verifico se la connessione è stata cancellata
	$dati = "";
	$value = "";
	$messaggio = "";
	$errore = "";
}
if($actionContext->getForm()=="SAVE_ZOOM") {
	$_SESSION['TELNET_5250_ZOOM'] = $_REQUEST['ZOOM'];
	die("ok");
}