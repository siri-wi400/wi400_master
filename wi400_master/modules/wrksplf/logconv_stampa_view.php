<?php

	if(in_array($actionContext->getForm(), array("STAMPA_SEL", "STAMPA_SEL_TUTTO"))) {
		$actionDetail = new wi400Detail($azione.'_STAMPA_SEL_DET', True);
	
		$myField = new wi400InputText('OUTQ');
		$myField->setLabel("OUTQ");
		$myField->setInfo(_t('SPOOL_OUTQ_INFO'));
		if(isset($outq) && $outq!="")
			$myField->setValue($outq);
		$myField->setCase("UPPER");
		$myField->addValidation('required');
	
		$myField->setOnChange("check_duplex()");
	
		$myLookUp =new wi400LookUp("LU_OBJECT");
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
		$mySelect = new wi400InputSelect('DUPLEX_SEL');
		$mySelect->setLabel("Stampa Duplex");
		$mySelect->addOption("SI", "S");
		$mySelect->addOption("NO", "N");
		$mySelect->setValue($check_duplex);
		$actionDetail->addField($mySelect);
	
		if(isset($_REQUEST['DETAIL_KEY']))
			$actionDetail->addParameter("DETAIL_KEY", $_REQUEST['DETAIL_KEY']);
		if(isset($_REQUEST['IDLIST']))
			$actionDetail->addParameter("IDLIST", $_REQUEST['IDLIST']);
	
		$actionDetail->dispose();
	
		$myButton = new wi400InputButton("PRINT_BUTTON");
		$myButton->setAction($azione);
		if($actionContext->getForm()=="STAMPA_SEL") {
			$myButton->setForm("STAMPA");
			$myButton->setConfirmMessage("Stampare?");
		}
		else if($actionContext->getForm()=="STAMPA_SEL_TUTTO") {
			$myButton->setForm("STAMPA_TUTTO");
			$myButton->setConfirmMessage("Stampare TUTTO?");
		}
		$myButton->setValidation(true);
		$myButton->setLabel("Stampa");
		$buttonsBar[] = $myButton;
	
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
//		$myButton->setScript('closeWindow()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}
	else if($actionContext->getForm()=="CLOSE_WINDOW") {
		close_window();
	}
?>
<script>
	function check_duplex(){
		var codaStampa = document.getElementById("OUTQ");
		if (codaStampa == ""){
			document.getElementById("DUPLEX_SEL").value = "";
		}else{
			new Ajax.Request(_APP_BASE + APP_SCRIPT + "?t=LOGCONV_STAMPA&f=CALCULATE&DECORATION=clean&OUTQ=" + codaStampa.value, { 
				method:'get',
				onSuccess: function(response){
					fieldObj = document.getElementById("DUPLEX_SEL");
//					fieldObj.value = response.responseText;
					
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