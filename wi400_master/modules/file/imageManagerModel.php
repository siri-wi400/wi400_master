<?php

	$reloadImage = false;
    for($j=1; $j<2; $j++) {
	if ($actionContext->getForm() == "UPLOAD" 
			&& isset($_REQUEST["OBJ_CODE"]) && $_REQUEST["OBJ_CODE"] != "" 
			&& isset($_REQUEST["OBJ_TYPE"]) && $_REQUEST["OBJ_TYPE"] != ""
			){

		if (isset($_FILES['image'])){
			if (is_uploaded_file($_FILES['image']['tmp_name'])) {
	
			  	// Controllo che il file non superi i 1 MB
			    if ($_FILES['image']['size'] > 10000000) {
			      $messageContext->addMessage("ERROR","Il file non deve superare 10 MB!!");
			      $actionContext->gotoAction("IMAGEMANAGER", "","", True, False);
			      break;
			    }
			    
			    // Ottengo le informazioni sull'immagine
			    list($width, $height, $type, $attr) = getimagesize($_FILES['image']['tmp_name']);
			    
			    // Controllo che le dimensioni (in pixel) non superino 2000 x 2000
			    if (($width > 10000) || ($height > 10000)) {
			      $messageContext->addMessage("ERROR","Dimensioni immagine eccessive...");
			      $actionContext->gotoAction("IMAGEMANAGER", "","", True, False);
			      break;
			    }
			    
			    // Controllo che il file sia in uno dei formati GIF, JPG o PNG
			    if (($type!=1) && ($type!=2) && ($type!=3)) {
			      $messageContext->addMessage("ERROR","Formato non corretto!!");
			      $actionContext->gotoAction("IMAGEMANAGER", "","", True, False);
			      break;
			    }
			    
			    // REPERISCO CODICE IMMAGINE
				$sequence = new wi400Routine("ZCREPNUM", $connzend);
				$do = $sequence->load_description();
				$do = $sequence->prepare();
				$do = $sequence->set('CODNUM',"IMAGES");
				$do = $sequence->call();
				$id = $sequence->get('NUMERO');
		
				
			    $imgExt = substr($_FILES['image']['name'],strrpos($_FILES['image']['name'],".")+1);
			    $imgCompleteName = intval($id).".".$imgExt;
		
				$completePath = $_SERVER['DOCUMENT_ROOT'].$settings['uploadPath'];
		
				// Creo le directory objType
			  	if (!file_exists($_SERVER['DOCUMENT_ROOT'].$settings['uploadPath'].$_REQUEST["OBJ_TYPE"])){
					wi400_mkdir($_SERVER['DOCUMENT_ROOT'].$settings['uploadPath'].$_REQUEST["OBJ_TYPE"]);
				}
				$completePath = $completePath.$_REQUEST["OBJ_TYPE"]."/";
					
				$imgType = null;
				if (isset($_REQUEST["IMG_TYPE"]) && $_REQUEST["IMG_TYPE"] != ""){
					// 	Creo le directory imgType
			  		if (!file_exists($_SERVER['DOCUMENT_ROOT'].$settings['uploadPath'].$_REQUEST["OBJ_TYPE"]."/".$_REQUEST["IMG_TYPE"])){
						wi400_mkdir($_SERVER['DOCUMENT_ROOT'].$settings['uploadPath'].$_REQUEST["OBJ_TYPE"]."/".$_REQUEST["IMG_TYPE"]);
					}
					$completePath = $completePath.$_REQUEST["IMG_TYPE"]."/";
					$imgType = $_REQUEST["IMG_TYPE"];
				}
				
				// Sposto il file nella cartella da me desiderata
			    if (!move_uploaded_file($_FILES['image']['tmp_name'], $completePath.$imgCompleteName)) {
					$messageContext->addMessage("ERROR","Errore nel caricamento dell'immagine!!");
					$actionContext->gotoAction("IMAGEMANAGER", "","", True, False);
					break;
			    }else{
			    	
			    	if (file_exists($completePath.$imgCompleteName)){
				    	// SALVATAGGIO DB
				    	$field = array("OBJ_CODE", "OBJ_TYPE", "IMG_TYPE", "IMG_CODE", "IMG_EXT", "IMG_RNK", "IMG_USR", "IMG_TMS");
						$key = array();
						$stmt = $db->prepare("INSERT", "OBJ_IMG", $key, $field);
						$campi = array($_REQUEST["OBJ_CODE"], $_REQUEST["OBJ_TYPE"], $imgType, $id, $imgExt, 1, $dbUser, $dbTime );
						$result = $db->execute($stmt, $campi);

						$reloadImage = true;
						if (class_exists('Imagick')) {
							// Verifica CMYK
							$i = new Imagick($completePath.$imgCompleteName);
							$cs = $i->getImageColorspace();
							if ($cs == Imagick::COLORSPACE_CMYK) {
								$messageContext->addMessage("ALERT","L'immagine caricata ha un modello di colori CMYK e potrebbe presentare difetti visivi!");
								$actionContext->gotoAction("IMAGEMANAGER", "","", True, False);
							}
							$i->clear();
							$i->destroy();
							$i = null;
							// End verifica
						}else {
							$messageContext->addMessage("INFO", "Abilitare la classe Imagick!!");
							$actionContext->gotoAction("IMAGEMANAGER", "","", True, False);
						}
						
						$messageContext->addMessage("SUCCESS","Caricamento effettuato in modo corretto!!");
			    	}else{
			    		$messageContext->addMessage("ERROR","Errore nel caricamento dell'immagine!!");
			    		$actionContext->gotoAction("IMAGEMANAGER", "","", True, False);
			    	}
			    }
			    
			    
		    
	  		}
		}
	}
    }
?>