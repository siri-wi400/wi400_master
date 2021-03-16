<?php
	
	global $appBase,$root_path,$wi400Debug,$settings,$time_start,$time_step,$temaDir, $architettura;
	global $viewContext,$gatewayContext,$menuContext,$messageContext,$actionContext,$breadCrumbs,$listContext,$lookUpContext;
	global $pageDefaultDecoration,$showLoginForm,$show_footer,$show_header;
	global $data_path,$actionLabel,$buttonsBar,$tab_index;
	global $dbUser, $dbPath, $base_path, $CONTROLKEY, $INTERNALKEY;
	global $history, $wi400Batch, $wi400Cli;
	// Azioni CLI consentite solo da Localhost
    if(!isset($_SERVER['SHELL']) && $_SERVER['SERVER_ADDR']!=$_SERVER['REMOTE_ADDR']) {
		die("ACCESSO NON CONSENTITO");
	}
	/**
	 * ATTENZIONE!!! Le operazioni sulla memoria condivisa valgono solo per l'ambiente CLI, qualsiasi cosa creata da php eseguito da webServer non è vista dall'ambiente CLI
	 */
	ini_set("display_errors", 1);	
	ob_implicit_flush();
	$wi400Cli = True;
	// Recupero percorso base applicazione
	//require_once "appBase.php";
    echo "Inizio elaborazione cli\r\n";
	// CARICAMENTO XML
	// Ricavo l'ID del record che devo elaborare
	if (isset($argv['ID'])) {
		// Carico il file XML con i parametri per l'elaborazione BATCH
		$file = $argv['ID'];
		$handle = fopen($file, "r");
		$xml = fread($handle, filesize($file));
		fclose($handle);
		echo "Get ID $file\r\n";
		$parametri = array();
		$dom = new DomDocument('1.0');
		$dom->loadXML($xml);
		$params = $dom->getElementsByTagName('parametro');
		$i=0;
		foreach ($params as $param){
			$parametri[$params->item($i)->getAttribute('id')]=$params->item($i)->getAttribute('value');
			$i++;
		}		
	} else {
		// I parametri sono passati come linea comando 
		$parametri = array();
		for ($i=1;$i < $argc;$i++)
		{
			parse_str($argv[$i],$tmp);
			$parametri = array_merge($parametri, $tmp);
				
		}
		print_r($parametri);
	}
    
	if (isset($parametri['fileSave'])) {
		  $fileSave = $parametri['fileSave'];
		  $handle = fopen($fileSave, "a");
		  fwrite($handle, $xml);
		  fclose($handle);
	}
// @fix problema con headers : Cannot send session cache limiter - headers already sent 	
//	echo "<pre>";
//	print_r($parametri);
	$appBase = $parametri['appBase'];
	if ($appBase =="") {
		$appBase = '/WI400/';
	}
	// Configurazione
	$_SESSION['user']=$parametri['user'];
	//$_SESSION['lista_librerie']= explode(";", $parametri['lista_librerie']);
	require_once dirname($_SERVER['PHP_SELF'])."/base/includes/config.php";
	$phpJobName="PHP_CLI";
	$settings['jobname']="PHP_CLI";
	if (isset($parametri['jobname'])) {
		$newname = substr($parametri['jobname'],0,10);
		$phpJobName=$newname;
		$settings['jobname']=$newname;		
	}	
	// Inizializzazione
	$settings['i5_conn_type'] = 'T'; 
    $settings['db_conn_type'] = 'T';
	// Caricamento classi
	require_once $base_path."/includes/loader.php";
	// @fix problema con headers : Cannot send session cache limiter - headers already sent


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
	// Il loader resetta la sessione e quindi reimposto l'utente
	$_SESSION['user']=$parametri['user'];
	require_once p13n("/base/includes/init.php");
	$_SESSION['user']=$parametri['user'];
	// Caricamento sistema informativo
	$foundSys=false;
	// 1: Passate le librerie
	if (isset($parametri['lista_librerie'])) {
		// FIX per passaggio libreria con piripacchio § che non viene correttamente convertito 
		$_SESSION['lista_librerie']= explode(";", str_replace("@", "§" ,$parametri['lista_librerie']));
		//$_SESSION['lista_librerie']= explode(";", $parametri['lista_librerie']);
		$foundSys=True;
	}	    
	// 2: Passato il sistema informativo
	if (isset($parametri['sistema_informativo'])) {
		$_SESSION['lista_librerie']= retrive_sysinf_by_name($parametri['sistema_informativo']);
		$foundSys=True;
	}	    
	// 3: Lo carico dal profilo utente
	if ($foundSys==False) {
		$_SESSION['lista_librerie']= retrive_sysinf($parametri['user']);
		$foundSys=True;		
	}
	if (!isset($settings['xmlservice'])) {
		$mycon->add_to_librarylist($_SESSION["lista_librerie"] , $connzend);	
	}

	$db->add_to_librarylist($_SESSION["lista_librerie"], True);	
	// Controllo se reperire il DB-ENVIRONMENT
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
	// Caricamento lingue
	$infoUtente = rtvUserInfo($parametri['user']);
	$_SESSION['USER_LANGUAGE']= $infoUtente['LANGUAGE'];
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
	foreach ($parametri as $key => $value){
		$batchContext->__set($key, $value);
	}
	// Aggiornamento start dell'azione Batch
	if (!isset($parametri['nodb'])) {
		$sql = "UPDATE FBATCHJB SET TIMESTART='".getASTimestamp()."' ,STATO='2', 
	    ASUSER='".$header['ASUSER']."', ASJOB='".$header['ASJOB']."',ASJOBN='".$header['ASJOBN']."' 
		WHERE ID='".$parametri['id']."'";
	    $db->query($sql);
	}
	// Scrittura nel log del lavoro l'azione
	$pgm = new wi400Routine("ZDIAGMSG", $connzend);
	$pgm->load_description();
	$pgm->prepare();
	$message = "Azione:".$batchContext->action;
	if (isset($_SESSION['user'])) $message .= " USER:".$_SESSION['user'];
	$message .= " ".$appBase. "-".$parametri['id'];
	$pgm->set('MESSAGE',$message);
	$pgm->call();	
	// BATCH_ACTION
	echo "Azione da elaborare:".$batchContext->action."\r\n";
	$actionRow = rtvAzione($batchContext->action);

	$actionContext->setForm($batchContext->form);
	$actionContext->setType($actionRow["TIPO"]);
	$actionContext->setModule($actionRow["MODULO"]);
	$actionContext->setModel($actionRow["MODEL"]);
	$actionContext->setView($actionRow["VIEW"]);
	
	//if ($actionRow["TIPO"] = "B" && $actionRow["MODEL"] && $actionRow["MODEL"] != ""){
		require_once p13n($actionContext->getModelUrl($actionRow["MODEL"]));
		require_once p13n($actionContext->getViewUrl());
	//}
	$stato = '9';
	// Controllo se l'azione mi ha dato degli errori o eventuali errori
	if ($messageContext->getSeverity()=="ERROR") {
		$stato = 'E';
	}

	// Aggiornamento esecuzione batch
	if (!isset($parametri['nodb'])) {	
		$sql = "UPDATE FBATCHJB SET TIMECOMPLETE='".getASTimestamp()."' ,STATO='$stato' WHERE ID='".$parametri['id']."'";
	    $db->query($sql);
	}  
	// Disconnect XML Service
	if (isset($settings['xmlservice']) && !isset($parametri['private'])) {
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
    echo "Elaborazione terminata con stato: ".$stato."\r\n";
?>
