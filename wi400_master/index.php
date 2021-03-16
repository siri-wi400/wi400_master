<?php
	global $appBase,$root_path,$wi400Debug,$settings,$time_start,$time_step,$temaDir, $architettura, $currentAction, $currentForm;
	global $viewContext,$debugContext,$modelContext,$gatewayContext,$menuContext,$messageContext,$actionContext,$breadCrumbs,$listContext,$lookUpContext;
	global $wi400Wizard,$pageDefaultDecoration,$showLoginForm,$show_footer,$show_header;
	global $data_path,$actionLabel,$buttonsBar,$tab_index;
	global $dbUser, $dbPath, $log_path, $users_table;
	global $history, $currentHMAC, $WI400_PRAGMA, $wi400GO, $wi400_trigger;
	
	if ((!isset($_GET["t"]) || trim($_GET['t']) != "FILEDWN")) ob_start("ob_gzhandler"); else ob_start();
	// Abilitato il compress di apache
	// Recupero percorso base applicazione
	require_once "appBase.php";
	// Configurazione
	//require_once $_SERVER['DOCUMENT_ROOT'].$appBase."base/includes/config.php";
	require_once "base/includes/config.php";
	
	// Caricamento classi
	require_once $base_path."/includes/loader.php";
	//goHeader("index.php?t=PIPPO&f=FORM1&g=GFA");
	// Salvataggio risoluzione in sessione
	if (isset($_REQUEST['SCREEN_ALTEZZA'])) $_SESSION['SCREEN_ALTEZZA'] = $_REQUEST['SCREEN_ALTEZZA'];
	if (isset($_REQUEST['SCREEN_BASE'])) $_SESSION['SCREEN_BASE'] = $_REQUEST['SCREEN_BASE'];
	
	// RECUPERO REQUEST SALVATA IN PRECEDENZA
	if (isset($_REQUEST['CONT'])) {
		$dati = getSerializeRequest($_REQUEST['CONT']);
		$_REQUEST = array_merge($_REQUEST, $dati);
		$_GET = array_merge($_GET, $dati);
		unset($_GET['CONT']);
	}
	// RECUPERO AJAX POST SERIALIZZATO IN BASE 64
	if (isset($_GET['ID_FILE']) && $_GET['ID_FILE']!="") {
		$filename = wi400File::getUserFile("ajax_post", $_GET['ID_FILE'].".post");
		$custom_data = unserialize(file_get_contents($filename));
		$_REQUEST = array_merge($custom_data, $_REQUEST);
	}
	// Impostazionie si sicurezza
	require_once p13n("/base/includes/security.php");
	/*$handle = fopen("/var/www/html/WI400/session.txt", "a+");
	fwrite($handle, session_id()." - ".$_GET["t"]."\r\n");
	fclose($handle);*/
	
	register_shutdown_function('shutdown');
	// Gestione impostazioni di visualizzazione
	require_once $base_path."/includes/presentation.php";
	// Controllo caricamento OTM Password
	if (isset($_GET['OTM'])) {
		require_once $base_path."/includes/otmLoader.php";
	}
	// Controllo login utente
	if (isset($_SESSION['user'])){
		$userData = "";
		if (isset($_SESSION['user_data'])){
			$userData = $_SESSION['user_data'];
		}
		
		if (isset($_SESSION['user'])){
			$dbUser = $_SESSION['user'];
			$dbTime = getASTimestamp();
		}

		if (isset($_GET["t"]) && trim($_GET['t']) == "LOGIN"){

			// Azione di logout (rimarrà LOGOUT se non previste profilazioni utente)
			$_SESSION["LOGOUT_ACTION"] = "LOGOUT";
			$_SESSION["MY_IP"] = $_SERVER['REMOTE_ADDR'];

	
			// REDIRECT LOGIN_PROFILE
			goHeader($appBase."index.php?t=LOGIN_PROFILE");
			//header("Location: ".$appBase."index.php?t=LOGIN_PROFILE");
			exit();
		}
		
	}else{
			
		if (isset($settings['security_allow_direct_url']) && $settings['security_allow_direct_url']==True) {
			$_SESSION["URL_FROM"] = curPageURL();
		}
//		if (!isset($_GET["t"]) || (trim($_GET['t']) != "LOGIN" && trim($_GET['t']) != "CHECK_LOGIN" )){
		if (!isset($_GET["t"]) || 
				(!in_array(trim($_GET['t']),array("LOGIN","CHECK_LOGIN")) && 
					(trim($_GET['t'])!="CHGPWD" && (!isset($_GET['f']) || trim($_GET['f'])!="LOGIN")))
					){		 				
			// REDIRECT AUTORIZZAZIONE
/*			$secure_connection = false;
			if(isset($_SERVER['HTTPS'])) {
				if ($_SERVER["HTTPS"] == "on") {
					$secure_connection = true;
				}
			}
			if($secure_connection===false)
*/			//$_SESSION["URL_FROM"] = curPageURL();
			// @todo Se sono in finestra devo chiuderla prima di mostrare la pagina di login
			if(isset($_REQUEST['WI400_IS_WINDOW']) && $_REQUEST['WI400_IS_WINDOW']) {
				// Cosa si potrebbe fare!
				?>
				<script>
					top.location.href=top.location.href;
				</script>
				<?php
				die();
			}
			
			goHeader($appBase."index.php?t=LOGIN");
			//header("Location: ".$appBase."index.php?t=LOGIN");
			exit();
		}
	}
	
	if(isset($_SESSION['OLD_MY_IP']) && $_SESSION['OLD_MY_IP']!="") {
//		echo "INDEX IP: ".$_SESSION['MY_IP']." - MY IP: ".$_SESSION['OLD_MY_IP']."<br>";
		if($_SESSION['OLD_MY_IP']!=$_SESSION['MY_IP']) {
//			echo "SESSIONE SOSPESA<br>";
?>
			<script>
				alert("Sessione sospesa.");
			</script>
<?			
		}
		else {
?>
			<script>
				alert("Collegamento ad un'altra sessione attivo.");
			</script>
<?
		}
	}
	// Caricamento lingue
	require_once $base_path."/includes/language.php";
			
	// Inizializzazione
	require_once p13n("/base/includes/init.php");
		
	// Gestione selezioni di liste
	require_once $base_path."/includes/listInterceptor.php";
	
	// Gestione dati da detail
	if (!isset($_GET["PAGINATION"])) {
		require_once $base_path."/includes/detailInterceptor.php";
	}
	
	// Gestione dati lookup
	require_once $base_path."/includes/lookup.php";

	// Gestione locks
	require_once $base_path."/includes/locks.php";

	// Generazione LOG PIDS
	//$_SESSION["DEBUG"]="Ture";
	//getMicroTimeStep("INIZIO");
	if (isset($settings['pid_monitor']) && $settings['pid_monitor']===True) {
		setPidFile(True);
	}
	//getMicroTimeStep("FINE setPID");
	// Action di paginazione lista
	if (isset($_GET["PAGINATION"]) || isset($_GET["ORDER"]) || isset($_GET["FAST_FILTER_LIST"])){
		require_once $moduli_path."/list/wi400Pagination.php";
		exit();
	}
	
	if (is_object($messageContext) && $messageContext->getSeverity() != "ERROR"){
		if (isset($_GET["t"])&& trim($_GET["t"]) == "WIZARD"){
			require_once $moduli_path."/wizard/wi400Wizard.php";
			exit();
		}
	}
	// Action da richiamare
	$isMenuAction = false;
	
	if (isset($_GET["t"])&& trim($_GET["t"]) != ""){
		$message = "Azione:".$_GET["t"];
		$myUser="";
		if (isset($_SESSION['user']))  {
			$message .= " USER:".$_SESSION['user']. "-".session_id();
			$myUser = $_SESSION['user'];
		}
		$message .= " ".$appBase;  

		//logAction($message, $myUser, $_SERVER['REMOTE_ADDR']);

		if ($actionContext->getAction() == ""){
			// Nessun errore durante la validazione
			$actionContext->setAction($_GET["t"]);
			if (isset($_GET["f"]) && trim($_GET["f"]) != ""){
				$actionContext->setForm(trim($_GET["f"]));
			}
			if (isset($_GET["g"]) && trim($_GET["g"]) != ""){
				// Se presente un gateway lo applico				
				$actionContext->setGateway(trim($_GET["g"]));
			}
		}
		$actionRow = rtvAzione($actionContext->getAction());
		if ($actionRow['DISABILITA']=="S") {
			// header PAGE
			require_once $doc_root.$appBase."themes/common/".strtolower($pageDefaultDecoration)."header.php";
			echo "<center><h1 style='font-weight: normal;'><b>Azione:</b> <i>".$actionRow['DESCRIZIONE']."</i><BR/>
						<b>Momentaneamente non disponibile</b></h1>
						<i class='fa fa-exclamation-triangle' style='font-size: 60px; color: red;'></i>
					</center>";
			// footer PAGE
			require_once $doc_root.$appBase."themes/common/".strtolower($pageDefaultDecoration)."footer.php";
			die();
			
		}
		if (isset($actionRow['LOG_AZIONE']) && $actionRow['LOG_AZIONE']=="N") {
			//
		} else {
			logAction($message, $myUser, $_SERVER['REMOTE_ADDR']);
			if (isset($settings['log_user_action']) && $settings['log_user_action']==True) {
				logUserAction($actionRow);
			}
		}
		if (isset($actionRow["TIPO"]) && $actionRow["TIPO"] == "A" && isset($_GET["LCK_DLT"]) && $_GET["LCK_DLT"] == "true"){
			//Menu Action	
			$isMenuAction = true;
		}
		// Verifico se AZIONE CON OVERRIDE SICUREZZA ORIGINE DATI
		// @todo Implementare verifica parametro aggiuntivo
		
		$actionContext->setType($actionRow["TIPO"]);
		$actionContext->setModule($actionRow["MODULO"]);
		$actionContext->setModel($actionRow["MODEL"]);
		$actionContext->setView($actionRow["VIEW"]);
		// Gestione descrizione in lingua
		$des_azione = $actionRow['DESCRIZIONE'];
		if (isset($actionRow['STRING']) && $actionRow['STRING']!="") {
			$des_azione = $actionRow['STRING'];
		}
		$actionContext->setLabel($des_azione);
		
		$settings['wiki_url'] = str_replace(" ","_",$actionRow["DESCRIZIONE"]);
		
	}else if (!isset($_GET["t"]) || trim($_GET['t']) == ""){
		// Azione di default se presente
		if (isset($_SESSION["URL_FROM"]) && trim($_SESSION["URL_FROM"]) != ""){
			// REDIRECT DEFAULT ACTION
			goHeader($_SESSION["URL_FROM"]);
			//header("Location: ".$_SESSION["URL_FROM"]);
			unset($_SESSION["URL_FROM"]);
			exit();
		}else if (isset($_SESSION['DEFAULT_ACTION']) && trim($_SESSION['DEFAULT_ACTION']) != ""){
			// REDIRECT DEFAULT ACTION
			goHeader($appBase."index.php?t=".$_SESSION['DEFAULT_ACTION'].'&LEFT_MENU_STATUS=close');
			//header("Location: ".$appBase."index.php?t=".$_SESSION['DEFAULT_ACTION'].'&LEFT_MENU_STATUS=close');
			exit();
		}
	}
	// Developer Console
	
	if (isDeveloper()) {
		// Pulizia Array Developer Runtime Field
		$skip_action = array("DEVELOPER_DOC", "ROUTINE_VIEWER");
		$skip_list = array("ROUTINE_VIEWER_LIST");
		if((isset($_GET['t']) && !in_array($_GET['t'], $skip_action)) || (isset($_GET['IDLIST']) && !in_array($_GET['IDLIST'], $skip_list))) {
			$_SESSION['DEVELOPER_RUNTIME_FIELD']=array();
		}
	}	
	// GATEWAY
	if (isset($actionRow["GATEWAY"]) && $actionRow["GATEWAY"] != "" && $actionContext->getGateway() != ""){
		require_once p13n($actionContext->getGatewayUrl($actionRow["GATEWAY"]));
	}
	
	// ACTION AND FORM
	$currentAction = $actionContext->getAction();
	$currentForm   = $actionContext->getForm();

	// CLEAN ID
	if (isset($_GET['cleanID']) && $_GET['cleanID']!="") {
		wi400Detail::cleanSession($_GET['cleanID']);
	}
	// MODEL
	if ($actionContext->getModel() != ""){
		require_once p13n($actionContext->getModelUrl());

		// Pulizia model context
		$modelContext = new wi400ValuesContainer();
		
		// Gestione azioni batch
		if ($actionContext->getType() == "B" && isset($_POST['CURRENT_ACTION']) && $_POST['CURRENT_ACTION'] != ""){
			$nextUrl =  $appBase."index.php?t=".$_POST['CURRENT_ACTION'];
			
			if (isset($_REQUEST['DECORATION'])){
				$nextUrl.= "&DECORATION=".$_REQUEST['DECORATION'];
			}
			
			if(isset($_POST['CURRENT_FORM']) && $_POST['CURRENT_FORM'] != "" && $_POST['CURRENT_FORM'] != "DEFAULT"){
				$nextUrl.= "&f=".$_POST['CURRENT_FORM'];
			}
			$gateway ="";
			if (isset($_GET["g"]) && trim($_GET["g"]) != ""){
				$gateway = "&g=".trim($_GET["g"]);
			}
//			header("Location:".$nextUrl);
			goHeader($nextUrl.$gateway);
			exit();
		}
		// AFTER MODEL
		// Se sto effettuando un SSO
		if ($_GET['t']=="CHECK_LOGIN" && isset($_SESSION['user'])) {
			$_SESSION["LOGOUT_ACTION"] = "LOGOUT";
			$nextUrl =  $appBase."index.php?t=LOGIN_PROFILE";
			goHeader($nextUrl);
			//header("Location:".$nextUrl);
			exit();
		}
		$actionContext->getNext($messageContext->getSeverity(),$actionContext->getAction(),$actionContext->getForm());
	}
	// Verifico se l'azione ha qualche Heade Pae particolare
	if (isset($actionRow['PACKAGE']) && $actionRow['PACKAGE']!=""){
		$pageDefaultDecoration=$actionRow['PACKAGE']."_";
	}
	
	// header PAGE
	require_once $doc_root.$appBase."themes/common/".strtolower($pageDefaultDecoration)."header.php";
	// ALBERTO
	if(isset($settings['window_cookie']) && $settings['window_cookie']) {
			
		if(isset($_REQUEST['WI400_IS_WINDOW'])) {
			$chiave = wi400GetWindowSizeKeyByRequest($_REQUEST);
			//echo "chiave__".$chiave."__<br>";
			if($chiave) $_REQUEST['WINDOW_SIZE_KEY'] = $chiave;
		}
		
		if((isset($_REQUEST['WINDOW_SIZE_KEY']) && $_REQUEST['WINDOW_SIZE_KEY'] && isset($_REQUEST['t']))) {
			if(!isset($_REQUEST['f'])) $_REQUEST['f'] = 'DEFAULT';
			
			$size = wi400ExistWindowSizeCookie($_REQUEST['t'], $_REQUEST['f'], $_REQUEST['WINDOW_SIZE_KEY']);
			if($size && (!isset($_REQUEST['DECORATION']) || $_REQUEST['DECORATION'] != 'clean')) {
				//echo "<script>setWindowSizeCookie('$size')</script>";
				//echo "ooooooooooooooooooooooooooo trovato da db<br>";
				//$size = explode('|', $size);
				echo "<script>
						var size = wi400WindowAdapterResolution('{$size}');

						wi400top.wi400ResizeWindow(null, null, null, size[0], size[1]);
					</script>";
			}else {
				//echo 'iiiiiiiiiiiiiiiiiiiii niente da db<br>';
				//echo "<script>wi400top.wi400ResizeWindow(null, null, null, 1000, 800)</script>";
			}
		}
		
		if(isset($_COOKIE['wi400WindowSizeSave'])) {
			$wi400WindowSizeSave = json_decode($_COOKIE['wi400WindowSizeSave'], true);
			
			if($wi400WindowSizeSave) {
				$error = wi400SaveWindowSize($wi400WindowSizeSave);
				if(!$error) {
					setcookie('wi400WindowSizeSave');
				}
			}
		}
		//showArray($_COOKIE);
		//$a = json_decode($_COOKIE[''])
	}
	// LUCA
	if(isset($settings['messages_enable']) && $settings['messages_enable'] && isset($actionRow)) {
		if($actionRow["TIPO"] == "A" ) {
		$announce = new wi400AnnounceMessage();
		$announce->getNewMessageAction($actionRow['AZIONE']);
		$id = $announce->getMessages(array("*ACTION"), array("1"), $actionRow['AZIONE']);
	
		//showArray($id);
		//$id =0;
		$script = "";
		if($id) {
			$script = "openWindow(_APP_BASE + APP_SCRIPT + '?t=ANNOUNCE&f=ACTION&DECORATION=lookUp&AZIONE=".$actionRow['AZIONE']."&' + jQuery('#'+APP_FORM).serialize(), 'buttonAction', '700', '500', true, true, false);";
			?>
				<script>
					<?php echo $script?>
				</script>
		<?	}
			
			
		}
		}
		// END LUCA
	// VIEW
	if ($actionContext->getView() != "") require_once p13n($actionContext->getViewUrl());
	// POST ACTION
	if (isset($_GET['postAction']) && $_GET['postAction']!="" && !isset($_GET['nopost'])) {
		$pa = unserialize(urldecode($_GET['postAction']));
		unset($_GET['postAction']);
		if ($pa->getTarget()=="WINDOW") {
			$script ="openWindow(_APP_BASE + APP_SCRIPT + '?t=".$pa->getAction()."&f=".$pa->getForm()."&DECORATION=lookUp&' + jQuery('#'+APP_FORM).serialize(), 'buttonAction', '".$pa->getWidth()."', '".$pa->getHeight()."', true, true, false);";
		} else {
			$script ="doSubmit(".$pa->getAction().", ".$pa->getForm().")";
		}
	?>
	<script>
		<?php echo $script?>
	</script>
	<?php 
	}
	// footer PAGE
	require_once $doc_root.$appBase."themes/common/".strtolower($pageDefaultDecoration)."footer.php";
	//echo '<script>setTimeout(function() {jQuery.blockUI({ message: "<h1><img src=\"themes/common/images/busy.gif\" /> Prego attendere ...</h1>" });}, 3000);</script>';