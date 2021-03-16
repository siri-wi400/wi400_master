<?php

	if($actionContext->getForm()=="USER_PANEL") {
		$azioniDetail = $wi400GO->getObject('USER_DETAIL');
		$azioniDetail->addTab("user_6", "Estensioni");
/*		
		// Utente
		if($wi400GO->getObject('FORM_USER') != 'COPIA') {
			$codUsr = wi400Detail::getDetailValue("USER_SEARCH", "codusr");
		}else {
			$codUsr = wi400Detail::getDetailValue("COPY_USER", "codusr1");
		}
*/		
		// Estensione Utente
		$sql_ext = "SELECT * FROM $tipo_tab_ext WHERE USER_NAME=?";
		$stmt_ext = $db->singlePrepare($sql_ext,0,true);
		$result_ext = $db->execute($stmt_ext,array($codUsr));
		
		$soc_pr = "";
		$tipo_user = "";
		$user_pin = "";	
		$agente = "";
		$interlocutore = "";
		if($row_ext = $db->fetch_array($stmt_ext)) {
			$soc_pr = $row_ext['SOCPRP'];
			$tipo_user = $row_ext['TIPOUSR'];
			$agente = $row_ext['AGESPE'];
			$interlocutore = $row_ext['INTERLOCUTORE'];
			if(!empty($row_ext['USRPIN']))
				$user_pin = $row_ext['USRPIN'];
		}
		
		$scheda = 'user_6';
		
		// Società Tabella 17
		$tab_17_array = rtvUserAS400($codUsr);
		$soc_17 = $tab_17_array['CODICE'];
		
		$myField = new wi400Text('SOC_17');
		$myField->setLabel("Ente legato Tabella 17");
		$myField->setValue($soc_17." - ".$tab_17_array['DES_COD']);
		$azioniDetail->addField($myField, $scheda);
		
		// Società
		$myField = new wi400InputText('SOCPRP');
		$myField->setLabel("Ente legato");
//		$myField->addValidation('required');
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setInfo("Inserire l'ente legato");
		$myField->setCase("UPPER");
		if($soc_17!="")
			$myField->setReadonly(true);
//		$myField->setUserApplicationValue("SOCIETA");
		$myField->setValue($soc_pr);
		
		$decodeParameters = array(
			'TYPE' => 'ente',
			'CLASSE_ENTE' => '09;01',
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COLUMN' => 'MAFDSE',
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_ENTI");
		$myLookUp->addParameter("CLASSE", "09;01");
		$myLookUp->addField("SOCPRP");
		$myField->setLookUp($myLookUp);
		
		$azioniDetail->addField($myField, $scheda);
		
		// Tipo Utente Amministrativo
		$mySelect = new wi400InputSelect('TIPOUSR');
		$mySelect->setLabel("Tipo Utente Amministrativo");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($tipo_user_amm_array);
		$mySelect->setValue($tipo_user);
		$azioniDetail->addField($mySelect, $scheda);
		
		// PIN Sicurezza
		$myField = new wi400InputText('USRPIN');
		$myField->setLabel("Pin Sicurezza");
//		$myField->addValidation('required');
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setInfo("Inserire il PIN di sicurezza in caso di Utente ANCHE Amministrativo");
		$myField->setMask("0123456789");
//		$myField->setType('PASSWORD');
		$myField->setValue($user_pin);
		
		// Genera PIN
		$script = "jQuery.ajax({";
		$script .= 		"type: 'GET',";
		$script .= 		"url: _APP_BASE + APP_SCRIPT + '?t=".$azione."&f=GENERA_PIN&DECORATION=clean'";
		$script .= "}).done(function ( response ) {";
		$script .= 		"jQuery('#USRPIN').val(response);";
		$script .= "}).fail(function ( data ) {";			
		$script .= "});";
			
		$customTool = new wi400CustomTool();
//		$customTool->setScript("genera_pin('USRPIN', '".$azione."')");
		$customTool->setScript($script);
		$customTool->setIco("themes/common/images/table-select-row.png");
//		$customTool->setIco("themes/common/images/arrow_curve.png");

		$myField->addCustomTool($customTool);
		
		$azioniDetail->addField($myField, $scheda);
		// Codice Agente Specialista
		// Unit� di misura
		$myField = new wi400InputText('AGENTE');
		$myField->setValue($agente);
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setInfo("Digitare il codice dell'agente/specialista");
		$myField->setLabel("Agente/Specialista");
		$decodeParameters = array(
				'TYPE' => 'table',
				'TABLE' => '0069',
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp =new wi400LookUp("LU_TABELLA");
		$myLookUp->addParameter("TABELLA","0069");
		$myField->setLookUp($myLookUp);
		$azioniDetail->addField($myField, $scheda);
		
		// Interlocutore
		$myField = new wi400InputText('INTERLOCUTORE');
		$myField->setValue($interlocutore);
		$myField->setMaxLength(6);
		$myField->setSize(6);
		$myField->setInfo("Digitare il codice intlocurotre");
		$myField->setLabel("Interlocutore");	
		$azioniDetail->addField($myField, $scheda);
		
		// Salva
		/*$myButton = new wi400InputButton('SAVE_BUTTON_ESTENSIONI');
		$myButton->setLabel("Salva Estensioni Utente");
		$myButton->setAction($azione);
		$myButton->setForm("SAVE");
		$myButton->setValidation(True);
		$azioniDetail->addButton($myButton, $scheda);*/
		
		
		$js = "<script>
					function salvaEstensioni(callback) {
						if(typeof(callback) == 'undefined') callback = function() {};
				
						jQuery.ajax({  
							type: 'GET',
							url: _APP_BASE + APP_SCRIPT + '?t=ESTENSIONE_UTENTI_VEGA&f=SAVE&DECORATION=clean',
							data: {
								'CODUSR': jQuery('#codusr').val(), 
								'USER_DETAIL': {
									'SOCPRP': jQuery('#SOCPRP').val(),
									'TIPOUSR': jQuery('#TIPOUSR').val(),
									'USRPIN': jQuery('#USRPIN').val(),
									'AGENTE': jQuery('#AGENTE').val(),
								}
							}
						}).done(function ( response ) {  
							var dati = JSON.parse(response);
							if(dati.error.length) {
								alert(dati.error.join('\\n'));
							}else {
								callback();
							}
						}).fail(function ( data ) {  
							alert('fail salvataggio estensioni');
						});
					}
					
					var saveButton = jQuery('#SAVE_BUTTON');
					var clickAggiorna = saveButton.attr('onclick');
					saveButton.attr('onclick', 'salvaEstensioni(function() { '+clickAggiorna+' })');
				</script>";
		
//		$wi400GO->addObject('JS_BUTTON_AGGIORNA', $js);
		
		
	}