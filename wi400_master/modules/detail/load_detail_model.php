<?
	$hasError = true;
	if (isset($_GET['IDDETAIL'])){
			// Recupero dati dettaglio
			$idDetail = $_GET['IDDETAIL'];
			 
			if ($_REQUEST[$idDetail.'_CUSTOM_FILTER']){
				$customValues = wi400Detail::loadCustomValues($idDetail);
				$detailSaveName = $_REQUEST[$idDetail.'_CUSTOM_FILTER'];
				$wi400DetailValues = wi400Detail::loadCustomValues($idDetail);
				wi400Detail::cleanSession($_GET['IDDETAIL']);
				if ($detailSaveName != "" && isset($wi400DetailValues[$detailSaveName])){
					$_SESSION[$idDetail."_LOAD_DETAIL"] = $wi400DetailValues[$detailSaveName];
					$hasError = false;
				}

			}
	}
	if ($hasError){
		$messageContext->addMessage("ERROR","Errore durante il caricamento del dettaglio!");
	}
?>