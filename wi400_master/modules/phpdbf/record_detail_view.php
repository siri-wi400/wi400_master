<?php


	if (($actionContext->getForm() == "UPDATE" || $actionContext->getForm() == "INSERT" || $actionContext->getForm() == "DELETE") && $messageContext->getSeverity() != "ERROR"){
?>
		<script>
			if (IFRAME_LOOKUP){
				top.doPagination('RECORDLIST',_PAGE_RELOAD);
			}else{
				window.opener.doPagination('RECORDLIST',_PAGE_RELOAD);
			}
			closeLookUp();
		</script>
<?	
		exit();
	}

  $azioniDetail = new wi400Detail('RECORD_DETAIL');
  $azioniDetail->setSource($row);
  
  foreach ($result as $key=>$value) {

		$myField = new wi400InputText($key);
		$myField->setFromArray($result);
		$myField->setLabel($value['REMARKS']);
		$azioniDetail->addField($myField);
  }
 // Aggiunta bottoni
 	$myButton = new wi400InputButton('CANCEL_BUTTON');
	$myButton->setLabel("Annulla");
	$myButton->setScript("closeLookUp()");
	$myButton->setValidation(False);
	$azioniDetail->addButton($myButton);
 // Update
 	$myButton = new wi400InputButton('INSERT');
	$myButton->setLabel("Insert");
	$myButton->setAction("RECORD_DETAIL");
	$myButton->setForm("INSERT");
	$azioniDetail->addButton($myButton);
 // Insert
 	$myButton = new wi400InputButton('UPDATE');
	$myButton->setLabel("Aggiorna");
	$myButton->setAction("RECORD_DETAIL");
	$myButton->setForm("UPDATE");
	$azioniDetail->addButton($myButton);
 // Delete
 	$myButton = new wi400InputButton('DELETE');
	$myButton->setLabel("Cancella");
	$myButton->setAction("RECORD_DETAIL");
	$myButton->setForm("DELETE");
	$azioniDetail->addButton($myButton);
	
	$azioniDetail->addParameter("LIBRERIA", $libreria);
	$azioniDetail->addParameter("TABELLA", $tabella);
	
	
  	$azioniDetail->dispose();
?>
