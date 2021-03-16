<?php
require_once $routine_path."/classi/wi400ExportList.cls.php";
require_once $routine_path."/classi/wi400invioEmail.cls.php";

$azione = $actionContext->getAction ();

$history->addCurrent ();

// Paramentri scelta libreria
if ($actionContext->getForm () == "DEFAULT") {
	$title = $actionContext->getLabel();
	$actionContext->setLabel ( "Parametri" );
}

// Lista
elseif ($actionContext->getForm () == "LIST" ) {
	$actionContext->setLabel ( "Lista" );
	// Parametri FORM DEFAULT
	$ente = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'ENTE' );
	$ordine = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'ORDINE' );
	$fornitore = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'FORNITORE' );
	$arti = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'ARTICOLO' );
	$dcaricod = dateViewToModel(wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'DCARICOD' ));
	$dcaricoa = dateViewToModel(wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'DCARICOA' ));
	$dregd = dateViewToModel(wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'DREGD' ));
	$drega = dateViewToModel(wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'DREGA' ));
	$perdelta = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'PERDELTA' );
	// Query SQL
	$where_array = array ();
	
	if ($ente != "") {
		$where_array [] = "LOGCDE in ('".implode ("', '", $ente)."')";
	}
	if ($ordine != "") {
		$where_array [] = "LOGNOR in ('".implode ("', '", $ordine)."')";
	}
	if ($fornitore != "") {
		$where_array [] = "LOGCDF in ('".implode ("', '", $fornitore)."')";
	}
	if ($arti != "") {
		$where_array [] = "LOGCDA in ('".implode ("', '", $arti)."')";
	}
	// Data Carico
	if ($dcaricod != "" && $dcaricoa != "") {
	$where_array [] = "LOGDCA between '$dcaricod' and '$dcaricoa'";
	}
	// Data Registrazione
	if ($dregd != "" && $drega != ""){
	$where_array [] = "digits(decimal(substr(digits(logdmo), 5, 2)+2000 , 4, 0)) !! digits(decimal(substr(digits(logdmo), 3, 2), 2, 0)) !! digits(decimal(substr(digits(logdmo), 1, 2), 2, 0)) between '$dregd' and '$drega'";
	}
	if ($perdelta != "") {
		$where_array [] = "LOGVAR >= '$perdelta'";
	}

}
