<?php

$campo = "USER_NAME";
if(isset($_REQUEST["CAMPO"]) && $_REQUEST["CAMPO"]!="")
	$campo = $_REQUEST["CAMPO"];

$miaLista = new wi400List("LU_USER_LIST",true);

$select = "USER_NAME, rtrim(FIRST_NAME)!!' '!!rtrim(LAST_NAME) as DES, EMAIL";

$from = $users_table;

$where = "";

if (isset($_REQUEST["FILTER_SQL"]) AND $_REQUEST["FILTER_SQL"] != ""){
	$where .= " AND ".$_REQUEST["FILTER_SQL"];
}

$miaLista->setField($select);
$miaLista->setFrom($from);
$miaLista->setWhere($where);
$miaLista->setOrder("USER_NAME");

//echo "SQL: ".$miaLista->getSql()."<br>";

$miaLista->setShowMenu(true);

$miaLista->setPassKey(true);
//$miaLista->setPassDesc("DES_GER");

$miaLista->setCalculateTotalRows("FALSE");

$miaLista->setCols(array(
	new wi400Column("USER_NAME",_t("CODICE")),
	new wi400Column("DES",_t("DESCRIZIONE")),
	new wi400Column("EMAIL","E-mail")
));

// aggiunta chiavi di riga
$miaLista->addKey("$campo");

$mioFiltro = new wi400Filter("DES",_t("DESCRIZIONE"),"STRING");
$mioFiltro->setFast(true);
$miaLista->addFilter($mioFiltro);

$mioFiltro = new wi400Filter("USER_NAME",_t("CODICE"),"STRING");
$mioFiltro->setFast(true);
$miaLista->addFilter($mioFiltro);

// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
	$str = addslashes($_REQUEST["ONCHANGE"]);
	$miaLista->setPassKeyJsFunction($str);
}

listDispose($miaLista);