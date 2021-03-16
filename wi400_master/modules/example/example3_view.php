<?php

	// Lista utenti di WI400
   	$miaLista = new wi400List("EXAMPLE3_LIST", true);
	$miaLista->setFrom("SIR_USERS");
	
	// Solo utenti con menu generale
	$miaLista->setWhere("MENU='UNO'");
	$miaLista->setOrder("USER_NAME DESC");

    // Scelta Colonne
	$miaLista->addCol(new wi400Column("USER_NAME","Prenotazione", null, "right"));
	$miaLista->addCol(new wi400Column("EMAIL","Causale"));
    // .. oppure creo l'oggetto
	$colonna = new wi400Column("AUTH_METOD", "Verifica Utente");
	$colonna->setShow(False);
	$miaLista->addCol($colonna);
	// Aggiunta filtro veloce
	$mioFiltro = new wi400Filter("USER_NAME","Codice Utente","STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	$miaLista->dispose();
	
   $phpCode = new wi400PhpCode();
   $phpCode->addFile($moduli_path."/example/example3_view.php");
   $phpCode->dispose();
?>