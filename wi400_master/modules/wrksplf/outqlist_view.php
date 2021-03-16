<?php

$subfile = new wi400Subfile($db, "OUTQLIST", $settings['db_temp'], 10);
$array = array();
$array['OUTQNAME']=$db->singleColumns("1", "10", "", "OutQ" );
$array['OUTQLIB']=$db->singleColumns("1", "10", "", "Libreria" );
$array['OUTQDESCR']=$db->singleColumns("1", "50", "", "Descrizione" );
$array['OUTQSPOOL']=$db->singleColumns("3", "7", "0", "Numero Spool" );

$subfile->inz($array);

require_once $routine_path."/os400/wi400Os400Object.cls.php";
require_once $routine_path."/os400/wi400Os400Spool.cls.php";

$list = new wi400Os400Object("*OUTQ");
$list->getList();
    
while ($obj_read = $list->getEntry()) {

$dati = wi400Os400Spool::getOutqInfo(str_pad($obj_read['NAME'], 10).$obj_read['LIBRARY']);	
	
$dati = array( 
	$obj_read['NAME'],
	$obj_read['LIBRARY'],
	$obj_read['DESCRIP'],
	$dati['QSPBCP']
    			);   	
		$subfile->write($dati);
}
$subfile->finalize();
$miaLista = new wi400List("OUTQLIST");

$miaLista->setFrom($subfile->getTable());
$miaLista->setOrder("OUTQNAME, OUTQLIB");
$miaLista->setEnableMovingWithKeys(True);
$cols = getColumnListFromTable($subfile->getTableName(), $settings['db_temp']);

$miaLista->setCols($cols);
// Numero lavoro lo voglio a Destra

// aggiunta chiavi di riga
$miaLista->addKey("OUTQNAME");
$miaLista->addKey("OUTQLIB");

// Aggiunta filtri
$toListFlt = new wi400Filter("OUTQNAME");
$toListFlt->setDescription("Coda di stampa");
$toListFlt->setFast(true);
$miaLista->addFilter($toListFlt);

// Aggiunta azioni
$action = new wi400ListAction();
$action->setAction("SPOOLLIST");
$action->setLabel("Visualizza contenuto");
$miaLista->addAction($action);
listDispose($miaLista);
?>
