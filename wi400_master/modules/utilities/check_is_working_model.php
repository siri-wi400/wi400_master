<?php
// Query su file Azioni per testare connessione
$sql = "SELECT AZIONE FROM FAZISIRI";
$result =$db->singleQuery($sql);
if (!$result) {
	$messageContext->addMessage("ERROR", "Errore DB");
} else {
	$row = $db->fetch_array($result);
	if (!$row) {
		$messageContext->addMessage("ERROR", "Errore DB");
	} else {
		echo "<br>".$row['AZIONE'];
		echo "\r\nCollegamento e query su DB ok!";
		// Proseguo con i test di richiamo di un comando
		$arrayJob = executeCommand("rtvjoba", array(), array("JOB"=>"JOB","USER"=>'USR',"NBR"=>"NBR"));
		if (!is_array($arrayJob)) {
			$messageContext->addMessage("ERROR", "Errore Comando");
		} else {
			// Tutto OK;
			echo "\r\nRichiamo comando nel sottosistema OK!";
			showArray($arrayJob);
		}
	}
}