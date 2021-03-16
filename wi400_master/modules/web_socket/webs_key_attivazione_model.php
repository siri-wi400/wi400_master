<?php
$azione = $actionContext->getAction();
$spacer = new wi400Spacer();

$array_stati = array(
		"Valido",
		"Disabilitato"
);

if($actionContext->getForm()=="DEFAULT") {
	$actionContext->setLabel("Attivazione Chiavi Web Socket");
	$history->addCurrent();
}
else if($actionContext->getForm()=="SAVE_WEB") {
	global $db;

//	$keyArray = getListKeyArray($azione."_LIST");
//	showArray($keyArray);
	$sql_upd = "update SIR_WEBS set WEBSTA = ? where 
	webid = ? and webusr = ?";
	
	$stmtUpd = $db->prepareStatement($sql_upd);

	$idList = $_GET['IDLIST'];
	$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);

	$righe= array();
	$qtaTOT=0;
	// Scrittura Righe
	foreach($wi400List->getSelectionArray() as $key => $value){
		$keyArray = array();
		$keyArray = explode("|",$key);
		
		if (isset($value['STATO']) && ($value['STATO']=="1" || $value['STATO']=="D")) {
			$result = $db->execute($stmtUpd, array($value['STATO'],$keyArray['0'],$keyArray['1']));
		}
	}
	if ($result==True) {
		$messageContext->addMessage("SUCCESS", "Stato Aggiornato");
	} else {
		$messageContext->addMessage("ERROR", "Stato non Aggiornato. ritentare l'operazione!");
	}

	$actionContext->gotoAction($azione, "DEFAULT", "",True);
}
else if($actionContext->getForm()=="DEL_WEB") {
	global $db;
	
	$keyArray = array();
	$keyArray = getListKeyArray($azione."_LIST");

	$sql_del = "delete SIR_WEBS where WEBTYP='STATIC' and
	webid = ? and webusr = ?";
	$stmtDel = $db->prepareStatement($sql_del);

	$idList = $_GET['IDLIST'];
	$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);

	// delete Righe
	foreach($wi400List->getSelectionArray() as $key => $value){
		$keyArray = explode("|",$key);
		$qtaStorno = 0;

			$result = $db->execute($stmtDel, array($keyArray['0'],$keyArray['1']));
	}
	if ($result==True) {
		$messageContext->addMessage("SUCCESS", "Eliminato");
	} else {
		$messageContext->addMessage("ERROR", "Non Eliminato. ritentare l'operazione!");
	}

	$actionContext->gotoAction($azione, "DEFAULT", "",True);
}