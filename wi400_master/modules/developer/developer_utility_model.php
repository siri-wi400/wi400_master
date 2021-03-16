<?php
	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();

	require_once "developer_auth.php";
	require_once "developer_functions.php";
	// Controllo se abilitato XMLSERVICE - AS400
	$showxml=False;
	if (isset($settings["xmlservice"]) && $settings["xmlservice"] == True){
	$showxml=True;
	}
	$data_creazione = wi400Detail::getDetailValue("DELETE_FILES_TMP_SEL","DATA_CREAZIONE");
	// Controllo il tipo di opcache attiva
	$wincache=False;
	if (extension_loaded( 'wincache' ) )
	{
		$wincache=True;
	}
	$opcache=False;
	if (extension_loaded( 'Zend OPcache' ) )
	{
		$opcache=True;
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$history->addCurrent();
	} // END DEFAULT
	
	if($actionContext->getForm()=="DELETE") {
		// Pulizia della directory serialize
		if(isset($_POST['DIR_SERIALIZE'])) {
			clean_dir_serialize();
			
			$messageContext->addMessage("SUCCESS", "Pulita la directory serialize");
		}
		
		// Pulizia delle sessioni (eccetto quella attuale)
		if(isset($_POST['SESSION'])) {
			$directory = getSessionPath();
			
			$dir_handle = opendir($directory);
			
			while(($file = readdir($dir_handle))!==false) {
				if(strncmp($file, "sess_", 5)==0 && $file!="sess_".session_id()) {
					$file_path = $directory.$file;
					unlink($file_path);
				}
			}
			
			closedir($dir_handle);

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
			
			$dir_handle = opendir($directory);
			
			while(($file = readdir($dir_handle))!==false) {
				$file_path = $directory.$file;
				unlink($file_path);
			}
			
			closedir($dir_handle);

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
		if(isset($_POST['WINCACHE'])) {
			wincache_ucache_clear();
				
			$messageContext->addMessage("SUCCESS", "Pulita cache WINCACHE");
		}
		if(isset($_POST['OPCACHE'])) {
			opcache_reset();
			$messageContext->addMessage("SUCCESS", "Pulita cache OPCACHE");
		}
		if(isset($_POST['WSDL'])) {
			$directory = "/tmp/";
				
			$dir_handle = opendir($directory);
				
			while(($file = readdir($dir_handle))!==false) {
				if(substr($file,0,4)=="wsdl") {
					$file_path = $directory.$file;
					unlink($file_path);
				}
			}
				
			closedir($dir_handle);
		
			$messageContext->addMessage("SUCCESS", "Pulita CACHE WSDL (Web Services)");
		}
		
		$actionContext->onSuccess($azione, "DEFAULT");
	   	$actionContext->onError($azione, "DEFAULT");
	} // END DELETE
	
	if($actionContext->getForm() == "MAPPING_BUTTON") {
		if(isset($_SESSION['BUTTON_MAPPA_DETAIL'])) {
			unset($_SESSION['BUTTON_MAPPA_DETAIL']);
		}else {
			$_SESSION['BUTTON_MAPPA_DETAIL'] = true;
		}
	
		
	}
?>