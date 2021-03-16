<?php

	// Lista utenti di WI400
   	$miaLista = new wi400List("EXAMPLE6_LIST", true);
	$miaLista->setFrom("SIR_USERS");

    // Scelta Colonne
	$miaLista->addCol(new wi400Column("USER_NAME","Prenotazione", null, "right"));
	$miaLista->addCol(new wi400Column("MENU","Causale"));
	// Colonna calcolata Run_time
	$colonna = new wi400Column("AUTH_METOD", "Verifica Utente");
	$colonna->setDecode(array('TYPE'=>'common', 'COLUMN'=>'DESCRIZIONE', 'TABLE_NAME'=>'FMNUSIRI', 'KEY_FIELD_NAME'=>'MENU'), 'MENU');
	$colonna->setSortable(False);
	$miaLista->addCol($colonna);
	
	// ... oppure la decodifico al volo con una funzione 
	//$colonna->setDefaultValue("EVAL:miaFunzione(parametro1, parametro2, parametro3)");
	// Aggiunta filtro veloce
	$mioFiltro = new wi400Filter("USER_NAME","Codice Utente","STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	$miaLista->dispose();
	
	
	$phpCode = new wi400PhpCode();
	$phpCode->addFile($moduli_path."/example/example6_view.php");
	$phpCode->dispose();
?>