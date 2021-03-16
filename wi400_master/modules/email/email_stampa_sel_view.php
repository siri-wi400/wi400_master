<?php

	if(in_array($actionContext->getForm(), array("STAMPA_SEL", "STAMPA_TUTTO"))) {
		$actionDetail = new wi400Detail($azione.'_STAMPA_SEL_DET', True);
	
		$myField = new wi400InputText('OUTQ');
		$myField->setLabel("OUTQ");
		$myField->addValidation('required');
		$myField->setInfo(_t('SPOOL_OUTQ_INFO'));
		if(isset($outq) && $outq!="")
			$myField->setValue($outq);
	
//		$myField->setOnChange("check_duplex(this,'DUPLEX_SEL')");
		$myField->setOnChange("check_duplex()");
	
		$myLookUp = new wi400LookUp("LU_OBJECT");
		$myLookUp->addField("OUTQ");
		$myLookUp->addParameter("OBJTYPE", "*OUTQ");
		$myField->setLookUp($myLookUp);
	
		$decodeParameters = array(
			'TYPE' => 'i5_object',
			'OBJTYPE' => '*OUTQ',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
	
		$actionDetail->addField($myField);
	
		// Stampa Duplex
/*
		$myField = new wi400InputSwitch("DUPLEX_SEL");
		$myField->setLabel("Stampa Duplex");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setInfo("Stampa fronte/retro");
		$myField->setChecked($check_duplex);
		$actionDetail->addField($myField);
	
		$myField = new wi400InputCheckbox("DUPLEX_SEL");
		$myField->setLabel("Stampa Duplex");
		$myField->setChecked($check_duplex);
		$actionDetail->addField($myField);
*/
		$mySelect = new wi400InputSelect('DUPLEX_SEL');
		$mySelect->setLabel("Stampa Duplex");
		$mySelect->addOption("SI", "S");
		$mySelect->addOption("NO", "N");
		$mySelect->setValue($check_duplex);
		$actionDetail->addField($mySelect);
	
		$actionDetail->dispose();
	
		$myButton = new wi400InputButton("PRINT_BUTTON");
		$myButton->setAction("EMAIL_STAMPA");
		$myButton->setForm($actionContext->getForm());		
		if($actionContext->getForm()=="STAMPA_TUTTO") {
//			$myButton->setForm("STAMPA_TUTTO");
			$myButton->setConfirmMessage("Stampare Tutto?");
		}
		else if($actionContext->getForm()=="STAMPA_SEL") {
//			$myButton->setForm("STAMPA_SEL");
			$myButton->setConfirmMessage("Stampare?");
		}
		$myButton->setLabel("Stampa");
		$myButton->setValidation(true);
		$buttonsBar[] = $myButton;
	
		$myButton = new wi400InputButton("CLOSE_BUTTON");
//		$myButton->setScript('closeLookUp()');
		$myButton->setScript('closeWindow()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}

?>
<!--
<script>
function check_duplex(which, where){
	var fieldObj = document.getElementById(where);
	if (which.value != "LUCA") {
		fieldObj.value = true;
		fieldObj.className = "inputtext";
		fieldObj.readOnly = false;
	}
	else{
		fieldObj.value = false;
		fieldObj.className = "inputtextDisabled";
		fieldObj.readOnly = true;
	}
}
</script>
-->
<script>
function check_duplex(){
	var codaStampa = document.getElementById("OUTQ");
	if (codaStampa == ""){
		document.getElementById("DUPLEX_SEL").value = "";
	}else{
		new Ajax.Request(_APP_BASE + APP_SCRIPT + "?t=LOGCONV&f=CALCULATE&DECORATION=clean&OUTQ=" + codaStampa.value, { 
			method:'get',
			onSuccess: function(response){
				fieldObj = document.getElementById("DUPLEX_SEL");
//				fieldObj.value = response.responseText;
						
				if(response.responseText == "") {
					fieldObj.className = "inputtextDisabled";
					fieldObj.readOnly = true;
					fieldObj.value = "N";
				}
				else {
					fieldObj.className = "inputtext";
					fieldObj.readOnly = false;
					fieldObj.value = "S";
				}
    		}
  		});
	}
}
</script>