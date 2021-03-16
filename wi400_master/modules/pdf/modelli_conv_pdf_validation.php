<?php

//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
//	echo "POST:<pre>"; print_r($_POST); echo "</pre>";

	if($actionContext->getForm()=="DEFAULT") {
		validation_default();
	}
	else if($actionContext->getForm()=="MODELLO_COPIA") {
		validation_copia();
	}
	
	function validation_default() {
		global $messageContext;
		
		$modello = "";
		if(isset($_POST['CODMOD_SRC']) && !empty($_POST['CODMOD_SRC']))
			$modello = $_POST['CODMOD_SRC'];
//		echo "VALIDATION - MODELLO: $modello<br>";
		
		$mod_cls = "";
		if(isset($_POST['MODCLS_SRC']) && !empty($_POST['MODCLS_SRC']))
			$mod_cls = $_POST['MODCLS_SRC'];
		
		if($modello!="" && $mod_cls!="") {
			$messageContext->addMessage("ERROR", "Filtrare o per 'Codice Modello' o per 'Classe di conversione'");
		}
		
		if($_REQUEST['f']=="MODELLO_COPIA" && $modello=="") {
			$messageContext->addMessage("ERROR", "Indicare il modello da copiare");
		}
	}
	
	function validation_copia() {
		global $db, $messageContext;
		
		$sql = "select * from SIR_MODULI where MODNAM ='".$_POST['CODMOD']."'";
//		echo "SQL: $sql<br>";

		$result = $db->singleQuery($sql);
		
		if($row = $db->fetch_array($result)) {
//			echo "ROW:<pre>"; print_r($row); echo "</pre><br>";
			$messageContext->addMessage("ERROR", "Codice già presente", "CODMOD", true);
		}
	}