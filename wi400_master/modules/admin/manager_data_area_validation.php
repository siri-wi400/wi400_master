<?php

	if(in_array($actionContext->getForm(), array("DETAIL", "NEW_DETAIL"))) {
		validation_detail();
	}
	
	function validation_detail() {
		global $messageContext;
		global $db, $routine_path;
		
		require_once $routine_path."/os400/wi400Os400Object.cls.php";
		
		$sql = "select * from ZDTATABE a where DTANAM='".$_POST['DTANAM']."'";
//		echo "SQL: $sql<br>";
		
		$result = $db->singleQuery($sql);
		
		if($row = $db->fetch_array($result)) {
			$messageContext->addMessage("ERROR", "Data Area già presente", "DTANAM", true);
		}
		
		$list = new wi400Os400Object("*DTAARA", $_POST['DTALIB'], $_POST['DTANAM']);
		$list->getList();
		
		if($obj_read = $list->getEntry()) {
			// ESISTE
		}
		else {
			$messageContext->addMessage("ERROR", "La Data Area selezionata non esiste in questa Libreria", "DTALIB", true);
		}
		
		$list = new wi400Os400Object("*FILE", $_POST['DTADSL'], $_POST['DTADS']);
		$list->getList();
		
		if($obj_read = $list->getEntry()) {
			// ESISTE
		}
		else {
			$messageContext->addMessage("ERROR", "La DS selezionata non esiste in questa Libreria", "DTADSL", true);
		}
	}