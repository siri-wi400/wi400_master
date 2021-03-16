<?php

$subfile = new wi400Subfile($db, "TABLELIST", $settings['db_temp']);
$sql = "SELECT TABLE_NAME, TABLE_TEXT FROM systables WHERE SYSTEM_TABLE <>  
'Y' AND FILE_TYPE ='D' AND TABLE_TYPE IN('P', 'T') AND TABLE_SCHEMA='$libreria'";
$subfile->setSql($sql);
$subfile->addParameter("LIBRERIA", $libreria, True);

$miaLista = new wi400List("TABLELIST", True);
$miaLista->setSubfile($subfile);

$miaLista->setFrom($subfile->getTable());
$miaLista->setOrder("TABLENAME DESC");

$cols = getColumnListFromArray('TABLELIST');

$miaLista->setCols($cols);
// Numero lavoro lo voglio a Destra

// aggiunta chiavi di riga
$miaLista->addKey("TABLENAME");
$miaLista->addParameter("LIBRERIA", $libreria);
// Aggiunta filtri
$listFlt = new wi400Filter("TABLENAME");
$listFlt->setDescription("Nome Tabella");
$listFlt->setFast(True);
$miaLista->addFilter($listFlt);
// Aggiunta azioni
$action = new wi400ListAction();
$action->setAction("RECORDLIST");
$action->setLabel("Visualizza contenuto");
$miaLista->addAction($action);


listDispose($miaLista);
?>
