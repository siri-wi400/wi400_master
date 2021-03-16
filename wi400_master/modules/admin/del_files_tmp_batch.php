<?php

	// Pulizia della directory serialize
	if($actionContext->getForm()=="DIR_SERIALIZE") {
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
		echo "Eseguita pulizia della directory serialize\r\n";
	}

	// Pulizia delle sessioni
	if($actionContext->getForm()=="SESSION") {
		$directory = $settings['sess_path'];
		
		$dir_handle = opendir($directory);
		
		while(($file = readdir($dir_handle))!==false) {
			if(strncmp($file, "WI400_", 6)==0) {
				$file_path = $directory.$file;
				unlink($file_path);
			}
		}
		
		closedir($dir_handle);

		$messageContext->addMessage("SUCCESS", "Pulite le sessioni");
		echo "Eseguita pulizia delle sessioni<br>";
	}
	
	// Pulizia delle tabelle temporanee
	if($actionContext->getForm()=="TMP_TABLES") {
		$sql = "select * from QSYS2".$settings['db_separator']."SYSTABLES where TABLE_SCHEMA='".$settings['db_temp']."'";
		
		$result = $db->query($sql);
	
		while($row = $db->fetch_array($result)) {
			$sql_drop = "drop table ".$settings['db_temp'].$settings['db_separator'].$row['TABLE_NAME'];

			$result_drop = $db->query($sql_drop);
		}
		
		$messageContext->addMessage("SUCCESS", "Pulite le tabelle temporanee");
		echo "Eseguita la pulizia delle tabelle temporanee<br>";
	}
	
	// Pulizia dei log SQL
	if($actionContext->getForm()=="LOG_SQL") {
		$directory = $settings['log_sql'];
			
		$dir_handle = opendir($directory);
		
		while(($file = readdir($dir_handle))!==false) {
			$file_path = $directory.$file;
			unlink($file_path);
		}
		
		closedir($dir_handle);

		$messageContext->addMessage("SUCCESS", "Puliti i log SQL");
		echo "Eseguita la pulizia dei log SQL<br>";
	}
	
	// Pulizia dei log dei webserver
	if($actionContext->getForm()=="WSLOG") {
		$directory = $settings['data_path']."wslog/";
			
		$dir_handle = opendir($directory);
		
		$data = mktime(0, 0, 0, date("m")  , date("d")-7, date("Y"));	
		$data_creazione = date("Ymd", $data);
		
		while(($file = readdir($dir_handle))!==false) {
			$file_path = $directory.$file;
			
			$file_data_creazione = date("Ymd", filectime($file_path));
			
			if($file_data_creazione<dateViewToModel($data_creazione)) {
				unlink($file_path);
			}
		}

		closedir($dir_handle);

		$messageContext->addMessage("SUCCESS", "Puliti i log dei webserver");
		echo "Eseguita la pulizia dei log dei webserver<br>";
	}
	
?>