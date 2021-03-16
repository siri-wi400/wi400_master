<?php

	/*
	 * Nel caso dell'invio via e-mail vengono caricati gli indirizzi dei destinatari dalla tabella FEMAILDT
	 * e gli allegati dalla tabella FEMAILAL (nel caso in cui siano richiesti vengono anche eseguite
	 * la conversione e la compressione in formato .zip degli allegati)
	 */

	require_once $routine_path.'/classi/wi400invioConvert.cls.php';
	
	$params = array();
	$params = get_log_email_params();
	
	/*
	 * Indicazione del tipo di funzionamento: in batch o no -
	* necessario per il recupero dei parametri a seconda del tipo utilizzato
	* e per fare in modo che quando avviene un errore in modalità batch venga interrotta l'esecuzione,
	* ma che in altra modalità (lancio da WI400) non si sbianchi lo schermo
	*/
	$isBatch = false;
	
	/*
	 * Se il programma viene lanciato con modalità batch i parametri vanno recuperati di conseguenza
	*/
	$ID_array = array();
	
	if(isset($argv) && !empty($argv)) {
		echo "BATCH DA AS<br>";
		/*
		 * Recupero dei parametri passati con funzionamento in batch -
		* argv[1] è il primo parametro passato, dopo argv[0] che indica il numero di parametri passati
		* @argv[1] in certi casi serve per fare il catch di eventuali errori (in questo caso non servirebbe)
		*/
	
		$ID = trim(substr(@$argv[1],0,10));
		// Recupero la lista delle librerie dell'interattivo
		$INT_LIBRARY = explode(";" ,trim($argv[2]));
	
		$isBatch = true;
		$ID_array = array($ID);
		$actionContext->setForm("ESECUZIONE");
	}
	else if(isset($batchContext)) {
		echo "BATCH<br>";
	
		$ID = $batchContext->id;
		$INT_LIBRARY = explode(";", $batchContext->lista_librerie);
	
		$isBatch = true;
		$ID_array = array($ID);
		$actionContext->setForm("ESECUZIONE");
	}
	else {
		echo "LANCIO DA WI400<br>";
	
		$ID_array = get_id_array($actionContext->getForm());
	}
	echo "ID_ARRAY:<pre>"; print_r($ID_array); echo "</pre>";
	
	function get_log_email_params() {
		$params = array();
		
//		$params['email_log_file'] = $moduli_path."/mpx/include/cvtspool_invio.log";
//		$params['email_log_file'] = $root_path."logs/email/cvtspool_invio_".date("Ymd").".log";
		
		// file di log
		$file_email_path = get_log_file_path("LOG_EMAIL");
		
		if(!file_exists($file_email_path)) {
			wi400_mkdir($file_email_path, 777, true);
		}
		
		$file_email_name = get_log_file_name("LOG_EMAIL");
		
		$params['email_log_file'] = $file_email_path.$file_email_name;
		
		return $params;
	}
	
	function get_id_array($form) {
		$idList = "MONITOR_EMAIL_LIST";
		echo "IDLIST: $idList<br>";
	
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
	
		$ID_array = array();
	
		foreach($wi400List->getSelectionArray() as $key => $value){
			$keyArray = explode("|",$key);
			
//			$ID = $keyArray[1];
			
			$keyArray = get_list_keys_num_to_campi($wi400List, $keyArray);
				
			$ID = $keyArray["ID"];
				
			if($form=="INOLTRO")
				$ID = "I".substr($ID,1);
	
			$ID_array[] = $ID;
		}
	
		return $ID_array;
	}
	
	function get_launch_batch_email_atc_cond($ID) {
		global $db;
		
		static $stmt_al_act;
		
		$cond = false;
		
		// Controllo della presenza di allegati da generare tramite azione
		if(!isset($stmt_al_act)) {
			$sql_al_act = "select * from FEMAILAL where ID=? and TPCONV='ACTION'";
			$stmt_al_act = $db->singlePrepare($sql_al_act, 0, true);
		}
		
		$res_al_act = $db->execute($stmt_al_act, array($ID));
			
		if($row_al_act = $db->fetch_array($stmt_al_act)) {
			$cond = true;
		}
		
		return $cond;
	}
	
	// @todo ????? SERVE VERAMENTE (più completa quella in wi400invioConvert.cls.php) ?????
	function launch_batch_email_atc_action($ID, $params, $isBatch) {
		global $db;
		global $appBase;
		global $messageContext;
		
		static $stmt_xml;
		
		if(!isset($stmt_xml)) {
			// Recupero dei contenuti XML legati all'e-mail
			$sql_xml = "select * from FEMAILCT where ID=? and UCTTYP='XML'";
			$stmt_xml = $db->singlePrepare($sql_xml, 0, true);
		}
		
		// Verifico se esiste il file XML
		$xml_path = "/siri/email/$ID/xml_$ID.xml";
		echo "XML PATH: $xml_path<br>";
			
		$postxml = "";
		if(file_exists($xml_path)) {
			echo "FILE XML<br>";
			
			$handle = fopen($xml_path, "r");
		
			$postxml = fread($handle, filesize($xml_path));
		
			fclose($handle);
		}
		else {
			echo "FEMAILCT<br>";
/*
			$sql_xml = "select * from FEMAILCT where ID='$ID' and UCTTYP='XML'";
			$res_xml = $db->query($sql_xml);
			
			if($row_xml = $db->fetch_array($res_xml)) {
*/
			$res_xml = $db->execute($stmt_xml, array($ID));
				
			if($row_xml = $db->fetch_array($stmt_xml)) {
//				echo "ROW XML:<pre>"; print_r($row_xml); echo "</pre>";
				$postxml = trim($row_xml['UCTKEY']);
			}
			else {
				$msg = "Contenuto XML legato all'e-mail non trovato. (".$ID.")";
				wi400invioConvert::write_log($ID,'1','025',$msg,$params['email_log_file'],"E-MAIL");
			
				if($isBatch)
					die();
				else
					$messageContext->addMessage("ERROR", $msg);
			}
		}
/*		
		$postxml = '<?xml version="1.0" ?><parametri><parametro id="action" value="SENDMAIL"/><parametro id="id" value="T000000617"/><parametro id="lista_librerie" value="SOCKETWNRC;UNICOMF;PHPLIB;MERSY_PERS;MERSY_DB;MERSY_OB;MERSY_SET;MERSY_TCP;MERSY_PLEX;MERSY_DIZD;MTRUNTOB;MTRUNTDB;MTRUNTPLEX;PLEX;QGPL;QDEVTOOLS;INTERFACCE;SENDYUNI;SENDYOBJ;PROBAS;MERSY_DWH;MERSY_GUAR"/><parametro id="user" value="MERSY"/><parametro id="appBase" value="//WI400_VPORRAZZO//"/><parametro id="nodb" value="True"/><parametro id="private" value="INVIOEMAIL_955556_197742"/><parametro id="jobname" value="MAIL"/><parametro id="timeout" value="1200"/><parametro id="fileSave" value="/SIRI/EMAIL/T000000617/xml_T000000617.xml"/><parametro id="WEMCISP" value=""/><parametro id="WEMCPCO" value="000000"/><parametro id="WEMCCAN" value="IPP"/><parametro id="WEMCFOR" value="68600"/><parametro id="WEMSTS" value=""/><parametro id="WEMTSHW" value=""/><parametro id="WEMCSHW" value="ZY"/><parametro id="WEMBSHW" value="2015"/><parametro id="WEMCAZI" value="900"/></parametri>';
*/		
//		echo "POST_XML: $postxml<br>";
		
		if(isset($postxml) && !empty($postxml)) {
			$xml_fields = array("POSTXML" => $postxml);
		
			$xml_fields_string = "";
			foreach($xml_fields as $key => $value) {
				$xml_fields_string .= $key.'='.$value.'&';
			}
										
			rtrim($xml_fields_string, '&');
//			echo $xml_fields_string;
		
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
				
			// set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, count($xml_fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_fields_string);
			
			//execute post
			$curl_result = curl_exec($ch);
			
//			echo "RES_DEL_CURL: "; print_r($curl_result); echo "<br>";
			
//			die("ESECUZIONE BATCH CON RECUPERO DI PARAMETRI DA FILE XML LEGATO ALL'ID DELL'E-MAIL");
		
			//close connection
			curl_close($ch);
		}
	}