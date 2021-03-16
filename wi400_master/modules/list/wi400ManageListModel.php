<?php

	if ($actionContext->getForm() == "RESTORE"){
	
		$idList = $_REQUEST["IDLIST"];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$filename = wi400File::getUserFile("list", $wi400List->getConfigFileName().".lst");
		//if (file_exists($filename)){
		//if (file_exists($filename)){
			//unlink($filename);
			wi400ConfigManager::deleteConfig("list", $wi400List->getConfigFileName(),"",$filename);
		//}
		wi400Session::delete(wi400Session::$_TYPE_LIST, $idList);
		
	} else if ($actionContext->getForm() == "SAVE"){
		$idList = $_REQUEST["IDLIST"];
		// Lista corrente	
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
					
		$columnOrder = explode("|",$_REQUEST["COLUMN_ORDER"]);
		$columnFix = explode("|",$_REQUEST["COLUMNS_FIX"]);
		$misure = array();
		if (isset($_REQUEST["MISURE"])) {
			$misure = explode("|", $_REQUEST["MISURE"]);
		}

		// ************************************************************************
		// Raggruppamenti
		// ************************************************************************
		$colsGroups = array();
		$hasGroup = false;
		foreach ($columnOrder as $columnKey) {
			$wi400Column = $wi400List->getCol($columnKey);
			if (is_object($wi400Column)) {
				if ($wi400Column->getGroup() != ""){
					$hasGroup = true;
					$colsGroups[$wi400Column->getGroup()][$wi400Column->getKey()] = $wi400Column->getKey();
				}
			}
		}

		$columnsArray = array();
		foreach ($columnOrder as $columnKey) {
			$wi400Column = $wi400List->getCol($columnKey);
			if (is_object($wi400Column)) {
				if ($wi400Column->getGroup() != ""){
					$colsList = $colsGroups[$wi400Column->getGroup()];
					foreach ($colsList as $ck) {
						if (array_search($ck,$columnsArray) === false){
							$columnsArray[] = $ck;
						}
					}
				}else{
					if (array_search($columnKey,$columnsArray) === false){
						$columnsArray[] = $columnKey;
					}
				}
			}
		}

		$wi400ConfigList = new wi400List();
		$wi400ConfigList->setColumnsOrder($columnsArray);
		$wi400ConfigList->setColumnsWidth($misure);
		$wi400ConfigList->setColumnsFix($columnFix);
		$wi400ConfigList->setPageRows($_REQUEST["NUM_ROWS"]);
		
		// BLOCCO FILTRI TESTATA E BLOCK SCROLL
		$old_filtri_testata = $wi400List->getShowHeadFilter();
		$old_block_header_scroll = $wi400List->getBlockScrollHeader();
		$old_hide_list = $wi400List->getStatus();
		$now_filtri_testata = False;
		$now_block_header_scroll = False;
		if (isset($_REQUEST["FILTRI_TESTATA"])) {
			$now_filtri_testata=True;
		}
		if (isset($_REQUEST["BLOCK_HEADER_SCROLL"])) {
			$now_block_header_scroll=True;
		}
		if ($now_block_header_scroll != $old_block_header_scroll) {
			//$res_del=True;
		}
		
		$res_del = false;
		if ($now_filtri_testata != $old_filtri_testata) {
			$res_del =True;
		}
		$hide_list = "OPEN_CONFIG";
		if(isset($_REQUEST['NASCONDI_LISTA'])) {
			$hide_list = "CLOSE_CONFIG";
		}
		if($old_hide_list != $hide_list) $res_del = true;
		
		$wi400List->setBlockScrollHeader($now_block_header_scroll);
		$wi400List->setShowHeadFilter($now_filtri_testata);
		$wi400List->setStatus($hide_list);
		$wi400ConfigList->setBlockScrollHeader($now_block_header_scroll);
		$wi400ConfigList->setShowHeadFilter($now_filtri_testata);
		$wi400ConfigList->setStatus($hide_list);
		// FINE BLOCCO

		$defaultFilter = "";
		if (isset($_REQUEST["DEFAULT_FILTER"])){
			$defaultFilter = $_REQUEST["DEFAULT_FILTER"];
		}
		if (isset($_REQUEST["DELETE_FILTER"])){
			$defaultFilter = "";
		}
		
		$wi400ConfigList->setDefaultFilter($defaultFilter);
		
		// Ordine della lista
		$wi400ConfigList->setOrder($wi400List->getOrder());
		
		// SALVATAGGIO SU FILE
		if ($columnOrder[0]===""){
			$messageContext->addMessage("ERROR",_t('LIST_COLUMN_SEL'));
			$actionContext->onError("MANAGELIST","DEFAULT");
		}
		else {
			if(isset($_REQUEST["DELETE_PERS_CONFIG"])){
				// Scansione delle cartelle utenti in $data_path per trovare e cancellare le impostazioni di lista personalizzate
				wi400ConfigManager::deleteMultiConfig("list", $wi400List->getConfigFileName(), "");
				/*$dir_handle = opendir($data_path);
				// Recupero dei file della directory
				while(($file = readdir($dir_handle))!==false) {
//		    		echo "FILE: $file<br>";
					$path = $data_path.$file;
					 
					if($file!="." && $file!=".." && $file!="CVS" && !is_file($path)) {
//						echo "FILE: $file<br>";
						 
						$file_path = $path."/list/".$wi400List->getConfigFileName().".lst";
			
						if(file_exists($file_path)) {
//							echo "REMOVE FILE: $file_path<br>";
							unlink($file_path);
						}
					}
				}*/
			
				closedir($dir_handle);
//die();
			}			
		
			$filename = wi400File::getUserFile("list", $wi400List->getConfigFileName().".lst");
			/*$handle = fopen($filename, "w");
			   
			if (flock($handle, LOCK_EX)){
		    	$putfile = True;
		    } else {
		    	$putfile = False;
		        fclose($handle);
		    }
		    if ($putfile){
	    		$contents = serialize($wi400ConfigList);
			    fputs($handle, $contents);
	    		flock($handle, LOCK_UN);
			    fclose($handle);
		    }*/
			wi400ConfigManager::saveConfig("list", $wi400List->getConfigFileName(),"", $filename, $wi400ConfigList);
		    
		    if(isset($_REQUEST["DELETE_MASTER_CONFIG"])){
		    	// Eliminazione della Configurazione Master di Default
		    	$filename = $settings['data_path']."list_master/MASTER_".$wi400List->getConfigFileName().".lst";
		    	wi400ConfigManager::deleteConfig("list_master", $wi400List->getConfigFileName(),"", $filename, $wi400ConfigList);
		    	/*if (file_exists($filename)){
		    		unlink($filename);
		    	}*/
//		    	wi400Session::delete(wi400Session::$_TYPE_LIST, $idList);
		    }		    
		    else if (isset($_REQUEST["MASTER_CONFIG"])){
		    	// Salvataggio della Configurazione Master di Default
		    	$filename = $settings['data_path']."list_master/MASTER_".$wi400List->getConfigFileName().".lst";
		    	
		    	/*$dir = dirname($filename);
		    	if(!file_exists($dir)) {
		    		wi400_mkdir($dir, 777, true);
		    	}*/
		    	wi400ConfigManager::saveConfig("list_master", $wi400List->getConfigFileName(),"", $filename, $wi400ConfigList);
		    	
		    	/*$handle = fopen($filename, "w");
		    	
		    	if (flock($handle, LOCK_EX)){
		    		$putfile = True;
		    	} else {
		    		$putfile = False;
		    		fclose($handle);
		    	}
		    	if ($putfile){
		    		$contents = serialize($wi400ConfigList);
		    		fputs($handle, $contents);
		    		flock($handle, LOCK_UN);
		    		fclose($handle);
		    	}*/
		    }
		    
		    // SALVATAGGIO PER SESSIONE CORRENTE
			$wi400List->setColumnsOrder($columnsArray);
			$wi400List->setColumnsWidth($misure);
			$wi400List->setColumnsFix($columnFix);
			$wi400List->setPageRows($_REQUEST["NUM_ROWS"]);
			
			// FINE BLOCCO
			$wi400List->setDefaultFilter($defaultFilter);
			if ($defaultFilter != ""){
				$wi400List->setCurrentFilter($defaultFilter);
				$res_del=True;
			}
			
			// CANCELLAZIONE FILTRO DA FILE
			if (isset($_REQUEST["DELETE_FILTER"])){
				$res_del = $wi400List->removeCustomFilter($_REQUEST["DEFAULT_FILTER"]);
				
				if($res_del===false) {
					$messageContext->addMessage("ERROR",_t("Utente non abilitato ad eliminare il filtro generico")." {$_REQUEST["DEFAULT_FILTER"]}");
					$actionContext->onError("MANAGELIST","DEFAULT");
				}
			}
			
			//$_SESSION[$idList] = $wi400List;
			wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
		}
		if (isset($_REQUEST['AJAX_REQUEST'])) {
			die();
		}
	}
?>