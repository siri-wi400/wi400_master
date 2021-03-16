<?php
$form = $actionContext->getForm();

if($actionContext->getForm()=="DEFAULT") {
	//Richiesta Parametri Codice Sessione
	$searchAction = new wi400Detail("ROUTINE_VIEWER_DETAIL_SRC", False);
	$searchAction->setTitle('Parametri');
	$searchAction->isEditable(true);
	$myField = new wi400InputText('SESSIONE');
	$myField->setLabel("Codice sessione");
	$myField->setValue(session_id());
	$myField->addValidation('required');
	$myField->setMaxLength(30);
	$myField->setSize(30);
	$myField->setInfo("Inserire il codice della sessione di cui visualizzare il log XMLSERVICE");
	$searchAction->addField($myField);
	
	$myButton = new wi400InputButton('SEARCH_BUTTON');
	$myButton->setLabel("Seleziona");
	$myButton->setAction($azione);
	$myButton->setForm("DETAIL");
	$myButton->setValidation(true);
	$searchAction->addButton($myButton);
	
	$searchAction->dispose();
	
}
else if($actionContext->getForm()=="DETAIL") {
	$miaLista = new wi400List($azione."_LIST", True);
	$sql="SELECT substr(TEXT, 11) AS ROUTINE, 'PGM' AS TIPO, COUNT(*) AS CHIAMATE         
	, (select max(\"LOG\") from XMLSERVLOG/\"LOG\" WHERE TEXT=A.TEXT) AS MAX FROM XMLSERVLOG/\"LOG\" AS A 
			WHERE TEXT LIKE 'L_DO_PGM%' AND \"KEY\"LIKE'".$sessione."%' GROUP BY TEXT UNION ALL
	SELECT substr(TEXT, 10, 10) AS ROUTINE, 'CMD' AS TIPO, COUNT(*) AS CHIAMATE         
	, (select max(\"LOG\") from XMLSERVLOG/\"LOG\" WHERE TEXT=A.TEXT) AS MAX FROM XMLSERVLOG/\"LOG\" AS A 
			WHERE TEXT LIKE 'L_DO_CMD%' AND \"KEY\"LIKE'".$sessione."%' GROUP BY TEXT";
	$miaLista->setQuery($sql);
	$miaLista->setSelection('SINGLE');
	
	$miaLista->setCols(array(
			new wi400Column("ROUTINE","Programma/Comando"),
			new wi400Column("TIPO","Tipo"),
			new wi400Column("CHIAMATE","Numero Richiami", "integer", "right"),
			new wi400Column("MAX","Ultima Chiamata")
	));
	// Azioni di LISTA 
	$miaLista->dispose();
	// Aggiungo bottono per abilitare o meno il debug
	$myButton = new wi400InputButton("XMLSERVICE_LOG");
	if (isset($_SESSION["DEVELOPER_DEBUG_NEXT_CALL"]) && $_SESSION["DEVELOPER_DEBUG_NEXT_CALL"] == True) {
		$myButton->setScript("set_enable_log_xmlservice('DEVELOPER_DEBUG_NEXT_CALL', 0);");
		$myButton->setLabel("Disattiva Debug Next Call");
	} else {
		$myButton->setScript("set_enable_log_xmlservice('DEVELOPER_DEBUG_NEXT_CALL', 1);");
		$myButton->setLabel("Attiva Debug Next Call");
	}
	$myButton->dispose();
	// Abilito LOG
	$myButton = new wi400InputButton("XMLSERVICE_LOG");
	if (isset($_SESSION["DEVELOPER_LOG_XMLSERVICE"]) && $_SESSION["DEVELOPER_LOG_XMLSERVICE"] == True) {
		$myButton->setScript("set_enable_log_xmlservice('DEVELOPER_LOG_XMLSERVICE', 0);");
		$myButton->setLabel("Disattiva Log XMLSERVICE");
	} else {
		$myButton->setScript("set_enable_log_xmlservice('DEVELOPER_LOG_XMLSERVICE', 1);");
		$myButton->setLabel("Attiva LOG XMLSERVICE");
	}
	$myButton->dispose();
	// Cancellazione Dati
	$myButton = new wi400InputButton("ELIMINA_LOG");
	$myButton->setAction($azione);
	$myButton->setForm("ELIMINA");
	$myButton->setLabel("Elimina Log");
	$myButton->dispose();
} 


