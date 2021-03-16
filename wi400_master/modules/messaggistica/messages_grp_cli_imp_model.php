<?
	
	$azione = $actionContext->getAction();
	
	if ($actionContext->getForm() == "ADD") {
		
		require_once 'modules/to/to_commons.php';
		
		$dati_mess = getListKeyArray("MANAGER_MESSAGES_HOME_LIST");
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_POST['IDLIST']);
	  	
		// INSERT
		$field = array("DSTID","DSTTYP","DSTDST","DSTIOE", "DSTOOA", "DSTSEQ", "DSTSTA", "USRMOD","TMSMOD", "USRINS", "TMSINS");
		$stmtinsert = $db->prepare("INSERT", "ZMSGDST", null, $field);
		
 	 	// COUNTER CLIENTE **************************
		$stmtcounter = $db->prepare("COUNTER", "ZMSGDST", "DSTID = ? AND DSTDST = ?");

		$timestamp = date('d/m/Y H:i:s');
		
		foreach($wi400List->getSelectionArray() as $key => $value) {
			
			$keyArray = explode("|",$key);
			
			$cliArray = getClientiV014($keyArray[0]);
			
			foreach ($cliArray as $c => $cliente){
				// Controllo se esiste già il cliente
				$rs = $db->execute($stmtcounter, array($dati_mess['TESID'], $cliente));
				$found = false;
				if ($rs){
					$arrayResult = $db->fetch_array($stmtcounter);
					if (isset($arrayResult["COUNTER"]) && $arrayResult["COUNTER"] > 0){
						$found = true;
					}
				}
				
				if (!$found){
					$campi = array($dati_mess['TESID'], "*INT", $cliente, "I", "OR", "1", "1", $_SESSION['user'], getDb2Timestamp($timestamp), $_SESSION['user'], getDb2Timestamp($timestamp));
					$db->execute($stmtinsert, $campi);
				}
			}
			
		}
	}
?>