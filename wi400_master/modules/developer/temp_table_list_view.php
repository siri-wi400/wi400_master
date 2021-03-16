<?php
if($actionContext->getForm()=="DEFAULT") {
	//Richiesta Parametri Codice Sessione
	$searchAction = new wi400Detail("TEMP_TABLE_SRC", False);
	$searchAction->setTitle('Parametri');
	$searchAction->isEditable(true);
	$myField = new wi400InputText('SESSIONE');
	$myField->setLabel("Codice sessione");
	$myField->setValue(session_id());
	$myField->addValidation('required');
	$myField->setMaxLength(30);
	$myField->setSize(30);
	$myField->setInfo("Inserire il codice della sessione di cui visualizzare le tabelle temporanee");
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
	$subfile = new wi400Subfile($db, "TEMP_TABLE", $settings['db_temp'],20);
	$subfile->setModulo("developer");
	$libreria="PHPTEMP";
	$database = $settings['database'];
	$su = strtoupper($sessione);
	// Devo recuperare la lista delle tabelle in base al sistema in cui mi trovo .. @todo
	if ($database == 'DB2AS400' || $database == 'DB2_ODBC' || $database=="DB2_PDO"){
		$sql = "SELECT SYSTEM_TABLE_NAME, TABLE_NAME, TABLE_TEXT FROM systables WHERE SYSTEM_TABLE <>  
		'Y' AND FILE_TYPE ='D' AND TABLE_TYPE IN('P', 'T') AND TABLE_SCHEMA='$libreria' 
		AND TABLE_NAME LIKE '%$su%'";
	}
	if ($database == 'GENERIC_PDO' || $database == 'GENERIC_PDO_GEN' || $database == 'DB2MYSQLI'){
		$libreria=$settings['db_temp'];
		$su = $sessione;
		$schema_tables = "TABLES";
		$schema_info = "INFORMATION_SCHEMA";
		$sql = "select NULL AS SYSTEM_TABLE_NAME, TABLE_NAME, TYPE_DESC as TABLE_TEXT, create_date as DATA_CREAZIONE, modify_date as ULTIMO_UTILIZZO 
		from $schema_info".$settings['db_separator']."$schema_tables , sys.objects 
		where table_name=name and 
		TABLE_SCHEMA = '".$settings['db_temp']."' and TABLE_NAME LIKE '%$su%'";
	}
	if ($database == 'OCI_PDO_ORACLE'){
	$sql = "select NULL AS SYSTEM_TABLE_NAME,TABLE_NAME,TABLE_TYPE as TABLE_TEXT from ALL_CATALOG where TABLE_NAME like'%".strtoupper(substr($session_id,0,15))."%' and OWNER ='".$settings['db_temp']."'";
	}
/*	if ($database == 'DB2_PDO'){
	$sql = "select SYSTEM_TABLE_NAME,TABLE_NAME, TABLE_TEXT from QSYS2".$settings['db_separator']."TABLES where TABLE_SCHEMA ='".$settings['db_temp']."' and TABLE_NAME like'%".strtoupper($session_id)."%'";
	}*/
	$subfile->setSql($sql);
	$subfile->addParameter("LIBRERIA", $libreria, True);
	
	$miaLista = new wi400List("TEMP_TABLE_LIST_LIST", True);
	$miaLista->setSubfile($subfile);
	
	$miaLista->setFrom($subfile->getTable());
	$miaLista->setOrder("TABLENAME DESC");
	
	$cols = getColumnListFromArray('TEMP_TABLE', "developer");
	
	$miaLista->setCols($cols);
	// Numero lavoro lo voglio a Destra
	
	// aggiunta chiavi di riga
	$miaLista->addKey("TABLENAME");
	$miaLista->addParameter("LIBRERIA", $libreria);
	// Aggiunta filtri
	$listFlt = new wi400Filter("TABLENAME");
	$listFlt->setDescription("Nome Tabella");
	$listFlt->setFast(True);
	$miaLista->addFilter($listFlt);
	// Aggiunta azioni
	$action = new wi400ListAction();
	$action->setAction("TEMP_RECORD_LIST");
	$action->setTarget("WINDOW", 1000, 600);
	$action->setLabel("Visualizza contenuto");
	$miaLista->addAction($action);
	// Aggiunta azioni
	$action = new wi400ListAction();
	$action->setAction($azione);
	$action->setForm("CANCELLA");
	$action->setLabel("Cancella Tabella");
	$miaLista->addAction($action);
	
	listDispose($miaLista);
}
