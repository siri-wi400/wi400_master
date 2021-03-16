<?php
global $INTERNALKEY, $CONTROLKEY, $jobname;
$key = session_id();
$jobname = $settings['jobname'];
if (!isset($key) || $key == '') {
	$key = uniqid("WS_");
}
if (!isset($_SESSION['user']) && !isset($wi400Batch) && !isset($wi400_isWS)) {
	$key = "LOGIN_01";
	$jobname = "LOGIN";
}
// Default collegamento XMLSERVICE
if (isset($settings['xmlservice'])){
	if (!isset($settings['xmlservice_jobd_lib']) || $settings['xmlservice_jobd_lib']=="") {
		$settings['xmlservice_jobd_lib']="ZENDSVR";
		if (isset($settings['zend_server_version'])) {
			switch ($settings['zend_server_version']) {
				case '6':
					$settings['xmlservice_jobd_lib']="ZENDSVR6";
					break;
				case '9':
					$settings['xmlservice_jobd_lib']="ZENDPHP7";
					break;
				default:
					$settings['xmlservice_jobd_lib']="ZENDSVR";
					break;		
			}
		}
	}
	if (!isset($settings['xmlservice_jobd']) || $settings['xmlservice_jobd']=="") {
		$settings['xmlservice_jobd']="ZSVR_JOBD";
	}
}
if (isset($settings['xmlservice'])){
	if (!isset($settings['xmlservice_jobd_lib_ws']) || $settings['xmlservice_jobd_lib_ws']=="") {
		$settings['xmlservice_jobd_lib_ws']=$settings['xmlservice_jobd_lib'];
	}
	if (!isset($settings['xmlservice_jobd_ws']) || $settings['xmlservice_jobd_ws']=="") {
		$settings['xmlservice_jobd_ws']=$settings['xmlservice_jobd'];
	}
}
// Parametri speciali jobname
if ($settings['jobname']=="*USER" && isset($_SESSION['user'])) {
	$jobname = substr($_SESSION['user'],0,10);
	$jobname = preg_replace("/[^A-Za-z0-9 ]/", '', $jobname);
	$number = substr($jobname,0,1);
	if (is_numeric($number)) {
		$jobname= "U".substr($_SESSION['user'],0,9);
	}
	if ($jobname=="") {
		$jobname = "PHPSIRI";
	}
}
// Casi particolari 
if (isset($_GET['t']) && $_GET['t']=="AJAX_PING_NET") {
	//$key = get_first_free_key("AJAX_PING_NET");
	$key = "AJAX_01";
	$jobname=substr("AJAX_PING_NET", 0 , 10);
} 
// @todo se sono su LINUX o WINDOW non devo utilizzare questo percorso ma si arrangia l'AS400
//$INTERNALKEY = '/tmp/'.$key;
$INTERNALKEY = $settings['sess_path'].$key;
//$CONTROLKEY = ' *wait(6000) *call(6000) *idle(600) *sbmjob(ZENDSVR/ZSVR_JOBD/'.$settings['jobname'].")";
//$CONTROLKEY = ' *idle('.$settings['timeout'].') *wait(6000) *call(6000) *sbmjob('.$settings['xmlservice_jobd_lib'].'/'.$settings['xmlservice_jobd'].'/'.$settings['jobname'].")";
$CONTROLKEY = ' *idle('.$settings['timeout'].') *wait(6000) *call(6000) *sbmjob('.$settings['xmlservice_jobd_lib'].'/'.$settings['xmlservice_jobd'].'/'.$jobname.")";
if (isset($settings['xmlservice_cdata']) && $settings['xmlservice_cdata']==True) {
	$CONTROLKEY .= " *cdata ".$CONTROLKEY;
}
if (isset($WI400_PRAGMA['XMLSERVICE_HEX_CONVERT']) ) {
	$CONTROLKEY .= " *hex({$WI400_PRAGMA['XMLSERVICE_HEX_CONVERT']}) ";
}
/*if (!isset($countXML)) $countXML=0;
$countXML=$countXML+2;
if ($countXML>1) {
	$CONTROLKEY="*debugproc";
}*/
// VECCHI GESTIONE .. MA FUNZIONE ANCORA ...
if (isset($_SESSION['XMLSERVICE_DEBUG_ACTIVE']) && $_SESSION['XMLSERVICE_DEBUG_ACTIVE']==True && $_GET['t']!="DEBUG") {
	$CONTROLKEY .=" *debugproc ";
	$_SESSION['XMLSERVICE_DEBUG_ACTIVE']=False;
}
// Ebug NEXT CALL SOLO SE AZIONE NON DEVELOPER
if (isset($_SESSION['DEVELOPER_DEBUG_NEXT_CALL']) && $_SESSION['DEVELOPER_DEBUG_NEXT_CALL']==True) {
	$skip_action = array("DEVELOPER_DOC", "ROUTINE_VIEWER");
	$skip_list = array("ROUTINE_VIEWER_LIST");
	if(!in_array($_GET['t'], $skip_action) && !in_array($_GET['IDLIST'], $skip_list)) {
		$CONTROLKEY .=" *debugproc ";
		unset($_SESSION['DEVELOPER_DEBUG_NEXT_CALL']);
	}
}
//echo "<br>COSA HO:".var_dump($_SESSION['DEVELOPER_DEBUG_NEXT_CALL']);
// Log delle chiamate
if (isset($_SESSION['DEVELOPER_LOG_XMLSERVICE']) && $_SESSION['DEVELOPER_LOG_XMLSERVICE']==True) {
	$CONTROLKEY .=" *log(".session_id().getPerformanceTime().") ";
}
//if (isset($settings['base_asp']) && $settings['base_asp']!="" && $settings['base_asp']!="*ARCH") {
//	$CONTROLKEY = ' *idle(600) *wait(6000) *call(6000) *sbmjob(ZENDSVR/ZSVR_JOBD/'.$settings['jobname'].'/'.$settings//['base_asp'].')';
//}
//$CONTROLKEY = '*here';
//define('CONTROLKEY', '*fly');
define('XMLHEADER', "<?xml version='1.0'?>");
if (!isset($settings['i5_toolkit']) && !defined("I5_IN")) {
	define ("I5_IN", 1);
	define ("I5_OUT", 2);
	define ("I5_INOUT", 3);
	define ("I5_TYPE_CHAR", 0);
	define('I5_TYPE_LONG', 2);
	define('I5_TYPE_INT', 2);
	define('I5_TYPE_FLOAT', 3);
	define('I5_TYPE_DOUBLE', 4);
	define('I5_TYPE_BIN', 5);
	define ("I5_TYPE_PACKED", 6);
	define ('I5_TYPE_BYTE', 5);
	define ("I5_TYPE_ZONED", 7);
	define('I5_TYPE_DATE', 8);
	define('I5_TYPE_TIME', 9);
	define('I5_TYPE_TIMESTP', 10);
	define ("I5_TYPE_STRUCT", 256);
	define('I5_NAME', 'name');
	define('I5_INITSIZE', 'initSize');
	define('I5_DESCRIPTION', 'description');
	define('I5_INIT_VALUE', 'initvalue');
	define('I5_AUTHORITY', 'authority');
	define('I5_LIBNAME', 'libName');
}
/**
 * @desc  Richiamo di un programma, wrapper delle vecchie funzioni i5
 * @param string $cmd Comando da eseguire
 * @param array $arrayInput Array con i parametri di input
 * @param array $arrayOutput Array con i parametri da valorizzare come variabili
 */
function callXMLCmd($cmd, $arrayInput=null, $arrayOutput=null) {
	global $messageContext;
	
	$xml = XMLHEADER;
	$xml.="<script>";
	$comando = $cmd;
	$tagcmd = '<cmd>';
	$returnVar = False;
	// Parametri di input
	if (isset($arrayInput) && count($arrayInput)>0) {
		foreach ($arrayInput as $key=>$value) {
			$comando .= " $key($value)";
		}
		$tagcmd = "<cmd>";
	}
	// Parametri di ritorno e trasformo tutte le kye in upper altrimenti si incasina
	$arrayNew = array();
	if (isset($arrayOutput) && count($arrayOutput)>0) {
		foreach ($arrayOutput as $key=>$value) {
			$comando .= " ".strtoupper($key)."(?)";
			$arrayNew[strtoupper($key)]=$value;
		}
		$tagcmd = "<cmd exec='rexx'>";
		$returnVar = True;
	}
	if ($tagcmd =='') {
		$tagcmd = '<cmd>';
	}
	$arrayOutput = $arrayNew;
	$xml .=$tagcmd.strtoupper($comando)."</cmd></script>";
	$OutputXML = callXMLService($xml);
	// Controllo il risultato
	if (!$OutputXML) {
		if (isset($messageContext)) {
			$messageContext->addMessage("LOG","Errore esecuzione comando ".$comando);
		}		
		return false;
	}

	// Verifico se contiene il messaggio di SUCCESS else .. errore
	if (!strpos($OutputXML, "+++ success",0)) {
		if (isset($messageContext)) {
			$messageContext->addMessage("LOG","Errore esecuzione comando ".$comando);
		}		
		return false;		
	}
	// Se ci sono parametri di ritorno li carico come global parsando i dati
	if ($returnVar) {
	  $dom = new DomDocument('1.0');
      $dom->loadXML($OutputXML);
      $params = $dom->getElementsByTagName('data');
      $i=0;
      foreach ($params as $p) {
      	    if (isset($arrayOutput[strtoupper($params->item($i)->getAttribute('desc'))])) {
      	    	$myVar  = $arrayOutput[strtoupper($params->item($i)->getAttribute('desc'))];
      	    	global ${$myVar};
      	    	${$myVar} = $params->item($i)->nodeValue;
      	    }
        	$i++;
      }	
	}

	return true;
}
function callXMLService($XInputXML, $XInternalKey=null, $XControlKey=null, $stmt=null) {
	global $db, $INTERNALKEY, $CONTROLKEY, $settings, $jobname, $InputXML, $OutputXML, $InternalKey, $ControlKey;
	static $loop_call, $stmt;
	static $libraryadd=False;
	// @todo Sistemare i casi di doppia chiamata!!!
	if (!$stmt) {
		$stmt = $db->getCallPGM();
	}
	if (!isset($stmt) || (isset($settings['xmlservice_init_each_prepare']) && $settings['xmlservice_init_each_prepare']==True)) {
		$stmt = $db->inzCallPGM();
	}
	if (!isset($XInternalKey)) {
		$XInternalKey = $INTERNALKEY;
	}
	if (!isset($XControlKey)) {
		$XControlKey = $CONTROLKEY;
	}
	// APPEND SBMJOB
	$other_options="";
	if (isset($_SESSION['user']) && $_SESSION['user']!="" && $settings['only_wi400_user']==True && isset($settings['sbmjob_with_user']) && $settings['sbmjob_with_user']==True) {
		$other_options="<?xml version='1.0'?><script><sbmjob>SBMJOB CMD(CALL PGM(".$settings['xmlservice_lib']."/XMLSERVICE) PARM('".$INTERNALKEY."')) JOB($jobname) JOBD(".$settings['xmlservice_jobd_lib']."/".$settings['xmlservice_jobd'].") USER(".$_SESSION['user'].")</sbmjob>";
		/*XInputXML = str_replace("<?xml version='1.0'?><script>", $sbmjob, $XInputXML);*/
	}
	// Aggiunta librerie se non era previsto all'inizio
	if(isset($settings['delay_library_list']) && $libraryadd==False) {
		if ($other_options=="") {
			$other_options   = "<?xml version='1.0'?><script>";
		}
		if (isset($settings['base_asp']) && $settings['base_asp']!="" && $settings['base_asp']!="*ARCH"  && $settings['base_asp']!="*P13N") {
			$other_options   .= "<cmd>SETASPGRP ASPGRP(".$settings['base_asp'].")</cmd>";
		}
		//$InputXML   .= "<cmd>CHGLIBL LIBL(".utf8_encode($this->options['i5_libl']).")</cmd>";
		$other_options   .= "<cmd>CHGLIBL LIBL(".$db->getOptions('i5_libl').")</cmd>";
		$libraryadd=True;
	}
	// Aggiunta altre opzioni di collegamento
	if(isset($settings['xmlservice_init_otions'])) {
		if ($other_options=="") {
			$other_options   = "<?xml version='1.0'?><script>";
		}
		$other_options   .= $settings['xmlservice_init_otions'];
	}
	if ($other_options!="") {
		$XInputXML = str_replace("<?xml version='1.0'?><script>", $other_options, $XInputXML);
	}
	//echo $XInputXML;
	/*echo "<br>CONTROL KEY:".$CONTROLKEY;
	echo "<br>CONTROL KEY pass:".$XControlKey;
	echo "<br>CONTROL KEY pass:".$INTERNALKEY;
	echo "<br>CONTROL KEY pass:".$XInternalKey;*/	
	//$handle = fopen("/www/ciccio", "a+");
	//fwrite($handle, $XInputXML.'\r\n');
	//fclose($handle);
	if (!isset($loop_call)) {
		$loop_call=0;
	}
	$loop_call = $loop_call+1;
	if (isset($settings['db_encode_input']) && $settings['db_encode_input']==False) {
		$InputXML = $XInputXML;
	} else {
		$InputXML = utf8_decode(trim($XInputXML));
	}
	$InternalKey = $XInternalKey;
	$ControlKey = $XControlKey;
	if ($loop_call> 1 && isset($_SESSION['user'])) {
		//$ControlKey = '*ignore';
	}
	$OutputXML="";
	if ($settings['xmlservice_driver']=="DB") {
		$db->bind_param($stmt, 1, "InternalKey", DB2_PARAM_IN );					
		$db->bind_param($stmt, 2, "ControlKey", DB2_PARAM_IN );
		$db->bind_param($stmt, 3, "InputXML", DB2_PARAM_IN );		
		$db->bind_param($stmt, 4, "OutputXML", DB2_PARAM_OUT );
		// Per le prossime chiamate ignoro i Flag per aumentare le perfomance, tanto i lavori sono già aperti
		//$OutputXML = i_xmlservice($InputXML, $ControlKey, $InternalKey);
	        //$CONTROLKEY = '*ignore';	
		$result = db2_execute($stmt);
		if (!$result) {
				return false;
		}
	} else if($settings['xmlservice_driver']=="IBMITOOL") {
		$OutputXML = i_xmlservice($InputXML, $ControlKey, $InternalKey);
	} else if($settings['xmlservice_driver']=="MEMORY") {
		//$OutputXML = i_xmlservice($InputXML, $ControlKey, $InternalKey, 819, 280);
		$OutputXML = i_xmlservice($InputXML, $ControlKey, $InternalKey);
	} else if($settings['xmlservice_driver']=="DB_DIRECT") {
		$OutputXML = db2_ibmi_xmlservice($db->getLink(),$InputXML,65000);
	} else if($settings['xmlservice_driver']=="ODBC") {
			$result = $db->execute($stmt, array($InternalKey, $ControlKey, $InputXML));
			if (!$result) {
				return false;
			}
			$OutputXML;
			$row = $db->fetch_array($stmt);
			$OutputXML.=$row['OUT151'];
	} else if($settings['xmlservice_driver']=="PDO") {
			$result = $stmt->execute(array($InternalKey, $ControlKey, $InputXML));
			if (!$result) {
				return false;
			}
			$OutputXML="";
			$row = $stmt->fetchAll(PDO::FETCH_NUM);
			//print_r($row);
			foreach ($row as $key => $value) {
				$OutputXML.=$value[0];
			}
	}
	if (isset($settings['db_encode_output']) && $settings['db_encode_output']==False) {
		return trim($OutputXML);
	} else {
		return utf8_encode(trim($OutputXML));
	}
	
	//$handle = fopen("/www/ciccio", "a+");
	//fwrite($handle, $OutputXML.'\r\n');
	//fclose($handle);
    return utf8_encode(trim($OutputXML));
}
function xmlservice_logout() {
	global $db, $INTERNALKEY, $CONTROLKEY, $settings, $messageContext;
	global $OutputXML, $InternalKey, $ControlKey, $InputXML;
	$CONTROLKEY="*immed";
	$ControlKey= $CONTROLKEY;
	$InputXML = ' ';
	$InternalKey = $INTERNALKEY;

	if($settings['xmlservice_driver']=="IBMITOOL") {
		$OutputXML = i_xmlservice($InputXML, $ControlKey, $InternalKey);
	} elseif($settings['xmlservice_driver']!="PDO") {
		$OutputXML= '';
		$callPGM = $db->inzCallPGM();
		$db->bind_param ($callPGM, 1, "InternalKey", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 2, "ControlKey", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 3, "InputXML", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 4, "OutputXML", DB2_PARAM_OUT );
		$ret = db2_execute($callPGM);
	} else {
		$callPGM = $db->getCallPGM();
		$result = $callPGM->execute(array($InternalKey, $ControlKey, $InputXML));
		if (!$result) {
			return false;
		}
		//$row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
		/*
		$OutputXML="";
		$row = $stmt->fetchAll(PDO::FETCH_NUM);
		//print_r($row);
		foreach ($row as $key => $value) {
			$OutputXML.=$value[0];
		}*/
	}
}
function callXMLService_tcp($XInputXML, $XInternalKey=null, $XControlKey=null, $stmt=null) {
	global $db, $INTERNALKEY, $CONTROLKEY,$routine_path;
	require_once $routine_path.'/generali/conversion.php';
	static $socket;
	
	if (!isset($socket)) {
	  $address="LOCALHOST";
	  $port=3005;
	  $socket=socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	  $connect = socket_connect($socket, $address, $port);
	}
	if (!isset($XInternalKey)) {
		$XInternalKey = $INTERNALKEY;
	}
	if (!isset($XControlKey)) {
		$XControlKey = $CONTROLKEY;
	}
	//echo $XInputXML;
	$XControlKey = "*idle(600) *call(6000) *wait(6000) *sbmjob(ZENDSVR/ZSVR_JOBD/TCPPHP)";
	//$data = str_pad(trim($XInternalKey), 100, " ").str_pad(trim($XControlKey), 100, " ").trim($XInputXML)."\0";
	$data = '**'.trim($XInternalKey)."<--1-->".trim($XControlKey)."<--2-->".trim($XInputXML)."<--3-->\0";
	//socket_set_nonblock($soc	ket);
	// Test conversione ASCII -> EBCDIC e viceversa lato server
	//$sock_data = socket_write($socket, a2e(trim($data)), strlen($data)); //Send data
	//$sock_data = socket_write($socket, trim($data), strlen($data)); //Send data
	$sock_data = socket_write($socket, trim($data), strlen($data)); //Send data
    //$bytes = socket_recv($socket, $buf, 8000, MSG_WAITALL);
    
    /*$buf="";
    $count = 0;
    $lastChar ="";
    
    do  {
    	$lastChar = socket_read($socket, 1);
    	$buf .=$lastChar;
    	$count++;
    	//echo "<br>Char $lastChar -- $count -- hex :". dechex(ord($lastChar));;
    	//ob_flush();
    	if ($count > 8002) break;
    } while ($lastChar !="\0");*/
    //echo "<br>Byte Count:".strlen($buf);
    //die("$buf");
    //die($buf);
    $buf="";
    do {
    	$read = socket_read($socket, 3000);
    	$buf .=trim($read);
    } while (strlen($read) == 3000);
    //$buf = substr($buf, 0, strpos($buf,"\0"));
    //echo "<br>Buffer Len:".strlen($buf);
    //$buffer = e2a($buf);
    //echo "<br>Passo!!";
    return $buf;
    $buffer = $buf;
    return $buffer;
    $OutputXML = $buffer;
	//$handle = fopen("/www/ciccio", "a+");
	//die("<br>Tick:".strlen($OutputXML));
    //fwrite($handle, $OutputXML);
    //fclose($handle);
    //echo $OutputXML;
    return $OutputXML;
}
/**
 * Esegue una query sulla QTEMP del lavoro
 * 
 * @param $sql    Statement SQL da eseguire
 * @return $result  Stringa Ritorna lo statement 
 */
function queryQTEMP($query, $scrollable=True, $optimize=10) {
	static $i = 0;
	$i++;
	if ($i > 20) {
	    $i=1;
	}
	
	$scroll = "";
	if ($scrollable && (strtoupper(substr($query, 0, 6))=="SELECT")) {
		$scroll = " scrollable='on' cursor='static' ";
	}
	$InputXML   = "<?xml version='1.0'?>
	<script>
	<sql>
	<options options='scrollable' $scroll stmt='stmt$i'/>
	<query options='scrollable' stmt='stmt$i'>$query</query>
	<describe desc='col'/>
	</sql>
	<script>";
	$OutputXML = callXMLService($InputXML);
	//echo $OutputXML;
	// Verifico se contiene il messaggio di SUCCESS else .. errore
	if (!strpos($OutputXML, "+++ success",0)) {
		if (isset($messageContext)) {
			$messageContext->addMessage("LOG","Errore esecuzione comando ".$comando);
		}
		return false;
	}
	if (isset($OutputXML)) {
		$pos = strpos($OutputXML, "stmt=");
		if ($pos!==False) {
			$end = strpos($OutputXML, ">", $pos);
			$statement = substr($OutputXML, $pos +6, $end-($pos+7));
			//echo "<br>query:".$statement;
			return $statement;
		}
	}
	return false;
	
}
/**
 * Free di tutti gli statement SQL

 */
function freeQTEMP() {

	$InputXML   = "<?xml version='1.0'?>
	<script>
	<sql>
	<free/>
	</sql>
	<script>";
	$OutputXML = callXMLService($InputXML);
	//echo $OutputXML;
	// Verifico se contiene il messaggio di SUCCESS else .. errore
	if (!strpos($OutputXML, "+++ success",0)) {
		if (isset($messageContext)) {
			$messageContext->addMessage("LOG","Errore esecuzione comando ".$comando);
		}
		return false;
	}
	if (isset($OutputXML)) {
		$pos = strpos($OutputXML, "stmt=");
		if ($pos!==False) {
			$end = strpos($OutputXML, ">", $pos);
			$statement = substr($OutputXML, $pos +6, $end-($pos+7));
			return $statement;
		}
	}
	return false;

}
function fetchQTEMP($stmt, $row_number = Null, $trim=True) {
	
	$rec ="";
	if ($row_number) {
		$rec = " rec='$row_number'";
	}
	//echo "<br>statement:".$stmt;
	$InputXML = "<?xml version='1.0'?>
		<script>
		<sql>
		<fetch block='1' desc='on' $rec stmt='$stmt'/>
		</sql>
		<script>";
	$OutputXML = callXMLService($InputXML);
	// Verifico se contiene il messaggio di SUCCESS else .. errore
	if (!strpos($OutputXML, "<row>",0)) {
		return false;
	}
	return getRecord($OutputXML);
}
/**
 * Legge record eseguito con una queryQTEMP
 *
 * @param $sql    Statement SQL da eseguire
 * @return $result  Stringa Ritorna lo statement
 */
function getRecord($xml) {
	global $settings;
	$dati = array();
	$start = 0;
	if (isset($settings['xmlservice_cdata']) && $settings['xmlservice_cdata']==True) {
		$xml=str_replace(array("<![CDATA[","]]>"),"",$xml);
	}
	$dove = strpos($xml, "<row>", $start);
	$fineds = strpos($xml, "</row>", $start);
	while ($dove!==False) {
		$dove = strpos($xml, "desc='", $dove+4);
		if ($dove>=$fineds) {
			break;
		}
		if ($dove!==False && $dove<$fineds) {
			$finevar = strpos($xml, "'", $dove+6);
			$finevalue = strpos($xml, "</", $finevar+1);
			$startvalue = strpos($xml, ">", $dove+6);
			$dati[substr($xml, $dove+6, $finevar-($dove+6) )]=
			substr($xml, $startvalue+1 , $finevalue-($startvalue+1));
		}
	}
	return $dati;
}
/**
 * Copia il file da QTEMP in PHPTEMP con un nome univoco per la successiva lettura
 *
 * @param $file    Nome del file creato in QTEMP
 * @return $phptemptable  Tabella temporanea creata in PHPTEMP
 */
function getQTEMPTable($file) {
	
   global $db;
   $phptempfile = $file.session_id();
   $querydrop="drop table phptemp/$phptempfile";
   $querycreate="CREATE table phptemp/$phptempfile as (select * from QTEMP/$file) definition only";
   $queryinsert="insert into phptemp/$phptempfile select * from QTEMP/$file with nc";
   // Drop della tabella creata precedentemente
   $InputXML   = "<?xml version='1.0'?>
   <script>
   <sql>
   <query>$querydrop</query>
   </sql>
   <script>";
   $OutputXML = callXMLService($InputXML);
   // Creazione della tabella
   $InputXML   = "<?xml version='1.0'?>
   <script>
   <sql>
   <query>$querycreate</query>
   </sql>
   <script>";
   $OutputXML = callXMLService($InputXML);
   // Inserimento dei dati
   $InputXML   = "<?xml version='1.0'?>
   <script>
   <sql>
   <query>$queryinsert</query>
   </sql>
   <script>";
   $OutputXML = callXMLService($InputXML);
   // Ritorno il nome della tabella creata da usare nell'SQL
   return $phptempfile;
}
