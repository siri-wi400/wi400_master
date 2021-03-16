<?
	if ($actionContext->getForm() == "SAVE"){
?>		
		<script>
		if (IFRAME_LOOKUP){
			if (top.document.getElementById("CURRENT_ACTION")
					&& top.document.getElementById("CURRENT_FORM")){
				top.doSubmit(top.document.getElementById("CURRENT_ACTION").value,
								top.document.getElementById("CURRENT_FORM").value);
			}else{
				top.location.href=top.location.href;
			}
			top.f_dialogClose();
		}else{
			if (window.opener.document.getElementById("CURRENT_ACTION")
					&& window.opener.document.getElementById("CURRENT_FORM")){
				window.opener.doSubmit(window.opener.document.getElementById("CURRENT_ACTION").value,
								window.opener.document.getElementById("CURRENT_FORM").value);
			}else{
				window.opener.location.href=window.opener.location.href;
			}
			self.close();
		}
		</script>
<?	
	}else if ($actionContext->getForm() == "DEFAULT"){
		$idDetail = "";
		$customValues = array();
		if (isset($_GET['IDDETAIL'])){
			// Recupero dati dettaglio
			$idDetail = $_GET['IDDETAIL'];
			$customValues = wi400Detail::loadCustomValues($idDetail);
		}else{
			echo "ERRORE GRAVE";
			exit();
		}
?>
<?
		$azioniDetail = new wi400Detail("MANAGEDETAIL");

		$mySelect = new wi400InputSelect('DEFAULT_DETAIL');
		$mySelect->setInfo('Indicare i valori da applicare.');
		$mySelect->setLabel("Valori di default");
		$mySelect->setFirstLabel("Nessun Valore");
		foreach ($customValues as $key => $value){
			if ($key != "DEFAULT_DETAIL"){
				$mySelect->addOption($key);
			}else{
				$mySelect->setValue($value);
			}
		}
		$azioniDetail->addField($mySelect);
		
		$myField = new wi400InputCheckbox('DELETE_DETAIL');
		$myField->setLabel("Elimina Valore Selezionato");
		$azioniDetail->addField($myField);

	$azioniDetail->addParameter("IDDETAIL", $idDetail);
		
	$azioniDetail->dispose();
	
	
	$myButton = new wi400InputButton("DETAIL_SAVE_BUTTON");
	$myButton->setAction("MANAGE_DETAIL");
	$myButton->setForm("SAVE");
	$myButton->setValidation(false);
	$myButton->setLabel("Conferma");
	$buttonsBar[] = $myButton;

	$myButton = new wi400InputButton("DETAIL_CANCEL_BUTTON");
	$myButton->setScript('closeLookUp()');
	$myButton->setLabel("Annulla");
	$buttonsBar[] = $myButton;

	}
?>