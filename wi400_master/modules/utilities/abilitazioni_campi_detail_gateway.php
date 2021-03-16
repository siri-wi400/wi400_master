<?php
	global $actionContext;
	
	if($actionContext->getGateway() == "UTENTI") {
		$azi = "ABILITAZIONI_CAMPI_DETAIL";
		
		$virtualList = new wi400List($azi."_AZIONI");
		$sa = array();
		$sa[$_REQUEST['AZI']."|"] = "";
		$virtualList->setSelectionArray($sa);
		$virtualList->addKey("WIDAZI");
		$virtualList->addKey("DESCRIZIONE");
		saveList($azi."_AZIONI", $virtualList);
		
		$virtualList = new wi400List($azi."_DETAIL");
		$sa = array();
		$sa["||P"] = "";
		$virtualList->setSelectionArray($sa);
		$virtualList->addKey("WIDID");
		$virtualList->addKey("WIDDED");
		$virtualList->addKey("WIDDOL");
		saveList($azi."_DETAIL", $virtualList);
	}
	if($actionContext->getGateway() == "GIWI400") {
	    $azi = "ABILITAZIONI_CAMPI_DETAIL";
	    $keyArray = getListKeyArray("CONSOLE_GIWI400_FORM_LIST");
	    $formato = $keyArray['COL0'];
	    $file = $keyArray['COL1'];
	    $libreria = $keyArray['COL2'];
	    // Creazione Liste Virtuali
	    $virtualList = new wi400List($azi."_AZIONI");
	    $sa = array();
	    $key = "CONSOLE_GIWI400|";
	    $sa[$key] = "";
	    $virtualList->setSelectionArray($sa);
	    $virtualList->addKey("WIDAZI");
	    $virtualList->addKey("DESCRIZIONE");
	    saveList($azi."_AZIONI", $virtualList);
	    // Seconda Lista
	    $virtualList = new wi400List($azi."_DETAIL");
	    $sa = array();
	    $key = "GIWI400_TABLE_".$libreria."_".$file."_".$formato."||L";
	    $sa[$key] = "";
	    $virtualList->setSelectionArray($sa);
	    $virtualList->addKey("WIDID");
	    $virtualList->addKey("WIDDED");
	    $virtualList->addKey("WIDDOL");
	    saveList($azi."_DETAIL", $virtualList);
	    
	}else if($actionContext->getGateway() == "FROM_DEVELOPER") {
		$azi = "ABILITAZIONI_CAMPI_DETAIL";
		
		$keyList = getListKeyArray("MAPPING_DETAIL_LIST_LIST");
		//showArray($keyList);
		//die("alberto");
		
		// Creazione Liste Virtuali
		$virtualList = new wi400List($azi."_AZIONI");
		$sa = array();
		$key = $keyList['AZIONE']."|";
		$sa[$key] = "";
		$virtualList->setSelectionArray($sa);
		$virtualList->addKey("WIDAZI");
		$virtualList->addKey("DESCRIZIONE");
		saveList($azi."_AZIONI", $virtualList);
		
		$type = 'D';
		if($key['TYPE'] == 'LIST') $type = 'L';
		// Seconda Lista
		$virtualList = new wi400List($azi."_DETAIL");
		$sa = array();
		$key = $keyList['OBJ']."||".$type;
		$sa[$key] = "";
		$virtualList->setSelectionArray($sa);
		$virtualList->addKey("WIDID");
		$virtualList->addKey("WIDDED");
		$virtualList->addKey("WIDDOL");
		saveList($azi."_DETAIL", $virtualList);
	}