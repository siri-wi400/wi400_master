<?php

	$azione = $actionContext->getAction();
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
	
	$keyArray = array();
	$keyArray = getListKeyArray("MONITOR_EMAIL_LIST");
	
	$old_ID = $keyArray['ID'];
	
	$ID = "I".substr($old_ID,1);
/*	
	if($actionContext->getForm()=="DEFAULT")
		$history->addCurrent();
*/	
	if($actionContext->getForm()=="DEFAULT") {
		$actionContext->setLabel("Inoltro e-mail $old_ID");
		
		$sql_email = "select * from FPDFCONV where ID='$old_ID'";
		$res_email = $db->singleQuery($sql_email);
		$row_email = $db->fetch_array($res_email);
//		echo "ROW EMAIL:<pre>"; print_r($row_email); echo "</pre>";
		
		$sql_atc = "select * from FEMAILAL where ID='$old_ID'";
		$res_atc = $db->query($sql_atc, false, 0);
		
		$body = "";
		$atc_array = array();
		
		$n = 0;
		while($row_atc = $db->fetch_array($res_atc)) {
//			echo "ROW ATC:<pre>"; print_r($row_atc); echo "</pre>";
			
			if($row_atc['TPCONV']=="BODY") {
				if(file_exists($row_atc['MAIATC'])) {
					$body = file_get_contents($row_atc['MAIATC']);
					
					$file_parts = pathinfo($row_atc['MAIATC']);
					if(in_array(strtolower($file_parts['extension']), array("htm", "html")))
						$body = trim(strip_tags($body, true));
					
						$file_body = $row_atc['MAIATC'];
				}
			}
			else {
				$n++;
				
				$atc = $n.") File: {$row_atc['MAIATC']}";
				
				if(isset($row_atc['MAIMOD']) && $row_atc['MAIMOD']!="")
					$atc .= " - Conversione: {$row_atc['MAIMOD']}";
				
				if(isset($row_atc['MAIPAT']) && $row_atc['MAIPAT']!="")
					$atc .= " - In File: {$row_atc['MAIPAT']}";
				
				if(isset($row_atc['MAINAM']) && $row_atc['MAINAM']!="")
					$atc .= " - Ridenominazione: {$row_atc['MAINAM']}";
					
				$atc_array[] = $atc;
			}
		}
		
		// ALLEGATI EXTRA
//		echo "REQUEST - ALLEGATI_PATH_0:<pre>"; var_dump($_REQUEST["ALLEGATI_PATH_0"]); echo "</pre>";
		
		$atc_path_array = array();
		if(isset($_REQUEST["ALLEGATI_PATH_0"])) {
			for($i=0; ; $i++) {
				$key = "ALLEGATI_PATH_".$i;
				
				if(!isset($_REQUEST[$key]))
					break;
				
				if(isset($_REQUEST["REMOVE_ATC"]) && $_REQUEST["REMOVE_ATC"]==$key)
					continue;
				
				$atc_path_array[] = $_REQUEST[$key];
			}
		}
//		echo "1 - ATC_PATH_ARRAY:<pre>"; print_r($atc_path_array); echo "</pre>";
		
		$loaded_file = check_load_file("IMPORT_FILE", array(), false);
		
		if($loaded_file!==false) {
			$load_file_name = $loaded_file['tmp_name'];
		
			$file_name = $loaded_file['name'];
			$file_parts = pathinfo($file_name);
//			echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
		
			$temp = "tmp";
			$file_path = wi400File::getUserFile($temp, $file_name);
//			echo "NEW FILE PATH: $file_path<br>";
				
			$rinomina = copy($load_file_name, $file_path);
			chmod($file_path, 777);

			$atc_path_array[] = $file_path;
		}
		
//		echo "2 - ATC_PATH_ARRAY:<pre>"; print_r($atc_path_array); echo "</pre>";
		
		if(!empty($atc_path_array)) {
			foreach($atc_path_array as $key => $file_path) {
				$n++;
				
				$atc = $n.") File: $file_path";
				
//				$atc_array[] = $atc;
				
				$imgHtml = get_image_url("REMOVE");				
				$htmlOutput = " <img id=\"ATC_REMOVE_TOOL\" class=\"wi400-pointer\" hspace=\"5\" style=\"cursor:pointer\" title=\""._t('REMOVE')."\" onClick=\"doSubmit('".$azione."', 'DEFAULT&REMOVE_ATC=ALLEGATI_PATH_".$key."')\" src=\"".$imgHtml."\">";
				
				$atc_array[] = $atc.$htmlOutput;
			}
		}
	}
	else if($actionContext->getForm()=="INOLTRO") {
//		echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
		
		// E-MAIL
		
		$sql_email = "select * from FPDFCONV where ID='$old_ID'";
		$res_email = $db->singleQuery($sql_email);
		$row_email = $db->fetch_array($res_email);
//		echo "ROW EMAIL:<pre>"; print_r($row_email); echo "</pre>";
		
		$stmt_ins_email = $db->prepare("INSERT", "FPDFCONV", null, array_keys($row_email));
//		echo "INSERT - EMAIL:<pre>"; print_r($row_email); echo "</pre>";
		
		$row_email['ID'] = $ID;
/*		
		if(strcmp($_REQUEST['MAISBJ'],$row_email['MAISBJ'])!=0) {
			$row_email['MAISBJ'] = $_REQUEST['MAISBJ'];
		}
*/		
		if(strcmp($_REQUEST['SUBJECT'],$row_email['MAISBJ'])!=0) {
			$row_email['MAISBJ'] = $_REQUEST['SUBJECT'];
		}
		
		$row_email['MAIEMA'] = "S";
		$row_email['MAIMPX'] = "N";
			
		$result = $db->execute($stmt_ins_email, $row_email);
		
		// ALLEGATI
				
		$sql_atc = "select * from FEMAILAL where ID='$old_ID'";
		$res_atc = $db->query($sql_atc, false, 0);
		
		$row_atc = getDs("FEMAILAL");
		
		$stmt_ins_atc = $db->prepare("INSERT", "FEMAILAL", null, array_keys($row_atc));
//		echo "INSERT - ATC:<pre>"; print_r($row_atc); echo "</pre>";

		$sql_body = "select * from FEMAILCT where ID='$old_ID' AND UCTTYP='BODY'";
		$res_body = $db->query($sql_body, false, 0);
		
		$row_body = getDs("FEMAILCT");
		
		$stmt_ins_body = $db->prepare("INSERT", "FEMAILCT", null, array_keys($row_body));
//		echo "INSERT - BODY:<pre>"; print_r($row_body); echo "</pre>";
		
		while($row_atc = $db->fetch_array($res_atc)) {
//			echo "ROW ATC:<pre>"; print_r($row_atc); echo "</pre>";
				
			if($row_atc['TPCONV']=="BODY") {
				$body = trim($row_atc['MAIATC']);
				
				if($body!="*CONTENTS") {
					if(file_exists($row_atc['MAIATC'])) {
						$file_parts = pathinfo($row_atc['MAIATC']);
						if(in_array(strtolower($file_parts['extension']), array("htm", "html"))) {
							$file_body = $row_atc['MAIATC'];
							echo "FILE BODY: $file_body<br>";
							
							$row_atc['ID'] = $ID;
			
							$result = $db->execute($stmt_ins_atc, $row_atc);
						}
					}
				}
				else {
//					echo "CONTENTS<br>";
				
					$row_atc['ID'] = $ID;
				
					$result = $db->execute($stmt_ins_atc, $row_atc);
						
					while($row_body = $db->fetch_array($res_body)) {
//						echo "BODY:<pre>"; print_r($row_body); echo "</pre>";
						$row_body['ID'] = $ID;
				
						$result = $db->execute($stmt_ins_body, $row_body);
					}
				}
			}
			else {
				$row_atc['ID'] = $ID;
		
				$result = $db->execute($stmt_ins_atc, $row_atc);
			}
		}
			
		if(isset($_REQUEST['BODY']) && $_REQUEST['BODY']!="" && !isset($file_body)) {
//			echo "TXT BODY: ".$_REQUEST['BODY']."<br>";

			$file = wi400File::getUserFile('tmp', $ID."_BODY.txt");
//			echo "FILE: $file<br>";
				
			$file_handle = fopen($file, 'w');
			fwrite($file_handle, $_REQUEST['BODY']);
			fclose($file_handle);

			$row_atc = getDs("FEMAILAL");
				
			$stmt_ins_atc = $db->prepare("INSERT", "FEMAILAL", null, array_keys($row_atc));
//			echo "INSERT - ATC:<pre>"; print_r($row_atc); echo "</pre>";
				
			$row_atc['ID'] = $ID;
			$row_atc['MAIATC'] = $file;
			$row_atc['TPCONV'] = "BODY";
			
			$row_atc['MAISTT'] = getDb2Timestamp("00/00/0000");
				
			$result = $db->execute($stmt_ins_atc, $row_atc);
		}
		
		// ALLEGATI EXTRA
/*		
		$loaded_file = check_load_file("IMPORT_FILE", array(), false);
		
		if($loaded_file!==false) {
			$load_file_name = $loaded_file['tmp_name'];
		
			$file_name = $loaded_file['name'];
			$file_parts = pathinfo($file_name);
//			echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";

			$temp = "tmp";
			$file_path = wi400File::getUserFile($temp, $file_name);
//			echo "NEW FILE PATH: $file_path<br>";
			
			$rinomina = copy($load_file_name, $file_path);
			chmod($file_path, 777);

			$row_atc = getDs("FEMAILAL");
			
			$row_atc['ID'] = $ID;
			$row_atc['MAIATC'] = $file_path;
				
			$row_atc['MAISTT'] = getDb2Timestamp("00/00/0000");
			
			$result = $db->execute($stmt_ins_atc, $row_atc);			
		}
*/
		// ALLEGATI EXTRA
//		echo "REQUEST - ALLEGATI_PATH_0:<pre>"; var_dump($_REQUEST["ALLEGATI_PATH_0"]); echo "</pre>";
		
		$atc_path_array = array();
		if(isset($_REQUEST["ALLEGATI_PATH_0"])) {
			for($i=0; ; $i++) {
				$key = "ALLEGATI_PATH_".$i;
		
				if(!isset($_REQUEST[$key]))
					break;
		
				$file_path = $_REQUEST[$key];
				
				$row_atc = getDs("FEMAILAL");
				
				$row_atc['ID'] = $ID;
				$row_atc['MAIATC'] = $file_path;
				
				$row_atc['MAISTT'] = getDb2Timestamp("00/00/0000");
					
				$result = $db->execute($stmt_ins_atc, $row_atc);
			}
		}
		
		// DESTINATARI
		
		$row_dest = getDs("FEMAILDT");
		
		$stmt_ins_dest = $db->prepare("INSERT", "FEMAILDT", null, array_keys($row_dest));
//		echo "INSERT - DEST:<pre>"; print_r($row_dest); echo "</pre>";
		
		$row_dest['ID'] = $ID;
		
		if(isset($_REQUEST['TO']) && !empty($_REQUEST['TO'])) {
			$row_dest['MATPTO'] = "TO";
			
			foreach($_REQUEST['TO'] as $val) {
				$row_dest['MAITOR'] = $val;
				
				$result = $db->execute($stmt_ins_dest, $row_dest);
			}
		}
		
		if(isset($_REQUEST['CC']) && !empty($_REQUEST['CC'])) {
			$row_dest['MATPTO'] = "CC";
			
			foreach($_REQUEST['CC'] as $val) {
				$row_dest['MAITOR'] = $val;
				
				$result = $db->execute($stmt_ins_dest, $row_dest);
			}
		}
		
		if(isset($_REQUEST['BCC']) && !empty($_REQUEST['BCC'])) {
			$row_dest['MATPTO'] = "BCC";
			
			foreach($_REQUEST['BCC'] as $val) {
				$row_dest['MAITOR'] = $val;
				
				$result = $db->execute($stmt_ins_dest, $row_dest);
			}
		}
		
		if(isset($_REQUEST['RPYTO']) && !empty($_REQUEST['RPYTO'])) {
			$row_dest['MATPTO'] = "RPYTO";
				
			foreach($_REQUEST['RPYTO'] as $val) {
				$row_dest['MAITOR'] = $val;
		
				$result = $db->execute($stmt_ins_dest, $row_dest);
			}
		}
		
		if(isset($_REQUEST['CONTO']) && !empty($_REQUEST['CONTO'])) {
			$row_dest['MATPTO'] = "CONTO";
				
			foreach($_REQUEST['CONTO'] as $val) {
				$row_dest['MAITOR'] = $val;
		
				$result = $db->execute($stmt_ins_dest, $row_dest);
			}
		}
		
		$actionContext->gotoAction("EMAIL_INVIO", "INOLTRO", "", true);
	}