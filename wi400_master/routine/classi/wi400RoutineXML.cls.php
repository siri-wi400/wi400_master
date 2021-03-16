<?php

/**
 * @name wi400Routine.class.php 
 * @desc Classe per accesso alle routine applicativo SIAD
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Luca Zovi
 * @version 1.01 14/07/2008   
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 * 
 * Da fare. Ritorno nome DS utilizzata dalla routine getDSName, getDSNumField
 * 
 */
 
class wi400Routine {
	
	private $prog;
	public $RPGProgram;
	private $parmout;
	private $parminp;
	private $desc;
	private $LastErr;
	private $autoreset;
	private $path;
	private $pathDefault;
	private $debug;
	private $package;
	private $dsCount;
	private $dim;
	private $parmbbb;
    private $stmt;	
    private $InternalKey ='';
    private $ControlKey ='*here'; 
    private $error;
    private $inputXML;
    public $OutputXML;
    private $numerici = array();
    private $overlay = array();
	    
	/**
	 * Costruttore della classe
	 *
	 * @param string $name			: Nome della routine da richiamare
	 * @param string $connzend		: ID della connessione a Zend
	 * @param boolean $autoreset	: Se Impostato a True resetta l'environment ad ogni richiamo successivo
	 * @param boolean $debug		: 
	 */
	function __construct($name, $connzend=null, $autoreset=True, $debug=false, $package='settings') {
		global $settings;
		
		$this->parminp = array();
		$this->parmout = array();
		$this->overlay = array();
		$this->RPGProgram = strtoupper($name);
		$this->connzend = $connzend;
		$this->autoreset = $autoreset;
		if ($package=='settings') {
			  $package = $settings['package'];
		}
		$this->package = $package;
		$this->path = 'base/package/'.$package.'/routine/';
		$this->pathDefault = 'base/package/default/routine/';
		$this->debug = $debug;
	}
	function getOutputXML() {
		return $this->OutputXML;
	}
	/**
	 * Funzione di caricamento del prototipo di chiamata. Vine cercato un script contenente le informazioni
	 * con il nome siad_<nome routine>.php
	 * 
	 * ATTENZIONE!: Tutti i descrittori esterni devono essere in minuscolo altrimenti non vengono inclusi.
	 * 
	 * @param string $description	: 
	 *
	 * @return boolean 	 Esito della chiamata: True eseguita con successo, False non eseguita per errori
	 */
	function load_description($description=null, $tracciato=null, $reset = False, $count = 0) {
		global $settings;
		// Controllo se è già in sessione
		// @todo salvare in sessione XML e non descrittore iniziale
		$file = '';
		if ($reset == True) unset($_SESSION[$this->RPGProgram]);
		if (isset($_SESSION[$this->RPGProgram])) {
			$this->desc = $_SESSION[$this->RPGProgram];
		} 
		else {
			// Cerco il descrittore, prima su package e se non trovato su package
			if (isset($description)){
				$file = p13n($this->path.$this->package."_".strtolower($description).".php");
				if (!$file) {
					$file = p13n($this->pathDefault.strtolower($description).".php");
				}
			} 
			else {
				$file = p13n($this->path.$this->package."_".strtolower($this->RPGProgram).".php");
				if (!$file) {
					$file = p13n($this->pathDefault.strtolower($this->RPGProgram).".php");
				}
			}
			if ($file) {
				include $file;
			} else {
				$export_description = '';
				echo "<br>Program not Found:".$this->RPGProgram;
			}
			$this->desc = $export_description;
			$_SESSION[$this->RPGProgram] = $export_description;
		}
		return true;
	}
	
	/**
	 * Preparazione del prototipo di chiamata
	 * La funzione prepara la chiamata e imposta gli arrai di input e di output costruendoli automaticamente
	 * dall'array del descrittore caricato con load_Description()
	 * 
	 * @return boolean	Esito della chiamata: True eseguita con successo, False non eseguita per errori
	 */
	function prepare() {
		global $db, $messageContext, $settings;
         
        /*if (!isset($db->getCallPGM())) {
        	$db->make_connection();
        }*/

		$this->parminp = $this->input_param($this->desc);
		
		return true;
	}
	/**
	 * Pulizia ultimo errore verificatosi all'interno della classe
	 * 
	 */
	function ClearLastErr() {
		$this->lastErr= null;
	}
	/**
	 * Chiamata effettiva del programma
	 * @param bool $reset: se resettare i parametri di input
	 * @param bool $inputDSParm: se la DS in entrata contiene parametri di input da settare
	 * 
	 * @return boolean	Esito della chiamata: True eseguita con successo, False non eseguita per errori
	 */
	function call($reset = True, $inputDSParam=False) {
		
		global $db, $messagContext;
		
		if ($reset) {
        	$this->reset();
		}	
		$this->ClearLastErr();

		//$InternalKey= INTERNALKEY;
		//$InternalKey= "/tmp/".session_id();
		//$InternalKey = "/tmp/aaaaa";
		//$ControlKey = $this->ControlKey;
		//$ControlKey = CONTROLKEY;
		$OutputXML  = "";		
        $InputXML = $this->getInputXML($inputDSParam);
        //echo $InputXML;
		//$OutputXML = callXMLService($InputXML, $InternalKey, $ControlKey, $db->callPGM);
		$OutputXML = callXMLService($InputXML, Null , Null , $db->getCallPGM());
		if(!$OutputXML ) {
			//echo "bad execute: " . "\$db->lastErrorMsg()".$db->getCallPGM().$this->RPGProgram.$OutputXML;
			developer_debug("Errore function call: outputXML è vuoto!");
			return false;
		}
		//if ($this->RPGProgram == 'RTSART') {
			//$f = fopen("/tmp/wi400/log.txt", "a+");
			//fwrite($f, $InputXML);		
			//fwrite($f, $OutputXML);
			//fclose($f);
		//}
		//echo "Name:".$this->RPGProgram. " - ".$OutputXML;
        $this->OutputXML = $OutputXML;
        //echo $OutputXML;
        if (strpos($OutputXML, "<errnoxml>",0)) {
        	if (isset($messageContext)) {
        		error_log("Errore richiamo routine ".$this->RPGProgram);
        	}
        	return false;
        }
        //$this->internalParse($OutputXML);
		return True;	
	}
	
	/**
	 * Settaggio parametri di input (NO DS)
	 *
	 * @param string $var	: Nome della variabile
	 * @param string $val	: Valore da settare
	 */
	function set($var, $val) {	
		if (isset($this->parminp[$var])) {
			$this->parminp[$var] = $val;
			return true;	
		} else {
			developer_debug("Errore set ".$var." -> ".$val);
			return false;
		}
	}
	
	/**
	 * Settaggio parametri di input per DS
	 *
	 * @param string $ds	: Nome della DS
	 * @param string $var	: Nome della variabile
	 * @param string $val	: Valore da settare
	 */
	function setDSParm($ds, $var, $val) {
		if (isset($this->parminp[$ds][$var])) {
			$this->parminp[$ds][$var] = $val; 
			return true;
		} else {
			developer_debug("Errore setDSParm ".$var." -> ".$val);
			return false;
		}	
	}
	function setDSOverlay($ds, $name, $type, $offset, $value) {
		$this->overlay[$ds][]=array('name'=>$name, 'type'=>$type, 'offset'=>$offset,'value'=>$value);
	}
	function setDSParm_array($ds,$array) {
		$this->parminp[$ds] = $array;
	}
	
	/**
	 * Recupero valore dai parametri di output (NO DS)
	 * 
	 * @param string $var	: Nome della variabile
	 * 
	 * @return string	Contenuto della variabile
	 */	
	function get($var, $occurence=1) {
		global $messageContext, $settings;
		// Controllo se è una DS
		if (isset($this->dsCount[$var])) {
			$start = 1;
			$numds = 1;
			if ($this->dsCount[$var] > 1) {
			   $numds = $this->dsCount[$var];
			}
			if ($occurence ==1) {
			   $numds = 1;
			}
//			echo "OUTPUTXML: "; var_dump($this->OutputXML); echo "<br>";
			// SIMPLE XML VA IN CRASH SE CI SONO CARATTERI STRANI ....
			if (isset($settings['convert_special_char_xml']) && $settings['convert_special_char_xml']==True) {
//				echo "PARSE<br>";
				//$this->OutputXML = str_replace('&', '&amp;', $this->OutputXML);
				$parseXML = preg_replace("/(<data\b[^>]*>)(.*?)<\/data>/i", "$1<![CDATA[$2]]></data>", $this->OutputXML);
				//$this->OutputXML = str_replace(array("<data><", "></data>"), array("<data><![CDATA[", "]]></data>"), $this->OutputXML);
				//$this->OutputXML = htmlspecialchars($this->OutputXML, ENT_NOQUOTES | ENT_SUBSTITUTE);
				//echo $this->OutputXML;die();
			} else {
				$parseXML = $this->OutputXML;
			}
			// Devo cercare se ci sono delle sotto DS
			for ($i=1;$i<=$numds;$i++) {
					$dati = array();
					// SIMPLE XML VA IN CRASH SE CI SONO CARATTERI STRANI ....
					//if (isset($settings['convert_special_char_xml']) && $settings['convert_special_char_xml']==True) {
						//$this->OutputXML = str_replace('&', '&amp;', $this->OutputXML);
						//$this->OutputXML = preg_replace("/(<data\b[^>]*>)(.*?)<\/data>/i", "$1<![CDATA[$2]]></data>", $this->OutputXML);
						//$this->OutputXML = str_replace(array("<data><", "></data>"), array("<data><![CDATA[", "]]></data>"), $this->OutputXML);
						//$this->OutputXML = htmlspecialchars($this->OutputXML, ENT_NOQUOTES | ENT_SUBSTITUTE);  
					//}
		            $dove = strpos($parseXML, "var='$var'", $start);
		            if ($dove===False) {
		            	developer_debug("Ds non esistente");
		            	continue;
		            }
		            // Trovo la fine assoluta della DS Principale
		            //$fineds = strpos($this->OutputXML, "</ds>",$dove);
		            $fineds = $this->findEndDs($dove, $parseXML);
		            //echo "<br>FINE DS:".$fineds;
		            //echo "MYDS-->".substr($this->OutputXML, $dove-4,$fineds-($dove-9));
		            $xml = simplexml_load_string(substr($parseXML, $dove-4,$fineds-($dove-9)),'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
//		            echo "XML: "; var_dump($parseXML); echo "<br>";
//		            echo "Simplexml_load_string: ".$this->OutputXML." - DOVE: $dove - FINEDS: $fineds<br>";
//		            echo "XML:<pre>"; print_r($xml); echo "</pre>";
		            /*if (!is_object($xml)) {
		            	echo "<br>Dove:".var_dump($dove);
		            	echo "<<<-$xml-->>";
		            }*/
		            //echo var_dump($xml);
		            //print_r($xml);
		            foreach ($xml->data as $value) {
		            	  //echo "<br>Value ".$value['var']." = ".$value;
		            	  $key = (string) $value['var'];
		            	  if (isset($this->numerici[$key]) && $this->numerici[$key]==True) {
		           	      	$dati[$key]=str_replace(",",".", (string)$value);
		            	  } else {
		            	  	$dati[$key]= (string) $value;
		            	  }
		            }
		            // Carico eventuali DS
		            foreach ($xml->ds as $data) {
		            		foreach ($data->data as $value) {
			            		$dskey = (string) $data['var'];
		            			$key = (string) $value['var'];
		            			if (isset($this->numerici[$key]) && $this->numerici[$key]==True) {
			            			$dati[$dskey][$key]=str_replace(",",".", (string)$value);
		            			} else {
		            				$dati[$dskey][$key]=(string)$value;
		            			}
		            		}
		            }
		            // Cerco se esiste un'altra DS innestata dentro la DS
		            //$intds = strpos($this->OutputXML, "<ds>",$dove);
		            /*if ($intds<$fineds) {
		            	// C'è una DS Dentro la DS
		            	$fineds = strpos($this->OutputXML, "</ds>",$fineds+1);
		            }*/	
					/*while ($dove!==False) {
			    		$dove = strpos($this->OutputXML, "var='", $dove+2);
			    		if ($dove>=$fineds) {
			    			break;
			    		}
			    		if ($dove!==False && $dove<$fineds) {
				    		$finevar = strpos($this->OutputXML, "'", $dove+5);
				    		$finevalue = strpos($this->OutputXML, "</", $finevar+1);
				    		$startvalue = strpos($this->OutputXML, ">", $dove+5);
				    		$dati[substr($this->OutputXML, $dove+5, $finevar-($dove+5) )]=
				    		str_replace(",",".", substr($this->OutputXML, $startvalue+1 , $finevalue-($startvalue+1)));
			    		}
			    	}*/
		    	$start = $fineds;
		    	$returnDati[] = $dati;
			}
			if ($numds==1) {
	    		return $returnDati[0];   
			} else {
			    return $returnDati;
			}         		
		} else { 
			if (!isset($this->dim[$var])) {
		    	$dove = strpos($this->OutputXML, "var='$var'");
		    	if ($dove!==False) {
		    		//$dove = strpos($this->OutputXML, ">", $dove);
		    		//$fine = strpos($this->OutputXML, "<", $dove+1);
		    		$pos = $this->getStartEnd($dove);
		    		//die("sono qui!!");
		    		//echo "<br>DOVE:".$dove;
		    		//print_r($pos);
		    		//die();
		    		//die();
		    		$valore = $this->format_valore(substr($this->OutputXML, $pos['START'], ($pos['END']+1)-$pos['START']), $var);
		    		//echo "VALORE:".$valore;
		    		//$valore = $this->format_valore(substr($this->OutputXML, $dove+1, $fine-($dove+1)), $var);
		    		return $valore;
		    	}else {
		    		//developer_debug();
		    	}
			} else {
				$start = 1;
				for ($i=0;$i<$this->dim[$var];$i++) {
					$dove = strpos($this->OutputXML, "var='$var'", $start);
					if ($dove!==False) {
						//$dove = strpos($this->OutputXML, ">", $dove);
						//$fine = strpos($this->OutputXML, "<", $dove+1);
						$pos = $this->getStartEnd($dove);
						$valore = $this->format_valore(substr($this->OutputXML, $pos['START'], ($pos['END']+1)-$pos['START']), $var);
						//$valore = $this->format_valore(substr($this->OutputXML, $dove+1, $fine-($dove+1)), $var);
						$valori[] = $valore;
						//$start = $fine;
						$start = $pos['START'];
					}else {
						break;
					}
				}
				if(!isset($valori)) {
					developer_debug($mess);
				}
				return $valori;
			}
		}
    	/*if (isset($this->dsCount[$var]) && $this->dsCount[$var]!=0) {
    		return $this->parmout[$var][$occurence-1];		
    	} else {
    		return $this->parmout[$var];
    	}*/
	}
	function getStartEnd($dove) {
		global $settings;
		$pos = array();
		if (isset($settings['xmlservice_cdata']) && $settings['xmlservice_cdata']==True) {
			$dove = strpos($this->OutputXML, "![CDATA[", $dove);
			$fine = strpos($this->OutputXML, "]]", $dove+1);
			$pos['START']=$dove+8;
			$pos['END']=$fine-1;
		} else {
			$dove = strpos($this->OutputXML, ">", $dove);
			$fine = strpos($this->OutputXML, "<", $dove+1);
			$pos['START']=$dove+1;
			$pos['END']=$fine-1;
		}
		return $pos;
	}
	private function findEndDs($start, $parseXML) {
		$fineds = strpos($parseXML, "</ds>",$start);
		$cerca = True;
		$intds= $start;
		// Cerco se esiste un'altra DS innestata dentro la DS
		while ($cerca) {
			$intds = strpos($parseXML, "<ds",$intds+1);
			if ($intds!==False && $intds<$fineds) {
				// C'è una DS Dentro la DS
				$fineds = strpos($parseXML, "</ds>",$fineds+1);
				$start = $start+2;
				continue;
			}
			break;
		}
		return $fineds;
	}
	/**
	 * Recupero valore dai parametri di output di una DS
	 *
	 * @param string $ds	: Nome del parametro DS
	 * @param string $var	: Nome della variabile
	 * 
	 * @return string	Contenuto del campo all'interno della DS
	 */
    function getDSParm($ds, $var, $occurence=1) {
    	$dove = strpos($this->OutputXML, "var='$var'");
    	if ($dove!==False) {
    		/*$dove = strpos($this->OutputXML, ">", $dove);
    		$fine = strpos($this->OutputXML, "<", $dove+1);
    		$valore = substr($this->OutputXML, $dove+1, $fine-($dove+1));*/
    		$pos = $this->getStartEnd($dove);
    		$valore = $this->format_valore(substr($this->OutputXML, $pos['START'], ($pos['END']+1)-$pos['START']), $var);
    		return $this->format_valore($valore, $var);
    	}
    	/*if ($this->dsCount[$ds]!=0) {
    		return $this->parmout[$ds][$occurence-1][$var];
    	} else {
    		return $this->parmout[$ds][$var];    		
    	}*/	
	}
	function format_valore($valore, $var) {
		
		if (isset($this->numerici[$var])) {
			//echo "<br>$var Numerico ".$valore;
			return str_replace(",",".",$valore);
		} else {
			//echo "<br>$var Alfanumerico ". $valore;
			return $valore;
		}
	}
	/**
	 * Recupero valore dai parametri di output di una DS
	 *
	 * @param string $ds	: Nome del parametro DS
	 * @param string $var	: Nome della variabile
	 * 
	 * @return string	Contenuto del campo all'interno della DS
	 */
    function getOutDSParm($ds, $var) {
		return $this->parminp[$ds][$var];
	}
	
	/**
	 * Recupero valore array di una DS
	 *
	 * @param string $ds	: Nome del parametro DS
	 * 
	 * @return array	Contenuto del campo all'interno della DS
	 */
	
    function getOutDS($ds) {
		return $this->parminp[$ds];
	}
	
	/**
	 * Inizializzazione DS di input	
	 * 
	 * @param string $ds	: Nome del parametro DS
	 */	
    function clearDS($ds) {
		
		return $this->parminp[$ds];
	}		
		
	/**
	* Recupero l'ultimo errore
	* 
	*/
	function getLastErr() {
		return $this->LastErr;
	}
	
	/**
	* Resetto l'environment della classe. Necessario per chiamate ricorsive. Da capire ????
	* 
	*/
	function reset() {
		
		unset($this->inputXML);
	}
	
	/**
	* Distruttore della classe
	* 
	*/	
	function __destruct() {
		// Nothing to do ... 
	}
	
	
	/**
	 * Costruisce l'array dei parametri di input copiandoli dal descrittore passato come parametro
	 *
	 * @param string $description	: Descrittore prototipo parametri
	 * 
	 * @return array	Array di input
	 */		
	function input_param($description) {
		
        if (isset($this->parmbbb)) {
        	return $this->parmbbb;
        }
		$parmbbb = array();
		if(!empty($description)) {
			foreach($description as $key) {
				if(isset($key['Name'])) {
					if ($key['Type']=="0") 			
						$parmbbb[$key['Name']]="";
					else 
						$parmbbb[$key['Name']]="0";
					// Controllo se c'è un DIM
					if (isset($key['Count'])) {
						$this->dim[$key['Name']]=$key['Count'];
					}
				}
				else {
				 	$dsparm = array();
				 	$this->dsCount[$key['DSName']]= 0;
				 	foreach($key['DSParm'] as $key1) {
				 		if (isset($key1['Name'])) {
							if ($key1['Type']=="0") 
								$dsparm[$key1['Name']]="";
							else 
								$dsparm[$key1['Name']]='0';
				 		} elseif  (isset($key1['DSName'])) {
				 			// C'è un'altra DS ... da gestire
				 			foreach($key1['DSParm'] as $key2) {
				 				if (isset($key2['Name'])) {
				 					if ($key2['Type']=="0")
				 						$dsparm[$key1['DSName']][$key2['Name']]="";
				 					else
				 						$dsparm[$key1['DSName']][$key2['Name']]='0';
				 				} 
				 			}
				 		} 
					}
					if (isset($key['Count']) && $key['Count']>1) {
						        $newArray = array();
				 				$this->dsCount[$key['DSName']]= $key['Count'];					        
								for ($i=0; $i<$key['Count']; $i++) {
									$newArray[$i]=$dsparm;
								}	
								$dsparm = $newArray;
					}
					$parmbbb[$key['DSName']]=$dsparm;
				 }
			}  
		}
		$this->parmbbb=$parmbbb;
		return $parmbbb;
	}
	function getType($value, $onlyType=False) {
		$type = "type='";
		if ($onlyType==True) {
			$type="";
		}
		if ($value['Type']==I5_TYPE_CHAR) {
			$type .= $value['Length']."A";
		} else if ($value['Type']==I5_TYPE_PACKED){
			$type .= str_replace(".","p",$value['Length']);
			$this->numerici[$value['Name']]=True;
		}  else if ($value['Type']==I5_TYPE_ZONED){
			$type .= str_replace(".","s",$value['Length']);
			$this->numerici[$value['Name']]=True;
		}  else if ($value['Type']==I5_TYPE_INT){
			$type .= str_replace(".","i",$value['Length']);
		}  else if ($value['Type']==I5_TYPE_BYTE){
			$type .= $value['Length']."B";
		}  else {
			$pos = strpos($value['Length'],".");
			if ($pos!==False) {
				$type .= str_replace(".","s",$value['Length']);
			} else {
				$type .= $value['Length']."A";
			}
		}
		if ($onlyType==False) $type .="'";
		return $type;		
	}
	function getValueString($value, $valore=null, $count = 0) {
        
		// @todo considerare tutti i tipi di campi per il caricamento
		/*$type = "type='";
		if ($value['Type']==I5_TYPE_CHAR) {
            $type .= $value['Length']."A";
         } else if ($value['Type']==I5_TYPE_PACKED){
            $type .= str_replace(".","p",$value['Length']);
            $this->numerici[$value['Name']]=True;
         }  else if ($value['Type']==I5_TYPE_ZONED){
            $type .= str_replace(".","s",$value['Length']);
            $this->numerici[$value['Name']]=True;
         }  else if ($value['Type']==I5_TYPE_INT){
            $type .= str_replace(".","i",$value['Length']);            
         }  else if ($value['Type']==I5_TYPE_BYTE){
            $type .= $value['Length']."B"; 
         }  else {
         	$pos = strpos($value['Length'],".");
         	if ($pos!==False) {
         		$type .= str_replace(".","s",$value['Length']);
         	} else {
         		$type .= $value['Length']."A";
         	}
         }
        $type .="'"; */
        $type = $this->getType($value);
        $inizializzo = '';
        $dim = '';
        if ($valore!=null) {
          $inizializzo = $valore;
        }
        if ($count >1) {
        	$dim = "dim='$count'";
        }
        // Potrebbe esserci un setlen o un len ma non entrambi
        $mylen = "";
        if (isset($value['Len'])) {
        	$mylen = " len='".$value['Len']."'";
        	$type = "";
        } 
        if (isset($value['SetLen'])) {
        	$mylen = " setlen='".$value['SetLen']."'";
        }
        if (isset($value['DATA_TYPE']) && $value['DATA_TYPE']=='TIME') {
        	$inizializzo = str_replace(":",".",$inizializzo);
        }
        return "<data $type var='".$value['Name']."'"." ".$dim.$mylen.">".$inizializzo."</data>";
	}
	function getInputXML($inputDsParam=False) {
	    global $settings;
		/*if (isset($this->inputXML)) {
			return $this->inputXML;
		}*/
		$mode ="";
		if ($settings['OS400']=="V5R4M0") {
			$mode ="mode='opm'";
		}
		$InputXML   = "";
		$InputXML   = "<?xml version='1.0'?><script>";
		$InputXML   .= "<pgm name='".$this->RPGProgram."' ".$mode.">";
		// Passaggio da Array WI400 ad Array XMLSERVICE
		if(!empty($this->desc)) {
			foreach ($this->desc as $key=>$value) {
			    // Controllo se si tratta di una DS
			    if (!isset($value['Name'])) {
			       $InputXML .= $this->getValueDS($value, $inputDsParam, False);		
			    } else {
			    	$InputXML .=$this->getValueParm($value);
			    }
			}
		}
		$InputXML .="</pgm></script>";	
		$this->inputXML = $InputXML;
		return $InputXML;	
	}
	function getValueDS($value, $inputDsParam, $subDs=False) {
		$InputXML="";
		if ($subDs==False) {
			$InputXML = "<parm io='BOTH'>";
	    }
		$count = "";
		if (isset($value['Count']) && $value['Count']>1 && !$inputDsParam) {
			$count = " dim='".$value['Count']."' ";
		}
		$setlen = "";
		if (isset($value['Len'])) {
			$setlen = " len='".$value['Len']."'";
		}
		$InputXML .= "<ds var='".$value['DSName']."' $count $setlen>";
		if (isset($this->dsCount[$value['DSName']]) && $this->dsCount[$value['DSName']]!=0) {
			for ($jj=0;$jj<$this->dsCount[$value['DSName']];$jj++) {
				foreach ($value['DSParm'] as $key1=>$value1) {
					//$valore = null;
					//if (isset($this->parminp[$value['DSName']][$jj][$value1['Name']])) {
					//	$valore = $this->parminp[$value['DSName']][$jj][$value1['Name']];
					//}
					//$InputXML .= $this->getValueString($value1, $valore);
					if (!isset($value1['Name'])) {
						$InputXML .= $this->getValueDS($value1, $inputDsParam, True);
					} else {
						$valore = null;
						if (isset($this->parminp[$value['DSName']][$jj][$value1['Name']])) {
							$valore = $this->parminp[$value['DSName']][$jj][$value1['Name']];
						}
						$InputXML .= $this->getValueString($value1, $valore);
					}
				}
				if (!$inputDsParam) {
					break;
				}
				$InputXML .= "</ds><ds var='".$value['DSName']."' $count>";
			}
		} else {
			foreach ($value['DSParm'] as $key1=>$value1) {
				//$valore = null;
				//if (isset($this->parminp[$value['DSName']][$value1['Name']])) {
				//	$valore = $this->parminp[$value['DSName']][$value1['Name']];
				//}
				if (!isset($value1['Name'])) {
					$InputXML .= $this->getValueDS($value1, $inputDsParam, True);
				} else {
					$valore = null;
					if (isset($this->parminp[$value['DSName']][$value1['Name']])) {
						$valore = $this->parminp[$value['DSName']][$value1['Name']];
					}
					$InputXML .= $this->getValueString($value1, $valore);
				}	
				
			}
		}
		$InputXML .= '</ds>';
		if ($subDs==False) {
			$InputXML.="</parm>";
		}
		// Attacco gli eventuali Overlay sulla DS appena scritta sull'XML, non so bene cosa mi ritornerà
		if (isset($this->overlay[$value['DSName']])) {
			foreach ($this->overlay[$value['DSName']] as $chiave =>$ov) {
				$offset = $ov['offset']-1;
				$InputXML .= "<overlay io='both' offset='$offset' ><data var='{$ov['name']}' type='{$ov['type']}'>{$ov['value']}</data></overlay>";
			}
		}
		
		return $InputXML;	
	}
	function getValueParm($value) {
		// Controllo se si tratta di una DS
		switch ($value['IO']) {
			case I5_IN:
				$io='in';
				break;
			case I5_OUT:
				$io='out';
				break;
			default:
				$io='both';
				break;
		}
		$InputXML = "<parm io='$io'>";
		$valore = null;
		$dim = 0;
		if (isset($value['Count']) && $value['Count']>1) {
			$dim = $value['Count'];
		}
		if (isset($this->parminp[$value['Name']])) {
			$valore = $this->parminp[$value['Name']];
		}
		$InputXML .= $this->getValueString($value, $this->parminp[$value['Name']], $dim);
		$InputXML.="</parm>";
		// Attacco gli eventuali Overlay sulla DS appena scritta sull'XML, non so bene cosa mi ritornerà
		if (isset($this->overlay[$value['Name']])) {
			foreach ($this->overlay[$value['Name']] as $chiave =>$ov) {
				$offset = $ov['offset']-1;
				$InputXML .= "<overlay io='both' offset='$offset' ><data var='{$ov['name']}' type='{$ov['type']}'>{$ov['value']}</data></overlay>";
			}
		}
		return $InputXML;
	}
	function errorHandler($errno, $errstr, $errfile, $errline) {   
	        $pos = strpos($errstr,"]:") ;   
	        if ($pos) {   
	            $errstr = substr($errstr,$pos+ 2);   
	        }   
	        $this->error = $errstr;
}	
/*   function internalParser($OutputXML) {
   	  $dom = new DomDocument('1.0');
      $this->error="";
      set_error_handler(array($this,"errorHandler"));    
      $dom->loadXML($OutputXML);
      restore_error_handler();
      $this->parmout = array();
      //echo "errore:".$this->error. " output XML:".$OutputXML;
      // @todo controllo errore di pasing XML
		$params = $dom->getElementsByTagName('pgm');
		$pgm=$params->item(0)->getAttribute('name');
		// @todo Controllo messaggio errore richiamo programma
		//echo "<br>Programma:".$pgm;
		// Recupero i parametri
		$params2 = $params->item(0)->getElementsByTagName('parm');
			  $i=0;
		      foreach ($params2 as $p) {
	      	    $ds = $p->getElementsByTagName('ds')->item(0);
	      		if (isset($ds)) {
      				  $j=0;
      				  $nomeds = $ds->getAttribute('var');
      				  $params3 = $ds->getElementsByTagName('data');
				      foreach ($params3 as $p) {
				          	if (isset($this->dsCount[$nomeds]) && $this->dsCount[$nomeds]!=0) {
					    		$this->parmout[$nomeds][0][$params3->item($j)->getAttribute('var')] = $params3->item($j)->nodeValue; 
				        	} else {
					    		$this->parmout[$nomeds][$params3->item($j)->getAttribute('var')] = $params3->item($j)->nodeValue; 
					    	} 
				      		$j++;
				      }		      			
	      		} else {
		      		$params3 = $p->getElementsByTagName('data');
		      		$this->parmout[$params3->item(0)->getAttribute('var')] = $params3->item(0)->nodeValue; 
	      		}		      		
	      		$i++;
		      }
   } */
}

?>
