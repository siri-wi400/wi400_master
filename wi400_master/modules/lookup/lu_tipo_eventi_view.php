<?php

	$miaLista = new wi400List("LU_TIPO_EVENTI_LIST",true);
	
	$miaLista->setSubfile($subfile);
	$miaLista->setFrom($subfile->getTable());
	$miaLista->setOrder("ANIDRT");
	
//	echo "SQL: ".$miaLista->getSql()."<br>";
	
	$miaLista->setShowMenu(true);
	
	$miaLista->setPassKey(true);
	
	$miaLista->setCalculateTotalRows("FALSE");
	
	$miaLista->setCols(array(
		new wi400Column("ANIDRT",_t('CODE')),
		new wi400Column("ANDSRT",_t('DESCRIPTION'))
	));
	
	// aggiunta chiavi di riga
	$miaLista->addKey("ANIDRT");
	
	$mioFiltro = new wi400Filter("ANDSRT",_t('DESCRIPTION'),"STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	$mioFiltro = new wi400Filter("ANIDRT",_t('CODE'),"STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
	if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
		$str = addslashes($_REQUEST["ONCHANGE"]);
		$miaLista->setPassKeyJsFunction($str);
	}

	listDispose($miaLista);
	
?>