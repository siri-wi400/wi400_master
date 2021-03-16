<?php

require_once 'wi_chkdelaymov_commons.php';

$azione = $actionContext->getAction ();

$history->addCurrent ();

// Paramentri scelta libreria
if ($actionContext->getForm () == "DEFAULT") {
	$title = $actionContext->getLabel();
	$actionContext->setLabel ( "Parametri" );
}
// Lista
elseif ($actionContext->getForm () == "LIST" ) {
	$actionContext->setLabel ( "ListaMovimentiInRitardo" );
	// Parametri FORM DEFAULT
	$ente = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'ENTE' );
	$datap = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'DATAP' );
	$articolo = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'ARTI' );
	
	// Data Limite
	if ($datap !== "") {
		$sql = "SELECT DIGITS(STGALI)!!DIGITS(STGMLI)!!DIGITS(STGGLI) AS DATAL FROM FSTGIPDV where DIGITS(STGGVA)!!'/'!!DIGITS(STGMVA)!!'/'!!DIGITS(STGAVA) = '$datap' ";
		$result = $db->query($sql);
		$row = $db->fetch_array($result);
		$datal = $row['DATAL'];
	}
	
	// Query SQL
	$datap = dateViewToModel ( $datap );
	$datal = substr($datal,2,6);
	$where_array = array ();
	$where_array [] = "CAADMO > '$datal' ";
	$where_array [] = "digits(CAAACP)!!digits(CAAMCP)!!digits(CAAGCP) <= $datap ";
	if ($articolo != "") {
		$where_array [] = "CAAARP in ('".implode ("', '", $articolo)."')";
	}
	if ($ente != "") {
		$where_array [] = "CAAENP in ('".implode ("', '", $ente)."')";
	}
}
