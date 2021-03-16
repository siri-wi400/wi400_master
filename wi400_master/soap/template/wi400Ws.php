<?php
class wi400Ws {
	
	public $conn = false;
		
	public $db;
	public $mycon;
	public $domout;
	public $resource;
	
	public $param;
	public $error;
	public $key;
	
	public $multiDataSet;
	public $dataSetCount;
	
	public $save_XML;
	public $save_LOG;
	public $save_path;
	public $log_file;
	 
	public $server_down;
	public $library;
	public $sysinf;
	
	public $makeDbConnection;
	public $DTAQKey;
	public $privateId;
	public $authSysInf;	
	public $timeout;
	
	public $rpgXML;
	public $lastMessage="";
	public $lastState="";
	public $plainText = array();	
	
	public $full_user_space;
	public $user_space_libl;
	public $user_space_name;
	public $appBase;
	public $defaultSysInf = "LGM";
	public $defaultRoutine = "webservices";
	public $requireLogin = False;
	// Per log
	public $init_timestamp;
	public $consumer="";
	public $context="";
	public $entita="";
	public $current_segmento="";
	public $segmenti = array();
	public $id="";
	public $file_XML_IN="";
	public $file_XML_OUT="";
	public $xml="";
	public $returnxml="";
	public $typeParameter;
	public $DS_INPUT;
	public $auth ="N";
	public $extra = ""; // EXTRA Data for logging
	public $keyIdEnable = True;
	public $recCount = 0;
	public $file_AGGIUNTIVO="";
	
	public function init() {
	      
		  // Apro i collegamenti con l'AS
		  require_once "ws_commons.php";
		  //$this->conn = $connzend;
		  $this->mycon = $mycon;
		  $this->privateId=0;
		  $this->authSysInf = $authSysInf;
		  $this->save_LOG = $save_LOG;
		  $this->save_XML = $save_XML;
		  $this->save_path = $save_path;
		  $this->server_down = $server_down; 
		  $this->makeDbConnection = False;
		  $this->rpgXML = False;
		  if (!isset($ws_timeout)) $ws_timeout=300;
		  $this->timeout = $ws_timeout;
		  $this->dataSetCount = 0;
		  $this->multiDataSet = false;
		  $this->appBase = $appBase;
		  $this->log_file = $log_file;
		  $this->init_timestamp = date("Y-m-d-H.i.s.").substr((string)microtime(), 2, 6);
		
	}
	
	/** stateMessage: Scrive il codice di ritorno del web services
	 * @param dom       object documento XML
	 * @param resource  child dove mettere il messaggio
	 * @param state     codice da ritornare
	 */
	public function stateMessage($state, $messaggio="", $id=""){
		
		$stato = $this->resource->appendChild($this->domout->createElement('state'));
		$field_name = $this->domout->createAttribute('code'); 
		$stato->appendChild($field_name); 
		$name = $this->domout->createTextNode($state);
		$field_name->appendChild($name);		 
    	
		if ($messaggio=="") {
			$message = retriveHeaderMessage($state);
		} else {
			$message = $messaggio;
		}	   				   				   				   				   
		$field_name->appendChild($name);		 
		$field_name = $this->domout->createAttribute('message'); 
    	$stato->appendChild($field_name); 
    	$name = $this->domout->createTextNode($message);
		$field_name->appendChild($name);
		// Verifico se devo attaccare l'ID
		if ($id!="" && $this->keyIdEnable==True) {
			$field_name = $this->domout->createAttribute('keyid');
			$stato->appendChild($field_name);
			$name = $this->domout->createTextNode($id);
			$field_name->appendChild($name);
		}
		$this->lastMessage = $message;
		$this->lastState = $state;	    		
	}
	/**
	 * log_file: Scrivo su di un file di log le informazioni generali sulla chiamata
	 */
	public function log_file() {
		global $db;
		// Salvataggio file
		$micro = substr(microtime(), 2, 8);
		$myid=$this->id;
		$file_XML_IN = $this->save_path."XMLI".$myid."_".$micro.".txt";
		$file_XML_OUT = $this->save_path."XMLO".$myid."_".$micro.".txt";
		if ($this->save_XML==True) {
			$handle = fopen($file_XML_IN, "w+");
			fwrite($handle, $this->xml);
			fclose($handle);
			$handle = fopen($file_XML_OUT, "w+");
			fwrite($handle, $this->returnxml);
			fclose($handle);
		}
		// Normalizzazione dati
		$user = $this->consumer;
		if (!isset($user) || $user=="") {
			$user = "WEB";
		}
		$segmenti = implode(";", $this->segmenti);
		if ($segmenti =="") $segmenti = "*NONE";
		if ($this->sysinf=="") $this->sysinf="*NONE";
		
		$tracciato = getDs("ZWEBSLOG");
		$tracciato['LOGTYP']="WEBS";
		$tracciato['LOGID']=$this->id;
		$tracciato['LOGUSR']=$user;
		$tracciato['LOGENT']=$this->entita;
		$tracciato['LOGSEG']=$segmenti;
		$tracciato['LOGRCX']=$this->init_timestamp;
		$tracciato['LOGTRX']=date("Y-m-d-H.i.s.").substr((string)microtime(), 2, 6);
		$tracciato['LOGSTA']='1';
		$tracciato['LOGERR']=$this->lastState;
		$tracciato['LOGDER']=substr($this->lastMessage,0,50);
		$tracciato['LOGIP']=$_SERVER['REMOTE_ADDR'];
		$tracciato['LOGSYS']=$this->sysinf;
		$tracciato['LOGXIN']=$file_XML_IN;
		$tracciato['LOGXOU']=$file_XML_OUT;
		$tracciato['LOGADL']=$this->file_AGGIUNTIVO;
		$tracciato['LOGAGE']=$_SERVER['HTTP_USER_AGENT'];
		$tracciato['LOGOPE']="";
		$tracciato['LOGBRW']="";
		$tracciato['LOGEXT']=$this->extra;
		$tracciato['LOGEX2']="";
		
		$stmtinsert = $db->prepare("INSERT", "ZWEBSLOG", null, array_keys($tracciato));
		$result = $db->execute($stmtinsert, $tracciato);
		
		
	}
	public function errorHandler($errno, $errstr, $errfile, $errline) {   
        $pos = strpos($errstr,"]:") ;   
        if ($pos) {   
            $errstr = substr($errstr,$pos+ 2);   
        }   
        $this->error = $errstr;
	}
	
	public function __destruct() {
		global $db, $settings, $CONTROLKEY, $INTERNALKEY;
     	//$ret = executeCommand('DLTUSRSPC USRSPC('.$this->user_space_libl.'/'.$this->user_space_name.')');
     	if ($this->privateId !='' && $this->privateId !==0) {
			$seg = msg_get_queue($this->DTAQKey) ;
			$daten=$this->privateId."/".time();
			msg_send ($seg, 1, $daten, true, true, $msg_err);				
     	}
     	// Scrittura log
     	if ($this->log_file == True) {
     		if ($this->makeDbConnection==False) {
     			$db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name']);
     			$this->connectDB();
     			$this->log_file();
     			//In questo caso cosa faccio .. mi connetto al DB e scrivo o lascio stare ...
     		} else {
     			$this->log_file();
     		}
     	}
	}
	
	
	public function sistemaInformativo($param){

	      global $db, $settings, $INTERNALKEY, $CONTROLKEY, $base_path;
     
	      // Se non viene passato il sistema informativo metto quello di default o in alternativa
	      $prefix = "";
          if (isset($param['sistemaLogico']) AND $param['sistemaLogico']!="") {
          	$sysinf = $param['sistemaLogico'];
          	$keyMsg = $sysinf;
          	$prefix ="I";
          } else {
          	$sysinf = $this->defaultSysInf;
          	$keyMsg = $this->defaultSysInf;
          	$prefix ="I";
          	if ($this->consumer!="") {
          		$userDati = explode("@", $this->consumer);
          		$keyMsg = $userDati[0];
          		$prefix ="U";
          	}
          }
          // Cerco di recuperare una connessione già esistente in coda per il sistema informativo
          //$this->privateId=0;
          /*if (substr($sysinf,0 ,1)=='Y') {
          	$settings['db_host']='QUALITY';
          }*/
		  $db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name']);
          // Inizializzo il Private ID
       	  if (isset($settings['i5_toolkit'])) { 
              $this->privateId=0;
       	  } else {
              $this->privateId=uniqid("WS_");
       	  }
          if (!isset($this->authSysInf["$keyMsg"])) {
              return '16';
          }
          // Verifico se mi è stato passato un ID privato di connessione
          if (isset($param['privateID']) AND $param['privateID']!="") { 
          		$this->privateId=$param['privateID'];
          }	else {
	          $Key=$this->authSysInf["$keyMsg"];
	          //$Key = 1;
	          $this->DTAQKey=$Key;
	          $msgtype_receive=1;
	          $maxsize=1000;
		      $message='';   
			  $serialize_needed=True;
			  $block_send=false;      
			  $msgtype_send=1;  
			  $option_receive=MSG_IPC_NOWAIT;
			  $seg = msg_get_queue($Key);
			  $queue_status=msg_stat_queue($seg);
			  $time_start = microtime(true);		  
			  // Controllo se in coda c'è già qualcosa
			  if ($queue_status['msg_qnum']>0) {
			  // Cerco una connessione non scaduta
			  $i=0;
				  for ($i; $i<=$queue_status['msg_qnum']; $i++){
				      if (msg_receive($seg,$msgtype_receive ,$msgtype_erhalten,$maxsize,$daten,$serialize_needed, $option_receive, $err)===true) {
				              $valori = explode("/", $daten);
				              $diff = time() - $valori[1];
				              if ($diff < $this->timeout) {
				                 //$this->privateId=intval($valori[0]);
				                 if (isset($settings['i5_toolkit'])) { 
					                 $this->privateId=intval($valori[0]);
				                 } else {
				                     $this->privateId = $valori[0];
				                 }    
				                 break;
				              }
				      } else {
				      	if (isset($settings['i5_toolkit'])) { 
				      		$this->privateId=0;
				      	} else {
				      		$this->privateId=uniqid("WS_");
				      	}
				      	break;
				      }
				  }
			  }
			  // Scrittura di eventuali LOG
			  if ($this->save_LOG) {
			  	$file = $this->save_path."log2.txt";
			  	$handle = fopen($file, "a");
			  	$time_end = microtime(true);
			  	$time = $time_end - $time_start;
			  	$dati = date("d:m:Y-H:i:s")." Tempo reperimento messaggio ".substr($time,0, 12)." seconds\r\n";
			  	fwrite($handle, $dati);
			  	fclose($handle);
			  }
          } 
	  
		  
          if (isset($settings['i5_toolkit'])) {
	          $this->mycon->set_options("I".substr($sysinf,0, 9),null ,null ,null , null , null, $this->timeout , True, $this->privateId);
		      $this->conn = $this->mycon->connect();
			  $this->privateId=$this->mycon->getPrivateId();	      
		      if (!is_resource($this->conn)){
	 	           return '3';
			  }
          } else {
			  $CONTROLKEY =  '*idle('.$this->timeout.') *sbmjob('.$settings['xmlservice_jobd_lib_ws'].'/'.$settings['xmlservice_jobd_ws'].'/'.$prefix.substr($sysinf,0, 9).')';
			  if (isset($settings['xmlservice_cdata']) && $settings['xmlservice_cdata']==True) {
			  	$CONTROLKEY .= " *cdata ".$CONTROLKEY;
			  }
			  $INTERNALKEY = '/tmp/'.$this->privateId;
              $this->connectDB();	
          }
          
		  // Ora che ho i parametri cerco il sistema informativo
          if ($this->auth!="S") {
			  $filename = wi400File::getCommonFile("serialize", "SYSINF_NAME_".$sysinf.".dat");
			  $library=fileSerialized($filename);
			  /*if (substr($sysinf,0 ,1)=='Y') {
	          		$do = executeCommand("CALL QGPL/ZDT_ASPQA2");
	          }*/
	          //throw new SoapFault('wi400WsSiriAtg',$sysinf);
	          // @todo la lista libreria se $library null viene già caricate ...
			  if ($library == Null) {
					$this->connectDB();	
				    $library = retrive_sysinf_by_name($sysinf);				
				    $this->db->add_to_librarylist($library, True);
	    	  }
	    	  if (!empty($library)) {
	    	  	if (isset($settings['i5_toolkit'])) {  
	    	  		$this->mycon->add_to_librarylist($library);
	    	  	} else {
	    	  		$this->db->add_to_librarylist($library, True);
	    	  	}
	    	  	$this->library = $library;
	    	  	$this->sysinf = $sysinf;
	    	  }	else{
	    	  	$this->sysinf = $sysinf;
	    	  	return '13';		
	    	  }
          }
    	  // A questo punto devo controllare se esiste l'utente e caricare il suo sistema informativo
    	  if ($this->consumer!="" && $this->auth=="S") {
    	  	  $this->connectDB();
    	  	  $userDati = explode("@", $this->consumer);	
	    	  require_once $base_path."/checkuser/checkUserAs.php";
	    	  $check = new checkUserAS();
	    	  $check->isBatch(True);
	    	  if (!$check->checkUser($userDati[0], $userDati[1])) return "B";
	    	  $library = retrive_sysinf(strtoupper($userDati[0]));
	    	  $this->sysinf = $userDati[0];
	    	  $this->db->add_to_librarylist($library, True);
    	  }
    	  // Tutto OK
    	  return '0';
	}
	
	public function createDocument($param){
			  
			  $dom = new DomDocument('1.0', 'UTF-8');
			  $resource = $dom->appendChild($dom->createElement('resource'));
			  $field_name = $dom->createAttribute('entityId'); 
		      $resource->appendChild($field_name); 
		      $name = $dom->createTextNode($param['entityId']);
			  $field_name->appendChild($name);		 
			  $field_name = $dom->createAttribute('entityDesc'); 
		      $resource->appendChild($field_name); 
		      $name = $dom->createTextNode($param['entityDesc']);
			  $field_name->appendChild($name);
			  $field_name = $dom->createAttribute('id'); 
		      $resource->appendChild($field_name); 
		      $name = $dom->createTextNode($param['id']);
			  $field_name->appendChild($name);
			  $this->domout = $dom;
			  $this->resource = $resource;
	}
	
	public function connectDB() {
		global $db, $settings;
		
		if ($this->makeDbConnection==False)
		{
		if ($settings['database']=='DB2I5')  {
			$db->setLink($this->conn);
		} else {
			$db->connect(False);
			$db->add_to_librarylist($this->library);
	
			$this->db = $db;
		}
		$this->makeDbConnection = True;
	
		}
	
	}
	public function getDescriptor($file, $desc=False) {
			
		$filename = wi400File::getCommonFile("serialize", rtvLibre($file, $this->conn)."_".strtoupper($file).".dat");
		$desc1=fileSerialized($filename);
		if ($desc1 == Null) {
			$this->connectDB();
			$desc1 = create_descriptor($file, $this->conn, null, $desc);
		}
		return $desc1;
	}
	/**
	 * @desc Recupero chiave in formato stringa
	 * @param unknown $param
	 * @return string
	 */
	protected function getKeyString($param, $j) {
		$key = "";
		$sepa = ";";
		if (isset($param['id'])) {
			$key = trim($param['id']);
		}
		// Compongo la chiave per la routine
		for ($i=0;$i<$param[$j]['keyCount'];$i++) {
			$key .= $sepa.trim($param[$j]['id'.$i]);
			if (str_pad($param[$j]['key'.$i], 10)=='*INTNETADR') {
				$key .= "-".$_SERVER['REMOTE_ADDR'];
			} else {
				$key .= "-".trim($param[$j]['key'.$i]);
			}
			$sepa = ";";
		}
		return $key;
	}
	/**
	 * @desc Recupero chiave in formato Standard con ds posizinale IANGENSTD
	 * @param unknown $param
	 * @return string
	 */
	protected function getKeyStandard($param, $j) {
		$key = "";
		if (isset($param['id'])) {
			//$key = trim($param['id']);
			$key = str_pad($param['id'], 10);		
		}
		// Compongo la chiave per la routine
		for ($i=0;$i<$param[$j]['keyCount'];$i++) {
			$key .= str_pad(trim(substr($param[$j]['id'.$i],0 , 4)), 4);
			if (str_pad($param[$j]['key'.$i], 10)=='*INTNETADR') {
				$key .= $_SERVER['REMOTE_ADDR'];
			} else {
				$key .= str_pad(trim(substr($param[$j]['key'.$i],0, 10)), 10);
			}
		}
		return $key;
	}
	/**
	 * @ Desc recupero chiave in formato DS, potrà essere utilizzata per USER_SPACE o per PARM_INPUT
	 * @param unknown $param
	 * @param unknown $routine
	 * @return boolean
	 */
	protected function getKeyDs($param, $routine) {
		// Devo scrivere la user space
	
		$ds_input = "I".substr($routine, 1);
		$desc = $this->getDescriptor($ds_input, True);
		$this->DS_INPUT=$desc;
		$parm_out = array();
		$key = "";
		// Compongo la chiave per la routine
		$parametri = array();
		for ($i=0;$i<$param['keyCount'];$i++) {
			$parametri[trim($param['id'.$i])]= trim($param['key'.$i]);
		}
		//foreach ($parametri as $key=>$value) {
		$skip = False;
		foreach($desc as $key2) {
			if ($skip) break;
			if($key2['Name']!='Id record input') {
				if (isset($parametri[$key2['Name']])) {
					$type = $key2['Type'];
					$parm_out[$key2['Name']] = $parametri[$key2['Name']];
					$myValore = $parametri[$key2['Name']];
					if ($type ==I5_TYPE_PACKED || $type ==I5_TYPE_ZONED) {
						$myValore= str_replace(array(".","-","+"), '',$myValore);
						if (strlen(trim($myValore))>$key2['Length']) {
							$this->lastState='5';
							$this->lastMessage = retriveSetMessage('5');
							$skip = True;
							break;
						}
					} else {
						if (strlen($myValore)>$key2['Length']) {
							$this->lastState='9';
							$this->lastMessage = $key2['Name']. " lunghezza parametro superiore a quella prevista";
							$skip = True;
							break;
						}
					}
					if ($type ==I5_TYPE_PACKED || $type ==I5_TYPE_ZONED){
						$myValore= str_replace(array("-","+"), '',$parametri[$key2['Name']]);
						$array = explode('.',$myValore);
						$format= explode('.',$key2['Length']);
						$elementi = count($array);
						if ($elementi > 2) {
							$this->lastState='6';
							$this->lastMessage = retriveSetMessage('6');
							$skip = True;
							break;
						} else {
							if ($elementi == 2) {
								$format[0]=$format[0]-$format[1];
							}
							for ($i=0; $i<$elementi; $i++) {
								if (!is_numeric($array[$i])) {
									$this->lastState='6';
									$this->lastMessage = retriveSetMessage('6');
									$skip = True;
									break;
								}
								if (strlen($array[$i])>$format[$i]) {
									$this->lastState='7';
									$this->lastMessage = retriveSetMessage('7');
									$skip = True;
									break;
								}
							}
						}
					}
				} else {
					// Errore Bloccante. Dataset incompleto
					$this->lastState='8';
					$this->lastMessage = retriveSetMessage('8');
					$skip = True;
					break;
					//$parm_out[$key2['Name']] = "";
				}
			}
	
		}
	
		//}
		if (!$skip) {
			// @todo Soprire a cosa serviva questo codice ...
			//$this->key=$parm_out;
		} else {
			return false;
		}
		//
		return $parm_out;
	}
	
	
	
}
