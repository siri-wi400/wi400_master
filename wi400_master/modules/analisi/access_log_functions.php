<?php
	function functionInputCellLog(wi400List $wi400List, $inputField, $row) {
		if (strpos($inputField->getId(), "TEST_DATA")!==False) {
			if ($row['TEST']=='1') {
				$inputField->setLabel("CIAO!");
				$inputField->setForceLabel(True);
			} else {
				//$inputField->setLabel("");
				//$inputField->setForceLabel(False);
			}
		}
		return $inputField;	
	}
    function functionValidationAccessLog(wi400List $wi400List, $request) {
    	// Aggiungo Messaggi
    	$errorListMessages= array();
    	$key = $request['LIST_KEY'];
		//showArray($request);
    	$rowsSelectionArray = $wi400List->getSelectionArray();

    	//$errorListMessages[$key][] = array("error", "Valore non ammesso", "TEST");
    	//$row = $wi400List->getCurrentRow();
    	//showArray($row);
		// Sostituisco eventuali $row con il Selection Array se colonna di input
		if (isset($rowsSelectionArray[$key])) {
			if (in_array($rowsSelectionArray[$key]['TEST'],array("1","2","3","4"))) {
				//
			} else {
				if ($rowsSelectionArray[$key]['TEST']=="5") {
					$errorListMessages[$key][] = array("error", "Valore non ammesso", "TEST");
				}
				if ($rowsSelectionArray[$key]['TEST']=="6") {
					$errorListMessages[$key][] = array("warning", "Valore non ammesso", "TEST");
				}
				if ($rowsSelectionArray[$key]['TEST']=="7") {
					$errorListMessages[$key][] = array("info", "Valore non ammesso", "TEST");
				}
				if ($rowsSelectionArray[$key]['TEST']=="8") {
					$errorListMessages[$key][] = array("success", "Valore non ammesso", "TEST");
				}				
			}
		}
		$wi400List->setErrorMessages($errorListMessages);
    	return $wi400List;
    }
    function functionReloadAccessLog(wi400List $wi400List, $request) {
    	// Aggiungo Messaggi
   		$wi400List->setSelectionArray(array());
    	return $wi400List;
    }
    function functionAfterFetchAccessLog(wi400List $wi400List, $row) {
    	// Aggiungo Messaggi
    	//foreach ($row as $key => $value) {
    	//	$row1[$key]="PIPPO";
    	//}
    	return $row;
    }
	function getLastTimeMod($sessione) {
		global $settings;
		
		if(!$sessione) {
			return "";
		}
		
		$filename = $settings['sess_path']."sess_".$sessione;
		//$filename = $settings['sess_path']."sess_021abl1mje353dql6hf311a9g1";
		
		if(file_exists($filename)) {
			return time_elapsed_string(date("d-m-Y H:i:s", filemtime($filename)));
		}else {
			return "Chiusa";
		}
	}
	
	function time_elapsed_string($datetime, $full = false) {
		$now = new DateTime;
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);
			
		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;
			
		$string = array(
				'y' => array('anno', 'anni'),
				'm' => array('mese', 'mesi'),
				'w' => array('settimana', 'settimane'),
				'd' => array('giorno', 'giorni'),
				'h' => array('ora', 'ore'),
				'i' => array('minuto', 'minuti'),
				's' => array('secondo', 'secondi')
		);
			
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k .' '.($diff->$k > 1 ? $v[1] : $v[0]);
			} else {
				unset($string[$k]);
			}
		}
	
		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' fa' : 'proprio adesso';
	}