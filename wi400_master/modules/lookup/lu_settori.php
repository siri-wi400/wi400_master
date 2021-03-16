<?php
$miaLista = new wi400List("SECTORS",true);

$miaLista->setFrom("FTAB001");

if (isset($_REQUEST["FILTER_SQL"]) AND $_REQUEST["FILTER_SQL"] != ""){
	$sql = $sql." AND ".$_REQUEST["FILTER_SQL"];
}

$miaLista->setWhere($sql);

$miaLista->setOrder("T01DEL ASC");

$miaLista->setShowMenu(false);

$miaLista->setPassKey('codset');

$miaLista->setCols(array(
						new wi400Column("T01COD","Codice"),
						new wi400Column("T01DEL","Descrizione")
						)
					);

// aggiunta chiavi di riga
$mioFiltro = new wi400Filter("T01DEL","Descrizione","STRING");
$mioFiltro->setFast(true);
$miaLista->addFilter($mioFiltro);
$miaLista->addKey("T01COD");

// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
	$str = addslashes($_REQUEST["ONCHANGE"]);
	$miaLista->setPassKeyJsFunction($str);
}

listDispose($miaLista);
?>