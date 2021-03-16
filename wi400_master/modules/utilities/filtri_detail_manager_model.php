<?php

	require_once 'filtri_detail_common.php';
	
	$azione = $actionContext->getAction();

//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
	
	$off = 1;
	if(in_array($actionContext->getForm(), array("DEFAULT", "DETAIL_LIST", "FILTRI_LIST", "DELETE_FILTRI_LIST"))) {
		$history->addCurrent();
		
		$off = 2;
	}
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	$last_action = "";
	$last_form = "";
	if(!empty($steps) && count($steps)>=$off) {
		$last_step = $steps[count($steps)-$off];
//		echo "LAST STEP: $last_step<br>";
			
		$last_action_obj = $history->getAction($last_step);
		if (isset($last_action_obj)) {
			$last_action = $last_action_obj->getAction();
			$last_form = $last_action_obj->getForm();
		}
	}
//	echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";
	
	$user_src = wi400Detail::getDetailValue($azione."_SRC",'USER_SRC');
	
	if(is_null($user_src)) {
		$user_src = $_SESSION['user'];
	}
	
	$file_type = $azione;
	
	$log_files_paths = array();
	if($user_src!="") {
		$log_files_paths = array(
			$file_type => $settings['data_path'].$user_src."/detail/"
		);
	}
	else {
		// Recupero dei file della directory
		$dir_handle = opendir($settings['data_path']);
		
		while(($file_name = readdir($dir_handle))!==false) {
			if(is_dir($settings['data_path'].$file_name)) {
				if($file_name!="." && $file_name!=".." && $file_name!="CVS") {
//					echo "FILE:$file_name<br>";
					$file_path = $settings['data_path'].$file_name."/detail/";
					if(file_exists($file_path)) {
						$log_files_paths[] = $file_path;
					}
				}
			}
		}
		
		closedir($dir_handle);
		
		$file_type = "ALL";
	}
//	echo "PATHS:<pre>"; print_r($log_files_paths); echo "</pre>";

	$new_filtri = array();
	$from_filtri_array = array();
	$to_filtri = array();
	$from_filtri = array();
	
//	if($actionContext->getForm()=="SEL_ALL") {
	if(in_array($actionContext->getForm(), array("SEL_ALL", "FILTRI_LIST", "DELETE_FILTRI_LIST"))) {
		if(isset($_REQUEST["FILTRI_DRAG_TO_FILTRI"]))
			$new_filtri = explode(",", $_REQUEST["FILTRI_DRAG_TO_FILTRI"]);
	
		if(!empty($new_filtri) && $new_filtri[0] == "")
			$new_filtri = array();
	
		if(isset($_REQUEST["FILTRI_DRAG_FROM_FILTRI"]))
			$from_filtri_array = explode(",", $_REQUEST["FILTRI_DRAG_FROM_FILTRI"]);
	
		if(!empty($from_filtri_array) && $from_filtri_array[0] == "")
			$from_filtri_array = array();
	
		if($actionContext->getForm()=="SEL_ALL") {
			if(!empty($from_filtri_array)) {
				$new_filtri = array_merge($new_filtri, $from_filtri_array);
				$from_filtri_array = array();
			}
	
//			$actionContext->setForm("FILTRI_LIST");
			$actionContext->setForm($last_form);
//die("FORM: ".$actionContext->getForm());
		}
		
//		echo "FROM FILTRI:<pre>"; print_r($from_filtri_array); echo "</pre>";
//		echo "NEW FILTRI:<pre>"; print_r($new_filtri); echo "</pre>";
	}
//	echo "FORM: ".$actionContext->getForm()."<br>";

	if(in_array($actionContext->getForm(), array("FILTRI_LIST", "SAVE", "DELETE_FILTRI_LIST", "DELETE"))) {
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_DETAIL_LIST");

		$file = $keyArray['FILE_NAME'];
		$user_src = $keyArray['UTENTE'];
		
		$to_user_array = array();
		if(wi400Detail::getDetailValue($azione."_FILTRI_LIST_DET",'TO_USER')!="")
			$to_user_array = wi400Detail::getDetailValue($azione."_FILTRI_LIST_DET",'TO_USER');
/*		
		$overwrite_obj = wi400Detail::getDetailField($azione."_FILTRI_LIST_DET",'OVERWRITE');
		$check_overwrite = false;
		$overwrite = "N";
		if($overwrite_obj!="") {
			$check_overwrite = $overwrite_obj->getChecked();
			if($check_overwrite!=false)
				$overwrite = "S";
		}
*/
		$check_overwrite = get_switch_bool_value($azione."_FILTRI_LIST_DET",'OVERWRITE');
		$overwrite = get_switch_value($azione."_FILTRI_LIST_DET",'OVERWRITE');
//		echo "OVERWRITE: $check_overwrite - OVERWRITE: $overwrite<br>";
/*		
		$to_all_obj = wi400Detail::getDetailField($azione."_FILTRI_LIST_DET",'TO_ALL');
		$check_to_all = false;
		$to_all = "N";
		if($to_all_obj!="") {
			$check_to_all = $to_all_obj->getChecked();
			if($check_to_all!=false)
				$to_all = "S";
		}
*/		
		$check_to_all = get_switch_bool_value($azione."_FILTRI_LIST_DET",'TO_ALL');
		$to_all = get_switch_value($azione."_FILTRI_LIST_DET",'TO_ALL');
//		echo "TO ALL: $check_to_all - TO ALL: $to_all<br>";

		$check_no_current = get_switch_bool_value($azione."_FILTRI_LIST_DET",'NO_CURRENT', true);
		$no_current = get_switch_value($azione."_FILTRI_LIST_DET",'NO_CURRENT', true);
//		echo "CHECK NO CURRENT: $check_no_current - NO CURRENT: $no_current<br>";

		$default_detail = wi400Detail::getDetailValue($azione."_FILTRI_LIST_DET",'DEFAULT_DETAIL');
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
		
		wi400Detail::cleanSession($azione."_FILTRI_LIST_DET");
	}
	else if($actionContext->getForm()=="DETAIL_LIST") {
		$actionContext->setLabel("Details");
		
		wi400Detail::cleanSession($azione."_FILTRI_LIST_DET");
		
		subfileDelete($azione."_LIST");
		
		$subfile = new wi400Subfile($db, $azione."_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("LOG_FILES_LIST");
		$subfile->setModulo("analisi");
		
		$subfile->addParameter("FILE_TYPE", $file_type);
		$subfile->addParameter("LOG_FILES_PATHS", $log_files_paths);
		
		$subfile->addParameter("AZIONE", $azione);
		$subfile->addParameter("USER_SRC", $user_src);
		$subfile->addParameter("SQL_USER", $sql_user);
		
		if($azione=="QUERY_MANAGER") {
//			$limit_files = array("QUERY_TOOL_SRC.dtl", "QUERY_TOOL_LIBERO_SRC.dtl");
			$limit_files = array("QUERY_TOOL_SRC.dtl");
			
			$subfile->addParameter("LIMIT_FILES", $limit_files);
		}
		
		$subfile->setSql("*AUTOBODY");
	}
	else if(in_array($actionContext->getForm(), array("FILTRI_LIST", "DELETE_FILTRI_LIST"))) {
		if($actionContext->getForm()=="FILTRI_LIST")
			$actionContext->setLabel("Distribuzione Filtri");
		else if($actionContext->getForm()=="DELETE_FILTRI_LIST")
			$actionContext->setLabel("Rimozione Filtri");
		
		$default_detail_file = "";
		
		// Applicazioni associate all'utente
		if(empty($new_filtri)) {
			if(file_exists($file)) {
				$handle = fopen($file, "r");
				$from_contents = fread($handle, filesize($file));
				fclose($handle);
				$from_filtri_objs = unserialize($from_contents);
				
				if(!empty($from_filtri_objs)) {
					foreach($from_filtri_objs as $filtro => $obj) { 
						if($filtro=="DEFAULT_DETAIL") {
//							echo "DEFAULT_DETAIL:<pre>"; print_r($obj); echo "</pre>";
							
//							$from_filtri[$filtro] = "<font color='red'>FILTRO DI DEFAULT:</font> $obj";

//							if($default_detail=="")
//								$default_detail = $obj;
							
							$default_detail_file = $obj;
						}
						else {
							$from_filtri[$filtro] = $filtro;
						}
					}
				}
			}
		}
		else {
			if(!empty($new_filtri)) {
				foreach($new_filtri as $filtro) {
					$to_filtri[$filtro] = $filtro;
				}
			}
			
			if(!empty($from_filtri_array)) {
				foreach($from_filtri_array as $filtro) {
					$from_filtri[$filtro] = $filtro;
				}
			}
		}
		
//		echo "FROM FILTRI:<pre>"; print_r($from_filtri); echo "</pre>";
//		echo "TO FILTRI:<pre>"; print_r($to_filtri); echo "</pre>";
	}
	else if($actionContext->getForm()=="SAVE") {
		$new_filtri = explode(",", $_REQUEST["FILTRI_DRAG_TO_FILTRI"]);
//		echo "NEW FILTRI:<pre>"; print_r($new_filtri); echo "</pre>";
		
		if($new_filtri[0]=="")
			$new_filtri = array();
		
		$errors = false;
		if(!empty($new_filtri) || $default_detail!="") {
			// SALVATAGGIO DEI FILTRI
			
			// In caso di ridistribuzione a TUTTI gli utenti
			if(empty($to_user_array) && $to_all=="S") {
				$sql = "select $id_user_name from $id_user_file_lib/$id_user_file where $id_user_name<>'$user_src'";
//				echo "SQL: $sql<br>";
				$res = $db->query($sql, false, 0);
				while($row = $db->fetch_array($res)) {
					$to_user_array[] = $row[$id_user_name];
				}
			}
			
//			echo "TO USER ARRAY:<pre>"; print_r($to_user_array); echo "</pre>";			

			// FROM CONTENTS
			if(file_exists($file)) {
				$handle = fopen($file, "r");
				$from_contents = fread($handle, filesize($file));
				fclose($handle);
				$from_filtri_objs = unserialize($from_contents);
			}
//			echo "FROM FILTRI:<pre>"; print_r($from_filtri_objs); echo "</pre>";

			if($default_detail!="") {
				if(!array_key_exists($default_detail, $from_filtri_objs)) {
					$messageContext->addMessage("ERROR","Il Filtro di Default selezionato non esiste.", "DEFAULT_DETAIL", true);
					$errors = true;
				}
			}
			
			if(!empty($to_user_array) && $errors===false) {
				foreach($to_user_array as $to_user) {
					$to_file =  $settings['data_path'].$to_user."/detail/".basename($file);
//					echo "TO FILE: $to_file<br>";
			
					// TO CONTENTS
					$to_filtri_objs = array();
					if(file_exists($to_file)) {
						$handle = fopen($to_file, "r");
						$to_contents = fread($handle, filesize($to_file));
						fclose($handle);
						$to_filtri_objs = unserialize($to_contents);
					}
//					echo "TO FILTRI:<pre>"; print_r($to_filtri_objs); echo "</pre>";
			
					foreach($new_filtri as $filtro) {
						if(!array_key_exists($filtro, $to_filtri_objs) || $overwrite=="S") {
							$to_filtri_objs[$filtro] = $from_filtri_objs[$filtro];
						}
					}
//					echo "SAVE FILTRI:<pre>"; print_r($to_filtri_objs); echo "</pre>";

					// GESTIONE DEL FILTRO DI DEFAULT
					if($default_detail!="") {
						// Aggiungo il filtro di default, in caso non ci sia tra quelli dell'utente
						if(!array_key_exists($default_detail, $to_filtri_objs) || $overwrite=="S") {
							$to_filtri_objs[$default_detail] = $from_filtri_objs[$default_detail];
						}
						
						// Setto il Filtro di Default del Dettaglio
						$to_filtri_objs["DEFAULT_DETAIL"] = $default_detail;
					}

					if(!file_exists($to_file)) {
						wi400_mkdir(dirname($to_file), 0, true);
					}
			
					$handle = fopen($to_file, "w");
					
					if(flock($handle, LOCK_EX)) {
						$putfile = True;
					}
					else {
						$putfile = False;
						fclose($handle);
					}
					
					if($putfile) {
						$new_contents = serialize($to_filtri_objs);
						fputs($handle, $new_contents);
						flock($handle, LOCK_UN);
						fclose($handle);
					}
				}
			}
				
			if($errors===false) {
				$messageContext->addMessage("SUCCESS","Assegnazione dei filtri eseguita con successo.");
			}
			else {
				$messageContext->addMessage("ERROR","Errore durante l'assegnazione dei filtri.");
			}
		}
		else {
			$messageContext->addMessage("ERROR","Nessun filtro selezionato.");
		}
	
		$actionContext->onSuccess($azione, "FILTRI_LIST");
		$actionContext->onError($azione, "FILTRI_LIST", "", "", true);
	}
	else if($actionContext->getForm()=="DELETE") {
		$new_filtri = explode(",", $_REQUEST["FILTRI_DRAG_TO_FILTRI"]);
//		echo "NEW FILTRI:<pre>"; print_r($new_filtri); echo "</pre>";
	
		if($new_filtri[0]=="")
			$new_filtri = array();
	
		$errors = false;
		if(!empty($new_filtri) || $default_detail!="") {
			// SALVATAGGIO DEI FILTRI
				
			// In caso di rimozione da TUTTI gli utenti tranne quello in esame
			if(empty($to_user_array) && $to_all=="S") {
				$sql = "select $id_user_name from $id_user_file_lib/$id_user_file where $id_user_name<>'$user_src'";
//				echo "SQL: $sql<br>";
				$res = $db->query($sql, false, 0);
				while($row = $db->fetch_array($res)) {
					$to_user_array[] = $row[$id_user_name];
				}
				
				if(!empty($to_user_array) && $no_current!="S") {
					$to_user_array[] = $user_src;
				}
			}
			
			if(empty($to_user_array)) {
				$to_user_array[] = $user_src;
			}
			
//			echo "TO USER ARRAY:<pre>"; print_r($to_user_array); echo "</pre>";
	
			// FROM CONTENTS
			if(file_exists($file)) {
				$handle = fopen($file, "r");
				$from_contents = fread($handle, filesize($file));
				fclose($handle);
				$from_filtri_objs = unserialize($from_contents);
			}
//			echo "FROM FILTRI:<pre>"; print_r($from_filtri_objs); echo "</pre>";
	
			if($default_detail!="") {
				if(!array_key_exists($default_detail, $from_filtri_objs)) {
					$messageContext->addMessage("ERROR","Il Filtro di Default selezionato non esiste.", "DEFAULT_DETAIL", true);
					$errors = true;
				}
			}
				
			if(!empty($to_user_array) && $errors===false) {
				foreach($to_user_array as $to_user) {
					$to_file =  $settings['data_path'].$to_user."/detail/".basename($file);
//					echo "TO FILE: $to_file<br>";
						
					// TO CONTENTS
					$to_filtri_objs = array();
					if(file_exists($to_file)) {
						$handle = fopen($to_file, "r");
						$to_contents = fread($handle, filesize($to_file));
						fclose($handle);
						$to_filtri_objs = unserialize($to_contents);
					}
//					echo "TO FILTRI:<pre>"; print_r($to_filtri_objs); echo "</pre>";
						
					foreach($new_filtri as $filtro) {
						if(array_key_exists($filtro, $to_filtri_objs)) {
							unset($to_filtri_objs[$filtro]);
						}
					}
					
					// GESTIONE DEL FILTRO DI DEFAULT
					// Rimuovo il filtro di default, in caso sia tra quelli da rimuovere
					if(isset($to_filtri_objs["DEFAULT_DETAIL"])) {
						if(in_array($to_filtri_objs["DEFAULT_DETAIL"], $new_filtri)) {
							unset($to_filtri_objs["DEFAULT_DETAIL"]);
						}
					}
					
//					echo "SAVE FILTRI:<pre>"; print_r($to_filtri_objs); echo "</pre>";

					if(empty($to_filtri_objs)) {
						unlink($to_file);
					}
					else {
						if(!file_exists($to_file)) {
							wi400_mkdir(dirname($to_file), 0, true);
						}
							
						$handle = fopen($to_file, "w");
							
						if(flock($handle, LOCK_EX)) {
							$putfile = True;
						}
						else {
							$putfile = False;
							fclose($handle);
						}
							
						if($putfile) {
							$new_contents = serialize($to_filtri_objs);
							fputs($handle, $new_contents);
							flock($handle, LOCK_UN);
							fclose($handle);
						}
					}
				}
			}
	
			if($errors===false) {
				$messageContext->addMessage("SUCCESS","Rimozione dei filtri eseguita con successo.");
			}
			else {
				$messageContext->addMessage("ERROR","Errore durante la rimozione dei filtri.");
			}
		}
		else {
			$messageContext->addMessage("ERROR","Nessun filtro selezionato.");
		}
		
		if(empty($to_filtri_objs))
			$actionContext->onSuccess($azione, "DETAIL_LIST");
		else 
			$actionContext->onSuccess($azione, "DELETE_FILTRI_LIST");
		
		$actionContext->onError($azione, "DELETE_FILTRI_LIST", "", "", true);
	}