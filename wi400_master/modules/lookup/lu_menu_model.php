<?php

	require_once $routine_path."/classi/wi400ExportList.cls.php";
	require_once $routine_path."/classi/wi400invioEmail.cls.php";
	
	if ($actionContext->getForm() == "EXPORT"){
		$exportFormat = $_REQUEST['FORMAT'];
		
		$exportTarget = $_REQUEST['TARGET'];
		$idList		  = "MENU_EXP";
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
			
			$menu_array = exportXLS($export, $idList, "FMNUSIRI");
//			echo "MENU ARRAY:<pre>"; print_r($menu_array);

			if($menu_array===false) {
				$actionContext->onError("LU_AZIONI", "EXPORT_SEL", "", "", true);
			}
			else {		
				$filepath = $export->get_filepath();
				
				$zip_parts = explode(".", basename($filepath));
				$zip_path = dirname($filepath)."/".$zip_parts[0].'.zip';
					
				wi400invioEmail::compress(array($filepath),$zip_path);
				
				unlink($filepath);
				
				$export_2 = new wi400ExportList();
				
				exportXLS($export_2, "AZIONE_EXP", "FAZISIRI", $menu_array);
				
				$filepath_2 = $export_2->get_filepath();
				
				wi400invioEmail::compress(array($filepath_2),$zip_path);
				
				unlink($filepath_2);
				
				$filename = basename($zip_path);
				$TypeImage = "zip";
			}
		}

//		echo "FILENAME: $filename - TYPE: $TypeImage - TEMP: $temp - FILEPATH: $filepath<br>";
	}
	else if ($actionContext->getForm() == "EXPORT_SEL"){	
		$export = new wi400ExportList();
	}
	else if ($actionContext->getForm() == "EXPORT_LIST") {
		$actionContext->setLabel(_t('MENU_EXPORT_TITLE'));
	}