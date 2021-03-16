<?php
	/**
	* file:	config.php
	*
	* 		Operazioni iniziali per caricamento parametri server e parametri
	*       generali dell'applicazione
	*
	* Copyright 2008 SIRI Informatica s.r.l
	* info@siri-informatica.it
	* http://www.siri-informatica.it
	* Developers: Massimiliano Consigli
	* Versione: 1.0.0
	****************************************************************************/
	global $settings, $appBase, $debugContext;
		
	//error_reporting(E_ALL);
	//ini_set("display_errors", 1);
	/*function customError($errno, $errstr, $errfile, $errline)
  	{
		  echo "<pre><b>Error:</b> [$errno] $errstr -- $errfile -- :$errline<br />";
		  debug_print_backtrace();
		  echo "<pre>";
		  
  	}
  	set_error_handler("customError");*/		
	//$myErrorHandler = set_error_handler("wi400ErrorHandler");
	/*$conffile = "wi400.conf.php";
	// Caricamento file di configurazione
	if (php_sapi_name() == 'cli') {
		//require_once dirname(filter_input(INPUT_SERVER, 'PHP_SELF'))."/conf/$conffile";
		$dircli = dirname(dirname(dirname(__FILE__)));
		require_once $dircli."/conf/$conffile";
		$root_dir = dirname($dircli);		
		if (is_file("$root_dir/settings/wi400CustomerBase.conf.php")) {
			require_once "$root_dir/settings/wi400CustomerBase.conf.php";
			$settings = array_merge($settings, $customerBaseSettings);
		}
		// Carico eventuali configurazioni sensibili fuori root
		if (isset($settings['more_config']) && $settings['more_config']!="") {
			if (file_exists($settings['more_config'])) {
				require_once $settings['more_config'];
			}
		}
	} else {
		if (is_file("conf/$conffile")) {
			require_once "conf/$conffile";
			// Carico configurazione custom installat dal cliente sulla directory settings
			if (is_file("../settings/wi400CustomerBase.conf.php")) {
				require_once "../settings/wi400CustomerBase.conf.php";
				$settings = array_merge($settings, $customerBaseSettings);
			}
			// Carico eventuale parametri aggiunti in override per previsti in sessione
			/*if (isset($_SESSION['override_conf_parm_file']) && is_file("conf/".$_SESSION['override_conf_parm_file'])) {
				require_once "conf/".$_SESSION['override_conf_parm_file'];
				array_merge($settings, $override_conf_parm_file);
			}*/
			// Carico eventuali configurazioni sensibili fuori root
			/*if (isset($settings['more_config']) && $settings['more_config']!="") {
				if (file_exists($settings['more_config'])) {
					require_once $settings['more_config'];
				}
			}
			// Verifico se esiste un environment particolare
			if (isset($_GET['WI400_ENV'])) {
				if ($_GET['WI400_ENV']!="") {
					$_SESSION['WI400_ENV_FILE']=$_GET["WI400_ENV"];
				} else {
					unset($_SESSION['WI400_ENV_FILE']);
				}
			}
			if (isset($_SESSION['WI400_ENV_FILE'])) {
				// Carico e faccio l'override dei parametri
				if (file_exists($settings['path_envfiles'].$_GET['WI400_ENV'].".conf.php")) {
					require_once $settings['path_envfiles'].$_GET['WI400_ENV'].".conf.php";
				}
			}
		} else {
			header("Location: ".$appBase."conf/installer.php");
			exit();
		}
	}*/
	require_once "getconfiguration.php";
	$settings = wi400GetSettings($appBase);
	if($settings['debug'] || isset($_SESSION["DEBUG"])) {
		ini_set("display_errors", 1);
		/*if(isset($settings['display_errors']) && $settings['display_errors']!="") {
			error_reporting($settings['display_errors']);
		}*/
		//else {
		error_reporting(-1);
			//		error_reporting(E_ALL & E_NOTICE);
			//	  	error_reporting(E_ALL & ~E_DEPRECATED);
		//}
		$debugContext = array();
	} else {
		ini_set("display_errors", 0);
	}
	/*echo "<pre>";
	print_r($settings);
	die();*/
	//$settings = parse_ini_file("conf/wi400i5.conf");
	// Conserva l'ordinamento tab
	$tab_index = 0;
	// *******************************************************
	// Impostazione timezone  
	// *******************************************************
	date_default_timezone_set($settings['timezone']);	
	// setto il default_locale
	if (isset($settings['default_locale']) && $settings['default_locale']!="") {
		setlocale(LC_ALL, $settings['default_locale']);
	}
	// setto numeri a formato default C Tutti i numeri con il punto!!!!
	setlocale(LC_NUMERIC, 'C');
	
	// PERCORSI
	if (php_sapi_name() == 'cli') {
		if (!isset($appBase) || $appBase=="") {
			$appBase = basename($dircli);
		}
		$path_root ="/".$appBase."/";
	} else {
		// Fix per direttiva Alias su Apache 2.4
		if (isset($_SERVER['CONTEXT_PREFIX']) && $_SERVER['CONTEXT_PREFIX']!="") {
			$name = explode('/',filter_input(INPUT_SERVER, 'CONTEXT_DOCUMENT_ROOT'));
			$x = count($name)-1;
			$path_root ="/".$name[$x]."/";
		} else {
			$name = explode('/',$_SERVER['REQUEST_URI']);
			$path = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$name[1];
			if(!file_exists($path)) {
			  // may be this is an alias
				$path_root ="/";
			} else {
				$path_root =$appBase;
			}
		}
	}
	$doc_root     = $settings['doc_root'];
	$data_path    = $settings['data_path'];
	$doc_root = substr($doc_root,0, strlen($doc_root)-1);
	$conf_path    = $doc_root.$path_root."conf";
	$root_path    = $doc_root.$path_root;
	$moduli_path  = $doc_root.$path_root."modules";
	$base_path    = $doc_root.$path_root."base";
	$routine_path = $doc_root.$path_root."routine";
	$main_page    = $doc_root.$path_root."index.php";
	$themes_path  = $doc_root.$path_root."themes";
	if (php_sapi_name() != 'cli') {
    	require_once $base_path."/includes/setup.php";
	}
	
	// file di log
	$log_path = $root_path;
	if(isset($settings['log_root']) && $settings['log_root']!="") {
		$log_path = $settings['log_root'];
	
		/*if(strpos($log_path, "##APP_BASE##")!==false) {
			$log_path = str_replace("##APP_BASE##", $appBase, $log_path);
		}
		
		if(strpos($log_path, "##USER##")!==false) {
			$log_path = str_replace("##USER##", $_SESSION['user'], $log_path);
		}*/
	}
	
	$file_error_path = $log_path."logs/error/";
	
	if(!file_exists($file_error_path)) {
		//Non si può usare wi400_mkdir siccome non è stato caricato ancora il common
		mkdir($file_error_path, 777, true);
	}
	
	$file_error_name = "php_error_".date("Ymd").".log";
	
	$do = ini_set("error_log", $file_error_path.$file_error_name);	
	// *******************************************************
	// Funzioni per controllo performance
	// *******************************************************
	function getMicroTime(){
		global $settings;
		if ($settings['debug'] || isset($_SESSION["DEBUG"])){
	    	list($usec, $sec) = explode(" ",microtime()); 
	    	return ((float)$usec + (float)$sec); 
		}
	}
	function getMicroTimeStep($stepName, $logphp=False){
		global $time_start,$time_step,$settings;
		if ($settings['debug'] || isset($_SESSION["DEBUG"])){
	    	$thisTimeStart = getMicroTime() - $time_start;
	    	$thisTimeStep = $thisTimeStart - $time_step;
	    	//echo "<div style=\"background-color:#000000;color:#FFFFFF\"><b>".$stepName."</b>: ".$thisTimeStart." (<i>".$thisTimeStep." dallo step precedente</i>)</div>";
	    	if ($logphp==False) {
	    		echo "<b>".$stepName."</b>: ".$thisTimeStart." (<i>".$thisTimeStep." dallo step precedente</i>)";
	    	} else {
	    		error_log($stepName.": ".$thisTimeStart." (".$thisTimeStep." dallo step precedente)");
	    	}
	    	$time_step = $thisTimeStart;
		}
	}
	if ($wi400Debug) $time_start = getMicroTime();
	// nome della personalizzazione, con le personalizzazioni per cliente
	$p13n_path    = "p13n/".$settings['p13n']."/";
	$settings['p13n_path']= $p13n_path;

	if ($settings['architettura']=="") $settings['architettura'] = 'default';
	if (!isset($settings['directory_separator'])) $settings['directory_separator']="";
	if ($settings['directory_separator']=="") $settings['directory_separator'] = '/';
	if (!isset($settings['crlf'])) $settings['crlf']="";
	if ($settings['crlf']=="") {
		$settings['crlf'] = '<br>';
		if (isset($wi400Batch)) $settings['crlf'] = PHP_EOL;
		if (isset($wi400Cli)) $settings['crlf'] = PHP_EOL;
	}
	if ($settings['package']=="") $settings['package'] = 'default';
	$settings['package']=strtolower($settings['package']);
	if (!isset($settings['caching_type']) || $settings['caching_type']=="") $settings['caching_type'] = 'default';	
    //************ Impostazione variabili con nome tabelle********
	$announcement_table = $settings['table_prefix']."announcements"; // announcement table name
	$users_table = $settings['table_prefix']."USERS"; // users table
	$ugroups_table = $settings['table_prefix']."ugroups"; // users group table
	$AS400_tabella_utenti = "FTAB017";
	$AS400_articoli_immagini = "FARTIIMG";
	$AS400_menu ="FMNUSIRI";
	$AS400_azioni = "FAZISIRI";
	// Array di gruppi dell'applicazione
	if (isset($settings['wi400_groups']) && !empty($settings['wi400_groups'])){
		$wi400_groups = explode(";",$settings['wi400_groups']);
//		echo "GROUPS:<pre>"; print_r($wi400_groups); echo "</pre>";
	}
	if (isset($settings['wi400_sel_groups']) && !empty($settings['wi400_sel_groups'])){
		$wi400_sel_groups = explode(";",$settings['wi400_sel_groups']);
//		echo "SEL GROUPS:<pre>"; print_r($wi400_sel_groups); echo "</pre>";
		
		$wi400_groups = array_merge($wi400_groups, $wi400_sel_groups);
//		echo "WI400 GROUPS:<pre>"; print_r($wi400_groups); echo "</pre>";
	}
	// Default collegamento XMLSERVICE
	if (isset($settings['xmlservice'])){
		if (!isset($settings['xmlservice_jobd_lib']) || $settings['xmlservice_jobd_lib']=="") {
			$settings['xmlservice_jobd_lib']="ZENDSVR";
		}
		if (!isset($settings['xmlservice_jobd']) || $settings['xmlservice_jobd']=="") {
			$settings['xmlservice_jobd']="ZSVR_JOBD";
		}
	}
	// Array di profile steps per il login
	$loginProfileSteps = explode(";",$settings["login_profile"]);
	$phpJobName=$settings['jobname'];
	// Memory Limit
	if (isset($settings["memory_limit"]) && $settings["memory_limit"]!="") {
		ini_set("memory_limit",$settings["memory_limit"]);
	}
	if (!isset($settings['width_breadCrumb'])) {
		$settings['width_breadCrumb']=400;
	}
?>
