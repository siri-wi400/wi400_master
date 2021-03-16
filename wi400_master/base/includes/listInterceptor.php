<?php
	if (isset($_REQUEST['IDLIST'])){
		$wi400List = new wi400List();
		
		$idList = "";
		$myLists = array();
		if (isset($_GET['IDLIST'])){
			$idList = $_GET['IDLIST'];
		}else if (isset($_POST['IDLIST'])){
			$idList = $_POST['IDLIST'];
		}
		if (!isset($_REQUEST['IDLISTS'])) {
			$myLists[] = $idList;
		} else {
			$myLists = $_REQUEST['IDLISTS'];
			// LZ Patch: A volte IDLISTS arriva serializzato
			if (is_serialized($myLists)) $myLists= unserialize($myLists);
		}
		if (!in_array($idList, $myLists)) {
			$myLists[]=$idList;
		}
		$myLists = array_unique($myLists);
		foreach ($myLists as $key => $idList) {
		if (existList($idList)){
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
			$pageRows    = $wi400List->getPageRows();
			$selection   = $wi400List->getSelection();
			$rowsSelectionArray = $wi400List->getSelectionArray();
			if (isset($_REQUEST[$idList."_STATUS"]) && $wi400List->getStatus() != "CLOSE_CONFIG") {
				$wi400List->setStatus($_REQUEST[$idList."_STATUS"]);
			}
			// Reperisco le colonne di INPUT
			$inputCol=array();
			foreach($wi400List->getCols() as $col){
					//if (method_exists($col, "getInput")) {
						$colField = $col->getInput();
						if (is_object($colField)){
								$inputCol[$col->getKey()] = $col;
						}
					//}
			}
			//
			for ($rowCounter = 0; $rowCounter < $pageRows; $rowCounter++){
				if (isset($_REQUEST[$idList.'-'.$rowCounter])){
					$rowKey = $_REQUEST[$idList.'-'.$rowCounter];
					if(isset($_REQUEST[$idList.'-'.$rowCounter.'-checkbox'])){
						// Selezione
						if ($selection == "SINGLE"){
							$rowsSelectionArray = array();
						}
						$rowsValueArray = array();
						//foreach($wi400List->getCols() as $col){
						foreach($inputCol as $col){
							if (isset($_REQUEST[$idList.'-'.$rowCounter.'-'.$col->getKey()])){
								$colField = $col->getInput();
								$requestValue = $_REQUEST[$idList.'-'.$rowCounter.'-'.$col->getKey()];
								if ($colField->getType() == "INPUT_TEXT" && method_exists($colField, "getDecimals") && $colField->getDecimals()>0){
									$requestValue = doubleViewToModel($requestValue);
								}
								$rowsValueArray[$col->getKey()] = $requestValue;
							}
						}
						// Se previsto riporto tutti i valori nel formato previsto dal MODEL
						if ($wi400List->getNormalizeData()==True) {
							$rowsValueArray = wi400List::viewToModelRow($wi400List, $rowsValueArray);
						}
						$rowsSelectionArray[$rowKey] = $rowsValueArray;
					}else{
						// Deselezione
						unset($rowsSelectionArray[$rowKey]);
					}
				}
			}
			
			// Remove selection request parameter
			if (isset($_GET["REMOVE_SELECTION"]) && $_GET["REMOVE_SELECTION"] == true){
				$rowsSelectionArray = array();
			}
			// Ignora selezioni
			if (!isset($_GET["IGNORE_SELECTION"])){
				$wi400List->setSelectionArray($rowsSelectionArray);
			}
			
			// ************************************************************************
			// CARICAMENTO FILTRI PERSONALIZZATI
			// ************************************************************************
			$filterSave = array();
			$customFilters = $wi400List->getCustomFilters();
			
			if (isset($_POST[$wi400List->getIdList()."_CUSTOM_FILTER"]) 
								&& $_POST[$wi400List->getIdList()."_CUSTOM_FILTER"] != $wi400List->getCurrentFilter()){
	
				if (isset($customFilters[$_POST[$wi400List->getIdList()."_CUSTOM_FILTER"]])){
					$wi400List->setCurrentFilter($_POST[$wi400List->getIdList()."_CUSTOM_FILTER"]);
					
					$loadedFilters = $customFilters[$_POST[$wi400List->getIdList()."_CUSTOM_FILTER"]];
					$wi400List->setCurrentFilter($_POST[$wi400List->getIdList()."_CUSTOM_FILTER"]);
					
					if (isset($loadedFilters["FILTER_LIST_CONFIG"])){
						$loadedListConfig = $loadedFilters["FILTER_LIST_CONFIG"];
						$wi400List->setColumnsOrder($loadedListConfig->getColumnsOrder());
						$wi400List->setOrder($loadedListConfig->getOrder());
					}
				}else{
					$wi400List->setCurrentFilter("");
					
					// CARICAMENTO LISTA DA FILE
					$filename = wi400File::getUserFile("list", $wi400List->getConfigFileName().".lst");
					$wi400ListFile= wi400ConfigManager::readConfig('list', $wi400List->getConfigFileName(), '',$filename);
					if ($wi400ListFile) {
						$wi400List->setColumnsOrder($wi400ListFile->getColumnsOrder());
						$wi400List->setOrder($wi400ListFile->getOrder());
					}
			    	/*if (file_exists($filename)) {
						$handle = fopen($filename, "r");
						$contents = fread($handle, filesize($filename));
					    fclose($handle);
					    $wi400ListFile = unserialize($contents);
					    if ($wi400ListFile){
					    	$wi400List->setColumnsOrder($wi400ListFile->getColumnsOrder());
					    	$wi400List->setOrder($wi400ListFile->getOrder());
					    }
					}*/
				}
			}
			
			if (isset($_POST[$wi400List->getIdList()."_SEARCH"])){
				// Cancello filtro personalizzato
				$wi400List->setCurrentFilter("");
			}
			// ************************************************************************
	
			
			// ************************************************************************
			// RICERCA AVANZATA E RICERCA VELOCE
			// ************************************************************************
			// Se in request POST trovo un filtro veloce gestisco la pulizia per il multifiltro
			$cleanFastFilter = false;
			foreach ($wi400List->getFilters() as $filter){
				if ($filter->getFast() && isset($_POST["FAST_FILTER_".$filter->getId()])){
					$cleanFastFilter = true;
				}
			}
	
			foreach ($wi400List->getFilters() as $filter){
				if ($filter->getFast() 
						|| isset($_POST[$wi400List->getIdList()."_SEARCH"])){
								
					if (isset($_POST[$wi400List->getIdList()."_SEARCH"]) 
							&& $_POST[$wi400List->getIdList()."_SEARCH"] == "REMOVE" && !$filter->getFast()){
								
						// Cancellazioni filtri avanzati
						$filter->setOption("");
						$filter->setValue("");
						$wi400List->addFilter($filter);
						
					}else{
						
						if (isset($_POST["FAST_FILTER_".$filter->getId()])){
							$valueToSearch = $_POST["FAST_FILTER_".$filter->getId()];
							
							if (!is_array($valueToSearch)){
								$valueToSearch = trim($valueToSearch);
								if ($filter->getCaseSensitive()===false){
									$valueToSearch = strtoupper($valueToSearch);
								}
								
								/* N.B. va in conflitto con la funzione where_text_condition (common.php) per la creazione del where con filtro
								 * il sistema faceva 2 volte il sanitize della stringa 
								*/
								/*if ($filter->getType() == "STRING" ){
									$valueToSearch = sanitize_sql_string($valueToSearch);
								}*/
							}
							$option = "";
							if (isset($_POST["FAST_FILTER_".$filter->getId()."_OPTION"])){
								$option = $_POST["FAST_FILTER_".$filter->getId()."_OPTION"];
								// X Salvataggio filtro
								//if (!$filter->getFast()){
									$filterSave[$filter->getId()."_OPTION"] = $option;
								//}
							}
							$filter->setOption($option);
							$filter->setValue($valueToSearch);
							$wi400List->addFilter($filter);
							
							// X Salvataggio filtro
							//if (!$filter->getFast()){
								$filterSave[$filter->getId()] = $valueToSearch;
							//}
							
						}else if ($filter->getType() == "CHECK_NUMERIC" || $filter->getType() == "CHECK_STRING" ) {
							$filter->setOption("");
							$filter->setValue("");
							$wi400List->addFilter($filter);
							
							// X Salvataggio filtro
							if (!$filter->getFast()){
								$filterSave[$filter->getId()] = "";
							}
							
						}else if($filter->getType() == "SELECT"){
							
							$valueToSearch = $_POST["FAST_FILTER_".$filter->getId()];
							$filter->setValue($valueToSearch);
							$wi400List->addFilter($filter);
							
						}else if ($filter->getFast() && $cleanFastFilter){
							$filter->setOption("");
							$filter->setValue("");
							$wi400List->addFilter($filter);
						}
					}
				}
			}
			// ************************************************************************
			
			// *************************************************************
			// SALVATAGGIO FILTRI SU FILE
			// *************************************************************
			if (isset($_POST[$wi400List->getIdList()."_SEARCH"]) 
					&& $_POST[$wi400List->getIdList()."_SEARCH"] == "SAVE"){
						if (isset($_POST[$wi400List->getIdList()."_FILTER_NAME"])){
							$customFilterName = $_POST[$wi400List->getIdList()."_FILTER_NAME"];
							
							$filterListConfig = "";
							if (isset($_POST[$wi400List->getIdList()."_FILTER_CONFIG"]) 
								&& $_POST[$wi400List->getIdList()."_FILTER_CONFIG"] !== ""){
								$filterListConfig = new wi400List();
								
								$filterListConfig->setOrder($wi400List->getOrder());
								$filterListConfig->setColumnsOrder($wi400List->getColumnsOrder());
								$filterListConfig->setPageRows($wi400List->getPageRows());
								$filterListConfig->setDefaultFilter($customFilterName);
							}
							
							$filterGen = "";
							if (isset($_POST[$wi400List->getIdList()."_FILTER_GEN"]) 
								&& $_POST[$wi400List->getIdList()."_FILTER_GEN"] != ""){
								
								$filterGen = $_POST[$wi400List->getIdList()."_FILTER_GEN"];
							}
							
//							print_log("ID LIST: ".$wi400List->getIdList());
//							print_log("FILTER CONFIG: ".$_POST[$wi400List->getIdList()."_FILTER_CONFIG"]);
//							print_log("POST FILTER GEN: ".$_POST[$wi400List->getIdList()."_FILTER_GEN"]);
//							print_log("FILTER GEN: $filterGen");
							
							$wi400List->addCustomFilter($customFilterName, $filterSave,$filterListConfig,$filterGen);
							$wi400List->setCurrentFilter($customFilterName);
						
						}
			}
			// ************************************************************************
			
			// Salvataggio lista in sessione
			wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);			
			//$_SESSION[$idList] = $wi400List;
		}
	}
	}
	
	function updateSelectionArray($idList) {
	if (existList($idList)){
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$pageRows    = $wi400List->getPageRows();
		$selection   = $wi400List->getSelection();
		$rowsSelectionArray = $wi400List->getSelectionArray();
			
		for ($rowCounter = 0; $rowCounter < $pageRows; $rowCounter++){
			if (isset($_REQUEST[$idList.'-'.$rowCounter])){
				$rowKey = $_REQUEST[$idList.'-'.$rowCounter];
				if(isset($_REQUEST[$idList.'-'.$rowCounter.'-checkbox'])){
					// Selezione
					if ($selection == "SINGLE"){
						$rowsSelectionArray = array();
					}
	
					$rowsValueArray = array();
					foreach($wi400List->getCols() as $col){
						if (isset($_REQUEST[$idList.'-'.$rowCounter.'-'.$col->getKey()])){
							$colField = $col->getInput();
							$requestValue = $_REQUEST[$idList.'-'.$rowCounter.'-'.$col->getKey()];
							if ($colField->getType() == "INPUT_TEXT" && $colField->getDecimals()>0){
								$requestValue = doubleViewToModel($requestValue);
							}
							$rowsValueArray[$col->getKey()] = $requestValue;
						}
					}
					$rowsSelectionArray[$rowKey] = $rowsValueArray;
				}else{
					// Deselezione
					unset($rowsSelectionArray[$rowKey]);
				}
			}
		}
			
		// Remove selection request parameter
		if (isset($_GET["REMOVE_SELECTION"]) && $_GET["REMOVE_SELECTION"] == true){
			$rowsSelectionArray = array();
		}
		// Ignora selezioni
		if (!isset($_GET["IGNORE_SELECTION"])){
			$wi400List->setSelectionArray($rowsSelectionArray);
		}
			
	
		// Salvataggio lista in sessione
		wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
		//$_SESSION[$idList] = $wi400List;
	}
	}
	
?>