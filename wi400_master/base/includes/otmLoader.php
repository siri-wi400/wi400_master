<?php

	require_once p13n("/base/includes/init.php");

	// Verifico se esiste la ONE TIME PASSWORD
//	$sql = "select * FROM PHPLIB".$settings['db_separator']."SIR_OTM WHERE OTMID='".$_GET['OTM']."'";
	$sql = "select * FROM SIR_OTM WHERE OTMID='".$_GET['OTM']."'";
	$result = $db->singleQuery($sql);
	$row = $db->fetch_array ($result);
	// se non ho letto nulla vado al LOGIN - OTM non trovata
	if (!$row) {
		$messageContext->addMessage("ERROR", "OTM_SCADUTA: Utilzzare utente e password per il collegamento");		
		header("Location: ".$appBase."index.php");
		exit();		
	}
	// se trovata la cancello immediatamente
//	$sql = "DELETE FROM PHPLIB".$settings['db_separator']. "SIR_OTM WHERE OTMID='".$_GET['OTM']."'";
	if ($row['OTMTYP']!="STATIC") {
		$sql = "DELETE FROM SIR_OTM WHERE OTMID='".$_GET['OTM']."'";
		$result = $db->query($sql);

		if (!$result) {
			$messageContext->addMessage("ERROR", "OTM_ANOMALA");		
			header("Location: ".$appBase."index.php");
			exit();		
		}
		$dati = explode(";",$row['OTMCON']);
		foreach ($dati as $key => $valore) {
			$dati2 = explode("=", $valore);
			$parametri[$dati2[0]]=$dati2[1];
		}
		// Metto in session il codice OTM per usi FUTURI
		$_SESSION['WI400_OTM_CODE']=$_GET['OTM'];
		if (isset($parametri['ACTION'])) {
			$_GET['t']=$parametri['ACTION'];
		}
	} else {
		// Aggiungo parametri contenuti nella REQUEST
		$_SESSION['WI400_OTM_PARAMETERS']=$row['OTMCON'];
		// Controllo se presente una azione da richiamare in automatico
		$dati = explode(";",$_SESSION['WI400_OTM_PARAMETERS']);
		foreach ($dati as $key => $valore) {
			$dati2 = explode("=", $valore);
			$parametri[$dati2[0]]=$dati2[1];
		}
		// Metto in session il codice OTM per usi FUTURI
		$_SESSION['WI400_OTM_CODE']=$_GET['OTM'];
		if (isset($parametri['ACTION'])) {
			$_GET['t']=$parametri['ACTION'];
		}
		if (isset($parametri['GATEWAY'])) {
			$_GET['g']=$parametri['GATEWAY'];
		}
		if (isset($parametri['OVERRIDE_CONF']) && $parametri['OVERRIDE_CONF']!="") {
			$_SESSION['override_conf_parm_file']=$parametri['OVERRIDE_CONF'].".conf.php";
		}
		// Parametri aggiuntivi OTM
		$_SESSION['WI400_OTM_PARM']=$_GET['OTM_PARM'];
	}
	// Controllo il timestamp
	$tm = $row['OTMTIM'];
	$anno = substr($tm,0,4);
	$mese  = substr($tm,5,2);
	$giorno= substr($tm,8,2);
	$ora = substr($tm,11, 2);
	$minuti = substr($tm,14,2);
	$secondi = substr($tm, 17, 2);
	//echo $ora."min".$minuti."secondi".$secondi."mese".$mese."giorno".$giorno."anno".$anno;
	$time_unix = mktime($ora+1,$minuti,$secondi+20,$mese,$giorno,$anno);
	$adesso = time();
	// se non ho letto nulla vado al LOGIN - OTM non trovata
	if ($time_unix < $adesso && $row['OTMTYP']!="STATIC") {
		$messageContext->addMessage("ERROR", "OTM_SCADUTA");		
		header("Location: ".$appBase."index.php");
		exit();		
	}	
	//
	$rowOtm=$row;
	//$codice = $row ['T17SOC'];	
	$userName = $row['OTMUSR'];
	if (isset($parametri['ACTIVATE']) && $parametri['ACTIVATE']=='S') {
		require_once $routine_path."/classi/wi400AdvancedUserSecurity.cls.php";
		$advanced_security = new wi400AdvancedUserSecurity($userName);
		$bloccato = $advanced_security->setUserUnBlocked();
		require_once $base_path."/includes/language.php";
		$messageContext->addMessage("INFO", _t("W400024"));
		$wi400_trigger->registerExitPoint("USER_ACTIVATE","AFTER", "*WI400", "Attivazione Utente", "user");
		$wi400_trigger->executeExitPoint("USER_ACTIVATE","AFTER", array('user'=>$userName));
	}
	// Controlli Trigger Login
	$wi400_trigger->executeExitPoint("CHECK_LOGIN","BEFORE_CHECK", array('user'=>$userName));
	$wi400_trigger->executeExitPoint("CHECK_LOGIN","AFTER_CHECK", array('user'=>$userName, 'check'=>True));

	$metodo = $settings['auth_method'];
	if (!isset($_SESSION['user'])) {
		// Verifico se l'utente che mi hanno passato ha una metodo di autorizzazione diverso
		// Verifico se l'utente è presente su DB
		$sql = "select * from " . $users_table . " where user_name='" . strtoupper ( $userName ) . "'";
		$result = $db->singleQuery ( $sql );
		$row = $db->fetch_array ( $result );
		// Se l'utente non è codificato in tabella WI400 e non sono previsti utenti esterni esco    
		if (! $row && $settings['only_wi400_user']) {
			$messageContext->addMessage("ERROR", "OTM_NON_VALIDA");
			header("Location: ".$appBase."index.php");
			exit();	
		} else {
			if (($row ['AUTH_METOD'] != $metodo) && ($row ['AUTH_METOD'] != '*DEFAULT') && ($row ['AUTH_METOD'] != '')) {
				$metodo = $row ['AUTH_METOD'];
			}
			$_SESSION ['USER_LIBL'] = $row ['USER_GROUP'];
		}
		
		if (isset ( $row ['WI400_GROUPS'] ) && $row ['WI400_GROUPS'] != "") {
			// Questo valore sarà sovrascritto in caso di autorizzazione con LDAP
			$_SESSION ['WI400_GROUPS'] = explode ( ";", $row ['WI400_GROUPS'] );
		}
		
		$_SESSION ['AUTH_METOD'] = $metodo;
		$stringa = 'checkUser'.$metodo;	
		//		
		$_SESSION['user'] = $userName;	
		$_SESSION['data_validita'] = date("Ymd");
		// Contenitore per le azioni di menu autorizzate
		$_SESSION['auth_action'] = array();
		// Recupero informazioni utente
		$user = rtvUserInfo($userName);
		$_SESSION['DEFAULT_ACTION']= $user['DEFAULT_ACTION'];
		// Linguaggio utente
		$_SESSION['USER_LANGUAGE']=$user['LANGUAGE'];
		// Recupera da DB ultimo accesso
		$_SESSION['last_login'] = $user['TIME_OFFSET'];
		// Utilizzato da fckeditor
		if (isset($_POST['HTTP_URL'])){
			$_SESSION['HTTP_URL'] = $_POST['HTTP_URL'];
		}
		if (isset($user['OFFICE']) && $user['OFFICE']!="" && $user['OFFICE']!="default") {
			$_SESSION['my_p13n'] = $user['OFFICE'];
			$settings['p13n']=$user['OFFICE'];
		}
		$treeMenu = new HTML_TreeMenu_DHTML(explodoMenu($user['MENU']), array('images' => $temaDir."images/tree", 'defaultClass' => 'wi400-tree-menu'));
		//$_SESSION['tree_menu'] = $treeMenu;
		wi400Session::save(wi400Session::$_TYPE_TREE, "TREE_MENU", $treeMenu);
		$treeUserMenu = new HTML_TreeMenu_DHTML(explodoMenu($user['USER_MENU']), array('images' => $temaDir."images/tree", 'defaultClass' => 'wi400-tree-menu'));
		//$_SESSION['user_menu'] = $treeUserMenu;
		wi400Session::save(wi400Session::$_TYPE_TREE, "USER_MENU", $treeUserMenu);
		// Salva su DB il nuovo accesso
		setTimeOffset($userName);
		wi400File::deleteExportFiles();
		loadUserLibraries($_SESSION['user']);	
		$dbUser = $_SESSION['user'];
		$dbTime = getASTimestamp();	
		$_SESSION["LOGOUT_ACTION"] = "LOGOUT";
	}
	if (!isset($_GET["t"]))	{
		header("Location: ".$appBase."index.php?t=LOGIN_PROFILE");	
	} else {
		$parametri = $_GET;
		$stringa="";
		foreach ($parametri as $key=>$value) {
			if ($key!="t" && $key!="OTM") {
				$stringa .= "&$key=$value";
			}
		}
		header("Location: ".$appBase."index.php?t=".$_GET["t"].$stringa);
	}
	exit();

?>