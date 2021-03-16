<?php
	
	if(isset($_REQUEST['IDLIST'])){
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['IDLIST']);
	}
	else {
		echo "ERRORE GRAVE";
		exit();
	}
	
//	$idList = $wi400List->getIdList();
	
	$sortList = $wi400List->getSortList();
	
	$keysList = $sortList->getSortKeys();		// Chiavi di riga (campi WHERE dell'UPDATE)
	$field = $sortList->getSorter();			// Campo sequenza da modificare (campo SET dell'UPDATE)
	$table = $sortList->getSortTable();			// Tabella AS400 in esame (elemento FROM dell'UPDATE)
	$mask = $sortList->getSortMask();
	$where_list = $sortList->getSortWhere();
	
//	echo "CAMPO: $field - TABELLA: $table - CHIAVI:<pre>"; print_r($keysList); echo "</pre>";

	if($actionContext->getForm() == "SAVE") {
		$keyOrder = explode("|", $_REQUEST["COLUMN_ORDER"]);	// Elenco ordinato delle righe (costituito dalle chiavi di riga)
//		echo "KEY ORDER:<pre>"; print_r($keyOrder); echo "</pre>";

//		$orderCount = 1;
		foreach($keyOrder as $s => $keyValues) {
//			$key_vals = explode(";", $keyValues);
			$key_vals = explode("§", $keyValues);
			
			foreach($keysList as $key => $val) {
				$keysName[$val] = $key_vals[$key];
			}
//			echo "KEYS:<pre>"; print_r($keysName); echo "</pre>";

			$stmtupdate  = $db->prepare("UPDATE", $table, $keysName, array($field));
			
//			echo "SEQUEN: ".($s+1)."<br>";
			
//			$db->execute($stmtupdate, array($orderCount));
			$indice = $s + 1;
			if ($mask !="") {
				$mask2 = str_replace("##FIELD##", $indice, $mask);
				eval ("\$indice=$mask2;");
			}
			$db->execute($stmtupdate, array($indice));
			
//			$orderCount++;
		}
	}
	