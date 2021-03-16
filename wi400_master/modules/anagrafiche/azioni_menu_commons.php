<?php

	$tipo_azione = array(
		"A" => "AZIONI",
		"B" => "AZIONI",
		"N" => "AZIONI",
		"M" => "MENU"
	);
	
	function get_widget() {
		$widget = '0';
		if (isset($_POST['HAS_WIDGET'])) {
			$widget = '1';
		}
		
		return $widget;
	}

	function get_azione_sistema() {
		// Azione di sistema
		$sistema = '0';
		if (isset($_POST['SYSTEM'])) {
			 $sistema = '1';
		}
		
		return $sistema;
	}
	
	function get_azione_modale() {
		// Azione di sistema
		$modale = '0';
		if (isset($_POST['URL_MODAL'])) {
			$modale = '1';
		}
	
		return $modale;
	}
	
	function get_azione_log() {
		// Azione di sistema
		$log = 'N';
		if (isset($_POST['LOG_AZIONE'])) {
			$log = 'S';
		}
	
		return $log;
	}
	
	function get_azione_disabilita() {
		// Azione di sistema
		$disable = '';
		if (isset($_POST['DISABLE'])) {
			$disable = 'S';
		}
	
		return $disable;
	}
	
	function get_permessi($drag_and_drop) {
		global $wi400_groups;
		
		// Permessi
		$authGroups = array();
		$authGroupsList = "";
/*		
		foreach($wi400_groups as $groupName) {
			if(isset($_POST[$groupName])) {
				$authGroups[] = $groupName;
			}
		}
		$authGroupsList = implode(";",$authGroups);
*/
		if(isset($_REQUEST[$drag_and_drop]))
			$authGroupsList = str_replace(",", ";", $_REQUEST[$drag_and_drop]);
//		echo "AUTH GROUPS: $authGroupsList<br>";
		
		return $authGroupsList;
	}
	
	function insert_azioni($azione, $drag_and_drop) {
		global $db, $messageContext;
		
		// Recupero dei dati
		// Permessi
		$authGroupsList = get_permessi($drag_and_drop);
		
		// Azione di sistema
		$widget = get_widget();
		$sistema = get_azione_sistema();
		$modale = get_azione_modale();
		$log = get_azione_log();
		$disable = get_azione_disabilita();
		
		$field = array("AZIONE", "DESCRIZIONE", "MODULO", "VIEW", "TIPO", "ICOMENU", "WI400_GROUPS", "EXPICO","MODEL","GATEWAY","VALIDATION", "SYSTEM", "URL", "URL_OPEN", "URL_MODAL", "LOG_AZIONE", "HAS_WIDGET", "DISABILITA");
		$field = $db->escapeSpecialKey($field);
		$stmt = $db->prepare("INSERT", "FAZISIRI", null, $field);
		
		$campi = array($azione, $_POST['DESCRIZIONE'], $_POST['MODULO'], $_POST['VIEW'], $_POST['TIPO'], '', $authGroupsList, '',$_POST['MODEL'],$_POST['GATEWAY'],$_POST['VALIDATION'], $sistema, $_POST['URL'],$_POST['URL_OPEN'], $modale, $log, $widget, $disable);
		$result = $db->execute($stmt, $campi);
		
	    if($result) 
	    	$messageContext->addMessage("SUCCESS", _t('UPDATE_SUCCESS'));
	    else 
	    	$messageContext->addMessage("ERROR", _t('UPDATE_ERROR'));
	}
	
	function update_azioni($azione, $drag_and_drop) {
		global $db, $messageContext;
		
		// Recupero dei dati
		// Permessi
		$authGroupsList = get_permessi($drag_and_drop);
		
		// Azione di sistema
		$widget = get_widget();
		$sistema = get_azione_sistema();
		$modale = get_azione_modale();
		$log = get_azione_log();
		$disable = get_azione_disabilita();
		
		$field = array("DESCRIZIONE", "MODULO", "VIEW", "TIPO", "WI400_GROUPS","MODEL","GATEWAY","VALIDATION", "SYSTEM", "URL", "URL_OPEN", "URL_MODAL", "LOG_AZIONE", "HAS_WIDGET", "DISABILITA", 'TABLETICO', 'TABLETCOL');
		$field = $db->escapeSpecialKey($field);
		$key = array("AZIONE"=>$azione);
		$stmt = $db->prepare("UPDATE", "FAZISIRI", $key, $field);
	
		$campi = array($_POST['DESCRIZIONE'], $_POST['MODULO'], $_POST['VIEW'], $_POST['TIPO'], $authGroupsList, $_POST['MODEL'],$_POST['GATEWAY'],$_POST['VALIDATION'], $sistema, $_POST['URL'],$_POST['URL_OPEN'], $modale, $log, $widget, $disable, $_POST['TABLETICO'], $_POST['COLORE']);
		$result = $db->execute($stmt, $campi);
		
	    if($result) 
	    	$messageContext->addMessage("SUCCESS", _t('UPDATE_SUCCESS'));
	    else 
	    	$messageContext->addMessage("ERROR", _t('UPDATE_ERROR'));
	}
	
    function get_language_string($argo, $key, $lang) {
   	    global $db;
   	     
		$sql = "select * from FLNGTRST WHERE LANG='".$lang."' AND ARGO='".$argo."' and KEY='".$key."'";
		$sql = $db->escapeSpecialKey($sql);
		$result = $db->singleQuery($sql);
		$traduzione = "";
		if($row = $db->fetch_array($result)) {
			$traduzione = $row['STRING'];
		}
		return $traduzione;
    }
    
    function set_language_string($argo, $key, $lang) {
   	    global $db, $messageContext;
   	    
   	    $lingua = getLanguageID($lang);
   	    $string = "";
   	    if(isset($_POST[$lang]))
   	   		$string = trim($_POST[$lang]);

   	    $sql = "select * from FLNGTRST WHERE LANG='$lingua' AND ARGO='$argo' and KEY='$key'";
   	    $sql = $db->escapeSpecialKey($sql);
   	    $result_src = $db->singleQuery($sql);
		if($row = $db->fetch_array($result_src)) {
			if($string!="") {
				// Update
				$field = array("STRING");
				$keys = array("LANG" => $lingua, "ARGO" => $argo, "KEY" => $key);
				$keys = $db->escapeSpecialKey($keys);
				$stmt = $db->prepare("UPDATE", "FLNGTRST", $keys, $field);
			
				$campi = array($string);
				$result = $db->execute($stmt, $campi);
			}
			else {
				$keys = array("LANG","ARGO","KEY");
				$keys = $db->escapeSpecialKey($keys);
				$stmt_delete = $db->prepare("DELETE", "FLNGTRST", $keys, null);
				$campi = array($lingua,$argo,$key);
				$result = $db->execute($stmt_delete, $campi);
			}
		}
		else if($string!=""){
			// Insert
			$field = array("LANG", "ARGO", "KEY", "STRING");
			$field = $db->escapeSpecialKey($field);
			$stmt = $db->prepare("INSERT", "FLNGTRST", null, $field);
			
			$campi = array($lingua, $argo, $key, $string);
			$result = $db->execute($stmt, $campi);
		}
		
		if(isset($result)) {
			if($result) 
		    	$messageContext->addMessage("SUCCESS", _t('UPDATE_SUCCESS'));
		    else 
		    	$messageContext->addMessage("ERROR", _t('UPDATE_ERROR'));
		}
    }
    
    function add_missing_parameters_post() {
    	if($_POST['TIPO'] == "M" || $_POST['TIPO'] == "L" || $_POST['TIPO'] == "T") {
    		$_POST['MODULO'] = "";
    		$_POST['GATEWAY'] = "";
    		$_POST['MODEL'] = "";
    		$_POST['VIEW'] = "";
    		$_POST['VALIDATION'] = "";
    			
    		if($_POST['TIPO'] == "M") {
    			$_POST['URL'] = "";
    			$_POST['URL_OPEN'] = "";
    		}
    		if($_POST['TIPO'] == 'T') {
    			$_POST['URL'] = "";
    		}
    	}else {
    		$_POST['URL'] = "";
    	}
    }
?>