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
	ob_start();
	/*echo "<pre>";
	print_r($_SERVER);
	print_r($_GET);*/
	
	// Verifico se devo stampare i messaggi
	$em = False;
	if (isset($_GET['debug'])) {
		$em= True;
	}
	// Inizio
    if ($em) echo "Inizio elaborazione rest \r\n";
	$parametri = array();
	$parametri = array_merge($parametri, $_GET);
// @fix problema con headers : Cannot send session cache limiter - headers already sent 	
//	echo "PARAMETRI: <pre>"; print_r($parametri); echo "</pre>";
	if (isset($parametri['appBase'])) {
		$appBase = $parametri['appBase'];
	}
	// Cerco app Base da url
	if ($appBase=="") {
		require_once "appBase.php";
	}
	if ($appBase =="") {
		$appBase = '/WI400/';
	}
	if ($em) echo "appBase: $appBase \r\n";
	// Configurazione
	if (isset($parametri['user'])) {
		$_SESSION['user']=$parametri['user'];
	}
	//$_SESSION['lista_librerie']= explode(";", $parametri['lista_librerie']);
	require_once "base/includes/config.php";
	if (!$em) {
		ini_set("display_errors", 0);
	} else {
		$settings['debug']=True;
		getMicroTimeStep("<br>Inizio Rest");
	}
	$otmCheck = False;
	// Caricamento classi
	$xmlservice = False;
	if (isset($settings['xmlservice'])) {
		unset($settings['xmlservice']);
		$xmlservice = True;
	}
	require_once $base_path."/includes/loader.php";
	// Pre Controllo OTM checksum prima della connessione al DB
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
	}
	// LZ: Non serve require_once p13n("/base/includes/init.php");
	$string = 'architettura_'.strtolower($settings['architettura']);
	$architettura = new $string();
	$alternativa = array();
	$db = new $settings['database'] ();
	$db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name'], $settings['db_conn_type'], false, $settings['db_log']);
	$db->add_to_librarylist($alternativa);
	$db->connect(True);
	$messageContext = new wi400Messages();
	$actionContext  = new wi400ActionContext();
	// Se non ho già verificato l'TOM
	if (isset($_GET['OTM'])) {
		$otm = new wi400Otm($_GET['OTM']);
		$otm->getOtm();
		if ($otm->isError()) {
			ob_end_clean();
			die("OTM non valida!");
		}
		$otmparm = $otm->parseParameter();
		$parametri = array_merge($parametri, $otmparm);
		$parametri['user']=$otm->getUser();
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
	// Gestione pooling
	if (isset($parametri['pooling'])) {
		// Verifico se presente già un lavoro in attesa altrimenti verrà creato
		$dir = $data_path."/pooling/".$parametri['pooling'];
		wi400_mkdir($dir, 777, true);
		$DTAQKey = ftok($dir, "P");
		$privateId = getPrivatePoolingId($DTAQKey);
		$INTERNALKEY=$settings['sess_path'].$privateId;
	}
	// @fix problema con headers : Cannot send session cache limiter - headers already sent
	if ($em) {
		echo "PARAMETRI: <pre>"; print_r($parametri); echo "</pre>";
	}
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
	// Parametri arrivati da chiamata Rest - OVERRIDE OTM
	if (isset($_GET['reqrest'])) {
		$restParm = explode("/",$_GET['reqrest']);
		if (isset($restParm[0]) && $restParm[0]!="DEFAULT") {
			$parametri['action']=$restParm[0];
		}
		if (isset($restParm[1]) && $restParm[1]!="DEFAULT") {
			$parametri['inout']=$restParm[1];
		}
		for ($i=2;$i<count($restParm);$i++) {
			$parametri[$restParm[$i]]=$restParm[$i+1];
			$i++;
		}
	}
	$_SESSION['user']=$parametri['user'];
	// Caricamento XMLSERVICE
	if ($xmlservice==True) {
		$settings['xmlservice']=True;
	}
	if (isset($settings['xmlservice'])) {
		require_once $routine_path.'/classi/wi400RoutineXML.cls.php';
		require_once $routine_path."/generali/xmlsupport.php";
	}
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
	if ($em) echo "Language: ".$language."\r\n";
	// Gestione locks
	require_once $base_path."/includes/locks.php";
    // Impostazione per batch, esecuizione per massimo 1 ora e max limite memoria
	ini_set('max_execution_time', 3600);
	ini_set("memory_limit","500M");	
	ignore_user_abort(TRUE); 	
	ini_set("display_errors", 0);

	global $batchContext;
	$batchContext = new wi400ValuesContainer();
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
	// BATCH_ACTION
	if ($em) echo "Azione da elaborare:".$batchContext->action."\r\n";
	
	$actionRow = rtvAzione($batchContext->action);
	if (isset($actionRow['LOG_AZIONE']) && $actionRow['LOG_AZIONE']=="N") {
		//
	} else {
		logAction($message, $_SESSION['user'], $_SERVER['REMOTE_ADDR']);
		if (isset($settings['log_user_action']) && $settings['log_user_action']==True) {
			logUserAction($actionRow);
		}
	}
	$stato = '9'; 
	if (!$actionRow) {
		if ($em) echo "Azione inesistente!";
		$stato = 'E';
	} else {
		$actionContext->setForm($batchContext->form);
		
		$actionContext->setType($actionRow["TIPO"]);
		$actionContext->setModule($actionRow["MODULO"]);
		$actionContext->setModel($actionRow["MODEL"]);
		
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
	}
	// Controllo se l'azione mi ha dato degli errori o eventuali errori
	if ($messageContext->getSeverity()=="ERROR") {
		$stato = 'E';
		// Stampa gli errori in modo che vengano visualizzati sul log
		if ($em) echo var_dump($messageContext->getMessages());
	}

	// Aggiornamento esecuzione batch
	if (!isset($parametri['nodb']) && isset($parametri['id'])) {	
		$sql = "UPDATE FBATCHJB SET TIMECOMPLETE='".$db->getTimestamp()."' ,STATO='$stato' WHERE ID='".$parametri['id']."'";
	    $db->query($sql);
	}  
	// Disconnect XML Service
	if (isset($settings['xmlservice']) && !isset($parametri['private']) && !isset($parametri['pooling'])) {
		xmlservice_logout();
	}
	// Metto in coda il lavoro per il successivo pooling
	if (isset($parametri['pooling'])) {
		$seg = msg_get_queue($DTAQKey) ;
		$daten=$privateId."/".time();
		msg_send ($seg, 1, $daten, true, true, $msg_err);
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
	if ($em) {
		getMicroTimeStep("<br>Fine Rest");
	}
	
	if ($em) echo "\r\nElaborazione terminata con stato: ".$stato."\r\n";
?>