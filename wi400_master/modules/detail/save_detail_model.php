<?


	$idDetail = "";
	$customValues = array();
	if (isset($_GET['IDDETAIL'])){
		// Recupero dati dettaglio
		$idDetail = $_GET['IDDETAIL'];
		$customValues = wi400Detail::loadCustomValues($idDetail);
	}else{
		echo "ERRORE GRAVE";
		exit();
	}
	
	$fieldsList = array();
	
	if (existDetail($idDetail)){
		$detailSessionObj = getDetail($idDetail);
		$fieldsList = $detailSessionObj["FIELDS"];
	}
	
	if ($actionContext->getForm() == "SAVE"){
		
			$detailSaveName = $_REQUEST[$idDetail."_DETAIL_SAVE"];
			$wi400DetailValues = wi400Detail::loadCustomValues($idDetail);
			
			if (is_array($fieldsList)){
				$fieldSessionList = array();
				foreach ($fieldsList as $key => $fieldSessionObj){
					if (isset($_REQUEST[$key]) && $fieldSessionObj->getSaveFile()){
						$fieldSessionObj->setValue($_REQUEST[$key]);
					}
					$fieldSessionList[$key] = $fieldSessionObj;
				}
				
				$_SESSION[$idDetail."_LOAD_DETAIL"] = $fieldsList;
				$wi400DetailValues[$detailSaveName] = $fieldsList;
				wi400Detail::saveCustomValues($idDetail, $wi400DetailValues);

			}
	}
?>