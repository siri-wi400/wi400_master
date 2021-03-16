<?

	//require_once 'to_commons.php';
	
	$azione = $actionContext->getAction();
	
	$dati_mess = getListKeyArray("MANAGER_MESSAGES_HOME_LIST");
	
	if ($actionContext->getForm() == "ADD") {
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_POST['IDLIST']);
	  	
		// COUNTER CLIENTE **************************
		$stmtcounter = $db->prepare("COUNTER", "ZMSGDST", "DSTID = ? AND DSTDST = ?");
	
		// INSERT
		$field = array("DSTID","DSTTYP","DSTDST","DSTIOE", "DSTOOA", "DSTSEQ", "DSTSTA", "USRMOD","TMSMOD", "USRINS", "TMSINS");
		$stmtinsert = $db->prepare("INSERT", "ZMSGDST", null, $field);
		
		$timestamp = date('d/m/Y H:i:s');
		
		foreach($wi400List->getSelectionArray() as $key => $value){
			$keyArray = explode("|",$key);
			// Codice cliente
			$cliente = $keyArray[0];
			
			// Controllo se esiste già il cliente
			$rs = $db->execute($stmtcounter, array($dati_mess['TESID'], $cliente));
			$found = false;
			if ($rs){
				$arrayResult = $db->fetch_array($stmtcounter);
				if (isset($arrayResult["COUNTER"]) && $arrayResult["COUNTER"] > 0){
					$found = true;
				}
			}
			
			if (!$found) {
				// INSERT
				$campi = array($dati_mess['TESID'], "*INT", $cliente, "I", "OR", "1", "1", $_SESSION['user'], getDb2Timestamp($timestamp), $_SESSION['user'], getDb2Timestamp($timestamp));
				$db->execute($stmtinsert, $campi);
			}
		}
	}

?>