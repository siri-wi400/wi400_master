<?php

	require_once $moduli_path.'/utilities/filtri_detail_common.php';

	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()=="DEFAULT") {
		$history->addCurrent();
	}
	
	$check_overwrite = get_switch_bool_value($azione."_DET",'OVERWRITE');
	$overwrite = get_switch_value($azione."_DET",'OVERWRITE');
//	echo "OVERWRITE: $check_overwrite - OVERWRITE: $overwrite<br>";

	$to_user_sel = array();
	if(wi400Detail::getDetailValue($azione."_DET",'TO_USER')!="")
		$to_user_sel = wi400Detail::getDetailValue($azione."_DET",'TO_USER');
//	echo "TO USER SEL:<pre>"; print_r($to_user_sel); echo "</pre>";

	if($actionContext->getForm()=="DEFAULT") {
		
	}
	else if($actionContext->getForm()=="MIGRA") {
		$to_user_array = array();
		if(empty($to_user_sel)) {
			$sql = "select $id_user_name from $id_user_file_lib/$id_user_file";
//			echo "SQL: $sql<br>";
			$res = $db->query($sql, false, 0);
		
			while($row = $db->fetch_array($res)) {
				$to_user_array[] = $row[$id_user_name];
			}
		}
		else {
			$to_user_array = $to_user_sel;
		}
//		echo "TO USER ARRAY:<pre>"; print_r($to_user_array); echo "</pre>";
		
		$errors = false;
		if(!empty($to_user_array)) {
			foreach($to_user_array as $to_user) {
				$file_path =  $settings['data_path'].$to_user."/detail/";
				
				$from_file = $file_path."QUERY_TOOL_LIBERO_SRC.dtl";
//				echo "FROM FILE: $from_file<br>";

				// TO CONTENTS
				$from_filtri_objs = array();
				if(file_exists($from_file)) {
					$handle = fopen($from_file, "r");
					$from_contents = fread($handle, filesize($from_file));
					fclose($handle);
					$from_filtri_objs = unserialize($from_contents);
				}
				else {
					continue;
				}
//				echo "FROM FILTRI:<pre>"; print_r($from_filtri_objs); echo "</pre>";

				if(empty($from_filtri_objs)) {
					continue;
				}
		
				$to_file = $file_path."QUERY_TOOL_SRC.dtl";
//				echo "TO FILE: $to_file<br>";

				// TO CONTENTS
				$to_filtri_objs = array();
				if(file_exists($to_file)) {
					$handle = fopen($to_file, "r");
					$to_contents = fread($handle, filesize($to_file));
					fclose($handle);
					$to_filtri_objs = unserialize($to_contents);
				}
//				echo "TO FILTRI:<pre>"; print_r($to_filtri_objs); echo "</pre>";

				foreach($from_filtri_objs as $filtro => $vals) {
					if(!array_key_exists($filtro, $to_filtri_objs) || $overwrite=="S") {
						$to_filtri_objs[$filtro] = $from_filtri_objs[$filtro];
					}
				}
//				echo "SAVE FILTRI:<pre>"; print_r($to_filtri_objs); echo "</pre>";

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
				
				// @todo ATTIVARE UNA VOLTA FINITI I TEST PER CANCELLARE IL FILE QUERY_TOOL_LIBERO_SRC.dtl
//				unlink($from_file);
			}
		}
		
		if($errors===false) {
			$messageContext->addMessage("SUCCESS","Migrazione dei filtri delle query eseguita con successo.");
		}
		else {
			$messageContext->addMessage("ERROR","Errore durante la migrazione dei filtri delle query.");
		}
		
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DEFAULT", "", "", true);
	}