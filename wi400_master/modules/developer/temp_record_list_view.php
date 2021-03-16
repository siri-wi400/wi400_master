<?php

$miaLista = new wi400List("TEMP_RECORD_LIST_LIST", True);
$miaLista->setFrom("$libreria".$settings['db_separator']."$tabella A");
$miaLista->setField("RRN(A) AS NREL, A.*");
$detail_create = False;

// Aggiunta colonna con numero relativo di record
$cols = getColumnListFromTable($tabella, $libreria);
$cols[] = new wi400Column ("NREL", "NUMERO RELATIVO RECORD" );
$miaLista->setCols($cols);
// Aggiunta dinamica filtri su tutti i campi
foreach($cols as $key=>$value) {
	$listFlt = new wi400Filter($key);
	$listFlt->setDescription($value->getDescription());
	if ($value->getAlign()=='right') $listFlt->setType("NUMERIC");
	else $listFlt->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
	$miaLista->addFilter($listFlt);
}
// Aggiunta Azioni
$action = new wi400ListAction();
$action->setAction("TEMP_RECORD_DETAIL");
$action->setLabel("Modifica Record");
$action->setTarget("WINDOW");
$miaLista->addAction($action);
// Aggiunta chiavi di riga
$miaLista->addKey("NREL");
$miaLista->addParameter("LIBRERIA", $libreria);
$miaLista->addParameter("TABELLA", $tabella);
listDispose($miaLista);
?>
