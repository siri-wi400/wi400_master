<?php

subfileDelete("WRKSPLFA");
$subfile = new wi400Subfile($db, "WRKSPLFA", $settings['db_temp']);
$subfile->setSql("*AUTOBODY");
$subfile->addParameter("SPOOL_PARAMETER", $desc);

$miaLista = new wi400List("WRKSPLFA", True);
$miaLista->setSubfile($subfile);

$miaLista->setFrom($subfile->getTable());
$miaLista->setOrder("SPOOLDATA DESC");

//$cols = getColumnListFromTable($subfile->getTableName(), "PHPTEMP");
$cols = getColumnListFromArray('WRKSPLFA','wrksplf');
$cols['SPOOLNUMBER']->setAlign('right');
$cols['SPOOLDATA']->setFormat('DATE');
$cols['FATTURA']->setAlign('right');
$inputField = new wi400InputText("USRDATA");
$inputField->setSize(10);
$cols['SPOOLUSRDATA']->setAlign("right");
$cols['SPOOLUSRDATA']->setSortable(false);
$cols['SPOOLUSRDATA']->setDescription("Dati utente");
$cols['SPOOLUSRDATA']->setInput($inputField);
$cols['SPOOLUSRDATA']->setDefaultValue("");
// Imposto le colonne sulle lista
$miaLista->setCols($cols);
// Numero lavoro lo voglio a Destra

// aggiunta chiavi di riga
$miaLista->addKey("SPOOLJOB");
$miaLista->addKey("SPOOLUSER");
$miaLista->addKey("SPOOLNUMBER");
$miaLista->addKey("SPOOLNAME");
$miaLista->addKey("SPOOLNBR");
$miaLista->addKey("SPOOLUSRDATA");
$miaLista->addKey("SPOOLMODULO");
$miaLista->addKey("FATTURA");
// Aggiunta filtri
$listFlt = new wi400Filter("SPOOLNAME");
$listFlt->setDescription("Nome Spool");
$miaLista->addFilter($listFlt);
$listFlt = new wi400Filter("SPOOLUSRDATA");
$listFlt->setDescription("Dati utente");
$miaLista->addFilter($listFlt);
$listFlt = new wi400Filter("SPOOLUSER");
$listFlt->setDescription("Utente");
$miaLista->addFilter($listFlt);
$listFlt = new wi400Filter("SPOOLJOB");
$listFlt->setDescription("Nome lavoro");
$miaLista->addFilter($listFlt);
$listFlt = new wi400Filter("FATTURA");
$listFlt->setDescription("Numero Fattura");
$listFlt->setFast(True);
$miaLista->addFilter($listFlt);
// Aggiunta azioni
$action = new wi400ListAction();
$action->setAction("TSPOOLVIEW");
$action->setLabel("Visualizza dettaglio");
$miaLista->addAction($action);
$action = new wi400ListAction();
$action->setAction("TSPOOLPDF");
$action->setLabel("Converti in formato PDF");
$action->setTarget("WINDOW");		
$miaLista->addAction($action);
// Cancellazione spool
$action = new wi400ListAction();
$action->setAction("DELETESPOOL");
$action->setLabel("Cancellazione spool");
$miaLista->addAction($action);
// Modifica dati utente
$action = new wi400ListAction();
$action->setAction("SPOOL_CHANGE");
$action->setLabel("Modifica dati utente");
$miaLista->addAction($action);

getMicroTimeStep("inizio dispose");
listDispose($miaLista);
getMicroTimeStep("fine dispose");
?>
