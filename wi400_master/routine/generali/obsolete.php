<?php 
// Controllo la validità di una licenza 
function chkLicenza($licenza) {

	global $db, $badmode, $messageContext, $connzend;
    
    getMicroTimeStep("Cerca Licenza");
    if (isset($_SESSION['LIC_'.$licenza]))
    {
    	$FLAG = $_SESSION['LIC_'.$licenza];
      getMicroTimeStep("Ricerca su sessione");
  	}	else {
    // Reperisco la descrizione dell'ente
    $pgmlic = new wi400Routine('ZLICRT', $connzend, False, True);
    $do = $pgmlic->load_description();
    $do = $pgmlic->prepare();

    // Imposto i parametri di chiamat
    $pgmlic->set('FLAG',"0");
    $pgmlic->set('MODULO',strtoupper($licenza));
		//$datiUtente[] = $codice ." ". substr($TRACCIATO, 42, 25);
    $do = $pgmlic->call();
    $FLAG=$pgmlic->get('FLAG');
    $_SESSION['LIC_'.$licenza]=$FLAG;
        getMicroTimeStep("Ricerca cou routine");

  	}
		// Utente non riconosciuto dal sistema
    if ($FLAG == '1')
    {
        $messageContext->addMessage("ALERT", "Attenzione licenza ".$licenza." SCADUTA. Contattare S.I.R.I Informatica", "", false);
        if ($badmode) exit;
    	return false;
    }
    // Scaduti codici di lincenza WI400
    if ($FLAG == '2') {
    	$messageContext->addMessage("ALERT", "Attenzione licenza ".$licenza." non valida. Contattare S.I.R.I Informatica", "", false);
            if ($badmode) exit;
    	return false;
    } 
    	if ($FLAG == '3') {
        $messageContext->addMessage("ALERT", "Attenzione licenza ".$licenza." non trovata. Contattare S.I.R.I Informatica", "", false);	
        if ($badmode) exit;
    	return false;

    } 
    if ($FLAG == '4') {
        $messageContext->addMessage("INFO", "Attenzione licenza ".$licenza." in scadenza. Contattare S.I.R.I Informatica per il rinnovo", "", false);	
        if ($badmode) exit;
    	return false;
    }
    getMicroTimeStep("Fine Cerca Licenza");

	return true;

}
function counter_check($counter, $limit, $segno = '+', $notNegative = false) {
	
	// @todo Prima di usare la funzione bisogna attivare l'estensione per i Semaphore	
	$filename = wi400File::getCommonFile ( "counter", $counter . ".dat" );
	if (file_exists ( $filename )) {
		$fp = fopen ( $filename, "r+" );
	} else {
		$fp = fopen ( $filename, "w" );
	}
	$id = ftok($filename, "W");
	$return = - 1;
	$semaforo = sem_get($id);
	sem_acquire($semaforo);
	$cntr = fread ( $fp, 80 );
		if ($notNegative && $cntr <= 0)
			$cntr = 0;
		if ($cntr < $limit or $segno == '-') {
			$return = 0;
			switch ($segno) {
				case '+' :
					$cntr = $cntr + 1;
					break;
				case '-' :
					$cntr = $cntr - 1;
					break;
			}
	$return = $cntr;
	// Scrittura nuovo valore
	fseek ( $fp, 0 );
	fwrite ( $fp, $cntr );
	fclose ( $fp );
	sem_release($semaforo);
	return $return;
	}
}
function ean8_decode($ean, $flagnum = "S") {
	// Verifico se calcolare il check digit
	if (strlen ( $ean ) == 7) {
		$calcolo1 = substr ( $ean, 1, 1 ) + substr ( $ean, 3, 1 ) + substr ( $ean, 5, 1 );
		//$calcolo1 = $calcolo1 * 3;
		$calcolo2 = substr ( $ean, 0, 1 ) + substr ( $ean, 2, 1 ) + substr ( $ean, 4, 1 ) + substr ( $ean, 6, 1 );
		$calcolo2 = $calcolo2 * 3;
		$calcolo3 = $calcolo1 + $calcolo2;
		$resto = ($calcolo3 % 10);
		if ($resto == 0)
			$resto = 10;
		$check = 10 - $resto;
		$ean = $ean . $check;
	}
	
	if ($flagnum == "S") {
		$A = 65;
		$C = 85;
	} else {
		$A = 161;
		$C = 181;
	}
	
	// Start barcode
	$barcode = chr ( 33 );
	// Prima parte barcode con codifica di tipo A
	$barcode = $barcode . chr ( $A + substr ( $ean, 0, 1 ) ) . chr ( $A + substr ( $ean, 1, 1 ) ) . chr ( $A + substr ( $ean, 2, 1 ) ) . chr ( $A + substr ( $ean, 3, 1 ) );
	// Barra di mezzo
	$barcode = $barcode . chr ( 35 );
	// Seconda parte barcode con codifica di tipo C
	$barcode = $barcode . chr ( $C + substr ( $ean, 4, 1 ) ) . chr ( $C + substr ( $ean, 5, 1 ) ) . chr ( $C + substr ( $ean, 6, 1 ) ) . chr ( $C + substr ( $ean, 7, 1 ) );
	// Stop barcode
	$barcode = $barcode . chr ( 33 );
	return $barcode;
}
?>