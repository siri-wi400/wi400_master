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
	$magazzino = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'MAGAZZINO' );
	$datad = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'DATAD' );
	$datal = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'DATAL' );
	$post = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'POST' );
	$cumu = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'CUMU' );
	$arti = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'ARTI' );
	// Query SQL
	$datad = dateViewToModel ( $datad );
	$datal = dateViewToModel ( $datal );
	$where_array = array ();
	
	$where_array [] = "LOGDTA between '$datad' and '$datal' ";
	if ($magazzino != "") {
		$where_array [] = "LOGCDE in ('".implode ("', '", $magazzino)."')";
	}
	if ($post != "") {
		$where_array [] = "LOGPST in ('".implode ("', '", $post)."')";
	}
	if ($cumu != "") {
		$where_array [] = "LOGCUM in ('".implode ("', '", $cumu)."')";
	}
	if ($arti != "") {
		$where_array [] = "LOGCDA in ('".implode ("', '", $arti)."')";
	}

}


elseif ($actionContext->getForm()=="EXPORT_BATCH") {
//	$exportTarget = $_REQUEST['TARGET'];
//	$idList = $_REQUEST['IDLIST'];
	$datad = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'DATAD' );
	$datal = wi400Detail::getDetailValue ( $azione . "_PARAMETRI", 'DATAL' );
	if (isset($_REQUEST['IDLIST'])){
		$wi400List = new wi400List();
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		$export = new wi400ExportList("ALL", $wi400List);
		
		$export->prepare();
		
		$sql = $export->get_query();
		
		$rowsSelectionArray = array();
		$rowsSelectionArray = $export->get_rowsSelectionArray();
		
		$pageRows = $export->get_PageRows();
		$startFrom = $export->getStartFrom();
		
		$rowsSelection = serialize($rowsSelectionArray);
//		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['IDLIST']);
	} else {
		// Query SQL
		$datad = dateViewToModel ( $datad );
		$datal = dateViewToModel ( $datal );
		$wi400List = new wi400List();
		$export = new wi400ExportList("ALL", $wi400List);
		$export->prepare();
		$sql = $export = "select LOGDTA,LOGCDE,LOGPST,LOGCUM,LOGCDA,LOGFTC,LOGINI,LOGCAR,LOGSCA,LOGFIN,LOGQTS,LOGDLT,LOGINC,LOGNOT from FLOGDESC where logdta between $datad and $datal";		
		$rowsSelectionArray = array();		
		$pageRows = $export = ("10");
		$startFrom = $export = ("0");	
		$rowsSelection = serialize($rowsSelectionArray);
	}
	/*
		echo "SQL: $sql<br>";
	echo "<b>ROWS SELECTION ARRAY:</b> "; print_r($rowsSelectionArray); echo "<br>";
	echo "<b>SERIALIZED ROWS SELECTION ARRAY:</b> "; print_r($rowsSelection); echo "<br>";

	$rowsSelectionArray2 = unserialize($rowsSelection);
	echo "<b>UNSERIALIZED ROWS SELECTION ARRAY:</b> "; print_r($rowsSelectionArray2); echo "<br>";
	die();
	*/
	$username = $_SESSION['user'];

	$zip = "ZIP";
//	if(isset($_REQUEST['ZIP']) && !empty($_REQUEST['ZIP']))
//		$zip = $_REQUEST['ZIP'];

	$batch = new wi400Batch("ALLINEA");
	$batch->setAction("DESTOCKING_EXPORT");
	$batch->addParameter("DATA_VAL", $datad);
	$batch->addParameter("TARGET", "ALL");
	$batch->addParameter("FORMAT", "excel5");
	$batch->addParameter("QUERY", $sql);
	$batch->addParameter("SEL_ROWS", $rowsSelection);
	$batch->addParameter("PAGE_ROWS", $pageRows);
	$batch->addParameter("START_FROM", $startFrom);
	$batch->addParameter("NOTIFICA", "ALLEGATO");
	$batch->addParameter("ZIP", $zip);
	$batch->addParameter("USERNAME", $username);
	
	$batch->addParameter("USER_LOCALE", $_SESSION['locale']);
		
	$area_fun = "";
	if(isset($_SESSION["LOGIN_PROFILE"]['AREA']))
		$area_fun = $_SESSION["LOGIN_PROFILE"]['AREA'];
	$batch->addParameter("AREA_FUN", $area_fun);
	
	$batch->addParameter("name_job", "DESTOCKING_EXP");
	$batch->addParameter("des_job", "Destocking");
	
//	echo "ARRIVATO";
//	showArray($batch);
//	break;
	
    $result_batch = $batch->call($connzend);

	if (is_null($_REQUEST['IDLIST'])){
	$actionContext->gotoAction($azione,"DEFAULT","",true);
	}
//	$actionContext->setForm("DEFAULT");
}
