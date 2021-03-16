<?php

	// Esportazione di una lista in formato XML
	function exportXML($export, $idList, $exportTarget){
		global $db;
/*		
		$exportFormat = $_REQUEST['FORMAT'];
		$exportTarget = $_REQUEST['TARGET'];
		$idList       = $_REQUEST['IDLIST'];
*/			
//		echo "FILTERS: ".$_REQUEST['FILTERS']."<br>";
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		$filename =  date("YmdHis")."_".$wi400List->getIdList().".xml";
		$temp = "export";
		$TypeImage = "xml.png";
		
		$export->setDatiExport($filename, $temp, $TypeImage);
		
	    if(!$handle = fopen(wi400File::getUserFile($temp,$filename), 'w')) {
	         echo "Non si riesce ad aprire il file ($filename)";
	         exit;
	    }
    
		/* Creazione del documento DOM per creare script XML */
		$dom = new DomDocument('1.0');
		
		$event = $dom->appendChild($dom->createElement('event'));
		$field_name = $dom->createAttribute('listDesc'); 
		$event->appendChild($field_name);
		$name = $dom->createTextNode($idList);
		$field_name->appendChild($name);
		
		$attributes = $event->appendChild($dom->createElement('attributes'));
		
		$attribute = $attributes->appendChild($dom->createElement('attribute'));
		$field_name = $dom->createAttribute('id'); 
		$attribute->appendChild($field_name);
		$name = $dom->createTextNode('sysDate');
		$field_name->appendChild($name);
		$field_name = $dom->createAttribute('value'); 
		$attribute->appendChild($field_name);
		$name = $dom->createTextNode(date("Y-m-d-H.i.s"));
		$field_name->appendChild($name);
		
		$attribute = $attributes->appendChild($dom->createElement('attribute'));
		$field_name = $dom->createAttribute('id'); 
		$attribute->appendChild($field_name);
		$name = $dom->createTextNode('user');
		$field_name->appendChild($name);
		$field_name = $dom->createAttribute('value'); 
		$attribute->appendChild($field_name); 
		$name = $dom->createTextNode($_SESSION['user']);
		$field_name->appendChild($name);
		
		// Filtri di selezione
		if(isset($_REQUEST['FILTERS']) && $_REQUEST['FILTERS']=="FILTERS") {
			$idDetails = $export->getIdDetails();
			
			if(!empty($idDetails)) {
				$parametersList = $attributes->appendChild($dom->createElement('parametersList'));
				
				foreach($idDetails as $idDetail) {
					$detailFields = wi400Detail::getDetailFields($idDetail);
			
					foreach($detailFields as $idField => $fieldObj){
						$label = $fieldObj->getLabel(); // Etichetta
						$label = prepare_string($label);
						
						$value = $fieldObj->getValue(); // Valore
						$value = prepare_string($value);
						
//						echo $label." - ".$value."<br>";
						
						$attribute = $parametersList->appendChild($dom->createElement('attribute'));
						$field_name = $dom->createAttribute('id');
						$attribute->appendChild($field_name);
						$name = $dom->createTextNode($label);
						$field_name->appendChild($name);
						
						$field_name = $dom->createAttribute('value');
						$attribute->appendChild($field_name);
						$name = $dom->createTextNode($value);
						$field_name->appendChild($name);
					}
				}
			}
		}
			
		$headerList = $attributes->appendChild($dom->createElement('headerList'));
		
		$columnsKeyArray = $wi400List->getColumnsOrder();
		
		foreach($columnsKeyArray as $columnKey) {
			$columnKey = prepare_string($columnKey);
			
			$attribute = $headerList->appendChild($dom->createElement('attribute'));
			$field_name = $dom->createAttribute('id');
			$attribute->appendChild($field_name);
			$name = $dom->createTextNode($columnKey);
			$field_name->appendChild($name);
			
			$wi400Column = $wi400List->getCol($columnKey);
			if($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()){
				$columnDesc = $wi400Column->getDescription();
			}
			
			$columnDesc = prepare_string($columnDesc);
			
			$field_name = $dom->createAttribute('value');
			$attribute->appendChild($field_name);
			$name = $dom->createTextNode($columnDesc);
			$field_name->appendChild($name);
		}
		
		$datasetList = $attributes->appendChild($dom->createElement('datasetList'));
		
		$sql_list = $export->get_query();
		
		if($exportTarget == "PAGE")
			$resultSet = $db->query($sql_list, true, $wi400List->getPageRows());
		else
			$resultSet = $db->query($sql_list, False, 0);
			
		if($exportTarget=="PAGE") {
			// Posizionamento su record
			if ($export->getStartFrom()>0) 
				$db->fetch_array($resultSet,$export->getStartFrom());
		}
		else if($exportTarget=="SELECTED") {
			$rowsSelectionArray = $wi400List->getSelectionArray();
		}
		
		$i=0;
		while($row = $db->fetch_array($resultSet)) { 
			if ($exportTarget == "PAGE" && $i == $wi400List->getPageRows())
				break;
			else if ($exportTarget == "SELECTED") {
				if ($i < count($rowsSelectionArray)) {
					$keysRow = "";
					$keyValue = "";
					$isFirst = true;
					foreach ($wi400List->getKeys() as $key => $keyColumn) {
						if (isset($row[$key]))
							$keyValue = $row[$key];
						else
							$keyValue = $wi400Column->getValue();
				
						$keyValue = wi400List::applyFormat($keyValue, $keyColumn->getFormat());
							
						if (!$isFirst)
							$keysRow = $keysRow."|".$keyValue;
						else{
							$isFirst = false;
							$keysRow = $keysRow."".$keyValue;
						}
					}
					$keysRow = trim($keysRow);
					$isSelected = false;
					if (isset($rowsSelectionArray[$keysRow]))
						$isSelected = true;
					
					if($isSelected == false)
						continue;
				}
				else
					break;
			}
			
			$riga = $datasetList->appendChild($dom->createElement('row'));
			
			foreach($columnsKeyArray as $columnKey) {
				$wi400Column = $wi400List->getCol($columnKey);
				if($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()){
					$columnKey = prepare_string($columnKey);
					
					$attribute = $riga->appendChild($dom->createElement('attribute'));
					$field_name = $dom->createAttribute('id');
					$attribute->appendChild($field_name);
					$name = $dom->createTextNode($columnKey);
					$field_name->appendChild($name);
					$field_name = $dom->createAttribute('value'); 
					$attribute->appendChild($field_name); 
					$rowValue = "";
					if (isset($row[$columnKey])) {
					     $rowValue = $row[$columnKey];
					     
					     $stampa = true;
					     
					     if(is_numeric($rowValue)) {
					     	if ($wi400Column->getDefaultValue() != ""){
					     		$defaultValue = $wi400Column->getDefaultValue();
					     		if (is_array($defaultValue)>0){
					     			$condition = false;
					     			foreach($defaultValue as $rowCondition){
					     				$evalValue = substr($rowCondition[0],5).";";
					     				eval('$condition='.$evalValue.';');
					     				if ($condition){
					     					$rowValue = $rowCondition[1];
					     					break;
					     				}
					     			}
					     			$stampa = false;
					     		}
					     		else if (strpos($defaultValue, "EVAL:")===0){
					     			$evalValue = substr($defaultValue,5).";";
					     			eval('$rowValue='.$evalValue);
					     			
					     			$stampa = false;
					     		}
					     		// Per inserire in array colonne proiettate
					     		if (!isset($row[$columnKey])) {
					     			$row[$columnKey] = $rowValue;
					     		}
					     	}
					     }
					} 
					else {
						if ($wi400Column->getDefaultValue() != ""){
							$defaultValue = $wi400Column->getDefaultValue();
							if (is_array($defaultValue)>0){
								$condition = false;
								foreach($defaultValue as $rowCondition){
									$evalValue = substr($rowCondition[0],5).";";
									eval('$condition='.$evalValue.';');
									if ($condition){
										$rowValue = $rowCondition[1];
										break;
									}
								}
							} 
							else if (strpos($defaultValue, "EVAL:")===0){
								$evalValue = substr($defaultValue,5).";";
								eval('$rowValue='.$evalValue);
							}
							// Per inserire in array colonne proiettate
							if (!isset($row[$columnKey])) {
								$row[$columnKey] = $rowValue;
							}
						}
						if($wi400Column->getDecodeKey()) {
							$rowValue = wi400List::applyDecode($row[$wi400Column->getDecodeKey()], $wi400Column->getDecode());
						}
					}
					
					$rowValue = prepare_string($rowValue);
					
					$name = $dom->createTextNode($rowValue);
					$field_name->appendChild($name);
				}	
			}
			$i++;
		}

		/* Output XML del documento DOM */
		$dom->formatOutput = true;
		$returnValue = $dom->saveXML();
		
		fwrite($handle, $returnValue);
		
		fclose($handle);
	}

?>