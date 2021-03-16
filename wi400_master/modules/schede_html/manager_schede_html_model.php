<?php

	require_once 'manager_schede_html_commons.php';

	$azione = $actionContext->getAction();

	$history->addCurrent();
	
	// Oggetto Folder TO
	$toThemeObj = array();
	
	$fldType    = "";
	
	if($actionContext->getForm() != "DEFAULT") {
		$argomento = wi400Detail::getDetailValue($azione."_ARGOMENTO", "OBJ_ARGO");
	}
	
	if($actionContext->getForm() == "AJAX_APPLICA_HTML") {
		$keyArray = getListKeyArray("TO_THEMES_LIST");
		
		$where = array("OBJ_ARGO" => $argomento, "FLD_TYPE" => sanitize_sql_string($keyArray['FLD_TYPE']));
		$stmt_html = $db->prepare("UPDATE", "ZFLDHTML", $where, array("FLD_TEMPLATE"));
		$rs = $db->execute($stmt_html, array(base64_decode($_REQUEST['HTML'])));
		
		$return = array("rs" => "ERROR", "testo" => "Errore aggiornamento html!");
		if($rs) {
			$return['rs'] = "SUCCESS";
			$return['testo'] = "Html aggiornato con successo!";
		}
		
		echo json_encode($return);
		
		die();
	}
	
	if($actionContext->getForm() == "DEFAULT") {
		$actionContext->setLabel("Argomento");
	}else if($actionContext->getForm() == "DETAIL") {
		
	}else if ($actionContext->getForm() == "UPDATE"){
			
		$keyArray = getListKeyArray("TO_THEMES_LIST");
		
		$objectTypes = getTipoScheda($argomento);
		
		if (isset($keyArray['FLD_TYPE'])){
			$fldType = $keyArray['FLD_TYPE'];
			// Recupero informazioni tema
			$sqlTheme = "SELECT * from ZFLDHTML
							WHERE FLD_TYPE = ".sanitize_sql_string($fldType);
		
			$result = $db->singleQuery($sqlTheme);
		
			if ($result){
				$toThemeObj = $db->fetch_array($result);
			}
		}
		
		$actionContext->setLabel("Modifica Tipo Scheda");
			
	}else if ($actionContext->getForm() == "NEW") {
		
		$actionContext->setLabel("Nuovo Tipo Scheda");
		$objectTypes = getTipoScheda($argomento);
				
	}else if ($actionContext->getForm() == "SAVE"){
		
		$keyArray = getListKeyArray("TO_THEMES_LIST");
		
		if (isset($_REQUEST["FLD_DYN"])){
			$fldDyn = 1;
		}else {
			$fldDyn = 0;
		}
		
		if (isset($keyArray[0]) && isset($_REQUEST["FLD_TYPE"])
					&& $_REQUEST["FLD_TYPE"] == $keyArray[0]) {

			$fldType = $keyArray[0];
			$keysName    = array("FLD_TYPE" => $fldType, "OBJ_ARGO" => $argomento);
			$campoUser = "USER";
			if ($db->type=="GENERIC_PDO") {
				$campoUser="[USER]";
			}
			$fieldsName  = array("FLD_DESC","OBJ_TYPE","FLD_TEMPLATE","FLD_DYN", $campoUser, "TMSMOD");
			$stmtupdate  = $db->prepare("UPDATE", "ZFLDHTML", $keysName, $fieldsName);
			$fieldsValue = array($_REQUEST["FLD_DESC"], $_REQUEST["OBJ_TYPE"], $_REQUEST["FLD_TEMPLATE"], $fldDyn, $_SESSION['user'], getDb2Timestamp());
			$result = $db->execute($stmtupdate, $fieldsValue);
			
			if ($result) {
				$messageContext->addMessage("SUCCESS","Tipo scheda modificato in modo corretto.");
			}else {
				$messageContext->addMessage("ERROR","Errori durante il salvataggio.");
			}
			$actionContext->onSuccess($azione, "DETAIL");
			$actionContext->onError("$azione", "UPDATE");
			
		}else {
			
			$fldType = getSequence("FLD_TYPE");
			$campoUser = "USER";
			if ($db->type=="GENERIC_PDO") {
				$campoUser="[USER]";
			}
			$fieldsName = array("OBJ_ARGO", "FLD_TYPE","FLD_DESC","OBJ_TYPE","FLD_TEMPLATE","FLD_DYN","THM_ORDER", $campoUser, "TMSMOD");
			$stmtinsert = $db->prepare("INSERT", "ZFLDHTML", null, $fieldsName);
			
			// ***********************************************************************			
			// Recupero ultimo inserito
			// ***********************************************************************
			$sqlLastFolder = "SELECT MAX(THM_ORDER) as MAX_ORDER from ZFLDHTML";
			$result = $db->singleQuery($sqlLastFolder);
			
			$fldOrder = 1;
			if ($result){
				$fldResult = $db->fetch_array($result);
				$fldOrder = $fldResult["MAX_ORDER"];
			}
			$fldOrder = $fldOrder + 1;
			// ***********************************************************************
			
			$fieldsValue = array($argomento, $fldType, $_REQUEST["FLD_DESC"],$_REQUEST["OBJ_TYPE"],$_REQUEST["FLD_TEMPLATE"],$fldDyn,$fldOrder, $_SESSION['user'], getDb2Timestamp('*INZ'));
			$result = $db->execute($stmtinsert, $fieldsValue);
			
			if ($result){
				$messageContext->addMessage("SUCCESS","Tipo scheda inserito in modo corretto.");
			}else{
				$messageContext->addMessage("ERROR","Errori durante il salvataggio.");
			}
			
			$actionContext->onSuccess($azione, "DETAIL");
			$actionContext->onError($azione, "NEW");
			
		}
		
	}else if($actionContext->getForm() == "DELETE") {
		$keyArray = getListKeyArray("TO_THEMES_LIST");
		
		$fieldsName = array("FLD_TYPE", "OBJ_ARGO");
		$stmtdelete = $db->prepare("DELETE", "ZFLDHTML", $fieldsName, null);
		
		// Cancello da tabella riferimento
		$fieldsValue = array($keyArray[0], $argomento);
		$result = $db->execute($stmtdelete, $fieldsValue);
		
		if ($result){
			$messageContext->addMessage("SUCCESS","Cancellazione tema effettuata");
		}else{
			$messageContext->addMessage("ERROR","Errore durante la cancellazione");
		}
		
		$actionContext->gotoAction($azione, "DETAIL", "", false, true);
	}else if($actionContext->getForm() == "ANTEPRIMA") {
		$toThemeObj = array();
		if (isset($_REQUEST["DETAIL_KEY"])){
			$fldType = $_REQUEST["DETAIL_KEY"];
		
			// Recupero informazioni tema
			$sqlTheme = "SELECT * from ZFLDHTML
							WHERE FLD_TYPE = ".sanitize_sql_string($fldType);
			$result = $db->singleQuery($sqlTheme);
		
			if ($result){
				$toThemeObj = $db->fetch_array($result);
		
				$pdfFileName = wi400File::getUserFile("tmp",$fldType."_template.pdf");
		
				require_once($routine_path.'/h2p/html2pdf.class.php');
				$html2pdf = new HTML2PDF('P','A4', 'it');
				$html2pdf->setTestTdInOnePage(false);
				$html2pdf->WriteHTML($toThemeObj["FLD_TEMPLATE"], isset($_GET['vuehtml']));
				$html2pdf->Output($pdfFileName, 'F');
			}
		}
		
		$actionContext->onSuccess("FILEVIEW&DECORATION=clean&APPLICATION=pdf&CONTEST=tmp&FILE_NAME=".$fldType."_template.pdf");
	}
?>