<?php
/**
 * @desc Recupero informazioni sul lavoro corrente di collegamento
 * @param string $reset Se ricaricare il dato o usare la cache
 * @return array Array con valori
 */
function getJobInfo_OS400($reset=False) {
	global $db, $settings;
	$array = array();
	if (!isset($settings['noroutine'])) {
		if (isset($_SESSION['JOB_INFO_USER']) && $reset==False) {
			$array['JOB'] = $_SESSION['JOB_INFO_NAME'];
			$array['USR'] = $_SESSION['JOB_INFO_USER'];
			$array['NBR'] = $_SESSION['JOB_INFO_NBR'];
		} else {
			$arrayJob = executeCommand("rtvjoba", array(), array("JOB"=>"JOB","USER"=>'USR',"NBR"=>"NBR"));
			if (is_array($arrayJob)) {
				$_SESSION['JOB_INFO_NAME'] = $arrayJob['JOB'];
				$_SESSION['JOB_INFO_USER'] = $arrayJob['USR'];
				$_SESSION['JOB_INFO_NBR'] = $arrayJob['NBR'];
				$array = $arrayJob;
			}
		}
	}
	return $array;
}
/**
 * @desc Recupero un duplicato di file con nome univoco su AS400
 * @param string $file File da duplicare
 * @param string $libl Libreria del file
 * @param string $clear Pulizia del file recuperato
 * @param string $prefix Prefisso del file, default F
 * @return string Nome del file se creato, altrimenti ERROR
 */
function getUniqueFile($file, $libl="PHPTEMP", $clear=False, $prefix="F", $data='*NO') {
	global $db, $settings;
    static $fileCache = array();
	$phptempfile = strtoupper($file.$prefix.session_id());
	$new = false;
	$prefix = substr($prefix, 0, 1);
	if (isset($fileCache[$phptempfile."-".$libl]) and  $clear==False) {
		return $fileCache[$phptempfile."-".$libl];
	} else {
		if (!$db->ifExist($phptempfile, $libl)) {
			$new = True;
			//$querydrop="drop table $libl/$phptempfile";
			//$db->query($querydrop);
			$uniqid = $prefix.strtoupper(substr(uniqid(),5,9));
			//$commandcopy ="CPYF FROMFILE($file) TOFILE($libl/$uniqid) MBROPT(*REPLACE) CRTFILE(*YES) NBRRCDS(1)";
			$commandcopy ="CRTDUPOBJ OBJ($file) FROMLIB(*LIBL) OBJTYPE(*FILE) TOLIB($libl) NEWOBJ($uniqid) CST(*NO) TRG(*NO) DATA($data)";
			executeCommand($commandcopy);
			//$querycreate="CREATE table phptemp/$phptempfile as (select * from $file) definition only";
			$queryrename="RENAME TABLE $libl".$settings['db_separator']."$uniqid TO $phptempfile";
			//$db->query($querycreate);
			$db->PRAGMA_RESOLVE_TABLE=False;
			$db->query($queryrename);
			$db->PRAGMA_RESOLVE_TABLE=True;
		}
		$queryselect = "SELECT SYSTEM_TABLE_NAME FROM QSYS2".$settings['db_separator']."SYSTABLES WHERE TABLE_NAME='".strtoupper($phptempfile)."' AND TABLE_SCHEMA='$libl'";
		$db->PRAGMA_RESOLVE_TABLE=False;
		$result = $db->singleQuery($queryselect);
		$db->PRAGMA_RESOLVE_TABLE=True;
		$row = $db->fetch_array($result);
		if ($row) {
			if (!$new) {
				if ($clear==True) {
					$commandcopy ="CLRPFM FILE($libl/".$row['SYSTEM_TABLE_NAME'].")";
					executeCommand($commandcopy);
				}
			}
			$fileCache[$phptempfile."-".$libl]=$row['SYSTEM_TABLE_NAME'];
			return $row['SYSTEM_TABLE_NAME'];
		} else {
			$fileCache[$phptempfile."-".$libl]="ERROR";
			return "ERROR";
		}
	}
}

/**
 * @Desc Cerca ASP da aggiungere alla lista
 * @param string: Codice ASP
 */
function setASP($asp = "") {
	global $architettura, $connzend,$routine_path, $settings;
	if ($asp!="") {
		switch ($asp) {
			case "*ARCH";
			if (method_exists ( $architettura , "getASP" )) {
				$asp = $architettura->getASP();
				executeCommand("SETASPGRP ASPGRP(".trim($asp).")");         		    }
				break;
			case "*P13N";
			require_once p13n('/base/includes/asp.php');
			break;
			default:
				//executeCommand("SETASPGRP ASPGRP(".trim($asp).")");
		}
	}
}
/**
 * @desc Creazione di una UserSpace
 * @param array $property  Array con i parametri di creazione
 */
function userspace_create($property) {
	global $settings, $connzend, $routine_path;

	require_once $routine_path."/os400/APIFunction.php";
	if (isset($settings['user_space_storeprocedure'])) {
		require_once $routine_path."/classi/wi400UserSpaceST.cls.php";
		$ret = wi400UserSpaceST::create_userspace($property);
		//$ret = True;
	} else {
		if (isset($settings['xmlservice'])) {
			$usr_spc = new wi400Routine('QUSCRTUS', $connzend);
			$usr_spc->load_description();
			$usr_spc->prepare();
			$usr_spc->set('USERSPACE', str_pad($property[I5_NAME], 10, " ").str_pad($property[I5_LIBNAME], 10 , " "));
			$usr_spc->set('INITSIZE', $property[I5_INITSIZE]);
			$usr_spc->set('PUBAUT',$property[I5_AUTHORITY]);
			$usr_spc->set('INITVALUE',$property[I5_INIT_VALUE]);
			$usr_spc->set('REPLACE',"*YES");
			$usr_spc->set('DESC',$property[I5_DESCRIPTION]);
			$ret = $usr_spc->call(True);
		} else {
			$ret = i5_userspace_create($property);
		}
	}
	return $ret;
}
/**
 * @desc Preparo la lettura/scrittura di una USERSPACE
 * @param string $userSpace  Nome della USERSPACE in formato libreria/dtaq
 * @param struct $tracciatoDati Tracciato DS da leggere/scrivere
 * @param object $conn Connessione nel caso di i5
 * @param string $type Tipo Perapre R=Read, W=Read
 */
function userspace_prepare($userSpace, $tracciatoDati,$conn =null,  $type="R", $count = 1, $fast=False) {

	global $settings, $routine_path;
	require_once $routine_path."/os400/APIFunction.php";
	if ($fast == True && $settings['platform']=='AS400' && $settings['xmlservice_userspace_fast'] ==true) {
		require_once $routine_path."/classi/wi400UserSpaceFast.cls.php";
		require_once $routine_path."/generali/conversion.php";
		$dati = explode("/", $userSpace);
		$usspc = new wi400UserSpaceFast($dati[1], $dati[0], $tracciatoDati,strtolower($type));
		return $usspc;
	}
	if (isset($settings['user_space_storeprocedure'])) {
		require_once $routine_path."/classi/wi400UserSpaceST.cls.php";
		$dati = explode("/", $userSpace);
		$usspc = new wi400UserSpaceST($dati[1], $dati[0], $tracciatoDati);
		return $usspc;
	}
	if (isset($settings['xmlservice'])) {
		$program = 'QUSRTVUS';
		$len = dsLen($tracciatoDati);
		if ($type!="R") {
			$program = 'QUSCHGUS';
		}
		if ($count > 1) {
			$len = $len * $count;
		}
		$queue = new wi400Routine($program);
		$queue->load_description (null, $tracciatoDati, True, $count);
		$queue->prepare (True);
		$libusr = explode("/",$userSpace);
		$queue->set('USERSPACE', str_pad($libusr[1], 10, " ").str_pad($libusr[0], 10, " "));
		$queue->set('SIZE', $len);
	} else {
		$queue = i5_userspace_prepare($userSpace, $tracciatoDati, $conn);
	}
	return $queue;
}
/**
 * @desc Retrive UserSpace
 * @param string $userSpace  Oggetto DTAQ
 * @return array: $dati Dati letti
 */
function userspace_get($userSpace, $parmOut, $start = 1) {

	global $settings;
	if (get_class($userSpace)=="wi400UserSpaceFast") {
		return $userSpace->get($start-1);
	}
	if (get_class($userSpace)=="wi400UserSpaceST") {
		return $userSpace->get($start-1);
	}
	if (isset($settings['xmlservice'])) {
		$userSpace->set('OFFSET', $start);
		$do = $userSpace->call();
		if ($do) {
			$dati = $userSpace->get('DATI','*ALL');
		} else {
			$dati = False;
		}
	} else {
		i5_userspace_get($userSpace, $parmOut, $start);
		foreach ($parmOut as $key =>$value) {
			$dati[$key] = ${$value};
		}
	}
	return $dati;
}
/**
 * @desc Write UserSpace
 * @param string $userSpace  Oggetto UserSpace
 * @return array: $dati Dati da Scrivere
 */
function userspace_put($userSpace, $parmOut, $start=1) {

	global $settings;

	if (get_class($userSpace)=="wi400UserSpaceST") {
		return $userSpace->put($parmOut, $start-1);
	}
	if (isset($settings['xmlservice'])) {
		$userSpace->set('DATI', $parmOut);
		$userSpace->set('OFFSET', $start);
		$userSpace->set('FORCE', '0');
		$do = $userSpace->call(True, True);
		if ($do) {
			$ret = True;
		} else {
			$ret = False;
		}
	} else {
		$ret = i5_userspace_put($userSpace, $parmOut, $start);
	}
	return $ret;
}
/**
 * @desc Richiamo Comando
 * @param string $cmd  Stringa di comando
 * @param array  $arrayInput Array con i parametri di input
 * @param array  $arrayOutput Array con i parametri di ritorno
 */
function executeCommand($cmd, $arrayInput=array(), $arrayOutput=array()) {
	global $settings;
	if (isset($settings['xmlservice'])) {
		foreach ($arrayOutput as $key=>$value) {
			global ${$value};
		}
		$test = callXMLCmd($cmd ,$arrayInput, $arrayOutput);
		if (isset($arrayOutput) && count($arrayOutput)> 0) {
			$dati = array();
			foreach ($arrayOutput as $key=>$value) {
				$dati[$value] = ${$value};
			}
			$test = $dati;
		}
		return $test;
	} else {
		foreach ($arrayOutput as $key=>$value) {
			global ${$value};
		}
		return i5_command($cmd ,$arrayInput, $arrayOutput);
	}
}
/**
 * @desc Lettura di una DTAAREA
 * @param string $dtaare  Data Area da leggere
 * @param integer  $start Parametro iniziale
 * @param integer  $length Lunghezza da leggere
 */
function data_area_read($dataArea, $start=0, $length=0) {
	global $settings, $db;
	
	if (isset($settings['data_area_with_udft'])) {
		$pezzi = explode("/", $dataArea);
		$campo ="DATA_AREA_VALUE";
		// Se non c'è l'oggetto significa che non è stato passato
		if (!isset($pezzi[1]) || $pezzi[1]=="") {
			$pezzi[1]=$pezzi[0];
			$pezzi[0]="*LIBL";
		}
		if ($start!=0) {
			$campo = "SUBSTR($campo, $start";
			if ($length!=0) {
				$campo .=" , $length) AS VALUE";
			} else {
				$campo .=") AS VALUE";
			}
		} else {
			$campo .= " AS VALUE";
		}
		$sql = "SELECT $campo FROM TABLE(QSYS2.DATA_AREA_INFO(           
         DATA_AREA_NAME => '$pezzi[1]', DATA_AREA_LIBRARY => '$pezzi[0]'))";                                                         

		$result = $db->query($sql);
        $row = $db->fetch_array($result);
        $result = Null;
		return $row['VALUE'];
	} else {
		if (isset($settings['xmlservice'])) {
			$arrayOutput = array("RTNVAR"=>"retval");
			$parm = "$dataArea";
			if ($start != 0) {
				$parm .= " ($start";
				if ($length!=0) {
					$parm .=" $length)";
				} else {
					$parm .=")";
				}
			}
			$arrayInput = array("DTAARA"=>$parm);
			foreach ($arrayOutput as $key=>$value) {
				global ${$value};
			}
			executeCommand("RTVDTAARA" ,$arrayInput, $arrayOutput);
			$ret = $retval;
		} else {
			if ($start !=0 && $length !=0 ){
				$ret = i5_data_area_read($dataArea ,$start, $length);
			} elseif ($start!=0 && length ==0) {
				$ret = i5_data_area_read($dataArea ,$start, $length);
			} else {
				$ret = i5_data_area_read($dataArea);
			}
		}
	}
	return $ret;
}
/**
 * @desc Scrittura di una DTAAREA
 * @param string $dtaaArea  Data Area da leggere
 * @param string $valore Valore
 * @param integer  $start Parametro iniziale
 * @param integer  $length Lunghezza da leggere
 */
function data_area_write($dataArea, $valore, $start=0, $length=0) {
	global $settings;

	if (isset($settings['xmlservice'])) {
		$arrayOutput = array();
		$parm = "$dataArea";
		if ($start != 0) {
			$parm .= " ($start";
			if ($length!=0) {
				$parm .=" $length)";
			} else {
				$parm .=")";
			}
		}
		$arrayInput = array("DTAARA"=>$parm, "VALUE"=>"'".$valore."'");
		$ret = executeCommand("CHGDTAARA" ,$arrayInput, $arrayOutput);
	} else {
		foreach ($arrayOutput as $key=>$value) {
			global ${$value};
		}
		if ($start !=0 && $length !=0 ){
			$ret = i5_data_area_write($dataArea , $valore, $start, $length);
		} elseif ($start!=0 && length ==0) {
			$ret = i5_data_area_write($dataArea , $valore, $start, $length);
		} else {
			$ret = i5_data_area_write($dataArea, $valore);
		}
	}
	return $ret;
}
/**
 * @desc Creazione di una DTAAREA
 * @param string $dtaaArea  Data Area da leggere
 * @param integer  $length Lunghezza da leggere
 */
function data_area_create($dataArea, $length=0) {
	global $settings;

	if (isset($settings['xmlservice'])) {
		$arrayOutput = array();
		$arrayInput = array("DTAARA"=>$dataArea, "TYPE"=>"*CHAR", "LEN"=>$length);
		$ret = executeCommand("CRTDTAARA" ,$arrayInput, $arrayOutput);
	} else {
		$ret = i5_data_area_create($dataArea , $length);
	}
	return $ret;
}
/**
 * @desc Preparo la lettura/scrittura di una DTAQ
 * @param string $dtaaQ  DataQ in formato libreria/dtaq
 * @param struct $tracciatoDati Tracciato DS da leggere
 * @param string $type tipo prepare S SEND, R RECEIVE
 */
function dtaq_prepare($dataQ, $tracciatoDati, $type="S", $len =-1, $isKeyed = False) {

	global $settings;
	if (isset($settings['xmlservice'])) {
		$program = 'QSNDDTAQ';
		$description = 'QSNDDTAQ_K';
		if ($len==-1) {
			$len = dsLen($tracciatoDati);
		}
		if ($isKeyed == True) {
			$description = "QSNDDTAQ_K";
		}
		if ($type!="S") {
			$program = 'QRCVDTAQ';
			$description = "QRCVDTAQ";
			$len = 0;
		}
		/*echo "<br>PrograM:".$program."_Type:".$type;
		 echo "<pre>";
		print_r($description);
		echo "</pre>";*/
		$queue = new wi400Routine($program);
		$queue->load_description ($description, $tracciatoDati, True);
		$queue->prepare ();
		$libcod = explode("/",$dataQ);
		$queue->set('CODA', $libcod[1]);
		$queue->set('LIBRERIA', $libcod[0]);
		$queue->set('LEN', $len);
	} else {
		$queue = i5_dtaq_prepare($dataQ, $tracciatoDati);
	}
	return $queue;
}
/**
 * @desc Send DTAQ
 * @param string $dtaaQ  DataQ in formato libreria/dtaq
 * @param string $key Chiavi di scrittura
 * @param struct $tracciatoDati Tracciato DS da leggere
 * @param string $type tipo prepare S SEND, R RECEIVE
 */
function dtaq_send($dataQ, $key="", $dati="") {

	global $settings;

	if (isset($settings['xmlservice'])) {
		$dataQ->set("DATI", $dati);
		if ($key != "") {
			$dataQ->set("KEY", $key);
			$dataQ->set("KEYLEN", 8);
		}
		$do = $dataQ->call();
	} else {
		$do = i5_dtaq_send($dataQ,$key,$dati);
	}
	return $do;
}
/**
 * @desc Receive DTAQ
 * @param string $dtaaQ  Oggetto DTAQ
 * @return array: $dati Dati letti
 */
function dtaq_receive($dataQ, $timeOut=0) {

	global $settings;

	if (isset($settings['xmlservice'])) {
		$dataQ->set("WAIT", $timeOut);
		$do = $dataQ->call();
		if ($do) {
			$dati = $dataQ->get('DATI');
		} else {
			$dati = False;
		}
	} else {
		$dati = i5_dtaq_receive($dataQ);
	}
	return $dati;
}
/**
 * Costruisce automaticamente il descrittore di una DS legata ad un file reperendo automaticamente la sua
 * struttura da AS400
 *
 * @param $file      string:file di cui costruire il descrittore
 * @param $db        object:oggetto di connessione al DB
 * @param $connzend  string:connessione a ZEND. Non viene usato $this->Connezend perchè la funzione viene usata anceh esternamente
 * @param $libre     string:libreria del file. Se non passata viene ricercata
 *
 * @return array     Array contenente la descrizine dei campi del file
 */
function create_descriptor_OS400($file, $connzend, $libre = Null, $desc = False) {
	global $db, $settings;

	static $stmt;

	if ($desc) {
		$name = 'COLUMN_TEXT';
	} else {
		$name = 'COLUMN_NAME';
	}
	// Verifico se ho già creato il descrittore del file e se esiste se per caso ha la data del giorno
	$putfile = False;
	// Se non mi è stata passata la libreria la cerco
	if (! isset ( $libre )) {
		$libre = rtvLibre ( $file, $connzend );
	}
	$filename = wi400File::getCommonFile ( "serialize", $libre . "_" . $file . ".dat" );
	$desc = fileSerialized ( $filename );
	if ($desc != null) {
		return $desc;
	}
	// Se arrivo qui devo ricaricre il descrittore e quindi apro il file per la scrittura
	// Accedo alla tabella su AS400 per recuperarne la struttura
	if (! isset ( $stmt )) {
		$sql = "SELECT COLUMN_NAME, LENGTH , DATA_TYPE, COALESCE(NUMERIC_SCALE, 0) AS NUMERIC_SCALE, COALESCE(NUMERIC_PRECISION, 0) AS NUMERIC_PRECISION, COALESCE(COLUMN_TEXT,'') AS COLUMN_TEXT FROM
	QSYS2".$settings['db_separator']."SYSCOLUMNS2 WHERE TABLE_NAME = ?
	AND TABLE_SCHEMA = ? ORDER BY ORDINAL_POSITION";
		$db->PRAGMA_RESOLVE_TABLE=False;
		$stmt = $db->prepareStatement ( $sql );
		$db->PRAGMA_RESOLVE_TABLE=True;
	}
	$result = $db->execute ( $stmt, array ($file, $libre ) );
	// Verifico se ho trovato qualcosa
	if (! $result) {
		return false;
	}
	$desc1 = array ();
	// Ciclo di costruzione e caricamento del descrittore della DS da utilizzare
	while ( $info = $db->fetch_array ( $stmt ) ) {
		// Campi Packed
		if ($info ['DATA_TYPE'] == 'DECIMAL') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_PACKED, "Length" => "$len.$dec" );
		}
		// Campi alfanumerici
		if ($info ['DATA_TYPE'] == 'CHAR') {
			$len = $info ['LENGTH'];
			$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );

		}
		// Zoned
		if ($info ['DATA_TYPE'] == 'NUMERIC') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_ZONED, "Length" => "$len.$dec" );
		}
		// Integer
		if ($info ['DATA_TYPE'] == 'INTEGER') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_INT, "Length" => "$len.$dec" );
		}
		// Time
		if ($info ['DATA_TYPE'] == 'TIME') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "8" );
		}
		// Date
		if ($info ['DATA_TYPE'] == 'DATE') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "10" );
		}
		// TimeStamp
		if ($info ['DATA_TYPE'] == 'TIMESTMP') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "26" );
		}
		// TimeStamp
		if ($info ['DATA_TYPE'] == 'FLOAT') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_FLOAT, "Length" => "26" );
		}

	}
	//db2_free_result($result);

	put_serialized_file($filename, $desc1);
	// Ritorno il descrittore recuperato dalla routine
	return $desc1;
}

function rtvLibre_OS400($file, $conn) {
	static $pgm;

	if (! isset ( $pgm )) {
		$pgm = new wi400Routine( "ZRTVLIB", $conn );
		$pgm->load_description ();
		$pgm->prepare ();
	}
	if (isset ( $_SESSION ['array_librerie'][$file])) {
		return $_SESSION ['array_librerie'][$file];
	} else {
		if (strlen($file)> 10) {
			return "PHPTEMP";
		} else {
			$pgm->set ( 'FILE', $file );
			$pgm->call ();
			if ($pgm) {
				$libre = array ();
				if (isset($_SESSION ['array_librerie'])) {
					$libre = $_SESSION ['array_librerie'];
				}
				$libre [$file] = $pgm->get ( 'LIBRE' );
				$_SESSION['array_librerie'] = $libre;
				return $libre [$file];
			} else {
				return "";
			}
		}
	}
}
function getSequence_OS400($name) {
	global $connzend;

	$sequence = new wi400Routine( "ZCREPNUM", $connzend );
	$do = $sequence->load_description ();
	$do = $sequence->prepare ();
	$do = $sequence->set ( 'CODNUM', $name );
	$do = $sequence->call ();
	return $sequence->get ( 'NUMERO' );

}
function getSysSequence_OS400($name) {
	global $connzend;

	$sequence = new wi400Routine( "ZCSYSNUM", $connzend );
	$do = $sequence->load_description ();
	$do = $sequence->prepare ();
	$do = $sequence->set ( 'CODNUM', $name );
	$do = $sequence->call ();
	return $sequence->get ( 'NUMERO' );
}
function clearPHPTEMP_OS400($session_id) {
	global $db, $settings;

	$sql = "select TABLE_SCHEMA, TABLE_NAME from QSYS2".$settings['db_separator']."SYSTABLES where TABLE_SCHEMA ='".$settings['db_temp']."' and TABLE_NAME like'%".strtoupper($session_id)."%'";
	$db->PRAGMA_RESOLVE_TABLE=False;
	$result = $db->query($sql, False, 0);
	$db->PRAGMA_RESOLVE_TABLE=True;
	if ($result) {
		while ($row = $db->fetch_array($result)) {
			$sql1 = "DROP TABLE ".trim($row['TABLE_SCHEMA']).$settings['db_separator'].trim($row['TABLE_NAME']);
			$db->query($sql1);
		}
	}
}

function get_job_log_data($jobName,$userName,$jobNumber,$form) {
	global $db, $connzend, $routine_path;

	require_once $routine_path."/os400/wi400Os400Job.cls.php";

	$found = false;
	$dati = array();
	$lines = "";
	$user_id = "";
	$jobqual = str_pad($jobName, 10).str_pad($userName, 10).str_pad($jobNumber, 6);
	//$HdlJob = wi400Os400Job::getJobLog($jobqual);
	//$HdlJob = i5_jobLog_list(array(I5_JOBNAME=>$jobName,I5_USERNAME=>$userName,I5_JOBNUMBER=>$jobNumber));
	$HdlJob=array();
	$dati = wi400Os400Job::getJobLogMsg($jobqual, "*FIRST", "");
	while ($dati['MESSAGE']!="") {
		$HdlJob[]=$dati['MESSAGE'];
		$dati =  wi400Os400Job::getJobLogMsg($jobqual, "*NEXT", $dati['KEY']);
	}

	$dati['SESSION_ID'] = '';

	if (is_bool($HdlJob)){
		$ret = "Errore reperimento JobLog";
		//			print_r($ret);
	}
	else {
		foreach ($HdlJob as $key=>$valore) {
			$session_id = "";

			if($found===false && strpos($valore,"USER:")!==false) {
				$pos_i = strpos($valore,"USER:");
				$pos_f = strpos($valore,"-",$pos_i);

				$user_id = substr($valore,$pos_i+5,($pos_f-($pos_i+5)));
				$session_id = substr($valore,$pos_f+1,26);

				//						echo "SESSION:$session_id - USER:$user_id<br>";

				$dati['SESSION_ID'] = $session_id;

				$found = true;
				if($form!="LOG_LAVORO")
					break;
			}

			$lines .= "- ".$valore."\r\n";
			/*if(isset($data[I5_LOBJ_MSGHLP]))
			 $lines .= $data[I5_LOBJ_MSGHLP]."\r\n";*/
		}
	}

	//		echo "SESSION: $session_id<br>";
	$ip = "";
	if($dati['SESSION_ID']!="")
		$ip = get_my_ip($dati['SESSION_ID']);

	$dati['LINES'] = $lines;
	$dati['USER_ID'] = $user_id;
	$dati['IP'] = $ip;

	//		echo "DATI:<pre>"; print_r($dati); echo "</pre>";

	return $dati;
}