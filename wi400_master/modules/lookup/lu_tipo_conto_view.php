<?php

	$miaLista = new wi400List("TIPO_CONTO_LST",true);
	$miaLista->setFrom($subfile->getTable());
	
	$miaLista->setCols(array(
		new wi400Column("COD_TIPO_CONTO","Codice"),
		new wi400Column("DES_TIPO_CONTO","Descrizione")
	));
	
	// aggiunta chiavi di riga
	$miaLista->addKey("COD_TIPO_CONTO");
	$miaLista->setPassKey(true);
	
	// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
	if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
		$str = addslashes($_REQUEST["ONCHANGE"]);
		$miaLista->setPassKeyJsFunction($str);
	}
	
	listDispose($miaLista);

?>