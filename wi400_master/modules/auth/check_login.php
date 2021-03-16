<?php
	// WI400-MACRO:REGISTER EXIT POINT 
	$wi400_trigger->registerExitPoint("CHECK_LOGIN","BEFORE_CHECK", "*WI400", "Punto uscita prima del controllo password", "user");
	$wi400_trigger->registerExitPoint("CHECK_LOGIN","AFTER_CHECK", "*WI400", "Punto uscita dopo controllo password", "user;check");
	$wi400_trigger->registerExitPoint("CHECK_LOGIN","AFTER_LOGIN", "*WI400", "Punto uscita dopo esito positivo controllo login", "user");
	// END-WI400-MACRO
    /*if (isset($_GET['user'])) {
		$_POST['user']=$_GET['user'];
	    $_POST['password']=$_GET['password'];
    }*/
    
	$userName = "";
	if (isset($_POST['user'])){
		$userName = trim($_POST['user']);
	}		
	// Verifico se l'utente è bloccato
	/* TRIGGER-EXIT_POINT (CHECK_LOGIN,BEFOR_CHECK,"","", (user))
	 * NAME CHECK_LOGIN
	 * EVENT BEFORE_CHECK
	 * AZIONE 
	 * FORM
	 * PARAMETERS user,
	 * RETURN HTML
	 */
	$wi400_trigger->executeExitPoint("CHECK_LOGIN","BEFORE_CHECK", array('user'=>$_POST['user']));
	
	$pwd =  getMD5();
	$check = checkUser($userName, $pwd);
	$wi400_trigger->executeExitPoint("CHECK_LOGIN","AFTER_CHECK", array('user'=>$_POST['user'],'check'=>$check));
	// Controllo autenticazione utente
	if($check===true) {		
		// Controllo ip
		$check = new wi400Control();
		$risultato = $check->ip_filter($userName);
		if ($risultato){
	
			$_SESSION['user'] = $userName;
			$_SESSION['pw'] = PMA_blowfish_encrypt($pwd, session_id());
			
			$_SESSION['data_validita'] = date("Ymd");
			
			// Contenitore per le azioni di menu autorizzate
			$_SESSION['auth_action'] = array();
			
			// Recupero informazioni utente
			$user = rtvUserInfo(strtoupper($userName));
//			echo "USER:<pre>"; print_r($user); echo "</pre>";die();
			$_SESSION['DEFAULT_ACTION'] = $user['DEFAULT_ACTION'];
			// Linguaggio utente
			$_SESSION['USER_LANGUAGE'] = $user['LANGUAGE'];
			// Recupera da DB ultimo accesso
			$_SESSION['last_login'] = $user['TIME_OFFSET'];
			// Controllo se il profilo ha un tema personalizzato
			if (isset($user['THEME']) && $user['THEME']!="" && $user['THEME']!="default") {
				$_SESSION["theme"] = $user['THEME'];
			}
			if (isset($user['OFFICE']) && $user['OFFICE']!="" && $user['OFFICE']!="default") {
				$_SESSION['my_p13n'] = $user['OFFICE'];
			}
			$_SESSION['my_package']="";
			if (isset($user['PACKAGE']) && $user['PACKAGE']!="" && $user['PACKAGE']!="default") {
				$_SESSION['my_package'] = $user['PACKAGE'];
			}
			// Utilizzato da fckeditor
			if (isset($_POST['HTTP_URL'])){
				$_SESSION['HTTP_URL'] = $_POST['HTTP_URL'];
			}
			if(isset($user['ADMIN']) && !empty($user['ADMIN'])) {
				$_SESSION["user_admin"] = $user['ADMIN'];
			}
			
			$treeMenu = new HTML_TreeMenu_DHTML(explodoMenu($user['MENU']), array('images' => $temaDir."images/tree", 'defaultClass' => 'wi400-tree-menu'));
			$treeMenu->setAc(countMenu($user['MENU']));
			wi400Session::save(wi400Session::$_TYPE_TREE, "TREE_MENU", $treeMenu);
			
			$treeUserMenu = new HTML_TreeMenu_DHTML(explodoMenu($user['USER_MENU']), array('images' => $temaDir."images/tree", 'defaultClass' => 'wi400-tree-menu'));
			wi400Session::save(wi400Session::$_TYPE_TREE, "USER_MENU", $treeUserMenu);
			
			// Salva su DB il nuovo accesso
			setTimeOffset($userName);
			if (isset($settings['user_statistics']) && $settings['user_statistics']) {
				writeUserStatistics($userName);
			}
			if (isset($settings['tmp_clean_logout']) && $settings['tmp_clean_logout']==False) {
				// Nulla
			} else {
				wi400File::deleteExportFiles();
			}
			$wi400_trigger->executeExitPoint("CHECK_LOGIN","AFTER_LOGIN", array('user'=>$_POST['user']));
			//$customTrigger->addExitPoint("CHECK_LOGIN", "AFTER_INIT", $param = array("LOGIN"=>$_POST['user']);
		}
		else{	
			// Ip bloccato
			$messageContext->addMessage("ERROR", _t("IP_NON_ABILITATO"));
		}
	}
	else if($check===false) {
		if (isset($_POST['password']) && $_POST['password'] !== "" 
				&& strtoupper($_POST['password']) == $_POST['password']){
			$messageContext->addMessage("ALERT", _t("TASTO_CAPS_LOCK"));
		} 
	}
	else if($check=='3') {
		//echo "PASSWORD SCADUTA<br>";
		goHeader($appBase."index.php?t=CHGPWD&f=LOGIN&DECORATION=login&user=$userName");
		//header("Location: ".$appBase."index.php?t=CHGPWD&f=LOGIN&DECORATION=login&user=$userName");

		$messageContext->addMessage ("ERROR", _t("W400011"));
		die();
	}
	function writeUserStatistics($name) {
			global $settings, $db;
			// Scrittura Log Accesso
			$values= array();
			$values['ZSUTE']= $name; //UTENTE
			$values['ZAGEN']= $_SERVER['HTTP_USER_AGENT'];
			$values['STAT']="";
			$values['ZIPAD']=substr($_SERVER['REMOTE_ADDR'],0,500);
			$values['ZURI']=substr($_SERVER['REQUEST_URI'],0,500);
			$values['ZLANG']=substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,100);
			$values['ZREFE']=substr($_SERVER['HTTP_REFERER'],0,200);
			$values['ZSESID']=session_id();
			$values['ZBAS']="";
			$values['ZALT']="0";
			if (isset($_SESSION['SCREEN_ALTEZZA'])) $values['ZALT']=$_SESSION['SCREEN_ALTEZZA'];
			if (isset($_SESSION['SCREEN_BASE'])) $values['ZBAS']=$_SESSION['SCREEN_BASE'];
			
			$values['ZTIME']= getDb2Timestamp();  //TIMESTAMP
			
			$stmtDoc = $db->prepare("INSERT", "ZSLOGSTS", null, array_keys($values));
			$result = $db->execute($stmtDoc, $values);
			
	}
?>