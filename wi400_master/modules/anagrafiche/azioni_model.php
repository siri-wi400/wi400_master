<?php 
	$wi400_trigger->registerExitPoint("AZIONI","CUSTOM_TAB", "*WI400", "Tab custom su azioni utenti", '$tipo_azione');
	require_once 'azioni_menu_commons.php';
	developer_add_variable("PROVA_MODEL", "LUCA");
	$azione_corrente = $actionContext->getAction();
	
	if(in_array($actionContext->getForm(),array("DETAIL","COPIA","INSERT","UPDATE"))) {
		$lang_array = array();
		if(isset($settings['multi_language']) && $settings['multi_language']===True) {
			$path = "$base_path/lang";
			$dir = opendir("$path");
			while($thafile = readdir($dir)) {
				if(is_file("$path/$thafile") && preg_match("/.lang\.php$/", "$path/$thafile")) {
					$thafile = str_replace ( ".lang.php", "", $thafile );
					if($thafile!=$settings['default_language']) {
						$lang_array[] = $thafile;
					}
				}
			}
		}
//		echo "LANG ARRAY:<pre>"; print_r($lang_array); echo "</pre>";
	}
	
	if(in_array($actionContext->getForm(),array("DEFAULT","DETAIL","COPIA")))
		$history->addCurrent();
		
	if(!in_array($actionContext->getForm(),array("DEFAULT","DETAIL","COPIA"))) {
		$azione = wi400Detail::getDetailValue("DETTAGLIO_AZIONE","codazi");
	}

	if($actionContext->getForm()=="DEFAULT") {
		if(isset($_SESSION['SAV_AZIONE'])) {
			unset($_SESSION['SAV_AZIONE']);
		}
	}
	else if(in_array($actionContext->getForm(),array("DETAIL","COPIA"))) {
		$sql="select * from FAZISIRI where AZIONE =?";
		$stmt = $db->singlePrepare($sql,0,true);
		
		if($actionContext->getForm()=="DETAIL") {
			$azione = wi400Detail::getDetailValue("SEARCH_ACTION","codazi");
			
			$result = $db->execute($stmt,array($azione));
			
			// Azione corrente
			$actionContext->setLabel("Dettaglio azione");
		}
		else if($actionContext->getForm()=="COPIA") {
			$azione_old = wi400Detail::getDetailValue("COPY_ACTION","codazi1");
			$azione_new = wi400Detail::getDetailValue("COPY_ACTION","codazi2");
			
			$result = $db->execute($stmt,array($azione_old));
			
			// Azione corrente
			$actionContext->setLabel("Copia azione");
		}
		
		$resultArray = $db->columns('FAZISIRI');
//		echo "COLUMNS:<pre>"; print_r($resultArray); echo "</pre>";

		$row = $db->fetch_array($stmt);
//		echo "ROW:<pre>"; print_r($row); echo "</pre>";
		
		$saveAction = "INSERT";
		
		if($actionContext->getForm()=="DETAIL" && isset($row["AZIONE"])) {
			$saveAction = "UPDATE";
		}
		else {
			if($actionContext->getForm()=="DETAIL") {
				if(!isset($_REQUEST['MODULO']) && !isset($_SESSION['SAV_AZIONE']['MODULO'])) {
					$model = strtolower($azione)."_model.php";
					$view = strtolower($azione)."_view.php";
				}
			}
			else if($actionContext->getForm()=="COPIA") {
				if(isset($row['TIPO']) && $row['TIPO'] == "B") {
					$model = strtolower($azione_new)."_batch.php";
					$view = "";
				}
				else {
					$model = strtolower($azione_new)."_model.php";
					$view = strtolower($azione_new)."_view.php";
				}
			}
		}
//		echo "SAVE: $saveAction<br>";	
			
		if($actionContext->getForm()=="COPIA") {
			if(isset($row['TIPO']) && $row['TIPO'] == "B") {
				$row['MODEL'] = strtolower($azione_new)."_batch.php";
				$row['VIEW'] = "";
			}
			else {		
				if(isset($row['MODEL']) && $row['MODEL']!="")
					$row['MODEL'] = strtolower($azione_new)."_model.php";
				if(isset($row['VIEW']) && $row['VIEW']!="")
					$row['VIEW'] = strtolower($azione_new)."_view.php";
			}
			
			if(isset($row['VALIDATION']) && $row['VALIDATION']!="")
				$row['VALIDATION'] = strtolower($azione_new)."_validation.php";
			if(isset($row['GATEWAY']) && $row['GATEWAY']!="")
				$row['GATEWAY'] = strtolower($azione_new)."_gateway.php";
		}
		
		$from_groups_array = array();
		$to_groups_array = array();
			
		if(isset($_REQUEST[$azione_corrente."_DRAG_TO_GROUPS"]))
			$to_groups_array = explode(",", $_REQUEST[$azione_corrente."_DRAG_TO_GROUPS"]);

		if(!empty($to_groups_array) && $to_groups_array[0]=="")
			$to_groups_array = array();
		
		if(isset($_REQUEST[$azione_corrente."_DRAG_FROM_GROUPS"]))
			$from_groups_array = explode(",", $_REQUEST[$azione_corrente."_DRAG_FROM_GROUPS"]);

		if(!empty($from_groups_array) && $from_groups_array[0]=="")
			$from_groups_array = array();
			
//		echo "TO GROUPS:<pre>"; print_r($to_groups_array); echo "</pre>";
//		echo "FROM GROUPS:<pre>"; print_r($from_groups_array); echo "</pre>";
		
		$from_groups = array();
		$to_groups = array();
			
		if(empty($to_groups_array) && empty($from_groups_array)) {
			if(isset($row["WI400_GROUPS"]) && $row["WI400_GROUPS"] != "")
				$to_groups_array = explode(";", $row["WI400_GROUPS"]);
		
			$from_groups_array = array_diff($wi400_groups, $to_groups);
		}
			
		if(!empty($to_groups_array)) {
			foreach($to_groups_array as $group) {
				$to_groups[$group] = $group;
			}
		}
			
		if(!empty($from_groups_array)) {
			foreach($from_groups_array as $group) {
				$from_groups[$group] = $group;
			}
		}
	}
	else if(in_array($actionContext->getForm(),array("INSERT","UPDATE"))) {
		add_missing_parameters_post();
		
		if($actionContext->getForm()=="INSERT")
			insert_azioni($azione, $azione_corrente."_DRAG_TO_GROUPS");
		else if($actionContext->getForm()=="UPDATE")
			update_azioni($azione, $azione_corrente."_DRAG_TO_GROUPS");
			
		if($settings['multi_language']===true) {
//			echo "POST:<pre>"; print_r($_POST); echo "</pre>";
			foreach($lang_array as $val) {
				set_language_string($tipo_azione[$_POST['TIPO']],$azione,$val);	
			}
		}
		
		if(isset($_SESSION['LIST_ACTION'])) {
			$disable = get_azione_disabilita();
			//showArray($_SESSION['LIST_ACTION']['TARTICOLI']);
			$_SESSION['LIST_ACTION']['TARTICOLI']['DISABILITA'] = $disable;
		}
		
		$actionContext->onSuccess($azione_corrente, "DEFAULT");
	    $actionContext->onError($azione_corrente, "DETAIL");
	}
	else if(in_array($actionContext->getForm(),array("INSERT_MENU","UPDATE_MENU"))) {
		add_missing_parameters_post();
		
		if($actionContext->getForm()=="INSERT_MENU")
			insert_azioni($azione, $azione_corrente."_DRAG_TO_GROUPS");
		else if($actionContext->getForm()=="UPDATE_MENU")
			update_azioni($azione, $azione_corrente."_DRAG_TO_GROUPS");
		
		$actionContext->onSuccess("TMENU", "DETAIL", "", "AZIONI");
	    $actionContext->onError($azione_corrente, "DETAIL");
	}
	else if($actionContext->getForm()=="DELETE") {
		$sql = "delete from FAZISIRI where AZIONE='$azione'";
        $result = $db->query($sql); 
        
        if($settings['multi_language']===true) {
        	$keys = array("ARGO","KEY");
        	$keys = $db->escapeSpecialKey($keys);
			$stmt_delete = $db->prepare("DELETE", "FLNGTRST", $keys, null);
			$campi = array($tipo_azione[$_POST['TIPO']],$azione);
			$result_del = $db->execute($stmt_delete, $campi);
        }
        
       	if($result) 
       		$messageContext->addMessage("SUCCESS", _t('DELETE_SUCCESS',array($azione)));
	    else 
	    	$messageContext->addMessage("ERROR", _t('DELETE_ERROR', array($azione)));
	    
	    $actionContext->onSuccess($azione_corrente, "DEFAULT");
    	$actionContext->onError($azione_corrente, "DETAIL");
	}
	else if($actionContext->getForm()=="CHECK_FILES") {
		$modulo = wi400Detail::getDetailValue("DETTAGLIO_AZIONE","MODULO");
//		echo "MODULO:$modulo<br>";
		
		$pers_path = $root_path."p13n/";
		
		$dir_pers_handle = opendir($pers_path);
		
		// Directories contenenti dati personalizzati
		$file_pers_dir = array();
		while(($dir=readdir($dir_pers_handle))!==false) {
			if(!in_array($dir,array(".","..","CVS"))) {
				$file_pers_dir[] = $dir;
			}
		}
//		echo "DIRECTORIES FILES PERSONALIZZATI:<pre>"; print_r($file_pers_dir); echo"</pre>";
		
		closedir($dir_pers_handle);
		
		$array_files = array(
			"MODEL" => "", 
			"VIEW" => "", 
			"VALIDATION" => "", 
			"GATEWAY" => ""
		);
		$array_p13nfiles = array(
			"MODEL" => array(), 
			"VIEW" => array(), 
			"VALIDATION" => array(), 
			"GATEWAY" => array()
		);
		
		foreach($array_files as $key => $val) {
			$filename = wi400Detail::getDetailValue("DETTAGLIO_AZIONE",$key);
			
			if($filename=="")
				continue;
				
			// Files di default
			$array_files[$key] = $filename;
				
			// Files personalizzati
			foreach($file_pers_dir as $dir) {
				$p13n_file = $pers_path.$dir."/modules/".$modulo."/".$filename;
//				echo "FILE 1: $p13n_file<br>";
				if(file_exists($p13n_file)) {
//					echo "FILE: $p13n_file<br>";
					$array_p13nfiles[$key][] = $p13n_file;
				}
			}
		}
//		echo "FILES:<pre>"; print_r($array_files); echo"</pre>";
//		echo "P13N FILES:<pre>"; print_r($array_p13nfiles); echo"</pre>";
	}

?>