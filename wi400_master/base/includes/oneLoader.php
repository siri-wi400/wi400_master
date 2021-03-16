<?php

	require_once p13n("/base/includes/init.php");

	// Verifico indirizzo IP di chiamata
	
	// $_SERVER['CLIENT_ADDRESS']
	
	$userName = '';
	
	// Verifico se chiamata da portale con attività di inserimento utenti
	if ($_GET['ONE'] == 'P') {
		
		$userName = strtoupper($_GET['cd_utente']);	
		
		// Verifico se l'utente è presente su DB
		$sql = "select * from " . $users_table . " where user_name='" . $userName . "'";
		$result = $db->singleQuery ( $sql );
		$row = $db->fetch_array ( $result );
		
		// Se non presente chiamo routine di inserimento
		if (! $row ) {
			
		//echo $sql.'inserimento'; die();
		}
		//....
		
		
		
	    // Segnalo errori di inserimento
			if (false) {
			$messageContext->addMessage("ERROR", "MESSAGGIO ERRORE");
			header("Location: ".$appBase."index.php");
			exit();	
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
			if ($key!="t" && $key!="ONE") {
				$stringa .= "&$key=$value";
			}
		}
		header("Location: ".$appBase."index.php?t=".$_GET["t"].$stringa);
	}
	exit();

?>