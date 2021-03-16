<?php

	$azione = $actionContext->getAction();

	if($actionContext->getForm()=="DEFAULT") {
		$user = $_SESSION['user'];
		
		$sql = "SELECT * FROM $users_table WHERE USER_NAME=?";
		//echo "SQL: $sql<br>";
		$stmt = $db->singlePrepare($sql,0,true);
		$rs = $db->execute($stmt, array($user));
		$row = $db->fetch_array($stmt);
	}
	if($actionContext->getForm()=="MODIFICA") {
		$language = wi400Detail::getDetailValue("chglng","LANGUAGE");
		$sql = "UPDATE SIR_USERS SET \"LANGUAGE\"='$language' WHERE USER_NAME='".$_SESSION['user']."'";
		$rs = $db->query($sql);
		if($rs) {
			$messageContext->addMessage("SUCCESS", "Lingua cambiata con successo.");
		}else {
			$messageContext->addMessage("ERROR", "Errore durante il cambio lingua.");
		}
		
		$actionContext->gotoAction($azione, "DEFAULT", "", true);
	}
?>
