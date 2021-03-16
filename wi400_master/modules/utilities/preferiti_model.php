<?php

$preferitiArray = array();

if ($actionContext->getForm() != "DEFAULT"){
	
	$preferitiFile = wi400File::getUserFile("config", "preferiti.txt");
	
   	// Caricamento array da file serializzato
	/*$handle = fopen($preferitiFile, "r");
	$contents = fread($handle, filesize($preferitiFile));
	fclose($handle);
	$preferitiArray = unserialize($contents);*/
	$preferitiArray = wi400ConfigManager::readConfig('preferiti', 'preferiti', '',$preferitiFile);

	if ($actionContext->getForm() == "ADD"){
	
		if (isset($_GET["ACTION"]) && !isset($preferitiArray[$_GET["ACTION"]])){
			
		
			$actionRec = rtvAzione($_GET["ACTION"]);
			$actionObj = array();
			$actionObj["ACTION"] = $_GET["ACTION"];
			$actionObj["LABEL"]  = $actionRec["DESCRIZIONE"];
			
			if (isset($_GET["FORM"])){
				$actionObj["FORM"] = $_GET["FORM"];
			}
			$preferitiArray[$_GET["ACTION"]] = $actionObj;
		}
		
	} else if ($actionContext->getForm() == "REMOVE"){
		
		if (isset($_GET["REMOVE"])){
			unset($preferitiArray[$_GET["REMOVE"]]);
		}
		
	}
	
	// Salvataggio array su file serializzato
	wi400ConfigManager::saveConfig('preferiti', 'preferiti', '', $preferitiFile, $preferitiArray);
	/*$handle = fopen($preferitiFile, "w");
	$contents = serialize($preferitiArray);
    fwrite($handle, $contents);
	fclose($handle);*/

}