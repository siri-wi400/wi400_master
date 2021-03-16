<?php 
    // Creazione dell'oggetto subfile
	$subfile = new wi400Subfile($db, "EXAMPLE8", $settings['db_temp']);
	// Modulo di riferimento
	$subfile->setModulo("example");
	// Query guida del subfile
	$sql = '*AUTOBODY';
	$subfile->setSql($sql);	
	// Eventuali altri parametri utilizzati dall'oggetto subfile
	$subfile->addParameter("MENU", "UNO", true);	
?>