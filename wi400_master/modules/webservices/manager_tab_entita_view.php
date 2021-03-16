<?php
	
	if($form=="DEFAULT") {
		$miaLista = new wi400List($azione."_ENTITA_LIST", TRUE);
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setIncludeFile("webservices", "manager_tab_entita_function.php");
	
		//$miaLista->setQuery("select * from faentita");
		$miaLista->setField("AENCOD, AENDCO, AENSTA");
		$miaLista->setFrom("FAENTITA");
		//$miaLista->setAutoRowNumber(1);
		//$miaLista->setOrder("ANACOD");
	
		$desc_col = new wi400Column("AENDCO", "Descrizione");
		$desc_col->setActionListId($azione."_DETTAGLIO");
		
		$stato_col = new wi400Column("AENSTA", "Stato");
		$stato_col->setDecorator("YES_NO_ICO");
		
		$dettaglio_col = new wi400Column("DETTAGLIO", "Dettaglio", "", "CENTER");
		$dettaglio_col->setDecorator("ICONS");
		$dettaglio_col->setDefaultValue("SEARCH");
		$dettaglio_col->setActionListId($azione."_DETTAGLIO");
		$dettaglio_col->setSortable(false);
		$dettaglio_col->setExportable(false);
		
		$modifica_col = new wi400Column("MODIFICA", "Modifica", "", "CENTER");
		$modifica_col->setDecorator("NOTE_ICONS");
		$modifica_col->setDefaultValue("1");
		$modifica_col->setActionListId("MOD_REC");
		$modifica_col->setSortable(false);
		$modifica_col->setExportable(false);
		
		$ult_col = new wi400Column("ULT_RICHIAMO", "Ult chiamata", "TIMESTAMP");
		$ult_col->setDefaultValue('EVAL:get_last_call($row["AENCOD"])');
	
		$miaLista->setCols(array(
				$dettaglio_col,
				$modifica_col,
				new wi400Column("AENCOD", "Codice"),
				$desc_col,
				$stato_col,
				$ult_col
		));
	
		$miaLista->addKey("AENCOD");
		$miaLista->addKey("AENDCO");
	
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_REC");
		$action->setLabel("Nuovo");
		if($enable_scheda_parametri) {
			$action->setTarget("WINDOW", $width_window, $height_window);
		}else {
			$action->setTarget("WINDOW");
		}
		$action->setSelection("NONE");
		$miaLista->addAction($action);
	
		$action = new wi400ListAction();
		$action->setId("MOD_REC");
		$action->setAction($azione);
		$action->setForm("MOD_REC");
		$action->setLabel("Modifica");
		if($enable_scheda_parametri) {
			$action->setTarget("WINDOW", $width_window, $height_window);
		}else {
			$action->setTarget("WINDOW");
		}
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Dettaglio entità
		$action = new wi400ListAction();
		$action->setId($azione."_DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("SEGMEN_LIST");
		$action->setLabel("Dettaglio entità");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
	
		listDispose($miaLista);
	}
	else if(in_array($form, array("NEW_REC", "MOD_REC"))) {
	//else if($form == "NEW_REC") {
		$idDetail = $azione."_".$form."_DET";
		$actionDetail = new wi400Detail($idDetail, true);
		
		$actionDetail->addTab("scheda_1", "Dati base");
		if($enable_scheda_parametri) 
			$actionDetail->addTab("scheda_2", "Parametri");
		$readonly = false;
		
		if($form == "MOD_REC") {
			$codice = $key_entita['AENCOD'];
			$readonly = true;
			$field_hidden = "";
			
			if($enable_fields) {
				$field_hidden = ", AENLIB, AENCDA, AENRVE, AENRSC";
			}
			
			$sql = "select AENCOD, AENDCO, AENSTA$field_hidden from FAENTITA where AENCOD='$codice'";
			$res = $db->singleQuery($sql);
			$row = $db->fetch_array($res);
			
			$stat = false;
			if($row['AENSTA']) {
				$stat = true; 
			}
			
			$actionDetail->setSource($row);
		}else {
			$stat = true;
		}
		
		$tabId = "scheda_1";
		
		// Codice
		$myField = new wi400InputText('AENCOD');
		$myField->setLabel("Codice");
		$myField->setMask("0123456789");
		//$myField->setValue($key_entita['AENCOD']);
		$myField->addValidation('required');
		$myField->setCase('UPPER');
		$myField->setReadonly($readonly);
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$actionDetail->addField($myField, $tabId);
		
		// Descrizione
		$myField = new wi400InputText('AENDCO');
		$myField->setLabel("Descrizione");
		$myField->addValidation('required');
		$myField->setMaxLength(40);
		$myField->setSize(40);
		$actionDetail->addField($myField, $tabId);
		
		if($enable_fields) {
			//Libreria AENLIB
			$decodeParameters = array(
					'TYPE' => 'i5_object',
					'OBJTYPE' => '*LIB',
					'AJAX' => true
			);
			$myField = new wi400InputText('AENLIB');
			$myField->setLabel("Libreria");
			$myField->setDecode($decodeParameters);
			$actionDetail->addField($myField, $tabId);
			
			//Coda dati AENCDA
			$decodeParameters = array(
					'TYPE' => 'i5_object',
					'OBJTYPE' => '*DTAQ',
					'AJAX' => true
			);
			$myField = new wi400InputText('AENCDA');
			$myField->setLabel("Coda Dati");
			$myField->setDecode($decodeParameters);
			$actionDetail->addField($myField, $tabId);
			
			//Routine Verifica Completezza AENRVE
			$decodeParameters = array(
					'TYPE' => 'i5_object',
					'OBJTYPE' => '*PGM',
					'AJAX' => true
			);
			$myField = new wi400InputText('AENRVE');
			$myField->setLabel("Routine Verifica Completezza");
			$myField->setDecode($decodeParameters);
			$actionDetail->addField($myField, $tabId);
			
			//Routine Scarico Informazioni AENRSC
			$myField = new wi400InputText('AENRSC');
			$myField->setLabel("Routine Scarico Informazioni");
			$myField->setDecode($decodeParameters);
			$actionDetail->addField($myField, $tabId);
		}
		
		// Stato
		$myField = new wi400InputSwitch("AENSTA");
		$myField->setLabel("Stato");
		$myField->setOnLabel(_t('VALIDO'));
		$myField->setOffLabel(_t('ANULLATO'));
		$myField->setChecked($stat);
		$myField->setValue("S");
		$actionDetail->addField($myField, $tabId);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($form=="MOD_REC")
			$myButton->setForm("UPDT_REC");
		else if($form=="NEW_REC")
			$myButton->setForm("INS_REC");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->addParameter("CLEAN_DETAIL", $idDetail);
		$actionDetail->addButton($myButton);
		
		if($enable_scheda_parametri) {
			create_detail_parametri($actionDetail, "FWSDPARM", $parametri_testata, $form == "MOD_REC" ? true : false, $key_entita[0]);
		}
		
		$actionDetail->dispose();
	}
	else if($form=="SEGMEN_LIST") {
		$ListDetail = new wi400Detail($azione."_".$form."_DET");
		$ListDetail->setColsNum(1);
		
		// Codice Entità
		$fieldDetail = new wi400Text("COD_ENT");
		$fieldDetail->setLabel("Entità");
		$fieldDetail->setValue($key_entita['AENCOD']);
		$ListDetail->addField($fieldDetail);
		
		// Descrizione entità
		$fieldDetail = new wi400Text("DESC_ENT");
		$fieldDetail->setLabel("Descrizione");
		$fieldDetail->setValue($key_entita['AENDCO']);
		$ListDetail->addField($fieldDetail);
		
		$ListDetail->dispose();
		
		echo "<br/>";
		
		$miaLista = new wi400List($azione."_SEGMEN_LIST", true);
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setIncludeFile("webservices", "manager_tab_entita_function.php");
		
		$miaLista->setField("ASEENT, ASECOD, ASEDCO, ASESTA, ASEPRM");
		$miaLista->setFrom("FAENTITA a, FASEGMEN b");
		$miaLista->setWhere("a.aencod=b.aseent and a.aencod='".$key_entita['AENCOD']."'");
		//$miaLista->setOrder("ANACOD");
		
		$stato_col = new wi400Column("ASESTA", "Stato", "", "CENTER");
		$stato_col->setDecorator("YES_NO_ICO");
		
		$modifica_col = new wi400Column("MODIFICA", "Modifica", "", "CENTER");
		$modifica_col->setDecorator("NOTE_ICONS");
		$modifica_col->setDefaultValue("1");
		$modifica_col->setActionListId("MOD_SEGMEN");
		$modifica_col->setSortable(false);
		$modifica_col->setExportable(false);
		
		$ult_col = new wi400Column("ULT_RICHIAMO", "Ult chiamata", "TIMESTAMP");
		$ult_col->setDefaultValue('EVAL:get_last_call("'.$key_entita['AENCOD'].'", $row["ASECOD"])');
		
		$miaLista->setCols(array(
				$modifica_col,
				new wi400Column("ASECOD", "Codice"),
				new wi400Column("ASEDCO", "Descrizione"),				
				new wi400Column("ASEPRM", "Metodo", "STRING", "CENTER"),
				$stato_col,
				$ult_col
		));
		
		$miaLista->addKey("ASECOD");
		$miaLista->addKey("ASEENT");
		/*$hiddenField = new wi400InputHidden("AENCOD");
		$hiddenField->setValue($keyArray['AENCOD']);
		$hiddenField->dispose();*/
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_SEGMEN");
		$action->setLabel("Nuovo");
		if($enable_scheda_parametri) {
			$action->setTarget("WINDOW", $width_window, $height_window);
		}else {
			$action->setTarget("WINDOW");
		}
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setId("MOD_SEGMEN");
		$action->setAction($azione);
		$action->setForm("MOD_SEGMEN");
		$action->setLabel("Modifica");
		if($enable_scheda_parametri) {
			$action->setTarget("WINDOW", $width_window, $height_window);
		}else {
			$action->setTarget("WINDOW");
		}
		
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if(in_array($form, array("NEW_SEGMEN", "MOD_SEGMEN"))) {
		
		//showArray($_REQUEST);
		
		$idDetail = $azione."_".$form."_DET";
		$actionDetail = new wi400Detail($idDetail, true);
		$actionDetail->addTab("scheda_1", "Dati base");
		if($enable_scheda_parametri) {
			$actionDetail->addTab("scheda_2", "Parametri");
			$actionDetail->addTab("scheda_3", "Input/Output");
		}
		
		$tabId = "scheda_1";
		
		$readonly = false;
		$stat = false;
		$aut = false; // Autenticazione
		$option = array("" => "Standard", "D" => "DS", "S" => "String", "U"=> "User space", "M" => "Multirecord");
		$check_runphp_scrittura = false;
		
		if($form == "MOD_SEGMEN") {
			$codice = $key_segmento['ASECOD'];
			$readonly = true;

			$sql = "select * from FASEGMEN where ASEENT='".$key_entita[0]."' and ASECOD='$codice'";
			$res = $db->singleQuery($sql);
			$row = $db->fetch_array($res);
			
			if($row['ASESTA']) {
				$stat = true; 
			}
			if($row['ASEAUT'] == 'S') {
				$aut = true;
			}
			
			$check_runphp = false;
			if($row['ASERIN'] == "*RUNPHP") {
				$row['ASERIN'] = "";
				$check_runphp = true;
			}
			
			if($row['ASERSD'] == "*RUNPHP") {
				$row['ASERSD'] = "";
				$check_runphp_scrittura = true;
			}
			
			$actionDetail->setSource($row);
		}else {
			$stat = true;
		}
		
		// Codice
		$myField = new wi400InputText('ASECOD');
		$myField->setLabel("Codice");
		$myField->setMask("0123456789");
		//$myField->setValue($key_segmento['AENCOD']);
		$myField->addValidation('required');
		$myField->setCase('UPPER');
		$myField->setReadonly($readonly);
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$actionDetail->addField($myField, $tabId);
		
		// Descrizione
		$myField = new wi400InputText('ASEDCO');
		$myField->setLabel("Descrizione");
		$myField->addValidation('required');
		$myField->setMaxLength(40);
		$myField->setSize(40);
		$actionDetail->addField($myField, $tabId);
		
		//PHP script
		$myField_php_script = new wi400InputText('ASEPHP');
		$myField_php_script->setLabel("Script PHP");
		$myField_php_script->setSize(40);
		
		//Routine innesco coda dati ASERCD
		$decodeParameters = array(
				'TYPE' => 'i5_object',
				'OBJTYPE' => '*PGM',
				'AJAX' => true
		);
		$myField = new wi400InputText('ASERCD');
		$myField->setLabel("Rount. inn. coda dati");
		$myField->setDecode($decodeParameters);
		$actionDetail->addField($myField, $tabId);
			
		//Rountine reperimento info ASERIN
		$myField_rout_info = new wi400InputText('ASERIN');
		$myField_rout_info->setLabel("Rout. reperimento info");
		$myField_rout_info->setDecode($decodeParameters);
		
		// Routine PHP check box
		$myField = new wi400InputCheckbox('ROUTINE_PHP');
		if(isset($row['ASERIN']) && $row['ASERIN'] == "" && $check_runphp) {
			$myField->setChecked(true);
			$myField_rout_info->setReadonly(true);
		}else {
			$myField->setChecked(false);
			$myField_php_script->setReadonly(true);
		}
		$myField->setOnChange($onChange);
		$myField->setLabel("Routine PHP");
		
		$actionDetail->addField($myField_rout_info, $tabId);
		$actionDetail->addField($myField, $tabId);
			
		//Rountine reperimento destinat ASERDE

		$myField = new wi400InputText('ASERDE');
		$myField->setLabel("Rout. reperimento destinat");
		$myField->setDecode($decodeParameters);
		$actionDetail->addField($myField, $tabId);
		
		//Rountine scrittura dati ASERSD
		$myField_rout_dati = new wi400InputText('ASERSD');
		$myField_rout_dati->setLabel("Rout. scrittura dati");
		$myField_rout_dati->setDecode($decodeParameters);
		
		// Routine PHP check box
		$myField = new wi400InputCheckbox('ROUTINE_SCRITTURA_PHP');
		if(isset($row['ASERSD']) && $row['ASERSD'] == "" && $check_runphp_scrittura) {
			$myField->setChecked(true);
			$myField_rout_dati->setReadonly(true);
		}
		$myField->setOnChange($onChange_dati);
		$myField->setLabel("Routine scrittura PHP");
		
		$actionDetail->addField($myField_rout_dati, $tabId);
		$actionDetail->addField($myField, $tabId);
		
		if($form=="MOD_SEGMEN") {
			// Data ult estrazione
			$myField = new wi400InputText('ASEDUL');
			$myField->setLabel("Data ult estrazione");
			$myField->setReadonly(true);
			$myField->setSize(20);
			$actionDetail->addField($myField, $tabId);
			
			// Ora ult estrazione
			$myField = new wi400InputText('ASEHUL');
			$myField->setLabel("Ora ult estrazione");
			$myField->setReadonly(true);
			$myField->setSize(20);
			$actionDetail->addField($myField, $tabId);
		}
		
		// Numero chiavi di rottura xml
		$myField = new wi400InputText('ASENUK');
		$myField->setLabel("Numero chiavi");
		$myField->setMaxLength(2);
		$myField->setSize(10);
		$actionDetail->addField($myField, $tabId);
		
		// Sequenza chiavi
		$myField = new wi400InputText('ASEKEY');
		$myField->setLabel("Sequenza chiavi");
		$myField->setMaxLength(20);
		$myField->setSize(20);
		$actionDetail->addField($myField, $tabId);
		
		// Id chiavi di rottura
		$myField = new wi400InputText('ASESTK');
		$myField->setLabel("Id chiavi");
		$myField->setSize(20);
		$actionDetail->addField($myField, $tabId);
		
		// Script php
		
		$actionDetail->addField($myField_php_script, $tabId);
		
		// Metodo
		$mySelect = new wi400InputSelect('ASEPRM');
		$mySelect->setLabel("Metodo");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setValidations("required");
		$mySelect->setOptions($option);
		$actionDetail->addField($mySelect, $tabId);
		
		// Stato
		$myField = new wi400InputSwitch("ASESTA");
		$myField->setLabel("Stato");
		$myField->setOnLabel(_t('VALIDO'));
		$myField->setOffLabel(_t('ANULLATO'));
		$myField->setChecked($stat);
		$myField->setValue("S");
		$actionDetail->addField($myField, $tabId);
		
		// Autenticazione
		$myField = new wi400InputSwitch("ASEAUT");
		$myField->setLabel("Autenticazione");
		$myField->setOnLabel(_t('SI'));
		$myField->setOffLabel(_t('NO'));
		$myField->setChecked($aut);
		$myField->setValue("S");
		$actionDetail->addField($myField, $tabId);
		
		if($enable_scheda_parametri) {
			create_detail_parametri($actionDetail, "FWSDPARM", $parametri_testata, $form == "MOD_SEGMEN" ? true : false, $key_entita[0], $key_segmento[0]);
			
			//Dettaglio
			$miaLista = new wi400List($azione."_IO_PARAM", true);
			$miaLista->setSubfile($subfile);
			$miaLista->setIncludeFile("webservices", "manager_tab_entita_function.php");
			$miaLista->setAutoUpdateList(true);
			//$miaLista->setUpdateOnChangeRow(true);
			$miaLista->setCallBackFunction("validationRow", "functionValidationRowParametriIO");
			//$miaLista->setCallBackFunction("validation", "functionValidationCosti");
			$miaLista->setCallBackFunction("updateRow", "functionUpdateRowParametriIO");
			
			$del_col = new wi400Column("ELIMINA_RIGA", "Elimina", "", "CENTER");
			$inputField = new wi400InputCheckbox("ELIMINA_RIGA_I");
			$inputField->setValue("1");
			$inputField->setUncheckedValue("0");
			$del_col->setInput($inputField);
			$del_col->setSortable(false);
			
			$origine_col = new wi400Column("ASEORI", "Origine");
			$myField = new wi400InputSelect("SELECT_ORI");
			$myField->setFirstLabel("Seleziona");
			$myField->addOption("Attributo", "ATR");
			$myField->addOption("Parametro", "PARM");
			$origine_col->setInput($myField);
			
			$miaLista->setCols(array(
				$del_col,
				new wi400Column('ASESEQ', 'Sequenza'),
				//new wi400Column('ASEMET', 'Metodo'),
				new wi400Column('ASETYP', 'Tipo'),
				new wi400Column('ASENAM', 'Nome origine'),
				new wi400Column('ASENA2', 'Nome destinazione'),
				new wi400Column('ASEDES', 'Descrizione', "", "left", "", false),
				$origine_col,
				new wi400Column('ASEGET', 'Funzione rep. parametri'),
				new wi400Column('ASEDFT', 'Default')
			));
			
			$miaLista->addKey("NREL");
			$miaLista->addKey("ASEENT");
			$miaLista->addKey("ASECOD");
			
			foreach ($miaLista->getCols() as $id => $col) {
				if(in_array($id, array("ELIMINA_RIGA", "ASEORI"))) continue;
				if($id == "ASETYP") {
					$myField = new wi400InputSelect("SELECT_".$id);
					$myField->setFirstLabel("Seleziona");
					$myField->addOption("INPUT", "INPUT");
					$myField->addOption("OUTPUT", "OUTPUT");
				}else {
					$myField = new wi400InputText("INPUT_".$id);
					if(isset($size_lista_io[$id])) {
						$myField->setSize($size_lista_io[$id]);
					}
				}
				
				$col->setInput($myField);
			}
			
			$actionDetail->addField($miaLista, "scheda_3");
			//$miaLista->dispose();
		}
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($form=="MOD_SEGMEN")
			$myButton->setForm("UPDT_SEGMEN");
		else if($form=="NEW_SEGMEN")
			$myButton->setForm("INS_SEGMEN");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->addParameter("CLEAN_DETAIL", $idDetail);
		$actionDetail->addButton($myButton);
		
		if($enable_scheda_parametri) {
			$myButton = new wi400InputButton('ENTITA_PARAMETRI_BUTTON');
			$myButton->setLabel("Paramenti entità");
			$myButton->setAction($azione);
			$myButton->setForm("PARAMETRI_ENTITA");
			$myButton->setTarget("WINDOW");
			$actionDetail->addButton($myButton);
		}
		
		$actionDetail->dispose();
	}else if($form == "PARAMETRI_ENTITA") {
		
		$detail = new wi400Detail($azione."_PARAM_ENTITA", true);
		
		while($row = $db->fetch_array($rs)) {
			//echo $row['ASEPRM'].": ".$row['ASEVAL']."<br/>";
			$myField = new wi400Text($row['ASEPRM'], $row['ASEPRM'], $row['ASEVAL']);
			$detail->addField($myField);
		}
		
		$detail->dispose();
	}else if($form == "LIST_SOAP_ACTION") {
		$url = $_REQUEST['WSDL'];
		//showArray($_REQUEST);
	
		try {
			$params = array();
			$client = new SoapClient($url,$params);

			$function = $client->__getFunctions();
			/*echo "<div style='font-size: 18px;'>";
			foreach ($function as $key => $val) {
				echo ($key+1).") ".$val."<br/>";
			}
			echo "</div>";*/
			
			$subfile->addParameter("AZIONI", $function);
			
			$miaLista = new wi400List($azione."_SOAP_ACTION", true);
			$miaLista->setSubfile($subfile);
			
			$miaLista->setCols(array(
				new wi400Column('AZIONE', 'Azioni')
			));
			
			$miaLista->addKey("AZIONE");
			
			$miaLista->setPassValue($_REQUEST['CAMPO']);
			
			$miaLista->dispose();
			//showArray($function); 
		} catch (SoapFault $exception) {
			echo "Errore wsdl";
		}
	}else if ($actionContext->getForm() == "PASS_VALUE") {
		$key = getListKeyArray($azione."_SOAP_ACTION");
?>
		<script type="text/javascript">
			passValue('<?=$key['AZIONE']?>', '<?=$_REQUEST['CAMPO']?>');

			closeLookUp();
		</script>
<?
	}
	
	if(in_array($form, array("NEW_REC", "MOD_REC", "NEW_SEGMEN", "MOD_SEGMEN"))) {
?>
		<script type="text/javascript">
			function checkWsdl(id) {
				console.log(jQuery("#"+id));
				var wsdl = jQuery("#"+id).val();
				jQuery.ajax({  
					type: "GET",
					url: _APP_BASE + APP_SCRIPT + "?t="+CURRENT_ACTION+"&f=AJAX_CHECK_WSDL&DECORATION=clean&WSDL="+btoa(wsdl)
				}).done(function (response) {  
					alert(response);
				}).fail(function (data) {  
					alert("Ajax in errore");
				});
			}
		</script>
	
<?php 
	}
	
?>