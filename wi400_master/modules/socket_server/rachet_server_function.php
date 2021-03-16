<?php
/**
 * Funzione di scrittura del log dei messaggi arrivati
 * 
 * @param unknown $msg
 * @param string $fase: * Inserimento, 1 Inoltro, 2 Spedizione, 3 Ricevuto, 4 Risposta
 */
function log_message($msg, $fase="") {
	global $db;
	static $select_stmt;
	if (!isset($select_stmt)) {
		$sql = "SELECT * FROM ZWSIDMSG WHERE WSID=?";
	    $select_stmt = $db->singlePrepare($sql);			
	}
	// Controllo se il messaggio esiste già per vedere se fare l'insert o l'update delle informazioni
	$found = False;
	$result = $db->execute($select_stmt, array($msg->id));
	if ($row = $db->fetch_array($select_stmt)) {
		$found = True;
	}
	// Se non c'è inserisco tutti gli identificativi
	if ($found==False) {
		insert_message($msg, $fase);
	} 
	// Aggiornamento .. devo reperire la fase
	else {
		update_message($msg, $fase);
	}
}
function insert_message($msg, $fase) {
	global $db;
	static $insert_stmt, $fieldDoc;
	if (!isset($insert_stmt)) {
		$fieldDoc = getDs("ZWSIDMSG");
		$insert_stmt = $db->prepare("INSERT", "ZWSIDMSG", null, array_keys($fieldDoc));
		$select_stmt = $db->singlePrepare($sql);
	}
	if (!isset($stmtDoc)) {
		// INSERT DOCUMENTI
		$stmtDoc = $db->prepare("INSERT", "SIR_WEBS", null, array_keys($fieldDoc));
	}
	$user="";
	if ($user=="") {
		$user = $_SESSION['user'];
	}
	$timeStamp = getDb2Timestamp();
	$fieldDoc['WSID']=$msg->id;
	$fieldDoc['WSUSER']=$user;
	$fieldDoc['WSSEND']=$msg->sender;
	$fieldDoc['WSSTAT']="*";
	$fieldDoc['WSTYPE']=$msg->action;
	$fieldDoc['WSIPAD']=$msg->ip;
	$fieldDoc['WSIDEV']=$msg->device;
	$fieldDoc['WSARGO']=$msg->argomento;
	$fieldDoc['WSAPIP']=$msg->user_code;
	$fieldDoc['TMSINS']=$timestamp;
	$fieldDoc['TMSINO']=$timestamp;
	$fieldDoc['TMSSPE']=$timestamp;
	$fieldDoc['TMSRPL']=$timestamp;
	// Scrittura del RECORD
	$result = $db->execute($stmtDoc, $fieldDoc);
	// Scrittura del dettaglio delle operazioni se presente
	//insert_operazioni($msg);
	// Scrittura dell'intero pacchetto del messaggio
	//insert_contenuto($msg);
	return $result;
}
function update_message_inoltro($msg) {
	global $db;
	static $update_stmt, $fieldDoc;

	if (!isset($update_stmt)) {
		$update_stmt = $db->prepareStatement("UPDATE ZWSIDMSG SET TMSINO=?, TMSSTA=? WHERE WSID=?");
	}
	$res_updt = $db->execute($stmt_updt, array("TMSINO"=> getDb2Timestamp(), "TMSSTA"=>"1", "WSID"=>$msg->id));
	return $result;
}
/**
 * @desc: Generazioni di un TOKEN da utilizzare per il collegamento e le successive richieste
 */


function webs_create_token($max_life="*IMMED", $user="", $tipo="TEMP", $id="", $stato="", $note="") {
	global $db;
	static $stmtDoc;
	
	if ($id=="") {
		$id = uniqid('WEBS_', true);
	}
	if ($stato=="") {
		$stato = "1";
	}
	$fieldDoc = getDs("SIR_WEBS");
	if (!isset($stmtDoc)) {
		// INSERT DOCUMENTI
		$stmtDoc = $db->prepare("INSERT", "SIR_WEBS", null, array_keys($fieldDoc));
	}
	if ($user=="") {
		$user = $_SESSION['user'];
	}
	$timeStamp = getDb2Timestamp();
 	$fieldDoc['WEBID']=$id;
	$fieldDoc['WEBUSR']=$user;
	$fieldDoc['WEBTIM']=$timeStamp;
	$fieldDoc['WEBSTA']=$stato;
	$fieldDoc['WEBTYP']=$tipo;
	$fieldDoc['WEBLIF']=$max_life;
	$fieldDoc['WEBTIU']=time();
	$fieldDoc['WEBNOT']=$note;
	// Scrittura del RECORD
	$result = $db->execute($stmtDoc, $fieldDoc);
	return $id;
}
function webs_check_token($id) {
	global $db;
	static $stmt, $stmtDel;
	if (!$stmt) {
		$sql = "SELECT * FROM SIR_WEBS WHERE WEBID=?";
		$stmt = $db->singlePrepare($sql);
		$sql = "DELETE FROM SIR_WEBS WHERE WEBID=?";
		$stmtDel = $db->prepareStatement($sql);
	}
	
	$result = $db->execute($stmt, array($id));	
	echo "\r\nSearchig ...".$id;
	if ($result) {
		$row = $db->fetch_array($stmt);
		if (!$row) {
			return array("RESULT"=>False, "MSG"=>"TOKEN: Dati non trovati");
		}
		echo "\r\nFound ...".$id;
		echo var_dump($row);
		// Se la vita è immediata cancello il file e segnalo come TOKEN Valido
		if ($row['WEBSTA']=="D") {
			return array("RESULT"=>False, "MSG"=>"TOKEN: Disabilitato", "STATO"=>"*DISABLED");
		}
		// Se la vita è immediata cancello il file e segnalo come TOKEN Valido
		if ($row['WEBLIF']=="*IMMED") {
			$result = $db->execute($stmtDel, array($id));	
			return array("RESULT"=>True);
		}
		// Altrimenti verifico se il TOKEN è scaduto
		$now = time();
		if ($row['WEBLIF']!="*NOSCAD") {
			if ($now > $row['WEBTIU']+$row['WEBLIF']) {
				$result = $db->execute($stmtDel, array($id));	
				return array("RESULT"=>False, "MSG"=>"TOKEN: Scaduto");
			}
		}
		// TOKEN VALIDO E ANCORA ATTIVO
		return array("RESULT"=>True);
	} else {
		return array("RESULT"=>False, "MSG"=>"TOKEN: Non Trovato!");
	}
}
function push_create_token() {
	$id = uniqid('PUSH_', true);
	return $id;
}
function push_setProcessData($idProcess, $data, $key="") {
	$file = wi400File::getCommonFile("process", $id);
	$dati = array("PROCESSID"=>$idProcess, "MSG"=>$data);
	$handle = fopen($file, "w");
	fwrite($handle, json_encode($dati));
	fclose($handle);
	return $id;
}
function push_getProcessData($idProcess, $key="") {
	
}
function webs_formatReply($sender, $action, $result, $message="") {
	$reply= base64_encode(json_encode(array("sender"=>$sender, "action"=>$action, "result"=>array("code"=>$result, "messaggio"=>$message))));
	return $reply;
}
function webs_upgradeReply() {
	$msg = array("operazione"=>"DOWNLOAD_LINK","dati"=>"http://10.0.40.1:89/software/tutto.zip","extra"=>"");
	$reply= base64_encode(json_encode(array("sender"=>"CONSOLE", "action"=>"UPGRADE","msg"=> array("0"=>$msg), "result"=>array("code"=>"OK", "message"=>""))));
	return $reply;
}
function insertID($id, $user="", $sender="") {
	global $db;
	$fieldArt = getDS($artFile);
	$stmtArt = $db->prepare("INSERT", $artFile, null, array_keys($fieldArt));
	return True;
}
function getCounterMessage($file="/www/counter_message.txt"){
	return uniqid("i_", True);
	/*$f = fopen($file, "w+");
	// We get the exclusive lock
	if (flock($f, LOCK_EX)) {
		$counter = (int) fgets ($f); // Gets the value of the counter
		//rewind($f); // Moves pointer to the beginning
		ftruncate($f, 0);     
		$new = $counter + 1;
		fwrite($f, $new); // Increments the variable and overwrites it
		fflush($f);
		flock($f, LOCK_UN); // Unlocks the file for other uses
		fclose($f); // Closes the file
		return $counter;
	}*/
}