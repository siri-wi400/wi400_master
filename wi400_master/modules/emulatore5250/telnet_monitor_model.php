<?php 
	$azione = $actionContext->getAction();
	$stato_subsystem="";
	$image_subsystem="themes/common/images/offline.png";
	$versione = data_area_read("OT_VERSION", 1, 10);
	$dati = parse_ini_file("ot_version.ini");
	// Recupero lo stato del sottosistema
	// Routine RTLPAR
	$rtlpar = new wi400Routine('ZCHKSBSA', $connzend);
	$rtlpar->load_description();
	$rtlpar->prepare();
	$rtlpar->set('SBSNAME', "OPENTERM");
	$rtlpar->set('SBSLIB', "OPENTERM");
	$rtlpar->call();
	$stato_subsystem=$rtlpar->get("SBSSTATUS");
	if ($stato_subsystem=="*ACTIVE") {
		$image_subsystem="themes/common/images/online.png";
	}
	if ($actionContext->getForm() == "START_SUBSYSTEM"){
		executeCommand("STRSBS SBSD(OPENTERM/OPENTERM)");
		sleep(5);
		$actionContext->gotoAction($azione, "DEFAULT", "", True);
	} elseif ($actionContext->getForm() == "STOP_SUBSYSTEM"){
		executeCommand("ENDSBS SBS(OPENTERM) OPTION(*IMMED)");
		sleep(2);
		// Chiusura con Kill di tutti i processi
		$actionContext->gotoAction($azione, "DEFAULT", "", True);
	} elseif ($actionContext->getForm() == "STOP_TELNET"){
		require_once "telnet_5250_class.php";
		$session_id = $_GET['HANDLE'];
		$Sessione5250 = new wi400AS400Session($session_id);
		$Sessione5250->closeTerminalConnection();
		$actionContext->gotoAction($azione, "DEFAULT", "", True);
	}else if ($actionContext->getForm() == "PREVIEW_TELNET") {
		require_once "telnet_5250_class.php";
		require_once 'telnet_5250_common.php';
		
		$id = $_REQUEST['HANDLE'];
		$timestamp = $_REQUEST['TIMESTAMP'];
		// Carico gli ultimi n record dal file di log per mostrare la PREVIEW
		$sql = "SELECT hex(logsdt) DATI, A.* FROM ZOPNLOGS A WHERE LOGSID='$id' and LOGSTM<='$timestamp' ORDER BY LOGSTM DESC
				FETCH FIRST 10 ROWS ONLY";
		$result = $db->query($sql);
		
		//echo $sql."__<br/>";
		$i=0;
		$dati = array();
		while ($row = $db->fetch_array($result)) {
			$dati[] = $row;
		}
		
		$dati = array_reverse($dati);
		
		//showArray($dati);
		
		$righe = array();
		foreach($dati as $key => $row) {
			//echo $row['LOGSTM']."__<br/>";
			$righe[] = "3".substr($row['DATI'], 0, $row['LOGLEN']*2);
		}
		
		//showArray($righe);
		
		$dati = implode('!', $righe);
		
		//echo $dati."___<br/>";
	}
