<?php

	require_once $routine_path."/classi/wi400ExportList.cls.php";
	
	if ($actionContext->getForm() == "EXPORT"){
		$exportFormat = $_REQUEST['FORMAT'];
		
		$exportTarget = $_REQUEST['TARGET'];
		$idList		  = "AZIONI_EXP";
		$wi400List = getList($idList);

		$export = new wi400ExportList($exportTarget, $wi400List);
		
		// Recupero la query della lista (compresi i filtri utilizzati)
		$export->prepare();
		
		/*
		 * Lancio l'esportazione a seconda del formato (impostato nel codice html che si trova nella _view.php)
		 * e ottengo il filename di ritorno in modo che sia comune con la _view.php
		 */ 
		if(in_array($exportFormat, array("excel2007", "excel5"))) {
			require_once $moduli_path.'/anagrafiche/azioni_export.php';
			
			exportXLS($export, $idList, "FAZISIRI");
		}
		
		// Recupero dei parametri del file generato necessari per il download
		$filename = $export->getFilename();
		$filepath = $export->get_filepath();
		$TypeImage = $export->getTypeImage();
		$temp = $export->getTemp();

//		echo "FILENAME: $filename - TYPE: $TypeImage - TEMP: $temp - FILEPATH: $filepath<br>";
	}
	else if ($actionContext->getForm() == "EXPORT_SEL"){	
		$export = new wi400ExportList();
	}
	else if ($actionContext->getForm() == "EXPORT_LIST") {
		$actionContext->setLabel(_t('ACTION_EXPORT_TITLE'));
	}