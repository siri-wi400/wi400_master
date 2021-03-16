<?php

//	echo "POST:<pre>"; print_r($_POST); echo "</pre>"; die();

	if($actionContext->getForm()=="FILTRI_LIST") {
		validation_filtri();
	}
	else if($actionContext->getForm()=="DELETE_FILTRI_LIST") {
		validation_delete_filtri();
	}
	
	function validation_filtri() {
		global $messageContext;
		
		if(isset($_POST['TO_USER']) && !empty($_POST['TO_USER'])) {
			$to_user_array = $_POST['TO_USER'];
		}
		
		$to_all = false;
		if(isset($_POST['TO_ALL']) && !empty($_POST['TO_ALL']) && $_POST['TO_ALL']=='1') {
			$to_all = true;
		}
		
		if(!empty($to_user_array) && $to_all===true) {
			$messageContext->addMessage("ERROR", "Passare i filtri a tutti o solo ad un elenco di utenti", "", true);
		}
		else if(empty($to_user_array) && $to_all===false) {
			$messageContext->addMessage("ERROR", "Passare i filtri a tutti o solo ad un elenco di utenti", "", true);
		}
	}
	
	function validation_delete_filtri() {
		global $messageContext;
	
		if(isset($_POST['TO_USER']) && !empty($_POST['TO_USER'])) {
			$to_user_array = $_POST['TO_USER'];
		}
	
		$to_all = false;
		if(isset($_POST['TO_ALL']) && !empty($_POST['TO_ALL']) && $_POST['TO_ALL']=='1') {
			$to_all = true;
		}
		
		$no_current = false;
		if(isset($_POST['NO_CURRENT']) && !empty($_POST['NO_CURRENT']) && $_POST['NO_CURRENT']=='1') {
			$no_current = true;
		}
	
		if(!empty($to_user_array) && $to_all===true) {
//			$messageContext->addMessage("ERROR", "Rimuovere i filtri da tutti o solo da un elenco di utenti", "", true);
			$messageContext->addMessage("ERROR", "Rimuovere i filtri da tutti, da un elenco di utenti o dall'utente in esame", "", true);
		}
/*		
		else if(empty($to_user_array) && $to_all===false) {
			$messageContext->addMessage("ERROR", "Rimuovere i filtri da tutti o solo da un elenco di utenti", "", true);
		}
*/		
		if($to_all===false && $no_current===true) {
			$messageContext->addMessage("ERROR", "'Non rimuovere da questo utente' selezionabile solo in caso di rimozione dat Tutti gli utenti", "", true);
		}
	}