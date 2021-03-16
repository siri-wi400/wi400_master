<?php

	// *********************************************************************
	// Uploader 2.1
	// Programma automatico per l'inserimento delle immagini degli articoli.
	// L'immagine viene salvata sotto upload se esiste uno o più codici
	// articolo nel nome del file.
	// *********************************************************************
	
	// Parametri

	// Cartella contenente le immagini
	$imagesFolder = $settings['doc_root']."VEGA";
	// Regular Expression individua 7 numeri consecutivi
	$itemCodeRegEx = "/\d{7}/";
	
	// Inizializzazione routine	
	$rtlart = new wi400Routine('RTLART', $connzend);
	$rtlart->load_description();
	$rtlart->prepare();
	
	// Prepare per inserire relazioni immagini-oggetti.
	$field = array("OBJ_CODE", "OBJ_TYPE", "IMG_CODE", "IMG_EXT", "IMG_RNK");
	$key = array();
	$stmt = $db->prepare("INSERT", "OBJ_IMG", $key, $field);

	// Inizializzazione routine numeratore
	$sequence = new wi400Routine("ZCREPNUM", $connzend);
	$do = $sequence->load_description();
	$do = $sequence->prepare();
							
	if ($handle = opendir($imagesFolder)) {
		
		$filename = "";
		
	    /* This is the correct way to loop over the directory. */
	    while (false !== ($filename = readdir($handle))) {
	        
	    	preg_match_all($itemCodeRegEx, $filename, $matches, PREG_SET_ORDER);
			
	    	foreach ($matches as $itemArray){
				foreach ($itemArray as $itemCode){

						echo "<br>Controllo se esiste ".$itemCode."<br>";
					    
					    $rtlart->set('NUMRIC',1);
					    $rtlart->set('DATINV', date("Ymd"));
					    $rtlart->set('ARTICOLO',$itemCode);
					    $result = $db->columns("FMDAANAR");
					    $rtlart->call();
					    
						$row = $rtlart->get('ARTI');
						
						if (!isset($row["MDACDA"]) || $row["MDACDA"] == ""){

						}else{
							echo "Esiste ".$itemCode;
							
							// SALVATAGGIO DB
							$do = $sequence->set('CODNUM',"IMAGES");
							$do = $sequence->call();
							$id = $sequence->get('NUMERO');

							echo $id."<br>";

							$imgExt = substr($filename,strrpos($filename,".")+1);
						    $imgCompleteName = intval($id).".".$imgExt;
						    
							$campi = array($itemCode, "ART", $id, $imgExt, 1);
							$result = $db->execute($stmt, $campi);
							
							// COPIA IMMAGINE SOTTO UPLOAD
							$completePath = $_SERVER['DOCUMENT_ROOT'].$settings['uploadPath']."ART/";
							copy($imagesFolder."/".$filename, $completePath.$imgCompleteName);
						}
				}
				
			}
	    }

		
		 closedir($handle);
	}

?>