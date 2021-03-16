<?php

	require_once "batch_commons.php";
	
	$azione = $actionContext->getAction();
	
	$path = $settings['data_path']."BATCH/ID/";
	
	if(!in_array($actionContext->getForm(),array("DEFAULT","REMOVE","FILES_LIST"))) {
		$keyArray = array();
		$keyArray = getListKeyArray("BATCHJOB_LIST");
		
		$id = $keyArray['ID'];
	}
	
	if($actionContext->getForm()=="REMOVE") {
		//$wi400List =  $_SESSION["BATCHJOB_LIST"];
		$wi400List = getList('BATCHJOB_LIST');
		$rowsSelectionArray = $wi400List->getSelectionArray();
//		echo "SEL_ARRAY: "; print_r($rowsSelectionArray); echo "<br>";	
		
		foreach($rowsSelectionArray as $key => $val) {
			$keys = explode("|", $key);
			$id = $keys[0];
//			echo "ID: $id<br>";

			$file_dir = $path.$id;
//			echo "FILE: $file_dir<br>";
			
			// Eliminazione dei files
			if(file_exists($file_dir)) {
				$dir_handle = opendir($file_dir);
				
				while(($file = readdir($dir_handle))!==false) {
					if($file!="." && $file!="..") {
						$file_path = $file_dir."/".$file;
						
						if(file_exists($file_path)) {
							unlink($file_path);
						}
					}
				}
//				echo "REMOVE<br>";
				rmdir($file_dir);
			}
			
			if(!isset($stmtdelete))
				$stmtdelete = $db->prepare("DELETE", "FBATCHJB", array("ID"), null);
			$deleteRes = $db->execute($stmtdelete, array($id));
			
			if($deleteRes)
				$messageContext->addMessage("SUCCESS", "Eliminazione del file $id avvenuta con successo");
			else
				$messageContext->addMessage("ERROR", "Errore durante l'eliminazione del file $id");
		}

		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="REMOVE_FILE") {
		//$wi400List =  $_SESSION["BATCH_FILE_LST"];
		$wi400List = getList('BATCH_FILE_LIST');
		$rowsSelectionArray = $wi400List->getSelectionArray();
//		echo "SEL_ARRAY: "; print_r($rowsSelectionArray); echo "<br>";
		
		foreach($rowsSelectionArray as $file => $v) {
			$file_path = $path.$id."/".$file;
			
			if(file_exists($file_path)) {
				unlink($file_path);
			}	
		}
		
		$messageContext->addMessage("SUCCESS", "Eliminazione dei file avvenuta con successo");
		
//		$actionContext->setForm("FILES_LIST");
		$actionContext->onSuccess($azione, "FILES_LIST");
		$actionContext->onError($azione, "FILES_LIST");
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		subfileDelete("BATCHJOB");
		
		$sql = "select * from FBATCHJB";
		
		$where = array();
		
		$solo_user = false;
		if($azione=="BATCHJOB_USER") {
			$solo_user = true;
		}
		else {	
			$solo_user_obj = wi400Detail::getDetailField('batchDetail',"SOLO_UTENTE");
			if($solo_user_obj!="") {
				$solo_user = $solo_user_obj->getChecked();
			}
		}

		if ($solo_user===true){
			$where[] = "UTENTE='".$_SESSION['user']."'";
		}
		
		$solo_session_obj = wi400Detail::getDetailField('batchDetail',"SOLO_SESSIONE");
		$solo_session = false;
		if($solo_session_obj!="") {
			$solo_session = $solo_session_obj->getChecked();
		}
		
		if ($solo_session===true){
			$where[] = "SESSIONE='".session_id()."'";
		}
		
		if(!empty($where))
			$sql .= " where ".implode(" and ", $where);
			
//		echo "SQL: $sql<br>";
		
		$subfile = new wi400Subfile($db, "BATCHJOB", $settings['db_temp']);
		$subfile->setConfigFileName("BATCHJOB");
		$subfile->setModulo('batch');
		$subfile->addParameter("PATH", $path);
		$subfile->setSql($sql);
		
		// Azione corrente
//	 	$actionContext->setLabel("Lista dei file caricati");
	 	
	 	$history->addCurrent();
	}
/*
	else if($actionContext->getForm()=="FILE_PRV") {
//		echo "DETAIL KEY: ".$_REQUEST["DETAIL_KEY"]."<br>";
		
		$atc_param = explode('|', $_REQUEST["DETAIL_KEY"]);
		$file = trim($atc_param[0]);
		$id = trim($atc_param[1]);
		
//		echo "FILE: $file<br>";
		
		$file_path = $path.$id."/".$file;
		
//		echo "FILE_PATH: $file_path<br>";
	}
	
	if($actionContext->getForm()=="FILES_LIST") {
		subfileDelete("BATCH_FILES_LST");
		
		if(isset($_GET['DETAIL_KEY'])) {
			$keyArray = explode("|",$_GET['DETAIL_KEY']);
			
			$id = $keyArray[0];
			$stato = $keyArray[1];
			$time_sub = $keyArray[2];
			$time_start = $keyArray[3];
			$time_complete = $keyArray[4];
			$stato_batch = $keyArray[5];
		}
		else {
			$keyArray = getListKeyArray('BATCHJOB_LIST');
			
			$id = $keyArray['ID'];
			$stato = $keyArray['STATO'];
			$time_sub = $keyArray['TIMESUB'];
			$time_start = $keyArray['TIMESTART'];
			$time_complete = $keyArray['TIMECOMPLETE'];
			$stato_batch = $keyArray['STATO_BATCH'];
		}
		
		$subfile = new wi400Subfile($db, "BATCH_FILES_LST", $settings['db_temp'], 20);
		    
	    $array = array();
	    $array['ID']=$db->singleColumns("1", "12");
	    $array['FILE']=$db->singleColumns("1", "100");
	    $array['SIZE_B']=$db->singleColumns("3", "9", 0);
	    $array['SIZE_F']=$db->singleColumns("3", "9", 2);
	    $array['TYPE_F']=$db->singleColumns("1", "2");
		$subfile->inz($array);
		
		$file_dir = $path.$id;
		
//		echo "<b>DIR: $file_dir</b><br>";
		
		if(file_exists($file_dir)) {
			$dir_handle = opendir($file_dir);

			$log_file = false;
			
			while(($file = readdir($dir_handle))!==false) {
				if($file!="." && $file!="..") {
					$file_parts = explode(".",$file);
					
					// creazione riga
					$file_path = $file_dir."/".$file;					
//					echo "FILE: $file_path<br>";

					// Dimensione in Bytes
					$size_b = filesize($file_path);
					
					// Dimensione formattata
//					$size_f = File_Size($file_path, "MB");
//					$size_f = format_size($size_b, "MB");

					$size = format_size($size_b);
					$size_parts = explode(" ", $size);
					$size_f = $size_parts[0];
					$type_f = $size_parts[1];
					
					$dati = array(
						$id,
						$file,
						$size_b,
						$size_f,
						$type_f
					);
					
					if($file_parts[0]!=$id) {
						$subfile->write($dati);
					}
					else {
						if($azione=="BATCHJOB")
							$subfile->write($dati);
						$log_file = true;
					}
				}
			}
			$subfile->finalize();
			closedir($dir_handle);
		}
		
		// Azione corrente
	 	$actionContext->setLabel(_t('Elenco dei files')." ($id)");
	 	
	 	$history->addCurrent();
	}
*/	
?>