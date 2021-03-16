<?php

	$miaLista = new wi400List("RICLIST",true);

	$miaLista->setFrom("FRICANAR");
	
	$sql = "'".$_SESSION['data_validita']."' BETWEEN 
				DIGITS(RICAVA)!!digits(RICMVA)!!digits(RICGVA) 
				AND DIGITS(RICAFV)!!digits(RICMFV)!!digits(RICGFV)";
		
	if (isset($_REQUEST["FILTER_SQL"]) AND $_REQUEST["FILTER_SQL"] != ""){
		$sql = $sql." AND ".$_REQUEST["FILTER_SQL"];
	}
	
	$miaLista->setWhere($sql);
	$miaLista->setField("RICCDA, RICDSA");	
	$miaLista->setOrder("RICCDA ASC");
	
	$miaLista->setShowMenu(true);
	
	$miaLista->setPassKey(true);
	
	$miaLista->setCalculateTotalRows("FALSE");
	
	$miaLista->setCols(array(
		new wi400Column("RICCDA",_t('CODE')),
		new wi400Column("RICDSA",_t('DESCRIPTION'))
	));
	
	// aggiunta chiavi di riga
	$miaLista->addKey("RICCDA");
	
	$mioFiltro = new wi400Filter("RICDSA",_t('DESCRIPTION'),"STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	$mioFiltro = new wi400Filter("RICCDA",_t('CODE'),"STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
	if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
		$str = addslashes($_REQUEST["ONCHANGE"]);
		$miaLista->setPassKeyJsFunction($str);
	}

	listDispose($miaLista);
	
?>