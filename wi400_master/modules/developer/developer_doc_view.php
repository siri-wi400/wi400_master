<?php
$wi400_trigger->registerExitPoint("DEVELOPER_DOC","CUSTOM_TAB", "*WI400", "Punto uscita TAB aggiuntive Developer", "");

$form = $actionContext->getForm();
if($actionContext->getForm()=="DEFAULT") {
	//Richiesta Parametri Codice Sessione
	$searchAction = new wi400Detail("DEVELOPER_DOC_SRC", False);
	$searchAction->setTitle('Parametri');
	$searchAction->isEditable(true);
	$myField = new wi400InputText('SESSIONE');
	$myField->setLabel("Codice sessione");
	$myField->setValue(session_id());
	$myField->addValidation('required');
	$myField->setMaxLength(30);
	$myField->setSize(30);
	$myField->setInfo("Inserire il codice della sessione di cui visualizzare le informazioni");
	$searchAction->addField($myField);

	$myButton = new wi400InputButton('SEARCH_BUTTON');
	$myButton->setLabel("Seleziona");
	$myButton->setAction($azione);
	$myButton->setForm("DETAIL");
	$myButton->setValidation(true);
	$searchAction->addButton($myButton);

	$searchAction->dispose();

}
if($form == "DETAIL") {
	$sessione = wi400Detail::getDetailValue("DEVELOPER_DOC_SRC", "SESSIONE");
	$USERSESSION = getSessionUser($sessione);
	if (isset($_REQUEST['ACTION'])) {
		$theaction = $_REQUEST['ACTION'];
		$USERSESSION['CURRENT_ACTION_DEVELOPER']=$theaction;
	} else {
		// Provo a vedere la sessione
		$theaction = $USERSESSION['CURRENT_ACTION_DEVELOPER'];
	} 
	//if(isset($_re))
	$actionRow=rtvAzione($theaction);
	$path = "/modules/".$actionRow['MODULO']."/";
	$full_path = $moduli_path.$actionRow['MODULO']."/";
	$doc = array();
	$files= array();
	// Recupero i pezzi
	if (isset($actionRow["GATEWAY"]) && $actionRow["GATEWAY"] != ""){
		$file = p13n($path.$actionRow["GATEWAY"]);
		if (file_exists($file)) {
			$text= file_get_contents($file);
			$doc1 = everything_in_tags($text, "DEVCOM");
			$doc = array_merge($doc, $doc1);
			$files[]=$file;
		}
	}
	if (isset($actionRow["MODEL"]) && $actionRow["MODEL"] != ""){
		$file = p13n($path.$actionRow["MODEL"]);
		if (file_exists($file)) {
			$text= file_get_contents($file);
			$doc1 = everything_in_tags($text, "DEVCOM");
			$doc = array_merge($doc, $doc1);
			$files[]=$file;
		}
	}
	if (isset($actionRow["VIEW"]) && $actionRow["VIEW"] != ""){
		$file = p13n($path.$actionRow["VIEW"]);
		if (file_exists($file)) {
			$text= file_get_contents($file);
			$doc1 = everything_in_tags($text, "DEVCOM");
			$doc = array_merge($doc, $doc1);
			$files[]=$file;
		}
	}
	if (isset($actionRow["VALIDATION"]) && $actionRow["VALIDATION"] != ""){
		$file = p13n($path.$actionRow["VALIDATION"]);
		if (file_exists($file)) {
			$text= file_get_contents($file);
			$doc1 = everything_in_tags($text, "DEVCOM");
			$doc = array_merge($doc, $doc1);
			$files[]=$file;
		}
	}
	// Prendo i file inclusi che appartengono al modulo
	foreach ($USERSESSION['DEVELOPER_DOC_INCLUDED'] as $key =>$file) {
		if (strpos($file, $path)) {
			if (!in_array($file, $files)) {
				$text= file_get_contents($file);
				$doc1 = everything_in_tags($text, "DEVCOM");
				$doc = array_merge($doc, $doc1);
				$files[]=$file;
			}
		}
	}
	$first= True;
	$commenti = "";
	foreach ($doc as $row => $key) {
		if ($first) {
			//echo "<pre>-------------------> COMMENTI PER SVILUPPATORI <--------------------</pre>";
			$first=False;
		}
		$key=str_replace("\r\n", "<br>", $key);
		$commenti .=trim($key);
	}
	if (isset($USERSESSION['DEVELOPER_RUNTIME_FIELD']['CUSTOM']) && count($USERSESSION['DEVELOPER_RUNTIME_FIELD']['CUSTOM'])>0) {
		$commenti .="<br><b>VARIABILI</b>";
		foreach ($USERSESSION['DEVELOPER_RUNTIME_FIELD']['CUSTOM'] as $key =>$value) {
			if (is_array($value)) {
				$commenti .= "<br><b><u>$key</b></u><br><pre>".print_r($value, True)."</pre>";
			} else {
				$commenti .= "<br><b><u>$key</b></u></br>".$value;
			}
		}
	}
	//echo "<pre>-------------------> FILE SORGENTI PHP E LOG <---------------------</pre>";
	$phpCode = new wi400PhpCode($files);
	//$phpCode->dispose();
	//DETTAGLIO MASTER
	$azioniDetail = new wi400Detail('DEVELOPER_MASTER', false);
	$azioniDetail->addTab("enti_1", "Commenti");
	$azioniDetail->addTab("enti_2", "Source");
	$azioniDetail->addTab("enti_3", "Log");
	// Visualizzo per database AS400 - XMLSERVICE Abilitato
		if ($showxml == True){
	$azioniDetail->addTab("enti_4", "JobLog XML");
	$azioniDetail->addTab("enti_41", "JobLog SQL");
	$azioniDetail->addTab("enti_5", "XMLSERVICE");
		}
	$azioniDetail->addTab("enti_6", "Settings");
	$azioniDetail->addTab("enti_7", "Sessione");
	$azioniDetail->addTab("enti_8", "WI-Object");
	$azioniDetail->addTab("enti_9", "Utilità"); 
	$azioniDetail->addTab("enti_10", "Serialized"); 
	$azioniDetail->addTab("enti_11", "Temp Table"); 
	$azioniDetail->addTab("enti_12", "Mapping");

	$azioniDetail->addJstoTab("enti_3", "activeIframe('zero')");
	$azioniDetail->addJstoTab("enti_4", "activeIframe('primo')");
	$azioniDetail->addJstoTab("enti_41", "activeIframe('primo1')");
	$azioniDetail->addJstoTab("enti_5", "activeIframe('secondo')");
	$azioniDetail->addJstoTab("enti_7", "activeIframe('secondo2')");
	$azioniDetail->addJstoTab("enti_8", "activeIframe('terzo')");
	$azioniDetail->addJstoTab("enti_9", "activeIframe('quarto')");
	$azioniDetail->addJstoTab("enti_10", "activeIframe('quinto')");	
	$azioniDetail->addJstoTab("enti_11", "activeIframe('sesto')");
	$azioniDetail->addJstoTab("enti_12", "activeIframe('settimo')");
	
	$tab="enti_1";
	
	if ($commenti=="") {
		$commenti ="*NO COMMENT FOUND*";
	}
	$labelDetail = new wi400InputText("COMMENTI");
	$labelDetail->setCustomHTML($commenti);
	$azioniDetail->addField($labelDetail, $tab);
	
	$tab="enti_2";
	
	$labelDetail = new wi400InputText("CODICE");
	$labelDetail->setCustomHTML($phpCode->getHtml());
	$azioniDetail->addField($labelDetail, $tab);
	
	$scheda = "enti_3";
	$iframe = new wi400Iframe("zero", "DEVELOPER_LOG", "DEFAULT");
	$iframe->setDecoration("lookup");
	$iframe->setStyle("height: 100%;");
	$iframe->setAutoLoad(false);
	$myField = new wi400InputText('LOG_GENERALI');
	$myField->setCustomHTML($iframe->getHtml());
	$myField->setHeight(500);
	$azioniDetail->addField($myField, $scheda);
	
	// Visualizzo per database AS400 - XMLSERVICE Abilitato
		if ($showxml == True){
	// Scheda JOLOG
	$scheda = "enti_4";
	$iframe = new wi400Iframe("primo", "JOBLOG_VIEWER", "DETAIL", "DEVELOPER_DOCX");
	$iframe->setDecoration("lookup");
	$iframe->setStyle("height: 100%;");
	$iframe->setAutoLoad(false);
	$myField = new wi400InputText('JOBXML');
	$myField->setCustomHTML($iframe->getHtml());
	$myField->setHeight(500);
	$azioniDetail->addField($myField, $scheda);
	
	// Scheda JOLOG
	$scheda = "enti_41";
	$iframe = new wi400Iframe("primo1", "JOBLOG_VIEWER", "DETAIL", "DEVELOPER_DOCS");
	$iframe->setDecoration("lookup");
	$iframe->setStyle("height: 100%;");
	$iframe->setAutoLoad(false);
	$myField = new wi400InputText('JOBSQL');
	$myField->setCustomHTML($iframe->getHtml());
	$myField->setHeight(500);
	$azioniDetail->addField($myField, $scheda);

	// XMLSERVICE
	$scheda = "enti_5";
	$iframe = new wi400Iframe("secondo", "ROUTINE_VIEWER", "DETAIL", "DEVELOPER_DOC");
	$iframe->setDecoration("lookup");
	$iframe->setStyle("height: 100%;");
	$iframe->setAutoLoad(false);
	$myField = new wi400InputText('ROUTINE_VIEWER');
	$myField->setCustomHTML($iframe->getHtml());
	$myField->setHeight(500);
	$azioniDetail->addField($myField, $scheda);
		}
	
	$tab="enti_6";
	
	$labelDetail = new wi400InputText("SETTINGS");
	$labelDetail->setCustomHTML(getHTMLObject($settings, "Array", "1", True));
	$azioniDetail->addField($labelDetail, $tab);

	$tab="enti_7";
	// XMLSERVICE
	$scheda = "enti_7";
	$iframe = new wi400Iframe("secondo2", "NAVIGATE_OBJECT", "DETAIL", "DEVELOPER_DOC");
	$iframe->setDecoration("lookup");
	$iframe->setStyle("height: 100%;");
	$iframe->setAutoLoad(false);
	$myField = new wi400InputText('SESSION_OBJECT');
	$myField->setCustomHTML($iframe->getHtml());
	$myField->setHeight(500);
	$azioniDetail->addField($myField, $scheda);
	
		
	// XMLSERVICE
	$scheda = "enti_8";
	$iframe = new wi400Iframe("terzo", "SESSION_LIST_FILE", "DETAIL", "DEVELOPER_DOC");
	$iframe->setDecoration("lookup");
	$iframe->setStyle("height: 100%;");
	$iframe->setAutoLoad(false);
	$myField = new wi400InputText('SESSION_LIST_FILE');
	$myField->setCustomHTML($iframe->getHtml());
	$myField->setHeight(500);
	$azioniDetail->addField($myField, $scheda);

	// UTILITY
	$scheda = "enti_9";
	$iframe = new wi400Iframe("quarto", "DEVELOPER_UTILITY", "", "");
	$iframe->setDecoration("lookup");
	$iframe->setAutoLoad(false);
	$iframe->setStyle("height: 100%;");
	$myField = new wi400InputText('SESSION_LIST_FILE');
	$myField->setCustomHTML($iframe->getHtml());
	$myField->setHeight(500);
	$azioniDetail->addField($myField, $scheda);

	// SERIALIZED
	$scheda = "enti_10";
	$iframe = new wi400Iframe("quinto", "SERIALIZED_FILE", "", "");
	$iframe->setDecoration("lookup");
	$iframe->setAutoLoad(false);
	$iframe->setStyle("height: 100%;");
	$myField = new wi400InputText('SERIALIZED_FILE');
	$myField->setCustomHTML($iframe->getHtml());
	$myField->setHeight(500);
	$azioniDetail->addField($myField, $scheda);
	// TEMP TABLE
	$scheda = "enti_11";
	$iframe = new wi400Iframe("sesto", "TEMP_TABLE_LIST", "DETAIL", "DEVELOPER_DOC");
	$iframe->setDecoration("lookup");
	$iframe->setAutoLoad(false);
	$iframe->setStyle("height: 100%;");
	$myField = new wi400InputText('TEMP_TABLE');
	$myField->setCustomHTML($iframe->getHtml());
	$myField->setHeight(500);
	$azioniDetail->addField($myField, $scheda);
	
	// TEMP TABLE
	$scheda = "enti_12";
	$iframe = new wi400Iframe("settimo", "MAPPING_DETAIL_LIST", "");
	$iframe->setDecoration("lookup");
	$iframe->setAutoLoad(false);
	$iframe->setStyle("height: 100%;");
	$myField = new wi400InputText('MAPPING_LIST_DETAIL');
	$myField->setCustomHTML($iframe->getHtml());
	$myField->setHeight(500);
	$azioniDetail->addField($myField, $scheda);
	
	// Aggiunta Schede Custom da Trigger
	$wi400_trigger->executeExitPoint("DEVELOPER_DOC","CUSTOM_TAB", array());
	
	// Dispose Finale
	$azioniDetail->dispose();

}