<?php

if (isset($_REQUEST['IDDETAIL'])){

	$idDetails = $_REQUEST['IDDETAIL'];

	if (!is_array($idDetails)){
		$idDetails = array();
		$idDetails[] = $_REQUEST['IDDETAIL'];
	}
	
	if(isset($_REQUEST['DRAG_ID'])) {
		$idDrags = $_REQUEST['DRAG_ID'];
		if(count($idDrags) == 1 && isset($_SESSION[$idDrags[0]])) {
			wi400DragAndDrop::save($idDrags[0], $_SESSION[$idDrags[0]]);
		}
	}
    // Se è Un detail multitab in idDetail è presente il dettaglio più volte per ogni tab
	$idDetails = array_unique($idDetails);
	foreach($idDetails as $idDetail){
		if (existDetail($idDetail)){
			$detailSessionObj = getDetail($idDetail);
			$fieldsList = $detailSessionObj["FIELDS"];
			// Salvataggio stato di aperto o chiuso
			if (isset($_REQUEST[$idDetail."_STATUS"])) {
				$detailSessionObj['STATUS']= $_REQUEST[$idDetail."_STATUS"];
			}
		
			foreach($fieldsList as $fieldId => $field){
				
				if($field->getType() == 'HIDDEN' && get_class($field) == 'wi400InputHidden' && !$field->getDispose()) {
					//Mantengo il valore che era stato passato all'oggetto
					continue;
				}
				// Salto anche i campi che sono Protetti, nessuno dovrebbe modificarli in maschera
				if ($field->getReadOnly()==True) {
					continue;
				}

				$fieldName = $field->getName();
//				if (isset($_REQUEST[$fieldName])){
				if (isset($_REQUEST[$fieldId])){

					if ($field->getType() == "CHECKBOX"){
						$field->setChecked(true);
						$valore = $_REQUEST[$fieldId];
						if ($valore=="") $valore = True;
						$field->setValue($valore);
					}else{
//						$requestValue = $_REQUEST[$fieldName];
						$requestValue = $_REQUEST[$fieldId];
							
						if (in_array("double",$field->getValidations())===True){
							$requestValue = doubleViewToModel($requestValue);
						}
						$field->setValue($requestValue);

						// Se di tipo INPUT_TEXT e con valore user application data lo aggiorno
						if ($field->getType() == "INPUT_TEXT" && $field->getUserApplicationValue() != ""){
							if (!isset($_SESSION["USER_APPLICATION_DATA"])){
								$_SESSION["USER_APPLICATION_DATA"] = array();
							}
							$_SESSION["USER_APPLICATION_DATA"][$field->getUserApplicationValue()] = $field->getValue();
						}
					}

				}else if ($field->getType() == "FILE"){
					// upload di FILE
					if (isset($_FILES[$fieldName])){
						$field->setValue($_FILES[$fieldName]["name"]);
					}
				}else if ($field->getType() == "SELECT_CHECKBOX"){
					// select check box
					$array = $field->getOptions();
					$values = array();
					if (isset($array)) {	
						foreach ($array as $optionKey => $optionValue){ 
							if (isset($_REQUEST[$optionKey])){
								$values[] = $optionKey;
							}
						}
					}
					$field->setValue($values);
					
				}else{
					if ($field->getType() == "CHECKBOX"){
						// Tolgo check
						$field->setChecked(false);
					}else if ($field->getType() == "TEXT"){
						// Testo semplice
						$field->setValue("");
					} else {
						// Sbianco valori
						$field->setValue("");
					}
						
				}

			}
			// Path di prova per sovrascrittura su liste multiple del detail 
			if (isset($detailSessionObj["FIELDS"])) {
				$detailSessionObj["FIELDS"] = $fieldsList;
				saveDetail($idDetail, $detailSessionObj);
			}
		}
	}
	
	// VALIDATION
	if (isset($_REQUEST["DETAIL_VALIDATION"])){
		// Validazioni di base
		wi400Validation::validateForm($idDetail);
	
		// Validazione personalizzata
		if (isset($_POST["CURRENT_ACTION"]) && $_POST["CURRENT_ACTION"] != ""){
			$validationAction = rtvAzione(filter_input(INPUT_POST, "CURRENT_ACTION", FILTER_SANITIZE_STRING));
			if (isset($validationAction["VALIDATION"]) && $validationAction["VALIDATION"] != ""){
				// 	Valorizzo il form nell'actionContext per effettuare degli eventuali controlli
				if (isset($_POST["CURRENT_FORM"]) && $_POST["CURRENT_FORM"] != "") $actionContext->setForm($_POST["CURRENT_FORM"]);
				require_once p13n("modules/".$validationAction["MODULO"]."/".$validationAction["VALIDATION"]);
			}
		}
		
		// Controllo risultato validazione X Redirect se non è una azione AJAX di controllo
		if ($messageContext->getSeverity() == "ERROR" ){
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			  // Validation non da errori AJAX!!	
			} else {
				if (isset($_POST['CURRENT_ACTION']) && $_POST['CURRENT_ACTION'] != ""){
					$actionContext->setAction($_POST['CURRENT_ACTION']);
					if(isset($_POST['CURRENT_FORM'])){
						$actionContext->setForm($_POST['CURRENT_FORM']);
					}
				}
			}
		}
	}

}




?>