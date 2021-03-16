<?php 

/**
 * @name wi400invioConvert 
 * @desc Classe per l'invio di un'e-mail con allegati
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Valeria Porrazzo
 * @version 1.00 26/06/2013
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400invioConvert {
	// Dichiarazione degli attributi della classe
	private $ID;
	private $connDb;
	private $connzend;
	private $isBatch;
	
	private $params = array();
	
	private $user;
	private $pass;
	private $mail_host;
	private $from_name;
	private $SMTPauth;
	private $SMTPSecure;
	private $Port;
	
	private $saveLOG;
	private $log_file;	
//	private $save_path;
	
	private $dati_rec = array();
	
	private $stampa = "S";
	private $archiviazione = "S";
	
	private $zp2oprt = null;
	
	private $timeStamp;
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $ID		: Codice identificativo dell'invio
	 * @param string $connDb	: Connessione al Database
	 * @param string $connzend	: Connessione a Zend
	 */
	public function __construct($ID, $connDb, $connzend, $isBatch=false, $params=array()) {
//	public function __construct($ID, $isBatch=false, $params) {
		global $db, $connzend, $appBase, $settings, $dbTime;
	
		$this->ID = $ID;
		
		$this->connDb = $connDb;
		if(!isset($connDb))
			$this->connDb = $db;
		
		$this->connzend = $connzend;	
		if(!isset($connzend))
			$this->connzend = $connzend;
		
		$this->isBatch = $isBatch;
		
		$this->params = $params;
	
		$this->user = $settings['smtp_user'];
		$this->pass = $settings['smtp_pass'];
		$this->mail_host = $settings['smtp_host'];
		$this->from_name = $settings['smtp_from'];
		$this->SMTPauth = $settings['smtp_auth'];
		$this->SMTPSecure = $settings['smtp_secure'];
		$this->Port = $settings['smtp_port'];
	
		$this->saveLOG = $settings['save_email_log'];
		$this->log_file = $params['email_log_file'];
	
//		$this->save_path = $params['mpx_pdf_path'];

//		$this->timeStamp = $dbTime;
		$this->timeStamp = getDb2Timestamp();
	
		/*
		* Impostazione del debug -
		* true = mostra messaggi di errore a schermo
		* false = non mostra messaggi di errore (default)
		*/
//		$debug = true;
		$debug = $settings['debug'];
		
		// Controllo del debug
		if($debug===true) {
			error_reporting(E_ALL);
			ini_set("display_errors", true);
		}
	
		// Define variable to prevent hacking
		if(!defined('IN_CB'))
			define('IN_CB', true);
	
		// Per evitare problemi di conversione con grossi spool setto la memoria a 20 megabyte massimi
		ini_set("memory_limit","200M");
	}
	
	/**
	 * Distruttore della classe
	 *
	 */
	public function __destruct() {
	
	}
	
	/**
	 * Caricamento nella variabile della classe $this->dati_rec dei dati per l'invio già caricati
	 *
	 * @param array $idrec	: array contenente i dati di invio
	 */
	public function set_dati_rec($dati) {
		$this->dati_rec = $dati;
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
		global $routine_path;
	
		require_once $routine_path."/PHPMailer/class.phpmailer.php";
		
		// Istanzio la classe per l'invio delle e-mail
		$mail = new PHPMailer();
		
//		$mail->CharSet = 'UTF-8';		// @todo Serve?
		
		// Impostazione di Mailer per l'invio di messaggi tramite SMTP
		$mail->IsSMTP();
		
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
		$mail->SMTPSecure = $this->SMTPSecure;
		// Impostazione della port da utilizzare
		$mail->Port = $this->Port;
		
		// Impostazione dell'attributo From della classse PHPMailer
//		$mail->From = trim($this->dati_rec['MAIFRM']);
		$from = $this->dati_rec['MAIFRM'];
		$mail->setFrom($from, $from);
		// Impostazione dell'attributo FromName della classe PHPMailer
		if(!empty($this->dati_rec['MAIALI'])) {
			$mail->FromName = trim($this->dati_rec['MAIALI']); 
		}
		
		// Recupero dei destinatari dell'e-mail
		$sql_dest = "select * from FEMAILDT where ID='{$this->ID}'";
		$res_dest = $this->connDb->query($sql_dest);
		
		if(!$res_dest) {
			$msg="Record ID: {this->ID} non trovato nella tabella FEMAILDT";
			$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'008',$msg,$this->log_file,'E-MAIL');
			
			if($this->isBatch)
				die();
			else
				return false;
		}
		
		while($dest = $this->connDb->fetch_array($res_dest)) {
			if(trim($dest['MATPTO'])=='TO')
				$mail->AddAddress(trim($dest['MAITOR']), trim($dest['MAIALI']));
			else if(trim($dest['MATPTO'])=='CC')
				$mail->AddCC(trim($dest['MAITOR']), trim($dest['MAIALI']));
			else if(trim($dest['MATPTO'])=='BCC' || trim($dest['MATPTO'])=='CCN')
				$mail->AddBCC(trim($dest['MAITOR']), trim($dest['MAIALI']));
			else if(trim($dest['MATPTO'])=='RPYTO')
				$mail->AddReplyTo(trim($dest['MAITOR']), trim($dest['MAIALI']));
			else if(trim($dest['MATPTO'])=='CONTO')
				$mail->ConfirmReadingTo = trim($dest['MAITOR']);
		}
		
		// Impostazione dell'attributo Subject della classe PHPMailer
		$mail->Subject = trim($this->dati_rec['MAISBJ']);
		
		// Recupero di eventuali CONTENUTI (quando in FEMAILAL TPCONV='BODY' e MAIATC='*CONTENTS')
		$sql_body = "select * from FEMAILCT where ID=? AND UCTTYP='BODY'";
		$stmt_body = $this->connDb->singlePrepare($sql_body, 0, true);
		
		// Recupero degli allegati e del body dell'e-mail
		$sql_atc = "select * from FEMAILAL where ID='{$this->ID}'";
		$allegati = $this->connDb->query($sql_atc);
		
		$i = 0;
		while($atc = $this->connDb->fetch_array($allegati)) {
			if(trim($atc['MAIATC'])=="") {
				$msg="Campo allegato vuoto";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'013',$msg,$this->log_file,'E-MAIL');
				
				if($this->isBatch)
					die();
				else
					return false;
			}
			
			// Body dell'e-mail
			if($atc['TPCONV']=='BODY') {
				$body = trim($atc['MAIATC']);
				
				if($body!="*CONTENTS") {
					$msg="Presenza del file BODY $body";
					$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL');
					
					$file_parts = pathinfo($body);
					if(!isset($file_parts['extension']) || !in_array(strtoupper($file_parts['extension']), array("HTM", "HTML", "TXT"))) {
						$msg="Estensione del file $body errata";
						$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'014',$msg,$this->log_file,'E-MAIL');
						continue;
					}
					
					if(file_exists($body)) {
						$handle = fopen($body, "r");
						$contents = fread($handle, filesize($body));
						fclose($handle);
						
						// Impostazione dell'attributo Body della classe PHPMailer
	
						$file_parts = pathinfo($body);
						if(isset($file_parts['extension']) && in_array(strtoupper($file_parts['extension']), array("HTM", "HTML"))) {
							$mail->IsHTML(true);
							
							$msg="Body di tipo ".strtoupper($file_parts['extension']);
							$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL');
						}
						
						$msg="Contenuti: ".trim($contents);
						$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL');
						
						$mail->Body = trim($contents);	
					}
					else {
						$msg="File Body $body non trovato";
						$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'016',$msg,$this->log_file,'E-MAIL');
						
						continue;
					}
				}
				else {
					$msg = "Presenza di CONTENUTI da recuperare";
					$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL');
/*
					$sql_body = "select * from FEMAILCT where ID='".$this->ID."' AND UCTTYP='BODY'";
					$res_body = $this->connDb->singleQuery($sql_body);
					if($row_body = $this->connDb->fetch_array($res_body)) {
*/
					$res_body = $this->connDb->execute($stmt_body, array($this->ID));
					if($row_body = $this->connDb->fetch_array($stmt_body)) {
						$msg="Presenza del Body";
						$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL');
						
						$mail->Body = utf8_decode(trim($row_body['UCTKEY']));
						
						if(isHtml($row_body['UCTKEY'])) {
							$mail->isHTML(True);
						}
						
//						echo "CONTENTS - BODY: ".$mail->Body."<br>";
					}					
					else {
						$msg="Body $body non trovato";
						$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'024',$msg,$this->log_file,'E-MAIL');
						continue;
					}
				}
				
				// Impostazione dell'attributo WordWrap della classe PHPMailer
				//$mail->WordWrap = 50;
				
				continue;
			}
			
			// Allegati
			if($this->dati_rec['MAIEMA']=='S') {
				if($atc['CONV']!='S') {
//					echo "ALLEGATO DIRETTO<br>";
					$allega = trim($atc['MAIATC']);
				}
				else {
//					echo "CONVERSIONE<br>";
					$allega = trim($this->convert_file($atc, "", false));
				}
			}
//			echo "ALLEGATO: $allega<br>";
			
			if(!file_exists($allega)) {
				$msg = "Allegato: $allega non trovato";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'010',$msg,$this->log_file,'E-MAIL');
				
				if($this->isBatch)
					die();
				else
					return false;
			}
			
			// Ridenominazione dell'allegato
			$allega = $this->rename_file($allega, $atc);
//			echo "RENAME ALLEGATO: $allega<br>";
				
			// Compressione dell'allegato
			if($atc['FILZIP']!='S') {
				$mail->AddAttachment($allega);
				
				$msg = "File $allega allegato all'e-mail";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL');
			}
			else {
				$name = explode(".", basename($allega));
//				$zip = $this->save_path . $name[0] . '.zip';

				$zip = wi400File::getUserFile('tmp', $name[0].".zip");
//				echo "ZIP FILE: $zip<br>";
				
				$cmp = $this->compress(array($allega),$zip);
				
				if(file_exists($zip)) {
					$mail->AddAttachment($zip);
					$zipped[$i] = $zip;
					$i++;
					
					$msg = "File $allega compresso ed allegato all'e-mail";
					$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL');
				}
				else {
					$msg="Compressione del file $allega in file $zip fallita";
					$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'017',$msg,$this->log_file,'E-MAIL');
					
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
			$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL');
		}
		$mail->SMTPAutoTLS = False;
		// Creazione ed invio del messagio
		if(!$mail->Send()) {
			// In caso di errore utilizzare l'attributo ErrorInfo della classe PHPMailer per ottenere la descrizione dell'errore
			$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'011',$mail->ErrorInfo,$this->log_file,'E-MAIL');
				
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
				
			$msg = "Mail inviata con successo";
			$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'000',$msg,$this->log_file,'E-MAIL');
				
			return true;
		}
	}
	
	public function convert($atc="") {
		// Recupero degli allegati e del body dell'e-mail
		$sql_atc = "select * from FEMAILAL a where ID='{$this->ID}'";
		
		if($atc!="") {
			$sql_atc .= " and MAIATC='$atc'";
		}
		
		$allegati = $this->connDb->query($sql_atc);
		
		while($row_atc = $this->connDb->fetch_array($allegati)) {
			if($atc!="") {
				$msg = "ALLEGATO: ".$row_atc['MAIATC'];
				$this->write_log($this->ID,'1','','',$msg,$this->log_file,"CONV");
			}
			
			if(trim($row_atc['MAIATC'])=="") {
				$msg="Campo allegato vuoto";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'013',$msg,$this->log_file,'CONV');
		
				if($this->isBatch)
					die();
				else
					return false;
			}
				
			// Body dell'e-mail
			if($row_atc['TPCONV']=='BODY') {
				continue;
			}
			
			if($row_atc['CONV']=="S") {
				$this->convert_file($row_atc);	
			}
			else {
				continue;
			}
		}
	}
	
	public function stampa($outq, $duplex, $atc_key_array = array()) {
		// Recupero degli allegati e del body dell'e-mail
		$sql_atc = "select * from FEMAILAL where ID='{$this->ID}'";
		
		if(!empty($atc_key_array)) {
			$atc_array = array();
			
			foreach($atc_key_array as $key) {			
				$keyArray = explode("|",$key);
				
				$ID = $keyArray[0];
				$atc = $keyArray[1];
				
				$atc_array[] = $atc;
			}
			
			if(!empty($atc_array))
				$sql_atc .= " and MAIATC in ('".implode("', '", $atc_array)."')";
		}
//		echo "SQL ATC: $sql_atc<br>";
		
		$res_atc = $this->connDb->query($sql_atc, false, 0);
		
		while($row_atc = $this->connDb->fetch_array($res_atc)) {
//			echo "ATC:<pre>"; print_r($row_atc); echo "</pre>";

			if(trim($row_atc['MAIATC'])=="") {
				$msg="Campo allegato vuoto";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'013',$msg,$this->log_file,'E-MAIL');
		
				if($this->isBatch)
					die();
				else
					return false;
			}
		
			// Body dell'e-mail
			if($row_atc['TPCONV']=='BODY') {
				continue;
			}
				
			if($row_atc['CONV']!='S') {
				$allega = trim($row_atc['MAIATC']);
				
				$this->stampa_file($allega, $outq, $duplex);
			}
			else
				$allega = trim($this->convert_file($row_atc, $outq));
		}
	}
	
	/**
	 * Conversione in PDF degli allegati e creazione del file XML da spedire ad MPX
	 * 
	 * @return boolean
	 */
	public function mpx_conv() {		
		global $routine_path;
		
		require_once $routine_path."/classi/wi400invioMPX.cls.php";
		
		// Invio del file ad MPX
		// Recupero il record impostato nella tabella FMPXPARM
		$sql_mpx = "select * from FMPXPARM where ID='{$this->ID}'";
		$res_mpx = $this->connDb->singleQuery($sql_mpx);
		$mpx_rec  = $this->connDb->fetch_array($res_mpx);
		
		if(!$mpx_rec) {
			$msg = "Dati per la creazione dello script XML da inviare ad MPX non trovati";
			$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'020',$msg,$this->log_file,"MPX");
				
			if($this->isBatch)
				die();
			else
				return false;
		}
		
		$msg = "Genero un file XML per l'invio ad MPX";
		$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,"MPX");
		
		// Istanzio la classe wi400invioMPX
		$invioMPX = new wi400invioMPX($this->ID, $this->connDb, $this->isBatch, $this->params);
		
		// Set del record dei dati di conversione/invio
		$invioMPX->set_conv_rec($this->dati_rec);
		
		// Recupero degli allegati e del body dell'e-mail
		$sql_atc = "select * from FEMAILAL where ID='{$this->ID}'";
		$allegati = $this->connDb->query($sql_atc);
		
		$i = 0;
		while($atc = $this->connDb->fetch_array($allegati)) {
			if(trim($atc['MAIATC'])=="") {
				$msg="Campo allegato vuoto";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'013',$msg,$this->log_file,'MPX');
		
				if($this->isBatch)
					die();
				else
					return false;
			}
				
			// Body dell'e-mail
			if($atc['TPCONV']=='BODY') {
				continue;
			}
				
			// Allegati
			if($this->dati_rec['MAIMPX']=="S") {
				if($this->dati_rec['MAIEMA']=="S") {
					/*
					 * Nel caso in cui oltre all'esecuzione per l'invio ad MPX sia necessario inviare anche un'e-mail
					 * l'e-mail viene generata ed inviata per prima
					 * quindi anche l'esecuzione delle conversioni dei files viene eseguita durante la generazione dell'e-mail
					 * risulta quindi inutile riconvertire i files per MPX
					 */
					if($atc['MAINAM']!="") {
						$file = $this->get_file_rename($atc);
						
						if(file_exists($file)) {
							$allega = trim($file);
						}
					}
					else if($atc['MAIPAT']!="" && file_exists($atc['MAIPAT'])) {
						$allega = trim($atc['MAIPAT']);
					}
					else if(file_exists($atc['MAIATC'])) {
						$allega = trim($atc['MAIATC']);
					}
				}
				else {
					/*
					 * Nel caso in cui non sia necessario inviare un'e-mail oltre all'esecuzione per l'invio ad MPX
					 * risulta necessario convertire i files per MPX
					 */
					if($atc['CONV']!='S')
						$allega = trim($atc['MAIATC']);
					else {
						$allega = trim($this->convert_file($atc, "", false));
						
						// Se eseguo la conversione devo aggiornare i dati del record del file convertito da passare alla classe wi400invioMPX
						$sql_atc_new = "select * from FEMAILAL where ID='{$this->ID}' and TPCONV<>'BODY' and MAIATC='{$atc['MAIATC']}'";
						$res_atc_new = $this->connDb->singleQuery($sql_atc_new);
						if($atc_new = $this->connDb->fetch_array($res_atc_new)) {
							$atc = $atc_new;
						}
					}
				}
			}
				
			if(!file_exists($allega)) {
				$msg = "Allegato: $allega non trovato";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'010',$msg,$this->log_file,'MPX');
		
				if($this->isBatch)
					die();
				else
					return false;
			}
			
			$invioMPX->set_atc_rec($atc);
		
			// Ridenominazione dell'allegato
			$allega = $this->rename_file($allega, $atc);
			
			$msg = "File $allega pronto per invio ad MPX";
			$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'MPX');
			
			// Creazione dello script XML
			$XML = $invioMPX->create_XML();
			
			if(!$XML) {
				$msg = "Errore durante la creazione del file XML per l'invio ad MPX del file $allega";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'021',$msg,$this->log_file,'MPX');
			
				if($this->isBatch)
					die();
				else
					return false;
			}
		}
		
		if(isset($XML))
			return true;
		else
			return false;
	}
	
	static public function get_file_rename($atc) {
		$file = "";
		if($atc['MAINAM']!="") {
			$file = $atc['MAINAM'];
				
			$file_path = dirname($file);
//			echo "FILE PATH: $file_path<br>";
			if($file_path==".") {
				if(isset($atc['MAIPAT']) && trim($atc['MAIPAT'])!="") {
					$file_path = dirname($atc['MAIPAT']);
					$file = $file_path."/".$file;
				}
				else {
					$file = wi400File::getUserFile('tmp', $file);			// @todo ASSICURARSI CHE VADA BENE QUESTA LOGICA
				}
			}
//			echo "FILE: $file<br>";
		}
		
		return $file;
	}
	
	public function invio_mpx($isBatch) {
		global $routine_path;
		
		require_once $routine_path."/classi/wi400invioMPX.cls.php";
		
		// Istanzio la classe wi400invioMPX
		$invioMPX = new wi400invioMPX("", $this->connDb, $isBatch);
		
		$post_results = $invioMPX->httpPost();
		
		if(!is_string($post_results)){
			if($isBatch)
				die();
		}
		else {
			// Parse della response
			$resultMpx = $invioMPX->parse_XML_res();
		}
		
		if($isBatch)
			die();		
	}
	
	/**
	 * Conversione di un allegato secondo nel tipo di file indicato
	 *
	 * @param string $allegato	: allegato da convertire
	 * @return 	Ritorna il path dell'allegato convertito
	 */
	private function convert_file($allegato, $outq="", $rename=true) {
		if(trim($allegato['TPCONV'])=='PDF') {
			$file_conv = $this->cvtspool($allegato, $outq);
		}
		else if(trim($allegato['TPCONV']=="ACTION")) {
			$file_conv = $this->file_by_launch_batch_action($allegato);
		}
			
		// Ridenominazione dell'allegato
		if($rename===true)
			$file_conv = $this->rename_file($file_conv, $allegato);
			
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
	
//		echo "<font color='blue'>wi400invioConvert.cls.php - file_by_launch_batch_action</font><br>";
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
			$sql = "select * from FEMAILCT where ID='".$ID."' AND UCTTYP='XML'";
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
			
//		die("ESECUZIONE_LAUNCH_BATCH");
			
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
	private function cvtspool($allegato, $outq="") {
		global $dbTime, $routine_path, $settings, $base_path;
		
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

		$sql_mod="select * from SIR_MODULI where MODNAM=?";
		$stmt_mod = $this->connDb->singlePrepare($sql_mod, 0, true);

		if(trim($codice_modulo)!="") {
			$res_mod = $this->connDb->execute($stmt_mod, array($codice_modulo));
			$modulo = $this->connDb->fetch_array($stmt_mod);
		}
//		echo "MODULO:<pre>"; print_r($modulo); echo "</pre>";

		// Se la ricerca nel databse non fornisce risultati allora si usa il modulo di default
		if(!isset($modulo) || !$modulo) {
			$codice_modulo = "*DEFAULT";
			
			$res_mod = $this->connDb->execute($stmt_mod, array($codice_modulo));
			$modulo = $this->connDb->fetch_array($stmt_mod);
			
//			echo "MOD:<pre>"; print_r($modulo); echo "</pre>";
			if($modulo) {
				$msg = "Usato *DEFAULT come modulo di conversione";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL');
			}
			else {
				$msg = "Nessun modulo di conversione applicabile";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'015',$msg,$this->log_file,'E-MAIL');
				
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
		if(isset($modulo) && trim($modulo['MODCLS']!="" && $modulo['MODCLS']!="*DEFAULT")) {
			$classe_particolare = "$base_path/package/".$settings['package'].'/persconv/wi400SpoolCvt_'.trim($modulo['MODCLS']).".cls.php";
//			$classe_particolare = "pers/wi400SpoolCvt_".trim($modulo['MODCLS']).".cls.php";

			if(file_exists($classe_particolare)) {
				$classe = $classe_particolare;
				$conv = "wi400SpoolConvert_".trim($modulo['MODCLS']);
				$modcls = $modulo['MODCLS'];
			}
			else {
				$msg = "Classe $classe_particolare non trovata";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'012',$msg,$this->log_file,'E-MAIL');
			}
		}
		
		include_once $classe;
		
//		echo "MODULO: $modcls - PARAMS:<pre>"; print_r($modulo); echo "</pre>";
		
		$msg = "Utilizzata classe $conv per convertire il file";
		$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL');

		// Istanzio la classe
		$convert = new $conv($this->ID, $this->connzend, $this->connDb, $allegato['MAIATC']);

		if (!$convert->getFile()) {
			$msg = "Allegato:". $allegato['MAIATC']. " non trovato";
			$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'010',$msg,$this->log_file,'E-MAIL');
			
     		if($this->isBatch)
				die();
			else 
				return false;
		}
		
		$dati_conv = array();	
		$fields = $this->connDb->columns('FLOGCONV', Null, True);
//		echo "FIELDS:<pre>"; print_r($fields); echo "</pre><br>";

		$values = array(
			$this->dati_rec['MAIUSR'],
			$this->dati_rec['MAIJOB'],
			$this->dati_rec['MAINBR'],
			$this->dati_rec['MAIINS'],
//			$dbTime,
			$this->timeStamp,
			"",
			"",
			$codice_modulo,
			"",
		);
		
		// Chiavi ricerca
		for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
			$values["LOGKY".$i] = $modulo['MODKY'.$i];
		}
	
		// Chiavi utente
		for($i=1; $i<=$settings['modelli_pdf_user_keys']; $i++) {
			$values["LOGKU".$i] = "";
		}
		
		$values[] = $allegato['MAISTO'];
		$values[] = $allegato['MAIOUT'];
		$values[] = $allegato['MAISTT'];
		$values[] = 1;
		$values[] = $_SESSION['user'];
		$values[] = $allegato['ID'];
		
//		echo "VALUES:<pre>"; print_r($values); echo "</pre><br>";

		$dati_conv = array_combine($fields, $values);
//		echo "FIELD VALUES:<pre>"; print_r($dati_conv); echo "</pre><br>";

		$convert->setDatiConv($dati_conv);

		// Carico i parametri dai moduli SIRI
		$dati = $convert->setDatiBySiriModuli($modulo);
		
		$convert->setDati('AUTORE', 'WI400 By SIRI-Informatica!');
		$convert->setDati('CREATORE', $settings['cliente_installazione']);
		
		// Setto una eventuale coda di stampa
		if ($allegato['MAISTO']!="S") {
			$convert->setDati('OUTQ', $allegato['MAIOUT']);
		}
		
		if($this->stampa=="S") {
			$convert->setDati('OUTQ', $outq);
		}
		
		if($allegato['MAINAM']!="") {
			$convert->setDati('MAINAM', $allegato['MAINAM']);
		}
		
		$convert->setStampa($this->stampa);
		$convert->setArchiviazione($this->archiviazione);
		
		$convert->createPdf();
		$do = $convert->convert();
				
		if($do) {
			$msg = "Conversione effettuata con successo!";
			$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'000',$msg,$this->log_file,'E-MAIL');
		}
		else {
			$msg = "Errore di conversione!";
			$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'018',$msg,$this->log_file,'E-MAIL');
			
			if($this->isBatch)
				die();
			else 
				return false;	
		}
		
		$keyUpdt = array("ID" => $this->ID, "MAIATC" => $allegato['MAIATC']);
		$fieldsValue = array(
			"MAIPAT" => trim($convert->getFullPdfName()),
//			"MAIPAG" => $convert->getTotPag()
		);
		$stmt_updt = $this->connDb->prepare("UPDATE", "FEMAILAL", $keyUpdt, array_keys($fieldsValue));
		$resUpdt = $this->connDb->execute($stmt_updt, $fieldsValue);
		
		if($this->dati_rec['MAIMPX']=="S") {
			$keyUpdtMpx = array("ID" => $this->ID);
			$fieldsValueMpx = array("NUMPAG" => $convert->getTotPag());
			$stmt_updt_Mpx = $this->connDb->prepare("UPDATE", "FMPXPARM", $keyUpdtMpx, array_keys($fieldsValueMpx));
			$resUpdtMpx = $this->connDb->execute($stmt_updt_Mpx, $fieldsValueMpx);
		}
		
		return $convert->getFullPdfName();
	}
	
	/**
	 * Ridenominazione dell'allegato
	 * 
	 * @param unknown_type $allega
	 * @param unknown_type $atc
	 * @return boolean|string
	 */
	private function rename_file($allega, $atc) {
		// L'allegato da ridenominare viene copiato con il nome e il path specificati
		if(!empty($atc['MAINAM'])) {
			$renameFile = trim($atc['MAINAM']);
//			echo "MAINAM: $renameFile<br>";
				
			$path_parts = pathinfo($renameFile);
				
			if(!isset($path_parts['extension'])) {
				$msg="Ridenominazione del file ".$allega." fallita";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'019',$msg,$this->log_file,'E-MAIL');
				if($this->isBatch)
					die();
				else
					return false;
			}
				
			$dir = $path_parts['dirname'];
			$basename = $path_parts['basename'];
//			echo "DIR: $dir - BASENAME: $basename<br>";
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
//			echo "RENAME: $rename<br>";
				
			if($atc['CONV']=='S') {
//				echo "COPY<br>";
				$rinomina = copy($allega, $rename);
			}
			else {
//				echo "RINOMINA<br>";
				$rinomina = rename($allega, $rename);
			}
			chmod($rename, 777);
				
			if($rinomina && file_exists($rename)) {
				$msg="File ".$allega." ridenominato in ".$rename;
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'',$msg,$this->log_file,'E-MAIL');
					
				$allega = $rename;
			}
			else {
				$msg="Ridenominazione del file ".$allega." fallita";
				$this->agg_log($this->ID,'1',$this->dati_rec['MAIRIS'],'019',$msg,$this->log_file,'E-MAIL');
					
				if($this->isBatch)
					die();
				else
					return false;
			}
		}
		
		return $allega;
	}
	
	public function stampa_file($file, $outq, $duplex) {
//		echo "FILE: $file - OUTQ: $outq - DUPLEX: $duplex<br>";
		
		if(!$this->zp2oprt) {
			$this->zp2oprt = new wi400Routine('ZP2OPRT', $this->connzend);
			$this->zp2oprt->load_description('ZP2OPRT');
			$this->zp2oprt->prepare();
		}
		
		$coda = substr($outq,0, 10);
		$libl = substr($outq,10, 10);
/*		
		$sql_outq = "select * FROM FP2OPARM WHERE PROUTQ='$coda'";
		$res_outq = $this->connDb->singleQuery($sql_outq);
		
		$duplex = "N";
		if($row_outq = $this->connDb->fetch_array($res_outq)) {
			$duplex = $row_outq['PRDUPX'];
		}
*/
		$this->zp2oprt->set("PDF", $file);
		$this->zp2oprt->set("OUTQ", $coda);
		$this->zp2oprt->set("LIBL", $libl);
		$this->zp2oprt->set("DUPLEX", $duplex);
		$this->zp2oprt->set("FLAG", "0");
		$this->zp2oprt->call();
	}
	
	/**
	 * Creazione di file .zip
	 *
	 * @param string $srcFileName
	 * @param string $dstFileName
	 */
//	public function compress($srcFileName, $dstFileName, $directory="") {
	public function compress($srcFileName, $dstFileName, $directory="", $exit=true) {
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
		global $db, $messageContext;
		
//		$timeStamp = $dbTime;
		$timeStamp = getDb2Timestamp();
		
		wi400invioConvert::write_log($ID,$stato='1',$err,$log_des,$email_log_file,$tipo=null);
		
		if(!isset($connDb))
			$connDb = $db;
	
		// Aggiornamento del log degli errori nella tabella FPDFCONV
		if($err!='') {
			$risp++;
				
			$keysName = array("ID"=>$ID);
			$fieldsValue = array(
				"MAISTA" => $stato,
				"MAIRIS" => $risp,
				"MAIERR" => $err,
				"MAIDER" => substr($log_des,0,40),
//				"MAIELA" => date("Y-m-d-H.i.s.00000")
//				"MAIELA" => $this->timeStamp
				"MAIELA" => $timeStamp
			);
	
			if(!isset($stmtupdate))
				$stmtupdate  = $connDb->prepare("UPDATE", "FPDFCONV", $keysName, array_keys($fieldsValue));
	
			$result = $connDb->execute($stmtupdate, $fieldsValue);
/*			
			// Visualizzazione del messaggio di errore
			if(!in_array($err, array('000', 'MPX', 'EMA', 'CNV'))) {
				$messageContext->addMessage("ERROR", $log_des);
			}
*/			
		}
	}
	
	static public function write_log($ID,$stato='1',$err,$log_des,$email_log_file,$tipo=null) {
		global $settings;
		
		if(!is_file($email_log_file))
			return;
		
		$log_msg_array = array();
		$event_msg_array = array();
		
		if($settings['save_email_log']===True || ($settings['save_email_log']===False && $err!='')) {
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
		if($settings['save_email_log']===True || ($settings['save_email_log']===False && $err!='')) {
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
//			echo "EVENT MSG: $event_msg<br>";
			
			$notify = array();
			if(isset($settings[$event."_notify"]) && !empty($settings[$event."_notify"]))
				$notify = $settings[$event."_notify"];
		
			signal_event("EMAIL", $event_msg, $notify);
		}
	}
	
//	function prepareInvioEmail($filename="", $contest="", $common="", $campi=array(), $LookUpEmail=true) {
//	public static function prepareInvioEmail($filename="", $contest="", $common="", $campi=array(), $disable=array()) {
//	public static function prepareInvioEmail($id="", $contest="", $common="", $campi=array(), $disable=array(), $isHtml=False) {
	public static function prepareInvioEmail($id="", $contest="", $common="", $campi=array(), $disable=array(), $isHtml=False, $specialConf="") {
		global $db, $settings, $users_table, $appBase;
		
//		echo "prepareInvioEmail - wi400invioConvert<br>";
		
//		echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
		
//		echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
//		echo "DISABLE:<pre>"; print_r($disable); echo "</pre>";
		
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
		
		if(isset($disable['SAVE_DETAIL']) && $disable['SAVE_DETAIL']===false)
			$emailDetail->setSaveDetail(true);
	
		// recupero l'indirizzo e-mail dell'utente loggato
		$userMail = getUserMail($_SESSION['user']);
		
		if(isset($campi['ID'])) {
			// ID
			$myField = new wi400InputText('ID');
			$myField->setLabel('ID');
			$myField->addValidation('required');
			$myField->setReadonly(true);
			$myField->setValue($campi['ID']);
			$myField->setSize(10);
			$myField->setMaxLength(10);
			$emailDetail->addField($myField);
		}
	
		// FROM
		$myField = new wi400InputText('FROM');
		$myField->setLabel(_t("DA"));
		$myField->addValidation('required');
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
		$myField->addValidation('required');
		$myField->setShowMultiple(true);
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
			$myLookUp = new wi400LookUp("LU_GENERICO");
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
			$myLookUp = new wi400LookUp("LU_GENERICO");
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
				$myLookUp = new wi400LookUp("LU_GENERICO");
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
		if(isset($campi['FILE_BODY'])) {
			$myField = new wi400Text('FILE_BODY');
			$myField->setLabel("File testo");
			$myField->setValue($campi['FILE_BODY']);
			$emailDetail->addField($myField);
		}
	
		$myField = new wi400InputTextArea('BODY');
		$myField->setLabel(_t("TESTO"));
		$myField->setSize(75);
		$myField->setRows(5);
		$myField->setInfo(_t("DIGITARE_TESTO"));
		if(isset($campi['BODY']) && $campi['BODY']!="")
			$myField->setValue($campi['BODY']);
		$emailDetail->addField($myField);
	
		if(!isset($disable['INVIO_EMAIL']) || $disable['INVIO_EMAIL']!==true) {
			// Invia
			$myButton = new wi400InputButton('INVIO_BUTTON');
			$myButton->setLabel(_t("INVIA"));
			$myButton->setAction("EMAIL_EDITOR");
			$myButton->setForm("INVIO");
			$myButton->setValidation(true);
			$emailDetail->addButton($myButton);
		}
		else {
			// Inoltra
			$myButton = new wi400InputButton('INOLTRO_BUTTON');
			$myButton->setLabel("Inoltra");
			$myButton->setAction("EMAIL_INOLTRO");
			$myButton->setForm("INOLTRO");
			$myButton->setConfirmMessage("Inoltrare?");
			$myButton->setValidation(true);
			$emailDetail->addButton($myButton);
			
			// Annulla
			$myButton = new wi400InputButton('BACK_BUTTON');
			$myButton->setLabel("Annulla");
			$myButton->setAction("CLOSE");
			$myButton->setForm("CLOSE_LOOKUP");
			$emailDetail->addButton($myButton);
		}
	
		$emailDetail->addParameter("ID_EMAIL_DET", "FILEDWNLD_EMAIL");
		$emailDetail->addParameter("COMMON", $common);
		
		$emailDetail->addParameter("ALLEGATO", $filename);
		$emailDetail->addParameter("CONTEST", $contest);
		
		$emailDetail->addParameter("ISHTML", $isHtml);
		
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
//	public static function invioEmail($from=null,$to_array=array(),$dest_array=array(),$subject,$body=null,$files=array(),$SMTP=array(), $ishtml=False) {
	public static function invioEmail($from=null,$to_array=array(),$dest_array=array(),$subject,$body=null,$files=array(),$SMTP=array(), $ishtml=False, $logFile=False) {
		global $settings, $routine_path;
	
		require_once $routine_path."/PHPMailer/class.phpmailer.php";
	
		if(empty($SMTP)) {
			$host = $settings["smtp_host"];
			$SMTPAuth = (bool) $settings["smtp_auth"];
			$Username = $settings["smtp_user"];
			$Password = $settings["smtp_pass"];
			
			if(!isset($from) || $from=="")
				$from = $settings['smtp_user'];
			
			$SMTPSecure="";
			if(isset($settings['smtp_secure'])) {
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
			if(isset($SMTP['smtp_secure'])) {
				$SMTPSecure = $SMTP['smtp_secure'];
			}
			
			// Impostazione della porta da utilizzare
			if(isset($SMTP['smtp_port']) && $SMTP['smtp_port']!="") {
				$Port = $SMTP['smtp_port'];
			}
		}
		
		// Istanzio la classe per l'invio delle e-mail
		$mail = new PHPMailer();
		
		$mail->CharSet = 'UTF-8';
	
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
		foreach($to_array as $to)
			$mail->AddAddress(trim($to));
			
		// CC
		if(isset($dest_array['CC'])) {
			$cc_array = $dest_array['CC'];
		}
		else {
//			$cc_array = $dest_array;

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
	
		if(isset($dest_array['BCC'])) {
			$bcc_array = $dest_array['BCC'];
				
			if(isset($bcc_array) && !empty($bcc_array)) {
				foreach($bcc_array as $bcc)
					$mail->AddBCC(trim($bcc));
			}
		}
	
		// Impostazione dell'attributo Subject della classe PHPMailer
		$mail->Subject = trim($subject);
	
		// Impostazione dell'attributo Body della classe PHPMailer
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