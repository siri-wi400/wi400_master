<?php

	$miaLista = new wi400List("LU_OPERATORI_LIST",true);

	$miaLista->setSubfile($subfile);
	$miaLista->setFrom($subfile->getTable());
	
	$miaLista->setOrder("T703CD ASC");
	
	$miaLista->setShowMenu(true);
	
	$miaLista->setPassKey(true);
	
	$miaLista->setCalculateTotalRows("FALSE");
	
	$miaLista->setCols(array(
		new wi400Column("T703CD",_t('CODE')),
		new wi400Column("DES_OPE",_t('DESCRIPTION'))
	));
	
	// aggiunta chiavi di riga
	$miaLista->addKey("T703CD");
	
	$mioFiltro = new wi400Filter("DES_OPE",_t('DESCRIPTION'),"STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	$mioFiltro = new wi400Filter("T703CD",_t('CODE'),"STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
	if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
		$str = addslashes($_REQUEST["ONCHANGE"]);
		$miaLista->setPassKeyJsFunction($str);
	}

	listDispose($miaLista);
	
?>