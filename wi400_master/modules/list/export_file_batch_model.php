<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	$actionContext->setForm("EXPORT");
//	echo "PARAMETRI ESPORTAZIONE LISTA BATCH\r\n";
	$_REQUEST['FORMAT'] = substr(strtolower($batchContext->FORMAT),1);
//	 echo "\r\nSTAMPO FORMATO:".$_REQUEST['FORMAT'];die();
	$_REQUEST['TARGET'] = 'ALL';
	$_REQUEST['ORIENTATION'] = $batchContext->ORIENTATION;
	echo "FORMAT: ".$batchContext->FORMAT." - TARGET: ".$batchContext->TARGET." - ORIENTATION: ".$batchContext->ORIENTATION."\r\n";
	
	// Controllo il file da estrarre
	$tabella = $batchContext->FILE;
	if ($batchContext->LIBRE!="" && $batchContext->LIBRE!="*LIBL") {
		$tabella = $batchContext->LIBRE.$settings['db_separator'].$tabella;
	}
	
	// Controllo se è stata richiesta l'esportazione dell'indicazione dei filtri della lista utilizzati
	if(isset($batchContext->FILTERS))
		$_REQUEST['FILTERS'] = $batchContext->FILTERS;
	// Creo un lista FITTIZIA da Passare all'esportazione
	$libreria ="";
	if ($batchContext->LIBRE!="" && $batchContext->LIBRE!="*LIBL") {
		$libreria = $batchContext->LIBRE;
	}
	$mylist = new wi400List("TO_FLD_ITEM", true);
	$mylist->setFrom($tabella);
	if ($batchContext->FIELD!="") {
		$mylist->setField($batchContext->FIELD);
	}
	if ($batchContext->WHERE!="") {
		// I parametri sono in base64 e devo sostituire il doppio apice con il singolo
		$where = str_replace("\"", "'", base64_decode($batchContext->WHERE));
		$mylist->setWhere($where);
		echo "WHERE: ".$where."\r\n";
	}
	

	$mylist->setOrder($batchContext->ORDER);
	$mylist->setGroup($batchContext->GROUPBY);
	$colonne = getColumnListFromTable($batchContext->FILE,$libreria);
	// Imposto la label delle colonne a secondo di cosa hanno chiesto
	if ($batchContext->COLUMN=="*NAME") {
		foreach ($colonne as $key => $value) {
			$value->setDescription($key);
		}
	}
	// Se ho scelto delle colonne devo rimuoverle
	if ($batchContext->FIELD!="") {
		$scelte = explode(",",$batchContext->FIELD);
		$scelte = array_map('trim', $scelte);
		foreach ($colonne as $key => $value) {
			if (!in_array($key, $scelte)) {
				unset($colonne[$key]);
			}
		}
	}
	
	$mylist->setCols($colonne);
	wi400Session::save(wi400Session::$_TYPE_LIST, "TO_FLD_ITEM", $mylist);
	//$mylist->dispose();
	// Verifico se fare l'override sul membro
	if ($batchContext->MEMBER!="*FIRST") {
		$ovrsql = "CALL QCMDEXC('OVRDBF FILE(".trim($batchContext->FILE).") TOFILE(*FILE) MBR(".trim($batchContext->MEMBER).") OVRSCOPE(*JOB)')";
		echo "OVERRIDE: ".$ovrsql."\r\n";
		$db->query($ovrsql);
	}
	//echo var_dump($batchContext);
	//echo var_dump(getColumnListFromTable($batchContext->FILE, $libreria));
	//die();
	$_REQUEST['ORIENTATION'] = "";
		
	$_REQUEST['EXP_LIST'] = "TO_FLD_ITEM";
	echo "IDLIST: ".$batchContext->EXP_LIST."\r\n";
		
	// Recupero i dettagli da stampare
	if(isset($batchContext->ID_DETAILS))
		$_REQUEST['ID_DETAILS'] = $batchContext->ID_DETAILS;
		
	if(isset($batchContext->ZIP))
		$_REQUEST['ZIP'] = $batchContext->ZIP;
	echo "ZIP: ".$batchContext->ZIP."\r\n";
		
	if(isset($batchContext->NOTIFICA))
		$_REQUEST['NOTIFICA'] = $batchContext->NOTIFICA;
	echo "NOTIFICA: ".$batchContext->NOTIFICA."\r\n";
	
	echo "EXPORT_BATCH INIZIO\r\n";
	require_once 'export_list_model.php';
	echo "EXPORT_BATCH FINE\r\n";
	
	// Copio i file dalla export
	echo "FILE PATH: $filepath\r\n";
	echo "BATCH PATH: $new_path\r\n";
	$new_path = $batchContext->FILEOUTPUT;	
	if ($batchContext->CRTDIR=="*YES") {
		$path_parts = pathinfo($new_path);
		mkdir( $path_parts['dirname'], 0777, true);
		chmod( $path_parts['dirname'], 0777);
	}
	if (rename($filepath, $new_path)) {
			// Tutto OK	
	} else {
		$messageContext->addMessage("ERROR", "File non creato!");
	}
	if ($batchContext->MEMBER!="*FIRST") {
		$ovrsql = "CALL QCMDEXC('DLTOVR FILE(".trim($batchContext->FILE).") LVL(*JOB)')";
		echo "DLT OVERRIDE: ".$ovrsql."\r\n";
		$db->query($ovrsql);
	}
	// Scrittura dettagli su log fisico
	$sql = "INSERT INTO FBATCHJD (ID, PARAMETRO, VALORE) VALUES('$batchContext->id', 'FILE_PATH', '$new_path')";
	$db->query($sql);