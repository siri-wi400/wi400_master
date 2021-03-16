<?php 

	$spacer = new wi400Spacer();
	
	if($actionContext->getForm()=="DEFAULT") {
		// Gestione azioni
		$searchAction = new wi400Detail('SEARCH_ACTION', false);
		$searchAction->setTitle(_t("MANAGEACTIONS"));
		$searchAction->isEditable(true);
		
		// Gestione delle azioni
		$myField = new wi400InputText('codazi');
		$myField->setLabel(_t("CODE"));
//		$myField->addValidation("required");
		$myField->setCase("UPPER");	
		$myField->setMaxLength(40);
		//$myField->setValue($azione);
		$myField->setInfo(_t("ACTION_CODE_INFO"));
		
		$decodeParameters = array(
			'TYPE'=> 'common',
			'COLUMN' => 'DESCRIZIONE',
			'TABLE_NAME' => 'FAZISIRI',
			'KEY_FIELD_NAME' => 'AZIONE',
			'ALLOW_NEW' => True,	
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$myLookUp->addField("codazi");
		$myLookUp->addField("DATA");
		
//		$myLookUp->addField("codazi");
		$customTool = new wi400CustomTool("TRI_ANASOC", "NUOVO");
		$customTool->addParameter("RETURN_ID", "codazi");
		$customTool->addParameter("RETURN_DETAIL", "SEARCH_ACTION");
		$customTool->setIco("themes/common/images/table-select-row.png");
		$customTool->setTarget("WINDOW");
		$myField->addCustomTool($customTool);
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel(_t("SELECT"));
		$myButton->setAction($azione_corrente);
		$myButton->setForm("DETAIL");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
		
		$spacer->dispose();
		
		// Copia azioni
		$searchAction = new wi400Detail('COPY_ACTION', false);
		$searchAction->setTitle(_t("COPYACTIONS"));
		$searchAction->isEditable(true);
		$searchAction->setColsNum(2);
		
		// Azione da copiare
		$myField = new wi400InputText('codazi1');
		$myField->setLabel(_t("CODE"));
//		$myField->addValidation("required");
		$myField->setCase("UPPER");	
		$myField->setMaxLength(40);
		//$myField->setValue($azione_old);
		$myField->setInfo(_t("ACTION_CODE_INFO"));
		
		$decodeParameters = array(
			'TYPE'=> 'common',
			'COLUMN' => 'DESCRIZIONE',
			'TABLE_NAME' => 'FAZISIRI',
			'KEY_FIELD_NAME' => 'AZIONE',
//			'ALLOW_NEW' => True,	
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
//		$myLookUp->addField("codazi");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Nuova azione in cui copiare
		$myField = new wi400InputText('codazi2');
		$myField->setLabel(_t("CODE")." ".strtolower(_t("COPY")));
//		$myField->addValidation("required");
		$myField->setCase("UPPER");	
		$myField->setMaxLength(40);
		//$myField->setValue($azione_new);
		$myField->setInfo(_t("ACTION_CODE_INFO"));
/*		
		$decodeParameters = array(
			'TYPE'=> 'common',
			'COLUMN' => 'DESCRIZIONE',
			'TABLE_NAME' => 'FAZISIRI',
			'KEY_FIELD_NAME' => 'AZIONE',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
//		$myLookUp->addField("codazi");
		$myField->setLookUp($myLookUp);
*/		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('COPY_BUTTON');
		$myButton->setLabel(_t("COPY"));
		$myButton->setAction($azione_corrente);
		$myButton->setForm("COPIA");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
		
		$spacer->dispose();
		
		// Esportazione azioni
		$exportAction = new wi400Detail('EXPORT_ACTION', True);
		$exportAction->setTitle(_t("ACTION_EXPORT_TITLE"));
		$exportAction->isEditable(true);
	
		$myButton = new wi400InputButton('EXPORT_BUTTON');
		$myButton->setLabel(_t("ACTION_EXPORT_DETAIL"));
//		$myButton->setScript("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=LU_AZIONI&f=EXPORT_LIST', '"._t('ACTION_EXPORT_TITLE')."')");
		$myButton->setAction("LU_AZIONI");
		$myButton->setForm("EXPORT_LIST");
		$myButton->setTarget("WINDOW");
		$myButton->setTitle(_t('ACTION_EXPORT_TITLE'));
		$exportAction->addButton($myButton);
		
		$exportAction->dispose();
		
		$spacer->dispose();
		
		// Importazione azioni
		$importAction = new wi400Detail('IMPORT_ACTION', True);
		$importAction->setTitle(_t("ACTION_IMPORT_TITLE"));
		$importAction->isEditable(true);
	
		$myField = new wi400InputFile("IMPORT_FILE");
		$myField->setLabel(_t("ACTION_IMPORT_DETAIL"));
		$importAction->addField($myField);
	
		$myField = new wi400InputCheckbox("OVERWRITE_EXP");
		$myField->setLabel(_t("ACTION_REPLACE"));
		$myField->setChecked(false);
		$importAction->addField($myField);
	
		$myButton = new wi400InputButton('IMPORT_BUTTON');
		$myButton->setLabel(_t('IMPORT'));
		$myButton->setAction("AZIONI_IMPORT");
		$importAction->addButton($myButton);

		$importAction->dispose();
	}
	else if(in_array($actionContext->getForm(),array("DETAIL","COPIA"))) {
		$tipo_azi = "";
		if(isset($_REQUEST['TIPO_AZI'])) {
			$tipo_azi = $_REQUEST['TIPO_AZI'];
		}else {
			$tipo_azi = $row['TIPO'];
		}
		
		$azioniDetail = new wi400Detail('DETTAGLIO_AZIONE');
		$azioniDetail->isEditable(true);
		
		// Se l'azione esiste già
		if(isset($row)) {
			//Controllo se ho i dati in REQUEST
			if(!isset($_REQUEST['MODULO'])) {
				//Controllo se ho i dati in SESSIONE
				if(isset($_SESSION['SAV_AZIONE']['MODULO'])) {
					$_SESSION['SAV_AZIONE']['DESCRIZIONE'] = $_REQUEST['DESCRIZIONE'];
					$azioniDetail->setSource($_SESSION['SAV_AZIONE']);
					$modale_value = (isset($_SESSION['SAV_AZIONE']['URL_MODAL']));
					$widget_value = (isset($_REQUEST['HAS_WIDGET']));
					$system_value = (isset($_REQUEST['SYSTEM']));
					$log_value = (isset($_REQUEST['LOG_AZIONE']));
					$disable_value = (isset($_REQUEST['DISABLE']));
				}else {
					if($tipo_azi == "B") $row['VIEW'] = "";
					$azioniDetail->setSource($row);
					$modale_value = ($row['URL_MODAL'] == '1');
					$widget_value = ($row['HAS_WIDGET']=='1');
					$system_value = ($row['SYSTEM']=='1');
					$log_value = ($row['LOG_AZIONE'] == 'S');
					$disable_value = ($row['DISABILITA'] == 'S');
				}
			}else {
				$_SESSION['SAV_AZIONE'] = $_REQUEST;
				$azioniDetail->setSource($_REQUEST);
				$modale_value = (isset($_REQUEST['URL_MODAL']));
				$widget_value = (isset($_REQUEST['HAS_WIDGET']));
				$system_value = (isset($_REQUEST['SYSTEM']));
				$log_value = (isset($_REQUEST['LOG_AZIONE']));
				$disable_value = (isset($_REQUEST['DISABLE']));
			}
		}
		
		$azioniDetail->addTab("azioni_1", "Dati Azioni");
		if (isset($settings['multi_language']) && $settings['multi_language']==True)
			$azioniDetail->addTab("azioni_2", "Traduzioni in lingua");
		$azioniDetail->addTab("azioni_3",_t('GROUPS'));
		
		$scheda = 'azioni_1';

//		$azioniDetail->addParameter("SAVE_ACTION", $saveAction);
		
		$myField = new wi400InputText('codazi');
		$myField->setLabel(_t('ACTION_CODE'));
		if($actionContext->getForm()=="DETAIL")
			$myField->setValue($azione);
		else if($actionContext->getForm()=="COPIA")
			$myField->setValue($azione_new);
		$myField->setReadonly(true);
		$azioniDetail->addField($myField, $scheda);
		
		//showArray($resultArray);
		$myField = new wi400InputText('DESCRIZIONE');
		$myField->setFromArray($resultArray);
		$myField->setAutoSelection(True);
	    $myField->addValidation("required");
		$myField->setLabel(_t('DESCRIPTION'));
		$azioniDetail->addField($myField, $scheda);
		
		$mySelect = new wi400InputSelect('TIPO');
		$mySelect->setFromArray($resultArray, $scheda);
		$mySelect->setLabel(_t('TYPE'));
		$mySelect->setFirstLabel(_t('TYPE_SELECT'));
		$mySelect->addOption(_t('ACTION_TYPE_MENU'),"A");
		$mySelect->addOption(_t('ACTION_TYPE_COLLECT'),"M");
		$mySelect->addOption(_t('ACTION_TYPE_SIMPLY'),"N");
		$mySelect->addOption(_t('ACTION_TYPE_BATCH'),"B");
		$mySelect->addOption(_t('ACTION_TYPE_LINK'),"L");
		$mySelect->addOption(_t('ACTION_5250'),"T");
		$mySelect->addValidation("required");
		$mySelect->setValue($tipo_azi);
//		$mySelect->setOnChange("checkActionType(this,'VIEW')");
//		$mySelect->setOnChange("checkMenu(this,'MENU_BUTTON')");
		$mySelect->setOnChange("checkActionType(this)");
		$azioniDetail->addField($mySelect, $scheda);
	
		if(!in_array($tipo_azi, array("L", "M", "T"))) {
			$myField = new wi400InputText('MODULO');
			$myField->setFromArray($resultArray);	
			$myField->addValidation("required");
			$myField->setLabel(_t('MODULE'));
			$azioniDetail->addField($myField, $scheda);
		
			$myField = new wi400InputText('GATEWAY');
			$myField->setFromArray($resultArray);	
			$myField->setLabel("Gateway");
			$myField->setInfo(_t('GATEWAY_INFO'));
			$customTool = new wi400CustomTool();
			$customTool->setScript("auto_complete('GATEWAY')");
			$customTool->setIco("themes/common/images/table-select-row.png");
			$myField->addCustomTool($customTool);
			$azioniDetail->addField($myField, $scheda);

			$myField = new wi400InputText('MODEL');
			$myField->setFromArray($resultArray);	
			$myField->setLabel("Model");
			if(isset($model) && $model!="") {
				$myField->setValue($model);
			}
			$azioniDetail->addField($myField, $scheda);
		
			$myField = new wi400InputText('VIEW');
			$myField->setFromArray($resultArray);
			$myField->setLabel("View");
				
			if(isset($view) && $view!="") {
				$myField->setValue($view);
			}
			
			if($tipo_azi == "B") {
				$myField->setValue(" ");
				$myField->setReadonly(true);
			}
			
			$azioniDetail->addField($myField, $scheda);
		
			$myField = new wi400InputText('VALIDATION');
			$myField->setFromArray($resultArray);	
			$myField->setLabel(_t('VALIDATION'));
			$myField->setInfo(_t('VALIDATION_INFO'));
			$customTool = new wi400CustomTool();
			$customTool->setScript("auto_complete('VALIDATION')");
			$customTool->setIco("themes/common/images/table-select-row.png");
			$myField->addCustomTool($customTool);
			$azioniDetail->addField($myField, $scheda);
			
?>
			<script type="text/javascript">
				function auto_complete(id) {
					var a = jQuery("#codazi").val().toLocaleLowerCase();
					jQuery("#"+id).val(a+"_"+(id.toLocaleLowerCase())+".php");
				}
			</script>
<?php 
		}else {
			if($tipo_azi == "L") {
				$myField = new wi400InputText('URL');
				$myField->setSize(45);
				$myField->setLabel("Link");
				$azioniDetail->addField($myField, $scheda);
			}
		}
		
		//Scelta icona
		$myField = new wi400InputText('TABLETICO');
		$myField->setLabel('Icona');
		
		$customTool = new wi400CustomTool("ICONE_FONT_AWESOME", "DEFAULT");
		$customTool->addParameter("RETURN_ID", "TABLETICO");
		$customTool->addParameter("RETURN_DETAIL", "DETTAGLIO_AZIONE");
		$customTool->setIco("themes/common/images/table-select-row.png");
		$customTool->setTarget("WINDOW");
		$myField->addCustomTool($customTool);
		
		$azioniDetail->addField($myField, $scheda);
		
		$colore = $row['TABLETCOL'] ? $row['TABLETCOL'] : '50A1D1';
		$myField = new wi400Text("TABLETCOL", "Colore icona");
		$myField->setValue("<script src='routine/jscolor/jscolor.js'></script><input id='COLORE' name='COLORE' class='jscolor' value='$colore'>");
		$azioniDetail->addField($myField, $scheda);
		
		if($tipo_azi != "M") {
			$mySelect = new wi400InputSelect("URL_OPEN");
			$mySelect->setLabel("Target");
			if($tipo_azi == "L") {
				$mySelect->addOption("WINDOW", "_blank");
				$mySelect->addOption("POPUP", "_popup");
			}else {
				$mySelect->addOption("SELF", "_self");
				$mySelect->addOption("POPUP", "_popup");
				$mySelect->addOption("WINDOW", "_blank");
			}
			$azioniDetail->addField($mySelect, $scheda);
			
			if($tipo_azi != "L") {
				$myField = new wi400InputCheckbox("URL_MODAL");
				$myField->setLabel("Modale");
				$myField->setValue(true);
				$value_check = "";
				//if(isset($row['URL_MODAL'])) $value_check = $row['URL_MODAL'];
				//if(isset($row['URL']))
					$myField->setChecked($modale_value);
				$azioniDetail->addField($myField, $scheda);
			}
		}

		$myField = new wi400InputCheckbox("HAS_WIDGET");
		$myField->setLabel("Widget");
		$myField->setValue(false);
		$myField->setChecked($widget_value);
		$azioniDetail->addField($myField, $scheda);

        // Setto se azione di sistema
		//if(!in_array($tipo_azi, array("L"))) {
			$myField = new wi400InputCheckbox("SYSTEM");
			$myField->setLabel(_t('ACTION_TYPE_SYSTEM'));
			$myField->setValue(True);
			$myField->setChecked($system_value);
			$azioniDetail->addField($myField, $scheda);
			
			$myField = new wi400InputCheckbox("LOG_AZIONE");
			$myField->setLabel('Log azione');
			$myField->setValue(True);
			$myField->setChecked($log_value);
			$azioniDetail->addField($myField, $scheda);
		//}
		
		$myField = new wi400InputCheckbox("DISABLE");
		$myField->setLabel(_t('ACTION_DISABLE'));
		$myField->setValue(True);
		$myField->setChecked($disable_value);
		$azioniDetail->addField($myField, $scheda);
		
		// Scehda multilingua
		if(isset($settings['multi_language']) && $settings['multi_language']===True) {
			foreach($lang_array as $val) {
				$myField = new wi400InputText($val);
				$myField->setMaxLength(100);
				$myField->setSize(100);
				$myField->setLabel($val);
				if($actionContext->getForm()=="DETAIL")
					$myField->setValue(get_language_string("AZIONI", $azione, getLanguageID($val)));
				else if($actionContext->getForm()=="COPIA")
					$myField->setValue(get_language_string("AZIONI", $azione_old, getLanguageID($val)));
				$scheda = 'azioni_2';
				$azioniDetail->addField($myField, $scheda);
			}
		}
		
		$scheda = "azioni_3";
		
		$appDrag = new wi400DragAndDrop($azione_corrente."_DRAG");
		$appDrag->setWidth("49%");
		$appDrag->setHeight("100");
		$appDrag->setCheckUpdate(true);
		
		$appList1 = new wi400DragList("TO_GROUPS", _t("SELEZIONATI"));
		$appList1->setRows($to_groups);
		$appList1->setColor("#ffebe8");
		$appDrag->addList($appList1);
		
		$appList2 = new wi400DragList("FROM_GROUPS", _t('NON_SELEZIONATI'));
		$appList2->setRows($from_groups);
		$appList2->setColor("#FFFFCC");
		$appDrag->addList($appList2);
		
		$azioniDetail->addField($appDrag, $scheda);
		
		$azioniDetail->addTab("azioni_4", "Configurazione");
		
		$iframe = new wi400Iframe($azione."_CONF", "ABILITAZIONI_CAMPI_DETAIL", "CHECK_CONF_EXIST");
		//$iframe->setAutoLoad(false);
		$iframe->setStyle("height: 100%;");
		//$iframe->setDecoration("lookup");
		//$iframe->setAutoResize(true);
		$myField = new wi400InputText($azione."_INPUT_CONF");
		$myField->setCustomHTML($iframe->getHtml());
		$myField->setHeight("500");
		$azioniDetail->addField($myField, "azioni_4");
		
		// Bottoni
		// Check
		$myButton = new wi400InputButton('CHECK_BUTTON');
		$myButton->setLabel(_t('CHECK_FILES'));
		$myButton->setAction("TAZIONI");
		$myButton->setForm("CHECK_FILES");
		$myButton->setTarget("WINDOW", 700, 500);
		//$myButton->setScript("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=TAZIONI&f=CHECK_FILES', '"._t('CHECK_FILES')."',700,500)");
		$azioniDetail->addButton($myButton);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		if($saveAction=="UPDATE")
			$myButton->setLabel(_t('UPDATE'));
		else if($saveAction=="INSERT")
			$myButton->setLabel(_t('SAVE'));
		$myButton->setAction($azione_corrente);
		$myButton->setForm($saveAction);
		$myButton->setValidation(true);
		$azioniDetail->addButton($myButton);
		
		// Menu (salvataggio e passaggio alla gestione del menu se l'azione è un "Contenitore di azioni")
		$myButton = new wi400InputButton('MENU_BUTTON');
		$myButton->setLabel('Menu');
		$myButton->setAction($azione_corrente);
		$myButton->setForm($saveAction."_MENU");
		$myButton->setGateway("AZIONI");
		$myButton->setValidation(true);
		if($actionContext->getForm()=="COPIA")
			$myButton->setDisabled(true);
		else if($tipo_azi == "M")
			$myButton->setDisabled(false);
		else
			$myButton->setDisabled(true);
		$azioniDetail->addButton($myButton);
		
		// Annulla
		$myButton = new wi400InputButton('CANCEL_BUTTON');
		$myButton->setLabel(_t('CANCEL'));
		$myButton->setAction($azione_corrente);
		$myButton->setForm("DEFAULT");
		$azioniDetail->addButton($myButton);
		
		// Elimina azione (se già esistente)
		if($saveAction=="UPDATE") {
			$myButton = new wi400InputButton('DELETE_BUTTON');
			$myButton->setLabel(_t('DELETE'));
			$myButton->setAction($azione_corrente);
			$myButton->setForm("DELETE");
			$myButton->setValidation(False);
			$myButton->setConfirmMessage(_t('DELETE_CONFIRM'));
			$azioniDetail->addButton($myButton);
		}
		// Se è una azione 5250 carico la scheda azione
		$wi400_trigger->executeExitPoint("AZIONI","CUSTOM_TAB", array("tipo_azione"=>$tipo_azi));
		// Check Action
		$myButton = new wi400InputButton('CHECK_ACTION');
		$myButton->setLabel(_t('CHECK_ACTION'));
		$myButton->setAction("CHECK_AZIONE");
		$myButton->setGateway("CHECK_AZIONE");
		$myButton->setForm("CHECK_AZIONE");
		$myButton->setTarget("WINDOW");
		$azioniDetail->addButton($myButton);
		
		$azioniDetail->dispose();
	}
	else if($actionContext->getForm()=="CHECK_FILES") {
		$dati_exist = false;
		foreach($array_files as $key => $file) {
			if($file=="")
				continue;
				
			$dati_exist = true;
				
			$azioniDetail = new wi400Detail('TAZIONI_'.$key.'_DETAIL');
			$azioniDetail->setTitle(ucfirst($key));
			
			$file_path = $moduli_path."/".$modulo."/".$file;
			
			$labelDetail = new wi400Text("FILE_PATH");
			$labelDetail->setLabel(_t('FILE_PATH'));
			$labelDetail->setValue($file_path);
			$azioniDetail->addField($labelDetail);

			if(file_exists($file_path))
				$file_exists = "<font color='green'>"._t('FILE_EXIST')."</font>";
			else
				$file_exists = "<font color='red'>"._t('FILE_NOT_EXIST')."</font>";
				
			$labelDetail = new wi400Text("FILE_EXISTS");
			$labelDetail->setLabel(_t('CHECK_FILES'));
			$labelDetail->setValue($file_exists);
			$azioniDetail->addField($labelDetail);
			
			if(!empty($array_p13nfiles[$key])) {
				$labelDetail = new wi400Text("FILE_EXISTS");
				$labelDetail->setLabel(_t('CUSTOMIZE'));
				$labelDetail->setValue(implode("<br>",$array_p13nfiles[$key]));
				$azioniDetail->addField($labelDetail);
			}
			
			$azioniDetail->dispose();
			
			$spacer->dispose();
		}
		
		if($dati_exist===false) {
			// Se non ci sono dati nei campi
?>
			<script>
				alert("Dati mancanti.");
				closeLookUp();
			</script>
<?			
		}			
		else {		
			$myButton = new wi400InputButton("CLOSE_BUTTON");
			$myButton->setScript('closeLookUp()');
			$myButton->setLabel(_t('CLOSE'));
			$buttonsBar[] = $myButton;
		}
	}

?>
<!--
<script>
function checkProtect(which){
	if (which.checked){
		document.getElementById("GROUPS").disabled = false;
	}else{
		document.getElementById("GROUPS").disabled = true;
		document.getElementById("GROUPS").selectedIndex = -1;
	}
}

function checkMenu(which, where){
	var fieldObj = document.getElementById(where);
	if (which.value != "M") {
		fieldObj.className = "button-detail";
		fieldObj.disabled = true;
	}else{
		fieldObj.className = "button-detail";
		fieldObj.disabled = false;
	}
}
</script>
-->