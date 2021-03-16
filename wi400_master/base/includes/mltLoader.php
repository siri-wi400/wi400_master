<?php

	require_once p13n("/base/includes/init.php");
	global $db, $connzend, $mycon, $arch;
	
	/*
	 * Esempi di url ricevuto:
	 * http://172.16.100.201:89/WI400/index.php?t=P101&LEFT_MENU_STATUS=close&cd_utente=dmartiniello&cd_pdv=001830&cd_gruppo=028&fg_utente=P
	 * http://10.0.50.1:89/WI400/index.php?t=P101&LEFT_MENU_STATUS=close&cd_utente=prova123&cd_pdv=001830&cd_gruppo=028&fg_utente=P
	 *
	 * cd_utente = profilo utente di login
	 * cd_pdv    = codice cliente
	 * cd_gruppo = gruppo di appartentenza
	 *
	 */
		
	// Facoltatico: aggiungere controllo di verifica indirizzo IP di chiamata
	// es.  [REMOTE_ADDR] => 10.0.15.240
	// $_SERVER['REMOTE_ADDR']  
	
	$userName = '';

	// Verifico se chiamata da portale con attività di inserimento utenti
	if ($_GET ['fg_utente'] == 'P' || $_GET ['fg_utente'] == 'T') {
		
		$userName = strtoupper ( $_GET ['cd_utente'] );
		
		// Verifico se l'utente è presente su DB
		$sql = "select * from " . $users_table . " where user_name='" . $userName . "'";
		$result = $db->singleQuery ( $sql );
		$row = $db->fetch_array ( $result );
		//	echo $sql."<pre>"; print_r ($row) ; echo "</pre>"; 
		
		// Se l'utente non è presente chiamo routine di inserimento
		if (! $row) {
			
			if ($_GET ['fg_utente'] == 'P') {
				$user_sysinf = $settings ['loader_user_prod'];
			} else {
				$user_sysinf = $settings ['loader_user_test'];
			}
			
			$library = $architettura->retrive_sysinf ( $user_sysinf );
			//print_r($library); die();
			$db->add_to_librarylist ( $library );
			if (isset ( $mycon )) {
				$mycon->add_to_librarylist ( $library, $connzend );
			}
			
			// Richiamo programma inserimento utente da portale
			$in = "";
			$in .= "FLD1(" . $userName . ")";
			$in .= "FLD2(" . $user_sysinf . ")";
			$in .= "FLD3(" . $_GET ['cd_pdv'] . ")";
			$in .= "FLD4(" . $_GET ['cd_gruppo'] . ")";
			//echo 'in -->'.$in.'<br>';
			
			$wilgrg01 = new wi400Routine ( 'WILGRG01', $connzend );
			$wilgrg01->load_description ( "w400" );
			$wilgrg01->prepare ();
			$wilgrg01->clearDS ( "W400DSIN" );
			$wilgrg01->clearDS ( "W400DSOU" );
			$wilgrg01->setDSParm ( "W400DSIN", "W400PARMIN", $in );
			$wilgrg01->call ();
			
			// Controllo Esito
			$w400dsou = $wilgrg01->get ( 'W400DSOU' );
			if ($w400dsou ['W400ERRORE'] == 'S') {
				$messageContext->addMessage ( "ERROR", $w400dsou ['W400MESSOU'] );
				header ( "Location: " . $appBase . "index.php" );
				exit ();
			}
		}
	}
		
	//echo 'utente url==>'.$userName; die();
	
	//________________________________________________________________________________________________________-
	// Collegamento Standard
	$metodo = $settings['auth_method'];
	// Verifico se l'utente che mi hanno passato ha una metodo di autorizzazione diverso
	// Verifico se l'utente è presente su DB
	$sql = "select * from " . $users_table . " where user_name='" . $userName  . "'";
	$result = $db->singleQuery ( $sql );
	$row = $db->fetch_array ( $result );
	// Se l'utente non è codificato in tabella WI400 e non sono previsti utenti esterni esco    
	if (! $row && $settings['only_wi400_user']) {
		$messageContext->addMessage("ERROR", "UTENTE NON CODIFICATO");
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
	if (!isset($_GET["t"]))	{
		header("Location: ".$appBase."index.php?t=LOGIN_PROFILE");	
	} else {
		$parametri = $_GET;
		$stringa="";
		foreach ($parametri as $key=>$value) {
			if ($key!="t" && $key!="fg_utente") {
				$stringa .= "&$key=$value";
			}
		}
		header("Location: ".$appBase."index.php?t=".$_GET["t"].$stringa);
	}
	exit();

?>