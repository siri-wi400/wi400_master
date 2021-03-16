<?php

	// Lista utenti di WI400
   	$miaLista = new wi400List("EXAMPLE7_LIST", true);
	$miaLista->setFrom($subfile->getTable());
	$miaLista->setSubfile($subfile);
	echo $subfile->getTable();

    // Metto tutte le colonne presenti nel subfile
	$cols = getColumnListFromArray('EXAMPLE7', "example");
	$miaLista->setCols($cols);
	// Aggiunta filtro veloce
	$mioFiltro = new wi400Filter("USER_NAME","Codice Utente","STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	$miaLista->dispose();
	
	$phpCode = new wi400PhpCode();
	$phpCode->addFile($moduli_path."/example/example7_model.php");
	$phpCode->addFile($moduli_path."/example/subfile/EXAMPLE7.cls.php");
	$phpCode->addFile($moduli_path."/example/example7_view.php");
	$phpCode->dispose();
?>