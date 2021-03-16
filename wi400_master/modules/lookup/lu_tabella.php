<?php

$tabella = "";
if(isset($_REQUEST["TABELLA"]) && $_REQUEST["TABELLA"]!="")
	$tabella = $_REQUEST["TABELLA"];
else if(isset($_REQUEST["TABLE"]) && $_REQUEST["TABLE"]!="")
	$tabella = $_REQUEST["TABLE"];

$tabelle = new wi400Tabelle (Null, Null,  $db);
if (isset($_REQUEST["FILTER_SQL"]) AND $_REQUEST["FILTER_SQL"] != ""){
	$sql = " AND ".$_REQUEST["FILTER_SQL"];
	$tabelle->setWhere($sql);
}
if (isset($_REQUEST["BASE64_FILTER_SQL"]) AND $_REQUEST["BASE64_FILTER_SQL"] != ""){
	$sql = " AND ".base64_decode($_REQUEST["BASE64_FILTER_SQL"]);
	$tabelle->setWhere($sql);
}
$tabelle->preparaTabella($tabella);
// Inizializzo lista 
$subfile = new wi400Subfile($db, "TAB", $settings['db_temp'], 10);
$array = array();
$array['CODICE']=$db->singleColumns("1", 20 );
$array['DESCRIZIONE']=$db->singleColumns("1", 40 );
$subfile->inz($array);

while($tabelle->caricaTabella()){

	if ($tabelle->getStato() != '0')
        {
	        $dati = array($tabelle->getElemento() , $tabelle->getDescrizione());				
	 	    $subfile->write($dati);
        }
}
$subfile->finalize();
$miaLista = new wi400List("TABELLA_$tabella",true);

$miaLista->setPassKey("CODICE");
$miaLista->setPassDesc("DESCRIZIONE");

$miaLista->setFrom($subfile->getTable());

$where = "";
if(isset($_REQUEST['LU_WHERE']) && $_REQUEST['LU_WHERE']!="") {
	$where = $_REQUEST['LU_WHERE'];
}

$miaLista->setWhere($where);

$miaLista->setOrder("CODICE ASC");

//echo "SQL: ".$miaLista->getSql()."<br>";

$miaLista->setCols(array(
						new wi400Column("CODICE","Codice Elemento"),
						new wi400Column("DESCRIZIONE","Descrizione elemento"),
						)
					);

// Aggiunta chiavi di riga
$mioFiltro = new wi400Filter("DESCRIZIONE","Descrizione","STRING");
$mioFiltro->setFast(true);
$miaLista->addFilter($mioFiltro);
$mioFiltro = new wi400Filter("CODICE","Codice","STRING");
$mioFiltro->setFast(true);
$miaLista->addFilter($mioFiltro);

$miaLista->addKey("CODICE");

// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
	$str = addslashes($_REQUEST["ONCHANGE"]);
	$miaLista->setPassKeyJsFunction($str);
}

listDispose($miaLista);
?>