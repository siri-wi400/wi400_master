<?php
require_once 'macro.php';
class rcf006pj extends Macro {

	public function generate(){
		
		global $moduli_path, $routine_path, $db, $appBase;
		$param = $this->getParameters();
		$detailKey = array();
		if (isset($param["DETAIL_KEY"])){
			$detailKey = explode("|", $param['DETAIL_KEY']);
		}
		//print_r($detailKey);
		//die();
		// File Keyboard (default)
		//$fileKbd = $moduli_path.'/macro/template/AS400.KMP';
		// File Keyboard (default)
		$fileKbd = $this->getKeyboardFile();
		if ($fileKbd=="") {
			$fileKbd = $moduli_path.'/macro/template/AS400.KMP';
		} 
		// File ICO (Default)
		$fileIco = $moduli_path.'/macro/template/favicon.ico'; 
		// Creazione WS
		$file = $moduli_path.'/macro/template/template.ws';
        $command = file_get_contents($file);
		//$mark = array('##HOST##','##PATH##');
		//$command = str_replace($mark, array($_SERVER['SERVER_NAME'], $this->getPath()), $command);
		$mark = array('##HOST##', "##ID##", '##MACRO##');
		$idWs = "WI".substr($this->getId(),3,7)."*";
		$idSessione = "PO5".substr($this->getId(),3,7);
		$command = str_replace($mark, array(getServerAddress(), $idWs, $idSessione), $command);
        // Scrittura file WS temporaneo
		$fileWs = wi400File::getSessionFile(session_id(), "open_5250.ws");
		file_put_contents($fileWs, $command);
		// Creazione file MACRO
		$fileName = $moduli_path.'/macro/template/template.mac';
        $command = file_get_contents($fileName);
  		// Macro
		$macro = 'autECLSession.autECLPS.SendKeys "'.$detailKey[0].'"
				   autECLSession.autECLOIA.WaitForInputReady
				   autECLSession.autECLPS.SendKeys "[tab]"
				   autECLSession.autECLOIA.WaitForInputReady
				   autECLSession.autECLPS.SendKeys "'.date("dmy").'  "
				   autECLSession.autECLOIA.WaitForInputReady
				   autECLSession.autECLPS.SendKeys "[tab]"
				   autECLSession.autECLOIA.WaitForInputReady
				   autECLSession.autECLPS.SendKeys "[enter]"
				   autECLSession.autECLOIA.WaitForInputReady';      
		$mark = array('##USER##', '##PASSWORD##', '##ID##', "##SCRIPT##");
        $macro = "";		
		$command = str_replace($mark, array($_SESSION['user'], getUserPassword(), $this->getId(), $macro), $command);
		// Scrittura file WS temporaneo
		$fileMac = wi400File::getSessionFile(session_id(), $idSessione.".mac");
		file_put_contents($fileMac, $command);
		// Scrittura record di innesco
		$field = array("IDOPEN","AZIONE","PGM", "ONLYJBU", "SYSINF","IDFL01","IDFL02","IDFL03","IDFL04","IDFL05","STATO","OKPJBU");		
		$stmtInsert = $db->prepare("INSERT", "ZID5250O", null, $field);
		$stringa=" ";
		$stringa = str_pad($stringa, 40," ", STR_PAD_LEFT).$detailKey[0]."    ".str_pad(substr($detailKey[1],0,2), 2, " ",STR_PAD_LEFT).$detailKey[2].
		str_pad(substr($detailKey[3],7,7), 138, " ", STR_PAD_LEFT);
		$campi = array($this->getId(), "", "CCF006PJWI", "S", $_SESSION['sysinfname'], "", "", "", "", "", 'V',$stringa);
		
		$db->execute($stmtInsert, $campi);
		// Compressione dei file
		require_once $routine_path."/classi/wi400InvioEmail.cls.php";
		$zipName = wi400File::getUserFile("tmp", $this->getId().".zip");
		wi400InvioEmail::compress(array($fileMac, $fileWs, $fileKbd, $fileIco), $zipName);
		//parent::generate();
		unlink($fileWs);
		unlink($fileMac);
		
		return $this->getId().".zip";
	}
	
}	


?>