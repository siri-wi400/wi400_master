<?php

$subfile = new wi400Subfile($db, "LIBLIST", $settings['db_temp'], 10);
$array = array();
$array['LIBNAME']=$db->singleColumns("1", "10", "", "Libreria" );
$array['LIBDESCR']=$db->singleColumns("1", "50", "", "Descrizione" );

$subfile->inz($array);

require_once $routine_path."/os400/wi400Os400Object.cls.php";
$list = new wi400Os400Object("*LIB");
$list->getList();

while ($obj_read = $list->getEntry()) {

$dati = array( 
	$obj_read['NAME'],
	$obj_read['DESCRIP']
    			);   	
		$subfile->write($dati);
}
$subfile->finalize();
$miaLista = new wi400List("LIBLIST", !$isFromHistory);

$miaLista->setFrom($subfile->getTable());
$miaLista->setOrder("LIBNAME");

$cols = getColumnListFromTable($subfile->getTableName(), $settings['db_temp']);

$miaLista->setCols($cols);
// Numero lavoro lo voglio a Destra

// aggiunta chiavi di riga
$miaLista->addKey("LIBNAME");

// Aggiunta filtri
$toListFlt = new wi400Filter("LIBNAME");
$toListFlt->setDescription("Libreria");
$toListFlt->setFast(true);
//$toListFlt->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
$miaLista->addFilter($toListFlt);

$toListFlt = new wi400Filter("LIBDESCR");
$toListFlt->setDescription("Descrizione");
//$toListFlt->setFast(true);
$toListFlt->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
$miaLista->addFilter($toListFlt);

// Aggiunta azioni
$action = new wi400ListAction();
$action->setAction("TABLELIST");
$action->setLabel("Visualizza tabelle fisiche");
$miaLista->addAction($action);
listDispose($miaLista);
?>
