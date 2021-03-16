<?php
	require_once 'console_giwi400_commons.php';

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
//	echo "FORM: $form<br>";
	
	if(!in_array($form, array(''))) {
		$history->addCurrent();
	}
	
	if($form != 'DEFAULT') {
		$param = wi400Detail::getDetailValues($azione."_PARAM");
	}
	if(!in_array($form, array('DEFAULT', 'DETAIL'))) {
		$keyFile = getListKeyArray($azione."_LIST");
		
		$file_src = $keyFile['OT5KEY'];
	}
	if(!in_array($form, array('DEFAULT', 'DETAIL', 'FORM'))) {
		$keyForm = getListKeyArray($azione."_LIST_FORM");
		
		$form_src = $keyForm['OT5FMT'];
	}
	if(in_array($form, array('SAVE_CLIENT_ATTRIBUTI', 'CLIENT_ATTRIBUTI', 
		'PARAMETRI_CLIENT_ATTRIBUTI', 'SAVE_PARAMETRI_CLIENT',
		'LEGAME', "ADDNEW", "ELIMINA_FIELD", "INSERT_LAGAME", "UPDATE_LAGAME", "DELETE_LAGAME",
		"UPDATE_SEQUENZA"
	))) {
		$keyField = getListKeyArray($azione."_LIST_CAMPI");
		//showArray($keyField);
		
		$field_src = $keyField['OT5FLD'];
	}
//	echo "FILE: $file_src - FORM: $form_src - FIELD: $field_src<br>";
	
	if($form == 'AJAX_SAVE_CONDIZIONI') {
		showArray($_REQUEST);
		
		$_SESSION['GIWI_CONDIZIONI'][$_REQUEST['ID']] = $_REQUEST['VALUE'];
		die();
	}
	
	if($form == 'DEFAULT') {
		$actionContext->setLabel('Parametri');
		
	}else if($form == 'DETAIL') {
		
		if(!isset($_SESSION['GIWI_CONDIZIONI'])) {
			$condizioni = array();
			foreach($var_condition as $condition) {
				$condizioni[$condition] = 'false';
			}
			ksort($condizioni);
			$_SESSION['GIWI_CONDIZIONI'] = $condizioni;
		}
		//showArray($param);
		
		$where = array();
		
		if($param['LIBRERIA']) {
			$where[] = "OT5LIB='{$param['LIBRERIA']}'";
		}
		if($param['FILE']) {
			$where[] = "OT5FIL='{$param['FILE']}'";
		}
		
		$where = implode(' and ', $where);
	}else if($form == 'FORM') {
		$actionContext->setLabel('Form');
		//showArray($keyFile);
		
		
	}else if($form == 'VIS_MASCHERA') {
		$actionContext->setLabel('Maschera');
		//showArray($keyForm);
		
		if(!isset($_SESSION['GIWI400_ID'])) {
			list($id, $id_file) = getGiwi400Id();
			$_SESSION['GIWI400_ID'] = $id;
		}
		
		require_once 'modules/giwi400/classi/giwi400.cls.php';
		require_once 'modules/giwi400/classi/giwi400Display.cls.php';
		
		$string_xml = '<display>
	<DSWIHEAD>
      <I_GIWI_DSF>FORM_TEST</I_GIWI_DSF>
      <ERRORI />
      <ERRORE>0</ERRORE>
      <I_GIWI_FLI>'.$keyFile['OT5LIB'].'</I_GIWI_FLI>
      <I_GIWI_FIL>'.$keyFile['OT5FIL'].'</I_GIWI_FIL>
      <I_GIWI_TAB />
      <FUNCPRS>ENTER</FUNCPRS>
      <I_GIWI_TIT>TITLE FORM</I_GIWI_TIT>
      <I_GIWI_PGM>RGIWI01</I_GIWI_PGM>
      <I_GIWI_OPE>E</I_GIWI_OPE>
      <I_GIWI_FRM>'.$keyForm['OT5FMT'].'</I_GIWI_FRM>
      <I_GIWI_DBT>PHPTEMP/GIWI'.$_SESSION['GIWI400_ID'].'</I_GIWI_DBT>
   </DSWIHEAD>
</display>';
		
		$manager = '';
		
		$useClass = getUseClass();
		//echo $useClass."__useClass<br>";
		
		$giwi400 = new giwi400($string_xml, 0, $manager);
		//$giwi400->checkErrori();
		$giwi400->setShowDatiField(true);
		
		$sql = "SELECT * FROM ZOT5RECF WHERE OT5KEY='{$keyFile['OT5KEY']}' AND OT5FMT='{$keyForm['OT5FMT']}'";
		$rs = $db->singleQuery($sql);
		$row = $db->fetch_array($rs);
		
		if($row) {
			if($row['OT5TAB']) {
				$row['OT5TAB'] = explode('|', $row['OT5TAB']);
				foreach($row['OT5TAB'] as $i => $label_tab) {
					$row['OT5TAB'][$i] = str_replace(" ", "_", $label_tab);
				}
			}
		}
	}else if($form == 'RECORD') {
		$sql = "SELECT * FROM ZOT5RECF WHERE OT5KEY='{$keyFile['OT5KEY']}' AND OT5FMT='{$keyForm['OT5FMT']}'";
		$rs = $db->singleQuery($sql);
		$row = $db->fetch_array($rs);
		
		if($row) {
			if($row['OT5TAB']) {
				$row['OT5TAB'] = explode('|', $row['OT5TAB']);
				foreach($row['OT5TAB'] as $i => $label_tab) {
					$row['OT5TAB'][$i] = str_replace(" ", "_", $label_tab);
				}
			}
		}
	}else if($form == 'ABBINA_FORM') {
		$actionContext->setLabel('Abbina');

		require_once 'modules/giwi400/classi/giwi400.cls.php';
		
		$detailAbbina = new wi400Detail('PROVA');
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_POST['IDLIST']);
		foreach($wi400List->getSelectionArray() as $k => $value) {
			$keyForm = get_list_keys_num_to_campi($wi400List, explode('|', $k));
			
			//showArray($keyFile);
			//showArray($keyForm);
			
			$string_xml = '<display>
	<WI4DOLCMD></WI4DOLCMD>
	<WI4DOLFILE></WI4DOLFILE>
	<DSWIHEAD>
      <I_GIWI_DSF>FORM_TEST</I_GIWI_DSF>
      <ERRORI />
      <ERRORE>0</ERRORE>
      <I_GIWI_FLI>'.$keyFile['OT5LIB'].'</I_GIWI_FLI>
      <I_GIWI_FIL>'.$keyFile['OT5FIL'].'</I_GIWI_FIL>
      <I_GIWI_TAB />
      <FUNCPRS>ENTER</FUNCPRS>
      <I_GIWI_TIT>TITLE FORM</I_GIWI_TIT>
      <I_GIWI_PGM>RGIWI01</I_GIWI_PGM>
      <I_GIWI_OPE>E</I_GIWI_OPE>
      <I_GIWI_FRM>'.$keyForm['OT5FMT'].'</I_GIWI_FRM>
   </DSWIHEAD>
</display>';
			
			$giwi400 = new giwi400($string_xml, 0);
			//$giwi400->checkErrori();
			$giwi400->setShowDatiField(true);
			$detailGiwi = $giwi400->getDetailForm();
			foreach ($detailGiwi->getFields() as $k => $field) {
				$detailAbbina->addField($field);
			}
		}
		
	}else if($form == 'DETTAGLIO_CAMPI') {
		//showArray($keyForm);
		wi400Detail::cleanSession($azione."_CLIENT_ATTRIBUTI");
	}else if($form == 'CLIENT_ATTRIBUTI') {
		$actionContext->setLabel('Attributi '.$keyField['OT5FLD']);
		
		$row = getDatiClientAttributi($keyFile['OT5KEY'], $keyForm['OT5FMT'], $keyField['OT5FLD']);
		// Verifico se è un campo Non da MASCHERA RPG
		$sql = "SELECT OT5USE FROM ZOT5FLDL WHERE OT5KEY='{$keyFile['OT5KEY']}' AND OT5FMT='{$keyForm['OT5FMT']}' AND OT5FLD='{$keyField['OT5FLD']}'";
//		echo "SQL: $sql<br>";
		$result = $db->singleQuery($sql);
		$row2 = $db->fetch_array($result);
		$theuse = $row2['OT5USE'];
//		echo "UTILIZZO: ".$theuse."<br>";
		
//		$theuse = utf8_encode($theuse);
//		$theuse = prepare_string($theuse);
//		echo "ENCODE: ".$theuse."<br>";
/*		
		if(isset($settings['utf8_decode']) && $settings['utf8_decode']===true) {
//			echo "DECODE UTF-8<br>";
		}
		else if(isset($settings['utf8_encode']) && $settings['utf8_encode']===true) {
//			echo "ENCODE UTF-8<br>";
			$theuse = utf8_encode($theuse);	// in ATG_8_3 ma NON in UNICOMM e ATG VECCHIO
			echo "ENCODE: ".$theuse."<br>";
		}
*/		
		if ($theuse=="§") {
//			echo "NUOVO<br>";	
		}
	}else if($form == 'CLIENT_ATTRIBUTI_WINDOWS') {
		$file=$_REQUEST['I_GIWI_FIL'];
		$libre=$_REQUEST['FOMRFILL'];
		$form=$_REQUEST['I_GIWI_FRM'];
		$fld=$_REQUEST['CAMPO'];
		$key = $file."_".$libre;
		//showArray($_REQUEST);die();
		// Creazione della lista fittizia per generare valori che utilizza la funzione emetti
		/*$cliVirtualList = new wi400List($azione."_LIST");
		$cliVirtualList->addKey('OT5KEY');
		$cliVirtualList->addKey('OT5LIB');
		$cliVirtualList->addKey('OT5FIL');
		$sa = array();
		$key=$key."|".$libre."|".$file;
		$sa[$key] = "";
		$cliVirtualList->setSelectionArray($sa);
		saveList($azione."_LIST", $cliVirtualList);
		// Lista Form
		$cliVirtualList = new wi400List($azione."_LIST_FORM");
		$cliVirtualList->addKey('OT5FMT');
		$sa = array();
		$key=$form;
		$sa[$key] = "";
		$cliVirtualList->setSelectionArray($sa);
		saveList($azione."_LIST_FORM", $cliVirtualList);*/
		// Lista Campi
		$cliVirtualList = new wi400List($azione."_LIST_CAMPI");
		$cliVirtualList->addKey('OT5FLD');
		$sa = array();
		$key=$fld;
		$sa[$key] = "";
		$cliVirtualList->setSelectionArray($sa);
		saveList($azione."_LIST_CAMPI", $cliVirtualList);
		$actionContext->gotoAction($azione, "CLIENT_ATTRIBUTI","",True);
		
	}else if($form == 'ELIMINA_FIELD') {
		$rs = deleteClientAttributi($keyFile['OT5KEY'], $keyForm['OT5FMT'], $keyField['OT5FLD']);
		$sql = "DELETE FROM ZOT5FLDL WHERE OT5KEY='{$keyFile['OT5KEY']}' AND OT5FMT='{$keyForm['OT5FMT']}' AND OT5FLD='{$keyField['OT5FLD']}'";
		$result = $db->query($sql);
		$actionContext->gotoAction("CLOSE", "RELOAD_PREVIOUS_WINDOW", "", true);
	}
	else if($form == 'UPDATE_SEQUENZA') {
		$idList = $_REQUEST['IDLIST'];
		if(isset($_REQUEST['CURRENT_IDLIST']))
			$idList = $_REQUEST['CURRENT_IDLIST'];
		else
			$idList = $_REQUEST['PARENT_ID'];
//		echo "IDLIST: $idList<br>";
		
		$wi400List = getList($idList);
		
		$rowsSelectionArray = $wi400List->getSelectionArray();
//		echo "ROWS SEL:<pre>"; print_r($rowsSelectionArray); echo "</pre>";
		
		$keyUpdt = array("OT5KEY" => "?", "OT5FMT" => "?", "OT5FLD" => "?");
			
		$fieldsValue = array();
		$fieldsValue['OT5SEQ'] = 0;
		
		$stmt_updt = $db->prepare("UPDATE", $tabClientAttributi, $keyUpdt, array_keys($fieldsValue));
		
		foreach($rowsSelectionArray as $key => $value) {
//			echo "KEY: $key<br>";
//			echo "VALUE:<pre>"; print_r($value); echo "</pre>";
		
			$keyArrayS = explode("|", $key);
		
			$keyArrayS = get_list_keys_num_to_campi($wi400List, $keyArrayS);
//			echo "LIST KEYS:<pre>"; print_r($keyArrayS); echo "</pre>";
		
			$ot5key = $keyArrayS["OT5KEY"];
			$ot5fmt = $keyArrayS["OT5FMT"];
			$ot5fld = $keyArrayS["OT5FLD"];
				
//			echo "UPDATE - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
		
			$sequenza = $value['SEQUENZA'];
		
			$campi_updt = $fieldsValue;
			$campi_updt['OT5SEQ'] = $sequenza;
			$campi_updt['OT5KEY'] = $ot5key;
			$campi_updt['OT5FMT'] = $ot5fmt;
			$campi_updt['OT5FLD'] = $ot5fld;
//			echo "UPDATE - FIELDS:<pre>"; print_r($campi_updt); echo "</pre>";
				
			$result = $db->execute($stmt_updt, $campi_updt);
		
			if(!$result) {
//				$messageContext->addMessage("ERROR", "Errore durante il salvataggio");
			}
		}
		
		$messageContext->addMessage("SUCCESS", "Salvataggio avvenuto con successo");
		
		$actionContext->gotoAction($azione, "DETTAGLIO_CAMPI", "", true);
	}
	else if($form == 'SAVE_CLIENT_ATTRIBUTI') {
		
		$dati = wi400Detail::getDetailValues($azione."_CLIENT_ATTRIBUTI");
		//showArray($dati);
		
		$rs = deleteClientAttributi($keyFile['OT5KEY'], $keyForm['OT5FMT'], $keyField['OT5FLD']);
		if($rs) {
			$rs = insertClientAttributi($keyFile['OT5KEY'], $keyForm['OT5FMT'], $keyField['OT5FLD'], $dati);
			if(!$rs) {
				$messageContext->addMessage('ERROR', 'Errore inserimento client attributi');
			}else {
				$messageContext->addMessage('SUCCESS', 'Inserimento attributi effettuato con successo.');
			}
		}else {
			$messageContext->addMessage('ERROR', 'Errore eliminazione client attributi');
		}
		
		deleteSessionManager($keyFile['OT5FIL'], $keyFile['OT5LIB']);
		
		$actionContext->gotoAction("CLOSE", "RELOAD_PREVIOUS_WINDOW", "", true);
	}else if($form == "SAVE_DATI_DETAIL") {
		
		$dati = wi400Detail::getDetailValues($azione."_RIEP_LIST");
		
		/*showArray($keyFile);
		showArray($keyForm);
		showArray($dati);*/
		//$row = getGiwi400ParamDetail($keyFile['OT5KEY'], $keyForm['OT5FMT']);
		$rs = deleteDetailAttributi($keyFile['OT5KEY'], $keyForm['OT5FMT']);
		if($rs) {
			$rs = insertDetailAttributi($keyFile['OT5KEY'], $keyForm['OT5FMT'], $dati);
			if(!$rs) {
				$messageContext->addMessage('ERROR', 'Errore inserimento detail attributi');
			}else {
				$messageContext->addMessage('SUCCESS', 'Inserimento attributi detail effettuato con successo.');
			}
		}else {
			$messageContext->addMessage('ERROR', 'Errore eliminazione detail attributi');
		}
		
		deleteSessionManager($keyFile['OT5FIL'], $keyFile['OT5LIB']);
		if(isset($_REQUEST['WI400_IS_WINDOW']) && $_REQUEST['WI400_IS_WINDOW'] == '1') {
			$actionContext->gotoAction("CLOSE", 'RELOAD_PREVIOUS_WINDOW', '', true);
		} else {
			$actionContext->gotoAction($azione, 'VIS_MASCHERA', '', true);
		}
	}else if($form == 'PARAMETRI_CLIENT_ATTRIBUTI') {
		$actionContext->setLabel("Parametri client ".$_REQUEST['OT5FLD1']);
		
		if(!isset($_REQUEST['NO_SUBFILE_DELETE'])) {
			subfileDelete("PARAMETRI_CLIENT");
		}
		
		$subfile = new wi400Subfile($db, "PARAMETRI_CLIENT", $settings['db_temp'], 20);
		$subfile->setConfigFileName("PARAMETRI_CLIENT");
		$subfile->setModulo("giwi400");
		
		$subfile->addParameter("LEGAME", $keyFile['OT5KEY']);
		$subfile->addParameter("FORM", $keyForm['OT5FMT']);
		$subfile->addParameter("FIELD", $keyField['OT5FLD']);
		$subfile->addParameter("TYPE", $_REQUEST['OT5FLD1']);
		
		$subfile->setSql("*AUTOBODY");
		
		/*$sql = "SELECT OT5PRM 
				FROM $tabParametriClient 
				WHERE OT5FLD1='{$_REQUEST['OT5FLD1']}' 
				GROUP BY OT5PRM";
		$rs = $db->query($sql);
		while($row = $db->fetch_array($rs)) {
			
		}*/
		if($_REQUEST['OT5FLD1'] == 'DECODING') {
			$param_union = $param_decorator; 
		}
		if($_REQUEST['OT5FLD1'] == 'LOOKUP') {
			$param_union = $param_lookup;
		}
		
	}else if($form == 'SAVE_PARAMETRI_CLIENT') {
		
		$type = $_REQUEST['OT5FLD1'];
		
		//showArray($keyForm);
		
		$errors = saveParametriClient($keyFile['OT5KEY'], $keyForm['OT5FMT'], $keyField['OT5FLD'], $type);
		if($errors) {
			foreach($errors as $error) {
				$messageContext->addMessage('ERROR', $error);
				$actionContext->gotoAction($azione, 'PARAMETRI_CLIENT_ATTRIBUTI&OT5FLD1='.$type."&NO_SUBFILE_DELETE=1", '', true);
			}
		}else {
			$messageContext->addMessage('SUCCESS', 'Salvataggio parametri effettuato con successo');
		}
		
		$actionContext->gotoAction('CLOSE', 'RELOAD_PREVIOUS_WINDOW', '', true);
	}else if($form == 'SHOW_DATI_FIELD') {
		
		require_once 'modules/giwi400/classi/giwi400Display.cls.php';
		require_once 'modules/developer/developer_functions.php';
		
		$field = array();
		$descrizione = '';
		
		$manager = new giwi400Manager($_REQUEST['I_GIWI_FIL'], $_REQUEST['I_GIWI_FLI']);
		if(!isset($_REQUEST['IS_MANAGER'])) {
			if(!isset($_REQUEST['IS_BUTTON'])) {
				$fields = $manager->getDisplay()->getForm($_REQUEST['I_GIWI_FRM'])->getFields();
				$field = $fields[$_REQUEST['CAMPO']];
				
				$descrizione = $field->getDescription();
				if(!$descrizione) $descrizione = $_REQUEST['CAMPO'];
			}else {
				$buttonManager = $manager->getFunctionKey($_REQUEST['I_GIWI_FRM']);
				$buttonDisplay = $manager->getDisplay()->getFunctionKey();
				
				$allButton = array_merge($buttonManager, $buttonDisplay);
				
				foreach($allButton as $button) {
					if($button->getId() == $_REQUEST['CAMPO']) {
						$field = $button;
						$descrizione = $button->getId();
						break;
					}
				}
			}
		}else {
			$field = $manager;
		}
		
		$actionContext->setLabel('Attributi '.$descrizione);
	}else if($form == 'CONDIZIONI') {
		$actionContext->setLabel('Condizioni');
		
		$condizioni = $_SESSION['GIWI_CONDIZIONI'];
	}
	else if($actionContext->getForm()=="LEGAME") {
		$actionContext->setLabel("Campo Legato");
		
		if(substr($field_src, 0, 1)==="*") {
			$messageContext->addMessage("ERROR","Non è possibile legare il campo $field_src");
			$actionContext->gotoAction("CLOSE", "RELOAD_PREVIOUS_WINDOW", "", true);
		}
		
		$sql_legame = "select * from ZOT5FLDR where OT5KEY='$file_src' and OT5FMT='$form_src' and (OT5FLD='$field_src' or OT5FL1='$field_src')";
		$res_legame = $db->singleQuery($sql_legame);
		
		$has_legame = false;
		$legame = "";
		
		if($row = $db->fetch_array($res_legame)) {
			if($field_src==$row['OT5FL1']) {
				$messageContext->addMessage("ERROR","Il campo $field_src è già stato usato come legame");
				$actionContext->gotoAction("CLOSE", "RELOAD_PREVIOUS_WINDOW", "", true);
			}
			
			$has_legame = true;
			$legame = $row['OT5FL1'];
		}
		else {
			if(isset($_POST['LEGAME']))
				$legame = $_POST['LEGAME'];
		}
	}
	else if($actionContext->getForm()=="INSERT_LAGAME") {
//		echo "FILE: $file_src - FORM: $form_src - FIELD: $field_src<br>";
//		echo "LEGAME: ".$_POST['LEGAME']."<br>";
		
		$fieldValues = array(
			"OT5KEY" => $file_src,
			"OT5FMT" => $form_src,
			"OT5FLD" => $field_src,
			"OT5FL1" => $_POST['LEGAME'],
		);
		
		$stmt_ins = $db->prepare("INSERT", "ZOT5FLDR", null, array_keys($fieldValues));
		
		$result = $db->execute($stmt_ins, $fieldValues);
			
		if(!$result) {
			$messageContext->addMessage("ERROR","Errore durante l'aggiunta del legame");
		}
		else {
			$messageContext->addMessage("SUCCESS","Legame di $field_src aggiunto con successo");
		}
		
		$actionContext->onSuccess("CLOSE", "RELOAD_PREVIOUS_WINDOW");
		$actionContext->onError($azione, "LEGAME", "", "", true);
	}
	else if($actionContext->getForm()=="INSERT_ADDNEW") {
		//showArray($keyField);
		$fieldsA = getDs("ZOT5FLDF");
		$fieldsL = getDs("ZOT5FLDL");
		//showArray($fieldsA);
		//showArray($fieldsL);
		
		$dati = wi400Detail::getDetailValues($azione."_RIEP_LIST");
		$datiFld = wi400Detail::getDetailValues($azione."_ADDNEW");
		
		$keyForm = getListKeyArray($azione."_LIST_FORM");
		//showArray($dati);
		//showArray($datiFld);
		//showArray($keyForm);die();
		// Valorizzazione e scrittura FLDF
		$fieldsA['OT5KEY'] = $dati['CHIAVE'];
		$fieldsA['OT5FMT'] = $dati['FORM'];
		$fieldsA['OT5FLD'] = $datiFld['CAMPO'];
		$fieldsA['OT5FOP'] = $datiFld['PHPOUT'];
		$fieldsA['OT5SEQ'] = 99999;
		$stmt_ins = $db->prepare("INSERT", "ZOT5FLDF", null, array_keys($fieldsA));
		$result = $db->execute($stmt_ins, $fieldsA);
		// Valorizzazione e srittura FLDL
		$fieldsL['OT5KEY'] = $dati['CHIAVE'];
		$fieldsL['OT5FMT'] = $dati['FORM'];
		$fieldsL['OT5FLD'] = $datiFld['CAMPO'];
		$fieldsL['OT5USE'] = "§";
		$fieldsL['OT5TYP'] = "O";
		$stmt_ins = $db->prepare("INSERT", "ZOT5FLDL", null, array_keys($fieldsL));
		$result = $db->execute($stmt_ins, $fieldsL);
		// Verifica errori e proseguimento
		$actionContext->onSuccess("CLOSE", "RELOAD_PREVIOUS_WINDOW");
		$actionContext->onError($azione, "ADDNEW", "", "", true);
		
	}
	else if($actionContext->getForm()=="UPDATE_LAGAME") {
		$keyUpdt = array("OT5KEY" => $file_src, "OT5FMT" => $form_src, "OT5FLD" => $field_src);
		$fieldValues = array("OT5FL1" => $_POST['LEGAME']);

		$stmt_updt = $db->prepare("UPDATE", "ZOT5FLDR", $keyUpdt, array_keys($fieldValues));
			
		$result = $db->execute($stmt_updt, $fieldValues);
			
		if(!$result) {
			$messageContext->addMessage("ERROR","Errore durante l'aggiornamento del legame");
		}
		else {
			$messageContext->addMessage("SUCCESS","Legame di $field_src aggiornato con successo");
		}
		
		$actionContext->onSuccess("CLOSE", "RELOAD_PREVIOUS_WINDOW");
		$actionContext->onError($azione, "LEGAME", "", "", true);
	}
	else if($actionContext->getForm()=="DELETE_LAGAME") {
		$keyDel = array("OT5KEY", "OT5FMT", "OT5FLD");
		$stmt_del = $db->prepare("DELETE", "ZOT5FLDR", $keyDel, null);
		
		$campi = array($file_src, $form_src, $field_src);
		
		$result = $db->execute($stmt_del, $campi);
		
		if(!$result) {
			$messageContext->addMessage("ERROR","Errore durante l'eliminazione del legame");
		}
		else {
			$messageContext->addMessage("SUCCESS","Legame di $field_src eliminato con successo");
		}
		
		$actionContext->onSuccess("CLOSE", "RELOAD_PREVIOUS_WINDOW");
		$actionContext->onError($azione, "LEGAME", "", "", true);
	} 