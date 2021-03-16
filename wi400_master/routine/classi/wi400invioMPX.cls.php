<?php 

/**
 * @name wi400invioMPX 
 * @desc  Classe per l'invio di uno script XML ad MPX e per la conversione di file di spool in PDF
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Valeria Porrazzo
 * @version 1.00 23/04/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400invioMPX {
	
	// Dichiarazione degli attributi della classe
	private $ID;
	private $connDb;
	private $isBatch;
	
	private $ZCode;
	private $Username;
	private $Password;
	private $WorkProcessID;
	
	private $saveXML;
	private $pathXML;
	private $pathXML_invio;
	private $saveLOG;
	private $log_file;
	private $savePDF;
	private $pathPDF;
	
	private $server;
	private $port;
	private $uri;
	
	private $isTest;
	
	private $XML;
	private $XML_response;
	
	private $fileXML;
	
	private $conv_rec;
	private $atc_rec;
	private $mpx_rec;
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $ID		: Codice identificativo dell'invio
	 * @param string $connDb	: Connessione al Database
	 */
	public function __construct($ID=null, $connDb=null, $isBatch=false, $params=array()) {
		global $appBase, $settings;
		
		$this->ID = $ID;
		$this->connDb = $connDb;
		$this->isBatch = $isBatch;
		
		$this->ZCode = $settings['mpx_ZCode'];
		$this->Username = $settings['mpx_username'];
		$this->Password = $settings['mpx_password'];
		$this->WorkProcessID = $settings['mpx_WorkProcessID'];
		
		$this->saveXML = $settings['save_mpx_xml'];
		$this->pathXML = $params['mpx_xml_path'];
		$this->pathXML_invio = $params['mpx_xml_invio'];
		
		$this->saveLOG = $settings['save_email_log'];
		$this->log_file = $params['email_log_file'];
		
		$this->savePDF = $settings['save_mpx_pdf'];
		$this->pathPDF = $params['mpx_pdf_path'];
		
		$this->server = $settings['mpx_server'];
		$this->port = $settings['mpx_port'];
		$this->uri = $params['mpx_uri'];
		
		$this->isTest = $settings['mpx_test'];
		
		/* 
		 * Impostazione del debug -
		 * true = mostra messaggi di errore a schermo
		 * false = non mostra messaggi di errore (default)
		 */   
		$debug = TRUE;
		
		/* Define variable to prevent hacking */
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
	 * Aggiornamento delle tabelle FEMAILAL e FMPXPARM con il path del file PDF convertito 
	 * ed il suo numero di pagine
	 *
	 * @param string $idrec				: id del lavoro
	 * @param string $filePDF			: nome del file PDF completo di path
	 * @param integer $numPag			: numero di pagine del file PDF
	 * 
	 * @return array
	 */
	public function DbIns_path_npag($idrec, $filePDF, $numPag) {
		$sql = "UPDATE FEMAILAL";
		$sql .= " SET MAIPAT='" . $filePDF . "'";
		$sql .= " WHERE ID='" . $this->ID . "' AND MAIATC='" . $idrec['MAIATC'] . "'";
		$result = $this->connDb->query($sql);

		if($this->get_mpx_rec()) {
			$sql = "UPDATE FMPXPARM";
			$sql .= " SET NUMPAG='" . $numPag . "'";
			$sql .= " WHERE ID='" . $this->ID . "'";
			$result = $this->connDb->query($sql);
		}

		// Una volta aggiornata la tabella FEMAILAL bisogna rieseguire la query per ottenere i dati aggiornati
		return $this->get_atc_rec();
	}
	
	/**
	 * Recupero del file da inviare ad MPX
	 *
	 * @return array	Ritorna il risultato della query
	 */
	public function get_atc_rec() {
		$sql = "select * from FEMAILAL where ID='" . $this->ID . "' AND TPCONV<>'BODY'";
		$result = $this->connDb->query($sql);
		$this->atc_rec = $this->connDb->fetch_array($result);
		
		if(!$this->atc_rec) {
			$this->agg_log_MPX($this->ID, '1', '007', "Record ID: {$this->ID} non trovato nella tabella FEMAILAL");
			if($this->isBatch)
				die();
			else
				return false;
		}
		
		return $this->atc_rec;
	}
	
	/**
	 * Caricamento nella variabile della classe $this->atc_rec del record del file
	 *
	 * @param array $idrec	: array contenente i dati di invio
	 */
	public function set_atc_rec($idrec) {
		$this->atc_rec = $idrec;
	}
	
	/**
	 * Caricamento nella variabile della classe $this->conv_rec del record di conversione/invio
	 *
	 * @param array $idrec	: array contenente i dati di conversione/invio
	 */
	public function set_conv_rec($idrec) {
		$this->conv_rec = $idrec;
	}
	
	/**
	 * Recupero dei dati per l'invio ad MPX
	 *
	 * @return array 	Ritorna il risultato della query
	 */
	public function get_mpx_rec() {
		$sql = "select * from FMPXPARM where ID='" . $this->ID . "'";
		$result = $this->connDb->query($sql);
		$this->mpx_rec = $this->connDb->fetch_array($result);
		
		if(!$this->mpx_rec) {
			$this->agg_log_MPX($this->ID, '1', '009', "Record ID: {$this->ID} non trovato nella tabella FMPXPARM");
			if($this->isBatch)
				die();
			else
				return false;
		}
		
		return $this->mpx_rec;
	}
	
	/**
	 * Creazione del documento DOM per la generazione dell'XML da inviare ad MPX
	 *
	 * @return string	Ritorna lo script XML generato
	 */
	public function create_XML() {
//		$this->get_mpx_rec();
		if(!$this->get_mpx_rec())
			return false;
		
		$dom = new DomDocument('1.0');
		// Non si possono avere più di un elemento di 1° livello
		// Creazione del tag MPX di 1° livello
		$mpx = $dom->appendChild($dom->createElement('MPX'));
		// Creazione del tag Header di 2° livello con attributi
		$header = $mpx->appendChild($dom->createElement('Header'));
		$field_name = $dom->createAttribute('ZCode'); 
		$header->appendChild($field_name);
		$name = $dom->createTextNode($this->ZCode);
		$field_name->appendChild($name);
		$field_name = $dom->createAttribute('Username'); 
		$header->appendChild($field_name); 
		$name = $dom->createTextNode($this->Username);
		$field_name->appendChild($name);
		$field_name = $dom->createAttribute('Password'); 
		$header->appendChild($field_name); 
		$name = $dom->createTextNode($this->Password);
		$field_name->appendChild($name);
		// Creazione del tag Set di 2° livello con attributi
		$set = $mpx->appendChild($dom->createElement('Set'));
		$field_name = $dom->createAttribute('Test'); 
		$set->appendChild($field_name);
		/*
		 * Nel caso in cui il campo TEST della tabella FMPXPARM sia:
		 * 1 allora il blocco Set sarà di tipo test (attributo Test='ON', i dati contenuti nello script XML
		 * saranno importati da MPX, ma la stampa effettiva non avrà luogo)
		 * 0 allora il blocco Set sarà di effettiva produzione (attributo Test='OFF') 
		 */
		if($this->mpx_rec['TEST']=='1')
			$test = 'ON';
		elseif($this->mpx_rec['TEST']=='0')
			$test = 'OFF';
		$name = $dom->createTextNode($test);
		$field_name->appendChild($name);
		$field_name = $dom->createAttribute('CustomerSetID'); 
		$set->appendChild($field_name);
		$name = $dom->createTextNode($this->ID);
		$field_name->appendChild($name);
		// Creazione del tag PDF di 3° livello con attributi
		$pdf = $set->appendChild($dom->createElement('Pdf'));
		$field_name = $dom->createAttribute('NumPages'); 
		$pdf->appendChild($field_name);
		$name = $dom->createTextNode(trim($this->mpx_rec['NUMPAG']));
		$field_name->appendChild($name);
		$field_name = $dom->createAttribute('Type'); 
		$pdf->appendChild($field_name);
		$name = $dom->createTextNode('online');
		$field_name->appendChild($name);
		// Creazione del tag Envelope di 4° livello con attributi
		$envelope = $pdf->appendChild($dom->createElement('Envelope'));
		$field_name = $dom->createAttribute('PageStart'); 
		$envelope->appendChild($field_name);
		$name = $dom->createTextNode('1');
		$field_name->appendChild($name);
		$field_name = $dom->createAttribute('PageEnd'); 
		$envelope->appendChild($field_name);
		$name = $dom->createTextNode(trim($this->mpx_rec['NUMPAG']));
		$field_name->appendChild($name);
		$field_name = $dom->createAttribute('WorkProcessID'); 
		$envelope->appendChild($field_name);
		// Se nel database non è stato inserito un valore per WorkProcessID verrà utilizzato il valore di default 
		if(!empty($this->mpx_rec['WKPRID']))
			$name = $dom->createTextNode(trim($this->mpx_rec['WKPRID']));
		else
			$name = $dom->createTextNode($this->WorkProcessID);
		$field_name->appendChild($name);
		// Creazione del tag Data di 5° livello
		$data = $envelope->appendChild($dom->createElement('Data'));
		// Creazione di tag di 6° livello senza atrributi
		$dati = $data->appendChild($dom->createElement('AddressLine1', trim($this->mpx_rec['ADDR1'])));
		$dati = $data->appendChild($dom->createElement('AddressLine2', trim($this->mpx_rec['ADDR2'])));
		$dati = $data->appendChild($dom->createElement('AddressLine3', trim($this->mpx_rec['ADDR3'])));
		$dati = $data->appendChild($dom->createElement('CAP', trim($this->mpx_rec['CAP'])));
		$dati = $data->appendChild($dom->createElement('City', trim($this->mpx_rec['CITTA'])));
		$dati = $data->appendChild($dom->createElement('LocalCode', trim($this->mpx_rec['PROV'])));
		$dati = $data->appendChild($dom->createElement('Country', trim($this->mpx_rec['NAZ'])));
		/*
		 * Creazione del tag PdfByteCode di 4° livello senza attributi
		 * con inserimento del ByteCode del PDF codificato in base 64
		 */
		if($pdf_encoded = $this->encode_PDF())
			$pdfbytecode = $pdf->appendChild($dom->createElement('PdfByteCode64', $pdf_encoded));
		else
			return false;

		// Output XML del documento DOM
		$dom->formatOutput = true;
		$this->XML = $dom->saveXML();
		
		if($this->saveXML)
			$this->save_XML_file();
		
		return $this->XML;
	}
	
	/**
	 * Codifica del file PDF in base64
	 *
	 * @return string	Ritorna una stringa con la codifica in base64 del file PDF
	 */
	private function encode_PDF() {	
		// Verifica dell'esistenza del file
		if($this->atc_rec['MAIPAT'])
			$file = trim($this->atc_rec['MAIPAT']);
		else 
			$file = trim($this->atc_rec['MAIATC']);
		
		if (!file_exists($file)) {
			// Scrittura in un file di log del motivo del fallimento dell'operazione
			$this->agg_log_MPX($this->ID, '1', '032', "File PDF da inviare ad MPX inesistente");
			if($this->isBatch)
				die();
			else
				return false;
		}
		
		// Codifica del file PDF in base64 per evitare errori
		$handle = fopen($file, "r");
		$contents = fread($handle, filesize($file));
		$encoded = base64_encode($contents);
		// Si potrebbe dividere il codice del file PDF, codificato in base64, in blocchi più piccoli, detti chunks  
//		$encoded = chunk_split($encoded);
		fclose($handle);
		
		return $encoded;
	}

	/**
	 * Salvataggio del file XML
	 *
	 */
	private function save_XML_file() {
		if($this->saveXML) {
			$nameXML = $this->ID . "_" . date('YmdHisu') . "_MPX.xml";
			$fileXML = $this->pathXML . $nameXML;
			$handle = fopen($fileXML, "w");
			fwrite($handle, $this->XML);
			fclose($handle);

			// Log di fine generazione XML
			$handle = fopen($fileXML, "r");
			if($handle) {
				fclose($handle);
				$this->agg_log_MPX($this->ID, '1', 'MPX', "Generato file XML: $nameXML");
			}
			else {
				fclose($handle);
				$this->agg_log_MPX($this->ID, '1', '023', "File XML non generato");
			}
		}
	}
	
	/**
	 * Raggruppamento dei singoli file XML in un file XML unico per l'invio ad MPX
	 *
	 * @return boolean	Ritorna true se la generazione del file XML comune è andata a buon fine 
	 */
	private function prepare_XML_file() {
		global $routine_path;
		
		ini_set("memory_limit","200M");
		
		$classe = "concat_pdf.php";
		require_once $routine_path . "/FPDF/".$classe;

//		if(!file_exists($this->pathXML_invio)) {
//			wi400_mkdir($this->pathXML_invio);
//		}		
		
		$dir_handle = opendir($this->pathXML);
		
		if($dir_handle===false) {
			// Scrittura in un file di log del motivo del fallimento dell'operazione
			$this->agg_log_MPX($this->ID, '1', '032', "Errore nella lettura della cartella dei files XML");
			if($this->isBatch)
				die();
			else
				return false;
		}

		// Apertura del file XML in cui raggruppare quelli singoli
		$nameXML = "Invio_" . date('YmdHisu') . "_MPX.xml";
		$this->fileXML = $this->pathXML_invio.$nameXML;

		$handle_XML = fopen($this->fileXML, "a");
		
		$XML_array = array();
		// Recupero dei file XML della directory
		while(($file = readdir($dir_handle))!==false) {
			$path_parts = pathinfo($file);
			if(isset($path_parts['extension'])) {
				$ext = $path_parts['extension'];
				if($file!="." && $file!=".." && $ext == "xml" && strncmp($file,$nameXML,6)!=0) {
					$file_path = $this->pathXML.$file;
					$XML_array[] = $file_path;
				}
			}
		}

		$isFirst = true;
		$page_start = 1;
		$count = 0;
		$numIDEnvelope=0;
		$array_concat = array();
		
		// Concatenazione dei files XML
		foreach($XML_array as $file_path) {
			$file_handle = fopen($file_path, "r+");
			
			// Lock del file XML
			if(flock($file_handle, LOCK_EX)) {
				$contents = "";
				// Lettura del file XML recuperato
				while(!feof($file_handle)) {
	 				$contents .= fread($file_handle, 8192);
				}
				
				// Parse del file XML
				// Carico l'XML e comincio a parsarlo
				$dom_xml = new DomDocument('1.0');
				
				// Gestione degli errori con chiamata alla funzione errorHandler()
				$error = "";
				set_error_handler(array($this, "errorHandler"));
				
				// Caricamento del corpo della response XML in un DOM 
				$dom_xml->loadXML($contents);
				
				restore_error_handler();
				if($error!="") 
					throw new SoapFault('wi400WsSiriAtg', "XML non valido:" . $error);
				
				// Estrazione dei dati di interesse dalla response XML
				$params = array();
				$params = $this->parse_XML_file($dom_xml);
				$ByteCode64 = $this->parse_PdfByteCode64($dom_xml);
	
				if(!$params || !$ByteCode64) 
					throw new SoapFault('wi400WsSiriAtg', 'XML non contiene parametri validi oppure incompleto');
	
				if($isFirst===true) {
					$dom = new DomDocument('1.0');
					// Non si possono avere più di un elemento di 1° livello 
					// Creazione del tag MPX di 1° livello
					$mpx = $dom->appendChild($dom->createElement('MPX'));
					// Creazione del tag Header di 2° livello con attributi
					$header = $mpx->appendChild($dom->createElement('Header'));
					$field_name = $dom->createAttribute('ZCode'); 
					$header->appendChild($field_name);
					$name = $dom->createTextNode($params['ZCode']);
					$field_name->appendChild($name);
					$field_name = $dom->createAttribute('Username'); 
					$header->appendChild($field_name); 
					$name = $dom->createTextNode($params['Username']);
					$field_name->appendChild($name);
					$field_name = $dom->createAttribute('Password'); 
					$header->appendChild($field_name); 
					$name = $dom->createTextNode($params['Password']);
					$field_name->appendChild($name);
					// Creazione del tag Set di 2° livello con attributi 
					$set = $mpx->appendChild($dom->createElement('Set'));
					$field_name = $dom->createAttribute('Test'); 
					$set->appendChild($field_name);
					$name = $dom->createTextNode($params['Test']);
					$field_name->appendChild($name);
					$field_name = $dom->createAttribute('CustomerSetID'); 
					$set->appendChild($field_name);
					$name = $dom->createTextNode($params['CustomerSetID']);
					$field_name->appendChild($name);
					// Creazione del tag PDF di 3° livello con attributi 
					$pdf = $set->appendChild($dom->createElement('Pdf'));
					$field_name = $dom->createAttribute('NumPages'); 
					$pdf->appendChild($field_name);
					$name = $dom->createTextNode($params['NumPages']);
					$field_name->appendChild($name);
					$field_name = $dom->createAttribute('Type'); 
					$pdf->appendChild($field_name);
					$name = $dom->createTextNode('online');
					$field_name->appendChild($name);
				}
			
				$page_end = $page_start+$params['NumPages']-1;
				
				// Creazione del tag Envelope di 4° livello con attributi 
				$envelope = $pdf->appendChild($dom->createElement('Envelope'));
				$field_name = $dom->createAttribute('PageStart'); 
				$envelope->appendChild($field_name);
				$name = $dom->createTextNode($page_start);
				$field_name->appendChild($name);
				$field_name = $dom->createAttribute('PageEnd'); 
				$envelope->appendChild($field_name);
				$name = $dom->createTextNode($page_end);
				$field_name->appendChild($name);
				$field_name = $dom->createAttribute('WorkProcessID'); 
				$envelope->appendChild($field_name);
				$name = $dom->createTextNode($params['WorkProcessID']);
				$field_name->appendChild($name);
				// Chiave per recupero informazioni
				$field_name = $dom->createAttribute('CustomerEnvelopeID'); 
				$envelope->appendChild($field_name);
				$name = $dom->createTextNode($numIDEnvelope++);
				$field_name->appendChild($name);				
				// Creazione del tag Data di 5° livello 
				$data = $envelope->appendChild($dom->createElement('Data'));
				// Creazione di tag di 6° livello senza atrributi 
				$dati = $data->appendChild($dom->createElement('AddressLine1', $params['AddressLine1']));
				$dati = $data->appendChild($dom->createElement('AddressLine2', $params['AddressLine2']));
				$dati = $data->appendChild($dom->createElement('AddressLine3', $params['AddressLine3']));
				$dati = $data->appendChild($dom->createElement('CAP', $params['CAP']));
				$dati = $data->appendChild($dom->createElement('City', $params['City']));
				$dati = $data->appendChild($dom->createElement('LocalCode', $params['LocalCode']));
				$dati = $data->appendChild($dom->createElement('Country', $params['Country']));
				
				// Decodifica e salvataggio del codice in base64 del PDF
				$decoded = base64_decode($ByteCode64);
				// Salvataggio del file PDF
				$namePDF = "File_".date('YmdHisu')."_".$count."_MPX.pdf";
				$filePDF = $this->pathPDF.$namePDF;
				// Inserimento del nome del PDF in un array per mantenere l'ordine di concatenamento 
				// congruente con l'ordine dei dati nel file XML
				$handle = fopen($filePDF, "w");
				if(fwrite($handle, $decoded)!==false)
					$array_concat[] = $filePDF;
				else {
					// Scrittura in un file di log del motivo del fallimento dell'operazione
					$this->agg_log_MPX($this->ID, '1', '031', "Errore nella decodifica del PDF del file XML: $XMLfile_path per la concatenazione dei PDF");
					if($this->isBatch)
						die();
					else
						return false;
				}			
				fclose($handle);
	
				// Chiusura del file XML letto
				fclose($file_handle);

				$isFirst = false;
				$count++;
				$page_start += $params['NumPages'];
				// Eliminazione del file XML letto
				unlink($file_path);
			}
			else {
				fclose($file_handle);
			}
		}
		closedir($dir_handle);
		
		// Sostituzione dell'attributo NumPages con il totale reale delle pagine del documento PDF concatenato
		// Cerco se c'è il tag resource 
		$params = $dom->getElementsByTagName('resource'); // Find Sections
		// se non c'è cerco il tag PdfByteCode64 
		if(!isset($params->item(0)->nodeValue) or ($params->item(0)->nodeValue)=="") 
			$params = $dom->getElementsByTagName('Pdf'); // Find Sections
		// Se non ho trovato nulla errore
		if(!isset($params)) 
			return false;

		$params->item(0)->setAttribute('NumPages', $page_end);
		
		// Concatenazione dei file PDF
		$dir_handle = opendir($this->pathPDF);
		
		if($dir_handle===false) {
			// Scrittura in un file di log del motivo del fallimento dell'operazione
			$this->agg_log_MPX($this->ID, '1', '033', "Errore nella lettura della cartella dei files PDF");
			if($this->isBatch)
				die();
			else
				return false;
		}

		// Apertura del file PDF in cui raggruppare quelli singoli
		$namePDF = "Invio_" . date('YmdHisu') . "_MPX.pdf";
		$filePDF = $this->pathPDF.$namePDF;		
		
		$pdf_concat =new concat_pdf();   
		$pdf_concat->setFiles($array_concat);   
		$pdf_concat->concat();   
		$pdf_concat->Output($filePDF, 'F'); 
	
		foreach($array_concat as $pdf_file) {
			unlink($pdf_file);
		}

		// Codifica del file PDF concatenato in base 64
		if (!file_exists($filePDF)) {
			/* Scrittura in un file di log del motivo del fallimento dell'operazione */
			$this->agg_log_MPX($this->ID, '1', '024', "File PDF da inviare ad MPX inesistente");
			if($this->isBatch)
				die();
			else
				return false;
		}
		
		$handle = fopen($filePDF, "r");
		$contents = fread($handle, filesize($filePDF));
		$encoded = base64_encode($contents);
		// Si potrebbe dividere il codice del file PDF, codificato in base64, in blocchi più piccoli, detti chunks  
//		$encoded = chunk_split($encoded);
		fclose($handle);
		unlink($filePDF);
		
		// Inserimento del file PDF codificato in base 64 nel codice XML
		$pdfbytecode = $pdf->appendChild($dom->createElement('PdfByteCode64', $encoded));
		
		$ByteCode64 = $this->parse_PdfByteCode64($dom);
		
		if($this->savePDF===true) {
			// Decodifica e salvataggio del codice in base64 del PDF
			$decoded = base64_decode($ByteCode64);
			// Salvataggio del file PDF
			$namePDF = "Decode_".date('YmdHisu')."_MPX.pdf";
			$filePDF = $this->pathPDF . $namePDF;
			$handle = fopen($filePDF, "w");
			fwrite($handle, $decoded);
			fclose($handle);
		}

		// Output XML del documento DOM
		$dom->formatOutput = true;
		$this->XML = $dom->saveXML();

		fwrite($handle_XML, $this->XML);	
				
		fclose($handle_XML);

		return true;
	}
	
	/**
	 * Invio del file XML ad MPX
	 *
	 * @return string/boolean		Ritorna il messaggio della response, false se ci sono errori
	 */
	public function httpPost() {
		// Raggruppamento dei singoli file XML in un file XML unico per l'invio ad MPX
		if(!$this->prepare_XML_file())
			return false;
		
		// Controllo dei paramentri di connessione al server a cui si devono inviare i dati
    	if(empty($this->server))         { return false; }
    	if(!is_numeric($this->port)) { return false; }
    	if(empty($this->uri))        { return false; }
    	if(empty($this->XML))    { return false; }

	    /*
		 * Creazione dell'header del post in un array
		 * Il post (cioè l'invio dei dati) ad un server deve essere corredato da un header 
		 * contente le informazioni sul post
		 */
    	$t = array();
    	$t[] = 'POST ' . $this->uri . ' HTTP/1.0';
    	$t[] = 'Content-Type: text/html';
    	$t[] = 'Host: ' . $this->server . ':' . $this->port;
    	$t[] = 'Content-Length: ' . strlen($this->XML);
    	$t[] = 'Connection: close';
		$t = implode("\r\n",$t) . "\r\n\r\n" . $this->XML;
	
		// Open socket, provide error report vars and timeout of 10 seconds.
    	/*
		 * Apertura del socket (cioè della connessione al server)
		 * La connessione al socket avviene attraverso la generazione di un handle "$fp" (cioè un puntatore al socket)
		 * (come per la funzione fopen() per l'apertura di un file) 
		 */
		$fp = @fsockopen($this->server,$this->port,$errno,$errstr,10);
	
		/*
		 * Se la funzione get_resource_type() ha valore diverso da 'stream' vuol dire che
		 * la connessione non ha avuto luogo e viene quindi annullata l'operazione
		 */
		if(!(get_resource_type($fp) == 'stream')) {
			$msg = "Errore in apertura del socket TCP/IP: $this->server:$this->port - $errno $errstr";
			$this->agg_log_MPX($this->ID, '1', '025', $msg);
			fclose($fp);
			return false; 
		}

		$msg = "Accesso al socket TCP/IP: $this->server:$this->port eseguito con successo";
		$this->agg_log_MPX($this->ID, '1', '', $msg);
	
		/*
		 * Invio dell'header e del contenuto del post (i dati da inviare)
		 * E' come se l'handle del socket puntasse ad un file, quindi per passare i dati al server basta
		 * eseguire la scrittura dei dati sul socket, ci pensa poi il server stesso ad interpretare corretamente
		 * l'operazione
		 */
		if(!fwrite($fp, $t)) {
			$this->agg_log_MPX($this->ID, '1', '026', "Errore durante l'invio del post");
			fclose($fp);
        	return false;
		}
	
		$this->agg_log_MPX($this->ID, '1', 'MPX', "Post dei dati avvenuto con successo");

		// Lettura della response (viene inserita in $rsp)
    	$rsp = '';
    	/*
    	 * Dal punto in cui si trova, fino alla fine del file il puntatore al file (socket) punta alla response
    	 * feof() funzione di test for end-of-file eseguita su un puntatore ad un file
    	 * fgets() ritorna il contenuto del file a cui si fa riferimento in una stringa di lunghezza indicata
    	 */
		while(!feof($fp)) { $rsp .= fgets($fp,8192); }
		
		// Salvataggio della response per future consultazioni
		$nameXML = "RESPONSE_" . date('YmdHisu') . "_MPX.xml";
		$fileXML = $this->pathXML_invio. $nameXML;
		$handle = fopen($fileXML, "w");
		fwrite($handle, $rsp);
		fclose($handle);
	
		// Chiusura della connessione al socket (proprio come si deve chiudere la connessione ad un file)
    	fclose($fp);
    
    	// Call parseHttpResponse() to return the results.
		$this->XML_response = $this->parseHttpResponse($rsp);
		
		return $this->XML_response;
	}
	
	/**
	 * Accetta il contenuto http fornito, controlla se si tratta di una response http valida,
	 * se necessario fa l'unchunk del body
	 *
	 * @param string $content	: il contenuto http ricevuto (body ed header)
	 * @return string/boolean	Ritorna il contenuto http senza gli headers, false se ci sono errori
	 */
	private function parseHttpResponse($content=null) {
		if(empty($content)) { 
			$this->agg_log_MPX($this->ID, '1', '027', "Response non ricevuta");
			return false; 
		}
    
		// Divisione della stringa passata nei suoi elementi costitutivi: header e contenuto del post
	    $hunks = explode("\r\n\r\n",trim($content));

		/*
		 * Controllo del numero di pezzi di cui è composta la response
		 * (non possono essere meno di 2, tranne che nel caso di prova, in cui la response
		 * viene simulata, dove non possono essere meno di 3, in quanto viene aggiunto
		 * un header in più per simulare quello della response) 
		 */
	    if($this->isTest == true)
	    	$pezzi = 3;
	    else
	    	$pezzi = 2;
	    
		if(!is_array($hunks) or count($hunks) < $pezzi) {
    		$this->agg_log_MPX($this->ID, '1', '028', "Response incompleta");
    		return false;
		}
    	$header = $hunks[count($hunks) - 2];
    	$body   = $hunks[count($hunks) - 1];
    
    	// Esplosione del blocco header nelle sue componenti in un ulteriore array di stringhe
    	$headers = explode("\n",$header);
     
	    // Distruzione delle variabili array ed header in quanto non servono più
    	unset($hunks);
    	unset($header);
    
    	// Validazione della response ricevuta attraverso il controllo dell'header
		if (!$this->validateHttpResponse($headers)) {
			$this->agg_log_MPX($this->ID, '1', '029', "Messaggio di errore - " . trim(($headers[0])));
			return false; 
		}

		$this->agg_log_MPX($this->ID, '1', '', "Validazione eseguita con successo");
    
    	/*
		 * Se nell'header di un post viene indicato "Transfer-Coding: chunked" vuol dire che 
		 * la stringa di dati (del contenuto del post) trasmessa è stata divisa in pezzi più piccoli
		 */
    	// in_array() funzione che controlla la presenza del valore indicato all'interno di un array
    	if(in_array('Transfer-Coding: chunked',$headers)) {
    	    return trim($this->unchunkHttpResponse($body));
		} 
		else {
        	return trim($body);
		}
	}

	/**
	 * Validazione della response ricevuta attraverso il controllo dell'header
	 *
	 * @param string $headers	: il contenuto degli headers della response http
	 * @return boolean	Ritorna true se si tratta di un messaggio di ok, false se si tratta di un messaggio di errore
	 */
	private function validateHttpResponse($headers=null) {
		if(!is_array($headers) or count($headers) < 1) { return false; }
    
		/*
		 * Controllo del contenuto della prima riga dell'header della response
		 * Le formule indicate indicano una response di esecuzione del post avvenuta con successo 
		 */
    	switch(trim(strtolower($headers[0]))) {
        	case 'http/1.0 100 ok':
        	case 'http/1.0 200 ok':
        	case 'http/1.1 100 ok':
        	case 'http/1.1 200 ok':
				return true;
        	break;
		}
    	return false;
	}

	/**
	 * Unchunk del contenuto http
	 *
	 * @param string $str	: il contenuto del body della response http
	 * @return string/boolean	Ritorna il contenuto unchunked, false il caso di errore
	 */
	private function unchunkHttpResponse($str=null) {
		if(!is_string($str) or strlen($str) < 1) { return false; }
    	$eol = "\r\n";
    	$add = strlen($eol);
    	$tmp = $str;
    	$str = '';
    	
    	do {
        	$tmp = ltrim($tmp);
        	$pos = strpos($tmp, $eol);
        	if($pos === false) { return false; }
        	$len = hexdec(substr($tmp,0,$pos));
        	if(!is_numeric($len) or $len < 0) { return false; }
        	$str .= substr($tmp, ($pos + $add), $len);
        	$tmp  = substr($tmp, ($len + $pos + $add));
        	$check = trim($tmp);
        } while(!empty($check));
        
    	unset($tmp);
    	
    	return $str;
	}
	
	/**
	 * Parse dell'XML di ritorno
	 *
	 */
	public function parse_XML_res() {
		// Carico l'XML e comincio a parsarlo
		$dom = new DomDocument('1.0');

		// Gestione degli errori con chiamata alla funzione errorHandler()
		$error = "";
		set_error_handler(array($this, "errorHandler"));

		// Caricamento del corpo della response XML in un DOM 
		$dom->loadXML($this->XML_response);

		restore_error_handler();
		if($error!="") throw new SoapFault('wi400WsSiriAtg', "XML non valido:" . $error);

		// Estrazione dei dati di interesse dalla response XML
		$param = $this->parseXML($dom);
		
		if(!$param) throw new SoapFault('wi400WsSiriAtg', 'XML non contiene parametri validi oppure incompleto');

		// Aggiornamento del log del database con i dati ricavati da parseXML() 
		$this->aggiorna_db_log($param);

		if($param['GlobalCode']!='0' || $param['SetCode']!='0' || $param['PdfCode']!='0' ||
			$param['EnvelopeCode']!='0') {
			$this->agg_log_MPX($this->ID, '1', '030', "Response contenente codici di errore");
			return false;
		}
		else {
			$this->agg_log_MPX($this->ID, '1', '000', "Response di conferma del successo del post");
			return true;
		}
	}

	/**
	 * Parse dell'XML contenuto nella response
	 *
	 * @param unknown_type $dom	: il documento DOM contenente lo script XML della response
	 * @return array	Ritorna un array con i dati ricavati dal parse dello script XML della response
	 */
	private function parseXML($dom) {
		$array = array();
		
		// Cerco se c'è il tag resource	
		$params = $dom->getElementsByTagName('resource'); // Find Sections
		// se non c'è cerco il tag Header di 2° livello 
		if(!isset($params->item(0)->nodeValue) or ($params->item(0)->nodeValue)=="") 
			$params = $dom->getElementsByTagName('Header'); // Find Sections
		// Se non ho trovato nulla errore 
		if(!isset($params)) return;
		$k=0;
		foreach($params as $param) {	
			$array['GlobalCode'] = $params->item($k)->getAttribute('GlobalCode');
			$k++;
		}
		// Cerco se c'è il tag Set di 2° livello 
		$params = $dom->getElementsByTagName('Set');
		$k=0;
		foreach($params as $param) {
			$array['SetID']=$params->item($k)->getAttribute('ID');
			$array['SetCode']=$params->item($k)->getAttribute('SetCode');
			$array['CustomerSetID']=$params->item($k)->getAttribute('CustomerSetID');
			/* Cerco se c'è il tag Pdf di 3° livello */
			$params2 = $params->item($k)->getElementsByTagName('Pdf');
			$i=0;
			foreach($params2 as $p) {
				$array['PdfID']=$params2->item($i)->getAttribute('ID');
				$array['PdfCode']=$params2->item($i)->getAttribute('PdfCode');
				$array['EnvelopeCode']=$params2->item($i)->getAttribute('EnvelopeCode');
				$params3 = $params2->item($i)->getElementsByTagName('Envelope');
				$j=0;
				foreach($params3 as $p2) {
					$array['EnvelopeID']=$params3->item($j)->getAttribute('ID');
					$array['EnvelopeCode']=$params3->item($j)->getAttribute('EnvelopeCode');
					$j++;	
				}
				$i++;   
			}  
			$k++;
		}
		return $array;
	}

	private function parse_XML_file($dom) {
		$array = array();
		
		// Cerco se c'è il tag resource 
		$params = $dom->getElementsByTagName('resource'); // Find Sections
		// se non c'è cerco il tag Header di 2° livello 
		if(!isset($params->item(0)->nodeValue) or ($params->item(0)->nodeValue)=="") 
			$params = $dom->getElementsByTagName('Header'); // Find Sections
		// Se non ho trovato nulla errore 
		if(!isset($params)) return;
		$k=0;
		foreach($params as $param) {
			$array['ZCode'] = $params->item(0)->getAttribute('ZCode');
			$array['Username'] = $params->item(0)->getAttribute('Username');
			$array['Password'] = $params->item(0)->getAttribute('Password');
			$k++;
		}
		// Cerco se c'è il tag Set di 2° livello
		$params = $dom->getElementsByTagName('Set');
		$k=0;
		foreach($params as $param) {
			$array['Test']=$params->item($k)->getAttribute('Test');
			$array['CustomerSetID']=$params->item($k)->getAttribute('CustomerSetID');
			// Cerco se c'è il tag Pdf di 3° livello
			$params2 = $params->item($k)->getElementsByTagName('Pdf');
			$i=0;
			foreach($params2 as $p) {
				$array['NumPages']=$params2->item($i)->getAttribute('NumPages');
				$array['Type']=$params2->item($i)->getAttribute('Type');
				$params3 = $params2->item($i)->getElementsByTagName('Envelope');
				$j=0;
				foreach($params3 as $p2) {
					$array['WorkProcessID']=$params3->item($j)->getAttribute('WorkProcessID');
					$params4 = $params3->item($j)->getElementsByTagName('Data');
					$m=0;
					foreach($params4 as $p3) {
						$params5 = $params4->item($m)->getElementsByTagName('AddressLine1');
						$n=0;
						foreach($params5 as $p5) {
							$array['AddressLine1']=$params5->item($n)->nodeValue;
							$n++;						
						}
						$params5 = $params3->item($j)->getElementsByTagName('AddressLine2');
						$n=0;
						foreach($params5 as $p4) {
							$array['AddressLine2']=$params5->item($n)->nodeValue;
							$n++;						
						}
						$params5 = $params3->item($j)->getElementsByTagName('AddressLine3');
						$n=0;
						foreach($params5 as $p4) {
							$array['AddressLine3']=$params5->item($n)->nodeValue;
							$n++;						
						}
						$m++;
						$params5 = $params3->item($j)->getElementsByTagName('CAP');
						$n=0;
						foreach($params5 as $p4) {
							$array['CAP']=$params5->item($n)->nodeValue;
							$n++;						
						}
						$params5 = $params3->item($j)->getElementsByTagName('City');
						$n=0;
						foreach($params5 as $p4) {
							$array['City']=$params5->item($n)->nodeValue;
							$n++;						
						}
						$params5 = $params3->item($j)->getElementsByTagName('LocalCode');
						$n=0;
						foreach($params5 as $p4) {
							$array['LocalCode']=$params5->item($n)->nodeValue;
							$n++;						
						}
						$params5 = $params3->item($j)->getElementsByTagName('Country');
						$n=0;
						foreach($params5 as $p4) {
							$array['Country']=$params5->item($n)->nodeValue;
							$n++;						
						}
						$m++;					
					}
					$j++;	
				}
				$i++;   
			}  
			$k++;
		}
		return $array;
	}
	
	private function parse_PdfByteCode64($dom) {
		$array = array();
			
		// Cerco se c'è il tag resource	
		$params = $dom->getElementsByTagName('resource'); // Find Sections
		// se non c'è cerco il tag PdfByteCode64
		if(!isset($params->item(0)->nodeValue) or ($params->item(0)->nodeValue)=="") 
			$params = $dom->getElementsByTagName('PdfByteCode64'); // Find Sections
		// Se non ho trovato nulla errore
		if(!isset($params)) return;
		
		return $params->item(0)->nodeValue;
	}

	/**
	 * Aggiornamento del log del database
	 *
	 * @param array $param	: l'elenco dei dati ricavati dal parse dello script XML della response
	 */
	private function aggiorna_db_log($param) {
		$sql = "UPDATE FMPXPARM";
		$sql .= " SET GLOCOD='" . $param['GlobalCode'] . "', SETID='" . $param['SetID'] . "'";
		$sql .= ", SETCOD='" . $param['SetCode'] . "', PDFCOD='" . $param['PdfCode'] . "'";
		$sql .= ", ENVCOD='" . $param['EnvelopeCode'] . "'";
		$sql .= " WHERE ID = '$this->ID'";
		$result = $this->connDb->query($sql);
	}
	
	/**
	 * Gestione degli errori
	 *
	 * @param string $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param string $errline
	 */
	private function errorHandler($errno, $errstr, $errfile, $errline) {
		$pos = strpos($errstr,"]:") ;   
		if($pos) {   
			$errstr = substr($errstr, $pos + 2);   
		}   
		$error = $errstr;
	}
	
	/**
	 * Aggiornamento del log
	 *
	 * @param string $ID		: Codice identificativo dell'invio
	 * @param string $err		: Codice identificativo dell'errore
	 * @param string $log_des	: Messaggio di log
	 */
	private function agg_log_MPX($ID, $stato='1', $err="", $log_des="") {
		if($this->saveLOG == True || ($this->saveLOG == False && $err!='')) {
			// Aggiornamento del file di log
			$log_msg = date('D, d M Y H:i:s T') . " - MPX - ID: $ID";
			if($err!='' && $err!="000" && $err !="MPX")
				$log_msg .= " - Error $err";
			$log_msg .= " - $log_des \r\n";
			// fopen() deve essere impostato ad "a" per scrivere sul file senza però riscrivere la stessa riga
			$log_handle = fopen($this->log_file, "a");
			fwrite($log_handle, $log_msg);
			fclose($log_handle);
		}
		
		if($err!='') {
			$risp = $this->conv_rec['MAIRIS'] + 1;
			
			$keysName = array("ID"=>$ID);
			$fieldsValue = array(
				"MAISTA" => $stato,
				"MAIRIS" => $risp,
				"MAIERR" => $err,
				"MAIDER" => substr($log_des,0,40),
				"MAIELA" => date("Y-m-d-H.i.s.00000")
			);
		
			if(!isset($stmtupdate)) {
				$stmtupdate  = $db->prepare("UPDATE", "FPDFCONV", $keysName, array_keys($fieldsValue));
			}
			
			$result = $connDb->execute($stmtupdate, $fieldsValue);
		}
		
	}
	
}

?>