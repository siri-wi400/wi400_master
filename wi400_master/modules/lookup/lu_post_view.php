<?php
$miaLista = new wi400List("LU_POST_LIST",true);

$select = "a.BSCCDP, a.BSCDSP";
/*
$from = "FBSCPOST a, lateral ( 
	select rrn(o) as nrel 
	from LBSCPOST o 
	where a.BSCCDB=o.BSCCDB and a.BSCCDP=o.BSCCDP and digits(BSCAVA)!!digits(BSCMVA)!!digits(BSCGVA)<=".$_SESSION['data_validita']."
	fetch first row only) as x";		// @todo Non serve l'order by? order by digits(BSCAVA)!!digits(BSCMVA)!!digits(BSCGVA)
*/
$from = "FBSCPOST a, lateral (
	select rrn(o) as nrel
	from LBSCPOST o
	where a.BSCCDP=o.BSCCDP ";
if(isset($_REQUEST["ENTE_SQL"]) && $_REQUEST["ENTE_SQL"]!=""){
	$from .= " and BSCCDB='".$_REQUEST["ENTE_SQL"]."'";
}
$from .= " and digits(BSCAVA)!!digits(BSCMVA)!!digits(BSCGVA)<=".$_SESSION['data_validita']."
	fetch first row only) as x";		// @todo Non serve l'order by? order by digits(BSCAVA)!!digits(BSCMVA)!!digits(BSCGVA)

$where = "rrn(a) = x.NREL and BSCSTA='1'";
/*
$where .= " and digits(BSCAVA)!!digits(BSCMVA)!!digits(BSCGVA)<=".$_SESSION['data_validita']." and 
	digits(BSCAFI)!!digits(BSCMFI)!!digits(BSCGFI)>=".$_SESSION['data_validita'];
$where .= " and BSCSTA='1'";
*/
if(isset($_REQUEST["ENTE_SQL"]) && $_REQUEST["ENTE_SQL"]!=""){
	$where .= " and BSCCDB='".$_REQUEST["ENTE_SQL"]."'";
}

if(isset($_REQUEST["FILTER_SQL"]) && $_REQUEST["FILTER_SQL"]!=""){
	$where .= " and ".$_REQUEST["FILTER_SQL"];
}

$group_by = "BSCCDP, BSCDSP";

$miaLista->setField($select);
$miaLista->setFrom($from);
$miaLista->setWhere($where);
$miaLista->setGroup($group_by);
$miaLista->setOrder("BSCCDP ASC");

//echo "SQL: ".$miaLista->getSql()."<br>";

/*
$from = "FBSCPOST a, lateral (
	select rrn(o) as nrel
	from LBSCPOST o
	where a.BSCCDB=o.BSCCDB and a.BSCCDP=o.BSCCDP and digits(BSCAVA)!!digits(BSCMVA)!!digits(BSCGVA)<=".$_SESSION['data_validita']."
	fetch first row only) as x";		// @todo Non serve l'order by? order by digits(BSCAVA)!!digits(BSCMVA)!!digits(BSCGVA)

$where = "rrn(a) = x.NREL";
*//*
	$where .= " and digits(BSCAVA)!!digits(BSCMVA)!!digits(BSCGVA)<=".$_SESSION['data_validita']." and
	digits(BSCAFI)!!digits(BSCMFI)!!digits(BSCGFI)>=".$_SESSION['data_validita'];
	$where .= " and BSCSTA='1'";
*//*
if(isset($_REQUEST["ENTE"]) && $_REQUEST["ENTE"]!=""){
	$where .= " and BSCCDB='".$_REQUEST["ENTE"]."'";
}

if(isset($_REQUEST["FILTER_SQL"]) && $_REQUEST["FILTER_SQL"]!=""){
	$where .= " and ".$_REQUEST["FILTER_SQL"];
}

$sql_1 = "select BSCCDP
	from $from
	where $where
	group by BSCCDP";

$sql_2 = "with TAB_POST as ($sql_1)
	select a.BSCCDP, a.BSCDSP
	from TAB_POST i, $from
	where rrn(a) = x.NREL and i.bsccdp=a.bsccdp";
	
$miaLista->setQuery($sql_2);

echo "SQL: ".$miaLista->getQuery()."<br>";
*/

$miaLista->setOrder("BSCCDP ASC");

$miaLista->setCols(array(
	new wi400Column("BSCCDP", "POST"),
	new wi400Column("BSCDSP", _t('DESCRIPTION')." POST")
));

$miaLista->setShowMenu(false);

$miaLista->setPassKey(true);
$miaLista->setPassDesc("BSCDSP");

$mioFiltro = new wi400Filter("BSCDSP",_t('DESCRIPTION'),"STRING");
$mioFiltro->setFast(true);
$miaLista->addFilter($mioFiltro);

$mioFiltro = new wi400Filter("BSCCDP",_t('CODICE'),"STRING");
$mioFiltro->setFast(true);
$miaLista->addFilter($mioFiltro);

// aggiunta chiavi di riga
$miaLista->addKey("BSCCDP");

// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
	$str = addslashes($_REQUEST["ONCHANGE"]);
	$miaLista->setPassKeyJsFunction($str);
}

listDispose($miaLista);