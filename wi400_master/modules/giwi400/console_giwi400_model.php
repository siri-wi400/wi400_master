<?php

	require_once 'classi/giwi400Cmd.cls.php';
	require_once 'classi/giwi400.cls.php';
	require_once 'console_giwi400_commons.php';
	require_once 'classi/'.$useClass.".cls.php";
	
	$time_start = microtime_float();

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	if(isset($_SESSION['GIWI400_ID']) && $_SESSION['GIWI400_ID']) {
		$id = $_SESSION['GIWI400_ID'];
	}
	
	$param = wi400Detail::getDetailValues("CONSOLE_GIWI400_NOME_PROGRAM"); 
	
	//echo "current_form___".$_SESSION['GIWI400_CURRENT_FORM']."<br>";
	if($form == 'AJAX_SAVE_OPEN_CLOSE_DETAIL') {
		
		$_SESSION['GIWI400_OPEN_CLOSE'] = $_REQUEST['OPEN_CLOSE'];
		
		//echo 'open_close: '.$_SESSION['GIWI400_OPEN_CLOSE'];
		
		die();
	}else if($form == 'AJAX_PRESS_BUTTON') {
		$return = array('success' => false, 'target' => '', 'error' => false, 'libreria' => '', 'file' => '', 'form' => '');
		
		$retry = isset($_REQUEST['RETRY']) ? true : false;
		
		$errorMessages = false;
		if(!$retry) {
			$errorMessages = $messageContext->getMessages();  
			if($errorMessages) {
				//$firephp->fb('errori');
				//$firephp->fb($errorMessages);
				
				$return['error'] = true;
			}
		}
		
		if(!$errorMessages && isset($_SESSION['GIWI400_FILE']) && $_SESSION['GIWI400_FILE']) {
			if(!$retry) {
				$time2 = microtime_float();
				$string_xml = file_get_contents($_SESSION['GIWI400_FILE'], FILE_USE_INCLUDE_PATH);
				writeDurationProgram($time2, $id, 'LETTURA file_get_contents');
				//$firephp->fb($_REQUEST);
				
				$time2 = microtime_float();
				$giwi400Cmd = new giwi400Cmd($string_xml);
				writeDurationProgram($time2, $id, 'Creazione classe giw400Cmd');
				$giwi400Cmd->setIndicatoreButtone($_REQUEST['INDICATORE_BOTTONE']);
				$giwi400Cmd->setDatiDetailAndFile();
				writeDurationProgram($time2, $id, 'setDatiDetailAndFile');
				$rs = $giwi400Cmd->createCmdFileXml($_SESSION['GIWI400_FILE']);
				writeDurationProgram($time2, $id, 'Totale set valori nell\'xml + saveDB + creazione file xml');
			}else {
				$rs = true;
			}
				
			if($rs) {
		
				if(!$retry) {
					$progressivo = '';
					$operazione  = 'EXFMT';
					$time2 = microtime_float();
					$output = writeCoda($progressivo, $id, $operazione);
					writeDurationProgram($time2, $id, 'writeCoda');
				}
				//error_log('OUTPUT WRITE: '.$output);
		
				$time2 = microtime_float();
				$output = readCoda($id);
				writeDurationProgram($time2, $id, 'readCoda');
				//echo "<br>Risultato:".$output.'<br/>';
				//showArray($output);
				//error_log('OUTPUT READ: '.$output);
				//$output = '*TIMEOUT';
		
				$dati = getDatiOutput($output);
		
				if(!$dati['TIMEOUT']) {
					if(in_array($dati['OPERAZIONE'], array('ENDPGM', 'ENDJOB'))) {
						//echo 'Faccio l\'end job<br>';
						$_SESSION['GIWI400_ENDJOB'] = $dati['OPERAZIONE'];
						unset($_SESSION['GIWI400_FILE']);
						unset($_SESSION['GIWI400_ID']);
						
						$return['success'] = true;
					}else {
						$file = $dati['FILE_PATH'];
							
						//$firephp->fb('File: '.$file);
						//echo "_".$file."_<br/>";
							
						$string_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH);
		
						if($string_xml) {
							$time2 = microtime_float();
							$giwi400Cmd = new giwi400Cmd($string_xml);
							$inWindow = $giwi400Cmd->checkTargetWindow();
							
							$errorMessages = $messageContext->getMessages();
							if($errorMessages) {
								$return['error'] = true;
							}
							
							if(!$return['error']) {
								$_SESSION['GIWI400_FILE'] = $file;
								
								$current_form = $giwi400Cmd->getCurrentForm();
								writeDurationProgram($time2, $id, 'controllo se è in window');
								
								$return['libreria'] = $giwi400Cmd->getCurrentLibreria();
								$return['file'] = $giwi400Cmd->getCurrentFile();
								$return['form'] = $current_form;
								
								//setcookie("albertoProva", 'ok'); //Un anno
								
								//$plus_key = $return['libreria'].'_'.$return['file']."_".$return['form'];
								//wi400ExistWindowSizeCookie($azione, 'DEFAULT', $plus_key);
								
								//showArray($_COOKIE);
								
								if($inWindow) {
									if(isset($_REQUEST['GIWI400_WINDOW']) && $_SESSION['GIWI400_CURRENT_FORM'] == $current_form) {
										//Non apro una nuova window (sono nello stesso form)
									}else {
										$position = array_search($current_form, $_SESSION['GIWI400_OPEN_FORM']);
										if($position !== false) { //trovato
											//C'è già una finestra aperta di questo tipo. Chiudo quella corrente e faccio un refresh di quella sotto
											$return['target'] = 'close_refresh';
											
											//rimuovo i form aperti prima di questo
											$_SESSION['GIWI400_OPEN_FORM'] = array_slice($_SESSION['GIWI400_OPEN_FORM'], 0, $position+1);
										}else { //non trovato
											$return['target'] = 'window';
											$_SESSION['GIWI400_OPEN_FORM'][] = $current_form;
										}
									}
								}else {
									if(isset($_REQUEST['GIWI400_WINDOW'])) {
										$return['target'] = 'close';
									}
								}
								$return['success'] = true;
							}else {
								//showArray($errorMessages);
								$return['target'] = 'Maschera non presente';
							}
						}else {
							error_log('Errore contenuto file di ritorno ajaxxx');
						}
					}
				}else {
					//gestione timeout
					$return['error'] = true;
					$return['target'] = 'timeout';
				}
				//$actionContext->gotoAction($azione, 'DEFAULT', '', true);
			}else {
				echo 'Errore scrittura file<br/>';
			}
		}
		
		echo "REPLY:";
		
		echo json_encode($return);
		
		echo ":END-REPLY";
		
		writeDurationProgram($time_start, $id, 'total php', true);
		
		die();
	}
	
	if($form == 'INIT_JOB') {
		
		unset($_SESSION['GIWI400_FILE']);
		$_SESSION['GIWI400_CURRENT_FORM'] = '';
		
		list($id, $id_file) = getGiwi400Id();
		
		$_SESSION['GIWI400_ID'] = $id;
		$_SESSION['GIWI400_FILE'] = $id_file;
		
		//echo $id_file."___<br>";
		
		/*echo $id."___id<br/>";
		echo $_SESSION['GIWI400_ID']."___session<br/>";
		
		die("alberto");*/
		
		if($id) {
			
			echo "chiamo il readCoda con l'$id<br/>";
			
			//READ 
			/*$sql =  "call GIWI400/ZDT_DQUW(?,?,?,?,?,?)";
			$dq_lib="GIWI400";
			$dq_name="UIWI400RTC";
			$dq_key="C".$id; //S => per inviare all'as400; C => per ricevere da AS
			echo $dq_key."_<br>";
			$dq_data="PRIMA USER-SPACE DI TEST";
			$dq_oper="R"; //'R' per leggere -- 'W' per scrittura
			$output = "";
			$stmt = $db->prepareStatement($sql, 0, False);
			$db->bind_param($stmt, 1, "dq_lib", DB2_PARAM_IN );
			$db->bind_param($stmt, 2, "dq_name", DB2_PARAM_IN );
			$db->bind_param($stmt, 3, "dq_key", DB2_PARAM_IN );
			$db->bind_param($stmt, 4, "dq_data", DB2_PARAM_IN );
			$db->bind_param($stmt, 5, "dq_oper", DB2_PARAM_IN );
			$db->bind_param($stmt, 6, "output", DB2_PARAM_OUT);
				
			$result = db2_execute($stmt);
			//$output = readCoda($id);
			echo "<br>Risultato:".$output.'<br/>';*/
			$output = readCoda($id);
			
			//error_log('OUTPUT INIT READ: '.$output);
			
			//TODO timeout
			//getMicroTimeStep("<br>FINE LETTURA DTAQ");
		}else {
			error_log("Giwi init job: id nullo");
		}
		
		
		
		$actionContext->gotoAction($azione, 'DEFAULT&FROM_INIT=si', '', true);
	}else if($form == 'END_JOB') {
		
		
		unset($_SESSION['GIWI400_ID']);
		unset($_SESSION['GIWI400_FILE']);
		//$output = writeCoda('', $id, 'CLOSE_JOB');
		$progressivo = '';
		$operazione  = 'CLOSE_JOB';
		/*$sql =  "call GIWI400/ZDT_DQUW(?,?,?,?,?,?)";
		$dq_lib="GIWI400";
		$dq_name="UIWI400RTC";
		$dq_key = "S".$id; //S => per inviare all'as400; C => per ricevere da AS
		//$dq_data = str_pad($progressivo, 30, ' ', STR_PAD_LEFT).str_pad($id, 10, ' ', STR_PAD_LEFT).str_pad($operazione, 10, ' ', STR_PAD_LEFT);
		$dq_data = str_pad($progressivo, 30, ' ', STR_PAD_RIGHT).str_pad($id, 10, ' ', STR_PAD_RIGHT).str_pad($operazione, 10, ' ', STR_PAD_RIGHT);
		showArray($dq_data);
		$dq_oper="W"; //'R' per leggere -- 'W' per scrittura
		$output="";
		$stmt = $db->prepareStatement($sql, 0, False);
		$db->bind_param($stmt, 1, "dq_lib", DB2_PARAM_IN );
		$db->bind_param($stmt, 2, "dq_name", DB2_PARAM_IN );
		$db->bind_param($stmt, 3, "dq_key", DB2_PARAM_IN );
		$db->bind_param($stmt, 4, "dq_data", DB2_PARAM_IN );
		$db->bind_param($stmt, 5, "dq_oper", DB2_PARAM_IN );
		$db->bind_param($stmt, 6, "output", DB2_PARAM_INOUT);
			
		$result = db2_execute($stmt);
		
		echo "<br>Risultato:".$output.'<br/>';*/
		
		$output = writeCoda($progressivo, $id, $operazione);
		//error_log('OUTPUT END_JOB WRITE: '.$output);
		
		$actionContext->gotoAction($azione, 'DEFAULT', '', true);
	}else if($form == 'START_PROGRAM') {
		
		$nome_programma = wi400Detail::getDetailValue("CONSOLE_GIWI400_NOME_PROGRAM", 'NOME_PROGRAM');
		
		$_SESSION['GIWI400_CURRENT_FORM'] = '';
		//$_SESSION['GIWI400_NOME_PRG'] = $nome_programma;
		
		echo $id."_ID<BR>";
		
		//$output = writeCoda('', $id, 'RUNPGM');
		$progressivo = '';
		$operazione  = 'RUNPGM';
		
		$output = writeCoda($progressivo, $id, $operazione, $nome_programma);
		//error_log('OUTPUT START PROGRAM WRITE: '.$output);
		
		//LETTURA CODA
		$output = readCoda($id);
		
		//error_log('READ_OUTPUT: '.$output);
		
		$dati = getDatiOutput($output);
		
		$file = $dati['FILE_PATH'];
		
		//echo "_".$file."_<br/>";
		$_SESSION['GIWI400_FILE'] = $file;
		
		$actionContext->gotoAction($azione, 'DEFAULT', '', true);
	}else if($form == 'WRITE_FILE') {
		if(isset($_SESSION['GIWI400_FILE']) && $_SESSION['GIWI400_FILE']) {
			$string_xml = file_get_contents($_SESSION['GIWI400_FILE'], FILE_USE_INCLUDE_PATH);
			
			
			$giwi400Cmd = new giwi400Cmd($string_xml);
			$giwi400Cmd->setDatiDetailAndFile();
			$rs = $giwi400Cmd->createCmdFileXml($_SESSION['GIWI400_FILE']);
			
			if($rs) {
				echo 'OK scrittura file<br/>';
				
				echo $id."_ID<BR>";
				
				$_SESSION['OLD_GIWI400_GILE'] = $_SESSION['GIWI400_FILE'];
				
				//$output = writeCoda('', $id, 'EXFMT');
				$progressivo = '';
				$operazione  = 'EXFMT';
				/*$sql =  "call GIWI400/ZDT_DQUW(?,?,?,?,?,?)";
				$dq_lib="GIWI400";
				$dq_name="UIWI400RTC";
				$dq_key = "S".$id; //S => per inviare all'as400; C => per ricevere da AS
				//$dq_data = str_pad($progressivo, 30, ' ', STR_PAD_LEFT).str_pad($id, 10, ' ', STR_PAD_LEFT).str_pad($operazione, 10, ' ', STR_PAD_LEFT);
				$dq_data = str_pad($progressivo, 30, ' ', STR_PAD_RIGHT).str_pad($id, 10, ' ', STR_PAD_RIGHT).str_pad($operazione, 10, ' ', STR_PAD_RIGHT);
				//showArray($dq_data);
				$dq_oper="W"; //'R' per leggere -- 'W' per scrittura
				$output="";
				$stmt = $db->prepareStatement($sql, 0, False);
				db2_bind_param($stmt, 1, "dq_lib", DB2_PARAM_IN );
				db2_bind_param($stmt, 2, "dq_name", DB2_PARAM_IN );
				db2_bind_param($stmt, 3, "dq_key", DB2_PARAM_IN );
				db2_bind_param($stmt, 4, "dq_data", DB2_PARAM_IN );
				db2_bind_param($stmt, 5, "dq_oper", DB2_PARAM_IN );
				db2_bind_param($stmt, 6, "output", DB2_PARAM_OUT);
					
				$result = db2_execute($stmt);
				echo "<br>Risultato:".$output.'<br/>'; */
				$output = writeCoda($progressivo, $id, $operazione);
				
				//error_log('OUTPUT WRITE: '.$output);
				
				//LETTURA CODA
				/*$sql =  "call GIWI400/ZDT_DQUW(?,?,?,?,?,?)";
				$dq_lib="GIWI400";
				$dq_name="UIWI400RTC";
				$dq_key="C".$id; //S => per inviare all'as400; C => per ricevere da AS
				//echo $dq_key."_<br>";
				$dq_data="PRIMA USER-SPACE DI TEST";
				$dq_oper="R"; //'R' per leggere -- 'W' per scrittura
				$output = "";
				$stmt = $db->prepareStatement($sql, 0, False);
				$db->bind_param($stmt, 1, "dq_lib", DB2_PARAM_IN );
				$db->bind_param($stmt, 2, "dq_name", DB2_PARAM_IN );
				$db->bind_param($stmt, 3, "dq_key", DB2_PARAM_IN );
				$db->bind_param($stmt, 4, "dq_data", DB2_PARAM_IN );
				$db->bind_param($stmt, 5, "dq_oper", DB2_PARAM_IN );
				$db->bind_param($stmt, 6, "output", DB2_PARAM_OUT);
					
				$result = db2_execute($stmt);*/
				$output = readCoda($id);
				//echo "<br>Risultato:".$output.'<br/>';
				//showArray($output);
				//error_log('OUTPUT READ: '.$output);
				
				$dati = getDatiOutput($output);
				
				//showArray($dati);
				
				if(in_array($dati['OPERAZIONE'], array('ENDPGM', 'ENDJOB'))) {
					echo 'Faccio l\'end job<br>';
					$_SESSION['GIWI400_ENDJOB'] = $dati['OPERAZIONE'];
					unset($_SESSION['GIWI400_FILE']);
					unset($_SESSION['GIWI400_ID']);
				}else {
					$file = $dati['FILE_PATH'];
					
					echo "_".$file."_<br/>";
					
					$_SESSION['GIWI400_FILE'] = $file;
					
					if(1==2) {
						$string_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH);
						
						if($string_xml) {
							$giwi400Cmd = new giwi400Cmd($string_xml);
							$target = $giwi400Cmd->getTarget();
							
							if($target == 'WINDOW') {
								
							}
						}else {
							error_log('Errore contenuto file di ritorno');
						}
					}
					
				}
				
				
				$actionContext->gotoAction($azione, 'DEFAULT', '', true);
			}else {
				echo 'Errore scrittura file<br/>';
			}
		}
	}
	
	if($form == 'DEFAULT') {
		//$_SESSION['GIWI400_FILE'] = '/www/giwi400/session/550734_CMD.xml';
			
		if(isset($_SESSION['GIWI400_FILE']) && $_SESSION['GIWI400_FILE']) {
			
			
			if(file_exists($_SESSION['GIWI400_FILE'])) {
				$string_xml = file_get_contents($_SESSION['GIWI400_FILE'], FILE_USE_INCLUDE_PATH);
				
				if($string_xml) {
					$giwi400Cmd = new giwi400Cmd($string_xml);
					$giwi400Cmd->checkErrori();
				}else {
					$messageContext->addMessage('ERROR', 'Xml vuoto');
				}
			
			}else {
				if(!isset($_REQUEST['FROM_INIT']))
					$messageContext->addMessage('ERROR', 'File '.$_SESSION['GIWI400_FILE'].' non esistente.');
			}
			
		}
		
		if(isset($_REQUEST['SHOW_TESTATA'])) {
			if($_REQUEST['SHOW_TESTATA']) {
				$_SESSION['GIWI400_SHOW_TESTATA'] = true;
			}else {
				$_SESSION['GIWI400_SHOW_TESTATA'] = false;
			}
		}
		
		if(!isset($_REQUEST['GIWI400_WINDOW'])) {
			$_SESSION['GIWI400_OPEN_FORM'] = array();
		}
	}else if($form == 'IN_WINDOW') {
		
	}else if(in_array($form, array('SYSTEM_BUTTON', 'INFO_MASCHERA'))) {
		if($form == 'SYSTEM_BUTTON') {
			$actionContext->setLabel("Bottoni di sistema");
		}else {
			$actionContext->setLabel("Info maschera");
		}
		if(isset($_SESSION['GIWI400_FILE']) && $_SESSION['GIWI400_FILE']) {
				
				
			if(file_exists($_SESSION['GIWI400_FILE'])) {
				$string_xml = file_get_contents($_SESSION['GIWI400_FILE'], FILE_USE_INCLUDE_PATH);
				if($string_xml) {
					$giwi400Cmd = new giwi400Cmd($string_xml);
					$files = $giwi400Cmd->getFiles();
					
					$string_xml = file_get_contents(end($files), FILE_USE_INCLUDE_PATH);
					if(!$string_xml) {
						echo "xml file giwi400 vuoto";
					}
				}else {
					$messageContext->addMessage('ERROR', 'Xml giwi400Cmd vuoto');
				}
			}else {
				echo "non esiste il file";
			}
		}else {
			echo "non esiste la variabile giwi400_file in sessione";
		}
	}
	
	if($form == 'DEFAULT2') {
		$string_xml = file_get_contents($file_path, FILE_USE_INCLUDE_PATH);
	}else if($form == 'DEFAULT3') {

		$string_xml = file_get_contents($file_path, FILE_USE_INCLUDE_PATH);
		
		if(isset($string_xml) && $string_xml) {
			$giwi400Cmd = new giwi400Cmd($string_xml);
			$giwi400Cmd->checkErrori();
		}else {
			echo "non c'è il file<br>";
		}
		
	}else if($form == "MAPPA") {
		
		$indirizzo = base64_decode($_REQUEST['INDIRIZZO']);
		$actionContext->setLabel($indirizzo);
	} else if($actionContext->getForm()=="CLEAR_RELOAD") {
		$string_xml = file_get_contents($_SESSION['GIWI400_FILE'], FILE_USE_INCLUDE_PATH);
		if($string_xml) {
			$giwi400Cmd = new giwi400Cmd($string_xml);
			$files = $giwi400Cmd->getFiles();
			$string_xml = file_get_contents(end($files), FILE_USE_INCLUDE_PATH);
			$useClass = getUseClass();
			$giwi400 = new $useClass($string_xml, 'INFO_MASCHERA', '');
			$datiTestata = $giwi400->getDatiTestata();
		}else {
			$messageContext->addMessage('ERROR', 'Xml giwi400Cmd vuoto');
		}
		//showArray($datiTestata);
		$file = $datiTestata['I_GIWI_FIL'];
		$libreria = $datiTestata['I_GIWI_FLI'];
		$id="GIWI_DISPLAY_".$file."_".$libreria;
		$path_file = wi400File::getCommonFile('giwi400', $id.'.txt');
		unlink($path_file);
		$path_file = wi400File::getSessionFile(session_id(), $id.'.generic');
		unlink($path_file);
		// Chiudi Finestra e ricarica il form sotto
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
	}else if($form == 'CUSTOM_TOOL_FIELD') {
		
		if(isset($_SESSION['GIWI400_CUSTOM_TOOL_FIELD'])) {
			unset($_SESSION['GIWI400_CUSTOM_TOOL_FIELD']);
		}else {
			$_SESSION['GIWI400_CUSTOM_TOOL_FIELD'] = true;
		}
		
		$actionContext->gotoAction('CLOSE', 'CLOSE_WINDOW', '', true);
	}/*else if($form == 'AZIONI_DI_LISTA') {
		$num_azione = $_REQUEST['NUM_AZIONE'];
		
		$key = getListKeyArray($_REQUEST['IDLIST']);
		echo "key";
		showArray($key);
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['IDLIST']);
		
		$select = $wi400List->getField();
		$from = $wi400List->getFrom();
		$where = $wi400List->getWhere();
		
		showArray($select);
		showArray($from);
		showArray($where);
		
		
		$row = $wi400List->getCurrentRow();
		echo "row";
		showArray($row);
		
		//showArray($_REQUEST);
	}*/
	
