<?php

/**
 * 
 * @author Luca
 *
Formato XML - INPUT
<?xml version="1.0"?>
<OTM>
  <Header>
    <attribute id="user" value="<utente per cui si richiede l'OTM>" />
    <attribute id="timestamp" value="2011-01-04T14:40:41" />
  </Header>
</OTM>
Formato XML - OUTPUT
<?xml version="1.0"?>
<OTM>
  <Header>
    <attribute id="OTMID" value="<OTM ID>" />
  </Header>
</OTM>
 */

class wi400Ws_otm {

	private $mycon;
	private $domout;
	private $resource;
	private $libary;
	private $sysinf;
	private $param;
	private $error;
	private $wsparm;
	private $found;
	private $ID;
	private $makeDbConnection;	
	private $timeout = 180;
	private $xml_in;
	private $xml_out;
	
	public function __construct() {

    	  require_once "ws_otm_commons.php";
    	  
		  $this->wsparm = $wsparm;
		  $this->mycon = $mycon;
		  $this->makeDbConnection = False;
		  $this->ID="";
		  $this->xml_in="";
		  $this->xml_out="";
		  $file = $this->wsparm['save_path']."X_ORI".date("YmdHis").".txt";
		  $this->xml_in = $file;
		  $file = $this->wsparm['save_path']."XORO".date("YmdHis").".txt";
		  $this->xml_out = $file;		  
	
	}
	
	public function getOTM($utente, $password, $xml) {
		echo "getOTM<br>";
		return $this->OTM($utente, $password, $xml);
	}
	
	/**
	* get: recupero generale informazioni anagrafiche
	* @param string $consumer   chiamante del servizio
	* @param string $contest    contesto di chiamata del servizio
	* @param string $xml        XML con parametri di ingresso
	* @param string $regole     regole di esecuzione WEB services ** AL MOMENTO NON GESTITO
	* 
	* @return string xml
	**/
	private function OTM($utente, $password, $xml) {
		global $db;
		
	      // Verifico se il server è stato disabilitato logicamente
	      if ( $this->wsparm['server_down']) {
		      $this->createDocument();
		      $this->stateMessage('12');
		      $this->domout->formatOutput = true;
			  $returnValue = $this->domout->saveXML();
		      return $returnValue;	      	 	
	      }
	      // Verifico se il server è stato disabilitato logicamente
	      if ($this->wsparm['check_user']) {
		      if ($utente=='' && $password=='') {
			      $this->createDocument();
			      $this->stateMessage('18');
			      $this->domout->formatOutput = true;
				  $returnValue = $this->domout->saveXML();
			      return $returnValue;	      	 	
		      } else {
		          $check = $this->checkUserPwd($utente, $password);
		          if (!$check) {
				      $this->createDocument();
				      $this->stateMessage('17');
				      $this->domout->formatOutput = true;
					  $returnValue = $this->domout->saveXML();
				      return $returnValue;	      	 	
		          }
		      }
	      }
		  // Salvataggio file XML per future consultazioni
		  if ($this->wsparm['save_log']) {
		  	  $time_start = microtime(true);
		  }			  
		  if ($this->wsparm['save_xml_in']) {
			  $handle = fopen($this->xml_in, "w+");
			  fwrite($handle, $xml);
			  fclose($handle);
		  }	
//		  throw new SoapFault('wi400WsSiriAtg',"SAVE_XML_IN: {$this->wsparm['save_xml_in']} <br> XML IN: {$this->xml_in} <br> XML: $xml");

		  // Caricamento e pulizia xml
		  $domLoader = new DomDocument('1.0');
		  $domLoader->loadXML($xml);
		  $domLoader->formatOutput = true;
		  $xml = $domLoader->saveXML();
		  
	      // Carico l'XML e comincio a parsarlo
	      $dom = new DomDocument('1.0');
	      $this->error="";
	      set_error_handler(array($this,"errorHandler"));   
	      $dom->loadXML($xml);
	      restore_error_handler();
	      if ($this->error!="") {
		      $this->createDocument();
	      	  $this->stateMessage('8');
		      $this->domout->formatOutput = true;
			  $returnValue = $this->domout->saveXML();
		      return $returnValue;
	      }	
	      
	      // @todo Check ip address Da abilitare per controllare l'IP del chiamante
	      // $_SERVER['REMOTE_ADDR']==$this->wsparm['client_ip']
	      
	      // Effettuo il parsing del file per recuperare i parametri
		  $param = $this->parseXML($dom);
	      if (!$param) {
		  	   $this->createDocument();
	      	   $this->stateMessage('10');
	      	   $this->domout->formatOutput = true;
		   	   $returnValue = $this->domout->saveXML();
	           return $returnValue;
	      }
	      
	      // Creazione intestazione documento XML
	      $this->createDocument();
	      if (!$this->parmOk) {
	      	$this->stateMessage('14');
	      	$this->domout->formatOutput = true;
	      	$returnValue = $this->domout->saveXML();
	      	return $returnValue;
	      }
           
	      // Ora che ho i parametri cerco il sistema informativo
          $flag = $this->sistemaInformativo($param);
		  if ($flag!='0') {
		      $this->stateMessage($flag);
		      $this->domout->formatOutput = true;
			  $returnValue = $this->domout->saveXML();
		      return $returnValue;		  	
		  }
		  
		  // Se arrivo qui vuol dire che il documento XML è formalmente valido
		  
		  $otm_id = uniqid();
		  $otm_user = $param['user'];
//		  throw new SoapFault('wi400WsSiriAtg',"OTM USER: $otm_user - OTM_ID: $otm_id");
		  
		  $timeStamp = getDb2Timestamp();
		  
		  // Scrivo l'OTM ID in SIR_OTM
		  $sql_otm = "select * from SIR_OTM where OTMUSR='$otm_user'";
		  $res_otm = $db->singleQuery($sql_otm);
		  if($row_otm = $db->fetch_array($res_otm)) {
		  	// UPDATE
		  	$keyUpdt = array("OTMUSR" => $otm_user);
		  	$fieldsValue = array("OTMID" => $otm_id, "OTMTIM" => $timeStamp);		  	
		  	$stmt_updt = $db->prepare("UPDATE", "SIR_OTM", $keyUpdt, array_keys($fieldsValue));
		  	$result = $db->execute($stmt_updt, $fieldsValue);
		  }
		  else {
		  	// INSERT
		  	$fieldsValue = array("OTMID" => $otm_id, "OTMUSR" => $otm_user,"OTMTIM" => $timeStamp);
		  	$stmt_ins = $db->prepare("INSERT", "SIR_OTM", null, array_keys($fieldsValue));
		  	$result = $db->execute($stmt_ins, $fieldsValue);
		  }
		  
		  // Personalizzare per il formato di uscita
		  $returnValue = $this->createXMLreply($otm_id);
//		  throw new SoapFault('wi400WsSiriAtg',"XML REPLY: $returnValue");
		  
		  $do = $this->elaboraXml($xml, $param);
/*		  
		  // Finalizzazione del documento XML
	      $this->stateMessage($do);
	      $this->domout->formatOutput = true;
		  $returnValue = $this->domout->saveXML();
*/		  
		  // Salvataggio LOG
	      if ($this->wsparm['save_log']) {
			  $file = $this->wsparm['save_path']."log.txt";
			  $handle = fopen($file, "a");
	          $time_end = microtime(true);
			  $time = $time_end - $time_start;
	 		  $dati = date("d:m:Y-H:i:s")." Tempo esecuzione set in ".substr($time,0, 12)." seconds\r\n";
			  fwrite($handle, $dati);
			  fclose($handle);
		  }	
		  		  
		  if ($this->wsparm['save_xml_out']) {
			  $handle = fopen($this->xml_out , "w+");
			  fwrite($handle, $returnValue);
			  fclose($handle);
		  }
		  
	      return $returnValue;
	}
	
	/** parseXML: Parsa l'XML e recupera i suoi parametri in un array
	 * @param dom       object documento XML 
	 * 
	 * @return parm array  array con i parametri
	 */
	private function elaboraXML($xml, $param){
		global $db;
		
		$execute = true;
		$controlli = true;
		
		$err = "";
		$stato = "*";
		 
		$seriale = "";
		$ip = "";
		 
		$this->found = False;
		$numEla = 1;
		
		// Ritorno flag di elaborazione terminata senza errori
		return '0';
	}
	
	/** parseXML: Parsa l'XML e recupera i suoi parametri in un array
	 * @param dom       object documento XML 
	 * 
	 * @return parm array  array con i parametri
	 */
	// @TODO:path di salvataggio da file di configurazione
	private function parseXML($dom){
		$array = array();	
		$foundDati=False;
	    $this->parmOk=False;		
		// Cerco se c'è resource	
		$params = $dom->getElementsByTagName('OTM'); // Find Sections
		// .. se non c'è
		if (!isset($params->item(0)->nodeValue) or ($params->item(0)->nodeValue)=="");
		if (!isset($params)) return;
		$k=0;
		foreach ($params as $param){
			$array['id']=$params->item($k)->getAttribute('id');
			$params2 = $params->item($k)->getElementsByTagName('Header'); //Vado in profondità sugli attributi
			$i=0;
			foreach ($params2 as $p) {
				$params3 = $params2->item($i)->getElementsByTagName('attribute'); //dig Arti into Categories
				$j=0;
				foreach ($params3 as $p2) {
					$array[$params3->item($j)->getAttribute('id')]= $params3->item($j)->getAttribute('value');
					$j++;   
				}              
				$i++;
			}
		}
		
		if(!empty($array) && array_key_exists("user", $array) && array_key_exists("timestamp", $array)) {
			$this->parmOk=True;
		}
		
		return($array);
	}
	
	private function createXMLreply($otm_id) {
		// Generazione dell'XML
		$dom = new DomDocument('1.0', 'UTF-8');
		
		$reply = $dom->appendChild($dom->createElement('OTMReply'));
		
		$header = $reply->appendChild($dom->createElement('Header'));
		
		$resource = $header->appendChild($dom->createElement('attribute'));
		
		$field_name = $dom->createAttribute('id');
		$resource->appendChild($field_name);
		$name = $dom->createTextNode("OTMID");
		$field_name->appendChild($name);
	
		$field_name = $dom->createAttribute('value');
		$resource->appendChild($field_name);
		$name = $dom->createTextNode($otm_id);
		$field_name->appendChild($name);
		
		$dom->formatOutput = true;
		$returnValue = $dom->saveXML();
	
		return $returnValue;
	}
	
	private function connectDB() {
		global $db;
		
		if ($this->makeDbConnection==False) {
		    // @TODO controllare se è settato $this->library
		    $library = array();
		    if (isset($this->library)) {
			    $library = $this->library;
		    }
			$db->add_to_librarylist($library);
			$db->connect();
			$this->db = $db;
			$this->makeDbConnection = True;
		}		
	}
	
	private function checkUserPwd($utente, $password) {
	    $check = False;
	    if ($this->wsparm['check_user']==True) {
			if ($utente == $this->wsparm['user'] && $password == $this->wsparm['password']) {
				$check = True;
			}     
	    } else {
	        $check = True;
	    }
	    return $check;
	}

	private function createDocument(){		  
		$dom = new DomDocument('1.0', 'UTF-8');
		$resource = $dom->appendChild($dom->createElement('ordine'));
		if (isset($this->ID) && $this->ID!='') {
			$field_name = $dom->createAttribute('id'); 
			$resource->appendChild($field_name); 
			$name = $dom->createTextNode($this->ID);
			$field_name->appendChild($name);		 
		}
		$this->resource = $resource;
		$this->domout = $dom;
	}
	
	public function __destruct() {
	    global $settings, $db, $INTERNALKEY;
	    
	    if (isset($settings['xmlservice'])) {
			$InputXML = '<?xml version="1.0"?>';
			$InternalKey = $INTERNALKEY;    
		  	$ControlKey="*immed";
		  	$OutputXML = '';
		  	$callPGM = $db->getCallPGM();
			$db->bind_param ($callPGM, 1, "InternalKey", DB2_PARAM_IN );					
			$db->bind_param ($callPGM, 2, "ControlKey", DB2_PARAM_IN );
			$db->bind_param ($callPGM, 3, "InputXML", DB2_PARAM_IN );		
			$db->bind_param ($callPGM, 4, "OutputXML", DB2_PARAM_OUT );	  	
			$ret = db2_execute($callPGM);
		}
		
	}
	
	private function sistemaInformativo($param){
		$this->connectDB();
		// Ora che ho i parametri cerco il sistema informativo
		if (isset($param['environment']) && strtolower($param['environment'])=='test') {
			$sysinf = $this->wsparm['sysinf_test'];
		} else {
			$sysinf = $this->wsparm['sysinf_prod'];
		}
		$filename = wi400File::getCommonFile("serialize", "SYSINF_NAME_".$sysinf.".dat");
		$library=fileSerialized($filename);
		if ($library == Null) {
			$this->connectDB();	
			$library = retrive_sysinf_by_name($sysinf);
		}
		if (!empty($library)) {    
			$this->db->add_to_librarylist($library, True);
			$this->library = $library;
			$this->sysinf = $sysinf;
			return '0';
		}	else {
			return '13';		
		}
	}
	
	function errorHandler($errno, $errstr, $errfile, $errline) {   
		$pos = strpos($errstr,"]:") ;   
		if ($pos) {   
			$errstr = substr($errstr,$pos+ 2);   
		}   
		$this->error = $errstr;
	}
	/** stateMessage: Scrive il codice di ritorno del web services
	 * @param dom       object documento XML
	 * @param resource  child dove mettere il messaggio
	 * @param state     codice da ritornare
	 */
	private function stateMessage($state){	
		$stato = $this->resource->appendChild($this->domout->createElement('Reply'));
		$field_name = $this->domout->createAttribute('code'); 
		$stato->appendChild($field_name); 
		$name = $this->domout->createTextNode($state);
		$field_name->appendChild($name);		 
	    	
		$message = retriveMessage($state);	   				   				   				   				   
		$field_name->appendChild($name);		 
		$field_name = $this->domout->createAttribute('message'); 
		$stato->appendChild($field_name); 
		$name = $this->domout->createTextNode($message);
		$field_name->appendChild($name);	    		
	}
	
}		  

// Start WEB service
$server = new SoapServer("wi400Ws_otm.wsdl", array('soap_version' => SOAP_1_2));
$server->setClass("wi400Ws_otm");
//$server->setPersistence(SOAP_PERSISTENCE_REQUEST);
$server->handle();
?>