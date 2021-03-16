<?php
    global $settings, $routine_path, $architettura;
    
	// *******************************************************
	// Contesti
	// *******************************************************
	$messageContext = new wi400Messages();
	$actionContext  = new wi400ActionContext();
	$lookUpContext  = new wi400LookUp("CONTEXT");
	$breadCrumbs    = new wi400BreadCrumbs();
	$buttonsBar 	= array();
	$viewContext 	= new wi400ValuesContainer();
	$gatewayContext = new wi400ValuesContainer();
	$modelContext   = new wi400ValuesContainer();
	$menuContext 	= new wi400MenuContext();
	
	if (wi400Session::exist(wi400Session::$_TYPE_HISTORY, "BREAD_CRUMBS")){
		$history = wi400Session::load(wi400Session::$_TYPE_HISTORY, "BREAD_CRUMBS");
	}else{
		$history = new wi400History();
		wi400Session::save(wi400Session::$_TYPE_HISTORY, "BREAD_CRUMBS", $history);
		//$_SESSION["WI400_HISTORY"] = $history;
	}
	
	if (isset($_SESSION["WI400_WIZARD"])){
		$wi400Wizard = $_SESSION["WI400_WIZARD"];
	}
	// *******************************************************
	// Decorazione della pagina
	// *******************************************************
	$pageDefaultDecoration = "";
	$show_header = true;
	$show_footer = true;
	if (isset($_REQUEST['DECORATION'])){
		$pageDefaultDecoration = $_REQUEST['DECORATION']."_";
	}
	// **************************************************
	// Connessione al sottisistema ZEND per le chiamate native su AS400 se previsto da configurazione
	// **************************************************
	$connzend = "";
	$delay = True;
	if (isset($settings['i5_toolkit'])) {
		require_once $routine_path.'/classi/wi400Connect.cls.php';
		$privateId=0;
		if (isset($_SESSION['connectionID'])) {
			$privateId=$_SESSION['connectionID'];
		}
		$mycon = new i5_connect($settings['server_zend_ip'], $settings['db_user'], $settings['db_pwd'], $settings['i5_conn_type'],true);
		// @todo decimal separator in base a db2_query o i5_query
		$i5_dec_separator = null;
		if (isset($settings['i5_dec_separator'])){
			$i5_dec_separator = $settings['i5_dec_separator'];
		}
		$mycon->set_options($phpJobName ,null ,$i5_dec_separator ,null , null ,null, 600 , True,$privateId);
		$connzend = $mycon->connect();
		$_SESSION['connectionID']=$mycon->getPrivateId();
	} else {
		$delay = False;
	}
	// **************************************************
	// Carico il tema grafico
	// **************************************************
	$temiDir     = "themes/";
	//$settings['temaDefault'] = "default";
	if (isset($_GET["theme"])){
		$_SESSION["theme"] = $_GET["theme"];
	}
	if (isset($_SESSION["theme"])){
		$settings['temaDefault'] = $_SESSION["theme"];
	}
	$temaDir     = $appBase.$temiDir.$settings['temaDefault']."/";
	$temaCommonDir=$appBase.$temiDir."common/";
	// **************************************************
	// Architettura
	// **************************************************
	$string = 'architettura_'.strtolower($settings['architettura']);
	$architettura = new $string();
	// Carico il sistema informativo legato all'utente
	// **************************************************
	if (isset($_SESSION['user']) && $_SESSION['user'] != '' && isset($_SESSION["lista_librerie"])) {

		if (isset($settings['base_asp']) && $settings['base_asp']!="" ) {
			setASP($settings['base_asp']);
		}
		$db = new $settings['database'] ();
		//echo "my db is:".$settings['database'];
		if ($settings['database']=='DB2I5') $db->setLink($connzend);
		$db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name'], $settings['db_conn_type'], $settings['db_debug'], $settings['db_log']);
		$db->add_to_librarylist($_SESSION["lista_librerie"]);
		$db->connect($delay);
        if (isset($mycon)) {
			$mycon->add_to_librarylist($_SESSION["lista_librerie"] , $connzend);
        }
		require_once p13n("/base/includes/initInclude.php");
		// Verifico se è definito l'ambiente di test. In questo caso cambio la directory di upload
     	if (!isset($_SESSION['DB-ENVIRONMENT'])) {
     	$_SESSION['DB-ENVIRONMENT']="";	
     	// Aggiungere Lettura Nazione ----
		//$ret = @data_area_read("*LIBL/DB_ENV");
		if (!isset($settings['noroutine'])) {
			$ret = data_area_read("*LIBL/DB_ENV");
	     	if ($ret) {
	     		if (strtoupper($ret)=="TEST") {
	     			 $_SESSION['DB-ENVIRONMENT']="TEST";
	     		}
	     	}
		}
     	} else  {
     		if($_SESSION['DB-ENVIRONMENT']=='TEST') {
     			$settings['uploadPath'] = "/upload_test/";
     		}
       	}
	} else {
		// Cosa faccio se non ho niente da caricare. Metto almeno quelle alternative
		$alternativa = array();
		if (isset($settings['base_asp']) && $settings['base_asp']!="" ) {
			setASP($settings['base_asp']);
		}
		//$alternativa = explode(";",$settings['db_lib_list']);
		if (isset($mycon)) {
			$mycon->add_to_librarylist($alternativa,$connzend);
		}	
		$db = new $settings['database'] ();
		if ($settings['database']=='DB2I5') $db->setLink($connzend);		
		$db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name'], $settings['db_conn_type'], false, $settings['db_log']);
		$db->add_to_librarylist($alternativa);
		$db->connect($delay);
		
	} 

	//require_once p13n($routine_path.'/classi/wi400Persistent_Table.cls.php');
	//$persTable = new wi400Persistent_Table(True);
	if (version_compare(PHP_VERSION, '7.3.0') <= 0) {
	if (get_magic_quotes_gpc())
		{
			// Apply stripMagicQuotes() to all data
			$_GET = stripMagicQuotes($_GET);
			$_POST = stripMagicQuotes($_POST);
			$_COOKIE = stripMagicQuotes($_COOKIE);
			$_REQUEST = stripMagicQuotes($_REQUEST);
		}
	}
	// Apply addslashesRecursive() to all data
	$_GET = addslashesRecursive($_GET);
	$_POST = addslashesRecursive($_POST);
	$_COOKIE = addslashesRecursive($_COOKIE);
	$_REQUEST = addslashesRecursive($_REQUEST);
	//This is the string that is inserted after the user name and again after the message in the update log.
	//This can't be the same as anything that a user would type.  Do not change.
	$delimiter = "--//--";
	
	$badmode = False;
	$current_time = gmdate($settings['time_format']);
	
	// Istanzio oggetto di architettura
	//$string = 'architettura_'.strtolower($settings['architettura']);
	//$architettura = new $string();
	// p13n Personalizzata per utente
	if (isset($_SESSION['my_p13n']) && $_SESSION['my_p13n']!="" && $_SESSION['my_p13n']!="default") {
		$settings['p13n']= $_SESSION['my_p13n'];
	}
	$p13n_path    = "p13n/".$settings['p13n']."/";
	$settings['p13n_path']= $p13n_path;
	//echo $routine_path.'/classi/wi400Persistent_Table.cls.php';

	require_once p13n('/routine/classi/wi400Persistent_Table.cls.php');
	$persTable = new wi400Persistent_Table(True);
	// Inizializzazione lista azioni da cache sistema se presente, al momento mi interessa solo LOGIN
	if (!isset($_SESSION['LIST_ACTION'])) {
		$filename = wi400File::getCommonFile("serialize", "AZIONE_LOGIN.dat");
		$array=fileSerialized($filename);
		if ($array == Null) {
			$sql = "SELECT * FROM $AS400_azioni WHERE AZIONE = 'LOGIN'";
			$array = make_serialized_file($sql, $filename, array("AZIONE"));
		}
		$_SESSION['LIST_ACTION']['LOGIN'] = $array['LOGIN'];
		/*$_SESSION['LIST_ACTION']['LOGIN'] = array(
		 'ID'  => 11111,
				'AZIONE' =>  'LOGIN',
				'DESCRIZIONE' =>  'Pagina Login',
				'MODULO' => "auth",
				'TIPO'  => 'N',
				'ICOMENU' => '',
				'CHKPGM' => '',
				'EXPICO' => '',
				'MODEL' => 'login_model.php',
				'VIEW' => 'login_view.php',
				'CONTROL' => '',
				'TIPOPGM' => '',
				'WI400_GROUPS' => '',
				'GATEWAY' => '',
				'VALIDATION' => '',
				'LOG_AZIONE' => 'N'
		);*/
	}
	// EXIT POINT TRIGGER
	$wi400_trigger = new wi400ExitPoint();
	$wi400GO = new wi400GlobalObject();
?>
