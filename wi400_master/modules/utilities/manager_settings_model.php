<?php

	$azione = $actionContext->getAction();
	
	$off = 1;
	if(!in_array($actionContext->getForm(), array("SAVE"))) {
		$off = 2;
		$history->addCurrent();
	}
	
	$file_1 = trim(wi400Detail::getDetailValue($azione."_SRC",'FILE_1'));
	$file_2 = trim(wi400Detail::getDetailValue($azione."_SRC",'FILE_2'));
	
	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
		$actionContext->setLabel("Parametri");
		
		wi400Detail::cleanSession($azione."_LIST_SRC");
	}
	else if($actionContext->getForm()=="LIST") {
/*		
		if(isset($_FILES['IMPORT_FILE']) && is_uploaded_file($_FILES['IMPORT_FILE']['tmp_name'])) {
			// Controllo che il file non superi i 10 MB
			if($_FILES['IMPORT_FILE']['size'] > 1200000000) {
				$messageContext->addMessage("ERROR","Il file non deve superare 10 MB!");
					
				$errors = true;
			}
			else {
				$file_name = $_FILES['IMPORT_FILE']['name'];
				$file_parts = pathinfo($file_name);
//				echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
					
				$imgExt = $file_parts['extension'];
					
				if(!in_array(strtoupper($imgExt), array("PHP"))) {
					$messageContext->addMessage("ERROR","Il file deve essere in formato php.");
		
					$errors = true;
				}
				else if(!file_exists($_FILES['IMPORT_FILE']['tmp_name'])) {
					$messageContext->addMessage("ERROR","File non trovato.");
		
					$errors = true;
				}
				else {
					// LETTURA DEL FILE
				}
			}
		}
		else {
			$messageContext->addMessage("ERROR","Il file non è stato caricato.");
				
			$errors = true;
		}
*/		
		// FILE 1
		if(!empty($file_1)) {
			if(!file_exists($file_1)) {
				$path_1 = $doc_root.$file_1;
			}
			else {
				$path_1 = $file_1;
			}
			
			if(file_exists($path_1)) {
				$file_parts = pathinfo($path_1);
//				echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";

				$imgExt = $file_parts['extension'];
					
				if(!in_array(strtoupper($imgExt), array("PHP"))) {
					$messageContext->addMessage("ERROR","Il file deve essere in formato php.");
				}
				else {
//					$lines = file_get_contents($path_1);

					$handle = fopen($path_1, "r");
					
					$params_1 = array();
					$is_array = false;
					$array_params = array();
					$is_remmato = false;
					while($riga = fgets($handle)) {
						$riga = trim($riga);
//						echo "RIGA: "; var_dump($riga); echo "<br>";

						if($is_remmato===true) {
							if(substr($riga, 0, 2)=="*/") {
								$is_remmato = false;
								$riga = trim(substr($riga, 2));
							}
							else {
								continue;
							}
						}
						
						if(empty($riga) || substr($riga, 0, 2)=="//" || substr($riga, 0, strlen('$settings'))=='$settings' || in_array($riga, array("", ");", "?>")))
							continue;
						
						if(substr($riga, 0, 2)=="/*") {
							$is_remmato = true;
							continue;
						}
						
						if($is_array===true) {
							$val = $riga;
							
							$params_1[$par] .= $val;
							
							if(substr($val, 0, 1)==")")
								$is_array = false;
							else 
								$params_1[$par] .= "\r\n";
						}
						else {
							$parts = explode("=>", $riga);
//							echo "PARTS:<pre>"; print_r($parts); echo "</pre>";

							$par = trim($parts[0]);
							$val = trim($parts[1]);
							
							$params_1[$par] = $val;
						
							if(substr($val, 0, strlen("array()"))=="array()") {
								$is_array = false;
							}
							else if(substr($val, 0, strlen("array("))=="array(") {
								$is_array = true;
								$array_params[] = $par;
								$params_1[$par] .= "\r\n";
							}
						}					
					}
//					echo "PARAMS 1:<pre>"; print_r($params_1); echo "</pre>";
//					echo "ARRAY_PARAMS:<pre>"; print_r($array_params); echo "</pre>";
					
					fclose($handle);
				}
			}
			else {
				$messageContext->addMessage("ERROR","File non trovato.");
			}
		}
		
		// FILE 2
		if(!empty($file_2)) {
			if(!file_exists($file_2)) {
				$path_2 = $doc_root.$file_2;
			}
			else {
				$path_2 = $file_2;
			}
				
			if(file_exists($path_2)) {
				$file_parts = pathinfo($path_2);
//				echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
		
				$imgExt = $file_parts['extension'];
					
				if(!in_array(strtoupper($imgExt), array("PHP"))) {
					$messageContext->addMessage("ERROR","Il file deve essere in formato php.");
				}
				else {
//					$lines = file_get_contents($path_2);
		
					$handle = fopen($path_2, "r");
						
					$params_2 = array();
					$is_array = false;
//					$array_params = array();
					$is_remmato = false;
					while($riga = fgets($handle)) {
						$riga = trim($riga);
//						echo "RIGA: "; var_dump($riga); echo "<br>";

						if($is_remmato===true) {
							if(substr($riga, 0, 2)=="*/") {
								$is_remmato = false;
								$riga = trim(substr($riga, 2));
							}
							else {
								continue;
							}
						}
						
						if(empty($riga) || substr($riga, 0, 2)=="//" || substr($riga, 0, strlen('$settings'))=='$settings' || in_array($riga, array("", ");", "?>")))
							continue;
						
						if(substr($riga, 0, 2)=="/*") {
							$is_remmato = true;
							continue;
						}
		
						if($is_array===true) {
							$val = $riga;
								
							$params_2[$par] .= $val;
								
							if(substr($val, 0, 1)==")")
								$is_array = false;
							else
								$params_2[$par] .= "\r\n";
						}
						else {
							$parts = explode("=>", $riga);
//							echo "PARTS:<pre>"; print_r($parts); echo "</pre>";
		
							$par = trim($parts[0]);
							$val = trim($parts[1]);
								
							$params_2[$par] = $val;
		
							if(substr($val, 0, strlen("array()"))=="array()") {
								$is_array = false;
							}
							else if(substr($val, 0, strlen("array("))=="array(") {
								$is_array = true;
								$array_params[] = $par;
								$params_2[$par] .= "\r\n";
							}
						}
					}
//					echo "PARAMS 2:<pre>"; print_r($params_2); echo "</pre>";
//					echo "ARRAY_PARAMS:<pre>"; print_r($array_params); echo "</pre>";
						
					fclose($handle);
				}
			}
			else {
				$messageContext->addMessage("ERROR","File non trovato.");
			}
		}
	}
	else if($actionContext->getForm()=="SAVE") {
		$det_values = wi400Detail::getDetailValues($azione."_LIST_SRC");
		
		$params_1 = array();
		$params_2 = array();
		foreach($det_values as $key => $val) {
			if(substr($key, -2)=="_2") {
				$par = substr($key, -2);
				
				$params_2[$par] = $par." => ".$val; 
			}
			else {
				$par = $key;
				
				$params_1[$par] = $par." => ".$val;
			}
		}
		
		if(!empty($file_1)) {
			if(!file_exists($file_1)) {
				$path_1 = $doc_root.$file_1;
			}
			else {
				$path_1 = $file_1;
			}
				
			$string_1 = implode("/r/n", $params_1);
			
			$string = '<?php';
			$string .= '/r/n';
			$string .= '$settings = array(';
			$string .= $string_1;
			$string .= '/r/n';
			$string .= ');';
			
			file_put_contents($path_1, $string);
		}

		if(!empty($file_2)) {
			if(!file_exists($file_2)) {
				$path_2 = $doc_root.$file_2;
			}
			else {
				$path_2 = $file_2;
			}
				
			$string_2 = implode("/r/n", $params_2);
			
			$string = '<?php';
			$string .= '/r/n';
			$string .= '$settings = array(';
			$string .= $string_2;
			$string .= '/r/n';
			$string .= ');';
			
			file_put_contents($path_2, $string);
		}

		$messageContext->addMessage("SUCCESS", "Dati salvati");
		wi400Detail::cleanSession($azione."_LIST_SRC");
		$actionContext->gotoAction($azione, "LIST", "", true);
	}