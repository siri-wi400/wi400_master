<?php
//require_once 'wi400Ws.php';
class wi400WsSiriAtg extends wi400Ws{
	
	private $stk;
	private $numkey;
	private $currentID;

	private $dataList;
	private $firstDataSetList;

	public function __construct() {
		global $settings;
		
      	$this->full_user_space = 'SIRITEST  QTEMP    ';
		$this->user_space_libl = "QTEMP";
		$this->user_space_name = "SIRITEST";
		$this->user_space_all = "QTEMP/SIRITEST";
		// SE utilizzo le funioni per il reperimento USER-SPACE con store procedure verrà creata dinamicamente

		$this->init();
	}
	
	/*public function getArticolo($consumer, $contest, $xml, $regole) {
		return $this->get($consumer, $contest, $xml, $regole);
	}
	public function getFornitore($consumer, $contest, $xml, $regole) {
		return $this->get($consumer, $contest, $xml, $regole);
	}
	public function getBolla($consumer, $contest, $xml, $regole) {
		return $this->get($consumer, $contest, $xml, $regole);
	}
	public function getDestinatari($consumer, $contest, $xml, $regole) {
		return $this->get($consumer, $contest, $xml, $regole, True);
	}
	public function getListino($consumer, $contest, $xml, $regole) {
		return $this->get($consumer, $contest, $xml, $regole, True);
	}*/
	protected function getP($consumer, $contest, $xml, $regole, $dest=False) {
		$this->returnxml = $this->get($consumer, $contest, $xml, $regole, $dest);
		return $this->returnxml;
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
	protected function get($consumer, $contest, $xml, $regole, $dest=False) {
	      global $db, $settings, $routine_path,$base_path, $moduli_path;
	      
	      $this->consumer = $consumer;
	      $this->contest = $contest;
	      $myid = uniqid().date("YmdHis");
	      $this->id = $myid;
	      $this->xml = $xml;
	      // Da verificare in produzione con un test di TRYA
	      /*if (isset($_SERVER['HTTP_CLIENT_IP'])
	      		|| isset($_SERVER['HTTP_X_FORWARDED_FOR'])
	      		|| !in_array(@$_SERVER['REMOTE_ADDR'], array(
	      				'127.0.0.1',
	      				'::1', '10.10.1.60','10.0.40.1'
	      		))
	      ) {
	      	$string = "<br>CLIENT IP:".$_SERVER['HTTP_CLIENT_IP'];
	      	$string .= "<br>FORWARDED:".$_SERVER['HTTP_X_FORWARDED_FOR'];
	      	$string .= "<br>REMOTE ADDR:".$_SERVER['REMOTE_ADDR'];
		      	throw new SoapFault('wi400WsSiriAtg','You are not allowed to access this file.');
	      }*/
	       
	      // Verifico se il server è stato disabilitato logicamente
	      if ($this->server_down) {
		      $this->createDocument($param);
		      $this->stateMessage('12');
		      $this->domout->formatOutput = true;
			  //$returnValue = $this->domout->saveXML();
			  $returnValue = $this->getReturnValue($param);
		      return $returnValue;	      	 	
	      }
	      /**
	       * Attempt a quickie detection
	       */
	      /*$collapsedXML = preg_replace("/[:space:]/", '', $xml);
	      if (str_pos("/<!DOCTYPE/i", $collapsedXml)) {
	      	throw new SoapFault('wi400WsSiriAtg','Invalid XML: Detected use of illegal DOCTYPE');
	      }*/
	      if (strpos($xml, "!DOCTYPE")!==False) {
	      	throw new SoapFault('wi400WsSiriAtg','Invalid XML: Detected use of illegal DOCTYPE');
	      }
	      if ($xml=="") {
	      	$this->createDocument($param);
	      	$this->stateMessage('8');
	      	$this->domout->formatOutput = true;
	      	//$returnValue = $this->domout->saveXML();
	      	$returnValue = $this->getReturnValue();
	      	return $returnValue;
	      }
    
		  // Caricamento e pulizia xml
	      //$previous = libxml_disable_entity_loader(True);
		  $domLoader = new DomDocument('1.0');
		  $domLoader->loadXML($xml, LIBXML_NOENT);
		  $domLoader->formatOutput = true;
		  $xml = $domLoader->saveXML();
	      // Carico l'XML e comincio a parsarlo
	      $dom = new DomDocument('1.0');
	      $this->error="";
	      set_error_handler(array($this,"errorHandler"));    
	      $dom->loadXML($xml, LIBXML_NOENT);
	      //libxml_disable_entity_loader($previous);
	      restore_error_handler();
	      if ($this->error!="") {
			  $this->createDocument($param);	      
     	      $this->stateMessage('8');
		      $this->domout->formatOutput = true;
			  //$returnValue = $this->domout->saveXML();
			  $returnValue = $this->getReturnValue($param);
		      return $returnValue;
	      }	
	      foreach ($dom->childNodes as $child) {
	      	if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
		      	throw new SoapFault('wi400WsSiriAtg','Invalid XML: Detected use of illegal DOCTYPE');
	      	}
	      }
	      // Effettuo il parsing del file per recuperare i parametri
	      $param = $this->parseXML($dom);
	      if (!$param) {
			   $this->createDocument($param);		      	
	      	   $this->stateMessage('10');
	      	   $this->domout->formatOutput = true;
		   	   //$returnValue = $this->domout->saveXML();
		   	   $returnValue = $this->getReturnValue($param);
	           return $returnValue;
	      }
	      if (isset($param['debug_input'])) {
	      	throw new SoapFault('wi400WsSiriAtg', serialize($param));
	      }
	      // Creazione intestazione documento XML
		  $this->createDocument($param);	      
		  /*if (!is_resource($this->conn)){
        	   $this->stateMessage('3');
	      	   $this->domout->formatOutput = true;
		   	   $returnValue = $this->domout->saveXML();
	           return $returnValue;
		  }*/
		  if (isset($param['nolog'])) {
		  	   $this->save_XML=False;
		  }
		  // Salvataggio file XML per future consultazioni
		  if ($this->save_LOG) {
		  	  $time_start = microtime(true);
		  }		
		  $myid = uniqid().date("YmdHis");	  
		  if ($this->save_XML) {
			  $micro = substr(microtime(), 2, 8); 	
			  $file = $this->save_path."XMLI".$myid."_".$micro.".txt";		  	
			  $handle = fopen($file, "w+");
			  fwrite($handle, $xml);
			  fclose($handle);
		  }	
          // Ora che ho i parametri cerco il sistema informativo
          $flag = $this->sistemaInformativo($param);
		  if ($flag!='0') {
		      $this->stateMessage($flag);
		      $this->domout->formatOutput = true;
			  //$returnValue = $this->domout->saveXML();
			  $returnValue = $this->getReturnValue($param);
		      return $returnValue;		  	
		  }
		  $this->firstDataSetList=False;
		  // Creo il documento -- Già fatto sopra
		  //$this->createDocument($param);
		  $datavalidita= substr($param['dataValidita'], 6, 4).substr($param['dataValidita'], 3, 2).substr($param['dataValidita'], 0, 2);
		  // Preparo lo statement SQL
		  // Ora che ho i parametri cerco il sistema informativo
		  $filename = wi400File::getCommonFile("serialize", $this->sysinf."_FASEGMEN.dat");
		  $array=fileSerialized($filename);
	  	  if ($array == Null) {
				$this->connectDB();
		  		$sql = "SELECT A.* FROM FASEGMEN A, FAENTITA WHERE AENCOD = ASEENT AND ASESTA = '1' AND AENSTA = '1'";
			    $array = make_serialized_file($sql, $filename, array("ASEENT", "ASECOD"));
    	  }	
		  for ($j=0;$j<$this->dataSetCount;$j++) {
		  
		      // Cerco la routine da richiamare
		      $this->entita = $param['entityId'];
		      $this->segmenti[] = $param[$j]['segmento']; 
		      $this->current_segmento = $param[$j]['segmento']; 
		      $this->currentID = $param[$j]['keyid'];
		      if ($param[$j]['keyid']!="") {
		      	 $this->currentID = $param[$j]['keyid'];
		      } else {
		      	 $this->currentID = $j+1;
		      	 $param[$j]['keyid']= $this->currentID;
		      }
		      $keyarray = $param['entityId']."-".$param[$j]['segmento'];
		      $row = $array[$keyarray];
		      // Creo il documento base per l'XML
			  $param[$j]['desseg']=utf8_encode($row['ASEDCO']);
			  $this->auth=$row['ASEAUT'];
			  if ($this->auth=="S" && $consumer=="") {
			  	$this->createDocument($param);
			  	$this->stateMessage('B');
			  	$this->domout->formatOutput = true;
			  	//$returnValue = $this->domout->saveXML();
			  	$returnValue = $this->getReturnValue($param);
			  	return $returnValue;
			  }
		      //$this->createDocument($param);
		      $this->param = $param;
		      // Verifico se utilizzare il nome programma segmento o se servono i destinatari
		      if (!$dest){
		      	$pgm = trim($row['ASERIN']);
		      }else{
		      	$pgm = trim($row['ASERDE']);
		      }
		      $this->numkey = 0;
		      if ($row['ASENUK']>0) {
			      $this->key=explode(";",$row['ASEKEY']);
			      $this->stk=explode(";",$row['ASESTK']);
			      $this->numkey = $row['ASENUK'];
		      }
		      if (isset($pgm) && ($pgm!="")) {
		      	  $this->typeParameters = $row['ASEPRM'];	
		      	switch ($this->typeParameters) {
				    case '':
				        $key = $this->getKeyStandard($param, $j);
				        break;
				    case "S":
				        $key = $this->getKeyString($param, $j);
				        break;
				    case "D":
				        $key = $this->getKeyDs($param[$j], $pgm);
				        if ($key===False) {
				        	$this->stateMessage($this->lastState, $this->lastMessage, $this->currentID);
				        	$this->domout->formatOutput = true;
				        	//$returnValue = $this->domout->saveXML();
				        	//return $returnValue;
							continue;
				        }
				        break;
			        case "U":
			        	$key = $this->getKeyDs($param[$j], $pgm);
			        	if ($key===False) {
			        		$this->stateMessage($this->lastState, $this->lastMessage, $this->currentID);
			        		$this->domout->formatOutput = true;
			        		//$returnValue = $this->domout->saveXML();
			        		//return $returnValue;
							continue;
			        	}
			        	break;
					case "M":
						$key = $this->getKeyStandard($param, $j);
                        break;					
			        default:
			        	$key = $this->getKeyStandard($param, $j);
			        	break;			        		
				}
		      // Richiamo la funzione che reperisce i dati generica per ogni tipo di metodo messo a disposzione dal servizio
			  		// Controllo se devo richiamare un RPG oppure uno script PHP
			  		if ($pgm !='*RUNPHP') {  
					    if ($this->typeParameters!="M") {
							$do = $this->getDataXML($key,$datavalidita,$param[$j], $pgm, $dest);
						} else {
							//throw new SoapFault('wi400WsSiriAtg',json_encode($param));
							$do = $this->setData($key,$datavalidita,$param[$j], $pgm, $dest);
						}
			  		} else {
			  			$this->connectDB();
			  			require_once $_SERVER['DOCUMENT_ROOT'].$this->appBase.strtolower($row['ASEPHP']);
			  			if (isset($param['jsonEncode']) AND strtoupper($param['jsonEncode'])=="TRUE") {
			  				require_once $routine_path."/generali/xmlfunction.php";
			  				//$myarray = xml2array($XMLPHP);
			  				//return utf8_encode(json_encode($myarray));
			  				$myarray = xml2array($XMLPHP);
			  				return json_encode($myarray);
			  			}
			  			if (isset($param['jsonEncodeFast']) AND strtoupper($param['jsonEncodeFast'])=="TRUE") {
			  				//throw new SoapFault('wi400WsSiriAtg','You are not allowed to access this file.'.$datavalidita." ".$this->defaultRoutine. " ".$routine);
			  				return json_encode($XMLPHP);
			  			}
			  			if (isset($param['plainText']) AND strtoupper($param['plainText'])=="TRUE") {
			  				$text="\n###INIT###\n";
			  				$text.="WS_Code=".$this->lastState."$$$\n";
			  				$text.="WS_Messagge=".$this->lastMessage."$$$\n";
			  				if (count($XMLPHP)>0) {
			  						$text.="Segmento=". $param[$j]['segmento']."$$$\n";
			  						foreach ($XMLPHP as $key1 => $value1) {
			  							$text.=$key1."=".$value1."$$$\n";
			  						}
			  				}
			  				$text .="###FINE###\n";
			  				return $text;
			  			}
			  			return $XMLPHP;
			  		}
		      } else {
		      	      $this->stateMessage('7');
		      }
		  }
		  // Se è un XML rpg
		  if ($this->rpgXML == True) {
		  	$this->OUTPUTXML = str_replace("> <", "><", $this->OUTPUTXML);
		  	$this->OUTPUTXML = str_replace(">  <", "><", $this->OUTPUTXML);
		  	$this->OUTPUTXML = str_replace("&", "&amp;", $this->OUTPUTXML);
		  	/*$handle = fopen("/www/vai.txt", "w+");
		  	fwrite($handle, $this->OUTPUTXML);
		  	fclose($handle);*/
		  	//return utf8_encode($this->OUTPUTXML);
		  	return $this->OUTPUTXML;
		  }
		  // Finalizzazione del documento XML
	      $this->domout->formatOutput = true;
	      //$returnValue = $this->domout->saveXML();
	      // Ritorno in formato JSON
	      if (isset($param['jsonEncode']) AND strtoupper($param['jsonEncode'])=="TRUE") {
	      	$myarray = xml2array($this->domout->saveXML());
	      	return json_encode($myarray);
	      }
		  // Salvataggio LOG
	      if ($this->save_LOG) {
			  $file = $this->save_path."log.txt";
			  $handle = fopen($file, "a");
	          $time_end = microtime(true);
			  $time = $time_end - $time_start;
	 		  $dati = date("d:m:Y-H:i:s")." ".$_SERVER['REMOTE_ADDR']." Ent/Seg ".$param['entityId']."-".$param[0]['segmento']." ID ".$myid." Time ".substr($time,0, 12)." seconds\r\n";
			  fwrite($handle, $dati);
			  fclose($handle);
		  }
		  $returnValue = $this->getReturnValue($param);
		  if ($this->save_XML) {
			  $micro = substr(microtime(), 2, 8); 	
			  $file = $this->save_path."XMLO".$myid."_".$micro.".txt";
			  $handle = fopen($file, "w+");
			  fwrite($handle, $returnValue);
			  fclose($handle);
		  }
		  /*if (isset($param['plainText']) AND strtoupper($param['plainText'])=="TRUE") {
		  	  $text="\n###INIT###\n";
		  	  $text.="WS_Code=".$this->lastState."$$$\n";
		  	  $text.="WS_Messagge=".$this->lastMessage."$$$\n";
		  	  if (count($this->plainText)>0) {
				  foreach ($this->plainText as $key=>$value) {
				  	$text.="Segmento=".$key."$$$\n";
				  	foreach ($value as $key1 => $value1) {
				  		$text.=$key1."=".$value1."$$$\n";
				  	}
				  }
		  	  }
			  $text .="###FINE###\n";
			  return $text;
		  }*/
	      return $returnValue;
	}
	/**
	* @param string $datavalidita
	* @param string $codice
	*
	* @return string
	**/
	protected function getDataXML($codice=Null, $datavalidita, $param, $routine, $dest){
			global $settings, $routine_path;
			// Creazione USER SPACE per reperimento dati da routine RPG
				if (isset($settings['user_space_storeprocedure'])) {
					$this->full_user_space = "";
					$this->user_space_libl = "";
					$this->user_space_name = "";
				}
			    $property = array(
				            I5_INITSIZE        => '1', 
							I5_DESCRIPTION     => 'User SPACE Web-Services',
							I5_INIT_VALUE      => ' ',
							I5_AUTHORITY       => '*ALL',
							I5_LIBNAME         => $this->user_space_libl,
							I5_NAME            => $this->user_space_name
				            );
	     		$user_space = userspace_create($property);
	     		//throw new SoapFault('wi400WsSiriAtg','USER SPACE CREATE:'.$user_space['NAM'].print_r($user_space, True));
	     		if (isset($settings['user_space_storeprocedure'])) {
	     			$this->full_user_space = str_pad($user_space['NAM'], 10, " ",STR_PAD_RIGHT).$user_space['LIB'];
	     			$this->user_space_libl = $user_space['LIB'];
	     			$this->user_space_name = $user_space['NAM'];
	     			$this->user_space_all = $user_space['LIB']."/".$user_space['NAM'];
	     		}
	     		//throw new SoapFault('wi400WsSiriAtg','USER SPACE CREATE:'.$user_space['NAM'].print_r($user_space, True));
	     		//throw new SoapFault('wi400WsSiriAtg',$this->user_space_libl.'/'.$this->user_space_name." ".print_r($user_space, True));
	     		//throw new SoapFault('wi400WsSiriAtg','USER SPACE CREATE:'.$this->full_user_space);
				if (isset($settings['i5_toolkit'])) {
	     		if ($user_space === true) {
			    	//  Succes	so!
			    } else {
			    	if (i5_errormsg()=="CPF9870"){
				$ret = executeCommand('DLTUSRSPC USRSPC('.$this->user_space_libl.'/'.$this->user_space_name.')');
			  		$user_space = i5_userspace_create($property);
			    	} else {
						$this->stateMessage($this->domout, $resource, '4');
						return False;			    	
			    }
			    }
				}
				$pgm = new wi400Routine($routine, $this->conn);
				//$pgm->load_description('webservices');
				$pgm->load_description($this->defaultRoutine);
				$pgm->prepare();
				$pgm->set('USER_SPACE',$this->full_user_space);
				// Passaggio dei parametri chiave
				//throw new SoapFault('wi400WsSiriAtg', "errore C".$routine);
				switch ($this->typeParameters) {
					case '':
						$pgm->set('INPUT',$codice);
						break;
					case "S":
						$pgm->set('INPUT',$codice);
						break;
					case "D":
						require_once $routine_path."/generali/conversion.php";
						//$pgm->set('INPUT',ds2string($codice, $this->DS_INPUT));
						$overlays = array();
						$count = 1;
						foreach ($this->DS_INPUT as $array) {
							$type = wi400Routine::getType($array, True);
							//throw new SoapFault('wi400WsSiriAtg', $type);
							$pgm->setDSOverlay("INPUT", $array['Name'], $type , $count, $codice[$array['Name']]);
							$count = $count + dsFieldLen($array);
						}
						break;
					case "U":
						$uspc = userspace_prepare($this->user_space_all, $this->DS_INPUT, $this->conn, "W");
						$ret = userspace_put($uspc, $codice, 1);
						break;
					default:
						$pgm->set('INPUT',$codice);
						break;
				}
				//throw new SoapFault('wi400WsSiriAtg','You are not allowed to access this file.'.$datavalidita." ".$this->defaultRoutine. " ".$routine);
				$pgm->set('DATA',$datavalidita);
				$do = $pgm->call();  
				if ($do === true){
					if ($pgm->get('FLAG')=='0') {
					    $this->readUsrSpace($param['segmento'], $param['desseg'], $dest);
					} elseif ($pgm->get('FLAG')=='C') {
						$start = 101;
						//throw new SoapFault('wi400WsSiriAtg', "errore C");
						$filename = wi400File::getCommonFile("serialize", rtvLibre("DMSGUSRSPC", $this->conn)."_DMSGUSRSPC.dat");
						$desc1=fileSerialized($filename);
						if ($desc1 == Null) {
							$this->connectDB();
							$desc1 = create_descriptor("DMSGUSRSPC", $this->conn, null);
						}
						//throw new SoapFault('wi400WsSiriAtg',${'Codice Articolo'}." ciao ".var_dump($desc1));
						// Preparo la USER SPACE per la LETTURA
						$uspc = userspace_prepare($this->user_space_all, $desc1, $this->conn);
						foreach($desc1 as $key)
						{
							$parm_out[$key['Name']] = $key['Name'];
						}
						$ret = userspace_get($uspc, $parm_out, $start);
						extract($ret);
						//throw new SoapFault('wi400WsSiriAtg',${'Codice Articolo'}." ciao ".var_dump($ret));
						$this->stateMessage($MSG_ID, $MSG_DES);
					} else {
					if ($this->multiDataSet==False){
							$this->stateMessage($pgm->get('FLAG'));
							return false;
						}
					}
				} else {
					$this->stateMessage('6');
					return false;
				}
				return true;
	}
	protected function getReturnValue($param=array()) {
		
		if (isset($param['plainText']) AND strtoupper($param['plainText'])=="TRUE") {
			$text="\n###INIT###\n";
			$text.="WS_Code=".$this->lastState."$$$\n";
			$text.="WS_Messagge=".$this->lastMessage."$$$\n";
			if (count($this->plainText)>0) {
				foreach ($this->plainText as $key=>$value) {
					$text.="Segmento=".$key."$$$\n";
					foreach ($value as $key1 => $value1) {
						$text.=$key1."=".$value1."$$$\n";
					}
				}
			}
			$text .="###FINE###\n";
			$returnValue = $text;
		}	
			else {
				$returnValue = $this->domout->saveXML();				 
		}
		return $returnValue;
	}
	/**  readUsrSpace: Funzione di lettura delle user space con i dati di ritorno e creazione XML con data set ritornati
	 *  parametri
	 *  @param dom   object documento XML
	 *  @param resource object di dom con il primo child
	 *  @param idDataset   string codice dataset estratto
	 *  @param descDataset string descrizione dataset estratto
	 */
	protected function readUsrSpace($idDataset, $descDataset, $dest){
            global $routine_path, $settings;
            
		    $filename = wi400File::getCommonFile("serialize", rtvLibre("DARCUSRSPC", $this->conn)."_DARCUSRSPC.dat");
		    $desc1=fileSerialized($filename);
		    if ($desc1 == Null) {
			    $this->connectDB();	
			    $desc1 = create_descriptor("DARCUSRSPC", $this->conn, null);
		    }
			// Preparo la USER SPACE per la LETTURA
		    //$prima =$this->user_space_libl."/".$this->user_space_name;
		    //throw new SoapFault('wi400WsSiriAtg','USER SPACE CREATE:'.$this->full_user_space." - ".$this->user_space_libl.'/'.$this->user_space_name. " - ".$prima);
		    
		    $uspc = userspace_prepare($this->user_space_all, $desc1, $this->conn);
		    //throw new SoapFault('wi400WsSiriAtg','USER SPACE CREATE:'.$this->full_user_space." - ".$this->user_space_libl.'/'.$this->user_space_name. " - ".$prima);
		    
			$parm_out=Array();
	        // Valorizzo Array dei parametri di output
			foreach($desc1 as $key)
					{
						$parm_out[$key['Name']] = $key['Name'];
					}
					
					$ret = userspace_get($uspc, $parm_out);
			
			extract($ret);
			$this->rpgXML = False;
			// Verifico se si la routine mi ha tornato un XML
			if ($USR_OUT=='XML' && $USR_LEN> 0) {
				 require_once $routine_path."/generali/conversion.php";
				 $this->rpgXML = True;                                                                                 
				 //$this->rpgXML = file_get_contents('/QSYS.lib/qtemp.lib/siritest.usrspc');                           
				 $len = $USR_NUM * $USR_LEN;                                                                           
				 $desc1 = array(array("Name"=>"DATI", "IO"=>I5_INOUT, "Type"=>I5_TYPE_BYTE, "Length"=>"$USR_LEN"));        
				 $uspc = userspace_prepare($this->user_space_libl.'/'.$this->user_space_name, $desc1, $this->conn); 
				 $start = 101;  
				 $mydati = "";                                                                                       
				 for ($i=0; $i<= $USR_NUM; $i++) {                                                                   
					  $parm_out=Array("DATI"=>"DATI");                                                                     
					  $ret = userspace_get($uspc, $parm_out, $start); 
					  extract($ret);
					  $mydati =e2a(hextostr($DATI));
					  if (strlen($mydati) < $USR_LEN) {
					  	$mydati = str_pad($mydati, $USR_LEN, " ");
					  }  
					  $mydati = utf8_encode($mydati);
					  $this->OUTPUTXML .=$mydati;
					  $start = $start + $USR_LEN;
				 }
				 //$this->OUTPUTXML = e2a(hextostr($this->OUTPUTXML));		                                                                           
				 return True;                                                                                          				
			}
		    if ($USR_NUM==0 AND $this->multiDataSet==False){
				    	$this->stateMessage('2');
						return false;
		    } else {
		    	    if ($this->firstDataSetList==False) {
			    	    $this->stateMessage("0");
			    	    $datalist = $this->resource->appendChild($this->domout->createElement('datasetList'));
			    	    $this->dataList = $datalist;
			    	    $this->firstDataSetList=True;
		    	    }	
		    	    $filename = wi400File::getCommonFile("serialize", rtvLibre($USR_DS, $this->conn)."_".$USR_DS.".dat");
		    		$desc=fileSerialized($filename);
		    		if ($desc == Null) {
			    		$this->connectDB();	
					    $desc = create_descriptor($USR_DS, $this->conn, null, True);
				    }
		    	    //$desc = create_descriptor($USR_DS, $this->conn, null, True);
				    
					$uspc = userspace_prepare($this->user_space_all, $desc, $this->conn);
					$dataset = $this->dataList->appendChild($this->domout->createElement('dataset'));
					$field_name = $this->domout->createAttribute('id'); 
			    	$dataset->appendChild($field_name); 
			    	$name = $this->domout->createTextNode($idDataset);
					$field_name->appendChild($name);		 
					$field_name = $this->domout->createAttribute('value'); 
			    	$dataset->appendChild($field_name); 
			    	$name = $this->domout->createTextNode($descDataset);
					$field_name->appendChild($name);
					// Verifico se è un multi record
					if ($this->typeParameters=="M") {
						$ult_child = $dataset->appendChild($this->domout->createElement('records'));
					}
					// Key ID se presente:
					if ($this->keyIdEnable==True) {
						$field_name = $this->domout->createAttribute('keyid');
						$dataset->appendChild($field_name);
						$name = $this->domout->createTextNode($this->currentID);
						$field_name->appendChild($name);
					}
					
					$ult_child = $dataset;
					if ($dest) {
					      $entitys = $dataset->appendChild($this->domout->createElement('entities'));
					      $entity = $entitys->appendChild($this->domout->createElement('entity'));
					      $field_name = $this->domout->createAttribute('name'); 
	    				  $entity->appendChild($field_name); 
	    				  $name = $this->domout->createTextNode("NEGOZIO");
					      $field_name->appendChild($name);
					}
					$start = 101;
					$mk = false;
					// Attenzione i dati mi devono già tornare ordinati per chiave ..
		    		if ($this->numkey>0) {
		    		
						$mk= True;
						// Valore = Chiave
						$f = 0;
						$xml_key=array();
						$xml_stk=array();
						foreach($this->key as $key=>$valore)
							{
								$xml_key[$valore]=$valore;
								$xml_stk[$valore]=$this->stk[$f];
								$f++;
							}
						// Imposto il padre di default di tutte le chiavi e la segnalazione di rottura
						$xml_child = array();
						$xml_break = array();
						$xml_entity= array();
							foreach($xml_key as $key)
							{
								$xml_child[$key]=$ult_child;
								$xml_break[$key]=True;
								$old_xml_key[$key]="";
							}						
					}
					// Ciclo su tutte le ricorrenze
					for ($i=0;$i<$USR_NUM;$i++)
					{
					$parm_out=Array();
					foreach($desc as $key)
							{
								$parm_out[$key['Name']] = $key['Name'];
							}
					$ret = userspace_get($uspc, $parm_out, $start);
					foreach ($ret as $key=>$value) {
						${$key}=$value;
					}
					//throw new SoapFault('wi400WsSiriAtg',${'Codice Articolo'}." ciao ");
					// monitorare errore di sistema
					if (!$ret) return i5_errormsg()." errore ".i5_errno();
					// Verifico se ci sono rotture di chiavi per la produzinoe dell'XML
							//throw new SoapFault('wi400WsSiriAtg', var_dump($parm_out));
					$ix=0;
					$only_one = False;		
					// Verifico tutte le rotture di chiavi
					if (!$dest && $this->typeParameters!="M") {
							// Verifico se ci sono rotture di chiavi
							if ($mk){
								 // Ciclo sull'array delle chiavi
								 foreach($xml_key as $chiave){
									 $ix++;
									 $yz=0;
									 foreach($desc as $key){
									 	$yz++;
									 	if ($chiave == $yz) break;
									 }
									 // Rottura della prima Key
								     if(${$key['Name']}!=$old_xml_key[$chiave]) {
								       	  // Verifico se c'è già stata una rottura di chiave, in questo caso scrivo l'entity
								       	  if ($xml_break[$chiave]==True) {
								       	   	 $entitys = $xml_child[$chiave]->appendChild($this->domout->createElement('entities'));
									         $entity = $entitys->appendChild($this->domout->createElement('entity'));
									         $field_name = $this->domout->createAttribute('id'); 
					    				     $entity->appendChild($field_name); 
					    				     $name = $this->domout->createTextNode(htmlspecialchars($xml_stk[$chiave]));
									         $field_name->appendChild($name);
									         $field_name = $this->domout->createAttribute('name'); 
					    				     $entity->appendChild($field_name); 
					    				     $name = $this->domout->createTextNode($key['Name']);
									         $field_name->appendChild($name);
									         $xml_entity[$chiave]=$entity;
								       	  	 $xml_break[$chiave]=False;
								       	  }
									      $instance = $xml_entity[$chiave]->appendChild($this->domout->createElement("instance"));
										  $field_name = $this->domout->createAttribute('id'); 
					    				  $instance->appendChild($field_name); 
					    				  //$name = $this->domout->createTextNode(utf8_encode($$key['Name']));
					    				  $name = $this->domout->createTextNode(${$key['Name']});
										  $field_name->appendChild($name);
									      $old_xml_key[$chiave]=${$key['Name']};
									      $ult_child = $instance;
								     		// Aggiorno i child delle chiavi sucessive
								     		$j=0;
											foreach($xml_key as $key)
											{
												$j++;
												if ($j>$ix){
												$xml_child[$key]=$ult_child;
												// Devo fare rompere tutte le chiavi successive
												$xml_break[$key]=True;
												$old_xml_key[$key]="";
												}
											}
								     }
								}
						    }
					}
					// Fine verifica rotture di chiavi
					$first = True;
					foreach($desc as $key){
						if ($this->typeParameters!="M") {
							if (!$dest){
								//throw new SoapFault('wi400WsSiriAtg', print_r($key));
							    if (!$only_one) {
									$entity = $ult_child->appendChild($this->domout->createElement('attributes'));
									$ult_child = $entity;
									$only_one=True;
								}
								$field = $ult_child->appendChild($this->domout->createElement("attribute"));
								$field_name = $this->domout->createAttribute('id'); 
		    					$field->appendChild($field_name); 
		    					//$name = $this->domout->createTextNode(utf8_encode($key['Name']));
		    					$name = $this->domout->createTextNode(htmlspecialchars($key['Name']));
								$field_name->appendChild($name);
								$field_name = $this->domout->createAttribute('value'); 
		    					$field->appendChild($field_name);
	    						// Taccone per la data
	    						if (strtoupper($key['Name'])=='DATA DECORRENZA') {
	    						  //$data = utf8_encode($$key['Name']);
	    						  $data = ${$key['Name']};
	    						  $datfmt = substr($data, 6, 2)."-".substr($data, 4, 2)."-".substr($data, 0, 4);
	    						  $name = $this->domout->createTextNode($datfmt);	 
	    						} else {
	    							// Patch per gestire correttamente la conversione del carattere EURO IN UTF-8
	    							//$string = utf8_encode($$key['Name']);
	    							$string = ${$key['Name']};
	    							//$string = utf8_decode($string);
	    							$string = str_replace(chr(0xC2).chr(0xA4) , chr(0xE2).chr(0x82).chr(0xAC), $string);
	    							//$string = "PIPPO";
	    							//$string = utf8_encode($string);
	    							$name = $this->domout->createTextNode($string);
	    							//$name = $this->domout->createTextNode(utf8_encode($$key['Name']) );
	    						}
	    						$this->plainText[$idDataset][$key['Name']]=${$key['Name']};
								$field_name->appendChild($name);
					        } else {
						        $field = $entity->appendChild($this->domout->createElement("instance"));
								$field_name = $this->domout->createAttribute('id'); 
	    						$field->appendChild($field_name); 
	    						$name = $this->domout->createTextNode(htmlspecialchars($key['Name']));
	    						$field_name->appendChild($name);
	    						//$name = $this->domout->createTextNode(utf8_encode($$key['Name'] ));
	    						$field_name = $this->domout->createAttribute('value');
	    						$field->appendChild($field_name); 
	    						$name = $this->domout->createTextNode(${$key['Name']});
								$field_name->appendChild($name);
							}
						} else {
							if ($first==True) {
								$record = $ult_child->appendChild($this->domout->createElement('record'));
								$first = False;
								if (isset($WS_INT_ID) && $WS_INT_ID!="") {
									$field_name = $this->domout->createAttribute('id');
									$record->appendChild($field_name);
									$name = $this->domout->createTextNode(htmlspecialchars($WS_INT_ID));
									$field_name->appendChild($name);
								}
								if (isset($WS_INT_COD) && $WS_INT_COD!="") {
									$field_name = $this->domout->createAttribute('code');
									$record->appendChild($field_name);
									$name = $this->domout->createTextNode(htmlspecialchars($WS_INT_COD));
									$field_name->appendChild($name);
								}
								if (isset($WS_INT_MSG) && $WS_INT_MSG!="") {
									$field_name = $this->domout->createAttribute('message');
									$record->appendChild($field_name);
									$name = $this->domout->createTextNode(htmlspecialchars($WS_INT_MSG));
									$field_name->appendChild($name);
								}
							}
							if (strpos($key['Name'], "WS_INT_")!==False) {
								// Campi Statici
							} else {
								$field = $record->appendChild($this->domout->createElement("field"));
								$field_name = $this->domout->createAttribute('id');
								$field->appendChild($field_name);
								$name = $this->domout->createTextNode(htmlspecialchars($key['Name']));
								$field_name->appendChild($name);
								$field_name = $this->domout->createAttribute('value');
								$field->appendChild($field_name);
								$name = $this->domout->createTextNode(htmlspecialchars(${$key['Name']}));
								$field_name->appendChild($name);
							}
						}
						}
						$start = $start + $USR_LEN;
					}
					//$ret = executeCommand('DLTUSRSPC USRSPC('.$this->user_space_libl.'/'.$this->user_space_name.')');
		    }
	}
	/** parseXML: Parsa l'XML e recupera i suoi parametri in un array
	 * @param dom       object documento XML 
	 * 
	 * @return parm array  array con i parametri
	 */
	protected function parseXML($dom){
		$array = array();	
		// Cerco se c'è resource	
		$params = $dom->getElementsByTagName('resource'); // Find Sections
		// .. se non c'è
		if (!isset($params->item(0)->nodeValue) or ($params->item(0)->nodeValue)=="") $params = $dom->getElementsByTagName('event'); // Find Sections
		// Se non ho trovato nulla errore
		if (!isset($params)) return;
		$k=0;
		foreach ($params as $param){
		      $array['entityId']=$params->item($k)->getAttribute('entityId');
		      $array['entityDesc']=$params->item($k)->getAttribute('entityDesc');
		      $array['id']=$params->item($k)->getAttribute('id');
		      $params2 = $params->item($k)->getElementsByTagName('attributes'); //Vado in profondità sugli attributi
		      $i=0;
		      foreach ($params2 as $p) {
		            $params3 = $params2->item($i)->getElementsByTagName('attribute'); //dig Arti into Categories
			                 $j=0;
		                     foreach ($params3 as $p2)
		                     {
		                     	$array[$params3->item($j)->getAttribute('id')]= $params3->item($j)->getAttribute('value');
		                        $j++;   
		                     }              
		      $i++;
		      }
		      // Verifico se ci sono informazioni aggiuntivi come il dataset e le chiavi aggiuntive
		      $params2 = $params->item($k)->getElementsByTagName('datasetList'); //digg categories with in Section
		      $i=0; // values is used to iterate categories  
		      foreach ($params2 as $p) {
		                 $params3 = $params2->item($i)->getElementsByTagName('dataset'); //dig Arti into Categories
		                   $j=0;//values used to interate Arti
		                   foreach ($params3 as $p2)
		                   {
		                   	$array[$j]['segmento']= $params3->item($j)->getAttribute('id');
		                   	$array[$j]['desseg'] = $params3->item($j)->getAttribute('value');
		                   	$array[$j]['keyid']= $params3->item($j)->getAttribute('keyid');;
		                   	$params4 = $params3->item($j)->getElementsByTagName('attribute'); 
		                    $p=0;
		                    // ulteriori chiavi
			                foreach ($params4 as $p3)
			                   {
			                    $array[$j]['key'.$p]= $params4->item($p)->getAttribute('value');
			                   	$array[$j]['id'.$p]= $params4->item($p)->getAttribute('id');
			                   	$array['segmento'][$params3->item($j)->getAttribute('id')][$params4->item($p)->getAttribute('id')] = $params4->item($p)->getAttribute('value');
			                   	$p++;   
			                }
				            $array[$j]['keyCount']=$p;
				            // Cerco eventuale presenza del tag RECORD
				            $idd= $params3->item($j)->getAttribute('id');
				            $params4 = $params3->item($i)->getElementsByTagName('record');
				            $aa=0;
				            // ulteriori chiavi
				            foreach ($params4 as $p3)
				            {
				            	$id = $params4->item($aa)->getAttribute('ID');
				            	// Controllo se esiste già l'ID
				            	if (isset($array['segmento'][$idd]['record'][$id])) {
				            		//throw new SoapFault('wi400WsSiriAtg','Non trovato ID');
				            		$id = $id."_".($aa+1);
				            	}
				            	if (!isset($id) || $id=="") {
				            		$id = "ID".($aa+1);
				            	}
				            	$params5 = $params4->item($aa)->getElementsByTagName('field'); //Vado in profondità sugli attributi
				            	$w=0;
				            	foreach ($params5 as $p5)
				            	{
				            		$array['segmento'][$idd]['record'][$id][$params5->item($w)->getAttribute('id')]= $params5->item($w)->getAttribute('value');
				            		$w++;
				            	}
				            	$aa++;
				            }
				            $array['segmento'][$idd]['recordCount']=$aa;
				            $this->recCount = $this->recCount + $aa;
				            // Fine ricerca RECORD
				            $j++;    
		                   }
		                   // Verifico se è un multidataset
		                   if ($j>1) {
		                   	    $this->multiDataSet = True;          
		                   } else {
		                   	    $this->multiDataSet = False;
		                   }
		                   $this->dataSetCount = $j;
		      $i++;
		      }      
		$k++;    
		}
	  return $array;
	}
	/**
	* @param string $datavalidita
	* @param string $codice
	*
	* @return string
	**/
	private function setData($codice=Null, $datavalidita, $param, $routine, $dest){
			global $settings;
			// Creazione USER SPACE per reperimento dati da routine RPG
				if (isset($settings['user_space_storeprocedure'])) {
					$this->full_user_space = "";
					$this->user_space_libl = "";
					$this->user_space_name = "";
				}
			    $property = array(
				            I5_INITSIZE        => '500000', 
							I5_DESCRIPTION     => 'User SPACE Web-Services',
							I5_INIT_VALUE      => ' ',
							I5_AUTHORITY       => '*ALL',
							I5_LIBNAME         => $this->user_space_libl,
							I5_NAME            => $this->user_space_name
				            );
	     		$user_space = userspace_create($property);
	     		if (isset($settings['user_space_storeprocedure'])) {
	     			$this->full_user_space = str_pad($user_space['NAM'], 10, " ",STR_PAD_RIGHT).$user_space['LIB'];
	     			$this->user_space_libl = trim($user_space['LIB']);
	     			$this->user_space_name = trim($user_space['NAM']);
	     			$this->user_space_all = $user_space['LIB']."/".$user_space['NAM'];
	     		}
	     		if (isset($settings['i5_toolkit'])) {
				if ($user_space === true) {
			    	//  Successo!
			    } else {
			    	if (i5_errormsg()=="CPF9870"){
				$ret = executeCommand('DLTUSRSPC USRSPC('.$this->user_space_libl.'/'.$this->user_space_name.')');
			  		$user_space = i5_userspace_create($property);
			    	} else {
		  		        $this->arrayRisultati['dataset'][$key]['global']="4";	
		  		  		$this->globalCode = "15";   		  		        		    		
						return False;			    	
			    }
			    }
	     		}
				$pgm = new wi400Routine($routine, $this->conn);
				$pgm->load_description($this->defaultRoutine);
				$pgm->prepare();
				// Preparo la USER SPACE per la SCRITTURA
				// Default il formato è I + Nome routine meno primo byte;
				$ds_input = "I".substr($routine, 1);
				$desc = $this->getDescriptor($ds_input, True);
				$lenght=0;
				foreach ($desc as $key=>$value) {
					$lenght=$lenght+round($value['Length'], 0);
				}
				$uspc = userspace_prepare($this->user_space_all, $desc, $this->conn, "W");				
				$start = 101;
				$parm_out = array();
				$this->sa = array();
				$yy = 0;
				$ricorrenze = 0;
				foreach ($this->param['segmento'][$param['segmento']]['record'] as $key=>$value) {
					    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['id']=$key;
				    	$parm_out['Id record input']=$key;
					    $skip = False;
						foreach($desc as $key2) {
							if ($skip) break;
							if($key2['Name']!='Id record input') {
							if (isset($value[$key2['Name']])) {
							$type = $key2['Type'];								
							$parm_out[$key2['Name']] = $value[$key2['Name']];
							$myValore = $value[$key2['Name']];
							if ($type ==I5_TYPE_PACKED || $type ==I5_TYPE_ZONED) {
							$myValore= str_replace(array(".","-","+"), '',$myValore); 
								if (strlen($myValore)>$key2['Length']) {
				                    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Code']='5';
				                    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Message']=$key2['Name'].retriveSetMessage('5');
				                    $skip = True;
				                    break;
								}
							} else {
							if (strlen($myValore)>$key2['Length']) {
			                    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Code']='9';
			                    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Message']=$key2['Name'].retriveSetMessage('9');
			                    $skip = True;
			                    break;								
							}
							}
							if ($type ==I5_TYPE_PACKED || $type ==I5_TYPE_ZONED){
								$array = explode('.',$value[$key2['Name']]);
								$format= explode('.',$key2['Length']);
								$elementi = count($array);
								if ($elementi > 2) {
				                    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Code']='6';
				                    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Message']=$key2['Name'].retriveSetMessage('6');
				                    $skip = True;
				                    break;
								} else {
									if ($elementi == 2) {
										$format[0]=$format[0]-$format[1];
									}
									for ($i=0; $i<$elementi; $i++) {
										if (!is_numeric($array[$i])) {
						                    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Code']='6';
						                    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Message']=$key2['Name'].retriveSetMessage('6');
						                    $skip = True;
						                    break;
										}
										if (strlen($array[$i])>$format[$i]) {
						                    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Code']='7';
						                    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Message']=$key2['Name'].retriveSetMessage('7');
						                    $skip = True;
											break;			
										}
									}
								}
								}
						} else {
							// Errore Bloccante. Dataset incompleto
						    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Code']='8';
						    $this->arrayRisultati['dataset'][$codice]['record'][$yy]['Message']="RECORD ID ".$key.retriveSetMessage('8');
			                $skip = True;
							break;	
							//$parm_out[$key2['Name']] = "";
						}
						}

					}

					if (!$skip) {
						$this->sa[]=$yy;
						$ricorrenze++;
						$ret = userspace_put($uspc, $parm_out, $start);
						$start = $start + $lenght;
					}
					$yy++;
				}
				//
				// Scrittura primi 100 byte della user-space
				$desc1 = $this->getDescriptor("DARCUSRSPC");
				// Preparo la USER SPACE per la SCRITTURA
				$uspc = userspace_prepare($this->user_space_all, $desc1, $this->conn, "W");
				$parm_out=Array();
				//$desc = $this->getDescriptor("IMOSCAPDV", True);
		        //throw new SoapFault('wi400WsSetAtg', $param['recordCount']);
				$parm_out['USR_NUM'] = $ricorrenze;
				$parm_out['USR_LEN'] = $lenght;
				$parm_out['USR_DS'] = $ds_input;
				$ret = userspace_put($uspc, $parm_out, 1);
				$pgm->set('USER_SPACE',$this->full_user_space);
				$pgm->set('INPUT',$codice);
				$pgm->set('DATA',$datavalidita);
				$do = $pgm->call();  
				if ($do === true){
					if ($pgm->get('FLAG')=='0') {
						$this->readUsrSpace($param['segmento'], $param['desseg'], $dest);
					    //$this->readUsrSpace($codice, $param, $dest);
					} else {
						if ($this->multiDataSet==False){
						$this->stateMessage($pgm->get('FLAG'));
						return false;
						}
					}
				} else {
					//$this->stateMessage('6');
		  		    $this->arrayRisultati['dataset'][$key]['global']="6";
		  		    $this->globalCode = "15";   						
					return false;
				}
				return true;
	}
}
?>
