<?php
	
	global $db,$WI4_CHECK_VERIFY,$WI4_CHECK_MESSAGE,$WI4_CHECK_VERSION,$WI4_ACTION_NAME,$WI400_CHECK_MESSAGE;
	
	$azione = $actionContext->getAction();

	if($actionContext->getForm()=="CHECK_AZIONE") {
		
		$history->addCurrent();
		
		if(wi400Detail::getDetailValue($azione."_SRC", 'CODAZI')!="")
			$codazi = wi400Detail::getDetailValue($azione."_SRC", 'CODAZI');
		
		$row_azione = rtvAzione($codazi);
		
		// Verifica presenza files
		if (!glob($moduli_path."/".$row_azione['MODULO']."/".$codazi."_actioncheck.php")){
		$messageContext->addMessage("ERROR", "File checklist azione ".$moduli_path."/".$row_azione['MODULO']."/".$codazi."_actioncheck.php non trovato");
		$actionContext->gotoAction($azione,"DEFAULT","","",true,false);
		}else{
		// Reperisco il modulo di controllo
		require_once $moduli_path."/".$row_azione['MODULO']."/".$codazi.'_actioncheck'.".".'php';
		
		// Controllo la presenza dei files		
//		showArray($WI4_CHECK_FILES);
		foreach ($WI4_CHECK_FILES as $key=>$value){
			$directory = $moduli_path."/";
			$modulo = $row_azione['MODULO']."/";
			if (isset($value['path'])){
				$directory = $value['path'];
				$modulo = "";
				$value=$key;
			}
			if (glob($directory.$modulo.$value)){
			$WI4_CHECK_MESSAGE[] = '<span style="color:#008000;text-align:center;">'."File ".$directory.$modulo.$value." presente".'</span>';
		}
		else {
			$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."File ".$directory.$modulo.$value." non trovato".'</span>';
		}
		
		}
		
		// Controllo la presenza delle azioni
//						showArray($WI4_ACTION_NEED);
		foreach ($WI4_ACTION_NEED as $key=>$value){
			if (!rtvAzione($value)){
				$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."Azione ".$value." non presente".'</span>';
			}
			else {$WI4_CHECK_MESSAGE[] = '<span style="color:#008000;text-align:center;">'."Azione ".$value." presente".'</span>';
		
			}
		
		}
		
		// Controllo la presenza delle directory
//		showArray($WI4_CHECK_DIR);
		$array_create_dir = array();
		foreach ($WI4_CHECK_DIR as $key=>$value){
			if (!glob($key)){
				
			if ($value['CREATE']==True){
//				mkdir($key, 0777, true);
			$WI4_CHECK_MESSAGE[] = '<span style="color:#0000FF;text-align:center;">'."Directory ".$key." dovrà essere creata".'</span>';
			$array_create_dir [] = $key;
			}else{
				$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."Directory ".$key." non presente".'</span>';
			}
		}else {	$WI4_CHECK_MESSAGE[] = '<span style="color:#008000;text-align:center;">'."Directory ".$key." presente".'</span>';
			
		}
		
		}
		
		// Controllo la presenza dei settings
//		showArray($WI4_CHECK_SETTINGS);
//		showArray($settings);
		foreach ($WI4_CHECK_SETTINGS as $key=>$value){
			if (!isset($settings["$key"])){
				if (isset($value['TYPE']) && $value['TYPE'] != "OPTIONAL")
					$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."Settaggio ".$key."=>".$value['TYPE']." non presente, da inserire".'</span>';
				if (isset($value['TYPE']) && $value['TYPE'] == "OPTIONAL")
					$WI4_CHECK_MESSAGE[] = '<span style="color:#0000FF;text-align:center;">'."Settaggio ".$key."=>".$value['TYPE']." non presente ma opzionale".'</span>';			
				if (!isset($value['TYPE']))
					$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."Settaggio ".$key."=>".isset($value['TYPE'])." non presente, da inserire".'</span>';
			}else {	
				
				if (isset($value['TYPE']) && $value['TYPE'] == $settings["$key"]) 
				$WI4_CHECK_MESSAGE[] = '<span style="color:#008000;text-align:center;">'."Settaggio ".$key."=>".$value['TYPE']." presente".'</span>';
				if (isset($value['TYPE']) && $value['TYPE'] != $settings["$key"])
				$WI4_CHECK_MESSAGE[] = '<span style="color:#0000FF;text-align:center;">'."Settaggio ".$key."=>".$value['TYPE']." presente ma con valore diverso ".$key."=>".$settings["$key"].'</span>';
				if (!isset($value['TYPE']))
				$WI4_CHECK_MESSAGE[] = '<span style="color:#008000;text-align:center;">'."Settaggio ".$key."=>".isset($value['TYPE'])." presente".'</span>';
			}
		
		}
		
		// Controllo la presenza delle funzioni
//				showArray($WI4_CHECK_FUNCTIONS);
		foreach ($WI4_CHECK_FUNCTIONS as $key=>$value){
			if (!is_callable($value)){
					$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."Funzione ".$value." non richiamabile".'</span>';
				}
			else {	$WI4_CHECK_MESSAGE[] = '<span style="color:#008000;text-align:center;">'."Funzione ".$value." presente".'</span>';
		
			}
		
		}
		
		// Controllo IBM object
		if ($settings['platform']=="AS400"){
//						showArray($WI4_CHECK_IBMI_OBJECT);
		foreach ($WI4_CHECK_IBMI_OBJECT as $key=>$value){
		$decodeType ="i5_object";
		require_once p13nPackage($decodeType);
//		echo $key."-----".$value['LIB'];
		if ($value['OBJTYPE']){
		$decodeClass = new $decodeType();
		$decodeClass->setDecodeParameters(array(
				'TYPE' => 'i5_object',
				'OBJTYPE' => $value['OBJTYPE']
		));
		$decodeClass->setFieldValue($key);
		$decode_object = $decodeClass->decode();
		if (!$decode_object){
			$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."Oggetto ".$key." di tipo ".$value['OBJTYPE']." non presente".'</span>';
		}
		else {	$WI4_CHECK_MESSAGE[] = '<span style="color:#008000;text-align:center;">'."Oggetto ".$key." di tipo ".$value['OBJTYPE']." presente".'</span>';
		
		}
		
		}else{
			$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."Tipo Oggetto ".$key." di tipo ".$value['OBJTYPE']." non presente".'</span>';
		}
		
		}
		
		}else{
			$WI4_CHECK_MESSAGE[] = '<span style="color:#0000FF;text-align:center;">'."Il controllo IBM object è stato disabilitato perchè ambiente diverso da AS400".'</span>';
		}
		// Controllo la presenza delle tabelle
//		showArray($WI4_CHECK_TABLE);
		$array_ds = array();
		foreach ($WI4_CHECK_TABLE as $key=>$value){
			$libl ="";
			if (isset($value['LIBL']))$libl=$value['LIBL'];
			if (!$db->ifExist($key,$libl)){
				$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."Tabella ".$key." non presente".'</span>';
			}
			else {	$WI4_CHECK_MESSAGE[] = '<span style="color:#008000;text-align:center;">'."Tabella ".$key." presente".'</span>';
			$formato_campi = $db->columns($key,"",false,"",$libl);//showarray($formato_campi);
			$array_ds = getDs($key);
//			showArray($array_ds);
		
//			}
			// Check campi array to database
			if (isset($value['SRUCT'])){
			foreach($value['SRUCT'] as $field=>$values){
				if (!isset($array_ds["$field"])){
					$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."Field ".$field." non presente in ".$key.'</span>';}
					else{ $WI4_CHECK_MESSAGE[] = '<span style="color:#008000;text-align:center;">'."Field ".$field." presente in ".$key.'</span>';}
			}
/*			// Check campi database to array
			foreach($array_ds as $field=>$values){
				if (!isset($value["SRUCT"]["$field"])){
					$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."Field ".$field." non presente in array".'</span>';}
					else{ $WI4_CHECK_MESSAGE[] = '<span style="color:#008000;text-align:center;">'."Field ".$field." presente in array".'</span>';}
			}*/
			// Check formato dei campi
			foreach($value['SRUCT'] as $field=>$values){
				// Lunghezza campi
				if (isset($formato_campi["$field"]) && $values['LENGHT']!=$formato_campi["$field"]["LENGTH_PRECISION"])
				$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."La lunghezza del campo passato ".$field." è da ".$values['LENGHT']." e non coincide con ".$formato_campi["$field"]["LENGTH_PRECISION"].'</span>';
				// Tipo campo
				if (isset($formato_campi["$field"]) && $values['TYPE']!=$formato_campi["$field"]["DATA_TYPE_STRING"])
					$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."La Data type del campo passato ".$field." è da ".$values['LENGHT']." e non coincide con ".$formato_campi["$field"]["LENGTH_PRECISION"].'</span>';
				}
			}
			}
		}
		
		// Controllo la presenza dei moduli PHP abilitati
		//				showArray($WI4_CHECK_PHP_MODULES);
		foreach ($WI4_CHECK_PHP_MODULES as $key=>$value){
			if (!extension_loaded($key)){
				$WI4_CHECK_MESSAGE[] = '<span style="color:#FF0000;text-align:center;">'."Modulo PHP ".$value." non abilitato".'</span>';
			}
			else {	$WI4_CHECK_MESSAGE[] = '<span style="color:#008000;text-align:center;">'."Modulo PHP ".$value." abilitato".'</span>';
		
			}
		
		}
		
		// Particolari Configurazioni o Release, da funzione particolare
		$check_func = wi400_custom_function_check();
		$WI4_CHECK_MESSAGE = array_merge($WI4_CHECK_MESSAGE, $check_func);
		}

	}
	elseif($actionContext->getForm()=="CREATE_DIR") {
		$array_create_dir=array();
		$array_create_dir = explode(";",$_REQUEST['ARRAY_DIR']);
		// Controllo la presenza delle directory
//		showArray($array_create_dir);
		foreach ($array_create_dir as $value){
			if (!glob($value)){
				mkdir($value, 0777, true);
				if (glob($value)) $messageContext->addMessage("INFO", "Directory ".$value." creata");
				if (!glob($value)) $messageContext->addMessage("ERROR", "Directory ".$value." non creata");
				}else{
					$messageContext->addMessage("INFO", "Directory ".$value." già presente");
				}
		$actionContext->gotoAction($azione,"DEFAULT");
	}		
	}
	function wi400_check_message_format($error, $message) {
		$messaggio="";
		if ($error==True) {
			$messaggio = '<span style="color:#FF0000;text-align:center;">'.$message.'</span>';
		} else {
			$messaggio = '<span style="color:#008000;text-align:center;">'.$message.'</span>';
		}
		return $messaggio;
	}