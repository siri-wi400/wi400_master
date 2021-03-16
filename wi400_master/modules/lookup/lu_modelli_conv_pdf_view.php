<?php

	$miaLista = new wi400List("LU_MODELLI_CONV_PDF_LIST", !$isFromHistory);
	
	$miaLista->setSubfile($subfile);
	$miaLista->setFrom($subfile->getTable());
	$miaLista->setOrder("MODELLO");
	
	$miaLista->setSelection("MULTIPLE");

//	$miaLista->setCalculateTotalRows('FALSE');
	
	$miaLista->setCols(array(
		new wi400Column("MODELLO", "Modello"),
	));
	
	$miaLista->addKey("MODELLO");
	$miaLista->setPassKey("MODELLO");
	
	// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
	if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
		$str = addslashes($_REQUEST["ONCHANGE"]);
		$miaLista->setPassKeyJsFunction($str);
	}

	listDispose($miaLista);