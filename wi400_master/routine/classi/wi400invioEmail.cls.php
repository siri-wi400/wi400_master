<?php 

/**
 * @name wi400invioEmail 
 * @desc Classe per l'invio di un'e-mail con allegati
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Valeria Porrazzo
 * @version 1.00 27/04/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $routine_path.'/vendor/autoload.php';

class wi400invioEmail {

	// Dichiarazione degli attributi della classe
	private $ID;
	private $connDb;
	private $connzend;
	private $isBatch;
	
	private $user;
	private $pass;
	private $mail_host;
	private $from_name;
	private $SMTPauth;
	
	private $saveLOG;
	private $log_file;
	
	private $save_path;
	
	private $conv_rec;
	
	private $stampa = "S";
	private $archiviazione = "S";
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $ID		: Codice identificativo dell'invio
	 * @param string $connDb	: Connessione al Database
	 * @param string $connzend	: Connessione a Zend
	 */
	public function __construct($ID, $connDb, $connzend, $isBatch=false, $params) {
		global $appBase, $settings;

		$this->ID = $ID;
		$this->connDb = $connDb;
		$this->connzend = $connzend;
		$this->isBatch = $isBatch;
		
		$this->user = $settings['smtp_user'];
		$this->pass = $settings['smtp_pass'];
		$this->mail_host = $settings['smtp_host'];
		$this->from_name = $settings['smtp_from'];
		$this->SMTPauth = $settings['smtp_auth'];
		
		$this->saveLOG = $settings['save_email_log'];
		$this->log_file = $params['email_log_file'];
		
		$this->save_path = $params['mpx_pdf_path'];
		
		/* 
		 * Impostazione del debug -
		 * true = mostra messaggi di errore a schermo
		 * false = non mostra messaggi di errore (default)
		 */   
		$debug = TRUE;
		
		// Define variable to prevent hacking
		if(!defined('IN_CB'))
			define('IN_CB',true);
		
		// Per evitare problemi di conversione con grossi spool setto la memoria a 20 megabyte massimi
		ini_set("memory_limit","200M");

		// Controllo del debug
		if($debug) {
			error_reporting(E_ALL);
			ini_set("display_errors", true);
		}
	}

	/**
	 * Distruttore della classe
	 *
	 */
	public function __destruct() {

	}
	
	// Dichiarazione e definizione dei metodi della classe
	
	/**
	 * Caricamento nella variabile della classe $this->conv_rec dei dati per l'invio già caricati
	 *
	 * @param array $idrec	: array contenente i dati di invio
	 */
	public function set_conv_rec($idrec) {
		$this->conv_rec = $idrec;
	}
	
	public function setStampa($stampa) {
		$this->stampa = $stampa;
	}
	
	public function setArchiviazione($archive) {
		$this->archiviazione = $archive;
	}
	
	/**
	 * Invio di un'e-mail
	 *
	 */
	public function invio_email() {
		global $routine_path, $settings;
		
//		echo "invio_email: ".$this->ID."<br>";
		
		//require_once $routine_path."/PHPMailer/class.phpmailer.php";
		
		// Istanzio la classe per l'invio delle e-mail
		$mail = new PHPMailer();
		// Impostazione di Mailer per l'invio di messaggi tramite SMTP
		$mail->IsSMTP();
		$mail->SMTPAutoTLS = False;
		// Impostazione dell'attributo Host della classe PHPMailer
		$mail->Host = $this->mail_host;
		// Impostazione dell'autorizzazione all'autenticazione
		$mail->SMTPAuth = $this->SMTPauth;
		if($this->SMTPauth == true) {
			// Impostazione dell'attributo Username della classe PHPMailer
			$mail->Username = $this->user;
			// Impostazione dell'attributo Password della classe PHPMailer
			$mail->Password = $this->pass;
		}
		// Impostazione del tipo di conessione ("", "ssl" or "tls")
		$mail->SMTPSecure = $settings['smtp_secure'];
		// Impostazione della port da utilizzare
		if(isset($settings['smtp_port']) && $settings['smtp_port']!="") {
			$mail->Port = $settings['smtp_port'];
		}
		// Impostazione dell'attributo From della classse PHPMailer 
//		$mail->From = trim($this->conv_rec['MAIFRM']);
		$from = trim($this->conv_rec['MAIFRM']);
		$mail->setFrom($from, $from);
		// Impostazione dell'attributo FromName della classe PHPMailer
		if(!empty($this->conv_rec['MAIALI']))
			$mail->FromName = trim($this->conv_rec['MAIALI']);

		// Recupero dei destinatari dell'e-mail
		$sql = "select * from FEMAILDT where ID='" . $this->ID . "'";
		$email_dest = $this->connDb->query($sql);
		
		// Impostazione dei destinatari dell'e-mail
		while($dest = $this->connDb->fetch_array($email_dest)) {
			if(trim($dest['MATPTO'])=='TO')
				$mail->AddAddress(trim($dest['MAITOR']), trim($dest['MAIALI']));
			elseif(trim($dest['MATPTO'])=='CC')
				$mail->AddCC(trim($dest['MAITOR']), trim($dest['MAIALI']));
			elseif(trim($dest['MATPTO'])=='BCC')
				$mail->AddBCC(trim($dest['MAITOR']), trim($dest['MAIALI']));
			elseif(trim($dest['MATPTO'])=='RPYTO')
				$mail->AddReplyTo(trim($dest['MAITOR']), trim($dest['MAIALI']));
			elseif(trim($dest['MATPTO'])=='CONTO')
				$mail->ConfirmReadingTo = trim($dest['MAITOR']);
		}

		// Impostazione dell'attributo Subject della classe PHPMailer 	
		$mail->Subject = trim(utf8_decode($this->conv_rec['MAISBJ']));
		
		// Recupero di eventuali CONTENUTI (quando in FEMAILAL TPCONV='BODY' e MAIATC='*CONTENTS')
		$sql_body = "select * from FEMAILCT where ID=? AND UCTTYP='BODY'";
		$stmt_body = $this->connDb->singlePrepare($sql_body, 0, true);

		// Recupero degli allegati e del body dell'e-mail 
		$sql = "select * from FEMAILAL where ID='" . $this->ID . "'";
		$allegati = $this->connDb->query($sql);
		
		$i = 0;
		while($atc = $this->connDb->fetch_array($allegati)) {
//			echo "ATC:<pre>"; print_r($atc); echo "</pre>";
			
			if(trim($atc['MAIATC'])=="") {
				$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'007',
					"Campo allegato vuoto",$this->log_file,'E-MAIL',$this->connDb);
				if($this->isBatch)
					die();
				else
					return false;
			}
			
			// Body dell'e-mail
			if($atc['TPCONV']=='BODY') {
				$body = trim($atc['MAIATC']);
//				echo "BODY: $body<br>";
				
				if($body!="*CONTENTS") {
					$msg="Presenza del file BODY $body";
					$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL',$this->connDb);
					
					$file_parts = pathinfo($body);
					if(!isset($file_parts['extension']) || !in_array(strtoupper($file_parts['extension']), array("HTM", "HTML", "TXT"))) {
						$msg="Estensione del file $body errata";
						$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL',$this->connDb);
						continue;
					}
					
					if(file_exists($body)) {
						$handle = fopen($body, "r");
						$contents = fread($handle, filesize($body));
						fclose($handle);
						// Impostazione dell'attributo Body della classe PHPMailer
/*					
						$file_parts = explode(".", basename($body));
						if(stristr($file_parts[1],"htm")) {
							$mail->IsHTML(true);
						}
*/
						$file_parts = pathinfo($body);
						if(isset($file_parts['extension']) && in_array(strtoupper($file_parts['extension']), array("HTM", "HTML"))) {
							$mail->IsHTML(true);
							
							$msg="Body di tipo ".strtoupper($file_parts['extension']);
							$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL',$this->connDb);
						}
						$msg="Contenuti: ".trim($contents);
						$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL',$this->connDb);
						$mail->Body = trim($contents);	
					}
					else {
						$msg="File Body $body non trovato";
						$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL',$this->connDb);
						continue;
					}
				}
				else {
					$msg = "Presenza di CONTENUTI da recuperare";
					$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL',$this->connDb);
/*
					$sql_body = "select * from FEMAILCT where ID='".$this->ID."' AND UCTTYP='BODY'";
					$res_body = $this->connDb->singleQuery($sql_body);
					if($row_body = $this->connDb->fetch_array($res_body)) {
*/
					$res_body = $this->connDb->execute($stmt_body, array($this->ID));
					if($row_body = $this->connDb->fetch_array($stmt_body)) {
						$msg="Presenza del Body";
						$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL',$this->connDb);
						
						$mail->Body = utf8_decode(trim($row_body['UCTKEY']));
						if (isHtml($row_body['UCTKEY'])) {
							$mail->isHTML(True);
						}
//						echo "CONTENTS - BODY: ".$mail->Body."<br>";
					}					
					else {
						$msg="Body $body non trovato";
						$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'024',$msg,$this->log_file,'E-MAIL',$this->connDb);
						continue;
					}										
				}
				
				// Impostazione dell'attributo WordWrap della classe PHPMailer
				//$mail->WordWrap = 50;
				continue;
			}
			
			// Allegati
			if($this->conv_rec['MAIMPX']=='S') {
				// L'allegato dell'e-mail è il file già inviato ad MPX
				if($atc['CONV']!='S')
					$allega = trim($atc['MAIATC']);
				else
					$allega = trim($atc['MAIPAT']);
			}
			else {
				if($atc['CONV']!='S')
					$allega = trim($atc['MAIATC']);
				else 
					$allega = trim($this->file_conv($atc));
			}
							
			if(!file_exists($allega)) {
				$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'001',
					"Allegato: $allega non trovato",$this->log_file,'E-MAIL',$this->connDb);
				if($this->isBatch)
					die();
				else
					return false;
			}
			
			/*
			 * Ridenominazione dell'allegato
			 * L'allegato da ridenominare viene copiato con il nome e il path specificati
			 */
/*
			if(!empty($atc['MAINAM'])) {
				$renameFile = trim($atc['MAINAM']);
				$basename = basename($atc['MAINAM']);
				$directory = dirname($renameFile);
//				echo "RENAME FILE: $renameFile<br>";
//				echo "BASENAME: $basename<br>";
//				echo "DIRECTORY: $directory<br>";

				if($atc['CONV']=='S') {
					$rename = wi400File::getUserFile('tmp', $basename);
//					echo "RENAME: $rename<br>";
					
					if($directory!="" && $directory!=".") {
//						echo "COPY<br>";
						$copy = copy($allega, trim($atc['MAINAM']));
					}
					
					$rinomina = rename($allega, $rename); 
					
					if($rinomina && file_exists($rename)) {
						$msg="File ".$allega." ridenominato in ".$rename;
						$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL',$this->connDb);
						$allega = $rename;
					}
					else {
						$msg="Ridenominazione del file ".$allega." fallita";
						$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'002',$msg,$this->log_file,'E-MAIL',$this->connDb);
						if($this->isBatch)
							die();
						else 
							return false;
					}
				}
			}
*/
			if(!empty($atc['MAINAM'])) {
				$renameFile = trim($atc['MAINAM']);
				
				$path_parts = pathinfo($renameFile);
				
				if(!isset($path_parts['extension'])) {
					$msg="Ridenominazione del file ".$allega." fallita";
					$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'002',$msg,$this->log_file,'E-MAIL',$this->connDb);
					if($this->isBatch)
						die();
					else
						return false;
				}
				
//				$basename = str_replace("/", "-", $renameFile);
		
				$dir = $path_parts['dirname'];
				$basename = $path_parts['basename'];
//				echo "DIR: $dir - BASENAME: $basename<br>";
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
//				echo "RENAME: $rename<br>";
				
				if($atc['CONV']=='S') {
//					echo "COPY<br>";
					$rinomina = copy($allega, $rename);
					chmod($rename, 777);
				}
				else {		
//					echo "RINOMINA<br>";
					$rinomina = rename($allega, $rename);
					chmod($rename, 777);
				}
									
				if($rinomina && file_exists($rename)) {
					$msg="File ".$allega." ridenominato in ".$rename;
					$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL',$this->connDb);
					$allega = $rename;
				}
				else {
					$msg="Ridenominazione del file ".$allega." fallita";
					$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'002',$msg,$this->log_file,'E-MAIL',$this->connDb);
					if($this->isBatch)
						die();
					else
						return false;
				}
			}
			
			if($atc['FILZIP']!='S') {
				$mail->AddAttachment($allega);
				$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',
					"File $allega allegato all'e-mail",$this->log_file,'E-MAIL',$this->connDb);
			}
			else {
				$name = explode(".", basename($allega));
				$zip = $this->save_path . $name[0] . '.zip';
				$cmp = $this->compress(array($allega),$zip);
				if(file_exists($zip)) {
					$mail->AddAttachment($zip);
					$zipped[$i] = $zip;
					$i++;
					$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',
						"File $allega compresso ed allegato all'e-mail",$this->log_file,'E-MAIL',$this->connDb);
				}
				else {
					$msg="Compressione del file $allega in file $zip fallita";
					$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'009',$msg,$this->log_file,'E-MAIL',$this->connDb);
					if($this->isBatch)
						die();
					else
						return false;
				}
			}			

		}
		
		if(!isset($mail->Body) || $mail->Body=="") {
			$mail->Body = " ";
			
			$msg="Body non inserito";
			$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL',$this->connDb);
		}
			
		// Creazione ed invio del messagio 
		if(!$mail->Send()) {
			/*
			 * In caso di errore utilizzare l'attributo ErrorInfo della classe PHPMailer per
			 * ottenere la descrizione dell'errore
			 */
			$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'003',
				$mail->ErrorInfo,$this->log_file,'E-MAIL',$this->connDb);
		
			if(!empty($zipped)) {
				foreach($zipped as $v) {
					unlink($v);
				}
			}
			
			return false;
		}
		else {
			if(!empty($zipped)) {
				foreach($zipped as $v) {
					unlink($v);
				}
			}
			
			$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'000',
				"Mail inviata con successo",$this->log_file,'E-MAIL',$this->connDb);

			return true;
		}		
	}

	/**
	 * Conversione di un allegato secondo nel tipo di file indicato
	 *
	 * @param string $allegato	: allegato da convertire
	 * @return 	Ritorna il path dell'allegato convertito
	 */
	private function file_conv($allegato) {
//		echo "file_conv - TPCONV: ".$allegato['TPCONV']."<br>";
		if(trim($allegato['TPCONV'])=='PDF')
			$file_conv = $this->cvtspool($allegato);
		else if(trim($allegato['TPCONV']=="ACTION"))
			$file_conv = $this->file_by_launch_batch_action($allegato);
			
		return $file_conv;
	}
	
	/**
	 * Funzione di generazione di un file attraverso il lancio di un'azione batch
	 *
	 * @param string $allegato	: file di spool da convertire in PDF
	 * @return 	Ritorna il path del file del file PDF convertito
	 */
	private function file_by_launch_batch_action($allegato) {
		global $dbTime, $routine_path, $settings, $base_path;
		global $actionContext, $batchContext, $messageContext;
		global $db, $connzend, $appBase;
	
//		echo "<font color='blue'>wi400invioEmail.cls.php - file_by_launch_batch_action</font><br>";
//		echo "BATCH:<pre>"; echo var_dump($batchContext); echo "</pre>";
		
		// @todo DECIDERE COME PROCEDERE - indicazioni azione da eseguire in BATCHCONTEXT o recuperate da MAIATC?
/*	
		$actionRow = rtvAzione($batchContext->action);
			
		$actionContext->setForm($batchContext->form);
		$actionContext->setGateway($batchContext->gateway);
*/
		$action_parts = explode("&", $allegato['MAIATC']);
			
		$action_array = array();
		foreach($action_parts as $str) {
			$parts = explode("=", $str);
		
			$action_array[$parts[0]] = $parts[1];
		}
//		echo "ACTION ARRAY:<pre>"; print_r($action_array); echo "</pre>";
/*			
		if(isset($action_array['t'])) {
			$actionRow = rtvAzione($action_array['t']);
			echo "ACTION ROW:<pre>"; print_r($actionRow); echo "</pre>";
		}
		else {
*/
		if(!isset($action_array['t'])) {		
			$msg = "Manca l'indicazione dell'azione da lanciare";
			$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'023',$msg,$this->log_file,'E-MAIL',$this->connDb);
			
			if($this->isBatch)
				die();
			else
				return false;
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
			
//			echo "GATEWAY PARTIAL PATH: ".$actionContext->getGatewayUrl($actionRow["GATEWAY"])."<br>";
			
			require_once p13n($actionContext->getGatewayUrl($actionRow["GATEWAY"]));
		}
			
		require_once p13n($actionContext->getModelUrl($actionRow["MODEL"]));
*/
		$ID = $allegato['ID'];
			
//		$url = 'http://127.0.0.1:89'.$appBase.'batch.php';
//		$url = "http://".$_SERVER['SERVER_ADDR'].$appBase.'batch.php';
		$url = "http://".$_SERVER['HTTP_HOST'].$appBase.'batch.php';
//		echo "URL: $url<br>";
			
//		echo "URL: ".curPageURL()."<br>";
//		echo "SERVER HOST: ".$_SERVER['HTTP_HOST']."<br>";
//		echo "SERVER NAME: ".$_SERVER['SERVER_NAME']."<br>";
//		echo "SERVER:<pre>"; print_r($_SERVER); echo "</pre>";
			
		//open connection
		$ch = curl_init();
			
		// Verifico se esiste il file XML
		$xml = "/siri/email/$ID/xml_$ID.xml";
//		echo "XML PATH: $xml<br>";
			
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
		
//		echo "XML: $postxml<br>";
		
//		$postxml = utf8_encode($postxml);
		
//		echo "XML: $postxml<br>";
		
		// Creazione del documento DOM per creare script XML
		$dom = new DomDocument('1.0');
		$dom->loadXML($postxml);
		
		$params = $dom->getElementsByTagName('parametro');
		
		$i=0;
		$parametri = array();
		foreach ($params as $param){
			$parametri[$params->item($i)->getAttribute('id')]=$params->item($i)->getAttribute('value');
			$i++;
		}
		
		$parametri['action'] = $action_array['t'];
		
		if(isset($action_array['f']))
			$parametri['form'] = $action_array['f'];
		
		if(isset($action_array['g']))
			$parametri['gateway'] = $action_array['g'];
		
		// Creazione del documento DOM per creare script XML
		$dom = new DomDocument('1.0');
		
		$param_1 = $dom->appendChild($dom->createElement('parametri'));
		
		foreach($parametri as $key => $val) {
			$param_2 = $param_1->appendChild($dom->createElement('parametro'));
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
		
//		echo "NEW XML: $postxml<br>";

		$postxml = str_replace(array('<br>','</br>', "\r\n", "\n", "\r"), "", $postxml);
		$postxml = str_replace(array('>  <'), "><", $postxml);
		
//		echo "NEW XML: $postxml<br>";
						
		$fields = array("POSTXML" => $postxml);
		
		$fields_string = "";
		foreach($fields as $key => $value) {
			$fields_string .= $key.'='.$value.'&';
		}
		
		rtrim($fields_string, '&');
//		echo "CURLOPT_POSTFIELDS: $fields_string<br>";
		
		// set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
		//execute post
		$result = curl_exec($ch);
			
//		echo "RES_DEL_CURL_LAUNCH: "; print_r($result); echo "<br>";
		
		$marker = "##EMAIL_AZIONE_BATCH_FILE:";
		
		$pos = strpos($result, $marker);
		$pos += strlen($marker);
		
		$string = substr($result, $pos);
		$pos = strpos($string, "##");
		
		$file_batch = substr($string, 0, $pos);
//		echo "FILE BATCH LAUNCH: $file_batch<br>";		
			
//		die("<br>ESECUZIONE_LAUNCH_BATCH");
			
		//close connection
		curl_close($ch);
		
//		if(isset($_SESSION['EMAIL_AZIONE_BATCH_FILE']) && $_SESSION['EMAIL_AZIONE_BATCH_FILE']!="") {
		if(isset($file_batch) && !empty($file_batch) && file_exists($file_batch)) {
//			$file_batch = $_SESSION['EMAIL_AZIONE_BATCH_FILE'];
			
//			echo "file_by_launch_batch_action - <font color='orange'>BATCH FILE: ".$file_batch."</font><br>";
			
			$msg = "File generato con successo!";
			$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'000',$msg,$this->log_file,'E-MAIL',$this->connDb);
/*	
			$sql_updt = "UPDATE FEMAILAL";
			$sql_updt .= " SET MAIPAT='" . trim($_SESSION['EMAIL_AZIONE_BATCH_FILE']) . "'";
			$sql_updt .= " WHERE ID='" . $this->ID . "' AND MAIATC='" . $allegato['MAIATC'] . "'";
			$res_updt = $this->connDb->query($sql_updt);
*/			
			$keyUpdt = array("ID" => $this->ID, "MAIATC" => $allegato['MAIATC']);
			$fieldsValue = array(
				"MAIPAT" => trim($file_batch)
			);
			$stmt_updt = $this->connDb->prepare("UPDATE", "FEMAILAL", $keyUpdt, array_keys($fieldsValue));
			$resUpdt = $this->connDb->execute($stmt_updt, $fieldsValue);
		}
		else {
			$msg = "Errore durante la generazione del file!";
			$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'022',$msg,$this->log_file,'E-MAIL',$this->connDb);
				
			if($this->isBatch)
				die();
			else
				return false;
		}
/*	
		$keyUpdt = array("ID" => $this->ID, "MAIATC" => $allegato['MAIATC']);
		$fieldsValue = array(
//			"MAIPAT" => trim($file_batch),
			"MAINAM" => trim($file_batch)
		);
		$stmt_updt = $this->connDb->prepare("UPDATE", "FEMAILAL", $keyUpdt, array_keys($fieldsValue));
		$resUpdt = $this->connDb->execute($stmt_updt, $fieldsValue);
*/	
		return $file_batch;
	}
	
	/**
	 * Funzione di conversione di file di spool in PDF
	 *
	 * @param string $allegato	: file di spool da convertire in PDF
	 * @return 	Ritorna il path del file del file PDF convertito
	 */
	private function cvtspool($allegato) {
		global $db, $dbTime, $routine_path, $settings, $base_path;
		
		/*
		 * Caricamento del modulo da utilizzare
		 * (il codice modulo rappresenta la struttura con cui deve essere generato il PDF
		 * es: fattura, ...)
		 */
		$codice_modulo = "";
		if(isset($allegato['MAIMOD']) && trim($allegato['MAIMOD'])!="")
			$codice_modulo = $allegato['MAIMOD'];

		// Se il codice modulo non è stato valorizzato lo imposto con il codice argomento.
		if(trim($codice_modulo)=="" && isset($allegato['MAIARG']) && trim($allegato['MAIARG'])!="") 
			$codice_modulo = $allegato['MAIARG'];
		
//		echo "CODICE MODULO: $codice_modulo<br>";

		if(trim($codice_modulo)!="") {
			$sql="select * from SIR_MODULI where MODNAM='$codice_modulo'";
			$result = $this->connDb->singleQuery($sql);
			$modulo = $this->connDb->fetch_array($result);
		}
//		echo "MODULO:<pre>"; print_r($modulo); echo "</pre>";

		// Se la ricerca nel databse non fornisce risultati allora si usa il modulo di default
		if(!isset($modulo) || !$modulo) {
			$codice_modulo = "*DEFAULT";
			$sql="select * from SIR_MODULI where MODNAM='$codice_modulo'";
			$result = $this->connDb->singleQuery($sql);
			$modulo = $this->connDb->fetch_array($result);
//			echo "MOD:<pre>"; print_r($modulo); echo "</pre>";
			if($modulo) {
				$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',
					"Usato *DEFAULT come modulo di conversione",$this->log_file,'E-MAIL',$this->connDb);
			}
			else {
				$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'008',
					"Nessun modulo di conversione applicabile",$this->log_file,'E-MAIL',$this->connDb);
				if($this->isBatch)
					die();
				else
					return false;
			}
		}
		// Istanzio la classe e trovo l'eventuale personalizzazione
		$classe = $routine_path."/classi/wi400SpoolCvt.cls.php";
		$conv = "wi400SpoolConvert";
		$modcls = "*DEFAULT";
		if (isset($modulo) && trim($modulo['MODCLS']!="" && $modulo['MODCLS']!="*DEFAULT")) {
			$classe_particolare = "$base_path/package/".$settings['package'].'/persconv/wi400SpoolCvt_'.trim($modulo['MODCLS']).".cls.php";
//			$classe_particolare = "pers/wi400SpoolCvt_".trim($modulo['MODCLS']).".cls.php";

			if (file_exists($classe_particolare)) {
				$classe = $classe_particolare;
				$conv = "wi400SpoolConvert_".trim($modulo['MODCLS']);
				$modcls = $modulo['MODCLS'];
			}
			else
				$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'004',
					"Classe $classe_particolare non trovata",$this->log_file,'E-MAIL',$this->connDb);
		}
		include_once $classe;
		
//		echo "MODULO: $modcls - PARAMS:<pre>"; print_r($modulo); echo "</pre>";
		
		$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'',
			"Utilizzata classe $conv per convertire il file",$this->log_file,'E-MAIL',$this->connDb);

		// Istanzio la classe
		$convert = new $conv($this->ID, $this->connzend, $this->connDb, $allegato['MAIATC']);

		if (!$convert->getFile()) {
			$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'005',
				"Allegato:". $allegato['MAIATC']. " non trovato",$this->log_file,'E-MAIL',$this->connDb);
     		if($this->isBatch)
				die();
			else 
				return false;
		}
		
		$dati_conv = array();	
		$fields = $db->columns('FLOGCONV', Null, True);
//		echo "FIELDS:<pre>"; print_r($fields); echo "</pre><br>";
		$values = array(
			$this->conv_rec['MAIUSR'],
			$this->conv_rec['MAIJOB'],
			$this->conv_rec['MAINBR'],
			$this->conv_rec['MAIINS'],
			$dbTime,
			"",
			"",
//			$modulo['MODCLS'],
//			$modcls,
			$codice_modulo,
			"",
		);
		
		// Chiavi Ricerca
		for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
			$values["LOGKY".$i] = $modulo['MODKY'.$i];
		}

		// Chiavi utente
		for($i=1; $i<=$settings['modelli_pdf_user_keys']; $i++) {
			$values["LOGKU".$i] = "";
		}
//		echo "USER KEYS:<pre>"; print_r($user_keys); echo "</pre><br>";
		
		$values[] = $allegato['MAISTO'];
		$values[] = $allegato['MAIOUT'];
		$values[] = $allegato['MAISTT'];
		$values[] = 1;
		$values[] = $_SESSION['user'];
		$values[] = $allegato['ID'];
		
//		echo "VALUES:<pre>"; print_r($values); echo "</pre><br>";
		//$dati_conv = array_combine($fields, $values);
//		echo "FIELD VALUES:<pre>"; print_r($dati_conv); echo "</pre><br>";
		// LZ PHP8 da un fatal error se gli array di un combine non hanno gli stessi elementi
		$i=0;
		foreach ($fields as $key => $value) {
			$dati_conv[$key]=$values[$i];
			$i++;
		}
		$convert->setDatiConv($dati_conv);

		// Carico i parametri dai moduli SIRI
		$dati = $convert->setDatiBySiriModuli($modulo);
		$convert->setDati('AUTORE', 'WI400 By SIRI-Informatica!');
		$convert->setDati('CREATORE', $settings['cliente_installazione']);
		// LZ Setto una eventuale coda di stampa
		if ($allegato['MAISTO']!="S") {
			$convert->setDati('OUTQ', $allegato['MAIOUT']);
		}
		
		if($allegato['MAINAM']!="")
			$convert->setDati('MAINAM', $allegato['MAINAM']);
		
		if($this->stampa=="N") {
			$convert->setStampa("N");
		}
		if($this->archiviazione=="N") {
			$convert->setArchiviazione("N");
		}		
		
		$convert->createPdf();
		$do = $convert->convert();
				
		if($do)
			$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'EMA',
				"Conversione effettuata con successo!",$this->log_file,'E-MAIL',$this->connDb);
		else {
			$this->agg_log($this->ID,'1',$this->conv_rec['MAIRIS'],'006',
				"Errore di conversione!",$this->log_file,'E-MAIL',$this->connDb);
			if($this->isBatch)
				die();
			else 
				return false;	
		}
		
		$sql = "UPDATE FEMAILAL";
		$sql .= " SET MAIPAT='" . trim($convert->getFullPdfName()) . "'";
		$sql .= " WHERE ID='" . $this->ID . "' AND MAIATC='" . $allegato['MAIATC'] . "'";
		$result = $this->connDb->query($sql);
		
		return $convert->getFullPdfName();
	}

	/**
	 * Creazione di file .zip
	 *
	 * @param string $srcFileName
	 * @param string $dstFileName
	 */
//	public static function compress($srcFileName, $dstFileName, $directory="") {
	public static function compress($srcFileName, $dstFileName, $directory="", $exit=true) {
		global $settings, $routine_path;
		
		ini_set("memory_limit","1000M");
		set_time_limit(0);
		
//		echo "FILE:"; print_r($srcFileName); echo "</pre>DEST: $dstFileName<br>";
    	
		if(!isset($settings['zip_compress']) || $settings['zip_compress']=="ZipArchive") {
//		if ($settings['OS400']!='V5R3M0') {
//			echo "ZipArchive<br>";			
			checkIfZipLoaded();

			$zip = new ZipArchive();

			if ($zip->open($dstFileName, ZIPARCHIVE::CREATE)!==TRUE) {
				if($exit===true)
	    			exit("cannot open <$dstFileName>\n");
				else 
					return false;
			}
			
			if(isset($directory) && $directory!="") {
				if($zip->addEmptyDir($directory)) {
					$directory .= "/";
//					echo "DIR: $directory<br>";
				}
				else {
					if($exit===true)
						exit("Could not create the directory in the zip file\n");
					else
						return false;
				}
			}
	
			$add_file = "";
			foreach($srcFileName as $file) {
				if(file_exists($file)) {
					$add_file = $directory.basename($file);
//					echo "ADD FILE: $add_file<br>";
					$zip->addFile($file, $add_file);
				}
			}
	
			$zip->close();
			
			if(!file_exists($dstFileName))
				if($exit===true)
					exit("File $dstFileName non creato\n");
				else
					return false;
    	}
    	else if ($settings['zip_compress']=="zip_lib") {
    		if(isset($directory) && $directory!="") {
    			if($exit===true)
    				exit("Could not create the directory in the zip file\n");
    			else
    				return false;
    		}
    		
			include_once $routine_path.'/zip/zip.lib.php';
//			echo "zip_lib<br>";			
	    	$ziper = new zipfile();
	    	$ziper->addFiles($srcFileName);  //array of files
	    	$ziper->output($dstFileName);
//	    	return $dstFileName;
    	}
    	
    	return true;
	}

	/**
	 * Aggiornamento del log
	 *
	 * @param string $ID		: Codice identificativo dell'invio
	 * @param string $err		: Codice identificativo dell'errore
	 * @param string $log_des	: Messaggio di log
	 */
	public static function agg_log($ID,$stato='1',$risp,$err,$log_des,$email_log_file,$tipo=null,$connDb=null) {
		global $db, $settings;
		
		if(!isset($connDb))
			$connDb=$db;
		
		$log_msg_array = array();
		$event_msg_array = array();
				
		if($settings['save_email_log'] == True || ($settings['save_email_log'] == False && $err!='')) {
			$log_msg_array[] = date('D, d M Y H:i:s T');
			
			if(isset($tipo) && $tipo!="")
				$log_msg_array[] = $tipo;
		}
		
		$log_msg_array[] = "ID: $ID";
		$event_msg_array[] = "ID: $ID";
/*			
		switch($tipo) {
			case "":
				if($err!='' && $err!="000" && $err!='CNV')
					$log_msg_array[] = "Error $err";
				break;
			case "E-MAIL":
				if($err!='' && $err!="000" && $err !="EMA")
					$log_msg_array[] = "Error $err";
				break;
		}
*/		
//		if(in_array($tipo, array("", "E-MAIL")) && !in_array($err, array("", "000", "CNV", "EMA"))) {
		if(!in_array($err, array('', '000', 'MPX', 'EMA', 'CNV'))) {
			$log_msg_array[] = "Error $err";
			$event_msg_array[] = "Error $err";
		}
		
		$log_msg_array[] = $log_des;
		$event_msg_array[] = $log_des;
		
		// Aggiornamento del file di log
		if($settings['save_email_log'] == True || ($settings['save_email_log'] == False && $err!='')) {
			$log_msg = implode(" - ", $log_msg_array);
			$log_msg .= "\r\n";
//			echo "LOG MSG: $log_msg<br>";
			
			$dir = dirname($email_log_file);
			if(!file_exists($dir)) {
				wi400_mkdir($dir, 777, true);
			}
			
			// fopen() deve essere impostato ad "a" per scrivere sul file senza però riscrivere la stessa riga
			$log_handle = fopen($email_log_file, "a");
			fwrite($log_handle, $log_msg);
			fclose($log_handle);
		}
		
		// Aggiunta del messaggio di errore alla lista di eventi da notificare
		$event = "event_email";
		if(isset($settings[$event]) && $settings[$event]===true && !in_array($err, array('', '000', 'MPX', 'EMA', 'CNV'))) {
			$event_msg = implode(" - ", $event_msg_array);
			$event_msg .= "\r\n";
			echo "EVENT MSG: $event_msg<br>";
			
			$notify = array();
			if(isset($settings[$event."_notify"]) && !empty($settings[$event."_notify"]))
				$notify = $settings[$event."_notify"];
		
			signal_event("EMAIL", $event_msg, $notify);
		}
		
		// Aggiornamento del log degli errori nella tabella FPDFCONV
		if($err!='') {
			$risp++;
			
			$keysName = array("ID"=>$ID);
			$fieldsValue = array(
				"MAISTA" => $stato,
				"MAIRIS" => $risp,
				"MAIERR" => $err,
				"MAIDER" => substr($log_des,0,40),
				"MAIELA" => date("Y-m-d-H.i.s.00000")
			);
		
			if(!isset($stmtupdate))
				$stmtupdate  = $db->prepare("UPDATE", "FPDFCONV", $keysName, array_keys($fieldsValue));
				
			$result = $connDb->execute($stmtupdate, $fieldsValue);
		}
	}
	
//	function prepareInvioEmail($filename="", $contest="", $common="", $campi=array(), $LookUpEmail=true) {
//	public static function prepareInvioEmail($filename="", $contest="", $common="", $campi=array(), $disable=array()) {		
//	public static function prepareInvioEmail($id="", $contest="", $common="", $campi=array(), $disable=array(), $isHtml=False) {
	public static function prepareInvioEmail($id="", $contest="", $common="", $campi=array(), $disable=array(), $isHtml=False, $specialConf="") {
		global $db, $settings, $users_table, $appBase;
		
//		echo "prepareInvioEmail - wi400invioEmail<br>";
		
//		echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";

//		echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
//		echo "DISABLE:<pre>"; print_r($disable); echo "</pre>";
		
		// Recupero il nome del file per il download
		// Recupero il nome del file dell'ID passato
		$filename = "";
		if($id!="") {
			$filename = get_file_from_id($id);
		}
		
//		echo "PREPARE CAMPI:"; print_r($campi); echo "</pre>";
		
		$emailDetail = new wi400Detail('FILEDWNLD_EMAIL', true);
		if(isset($campi['TITLE']))
			$emailDetail->setTitle($campi['TITLE']);
		else
			$emailDetail->setTitle(_t('EMAIL_FILE_SEND'));
		$emailDetail->isEditable(true);
		
		// recupero l'indirizzo e-mail dell'utente loggato
		$userMail = getUserMail($_SESSION['user']);
		
		// FROM
		$myField = new wi400InputText('FROM');
		$myField->setLabel(_t("DA"));
		$myField->addValidation("required");
		$myField->setMaxLength(100);
		$myField->setSize(50);
		$myField->setInfo(_t("DIGITARE_MITTENTE"));
		if(isset($campi['FROM'])) {
			$myField->setValue($campi['FROM']);
		}
		else {
			if($settings['self_export']===true)
				$myField->setValue($userMail);
			else
				$myField->setValue($settings['smtp_user']);
		}
		if(isset($disable['FROM']) && $disable['FROM']===true)
			$myField->setReadonly(true);
		
		$emailDetail->addField($myField);
		
		// TO
		$myField = new wi400InputText('TO');
		$myField->setLabel(_t("A"));
		$myField->setShowMultiple(true);
		$myField->addValidation("required");
		$myField->setMaxLength(100);
		$myField->setSize(50);
		$myField->setInfo(_t("DIGITARE_DESTINATARIO"));
		if(!isset($campi['TO']))
			$myField->setValue(array($userMail));
		else {
			if(!is_array($campi['TO']))
				$myField->setValue(array($campi['TO']));
			else
				$myField->setValue($campi['TO']);
		}
			
//		if($LookUpEmail===true) {
		if(!isset($disable['LOOK_UP_EMAIL']) || $disable['LOOK_UP_EMAIL']!==true) {
			$myLookUp =new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$users_table);
			$myLookUp->addParameter("CAMPO","EMAIL");
			$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
			$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");	
			$myField->setLookUp($myLookUp);
		}
		
		$emailDetail->addField($myField);
		
		// CC
		$myField = new wi400InputText('CC');
		$myField->setLabel(_t("CC"));
		$myField->setShowMultiple(true);
		$myField->setMaxLength(100);
		$myField->setSize(50);
		$myField->setInfo(_t("DIGITARE_DESTINATARIO"));
		if(isset($campi['CC'])) {
			if(!is_array($campi['CC']))
				$myField->setValue(array($campi['CC']));
			else
				$myField->setValue($campi['CC']);
		}
		
		if(!isset($disable['LOOK_UP_EMAIL']) || $disable['LOOK_UP_EMAIL']!==true) {
			$myLookUp =new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$users_table);
			$myLookUp->addParameter("CAMPO","EMAIL");
			$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
			$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");
			$myField->setLookUp($myLookUp);
		}
		
		$emailDetail->addField($myField);
		
		// BCC
		if(!isset($disable['BCC']) || $disable['BCC']!==true) {
			$myField = new wi400InputText('BCC');
			$myField->setLabel(_t("BCC"));
			$myField->setShowMultiple(true);
			$myField->setMaxLength(100);
			$myField->setSize(50);
			$myField->setInfo(_t("DIGITARE_DESTINATARIO"));
			if(isset($campi['BCC'])) {
				if(!is_array($campi['BCC']))
					$myField->setValue(array($campi['BCC']));
				else
					$myField->setValue($campi['BCC']);
			}
		
			if(!isset($disable['LOOK_UP_EMAIL']) || $disable['LOOK_UP_EMAIL']!==true) {
				$myLookUp =new wi400LookUp("LU_GENERICO");
				$myLookUp->addParameter("FILE",$users_table);
				$myLookUp->addParameter("CAMPO","EMAIL");
				$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
				$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");
				$myField->setLookUp($myLookUp);
			}
			
			$emailDetail->addField($myField);
		}
		
		// Oggetto
		$myField = new wi400InputText('SUBJECT');
		$myField->setLabel(_t("OGGETTO"));
		$myField->setMaxLength(200);
		$myField->setSize(75);
		$myField->setInfo(_t("DIGITARE_OGGETTO"));
		if(isset($campi['SUBJECT']) && $campi['SUBJECT']!="")
			$myField->setValue($campi['SUBJECT']);
		else if($filename!="")
			$myField->setValue("Invio file: ".basename($filename));
		$emailDetail->addField($myField);
		
		// Allegato
		if(!isset($disable['ATC']) || $disable['ATC']!==true) {
/*			
			if($filename!="") {
//				$fileLink = new wi400Text("fileLink", "Allegato", basename($filename), "", $appBase."index.php?t=FILEDWN&FILE_NAME=".urlencode($filename)."&CONTEST=".$contest."&DECORATION=clean&f=".$common);
				$fileLink = new wi400Text("fileLink", "Allegato", basename($filename), "", $appBase."index.php?t=FILEDWN&ID=".urlencode($id)."&CONTEST=".$contest."&DECORATION=clean&f=".$common);
				$emailDetail->addField($fileLink);
			}
*/
			$i = 0;
			if(isset($campi['ALLEGATI']) && !empty($campi['ALLEGATI'])) {
				$myField = new wi400Text('ALLEGATI');
				$myField->setLabel("Allegati");
				$myField->setValue(implode("<br>", $campi['ALLEGATI']));
				$emailDetail->addField($myField);
			}
			else if($filename!="") {
//				$fileLink = new wi400Text("fileLink", "Allegato", basename($filename), "", $appBase."index.php?t=FILEDWN&FILE_NAME=".urlencode($filename)."&CONTEST=".$contest."&DECORATION=clean&f=".$common);
				$fileLink = new wi400Text("fileLink", "Allegato", basename($filename), "", $appBase."index.php?t=FILEDWN&ID=".urlencode($id)."&CONTEST=".$contest."&DECORATION=clean&f=".$common);
				$emailDetail->addField($fileLink);
			
				$file_path = "";
				if($common=="COMMON") {
					$file_path = wi400File::getCommonFile($contest, $filename);
				}
				else{
					if(!empty($contest)) {
						$file_path = wi400File::getUserFile($contest, $filename);
					}
					else {
						$file_path = $filename;
					}
				}
			
				$emailDetail->addParameter("ALLEGATI_PATH_".$i++, $file_path);
			}
				
			if(isset($settings['mail_add_attachments']) && $settings['mail_add_attachments']===true) {
				$myField = new wi400InputFile("IMPORT_FILE");
				$myField->setLabel("Aggiungi Allegato");
				$myField->setOnChange("doSubmit('".$_REQUEST['t']."', '".$_REQUEST['f']."')");
				$emailDetail->addField($myField);
			}
				
			if(isset($campi['ALLEGATI_PATH']) && !empty($campi['ALLEGATI_PATH'])) {
				foreach($campi['ALLEGATI_PATH'] as $file_path) {
					$emailDetail->addParameter("ALLEGATI_PATH_".$i, $file_path);
					$i++;
				}
			}
		}
		
		// Testo
		$myField = new wi400InputTextArea('BODY');
		$myField->setLabel(_t("TESTO"));
		$myField->setSize(75);
		$myField->setRows(5);
		$myField->setInfo(_t("DIGITARE_TESTO"));
		if(isset($campi['BODY']) && $campi['BODY']!="")
			$myField->setValue($campi['BODY']);
		$emailDetail->addField($myField);
		
		$myButton = new wi400InputButton('INVIO_BUTTON');
		$myButton->setLabel(_t("INVIA"));
		if(!isset($campi['ACTION']) || $campi['ACTION']=="")
			$myButton->setAction("INVIO_EMAIL");
		else
			$myButton->setAction($campi['ACTION']);
		if(!isset($campi['FORM']) || $campi['FORM']=="")
			$myButton->setForm("DEFAULT");
		else
			$myButton->setForm($campi['FORM']);
		$myButton->setValidation(true);
		$emailDetail->addButton($myButton);
		
		$emailDetail->addParameter("ID_EMAIL_DET", "FILEDWNLD_EMAIL");
		$emailDetail->addParameter("COMMON", $common);
		$emailDetail->addParameter("CONTEST", $contest);
		$emailDetail->addParameter("ISHTML", $isHtml);
		$emailDetail->addParameter("ALLEGATO", $filename);
		$emailDetail->addParameter("SPECIAL_CONF", $specialConf);
		
		if(isset($campi['CONTO'])) {
			$myField = new wi400InputHidden('CONTO');
			$myField->setValue(serialize($campi['CONTO']));
			$emailDetail->addField($myField);
		}
		
		if(isset($campi['RPYTO'])) {
			$myField = new wi400InputHidden('RPYTO');
			$myField->setValue(serialize($campi['RPYTO']));
			$emailDetail->addField($myField);
		}
		
		if(isset($campi['REQUEST_PARAMS']) && !empty($campi['REQUEST_PARAMS'])) {
			foreach($campi['REQUEST_PARAMS'] as $val) {
				if(isset($_REQUEST[$val]))
					$emailDetail->addParameter($val, $_REQUEST[$val]);
			}
		}
		
		if(isset($campi['PARAMS']) && !empty($campi['PARAMS'])) {
			foreach($campi['PARAMS'] as $key => $val) {
				$emailDetail->addParameter($key, $val);
			}
		}
		
		$emailDetail->dispose();
	}

//	function invioEmail($from=null,$to_array=array(),$cc_array=array(),$subject,$body=null,$files=array(),$SMTP=array(), $ishtml=False) {
	static function invioEmail($from=null,$to_array=array(),$dest_array=array(),$subject,$body=null,$files=array(),$SMTP=array(), $ishtml=False, $logFile=False) {
		global $settings,$routine_path;
		
		//require_once $routine_path."/PHPMailer/class.phpmailer.php";
		
		if(empty($SMTP)) {
			$host = $settings["smtp_host"];
			$SMTPAuth = (bool) $settings["smtp_auth"];
			$Username = $settings["smtp_user"];
			$Password = $settings["smtp_pass"];
			
			if(!isset($from) || $from=="")
				$from = $settings['smtp_user'];
			
			$SMTPSecure="";	
			if (isset($settings['smtp_secure'])) {
				$SMTPSecure = $settings['smtp_secure'];
			}
			
			// Impostazione della porta da utilizzare
			if(isset($settings['smtp_port']) && $settings['smtp_port']!="") {
				$Port = $settings['smtp_port'];
			}
		}
		else {
			$host = $SMTP['mail_host'];
			$SMTPAuth = $SMTP['SMTPauth'];
			$Username = $SMTP['user'];
			$Password = $SMTP['pass'];
			$from = $SMTP['from_name'];
			
			$SMTPSecure="";	
			if (isset($SMTP['smtp_secure'])) {
				$SMTPSecure = $SMTP['smtp_secure'];
			}
			
			// Impostazione della porta da utilizzare
			if(isset($SMTP['smtp_port']) && $SMTP['smtp_port']!="") {
				$Port = $SMTP['smtp_port'];
			}
		}
		// Istanzio la classe per l'invio delle e-mail
		$mail = new PHPMailer(True);
		$mail->CharSet = 'UTF-8';
		$mail->SMTPDebug = 4;
		// Se è HTML
		if ($ishtml) {
			$mail->IsHTML(true);
		}
		// Tentativo di autodetect
		if (isset($body) && isHtml($body)) {
			$mail->isHTML(True);
		}
		// Impostazione di Mailer per l'invio di messaggi tramite SMTP
		$mail->IsSMTP();
	
		// Impostazione dell'attributo Host della classe PHPMailer
		$mail->Host = $host;
		
		// Impostazione dell'autorizzazione all'autenticazione
		$mail->SMTPAuth = $SMTPAuth;
	
		if($mail->SMTPAuth===true) {
			// Impostazione dell'attributo Username della classe PHPMailer
			$mail->Username = $Username;
		
			// Impostazione dell'attributo Password della classe PHPMailer
			$mail->Password = $Password;
		}
		
		// Impostazione del tipo di conessione ("", "ssl" or "tls")
		$mail->SMTPSecure = $SMTPSecure;
		
		// Impostazione della port da utilizzare
		if(isset($Port) && $Port!="") {
			$mail->Port = $Port;
		}
		
		// Impostazione dell'attributo From della classse PHPMailer
//		$mail->From = trim($from);
		$from = trim($from);
		$mail->setFrom($from, $from);
	
		// Impostazione dei destinatari dell'e-mail
		// TO
		foreach($to_array as $to)
			$mail->AddAddress(trim($to));
			
		// CC
		if(isset($dest_array['CC'])) {
			$cc_array = $dest_array['CC'];
		}
		else {
			//$cc_array = $dest_array;
			// Metto solo eventuali chiavi .. non array
			if (is_array($dest_array)) {
				foreach ($dest_array as $xkey => $xvalue) {
					if (!is_array($xvalue)) {
						$cc_array[]=$xvalue;
					}
				}
			}
		}
		
		if(isset($cc_array) && !empty($cc_array)) {
			foreach($cc_array as $cc)
				$mail->AddCC(trim($cc));
		}
		
		// BCC
		if(isset($dest_array['BCC']) && !empty($dest_array['BCC'])) {
			foreach($dest_array['BCC'] as $email) {
				$mail->AddBCC(trim($email));
			}
		}
		
		// RPYTO
		if(isset($dest_array['RPYTO']) && !empty($dest_array['RPYTO'])) {
			foreach($dest_array['RPYTO'] as $email) {
				$mail->AddReplyTo(trim($email));
			}
		}
		
		// CONTO
		if(isset($dest_array['CONTO']) && !empty($dest_array['CONTO'])) {
			foreach($dest_array['CONTO'] as $email) {
				$mail->ConfirmReadingTo = trim($email);
			}
		}
				
		// Impostazione dell'attributo Subject della classe PHPMailer
		$mail->Subject = trim($subject);
				
		// Impostazione dell'attributo Body della classe PHPMailer
	    //$body = utf8_decode($body);
		if(isset($body) && !empty($body))
			$mail->Body = trim($body);
		else
			$mail->Body = " ";
		
		// Impostazione dell'attributo WordWrap della classe PHPMailer
		//$mail->WordWrap = 50;
		
		// Aggiunta dell'allegato
//		echo "INVIO EMAIL FILES:<pre>"; print_r($files); echo "<br>";
		if(isset($files) && !empty($files)) {
			foreach($files as $file_path) {
				$mail->AddAttachment($file_path);
			}
		}
		$mail->SMTPAutoTLS = False;
		
		$sent = $mail->Send();
		
		// Se ho il log devo scrivere anche l'esito del log dopo averla inviata
		if ($logFile==True)	{
			wi400EmailLog::SetByObject($mail, $sent);
		} 
		 
		return $sent;
	}

	
}

?>