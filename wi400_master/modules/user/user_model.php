<?php

	require_once 'user_commons.php';
	
	$wi400_trigger->registerExitPoint("USER","CUSTOM_TAB", "*WI400", "Tab custom su anagrafica utenti", "user");
	$wi400_trigger->registerExitPoint("USER","SAVE", "*WI400", "Tab custom su anagrafica utenti", "form");
	
	$azione = $actionContext->getAction();

	if(in_array($actionContext->getForm(),array("DEFAULT","DETAIL","COPIA")))
    	$history->addCurrent();
    
	// Recupero permessi
	$authGroups = array();
	$authGroupsList = "";
	
	if(in_array($actionContext->getForm(),array("INSERT","UPDATE"))) {
/*		
		foreach ($wi400_groups as $groupName){
			if (isset($_POST[$groupName])){
				$authGroups[] = $groupName;
			}
		}
		$authGroupsList = implode(";",$authGroups);
*/		
		if(isset($_REQUEST[$azione."_DRAG_TO_GROUPS"]))
			$authGroupsList = str_replace(",", ";", $_REQUEST[$azione."_DRAG_TO_GROUPS"]);
//			echo "AUTH GROUPS: $authGroupsList<br>";
	}
	
	if(!in_array($actionContext->getForm(),array("DEFAULT","DETAIL","COPIA"))) {
		$codUsr = wi400Detail::getDetailValue("USER_DETAIL","codusr");
	}

	if($actionContext->getForm()=="DEFAULT") {
		endLock($users_table,"",session_id());
		// Pulisco il dettaglio, altrimenti se ci rientro lo ritrovo in sessione.
		wi400Detail::cleanSession("USER_DETAIL");
		
		$codUsr = wi400Detail::getDetailValue("USER_SEARCH","codusr");
		$user_old = wi400Detail::getDetailValue("COPY_USER","codusr1");
		$user_new = wi400Detail::getDetailValue("COPY_USER","codusr2");
	}
	else if(in_array($actionContext->getForm(),array("DETAIL","COPIA"))) {
		// Se ci sono errori sul form di dettaglio segnalo l'errore
//		if ($messageContext->getSeverity() == "ERROR") {
//			$messageContext->addMessage("ERROR", "Sono presenti errori nel form. Controlla i dati inseriti.");
//		}
//		else{
			// .. altimenti proseguo con il caricamento dei dati del detail
		    $sql = "SELECT * FROM $users_table WHERE USER_NAME=?";
//		    echo "SQL: $sql<br>";
			$stmt = $db->singlePrepare($sql,0,true);
		
			$codUsr = "";
			$user_new = "";
			if($actionContext->getForm()=="DETAIL") {
				$codUsr = wi400Detail::getDetailValue("USER_SEARCH","codusr");
				
				$result = $db->execute($stmt,array($codUsr));
				
				// Azione corrente
				$actionContext->setLabel("Dettaglio utente");
			}
			else if($actionContext->getForm()=="COPIA") {
				$user_old = wi400Detail::getDetailValue("COPY_USER","codusr1");
				$user_new = wi400Detail::getDetailValue("COPY_USER","codusr2");
				
				$result = $db->execute($stmt,array($user_old));
				
				// Azione corrente
				$actionContext->setLabel("Copia utente");
			}
		
			$row = $db->fetch_array($stmt);
//			echo "ROW:<pre>"; print_r($row); echo "</pre>";
			$resultArray = $db->columns($users_table);
//			echo "COLS:<pre>"; print_r($resultArray); echo "</pre>";
		
			$saveAction = "INSERT";
			if($actionContext->getForm()=="DETAIL" && isset($row["USER_NAME"])) {
				$saveAction = "UPDATE";
				
				// Lock dell'articolo selezionato
				startLock($users_table,$codUsr);
			}
			else {
				// Lock dell'articolo selezionato
				startLock($users_table,$user_new);
			}
			
			$from_groups_array = array();
			$to_groups_array = array();
			
			if(isset($_REQUEST[$azione."_DRAG_TO_GROUPS"]))
				$to_groups_array = explode(",", $_REQUEST[$azione."_DRAG_TO_GROUPS"]);
			
			if(!empty($to_groups_array) && $to_groups_array[0]=="")
				$to_groups_array = array();
			
			if(isset($_REQUEST[$azione."_DRAG_FROM_GROUPS"]))
				$from_groups_array = explode(",", $_REQUEST[$azione."_DRAG_FROM_GROUPS"]);
			
			if(!empty($from_groups_array) && $from_groups_array[0]=="")
				$from_groups_array = array();
			
//			echo "TO GROUPS:<pre>"; print_r($to_groups_array); echo "</pre>";
//			echo "FROM GROUPS:<pre>"; print_r($from_groups_array); echo "</pre>";

			$from_groups = array();
			$to_groups = array();
			
			if(empty($to_groups_array) && empty($from_groups_array)) {
				if(isset($row["WI400_GROUPS"]) && $row["WI400_GROUPS"] != "")
					$to_groups_array = explode(";", $row["WI400_GROUPS"]);
				
				$from_groups_array = array_diff($wi400_groups, $to_groups_array);
			}
			
//			echo "TO GROUPS:<pre>"; print_r($to_groups_array); echo "</pre>";
//			echo "FROM GROUPS:<pre>"; print_r($from_groups_array); echo "</pre>";
			
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
			
//			echo "TO GROUPS:<pre>"; print_r($to_groups_array); echo "</pre>";
//			echo "FROM GROUPS:<pre>"; print_r($from_groups_array); echo "</pre>";
//		}	
				
//		$actionContext->onError($azione,"DEFAULT","","",true);		
	}
	else if ($actionContext->getForm() == "UPDATE"){
		$admin = $sistema = '0';
		if (isset($_POST['ADMIN'])) {
			 $admin = '1';
		}
		$field = array(
				"FIRST_NAME", "LAST_NAME", "EMAIL", "OFFICE", "THEME","PHONE", "TITLE", "ADDRESS", "CITY", "STATE_PROVINCE", "COUNTRY", 
				"MENU", "USER_MENU", "LANGUAGE", "AUTH_METOD","USER_GROUP","WI400_GROUPS", "DEFAULT_ACTION", "ADMIN", "PACKAGE", "MYPASSWORD");
		if ($_POST['MYPASSWORD']=="") {
			unset($field[count($field)-1]);
        }		
		$key = array("USER_NAME"=>$codUsr);
		$stmt = $db->prepare("UPDATE", $users_table, $key, $field);
		$campi = array($_POST["FIRST_NAME"], $_POST["LAST_NAME"], $_POST["EMAIL"], trim($_POST["OFFICE"]), trim($_POST["THEME"]),
				$_POST["PHONE"], $_POST["TITLE"], $_POST["ADDRESS"], $_POST["CITY"], $_POST["STATE_PROVINCE"], $_POST["COUNTRY"], 
				$_POST["MENU"], $_POST["USER_MENU"], $_POST["LANGUAGE"],$_POST["AUTH_METOD"],$_POST["USER_GROUP"],$authGroupsList, 
				$_POST["DEFAULT_ACTION"], $admin, $_POST['PACKAGE'], md5($_POST['MYPASSWORD']));
		if ($_POST['MYPASSWORD']=="") {
			 unset($campi[count($campi)-1]);
        }
	
		$result = $db->execute($stmt, $campi);
	    if ($result) 
	    	$messageContext->addMessage("SUCCESS", "Dati Generali aggiornati con successo");
	    else 
	    	$messageContext->addMessage("ERROR", "Si sono verificati degli errori");

    	$actionContext->onSuccess($azione,"DEFAULT");
    	$actionContext->onError($azione,"DETAIL","","",true);
    	
		// Caricamento parametri standard utente
		$abilitazioni= parse_ini_file("conf/WI400UserParmDefault.conf");
		
		foreach ($abilitazioni as $key=>$value) {
			if (isset($_POST[$key]) AND trim($_POST[$key])!="") { 
			   $array[$key]=$_POST[$key];
			}   
		}
		
		$myFile = wi400File::getUserFile("parm", "WI400UserParm.conf");
		if (isset($array)){
			write_ini_file($myFile, $array);
		}
		
//		$wi400_trigger->executeExitPoint("USER","SAVE", array("form"=>"UPDATE"));
		$wi400_trigger->executeExitPoint("USER","SAVE", array());
		
		printMessageError();
	} 
	else if ($actionContext->getForm() == "CHECK"){
		// Controllo
		$saveAction = $_POST['SAVE_ACTION'];
		// Controllo area Merceologica
		//$messageContext->addMessage("ERROR", "Hai sbagliato un po' di cose");
		/*echo "<pre>";
		print_r($_POST);
		echo "</pre>";
		die();*/
		$actionContext->onSuccess("EPRO","DETAIL");
    	$actionContext->onError("EPRO","DETAIL","","",true);   	
	} 
	else if ($actionContext->getForm() == "DELETE"){
		// Eliminazine
        $sql = "DELETE FROM $users_table WHERE USER_NAME='".$codUsr."'";
        $result = $db->query($sql); 
       	if ($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record utente ".$codUsr." eseguita");
	    else 
	    	$messageContext->addMessage("ERROR", "Il record dell'utente ".$codUsr." non è stato cancellato");
	    	
	    $actionContext->onSuccess($azione);
    	$actionContext->onError($azione,"DETAIL","","",true);
    	
	   	$wi400_trigger->executeExitPoint("USER","SAVE", array("form"=>"DELETE"));
	   	
	   	printMessageError();
	} 
	else if ($actionContext->getForm() == "INSERT"){ 
		$admin = $sistema = '0';
		if (isset($_POST['ADMIN'])) {
			$admin = '1';
		}
		
		$field = array("USER_NAME", "FIRST_NAME", "LAST_NAME", "EMAIL", "OFFICE", "THEME","PHONE", "TITLE", "ADDRESS", "CITY", 
			"STATE_PROVINCE", "COUNTRY", "MENU", "USER_MENU", "LANGUAGE", "OTHER", "LASTACTIVE", "AUTH_METOD","USER_GROUP","WI400_GROUPS", 
			"DEFAULT_ACTION", "MYPASSWORD", "ADMIN", "PACKAGE");
		//$key = array("USER_NAME"=>$_POST['codusr']);
		$key = array();
		$stmt = $db->prepare("INSERT", $users_table, $key, $field);
		
		$myPassword = "";
		if(isset($_POST['MYPASSWORD']))
			$myPassword = md5($_POST['MYPASSWORD']);
		
		$campi = array($codUsr, $_POST["FIRST_NAME"], $_POST["LAST_NAME"], $_POST["EMAIL"], trim($_POST["OFFICE"]), trim($_POST["THEME"]), 
				$_POST["PHONE"], $_POST["TITLE"], $_POST["ADDRESS"], $_POST["CITY"], $_POST["STATE_PROVINCE"], $_POST["COUNTRY"], 
				$_POST["MENU"], $_POST["USER_MENU"], $_POST["LANGUAGE"], "nessuna nota", 1,$_POST["AUTH_METOD"],$_POST["USER_GROUP"],
				$authGroupsList, $_POST["DEFAULT_ACTION"], md5($_POST['MYPASSWORD']), $admin, $_POST['PACKAGE']);

		$result = $db->execute($stmt, $campi);
	    
	    if ($result) 
	    	$messageContext->addMessage("SUCCESS", "Dati Generali aggiornati con successo");
	    else 
	    	$messageContext->addMessage("ERROR", "Si sono verificati degli errori nell'inserimento");

    	$actionContext->onSuccess($azione);
    	$actionContext->onError($azione,"DETAIL","","",true);
    	
		// Caricamento parametri standard utente
		$userStd = parse_ini_file("conf/WI400UserParmDefault.conf");
		// Caricamento parametri personali utente
		$myFile = wi400File::getUserFile("parm", "WI400UserParm.conf");
		$userParm = parse_ini_file($myFile);
		$abilitazioni = array_merge($userStd, $userParm);
		
//		$wi400_trigger->executeExitPoint("USER","SAVE", array("form"=>"INSERT"));
		$wi400_trigger->executeExitPoint("USER","SAVE", array());
		
		printMessageError();
	}
	
function write_ini_file($path, $assoc_array) {
	$content = "";
    foreach ($assoc_array as $key => $item) {
        if (is_array($item)) {
            $content .= "\n[$key]\n";
            foreach ($item as $key2 => $item2) {
                $content .= "$key2 = \"$item2\"\r\n";
            }        
        } else {
            $content .= "$key = \"$item\"\r\n";
        }
    }        
    
    if (!$handle = fopen($path, 'w')) {
        return false;
    }
    if (!fwrite($handle, $content)) {
        return false;
    }
    fclose($handle);
    return true;
}

function parseXML($dom){
	$array = array();	
	// Cerco se c'è resource	
	$params = $dom->getElementsByTagName('configurazione'); // Find Sections
	// .. se non c'è
	if (!isset($params->item(0)->nodeValue) or ($params->item(0)->nodeValue)=="") $params = $dom->getElementsByTagName('event'); // Find Sections
	// Se non ho trovato nulla errore
	if (!isset($params)) return;
	$k=0;
	foreach ($params as $param){
		$params2 = $params->item($k)->getElementsByTagName('parametro'); //Vado in profondità sugli attributi
		$i=0;
		foreach ($params2 as $p) {
			$params3 = $params2->item($i)->getElementsByTagName('attribute'); //dig Arti into Categories
			$j=0;
			foreach ($params3 as $p2) {
				$array[$i][$params3->item($j)->getAttribute('id')]= $params3->item($j)->getAttribute('value');
				$j++;   
			}              
			$i++;
		}
		$k++;    
	}
  	return $array;
}
?>