<?php

	$azione = $actionContext->getAction();
//	echo "AZIONE: $azione - FORM: ".$actionContext->getForm()."<br>";
		
	if($actionContext->getForm()=="DEFAULT") {
		wi400Session::delete(wi400Session::$_TYPE_DETAIL, $azione.'_STAMPA_SEL_DET');
	}
/*
	else if($actionContext->getForm()=="DOWNLOAD_FILE") {
		$file_parm = explode('|', $_REQUEST["DETAIL_KEY"]);
		$path = trim($file_parm[0]);
		$name = trim($file_parm[1]);
	
		$filename = $path."/".$name;
	
//		echo "FILE: $filename<br>";
	
		$TypeImage = "";
		$file_parts = pathinfo($filename);
		if(isset($file_parts['extension']))
			$TypeImage = strtolower($file_parts['extension']);
	}
*/
	else if($actionContext->getForm()=="DOWNLOAD_FILE") {
		$file_parm = explode('|', $_REQUEST["DETAIL_KEY"]);
//		$path = trim($file_parm[0]);
//		$name = trim($file_parm[1]);

		$user = $file_parm[0];
		$job = $file_parm[1];
		$nbr = $file_parm[2];
		$user_data = $file_parm[3];
		$modulo = $file_parm[6];
		$ultima_conv = $file_parm[7];
		
		$path = trim($file_parm[4]);
		$name = trim($file_parm[5]);
	
		$filename = $path."/".$name;
	
//		echo "FILE: $filename<br>";

		$temp = "";
	
		$TypeImage = "";
		$file_parts = pathinfo($filename);
		if(isset($file_parts['extension']))
			$TypeImage = strtolower($file_parts['extension']);
		
		$campi = array();
		
		$sql = "select * 
			from FPDFCONV a left join FEMAILDT b on a.ID=b.ID 
			where a.ID='$ultima_conv' and MAIUSR='$user' and MAIJOB='$job' and MAINBR='$nbr'";
//		echo "SQL: $sql<br>";
		$res = $db->query($sql, false, 0);
		
//		$to_email = getEmailNegozio($negozio);
		
		while($row = $db->fetch_array($res)) {
//			echo "ROW:<pre>"; print_r($row); echo "</pre>";
			if(empty($campi)) {
				$campi['FROM'] = $row['MAIFRM'];
				$campi['SUBJECT'] = $row['MAISBJ'];
			}
			
			$campi[$row['MATPTO']][] = $row['MAITOR'];
		}
	}
	else if($actionContext->getForm()=="REMOVE") {
		$idList = $azione."_LIST";
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		$rowsSelectionArray = $wi400List->getSelectionArray();	
//		echo "SEL_ARRAY: "; print_r($rowsSelectionArray); echo "<br>";
	
		foreach($rowsSelectionArray as $key => $val) {
			$keys = explode("|", $key);
//			echo "KEYS:<pre>"; print_r($keys); echo "</pre><br>";
				
			$path = $keys[4];
			$name = $keys[5];
				
			$file_path = $path."/".$name;
//			echo "FILE: $file_path<br>";
				
			if(file_exists($file_path)) {
				unlink($file_path);
			}
				
			$keys_array = array(
				"LOGUSR" => $keys[0],
				"LOGJOB" => $keys[1],
				"LOGNBR" => $keys[2],
				"LOGDTA" => $keys[3],
				"LOGPTH" => $path,
				"LOGNOM" => $name,
				"LOGMOD" => $keys[6],
				"LOGID" => $keys [7]
			);
				
			$j = 8;
			for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
				$keys_array["LOGKY".$i] = $keys[$j++];
			}
				
//			echo "DEL KEYS:<pre>"; print_r($keys_array); echo "</pre><br>";
	
			$stmtdelete = $db->prepare("DELETE", "FLOGCONV", array_keys($keys_array), null);
			$deleteRes = $db->execute($stmtdelete, $keys_array);
				
			if($deleteRes)
				$messageContext->addMessage("SUCCESS", "Eliminazione del log avvenuta con successo");
			else
				$messageContext->addMessage("ERROR", "Errore durante l'eliminazione del log");
		}
	
		$actionContext->onError($azione, "DEFAULT");
		$actionContext->onSuccess($azione, "DEFAULT");
	}
/*	
	else if ($actionContext->getForm() == "CALCULATE"){
		$check_duplex = "";
		if (isset($_GET["OUTQ"])){
			$sql = "select * from FP2OPARM where PROUTQ='{$_GET["OUTQ"]}'";
			$res = $db->singleQuery($sql);
			if($row = $db->fetch_array($res)) {
				$check_duplex = "S";
			}
		}
		die($check_duplex);
	}
*/