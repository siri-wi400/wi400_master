<?php 

	require_once 'query_tool_db_commons.php';
	
	$azione = $actionContext->getAction();
//	echo "FORM: ".$actionContext->getForm()."<br>";

	$idDetail = $azione."_SRC";
//	$idDetailMarkers = $azione."_MARKERS_SRC";

	$id_query_array = wi400Detail::getDetailValue($idDetail, 'ID_QUERY');
//	echo "ID QUERY: $id_query<br>";

	if(in_array($actionContext->getForm(), array("DEFAULT", "SELECT_FRAME", "EXECUTE_FRAME"))) {
		$history->addCurrent();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
		$actionContext->setLabel("Parametri");
		
		$where = "STATO='1'";
		$where .= " and ID_QUERY in (select ID_QUERY from USERQUERY where USER_NAME='$idUser' and STATO='1')";
	}
	else if(in_array($actionContext->getForm(), array("SELECT_FRAME", "EXECUTE_FRAME"))) {
	
	}