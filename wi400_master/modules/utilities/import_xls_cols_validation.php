<?php 

//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
//	echo "FORM ATTUALE: ".$actionContext->getForm()."<br>";
//	echo "FORM: ".$_REQUEST['f']."<br>";
//	echo "FILE: <pre>"; print_r($_FILES['IMPORT_FILE']); echo "</pre>";
//	die();
	if($_REQUEST['f']=="IMPORT") {
		if(!isset($_FILES['IMPORT_FILE']) || $_FILES['IMPORT_FILE']['name']=="") {
			$messageContext->addMessage("ERROR","File da importare non selezionato");
		}
		
		if(empty($_REQUEST['COLONNA']) && empty($_REQUEST['CAMPO'])) {
			if(empty($_REQUEST['COLONNA'])) {
				$messageContext->addMessage("ERROR","Impostare le colonne da importare");
			}
			if(empty($_REQUEST['CAMPO'])) {
				$messageContext->addMessage("ERROR","Impostare i campi da importare");
			}
		}
		else if(count($_REQUEST['COLONNA'])!=count($_REQUEST['CAMPO'])) {
			$messageContext->addMessage("ERROR","I parameteri Colonne e Campi non coincidono");
		}
		
		if(!isset($_REQUEST['START_ROW']) || $_REQUEST['START_ROW']=="" || $_REQUEST['START_ROW']<1) {
			$messageContext->addMessage("ERROR","Si deve partire almeno da riga 1 per importare i dati","START_ROW");
		}
	}

?>