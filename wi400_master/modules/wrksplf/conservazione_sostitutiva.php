<?php

	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()=="DEFAULT") {
		require_once $routine_path."/classi/wi400invioEmail.cls.php";
		
		$keyUpdt = array("LCONOM" => "?");
		$fieldsValue = array("LCONZP" => "", "LCOIZP" => "");
		
		$stmt_updt = $db->prepare("UPDATE", "FLCONSSO", $keyUpdt, array_keys($fieldsValue));
		
		$sql = "select PARPE4, PARPE5 from FPARFDOC";
		$res = $db->query($sql, false, 0);
		while($row = $db->fetch_array($res)) {
			$path_input = $row['PARPE4'];
			$path_output = $row['PARPE5'];
//			$path_copy = $row['PARPE2'];
//			echo "<font color='blue'>PATH INPUT:</font> $path_input<br>";
//			echo "<font color='red'>PATH OUTPUT:</font> $path_output<br>";
//			echo "<font color='green'>PATH COPY:</font> $path_copy<br>";

			if($path_input=="" || !file_exists($path_input)) {
//				echo "PATH INPUT NON TROVATO<br>";
				$messageContext->addMessage("ERROR", "Path di input $path_input non trovato");
				continue;
			}
			
			if($path_output=="") {
//				echo "PATH OUTPUT NON TROVATO<br>";
				$messageContext->addMessage("ERROR", "Path di output vuoto");
				continue;
			}
			
			$user = $_SESSION["user"];
//			echo "USER: $user<br>";
/*			
			$sql_naz = "select USNAZI 
				from SIRIUTENZE/fuser, SIRIUTENZE/fareafun
				where FUSER.USARFU=FAREAFUN.AFAREA and FUSER.USSTAT='1' and FUSER.USUSER='".sanitize_sql_string($_SESSION["user"])."' and 
					FUSER.USARFU='".sanitize_sql_string($loginProfile["AREA"])."'";
*/			
			$sql_naz = "select USNAZI
				from SIRIUTENZE/fuser
				where USSTAT='1' and USUSER='".sanitize_sql_string($user)."'";
			$res_naz = $db->singleQuery($sql_naz);
			$nazione = "";
			if($row_naz = $db->fetch_array($res_naz)) {
				$nazione = $row_naz['USNAZI'];
			}
//			echo "NAZIONE: $nazione<br>";
			
			$zip_name = $nazione."_".date("Ymdhis").".zip";
//			echo "ZIP FILE NAME: $zip_name<br>";
			
			$zip_path = $path_output.$zip_name;
//			echo "ZIP: $zip_path<br>";
			
			$scan_files = scandir($path_input);
//			echo "SCAN FILES:<pre>"; print_r($scan_files); echo "</pre>";
			
			$array_files = array();
			if(!empty($scan_files)) {
				foreach($scan_files as $key => $val) {
					if(!is_dir($val)) {
						$array_files[] = $path_input.$val;
						
						// Update di FLCONSSO
						$campi = $fieldsValue;
						$campi["LCONZP"] = $zip_path;
						$campi["LCOIZP"] = "S";
						$campi["LCONOM"] = $val;
//						echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
						
						$result = $db->execute($stmt_updt, $campi);
					}
				}
			}
//			echo "FILES INPUT:<pre>"; print_r($array_files); echo "</pre>";
				
			if(!empty($array_files)) {
				if(!file_exists($path_output)) {
					wi400_mkdir($path_output, 777, true);
				}
				
//				$zip_path = $path_output.$zip_name;				
//				echo "ZIP: $zip_path<br>";
				
				wi400invioEmail::compress($array_files, $zip_path);
//				echo "FILE ZIPPATO<br>";
/*				
				if($path_copy!="") {
					if(!file_exists($path_copy)) {
						wi400_mkdir($path_copy, 0, true);
					}
					
					$zip_copy = $path_copy.$zip_name;
					echo "COPIA: $zip_copy<br>";
					
					copy($zip_path, $zip_copy);
				}
*/				
				// @todo RIATTIVARE L'ELIMINAZINE DEI FILES
				if(file_exists($zip_path)) {
					foreach($array_files as $val) {
//						echo "REMOVE FILE: $val<br>";
						unlink($val);
					}
				}
				
				$messageContext->addMessage("SUCCESS", "Archiviazione eseguita con successo");
			}
		}
	}