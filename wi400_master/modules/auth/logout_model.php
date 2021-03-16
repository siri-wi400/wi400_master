<?php
	// WI400-MACRO:REGISTER EXIT POINT
	$wi400_trigger->registerExitPoint("LOGOUT","EXIT", "*WI400", "Punto uscita al logout utente", "user;session_id");
	// END-WI400-MACRO
	$pageDefaultDecoration = "clean_";
		
	// Eliminazione lock per la sessione corrente
	endLock("","",session_id());
	
	// Cancello tutte le tabelle temporanee create
	$db->destroyTable(session_id());
    $db->clearPHPTEMP(session_id());
	// Cancello i file wi400Session
	wi400Session::destroy();
	
	// Cancello file temporanei
	if (isset($settings['tmp_clean_logout']) && $settings['tmp_clean_logout']==False) {
		// Nulla ..
	} else {
		$tmpDir = $data_path.$_SESSION["user"]."/tmp/";
		$now   = time();
		if (file_exists($tmpDir)){
			$handler = opendir($tmpDir);
			while ($file = readdir($handler)) {
		        if (!is_dir($file)){
		        	if ($now - filemtime($tmpDir.$file) >= 60 * 60 * 24) { // 1 days
		        		unlink($tmpDir.$file);
		        	}
		        }
		    }
		    closedir($handler);
		}
	}
	// Pulizi POST AJAX
	$tmpDir = $data_path.$_SESSION["user"]."/ajax_post/";
	$now   = time();
	if (file_exists($tmpDir)){
		$handler = opendir($tmpDir);
		while ($file = readdir($handler)) {
			if (!is_dir($file)){
				if ($now - filemtime($tmpDir.$file) >= 60 * 60 * 24) { // 1 days
					unlink($tmpDir.$file);
				}
			}
		}
		closedir($handler);
	}
	// Pulizia UserCache se presente
	$cache_file = wi400File::getCommonFile("usercache", session_id());
	if(file_exists($cache_file)) {
		unlink($cache_file);
	}
	//Cancello file cache delle abilitazioni di sistema
	if(!isset($settings['check_field_enable_on_detail']) || $settings['check_field_enable_on_detail']===true) {
		$file_checkField = $data_path."COMMON/checkFieldEnabled/".$_SESSION['user']."_".session_id();
		
		if(file_exists($file_checkField)) {
			unlink($file_checkField);
		}
	}
	// Cancello connessioni
	if (isset($mycon)) {
		$mycon->disconnect();
	}
	if (isset($settings['xmlservice'])) {
		xmlservice_logout();
		/*$InputXML = '<?xml version="1.0"?>';
		$InputXML = "";
		$InternalKey = $INTERNALKEY;    
	  	$ControlKey="*immed";
	  	$OutputXML = '';
	  	$callPGM = $db->inzCallPGM();
		$db->bind_param ($callPGM, 1, "InternalKey", DB2_PARAM_IN );					
		$db->bind_param ($callPGM, 2, "ControlKey", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 3, "InputXML", DB2_PARAM_IN );		
		$db->bind_param ($callPGM, 4, "OutputXML", DB2_PARAM_OUT );	  	
		$ret = db2_execute($callPGM);*/
	}
	// Punto di uscita
	$wi400_trigger->executeExitPoint("LOGOUT","EXIT", array('user'=>$_SESSION['user'], "session_id"=>session_id()));
	// distruzione sessioni
	shutdown();
	session_unset();
	session_destroy();
	
?>