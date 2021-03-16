<?php

	$data_creazione = wi400Detail::getDetailValue("DELETE_FILES_TMP_SEL","DATA_CREAZIONE");

	if($actionContext->getForm()=="DEFAULT") {
		$history->addCurrent();
	} // END DEFAULT
	
	if($actionContext->getForm()=="DELETE") {
		// Pulizia della directory serialize
		if(isset($_POST['DIR_SERIALIZE'])) {
/*
			$directory = $settings['data_path']."COMMON/serialize/";
			
			$dir_handle = opendir($directory);
			
			while(($file = readdir($dir_handle))!==false) {
				if($file!="." && $file!="..") {
					$file_path = $directory.$file;
					unlink($file_path);
				}
			}
			
			closedir($dir_handle);

			$messageContext->addMessage("SUCCESS", "Pulita la directory serialize");
*/
			clean_dir_serialize();
			
			$messageContext->addMessage("SUCCESS", "Pulita la directory serialize");
		}
		
		// Pulizia delle sessioni (eccetto quella attuale)
		if(isset($_POST['SESSION'])) {
			$directory = $settings['sess_path'];
			if (is_dir($directory)) {
				$dir_handle = opendir($directory);
				
				while(($file = readdir($dir_handle))!==false) {
					if(strncmp($file, "WI400_", 6)==0 && $file!="WI400_".session_id().".txt") {
						$file_path = $directory.$file;
						unlink($file_path);
					}
				}
				
				closedir($dir_handle);
			}
			$messageContext->addMessage("SUCCESS", "Pulite le sessioni");
		}
		
		// Pulizia delle tabelle temporanee
		if(isset($_POST['TMP_TABLES'])) {
			$sql = "select * from QSYS2".$settings['db_separator']."SYSTABLES where TABLE_SCHEMA='".$settings['db_temp']."'";
			
			$result = $db->query($sql);
		
			while($row = $db->fetch_array($result)) {
				$sql_drop = "drop table ".$settings['db_temp'].$settings['db_separator'].$row['TABLE_NAME'];

				$result_drop = $db->query($sql_drop);
			}
			
			$messageContext->addMessage("SUCCESS", "Pulite le tabelle temporanee");
		}
		
		// Pulizia dei log SQL
		if(isset($_POST['LOG_SQL'])) {
			$directory = $settings['log_sql'];
			
			if (is_dir($directory)) {
			$dir_handle = opendir($directory);
			
			while(($file = readdir($dir_handle))!==false) {
				$file_path = $directory.$file;
				unlink($file_path);
			}
			
			closedir($dir_handle);
			}
			$messageContext->addMessage("SUCCESS", "Puliti i log SQL");
		}
		
		// Pulizia dei log dei webserver
		if(isset($_POST['WSLOG'])) {
			$directory = $settings['data_path']."wslog/";
			
			if (is_dir($directory)) {
				$dir_handle = opendir($directory);
				while(($file = readdir($dir_handle))!==false) {
					$file_path = $directory.$file;
					
					$file_data_creazione = date("Ymd", filectime($file_path));
					
					if($file_data_creazione<dateViewToModel($data_creazione)) {
						unlink($file_path);
					}
				}
			}

			closedir($dir_handle);

			$messageContext->addMessage("SUCCESS", "Puliti i log dei webserver");
		}
		
		$actionContext->onSuccess("DELETE_FILES_TMP", "DEFAULT");
	   	$actionContext->onError("DELETE_FILES_TMP", "DEFAULT");
	} // END DELETE

?>