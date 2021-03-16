<?php
	
	global $appBase,$root_path,$wi400Debug,$settings,$time_start,$time_step,$temaDir, $architettura;
	global $viewContext,$gatewayContext,$menuContext,$messageContext,$actionContext,$breadCrumbs,$listContext,$lookUpContext;
	global $pageDefaultDecoration,$showLoginForm,$show_footer,$show_header;
	global $data_path,$actionLabel,$buttonsBar,$tab_index,$log_path;
	global $dbUser, $dbPath, $base_path, $CONTROLKEY, $INTERNALKEY;
	global $history, $wi400Batch, $WI400_PRAGMA, $wi400_trigger;

	ini_set("display_errors", 0);
	ignore_user_abort(true);
	$wi400Batch = True;
	// Recupero percorso base applicazione
	//require_once "appBase.php";
    echo "Inizio elaborazione batch \r\n";
	// CARICAMENTO XML
	// Ricavo l'ID del record che devo elaborare
	if (isset($_POST['MYXML'])) {
	    $post = $_POST['MYXML'];
		// Recupero i parametri arrivati con XML
		$header = array();
		$dom = new DomDocument('1.0');
		$dom->loadXML($post);
		$params = $dom->getElementsByTagName('parametro');
		$i=0;
		foreach ($params as $param){
			if ($params->item($i)->getAttribute('encode')=="base64") {
				$header[$params->item($i)->getAttribute('id')]=base64_decode($params->item($i)->getAttribute('value'));
			} else {
				$header[$params->item($i)->getAttribute('id')]=$params->item($i)->getAttribute('value');
			}
			//$header[$params->item($i)->getAttribute('id')]=$params->item($i)->getAttribute('value');
			$i++;
		}
		// Carico il file XML con i parametri per l'elaborazione BATCH
		$file = $header['ID'];
		$handle = fopen($file, "r");
		$xml = fread($handle, filesize($file));
		fclose($handle);
		echo "Get MYXML \r\n";		
	} elseif (isset($_POST['POSTXML'])) {
		echo "Get POSTXML \r\n";
		$xml = utf8_encode($_POST['POSTXML']);
	}
	echo "XML: ".$xml." \r\n";
    
	$parametri = array();
	/*$dom = new DomDocument('1.0');
	$dom->loadXML($xml);
	$params = $dom->getElementsByTagName('parametro');
	$i=0;
	foreach ($params as $param){
		if ($params->item($i)->getAttribute('encode')=="base64") {
			$parametri[$params->item($i)->getAttribute('id')]=base64_decode($params->item($i)->getAttribute('value'));
		} else {
			$parametri[$params->item($i)->getAttribute('id')]=$params->item($i)->getAttribute('value');
		}
		$i++;
	}*/ 
	// Con simple xml PIU PERFORMANTE E MENO MEMORIA
	/*$oggetto = simplexml_load_string($xml);
	foreach ($oggetto->parametro as $param){
		$id = (string) $param['id'];
		$value = (string) $param['value'];
		if (isset($param['encode'])) {
			$encode = (string) $param['encode'];
		} else {
			$encode = "";
		}
		if ($encode=="base64") {
			$parametri[$id]=base64_decode($value);
		} else {
			$parametri[$id]=$value;
		}
	} 
	unset($oggetto);*/
	$parametri = getArrayFromXML($xml);
	//unset($dom);
	if (isset($parametri['fileSave'])) {
		  $fileSave = $parametri['fileSave'];
		  $handle = fopen($fileSave, "w");
		  fwrite($handle, $xml);
		  fclose($handle);
		  
	}
// @fix problema con headers : Cannot send session cache limiter - headers already sent 	
//	echo "PARAMETRI: <pre>"; print_r($parametri); echo "</pre>";
	$appBase = $parametri['appBase'];
	// Cerco app Base da url
	if ($appBase=="") {
		require_once "appBase.php";
	}
	if ($appBase =="") {
		$appBase = '/WI400/';
	}
	echo "appBase: $appBase \r\n";
	// Configurazione
	$_SESSION['user']=$parametri['user'];
	//$_SESSION['lista_librerie']= explode(";", $parametri['lista_librerie']);
	require_once "base/includes/config.php";
	// Azioni Batch consentite solo da Localhost o IP autorizzati
	if (isset($settings['security_trust_ip']) && in_array(filter_input(INPUT_SERVER, 'REMOTE_ADDR'), $settings['security_trust_ip'])) {
		// Posso passare
	}	else {
		if(!isset($_SERVER['SHELL']) && $_SERVER['SERVER_ADDR']!=$_SERVER['REMOTE_ADDR']) {
			if (!isset($_GET['OTM'])) {
				ob_end_clean();
				die("ACCESSO NON CONSENTITO!");
			} else {
				require_once $routine_path.'/classi/wi400Otm.cls.php';
				$otm = new wi400Otm($_GET['OTM']);
				if (!$otm->verifyOtm($_GET['OTM'])) {
					ob_end_clean();
					die("OTM CHECK SUM ERRATO!");
				}
				$otmParm = $otm->parseParameterSerialize();
				$parametri = array_merge($parametri, $otmParm);
			}
		}
	}	
	$phpJobName="PHPBATCH";
	$settings['jobname']="PHPBATCH";
	if (isset($parametri['jobname'])) {
		$newname = substr($parametri['jobname'],0,10);
		$phpJobName=$newname;
		$settings['jobname']=$newname;		
	}	
	// Inizializzazione
	$settings['i5_conn_type'] = 'T'; 
    $settings['db_conn_type'] = 'T';
    /*if (isset($_GET['OTM'])) {
    	$parametri['private']=="OTMMASTER_01";
    	$settings['jobname']="OTMMASTER";
    }*/
    // SE ESISTE UTILIZZO LA CODA LAVORA PER I BATCH
    if (isset($settings['xmlservice_jobd_lib_batch']) && isset($settings['xmlservice_jobd_batch']) && $settings['xmlservice_jobd_batch']!="") {
    	$settings['xmlservice_jobd_lib'] = $settings['xmlservice_jobd_lib_batch'];
    	$settings['xmlservice_jobd'] = $settings['xmlservice_jobd_batch'];
    }
    // CODA LAVORI E LIBRERIA DA UTILIZZARE
    if (isset($parametri['xmlservice_jobd_lib']) && isset($parametri['xmlservice_jobd'])) {
    	$settings['xmlservice_jobd_lib'] = $parametri['xmlservice_jobd_lib'];
    	$settings['xmlservice_jobd'] = $parametri['xmlservice_jobd'];
    }
	// Caricamento classi
	require_once $base_path."/includes/loader.php";
	// @fix problema con headers : Cannot send session cache limiter - headers already sent
	echo "PARAMETRI: <pre>"; print_r($parametri); echo "</pre>";
	// ID della connessione private
	if (isset($parametri['private'])) {
		$INTERNALKEY=$settings['sess_path'].$parametri['private'];
	}
	// TIMEOUT 
	if (isset($parametri['timeout'])) {
		$timeout = $parametri['timeout'];
		if ($timeout !="" && $timeout!=0) {
			$CONTROLKEY = preg_replace('/\*idle\([0-9]+\)/', '*idle('.$timeout.')', $CONTROLKEY);
		}
	}
	$_SESSION['data_validita'] = date("Ymd");
	// Il loader resetta la sessione e quindi reimposto l'utente
	if (isset($parametri['user'])) {
		$_SESSION['user']=$parametri['user'];
	} else {
		$_SESSION['user']="";
	}
	require_once p13n("/base/includes/init.php");
	if (isset($_GET['OTM'])) {
		$otm = new wi400Otm($_GET['OTM']);
		$otm->getOtm();
		if ($otm->isError()) {
			ob_end_clean();
			die("OTM non valida!");
		}
		$otmparm = $otm->parseParameter();
		$parametri = array_merge($parametri, $otmparm);
	}
	$_SESSION['user']=$parametri['user'];
	// Caricamento sistema informativo
	$foundSys=false;
	// 1: Passate le librerie
	if (isset($parametri['lista_librerie'])  && $parametri['lista_librerie']!="") {
		// FIX per passaggio libreria con piripacchio § che non viene correttamente convertito 
		$_SESSION['lista_librerie']= explode(";", str_replace("@", "§" ,$parametri['lista_librerie']));
		foreach ($_SESSION['lista_librerie'] as $bkey => $bvalue) {
			if (strpos($bvalue, "§")!==False) {
				unset($_SESSION['lista_librerie'][$bkey]);
			}
		}	
		//$_SESSION['lista_librerie']= explode(";", $parametri['lista_librerie']);
		$foundSys=True;
	}	    
	// 2: Passato il sistema informativo
	if (isset($parametri['sistema_informativo']) && $parametri['sistema_informativo']!="") {
		$_SESSION['lista_librerie']= retrive_sysinf_by_name($parametri['sistema_informativo']);
		$_SESSION['sysinfname']= $parametri['sistema_informativo'];
		$foundSys=True;
	}	    
	// 3: Lo carico dal profilo utente
	if ($foundSys==False) {
		// Verifico se l'utente passato ha un utente di gruppo
		$usertouse = $parametri['user'];
		$user = rtvUserInfo($parametri['user']);
		if ($user['USER_GROUP']!="") {
			$usertouse=$user['USER_GROUP'];
		}
		$_SESSION['lista_librerie']= retrive_sysinf($usertouse);
		$foundSys=True;		
	}
	if (!isset($settings['xmlservice']) && isset($mycon)) {
		$mycon->add_to_librarylist($_SESSION["lista_librerie"] , $connzend);	
	}
	$db->add_to_librarylist($_SESSION["lista_librerie"], True);	
	// Controllo se reperire il DB-ENVIRONMENT
	if (isset($settings['xmlservice']) || isset($mycon)) {
		if (!isset($_SESSION['DB-ENVIRONMENT'])) {
			$_SESSION['DB-ENVIRONMENT']="";
			//$ret = @data_area_read("*LIBL/DB_ENV");
			$ret = data_area_read("*LIBL/DB_ENV");
			if ($ret) {
				if (strtoupper($ret)=="TEST") {
					$_SESSION['DB-ENVIRONMENT']="TEST";
				}
			}
		} else  {
			if($_SESSION['DB-ENVIRONMENT']=='TEST') {
				$settings['uploadPath'] = "/upload_test/";
			}
		}
	}	
	// Caricamento lingue
	$infoUtente = rtvUserInfo($parametri['user']);
	// L'utente su WI400 potrebbe non esserci ...
	if (isset($infoUtente['LANGUAGE'])) {
		$_SESSION['USER_LANGUAGE']= $infoUtente['LANGUAGE'];
	} else {
		$_SESSION['USER_LANGUAGE']= "";
	}
	if (isset($parametri['custom_language'])) {
		$_SESSION['CUSTOM_LANGUAGE']=getLanguageID($parametri['custom_language'], False);
	}
	require_once $base_path."/includes/language.php";
	echo "Language: ".$language."\r\n";
	// Gestione locks
	require_once $base_path."/includes/locks.php";
    // Impostazione per batch, esecuizione per massimo 1 ora e max limite memoria
	ini_set('max_execution_time', 3600);
	ini_set("memory_limit","500M");	
	ignore_user_abort(TRUE); 	
	ini_set("display_errors", 0);

	global $batchContext;
	$batchContext = new wi400ValuesContainer();
	// Attacco i parametri presenti sul GET
	/*if (isset($_GET)) {
		foreach ($_GET as $key => $value){
			$batchContext->__set($key, $value);
		}
	}*/
	// Attacco i parametri
	foreach ($parametri as $key => $value){
		$batchContext->__set($key, $value);
	}
//	echo "BATCHCONTEXT:<pre>"; print_r($batchContext); echo "</pre>";
	
	// Aggiornamento start dell'azione Batch
	if (!isset($parametri['nodb']) && isset($parametri['id'])) {
		if (!isset($header['ASJOB'])) $header['ASJOB']="";
		if (!isset($header['ASJOBN'])) $header['ASJOBN']="";
		if (!isset($header['ASUSER'])) $header['ASUSER']="";
		$sql = "UPDATE FBATCHJB SET TIMESTART='".$db->getTimestamp()."' ,STATO='2', 
	    ASUSER='".$header['ASUSER']."', ASJOB='".$header['ASJOB']."',ASJOBN='".$header['ASJOBN']."' 
		WHERE ID='".$parametri['id']."'";
	    $db->query($sql);
	}
	// Scrittura nel log del lavoro l'azione
	/*$pgm = new wi400Routine("ZDIAGMSG", $connzend);
	$pgm->load_description();
	$pgm->prepare();*/
	
	$message = "Azione:".$batchContext->action;
	
	$identificativo = "undefined";
	
	if (isset($parametri['id'])) 
		$identificativo = $parametri['id'];
	
	if (isset($_SESSION['user'])) 
		$message .= " USER:".$_SESSION['user'];
	
	$message .= " ".$appBase. "-".$identificativo;
	
	/*$pgm->set('MESSAGE',$message);
	$pgm->call();*/
	
	// BATCH_ACTION
	echo "Azione da elaborare:".$batchContext->action."\r\n";
	
	$actionRow = rtvAzione($batchContext->action);
	if (isset($actionRow['LOG_AZIONE']) && $actionRow['LOG_AZIONE']=="N") {
		//
	} else {
		logAction($message, $_SESSION['user'], $_SERVER['REMOTE_ADDR']);
		if (isset($settings['log_user_action']) && $settings['log_user_action']==True) {
			logUserAction($actionRow);
		}
	}
	if (!$actionRow) {
		echo "Azione inesistente!";
		die();
	}
	$actionContext->setForm($batchContext->form);
	
	$actionContext->setType($actionRow["TIPO"]);
	$actionContext->setModule($actionRow["MODULO"]);
	$actionContext->setModel($actionRow["MODEL"]);
	
//	echo "ACTION: ".$batchContext->action."<br>";
//	echo "FORM: ".$batchContext->form."<br>";
//	echo "GATEWAY: ".$batchContext->gateway."<br>";
	
//	if(isset($batchContext->gateway) && !empty($batchContext->gateway)) {
//	if($batchContext->gateway!="") {
	if(isset($parametri["gateway"]) && !empty($parametri["gateway"])) {
		$actionContext->setGateway($batchContext->gateway);
//		echo "ACTIONCONTEXT GATEWAY: ".$actionContext->getGateway()."<br>";
		
		$gateway_path = p13n($actionContext->getGatewayUrl($actionRow["GATEWAY"]));
//		echo "GATEWAY PATH: $gateway_path<br>";
	
		require_once p13n($actionContext->getGatewayUrl($actionRow["GATEWAY"]));
	}

	if ($actionRow["TIPO"] == "B" && $actionRow["MODEL"] && $actionRow["MODEL"] != ""){
		require_once p13n($actionContext->getModelUrl($actionRow["MODEL"]));
	} else {
		$messageContext->addMessage("ERROR", "Azione non di tipo batch");
	}
	
	$stato = '9'; 
	// Controllo se l'azione mi ha dato degli errori o eventuali errori
	if ($messageContext->getSeverity()=="ERROR") {
		$stato = 'E';
		// Stampa gli errori in modo che vengano visualizzati sul log
		echo var_dump($messageContext->getMessages());
	}

	// Aggiornamento esecuzione batch
	if (!isset($parametri['nodb']) && isset($parametri['id'])) {	
		$sql = "UPDATE FBATCHJB SET TIMECOMPLETE='".$db->getTimestamp()."' ,STATO='$stato' WHERE ID='".$parametri['id']."'";
	    $db->query($sql);
	}  
	// Disconnect XML Service
	if (isset($settings['xmlservice']) && !isset($parametri['private'])) {
		xmlservice_logout();
		/*$InputXML = '<?xml version="1.0"?>';
		$InternalKey = $INTERNALKEY;
		$ControlKey="*immed";
		$OutputXML = '';
		$callPGM = $db->getCallPGM();
		if ($settings['xmlservice_driver']=="DB") {
			$db->bind_param ($callPGM, 1, "InternalKey", DB2_PARAM_IN );
			$db->bind_param ($callPGM, 2, "ControlKey", DB2_PARAM_IN );
			$db->bind_param ($callPGM, 3, "InputXML", DB2_PARAM_IN );
			$db->bind_param ($callPGM, 4, "OutputXML", DB2_PARAM_OUT );
			$ret = db2_execute($callPGM);
		} else if($settings['xmlservice_driver']=="PDO") {
			$result = $callPGM->execute(array($InternalKey, $ControlKey, $InputXML));
		}*/
		/*$db->bind_param ($callPGM, 1, "InternalKey", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 2, "ControlKey", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 3, "InputXML", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 4, "OutputXML", DB2_PARAM_OUT );
		$ret = db2_execute($callPGM);*/
	}
	// Eliminazione di eventuali lock
	endLock("","",session_id());
	// Cancello tutte le tabelle temporanee create
	if (isset($db)) {
		$db->destroyTable(session_id());
		$db->clearPHPTEMP(session_id());
	}
	// Cancello i file wi400Session
	wi400Session::destroy();
	session_unset();
	session_destroy();
	
    echo "\r\nElaborazione terminata con stato: ".$stato."\r\n";
    function getArrayFromXML($XML) {
    	$parametri = array();
    	preg_match_all("'arametro id=\"(.*?)/>'si", $XML, $match);
    	foreach($match[1] as $val)
    	{
    		//echo "<br>VALORE".$val;
    		// Cerco la chiusura del parametro:
    		$pos = strpos($val, '"');
    		$parametro = substr($val, 0, $pos);
    		//echo "<br>:".$parametro;
    		$pos2 = strpos($val, "value=\"", $pos+1);
    		$finpos = strpos($val, '"', $pos2+8);
    		$valore = substr($val, $pos2+7, $finpos-($pos2+7));
    		//echo "<br>VALORE:".$valore;
    		// Cerco se per caso vi è l'encode
    		$pos3 = strpos($val, "encode=\"", $finpos+1);
    		if ($pos3===False) {
    			//echo "<br>NO ENCODE!";
    			$encode = "";
    			
    		} else {
    			$finpos = strpos($val, '"', $pos3+8);
    			$encode = substr($val, $pos3+8, $finpos-($pos3+8));
    			//echo "<br>ENCODE:".$encode;
    		}
    		if ($encode=="base64") {
    			$parametri[$parametro]=base64_decode($valore);
    		} else {
    			$parametri[$parametro]=$valore;
    		}
    		
    	}
    	return $parametri;
    }
?>