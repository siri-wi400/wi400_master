<?php

	
	
	$ordersType = array();
	$ordersType["DESC"] = "Discendente";
	$ordersType["ASC"] = "Ascendente";

	$ordersSubFile = new wi400Subfile($db, "ORDERS_LIST");
	$ordersSubFile->setConfigFileName("ORDERS_LIST");
	$ordersSubFile->setModulo("list");
	$ordersSubFile->setSql("*AUTOBODY");
	$ordersSubFile->addParameter('ORDLIST',$_REQUEST["ORDLIST"],true);
	
	$parentList = getList($_REQUEST["ORDLIST"]);
	$parentCols = $parentList->getCols();
	
	
	if ($actionContext->getForm() == "ADD_LEVEL"){

		$decodeCol = array();
		foreach ($parentCols as $parentCol){
			if ($parentCol->getSortable()){
				$colGroup = "";
				if ($parentCol->getGroup() != "") $colGroup = " (".$parentList->getGroupDescription($parentCol->getGroup()).")";
					
				$decodeCol[$parentCol->getKey()] = str_replace("<br>"," ",$parentCol->getDescription().$colGroup);
			}
		}
		$field = array("ID", "COLONNA", "TYPE");
		$stmtinsert = $db->prepare("INSERT", $ordersSubFile->getFullTableName(), null, $field);
		$campi = array($_REQUEST["COLONNA"], $decodeCol[$_REQUEST["COLONNA"]], $_REQUEST["TYPE"]);
		$db->execute($stmtinsert, $campi);
		
	}else if ($actionContext->getForm() == "DELETE_LEVEL"){
		
		$colKey = getListKey("ORDERS_LEVELS");
		$stmtdelete = $db->prepare("DELETE", $ordersSubFile->getFullTableName(), array("ID"), null);
		$result = $db->execute($stmtdelete, array($colKey));

	}else if ($actionContext->getForm() == "SAVE"){
		
		$sqlOrder = "SELECT ID, TYPE FROM ".$ordersSubFile->getFullTableName();
		$resultOrder= $db->query($sqlOrder, True , 0);
		$ordersArray = array();
		while($rowOrder = $db->fetch_array($resultOrder)){
			$ordersArray[$rowOrder["ID"]] = $rowOrder["TYPE"];
		}
		$parentList->setOrder($ordersArray);
		saveList($_REQUEST["ORDLIST"], $parentList);
		

	}else if ($actionContext->getForm() == "CANCEL"){
	
		$parentList->setOrder(array());
		saveList($_REQUEST["ORDLIST"], $parentList);
		
	}

	
	$orders = array();
	if ($actionContext->getForm() == "DEFAULT"){
		subfileDelete("ORDERS_LIST");
	
		if (is_array($parentList->getOrder())) $orders = $parentList->getOrder();
	
	}else{
	
		$sqlOrder = "SELECT ID, TYPE FROM ".$ordersSubFile->getFullTableName();
		$resultOrder= $db->query($sqlOrder, True , 0);
		while($rowOrder = $db->fetch_array($resultOrder)){
			$orders[$rowOrder["ID"]] = $rowOrder["TYPE"];
		}
	
	}
	
	$ordersCol = array();
	foreach ($parentCols as $parentCol){
		if ($parentCol->getSortable() && !isset($orders[$parentCol->getKey()])){
			$colGroup = "";
			if ($parentCol->getGroup() != "") $colGroup = " (".$parentList->getGroupDescription($parentCol->getGroup()).")";
			$ordersCol[$parentCol->getKey()] =  str_replace("<br>"," ",$parentCol->getDescription().$colGroup);
		}
	}



