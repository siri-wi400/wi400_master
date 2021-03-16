<?php

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	if($form == "SAVE_POSITION") {
		//showArray($_REQUEST['POSIZIONI']);
		//echo "totcol: ".$_REQUEST['TOTCOL'];
		
		$succ = true;
		$file = "ZWIDGUSR";
		$fields = getDS($file);
		$where = array("WIDUSR" => "?",
				"WIDAZI" => "?",
				"WIDPRG" => "?");
		$stmt_pos = $db->prepare("UPDATE", $file, $where, array("WIDRIG"));
		
		foreach($_REQUEST['POSIZIONI'] as $pos => $dati) {
			$rs = $db->execute($stmt_pos, array($dati['riga'], $dati['user'], $dati['azione'], $dati['progressivo']));
			if(!$rs) $succ = false;
		}
		
		echo $succ ? "true" : "";
		die();
	}
	
	if($form == "DEFAULT") {
		$history->addCurrent();
	}