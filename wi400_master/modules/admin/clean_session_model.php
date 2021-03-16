<?php

//	echo "<b>SESSION ID:</b> ".strtoupper(session_id())."<br><br>";
	
	// Pulizia delle tabelle temporanee e dei subfile
	$sql = "select * from QSYS2/SYSTABLES where TABLE_SCHEMA='".$settings['db_temp']."' 
		and TABLE_NAME like '%".strtoupper(session_id())."'";
	
//	echo "<b>SQL:</b> $sql<br><br>";
	
	$result = $db->query($sql);
	
	$isError = false;

	while($row = $db->fetch_array($result)) {
//		echo "<b>TABLE:</b> ".$row['TABLE_NAME']."<br><br>";
		$sql_drop = "drop table ".$settings['db_temp']."/".$row['TABLE_NAME'];

		$result_drop = $db->query($sql_drop);

		if(!$result_drop)
			$isError = true;
	}
	
	// Cancello i file wi400Session
	if (!$isError) wi400Session::destroy();
	
	if($isError===true)
		$messageContext->addMessage("ERROR", "Errore durante la pulizia delle tabelle temporanee e dei subfiles");
	else
		$messageContext->addMessage("SUCCESS", "Tabelle temporanee e subfiles puliti con successo");
		
	// Pulizia delle liste
	$isError = false;
	
	foreach($_SESSION as $val) {
		if(is_object($val) && get_class($val)=="wi400List") {
			$idList = $val->getIdList();
//			echo "<b>LIST PRIMA:</b> "; print_r($_SESSION[$idList]); echo "<br>";
//			echo "<b>NOME LISTA:</b> $idList<br>";
			if(!sessionUnregister($idList)) {
				$isError = true;
			}
//			echo "<b>LIST DOPO:</b> "; print_r($_SESSION[$idList]); echo "<br><br>";
		}
	}
		
	if($isError===true)
		$messageContext->addMessage("ERROR", "Errore durante la pulizia delle liste");
	else {
		$messageContext->addMessage("SUCCESS", "Liste pulite con successo");
	}
	
	// Pulizia dei details
	$isError = false;
	
	if(isset($_SESSION["WI400_DETAILS"])) {
//		echo "<b>DETAILS PRIMA:</b> "; print_r($_SESSION["WI400_DETAILS"]); echo "<br>";
		if(!sessionUnregister("WI400_DETAILS")) {
			$isError = true;
		}
	}
		
	if($isError===true)
		$messageContext->addMessage("ERROR", "Errore durante la pulizia dei details");
	else {
//		echo "<b>DETAILS DOPO:</b> "; print_r($_SESSION["WI400_DETAILS"]); echo "<br><br>";
		$messageContext->addMessage("SUCCESS", "Details puliti con successo");
	}

	// Messaggio finale
	if($messageContext->getSeverity()!="ERROR")
		$messageContext->addMessage("SUCCESS", "Pulizia della sessione avvenuta con successo");
	else
		$messageContext->addMessage("ERROR", "Errore durante la pulizia della sessione");

?>