<?php
die("VALIDATION");
	if($actionContext->getForm()=="USER_PANEL") {
		validation_user_panel();
	}
	
	function validation_user_panel() {
		global $messageContext;
	
		$tipo_user = "";
		if(isset($_POST["TIPOUSR"]) && trim($_POST["TIPOUSR"])!="")
			$tipo_user = $_POST['TIPOUSR'];
		
		$user_pin = "";
		if(isset($_POST["USRPIN"]) && trim($_POST["USRPIN"])!="")
			$user_pin = $_POST['USRPIN'];
			
		if($tipo_user=="AAM" && empty($user_pin)) {
			$messageContext->addMessage("ERROR", "Inserire il PIN per Utente ANCHE Amministrativo", "USRPIN", true);
		}
	}