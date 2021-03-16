<?php
/**
 * 
 * Per test /USR/LOCAL/ZENDSVR/BIN/PHP /www/zendsvr/htdocs/wi400_lzovi/soap/template/wi400WsPool.php
 * 
 **/
// Carico le configurazioni di pooling settate per i vari sottosistemi
global $appBase,$root_path,$wi400Debug,$time_start,$time_step,$temaDir, $timeout, $mykey;
global $data_path,$actionLabel,$buttonsBar,$tab_index, $architettura, $db_separator,$authSysInf;
global $dbUser, $dbPath, $db, $settings, $routine_path;
// @todo parse_ini file per recuperare i sistemi informativi abilitati
$authSysInf = parse_ini_file("abilitazioni.ini");
//$authSysInf = array("INDTESTNEW"=>1001, "IND"=>1002, "FORMAZIONE"=>1003, "INDFRPR"=>1004, "YINDTESTNW"=>1005);  
//require_once "/datadisk/www/WI400/conf/wi400.conf.php";
require_once "/www/zendsvr/htdocs/WI400_LZOVI/conf/wi400.conf.php";
/*if (php_sapi_name() == 'cli') {
	require_once dirname(filter_input(INPUT_SERVER, 'PHP_SELF'))."/conf/wi400.conf.php";
} else {
	if (is_file("conf/wi400.conf.php")) {
		require_once "conf/wi400.conf.php";
		// Carico configurazione custom installat dal cliente sulla directory settings
		if (is_file("./settings/wi400Customer.conf.php")) {
			require_once "./settings/wi400Customer.conf.php";
			array_merge($settings, $customerSettings);
		}
	} else {
		header("Location: ".$appBase."conf/installer.php");
		exit();
	}
}*/
$appBase = '/WI400_LZOVI/';
//$appBase = '/WI400_LZOVI/';
//$name = explode('/',$_SERVER['REQUEST_URI']);
//$appBase = "/".$name[1]."/";
// PERCORSI
$doc_root     = $settings['doc_root'];
$data_path    = $settings['data_path'];
$doc_root = substr($doc_root,0, strlen($doc_root)-1);
$conf_path    = $doc_root.$appBase."conf";
$root_path    = $doc_root.$appBase;
$moduli_path  = $doc_root.$appBase."modules";
$base_path    = $doc_root.$appBase."base";
$routine_path = $doc_root.$appBase."routine";
$main_page    = $doc_root.$appBase."index.php";
$themes_path  = $doc_root.$appBase."themes";
$p13n_path    = "p13n/".$settings['p13n']."/";
$settings['p13n_path']= $p13n_path;
if ($settings['architettura']=="") $settings['architettura'] = 'default';
if ($settings['package']=="") $settings['package'] = 'default';
if (!isset($settings['caching_type']) || $settings['caching_type']=="") $settings['caching_type'] = 'default';	
$settings['package']=strtolower($settings['package']);
$save_XML = True;
$save_LOG = True;
$save_path = $settings['data_path']."wslog/";
$server_down = False;
// Configurazione
require_once $routine_path."/database/".strtolower($settings['database']).".cls.php";
//unset($settings['i5_toolkit']);
//$settings['xmlservice']=True;
//require_once $routine_path."/classi/wi400Routine.cls.php";
//require_once $routine_path."/classi/wi400Routine.cls.php";
if (isset($settings['xmlservice'])) {
	require_once $routine_path.'/classi/wi400RoutineXML.cls.php';
	require_once $routine_path."/generali/xmlsupport.php";		
} else {
    require_once $routine_path.'/classi/wi400Routine.cls.php';
}
require_once $routine_path."/generali/common.php";
require_once $routine_path."/os400/wi400Os400Job.cls.php";
require_once $routine_path."/generali/wi400File.php";
require_once $base_path."/arch/default.php";
require_once $base_path."/arch/".strtolower($settings['architettura']).".php";
require_once $base_path."/package/".strtolower($settings['package'])."/".strtolower($settings['package']).".php";
require_once $base_path.'/caching/'.strtolower($settings['caching_type']).'.php';
date_default_timezone_set($settings['timezone']);
$string = 'architettura_'.strtolower($settings['architettura']);
$architettura = new $string();
// **************************************************
// Connessione al sottisistema ZEND per le chiamate native su AS400
// **************************************************
include_once $routine_path.'/classi/wi400Connect.cls.php';
$db_user='PHPWEBSRVS';
$db_pwd='PHPWEBSRVS';
$mycon = new i5_connect($settings['server_zend_ip'], $settings['db_user'], $settings['db_pwd'], "P");

$db = new $settings['database'] ();
$timeout = 300;
$sysPooling = parse_ini_file("pooling.ini");
// Scrittura log di inzio pooling
$file = "/home/wi400/pooling.txt";
$handle = fopen($file, "a");
$time_end = microtime(true);
$time = $time_end - $time_start;
$dati = date("d:m:Y-H:i:s")." Inizio Job Pooling"."\r\n";
fwrite($handle, $dati);
// Ciclo sui vari sottosistemi per controllare quanti lavori ho attivi e aprire quelli mancani
foreach($sysPooling as $key=>$value)
{
	        // Se ho un valore maggiore di 0
			if ($value > 0) {
				$dati = date("d:m:Y-H:i:s")." Sistema Informativo:".$key." lavori minimi $value\r\n";
				fwrite($handle, $dati);
				poolJob($key, $value, $handle);
	        }
}
$dati = date("d:m:Y-H:i:s")." Fine job!"."\r\n";
fwrite($handle, $dati);
fclose($handle);
// Dsconnessione del lavoro utilizzato per lanciare i comandi
if (isset($settings['xmlservice'])) {
	$InputXML = '<?xml version="1.0"?>';
	$InternalKey = $mykey;
	$ControlKey="*immed";
	$OutputXML = '';
	$callPGM = $db->getCallPGM();
	if ($settings['xmlservice_driver']=="DB") {
		$db->bind_param ($callPGM, 1, "InternalKey", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 2, "ControlKey", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 3, "InputXML", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 4, "OutputXML", DB2_PARAM_OUT );
		$ret = db2_execute($callPGM);
	} else if($settings['xmlservice_driver']=="PDO") {
		$result = $callPGM->execute(array($InternalKey, $ControlKey, $InputXML));
		if (!$result) {
			return false;
		}
		$OutputXML="";
		$row = $callPGM->fetchAll(PDO::FETCH_NUM);
		//print_r($row);
		foreach ($row as $key => $value) {
			$OutputXML.=$value[0];
		}
	}
}
// Fine programma
exit(0);

function insertKey($key, $privateId) {
		$seg = msg_get_queue($key) ;
		$daten=$privateId."/".time();
    	msg_send ($seg, 1, $daten, true, true, $msg_err);				
}
/**
 * 
 * Verifico quanti lavori sono aperti per il sistema informativo e ne apro tanti fino ad arriva al numero impostato da prametro
 * 
 **/	
function poolJob($sysinf, $value, $handle){

	      global $db, $settings, $INTERNALKEY, $CONTROLKEY, $authSysInf, $timeout, $mykey;
     
//	      $db->add_to_librarylist(array("PHPLIB"), True);
	      $db->add_to_librarylist(array($settings['db_name']), True);
          // Cerco di recuperare una connessione già esistente in coda per il sistema informativo
          //$this->privateId=0;
          /*if (substr($sysinf,0 ,1)=='Y') {
          	$settings['db_host']='QUALITY';
          }*/
		  $db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name']);
          // Inizializzo il Private ID
       	  if (isset($settings['i5_toolkit'])) { 
              $privateId=0;
       	  } else {
              $privateId=uniqid("WS_");
       	  }
          // Verifico se mi è stato passato un ID privato di connessione
          $Key=$authSysInf["$sysinf"];
          //$Key = 1;
          $DTAQKey=$Key;
          $msgtype_receive=1;
          $maxsize=1000;
	      $message='';   
		  $serialize_needed=True;
		  $block_send=false;      
		  $msgtype_send=1;  
		  $option_receive=MSG_IPC_NOWAIT;
		  $seg = msg_get_queue($Key);
		  $queue_status=msg_stat_queue($seg);
		  $time_start = microtime(true);		 
		  // Verifico quanti ce ne sono fisicamente attivi.
          $numjob = 0;
          $list = new wi400Os400Job("I".substr($sysinf,0, 9));
          $list->getList();
          $messaggi = array();
          // Verifico il numero di lavori attivi e la relativa chiave
          while ($dati = $list->getEntry()) {
          	$key = str_pad($dati['JOBNAME'], 10).str_pad($dati['JOBUSER'], 10).str_pad($dati['JOBNBR'], 6);
          	//echo $key."\r\n";
          	$dati = $list->getJobLogMsg($key, "*FIRST", "");
          	$messaggi[]=$dati['MESSAGE'];
          	$dati = $list->getJobLogMsg($key, "*NEXT", $dati['KEY']);
          	$messaggi[]=$dati['MESSAGE'];
          	$dati = $list->getJobLogMsg($key, "*NEXT", $dati['KEY']);
          	$messaggi[]=$dati['MESSAGE'];
          	//print_r($messaggi);
          	$numjob++;
          }
          $active_key = array();
          // Metto in array le chiavi
          foreach ($messaggi as $key => $value) {
          	if (strpos($value, "CALL PGM(ZENDSVR/XMLSERVICE) PARM")!==False) {
          		$key = strpos($value, "WS_",0);
          		$fine = strpos($value, ")", $key);
          		$len = $fine-$key-1;
          		$internal_key = substr($value, $key, $len);
          		$active_key[] = $internal_key;
          		//echo $key. " ---> ".$internal_key."\r\n";
          	}
          }
          $mykey = $INTERNALKEY;
          $dati = date("d:m:Y-H:i:s")." Lavori Attivi:".$numjob."\r\n";
          fwrite($handle, $dati);
          if ($numjob==0) {
          	// Ripulisco la coda
          	msg_remove_queue($seg);
          	$dati = date("d:m:Y-H:i:s")." Pulizia Coda:".$numjob."\r\n";
          	$seg = msg_get_queue($Key);
          }
		  // Se in numero congruo esco
		  if ($numjob >= $value) {
		  	return true;
		  }
		  // Se in numero minore controllo le code, non si sa mai che qualcuno sia in scadenza e in ogni caso li rinfresco per tenerli attivi
          $k=0;
		  if ($numjob < $value) {
			  // Controllo se in coda c'è già qualcosa
			  if ($queue_status['msg_qnum']>0) {
			  // Cerco una connessione non scaduta
			      $i=0;
				  for ($i; $i<=$queue_status['msg_qnum']; $i++){
				      if (msg_receive($seg,$msgtype_receive ,$msgtype_erhalten,$maxsize,$daten,$serialize_needed, $option_receive, $err)===true) {
				              $valori = explode("/", $daten);
				              if (in_array($valori[0], $active_key)) {
				              	  echo "\r\nnon trovato in array";
					              $diff = time() - $valori[1];
					              if ($diff < $timeout) {
					                 //$this->privateId=intval($valori[0]);
					                 $k++;
					                 if (isset($settings['i5_toolkit'])) { 
						                 insertKey($DTAQKey,intval($valori[0]));
					                 } else {
					                     insertKey($DTAQKey,$valori[0]);
					                 }    
					              }
				              }
				  	   }
			  	   }
		       }
		  }
		  $dati = date("d:m:Y-H:i:s")." Lavori in coda $DTAQKey:".$k."\r\n";
		  fwrite($handle, $dati);
		  // Calcolo il numero dei lavori mancanti
		  $mancano = $value - $numjob;
		  if ($mancano <= 0) {
		  	return true;
		  }
		  $dati = date("d:m:Y-H:i:s")." Lavori da istanziare:".$mancano."\r\n";
		  fwrite($handle, $dati);
          // Creazione lavori mancanti
          for ($j=1;$j<=$mancano;$j++) { 
			  if (isset($settings['i5_toolkit'])) {
			  	$privateId=0;
			  } else {
			  	$privateId=uniqid("WS_");
			  }
	          if (isset($settings['i5_toolkit'])) {
		          $mycon->set_options("I".substr($sysinf,0, 9),null ,null ,null , null , null, $this->timeout , True, $privateId);
			      $conn = $mycon->connect();
				  $privateId=$mycon->getPrivateId();	      
			      if (!is_resource($conn)){
		 	           return '3';
				  }
	          } else {
				  $CONTROLKEY = '*idle('.$timeout.') *sbmjob(ZENDSVR/ZSVR_JOBD/'."I".substr($sysinf,0, 9).')';
				  $INTERNALKEY = '/tmp/'.$privateId;
	              connectDB();	
	          }
	          insertKey($DTAQKey,$privateId);
			  // Ora che ho i parametri cerco il sistema informativo
			  $filename = wi400File::getCommonFile("serialize", "SYSINF_NAME_".$sysinf.".dat");
			  $library=fileSerialized($filename);
			  /*if (substr($sysinf,0 ,1)=='Y') {
	          		$do = executeCommand("CALL QGPL/ZDT_ASPQA2");
	          }*/
	          // @todo la lista libreria se $library null viene già caricate ...
			  if ($library == Null) {
					connectDB();	
				    $library = retrive_sysinf_by_name($sysinf);				
				    $db->add_to_librarylist($library, True);
	    	  }
	    	  if (!empty($library)) {
	    	  	if (isset($settings['i5_toolkit'])) {  
	    	  		$mycon->add_to_librarylist($library);
	    	  	} else {
	    	  		$db->add_to_librarylist($library, True);
	    	  	}
	    	  }
		  }
}
/**
 * Mi connetto al Database, mi basta una volta con qualsiasi lista librerie
 */	
function connectDB($library="") {
	global $db, $settings;
	
	if ($settings['database']=='DB2I5')  {
		$db->setLink($this->conn);
	} else {
		$db->connect(False);
		$db->add_to_librarylist($library);
	}

}
	
