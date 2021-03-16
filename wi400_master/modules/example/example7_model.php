<?php 
    // Creazione dell'oggetto subfile
	$subfile = new wi400Subfile($db, "EXAMPLE7", $settings['db_temp']);
	// Modulo di riferimento
	$subfile->setModulo("example");
	// Query guida del subfile
	$sql = 'SELECT * FROM SIR_USERS';
	$subfile->setSql($sql);	
	// Eventuali altri parametri utilizzati dall'oggetto subfile
	// .. la rottura di una chiave comporta il ricaricamento del subfile ..
	//$subfile->addParameter("USER_TYPE", $userType);	
?>