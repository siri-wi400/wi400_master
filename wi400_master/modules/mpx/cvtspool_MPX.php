<?php 

//echo "<font color='green'>cvtspool_MPX.php</font><br>";
/*
// @todo TEST - Per testare funzionamento con wi400Convert.cls.php
// Quando sarà il momento di cambiare il vecchio MPX_CONV_INVIO con MONITOR_EMAIL, cambiare l'azione SENDMAIL
// da modulo mpx e model cvtspool_MPX.php a modulo email e model email_invio.php
if(isset($batchContext)) {	
	require_once $moduli_path."/email/email_invio.php";
}
else {
*/
/*
 * Applicazione per l'invio di file via e-mail o di PDF ad MPX
 * 
 * Nel caso dell'invio via e-mail vengono caricati gli indirizzi dei destinatari dalla tabella FEMAILDT
 * e gli allegati dalla tabella FEMAILAL (nel caso in cui siano richiesti vengono anche eseguite
 * la conversione e la compressione in formato .zip degli allegati)
 */

/* 
 * Recupero dei parametri passati con funzionamento in bash -
 * argv[1] è il primo parametro passato, dopo argv[0] che indica il numero di parametri passati
 * @argv[1] in certi casi serve per fare il catch di eventuali errori (in questo caso non servirebbe)
 */

/*
 * Indicazione del tipo di funzionamento: in batch o no -
 * necessario per il recupero dei parametri a seconda del tipo utilizzato
 * e per fare in modo che quando avviene un errore in modalità batch venga interrotta l'esecuzione,
 * ma che in altra modalità (lancio da programma mpx_invio.php in WI400) non si sbianchi lo schermo 
 */ 
$isBatch = false;

/*
 * Se $ID non è stato già valorizzato da un altro programma,
 * allora il programma lanciato è di tipo batch e i parametri vanno recuperati di conseguenza
 */
if(!isset($ID) || empty($ID)) {
	if(isset($argv) && !empty($argv)) {	
	 	$ID = trim(substr(@$argv[1],0,10));
		// Recupero la lista delle librerie dell'interattivo 	
		$INT_LIBRARY = explode(";" ,trim($argv[2]));
	}
	else if(isset($batchContext)) {
		$ID = $batchContext->id;
		$INT_LIBRARY = explode(";", $batchContext->lista_librerie);
	}
	
	$isBatch = true;
}

require_once $routine_path.'/classi/wi400invioMPX.cls.php';
require_once $routine_path.'/classi/wi400invioEmail.cls.php';

// file di log
$file_email_path = get_log_file_path("LOG_EMAIL");

if(!file_exists($file_email_path)) {
	wi400_mkdir($file_email_path, 777, true);
}

$file_email_name = get_log_file_name("LOG_EMAIL");

$params = array();
//$params['email_log_file'] = $moduli_path."/mpx/include/cvtspool_invio.log";
$params['email_log_file'] = $file_email_path.$file_email_name;
$params['mpx_xml_path'] = $moduli_path."/mpx/include/";
$params['mpx_xml_invio'] = $moduli_path."/mpx/include/invio/";
$params['mpx_pdf_path'] = $moduli_path."/mpx/include/";
if($settings['mpx_uri']!="")
	$params['mpx_uri'] = $settings['mpx_uri'];
else
	$params['mpx_uri'] = 'http://'.$settings['mpx_server'].":".$settings['mpx_port'].$appBase."modules/mpx/include/response_MPX.php?ID=".$ID;

// Indicazione dello stato dell'operazione, avrà valore true quando l'operazione avrà avuto successo
$resultExe = 0;
$resultMpx = 0;
$resultConv = 0;
$resultEmail = 0;

// Recupero il record impostato nella tabella FPDFCONV
$sql = "select * from FPDFCONV where ID='$ID'";
$result = $db->singleQuery($sql);
$conv_rec  = $db->fetch_array($result);

if(!$conv_rec) {
	wi400invioEmail::agg_log($ID,'1',0,'001',
		"Record ID: $ID non trovato nella tabella FPDFCONV",$params['email_log_file']);
	$resultExe = false;
	if($isBatch) 
		die();
	else
		return $resultExe;
}

wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'',
	"Trovato record ID: $ID in tabella FPDFCONV",$params['email_log_file']);

//echo "TIPO AZIONE: $form<br>";
//echo "CONV REC:<pre>"; print_r($conv_rec); echo "</pre>";

// Conversione/Invio ad MPX
if($conv_rec['MAIMPX']=='S' || ($conv_rec['MAIMPX']!='S' && $conv_rec['MAIEMA']!='S')) {
	// Recupero il record impostato nella tabella FEMAILAL contenente il file da convertire/inviare ad MPX
	$sql = "select * from FEMAILAL where ID='$ID' and TPCONV<>'BODY'";
	$result = $db->singleQuery($sql);
	$atc_rec  = $db->fetch_array($result);

	if(!$atc_rec || empty($atc_rec['MAIATC'])) {
		wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'003',
			"Non è stato trovato alcun record nella tabella FEMAILAL",$params['email_log_file']);
		$resultExe = false;
		if($isBatch) 
			die();
		else
			return $resultExe;
	}
	
	// Conversione in PDF del file da inviare ad MPX
	if($atc_rec['CONV']=='S') {
		// Conversione in PDF di un file di spool
		if($conv_rec['MAIMPX']=='S' || $atc_rec['TPCONV']=='PDF') {
			wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'',
				"Conversione in PDF di un file di spool",$params['email_log_file']);
	
			/*
		 	 * Caricamento del modulo da utilizzare
			 * (il codice modulo rappresenta la struttura con cui deve essere generato il PDF
			 * es: fattura, ...)
			 */
			$codice_modulo = $atc_rec['MAIMOD'];
			// Se il codice modulo non è stato valorizzato lo imposto con il codice argomento.
			if(trim($codice_modulo)=="") 
				$codice_modulo = $atc_rec['MAIARG'];
			$sql="select * from SIR_MODULI where MODNAM='$codice_modulo'";
			$result = $db->singleQuery($sql);
			$modulo= $db->fetch_array($result);
			// Se la ricerca nel databse non fornisce risultati allora si usa il modulo di default
			if(!isset($modulo) || !$modulo) {
				$codice_modulo = "*DEFAULT";
				$sql="select * from SIR_MODULI where MODNAM='$codice_modulo'";
				$result = $db->singleQuery($sql);
				$modulo=$db->fetch_array($result);	
				if($modulo) {
					wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'',
						"Usato *DEFAULT come modulo di conversione",$params['email_log_file']);
				}
				else {
					wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'008',
						"Nessun modulo di conversione applicabile",$params['email_log_file']);
					$resultMpx = false;
					if($isBatch) 
						die();
					else 
						return $resultMpx;
				}
			}
/*			
			// Istanzio la classe e trovo l'eventuale personalizzazione
			$classe = "wi400SpoolCvt.cls.php";
			$conv = "wi400SpoolConvert";
			if (trim($modulo['MODCLS']!="" && $modulo['MODCLS']!="*DEFAULT")) {
//				$classe_particolare = "wi400SpoolCvt_".trim($modulo['MODCLS']).".cls.php";
				$classe_particolare = "pers/wi400SpoolCvt_".trim($modulo['MODCLS']).".cls.php";

				if (file_exists($routine_path . "/classi/" . $classe_particolare)) {
					$classe = $classe_particolare;
					$conv = "wi400SpoolConvert_".trim($modulo['MODCLS']);
				}
				else
					wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'004',
						"Classe $classe_particolare non trovata",$params['email_log_file']);
			}
			require $routine_path . "/classi/" . $classe;
*/					
			// Istanzio la classe e trovo l'eventuale personalizzazione
			$classe = $routine_path."/classi/wi400SpoolCvt.cls.php";
			$conv = "wi400SpoolConvert";
			$modcls = "*DEFAULT";
			if (isset($modulo) && trim($modulo['MODCLS']!="" && $modulo['MODCLS']!="*DEFAULT")) {
				$classe_particolare = "$base_path/package/".$settings['package'].'/persconv/wi400SpoolCvt_'.trim($modulo['MODCLS']).".cls.php";
//				$classe_particolare = "pers/wi400SpoolCvt_".trim($modulo['MODCLS']).".cls.php";
			
				if (file_exists($classe_particolare)) {
					$classe = $classe_particolare;
					$conv = "wi400SpoolConvert_".trim($modulo['MODCLS']);
					$modcls = $modulo['MODCLS'];
				}
				else
					wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'004',
						"Classe $classe_particolare non trovata",$params['email_log_file']);
			}
			include_once $classe;
			
			wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'',
				"Utilizzata classe $conv per convertire il file",$params['email_log_file']);
	
			// Istanzio la classe
			$convert = new $conv($ID, $connzend, $db, trim($atc_rec['MAIATC']));

			if(!$convert->getFile()) {
				wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'005',
					"File da convertire in PDF non trovato",$params['email_log_file']);
				$resultMpx = false;
				if($isBatch) 
					die();
				else 
					return $resultMpx;
			}
			
			$dati_conv = array();	
			$fields = $db->columns('FLOGCONV', Null, True);
//			echo "FIELDS:<pre>"; print_r($fields); echo "</pre><br>";
			$values = array(
				$conv_rec['MAIUSR'],
				$conv_rec['MAIJOB'],
				$conv_rec['MAINBR'],
				$conv_rec['MAIINS'],
//				$dbTime,
				getDb2Timestamp(),
				"",
				"",
//				$modulo['MODCLS'],
				$codice_modulo,
				"",
			);
			
			for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
				$values[] = "";
			}
			
			for($i=1; $i<=$settings['modelli_pdf_user_keys']; $i++) {
				$values[] = "";
			}
			
			$values[] = $atc_rec['MAISTO'];
			$values[] = $atc_rec['MAIOUT'];
			$values[] = $atc_rec['MAISTT'];
			$values[] = 1;
			$values[] = $_SESSION['user'];
			$values[] = $atc_rec['ID'];
			
//			echo "VALUES:<pre>"; print_r($values); echo "</pre><br>";
			$dati_conv = array_combine($fields, $values);
//			echo "FIELD VALUES:<pre>"; print_r($dati_conv); echo "</pre><br>";
			$convert->setDatiConv($dati_conv);
		
			// Carico i parametri dai moduli SIRI
			$dati = $convert->setDatiBySiriModuli($modulo);
			$convert->setDati('AUTORE', 'WI400 By SIRI-Informatica!');
			$convert->setDati('CREATORE', $settings['cliente_installazione']);
			// LZ Setto una eventuale coda di stampa
			if ($atc_rec['MAISTO']!="S") {
				$convert->setDati('OUTQ', $atc_rec['MAIOUT']);
			}
			
			if($atc_rec['MAINAM']!="")
				$convert->setDati('MAINAM', $atc_rec['MAINAM']);
			
			if(isset($form) && $form=="INOLTRO") {
				$convert->setStampa("N");
				$convert->setArchiviazione("N");
			}
				
			$convert->createPdf();

			$do = $convert->convert();
			if($do) {
				wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'000',
					"Conversione del file da inviare ad MPX effettuata con successo",$params['email_log_file']);
				
				$sql_updt = "UPDATE FEMAILAL";
				$sql_updt .= " SET MAIPAT='" . trim($convert->getFullPdfName()) . "'";
				$sql_updt .= " WHERE ID='" . $ID . "' AND MAIATC='" . $atc_rec['MAIATC'] . "'";
				$res_updt = $db->query($sql_updt);
				
				$atc_rec['MAIPAT'] = trim($convert->getFullPdfName());
				
//				echo "CONV:<pre>"; print_r($conv_rec); echo "</pre>";
//				echo "ATC:<pre>"; print_r($atc_rec); echo "</pre>";
				
				// Ridenominazione del file
				if($conv_rec['MAIMPX']=='S') {
					// L'allegato dell'e-mail è il file già inviato ad MPX
					if($atc_rec['CONV']!='S')
						$allega = trim($atc_rec['MAIATC']);
					else
						$allega = trim($atc_rec['MAIPAT']);
				}
				else {
					if($atc_rec['CONV']!='S')
						$allega = trim($atc_rec['MAIATC']);
					else
						$allega = trim($convert->getFullPdfName());
				}
//				echo "ALLEGA: $allega<br>";
				
				if(!file_exists($allega)) {
					$msg = "File: $allega non trovato";
					wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'010',$msg,$params['email_log_file']);
				
					if($isBatch)
						die();
					else
						return false;
				}
				
				if(!empty($atc_rec['MAINAM'])) {
					$renameFile = trim($atc_rec['MAINAM']);
				
					$path_parts = pathinfo($renameFile);
				
					if(!isset($path_parts['extension'])) {
						$msg="Ridenominazione del file ".$allega." fallita";
						wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'002',$msg,$params['email_log_file']);
						if($isBatch)
							die();
						else
							return false;
					}
				
//					$basename = str_replace("/", "-", $renameFile);
				
					$dir = $path_parts['dirname'];
					$basename = $path_parts['basename'];
/*					
					while(!file_exists($dir)) {
						if($dir!="" && $dir!=".") {
							$basename = basename($dir)."-".$basename;
						}
						$dir = dirname($dir);
					}
*/				
					$rename = "";
					if($dir!="" && $dir!=".") {
						$rename = $dir;
					}
					else {
						$dir_al = dirname($allega);
						if($dir_al!="" && $dir_al!=".") {
							$rename = $dir_al;
						}
					}
					
					if(!file_exists($rename))
						wi400_mkdir($rename, 777);
					
					$rename .= "/".$basename;
//					echo "RENAME: $rename<br>";
				
					if($atc_rec['CONV']=='S') {
//						echo "COPY<br>";
						$rinomina = copy($allega, $rename);
					}
					else {
//						echo "RINOMINA<br>";
						$rinomina = rename($allega, $rename);
					}
					chmod($rename, 777);
				
					if($rinomina && file_exists($rename)) {
						$msg="File ".$allega." ridenominato in ".$rename;
						wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'',$msg,$params['email_log_file']);
						$allega = $rename;
					}
					else {
						$msg="Ridenominazione del file ".$allega." fallita";
						wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'002',$msg,$params['email_log_file']);
						if($isBatch)
							die();
						else
							return false;
					}
				}
			}
			else {
				wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'006',
					"Conversione del file da inviare ad MPX fallita",$params['email_log_file']);
				$resultMpx = false;
				if($isBatch) 
					die();
				else 
					return $resultMpx;
			}		
		}
		else if($atc_rec['TPCONV']=='ACTION') {
			echo "<font color='red'>cvtspool_MPX</font> - CONVERT<br>";
			
			// @todo DECIDERE COME PROCEDERE - indicazioni azione da eseguire in BATCHCONTEXT o recuperate da MAIATC?
/*			
			$actionRow = rtvAzione($batchContext->action);
			
			$actionContext->setForm($batchContext->form);
			$actionContext->setGateway($batchContext->gateway);
*/
			$action_parts = explode("&", $atc_rec['MAIATC']);
			
			$action_array = array();
			foreach($action_parts as $str) {
				$parts = explode("=", $str);
				
				$action_array[$parts[0]] = $parts[1];
			}
			echo "ACTION ARRAY:<pre>"; print_r($action_array); echo "</pre>";
/*			
			if(isset($action_array['t'])) {
				$actionRow = rtvAzione($action_array['t']);
				echo "ACTION ROW:<pre>"; print_r($actionRow); echo "</pre>";
			}
			else {
*/			
			if(!isset($action_array['t'])) {
				wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'006',
					"Manca l'indicazione dell'azione da lanciare",$params['email_log_file']);
				$resultMpx = false;
				if($isBatch)
					die();
				else
					return $resultMpx;
			}
/*			
			$actionContext->setType($actionRow["TIPO"]);
			$actionContext->setModule($actionRow["MODULO"]);
			$actionContext->setModel($actionRow["MODEL"]);

			if(isset($action_array['f']))
				$actionContext->setForm($action_array['f']);
			
			if(isset($action_array['g'])) {
				$actionContext->setGateway($action_array['g']);
				echo "SET GATEWAY: ".$actionContext->getGateway()."<br>";
			
//				echo "GATEWAY PARTIAL PATH: ".$actionContext->getGatewayUrl($actionRow["GATEWAY"])."<br>";
			
				require_once p13n($actionContext->getGatewayUrl($actionRow["GATEWAY"]));
			}
			
			require_once p13n($actionContext->getModelUrl($actionRow["MODEL"]));
*/
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
			
			// @todo MODIFICO L'XML CON LA NUOVA AZIONE DA LANCIARE PER LA CONVERSIONE
		
//			echo "XML: $postxml<br>";
		
//			$postxml = utf8_encode($postxml);
		
//			echo "XML: $postxml<br>";
		
			// Creazione del documento DOM per creare script XML
			$dom = new DomDocument('1.0');
			$dom->loadXML($postxml);
			
			$params = $dom->getElementsByTagName('parametro');
			
			$i=0;
			$parametri = array();
			$ad = array("action", "form", "gateway", "fileSave");
			foreach ($params as $param){
				if ($params->item($i)->getAttribute('id')=="fileSave") {
					$parametri[$params->item($i)->getAttribute('id')]=$params->item($i)->getAttribute('value').".1";
				}
				if (in_array($params->item($i)->getAttribute('id'), $ad)) {
					$param->parentNode->removeChild($param);
				}
				$i++;
			}
			
			$parametri['action'] = $action_array['t'];
			
			if(isset($action_array['f']))
				$parametri['form'] = $action_array['f'];
			
			if(isset($action_array['g']))
				$parametri['gateway'] = $action_array['g'];
			
			// Creazione del documento DOM per creare script XML
			//$dom = new DomDocument('1.0');
			
			//$param_1 = $dom->appendChild($dom->createElement('parametri'));
			$param_1 = $dom->getElementsByTagName('parametri');
				
			foreach($parametri as $key => $val) {
				$param_2 = $param_1->item(0)->appendChild($dom->createElement('parametro'));
				$field_name = $dom->createAttribute('id');
				$param_2->appendChild($field_name);
				$name = $dom->createTextNode($key);
				$field_name->appendChild($name);			
				$field_name = $dom->createAttribute('value');
				$param_2->appendChild($field_name);
				$name = $dom->createTextNode($val);
				$field_name->appendChild($name);
			}
			
			// Output XML del documento DOM
			$dom->formatOutput = true;		
			$postxml = $dom->saveXML();
		
//			echo "NEW XML: $postxml<br>";

			$postxml = str_replace(array('<br>','</br>', "\r\n", "\n", "\r"), "", $postxml);
			$postxml = str_replace(array('>  <'), "><", $postxml);
		
//			echo "NEW XML: $postxml<br>";
						
			$fields = array("POSTXML" => $postxml);
			
			$fields_string = "";
			foreach($fields as $key => $value) {
				$fields_string .= $key.'='.$value.'&';
			}
			
			rtrim($fields_string, '&');
//			echo "CURLOPT_POSTFIELDS: $fields_string<br>";
			
			// set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
				
			//execute post
			$result = curl_exec($ch);
			
//			echo "RES_DEL_CURL_LAUNCH: "; print_r($result); echo "<br>";
		
			$marker = "##EMAIL_AZIONE_BATCH_FILE:";
			
			$pos = strpos($result, $marker);
			$pos += strlen($marker);
			
			$string = substr($result, $pos);
			$pos = strpos($string, "##");
			
			$file_batch = substr($string, 0, $pos);
//			echo "FILE BATCH LAUNCH: $file_batch<br>";		
			
//			die("ESECUZIONE_CVTSPOOL_MPX");
				
			//close connection
			curl_close($ch);
				
//			if(isset($_SESSION['EMAIL_AZIONE_BATCH_FILE']) && $_SESSION['EMAIL_AZIONE_BATCH_FILE']!="") {
			if(isset($file_batch) && !empty($file_batch) && file_exists($file_batch)) {			
				wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'000',
					"File generato con successo",$params['email_log_file']);
/*				
				$sql_updt = "UPDATE FEMAILAL";
				$sql_updt .= " SET MAIPAT='" . trim($_SESSION['EMAIL_AZIONE_BATCH_FILE']) . "'";
				$sql_updt .= " WHERE ID='" . $ID . "' AND MAIATC='" . $atc_rec['MAIATC'] . "'";
				$res_updt = $db->query($sql_updt);
*/
				$keyUpdt = array("ID" => $ID, "MAIATC" => $atc_rec['MAIATC']);
				$fieldsValue = array(
					"MAIPAT" => trim($file_batch)
				);
				$stmt_updt = $db->prepare("UPDATE", "FEMAILAL", $keyUpdt, array_keys($fieldsValue));
				$resUpdt = $db->execute($stmt_updt, $fieldsValue);
				
				if(!empty($atc_rec['MAINAM'])) {
					$renameFile = trim($atc_rec['MAINAM']);
				
					$path_parts = pathinfo($renameFile);
				
					if(!isset($path_parts['extension'])) {
						$msg="Ridenominazione del file ".$allega." fallita";
						wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'002',$msg,$params['email_log_file']);
						if($isBatch)
							die();
						else
							return false;
					}
				
//					$basename = str_replace("/", "-", $renameFile);
				
					$dir = $path_parts['dirname'];
					$basename = $path_parts['basename'];
/*
					while(!file_exists($dir)) {
						if($dir!="" && $dir!=".") {
							$basename = basename($dir)."-".$basename;
						}
						$dir = dirname($dir);
					}
*/
					$rename = "";
					if($dir!="" && $dir!=".") {
						$rename = $dir;
					}
					else {
						$dir_al = dirname($allega);
						if($dir_al!="" && $dir_al!=".") {
							$rename = $dir_al;
						}
					}
						
					if(!file_exists($rename))
						wi400_mkdir($rename, 777);
						
					$rename .= "/".$basename;
//					echo "RENAME: $rename<br>";
				
					if($atc_rec['CONV']=='S') {
//						echo "COPY<br>";
						$rinomina = copy($file_batch, $rename);
					}
					else {
//						echo "RINOMINA<br>";
						$rinomina = rename($file_batch, $rename);
					}
					chmod($rename, 777);
				
					if($rinomina && file_exists($rename)) {
						$msg="File ".$file_batch." ridenominato in ".$rename;
						wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'',$msg,$params['email_log_file']);
						$allega = $rename;
					}
					else {
						$msg="Ridenominazione del file ".$file_batch." fallita";
						wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'002',$msg,$params['email_log_file']);
						if($isBatch)
							die();
						else
							return false;
					}
				}
				
				if($isBatch)
					die();
				else
					return true;
			}
			else {
				wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'006',
					"Errore durante la generazione del file!",$params['email_log_file']);
				$resultMpx = false;
				if($isBatch)
					die();
				else
					return $resultMpx;
			}
		}
		
		/*
		 * Chiamare funzioni per altri tipi di conversione
		 * (quella in PDF non è stata inserita in una funzione per non avere problemi dato che in seguito 
		 * servono alcune delle variabili utilizzate)
		 */
	}

	/*
	 * Se si deve eseguire solo la conversione, la tabella FEMAILAL viene aggiornta 
	 * con il path del file convertito e il processo termina
	 */
	if($conv_rec['MAIMPX']!='S' && $conv_rec['MAIEMA']!='S' && $atc_rec['TPCONV']!='ACTION') {
		$sql = "UPDATE FEMAILAL";
		$sql .= " SET MAIPAT='" . trim($convert->getFullPdfName()) . "'";
		$sql .= " WHERE ID='" . $ID . "' AND MAIATC='" . $atc_rec['MAIATC'] . "'";
		$result = $db->query($sql);
		
		$resultConv = true;
		if($isBatch)
			die();
		else
			return $resultConv;
	}
	
	// Invio del file ad MPX
	// Recupero il record impostato nella tabella FMPXPARM
	$sql = "select * from FMPXPARM where ID='$ID'";
	$result = $db->singleQuery($sql);
	$mpx_rec  = $db->fetch_array($result);
	
	if(!$mpx_rec) {
		wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'007',
			"Dati per la creazione dello script XML da inviare ad MPX non trovati",$params['email_log_file']);
		$resultMpx = false;
		if($isBatch) 
			die();
		else 
			return $resultMpx;
	}

	wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'',
		"Genero un file XML per l'invio ad MPX",$params['email_log_file']);
	
	// Istanzio la classe wi400invioMPX
	$invioMPX = new wi400invioMPX($ID, $db, $isBatch, $params);

	/*
	 * Se è stata eseguita la conversione in PDF di un file di spool, bisogna:
	 * - aggioranre la tabella FEMAILAL con il path del PDF generato
	 * - aggiornare la tabella FMPXPARM con il numero di pagine di cui è composto il PDF generato
	 * - rieseguire la query per ottenere in $atc_rec il record aggiornato
	 */
	if(!empty($convert)) {
		$atc_rec = $invioMPX->DbIns_path_npag($atc_rec, trim($convert->getFullPdfName()), $convert->getTotPag());
	}
	else
		// Set del record del file da inviare ad MPX già trovato
		$invioMPX->set_atc_rec($atc_rec);
	
	// Set del record dei dati di conversione/invio
	$invioMPX->set_conv_rec($conv_rec);
		
	// Creazione dello script XML
	$XML = $invioMPX->create_XML();
	
	if($XML)
		$resultMpx = true;
	else
		$resultMpx = false;
}

// Se dopo aver eseguito l'invio ad MPX non si deve inviare anche un'e-mail il processo termina
if($conv_rec['MAIEMA']!='S'){
	if($isBatch) 
		die();
	else
		return $resultMpx;
}

// Invio di un'e-mail
wi400invioEmail::agg_log($ID,'1',$conv_rec['MAIRIS'],'',"Invio di un'email",$params['email_log_file']);

$resultEmail = false;

// Istanzio la classe wi400invioEmail
$invioEmail = new wi400invioEmail($ID, $db, $connzend, $isBatch, $params);	

$invioEmail->set_conv_rec($conv_rec);

if(isset($form) && $form=="INOLTRO") {
	$invioEmail->setStampa("N");
	$invioEmail->setArchiviazione("N");
}

// Invio dell'e-mail
$invio = $invioEmail->invio_email();

$resultEmail = $invio;

// Fine invio
/*if($isBatch) 
	die();
else 
	return $resultEmail;*/
if(!$isBatch)
	return $resultEmail;
//}