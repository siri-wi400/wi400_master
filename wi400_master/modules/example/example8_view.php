<?php

	// Lista utenti di WI400
   	$miaLista = new wi400List("EXAMPLE8_LIST", true);
	$miaLista->setFrom($subfile->getTable());
	$miaLista->setSubfile($subfile);

    // Metto tutte le colonne presenti nel subfile
	$cols = getColumnListFromArray('EXAMPLE7', "example");
	// Alla colonna UTENTE attacco un link per vedere il dettaglio del cliente di riferimento associato
	$user = $cols['UTENTE'];
	$user->setDetailAction("EXAMPLE4","DETAIL");
	$user->addDetailKey('UTENTE');
	$miaLista->setCols($cols);
	// Aggiunta filtro veloce
	$mioFiltro = new wi400Filter("USER_NAME","Codice Utente","STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
    $miaLista->addKey("UTENTE");
    
	$miaLista->dispose();
	
	$phpCode = new wi400PhpCode();
	$phpCode->addFile($moduli_path."/example/example8_model.php");
	$phpCode->addFile($moduli_path."/example/subfile/EXAMPLE8.cls.php");
	$phpCode->addFile($moduli_path."/example/example8_view.php");
	$phpCode->dispose();
?>