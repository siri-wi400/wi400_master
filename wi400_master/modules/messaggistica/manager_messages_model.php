<?php
//	require_once 'manager_messages_common.php';
	require_once 'manager_messages_function.php';
	
	require_once $routine_path.'/classi/wi400AnnounceMessage.cls.php';
/*	
	use PhpOffice\PhpSpreadsheet\IOFactory as PHPExcel_IOFactory;
	use PhpOffice\PhpSpreadsheet\Cell\Coordinate as PHPExcel_Cell;
*/
	//echo "server: ".getServerAddress();
	/*echo "max_input_time: ".ini_get('max_input_time')."<br/>";
	echo "upload_max_filesize: ".ini_get('upload_max_filesize')."<br/>";
	echo "post_max_size: ".ini_get('post_max_size')."<br/>";
	echo "memory_limit: ".ini_get('memory_limit')."<br/>";*/
	
	$azione = $actionContext->getAction();
	
	$checkGroup = 0;
	//showArray($_SESSION['WI400_GROUPS']);
	
	foreach($_SESSION['WI400_GROUPS'] as $gruppo) {
		if(strpos($gruppo, "MSG_") !== false) {
			$checkGroup = 1;
			break;
		}
	}
	
	if(in_array($actionContext->getForm(), array("DEFAULT", "DETAIL_LOG", "CONTENUTO", "ALLEGATI", "DESTINATARI"))) {
		$history->addCurrent();
	}
	
	if($actionContext->getForm() != "DEFAULT") {
		$key = getListKeyArray($azione."_HOME_LIST");
	}
	
	if($actionContext->getForm() == "DEFAULT") {
		
	}
	else if($actionContext->getForm() == "DETAIL_LOG") {
		$actionContext->setLabel("Dettaglio log");
	}
	else if($actionContext->getForm() == "NEW_MESSAGE") {
		$actionContext->setLabel("Nuovo messaggio");
		
	}
	else if($actionContext->getForm() == "MOD_MESSAGE") {
		$actionContext->setLabel("Modifica messaggio");
	}
	else if($actionContext->getForm() == "PUBBLICA_MESS") {
		$error = array();
		if($key['TESFMT'] == "TXT") {
			$sql = "select ctttxt from zmsgctt where cttid='{$key['TESID']}' AND CTTRIG>0";
		}else {
			$sql = "select cthhtm from zmsgcth where cthid='{$key['TESID']}'";
		}
		
		$rs = $db->singleQuery($sql);
		if(!$row = $db->fetch_array($rs)) {
			$error[] = "Non hai inserito il contenuto del messaggio!";
		}
		
		$sql = "select DSTDST from ZMSGDST where DSTID='{$key['TESID']}'";
		$rs = $db->singleQuery($sql);
		if(!$row = $db->fetch_array($rs)) {
			$error[] = "Non hai inserito nessun destinatario per il messaggio!";
		}
		
		if($error) {
			$messageContext->addMessage("ERROR", implode("<br/>", $error));
		}
		else {
/*			
			$file = "ZMSGTES";
			$field = array(	"TESSTA" => '1',
					"USRMOD" => $_SESSION['user'],
					"TMSMOD" => getDb2Timestamp(date('d/m/Y H:i:s')));
			$key = array("TESID" => $key['TESID']);
			
			//showArray($key);
			//showArray($field);
			
			$stmtTes = $db->prepare("UPDATE", $file, $key, array_keys($field));
			
			$result = $db->execute($stmtTes, $field);
			
			if($result)  {
				$messageContext->addMessage("SUCCESS", "Messaggio pubblicato con successo!");
				
				$announce = new wi400AnnounceMessage();
				$dati_tes = $announce->getTestata($key['TESID']);
				
				if($dati_tes['TESDIV'] == "*IMMED") {
					$rs = $announce->divulgaMessageId($key['TESID']);
				}
			}
			else {
				$messageContext->addMessage("ERROR", "Errore pubblicazione messaggio!");
			}
*/
			$res_pub = pubblica_messaggio($key['TESID'], true);			
		}
		
		$actionContext->gotoAction($azione, "DEFAULT", false, true);
	}
	else if($actionContext->getForm() == "ELIMINA_MESSAGE") {
		$clear_tab = array("dst", "atc", "ctt", "cth", "tes", "log", "prm");
		$error = array();
		
		foreach($clear_tab as $file) {
			$sql = "DELETE FROM zmsg".$file." WHERE ".$file."id='{$key['TESID']}'";
			$rs = $db->query($sql);
			
			if(!$rs) {
				$error[] = "Errore eliminazione zmsg".$file;
			}
		}
		
		if(!$error) {
			$messageContext->addMessage("SUCCESS", "Messaggio eliminato con successo!");
		}else {
			$messageContext->addMessage("ERROR", implode("<br/>", $error));
		}
		
		$actionContext->gotoAction($azione, "DEFAULT", false, true);
	}else if(in_array($actionContext->getForm(), array("INSERT_MESSAGE", "UPDATE_MESSAGE"))) {
		//global $db;
		
		$update = "";
		if($actionContext->getForm() == "UPDATE_MESSAGE") {
			$update = 1;
			
			$log_error = "Errore modifica messaggio";
			$log_succ = "Messaggio modificato con successo";
		}else {
			$id = "MSG_".getSysSequence("MESSAGES");
			$log_error = "Errore inserimento messaggio";
			$log_succ = "Messaggio inserito con successo";
		}
		
		$tespub = date('d/m/Y');
		$tessca = "";
		$error = array();
		/*$tesrpy = "N";
		$tesevr = "N";
		$tesprv = "N";*/
		
		//showArray($_REQUEST);
		
		$timestamp = date('d/m/Y H:i:s');
		
		if(!$update) {
//			$sql_header = "INSERT INTO PHPLIB/ZMSGCTT VALUES('$id', 'ITA', 0, '{$_REQUEST['TITOLO']}', '1', '{$_SESSION['user']}', '".getDb2Timestamp($timestamp)."', '{$_SESSION['user']}', '".getDb2Timestamp($timestamp)."')";
			$sql_header = "INSERT INTO ZMSGCTT VALUES('$id', 'ITA', 0, '{$_REQUEST['TITOLO']}', '1', '{$_SESSION['user']}', '".getDb2Timestamp($timestamp)."', '{$_SESSION['user']}', '".getDb2Timestamp($timestamp)."')";			
			$rs = $db->query($sql_header);
			if(!$rs) {
				$error[] = "Errore inserimento titolo!";
			}
		}else {
			if($_REQUEST['OLD_TITOLO'] != $_REQUEST['TITOLO']) {
				//Controllo se il titolo contiene apostrofi  --> in db2 gli apostrofi sono così => ''
				$_REQUEST['TITOLO'] = str_replace("'", "''", $_REQUEST['TITOLO']);
				$sql_header = "UPDATE ZMSGCTT SET ctttxt='{$_REQUEST['TITOLO']}' WHERE cttid='{$key['TESID']}' AND cttrig=0";
				
				$rs = $db->query($sql_header);
				if(!$rs) {
					$error[] = "Errore modifica titolo testata";
				}
			}
		}
		
		$tesFile = "ZMSGTES";
		$fieldTes = getDs($tesFile);
		
		$fieldTes['TESGRP'] = $_REQUEST['TESGRP'];
		$fieldTes['TESFMT'] = $_REQUEST['TESFMT'];
		$fieldTes['TESTYP'] = $_REQUEST['TESTYP'];
		$fieldTes['TESTNO'] = $_REQUEST['TESTNO'];
		$fieldTes['TESCLE'] = $tescle;
		$fieldTes['TESRPY'] = $tesrpy;
		$fieldTes['TESVIS'] = $_REQUEST['TESVIS'];
		$fieldTes['TESPRV'] = $tesprv;
		$fieldTes['TESTO'] = $_REQUEST['TESTO'];
		$fieldTes['TESRPYT'] = $_REQUEST['TESRPYT'];
		$fieldTes['TESAZI'] = $_REQUEST['TESAZI'];
		$fieldTes['TESDIV'] =  $_REQUEST['TESDIV'];
		$fieldTes['TESPUB'] = getDb2Timestamp($_REQUEST['TESPUB']);
		$fieldTes['TESSCA'] = getDb2Timestamp($_REQUEST['TESSCA']);
		$fieldTes['TESPRY'] = $_REQUEST['TESPRY'];
		$fieldTes['TESEVR'] = $tesevr;
		$fieldTes['USRMOD'] = $_SESSION['user'];
		$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
		$fieldTes['TESARE'] = $_REQUEST['TESARE'];
		
		if(!$update) {
			$fieldTes['TESID'] = $id;
			$fieldTes['TESUSR'] = $_SESSION['user'];
			$fieldTes['TESSTA'] = '0';
			$fieldTes['TESTOL'] = 0;
			$fieldTes['TESTOV'] = 0;
			$fieldTes['TESTOR'] = 0;
			$fieldTes['USRINS'] = $_SESSION['user'];
			$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);
		}else {
			$fieldTes['TESID'] = $key['TESID'];
			unset($fieldTes['TESUSR']);
			unset($fieldTes['TESSTA']);
			unset($fieldTes['TESTOL']);
			unset($fieldTes['TESTOV']);
			unset($fieldTes['TESTOR']);
				
			unset($fieldTes['USRINS']);
			unset($fieldTes['TMSINS']);
		}
		
		//$fieldsName = array("TESID", "TESFMT","TESTYP", "TESRPY", "TESVIS", "TESUSR", "TESRPYT", "TESAZI", "TESDIV", "TESPUB", "TESSCA", "TESPRY", "TESEVR", "TESSTA", "TESTOL", "TESTOV", "TESTOR");
		if(!$update) {
			$stmtTes = $db->prepare("INSERT", $tesFile, null, array_keys($fieldTes));
		}else {
			$chiavi = array("TESID" => $key['TESID']);
			$stmtTes = $db->prepare("UPDATE", $tesFile, $chiavi, array_keys($fieldTes));
		}
		//$fieldsValue = array($id, $_REQUEST['TESFMT'], $_REQUEST['TESTYP'], $tesrpy, "*".$_REQUEST['TESVIS'], $_SESSION['user'], $_REQUEST['TESRPYT'], $_REQUEST['TESAZI'], "*".$_REQUEST['TESDIV'], getDb2Timestamp($tespub), getDb2Timestamp(tessca), $_REQUEST['TESPRY'], $tesevr, '0', 0, 0, 0);
		
		$result = $db->execute($stmtTes, $fieldTes);
		
		if(!$result) {
			$error[] = $log_error;
		}
		
		if(!$error) {
			$messageContext->addMessage("SUCCESS", $log_succ);
		}else {
			$messageContext->addMessage("ERROR", implode("<br/>", $error));
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
	}else if($actionContext->getForm() == "CONTENUTO") {
		$actionContext->setLabel("Contenuto");
		
		if($key['TESFMT'] == "TXT") {
			$sql = "SELECT CTTID, CTTTXT FROM ZMSGCTT WHERE CTTID='{$key['TESID']}' and CTTRIG>0";
			$rs = $db->query($sql);
			$contenuto = array();
			while($row = $db->fetch_array($rs)) {
				$contenuto[] = $row['CTTTXT']; 
			}
			$contenuto = implode("\n", $contenuto);
		}else {
			$sql = "SELECT CTHID, CTHHTM FROM ZMSGCTH WHERE CTHID='".$key['TESID']."'";
			$rs = $db->query($sql);
			$contenuto = "";
			if($row = $db->fetch_array($rs)) {
				$contenuto = $row['CTHHTM'];
			}
		}
	}else if($actionContext->getForm() == "INSERT_CONTENUTO_MESSAGE") {
		$timestamp = date('d/m/Y H:i:s');
		
		$error = array();
		//showArray($key);
		//showArray($_REQUEST);
		
		if($key['TESFMT'] == "TXT") {
			$tesFile = "ZMSGCTT";
			$fieldTes = getDs($tesFile);
			
			if(isset($_REQUEST['UPDATE'])) {
				$sql = "DELETE FROM ZMSGCTT WHERE cttid='".$key['TESID']."' and cttrig>0";
				$rs = $db->query($sql);
				if(!$rs) {
					$error[] = "Errore eliminazione vecchio contenuto";
				}
				
				unset($fieldTes['USRINS']);
				unset($fieldTes['TMSINS']);
			}
			
			$stmtTes = $db->prepare("INSERT", $tesFile, null, array_keys($fieldTes));
				
			$righe = explode("\n", $_REQUEST['AREA_CONTENUTO']);
			foreach($righe as $chiave => $riga) {
				$fieldTes['CTTID'] = $key['TESID'];
				$fieldTes['CTTLNG'] = 'ITA';
				$fieldTes['CTTRIG'] = $chiave+1;
				$fieldTes['CTTTXT'] = $riga;
				$fieldTes['CTTSTA'] = '1';
				$fieldTes['USRMOD'] = $_SESSION['user'];
				$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
				if(!isset($_REQUEST['UPDATE'])) {
					$fieldTes['USRINS'] = $_SESSION['user'];
					$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);
				}
		
				$result = $db->execute($stmtTes, $fieldTes);
				if(!$result && !in_array($error, "Errore inserimento nuovo contenuto")) {
					$error[] = "Errore inserimento nuovo contenuto";
				}
			}
				
			if($error) {
				$messageContext->addMessage("ERROR", implode("<br/>", $error));
			}else {
				$messageContext->addMessage("SUCCESS", "Contenuto messaggio salvato con successo!");
			}
		}else {
			$tesFile = "ZMSGCTH";
			//TODO aggiornare anche usermod e timestamp mod
			if(isset($_REQUEST['UPDATE'])) {

				$field = array("CTHHTM" => $_REQUEST['EDITOR'],
								 "USRMOD" => $_SESSION['user'],
								 "TMSMOD" => getDb2Timestamp($timestamp));
				$key = array("CTHID" => $key['TESID']);
				$stmtTes = $db->prepare("UPDATE", $tesFile, $key, array_keys($field));

				$result = $db->execute($stmtTes, $field);
			
				if(!$result) {
					$error[] = "Errore update";
				}
			}else {
				$fieldTes = getDs($tesFile);
				$fieldTes['CTHHTM'] = "";
				//showArray($fieldTes);
				$stmtTes = $db->prepare("INSERT", $tesFile, null, array_keys($fieldTes));
				
				$fieldTes['CTHID'] = $key['TESID'];
				$fieldTes['CTHLNG'] = 'ITA';
				$fieldTes['CTHHTM'] = $_REQUEST['EDITOR'];
				$fieldTes['CTHSTA'] = '1';
				$fieldTes['USRMOD'] = $_SESSION['user'];
				$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
				$fieldTes['USRINS'] = $_SESSION['user'];
				$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);
				
				$result = $db->execute($stmtTes, $fieldTes);
				if(!$result)  {
					$error[] = "Errore inserimento del contenuto";
				}
			}
			
			if($error) {
				$messageContext->addMessage("ERROR", implode("\n", $error));
			}else {
				$messageContext->addMessage("SUCCESS", "Contenuto messaggio salvato con successo!");
			}
		}
		
		$actionContext->gotoAction($azione, "DEFAULT", false, true);
	}else if($actionContext->getForm() == "ALLEGATI") {
		$actionContext->setLabel("Allegati");
	}else if($actionContext->getForm() == "IMPORT_FILE") {
		//showArray($_FILES);
		if(isset($_FILES['IMPORT_FILE']) && is_uploaded_file($_FILES['IMPORT_FILE']['tmp_name'])) {
			echo "Sono entrato?";
			$file_name = $_FILES['IMPORT_FILE']['name'];
			
			$folder_mess = $data_path."messages";
			
			if(!file_exists($folder_mess)) {
				wi400_mkdir($folder_mess);
			}
			
			$target_file = $data_path."messages/".$key['TESID']."/";
				
			if(!file_exists($target_file)) {
				wi400_mkdir($target_file);
			}
		
			$target_file .= $_FILES['IMPORT_FILE']['name'];
			echo $target_file."<br/>";
				
			//$file_parts = pathinfo($file_name);
			if (move_uploaded_file($_FILES["IMPORT_FILE"]["tmp_name"], $target_file)) {
				//Faccio l'insert
				echo "<br/>Sono entrato 2?<br/>";
/*				
				$timestamp = date('d/m/Y H:i:s');
				
				$fieldTes = getDs("ZMSGATC");
				$stmtTes = $db->prepare("INSERT", "ZMSGATC", null, array_keys($fieldTes));
				
				$fieldTes['ATCID'] = $key['TESID'];
				$fieldTes['ATCLNG'] = 'ITA';
				$fieldTes['ATCATC'] = $target_file;
				$fieldTes['ATCSTA'] = '1';
				$fieldTes['USRMOD'] = $_SESSION['user'];
				$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
				$fieldTes['USRINS'] = $_SESSION['user'];
				$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);				
				
				$result = $db->execute($stmtTes, $fieldTes);
*/
				$ann_msg_obj = new wi400AnnounceMessageSet($key['TESID']);
				
				$result = $ann_msg_obj->saveAllegato($target_file);		
						
				if($result)  {
					$messageContext->addMessage("SUCCESS", "Allegato caricato con successo");
				}else {
					$messageContext->addMessage("ERROR", "Errore inserimento allegato");
				}
			}else {
				$messageContext->addMessage("ERROR", "Errore caricamento allegato");
			}
		}else {
			$messageContext->addMessage("ERROR", "Errore caricamento file");
		}
		$actionContext->gotoAction($azione, "ALLEGATI", false, true);
	}else if($actionContext->getForm() == "ELIMINA_ALLEGATO") {
		$allegato = getListKeyArray($azione."_LIST_ALLEGATI");
/*	
		$sql = "DELETE FROM ZMSGATC WHERE ATCATC='{$allegato['ATCATC']}'";
		$rs = $db->query($sql);
		
		if($rs) {
			$folder = $data_path."messages/".$key['TESID'];
			
			$nome = explode("/", $allegato['ATCATC']);
			$nome = $nome[count($nome)-1];
			
			$file_path = $folder."/".$nome;
			$result = unlink($file_path);
			
			// 0 => . 1 => .. quindi 2 se vuota
			if(count(scandir($folder)) == 2) {
				rmdir($folder);
			}
			
			$messageContext->addMessage("SUCCESS", "Allegato eliminato con successo!");
		}else {
			$messageContext->addMessage("ERROR", "Errore eliminazione allegato");
		}
*/
		$ann_msg_obj = new wi400AnnounceMessageSet($key['TESID']);
		
		$result = $ann_msg_obj->deleteAllegato($allegato['ATCATC']);
		
		if($res===true)
			$messageContext->addMessage("SUCCESS", "Allegato eliminato con successo!");
		else 
			$messageContext->addMessage("ERROR", "Errore eliminazione allegato");
		
		$actionContext->gotoAction($azione, "ALLEGATI", false, true);
	}else if($actionContext->getForm() == "DESTINATARI") {
		$actionContext->setLabel("Destinatari");
		$files_path = $settings['template_path']."template_destinatari.xlsx";
	}else if($actionContext->getForm() == "NEW_DESTINATARIO") {
		$actionContext->setLabel("Nuovo destinatario");
		
		if(isset($_REQUEST['DSTTYP'])) {
			$dest = array();
			$dest['DSTTYP'] = $_REQUEST['DSTTYP'];
		}
	}else if($actionContext->getForm() == "MOD_DESTINATARIO") {
		$actionContext->setLabel("Modifica destinatario");
		
		$dest = getListKeyArray($azione."_LIST_DESTINATARI");
		if(isset($_REQUEST['DSTTYP'])) {
			$dest['DSTTYP'] = $_REQUEST['DSTTYP'];
			$dest['DSTDST'] = "";
		}
	}else if($actionContext->getForm() == "INSERT_DESTINATARIO") {
		$file = "ZMSGDST";
		$fieldTes = getDs($file);
		$timestamp = date('d/m/Y H:i:s');
		//showArray($fieldTes);
		
		//showArray($_REQUEST);
		$stmtTes = $db->prepare("INSERT", $file, null, array_keys($fieldTes));
		
		$fieldTes['DSTID'] = $key['TESID'];
		$fieldTes['DSTTYP'] = $_REQUEST['DSTTYP'];
		$fieldTes['DSTDST'] = $_REQUEST['DSTDST'];
		$fieldTes['DSTIOE'] = $_REQUEST['DSTIOE'] == "Includi" ? "I" : "E";
		$fieldTes['DSTOOA'] = $_REQUEST['DSTOOA'];
		$fieldTes['DSTSEQ'] = $_REQUEST['DSTSEQ'];
		$fieldTes['DSTSTA'] = '1';
		$fieldTes['USRMOD'] = $_SESSION['user'];
		$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
		$fieldTes['USRINS'] = $_SESSION['user'];
		$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);
		
		//showArray($fieldTes);
		
		$result = $db->execute($stmtTes, $fieldTes);
		
		if($result)  {
			$messageContext->addMessage("SUCCESS", "Destinatario inserito con successo");
			
			$res_pub = pubblica_messaggio($key['TESID']);
		}else {
			$messageContext->addMessage("ERROR", "Errore inserimento destinatario");
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW", false, true);
	}else if($actionContext->getForm() == "UPDATE_DESTINATARIO") {
		$timestamp = date('d/m/Y H:i:s');
		
		$file = "ZMSGDST";
		$field = array(	"DSTTYP" => $_REQUEST['DSTTYP'],
						"DSTDST" => $_REQUEST['DSTDST'],
						"DSTIOE" => $_REQUEST['DSTIOE'] == "Includi" ? "I" : "E",
						"DSTOOA" => $_REQUEST['DSTOOA'],
						"DSTSEQ" => $_REQUEST['DSTSEQ'],
						 "USRMOD" => $_SESSION['user'],
						 "TMSMOD" => getDb2Timestamp($timestamp));
		$chiavi = array("DSTID" => $key['TESID'],
						"DSTDST" => $_REQUEST['OLD_DST']);
		
		$stmtTes = $db->prepare("UPDATE", $file, $chiavi, array_keys($field));

		$result = $db->execute($stmtTes, $field);
	
		if($result)  {
			$file = "ZMSGTES";
			
			$field = array("USRMOD" => $_SESSION['user'],
							"TMSMOD" => getDb2Timestamp($timestamp));
			$chiavi = array("TESID" => $key['TESID']);

			
			$stmtTes = $db->prepare("UPDATE", $file, $chiavi, array_keys($field));
			
			$result = $db->execute($stmtTes, $field);
			
			if($result) {
				echo "Si";
			}else {
				echo "No";
			}
			//die();
			$messageContext->addMessage("SUCCESS", "Destinatario modificato con successo");
			
			$res_pub = pubblica_messaggio($key['TESID']);
		}else {
			$messageContext->addMessage("ERROR", "Errore modifica destinatario");
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW", false, true);
	}else if($actionContext->getForm() == "ELIMINA_DESTINATARIO") {
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_POST['IDLIST']);
		
		$campi = array("DSTTYP", "DSTDST", "DSTIOE", "DSTOOA", "DSTSEQ");
		
		foreach($wi400List->getSelectionArray() as $k => $value) {
			$dest = explode("|", $k);
			
			$sql = "DELETE FROM ZMSGDST WHERE DSTID='{$key['TESID']}' AND ";
			
			$where = array();
			foreach($dest as $chiave => $valore) {
				$where[] = $campi[$chiave]."='".$valore."'";
			}
			
			$sql .= implode(" AND ", $where);
			$rs = $db->query($sql);
			if($rs) {
				$messageContext->addMessage("SUCCESS", "Destinatario {$dest[1]} eliminato con successo!");
			}else {
				$messageContext->addMessage("ERROR", "Errore eliminazione destinatario {$dest[1]}!");
			}
		}
		
		$actionContext->gotoAction($azione, "DESTINATARI", false, true);
	}else if($actionContext->getForm() == "SVUOTA_DESTINATARIO") {
		$query = "DELETE FROM ZMSGDST WHERE DSTID='{$key['TESID']}'";
		
		if($db->query($query)) {
			$messageContext->addMessage("SUCCESS", "Lista destinatari svuotata con successo!");
		}else {
			$messageContext->addMessage("ERROR", "Errore svuotamento lista destinatari!");
		}
		
		$actionContext->gotoAction($azione, "DESTINATARI", false, true);
	}else if($actionContext->getForm() == "SIMULA_DIVULGAZIONE") {
		$actionContext->setLabel("Simulazione divulgazione messaggi");
		
		$checkEntiInt = 0;
		$sql = "SELECT dsttyp FROM zmsgdst where dstid='{$key['TESID']}' AND dsttyp in ('*INT', '*ENTE')";
		$rs = $db->query($sql);
		if($db->num_rows($rs)) {
			$checkEntiInt = 1;
		}
	}
	else if($actionContext->getForm()=="PARAMS_SEL") {
		// @todo Parametri Aggiuntivi
		$actionContext->setLabel("Parametri Aggiuntivi");
	}
	else if($actionContext->getForm()=="SAVE_PARAMS") {
		
	}
	
	if($actionContext->getForm()=="DESTINATARI_EXCEL") {
		global $routine_path;
		
		$timestamp = date('d/m/Y H:i:s');
		// Aumentata la dimensione del limite della memoria
		ini_set("memory_limit","1000M");
		set_time_limit(0);
	
		if(isset($_FILES['IMPORT_FILE']) && is_uploaded_file($_FILES['IMPORT_FILE']['tmp_name'])) {
			// Controllo che il file non superi i 1 MB
			if($_FILES['IMPORT_FILE']['size'] > 1200000) {
				$messageContext->addMessage("ERROR","Il file non deve superare 1 MB!");
			}else {
				$file_name = $_FILES['IMPORT_FILE']['name'];
				$file_parts = pathinfo($file_name);
				//echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
	
				$imgExt = $file_parts['extension'];
	
				if(!in_array($imgExt, array("xls", "xlsx", "XLS", "XLSX"))) {
					$messageContext->addMessage("ERROR","Il file deve essere in formato xls/xlsx.");
				}
				else if(!file_exists($_FILES['IMPORT_FILE']['tmp_name'])) {
					$messageContext->addMessage("ERROR","File non trovato.");
				}
				else {
/*					
					checkIfZipLoaded();
	
					// PHPExcel
					require_once $routine_path."/excel/PHPExcel.php";
	
					// PHPExcel_IOFactory
					require_once $routine_path.'/excel/PHPExcel/IOFactory.php';
*/	
					$load_file_name = $_FILES['IMPORT_FILE']['tmp_name'];
					
					$classe_export = "";
					if(isset($settings['classe_export']) && $settings['classe_export']=="PhpSpreadsheet") {
//						require_once $routine_path."/generali/xls_common_PhpSpreadsheet.php";
						require_once $routine_path.'/vendor/autoload.php';
						
						$classe_export = "PhpSpreadsheet";
						$col_ini = 1;
							
						$importType = PhpOffice\PhpSpreadsheet\IOFactory::identify($load_file_name);
					}
					else {
						$classe_export = "PhpExcel";
						require_once $routine_path."/generali/xls_common.php";
						$col_ini = 0;
							
						switch($imgExt) {
							case "xls":
							case "XLS":
								$importType = "Excel5";
								break;
							case "xlsx":
							case "XLSX":
								$importType = "Excel2007";
								break;
						}
					}
//					echo "CLASSE_EXPORT: $classe_export<br>";
//					echo "COL_INI: $col_ini<br>";
//					echo "FILE TYPE: $importType<br>";
					
					$PhpS_use = false;			// true se abbandoniamo completamente PhpExcel e attiviamo gli use all'inizio del programma
					
					// Lettura del file Excel
					
					// Create new PHPExcel object
					if($classe_export=="PhpSpreadsheet" && $PhpS_use===false) {
//						echo "INIZIO PHPSPREADSHEET<br>";
						
						$objReader = PhpOffice\PhpSpreadsheet\IOFactory::createReader($importType);
					}
					else {
//						echo "INIZIO PHPEXCEL<br>";
						
						$objReader = PHPExcel_IOFactory::createReader($importType);
					}
					
					$objPHPExcel = $objReader->load($load_file_name);
					//echo "<font color='red'>LOAD FILE</font><br>";
						
					$sheet = $objPHPExcel->getActiveSheet();
	
					$max_rows = $sheet->getHighestRow();
					//echo "MAX ROWS: $max_rows<br>";
					if($classe_export=="PhpSpreadsheet" && $PhpS_use===false)
						$max_cols = PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());
					else
						$max_cols = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
					$max_cols += $col_ini;
//					echo "MAX COLS: $max_cols<br>";
	
					$file="ZMSGDST";
//					$id_lista = trim($key['TESID']);
					// Pulisco il file degli articoli esclusi
//					$query = "DELETE FROM $file where DSTID = '$id_lista'";
//					$stmt_elimina = $db->prepareStatement($query);
//					$rs = $db->execute($stmt_elimina);

					// Controllo Duplicati
					$query_doppi = "SELECT * FROM ".$file." where dstid = '".$key['TESID']."' and dsttyp=? AND dstdst=?";
					$stmt_doppi = $db->singlePrepare($query_doppi);
					
					$inseriti = 0;
					$letti = 0;
	
					for($riga=2; $riga<=$max_rows; $riga++) {
						$check = 0;
						$letti++;
						$val1 = trim($sheet->getCellByColumnAndRow(0+$col_ini, $riga)->getCalculatedValue());
						$val2 = trim($sheet->getCellByColumnAndRow(1+$col_ini, $riga)->getCalculatedValue());
						$val3 = trim($sheet->getCellByColumnAndRow(2+$col_ini, $riga)->getCalculatedValue());
						$val4 = trim($sheet->getCellByColumnAndRow(3+$col_ini, $riga)->getCalculatedValue());
						$val5 = trim($sheet->getCellByColumnAndRow(4+$col_ini, $riga)->getCalculatedValue());
						// Controllo i tipi inseriti
						if ($val1 != "*USER" && $val1 != "*GRUPPO" && $val1 != "*INT" && $val1 != "*ENTE") {
							$check = 1;
							$messageContext->addMessage("ERROR","Tipo Destinatario : "."(Tipo) ".$val1." (Dest.) ".$val2." (I/E) ".$val3." (AND/OR) ".$val4." (SEQ.) ".$val5." non valido");
						}
						// Controllo i gruppi inseriti
						if ($val1 == "*GRUPPO") {
							$wi400_groups=array();
							if (isset($settings['wi400_groups']) && !empty($settings['wi400_groups'])){
								$wi400_groups = explode(";",$settings['wi400_groups']);
							}
							if (isset($settings['wi400_sel_groups']) && !empty($settings['wi400_sel_groups'])){
								$wi400_sel_groups = explode(";",$settings['wi400_sel_groups']);
								$wi400_groups = array_merge($wi400_groups, $wi400_sel_groups);
							}
							//$gruppi = explode(";", $wi400_groups);
							if(!in_array($val2, $wi400_groups)) {
								$check = 1;			
								$messageContext->addMessage("ERROR", "Gruppo inesistente "."(Tipo) ".$val1." (Dest.) ".$val2." (I/E) ".$val3." (AND/OR) ".$val4." (SEQ.) ".$val5);
							}
						}
						
						// Controllo se divulgazione *IMMED per ente e interlocutore
						if ($val1=="*INT" && $key['TESDIV']== "*IMMED" || $val1=="*ENTE" && $key['TESDIV']== "*IMMED") {
							$check = 1;
							$messageContext->addMessage("ERROR", "Impossibile salvare il destinatario con tipo divulgazione *IMMED "."(Tipo) ".$val1." (Dest.) ".$val2." (I/E) ".$val3." (AND/OR) ".$val4." (SEQ.) ".$val5);
						}
						// Controllo interlocutore
						if ($val1=="*INT" && $key['TESDIV']!= "*IMMED" && getDescrizione($val1,$val2) == null) {
							$check = 1;
							$messageContext->addMessage("ERROR","Destinatario : "."(Tipo) ".$val1." (Dest.) ".$val2." (I/E) ".$val3." (AND/OR) ".$val4." (SEQ.) ".$val5." non valido");
						}
						// Controllo ente
						if ($val1=="*ENTE" && $key['TESDIV']!= "*IMMED" && getDescrizione($val1,$val2) == null) {
							$check = 1;
							$messageContext->addMessage("ERROR","Destinatario : "."(Tipo) ".$val1." (Dest.) ".$val2." (I/E) ".$val3." (AND/OR) ".$val4." (SEQ.) ".$val5." non valido");
						}	
						// Controllo utente
						if ($val1=="*USER" && getDescrizione($val1,$val2) == null) {
							$check = 1;
							$messageContext->addMessage("ERROR","Destinatario : "."(Tipo) ".$val1." (Dest.) ".$val2." (I/E) ".$val3." (AND/OR) ".$val4." (SEQ.) ".$val5." non valido");
						}
						// Controllo campo Includi/Escludi
						if ($val3 != "I" && $val3 != "E") {
							$check = 1;
							$messageContext->addMessage("ERROR","Includi Escludi : "."(Tipo) ".$val1." (Dest.) ".$val2." (I/E) ".$val3." (AND/OR) ".$val4." (SEQ.) ".$val5." non valido");
						}
						// Controllo campo AND/OR
						if ($val4 != "AND" && $val4 != "OR") {
							$check = 1;
							$messageContext->addMessage("ERROR","Clausola AND/OR : "."(Tipo) ".$val1." (Dest.) ".$val2." (I/E) ".$val3." (AND/OR) ".$val4." (SEQ.) ".$val5." non valida");
						}
						// Controllo se sequenza numerica
						if (!is_numeric($val5)) {
							$check = 1;
							$messageContext->addMessage("ERROR","Sequenza : "."(Tipo) ".$val1." (Dest.) ".$val2." (I/E) ".$val3." (AND/OR) ".$val4." (SEQ.) ".$val5." non valida");
						}
						// Controllo Doppi
						$rs = $db->execute($stmt_doppi,array($val1,$val2));
						if ($row_doppi = $db->fetch_array($stmt_doppi)) {
						$messageContext->addMessage("ERROR","Destinatario : "."(Tipo) ".$val1." (Dest.) ".$val2." (I/E) ".$val3." (AND/OR) ".$val4." (SEQ.) ".$val5." duplicato");
						$check = 1;
						}
						
						if($check != 1) {
							$keyInsert = getDs($file);
							
							$fieldsValue = array();
							$fieldsValue['DSTID'] = $key['TESID'];
							$fieldsValue['DSTTYP'] = $val1;
							$fieldsValue['DSTDST'] = $val2;
							$fieldsValue['DSTIOE'] = $val3;
							$fieldsValue['DSTOOA'] = $val4;
							$fieldsValue['DSTSEQ'] = $val5;
							$fieldsValue['DSTSTA'] = '1';
							$fieldsValue['USRMOD'] = $_SESSION['user'];
							$fieldsValue['TMSMOD'] = getDb2Timestamp($timestamp);
							$fieldsValue['USRINS'] = $_SESSION['user'];
							$fieldsValue['TMSINS'] = getDb2Timestamp($timestamp);
//							echo "CAMPI:<pre>"; print_r($fieldsValue); echo "</pre>";

							$stmt_insert = $db->prepare("INSERT", $file, $keyInsert, array_keys($fieldsValue));
							$rs = $db->execute($stmt_insert, $fieldsValue);
							
							if($rs) {
								$messageContext->addMessage("INFO","Destinatario "."(Tipo) ".$val1." (Dest.) ".$val2." (I/E) ".$val3." (AND/OR) ".$val4." (SEQ.) ".$val5." caricato");
								$inseriti ++;
							}
						}
					}
				}
			}
		}
		else{
			$messageContext->addMessage("ERROR","Selezionare un file");
			$actionContext->gotoAction($azione, "DESTINATARI", "", true);
		}
		
		if($inseriti>0) {
			$res_pub = pubblica_messaggio($key['TESID']);
		}
		
		$messageContext->addMessage("INFO","Sono stati caricati ".$inseriti." destinatari su ".$letti);
		$actionContext->gotoAction($azione, "DESTINATARI", "", true);
	}