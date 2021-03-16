<?php
$azione = $actionContext->getAction();
require_once "telnet_5250_class.php";
require_once "telnet_5250_common.php";
// Apro la connessione uguale per tutti
if($actionContext->getGateway() == "ANAGRAFICA_ARTICOLI") {
	$Sessione5250 = new wi400AS400Session(wi400AS400Func::getSessionId());
	$Sessione5250->setUser("VEGANEW");
	$Sessione5250->setPassword("VEGANEW");
	$Sessione5250->setInitPgm("ZGEBIDC");
	$Sessione5250->setInitLib("OPENTERM");
	// Parametri per passaggio dati
	$parm = new wi400AS400Param($Sessione5250->getId(), "@A02", "", $_SESSION['sysinfname']);
	$parm->write();
	$Sessione5250->setApplicationParm($parm->getId());
	$articolo = $_GET['ARTICOLO'];
	// Macro da eseguire dopo che succede qualche cosa
	$script ="
	if (\$display->findText('assegnata ad un altro lavoro')==True) {
			\$this->writeTerminalData('0101F1');
			\$this->dialogWith5250(\$display);
    }
	// Inserisco i campi
	\$display->fillText('".$articolo."', 3,25);
	\$display->fillText('070618  ', 3,73);
	\$dati =\$display->getModifiedString();
	\$this->writeTerminalData('0101F1'.\$dati);
	\$this->dialogWith5250(\$display);
	";
	$Sessione5250->setMacroScript($script);
	
}
if($actionContext->getGateway() == "ECON_SALDI") {
	$Sessione5250 = new wi400AS400Session(wi400AS400Func::getSessionId());
	$Sessione5250->setUser($_SESSION['user']);
	$Sessione5250->setPassword($_SESSION['user']);
	$Sessione5250->setInitPgm("ZGEBIDC");
	$Sessione5250->setInitLib("OPENTERM");
	// Parametri per passaggio dati
	if (isset($_REQUEST["DETAIL_KEY"])){
		$detailKey = explode("|", $_REQUEST['DETAIL_KEY']);
	}
	$stringa = str_pad($_REQUEST["AZIENDA"], 3,"", STR_PAD_LEFT).str_pad($desAzienda, 35," ", STR_PAD_LEFT).str_pad($detailKey[1], 3, STR_PAD_LEFT).
	str_pad($detailKey[0], 8, "0",STR_PAD_LEFT)."2";
	
	$parm = new wi400AS400Param($Sessione5250->getId(), "", "§IGEINCL", $_SESSION['sysinfname'], "S", "V", $stringa);
	$parm->write();
	$Sessione5250->setApplicationParm($parm->getId());
	$articolo = $_GET['ARTICOLO'];
	// Macro da eseguire dopo che succede qualche cosa
	$script ="
	if (\$display->findText('assegnata ad un altro lavoro')==True) {
			\$this->writeTerminalData('0101F1');
			\$this->dialogWith5250(\$display);
    }
	// Inserisco i campi
	\$display->fillText('".$articolo."', 3,25);
	\$display->fillText('070618  ', 3,73);
	\$dati =\$display->getModifiedString();
	\$this->writeTerminalData('0101F1'.\$dati);
	\$this->dialogWith5250(\$display);
	";
	$Sessione5250->setMacroScript($script);
	
}
if($actionContext->getGateway() == "FROM_MENU") {
	
	//showArray($_REQUEST);
	$azione5250 = $_REQUEST['AZIONE_5250'];
	
	$dati_azi5250 = rtvAzione5250($azione5250);
	
	//showArray($dati_azi5250);
	
	$Sessione5250 = new wi400AS400Session(wi400AS400Func::getSessionId());
	$Sessione5250->setUser($_SESSION['user']);
	$Sessione5250->setPassword($_SESSION['user']);
	$Sessione5250->setInitPgm("ZGEBIDC");
	$Sessione5250->setInitLib("OPENTERM");
	// Scrivo i parametri legati all'azione
	$parm = new wi400AS400Param($Sessione5250->getId(), $dati_azi5250['AZIONE'], "", $_SESSION['sysinfname']);
	$parm->write();
	$Sessione5250->setApplicationParm($parm->getId());
}
	
	