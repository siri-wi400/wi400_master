<?php 

	if($actionContext->getForm()=="ESECUZIONE") {
//		$ID = getListKey("MPX_CONV_INVIO");

//		require_once 'cvtspool_MPX.php';
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, "MPX_CONV_INVIO");
		foreach($wi400List->getSelectionArray() as $key => $value){
			$keyArray = explode("|",$key);
			
			$ID = $keyArray[0];
	
			// Lancio di un programma
			require 'cvtspool_MPX.php';
			
			if($resultExe===false)
				$messageContext->addMessage("ERROR", "Errore durante la conversione del file (".$ID.").");
			else {
				if($resultConv===true)
					$messageContext->addMessage("SUCCESS", "Esecuzione conversione riuscita. (".$ID.")");
				else if($resultConv===false)
					$messageContext->addMessage("ERROR", "Errore durante la conversione del file. (".$ID.")");
				
				if($resultEmail===true)
					$messageContext->addMessage("SUCCESS", "Esecuzione invio e-mail riuscita. (".$ID.")");
				else if($resultEmail===false)
					$messageContext->addMessage("ERROR", "Errore durante l'invio dell'e-mail. (".$ID.")");
					
				if($resultMpx===true)
					$messageContext->addMessage("SUCCESS", "Esecuzione generazione XML riuscita. (".$ID.")");
				else if($resultMpx===false)
					$messageContext->addMessage("ERROR", "Errore durante la generazione del file XML. (".$ID.")");
			}
		}
		
	}
	if($actionContext->getForm()=="NEW_ESECUZIONE") {
//		$ID = getListKey("MPX_CONV_INVIO");
	
//		require_once 'cvtspool_MPX.php';
	
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, "MPX_CONV_INVIO");
		
		foreach($wi400List->getSelectionArray() as $key => $value){
			$keyArray = explode("|",$key);
			
			$ID = $keyArray[0];
			
//			$url = 'http://127.0.0.1:89'.$appBase.'batch.php';
//			$url = "http://".$_SERVER['SERVER_ADDR'].$appBase.'batch.php';
			$url = "http://".$_SERVER['HTTP_HOST'].$appBase.'batch.php';
			echo "URL: $url<br>";
			
//			echo "URL: ".curPageURL()."<br>";
//			echo "SERVER HOST: ".$_SERVER['HTTP_HOST']."<br>";
//			echo "SERVER NAME: ".$_SERVER['SERVER_NAME']."<br>";
//			echo "SERVER:<pre>"; print_r($_SERVER); echo "</pre>";
			
			//open connection
			$ch = curl_init();
			
			// Verifico se esiste il file XML 
			$xml = "/siri/email/$ID/xml_$ID.xml";
			echo "XML PATH: $xml<br>";
			
			if(file_exists($xml)) {
				$handle = fopen($xml, "r");
				
				$postxml = fread($handle, filesize($xml));
				
				fclose($handle);
			}
			else {
				$sql = "select * from FEMAILCT where ID='" . $ID . "' AND UCTTYP='XML'";
				$contents = $db->query($sql);
				
				$conts = $db->fetch_array($contents);
				
				if($conts) {
					$postxml = trim($conts['UCTKEY']);					
				}
				else {
					die("Contents non trovato!!!");
				}
			}
			
			$fields = array("POSTXML" => $postxml);
			
			$fields_string = "";
			foreach($fields as $key=>$value) {
				$fields_string .= $key.'='.$value.'&';
			}
			
			rtrim($fields_string, '&');
//			echo $fields_string;

			// set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			
			//execute post
			$res = curl_exec($ch);
			
//			echo "RES_DEL_CURL: "; print_r($res); echo "<br>";
			
//			die("<br>ESECUZIONE BATCH CON RECUPERO DI PARAMETRI DA FILE XML LEGATO ALL'ID DELL'E-MAIL");
			
			//close connection
			curl_close($ch);
		}
	
	}
	else if($actionContext->getForm()=="INVIO_MPX") {
		$NotBatch = true;
		
		// Lancio di un programma
		require_once 'invio_MPX.php';

		if($resultMpx) {
			$messageContext->addMessage("SUCCESS", "Invio ad MPX riuscito.");
		}
		else {
			$messageContext->addMessage("ERROR", "Errore durante l'invio ad MPX.");
		}
	}
	
?>